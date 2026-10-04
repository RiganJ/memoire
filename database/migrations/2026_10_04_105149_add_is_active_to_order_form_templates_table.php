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
        $activeFormId = DB::table('order_form_templates')
            ->orderByDesc('updated_at')
            ->orderByDesc('id')
            ->value('id');

        Schema::table('order_form_templates', function (Blueprint $table): void {
            $table->boolean('is_active')->default(false)->after('fields');
        });

        if ($activeFormId !== null) {
            DB::table('order_form_templates')->where('id', $activeFormId)->update(['is_active' => true]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('order_form_templates', function (Blueprint $table): void {
            $table->dropColumn('is_active');
        });
    }
};
