<?php
require_once __DIR__ . '/auth.php';
requerir_autenticacion();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <title>AlmacÃ©n Croram - Areas</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta content="Sistema de inventario de CRORAM." name="description" />
    <meta content="HoppingJet Studio." name="author" />

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
                    <h4 class="fs-18 fw-bold mb-0">Proveedores</h4>
                </div>

                <div class="text-end">
                    <ol class="breadcrumb m-0 py-0 fs-13">
                        <li class="breadcrumb-item"><a href="javascript: void(0);">Ver</a></li>

                        <li class="breadcrumb-item active">Proveedores</li>
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
                                <h4 class="header-title">Areas</h4>
                                <p class="text-muted font-14">
                                    Da de alta un area para poder crear ordenes de compra.
                                </p>
                                <table id="alternative-page-datatable"
                                    class="table table-striped dt-responsive nowrap w-100">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Nombre</th>
                                            <th>Presupuesto</th>
                                            <th>Encargado</th>
                                            <th>Acciones</th>
                                        </tr>
                                    </thead>

                                    <tbody>
                                        <tr>
                                            <td>1</td>
                                            <td>AlmacÃ©n General</td>
                                            <td>$150,000.00</td>
                                            <td>Juan PÃ©rez</td>
                                            <td>
                                                <button class="btn btn-sm btn-primary">Editar</button>
                                                <button class="btn btn-sm btn-danger">Eliminar</button>
                                            </td>
                                        </tr>

                                        <tr>
                                            <td>2</td>
                                            <td>Compras</td>
                                            <td>$80,000.00</td>
                                            <td>MarÃ­a LÃ³pez</td>
                                            <td>
                                                <button class="btn btn-sm btn-primary">Editar</button>
                                                <button class="btn btn-sm btn-danger">Eliminar</button>
                                            </td>
                                        </tr>

                                        <tr>
                                            <td>3</td>
                                            <td>LogÃ­stica</td>
                                            <td>$120,000.00</td>
                                            <td>Carlos RamÃ­rez</td>
                                            <td>
                                                <button class="btn btn-sm btn-primary">Editar</button>
                                                <button class="btn btn-sm btn-danger">Eliminar</button>
                                            </td>
                                        </tr>

                                        <tr>
                                            <td>4</td>
                                            <td>AdministraciÃ³n</td>
                                            <td>$200,000.00</td>
                                            <td>Ana Torres</td>
                                            <td>
                                                <button class="btn btn-sm btn-primary">Editar</button>
                                                <button class="btn btn-sm btn-danger">Eliminar</button>
                                            </td>
                                        </tr>

                                        <tr>
                                            <td>5</td>
                                            <td>Ventas</td>
                                            <td>$95,000.00</td>
                                            <td>Luis HernÃ¡ndez</td>
                                            <td>
                                                <button class="btn btn-sm btn-primary">Editar</button>
                                                <button class="btn btn-sm btn-danger">Eliminar</button>
                                            </td>
                                        </tr>
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
</body>

</html>
