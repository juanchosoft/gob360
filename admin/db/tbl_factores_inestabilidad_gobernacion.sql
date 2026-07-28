CREATE TABLE `tbl_factores_inestabilidad_gobernacion` (
  `id` int(11) NOT NULL,
  `nombre_categoria` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `dtcreate` datetime NOT NULL,
  `tec_usuario_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

ALTER TABLE `tbl_factores_inestabilidad_gobernacion` ADD PRIMARY KEY (`id`);
ALTER TABLE `tbl_factores_inestabilidad_gobernacion` MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
COMMIT;

INSERT INTO `tbl_factores_inestabilidad_gobernacion` (`nombre_categoria`, `dtcreate`, `tec_usuario_id`) VALUES
('Seguridad', NOW(), 1),
('Social y Cultural', NOW(), 1),
('Infraestructura y Conectividad', NOW(), 1),
('Politico Institucional', NOW(), 1),
('Ambiental', NOW(), 1),
('Económico', NOW(), 1);
