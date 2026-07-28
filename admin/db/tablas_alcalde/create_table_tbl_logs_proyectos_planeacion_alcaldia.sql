CREATE TABLE `tbl_logs_proyectos_planeacion_alcaldia` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `proyecto_id` INT UNSIGNED NOT NULL COMMENT 'Llave foránea de tbl_proyectos_planeacion_alcaldia',
  `usuario_id` INT UNSIGNED NOT NULL,
  
  -- Optimización de acción: VARCHAR corto o incluso TINYINT si se mapea por código
  `accion` VARCHAR(30) NOT NULL COMMENT 'Ej: Creado, Rechazado, Aprobado',
  
  `observacion` TEXT DEFAULT NULL,
  `documento_ruta` VARCHAR(255) DEFAULT NULL,
  
  -- Uso de TIMESTAMP para mayor eficiencia en logs
  `dtcreated` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,

  PRIMARY KEY (`id`),
  -- Índices optimizados
  INDEX `idx_proyecto_cronologico` (`proyecto_id`, `dtcreated`),
  INDEX `idx_usuario_id` (`usuario_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;