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
    Schema::table('role_users', function (Blueprint $table) {
      $table->json('permissions')->nullable()->after('need_approval');
    });

    // Set default permissions for each role
    DB::table('role_users')->where('name', 'Super Admin')->update([
      'permissions' => json_encode([
        'buku_tamu' => true,
        'activity_log' => true,
        'pegawai_izin' => true,
        'data_pegawai' => true,
        'dropdown_options' => true,
        'pegawai_piket' => true,
        'user_management' => true,
        'can_print' => true,
        'can_change_status' => true,
      ]),
    ]);

    DB::table('role_users')->where('name', 'Ketua KCD')->update([
      'permissions' => json_encode([
        'buku_tamu' => true,
        'activity_log' => false,
        'pegawai_izin' => false,
        'data_pegawai' => false,
        'dropdown_options' => false,
        'pegawai_piket' => false,
        'user_management' => false,
        'can_print' => false,
        'can_change_status' => false,
      ]),
    ]);

    DB::table('role_users')->where('name', 'Piket')->update([
      'permissions' => json_encode([
        'buku_tamu' => true,
        'activity_log' => false,
        'pegawai_izin' => true,
        'data_pegawai' => false,
        'dropdown_options' => false,
        'pegawai_piket' => false,
        'user_management' => false,
        'can_print' => true,
        'can_change_status' => true,
      ]),
    ]);
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::table('role_users', function (Blueprint $table) {
      $table->dropColumn('permissions');
    });
  }
};
