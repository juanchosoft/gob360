CREATE TABLE `tbl_ingreso_informacion_x_actualizacion` (
  `id` int(10) UNSIGNED NOT NULL,
  `dtcreate` datetime NOT NULL,
  `tec_usuario_id` int(11) DEFAULT NULL,
  `valor_actualizacion` int(11) DEFAULT NULL,
  `tbl_ingreso_informacion_id` int(11) DEFAULT NULL,
  `tbl_actor_id` int(11) DEFAULT NULL,
  `observaciones_actualizacion` varchar(300) DEFAULT NULL,
  `foto_actualizada_1` varchar(155) DEFAULT NULL,
  `foto_actualizada_2` varchar(155) DEFAULT NULL,
  `foto_actualizada_3` varchar(155) DEFAULT NULL,
  `foto_actualizada_4` varchar(155) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

ALTER TABLE `tbl_ingreso_informacion_x_actualizacion`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `tbl_ingreso_informacion_x_actualizacion`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;
COMMIT;

