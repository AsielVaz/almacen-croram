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
        $costo_reposicon = 0
    ) {

        $sku = $this->limpiar($sku);
        $nombre = $this->limpiar($nombre);
        $id_familia = (int)$id_familia;
        $id_subfamilia = $id_subfamilia !== null ? (int)$id_subfamilia : 'NULL';
        $unidad_medida = $this->limpiar($unidad_medida);
        $descripcion = $this->limpiar($descripcion);
        $costo_reposicon = (float)$costo_reposicon;
        $activo = (int)$activo;

        $sql = "
            INSERT INTO productos
            (sku, nombre, id_familia, id_subfamilia, unidad_medida, descripcion, activo, costo_reposicion)
            VALUES (
                " . ($sku !== '' ? "'$sku'" : "NULL") . ",
                '$nombre',
                $id_familia,
                $id_subfamilia,
                '$unidad_medida',
                '$descripcion',
                $activo,
                $costo_reposicon
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
                activo
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
        $activo = 1
    ) {

        $id = (int)$id;
        $sku = $this->limpiar($sku);
        $nombre = $this->limpiar($nombre);
        $id_familia = (int)$id_familia;
        $id_subfamilia = $id_subfamilia !== null ? (int)$id_subfamilia : 'NULL';
        $unidad_medida = $this->limpiar($unidad_medida);
        $descripcion = $this->limpiar($descripcion);
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
                activo
            FROM productos
            WHERE id = $id
            LIMIT 1
        ";
        return $this->ejecutar($sql);
    }
}
