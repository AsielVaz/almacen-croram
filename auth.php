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

function usuario_rol_actual(): string {
    $rol = strtoupper(trim((string)($_SESSION['usuario_auth']['rol'] ?? '')));

    $rolesLegados = [
        'COMPRAS' => 'ALMACEN',
        'VENTAS' => 'CONSUMIDOR',
    ];

    return $rolesLegados[$rol] ?? $rol;
}

function usuario_es_admin(): bool {
    return usuario_rol_actual() === 'ADMIN';
}

function usuario_tiene_permiso(string $permiso): bool {
    $rol = usuario_rol_actual();

    if ($rol === 'ADMIN') {
        return true;
    }

    $permisosPorRol = [
        'ALMACEN' => [
            'panel_ver',
            'articulos_ver',
            'catalogos_operativos',
            'ordenes_compra_ver',
            'ordenes_compra_crear',
            'ordenes_salida_ver',
            'ordenes_salida_crear',
            'ordenes_validar_qr',
            'reportes_ver',
        ],
        'CONSUMIDOR' => [
            'ordenes_salida_crear',
        ],
        'LECTOR' => [
            'panel_ver',
            'articulos_ver',
            'ordenes_compra_ver',
            'ordenes_salida_ver',
            'reportes_ver',
        ],
    ];

    return in_array($permiso, $permisosPorRol[$rol] ?? [], true);
}

function ruta_inicio_usuario(): string {
    return usuario_rol_actual() === 'CONSUMIDOR'
        ? 'ordenes-salida-form-fast.php'
        : 'index.php';
}

function requerir_permiso(string $permiso): void {
    if (usuario_tiene_permiso($permiso)) {
        return;
    }

    $_SESSION['mensaje_acceso'] = 'No tienes permisos para acceder a esa sección.';
    if (!headers_sent()) {
        header('Location: ' . ruta_inicio_usuario());
    }
    exit;
}

function requerir_permiso_json(string $permiso): void {
    if (usuario_tiene_permiso($permiso)) {
        return;
    }

    http_response_code(403);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode([
        'status' => 'error',
        'message' => 'No tienes permisos para realizar esta acción.',
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

function permiso_ruta_actual(): ?string {
    $ruta = basename((string)($_SERVER['SCRIPT_NAME'] ?? ''));
    $rutas = [
        'index.php' => 'panel_ver',
        'articulos.php' => 'articulos_ver',
        'articulo-historial.php' => 'articulos_ver',
        'articulos-form.php' => 'articulos_administrar',
        'carga-inv.php' => 'articulos_administrar',
        'catalogos-familias.php' => 'catalogos_operativos',
        'catalogos-familias-form.php' => 'catalogos_operativos',
        'catalogos-sub-familias.php' => 'catalogos_operativos',
        'catalogos-subfamilias-form.php' => 'catalogos_operativos',
        'proveedores-alta.php' => 'catalogos_operativos',
        'proveedores-ver.php' => 'catalogos_operativos',
        'areas-alta.php' => 'areas_administrar',
        'areas-ver.php' => 'areas_administrar',
        'usuarios.php' => 'usuarios_administrar',
        'usuarios-form.php' => 'usuarios_administrar',
        'ordenes-entrada.php' => 'ordenes_compra_ver',
        'ordenes-entrada-detalle.php' => 'ordenes_compra_ver',
        'ordenes-entrada-form.php' => 'ordenes_compra_crear',
        'ordenes-salida.php' => 'ordenes_salida_ver',
        'ordenes-salida-detalle.php' => 'ordenes_salida_ver',
        'ordenes-salida-escaner.php' => 'ordenes_validar_qr',
        'ordenes-salida-form.php' => 'ordenes_salida_crear',
        'ordenes-salida-form-fast.php' => 'ordenes_salida_crear',
        'reportes.php' => 'reportes_ver',
        'reportes-exportar.php' => 'reportes_ver',
        'reportes-bajo-stock.php' => 'reportes_ver',
        'reportes-compras-sugeridas.php' => 'reportes_ver',
        'reportes-inventario-aleatorio.php' => 'reportes_ver',
        'reporte-inventario.php' => 'reportes_ver',
        'reportes-ordenes-entrada-sin-autorizar.php' => 'ordenes_compra_autorizar',
    ];

    return $rutas[$ruta] ?? null;
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
        $permiso = permiso_ruta_actual();
        if ($permiso !== null) {
            requerir_permiso($permiso);
        }
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
