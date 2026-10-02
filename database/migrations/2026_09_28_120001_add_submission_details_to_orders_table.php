<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table): void {
            $table->foreignId('customer_id')->nullable()->after('id')->constrained()->nullOnDelete();
            $table->foreignId('catalog_id')->nullable()->after('customer_id')->constrained()->nullOnDelete();
            $table->string('form_category')->nullable()->after('event_type');
            $table->json('form_data')->nullable()->after('form_category');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('catalog_id');
            $table->dropConstrainedForeignId('customer_id');
            $table->dropColumn(['form_category', 'form_data']);
        });
    }
};
