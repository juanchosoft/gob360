-- VERIFICAR primero
SELECT id, secretaria, secretario 
FROM tbl_secretarias_municipios 
WHERE codigo_municipio = '68167'
ORDER BY id;

-- ELIMINAR
DELETE FROM tbl_secretarias_municipios 
WHERE codigo_municipio = '68167';




SELECT * FROM `tbl_plandesarrollo_alcalde` where tbl_municipio_id = '68167';


DELETE FROM tbl_plandesarrollo_alcalde 
WHERE tbl_municipio_id = '68167';