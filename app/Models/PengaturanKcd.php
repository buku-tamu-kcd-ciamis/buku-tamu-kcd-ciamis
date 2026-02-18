<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class PengaturanKcd extends Model
{
  use LogsActivity;

  protected $table = 'pengaturan_kcd';

  public function getActivitylogOptions(): LogOptions
  {
    return LogOptions::defaults()
      ->logOnly(['nama_kepala', 'nip_kepala', 'jabatan', 'barcode_skm'])
      ->logOnlyDirty()
      ->dontSubmitEmptyLogs()
      ->useLogName('pengaturan')
      ->setDescriptionForEvent(fn(string $eventName) => match ($eventName) {
        'created' => 'Pengaturan KCD dibuat',
        'updated' => 'Pengaturan KCD diperbarui',
        default => "Pengaturan KCD {$eventName}",
      });
  }

  protected $fillable = [
    'nama_kepala',
    'nip_kepala',
    'jabatan',
    'barcode_skm',
  ];

  /**
   * Get the single KCD settings record (singleton pattern).
   */
  public static function getSettings(): self
  {
    return static::firstOrCreate([], [
      'jabatan' => 'Kepala Cabang Dinas Pendidikan Wilayah XIII',
    ]);
  }

  /**
   * Get formatted name for signature display.
   */
  public function getFormattedNamaAttribute(): string
  {
    return $this->nama_kepala ?: '(...............................................)';
  }

  /**
   * Get formatted NIP for signature display.
   */
  public function getFormattedNipAttribute(): string
  {
    return $this->nip_kepala ? 'NIP. ' . $this->nip_kepala : 'NIP. ..............................';
  }

  /**
   * Get full URL for barcode SKM image.
   */
  public function getBarcodeSkMUrlAttribute(): ?string
  {
    if (!$this->barcode_skm) {
      return asset('img/barcode-skm.png'); // Default barcode
    }

    return Storage::url($this->barcode_skm);
  }
}
