<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PegawaiIzin extends Model
{
  protected $table = 'pegawai_izin';

  protected $fillable = [
    'nama_pegawai',
    'nip',
    'jabatan',
    'unit_kerja',
    'jenis_izin',
    'tanggal_mulai',
    'tanggal_selesai',
    'keterangan',
    'status',
  ];

  protected $casts = [
    'tanggal_mulai' => 'date',
    'tanggal_selesai' => 'date',
  ];

  public const JENIS_IZIN_LABELS = [
    'sakit' => 'Sakit',
    'cuti' => 'Cuti',
    'dinas_luar' => 'Dinas Luar',
    'izin_pribadi' => 'Izin Pribadi',
    'lainnya' => 'Lainnya',
  ];
}
