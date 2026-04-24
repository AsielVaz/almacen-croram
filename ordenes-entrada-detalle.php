<?php
require_once __DIR__ . '/auth.php';
requerir_autenticacion();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <title>Almac&eacute;n Croram - &Oacute;rdenes</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta content="A fully featured admin theme which can be used to build CRM, CMS, etc." name="description" />
    <meta content="Coderthemes" name="author" />

    <!-- App favicon -->
    <link rel="shortcut icon" href="favicon.png">

    <!-- Vendor css -->
    <link href="assets/css/vendor.min.css" rel="stylesheet" type="text/css" />

    <!-- App css -->
    <link href="assets/css/app.min.css" rel="stylesheet" type="text/css" id="app-style" />

    <!-- Icons css -->
    <link href="assets/css/icons.min.css" rel="stylesheet" type="text/css" />

    <!-- Theme Config Js -->
    <script src="assets/js/config.js"></script>

    <!-- Datatables css -->
    <link href="assets/vendor/datatables.net-bs5/css/dataTables.bootstrap5.min.css" rel="stylesheet" type="text/css" />
    <link href="assets/vendor/datatables.net-responsive-bs5/css/responsive.bootstrap5.min.css" rel="stylesheet"
        type="text/css" />
    <link href="assets/vendor/datatables.net-fixedcolumns-bs5/css/fixedColumns.bootstrap5.min.css" rel="stylesheet"
        type="text/css" />
    <link href="assets/vendor/datatables.net-fixedheader-bs5/css/fixedHeader.bootstrap5.min.css" rel="stylesheet"
        type="text/css" />
    <link href="assets/vendor/datatables.net-buttons-bs5/css/buttons.bootstrap5.min.css" rel="stylesheet"
        type="text/css" />
    <link href="assets/vendor/datatables.net-select-bs5/css/select.bootstrap5.min.css" rel="stylesheet"
        type="text/css" />

    <style>
        .order-card {
            border: 1px solid #e0e0e0;
            box-shadow: 0 0.125rem 0.5rem rgba(0, 0, 0, 0.05);
            border-radius: 0.5rem;
            overflow: hidden;
        }

        .order-header {
            background: linear-gradient(135deg, #4a5568 0%, #2d3748 100%);
            color: white;
            padding: 2rem;
            border-radius: 0.5rem 0.5rem 0 0;
        }

        .order-info-card {
            background: #f8f9fa;
            border-radius: 0.5rem;
            padding: 1.5rem;
            box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.05);
            border: 1px solid #e0e0e0;
        }

        .status-badge {
            font-size: 0.875rem;
            padding: 0.5rem 1rem;
            border-radius: 0.25rem;
            font-weight: 600;
            text-transform: uppercase;
            border: 1px solid;
        }

        .status-PENDIENTE {
            background-color: #f8f9fa;
            color: #495057;
            border-color: #dee2e6;
        }

        .status-AUTORIZADA {
            background-color: #e9ecef;
            color: #495057;
            border-color: #adb5bd;
        }

        .status-CANCELADA {
            background-color: #6c757d;
            color: #fff;
            border-color: #495057;
        }

        .status-RECIBIDA {
            background-color: #343a40;
            color: #fff;
            border-color: #212529;
        }

        .info-label {
            font-size: 0.875rem;
            color: #6c757d;
            font-weight: 500;
            margin-bottom: 0.25rem;
        }

        .info-value {
            font-size: 1.25rem;
            font-weight: 600;
            color: #212529;
        }

        .table-modern {
            border-radius: 0.5rem;
            overflow: hidden;
            border: 1px solid #e0e0e0;
        }

        .table-modern thead {
            background: #495057;
            color: white;
        }

        .table-modern thead th {
            border: none;
            padding: 1rem;
            font-weight: 600;
            text-transform: uppercase;
            font-size: 0.875rem;
            letter-spacing: 0.5px;
        }

        .table-modern tbody tr {
            transition: all 0.3s ease;
        }

        .table-modern tbody tr:hover {
            background-color: #f8f9fa;
            transform: scale(1.005);
        }

        .table-modern tbody td {
            padding: 1rem;
            vertical-align: middle;
            border-color: #e0e0e0;
        }

        .total-card {
            background: linear-gradient(135deg, #495057 0%, #343a40 100%);
            color: white;
            border-radius: 0.5rem;
            padding: 1.5rem;
            box-shadow: 0 0.25rem 0.75rem rgba(0, 0, 0, 0.1);
        }

        .total-label {
            font-size: 1rem;
            opacity: 0.9;
            margin-bottom: 0.5rem;
        }

        .total-amount {
            font-size: 2rem;
            font-weight: 700;
            letter-spacing: -0.5px;
        }

        .btn-modern {
            padding: 0.75rem 2rem;
            border-radius: 0.375rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            transition: all 0.3s ease;
            border: none;
        }

        .btn-primary-modern {
            background: #495057;
            color: white;
            border: 2px solid #343a40;
        }

        .btn-primary-modern:hover {
            background: #343a40;
            transform: translateY(-2px);
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
        }

        .iframe-card {
            border: 1px solid #e0e0e0;
            box-shadow: 0 0.125rem 0.5rem rgba(0, 0, 0, 0.05);
            border-radius: 0.5rem;
        }

        .iframe-card .card-header {
            background: #495057;
            color: white;
            border: none;
            padding: 1rem 1.5rem;
        }

        .modal-header-custom {
            background: linear-gradient(135deg, #495057 0%, #343a40 100%);
            color: white;
            border-radius: 0.5rem 0.5rem 0 0;
        }

        .modal-header-custom .btn-close {
            filter: brightness(0) invert(1);
        }

        .price-input-group {
            position: relative;
        }

        .price-input {
            padding-left: 2rem;
            border: 2px solid #dee2e6;
            border-radius: 0.375rem;
            transition: all 0.3s ease;
        }

        .price-input:focus {
            border-color: #6c757d;
            box-shadow: 0 0 0 0.2rem rgba(108, 117, 125, 0.15);
        }

        .currency-symbol {
            position: absolute;
            left: 0.75rem;
            top: 50%;
            transform: translateY(-50%);
            color: #6c757d;
            font-weight: 600;
        }

        .product-row {
            transition: all 0.3s ease;
            border-radius: 0.375rem;
            padding: 0.5rem;
        }

        .product-row:hover {
            background-color: #f8f9fa;
        }

        .divider {
            height: 1px;
            background: linear-gradient(to right, transparent, #dee2e6, transparent);
            margin: 2rem 0;
        }

        .badge {
            border-radius: 0.25rem;
        }

        #notasOrden {
            transition: all 0.3s ease;
        }

        #notasOrden:focus {
            border-color: #6c757d;
            box-shadow: 0 0 0 0.2rem rgba(108, 117, 125, 0.15);
        }
    </style>
</head>

<body>
    <!-- Begin page -->
    <div class="wrapper">

        <!-- Menu -->
        <!-- Sidenav Menu Start -->
        <?php include_once 'templates/barra.php' ?>
        <!-- Sidenav Menu End -->

        <!-- Topbar Start -->
        <?php include_once 'templates/headder.php' ?>
        <!-- Topbar End -->
        <?php
        include_once 'api/adminOrdenes.php';
        $adminOrdenes = new AdministradorOrdenes();
        $ordenes = json_decode($adminOrdenes->obtenerOrdenCompra($_GET['id'] ?? 0));
        $detallesOrden = json_decode($adminOrdenes->listarDetallesOrden($_GET['id'] ?? 0));
        ?>
        <!-- Search Modal -->
        <div class="modal fade" id="searchModal" tabindex="-1" aria-labelledby="searchModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content bg-transparent">
                    <form>
                        <div class="card mb-1">
                            <div class="px-3 py-2 d-flex flex-row align-items-center" id="top-search">
                                <i class="ri-search-line fs-22"></i>
                                <input type="search" class="form-control border-0" id="search-modal-input"
                                    placeholder="Search for actions, people,">
                                <button type="submit" class="btn p-0" data-bs-dismiss="modal" aria-label="Close">[esc]</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Modal Ingresar Orden -->
        <div class="modal fade" id="modalIngresarOrden" tabindex="-1" aria-labelledby="modalIngresarOrdenLabel" aria-hidden="true">
            <div class="modal-dialog modal-xl">
                <div class="modal-content">
                    <div class="modal-header modal-header-custom">
                        <h5 class="modal-title" id="modalIngresarOrdenLabel">
                            <i class="ri-file-list-3-line me-2"></i>Ingresar Orden de Entrada
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="alert alert-light border d-flex align-items-center" role="alert">
                            <i class="ri-information-line fs-20 me-2 text-dark"></i>
                            <div>
                                Revise y ajuste los precios reales de compra para cada producto. Por defecto se muestran los precios originales de la orden.
                            </div>
                        </div>

                        <div class="table-responsive">
                            <table class="table table-modern" id="tablaProductosModal">
                                <thead>
                                    <tr>
                                        <th width="5%">#</th>
                                        <th width="35%">Producto</th>
                                        <th width="10%">Cantidad</th>
                                        <th width="20%">Precio Original</th>
                                        <th width="25%">Precio Real Comprado</th>
                                        <th width="5%"></th>
                                    </tr>
                                </thead>
                                <tbody id="productosModalBody">
                                    <?php
                                    foreach ($detallesOrden as $index => $detalle):
                                    ?>
                                        <tr class="product-row" data-detalle-id="<?= $detalle->id_detalle ?? $index ?>">
                                            <td class="fw-bold text-center"><?= $index + 1 ?></td>
                                            <td>
                                                <div class="fw-bold"><?= $detalle->nombre_producto ?></div>
                                                <small class="text-muted">ID: <?= $detalle->id_producto ?? 'N/A' ?></small>
                                            </td>
                                            <td class="text-center">
                                                <span class="badge bg-secondary"><?= (int)round((float)$detalle->cantidad) ?> unidades</span>
                                            </td>
                                            <td>
                                                <div class="fw-bold text-dark">$<?= number_format($detalle->precio_unitario, 2) ?></div>
                                                <small class="text-muted">Precio original</small>
                                            </td>
                                            <td>
                                                <div class="price-input-group">
                                                    <span class="currency-symbol">$</span>
                                                    <input 
                                                        type="number" 
                                                        step="0.01" 
                                                        min="0"
                                                        class="form-control price-input precio-real" 
                                                        value="<?= number_format($detalle->precio_unitario, 2, '.', '') ?>"
                                                        data-precio-original="<?= $detalle->precio_unitario ?>"
                                                        data-id-producto="<?= $detalle->id_producto ?? '' ?>"
                                                        data-nombre-producto="<?= htmlspecialchars($detalle->nombre_producto) ?>"
                                                        data-cantidad="<?= (int)round((float)$detalle->cantidad) ?>"
                                                    >
                                                </div>
                                            </td>
                                            <td class="text-center">
                                                <i class="ri-edit-line text-secondary fs-18"></i>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>

                        <!-- Área de notas -->
                        <div class="mt-4">
                            <label for="notasOrden" class="form-label fw-bold">
                                <i class="ri-file-text-line me-2"></i>Notas de la Orden
                            </label>
                            <textarea 
                                class="form-control" 
                                id="notasOrden" 
                                rows="4" 
                                placeholder="Ingrese observaciones, comentarios o detalles adicionales sobre esta orden de entrada..."
                                style="border: 2px solid #dee2e6; border-radius: 0.375rem;"
                            ></textarea>
                            <small class="text-muted">
                                <i class="ri-information-line"></i> Estas notas se guardarán junto con la orden de entrada
                            </small>
                        </div>

                        <div class="divider"></div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="alert alert-light border">
                                    <strong>Total de productos:</strong> <?= count($detallesOrden) ?>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="alert alert-light border">
                                    <strong>Orden ID:</strong> <?= $_GET['id'] ?? 0 ?>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer bg-light">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                            <i class="ri-close-line me-1"></i>Cancelar
                        </button>
                        <button type="button" class="btn btn-primary-modern btn-modern" id="btnGuardarOrdenEntrada">
                            <i class="ri-save-line me-1"></i>Guardar Orden de Entrada
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- ============================================================== -->
        <!-- Start Page Content here -->
        <!-- ============================================================== -->

        <div class="page-content">

            <div class="page-title-head d-flex align-items-center gap-2 mb-4">
                <div class="flex-grow-1">
                    <h4 class="fs-18 fw-bold mb-0">
                                            <i class="ri-file-list-3-line me-2"></i>&Oacute;rdenes de compra
                    </h4>
                </div>

                <div class="text-end">
                    <ol class="breadcrumb m-0 py-0 fs-13">
                        <li class="breadcrumb-item"><a href="javascript: void(0);">Catálogos</a></li>
                                    <li class="breadcrumb-item active">&Oacute;rdenes</li>
                    </ol>
                </div>
            </div>

            <div class="page-container">

                <div class="row">
                    <div class="col-12">

                        <!-- Card Orden -->
                        <div class="card order-card mb-4">
                            <div class="order-header">
                                <div class="row align-items-center">
                                    <div class="col-md-8">
                                        <h2 class="mb-3 fw-bold">
                                            <i class="ri-file-text-line me-2"></i>Orden de Compra #<?php echo $ordenes[0]->folio ?? 'N/A'; ?>
                                        </h2>
                                        <div class="d-flex align-items-center gap-3">
                                            <span class="status-badge status-<?php echo $ordenes[0]->estatus ?? 'PENDIENTE'; ?>">
                                                <?php echo $ordenes[0]->estatus ?? 'PENDIENTE'; ?>
                                            </span>
                                            <?php if ($ordenes[0]->estatus == "RECIBIDA"){ $activo = true;} ?>
                                            <select name="estatus" id="estatus" class="form-select w-auto bg-white">
                                                <option value="PENDIENTE" <?php if (($ordenes[0]->estatus ?? '') === 'PENDIENTE') echo 'selected'; ?>>PENDIENTE</option>
                                                <option value="AUTORIZADA" <?php if (($ordenes[0]->estatus ?? '') === 'AUTORIZADA') echo 'selected'; ?>>AUTORIZADA</option>
                                                <option value="CANCELADA" <?php if (($ordenes[0]->estatus ?? '') === 'CANCELADA') echo 'selected'; ?>>CANCELADA</option>
                                                <option value="RECIBIDA" <?php if (($ordenes[0]->estatus ?? '') === 'RECIBIDA') echo 'selected'; ?>>RECIBIDA</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-4 text-end">
                                        <div class="order-info-card">
                                            <div class="mb-3">
                                                <div class="info-label">Fecha de Orden</div>
                                                <div class="info-value text-dark">
                                                    <i class="ri-calendar-line me-1"></i><?php echo $ordenes[0]->fecha_orden ?? 'N/A'; ?>
                                                </div>
                                            </div>
                                            <div>
                                                <div class="info-label">Proveedor</div>
                                                <div class="info-value text-dark">
                                                    <i class="ri-building-line me-1"></i><?php echo $ordenes[0]->nombre_proveedor ?? 'N/A'; ?>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="card-body p-4">
                                <?php if (!empty($ordenes[0]->nota ?? '')): ?>
                                    <div class="alert alert-light border mb-4">
                                        <div class="fw-bold mb-2"><i class="ri-sticky-note-line me-2"></i>Notas de entrada</div>
                                        <div class="text-muted"><?= nl2br(htmlspecialchars($ordenes[0]->nota)) ?></div>
                                    </div>
                                <?php endif; ?>

                                <h5 class="mb-4 fw-bold">
                                    <i class="ri-shopping-cart-line me-2"></i>Detalles de Productos
                                </h5>

                                <!-- Tabla de productos -->
                                <div class="table-responsive">
                                    <table class="table table-modern">
                                        <thead>
                                            <tr>
                                                <th width="5%">#</th>
                                                <th width="45%">Producto</th>
                                                <th width="15%" class="text-center">Cantidad</th>
                                                <th width="15%" class="text-end">Precio Unitario</th>
                                                <th width="20%" class="text-end">Subtotal</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php
                                            $total = 0;
                                            foreach ($detallesOrden as $index => $detalle):
                                                $total += $detalle->subtotal;
                                            ?>
                                                <tr>
                                                    <td class="fw-bold text-center"><?= $index + 1 ?></td>
                                                    <td>
                                                        <div class="fw-bold"><?= $detalle->nombre_producto ?></div>
                                                    </td>
                                                    <td class="text-center">
                                                        <span class="badge bg-secondary"><?= (int)round((float)$detalle->cantidad) ?></span>
                                                    </td>
                                                    <td class="text-end fw-bold">$<?= number_format($detalle->precio_unitario, 2) ?></td>
                                                    <td class="text-end fw-bold text-dark">$<?= number_format($detalle->subtotal, 2) ?></td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>

                                <!-- Totales -->
                                <div class="row justify-content-end mt-4">
                                    <div class="col-md-4">
                                        <div class="total-card">
                                            <div class="total-label">TOTAL DE LA ORDEN</div>
                                            <div class="total-amount">$<?= number_format($total, 2) ?></div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Botón Ingresar Orden -->
                                <div class="row mt-4">
                                    <div class="col-12 text-center">
                                        <button type="button" class="btn btn-primary-modern btn-modern" data-bs-toggle="modal" data-bs-target="#modalIngresarOrden">
                                            <i class="ri-download-cloud-line me-2"></i>Ingresar Orden
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Iframe inferior -->
                        <div class="card iframe-card">
                            <div class="card-header">
                                <h5 class="mb-0 fw-bold">
                                    <i class="ri-file-pdf-line me-2"></i>Vista Adicional - PDF
                                </h5>
                            </div>
                            <div class="card-body p-0">
                                <iframe
                                    src="api/generaOrdenSalidaPdf.php?id=<?php echo $_GET['id'] ?? 0; ?>"
                                    width="100%"
                                    height="600"
                                    frameborder="0"
                                    style="border:0; border-radius: 0 0 0.5rem 0.5rem;"
                                    allowfullscreen>
                                </iframe>
                            </div>
                        </div>

                    </div>
                </div>

            </div> <!-- container -->

            <!-- Footer Start -->
            <?php include 'templates/footer.php'; ?>
            <!-- end Footer -->

        </div>

        <!-- ============================================================== -->
        <!-- End Page content -->
        <!-- ============================================================== -->

    </div>
    <!-- END wrapper -->

    <!-- Theme Settings -->
    <?php include_once 'templates/theme.php' ?>

    <!-- Vendor js -->
    <script src="assets/js/vendor.min.js"></script>

    <!-- App js -->
    <script src="assets/js/app.js"></script>

    <!-- Apex Chart js -->
    <script src="assets/vendor/apexcharts/apexcharts.min.js"></script>

    <!-- Projects Analytics Dashboard App js -->
    <script src="assets/js/pages/dashboard.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <!-- Datatables js -->
    <script src="assets/vendor/datatables.net/js/jquery.dataTables.min.js"></script>
    <script src="assets/vendor/datatables.net-bs5/js/dataTables.bootstrap5.min.js"></script>
    <script src="assets/vendor/datatables.net-responsive/js/dataTables.responsive.min.js"></script>
    <script src="assets/vendor/datatables.net-responsive-bs5/js/responsive.bootstrap5.min.js"></script>
    <script src="assets/vendor/datatables.net-fixedcolumns-bs5/js/fixedColumns.bootstrap5.min.js"></script>
    <script src="assets/vendor/datatables.net-fixedheader/js/dataTables.fixedHeader.min.js"></script>
    <script src="assets/vendor/datatables.net-buttons/js/dataTables.buttons.min.js"></script>
    <script src="assets/vendor/datatables.net-buttons-bs5/js/buttons.bootstrap5.min.js"></script>
    <script src="assets/vendor/datatables.net-buttons/js/buttons.html5.min.js"></script>
    <script src="assets/vendor/datatables.net-buttons/js/buttons.flash.min.js"></script>
    <script src="assets/vendor/datatables.net-buttons/js/buttons.print.min.js"></script>
    <script src="assets/vendor/datatables.net-keytable/js/dataTables.keyTable.min.js"></script>
    <script src="assets/vendor/datatables.net-select/js/dataTables.select.min.js"></script>
    <script src="assets/js/components/table-datatable.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Botón Guardar Orden de Entrada
            const btnGuardarOrden = document.getElementById('btnGuardarOrdenEntrada');
            
            if (btnGuardarOrden) {
                btnGuardarOrden.addEventListener('click', function() {
                    // Obtener el ID de la orden
                    const urlParams = new URLSearchParams(window.location.search);
                    const idOrden = urlParams.get('id') || 0;
                    
                    // Obtener las notas
                    const notas = document.getElementById('notasOrden').value.trim();
                    
                    // Recopilar los productos con sus precios
                    const productos = [];
                    const inputsPrecios = document.querySelectorAll('.precio-real');
                    
                    inputsPrecios.forEach(function(input) {
                        const precioOriginal = parseFloat(input.dataset.precioOriginal);
                        const precioReal = parseFloat(input.value) || 0;
                        const idProducto = input.dataset.idProducto;
                        const nombreProducto = input.dataset.nombreProducto;
                        const cantidad = parseInt(input.dataset.cantidad, 10) || 0;
                        
                        productos.push({
                            id_producto: idProducto,
                            nombre_producto: nombreProducto,
                            cantidad: cantidad,
                            precio_original: precioOriginal,
                            precio_real: precioReal
                        });
                    });
                    
                    const formData = new FormData();
                    formData.append('accion', 'guardarOrdenEntrada');
                    formData.append('id_orden', idOrden);
                    formData.append('productos', JSON.stringify(productos));
                    formData.append('nota', notas);
                    
                    // Mostrar loading
                    Swal.fire({
                        title: 'Procesando...',
                        text: 'Guardando orden de entrada',
                        allowOutsideClick: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });
                    
                    // Enviar por POST
                    fetch('api/apiOrdenes.php', {
                        method: 'POST',
                        body: formData
                    })
                    .then(response => response.json())
                    .then(data => {
                        Swal.close();
                        
                        if (data.success || data.status === 'success') {
                            Swal.fire({
                                icon: 'success',
                                title: '&iexcl;&Eacute;xito!',
                                text: data.message || 'Orden de entrada guardada correctamente',
                                confirmButtonColor: '#495057'
                            }).then(() => {
                                // Cerrar modal
                                const modal = bootstrap.Modal.getInstance(document.getElementById('modalIngresarOrden'));
                                if (modal) {
                                    modal.hide();
                                }
                                // Limpiar el campo de notas
                                document.getElementById('notasOrden').value = '';
                                // Recargar página
                                location.reload();
                            });
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: data.message || 'Error al guardar la orden de entrada',
                                confirmButtonColor: '#6c757d'
                            });
                        }
                    })
                    .catch(error => {
                        Swal.close();
                        console.error('Error:', error);
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'Ocurrió un error al procesar la solicitud',
                            confirmButtonColor: '#6c757d'
                        });
                    });
                });
            }

            // Validación en tiempo real de los inputs de precio
            const preciosInputs = document.querySelectorAll('.precio-real');
            preciosInputs.forEach(input => {
                input.addEventListener('input', function() {
                    if (this.value < 0) {
                        this.value = 0;
                    }
                });
            });

            // Limpiar campo de notas cuando se cierra el modal
            const modalIngresarOrden = document.getElementById('modalIngresarOrden');
            if (modalIngresarOrden) {
                modalIngresarOrden.addEventListener('hidden.bs.modal', function () {
                    document.getElementById('notasOrden').value = '';
                });
            }
        });
    </script>

</body>

</html>
