--Consulta que actualiza registros con un estado POR CUMPLIR a SIN CUMPLIR, con la condicion que sean de tipo COMPROMISO

UPDATE tbl_visitas
SET 
    estado = 'SIN CUMPLIR'
WHERE 
    tipo_registro = 'COMPROMISO'
    AND estado = 'POR CUMPLIR';

-- --Consulta que actualiza registros con un estado_autorizar POR CUMPLIR a SIN CUMPLIR

UPDATE tbl_visitas
SET 
    estado = 'SIN CUMPLIR'
WHERE 
    tipo_registro = 'COMPROMISO'
    AND estado_autorizar = 'POR CUMPLIR';



--- Consulta que cambia el estado_autorizar a SIN CUMPLIR, cuando es null, hace que algunos regiustros salgan con una
--linea de color gris o aparezcan como "Por cumplir"


UPDATE tbl_visitas
SET estado_autorizar = 'SIN CUMPLIR'
WHERE tipo_registro = 'COMPROMISO'
  AND (
      estado_autorizar IS NULL
      OR estado_autorizar = ''
      OR estado_autorizar = 'POR CUMPLIR'
  );


---nueva columna en tbl_visitas que apuntará al ID del compromiso original
ALTER TABLE `tbl_visitas`
ADD COLUMN `compromiso_origen_id` INT(11) NULL AFTER `tbl_usuario_id`; 

---nueva columna en tbl_visitas que apuntará al id del usuario
ALTER TABLE `tbl_visitas`
ADD COLUMN `trasladado_por_usuario_id` INT(11) NULL AFTER `compromiso_origen_id`;