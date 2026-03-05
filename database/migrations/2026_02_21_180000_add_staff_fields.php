<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // Add pegawai_id to users table (link staff user to pegawai data)
        Schema::table('users', function (Blueprint $table) {
            $table->unsignedBigInteger('pegawai_id')->nullable()->after('role_user_id');
            $table->foreign('pegawai_id')->references('id')->on('pegawai')->nullOnDelete();
        });

        // Add staff_dituju to buku_tamu table (optional: which staff the guest wants to meet)
        Schema::table('buku_tamu', function (Blueprint $table) {
            $table->string('staff_dituju')->nullable()->after('bagian_dituju');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['pegawai_id']);
            $table->dropColumn('pegawai_id');
        });

        Schema::table('buku_tamu', function (Blueprint $table) {
            $table->dropColumn('staff_dituju');
        });
    }
};
