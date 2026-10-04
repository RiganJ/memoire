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

    public function test_one_shared_order_form_is_used_by_every_catalog(): void
    {
        $catalogs = Catalog::query()->where('status', 'active')->take(2)->get();
        $sharedForm = OrderFormTemplate::active();

        $this->assertDatabaseCount('order_form_templates', 1);
        foreach ($catalogs as $catalog) {
            $this->getJson(route('public.orders.form', $catalog))
                ->assertOk()
                ->assertJsonPath('template.id', $sharedForm->id)
                ->assertJsonPath('template.name', 'Form Pesanan Utama');
        }
    }

    public function test_new_catalog_uses_shared_form_without_creating_another_form(): void
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
        $this->getJson(route('public.orders.form', $catalog))
            ->assertOk()
            ->assertJsonPath('template.id', OrderFormTemplate::active()->id);
        $this->assertDatabaseCount('order_form_templates', 1);
    }

    public function test_admin_form_hides_field_codes_and_lists_active_payment_methods(): void
    {
        $user = User::factory()->create();
        $template = OrderFormTemplate::active();

        $this->actingAs($user)->get(route('admin.order-forms.edit', $template))
            ->assertOk()
            ->assertDontSee('Kode field')
            ->assertSee('Identitas teknis dibuat otomatis oleh sistem.')
            ->assertSee('Metode pembayaran')
            ->assertSee('Transfer Bank BCA')
            ->assertSee('DANA');
    }

    public function test_admin_updates_shared_form_and_field_codes_are_generated_automatically(): void
    {
        $user = User::factory()->create();
        $template = OrderFormTemplate::active();

        $this->actingAs($user)->put(route('admin.order-forms.update', $template), [
            'name' => $template->name,
            'fields' => [
                ['label' => 'Nama pasangan', 'type' => 'text', 'required' => '1', 'options' => ''],
                ['label' => 'Tema acara', 'type' => 'select', 'options' => "Klasik\nModern\nKlasik"],
                ['label' => 'Tema acara', 'type' => 'textarea', 'options' => ''],
            ],
        ])->assertRedirect()->assertSessionHas('success');

        $this->assertSame([
            ['key' => 'nama_pasangan', 'label' => 'Nama pasangan', 'type' => 'text', 'required' => true, 'options' => []],
            ['key' => 'tema_acara', 'label' => 'Tema acara', 'type' => 'select', 'required' => false, 'options' => ['Klasik', 'Modern']],
            ['key' => 'tema_acara_2', 'label' => 'Tema acara', 'type' => 'textarea', 'required' => false, 'options' => []],
        ], $template->fresh()->fields);
    }

    public function test_admin_update_preserves_existing_automatically_generated_field_code(): void
    {
        $user = User::factory()->create();
        $template = OrderFormTemplate::active();

        $this->actingAs($user)->put(route('admin.order-forms.update', $template), [
            'name' => $template->name,
            'fields' => [
                ['key' => 'event_name', 'label' => 'Judul acara', 'type' => 'text', 'required' => '1', 'options' => ''],
            ],
        ])->assertRedirect()->assertSessionHas('success');

        $this->assertSame('event_name', $template->fresh()->fields[0]['key']);
    }

    public function test_admin_cannot_save_select_field_without_options(): void
    {
        $user = User::factory()->create();
        $template = OrderFormTemplate::active();

        $this->actingAs($user)->from(route('admin.order-forms.edit', $template))->put(route('admin.order-forms.update', $template), [
            'name' => $template->name,
            'fields' => [['label' => 'Tema', 'type' => 'select', 'options' => '']],
        ])->assertUnprocessable();

        $this->assertSame(OrderFormTemplate::BASIC_FIELDS, $template->fresh()->fields);
    }

    public function test_admin_can_create_an_inactive_form_and_activate_it_for_all_checkouts(): void
    {
        $user = User::factory()->create();
        $activeForm = OrderFormTemplate::active();
        $catalog = Catalog::create([
            'name' => 'Arunika',
            'category' => 'Pernikahan',
            'package' => 'Timeless',
            'color' => '#582308',
            'status' => 'active',
        ]);

        $response = $this->actingAs($user)->post(route('admin.order-forms.store'), [
            'name' => 'Form Acara Intim',
            'description' => 'Detail acara yang lebih personal.',
            'fields' => [
                ['label' => 'Nama pasangan', 'type' => 'text', 'required' => '1', 'options' => ''],
            ],
        ])->assertRedirect();

        $newForm = OrderFormTemplate::query()->where('name', 'Form Acara Intim')->firstOrFail();
        $response->assertRedirect(route('admin.order-forms.show', $newForm));
        $this->assertDatabaseHas('order_form_templates', ['id' => $newForm->id, 'is_active' => false]);

        $this->patch(route('admin.order-forms.activate', $newForm))
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertDatabaseHas('order_form_templates', ['id' => $newForm->id, 'is_active' => true]);
        $this->assertDatabaseHas('order_form_templates', ['id' => $activeForm->id, 'is_active' => false]);
        $this->getJson(route('public.orders.form', $catalog))
            ->assertOk()
            ->assertJsonPath('template.id', $newForm->id)
            ->assertJsonPath('template.name', 'Form Acara Intim');
    }

    public function test_admin_can_read_and_update_a_form(): void
    {
        $user = User::factory()->create();
        $form = OrderFormTemplate::query()->create([
            'name' => 'Form Draft',
            'category' => OrderFormTemplate::SHARED_CATEGORY,
            'fields' => [['key' => 'event_name', 'label' => 'Nama acara', 'type' => 'text', 'required' => true, 'options' => []]],
        ]);

        $this->actingAs($user)->get(route('admin.order-forms.show', $form))
            ->assertOk()
            ->assertSee('Form Draft')
            ->assertSee('Nama acara');

        $this->put(route('admin.order-forms.update', $form), [
            'name' => 'Form Draft Revisi',
            'description' => 'Deskripsi yang diperbarui.',
            'fields' => [
                ['label' => 'Nama penyelenggara', 'type' => 'text', 'required' => '1', 'options' => ''],
            ],
        ])->assertRedirect(route('admin.order-forms.show', $form))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('order_form_templates', [
            'id' => $form->id,
            'name' => 'Form Draft Revisi',
            'description' => 'Deskripsi yang diperbarui.',
        ]);
        $this->assertSame('nama_penyelenggara', $form->fresh()->fields[0]['key']);
    }

    public function test_admin_can_delete_an_inactive_form(): void
    {
        $user = User::factory()->create();
        $form = OrderFormTemplate::query()->create([
            'name' => 'Form Lama',
            'category' => OrderFormTemplate::SHARED_CATEGORY,
            'fields' => OrderFormTemplate::BASIC_FIELDS,
        ]);

        $this->actingAs($user)->delete(route('admin.order-forms.destroy', $form))
            ->assertRedirect(route('admin.order-forms.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseMissing('order_form_templates', ['id' => $form->id]);
    }

    public function test_admin_cannot_delete_the_active_form(): void
    {
        $user = User::factory()->create();
        $activeForm = OrderFormTemplate::active();

        $this->actingAs($user)->delete(route('admin.order-forms.destroy', $activeForm))
            ->assertRedirect()
            ->assertSessionHas('error');

        $this->assertModelExists($activeForm);
    }
}
