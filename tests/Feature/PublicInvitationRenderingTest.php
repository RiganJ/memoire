<?php

namespace Tests\Feature;

use App\Models\Invitation;
use App\Models\Template;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PublicInvitationRenderingTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_url_renders_the_matching_guest_and_invitation_placeholders(): void
    {
        Storage::fake('public');
        [$invitation] = $this->createPublishedInvitation();
        $invitation->guests()->create(['name' => 'Budi & Partner', 'slug' => 'budi']);
        $invitation->guests()->create(['name' => 'Andi Pratama', 'slug' => 'andi-pratama']);

        $this->get('/rigan-salsa/budi')
            ->assertOk()
            ->assertSee('KEPADA YTH.')
            ->assertSee('Budi &amp; Partner', false)
            ->assertSee('Rigan &amp; Salsa', false)
            ->assertSee('Rigan')
            ->assertSee('Salsa')
            ->assertDontSee('Andi Pratama');

        $this->get('/rigan-salsa/andi-pratama')
            ->assertOk()
            ->assertSee('Andi Pratama')
            ->assertDontSee('Budi &amp; Partner', false);
    }

    public function test_invitation_url_without_guest_uses_default_guest_name(): void
    {
        Storage::fake('public');
        $this->createPublishedInvitation();

        $this->get('/rigan-salsa')
            ->assertOk()
            ->assertSee('KEPADA YTH.')
            ->assertSee('Tamu Undangan');
    }

    public function test_multiple_invitations_share_one_template_with_different_data(): void
    {
        Storage::fake('public');
        [$firstInvitation, $template] = $this->createPublishedInvitation();
        $firstInvitation->guests()->create(['name' => 'Budi', 'slug' => 'budi']);
        $secondInvitation = Invitation::create([
            'template_id' => $template->id,
            'name' => 'Dimas & Putri',
            'slug' => 'dimas-putri',
            'groom_name' => 'Dimas',
            'bride_name' => 'Putri',
            'status' => 'published',
        ]);
        $secondInvitation->guests()->create(['name' => 'Rina', 'slug' => 'rina']);

        $this->get('/dimas-putri/rina')
            ->assertOk()
            ->assertSee('Rina')
            ->assertSee('Dimas &amp; Putri', false)
            ->assertDontSee('Rigan &amp; Salsa', false);

        $this->assertDatabaseCount('templates', 1);
        Storage::disk('public')->assertExists('invitations/midnight-blossom/index.html');
        Storage::disk('public')->assertMissing('invitations/midnight-blossom/rina/index.html');
    }

    public function test_every_installed_template_renders_the_guest_name_on_its_personal_url(): void
    {
        $templateFolders = [
            'midnight-blossom',
            'sweetseventeen',
            'the-gilded-vow',
            'the-love-echo',
            'vintage-vows',
            'wedding-invitation',
        ];

        foreach ($templateFolders as $index => $templateFolder) {
            $template = Template::create([
                'name' => str($templateFolder)->headline(),
                'slug' => $templateFolder,
                'folder_name' => $templateFolder,
                'status' => 'published',
            ]);
            $invitation = Invitation::create([
                'template_id' => $template->id,
                'name' => 'Rigan & Salsa',
                'slug' => 'personal-invitation-'.$index,
                'groom_name' => 'Rigan',
                'bride_name' => 'Salsa',
                'status' => 'published',
            ]);
            $invitation->guests()->create([
                'name' => 'Tamu Personal '.$index,
                'slug' => 'tamu-personal',
            ]);

            $this->get('/personal-invitation-'.$index.'/tamu-personal')
                ->assertOk()
                ->assertSee('Tamu Personal '.$index)
                ->assertDontSee('{{guest_name}}', false);
        }
    }

    public function test_renderer_escapes_database_values_and_generates_asset_path(): void
    {
        Storage::fake('public');
        [$invitation] = $this->createPublishedInvitation();
        $invitation->guests()->create([
            'name' => '<script>alert("guest")</script>',
            'slug' => 'unsafe',
        ]);

        $this->get('/rigan-salsa/unsafe')
            ->assertOk()
            ->assertSee('&lt;script&gt;alert(&quot;guest&quot;)&lt;/script&gt;', false)
            ->assertDontSee('<script>alert("guest")</script>', false)
            ->assertSee('/invitation-assets/midnight-blossom/css/style.css', false)
            ->assertSee('href="/rigan-salsa/unsafe#details"', false)
            ->assertSee('<base href="/invitation-assets/midnight-blossom/">', false);
    }

    public function test_template_assets_are_served_without_a_public_storage_symlink(): void
    {
        Storage::fake('public');
        $this->createPublishedInvitation();

        $this->get('/invitation-assets/midnight-blossom/css/style.css')
            ->assertOk()
            ->assertHeader('Content-Type', 'text/css; charset=utf-8');

        $this->get('/invitation-assets/midnight-blossom/js/script.js')
            ->assertOk()
            ->assertHeader('Content-Type', 'text/javascript; charset=utf-8');

        $this->get('/invitation-assets/midnight-blossom/css/missing.css')
            ->assertNotFound();
    }

    public function test_unknown_guest_and_draft_invitation_return_404(): void
    {
        Storage::fake('public');
        [$invitation] = $this->createPublishedInvitation();

        $this->get('/rigan-salsa/tidak-ada')->assertNotFound();

        $invitation->update(['status' => 'draft']);
        $this->get('/rigan-salsa')->assertNotFound();
    }

    public function test_existing_application_routes_are_not_captured(): void
    {
        $this->get('/')->assertOk();
        $this->get('/login')->assertOk();
        $this->get('/admin')->assertRedirect(route('admin.login'));
        $this->get('/api')->assertNotFound();
        $this->get('/storage')->assertNotFound();
    }

    /** @return array{Invitation, Template} */
    private function createPublishedInvitation(): array
    {
        $template = Template::create([
            'name' => 'Midnight Blossom',
            'slug' => 'midnight-blossom',
            'folder_name' => 'midnight-blossom',
            'status' => 'published',
        ]);
        Storage::disk('public')->put('invitations/midnight-blossom/index.html', <<<'HTML'
<!doctype html><html><head><link rel="stylesheet" href="{{asset_path}}/css/style.css"><script src="js/script.js"></script></head><body><a href="#details">Detail</a><section id="details"></section><p>KEPADA YTH.</p><h1>{{guest_name}}</h1><p>{{invitation_name}}</p><span>{{groom_name}}</span><span>{{bride_name}}</span></body></html>
HTML);
        Storage::disk('public')->put('invitations/midnight-blossom/css/style.css', 'body { color: black; }');
        Storage::disk('public')->put('invitations/midnight-blossom/js/script.js', 'window.ready = true;');
        Storage::disk('public')->put('invitations/midnight-blossom/assets/images/hero.webp', 'image');
        Storage::disk('public')->put('invitations/midnight-blossom/assets/audio/song.mp3', 'audio');
        Storage::disk('public')->put('invitations/midnight-blossom/assets/video/intro.mp4', 'video');

        $invitation = Invitation::create([
            'template_id' => $template->id,
            'name' => 'Rigan & Salsa',
            'slug' => 'rigan-salsa',
            'groom_name' => 'Rigan',
            'bride_name' => 'Salsa',
            'status' => 'published',
        ]);

        return [$invitation, $template];
    }
}
