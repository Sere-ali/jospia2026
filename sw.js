/* Service worker JOSPIA : les pages restent toujours en direct (réseau), seuls les fichiers fixes sont mis en cache. */
var V = 'jospia-v1';
var HORS_LIGNE = '<!DOCTYPE html><html lang="fr"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Hors connexion</title><style>body{font-family:sans-serif;text-align:center;padding:60px 20px;color:#0b3d2a}button{background:#0B8A4E;color:#fff;border:0;border-radius:10px;padding:12px 22px;font-size:1rem}</style></head><body><h2>Pas de connexion internet</h2><p>Reconnectez-vous puis réessayez.</p><button onclick="location.reload()">Réessayer</button></body></html>';
self.addEventListener('install', function () { self.skipWaiting(); });
self.addEventListener('activate', function (e) {
    e.waitUntil(caches.keys().then(function (k) { return Promise.all(k.filter(function (n) { return n !== V; }).map(function (n) { return caches.delete(n); })); }).then(function () { return self.clients.claim(); }));
});
self.addEventListener('fetch', function (e) {
    var r = e.request;
    if (r.method !== 'GET') return;
    var u = new URL(r.url);
    if (u.origin !== location.origin) return;
    if (r.mode === 'navigate') {
        e.respondWith(fetch(r).catch(function () { return new Response(HORS_LIGNE, { headers: { 'Content-Type': 'text/html; charset=utf-8' } }); }));
        return;
    }
    if (/\/assets\/(img|icons)\//.test(u.pathname)) {
        e.respondWith(caches.open(V).then(function (c) {
            return c.match(r).then(function (m) {
                return m || fetch(r).then(function (n) { if (n.ok) c.put(r, n.clone()); return n; });
            });
        }));
    }
});
