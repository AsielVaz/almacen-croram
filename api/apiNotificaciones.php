<?php

require_once __DIR__ . '/../auth.php';
requerir_autenticacion_json();
require_once __DIR__ . '/adminNotificaciones.php';

header('Content-Type: application/json; charset=utf-8');

$entrada = json_decode(file_get_contents('php://input'), true);
if (!is_array($entrada)) {
    $entrada = $_POST;
}
$accion = (string)($entrada['accion'] ?? $_GET['accion'] ?? 'listar');
$idUsuario = usuario_id_actual();
$admin = new AdministradorNotificaciones();

if (!in_array($accion, ['listar', 'configuracion'], true)
    && ($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') !== 'XMLHttpRequest') {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Solicitud no válida.']);
    exit;
}

try {
    switch ($accion) {
        case 'configuracion':
            $configuracion = require dirname(__DIR__) . '/config/notificaciones.php';
            echo json_encode([
                'status' => 'success',
                'publicKey' => $configuracion['vapid_public_key'] ?? '',
            ]);
            break;

        case 'suscribir':
            $admin->guardarSuscripcion($idUsuario, $entrada['suscripcion'] ?? [], $_SERVER['HTTP_USER_AGENT'] ?? '');
            echo json_encode(['status' => 'success', 'message' => 'Notificaciones activadas en este dispositivo.']);
            break;

        case 'desuscribir':
            $admin->desactivarSuscripcion($idUsuario, (string)($entrada['endpoint'] ?? ''));
            echo json_encode(['status' => 'success', 'message' => 'Notificaciones desactivadas en este dispositivo.']);
            break;

        case 'marcarLeida':
            $admin->marcarLeida($idUsuario, (int)($entrada['id'] ?? 0));
            echo json_encode(['status' => 'success']);
            break;

        case 'marcarTodasLeidas':
            $admin->marcarTodasLeidas($idUsuario);
            echo json_encode(['status' => 'success']);
            break;

        case 'listar':
            echo json_encode([
                'status' => 'success',
                'noLeidas' => $admin->contarNoLeidas($idUsuario),
                'notificaciones' => $admin->listar($idUsuario),
            ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            break;

        default:
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'Acción no válida.']);
    }
} catch (Throwable $error) {
    http_response_code(400);
    $payload = json_decode($error->getMessage(), true);
    echo json_encode($payload ?: ['status' => 'error', 'message' => $error->getMessage()], JSON_UNESCAPED_UNICODE);
}
