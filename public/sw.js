const CACHE_VERSION = 'v3';
const CACHE_NAME = `fmikom-portal-${CACHE_VERSION}`;
const FONT_CACHE_NAME = `fmikom-fonts-${CACHE_VERSION}`;
const ASSET_CACHE_NAME = `fmikom-assets-${CACHE_VERSION}`;

// Aset yang akan di-precache agar dapat diakses secara offline secara instan
const PRECACHE_ASSETS = [
  '/offline.html',
  '/manifest.json',
  '/asset/favicon.ico',
  '/asset/android-chrome-192x192.png',
  '/asset/android-chrome-512x512.png'
];

// Helper to return a valid Response fallback when network and cache both fail
function getFallbackResponse(request) {
  const acceptHeader = request.headers.get('accept') || '';
  
  if (request.mode === 'navigate' || acceptHeader.includes('text/html')) {
    return new Response(
      '<!DOCTYPE html><html lang="id"><head><meta charset="utf-8"><title>Koneksi Terputus</title><style>body{font-family:system-ui,-apple-system,sans-serif;display:flex;flex-direction:column;align-items:center;justify-content:center;height:100vh;margin:0;background:#f8fafc;color:#1e293b;text-align:center}h1{margin:0 0 10px;font-size:24px}p{margin:0;color:#64748b}</style></head><body><h1>Koneksi Bermasalah</h1><p>Anda sedang offline atau server tidak merespon.</p></body></html>',
      {
        status: 503,
        statusText: 'Service Unavailable',
        headers: { 'Content-Type': 'text/html; charset=utf-8' }
      }
    );
  }
  
  if (request.destination === 'image') {
    return new Response(
      '<svg xmlns="http://www.w3.org/2000/svg" width="1" height="1"/>',
      {
        status: 404,
        headers: { 'Content-Type': 'image/svg+xml' }
      }
    );
  }
  
  return new Response('', {
    status: 503,
    statusText: 'Service Unavailable'
  });
}

// Instalasi Service Worker & Pre-caching
self.addEventListener('install', (event) => {
  event.waitUntil(
    caches.open(CACHE_NAME).then((cache) => {
      console.log('[Service Worker] Pre-caching offline assets...');
      return cache.addAll(PRECACHE_ASSETS);
    }).then(() => {
      return self.skipWaiting();
    })
  );
});

// Aktivasi & Pembersihan Cache Lama
self.addEventListener('activate', (event) => {
  const currentCaches = new Set([CACHE_NAME, FONT_CACHE_NAME, ASSET_CACHE_NAME]);
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
    }).then(() => {
      return self.clients.claim();
    })
  );
});

// Fetch Interceptor dengan Strategi Caching Pintar
self.addEventListener('fetch', (event) => {
  const request = event.request;
  const url = new URL(request.url);

  // Abaikan request non-GET (POST, PUT, DELETE, dll)
  if (request.method !== 'GET') {
    return;
  }

  // Abaikan request cross-origin untuk menghindari isu CORS dan Private Network Access (PNA),
  // kecuali untuk resource esensial seperti Google Fonts (fonts.googleapis.com / fonts.gstatic.com)
  const isSameOrigin = url.origin === self.location.origin;
  const isGoogleFont = url.host.includes('fonts.gstatic.com') || url.host.includes('fonts.googleapis.com');
  if (!isSameOrigin && !isGoogleFont) {
    return;
  }

  // Abaikan request backend API khusus Laravel Telescope, Reverb, Horizon, Pulse, dan Livewire/Broadcasting
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

  // 1. Strategi untuk Halaman Navigasi / Inertia.js Page Visits (Network-First)
  if (request.mode === 'navigate' || request.headers.get('X-Inertia') === 'true') {
    event.respondWith(
      fetch(request)
        .then((response) => {
          // Hanya cache jika response sukses (status 200 OK)
          if (response.status === 200) {
            const responseClone = response.clone();
            caches.open(CACHE_NAME).then((cache) => {
              cache.put(request, responseClone);
            });
          }
          return response;
        })
        .catch(() => {
          // Jika offline, coba ambil dari cache halaman yang pernah dibuka
          return caches.match(request).then((cachedResponse) => {
            if (cachedResponse) {
              return cachedResponse;
            }
            // Jika benar-benar tidak ada di cache, tampilkan offline.html dari cache
            return caches.match('/offline.html').then((offlineResponse) => {
              if (offlineResponse) {
                return offlineResponse;
              }
              // Fallback terakhir jika offline.html pun tidak ada di cache
              return getFallbackResponse(request);
            });
          });
        })
    );
    return;
  }

  // 2a. Fonts — CacheFirst dengan TTL 1 tahun (immutable filenames)
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

  // 2b. Vite Build Assets — CacheFirst (hash-keyed, immutable)
  if (url.pathname.startsWith('/build/assets/')) {
    event.respondWith(
      caches.open(ASSET_CACHE_NAME).then((cache) => {
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

  // 2c. Strategi untuk Static Assets lain (/build/, /asset/) - Stale-While-Revalidate
  const isStaticAsset = 
    url.pathname.startsWith('/build/') || 
    url.pathname.startsWith('/asset/') || 
    url.host.includes('fonts.gstatic.com');

  if (isStaticAsset) {
    event.respondWith(
      caches.match(request).then((cachedResponse) => {
        // Kembalikan cache instan, tapi fetch jaringan di latar belakang untuk memperbarui
        if (cachedResponse) {
          fetch(request).then((networkResponse) => {
            if (networkResponse.status === 200) {
              caches.open(CACHE_NAME).then((cache) => cache.put(request, networkResponse));
            }
          }).catch(() => { /* Abaikan error network di bg */ });
          return cachedResponse;
        }

        // Jika cache miss, fetch ke jaringan dan simpan ke cache
        return fetch(request)
          .then((response) => {
            if (!response || response.status !== 200) return response;
            const responseClone = response.clone();
            caches.open(CACHE_NAME).then((cache) => cache.put(request, responseClone));
            return response;
          })
          .catch(() => getFallbackResponse(request));
      })
    );
    return;
  }

  // 3. Strategi Default untuk Request Lain (Network-First)
  event.respondWith(
    fetch(request)
      .then((response) => {
        // Caching opsional untuk gambar dinamis
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
          if (cachedResponse) {
            return cachedResponse;
          }
          return getFallbackResponse(request);
        });
      })
  );
});
