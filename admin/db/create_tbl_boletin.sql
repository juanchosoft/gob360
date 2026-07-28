-- =====================================================
-- Migración: Boletines diarios (Dashboard Interior)
-- Crea tablas para soportar boletines por día
-- =====================================================

-- 1. Tabla maestra de boletines diarios
CREATE TABLE IF NOT EXISTS `tbl_boletin` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `numero` int(11) NOT NULL COMMENT 'Número secuencial del boletín',
  `fecha` date NOT NULL COMMENT 'Fecha del boletín',
  `activo` tinyint(1) NOT NULL DEFAULT 1,
  `anio_1` int(11) NOT NULL DEFAULT 2025 COMMENT 'Año de referencia',
  `anio_2` int(11) NOT NULL DEFAULT 2026 COMMENT 'Año actual',
  `fecha_cierre` date DEFAULT NULL,
  `fuente` varchar(80) DEFAULT NULL,
  `tasa_homicidios` varchar(20) DEFAULT NULL,
  `municipios_sin_homicidios` int(11) DEFAULT 0,
  `nota_html` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_numero` (`numero`),
  UNIQUE KEY `uq_fecha` (`fecha`),
  KEY `idx_fecha` (`fecha`),
  KEY `idx_activo` (`activo`),
  KEY `idx_anio_2` (`anio_2`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 2. Valores numéricos por boletín
CREATE TABLE IF NOT EXISTS `tbl_boletin_valor` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `boletin_id` int(11) NOT NULL,
  `card_key` varchar(50) NOT NULL COMMENT 'Clave del gráfico (card_key)',
  `categoria` varchar(90) NOT NULL COMMENT 'Nombre de la categoría',
  `valor` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_boletin_card_cat` (`boletin_id`, `card_key`, `categoria`),
  KEY `fk_bv_boletin` (`boletin_id`),
  CONSTRAINT `fk_bv_boletin` FOREIGN KEY (`boletin_id`) REFERENCES `tbl_boletin` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 3. Factor de atención por boletín y gráfico
CREATE TABLE IF NOT EXISTS `tbl_boletin_factor` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `boletin_id` int(11) NOT NULL,
  `card_key` varchar(50) NOT NULL,
  `texto` longtext DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_bf_boletin_card` (`boletin_id`, `card_key`),
  KEY `fk_bf_boletin` (`boletin_id`),
  CONSTRAINT `fk_bf_boletin` FOREIGN KEY (`boletin_id`) REFERENCES `tbl_boletin` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
