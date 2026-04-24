<?php
require_once __DIR__ . '/auth.php';
requerir_autenticacion();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <title>Almacén Croram</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta content="Sistema de inventario de CRORAM." name="description" />
    <meta content="HoppingJet Studio." name="author" />

    <!-- App favicon -->
    <link rel="shortcut icon" href="favicon.png">

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@300;400;500;600&family=Syne:wght@600;700&display=swap" rel="stylesheet">

    <!-- Vendor css -->
    <link href="assets/css/vendor.min.css" rel="stylesheet" type="text/css" />

    <!-- App css -->
    <link href="assets/css/app.min.css" rel="stylesheet" type="text/css" id="app-style" />

    <!-- Icons css -->
    <link href="assets/css/icons.min.css" rel="stylesheet" type="text/css" />

    <!-- Theme Config Js -->
    <script src="assets/js/config.js"></script>

    <style>
        :root {
            --cr-white:      #ffffff;
            --cr-bg:         #f5f6fa;
            --cr-surface:    #ffffff;
            --cr-border:     #e8eaf0;
            --cr-border-focus: #2d3a8c;
            --cr-text:       #1a1d2e;
            --cr-muted:      #7b7f9a;
            --cr-accent:     #2d3a8c;
            --cr-accent-lt:  #eef0fb;
            --cr-accent-mid: #4a5bbf;
            --cr-danger:     #e8415a;
            --cr-success:    #18a974;
            --cr-radius:     12px;
            --cr-radius-sm:  8px;
            --cr-shadow:     0 1px 3px rgba(30,35,80,.06), 0 4px 20px rgba(30,35,80,.07);
            --cr-shadow-md:  0 2px 8px rgba(30,35,80,.08), 0 8px 32px rgba(30,35,80,.10);
            --cr-font-body:  'DM Sans', sans-serif;
            --cr-font-head:  'Syne', sans-serif;
            --cr-transition: 160ms cubic-bezier(.4,0,.2,1);
        }

        /* �????,?�????,? Global overrides �????,?�????,? */
        body { background: var(--cr-bg) !important; font-family: var(--cr-font-body) !important; color: var(--cr-text) !important; }

        /* �????,?�????,? Page title �????,?�????,? */
        .page-title-head { margin-bottom: 28px; }
        .page-title-head h4 { font-family: var(--cr-font-head); font-size: 22px !important; letter-spacing: -.3px; color: var(--cr-text); }

        /* �????,?�????,? Main card �????,?�????,? */
        .cr-card {
            background: var(--cr-surface);
            border: 1px solid var(--cr-border);
            border-radius: var(--cr-radius);
            box-shadow: var(--cr-shadow);
            overflow: hidden;
            animation: fadeUp .35s ease both;
        }

        .cr-card-header {
            padding: 24px 28px 20px;
            border-bottom: 1px solid var(--cr-border);
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .cr-card-header .cr-icon {
            width: 40px; height: 40px;
            border-radius: 10px;
            background: var(--cr-accent-lt);
            display: flex; align-items: center; justify-content: center;
            color: var(--cr-accent);
            font-size: 18px;
            flex-shrink: 0;
        }

        .cr-card-header h4 {
            font-family: var(--cr-font-head);
            font-size: 17px;
            margin: 0;
            color: var(--cr-text);
        }

        .cr-card-header .cr-badge {
            margin-left: auto;
            font-size: 11px;
            font-weight: 600;
            letter-spacing: .4px;
            text-transform: uppercase;
            padding: 4px 10px;
            border-radius: 20px;
        }

        .cr-badge-edit  { background: #fff4e0; color: #b06800; }
        .cr-badge-new   { background: var(--cr-accent-lt); color: var(--cr-accent); }

        .cr-card-body { padding: 28px; }

        /* �????,?�????,? Section dividers �????,?�????,? */
        .cr-section {
            margin-bottom: 28px;
            padding-bottom: 24px;
            border-bottom: 1px dashed var(--cr-border);
        }
        .cr-section:last-of-type { border-bottom: none; margin-bottom: 0; padding-bottom: 0; }

        .cr-section-label {
            font-size: 11px;
            font-weight: 600;
            letter-spacing: .8px;
            text-transform: uppercase;
            color: var(--cr-muted);
            margin-bottom: 18px;
        }

        /* �????,?�????,? Form fields �????,?�????,? */
        .cr-field { margin-bottom: 20px; }

        .cr-label {
            display: block;
            font-size: 13px;
            font-weight: 500;
            color: var(--cr-text);
            margin-bottom: 6px;
        }
        .cr-label .req { color: var(--cr-danger); margin-left: 2px; }
        .cr-hint { font-size: 11px; color: var(--cr-muted); margin-top: 5px; }

        .cr-input, .cr-select, .cr-textarea {
            display: block;
            width: 100%;
            padding: 10px 14px;
            font-family: var(--cr-font-body);
            font-size: 14px;
            color: var(--cr-text);
            background: var(--cr-white);
            border: 1.5px solid var(--cr-border);
            border-radius: var(--cr-radius-sm);
            transition: border-color var(--cr-transition), box-shadow var(--cr-transition);
            outline: none;
            appearance: none;
            -webkit-appearance: none;
        }
        .cr-input::placeholder, .cr-textarea::placeholder { color: #b5b9ce; }
        .cr-input:focus, .cr-select:focus, .cr-textarea:focus {
            border-color: var(--cr-border-focus);
            box-shadow: 0 0 0 3px rgba(45,58,140,.10);
        }

        .cr-select-wrap { position: relative; }
        .cr-select-wrap::after {
            content: '';
            pointer-events: none;
            position: absolute; right: 14px; top: 50%; transform: translateY(-50%);
            width: 0; height: 0;
            border-left: 5px solid transparent;
            border-right: 5px solid transparent;
            border-top: 5px solid var(--cr-muted);
        }

        .cr-textarea { resize: vertical; min-height: 90px; }

        /* Input with prefix */
        .cr-input-group { display: flex; align-items: stretch; }
        .cr-input-group .cr-prefix {
            display: flex; align-items: center; padding: 0 12px;
            background: #f0f2f9; border: 1.5px solid var(--cr-border);
            border-right: none; border-radius: var(--cr-radius-sm) 0 0 var(--cr-radius-sm);
            font-size: 13px; color: var(--cr-muted); white-space: nowrap;
        }
        .cr-input-group .cr-input { border-radius: 0 var(--cr-radius-sm) var(--cr-radius-sm) 0; }

        /* SKU pill preview */
        #sku_preview {
            display: inline-flex; align-items: center; gap: 6px;
            margin-top: 6px; padding: 4px 10px;
            border-radius: 20px;
            background: var(--cr-accent-lt);
            color: var(--cr-accent);
            font-size: 12px; font-weight: 600; letter-spacing: .4px;
            transition: opacity var(--cr-transition);
        }
        #sku_preview.hidden { opacity: 0; }

        /* Status toggle */
        .cr-status-group { display: flex; gap: 10px; }
        .cr-status-opt { flex: 1; }
        .cr-status-opt input[type="radio"] { display: none; }
        .cr-status-opt label {
            display: block; text-align: center; padding: 9px 0;
            border: 1.5px solid var(--cr-border);
            border-radius: var(--cr-radius-sm);
            font-size: 13px; font-weight: 500;
            cursor: pointer;
            transition: all var(--cr-transition);
            color: var(--cr-muted);
        }
        .cr-status-opt input[type="radio"]:checked + label {
            border-color: var(--cr-accent);
            background: var(--cr-accent-lt);
            color: var(--cr-accent);
        }
        .cr-status-opt input[type="radio"]#activo_1:checked + label { border-color: var(--cr-success); background: #edfaf4; color: var(--cr-success); }
        .cr-status-opt input[type="radio"]#activo_0:checked + label { border-color: var(--cr-danger); background: #fef0f2; color: var(--cr-danger); }

        /* �????,?�????,? Grid layout �????,?�????,? */
        .cr-grid-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 0 24px; }
        .cr-grid-3 { display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 0 24px; }
        @media (max-width: 768px) {
            .cr-grid-2, .cr-grid-3 { grid-template-columns: 1fr; }
        }

        /* �????,?�????,? Buttons �????,?�????,? */
        .cr-actions {
            display: flex; justify-content: space-between; align-items: center;
            padding-top: 24px;
            border-top: 1px solid var(--cr-border);
            margin-top: 8px;
        }

        .cr-btn {
            display: inline-flex; align-items: center; gap: 8px;
            padding: 10px 22px;
            border-radius: var(--cr-radius-sm);
            font-family: var(--cr-font-body);
            font-size: 14px; font-weight: 500;
            border: none; cursor: pointer;
            transition: all var(--cr-transition);
            letter-spacing: .1px;
        }
        .cr-btn:active { transform: scale(.97); }

        .cr-btn-primary {
            background: var(--cr-accent);
            color: #fff;
            box-shadow: 0 2px 10px rgba(45,58,140,.25);
        }
        .cr-btn-primary:hover { background: var(--cr-accent-mid); box-shadow: 0 4px 18px rgba(45,58,140,.30); }

        .cr-btn-ghost {
            background: transparent;
            color: var(--cr-muted);
            border: 1.5px solid var(--cr-border);
        }
        .cr-btn-ghost:hover { border-color: var(--cr-accent); color: var(--cr-accent); background: var(--cr-accent-lt); }

        /* �????,?�????,? Next ID chip �????,?�????,? */
        .cr-next-id {
            display: inline-flex; align-items: center; gap: 6px;
            background: #f0f2f9; border-radius: 20px;
            padding: 4px 12px; margin-left: 10px;
            font-size: 12px; color: var(--cr-muted);
            font-weight: 500;
        }
        .cr-next-id span { color: var(--cr-accent); font-weight: 700; }

        /* �????,?�????,? Spinner on submit �????,?�????,? */
        .cr-spinner {
            display: none;
            width: 16px; height: 16px;
            border: 2px solid rgba(255,255,255,.4);
            border-top-color: #fff;
            border-radius: 50%;
            animation: spin .6s linear infinite;
        }
        .cr-btn.loading .cr-spinner { display: block; }
        .cr-btn.loading .cr-btn-label { display: none; }

        /* �????,?�????,? Animations �????,?�????,? */
        @keyframes fadeUp {
            from { opacity: 0; transform: translateY(18px); }
            to   { opacity: 1; transform: translateY(0); }
        }
        @keyframes spin { to { transform: rotate(360deg); } }
    </style>
</head>

<body>
    <div class="wrapper">

        <?php include_once 'templates/barra.php' ?>
        <?php include_once 'templates/headder.php' ?>

        <?php
        include_once 'api/adminCatalogos.php';
        include_once 'api/adminArticulos.php';
        $adminArticulos  = new AdministradorArticulos();
        $adminCatalogos  = new AdministradorCatalogos();
        $ultimoId        = $adminArticulos->obtenerUltimoArticuloInsertado();
        $familias        = $adminCatalogos->listarFamilias(true);
        $familias        = json_decode($familias);
        $esEdicion       = isset($_GET['id']);

        if ($esEdicion) {
            $articulo = $adminArticulos->dameArticulo($_GET['id']);
            $articulo = json_decode($articulo);
        }
        ?>

        <!-- Search Modal -->
        <div class="modal fade" id="searchModal" tabindex="-1" aria-labelledby="searchModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content bg-transparent">
                    <form>
                        <div class="card mb-1">
                            <div class="px-3 py-2 d-flex flex-row align-items-center" id="top-search">
                                <i class="ri-search-line fs-22"></i>
                                <input type="search" class="form-control border-0" id="search-modal-input" placeholder="Search for actions, people,">
                                <button type="submit" class="btn p-0" data-bs-dismiss="modal" aria-label="Close">[esc]</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- ========================================================= -->
        <!-- Page Content -->
        <!-- ========================================================= -->
        <div class="page-content">

            <!-- Breadcrumb -->
            <div class="page-title-head d-flex align-items-center gap-2">
                <div class="flex-grow-1" style="display:flex;align-items:center;gap:8px;">
                    <h4 class="fs-18 fw-bold mb-0">
                        <?= $esEdicion ? 'Editar artículo' : 'Nuevo artículo' ?>
                    </h4>
                    <?php if (!$esEdicion): ?>
                        <span class="cr-next-id">Siguiente ID <span><?= $ultimoId ?></span></span>
                    <?php endif; ?>
                </div>
                <div class="text-end">
                    <ol class="breadcrumb m-0 py-0 fs-13">
                        <li class="breadcrumb-item"><a href="javascript:void(0);">Almacén</a></li>
                        <li class="breadcrumb-item"><a href="javascript:void(0);">Artículos</a></li>
                        <li class="breadcrumb-item active"><?= $esEdicion ? 'Editar' : 'Nuevo' ?></li>
                    </ol>
                </div>
            </div>

            <div class="page-container">
                <div class="row justify-content-center">
                    <div class="col-lg-8 col-xl-7">

                        <div class="cr-card">

                            <!-- Card header -->
                            <div class="cr-card-header">
                                <div class="cr-icon">
                                    <i class="<?= $esEdicion ? 'ri-edit-2-line' : 'ri-add-line' ?>"></i>
                                </div>
                                <div>
                                    <h4><?= $esEdicion ? 'Editar artículo' : 'Registrar artículo' ?></h4>
                                    <p style="margin:0;font-size:12px;color:var(--cr-muted);">
                                        <?= $esEdicion ? 'Modifica los datos del artículo seleccionado' : 'Completa la información del nuevo artículo' ?>
                                    </p>
                                </div>
                                <span class="cr-badge <?= $esEdicion ? 'cr-badge-edit' : 'cr-badge-new' ?>">
                                    <?= $esEdicion ? 'Editando' : 'Nuevo' ?>
                                </span>
                            </div>

                            <!-- Form -->
                            <div class="cr-card-body">
                                <form id="formArticulo" autocomplete="off">

                                    <!-- �????,?�????,? Identificaci�n �????,?�????,? -->
                                    <div class="cr-section">
                                        <p class="cr-section-label">Identificación</p>

                                        <div class="cr-field">
                                            <label class="cr-label" for="nombre_articulo">
                                                Nombre del artículo <span class="req">*</span>
                                            </label>
                                            <input
                                                type="text"
                                                id="nombre_articulo"
                                                name="nombre"
                                                class="cr-input"
                                                placeholder="Descripción del artículo"
                                                value="<?= $esEdicion ? htmlspecialchars($articulo[0]->nombre) : '' ?>"
                                                required>
                                        </div>

                                        <div class="cr-grid-2">
                                            <div class="cr-field">
                                                <label class="cr-label" for="id_familia_articulo">
                                                    Familia <span class="req">*</span>
                                                </label>
                                                <div class="cr-select-wrap">
                                                    <select
                                                        id="id_familia_articulo"
                                                        name="id_familia"
                                                        class="cr-select"
                                                        onchange="cargarSubFamilias()"
                                                        required>
                                                        <option value="">Selecciona una familia</option>
                                                        <?php foreach ($familias as $familia) : ?>
                                                            <option
                                                                value="<?= $familia->id ?>"
                                                                <?= ($esEdicion && $articulo[0]->id_familia == $familia->id) ? 'selected' : '' ?>>
                                                                <?= htmlspecialchars($familia->nombre) ?>
                                                            </option>
                                                        <?php endforeach; ?>
                                                    </select>
                                                </div>
                                            </div>

                                            <div class="cr-field">
                                                <label class="cr-label" for="id_subfamilia_articulo">Subfamilia</label>
                                                <div class="cr-select-wrap">
                                                    <select
                                                        id="id_subfamilia_articulo"
                                                        name="id_subfamilia"
                                                        class="cr-select"
                                                        onchange="armarSku()">
                                                        <option value="1">Sin familia</option>
                                                    </select>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="cr-field">
                                            <label class="cr-label" for="sku_articulo">SKU / Código interno</label>
                                            <input
                                                type="text"
                                                id="sku_articulo"
                                                name="sku"
                                                class="cr-input"
                                                placeholder="Código interno / SKU"
                                                value="<?= $esEdicion ? htmlspecialchars($articulo[0]->sku) : '' ?>">
                                            <span id="sku_preview" class="hidden">
                                                <i class="ri-barcode-line" style="font-size:13px;"></i>
                                                <span id="sku_preview_text"></span>
                                            </span>
                                        </div>
                                    </div>

                                    <!-- �????,?�????,? Detalles �????,?�????,? -->
                                    <div class="cr-section">
                                        <p class="cr-section-label">Detalles</p>

                                        <div class="cr-field">
                                            <label class="cr-label" for="descripcion_articulo">Descripción</label>
                                            <textarea
                                                id="descripcion_articulo"
                                                name="descripcion"
                                                class="cr-textarea"
                                                placeholder="Descripción detallada del artículo"><?= $esEdicion ? htmlspecialchars($articulo[0]->descripcion) : '' ?></textarea>
                                        </div>

                                        <div class="cr-grid-2">
                                            <div class="cr-field">
                                                <label class="cr-label" for="unidad_medida">Unidad de medida</label>
                                                <input
                                                    type="text"
                                                    id="unidad_medida"
                                                    name="unidad_medida"
                                                    class="cr-input"
                                                    placeholder="Ej. PZA, KG, LT"
                                                    value="<?= $esEdicion ? htmlspecialchars($articulo[0]->unidad_medida) : '' ?>">
                                                <p class="cr-hint">Piezas, kilogramos, litros, etc.</p>
                                            </div>

                                            <div class="cr-field">
                                                <label class="cr-label">Estado</label>
                                                <div class="cr-status-group">
                                                    <div class="cr-status-opt">
                                                        <input type="radio" name="activo" id="activo_1" value="1"
                                                            <?= (!$esEdicion || $articulo[0]->activo == 1) ? 'checked' : '' ?>>
                                        <label for="activo_1">Activo</label>
                                                    </div>
                                                    <div class="cr-status-opt">
                                                        <input type="radio" name="activo" id="activo_0" value="0"
                                                            <?= ($esEdicion && $articulo[0]->activo == 0) ? 'checked' : '' ?>>
                                        <label for="activo_0">Inactivo</label>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- �????,?�????,? Inventario �????,?�????,? -->
                                    <div class="cr-section">
                                        <p class="cr-section-label">Inventario y costos</p>

                                        <div class="cr-grid-2">
                                            <div class="cr-field">
                                                <label class="cr-label" for="inventario_inicial">
                                                    <?= $esEdicion ? 'Inventario actual' : 'Inventario inicial' ?>
                                                </label>
                                                <input
                                                    type="number"
                                                    id="inventario_inicial"
                                                    name="inventario_inicial"
                                                    class="cr-input"
                                                    placeholder="0"
                                                    min="0"
                                       value="<?= $esEdicion && isset($articulo[0]->inventario_inicial) ? (int)round((float)$articulo[0]->inventario_inicial) : '' ?>">
                                            </div>

                                            <div class="cr-field">
                                                <label class="cr-label" for="costo_reposicion">Costo de reposición promedio</label>
                                                <div class="cr-input-group">
                                                    <span class="cr-prefix">$</span>
                                                    <input
                                                        type="number"
                                                        id="costo_reposicion"
                                                        name="costo_reposicion"
                                                        class="cr-input"
                                                        placeholder="0.00"
                                                        step="0.01"
                                                        min="0"
                                                        value="<?= $esEdicion ? $articulo[0]->costo_reposicion : '' ?>">
                                                </div>
                                            </div>
                                        </div>

                                        <div class="cr-grid-2">
                                            <div class="cr-field">
                                                <label class="cr-label" for="consumo_diario">Consumo diario</label>
                                                <input
                                                    type="number"
                                                    id="consumo_diario"
                                                    name="consumo_diario"
                                                    class="cr-input"
                                                    placeholder="0"
                                                    step="0.01"
                                                    min="0"
                                                    value="<?= $esEdicion && isset($articulo[0]->consumo_diario) ? $articulo[0]->consumo_diario : '' ?>">
                                                <p class="cr-hint">Cantidad promedio consumida por día.</p>
                                            </div>

                                            <div class="cr-field">
                                                <label class="cr-label" for="tiempo_reposicion">Tiempo de reposición</label>
                                                <input
                                                    type="number"
                                                    id="tiempo_reposicion"
                                                    name="tiempo_reposicion"
                                                    class="cr-input"
                                                    placeholder="0"
                                                    step="1"
                                                    min="0"
                                                    value="<?= $esEdicion && isset($articulo[0]->tiempo_reposicion) ? (int)$articulo[0]->tiempo_reposicion : '' ?>">
                                                <p class="cr-hint">Días estimados para reabastecer el artículo.</p>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- �????,?�????,? Acciones �????,?�????,? -->
                                    <div class="cr-actions">
                                        <button type="reset" class="cr-btn cr-btn-ghost" id="btnReset">
                                            <i class="ri-refresh-line"></i>
                                            Limpiar
                                        </button>

                                        <button type="submit" class="cr-btn cr-btn-primary" id="btnGuardar">
                                            <div class="cr-spinner"></div>
                                            <span class="cr-btn-label">
                                                <i class="<?= $esEdicion ? 'ri-save-line' : 'ri-add-circle-line' ?>"></i>
                                                <?= $esEdicion ? 'Guardar cambios' : 'Registrar artículo' ?>
                                            </span>
                                        </button>
                                    </div>

                                </form>
                            </div><!-- end card-body -->
                        </div><!-- end cr-card -->

                    </div>
                </div>
            </div><!-- container -->

            <!-- Footer -->
            <?php include 'templates/footer.php'; ?>
        </div>
        <!-- End Page content -->

    </div>
    <!-- END wrapper -->

    <?php include_once 'templates/theme.php' ?>

    <script src="assets/js/vendor.min.js"></script>
    <script src="assets/js/app.js"></script>
    <script src="assets/vendor/apexcharts/apexcharts.min.js"></script>
    <script src="assets/js/pages/dashboard.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        /* �????,?�????,? Constantes PHP �?????T JS �????,?�????,? */
        const ES_EDICION = <?= $esEdicion ? 'true' : 'false' ?>;
        const ULTIMO_ID  = <?= (int)$ultimoId ?>;
        <?php if ($esEdicion): ?>
        const ARTICULO_ID = <?= (int)$_GET['id'] ?>;
        const SUBFAMILIA_ID = <?= isset($articulo[0]->id_subfamilia) ? (int)$articulo[0]->id_subfamilia : 1 ?>;
        <?php endif; ?>

        /* �????,?�????,? Submit �????,?�????,? */
        document.getElementById('formArticulo').addEventListener('submit', function (e) {
            e.preventDefault();

            const btn = document.getElementById('btnGuardar');
            btn.classList.add('loading');
            btn.disabled = true;

            const formData = new FormData(this);
            formData.append('accion', ES_EDICION ? 'actualizarArticulo' : 'altaArticulo');
            if (ES_EDICION) formData.append('id', ARTICULO_ID);

            fetch('api/apiArticulos.php', { method: 'POST', body: formData })
                .then(r => r.json())
                .then(res => {
                    btn.classList.remove('loading');
                    btn.disabled = false;

                    if (res.status === 'success') {
                        Swal.fire({
                title: '¡Listo!',
                            text: res.message,
                            icon: 'success',
                            confirmButtonColor: '#2d3a8c',
                            confirmButtonText: 'Continuar'
                        }).then(() => window.location.reload());
                    } else {
                        Swal.fire({
                            title: 'Error',
                            text: res.message,
                            icon: 'error',
                            confirmButtonColor: '#2d3a8c'
                        });
                    }
                })
                .catch(() => {
                    btn.classList.remove('loading');
                    btn.disabled = false;
                    Swal.fire('Error', 'No se pudo conectar con el servidor.', 'error');
                });
        });

        /* �????,?�????,? Cargar subfamilias �????,?�????,? */
        function cargarSubFamilias(preselect = null) {
            const idFamilia       = document.getElementById('id_familia_articulo').value;
            const subfamiliaSelect = document.getElementById('id_subfamilia_articulo');
            const valorObjetivo = String(preselect ?? subfamiliaSelect.value ?? '1');

            subfamiliaSelect.innerHTML = '<option value="1">Sin familia</option>';
            subfamiliaSelect.value = '1';

            if (!idFamilia) {
                armarSku();
                return;
            }

            fetch('api/apiCatalogos.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: new URLSearchParams({ accion: 'listarSubFamilias', id_familia: idFamilia, soloActivas: 1 })
            })
            .then(r => r.json())
            .then(data => {
                data.forEach(sf => {
                    if (String(sf.id) === '1') return;
                    const opt = document.createElement('option');
                    opt.value = sf.id;
                    opt.textContent = sf.nombre;
                    subfamiliaSelect.appendChild(opt);
                });
                subfamiliaSelect.value = Array.from(subfamiliaSelect.options).some(opt => String(opt.value) === valorObjetivo)
                    ? valorObjetivo
                    : '1';
                armarSku();
            })
            .catch(err => console.error('Error cargando subfamilias:', err));
        }

        /* �????,?�????,? Armar SKU �????,?�????,? */
        function armarSku() {
            const subfamiliaSelect = document.getElementById('id_subfamilia_articulo');
            const skuInput         = document.getElementById('sku_articulo');
            const preview          = document.getElementById('sku_preview');
            const previewText      = document.getElementById('sku_preview_text');

            const sfTexto = subfamiliaSelect.options[subfamiliaSelect.selectedIndex]?.text || '';
            let sku = '';

            if (sfTexto && sfTexto !== 'Selecciona una subfamilia' && sfTexto !== 'Sin familia') {
                sku = sfTexto.substring(0, 3).toUpperCase() + '-000' + ULTIMO_ID;
            }

            if (sku && !ES_EDICION) {
                skuInput.value    = sku;
                previewText.textContent = sku;
                preview.classList.remove('hidden');
            } else if (!ES_EDICION) {
                preview.classList.add('hidden');
            }
        }

        /* �????,?�????,? Precargar subfamilias en edici�n �????,?�????,? */
        <?php if ($esEdicion): ?>
        document.addEventListener('DOMContentLoaded', () => {
            cargarSubFamilias(SUBFAMILIA_ID);
        });
        <?php endif; ?>
    </script>

</body>
</html>
