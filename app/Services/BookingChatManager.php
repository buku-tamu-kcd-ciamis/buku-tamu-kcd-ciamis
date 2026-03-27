<?php

namespace App\Services;

use App\Models\BookingChat;
use App\Models\BookingChatMessage;
use App\Models\BukuTamu;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class BookingChatManager
{
    public function resolveStaffUsersForBooking(BukuTamu $booking): Collection
    {
        return User::query()
            ->whereHas('role_user', fn($query) => $query->where('name', 'Staff'))
            ->whereHas('pegawai', fn($query) => $query->where('nama', $booking->staff_dituju))
            ->get();
    }

    public function getOrCreateForBookingAndStaff(BukuTamu $booking, User $staffUser, ?User $creator = null): BookingChat
    {
        $chat = BookingChat::query()->firstOrCreate(
            [
                'buku_tamu_id' => $booking->id,
                'staff_user_id' => $staffUser->id,
            ],
            [
                'created_by_user_id' => $creator?->id,
                'piket_user_id' => $creator && $creator->hasRole('Piket') ? $creator->id : null,
                'last_message_at' => now(),
            ],
        );

        if (!$chat->last_message_at) {
            $chat->update(['last_message_at' => now()]);
        }

        return $chat;
    }

    public function bootstrapForBooking(BukuTamu $booking, ?User $creator = null): Collection
    {
        $createdChats = collect();

        foreach ($this->resolveStaffUsersForBooking($booking) as $staffUser) {
            $chat = $this->getOrCreateForBookingAndStaff($booking, $staffUser, $creator);

            if ($chat->messages()->doesntExist()) {
                $this->sendSystemMessage(
                    $chat,
                    "Booking baru masuk dari {$booking->nama_lengkap} ({$booking->instansi}). Koordinasikan alur penerimaan tamu melalui chat ini.",
                );
            }

            $createdChats->push($chat);
        }

        return $createdChats;
    }

    public function sendSystemMessage(BookingChat $chat, string $message): BookingChatMessage
    {
        $sanitizedMessage = trim($message);

        return DB::transaction(function () use ($chat, $sanitizedMessage): BookingChatMessage {
            $chatMessage = $chat->messages()->create([
                'sender_user_id' => null,
                'message' => $sanitizedMessage,
                'is_system' => true,
                'read_at' => now(),
            ]);

            $chat->update(['last_message_at' => $chatMessage->created_at]);

            return $chatMessage;
        });
    }

    /**
     * @param array{path:string,name?:string,mime?:string,size?:int}|null $attachment
     */
    public function sendMessage(BookingChat $chat, User $sender, ?string $message = null, ?array $attachment = null): BookingChatMessage
    {
        if (!$chat->canBeAccessedBy($sender)) {
            abort(403);
        }

        $sanitizedMessage = trim((string) $message);
        $hasAttachment = is_array($attachment) && filled($attachment['path'] ?? null);

        if ($sanitizedMessage === '' && !$hasAttachment) {
            throw new \InvalidArgumentException('Message or attachment is required.');
        }

        return DB::transaction(function () use ($chat, $sender, $sanitizedMessage, $attachment, $hasAttachment): BookingChatMessage {
            if ($sender->hasRole('Piket') && !$chat->piket_user_id) {
                $chat->update(['piket_user_id' => $sender->id]);
            }

            $chatMessage = $chat->messages()->create([
                'sender_user_id' => $sender->id,
                'message' => $sanitizedMessage !== '' ? $sanitizedMessage : '[Lampiran]',
                'attachment_path' => $hasAttachment ? $attachment['path'] : null,
                'attachment_name' => $hasAttachment ? ($attachment['name'] ?? null) : null,
                'attachment_mime' => $hasAttachment ? ($attachment['mime'] ?? null) : null,
                'attachment_size' => $hasAttachment ? ($attachment['size'] ?? null) : null,
                'is_system' => false,
            ]);

            $chat->update(['last_message_at' => $chatMessage->created_at]);
            $chat->markPresenceFor($sender);
            $chat->clearTypingFor($sender);

            return $chatMessage;
        });
    }
}
