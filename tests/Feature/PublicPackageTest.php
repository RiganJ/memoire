<?php

namespace Tests\Feature;

use App\Models\ServicePackage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicPackageTest extends TestCase
{
    use RefreshDatabase;

    public function test_returns_only_active_packages_in_admin_sort_order(): void
    {
        $secondPackage = ServicePackage::create([
            'name' => 'Signature',
            'price' => 179000,
            'description' => 'Paket lengkap.',
            'features' => ['RSVP', 'Galeri foto'],
            'badge' => 'Favorit',
            'sort_order' => 20,
            'is_active' => true,
        ]);
        $firstPackage = ServicePackage::create([
            'name' => 'Essential',
            'price' => 99000,
            'description' => 'Paket sederhana.',
            'features' => ['Musik latar'],
            'sort_order' => 10,
            'is_active' => true,
        ]);
        ServicePackage::create([
            'name' => 'Paket Nonaktif',
            'price' => 499000,
            'sort_order' => 1,
            'is_active' => false,
        ]);

        $this->getJson(route('public.packages.index'))
            ->assertOk()
            ->assertExactJson([
                [
                    'id' => $firstPackage->id,
                    'name' => 'Essential',
                    'price' => 99000,
                    'description' => 'Paket sederhana.',
                    'features' => ['Musik latar'],
                    'badge' => null,
                ],
                [
                    'id' => $secondPackage->id,
                    'name' => 'Signature',
                    'price' => 179000,
                    'description' => 'Paket lengkap.',
                    'features' => ['RSVP', 'Galeri foto'],
                    'badge' => 'Favorit',
                ],
            ]);
    }
}
