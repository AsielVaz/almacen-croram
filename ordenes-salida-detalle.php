<?php
require_once __DIR__ . '/auth.php';
requerir_autenticacion();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <title>Almacén Croram - Orden de Salida</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta content="A fully featured admin theme which can be used to build CRM, CMS, etc." name="description" />
    <meta content="Coderthemes" name="author" />

    <!-- App favicon -->
    <link rel="shortcut icon" href="assets/images/favicon.ico">

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
        body {
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
        }

        .order-card {
            border: 1px solid #e9ecef;
            box-shadow: 0 0.125rem 0.5rem rgba(0, 0, 0, 0.05);
            border-radius: 0.5rem;
            overflow: hidden;
            background: white;
        }

        .order-header {
            background: white;
            color: #212529;
            padding: 2rem;
            border-radius: 0.5rem 0.5rem 0 0;
            border-left: 6px solid #dc3545;
        }

        .order-info-card {
            background: #f8f9fa;
            border-radius: 0.5rem;
            padding: 1.5rem;
            box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.05);
            border: 1px solid #e9ecef;
        }

        .status-badge {
            font-size: 0.875rem;
            padding: 0.5rem 1rem;
            border-radius: 0.25rem;
            font-weight: 600;
            text-transform: uppercase;
            border: 1px solid;
        }

        .status-BORRADOR {
            background-color: #f8f9fa;
            color: #495057;
            border-color: #dee2e6;
        }

        .status-CONFIRMADA {
            background-color: #343a40;
            color: #fff;
            border-color: #212529;
        }

        .status-CANCELADA {
            background-color: #6c757d;
            color: #fff;
            border-color: #495057;
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
            border: 1px solid #e9ecef;
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
            border-color: #e9ecef;
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

        .btn-approve {
            background: linear-gradient(135deg, #28a745 0%, #218838 100%);
            color: white;
            border: 2px solid #1e7e34;
        }

        .btn-approve:hover {
            background: linear-gradient(135deg, #218838 0%, #1e7e34 100%);
            transform: translateY(-2px);
            box-shadow: 0 0.5rem 1rem rgba(40, 167, 69, 0.3);
        }

        .btn-cancel {
            background: #6c757d;
            color: white;
            border: 2px solid #5a6268;
        }

        .btn-cancel:hover {
            background: #5a6268;
            transform: translateY(-2px);
            box-shadow: 0 0.5rem 1rem rgba(108, 117, 125, 0.2);
        }

        .iframe-card {
            border: 1px solid #e9ecef;
            box-shadow: 0 0.125rem 0.5rem rgba(0, 0, 0, 0.05);
            border-radius: 0.5rem;
            background: white;
        }

        .iframe-card .card-header {
            background: #495057;
            color: white;
            border: none;
            padding: 1rem 1.5rem;
        }

        .divider {
            height: 1px;
            background: linear-gradient(to right, transparent, #e9ecef, transparent);
            margin: 2rem 0;
        }

        .page-header-custom {
            background: white;
            color: #212529;
            padding: 1.5rem;
            border-radius: 0.5rem;
            margin-bottom: 2rem;
            box-shadow: 0 0.125rem 0.5rem rgba(0, 0, 0, 0.05);
            border: 1px solid #e9ecef;
            border-left: 6px solid #dc3545;
        }

        .page-header-custom h4 {
            margin: 0;
            font-weight: 700;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .alert-warning-custom {
            background: #fff3cd;
            border: 1px solid #ffc107;
            border-left: 4px solid #ffc107;
            padding: 1rem;
            border-radius: 0.375rem;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .alert-warning-custom i {
            color: #ffc107;
            font-size: 1.5rem;
        }

        .alert-warning-custom-text {
            flex: 1;
            color: #856404;
        }

        .action-buttons {
            display: flex;
            gap: 1rem;
            justify-content: center;
            margin-top: 2rem;
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
        $ordenes = json_decode($adminOrdenes->obtenerOrdenSalida($_GET['id'] ?? 0));
        $detallesOrden = json_decode($adminOrdenes->listarDetallesOrdenSalida($_GET['id'] ?? 0));
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

        <!-- ============================================================== -->
        <!-- Start Page Content here -->
        <!-- ============================================================== -->

        <div class="page-content">

            <div class="page-header-custom">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h4>
                            <i class="ri-logout-box-line"></i>
                            Orden de Salida
                        </h4>
                    </div>
                    <div class="text-end">
                        <ol class="breadcrumb m-0 py-0 fs-13">
                            <li class="breadcrumb-item"><a href="javascript: void(0);">Catálogos</a></li>
                        <li class="breadcrumb-item active">Órdenes de salida</li>
                        </ol>
                    </div>
                </div>
            </div>

            <div class="page-container">

                <div class="row">
                    <div class="col-12">

                        <!-- Alerta de advertencia -->
                        <div class="alert-warning-custom">
                            <i class="ri-alert-line"></i>
                            <div class="alert-warning-custom-text">
                                <strong>Atención:</strong> Al aprobar esta orden, los productos serán descontados del inventario.
                            </div>
                        </div>

                        <!-- Card Orden -->
                        <div class="card order-card mb-4">
                            <div class="order-header">
                                <div class="row align-items-center">
                                    <div class="col-md-8">
                                        <h2 class="mb-3 fw-bold">
                                            <i class="ri-file-text-line me-2"></i>Orden de Salida #<?php echo $ordenes[0]->folio ?? 'N/A'; ?>
                                        </h2>
                                        <div class="d-flex align-items-center gap-3">
                                            <span class="status-badge status-<?php echo $ordenes[0]->estatus ?? 'BORRADOR'; ?>">
                                                <?php echo $ordenes[0]->estatus ?? 'BORRADOR'; ?>
                                            </span>
                                            <select name="estatus" id="estatus" class="form-select w-auto bg-white">
                                                <option value="BORRADOR" <?php if (($ordenes[0]->estatus ?? '') === 'BORRADOR') echo 'selected'; ?>>BORRADOR</option>
                                                <option value="CONFIRMADA" <?php if (($ordenes[0]->estatus ?? '') === 'CONFIRMADA') echo 'selected'; ?>>CONFIRMADA</option>
                                                <option value="CANCELADA" <?php if (($ordenes[0]->estatus ?? '') === 'CANCELADA') echo 'selected'; ?>>CANCELADA</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-4 text-end">
                                        <div class="order-info-card">
                                            <div class="info-label">Fecha de Salida</div>
                                            <div class="info-value text-dark">
                                                <i class="ri-calendar-line me-1"></i><?php echo $ordenes[0]->fecha_salida ?? 'N/A'; ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="card-body p-4">
                                <h5 class="mb-4 fw-bold">
                                    <i class="ri-shopping-cart-line me-2"></i>Productos de la Orden
                                </h5>

                                <!-- Tabla de productos -->
                                <div class="table-responsive">
                                    <table class="table table-modern">
                                        <thead>
                                            <tr>
                                                <th width="10%">#</th>
                                                <th width="60%">Producto</th>
                                                <th width="30%" class="text-center">Cantidad</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php
                                            $total = 0;
                                            foreach ($detallesOrden as $index => $detalle):
                        $total += (int)round((float)($detalle->cantidad ?? 0));
                                            ?>
                                                <tr>
                                                    <td class="fw-bold text-center"><?= $index + 1 ?></td>
                                                    <td>
                                                        <div class="fw-bold"><?= $detalle->nombre_producto ?></div>
                                                    </td>
                                                    <td class="text-center">
                                                <span class="badge bg-secondary"><?= (int)round((float)$detalle->cantidad) ?> unidades</span>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                        <tfoot>
                                            <tr>
                                                <td colspan="2" class="text-end fw-bold">TOTAL DE UNIDADES:</td>
                                                <td class="text-center">
                                                    <span class="badge bg-dark" style="font-size: 1rem; padding: 0.5rem 1rem;">
                                                        <?= $total ?> unidades
                                                    </span>
                                                </td>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>

                                <!-- Botones de acción -->
                                <div class="action-buttons">
                                    <button type="button" class="btn btn-modern btn-approve" id="btnAprobar">
                                        <i class="ri-check-line me-2"></i>Aprobar Orden
                                    </button>
                                    <button type="button" class="btn btn-modern btn-cancel" onclick="history.back()">
                                        <i class="ri-arrow-left-line me-2"></i>Regresar
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Iframe inferior -->
                        <div class="card iframe-card">
                            <div class="card-header">
                                <h5 class="mb-0 fw-bold">
                                    <i class="ri-global-line me-2"></i>Vista Adicional
                                </h5>
                            </div>
                            <div class="card-body p-0">
                                <iframe
                                    src="api/generaOrdenSalidaInventarioPdf.php?id=<?php echo $_GET['id'] ?? 0; ?>"
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
            // Botón Aprobar Orden
            const btnAprobar = document.getElementById('btnAprobar');
            
            if (btnAprobar) {
                btnAprobar.addEventListener('click', function() {
                    // Obtener el ID de la orden
                    const urlParams = new URLSearchParams(window.location.search);
                    const idOrden = urlParams.get('id') || 0;
                    
                    // Confirmación
                    Swal.fire({
                        title: '¿Aprobar orden de salida?',
                        html: `
                            <p>Esta acción confirmará la salida de los productos del inventario.</p>
                        <p class="text-danger"><strong>Esta acción no se puede deshacer</strong></p>
                        `,
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#28a745',
                        cancelButtonColor: '#6c757d',
                        confirmButtonText: 'Sí, aprobar',
                        cancelButtonText: 'Cancelar'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            aprobarOrden(idOrden);
                        }
                    });
                });
            }
        });

        function aprobarOrden(idOrden) {
            // Crear FormData
            const formData = new FormData();
            formData.append('accion', 'aprovarSalida');
            formData.append('id', idOrden);
            
            // Mostrar loading
            Swal.fire({
                title: 'Procesando...',
                text: 'Aprobando orden de salida',
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
                        title: '¡Orden aprobada!',
                        text: data.message || 'La orden de salida ha sido aprobada correctamente',
                        confirmButtonColor: '#28a745'
                    }).then(() => {
                        // Recargar página o redirigir
                        location.reload();
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: data.message || 'Error al aprobar la orden de salida',
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
        }
    </script>

</body>

</html>
