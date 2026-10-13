<?php
require_once __DIR__ . '/../auth.php';
requerir_autenticacion_json();

header('Content-Type: application/json; charset=utf-8');

include_once 'adminOrdenes.php';
require_once __DIR__ . '/recepcionCompra.php';

$accion = $_POST['accion'] ?? '';

$admin = new AdministradorOrdenes();

function entregar_notificaciones_pendientes(): void {
    try {
        require_once __DIR__ . '/adminNotificaciones.php';
        (new AdministradorNotificaciones())->procesarPendientes(25);
    } catch (Throwable $error) {
        error_log('No fue posible entregar las notificaciones push: ' . $error->getMessage());
    }
}

$permisosPorAccion = [
    'altaOrdenCompra' => 'ordenes_compra_crear',
    'altaOrdenSalida' => 'ordenes_salida_crear',
    'guardarOrdenEntrada' => 'ordenes_compra_recibir_sin_qr',
    'autorizarOrdenCompra' => 'ordenes_compra_autorizar',
    'autorizarRecepcionOrdenCompra' => 'ordenes_compra_autorizar',
    'aprovarSalida' => 'ordenes_salida_confirmar_sin_qr',
];

if (isset($permisosPorAccion[$accion])) {
    requerir_permiso_json($permisosPorAccion[$accion]);
}

function validar_idempotencia(string $accion): void {
    global $tokenReservado;
    $token = trim((string)($_POST['request_token'] ?? ''));
    if ($token === '') {
        return;
    }

    $_SESSION['orden_tokens_procesados'] = $_SESSION['orden_tokens_procesados'] ?? [];
    $clave = $accion . ':' . $token;

    if (!empty($_SESSION['orden_tokens_procesados'][$clave])) {
        throw new Exception(json_encode([
            'status' => 'error',
            'message' => 'Esta solicitud ya fue procesada. Actualiza la pantalla antes de intentar de nuevo.',
        ]));
    }

    $_SESSION['orden_tokens_procesados'][$clave] = time();
    $tokenReservado = true;
    if (count($_SESSION['orden_tokens_procesados']) > 50) {
        $_SESSION['orden_tokens_procesados'] = array_slice($_SESSION['orden_tokens_procesados'], -50, null, true);
    }
}

try {

    switch ($accion) {

        case 'altaOrdenCompra':
            $folio = 'OC-' . date('YmdHis');
            $id_proveedor = (int)($_POST['proveedor'] ?? 0);
            $fecha_orden = date('Y-m-d');
            $estatus = 'PENDIENTE';
            $id_usuario = usuario_id_actual();
            $nota = $_POST['nota'] ?? '';
            $orden = $_POST['orden'] ?? [];
            $ordenDecode = json_decode($orden, true) ?: [];

            if (count($ordenDecode) === 0) {
                throw new Exception(json_encode([
                    'status' => 'error',
                    'message' => 'Debes agregar al menos un producto',
                ]));
            }

            if ($id_proveedor > 0 && !$admin->proveedorExiste($id_proveedor)) {
                throw new Exception(json_encode([
                    'status' => 'error',
                    'message' => 'El proveedor seleccionado no existe',
                ]));
            }

            validar_idempotencia('altaOrdenCompra');
            $admin->iniciarTransaccion();
            $admin->agregarOrdenCompra($folio, $id_proveedor, $fecha_orden, $estatus, $id_usuario, $nota);
            $ultimo_id = $admin->dameUltimoIdOrdenCompra();

            foreach ($ordenDecode as $item) {
                $admin->agregarDetalleOrden(
                    $ultimo_id,
                    $item['id'] ?? 0,
                    $item['cantidad'] ?? 0,
                    $item['precio'] ?? 0
                );
            }

            $admin->registrarNotificacionesOrdenNueva('ORDEN_COMPRA_NUEVA', $ultimo_id, $folio);

            $admin->confirmarTransaccion();
            entregar_notificaciones_pendientes();

            echo json_encode([
                'status' => 'success',
                'message' => 'Orden de compra agregada correctamente'
            ]);
            break;

        case 'altaOrdenSalida':
            $salida = $_POST['orden'] ?? [];
            $salidaDecode = json_decode($salida, true) ?: [];

            $folio = 'OS-' . date('YmdHis');
            $fecha_orden = date('Y-m-d');
            $estatus = 'CAPTURADA';
            $id_usuario = usuario_id_actual();
            $nota = $_POST['nota'] ?? '';
            $id_area = $_POST['id_area'] ?? 0;

            if ((int)$id_area <= 0 || trim((string)$nota) === '' || count($salidaDecode) === 0) {
                throw new Exception(json_encode([
                    'status' => 'error',
                    'message' => 'Area, observaciones y productos son obligatorios para registrar la salida',
                ]));
            }

            validar_idempotencia('altaOrdenSalida');
            $admin->iniciarTransaccion();
            $admin->agregarOrdenSalida($folio, $fecha_orden, 'CONSUMO_INTERNO', $estatus, $id_usuario, $nota, $id_area);
            $ultimo_id = $admin->dameUltimoIdOrdenSalida();

            foreach ($salidaDecode as $item) {
                $admin->agregarDetalleOrdenSalida(
                    $ultimo_id,
                    $item['id'] ?? 0,
                    $item['cantidad'] ?? 0,
                    $item['precio'] ?? 0
                );
            }

            $admin->registrarNotificacionesOrdenNueva('ORDEN_SALIDA_NUEVA', $ultimo_id, $folio);

            $admin->confirmarTransaccion();
            entregar_notificaciones_pendientes();

            echo json_encode([
                'status' => 'success',
                'message' => 'Orden de salida agregada correctamente',
                'redirect' => ruta_inicio_usuario(),
            ]);
            break;

        case 'guardarOrdenEntrada':
            validar_idempotencia('guardarOrdenEntrada');
            $idOrden = $_POST['id_orden'] ?? 0;
            $productos = json_decode($_POST['productos'] ?? '[]', true);
            if (!is_array($productos)) {
                throw new InvalidArgumentException('Las partidas de recepción no son válidas.');
            }

            $admin->iniciarTransaccion();
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
                    'message' => 'La orden ya tiene entradas registradas en inventario. No se volveran a sumar unidades.',
                ]));
            }

            $detallesOrden = json_decode($admin->listarDetallesOrden($idOrden), true) ?: [];
            $partidas = validarRecepcionCompra($detallesOrden, $productos);
            $requiereAutorizacionPrecio = false;
            foreach ($partidas as $partida) {
                if ($partida['cantidad_recibida'] > 0 && $partida['precio_real'] > $partida['precio_autorizado'] + 0.0001) {
                    $requiereAutorizacionPrecio = true;
                }
                $admin->guardarPartidaRecepcion($idOrden, $partida);
            }
            $admin->agregarNotaRecepcion($idOrden, $_POST['nota'] ?? '');
            $idUsuarioActual = usuario_id_actual();
            $pendiente = $requiereAutorizacionPrecio && !usuario_tiene_permiso('ordenes_compra_autorizar');
            if ($pendiente) {
                $admin->marcarRecepcionPendienteAutorizacion($idOrden, $idUsuarioActual);
            } else {
                foreach ($partidas as $partida) {
                    $admin->registrarLoteInventario($partida['id_producto'], $idOrden,
                        $partida['cantidad_recibida'], $partida['precio_real'], date('Y-m-d'));
                }
                // El trigger suma las cantidades efectivamente recibidas, una sola vez.
                $admin->marcarOrdenRecibida($idOrden, $idUsuarioActual,
                    $requiereAutorizacionPrecio ? $idUsuarioActual : 0);
            }
            $admin->confirmarTransaccion();

            echo json_encode([
                'status' => 'success',
                'message' => $pendiente
                    ? 'Recepción pendiente de autorización por incremento de precio. El inventario aún no se ha modificado.'
                    : 'Orden ingresada con las cantidades recibidas. La diferencia no recibida quedó registrada.'
            ]);
            break;

        case 'autorizarOrdenCompra':
            validar_idempotencia('autorizarOrdenCompra');
            $idOrden = $_POST['id'] ?? 0;

            $admin->iniciarTransaccion();
            $ordenActual = json_decode($admin->bloquearOrdenCompra($idOrden), true)[0] ?? null;

            if (!$ordenActual || ($ordenActual['estatus'] ?? '') !== 'PENDIENTE') {
                throw new Exception(json_encode([
                    'status' => 'error',
                    'message' => 'La orden no existe o ya no se encuentra pendiente',
                ]));
            }

            $admin->autorizarOrdenCompra($idOrden, usuario_id_actual());
            $admin->confirmarTransaccion();

            echo json_encode([
                'status' => 'success',
                'message' => 'Orden de compra autorizada correctamente'
            ]);
            break;

        case 'autorizarRecepcionOrdenCompra':
            validar_idempotencia('autorizarRecepcionOrdenCompra');
            $idOrden = $_POST['id'] ?? 0;

            $admin->iniciarTransaccion();
            $ordenActual = json_decode($admin->bloquearOrdenCompra($idOrden), true)[0] ?? null;

            if (!$ordenActual || ($ordenActual['estatus'] ?? '') !== 'PENDIENTE_AUTORIZACION_RECEPCION') {
                throw new Exception(json_encode([
                    'status' => 'error',
                    'message' => 'La orden no tiene una recepción pendiente de autorización.',
                ], JSON_UNESCAPED_UNICODE));
            }

            if ($admin->ordenCompraTieneLotes($idOrden)) {
                throw new Exception(json_encode([
                    'status' => 'error',
                    'message' => 'La orden ya tiene lotes registrados y no puede volver a recibirse.',
                ], JSON_UNESCAPED_UNICODE));
            }

            $detallesOrden = json_decode($admin->listarDetallesOrden($idOrden), true) ?: [];
            foreach ($detallesOrden as $detalle) {
                $idProducto = (int)($detalle['id_producto'] ?? 0);
                $cantidad = (float)($detalle['cantidad'] ?? 0);
                $precioRecepcion = (float)($detalle['precio_recepcion'] ?? $detalle['precio_autorizado'] ?? $detalle['precio_unitario'] ?? 0);

                $admin->guardarPartidaRecepcion($idOrden, [
                    'id_detalle' => (int)$detalle['id'],
                    'cantidad_solicitada' => (float)($detalle['cantidad_solicitada'] ?? $cantidad),
                    'cantidad_recibida' => $cantidad,
                    'precio_real' => $precioRecepcion,
                ]);
                $admin->registrarLoteInventario($idProducto, $idOrden, $cantidad, $precioRecepcion, date('Y-m-d'));
            }

            $idUsuarioRecepcion = (int)($ordenActual['id_usuario_recepcion'] ?? 0);
            if ($idUsuarioRecepcion <= 0) {
                $idUsuarioRecepcion = usuario_id_actual();
            }
            $admin->marcarOrdenRecibida($idOrden, $idUsuarioRecepcion, usuario_id_actual());
            $admin->confirmarTransaccion();

            echo json_encode([
                'status' => 'success',
                'message' => 'Recepción autorizada e inventario actualizado correctamente.',
            ], JSON_UNESCAPED_UNICODE);
            break;

        case 'aprovarSalida':
            validar_idempotencia('aprovarSalida');
            $idOrden = $_POST['id'] ?? 0;
            $admin->iniciarTransaccion();
            $ordenActual = json_decode($admin->bloquearOrdenSalida($idOrden), true)[0] ?? null;
            if (!$ordenActual || in_array(($ordenActual['estatus'] ?? ''), ['CONFIRMADA', 'CANCELADA'], true)) {
                throw new Exception(json_encode([
                    'status' => 'error',
                    'message' => 'La orden de salida no existe, ya fue confirmada o está cancelada',
                ]));
            }
            $detalles = json_decode($admin->listarDetallesOrdenSalida($idOrden), true) ?: [];

            foreach ($detalles as $detalle) {
                $costoPeps = $admin->consumirInventarioPeps($detalle['id_producto'] ?? 0, $detalle['cantidad'] ?? 0);
                $admin->actualizarCostoDetalleSalida($idOrden, $detalle['id_producto'] ?? 0, $costoPeps);
            }

            // El trigger trg_os_confirmada_salida descuenta inventario al confirmar.
            $admin->actualizarEstatusOrdenSalida($idOrden, 'CONFIRMADA');
            $admin->confirmarTransaccion();

            echo json_encode([
                'status' => 'success',
                'message' => 'Orden de salida aprobada correctamente'
            ]);
            break;

        default:
            echo json_encode([
                'status' => 'error',
                'message' => 'Acción no válida'
            ]);
            break;
    }
} catch (Exception $e) {
    $admin->revertirTransaccion();
    $token = trim((string)($_POST['request_token'] ?? ''));
    if ($token !== '' && !empty($tokenReservado)) {
        unset($_SESSION['orden_tokens_procesados'][$accion . ':' . $token]);
    }
    $payload = json_decode($e->getMessage(), true);
    echo json_encode($payload ?: [
        'status' => 'error',
        'message' => $e->getMessage(),
    ]);
}
