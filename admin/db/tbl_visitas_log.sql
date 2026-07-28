CREATE TABLE `tbl_visitas_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `tbl_visitas_id` int(11) NOT NULL COMMENT 'FK a tbl_visitas',
  `tbl_usuario_id` int(10) UNSIGNED NOT NULL COMMENT 'FK a tbl_usuarios (quien hizo el cambio)',
  `campo` varchar(50) NOT NULL COMMENT 'Nombre del campo modificado: compromiso_pactado, respuesta, acciones_tomadas, pdf, pdf2, pdf3, pdf4',
  `valor_anterior` text DEFAULT NULL COMMENT 'Valor antes del cambio',
  `valor_nuevo` text DEFAULT NULL COMMENT 'Valor después del cambio',
  `created_at` datetime NOT NULL DEFAULT current_timestamp() COMMENT 'Fecha y hora del cambio',
  PRIMARY KEY (`id`),
  KEY `idx_visitas_id` (`tbl_visitas_id`),
  KEY `idx_usuario_id` (`tbl_usuario_id`),
  CONSTRAINT `fk_log_visitas` FOREIGN KEY (`tbl_visitas_id`) REFERENCES `tbl_visitas` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_log_usuarios` FOREIGN KEY (`tbl_usuario_id`) REFERENCES `tbl_usuarios` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_spanish2_ci;

ALTER TABLE `tbl_visitas_log`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
