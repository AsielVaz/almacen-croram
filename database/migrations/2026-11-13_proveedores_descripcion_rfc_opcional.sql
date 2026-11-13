SET @agregar_descripcion = IF(
    EXISTS(
        SELECT 1
        FROM information_schema.COLUMNS
        WHERE TABLE_SCHEMA = DATABASE()
          AND TABLE_NAME = 'proveedores'
          AND COLUMN_NAME = 'descripcion'
    ),
    'SELECT 1',
    'ALTER TABLE proveedores ADD COLUMN descripcion TEXT NULL AFTER contacto'
);

PREPARE migracion_proveedores FROM @agregar_descripcion;
EXECUTE migracion_proveedores;
DEALLOCATE PREPARE migracion_proveedores;

ALTER TABLE proveedores
    MODIFY COLUMN rfc VARCHAR(20) NULL DEFAULT NULL;

UPDATE proveedores
SET rfc = NULL
WHERE TRIM(COALESCE(rfc, '')) = '';
