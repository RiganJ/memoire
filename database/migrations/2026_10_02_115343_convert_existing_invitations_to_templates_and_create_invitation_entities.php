<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('invitations', function (Blueprint $table): void {
            $table->dropUnique(['slug']);
            $table->dropUnique(['folder']);
            $table->dropIndex(['status']);
        });

        Schema::rename('invitations', 'templates');

        Schema::table('templates', function (Blueprint $table): void {
            $table->renameColumn('title', 'name');
            $table->renameColumn('folder', 'folder_name');
            $table->unique('slug', 'templates_slug_unique');
            $table->unique('folder_name', 'templates_folder_name_unique');
            $table->index('status', 'templates_status_index');
        });

        Schema::create('invitations', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('template_id')->constrained('templates')->restrictOnDelete();
            $table->string('name', 180);
            $table->string('slug', 100)->unique();
            $table->string('groom_name', 120);
            $table->string('bride_name', 120);
            $table->string('status', 20)->default('draft');
            $table->timestamps();

            $table->index(['status', 'created_at'], 'invitations_status_created_idx');
        });

        Schema::create('invitation_guests', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('invitation_id')->constrained()->cascadeOnDelete();
            $table->string('name', 180);
            $table->string('slug', 100);
            $table->string('phone', 25)->nullable();
            $table->string('token', 100)->nullable()->unique();
            $table->timestamps();

            $table->unique(['invitation_id', 'slug'], 'guests_invitation_slug_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invitation_guests');
        Schema::dropIfExists('invitations');

        $templateIndexes = collect(Schema::getIndexes('templates'))->pluck('name');

        Schema::table('templates', function (Blueprint $table) use ($templateIndexes): void {
            $table->dropUnique($templateIndexes->contains('templates_slug_unique')
                ? 'templates_slug_unique'
                : 'invitations_slug_unique');
            $table->dropUnique($templateIndexes->contains('templates_folder_name_unique')
                ? 'templates_folder_name_unique'
                : 'invitations_folder_unique');
            $table->dropIndex($templateIndexes->contains('templates_status_index')
                ? 'templates_status_index'
                : 'invitations_status_index');
            $table->renameColumn('name', 'title');
            $table->renameColumn('folder_name', 'folder');
            $table->unique('slug');
            $table->unique('folder');
            $table->index('status');
        });

        Schema::rename('templates', 'invitations');
    }
};
