<?php

namespace App\Models;

use App\Traits\UuidTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class BookingChat extends Model
{
    use UuidTrait;

    protected $fillable = [
        'buku_tamu_id',
        'staff_user_id',
        'piket_user_id',
        'created_by_user_id',
        'last_message_at',
        'staff_last_seen_at',
        'piket_last_seen_at',
        'staff_typing_at',
        'piket_typing_at',
    ];

    protected $casts = [
        'last_message_at' => 'datetime',
        'staff_last_seen_at' => 'datetime',
        'piket_last_seen_at' => 'datetime',
        'staff_typing_at' => 'datetime',
        'piket_typing_at' => 'datetime',
    ];

    public function bukuTamu(): BelongsTo
    {
        return $this->belongsTo(BukuTamu::class, 'buku_tamu_id');
    }

    public function staffUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'staff_user_id');
    }

    public function piketUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'piket_user_id');
    }

    public function createdByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }

    public function messages(): HasMany
    {
        return $this->hasMany(BookingChatMessage::class, 'booking_chat_id');
    }

    public function latestMessage(): HasOne
    {
        return $this->hasOne(BookingChatMessage::class, 'booking_chat_id')->latestOfMany('created_at');
    }

    public function canBeAccessedBy(User $user): bool
    {
        if ($user->id === $this->staff_user_id) {
            return true;
        }

        return $user->hasAnyRole(['Piket', 'Super Admin']) && ($user->role_user?->hasPermission('buku_tamu') ?? false);
    }

    public function unreadCountFor(User $user): int
    {
        return $this->messages()
            ->where('is_system', false)
            ->whereNull('read_at')
            ->where(function ($query) use ($user) {
                $query->whereNull('sender_user_id')->orWhere('sender_user_id', '!=', $user->id);
            })
            ->count();
    }

    public function markMessagesAsReadFor(User $user): void
    {
        $this->messages()
            ->where('is_system', false)
            ->whereNull('read_at')
            ->where(function ($query) use ($user) {
                $query->whereNull('sender_user_id')->orWhere('sender_user_id', '!=', $user->id);
            })
            ->update(['read_at' => now()]);
    }

    public function markPresenceFor(User $user): void
    {
        $column = $user->id === $this->staff_user_id ? 'staff_last_seen_at' : 'piket_last_seen_at';

        $this->forceFill([$column => now()])->save();
    }

    public function markTypingFor(User $user): void
    {
        $column = $user->id === $this->staff_user_id ? 'staff_typing_at' : 'piket_typing_at';

        $this->forceFill([$column => now()])->save();
    }

    public function clearTypingFor(User $user): void
    {
        $column = $user->id === $this->staff_user_id ? 'staff_typing_at' : 'piket_typing_at';

        $this->forceFill([$column => null])->save();
    }
}
