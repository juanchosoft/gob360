-- Agregar columna nombre_api_arcgis_pae a tbl_ciudades_accion_unificada
ALTER TABLE gobernacion_prod_db.tbl_ciudades_accion_unificada 
ADD COLUMN nombre_api_arcgis_pae VARCHAR(100) NULL 
COMMENT 'Nombre del municipio en la API de ArcGIS PAE' 
AFTER nombre_api_rpc;

-- Actualizar los nombres de municipios de ArcGIS PAE
UPDATE gobernacion_prod_db.tbl_ciudades_accion_unificada 
SET nombre_api_arcgis_pae = UPPER(
    REPLACE(
        REPLACE(
            REPLACE(
                REPLACE(
                    REPLACE(
                        REPLACE(
                            REPLACE(
                                REPLACE(municipio, 'á', 'a'),
                            'é', 'e'),
                        'í', 'i'),
                    'ó', 'o'),
                'ú', 'u'),
            'ñ', 'n'),
        ' ', '_')
);

-- Verificar los nombres actualizados
SELECT codigo_municipio, municipio, nombre_api_arcgis_pae 
FROM gobernacion_prod_db.tbl_ciudades_accion_unificada 
WHERE codigo_departamento = 68 
ORDER BY nombre_api_arcgis_pae;
