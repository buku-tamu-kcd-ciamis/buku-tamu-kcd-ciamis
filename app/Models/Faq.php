<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Faq extends Model
{
  use LogsActivity;

  public function getActivitylogOptions(): LogOptions
  {
    return LogOptions::defaults()
      ->logOnly(['question', 'answer', 'target', 'sort_order', 'is_active'])
      ->logOnlyDirty()
      ->dontSubmitEmptyLogs()
      ->useLogName('faq')
      ->setDescriptionForEvent(fn(string $eventName) => match ($eventName) {
        'created' => 'FAQ baru ditambahkan: ' . ($this->question ? '"' . \Illuminate\Support\Str::limit($this->question, 50) . '"' : ''),
        'updated' => 'FAQ diperbarui: ' . ($this->question ? '"' . \Illuminate\Support\Str::limit($this->question, 50) . '"' : ''),
        'deleted' => 'FAQ dihapus: ' . ($this->question ? '"' . \Illuminate\Support\Str::limit($this->question, 50) . '"' : ''),
        default => "FAQ {$eventName}",
      });
  }

  protected $fillable = [
    'question',
    'answer',
    'target',
    'sort_order',
    'is_active',
  ];

  protected $casts = [
    'is_active' => 'boolean',
    'sort_order' => 'integer',
  ];

  public const TARGET_SEMUA = 'semua';
  public const TARGET_ADMIN = 'admin';
  public const TARGET_PIKET = 'piket';

  public const TARGET_LABELS = [
    self::TARGET_SEMUA => 'Semua Panel',
    self::TARGET_ADMIN => 'Panel Admin (Ketua KCD)',
    self::TARGET_PIKET => 'Panel Piket',
  ];

  /**
   * Get FAQs for Admin panel (semua + admin).
   */
  public static function getForAdmin(): array
  {
    return static::where('is_active', true)
      ->whereIn('target', [self::TARGET_SEMUA, self::TARGET_ADMIN])
      ->orderBy('sort_order')
      ->get()
      ->map(fn($faq) => [
        'question' => $faq->question,
        'answer' => $faq->answer,
      ])
      ->toArray();
  }

  /**
   * Get FAQs for Piket panel (semua + piket).
   */
  public static function getForPiket(): array
  {
    return static::where('is_active', true)
      ->whereIn('target', [self::TARGET_SEMUA, self::TARGET_PIKET])
      ->orderBy('sort_order')
      ->get()
      ->map(fn($faq) => [
        'question' => $faq->question,
        'answer' => $faq->answer,
      ])
      ->toArray();
  }
}
