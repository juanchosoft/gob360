-- Script para actualizar la tabla tbl_plandesarrollo_alcalde con nuevos campos
-- Ejecutar este script en la base de datos después de aplicar el CREATE TABLE modificado

USE `gobernacion_prod_db`;

-- Agregar nuevos campos si no existen
ALTER TABLE `tbl_plandesarrollo_alcalde`
ADD COLUMN IF NOT EXISTS `anio_2024` TEXT DEFAULT NULL COMMENT 'Planificación para el año 2024',
ADD COLUMN IF NOT EXISTS `avance_2024` VARCHAR(500) DEFAULT NULL COMMENT 'Avance del plan en 2024',
ADD COLUMN IF NOT EXISTS `avance_2025` VARCHAR(500) DEFAULT NULL COMMENT 'Avance del plan en 2025',
ADD COLUMN IF NOT EXISTS `anio_2025` TEXT DEFAULT NULL COMMENT 'Planificación para el año 2025',
ADD COLUMN IF NOT EXISTS `anio_2026` TEXT DEFAULT NULL COMMENT 'Planificación para el año 2026',
ADD COLUMN IF NOT EXISTS `anio_2027` TEXT DEFAULT NULL COMMENT 'Planificación para el año 2027';