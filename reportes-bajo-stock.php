<?php
require_once __DIR__ . '/auth.php';
requerir_autenticacion();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <title>Almacen Croram - Bajo Stock</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta content="Reporte de items con bajo stock" name="description" />
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
$adminArticulos = new AdministradorArticulos();

$limite = isset($_GET['limite']) ? (float)$_GET['limite'] : 5;
$titulo = $limite <= 0 ? 'Items sin stock' : 'Items con bajo stock';
$descripcion = $limite <= 0
    ? 'Articulos cuya existencia actual es 0 o negativa.'
    : 'Articulos cuya existencia actual es menor o igual al limite configurado.';

$articulos = json_decode($adminArticulos->listarArticulosBajoStock($limite, true), true) ?: [];
$totalItems = count($articulos);
$totalSinStock = count(array_filter($articulos, fn($a) => (float)($a['cantidad'] ?? 0) <= 0));
?>

<div class="page-content">
    <div class="page-title-head d-flex align-items-center gap-2">
        <div class="flex-grow-1">
            <h4 class="fs-18 fw-bold mb-0"><?= $titulo ?></h4>
        </div>
        <div class="text-end">
            <ol class="breadcrumb m-0 py-0 fs-13">
                <li class="breadcrumb-item"><a href="index.php">Inicio</a></li>
                <li class="breadcrumb-item"><a href="reportes.php">Reportes</a></li>
                <li class="breadcrumb-item active"><?= $titulo ?></li>
            </ol>
        </div>
    </div>

    <div class="page-container">
        <div class="row g-3 mb-3">
            <div class="col-md-3"><div class="card"><div class="card-body"><small class="text-muted d-block">Limite actual</small><h3 class="mb-0"><?= number_format($limite, 0) ?></h3></div></div></div>
            <div class="col-md-3"><div class="card"><div class="card-body"><small class="text-muted d-block">Items encontrados</small><h3 class="mb-0"><?= $totalItems ?></h3></div></div></div>
            <div class="col-md-3"><div class="card"><div class="card-body"><small class="text-muted d-block">Sin stock</small><h3 class="mb-0"><?= $totalSinStock ?></h3></div></div></div>
            <div class="col-md-3"><div class="card"><div class="card-body"><small class="text-muted d-block">Con existencia critica</small><h3 class="mb-0"><?= max(0, $totalItems - $totalSinStock) ?></h3></div></div></div>
        </div>

        <div class="card mb-3">
            <div class="card-header d-flex justify-content-between align-items-center">
                <div>
                    <h4 class="header-title mb-1"><?= $titulo ?></h4>
                    <p class="text-muted mb-0"><?= $descripcion ?></p>
                </div>
                <div class="d-flex gap-2 align-items-center">
                    <form method="get" class="d-flex gap-2 align-items-center mb-0">
                        <label for="limite" class="mb-0 text-muted">Limite</label>
                        <input type="number" min="0" step="1" id="limite" name="limite" class="form-control form-control-sm" value="<?= htmlspecialchars((string)$limite) ?>" style="width: 90px;">
                        <button type="submit" class="btn btn-sm btn-outline-secondary">Aplicar</button>
                    </form>
                    <button class="btn btn-success btn-sm" onclick="exportTableToExcel('tablaBajoStock', 'reporte_bajo_stock')">Exportar Excel</button>
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table id="tablaBajoStock" class="table table-striped dt-responsive nowrap w-100">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>SKU</th>
                                <th>Articulo</th>
                                <th>Familia</th>
                                <th>Subfamilia</th>
                                <th>Descripcion</th>
                                <th>Unidad</th>
                                <th>Existencia</th>
                            </tr>
                        </thead>
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
        $('#tablaBajoStock').DataTable({
            pageLength: 10,
            responsive: true,
            order: [[7, 'asc']]
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

