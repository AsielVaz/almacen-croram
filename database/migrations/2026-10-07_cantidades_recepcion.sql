-- cantidad conserva su significado operativo: unidades que ingresan al inventario.
-- La cantidad pedida se conserva al capturar la recepción; las órdenes previas
-- y las recepciones por QR siguen usando cantidad cuando este campo es NULL.
ALTER TABLE orden_compra_detalle
    ADD COLUMN cantidad_solicitada DECIMAL(12,4) NULL AFTER cantidad;
