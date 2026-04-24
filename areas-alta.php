<?php
require_once __DIR__ . '/auth.php';
requerir_autenticacion();
include_once 'api/adminAreas.php';
include_once 'api/adminUsuarios.php';

$adminAreas = new AdministradorAreas();
$adminUsuarios = new AdministradorUsuarios();
$esEdicion = isset($_GET['id']);
$areaEdit = null;

if ($esEdicion) {
    $resultado = json_decode($adminAreas->obtenerArea($_GET['id'] ?? 0), true) ?: [];
    $areaEdit = $resultado[0] ?? null;
}

$usuarios = json_decode($adminUsuarios->listarUsuarios(true), true) ?: [];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8" />
    <title>Almacén Croram - Áreas</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta content="Sistema de inventario de CRORAM." name="description" />
    <meta content="HoppingJet Studio." name="author" />
    <link rel="shortcut icon" href="favicon.png">
    <link href="assets/css/vendor.min.css" rel="stylesheet" type="text/css" />
    <link href="assets/css/app.min.css" rel="stylesheet" type="text/css" id="app-style" />
    <link href="assets/css/icons.min.css" rel="stylesheet" type="text/css" />
    <script src="assets/js/config.js"></script>
</head>
<body>
<div class="wrapper">
    <?php include_once 'templates/barra.php'; ?>
    <?php include_once 'templates/headder.php'; ?>
    <div class="page-content">
        <div class="page-title-head d-flex align-items-center gap-2">
            <div class="flex-grow-1"><h4 class="fs-18 fw-bold mb-0"><?= $esEdicion ? 'Editar área' : 'Nueva área' ?></h4></div>
            <div class="text-end"><ol class="breadcrumb m-0 py-0 fs-13"><li class="breadcrumb-item"><a href="areas-ver.php">Áreas</a></li><li class="breadcrumb-item active"><?= $esEdicion ? 'Editar' : 'Nuevo' ?></li></ol></div>
        </div>

        <div class="page-container">
            <div class="card">
                <div class="card-body">
                    <div class="mb-3">
                        <h4 class="header-title"><?= $esEdicion ? 'Actualizar área' : 'Registrar área' ?></h4>
                        <p class="text-muted mb-0"><?= $esEdicion ? 'Modifica la información del área seleccionada.' : 'Da de alta un área y asigna a un usuario como encargado.' ?></p>
                    </div>

                    <form id="formArea" autocomplete="off">
                        <div class="mb-3">
                            <label for="nombre" class="form-label">Nombre del área</label>
                            <input type="text" id="nombre" name="nombre" class="form-control" value="<?= htmlspecialchars($areaEdit['nombre'] ?? '') ?>" required>
                        </div>

                        <div class="mb-3">
                            <label for="presupuesto" class="form-label">Presupuesto asignado</label>
                            <input type="number" step="0.01" min="0" id="presupuesto" name="presupuesto" class="form-control" value="<?= htmlspecialchars((string)($areaEdit['presupuesto'] ?? '')) ?>" required>
                        </div>

                        <div class="mb-4">
                            <label for="id_encargado" class="form-label">Encargado</label>
                            <select id="id_encargado" name="id_encargado" class="form-select" required>
                                <option value="">Seleccione un usuario</option>
                                <?php foreach ($usuarios as $usuario): ?>
                                    <option value="<?= (int)$usuario['id'] ?>" <?= ((int)($areaEdit['id_encargado'] ?? 0) === (int)$usuario['id']) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($usuario['nombre'] ?: $usuario['usuario']) ?><?= !empty($usuario['email']) ? ' - ' . htmlspecialchars($usuario['email']) : '' ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="d-flex justify-content-between">
                            <a href="areas-ver.php" class="btn btn-secondary">Cancelar</a>
                            <button type="submit" class="btn btn-primary"><?= $esEdicion ? 'Guardar cambios' : 'Guardar área' ?></button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <?php include 'templates/footer.php'; ?>
    </div>
</div>
<?php include_once 'templates/theme.php'; ?>
<script src="assets/js/vendor.min.js"></script>
<script src="assets/js/app.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
const ES_EDICION = <?= $esEdicion ? 'true' : 'false' ?>;
const AREA_ID = <?= (int)($_GET['id'] ?? 0) ?>;

document.getElementById('formArea').addEventListener('submit', async (event) => {
    event.preventDefault();
    const formData = new FormData(event.target);
    formData.append('accion', ES_EDICION ? 'actualizarArea' : 'altaArea');

    if (ES_EDICION) {
        formData.append('id', AREA_ID);
    }

    const response = await fetch('api/apiAreas.php', { method: 'POST', body: formData });
    const data = await response.json();

    if (data.status === 'success') {
        await Swal.fire({ icon: 'success', title: 'Guardado', text: data.message });
        window.location.href = 'areas-ver.php';
        return;
    }

    Swal.fire({ icon: 'error', title: 'Error', text: data.message || 'No fue posible guardar el área' });
});
</script>
</body>
</html>
