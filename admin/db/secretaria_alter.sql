ALTER TABLE `gobersantander_prod`.`tbl_secretarias` 
ADD COLUMN `tiene_inversion` VARCHAR(4) NULL DEFAULT 'si' AFTER `update_at`;
