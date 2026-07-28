

CREATE TABLE `tbl_ingreso_informacion` (
  `id` int(10) UNSIGNED NOT NULL,
  `dtcreate` datetime NOT NULL,
  `tec_usuario_id` int(11) DEFAULT NULL,
  `valor` int(11) DEFAULT NULL,
  `longitud` varchar(90) DEFAULT NULL,
  `latitud` varchar(90) DEFAULT NULL,
  `observaciones` varchar(300) DEFAULT NULL,
  `codigo_departamento` int(11) DEFAULT NULL,
  `codigo_municipio` int(11) DEFAULT NULL,
  `tbl_vereda_id` int(11) DEFAULT NULL,
  `tbl_factor_id` int(11) DEFAULT NULL,
  `foto1` varchar(155) DEFAULT NULL,
  `foto2` varchar(155) DEFAULT NULL,
  `foto3` varchar(155) DEFAULT NULL,
  `foto4` varchar(155) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;


ALTER TABLE `tbl_ingreso_informacion`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `tbl_ingreso_informacion`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;




ALTER TABLE tbl_ingreso_informacion
ADD UNIQUE KEY unique_factor_municipio_departamento_vereda (tbl_factor_id, codigo_municipio, codigo_departamento, tbl_vereda_id);

COMMIT;