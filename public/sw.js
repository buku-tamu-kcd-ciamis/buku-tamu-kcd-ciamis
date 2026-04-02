const CACHE_NAME = "cadisdik-pwa-v2";
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
