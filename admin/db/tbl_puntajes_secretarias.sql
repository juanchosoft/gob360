CREATE TABLE `tbl_puntajes_secretarias` (
  `id` int(11) NOT NULL,
  `tbl_secretaria_id` int(11) DEFAULT NULL COMMENT 'ID del Secreataria relacionado',
  `tipo_medicion` varchar(150) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'Tipo de medición seleccionada',
  `rango_desde` int(11) DEFAULT NULL COMMENT 'Valor inicial del rango',
  `rango_hasta` int(11) DEFAULT NULL COMMENT 'Valor final del rango',
  `color` varchar(7) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'Color seleccionado (formato HEX)',
  `dtcreate` datetime NOT NULL COMMENT 'Fecha de creación del registro',
  `tbl_usuario_id` int(11) NOT NULL COMMENT 'ID del usuario que realiza la acción'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;


ALTER TABLE `tbl_puntajes_secretarias`
  ADD PRIMARY KEY (`id`);


ALTER TABLE `tbl_puntajes_secretarias`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
COMMIT;
