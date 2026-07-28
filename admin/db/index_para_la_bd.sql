-- Índice simple para consultas por municipio
CREATE INDEX idx_codigo_municipio ON tbl_ciudades_accion_unificada (codigo_muncipio);

-- Índice simple para código de departamento
CREATE INDEX idx_codigo_departamento ON tbl_ciudades_accion_unificada (codigo_departamento);

-- Índice combinado si haces filtros por ambos campos
CREATE INDEX idx_codigo_dpto_municipio ON tbl_ciudades_accion_unificada (codigo_departamento, codigo_muncipio);

-- Si filtras por nombre del municipio (campo texto)
CREATE INDEX idx_municipio ON tbl_ciudades_accion_unificada (municipio);








-- Para consultas por departamento
CREATE INDEX idx_departamento_id ON tbl_vereda (departamento_id);

-- Para consultas por municipio
CREATE INDEX idx_municipio_id ON tbl_vereda (municipio_id);

-- Para búsquedas por código de vereda
CREATE INDEX idx_codigo_vereda ON tbl_vereda (codigo_vereda);

-- Índice combinado si consultas por municipio + vereda
CREATE INDEX idx_municipio_vereda ON tbl_vereda (municipio_id, codigo_vereda);


-- Por nombre de la vereda
CREATE INDEX idx_nombre_vereda ON tbl_vereda (nombre_vereda(50)); -- Index parcial (MySQL < 5.7)



-- Por campo `habilitada_para_votar`
CREATE INDEX idx_habilitada ON tbl_vereda (habilitada_para_votar);

-- Por carpeta y nombre del SVG (acceso rápido a archivos)
CREATE INDEX idx_svg ON tbl_vereda (carpeta_svg, nombre_svg);



ALTER TABLE tbl_vereda
MODIFY nombre_vereda VARCHAR(100)
CHARACTER SET utf8
COLLATE utf8_unicode_ci;



CREATE INDEX idx_dtcreate ON tbl_ingreso_informacion (dtcreate);
CREATE INDEX idx_fecha_modificacion ON tbl_ingreso_informacion (fecha_modificacion);
CREATE INDEX idx_tec_usuario_id ON tbl_ingreso_informacion (tec_usuario_id);
CREATE INDEX idx_tbl_vereda_id ON tbl_ingreso_informacion (tbl_vereda_id);
CREATE INDEX idx_tbl_factor_id ON tbl_ingreso_informacion (tbl_factor_id);
CREATE INDEX idx_codigo_departamento ON tbl_ingreso_informacion (codigo_departamento);
CREATE INDEX idx_codigo_municipio ON tbl_ingreso_informacion (codigo_municipio);
CREATE INDEX idx_dep_mun_ingreso ON tbl_ingreso_informacion (codigo_departamento, codigo_municipio);
