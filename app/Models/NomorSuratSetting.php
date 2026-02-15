<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class NomorSuratSetting extends Model
{
  protected $fillable = [
    'jenis_surat',
    'nama_jenis',
    'template',
    'kode_surat',
    'padding_length',
    'keterangan',
    'is_active',
  ];

  protected $casts = [
    'is_active' => 'boolean',
    'padding_length' => 'integer',
  ];

  /**
   * Generate nomor surat berdasarkan template
   */
  public function generateNomor($id, $tanggal = null)
  {
    $date = $tanggal ? Carbon::parse($tanggal) : Carbon::now();

    // Pad nomor urut
    $nomor = str_pad($id, $this->padding_length, '0', STR_PAD_LEFT);

    // Bulan romawi
    $bulanRomawi = [
      1 => 'I',
      2 => 'II',
      3 => 'III',
      4 => 'IV',
      5 => 'V',
      6 => 'VI',
      7 => 'VII',
      8 => 'VIII',
      9 => 'IX',
      10 => 'X',
      11 => 'XI',
      12 => 'XII'
    ];

    // Replace placeholders
    $result = str_replace(
      ['{NOMOR}', '{KODE}', '{BULAN}', '{TAHUN}', '{TAHUN_PENDEK}', '{ROMAWI}'],
      [
        $nomor,
        $this->kode_surat,
        $date->format('m'),
        $date->format('Y'),
        $date->format('y'),
        $bulanRomawi[$date->month]
      ],
      $this->template
    );

    return $result;
  }

  /**
   * Get setting by jenis surat
   */
  public static function getByJenis($jenis)
  {
    return self::where('jenis_surat', $jenis)->where('is_active', true)->first();
  }
}
