INSERT INTO inventario_lotes
    (
        id_producto,
        id_orden_compra,
        fecha_entrada,
        cantidad_inicial,
        cantidad_disponible,
        costo_unitario
    )
SELECT
    i.id_producto,
    NULL,
    '1970-01-01',
    i.stock - COALESCE(lotes.cantidad_disponible, 0),
    i.stock - COALESCE(lotes.cantidad_disponible, 0),
    COALESCE(NULLIF(p.costo_reposicion, 0), ultima_compra.precio_unitario, 0)
FROM inventario i
INNER JOIN productos p ON p.id = i.id_producto
LEFT JOIN (
    SELECT
        id_producto,
        SUM(cantidad_disponible) AS cantidad_disponible
    FROM inventario_lotes
    GROUP BY id_producto
) lotes ON lotes.id_producto = i.id_producto
LEFT JOIN (
    SELECT
        ocd.id_producto,
        ocd.precio_unitario
    FROM orden_compra_detalle ocd
    INNER JOIN ordenes_compra oc ON oc.id = ocd.id_orden_compra
    INNER JOIN (
        SELECT
            ocd2.id_producto,
            MAX(ocd2.id) AS id_detalle
        FROM orden_compra_detalle ocd2
        INNER JOIN ordenes_compra oc2 ON oc2.id = ocd2.id_orden_compra
        WHERE oc2.estatus = 'RECIBIDA'
        GROUP BY ocd2.id_producto
    ) recibida
        ON recibida.id_producto = ocd.id_producto
       AND recibida.id_detalle = ocd.id
) ultima_compra ON ultima_compra.id_producto = i.id_producto
WHERE i.stock - COALESCE(lotes.cantidad_disponible, 0) > 0.0001;
