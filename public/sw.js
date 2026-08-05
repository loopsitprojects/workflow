const CACHE_NAME = 'loops-pwa-cache-v2';
const STATIC_ASSETS = [
  '/offline.html',
  '/loops-icon.png'
];

// Install: only cache static fallbacks, NOT the app shell itself
self.addEventListener('install', event => {
  event.waitUntil(
    caches.open(CACHE_NAME)
      .then(cache => cache.addAll(STATIC_ASSETS))
      .then(() => self.skipWaiting())
  );
});

// Activate: clean up old caches immediately
self.addEventListener('activate', event => {
  event.waitUntil(
    caches.keys().then(cacheNames =>
      Promise.all(
        cacheNames
          .filter(name => name !== CACHE_NAME)
          .map(name => caches.delete(name))
      )
    ).then(() => self.clients.claim())
  );
});

// Fetch: Network-first strategy
// Always try network first; only fall back to cache for offline support
self.addEventListener('fetch', event => {
  // Skip non-GET requests and browser-extension/chrome URLs
  if (event.request.method !== 'GET') return;
  if (!event.request.url.startsWith('http')) return;

  // For navigation requests (page loads), always go network-first
  if (event.request.mode === 'navigate') {
    event.respondWith(
      fetch(event.request)
        .catch(() => caches.match('/offline.html'))
    );
    return;
  }

  // For static assets: network-first with cache fallback
  event.respondWith(
    fetch(event.request)
      .then(response => {
        // Don't cache bad responses
        if (!response || response.status !== 200 || response.type !== 'basic') {
          return response;
        }
        // Cache a clone for future offline use
        const responseToCache = response.clone();
        caches.open(CACHE_NAME).then(cache => {
          cache.put(event.request, responseToCache);
        });
        return response;
      })
      .catch(() => caches.match(event.request))
  );
});
