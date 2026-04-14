<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Support\LogOptions;
use Spatie\Activitylog\Models\Concerns\LogsActivity;

class Pegawai extends Model
{
  use LogsActivity;

  protected $table = 'pegawai';

  public const AVAILABILITY_AVAILABLE = 'available';
  public const AVAILABILITY_BUSY = 'busy';
  public const AVAILABILITY_OUT_OF_OFFICE = 'out_of_office';

  public const AVAILABILITY_LABELS = [
    self::AVAILABILITY_AVAILABLE => 'Tersedia',
    self::AVAILABILITY_BUSY => 'Sibuk',
    self::AVAILABILITY_OUT_OF_OFFICE => 'Tidak di Kantor',
  ];

  public const AVAILABILITY_COLORS = [
    self::AVAILABILITY_AVAILABLE => 'success',
    self::AVAILABILITY_BUSY => 'warning',
    self::AVAILABILITY_OUT_OF_OFFICE => 'danger',
  ];

  public const AVAILABILITY_ICONS = [
    self::AVAILABILITY_AVAILABLE => 'heroicon-o-check-circle',
    self::AVAILABILITY_BUSY => 'heroicon-o-clock',
    self::AVAILABILITY_OUT_OF_OFFICE => 'heroicon-o-x-circle',
  ];

  public function getActivitylogOptions(): LogOptions
  {
    return LogOptions::defaults()
      ->logOnly(['nama', 'nip', 'email', 'jabatan', 'nomor_hp', 'unit_kerja', 'is_active'])
      ->logOnlyDirty()
      ->dontLogEmptyChanges()
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
    'email',
    'jabatan',
    'nomor_hp',
    'unit_kerja',
    'is_active',
    'availability_status',
  ];

  protected $casts = [
    'is_active' => 'boolean',
    'availability_status' => 'string',
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
