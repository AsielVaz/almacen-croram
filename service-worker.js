'use strict';

self.addEventListener('install', () => self.skipWaiting());
self.addEventListener('activate', event => event.waitUntil(self.clients.claim()));

self.addEventListener('push', event => {
    let data = {};
    try {
        data = event.data ? event.data.json() : {};
    } catch (error) {
        data = { title: 'Almacén Croram', body: event.data ? event.data.text() : '' };
    }

    const options = {
        body: data.body || 'Hay una nueva actividad en el almacén.',
        icon: 'assets/images/logo_almacen.png',
        badge: 'favicon.png',
        tag: data.tag || 'almacen-croram',
        renotify: true,
        data: { url: data.url || 'index.php' },
    };
    event.waitUntil(self.registration.showNotification(data.title || 'Almacén Croram', options));
});

self.addEventListener('notificationclick', event => {
    event.notification.close();
    const destino = new URL(event.notification.data?.url || 'index.php', self.registration.scope).href;
    event.waitUntil(self.clients.matchAll({ type: 'window', includeUncontrolled: true }).then(ventanas => {
        for (const ventana of ventanas) {
            if (ventana.url === destino && 'focus' in ventana) {
                return ventana.focus();
            }
        }
        return self.clients.openWindow ? self.clients.openWindow(destino) : undefined;
    }));
});

