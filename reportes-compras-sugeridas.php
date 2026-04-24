<?php
require_once __DIR__ . '/auth.php';
requerir_autenticacion();

include_once 'api/adminArticulos.php';

$adminArticulos = new AdministradorArticulos();
$dias = max(1, (int)($_GET['dias'] ?? 30));
$articulos = json_decode($adminArticulos->listarComprasSugeridas($dias), true) ?: [];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8" />
    <title>Almacén Croram - Compras sugeridas</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta content="Reporte de compras sugeridas del almacén" name="description" />
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

<div class="page-content">
    <div class="page-title-head d-flex align-items-center gap-2">
        <div class="flex-grow-1">
            <h4 class="fs-18 fw-bold mb-0">Compras sugeridas</h4>
        </div>
        <div class="text-end">
            <ol class="breadcrumb m-0 py-0 fs-13">
                <li class="breadcrumb-item"><a href="index.php">Inicio</a></li>
                <li class="breadcrumb-item"><a href="reportes.php">Reportes</a></li>
                <li class="breadcrumb-item active">Compras sugeridas</li>
            </ol>
        </div>
    </div>

    <div class="page-container">
        <div class="card mb-3">
            <div class="card-body">
                <form class="row g-3 align-items-end" method="get" action="reportes-compras-sugeridas.php">
                    <div class="col-md-3">
                        <label for="dias" class="form-label">Días de cobertura</label>
                        <input type="number" min="1" step="1" class="form-control" id="dias" name="dias" value="<?= $dias ?>">
                    </div>
                    <div class="col-md-auto">
                        <button type="submit" class="btn btn-primary">Actualizar reporte</button>
                    </div>
                    <div class="col-md-auto">
                        <button type="button" class="btn btn-success" onclick="exportTableToExcel('tablaComprasSugeridas', 'compras_sugeridas_<?= $dias ?>_dias')">Exportar Excel</button>
                    </div>
                    <div class="col-12">
                        <p class="text-muted mb-0">Se muestran los artículos activos cuyo inventario alcanzará para <?= $dias ?> días o menos. La prioridad se ordena por mayor tiempo de reposición y después por menos días restantes.</p>
                    </div>
                </form>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h4 class="header-title mb-1">Artículos próximos a agotarse</h4>
                <p class="text-muted mb-0">Total de resultados: <?= count($articulos) ?></p>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table id="tablaComprasSugeridas" class="table table-striped dt-responsive nowrap w-100">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>SKU</th>
                                <th>Artículo</th>
                                <th>Familia</th>
                                <th>Subfamilia</th>
                                <th>Existencia</th>
                                <th>Consumo diario</th>
                                <th>Tiempo reposición</th>
                                <th>Días restantes</th>
                                <th>Costo promedio</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($articulos as $articulo): ?>
                            <tr>
                                <td><?= (int)$articulo['id'] ?></td>
                                <td><?= htmlspecialchars($articulo['sku'] ?? '') ?></td>
                                <td><?= htmlspecialchars($articulo['nombre'] ?? '') ?></td>
                                <td><?= htmlspecialchars($articulo['familia'] ?? '') ?></td>
                                <td><?= htmlspecialchars($articulo['subfamilia'] ?? 'Sin familia') ?></td>
                                <td><?= number_format((float)($articulo['cantidad'] ?? 0), 0) ?></td>
                                <td><?= number_format((float)($articulo['consumo_diario'] ?? 0), 2) ?></td>
                                <td><?= (int)($articulo['tiempo_reposicion'] ?? 0) ?></td>
                                <td><?= number_format((float)($articulo['dias_restantes'] ?? 0), 2) ?></td>
                                <td>$<?= number_format((float)($articulo['costo_reposicion'] ?? 0), 2) ?></td>
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
        $('#tablaComprasSugeridas').DataTable({
            pageLength: 10,
            responsive: true,
            order: [[7, 'desc'], [8, 'asc']]
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
