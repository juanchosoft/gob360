-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 10-07-2025 a las 21:51:12
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
-- Base de datos: `gobersantander`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tbl_hacienda`
--

CREATE TABLE `tbl_hacienda` (
  `id` int(11) NOT NULL,
  `dtcreate_at` datetime NOT NULL,
  `accion` varchar(100) NOT NULL,
  `cantidad` int(11) DEFAULT 0,
  `incautacion_licores` int(11) DEFAULT 0,
  `valor_licores` decimal(25,2) DEFAULT NULL,
  `incautacion_cigarrillos` int(11) DEFAULT 0,
  `valor_cigarrillos` decimal(25,2) DEFAULT NULL,
  `incautacion_cerveza` int(11) NOT NULL,
  `valor_cerveza` decimal(25,2) NOT NULL DEFAULT 0.00,
  `valor_importado` decimal(25,2) NOT NULL DEFAULT 0.00 COMMENT 'Viene de Recaudo del impuesto al consumo',
  `valor_nacional` decimal(25,2) NOT NULL DEFAULT 0.00 COMMENT 'Viene de Recaudo del impuesto al consumo',
  `valor_tramite` decimal(25,2) NOT NULL DEFAULT 0.00 COMMENT 'Recaudo del impuesto de registro',
  `valor_recaudo` decimal(25,2) NOT NULL DEFAULT 0.00 COMMENT 'Recaudo del impuesto de registro',
  `valor_tramite_impuesto_vehicular` decimal(25,2) NOT NULL COMMENT 'Impuesto Vehicular Recaudado',
  `valor_recaudo_impuesto_vehicular` decimal(25,2) NOT NULL COMMENT 'Impuesto Vehicular Recaudado',
  `estampilla` varchar(40) NOT NULL COMMENT 'Impuesto Estampillas Recaudado',
  `valor_estampilla` decimal(25,2) NOT NULL COMMENT 'Impuesto Estampillas Recaudado',
  `capacitacion_programada` int(11) DEFAULT 0,
  `capacitacion_ejecutada` int(11) DEFAULT NULL,
  `cantidad_personas` int(11) DEFAULT NULL,
  `observaciones` text DEFAULT NULL,
  `tbl_municipio_id` int(11) NOT NULL,
  `date` date NOT NULL,
  `secretaria` varchar(20) DEFAULT NULL,
  `objeto` text DEFAULT NULL,
  `provincia` varchar(100) DEFAULT NULL,
  `tbl_departamento_id` int(11) DEFAULT NULL,
  `tbl_usuario_id` int(11) DEFAULT NULL,
  `valor_total` decimal(25,2) NOT NULL,
  `foto` varchar(155) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Volcado de datos para la tabla `tbl_hacienda`
--

INSERT INTO `tbl_hacienda` (`id`, `dtcreate_at`, `accion`, `cantidad`, `incautacion_licores`, `valor_licores`, `incautacion_cigarrillos`, `valor_cigarrillos`, `incautacion_cerveza`, `valor_cerveza`, `valor_importado`, `valor_nacional`, `valor_tramite`, `valor_recaudo`, `valor_tramite_impuesto_vehicular`, `valor_recaudo_impuesto_vehicular`, `estampilla`, `valor_estampilla`, `capacitacion_programada`, `capacitacion_ejecutada`, `cantidad_personas`, `observaciones`, `tbl_municipio_id`, `date`, `secretaria`, `objeto`, `provincia`, `tbl_departamento_id`, `tbl_usuario_id`, `valor_total`, `foto`) VALUES
(40, '2025-07-09 22:02:31', 'Capacitacion Fiscal y Financiera', 0, 0, 0.00, 0, 0.00, 0, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, '', 0.00, 0, NULL, 40, '40 personas capacitadas en AGUADA', 68013, '2025-07-09', 'Hacienda', 'Capacitaciones', '', 68, 2, 0.00, ''),
(41, '2025-07-09 22:13:17', 'Operativos Contrabando licores', 0, 456, 80000000.00, 0, 0.00, 0, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, '', 0.00, 0, NULL, 0, 'Operativo contrabando licores.', 68001, '2025-07-09', 'Hacienda', '', '', 68, 2, 0.00, ''),
(42, '2025-07-09 22:37:08', 'Impuesto Vehicular Recaudado', 0, 0, 0.00, 0, 0.00, 0, 0.00, 0.00, 0.00, 0.00, 0.00, 40.00, 500000.00, '', 0.00, 0, NULL, 0, 'fdsfdsfsdfdsf', 0, '2025-07-09', 'Hacienda', '', '', 68, 2, 0.00, ''),
(43, '2025-07-09 22:59:01', 'Impuesto Vehicular Recaudado', 0, 0, 0.00, 0, 0.00, 0, 0.00, 0.00, 0.00, 0.00, 0.00, 10.00, 60.00, '', 0.00, 0, NULL, 0, 'Recaudo nnn', 0, '2025-07-09', 'Hacienda', '', '', 68, 2, 0.00, ''),
(44, '2025-07-09 23:09:43', 'Recaudo del impuesto de registro', 0, 0, 0.00, 0, 0.00, 0, 0.00, 0.00, 0.00, 40.00, 9000000000.00, 0.00, 0.00, '', 0.00, 0, NULL, 0, 'Recaudo del impuesto de registro', 68001, '2025-07-09', 'Hacienda', '', '', 68, 2, 0.00, ''),
(45, '2025-07-09 23:16:18', 'Impuesto Estampillas Recaudado', 0, 0, 0.00, 0, 0.00, 0, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 'OBRA PUBLICA', 1500000.00, 0, NULL, 0, 'observaciones de Valor Estampilla Recaudado', 0, '2025-07-09', 'Hacienda', '', '', 68, 2, 0.00, ''),
(46, '2025-07-09 23:38:00', 'Impuesto Estampillas Recaudado', 0, 0, 0.00, 0, 0.00, 0, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 'PRO HOSPITAL', 480000.00, 0, NULL, 0, '34567u8i9o0', 0, '2025-07-09', 'Hacienda', '', '', 68, 2, 0.00, ''),
(47, '2025-07-09 23:38:54', 'Impuesto Estampillas Recaudado', 0, 0, 0.00, 0, 0.00, 0, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 'PRO HOSPITAL', 500000.00, 0, NULL, 0, '', 0, '2025-07-09', 'Hacienda', '', '', 68, 2, 0.00, ''),
(48, '2025-07-10 00:04:28', 'Recaudo del impuesto al consumo', 0, 0, 0.00, 0, 0.00, 0, 0.00, 4000.00, 50000.00, 0.00, 0.00, 0.00, 0.00, '', 0.00, 0, NULL, 0, 'fdsfdsfdsfsdf', 68001, '2025-07-10', 'Hacienda', '', '', 68, 2, 0.00, ''),
(49, '2025-07-10 00:14:36', 'Operativos Contrabando cigarrillos', 0, 0, 0.00, 450, 8000000.00, 0, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, '', 0.00, 0, NULL, 0, 'cigarrillos', 68001, '2025-07-10', 'Hacienda', '', '', 68, 2, 0.00, ''),
(50, '2025-07-10 00:15:22', 'Operativos Contrabando cerveza', 0, 0, 0.00, 0, 0.00, 800, 9300000.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, '', 0.00, 0, NULL, 0, '', 68001, '2025-07-10', 'Hacienda', '', '', 68, 2, 0.00, ''),
(51, '2025-07-10 08:34:14', 'Recaudo del impuesto al consumo', 0, 0, 0.00, 0, 0.00, 0, 0.00, 800000000.00, 9000000.00, 0.00, 0.00, 0.00, 0.00, '', 0.00, 0, NULL, 0, 'Recaudo del impuesto al consumo', 68001, '2025-07-10', 'Hacienda', '', '', 68, 2, 0.00, 'assets/img/admin/b887f3f4b92e8ceaed743368af8d8389iyt0a8dp23.png'),
(52, '2025-07-10 13:02:13', 'Impuesto Vehicular Recaudado', 0, 0, 0.00, 0, 0.00, 0, 0.00, 0.00, 0.00, 0.00, 0.00, 7000.00, 80000006540.00, '', 0.00, 0, NULL, 0, 'gfdgfdgfdgdfgdfg', 0, '2025-07-10', 'Hacienda', '', '', 68, 2, 0.00, ''),
(53, '2025-07-10 13:13:12', 'Impuesto Vehicular Recaudado', 0, 0, 0.00, 0, 0.00, 0, 0.00, 0.00, 0.00, 0.00, 0.00, 4645.00, 6546456.00, '', 0.00, 0, NULL, 0, '65ggd', 0, '2025-07-10', 'Hacienda', '', '', 68, 2, 0.00, ''),
(54, '2025-07-10 13:13:13', 'Impuesto Vehicular Recaudado', 0, 0, 0.00, 0, 0.00, 0, 0.00, 0.00, 0.00, 0.00, 0.00, 4645.00, 6546456.00, '', 0.00, 0, NULL, 0, '65ggd', 0, '2025-07-10', 'Hacienda', '', '', 68, 2, 0.00, ''),
(55, '2025-07-10 13:13:13', 'Impuesto Vehicular Recaudado', 0, 0, 0.00, 0, 0.00, 0, 0.00, 0.00, 0.00, 0.00, 0.00, 4645.00, 6546456.00, '', 0.00, 0, NULL, 0, '65ggd', 0, '2025-07-10', 'Hacienda', '', '', 68, 2, 0.00, ''),
(56, '2025-07-10 13:13:13', 'Impuesto Vehicular Recaudado', 0, 0, 0.00, 0, 0.00, 0, 0.00, 0.00, 0.00, 0.00, 0.00, 4645.00, 6546456.00, '', 0.00, 0, NULL, 0, '65ggd', 0, '2025-07-10', 'Hacienda', '', '', 68, 2, 0.00, ''),
(57, '2025-07-10 14:22:38', 'Impuesto Vehicular Recaudado', 0, 0, 0.00, 0, 0.00, 0, 0.00, 0.00, 0.00, 0.00, 0.00, 50000.00, 78888888000.00, '', 0.00, 0, NULL, 0, '65ggd', 0, '2024-07-10', 'Hacienda', '', '', 68, 2, 0.00, ''),
(58, '2025-07-10 14:36:56', 'Recaudo del impuesto al consumo', 0, 0, 0.00, 0, 0.00, 0, 0.00, 840000.00, 86000.00, 0.00, 0.00, 0.00, 0.00, '', 0.00, 0, NULL, 0, 'hffghfghfh', 68001, '2024-07-10', 'Hacienda', '', '', 68, 2, 0.00, '');

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `tbl_hacienda`
--
ALTER TABLE `tbl_hacienda`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `tbl_hacienda`
--
ALTER TABLE `tbl_hacienda`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=59;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;



ALTER TABLE `tbl_hacienda` ADD `incautacion_tabaco` INT NOT NULL DEFAULT '0.00' AFTER `valor_cigarrillos`, ADD `valor_tabaco` DECIMAL(25,2) NOT NULL DEFAULT '0.00' AFTER `incautacion_tabaco`; 