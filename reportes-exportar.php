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
$idFamilia = (int)($_GET['id_familia'] ?? 0);
$idSubfamilia = (int)($_GET['id_subfamilia'] ?? 0);

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
            $filas[] = [
                $articulo['id'] ?? '',
                $articulo['sku'] ?? '',
                $articulo['nombre'] ?? '',
                $articulo['familia'] ?? '',
                $articulo['subfamilia'] ?? '',
                $articulo['descripcion'] ?? '',
                $articulo['unidad_medida'] ?? '',
                number_format((float)($articulo['cantidad'] ?? 0), 0, '.', ''),
                number_format((float)($articulo['total_entradas'] ?? 0), 0, '.', ''),
                number_format((float)($articulo['total_salidas'] ?? 0), 0, '.', ''),
                (int)($articulo['total_movimientos'] ?? 0),
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
            ]);
        }

        exportarExcelHtml(
            'reporte_inventario_' . ($idFamilia ?: 'todas') . '_' . ($idSubfamilia ?: 'todas'),
            'Inventario',
            ['ID', 'SKU', 'Articulo', 'Familia', 'Subfamilia', 'Descripcion', 'Unidad', 'Existencia', 'Entradas', 'Salidas', 'Movimientos', 'Precio promedio', 'Estado'],
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

    case 'entradas':
        $ordenesEntrada = json_decode($adminOrdenes->listarOrdenesCompra(null, $entradaInicio, $entradaFin), true) ?: [];
        $filas = [];
        foreach ($ordenesEntrada as $orden) {
            $filas[] = [
                $orden['id'] ?? '',
                $orden['folio'] ?? '',
                $orden['nombre_proveedor'] ?? '',
                $orden['fecha_orden'] ?? '',
                $orden['estatus'] ?? '',
                $orden['nombre_usuario'] ?? '',
            ];
        }

        exportarExcelHtml(
            'reporte_ordenes_entrada_' . ($entradaInicio ?: 'inicio') . '_' . ($entradaFin ?: 'fin'),
            'Ordenes de entrada',
            ['ID', 'Folio', 'Proveedor', 'Fecha', 'Estatus', 'Solicito'],
            $filas
        );
        break;

    case 'salidas':
        $ordenesSalida = json_decode($adminOrdenes->listarOrdenesSalida(null, $salidaInicio, $salidaFin), true) ?: [];
        $filas = [];
        foreach ($ordenesSalida as $orden) {
            $filas[] = [
                $orden['id'] ?? '',
                $orden['folio'] ?? '',
                $orden['fecha_salida'] ?? '',
                $orden['tipo'] ?? '',
                $orden['estatus'] ?? '',
                $orden['nombre_usuario'] ?? '',
            ];
        }

        exportarExcelHtml(
            'reporte_ordenes_salida_' . ($salidaInicio ?: 'inicio') . '_' . ($salidaFin ?: 'fin'),
            'Ordenes de salida',
            ['ID', 'Folio', 'Fecha', 'Tipo', 'Estatus', 'Solicito'],
            $filas
        );
        break;
}

http_response_code(400);
echo 'Tipo de reporte no válido.';
