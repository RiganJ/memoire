<?php

namespace Tests\Feature;

// use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    public function test_homepage_renders_memoire_favicon_metadata(): void
    {
        $response = $this->get('/');

        $response->assertSee('rel="icon" type="image/png" sizes="96x96"', false)
            ->assertSee('/images/favicon-memoire.png', false)
            ->assertSee('rel="apple-touch-icon"', false)
            ->assertSee('/images/logo-memoire.png', false);
    }
}
