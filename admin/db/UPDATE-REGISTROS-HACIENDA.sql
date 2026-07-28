-- ==========================================
-- RESPALDO (RECOMENDADO)
-- ==========================================

CREATE TABLE IF NOT EXISTS tbl_hacienda_backup_20260609 AS
SELECT *
FROM tbl_hacienda;


-- ==========================================
-- VALIDAR CUÁNTOS REGISTROS SERÁN AFECTADOS
-- ==========================================

SELECT COUNT(*) AS total_registros
FROM tbl_hacienda;


-- ==========================================
-- ACTUALIZAR dtcreate_at
-- Tomando el año de `date`
-- Conservando mes, día y hora de dtcreate_at
-- Corrigiendo 29-Feb en años no bisiestos
-- ==========================================

UPDATE tbl_hacienda
SET dtcreate_at =
CASE

    -- Caso especial: 29 de febrero en año no bisiesto
    WHEN MONTH(dtcreate_at) = 2
         AND DAY(dtcreate_at) = 29
         AND NOT (
              MOD(YEAR(`date`), 400) = 0
              OR (
                   MOD(YEAR(`date`), 4) = 0
                   AND MOD(YEAR(`date`), 100) <> 0
              )
         )
    THEN TIMESTAMP(
            CONCAT(YEAR(`date`), '-02-28'),
            TIME(dtcreate_at)
         )

    -- Resto de fechas
    ELSE TIMESTAMP(
            CONCAT(
                YEAR(`date`), '-',
                DATE_FORMAT(dtcreate_at, '%m-%d')
            ),
            TIME(dtcreate_at)
         )

END;


-- ==========================================
-- VALIDAR RESULTADO
-- ==========================================

SELECT
    id,
    dtcreate_at,
    `date`
FROM tbl_hacienda
ORDER BY id DESC
LIMIT 20;