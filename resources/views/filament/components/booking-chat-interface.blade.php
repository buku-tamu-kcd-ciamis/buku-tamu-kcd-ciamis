@php
    $chats = $this->getChats();
    $selectedChat = $this->getSelectedChat();
    $messages = $this->getSelectedMessages();
    $counterpartState = $this->getCounterpartState($selectedChat);
    $quickReplies = $this->getQuickReplies();
    $activeReplyMessage = $this->getActiveReplyMessage($selectedChat);
    $activeEditingMessage = $this->getActiveEditingMessage($selectedChat);
    $isChatClosed = (($selectedChat?->bukuTamu?->status ?? null) === \App\Models\BukuTamu::STATUS_SELESAI);
    $lastThreadSeparator = null;
    $lastMessageDay = null;
    $selectedBookingDateText = $selectedChat?->bukuTamu?->created_at
        ? $selectedChat->bukuTamu->created_at->locale('id')->isoFormat('dddd, D MMMM YYYY [pukul] HH:mm')
        : 'Tanggal booking belum tersedia';
    $latestIncomingMessage = $messages
        ->filter(fn ($message) => !$message->is_system
            && !$message->isDeletedForEveryone()
            && (string) $message->sender_user_id !== (string) auth()->id())
        ->sortBy('created_at')
        ->last();
    $latestIncomingPreview = '';

    if ($latestIncomingMessage) {
        if ($latestIncomingMessage->hasAttachment() && $latestIncomingMessage->message === '[Lampiran]') {
            $latestIncomingPreview = '[Lampiran] ' . ($latestIncomingMessage->attachment_name ?? 'Lampiran');
        } else {
            $latestIncomingPreview = \Illuminate\Support\Str::limit(trim((string) preg_replace('/\s+/u', ' ', (string) $latestIncomingMessage->message)), 120);
        }
    }
    // panelLabel = who is the current user's panel (e.g. 'Piket' or 'Staff')
    // counterpart label is the opposite
    $counterpartLabel = ($panelLabel === 'Piket') ? 'Staff' : 'Piket';
@endphp

<div
    class="booking-chat-page"
    x-data="{
        mobileThreadOpen: false,
        pausePolling: false,
        pollingTimer: null,
        startPolling() {
            if (this.pollingTimer) {
                clearInterval(this.pollingTimer);
            }

            this.pollingTimer = setInterval(() => {
                if (!this.pausePolling) {
                    const refreshRequest = this.$wire.refreshChatList();

                    if (refreshRequest && typeof refreshRequest.finally === 'function') {
                        refreshRequest.finally(() => {
                            window.dispatchEvent(new CustomEvent('booking-chat-refreshed'));
                        });

                        return;
                    }

                    window.dispatchEvent(new CustomEvent('booking-chat-refreshed'));
                }
            }, 4000);
        },
        stopPolling() {
            if (this.pollingTimer) {
                clearInterval(this.pollingTimer);
                this.pollingTimer = null;
            }
        }
    }"
    x-init="startPolling()"
    x-on:booking-chat-pause-polling.window="pausePolling = !!($event.detail && $event.detail.paused)"
>
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
                        $latestMessage = $chat->previewMessage ?? $chat->latestMessage;
                        $threadTimeSource = $latestMessage?->edited_at ?? $latestMessage?->created_at ?? $chat->last_message_at ?? $chat->bukuTamu?->created_at ?? $chat->created_at;
                        $activityAt = $latestMessage?->edited_at ?? $latestMessage?->created_at ?? $threadTimeSource;
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
                                            @if($latestMessage->isDeletedForEveryone())
                                                Pesan telah dihapus.
                                            @elseif($latestMessage->hasAttachment() && $latestMessage->message === '[Lampiran]')
                                                <x-heroicon-m-paper-clip class="inline-block w-4 h-4 mr-0.5" /> {{ $latestMessage->attachment_name ?? 'Lampiran' }}
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
                        <div class="booking-chat-empty-icon">
                            <x-heroicon-o-chat-bubble-left-right class="w-12 h-12 mx-auto text-gray-300" />
                        </div>
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
                cameraFacingMode: 'user',
                cameraSwitching: false,
                cameraNeedsFlip: false,
                cameraManualFlip: null,
                cameraFlipStorageKey: 'booking-chat-camera-unmirror',
                dragCounter: 0,
                isDropActive: false,
                dropNotice: '',
                dropNoticeType: 'info',
                dropNoticeTimer: null,
                isAttachmentUploading: false,
                serverSafeAttachmentMaxBytes: 600 * 1024,
                notificationPermission: 'default',
                notificationPromptVisible: false,
                notificationPromptDismissStorageKey: 'booking-chat-notification-dismissed',
                lastObservedIncomingByChat: {},
                lastNotifiedIncomingByChat: {},
                notificationRegistration: null,
                notificationClickListenerBound: false,
                webPushPublicKey: @js((string) config('webpush.vapid.public_key', '')),
                webPushSubscribeUrl: @js(route('web-push.subscribe')),
                webPushUnsubscribeUrl: @js(route('web-push.unsubscribe')),
                pushSubscriptionEndpoint: '',
                pushSubscriptionSyncInFlight: false,
                imagePreviewOpen: false,
                imagePreviewUrl: '',
                imagePreviewName: '',
                deleteConfirmOpen: false,
                deleteConfirmMode: 'me',
                deleteConfirmMessageId: null,
                resizeComposer(el) {
                    const min = 40;
                    const max = Math.min(Math.round(window.innerHeight * 0.42), 260);
                    el.style.height = 'auto';
                    const next = Math.min(Math.max(el.scrollHeight, min), max);
                    el.style.height = next + 'px';
                    el.style.overflowY = el.scrollHeight > max ? 'auto' : 'hidden';
                },
                openDeleteConfirm(mode, messageId) {
                    this.deleteConfirmMode = mode;
                    this.deleteConfirmMessageId = messageId;
                    this.deleteConfirmOpen = true;
                },
                closeDeleteConfirm() {
                    this.deleteConfirmOpen = false;
                    this.deleteConfirmMode = 'me';
                    this.deleteConfirmMessageId = null;
                },
                runDeleteConfirmed() {
                    if (!this.deleteConfirmMessageId) {
                        this.closeDeleteConfirm();
                        return;
                    }

                    if (this.deleteConfirmMode === 'everyone') {
                        this.$wire.deleteMessageForEveryone(this.deleteConfirmMessageId);
                    } else {
                        this.$wire.deleteMessageForMe(this.deleteConfirmMessageId);
                    }

                    this.closeDeleteConfirm();
                },
                openImagePreview(url, name = 'Preview gambar') {
                    if (!url) {
                        return;
                    }

                    this.imagePreviewUrl = url;
                    this.imagePreviewName = name || 'Preview gambar';
                    this.imagePreviewOpen = true;
                    this.$dispatch('booking-chat-pause-polling', { paused: true });
                },
                closeImagePreview() {
                    this.imagePreviewOpen = false;
                    this.imagePreviewUrl = '';
                    this.imagePreviewName = '';
                    this.$dispatch('booking-chat-pause-polling', { paused: false });
                },
                isSafariLikeBrowser() {
                    const ua = (navigator.userAgent || '').toLowerCase();
                    const isSafari = ua.includes('safari')
                        && !ua.includes('chrome')
                        && !ua.includes('crios')
                        && !ua.includes('android')
                        && !ua.includes('edg')
                        && !ua.includes('opr');
                    const isIos = /iphone|ipad|ipod/.test(ua);

                    return isSafari || isIos;
                },
                downloadAttachment(url, fileName = 'Lampiran') {
                    if (!url) {
                        return;
                    }

                    // Safari/iOS tends to block async popup downloads; same-tab navigation is the most reliable.
                    if (this.isSafariLikeBrowser()) {
                        window.location.assign(url);

                        return;
                    }

                    const link = document.createElement('a');
                    link.href = url;
                    link.target = '_blank';
                    link.rel = 'noopener noreferrer';

                    if (fileName) {
                        link.setAttribute('download', fileName);
                    }

                    document.body.appendChild(link);
                    link.click();
                    link.remove();
                },
                showDropNotice(message, type = 'info') {
                    this.dropNotice = message;
                    this.dropNoticeType = type;

                    if (this.dropNoticeTimer) {
                        clearTimeout(this.dropNoticeTimer);
                    }

                    this.dropNoticeTimer = setTimeout(() => {
                        this.dropNotice = '';
                    }, 3200);
                },
                syncNotificationPermissionState() {
                    if (!('Notification' in window)) {
                        this.notificationPermission = 'unsupported';
                        this.notificationPromptVisible = false;

                        return;
                    }

                    this.notificationPermission = Notification.permission;

                    if (this.notificationPermission === 'granted') {
                        this.notificationPromptVisible = false;

                        return;
                    }

                    const dismissedAt = Number(window.localStorage.getItem(this.notificationPromptDismissStorageKey) || 0);
                    const dismissDurationMs = 1000 * 60 * 60 * 12; // 12 jam
                    const isDismissed = dismissedAt && (Date.now() - dismissedAt < dismissDurationMs);

                    this.notificationPromptVisible = !isDismissed;
                },
                async initNotificationChannel() {
                    this.syncNotificationPermissionState();

                    if (!('serviceWorker' in navigator)) {
                        this.notificationRegistration = null;

                        return;
                    }

                    try {
                        this.notificationRegistration = await this.ensureServiceWorkerRegistration();

                        if (this.notificationPermission === 'granted') {
                            await this.ensureWebPushSubscription();
                        }
                    } catch (error) {
                        this.notificationRegistration = null;
                    }
                },
                getNormalizedVapidPublicKey() {
                    return String(this.webPushPublicKey || '').replace(/\s+/g, '');
                },
                urlBase64ToUint8Array(base64String) {
                    const padding = '='.repeat((4 - (base64String.length % 4)) % 4);
                    const normalized = (base64String + padding)
                        .replace(/-/g, '+')
                        .replace(/_/g, '/');
                    const decoded = window.atob(normalized);
                    const outputArray = new Uint8Array(decoded.length);

                    for (let i = 0; i < decoded.length; i += 1) {
                        outputArray[i] = decoded.charCodeAt(i);
                    }

                    return outputArray;
                },
                getCsrfToken() {
                    return document.querySelector('meta[name=csrf-token]')?.getAttribute('content') || '';
                },
                isWebPushConfigured() {
                    return this.getNormalizedVapidPublicKey() !== ''
                        && this.webPushSubscribeUrl !== ''
                        && this.webPushUnsubscribeUrl !== '';
                },
                async ensureServiceWorkerRegistration() {
                    if (!('serviceWorker' in navigator)) {
                        throw new Error('SERVICE_WORKER_UNSUPPORTED');
                    }

                    const existingRegistration = await navigator.serviceWorker.getRegistration();

                    if (existingRegistration) {
                        return existingRegistration;
                    }

                    const registration = await navigator.serviceWorker.register('{{ asset('sw.js') }}');

                    return registration;
                },
                async registerWebPushSubscription(subscription) {
                    const payload = subscription?.toJSON?.();
                    const csrfToken = this.getCsrfToken();

                    if (!payload?.endpoint || !payload?.keys?.p256dh || !payload?.keys?.auth) {
                        throw new Error('INVALID_SUBSCRIPTION_PAYLOAD');
                    }

                    if (!csrfToken) {
                        throw new Error('CSRF_TOKEN_MISSING');
                    }

                    const response = await fetch(this.webPushSubscribeUrl, {
                        method: 'POST',
                        credentials: 'same-origin',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': csrfToken,
                        },
                        body: JSON.stringify({
                            endpoint: payload.endpoint,
                            keys: payload.keys,
                            contentEncoding: payload.contentEncoding || 'aes128gcm',
                        }),
                    });

                    if (!response.ok) {
                        let errorBody = '';

                        try {
                            errorBody = await response.text();
                        } catch (_error) {
                            errorBody = '';
                        }

                        const responseError = new Error('REGISTER_PUSH_SUBSCRIPTION_FAILED');
                        responseError.statusCode = response.status;
                        responseError.responseBody = errorBody;

                        throw responseError;
                    }

                    this.pushSubscriptionEndpoint = String(payload.endpoint);
                },
                async unregisterWebPushSubscription(endpoint) {
                    if (!endpoint) {
                        return;
                    }

                    await fetch(this.webPushUnsubscribeUrl, {
                        method: 'DELETE',
                        credentials: 'same-origin',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': this.getCsrfToken(),
                        },
                        body: JSON.stringify({ endpoint }),
                    });
                },
                describePushSyncError(error) {
                    const statusCode = Number(error?.statusCode || 0);
                    const messageText = String(error?.message || '').toLowerCase();

                    if (statusCode === 401 || statusCode === 403) {
                        return 'Sesi login tidak valid untuk sinkronisasi push. Silakan login ulang.';
                    }

                    if (statusCode === 419 || messageText.includes('csrf')) {
                        return 'Token keamanan kadaluarsa. Refresh halaman lalu coba aktifkan notifikasi lagi.';
                    }

                    if (statusCode === 422) {
                        return 'Format data push subscription tidak valid. Coba nonaktifkan lalu aktifkan lagi notifikasi browser.';
                    }

                    if (messageText.includes('invalidcharactererror') || messageText.includes('atob')) {
                        return 'Konfigurasi VAPID public key tidak valid. Periksa WEB_PUSH_VAPID_PUBLIC_KEY di server.';
                    }

                    if (messageText.includes('notallowederror') || messageText.includes('permission denied')) {
                        return 'Izin notifikasi diblokir browser. Aktifkan kembali dari Site Settings.';
                    }

                    if (messageText.includes('aborterror') || messageText.includes('network')) {
                        return 'Koneksi jaringan bermasalah saat sinkronisasi push. Coba lagi beberapa saat.';
                    }

                    return 'Notifikasi push belum aktif penuh. Refresh halaman lalu coba aktifkan lagi.';
                },
                async ensureWebPushSubscription() {
                    if (
                        this.pushSubscriptionSyncInFlight
                        || this.notificationPermission !== 'granted'
                        || !this.isWebPushConfigured()
                        || !('serviceWorker' in navigator)
                        || !('PushManager' in window)
                    ) {
                        return false;
                    }

                    this.pushSubscriptionSyncInFlight = true;

                    try {
                        const registration = this.notificationRegistration || await this.ensureServiceWorkerRegistration();
                        this.notificationRegistration = registration;
                        const vapidPublicKey = this.getNormalizedVapidPublicKey();

                        let subscription = await registration.pushManager.getSubscription();

                        if (
                            subscription
                            && this.pushSubscriptionEndpoint !== ''
                            && this.pushSubscriptionEndpoint !== String(subscription.endpoint || '')
                        ) {
                            try {
                                await subscription.unsubscribe();
                            } catch (_unsubscribeError) {
                                // Ignore cleanup failure and continue to resubscribe.
                            }

                            subscription = null;
                        }

                        if (!subscription) {
                            subscription = await registration.pushManager.subscribe({
                                userVisibleOnly: true,
                                applicationServerKey: this.urlBase64ToUint8Array(vapidPublicKey),
                            });
                        }

                        try {
                            await this.registerWebPushSubscription(subscription);
                        } catch (registrationError) {
                            const statusCode = Number(registrationError?.statusCode || 0);

                            if (statusCode === 401 || statusCode === 403 || statusCode === 419) {
                                throw registrationError;
                            }

                            // Recover from stale browser subscription by recreating once.
                            if (subscription) {
                                try {
                                    await this.unregisterWebPushSubscription(subscription.endpoint);
                                    await subscription.unsubscribe();
                                } catch (_cleanupError) {
                                    // Ignore cleanup failure; continue with a fresh subscribe attempt.
                                }

                                const freshSubscription = await registration.pushManager.subscribe({
                                    userVisibleOnly: true,
                                    applicationServerKey: this.urlBase64ToUint8Array(vapidPublicKey),
                                });

                                await this.registerWebPushSubscription(freshSubscription);
                            } else {
                                throw registrationError;
                            }
                        }

                        return true;
                    } catch (error) {
                        this.showDropNotice(this.describePushSyncError(error), 'error');

                        return false;
                    } finally {
                        this.pushSubscriptionSyncInFlight = false;
                    }
                },
                registerNotificationClickHandler() {
                    if (this.notificationClickListenerBound || !('serviceWorker' in navigator)) {
                        return;
                    }

                    navigator.serviceWorker.addEventListener('message', (event) => {
                        const payload = event?.data || {};

                        if (payload.type !== 'BOOKING_CHAT_NOTIFICATION_CLICK') {
                            return;
                        }

                        if (payload.chatId) {
                            this.$wire.selectChat(payload.chatId);
                        }

                        window.focus();
                    });

                    this.notificationClickListenerBound = true;
                },
                dismissNotificationPrompt() {
                    window.localStorage.setItem(this.notificationPromptDismissStorageKey, String(Date.now()));
                    this.notificationPromptVisible = false;
                },
                detectBrowserFamily() {
                    const ua = (navigator.userAgent || '').toLowerCase();

                    if (ua.includes('edg/')) {
                        return 'edge';
                    }

                    if (ua.includes('brave')) {
                        return 'brave';
                    }

                    if (ua.includes('chrome') && !ua.includes('edg/') && !ua.includes('opr/') && !ua.includes('brave')) {
                        return 'chrome';
                    }

                    if (ua.includes('firefox')) {
                        return 'firefox';
                    }

                    if (ua.includes('safari') && !ua.includes('chrome')) {
                        return 'safari';
                    }

                    return 'unknown';
                },
                getNotificationSettingsUrlForBrowser() {
                    const encodedOrigin = encodeURIComponent(window.location.origin);
                    const browser = this.detectBrowserFamily();

                    if (browser === 'edge') {
                        return `edge://settings/content/siteDetails?site=${encodedOrigin}`;
                    }

                    if (browser === 'brave') {
                        return `brave://settings/content/siteDetails?site=${encodedOrigin}`;
                    }

                    if (browser === 'chrome') {
                        return `chrome://settings/content/siteDetails?site=${encodedOrigin}`;
                    }

                    if (browser === 'firefox') {
                        return 'about:preferences#privacy';
                    }

                    if (browser === 'safari') {
                        return null;
                    }

                    return null;
                },
                openBrowserNotificationSettings() {
                    const settingsUrl = this.getNotificationSettingsUrlForBrowser();
                    const browser = this.detectBrowserFamily();

                    if (settingsUrl) {
                        let opened = false;

                        try {
                            const popup = window.open(settingsUrl, '_blank', 'noopener');
                            opened = popup !== null;
                        } catch (_error) {
                            opened = false;
                        }

                        if (!opened) {
                            try {
                                window.location.href = settingsUrl;
                                opened = true;
                            } catch (_error) {
                                opened = false;
                            }
                        }

                        if (opened) {
                            this.showDropNotice('Pengaturan izin notifikasi browser dibuka. Ubah izin situs ini ke Allow, lalu kembali ke chat.', 'info');

                            return;
                        }
                    }

                    if (browser === 'safari') {
                        this.showDropNotice('Safari: buka Site Settings > Notifications untuk situs ini, lalu ubah ke Allow.', 'info');

                        return;
                    }

                    this.showDropNotice('Buka pengaturan browser > Site Settings > Notifications, lalu izinkan situs ini.', 'info');
                },
                async requestBrowserNotificationPermission() {
                    if (!('Notification' in window)) {
                        this.showDropNotice('Browser tidak mendukung notifikasi web.', 'error');

                        return;
                    }

                    if (!this.isWebPushConfigured()) {
                        this.showDropNotice('Notifikasi belum aktif. Isi WEB_PUSH_VAPID_PUBLIC_KEY dan WEB_PUSH_VAPID_PRIVATE_KEY di .env.', 'error');

                        return;
                    }

                    if (this.notificationPermission === 'denied') {
                        this.openBrowserNotificationSettings();

                        return;
                    }

                    const permission = await Notification.requestPermission();
                    this.notificationPermission = permission;

                    if (permission === 'granted') {
                        await this.initNotificationChannel();
                        const isSubscribed = await this.ensureWebPushSubscription();

                        if (!isSubscribed) {
                            this.showDropNotice('Izin browser sudah aktif, tapi sinkronisasi push gagal. Cek konfigurasi VAPID lalu refresh.', 'error');

                            return;
                        }

                        window.localStorage.removeItem(this.notificationPromptDismissStorageKey);
                        this.notificationPromptVisible = false;
                        this.showDropNotice('Notifikasi chat HP berhasil diaktifkan.', 'success');

                        return;
                    }

                    this.showDropNotice('Izin notifikasi belum diberikan.', 'info');
                    this.syncNotificationPermissionState();
                },
                getIncomingMessageMarkerPayload() {
                    const marker = this.$refs.incomingMessageMarker;

                    if (!marker) {
                        return null;
                    }

                    return {
                        chatId: String(marker.dataset.chatId || ''),
                        messageId: String(marker.dataset.messageId || ''),
                        senderName: String(marker.dataset.senderName || 'Pengguna'),
                        preview: String(marker.dataset.preview || 'Pesan baru masuk.'),
                    };
                },
                async showSystemNotification(payload) {
                    if (this.notificationPermission !== 'granted') {
                        return false;
                    }

                    const title = `Pesan baru dari ${payload.senderName}`;
                    const options = {
                        body: payload.preview,
                        icon: '{{ asset('img/logo-cadisdik.png') }}',
                        badge: '{{ asset('img/logo-cadisdik.png') }}',
                        tag: `booking-chat-${payload.chatId}`,
                        renotify: true,
                        vibrate: [120, 60, 120],
                        data: {
                            chatId: payload.chatId,
                            url: window.location.href,
                        },
                    };

                    if (this.notificationRegistration && typeof this.notificationRegistration.showNotification === 'function') {
                        await this.notificationRegistration.showNotification(title, options);

                        return true;
                    }

                    if ('Notification' in window) {
                        const fallbackNotification = new Notification(title, options);

                        fallbackNotification.onclick = () => {
                            window.focus();
                            this.$wire.selectChat(payload.chatId);
                            fallbackNotification.close();
                        };

                        return true;
                    }

                    return false;
                },
                syncIncomingMessageState(shouldNotify = false) {
                    const payload = this.getIncomingMessageMarkerPayload();

                    if (!payload || payload.chatId === '' || payload.messageId === '') {
                        return;
                    }

                    const previousIncomingId = this.lastObservedIncomingByChat[payload.chatId];

                    if (!previousIncomingId) {
                        this.lastObservedIncomingByChat[payload.chatId] = payload.messageId;

                        return;
                    }

                    if (previousIncomingId === payload.messageId) {
                        return;
                    }

                    this.lastObservedIncomingByChat[payload.chatId] = payload.messageId;

                    if (!shouldNotify || this.notificationPermission !== 'granted') {
                        return;
                    }

                    if (this.lastNotifiedIncomingByChat[payload.chatId] === payload.messageId) {
                        return;
                    }

                    this.lastNotifiedIncomingByChat[payload.chatId] = payload.messageId;

                    this.showSystemNotification(payload).catch(() => {
                        this.showDropNotice('Gagal menampilkan notifikasi HP.', 'error');
                    });
                },
                getAllowedAttachmentExtensions() {
                    return ['jpg', 'jpeg', 'png', 'gif', 'webp', 'bmp', 'pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx', 'txt', 'csv', 'zip', 'rar'];
                },
                validateAttachmentFile(file) {
                    if (!file) {
                        this.showDropNotice('File tidak ditemukan.', 'error');

                        return false;
                    }

                    const maxBytes = 10 * 1024 * 1024;

                    if ((file.size || 0) > maxBytes) {
                        this.showDropNotice('Ukuran berkas maksimal 10 MB.', 'error');

                        return false;
                    }

                    const fileName = String(file.name || '').toLowerCase();
                    const extension = fileName.includes('.') ? fileName.split('.').pop() : '';

                    if (extension && !this.getAllowedAttachmentExtensions().includes(extension)) {
                        this.showDropNotice('Format berkas tidak didukung.', 'error');

                        return false;
                    }

                    return true;
                },
                humanizeBytes(bytes) {
                    const value = Number(bytes || 0);

                    if (value < 1024) {
                        return `${value} B`;
                    }

                    if (value < 1024 * 1024) {
                        return `${(value / 1024).toFixed(1)} KB`;
                    }

                    return `${(value / (1024 * 1024)).toFixed(2)} MB`;
                },
                isCompressibleImage(file) {
                    const mime = String(file?.type || '').toLowerCase();

                    return ['image/jpeg', 'image/jpg', 'image/png', 'image/webp', 'image/bmp'].includes(mime);
                },
                async loadImageElementFromFile(file) {
                    return await new Promise((resolve, reject) => {
                        const reader = new FileReader();

                        reader.onerror = () => reject(new Error('READ_FILE_FAILED'));
                        reader.onload = () => {
                            const image = new Image();
                            image.onload = () => resolve(image);
                            image.onerror = () => reject(new Error('READ_IMAGE_FAILED'));
                            image.src = String(reader.result || '');
                        };

                        reader.readAsDataURL(file);
                    });
                },
                canvasToBlob(canvas, mimeType, quality) {
                    return new Promise((resolve) => {
                        canvas.toBlob((blob) => resolve(blob), mimeType, quality);
                    });
                },
                async compressImageFileToSafeSize(file, targetBytes) {
                    const sourceImage = await this.loadImageElementFromFile(file);

                    const maxDimension = 1920;
                    const widthRatio = maxDimension / Math.max(sourceImage.width || 1, 1);
                    const heightRatio = maxDimension / Math.max(sourceImage.height || 1, 1);
                    const baseScale = Math.min(1, widthRatio, heightRatio);

                    const scaleCandidates = [baseScale, baseScale * 0.9, baseScale * 0.8, baseScale * 0.7, baseScale * 0.6]
                        .map((value) => Math.max(0.35, Math.min(1, Number(value || 1))));
                    const qualityCandidates = [0.88, 0.8, 0.72, 0.64, 0.56, 0.48, 0.42];
                    const outputMime = 'image/jpeg';

                    let bestBlob = null;

                    for (const scale of scaleCandidates) {
                        const nextWidth = Math.max(320, Math.round((sourceImage.width || 1) * scale));
                        const nextHeight = Math.max(240, Math.round((sourceImage.height || 1) * scale));
                        const canvas = document.createElement('canvas');
                        canvas.width = nextWidth;
                        canvas.height = nextHeight;
                        const context = canvas.getContext('2d');

                        if (!context) {
                            continue;
                        }

                        context.drawImage(sourceImage, 0, 0, nextWidth, nextHeight);

                        for (const quality of qualityCandidates) {
                            const blob = await this.canvasToBlob(canvas, outputMime, quality);

                            if (!blob) {
                                continue;
                            }

                            if (!bestBlob || blob.size < bestBlob.size) {
                                bestBlob = blob;
                            }

                            if (blob.size <= targetBytes) {
                                const originalName = String(file.name || 'gambar.jpg');
                                const sanitizedBase = originalName.replace(/\.[^.]+$/u, '');

                                return new File([blob], `${sanitizedBase}.jpg`, {
                                    type: outputMime,
                                    lastModified: Date.now(),
                                });
                            }
                        }
                    }

                    if (!bestBlob) {
                        return null;
                    }

                    const originalName = String(file.name || 'gambar.jpg');
                    const sanitizedBase = originalName.replace(/\.[^.]+$/u, '');

                    return new File([bestBlob], `${sanitizedBase}.jpg`, {
                        type: outputMime,
                        lastModified: Date.now(),
                    });
                },
                async prepareAttachmentFile(file) {
                    if (!this.validateAttachmentFile(file)) {
                        return null;
                    }

                    const safeMaxBytes = this.serverSafeAttachmentMaxBytes;
                    const originalBytes = Number(file.size || 0);

                    if (originalBytes <= safeMaxBytes) {
                        return {
                            file,
                            wasCompressed: false,
                            originalBytes,
                        };
                    }

                    if (!this.isCompressibleImage(file)) {
                        this.showDropNotice(`Berkas non-gambar maksimal ${this.humanizeBytes(safeMaxBytes)} di server online. Kompres file dulu lalu unggah ulang.`, 'error');

                        return null;
                    }

                    try {
                        const compressed = await this.compressImageFileToSafeSize(file, safeMaxBytes);

                        if (!compressed) {
                            this.showDropNotice('Gagal mengompres gambar. Coba gambar lain atau kompres manual.', 'error');

                            return null;
                        }

                        if (compressed.size > safeMaxBytes) {
                            this.showDropNotice(`Gambar masih terlalu besar setelah kompresi. Target maksimal ${this.humanizeBytes(safeMaxBytes)}.`, 'error');

                            return null;
                        }

                        return {
                            file: compressed,
                            wasCompressed: true,
                            originalBytes,
                        };
                    } catch (error) {
                        this.showDropNotice('Gagal memproses kompresi gambar di browser.', 'error');

                        return null;
                    }
                },
                async handleAttachmentInputChange(event) {
                    const input = event?.target;
                    const pickedFile = input?.files?.[0] || null;

                    if (!input || !pickedFile) {
                        return;
                    }

                    event.preventDefault();
                    event.stopPropagation();
                    if (typeof event.stopImmediatePropagation === 'function') {
                        event.stopImmediatePropagation();
                    }

                    const prepared = await this.prepareAttachmentFile(pickedFile);

                    if (!prepared) {
                        input.value = '';

                        return;
                    }

                    const uploaded = await this.uploadPreparedAttachment(prepared.file);

                    input.value = '';

                    if (!uploaded) {
                        return;
                    }

                    if (prepared.wasCompressed) {
                        this.showDropNotice(`Gambar dikompres otomatis (${this.humanizeBytes(prepared.originalBytes)} -> ${this.humanizeBytes(prepared.file.size)}).`, 'success');
                    } else {
                        this.showDropNotice(`Lampiran ${prepared.file.name || ''} siap dikirim.`.trim(), 'success');
                    }
                },
                stopCameraStream() {
                    if (this.cameraStream) {
                        this.cameraStream.getTracks().forEach((track) => track.stop());
                        this.cameraStream = null;
                    }

                    this.cameraNeedsFlip = false;
                    this.cameraManualFlip = null;
                },
                loadCameraFlipPreference() {
                    try {
                        const saved = localStorage.getItem(this.cameraFlipStorageKey);
                        if (saved === '1') {
                            this.cameraManualFlip = true;
                        } else if (saved === '0') {
                            this.cameraManualFlip = false;
                        }
                    } catch (error) {
                        this.cameraManualFlip = null;
                    }
                },
                saveCameraFlipPreference(value) {
                    try {
                        localStorage.setItem(this.cameraFlipStorageKey, value ? '1' : '0');
                    } catch (error) {
                        // Ignore storage errors in restricted browser contexts.
                    }
                },
                shouldUnmirrorFrontCamera(stream) {
                    const track = stream?.getVideoTracks?.()[0];

                    if (!track || !track.getSettings) {
                        return true;
                    }

                    const settings = track.getSettings();
                    const label = String(track.label || '').toLowerCase();

                    if (label.includes('front') || label.includes('facetime') || label.includes('user')) {
                        return true;
                    }

                    if (label.includes('back') || label.includes('rear') || label.includes('environment')) {
                        return false;
                    }

                    if (settings.facingMode) {
                        return settings.facingMode === 'user';
                    }

                    // Desktop webcam umumnya front-facing dan sering tampil mirror.
                    return true;
                },
                detectFacingModeFromStream(stream, fallback = 'user') {
                    const normalizedFallback = fallback === 'environment' ? 'environment' : 'user';
                    const track = stream?.getVideoTracks?.()[0];

                    if (!track) {
                        return normalizedFallback;
                    }

                    const settingsFacingMode = track.getSettings?.()?.facingMode;

                    if (settingsFacingMode === 'environment' || settingsFacingMode === 'user') {
                        return settingsFacingMode;
                    }

                    const label = String(track.label || '').toLowerCase();

                    if (label.includes('back') || label.includes('rear') || label.includes('environment')) {
                        return 'environment';
                    }

                    if (label.includes('front') || label.includes('facetime') || label.includes('user')) {
                        return 'user';
                    }

                    return normalizedFallback;
                },
                getEffectiveCameraFlip() {
                    return this.cameraManualFlip === null ? this.cameraNeedsFlip : this.cameraManualFlip;
                },
                applyCameraMirrorFix() {
                    if (!this.$refs.cameraVideo) {
                        return;
                    }

                    const transformValue = this.getEffectiveCameraFlip() ? 'scaleX(-1)' : 'scaleX(1)';
                    this.$refs.cameraVideo.style.setProperty('transform', transformValue, 'important');
                    this.$refs.cameraVideo.style.webkitTransform = transformValue;
                    this.$refs.cameraVideo.style.transformOrigin = 'center center';
                },
                toggleCameraFlip() {
                    this.cameraManualFlip = !this.getEffectiveCameraFlip();
                    this.saveCameraFlipPreference(this.cameraManualFlip);
                    this.applyCameraMirrorFix();
                },
                async startCameraStream(preferredFacingMode = 'user') {
                    const normalizedPreferredMode = preferredFacingMode === 'environment' ? 'environment' : 'user';
                    const fallbackMode = normalizedPreferredMode === 'user' ? 'environment' : 'user';
                    const baseVideoConstraints = {
                        width: { ideal: 1280 },
                        height: { ideal: 720 },
                    };

                    this.cameraError = '';
                    this.stopCameraStream();

                    const streamAttempts = [
                        { ...baseVideoConstraints, facingMode: { exact: normalizedPreferredMode } },
                        { ...baseVideoConstraints, facingMode: { ideal: normalizedPreferredMode } },
                        { ...baseVideoConstraints, facingMode: { ideal: fallbackMode } },
                        baseVideoConstraints,
                        true,
                    ];

                    for (const videoConstraints of streamAttempts) {
                        try {
                            const stream = await navigator.mediaDevices.getUserMedia({
                                video: videoConstraints,
                                audio: false,
                            });

                            this.cameraStream = stream;
                            this.cameraFacingMode = this.detectFacingModeFromStream(stream, normalizedPreferredMode);
                            this.cameraNeedsFlip = this.shouldUnmirrorFrontCamera(stream);

                            if (this.cameraManualFlip === null) {
                                this.cameraManualFlip = this.cameraNeedsFlip;
                                this.saveCameraFlipPreference(this.cameraManualFlip);
                            }

                            await this.$nextTick();

                            if (this.$refs.cameraVideo) {
                                this.$refs.cameraVideo.srcObject = stream;
                                this.$refs.cameraVideo.onloadedmetadata = () => this.applyCameraMirrorFix();
                                this.$refs.cameraVideo.onresize = () => this.applyCameraMirrorFix();
                                await this.$refs.cameraVideo.play();
                                this.applyCameraMirrorFix();
                                setTimeout(() => this.applyCameraMirrorFix(), 120);
                            }

                            return true;
                        } catch (error) {
                            // Try the next constraint strategy.
                        }
                    }

                    this.cameraError = 'Kamera tidak dapat diakses. Cek izin kamera di browser.';
                    this.stopCameraStream();

                    return false;
                },
                async switchCameraFacing() {
                    if (!this.cameraOpen || this.cameraSwitching) {
                        return;
                    }

                    this.cameraSwitching = true;
                    const nextMode = this.cameraFacingMode === 'user' ? 'environment' : 'user';
                    await this.startCameraStream(nextMode);
                    this.cameraSwitching = false;
                },
                async openCamera() {
                    this.cameraOpen = true;
                    this.cameraError = '';
                    this.closeImagePreview();
                    this.$dispatch('booking-chat-pause-polling', { paused: true });
                    this.loadCameraFlipPreference();

                    if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
                        this.cameraError = 'Browser tidak mendukung akses kamera.';
                        return;
                    }

                    await this.startCameraStream(this.cameraFacingMode);
                },
                closeCamera() {
                    this.stopCameraStream();
                    this.cameraOpen = false;
                    this.cameraError = '';
                    this.cameraSwitching = false;
                    this.$dispatch('booking-chat-pause-polling', { paused: false });
                },
                async uploadPreparedAttachment(file) {
                    if (!this.$wire || typeof this.$wire.upload !== 'function') {
                        this.showDropNotice('Komponen upload belum siap. Muat ulang halaman lalu coba lagi.', 'error');

                        return false;
                    }

                    this.isAttachmentUploading = true;

                    try {
                        await new Promise((resolve, reject) => {
                            this.$wire.upload(
                                'attachmentDraft',
                                file,
                                () => resolve(true),
                                () => reject(new Error('LIVEWIRE_UPLOAD_FAILED')),
                            );
                        });

                        return true;
                    } catch (error) {
                        this.showDropNotice('Upload lampiran gagal. Coba ulangi dengan ukuran file lebih kecil.', 'error');

                        return false;
                    } finally {
                        this.isAttachmentUploading = false;
                    }
                },
                async attachFileToComposer(file, successMessage = 'Lampiran siap dikirim.') {
                    const prepared = await this.prepareAttachmentFile(file);

                    if (!prepared) {
                        return false;
                    }

                    if (!await this.uploadPreparedAttachment(prepared.file)) {
                        return false;
                    }

                    if (prepared.wasCompressed) {
                        this.showDropNotice(`Gambar dikompres otomatis (${this.humanizeBytes(prepared.originalBytes)} -> ${this.humanizeBytes(prepared.file.size)}).`, 'success');

                        return true;
                    }

                    this.showDropNotice(successMessage, 'success');

                    return true;
                },
                canAcceptDropPayload(dataTransfer) {
                    if (!dataTransfer) {
                        return false;
                    }

                    const types = Array.from(dataTransfer.types || []);

                    return types.includes('Files') || types.includes('text/uri-list') || types.includes('text/plain') || types.includes('text/html');
                },
                handleComposerDragEnter(event) {
                    if (!this.canAcceptDropPayload(event.dataTransfer)) {
                        return;
                    }

                    this.dragCounter += 1;
                    this.isDropActive = true;
                },
                handleComposerDragOver(event) {
                    if (!this.canAcceptDropPayload(event.dataTransfer)) {
                        return;
                    }

                    this.isDropActive = true;
                },
                handleComposerDragLeave() {
                    this.dragCounter = Math.max(0, this.dragCounter - 1);

                    if (this.dragCounter === 0) {
                        this.isDropActive = false;
                    }
                },
                extractDroppedUrl(dataTransfer) {
                    if (!dataTransfer) {
                        return '';
                    }

                    const uriList = (dataTransfer.getData('text/uri-list') || '')
                        .split(/\r?\n/u)
                        .map((line) => line.trim())
                        .find((line) => line !== '' && !line.startsWith('#'));

                    if (uriList && /^https?:\/\//i.test(uriList)) {
                        return uriList;
                    }

                    const plainText = (dataTransfer.getData('text/plain') || '').trim();

                    if (plainText && /^https?:\/\//i.test(plainText)) {
                        return plainText;
                    }

                    const html = dataTransfer.getData('text/html') || '';
                    const srcMatch = html.match(/src=['`]([^'`]+)['`]/i);

                    if (srcMatch && srcMatch[1] && /^https?:\/\//i.test(srcMatch[1])) {
                        return srcMatch[1];
                    }

                    return '';
                },
                inferFileNameFromUrl(url, contentType = '') {
                    const cleanUrl = String(url || '').split('#')[0].split('?')[0];
                    const lastSegment = cleanUrl.split('/').pop() || '';

                    if (lastSegment.includes('.')) {
                        return decodeURIComponent(lastSegment);
                    }

                    const mime = String(contentType || '').split(';')[0].trim().toLowerCase();
                    const extensionMap = {
                        'image/jpeg': 'jpg',
                        'image/jpg': 'jpg',
                        'image/png': 'png',
                        'image/gif': 'gif',
                        'image/webp': 'webp',
                        'image/bmp': 'bmp',
                        'application/pdf': 'pdf',
                        'text/plain': 'txt',
                        'text/csv': 'csv',
                        'application/zip': 'zip',
                    };
                    const extension = extensionMap[mime] || 'bin';
                    const timestamp = new Date().toISOString().replace(/[:.]/g, '-');

                    return `drop-${timestamp}.${extension}`;
                },
                async attachUrlAsFile(url) {
                    try {
                        const response = await fetch(url, { mode: 'cors', credentials: 'omit' });

                        if (!response.ok) {
                            throw new Error('FETCH_FAILED');
                        }

                        const blob = await response.blob();

                        if (!blob || blob.size <= 0) {
                            throw new Error('EMPTY_BLOB');
                        }

                        const mimeType = String(blob.type || response.headers.get('content-type') || 'application/octet-stream');
                        const fileName = this.inferFileNameFromUrl(url, mimeType);
                        const file = new File([blob], fileName, { type: mimeType });

                        return this.attachFileToComposer(file, 'File dari sumber online siap dikirim.');
                    } catch (error) {
                        this.showDropNotice('Gagal mengambil file dari online. Simpan dulu ke komputer lalu drag ulang.', 'error');

                        return false;
                    }
                },
                async handleComposerDrop(event) {
                    this.dragCounter = 0;
                    this.isDropActive = false;

                    const dataTransfer = event.dataTransfer;

                    if (!dataTransfer) {
                        return;
                    }

                    const files = Array.from(dataTransfer.files || []);

                    if (files.length > 0) {
                        await this.attachFileToComposer(files[0], `Lampiran ${files[0].name || ''} siap dikirim.`.trim());

                        return;
                    }

                    const droppedUrl = this.extractDroppedUrl(dataTransfer);

                    if (droppedUrl) {
                        await this.attachUrlAsFile(droppedUrl);

                        return;
                    }

                    this.showDropNotice('Data drag & drop tidak dikenali.', 'error');
                },
                async handleComposerPaste(event) {
                    const clipboardData = event.clipboardData || window.clipboardData;

                    if (!clipboardData || !clipboardData.items || !this.$refs.attachInput) {
                        return;
                    }

                    const imageItem = Array.from(clipboardData.items).find((item) => {
                        return item.kind === 'file' && String(item.type || '').startsWith('image/');
                    });

                    if (!imageItem) {
                        return;
                    }

                    const originalFile = imageItem.getAsFile();

                    if (!originalFile) {
                        return;
                    }

                    event.preventDefault();

                    const mime = String(originalFile.type || 'image/png').toLowerCase();
                    const extensionMap = {
                        'image/jpeg': 'jpg',
                        'image/jpg': 'jpg',
                        'image/png': 'png',
                        'image/gif': 'gif',
                        'image/webp': 'webp',
                        'image/bmp': 'bmp',
                    };
                    const extension = extensionMap[mime] || 'png';
                    const timestamp = new Date().toISOString().replace(/[:.]/g, '-');
                    const pastedFile = new File([originalFile], `paste-${timestamp}.${extension}`, {
                        type: mime,
                    });

                    await this.attachFileToComposer(pastedFile, 'Gambar dari clipboard siap dikirim.');
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

                    context.save();

                    if (this.getEffectiveCameraFlip()) {
                        context.translate(width, 0);
                        context.scale(-1, 1);
                    }

                    context.drawImage(video, 0, 0, width, height);
                    context.restore();

                    canvas.toBlob(async (blob) => {
                        if (!blob) {
                            this.cameraError = 'Gagal mengambil foto. Coba lagi.';
                            return;
                        }

                        const timestamp = new Date().toISOString().replace(/[:.]/g, '-');
                        const file = new File([blob], `kamera-${timestamp}.jpg`, { type: 'image/jpeg' });

                        if (await this.attachFileToComposer(file, 'Foto kamera siap dikirim.')) {
                            this.closeCamera();
                        }
                    }, 'image/jpeg', 0.92);
                }
            }"
            x-init="initNotificationChannel(); ensureWebPushSubscription(); registerNotificationClickHandler(); syncIncomingMessageState(false)"
            x-on:booking-chat-refreshed.window="syncIncomingMessageState(true); syncNotificationPermissionState()"
            x-on:focus.window="syncNotificationPermissionState(); if (notificationPermission === 'granted') { ensureWebPushSubscription() }"
            x-on:booking-chat-scroll-bottom.window="$nextTick(() => { if ($refs.viewport) { $refs.viewport.scrollTop = $refs.viewport.scrollHeight } })"
            x-on:keydown.escape.window="if (deleteConfirmOpen) closeDeleteConfirm(); if (imagePreviewOpen) closeImagePreview(); if (cameraOpen) closeCamera()"
        >
            @if ($selectedChat)
                <div
                    x-ref="incomingMessageMarker"
                    class="booking-chat-incoming-marker"
                    data-chat-id="{{ $selectedChat->id }}"
                    data-message-id="{{ $latestIncomingMessage?->id ?? '' }}"
                    data-sender-name="{{ e($latestIncomingMessage?->sender?->name ?? $counterpartState['name']) }}"
                    data-preview="{{ e($latestIncomingPreview !== '' ? $latestIncomingPreview : 'Pesan baru masuk.') }}"
                ></div>

                <div
                    x-cloak
                    x-show="notificationPromptVisible"
                    x-transition.opacity
                    class="booking-chat-notification-popup"
                    role="status"
                    aria-live="polite"
                >
                    <button
                        type="button"
                        class="booking-chat-notification-close"
                        x-on:click="dismissNotificationPrompt()"
                        aria-label="Tutup pengingat notifikasi"
                    >✕</button>

                    <div class="booking-chat-notification-content">
                        <p class="booking-chat-notification-title">Aktifkan Notifikasi Chat</p>
                        <p class="booking-chat-notification-desc" x-show="webPushPublicKey === ''">
                            Notifikasi chat belum bisa diaktifkan karena kunci VAPID server belum diisi.
                        </p>
                        <p class="booking-chat-notification-desc" x-show="webPushPublicKey !== '' && notificationPermission !== 'denied'">
                            Supaya pesan baru dari {{ $counterpartLabel }} langsung muncul sebagai popup notifikasi sistem di HP.
                        </p>
                        <p class="booking-chat-notification-desc" x-show="webPushPublicKey !== '' && notificationPermission === 'denied'">
                            Izin notifikasi saat ini diblokir browser. Aktifkan kembali di pengaturan browser untuk situs ini.
                        </p>
                    </div>

                    <button
                        type="button"
                        class="booking-chat-notification-action"
                        x-on:click="requestBrowserNotificationPermission()"
                        x-bind:disabled="webPushPublicKey === ''"
                        x-text="webPushPublicKey === '' ? 'Belum Tersedia' : (notificationPermission === 'denied' ? 'Cek Izin Browser' : 'Aktifkan Notifikasi')"
                    ></button>
                </div>

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

                    <div class="booking-chat-room-head-right">
                        <span class="booking-chat-status status-{{ $selectedChat->bukuTamu?->status }}">
                            {{ \App\Models\BukuTamu::STATUS_LABELS[$selectedChat->bukuTamu?->status] ?? ucfirst((string) $selectedChat->bukuTamu?->status) }}
                        </span>

                        <div class="booking-chat-room-actions" x-data="{ open: false }" x-on:click.outside="open = false">
                            <button
                                type="button"
                                class="booking-chat-room-actions-toggle"
                                x-on:click="open = !open"
                                x-bind:aria-expanded="open ? 'true' : 'false'"
                                aria-label="Aksi chat"
                                title="Aksi chat"
                            >⋮</button>

                            <div class="booking-chat-room-actions-menu" x-show="open" x-cloak>
                                @if ($panelLabel === 'Staff')
                                    @if ($isChatClosed)
                                        <button
                                            type="button"
                                            class="booking-chat-room-action-btn"
                                            disabled
                                        >Status sudah Selesai</button>
                                    @else
                                        <button
                                            type="button"
                                            class="booking-chat-room-action-btn"
                                            x-on:click="$wire.markSelectedBookingAsSelesai(); open = false"
                                        >Ubah Status ke Selesai</button>
                                    @endif
                                @endif

                                <button
                                    type="button"
                                    class="booking-chat-room-action-btn"
                                    x-on:click="$wire.exportSelectedChat(); open = false"
                                >Export Chat (.txt)</button>
                            </div>
                        </div>
                    </div>
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
                            $deletedForEveryone = $message->isDeletedForEveryone();
                            $messageTimeSource = $message->isEdited() && $message->edited_at ? $message->edited_at : $message->created_at;
                            $canEditMessage = $isMine
                                && !$deletedForEveryone
                                && !$hasAttachment
                                && $message->message !== '[Lampiran]'
                                && $message->isWithinEditWindow(\App\Services\BookingChatManager::EDIT_WINDOW_MINUTES);
                        @endphp

                        @if ($messageDay !== $lastMessageDay)
                            <p class="booking-chat-message-separator">{{ $messageDayLabel }}</p>
                            @php
                                $lastMessageDay = $messageDay;
                            @endphp
                        @endif

                        <article
                            class="booking-chat-message {{ $isMine ? 'is-mine' : '' }} {{ $isSystem ? 'is-system' : '' }} {{ !$isSystem && !$deletedForEveryone ? 'is-swipe-reply' : '' }}"
                            @if (!$isSystem)
                                x-data="{ startX: 0, startY: 0, dragX: 0, isTracking: false, isMine: @js($isMine), canReply: @js(!$deletedForEveryone) }"
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

                                    if (canReply && window.innerWidth <= 820 && Math.abs(dragX) >= 44) {
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
                                        $replyPreviewText = $repliedToMessage->deleted_for_everyone_at
                                            ? 'Pesan telah dihapus'
                                            : ($repliedToMessage->message === '[Lampiran]'
                                            ? '<x-heroicon-m-paper-clip class="inline-block w-3.5 h-3.5 mr-0.5" /> ' . ($repliedToMessage->attachment_name ?: 'Lampiran')
                                            : \Illuminate\Support\Str::limit((string) $repliedToMessage->message, 95));
                                        $replySenderName = $repliedToMessage->sender?->name ?: 'Pengguna';
                                    @endphp

                                    <div class="booking-chat-reply-context {{ $isMine ? 'is-mine' : '' }}">
                                        <p class="booking-chat-reply-context-name">{{ $replySenderName }}</p>
                                        <p class="booking-chat-reply-context-text">{{ $replyPreviewText }}</p>
                                    </div>
                                @endif

                                @if ($deletedForEveryone)
                                    <p class="booking-chat-message-deleted">
                                        {{ $isMine ? 'Anda menghapus pesan ini.' : 'Pesan ini telah dihapus.' }}
                                    </p>
                                @else
                                    @if ($hasAttachment && $attachmentUrl)
                                        @if ($isImage)
                                            <button
                                                type="button"
                                                class="booking-chat-attachment-image-wrap"
                                                x-on:click="openImagePreview(@js($attachmentUrl), @js($message->attachment_name ?? 'Preview gambar'))"
                                                title="Lihat gambar"
                                                aria-label="Lihat gambar"
                                            >
                                                <img
                                                    src="{{ $attachmentUrl }}"
                                                    alt="{{ $message->attachment_name ?? 'Gambar' }}"
                                                    class="booking-chat-attachment-image"
                                                    loading="lazy"
                                                />
                                            </button>
                                        @else
                                            <a
                                                href="{{ $attachmentUrl }}"
                                                target="_blank"
                                                rel="noopener noreferrer"
                                                download="{{ $message->attachment_name }}"
                                                x-on:click.prevent="downloadAttachment(@js($attachmentUrl), @js($message->attachment_name ?? 'Lampiran'))"
                                                class="booking-chat-attachment-file"
                                            >
                                                <span class="booking-chat-attachment-icon">
                                                    <x-heroicon-o-paper-clip class="w-5 h-5" />
                                                </span>
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
                                @endif

                                <div class="booking-chat-message-meta">
                                    <p class="booking-chat-message-time">
                                        @if (!$isSystem && !$deletedForEveryone && $message->isEdited())
                                            <span class="booking-chat-message-edited">diedit</span>
                                        @endif
                                        {{ $messageTimeSource?->format('H:i') }}
                                        @if(!$isSystem && $isMine)
                                            <span class="booking-chat-message-check {{ $message->read_at ? 'is-read' : '' }}"><span class="booking-chat-check-mark">✓</span><span class="booking-chat-check-mark">✓</span></span>
                                        @endif
                                    </p>
                                </div>

                                @if (!$isSystem)
                                    <div class="booking-chat-message-actions" x-data="{ open: false }" x-on:click.outside="open = false">
                                        <button
                                            type="button"
                                            class="booking-chat-message-actions-toggle"
                                            x-on:click="open = !open"
                                            x-bind:aria-expanded="open ? 'true' : 'false'"
                                            title="Aksi pesan"
                                            aria-label="Aksi pesan"
                                        >⋮</button>

                                        <div class="booking-chat-message-actions-menu" x-show="open" x-cloak>
                                            @if (!$deletedForEveryone)
                                                <button
                                                    type="button"
                                                    class="booking-chat-message-action-btn"
                                                    x-on:click="$wire.setReplyTo('{{ $message->id }}'); open = false"
                                                >Balas</button>
                                            @endif

                                            @if ($canEditMessage)
                                                <button
                                                    type="button"
                                                    class="booking-chat-message-action-btn"
                                                    x-on:click="$wire.startEditingMessage('{{ $message->id }}'); open = false"
                                                >Edit Pesan</button>
                                            @endif

                                            <button
                                                type="button"
                                                class="booking-chat-message-action-btn"
                                                x-on:click="openDeleteConfirm('me', '{{ $message->id }}'); open = false"
                                            >Hapus untuk Saya</button>

                                            @if ($isMine && !$deletedForEveryone)
                                                <button
                                                    type="button"
                                                    class="booking-chat-message-action-btn is-danger"
                                                    x-on:click="openDeleteConfirm('everyone', '{{ $message->id }}'); open = false"
                                                >Hapus untuk Semua</button>
                                            @endif
                                        </div>
                                    </div>
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
                            ? '<x-heroicon-m-paper-clip class="inline-block w-4 h-4 mr-0.5" /> ' . ($activeReplyMessage->attachment_name ?: 'Lampiran')
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

                @if ($activeEditingMessage)
                    @php
                        $activeEditingPreviewText = \Illuminate\Support\Str::limit((string) $activeEditingMessage->message, 130);
                    @endphp

                    <div class="booking-chat-reply-draft">
                        <div class="booking-chat-reply-draft-texts">
                            <p class="booking-chat-reply-draft-title">Mode edit pesan</p>
                            <p class="booking-chat-reply-draft-preview">{{ $activeEditingPreviewText }}</p>
                        </div>
                        <button
                            type="button"
                            wire:click="cancelEditingMessage"
                            class="booking-chat-reply-draft-close"
                            title="Batalkan edit"
                            aria-label="Batalkan edit"
                        >✕</button>
                    </div>
                @endif

                {{-- Quick Replies --}}
                @if (count($quickReplies) > 0 && !$activeEditingMessage)
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
                @if ($attachmentDraft && !$activeEditingMessage)
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
                            <span class="booking-chat-attachment-preview-icon">
                                <x-heroicon-o-paper-clip class="w-6 h-6 text-gray-500" />
                            </span>
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
                    x-show="deleteConfirmOpen"
                    x-transition.opacity
                    class="booking-chat-delete-modal"
                    x-on:click.self="closeDeleteConfirm()"
                    aria-modal="true"
                    role="dialog"
                >
                    <div class="booking-chat-delete-dialog">
                        <h4 class="booking-chat-delete-title" x-text="deleteConfirmMode === 'everyone' ? 'Hapus untuk Semua?' : 'Hapus untuk Saya?'"></h4>
                        <p class="booking-chat-delete-text" x-text="deleteConfirmMode === 'everyone' ? 'Pesan akan diganti menjadi teks &quot;pesan dihapus&quot; untuk semua orang.' : 'Pesan ini hanya akan disembunyikan dari tampilan Anda.'"></p>

                        <div class="booking-chat-delete-actions">
                            <button type="button" class="booking-chat-delete-cancel" x-on:click="closeDeleteConfirm()">Batal</button>
                            <button type="button" class="booking-chat-delete-submit" x-on:click="runDeleteConfirmed()">Hapus</button>
                        </div>
                    </div>
                </div>

                <div
                    x-cloak
                    x-show="imagePreviewOpen"
                    x-transition.opacity
                    class="booking-chat-image-modal"
                    x-on:click.self="closeImagePreview()"
                    aria-modal="true"
                    role="dialog"
                >
                    <div class="booking-chat-image-dialog">
                        <div class="booking-chat-image-head">
                            <p class="booking-chat-image-title" x-text="imagePreviewName"></p>

                            <div class="booking-chat-image-actions">
                                <button
                                    type="button"
                                    class="booking-chat-image-download"
                                    x-on:click="downloadAttachment(imagePreviewUrl, imagePreviewName)"
                                    aria-label="Download gambar"
                                    title="Download gambar"
                                >Download</button>

                                <button
                                    type="button"
                                    class="booking-chat-image-close"
                                    x-on:click="closeImagePreview()"
                                    aria-label="Tutup preview gambar"
                                    title="Tutup"
                                >✕</button>
                            </div>
                        </div>

                        <div class="booking-chat-image-viewport">
                            <img
                                x-bind:src="imagePreviewUrl"
                                x-bind:alt="imagePreviewName"
                                class="booking-chat-image-full"
                            />
                        </div>
                    </div>
                </div>

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
                            <div class="booking-chat-camera-controls">
                                <button
                                    type="button"
                                    class="booking-chat-camera-flip-preview"
                                    x-on:click="toggleCameraFlip()"
                                    :class="{ 'is-active': getEffectiveCameraFlip() }"
                                    :disabled="cameraError !== ''"
                                    aria-label="Mirror tampilan kamera"
                                    title="Mirror tampilan"
                                >↔</button>
                                <button
                                    type="button"
                                    class="booking-chat-camera-flip-preview"
                                    x-on:click="switchCameraFacing()"
                                    :class="{ 'is-active': cameraFacingMode === 'environment' }"
                                    :disabled="cameraSwitching || cameraError !== ''"
                                    aria-label="Balik kamera depan dan belakang"
                                    title="Balik kamera depan/belakang"
                                >⟳</button>
                            </div>
                        </div>

                        <div class="booking-chat-camera-viewport">
                            <video
                                x-ref="cameraVideo"
                                class="booking-chat-camera-video"
                                x-bind:class="{ 'is-force-unmirror': getEffectiveCameraFlip() }"
                                autoplay
                                playsinline
                                muted
                            ></video>

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

                @if ($isChatClosed)
                    <div class="booking-chat-reply-draft is-thread-closed">
                        <div class="booking-chat-reply-draft-texts">
                            <p class="booking-chat-reply-draft-title">Thread ditutup</p>
                            <p class="booking-chat-reply-draft-preview is-thread-closed">
                                Status booking sudah Selesai. Chat ini tidak bisa dilanjutkan dan akan aktif kembali saat ada booking baru dari tamu yang sama ke staff yang sama.
                            </p>
                        </div>
                    </div>
                @else
                    <form
                        wire:submit.prevent="sendMessage"
                        class="booking-chat-composer"
                        x-bind:class="{ 'is-drop-active': isDropActive }"
                        x-on:dragenter.prevent="handleComposerDragEnter($event)"
                        x-on:dragover.prevent="handleComposerDragOver($event)"
                        x-on:dragleave.prevent="handleComposerDragLeave()"
                        x-on:drop.prevent="handleComposerDrop($event)"
                    >
                        @if (!$activeEditingMessage)
                            <div class="booking-chat-composer-tools">
                                {{-- Attachment button --}}
                                <label class="booking-chat-attach-btn" title="Lampirkan berkas">
                                    <x-heroicon-o-paper-clip class="w-5 h-5 text-gray-500 dark:text-gray-400" />
                                    <input
                                        type="file"
                                        wire:key="attachment-{{ $attachmentInputIteration }}"
                                        accept=".jpg,.jpeg,.png,.gif,.webp,.bmp,.pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.txt,.csv,.zip,.rar"
                                        x-ref="attachInput"
                                        x-on:change.capture="handleAttachmentInputChange($event)"
                                        class="booking-chat-attach-input"
                                    />
                                </label>

                                <button
                                    type="button"
                                    class="booking-chat-camera-btn"
                                    x-on:click="openCamera()"
                                    title="Ambil foto dari kamera"
                                    aria-label="Ambil foto dari kamera"
                                ><x-heroicon-o-camera class="w-5 h-5 text-gray-500 dark:text-gray-400" /></button>

                                @if ($panelLabel === 'Piket')
                                    <button
                                        type="button"
                                        class="booking-chat-contact-btn"
                                        wire:click="shareGuestContact"
                                        wire:loading.attr="disabled"
                                        wire:target="shareGuestContact"
                                        title="Bagikan kontak tamu"
                                        aria-label="Bagikan kontak tamu"
                                    ><x-heroicon-o-user-circle class="w-5 h-5 text-gray-500 dark:text-gray-400" /></button>
                                @endif

                                {{-- Quick replies toggle --}}
                                @if (count($quickReplies) > 0)
                                    <button
                                        type="button"
                                        x-on:click="showQuickReplies = !showQuickReplies"
                                        class="booking-chat-quick-reply-toggle"
                                        title="Balasan cepat"
                                        :class="{ 'is-active': showQuickReplies }"
                                    ><x-heroicon-o-bolt class="w-5 h-5 text-gray-500 dark:text-gray-400" /></button>
                                @endif
                            </div>
                        @endif

                        <p
                            x-cloak
                            x-show="dropNotice !== ''"
                            x-transition.opacity
                            class="booking-chat-drop-notice"
                            x-bind:class="`is-${dropNoticeType}`"
                            x-text="dropNotice"
                        ></p>

                        <div class="booking-chat-composer-row">
                            <textarea
                                wire:model.defer="messageDraft"
                                wire:input.debounce.500ms="markTyping"
                                wire:focus="markTyping"
                                x-on:keydown="if ($event.key === 'Enter' && !$event.shiftKey) { $event.preventDefault(); $el.form.requestSubmit(); }"
                                x-on:paste="handleComposerPaste($event)"
                                x-on:input="resizeComposer($el)"
                                x-on:booking-chat-reset-input.window="resizeComposer($el)"
                                x-init="resizeComposer($el)"
                                rows="1"
                                maxlength="2000"
                                placeholder="{{ $activeEditingMessage ? 'Edit pesan lalu tekan kirim untuk simpan.' : 'Ketik pesan... (Enter kirim, Shift+Enter baris baru)' }}"
                                class="booking-chat-textarea"
                            ></textarea>

                            <button
                                type="submit"
                                class="booking-chat-send-btn"
                                wire:loading.attr="disabled"
                                wire:target="sendMessage"
                                x-bind:disabled="isAttachmentUploading"
                                aria-label="{{ $activeEditingMessage ? 'Simpan edit pesan' : 'Kirim pesan' }}"
                                title="{{ $activeEditingMessage ? 'Simpan edit pesan' : 'Kirim' }}"
                            >
                                @if ($activeEditingMessage)
                                    <x-heroicon-s-check class="w-5 h-5 text-white" />
                                @else
                                    <x-heroicon-s-paper-airplane class="w-5 h-5 text-white" />
                                @endif
                            </button>
                        </div>
                    </form>
                @endif
            @else
                <div class="booking-chat-room-empty">
                    <div class="booking-chat-room-empty-icon">
                        <x-heroicon-o-chat-bubble-left-right class="w-16 h-16 mx-auto text-gray-200" />
                    </div>
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
