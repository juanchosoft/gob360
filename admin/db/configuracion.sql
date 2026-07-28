

CREATE TABLE `tbl_configuracion` (
  `id` int(11) NOT NULL,
  `tipo_configuracion_colores` varchar(60) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `comentarios` text DEFAULT NULL,
  `dtcreate` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;


INSERT INTO `tbl_configuracion` (`id`, `tipo_configuracion_colores`, `comentarios`, `dtcreate`) VALUES
(1, 'Rango', 'Comentarios...', '2025-02-18 12:26:38');

ALTER TABLE `tbl_configuracion`
  ADD PRIMARY KEY (`id`);




ALTER TABLE `tbl_gestora` ADD `linea` VARCHAR(155) NULL AFTER `dtcreate`, ADD `estrategia` VARCHAR(155) NULL AFTER `linea`, ADD `campana` TEXT NULL AFTER `estrategia`, ADD `actividad` TEXT NULL AFTER `campana`; 


COMMIT;
