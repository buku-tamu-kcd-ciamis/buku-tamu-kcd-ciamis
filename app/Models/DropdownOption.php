<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Support\Facades\Cache;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class DropdownOption extends Model
{
  use HasUuids, LogsActivity;

  public function getActivitylogOptions(): LogOptions
  {
    return LogOptions::defaults()
      ->logOnly(['category', 'label', 'value', 'is_active', 'sort_order'])
      ->logOnlyDirty()
      ->dontSubmitEmptyLogs()
      ->useLogName('dropdown_option')
      ->setDescriptionForEvent(fn(string $eventName) => match ($eventName) {
        'created' => "Opsi dropdown '{$this->label}' (" . (self::CATEGORY_LABELS[$this->category] ?? $this->category) . ") ditambahkan",
        'updated' => "Opsi dropdown '{$this->label}' (" . (self::CATEGORY_LABELS[$this->category] ?? $this->category) . ") diperbarui",
        'deleted' => "Opsi dropdown '{$this->label}' (" . (self::CATEGORY_LABELS[$this->category] ?? $this->category) . ") dihapus",
        default => "Opsi dropdown '{$this->label}' {$eventName}",
      });
  }

  protected $table = 'dropdown_options';

  protected $fillable = [
    'category',
    'value',
    'label',
    'metadata',
    'sort_order',
    'is_active',
  ];

  protected $casts = [
    'metadata' => 'array',
    'is_active' => 'boolean',
    'sort_order' => 'integer',
  ];

  // Categories
  public const CATEGORY_JENIS_ID = 'jenis_id';
  public const CATEGORY_KEPERLUAN = 'keperluan';
  public const CATEGORY_KABUPATEN_KOTA = 'kabupaten_kota';
  public const CATEGORY_BAGIAN_DITUJU = 'bagian_dituju';
  public const CATEGORY_PEGAWAI_PIKET = 'pegawai_piket';

  public const CATEGORY_LABELS = [
    self::CATEGORY_JENIS_ID => 'Jenis ID',
    self::CATEGORY_KEPERLUAN => 'Keperluan',
    self::CATEGORY_KABUPATEN_KOTA => 'Kabupaten/Kota',
    self::CATEGORY_BAGIAN_DITUJU => 'Bagian Yang Dituju',
    self::CATEGORY_PEGAWAI_PIKET => 'Pegawai Piket',
  ];

  /**
   * Scope: active options for a category, ordered by sort_order
   */
  public function scopeForCategory($query, string $category)
  {
    return $query->where('category', $category)
      ->where('is_active', true)
      ->orderBy('sort_order');
  }

  /**
   * Get options as value => label array for Filament Select fields
   */
  public static function getOptions(string $category): array
  {
    return Cache::remember("dropdown_options.{$category}", 60 * 5, function () use ($category) {
      return static::forCategory($category)
        ->pluck('label', 'value')
        ->toArray();
    });
  }

  /**
   * Get full option data for a category (used by API/JS)
   */
  public static function getFullOptions(string $category): array
  {
    return Cache::remember("dropdown_options_full.{$category}", 60 * 5, function () use ($category) {
      return static::forCategory($category)
        ->get(['value', 'label', 'metadata'])
        ->map(function ($item) {
          $data = [
            'value' => $item->value,
            'label' => $item->label,
          ];
          if ($item->metadata) {
            $data['metadata'] = $item->metadata;
          }
          return $data;
        })
        ->toArray();
    });
  }

  /**
   * Clear cached options when data changes
   */
  public static function clearCache(?string $category = null): void
  {
    if ($category) {
      Cache::forget("dropdown_options.{$category}");
      Cache::forget("dropdown_options_full.{$category}");
    } else {
      foreach (array_keys(self::CATEGORY_LABELS) as $cat) {
        Cache::forget("dropdown_options.{$cat}");
        Cache::forget("dropdown_options_full.{$cat}");
      }
    }
  }

  /**
   * Boot method to clear cache on model events
   */
  protected static function booted(): void
  {
    static::saved(function (self $model) {
      self::clearCache($model->category);
    });

    static::deleted(function (self $model) {
      self::clearCache($model->category);
    });
  }
}
