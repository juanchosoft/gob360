-- ============================================================
-- Migración: Agregar columnas request_url y response_body
-- a la tabla tbl_log_api_pae_arcgis
-- Fecha: 2026-02-23
-- Motivo: La tabla fue creada sin estas columnas, lo que
--         causaba que el INSERT fallara silenciosamente y
--         los logs no se guardaran.
-- ============================================================

ALTER TABLE `gobernacion_prod_db`.`tbl_log_api_pae_arcgis`
  ADD COLUMN `request_url`   TEXT     DEFAULT NULL COMMENT 'URL completa con parámetros enviada a ArcGIS' AFTER `municipio_nombre`,
  ADD COLUMN `response_body` LONGTEXT DEFAULT NULL COMMENT 'Cuerpo completo de la respuesta JSON de ArcGIS' AFTER `request_url`;
