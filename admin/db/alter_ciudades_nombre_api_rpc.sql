-- =============================================================================
-- Migración: Agregar campo nombre_api_rpc a tbl_ciudades_accion_unificada
--
-- Propósito: Almacenar el nombre exacto del municipio como aparece en la
-- API externa JSON-RPC de Proyectos (Rendición de Cuentas).
-- Esto permite hacer JOIN directo sin depender de normalización de tildes
-- o diferencias en escritura de nombres.
--
-- Fuente de datos: Excel "Muncipios y Provincias.xlsx" proporcionado por
-- el equipo de la API externa.
--
-- @author SPIDERSOFTWARE
-- @date 2026-02-19
-- =============================================================================

-- Paso 1: Agregar la columna
ALTER TABLE gobernacion_prod_db.tbl_ciudades_accion_unificada
ADD COLUMN nombre_api_rpc VARCHAR(100) DEFAULT NULL
AFTER municipio;

-- Paso 2: Corregir 3 municipios con nombres incompletos en la tabla local
UPDATE gobernacion_prod_db.tbl_ciudades_accion_unificada SET municipio = 'SAN JOSE DE MIRANDA' WHERE codigo_muncipio = '68684';
UPDATE gobernacion_prod_db.tbl_ciudades_accion_unificada SET municipio = 'SANTA HELENA DEL OPON' WHERE codigo_muncipio = '68720';
UPDATE gobernacion_prod_db.tbl_ciudades_accion_unificada SET municipio = 'VALLE DE SAN JOSE' WHERE codigo_muncipio = '68855';

-- Paso 3: Poblar nombre_api_rpc con los 87 nombres exactos de la API externa
-- Provincia: Metropolitana (10)
UPDATE gobernacion_prod_db.tbl_ciudades_accion_unificada SET nombre_api_rpc = 'Bucaramanga' WHERE codigo_muncipio = '68001';
UPDATE gobernacion_prod_db.tbl_ciudades_accion_unificada SET nombre_api_rpc = 'El Playón' WHERE codigo_muncipio = '68255';
UPDATE gobernacion_prod_db.tbl_ciudades_accion_unificada SET nombre_api_rpc = 'Floridablanca' WHERE codigo_muncipio = '68276';
UPDATE gobernacion_prod_db.tbl_ciudades_accion_unificada SET nombre_api_rpc = 'Girón' WHERE codigo_muncipio = '68307';
UPDATE gobernacion_prod_db.tbl_ciudades_accion_unificada SET nombre_api_rpc = 'Lebrija' WHERE codigo_muncipio = '68406';
UPDATE gobernacion_prod_db.tbl_ciudades_accion_unificada SET nombre_api_rpc = 'Los Santos' WHERE codigo_muncipio = '68418';
UPDATE gobernacion_prod_db.tbl_ciudades_accion_unificada SET nombre_api_rpc = 'Piedecuesta' WHERE codigo_muncipio = '68547';
UPDATE gobernacion_prod_db.tbl_ciudades_accion_unificada SET nombre_api_rpc = 'Rionegro' WHERE codigo_muncipio = '68615';
UPDATE gobernacion_prod_db.tbl_ciudades_accion_unificada SET nombre_api_rpc = 'Santa Bárbara' WHERE codigo_muncipio = '68705';
UPDATE gobernacion_prod_db.tbl_ciudades_accion_unificada SET nombre_api_rpc = 'Tona' WHERE codigo_muncipio = '68820';

-- Provincia: Yariguíes (7)
UPDATE gobernacion_prod_db.tbl_ciudades_accion_unificada SET nombre_api_rpc = 'Zapatoca' WHERE codigo_muncipio = '68895';
UPDATE gobernacion_prod_db.tbl_ciudades_accion_unificada SET nombre_api_rpc = 'Barrancabermeja' WHERE codigo_muncipio = '68081';
UPDATE gobernacion_prod_db.tbl_ciudades_accion_unificada SET nombre_api_rpc = 'Betulia' WHERE codigo_muncipio = '68092';
UPDATE gobernacion_prod_db.tbl_ciudades_accion_unificada SET nombre_api_rpc = 'El Carmen de Chucurí' WHERE codigo_muncipio = '68235';
UPDATE gobernacion_prod_db.tbl_ciudades_accion_unificada SET nombre_api_rpc = 'Puerto Wilches' WHERE codigo_muncipio = '68575';
UPDATE gobernacion_prod_db.tbl_ciudades_accion_unificada SET nombre_api_rpc = 'Sabana de Torres' WHERE codigo_muncipio = '68655';
UPDATE gobernacion_prod_db.tbl_ciudades_accion_unificada SET nombre_api_rpc = 'San Vicente de Chucurí' WHERE codigo_muncipio = '68689';

-- Provincia: Vélez (19)
UPDATE gobernacion_prod_db.tbl_ciudades_accion_unificada SET nombre_api_rpc = 'Aguada' WHERE codigo_muncipio = '68013';
UPDATE gobernacion_prod_db.tbl_ciudades_accion_unificada SET nombre_api_rpc = 'Albania' WHERE codigo_muncipio = '68020';
UPDATE gobernacion_prod_db.tbl_ciudades_accion_unificada SET nombre_api_rpc = 'Barbosa' WHERE codigo_muncipio = '68077';
UPDATE gobernacion_prod_db.tbl_ciudades_accion_unificada SET nombre_api_rpc = 'Bolívar' WHERE codigo_muncipio = '68101';
UPDATE gobernacion_prod_db.tbl_ciudades_accion_unificada SET nombre_api_rpc = 'Cimitarra' WHERE codigo_muncipio = '68190';
UPDATE gobernacion_prod_db.tbl_ciudades_accion_unificada SET nombre_api_rpc = 'El Peñón' WHERE codigo_muncipio = '68250';
UPDATE gobernacion_prod_db.tbl_ciudades_accion_unificada SET nombre_api_rpc = 'Chipatá' WHERE codigo_muncipio = '68179';
UPDATE gobernacion_prod_db.tbl_ciudades_accion_unificada SET nombre_api_rpc = 'Florián' WHERE codigo_muncipio = '68271';
UPDATE gobernacion_prod_db.tbl_ciudades_accion_unificada SET nombre_api_rpc = 'Guavatá' WHERE codigo_muncipio = '68324';
UPDATE gobernacion_prod_db.tbl_ciudades_accion_unificada SET nombre_api_rpc = 'Güepsa' WHERE codigo_muncipio = '68327';
UPDATE gobernacion_prod_db.tbl_ciudades_accion_unificada SET nombre_api_rpc = 'Jesús María' WHERE codigo_muncipio = '68368';
UPDATE gobernacion_prod_db.tbl_ciudades_accion_unificada SET nombre_api_rpc = 'La Belleza' WHERE codigo_muncipio = '68377';
UPDATE gobernacion_prod_db.tbl_ciudades_accion_unificada SET nombre_api_rpc = 'La Paz' WHERE codigo_muncipio = '68397';
UPDATE gobernacion_prod_db.tbl_ciudades_accion_unificada SET nombre_api_rpc = 'Landázuri' WHERE codigo_muncipio = '68385';
UPDATE gobernacion_prod_db.tbl_ciudades_accion_unificada SET nombre_api_rpc = 'Puente Nacional' WHERE codigo_muncipio = '68572';
UPDATE gobernacion_prod_db.tbl_ciudades_accion_unificada SET nombre_api_rpc = 'Puerto Parra' WHERE codigo_muncipio = '68573';
UPDATE gobernacion_prod_db.tbl_ciudades_accion_unificada SET nombre_api_rpc = 'San Benito' WHERE codigo_muncipio = '68673';
UPDATE gobernacion_prod_db.tbl_ciudades_accion_unificada SET nombre_api_rpc = 'Sucre' WHERE codigo_muncipio = '68773';
UPDATE gobernacion_prod_db.tbl_ciudades_accion_unificada SET nombre_api_rpc = 'Vélez' WHERE codigo_muncipio = '68861';

-- Provincia: Soto Norte (5)
UPDATE gobernacion_prod_db.tbl_ciudades_accion_unificada SET nombre_api_rpc = 'California' WHERE codigo_muncipio = '68132';
UPDATE gobernacion_prod_db.tbl_ciudades_accion_unificada SET nombre_api_rpc = 'Charta' WHERE codigo_muncipio = '68169';
UPDATE gobernacion_prod_db.tbl_ciudades_accion_unificada SET nombre_api_rpc = 'Matanza' WHERE codigo_muncipio = '68444';
UPDATE gobernacion_prod_db.tbl_ciudades_accion_unificada SET nombre_api_rpc = 'Suratá' WHERE codigo_muncipio = '68780';
UPDATE gobernacion_prod_db.tbl_ciudades_accion_unificada SET nombre_api_rpc = 'Vetas' WHERE codigo_muncipio = '68867';

-- Provincia: Guanentá (18)
UPDATE gobernacion_prod_db.tbl_ciudades_accion_unificada SET nombre_api_rpc = 'Aratoca' WHERE codigo_muncipio = '68051';
UPDATE gobernacion_prod_db.tbl_ciudades_accion_unificada SET nombre_api_rpc = 'Barichara' WHERE codigo_muncipio = '68079';
UPDATE gobernacion_prod_db.tbl_ciudades_accion_unificada SET nombre_api_rpc = 'Cabrera' WHERE codigo_muncipio = '68121';
UPDATE gobernacion_prod_db.tbl_ciudades_accion_unificada SET nombre_api_rpc = 'Coromoro' WHERE codigo_muncipio = '68217';
UPDATE gobernacion_prod_db.tbl_ciudades_accion_unificada SET nombre_api_rpc = 'Curití' WHERE codigo_muncipio = '68229';
UPDATE gobernacion_prod_db.tbl_ciudades_accion_unificada SET nombre_api_rpc = 'Charalá' WHERE codigo_muncipio = '68167';
UPDATE gobernacion_prod_db.tbl_ciudades_accion_unificada SET nombre_api_rpc = 'Encino' WHERE codigo_muncipio = '68264';
UPDATE gobernacion_prod_db.tbl_ciudades_accion_unificada SET nombre_api_rpc = 'Jordán' WHERE codigo_muncipio = '68370';
UPDATE gobernacion_prod_db.tbl_ciudades_accion_unificada SET nombre_api_rpc = 'Ocamonte' WHERE codigo_muncipio = '68498';
UPDATE gobernacion_prod_db.tbl_ciudades_accion_unificada SET nombre_api_rpc = 'Mogotes' WHERE codigo_muncipio = '68464';
UPDATE gobernacion_prod_db.tbl_ciudades_accion_unificada SET nombre_api_rpc = 'Onzaga' WHERE codigo_muncipio = '68502';
UPDATE gobernacion_prod_db.tbl_ciudades_accion_unificada SET nombre_api_rpc = 'Páramo' WHERE codigo_muncipio = '68533';
UPDATE gobernacion_prod_db.tbl_ciudades_accion_unificada SET nombre_api_rpc = 'Pinchote' WHERE codigo_muncipio = '68549';
UPDATE gobernacion_prod_db.tbl_ciudades_accion_unificada SET nombre_api_rpc = 'San Joaquín' WHERE codigo_muncipio = '68682';
UPDATE gobernacion_prod_db.tbl_ciudades_accion_unificada SET nombre_api_rpc = 'San Gil' WHERE codigo_muncipio = '68679';
UPDATE gobernacion_prod_db.tbl_ciudades_accion_unificada SET nombre_api_rpc = 'Valle de San José' WHERE codigo_muncipio = '68855';
UPDATE gobernacion_prod_db.tbl_ciudades_accion_unificada SET nombre_api_rpc = 'Villanueva' WHERE codigo_muncipio = '68872';
UPDATE gobernacion_prod_db.tbl_ciudades_accion_unificada SET nombre_api_rpc = 'Cepitá' WHERE codigo_muncipio = '68160';

-- Provincia: García-Rovira (12)
UPDATE gobernacion_prod_db.tbl_ciudades_accion_unificada SET nombre_api_rpc = 'Capitanejo' WHERE codigo_muncipio = '68147';
UPDATE gobernacion_prod_db.tbl_ciudades_accion_unificada SET nombre_api_rpc = 'Carcasí' WHERE codigo_muncipio = '68152';
UPDATE gobernacion_prod_db.tbl_ciudades_accion_unificada SET nombre_api_rpc = 'Cerrito' WHERE codigo_muncipio = '68162';
UPDATE gobernacion_prod_db.tbl_ciudades_accion_unificada SET nombre_api_rpc = 'Concepción' WHERE codigo_muncipio = '68207';
UPDATE gobernacion_prod_db.tbl_ciudades_accion_unificada SET nombre_api_rpc = 'Enciso' WHERE codigo_muncipio = '68266';
UPDATE gobernacion_prod_db.tbl_ciudades_accion_unificada SET nombre_api_rpc = 'Guaca' WHERE codigo_muncipio = '68318';
UPDATE gobernacion_prod_db.tbl_ciudades_accion_unificada SET nombre_api_rpc = 'Macaravita' WHERE codigo_muncipio = '68425';
UPDATE gobernacion_prod_db.tbl_ciudades_accion_unificada SET nombre_api_rpc = 'Málaga' WHERE codigo_muncipio = '68432';
UPDATE gobernacion_prod_db.tbl_ciudades_accion_unificada SET nombre_api_rpc = 'Molagavita' WHERE codigo_muncipio = '68468';
UPDATE gobernacion_prod_db.tbl_ciudades_accion_unificada SET nombre_api_rpc = 'San Andrés' WHERE codigo_muncipio = '68669';
UPDATE gobernacion_prod_db.tbl_ciudades_accion_unificada SET nombre_api_rpc = 'San José de Miranda' WHERE codigo_muncipio = '68684';
UPDATE gobernacion_prod_db.tbl_ciudades_accion_unificada SET nombre_api_rpc = 'San Miguel' WHERE codigo_muncipio = '68686';

-- Provincia: Comunera (16)
UPDATE gobernacion_prod_db.tbl_ciudades_accion_unificada SET nombre_api_rpc = 'Chima' WHERE codigo_muncipio = '68176';
UPDATE gobernacion_prod_db.tbl_ciudades_accion_unificada SET nombre_api_rpc = 'Confines' WHERE codigo_muncipio = '68209';
UPDATE gobernacion_prod_db.tbl_ciudades_accion_unificada SET nombre_api_rpc = 'Contratación' WHERE codigo_muncipio = '68211';
UPDATE gobernacion_prod_db.tbl_ciudades_accion_unificada SET nombre_api_rpc = 'El Guacamayo' WHERE codigo_muncipio = '68245';
UPDATE gobernacion_prod_db.tbl_ciudades_accion_unificada SET nombre_api_rpc = 'Galán' WHERE codigo_muncipio = '68296';
UPDATE gobernacion_prod_db.tbl_ciudades_accion_unificada SET nombre_api_rpc = 'Gámbita' WHERE codigo_muncipio = '68298';
UPDATE gobernacion_prod_db.tbl_ciudades_accion_unificada SET nombre_api_rpc = 'Guadalupe' WHERE codigo_muncipio = '68320';
UPDATE gobernacion_prod_db.tbl_ciudades_accion_unificada SET nombre_api_rpc = 'Guapotá' WHERE codigo_muncipio = '68322';
UPDATE gobernacion_prod_db.tbl_ciudades_accion_unificada SET nombre_api_rpc = 'Hato' WHERE codigo_muncipio = '68344';
UPDATE gobernacion_prod_db.tbl_ciudades_accion_unificada SET nombre_api_rpc = 'Oiba' WHERE codigo_muncipio = '68500';
UPDATE gobernacion_prod_db.tbl_ciudades_accion_unificada SET nombre_api_rpc = 'Palmar' WHERE codigo_muncipio = '68522';
UPDATE gobernacion_prod_db.tbl_ciudades_accion_unificada SET nombre_api_rpc = 'Palmas del Socorro' WHERE codigo_muncipio = '68524';
UPDATE gobernacion_prod_db.tbl_ciudades_accion_unificada SET nombre_api_rpc = 'Santa Helena del Opón' WHERE codigo_muncipio = '68720';
UPDATE gobernacion_prod_db.tbl_ciudades_accion_unificada SET nombre_api_rpc = 'Simacota' WHERE codigo_muncipio = '68745';
UPDATE gobernacion_prod_db.tbl_ciudades_accion_unificada SET nombre_api_rpc = 'Socorro' WHERE codigo_muncipio = '68755';
UPDATE gobernacion_prod_db.tbl_ciudades_accion_unificada SET nombre_api_rpc = 'Suaita' WHERE codigo_muncipio = '68770';

-- Verificación
SELECT codigo_muncipio, municipio, nombre_api_rpc
FROM gobernacion_prod_db.tbl_ciudades_accion_unificada
WHERE codigo_departamento = '68'
ORDER BY municipio;
