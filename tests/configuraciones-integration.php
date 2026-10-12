<?php
// Disposable LOCAL database only; application credentials are never used.
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
$socket = getenv('TEST_MYSQL_SOCKET') ?: '/Applications/MAMP/tmp/mysql/mysql.sock';
$user = getenv('TEST_MYSQL_USER') ?: 'root';
$password = getenv('TEST_MYSQL_PASSWORD') ?: 'root';
$db = new mysqli('localhost', $user, $password, '', 0, $socket);
$name = 'croram_config_test_' . bin2hex(random_bytes(6));
$root = dirname(__DIR__); $tmp = sys_get_temp_dir() . '/' . $name;
mkdir($tmp, 0700); mkdir("$tmp/api"); mkdir("$tmp/templates");
function verify($ok, $message) { if (!$ok) throw new RuntimeException($message); }
function runRequest($role, $route, $post = [], $extra = '') {
    global $tmp;
    $code = '<?php session_save_path(' . var_export($tmp,true) . '); require ' . var_export("$tmp/auth.php",true) . ';'
        . '$_SESSION["usuario_auth"]=["id"=>1,"rol"=>' . var_export($role,true) . '];'
        . '$_SESSION["configuraciones_csrf"]="test-token";'
        . '$_SERVER["SCRIPT_NAME"]=' . var_export($route,true) . ';'
        . '$_SERVER["REQUEST_METHOD"]=' . var_export($post ? 'POST' : 'GET',true) . ';'
        . '$_POST=' . var_export($post,true) . ';' . $extra
        . ($route ? 'require ' . var_export("$tmp/$route",true) . ';' : '');
    $proc = proc_open([PHP_BINARY], [['pipe','r'],['pipe','w'],['pipe','w']], $pipes, "$tmp/api");
    fwrite($pipes[0],$code); fclose($pipes[0]);
    $out=stream_get_contents($pipes[1]); fclose($pipes[1]);
    $err=stream_get_contents($pipes[2]); fclose($pipes[2]);
    verify(proc_close($proc)===0, "PHP failed: $err"); return $out;
}
function migrateConfig() {
    global $db,$root;
    $db->multi_query(file_get_contents($root.'/database/migrations/2026-10-12_configuraciones.sql'));
    while ($db->more_results()) $db->next_result();
}
function settingValue() {
    global $db;
    return $db->query("SELECT valor FROM configuraciones WHERE clave='almacen_confirmar_salida_sin_qr'")->fetch_row()[0];
}
try {
    $db->query("CREATE DATABASE $name CHARACTER SET utf8mb4"); $db->select_db($name); $db->set_charset('utf8mb4');
    migrateConfig();
    copy($root.'/auth.php',"$tmp/auth.php"); copy($root.'/configuraciones.php',"$tmp/configuraciones.php");
    foreach (['adminConfiguraciones.php','apiOrdenes.php','adminOrdenes.php','recepcionCompra.php'] as $file) copy("$root/api/$file","$tmp/api/$file");
    foreach (['barra.php','headder.php','footer.php','theme.php'] as $file) copy("$root/templates/$file","$tmp/templates/$file");
    $con=file_get_contents($root.'/api/conector.php');
    $replacement='new mysqli('.implode(',',array_map(fn($v)=>var_export($v,true),['localhost',$user,$password,$name,0,$socket])).')';
    file_put_contents("$tmp/api/conector.php",preg_replace('/new mysqli\([^;]+\)/',$replacement,$con,1));
    $permission='echo usuario_tiene_permiso("ordenes_salida_confirmar_sin_qr") ? "yes" : "no";';
    verify(settingValue()==='1','Default not enabled');
    foreach (['ALMACEN','COMPRAS','ADMIN','CONSUMIDOR'] as $role) verify(runRequest($role,'',[],$permission)==='yes',"Missing permission: $role");
    verify(runRequest('LECTOR','',[],$permission)==='no','Reader gained confirmation');
    $save=['csrf_token'=>'test-token','configuraciones'=>['almacen_confirmar_salida_sin_qr'=>'0']];
    foreach (['ALMACEN','CONSUMIDOR','LECTOR'] as $role) {
        verify(runRequest($role,'configuraciones.php')==='','Non-admin viewed settings');
        runRequest($role,'configuraciones.php',$save);
        verify(settingValue()==='1','Non-admin changed settings');
    }
    verify(strpos(runRequest('ADMIN','configuraciones.php'),'Guardar configuraciones')!==false,'Admin cannot view settings');
    $invalid=$save; $invalid['csrf_token']='bad';
    verify(strpos(runRequest('ADMIN','configuraciones.php',$invalid),'sesión del formulario')!==false,'CSRF not rejected');
    verify(settingValue()==='1','CSRF request changed settings');
    $invalid=$save; $invalid['configuraciones']['almacen_confirmar_salida_sin_qr']='invalid';
    runRequest('ADMIN','configuraciones.php',$invalid); verify(settingValue()==='1','Invalid bool saved');
    $invalid=$save; $invalid['configuraciones']['unknown']='1';
    runRequest('ADMIN','configuraciones.php',$invalid); verify(settingValue()==='1','Unknown key allowed partial save');
    runRequest('ADMIN','configuraciones.php',$save); verify(settingValue()==='0','Admin disable failed');
    verify((int)$db->query('SELECT actualizado_por FROM configuraciones')->fetch_row()[0]===1,'Missing actor');
    migrateConfig(); verify(settingValue()==='0','Migration reset saved setting');
    foreach (['ALMACEN','COMPRAS'] as $role) verify(runRequest($role,'',[],$permission)==='no','Disabled flag ignored');
    foreach (['ADMIN','CONSUMIDOR'] as $role) verify(runRequest($role,'',[],$permission)==='yes','Other role changed');
    $post=['accion'=>'aprovarSalida','id'=>1];
    $denied=json_decode(runRequest('ALMACEN','api/apiOrdenes.php',$post),true);
    verify(($denied['message']??'')==='No tienes permisos para realizar esta acción.','Direct API bypass');
    $save['configuraciones']['almacen_confirmar_salida_sin_qr']='1';
    runRequest('ADMIN','configuraciones.php',$save); verify(settingValue()==='1','Admin enable failed');
    $db->query("CREATE TABLE ordenes_salida (id INT PRIMARY KEY, estatus VARCHAR(30))");
    $db->query("INSERT INTO ordenes_salida VALUES (1,'CANCELADA'),(2,'CONFIRMADA')");
    foreach ([1,2] as $id) {
        $result=json_decode(runRequest('ALMACEN','api/apiOrdenes.php',['accion'=>'aprovarSalida','id'=>$id]),true);
        verify(strpos($result['message']??'', 'cancelada')!==false,'Finalized order accepted');
    }
    $db->query("CREATE TABLE productos (id INT PRIMARY KEY,nombre VARCHAR(100),descripcion TEXT,sku VARCHAR(30),ubicacion VARCHAR(50),costo_reposicion DECIMAL(12,4))");
    $db->query("INSERT INTO productos VALUES (1,'Prueba','','SKU','A1',5)");
    $db->query("CREATE TABLE inventario (id_producto INT PRIMARY KEY,stock DECIMAL(12,4))");
    $db->query("INSERT INTO inventario VALUES (1,10)");
    $db->query("CREATE TABLE inventario_lotes (id INT AUTO_INCREMENT PRIMARY KEY,id_producto INT,id_orden_compra INT NULL,fecha_entrada DATE,cantidad_inicial DECIMAL(12,4),cantidad_disponible DECIMAL(12,4),costo_unitario DECIMAL(12,4))");
    $db->query("CREATE TABLE orden_salida_detalle (id INT PRIMARY KEY,id_orden_salida INT,id_producto INT,cantidad DECIMAL(12,4),costo_unitario DECIMAL(12,4),costo_peps DECIMAL(12,4),subtotal DECIMAL(14,4))");
    $db->query("INSERT INTO ordenes_salida VALUES (3,'CAPTURADA')");
    $db->query("INSERT INTO orden_salida_detalle VALUES (1,3,1,2,0,0,0)");
    $db->query("CREATE TRIGGER salida_test AFTER UPDATE ON ordenes_salida FOR EACH ROW BEGIN IF OLD.estatus <> 'CONFIRMADA' AND NEW.estatus = 'CONFIRMADA' THEN UPDATE inventario i INNER JOIN orden_salida_detalle d ON d.id_producto=i.id_producto SET i.stock=i.stock-d.cantidad WHERE d.id_orden_salida=NEW.id; END IF; END");
    $result=json_decode(runRequest('ALMACEN','api/apiOrdenes.php',['accion'=>'aprovarSalida','id'=>3]),true);
    verify(($result['status']??'')==='success','Warehouse confirmation failed');
    verify((float)$db->query('SELECT stock FROM inventario')->fetch_row()[0]===8.0,'Incorrect stock after confirmation');
    verify((float)$db->query('SELECT SUM(cantidad_disponible) FROM inventario_lotes')->fetch_row()[0]===8.0,'Incorrect lots after confirmation');
    $result=json_decode(runRequest('ALMACEN','api/apiOrdenes.php',['accion'=>'aprovarSalida','id'=>3]),true);
    verify(($result['status']??'')==='error','Duplicate confirmation accepted');
    verify((float)$db->query('SELECT stock FROM inventario')->fetch_row()[0]===8.0,'Duplicate stock deduction');
    $db->query('DROP TABLE configuraciones');
    verify(runRequest('ALMACEN','',[],$permission)==='no','Missing settings fail open');
    echo "PASS: admin-only page and writes, CSRF, validation, audit, migration rerun, enabled/disabled roles, API enforcement, finalized orders and fail-closed.\n";
} finally {
    $db->query("DROP DATABASE IF EXISTS $name");
    $files=new RecursiveIteratorIterator(new RecursiveDirectoryIterator($tmp,FilesystemIterator::SKIP_DOTS),RecursiveIteratorIterator::CHILD_FIRST);
    foreach($files as $file) $file->isDir()?rmdir($file->getPathname()):unlink($file->getPathname());
    rmdir($tmp);
}
