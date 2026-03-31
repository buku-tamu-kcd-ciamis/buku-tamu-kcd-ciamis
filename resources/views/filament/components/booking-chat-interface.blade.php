@php
    $chats = $this->getChats();
    $selectedChat = $this->getSelectedChat();
    $messages = $this->getSelectedMessages();
    $counterpartState = $this->getCounterpartState($selectedChat);
    $quickReplies = $this->getQuickReplies();
    $lastThreadSeparator = null;
    $lastMessageDay = null;
    $selectedBookingDateText = $selectedChat?->bukuTamu?->created_at
        ? $selectedChat->bukuTamu->created_at->locale('id')->isoFormat('dddd, D MMMM YYYY [pukul] HH:mm')
        : 'Tanggal booking belum tersedia';
    // panelLabel = who is the current user's panel (e.g. 'Piket' or 'Staff')
    // counterpart label is the opposite
    $counterpartLabel = ($panelLabel === 'Piket') ? 'Staff' : 'Piket';
@endphp

<div class="booking-chat-page" wire:poll.4s="refreshChatList">
    <div class="booking-chat-shell">
        <aside class="booking-chat-sidebar">
            <div class="booking-chat-sidebar-head">
                <h2 class="booking-chat-sidebar-title">Daftar Booking</h2>
                <p class="booking-chat-sidebar-subtitle">Thread koordinasi dengan tim {{ $counterpartLabel }}.</p>
            </div>

            <div class="booking-chat-search-wrap">
                <input
                    type="search"
                    wire:model.live.debounce.300ms="search"
                    placeholder="Cari tamu, instansi, atau nama staff..."
                    class="booking-chat-search"
                />
            </div>

            <div class="booking-chat-thread-list">
                @forelse ($chats as $chat)
                    @php
                        $isActive = $selectedChat?->id === $chat->id;
                        $latestMessage = $chat->latestMessage;
                        $threadTimeSource = $chat->last_message_at ?? $chat->bukuTamu?->created_at ?? $chat->created_at;
                        $threadSeparatorKey = $threadTimeSource?->format('Y-m-d') ?? 'no-date';
                        $threadSeparatorLabel = match (true) {
                            !$threadTimeSource => 'Tanpa tanggal',
                            $threadTimeSource->isToday() => 'Hari ini',
                            $threadTimeSource->isYesterday() => 'Kemarin',
                            default => $threadTimeSource->locale('id')->isoFormat('dddd, D MMMM YYYY'),
                        };
                        $bookingAt = $chat->bukuTamu?->created_at;
                        $bookingDateText = match (true) {
                            !$bookingAt => 'Tanggal tidak tersedia',
                            $bookingAt->isToday() => 'Hari ini',
                            $bookingAt->isYesterday() => 'Kemarin',
                            default => $bookingAt->locale('id')->isoFormat('ddd, D MMM'),
                        };
                        $bookingDateFull = $bookingAt
                            ? $bookingAt->locale('id')->isoFormat('dddd, D MMMM YYYY [pukul] HH:mm')
                            : 'Tanggal booking belum tersedia';
                        $statusValue = $chat->bukuTamu?->status ?? 'menunggu';
                        $statusLabel = \App\Models\BukuTamu::STATUS_LABELS[$statusValue] ?? ucfirst((string) $statusValue);
                        $counterpartAvatar = $this->resolveCounterpartAvatarUrl($chat);
                    @endphp

                    @if ($threadSeparatorKey !== $lastThreadSeparator)
                        <p class="booking-chat-thread-separator">{{ $threadSeparatorLabel }}</p>
                        @php
                            $lastThreadSeparator = $threadSeparatorKey;
                        @endphp
                    @endif

                    <button
                        type="button"
                        wire:click="selectChat('{{ $chat->id }}')"
                        class="booking-chat-thread-item {{ $isActive ? 'is-active' : '' }}"
                        title="{{ $bookingDateFull }}"
                    >
                        <div class="booking-chat-thread-main">
                            <img src="{{ $counterpartAvatar }}" alt="Avatar" class="booking-chat-avatar" loading="lazy" />

                            <div class="booking-chat-thread-body">
                                <div class="booking-chat-thread-title-row">
                                    <span class="booking-chat-thread-name">{{ $chat->bukuTamu?->nama_lengkap ?? 'Booking Tidak Ditemukan' }}</span>
                                    <span class="booking-chat-thread-date">{{ $bookingDateText }}</span>
                                </div>

                                <div class="booking-chat-thread-booking-row">
                                    <span class="booking-chat-thread-status status-{{ $statusValue }}">{{ $statusLabel }}</span>
                                    <span class="booking-chat-thread-instansi">{{ $chat->bukuTamu?->instansi ?: 'Tanpa Instansi' }}</span>
                                </div>

                                <div class="booking-chat-thread-last-row">
                                    <p class="booking-chat-thread-preview">
                                        @if($latestMessage)
                                            @if(!$latestMessage->is_system && $latestMessage->sender_user_id === auth()->id())
                                                <span class="booking-chat-thread-check {{ $latestMessage->read_at ? 'is-read' : '' }}">{{ $latestMessage->read_at ? '✓✓' : '✓' }}</span>
                                            @endif
                                            @if($latestMessage->hasAttachment() && $latestMessage->message === '[Lampiran]')
                                                📎 {{ $latestMessage->attachment_name ?? 'Lampiran' }}
                                            @else
                                                {{ $latestMessage->is_system ? '[Sistem] ' : '' }}{{ \Illuminate\Support\Str::limit($latestMessage->message, 60) }}
                                            @endif
                                        @else
                                            Belum ada pesan.
                                        @endif
                                    </p>

                                    @if (($chat->unread_count ?? 0) > 0)
                                        <span class="booking-chat-thread-unread">{{ $chat->unread_count }}</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </button>
                @empty
                    <div class="booking-chat-empty-list">
                        <div class="booking-chat-empty-icon">💬</div>
                        <p>Belum ada thread chat.</p>
                        <p class="booking-chat-empty-hint">Thread akan otomatis muncul saat booking tamu masuk dan diteruskan ke {{ $counterpartLabel }}.</p>
                    </div>
                @endforelse
            </div>
        </aside>

        <section
            class="booking-chat-room {{ $selectedChat ? '' : 'is-empty' }}"
            x-data="{
                showQuickReplies: false,
                resizeComposer(el) {
                    const min = 40;
                    const max = 132;
                    el.style.height = 'auto';
                    const next = Math.min(Math.max(el.scrollHeight, min), max);
                    el.style.height = next + 'px';
                    el.style.overflowY = el.scrollHeight > max ? 'auto' : 'hidden';
                }
            }"
            x-on:booking-chat-scroll-bottom.window="$nextTick(() => { if ($refs.viewport) { $refs.viewport.scrollTop = $refs.viewport.scrollHeight } })"
        >
            @if ($selectedChat)
                <header class="booking-chat-room-head">
                    <div class="booking-chat-peer">
                        <img src="{{ $counterpartState['avatarUrl'] }}" alt="Avatar" class="booking-chat-peer-avatar" loading="lazy" />
                        <div>
                            <h3 class="booking-chat-room-title">{{ $counterpartState['name'] }}</h3>
                            <p class="booking-chat-peer-status {{ $counterpartState['isOnline'] ? 'is-online' : '' }} {{ $counterpartState['isTyping'] ? 'is-typing' : '' }}">
                                <span class="booking-chat-peer-dot"></span>
                                {{ $counterpartState['statusText'] }}
                            </p>
                        </div>
                    </div>
                    <span class="booking-chat-status status-{{ $selectedChat->bukuTamu?->status }}">
                        {{ \App\Models\BukuTamu::STATUS_LABELS[$selectedChat->bukuTamu?->status] ?? ucfirst((string) $selectedChat->bukuTamu?->status) }}
                    </span>
                </header>

                <div class="booking-chat-booking-meta">
                    <p>
                        <strong>Booking:</strong> {{ $selectedBookingDateText }}
                        <span class="booking-chat-dot">•</span>
                        <strong>Tamu:</strong> {{ $selectedChat->bukuTamu?->nama_lengkap ?? '-' }}
                        <span class="booking-chat-dot">•</span>
                        <strong>Instansi:</strong> {{ $selectedChat->bukuTamu?->instansi ?: 'Tanpa Instansi' }}
                    </p>
                </div>

                <div class="booking-chat-messages" x-ref="viewport" x-init="$nextTick(() => { $refs.viewport.scrollTop = $refs.viewport.scrollHeight })">
                    @foreach ($messages as $message)
                        @php
                            $isMine = $message->sender_user_id === auth()->id();
                            $isSystem = $message->is_system;
                            $senderAvatar = $this->resolveUserAvatarUrl($message->sender);
                            $messageDay = $message->created_at?->format('Y-m-d') ?? 'no-date';
                            $messageDayLabel = match (true) {
                                !$message->created_at => 'Tanpa tanggal',
                                $message->created_at->isToday() => 'Hari ini',
                                $message->created_at->isYesterday() => 'Kemarin',
                                default => $message->created_at->locale('id')->isoFormat('dddd, D MMMM YYYY'),
                            };
                            $hasAttachment = $message->hasAttachment();
                            $isImage = $message->isImageAttachment();
                            $attachmentUrl = $message->attachmentUrl();
                        @endphp

                        @if ($messageDay !== $lastMessageDay)
                            <p class="booking-chat-message-separator">{{ $messageDayLabel }}</p>
                            @php
                                $lastMessageDay = $messageDay;
                            @endphp
                        @endif

                        <article class="booking-chat-message {{ $isMine ? 'is-mine' : '' }} {{ $isSystem ? 'is-system' : '' }}">
                            @if (!$isSystem && !$isMine)
                                <img src="{{ $senderAvatar }}" alt="Avatar" class="booking-chat-message-avatar" loading="lazy" />
                            @endif

                            <div class="booking-chat-message-bubble">
                                @if ($isSystem)
                                    <p class="booking-chat-message-system-label">🤖 Sistem</p>
                                @elseif (!$isMine)
                                    <p class="booking-chat-message-sender">{{ $message->sender?->name ?: 'Pengguna' }}</p>
                                @endif

                                @if ($hasAttachment && $attachmentUrl)
                                    @if ($isImage)
                                        <a href="{{ $attachmentUrl }}" target="_blank" class="booking-chat-attachment-image-wrap">
                                            <img
                                                src="{{ $attachmentUrl }}"
                                                alt="{{ $message->attachment_name ?? 'Gambar' }}"
                                                class="booking-chat-attachment-image"
                                                loading="lazy"
                                            />
                                        </a>
                                    @else
                                        <a href="{{ $attachmentUrl }}" target="_blank" download="{{ $message->attachment_name }}" class="booking-chat-attachment-file">
                                            <span class="booking-chat-attachment-icon">📎</span>
                                            <span class="booking-chat-attachment-name">{{ $message->attachment_name ?? 'Unduh Lampiran' }}</span>
                                            @if ($message->attachment_size)
                                                <span class="booking-chat-attachment-size">
                                                    {{ number_format($message->attachment_size / 1024, 1) }} KB
                                                </span>
                                            @endif
                                        </a>
                                    @endif
                                @endif

                                @if ($message->message && $message->message !== '[Lampiran]')
                                    <p class="booking-chat-message-text">{{ $message->message }}</p>
                                @endif

                                <p class="booking-chat-message-time">
                                    {{ $message->created_at?->format('d/m/Y H:i') }}
                                    @if(!$isSystem && $isMine)
                                        <span class="booking-chat-message-check {{ $message->read_at ? 'is-read' : '' }}">{{ $message->read_at ? '✓✓' : '✓' }}</span>
                                    @endif
                                </p>
                            </div>
                        </article>
                    @endforeach
                </div>

                @if ($counterpartState['isTyping'])
                    <div class="booking-chat-typing-indicator" aria-live="polite">
                        <span class="dot"></span>
                        <span class="dot"></span>
                        <span class="dot"></span>
                        <span class="label">{{ $counterpartState['name'] }} sedang mengetik...</span>
                    </div>
                @endif

                {{-- Quick Replies --}}
                @if (count($quickReplies) > 0)
                    <div class="booking-chat-quick-replies" x-show="showQuickReplies" x-cloak>
                        @foreach ($quickReplies as $reply)
                            <button
                                type="button"
                                wire:click="useQuickReply('{{ addslashes($reply) }}')"
                                x-on:click="showQuickReplies = false"
                                class="booking-chat-quick-reply-btn"
                            >{{ $reply }}</button>
                        @endforeach
                    </div>
                @endif

                {{-- Attachment Preview --}}
                @if ($attachmentDraft)
                    <div class="booking-chat-attachment-preview">
                        <span class="booking-chat-attachment-preview-name">📎 {{ $attachmentDraft->getClientOriginalName() }}</span>
                        <button type="button" wire:click="clearAttachmentDraft" class="booking-chat-attachment-preview-remove" title="Hapus lampiran">✕</button>
                    </div>
                @endif

                <form wire:submit.prevent="sendMessage" class="booking-chat-composer">
                    <div class="booking-chat-composer-tools">
                        {{-- Attachment button --}}
                        <label class="booking-chat-attach-btn" title="Lampirkan berkas">
                            📎
                            <input
                                type="file"
                                wire:model="attachmentDraft"
                                wire:key="attachment-{{ $attachmentInputIteration }}"
                                accept=".jpg,.jpeg,.png,.gif,.webp,.bmp,.pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.txt,.csv,.zip,.rar"
                                class="booking-chat-attach-input"
                            />
                        </label>

                        {{-- Quick replies toggle --}}
                        @if (count($quickReplies) > 0)
                            <button
                                type="button"
                                x-on:click="showQuickReplies = !showQuickReplies"
                                class="booking-chat-quick-reply-toggle"
                                title="Balasan cepat"
                                :class="{ 'is-active': showQuickReplies }"
                            >⚡</button>
                        @endif
                    </div>

                    <div class="booking-chat-composer-row">
                        <textarea
                            wire:model.defer="messageDraft"
                            wire:input.debounce.500ms="markTyping"
                            wire:focus="markTyping"
                            x-on:keydown="if ($event.key === 'Enter' && !$event.shiftKey) { $event.preventDefault(); $el.form.requestSubmit(); }"
                            x-on:input="resizeComposer($el)"
                            x-on:booking-chat-reset-input.window="resizeComposer($el)"
                            x-init="resizeComposer($el)"
                            rows="1"
                            maxlength="2000"
                            placeholder="Ketik pesan... (Enter kirim, Shift+Enter baris baru)"
                            class="booking-chat-textarea"
                        ></textarea>

                        <button
                            type="submit"
                            class="booking-chat-send-btn"
                            wire:loading.attr="disabled"
                            wire:target="sendMessage"
                            aria-label="Kirim pesan"
                            title="Kirim"
                        >
                            <span class="booking-chat-send-icon">➤</span>
                        </button>
                    </div>
                </form>
            @else
                <div class="booking-chat-room-empty">
                    <div class="booking-chat-room-empty-icon">💬</div>
                    <h3>Pilih Thread Booking</h3>
                    <p>Pilih salah satu booking di panel kiri untuk mulai koordinasi chat dengan tim {{ $counterpartLabel }}.</p>
                    @if ($chats->isEmpty())
                        <p class="booking-chat-room-empty-hint">Belum ada thread yang tersedia. Thread akan muncul otomatis saat tamu baru masuk.</p>
                    @endif
                </div>
            @endif
        </section>
    </div>
</div>
