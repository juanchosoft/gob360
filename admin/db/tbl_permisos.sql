
CREATE TABLE `tbl_permisos` (
  `id` int(10) UNSIGNED NOT NULL,
  `dtcreate` datetime NOT NULL,
  `modulo` varchar(45) NOT NULL,
  `nombre` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Volcado de datos para la tabla `tbl_permisos`
--

INSERT INTO `tbl_permisos` (`id`, `dtcreate`, `modulo`, `nombre`) VALUES
(1, '2021-02-14 00:00:00', 'Usuarios', 'Usuarios - Ver'),
(2, '2021-02-14 00:00:00', 'Usuarios', 'Usuarios - Crear'),
(3, '2021-02-14 00:00:00', 'Usuarios', 'Usuarios - Editar'),
(4, '2021-02-14 00:00:00', 'Usuarios', 'Usuarios - Permisos'),
(5, '2021-02-14 00:00:00', 'Secretarias', 'Secretarias - Ver'),
(6, '2021-02-14 00:00:00', 'Secretarias', 'Secretarias - Crear'),
(7, '2021-02-14 00:00:00', 'Secretarias', 'Secretarias - Editar'),
(8, '2021-02-14 00:00:00', 'Mapa', 'Mapa - Ver'),
(9, '2021-02-14 00:00:00', 'Mapa', 'Mapa - Crear'),
(10, '2021-02-14 00:00:00', 'Mapa', 'Mapa - Editar'),
(11, '2021-02-14 00:00:00', 'Municipios', 'Municipios - Ver'),
(12, '2021-02-14 00:00:00', 'Municipios', 'Municipios - Crear'),
(13, '2021-02-14 00:00:00', 'Municipios', 'Municipios - Editar'),
(14, '2021-02-14 00:00:00', 'Visitas', 'Visitas - Ver'),
(15, '2021-02-14 00:00:00', 'Visitas', 'Visitas - Crear'),
(16, '2021-02-14 00:00:00', 'Visitas', 'Visitas - Editar'),
(17, '2021-02-14 00:00:00', 'Proyectos', 'Proyectos - Ver'),
(18, '2021-02-14 00:00:00', 'Proyectos', 'Proyectos - Crear'),
(19, '2021-02-14 00:00:00', 'Proyectos', 'Proyectos - Editar'),
(20, '2021-02-14 00:00:00', 'Seguimiento', 'Seguimiento - Ver'),
(21, '2021-02-14 00:00:00', 'Seguimiento', 'Seguimiento - Crear'),
(22, '2021-02-14 00:00:00', 'Seguimiento', 'Seguimiento - Editar'),
(23, '2021-02-14 00:00:00', 'Imagenes', 'Imagenes - Crear'),
(24, '2021-05-02 19:38:07', 'Imagenes', 'Imagenes - Ver'),
(25, '2021-05-02 19:44:26', 'Imagenes', 'Imagenes - Editar'),
(26, '2021-05-02 19:44:53', 'Prensa', 'Prensa -Ver'),
(27, '2021-05-02 19:45:18', 'Prensa', 'Prensa - Editar'),
(28, '2021-05-02 19:48:27', 'Prensa', 'Prensa - Crear'),
(29, '2024-10-22 08:21:44', 'Gestora', 'Gestora- ver'),
(30, '2024-10-22 08:22:06', 'Gestora', 'Gestora -Crear'),
(31, '2024-10-22 08:22:28', 'Gestora', 'Gestora -Editar'),
(32, '2024-10-22 08:22:53', 'Gestora', 'Gestora -Borrar'),
(36, '0000-00-00 00:00:00', 'Proyectos - Alcaldias', 'Accuni  Proyectos -Ver'),
(37, '0000-00-00 00:00:00', 'Proyectos - Alcaldias', 'Accuni Proyectos -Crear'),
(38, '0000-00-00 00:00:00', 'Proyectos - Alcaldias', 'Accuni  Proyectos -Editar'),
(39, '0000-00-00 00:00:00', 'Departamentos', 'Accuni  Departamentos Accion unificadada - Ver'),
(40, '0000-00-00 00:00:00', 'Municipios', 'Accuni  Municipios Accion unificadada - Ver'),
(41, '0000-00-00 00:00:00', 'Municipios', 'Accuni  Municipios Accion unificadada -crear'),
(42, '0000-00-00 00:00:00', 'Municipios', 'Accuni  Municipios Accion unificadada - Editar'),
(43, '0000-00-00 00:00:00', 'Veredas', 'Accuni  Veredas  Accion unificadada - Ver'),
(44, '0000-00-00 00:00:00', 'Veredas', 'Accuni  Veredas  Accion unificadada - Crear'),
(45, '0000-00-00 00:00:00', 'Veredas', 'Accuni  Veredas Accion unificadada - Editar'),
(46, '0000-00-00 00:00:00', 'Veredas Criticas', 'Accuni  Veredas Criticas - Ver'),
(47, '0000-00-00 00:00:00', 'Veredas Criticas', 'Accun Veredas Criticas - Crear'),
(48, '0000-00-00 00:00:00', 'Veredas Criticas', 'Accun Veredas Criticas - Editar'),
(49, '0000-00-00 00:00:00', 'Avances ', 'Accuni Avances - Ver'),
(50, '0000-00-00 00:00:00', 'Avances', 'Accuni Avancesi - Crear'),
(51, '0000-00-00 00:00:00', 'Avances', ' Accuni Avances - Editar'),
(52, '0000-00-00 00:00:00', 'Imagenes Historial', ' Accuni Imagenes Historial - Ver'),
(53, '0000-00-00 00:00:00', 'Imagenes Historial', ' Accuni Imagenes Historial - Crear'),
(54, '0000-00-00 00:00:00', 'Imagenes Historial', ' Accuni Imagenes Historial - Editar'),
(55, '0000-00-00 00:00:00', 'Ingresso Factores', ' Accuni Ingresso Factores - Ver'),
(56, '0000-00-00 00:00:00', 'Ingresso Factores', ' Accuni Ingresso Factores - Crear'),
(57, '0000-00-00 00:00:00', 'Ingresso Factores', ' Accuni Ingresso Factores - Editar'),
(58, '0000-00-00 00:00:00', 'Ingreso Actores', ' Accuni Ingreso Actores - Ver'),
(59, '0000-00-00 00:00:00', 'Ingreso Actores', ' Accuni Ingreso Actores - Crear'),
(60, '0000-00-00 00:00:00', 'Ingreso Actores', ' Accuni Ingreso Actores - Editar'),
(61, '0000-00-00 00:00:00', 'Ingreso Informacion', ' Accuni Ingreso Informacion - Ver'),
(62, '0000-00-00 00:00:00', 'Ingreso Informacion', ' Accuni Ingreso Informacion - Crear'),
(63, '0000-00-00 00:00:00', 'Ingreso Informacion', ' Accuni Ingreso Informacion - Editar'),
(64, '0000-00-00 00:00:00', 'Actualizacion Informacion', ' Accuni Actualizacion Informacion - Ver'),
(65, '0000-00-00 00:00:00', 'Actualizacion Informacion', ' Accuni Actualizacion Informacion - Crear'),
(66, '0000-00-00 00:00:00', 'Actualizacion Informacion', ' Accuni Actualizacion Informacion - Editar'),
(67, '0000-00-00 00:00:00', 'Configuracion Puntajes', ' Accuni Configuracion Puntajes - Ver'),
(68, '0000-00-00 00:00:00', 'Configuracion Puntajes', ' Accuni Configuracion Puntajes  - Crear'),
(69, '0000-00-00 00:00:00', 'Configuracion Puntajes', ' Accuni Configuracion Puntajes - Editar'),
(70, '0000-00-00 00:00:00', 'Index', 'Index - Ver'),
(71, '0000-00-00 00:00:00', 'Index', 'Index - Crear'),
(72, '0000-00-00 00:00:00', 'Index', 'Index - Editar'),
(73, '0000-00-00 00:00:00', 'Proyectos Secretarias', 'Proyectos Secretarias _Ver'),
(74, '0000-00-00 00:00:00', 'Proyectos Secretarias', 'Proyectos Secretarias -Crear'),
(75, '0000-00-00 00:00:00', 'Proyectos Secretarias', 'Proyectos Secretarias- editar'),
(76, '0000-00-00 00:00:00', 'Area', 'Area - Ver'),
(77, '0000-00-00 00:00:00', 'Area', 'Area - Crear'),
(79, '0000-00-00 00:00:00', 'Area', 'Area - Editar');

(80, '0000-00-00 00:00:00', 'Config Puntajes', 'Config Puntajes - Ver'),
(81, '0000-00-00 00:00:00', 'Config Puntajes', 'Config Puntajes - Crear'),
(82, '0000-00-00 00:00:00', 'Config Puntajes', 'Config Puntajes - Editar');


--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `tbl_permisos`
--
ALTER TABLE `tbl_permisos`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `tbl_permisos`
--
ALTER TABLE `tbl_permisos`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=73;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
