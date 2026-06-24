ALTER TABLE productos
    ADD COLUMN ubicacion VARCHAR(150) NULL AFTER descripcion,
    ADD INDEX idx_productos_ubicacion (ubicacion);
