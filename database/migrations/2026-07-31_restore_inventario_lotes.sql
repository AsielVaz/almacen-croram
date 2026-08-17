CREATE TABLE IF NOT EXISTS inventario_lotes (
    id INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
    id_producto INT(10) UNSIGNED NOT NULL,
    id_orden_compra INT(10) UNSIGNED DEFAULT NULL,
    fecha_entrada DATE NOT NULL,
    cantidad_inicial DECIMAL(12,4) NOT NULL,
    cantidad_disponible DECIMAL(12,4) NOT NULL,
    costo_unitario DECIMAL(12,4) NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_il_producto_fecha (id_producto, fecha_entrada, id),
    KEY idx_il_orden_compra (id_orden_compra),
    CONSTRAINT fk_il_producto
        FOREIGN KEY (id_producto) REFERENCES productos (id),
    CONSTRAINT fk_il_orden_compra
        FOREIGN KEY (id_orden_compra) REFERENCES ordenes_compra (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
