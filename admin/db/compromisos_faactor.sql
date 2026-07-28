CREATE TABLE `tbl_compromisos_pilares_factores` ( 
    `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT, 
    `codigo_departamento` INT(11) NOT NULL, 
    `codigo_municipio` INT(11) NOT NULL, 
    `tbl_vereda_id` INT(11) NOT NULL, 
    `tbl_factor_id` INT(11) NOT NULL, 
    `tbl_actor_id` INT(11) NOT NULL, 
    `cantidad_instante` INT(11) NOT NULL, 
    `cantidad` INT(11) NOT NULL, 
    `observaciones` TEXT NOT NULL, 
    `tec_usuario_id` int(11) NOT NULL,
    `dtcreate` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP, 
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_spanish2_ci;

