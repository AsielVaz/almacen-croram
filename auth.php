<?php
if (session_status() !== PHP_SESSION_ACTIVE && !headers_sent()) {
    session_name('almacen_session');
    session_start();
}

function usuario_actual(): ?array {
    return $_SESSION['usuario_auth'] ?? null;
}

function usuario_autenticado(): bool {
    return !empty($_SESSION['usuario_auth']) && !empty($_SESSION['usuario_auth']['id']);
}

function usuario_id_actual(): int {
    return (int)($_SESSION['usuario_auth']['id'] ?? 0);
}

function iniciar_sesion_usuario(array $usuario, bool $recordarUsuario = false): void {
    session_regenerate_id(true);
    $_SESSION['usuario_auth'] = [
        'id' => (int)($usuario['id'] ?? 0),
        'nombre' => $usuario['nombre'] ?? '',
        'usuario' => $usuario['usuario'] ?? '',
        'email' => $usuario['email'] ?? '',
        'rol' => $usuario['rol'] ?? '',
        'activo' => (int)($usuario['activo'] ?? 0),
    ];

    if ($recordarUsuario) {
        setcookie('recordar_usuario', (string)($usuario['email'] ?? ''), [
            'expires' => time() + (60 * 60 * 24 * 30),
            'path' => '/',
            'httponly' => false,
            'samesite' => 'Lax',
        ]);
    } else {
        setcookie('recordar_usuario', '', [
            'expires' => time() - 3600,
            'path' => '/',
            'httponly' => false,
            'samesite' => 'Lax',
        ]);
    }
}

function cerrar_sesion_usuario(): void {
    $_SESSION = [];

    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
    }

    session_destroy();
}

function requerir_autenticacion(): void {
    if (usuario_autenticado()) {
        return;
    }

    if (!headers_sent()) {
        header('Location: login.php');
    }
    exit;
}

function requerir_autenticacion_json(): void {
    if (usuario_autenticado()) {
        return;
    }

    http_response_code(401);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode([
        'status' => 'error',
        'message' => 'Debes iniciar sesion para continuar'
    ]);
    exit;
}
