<?php
require_once __DIR__ . '/auth.php';
requerir_autenticacion();
requerir_permiso('configuraciones_administrar');
require_once __DIR__ . '/api/adminConfiguraciones.php';

$_SESSION['configuraciones_csrf'] = $_SESSION['configuraciones_csrf'] ?? bin2hex(random_bytes(32));
$error = '';
$configuraciones = [];
$disponible = false;
try {
    $admin = new AdministradorConfiguraciones();
    if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
        $token = $_POST['csrf_token'] ?? '';
        if (!is_string($token) || !hash_equals($_SESSION['configuraciones_csrf'], $token)) {
            http_response_code(403);
            throw new InvalidArgumentException('La sesión del formulario venció. Recarga la página e intenta de nuevo.');
        }
        $valores = $_POST['configuraciones'] ?? null;
        if (!is_array($valores) || !$valores) {
            throw new InvalidArgumentException('No se recibieron configuraciones para guardar.');
        }
        $admin->guardar($valores, usuario_id_actual());
        $_SESSION['configuraciones_guardadas'] = true;
        header('Location: configuraciones.php');
        exit;
    }
    $configuraciones = $admin->listar();
    $disponible = true;
} catch (InvalidArgumentException $e) {
    $error = $e->getMessage();
    $configuraciones = isset($admin) ? $admin->listar() : [];
    $disponible = true;
} catch (Throwable $e) {
    http_response_code(503);
    $error = 'No se pudieron cargar o guardar las configuraciones. Intenta de nuevo más tarde.';
    error_log('Error al consultar o guardar configuraciones del sistema.');
}
$guardadas = !empty($_SESSION['configuraciones_guardadas']);
unset($_SESSION['configuraciones_guardadas']);
function config_html($valor): string { return htmlspecialchars((string)$valor, ENT_QUOTES, 'UTF-8'); }
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Almacén Croram - Configuraciones</title>
    <link rel="shortcut icon" href="favicon.png">
    <link href="assets/css/vendor.min.css" rel="stylesheet">
    <link href="assets/css/app.min.css" rel="stylesheet" id="app-style">
    <link href="assets/css/icons.min.css" rel="stylesheet">
    <script src="assets/js/config.js"></script>
</head>
<body>
<div class="wrapper">
    <?php include __DIR__ . '/templates/barra.php'; ?>
    <?php include __DIR__ . '/templates/headder.php'; ?>
    <div class="page-content">
        <div class="page-title-head"><h4 class="fs-18 fw-bold mb-0">Configuraciones</h4></div>
        <div class="page-container">
            <?php if ($guardadas): ?><div class="alert alert-success" role="status">Configuraciones guardadas. Los cambios se aplican al abrir o recargar la orden y al intentar confirmarla.</div><?php endif; ?>
            <?php if ($error !== ''): ?><div class="alert alert-danger" role="alert"><?= config_html($error) ?></div><?php endif; ?>
            <div class="card"><div class="card-body">
                <h4 class="header-title">Opciones del sistema</h4>
                <p class="text-muted">Solo los administradores pueden modificar estas opciones.</p>
                <?php if ($disponible && $configuraciones): ?>
                <form method="post">
                    <input type="hidden" name="csrf_token" value="<?= config_html($_SESSION['configuraciones_csrf']) ?>">
                    <?php $grupo = null; foreach ($configuraciones as $indice => $config): ?>
                        <?php if ($grupo !== $config['grupo']): $grupo = $config['grupo']; ?>
                            <h5 class="mt-4 mb-3"><?= config_html($grupo) ?></h5>
                        <?php endif; ?>
                        <div class="border rounded p-3 mb-3">
                            <?php $nombre = 'configuraciones[' . $config['clave'] . ']'; $id = 'config-' . $indice; ?>
                            <?php if ($config['tipo'] === 'booleano'): ?>
                                <input type="hidden" name="<?= config_html($nombre) ?>" value="0">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" role="switch" id="<?= $id ?>" name="<?= config_html($nombre) ?>" value="1" <?= $config['valor'] === '1' ? 'checked' : '' ?> aria-describedby="<?= $id ?>-descripcion">
                                    <label class="form-check-label fw-semibold" for="<?= $id ?>"><?= config_html($config['etiqueta']) ?></label>
                                </div>
                            <?php else: ?>
                                <label class="form-label fw-semibold" for="<?= $id ?>"><?= config_html($config['etiqueta']) ?></label>
                                <input class="form-control" type="<?= $config['tipo'] === 'entero' ? 'number' : 'text' ?>" <?= $config['tipo'] === 'entero' ? 'step="1" required' : '' ?> id="<?= $id ?>" name="<?= config_html($nombre) ?>" value="<?= config_html($config['valor']) ?>" aria-describedby="<?= $id ?>-descripcion">
                            <?php endif; ?>
                            <p class="text-muted mb-0 mt-2" id="<?= $id ?>-descripcion"><?= config_html($config['descripcion']) ?></p>
                        </div>
                    <?php endforeach; ?>
                    <button type="submit" class="btn btn-primary">Guardar configuraciones</button>
                </form>
                <?php elseif ($disponible): ?>
                    <p class="text-muted mb-0">Aún no hay configuraciones disponibles.</p>
                <?php endif; ?>
            </div></div>
        </div>
        <?php include __DIR__ . '/templates/footer.php'; ?>
    </div>
</div>
<?php include __DIR__ . '/templates/theme.php'; ?>
<script src="assets/js/vendor.min.js"></script>
<script src="assets/js/app.js"></script>
</body>
</html>
