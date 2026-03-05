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
        // Use raw SQL to alter enum because Laravel's change() on enums is problematic without DBAL
        \Illuminate\Support\Facades\DB::statement("ALTER TABLE pegawai_izin MODIFY COLUMN status ENUM('menunggu', 'disetujui', 'ditolak', 'aktif', 'selesai') DEFAULT 'menunggu'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        \Illuminate\Support\Facades\DB::statement("ALTER TABLE pegawai_izin MODIFY COLUMN status ENUM('aktif', 'selesai') DEFAULT 'aktif'");
    }
};
