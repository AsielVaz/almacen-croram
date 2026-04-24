<?php
require_once __DIR__ . '/../auth.php';
requerir_autenticacion_json();

header('Content-Type: application/json; charset=utf-8');

include_once 'adminCatalogos.php';

$accion = $_POST['accion'] ?? '';

$admin = new AdministradorCatalogos();

try {

    switch ($accion) {

        /* =========================
           FAMILIAS
        ========================= */

        case 'altaFamilia':

            $nombre = $_POST['nombre'] ?? '';
            $descripcion = $_POST['descripcion'] ?? '';
            $activo = $_POST['activo'] ?? 1;

            if (trim($nombre) === '') {
                echo json_encode([
                    'status' => 'error',
                    'message' => 'El nombre de la familia es obligatorio'
                ]);
                exit;
            }

            echo $admin->agregarFamilia($nombre, $descripcion, $activo);
            break;

        case 'listarFamilias':

            $soloActivas = $_POST['soloActivas'] ?? false;
            echo $admin->listarFamilias((bool)$soloActivas);
            break;

        case 'obtenerFamilia':

            $id = $_POST['id'] ?? 0;
            echo $admin->obtenerFamilia($id);
            break;

        case 'actualizarFamilia':

            $admin->actualizarFamilia(
                $_POST['id'] ?? 0,
                $_POST['nombre'] ?? '',
                $_POST['descripcion'] ?? '',
                $_POST['activo'] ?? 1
            );
            echo json_encode([
                'status' => 'success',
                'message' => 'Familia actualizada correctamente'
            ]);
            break;

        case 'eliminarFamilia':
            $admin->eliminarFamilia($_POST['id'] ?? 0);
            echo json_encode([
                'status' => 'success',
                'message' => 'Familia eliminada correctamente'
            ]);
            break;
        

        /* =========================
           SUBFAMILIAS
        ========================= */

        case 'altaSubFamilia':

            $id_familia = $_POST['id_familia'] ?? 0;
            $nombre = $_POST['nombre'] ?? '';
            $descripcion = $_POST['descripcion'] ?? '';
            $activo = $_POST['activo'] ?? 1;

            if ((int)$id_familia <= 0 || trim($nombre) === '') {
                echo json_encode([
                    'status' => 'error',
                    'message' => 'Familia y nombre son obligatorios'
                ]);
                exit;
            }

            $admin->agregarSubfamilia($id_familia, $nombre, $descripcion, $activo);
            $mensaje = json_encode([
                'status' => 'success',
                'message' => 'Subfamilia agregada correctamente'
            ]);
            echo $mensaje;
            break;

        case 'listarSubFamilias':

            $id_familia = $_POST['id_familia'] ?? null;
            $soloActivas = $_POST['soloActivas'] ?? false;

            echo $admin->listarSubfamilias($id_familia, (bool)$soloActivas);
            break;

        case 'obtenerSubFamilia':

            echo $admin->obtenerSubfamilia($_POST['id'] ?? 0);
            break;

        case 'actualizarSubFamilia':

            $admin->actualizarSubfamilia(
                $_POST['id'] ?? 0,
                $_POST['id_familia'] ?? 0,
                $_POST['nombre'] ?? '',
                $_POST['descripcion'] ?? '',
                $_POST['activo'] ?? 1
            );
            echo json_encode([
                'status' => 'success',
                'message' => 'Subfamilia actualizada correctamente'
            ]);
            break;

        case 'eliminarSubFamilia':
            $admin->eliminarSubfamilia($_POST['id'] ?? 0);
            echo json_encode([
                'status' => 'success',
                'message' => 'Sub familia eliminada correctamente'
            ]);
            break;

        /* =========================
           DEFAULT
        ========================= */

        default:
            echo json_encode([
                'status' => 'error',
                'message' => 'Acción no válida'
            ]);
            break;
    }

} catch (Exception $e) {
    echo $e->getMessage();
}

