<?php
header('Content-Type: application/json; charset=utf-8');

include_once 'adminOrdenes.php';

$config = include __DIR__ . '/mobile_config.php';
$origin = $_SERVER['HTTP_ORIGIN'] ?? '';

if ($origin !== '' && in_array($origin, $config['allowed_origins'] ?? [], true)) {
    header('Access-Control-Allow-Origin: ' . $origin);
    header('Vary: Origin');
}

header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, X-CRORAM-Mobile-Token');
header('Access-Control-Max-Age: 86400');

if (($_SERVER['REQUEST_METHOD'] ?? '') === 'OPTIONS') {
    http_response_code(204);
    exit;
}

function responder($payload, int $codigo = 200): void {
    http_response_code($codigo);
    echo json_encode($payload, JSON_UNESCAPED_UNICODE);
    exit;
}

function leer_payload(): array {
    $raw = file_get_contents('php://input') ?: '';
    $json = json_decode($raw, true);
    if (is_array($json)) {
        return $json;
    }

    return $_POST ?: [];
}

function autorizar_api(array $config): void {
    $tokenEsperado = (string)($config['token'] ?? '');
    $tokenRecibido = $_SERVER['HTTP_X_CRORAM_MOBILE_TOKEN'] ?? '';

    if ($tokenEsperado === '' || !hash_equals($tokenEsperado, $tokenRecibido)) {
        responder([
            'status' => 'error',
            'message' => 'Token movil invalido',
        ], 401);
    }
}

function normalizar_codigo($codigo): string {
    $codigo = trim((string)$codigo);
    if ($codigo === '') {
        return '';
    }

    $partes = parse_url($codigo);
    if (!empty($partes['query'])) {
        parse_str($partes['query'], $query);
        if (!empty($query['id'])) {
            return trim((string)$query['id']);
        }
    }

    return $codigo;
}

function normalizar_tipo($tipo): string {
    $tipo = strtolower(trim((string)$tipo));
    if (in_array($tipo, ['entrada', 'compra', 'orden_compra', 'oc'], true)) {
        return 'entrada';
    }
    if (in_array($tipo, ['salida', 'orden_salida', 'os'], true)) {
        return 'salida';
    }
    return '';
}

function obtener_orden(AdministradorOrdenes $admin, string $tipo, string $codigo): ?array {
    $codigo = normalizar_codigo($codigo);
    $codigoSql = $admin->escapar($codigo);

    if ($tipo === '') {
        if (stripos($codigo, 'OC-') === 0) {
            $tipo = 'entrada';
        } elseif (stripos($codigo, 'OS-') === 0) {
            $tipo = 'salida';
        }
    }

    if ($tipo === 'entrada') {
        $where = ctype_digit($codigo) ? "oc.id = " . (int)$codigo : "oc.folio = '$codigoSql'";
        $orden = json_decode($admin->ejecutar("
            SELECT
                oc.*,
                pr.nombre AS nombre_proveedor,
                COALESCE(u.nombre, CONCAT('Usuario #', oc.id_usuario)) AS nombre_usuario
            FROM ordenes_compra oc
            INNER JOIN proveedores pr ON pr.id = oc.id_proveedor
            LEFT JOIN usuarios u ON u.id = oc.id_usuario
            WHERE $where
            LIMIT 1
        "), true)[0] ?? null;

        return $orden ? ['tipo' => 'entrada', 'orden' => $orden] : null;
    }

    if ($tipo === 'salida') {
        $where = ctype_digit($codigo) ? "os.id = " . (int)$codigo : "os.folio = '$codigoSql'";
        $orden = json_decode($admin->ejecutar("
            SELECT
                os.*,
                COALESCE(a.nombre, '') AS nombre_area,
                COALESCE(u.nombre, CONCAT('Usuario #', os.id_usuario)) AS nombre_usuario
            FROM ordenes_salida os
            LEFT JOIN areas a ON a.id = os.id_area
            LEFT JOIN usuarios u ON u.id = os.id_usuario
            WHERE $where
            LIMIT 1
        "), true)[0] ?? null;

        return $orden ? ['tipo' => 'salida', 'orden' => $orden] : null;
    }

    $entrada = obtener_orden($admin, 'entrada', $codigo);
    if ($entrada) {
        return $entrada;
    }

    return obtener_orden($admin, 'salida', $codigo);
}

function detalles_orden(AdministradorOrdenes $admin, string $tipo, int $idOrden): array {
    $detalles = $tipo === 'entrada'
        ? json_decode($admin->listarDetallesOrden($idOrden), true)
        : json_decode($admin->listarDetallesOrdenSalida($idOrden), true);

    return is_array($detalles) ? $detalles : [];
}

function agrupar_esperados(array $detalles): array {
    $esperados = [];

    foreach ($detalles as $detalle) {
        $idProducto = (int)($detalle['id_producto'] ?? 0);
        if ($idProducto <= 0) {
            continue;
        }

        if (!isset($esperados[$idProducto])) {
            $esperados[$idProducto] = [
                'id_producto' => $idProducto,
                'sku' => (string)($detalle['sku'] ?? ''),
                'nombre_producto' => (string)($detalle['nombre_producto'] ?? ''),
                'ubicacion' => (string)($detalle['ubicacion'] ?? ''),
                'cantidad_requerida' => 0,
                'cantidad_validada' => 0,
                'precio_unitario' => (float)($detalle['precio_unitario'] ?? $detalle['costo_promedio'] ?? 0),
            ];
        }

        $esperados[$idProducto]['cantidad_requerida'] += max(0, (int)round((float)($detalle['cantidad'] ?? 0)));
    }

    return $esperados;
}

function validar_escaneos(array $detalles, array $escaneos): array {
    $esperados = agrupar_esperados($detalles);
    $extras = [];

    foreach ($escaneos as $escaneo) {
        $codigo = is_array($escaneo) ? ($escaneo['codigo'] ?? $escaneo['sku'] ?? $escaneo['id_producto'] ?? '') : $escaneo;
        $codigo = strtoupper(trim((string)$codigo));
        if ($codigo === '') {
            continue;
        }

        $encontrado = false;
        foreach ($esperados as &$producto) {
            $sku = strtoupper(trim((string)$producto['sku']));
            $idProducto = strtoupper((string)$producto['id_producto']);

            if ($codigo === $sku || $codigo === $idProducto) {
                $producto['cantidad_validada']++;
                $encontrado = true;
                break;
            }
        }
        unset($producto);

        if (!$encontrado) {
            $extras[] = $codigo;
        }
    }

    $faltantes = [];
    $completos = [];

    foreach ($esperados as $producto) {
        $pendiente = max(0, $producto['cantidad_requerida'] - $producto['cantidad_validada']);
        $producto['cantidad_pendiente'] = $pendiente;

        if ($pendiente > 0) {
            $faltantes[] = $producto;
        } else {
            $completos[] = $producto;
        }
    }

    return [
        'status' => 'success',
        'valida' => count($faltantes) === 0 && count($extras) === 0,
        'esperados' => array_values($esperados),
        'completos' => $completos,
        'faltantes' => $faltantes,
        'extras' => $extras,
    ];
}

function preparar_respuesta_orden(AdministradorOrdenes $admin, array $ordenInfo): array {
    $tipo = $ordenInfo['tipo'];
    $orden = $ordenInfo['orden'];
    $idOrden = (int)($orden['id'] ?? 0);
    $detalles = detalles_orden($admin, $tipo, $idOrden);

    return [
        'status' => 'success',
        'tipo_orden' => $tipo,
        'orden' => $orden,
        'detalles' => array_values(agrupar_esperados($detalles)),
        'puede_finalizar' => $tipo === 'entrada'
            ? (($orden['estatus'] ?? '') === 'AUTORIZADA' && !$admin->ordenCompraTieneLotes($idOrden))
            : (($orden['estatus'] ?? '') !== 'CONFIRMADA'),
    ];
}

autorizar_api($config);

$payload = leer_payload();
$accion = $payload['accion'] ?? '';
$admin = new AdministradorOrdenes();

try {
    switch ($accion) {
        case 'health':
            responder([
                'status' => 'success',
                'message' => 'API movil disponible',
                'base_url' => 'https://almacen.grupocroram.com/api/mobileOrdenes.php',
            ]);
            break;

        case 'buscarOrden':
            $ordenInfo = obtener_orden(
                $admin,
                normalizar_tipo($payload['tipo_orden'] ?? ''),
                (string)($payload['codigo'] ?? $payload['id_orden'] ?? '')
            );

            if (!$ordenInfo) {
                responder([
                    'status' => 'error',
                    'message' => 'Orden no encontrada',
                ], 404);
            }

            responder(preparar_respuesta_orden($admin, $ordenInfo));
            break;

        case 'validarEscaneos':
            $tipo = normalizar_tipo($payload['tipo_orden'] ?? '');
            $idOrden = (int)($payload['id_orden'] ?? 0);
            $escaneos = $payload['escaneos'] ?? [];

            if ($tipo === '' || $idOrden <= 0 || !is_array($escaneos)) {
                responder([
                    'status' => 'error',
                    'message' => 'tipo_orden, id_orden y escaneos son obligatorios',
                ], 400);
            }

            responder(validar_escaneos(detalles_orden($admin, $tipo, $idOrden), $escaneos));
            break;

        case 'finalizarOrden':
            $tipo = normalizar_tipo($payload['tipo_orden'] ?? '');
            $idOrden = (int)($payload['id_orden'] ?? 0);
            $escaneos = $payload['escaneos'] ?? [];
            $preciosReales = is_array($payload['precios_reales'] ?? null) ? $payload['precios_reales'] : [];

            if ($tipo === '' || $idOrden <= 0 || !is_array($escaneos)) {
                responder([
                    'status' => 'error',
                    'message' => 'tipo_orden, id_orden y escaneos son obligatorios',
                ], 400);
            }

            $detalles = detalles_orden($admin, $tipo, $idOrden);
            $validacion = validar_escaneos($detalles, $escaneos);
            if (!$validacion['valida']) {
                responder([
                    'status' => 'error',
                    'message' => 'La orden no esta completamente validada',
                    'validacion' => $validacion,
                ], 422);
            }

            $admin->iniciarTransaccion();

            if ($tipo === 'entrada') {
                $ordenActual = json_decode($admin->bloquearOrdenCompra($idOrden), true)[0] ?? null;
                if (!$ordenActual || ($ordenActual['estatus'] ?? '') === 'RECIBIDA') {
                    throw new Exception(json_encode([
                        'status' => 'error',
                        'message' => 'La orden de entrada no existe o ya fue recibida',
                    ]));
                }

                if (($ordenActual['estatus'] ?? '') !== 'AUTORIZADA') {
                    throw new Exception(json_encode([
                        'status' => 'error',
                        'message' => 'La orden debe estar autorizada antes de ingresar las partes',
                    ]));
                }

                if ($admin->ordenCompraTieneLotes($idOrden)) {
                    throw new Exception(json_encode([
                        'status' => 'error',
                        'message' => 'La orden ya tiene entradas registradas en inventario',
                    ]));
                }

                foreach ($validacion['esperados'] as $producto) {
                    $idProducto = (int)$producto['id_producto'];
                    $cantidad = (float)$producto['cantidad_requerida'];
                    $precioReal = isset($preciosReales[$idProducto])
                        ? (float)$preciosReales[$idProducto]
                        : (float)$producto['precio_unitario'];

                    $admin->actualizarDetalleOrdenCompra($idOrden, $idProducto, $precioReal);
                    $admin->registrarEntradaInventario($idProducto, $cantidad);
                    $admin->registrarLoteInventario($idProducto, $idOrden, $cantidad, $precioReal, $ordenActual['fecha_orden'] ?? date('Y-m-d'));
                }

                $admin->actualizarEstatusOrdenCompra($idOrden, 'RECIBIDA');
                $admin->confirmarTransaccion();

                responder([
                    'status' => 'success',
                    'message' => 'Orden de entrada ingresada correctamente',
                    'tipo_orden' => 'entrada',
                    'id_orden' => $idOrden,
                ]);
            }

            if ($tipo === 'salida') {
                $ordenActual = json_decode($admin->bloquearOrdenSalida($idOrden), true)[0] ?? null;
                if (!$ordenActual || ($ordenActual['estatus'] ?? '') === 'CONFIRMADA') {
                    throw new Exception(json_encode([
                        'status' => 'error',
                        'message' => 'La orden de salida no existe o ya fue confirmada',
                    ]));
                }

                foreach ($validacion['esperados'] as $producto) {
                    $idProducto = (int)$producto['id_producto'];
                    $cantidad = (float)$producto['cantidad_requerida'];
                    $costoPeps = $admin->consumirInventarioPeps($idProducto, $cantidad);
                    $admin->actualizarCostoDetalleSalida($idOrden, $idProducto, $costoPeps);
                    $admin->registrarSalidaInventario($idProducto, $cantidad);
                }

                $admin->actualizarEstatusOrdenSalida($idOrden, 'CONFIRMADA');
                $admin->confirmarTransaccion();

                responder([
                    'status' => 'success',
                    'message' => 'Orden de salida aprobada correctamente',
                    'tipo_orden' => 'salida',
                    'id_orden' => $idOrden,
                ]);
            }
            break;

        default:
            responder([
                'status' => 'error',
                'message' => 'Accion no valida',
            ], 400);
    }
} catch (Exception $e) {
    try {
        $admin->revertirTransaccion();
    } catch (Throwable $ignored) {
    }

    $payloadError = json_decode($e->getMessage(), true);
    responder($payloadError ?: [
        'status' => 'error',
        'message' => $e->getMessage(),
    ], 500);
}
