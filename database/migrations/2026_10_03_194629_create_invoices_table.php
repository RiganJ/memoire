<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('invoices', function (Blueprint $table): void {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('invoice_number', 32)->unique();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('payment_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('customer_name', 150);
            $table->string('customer_email');
            $table->string('package_name');
            $table->decimal('amount', 12, 2);
            $table->char('currency', 3)->default('IDR');
            $table->timestamp('paid_at');
            $table->string('email_status', 20)->default('pending');
            $table->timestamp('email_sent_at')->nullable();
            $table->unsignedSmallInteger('email_attempts')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};
