<?php
require_once __DIR__ . '/auth.php';
requerir_autenticacion();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <title>Almacen Croram - Subfamilias</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta content="Sistema de inventario de CRORAM." name="description" />
    <meta content="HoppingJet Studio." name="author" />
    <link rel="shortcut icon" href="assets/images/favicon.ico">
    <link href="assets/css/vendor.min.css" rel="stylesheet" type="text/css" />
    <link href="assets/css/app.min.css" rel="stylesheet" type="text/css" id="app-style" />
    <link href="assets/css/icons.min.css" rel="stylesheet" type="text/css" />
    <script src="assets/js/config.js"></script>
</head>
<body>
    <div class="wrapper">
        <?php include_once 'templates/barra.php' ?>
        <?php include_once 'templates/headder.php' ?>
        <?php
        include_once 'api/adminCatalogos.php';
        $adminCatalogos = new AdministradorCatalogos();
        $familias = json_decode($adminCatalogos->listarFamilias(true));
        $esEdicion = isset($_GET['id']);
        $subfamilia = null;
        if ($esEdicion) {
            $resultado = json_decode($adminCatalogos->obtenerSubfamilia($_GET['id'] ?? 0));
            $subfamilia = $resultado[0] ?? null;
        }
        ?>
        <div class="page-content">
            <div class="page-title-head d-flex align-items-center gap-2">
                <div class="flex-grow-1">
                    <h4 class="fs-18 fw-bold mb-0"><?= $esEdicion ? 'Editar subfamilia' : 'Nueva subfamilia' ?></h4>
                </div>
                <div class="text-end">
                    <ol class="breadcrumb m-0 py-0 fs-13">
                        <li class="breadcrumb-item"><a href="catalogos-sub-familias.php">Catalogos</a></li>
                        <li class="breadcrumb-item active"><?= $esEdicion ? 'Editar subfamilia' : 'Nueva subfamilia' ?></li>
                    </ol>
                </div>
            </div>
            <div class="page-container">
                <div class="row">
                    <div class="col-lg-12">
                        <div class="card">
                            <div class="card-header border-bottom border-dashed d-flex align-items-center">
                                <h4 class="header-title"><?= $esEdicion ? 'Editar subfamilia' : 'Subfamilias del sistema' ?></h4>
                            </div>
                            <div class="card-body">
                                <p class="text-muted"><?= $esEdicion ? 'Actualiza la subfamilia seleccionada.' : 'Agrega subfamilias para clasificar mejor los articulos.' ?></p>
                                <form id="formSubfamilia" autocomplete="off">
                                    <div class="mb-2">
                                        <label for="id_familia" class="form-label">Familia <span class="text-danger">*</span></label>
                                        <div class="row">
                                            <div class="col-sm-6">
                                                <select id="id_familia" name="id_familia" class="form-control" required>
                                                    <option value="">Selecciona una familia</option>
                                                    <?php foreach ($familias as $familia) : ?>
                                                        <option value="<?= $familia->id ?>" <?= (($subfamilia->id_familia ?? 0) == $familia->id) ? 'selected' : '' ?>><?= htmlspecialchars($familia->nombre) ?></option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="mb-2">
                                        <label for="nombre_subfamilia" class="form-label">Nombre de la subfamilia <span class="text-danger">*</span></label>
                                        <div class="row">
                                            <div class="col-sm-6">
                                                <input type="text" id="nombre_subfamilia" name="nombre" class="form-control" placeholder="Ej. Bebidas, Cables, Tornilleria" value="<?= htmlspecialchars($subfamilia->nombre ?? '') ?>" required>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="mb-2">
                                        <label for="descripcion_subfamilia" class="form-label">Descripcion</label>
                                        <div class="row">
                                            <div class="col-sm-8">
                                                <textarea id="descripcion_subfamilia" name="descripcion" class="form-control" rows="3" placeholder="Descripcion opcional de la subfamilia"><?= htmlspecialchars($subfamilia->descripcion ?? '') ?></textarea>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="mb-2">
                                        <label for="activo_subfamilia" class="form-label">Estado</label>
                                        <div class="row">
                                            <div class="col-sm-4">
                                                <select id="activo_subfamilia" name="activo" class="form-control">
                                                    <option value="1" <?= (($subfamilia->activo ?? 1) == 1) ? 'selected' : '' ?>>Activa</option>
                                                    <option value="0" <?= (($subfamilia->activo ?? 1) == 0) ? 'selected' : '' ?>>Inactiva</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="mb-2">
                                        <div class="row">
                                            <div class="col-sm-12 d-flex justify-content-between">
                                                <a href="catalogos-sub-familias.php" class="btn btn-secondary">Cancelar</a>
                                                <button type="submit" class="btn btn-primary"><?= $esEdicion ? 'Guardar cambios' : 'Guardar subfamilia' ?></button>
                                            </div>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php include 'templates/footer.php'; ?>
        </div>
    </div>
    <?php include_once 'templates/theme.php' ?>
    <script src="assets/js/vendor.min.js"></script>
    <script src="assets/js/app.js"></script>
    <script src="assets/vendor/apexcharts/apexcharts.min.js"></script>
    <script src="assets/js/pages/dashboard.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        const ES_EDICION = <?= $esEdicion ? 'true' : 'false' ?>;
        const SUBFAMILIA_ID = <?= (int)($_GET['id'] ?? 0) ?>;
        document.getElementById('formSubfamilia').addEventListener('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            formData.append('accion', ES_EDICION ? 'actualizarSubFamilia' : 'altaSubFamilia');
            if (ES_EDICION) formData.append('id', SUBFAMILIA_ID);
            fetch('api/apiCatalogos.php', { method: 'POST', body: formData })
                .then(r => r.json())
                .then(res => {
                    if (res.status === 'success') {
                        Swal.fire('Exito', res.message, 'success')
                            .then(() => window.location.href = 'catalogos-sub-familias.php');
                    } else {
                        Swal.fire('Error', res.message || 'No se pudo guardar la subfamilia', 'error');
                    }
                });
        });
    </script>
</body>
</html>

