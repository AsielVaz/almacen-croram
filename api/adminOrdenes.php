<?php
include_once 'conector.php';

class AdministradorOrdenes extends Con
{
    public function agregarOrdenCompra($folio, $id_proveedor, $fecha_orden, $estatus, $id_usuario, $nota = '')
    {
        $folio = $this->limpiar($folio);
        $id_proveedor = (int)$id_proveedor;
        $fecha_orden = $this->limpiar($fecha_orden);
        $estatus = $this->limpiar($estatus);
        $id_usuario = (int)$id_usuario;
        $nota = $this->limpiar($nota);

        $sql = "
            INSERT INTO ordenes_compra (folio, id_proveedor, fecha_orden, estatus, id_usuario, nota)
            VALUES ('$folio', $id_proveedor, '$fecha_orden', '$estatus', $id_usuario, " . ($nota !== '' ? "'$nota'" : 'NULL') . ")
        ";

        return $this->ejecutar($sql);
    }

    public function listarOrdenesCompra($limit = null, $fechaInicio = '', $fechaFin = '')
    {
        $limitSql = $limit !== null ? " LIMIT " . max(1, (int)$limit) : "";
        $whereSql = $this->construirFiltroFecha('oc.fecha_orden', $fechaInicio, $fechaFin);

        $sql = "
            SELECT
                oc.id,
                oc.folio,
                oc.id_proveedor,
                oc.fecha_orden,
                oc.estatus,
                oc.id_usuario,
                oc.created_at,
                p.nombre AS nombre_proveedor,
                COALESCE(u.nombre, CONCAT('Usuario #', oc.id_usuario)) AS nombre_usuario
            FROM ordenes_compra oc
            INNER JOIN proveedores p ON p.id = oc.id_proveedor
            LEFT JOIN usuarios u ON u.id = oc.id_usuario
            $whereSql
            ORDER BY oc.created_at DESC
            $limitSql
        ";

        return $this->ejecutar($sql);
    }

    public function bloquearOrdenCompra($id)
    {
        $id = (int)$id;
        return $this->ejecutar("
            SELECT *
            FROM ordenes_compra
            WHERE id = $id
            LIMIT 1
            FOR UPDATE
        ");
    }

    public function dameUltimoIdOrdenCompra()
    {
        $ultimoId = $this->ultimoId();

        if ($ultimoId > 0) {
            return $ultimoId;
        }

        $sql = "SELECT COALESCE(MAX(id), 0) AS ultimo_id FROM ordenes_compra";
        $resultado = $this->ejecutar($sql);
        $fila = json_decode($resultado, true)[0] ?? ['ultimo_id' => 0];
        return (int)$fila['ultimo_id'];
    }

    public function obtenerOrdenCompra($id)
    {
        $id = (int)$id;

        $sql = "
            SELECT
                ordenes_compra.*,
                proveedores.nombre AS nombre_proveedor,
                COALESCE(usuarios.nombre, CONCAT('Usuario #', ordenes_compra.id_usuario)) AS nombre_usuario
            FROM ordenes_compra
            INNER JOIN proveedores ON proveedores.id = ordenes_compra.id_proveedor
            LEFT JOIN usuarios ON usuarios.id = ordenes_compra.id_usuario
            WHERE ordenes_compra.id = $id
            LIMIT 1
        ";

        return $this->ejecutar($sql);
    }

    public function agregarDetalleOrden($id_orden_compra, $id_producto, $cantidad, $precio_unitario)
    {
        $id_orden_compra = (int)$id_orden_compra;
        $id_producto = (int)$id_producto;
        $cantidad = (float)$cantidad;
        $precio_unitario = (float)$precio_unitario;
        $subtotal = $cantidad * $precio_unitario;

        $sql = "
            INSERT INTO orden_compra_detalle (id_orden_compra, id_producto, cantidad, precio_unitario, subtotal)
            VALUES ($id_orden_compra, $id_producto, $cantidad, $precio_unitario, $subtotal)
        ";

        return $this->ejecutar($sql);
    }

    public function listarDetallesOrden($id_orden_compra)
    {
        $id_orden_compra = (int)$id_orden_compra;

        $sql = "
            SELECT
                orden_compra_detalle.*,
                COALESCE(NULLIF(productos.nombre, ''), productos.descripcion, 'Sin nombre') AS nombre_producto,
                productos.sku,
                productos.ubicacion,
                ultima_compra.fecha_orden AS ultima_compra_fecha,
                ultima_compra.nombre_proveedor AS ultima_compra_proveedor,
                ultima_compra.precio_unitario AS ultima_compra_precio
            FROM orden_compra_detalle
            inner join productos on productos.id = orden_compra_detalle.id_producto
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
                    WHERE oc2.id <> $id_orden_compra
                      AND oc2.estatus = 'RECIBIDA'
                    GROUP BY ocd2.id_producto
                ) ult ON ult.id_producto = ocd.id_producto AND ult.id_detalle = ocd.id
            ) ultima_compra ON ultima_compra.id_producto = productos.id
            WHERE id_orden_compra = $id_orden_compra
        ";

        return $this->ejecutar($sql);
    }

    public function listarEntradasDetalle($fechaInicio = '', $fechaFin = '')
    {
        $whereSql = $this->construirFiltroFecha('oc.fecha_orden', $fechaInicio, $fechaFin);

        return $this->ejecutar("
            SELECT
                oc.id,
                oc.folio,
                oc.fecha_orden,
                oc.estatus,
                pr.nombre AS proveedor,
                p.sku,
                COALESCE(NULLIF(p.nombre, ''), p.descripcion, 'Sin nombre') AS articulo,
                p.descripcion,
                p.ubicacion,
                ocd.cantidad,
                ocd.precio_unitario,
                ocd.subtotal,
                COALESCE(u.nombre, CONCAT('Usuario #', oc.id_usuario)) AS nombre_usuario
            FROM orden_compra_detalle ocd
            INNER JOIN ordenes_compra oc ON oc.id = ocd.id_orden_compra
            INNER JOIN proveedores pr ON pr.id = oc.id_proveedor
            INNER JOIN productos p ON p.id = ocd.id_producto
            LEFT JOIN usuarios u ON u.id = oc.id_usuario
            $whereSql
            ORDER BY oc.fecha_orden DESC, oc.id DESC, articulo ASC
        ");
    }



    //SELECT `id`, `folio`, `fecha_salida`, `tipo`, `estatus`, `id_usuario`, `created_at` FROM `ordenes_salida` WHERE 1
    public function agregarOrdenSalida($folio, $fecha_salida, $tipo, $estatus, $id_usuario, $nota = '', $id_area = 0)
    {
        $folio = $this->limpiar($folio);
        $fecha_salida = $this->limpiar($fecha_salida);
        $tipo = $this->limpiar($tipo);
        $estatus = $this->limpiar($estatus);
        $id_usuario = (int)$id_usuario;
        $nota = $this->limpiar($nota);
        $id_area = (int)$id_area;

        $sql = "
            INSERT INTO ordenes_salida (folio, fecha_salida, tipo, id_area, estatus, id_usuario, nota)
            VALUES ('$folio', '$fecha_salida', '$tipo', " . ($id_area > 0 ? $id_area : 'NULL') . ", '$estatus', $id_usuario, " . ($nota !== '' ? "'$nota'" : 'NULL') . ")
        ";

        return $this->ejecutar($sql);
    }

    //SELECT `id`, `id_orden_salida`, `id_producto`, `cantidad`, `costo_unitario`, `subtotal` FROM `orden_salida_detalle` WHERE 1
    public function agregarDetalleOrdenSalida($id_orden_salida, $id_producto, $cantidad, $costo_unitario)
    {
        $id_orden_salida = (int)$id_orden_salida;
        $id_producto = (int)$id_producto;
        $cantidad = (float)$cantidad;
        $costo_unitario = (float)$costo_unitario;
        $subtotal = $cantidad * $costo_unitario;

        $sql = "
            INSERT INTO orden_salida_detalle (id_orden_salida, id_producto, cantidad, costo_unitario, subtotal)
            VALUES ($id_orden_salida, $id_producto, $cantidad, $costo_unitario, $subtotal)
        ";

        return $this->ejecutar($sql);
    }

    public function listarDetallesOrdenSalida($id_orden_salida)
    {
        $id_orden_salida = (int)$id_orden_salida;

        $sql = "
            SELECT
                orden_salida_detalle.*,
                COALESCE(NULLIF(productos.nombre, ''), productos.descripcion, 'Sin nombre') AS nombre_producto,
                productos.sku,
                productos.ubicacion,
                COALESCE(
                    NULLIF(orden_salida_detalle.costo_peps, 0),
                    NULLIF(orden_salida_detalle.costo_unitario, 0),
                    NULLIF(productos.costo_reposicion, 0),
                    ultima_compra.precio_unitario,
                    0
                ) AS costo_promedio,
                COALESCE(
                    NULLIF(orden_salida_detalle.subtotal, 0),
                    orden_salida_detalle.cantidad * COALESCE(
                        NULLIF(orden_salida_detalle.costo_peps, 0),
                        NULLIF(orden_salida_detalle.costo_unitario, 0),
                        NULLIF(productos.costo_reposicion, 0),
                        ultima_compra.precio_unitario,
                        0
                    )
                ) AS subtotal
            FROM orden_salida_detalle
            INNER JOIN productos ON productos.id = orden_salida_detalle.id_producto
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
            ) ultima_compra ON ultima_compra.id_producto = productos.id
            WHERE id_orden_salida = $id_orden_salida
        ";

        return $this->ejecutar($sql);
    }

    public function obtenerOrdenSalida($id)
    {
        $id = (int)$id;

        $sql = "
            SELECT ordenes_salida.*, usuarios.nombre AS nombre_usuario, areas.nombre AS nombre_area
            FROM ordenes_salida
            inner join usuarios on usuarios.id = ordenes_salida.id_usuario
            LEFT JOIN areas ON areas.id = ordenes_salida.id_area
            WHERE ordenes_salida.id = $id
            LIMIT 1
        ";

        return $this->ejecutar($sql);
    }

    public function bloquearOrdenSalida($id)
    {
        $id = (int)$id;
        return $this->ejecutar("
            SELECT *
            FROM ordenes_salida
            WHERE id = $id
            LIMIT 1
            FOR UPDATE
        ");
    }

    public function listarOrdenesSalida($limit = null, $fechaInicio = '', $fechaFin = '')
    {
        $limitSql = $limit !== null ? " LIMIT " . max(1, (int)$limit) : "";
        $whereSql = $this->construirFiltroFecha('os.fecha_salida', $fechaInicio, $fechaFin);

        $sql = "
            SELECT
                os.id,
                os.folio,
                os.fecha_salida,
                os.tipo,
                os.id_area,
                os.estatus,
                os.id_usuario,
                os.created_at,
                COALESCE(u.nombre, CONCAT('Usuario #', os.id_usuario)) AS nombre_usuario,
                COALESCE(a.nombre, '') AS nombre_area
            FROM ordenes_salida os
            LEFT JOIN usuarios u ON u.id = os.id_usuario
            LEFT JOIN areas a ON a.id = os.id_area
            $whereSql
            ORDER BY os.created_at DESC
            $limitSql
        ";

        return $this->ejecutar($sql);
    }

    public function listarSalidasDetalle($fechaInicio = '', $fechaFin = '', $idArea = 0)
    {
        $condiciones = [];
        $fechaInicio = $this->normalizarFecha($fechaInicio);
        $fechaFin = $this->normalizarFecha($fechaFin);
        $idArea = (int)$idArea;

        if ($fechaInicio !== '') {
            $condiciones[] = "os.fecha_salida >= '$fechaInicio'";
        }

        if ($fechaFin !== '') {
            $condiciones[] = "os.fecha_salida <= '$fechaFin'";
        }

        if ($idArea > 0) {
            $condiciones[] = "os.id_area = $idArea";
        }

        $whereSql = count($condiciones) ? 'WHERE ' . implode(' AND ', $condiciones) : '';

        return $this->ejecutar("
            SELECT
                os.id,
                os.folio,
                os.fecha_salida,
                os.estatus,
                os.tipo,
                os.nota,
                COALESCE(a.nombre, '') AS area,
                p.sku,
                COALESCE(NULLIF(p.nombre, ''), p.descripcion, 'Sin nombre') AS articulo,
                p.descripcion,
                p.ubicacion,
                osd.cantidad,
                COALESCE(
                    NULLIF(osd.costo_peps, 0),
                    NULLIF(osd.costo_unitario, 0),
                    NULLIF(p.costo_reposicion, 0),
                    ultima_compra.precio_unitario,
                    0
                ) AS costo_peps,
                COALESCE(
                    NULLIF(osd.subtotal, 0),
                    osd.cantidad * COALESCE(
                        NULLIF(osd.costo_peps, 0),
                        NULLIF(osd.costo_unitario, 0),
                        NULLIF(p.costo_reposicion, 0),
                        ultima_compra.precio_unitario,
                        0
                    )
                ) AS subtotal,
                COALESCE(u.nombre, CONCAT('Usuario #', os.id_usuario)) AS nombre_usuario
            FROM orden_salida_detalle osd
            INNER JOIN ordenes_salida os ON os.id = osd.id_orden_salida
            INNER JOIN productos p ON p.id = osd.id_producto
            LEFT JOIN areas a ON a.id = os.id_area
            LEFT JOIN usuarios u ON u.id = os.id_usuario
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
            $whereSql
            ORDER BY os.fecha_salida DESC, os.id DESC, articulo ASC
        ");
    }


    public function dameUltimoIdOrdenSalida()
    {
        $ultimoId = $this->ultimoId();

        if ($ultimoId > 0) {
            return $ultimoId;
        }

        $sql = "SELECT COALESCE(MAX(id), 0) AS ultimo_id FROM ordenes_salida";
        $resultado = $this->ejecutar($sql);
        $fila = json_decode($resultado, true)[0] ?? ['ultimo_id' => 0];
        return (int)$fila['ultimo_id'];
    }

    public function actualizarEstatusOrdenCompra($id, $estatus)
    {
        $id = (int)$id;
        $estatus = $this->limpiar($estatus);

        $sql = "
            UPDATE ordenes_compra
            SET estatus = '$estatus'
            WHERE id = $id
        ";

        return $this->ejecutar($sql);
    }

    public function actualizarEstatusOrdenSalida($id, $estatus)
    {
        $id = (int)$id;
        $estatus = $this->limpiar($estatus);

        $sql = "
            UPDATE ordenes_salida
            SET estatus = '$estatus'
            WHERE id = $id
        ";

        return $this->ejecutar($sql);
    }

    public function actualizarDetalleOrdenCompra($idOrden, $idProducto, $precioUnitario)
    {
        $idOrden = (int)$idOrden;
        $idProducto = (int)$idProducto;
        $precioUnitario = (float)$precioUnitario;

        $sql = "
            UPDATE orden_compra_detalle
            SET
                precio_unitario = $precioUnitario,
                subtotal = cantidad * $precioUnitario
            WHERE id_orden_compra = $idOrden
              AND id_producto = $idProducto
        ";

        return $this->ejecutar($sql);
    }

    public function registrarEntradaInventario($idProducto, $cantidad)
    {
        $idProducto = (int)$idProducto;
        $cantidad = (float)$cantidad;

        $sql = "
            INSERT INTO inventario (id_producto, stock, updated_at)
            VALUES ($idProducto, $cantidad, NOW())
            ON DUPLICATE KEY UPDATE
                stock = stock + VALUES(stock),
                updated_at = VALUES(updated_at)
        ";

        return $this->ejecutar($sql);
    }

    public function registrarLoteInventario($idProducto, $idOrdenCompra, $cantidad, $precioUnitario, $fechaEntrada)
    {
        $idProducto = (int)$idProducto;
        $idOrdenCompra = (int)$idOrdenCompra;
        $cantidad = (float)$cantidad;
        $precioUnitario = (float)$precioUnitario;
        $fechaEntrada = $this->limpiar($fechaEntrada);

        $sql = "
            INSERT INTO inventario_lotes
                (id_producto, id_orden_compra, fecha_entrada, cantidad_inicial, cantidad_disponible, costo_unitario)
            VALUES
                ($idProducto, $idOrdenCompra, '$fechaEntrada', $cantidad, $cantidad, $precioUnitario)
        ";

        return $this->ejecutar($sql);
    }

    public function ordenCompraTieneLotes($idOrdenCompra)
    {
        $idOrdenCompra = (int)$idOrdenCompra;
        $resultado = json_decode($this->ejecutar("
            SELECT COUNT(*) AS total
            FROM inventario_lotes
            WHERE id_orden_compra = $idOrdenCompra
        "), true);

        return (int)($resultado[0]['total'] ?? 0) > 0;
    }

    public function registrarSalidaInventario($idProducto, $cantidad)
    {
        $idProducto = (int)$idProducto;
        $cantidad = (float)$cantidad * -1;

        $sql = "
            INSERT INTO inventario (id_producto, stock, updated_at)
            VALUES ($idProducto, $cantidad, NOW())
            ON DUPLICATE KEY UPDATE
                stock = stock + VALUES(stock),
                updated_at = VALUES(updated_at)
        ";

        return $this->ejecutar($sql);
    }

    public function consumirInventarioPeps($idProducto, $cantidad)
    {
        $idProducto = (int)$idProducto;
        $cantidadPendiente = (float)$cantidad;
        $costoTotal = 0.0;
        $cantidadOriginal = $cantidadPendiente;

        $stockActual = json_decode($this->ejecutar("
            SELECT COALESCE(inv.stock, 0) AS stock, COALESCE(p.costo_reposicion, 0) AS costo
            FROM productos p
            LEFT JOIN inventario inv ON inv.id_producto = p.id
            WHERE p.id = $idProducto
            LIMIT 1
        "), true)[0] ?? ['stock' => 0, 'costo' => 0];

        $lotesResumen = json_decode($this->ejecutar("
            SELECT
                COALESCE(SUM(cantidad_disponible), 0) AS disponible,
                COUNT(*) AS total_lotes
            FROM inventario_lotes
            WHERE id_producto = $idProducto
        "), true)[0] ?? ['disponible' => 0, 'total_lotes' => 0];

        $loteDisponible = (float)($lotesResumen['disponible'] ?? 0);
        $totalLotes = (int)($lotesResumen['total_lotes'] ?? 0);
        $stockSinLote = (float)$stockActual['stock'] - $loteDisponible;
        if ($totalLotes === 0 && $stockSinLote > 0.0001) {
            $costoLegacy = (float)$stockActual['costo'];
            $this->ejecutar("
                INSERT INTO inventario_lotes
                    (id_producto, id_orden_compra, fecha_entrada, cantidad_inicial, cantidad_disponible, costo_unitario)
                VALUES
                    ($idProducto, NULL, '1970-01-01', $stockSinLote, $stockSinLote, $costoLegacy)
            ");
        }

        $lotes = json_decode($this->ejecutar("
            SELECT id, cantidad_disponible, costo_unitario
            FROM inventario_lotes
            WHERE id_producto = $idProducto
              AND cantidad_disponible > 0
            ORDER BY fecha_entrada ASC, id ASC
        "), true) ?: [];

        foreach ($lotes as $lote) {
            if ($cantidadPendiente <= 0) {
                break;
            }

            $disponible = (float)$lote['cantidad_disponible'];
            $consumir = min($disponible, $cantidadPendiente);
            $nuevoDisponible = $disponible - $consumir;
            $idLote = (int)$lote['id'];

            $this->ejecutar("
                UPDATE inventario_lotes
                SET cantidad_disponible = $nuevoDisponible
                WHERE id = $idLote
            ");

            $costoTotal += $consumir * (float)$lote['costo_unitario'];
            $cantidadPendiente -= $consumir;
        }

        if ($cantidadPendiente > 0.0001) {
            throw new Exception(json_encode([
                'status' => 'error',
                'message' => 'No hay inventario PEPS suficiente para el producto #' . $idProducto,
            ]));
        }

        return $cantidadOriginal > 0 ? $costoTotal / $cantidadOriginal : 0;
    }

    public function actualizarCostoDetalleSalida($idOrden, $idProducto, $costoUnitario)
    {
        $idOrden = (int)$idOrden;
        $idProducto = (int)$idProducto;
        $costoUnitario = (float)$costoUnitario;

        return $this->ejecutar("
            UPDATE orden_salida_detalle
            SET costo_unitario = $costoUnitario,
                costo_peps = $costoUnitario,
                subtotal = cantidad * $costoUnitario
            WHERE id_orden_salida = $idOrden
              AND id_producto = $idProducto
        ");
    }

    public function contarOrdenesCompraPorEstatus(array $estatuses)
    {
        if (count($estatuses) === 0) {
            return 0;
        }

        $estatusSql = array_map(fn($estatus) => "'" . $this->limpiar($estatus) . "'", $estatuses);
        $sql = "
            SELECT COUNT(*) AS total
            FROM ordenes_compra
            WHERE estatus IN (" . implode(', ', $estatusSql) . ")
        ";

        $resultado = $this->ejecutar($sql);
        $fila = json_decode($resultado, true)[0] ?? ['total' => 0];
        return (int)$fila['total'];
    }

    public function contarOrdenesSalidaPorEstatus(array $estatuses)
    {
        if (count($estatuses) === 0) {
            return 0;
        }

        $estatusSql = array_map(fn($estatus) => "'" . $this->limpiar($estatus) . "'", $estatuses);
        $sql = "
            SELECT COUNT(*) AS total
            FROM ordenes_salida
            WHERE estatus IN (" . implode(', ', $estatusSql) . ")
        ";

        $resultado = $this->ejecutar($sql);
        $fila = json_decode($resultado, true)[0] ?? ['total' => 0];
        return (int)$fila['total'];
    }

    public function listarComprasPorProveedor($fechaInicio = '', $fechaFin = '', $idProveedor = 0)
    {
        $condiciones = [];
        $fechaInicio = $this->normalizarFecha($fechaInicio);
        $fechaFin = $this->normalizarFecha($fechaFin);
        $idProveedor = (int)$idProveedor;

        if ($fechaInicio !== '') {
            $condiciones[] = "oc.fecha_orden >= '$fechaInicio'";
        }

        if ($fechaFin !== '') {
            $condiciones[] = "oc.fecha_orden <= '$fechaFin'";
        }

        if ($idProveedor > 0) {
            $condiciones[] = "oc.id_proveedor = $idProveedor";
        }

        $whereSql = count($condiciones) ? 'WHERE ' . implode(' AND ', $condiciones) : '';

        return $this->ejecutar("
            SELECT
                oc.id,
                oc.folio,
                oc.fecha_orden,
                oc.estatus,
                pr.nombre AS proveedor,
                p.sku,
                COALESCE(NULLIF(p.nombre, ''), p.descripcion, 'Sin nombre') AS articulo,
                p.ubicacion,
                ocd.cantidad,
                ocd.precio_unitario,
                ocd.subtotal
            FROM orden_compra_detalle ocd
            INNER JOIN ordenes_compra oc ON oc.id = ocd.id_orden_compra
            INNER JOIN proveedores pr ON pr.id = oc.id_proveedor
            INNER JOIN productos p ON p.id = ocd.id_producto
            $whereSql
            ORDER BY pr.nombre ASC, oc.fecha_orden DESC, oc.id DESC
        ");
    }

    public function listarConsumosPorArea($fechaInicio = '', $fechaFin = '', $idArea = 0)
    {
        $condiciones = ["os.estatus = 'CONFIRMADA'"];
        $fechaInicio = $this->normalizarFecha($fechaInicio);
        $fechaFin = $this->normalizarFecha($fechaFin);
        $idArea = (int)$idArea;

        if ($fechaInicio !== '') {
            $condiciones[] = "os.fecha_salida >= '$fechaInicio'";
        }

        if ($fechaFin !== '') {
            $condiciones[] = "os.fecha_salida <= '$fechaFin'";
        }

        if ($idArea > 0) {
            $condiciones[] = "os.id_area = $idArea";
        }

        $whereSql = 'WHERE ' . implode(' AND ', $condiciones);

        return $this->ejecutar("
            SELECT
                COALESCE(a.nombre, 'Sin area') AS area,
                f.nombre AS familia,
                COALESCE(sf.nombre, 'Sin subfamilia') AS subfamilia,
                p.sku,
                COALESCE(NULLIF(p.nombre, ''), p.descripcion, 'Sin nombre') AS articulo,
                p.ubicacion,
                SUM(osd.cantidad) AS cantidad,
                SUM(COALESCE(
                    NULLIF(osd.subtotal, 0),
                    osd.cantidad * COALESCE(
                        NULLIF(osd.costo_peps, 0),
                        NULLIF(osd.costo_unitario, 0),
                        NULLIF(p.costo_reposicion, 0),
                        ultima_compra.precio_unitario,
                        0
                    )
                )) AS total,
                CASE
                    WHEN SUM(osd.cantidad) > 0 THEN SUM(COALESCE(
                        NULLIF(osd.subtotal, 0),
                        osd.cantidad * COALESCE(
                            NULLIF(osd.costo_peps, 0),
                            NULLIF(osd.costo_unitario, 0),
                            NULLIF(p.costo_reposicion, 0),
                            ultima_compra.precio_unitario,
                            0
                        )
                    )) / SUM(osd.cantidad)
                    ELSE 0
                END AS costo_peps_promedio
            FROM orden_salida_detalle osd
            INNER JOIN ordenes_salida os ON os.id = osd.id_orden_salida
            INNER JOIN productos p ON p.id = osd.id_producto
            INNER JOIN familias f ON f.id = p.id_familia
            LEFT JOIN subfamilias sf ON sf.id = p.id_subfamilia
            LEFT JOIN areas a ON a.id = os.id_area
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
            $whereSql
            GROUP BY a.nombre, f.nombre, sf.nombre, p.sku, articulo, p.ubicacion
            ORDER BY a.nombre ASC, f.nombre ASC, articulo ASC
        ");
    }


    private function limpiar($valor)
    {
        return htmlspecialchars(trim($valor), ENT_QUOTES, 'UTF-8');
    }

    private function construirFiltroFecha($campo, $fechaInicio = '', $fechaFin = '')
    {
        $condiciones = [];
        $fechaInicio = $this->normalizarFecha($fechaInicio);
        $fechaFin = $this->normalizarFecha($fechaFin);

        if ($fechaInicio !== '') {
            $condiciones[] = "$campo >= '$fechaInicio'";
        }

        if ($fechaFin !== '') {
            $condiciones[] = "$campo <= '$fechaFin'";
        }

        return count($condiciones) ? 'WHERE ' . implode(' AND ', $condiciones) : '';
    }

    private function normalizarFecha($fecha)
    {
        $fecha = trim((string)$fecha);

        if ($fecha === '' || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $fecha)) {
            return '';
        }

        return $this->limpiar($fecha);
    }
}
