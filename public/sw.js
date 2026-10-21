const CACHE_NAME = 'tailorpro-v2';
const STATIC_ASSETS = [
  '/',
  '/favicon.png',
  '/logo.png',
  '/pwa-icon-192.png',
  '/pwa-icon-512.png',
  '/manifest.json',
];

self.addEventListener('install', (event) => {
  event.waitUntil(
    caches.open(CACHE_NAME).then((cache) => {
      return cache.addAll(STATIC_ASSETS);
    })
  );
  self.skipWaiting();
});

self.addEventListener('activate', (event) => {
  event.waitUntil(
    caches.keys().then((keys) => {
      return Promise.all(
        keys.filter((key) => key !== CACHE_NAME).map((key) => caches.delete(key))
      );
    })
  );
  self.clients.claim();
});

self.addEventListener('fetch', (event) => {
  const { request } = event;
  const url = new URL(request.url);

  // Ignore non-GET and API / Sanctum requests (always live from network)
  if (request.method !== 'GET' || url.pathname.startsWith('/api') || url.pathname.startsWith('/sanctum')) {
    return;
  }

  // Cache-first for built assets and images
  if (
    url.pathname.startsWith('/build/') ||
    url.pathname.match(/\.(png|jpg|jpeg|svg|woff2|woff|ttf|css|js|json)$/)
  ) {
    event.respondWith(
      caches.match(request).then((cached) => {
        if (cached) return cached;
        return fetch(request).then((response) => {
          if (!response || response.status !== 200 || response.type !== 'basic') {
            return response;
          }
          const responseToCache = response.clone();
          caches.open(CACHE_NAME).then((cache) => {
            cache.put(request, responseToCache);
          });
          return response;
        });
      })
    );
    return;
  }

  // Network-first with cache fallback for HTML pages / SPA routing
  event.respondWith(
    fetch(request).catch(async () => {
      const match = await caches.match(request);
      if (match) return match;
      if (request.mode === 'navigate') {
        const root = await caches.match('/');
        if (root) return root;
      }
      return new Response('Offline', { status: 503, statusText: 'Service Unavailable' });
    })
  );
});
