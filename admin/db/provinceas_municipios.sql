UPDATE `tbl_ciudades_accion_unificada` c
INNER JOIN (
    SELECT DISTINCT provincia 
    FROM `tbl_visitas`
) v ON c.subregion = v.provincia COLLATE utf8_general_ci
SET c.subregion = v.provincia
WHERE c.codigo_departamento = 68;

