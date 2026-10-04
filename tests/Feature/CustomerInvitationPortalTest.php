<?php

namespace Tests\Feature;

use App\Models\Invitation;
use App\Models\InvitationGuest;
use App\Models\Order;
use App\Models\ServicePackage;
use App\Models\Template;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class CustomerInvitationPortalTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_can_login_with_unique_invitation_code_and_logout(): void
    {
        $invitation = $this->createInvitation('rigan-salsa', 'RIGAN2026CODE');

        $this->get(route('customer.dashboard'))->assertRedirect(route('customer.login'));
        $this->post(route('customer.login.store'), ['access_code' => 'wrong-code'])->assertSessionHasErrors('access_code');
        $this->post(route('customer.login.store'), ['access_code' => strtolower($invitation->customer_access_code)])
            ->assertRedirect(route('customer.dashboard'))
            ->assertSessionHas('customer_invitation_id', $invitation->id);

        $this->get(route('customer.dashboard'))->assertOk()->assertSee('Rigan Salsa');
        $this->post(route('customer.logout'))->assertRedirect(route('customer.login'));
        $this->get(route('customer.dashboard'))->assertRedirect(route('customer.login'));
    }

    public function test_customer_pages_render_the_access_code_field_and_logout_confirmation(): void
    {
        $invitation = $this->createInvitation('rigan-salsa', 'RIGAN2026CODE');

        $this->get(route('customer.login'))
            ->assertOk()
            ->assertSee('for="access_code"', false)
            ->assertSee('placeholder="MEMOIRE-XXXXXXXX"', false);

        $this->withSession(['customer_invitation_id' => $invitation->id])
            ->get(route('customer.dashboard'))
            ->assertOk()
            ->assertSee('sweetalert2@11', false)
            ->assertSee('js-logout-form', false)
            ->assertSee('Keluar dari portal?');
    }

    public function test_paid_eligible_order_code_can_login_to_customer_order_dashboard(): void
    {
        $package = ServicePackage::create(['name' => 'Timeless', 'price' => 249000, 'is_active' => true]);
        $order = Order::factory()->create([
            'service_package_id' => $package->id,
            'package' => 'Timeless',
            'customer_name' => 'Nadia Memoire',
            'payment_status' => 'paid',
            'customer_access_code' => 'MEMOIRE-PAID1234',
        ]);

        $this->post(route('customer.login.store'), ['access_code' => 'memoire-paid1234'])
            ->assertRedirect(route('customer.dashboard'))
            ->assertSessionHas('customer_order_id', $order->id);

        $this->get(route('customer.dashboard'))
            ->assertOk()
            ->assertSee('Nadia Memoire')
            ->assertSee('Timeless')
            ->assertSee('MEMOIRE-PAID1234');
    }

    public function test_unpaid_or_simple_order_code_cannot_login_to_customer_dashboard(): void
    {
        $simple = ServicePackage::create(['name' => 'Simple', 'price' => 99000, 'is_active' => true]);
        Order::factory()->create([
            'service_package_id' => $simple->id,
            'package' => 'Simple',
            'payment_status' => 'paid',
            'customer_access_code' => 'MEMOIRE-SIMPLE01',
        ]);
        $timeless = ServicePackage::create(['name' => 'Timeless', 'price' => 249000, 'is_active' => true]);
        Order::factory()->create([
            'service_package_id' => $timeless->id,
            'package' => 'Timeless',
            'payment_status' => 'unpaid',
            'customer_access_code' => 'MEMOIRE-UNPAID01',
        ]);

        $this->post(route('customer.login.store'), ['access_code' => 'MEMOIRE-SIMPLE01'])
            ->assertSessionHasErrors('access_code');
        $this->post(route('customer.login.store'), ['access_code' => 'MEMOIRE-UNPAID01'])
            ->assertSessionHasErrors('access_code');
    }

    public function test_dashboard_statistics_and_guest_list_only_contain_logged_in_customer_data(): void
    {
        $first = $this->createInvitation('rigan-salsa', 'FIRSTCODE123');
        $second = $this->createInvitation('dimas-putri', 'SECONDCODE12');
        $first->guests()->createMany([
            ['name' => 'Budi', 'slug' => 'budi', 'phone' => '0811', 'opened_at' => now(), 'open_count' => 2, 'rsvp_status' => InvitationGuest::RSVP_ATTENDING],
            ['name' => 'Rina', 'slug' => 'rina', 'phone' => '0822', 'rsvp_status' => InvitationGuest::RSVP_DECLINED],
            ['name' => 'Andi', 'slug' => 'andi', 'phone' => '0833', 'rsvp_status' => InvitationGuest::RSVP_PENDING],
        ]);
        $second->guests()->create(['name' => 'Rahasia Customer Lain', 'slug' => 'rahasia', 'phone' => '0899']);

        $this->withSession(['customer_invitation_id' => $first->id])
            ->get(route('customer.dashboard'))
            ->assertOk()
            ->assertSeeInOrder(['3', 'Undangan dikirim'])
            ->assertSeeInOrder(['1', 'Undangan dibuka'])
            ->assertSee('Budi')
            ->assertSee('Rina')
            ->assertSee('Andi')
            ->assertDontSee('Rahasia Customer Lain');
    }

    public function test_customer_can_create_update_and_delete_only_their_own_guests(): void
    {
        $first = $this->createInvitation('rigan-salsa', 'FIRSTCODE123');
        $second = $this->createInvitation('dimas-putri', 'SECONDCODE12');
        $otherGuest = $second->guests()->create(['name' => 'Tamu Customer Lain', 'slug' => 'tamu-lain', 'phone' => '0812']);
        $session = ['customer_invitation_id' => $first->id];

        $this->withSession($session)->post(route('customer.guests.store'), [
            'name' => 'Budi & Partner',
            'phone' => '08123456789',
            'rsvp_status' => InvitationGuest::RSVP_ATTENDING,
        ])->assertRedirect(route('customer.dashboard'));

        $guest = $first->guests()->firstOrFail();
        $this->assertSame('budi-partner', $guest->slug);
        $this->assertNotNull($guest->token);
        $this->assertNotNull($guest->rsvp_responded_at);

        $this->withSession($session)->put(route('customer.guests.update', $guest), [
            'name' => 'Budi & Keluarga',
            'phone' => '08123456789',
            'rsvp_status' => InvitationGuest::RSVP_PENDING,
        ])->assertRedirect(route('customer.dashboard'));
        $this->assertDatabaseHas('invitation_guests', ['id' => $guest->id, 'slug' => 'budi-keluarga', 'rsvp_status' => 'pending']);

        $this->withSession($session)->get(route('customer.guests.edit', $otherGuest))->assertNotFound();
        $this->withSession($session)->delete(route('customer.guests.destroy', $otherGuest))->assertNotFound();
        $this->assertModelExists($otherGuest);

        $this->withSession($session)->delete(route('customer.guests.destroy', $guest))->assertRedirect(route('customer.dashboard'));
        $this->assertModelMissing($guest);
    }

    public function test_personal_invitation_visit_is_counted_as_opened(): void
    {
        Storage::fake('public');
        $invitation = $this->createInvitation('rigan-salsa', 'RIGAN2026CODE');
        Storage::disk('public')->put('invitations/'.$invitation->template->folder_name.'/index.html', '<html><body>{{guest_name}}</body></html>');
        $guest = $invitation->guests()->create(['name' => 'Budi', 'slug' => 'budi', 'phone' => '0812']);

        $parameters = ['invitationSlug' => $invitation->slug, 'guestSlug' => $guest->slug];
        $this->get(route('public.invitations.show', $parameters))->assertOk();
        $this->get(route('public.invitations.show', $parameters))->assertOk();

        $guest->refresh();
        $this->assertNotNull($guest->opened_at);
        $this->assertSame(2, $guest->open_count);
    }

    public function test_admin_can_regenerate_customer_access_code(): void
    {
        $admin = User::factory()->create();
        $invitation = $this->createInvitation('rigan-salsa', 'OLDCODE12345');

        $this->actingAs($admin)
            ->post(route('admin.invitations.regenerate-customer-code', $invitation))
            ->assertRedirect(route('admin.invitations.show', $invitation));

        $this->assertNotSame('OLDCODE12345', $invitation->refresh()->customer_access_code);
        $this->assertStringStartsWith('MEMOIRE-', $invitation->customer_access_code);
        $this->assertSame(16, strlen($invitation->customer_access_code));
    }

    private function createInvitation(string $slug, string $accessCode): Invitation
    {
        $template = Template::create([
            'name' => 'Customer Template '.$slug,
            'slug' => 'customer-template-'.$slug,
            'folder_name' => 'customer-template-'.$slug,
            'status' => 'published',
        ]);

        return Invitation::create([
            'template_id' => $template->id,
            'name' => str($slug)->replace('-', ' ')->title()->toString(),
            'slug' => $slug,
            'groom_name' => 'Pria',
            'bride_name' => 'Wanita',
            'status' => 'published',
            'customer_access_code' => $accessCode,
        ]);
    }
}
