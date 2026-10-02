<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('catalogs') || Schema::hasColumn('catalogs', 'image_path')) {
            return;
        }

        Schema::table('catalogs', function (Blueprint $table): void {
            $table->string('image_path')->nullable()->after('color');
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('catalogs') || ! Schema::hasColumn('catalogs', 'image_path')) {
            return;
        }

        Schema::table('catalogs', function (Blueprint $table): void {
            $table->dropColumn('image_path');
        });
    }
};
