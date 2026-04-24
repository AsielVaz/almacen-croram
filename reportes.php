<?php
require_once __DIR__ . '/auth.php';
requerir_autenticacion();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <title>Almacen Croram - Reportes</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta content="Reportes operativos del almacen" name="description" />
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
include_once 'api/adminArticulos.php';
include_once 'api/adminOrdenes.php';
include_once 'api/adminProveedores.php';

$adminArticulos = new AdministradorArticulos();
$adminOrdenes = new AdministradorOrdenes();
$adminProveedores = new AdministradorProveedores();

$articulos = json_decode($adminArticulos->listarArticulosCompleto(false), true) ?: [];
$proveedores = json_decode($adminProveedores->listarProveedores(false), true) ?: [];
$ordenesEntrada = json_decode($adminOrdenes->listarOrdenesCompra(), true) ?: [];
$ordenesSalida = json_decode($adminOrdenes->listarOrdenesSalida(), true) ?: [];
?>

<div class="page-content">
    <div class="page-title-head d-flex align-items-center gap-2">
        <div class="flex-grow-1">
            <h4 class="fs-18 fw-bold mb-0">Reportes</h4>
        </div>
        <div class="text-end">
            <ol class="breadcrumb m-0 py-0 fs-13">
                <li class="breadcrumb-item"><a href="index.php">Inicio</a></li>
                <li class="breadcrumb-item active">Reportes</li>
            </ol>
        </div>
    </div>

    <div class="page-container">
        <div class="row g-3 mb-3">
            <div class="col-md-3"><div class="card"><div class="card-body"><small class="text-muted d-block">Articulos</small><h3 class="mb-0"><?= count($articulos) ?></h3></div></div></div>
            <div class="col-md-3"><div class="card"><div class="card-body"><small class="text-muted d-block">Proveedores</small><h3 class="mb-0"><?= count($proveedores) ?></h3></div></div></div>
            <div class="col-md-3"><div class="card"><div class="card-body"><small class="text-muted d-block">Ordenes entrada</small><h3 class="mb-0"><?= count($ordenesEntrada) ?></h3></div></div></div>
            <div class="col-md-3"><div class="card"><div class="card-body"><small class="text-muted d-block">Ordenes salida</small><h3 class="mb-0"><?= count($ordenesSalida) ?></h3></div></div></div>
        </div>

        <div class="card mb-3">
            <div class="card-header d-flex justify-content-between align-items-center">
                <div>
                    <h4 class="header-title mb-1">Inventario detallado</h4>
                    <p class="text-muted mb-0">Catalogo completo con familia, subfamilia y existencias.</p>
                </div>
                <button class="btn btn-success btn-sm" onclick="exportTableToExcel('tablaInventario', 'reporte_inventario')">Exportar Excel</button>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table id="tablaInventario" class="table table-striped dt-responsive nowrap w-100 report-table">
                        <thead><tr><th>ID</th><th>SKU</th><th>Articulo</th><th>Familia</th><th>Subfamilia</th><th>Descripcion</th><th>Unidad</th><th>Existencia</th><th>Estado</th></tr></thead>
                        <tbody>
                        <?php foreach ($articulos as $articulo): ?>
                            <tr>
                                <td><?= $articulo['id'] ?></td>
                                <td><?= htmlspecialchars($articulo['sku'] ?? '') ?></td>
                                <td><?= htmlspecialchars($articulo['nombre'] ?? '') ?></td>
                                <td><?= htmlspecialchars($articulo['familia'] ?? '') ?></td>
                                <td><?= htmlspecialchars($articulo['subfamilia'] ?? '') ?></td>
                                <td><?= htmlspecialchars($articulo['descripcion'] ?? '') ?></td>
                                <td><?= htmlspecialchars($articulo['unidad_medida'] ?? '') ?></td>
                                <td><?= number_format((float)($articulo['cantidad'] ?? 0), 0) ?></td>
                                <td><?= ((int)($articulo['activo'] ?? 0) === 1) ? 'Activo' : 'Inactivo' ?></td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="card mb-3">
            <div class="card-header d-flex justify-content-between align-items-center">
                <div>
                    <h4 class="header-title mb-1">Proveedores</h4>
                    <p class="text-muted mb-0">Directorio de proveedores y condiciones de credito.</p>
                </div>
                <button class="btn btn-success btn-sm" onclick="exportTableToExcel('tablaProveedores', 'reporte_proveedores')">Exportar Excel</button>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table id="tablaProveedores" class="table table-striped dt-responsive nowrap w-100 report-table">
                        <thead><tr><th>ID</th><th>Nombre</th><th>Contacto</th><th>Correo</th><th>RFC</th><th>Credito</th><th>Plazo</th><th>Estado</th></tr></thead>
                        <tbody>
                        <?php foreach ($proveedores as $proveedor): ?>
                            <tr>
                                <td><?= $proveedor['id'] ?></td>
                                <td><?= htmlspecialchars($proveedor['nombre'] ?? '') ?></td>
                                <td><?= htmlspecialchars($proveedor['contacto'] ?? '') ?></td>
                                <td><?= htmlspecialchars($proveedor['mail'] ?? '') ?></td>
                                <td><?= htmlspecialchars($proveedor['rfc'] ?? '') ?></td>
                                <td><?= ((int)($proveedor['credito'] ?? 0) === 1) ? 'Si' : 'No' ?></td>
                                <td><?= (int)($proveedor['plazo_credito'] ?? 0) ?></td>
                                <td><?= ((int)($proveedor['activo'] ?? 0) === 1) ? 'Activo' : 'Inactivo' ?></td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-xl-6">
                <div class="card mb-3">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <div>
                            <h4 class="header-title mb-1">Ordenes de entrada</h4>
                            <p class="text-muted mb-0">Seguimiento de compras registradas.</p>
                        </div>
                        <button class="btn btn-success btn-sm" onclick="exportTableToExcel('tablaEntradas', 'reporte_ordenes_entrada')">Exportar Excel</button>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table id="tablaEntradas" class="table table-striped dt-responsive nowrap w-100 report-table">
                                <thead><tr><th>ID</th><th>Folio</th><th>Proveedor</th><th>Fecha</th><th>Estatus</th><th>Solicito</th></tr></thead>
                                <tbody>
                                <?php foreach ($ordenesEntrada as $orden): ?>
                                    <tr>
                                        <td><?= $orden['id'] ?></td>
                                        <td><?= htmlspecialchars($orden['folio'] ?? '') ?></td>
                                        <td><?= htmlspecialchars($orden['nombre_proveedor'] ?? '') ?></td>
                                        <td><?= htmlspecialchars($orden['fecha_orden'] ?? '') ?></td>
                                        <td><?= htmlspecialchars($orden['estatus'] ?? '') ?></td>
                                        <td><?= htmlspecialchars($orden['nombre_usuario'] ?? '') ?></td>
                                    </tr>
                                <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-6">
                <div class="card mb-3">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <div>
                            <h4 class="header-title mb-1">Ordenes de salida</h4>
                            <p class="text-muted mb-0">Seguimiento de movimientos de salida.</p>
                        </div>
                        <button class="btn btn-success btn-sm" onclick="exportTableToExcel('tablaSalidas', 'reporte_ordenes_salida')">Exportar Excel</button>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table id="tablaSalidas" class="table table-striped dt-responsive nowrap w-100 report-table">
                                <thead><tr><th>ID</th><th>Folio</th><th>Fecha</th><th>Tipo</th><th>Estatus</th><th>Solicito</th></tr></thead>
                                <tbody>
                                <?php foreach ($ordenesSalida as $orden): ?>
                                    <tr>
                                        <td><?= $orden['id'] ?></td>
                                        <td><?= htmlspecialchars($orden['folio'] ?? '') ?></td>
                                        <td><?= htmlspecialchars($orden['fecha_salida'] ?? '') ?></td>
                                        <td><?= htmlspecialchars($orden['tipo'] ?? '') ?></td>
                                        <td><?= htmlspecialchars($orden['estatus'] ?? '') ?></td>
                                        <td><?= htmlspecialchars($orden['nombre_usuario'] ?? '') ?></td>
                                    </tr>
                                <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php include 'templates/footer.php'; ?>
</div>
</div>

<?php include_once 'templates/theme.php' ?>
<script src="assets/js/vendor.min.js"></script>
<script src="assets/js/app.js"></script>
<script src="assets/vendor/datatables.net/js/jquery.dataTables.min.js"></script>
<script src="assets/vendor/datatables.net-bs5/js/dataTables.bootstrap5.min.js"></script>
<script src="assets/vendor/datatables.net-responsive/js/dataTables.responsive.min.js"></script>
<script src="assets/vendor/datatables.net-responsive-bs5/js/responsive.bootstrap5.min.js"></script>
<script>
    $(document).ready(function () {
        $('.report-table').DataTable({
            pageLength: 10,
            responsive: true,
            order: []
        });
    });

    function exportTableToExcel(tableId, filename) {
        const table = document.getElementById(tableId).outerHTML;
        const template = `
            <html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:x="urn:schemas-microsoft-com:office:excel" xmlns="http://www.w3.org/TR/REC-html40">
            <head><meta charset="UTF-8"></head>
            <body>${table}</body>
            </html>`;

        const blob = new Blob([template], { type: 'application/vnd.ms-excel' });
        const link = document.createElement('a');
        link.href = URL.createObjectURL(blob);
        link.download = `${filename}.xls`;
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
    }
</script>
</body>
</html>

