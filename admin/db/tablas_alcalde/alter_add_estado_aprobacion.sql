-- Agregar campos necesarios para el sistema de aprobación en tbl_compromisos_alcalde

ALTER TABLE `tbl_compromisos_alcalde`
ADD COLUMN `estado` ENUM('En Espera','Aprobado','Rechazado') DEFAULT NULL COMMENT 'Estado de aprobación del compromiso' AFTER `cumplimiento`,
ADD COLUMN `estado_autorizar` ENUM('Cumplido','En Trámite','Sin Cumplir') DEFAULT NULL COMMENT 'Estado al que pasará si se aprueba' AFTER `estado`,
ADD COLUMN `usuario_aprobador_id` INT(11) DEFAULT NULL COMMENT 'ID del usuario que aprobó/rechazó' AFTER `estado_autorizar`,
ADD COLUMN `fecha_aprobacion` TIMESTAMP NULL DEFAULT NULL COMMENT 'Fecha de aprobación/rechazo' AFTER `usuario_aprobador_id`,
ADD COLUMN `usuario_traslado_id` INT(11) DEFAULT NULL COMMENT 'ID del usuario que realizó traslado' AFTER `fecha_aprobacion`,
ADD COLUMN `compromiso_original_id` INT(11) DEFAULT NULL COMMENT 'ID del compromiso original si fue trasladado' AFTER `usuario_traslado_id`,
ADD INDEX `idx_estado` (`estado`),
ADD INDEX `idx_estado_autorizar` (`estado_autorizar`);

-- Crear tabla de observaciones para compromisos de alcalde
CREATE TABLE IF NOT EXISTS `tbl_compromisos_alcalde_observaciones` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `tbl_compromiso_id` INT(11) NOT NULL COMMENT 'ID del compromiso',
  `tbl_usuario_id` INT(11) NOT NULL COMMENT 'ID del usuario que agregó la observación',
  `observacion` TEXT COLLATE utf8_spanish_ci NOT NULL COMMENT 'Texto de la observación',
  `fecha` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Fecha de la observación',
  PRIMARY KEY (`id`),
  KEY `idx_compromiso` (`tbl_compromiso_id`),
  KEY `idx_usuario` (`tbl_usuario_id`),
  KEY `idx_fecha` (`fecha`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci COMMENT='Observaciones para compromisos de alcalde';
