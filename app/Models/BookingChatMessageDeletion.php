<?php

namespace App\Models;

use App\Traits\UuidTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BookingChatMessageDeletion extends Model
{
    use UuidTrait;

    protected $fillable = [
        'booking_chat_message_id',
        'user_id',
    ];

    public function message(): BelongsTo
    {
        return $this->belongsTo(BookingChatMessage::class, 'booking_chat_message_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
