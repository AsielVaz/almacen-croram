<?php
require_once __DIR__ . '/auth.php';
requerir_autenticacion();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <title>Almacen Croram - Proveedores</title>
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
        <?php include_once 'templates/barra.php' ?>
        <?php include_once 'templates/headder.php' ?>
        <?php
        include_once 'api/adminProveedores.php';
        $adminProveedores = new AdministradorProveedores();
        $proveedores = json_decode($adminProveedores->listarProveedores());
        ?>
        <div class="page-content">
            <div class="page-title-head d-flex align-items-center gap-2">
                <div class="flex-grow-1"><h4 class="fs-18 fw-bold mb-0">Proveedores</h4></div>
                <div class="text-end"><ol class="breadcrumb m-0 py-0 fs-13"><li class="breadcrumb-item"><a href="javascript:void(0);">Ver</a></li><li class="breadcrumb-item active">Proveedores</li></ol></div>
            </div>
            <div class="page-container">
                <div class="row"><div class="col-12"><div class="card"><div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div><h4 class="header-title">Proveedores</h4><p class="text-muted font-14 mb-0">Consulta y actualiza los proveedores disponibles para ordenes de compra.</p></div>
                        <div><a href="proveedores-alta.php" class="btn btn-primary"><i class="fa fa-plus mr-1"></i> Agregar proveedor</a></div>
                    </div>
                    <table id="alternative-page-datatable" class="table table-striped dt-responsive nowrap w-100">
                        <thead><tr><th>ID</th><th>Nombre</th><th>Contacto</th><th>Correo</th><th>RFC</th><th>Credito</th><th>Estado</th><th>Acciones</th></tr></thead>
                        <tbody>
                        <?php foreach ($proveedores as $proveedor): ?>
                            <tr>
                                <td><?= $proveedor->id ?></td>
                                <td><?= htmlspecialchars($proveedor->nombre) ?></td>
                                <td><?= htmlspecialchars($proveedor->contacto ?: 'Sin contacto') ?></td>
                                <td><?= htmlspecialchars($proveedor->mail ?: 'Sin correo') ?></td>
                                <td><?= htmlspecialchars($proveedor->rfc ?: 'Sin RFC') ?></td>
                                <td><?= ((int)$proveedor->credito === 1) ? 'Si' : 'No' ?></td>
                                <td><?= ((int)$proveedor->activo === 1) ? 'Activo' : 'Inactivo' ?></td>
                                <td><a href="proveedores-alta.php?id=<?= $proveedor->id ?>" class="btn btn-sm btn-primary">Editar</a> <button class="btn btn-sm btn-danger" onclick="eliminarProveedor(<?= $proveedor->id ?>)">Eliminar</button></td>
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
        function eliminarProveedor(id) {
            Swal.fire({ title: 'Eliminar proveedor', text: 'El proveedor se marcara como inactivo.', icon: 'warning', showCancelButton: true, confirmButtonText: 'Si, eliminar', cancelButtonText: 'Cancelar' })
            .then((result) => {
                if (!result.isConfirmed) return;
                const formData = new FormData();
                formData.append('accion', 'eliminarProveedor');
                formData.append('id', id);
                fetch('api/apiProveedores.php', { method: 'POST', body: formData })
                    .then(r => r.json())
                    .then(res => {
                        if (res.status === 'success') {
                            Swal.fire('Eliminado', res.message, 'success').then(() => location.reload());
                        } else {
                            Swal.fire('Error', res.message || 'No se pudo eliminar el proveedor', 'error');
                        }
                    });
            });
        }
    </script>
</body>
</html>

