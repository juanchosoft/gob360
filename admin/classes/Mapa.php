<?php

/**
 * Clase que contiene todas las operaciones utilizadas sobre la base de datos
 * @author SPIDERSOFTWARE
 */
class Mapa
{

    public function __construct() {}



    public static function getAll()
    {
        $db = new DbConection();
        $pdo = $db->openConect();

        $q = "SELECT sum(CAST(puntaje AS UNSIGNED)) total," . $db->getTable('tbl_departamentos') . ".departamento," . $db->getTable('tbl_ciudades') . ".municipio," . $db->getTable('tbl_vereda') . " .nombre_vereda FROM " . $db->getTable('tbl_resultados_armado') . " INNER JOIN " . $db->getTable('tbl_departamentos') . " on " . $db->getTable('tbl_resultados_armado') . ".departamento_id = " . $db->getTable('tbl_departamentos') . ".id INNER JOIN " . $db->getTable('tbl_ciudades') . " on " . $db->getTable('tbl_ciudades') . ".id = " . $db->getTable('tbl_resultados_armado') . ".municipio_id INNER JOIN " . $db->getTable('tbl_vereda') . " on " . $db->getTable('tbl_vereda') . ".id = " . $db->getTable('tbl_resultados_armado') . ".vereda_id GROUP BY vereda_id";

        $qSocial = "SELECT sum(CAST(puntaje AS UNSIGNED)) total," . $db->getTable('tbl_departamentos') . ".departamento," . $db->getTable('tbl_ciudades') . ".municipio," . $db->getTable('tbl_vereda') . " .nombre_vereda FROM " . $db->getTable('tbl_resultados_social') . " INNER JOIN " . $db->getTable('tbl_departamentos') . " on " . $db->getTable('tbl_resultados_social') . ".departamento_id = " . $db->getTable('tbl_departamentos') . ".id INNER JOIN " . $db->getTable('tbl_ciudades') . " on " . $db->getTable('tbl_ciudades') . ".id = " . $db->getTable('tbl_resultados_social') . ".municipio_id INNER JOIN " . $db->getTable('tbl_vereda') . " on " . $db->getTable('tbl_vereda') . ".id = " . $db->getTable('tbl_resultados_social') . ".vereda_id GROUP BY vereda_id";

        $qEcono = "SELECT sum(CAST(puntaje AS UNSIGNED)) total," . $db->getTable('tbl_departamentos') . ".departamento," . $db->getTable('tbl_ciudades') . ".municipio," . $db->getTable('tbl_vereda') . " .nombre_vereda FROM " . $db->getTable('tbl_resultados_economico') . " INNER JOIN " . $db->getTable('tbl_departamentos') . " on " . $db->getTable('tbl_resultados_economico') . ".departamento_id = " . $db->getTable('tbl_departamentos') . ".id INNER JOIN " . $db->getTable('tbl_ciudades') . " on " . $db->getTable('tbl_ciudades') . ".id = " . $db->getTable('tbl_resultados_economico') . ".municipio_id INNER JOIN " . $db->getTable('tbl_vereda') . " on " . $db->getTable('tbl_vereda') . ".id = " . $db->getTable('tbl_resultados_economico') . ".vereda_id GROUP BY vereda_id";



        $result = $pdo->query($q);
        $resultSocial = $pdo->query($qSocial);
        $resultEconomia = $pdo->query($qEcono);

        $arr = array("armada" => [], "social" => [], "economia" => []);
        if ($result) {
            foreach ($result as $valor) {
                unset($valor["0"], $valor["1"], $valor["2"], $valor["3"]);
                $valor["nombre_vereda"] = utf8_encode($valor["nombre_vereda"]);
                $arr["armada"][] = $valor;
            }
        }

        if ($resultSocial) {
            foreach ($resultSocial as $valor) {
                unset($valor["0"], $valor["1"], $valor["2"], $valor["3"]);
                $valor["nombre_vereda"] = utf8_encode($valor["nombre_vereda"]);
                $arr["social"][] = $valor;
            }
        }

        if ($resultEconomia) {
            foreach ($resultEconomia as $valor) {
                unset($valor["0"], $valor["1"], $valor["2"], $valor["3"]);
                $valor["nombre_vereda"] = utf8_encode($valor["nombre_vereda"]);
                $arr["economia"][] = $valor;
            }
        }
        $arrjson = array('output' => array('valid' => true, 'response' => $arr));
        $db->closeConect();
        return $arrjson;
    }

    public static function getInformacionSecretariaEnMapaGoogle($rqst)
    {
        $secretariaId = isset($rqst['secretariaId']) ? intval($rqst['secretariaId']) : 0;
        $departamento = Util::getDepartamentoPrincipal();

        $db = new DbConection();
        $pdo = $db->openConect();

        $sql = "
            SELECT 
                tbl_ingreso_informacion.id, 
                tbl_ingreso_informacion.codigo_municipio, 
                tbl_ingreso_informacion.latitud, 
                tbl_ingreso_informacion.longitud, 
                tbl_ingreso_informacion.valor, 
                tbl_factores.icono, 
                tbl_factores.tipo, 
                tbl_secretarias.id AS secretaria_id, 
                tbl_secretarias.secretaria,  
                tbl_ciudades_accion_unificada.municipio, 
                tbl_vereda.nombre_vereda, 
                tbl_ciudades_accion_unificada.name, 
                tbl_ciudades_accion_unificada.class, 
                tbl_ingreso_informacion.codigo_departamento,
                tbl_ingreso_informacion.observaciones
            FROM 
                " . $db->getTable('tbl_ingreso_informacion') . "
            INNER JOIN 
                " . $db->getTable('tbl_factores') . " 
                ON tbl_ingreso_informacion.tbl_factor_id = tbl_factores.id 
            INNER JOIN 
                " . $db->getTable('tbl_secretarias') . " 
                ON tbl_factores.tbl_secretaria_id = tbl_secretarias.id 
            INNER JOIN 
                " . $db->getTable('tbl_ciudades_accion_unificada') . " 
                ON tbl_ingreso_informacion.codigo_municipio = tbl_ciudades_accion_unificada.codigo_muncipio
            INNER JOIN 
                " . $db->getTable('tbl_vereda') . " 
                ON tbl_ingreso_informacion.tbl_vereda_id = tbl_vereda.id
            WHERE 
                tbl_ingreso_informacion.codigo_departamento = $departamento  AND tbl_secretarias.id = :secretariaId
            ORDER BY 
                tbl_ciudades_accion_unificada.municipio ASC
            ";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':secretariaId', $secretariaId, PDO::PARAM_INT);
        $stmt->execute();
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $arr = array();
        if ($result) {
            foreach ($result as $valor) {
                $arr[] = $valor;
            }
            $arrjson = array('output' => array('valid' => true, 'response' => $arr));
        } else {
            $arrjson = Util::error_no_result();
        }
        $db->closeConect();
        return $arrjson;
    }

    /**
     * Obtiene información de proyectos de alcaldías para mostrar en mapa de Google
     * Consulta tbl_proyectos_alcaldias y tbl_secretarias_municipios
     * Usa las coordenadas del municipio (tbl_ciudades_accion_unificada) como fallback
     *
     * @param array $rqst Array con parámetros:
     *                    - municipioId: Código del municipio
     *                    - secretariaId: ID de la secretaría municipal (opcional)
     * @return array Array con información de proyectos para marcadores
     */
    public static function getInformacionAlcaldiaEnMapaGoogle($rqst)
    {
        $municipioId = isset($rqst['municipioId']) ? intval($rqst['municipioId']) : 0;
        $secretariaId = isset($rqst['secretariaId']) ? intval($rqst['secretariaId']) : 0;

        $db = new DbConection();
        $pdo = $db->openConect();

        // Construir condiciones WHERE
        $whereConditions = ["tbl_proyectos_alcaldias.tbl_municipio_id = :municipioId"];
        $params = [':municipioId' => $municipioId];

        if ($secretariaId > 0) {
            $whereConditions[] = "tbl_secretarias_municipios.id = :secretariaId";
            $params[':secretariaId'] = $secretariaId;
        }

        $whereClause = implode(' AND ', $whereConditions);

        // Usa COALESCE para tomar coordenadas de vereda o del municipio como fallback
        $sql = "
            SELECT
                tbl_proyectos_alcaldias.id,
                tbl_proyectos_alcaldias.proyecto,
                tbl_proyectos_alcaldias.valor_proyecto,
                tbl_proyectos_alcaldias.estado,
                tbl_proyectos_alcaldias.observaciones,
                tbl_proyectos_alcaldias.tbl_municipio_id AS codigo_municipio,
                COALESCE(tbl_vereda.latitud, tbl_ciudades_accion_unificada.latitud) AS latitud,
                COALESCE(tbl_vereda.longitud, tbl_ciudades_accion_unificada.longitud) AS longitud,
                tbl_vereda.nombre_vereda,
                tbl_secretarias_municipios.id AS secretaria_id,
                tbl_secretarias_municipios.secretaria,
                tbl_ciudades_accion_unificada.municipio,
                'assets/iconos/maps/geo.png' AS icono
            FROM
                " . $db->getTable('tbl_proyectos_alcaldias') . "
            INNER JOIN
                " . $db->getTable('tbl_secretarias_municipios') . "
                ON tbl_proyectos_alcaldias.tbl_secretarias_id = tbl_secretarias_municipios.id
            INNER JOIN
                " . $db->getTable('tbl_ciudades_accion_unificada') . "
                ON tbl_proyectos_alcaldias.tbl_municipio_id = tbl_ciudades_accion_unificada.codigo_muncipio
            LEFT JOIN
                " . $db->getTable('tbl_vereda') . "
                ON tbl_proyectos_alcaldias.tbl_vereda_id = tbl_vereda.id
            WHERE
                $whereClause
            ORDER BY
                tbl_vereda.nombre_vereda ASC,
                tbl_proyectos_alcaldias.proyecto ASC
        ";

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $arr = array();
        if ($result) {
            // Pequeño offset para evitar que los marcadores se superpongan
            $offsetIndex = 0;
            foreach ($result as $valor) {
                // Solo agregar si tiene coordenadas válidas (del municipio o vereda)
                if (!empty($valor['latitud']) && !empty($valor['longitud'])) {
                    // Agregar pequeño offset para evitar superposición de marcadores
                    $valor['latitud'] = floatval($valor['latitud']) + ($offsetIndex * 0.001);
                    $valor['longitud'] = floatval($valor['longitud']) + ($offsetIndex * 0.001);
                    $arr[] = $valor;
                    $offsetIndex++;
                }
            }
            if (!empty($arr)) {
                $arrjson = array('output' => array('valid' => true, 'response' => $arr));
            } else {
                $arrjson = array('output' => array('valid' => false, 'response' => 'No hay proyectos con coordenadas de geolocalización'));
            }
        } else {
            $arrjson = Util::error_no_result();
        }

        $db->closeConect();
        return $arrjson;
    }

    public static function getMapaByFactores($rqst)
    {

        $factorId = isset($rqst['factorId']) ? intval($rqst['factorId']) : 0;
        $pilarId = isset($rqst['pilarId']) ? intval($rqst['pilarId']) : 0;

        if ($pilarId == Util::codigoTodos()) {
            return Mapa::getMapaByTodosLosPilares($rqst);
        }

        $db = new DbConection();
        $pdo = $db->openConect();

        if ($factorId > 0) {
            $q = "SELECT 
            tbl_pilar.nombre as pilar,
            tbl_factores.tipo,
            tbl_factores.icono,
            tbl_ingreso_informacion.*,
            tbl_ciudades_accion_unificada.municipio,
            tbl_vereda.nombre_vereda
            FROM " . $db->getTable('tbl_ingreso_informacion') . "  
            INNER JOIN " . $db->getTable('tbl_factores') . "  ON tbl_ingreso_informacion.tbl_factor_id = tbl_factores.id
            INNER JOIN " . $db->getTable('tbl_pilar') . "   ON tbl_factores.tec_pilar_id = tbl_pilar.id 
            LEFT JOIN " . $db->getTable('tbl_ingreso_informacion_x_actualizacion') . "   ON tbl_ingreso_informacion.id = tbl_ingreso_informacion_x_actualizacion.tbl_ingreso_informacion_id 
            INNER JOIN " . $db->getTable('tbl_ciudades_accion_unificada') . "  ON tbl_ingreso_informacion.codigo_municipio = tbl_ciudades_accion_unificada.codigo_muncipio 
            INNER JOIN " . $db->getTable('tbl_vereda') . "  ON tbl_ingreso_informacion.tbl_vereda_id = tbl_vereda.id 
            WHERE tbl_factores.id = $factorId";
        }

        if ($pilarId) {
            $q = "SELECT 
            tbl_pilar.nombre as pilar,
            tbl_factores.tipo,
            tbl_factores.icono,
            tbl_ingreso_informacion.*,
            tbl_ciudades_accion_unificada.municipio,
            tbl_vereda.nombre_vereda
            FROM " . $db->getTable('tbl_ingreso_informacion') . "  INNER JOIN " . $db->getTable('tbl_factores') . "  ON tbl_ingreso_informacion.tbl_factor_id = tbl_factores.id
            INNER JOIN " . $db->getTable('tbl_pilar') . "   ON tbl_factores.tec_pilar_id = tbl_pilar.id 
            LEFT JOIN " . $db->getTable('tbl_ingreso_informacion_x_actualizacion') . "   ON tbl_ingreso_informacion.id = tbl_ingreso_informacion_x_actualizacion.tbl_ingreso_informacion_id 
            INNER JOIN " . $db->getTable('tbl_ciudades_accion_unificada') . "  ON tbl_ingreso_informacion.codigo_municipio = tbl_ciudades_accion_unificada.codigo_muncipio 
            INNER JOIN " . $db->getTable('tbl_vereda') . "  ON tbl_ingreso_informacion.tbl_vereda_id = tbl_vereda.id 
            WHERE tbl_pilar.id = $pilarId";
        }
        $result = $pdo->query($q);
        $arr = array();
        if ($result) {
            foreach ($result as $valor) {
                $arr[] = $valor;
            }
            $arrjson = array('output' => array('valid' => true, 'response' => $arr));
        } else {
            $arrjson = Util::error_no_result();
        }
        $db->closeConect();
        return $arrjson;
    }

    public static function getMapaByTodosLosPilares($rqst)
    {
        $db = new DbConection();
        $pdo = $db->openConect();

        $q = "SELECT 
            tbl_pilar.nombre as pilar,
            tbl_factores.tipo,
            tbl_factores.icono,
            tbl_ingreso_informacion.*,
            tbl_ciudades_accion_unificada.municipio,
            tbl_vereda.nombre_vereda
            FROM " . $db->getTable('tbl_ingreso_informacion') . "  
            INNER JOIN " . $db->getTable('tbl_factores') . " ON tbl_ingreso_informacion.tbl_factor_id = tbl_factores.id
            INNER JOIN " . $db->getTable('tbl_pilar') . " ON tbl_factores.tec_pilar_id = tbl_pilar.id 
            LEFT JOIN " . $db->getTable('tbl_ingreso_informacion_x_actualizacion') . " ON tbl_ingreso_informacion.id = tbl_ingreso_informacion_x_actualizacion.tbl_ingreso_informacion_id 
            INNER JOIN " . $db->getTable('tbl_ciudades_accion_unificada') . " ON tbl_ingreso_informacion.codigo_municipio = tbl_ciudades_accion_unificada.codigo_muncipio 
            INNER JOIN " . $db->getTable('tbl_vereda') . " ON tbl_ingreso_informacion.tbl_vereda_id = tbl_vereda.id";

        $result = $pdo->query($q);
        $arr = [];

        if ($result) {
            foreach ($result as $valor) {
                $arr[] = $valor;
            }

            $arrjson = ['output' => ['valid' => true, 'response' => $arr]];
        } else {
            $arrjson = Util::error_no_result();
        }

        $db->closeConect();
        return $arrjson;
    }


    /**
     * Metodo que muestra la informacion de los puntos en el mapa por le pilar selecionado y por ciudad Id
     * en el moodulo de geocalización en municipios y veredas cuando se tiene la vereda Id
     */
    public static function getMapaByPilarByMunicipioId($rqst)
    {

        $pilarId = isset($rqst['pilarId']) ? intval($rqst['pilarId']) : 0;
        $codigo_municipio = isset($rqst['codigoMunicipio']) ? intval($rqst['codigoMunicipio']) : 0;
        $veredaId = isset($rqst['veredaId']) ? intval($rqst['veredaId']) : 0;
        $factor_id = isset($rqst['factor_id']) ? intval($rqst['factor_id']) : 0;

        if (!$pilarId || !$codigo_municipio) {
            return Util::error_missing_data();
        }

        if ($pilarId == Util::codigoTodos()) {
            return Mapa::getMapaByTodosPilaresByMunicipioId($rqst);
        }

        $db = new DbConection();
        $pdo = $db->openConect();

        try {
            // La tabla principal es tbl_ingreso_informacion para no perder registros
            // sin vereda asociada (tbl_vereda_id NULL). tbl_vereda se une con LEFT JOIN.
            $q = "SELECT
                    tbl_vereda.nombre_vereda,
                    tbl_vereda.codigo_vereda,
                    tbl_ciudades_accion_unificada.municipio,
                    tbl_factores.tipo,
                    tbl_pilar.id AS pilar_id,
                    tbl_pilar.nombre AS pilar,
                    tbl_factores.icono,
                    tbl_ingreso_informacion.*
                FROM
                    " . $db->getTable('tbl_ingreso_informacion') . " tbl_ingreso_informacion
                    LEFT JOIN " . $db->getTable('tbl_vereda') . " tbl_vereda ON tbl_ingreso_informacion.tbl_vereda_id = tbl_vereda.id
                    INNER JOIN " . $db->getTable('tbl_ciudades_accion_unificada') . " tbl_ciudades_accion_unificada ON tbl_ingreso_informacion.codigo_municipio = tbl_ciudades_accion_unificada.codigo_muncipio
                    INNER JOIN " . $db->getTable('tbl_factores') . " tbl_factores ON tbl_ingreso_informacion.tbl_factor_id = tbl_factores.id
                    INNER JOIN " . $db->getTable('tbl_pilar') . " tbl_pilar ON tbl_factores.tec_pilar_id = tbl_pilar.id
                WHERE
                    tbl_ingreso_informacion.codigo_municipio = :codigo_municipio";

            if ($pilarId !== 10000) {
                $q .= " AND tbl_factores.tec_pilar_id = :pilarId";
            }

            if ($veredaId > 0) {
                $q .= " AND tbl_ingreso_informacion.tbl_vereda_id = :veredaId";
            }
            if ($factor_id > 0) {
                $q .= " AND tbl_ingreso_informacion.tbl_factor_id = :factor";
            }

            $stmt = $pdo->prepare($q);
            $stmt->bindParam(':codigo_municipio', $codigo_municipio, PDO::PARAM_INT);

            if ($pilarId !== 10000) {
                $stmt->bindParam(':pilarId', $pilarId, PDO::PARAM_INT);
            }

            if ($veredaId > 0) {
                $stmt->bindParam(':veredaId', $veredaId, PDO::PARAM_INT);
            }
            if ($factor_id > 0) {
                $stmt->bindParam(':factor', $factor_id, PDO::PARAM_INT);
            };

            $stmt->execute();
            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

            return ['output' => ['valid' => true, 'response' => $result]];
        } catch (Exception $e) {
            return Util::error_general();
        } finally {
            $db->closeConect();
        }
    }
    /**
     * Metodo que lista las veredas por municipio
     * @param array $rqst
     * @return array
     */
    public static function getAllByMunicipio($rqst)
    {

        $todos = Util::codigoTodos();
        $codigo_municipio = isset($rqst['codigoMunicipio']) ? intval($rqst['codigoMunicipio']) : 0;
        $pilarId = isset($rqst['pilarId']) ? intval($rqst['pilarId']) : $todos;


        $db = new DbConection();
        $pdo = $db->openConect();

        try {
            $q = "SELECT 
                    tbl_vereda.*, 
                    tbl_ciudades_accion_unificada.municipio, 
                    tbl_factores.tipo, 
                    tbl_pilar.id AS pilar_id, 
                    tbl_pilar.nombre as pilar,
                    tbl_factores.icono,
                    tbl_ingreso_informacion.*
                FROM 
                    " . $db->getTable('tbl_vereda') . " tbl_vereda
                INNER JOIN 
                    " . $db->getTable('tbl_ciudades_accion_unificada') . " tbl_ciudades_accion_unificada 
                    ON tbl_vereda.municipio_id = tbl_ciudades_accion_unificada.codigo_muncipio
                INNER JOIN 
                    " . $db->getTable('tbl_ingreso_informacion') . " tbl_ingreso_informacion 
                    ON tbl_vereda.id = tbl_ingreso_informacion.tbl_vereda_id 
                INNER JOIN 
                    " . $db->getTable('tbl_factores') . " tbl_factores 
                    ON tbl_ingreso_informacion.tbl_factor_id = tbl_factores.id
                INNER JOIN 
                    " . $db->getTable('tbl_pilar') . " tbl_pilar 
                    ON tbl_factores.tec_pilar_id = tbl_pilar.id
                WHERE 
                    tbl_ingreso_informacion.codigo_municipio = :codigo_municipio ";

            if ($pilarId != $todos) {
                $q .= " AND tbl_factores.tec_pilar_id = :pilarId";
            }

            $stmt = $pdo->prepare($q);
            $stmt->bindParam(':codigo_municipio', $codigo_municipio, PDO::PARAM_INT);
            if ($pilarId != $todos) {
                $stmt->bindValue(':pilarId', $pilarId, PDO::PARAM_INT);
            }

            $stmt->execute();
            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

            return ['output' => ['valid' => true, 'response' => $result ?: null]];
        } catch (Exception $e) {
            return Util::error_general();
        } finally {
            $db->closeConect();
        }
    }

    /**
     * Metodo que lista las sedes de las instituciones de PAE
     */
    public static function getMapaSedesPAE($rqst)
    {
        $codigo_departamento = Util::getDepartamentoPrincipal();

        if (!$codigo_departamento) {
            return Util::error_missing_data();
        }

        $db = new DbConection();
        $pdo = $db->openConect();

        try {
            $q = "SELECT 
                tbl_pae.tbl_municipio_id, 
                tbl_ciudades_accion_unificada.municipio, 
                tbl_ciudades_accion_unificada.class, 
                tbl_ciudades_accion_unificada.latitud, 
                tbl_ciudades_accion_unificada.longitud, 
                tbl_vereda.nombre_vereda, 
                tbl_sede_educativa.nombre, 
                tbl_instituciones_educativas.nombre_institucion
            FROM (
                ((" . $db->getTable('tbl_pae') . " AS tbl_pae 
                LEFT JOIN " . $db->getTable('tbl_ciudades_accion_unificada') . " AS tbl_ciudades_accion_unificada 
                    ON tbl_pae.tbl_municipio_id = tbl_ciudades_accion_unificada.codigo_muncipio)
                LEFT JOIN " . $db->getTable('tbl_sede_educativa') . " AS tbl_sede_educativa 
                    ON tbl_pae.tbl_sede_educativa_id = tbl_sede_educativa.id)
                LEFT JOIN " . $db->getTable('tbl_vereda') . " AS tbl_vereda 
                    ON tbl_sede_educativa.tbl_vereda_id = tbl_vereda.id
            )
            INNER JOIN " . $db->getTable('tbl_instituciones_educativas') . " AS tbl_instituciones_educativas 
                ON tbl_pae.tbl_instituciones_educativas_id = tbl_instituciones_educativas.id
            WHERE 
                tbl_ciudades_accion_unificada.codigo_departamento = :codigo_departamento
            ";
            $stmt = $pdo->prepare($q);
            $stmt->bindParam(':codigo_departamento', $codigo_departamento, PDO::PARAM_INT);
            $stmt->execute();
            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

            return ['output' => ['valid' => true, 'response' => $result ?: Util::error_missing_data()]];
        } catch (Exception $e) {
            return Util::error_general();
        } finally {
            $db->closeConect();
        }
    }

    public static function getMapaByTodosPilaresByMunicipioId($rqst)
    {
        $codigo_municipio = isset($rqst['codigoMunicipio']) ? intval($rqst['codigoMunicipio']) : 0;
        $factor_id = isset($rqst['factor_id']) ? intval($rqst['factor_id']) : 0;
        $vereda_id = isset($rqst['veredaId']) ? intval($rqst['veredaId']) : 0;

        if (!$codigo_municipio) {
            return Util::error_missing_data();
        }

        $db = new DbConection();
        $pdo = $db->openConect();

        try {
            $q = "SELECT 
                tbl_vereda.*, 
                tbl_ciudades_accion_unificada.municipio, 
                tbl_factores.tipo, 
                tbl_pilar.id AS pilar_id, 
                tbl_pilar.nombre AS pilar,
                tbl_factores.id AS factor_id,
                tbl_factores.icono,
                tbl_ingreso_informacion.*
            FROM 
                " . $db->getTable('tbl_vereda') . " tbl_vereda
            INNER JOIN 
                " . $db->getTable('tbl_ciudades_accion_unificada') . " tbl_ciudades_accion_unificada 
                ON tbl_vereda.municipio_id = tbl_ciudades_accion_unificada.codigo_muncipio
            INNER JOIN 
                " . $db->getTable('tbl_ingreso_informacion') . " tbl_ingreso_informacion 
                ON tbl_vereda.id = tbl_ingreso_informacion.tbl_vereda_id 
            INNER JOIN 
                " . $db->getTable('tbl_factores') . " tbl_factores 
                ON tbl_ingreso_informacion.tbl_factor_id = tbl_factores.id
            INNER JOIN 
                " . $db->getTable('tbl_pilar') . " tbl_pilar 
                ON tbl_factores.tec_pilar_id = tbl_pilar.id
            WHERE 
                tbl_ingreso_informacion.codigo_municipio = :codigo_municipio";

            if ($factor_id) {
                $q .= " AND tbl_ingreso_informacion.tbl_factor_id = :factor";
            }
            if ($vereda_id > 0) {
                $q .= " AND tbl_ingreso_informacion.tbl_vereda_id = :vereda";
            }

            $stmt = $pdo->prepare($q);
            $stmt->bindParam(':codigo_municipio', $codigo_municipio, PDO::PARAM_INT);
            if ($factor_id) {
                $stmt->bindParam(':factor', $factor_id, PDO::PARAM_INT);
            }
            if ($vereda_id > 0) {
                $stmt->bindParam(':vereda', $vereda_id, PDO::PARAM_INT);
            }
            $stmt->execute();

            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

            return ['output' => ['valid' => true, 'response' => $result ?: null]];
        } catch (Exception $e) {
            return Util::error_general("Error listando todos los pilares por vereda.");
        } finally {
            $db->closeConect();
        }
    }
}
