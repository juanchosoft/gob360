

CREATE TABLE `tbl_puntajes` (
  `id` INT(11) NOT NULL,
  `tbl_eje_id` INT(11) DEFAULT NULL COMMENT 'ID del Eje relacionado',
  `tbl_pilar_id` INT(11) DEFAULT NULL COMMENT 'ID del Pilar relacionado con el Eje',
  `tipo_medicion` VARCHAR(150) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'Tipo de medición seleccionada',
  `rango_desde` INT(11) DEFAULT NULL COMMENT 'Valor inicial del rango',
  `rango_hasta` INT(11) DEFAULT NULL COMMENT 'Valor final del rango',
  `color` VARCHAR(7) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'Color seleccionado (formato HEX)',
  `dtcreate` DATETIME NOT NULL COMMENT 'Fecha de creación del registro',
  `tbl_usuario_id` INT(11) NOT NULL COMMENT 'ID del usuario que realiza la acción'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- Configuración de Claves Primarias y Auto Incremental
ALTER TABLE `tbl_puntajes`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `tbl_puntajes`
  MODIFY `id` INT(11) NOT NULL AUTO_INCREMENT;

COMMIT;
