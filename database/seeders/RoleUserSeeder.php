<?php

namespace Database\Seeders;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RoleUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        if (! DB::table('role_users')->where('name', '=', 'Super Admin')->exists()) {
            DB::table('role_users')->insert([
                'id'    => uniqid(),
                'name' => 'Super Admin',
                'need_approval' => false,
                'author_id' => null,
                'created_at' => Carbon::now()->format('Y-m-d H:i:s'),
            ]);
        }

        if (! DB::table('role_users')->where('name', '=', 'Kepala Cabang Dinas')->exists()) {
            DB::table('role_users')->insert([
                'id'    => uniqid(),
                'name' => 'Kepala Cabang Dinas',
                'need_approval' => false,
                'author_id' => null,
                'created_at' => Carbon::now()->format('Y-m-d H:i:s'),
            ]);
        }

        if (! DB::table('role_users')->where('name', '=', 'Piket')->exists()) {
            DB::table('role_users')->insert([
                'id'    => uniqid(),
                'name' => 'Piket',
                'need_approval' => false,
                'author_id' => null,
                'created_at' => Carbon::now()->format('Y-m-d H:i:s'),
            ]);
        }

        if (! DB::table('role_users')->where('name', '=', 'Customer')->exists()) {
            DB::table('role_users')->insert([
                'id'    => uniqid(),
                'name' => 'Customer',
                'need_approval' => true,
                'author_id' => null,
                'created_at' => Carbon::now()->format('Y-m-d H:i:s'),
            ]);
        }
    }
}
