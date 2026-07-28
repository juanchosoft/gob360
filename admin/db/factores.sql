
CREATE TABLE `tbl_factores` (
  `id` int(11) NOT NULL,
  `tbl_eje_id` int(11) DEFAULT NULL,
  `tec_area_id` int(11) DEFAULT NULL,
  `tec_pilar_id` int(11) DEFAULT NULL,
  `tipo` varchar(150) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL,
  `tipo_medicion` varchar(150) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL,
  `icono` varchar(100) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL,
  `dtcreate` datetime NOT NULL,
  `tec_usuario_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;


ALTER TABLE `tbl_factores`
  ADD PRIMARY KEY (`id`);


ALTER TABLE `tbl_factores`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
COMMIT;
