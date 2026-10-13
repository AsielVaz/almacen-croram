<?php

require_once __DIR__ . '/conector.php';

class AdministradorNotificaciones extends Con
{
    public function guardarSuscripcion(int $idUsuario, array $suscripcion, string $agenteUsuario = ''): void
    {
        $endpoint = trim((string)($suscripcion['endpoint'] ?? ''));
        $clavePublica = trim((string)($suscripcion['keys']['p256dh'] ?? ''));
        $token = trim((string)($suscripcion['keys']['auth'] ?? ''));
        $codificacion = trim((string)($suscripcion['contentEncoding'] ?? 'aes128gcm'));

        if ($idUsuario <= 0 || $endpoint === '' || $clavePublica === '' || $token === '') {
            throw new InvalidArgumentException('La suscripción del navegador está incompleta.');
        }

        if (!str_starts_with($endpoint, 'https://')) {
            throw new InvalidArgumentException('El servicio de notificaciones proporcionó un endpoint no seguro.');
        }

        $endpointSql = $this->escapar($endpoint);
        $hashSql = hash('sha256', $endpoint);
        $claveSql = $this->escapar($clavePublica);
        $tokenSql = $this->escapar($token);
        $codificacionSql = $this->escapar($codificacion ?: 'aes128gcm');
        $agenteSql = $this->escapar(mb_substr($agenteUsuario, 0, 500));

        $this->ejecutar("\n            INSERT INTO push_suscripciones\n                (id_usuario, endpoint, endpoint_hash, clave_publica, token_autenticacion, codificacion, agente_usuario, activo)\n            VALUES\n                ($idUsuario, '$endpointSql', '$hashSql', '$claveSql', '$tokenSql', '$codificacionSql', '$agenteSql', 1)\n            ON DUPLICATE KEY UPDATE\n                id_usuario = VALUES(id_usuario),\n                endpoint = VALUES(endpoint),\n                clave_publica = VALUES(clave_publica),\n                token_autenticacion = VALUES(token_autenticacion),\n                codificacion = VALUES(codificacion),\n                agente_usuario = VALUES(agente_usuario),\n                activo = 1,\n                ultimo_error = NULL\n        ");
    }

    public function desactivarSuscripcion(int $idUsuario, string $endpoint): void
    {
        $hashSql = hash('sha256', trim($endpoint));
        $this->ejecutar("\n            UPDATE push_suscripciones\n            SET activo = 0\n            WHERE id_usuario = $idUsuario AND endpoint_hash = '$hashSql'\n        ");
    }

    public function listar(int $idUsuario, int $limite = 15): array
    {
        $limite = max(1, min(50, $limite));
        return json_decode($this->ejecutar("\n            SELECT id, tipo, id_referencia, titulo, mensaje, url, leida, created_at\n            FROM notificaciones\n            WHERE id_usuario = $idUsuario\n            ORDER BY created_at DESC, id DESC\n            LIMIT $limite\n        "), true) ?: [];
    }

    public function contarNoLeidas(int $idUsuario): int
    {
        $fila = json_decode($this->ejecutar("\n            SELECT COUNT(*) AS total FROM notificaciones\n            WHERE id_usuario = $idUsuario AND leida = 0\n        "), true)[0] ?? ['total' => 0];
        return (int)$fila['total'];
    }

    public function marcarLeida(int $idUsuario, int $idNotificacion): void
    {
        $this->ejecutar("\n            UPDATE notificaciones SET leida = 1\n            WHERE id = $idNotificacion AND id_usuario = $idUsuario\n        ");
    }

    public function marcarTodasLeidas(int $idUsuario): void
    {
        $this->ejecutar("UPDATE notificaciones SET leida = 1 WHERE id_usuario = $idUsuario AND leida = 0");
    }

    public function procesarPendientes(int $limite = 25): array
    {
        $configuracion = require dirname(__DIR__) . '/config/notificaciones.php';
        if (empty($configuracion['vapid_public_key']) || empty($configuracion['vapid_private_key'])) {
            throw new RuntimeException('No se han configurado las claves VAPID.');
        }

        require_once dirname(__DIR__) . '/vendor/autoload.php';
        $limite = max(1, min(100, $limite));
        $this->ejecutar("\n            UPDATE notificaciones\n            SET push_estatus = 'ERROR', push_error = 'Entrega interrumpida; se programó un nuevo intento.'\n            WHERE push_estatus = 'PROCESANDO'\n              AND procesado_at < DATE_SUB(NOW(), INTERVAL 10 MINUTE)\n        ");
        $pendientes = json_decode($this->ejecutar("\n            SELECT * FROM notificaciones\n            WHERE push_estatus IN ('PENDIENTE', 'ERROR') AND push_intentos < 5\n            ORDER BY created_at ASC, id ASC\n            LIMIT $limite\n        "), true) ?: [];
        $resultado = ['procesadas' => 0, 'enviadas' => 0, 'sin_suscripcion' => 0, 'errores' => 0];

        foreach ($pendientes as $notificacion) {
            $idNotificacion = (int)$notificacion['id'];
            $idUsuario = (int)$notificacion['id_usuario'];
            $this->ejecutar("\n                UPDATE notificaciones\n                SET push_estatus = 'PROCESANDO', push_intentos = push_intentos + 1, procesado_at = NOW()\n                WHERE id = $idNotificacion AND push_estatus IN ('PENDIENTE', 'ERROR')\n            ");
            if ($this->filasAfectadas() !== 1) {
                continue;
            }

            $suscripciones = json_decode($this->ejecutar("\n                SELECT * FROM push_suscripciones\n                WHERE id_usuario = $idUsuario AND activo = 1\n            "), true) ?: [];

            if (!$suscripciones) {
                $this->actualizarResultadoPush($idNotificacion, 'SIN_SUSCRIPCION', null);
                $resultado['sin_suscripcion']++;
                $resultado['procesadas']++;
                continue;
            }

            try {
                $webPush = new Minishlink\WebPush\WebPush([
                    'VAPID' => [
                        'subject' => $configuracion['vapid_subject'],
                        'publicKey' => $configuracion['vapid_public_key'],
                        'privateKey' => $configuracion['vapid_private_key'],
                    ],
                ], ['TTL' => 86400, 'urgency' => 'normal'], 10);

                $payload = json_encode([
                    'title' => $notificacion['titulo'],
                    'body' => $notificacion['mensaje'],
                    'url' => $notificacion['url'],
                    'tag' => $notificacion['tipo'] . '-' . $notificacion['id_referencia'],
                ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

                foreach ($suscripciones as $suscripcion) {
                    $webPush->queueNotification(Minishlink\WebPush\Subscription::create([
                        'endpoint' => $suscripcion['endpoint'],
                        'publicKey' => $suscripcion['clave_publica'],
                        'authToken' => $suscripcion['token_autenticacion'],
                        'contentEncoding' => $suscripcion['codificacion'],
                    ]), $payload);
                }

                $enviada = false;
                $ultimoError = '';
                foreach ($webPush->flush() as $reporte) {
                    if ($reporte->isSuccess()) {
                        $enviada = true;
                        continue;
                    }
                    $ultimoError = mb_substr($reporte->getReason(), 0, 500);
                    $hashEndpoint = hash('sha256', $reporte->getEndpoint());
                    $errorSql = $this->escapar($ultimoError);
                    $activo = $reporte->isSubscriptionExpired() ? 0 : 1;
                    $this->ejecutar("\n                        UPDATE push_suscripciones\n                        SET activo = $activo, ultimo_error = '$errorSql'\n                        WHERE endpoint_hash = '$hashEndpoint'\n                    ");
                }

                if ($enviada) {
                    $this->actualizarResultadoPush($idNotificacion, 'ENVIADA', null);
                    $resultado['enviadas']++;
                } else {
                    $this->actualizarResultadoPush($idNotificacion, 'ERROR', $ultimoError ?: 'No fue posible entregar la notificación.');
                    $resultado['errores']++;
                }
            } catch (Throwable $error) {
                $this->actualizarResultadoPush($idNotificacion, 'ERROR', $error->getMessage());
                $resultado['errores']++;
            }
            $resultado['procesadas']++;
        }

        return $resultado;
    }

    private function actualizarResultadoPush(int $idNotificacion, string $estatus, ?string $error): void
    {
        $estatusSql = $this->escapar($estatus);
        $errorSql = $error === null ? 'NULL' : "'" . $this->escapar(mb_substr($error, 0, 500)) . "'";
        $this->ejecutar("\n            UPDATE notificaciones\n            SET push_estatus = '$estatusSql', push_error = $errorSql, procesado_at = NOW()\n            WHERE id = $idNotificacion\n        ");
    }
}
