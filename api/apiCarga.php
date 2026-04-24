<?php
require_once __DIR__ . '/../auth.php';
requerir_autenticacion_json();

header('Content-Type: application/json; charset=utf-8');

$db_config = array(
    'host'    => 'localhost',
    'dbname'  => 'grupo465_almacen',
    'user'    => 'grupo465_almacen',
    'pass'    => 'fGJ~9uhTggk%',
    'charset' => 'utf8mb4',
);

function responder($data, $codigo_http) {
    http_response_code($codigo_http);
    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    exit;
}

function limpiar_texto($texto) {
    $texto = trim($texto);
    $texto = preg_replace('/\s+/', ' ', $texto);
    return $texto;
}

function obtener_valor_columna($fila, $indice) {
    return isset($fila[$indice]) ? trim($fila[$indice]) : '';
}

function convertir_numero($valor) {
    $valor = trim($valor);
    if ($valor === '') {
        return 0;
    }

    $valor = str_replace(',', '', $valor);

    if (!is_numeric($valor)) {
        return 0;
    }

    return (float)$valor;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    responder(array(
        'ok' => false,
        'mensaje' => 'MÃ©todo no permitido. Usa POST.'
    ), 405);
}

if (!isset($_FILES['archivo'])) {
    responder(array(
        'ok' => false,
        'mensaje' => 'No se recibiÃ³ el archivo en el campo archivo.'
    ), 400);
}

if ($_FILES['archivo']['error'] !== UPLOAD_ERR_OK) {
    responder(array(
        'ok' => false,
        'mensaje' => 'Error al subir el archivo.',
        'codigo_error' => $_FILES['archivo']['error']
    ), 400);
}

$tmpFile = $_FILES['archivo']['tmp_name'];

if (!is_uploaded_file($tmpFile)) {
    responder(array(
        'ok' => false,
        'mensaje' => 'El archivo recibido no es vÃ¡lido.'
    ), 400);
}

$mysqli = new mysqli(
    $db_config['host'],
    $db_config['user'],
    $db_config['pass'],
    $db_config['dbname']
);

if ($mysqli->connect_error) {
    responder(array(
        'ok' => false,
        'mensaje' => 'Error de conexiÃ³n a la base de datos.',
        'error' => $mysqli->connect_error
    ), 500);
}

$mysqli->set_charset($db_config['charset']);

$handle = fopen($tmpFile, 'r');
if (!$handle) {
    responder(array(
        'ok' => false,
        'mensaje' => 'No se pudo abrir el archivo CSV.'
    ), 400);
}

$primera_fila = fgetcsv($handle);
if ($primera_fila === false) {
    fclose($handle);
    responder(array(
        'ok' => false,
        'mensaje' => 'El archivo CSV estÃ¡ vacÃ­o.'
    ), 400);
}

$sql_producto = "INSERT INTO productos 
    (sku, nombre, descripcion, id_familia, id_subfamilia, unidad_medida, activo, created_at, costo_reposicion)
    VALUES (?, ?, ?, ?, ?, ?, ?, NOW(), ?)";

$stmt_producto = $mysqli->prepare($sql_producto);

if (!$stmt_producto) {
    fclose($handle);
    responder(array(
        'ok' => false,
        'mensaje' => 'No se pudo preparar la consulta de productos.',
        'error' => $mysqli->error
    ), 500);
}

$sql_inventario = "INSERT INTO inventario (id_producto, stock, updated_at)
                   VALUES (?, ?, NOW())";

$stmt_inventario = $mysqli->prepare($sql_inventario);

if (!$stmt_inventario) {
    $stmt_producto->close();
    fclose($handle);
    responder(array(
        'ok' => false,
        'mensaje' => 'No se pudo preparar la consulta de inventario.',
        'error' => $mysqli->error
    ), 500);
}

$insertados = 0;
$errores = array();
$fila_numero = 1;

while (($fila = fgetcsv($handle)) !== false) {
    $fila_numero++;

    $nombre = limpiar_texto(obtener_valor_columna($fila, 0));
    $cantidad_actual = convertir_numero(obtener_valor_columna($fila, 2));
    $ubicacion = limpiar_texto(obtener_valor_columna($fila, 4));
    $unidad_medida = limpiar_texto(obtener_valor_columna($fila, 5));

    if ($nombre === '') {
        $errores[] = array(
            'fila' => $fila_numero,
            'mensaje' => 'El nombre estÃ¡ vacÃ­o, no se insertÃ³.'
        );
        continue;
    }

    $sku = 'NA' . str_pad($fila_numero, 6, '0', STR_PAD_LEFT);
    $descripcion = $ubicacion;
    $id_familia = 1;
    $id_subfamilia = 1;
    $activo = 1;
    $costo_reposicion = 0;

    $mysqli->begin_transaction();

    $stmt_producto->bind_param(
        'sssiisid',
        $sku,
        $nombre,
        $descripcion,
        $id_familia,
        $id_subfamilia,
        $unidad_medida,
        $activo,
        $costo_reposicion
    );

    if (!$stmt_producto->execute()) {
        $mysqli->rollback();
        $errores[] = array(
            'fila' => $fila_numero,
            'nombre' => $nombre,
            'mensaje' => 'Error al insertar producto.',
            'error' => $stmt_producto->error
        );
        continue;
    }

    $id_producto = $mysqli->insert_id;

    $stmt_inventario->bind_param(
        'id',
        $id_producto,
        $cantidad_actual
    );

    if (!$stmt_inventario->execute()) {
        $mysqli->rollback();
        $errores[] = array(
            'fila' => $fila_numero,
            'nombre' => $nombre,
            'id_producto' => $id_producto,
            'mensaje' => 'Producto insertado pero fallÃ³ inventario.',
            'error' => $stmt_inventario->error
        );
        continue;
    }

    $mysqli->commit();
    $insertados++;
}

$stmt_producto->close();
$stmt_inventario->close();
fclose($handle);
$mysqli->close();

responder(array(
    'ok' => true,
    'mensaje' => 'Proceso terminado.',
    'insertados' => $insertados,
    'errores' => $errores,
    'total_errores' => count($errores)
), 200);
?>
