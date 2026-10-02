<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\Payment;
use App\Models\PaymentMethod;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminPaymentTest extends TestCase
{
    use RefreshDatabase;

    public function test_payment_pages_require_authentication(): void
    {
        $this->get(route('admin.payments.index'))->assertRedirect(route('admin.login'));
        $this->get(route('admin.payments.create'))->assertRedirect(route('admin.login'));
        $this->get(route('admin.payment-methods.create'))->assertRedirect(route('admin.login'));
    }

    public function test_bca_dana_and_gopay_are_available_and_editable(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->get(route('admin.payments.index'))
            ->assertOk()
            ->assertSee('Transfer Bank BCA')
            ->assertSee('DANA')
            ->assertSee('GoPay')
            ->assertSee('Arus pendapatan')
            ->assertSee('Ekspor');

        $dana = PaymentMethod::query()->where('code', 'dana')->firstOrFail();
        $this->actingAs($user)->put(route('admin.payment-methods.update', $dana), [
            'code' => 'dana', 'name' => 'DANA Bisnis', 'account_name' => 'Memoire Indonesia',
            'account_number' => '081234567890', 'instructions' => 'Kirim sesuai nominal transaksi.',
            'sort_order' => 5, 'is_active' => '1',
        ])->assertRedirect(route('admin.payments.index'));

        $this->assertDatabaseHas('payment_methods', [
            'id' => $dana->id, 'name' => 'DANA Bisnis', 'account_name' => 'Memoire Indonesia',
            'account_number' => '081234567890', 'is_active' => true,
        ]);
    }

    public function test_admin_can_create_update_view_and_delete_a_payment(): void
    {
        $user = User::factory()->create();
        $order = Order::factory()->create(['payment_status' => 'unpaid']);
        $bca = PaymentMethod::query()->where('code', 'bca')->firstOrFail();

        $this->actingAs($user)->post(route('admin.payments.store'), [
            'order_id' => $order->id, 'payment_method_id' => $bca->id, 'transaction_number' => '',
            'amount' => 179000, 'status' => 'success', 'paid_at' => '2026-10-03 10:30:00',
            'notes' => 'Transfer telah diverifikasi.',
        ])->assertRedirect();

        $payment = Payment::query()->firstOrFail();
        $this->assertSame('PAY-0001', $payment->transaction_number);
        $this->assertSame('paid', $order->fresh()->payment_status);

        $this->actingAs($user)->get(route('admin.payments.show', $payment))
            ->assertOk()->assertSee('PAY-0001')->assertSee($order->customer_name);

        $this->actingAs($user)->put(route('admin.payments.update', $payment), [
            'order_id' => $order->id, 'payment_method_id' => $bca->id, 'transaction_number' => 'PAY-0001',
            'amount' => 179000, 'status' => 'refunded', 'paid_at' => '2026-10-03 11:00:00',
            'notes' => 'Dana dikembalikan.',
        ])->assertRedirect(route('admin.payments.show', $payment));

        $this->assertDatabaseHas('payments', ['id' => $payment->id, 'status' => 'refunded']);
        $this->assertSame('refunded', $order->fresh()->payment_status);

        $this->actingAs($user)->delete(route('admin.payments.destroy', $payment))
            ->assertRedirect(route('admin.payments.index'));

        $this->assertModelMissing($payment);
        $this->assertSame('unpaid', $order->fresh()->payment_status);
    }

    public function test_payment_filters_and_csv_export_use_database_transactions(): void
    {
        $user = User::factory()->create();
        $order = Order::factory()->create(['customer_name' => 'Nadia Prameswari']);
        $gopay = PaymentMethod::query()->where('code', 'gopay')->firstOrFail();
        Payment::factory()->for($order)->for($gopay, 'paymentMethod')->create([
            'transaction_number' => 'PAY-SEARCH', 'status' => 'success', 'paid_at' => now(),
        ]);

        $this->actingAs($user)->get(route('admin.payments.index', ['search' => 'Nadia', 'status' => 'success']))
            ->assertOk()->assertSee('PAY-SEARCH');

        $this->actingAs($user)->get(route('admin.payments.index', ['period' => 12]))
            ->assertOk()->assertSee('value="12" selected', false);

        $this->actingAs($user)->get(route('admin.payments.export', ['search' => 'PAY-SEARCH']))
            ->assertOk()->assertDownload('laporan-pembayaran-'.now()->format('Y-m-d').'.csv');
    }

    public function test_used_payment_method_cannot_be_deleted(): void
    {
        $user = User::factory()->create();
        $method = PaymentMethod::query()->where('code', 'bca')->firstOrFail();
        Payment::factory()->for($method, 'paymentMethod')->create();

        $this->actingAs($user)->from(route('admin.payments.index'))->delete(route('admin.payment-methods.destroy', $method))
            ->assertRedirect(route('admin.payments.index'))->assertSessionHas('error');

        $this->assertModelExists($method);
    }

    public function test_payment_input_is_validated(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->from(route('admin.payments.create'))->post(route('admin.payments.store'), [
            'order_id' => 999999, 'payment_method_id' => 999999, 'amount' => -1, 'status' => 'invalid',
        ])->assertRedirect(route('admin.payments.create'))
            ->assertSessionHasErrors(['order_id', 'payment_method_id', 'amount', 'status']);

        $this->assertDatabaseCount('payments', 0);
    }

    public function test_successful_payment_requires_a_payment_date(): void
    {
        $user = User::factory()->create();
        $order = Order::factory()->create();
        $method = PaymentMethod::query()->where('code', 'bca')->firstOrFail();

        $this->actingAs($user)->from(route('admin.payments.create'))->post(route('admin.payments.store'), [
            'order_id' => $order->id,
            'payment_method_id' => $method->id,
            'amount' => 179000,
            'status' => 'success',
        ])->assertRedirect(route('admin.payments.create'))->assertSessionHasErrors('paid_at');

        $this->assertDatabaseCount('payments', 0);
    }
}
