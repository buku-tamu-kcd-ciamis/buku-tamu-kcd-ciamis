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
        Schema::table('pegawai', function (Blueprint $table) {
            $table->string('nip', 18)->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Backfill null NIP with deterministic unique placeholder values before making it required again.
        DB::table('pegawai')
            ->whereNull('nip')
            ->orderBy('id')
            ->chunkById(500, function ($rows): void {
                foreach ($rows as $row) {
                    DB::table('pegawai')
                        ->where('id', $row->id)
                        ->update([
                            'nip' => '990000000000' . str_pad((string) $row->id, 6, '0', STR_PAD_LEFT),
                        ]);
                }
            });

        Schema::table('pegawai', function (Blueprint $table) {
            $table->string('nip', 18)->nullable(false)->change();
        });
    }
};
