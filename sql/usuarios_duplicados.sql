-- ============================================================
-- Listado de usuarios con nickname duplicado
-- Muestra todos los usuarios que comparten un mismo username,
-- ordenados para revisar cuál conservar y cuál eliminar.
-- ============================================================
SELECT u1.id,
       u1.nickname,
       u1.nombre,
       u1.apellido,
       u1.tipo,
       u1.habilitado,
       u1.dtcreate,
       u1.tbl_municipio_id,
       u1.tbl_secretarias_id
FROM tbl_usuarios u1
INNER JOIN (
    SELECT nickname
    FROM tbl_usuarios
    GROUP BY nickname
    HAVING COUNT(*) > 1
) dup ON u1.nickname = dup.nickname
ORDER BY u1.nickname ASC, u1.id ASC;
