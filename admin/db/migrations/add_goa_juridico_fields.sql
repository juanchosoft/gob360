-- Migración: Agregar campos GOA Jurídico a tbl_hacienda
-- Fecha: 2026-02-27
-- Descripción: Nuevos campos para la acción "GOA Juridico":
--   Proceso en Custodia: valor_total, cantidad_procesos, cantidad_unidades
--   Destrucción de Procesos: cantidad_unidades, valor_total

ALTER TABLE `gobernacion_prod_db`.`tbl_hacienda`
    ADD COLUMN `goa_juridico_custodia_valor_total`          DECIMAL(25,2) NOT NULL DEFAULT 0.00 COMMENT 'GOA Jurídico - Proceso en Custodia: Valor Total',
    ADD COLUMN `goa_juridico_custodia_cantidad_procesos`    INT(11)       NOT NULL DEFAULT 0    COMMENT 'GOA Jurídico - Proceso en Custodia: Cantidad de Procesos',
    ADD COLUMN `goa_juridico_custodia_cantidad_unidades`    INT(11)       NOT NULL DEFAULT 0    COMMENT 'GOA Jurídico - Proceso en Custodia: Cantidad de Unidades',
    ADD COLUMN `goa_juridico_destruccion_cantidad_unidades` INT(11)       NOT NULL DEFAULT 0    COMMENT 'GOA Jurídico - Destrucción: Cantidad de Unidades',
    ADD COLUMN `goa_juridico_destruccion_valor_total`       DECIMAL(25,2) NOT NULL DEFAULT 0.00 COMMENT 'GOA Jurídico - Destrucción: Valor Total';
