<?php
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../auth.php';
include_once 'adminUsuarios.php';

$accion = $_POST['accion'] ?? '';
$admin = new AdministradorUsuarios();

try {
    if ($accion !== 'login') {
        requerir_autenticacion_json();
    }

    switch ($accion) {
        case 'login':
            $email = $_POST['email'] ?? '';
            $password = $_POST['password'] ?? '';
            $recordar = !empty($_POST['recordar_usuario']);

            if (trim($email) === '' || trim($password) === '') {
                echo json_encode(['status' => 'error', 'message' => 'Correo y password son obligatorios']);
                exit;
            }

            $resultado = json_decode($admin->autenticarPorEmail($email, $password), true) ?: [];
            $usuarioDb = $resultado[0] ?? null;

            if (!$usuarioDb) {
                echo json_encode(['status' => 'error', 'message' => 'Credenciales inválidas']);
                exit;
            }

            iniciar_sesion_usuario($usuarioDb, $recordar);
            echo json_encode(['status' => 'success', 'message' => 'Sesión iniciada correctamente']);
            break;

        case 'logout':
            cerrar_sesion_usuario();
            echo json_encode(['status' => 'success', 'message' => 'Sesión cerrada']);
            break;

        case 'listarUsuarios':
            echo $admin->listarUsuarios((bool)($_POST['soloActivos'] ?? false));
            break;

        case 'obtenerUsuario':
            echo $admin->obtenerUsuario($_POST['id'] ?? 0);
            break;

        case 'altaUsuario':
            $usuario = $_POST['usuario'] ?? '';
            $email = $_POST['email'] ?? '';

            if (trim($_POST['nombre'] ?? '') === '' || trim($usuario) === '' || trim($_POST['password'] ?? '') === '') {
                echo json_encode(['status' => 'error', 'message' => 'Nombre, usuario y password son obligatorios']);
                exit;
            }

            if ($admin->existeUsuario($usuario)) {
                echo json_encode(['status' => 'error', 'message' => 'El usuario ya está registrado']);
                exit;
            }

            if ($admin->existeEmail($email)) {
                echo json_encode(['status' => 'error', 'message' => 'El correo ya está registrado']);
                exit;
            }

            $admin->crearUsuario(
                $_POST['nombre'] ?? '',
                $usuario,
                $_POST['password'] ?? '',
                $email,
                $_POST['rol'] ?? '',
                $_POST['activo'] ?? 1
            );

            echo json_encode(['status' => 'success', 'message' => 'Usuario agregado correctamente']);
            break;

        case 'actualizarUsuario':
            $id = $_POST['id'] ?? 0;
            $usuario = $_POST['usuario'] ?? '';
            $email = $_POST['email'] ?? '';

            if (trim($_POST['nombre'] ?? '') === '' || trim($usuario) === '' || trim($_POST['password'] ?? '') === '') {
                echo json_encode(['status' => 'error', 'message' => 'Nombre, usuario y password son obligatorios']);
                exit;
            }

            if ($admin->existeUsuario($usuario, $id)) {
                echo json_encode(['status' => 'error', 'message' => 'El usuario ya está registrado']);
                exit;
            }

            if ($admin->existeEmail($email, $id)) {
                echo json_encode(['status' => 'error', 'message' => 'El correo ya está registrado']);
                exit;
            }

            $admin->actualizarUsuario(
                $id,
                $_POST['nombre'] ?? '',
                $usuario,
                $_POST['password'] ?? '',
                $email,
                $_POST['rol'] ?? '',
                $_POST['activo'] ?? 1
            );

            echo json_encode(['status' => 'success', 'message' => 'Usuario actualizado correctamente']);
            break;

        case 'eliminarUsuario':
            $admin->eliminarUsuario($_POST['id'] ?? 0);
            echo json_encode(['status' => 'success', 'message' => 'Usuario desactivado correctamente']);
            break;

        case 'activarUsuario':
            $admin->activarUsuario($_POST['id'] ?? 0);
            echo json_encode(['status' => 'success', 'message' => 'Usuario activado correctamente']);
            break;

        default:
            echo json_encode(['status' => 'error', 'message' => 'Acción no válida']);
            break;
    }
} catch (Exception $e) {
    echo $e->getMessage();
}
