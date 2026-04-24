<?php
require_once __DIR__ . '/auth.php';
requerir_autenticacion();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <title>Almacén Croram - Conciliación Total</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta content="Conciliación total de inventario" name="description" />
    <meta content="HoppingJet Studio." name="author" />

    <link rel="shortcut icon" href="assets/images/favicon.ico">

    <!-- Vendor css -->
    <link href="assets/css/vendor.min.css" rel="stylesheet" type="text/css" />
    <link href="assets/css/app.min.css" rel="stylesheet" type="text/css" />
    <link href="assets/css/icons.min.css" rel="stylesheet" type="text/css" />

    <script src="assets/js/config.js"></script>

    <!-- Datatables css -->
    <link href="assets/vendor/datatables.net-bs5/css/dataTables.bootstrap5.min.css" rel="stylesheet" />
    <link href="assets/vendor/datatables.net-responsive-bs5/css/responsive.bootstrap5.min.css" rel="stylesheet" />
</head>

<body>
<div class="wrapper">

<?php
include_once 'templates/barra.php';
include_once 'templates/headder.php';

include_once 'api/adminArticulos.php';
$adminArticulos = new AdministradorArticulos();

/* =============================
   OBTENER TODOS LOS ARTÃCULOS
============================= */
$articulos = json_decode($adminArticulos->listarArticulos(false), true);
?>

<div class="page-content">

<div class="page-title-head d-flex align-items-center gap-2">
    <div class="flex-grow-1">
        <h4 class="fs-18 fw-bold mb-0">Conciliación Total de Inventario</h4>
    </div>
    <div class="text-end">
        <ol class="breadcrumb m-0 py-0 fs-13">
            <li class="breadcrumb-item">Inventario</li>
            <li class="breadcrumb-item active">Conciliación Total</li>
        </ol>
    </div>
</div>

<div class="page-container">

<div class="card">
<div class="card-body">

<h4 class="header-title mb-3">
    Conciliación del 100 % de los artículos
</h4>

<table id="tablaConciliacion" class="table table-striped dt-responsive nowrap w-100">
    <thead>
        <tr>
            <th>ID</th>
            <th>Artículo</th>
            <th>SKU</th>
            <th>Existencias sistema</th>
            <th>Existencias reales</th>
            <th>Diferencia</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($articulos as $articulo):
            $cantidad = (int)round((float)($articulo['cantidad'] ?? 0));
        ?>
        <tr data-id="<?php echo $articulo['id']; ?>">
            <td><?php echo $articulo['id']; ?></td>
            <td><?php echo htmlspecialchars($articulo['nombre']); ?></td>
            <td><?php echo htmlspecialchars($articulo['sku']); ?></td>
            <td class="cantidad-sistema"><?php echo $cantidad; ?></td>
            <td>
                <input type="number"
                       class="form-control form-control-sm cantidad-real"
                       min="0"
                       value="<?php echo $cantidad; ?>">
            </td>
            <td class="diferencia">0</td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<div class="text-end mt-3">
    <button class="btn btn-success" id="btnTerminar">
        <i class="fa fa-check"></i> Terminar conciliación
    </button>
</div>

</div>
</div>

</div>

<?php include 'templates/footer.php'; ?>
</div>
</div>

<?php include_once 'templates/theme.php'; ?>

<!-- JS -->
<script src="assets/js/vendor.min.js"></script>
<script src="assets/js/app.js"></script>

<script src="assets/vendor/datatables.net/js/jquery.dataTables.min.js"></script>
<script src="assets/vendor/datatables.net-bs5/js/dataTables.bootstrap5.min.js"></script>
<script src="assets/vendor/datatables.net-responsive/js/dataTables.responsive.min.js"></script>
<script src="assets/vendor/datatables.net-responsive-bs5/js/responsive.bootstrap5.min.js"></script>

<script>
/* =============================
   DATATABLE
============================= */
$('#tablaConciliacion').DataTable({
    pageLength: 25,
    responsive: true,
    ordering: false
});

/* =============================
   CALCULAR DIFERENCIAS
============================= */
document.querySelectorAll('.cantidad-real').forEach(input => {
    input.addEventListener('input', () => {
        const row = input.closest('tr');
        const sistema = parseFloat(row.querySelector('.cantidad-sistema').textContent);
        const real = parseFloat(input.value || 0);
        row.querySelector('.diferencia').textContent = real - sistema;
    });
});

/* =============================
   ENVIAR CONCILIACIÃ“N
============================= */
document.getElementById('btnTerminar').addEventListener('click', () => {
    const conciliacion = [];

    document.querySelectorAll('#tablaConciliacion tbody tr').forEach(row => {
        conciliacion.push({
            id_articulo: row.dataset.id,
            sistema: parseFloat(row.querySelector('.cantidad-sistema').textContent),
            real: parseFloat(row.querySelector('.cantidad-real').value || 0),
            diferencia: parseFloat(row.querySelector('.diferencia').textContent)
        });
    });

    fetch('api/apiConciliacion.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({
            accion: 'guardarConciliacionTotal',
            conciliacion: conciliacion
        })
    })
    .then(r => r.json())
    .then(() => {
        alert('Conciliación total registrada correctamente');
        location.reload();
    })
    .catch(() => alert('Error al guardar conciliación'));
});
</script>

</body>
</html>

