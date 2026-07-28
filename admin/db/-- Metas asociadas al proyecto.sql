-- Metas asociadas al proyecto (Sin FK física)
CREATE TABLE tbl_proyectos_planeacion_metas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tbl_proyecto_id INT NOT NULL,
    tbl_meta_id INT NOT NULL,
    UNIQUE KEY uq_proyecto_meta (tbl_proyecto_id, tbl_meta_id)
) ENGINE=InnoDB;

-- Secretarías asociadas al proyecto (Sin FK física)
CREATE TABLE tbl_proyectos_planeacion_secretarias (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tbl_proyecto_id INT NOT NULL,
    tbl_secretarias_id INT NOT NULL,
    UNIQUE KEY uq_proyecto_secretaria (tbl_proyecto_id, tbl_secretarias_id)
) ENGINE=InnoDB;