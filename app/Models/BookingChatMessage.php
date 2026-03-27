<?php

namespace App\Models;

use App\Traits\UuidTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class BookingChatMessage extends Model
{
    use UuidTrait;

    protected $fillable = [
        'booking_chat_id',
        'sender_user_id',
        'message',
        'attachment_path',
        'attachment_name',
        'attachment_mime',
        'attachment_size',
        'is_system',
        'read_at',
    ];

    protected $casts = [
        'is_system' => 'boolean',
        'attachment_size' => 'integer',
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

    public function hasAttachment(): bool
    {
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
}
