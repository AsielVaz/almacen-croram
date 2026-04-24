<?php
require_once __DIR__ . '/auth.php';
requerir_autenticacion();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <title>Almacen Croram - Subfamilias</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta content="Sistema de inventario de CRORAM." name="description" />
    <meta content="HoppingJet Studio." name="author" />
    <link rel="shortcut icon" href="assets/images/favicon.ico">
    <link href="assets/css/vendor.min.css" rel="stylesheet" type="text/css" />
    <link href="assets/css/app.min.css" rel="stylesheet" type="text/css" id="app-style" />
    <link href="assets/css/icons.min.css" rel="stylesheet" type="text/css" />
    <script src="assets/js/config.js"></script>
    <link href="assets/vendor/datatables.net-bs5/css/dataTables.bootstrap5.min.css" rel="stylesheet" type="text/css" />
    <link href="assets/vendor/datatables.net-responsive-bs5/css/responsive.bootstrap5.min.css" rel="stylesheet" type="text/css" />
</head>
<body>
    <div class="wrapper">
        <?php include_once 'templates/barra.php' ?>
        <?php include_once 'templates/headder.php' ?>
        <?php
        include_once 'api/adminCatalogos.php';
        $adminCatalogos = new AdministradorCatalogos();
        $subfamilias = json_decode($adminCatalogos->listarSubfamilias(null, false));
        ?>
        <div class="page-content">
            <div class="page-title-head d-flex align-items-center gap-2">
                <div class="flex-grow-1"><h4 class="fs-18 fw-bold mb-0">Subfamilias</h4></div>
                <div class="text-end"><ol class="breadcrumb m-0 py-0 fs-13"><li class="breadcrumb-item"><a href="javascript:void(0);">Catalogos</a></li><li class="breadcrumb-item active">Subfamilias</li></ol></div>
            </div>
            <div class="page-container">
                <div class="row"><div class="col-12"><div class="card"><div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div><h4 class="header-title mb-1">Subfamilias</h4><p class="text-muted font-14 mb-0">Administra las subfamilias ligadas a cada familia del inventario.</p></div>
                        <div><a href="catalogos-subfamilias-form.php" class="btn btn-primary"><i class="fa fa-plus mr-1"></i> Agregar nueva subfamilia</a></div>
                    </div>
                    <table id="alternative-page-datatable" class="table table-striped dt-responsive nowrap w-100">
                        <thead><tr><th>ID</th><th>Familia</th><th>Nombre</th><th>Descripcion</th><th>Estado</th><th>Acciones</th></tr></thead>
                        <tbody>
                        <?php foreach ($subfamilias as $subfamilia): ?>
                            <tr>
                                <td><?= $subfamilia->id ?></td>
                                <td><?= htmlspecialchars($subfamilia->familia) ?></td>
                                <td><?= htmlspecialchars($subfamilia->nombre) ?></td>
                                <td><?= htmlspecialchars($subfamilia->descripcion ?: 'Sin descripcion') ?></td>
                                <td><?= ((int)$subfamilia->activo === 1) ? 'Activa' : 'Inactiva' ?></td>
                                <td><a href="catalogos-subfamilias-form.php?id=<?= $subfamilia->id ?>" class="btn btn-sm btn-primary">Editar</a> <button class="btn btn-sm btn-danger" onclick="eliminarSubFamilia(<?= $subfamilia->id ?>)">Eliminar</button></td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div></div></div></div>
            </div>
            <?php include 'templates/footer.php'; ?>
        </div>
    </div>
    <?php include_once 'templates/theme.php' ?>
    <script src="assets/js/vendor.min.js"></script>
    <script src="assets/js/app.js"></script>
    <script src="assets/vendor/apexcharts/apexcharts.min.js"></script>
    <script src="assets/js/pages/dashboard.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="assets/vendor/datatables.net/js/jquery.dataTables.min.js"></script>
    <script src="assets/vendor/datatables.net-bs5/js/dataTables.bootstrap5.min.js"></script>
    <script src="assets/vendor/datatables.net-responsive/js/dataTables.responsive.min.js"></script>
    <script src="assets/vendor/datatables.net-responsive-bs5/js/responsive.bootstrap5.min.js"></script>
    <script src="assets/js/components/table-datatable.js"></script>
    <script>
        function eliminarSubFamilia(id) {
            Swal.fire({ title: 'Eliminar subfamilia', text: 'La subfamilia se marcara como inactiva.', icon: 'warning', showCancelButton: true, confirmButtonText: 'Si, eliminar', cancelButtonText: 'Cancelar' })
            .then((result) => {
                if (!result.isConfirmed) return;
                const formData = new FormData();
                formData.append('accion', 'eliminarSubFamilia');
                formData.append('id', id);
                fetch('api/apiCatalogos.php', { method: 'POST', body: formData })
                    .then(r => r.json())
                    .then(res => {
                        if (res.status === 'success') {
                            Swal.fire('Eliminado', res.message, 'success').then(() => location.reload());
                        } else {
                            Swal.fire('Error', res.message || 'No se pudo eliminar la subfamilia', 'error');
                        }
                    });
            });
        }
    </script>
</body>
</html>

