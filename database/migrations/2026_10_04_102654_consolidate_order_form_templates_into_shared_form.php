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
        $tableName = 'order_form_templates';

        $sharedForm = DB::table($tableName)->orderByDesc('updated_at')->orderByDesc('id')->first();

        if ($sharedForm === null) {
            $sharedFormId = DB::table($tableName)->insertGetId([
                'catalog_id' => null,
                'name' => 'Form Pesanan Utama',
                'category' => 'Semua Template',
                'description' => 'Form yang digunakan oleh seluruh template katalog.',
                'fields' => json_encode([
                    ['key' => 'event_name', 'label' => 'Nama acara', 'type' => 'text', 'required' => true, 'options' => []],
                    ['key' => 'event_date', 'label' => 'Tanggal acara', 'type' => 'date', 'required' => true, 'options' => []],
                    ['key' => 'event_time', 'label' => 'Waktu acara', 'type' => 'text', 'required' => true, 'options' => []],
                    ['key' => 'event_location', 'label' => 'Lokasi acara', 'type' => 'textarea', 'required' => true, 'options' => []],
                    ['key' => 'opening_text', 'label' => 'Teks pembuka', 'type' => 'textarea', 'required' => false, 'options' => []],
                    ['key' => 'additional_notes', 'label' => 'Catatan tambahan', 'type' => 'textarea', 'required' => false, 'options' => []],
                ], JSON_THROW_ON_ERROR),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        } else {
            $sharedFormId = $sharedForm->id;
        }

        DB::table($tableName)->where('id', '!=', $sharedFormId)->delete();
        DB::table($tableName)->where('id', $sharedFormId)->update([
            'catalog_id' => null,
            'name' => 'Form Pesanan Utama',
            'category' => 'Semua Template',
            'description' => 'Form yang digunakan oleh seluruh template katalog.',
            'updated_at' => now(),
        ]);

        if (DB::getDriverName() === 'sqlite' && Schema::hasColumn($tableName, 'catalog_id')) {
            $backupTable = $tableName.'_backup';
            Schema::dropIfExists($backupTable);

            Schema::create($backupTable, function (Blueprint $table): void {
                $table->id();
                $table->string('name');
                $table->string('category');
                $table->text('description')->nullable();
                $table->json('fields');
                $table->timestamps();
            });

            $sharedRow = DB::table($tableName)->where('id', $sharedFormId)->first();
            if ($sharedRow !== null) {
                DB::table($backupTable)->insert([
                    'id' => $sharedRow->id,
                    'name' => $sharedRow->name,
                    'category' => $sharedRow->category,
                    'description' => $sharedRow->description,
                    'fields' => $sharedRow->fields,
                    'created_at' => $sharedRow->created_at,
                    'updated_at' => $sharedRow->updated_at,
                ]);
            }

            Schema::dropIfExists($tableName);
            Schema::rename($backupTable, $tableName);

            return;
        }

        Schema::table($tableName, function (Blueprint $table): void {
            $table->dropForeign(['catalog_id']);
            $table->dropUnique(['catalog_id']);
        });

        Schema::table($tableName, function (Blueprint $table): void {
            $table->dropColumn('catalog_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('order_form_templates', function (Blueprint $table): void {
            $table->foreignId('catalog_id')->nullable()->unique()->after('id')->constrained()->cascadeOnDelete();
        });
    }
};
