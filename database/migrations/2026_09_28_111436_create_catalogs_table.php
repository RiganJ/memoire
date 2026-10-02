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
        Schema::create('catalogs', function (Blueprint $table): void {
            $table->id();
            $table->string('name', 120);
            $table->string('category', 80);
            $table->string('package', 30);
            $table->unsignedBigInteger('price');
            $table->string('color', 7);
            $table->string('image_path')->nullable();
            $table->string('status', 20)->default('draft');
            $table->timestamps();
        });

        $timestamp = now();

        DB::table('catalogs')->insert([
            ['name' => 'Arunika', 'category' => 'Elegan', 'package' => 'Signature', 'price' => 179000, 'color' => '#6b3520', 'status' => 'active', 'created_at' => $timestamp, 'updated_at' => $timestamp],
            ['name' => 'Serenada', 'category' => 'Floral', 'package' => 'Signature', 'price' => 179000, 'color' => '#a99a83', 'status' => 'active', 'created_at' => $timestamp, 'updated_at' => $timestamp],
            ['name' => 'Amorette', 'category' => 'Minimalis', 'package' => 'Essential', 'price' => 99000, 'color' => '#ddd0c0', 'status' => 'active', 'created_at' => $timestamp, 'updated_at' => $timestamp],
            ['name' => 'Kirana', 'category' => 'Floral', 'package' => 'Signature', 'price' => 179000, 'color' => '#74806c', 'status' => 'active', 'created_at' => $timestamp, 'updated_at' => $timestamp],
            ['name' => 'Naratama', 'category' => 'Elegan', 'package' => 'Bespoke', 'price' => 399000, 'color' => '#302723', 'status' => 'draft', 'created_at' => $timestamp, 'updated_at' => $timestamp],
            ['name' => 'Aluna', 'category' => 'Minimalis', 'package' => 'Essential', 'price' => 99000, 'color' => '#b9947b', 'status' => 'active', 'created_at' => $timestamp, 'updated_at' => $timestamp],
            ['name' => 'Senandika', 'category' => 'Corporate', 'package' => 'Bespoke', 'price' => 399000, 'color' => '#4d5350', 'status' => 'draft', 'created_at' => $timestamp, 'updated_at' => $timestamp],
            ['name' => 'Aurora', 'category' => 'Birthday', 'package' => 'Signature', 'price' => 179000, 'color' => '#927c8d', 'status' => 'archived', 'created_at' => $timestamp, 'updated_at' => $timestamp],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('catalogs');
    }
};
