
CREATE TABLE `tbl_historial_session` (
  `id` int(11) NOT NULL,
  `dtcreate` datetime NOT NULL,
  `ip` varchar(150) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL,
  `navegador` varchar(150) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL,
  `tec_usuario_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;


ALTER TABLE `tbl_historial_session`
  ADD PRIMARY KEY (`id`);


ALTER TABLE `tbl_historial_session`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
COMMIT;
