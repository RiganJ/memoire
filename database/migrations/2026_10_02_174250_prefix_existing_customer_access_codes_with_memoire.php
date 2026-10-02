<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('invitations')->orderBy('id')->eachById(function (object $invitation): void {
            do {
                $accessCode = 'MEMOIRE-'.Str::upper(Str::random(8));
            } while (DB::table('invitations')->where('customer_access_code', $accessCode)->exists());

            DB::table('invitations')->where('id', $invitation->id)->update([
                'customer_access_code' => $accessCode,
            ]);
        });
    }

    public function down(): void
    {
        DB::table('invitations')
            ->where('customer_access_code', 'like', 'MEMOIRE-%')
            ->orderBy('id')
            ->eachById(function (object $invitation): void {
                DB::table('invitations')->where('id', $invitation->id)->update([
                    'customer_access_code' => Str::after($invitation->customer_access_code, 'MEMOIRE-'),
                ]);
            });
    }
};
