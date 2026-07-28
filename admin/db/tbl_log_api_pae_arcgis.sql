-- ============================================================
-- Tabla de logs para API REST de ArcGIS Online - Módulo PAE
-- Registra cada request y response de la API de ArcGIS
-- ============================================================

CREATE TABLE IF NOT EXISTS `gobernacion_prod_db`.`tbl_log_api_pae_arcgis` (
    `id`                INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
    `fecha`             DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Fecha y hora de la petición',
    `tipo_consulta`     VARCHAR(100) NOT NULL COMMENT 'Tipo de consulta (getDataFromArcgis, getMunicipios, etc.)',
    `endpoint_url`      VARCHAR(500) NOT NULL COMMENT 'URL completa del endpoint ArcGIS',
    `where_clause`      VARCHAR(500) DEFAULT NULL COMMENT 'Cláusula WHERE enviada a ArcGIS',
    `municipio_codigo`  VARCHAR(10) DEFAULT NULL COMMENT 'Código DANE del municipio consultado',
    `municipio_nombre`  VARCHAR(100) DEFAULT NULL COMMENT 'Nombre del municipio normalizado para ArcGIS',
    `request_url`       LONGTEXT DEFAULT NULL COMMENT 'URL completa con parámetros enviada a ArcGIS',
    `response_body`     LONGTEXT DEFAULT NULL COMMENT 'Cuerpo completo de la respuesta JSON de ArcGIS',
    `http_code`         INT(5) DEFAULT NULL COMMENT 'Código HTTP de respuesta (200, 400, 500, etc.)',
    `response_size`     INT(11) DEFAULT 0 COMMENT 'Tamaño de la respuesta en bytes',
    `total_features`    INT(11) DEFAULT 0 COMMENT 'Cantidad de features retornados por ArcGIS',
    `tiempo_respuesta`  DECIMAL(8,3) DEFAULT NULL COMMENT 'Tiempo de respuesta en segundos',
    `estado`            ENUM('OK', 'ERROR_CURL', 'ERROR_HTTP', 'ERROR_JSON', 'ERROR_API', 'ERROR_BD') NOT NULL DEFAULT 'OK' COMMENT 'Estado general de la petición',
    `error_detalle`     TEXT DEFAULT NULL COMMENT 'Detalle del error si falló',
    `ip_solicitante`    VARCHAR(45) DEFAULT NULL COMMENT 'IP del usuario que originó la consulta',
    `usuario`           VARCHAR(100) DEFAULT NULL COMMENT 'Usuario de sesión que realizó la consulta',
    `origen`            VARCHAR(50) DEFAULT 'dashboard' COMMENT 'Origen de la petición (dashboard, ajax, test)',
    PRIMARY KEY (`id`),
    INDEX `idx_fecha` (`fecha`),
    INDEX `idx_tipo_consulta` (`tipo_consulta`),
    INDEX `idx_estado` (`estado`),
    INDEX `idx_municipio` (`municipio_codigo`),
    INDEX `idx_http_code` (`http_code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Log de peticiones a API REST de ArcGIS Online - Módulo PAE';
