-- Script de creación de tabla tbl_componente_municipios
-- Gestión de componentes por municipio
-- Creado: 2026-01-08

CREATE TABLE IF NOT EXISTS `tbl_componente_municipios` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nombre_componente` varchar(255) NOT NULL,
  `codigo_departamento` varchar(10) NOT NULL,
  `codigo_municipio` varchar(10) NOT NULL,
  `habilitado` enum('si','no') NOT NULL DEFAULT 'si',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_codigo_municipio` (`codigo_municipio`),
  KEY `idx_codigo_departamento` (`codigo_departamento`),
  KEY `idx_habilitado` (`habilitado`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
