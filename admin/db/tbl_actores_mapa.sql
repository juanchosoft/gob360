ALTER TABLE `tbl_actores_mapa` 
ADD COLUMN `municipio_id` int NULL AFTER `tbl_usuario_id`;


ALTER TABLE `tbl_actores_mapa` 
ADD COLUMN `municipio_id` int NULL AFTER `tbl_usuario_id`,
ADD INDEX `idx_municipio`(`municipio_id`);


ADD COLUMN `actor_id` int NULL AFTER `tbl_ministerios_id`,
ADD INDEX `idx_actor`(`actor_id`);

ALTER TABLE `tbl_ministerios_proyectos` 
MODIFY COLUMN `archivo` varchar(155) CHARACTER SET utf8 COLLATE utf8_spanish_ci NULL DEFAULT NULL AFTER `observaciones`;





SCRIPT BUENO

ALTER TABLE `tbl_actores_mapa` 
ADD COLUMN `municipio_id` int NULL AFTER `tbl_usuario_id`;

ALTER TABLE `tbl_actores_mapa` 
ADD INDEX `idx_municipio`(`municipio_id`);


ALTER TABLE tbl_ministerios_proyectos 
ADD COLUMN actor_id int NULL AFTER tbl_ministerios_id,
ADD INDEX idx_actor(actor_id);


ALTER TABLE `tbl_ministerios_proyectos` 
MODIFY COLUMN `archivo` varchar(155) CHARACTER SET utf8 COLLATE utf8_spanish_ci NULL DEFAULT NULL AFTER `observaciones`;