ALTER TABLE `santaok`.`tbl_vereda` 
ADD INDEX `idx_vereda` (`codigo_vereda`);

ALTER TABLE `santaok`.`tbl_ingreso_informacion` 
ADD INDEX `idx_codigo_departamento` (`codigo_departamento`),
ADD INDEX `idx_codigo_municipio` (`codigo_municipio`);
