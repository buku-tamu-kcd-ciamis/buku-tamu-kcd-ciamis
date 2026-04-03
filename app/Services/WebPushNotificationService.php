<?php

namespace App\Services;

use App\Models\BookingChat;
use App\Models\BookingChatMessage;
use App\Models\User;
use App\Models\WebPushSubscription;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Minishlink\WebPush\Subscription;
use Minishlink\WebPush\WebPush;

class WebPushNotificationService
{
    public function isEnabled(): bool
    {
        $vapid = $this->getVapidConfig();

        return filled($vapid['subject'] ?? null)
            && filled($vapid['publicKey'] ?? null)
            && filled($vapid['privateKey'] ?? null);
    }

    public function notifyChatMessage(BookingChat $chat, BookingChatMessage $message, User $sender): void
    {
        if ($message->is_system || !$this->isEnabled()) {
            return;
        }

        $recipientIds = $this->resolveRecipientIds($chat, $sender);

        if ($recipientIds->isEmpty()) {
            return;
        }

        $subscriptions = WebPushSubscription::query()
            ->whereIn('user_id', $recipientIds->all())
            ->get();

        if ($subscriptions->isEmpty()) {
            return;
        }

        $recipients = User::query()
            ->whereIn('id', $recipientIds->all())
            ->with('role_user:id,name')
            ->get()
            ->keyBy('id');

        $payloadsByEndpoint = $this->buildPayloadsByEndpoint($subscriptions, $recipients, $chat, $message, $sender);

        if ($payloadsByEndpoint->isEmpty()) {
            return;
        }

        $webPush = new WebPush([
            'VAPID' => $this->getVapidConfig(),
        ]);
        $webPush->setReuseVAPIDHeaders(true);

        $timeToLive = (int) config('webpush.ttl', 300);

        foreach ($subscriptions as $subscriptionModel) {
            $payload = $payloadsByEndpoint->get($subscriptionModel->endpoint);

            if (!$payload) {
                continue;
            }

            $webPush->queueNotification(
                Subscription::create([
                    'endpoint' => $subscriptionModel->endpoint,
                    'publicKey' => $subscriptionModel->public_key,
                    'authToken' => $subscriptionModel->auth_token,
                    'contentEncoding' => $subscriptionModel->content_encoding ?: 'aes128gcm',
                ]),
                $payload,
                ['TTL' => $timeToLive],
            );
        }

        $successfulIds = [];
        $expiredIds = [];
        $subscriptionsByEndpoint = $subscriptions->keyBy('endpoint');

        foreach ($webPush->flush() as $report) {
            $endpoint = (string) $report->getRequest()->getUri();
            $subscriptionModel = $subscriptionsByEndpoint->get($endpoint);

            if (!$subscriptionModel) {
                continue;
            }

            if ($report->isSuccess()) {
                $successfulIds[] = $subscriptionModel->id;

                continue;
            }

            $statusCode = $report->getResponse()?->getStatusCode();

            if (in_array($statusCode, [404, 410], true)) {
                $expiredIds[] = $subscriptionModel->id;
            }
        }

        if ($successfulIds !== []) {
            WebPushSubscription::query()
                ->whereIn('id', array_values(array_unique($successfulIds)))
                ->update(['last_used_at' => now()]);
        }

        if ($expiredIds !== []) {
            WebPushSubscription::query()
                ->whereIn('id', array_values(array_unique($expiredIds)))
                ->delete();
        }
    }

    /**
     * @return Collection<int, string>
     */
    private function resolveRecipientIds(BookingChat $chat, User $sender): Collection
    {
        $recipientIds = collect([$chat->staff_user_id, $chat->piket_user_id])
            ->filter()
            ->unique()
            ->reject(fn(string $userId): bool => (string) $userId === (string) $sender->id)
            ->values();

        if ($recipientIds->isNotEmpty()) {
            return $recipientIds;
        }

        if (!$sender->hasRole('Staff') || $chat->piket_user_id) {
            return collect();
        }

        // If chat is still unassigned, notify all eligible Piket users so the first Staff message still triggers push.
        return User::query()
            ->whereHas('role_user', fn(Builder $query) => $query->where('name', 'Piket'))
            ->with('role_user:id,name,permissions')
            ->get()
            ->filter(fn(User $user): bool => $user->role_user?->hasPermission('buku_tamu') ?? false)
            ->pluck('id')
            ->reject(fn(string $userId): bool => (string) $userId === (string) $sender->id)
            ->unique()
            ->values();
    }

    /**
     * @param Collection<int, WebPushSubscription> $subscriptions
     * @param Collection<string, User> $recipients
     * @return Collection<string, string>
     */
    private function buildPayloadsByEndpoint(
        Collection $subscriptions,
        Collection $recipients,
        BookingChat $chat,
        BookingChatMessage $message,
        User $sender,
    ): Collection {
        return $subscriptions
            ->mapWithKeys(function (WebPushSubscription $subscription) use ($recipients, $chat, $message, $sender): array {
                /** @var User|null $recipient */
                $recipient = $recipients->get($subscription->user_id);

                if (!$recipient) {
                    return [];
                }

                $payload = [
                    'title' => 'Pesan baru dari ' . ($sender->name ?: 'Pengguna'),
                    'body' => $this->formatMessagePreview($message),
                    'icon' => asset('img/logo-cadisdik.png'),
                    'badge' => asset('img/logo-cadisdik.png'),
                    'tag' => 'booking-chat-' . $chat->id,
                    'chatId' => (string) $chat->id,
                    'url' => $this->resolveChatUrlForRecipient($recipient, (string) $chat->id),
                ];

                $encoded = json_encode($payload, JSON_UNESCAPED_UNICODE);

                if ($encoded === false) {
                    return [];
                }

                return [$subscription->endpoint => $encoded];
            })
            ->filter(fn(string $payload): bool => $payload !== '');
    }

    private function formatMessagePreview(BookingChatMessage $message): string
    {
        if ($message->isDeletedForEveryone()) {
            return 'Pesan telah dihapus.';
        }

        if ($message->hasAttachment() && $message->message === '[Lampiran]') {
            return '[Lampiran] ' . ($message->attachment_name ?? 'Lampiran');
        }

        $text = trim((string) preg_replace('/\s+/u', ' ', (string) $message->message));

        return $text !== ''
            ? Str::limit($text, 120)
            : 'Pesan baru masuk.';
    }

    private function resolveChatUrlForRecipient(User $recipient, string $chatId): string
    {
        if ($recipient->hasRole('Staff')) {
            return route('filament.staff.pages.chat-booking', ['chat' => $chatId]);
        }

        if ($recipient->hasRole('Piket')) {
            return route('filament.piket.pages.chat-booking', ['chat' => $chatId]);
        }

        return url('/');
    }

    /**
     * @return array{subject:string,publicKey:string,privateKey:string}
     */
    private function getVapidConfig(): array
    {
        return [
            'subject' => (string) config('webpush.vapid.subject', ''),
            'publicKey' => (string) config('webpush.vapid.public_key', ''),
            'privateKey' => (string) config('webpush.vapid.private_key', ''),
        ];
    }
}
