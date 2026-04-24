<?php
require_once __DIR__ . '/../auth.php';
requerir_autenticacion_json();

header('Content-Type: application/json; charset=utf-8');

include_once 'adminAreas.php';

$accion = $_POST['accion'] ?? '';
$admin = new AdministradorAreas();

try {
    switch ($accion) {
        case 'listarAreas':
            echo $admin->listarAreas();
            break;

        case 'obtenerArea':
            echo $admin->obtenerArea($_POST['id'] ?? 0);
            break;

        case 'altaArea':
            $nombre = trim($_POST['nombre'] ?? '');
            $presupuesto = $_POST['presupuesto'] ?? '';
            $idEncargado = (int)($_POST['id_encargado'] ?? 0);

            if ($nombre === '' || $idEncargado <= 0 || $presupuesto === '') {
                echo json_encode([
                    'status' => 'error',
                    'message' => 'Nombre, presupuesto y encargado son obligatorios'
                ]);
                exit;
            }

            $admin->crearArea($nombre, $presupuesto, $idEncargado);
            echo json_encode([
                'status' => 'success',
                'message' => 'Área agregada correctamente'
            ]);
            break;

        case 'actualizarArea':
            $id = (int)($_POST['id'] ?? 0);
            $nombre = trim($_POST['nombre'] ?? '');
            $presupuesto = $_POST['presupuesto'] ?? '';
            $idEncargado = (int)($_POST['id_encargado'] ?? 0);

            if ($id <= 0 || $nombre === '' || $idEncargado <= 0 || $presupuesto === '') {
                echo json_encode([
                    'status' => 'error',
                    'message' => 'Datos incompletos para actualizar el área'
                ]);
                exit;
            }

            $admin->actualizarArea($id, $nombre, $presupuesto, $idEncargado);
            echo json_encode([
                'status' => 'success',
                'message' => 'Área actualizada correctamente'
            ]);
            break;

        case 'eliminarArea':
            $id = (int)($_POST['id'] ?? 0);
            if ($id <= 0) {
                echo json_encode([
                    'status' => 'error',
                    'message' => 'Área no válida'
                ]);
                exit;
            }

            $admin->eliminarArea($id);
            echo json_encode([
                'status' => 'success',
                'message' => 'Área eliminada correctamente'
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
    echo $e->getMessage();
}
