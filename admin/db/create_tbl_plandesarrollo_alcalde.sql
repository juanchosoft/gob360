-- Crear tabla tbl_plandesarrollo_alcalde para el módulo de Plan de Desarrollo de Alcaldes
-- Esta tabla es exclusiva para que los alcaldes gestionen el plan de desarrollo de su municipio

CREATE TABLE IF NOT EXISTS `tbl_plandesarrollo_alcalde` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `eje_estrategico` VARCHAR(500) DEFAULT NULL COMMENT 'Eje estratégico del plan',
  `sector_pdd` VARCHAR(500) DEFAULT NULL COMMENT 'Sector PDD',
  `sector_catalogo` VARCHAR(500) DEFAULT NULL COMMENT 'Sector catálogo de productos',
  `producto_bien_servicio` TEXT DEFAULT NULL COMMENT 'Producto, bien o servicio PDD',
  `anio_2024` TEXT DEFAULT NULL COMMENT 'Planificación para el año 2024',
  `avance_2024` VARCHAR(500) DEFAULT NULL COMMENT 'Avance del plan en 2024',
  `avance_2025` VARCHAR(500) DEFAULT NULL COMMENT 'Avance del plan en 2025',
  `anio_2025` TEXT DEFAULT NULL COMMENT 'Planificación para el año 2025',
  `anio_2026` TEXT DEFAULT NULL COMMENT 'Planificación para el año 2026',
  `anio_2027` TEXT DEFAULT NULL COMMENT 'Planificación para el año 2027',
  `tbl_secretaria_id` INT(11) DEFAULT NULL COMMENT 'ID de la secretaría responsable',
  `tbl_municipio_id` VARCHAR(10) NOT NULL COMMENT 'Código del municipio del alcalde',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT 'Fecha de creación',
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'Fecha de actualización',
  PRIMARY KEY (`id`),
  KEY `idx_municipio` (`tbl_municipio_id`),
  KEY `idx_secretaria` (`tbl_secretaria_id`),
  KEY `idx_created` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Plan de Desarrollo gestionado por Alcaldes';
