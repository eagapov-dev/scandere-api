<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // First add new columns
        Schema::table('comments', function (Blueprint $table) {
            $table->text('answer')->nullable()->after('body');
            $table->enum('status', ['draft', 'published'])->default('draft')->after('answer');
        });

        // Migrate existing data: is_approved = true → published, false → draft
        DB::table('comments')
            ->where('is_approved', true)
            ->update(['status' => 'published']);

        DB::table('comments')
            ->where('is_approved', false)
            ->update(['status' => 'draft']);

        // Then drop old column
        Schema::table('comments', function (Blueprint $table) {
            $table->dropColumn('is_approved');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // First add back old column
        Schema::table('comments', function (Blueprint $table) {
            $table->boolean('is_approved')->default(true)->after('body');
        });

        // Migrate data back: published → true, draft → false
        DB::table('comments')
            ->where('status', 'published')
            ->update(['is_approved' => true]);

        DB::table('comments')
            ->where('status', 'draft')
            ->update(['is_approved' => false]);

        // Then drop new columns
        Schema::table('comments', function (Blueprint $table) {
            $table->dropColumn(['answer', 'status']);
        });
    }
};
