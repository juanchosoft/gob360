-- ============================================================
-- Auditoría de usuarios eliminados (seguridad)
-- Ejecutar en la base de datos del proyecto
-- ============================================================
CREATE TABLE IF NOT EXISTS `tbl_usuarios_eliminados` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `usuario_id` INT NOT NULL,
    `nombre` VARCHAR(255),
    `apellido` VARCHAR(255),
    `nickname` VARCHAR(255),
    `tipo` VARCHAR(100),
    `email` VARCHAR(255),
    `deleted_at` DATETIME,
    `deleted_by` INT,
    INDEX `idx_deleted_at` (`deleted_at`)
);
