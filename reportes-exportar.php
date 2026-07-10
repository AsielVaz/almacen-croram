<?php
require_once __DIR__ . '/auth.php';
requerir_autenticacion();

include_once 'api/adminArticulos.php';
include_once 'api/adminOrdenes.php';
include_once 'api/adminProveedores.php';
include_once 'api/adminCatalogos.php';

$tipo = $_GET['tipo'] ?? '';
$entradaInicio = $_GET['entrada_inicio'] ?? '';
$entradaFin = $_GET['entrada_fin'] ?? '';
$salidaInicio = $_GET['salida_inicio'] ?? '';
$salidaFin = $_GET['salida_fin'] ?? '';
$movimientoInicio = $_GET['movimiento_inicio'] ?? '';
$movimientoFin = $_GET['movimiento_fin'] ?? '';
$tipoMovimiento = $_GET['tipo_movimiento'] ?? '';
$origenMovimiento = $_GET['origen_movimiento'] ?? '';
$idFamilia = (int)($_GET['id_familia'] ?? 0);
$idSubfamilia = (int)($_GET['id_subfamilia'] ?? 0);
$idProveedor = (int)($_GET['id_proveedor'] ?? 0);
$idArea = (int)($_GET['id_area'] ?? 0);

$adminArticulos = new AdministradorArticulos();
$adminOrdenes = new AdministradorOrdenes();
$adminProveedores = new AdministradorProveedores();
$adminCatalogos = new AdministradorCatalogos();

function limpiarNombreArchivo($valor)
{
    $valor = preg_replace('/[^A-Za-z0-9_-]+/', '_', $valor);
    return trim($valor, '_') ?: 'reporte';
}

function exportarExcelHtml($nombreArchivo, $titulo, array $columnas, array $filas)
{
    header('Content-Type: application/vnd.ms-excel; charset=UTF-8');
    header('Content-Disposition: attachment; filename="' . limpiarNombreArchivo($nombreArchivo) . '.xls"');
    header('Pragma: no-cache');
    header('Expires: 0');

    echo "<html><head><meta charset=\"UTF-8\"></head><body>";
    echo '<table border="1">';
    echo '<thead><tr>';
    foreach ($columnas as $columna) {
        echo '<th>' . htmlspecialchars($columna) . '</th>';
    }
    echo '</tr></thead><tbody>';

    foreach ($filas as $fila) {
        echo '<tr>';
        foreach ($fila as $celda) {
            echo '<td>' . htmlspecialchars((string)$celda) . '</td>';
        }
        echo '</tr>';
    }

    echo '</tbody></table>';
    echo "</body></html>";
    exit;
}

switch ($tipo) {
    case 'inventario':
        $articulos = json_decode($adminArticulos->listarArticulosReporteGeneral(false, $idFamilia, $idSubfamilia), true) ?: [];
        $nombreFamilia = '';
        $nombreSubfamilia = '';

        if ($idFamilia > 0) {
            $familia = json_decode($adminCatalogos->obtenerFamilia($idFamilia), true);
            $nombreFamilia = $familia[0]['nombre'] ?? '';
        }

        if ($idSubfamilia > 0) {
            $subfamilia = json_decode($adminCatalogos->obtenerSubfamilia($idSubfamilia), true);
            $nombreSubfamilia = $subfamilia[0]['nombre'] ?? '';
        }

        $filas = [];
        foreach ($articulos as $articulo) {
            $existenciaActual = (float)($articulo['cantidad'] ?? 0);
            $totalEntradas = (float)($articulo['total_entradas'] ?? 0);
            $totalSalidas = (float)($articulo['total_salidas'] ?? 0);
            $saldoInicial = $existenciaActual - $totalEntradas + $totalSalidas;

            $filas[] = [
                $articulo['id'] ?? '',
                $articulo['sku'] ?? '',
                $articulo['nombre'] ?? '',
                $articulo['familia'] ?? '',
                $articulo['subfamilia'] ?? '',
                $articulo['descripcion'] ?? '',
                $articulo['ubicacion'] ?? '',
                $articulo['tipo_articulo'] ?? 'NUEVO',
                $articulo['unidad_medida'] ?? '',
                number_format($saldoInicial, 0, '.', ''),
                number_format($totalEntradas, 0, '.', ''),
                number_format($totalSalidas, 0, '.', ''),
                number_format($existenciaActual, 0, '.', ''),
                number_format((float)($articulo['valor_inventario'] ?? 0), 2, '.', ''),
                number_format((float)($articulo['costo_por_unidad'] ?? 0), 2, '.', ''),
                number_format((float)($articulo['precio_promedio_compra'] ?? 0), 2, '.', ''),
                ((int)($articulo['activo'] ?? 0) === 1) ? 'Activo' : 'Inactivo',
            ];
        }

        if ($nombreFamilia !== '' || $nombreSubfamilia !== '') {
            array_unshift($filas, [
                '',
                '',
                '',
                $nombreFamilia !== '' ? $nombreFamilia : 'Todas',
                $nombreSubfamilia !== '' ? $nombreSubfamilia : 'Todas',
                '',
                '',
                '',
                '',
                '',
                '',
                '',
                '',
                '',
                '',
                '',
                '',
            ]);
        }

        exportarExcelHtml(
            'reporte_inventario_' . ($idFamilia ?: 'todas') . '_' . ($idSubfamilia ?: 'todas'),
            'Inventario',
            ['ID', 'SKU', 'Articulo', 'Familia', 'Subfamilia', 'Descripcion', 'Ubicacion', 'Condicion', 'Unidad', 'Saldo inicial', 'Entradas', 'Salidas', 'Existencia', 'Valor inventario', 'Costo por unidad', 'Precio promedio', 'Estado'],
            $filas
        );
        break;

    case 'proveedores':
        $proveedores = json_decode($adminProveedores->listarProveedores(false), true) ?: [];
        $filas = [];
        foreach ($proveedores as $proveedor) {
            $filas[] = [
                $proveedor['id'] ?? '',
                $proveedor['nombre'] ?? '',
                $proveedor['contacto'] ?? '',
                $proveedor['mail'] ?? '',
                $proveedor['rfc'] ?? '',
                ((int)($proveedor['credito'] ?? 0) === 1) ? 'Si' : 'No',
                (int)($proveedor['plazo_credito'] ?? 0),
                ((int)($proveedor['activo'] ?? 0) === 1) ? 'Activo' : 'Inactivo',
            ];
        }

        exportarExcelHtml(
            'reporte_proveedores',
            'Proveedores',
            ['ID', 'Nombre', 'Contacto', 'Correo', 'RFC', 'Credito', 'Plazo', 'Estado'],
            $filas
        );
        break;

    case 'obsoletos':
        $articulos = json_decode($adminArticulos->listarArticulosObsoletos(12), true) ?: [];
        $filas = [];
        foreach ($articulos as $articulo) {
            $filas[] = [
                $articulo['id'] ?? '',
                $articulo['sku'] ?? '',
                $articulo['nombre'] ?? '',
                $articulo['familia'] ?? '',
                $articulo['subfamilia'] ?? '',
                $articulo['ubicacion'] ?? '',
                number_format((float)($articulo['cantidad'] ?? 0), 0, '.', ''),
                number_format((float)($articulo['valor_inventario'] ?? 0), 2, '.', ''),
                number_format((float)($articulo['costo_por_unidad'] ?? 0), 2, '.', ''),
                $articulo['ultimo_movimiento'] ?? 'Sin movimiento',
            ];
        }

        exportarExcelHtml(
            'reporte_articulos_obsoletos',
            'Articulos obsoletos',
            ['ID', 'SKU', 'Articulo', 'Familia', 'Subfamilia', 'Ubicacion', 'Unidades', 'Valor inventario', 'Costo por unidad', 'Ultimo movimiento'],
            $filas
        );
        break;

    case 'entradas':
        $ordenesEntrada = json_decode($adminOrdenes->listarEntradasDetalle($entradaInicio, $entradaFin), true) ?: [];
        $filas = [];
        foreach ($ordenesEntrada as $entrada) {
            $filas[] = [
                $entrada['folio'] ?? '',
                $entrada['fecha_orden'] ?? '',
                $entrada['proveedor'] ?? '',
                $entrada['sku'] ?? '',
                $entrada['articulo'] ?? '',
                $entrada['descripcion'] ?? '',
                $entrada['ubicacion'] ?? '',
                number_format((float)($entrada['cantidad'] ?? 0), 0, '.', ''),
                number_format((float)($entrada['precio_unitario'] ?? 0), 2, '.', ''),
                number_format((float)($entrada['subtotal'] ?? 0), 2, '.', ''),
            ];
        }

        exportarExcelHtml(
            'reporte_ordenes_entrada_' . ($entradaInicio ?: 'inicio') . '_' . ($entradaFin ?: 'fin'),
            'Ordenes de entrada',
            ['Folio', 'Fecha', 'Proveedor', 'SKU', 'Articulo', 'Descripcion', 'Ubicacion', 'Unidades', 'Precio unitario', 'Total compra'],
            $filas
        );
        break;

    case 'salidas':
        $ordenesSalida = json_decode($adminOrdenes->listarSalidasDetalle($salidaInicio, $salidaFin, $idArea), true) ?: [];
        $filas = [];
        foreach ($ordenesSalida as $salida) {
            $filas[] = [
                $salida['folio'] ?? '',
                $salida['fecha_salida'] ?? '',
                $salida['area'] ?? '',
                $salida['sku'] ?? '',
                $salida['articulo'] ?? '',
                $salida['descripcion'] ?? '',
                $salida['ubicacion'] ?? '',
                $salida['nota'] ?? '',
                number_format((float)($salida['cantidad'] ?? 0), 0, '.', ''),
                number_format((float)($salida['costo_peps'] ?? 0), 2, '.', ''),
                number_format((float)($salida['subtotal'] ?? 0), 2, '.', ''),
            ];
        }

        exportarExcelHtml(
            'reporte_ordenes_salida_' . ($salidaInicio ?: 'inicio') . '_' . ($salidaFin ?: 'fin'),
            'Ordenes de salida',
            ['Folio', 'Fecha', 'Area', 'SKU', 'Articulo', 'Descripcion', 'Ubicacion', 'Observacion', 'Unidades', 'Costo PEPS', 'Total'],
            $filas
        );
        break;

    case 'compras_proveedor':
        $compras = json_decode($adminOrdenes->listarComprasPorProveedor($entradaInicio, $entradaFin, $idProveedor), true) ?: [];
        $filas = [];
        foreach ($compras as $compra) {
            $filas[] = [
                $compra['id'] ?? '',
                $compra['folio'] ?? '',
                $compra['fecha_orden'] ?? '',
                $compra['proveedor'] ?? '',
                $compra['estatus'] ?? '',
                $compra['sku'] ?? '',
                $compra['articulo'] ?? '',
                $compra['ubicacion'] ?? '',
                number_format((float)($compra['cantidad'] ?? 0), 0, '.', ''),
                number_format((float)($compra['precio_unitario'] ?? 0), 2, '.', ''),
                number_format((float)($compra['subtotal'] ?? 0), 2, '.', ''),
            ];
        }

        exportarExcelHtml(
            'compras_por_proveedor_' . ($entradaInicio ?: 'inicio') . '_' . ($entradaFin ?: 'fin'),
            'Compras por proveedor',
            ['ID orden', 'Folio', 'Fecha', 'Proveedor', 'Estatus', 'SKU', 'Articulo', 'Ubicacion', 'Cantidad', 'Precio', 'Subtotal'],
            $filas
        );
        break;

    case 'log_inventario':
        $movimientos = json_decode($adminArticulos->listarInventarioMovimientos($movimientoInicio, $movimientoFin, $tipoMovimiento, $origenMovimiento), true) ?: [];
        $filas = [];
        foreach ($movimientos as $movimiento) {
            $filas[] = [
                $movimiento['id'] ?? '',
                $movimiento['created_at'] ?? '',
                $movimiento['tipo'] ?? '',
                $movimiento['origen'] ?? '',
                $movimiento['id_referencia'] ?? '',
                $movimiento['id_producto'] ?? '',
                $movimiento['sku'] ?? '',
                $movimiento['articulo'] ?? '',
                $movimiento['descripcion'] ?? '',
                $movimiento['ubicacion'] ?? '',
                $movimiento['unidad_medida'] ?? '',
                $movimiento['tipo_articulo'] ?? '',
                number_format((float)($movimiento['cantidad'] ?? 0), 0, '.', ''),
                $movimiento['id_usuario'] ?? '',
            ];
        }

        exportarExcelHtml(
            'log_inventario_' . ($movimientoInicio ?: 'inicio') . '_' . ($movimientoFin ?: 'fin'),
            'Log de Inventario',
            ['ID', 'Fecha', 'Tipo', 'Origen', 'ID referencia', 'ID producto', 'SKU', 'Articulo', 'Descripcion', 'Ubicacion', 'Unidad', 'Condicion', 'Cantidad', 'ID usuario'],
            $filas
        );
        break;

    case 'consumos_area':
        $consumos = json_decode($adminOrdenes->listarConsumosPorArea($salidaInicio, $salidaFin, $idArea), true) ?: [];
        $filas = [];
        foreach ($consumos as $consumo) {
            $filas[] = [
                $consumo['area'] ?? '',
                $consumo['familia'] ?? '',
                $consumo['subfamilia'] ?? '',
                $consumo['sku'] ?? '',
                $consumo['articulo'] ?? '',
                $consumo['ubicacion'] ?? '',
                number_format((float)($consumo['cantidad'] ?? 0), 0, '.', ''),
                number_format((float)($consumo['costo_peps_promedio'] ?? 0), 2, '.', ''),
                number_format((float)($consumo['total'] ?? 0), 2, '.', ''),
            ];
        }

        exportarExcelHtml(
            'consumos_por_area_' . ($salidaInicio ?: 'inicio') . '_' . ($salidaFin ?: 'fin'),
            'Consumos por area',
            ['Area', 'Familia', 'Subfamilia', 'SKU', 'Articulo', 'Ubicacion', 'Unidades', 'Costo PEPS promedio', 'Total'],
            $filas
        );
        break;
}

http_response_code(400);
echo 'Tipo de reporte no válido.';
