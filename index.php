<?php
require_once __DIR__ . '/auth.php';
requerir_autenticacion();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <title>Almacen Croram - Inicio</title>
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
<?php include_once 'templates/barra.php' ?>
<?php include_once 'templates/headder.php' ?>
<?php
include_once 'api/adminArticulos.php';
include_once 'api/adminOrdenes.php';
include_once 'api/adminProveedores.php';

$adminArticulos = new AdministradorArticulos();
$adminOrdenes = new AdministradorOrdenes();
$adminProveedores = new AdministradorProveedores();

$resumenArticulos = json_decode($adminArticulos->obtenerResumenDashboard(), true)[0] ?? [];
$totalArticulos = (int)($resumenArticulos['total_articulos'] ?? 0);
$totalStock = (float)($resumenArticulos['total_stock'] ?? 0);
$sinStock = (int)($resumenArticulos['sin_stock'] ?? 0);
$totalProveedores = $adminProveedores->contarProveedores(true);
$pendientesEntrada = $adminOrdenes->contarOrdenesCompraPorEstatus(['PENDIENTE', 'AUTORIZADA']);
$pendientesSalida = $adminOrdenes->contarOrdenesSalidaPorEstatus(['BORRADOR']);

$ultimasEntradas = json_decode($adminOrdenes->listarOrdenesCompra(5), true) ?: [];
$ultimasSalidas = json_decode($adminOrdenes->listarOrdenesSalida(5), true) ?: [];
$articulosCriticos = json_decode($adminArticulos->listarArticulosCriticos(5), true) ?: [];
?>

<div class="page-content">
    <div class="page-title-head d-flex align-items-center gap-2">
        <div class="flex-grow-1">
            <h4 class="fs-18 fw-bold mb-0">Panel Operativo</h4>
        </div>
        <div class="text-end">
            <ol class="breadcrumb m-0 py-0 fs-13">
                <li class="breadcrumb-item"><a href="javascript:void(0);">Inicio</a></li>
                <li class="breadcrumb-item active">Resumen</li>
            </ol>
        </div>
    </div>

    <div class="page-container">
        <div class="row row-cols-xxl-4 row-cols-md-2 row-cols-1">
            <div class="col">
                <div class="card"><div class="card-body"><div class="d-flex align-items-start gap-2 justify-content-between"><div><h5 class="text-muted fs-13 fw-bold text-uppercase">ArtÃ­culos activos</h5><h3 class="mt-2 mb-1 fw-bold"><?= $totalArticulos ?></h3><p class="mb-0 text-muted"><span class="text-nowrap">CatÃ¡logo disponible para operaciones</span></p></div><div class="avatar-lg flex-shrink-0"><span class="avatar-title bg-primary-subtle text-primary rounded fs-28"><i class="ri-box-3-line"></i></span></div></div></div></div>
            </div>
            <div class="col">
                <div class="card"><div class="card-body"><div class="d-flex align-items-start gap-2 justify-content-between"><div><h5 class="text-muted fs-13 fw-bold text-uppercase">Unidades en stock</h5><h3 class="mt-2 mb-1 fw-bold"><?= number_format($totalStock, 0) ?></h3><p class="mb-0 text-muted"><span class="text-nowrap">Suma de inventario actual</span></p></div><div class="avatar-lg flex-shrink-0"><span class="avatar-title bg-success-subtle text-success rounded fs-28"><i class="ri-database-2-line"></i></span></div></div></div></div>
            </div>
            <div class="col">
                <div class="card"><div class="card-body"><div class="d-flex align-items-start gap-2 justify-content-between"><div><h5 class="text-muted fs-13 fw-bold text-uppercase">Proveedores activos</h5><h3 class="mt-2 mb-1 fw-bold"><?= $totalProveedores ?></h3><p class="mb-0 text-muted"><span class="text-nowrap">Disponibles para compra</span></p></div><div class="avatar-lg flex-shrink-0"><span class="avatar-title bg-info-subtle text-info rounded fs-28"><i class="ri-building-line"></i></span></div></div></div></div>
            </div>
            <div class="col">
                <div class="card"><div class="card-body"><div class="d-flex align-items-start gap-2 justify-content-between"><div><h5 class="text-muted fs-13 fw-bold text-uppercase">Ã“rdenes pendientes</h5><h3 class="mt-2 mb-1 fw-bold"><?= $pendientesEntrada + $pendientesSalida ?></h3><p class="mb-0 text-muted"><span class="text-nowrap"><?= $pendientesEntrada ?> entradas y <?= $pendientesSalida ?> salidas</span></p></div><div class="avatar-lg flex-shrink-0"><span class="avatar-title bg-warning-subtle text-warning rounded fs-28"><i class="ri-file-list-3-line"></i></span></div></div></div></div>
            </div>
        </div>

        <div class="row">
            <div class="col-xl-8">
                <div class="card mb-3">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h4 class="header-title mb-0">Ãšltimas Ã³rdenes de entrada</h4>
                        <a href="ordenes-entrada.php" class="btn btn-sm btn-outline-primary">Ver todas</a>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped align-middle mb-0">
                                <thead><tr><th>Folio</th><th>Proveedor</th><th>Fecha</th><th>Estatus</th></tr></thead>
                                <tbody>
                                <?php if (count($ultimasEntradas) === 0): ?>
                                    <tr><td colspan="4" class="text-muted">No hay Ã³rdenes de entrada registradas.</td></tr>
                                <?php else: ?>
                                    <?php foreach ($ultimasEntradas as $orden): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($orden['folio'] ?? '') ?></td>
                                            <td><?= htmlspecialchars($orden['nombre_proveedor'] ?? ('Proveedor #' . ($orden['id_proveedor'] ?? ''))) ?></td>
                                            <td><?= htmlspecialchars($orden['fecha_orden'] ?? '') ?></td>
                                            <td><?= htmlspecialchars($orden['estatus'] ?? '') ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h4 class="header-title mb-0">Ãšltimas Ã³rdenes de salida</h4>
                        <a href="ordenes-salida.php" class="btn btn-sm btn-outline-danger">Ver todas</a>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped align-middle mb-0">
                                <thead><tr><th>Folio</th><th>SolicitÃ³</th><th>Fecha</th><th>Estatus</th></tr></thead>
                                <tbody>
                                <?php if (count($ultimasSalidas) === 0): ?>
                                    <tr><td colspan="4" class="text-muted">No hay Ã³rdenes de salida registradas.</td></tr>
                                <?php else: ?>
                                    <?php foreach ($ultimasSalidas as $orden): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($orden['folio'] ?? '') ?></td>
                                            <td><?= htmlspecialchars($orden['nombre_usuario'] ?? ('Usuario #' . ($orden['id_usuario'] ?? ''))) ?></td>
                                            <td><?= htmlspecialchars($orden['fecha_salida'] ?? '') ?></td>
                                            <td><?= htmlspecialchars($orden['estatus'] ?? '') ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-4">
                <div class="card mb-3">
                    <div class="card-header"><h4 class="header-title mb-0">AtenciÃ³n inmediata</h4></div>
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <span class="text-muted">ArtÃ­culos sin stock</span>
                            <span class="badge bg-danger-subtle text-danger"><?= $sinStock ?></span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="text-muted">Entradas pendientes</span>
                            <span class="badge bg-warning-subtle text-warning"><?= $pendientesEntrada ?></span>
                        </div>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h4 class="header-title mb-0">ArtÃ­culos con menor stock</h4>
                        <a href="articulos.php" class="btn btn-sm btn-outline-secondary">Ver catÃ¡logo</a>
                    </div>
                    <div class="card-body">
                        <?php if (count($articulosCriticos) === 0): ?>
                            <p class="text-muted mb-0">No hay artÃ­culos para mostrar.</p>
                        <?php else: ?>
                            <?php foreach ($articulosCriticos as $articulo): ?>
                                <div class="d-flex justify-content-between align-items-start py-2 border-bottom">
                                    <div class="pe-3">
                                        <div class="fw-semibold"><?= htmlspecialchars($articulo['nombre'] ?? '') ?></div>
                                        <small class="text-muted"><?= htmlspecialchars($articulo['sku'] ?? '') ?></small>
                                    </div>
                                    <span class="badge <?= (($articulo['cantidad'] ?? 0) <= 0) ? 'bg-danger-subtle text-danger' : 'bg-secondary-subtle text-secondary' ?>">
                                        <?= number_format((float)($articulo['cantidad'] ?? 0), 0) ?>
                                    </span>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
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
</body>
</html>

