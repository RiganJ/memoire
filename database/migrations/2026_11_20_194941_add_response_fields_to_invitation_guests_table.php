<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('invitation_guests', function (Blueprint $table): void {
            $table->unsignedSmallInteger('party_size')->nullable()->after('rsvp_responded_at');
            $table->text('wish_message')->nullable()->after('party_size');
            $table->timestamp('wish_submitted_at')->nullable()->after('wish_message');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('invitation_guests', function (Blueprint $table): void {
            $table->dropColumn(['party_size', 'wish_message', 'wish_submitted_at']);
        });
    }
};
