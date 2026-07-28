-- Tabla de secretarías por municipio
-- Permite tener secretarías específicas para cada municipio
-- Autor: SPIDERSOFTWARE

CREATE TABLE IF NOT EXISTS `tbl_secretarias_municipios` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `secretaria` varchar(255) COLLATE utf8_spanish_ci NOT NULL COMMENT 'Nombre de la secretaría',
  `secretario` varchar(255) COLLATE utf8_spanish_ci DEFAULT NULL COMMENT 'Nombre del secretario',
  `correo` varchar(255) COLLATE utf8_spanish_ci DEFAULT NULL COMMENT 'Correo electrónico',
  `codigo_departamento` varchar(10) COLLATE utf8_spanish_ci NOT NULL COMMENT 'Código del departamento',
  `codigo_municipio` varchar(10) COLLATE utf8_spanish_ci NOT NULL COMMENT 'Código del municipio',
  `habilitado` enum('si','no') COLLATE utf8_spanish_ci DEFAULT 'si' COMMENT 'Estado habilitado',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp() COMMENT 'Fecha de creación',
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp() COMMENT 'Fecha de última actualización',
  PRIMARY KEY (`id`),
  KEY `idx_codigo_departamento` (`codigo_departamento`),
  KEY `idx_codigo_municipio` (`codigo_municipio`),
  KEY `idx_habilitado` (`habilitado`),
  KEY `idx_secretaria` (`secretaria`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci COMMENT='Secretarías municipales';
