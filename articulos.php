<?php
require_once __DIR__ . '/auth.php';
requerir_autenticacion();
$puedeAdministrarArticulos = usuario_tiene_permiso('articulos_administrar');
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <title>Almacén Croram - Artículos</title>
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

        .page-header-custom {
            background: white;
            padding: 1.5rem;
            border-radius: 0.5rem;
            margin-bottom: 2rem;
            box-shadow: 0 0.125rem 0.5rem rgba(0, 0, 0, 0.05);
            border: 1px solid #e9ecef;
            border-left: 6px solid #495057;
        }

        .inventory-card {
            background: white;
            border-radius: 0.5rem;
            box-shadow: 0 0.125rem 0.5rem rgba(0, 0, 0, 0.05);
            border: 1px solid #e9ecef;
        }

        .card-header-custom {
            background: #f8f9fa;
            padding: 1.5rem;
            border-bottom: 2px solid #e9ecef;
            border-radius: 0.5rem 0.5rem 0 0;
        }

        .card-title-main {
            font-size: 1.25rem;
            font-weight: 700;
            color: #212529;
            margin-bottom: 0.25rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .card-subtitle {
            color: #6c757d;
            font-size: 0.875rem;
            margin: 0;
        }

        .toolbar-section {
            display: flex;
            gap: 0.75rem;
            align-items: center;
            flex-wrap: wrap;
        }

        .btn-modern {
            padding: 0.625rem 1.5rem;
            border-radius: 0.375rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            transition: all 0.3s ease;
            border: none;
            font-size: 0.875rem;
        }

        .btn-add {
            background: #495057;
            color: white;
            border: 2px solid #343a40;
        }

        .btn-add:hover {
            background: #343a40;
            transform: translateY(-2px);
            box-shadow: 0 0.25rem 0.5rem rgba(0, 0, 0, 0.15);
        }

        .btn-qr {
            background: linear-gradient(135deg, #28a745 0%, #218838 100%);
            color: white;
            border: 2px solid #1e7e34;
            padding: 0.625rem 1.25rem;
        }

        .btn-qr:hover {
            background: linear-gradient(135deg, #218838 0%, #1e7e34 100%);
            transform: translateY(-2px);
            box-shadow: 0 0.25rem 0.5rem rgba(40, 167, 69, 0.3);
        }

        .select-modern {
            background: #f8f9fa;
            border: 2px solid #e9ecef;
            border-radius: 0.375rem;
            padding: 0.625rem 0.875rem;
            font-size: 0.875rem;
            font-weight: 600;
            transition: all 0.3s ease;
            min-width: 180px;
        }

        .select-modern:focus {
            background: white;
            border-color: #6c757d;
            box-shadow: 0 0 0 0.2rem rgba(108, 117, 125, 0.15);
            outline: none;
        }

        .qr-control-group {
            display: flex;
            gap: 0.5rem;
            align-items: center;
        }

        .table-modern {
            margin-bottom: 0;
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
            font-size: 0.8rem;
            letter-spacing: 0.5px;
            vertical-align: middle;
        }

        .table-modern tbody tr {
            transition: all 0.3s ease;
        }

        .table-modern tbody tr:hover {
            background-color: #f8f9fa;
            transform: scale(1.002);
        }

        .table-modern tbody td {
            padding: 1rem;
            vertical-align: middle;
            border-color: #e9ecef;
        }

        .table-modern tbody td:first-child {
            width: 50px;
            text-align: center;
        }

        .checkbox-custom {
            width: 18px;
            height: 18px;
            cursor: pointer;
            accent-color: #495057;
        }

        .badge-quantity {
            background: #e9ecef;
            color: #495057;
            padding: 0.375rem 0.75rem;
            border-radius: 0.25rem;
            font-weight: 700;
            font-size: 0.875rem;
        }

        .badge-sku {
            background: #f8f9fa;
            color: #6c757d;
            padding: 0.375rem 0.75rem;
            border-radius: 0.25rem;
            font-weight: 600;
            font-size: 0.8rem;
            border: 1px solid #dee2e6;
        }

        .btn-action {
            padding: 0.5rem 1rem;
            border-radius: 0.375rem;
            font-weight: 600;
            font-size: 0.8rem;
            transition: all 0.3s ease;
            background: #6c757d;
            color: white;
            border: none;
        }

        .btn-action:hover {
            background: #5a6268;
            transform: translateY(-2px);
            box-shadow: 0 0.25rem 0.5rem rgba(0, 0, 0, 0.15);
        }

        .btn-history {
            background: #198754;
        }

        .btn-history:hover {
            background: #157347;
        }

        .action-stack {
            display: flex;
            gap: 0.5rem;
            justify-content: center;
            flex-wrap: wrap;
        }

        .card-body-custom {
            padding: 1.5rem;
        }

        .table-container {
            border-radius: 0 0 0.5rem 0.5rem;
            overflow: hidden;
        }

        /* DataTable overrides */
        .dataTables_wrapper .dataTables_length select {
            background: #f8f9fa;
            border: 2px solid #e9ecef;
            border-radius: 0.375rem;
            padding: 0.375rem 0.75rem;
        }

        .dataTables_wrapper .dataTables_filter input {
            background: #f8f9fa;
            border: 2px solid #e9ecef;
            border-radius: 0.375rem;
            padding: 0.5rem 0.875rem;
            margin-left: 0.5rem;
        }

        .dataTables_wrapper .dataTables_filter input:focus {
            border-color: #6c757d;
            outline: none;
        }

        .dataTables_wrapper .dataTables_info {
            color: #6c757d;
            font-size: 0.875rem;
        }

        .dataTables_wrapper .dataTables_paginate .paginate_button {
            border-radius: 0.375rem;
            margin: 0 0.125rem;
        }

        .dataTables_wrapper .dataTables_paginate .paginate_button.current {
            background: #495057 !important;
            border-color: #495057 !important;
            color: white !important;
        }

        .family-text {
            color: #495057;
            font-weight: 600;
        }

        .subfamily-text {
            color: #6c757d;
            font-size: 0.875rem;
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
                        <h4 class="fs-18 fw-bold mb-0">
                            <i class="ri-archive-line me-2"></i>
                            Gestión de Inventario
                        </h4>
                    </div>
                    <div class="text-end">
                        <ol class="breadcrumb m-0 py-0 fs-13">
                            <li class="breadcrumb-item"><a href="javascript: void(0);">Artículos</a></li>
                            <li class="breadcrumb-item active">Inventario</li>
                        </ol>
                    </div>
                </div>
            </div>

            <div class="page-container">

                <div class="row">
                    <div class="col-12">
                        <div class="inventory-card">
                            <div class="card-header-custom">
                                <div class="d-flex justify-content-between align-items-start flex-wrap gap-3">
                                    <div>
                                        <h5 class="card-title-main">
                                            <i class="ri-box-3-line"></i>
                                            Catálogo de Artículos
                                        </h5>
                                        <p class="card-subtitle">
                                            Administre los productos del inventario
                                        </p>
                                    </div>

                                    <div class="toolbar-section">
                                        <?php if ($puedeAdministrarArticulos): ?>
                                        <a href="articulos-form.php" class="btn btn-modern btn-add">
                                            <i class="ri-add-circle-line me-1"></i> Agregar Artículo
                                        </a>
                                        <?php endif; ?>

                                        <div class="qr-control-group">
                                            <select id="tipoImpresion" class="select-modern">
                                                <option value="termica">Impresora Térmica</option>
                                                <option value="carta">Hoja Carta</option>
                                            </select>

                                            <button id="btnGenerarQr" class="btn btn-modern btn-qr">
                                                <i class="ri-qr-code-line me-1"></i> Generar QR
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="card-body-custom">
                                <div class="row g-3 align-items-end mb-3">
                                    <div class="col-md-6">
                                        <label for="busquedaArticulos" class="form-label fw-semibold">Buscar articulo</label>
                                        <input type="search" id="busquedaArticulos" class="form-control" placeholder="Nombre, SKU, descripcion o ubicacion">
                                    </div>
                                    <div class="col-md-2">
                                        <label for="porPaginaArticulos" class="form-label fw-semibold">Por pagina</label>
                                        <select id="porPaginaArticulos" class="form-select">
                                            <option value="10">10</option>
                                            <option value="25" selected>25</option>
                                            <option value="50">50</option>
                                            <option value="100">100</option>
                                        </select>
                                    </div>
                                    <div class="col-md-4 text-md-end">
                                        <div id="estadoArticulos" class="text-muted small">Cargando articulos...</div>
                                    </div>
                                </div>
                                <div class="table-container">
                                    <table id="articulos-api-table"
                                        class="table table-modern table-striped dt-responsive nowrap w-100">
                                        <thead>
                                            <tr>
                                                <th>
                                                    <input type="checkbox" id="checkAll" class="checkbox-custom">
                                                </th>
                                                <th>ID</th>
                                                <th>ARTÍCULO</th>
                                                <th>FAMILIA</th>
                                                <th>SUBFAMILIA</th>
                                                <th class="text-center">CANTIDAD</th>
                                                <th>SKU</th>
                                                <th>CONDICI&Oacute;N</th>
                                                <th>Unidad de medida</th>
                                                <th>DESCRIPCI&Oacute;N</th>
                                                <th>UBICACI&Oacute;N</th>
                                                <th class="text-center">ACCIONES</th>
                                            </tr>
                                        </thead>
                                        <tbody id="articulosTableBody">
                                            <tr><td colspan="12" class="text-center text-muted py-4">Cargando articulos...</td></tr>
                                        </tbody>
                                    </table>
                                </div>
                                <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mt-3">
                                    <div id="resumenPaginacionArticulos" class="text-muted small"></div>
                                    <div class="btn-group">
                                        <button type="button" class="btn btn-outline-secondary btn-sm" id="btnPaginaAnterior">Anterior</button>
                                        <button type="button" class="btn btn-outline-secondary btn-sm" id="btnPaginaSiguiente">Siguiente</button>
                                    </div>
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
    
    <script>
        const PUEDE_ADMINISTRAR_ARTICULOS = <?= $puedeAdministrarArticulos ? 'true' : 'false' ?>;
        const estadoArticulos = {
            pagina: 1,
            porPagina: 25,
            texto: '',
            totalPaginas: 1,
            total: 0,
            timer: null
        };

        function escaparHtml(valor) {
            return String(valor ?? '').replace(/[&<>"']/g, function(caracter) {
                return ({
                    '&': '&amp;',
                    '<': '&lt;',
                    '>': '&gt;',
                    '"': '&quot;',
                    "'": '&#039;'
                })[caracter];
            });
        }

        function renderArticulos(articulos) {
            const tbody = document.getElementById('articulosTableBody');

            if (!articulos.length) {
                tbody.innerHTML = '<tr><td colspan="12" class="text-center text-muted py-4">Sin articulos para mostrar</td></tr>';
                return;
            }

            tbody.innerHTML = articulos.map(articulo => {
                const cantidad = Math.round(parseFloat(articulo.cantidad || 0));
                const id = escaparHtml(articulo.id);
                const nombre = escaparHtml(articulo.nombre);
                const sku = escaparHtml(articulo.sku || '');
                const descripcion = escaparHtml(articulo.descripcion || '');

                return `
                    <tr>
                        <td>
                            <input type="checkbox" class="chkProducto checkbox-custom"
                                data-id="${id}"
                                data-nombre="${nombre}"
                                data-sku="${sku}"
                                data-descripcion="${descripcion}"
                                data-cantidad="${cantidad}">
                        </td>
                        <td class="fw-bold">${id}</td>
                        <td><div class="fw-bold text-dark">${nombre}</div></td>
                        <td><span class="family-text">${escaparHtml(articulo.familia || '')}</span></td>
                        <td><span class="subfamily-text">${escaparHtml(articulo.subfamilia || '')}</span></td>
                        <td class="text-center"><span class="badge-quantity">${cantidad}</span></td>
                        <td><span class="badge-sku"><i class="ri-barcode-line"></i> ${sku}</span></td>
                        <td><span class="family-text">${escaparHtml(articulo.tipo_articulo || 'NUEVO')}</span></td>
                        <td><span class="family-text">${escaparHtml(articulo.unidad_medida || '')}</span></td>
                        <td><span class="family-text">${escaparHtml(articulo.descripcion || '')}</span></td>
                        <td><span class="family-text">${escaparHtml(articulo.ubicacion || '')}</span></td>
                        <td class="text-center">
                            <div class="action-stack">
                                ${PUEDE_ADMINISTRAR_ARTICULOS ? `
                                    <a href="articulos-form.php?id=${id}" class="btn btn-action">
                                        <i class="ri-edit-line me-1"></i> Editar
                                    </a>
                                ` : ''}
                                <a href="articulo-historial.php?id=${id}" class="btn btn-action btn-history">
                                    <i class="ri-time-line me-1"></i> Historial
                                </a>
                            </div>
                        </td>
                    </tr>`;
            }).join('');
        }

        function cargarArticulos() {
            const formData = new FormData();
            formData.append('accion', 'listarArticulosPaginados');
            formData.append('pagina', estadoArticulos.pagina);
            formData.append('porPagina', estadoArticulos.porPagina);
            formData.append('texto', estadoArticulos.texto);
            formData.append('soloActivos', 0);

            document.getElementById('estadoArticulos').textContent = 'Cargando articulos...';

            fetch('api/apiArticulos.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                const articulos = data.data || [];
                const pagination = data.pagination || {};
                estadoArticulos.total = pagination.total || 0;
                estadoArticulos.totalPaginas = pagination.total_pages || 1;
                estadoArticulos.pagina = pagination.page || estadoArticulos.pagina;

                renderArticulos(articulos);
                document.getElementById('estadoArticulos').textContent = `${estadoArticulos.total} articulos encontrados`;
                document.getElementById('resumenPaginacionArticulos').textContent = `Pagina ${estadoArticulos.pagina} de ${estadoArticulos.totalPaginas}`;
                document.getElementById('btnPaginaAnterior').disabled = estadoArticulos.pagina <= 1;
                document.getElementById('btnPaginaSiguiente').disabled = estadoArticulos.pagina >= estadoArticulos.totalPaginas;
                document.getElementById('checkAll').checked = false;
            })
            .catch(error => {
                console.error('Error cargando articulos:', error);
                document.getElementById('articulosTableBody').innerHTML = '<tr><td colspan="12" class="text-center text-danger py-4">No se pudieron cargar los articulos</td></tr>';
                document.getElementById('estadoArticulos').textContent = 'Error al cargar articulos';
            });
        }

        document.getElementById('busquedaArticulos').addEventListener('input', function() {
            clearTimeout(estadoArticulos.timer);
            estadoArticulos.timer = setTimeout(() => {
                estadoArticulos.texto = this.value.trim();
                estadoArticulos.pagina = 1;
                cargarArticulos();
            }, 250);
        });

        document.getElementById('porPaginaArticulos').addEventListener('change', function() {
            estadoArticulos.porPagina = parseInt(this.value, 10) || 25;
            estadoArticulos.pagina = 1;
            cargarArticulos();
        });

        document.getElementById('btnPaginaAnterior').addEventListener('click', function() {
            if (estadoArticulos.pagina > 1) {
                estadoArticulos.pagina--;
                cargarArticulos();
            }
        });

        document.getElementById('btnPaginaSiguiente').addEventListener('click', function() {
            if (estadoArticulos.pagina < estadoArticulos.totalPaginas) {
                estadoArticulos.pagina++;
                cargarArticulos();
            }
        });

        cargarArticulos();

        document.getElementById('btnGenerarQr').addEventListener('click', function() {

            let seleccionados = [];

            document.querySelectorAll('.chkProducto:checked').forEach(chk => {
                seleccionados.push({
                    id: chk.dataset.id,
                    nombre: chk.dataset.nombre,
                    sku: chk.dataset.sku,
                    descripcion: chk.dataset.descripcion || '',
                    cantidad: chk.dataset.cantidad
                });
            });

            if (seleccionados.length === 0) {
                alert('Selecciona al menos un artículo');
                return;
            }

            // Leer tipo de impresión desde el select
            let tipo = document.getElementById('tipoImpresion').value;

            let url = 'api/generaProductosQrPdf.php';
            if (tipo === 'carta') {
                url = 'api/generaProductosQrPdfCarta.php';
            }

            // Crear formulario dinámico
            let form = document.createElement('form');
            form.method = 'POST';
            form.action = url;
            form.target = '_blank';

            let input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'productos';
            input.value = JSON.stringify(seleccionados);

            form.appendChild(input);
            document.body.appendChild(form);
            form.submit();
            document.body.removeChild(form);
        });

        // Check / uncheck todos
        document.getElementById('checkAll').addEventListener('change', function() {
            document.querySelectorAll('.chkProducto').forEach(chk => {
                chk.checked = this.checked;
            });
        });
    </script>

</body>

</html>
