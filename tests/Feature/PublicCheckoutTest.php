<?php

namespace Tests\Feature;

use App\Models\Catalog;
use App\Models\OrderFormTemplate;
use App\Models\PaymentMethod;
use App\Models\ServicePackage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicCheckoutTest extends TestCase
{
    use RefreshDatabase;

    public function test_lists_only_active_payment_methods_for_checkout(): void
    {
        PaymentMethod::query()->where('code', 'gopay')->update(['is_active' => false]);

        $this->getJson(route('public.orders.payment-methods'))
            ->assertOk()
            ->assertJsonCount(2)
            ->assertJsonPath('0.code', 'bca')
            ->assertJsonPath('1.code', 'dana')
            ->assertJsonMissing(['code' => 'gopay']);
    }

    public function test_rejects_checkout_when_required_customer_fields_are_missing(): void
    {
        $this->postJson(route('public.orders.store'), [])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['catalog_id', 'package_id', 'name', 'phone', 'email', 'answers']);

        $this->assertDatabaseCount('orders', 0);
    }

    public function test_rejects_package_that_does_not_belong_to_selected_design(): void
    {
        [$catalog] = $this->checkoutSelection();
        $otherPackage = ServicePackage::create(['name' => 'Bespoke', 'price' => 999000, 'is_active' => true]);

        $this->postJson(route('public.orders.store'), $this->payload($catalog->id, $otherPackage->id))
            ->assertUnprocessable()
            ->assertJsonValidationErrors('package_id');

        $this->assertDatabaseCount('orders', 0);
    }

    public function test_creates_order_with_normalized_phone_and_database_price(): void
    {
        [$catalog, $package] = $this->checkoutSelection();
        $payload = $this->payload($catalog->id, $package->id) + ['amount' => 1, 'total' => 1];

        $response = $this->postJson(route('public.orders.store'), $payload)
            ->assertCreated()
            ->assertJsonPath('order.total', 249000)
            ->assertJsonPath('order.phone', '6281234567890');

        $this->assertDatabaseHas('orders', [
            'uuid' => $response->json('order.uuid'),
            'service_package_id' => $package->id,
            'total' => 249000,
            'phone' => '6281234567890',
            'payment_status' => 'unpaid',
        ]);
    }

    /** @return array{Catalog, ServicePackage} */
    private function checkoutSelection(): array
    {
        $package = ServicePackage::create(['name' => 'Timeless', 'price' => 249000, 'is_active' => true]);
        $catalog = Catalog::create(['name' => 'Arunika', 'category' => 'Wedding', 'package' => 'Timeless', 'color' => '#582308', 'status' => 'active']);
        OrderFormTemplate::active()->update(['fields' => [['key' => 'event_name', 'label' => 'Nama Acara', 'type' => 'text', 'required' => true, 'options' => []]]]);

        return [$catalog, $package];
    }

    /** @return array<string, mixed> */
    private function payload(int $catalogId, int $packageId): array
    {
        return [
            'catalog_id' => $catalogId,
            'package_id' => $packageId,
            'name' => 'Rigan Jevi',
            'phone' => '+62 812-3456-7890',
            'email' => 'rigan@example.com',
            'answers' => ['event_name' => 'Pernikahan Rigan'],
        ];
    }
}
