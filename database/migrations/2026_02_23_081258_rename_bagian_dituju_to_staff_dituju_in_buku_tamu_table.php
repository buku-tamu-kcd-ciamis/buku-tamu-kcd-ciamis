<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Transfer data from bagian_dituju to staff_dituju if staff_dituju is null
        \Illuminate\Support\Facades\DB::update("UPDATE buku_tamu SET staff_dituju = bagian_dituju WHERE staff_dituju IS NULL OR staff_dituju = ''");

        // 2. Drop the old column
        Schema::table('buku_tamu', function (Blueprint $table) {
            $table->dropColumn('bagian_dituju');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('buku_tamu', function (Blueprint $table) {
            $table->string('bagian_dituju')->nullable();
        });

        \Illuminate\Support\Facades\DB::update("UPDATE buku_tamu SET bagian_dituju = staff_dituju");
    }
};
