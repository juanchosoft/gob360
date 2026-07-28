-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 10-06-2025 a las 00:33:21
-- Versión del servidor: 10.4.32-MariaDB
-- Versión de PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `gobersantander_prod`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tbl_ministerios_proyectos`
--

CREATE TABLE `tbl_ministerios_proyectos` (
  `id` int(11) NOT NULL,
  `tbl_departamento_id` int(11) NOT NULL,
  `tbl_municipio_id` int(11) NOT NULL,
  `date` varchar(100) DEFAULT NULL,
  `provincia_id` varchar(100) DEFAULT NULL,
  `proyecto` varchar(200) DEFAULT NULL,
  `aporte_municipio` decimal(25,2) DEFAULT NULL,
  `aporte_departamento` decimal(25,2) DEFAULT NULL,
  `aporte_nacion` decimal(25,2) DEFAULT NULL,
  `otro_aportes` decimal(25,2) DEFAULT NULL,
  `valor_proyecto` decimal(25,2) DEFAULT NULL,
  `observaciones` varchar(600) DEFAULT NULL,
  `archivo` varchar(10) DEFAULT NULL,
  `tbl_usuario_id` int(11) NOT NULL,
  `dtcreate` datetime(6) NOT NULL,
  `tbl_ministerios_id` int(11) DEFAULT NULL,
  `tbl_secretaria_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci ROW_FORMAT=DYNAMIC;

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `tbl_ministerios_proyectos`
--
ALTER TABLE `tbl_ministerios_proyectos`
  ADD PRIMARY KEY (`id`) USING BTREE,
  ADD KEY `idx_municipio` (`tbl_municipio_id`) USING BTREE,
  ADD KEY `idx_departamento` (`tbl_departamento_id`) USING BTREE,
  ADD KEY `idx_secretaria` (`tbl_secretaria_id`) USING BTREE,
  ADD KEY `idx_provincia` (`provincia_id`) USING BTREE;

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `tbl_ministerios_proyectos`
--
ALTER TABLE `tbl_ministerios_proyectos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=190;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
