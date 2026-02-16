<?php

namespace App\Models;

use App\Traits\UuidTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class ProfileChangeRequest extends Model
{
  use UuidTrait, LogsActivity;

  protected $fillable = [
    'user_id',
    'old_data',
    'new_data',
    'status',
    'catatan',
    'alasan_reject',
    'reviewed_by',
    'reviewed_at',
  ];

  protected $casts = [
    'old_data' => 'array',
    'new_data' => 'array',
    'reviewed_at' => 'datetime',
  ];

  public const STATUS_PENDING = 'pending';
  public const STATUS_APPROVED = 'approved';
  public const STATUS_REJECTED = 'rejected';

  public const STATUS_LABELS = [
    self::STATUS_PENDING => 'Menunggu Verifikasi',
    self::STATUS_APPROVED => 'Disetujui',
    self::STATUS_REJECTED => 'Ditolak',
  ];

  public const STATUS_COLORS = [
    self::STATUS_PENDING => 'warning',
    self::STATUS_APPROVED => 'success',
    self::STATUS_REJECTED => 'danger',
  ];

  public const STATUS_ICONS = [
    self::STATUS_PENDING => 'heroicon-o-clock',
    self::STATUS_APPROVED => 'heroicon-o-check-circle',
    self::STATUS_REJECTED => 'heroicon-o-x-circle',
  ];

  public function getActivitylogOptions(): LogOptions
  {
    return LogOptions::defaults()
      ->logOnly(['status', 'reviewed_by', 'alasan_reject'])
      ->logOnlyDirty()
      ->dontSubmitEmptyLogs()
      ->useLogName('profil_change')
      ->setDescriptionForEvent(fn(string $eventName) => match ($eventName) {
        'created' => 'Pengajuan perubahan profil oleh ' . ($this->user?->name ?? ''),
        'updated' => match ($this->status) {
          self::STATUS_APPROVED => 'Perubahan profil disetujui untuk ' . ($this->user?->name ?? ''),
          self::STATUS_REJECTED => 'Perubahan profil ditolak untuk ' . ($this->user?->name ?? ''),
          default => 'Pengajuan profil diperbarui',
        },
        default => "Profil change request {$eventName}",
      });
  }

  /**
   * User yang mengajukan perubahan
   */
  public function user(): BelongsTo
  {
    return $this->belongsTo(User::class, 'user_id');
  }

  /**
   * Super Admin yang mereview
   */
  public function reviewer(): BelongsTo
  {
    return $this->belongsTo(User::class, 'reviewed_by');
  }

  /**
   * Check if this request is still pending
   */
  public function isPending(): bool
  {
    return $this->status === self::STATUS_PENDING;
  }

  /**
   * Check if this request has been approved
   */
  public function isApproved(): bool
  {
    return $this->status === self::STATUS_APPROVED;
  }

  /**
   * Check if this request has been rejected
   */
  public function isRejected(): bool
  {
    return $this->status === self::STATUS_REJECTED;
  }

  /**
   * Get the list of changed fields between old and new data
   */
  public function getChangedFields(): array
  {
    $old = $this->old_data ?? [];
    $new = $this->new_data ?? [];
    $changed = [];

    foreach ($new as $key => $value) {
      if (($old[$key] ?? null) !== $value) {
        $changed[$key] = [
          'old' => $old[$key] ?? null,
          'new' => $value,
        ];
      }
    }

    return $changed;
  }

  /**
   * Field labels for display
   */
  public static function getFieldLabels(): array
  {
    return [
      'nama_ketua' => 'Nama Ketua KCD',
      'nip_ketua' => 'NIP',
      'jabatan' => 'Jabatan',
    ];
  }
}
