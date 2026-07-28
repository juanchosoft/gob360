-- Migración: Cambiar columnas INT a BIGINT en campos GOA Jurídico
-- Fecha: 2026-02-27
-- Descripción: INT(11) tiene un máximo de ~2.1 mil millones. Se cambia a BIGINT
--              para soportar valores grandes en cantidad_procesos y cantidad_unidades.

ALTER TABLE `gobernacion_prod_db`.`tbl_hacienda`
    MODIFY COLUMN `goa_juridico_custodia_cantidad_procesos`    BIGINT NOT NULL DEFAULT 0 COMMENT 'GOA Jurídico - Proceso en Custodia: Cantidad de Procesos',
    MODIFY COLUMN `goa_juridico_custodia_cantidad_unidades`    BIGINT NOT NULL DEFAULT 0 COMMENT 'GOA Jurídico - Proceso en Custodia: Cantidad de Unidades',
    MODIFY COLUMN `goa_juridico_destruccion_cantidad_unidades` BIGINT NOT NULL DEFAULT 0 COMMENT 'GOA Jurídico - Destrucción: Cantidad de Unidades';
