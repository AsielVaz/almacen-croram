<?php

if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}

$archivoBloqueo = sys_get_temp_dir() . '/croram_notificaciones_push.lock';
$bloqueo = fopen($archivoBloqueo, 'c');
if ($bloqueo === false) {
    fwrite(STDERR, "No fue posible crear el bloqueo del proceso de notificaciones.\n");
    exit(1);
}

if (!flock($bloqueo, LOCK_EX | LOCK_NB)) {
    fclose($bloqueo);
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
