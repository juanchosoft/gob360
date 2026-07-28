ALTER TABLE `tbl_puntajes`
	ADD COLUMN `name` VARCHAR(255) NULL DEFAULT NULL AFTER `id`;


INSERT INTO `tbl_puntajes` (`id`, `name`, `tbl_eje_id`, `tbl_pilar_id`, `tipo_medicion`, `rango_desde`, `rango_hasta`, `color`, `dtcreate`, `tbl_usuario_id`) VALUES (10, 'Neutro', 1, 1, 'Cantidad', -1000, 0, '#fff', '2025-03-12 20:51:07', 2);



UPDATE `tbl_puntajes` SET `name`='Estable' WHERE  `id`=11 AND `name` IS NULL AND `tbl_eje_id`=1 AND `tbl_pilar_id`=1 AND `tipo_medicion`='Cantidad' AND `rango_desde`=1 AND `rango_hasta`=100 AND `color`='#62af0a' AND CAST(`dtcreate` AS CHAR)='2025-03-12 20:51:07' AND `tbl_usuario_id`=2 LIMIT 1;
UPDATE `tbl_puntajes` SET `name`='Info' WHERE  `id`=12 AND `name` IS NULL AND `tbl_eje_id`=1 AND `tbl_pilar_id`=1 AND `tipo_medicion`='Cantidad' AND `rango_desde`=101 AND `rango_hasta`=299 AND `color`='#2774f1' AND CAST(`dtcreate` AS CHAR)='2025-03-12 20:50:49' AND `tbl_usuario_id`=2 LIMIT 1;
UPDATE `tbl_puntajes` SET `name`='Medio' WHERE  `id`=13 AND `name` IS NULL AND `tbl_eje_id`=1 AND `tbl_pilar_id`=1 AND `tipo_medicion`='Cantidad' AND `rango_desde`=300 AND `rango_hasta`=1199 AND `color`='#dbd509' AND CAST(`dtcreate` AS CHAR)='2025-04-08 11:28:50' AND `tbl_usuario_id`=2 LIMIT 1;
UPDATE `tbl_puntajes` SET `name`='Alto' WHERE  `id`=14 AND `name` IS NULL AND `tbl_eje_id`=1 AND `tbl_pilar_id`=1 AND `tipo_medicion`='Cantidad' AND `rango_desde`=1200 AND `rango_hasta`=1999 AND `color`='#cd7d16' AND CAST(`dtcreate` AS CHAR)='2025-04-08 11:28:30' AND `tbl_usuario_id`=2 LIMIT 1;
UPDATE `tbl_puntajes` SET `name`='Critico' WHERE  `id`=15 AND `name` IS NULL AND `tbl_eje_id`=1 AND `tbl_pilar_id`=1 AND `tipo_medicion`='Cantidad' AND `rango_desde`=2000 AND `rango_hasta`=1000000 AND `color`='#cd162c' AND CAST(`dtcreate` AS CHAR)='2025-11-19 22:27:29' AND `tbl_usuario_id`=2 LIMIT 1;
