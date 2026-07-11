const CACHE_NAME = 'keuangan-app-v1';
const ASSETS = [
  '/',
  '/css/style.css',
  '/js/app.js',
  'https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap',
  'https://fonts.googleapis.com/icon?family=Material+Icons'
];

// Install Service Worker
self.addEventListener('install', (e) => {
  e.waitUntil(
    caches.open(CACHE_NAME).then((cache) => {
      // Caching assets secara opsional, tidak block jika gagal (karena dinamis/local)
      return cache.addAll(ASSETS).catch(err => console.log("SW Caching warning: ", err));
    })
  );
  self.skipWaiting();
});

// Activate Service Worker
self.addEventListener('activate', (e) => {
  e.waitUntil(
    caches.keys().then((keys) => {
      return Promise.all(
        keys.map((key) => {
          if (key !== CACHE_NAME) {
            return caches.delete(key);
          }
        })
      );
    })
  );
  self.clients.claim();
});

// Fetch handler (Network First, fallback to cache)
self.addEventListener('fetch', (e) => {
  // Hanya tangani request HTTP/HTTPS (hindari chrome-extension atau file://)
  if (!e.request.url.startsWith('http')) return;

  e.respondWith(
    fetch(e.request)
      .then((response) => {
        // Clone response dan simpan ke cache jika sukses
        if (response.status === 200 && e.request.method === 'GET') {
          const responseClone = response.clone();
          caches.open(CACHE_NAME).then((cache) => {
            cache.put(e.request, responseClone);
          });
        }
        return response;
      })
      .catch(() => {
        // Offline fallback
        return caches.match(e.request);
      })
  );
});
