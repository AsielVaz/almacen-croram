<?php

$ejecucionWeb = PHP_SAPI !== 'cli';

if ($ejecucionWeb) {
    header('Content-Type: application/json; charset=utf-8');
    header('Cache-Control: no-store');
}

$archivoBloqueo = sys_get_temp_dir() . '/croram_notificaciones_push.lock';
$bloqueo = fopen($archivoBloqueo, 'c');
if ($bloqueo === false) {
    $mensaje = 'No fue posible crear el bloqueo del proceso de notificaciones.';
    if ($ejecucionWeb) {
        http_response_code(500);
        echo json_encode(['status' => 'error', 'message' => $mensaje], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) . PHP_EOL;
    } else {
        fwrite(STDERR, $mensaje . PHP_EOL);
    }
    exit(1);
}

if (!flock($bloqueo, LOCK_EX | LOCK_NB)) {
    fclose($bloqueo);
    if ($ejecucionWeb) {
        http_response_code(409);
        echo json_encode([
            'status' => 'error',
            'message' => 'El proceso de notificaciones ya se encuentra en ejecución.',
        ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) . PHP_EOL;
    }
    exit(0);
}

register_shutdown_function(static function () use ($bloqueo): void {
    flock($bloqueo, LOCK_UN);
    fclose($bloqueo);
});

try {
    require_once dirname(__DIR__) . '/api/adminNotificaciones.php';
    $resultado = (new AdministradorNotificaciones())->procesarPendientes(100);
    echo json_encode($resultado, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) . PHP_EOL;
    exit(0);
} catch (Throwable $error) {
    if ($ejecucionWeb) {
        http_response_code(500);
        echo json_encode([
            'status' => 'error',
            'message' => $error->getMessage(),
        ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) . PHP_EOL;
    } else {
        fwrite(STDERR, $error->getMessage() . PHP_EOL);
    }
    exit(1);
}
