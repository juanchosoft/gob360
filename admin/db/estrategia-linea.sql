
CREATE TABLE `tbl_linea` (
  `id` int(11) NOT NULL,
  `dtcreate` datetime NOT NULL,
  `nombre` varchar(90) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL,
  `descripcion` varchar(150) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL,
  `tec_usuario_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;


ALTER TABLE `tbl_linea`
  ADD PRIMARY KEY (`id`);


ALTER TABLE `tbl_linea`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
COMMIT;



CREATE TABLE `tbl_estrategia` (
  `id` int(11) NOT NULL,
  `dtcreate` datetime NOT NULL,
  `nombre` varchar(90) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL,
  `descripcion` varchar(150) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL,
  `tec_usuario_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;


ALTER TABLE `tbl_estrategia`
  ADD PRIMARY KEY (`id`);


ALTER TABLE `tbl_estrategia`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
COMMIT;





INSERT INTO `tbl_linea` (`dtcreate`, `nombre`, `tec_usuario_id`)
VALUES
    (NOW(), 'Agenda social', 2),
    (NOW(), 'Mujer', 2),
    (NOW(), 'Niñez', 2),
    (NOW(), 'Gobierno Territorial', 2);




INSERT INTO `tbl_estrategia` (`dtcreate`, `nombre`, `tec_usuario_id`)
VALUES
    (NOW(), 'Acción Unificada', 2),
    (NOW(), 'Acompañamiento', 2),
    (NOW(), 'Donación', 2),
    (NOW(), 'Campaña', 2);