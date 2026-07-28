-- Asegurar AUTO_INCREMENT y FK en tbl_inversion_municipios
ALTER TABLE `tbl_inversion_municipios`
    MODIFY `id` INT(11) NOT NULL AUTO_INCREMENT,
    ADD INDEX `idx_inversion_id` (`inversion_id`),
    ADD INDEX `idx_municipio_id` (`municipio_id`);
