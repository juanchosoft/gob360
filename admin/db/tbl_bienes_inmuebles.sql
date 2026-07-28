-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Host: mariadb:3306
-- Generation Time: Sep 01, 2025 at 09:29 PM
-- Server version: 10.11.14-MariaDB-ubu2204
-- PHP Version: 8.2.27

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `gobernacion_prod_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `tbl_bienes_inmuebles`
--

CREATE TABLE `tbl_bienes_inmuebles` (
  `id` int(11) NOT NULL,
  `codigo_control` varchar(255) DEFAULT NULL,
  `calcomania` varchar(255) DEFAULT NULL,
  `nombre_articulo` text DEFAULT NULL,
  `costo_unitario` decimal(15,2) DEFAULT NULL,
  `tbl_departamento_id` varchar(40) NOT NULL DEFAULT '68' COMMENT 'Código del departamento al que pertenece',
  `tbl_municipio_id` varchar(40) NOT NULL COMMENT 'Municipio al que pertenece',
  `tbl_secretaria_id` int(11) DEFAULT NULL,
  `dependencia` varchar(255) DEFAULT NULL,
  `cedula_o_nit` varchar(20) DEFAULT NULL,
  `responsable` varchar(255) DEFAULT NULL,
  `observacion` text DEFAULT NULL,
  `img1` varchar(255) DEFAULT NULL,
  `img2` varchar(255) DEFAULT NULL,
  `img3` varchar(255) DEFAULT NULL,
  `img4` varchar(255) DEFAULT NULL,
  `pdf` varchar(255) DEFAULT NULL,
  `dtcreate` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tbl_bienes_inmuebles`
--

INSERT INTO `tbl_bienes_inmuebles` (`id`, `codigo_control`, `calcomania`, `nombre_articulo`, `costo_unitario`, `tbl_departamento_id`, `tbl_municipio_id`, `tbl_secretaria_id`, `dependencia`, `cedula_o_nit`, `responsable`, `observacion`, `img1`, `img2`, `img3`, `img4`, `pdf`, `dtcreate`) VALUES
(1, '005324', '3-10-A300-131418', 'CALLE 45 11-12/32/52/68/78 SECRETARIA DE SALUD-BUCARAMANGA NUMERO PREDIAL 01-05-0184-0001000', 16332226626.00, '68', '68001', 3, 'Contratos Comodatos', '00899999034', 'SERVICIO NACIONAL DE APRENDIZAJE', 'Entregado por Liquidación del HURGV de Bucaramanga, Contrato 003580 diciembre 16 de 2021 se entrega un area de 3700mts, Se Entrego una oficina de area 13.10 mts2 a el Tribunal Departamental Etico de Enfermeriade Stder, Norte de stder y Arauca atrves de Comodato 03185 de Noviembre 07 de 2014, se entrego al CENTRO DE BIENESTAR DEL ANCIANO DE BUCARAMANGA un área de 3.217,25 mts2 a través de Comodato 1847 de SEPTIEMBRE 23 de 2024', NULL, NULL, NULL, NULL, NULL, '2025-09-01 17:57:16'),
(2, '005431', '3-10-A300-20866', 'CALLE 37 No 9-31 OFICINAS SINDICATO Y AGRICULTURA-BUCARAMANGA NUMERO PREDIAL 01-01-0188-0011000', 856460000.00, '68', '68001', 3, 'Contratos Comodatos', '00830010946', 'SINDICATO NACIONAL DE SERVIDORES PUBLICOS DEL ESTADO COLOMBIANO', 'Se entrega a SUNET - SECCIONAL BUCARAMANGA a traves de Comodato 001608 de Marzo 23 de 2023', NULL, NULL, NULL, NULL, NULL, '2025-09-01 17:57:16'),
(3, '005367', '3-10-A300-216879', 'CARRERA 11 No 43-49 FUNCIONA LA FUNDACION LAICAL MIANI - FULMIANI -BUCARAMANGA NUMERO PREDIAL 01-01-0174-0018000', 2285140000.00, '68', '68001', 3, 'Contratos Comodatos', '00900410301', 'FUNDACION LAICAL MIANI', 'Se entrego a través de comodato 001848 de septiembre 23 de 2024 un área de 810.5 mts2', NULL, NULL, NULL, NULL, NULL, '2025-09-01 17:57:16'),
(4, '005332', '3-10-A300-428306', 'CARRERA 5 3 par K 5 K 3 LOTE NUMERO 2-LICORERA-FLORIDABLANCA NUMERO PREDIAL 01-01-0318-0018-000', 32855572044.00, '68', '68276', 3, 'Contratos Comodatos', '00899999717', 'DEFENSA CIVIL COLOMBIANA', 'se realizo avaluo corporativo urbano N. 0148-2015 de Agosto de 2015 realizado por la corporacion lonja inmobiliaria de santander, el valor historico era de $886.080.545. Existe un comodato con la Defensa Civil Colombiana por las oficinas del segundo piso según resolución 3339 de 06 de octubre de 2022. En el terreno donado de área de (5.125 m2) se construyeron (05) cinco pisos con un área de 5.264 m2 donde funciona la Unidad Materno Infantil del sur de Floridablanca del Hospital San Juan de Dios de Floridablanca entregado bajo Resolución 20724 de diciembre 18 de 2018 y cuyo avalúo es por $11.437.149.676 de dicha edificación', NULL, NULL, NULL, NULL, NULL, '2025-09-01 17:57:16'),
(5, '005307', '3-10-A300-54832', 'CARRERA 11 N. 7-26/30- FLORIDABLANCA NUMERO PREDIAL 01-01-0039-0009-000 CONSEJO SUPERIOR DE LA JUDICATURA', 218879516.00, '68', '68276', 3, 'Contratos Comodatos', '00800165941', 'RAMA JUDICIAL DIR. DE ADMON JUDICIAL SANTANDER BMANGA', 'INCLUIDO CON ACTA DE SOSTENIBILIDAD 005 MAYO 02,06 Y 10 DE 2011, PROVIENE DE LA LIQUIDACION DE LA LICORERA DE SANTANDER ENTREGADO SEGUN CONTRATO SECOP 4828489 DE MARZO 28 DE 2023', NULL, NULL, NULL, NULL, NULL, '2025-09-01 17:57:16'),
(6, '006101', '3-10-A300-86267', 'CALLE 14 N. 17-02 Y CARRERA 17 N. 14-05, CASA HABITACION-BUCARAMANGA NUMERO PREDIAL 01-03-0041-0001000', 313750000.00, '68', '68001', 3, 'Contratos Comodatos', '00900466159', 'FUNDACION CASA DEL RESERVISTA', 'INCLUIDO SEGUN ACTA DE SOSTENIBILIDAD FINANCIERA DE AGOSTO 11/2010 COMPRADO EN AGOSTO 25 DE 1977, SE ENTREGO A TRAVES DE COMODATO 3397 DE JUNIO 04 DE 2013 UN AREA DE 250 MTS2', NULL, NULL, NULL, NULL, NULL, '2025-09-01 17:57:16'),
(7, '005428', '3-10-A303-1671', 'VIA A BUCARAMANGA KM 8, VIVERO-BARRANCABERMEJA NUMERO PREDIAL 00-02-0003-0036-000', 697500000.00, '68', '68081', 3, 'Contratos Comodatos', '00829003707', 'FUNDACION AMOR Y ALEGRIA DE NIÑOS ESPECIALES', 'Entregado en comodato segun Resolucion N. 0001 de enero 04 de 2022 un área de 6 Hectareas 2.500 mts2', NULL, NULL, NULL, NULL, NULL, '2025-09-01 17:57:16'),
(8, '005496', '3-10-A312-4291', 'LOTE \"LA HOYA\" VEREDA LLARGUTA -MACARAVITA NUMERO PREDIAL 00-00-0007-0098-000', 78750000.00, '68', '68425', 3, 'Contratos Comodatos', '00804002412', 'COLEGIO JUAN XXII', 'CONTRATO 3957 DE NOVIEMBRE 11 DE 2022', NULL, NULL, NULL, NULL, NULL, '2025-09-01 17:57:16'),
(9, '005502', '3-10-A314-66408', 'GRANJA VEREDA GUATIGUARA-LOTES UCC - PIEDECUESTA - NUMERO PREDIAL 68-547-00-00-0008-0137-000', 7442340000.00, '68', '68547', 3, 'Contratos Comodatos', '00860029924', 'UNIVERSIDAD COOPERATIVA DE COLOMBIA', 'Se realizo avaluo corporativo N. 0025-2015 de la corporacion lonja inmobiliaria de santander de Febrero de 2015, el valor historico era de $240.865.000 Entregado en comodato a la Universidad Cooperativa de Colombia UCC un área de 19.708.17 mts2 según Resolución N. 2591 de diciembre 26 de 2019; Según contrato N. 3857 de 2015 re realiza la venta del lote denominado subestación de un área de 12.176 mts2 por un valor de $1.077.153.250 por consiguiente el valor del predio varia a $3.184.484.690.0', NULL, NULL, NULL, NULL, NULL, '2025-09-01 17:57:16'),
(10, '019416', '3-10-A31418953', 'CALLE 6 CARRERAS 5 Y 6 5-42, CALLE 6 5-42 AREA DE TERRENO DE 208 M2', 330525780.00, '68', '68547', 3, 'Contratos Comodatos', '00800141397', 'POLICIA NACIONAL DE COLOMBIA', 'Contrato 003954 de diciembre 27 de 2022', NULL, NULL, NULL, NULL, NULL, '2025-09-01 17:57:16'),
(11, '020797', '3-10-A315-12189', 'VEREDA AGUA FRIA, GRANJA/ PUESTO DE MONTA-JESUS MARIA AREA DE 30 HECTAREAS', 108000000.00, '68', '68368', 3, 'Contratos Comodatos', '00890210946', 'MUNICIPIO DE JESUS MARIA SANTANDER', 'Contrato 002056 de octubre 11 de 2016', NULL, NULL, NULL, NULL, NULL, '2025-09-01 17:57:16'),
(12, '005467', '3-10-A321-11797', 'VEREDA MONTEGRANDE O MONTE ALEGRE, PUESTO DE MONTA-CHIMA NUMERO PREDIAL 00-00-0001-153-000', 78069915.00, '68', '68176', 3, 'Contratos Comodatos', '00804008361', 'CASA DEL ANCIANO CHIMA SANTANDER', 'Entregado a través de Comodato N. 01714 de octubre 04 de 2018', NULL, NULL, NULL, NULL, NULL, '2025-09-01 17:57:16'),
(13, '005365', '3-10-A321-11798', 'VEREDA MONTEGRANDE O MONTE ALEGRE PUESTO DE MONTA-CHIMA NUMERO PREDIAL 00-00-0001-0015-000', 100375605.00, '68', '68176', 3, 'Contratos Comodatos', '00804008361', 'CASA DEL ANCIANO CHIMA SANTANDER', 'Entregado en comodato un área de 21682mts2 segun Resolucion 3448 de junio 20 de 2013', NULL, NULL, NULL, NULL, NULL, '2025-09-01 17:57:16'),
(14, '017690', '3-10-A321-18051', 'CARRERA 9 N. 4-61/65/69 Y CALLE 5 N. 9-05/07/21 BARRIO EL CENTRO INMUEBLE PARA LA CONSTRUCCION DE LA ALCALDIA DE SUAITA', 313687080.00, '68', '68770', 3, 'Contratos Comodatos', '00890204985', 'MUNICIPIO DE SUAITA SANTANDER', 'se entrego en comodato al municipio de Suaita mediante comodato 3364 de noviembre 11 de 2022', NULL, NULL, NULL, NULL, NULL, '2025-09-01 17:57:16'),
(15, '005409', '3-10-A321-9303', 'CALLE 1 3 IMPAR VIA SOCORRO - SIMACOTA COLISEO DE FERIAS-SOCORRO NUMERO PREDIAL 01-00-0157-0001-000', 4069920000.00, '68', '68755', 3, 'Contratos Comodatos', '00890204475', 'COMITé DE GANADEROS DE LA HOYA DEL RIO SUAREZ', 'Contrato 2245 de noviembre 21 de 2020', NULL, NULL, NULL, NULL, NULL, '2025-09-01 17:57:16'),
(16, '020799', '3-10-A324-14404', 'CARRERA 2 N. 3-30 34 38 LA GRANJA EN CIMITARRA NUMERO PREDIAL 00-01-0004-0003-000 CON UN AREA DE 446.790 M2 DE EXTENSION', 12000000.00, '68', '68190', 3, 'Contratos Comodatos', '00800008208', 'COLEGIO INTEGRADO DEL CARARE', 'Contrat0 2555 de diciembre 09 de 2019', NULL, NULL, NULL, NULL, NULL, '2025-09-01 17:57:16'),
(17, '026136', '3-11-26934', 'CALLE 4 N. 3-25 COLEGIO JOSÉ DE FERRO MUNICIPIO DE ENCISO NÚMERO PREDIAL 01-0015-0005-0-00 CON UN AREA DE 1091 M2 ADICIONALMENTE TAMBIEN INCLUYE EL COLISEO UBICADO EN EL PREDIO DE LA CALLE 3 N. 3-10 DE MATRICULA 312-2524 CON NÚMERO PREDIAL 68-266-01-00001500060 CON UN AREA DE 600 M2', 3105844124.54, '68', '68266', 3, 'Contratos Comodatos', '00890201235', 'DEPARTAMENTO DE SANTANDER', 'SE RECIBIO DEL MUNICIPIO DE ENCISO EL COLEGIO JOSE FERRO CON UN AREA DE 1091 M2 ADICIONALMENTE TAMBIEN INCLUYE EL COLISEO UBICADO EN EL PREDIO DE LA CALLE 3 N. 3-10 DE MATRICULA 312-2524 CON NÚMERO PREDIAL 68-266-01-00001500060 CON UN AREA DE 600 M2 CONTRATO 003 DE ENERO 25 DE 2022', NULL, NULL, NULL, NULL, NULL, '2025-09-01 17:57:16'),
(18, '456789', '34567890', 'Nombre del Artículo', 87678688.00, '68', '68079', 8, 'Dependencia', '4234234234', 'ALEXANDER', 'Nombre del Artículo', 'assets/img/admin/d54f5a1ceee4096f9eb92b3055166a65d9e7c1au3x.jpg', 'assets/img/admin/7d6349cae83696594009a7d65c531df6p18amxuk5z.jpg', NULL, NULL, NULL, '2025-09-01 21:22:07');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `tbl_bienes_inmuebles`
--
ALTER TABLE `tbl_bienes_inmuebles`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `tbl_bienes_inmuebles`
--
ALTER TABLE `tbl_bienes_inmuebles`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
