<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class StaffRoleSeeder extends Seeder
{
    public function run(): void
    {
        if (!DB::table('role_users')->where('name', '=', 'Staff')->exists()) {
            DB::table('role_users')->insert([
                'id' => uniqid(),
                'name' => 'Staff',
                'need_approval' => false,
                'author_id' => null,
                'permissions' => json_encode([
                    'buku_tamu' => true,
                    'activity_log' => false,
                    'pegawai_izin' => true,
                    'rekap_izin' => false,
                    'data_pegawai' => true,
                    'dropdown_options' => false,
                    'pegawai_piket' => false,
                    'user_management' => false,
                    'profil_kepala_cabdin' => false,
                    'riwayat_tamu' => true,
                    'pengantar_berkas' => false,
                    'can_print' => false,
                    'can_change_status' => false,
                ]),
                'created_at' => Carbon::now()->format('Y-m-d H:i:s'),
            ]);
        }
    }
}
