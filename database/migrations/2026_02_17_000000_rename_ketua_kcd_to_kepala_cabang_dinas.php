<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Rename "Ketua KCD" → "Kepala Cabang Dinas" across the database.
     */
    public function up(): void
    {
        // 1. Rename role "Ketua KCD" → "Kepala Cabang Dinas"
        DB::table('role_users')->where('name', 'Ketua KCD')->update([
            'name' => 'Kepala Cabang Dinas',
        ]);

        // 2. Rename columns in pengaturan_kcd table
        Schema::table('pengaturan_kcd', function (Blueprint $table) {
            $table->renameColumn('nama_ketua', 'nama_kepala');
            $table->renameColumn('nip_ketua', 'nip_kepala');
        });

        // 3. Update permission key profil_ketua_kcd → profil_kepala_cabdin in role_users permissions JSON
        $roles = DB::table('role_users')->whereNotNull('permissions')->get();
        foreach ($roles as $role) {
            $permissions = json_decode($role->permissions, true);
            if ($permissions && array_key_exists('profil_ketua_kcd', $permissions)) {
                $permissions['profil_kepala_cabdin'] = $permissions['profil_ketua_kcd'];
                unset($permissions['profil_ketua_kcd']);
                DB::table('role_users')->where('id', $role->id)->update([
                    'permissions' => json_encode($permissions),
                ]);
            }
        }

        // 4. Update field references in profile_change_requests old_data/new_data JSON
        $requests = DB::table('profile_change_requests')->get();
        foreach ($requests as $request) {
            $updated = false;

            $oldData = json_decode($request->old_data, true);
            if ($oldData) {
                if (array_key_exists('nama_ketua', $oldData)) {
                    $oldData['nama_kepala'] = $oldData['nama_ketua'];
                    unset($oldData['nama_ketua']);
                    $updated = true;
                }
                if (array_key_exists('nip_ketua', $oldData)) {
                    $oldData['nip_kepala'] = $oldData['nip_ketua'];
                    unset($oldData['nip_ketua']);
                    $updated = true;
                }
            }

            $newData = json_decode($request->new_data, true);
            if ($newData) {
                if (array_key_exists('nama_ketua', $newData)) {
                    $newData['nama_kepala'] = $newData['nama_ketua'];
                    unset($newData['nama_ketua']);
                    $updated = true;
                }
                if (array_key_exists('nip_ketua', $newData)) {
                    $newData['nip_kepala'] = $newData['nip_ketua'];
                    unset($newData['nip_ketua']);
                    $updated = true;
                }
            }

            if ($updated) {
                DB::table('profile_change_requests')->where('id', $request->id)->update([
                    'old_data' => json_encode($oldData),
                    'new_data' => json_encode($newData),
                ]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // 1. Rename role back
        DB::table('role_users')->where('name', 'Kepala Cabang Dinas')->update([
            'name' => 'Ketua KCD',
        ]);

        // 2. Rename columns back
        Schema::table('pengaturan_kcd', function (Blueprint $table) {
            $table->renameColumn('nama_kepala', 'nama_ketua');
            $table->renameColumn('nip_kepala', 'nip_ketua');
        });

        // 3. Revert permission key
        $roles = DB::table('role_users')->whereNotNull('permissions')->get();
        foreach ($roles as $role) {
            $permissions = json_decode($role->permissions, true);
            if ($permissions && array_key_exists('profil_kepala_cabdin', $permissions)) {
                $permissions['profil_ketua_kcd'] = $permissions['profil_kepala_cabdin'];
                unset($permissions['profil_kepala_cabdin']);
                DB::table('role_users')->where('id', $role->id)->update([
                    'permissions' => json_encode($permissions),
                ]);
            }
        }

        // 4. Revert profile_change_requests
        $requests = DB::table('profile_change_requests')->get();
        foreach ($requests as $request) {
            $updated = false;

            $oldData = json_decode($request->old_data, true);
            if ($oldData) {
                if (array_key_exists('nama_kepala', $oldData)) {
                    $oldData['nama_ketua'] = $oldData['nama_kepala'];
                    unset($oldData['nama_kepala']);
                    $updated = true;
                }
                if (array_key_exists('nip_kepala', $oldData)) {
                    $oldData['nip_ketua'] = $oldData['nip_kepala'];
                    unset($oldData['nip_kepala']);
                    $updated = true;
                }
            }

            $newData = json_decode($request->new_data, true);
            if ($newData) {
                if (array_key_exists('nama_kepala', $newData)) {
                    $newData['nama_ketua'] = $newData['nama_kepala'];
                    unset($newData['nama_kepala']);
                    $updated = true;
                }
                if (array_key_exists('nip_kepala', $newData)) {
                    $newData['nip_ketua'] = $newData['nip_kepala'];
                    unset($newData['nip_kepala']);
                    $updated = true;
                }
            }

            if ($updated) {
                DB::table('profile_change_requests')->where('id', $request->id)->update([
                    'old_data' => json_encode($oldData),
                    'new_data' => json_encode($newData),
                ]);
            }
        }
    }
};
