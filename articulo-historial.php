<?php
require_once __DIR__ . '/auth.php';
requerir_autenticacion();

include_once 'api/adminArticulos.php';

$adminArticulos = new AdministradorArticulos();
$idArticulo = (int)($_GET['id'] ?? 0);
$articulo = json_decode($adminArticulos->obtenerArticulo($idArticulo));
$historialEntradas = json_decode($adminArticulos->obtenerHistorialEntradas($idArticulo)) ?: [];
$historialSalidas = json_decode($adminArticulos->obtenerHistorialSalidas($idArticulo)) ?: [];

if (!$articulo || empty($articulo[0])) {
    header('Location: articulos.php');
    exit;
}

$articulo = $articulo[0];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8" />
    <title>Almacén Croram - Historial de artículo</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta content="Historial operativo del artículo" name="description" />
    <meta content="HoppingJet Studio." name="author" />
    <link rel="shortcut icon" href="favicon.png">
    <link href="assets/css/vendor.min.css" rel="stylesheet" type="text/css" />
    <link href="assets/css/app.min.css" rel="stylesheet" type="text/css" id="app-style" />
    <link href="assets/css/icons.min.css" rel="stylesheet" type="text/css" />
    <script src="assets/js/config.js"></script>
    <link href="assets/vendor/datatables.net-bs5/css/dataTables.bootstrap5.min.css" rel="stylesheet" type="text/css" />
    <link href="assets/vendor/datatables.net-responsive-bs5/css/responsive.bootstrap5.min.css" rel="stylesheet" type="text/css" />
    <style>
        body {
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
        }

        .page-header-custom,
        .summary-card,
        .history-card {
            background: #fff;
            border: 1px solid #e9ecef;
            border-radius: 0.5rem;
            box-shadow: 0 0.125rem 0.5rem rgba(0, 0, 0, 0.05);
        }

        .page-header-custom {
            padding: 1.5rem;
            margin-bottom: 1.5rem;
            border-left: 6px solid #495057;
        }

        .summary-card {
            padding: 1.25rem;
            height: 100%;
        }

        .summary-label {
            color: #6c757d;
            font-size: 0.8rem;
            text-transform: uppercase;
            letter-spacing: 0.4px;
            margin-bottom: 0.35rem;
        }

        .summary-value {
            color: #212529;
            font-weight: 700;
            font-size: 1.05rem;
        }

        .history-card .card-header {
            background: #495057;
            color: #fff;
            border: none;
            padding: 1rem 1.25rem;
        }

        .table thead th {
            white-space: nowrap;
        }
    </style>
</head>
<body>
<div class="wrapper">
    <?php include_once 'templates/barra.php' ?>
    <?php include_once 'templates/headder.php' ?>

    <div class="page-content">
        <div class="page-header-custom d-flex justify-content-between align-items-center gap-3">
            <div>
                <h4 class="mb-1 fw-bold"><i class="ri-time-line me-2"></i>Historial del artículo</h4>
                <p class="text-muted mb-0">Consulta todas las órdenes en las que ha participado este artículo.</p>
            </div>
            <a href="articulos.php" class="btn btn-outline-secondary">
                <i class="ri-arrow-left-line me-1"></i>Volver a artículos
            </a>
        </div>

        <div class="page-container">
            <div class="row g-3 mb-4">
                <div class="col-md-3">
                    <div class="summary-card">
                        <div class="summary-label">ID</div>
                        <div class="summary-value"><?= (int)$articulo->id ?></div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="summary-card">
                        <div class="summary-label">Artículo</div>
                        <div class="summary-value"><?= htmlspecialchars($articulo->nombre ?? '') ?></div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="summary-card">
                        <div class="summary-label">SKU</div>
                        <div class="summary-value"><?= htmlspecialchars($articulo->sku ?? '') ?></div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="summary-card">
                        <div class="summary-label">Movimientos</div>
                        <div class="summary-value"><?= count($historialEntradas) + count($historialSalidas) ?></div>
                    </div>
                </div>
            </div>

            <div class="card history-card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <div>
                        <h4 class="header-title mb-1 text-white">Órdenes de entrada</h4>
                        <p class="mb-0 text-white-50">Aquí puedes ver cantidades y precio unitario registrado en compras.</p>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="tablaEntradasArticulo" class="table table-striped dt-responsive nowrap w-100">
                            <thead>
                                <tr>
                                    <th>Orden</th>
                                    <th>Folio</th>
                                    <th>Fecha</th>
                                    <th>Proveedor</th>
                                    <th>Usuario</th>
                                    <th>Estatus</th>
                                    <th>Cantidad</th>
                                    <th>Precio unitario</th>
                                    <th>Subtotal</th>
                                </tr>
                            </thead>
                            <tbody>
                            <?php foreach ($historialEntradas as $entrada): ?>
                                <tr>
                                    <td><a href="ordenes-entrada-detalle.php?id=<?= (int)$entrada->id_orden_compra ?>">#<?= (int)$entrada->id_orden_compra ?></a></td>
                                    <td><?= htmlspecialchars($entrada->folio ?? '') ?></td>
                                    <td><?= htmlspecialchars($entrada->fecha_orden ?? '') ?></td>
                                    <td><?= htmlspecialchars($entrada->proveedor ?? '') ?></td>
                                    <td><?= htmlspecialchars($entrada->usuario ?? '') ?></td>
                                    <td><?= htmlspecialchars($entrada->estatus ?? '') ?></td>
                                    <td><?= (int)round((float)($entrada->cantidad ?? 0)) ?></td>
                                    <td>$<?= number_format((float)($entrada->precio_unitario ?? 0), 2) ?></td>
                                    <td>$<?= number_format((float)($entrada->subtotal ?? 0), 2) ?></td>
                                </tr>
                            <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="card history-card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <div>
                        <h4 class="header-title mb-1 text-white">Órdenes de salida</h4>
                        <p class="mb-0 text-white-50">Consulta cuándo salió este artículo y en qué cantidad.</p>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="tablaSalidasArticulo" class="table table-striped dt-responsive nowrap w-100">
                            <thead>
                                <tr>
                                    <th>Orden</th>
                                    <th>Folio</th>
                                    <th>Fecha</th>
                                    <th>Tipo</th>
                                    <th>Usuario</th>
                                    <th>Estatus</th>
                                    <th>Cantidad</th>
                                    <th>Costo unitario</th>
                                    <th>Subtotal</th>
                                </tr>
                            </thead>
                            <tbody>
                            <?php foreach ($historialSalidas as $salida): ?>
                                <tr>
                                    <td><a href="ordenes-salida-detalle.php?id=<?= (int)$salida->id_orden_salida ?>">#<?= (int)$salida->id_orden_salida ?></a></td>
                                    <td><?= htmlspecialchars($salida->folio ?? '') ?></td>
                                    <td><?= htmlspecialchars($salida->fecha_salida ?? '') ?></td>
                                    <td><?= htmlspecialchars($salida->tipo ?? '') ?></td>
                                    <td><?= htmlspecialchars($salida->usuario ?? '') ?></td>
                                    <td><?= htmlspecialchars($salida->estatus ?? '') ?></td>
                                    <td><?= (int)round((float)($salida->cantidad ?? 0)) ?></td>
                                    <td>$<?= number_format((float)($salida->costo_unitario ?? 0), 2) ?></td>
                                    <td>$<?= number_format((float)($salida->subtotal ?? 0), 2) ?></td>
                                </tr>
                            <?php endforeach; ?>
                            </tbody>
                        </table>
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
        $('#tablaEntradasArticulo').DataTable({
            pageLength: 10,
            responsive: true,
            order: [[2, 'desc']]
        });

        $('#tablaSalidasArticulo').DataTable({
            pageLength: 10,
            responsive: true,
            order: [[2, 'desc']]
        });
    });
</script>
</body>
</html>
