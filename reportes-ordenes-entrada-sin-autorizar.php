<?php
require_once __DIR__ . '/auth.php';
requerir_autenticacion();

include_once __DIR__ . '/api/adminOrdenes.php';
$adminOrdenes = new AdministradorOrdenes();
$ordenes = json_decode($adminOrdenes->listarOrdenesCompraSinAutorizar(), true) ?: [];
$pendientesCompra = array_filter($ordenes, fn($orden) => ($orden['estatus'] ?? '') === 'PENDIENTE');
$pendientesRecepcion = array_filter(
    $ordenes,
    fn($orden) => ($orden['estatus'] ?? '') === 'PENDIENTE_AUTORIZACION_RECEPCION'
);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Almacén Croram - Entradas sin autorizar</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta content="Órdenes de entrada pendientes de autorización" name="description">
    <link rel="shortcut icon" href="favicon.png">
    <link href="assets/css/vendor.min.css" rel="stylesheet" type="text/css">
    <link href="assets/css/app.min.css" rel="stylesheet" type="text/css" id="app-style">
    <link href="assets/css/icons.min.css" rel="stylesheet" type="text/css">
    <script src="assets/js/config.js"></script>
    <style>
        .tabla-pendientes td,
        .tabla-pendientes th {
            vertical-align: middle;
        }

        .tabla-pendientes .dato-nowrap,
        .tabla-pendientes .btn {
            white-space: nowrap;
        }

        .tabla-pendientes .estatus-badge {
            max-width: 108px;
            white-space: normal;
            line-height: 1.2;
            text-align: center;
        }
    </style>
</head>
<body>
<div class="wrapper">
    <?php include_once 'templates/barra.php'; ?>
    <?php include_once 'templates/headder.php'; ?>

    <div class="page-content">
        <div class="page-title-head d-flex align-items-center gap-2">
            <div class="flex-grow-1">
                <h4 class="fs-18 fw-bold mb-0">Entradas sin autorizar</h4>
            </div>
            <div class="text-end">
                <ol class="breadcrumb m-0 py-0 fs-13">
                    <li class="breadcrumb-item"><a href="index.php">Inicio</a></li>
                    <li class="breadcrumb-item active">Entradas sin autorizar</li>
                </ol>
            </div>
        </div>

        <div class="page-container">
            <div class="row row-cols-1 row-cols-md-2 g-3 mb-3">
                <div class="col">
                    <div class="card h-100">
                        <div class="card-body">
                            <div class="text-muted text-uppercase fs-12 fw-semibold">Compras por autorizar</div>
                            <div class="fs-28 fw-bold mt-1"><?= count($pendientesCompra) ?></div>
                        </div>
                    </div>
                </div>
                <div class="col">
                    <div class="card h-100">
                        <div class="card-body">
                            <div class="text-muted text-uppercase fs-12 fw-semibold">Recepciones con precio mayor</div>
                            <div class="fs-28 fw-bold text-danger mt-1"><?= count($pendientesRecepcion) ?></div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <div>
                        <h4 class="header-title mb-1">Pendientes de administración</h4>
                        <p class="text-muted mb-0">Al volver desde el detalle, la lista se actualiza automáticamente.</p>
                    </div>
                    <a href="reportes-ordenes-entrada-sin-autorizar.php" class="btn btn-outline-secondary btn-sm">
                        <i class="ri-refresh-line me-1"></i>Actualizar
                    </a>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped align-middle mb-0 tabla-pendientes">
                            <thead>
                            <tr>
                                <th>Folio</th>
                                <th>Acción</th>
                                <th>Proveedor</th>
                                <th>Fecha</th>
                                <th>Solicitó</th>
                                <th class="text-center">Partidas</th>
                                <th class="text-center">Unidades</th>
                                <th class="text-end">Autorizado</th>
                                <th class="text-end">Recepción</th>
                                <th>Estatus</th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php if (count($ordenes) === 0): ?>
                                <tr>
                                    <td colspan="10" class="text-center text-muted py-5">
                                        No hay órdenes de entrada pendientes de autorización.
                                    </td>
                                </tr>
                            <?php endif; ?>
                            <?php foreach ($ordenes as $orden): ?>
                                <?php
                                $esRecepcion = ($orden['estatus'] ?? '') === 'PENDIENTE_AUTORIZACION_RECEPCION';
                                $detalleUrl = 'ordenes-entrada-detalle.php?id=' . (int)$orden['id']
                                    . '&return_to=' . rawurlencode('reportes-ordenes-entrada-sin-autorizar.php');
                                ?>
                                <tr>
                                    <td class="fw-semibold dato-nowrap"><?= htmlspecialchars($orden['folio'] ?? '') ?></td>
                                    <td>
                                        <a href="<?= htmlspecialchars($detalleUrl) ?>" class="btn btn-primary btn-sm">
                                            <i class="ri-search-eye-line me-1"></i>Revisar
                                        </a>
                                    </td>
                                    <td><?= htmlspecialchars($orden['nombre_proveedor'] ?? '') ?></td>
                                    <td class="dato-nowrap"><?= htmlspecialchars($orden['fecha_orden'] ?? '') ?></td>
                                    <td><?= htmlspecialchars($orden['nombre_usuario'] ?? '') ?></td>
                                    <td class="text-center"><?= (int)($orden['total_partidas'] ?? 0) ?></td>
                                    <td class="text-center"><?= number_format((float)($orden['total_unidades'] ?? 0), 0) ?></td>
                                    <td class="text-end">$<?= number_format((float)($orden['total_autorizado'] ?? 0), 2) ?></td>
                                    <td class="text-end">
                                        <?= $esRecepcion ? '$' . number_format((float)($orden['total_recepcion'] ?? 0), 2) : '—' ?>
                                    </td>
                                    <td>
                                        <span class="badge estatus-badge <?= $esRecepcion ? 'bg-danger-subtle text-danger' : 'bg-warning-subtle text-warning' ?>">
                                            <?= $esRecepcion ? 'RECEPCIÓN POR AUTORIZAR' : 'COMPRA POR AUTORIZAR' ?>
                                        </span>
                                    </td>
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

<?php include_once 'templates/theme.php'; ?>
<script src="assets/js/vendor.min.js"></script>
<script src="assets/js/app.js"></script>
</body>
</html>
