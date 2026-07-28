-- Ejemplos de componentes municipales
-- Este archivo contiene datos de ejemplo para la tabla tbl_componente_municipios
-- Puedes ejecutar este script después de crear la tabla para tener datos de prueba

-- Componentes para el municipio de CERRITO (código: 68162)
INSERT INTO `tbl_componente_municipios` (`nombre_componente`, `codigo_departamento`, `codigo_municipio`, `habilitado`) VALUES
('JURÍDICO', '68', '68162', 'si'),
('INFRAESTRUCTURA HOSPITALARIA', '68', '68162', 'si'),
('INFRAESTRUCTURA EDUCATIVA', '68', '68162', 'si'),
('VÍAS SECUNDARIAS Y TERCIARIAS', '68', '68162', 'si'),
('AGUA POTABLE - ALCANTARILLADO - PTAR', '68', '68162', 'si');

-- Componentes para el municipio de ARATOCA (código: 68051)
INSERT INTO `tbl_componente_municipios` (`nombre_componente`, `codigo_departamento`, `codigo_municipio`, `habilitado`) VALUES
('JURÍDICO', '68', '68051', 'si'),
('MEJORAMIENTO SERVICIO DE SALUD', '68', '68051', 'si'),
('TRANSPORTE ESCOLAR', '68', '68051', 'si'),
('INFRAESTRUCTURA INSTITUCIONES', '68', '68051', 'si'),
('FORTALECIMIENTO INSTITUCIONAL', '68', '68051', 'si');

-- Nota: Ajusta los códigos de departamento y municipio según tu base de datos
