-- Unificación tbl_gestora + tipo_actividad
-- Preferir: php admin/db/migrate_gestora_tipo_actividad.php

-- 1) Columna tipo
ALTER TABLE tbl_gestora
  ADD COLUMN IF NOT EXISTS tipo_actividad VARCHAR(30) NOT NULL DEFAULT 'primera_dama'
  COMMENT 'primera_dama | aspas'
  AFTER tbl_estrategia_id;

-- 2) Trazabilidad migración ASPAS
ALTER TABLE tbl_gestora
  ADD COLUMN IF NOT EXISTS aspas_origen_id INT(11) NULL DEFAULT NULL
  COMMENT 'ID original en tbl_gestora_aspas'
  AFTER tipo_actividad;

-- 3) Marcar existentes
UPDATE tbl_gestora
SET tipo_actividad = 'primera_dama'
WHERE aspas_origen_id IS NULL
  AND (tipo_actividad IS NULL OR tipo_actividad = '' OR tipo_actividad NOT IN ('primera_dama','aspas'));

-- 4) Migrar ASPAS (omitir ya migrados)
INSERT INTO tbl_gestora (
  tbl_acciong_id, provincia, impactada, desc_actividad, `date`,
  tbl_departamento_id, tbl_municipio_id, poblacion, tbl_usuario_id, img,
  inversion, foto1, foto2, foto3, foto4, dtcreate,
  linea, estrategia, campana, actividad, link,
  tbl_linea_id, tbl_estrategia_id, tipo_actividad, aspas_origen_id
)
SELECT
  a.tbl_acciong_id, a.provincia, a.impactada, a.desc_actividad, a.`date`,
  a.tbl_departamento_id, a.tbl_municipio_id, a.poblacion, a.tbl_usuario_id, a.img,
  a.inversion, a.foto1, a.foto2, a.foto3, a.foto4, a.dtcreate,
  a.linea, a.estrategia, a.campana, a.actividad, a.link,
  a.tbl_linea_id, a.tbl_estrategia_id, 'aspas', a.id
FROM tbl_gestora_aspas a
LEFT JOIN tbl_gestora g ON g.aspas_origen_id = a.id
WHERE g.id IS NULL;

-- Nota: no eliminar tbl_gestora_aspas hasta validar reportes/mapas.
