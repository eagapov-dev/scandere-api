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
        Schema::table('comments', function (Blueprint $table) {
            // Make product_id nullable (for general questions from homepage)
            $table->unsignedBigInteger('product_id')->nullable()->change();

            // Add flag to show on homepage
            $table->boolean('show_on_homepage')->default(false)->after('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('comments', function (Blueprint $table) {
            $table->dropColumn('show_on_homepage');

            // Note: reversing nullable might fail if there are null values
            // $table->unsignedBigInteger('product_id')->nullable(false)->change();
        });
    }
};
