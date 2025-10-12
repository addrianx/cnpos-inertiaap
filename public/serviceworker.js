// serviceworker.js
const CACHE_NAME = 'pos-toko-cache-v3';
const urlsToCache = [
  '/',
  '/manifest.json',
  '/icons/icon-192x192.png',  
  '/icons/icon-512x512.png'   
];

// Install event dengan error handling
self.addEventListener('install', (event) => {
  console.log('Service Worker: Installing...');
  
  event.waitUntil(
    caches.open(CACHE_NAME)
      .then((cache) => {
        console.log('Service Worker: Caching essential files');
        
        // Gunakan approach yang lebih robust daripada cache.addAll()
        return Promise.all(
          urlsToCache.map(url => {
            return cache.add(url).catch(error => {
              console.log(`Failed to cache ${url}:`, error);
              // Continue even if some files fail
              return Promise.resolve();
            });
          })
        );
      })
      .then(() => {
        console.log('Service Worker: All files processed, skip waiting');
        return self.skipWaiting();
      })
      .catch(error => {
        console.log('Service Worker: Installation failed:', error);
        // Jangan gagal instalasi hanya karena caching error
        return self.skipWaiting();
      })
  );
});

// Activate event
self.addEventListener('activate', (event) => {
  console.log('Service Worker: Activated');
  event.waitUntil(
    caches.keys().then((cacheNames) => {
      return Promise.all(
        cacheNames.map((cache) => {
          if (cache !== CACHE_NAME) {
            console.log('Service Worker: Clearing old cache', cache);
            return caches.delete(cache);
          }
        })
      );
    }).then(() => {
      console.log('Service Worker: Claiming clients');
      return self.clients.claim();
    })
  );
});

// Fetch event yang lebih robust
self.addEventListener('fetch', (event) => {
  // Skip non-GET requests
  if (event.request.method !== 'GET') {
    return;
  }

  // Skip external requests
  if (!event.request.url.startsWith(self.location.origin)) {
    return;
  }

  // Skip API calls
  if (event.request.url.includes('/api/') || 
      event.request.url.includes('/sanctum/') ||
      event.request.url.includes('/broadcasting/')) {
    return;
  }

  event.respondWith(
    caches.match(event.request)
      .then((response) => {
        // Return cached version jika ada
        if (response) {
          console.log('Service Worker: Serving from cache:', event.request.url);
          return response;
        }

        // Clone the request untuk fetch
        const fetchRequest = event.request.clone();

        return fetch(fetchRequest)
          .then((response) => {
            // Check jika response valid
            if (!response || response.status !== 200 || response.type === 'opaque') {
              return response;
            }

            // Clone the response untuk caching
            const responseToCache = response.clone();

            // Cache the response
            caches.open(CACHE_NAME)
              .then((cache) => {
                // Hanya cache successful responses
                if (response.status === 200) {
                  cache.put(event.request, responseToCache)
                    .catch(error => {
                      console.log('Failed to cache response:', error);
                    });
                }
              });

            return response;
          })
          .catch((error) => {
            console.log('Fetch failed:', error);
            
            // Fallback untuk halaman utama
            if (event.request.destination === 'document' || 
                event.request.headers.get('accept').includes('text/html')) {
              return caches.match('/')
                .then(response => response || new Response('You are offline'));
            }
            
            // Fallback untuk images
            if (event.request.destination === 'image') {
              return new Response('', { 
                status: 404, 
                statusText: 'Image not available offline' 
              });
            }
            
            return new Response('Network error happened', {
              status: 408,
              headers: { 'Content-Type': 'text/plain' }
            });
          });
      })
  );
});