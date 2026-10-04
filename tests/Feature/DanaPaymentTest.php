<?php

namespace Tests\Feature;

use App\Contracts\DanaQrisGateway;
use App\Data\DanaQrisResult;
use App\Data\DanaWebhookData;
use App\Jobs\SendInvoiceEmailJob;
use App\Models\Catalog;
use App\Models\Invoice;
use App\Models\Order;
use App\Models\Payment;
use App\Models\PaymentMethod;
use App\Models\ServicePackage;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Str;
use Mockery\MockInterface;
use RuntimeException;
use Tests\TestCase;

class DanaPaymentTest extends TestCase
{
    use RefreshDatabase;

    public function test_generate_qris_requires_valid_order(): void
    {
        $this->post(route('public.payments.dana.store'), ['order_uuid' => (string) Str::uuid()])
            ->assertSessionHasErrors('order_uuid');

        $this->assertDatabaseCount('payments', 0);
    }

    public function test_generate_qris_uses_database_amount_and_reuses_pending_payment(): void
    {
        [$order] = $this->paymentRecords();
        $this->mock(DanaQrisGateway::class, function (MockInterface $mock): void {
            $mock->shouldReceive('generate')->once()->withArgs(fn (Payment $payment): bool => (int) $payment->amount === 249000)
                ->andReturn(new DanaQrisResult('DANA-REF', '000201QRIS', null, null));
        });

        $this->post(route('public.payments.dana.store'), ['order_uuid' => $order->uuid, 'amount' => 1])->assertOk();
        $this->post(route('public.payments.dana.store'), ['order_uuid' => $order->uuid, 'amount' => 1])->assertOk();

        $this->assertDatabaseCount('payments', 1);
        $this->assertDatabaseHas('payments', ['order_id' => $order->id, 'amount' => 249000, 'qr_content' => '000201QRIS']);
    }

    public function test_generate_qris_is_unavailable_when_dana_method_is_inactive(): void
    {
        [$order, $payment] = $this->paymentRecords();
        $payment->delete();
        PaymentMethod::query()->where('code', 'dana')->update(['is_active' => false]);
        $this->mock(DanaQrisGateway::class, fn (MockInterface $mock) => $mock->shouldNotReceive('generate'));

        $this->post(route('public.payments.dana.store'), ['order_uuid' => $order->uuid])
            ->assertServiceUnavailable()
            ->assertSee('Pembayaran QRIS sedang dinonaktifkan');

        $this->assertDatabaseCount('payments', 0);
    }

    public function test_invalid_webhook_does_not_change_payment(): void
    {
        [, $payment] = $this->paymentRecords();
        $this->mock(DanaQrisGateway::class, fn (MockInterface $mock) => $mock->shouldReceive('parseWebhook')->once()->andThrow(new RuntimeException('invalid signature')));

        $this->postJson(route('dana.webhook'), [])->assertUnauthorized();

        $this->assertDatabaseHas('payments', ['id' => $payment->id, 'status' => 'pending']);
        $this->assertDatabaseCount('invoices', 0);
    }

    public function test_amount_mismatch_does_not_mark_payment_paid(): void
    {
        [, $payment] = $this->paymentRecords();
        $this->mockWebhook(new DanaWebhookData($payment->partner_reference_no, 'DANA-REF', 'merchant-test', '1.00', 'IDR', '00', CarbonImmutable::now()), 1);
        config(['dana.merchant_id' => 'merchant-test']);

        $this->postJson(route('dana.webhook'), [])->assertUnprocessable();

        $this->assertDatabaseHas('payments', ['id' => $payment->id, 'status' => 'pending']);
        $this->assertDatabaseCount('invoices', 0);
    }

    public function test_paid_webhook_is_idempotent_and_dispatches_one_invoice_email(): void
    {
        Queue::fake([SendInvoiceEmailJob::class]);
        [$order, $payment] = $this->paymentRecords();
        $this->mockWebhook(new DanaWebhookData($payment->partner_reference_no, 'DANA-REF', 'merchant-test', '249000.00', 'IDR', '00', CarbonImmutable::parse('2026-10-04 10:00:00')), 2);
        config(['dana.merchant_id' => 'merchant-test']);

        $this->postJson(route('dana.webhook'), [])->assertOk();
        $this->postJson(route('dana.webhook'), [])->assertOk();

        $this->assertDatabaseHas('payments', ['id' => $payment->id, 'status' => 'paid', 'dana_reference_no' => 'DANA-REF']);
        $this->assertDatabaseHas('orders', ['id' => $order->id, 'payment_status' => 'paid']);
        $this->assertMatchesRegularExpression('/\AMEMOIRE-[A-Z0-9]{8}\z/', $order->fresh()->customer_access_code);
        $this->assertDatabaseCount('invoices', 1);
        Queue::assertPushed(SendInvoiceEmailJob::class, 1);
    }

    public function test_email_failure_does_not_revert_paid_payment_or_order(): void
    {
        [$order, $payment] = $this->paymentRecords();
        $payment->update(['status' => 'paid', 'paid_at' => now()]);
        $order->update(['status' => 'paid', 'payment_status' => 'paid']);
        $invoice = Invoice::create([
            'uuid' => (string) Str::uuid(),
            'invoice_number' => 'INV-20261004-ABC123',
            'order_id' => $order->id,
            'payment_id' => $payment->id,
            'customer_name' => $order->customer_name,
            'customer_email' => $order->email,
            'package_name' => $order->package,
            'amount' => $payment->amount,
            'currency' => 'IDR',
            'paid_at' => now(),
        ]);

        (new SendInvoiceEmailJob($invoice->id))->failed(new RuntimeException('SMTP unavailable'));

        $this->assertDatabaseHas('invoices', ['id' => $invoice->id, 'email_status' => 'failed']);
        $this->assertDatabaseHas('payments', ['id' => $payment->id, 'status' => 'paid']);
        $this->assertDatabaseHas('orders', ['id' => $order->id, 'payment_status' => 'paid']);
    }

    public function test_invoice_displays_memoire_branding_and_complete_order_details(): void
    {
        [$order, $payment] = $this->paymentRecords();
        $catalog = Catalog::create([
            'name' => 'Arunika',
            'category' => 'Pernikahan',
            'package' => 'Timeless',
            'color' => '#582308',
            'status' => 'active',
        ]);
        $order->update([
            'catalog_id' => $catalog->id,
            'form_category' => 'Pernikahan',
            'form_data' => ['nama_mempelai' => 'Nadia & Arka', 'lokasi_acara' => 'Jakarta'],
            'event_date' => '2026-12-20',
            'notes' => 'Gunakan nuansa hangat.',
            'status' => 'paid',
            'payment_status' => 'paid',
        ]);
        $payment->update(['status' => 'paid', 'paid_at' => now(), 'dana_reference_no' => 'DANA-INVOICE-001']);
        $invoice = Invoice::create([
            'uuid' => (string) Str::uuid(),
            'invoice_number' => 'INV-20261004-MEM001',
            'order_id' => $order->id,
            'payment_id' => $payment->id,
            'customer_name' => $order->customer_name,
            'customer_email' => $order->email,
            'package_name' => 'Timeless',
            'amount' => 249000,
            'currency' => 'IDR',
            'paid_at' => now(),
        ]);

        $this->get(route('public.invoices.show', $invoice))
            ->assertOk()
            ->assertSee('logo-memoire.png', false)
            ->assertSee('INV-20261004-MEM001')
            ->assertSee('Arunika')
            ->assertSee('Pernikahan')
            ->assertSee('Nadia &amp; Arka', false)
            ->assertSee('Jakarta')
            ->assertSee('Gunakan nuansa hangat.')
            ->assertSee('DANA-INVOICE-001');
    }

    public function test_intimate_package_receives_access_code_only_after_successful_payment(): void
    {
        [$order, $payment] = $this->paymentRecords('Intimate');

        $this->assertNull($order->customer_access_code);
        $payment->update(['status' => 'paid', 'paid_at' => now()]);
        Payment::syncOrderStatus($order);

        $order->refresh();
        $this->assertSame('paid', $order->payment_status);
        $this->assertMatchesRegularExpression('/\AMEMOIRE-[A-Z0-9]{8}\z/', $order->customer_access_code);
    }

    public function test_simple_package_never_receives_customer_dashboard_access_code(): void
    {
        [$order, $payment] = $this->paymentRecords('Simple');
        $payment->update(['status' => 'paid', 'paid_at' => now()]);

        Payment::syncOrderStatus($order);

        $this->assertSame('paid', $order->fresh()->payment_status);
        $this->assertNull($order->customer_access_code);
    }

    /** @return array{Order, Payment} */
    private function paymentRecords(string $packageName = 'Timeless'): array
    {
        $package = ServicePackage::create(['name' => $packageName, 'price' => 249000, 'is_active' => true]);
        $method = PaymentMethod::query()->where('code', 'dana')->firstOrFail();
        $order = Order::factory()->create(['uuid' => (string) Str::uuid(), 'service_package_id' => $package->id, 'package' => $package->name, 'total' => 249000]);
        $payment = Payment::factory()->create([
            'uuid' => (string) Str::uuid(),
            'order_id' => $order->id,
            'payment_method_id' => $method->id,
            'service_package_id' => $package->id,
            'partner_reference_no' => 'MEM261004ABCDEFGHIJ',
            'amount' => 249000,
            'currency' => 'IDR',
            'status' => 'pending',
            'expires_at' => now()->addMinutes(15),
        ]);

        return [$order, $payment];
    }

    private function mockWebhook(DanaWebhookData $data, int $times): void
    {
        $this->mock(DanaQrisGateway::class, fn (MockInterface $mock) => $mock->shouldReceive('parseWebhook')->times($times)->andReturn($data));
    }
}
