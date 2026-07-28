DROP TABLE IF EXISTS tbl_notificaciones_secretaria;

CREATE TABLE `tbl_notificaciones_secretaria` (
  `id` int(11) NOT NULL,
  `codigo_municipio` varchar(12) NOT NULL,
  `tbl_ministerios_proyecto_id` int(11) NOT NULL,
  `tbl_secretaria_id` int(11) NOT NULL,
  `nombre_secretria` varchar(155) DEFAULT NULL,
  `titulo` varchar(255) DEFAULT NULL,
  `mensaje` text DEFAULT NULL,
  `leido` varchar(2) DEFAULT NULL,
  `dtcreate` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;



ALTER TABLE `tbl_ministerios_proyectos` ADD `leido` VARCHAR(4) NULL DEFAULT 'no' AFTER `dtcreate`, ADD `fecha_leido` DATETIME NULL AFTER `leido`, ADD `tbl_usuario_id_leido` INT NULL AFTER `fecha_leido`; 
ALTER TABLE `tbl_ministerios_proyectos` ADD `estado` VARCHAR(60) NOT NULL DEFAULT 'Proyecto no leido' AFTER `actor_id`; 
ALTER TABLE `tbl_ministerios_proyectos` ADD `pdf` VARCHAR(255) NULL AFTER `estado`; 