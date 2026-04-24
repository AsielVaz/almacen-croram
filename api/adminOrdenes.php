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

    public function listarOrdenesCompra($limit = null)
    {
        $limitSql = $limit !== null ? " LIMIT " . max(1, (int)$limit) : "";

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
            ORDER BY oc.created_at DESC
            $limitSql
        ";

        return $this->ejecutar($sql);
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
            SELECT orden_compra_detalle.*, productos.nombre AS nombre_producto
            FROM orden_compra_detalle
            inner join productos on productos.id = orden_compra_detalle.id_producto
            WHERE id_orden_compra = $id_orden_compra
        ";

        return $this->ejecutar($sql);
    }



    //SELECT `id`, `folio`, `fecha_salida`, `tipo`, `estatus`, `id_usuario`, `created_at` FROM `ordenes_salida` WHERE 1
    public function agregarOrdenSalida( $folio, $fecha_salida, $tipo, $estatus, $id_usuario, $nota = '')
    {
        $folio = $this->limpiar($folio);
        $fecha_salida = $this->limpiar($fecha_salida);
        $tipo = $this->limpiar($tipo);
        $estatus = $this->limpiar($estatus);
        $id_usuario = (int)$id_usuario;
        $nota = $this->limpiar($nota);

        $sql = "
            INSERT INTO ordenes_salida (folio, fecha_salida, tipo, estatus, id_usuario, nota)
            VALUES ('$folio', '$fecha_salida', '$tipo', '$estatus', $id_usuario, " . ($nota !== '' ? "'$nota'" : 'NULL') . ")
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
                productos.nombre AS nombre_producto,
                COALESCE(productos.costo_reposicion, 0) AS costo_promedio
            FROM orden_salida_detalle
            INNER JOIN productos ON productos.id = orden_salida_detalle.id_producto
            WHERE id_orden_salida = $id_orden_salida
        ";

        return $this->ejecutar($sql);
    }

    public function obtenerOrdenSalida($id)
    {
        $id = (int)$id;

        $sql = "
            SELECT ordenes_salida.*, usuarios.nombre AS nombre_usuario
            FROM ordenes_salida
            inner join usuarios on usuarios.id = ordenes_salida.id_usuario
            WHERE ordenes_salida.id = $id
            LIMIT 1
        ";

        return $this->ejecutar($sql);
    }

    public function listarOrdenesSalida($limit = null)
    {
        $limitSql = $limit !== null ? " LIMIT " . max(1, (int)$limit) : "";

        $sql = "
            SELECT
                os.id,
                os.folio,
                os.fecha_salida,
                os.tipo,
                os.estatus,
                os.id_usuario,
                os.created_at,
                COALESCE(u.nombre, CONCAT('Usuario #', os.id_usuario)) AS nombre_usuario
            FROM ordenes_salida os
            LEFT JOIN usuarios u ON u.id = os.id_usuario
            ORDER BY os.created_at DESC
            $limitSql
        ";

        return $this->ejecutar($sql);
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


    private function limpiar($valor)
    {
        return htmlspecialchars(trim($valor), ENT_QUOTES, 'UTF-8');
    }
}
