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
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Livewire\WithFileUploads;
use Symfony\Component\HttpFoundation\StreamedResponse;

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
    public ?string $editingMessageId = null;
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
            ->whereNull('deleted_for_everyone_at')
            ->whereDoesntHave('userDeletions', function (Builder $deletionQuery) use ($user) {
                $deletionQuery->where('user_id', $user->id);
            })
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
                /** @var User $user */
                $user = Auth::user();

                $chat = BookingChat::query()
                    ->where('buku_tamu_id', $bookingId)
                    ->where(function (Builder $query) use ($user) {
                        $query
                            ->where('piket_user_id', $user->id)
                            ->orWhereNull('piket_user_id');
                    })
                    ->first();

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
        $this->editingMessageId = null;
        $chat->markMessagesAsReadFor(Auth::user());
        $this->touchPresence($chat);
        $this->dispatch('booking-chat-scroll-bottom');
    }

    public function setReplyTo(string $messageId): void
    {
        $chat = $this->getSelectedChat();
        /** @var User $authUser */
        $authUser = Auth::user();

        if (!$chat) {
            return;
        }

        $targetMessage = $chat->messages()
            ->visibleForUser($authUser)
            ->whereNull('deleted_for_everyone_at')
            ->where('is_system', false)
            ->whereKey($messageId)
            ->first();

        if (!$targetMessage) {
            return;
        }

        $this->editingMessageId = null;
        $this->replyToMessageId = $targetMessage->id;
    }

    public function startEditingMessage(string $messageId): void
    {
        $chat = $this->getSelectedChat();
        /** @var User $authUser */
        $authUser = Auth::user();

        if (!$chat) {
            return;
        }

        $targetMessage = $chat->messages()
            ->visibleForUser($authUser)
            ->whereNull('deleted_for_everyone_at')
            ->where('is_system', false)
            ->whereKey($messageId)
            ->first();

        if (!$targetMessage) {
            return;
        }

        if ((string) $targetMessage->sender_user_id !== (string) $authUser->id) {
            Notification::make()
                ->title('Aksi ditolak')
                ->body('Anda hanya bisa mengedit pesan milik sendiri.')
                ->warning()
                ->send();

            return;
        }

        if ($targetMessage->hasAttachment() || $targetMessage->message === '[Lampiran]') {
            Notification::make()
                ->title('Tidak bisa diedit')
                ->body('Pesan lampiran belum bisa diedit.')
                ->warning()
                ->send();

            return;
        }

        if (!$targetMessage->isWithinEditWindow(BookingChatManager::EDIT_WINDOW_MINUTES)) {
            Notification::make()
                ->title('Batas waktu edit habis')
                ->body('Pesan hanya bisa diedit dalam 30 menit setelah dikirim.')
                ->warning()
                ->send();

            return;
        }

        $this->editingMessageId = $targetMessage->id;
        $this->replyToMessageId = null;
        $this->messageDraft = (string) $targetMessage->message;

        if ($this->attachmentDraft) {
            $this->attachmentDraft = null;
            $this->attachmentInputIteration++;
        }

        $this->dispatch('booking-chat-reset-input');
    }

    public function cancelEditingMessage(): void
    {
        $this->editingMessageId = null;
        $this->messageDraft = '';
        $this->dispatch('booking-chat-reset-input');
    }

    public function clearReplyTarget(): void
    {
        $this->replyToMessageId = null;
    }

    public function deleteMessageForMe(string $messageId): void
    {
        $chat = $this->getSelectedChat();

        if (!$chat) {
            return;
        }

        /** @var User $authUser */
        $authUser = Auth::user();

        $message = $chat->messages()
            ->where('is_system', false)
            ->whereKey($messageId)
            ->first();

        if (!$message) {
            return;
        }

        $message->userDeletions()->firstOrCreate([
            'user_id' => $authUser->id,
        ]);

        if ($this->replyToMessageId === $message->id) {
            $this->replyToMessageId = null;
        }

        if ($this->editingMessageId === $message->id) {
            $this->cancelEditingMessage();
        }

        Notification::make()
            ->title('Pesan dihapus untuk Anda')
            ->success()
            ->send();
    }

    public function deleteMessageForEveryone(string $messageId): void
    {
        $chat = $this->getSelectedChat();

        if (!$chat) {
            return;
        }

        /** @var User $authUser */
        $authUser = Auth::user();

        $message = $chat->messages()
            ->where('is_system', false)
            ->whereKey($messageId)
            ->first();

        if (!$message) {
            return;
        }

        if ($message->sender_user_id !== $authUser->id) {
            Notification::make()
                ->title('Aksi ditolak')
                ->body('Anda hanya bisa menghapus pesan milik sendiri untuk semua pengguna.')
                ->warning()
                ->send();

            return;
        }

        if ($message->isDeletedForEveryone()) {
            Notification::make()
                ->title('Pesan sudah dihapus')
                ->body('Pesan ini sudah dihapus untuk semua pengguna.')
                ->warning()
                ->send();

            return;
        }

        DB::transaction(function () use ($message, $authUser): void {
            $attachmentPath = $message->attachment_path;
            $shouldHardDeleteImageMessage = $message->hasAttachment() && $message->isImageAttachment();

            if ($shouldHardDeleteImageMessage) {
                $chat = $message->chat;

                $message->delete();

                if ($chat) {
                    $chat->forceFill([
                        'last_message_at' => $chat->messages()->max('created_at'),
                    ])->save();
                }
            } else {
                $message->forceFill([
                    'message' => '[Pesan dihapus]',
                    'attachment_path' => null,
                    'attachment_name' => null,
                    'attachment_mime' => null,
                    'attachment_size' => null,
                    'reply_to_message_id' => null,
                    'deleted_for_everyone_at' => now(),
                    'deleted_for_everyone_by' => $authUser->id,
                ])->save();
            }

            if ($attachmentPath && Storage::disk('public')->exists($attachmentPath)) {
                Storage::disk('public')->delete($attachmentPath);
            }
        });

        if ($this->replyToMessageId === $message->id) {
            $this->replyToMessageId = null;
        }

        if ($this->editingMessageId === $message->id) {
            $this->cancelEditingMessage();
        }

        Notification::make()
            ->title('Pesan dihapus untuk semua')
            ->success()
            ->send();
    }

    public function markTyping(): void
    {
        $chat = $this->getSelectedChat();

        if (!$chat) {
            return;
        }

        if (($chat->bukuTamu?->status ?? null) === BukuTamu::STATUS_SELESAI) {
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

        if ($this->editingMessageId) {
            if ($this->attachmentDraft) {
                Notification::make()
                    ->title('Mode edit aktif')
                    ->body('Batalkan edit dulu jika ingin mengirim lampiran.')
                    ->warning()
                    ->send();

                return;
            }

            if ($messageText === '') {
                Notification::make()
                    ->title('Pesan kosong')
                    ->body('Isi teks terlebih dahulu untuk menyimpan perubahan pesan.')
                    ->warning()
                    ->send();

                return;
            }

            try {
                $chatManager->editMessage($chat, Auth::user(), $this->editingMessageId, $messageText);
            } catch (\InvalidArgumentException $exception) {
                Notification::make()
                    ->title('Tidak bisa mengedit pesan')
                    ->body($exception->getMessage() !== ''
                        ? $exception->getMessage()
                        : 'Pesan ini tidak tersedia atau tidak memenuhi syarat untuk diedit.')
                    ->warning()
                    ->send();

                $this->editingMessageId = null;

                return;
            }

            $this->messageDraft = '';
            $this->editingMessageId = null;
            $this->replyToMessageId = null;
            $chat->markMessagesAsReadFor(Auth::user());
            $this->touchPresence($chat->fresh());
            $this->dispatch('booking-chat-scroll-bottom');
            $this->dispatch('booking-chat-reset-input');

            Notification::make()
                ->title('Pesan berhasil diedit')
                ->success()
                ->send();

            return;
        }

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
                ->title('Tidak bisa mengirim pesan')
                ->body($exception->getMessage() !== ''
                    ? $exception->getMessage()
                    : 'Pesan tidak bisa dikirim pada thread ini.')
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

    public function exportSelectedChat(): ?StreamedResponse
    {
        $chat = $this->getSelectedChat();

        if (!$chat) {
            Notification::make()
                ->title('Thread chat tidak ditemukan')
                ->warning()
                ->send();

            return null;
        }

        /** @var User $authUser */
        $authUser = Auth::user();

        $messages = $chat->messages()
            ->visibleForUser($authUser)
            ->with('sender:id,name,email,profile_photo_path')
            ->oldest('created_at')
            ->get();

        $guestName = trim((string) ($chat->bukuTamu?->nama_lengkap ?? 'tamu'));
        $safeGuestName = Str::slug($guestName !== '' ? $guestName : 'tamu');
        $fileName = 'chat-' . $safeGuestName . '-' . now()->format('Ymd-His') . '.txt';

        return response()->streamDownload(function () use ($chat, $messages): void {
            $output = fopen('php://output', 'w');

            if ($output === false) {
                return;
            }

            fwrite($output, "Export Chat Booking" . PHP_EOL);
            fwrite($output, "Tamu: " . ($chat->bukuTamu?->nama_lengkap ?? '-') . PHP_EOL);
            fwrite($output, "Instansi: " . ($chat->bukuTamu?->instansi ?? '-') . PHP_EOL);
            fwrite($output, str_repeat('=', 72) . PHP_EOL);

            foreach ($messages as $message) {
                $timestamp = $message->created_at?->format('d/m/Y H:i') ?? '--/--/---- --:--';
                $sender = $message->is_system ? 'Sistem' : ($message->sender?->name ?? 'Pengguna');

                if ($message->isDeletedForEveryone()) {
                    $body = '[Pesan telah dihapus]';
                } elseif ($message->hasAttachment() && $message->message === '[Lampiran]') {
                    $body = '[Lampiran] ' . ($message->attachment_name ?? 'Lampiran');
                } else {
                    $body = trim((string) $message->message);
                }

                fwrite($output, "[{$timestamp}] {$sender}: {$body}" . PHP_EOL);
            }

            fclose($output);
        }, $fileName, [
            'Content-Type' => 'text/plain; charset=UTF-8',
        ]);
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

        if (($chat->bukuTamu?->status ?? null) === BukuTamu::STATUS_SELESAI) {
            Notification::make()
                ->title('Thread sudah ditutup')
                ->body('Status booking sudah Selesai. Kontak tamu tidak bisa dibagikan lagi di thread ini.')
                ->warning()
                ->send();

            return;
        }

        $booking = BukuTamu::query()
            ->select(['id', 'nama_lengkap', 'nomor_hp'])
            ->find($chat->buku_tamu_id);

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

        try {
            $chatManager->sendMessage($chat, Auth::user(), implode(PHP_EOL, $contactMessage));
        } catch (\InvalidArgumentException $exception) {
            Notification::make()
                ->title('Tidak bisa membagikan kontak')
                ->body($exception->getMessage() !== ''
                    ? $exception->getMessage()
                    : 'Kontak tidak bisa dibagikan pada thread ini.')
                ->warning()
                ->send();

            return;
        }

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
        $searchKeyword = trim($this->search);
        $searchTerm = $searchKeyword !== '' ? '%' . $searchKeyword . '%' : null;

        $query = BookingChat::query()
            ->where(function (Builder $query) use ($user) {
                $query
                    ->where('piket_user_id', $user->id)
                    ->orWhereNull('piket_user_id');
            })
            ->with([
                'bukuTamu:id,nama_lengkap,instansi,staff_dituju,status,created_at,foto_selfie,nomor_hp',
                'staffUser:id,name,email,profile_photo_path',
                'piketUser:id,name,email,profile_photo_path',
                'latestMessage' => function ($messageQuery) use ($user) {
                    $messageQuery
                        ->whereNull('deleted_for_everyone_at')
                        ->whereDoesntHave('userDeletions', function (Builder $deletionQuery) use ($user) {
                            $deletionQuery->where('user_id', $user->id);
                        })
                        ->with('sender:id,name,email,profile_photo_path');
                },
            ])
            ->withCount([
                'messages as unread_count' => function (Builder $messageQuery) use ($user) {
                    $messageQuery
                        ->whereNull('deleted_for_everyone_at')
                        ->whereDoesntHave('userDeletions', function (Builder $deletionQuery) use ($user) {
                            $deletionQuery->where('user_id', $user->id);
                        })
                        ->where('is_system', false)
                        ->whereNull('read_at')
                        ->where(function (Builder $query) use ($user) {
                            $query->whereNull('sender_user_id')->orWhere('sender_user_id', '!=', $user->id);
                        });
                },
            ])
            ->orderByRaw('COALESCE(last_message_at, created_at) DESC');

        if ($searchTerm !== null) {

            $query->where(function (Builder $searchQuery) use ($searchTerm, $user) {
                $searchQuery
                    ->whereHas('bukuTamu', function (Builder $bookingQuery) use ($searchTerm) {
                        $bookingQuery
                            ->where('nama_lengkap', 'like', $searchTerm)
                            ->orWhere('instansi', 'like', $searchTerm)
                            ->orWhere('staff_dituju', 'like', $searchTerm);
                    })
                    ->orWhereHas('staffUser', fn(Builder $staffQuery) => $staffQuery->where('name', 'like', $searchTerm))
                    ->orWhereHas('messages', function (Builder $messageQuery) use ($searchTerm, $user) {
                        $messageQuery
                            ->visibleForUser($user)
                            ->whereNull('deleted_for_everyone_at')
                            ->where(function (Builder $messageSearchQuery) use ($searchTerm) {
                                $messageSearchQuery
                                    ->where('message', 'like', $searchTerm)
                                    ->orWhere('attachment_name', 'like', $searchTerm);
                            });
                    });
            });
        }

        $chats = $query->get();

        $chats->each(function (BookingChat $chat) use ($user, $searchTerm): void {
            if ($searchTerm !== null) {
                $latestMatchingMessage = $chat->messages()
                    ->visibleForUser($user)
                    ->whereNull('deleted_for_everyone_at')
                    ->where(function (Builder $messageSearchQuery) use ($searchTerm) {
                        $messageSearchQuery
                            ->where('message', 'like', $searchTerm)
                            ->orWhere('attachment_name', 'like', $searchTerm);
                    })
                    ->with('sender:id,name,email,profile_photo_path')
                    ->latest('created_at')
                    ->first();

                if ($latestMatchingMessage) {
                    $chat->setRelation('previewMessage', $latestMatchingMessage);

                    return;
                }
            }

            $latestVisibleMessage = $chat->messages()
                ->visibleForUser($user)
                ->whereNull('deleted_for_everyone_at')
                ->with('sender:id,name,email,profile_photo_path')
                ->latest('created_at')
                ->first();

            $chat->setRelation('previewMessage', $latestVisibleMessage);
        });

        return $chats;
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
        /** @var User $authUser */
        $authUser = Auth::user();

        if (!$chat) {
            return collect();
        }

        return $chat->messages()
            ->visibleForUser($authUser)
            ->with([
                'sender:id,name,email,profile_photo_path',
                'deletedForEveryoneBy:id,name',
                'repliedTo:id,sender_user_id,message,attachment_name,is_system,deleted_for_everyone_at',
                'repliedTo.sender:id,name,email,profile_photo_path',
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

        /** @var User $authUser */
        $authUser = Auth::user();

        $chat ??= $this->getSelectedChat();

        if (!$chat) {
            return null;
        }

        $replyMessage = $chat->messages()
            ->visibleForUser($authUser)
            ->whereNull('deleted_for_everyone_at')
            ->with('sender:id,name,email,profile_photo_path')
            ->where('is_system', false)
            ->whereKey($this->replyToMessageId)
            ->first();

        if (!$replyMessage) {
            $this->replyToMessageId = null;
        }

        return $replyMessage;
    }

    public function getActiveEditingMessage(?BookingChat $chat = null): ?BookingChatMessage
    {
        if (!$this->editingMessageId) {
            return null;
        }

        /** @var User $authUser */
        $authUser = Auth::user();

        $chat ??= $this->getSelectedChat();

        if (!$chat) {
            return null;
        }

        $editingMessage = $chat->messages()
            ->visibleForUser($authUser)
            ->whereNull('deleted_for_everyone_at')
            ->where('is_system', false)
            ->where('sender_user_id', $authUser->id)
            ->whereKey($this->editingMessageId)
            ->first();

        if (
            !$editingMessage
            || $editingMessage->hasAttachment()
            || $editingMessage->message === '[Lampiran]'
            || !$editingMessage->isWithinEditWindow(BookingChatManager::EDIT_WINDOW_MINUTES)
        ) {
            $this->editingMessageId = null;

            return null;
        }

        return $editingMessage;
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
            ->where(function (Builder $query) use ($user) {
                $query
                    ->where('piket_user_id', $user->id)
                    ->orWhereNull('piket_user_id');
            })
            ->with([
                'bukuTamu:id,nama_lengkap,instansi,staff_dituju,status,created_at,foto_selfie,nomor_hp',
                'staffUser:id,name,email,profile_photo_path',
                'piketUser:id,name,email,profile_photo_path',
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

