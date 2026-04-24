<?php
require_once __DIR__ . '/auth.php';
requerir_autenticacion();
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="utf-8" />
    <title>AlmacÃ©n Croram - Importar Inventario</title>
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

    <style>
        /* â”€â”€ Zona de drop â”€â”€ */
        #drop-zone {
            border: 2px dashed var(--bs-border-color, #dee2e6);
            border-radius: 12px;
            padding: 48px 24px;
            text-align: center;
            cursor: pointer;
            transition: border-color .2s, background .2s;
        }
        #drop-zone.drag-over {
            border-color: #0d6efd;
            background: rgba(13, 110, 253, .06);
        }
        #drop-zone .drop-icon {
            font-size: 3rem;
            color: #0d6efd;
            margin-bottom: 12px;
            display: block;
        }
        #file-input { display: none; }

        /* â”€â”€ Nombre de archivo seleccionado â”€â”€ */
        #file-label {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            font-size: .875rem;
            padding: 4px 12px;
            border-radius: 20px;
            background: rgba(13, 110, 253, .1);
            color: #0d6efd;
            margin-top: 10px;
        }
        #file-label.d-none { display: none !important; }

        /* â”€â”€ Tabla de resultados â”€â”€ */
        #result-section { display: none; }
        #errores-section { display: none; }

        /* â”€â”€ Spinner botÃ³n â”€â”€ */
        #btn-importar .spinner-border { width: 1rem; height: 1rem; border-width: 2px; }

        /* â”€â”€ Badges de resumen â”€â”€ */
        .stat-card {
            border-radius: 12px;
            padding: 20px 24px;
        }
        .stat-card .stat-number {
            font-size: 2rem;
            font-weight: 700;
            line-height: 1;
        }
        .stat-card .stat-label {
            font-size: .75rem;
            text-transform: uppercase;
            letter-spacing: .05em;
            margin-top: 4px;
            opacity: .7;
        }
    </style>
</head>

<body>
    <div class="wrapper">

        <?php include_once 'templates/barra.php' ?>
        <?php include_once 'templates/headder.php' ?>

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
                    <h4 class="fs-18 fw-bold mb-0">Importar Inventario</h4>
                </div>
                <div class="text-end">
                    <ol class="breadcrumb m-0 py-0 fs-13">
                        <li class="breadcrumb-item"><a href="javascript:void(0);">AlmacÃ©n</a></li>
                        <li class="breadcrumb-item active">Importar CSV</li>
                    </ol>
                </div>
            </div>

            <div class="page-container">
                <div class="row justify-content-center">
                    <div class="col-xxl-7 col-xl-8 col-lg-10">

                        <!-- â”€â”€ Tarjeta de carga â”€â”€ -->
                        <div class="card">
                            <div class="card-header">
                                <h5 class="card-title mb-0">
                                    <i class="ri-upload-cloud-2-line me-2 text-primary"></i>
                                    Cargar archivo CSV
                                </h5>
                            </div>
                            <div class="card-body">

                                <!-- Zona de drop -->
                                <div id="drop-zone">
                                    <i class="ri-file-text-line drop-icon"></i>
                                    <p class="mb-1 fw-semibold">Arrastra tu archivo aquÃ­</p>
                                    <p class="text-muted fs-13 mb-3">o haz clic para seleccionarlo</p>
                                    <button type="button" class="btn btn-outline-primary btn-sm px-4"
                                        onclick="document.getElementById('file-input').click()">
                                        <i class="ri-folder-open-line me-1"></i> Explorar archivos
                                    </button>
                                    <input type="file" id="file-input" accept=".csv">
                                    <div id="file-label" class="d-none mx-auto">
                                        <i class="ri-file-text-fill"></i>
                                        <span id="file-name">â€”</span>
                                        <i class="ri-close-line" style="cursor:pointer" onclick="resetFile()"></i>
                                    </div>
                                </div>

                                <!-- Info columnas requeridas -->
                                <div class="alert alert-info d-flex gap-2 mt-3 mb-0 fs-13" role="alert">
                                    <i class="ri-information-line fs-18 flex-shrink-0"></i>
                                    <div>
                                        El archivo debe contener las columnas:
                                        <strong>Nombre equipo</strong>, <strong>Ubicacion</strong>
                                        y <strong>Unidad medida</strong>.
                                        El separador debe ser <code>,</code>.
                                    </div>
                                </div>

                            </div>
                            <div class="card-footer d-flex justify-content-end gap-2">
                                <button type="button" class="btn btn-light" onclick="resetFile()">
                                    <i class="ri-refresh-line me-1"></i> Limpiar
                                </button>
                                <button type="button" class="btn btn-primary px-4" id="btn-importar"
                                    onclick="importar()" disabled>
                                    <span class="spinner-border d-none me-1" role="status" id="spinner"></span>
                                    <i class="ri-upload-2-line me-1" id="btn-icon"></i>
                                    Importar
                                </button>
                            </div>
                        </div>
                        <!-- /Tarjeta de carga -->

                        <!-- â”€â”€ Tarjeta de resultados â”€â”€ -->
                        <div id="result-section">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="card-title mb-0">
                                        <i class="ri-bar-chart-2-line me-2 text-success"></i>
                                        Resultado de la importaciÃ³n
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <div class="row g-3 text-center">

                                        <div class="col-4">
                                            <div class="stat-card bg-success bg-opacity-10">
                                                <div class="stat-number text-success" id="stat-insertados">0</div>
                                                <div class="stat-label text-success">Insertados</div>
                                            </div>
                                        </div>

                                        <div class="col-4">
                                            <div class="stat-card bg-warning bg-opacity-10">
                                                <div class="stat-number text-warning" id="stat-omitidos">0</div>
                                                <div class="stat-label text-warning">Omitidos</div>
                                            </div>
                                        </div>

                                        <div class="col-4">
                                            <div class="stat-card bg-danger bg-opacity-10">
                                                <div class="stat-number text-danger" id="stat-errores">0</div>
                                                <div class="stat-label text-danger">Errores</div>
                                            </div>
                                        </div>

                                    </div>
                                </div>
                            </div>

                            <!-- Detalle de errores -->
                            <div id="errores-section" class="card border-danger border-opacity-25">
                                <div class="card-header bg-danger bg-opacity-10">
                                    <h5 class="card-title mb-0 text-danger">
                                        <i class="ri-error-warning-line me-2"></i>
                                        Detalle de errores
                                    </h5>
                                </div>
                                <div class="table-responsive">
                                    <table class="table table-sm table-hover mb-0">
                                        <thead class="table-light">
                                            <tr>
                                                <th class="ps-3" style="width:80px">Fila</th>
                                                <th>Nombre</th>
                                                <th>Error</th>
                                            </tr>
                                        </thead>
                                        <tbody id="errores-tbody"></tbody>
                                    </table>
                                </div>
                            </div>

                        </div>
                        <!-- /Tarjeta de resultados -->

                    </div>
                </div>
            </div><!-- /page-container -->

            <?php include 'templates/footer.php'; ?>
        </div>
        <!-- ============================================================== -->
        <!-- End Page content -->
        <!-- ============================================================== -->

    </div><!-- END wrapper -->

    <?php include_once 'templates/theme.php' ?>

    <!-- Vendor js -->
    <script src="assets/js/vendor.min.js"></script>
    <!-- App js -->
    <script src="assets/js/app.js"></script>

    <script>
    // â”€â”€â”€ Referencias DOM â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
    const dropZone      = document.getElementById('drop-zone');
    const fileInput     = document.getElementById('file-input');
    const fileLabel     = document.getElementById('file-label');
    const fileName      = document.getElementById('file-name');
    const btnImportar   = document.getElementById('btn-importar');
    const spinner       = document.getElementById('spinner');
    const btnIcon       = document.getElementById('btn-icon');
    const resultSection = document.getElementById('result-section');
    const erroresSection= document.getElementById('errores-section');
    const erroresTbody  = document.getElementById('errores-tbody');

    let archivoSeleccionado = null;

    // â”€â”€â”€ Drag & Drop â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
    dropZone.addEventListener('dragover', e => {
        e.preventDefault();
        dropZone.classList.add('drag-over');
    });

    dropZone.addEventListener('dragleave', () => dropZone.classList.remove('drag-over'));

    dropZone.addEventListener('drop', e => {
        e.preventDefault();
        dropZone.classList.remove('drag-over');
        const file = e.dataTransfer.files[0];
        if (file) setArchivo(file);
    });

    dropZone.addEventListener('click', e => {
        // evitar doble disparo si el clic viene del botÃ³n o el close
        if (e.target.closest('button') || e.target.classList.contains('ri-close-line')) return;
        fileInput.click();
    });

    fileInput.addEventListener('change', () => {
        if (fileInput.files[0]) setArchivo(fileInput.files[0]);
    });

    // â”€â”€â”€ Helpers â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
    function setArchivo(file) {
        if (!file.name.toLowerCase().endsWith('.csv')) {
            showAlert('Solo se aceptan archivos .csv', 'danger');
            return;
        }
        archivoSeleccionado = file;
        fileName.textContent = file.name;
        fileLabel.classList.remove('d-none');
        btnImportar.disabled = false;
        resultSection.style.display = 'none';
    }

    function resetFile() {
        archivoSeleccionado = null;
        fileInput.value = '';
        fileName.textContent = 'â€”';
        fileLabel.classList.add('d-none');
        btnImportar.disabled = true;
        resultSection.style.display = 'none';
        erroresSection.style.display = 'none';
    }

    function setBusy(busy) {
        btnImportar.disabled = busy;
        spinner.classList.toggle('d-none', !busy);
        btnIcon.classList.toggle('d-none', busy);
    }

    function showAlert(mensaje, tipo = 'danger') {
        const existing = document.getElementById('alert-flash');
        if (existing) existing.remove();

        const alert = document.createElement('div');
        alert.id = 'alert-flash';
        alert.className = `alert alert-${tipo} alert-dismissible fade show mt-3`;
        alert.innerHTML = `${mensaje}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>`;
        document.querySelector('.page-container').prepend(alert);
    }

    // â”€â”€â”€ Importar â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
    async function importar() {
        if (!archivoSeleccionado) return;

        setBusy(true);
        resultSection.style.display = 'none';
        erroresSection.style.display = 'none';

        const formData = new FormData();
        formData.append('archivo', archivoSeleccionado);

        try {
            const res  = await fetch('api/apiCarga.php', { method: 'POST', body: formData });
            const data = await res.json();

            if (!res.ok || data.error) {
                showAlert(data.error ?? 'Error desconocido del servidor.');
                return;
            }

            // â”€â”€ Mostrar resumen â”€â”€
            document.getElementById('stat-insertados').textContent = data.insertados;
            document.getElementById('stat-omitidos').textContent   = data.omitidos;
            document.getElementById('stat-errores').textContent    = data.errores.length;
            resultSection.style.display = 'block';

            // â”€â”€ Detalle de errores â”€â”€
            if (data.errores.length > 0) {
                erroresTbody.innerHTML = data.errores.map(e => `
                    <tr>
                        <td class="ps-3 text-muted">${e.fila}</td>
                        <td>${escHtml(e.nombre)}</td>
                        <td class="text-danger">${escHtml(e.error)}</td>
                    </tr>`).join('');
                erroresSection.style.display = 'block';
            }

            const tipo = data.insertados > 0 ? 'success' : 'warning';
            showAlert(
                `<strong>ImportaciÃ³n completada.</strong> ${data.insertados} registro(s) insertados correctamente.`,
                tipo
            );

        } catch (err) {
            showAlert('No se pudo comunicar con el servidor. Verifica tu conexiÃ³n o la ruta del API.');
        } finally {
            setBusy(false);
        }
    }

    function escHtml(str) {
        return String(str)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;');
    }
    </script>

</body>
</html>
