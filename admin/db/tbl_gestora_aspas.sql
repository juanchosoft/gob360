
CREATE TABLE `tbl_gestora_aspas` (
  `id` int(11) NOT NULL,
  `tbl_acciong_id` int(11) NOT NULL,
  `provincia` varchar(150) DEFAULT NULL,
  `impactada` varchar(20) DEFAULT NULL,
  `desc_actividad` varchar(255) DEFAULT NULL,
  `date` varchar(100) DEFAULT NULL,
  `tbl_departamento_id` int(11) DEFAULT NULL,
  `tbl_municipio_id` int(11) DEFAULT NULL,
  `poblacion` varchar(100) DEFAULT NULL,
  `tbl_usuario_id` int(11) NOT NULL,
  `img` varchar(255) DEFAULT NULL,
  `inversion` varchar(100) DEFAULT NULL,
  `foto1` varchar(255) DEFAULT NULL,
  `foto2` varchar(255) DEFAULT NULL,
  `foto3` varchar(255) DEFAULT NULL,
  `foto4` varchar(255) DEFAULT NULL,
  `dtcreate` datetime NOT NULL,
  `linea` varchar(155) DEFAULT NULL,
  `estrategia` varchar(155) DEFAULT NULL,
  `campana` text DEFAULT NULL,
  `actividad` text DEFAULT NULL,
  `link` varchar(255) DEFAULT NULL COMMENT 'Link mediatico',
  `tbl_linea_id` int(11) DEFAULT NULL,
  `tbl_estrategia_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


ALTER TABLE `tbl_gestora_aspas`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `tbl_gestora_aspas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
COMMIT;
