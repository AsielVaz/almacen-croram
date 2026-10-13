(() => {
    'use strict';

    const contenedor = document.getElementById('centro-notificaciones');
    if (!contenedor) return;

    const api = 'api/apiNotificaciones.php';
    const lista = document.getElementById('lista-notificaciones');
    const badge = document.getElementById('notificaciones-badge');
    const botonPush = document.getElementById('notificaciones-push');
    const botonLeidas = document.getElementById('notificaciones-marcar-todas');
    const escapeHtml = texto => String(texto ?? '').replace(/[&<>'"]/g, caracter => ({
        '&': '&amp;', '<': '&lt;', '>': '&gt;', "'": '&#39;', '"': '&quot;'
    })[caracter]);

    async function solicitar(accion, datos = {}) {
        const respuesta = await fetch(api, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
            credentials: 'same-origin',
            body: JSON.stringify({ accion, ...datos }),
        });
        const json = await respuesta.json();
        if (!respuesta.ok || json.status !== 'success') throw new Error(json.message || 'No fue posible completar la solicitud.');
        return json;
    }

    function fechaLegible(valor) {
        const fecha = new Date(String(valor).replace(' ', 'T'));
        return Number.isNaN(fecha.getTime()) ? '' : new Intl.DateTimeFormat('es-MX', {
            dateStyle: 'short', timeStyle: 'short'
        }).format(fecha);
    }

    async function cargar() {
        try {
            const datos = await solicitar('listar');
            const noLeidas = Number(datos.noLeidas || 0);
            badge.textContent = noLeidas > 99 ? '99+' : String(noLeidas);
            badge.classList.toggle('d-none', noLeidas === 0);

            if (!datos.notificaciones.length) {
                lista.innerHTML = '<div class="px-3 py-4 text-center text-muted">No hay notificaciones.</div>';
                return;
            }
            lista.innerHTML = datos.notificaciones.map(item => `
                <button type="button" class="dropdown-item notificacion-item ${Number(item.leida) ? '' : 'notificacion-no-leida'}"
                    data-id="${Number(item.id)}" data-url="${escapeHtml(item.url)}">
                    <span class="notificacion-icono"><i class="ri-file-list-3-line"></i></span>
                    <span class="min-w-0 flex-grow-1">
                        <strong class="d-block text-wrap">${escapeHtml(item.titulo)}</strong>
                        <span class="d-block small text-muted text-wrap">${escapeHtml(item.mensaje)}</span>
                        <span class="d-block notificacion-fecha">${escapeHtml(fechaLegible(item.created_at))}</span>
                    </span>
                </button>`).join('');
        } catch (error) {
            lista.innerHTML = '<div class="px-3 py-4 text-center text-danger">No fue posible cargar los avisos.</div>';
        }
    }

    function convertirClave(clave) {
        const relleno = '='.repeat((4 - clave.length % 4) % 4);
        const base64 = (clave + relleno).replace(/-/g, '+').replace(/_/g, '/');
        return Uint8Array.from(atob(base64), caracter => caracter.charCodeAt(0));
    }

    function datosSuscripcion(suscripcion) {
        const datos = suscripcion.toJSON();
        datos.contentEncoding = (PushManager.supportedContentEncodings || ['aes128gcm'])[0];
        return datos;
    }

    async function estadoPush() {
        if (!('serviceWorker' in navigator) || !('PushManager' in window) || !('Notification' in window)) {
            botonPush.textContent = 'No compatible';
            botonPush.disabled = true;
            return;
        }
        const registro = await navigator.serviceWorker.register('service-worker.js');
        const suscripcion = await registro.pushManager.getSubscription();
        if (suscripcion) {
            await solicitar('suscribir', { suscripcion: datosSuscripcion(suscripcion) });
        }
        botonPush.textContent = suscripcion ? 'Desactivar en este dispositivo' : 'Activar en este dispositivo';
        botonPush.dataset.activo = suscripcion ? '1' : '0';
        if (Notification.permission === 'denied') {
            botonPush.textContent = 'Permiso bloqueado en el navegador';
            botonPush.disabled = true;
        }
    }

    async function alternarPush() {
        botonPush.disabled = true;
        try {
            const registro = await navigator.serviceWorker.ready;
            let suscripcion = await registro.pushManager.getSubscription();
            if (suscripcion) {
                await solicitar('desuscribir', { endpoint: suscripcion.endpoint });
                await suscripcion.unsubscribe();
            } else {
                const permiso = await Notification.requestPermission();
                if (permiso !== 'granted') throw new Error('El navegador no autorizó las notificaciones.');
                const configuracion = await solicitar('configuracion');
                if (!configuracion.publicKey) throw new Error('Las notificaciones aún no están configuradas en el servidor.');
                suscripcion = await registro.pushManager.subscribe({
                    userVisibleOnly: true,
                    applicationServerKey: convertirClave(configuracion.publicKey),
                });
                await solicitar('suscribir', { suscripcion: datosSuscripcion(suscripcion) });
            }
            await estadoPush();
        } catch (error) {
            window.alert(error.message);
        } finally {
            botonPush.disabled = false;
        }
    }

    lista.addEventListener('click', async event => {
        const item = event.target.closest('.notificacion-item');
        if (!item) return;
        try { await solicitar('marcarLeida', { id: Number(item.dataset.id) }); } catch (error) { /* Navegar de todos modos. */ }
        window.location.href = item.dataset.url;
    });
    botonLeidas.addEventListener('click', async () => {
        await solicitar('marcarTodasLeidas');
        await cargar();
    });
    botonPush.addEventListener('click', alternarPush);
    contenedor.addEventListener('toggle', () => {
        if (contenedor.open) cargar();
    });
    document.addEventListener('click', event => {
        if (contenedor.contains(event.target)) return;
        contenedor.removeAttribute('open');
    });
    document.querySelectorAll('a[href$="logout.php"]').forEach(enlaceSalir => {
        enlaceSalir.addEventListener('click', async event => {
            if (!('serviceWorker' in navigator)) return;
            event.preventDefault();
            try {
                const registro = await navigator.serviceWorker.getRegistration();
                const suscripcion = registro ? await registro.pushManager.getSubscription() : null;
                if (suscripcion) await solicitar('desuscribir', { endpoint: suscripcion.endpoint });
            } catch (error) {
                // El cierre de sesión debe continuar aunque el navegador no responda.
            }
            window.location.href = enlaceSalir.href;
        });
    });

    cargar();
    estadoPush().catch(() => {});
    window.setInterval(cargar, 60000);
})();
