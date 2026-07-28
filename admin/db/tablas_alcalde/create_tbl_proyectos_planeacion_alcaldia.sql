CREATE TABLE `tbl_proyectos_planeacion_alcaldia` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `fecha` DATE NOT NULL,
  
  -- IDs: Usamos UNSIGNED para ganar rango y eficiencia en llaves foráneas
  `tbl_municipio_id` INT UNSIGNED NOT NULL COMMENT 'Código del Municipio',
  `tbl_secretarias_id` INT UNSIGNED NOT NULL COMMENT 'Código de la Secretaría',
  `tbl_meta_id` INT UNSIGNED NOT NULL,
  `usuario_creador_id` INT UNSIGNED DEFAULT NULL,
  
  -- Textos: Ajuste de longitud para optimizar el ancho de fila
  `proyecto` VARCHAR(255) NOT NULL,
  `secretario_planeacion` VARCHAR(150) DEFAULT NULL,
  `bpin` VARCHAR(50) DEFAULT NULL,
  
  -- Estado: Se mantiene como VARCHAR según tu requerimiento
  `estado_proyecto` VARCHAR(50) DEFAULT 'Enviado',
  
  -- Financiero: DECIMAL(19,2) es el estándar para contabilidad (hasta 100 billones)
  `valor_proyecto` DECIMAL(19,2) NOT NULL DEFAULT 0.00,
  
  -- Archivos y Observaciones
  `observaciones` TEXT DEFAULT NULL,
  `foto2` VARCHAR(255) DEFAULT NULL,
  `documento2` VARCHAR(255) DEFAULT NULL,
  
  -- Tiempos: Auditoría automática
  `dtcreatedatetime` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `dtupdatedatetime` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

  PRIMARY KEY (`id`),
  
  -- ÍNDICES PARA ALTO RENDIMIENTO (Crucial para "mucha data")
  INDEX `idx_fecha` (`fecha`),
  INDEX `idx_municipio_estado` (`tbl_municipio_id`, `estado_proyecto`),
  INDEX `idx_bpin` (`bpin`),
  INDEX `idx_secretaria` (`tbl_secretarias_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;