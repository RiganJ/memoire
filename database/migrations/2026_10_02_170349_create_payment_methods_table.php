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
        Schema::create('payment_methods', function (Blueprint $table) {
            $table->id();
            $table->string('code', 30)->unique();
            $table->string('name', 80);
            $table->string('account_name', 120);
            $table->string('account_number', 80);
            $table->text('instructions')->nullable();
            $table->boolean('is_active')->default(true);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
        });

        $timestamp = now();
        DB::table('payment_methods')->insert([
            ['code' => 'bca', 'name' => 'Transfer Bank BCA', 'account_name' => 'Memoire', 'account_number' => '0000000000', 'instructions' => 'Transfer ke rekening BCA lalu simpan bukti pembayaran.', 'is_active' => true, 'sort_order' => 10, 'created_at' => $timestamp, 'updated_at' => $timestamp],
            ['code' => 'dana', 'name' => 'DANA', 'account_name' => 'Memoire', 'account_number' => '080000000000', 'instructions' => 'Kirim pembayaran melalui akun DANA yang tertera.', 'is_active' => true, 'sort_order' => 20, 'created_at' => $timestamp, 'updated_at' => $timestamp],
            ['code' => 'gopay', 'name' => 'GoPay', 'account_name' => 'Memoire', 'account_number' => '080000000000', 'instructions' => 'Kirim pembayaran melalui akun GoPay yang tertera.', 'is_active' => true, 'sort_order' => 30, 'created_at' => $timestamp, 'updated_at' => $timestamp],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payment_methods');
    }
};
