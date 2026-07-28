-- Migración: Agregar campos Operativos Vehículos a tbl_hacienda
-- Fecha: 2026-02-27
-- Descripción: Nuevos campos para la acción "Impuesto Vehicular Recaudado":
--   Operativos Vehículos: cantidad_operativos, cantidad_emplazados,
--                         cantidad_placas_consultadas, cantidad_campanas_sensibilizacion

ALTER TABLE `gobernacion_prod_db`.`tbl_hacienda`
    ADD COLUMN `vehicular_cantidad_operativos`              BIGINT NOT NULL DEFAULT 0 COMMENT 'Impuesto Vehicular - Operativos: Cantidad de Operativos',
    ADD COLUMN `vehicular_cantidad_emplazados`              BIGINT NOT NULL DEFAULT 0 COMMENT 'Impuesto Vehicular - Operativos: Cantidad de Emplazados',
    ADD COLUMN `vehicular_cantidad_placas_consultadas`      BIGINT NOT NULL DEFAULT 0 COMMENT 'Impuesto Vehicular - Operativos: Cantidad de Placas Consultadas',
    ADD COLUMN `vehicular_cantidad_campanas_sensibilizacion` BIGINT NOT NULL DEFAULT 0 COMMENT 'Impuesto Vehicular - Operativos: Cantidad de Campañas de Sensibilización';
