<?php
require_once __DIR__ . '/auth.php';
requerir_autenticacion();
include_once 'api/adminUsuarios.php';
$adminUsuarios = new AdministradorUsuarios();
$esEdicion = isset($_GET['id']);
$usuarioEdit = null;
if ($esEdicion) {
    $resultado = json_decode($adminUsuarios->obtenerUsuario($_GET['id'] ?? 0), true) ?: [];
    $usuarioEdit = $resultado[0] ?? null;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8" />
    <title>Almacen Croram - Usuario</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta content="Sistema de inventario de CRORAM." name="description" />
    <meta content="HoppingJet Studio." name="author" />
    <link rel="shortcut icon" href="favicon.png">
    <link href="assets/css/vendor.min.css" rel="stylesheet" type="text/css" />
    <link href="assets/css/app.min.css" rel="stylesheet" type="text/css" id="app-style" />
    <link href="assets/css/icons.min.css" rel="stylesheet" type="text/css" />
    <script src="assets/js/config.js"></script>
</head>
<body>
<div class="wrapper">
    <?php include_once 'templates/barra.php'; ?>
    <?php include_once 'templates/headder.php'; ?>
    <div class="page-content">
        <div class="page-title-head d-flex align-items-center gap-2">
            <div class="flex-grow-1"><h4 class="fs-18 fw-bold mb-0"><?= $esEdicion ? 'Editar usuario' : 'Nuevo usuario' ?></h4></div>
            <div class="text-end"><ol class="breadcrumb m-0 py-0 fs-13"><li class="breadcrumb-item"><a href="usuarios.php">Usuarios</a></li><li class="breadcrumb-item active"><?= $esEdicion ? 'Editar' : 'Nuevo' ?></li></ol></div>
        </div>
        <div class="page-container">
            <div class="card">
                <div class="card-body">
                    <p class="text-muted"><?= $esEdicion ? 'Actualiza la informacion del usuario seleccionado.' : 'Da de alta un nuevo usuario para acceder al sistema.' ?></p>
                    <form id="formUsuario" autocomplete="off">
                        <div class="mb-3"><label class="form-label" for="nombre">Nombre</label><input type="text" class="form-control" id="nombre" name="nombre" value="<?= htmlspecialchars($usuarioEdit['nombre'] ?? '') ?>" required></div>
                        <div class="mb-3"><label class="form-label" for="usuario">Usuario</label><input type="text" class="form-control" id="usuario" name="usuario" value="<?= htmlspecialchars($usuarioEdit['usuario'] ?? '') ?>" required></div>
                        <div class="mb-3"><label class="form-label" for="password">Password</label><input type="text" class="form-control" id="password" name="password" value="<?= htmlspecialchars($usuarioEdit['password'] ?? '') ?>" required></div>
                        <div class="mb-3"><label class="form-label" for="email">Email</label><input type="email" class="form-control" id="email" name="email" value="<?= htmlspecialchars($usuarioEdit['email'] ?? '') ?>"></div>
                        <div class="mb-3"><label class="form-label" for="rol">Rol</label><input type="text" class="form-control" id="rol" name="rol" value="<?= htmlspecialchars($usuarioEdit['rol'] ?? '') ?>"></div>
                        <div class="mb-4"><label class="form-label" for="activo">Estado</label><select class="form-select" id="activo" name="activo"><option value="1" <?= ((int)($usuarioEdit['activo'] ?? 1) === 1) ? 'selected' : '' ?>>Activo</option><option value="0" <?= ((int)($usuarioEdit['activo'] ?? 1) === 0) ? 'selected' : '' ?>>Inactivo</option></select></div>
                        <div class="d-flex justify-content-between">
                            <a href="usuarios.php" class="btn btn-secondary">Cancelar</a>
                            <button type="submit" class="btn btn-primary"><?= $esEdicion ? 'Guardar cambios' : 'Guardar usuario' ?></button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <?php include 'templates/footer.php'; ?>
    </div>
</div>
<?php include_once 'templates/theme.php'; ?>
<script src="assets/js/vendor.min.js"></script>
<script src="assets/js/app.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
const ES_EDICION = <?= $esEdicion ? 'true' : 'false' ?>;
const USUARIO_ID = <?= (int)($_GET['id'] ?? 0) ?>;

document.getElementById('formUsuario').addEventListener('submit', async (event) => {
    event.preventDefault();
    const formData = new FormData(event.target);
    formData.append('accion', ES_EDICION ? 'actualizarUsuario' : 'altaUsuario');
    if (ES_EDICION) {
        formData.append('id', USUARIO_ID);
    }

    const response = await fetch('api/apiUsuarios.php', { method: 'POST', body: formData });
    const data = await response.json();
    if (data.status === 'success') {
        await Swal.fire({ icon: 'success', title: 'Guardado', text: data.message });
        window.location.href = 'usuarios.php';
        return;
    }

    Swal.fire({ icon: 'error', title: 'Error', text: data.message || 'No fue posible guardar el usuario' });
});
</script>
</body>
</html>
