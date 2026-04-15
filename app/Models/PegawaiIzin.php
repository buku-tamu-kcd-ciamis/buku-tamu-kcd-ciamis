<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;

class PegawaiIzin extends Model
{
  use LogsActivity;

  protected $table = 'pegawai_izin';

  protected $fillable = [
    'nama_pegawai',
    'nip',
    'jabatan',
    'unit_kerja',
    'nomor_hp',
    'jenis_izin',
    'tanggal_mulai',
    'tanggal_selesai',
    'keterangan',
    'status',
    'nama_piket',
    'tanda_tangan_piket',
    'diverifikasi_oleh',
    'diverifikasi_pada',
    'catatan_verifikasi',
  ];

  protected $casts = [
    'tanggal_mulai' => 'date',
    'tanggal_selesai' => 'date',
    'diverifikasi_pada' => 'datetime',
  ];

  public const JENIS_IZIN_LABELS = [
    'sakit' => 'Sakit',
    'cuti' => 'Cuti',
    'dinas_luar' => 'Dinas Luar',
    'izin_pribadi' => 'Izin Pribadi',
    'lainnya' => 'Lainnya',
  ];

  public const STATUS_MENUNGGU = 'menunggu';
  public const STATUS_DISETUJUI = 'disetujui';
  public const STATUS_DITOLAK = 'ditolak';
  public const STATUS_AKTIF = 'aktif';
  public const STATUS_SELESAI = 'selesai';

  public const STATUS_LABELS = [
    self::STATUS_MENUNGGU => 'Menunggu',
    self::STATUS_DISETUJUI => 'Disetujui',
    self::STATUS_DITOLAK => 'Ditolak',
    self::STATUS_AKTIF => 'Aktif',
    self::STATUS_SELESAI => 'Selesai',
  ];

  public const VERIFIED_STATUSES = [
    self::STATUS_DISETUJUI,
    self::STATUS_AKTIF,
    self::STATUS_SELESAI,
  ];

  public function isVerifiedByKcd(): bool
  {
    return in_array($this->status, self::VERIFIED_STATUSES, true);
  }

  public function getActivitylogOptions(): LogOptions
  {
    return LogOptions::defaults()
      ->logOnly([
        'nama_pegawai',
        'nip',
        'jenis_izin',
        'status',
        'tanggal_mulai',
        'tanggal_selesai',
        'keterangan',
        'nama_piket',
        'diverifikasi_oleh',
        'diverifikasi_pada',
        'catatan_verifikasi',
      ])
      ->logOnlyDirty()
      ->dontLogEmptyChanges()
      ->setDescriptionForEvent(fn(string $eventName) => match ($eventName) {
        'created' => "Membuat surat izin {$this->jenis_izin} atas nama {$this->nama_pegawai}",
        'updated' => "Memperbarui surat izin {$this->jenis_izin} atas nama {$this->nama_pegawai}",
        'deleted' => "Menghapus surat izin {$this->jenis_izin} atas nama {$this->nama_pegawai}",
        default => "Aktivitas surat izin: {$eventName}",
      })
      ->useLogName('pegawai_izin');
  }
}
