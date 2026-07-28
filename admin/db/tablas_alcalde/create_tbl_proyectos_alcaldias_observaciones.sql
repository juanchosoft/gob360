-- ============================================================
-- Tabla: tbl_proyectos_alcaldias_observaciones
-- Descripción: Registro de observaciones históricas de proyectos de alcaldías
-- Esta tabla permite mantener un historial de todas las observaciones realizadas
-- ============================================================

-- Eliminar la tabla si existe (para evitar errores en re-ejecución)
DROP TABLE IF EXISTS `tbl_proyectos_alcaldias_observaciones`;

CREATE TABLE `tbl_proyectos_alcaldias_observaciones` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `tbl_proyecto_alcaldia_id` int(11) NOT NULL COMMENT 'ID del proyecto de alcaldía',
  `observaciones` text NOT NULL COMMENT 'Texto de la observación',
  `tbl_usuario_id` int(11) NOT NULL COMMENT 'Usuario que registró la observación',
  `dtcreate` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Fecha de creación',

  PRIMARY KEY (`id`),

  -- Índices para optimizar consultas
  KEY `idx_proyecto_alcaldia` (`tbl_proyecto_alcaldia_id`),
  KEY `idx_usuario` (`tbl_usuario_id`),
  KEY `idx_fecha` (`dtcreate`),
  KEY `idx_proyecto_fecha` (`tbl_proyecto_alcaldia_id`, `dtcreate`)

) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_spanish_ci COMMENT='Observaciones históricas de proyectos de alcaldías';

-- ============================================================
-- Verificar que se creó correctamente
-- ============================================================
SELECT 'Tabla tbl_proyectos_alcaldias_observaciones creada exitosamente' AS status;

DESCRIBE tbl_proyectos_alcaldias_observaciones;

-- ============================================================
-- Notas:
-- 1. Se eliminó el FOREIGN KEY para evitar errores de dependencia
-- 2. La relación se mantiene por convención (tbl_proyecto_alcaldia_id)
-- 3. Índices optimizados para consultas frecuentes
-- 4. DROP TABLE IF EXISTS para permitir re-ejecución del script
-- 5. Registro automático de fecha de creación
-- ============================================================
