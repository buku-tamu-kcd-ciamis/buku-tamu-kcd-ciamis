<?php

namespace App\Models;

use App\Traits\UuidTrait;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class BookingChatMessage extends Model
{
    use UuidTrait;

    protected $fillable = [
        'booking_chat_id',
        'sender_user_id',
        'reply_to_message_id',
        'message',
        'attachment_path',
        'attachment_name',
        'attachment_mime',
        'attachment_size',
        'deleted_for_everyone_at',
        'deleted_for_everyone_by',
        'is_system',
        'read_at',
    ];

    protected $casts = [
        'is_system' => 'boolean',
        'attachment_size' => 'integer',
        'deleted_for_everyone_at' => 'datetime',
        'read_at' => 'datetime',
    ];

    public function chat(): BelongsTo
    {
        return $this->belongsTo(BookingChat::class, 'booking_chat_id');
    }

    public function sender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sender_user_id');
    }

    public function repliedTo(): BelongsTo
    {
        return $this->belongsTo(self::class, 'reply_to_message_id');
    }

    public function deletedForEveryoneBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'deleted_for_everyone_by');
    }

    public function userDeletions(): HasMany
    {
        return $this->hasMany(BookingChatMessageDeletion::class, 'booking_chat_message_id');
    }

    public function scopeVisibleForUser(Builder $query, User $user): Builder
    {
        return $query->whereDoesntHave('userDeletions', function (Builder $deletionQuery) use ($user) {
            $deletionQuery->where('user_id', $user->id);
        });
    }

    public function scopeNotDeletedForEveryone(Builder $query): Builder
    {
        return $query->whereNull('deleted_for_everyone_at');
    }

    public function hasAttachment(): bool
    {
        if ($this->isDeletedForEveryone()) {
            return false;
        }

        return is_string($this->attachment_path) && $this->attachment_path !== '';
    }

    public function isImageAttachment(): bool
    {
        return is_string($this->attachment_mime) && Str::startsWith($this->attachment_mime, 'image/');
    }

    public function attachmentUrl(): ?string
    {
        if (!$this->hasAttachment()) {
            return null;
        }

        if (Str::startsWith($this->attachment_path, ['http://', 'https://', '/'])) {
            return Str::startsWith($this->attachment_path, '/')
                ? asset(ltrim($this->attachment_path, '/'))
                : $this->attachment_path;
        }

        return Storage::url($this->attachment_path);
    }

    public function isDeletedForEveryone(): bool
    {
        return $this->deleted_for_everyone_at !== null;
    }
}
