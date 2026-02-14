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
        Schema::create('pengaturan_kcd', function (Blueprint $table) {
            $table->id();
            $table->string('nama_ketua', 255)->nullable();
            $table->string('nip_ketua', 18)->nullable();
            $table->string('jabatan', 255)->default('Kepala Cabang Dinas Pendidikan Wilayah XIII');
            $table->timestamps();
        });

        // Insert default record
        DB::table('pengaturan_kcd')->insert([
            'nama_ketua' => null,
            'nip_ketua' => null,
            'jabatan' => 'Kepala Cabang Dinas Pendidikan Wilayah XIII',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pengaturan_kcd');
    }
};
