<?php
require_once __DIR__ . '/auth.php';
requerir_autenticacion();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <title>AlmacÃ©n Croram - Escanear Orden</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta content="Sistema de inventario de CRORAM." name="description" />
    <meta content="HoppingJet Studio." name="author" />

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

    <style>
        .scan-wrapper {
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 55vh;
        }

        .scan-card {
            max-width: 500px;
            width: 100%;
            text-align: center;
        }

        .scan-icon {
            font-size: 64px;
            color: var(--bs-primary);
            margin-bottom: 1rem;
            animation: pulse 2s ease-in-out infinite;
        }

        @keyframes pulse {
            0%, 100% { opacity: 1; transform: scale(1); }
            50% { opacity: 0.6; transform: scale(1.05); }
        }

        .scan-input {
            font-size: 1.5rem;
            text-align: center;
            letter-spacing: 4px;
            font-weight: 600;
            padding: 0.75rem 1rem;
        }

        .scan-input:focus {
            border-color: var(--bs-primary);
            box-shadow: 0 0 0 0.25rem rgba(var(--bs-primary-rgb), 0.15);
        }

        .scan-hint {
            font-size: 0.85rem;
            color: #9ca3af;
            margin-top: 0.75rem;
        }
    </style>
</head>

<body>
    <!-- Begin page -->
    <div class="wrapper">

        <!-- Sidenav Menu Start -->
        <?php include_once 'templates/barra.php' ?>
        <!-- Sidenav Menu End -->

        <!-- Topbar Start -->
        <?php include_once 'templates/headder.php' ?>
        <!-- Topbar End -->

        <!-- ============================================================== -->
        <!-- Start Page Content here -->
        <!-- ============================================================== -->

        <div class="page-content">

            <div class="page-title-head d-flex align-items-center gap-2">
                <div class="flex-grow-1">
                    <h4 class="fs-18 fw-bold mb-0">Escanear Orden de Salida</h4>
                </div>
                <div class="text-end">
                    <ol class="breadcrumb m-0 py-0 fs-13">
                        <li class="breadcrumb-item"><a href="index.php">Inicio</a></li>
                        <li class="breadcrumb-item active">Escanear Orden</li>
                    </ol>
                </div>
            </div>

            <div class="page-container">

                <div class="scan-wrapper">
                    <div class="scan-card">
                        <div class="card">
                            <div class="card-body p-4">

                                <div class="scan-icon">
                                    <i class="ri-qr-scan-2-line"></i>
                                </div>

                                <h5 class="mb-1">Escanear cÃ³digo QR</h5>
                                <p class="text-muted mb-4">
                                    Escanea el cÃ³digo QR de la orden de salida o ingresa el nÃºmero manualmente.
                                </p>

                                <div class="mb-2">
                                    <input
                                        type="text"
                                        id="qr-input"
                                        class="form-control scan-input"
                placeholder="â€”"
                                        autocomplete="off"
                                        inputmode="numeric"
                                    />
                                </div>

                                <p class="scan-hint">
                                    <i class="ri-information-line me-1"></i>
                                    Presiona <kbd>Enter</kbd> despuÃ©s de escanear para continuar
                                </p>

                                <!-- Mensaje de error (oculto por defecto) -->
                                <div id="scan-error" class="alert alert-danger mt-3 d-none" role="alert">
                                    <i class="ri-error-warning-line me-1"></i>
                                    Ingresa un nÃºmero de orden vÃ¡lido.
                                </div>

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

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const input    = document.getElementById('qr-input');
            const errorMsg = document.getElementById('scan-error');

            // Ã¢â€â‚¬Ã¢â€â‚¬ Auto-focus al cargar Ã¢â€â‚¬Ã¢â€â‚¬
            input.focus();

            // Ã¢â€â‚¬Ã¢â€â‚¬ Re-focus si el usuario hace clic fuera Ã¢â€â‚¬Ã¢â€â‚¬
            document.addEventListener('click', function () {
                input.focus();
            });

            // Ã¢â€â‚¬Ã¢â€â‚¬ Re-focus al cambiar de pestaÃƒÂ±a y volver Ã¢â€â‚¬Ã¢â€â‚¬
            document.addEventListener('visibilitychange', function () {
                if (!document.hidden) {
                    input.focus();
                }
            });

            // Ã¢â€â‚¬Ã¢â€â‚¬ Manejar Enter Ã¢â€â‚¬Ã¢â€â‚¬
            input.addEventListener('keydown', function (e) {
                if (e.key === 'Enter') {
                    e.preventDefault();

                    const valor = input.value.trim();

                    // Validar que sea un nÃºmero entero positivo
                    if (/^\d+$/.test(valor) && parseInt(valor) > 0) {
                        errorMsg.classList.add('d-none');
                        // Redirigir al detalle de la orden
                        window.location.href = 'ordenes-salida-detalle.php?id=' + encodeURIComponent(valor);
                    } else {
                        // Mostrar error y limpiar
                        errorMsg.classList.remove('d-none');
                        input.value = '';
                        input.focus();
                    }
                }
            });
        });
    </script>

</body>

</html>
