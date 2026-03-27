<?php

namespace App\Models;

use App\Traits\UuidTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StaffNotification extends Model
{
    use UuidTrait;

    protected $fillable = [
        'user_id',
        'buku_tamu_id',
        'type',
        'message',
        'is_read',
        'response',
        'responded_at',
    ];

    protected $casts = [
        'is_read' => 'boolean',
        'responded_at' => 'datetime',
    ];

    public const RESPONSE_DITERIMA = 'diterima';
    public const RESPONSE_DITOLAK = 'ditolak';

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function bukuTamu(): BelongsTo
    {
        return $this->belongsTo(BukuTamu::class);
    }

    /**
     * Scope: unread notifications for a specific user
     */
    public function scopeUnreadForUser($query, string $userId)
    {
        return $query->where('user_id', $userId)->where('is_read', false);
    }

    /**
     * Scope: pending (no response yet)
     */
    public function scopePending($query)
    {
        return $query->whereNull('response');
    }

    /**
     * Mark as read
     */
    public function markAsRead(): void
    {
        $this->update(['is_read' => true]);
    }

    /**
     * Respond to notification
     */
    public function respond(string $response): void
    {
        $this->update([
            'response' => $response,
            'responded_at' => now(),
            'is_read' => true,
        ]);
    }

    public function respondAndSyncVisitStatus(string $response): void
    {
        if (!in_array($response, [self::RESPONSE_DITERIMA, self::RESPONSE_DITOLAK], true)) {
            throw new \InvalidArgumentException('Invalid staff notification response.');
        }

        $this->respond($response);

        $visit = $this->bukuTamu;

        if (!$visit) {
            return;
        }

        if ($response === self::RESPONSE_DITERIMA && $visit->status === BukuTamu::STATUS_MENUNGGU) {
            $visit->update(['status' => BukuTamu::STATUS_DIPROSES]);

            return;
        }

        if (
            $response === self::RESPONSE_DITOLAK
            && !in_array($visit->status, [BukuTamu::STATUS_SELESAI, BukuTamu::STATUS_DIBATALKAN], true)
        ) {
            $visit->update(['status' => BukuTamu::STATUS_DITOLAK]);
        }
    }
}
