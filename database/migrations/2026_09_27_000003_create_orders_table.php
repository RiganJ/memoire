<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table): void {
            $table->id();
            $table->string('order_number')->unique();
            $table->string('customer_name');
            $table->string('email');
            $table->string('phone', 25)->nullable();
            $table->string('package');
            $table->string('event_type');
            $table->decimal('total', 12, 2)->default(0);
            $table->date('event_date')->nullable();
            $table->string('status')->default('waiting');
            $table->string('payment_status')->default('unpaid');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['status', 'payment_status']);
            $table->index('customer_name');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
