<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('settings', function (Blueprint $table): void {
            $table->id();
            $table->string('key')->unique();
            $table->text('value')->nullable();
            $table->timestamps();
        });

        $now = now();
        DB::table('settings')->insert([
            ['key' => 'brand_name', 'value' => 'Memoire', 'created_at' => $now, 'updated_at' => $now],
            ['key' => 'brand_tagline', 'value' => 'A home for moments', 'created_at' => $now, 'updated_at' => $now],
            ['key' => 'whatsapp_number', 'value' => '6281372339347', 'created_at' => $now, 'updated_at' => $now],
            ['key' => 'instagram_url', 'value' => 'https://www.instagram.com/memoire.___/', 'created_at' => $now, 'updated_at' => $now],
            ['key' => 'chat_greeting', 'value' => 'Halo, ada yang bisa kami bantu?', 'created_at' => $now, 'updated_at' => $now],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('settings');
    }
};
