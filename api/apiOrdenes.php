<?php
require_once __DIR__ . '/../auth.php';
requerir_autenticacion_json();

header('Content-Type: application/json; charset=utf-8');

include_once 'adminOrdenes.php';

$accion = $_POST['accion'] ?? '';

$admin = new AdministradorOrdenes();

try {

    switch ($accion) {

        case 'altaOrdenCompra':
            $folio = 'OC-' . date('YmdHis');
            $id_proveedor = $_POST['proveedor'] ?? 0;
            $fecha_orden = date('Y-m-d');
            $estatus = 'PENDIENTE';
            $id_usuario = $_POST['id_usuario'] ?? 0;
            $orden = $_POST['orden'] ?? [];
            $ordenDecode = json_decode($orden, true) ?: [];

            $admin->iniciarTransaccion();
            $admin->agregarOrdenCompra($folio, $id_proveedor, $fecha_orden, $estatus, $id_usuario);
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
            $estatus = 'BORRADOR';
            $id_usuario = $_POST['id_usuario'] ?? 0;

            $admin->iniciarTransaccion();
            $admin->agregarOrdenSalida($folio, $fecha_orden, 'CONSUMO_INTERNO', $estatus, $id_usuario);
            $ultimo_id = $admin->dameUltimoIdOrdenSalida();

            foreach ($salidaDecode as $item) {
                $admin->agregarDetalleOrdenSalida(
                    $ultimo_id,
                    $item['id'] ?? 0,
                    $item['cantidad'] ?? 0,
                    $item['precio'] ?? 0
                );
            }

            $admin->confirmarTransaccion();

            echo json_encode([
                'status' => 'success',
                'message' => 'Orden de salida agregada correctamente'
            ]);
            break;

        case 'guardarOrdenEntrada':
            $idOrden = $_POST['id_orden'] ?? 0;
            $productos = json_decode($_POST['productos'] ?? '[]', true) ?: [];

            $admin->iniciarTransaccion();

            foreach ($productos as $producto) {
                $idProducto = $producto['id_producto'] ?? 0;
                $cantidad = $producto['cantidad'] ?? 0;
                $precioReal = $producto['precio_real'] ?? 0;

                $admin->actualizarDetalleOrdenCompra($idOrden, $idProducto, $precioReal);
                $admin->registrarEntradaInventario($idProducto, $cantidad);
            }

            $admin->actualizarEstatusOrdenCompra($idOrden, 'RECIBIDA');
            $admin->confirmarTransaccion();

            echo json_encode([
                'status' => 'success',
                'message' => 'Orden de entrada guardada correctamente'
            ]);
            break;

        case 'aprovarSalida':
            $idOrden = $_POST['id'] ?? 0;
            $detalles = json_decode($admin->listarDetallesOrdenSalida($idOrden), true) ?: [];

            $admin->iniciarTransaccion();

            foreach ($detalles as $detalle) {
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
    echo $e->getMessage();
}

