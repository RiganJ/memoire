<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('order_form_templates', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('category')->unique();
            $table->text('description')->nullable();
            $table->json('fields');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_form_templates');
    }
};
