<?php

namespace Tests\Feature;

use App\Models\Catalog;
use App\Models\Conversation;
use App\Models\Customer;
use App\Models\Order;
use App\Models\OrderFormTemplate;
use App\Models\ServicePackage;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Tests\TestCase;

class AdminDashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_from_admin_dashboard(): void
    {
        $this->get('/admin')->assertRedirect(route('admin.login'));
    }

    public function test_authenticated_user_can_view_admin_dashboard(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get('/admin')
            ->assertOk()
            ->assertSee('Selamat datang kembali.')
            ->assertSee('Pesanan terbaru')
            ->assertSee('aria-label="Navigasi admin mobile"', false)
            ->assertSee(route('admin.orders.index'), false)
            ->assertSee(route('admin.settings.edit'), false)
            ->assertSee(route('admin.logout'), false);
    }

    public function test_login_page_does_not_render_an_inactive_password_reset_link(): void
    {
        $this->get(route('admin.login'))
            ->assertOk()
            ->assertSee('Hubungi pengelola akun jika lupa')
            ->assertDontSee('href="#"', false);
    }

    public function test_authenticated_user_can_view_orders_page(): void
    {
        $user = User::factory()->create();
        Order::create([
            'order_number' => 'INV-0241',
            'customer_name' => 'Nadia & Arka',
            'email' => 'nadia@example.com',
            'package' => 'Signature',
            'event_type' => 'Pernikahan',
            'total' => 179000,
            'status' => 'process',
            'payment_status' => 'paid',
        ]);

        $this->actingAs($user)
            ->get('/admin/pesanan')
            ->assertOk()
            ->assertSee('Semua pesanan')
            ->assertSee('INV-0241');
    }

    public function test_admin_can_create_update_and_delete_an_order(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->post(route('admin.orders.store'), [
            'order_number' => 'INV-1001',
            'customer_name' => 'Diana Righan',
            'email' => 'diana@example.com',
            'phone' => '+62 812 3456 7890',
            'package' => 'Signature',
            'event_type' => 'Pernikahan',
            'total' => 179000,
            'event_date' => '2026-12-20',
            'status' => 'waiting',
            'payment_status' => 'unpaid',
            'notes' => 'Gunakan tema hangat.',
        ])->assertRedirect();

        $order = Order::where('order_number', 'INV-1001')->firstOrFail();
        $this->assertSame('Diana Righan', $order->customer_name);

        $this->actingAs($user)->put(route('admin.orders.update', $order), [
            'order_number' => 'INV-1001',
            'customer_name' => 'Diana Righan',
            'email' => 'diana@example.com',
            'phone' => '+62 812 3456 7890',
            'package' => 'Bespoke',
            'event_type' => 'Pernikahan',
            'total' => 399000,
            'event_date' => '2026-12-20',
            'status' => 'process',
            'payment_status' => 'paid',
            'notes' => 'Data sudah lengkap.',
        ])->assertRedirect(route('admin.orders.show', $order));

        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'package' => 'Bespoke',
            'status' => 'process',
            'payment_status' => 'paid',
        ]);

        $this->actingAs($user)
            ->delete(route('admin.orders.destroy', $order))
            ->assertRedirect(route('admin.orders.index'));

        $this->assertDatabaseMissing('orders', ['id' => $order->id]);
    }

    public function test_order_input_is_validated(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->from(route('admin.orders.create'))
            ->post(route('admin.orders.store'), [
                'customer_name' => '',
                'email' => 'bukan-email',
                'package' => 'Paket Palsu',
                'event_type' => '',
                'total' => -1,
                'status' => 'status-palsu',
                'payment_status' => 'status-palsu',
            ])
            ->assertRedirect(route('admin.orders.create'))
            ->assertSessionHasErrors(['customer_name', 'email', 'package', 'event_type', 'total', 'status', 'payment_status']);

        $this->assertDatabaseCount('orders', 0);
    }

    public function test_guest_can_start_and_continue_a_chat_with_a_persistent_token(): void
    {
        $token = (string) Str::uuid();

        $this->postJson('/live-chat/conversations', [
            'guest_token' => $token,
            'guest_name' => 'Rigan',
            'guest_phone' => '+62 812 3456 7890',
            'message' => 'Apakah bisa custom tema?',
        ])->assertCreated()
            ->assertJsonPath('ticket_number', 'CH-0001')
            ->assertJsonPath('messages.0.sender_type', 'guest');

        $this->postJson("/live-chat/conversations/{$token}/messages", [
            'message' => 'Saya ingin melihat paket Signature.',
        ])->assertOk()
            ->assertJsonCount(2, 'messages');

        $this->getJson("/live-chat/conversations/{$token}")
            ->assertOk()
            ->assertJsonPath('guest_name', 'Rigan')
            ->assertJsonCount(2, 'messages');

        $this->patchJson("/live-chat/conversations/{$token}/close")
            ->assertOk()
            ->assertJsonPath('status', 'closed');

        $newToken = (string) Str::uuid();
        $this->postJson('/live-chat/conversations', [
            'guest_token' => $newToken,
            'guest_name' => 'Rigan',
            'message' => 'Saya ingin memulai percakapan baru.',
        ])->assertCreated()
            ->assertJsonPath('ticket_number', 'CH-0002');

        $this->assertDatabaseHas('conversations', ['guest_token' => $token, 'ticket_number' => 'CH-0001', 'status' => 'closed']);
        $this->assertDatabaseCount('chat_messages', 3);
    }

    public function test_guest_is_told_their_queue_position_when_a_chat_is_already_waiting(): void
    {
        $this->postJson('/live-chat/conversations', [
            'guest_token' => (string) Str::uuid(),
            'guest_name' => 'Nadia',
            'message' => 'Halo',
        ])->assertCreated()->assertJsonPath('queue_position', 1);

        $this->postJson('/live-chat/conversations', [
            'guest_token' => (string) Str::uuid(),
            'guest_name' => 'Rigan',
            'message' => 'Saya ingin bertanya paket.',
        ])->assertCreated()
            ->assertJsonPath('queue_position', 2)
            ->assertJsonPath('messages.1.sender_type', 'admin')
            ->assertJsonPath('messages.1.message', 'Saat ini terdapat antrean. Anda berada di urutan ke-2. Mohon menunggu, kami akan segera membantu.');
    }

    public function test_admin_can_reply_to_and_close_a_guest_chat_ticket(): void
    {
        $user = User::factory()->create();
        $token = (string) Str::uuid();

        $this->postJson('/live-chat/conversations', [
            'guest_token' => $token,
            'guest_name' => 'Rigan',
            'message' => 'Halo Memoire',
        ]);

        $conversation = Conversation::firstOrFail();
        $nextConversation = Conversation::create([
            'guest_token' => (string) Str::uuid(),
            'ticket_number' => 'CH-0002',
            'guest_name' => 'Nadia',
            'status' => 'waiting',
            'last_message_at' => now(),
        ]);

        $this->actingAs($user)
            ->get(route('admin.chats.index'))
            ->assertOk()
            ->assertSee('Antrian aktif')
            ->assertSee('Rigan');

        $this->actingAs($user)
            ->post(route('admin.chats.reply', $conversation), ['message' => 'Tentu, kami bisa membantu.'])
            ->assertRedirect();

        $this->assertDatabaseHas('chat_messages', ['conversation_id' => $conversation->id, 'sender_type' => 'admin']);

        $this->actingAs($user)
            ->patch(route('admin.chats.status', $conversation), ['status' => 'closed'])
            ->assertRedirect(route('admin.chats.show', $nextConversation));

        $this->assertDatabaseHas('conversations', ['id' => $conversation->id, 'status' => 'closed']);
    }

    public function test_authenticated_user_can_view_catalog_page(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get('/admin/katalog')
            ->assertOk()
            ->assertSee('Koleksi Memoire')
            ->assertSee('Arunika');
    }

    public function test_guest_is_redirected_from_catalog_pages(): void
    {
        $this->get(route('admin.catalog.index'))
            ->assertRedirect(route('admin.login'));
    }

    public function test_admin_can_create_update_and_delete_a_catalog_design(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('admin.catalog.create'))
            ->assertSee('Tambah Desain')
            ->assertDontSee('name="price"', false);

        $this->actingAs($user)->post(route('admin.catalog.store'), [
            'name' => 'Maheswari',
            'category' => 'Tradisional',
            'package' => 'Signature',
            'color' => '#82604a',
            'link' => 'https://memoire.test/desain/maheswari',
            'status' => 'draft',
        ])->assertRedirect(route('admin.catalog.index'));

        $catalog = Catalog::where('name', 'Maheswari')->firstOrFail();
        $this->assertModelExists($catalog);

        $this->actingAs($user)
            ->get(route('admin.catalog.edit', $catalog))
            ->assertSee('Edit Desain');

        $this->actingAs($user)->put(route('admin.catalog.update', $catalog), [
            'name' => 'Maheswari Baru',
            'category' => 'Tradisional',
            'package' => 'Bespoke',
            'color' => '#82604a',
            'link' => 'https://memoire.test/desain/maheswari-baru',
            'status' => 'active',
        ])->assertRedirect(route('admin.catalog.index'));

        $this->assertDatabaseHas('catalogs', [
            'id' => $catalog->id,
            'name' => 'Maheswari Baru',
            'package' => 'Bespoke',
            'status' => 'active',
            'link' => 'https://memoire.test/desain/maheswari-baru',
        ]);

        $this->actingAs($user)
            ->delete(route('admin.catalog.destroy', $catalog))
            ->assertRedirect(route('admin.catalog.index'));

        $this->assertDatabaseMissing('catalogs', ['id' => $catalog->id]);
    }

    public function test_catalog_rejects_invalid_input_without_creating_a_design(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->from(route('admin.catalog.create'))
            ->post(route('admin.catalog.store'), [
                'name' => '',
                'category' => 'Elegan',
                'package' => 'Paket Palsu',
                'color' => 'red',
                'link' => 'javascript:alert(1)',
                'status' => 'published',
            ])
            ->assertRedirect(route('admin.catalog.create'))
            ->assertSessionHasErrors(['name', 'package', 'color', 'link', 'status']);

        $this->assertDatabaseMissing('catalogs', ['name' => '']);
    }

    public function test_admin_can_upload_a_catalog_design_image(): void
    {
        Storage::fake('public');
        $user = User::factory()->create();

        $this->actingAs($user)->post(route('admin.catalog.store'), [
            'name' => 'Arunika Upload',
            'category' => 'Elegan',
            'package' => 'Signature',
            'color' => '#6b3520',
            'status' => 'active',
            'image' => UploadedFile::fake()->image('arunika.png', 600, 800),
        ])->assertRedirect(route('admin.catalog.index'));

        $catalog = Catalog::where('name', 'Arunika Upload')->firstOrFail();

        $this->assertNotNull($catalog->image_path);
        Storage::disk('public')->assertExists($catalog->image_path);
    }

    public function test_authenticated_user_can_view_customers_page(): void
    {
        $user = User::factory()->create();
        Customer::create([
            'name' => 'Nadia Prameswari',
            'email' => 'nadia@example.com',
            'segment' => 'active',
        ]);

        $this->actingAs($user)
            ->get('/admin/pelanggan')
            ->assertOk()
            ->assertSee('Semua pelanggan')
            ->assertSee('Nadia Prameswari');
    }

    public function test_admin_can_create_update_and_delete_a_customer(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->post(route('admin.customers.store'), [
            'name' => 'Rigan Pratama',
            'email' => 'rigan@example.com',
            'phone' => '+62 812 3456 7890',
            'segment' => 'new',
            'notes' => 'Memilih tema minimalis.',
        ])->assertRedirect();

        $customer = Customer::where('email', 'rigan@example.com')->firstOrFail();

        $this->actingAs($user)->put(route('admin.customers.update', $customer), [
            'name' => 'Rigan Pratama',
            'email' => 'rigan@example.com',
            'phone' => '+62 812 3456 7890',
            'segment' => 'vip',
            'notes' => 'Pelanggan prioritas.',
        ])->assertRedirect(route('admin.customers.show', $customer));

        $this->assertDatabaseHas('customers', ['id' => $customer->id, 'segment' => 'vip']);

        $this->actingAs($user)
            ->delete(route('admin.customers.destroy', $customer))
            ->assertRedirect(route('admin.customers.index'));

        $this->assertDatabaseMissing('customers', ['id' => $customer->id]);
    }

    public function test_admin_login_can_be_viewed(): void
    {
        $this->get('/login')
            ->assertOk()
            ->assertSee('Selamat datang.')
            ->assertSee('Masuk ke Dashboard');
    }

    public function test_admin_can_update_landing_page_settings(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('admin.settings.edit'))
            ->assertOk()
            ->assertSee('Pengaturan');

        $this->actingAs($user)
            ->put(route('admin.settings.update'), [
                'brand_name' => 'Memoire Studio',
                'brand_tagline' => 'Every detail matters',
                'whatsapp_number' => '628111234567',
                'instagram_url' => 'https://www.instagram.com/memoire.studio/',
                'chat_greeting' => 'Halo, ada yang bisa kami bantu hari ini?',
            ])
            ->assertRedirect();

        $this->assertSame('Memoire Studio', Setting::where('key', 'brand_name')->value('value'));
        $this->assertSame('628111234567', Setting::where('key', 'whatsapp_number')->value('value'));
    }

    public function test_admin_can_log_in_with_valid_strong_password(): void
    {
        $user = User::factory()->create([
            'email' => 'admin@memoire.id',
            'password' => 'Strong!Pass1',
        ]);

        $this->withSession([
            'admin_login_captcha_hash' => Hash::make('abc12'),
            'admin_login_captcha_issued_at' => now()->timestamp,
        ])->post('/login', [
            'email' => $user->email,
            'password' => 'Strong!Pass1',
            'captcha' => 'ABC12',
        ])->assertRedirect(route('admin.dashboard'));

        $this->assertAuthenticatedAs($user);
    }

    public function test_weak_password_is_rejected(): void
    {
        $this->from('/login')->post('/login', [
            'email' => 'admin@memoire.id',
            'password' => 'password',
        ])->assertSessionHasErrors('password');

        $this->assertGuest();
    }

    public function test_sql_injection_style_credentials_cannot_authenticate(): void
    {
        User::factory()->create([
            'email' => 'admin@memoire.id',
            'password' => 'Strong!Pass1',
        ]);

        $this->from('/login')->post('/login', [
            'email' => "admin@memoire.id' OR 1=1 --",
            'password' => "' OR 1=1 -- Aa1!",
        ])->assertSessionHasErrors();

        $this->assertGuest();
    }

    public function test_invalid_captcha_prevents_authentication(): void
    {
        User::factory()->create([
            'email' => 'admin@memoire.id',
            'password' => 'Strong!Pass1',
        ]);

        $this->withSession([
            'admin_login_captcha_hash' => Hash::make('abc12'),
            'admin_login_captcha_issued_at' => now()->timestamp,
        ])->from('/login')->post('/login', [
            'email' => 'admin@memoire.id',
            'password' => 'Strong!Pass1',
            'captcha' => 'WRONG',
        ])->assertSessionHasErrors('captcha');

        $this->assertGuest();
    }

    public function test_public_order_uses_catalog_category_form_and_persists_customer(): void
    {
        $catalog = Catalog::create([
            'name' => 'Arunika',
            'category' => 'Pernikahan',
            'package' => 'Signature',
            'color' => '#582308',
            'link' => 'https://memoire.test/desain/arunika',
            'status' => 'active',
        ]);
        ServicePackage::create([
            'name' => 'Signature',
            'price' => 179000,
            'sort_order' => 1,
            'is_active' => true,
        ]);
        OrderFormTemplate::create([
            'name' => 'Form Pernikahan',
            'category' => 'Pernikahan',
            'fields' => [
                ['key' => 'nama_acara', 'label' => 'Nama acara', 'type' => 'text', 'required' => true, 'options' => []],
            ],
        ]);

        $catalogResponse = $this->getJson(route('public.orders.catalogs'))
            ->assertOk()
            ->assertJsonFragment(['id' => $catalog->id, 'link' => 'https://memoire.test/desain/arunika']);
        $this->assertArrayNotHasKey('price', $catalogResponse->json('0'));

        $this->get(route('public.orders.form', $catalog))
            ->assertOk()
            ->assertJsonPath('template.name', 'Form Pernikahan');

        $this->postJson(route('public.orders.store'), [
            'catalog_id' => $catalog->id,
            'name' => 'Diana Righan',
            'email' => 'diana@example.com',
            'phone' => '08123456789',
            'answers' => ['nama_acara' => 'Diana & Rigan'],
        ])->assertCreated()->assertJsonPath('message', 'Pesanan Anda sudah kami terima.');

        $this->assertDatabaseHas('customers', ['email' => 'diana@example.com', 'name' => 'Diana Righan', 'total_orders' => 1]);
        $this->assertDatabaseHas('orders', ['catalog_id' => $catalog->id, 'event_type' => 'Pernikahan', 'total' => 179000]);
    }
}
