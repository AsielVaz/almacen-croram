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
        $ubicacion = '',
        $activo = 1,
        $costo_reposicon = 0,
        $consumo_diario = 0,
        $tiempo_reposicion = 0,
        $tipo_articulo = 'NUEVO'
    ) {

        $sku = $this->limpiar($sku);
        if ($sku === '') {
            $sku = $this->generarSkuConsecutivo();
        }
        $nombre = $this->limpiar($nombre);
        $id_familia = (int)$id_familia;
        $id_subfamilia = $id_subfamilia !== null ? (int)$id_subfamilia : 'NULL';
        $unidad_medida = $this->limpiar($unidad_medida);
        $tipo_articulo = $this->normalizarTipoArticulo($tipo_articulo);
        $descripcion = $this->limpiar($descripcion);
        $ubicacion = $this->limpiar($ubicacion);
        $costo_reposicon = (float)$costo_reposicon;
        $consumo_diario = max(0, (float)$consumo_diario);
        $tiempo_reposicion = max(0, (int)$tiempo_reposicion);
        $activo = (int)$activo;

        $sql = "
            INSERT INTO productos
            (sku, nombre, id_familia, id_subfamilia, unidad_medida, tipo_articulo, descripcion, ubicacion, activo, costo_reposicion, consumo_diario, tiempo_reposicion)
            VALUES (
                " . ($sku !== '' ? "'$sku'" : "NULL") . ",
                '$nombre',
                $id_familia,
                $id_subfamilia,
                '$unidad_medida',
                '$tipo_articulo',
                '$descripcion',
                " . ($ubicacion !== '' ? "'$ubicacion'" : "NULL") . ",
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

    public function actualizarInventarioExacto($id_articulo, $cantidad, $fecha) {
        $id_articulo = (int)$id_articulo;
        $cantidad = max(0, (float)$cantidad);
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
                stock = VALUES(stock),
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
                p.tipo_articulo,
                p.activo,
                p.descripcion,
                p.ubicacion,
                COALESCE(inv.stock, 0) AS cantidad,
                f.nombre AS familia,
                s.nombre AS subfamilia,
                COALESCE(entradas.total_entradas, 0) AS total_entradas,
                COALESCE(salidas.total_salidas, 0) AS total_salidas,
                COALESCE(entradas.total_movimientos_entrada, 0) + COALESCE(salidas.total_movimientos_salida, 0) AS total_movimientos,
                CASE
                    WHEN COALESCE(inv.stock, 0) > 0 THEN
                        COALESCE(valor_lotes.valor_inventario, 0)
                        + GREATEST(COALESCE(inv.stock, 0) - COALESCE(valor_lotes.cantidad_lotes, 0), 0)
                          * COALESCE(NULLIF(p.costo_reposicion, 0), ultima_compra.precio_unitario, 0)
                    ELSE 0
                END AS valor_inventario,
                CASE
                    WHEN COALESCE(inv.stock, 0) > 0 THEN (
                        COALESCE(valor_lotes.valor_inventario, 0)
                        + GREATEST(COALESCE(inv.stock, 0) - COALESCE(valor_lotes.cantidad_lotes, 0), 0)
                          * COALESCE(NULLIF(p.costo_reposicion, 0), ultima_compra.precio_unitario, 0)
                    ) / COALESCE(inv.stock, 0)
                    ELSE 0
                END AS costo_por_unidad,
                CASE
                    WHEN COALESCE(inv.stock, 0) > 0 THEN (
                        COALESCE(valor_lotes.valor_inventario, 0)
                        + GREATEST(COALESCE(inv.stock, 0) - COALESCE(valor_lotes.cantidad_lotes, 0), 0)
                          * COALESCE(NULLIF(p.costo_reposicion, 0), ultima_compra.precio_unitario, 0)
                    ) / COALESCE(inv.stock, 0)
                    ELSE COALESCE(NULLIF(p.costo_reposicion, 0), ultima_compra.precio_unitario, 0)
                END AS precio_promedio_compra
            FROM productos p
            JOIN familias f ON f.id = p.id_familia
            LEFT JOIN subfamilias s ON s.id = p.id_subfamilia
            LEFT JOIN inventario inv ON inv.id_producto = p.id
            LEFT JOIN (
                SELECT
                    il.id_producto,
                    SUM(il.cantidad_disponible * COALESCE(NULLIF(il.costo_unitario, 0), ultima_compra.precio_unitario, 0)) AS valor_inventario,
                    SUM(il.cantidad_disponible) AS cantidad_lotes
                FROM inventario_lotes il
                LEFT JOIN (
                    SELECT
                        ocd.id_producto,
                        ocd.precio_unitario
                    FROM orden_compra_detalle ocd
                    INNER JOIN ordenes_compra oc ON oc.id = ocd.id_orden_compra
                    INNER JOIN (
                        SELECT ocd2.id_producto, MAX(ocd2.id) AS id_detalle
                        FROM orden_compra_detalle ocd2
                        INNER JOIN ordenes_compra oc2 ON oc2.id = ocd2.id_orden_compra
                        WHERE oc2.estatus = 'RECIBIDA'
                        GROUP BY ocd2.id_producto
                    ) ult ON ult.id_producto = ocd.id_producto AND ult.id_detalle = ocd.id
                ) ultima_compra ON ultima_compra.id_producto = il.id_producto
                WHERE il.cantidad_disponible > 0
                GROUP BY il.id_producto
            ) valor_lotes ON valor_lotes.id_producto = p.id
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
                INNER JOIN ordenes_compra oc ON oc.id = ocd.id_orden_compra
                WHERE oc.estatus = 'RECIBIDA'
                GROUP BY ocd.id_producto
            ) entradas ON entradas.id_producto = p.id
            LEFT JOIN (
                SELECT
                    osd.id_producto,
                    SUM(osd.cantidad) AS total_salidas,
                    COUNT(DISTINCT osd.id_orden_salida) AS total_movimientos_salida
                FROM orden_salida_detalle osd
                INNER JOIN ordenes_salida os ON os.id = osd.id_orden_salida
                WHERE os.estatus = 'CONFIRMADA'
                GROUP BY osd.id_producto
            ) salidas ON salidas.id_producto = p.id
            LEFT JOIN (
                SELECT
                    ocd.id_producto,
                    ocd.precio_unitario
                FROM orden_compra_detalle ocd
                INNER JOIN ordenes_compra oc ON oc.id = ocd.id_orden_compra
                INNER JOIN (
                    SELECT ocd2.id_producto, MAX(ocd2.id) AS id_detalle
                    FROM orden_compra_detalle ocd2
                    INNER JOIN ordenes_compra oc2 ON oc2.id = ocd2.id_orden_compra
                    WHERE oc2.estatus = 'RECIBIDA'
                    GROUP BY ocd2.id_producto
                ) ult ON ult.id_producto = ocd.id_producto AND ult.id_detalle = ocd.id
            ) ultima_compra ON ultima_compra.id_producto = p.id
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
                p.ubicacion LIKE '%$textoEscapado%' OR
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

    public function listarComprasSugeridas($diasStockRequeridos = 15, $fechaInicioAnalisis = '') {
        $diasStockRequeridos = max(1, (int)$diasStockRequeridos);
        $fechaInicioAnalisis = $this->normalizarFecha($fechaInicioAnalisis);
        $fechaMinima = date('Y-m-d', strtotime('-12 months'));

        if ($fechaInicioAnalisis === '' || $fechaInicioAnalisis < $fechaMinima) {
            $fechaInicioAnalisis = $fechaMinima;
        }

        $mesesAnalisis = "GREATEST(DATEDIFF(CURDATE(), '$fechaInicioAnalisis') / 30.4, 1)";
        $consumoMensualSql = "(
            CASE
                WHEN COALESCE(consumos.consumo_12_meses, 0) > 0
                    THEN COALESCE(consumos.consumo_12_meses, 0) / $mesesAnalisis
                ELSE COALESCE(p.consumo_diario, 0) * 30.4
            END
        )";
        $consumoDiarioSql = "($consumoMensualSql / 30.4)";
        $diasTotalesStockSql = "($diasStockRequeridos + COALESCE(p.tiempo_reposicion, 0))";
        $existenciaEnDiasSql = "(
            CASE
                WHEN $consumoDiarioSql > 0
                    THEN ROUND(COALESCE(inv.stock, 0) / $consumoDiarioSql, 0)
                ELSE 0
            END
        )";
        $diasPorComprarSql = "GREATEST($diasTotalesStockSql - $existenciaEnDiasSql, 0)";
        $pedidoSugeridoSql = "ROUND($diasPorComprarSql * $consumoDiarioSql, 0)";

        $sql = "
            SELECT
                p.id,
                p.sku,
                COALESCE(NULLIF(p.nombre, ''), p.descripcion, 'Sin nombre') AS nombre,
                p.activo,
                p.unidad_medida,
                p.descripcion,
                p.ubicacion,
                COALESCE(
                    NULLIF(p.costo_reposicion, 0),
                    ultima_compra.precio_unitario,
                    0
                ) AS costo_por_pieza,
                COALESCE(
                    NULLIF(p.costo_reposicion, 0),
                    ultima_compra.precio_unitario,
                    0
                ) AS costo_reposicion,
                p.consumo_diario,
                p.tiempo_reposicion,
                COALESCE(inv.stock, 0) AS cantidad,
                f.nombre AS familia,
                s.nombre AS subfamilia,
                '$fechaInicioAnalisis' AS fecha_inicio_analisis,
                COALESCE(consumos.consumo_12_meses, 0) AS consumo_12_meses,
                $consumoMensualSql AS consumo_mensual_promedio,
                $consumoDiarioSql AS consumo_diario_calculado,
                $diasStockRequeridos AS dias_stock_requeridos,
                $diasTotalesStockSql AS dias_totales_stock,
                ROUND($consumoDiarioSql * $diasTotalesStockSql, 0) AS stock_objetivo,
                $existenciaEnDiasSql AS existencia_en_dias,
                $existenciaEnDiasSql AS dias_restantes,
                $diasPorComprarSql AS dias_por_comprar,
                $pedidoSugeridoSql AS pedido_sugerido,
                $pedidoSugeridoSql AS compra_sugerida
            FROM productos p
            JOIN familias f ON f.id = p.id_familia
            LEFT JOIN subfamilias s ON s.id = p.id_subfamilia
            LEFT JOIN inventario inv ON inv.id_producto = p.id
            LEFT JOIN (
                SELECT
                    osd.id_producto,
                    SUM(osd.cantidad) AS consumo_12_meses
                FROM orden_salida_detalle osd
                INNER JOIN ordenes_salida os ON os.id = osd.id_orden_salida
                WHERE os.estatus = 'CONFIRMADA'
                  AND os.fecha_salida >= '$fechaInicioAnalisis'
                GROUP BY osd.id_producto
            ) consumos ON consumos.id_producto = p.id
            LEFT JOIN (
                SELECT
                    ocd.id_producto,
                    ocd.precio_unitario
                FROM orden_compra_detalle ocd
                INNER JOIN ordenes_compra oc ON oc.id = ocd.id_orden_compra
                INNER JOIN (
                    SELECT ocd2.id_producto, MAX(ocd2.id) AS id_detalle
                    FROM orden_compra_detalle ocd2
                    INNER JOIN ordenes_compra oc2 ON oc2.id = ocd2.id_orden_compra
                    WHERE oc2.estatus = 'RECIBIDA'
                    GROUP BY ocd2.id_producto
                ) ult ON ult.id_producto = ocd.id_producto AND ult.id_detalle = ocd.id
            ) ultima_compra ON ultima_compra.id_producto = p.id
            WHERE $consumoDiarioSql > 0
              AND $pedidoSugeridoSql > 0
            ORDER BY pedido_sugerido DESC, p.tiempo_reposicion DESC, nombre ASC
        ";

        return $this->ejecutar($sql);
    }

    public function listarArticulosObsoletos($mesesSinMovimiento = 12) {
        $mesesSinMovimiento = max(1, (int)$mesesSinMovimiento);

        $sql = "
            SELECT
                p.id,
                p.sku,
                COALESCE(NULLIF(p.nombre, ''), p.descripcion, 'Sin nombre') AS nombre,
                p.descripcion,
                p.ubicacion,
                f.nombre AS familia,
                COALESCE(s.nombre, 'Sin familia') AS subfamilia,
                COALESCE(inv.stock, 0) AS cantidad,
                (
                    COALESCE(valor_lotes.valor_inventario, 0)
                    + GREATEST(COALESCE(inv.stock, 0) - COALESCE(valor_lotes.cantidad_lotes, 0), 0)
                      * COALESCE(NULLIF(p.costo_reposicion, 0), ultima_compra.precio_unitario, 0)
                ) AS valor_inventario,
                CASE
                    WHEN COALESCE(inv.stock, 0) > 0 THEN (
                        COALESCE(valor_lotes.valor_inventario, 0)
                        + GREATEST(COALESCE(inv.stock, 0) - COALESCE(valor_lotes.cantidad_lotes, 0), 0)
                          * COALESCE(NULLIF(p.costo_reposicion, 0), ultima_compra.precio_unitario, 0)
                    ) / COALESCE(inv.stock, 0)
                    ELSE 0
                END AS costo_por_unidad,
                movimientos.ultimo_movimiento
            FROM productos p
            INNER JOIN familias f ON f.id = p.id_familia
            LEFT JOIN subfamilias s ON s.id = p.id_subfamilia
            LEFT JOIN inventario inv ON inv.id_producto = p.id
            LEFT JOIN (
                SELECT
                    il.id_producto,
                    SUM(il.cantidad_disponible * COALESCE(NULLIF(il.costo_unitario, 0), ultima_compra.precio_unitario, 0)) AS valor_inventario,
                    SUM(il.cantidad_disponible) AS cantidad_lotes
                FROM inventario_lotes il
                LEFT JOIN (
                    SELECT
                        ocd.id_producto,
                        ocd.precio_unitario
                    FROM orden_compra_detalle ocd
                    INNER JOIN ordenes_compra oc ON oc.id = ocd.id_orden_compra
                    INNER JOIN (
                        SELECT ocd2.id_producto, MAX(ocd2.id) AS id_detalle
                        FROM orden_compra_detalle ocd2
                        INNER JOIN ordenes_compra oc2 ON oc2.id = ocd2.id_orden_compra
                        WHERE oc2.estatus = 'RECIBIDA'
                        GROUP BY ocd2.id_producto
                    ) ult ON ult.id_producto = ocd.id_producto AND ult.id_detalle = ocd.id
                ) ultima_compra ON ultima_compra.id_producto = il.id_producto
                WHERE il.cantidad_disponible > 0
                GROUP BY il.id_producto
            ) valor_lotes ON valor_lotes.id_producto = p.id
            LEFT JOIN (
                SELECT
                    ocd.id_producto,
                    ocd.precio_unitario
                FROM orden_compra_detalle ocd
                INNER JOIN ordenes_compra oc ON oc.id = ocd.id_orden_compra
                INNER JOIN (
                    SELECT ocd2.id_producto, MAX(ocd2.id) AS id_detalle
                    FROM orden_compra_detalle ocd2
                    INNER JOIN ordenes_compra oc2 ON oc2.id = ocd2.id_orden_compra
                    WHERE oc2.estatus = 'RECIBIDA'
                    GROUP BY ocd2.id_producto
                ) ult ON ult.id_producto = ocd.id_producto AND ult.id_detalle = ocd.id
            ) ultima_compra ON ultima_compra.id_producto = p.id
            LEFT JOIN (
                SELECT id_producto, MAX(fecha_movimiento) AS ultimo_movimiento
                FROM (
                    SELECT ocd.id_producto, oc.fecha_orden AS fecha_movimiento
                    FROM orden_compra_detalle ocd
                    INNER JOIN ordenes_compra oc ON oc.id = ocd.id_orden_compra
                    WHERE oc.estatus = 'RECIBIDA'
                    UNION ALL
                    SELECT osd.id_producto, os.fecha_salida AS fecha_movimiento
                    FROM orden_salida_detalle osd
                    INNER JOIN ordenes_salida os ON os.id = osd.id_orden_salida
                    WHERE os.estatus = 'CONFIRMADA'
                ) mov
                GROUP BY id_producto
            ) movimientos ON movimientos.id_producto = p.id
            WHERE p.activo = 1
              AND COALESCE(inv.stock, 0) > 0
              AND (
                    movimientos.ultimo_movimiento IS NULL
                    OR movimientos.ultimo_movimiento < DATE_SUB(CURDATE(), INTERVAL $mesesSinMovimiento MONTH)
                  )
            ORDER BY movimientos.ultimo_movimiento ASC, nombre ASC
        ";

        return $this->ejecutar($sql);
    }

    public function listarInventarioMovimientos($fechaInicio = '', $fechaFin = '', $tipo = '', $origen = '') {
        $fechaInicio = $this->normalizarFecha($fechaInicio);
        $fechaFin = $this->normalizarFecha($fechaFin);
        $tipo = strtoupper(trim((string)$tipo));
        $origen = strtoupper(trim((string)$origen));
        $condiciones = [];

        if ($fechaInicio !== '') {
            $condiciones[] = "DATE(im.created_at) >= '$fechaInicio'";
        }

        if ($fechaFin !== '') {
            $condiciones[] = "DATE(im.created_at) <= '$fechaFin'";
        }

        if (in_array($tipo, ['ENTRADA', 'SALIDA'], true)) {
            $condiciones[] = "im.tipo = '$tipo'";
        }

        if (in_array($origen, ['COMPRA', 'ORDEN_SALIDA', 'AJUSTE'], true)) {
            $condiciones[] = "im.origen = '$origen'";
        }

        $where = count($condiciones) ? "WHERE " . implode(" AND ", $condiciones) : "";

        $sql = "
            SELECT
                im.id,
                im.id_producto,
                im.tipo,
                im.origen,
                im.id_referencia,
                im.cantidad,
                im.id_usuario,
                im.created_at,
                COALESCE(NULLIF(u.nombre, ''), NULLIF(u.usuario, ''), CONCAT('Usuario #', im.id_usuario)) AS nombre_usuario,
                p.sku,
                COALESCE(NULLIF(p.nombre, ''), p.descripcion, 'Sin nombre') AS articulo,
                p.descripcion,
                p.ubicacion,
                p.unidad_medida,
                p.tipo_articulo
            FROM inventario_movimientos im
            INNER JOIN productos p ON p.id = im.id_producto
            LEFT JOIN usuarios u ON u.id = im.id_usuario
            $where
            ORDER BY im.created_at DESC, im.id DESC
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
                p.id,
                p.sku,
                p.nombre,
                p.id_familia,
                p.id_subfamilia,
                p.unidad_medida,
                p.tipo_articulo,
                p.descripcion,
                p.ubicacion,
                p.activo,
                CASE
                    WHEN COALESCE(inv.stock, 0) > 0 THEN (
                        COALESCE(valor_lotes.valor_inventario, 0)
                        + GREATEST(COALESCE(inv.stock, 0) - COALESCE(valor_lotes.cantidad_lotes, 0), 0)
                          * COALESCE(NULLIF(p.costo_reposicion, 0), ultima_compra.precio_unitario, 0)
                    ) / COALESCE(inv.stock, 0)
                    ELSE COALESCE(NULLIF(p.costo_reposicion, 0), ultima_compra.precio_unitario, 0)
                END AS costo_reposicion,
                p.consumo_diario,
                p.tiempo_reposicion,
                COALESCE(inv.stock, 0) AS inventario_inicial
            FROM productos p
            LEFT JOIN inventario inv ON inv.id_producto = p.id
            LEFT JOIN (
                SELECT
                    il.id_producto,
                    SUM(il.cantidad_disponible * COALESCE(NULLIF(il.costo_unitario, 0), ultima_compra.precio_unitario, 0)) AS valor_inventario,
                    SUM(il.cantidad_disponible) AS cantidad_lotes
                FROM inventario_lotes il
                LEFT JOIN (
                    SELECT
                        ocd.id_producto,
                        ocd.precio_unitario
                    FROM orden_compra_detalle ocd
                    INNER JOIN ordenes_compra oc ON oc.id = ocd.id_orden_compra
                    INNER JOIN (
                        SELECT ocd2.id_producto, MAX(ocd2.id) AS id_detalle
                        FROM orden_compra_detalle ocd2
                        INNER JOIN ordenes_compra oc2 ON oc2.id = ocd2.id_orden_compra
                        WHERE oc2.estatus = 'RECIBIDA'
                        GROUP BY ocd2.id_producto
                    ) ult ON ult.id_producto = ocd.id_producto AND ult.id_detalle = ocd.id
                ) ultima_compra ON ultima_compra.id_producto = il.id_producto
                WHERE il.cantidad_disponible > 0
                GROUP BY il.id_producto
            ) valor_lotes ON valor_lotes.id_producto = p.id
            LEFT JOIN (
                SELECT
                    ocd.id_producto,
                    ocd.precio_unitario
                FROM orden_compra_detalle ocd
                INNER JOIN ordenes_compra oc ON oc.id = ocd.id_orden_compra
                INNER JOIN (
                    SELECT ocd2.id_producto, MAX(ocd2.id) AS id_detalle
                    FROM orden_compra_detalle ocd2
                    INNER JOIN ordenes_compra oc2 ON oc2.id = ocd2.id_orden_compra
                    WHERE oc2.estatus = 'RECIBIDA'
                    GROUP BY ocd2.id_producto
                ) ult ON ult.id_producto = ocd.id_producto AND ult.id_detalle = ocd.id
            ) ultima_compra ON ultima_compra.id_producto = p.id
            WHERE p.id = $id
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
        $ubicacion = '',
        $activo = 1,
        $costo_reposicion = 0,
        $consumo_diario = 0,
        $tiempo_reposicion = 0,
        $tipo_articulo = 'NUEVO'
    ) {

        $id = (int)$id;
        $sku = $this->limpiar($sku);
        if ($sku === '') {
            $sku = $this->generarSkuConsecutivo();
        }
        $nombre = $this->limpiar($nombre);
        $id_familia = (int)$id_familia;
        $id_subfamilia = $id_subfamilia !== null ? (int)$id_subfamilia : 'NULL';
        $unidad_medida = $this->limpiar($unidad_medida);
        $tipo_articulo = $this->normalizarTipoArticulo($tipo_articulo);
        $descripcion = $this->limpiar($descripcion);
        $ubicacion = $this->limpiar($ubicacion);
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
                tipo_articulo = '$tipo_articulo',
                descripcion = '$descripcion',
                ubicacion = " . ($ubicacion !== '' ? "'$ubicacion'" : "NULL") . ",
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

    private function normalizarTipoArticulo($tipo) {
        $tipo = strtoupper(trim((string)$tipo));
        return in_array($tipo, ['NUEVO', 'USADO'], true) ? $tipo : 'NUEVO';
    }

    private function normalizarFecha($fecha) {
        $fecha = trim((string)$fecha);
        if ($fecha === '') {
            return '';
        }

        $dt = DateTime::createFromFormat('Y-m-d', $fecha);
        return ($dt && $dt->format('Y-m-d') === $fecha) ? $fecha : '';
    }

    private function generarSkuConsecutivo() {
        $sql = "SELECT COALESCE(MAX(id), 0) + 1 AS siguiente_id FROM productos";
        $resultado = json_decode($this->ejecutar($sql), true);
        $siguienteId = (int)($resultado[0]['siguiente_id'] ?? 1);
        return 'ART-' . str_pad((string)$siguienteId, 6, '0', STR_PAD_LEFT);
    }

    private function consultaBaseArticulos($whereSql = "", $orderSql = "ORDER BY p.nombre", $limit = null, $offset = null) {
        $limitSql = $limit !== null ? " LIMIT " . max(1, (int)$limit) : "";
        $offsetSql = ($limit !== null && $offset !== null && (int)$offset > 0) ? " OFFSET " . (int)$offset : "";

        return "
            SELECT
                p.id,
                p.sku,
                COALESCE(NULLIF(p.nombre, ''), p.descripcion, 'Sin nombre') AS nombre,
                p.unidad_medida,
                p.tipo_articulo,
                p.activo,
                p.id_familia,
                p.id_subfamilia,
                p.descripcion,
                p.ubicacion,
                p.costo_reposicion,
                p.consumo_diario,
                p.tiempo_reposicion,
                ultima_compra.fecha_orden AS ultima_compra_fecha,
                ultima_compra.nombre_proveedor AS ultima_compra_proveedor,
                ultima_compra.precio_unitario AS ultima_compra_precio,
                COALESCE(inv.stock, 0) AS cantidad,
                f.nombre AS familia,
                s.nombre AS subfamilia
            FROM productos p
            JOIN familias f ON f.id = p.id_familia
            LEFT JOIN subfamilias s ON s.id = p.id_subfamilia
            LEFT JOIN inventario inv ON inv.id_producto = p.id
            LEFT JOIN (
                SELECT
                    ocd.id_producto,
                    oc.fecha_orden,
                    pr.nombre AS nombre_proveedor,
                    ocd.precio_unitario
                FROM orden_compra_detalle ocd
                INNER JOIN ordenes_compra oc ON oc.id = ocd.id_orden_compra
                INNER JOIN proveedores pr ON pr.id = oc.id_proveedor
                INNER JOIN (
                    SELECT ocd2.id_producto, MAX(ocd2.id) AS id_detalle
                    FROM orden_compra_detalle ocd2
                    INNER JOIN ordenes_compra oc2 ON oc2.id = ocd2.id_orden_compra
                    GROUP BY ocd2.id_producto
                ) ult ON ult.id_producto = ocd.id_producto AND ult.id_detalle = ocd.id
            ) ultima_compra ON ultima_compra.id_producto = p.id
            $whereSql
            $orderSql
            $limitSql
            $offsetSql
        ";
    }

    public function dameArticulo($id) {
        return $this->obtenerArticulo($id);
    }
}
