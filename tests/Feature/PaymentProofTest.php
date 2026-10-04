<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\Payment;
use App\Models\PaymentMethod;
use App\Models\ServicePackage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Tests\TestCase;

class PaymentProofTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_can_upload_private_transfer_proof_for_an_order(): void
    {
        Storage::fake('local');
        $order = $this->eligibleOrder();
        $bca = PaymentMethod::query()->where('code', 'bca')->firstOrFail();

        $response = $this->post(route('public.payments.proof.store', $order), [
            'payment_method_id' => $bca->id,
            'proof' => UploadedFile::fake()->image('transfer.png'),
        ], ['Accept' => 'application/json'])->assertCreated();

        $payment = Payment::query()->where('order_id', $order->id)->firstOrFail();
        $response->assertJsonPath('message', 'Bukti pembayaran berhasil dikirim dan menunggu verifikasi admin.')
            ->assertJsonPath('payment.status', 'pending');
        $this->assertSame($order->total, (string) $payment->amount);
        $this->assertNotNull($payment->proof_submitted_at);
        Storage::disk('local')->assertExists($payment->proof_path);
    }

    public function test_customer_cannot_upload_invalid_file_or_use_qris_proof_upload(): void
    {
        Storage::fake('local');
        $order = $this->eligibleOrder();
        $bca = PaymentMethod::query()->where('code', 'bca')->firstOrFail();
        $dana = PaymentMethod::query()->where('code', 'dana')->firstOrFail();

        $this->post(route('public.payments.proof.store', $order), [
            'payment_method_id' => $bca->id,
            'proof' => UploadedFile::fake()->create('notes.txt', 10, 'text/plain'),
        ], ['Accept' => 'application/json'])->assertUnprocessable()
            ->assertJsonValidationErrors('proof');

        $this->post(route('public.payments.proof.store', $order), [
            'payment_method_id' => $dana->id,
            'proof' => UploadedFile::fake()->image('transfer.png'),
        ], ['Accept' => 'application/json'])->assertUnprocessable()
            ->assertJsonValidationErrors('payment_method_id');

        $this->assertDatabaseCount('payments', 0);
        $this->assertSame([], Storage::disk('local')->allFiles('payment-proofs'));
    }

    public function test_only_admin_can_view_uploaded_payment_proof(): void
    {
        Storage::fake('local');
        $payment = $this->paymentWithProof();
        Storage::disk('local')->put($payment->proof_path, 'private proof');

        $this->get(route('admin.payments.proof', $payment))
            ->assertRedirect(route('admin.login'));

        $this->actingAs(User::factory()->create())
            ->get(route('admin.payments.proof', $payment))
            ->assertOk();
    }

    public function test_admin_can_verify_payment_and_get_customer_dashboard_message(): void
    {
        Storage::fake('local');
        $user = User::factory()->create();
        $payment = $this->paymentWithProof();
        Storage::disk('local')->put($payment->proof_path, 'private proof');

        $this->actingAs($user)->get(route('admin.payments.show', $payment))
            ->assertOk()
            ->assertSee('Lihat bukti pembayaran')
            ->assertSee('Konfirmasi lunas')
            ->assertSee('Tolak bukti');

        $this->patch(route('admin.payments.verify', $payment), [
            'status' => 'paid',
            'notes' => 'Transfer sesuai nominal.',
        ])->assertRedirect(route('admin.payments.show', $payment))
            ->assertSessionHas('success');

        $order = $payment->fresh()->order;
        $this->assertDatabaseHas('payments', ['id' => $payment->id, 'status' => 'paid']);
        $this->assertDatabaseHas('orders', ['id' => $order->id, 'payment_status' => 'paid']);
        $this->assertNotNull($order->customer_access_code);
        $this->assertDatabaseHas('invoices', ['payment_id' => $payment->id]);

        $this->get(route('admin.orders.show', $order))
            ->assertOk()
            ->assertSee($order->customer_access_code)
            ->assertSee(route('customer.login'))
            ->assertSee('Kirim kode & link ke WhatsApp', false)
            ->assertSee('wa.me/6281234567890', false)
            ->assertSee('Lihat template pesan');
    }

    public function test_rejected_transfer_proof_keeps_order_unpaid_and_does_not_issue_access_code(): void
    {
        $user = User::factory()->create();
        $payment = $this->paymentWithProof();

        $this->actingAs($user)->patch(route('admin.payments.verify', $payment), [
            'status' => 'failed',
            'notes' => 'Nominal tidak sesuai.',
        ])->assertRedirect(route('admin.payments.show', $payment));

        $this->assertDatabaseHas('orders', [
            'id' => $payment->order_id,
            'payment_status' => 'unpaid',
            'customer_access_code' => null,
        ]);
        $this->assertDatabaseHas('payments', ['id' => $payment->id, 'status' => 'failed']);
    }

    public function test_paid_simple_order_does_not_receive_dashboard_access_code(): void
    {
        $user = User::factory()->create();
        $package = ServicePackage::create(['name' => 'Simple', 'price' => 59000, 'is_active' => true]);
        $order = Order::factory()->create([
            'service_package_id' => $package->id,
            'package' => $package->name,
            'total' => $package->price,
        ]);
        $bca = PaymentMethod::query()->where('code', 'bca')->firstOrFail();
        $payment = Payment::factory()->for($order)->for($bca, 'paymentMethod')->create([
            'transaction_number' => 'PAY-SIMPLE-001',
            'proof_path' => 'payment-proofs/simple.png',
            'proof_submitted_at' => now(),
        ]);

        $this->actingAs($user)->patch(route('admin.payments.verify', $payment), [
            'status' => 'paid',
        ])->assertRedirect();

        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'payment_status' => 'paid',
            'customer_access_code' => null,
        ]);
    }

    private function eligibleOrder(): Order
    {
        $package = ServicePackage::create([
            'name' => 'Intimate',
            'price' => 99000,
            'is_active' => true,
        ]);

        return Order::factory()->create([
            'uuid' => (string) Str::uuid(),
            'service_package_id' => $package->id,
            'package' => $package->name,
            'total' => $package->price,
            'customer_name' => 'Nadia Customer',
            'phone' => '+62 812-3456-7890',
        ]);
    }

    private function paymentWithProof(): Payment
    {
        $order = $this->eligibleOrder();
        $bca = PaymentMethod::query()->where('code', 'bca')->firstOrFail();

        return Payment::factory()->for($order)->for($bca, 'paymentMethod')->create([
            'transaction_number' => 'PAY-PROOF-001',
            'amount' => $order->total,
            'proof_path' => 'payment-proofs/receipt.png',
            'proof_submitted_at' => now(),
        ]);
    }
}
