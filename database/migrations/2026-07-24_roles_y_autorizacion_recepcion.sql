ALTER TABLE usuarios
    MODIFY rol ENUM('ADMIN','ALMACEN','CONSUMIDOR','COMPRAS','VENTAS','LECTOR') NOT NULL;

ALTER TABLE orden_compra_detalle
    ADD COLUMN precio_autorizado DECIMAL(12,4) NULL AFTER precio_unitario,
    ADD COLUMN precio_recepcion DECIMAL(12,4) NULL AFTER precio_autorizado;

UPDATE orden_compra_detalle
SET precio_autorizado = precio_unitario
WHERE precio_autorizado IS NULL;

ALTER TABLE ordenes_compra
    MODIFY estatus ENUM(
        'PENDIENTE',
        'AUTORIZADA',
        'PENDIENTE_AUTORIZACION_RECEPCION',
        'CANCELADA',
        'RECIBIDA'
    ) DEFAULT 'PENDIENTE',
    ADD COLUMN requiere_autorizacion_precio TINYINT(1) NOT NULL DEFAULT 0 AFTER estatus,
    ADD COLUMN id_usuario_autoriza_compra INT(10) UNSIGNED NULL AFTER id_usuario,
    ADD COLUMN fecha_autorizacion_compra DATETIME NULL AFTER id_usuario_autoriza_compra,
    ADD COLUMN id_usuario_recepcion INT(10) UNSIGNED NULL AFTER fecha_autorizacion_compra,
    ADD COLUMN fecha_recepcion DATETIME NULL AFTER id_usuario_recepcion,
    ADD COLUMN id_usuario_autoriza_recepcion INT(10) UNSIGNED NULL AFTER fecha_recepcion,
    ADD COLUMN fecha_autorizacion_recepcion DATETIME NULL AFTER id_usuario_autoriza_recepcion,
    ADD INDEX idx_oc_autoriza_compra (id_usuario_autoriza_compra),
    ADD INDEX idx_oc_usuario_recepcion (id_usuario_recepcion),
    ADD INDEX idx_oc_autoriza_recepcion (id_usuario_autoriza_recepcion);

UPDATE inventario_movimientos
SET origen = 'COMPRA'
WHERE tipo = 'ENTRADA'
  AND origen = '';

DROP TRIGGER IF EXISTS trg_oc_recibida_entrada;

DELIMITER $$
CREATE TRIGGER trg_oc_recibida_entrada
AFTER UPDATE ON ordenes_compra
FOR EACH ROW
BEGIN
    IF OLD.estatus <> 'RECIBIDA' AND NEW.estatus = 'RECIBIDA' THEN
        INSERT INTO inventario (id_producto, stock)
        SELECT d.id_producto, d.cantidad
        FROM orden_compra_detalle d
        WHERE d.id_orden_compra = NEW.id
        ON DUPLICATE KEY UPDATE stock = stock + VALUES(stock);

        INSERT INTO inventario_movimientos
            (id_producto, tipo, origen, id_referencia, cantidad, id_usuario)
        SELECT
            d.id_producto,
            'ENTRADA',
            'COMPRA',
            NEW.id,
            d.cantidad,
            COALESCE(NEW.id_usuario_recepcion, NEW.id_usuario)
        FROM orden_compra_detalle d
        WHERE d.id_orden_compra = NEW.id;
    END IF;
END$$
DELIMITER ;
