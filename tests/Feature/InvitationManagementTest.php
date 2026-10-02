<?php

namespace Tests\Feature;

use App\Models\Template;
use App\Models\User;
use App\Services\InvitationTemplateService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;
use ZipArchive;

class InvitationManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_upload_html_css_javascript_and_assets_separately(): void
    {
        Storage::fake('public');
        $user = User::factory()->create();
        $html = '<link rel="stylesheet" href="css/style.css"><script src="js/script.js"></script><img src="assets/foto.jpg"><video src="assets/video/section1.1.mp4"></video>';
        $css = 'body { color: teal; }';
        $javascript = 'window.templateLoaded = true;';
        $image = 'image-bytes';
        $video = 'mp4-video-bytes';

        $this->actingAs($user)->get(route('admin.templates.create'))
            ->assertOk()
            ->assertSee('name="index_html"', false)
            ->assertSee('name="css_files[]"', false)
            ->assertSee('name="js_files[]"', false)
            ->assertSee('webkitdirectory', false)
            ->assertSee('.mp4', false);

        $this->actingAs($user)->post(route('admin.templates.store'), [
            'name' => 'Sweet Seventeen',
            'slug' => 'sweet-seventeen',
            'status' => 'published',
            'index_html' => UploadedFile::fake()->createWithContent('index.html', $html),
            'css_files' => [UploadedFile::fake()->createWithContent('style.css', $css)],
            'js_files' => [UploadedFile::fake()->createWithContent('script.js', $javascript)],
            'asset_files' => [
                UploadedFile::fake()->createWithContent('foto.jpg', $image),
                UploadedFile::fake()->createWithContent('section1.1.mp4', $video),
            ],
            'asset_paths' => [
                'wedding-template/assets/photos/foto.jpg',
                'wedding-template/assets/video/section1.1.mp4',
            ],
        ])->assertRedirect(route('admin.templates.index'));

        Storage::disk('public')->assertExists('invitations/sweet-seventeen/index.html');
        Storage::disk('public')->assertExists('invitations/sweet-seventeen/css/style.css');
        Storage::disk('public')->assertExists('invitations/sweet-seventeen/js/script.js');
        Storage::disk('public')->assertExists('invitations/sweet-seventeen/assets/photos/foto.jpg');
        Storage::disk('public')->assertExists('invitations/sweet-seventeen/assets/video/section1.1.mp4');
        $this->assertSame($html, file_get_contents(Storage::disk('public')->path('invitations/sweet-seventeen/index.html')));
        $this->assertSame($css, file_get_contents(Storage::disk('public')->path('invitations/sweet-seventeen/css/style.css')));
        $this->assertSame($javascript, file_get_contents(Storage::disk('public')->path('invitations/sweet-seventeen/js/script.js')));
        $this->assertSame($image, file_get_contents(Storage::disk('public')->path('invitations/sweet-seventeen/assets/photos/foto.jpg')));
        $this->assertSame($video, file_get_contents(Storage::disk('public')->path('invitations/sweet-seventeen/assets/video/section1.1.mp4')));
        $invitation = Template::where('slug', 'sweet-seventeen')->firstOrFail();
        $this->actingAs($user)->put(route('admin.templates.update', $invitation), [
            'name' => 'Sweet Seventeen',
            'slug' => 'sweet-seventeen',
            'status' => 'draft',
            'index_html' => UploadedFile::fake()->createWithContent('index.html', '<h1>Updated invitation</h1>'),
            'css_files' => [UploadedFile::fake()->createWithContent('mobile.css', '@media (max-width: 600px) {}')],
        ])->assertRedirect(route('admin.templates.index'));

        Storage::disk('public')->assertExists('invitations/sweet-seventeen/css/mobile.css');
        $this->assertSame('<h1>Updated invitation</h1>', file_get_contents(Storage::disk('public')->path('invitations/sweet-seventeen/index.html')));
    }

    public function test_admin_can_create_preview_publish_and_serve_relative_template_assets(): void
    {
        Storage::fake('public');
        $user = User::factory()->create();
        $css = 'body { color: #582308; }';
        $script = 'window.invitationReady = true;';
        $image = 'image-bytes';
        $font = 'font-bytes';
        $audio = 'audio-bytes';

        $this->actingAs($user)->post(route('admin.templates.store'), [
            'name' => 'Wedding Rigan & Salsa',
            'slug' => 'Rigan Salsa',
            'template_zip' => $this->zipUpload([
                'index.html' => '<link rel="stylesheet" href="css/style.css"><img src="images/foto-1.jpg"><script src="js/script.js"></script>',
                'css/style.css' => $css,
                'js/script.js' => $script,
                'images/foto-1.jpg' => $image,
                'assets/fonts/wedding.woff2' => $font,
                'audio/wedding.mp3' => $audio,
            ]),
        ])->assertRedirect(route('admin.templates.index'));

        $invitation = Template::where('slug', 'rigan-salsa')->firstOrFail();
        $this->assertModelExists($invitation);
        $this->assertSame('rigan-salsa', $invitation->folder_name);
        $this->actingAs($user)->get(route('admin.templates.index'))
            ->assertOk()
            ->assertSee('Template Undangan')
            ->assertSee('Wedding Rigan &amp; Salsa', false);
        $this->assertDatabaseCount('orders', 0);

        Storage::disk('public')->assertExists('invitations/rigan-salsa/index.html');

        $this->actingAs($user)
            ->get(route('admin.templates.preview', $invitation))
            ->assertOk()
            ->assertSee('href="/invitation-assets/rigan-salsa/"', false)
            ->assertSee('href="css/style.css"', false);

        $this->actingAs($user)
            ->patch(route('admin.templates.publish', $invitation))
            ->assertRedirect(route('admin.templates.index'));

        $this->assertDatabaseHas('templates', ['id' => $invitation->id, 'status' => 'published']);
        $this->actingAs($user)
            ->patch(route('admin.templates.publish', $invitation))
            ->assertRedirect(route('admin.templates.index'));
        $this->assertDatabaseHas('templates', ['id' => $invitation->id, 'status' => 'draft']);
    }

    public function test_admin_can_rename_replace_and_manage_invitation_files(): void
    {
        Storage::fake('public');
        $user = User::factory()->create();

        $this->actingAs($user)->post(route('admin.templates.store'), [
            'name' => 'Wedding Rigan & Salsa',
            'slug' => 'rigan-salsa',
            'template_zip' => $this->zipUpload([
                'index.html' => '<h1>old template</h1>',
                'css/style.css' => 'body { color: black; }',
            ]),
        ])->assertRedirect(route('admin.templates.index'));

        $invitation = Template::where('slug', 'rigan-salsa')->firstOrFail();

        $this->actingAs($user)->put(route('admin.templates.update', $invitation), [
            'name' => 'Wedding Rigan Salsa',
            'slug' => 'rigan-salsa-wedding',
            'status' => 'draft',
            'asset_file' => UploadedFile::fake()->createWithContent('guestbook.json', '{"enabled":true}'),
        ])->assertRedirect(route('admin.templates.index'));

        $invitation->refresh();
        $this->assertSame('rigan-salsa-wedding', $invitation->slug);
        $this->assertSame('rigan-salsa-wedding', $invitation->folder_name);
        Storage::disk('public')->assertMissing('invitations/rigan-salsa');
        Storage::disk('public')->assertExists('invitations/rigan-salsa-wedding/index.html');
        Storage::disk('public')->assertExists('invitations/rigan-salsa-wedding/css/style.css');
        Storage::disk('public')->assertExists('invitations/rigan-salsa-wedding/guestbook.json');
        $this->assertSame('<h1>old template</h1>', file_get_contents(Storage::disk('public')->path('invitations/rigan-salsa-wedding/index.html')));

        $this->actingAs($user)->put(route('admin.templates.update', $invitation), [
            'name' => 'Wedding Rigan Salsa',
            'slug' => 'rigan-salsa-wedding',
            'status' => 'draft',
            'template_zip' => $this->zipUpload(['index.html' => '<h1>replacement template</h1>']),
            'asset_file' => UploadedFile::fake()->createWithContent('guestbook.json', '{"enabled":false}'),
        ])->assertRedirect(route('admin.templates.index'));

        $this->assertSame('<h1>replacement template</h1>', file_get_contents(Storage::disk('public')->path('invitations/rigan-salsa-wedding/index.html')));

        $this->actingAs($user)
            ->get(route('admin.templates.edit', $invitation))
            ->assertOk()
            ->assertSee('index.html')
            ->assertSee('guestbook.json');

        $this->actingAs($user)
            ->delete(route('admin.templates.files.destroy', $invitation), ['path' => 'guestbook.json'])
            ->assertRedirect(route('admin.templates.edit', $invitation));

        Storage::disk('public')->assertMissing('invitations/rigan-salsa-wedding/guestbook.json');
        Storage::disk('public')->assertExists('invitations/rigan-salsa-wedding/index.html');

        $this->actingAs($user)
            ->delete(route('admin.templates.destroy', $invitation))
            ->assertRedirect(route('admin.templates.index'));

        $this->assertDatabaseMissing('templates', ['id' => $invitation->id]);
        Storage::disk('public')->assertMissing('invitations/rigan-salsa-wedding');
    }

    public function test_reserved_and_duplicate_slugs_are_rejected(): void
    {
        Storage::fake('public');
        $user = User::factory()->create();
        $zip = $this->zipUpload(['index.html' => '<h1>Invitation</h1>']);

        $this->actingAs($user)
            ->from(route('admin.templates.create'))
            ->post(route('admin.templates.store'), [
                'name' => 'Reserved route',
                'slug' => 'admin',
                'template_zip' => $zip,
            ])
            ->assertRedirect(route('admin.templates.create'))
            ->assertSessionHasErrors('slug');

        $this->actingAs($user)->post(route('admin.templates.store'), [
            'name' => 'Wedding Rigan & Salsa',
            'slug' => 'rigan-salsa',
        ])->assertRedirect(route('admin.templates.index'));

        $this->actingAs($user)
            ->from(route('admin.templates.create'))
            ->post(route('admin.templates.store'), [
                'name' => 'Duplicate slug',
                'slug' => 'rigan-salsa',
            ])
            ->assertRedirect(route('admin.templates.create'))
            ->assertSessionHasErrors('slug');

        $this->assertDatabaseCount('templates', 1);
    }

    public function test_publishing_requires_a_root_index_file_and_failed_create_preserves_existing_folder(): void
    {
        Storage::fake('public');
        $user = User::factory()->create();
        Template::create([
            'name' => 'Draft without template',
            'slug' => 'draft-without-template',
            'folder_name' => 'draft-without-template',
            'status' => 'draft',
        ]);

        $this->actingAs($user)
            ->from(route('admin.templates.index'))
            ->patch(route('admin.templates.publish', Template::where('slug', 'draft-without-template')->firstOrFail()))
            ->assertRedirect(route('admin.templates.index'))
            ->assertSessionHasErrors('status');

        $this->assertDatabaseHas('templates', ['slug' => 'draft-without-template', 'status' => 'draft']);

        Storage::disk('public')->put('invitations/orphan-invitation/index.html', '<h1>Existing files</h1>');
        $this->actingAs($user)
            ->from(route('admin.templates.create'))
            ->post(route('admin.templates.store'), [
                'name' => 'Orphan Invitation',
                'slug' => 'orphan-invitation',
                'template_zip' => $this->zipUpload(['index.html' => '<h1>replacement</h1>']),
            ])
            ->assertRedirect(route('admin.templates.create'))
            ->assertSessionHasErrors('slug');

        $this->assertDatabaseMissing('templates', ['slug' => 'orphan-invitation']);
        $this->assertSame('<h1>Existing files</h1>', file_get_contents(Storage::disk('public')->path('invitations/orphan-invitation/index.html')));
    }

    public function test_zip_slip_and_disallowed_extensions_are_rejected_without_writing_outside_template_folder(): void
    {
        Storage::fake('public');
        $user = User::factory()->create();
        $diskRoot = Storage::disk('public')->path('');

        $this->actingAs($user)
            ->from(route('admin.templates.create'))
            ->post(route('admin.templates.store'), [
                'name' => 'Malicious invitation',
                'slug' => 'malicious-invitation',
                'template_zip' => $this->zipUpload([
                    'index.html' => '<h1>safe</h1>',
                    '../../.env' => 'APP_KEY=leaked',
                ]),
            ])
            ->assertRedirect(route('admin.templates.create'))
            ->assertSessionHasErrors('template_zip');

        $this->actingAs($user)
            ->from(route('admin.templates.create'))
            ->post(route('admin.templates.store'), [
                'name' => 'Executable asset',
                'slug' => 'executable-asset',
                'asset_file' => UploadedFile::fake()->createWithContent('payload.php', '<?php echo "unsafe";'),
            ])
            ->assertRedirect(route('admin.templates.create'))
            ->assertSessionHasErrors('asset_file');

        $this->assertFileDoesNotExist($diskRoot.'/.env');
        Storage::disk('public')->assertMissing('invitations/malicious-invitation/index.html');

        $this->actingAs($user)
            ->from(route('admin.templates.create'))
            ->post(route('admin.templates.store'), [
                'name' => 'Executable invitation',
                'slug' => 'executable-invitation',
                'template_zip' => $this->zipUpload([
                    'index.html' => '<h1>unsafe</h1>',
                    'payload.php' => '<?php echo "unsafe";',
                ]),
            ])
            ->assertRedirect(route('admin.templates.create'))
            ->assertSessionHasErrors('template_zip');
    }

    public function test_guest_cannot_access_draft_preview_or_invitation_management(): void
    {
        Storage::fake('public');
        $invitation = Template::create([
            'name' => 'Private invitation',
            'slug' => 'private-invitation',
            'folder_name' => 'private-invitation',
            'status' => 'draft',
        ]);

        $this->get(route('admin.templates.index'))
            ->assertRedirect(route('admin.login'));

        $this->get('/private-invitation')->assertNotFound();
        $this->get('/private-invitation/guest')->assertNotFound();
    }

    public function test_public_assets_cannot_escape_the_invitation_folder_or_follow_symlinks(): void
    {
        Storage::fake('public');
        Template::create([
            'name' => 'Published invitation',
            'slug' => 'published-invitation',
            'folder_name' => 'published-invitation',
            'status' => 'published',
        ]);

        Storage::disk('public')->put('secret.css', 'secret outside invitation');
        $assetDirectory = Storage::disk('public')->path('invitations/published-invitation/assets');
        mkdir($assetDirectory, 0755, true);
        symlink(Storage::disk('public')->path('secret.css'), $assetDirectory.'/escape.css');

        $templates = app(InvitationTemplateService::class);

        $this->assertNull($templates->resolveFile('published-invitation', 'assets/escape.css'));
        $this->assertNull($templates->resolveFile('published-invitation', '../../secret.css'));
    }

    public function test_template_preview_requires_authentication(): void
    {
        Storage::fake('public');
        Template::create([
            'name' => 'Public invitation',
            'slug' => 'public-invitation',
            'folder_name' => 'public-invitation',
            'status' => 'published',
        ]);
        Storage::disk('public')->put('invitations/public-invitation/index.html', '<h1>Public invitation</h1>');

        $this->get(route('admin.templates.preview', Template::where('slug', 'public-invitation')->firstOrFail()))
            ->assertRedirect(route('admin.login'));
    }

    public function test_existing_system_routes_are_not_captured_by_the_invitation_wildcard(): void
    {
        Storage::fake('public');

        $this->get('/')->assertOk();
        $this->get('/login')->assertOk()->assertSee('Masuk ke Dashboard');
        $this->get('/paket-harga')->assertOk()->assertJson([]);
        $this->get('/pemesanan/katalog')->assertOk();
        $this->get('/admin')->assertRedirect(route('admin.login'));
        $this->get('/admin/unknown')->assertNotFound();
    }

    /** @param array<string, string> $files */
    private function zipUpload(array $files): UploadedFile
    {
        $archivePath = tempnam(sys_get_temp_dir(), 'invitation-zip-');
        $archive = new ZipArchive;
        $archive->open($archivePath, ZipArchive::OVERWRITE);

        foreach ($files as $path => $contents) {
            $archive->addFromString($path, $contents);
        }

        $archive->close();
        $contents = file_get_contents($archivePath);
        unlink($archivePath);

        return UploadedFile::fake()->createWithContent('template.zip', $contents);
    }
}
