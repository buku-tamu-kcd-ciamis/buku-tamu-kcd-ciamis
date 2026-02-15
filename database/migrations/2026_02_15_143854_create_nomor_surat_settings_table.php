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
        Schema::create('nomor_surat_settings', function (Blueprint $table) {
            $table->id();
            $table->string('jenis_surat', 50)->unique()->comment('buku_tamu, surat_izin, dll');
            $table->string('nama_jenis', 100)->comment('Nama tampilan jenis surat');
            $table->string('template', 255)->comment('Template nomor surat dengan placeholder');
            $table->string('kode_surat', 20)->comment('Kode surat (misal: BT, SI)');
            $table->integer('padding_length')->default(6)->comment('Panjang padding nomor urut');
            $table->text('keterangan')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('nomor_surat_settings');
    }
};
