# Notificaciones de órdenes

El sistema registra una notificación para los usuarios activos con rol `ADMIN`, `ALMACEN` o `COMPRAS` cuando se crea una orden de entrada o de salida. El registro se realiza dentro de la misma transacción de la orden.

## Componentes

- `notificaciones`: bandeja por usuario, estado de lectura y estado de entrega push.
- `push_suscripciones`: dispositivos autorizados por cada usuario.
- `service-worker.js`: recepción del aviso fuera de la pestaña y apertura directa de la orden.
- `scripts/procesar_notificaciones.php`: reintento de entregas pendientes desde línea de comandos.

## Configuración

Las claves VAPID se leen de `CRORAM_VAPID_PUBLIC_KEY`, `CRORAM_VAPID_PRIVATE_KEY` y `CRORAM_VAPID_SUBJECT`. Para desarrollo también pueden definirse en `config/notificaciones.local.php`, archivo excluido de Git.

Después de desplegar el código se debe ejecutar `composer install --no-dev --optimize-autoloader` y aplicar `database/migrations/2026-10-12_notificaciones_push.sql`.

La creación de una orden intenta entregar los avisos inmediatamente. Como respaldo, el servidor puede ejecutar cada minuto:

```text
* * * * * cd /ruta/al/almacen-croram && /ruta/a/php scripts/procesar_notificaciones.php >/dev/null 2>&1
```

Web Push requiere HTTPS en producción. `localhost` se considera un contexto seguro para desarrollo.
