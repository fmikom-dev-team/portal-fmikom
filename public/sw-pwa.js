const CACHE_VERSION = 'v1783056340183';
const CACHE_NAME = `fmikom-portal-${CACHE_VERSION}`;
const FONT_CACHE_NAME = `fmikom-fonts-${CACHE_VERSION}`;
// ASSET_CACHE_NAME dihapus — tidak lagi digunakan untuk CacheFirst

// Aset yang akan di-precache agar dapat diakses secara offline secara instan
const PRECACHE_ASSETS = [
  '/offline.html',
  '/manifest.json',
  '/asset/favicon.ico',
];

// Helper to return a valid Response fallback when network and cache both fail
function getFallbackResponse(request) {
  const acceptHeader = request.headers.get('accept') || '';

  if (request.mode === 'navigate' || acceptHeader.includes('text/html')) {
    return caches.match('/offline.html').then((offlineResponse) => {
      if (offlineResponse) return offlineResponse;
      return new Response(
        '<!DOCTYPE html><html lang="id"><head><meta charset="utf-8"><title>Koneksi Terputus</title><style>body{font-family:system-ui,-apple-system,sans-serif;display:flex;flex-direction:column;align-items:center;justify-content:center;height:100vh;margin:0;background:#f8fafc;color:#1e293b;text-align:center}h1{margin:0 0 10px;font-size:24px}p{margin:0;color:#64748b}</style></head><body><h1>Koneksi Bermasalah</h1><p>Anda sedang offline atau server tidak merespon.</p></body></html>',
        {
          status: 503,
          statusText: 'Service Unavailable',
          headers: { 'Content-Type': 'text/html; charset=utf-8' }
        }
      );
    });
  }

  if (request.destination === 'image') {
    return Promise.resolve(new Response(
      '<svg xmlns="http://www.w3.org/2000/svg" width="1" height="1"/>',
      {
        status: 404,
        headers: { 'Content-Type': 'image/svg+xml' }
      }
    ));
  }

  return Promise.resolve(new Response('', {
    status: 503,
    statusText: 'Service Unavailable'
  }));
}

// ── Lifecycle Events ──────────────────────────────────────────────────────────

// Instalasi SW & Pre-caching aset offline
self.addEventListener('install', (event) => {
  event.waitUntil(
    caches.open(CACHE_NAME).then((cache) => {
      console.log('[Service Worker] Pre-caching offline assets...');
      return cache.addAll(PRECACHE_ASSETS);
    }).then(() => {
      // Langsung aktifkan Service Worker baru tanpa menunggu tab ditutup/SKIP_WAITING dari client
      // Ini memecahkan deadlock di mana SW lama mengintersepsi /build/ assets sebelum app.js baru sempat berjalan
      return self.skipWaiting();
    })
  );
});

// Terima perintah dari app Vue untuk aktivasi SW baru (dipicu setelah user setuju update)
self.addEventListener('message', (event) => {
  if (event.data && event.data.type === 'SKIP_WAITING') {
    self.skipWaiting();
  }
});

// Aktivasi & Pembersihan Cache Lama
self.addEventListener('activate', (event) => {
  const currentCaches = new Set([CACHE_NAME, FONT_CACHE_NAME]);
  event.waitUntil(
    caches.keys().then((cacheNames) => {
      return Promise.all(
        cacheNames.map((cacheName) => {
          if (!currentCaches.has(cacheName)) {
            console.log('[Service Worker] Deleting old cache:', cacheName);
            return caches.delete(cacheName);
          }
        })
      );
    })
    .then(() => self.clients.claim())
    .then(() => {
      // Beritahu semua tab yang terbuka bahwa versi baru sudah aktif → trigger reload
      return self.clients.matchAll({ type: 'window' }).then((clients) => {
        clients.forEach((client) => {
          client.postMessage({ type: 'APP_UPDATED', version: CACHE_VERSION });
        });
      });
    })
  );
});

// ── Fetch Interceptor dengan Strategi Caching Pintar ─────────────────────────
self.addEventListener('fetch', (event) => {
  const request = event.request;
  const url = new URL(request.url);

  // Abaikan request non-GET (POST, PUT, DELETE, dll)
  if (request.method !== 'GET') {
    return;
  }

  // Abaikan request cross-origin (external APIs, CDN pihak ketiga)
  const isSameOrigin = url.origin === self.location.origin;
  const isGoogleFont = url.host.includes('fonts.gstatic.com') || url.host.includes('fonts.googleapis.com');
  if (!isSameOrigin && !isGoogleFont) {
    return;
  }

  // Abaikan request backend API
  if (
    url.pathname.startsWith('/telescope') ||
    url.pathname.startsWith('/horizon') ||
    url.pathname.startsWith('/pulse') ||
    url.pathname.startsWith('/broadcasting') ||
    url.pathname.startsWith('/sanctum') ||
    url.pathname.startsWith('/vendor')
  ) {
    return;
  }

  // 1. Strategi untuk Halaman Navigasi / Inertia.js (Network-First, tanpa cache)
  // Halaman HARUS selalu dari network agar Inertia mendapatkan X-Inertia-Version terbaru.
  if (request.mode === 'navigate' || request.headers.get('X-Inertia') === 'true') {
    event.respondWith(
      fetch(request)
        .catch(() => {
          return caches.match(request).then((cachedResponse) => {
            if (cachedResponse) return cachedResponse;
            return getFallbackResponse(request);
          });
        })
    );
    return;
  }

  // 2. Fonts — CacheFirst (nama file immutable, tidak pernah berubah)
  if (url.pathname.startsWith('/fonts/')) {
    event.respondWith(
      caches.open(FONT_CACHE_NAME).then((cache) => {
        return cache.match(request).then((cachedResponse) => {
          if (cachedResponse) return cachedResponse;
          return fetch(request).then((networkResponse) => {
            if (networkResponse.status === 200) {
              cache.put(request, networkResponse.clone());
            }
            return networkResponse;
          }).catch(() => getFallbackResponse(request));
        });
      })
    );
    return;
  }

  // 3. Vite Build Assets (app.js, chunks, CSS) — TIDAK DIINTERSEP oleh Service Worker
  // ALASAN: CacheFirst menyebabkan browser menyimpan chunk lama yang sudah tidak ada
  // setelah build baru (404). Jika kita intercept dan network fail, 503 dari SW
  // akan memblokir chunk error handler di app.ts dari bereaksi dengan benar.
  // Biarkan browser menangani request ini secara native — Cloudflare CDN & header
  // immutable sudah menjamin caching optimal di layer edge.
  if (url.pathname.startsWith('/build/')) {
    // Tidak intercept — biarkan browser fetch secara native
    return;
  }

  // 4. PWA Assets (/asset/) — Network-First dengan fallback cache
  if (url.pathname.startsWith('/asset/')) {
    event.respondWith(
      fetch(request)
        .then((response) => {
          if (response.status === 200) {
            const responseClone = response.clone();
            caches.open(CACHE_NAME).then((cache) => cache.put(request, responseClone));
          }
          return response;
        })
        .catch(() => {
          return caches.match(request).then((cachedResponse) => {
            return cachedResponse || getFallbackResponse(request);
          });
        })
    );
    return;
  }

  // 5. Strategi Default — Network-First dengan cache untuk gambar
  event.respondWith(
    fetch(request)
      .then((response) => {
        if (response.status === 200 && request.destination === 'image') {
          const responseClone = response.clone();
          caches.open(CACHE_NAME).then((cache) => {
            cache.put(request, responseClone);
          });
        }
        return response;
      })
      .catch(() => {
        return caches.match(request).then((cachedResponse) => {
          if (cachedResponse) return cachedResponse;
          return getFallbackResponse(request);
        });
      })
  );
});
