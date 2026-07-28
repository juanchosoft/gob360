-- Agrega campo boletin_no a tbl_dash_interior_meta
ALTER TABLE `tbl_dash_interior_meta`
  ADD COLUMN `boletin_no` INT(11) NULL DEFAULT NULL COMMENT 'Número del boletín' AFTER `anio_2`;
