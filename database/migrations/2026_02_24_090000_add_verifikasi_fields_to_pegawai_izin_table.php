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
            $table->string('diverifikasi_oleh')->nullable()->after('tanda_tangan_piket');
            $table->timestamp('diverifikasi_pada')->nullable()->after('diverifikasi_oleh');
            $table->text('catatan_verifikasi')->nullable()->after('diverifikasi_pada');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pegawai_izin', function (Blueprint $table) {
            $table->dropColumn([
                'diverifikasi_oleh',
                'diverifikasi_pada',
                'catatan_verifikasi',
            ]);
        });
    }
};
