<?php

namespace Database\Seeders;

use App\Models\NomorSuratSetting;
use Illuminate\Database\Seeder;

class NomorSuratSettingSeeder extends Seeder
{
  /**
   * Run the database seeds.
   */
  public function run(): void
  {
    $settings = [
      [
        'jenis_surat' => 'buku_tamu',
        'nama_jenis' => 'Bukti Kunjungan Tamu',
        'template' => '{NOMOR}/BT/{BULAN}/{TAHUN}',
        'kode_surat' => 'BT',
        'padding_length' => 6,
        'keterangan' => 'Format nomor surat untuk bukti kunjungan tamu. Placeholder: {NOMOR} = nomor urut, {KODE} = kode surat, {BULAN} = bulan (01-12), {TAHUN} = tahun 4 digit, {TAHUN_PENDEK} = tahun 2 digit, {ROMAWI} = bulan romawi (I-XII)',
        'is_active' => true,
      ],
    ];

    foreach ($settings as $setting) {
      NomorSuratSetting::updateOrCreate(
        ['jenis_surat' => $setting['jenis_surat']],
        $setting
      );
    }
  }
}
