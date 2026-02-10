<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('buku_tamu', function (Blueprint $table) {
            $table->id();
            $table->string('jenis_id');
            $table->string('nik');
            $table->string('nama_lengkap');
            $table->string('instansi')->nullable();
            $table->string('nomor_hp');
            $table->string('jabatan')->nullable();
            $table->string('kabupaten_kota');
            $table->string('bagian_dituju');
            $table->string('email')->nullable();
            $table->text('keperluan');
            $table->longText('foto_selfie');
            $table->longText('foto_penerimaan')->nullable();
            $table->longText('tanda_tangan');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('buku_tamu');
    }
};
