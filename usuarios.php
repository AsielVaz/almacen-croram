<?php
require_once __DIR__ . '/auth.php';
requerir_autenticacion();
include_once 'api/adminUsuarios.php';
$adminUsuarios = new AdministradorUsuarios();
$usuarios = json_decode($adminUsuarios->listarUsuarios(), true) ?: [];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8" />
    <title>Almacen Croram - Usuarios</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta content="Sistema de inventario de CRORAM." name="description" />
    <meta content="HoppingJet Studio." name="author" />
    <link rel="shortcut icon" href="favicon.png">
    <link href="assets/css/vendor.min.css" rel="stylesheet" type="text/css" />
    <link href="assets/css/app.min.css" rel="stylesheet" type="text/css" id="app-style" />
    <link href="assets/css/icons.min.css" rel="stylesheet" type="text/css" />
    <script src="assets/js/config.js"></script>
    <link href="assets/vendor/datatables.net-bs5/css/dataTables.bootstrap5.min.css" rel="stylesheet" type="text/css" />
    <link href="assets/vendor/datatables.net-responsive-bs5/css/responsive.bootstrap5.min.css" rel="stylesheet" type="text/css" />
    <style>
        .usuario-inactivo td {
            background-color: #f1f3f5 !important;
            color: #8a969c !important;
        }

        .usuario-inactivo .badge-estado {
            background-color: #dee2e6;
            color: #6c757d;
        }
    </style>
</head>
<body>
<div class="wrapper">
    <?php include_once 'templates/barra.php'; ?>
    <?php include_once 'templates/headder.php'; ?>
    <div class="page-content">
        <div class="page-title-head d-flex align-items-center gap-2">
            <div class="flex-grow-1"><h4 class="fs-18 fw-bold mb-0">Administrar usuarios</h4></div>
            <div class="text-end"><ol class="breadcrumb m-0 py-0 fs-13"><li class="breadcrumb-item"><a href="index.php">Inicio</a></li><li class="breadcrumb-item active">Usuarios</li></ol></div>
        </div>
        <div class="page-container">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div>
                            <h4 class="header-title">Usuarios del sistema</h4>
                            <p class="text-muted mb-0">Administra accesos, roles y estado de usuarios.</p>
                        </div>
                        <a href="usuarios-form.php" class="btn btn-primary">Agregar usuario</a>
                    </div>
                    <table id="alternative-page-datatable" class="table table-striped dt-responsive nowrap w-100">
                        <thead>
                            <tr><th>ID</th><th>Nombre</th><th>Usuario</th><th>Email</th><th>Rol</th><th>Estado</th><th>Creado</th><th>Acciones</th></tr>
                        </thead>
                        <tbody>
                            <?php foreach ($usuarios as $usuario): ?>
                                <?php $estaActivo = ((int)($usuario['activo'] ?? 0) === 1); ?>
                                <tr class="<?= $estaActivo ? '' : 'usuario-inactivo' ?>">
                                    <td><?= (int)$usuario['id'] ?></td>
                                    <td><?= htmlspecialchars($usuario['nombre'] ?? '') ?></td>
                                    <td><?= htmlspecialchars($usuario['usuario'] ?? '') ?></td>
                                    <td><?= htmlspecialchars($usuario['email'] ?: 'Sin correo') ?></td>
                                    <td><?= htmlspecialchars($usuario['rol'] ?: 'Sin rol') ?></td>
                                    <td><span class="badge badge-estado <?= $estaActivo ? 'bg-success-subtle text-success' : '' ?>"><?= $estaActivo ? 'Activo' : 'Inactivo' ?></span></td>
                                    <td><?= htmlspecialchars($usuario['created_at'] ?? '') ?></td>
                                    <td>
                                        <?php if ($estaActivo): ?>
                                            <a href="usuarios-form.php?id=<?= (int)$usuario['id'] ?>" class="btn btn-sm btn-primary">Editar</a>
                                            <button type="button" class="btn btn-sm btn-danger" onclick="eliminarUsuario(<?= (int)$usuario['id'] ?>)">Desactivar</button>
                                        <?php else: ?>
                                            <button type="button" class="btn btn-sm btn-success" onclick="activarUsuario(<?= (int)$usuario['id'] ?>)">Activar</button>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
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
<script src="assets/vendor/datatables.net/js/jquery.dataTables.min.js"></script>
<script src="assets/vendor/datatables.net-bs5/js/dataTables.bootstrap5.min.js"></script>
<script src="assets/vendor/datatables.net-responsive/js/dataTables.responsive.min.js"></script>
<script src="assets/vendor/datatables.net-responsive-bs5/js/responsive.bootstrap5.min.js"></script>
<script src="assets/js/components/table-datatable.js"></script>
<script>
function eliminarUsuario(id) {
    Swal.fire({
        title: 'Desactivar usuario',
        text: 'El usuario dejara de poder iniciar sesion.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Si, desactivar',
        cancelButtonText: 'Cancelar'
    }).then(async (result) => {
        if (!result.isConfirmed) return;
        const formData = new FormData();
        formData.append('accion', 'eliminarUsuario');
        formData.append('id', id);
        const response = await fetch('api/apiUsuarios.php', { method: 'POST', body: formData });
        const data = await response.json();
        if (data.status === 'success') {
            location.reload();
            return;
        }
        Swal.fire({ icon: 'error', title: 'Error', text: data.message || 'No fue posible desactivar el usuario' });
    });
}

function activarUsuario(id) {
    Swal.fire({
        title: 'Activar usuario',
        text: 'El usuario podra volver a iniciar sesion.',
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Si, activar',
        cancelButtonText: 'Cancelar'
    }).then(async (result) => {
        if (!result.isConfirmed) return;
        const formData = new FormData();
        formData.append('accion', 'activarUsuario');
        formData.append('id', id);
        const response = await fetch('api/apiUsuarios.php', { method: 'POST', body: formData });
        const data = await response.json();
        if (data.status === 'success') {
            location.reload();
            return;
        }
        Swal.fire({ icon: 'error', title: 'Error', text: data.message || 'No fue posible activar el usuario' });
    });
}
</script>
</body>
</html>
