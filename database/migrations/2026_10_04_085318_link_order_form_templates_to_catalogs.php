<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('order_form_templates', function (Blueprint $table): void {
            $table->dropUnique(['category']);
            $table->foreignId('catalog_id')->nullable()->unique()->after('id')->constrained()->cascadeOnDelete();
        });

        $fields = json_encode([
            ['key' => 'event_name', 'label' => 'Nama acara', 'type' => 'text', 'required' => true, 'options' => []],
            ['key' => 'event_date', 'label' => 'Tanggal acara', 'type' => 'date', 'required' => true, 'options' => []],
            ['key' => 'event_time', 'label' => 'Waktu acara', 'type' => 'text', 'required' => true, 'options' => []],
            ['key' => 'event_location', 'label' => 'Lokasi acara', 'type' => 'textarea', 'required' => true, 'options' => []],
            ['key' => 'opening_text', 'label' => 'Teks pembuka', 'type' => 'textarea', 'required' => false, 'options' => []],
            ['key' => 'additional_notes', 'label' => 'Catatan tambahan', 'type' => 'textarea', 'required' => false, 'options' => []],
        ], JSON_THROW_ON_ERROR);
        $timestamp = now();

        DB::table('catalogs')->orderBy('id')->each(function (object $catalog) use ($fields, $timestamp): void {
            DB::table('order_form_templates')->insert([
                'catalog_id' => $catalog->id,
                'name' => 'Form Pesanan '.$catalog->name,
                'category' => $catalog->category,
                'description' => 'Lengkapi detail dasar untuk desain '.$catalog->name.'.',
                'fields' => $fields,
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
            ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('order_form_templates')->whereNotNull('catalog_id')->delete();

        Schema::table('order_form_templates', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('catalog_id');
            $table->unique('category');
        });
    }
};
