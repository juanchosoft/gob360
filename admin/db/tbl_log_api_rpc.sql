-- ============================================================
-- Tabla de logs para API JSON-RPC de Proyectos
-- Registra cada request y response de la API externa
-- ============================================================

CREATE TABLE IF NOT EXISTS `gobernacion_prod_db`.`tbl_log_api_rpc` (
    `id`                INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
    `fecha`             DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Fecha y hora de la petición',
    `metodo_rpc`        VARCHAR(100) NOT NULL COMMENT 'Método JSON-RPC invocado (ej: consultarProyectos)',
    `endpoint_url`      VARCHAR(500) NOT NULL COMMENT 'URL del endpoint API',
    `request_payload`   LONGTEXT NOT NULL COMMENT 'JSON completo enviado a la API (sin credenciales)',
    `request_params`    TEXT DEFAULT NULL COMMENT 'Parámetros de filtro enviados (vigencia, bpin, etc.)',
    `http_code`         INT(5) DEFAULT NULL COMMENT 'Código HTTP de respuesta (200, 401, 400, etc.)',
    `response_body`     LONGTEXT DEFAULT NULL COMMENT 'Cuerpo completo de la respuesta JSON',
    `total_registros`   INT(11) DEFAULT 0 COMMENT 'Cantidad de registros retornados en result',
    `tiempo_respuesta`  DECIMAL(8,3) DEFAULT NULL COMMENT 'Tiempo de respuesta en segundos',
    `estado`            ENUM('OK', 'ERROR_CURL', 'ERROR_HTTP', 'ERROR_JSON', 'ERROR_RPC', 'ERROR_CONFIG') NOT NULL DEFAULT 'OK' COMMENT 'Estado general de la petición',
    `error_detalle`     TEXT DEFAULT NULL COMMENT 'Detalle del error si falló',
    `ip_solicitante`    VARCHAR(45) DEFAULT NULL COMMENT 'IP del usuario que originó la consulta',
    `usuario`           VARCHAR(100) DEFAULT NULL COMMENT 'Usuario de sesión que realizó la consulta',
    `origen`            VARCHAR(50) DEFAULT 'dashboard' COMMENT 'Origen de la petición (dashboard, ajax, cron, test)',
    PRIMARY KEY (`id`),
    INDEX `idx_fecha` (`fecha`),
    INDEX `idx_metodo` (`metodo_rpc`),
    INDEX `idx_estado` (`estado`),
    INDEX `idx_http_code` (`http_code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Log de peticiones a API JSON-RPC de Proyectos externos';
