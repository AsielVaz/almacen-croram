<?php
require_once __DIR__ . '/auth.php';
requerir_autenticacion();

include_once 'api/adminArticulos.php';

$adminArticulos = new AdministradorArticulos();
$diasStock = max(1, (int)($_GET['dias_stock'] ?? 15));
$fechaInicioAnalisis = $_GET['fecha_inicio'] ?? '';
$articulos = json_decode($adminArticulos->listarComprasSugeridas($diasStock, $fechaInicioAnalisis), true) ?: [];
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
                        <label for="dias_stock" class="form-label">Días de stock requeridos</label>
                        <input type="number" min="1" step="1" class="form-control" id="dias_stock" name="dias_stock" value="<?= $diasStock ?>">
                    </div>
                    <div class="col-md-3">
                        <label for="fecha_inicio" class="form-label">Inicio de análisis</label>
                        <input type="date" class="form-control" id="fecha_inicio" name="fecha_inicio" value="<?= htmlspecialchars($fechaInicioAnalisis) ?>">
                    </div>
                    <div class="col-md-auto">
                        <button type="submit" class="btn btn-primary">Actualizar reporte</button>
                    </div>
                    <div class="col-md-auto">
                        <button type="button" class="btn btn-success" onclick="exportTableToExcel('tablaComprasSugeridas', 'compras_sugeridas_<?= $diasStock ?>_dias_stock')">Exportar Excel</button>
                    </div>
                    <div class="col-md-auto">
                        <button type="button" class="btn btn-outline-secondary" id="btnSeleccionarCompras">Seleccionar activos</button>
                    </div>
                    <div class="col-md-auto">
                        <button type="button" class="btn btn-dark" id="btnCrearOrdenCompra">
                            <i class="ri-shopping-cart-line me-1"></i>Crear orden con seleccionados
                        </button>
                    </div>
                    <div class="col-12">
                        <p class="text-muted mb-0">Stock sugerido = redondear((consumo mensual promedio desde el inicio de análisis / 30.4) * (días de stock requeridos + tiempo de surtido)). Compra sugerida = stock sugerido - existencia actual.</p>
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
                                <th>Comprar</th>
                                <th>ID</th>
                                <th>SKU</th>
                                <th>Estado</th>
                                <th>Artículo</th>
                                <th>Familia</th>
                                <th>Subfamilia</th>
                                <th>Existencia</th>
                                <th>Consumo mensual prom.</th>
                                <th>Inicio análisis</th>
                                <th>Días stock req.</th>
                                <th>Tiempo surtido</th>
                                <th>Días restantes</th>
                                <th>Stock sugerido</th>
                                <th>Pedido sugerido</th>
                                <th>Pedido confirmado</th>
                                <th>Costo por pieza</th>
                                <th>Compra sugerida</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($articulos as $articulo): ?>
                            <?php $estaActivo = (int)($articulo['activo'] ?? 0) === 1; ?>
                            <tr
                                data-id="<?= (int)$articulo['id'] ?>"
                                data-sku="<?= htmlspecialchars($articulo['sku'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                                data-nombre="<?= htmlspecialchars($articulo['nombre'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                                data-unidad="<?= htmlspecialchars($articulo['unidad_medida'] ?? 'pz', ENT_QUOTES, 'UTF-8') ?>"
                                data-precio="<?= htmlspecialchars((string)($articulo['costo_reposicion'] ?? 0), ENT_QUOTES, 'UTF-8') ?>"
                            >
                                <td>
                                    <input type="checkbox" class="form-check-input seleccionar-compra" <?= $estaActivo ? '' : 'disabled' ?> aria-label="Incluir en orden de compra">
                                </td>
                                <td><?= (int)$articulo['id'] ?></td>
                                <td><?= htmlspecialchars($articulo['sku'] ?? '') ?></td>
                                <td>
                                    <span class="badge <?= $estaActivo ? 'bg-success-subtle text-success' : 'bg-secondary-subtle text-secondary' ?>">
                                        <?= $estaActivo ? 'ACTIVO' : 'INACTIVO' ?>
                                    </span>
                                </td>
                                <td><?= htmlspecialchars($articulo['nombre'] ?? '') ?></td>
                                <td><?= htmlspecialchars($articulo['familia'] ?? '') ?></td>
                                <td><?= htmlspecialchars($articulo['subfamilia'] ?? 'Sin familia') ?></td>
                                <td><?= number_format((float)($articulo['cantidad'] ?? 0), 0) ?></td>
                                <td><?= number_format((float)($articulo['consumo_mensual_promedio'] ?? 0), 2) ?></td>
                                <td><?= htmlspecialchars($articulo['fecha_inicio_analisis'] ?? '') ?></td>
                                <td><?= (int)($articulo['dias_stock_requeridos'] ?? $diasStock) ?></td>
                                <td><?= (int)($articulo['tiempo_reposicion'] ?? 0) ?></td>
                                <td><?= number_format((float)($articulo['dias_restantes'] ?? 0), 2) ?></td>
                                <td><?= number_format((float)($articulo['stock_objetivo'] ?? 0), 0) ?></td>
                                <td><?= number_format((float)($articulo['pedido_sugerido'] ?? 0), 0) ?></td>
                                <td>
                                    <input type="number" min="0" step="1" class="form-control form-control-sm pedido-confirmado" value="<?= (int)round((float)($articulo['compra_sugerida'] ?? $articulo['pedido_sugerido'] ?? 0)) ?>">
                                </td>
                                <td>$<?= number_format((float)($articulo['costo_reposicion'] ?? 0), 2) ?></td>
                                <td><?= number_format((float)($articulo['compra_sugerida'] ?? $articulo['pedido_sugerido'] ?? 0), 0) ?></td>
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
    let tablaComprasSugeridas;

    $(document).ready(function () {
        tablaComprasSugeridas = $('#tablaComprasSugeridas').DataTable({
            pageLength: 10,
            responsive: true,
            order: [[17, 'desc']],
            columnDefs: [{ targets: 0, orderable: false, searchable: false }]
        });
    });

    document.getElementById('btnSeleccionarCompras').addEventListener('click', function () {
        const checks = tablaComprasSugeridas
            ? tablaComprasSugeridas.rows({ search: 'applied' }).nodes().to$().find('.seleccionar-compra:not(:disabled)')
            : $('.seleccionar-compra:not(:disabled)');
        const seleccionar = checks.filter(':checked').length !== checks.length;
        checks.prop('checked', seleccionar);
        this.textContent = seleccionar ? 'Quitar selección' : 'Seleccionar activos';
    });

    document.getElementById('btnCrearOrdenCompra').addEventListener('click', function () {
        const nodos = tablaComprasSugeridas
            ? tablaComprasSugeridas.rows().nodes().toArray()
            : Array.from(document.querySelectorAll('#tablaComprasSugeridas tbody tr'));
        const items = [];

        nodos.forEach(fila => {
            const check = fila.querySelector('.seleccionar-compra');
            if (!check || !check.checked || check.disabled) return;

            const cantidad = Math.max(0, Math.round(Number(fila.querySelector('.pedido-confirmado')?.value || 0)));
            if (cantidad <= 0) return;

            const precio = Math.max(0, Number(fila.dataset.precio || 0));
            items.push({
                id: Number(fila.dataset.id),
                sku: fila.dataset.sku || '',
                nombre: fila.dataset.nombre || 'Sin nombre',
                unidad: fila.dataset.unidad || 'pz',
                cantidad,
                precio,
                total: cantidad * precio
            });
        });

        if (items.length === 0) {
            alert('Selecciona al menos un artículo activo con una cantidad mayor a cero.');
            return;
        }

        sessionStorage.setItem('croram_compra_sugerida', JSON.stringify({
            origen: 'compras_sugeridas',
            creado_en: new Date().toISOString(),
            items
        }));
        window.location.href = 'ordenes-entrada-form.php?origen=compras-sugeridas';
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
