ALTER TABLE `tbl_area` 
ADD COLUMN `icono` VARCHAR(100) NULL AFTER `enable`,
ADD COLUMN `tec_usuario_id` INT NULL AFTER `icono`,
ADD COLUMN `dtcreate` DATETIME NULL AFTER `tec_usuario_id`;