<?php
include_once 'conector.php';

class AdministradorArticulos extends Con {

    /* =========================
       ARTÍCULOS / PRODUCTOS
    ========================= */

    // Crear artículo
    public function agregarArticulo(
        $sku,
        $nombre,
        $id_familia,
        $id_subfamilia = null,
        $unidad_medida = '',
        $descripcion = '',
        $activo = 1,
        $costo_reposicon = 0,
        $consumo_diario = 0,
        $tiempo_reposicion = 0
    ) {

        $sku = $this->limpiar($sku);
        $nombre = $this->limpiar($nombre);
        $id_familia = (int)$id_familia;
        $id_subfamilia = $id_subfamilia !== null ? (int)$id_subfamilia : 'NULL';
        $unidad_medida = $this->limpiar($unidad_medida);
        $descripcion = $this->limpiar($descripcion);
        $costo_reposicon = (float)$costo_reposicon;
        $consumo_diario = max(0, (float)$consumo_diario);
        $tiempo_reposicion = max(0, (int)$tiempo_reposicion);
        $activo = (int)$activo;

        $sql = "
            INSERT INTO productos
            (sku, nombre, id_familia, id_subfamilia, unidad_medida, descripcion, activo, costo_reposicion, consumo_diario, tiempo_reposicion)
            VALUES (
                " . ($sku !== '' ? "'$sku'" : "NULL") . ",
                '$nombre',
                $id_familia,
                $id_subfamilia,
                '$unidad_medida',
                '$descripcion',
                $activo,
                $costo_reposicon,
                $consumo_diario,
                $tiempo_reposicion
            )
        ";

        return $this->ejecutar($sql);
    }

    public function agregarInventario($id_articulo, $cantidad, $fecha) {
        $id_articulo = (int)$id_articulo;
        $cantidad = (float)$cantidad;
        $fecha = $this->limpiar($fecha);
        $sql = "
            INSERT INTO inventario
            (id_producto, stock, updated_at)
            VALUES (
                $id_articulo,
                $cantidad,
                '$fecha'
            )
            ON DUPLICATE KEY UPDATE
                stock = stock + VALUES(stock),
                updated_at = VALUES(updated_at)
        ";
        return $this->ejecutar($sql);
    }

    public function obtenerUltimoArticuloInsertado() {
        $ultimoId = $this->ultimoId();

        if ($ultimoId > 0) {
            return $ultimoId;
        }

        $sql = "
            SELECT COALESCE(MAX(id), 0) AS ultimo_id
            FROM productos
        ";
        $resultado = $this->ejecutar($sql);
        $ultimo = json_decode($resultado);
        return (int)($ultimo[0]->ultimo_id ?? 0);
    }
    // Listar artículos
    public function listarArticulos($soloActivos = false) {

        $where = $soloActivos ? "WHERE p.activo = 1" : "";

        return $this->ejecutar($this->consultaBaseArticulos($where, "ORDER BY p.nombre"));
    }

    public function listarArticulosCompleto($soloActivos = false) {

        $where = $soloActivos ? "WHERE p.activo = 1" : "";

        return $this->ejecutar($this->consultaBaseArticulos($where, "ORDER BY p.nombre"));
    }

    public function listarArticulosReporteGeneral($soloActivos = false, $idFamilia = 0, $idSubfamilia = 0) {
        $condiciones = [];

        if ($soloActivos) {
            $condiciones[] = "p.activo = 1";
        }

        $idFamilia = (int)$idFamilia;
        $idSubfamilia = (int)$idSubfamilia;

        if ($idFamilia > 0) {
            $condiciones[] = "p.id_familia = $idFamilia";
        }

        if ($idSubfamilia > 0) {
            $condiciones[] = "p.id_subfamilia = $idSubfamilia";
        }

        $where = count($condiciones) ? "WHERE " . implode(" AND ", $condiciones) : "";

        $sql = "
            SELECT
                p.id,
                p.sku,
                p.nombre,
                p.unidad_medida,
                p.activo,
                p.descripcion,
                COALESCE(inv.stock, 0) AS cantidad,
                f.nombre AS familia,
                s.nombre AS subfamilia,
                COALESCE(entradas.total_entradas, 0) AS total_entradas,
                COALESCE(salidas.total_salidas, 0) AS total_salidas,
                COALESCE(entradas.total_movimientos_entrada, 0) + COALESCE(salidas.total_movimientos_salida, 0) AS total_movimientos,
                COALESCE(entradas.precio_promedio_compra, p.costo_reposicion, 0) AS precio_promedio_compra
            FROM productos p
            JOIN familias f ON f.id = p.id_familia
            LEFT JOIN subfamilias s ON s.id = p.id_subfamilia
            LEFT JOIN inventario inv ON inv.id_producto = p.id
            LEFT JOIN (
                SELECT
                    ocd.id_producto,
                    SUM(ocd.cantidad) AS total_entradas,
                    COUNT(DISTINCT ocd.id_orden_compra) AS total_movimientos_entrada,
                    CASE
                        WHEN SUM(ocd.cantidad) > 0 THEN SUM(ocd.subtotal) / SUM(ocd.cantidad)
                        ELSE 0
                    END AS precio_promedio_compra
                FROM orden_compra_detalle ocd
                GROUP BY ocd.id_producto
            ) entradas ON entradas.id_producto = p.id
            LEFT JOIN (
                SELECT
                    osd.id_producto,
                    SUM(osd.cantidad) AS total_salidas,
                    COUNT(DISTINCT osd.id_orden_salida) AS total_movimientos_salida
                FROM orden_salida_detalle osd
                GROUP BY osd.id_producto
            ) salidas ON salidas.id_producto = p.id
            $where
            ORDER BY p.nombre
        ";

        return $this->ejecutar($sql);
    }

    public function listarArticulosPaginados($filtros = []) {
        $pagina = max(1, (int)($filtros['pagina'] ?? 1));
        $porPagina = max(1, min(100, (int)($filtros['por_pagina'] ?? 10)));
        $offset = ($pagina - 1) * $porPagina;

        $where = [];
        $soloActivos = !empty($filtros['solo_activos']);
        $idFamilia = (int)($filtros['id_familia'] ?? 0);
        $idSubfamilia = (int)($filtros['id_subfamilia'] ?? 0);
        $soloConStock = !empty($filtros['solo_con_stock']);
        $texto = trim((string)($filtros['texto'] ?? ''));

        if ($soloActivos) {
            $where[] = "p.activo = 1";
        }

        if ($idFamilia > 0) {
            $where[] = "p.id_familia = $idFamilia";
        }

        if ($idSubfamilia > 0) {
            $where[] = "p.id_subfamilia = $idSubfamilia";
        }

        if ($soloConStock) {
            $where[] = "COALESCE(inv.stock, 0) > 0";
        }

        if ($texto !== '') {
            $textoEscapado = $this->escapar($texto);
            $where[] = "(
                p.nombre LIKE '%$textoEscapado%' OR
                p.descripcion LIKE '%$textoEscapado%' OR
                p.sku LIKE '%$textoEscapado%'
            )";
        }

        $whereSql = count($where) ? "WHERE " . implode(" AND ", $where) : "";

        $sqlDatos = $this->consultaBaseArticulos($whereSql, "ORDER BY p.nombre", $porPagina, $offset);
        $sqlConteo = "
            SELECT COUNT(*) AS total
            FROM productos p
            LEFT JOIN inventario inv ON inv.id_producto = p.id
            $whereSql
        ";

        $datos = json_decode($this->ejecutar($sqlDatos), true) ?: [];
        $conteo = json_decode($this->ejecutar($sqlConteo), true);
        $total = (int)($conteo[0]['total'] ?? 0);

        return json_encode([
            'data' => $datos,
            'pagination' => [
                'page' => $pagina,
                'per_page' => $porPagina,
                'total' => $total,
                'total_pages' => max(1, (int)ceil($total / $porPagina)),
            ],
        ]);
    }

    public function obtenerCantidad($item = 0){
        $item = (int)$item;
        $sql = "
            SELECT COALESCE(stock, 0) as cantidad
            FROM inventario
            WHERE id_producto = $item
            LIMIT 1
        ";
        return $this->ejecutar($sql);
    }

    public function listarArticulosBajoStock($limite = 5, $soloActivos = true) {
        $limite = (float)$limite;
        $where = [];

        if ($soloActivos) {
            $where[] = "p.activo = 1";
        }

        $where[] = "COALESCE(inv.stock, 0) <= $limite";
        $whereSql = "WHERE " . implode(" AND ", $where);

        return $this->ejecutar($this->consultaBaseArticulos($whereSql, "ORDER BY cantidad ASC, p.nombre ASC"));
    }

    public function listarComprasSugeridas($dias = 30) {
        $dias = max(1, (int)$dias);

        $sql = "
            SELECT
                p.id,
                p.sku,
                p.nombre,
                p.unidad_medida,
                p.descripcion,
                p.costo_reposicion,
                p.consumo_diario,
                p.tiempo_reposicion,
                COALESCE(inv.stock, 0) AS cantidad,
                f.nombre AS familia,
                s.nombre AS subfamilia,
                CASE
                    WHEN p.consumo_diario > 0 THEN ROUND(COALESCE(inv.stock, 0) / p.consumo_diario, 2)
                    ELSE NULL
                END AS dias_restantes
            FROM productos p
            JOIN familias f ON f.id = p.id_familia
            LEFT JOIN subfamilias s ON s.id = p.id_subfamilia
            LEFT JOIN inventario inv ON inv.id_producto = p.id
            WHERE p.activo = 1
              AND p.consumo_diario > 0
              AND (COALESCE(inv.stock, 0) / p.consumo_diario) <= $dias
            ORDER BY p.tiempo_reposicion DESC, dias_restantes ASC, p.nombre ASC
        ";

        return $this->ejecutar($sql);
    }

    public function obtenerHistorialEntradas($idArticulo) {
        $idArticulo = (int)$idArticulo;

        $sql = "
            SELECT
                ocd.id_orden_compra,
                oc.folio,
                oc.fecha_orden,
                oc.estatus,
                oc.created_at,
                ocd.cantidad,
                ocd.precio_unitario,
                ocd.subtotal,
                p.nombre AS proveedor,
                COALESCE(u.nombre, CONCAT('Usuario #', oc.id_usuario)) AS usuario
            FROM orden_compra_detalle ocd
            INNER JOIN ordenes_compra oc ON oc.id = ocd.id_orden_compra
            INNER JOIN proveedores p ON p.id = oc.id_proveedor
            LEFT JOIN usuarios u ON u.id = oc.id_usuario
            WHERE ocd.id_producto = $idArticulo
            ORDER BY oc.fecha_orden DESC, oc.id DESC
        ";

        return $this->ejecutar($sql);
    }

    public function obtenerHistorialSalidas($idArticulo) {
        $idArticulo = (int)$idArticulo;

        $sql = "
            SELECT
                osd.id_orden_salida,
                os.folio,
                os.fecha_salida,
                os.tipo,
                os.estatus,
                os.created_at,
                osd.cantidad,
                osd.costo_unitario,
                osd.subtotal,
                COALESCE(u.nombre, CONCAT('Usuario #', os.id_usuario)) AS usuario
            FROM orden_salida_detalle osd
            INNER JOIN ordenes_salida os ON os.id = osd.id_orden_salida
            LEFT JOIN usuarios u ON u.id = os.id_usuario
            WHERE osd.id_producto = $idArticulo
            ORDER BY os.fecha_salida DESC, os.id DESC
        ";

        return $this->ejecutar($sql);
    }

    public function obtenerResumenDashboard() {
        $sql = "
            SELECT
                COUNT(*) AS total_articulos,
                COALESCE(SUM(COALESCE(inv.stock, 0)), 0) AS total_stock,
                SUM(CASE WHEN COALESCE(inv.stock, 0) <= 0 THEN 1 ELSE 0 END) AS sin_stock
            FROM productos p
            LEFT JOIN inventario inv ON inv.id_producto = p.id
            WHERE p.activo = 1
        ";

        return $this->ejecutar($sql);
    }

    public function listarArticulosCriticos($limite = 5) {
        $limite = max(1, (int)$limite);
        return $this->ejecutar($this->consultaBaseArticulos("WHERE p.activo = 1", "ORDER BY cantidad ASC, p.nombre ASC", $limite));
    }
    // Obtener artículo por ID
    public function obtenerArticulo($id) {

        $id = (int)$id;

        $sql = "
            SELECT
                id,
                sku,
                nombre,
                id_familia,
                id_subfamilia,
                unidad_medida,
                descripcion,
                activo,
                costo_reposicion,
                consumo_diario,
                tiempo_reposicion
            FROM productos
            WHERE id = $id
            LIMIT 1
        ";

        return $this->ejecutar($sql);
    }

    // Actualizar artículo
    public function actualizarArticulo(
        $id,
        $sku,
        $nombre,
        $id_familia,
        $id_subfamilia = null,
        $unidad_medida = '',
        $descripcion = '',
        $activo = 1,
        $costo_reposicion = 0,
        $consumo_diario = 0,
        $tiempo_reposicion = 0
    ) {

        $id = (int)$id;
        $sku = $this->limpiar($sku);
        $nombre = $this->limpiar($nombre);
        $id_familia = (int)$id_familia;
        $id_subfamilia = $id_subfamilia !== null ? (int)$id_subfamilia : 'NULL';
        $unidad_medida = $this->limpiar($unidad_medida);
        $descripcion = $this->limpiar($descripcion);
        $costo_reposicion = (float)$costo_reposicion;
        $consumo_diario = max(0, (float)$consumo_diario);
        $tiempo_reposicion = max(0, (int)$tiempo_reposicion);
        $activo = (int)$activo;

        $sql = "
            UPDATE productos
            SET
                sku = " . ($sku !== '' ? "'$sku'" : "NULL") . ",
                nombre = '$nombre',
                id_familia = $id_familia,
                id_subfamilia = $id_subfamilia,
                unidad_medida = '$unidad_medida',
                descripcion = '$descripcion',
                costo_reposicion = $costo_reposicion,
                consumo_diario = $consumo_diario,
                tiempo_reposicion = $tiempo_reposicion,
                activo = $activo
            WHERE id = $id
        ";

        return $this->ejecutar($sql);
    }

    // Eliminar artículo (lógico)
    public function eliminarArticulo($id) {

        $id = (int)$id;

        $sql = "
            UPDATE productos
            SET activo = 0
            WHERE id = $id
        ";

        return $this->ejecutar($sql);
    }

    /* =========================
       UTILIDAD
    ========================= */

    private function limpiar($valor) {
        return htmlspecialchars(trim($valor), ENT_QUOTES, 'UTF-8');
    }

    private function consultaBaseArticulos($whereSql = "", $orderSql = "ORDER BY p.nombre", $limit = null, $offset = null) {
        $limitSql = $limit !== null ? " LIMIT " . max(1, (int)$limit) : "";
        $offsetSql = ($limit !== null && $offset !== null && (int)$offset > 0) ? " OFFSET " . (int)$offset : "";

        return "
            SELECT
                p.id,
                p.sku,
                p.nombre,
                p.unidad_medida,
                p.activo,
                p.id_familia,
                p.id_subfamilia,
                p.descripcion,
                p.costo_reposicion,
                p.consumo_diario,
                p.tiempo_reposicion,
                COALESCE(inv.stock, 0) AS cantidad,
                f.nombre AS familia,
                s.nombre AS subfamilia
            FROM productos p
            JOIN familias f ON f.id = p.id_familia
            LEFT JOIN subfamilias s ON s.id = p.id_subfamilia
            LEFT JOIN inventario inv ON inv.id_producto = p.id
            $whereSql
            $orderSql
            $limitSql
            $offsetSql
        ";
    }

    public function dameArticulo($id) {
        $id = (int)$id;
        $sql = "
            SELECT
                id,
                sku,
                nombre,
                id_familia,
                id_subfamilia,
                unidad_medida,
                descripcion,
                activo,
                costo_reposicion,
                consumo_diario,
                tiempo_reposicion
            FROM productos
            WHERE id = $id
            LIMIT 1
        ";
        return $this->ejecutar($sql);
    }
}
