-- Empresas asociadas a municipios (Acción Unificada)
CREATE TABLE IF NOT EXISTS `tbl_empresas` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL COMMENT 'Usuario que registra o actualiza',
  `dt_create` datetime NOT NULL,
  `dt_update` datetime DEFAULT NULL,
  `nombre_empresa` varchar(255) NOT NULL,
  `nit` varchar(50) DEFAULT NULL,
  `nombre_contacto` varchar(255) NOT NULL,
  `telefono_contacto` varchar(50) NOT NULL,
  `email_contacto` varchar(255) DEFAULT NULL,
  `codigo_muncipio` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_empresas_municipio` (`codigo_muncipio`),
  KEY `idx_empresas_user` (`user_id`),
  KEY `idx_empresas_nombre` (`nombre_empresa`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
