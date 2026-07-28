ALTER TABLE `santaok`.`tbl_visitas` 
MODIFY COLUMN `id` int NOT NULL AUTO_INCREMENT FIRST;

ALTER TABLE `santaok`.`tbl_visitas` 
CHANGE COLUMN `cargo` `consecuencia` varchar(100) CHARACTER SET utf8 COLLATE utf8_spanish2_ci NULL DEFAULT NULL AFTER `tipo`;

ALTER TABLE `santaok`.`tbl_visitas` 
CHANGE COLUMN `tipo` `tipo_registro` varchar(30) CHARACTER SET utf8 COLLATE utf8_spanish2_ci NULL DEFAULT NULL AFTER `provincia`;

ALTER TABLE `santaok`.`tbl_visitas` 
CHANGE COLUMN `entidad` `tipo_visita` varchar(50) CHARACTER SET utf8 COLLATE utf8_spanish2_ci NOT NULL AFTER `id`;

ALTER TABLE `santaok`.`tbl_visitas` 
CHANGE COLUMN `beneficiario` `estado` varchar(50) CHARACTER SET utf8 COLLATE utf8_spanish2_ci NOT NULL AFTER `tbl_vereda_id`;

ALTER TABLE `santaok`.`tbl_visitas` 
CHANGE COLUMN `observaciones` `descripcion_hecho` text CHARACTER SET utf8 COLLATE utf8_spanish2_ci NULL AFTER `respuesta`;

ALTER TABLE `santaok`.`tbl_visitas` 
MODIFY COLUMN `estado` varchar(50) CHARACTER SET utf8 COLLATE utf8_spanish2_ci NOT NULL DEFAULT 'Sin Cumplir' AFTER `tbl_vereda_id`;

ALTER TABLE `santaok`.`tbl_visitas`
ADD COLUMN `pdf2` varchar(155) CHARACTER SET utf8 COLLATE utf8_spanish2_ci DEFAULT NULL AFTER `pdf`,
ADD COLUMN `pdf3` varchar(155) CHARACTER SET utf8 COLLATE utf8_spanish2_ci DEFAULT NULL AFTER `pdf2`,
ADD COLUMN `pdf4` varchar(155) CHARACTER SET utf8 COLLATE utf8_spanish2_ci DEFAULT NULL AFTER `pdf3`;