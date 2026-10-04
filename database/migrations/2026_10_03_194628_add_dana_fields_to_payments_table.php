<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payments', function (Blueprint $table): void {
            $table->uuid('uuid')->nullable()->unique()->after('id');
            $table->foreignId('service_package_id')->nullable()->after('payment_method_id')->constrained()->nullOnDelete();
            $table->string('partner_reference_no', 25)->nullable()->unique()->after('transaction_number');
            $table->string('dana_reference_no', 64)->nullable()->after('partner_reference_no');
            $table->char('currency', 3)->default('IDR')->after('amount');
            $table->text('qr_content')->nullable()->after('currency');
            $table->text('qr_url')->nullable()->after('qr_content');
            $table->longText('qr_image')->nullable()->after('qr_url');
            $table->timestamp('expires_at')->nullable()->after('status');
            $table->timestamp('failed_at')->nullable()->after('paid_at');
        });
    }

    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('service_package_id');
            $table->dropColumn(['uuid', 'partner_reference_no', 'dana_reference_no', 'currency', 'qr_content', 'qr_url', 'qr_image', 'expires_at', 'failed_at']);
        });
    }
};
