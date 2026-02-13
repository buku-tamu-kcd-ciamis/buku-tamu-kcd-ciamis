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
        Schema::table('pegawai_izin', function (Blueprint $table) {
            $table->string('nama_piket')->nullable()->after('status');
            $table->text('tanda_tangan_piket')->nullable()->after('nama_piket');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pegawai_izin', function (Blueprint $table) {
            $table->dropColumn(['nama_piket', 'tanda_tangan_piket']);
        });
    }
};
