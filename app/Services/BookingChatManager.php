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
    public const EDIT_WINDOW_MINUTES = 30;

    private function ensureChatIsOpenForConversation(BookingChat $chat): void
    {
        if (($chat->bukuTamu?->status ?? null) === BukuTamu::STATUS_SELESAI) {
            throw new \InvalidArgumentException('Thread ini sudah ditutup karena status booking Selesai. Chat baru akan terbuka saat ada booking baru dari tamu yang sama ke staff yang sama.');
        }
    }

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
    public function sendMessage(
        BookingChat $chat,
        User $sender,
        ?string $message = null,
        ?array $attachment = null,
        ?string $replyToMessageId = null,
    ): BookingChatMessage {
        if (!$chat->canBeAccessedBy($sender)) {
            abort(403);
        }

        $this->ensureChatIsOpenForConversation($chat);

        $sanitizedMessage = trim((string) $message);
        $hasAttachment = is_array($attachment) && filled($attachment['path'] ?? null);
        $replyToMessage = null;

        if ($replyToMessageId) {
            $replyToMessage = $chat->messages()
                ->whereKey($replyToMessageId)
                ->first();

            if (!$replyToMessage) {
                throw new \InvalidArgumentException('Reply target is invalid.');
            }
        }

        if ($sanitizedMessage === '' && !$hasAttachment) {
            throw new \InvalidArgumentException('Message or attachment is required.');
        }

        return DB::transaction(function () use ($chat, $sender, $sanitizedMessage, $attachment, $hasAttachment, $replyToMessage): BookingChatMessage {
            if ($sender->hasRole('Piket') && !$chat->piket_user_id) {
                $chat->update(['piket_user_id' => $sender->id]);
            }

            $chatMessage = $chat->messages()->create([
                'sender_user_id' => $sender->id,
                'reply_to_message_id' => $replyToMessage?->id,
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

    public function editMessage(
        BookingChat $chat,
        User $editor,
        string $messageId,
        string $message,
    ): BookingChatMessage {
        if (!$chat->canBeAccessedBy($editor)) {
            abort(403);
        }

        $this->ensureChatIsOpenForConversation($chat);

        $sanitizedMessage = trim($message);

        if ($sanitizedMessage === '') {
            throw new \InvalidArgumentException('Pesan wajib diisi.');
        }

        $chatMessage = $chat->messages()
            ->whereKey($messageId)
            ->where('is_system', false)
            ->first();

        if (!$chatMessage) {
            throw new \InvalidArgumentException('Pesan tidak tersedia.');
        }

        if ((string) $chatMessage->sender_user_id !== (string) $editor->id) {
            throw new \InvalidArgumentException('Anda hanya bisa mengedit pesan milik sendiri.');
        }

        if ($chatMessage->isDeletedForEveryone()) {
            throw new \InvalidArgumentException('Pesan yang sudah dihapus tidak bisa diedit.');
        }

        if ($chatMessage->hasAttachment() || $chatMessage->message === '[Lampiran]') {
            throw new \InvalidArgumentException('Pesan lampiran belum bisa diedit.');
        }

        if (!$chatMessage->isWithinEditWindow(self::EDIT_WINDOW_MINUTES)) {
            throw new \InvalidArgumentException('Pesan hanya bisa diedit dalam 30 menit setelah dikirim.');
        }

        if ($chatMessage->message === $sanitizedMessage) {
            return $chatMessage;
        }

        return DB::transaction(function () use ($chatMessage, $editor, $sanitizedMessage): BookingChatMessage {
            $chatMessage->forceFill([
                'message' => $sanitizedMessage,
                'edited_at' => now(),
                'edited_by' => $editor->id,
            ])->save();

            return $chatMessage->fresh();
        });
    }
}
