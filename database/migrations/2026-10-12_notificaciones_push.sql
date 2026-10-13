CREATE TABLE IF NOT EXISTS push_suscripciones (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    id_usuario INT UNSIGNED NOT NULL,
    endpoint VARCHAR(2048) NOT NULL,
    endpoint_hash CHAR(64) NOT NULL,
    clave_publica VARCHAR(255) NOT NULL,
    token_autenticacion VARCHAR(255) NOT NULL,
    codificacion VARCHAR(30) NOT NULL DEFAULT 'aes128gcm',
    agente_usuario VARCHAR(500) DEFAULT NULL,
    activo TINYINT(1) NOT NULL DEFAULT 1,
    ultimo_error VARCHAR(500) DEFAULT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_push_endpoint_hash (endpoint_hash),
    KEY idx_push_usuario_activo (id_usuario, activo),
    CONSTRAINT fk_push_usuario FOREIGN KEY (id_usuario) REFERENCES usuarios (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS notificaciones (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    id_usuario INT UNSIGNED NOT NULL,
    tipo VARCHAR(50) NOT NULL,
    id_referencia BIGINT UNSIGNED NOT NULL,
    titulo VARCHAR(180) NOT NULL,
    mensaje VARCHAR(500) NOT NULL,
    url VARCHAR(500) NOT NULL,
    leida TINYINT(1) NOT NULL DEFAULT 0,
    push_estatus VARCHAR(30) NOT NULL DEFAULT 'PENDIENTE',
    push_intentos TINYINT UNSIGNED NOT NULL DEFAULT 0,
    push_error VARCHAR(500) DEFAULT NULL,
    procesado_at DATETIME DEFAULT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_notificacion_evento_usuario (tipo, id_referencia, id_usuario),
    KEY idx_notificaciones_usuario (id_usuario, leida, created_at),
    KEY idx_notificaciones_push (push_estatus, push_intentos, created_at),
    CONSTRAINT fk_notificacion_usuario FOREIGN KEY (id_usuario) REFERENCES usuarios (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

