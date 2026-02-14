<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Pegawai extends Model
{
  use LogsActivity;

  protected $table = 'pegawai';

  public function getActivitylogOptions(): LogOptions
  {
    return LogOptions::defaults()
      ->logOnly(['nama', 'nip', 'jabatan', 'nomor_hp', 'unit_kerja', 'is_active'])
      ->logOnlyDirty()
      ->dontSubmitEmptyLogs()
      ->useLogName('pegawai')
      ->setDescriptionForEvent(fn(string $eventName) => match ($eventName) {
        'created' => "Data pegawai '{$this->nama}' ditambahkan",
        'updated' => "Data pegawai '{$this->nama}' diperbarui",
        'deleted' => "Data pegawai '{$this->nama}' dihapus",
        default => "Data pegawai '{$this->nama}' {$eventName}",
      });
  }

  protected $fillable = [
    'nama',
    'nip',
    'jabatan',
    'nomor_hp',
    'unit_kerja',
    'is_active',
  ];

  protected $casts = [
    'is_active' => 'boolean',
  ];

  public function scopeActive($query)
  {
    return $query->where('is_active', true);
  }

  /**
   * Get options for Select dropdown (nama => nama)
   */
  public static function getSelectOptions(): array
  {
    return static::active()
      ->orderBy('nama')
      ->pluck('nama', 'id')
      ->toArray();
  }
}
