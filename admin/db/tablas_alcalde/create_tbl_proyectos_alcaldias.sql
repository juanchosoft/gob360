-- ============================================================
-- Tabla: tbl_proyectos_alcaldias
-- Descripción: Gestión de proyectos de las alcaldías municipales
-- Optimizada con índices para mejorar el rendimiento de consultas
-- ============================================================

-- Eliminar la tabla si existe (para evitar errores en re-ejecución)
DROP TABLE IF EXISTS `tbl_proyectos_alcaldias`;

CREATE TABLE `tbl_proyectos_alcaldias` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `tbl_departamento_id` int(11) NOT NULL COMMENT 'Código de departamento',
  `tbl_municipio_id` int(11) NOT NULL COMMENT 'Código del municipio',
  `tbl_vereda_id` int(11) DEFAULT NULL COMMENT 'ID de la vereda (opcional)',
  `date` date DEFAULT NULL COMMENT 'Fecha de registro del proyecto',
  `proyecto` text NOT NULL COMMENT 'Descripción del objeto del proyecto',
  `nit` varchar(100) DEFAULT NULL COMMENT 'NIT del contratista',
  `tbl_secretarias_id` int(11) NOT NULL COMMENT 'Secretaría o dependencia encargada',
  `date_inicio` date DEFAULT NULL COMMENT 'Fecha de inicio del proyecto',
  `valor_proyecto` decimal(30,2) DEFAULT 0.00 COMMENT 'Valor total del proyecto',
  `aporte_municipio` decimal(30,2) DEFAULT 0.00 COMMENT 'Aporte del municipio',
  `aporte_nacion` decimal(30,2) DEFAULT 0.00 COMMENT 'Aporte de la nación',
  `otros_aportes` decimal(30,2) DEFAULT 0.00 COMMENT 'Otros aportes financieros',
  `entidad_otros` varchar(300) DEFAULT NULL COMMENT 'Entidad que realiza otros aportes',
  `valor_ejecutado` decimal(30,2) DEFAULT 0.00 COMMENT 'Valor ejecutado del proyecto',
  `porcentaje_ejecucion` decimal(5,2) DEFAULT 0.00 COMMENT 'Porcentaje de ejecución física',
  `porcentaje_financiero` decimal(5,2) DEFAULT 0.00 COMMENT 'Porcentaje de ejecución financiera',
  `aporte_gobernacion` decimal(30,2) DEFAULT 0.00 COMMENT 'Aporte de la gobernación',
  `adicion` decimal(30,2) DEFAULT 0.00 COMMENT 'Adición presupuestal',
  `estado` varchar(100) NOT NULL DEFAULT 'En Formulación' COMMENT 'Estado actual del proyecto',
  `dias_prorroga` int(11) DEFAULT NULL COMMENT 'Días de prórroga',
  `date_prorroga` date DEFAULT NULL COMMENT 'Fecha de prórroga',
  `observaciones` text DEFAULT NULL COMMENT 'Observaciones del proyecto',
  `contratista` varchar(255) DEFAULT NULL COMMENT 'Nombre del o los contratistas',
  `interventoria` varchar(255) DEFAULT NULL COMMENT 'Nombre del o los interventores',
  `plazo_construccion` int(11) DEFAULT NULL COMMENT 'Plazo de construcción en meses',
  `fecha_entrega` date DEFAULT NULL COMMENT 'Fecha estimada de entrega',
  `tbl_usuario_id` int(11) NOT NULL COMMENT 'Usuario que registró el proyecto',
  `dtcreate` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Fecha de creación del registro',
  `dtupdate` datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP COMMENT 'Fecha de última actualización',

  PRIMARY KEY (`id`),

  -- Índices para optimizar consultas frecuentes
  KEY `idx_municipio` (`tbl_municipio_id`),
  KEY `idx_vereda` (`tbl_vereda_id`),
  KEY `idx_secretaria` (`tbl_secretarias_id`),
  KEY `idx_departamento` (`tbl_departamento_id`),
  KEY `idx_estado` (`estado`),
  KEY `idx_usuario` (`tbl_usuario_id`),
  KEY `idx_fecha_registro` (`date`),
  KEY `idx_fecha_inicio` (`date_inicio`),
  KEY `idx_fecha_entrega` (`fecha_entrega`),

  -- Índice compuesto para consultas por municipio y secretaría
  KEY `idx_municipio_secretaria` (`tbl_municipio_id`, `tbl_secretarias_id`),

  -- Índice compuesto para consultas por estado y fecha
  KEY `idx_estado_fecha` (`estado`, `date`)

) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_spanish_ci COMMENT='Gestión de proyectos de alcaldías municipales';

-- ============================================================
-- Verificar que se creó correctamente
-- ============================================================
SELECT 'Tabla tbl_proyectos_alcaldias creada exitosamente' AS status;

DESCRIBE tbl_proyectos_alcaldias;

SHOW CREATE TABLE tbl_proyectos_alcaldias;


COMMIT;
