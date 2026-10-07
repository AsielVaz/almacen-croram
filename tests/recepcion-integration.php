<?php
// Run only against a disposable LOCAL MySQL database. No application credentials.
// TEST_MYSQL_SOCKET, TEST_MYSQL_USER and TEST_MYSQL_PASSWORD configure the local server.
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
$socket = getenv('TEST_MYSQL_SOCKET') ?: '/Applications/MAMP/tmp/mysql/mysql.sock';
$user = getenv('TEST_MYSQL_USER') ?: 'root';
$password = getenv('TEST_MYSQL_PASSWORD') ?: 'root';
$db = new mysqli('localhost', $user, $password, '', 0, $socket);
$name = 'croram_test_' . bin2hex(random_bytes(6));
$root = dirname(__DIR__);
$tmp = sys_get_temp_dir() . '/' . $name;
mkdir($tmp, 0700); mkdir("$tmp/api");
function check($condition, $message) {
    if (!$condition) throw new RuntimeException($message);
}
function requestApi($role, $post) {
    global $tmp;
    $code = '<?php session_save_path(' . var_export($tmp, true) . '); session_id(' . var_export('test-' . $role, true) . ');'
        . 'require ' . var_export("$tmp/auth.php", true) . ';'
        . '$_SESSION["usuario_auth"]=["id"=>1,"rol"=>' . var_export($role, true) . '];'
        . '$_POST=' . var_export($post, true) . ';'
        . 'require ' . var_export("$tmp/api/apiOrdenes.php", true) . ';';
    $process = proc_open([PHP_BINARY], [['pipe','r'],['pipe','w'],['pipe','w']], $pipes, "$tmp/api");
    fwrite($pipes[0], $code); fclose($pipes[0]);
    $out = stream_get_contents($pipes[1]); fclose($pipes[1]);
    $err = stream_get_contents($pipes[2]); fclose($pipes[2]);
    check(proc_close($process) === 0, "PHP error: $err");
    return json_decode($out, true, 512, JSON_THROW_ON_ERROR);
}
function postReceipt($order, $lines, $token) {
    return ['accion'=>'guardarOrdenEntrada','id_orden'=>$order,'productos'=>json_encode($lines), 'request_token'=>$token,'nota'=>"Entrega de prueba: proveedor O'Connor"];
}
function line($id, $quantity, $price = 10) {
    return ['id_detalle'=>$id,'cantidad_recibida'=>$quantity,'precio_real'=>$price];
}
function stock() {
    global $db;
    return (float)$db->query('SELECT COALESCE(SUM(stock),0) FROM inventario')->fetch_row()[0];
}
try {
    $db->query("CREATE DATABASE $name CHARACTER SET utf8 COLLATE utf8_unicode_ci");
    $db->select_db($name); $db->set_charset('utf8mb4');
    $db->query('SET FOREIGN_KEY_CHECKS=0');
    foreach (json_decode(file_get_contents(__DIR__.'/fixtures/recepcion-schema.json'), true) as $sql) $db->query($sql);
    $db->query(file_get_contents($root.'/database/migrations/2026-10-07_cantidades_recepcion.sql'));
    $migration = file_get_contents($root.'/database/migrations/2026-07-24_roles_y_autorizacion_recepcion.sql');
    preg_match('/CREATE TRIGGER.*?END\$\$/s', $migration, $trigger);
    $db->query(substr($trigger[0], 0, -2));
    $db->query("INSERT INTO usuarios (id,nombre,usuario,password,rol) VALUES (1,'Prueba','prueba','no-login','ALMACEN')");
    $db->query("INSERT INTO productos (id,nombre,id_familia,costo_reposicion,consumo_diario,tiempo_reposicion,ubicacion) VALUES (1,'Producto de prueba con descripción larga para revisar el PDF',1,10,0,0,'ESTANTE 2 PISO 3 COLGANTES'),(2,'Segundo producto',1,10,0,0,'A1')");
    for ($id=1; $id<=3; $id++) {
        $db->query("INSERT INTO ordenes_compra(id,folio,fecha_orden,estatus,id_usuario,nota) VALUES ($id,'TEST-$id',CURDATE(),'AUTORIZADA',1,'Prueba')");
    }
    $db->query("INSERT INTO orden_compra_detalle(id,id_orden_compra,id_producto,cantidad,precio_unitario,precio_autorizado,subtotal) VALUES (1,1,1,10,10,10,100),(2,1,1,2,10,10,20),(3,1,2,5,10,10,50),(4,2,1,3,10,10,30),(5,3,1,4,10,10,40)");
    $db->query('SET FOREIGN_KEY_CHECKS=1');
    copy($root.'/auth.php', "$tmp/auth.php");
    foreach (['apiOrdenes.php','adminOrdenes.php','recepcionCompra.php'] as $file) copy("$root/api/$file", "$tmp/api/$file");
    $con = file_get_contents($root.'/api/conector.php');
    $replacement = 'new mysqli(' . implode(',',array_map(fn($v)=>var_export($v,true),['localhost',$user,$password,$name,0,$socket])) . ')';
    $con = preg_replace('/new mysqli\([^;]+\)/', $replacement, $con, 1);
    file_put_contents("$tmp/api/conector.php", $con);

    $valid = [line(1,6.5),line(2,1),line(3,0)];
    check(requestApi('LECTOR',postReceipt(1,$valid,'deny'))['status']==='error','Reader must be denied');
    foreach ([[line(1,11),line(2,1),line(3,0)],[line(1,-1),line(2,1),line(3,0)], [line(1,'bad'),line(2,1),line(3,0)], [line(1,0),line(2,0),line(3,0)], [line(1,2),line(1,1),line(3,0)], [line(1,2)], [line(99,2),line(2,1),line(3,0)]] as $invalid) {
        check(requestApi('ALMACEN',postReceipt(1,$invalid,'retry'))['status']==='error','Invalid receipt accepted');
        check(stock()===0.0,'Invalid request changed inventory');
    }
    check(requestApi('ALMACEN',postReceipt(1,$valid,'retry'))['status']==='success','Warehouse receipt failed');
    check(stock()===7.5,'Received quantities were not used');
    $details=$db->query('SELECT cantidad,cantidad_solicitada FROM orden_compra_detalle WHERE id=1')->fetch_assoc();
    check((float)$details['cantidad']===6.5 && (float)$details['cantidad_solicitada']===10.0,'Requested quantity lost');
    check((int)$db->query('SELECT COUNT(*) FROM inventario_lotes')->fetch_row()[0]===2,'Zero receipt created a lot');
    check((float)$db->query('SELECT SUM(cantidad_disponible) FROM inventario_lotes')->fetch_row()[0]===7.5,'Lots differ from inventory');
    check((float)$db->query('SELECT SUM(cantidad) FROM inventario_movimientos')->fetch_row()[0]===7.5,'Movements differ from inventory');
    check(requestApi('ALMACEN',postReceipt(1,$valid,'retry'))['status']==='error','Same token accepted twice');
    check(requestApi('ALMACEN',postReceipt(1,$valid,'new-token'))['status']==='error','Same order received twice');
    check(stock()===7.5,'Duplicate receipt changed inventory');
    check(requestApi('ALMACEN',postReceipt(2,[line(4,2,12)],'higher-price'))['status']==='success','Staging price increase failed');
    check(stock()===7.5,'Unapproved price increase added inventory');
    check($db->query('SELECT estatus FROM ordenes_compra WHERE id=2')->fetch_row()[0]==='PENDIENTE_AUTORIZACION_RECEPCION','Missing price authorization');
    $approve=['accion'=>'autorizarRecepcionOrdenCompra','id'=>2,'request_token'=>'approve'];
    check(requestApi('ALMACEN',$approve)['status']==='error','Warehouse approved own price increase');
    check(requestApi('ADMIN',$approve)['status']==='success','Admin approval failed');
    check(stock()===9.5,'Approval added wrong quantity');
    check(requestApi('ALMACEN',postReceipt(3,[line(5,4)],'complete'))['status']==='success','Full receipt failed');
    check(stock()===13.5,'Full receipt added wrong quantity');
    echo "PASS: permissions, partial/full/zero lines, decimals, duplicates, rollback/retry, price approval, inventory, lots and movements.\n";
} finally {
    $db->query("DROP DATABASE IF EXISTS $name");
    $files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($tmp, FilesystemIterator::SKIP_DOTS), RecursiveIteratorIterator::CHILD_FIRST);
    foreach ($files as $file) $file->isDir() ? rmdir($file->getPathname()) : unlink($file->getPathname());
    rmdir($tmp);
}
