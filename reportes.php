<?php
require_once __DIR__ . '/auth.php';
requerir_autenticacion();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8" />
    <title>Almacén Croram - Reportes</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta content="Reportes operativos del almacén" name="description" />
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
include_once 'api/adminArticulos.php';
include_once 'api/adminOrdenes.php';
include_once 'api/adminProveedores.php';
include_once 'api/adminCatalogos.php';

$adminArticulos = new AdministradorArticulos();
$adminOrdenes = new AdministradorOrdenes();
$adminProveedores = new AdministradorProveedores();
$adminCatalogos = new AdministradorCatalogos();

$entradaInicio = $_GET['entrada_inicio'] ?? '';
$entradaFin = $_GET['entrada_fin'] ?? '';
$salidaInicio = $_GET['salida_inicio'] ?? '';
$salidaFin = $_GET['salida_fin'] ?? '';
$idFamilia = (int)($_GET['id_familia'] ?? 0);
$idSubfamilia = (int)($_GET['id_subfamilia'] ?? 0);

$familias = json_decode($adminCatalogos->listarFamilias(true), true) ?: [];
$subfamilias = json_decode($adminCatalogos->listarSubfamilias($idFamilia > 0 ? $idFamilia : null, true), true) ?: [];

$queryInventario = http_build_query([
    'tipo' => 'inventario',
    'id_familia' => $idFamilia,
    'id_subfamilia' => $idSubfamilia,
]);
$queryEntradas = http_build_query([
    'tipo' => 'entradas',
    'entrada_inicio' => $entradaInicio,
    'entrada_fin' => $entradaFin,
]);
$querySalidas = http_build_query([
    'tipo' => 'salidas',
    'salida_inicio' => $salidaInicio,
    'salida_fin' => $salidaFin,
]);

$articulos = json_decode($adminArticulos->listarArticulosReporteGeneral(false, $idFamilia, $idSubfamilia), true) ?: [];
$proveedores = json_decode($adminProveedores->listarProveedores(false), true) ?: [];
$ordenesEntrada = json_decode($adminOrdenes->listarOrdenesCompra(null, $entradaInicio, $entradaFin), true) ?: [];
$ordenesSalida = json_decode($adminOrdenes->listarOrdenesSalida(null, $salidaInicio, $salidaFin), true) ?: [];
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
            <div class="col-md-3"><div class="card"><div class="card-body"><small class="text-muted d-block">Artículos</small><h3 class="mb-0"><?= count($articulos) ?></h3></div></div></div>
            <div class="col-md-3"><div class="card"><div class="card-body"><small class="text-muted d-block">Proveedores</small><h3 class="mb-0"><?= count($proveedores) ?></h3></div></div></div>
            <div class="col-md-3"><div class="card"><div class="card-body"><small class="text-muted d-block">Órdenes de entrada</small><h3 class="mb-0"><?= count($ordenesEntrada) ?></h3></div></div></div>
            <div class="col-md-3"><div class="card"><div class="card-body"><small class="text-muted d-block">Órdenes de salida</small><h3 class="mb-0"><?= count($ordenesSalida) ?></h3></div></div></div>
        </div>

        <div class="card mb-3">
            <div class="card-header">
                <h4 class="header-title mb-1">Rango de análisis para órdenes</h4>
                <p class="text-muted mb-0">Filtra por separado las órdenes de entrada y salida que se muestran y exportan en este reporte.</p>
            </div>
            <div class="card-body">
                <form method="get" action="reportes.php" class="row g-3 align-items-end">
                    <input type="hidden" name="id_familia" value="<?= $idFamilia ?>">
                    <input type="hidden" name="id_subfamilia" value="<?= $idSubfamilia ?>">
                    <div class="col-md-3">
                        <label class="form-label" for="entrada_inicio">Entrada desde</label>
                        <input type="date" class="form-control" id="entrada_inicio" name="entrada_inicio" value="<?= htmlspecialchars($entradaInicio) ?>">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label" for="entrada_fin">Entrada hasta</label>
                        <input type="date" class="form-control" id="entrada_fin" name="entrada_fin" value="<?= htmlspecialchars($entradaFin) ?>">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label" for="salida_inicio">Salida desde</label>
                        <input type="date" class="form-control" id="salida_inicio" name="salida_inicio" value="<?= htmlspecialchars($salidaInicio) ?>">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label" for="salida_fin">Salida hasta</label>
                        <input type="date" class="form-control" id="salida_fin" name="salida_fin" value="<?= htmlspecialchars($salidaFin) ?>">
                    </div>
                    <div class="col-md-auto">
                        <button type="submit" class="btn btn-primary">Aplicar filtros</button>
                    </div>
                    <div class="col-md-auto">
                        <a href="reportes.php" class="btn btn-outline-secondary">Limpiar</a>
                    </div>
                </form>
            </div>
        </div>

        <div class="card mb-3">
            <div class="card-header d-flex justify-content-between align-items-center">
                <div>
                    <h4 class="header-title mb-1">Inventario detallado</h4>
                    <p class="text-muted mb-0">Catálogo completo con existencias, entradas, salidas, movimientos y precio promedio de compra.</p>
                </div>
                <a class="btn btn-success btn-sm" href="reportes-exportar.php?<?= htmlspecialchars($queryInventario) ?>">Exportar Excel</a>
            </div>
            <div class="card-body">
                <form method="get" action="reportes.php" class="row g-3 align-items-end mb-3" id="filtroInventarioForm">
                    <input type="hidden" name="entrada_inicio" value="<?= htmlspecialchars($entradaInicio) ?>">
                    <input type="hidden" name="entrada_fin" value="<?= htmlspecialchars($entradaFin) ?>">
                    <input type="hidden" name="salida_inicio" value="<?= htmlspecialchars($salidaInicio) ?>">
                    <input type="hidden" name="salida_fin" value="<?= htmlspecialchars($salidaFin) ?>">
                    <div class="col-md-4">
                        <label class="form-label" for="id_familia">Familia</label>
                        <select class="form-select" id="id_familia" name="id_familia">
                            <option value="0">Todas las familias</option>
                            <?php foreach ($familias as $familia): ?>
                                <option value="<?= (int)$familia['id'] ?>" <?= $idFamilia === (int)$familia['id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($familia['nombre']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label" for="id_subfamilia">Subfamilia</label>
                        <select class="form-select" id="id_subfamilia" name="id_subfamilia">
                            <option value="0">Todas las subfamilias</option>
                            <?php foreach ($subfamilias as $subfamilia): ?>
                                <option value="<?= (int)$subfamilia['id'] ?>" <?= $idSubfamilia === (int)$subfamilia['id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($subfamilia['nombre']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-auto">
                        <button type="submit" class="btn btn-primary">Actualizar inventario</button>
                    </div>
                </form>

                <div class="table-responsive">
                    <table id="tablaInventario" class="table table-striped dt-responsive nowrap w-100 report-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>SKU</th>
                                <th>Artículo</th>
                                <th>Familia</th>
                                <th>Subfamilia</th>
                                <th>Descripción</th>
                                <th>Unidad</th>
                                <th>Existencia</th>
                                <th>Entradas</th>
                                <th>Salidas</th>
                                <th>Movimientos</th>
                                <th>Precio promedio</th>
                                <th>Estado</th>
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
                                <td><?= number_format((float)($articulo['total_entradas'] ?? 0), 0) ?></td>
                                <td><?= number_format((float)($articulo['total_salidas'] ?? 0), 0) ?></td>
                                <td><?= (int)($articulo['total_movimientos'] ?? 0) ?></td>
                                <td>$<?= number_format((float)($articulo['precio_promedio_compra'] ?? 0), 2) ?></td>
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
                    <p class="text-muted mb-0">Directorio de proveedores y condiciones de crédito.</p>
                </div>
                <a class="btn btn-success btn-sm" href="reportes-exportar.php?tipo=proveedores">Exportar Excel</a>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table id="tablaProveedores" class="table table-striped dt-responsive nowrap w-100 report-table">
                        <thead><tr><th>ID</th><th>Nombre</th><th>Contacto</th><th>Correo</th><th>RFC</th><th>Crédito</th><th>Plazo</th><th>Estado</th></tr></thead>
                        <tbody>
                        <?php foreach ($proveedores as $proveedor): ?>
                            <tr>
                                <td><?= $proveedor['id'] ?></td>
                                <td><?= htmlspecialchars($proveedor['nombre'] ?? '') ?></td>
                                <td><?= htmlspecialchars($proveedor['contacto'] ?? '') ?></td>
                                <td><?= htmlspecialchars($proveedor['mail'] ?? '') ?></td>
                                <td><?= htmlspecialchars($proveedor['rfc'] ?? '') ?></td>
                                <td><?= ((int)($proveedor['credito'] ?? 0) === 1) ? 'Sí' : 'No' ?></td>
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
                            <h4 class="header-title mb-1">Órdenes de entrada</h4>
                            <p class="text-muted mb-0">Seguimiento de compras registradas<?= ($entradaInicio || $entradaFin) ? ' en el rango seleccionado' : '' ?>.</p>
                        </div>
                        <a class="btn btn-success btn-sm" href="reportes-exportar.php?<?= htmlspecialchars($queryEntradas) ?>">Exportar Excel</a>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table id="tablaEntradas" class="table table-striped dt-responsive nowrap w-100 report-table">
                                <thead><tr><th>ID</th><th>Folio</th><th>Proveedor</th><th>Fecha</th><th>Estatus</th><th>Solicitó</th></tr></thead>
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
                            <h4 class="header-title mb-1">Órdenes de salida</h4>
                            <p class="text-muted mb-0">Seguimiento de movimientos de salida<?= ($salidaInicio || $salidaFin) ? ' en el rango seleccionado' : '' ?>.</p>
                        </div>
                        <a class="btn btn-success btn-sm" href="reportes-exportar.php?<?= htmlspecialchars($querySalidas) ?>">Exportar Excel</a>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table id="tablaSalidas" class="table table-striped dt-responsive nowrap w-100 report-table">
                                <thead><tr><th>ID</th><th>Folio</th><th>Fecha</th><th>Tipo</th><th>Estatus</th><th>Solicitó</th></tr></thead>
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

        document.getElementById('id_familia')?.addEventListener('change', function () {
            document.getElementById('id_subfamilia').value = '0';
            document.getElementById('filtroInventarioForm').submit();
        });

        document.getElementById('id_subfamilia')?.addEventListener('change', function () {
            document.getElementById('filtroInventarioForm').submit();
        });
    });
</script>
</body>
</html>
