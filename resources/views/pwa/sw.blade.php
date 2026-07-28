const CACHE_VERSION = @json($cacheVersion);
const STATIC_CACHE = `${CACHE_VERSION}-static`;
const PUBLIC_CACHE = `${CACHE_VERSION}-public`;
const OFFLINE_URL = @json($offlineUrl);
const APP_ROOT = @json($appRoot);
const NOTIFICATION_FALLBACK_URL = `${APP_ROOT}/notifications`;
const STATIC_PATH_PATTERNS = [/\/build\//, /\/pwa\//, /\/images\/brand\//, /\/favicon\.ico$/];
const PRIVATE_PREFIXES = ['/dashboard', '/profile', '/student', '/coach', '/admin', '/super-admin', '/principal', '/notifications', '/settings', '/push'];
const NETWORK_ONLY_PREFIXES = ['/login', '/logout', '/register', '/forgot-password', '/reset-password', '/email/verify'];

self.addEventListener('install', (event) => {
    event.waitUntil(
        caches.open(STATIC_CACHE).then((cache) => cache.addAll([
            OFFLINE_URL,
            `${APP_ROOT}/pwa/icon-192.png`,
            `${APP_ROOT}/pwa/icon-512.png`,
            `${APP_ROOT}/pwa/badge-96.png`,
        ]))
    );
    self.skipWaiting();
});

self.addEventListener('activate', (event) => {
    event.waitUntil((async () => {
        const keys = await caches.keys();
        await Promise.all(keys.filter((key) => ![STATIC_CACHE, PUBLIC_CACHE].includes(key)).map((key) => caches.delete(key)));
        await self.clients.claim();
    })());
});

self.addEventListener('message', (event) => {
    if (event.data?.type === 'SKIP_WAITING') {
        self.skipWaiting();
    }

    if (event.data?.type === 'CLEAR_PRIVATE_CACHE') {
        event.waitUntil(caches.keys().then((keys) => Promise.all(keys.map((key) => caches.delete(key)))));
    }
});

const isPrivateRequest = (url) => PRIVATE_PREFIXES.some((prefix) => url.pathname.startsWith(prefix));
const isNetworkOnlyRequest = (url) => NETWORK_ONLY_PREFIXES.some((prefix) => url.pathname.startsWith(prefix));
const isStaticRequest = (url) => STATIC_PATH_PATTERNS.some((pattern) => pattern.test(url.pathname));
const isSafeNotificationUrl = (value) => {
    try {
        const url = new URL(value, APP_ROOT);

        return url.origin === new URL(APP_ROOT).origin
            && url.pathname.startsWith('/notifications');
    } catch (error) {
        return false;
    }
};

self.addEventListener('fetch', (event) => {
    const { request } = event;
    const url = new URL(request.url);

    if (request.method !== 'GET' || url.origin !== self.location.origin) {
        return;
    }

    if (isNetworkOnlyRequest(url) || isPrivateRequest(url)) {
        event.respondWith(fetch(request).catch(() => request.mode === 'navigate' ? caches.match(OFFLINE_URL) : Promise.reject()));
        return;
    }

    if (isStaticRequest(url)) {
        event.respondWith((async () => {
            const cached = await caches.match(request);
            if (cached) {
                return cached;
            }

            const response = await fetch(request);
            if (response.ok) {
                const cache = await caches.open(STATIC_CACHE);
                cache.put(request, response.clone());
            }

            return response;
        })());
        return;
    }

    event.respondWith((async () => {
        const cache = await caches.open(PUBLIC_CACHE);
        const cached = await cache.match(request);
        const fetchPromise = fetch(request).then((response) => {
            if (response.ok && response.type === 'basic') {
                cache.put(request, response.clone());
            }
            return response;
        });

        if (cached) {
            event.waitUntil(fetchPromise);
            return cached;
        }

        try {
            return await fetchPromise;
        } catch (error) {
            if (request.mode === 'navigate') {
                return caches.match(OFFLINE_URL);
            }
            throw error;
        }
    })());
});

self.addEventListener('push', (event) => {
    let payload = {};

    if (event.data) {
        try {
            payload = event.data.json();
        } catch (error) {
            payload = {
                title: 'Pemberitahuan',
                body: event.data.text() || 'Pesan push diterima.',
            };
        }
    }

    const targetUrl = typeof payload.url === 'string' && isSafeNotificationUrl(payload.url)
        ? payload.url
        : NOTIFICATION_FALLBACK_URL;

    event.waitUntil(
        self.registration.showNotification(payload.title || 'Pemberitahuan', {
            body: payload.body || '',
            icon: payload.icon || `${APP_ROOT}/pwa/icon-192.png`,
            badge: payload.badge || `${APP_ROOT}/pwa/badge-96.png`,
            tag: payload.tag || 'general',
            data: { url: targetUrl },
        })
    );
});

self.addEventListener('notificationclick', (event) => {
    event.notification.close();
    const targetUrl = isSafeNotificationUrl(event.notification.data?.url)
        ? event.notification.data.url
        : NOTIFICATION_FALLBACK_URL;

    event.waitUntil((async () => {
        const clientsList = await clients.matchAll({ type: 'window', includeUncontrolled: true });
        const existingClient = clientsList.find((client) => client.url.startsWith(APP_ROOT));

        if (existingClient) {
            await existingClient.focus();
            existingClient.navigate(targetUrl);
            return;
        }

        await clients.openWindow(targetUrl);
    })());
});
