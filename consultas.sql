
SELECT 
    tbl_proyectos.provincia, 
    tbl_secretarias.id AS secretaria_id, 
    tbl_secretarias.secretaria,

    -- Totales económicos consolidados
    SUM(tbl_proyectos.valor_proyecto) AS valor_proyecto_total, 
    SUM(tbl_proyectos.aporte_municipio) AS valor_municipio_total, 
    SUM(tbl_proyectos.aporte_nacion) AS valor_nacion_total, 
    SUM(tbl_proyectos.aporte_gobernacion) AS valor_departamento_total,

    -- Conteo de proyectos por estado
    COUNT(CASE WHEN tbl_proyectos.estado = 'Suspendido' THEN 1 END) AS suspendido,
    COUNT(CASE WHEN tbl_proyectos.estado = 'Terminado' THEN 1 END) AS terminado,
    COUNT(CASE WHEN tbl_proyectos.estado = 'Ejecutado' THEN 1 END) AS ejecutado,
    COUNT(CASE WHEN tbl_proyectos.estado = 'En Contrataciòn' THEN 1 END) AS en_contratacion,
    COUNT(CASE WHEN tbl_proyectos.estado = 'En Formulación' THEN 1 END) AS en_formulacion,
    COUNT(CASE WHEN tbl_proyectos.estado = 'Entregado' THEN 1 END) AS entregado,
    COUNT(CASE 
        WHEN tbl_proyectos.estado NOT IN (
            'Suspendido', 
            'Terminado', 
            'Ejecutado', 
            'En Contrataciòn', 
            'En Formulación', 
            'Entregado'
        ) THEN 1 
    END) AS en_ejecucion

FROM 
    tbl_proyectos

-- Join con municipio (solo si se requiere alguna condición futura)
INNER JOIN tbl_ciudades_accion_unificada 
    ON tbl_proyectos.tbl_municipio_id = tbl_ciudades_accion_unificada.codigo_muncipio

-- Join con secretarías
INNER JOIN tbl_secretarias 
    ON tbl_proyectos.tbl_secretarias_id = tbl_secretarias.id

-- Join opcional con observaciones (no afecta el resultado si no se usa ningún dato de allí)
LEFT JOIN tbl_proyectos_x_observaciones 
    ON tbl_proyectos.id = tbl_proyectos_x_observaciones.tbl_proyecto_id

-- Filtro por provincia y secretaría específica
WHERE  
    tbl_proyectos.provincia = 'Comunera' 
    AND tbl_secretarias.id = 6

-- Agrupación general
GROUP BY 
    tbl_proyectos.provincia, 
    tbl_secretarias.id, 
    tbl_secretarias.secretaria;




GRAFICAS

SELECT
    tbl_secretarias.id AS secretaria_id,
    tbl_secretarias.secretaria,
    -- Conteo de proyectos por estado específico
    COUNT(CASE WHEN tbl_proyectos.estado = 'Suspendido' THEN 1 END) AS suspendido,
    COUNT(CASE WHEN tbl_proyectos.estado = 'Terminado' THEN 1 END) AS terminado,
    COUNT(CASE WHEN tbl_proyectos.estado = 'Ejecutado' THEN 1 END) AS ejecutado,
    COUNT(CASE WHEN tbl_proyectos.estado = 'En Contrataciòn' THEN 1 END) AS en_contratacion,
    COUNT(CASE WHEN tbl_proyectos.estado = 'En Formulación' THEN 1 END) AS en_formulacion,
    COUNT(CASE WHEN tbl_proyectos.estado = 'Entregado' THEN 1 END) AS entregado,
    COUNT(CASE
        WHEN tbl_proyectos.estado NOT IN (
            'Suspendido', 
            'Terminado', 
            'Ejecutado', 
            'En Contrataciòn', 
            'En Formulación', 
            'Entregado'
        ) THEN 1 
    END) AS en_ejecucion

FROM 
    tbl_proyectos

INNER JOIN tbl_ciudades_accion_unificada 
    ON tbl_proyectos.tbl_municipio_id = tbl_ciudades_accion_unificada.codigo_muncipio

INNER JOIN tbl_secretarias 
    ON tbl_proyectos.tbl_secretarias_id = tbl_secretarias.id

GROUP BY 
 
    tbl_secretarias.id, 
    tbl_secretarias.secretaria;




ultima fecha de actualización de graficas


SELECT 
    tbl_proyectos_x_observaciones.id,
    tbl_proyectos.tbl_secretarias_id,
    tbl_proyectos_x_observaciones.dtcreate
FROM 
    tbl_proyectos
INNER JOIN 
    tbl_proyectos_x_observaciones 
    ON tbl_proyectos.id = tbl_proyectos_x_observaciones.tbl_proyecto_id
WHERE 
    tbl_proyectos_x_observaciones.dtcreate = (
        SELECT 
            MAX(sub.dtcreate)
        FROM 
            tbl_proyectos_x_observaciones sub
        INNER JOIN 
            tbl_proyectos psub 
            ON sub.tbl_proyecto_id = psub.id
        WHERE 
            psub.tbl_secretarias_id = tbl_proyectos.tbl_secretarias_id
    );





HACIENDA