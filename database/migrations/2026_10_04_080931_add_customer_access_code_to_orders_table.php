<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table): void {
            $table->string('customer_access_code', 20)->nullable()->unique()->after('payment_status');
        });

        DB::table('orders')
            ->where('payment_status', 'paid')
            ->whereNull('customer_access_code')
            ->orderBy('id')
            ->each(function (object $order): void {
                $packageName = $order->package;
                if ($order->service_package_id !== null) {
                    $packageName = DB::table('service_packages')->where('id', $order->service_package_id)->value('name') ?? $packageName;
                }

                if (! in_array(mb_strtolower(trim($packageName)), ['intimate', 'timeless'], true)) {
                    return;
                }

                do {
                    $accessCode = 'MEMOIRE-'.Str::upper(Str::random(8));
                } while (
                    DB::table('orders')->where('customer_access_code', $accessCode)->exists()
                    || DB::table('invitations')->where('customer_access_code', $accessCode)->exists()
                );

                DB::table('orders')->where('id', $order->id)->update(['customer_access_code' => $accessCode]);
            });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table): void {
            $table->dropUnique(['customer_access_code']);
            $table->dropColumn('customer_access_code');
        });
    }
};
