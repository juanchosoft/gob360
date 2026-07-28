ALTER TABLE `santaok`.`tbl_secretarias` 
MODIFY COLUMN `id` int NOT NULL AUTO_INCREMENT FIRST;

ALTER TABLE `santaok`.`tbl_visitas` 
ADD COLUMN `requiere_respuesta` varchar(5) NULL DEFAULT 'No' AFTER `date`;
ADD COLUMN `url` varchar(50) NULL AFTER `requiere_respuesta`,
ADD COLUMN `pdf` varchar(155) NULL AFTER `url`,
ADD COLUMN `img2` varchar(155) NULL AFTER `pdf`;