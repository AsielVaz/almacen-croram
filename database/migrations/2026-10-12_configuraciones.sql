CREATE TABLE IF NOT EXISTS configuraciones (
    clave VARCHAR(100) NOT NULL PRIMARY KEY,
    valor TEXT NOT NULL,
    tipo ENUM('booleano', 'entero', 'texto') NOT NULL DEFAULT 'texto',
    grupo VARCHAR(100) NOT NULL,
    etiqueta VARCHAR(200) NOT NULL,
    descripcion TEXT NOT NULL,
    actualizado_por INT UNSIGNED NULL,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Una ejecución posterior no restablece una opción desactivada por un administrador.
INSERT INTO configuraciones (clave, valor, tipo, grupo, etiqueta, descripcion)
SELECT 'almacen_confirmar_salida_sin_qr', '1', 'booleano', 'Órdenes de salida',
    'Confirmar salidas sin escanear — Almacén',
    'Permite al perfil Almacén confirmar una salida desde su detalle sin escanear los productos. Al desactivarlo se requiere el flujo de escaneo. No modifica los permisos de otros perfiles.'
WHERE NOT EXISTS (SELECT 1 FROM configuraciones WHERE clave = 'almacen_confirmar_salida_sin_qr');
