

CREATE TABLE `tbl_visitas_x_observaciones` (
  `id` int(11) NOT NULL,
  `tbl_visita_id` int(11) NOT NULL,
  `observaciones` text DEFAULT NULL,
  `tbl_usuario_id` int(11) NOT NULL,
  `dtcreate` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;


ALTER TABLE `tbl_visitas_x_observaciones`
  ADD PRIMARY KEY (`id`);


ALTER TABLE `tbl_visitas_x_observaciones`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;


ALTER TABLE tbl_visitas 
ADD COLUMN estado_autorizar VARCHAR(40) 
CHARACTER SET utf8 COLLATE utf8_spanish2_ci 
NOT NULL DEFAULT 'Sin Cumplir'
COMMENT 'Estado de autorización del compromiso'
AFTER estado;

COMMIT;