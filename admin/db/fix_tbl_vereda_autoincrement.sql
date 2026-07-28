-- ============================================================
-- Migración: agregar AUTO_INCREMENT a tbl_vereda.id
-- Aplicar en gobernacion_prod_db
-- ============================================================

USE gobernacion_prod_db;

-- Paso 1: revisar si existen IDs duplicados (debe retornar 0 filas)
-- SELECT id, COUNT(*) AS cnt FROM tbl_vereda GROUP BY id HAVING cnt > 1;

-- Paso 2: eliminar filas duplicadas conservando la de menor rowid
--   (ejecutar solo si el paso 1 retorna filas)
-- DELETE v1 FROM tbl_vereda v1
-- INNER JOIN tbl_vereda v2
--   ON v1.id = v2.id AND v1.codigo_vereda > v2.codigo_vereda;

-- Paso 3: agregar AUTO_INCREMENT y ajustar el valor al máximo actual + 1
ALTER TABLE tbl_vereda MODIFY COLUMN id INT(11) NOT NULL AUTO_INCREMENT;

-- Paso 4: verificar
-- SHOW CREATE TABLE tbl_vereda;
