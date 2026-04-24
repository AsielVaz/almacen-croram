<?php
require_once __DIR__ . '/auth.php';
include_once __DIR__ . '/api/adminUsuarios.php';

if (usuario_autenticado()) {
    header('Location: index.php');
    exit;
}

$correoRecordado = $_COOKIE['recordar_usuario'] ?? '';
$errorLogin = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');
    $recordar = !empty($_POST['recordar_usuario']);

    if ($email === '' || $password === '') {
        $errorLogin = 'Correo y password son obligatorios';
    } else {
        $adminUsuarios = new AdministradorUsuarios();
        $resultado = json_decode($adminUsuarios->autenticarPorEmail($email, $password), true) ?: [];
        $usuarioDb = $resultado[0] ?? null;

        if ($usuarioDb) {
            iniciar_sesion_usuario($usuarioDb, $recordar);
            header('Location: index.php');
            exit;
        }

        $errorLogin = 'Credenciales invÃ¡lidas';
        $correoRecordado = $recordar ? $email : '';
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8" />
    <title>AlmacÃ©n Croram - Iniciar sesiÃ³n</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta content="Sistema de inventario de CRORAM." name="description" />
    <meta content="HoppingJet Studio." name="author" />
    <link rel="shortcut icon" href="favicon.png">
    <link href="assets/css/vendor.min.css" rel="stylesheet" type="text/css" />
    <link href="assets/css/app.min.css" rel="stylesheet" type="text/css" />
    <link href="assets/css/icons.min.css" rel="stylesheet" type="text/css" />
    <script src="assets/js/config.js"></script>
    <style>
        body { min-height: 100vh; background: linear-gradient(135deg, #f4f6f8 0%, #dde3e8 100%); display: flex; align-items: center; justify-content: center; }
        .login-card { width: 100%; max-width: 430px; background: #fff; border-radius: 18px; box-shadow: 0 20px 50px rgba(0,0,0,.12); padding: 2rem; }
        .brand-box { text-align: center; margin-bottom: 1.5rem; }
        .brand-box img { max-width: 180px; }
        .login-title { font-size: 1.6rem; font-weight: 700; color: #212529; margin-bottom: .35rem; }
        .login-subtitle { color: #6c757d; margin-bottom: 1.5rem; }
        .form-label { font-weight: 600; }
        .btn-login { background: #495057; border-color: #495057; font-weight: 700; padding: .85rem 1rem; }
        .btn-login:hover { background: #343a40; border-color: #343a40; }
    </style>
</head>
<body>
    <div class="login-card">
        <div class="brand-box">
            <img src="assets/images/logo_almacen.png" alt="Logo">
        </div>
        <div class="text-center mb-4">
            <div class="login-title">Iniciar sesiÃ³n</div>
            <div class="login-subtitle">Accede para usar el sistema de almacÃ©n</div>
        </div>
        <form id="formLogin" method="post" autocomplete="off">
            <div class="mb-3">
                <label for="email" class="form-label">Correo</label>
                <input type="email" class="form-control" id="email" name="email" value="<?= htmlspecialchars($correoRecordado) ?>" required>
            </div>
            <div class="mb-3">
                <label for="password" class="form-label">Password</label>
                <input type="password" class="form-control" id="password" name="password" required>
            </div>
            <div class="form-check mb-4">
                <input class="form-check-input" type="checkbox" value="1" id="recordar_usuario" name="recordar_usuario" <?= $correoRecordado !== '' ? 'checked' : '' ?>>
                <label class="form-check-label" for="recordar_usuario">Recordar correo</label>
            </div>
            <button type="submit" class="btn btn-primary btn-login w-100">Entrar</button>
        </form>
    </div>
    <script src="assets/js/vendor.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <?php if ($errorLogin !== ''): ?>
    <script>
        Swal.fire({ icon: 'error', title: 'Acceso denegado', text: <?= json_encode($errorLogin, JSON_UNESCAPED_UNICODE) ?> });
    </script>
    <?php endif; ?>
</body>
</html>
