<?php

namespace App\Filament\Piket\Pages;

use App\Filament\Piket\Concerns\ChecksPiketPermission;
use App\Models\BookingChat;
use App\Models\BookingChatMessage;
use App\Models\BukuTamu;
use App\Models\User;
use App\Services\BookingChatManager;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Livewire\WithFileUploads;

class ChatBooking extends Page
{
    use ChecksPiketPermission;
    use WithFileUploads;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-chat-bubble-left-right';
    protected static ?string $navigationLabel = 'Chat Booking';
    protected static string|\UnitEnum|null $navigationGroup = 'Layanan Tamu';
    protected static ?string $title = 'Chat Koordinasi Staff';
    protected static ?int $navigationSort = 2;
    protected string $view = 'filament.piket.pages.chat-booking';

    public ?string $selectedChatId = null;
    public ?string $replyToMessageId = null;
    public string $messageDraft = '';
    public $attachmentDraft = null;
    public int $attachmentInputIteration = 0;
    public string $search = '';

    public static function shouldRegisterNavigation(): bool
    {
        return static::hasPiketPermission('buku_tamu');
    }

    public static function canAccess(): bool
    {
        return static::hasPiketPermission('buku_tamu');
    }

    public static function getNavigationBadge(): ?string
    {
        /** @var User|null $user */
        $user = Auth::user();

        if (!$user) {
            return null;
        }

        $count = BookingChatMessage::query()
            ->whereNull('read_at')
            ->where('is_system', false)
            ->where(function (Builder $query) use ($user) {
                $query->whereNull('sender_user_id')->orWhere('sender_user_id', '!=', $user->id);
            })
            ->whereHas('chat', function (Builder $query) use ($user) {
                // Only count messages in chats that the piket user has access to
                $query->where(function (Builder $q) use ($user) {
                    $q->where('piket_user_id', $user->id)
                      ->orWhereNull('piket_user_id'); // Also show unassigned chats
                });
            })
            ->count();

        return $count > 0 ? (string) $count : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'danger';
    }

    public function mount(BookingChatManager $chatManager): void
    {
        $chatId = (string) request()->query('chat', '');
        $bookingId = (int) request()->query('booking', 0);

        if ($chatId !== '') {
            $this->selectChat($chatId);

            return;
        }

        if ($bookingId > 0) {
            $booking = BukuTamu::query()->find($bookingId);

            if ($booking) {
                $chat = BookingChat::query()->where('buku_tamu_id', $bookingId)->first();

                if (!$chat) {
                    $chat = $chatManager->bootstrapForBooking($booking, Auth::user())->first();
                }

                if ($chat) {
                    $this->selectChat($chat->id);
                }
            }
        }

        $chat = $this->getSelectedChat();

        if ($chat) {
            $this->touchPresence($chat);
        }
    }

    public function refreshChatList(): void
    {
        $chat = $this->getSelectedChat();

        if ($chat) {
            $chat->markMessagesAsReadFor(Auth::user());
            $this->touchPresence($chat);
        }

        $this->dispatch('booking-chat-reset-input');
    }

    public function selectChat(string $chatId): void
    {
        $chat = $this->findChatById($chatId);

        if (!$chat) {
            return;
        }

        $this->selectedChatId = $chat->id;
        $this->replyToMessageId = null;
        $chat->markMessagesAsReadFor(Auth::user());
        $this->touchPresence($chat);
        $this->dispatch('booking-chat-scroll-bottom');
    }

    public function setReplyTo(string $messageId): void
    {
        $chat = $this->getSelectedChat();

        if (!$chat) {
            return;
        }

        $targetMessage = $chat->messages()
            ->where('is_system', false)
            ->whereKey($messageId)
            ->first();

        if (!$targetMessage) {
            return;
        }

        $this->replyToMessageId = $targetMessage->id;
    }

    public function clearReplyTarget(): void
    {
        $this->replyToMessageId = null;
    }

    public function markTyping(): void
    {
        $chat = $this->getSelectedChat();

        if (!$chat) {
            return;
        }

        $chat->markTypingFor(Auth::user());
        $this->touchPresence($chat);
        $this->dispatch('booking-chat-reset-input');
    }

    public function sendMessage(BookingChatManager $chatManager): void
    {
        $this->validate([
            'messageDraft' => ['nullable', 'string', 'max:2000'],
            'attachmentDraft' => ['nullable', 'file', 'max:10240', 'mimes:jpg,jpeg,png,gif,webp,bmp,pdf,doc,docx,xls,xlsx,ppt,pptx,txt,csv,zip,rar'],
        ], [
            'messageDraft.max' => 'Pesan maksimal 2000 karakter.',
            'attachmentDraft.max' => 'Ukuran berkas maksimal 10 MB.',
            'attachmentDraft.mimes' => 'Format berkas tidak didukung.',
        ]);

        $chat = $this->getSelectedChat();

        if (!$chat) {
            Notification::make()
                ->title('Thread chat tidak ditemukan')
                ->danger()
                ->send();

            return;
        }

        $messageText = trim($this->messageDraft);
        $attachmentPayload = null;

        if ($this->attachmentDraft) {
            $storedPath = $this->attachmentDraft->store('booking-chat/attachments', 'public');

            $attachmentPayload = [
                'path' => $storedPath,
                'name' => $this->attachmentDraft->getClientOriginalName(),
                'mime' => $this->attachmentDraft->getMimeType(),
                'size' => $this->attachmentDraft->getSize(),
            ];
        }

        if ($messageText === '' && !$attachmentPayload) {
            Notification::make()
                ->title('Pesan kosong')
                ->body('Ketik pesan atau pilih berkas terlebih dahulu.')
                ->warning()
                ->send();

            return;
        }

        try {
            $chatManager->sendMessage($chat, Auth::user(), $messageText, $attachmentPayload, $this->replyToMessageId);
        } catch (\InvalidArgumentException $exception) {
            Notification::make()
                ->title('Balasan tidak valid')
                ->body('Pesan yang ingin dibalas sudah tidak tersedia.')
                ->warning()
                ->send();

            $this->replyToMessageId = null;

            return;
        }

        $this->messageDraft = '';
        $this->replyToMessageId = null;
        $this->attachmentDraft = null;
        $this->attachmentInputIteration++;
        $chat->markMessagesAsReadFor(Auth::user());
        $this->touchPresence($chat->fresh());
        $this->dispatch('booking-chat-scroll-bottom');
        $this->dispatch('booking-chat-reset-input');
    }

    public function clearAttachmentDraft(): void
    {
        $this->attachmentDraft = null;
        $this->attachmentInputIteration++;
    }

    public function useQuickReply(string $message): void
    {
        $this->messageDraft = $message;
    }

    public function shareGuestContact(BookingChatManager $chatManager): void
    {
        $chat = $this->getSelectedChat();

        if (!$chat) {
            Notification::make()
                ->title('Thread chat tidak ditemukan')
                ->danger()
                ->send();

            return;
        }

        $booking = $chat->bukuTamu;
        $guestPhone = trim((string) ($booking?->nomor_hp ?? ''));

        if ($guestPhone === '') {
            Notification::make()
                ->title('Nomor tamu belum tersedia')
                ->body('Data nomor telepon tamu pada booking ini belum diisi.')
                ->warning()
                ->send();

            return;
        }

        $guestName = trim((string) ($booking?->nama_lengkap ?? 'Tamu'));
        $phoneDigits = (string) preg_replace('/\D+/', '', $guestPhone);

        if ($phoneDigits !== '') {
            if (Str::startsWith($phoneDigits, '0')) {
                $phoneDigits = '62' . substr($phoneDigits, 1);
            } elseif (!Str::startsWith($phoneDigits, '62')) {
                $phoneDigits = '62' . $phoneDigits;
            }
        }

        $contactMessage = [
            '[Kontak Tamu]',
            'Nama: ' . $guestName,
            'Nomor: ' . $guestPhone,
        ];

        if ($phoneDigits !== '') {
            $contactMessage[] = 'WhatsApp: https://wa.me/' . $phoneDigits;
        }

        $chatManager->sendMessage($chat, Auth::user(), implode(PHP_EOL, $contactMessage));

        $chat->markMessagesAsReadFor(Auth::user());
        $this->touchPresence($chat->fresh());
        $this->dispatch('booking-chat-scroll-bottom');

        Notification::make()
            ->title('Kontak tamu dibagikan')
            ->success()
            ->send();
    }

    public function getChats(): Collection
    {
        /** @var User $user */
        $user = Auth::user();

        $query = BookingChat::query()
            ->with([
                'bukuTamu:id,nama_lengkap,instansi,staff_dituju,status,created_at,foto_selfie',
                'staffUser:id,name,email',
                'piketUser:id,name,email',
                'latestMessage.sender:id,name,email',
            ])
            ->withCount([
                'messages as unread_count' => function (Builder $messageQuery) use ($user) {
                    $messageQuery
                        ->where('is_system', false)
                        ->whereNull('read_at')
                        ->where(function (Builder $query) use ($user) {
                            $query->whereNull('sender_user_id')->orWhere('sender_user_id', '!=', $user->id);
                        });
                },
            ])
            ->orderByRaw('COALESCE(last_message_at, created_at) DESC');

        if ($this->search !== '') {
            $searchTerm = '%' . $this->search . '%';

            $query->where(function (Builder $searchQuery) use ($searchTerm) {
                $searchQuery
                    ->whereHas('bukuTamu', function (Builder $bookingQuery) use ($searchTerm) {
                        $bookingQuery
                            ->where('nama_lengkap', 'like', $searchTerm)
                            ->orWhere('instansi', 'like', $searchTerm)
                            ->orWhere('staff_dituju', 'like', $searchTerm);
                    })
                    ->orWhereHas('staffUser', fn(Builder $staffQuery) => $staffQuery->where('name', 'like', $searchTerm));
            });
        }

        return $query->get();
    }

    public function getSelectedChat(): ?BookingChat
    {
        if (!$this->selectedChatId) {
            $first = $this->getChats()->first();

            if (!$first) {
                return null;
            }

            $this->selectedChatId = $first->id;
        }

        return $this->findChatById($this->selectedChatId);
    }

    public function getSelectedMessages(): Collection
    {
        $chat = $this->getSelectedChat();

        if (!$chat) {
            return collect();
        }

        return $chat->messages()
            ->with([
                'sender:id,name,email',
                'repliedTo:id,sender_user_id,message,attachment_name,is_system',
                'repliedTo.sender:id,name,email',
            ])
            ->oldest('created_at')
            ->limit(200)
            ->get();
    }

    public function getActiveReplyMessage(?BookingChat $chat = null): ?BookingChatMessage
    {
        if (!$this->replyToMessageId) {
            return null;
        }

        $chat ??= $this->getSelectedChat();

        if (!$chat) {
            return null;
        }

        $replyMessage = $chat->messages()
            ->with('sender:id,name,email')
            ->where('is_system', false)
            ->whereKey($this->replyToMessageId)
            ->first();

        if (!$replyMessage) {
            $this->replyToMessageId = null;
        }

        return $replyMessage;
    }

    public function getCounterpartState(?BookingChat $chat): array
    {
        /** @var User $authUser */
        $authUser = Auth::user();

        if (!$chat) {
            return [
                'name' => 'Staff',
                'statusText' => 'Offline',
                'isOnline' => false,
                'isTyping' => false,
                'avatarUrl' => $this->resolveUserAvatarUrl(null),
            ];
        }

        $isStaffSide = $chat->staff_user_id === $authUser->id;
        $counterpart = $isStaffSide ? $chat->piketUser : $chat->staffUser;
        $lastSeenAt = $isStaffSide ? $chat->piket_last_seen_at : $chat->staff_last_seen_at;
        $typingAt = $isStaffSide ? $chat->piket_typing_at : $chat->staff_typing_at;

        $isTyping = $typingAt && $typingAt->gte(now()->subSeconds(6));
        $isOnline = $lastSeenAt && $lastSeenAt->gte(now()->subSeconds(45));

        $statusText = match (true) {
            $isTyping => 'Sedang mengetik...',
            $isOnline => 'Online',
            (bool) $lastSeenAt => 'Terakhir aktif ' . $lastSeenAt->diffForHumans(),
            default => 'Offline',
        };

        return [
            'name' => $counterpart?->name ?? 'Staff Belum Ditemukan',
            'statusText' => $statusText,
            'isOnline' => $isOnline,
            'isTyping' => $isTyping,
            'avatarUrl' => $this->resolveUserAvatarUrl($counterpart),
        ];
    }

    public function resolveCounterpartAvatarUrl(BookingChat $chat): string
    {
        /** @var User $authUser */
        $authUser = Auth::user();

        $counterpart = $chat->staff_user_id === $authUser->id ? $chat->piketUser : $chat->staffUser;
        $counterpartAvatar = $this->resolveUserAvatarUrl($counterpart, false);

        if ($counterpartAvatar) {
            return $counterpartAvatar;
        }

        if ($chat->bukuTamu?->foto_selfie_url) {
            return $chat->bukuTamu->foto_selfie_url;
        }

        return $this->resolveUserAvatarUrl($counterpart);
    }

    public function resolveUserAvatarUrl(?User $user, bool $useFallback = true): ?string
    {
        if (!$user) {
            return $useFallback ? 'https://www.gravatar.com/avatar/00000000000000000000000000000000?d=mp&s=96' : null;
        }

        $rawPath = data_get($user, 'avatar_url')
            ?? data_get($user, 'photo_url')
            ?? data_get($user, 'profile_photo_url')
            ?? data_get($user, 'profile_photo_path')
            ?? data_get($user, 'photo')
            ?? data_get($user, 'image')
            ?? data_get($user, 'foto')
            ?? data_get($user, 'pegawai.foto');

        if (is_string($rawPath) && $rawPath !== '') {
            if (Str::startsWith($rawPath, ['http://', 'https://', 'data:image'])) {
                return $rawPath;
            }

            if (Str::startsWith($rawPath, ['/storage/', '/img/', '/'])) {
                return asset(ltrim($rawPath, '/'));
            }

            if (Storage::disk('public')->exists($rawPath)) {
                return Storage::url($rawPath);
            }
        }

        if (!$useFallback) {
            return null;
        }

        $email = strtolower(trim((string) ($user->email ?? '')));
        $hash = md5($email !== '' ? $email : 'default-avatar');

        return "https://www.gravatar.com/avatar/{$hash}?d=mp&s=96";
    }

    public function getQuickReplies(): array
    {
        return [
            'Noted, saya bantu follow up ke front desk sekarang.',
            'Mohon tunggu sebentar, staff sedang saya hubungi.',
            'Tamu sudah kami arahkan ke ruang staff terkait.',
            'Ada kendala di lapangan? Kirim detail agar kami bantu percepat.',
        ];
    }

    private function findChatById(?string $chatId): ?BookingChat
    {
        if (!$chatId) {
            return null;
        }

        /** @var User $user */
        $user = Auth::user();

        $chat = BookingChat::query()
            ->where('id', $chatId)
            ->with([
                'bukuTamu:id,nama_lengkap,instansi,staff_dituju,status,created_at,foto_selfie',
                'staffUser:id,name,email',
                'piketUser:id,name,email',
            ])
            ->first();

        if (!$chat || !$chat->canBeAccessedBy($user)) {
            return null;
        }

        return $chat;
    }

    private function touchPresence(BookingChat $chat): void
    {
        /** @var User $user */
        $user = Auth::user();

        $lastSeen = $user->id === $chat->staff_user_id ? $chat->staff_last_seen_at : $chat->piket_last_seen_at;

        if (!$lastSeen || $lastSeen->lt(now()->subSeconds(20))) {
            $chat->markPresenceFor($user);
        }
    }
}
