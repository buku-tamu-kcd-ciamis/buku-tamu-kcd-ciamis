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
        // First, truncate the table to avoid data type mismatch issues
        DB::table('activity_log')->truncate();

        Schema::table('activity_log', function (Blueprint $table) {
            // Change causer_id from bigInteger to uuid (string)
            $table->uuid('causer_id')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Truncate the table to avoid data type mismatch issues
        DB::table('activity_log')->truncate();

        Schema::table('activity_log', function (Blueprint $table) {
            // Revert causer_id back to unsignedBigInteger
            $table->unsignedBigInteger('causer_id')->nullable()->change();
        });
    }
};
