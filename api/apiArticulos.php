<?php
require_once __DIR__ . '/../auth.php';
requerir_autenticacion_json();

header('Content-Type: application/json; charset=utf-8');

include_once 'adminArticulos.php';

$accion = $_POST['accion'] ?? '';

$admin = new AdministradorArticulos();

try {

    switch ($accion) {

        /* =========================
           ARTICULOS
        ========================= */

        case 'altaArticulo':

            $nombre = $_POST['nombre'] ?? '';
            $id_familia = $_POST['id_familia'] ?? 0;
            $inventario_inicial = $_POST['inventario_inicial'] ?? 0;
            $costo_reposicon = $_POST['costo_reposicion'] ?? 0;

            if (trim($nombre) === '' || (int)$id_familia <= 0) {
                echo json_encode([
                    'status' => 'error',
                    'message' => 'Nombre y familia son obligatorios'
                ]);
                exit;
            }

            $admin->iniciarTransaccion();

            $admin->agregarArticulo(
                $_POST['sku'] ?? '',
                $nombre,
                $id_familia,
                $_POST['id_subfamilia'] ?? null,
                $_POST['unidad_medida'] ?? '',
                $_POST['descripcion'] ?? '',
                $_POST['activo'] ?? 1,
                $costo_reposicon ?? 0
            );

            $admin->agregarInventario(
                $admin->obtenerUltimoArticuloInsertado(),
                $inventario_inicial,
                date('Y-m-d H:i:s')
            );

            $admin->confirmarTransaccion();

            echo json_encode([
                'status' => 'success',
                'message' => 'Articulo agregado correctamente'
            ]);
            break;

        case 'listarArticulos':

            $soloActivos = $_POST['soloActivos'] ?? false;
            echo $admin->listarArticulos((bool)$soloActivos);
            break;

        case 'listarArticulosPaginados':

            echo $admin->listarArticulosPaginados([
                'pagina' => $_POST['pagina'] ?? 1,
                'por_pagina' => $_POST['porPagina'] ?? 10,
                'texto' => $_POST['texto'] ?? '',
                'id_familia' => $_POST['id_familia'] ?? 0,
                'id_subfamilia' => $_POST['id_subfamilia'] ?? 0,
                'solo_activos' => $_POST['soloActivos'] ?? 1,
                'solo_con_stock' => $_POST['soloConStock'] ?? 0,
            ]);
            break;

        case 'obtenerArticulo':

            echo $admin->obtenerArticulo($_POST['id'] ?? 0);
            break;

        case 'actualizarArticulo':

            $admin->actualizarArticulo(
                $_POST['id'] ?? 0,
                $_POST['sku'] ?? '',
                $_POST['nombre'] ?? '',
                $_POST['id_familia'] ?? 0,
                $_POST['id_subfamilia'] ?? null,
                $_POST['unidad_medida'] ?? '',
                $_POST['descripcion'] ?? '',
                $_POST['activo'] ?? 1
            );

            echo json_encode([
                'status' => 'success',
                'message' => 'Articulo actualizado correctamente'
            ]);
            break;

        case 'eliminarArticulo':

            echo $admin->eliminarArticulo($_POST['id'] ?? 0);
            break;

        default:
            echo json_encode([
                'status' => 'error',
                'message' => 'Accion no valida'
            ]);
            break;
    }

} catch (Exception $e) {
    $admin->revertirTransaccion();
    echo $e->getMessage();
}

