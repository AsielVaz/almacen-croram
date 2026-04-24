<?php
require_once __DIR__ . '/auth.php';
requerir_autenticacion();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <title>AlmacÃ©n Croram - Ordenes</title>
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
        $ordenes = json_decode($adminOrdenes->listarOrdenesCompra());
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


            <div class="page-title-head d-flex align-items-center gap-2">
                <div class="flex-grow-1">
                    <h4 class="fs-18 fw-bold mb-0">Ordenes</h4>
                </div>

                <div class="text-end">
                    <ol class="breadcrumb m-0 py-0 fs-13">
                        <li class="breadcrumb-item"><a href="javascript: void(0);">CatÃ¡logos</a></li>

                        <li class="breadcrumb-item active">Ordenes</li>
                    </ol>
                </div>
            </div>




            <div class="page-container">

                <div class="row row-cols-xxl-4 row-cols-md-2 row-cols-1">



                </div>

                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center mb-3">

                                    <div>
                                        <h4 class="header-title mb-1">Ordenes Entrada</h4>
                                        <p class="text-muted font-14 mb-0">
                                            Consulta las ordenes de entrada de inventario registradas en el sistema.
                                        </p>
                                    </div>

                                    <div>
                                        <a href="ordenes-entrada-form.php" class="btn btn-primary">
                                            <i class="fa fa-plus mr-1"></i> Crear nueva orden
                                        </a>
                                    </div>

                                </div>
                                <table id="alternative-page-datatable"
                                    class="table table-striped dt-responsive nowrap w-100">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Folio</th>
                                            <th>Proveedor</th>
                                            <th>Fecha de la orden</th>
                                            <th>Estatus</th>
                                            <th>Solicito</th>
                                            <th>Acciones</th>
                                        </tr>
                                    </thead>

                                    <tbody>
                                        <?php foreach ($ordenes as $orden) { ?>
                                            <tr>
                                                <td><?= $orden->id ?></td>
                                                <td><?= $orden->folio ?></td>
                                                <td><?= htmlspecialchars($orden->nombre_proveedor) ?></td>
                                                <td><?= $orden->fecha_orden ?></td>
                                                <td><?= $orden->estatus ?></td>
                                                <td><?= htmlspecialchars($orden->nombre_usuario) ?></td>
                                                <td>
                                                    <a href="ordenes-entrada-detalle.php?id=<?= $orden->id ?>" class="btn btn-sm btn-outline-primary">
                                                        Ver detalle
                                                    </a>
                                                </td>
                                            </tr>
                                        <?php } ?>
                                    </tbody>

                                </table>

                            </div> <!-- end card body-->
                        </div> <!-- end card -->
                    </div><!-- end col-->
                </div> <!-- end row-->





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
        function eliminarFamilia(id) {
            Swal.fire({
                    title: 'Â¿EstÃ¡s seguro?',
                text: "Esta acciÃ³n no se puede deshacer.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'SÃ­, eliminar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    // AquÃ­ irÃ­a la lÃ³gica para eliminar la familia, por ejemplo, una llamada AJAX al servidor.
                    var formData = new FormData();
                    formData.append('accion', 'eliminarFamilia');
                    formData.append('id', id);
                    fetch('api/apiCatalogos.php', {
                        method: 'POST',
                        body: formData
                    }).then(response => response.json())
                    .then(data => {
                        if (data.status === 'success') {
                            Swal.fire(
                                'Eliminado',
                                'La familia ha sido eliminada.',
                                'success'
                            ).then(() => {
                                location.reload();
                            });
                        } else {
                            Swal.fire(
                                'Error',
                                'Hubo un problema al eliminar la familia.',
                                'error'
                            );
                        }
                    }).catch(error => {
                        Swal.fire(
                            'Error',
                            'Hubo un problema al eliminar la familia.',
                            'error'
                        );
                    });
                }
            })
        
        }
    </script>
</body>

</html>

