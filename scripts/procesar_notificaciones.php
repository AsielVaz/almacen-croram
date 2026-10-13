<?php

$ejecucionWeb = PHP_SAPI !== 'cli';

if ($ejecucionWeb) {
    require_once dirname(__DIR__) . '/auth.php';
    header('Content-Type: application/json; charset=utf-8');
    header('Cache-Control: no-store');
    requerir_autenticacion_json();

    if (!usuario_es_admin()) {
        http_response_code(403);
        echo json_encode([
            'status' => 'error',
            'message' => 'Solo un administrador puede ejecutar este proceso.',
        ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) . PHP_EOL;
        exit;
    }
}

$archivoBloqueo = sys_get_temp_dir() . '/croram_notificaciones_push.lock';
$bloqueo = fopen($archivoBloqueo, 'c');
if ($bloqueo === false) {
    fwrite(STDERR, "No fue posible crear el bloqueo del proceso de notificaciones.\n");
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

require_once dirname(__DIR__) . '/api/adminNotificaciones.php';

try {
    $resultado = (new AdministradorNotificaciones())->procesarPendientes(100);
    echo json_encode($resultado, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) . PHP_EOL;
    exit(0);
} catch (Throwable $error) {
    fwrite(STDERR, $error->getMessage() . PHP_EOL);
    exit(1);
}
