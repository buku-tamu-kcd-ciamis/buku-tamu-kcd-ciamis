const CACHE_NAME = "cadisdik-pwa-v4";
const OFFLINE_URL = "/offline.html";
const PRECACHE_URLS = [
    OFFLINE_URL,
    "/css/filament-custom.css",
    "/img/logo-cadisdik.png",
    "/pwa/icon-192.png",
    "/pwa/icon-512.png",
    "/pwa/icon-maskable-512.png",
];

self.addEventListener("install", (event) => {
    event.waitUntil(
        caches.open(CACHE_NAME).then((cache) => cache.addAll(PRECACHE_URLS)),
    );

    self.skipWaiting();
});

self.addEventListener("activate", (event) => {
    event.waitUntil(
        caches
            .keys()
            .then((cacheNames) =>
                Promise.all(
                    cacheNames
                        .filter((cacheName) => cacheName !== CACHE_NAME)
                        .map((cacheName) => caches.delete(cacheName)),
                ),
            ),
    );

    self.clients.claim();
});

self.addEventListener("fetch", (event) => {
    if (event.request.method !== "GET") {
        return;
    }

    const requestUrl = new URL(event.request.url);

    if (requestUrl.origin !== self.location.origin) {
        return;
    }

    if (requestUrl.pathname === "/manifest.webmanifest") {
        event.respondWith(
            fetch(event.request)
                .then((response) => {
                    const responseClone = response.clone();
                    caches
                        .open(CACHE_NAME)
                        .then((cache) =>
                            cache.put(event.request, responseClone),
                        );

                    return response;
                })
                .catch(() => caches.match(event.request)),
        );

        return;
    }

    if (event.request.mode === "navigate") {
        event.respondWith(
            fetch(event.request)
                .then((response) => {
                    const responseClone = response.clone();
                    caches
                        .open(CACHE_NAME)
                        .then((cache) =>
                            cache.put(event.request, responseClone),
                        );

                    return response;
                })
                .catch(async () => {
                    const cachedPage = await caches.match(event.request);

                    if (cachedPage) {
                        return cachedPage;
                    }

                    return caches.match(OFFLINE_URL);
                }),
        );

        return;
    }

    event.respondWith(
        caches.match(event.request).then((cachedResponse) => {
            if (cachedResponse) {
                return cachedResponse;
            }

            return fetch(event.request)
                .then((response) => {
                    if (
                        !response ||
                        response.status !== 200 ||
                        response.type !== "basic"
                    ) {
                        return response;
                    }

                    const responseClone = response.clone();
                    caches
                        .open(CACHE_NAME)
                        .then((cache) =>
                            cache.put(event.request, responseClone),
                        );

                    return response;
                })
                .catch(() => caches.match(OFFLINE_URL));
        }),
    );
});

self.addEventListener("message", (event) => {
    if (event.data && event.data.type === "SKIP_WAITING") {
        self.skipWaiting();
    }
});

function normalizeNotificationPayload(rawPayload) {
    const payload = rawPayload || {};

    return {
        title: payload.title || "Notifikasi Baru",
        body: payload.body || "Ada pembaruan baru.",
        icon: payload.icon || "/img/logo-cadisdik.png",
        badge: payload.badge || "/img/logo-cadisdik.png",
        tag: payload.tag || "cadisdik-generic-notification",
        data: {
            chatId: payload.chatId || null,
            url: payload.url || "/",
        },
    };
}

self.addEventListener("push", (event) => {
    let parsedPayload = {};

    try {
        parsedPayload = event.data ? event.data.json() : {};
    } catch (error) {
        parsedPayload = {
            title: "Notifikasi Baru",
            body: event.data ? event.data.text() : "Ada pembaruan baru.",
        };
    }

    const payload = normalizeNotificationPayload(parsedPayload);

    event.waitUntil(
        self.registration.showNotification(payload.title, {
            body: payload.body,
            icon: payload.icon,
            badge: payload.badge,
            tag: payload.tag,
            renotify: true,
            vibrate: [120, 60, 120],
            data: payload.data,
        }),
    );
});

self.addEventListener("notificationclick", (event) => {
    const payload = normalizeNotificationPayload(
        event.notification?.data || {},
    );

    event.notification.close();

    event.waitUntil(
        clients
            .matchAll({ type: "window", includeUncontrolled: true })
            .then((clientList) => {
                const origin = self.location.origin;
                const targetUrl = payload.data.url.startsWith("http")
                    ? payload.data.url
                    : `${origin}${payload.data.url}`;

                for (const client of clientList) {
                    if (!client.url.startsWith(origin)) {
                        continue;
                    }

                    if ("navigate" in client && client.url !== targetUrl) {
                        client.navigate(targetUrl).catch(() => undefined);
                    }

                    client.postMessage({
                        type: "BOOKING_CHAT_NOTIFICATION_CLICK",
                        chatId: payload.data.chatId,
                    });

                    if ("focus" in client) {
                        return client.focus();
                    }
                }

                if (clients.openWindow) {
                    return clients.openWindow(targetUrl);
                }

                return undefined;
            }),
    );
});
