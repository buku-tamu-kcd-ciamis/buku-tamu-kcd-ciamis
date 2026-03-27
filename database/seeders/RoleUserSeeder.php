<?php

namespace Database\Seeders;

use App\Models\RoleUser;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RoleUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $now = Carbon::now()->format('Y-m-d H:i:s');

        $roles = [
            ['name' => 'Super Admin', 'need_approval' => false],
            ['name' => 'Kepala Cabang Dinas', 'need_approval' => false],
            ['name' => 'Piket', 'need_approval' => false],
            ['name' => 'Customer', 'need_approval' => true],
        ];

        foreach ($roles as $role) {
            $permissions = RoleUser::getSeedPermissionsForRole($role['name']);

            $existing = DB::table('role_users')->where('name', $role['name'])->first();

            if ($existing) {
                DB::table('role_users')
                    ->where('id', $existing->id)
                    ->update([
                        'need_approval' => $role['need_approval'],
                        'author_id' => null,
                        'permissions' => json_encode($permissions),
                    ]);

                continue;
            }

            DB::table('role_users')->insert([
                'id' => uniqid(),
                'name' => $role['name'],
                'need_approval' => $role['need_approval'],
                'author_id' => null,
                'permissions' => json_encode($permissions),
                'created_at' => $now,
            ]);
        }
    }
}
