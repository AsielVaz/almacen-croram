<?php
require_once __DIR__ . '/../auth.php';
requerir_autenticacion_json();

header('Content-Type: application/json; charset=utf-8');
include_once 'adminProveedores.php';

$accion = $_POST['accion'] ?? '';

$admin = new AdministradorProveedores();

try {

    switch ($accion) {

        /* =========================
           PROVEEDORES
        ========================= */

        case 'altaProveedor':

            $nombre = $_POST['nombre'] ?? '';

            if (trim($nombre) === '') {
                echo json_encode([
                    'status' => 'error',
                    'message' => 'El nombre del proveedor es obligatorio'
                ]);
                exit;
            }
            
            if ($admin->existeRfc($_POST['rfc'] ?? '')) {
                echo json_encode([
                    'status' => 'error',
                    'message' => 'El RFC ya está registrado'
                ]);
                exit;
            }

             $admin->agregarProveedor(
                $nombre,
                $_POST['contacto'] ?? '',
                $_POST['rfc'] ?? '',
                $_POST['domicilio_completo'] ?? '',
                $_POST['telefono_contacto'] ?? '',
                $_POST['movil_contacto'] ?? '',
                $_POST['mail'] ?? '',
                $_POST['credito'] ?? 0,
                $_POST['plazo_credito'] ?? 0,
                $_POST['activo'] ?? 1
            );
            $mensaje = json_encode([
                'status' => 'success',
                'message' => 'Proveedor agregado correctamente'
            ]);
            echo $mensaje;
            break;

        case 'listarProveedores':

            $soloActivos = $_POST['soloActivos'] ?? false;
            echo $admin->listarProveedores((bool)$soloActivos);
            break;

        case 'obtenerProveedor':

            echo $admin->obtenerProveedor($_POST['id'] ?? 0);
            break;

        case 'actualizarProveedor':

            if ($admin->existeRfc($_POST['rfc'] ?? '', $_POST['id'] ?? 0)) {
                echo json_encode([
                    'status' => 'error',
                    'message' => 'El RFC ya está registrado'
                ]);
                exit;
            }

            $admin->actualizarProveedor(
                $_POST['id'] ?? 0,
                $_POST['nombre'] ?? '',
                $_POST['contacto'] ?? '',
                $_POST['rfc'] ?? '',
                $_POST['domicilio_completo'] ?? '',
                $_POST['telefono_contacto'] ?? '',
                $_POST['movil_contacto'] ?? '',
                $_POST['mail'] ?? '',
                $_POST['credito'] ?? 0,
                $_POST['plazo_credito'] ?? 0,
                $_POST['activo'] ?? 1
            );
            echo json_encode([
                'status' => 'success',
                'message' => 'Proveedor actualizado correctamente'
            ]);
            break;

        case 'eliminarProveedor':

            $admin->eliminarProveedor($_POST['id'] ?? 0);
            echo json_encode([
                'status' => 'success',
                'message' => 'Proveedor eliminado correctamente'
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

