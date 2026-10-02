<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('invitations', function (Blueprint $table): void {
            $table->string('customer_access_code', 20)->nullable()->unique()->after('status');
        });

        DB::table('invitations')->orderBy('id')->eachById(function (object $invitation): void {
            do {
                $accessCode = Str::upper(Str::random(12));
            } while (DB::table('invitations')->where('customer_access_code', $accessCode)->exists());

            DB::table('invitations')->where('id', $invitation->id)->update(['customer_access_code' => $accessCode]);
        });

        Schema::table('invitation_guests', function (Blueprint $table): void {
            $table->timestamp('opened_at')->nullable()->after('token');
            $table->unsignedInteger('open_count')->default(0)->after('opened_at');
            $table->string('rsvp_status', 20)->default('pending')->after('open_count');
            $table->timestamp('rsvp_responded_at')->nullable()->after('rsvp_status');
            $table->index(['invitation_id', 'rsvp_status'], 'guests_invitation_rsvp_idx');
        });
    }

    public function down(): void
    {
        Schema::table('invitation_guests', function (Blueprint $table): void {
            $table->dropIndex('guests_invitation_rsvp_idx');
            $table->dropColumn(['opened_at', 'open_count', 'rsvp_status', 'rsvp_responded_at']);
        });

        Schema::table('invitations', function (Blueprint $table): void {
            $table->dropUnique(['customer_access_code']);
            $table->dropColumn('customer_access_code');
        });
    }
};
