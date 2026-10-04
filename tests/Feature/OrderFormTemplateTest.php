<?php

namespace Tests\Feature;

use App\Models\Catalog;
use App\Models\OrderFormTemplate;
use App\Models\ServicePackage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderFormTemplateTest extends TestCase
{
    use RefreshDatabase;

    public function test_every_existing_catalog_has_a_basic_order_form(): void
    {
        $catalogs = Catalog::query()->with('orderFormTemplate')->get();

        $this->assertNotEmpty($catalogs);
        foreach ($catalogs as $catalog) {
            $this->assertNotNull($catalog->orderFormTemplate, 'Form basic tidak ditemukan untuk '.$catalog->name);
            $this->assertSame($catalog->category, $catalog->orderFormTemplate->category);
            $this->assertCount(6, $catalog->orderFormTemplate->fields);
            $this->assertSame('event_name', $catalog->orderFormTemplate->fields[0]['key']);
        }
    }

    public function test_new_catalog_created_by_admin_automatically_receives_basic_form(): void
    {
        $user = User::factory()->create();
        ServicePackage::create(['name' => 'Timeless', 'price' => 249000, 'is_active' => true]);

        $this->actingAs($user)->post(route('admin.catalog.store'), [
            'name' => 'Senja Abadi',
            'category' => 'Pernikahan',
            'package' => 'Timeless',
            'color' => '#582308',
            'status' => 'active',
        ])->assertRedirect(route('admin.catalog.index'));

        $catalog = Catalog::query()->where('name', 'Senja Abadi')->firstOrFail();
        $this->assertDatabaseHas('order_form_templates', [
            'catalog_id' => $catalog->id,
            'name' => 'Form Pesanan Senja Abadi',
            'category' => 'Pernikahan',
        ]);
        $this->assertSame(OrderFormTemplate::BASIC_FIELDS, $catalog->orderFormTemplate->fields);
    }

    public function test_public_order_form_prefers_template_specific_form_over_category_fallback(): void
    {
        $catalog = Catalog::create([
            'name' => 'Arunika Khusus',
            'category' => 'Pernikahan',
            'package' => 'Timeless',
            'color' => '#582308',
            'status' => 'active',
        ]);
        OrderFormTemplate::create([
            'name' => 'Fallback Pernikahan',
            'category' => 'Pernikahan',
            'fields' => [['key' => 'fallback', 'label' => 'Fallback', 'type' => 'text', 'required' => true, 'options' => []]],
        ]);
        $specific = OrderFormTemplate::create([
            'catalog_id' => $catalog->id,
            'name' => 'Form Arunika Khusus',
            'category' => 'Pernikahan',
            'fields' => OrderFormTemplate::BASIC_FIELDS,
        ]);

        $this->getJson(route('public.orders.form', $catalog))
            ->assertOk()
            ->assertJsonPath('template.id', $specific->id)
            ->assertJsonPath('template.name', 'Form Arunika Khusus')
            ->assertJsonCount(6, 'template.fields');
    }
}
