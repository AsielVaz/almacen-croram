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
                    <h4 class="fs-18 fw-bold mb-0">Form Wizard</h4>
                </div>

                <div class="text-end">
                    <ol class="breadcrumb m-0 py-0 fs-13">
                        <li class="breadcrumb-item"><a href="javascript: void(0);">Abstack</a></li>

                        <li class="breadcrumb-item"><a href="javascript: void(0);">Forms</a></li>

                        <li class="breadcrumb-item active">Form Wizard</li>
                    </ol>
                </div>
            </div>




            <div class="page-container">

                <div class="row">
                    <div class="col-lg-12">
                        <div class="card">
                            <div class="card-header border-bottom border-dashed d-flex align-items-center">
                                <h4 class="header-title">Areas</h4>
                            </div>

                            <div class="card-body">
                                <p class="text-muted">
                                    Agrega aquÃ­ las areas que despuÃ©s daran orden al inventario.
                                </p>

                                <form id="formArea" autocomplete="off">

                                    <!-- Nombre del Ã¡rea -->
                                    <div class="mb-3">
                                        <label for="nombre" class="form-label">
                                            Nombre del Ã¡rea <span class="text-danger">*</span>
                                        </label>
                                        <input
                                            type="text"
                                            id="nombre"
                                            name="nombre"
                                            class="form-control"
                                            placeholder="Ej. AlmacÃ©n General"
                                            required>
                                    </div>

                                    <!-- Presupuesto -->
                                    <div class="mb-3">
                                        <label for="presupuesto" class="form-label">
                                            Presupuesto asignado <span class="text-danger">*</span>
                                        </label>
                                        <input
                                            type="number"
                                            step="0.01"
                                            id="presupuesto"
                                            name="presupuesto"
                                            class="form-control"
                                            placeholder="Ej. 150000"
                                            required>
                                    </div>

                                    <!-- Encargado -->
                                    <div class="mb-3">
                                        <label for="id_encargado" class="form-label">
                                            Encargado del Ã¡rea <span class="text-danger">*</span>
                                        </label>
                                        <select
                                            id="id_encargado"
                                            name="id_encargado"
                                            class="form-control"
                                            required>
                                            <option value="">Seleccione encargado</option>
                                            <option value="1">Juan PÃ©rez (Ejemplo)</option>
                                            <option value="2">MarÃ­a LÃ³pez (Ejemplo)</option>
                                        </select>
                                    </div>

                                    <!-- Acciones -->
                                    <div class="d-flex justify-content-between">
                                        <button type="reset" class="btn btn-secondary">
                                            Limpiar
                                        </button>

                                        <button type="submit" class="btn btn-primary">
                                            Guardar Ã¡rea
                                        </button>
                                    </div>

                                </form>




                            </div> <!-- end card-body -->
                        </div> <!-- end card -->
                    </div> <!-- end col -->


                </div>





            </div> <!-- container -->

            <!-- Footer Start -->
            <?php include 'templates/footer.php'; ?>

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

    <!-- Bootstrap Wizard Form js -->
    <script src="assets/vendor/vanilla-wizard/js/wizard.min.js"></script>

    <!-- Wizard Form Demo js -->
    <script src="assets/js/components/form-wizard.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        document.getElementById('credito_proveedor').addEventListener('change', function() {
            document.getElementById('plazo_credito').disabled = this.value == '0';
        });
    </script>

    <script>
        document.getElementById('formArea').addEventListener('submit', function(e) {
            e.preventDefault();

            const formData = new FormData(this);
            formData.append('accion', 'altaArea');

            fetch('api/apiAreas.php', {
                    method: 'POST',
                    body: formData
                })
                .then(r => r.json())
                .then(res => {
                    if (res.status === 'success') {
                        Swal.fire('Ã‰xito', res.message, 'success')
                            .then(() => window.location.reload());
                    } else {
                        Swal.fire('Error', res.message, 'error');
                    }
                })
                .catch(() => {
                    Swal.fire('Error', 'No se pudo conectar con el servidor', 'error');
                });
        });
    </script>


</body>

</html>
