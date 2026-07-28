ALTER TABLE `tbl_proyectos_planeacion_alcaldia`
    ADD COLUMN `documento3` VARCHAR(500) NULL DEFAULT NULL AFTER `documento2`,
    ADD COLUMN `documento4` VARCHAR(500) NULL DEFAULT NULL AFTER `documento3`,
    ADD COLUMN `documento5` VARCHAR(500) NULL DEFAULT NULL AFTER `documento4`,
    ADD COLUMN `documento6` VARCHAR(500) NULL DEFAULT NULL AFTER `documento5`;
