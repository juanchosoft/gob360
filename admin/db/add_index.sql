ALTER TABLE `tbl_vereda` 
ADD INDEX `idx_vereda` (`codigo_vereda`);

ALTER TABLE `tbl_ingreso_informacion` 
ADD INDEX `idx_codigo_departamento` (`codigo_departamento`),
ADD INDEX `idx_codigo_municipio` (`codigo_municipio`);