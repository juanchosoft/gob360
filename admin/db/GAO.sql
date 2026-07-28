ALTER TABLE `tbl_hacienda` ADD `cantidad_aprehendida` FLOAT NOT NULL DEFAULT '0' COMMENT ' Grupo Operativo Anti contrabando GOA' AFTER `foto`, ADD `avaluo_comercial` FLOAT NOT NULL DEFAULT '0' COMMENT ' Grupo Operativo Anti contrabando GOA' AFTER `cantidad_aprehendida`; 




ALTER TABLE `tbl_hacienda` CHANGE `avaluo_comercial` `avaluo_comercial` DECIMAL(25,2) NOT NULL DEFAULT '0' COMMENT ' Grupo Operativo Anti contrabando GOA'; 
ALTER TABLE `tbl_hacienda` CHANGE `cantidad_aprehendida` `cantidad_aprehendida` DECIMAL(25,2) NOT NULL DEFAULT '0' COMMENT ' Grupo Operativo Anti contrabando GOA'; 


ALTER TABLE `tbl_hacienda` ADD `cantidad_visitas_al_municipio` FLOAT NOT NULL AFTER `avaluo_comercial`; 




ALTER TABLE `tbl_hacienda` ADD `tipo_capacitacion_goa` VARCHAR(155) NOT NULL COMMENT ' Las capacitaciones del GOA' AFTER `cantidad_visitas_al_municipio`, ADD `numero_asistentes` INT NOT NULL DEFAULT '0' COMMENT 'Número de asistentes de cada capacitación\r\n' AFTER `tipo_capacitacion_goa`; 