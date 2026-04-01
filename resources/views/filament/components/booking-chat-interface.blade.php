@php
    $chats = $this->getChats();
    $selectedChat = $this->getSelectedChat();
    $messages = $this->getSelectedMessages();
    $counterpartState = $this->getCounterpartState($selectedChat);
    $quickReplies = $this->getQuickReplies();
    $activeReplyMessage = $this->getActiveReplyMessage($selectedChat);
    $lastThreadSeparator = null;
    $lastMessageDay = null;
    $selectedBookingDateText = $selectedChat?->bukuTamu?->created_at
        ? $selectedChat->bukuTamu->created_at->locale('id')->isoFormat('dddd, D MMMM YYYY [pukul] HH:mm')
        : 'Tanggal booking belum tersedia';
    // panelLabel = who is the current user's panel (e.g. 'Piket' or 'Staff')
    // counterpart label is the opposite
    $counterpartLabel = ($panelLabel === 'Piket') ? 'Staff' : 'Piket';
@endphp

<div class="booking-chat-page" wire:poll.4s="refreshChatList" x-data="{ mobileThreadOpen: false }">
    <div class="booking-chat-shell" :class="{ 'is-room-open': mobileThreadOpen }">
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
                        $activityAt = $latestMessage?->created_at ?? $threadTimeSource;
                        $threadSeparatorKey = $threadTimeSource?->format('Y-m-d') ?? 'no-date';
                        $threadSeparatorLabel = match (true) {
                            !$threadTimeSource => 'Tanpa tanggal',
                            $threadTimeSource->isToday() => 'Hari ini',
                            $threadTimeSource->isYesterday() => 'Kemarin',
                            default => $threadTimeSource->locale('id')->isoFormat('dddd, D MMMM YYYY'),
                        };
                        $activityTimeText = match (true) {
                            !$activityAt => '--:--',
                            $activityAt->isToday() => $activityAt->format('H:i'),
                            $activityAt->isYesterday() => 'Kemarin',
                            default => $activityAt->locale('id')->isoFormat('D MMM'),
                        };
                        $activityDateFull = $activityAt
                            ? $activityAt->locale('id')->isoFormat('dddd, D MMMM YYYY [pukul] HH:mm')
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
                        x-on:click="mobileThreadOpen = true"
                        class="booking-chat-thread-item {{ $isActive ? 'is-active' : '' }}"
                        title="{{ $activityDateFull }}"
                    >
                        <div class="booking-chat-thread-main">
                            <img src="{{ $counterpartAvatar }}" alt="Avatar" class="booking-chat-avatar" loading="lazy" />

                            <div class="booking-chat-thread-body">
                                <div class="booking-chat-thread-title-row">
                                    <span class="booking-chat-thread-name">{{ $chat->bukuTamu?->nama_lengkap ?? 'Booking Tidak Ditemukan' }}</span>
                                    <span class="booking-chat-thread-date">{{ $activityTimeText }}</span>
                                </div>

                                <div class="booking-chat-thread-booking-row">
                                    <span class="booking-chat-thread-status status-{{ $statusValue }}">{{ $statusLabel }}</span>
                                    <span class="booking-chat-thread-instansi">{{ $chat->bukuTamu?->instansi ?: 'Tanpa Instansi' }}</span>
                                </div>

                                <div class="booking-chat-thread-last-row">
                                    <p class="booking-chat-thread-preview">
                                        @if($latestMessage)
                                            @if(!$latestMessage->is_system && $latestMessage->sender_user_id === auth()->id())
                                                <span class="booking-chat-thread-check {{ $latestMessage->read_at ? 'is-read' : '' }}"><span class="booking-chat-check-mark">✓</span><span class="booking-chat-check-mark">✓</span></span>
                                            @endif
                                            @if($latestMessage->hasAttachment() && $latestMessage->message === '[Lampiran]')
                                                📎 {{ $latestMessage->attachment_name ?? 'Lampiran' }}
                                            @else
                                                {{ $latestMessage->is_system ? '[Sistem] ' : '' }}{{ \Illuminate\Support\Str::limit(trim((string) preg_replace('/\s+/u', ' ', (string) $latestMessage->message)), 90) }}
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
                cameraOpen: false,
                cameraError: '',
                cameraStream: null,
                resizeComposer(el) {
                    const min = 40;
                    const max = Math.min(Math.round(window.innerHeight * 0.42), 260);
                    el.style.height = 'auto';
                    const next = Math.min(Math.max(el.scrollHeight, min), max);
                    el.style.height = next + 'px';
                    el.style.overflowY = el.scrollHeight > max ? 'auto' : 'hidden';
                },
                stopCameraStream() {
                    if (this.cameraStream) {
                        this.cameraStream.getTracks().forEach((track) => track.stop());
                        this.cameraStream = null;
                    }
                },
                async openCamera() {
                    this.cameraOpen = true;
                    this.cameraError = '';

                    if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
                        this.cameraError = 'Browser tidak mendukung akses kamera.';
                        return;
                    }

                    try {
                        const stream = await navigator.mediaDevices.getUserMedia({
                            video: {
                                facingMode: { ideal: 'environment' },
                                width: { ideal: 1280 },
                                height: { ideal: 720 },
                            },
                            audio: false,
                        });

                        this.cameraStream = stream;

                        await this.$nextTick();

                        if (this.$refs.cameraVideo) {
                            this.$refs.cameraVideo.srcObject = stream;
                            await this.$refs.cameraVideo.play();
                        }
                    } catch (error) {
                        this.cameraError = 'Kamera tidak dapat diakses. Cek izin kamera di browser.';
                        this.stopCameraStream();
                    }
                },
                closeCamera() {
                    this.stopCameraStream();
                    this.cameraOpen = false;
                    this.cameraError = '';
                },
                assignAttachmentFile(file) {
                    if (!this.$refs.attachInput || !window.DataTransfer) {
                        this.cameraError = 'Browser tidak mendukung transfer file dari kamera.';
                        return;
                    }

                    const transfer = new DataTransfer();
                    transfer.items.add(file);
                    this.$refs.attachInput.files = transfer.files;
                    this.$refs.attachInput.dispatchEvent(new Event('change', { bubbles: true }));
                },
                captureCameraPhoto() {
                    if (!this.$refs.cameraVideo || !this.$refs.cameraCanvas) {
                        return;
                    }

                    const video = this.$refs.cameraVideo;
                    const canvas = this.$refs.cameraCanvas;

                    const width = video.videoWidth || 1280;
                    const height = video.videoHeight || 720;

                    canvas.width = width;
                    canvas.height = height;

                    const context = canvas.getContext('2d');

                    if (!context) {
                        this.cameraError = 'Gagal memproses foto dari kamera.';
                        return;
                    }

                    context.drawImage(video, 0, 0, width, height);

                    canvas.toBlob((blob) => {
                        if (!blob) {
                            this.cameraError = 'Gagal mengambil foto. Coba lagi.';
                            return;
                        }

                        const timestamp = new Date().toISOString().replace(/[:.]/g, '-');
                        const file = new File([blob], `kamera-${timestamp}.jpg`, { type: 'image/jpeg' });

                        this.assignAttachmentFile(file);
                        this.closeCamera();
                    }, 'image/jpeg', 0.92);
                }
            }"
            x-on:booking-chat-scroll-bottom.window="$nextTick(() => { if ($refs.viewport) { $refs.viewport.scrollTop = $refs.viewport.scrollHeight } })"
            x-on:keydown.escape.window="if (cameraOpen) closeCamera()"
        >
            @if ($selectedChat)
                <header class="booking-chat-room-head">
                    <button
                        type="button"
                        class="booking-chat-back-btn"
                        x-on:click="mobileThreadOpen = false"
                        aria-label="Kembali ke daftar chat"
                    >
                        ←
                    </button>

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
                            $repliedToMessage = $message->repliedTo;
                        @endphp

                        @if ($messageDay !== $lastMessageDay)
                            <p class="booking-chat-message-separator">{{ $messageDayLabel }}</p>
                            @php
                                $lastMessageDay = $messageDay;
                            @endphp
                        @endif

                        <article
                            class="booking-chat-message {{ $isMine ? 'is-mine' : '' }} {{ $isSystem ? 'is-system' : '' }} {{ !$isSystem ? 'is-swipe-reply' : '' }}"
                            @if (!$isSystem)
                                x-data="{ startX: 0, startY: 0, dragX: 0, isTracking: false, isMine: @js($isMine) }"
                                x-on:touchstart.passive="
                                    if (window.innerWidth > 820) return;
                                    isTracking = true;
                                    startX = $event.touches[0].clientX;
                                    startY = $event.touches[0].clientY;
                                    dragX = 0;
                                "
                                x-on:touchmove.passive="
                                    if (!isTracking || window.innerWidth > 820) return;

                                    const touch = $event.touches[0];
                                    const deltaX = touch.clientX - startX;
                                    const deltaY = touch.clientY - startY;

                                    if (Math.abs(deltaX) <= Math.abs(deltaY) + 6) {
                                        dragX = 0;
                                        return;
                                    }

                                    const directionalDelta = isMine
                                        ? Math.min(0, deltaX)
                                        : Math.max(0, deltaX);

                                    dragX = Math.max(-64, Math.min(64, directionalDelta));
                                "
                                x-on:touchend="
                                    if (!isTracking) return;

                                    if (window.innerWidth <= 820 && Math.abs(dragX) >= 44) {
                                        $wire.setReplyTo('{{ $message->id }}');
                                        if (navigator.vibrate) {
                                            navigator.vibrate(10);
                                        }
                                    }

                                    dragX = 0;
                                    isTracking = false;
                                "
                                x-on:touchcancel="dragX = 0; isTracking = false;"
                                x-bind:style="window.innerWidth <= 820 ? `transform: translateX(${dragX}px)` : ''"
                            @endif
                        >
                            @if (!$isSystem && !$isMine)
                                <img src="{{ $senderAvatar }}" alt="Avatar" class="booking-chat-message-avatar" loading="lazy" />
                            @endif

                            <div class="booking-chat-message-bubble">
                                @if ($isSystem)
                                    <p class="booking-chat-message-system-label">Sistem</p>
                                @else
                                    <p class="booking-chat-message-sender {{ $isMine ? 'is-mine' : '' }}">{{ $message->sender?->name ?: 'Pengguna' }}</p>
                                @endif

                                @if (!$isSystem && $repliedToMessage)
                                    @php
                                        $replyPreviewText = $repliedToMessage->message === '[Lampiran]'
                                            ? '📎 ' . ($repliedToMessage->attachment_name ?: 'Lampiran')
                                            : \Illuminate\Support\Str::limit((string) $repliedToMessage->message, 95);
                                        $replySenderName = $repliedToMessage->sender?->name ?: 'Pengguna';
                                    @endphp

                                    <div class="booking-chat-reply-context {{ $isMine ? 'is-mine' : '' }}">
                                        <p class="booking-chat-reply-context-name">{{ $replySenderName }}</p>
                                        <p class="booking-chat-reply-context-text">{{ $replyPreviewText }}</p>
                                    </div>
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

                                <div class="booking-chat-message-meta">
                                    <p class="booking-chat-message-time">
                                        {{ $message->created_at?->format('H:i') }}
                                        @if(!$isSystem && $isMine)
                                            <span class="booking-chat-message-check {{ $message->read_at ? 'is-read' : '' }}"><span class="booking-chat-check-mark">✓</span><span class="booking-chat-check-mark">✓</span></span>
                                        @endif
                                    </p>
                                </div>

                                @if (!$isSystem)
                                    <button
                                        type="button"
                                        class="booking-chat-reply-btn"
                                        wire:click="setReplyTo('{{ $message->id }}')"
                                        title="Balas pesan ini"
                                        aria-label="Balas pesan ini"
                                    >↩</button>
                                @endif
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

                @if ($activeReplyMessage)
                    @php
                        $activeReplyPreviewText = $activeReplyMessage->message === '[Lampiran]'
                            ? '📎 ' . ($activeReplyMessage->attachment_name ?: 'Lampiran')
                            : \Illuminate\Support\Str::limit((string) $activeReplyMessage->message, 130);
                    @endphp

                    <div class="booking-chat-reply-draft">
                        <div class="booking-chat-reply-draft-texts">
                            <p class="booking-chat-reply-draft-title">Membalas {{ $activeReplyMessage->sender?->name ?: 'Pengguna' }}</p>
                            <p class="booking-chat-reply-draft-preview">{{ $activeReplyPreviewText }}</p>
                        </div>
                        <button
                            type="button"
                            wire:click="clearReplyTarget"
                            class="booking-chat-reply-draft-close"
                            title="Batalkan balasan"
                            aria-label="Batalkan balasan"
                        >✕</button>
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
                    @php
                        $attachmentDraftName = $attachmentDraft->getClientOriginalName();
                        $attachmentDraftExtension = strtolower(pathinfo($attachmentDraftName, PATHINFO_EXTENSION));
                        $isImageAttachmentDraft = in_array($attachmentDraftExtension, ['jpg', 'jpeg', 'png', 'gif', 'webp', 'bmp'], true);
                        $attachmentDraftPreviewUrl = $isImageAttachmentDraft ? $attachmentDraft->temporaryUrl() : null;
                    @endphp

                    <div class="booking-chat-attachment-preview">
                        @if ($isImageAttachmentDraft && $attachmentDraftPreviewUrl)
                            <img
                                src="{{ $attachmentDraftPreviewUrl }}"
                                alt="Preview lampiran"
                                class="booking-chat-attachment-preview-image"
                                loading="lazy"
                            />
                        @else
                            <span class="booking-chat-attachment-preview-icon">📎</span>
                        @endif

                        <div class="booking-chat-attachment-preview-texts">
                            <span class="booking-chat-attachment-preview-name">{{ $attachmentDraftName }}</span>
                            <span class="booking-chat-attachment-preview-hint">Siap dikirim</span>
                        </div>

                        <button type="button" wire:click="clearAttachmentDraft" class="booking-chat-attachment-preview-remove" title="Hapus lampiran">✕</button>
                    </div>
                @endif

                <div
                    x-cloak
                    x-show="cameraOpen"
                    x-transition.opacity
                    class="booking-chat-camera-modal"
                    x-on:click.self="closeCamera()"
                    aria-modal="true"
                    role="dialog"
                >
                    <div class="booking-chat-camera-dialog">
                        <div class="booking-chat-camera-head">
                            <button
                                type="button"
                                class="booking-chat-camera-close"
                                x-on:click="closeCamera()"
                                aria-label="Tutup kamera"
                                title="Tutup"
                            >✕</button>
                            <p class="booking-chat-camera-title">Ambil foto</p>
                        </div>

                        <div class="booking-chat-camera-viewport">
                            <video x-ref="cameraVideo" class="booking-chat-camera-video" autoplay playsinline muted></video>

                            <p x-show="cameraError" class="booking-chat-camera-error" x-text="cameraError"></p>
                        </div>

                        <div class="booking-chat-camera-foot">
                            <button
                                type="button"
                                class="booking-chat-camera-capture"
                                x-on:click="captureCameraPhoto()"
                                :disabled="cameraError !== ''"
                                aria-label="Ambil foto"
                                title="Ambil foto"
                            >
                                <span class="booking-chat-camera-capture-dot"></span>
                            </button>
                        </div>
                    </div>

                    <canvas x-ref="cameraCanvas" class="booking-chat-camera-canvas" aria-hidden="true"></canvas>
                </div>

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
                                x-ref="attachInput"
                                class="booking-chat-attach-input"
                            />
                        </label>

                        <button
                            type="button"
                            class="booking-chat-camera-btn"
                            x-on:click="openCamera()"
                            title="Ambil foto dari kamera"
                            aria-label="Ambil foto dari kamera"
                        ><span class="booking-chat-camera-icon" aria-hidden="true">📷</span></button>

                        @if ($panelLabel === 'Piket')
                            <button
                                type="button"
                                class="booking-chat-contact-btn"
                                wire:click="shareGuestContact"
                                wire:loading.attr="disabled"
                                wire:target="shareGuestContact"
                                title="Bagikan kontak tamu"
                                aria-label="Bagikan kontak tamu"
                            >👤</button>
                        @endif

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
