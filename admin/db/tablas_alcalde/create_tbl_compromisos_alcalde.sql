-- Tabla de compromisos para Alcalde
-- Diferencia con tbl_compromisos: usa vereda en lugar de provincia

CREATE TABLE IF NOT EXISTS `tbl_compromisos_alcalde` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `compromisos` text COLLATE utf8_spanish_ci NOT NULL COMMENT 'Descripción del compromiso',
  `compromiso_pactado` text COLLATE utf8_spanish_ci DEFAULT NULL COMMENT 'Compromiso pactado',
  `consecuencia` text COLLATE utf8_spanish_ci DEFAULT NULL COMMENT 'Consecuencia si no se cumple',
  `respuesta` text COLLATE utf8_spanish_ci DEFAULT NULL COMMENT 'Respuesta o seguimiento',
  `cumplimiento` enum('Cumplido','En Trámite','Sin Cumplir') COLLATE utf8_spanish_ci DEFAULT 'En Trámite' COMMENT 'Estado del compromiso',
  `tbl_municipio_id` varchar(10) COLLATE utf8_spanish_ci DEFAULT NULL COMMENT 'Código del municipio',
  `tbl_vereda_id` int(11) DEFAULT NULL COMMENT 'ID de la vereda (reemplaza provincia)',
  `componente` varchar(255) COLLATE utf8_spanish_ci DEFAULT NULL COMMENT 'Componente o categoría',
  `tipo_ejecucion` enum('INVERSIÓN','GESTIÓN') COLLATE utf8_spanish_ci DEFAULT 'GESTIÓN' COMMENT 'Tipo de ejecución',
  `tbl_secretarias_id` int(11) NOT NULL COMMENT 'ID de la secretaría responsable',
  `img` varchar(255) COLLATE utf8_spanish_ci DEFAULT NULL COMMENT 'Imagen adjunta',
  `date` timestamp NOT NULL DEFAULT current_timestamp() COMMENT 'Fecha de creación',
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp() COMMENT 'Fecha de última actualización',
  PRIMARY KEY (`id`),
  KEY `idx_municipio` (`tbl_municipio_id`),
  KEY `idx_vereda` (`tbl_vereda_id`),
  KEY `idx_secretaria` (`tbl_secretarias_id`),
  KEY `idx_cumplimiento` (`cumplimiento`),
  KEY `idx_fecha` (`date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci COMMENT='Compromisos por municipio y vereda para Alcaldes';
