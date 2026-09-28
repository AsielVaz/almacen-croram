ALTER TABLE ordenes_compra
    DROP FOREIGN KEY fk_oc_proveedor,
    DROP INDEX fk_oc_proveedor,
    ADD INDEX idx_oc_proveedor (id_proveedor),
    MODIFY id_proveedor INT(10) UNSIGNED NULL DEFAULT NULL;
