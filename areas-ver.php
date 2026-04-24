<?php
require_once __DIR__ . '/auth.php';
requerir_autenticacion();
include_once 'api/adminAreas.php';
$adminAreas = new AdministradorAreas();
$areas = json_decode($adminAreas->listarAreas(), true) ?: [];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8" />
    <title>AlmacÃ©n Croram - Ãreas</title>
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
</head>
<body>
<div class="wrapper">
    <?php include_once 'templates/barra.php'; ?>
    <?php include_once 'templates/headder.php'; ?>
    <div class="page-content">
        <div class="page-title-head d-flex align-items-center gap-2">
            <div class="flex-grow-1"><h4 class="fs-18 fw-bold mb-0">Ãreas</h4></div>
            <div class="text-end"><ol class="breadcrumb m-0 py-0 fs-13"><li class="breadcrumb-item"><a href="index.php">Inicio</a></li><li class="breadcrumb-item active">Ãreas</li></ol></div>
        </div>

        <div class="page-container">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div>
                            <h4 class="header-title">Ãreas registradas</h4>
                            <p class="text-muted mb-0">Consulta, edita o elimina las Ã¡reas disponibles del sistema.</p>
                        </div>
                        <a href="areas-alta.php" class="btn btn-primary">Agregar Ã¡rea</a>
                    </div>

                    <table id="alternative-page-datatable" class="table table-striped dt-responsive nowrap w-100">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Nombre</th>
                                <th>Presupuesto</th>
                                <th>Encargado</th>
                                <th>Correo</th>
                                <th>Rol</th>
                                <th>Fecha</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($areas as $area): ?>
                                <tr>
                                    <td><?= (int)$area['id'] ?></td>
                                    <td><?= htmlspecialchars($area['nombre'] ?? '') ?></td>
                                    <td>$<?= number_format((float)($area['presupuesto'] ?? 0), 2) ?></td>
                                    <td><?= htmlspecialchars($area['nombre_encargado'] ?? '') ?></td>
                                    <td><?= htmlspecialchars($area['email'] ?: 'Sin correo') ?></td>
                                    <td><?= htmlspecialchars($area['rol'] ?: 'Sin rol') ?></td>
                                    <td><?= htmlspecialchars($area['fecha_inserta'] ?? '') ?></td>
                                    <td>
                                        <a href="areas-alta.php?id=<?= (int)$area['id'] ?>" class="btn btn-sm btn-primary">Editar</a>
                                        <button type="button" class="btn btn-sm btn-danger" onclick="eliminarArea(<?= (int)$area['id'] ?>)">Eliminar</button>
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
async function eliminarArea(id) {
    const result = await Swal.fire({
        title: 'Eliminar Ã¡rea',
        text: 'Esta acciÃ³n eliminarÃ¡ el registro de forma permanente.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'SÃ­, eliminar',
        cancelButtonText: 'Cancelar'
    });

    if (!result.isConfirmed) return;

    const formData = new FormData();
    formData.append('accion', 'eliminarArea');
    formData.append('id', id);

    const response = await fetch('api/apiAreas.php', { method: 'POST', body: formData });
    const data = await response.json();

    if (data.status === 'success') {
        window.location.reload();
        return;
    }

    Swal.fire({ icon: 'error', title: 'Error', text: data.message || 'No fue posible eliminar el Ã¡rea' });
}
</script>
</body>
</html>
