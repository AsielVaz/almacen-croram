<?php
require_once __DIR__ . '/auth.php';
requerir_autenticacion();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <title>Almacen Croram - Proveedores</title>
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
        include_once 'api/adminProveedores.php';
        $adminProveedores = new AdministradorProveedores();
        $esEdicion = isset($_GET['id']);
        $proveedor = null;
        if ($esEdicion) {
            $resultado = json_decode($adminProveedores->obtenerProveedor($_GET['id'] ?? 0));
            $proveedor = $resultado[0] ?? null;
        }
        ?>
        <div class="page-content">
            <div class="page-title-head d-flex align-items-center gap-2">
                <div class="flex-grow-1">
                    <h4 class="fs-18 fw-bold mb-0"><?= $esEdicion ? 'Editar proveedor' : 'Nuevo proveedor' ?></h4>
                </div>
                <div class="text-end">
                    <ol class="breadcrumb m-0 py-0 fs-13">
                        <li class="breadcrumb-item"><a href="proveedores-ver.php">Proveedores</a></li>
                        <li class="breadcrumb-item active"><?= $esEdicion ? 'Editar proveedor' : 'Nuevo proveedor' ?></li>
                    </ol>
                </div>
            </div>
            <div class="page-container">
                <div class="row">
                    <div class="col-lg-12">
                        <div class="card">
                            <div class="card-header border-bottom border-dashed d-flex align-items-center">
                                <h4 class="header-title">Proveedores</h4>
                            </div>
                            <div class="card-body">
                                <p class="text-muted"><?= $esEdicion ? 'Actualiza la informacion del proveedor seleccionado.' : 'Da de alta un proveedor para crear ordenes de compra.' ?></p>
                                <form id="formProveedor" autocomplete="off">
                                    <div class="mb-2"><label for="nombre_proveedor" class="form-label">Nombre del proveedor <span class="text-danger">*</span></label><div class="row"><div class="col-sm-8"><input type="text" id="nombre_proveedor" name="nombre" class="form-control" placeholder="Razon social o nombre comercial" value="<?= htmlspecialchars($proveedor->nombre ?? '') ?>" required></div></div></div>
                                    <div class="mb-2"><label for="contacto_proveedor" class="form-label">Contacto</label><div class="row"><div class="col-sm-6"><input type="text" id="contacto_proveedor" name="contacto" class="form-control" placeholder="Nombre del contacto" value="<?= htmlspecialchars($proveedor->contacto ?? '') ?>"></div></div></div>
                                    <div class="mb-2"><label for="rfc_proveedor" class="form-label">RFC</label><div class="row"><div class="col-sm-4"><input type="text" id="rfc_proveedor" name="rfc" class="form-control" placeholder="RFC del proveedor" maxlength="20" value="<?= htmlspecialchars($proveedor->rfc ?? '') ?>"></div></div></div>
                                    <div class="mb-2"><label for="domicilio_proveedor" class="form-label">Domicilio completo</label><div class="row"><div class="col-sm-10"><textarea id="domicilio_proveedor" name="domicilio_completo" class="form-control" rows="3" placeholder="Calle, numero, colonia, ciudad, estado, CP"><?= htmlspecialchars($proveedor->domicilio_completo ?? '') ?></textarea></div></div></div>
                                    <div class="mb-2"><label for="telefono_contacto" class="form-label">Telefono contacto</label><div class="row"><div class="col-sm-4"><input type="text" id="telefono_contacto" name="telefono_contacto" class="form-control" placeholder="Telefono fijo" value="<?= htmlspecialchars($proveedor->telefono_contacto ?? '') ?>"></div></div></div>
                                    <div class="mb-2"><label for="movil_contacto" class="form-label">Movil contacto</label><div class="row"><div class="col-sm-4"><input type="text" id="movil_contacto" name="movil_contacto" class="form-control" placeholder="Celular" value="<?= htmlspecialchars($proveedor->movil_contacto ?? '') ?>"></div></div></div>
                                    <div class="mb-2"><label for="mail_proveedor" class="form-label">Correo electronico</label><div class="row"><div class="col-sm-6"><input type="email" id="mail_proveedor" name="mail" class="form-control" placeholder="correo@proveedor.com" value="<?= htmlspecialchars($proveedor->mail ?? '') ?>"></div></div></div>
                                    <div class="mb-2"><label for="credito_proveedor" class="form-label">Otorga credito</label><div class="row"><div class="col-sm-3"><select id="credito_proveedor" name="credito" class="form-control"><option value="0" <?= (($proveedor->credito ?? 0) == 0) ? 'selected' : '' ?>>No</option><option value="1" <?= (($proveedor->credito ?? 0) == 1) ? 'selected' : '' ?>>Si</option></select></div></div></div>
                                    <div class="mb-2"><label for="plazo_credito" class="form-label">Plazo de credito (dias)</label><div class="row"><div class="col-sm-3"><input type="number" id="plazo_credito" name="plazo_credito" class="form-control" min="0" placeholder="Ej. 30" value="<?= htmlspecialchars((string)($proveedor->plazo_credito ?? '')) ?>"></div></div></div>
                                    <div class="mb-2"><label for="activo_proveedor" class="form-label">Estado</label><div class="row"><div class="col-sm-3"><select id="activo_proveedor" name="activo" class="form-control"><option value="1" <?= (($proveedor->activo ?? 1) == 1) ? 'selected' : '' ?>>Activo</option><option value="0" <?= (($proveedor->activo ?? 1) == 0) ? 'selected' : '' ?>>Inactivo</option></select></div></div></div>
                                    <div class="mb-2"><div class="row"><div class="col-sm-12 d-flex justify-content-between"><a href="proveedores-ver.php" class="btn btn-secondary">Cancelar</a><button type="submit" class="btn btn-primary"><?= $esEdicion ? 'Guardar cambios' : 'Guardar proveedor' ?></button></div></div></div>
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
    <script src="assets/vendor/vanilla-wizard/js/wizard.min.js"></script>
    <script src="assets/js/components/form-wizard.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        const ES_EDICION = <?= $esEdicion ? 'true' : 'false' ?>;
        const PROVEEDOR_ID = <?= (int)($_GET['id'] ?? 0) ?>;
        const credito = document.getElementById('credito_proveedor');
        const plazo = document.getElementById('plazo_credito');
        function actualizarPlazo() {
            plazo.disabled = credito.value === '0';
            if (credito.value === '0') plazo.value = 0;
        }
        credito.addEventListener('change', actualizarPlazo);
        actualizarPlazo();
        document.getElementById('formProveedor').addEventListener('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            formData.append('accion', ES_EDICION ? 'actualizarProveedor' : 'altaProveedor');
            if (ES_EDICION) formData.append('id', PROVEEDOR_ID);
            fetch('api/apiProveedores.php', { method: 'POST', body: formData })
                .then(r => r.json())
                .then(res => {
                    if (res.status === 'success') {
                        Swal.fire('Exito', res.message, 'success').then(() => window.location.href = 'proveedores-ver.php');
                    } else {
                        Swal.fire('Error', res.message || 'No se pudo guardar el proveedor', 'error');
                    }
                });
        });
    </script>
</body>
</html>

