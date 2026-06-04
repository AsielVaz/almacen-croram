<?php
require_once __DIR__ . '/../auth.php';
requerir_autenticacion_json();

header('Content-Type: application/json; charset=utf-8');

include_once 'adminOrdenes.php';

$accion = $_POST['accion'] ?? '';

$admin = new AdministradorOrdenes();

function validar_idempotencia(string $accion): void {
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
    if (count($_SESSION['orden_tokens_procesados']) > 50) {
        $_SESSION['orden_tokens_procesados'] = array_slice($_SESSION['orden_tokens_procesados'], -50, null, true);
    }
}

try {

    switch ($accion) {

        case 'altaOrdenCompra':
            $folio = 'OC-' . date('YmdHis');
            $id_proveedor = $_POST['proveedor'] ?? 0;
            $fecha_orden = date('Y-m-d');
            $estatus = 'PENDIENTE';
            $id_usuario = $_POST['id_usuario'] ?? 0;
            $nota = $_POST['nota'] ?? '';
            $orden = $_POST['orden'] ?? [];
            $ordenDecode = json_decode($orden, true) ?: [];

            if ((int)$id_proveedor <= 0 || count($ordenDecode) === 0) {
                throw new Exception(json_encode([
                    'status' => 'error',
                    'message' => 'Proveedor y productos son obligatorios',
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

            $admin->confirmarTransaccion();

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
            $estatus = 'CONFIRMADA';
            $id_usuario = $_POST['id_usuario'] ?? 0;
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
                $costoPeps = $admin->consumirInventarioPeps($item['id'] ?? 0, $item['cantidad'] ?? 0);
                $admin->actualizarCostoDetalleSalida($ultimo_id, $item['id'] ?? 0, $costoPeps);
                $admin->registrarSalidaInventario($item['id'] ?? 0, $item['cantidad'] ?? 0);
            }

            $admin->confirmarTransaccion();

            echo json_encode([
                'status' => 'success',
                'message' => 'Orden de salida agregada correctamente'
            ]);
            break;

        case 'guardarOrdenEntrada':
            validar_idempotencia('guardarOrdenEntrada');
            $idOrden = $_POST['id_orden'] ?? 0;
            $productos = json_decode($_POST['productos'] ?? '[]', true) ?: [];

            $admin->iniciarTransaccion();
            $ordenActual = json_decode($admin->bloquearOrdenCompra($idOrden), true)[0] ?? null;

            if (!$ordenActual || ($ordenActual['estatus'] ?? '') === 'RECIBIDA') {
                throw new Exception(json_encode([
                    'status' => 'error',
                    'message' => 'La orden de entrada no existe o ya fue recibida',
                ]));
            }

            foreach ($productos as $producto) {
                $idProducto = $producto['id_producto'] ?? 0;
                $cantidad = $producto['cantidad'] ?? 0;
                $precioReal = $producto['precio_real'] ?? 0;

                $admin->actualizarDetalleOrdenCompra($idOrden, $idProducto, $precioReal);
                $admin->registrarEntradaInventario($idProducto, $cantidad);
                $admin->registrarLoteInventario($idProducto, $idOrden, $cantidad, $precioReal, $ordenActual['fecha_orden'] ?? date('Y-m-d'));
            }

            $admin->actualizarEstatusOrdenCompra($idOrden, 'RECIBIDA');
            $admin->confirmarTransaccion();

            echo json_encode([
                'status' => 'success',
                'message' => 'Orden de entrada guardada correctamente'
            ]);
            break;

        case 'aprovarSalida':
            validar_idempotencia('aprovarSalida');
            $idOrden = $_POST['id'] ?? 0;
            $admin->iniciarTransaccion();
            $ordenActual = json_decode($admin->bloquearOrdenSalida($idOrden), true)[0] ?? null;
            if (!$ordenActual || ($ordenActual['estatus'] ?? '') === 'CONFIRMADA') {
                throw new Exception(json_encode([
                    'status' => 'error',
                    'message' => 'La orden de salida no existe o ya fue confirmada',
                ]));
            }
            $detalles = json_decode($admin->listarDetallesOrdenSalida($idOrden), true) ?: [];

            foreach ($detalles as $detalle) {
                $costoPeps = $admin->consumirInventarioPeps($detalle['id_producto'] ?? 0, $detalle['cantidad'] ?? 0);
                $admin->actualizarCostoDetalleSalida($idOrden, $detalle['id_producto'] ?? 0, $costoPeps);
                $admin->registrarSalidaInventario($detalle['id_producto'] ?? 0, $detalle['cantidad'] ?? 0);
            }

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
    $payload = json_decode($e->getMessage(), true);
    echo json_encode($payload ?: [
        'status' => 'error',
        'message' => $e->getMessage(),
    ]);
}
