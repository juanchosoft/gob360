-- 007: Asignaciones + permisos alcance/informes planeación alcaldía

CREATE TABLE IF NOT EXISTS tbl_proyectos_planeacion_asignaciones (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  proyecto_id INT UNSIGNED NOT NULL,
  usuario_id INT UNSIGNED NOT NULL,
  asignado_por_id INT UNSIGNED NOT NULL,
  activo TINYINT(1) NOT NULL DEFAULT 1,
  observacion VARCHAR(500) NULL,
  dtcreated TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uq_proyecto_usuario (proyecto_id, usuario_id),
  INDEX idx_usuario_activo (usuario_id, activo),
  INDEX idx_proyecto_activo (proyecto_id, activo)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
