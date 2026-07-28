-- ============================================================
-- BACKUP GOA — tbl_hacienda
-- Fecha: 2026-05-14
-- Acciones respaldadas:
--   · GOA Aprehensiones de Licores
--   · GOA Aprehensión de Cigarrillos
--   · GOA Aprehensión de Cervezas
--   · GOA Aprehensión de Tabaco y Otros
--   · GOA Juridico
--
-- PASO 1: Restaurar datos (si necesitas revertir)
--   mysql -uroot -proot123 gobernacion_prod_db < este_archivo.sql
-- PASO 2: El DELETE está al final — ejecutar cuando estés seguro
-- ============================================================

-- -----------------------------------------------
-- RESTAURACIÓN: INSERT de los registros actuales
-- -----------------------------------------------

INSERT INTO `tbl_hacienda` (`id`, `tipo`, `dtcreate_at`, `accion`, `cantidad`, `incautacion_licores`, `valor_licores`, `tipo_cigarrillo`, `incautacion_cigarrillos`, `valor_cigarrillos`, `tipo_tabaco`, `incautacion_tabaco`, `valor_tabaco`, `incautacion_cerveza`, `valor_cerveza`, `valor_importado`, `valor_nacional`, `valor_tramite`, `valor_recaudo`, `valor_tramite_impuesto_vehicular`, `valor_recaudo_impuesto_vehicular`, `estampilla`, `valor_estampilla`, `capacitacion_programada`, `capacitacion_ejecutada`, `cantidad_personas`, `observaciones`, `tbl_municipio_id`, `date`, `secretaria`, `objeto`, `provincia`, `tbl_departamento_id`, `tbl_usuario_id`, `valor_total`, `foto`, `cantidad_aprehendida`, `avaluo_comercial`, `cantidad_visitas_al_municipio`, `tipo_capacitacion_goa`, `numero_asistentes`, `direccion`, `barrio`, `administrador`, `nombre_establecimiento`, `tipoe`, `establecimiento`, `goa_juridico_custodia_valor_total`, `goa_juridico_custodia_cantidad_procesos`, `goa_juridico_custodia_cantidad_unidades`, `goa_juridico_destruccion_cantidad_unidades`, `goa_juridico_destruccion_valor_total`, `vehicular_cantidad_operativos`, `vehicular_cantidad_emplazados`, `vehicular_cantidad_placas_consultadas`, `vehicular_cantidad_campanas_sensibilizacion`)
VALUES
(1,'','2026-05-15 00:43:52','GOA Aprehensiones de Licores',0,0,0.00,'',0,0.00,'',0,0.00,0,0.00,0.00,0.00,0.00,0.00,0.00,0.00,'',0.00,0,NULL,0,'Observaciones opcionales',843,'2026-05-14','Hacienda','Descripción del objeto','Soto',68,2,0.00,'',120.00,4500000.00,0,'',0,'Calle 10 # 5-20','Centro','Juan Pérez','Tienda El Progreso','Tienda','Tienda de barrio',0.00,0,0,0,0.00,0,0,0,0),
(2,'','2026-05-15 01:14:15','GOA Aprehensión de Cigarrillos',0,0,0.00,'',0,0.00,'',0,0.00,0,0.00,0.00,0.00,0.00,0.00,0.00,0.00,'',0.00,0,NULL,0,'Observaciones opcionales',843,'2026-05-14','Hacienda','Descripción del objeto','Soto',68,2,0.00,'',120.00,4500000.00,0,'',0,'Calle 10 # 5-20','Centro','Juan Pérez','Tienda El Progreso','Tienda','Tienda de barrio',0.00,0,0,0,0.00,0,0,0,0);

-- -----------------------------------------------
-- RESUMEN DE REGISTROS RESPALDADOS
-- -----------------------------------------------
-- accion                              | total
-- ----------------------------------- | -----
-- GOA Aprehensiones de Licores        |     1  (id=1, dato de prueba/plantilla)
-- GOA Aprehensión de Cigarrillos      |     1  (id=2, dato de prueba/plantilla)
-- GOA Aprehensión de Cervezas         |     0
-- GOA Aprehensión de Tabaco y Otros   |     0
-- GOA Juridico                        |     0
-- TOTAL                               |     2
--
-- NOTA: Ambos registros son datos de ejemplo generados con la plantilla Excel
--       (municipio Bucaramanga, fecha 2026-05-14, valores de demo).
--       Se pueden eliminar con seguridad.
-- -----------------------------------------------

-- -----------------------------------------------
-- ELIMINACIÓN — ejecutar cuando estés listo
-- -----------------------------------------------
DELETE FROM `tbl_hacienda`
WHERE accion IN (
    'GOA Aprehensiones de Licores',
    'GOA Aprehensión de Cigarrillos',
    'GOA Aprehensión de Cervezas',
    'GOA Aprehensión de Tabaco y Otros',
    'GOA Juridico'
);

-- Verificar resultado:
-- SELECT accion, COUNT(*) FROM tbl_hacienda
-- WHERE accion IN ('GOA Aprehensiones de Licores','GOA Aprehensión de Cigarrillos',
--   'GOA Aprehensión de Cervezas','GOA Aprehensión de Tabaco y Otros','GOA Juridico')
-- GROUP BY accion;
