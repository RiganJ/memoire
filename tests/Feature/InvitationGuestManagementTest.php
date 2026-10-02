<?php

namespace Tests\Feature;

use App\Models\Invitation;
use App\Models\Template;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InvitationGuestManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_create_update_and_delete_an_invitation_with_its_guests(): void
    {
        $user = User::factory()->create();
        $template = $this->createTemplate();

        $this->actingAs($user)->post(route('admin.invitations.store'), [
            'template_id' => $template->id,
            'name' => 'Rigan & Salsa',
            'slug' => 'rigan-salsa',
            'groom_name' => 'Rigan',
            'bride_name' => 'Salsa',
            'status' => 'published',
        ])->assertRedirect();

        $invitation = Invitation::where('slug', 'rigan-salsa')->firstOrFail();
        $this->actingAs($user)->post(route('admin.invitation-guests.store', $invitation), [
            'name' => 'Budi & Partner',
            'phone' => '08123456789',
        ])->assertRedirect(route('admin.invitations.show', $invitation));

        $guest = $invitation->guests()->firstOrFail();
        $this->assertNotNull($guest->token);
        $this->assertDatabaseHas('invitation_guests', ['invitation_id' => $invitation->id, 'slug' => 'budi-partner']);

        $this->actingAs($user)->put(route('admin.invitation-guests.update', [$invitation, $guest]), [
            'name' => 'Budi & Keluarga',
            'phone' => '08123456789',
        ])->assertRedirect(route('admin.invitations.show', $invitation));
        $this->assertDatabaseHas('invitation_guests', ['id' => $guest->id, 'slug' => 'budi-keluarga']);

        $this->actingAs($user)->delete(route('admin.invitations.destroy', $invitation))
            ->assertRedirect(route('admin.invitations.index'));
        $this->assertDatabaseMissing('invitations', ['id' => $invitation->id]);
        $this->assertDatabaseMissing('invitation_guests', ['id' => $guest->id]);
        $this->assertModelExists($template);
    }

    public function test_guest_slug_is_generated_from_name_and_made_unique_inside_an_invitation(): void
    {
        $user = User::factory()->create();
        $template = $this->createTemplate();
        $first = $this->createInvitation($template, 'rigan-salsa');
        $second = $this->createInvitation($template, 'dimas-putri');
        $first->guests()->create(['name' => 'Budi', 'slug' => 'budi']);

        $this->actingAs($user)->post(route('admin.invitation-guests.store', $first), [
            'name' => 'Budi',
            'phone' => '08123456789',
        ])->assertRedirect(route('admin.invitations.show', $first));

        $this->actingAs($user)->post(route('admin.invitation-guests.store', $second), [
            'name' => 'Budi',
            'phone' => '08123456789',
        ])->assertRedirect(route('admin.invitations.show', $second));

        $this->assertDatabaseHas('invitation_guests', ['invitation_id' => $first->id, 'slug' => 'budi-2']);
        $this->assertDatabaseHas('invitation_guests', ['invitation_id' => $second->id, 'slug' => 'budi']);
        $this->assertDatabaseCount('invitation_guests', 3);
    }

    public function test_guest_name_and_whatsapp_number_are_required(): void
    {
        $user = User::factory()->create();
        $template = $this->createTemplate();
        $invitation = $this->createInvitation($template, 'rigan-salsa');

        $this->actingAs($user)
            ->from(route('admin.invitation-guests.create', $invitation))
            ->post(route('admin.invitation-guests.store', $invitation), [])
            ->assertRedirect(route('admin.invitation-guests.create', $invitation))
            ->assertSessionHasErrors(['name', 'phone']);

        $this->assertDatabaseCount('invitation_guests', 0);
    }

    public function test_guest_form_only_asks_for_name_and_whatsapp_number(): void
    {
        $user = User::factory()->create();
        $template = $this->createTemplate();
        $invitation = $this->createInvitation($template, 'rigan-salsa');

        $this->actingAs($user)
            ->get(route('admin.invitation-guests.create', $invitation))
            ->assertSee('name="name"', false)
            ->assertSee('name="phone"', false)
            ->assertDontSee('name="slug"', false);
    }

    public function test_guest_list_has_bulk_whatsapp_with_each_personal_invitation_link(): void
    {
        $user = User::factory()->create();
        $template = $this->createTemplate();
        $invitation = $this->createInvitation($template, 'rigan-salsa');
        $invitation->guests()->create([
            'name' => 'Budi & Keluarga',
            'slug' => 'budi-keluarga',
            'phone' => '08123456789',
        ]);

        $this->actingAs($user)
            ->get(route('admin.invitations.show', $invitation))
            ->assertSee('Kirim Massal')
            ->assertSee('data-whatsapp-url="https://wa.me/628123456789?text=', false)
            ->assertSee('/rigan-salsa/budi-keluarga');
    }

    public function test_admin_can_add_multiple_guests_while_creating_an_invitation(): void
    {
        $user = User::factory()->create();
        $template = $this->createTemplate();

        $this->actingAs($user)->post(route('admin.invitations.store'), [
            'template_id' => $template->id,
            'name' => 'Rigan & Salsa',
            'slug' => 'rigan-salsa',
            'groom_name' => 'Rigan',
            'bride_name' => 'Salsa',
            'status' => 'published',
            'guests' => [
                ['name' => 'Budi & Keluarga', 'phone' => '08123456789'],
                ['name' => 'Budi & Keluarga', 'phone' => '08129876543'],
            ],
        ])->assertRedirect();

        $invitation = Invitation::where('slug', 'rigan-salsa')->firstOrFail();
        $this->assertDatabaseHas('invitation_guests', [
            'invitation_id' => $invitation->id,
            'name' => 'Budi & Keluarga',
            'phone' => '08123456789',
            'slug' => 'budi-keluarga',
        ]);
        $this->assertDatabaseHas('invitation_guests', [
            'invitation_id' => $invitation->id,
            'phone' => '08129876543',
            'slug' => 'budi-keluarga-2',
        ]);
    }

    public function test_guest_from_another_invitation_cannot_be_edited_through_nested_route(): void
    {
        $user = User::factory()->create();
        $template = $this->createTemplate();
        $first = $this->createInvitation($template, 'rigan-salsa');
        $second = $this->createInvitation($template, 'dimas-putri');
        $guest = $second->guests()->create(['name' => 'Rina', 'slug' => 'rina']);

        $this->actingAs($user)
            ->get(route('admin.invitation-guests.edit', [$first, $guest]))
            ->assertNotFound();
    }

    public function test_template_in_use_cannot_be_deleted(): void
    {
        $user = User::factory()->create();
        $template = $this->createTemplate();
        $this->createInvitation($template, 'rigan-salsa');

        $this->actingAs($user)
            ->from(route('admin.templates.index'))
            ->delete(route('admin.templates.destroy', $template))
            ->assertRedirect(route('admin.templates.index'))
            ->assertSessionHasErrors('template');

        $this->assertModelExists($template);
    }

    private function createTemplate(): Template
    {
        return Template::create([
            'name' => 'Midnight Blossom',
            'slug' => 'midnight-blossom',
            'folder_name' => 'midnight-blossom',
            'status' => 'published',
        ]);
    }

    private function createInvitation(Template $template, string $slug): Invitation
    {
        return Invitation::create([
            'template_id' => $template->id,
            'name' => str($slug)->replace('-', ' ')->title()->toString(),
            'slug' => $slug,
            'groom_name' => 'Nama Pria',
            'bride_name' => 'Nama Wanita',
            'status' => 'published',
        ]);
    }
}
