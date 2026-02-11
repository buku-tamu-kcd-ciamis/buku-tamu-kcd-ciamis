<?php

namespace Database\Seeders;

use App\Models\RoleUser;
use Carbon\Carbon;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Super Admin
        if (! DB::table('users')->where('email', 'superadmin@cadisdik13.id')->exists()) {
            DB::table('users')->insert([
                'id'    => uniqid(),
                'name' => 'Super Admin',
                'email' => 'superadmin@cadisdik13.id',
                'role_user_id'  => RoleUser::where('name', 'Super Admin')->first()->id,
                'email_verified_at' => Carbon::now()->format('Y-m-d H:i:s'),
                'password' => Hash::make('superadmin123'),
                'created_at' => Carbon::now()->format('Y-m-d H:i:s'),
            ]);
        }

        // Ketua KCD
        if (! DB::table('users')->where('email', 'ketua@cadisdik13.id')->exists()) {
            DB::table('users')->insert([
                'id'    => uniqid(),
                'name' => 'Ketua KCD XIII',
                'email' => 'ketua@cadisdik13.id',
                'role_user_id'  => RoleUser::where('name', 'Ketua KCD')->first()->id,
                'email_verified_at' => Carbon::now()->format('Y-m-d H:i:s'),
                'password' => Hash::make('ketua123'),
                'created_at' => Carbon::now()->format('Y-m-d H:i:s'),
            ]);
        }

        // Loket
        if (! DB::table('users')->where('email', 'loket@cadisdik13.id')->exists()) {
            DB::table('users')->insert([
                'id'    => uniqid(),
                'name' => 'Petugas Loket',
                'email' => 'loket@cadisdik13.id',
                'role_user_id'  => RoleUser::where('name', 'Loket')->first()->id,
                'email_verified_at' => Carbon::now()->format('Y-m-d H:i:s'),
                'password' => Hash::make('loket123'),
                'created_at' => Carbon::now()->format('Y-m-d H:i:s'),
            ]);
        }
    }
}
