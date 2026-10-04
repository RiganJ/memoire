<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $legacyPackages = [
            'Essential' => 'Simple',
            'Signature' => 'Intimate',
            'Bespoke' => 'Timeless',
        ];

        foreach ($legacyPackages as $legacyName => $currentName) {
            $currentPackageIsActive = DB::table('service_packages')
                ->where('name', $currentName)
                ->where('is_active', true)
                ->exists();

            if ($currentPackageIsActive) {
                DB::table('catalogs')
                    ->where('package', $legacyName)
                    ->update(['package' => $currentName]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $currentPackages = [
            'Simple' => 'Essential',
            'Intimate' => 'Signature',
            'Timeless' => 'Bespoke',
        ];

        foreach ($currentPackages as $currentName => $legacyName) {
            DB::table('catalogs')
                ->where('package', $currentName)
                ->update(['package' => $legacyName]);
        }
    }
};
