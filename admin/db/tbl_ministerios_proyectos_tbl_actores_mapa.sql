SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ----------------------------
-- Table structure for tbl_ministerios_proyectos
-- ----------------------------
DROP TABLE IF EXISTS `tbl_ministerios_proyectos`;
CREATE TABLE `tbl_ministerios_proyectos`  (
  `id` int NOT NULL AUTO_INCREMENT,
  `tbl_departamento_id` int NOT NULL,
  `tbl_municipio_id` int NOT NULL,
  `date` varchar(100) CHARACTER SET utf8 COLLATE utf8_spanish_ci NULL DEFAULT NULL,
  `provincia_id` varchar(100) CHARACTER SET utf8 COLLATE utf8_spanish_ci NULL DEFAULT NULL,
  `proyecto` varchar(200) CHARACTER SET utf8 COLLATE utf8_spanish_ci NULL DEFAULT NULL,
  `aporte_municipio` decimal(25, 2) NULL DEFAULT NULL,
  `aporte_departamento` decimal(25, 2) NULL DEFAULT NULL,
  `aporte_nacion` decimal(25, 2) NULL DEFAULT NULL,
  `otro_aportes` decimal(25, 2) NULL DEFAULT NULL,
  `valor_proyecto` decimal(25, 2) NULL DEFAULT NULL,
  `tbl_secretaria_id` int NOT NULL,
  `observaciones` varchar(600) CHARACTER SET utf8 COLLATE utf8_spanish_ci NULL DEFAULT NULL,
  `archivo` varchar(155) CHARACTER SET utf8 COLLATE utf8_spanish_ci NULL DEFAULT NULL,
  `tbl_usuario_id` int NOT NULL,
  `dtcreate` datetime(6) NOT NULL,
  `tbl_ministerios_id` int NULL DEFAULT NULL,
  `actor_id` int NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `idx_municipio`(`tbl_municipio_id` ASC) USING BTREE,
  INDEX `idx_departamento`(`tbl_departamento_id` ASC) USING BTREE,
  INDEX `idx_secretaria`(`tbl_secretaria_id` ASC) USING BTREE,
  INDEX `idx_provincia`(`provincia_id` ASC) USING BTREE,
  INDEX `idx_actor`(`actor_id` ASC) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 2 CHARACTER SET = utf8 COLLATE = utf8_spanish_ci ROW_FORMAT = Dynamic;

SET FOREIGN_KEY_CHECKS = 1;


SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ----------------------------
-- Table structure for tbl_actores_mapa
-- ----------------------------
DROP TABLE IF EXISTS `tbl_actores_mapa`;
CREATE TABLE `tbl_actores_mapa`  (
  `id` int NOT NULL AUTO_INCREMENT,
  `nombre` varchar(120) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
  `pertenece` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
  `logo` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL,
  `dtcreate_at` datetime(6) NULL DEFAULT NULL,
  `tbl_usuario_id` int NOT NULL,
  `municipio_id` int NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `idx_municipio`(`municipio_id` ASC) USING BTREE
) ENGINE = InnoDB CHARACTER SET = utf8 COLLATE = utf8_general_ci ROW_FORMAT = Dynamic;

SET FOREIGN_KEY_CHECKS = 1;