<?php
require_once 'SessionData.php';
require_once 'DbConection.php';
require_once 'Util.php';
/**
 * Clase que contiene todas las operaciones utilizadas sobre la base de datos
 * @author SPIDERSOFTWARE
 */
class Colombia
{

    public function __construct() {}

    public static function getAll($rqst)
    {

        $id = isset($rqst['id']) ? intval($rqst['id']) : 0;

        $db = new DbConection();
        $pdo = $db->openConect();

        $q = "SELECT * FROM " . $db->getTable('tbl_colombia');
        if ($id > 0) {
            $q = "SELECT * FROM " . $db->getTable('tbl_colombia') . " WHERE id = " . $id;
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

    public static function getInformacionSecretariaHaciendaColoresMapa($rqst)
    {
        $default = 'Operativos Contrabando licores';
        $accion = isset($rqst['accion']) ? urldecode($rqst['accion']) : $default;

        // if($accion == 'Capacitacion Fiscal y Financiera'){
        //     $accion = $default;
        // }
        
        $secretariaId = Util::getSecretariaIdHacienda();
        $departamento = Util::getDepartamentoPrincipal();

        $accionesPermitidas = [
            'Capacitacion Fiscal y Financiera',
            'Operativos Contrabando licores',
            'Operativos Contrabando cigarrillos',
            'Operativos Contrabando cerveza',
            'Impuesto Vehicular Recaudado',
            'Recaudo del impuesto al consumo',
            'Recaudo del impuesto de registro',
            'Impuesto Estampillas Recaudado',

            'GOA Aprehensiones de Licores',
            'GOA Aprehensión de Cigarrillos',
            'GOA Aprehensión de Cervezas',
            'GOA Aprehensión de Tabaco y Otros',
            'Registro de Visitas a Establecimientos Comerciales',
            'GOA Juridico'
        ];

        if (!in_array($accion, $accionesPermitidas)) {
            return Util::error_missing_data();
        }

        $db = new DbConection();
        $pdo = $db->openConect();

        // Puntajes secretaria
        $qPuntaje = "SELECT * FROM " . $db->getTable('tbl_puntajes_secretarias') . " WHERE tbl_secretaria_id = " . $secretariaId;
        $resultPuntajes = $pdo->query($qPuntaje);
        $puntajes = $resultPuntajes ? $resultPuntajes->fetchAll(PDO::FETCH_ASSOC) : [];

        // Query principal
        $query = "
            SELECT
                ciudades.*,
                SUM(h.total_valor) AS total_valor
            FROM  " . $db->getTable('tbl_ciudades_accion_unificada') . " AS ciudades
            LEFT JOIN (
                SELECT
                    h.tbl_municipio_id,
                    CASE
                        WHEN h.accion = 'Operativos Contrabando licores' THEN h.incautacion_licores
                        WHEN h.accion = 'Operativos Contrabando cigarrillos' THEN (h.incautacion_cigarrillos + h.incautacion_tabaco)
                        WHEN h.accion = 'Operativos Contrabando cerveza' THEN h.incautacion_cerveza
                        WHEN h.accion = 'Capacitacion Fiscal y Financiera' THEN h.cantidad_personas
                        WHEN h.accion = 'Impuesto Vehicular Recaudado' THEN h.valor_recaudo_impuesto_vehicular
                        WHEN h.accion = 'Recaudo del impuesto al consumo' THEN (h.valor_importado + h.valor_nacional)
                        WHEN h.accion = 'Recaudo del impuesto de registro' THEN (h.valor_tramite + h.valor_recaudo)
                        WHEN h.accion = 'Impuesto Estampillas Recaudado' THEN h.valor_estampilla

                        WHEN h.accion = 'GOA Aprehensiones de Licores' THEN h.cantidad_aprehendida
                        WHEN h.accion = 'GOA Aprehensión de Cigarrillos' THEN cantidad_aprehendida
                        WHEN h.accion = 'GOA Aprehensión de Cervezas' THEN cantidad_aprehendida
                        WHEN h.accion = 'GOA Aprehensión de Tabaco y Otros' THEN cantidad_aprehendida

                        WHEN h.accion = 'Registro de Visitas a Establecimientos Comerciales' THEN h.cantidad_visitas_al_municipio

                        WHEN h.accion = 'GOA Juridico' THEN (h.goa_juridico_custodia_cantidad_procesos + h.goa_juridico_destruccion_cantidad_unidades)

                        ELSE 0
                    END AS total_valor
                FROM " . $db->getTable('tbl_hacienda') . " AS h
                WHERE h.accion = :accion
            ) AS h ON h.tbl_municipio_id = ciudades.codigo_muncipio
            WHERE ciudades.codigo_departamento = :departamento
            GROUP BY ciudades.codigo_muncipio, ciudades.municipio
            ORDER BY ciudades.codigo_muncipio ASC
        ";
        $stmt = $pdo->prepare($query);
        $stmt->execute(['accion' => $accion, 'departamento' => $departamento]);
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $arr = [];
        if ($result) {
            // Asignar color específico por acción
            $colorPorAccion = [
                'Operativos Contrabando licores' => '#62af0a',
                'Operativos Contrabando cigarrillos' => '#27C8F5',
                'Operativos Contrabando cerveza'  => '#FFFF00',
                'Capacitacion Fiscal y Financiera' => '#62af0a',
                'Impuesto Vehicular Recaudado' => '#4169E1',
                'Recaudo del impuesto al consumo' =>  "#DC143C",
                'Recaudo del impuesto de registro' => "#FFA500",
                'Impuesto Estampillas Recaudado' => '#EDC0DF',

                // Colores para GOA
                'GOA Aprehensiones de Licores' => '#c1c0edff',
                'GOA Aprehensión de Cigarrillos' => '#e427f5ff',
                'GOA Aprehensión de Cervezas'  => '#ff00aaff',
                'GOA Aprehensión de Tabaco y Otros' => '#e28d2bff',

                'Registro de Visitas a Establecimientos Comerciales' => '#2b80e2ff',
                'GOA Juridico' => '#1a6b4a'

            ];
            $accionesGOAAprehensiones = [
                'GOA Aprehensiones de Licores',
                'GOA Aprehensión de Cigarrillos',
                'GOA Aprehensión de Cervezas',
                'GOA Aprehensión de Tabaco y Otros',
            ];
            $esGOAAprehension = in_array($accion, $accionesGOAAprehensiones);
            $esGOAVisitas     = ($accion === 'Registro de Visitas a Establecimientos Comerciales');

            foreach ($result as $valor) {
                $total = intval($valor['total_valor'] ?? 0);

                if ($esGOAAprehension) {
                    $color = getColorByNumGOA($total);
                } elseif ($esGOAVisitas) {
                    $color = getColorByNumGOAVisitas($total);
                } else {
                    // Acciones no-GOA: mantener lógica original de color fijo
                    $color = Util::getColorNeutroMapa();
                    if ($total > 0 && isset($colorPorAccion[$accion])) {
                        $color = $colorPorAccion[$accion];
                    } elseif ($total > 0) {
                        $color = '#999999';
                    }
                }

                $valor['color']   = $color;
                $valor['num_val'] = $total;
                $arr[] = $valor;
            }



            $response = ['output' => ['valid' => true, 'response' => $arr, 'puntajes' => $puntajes]];
        } else {
            $response = Util::error_no_result();
        }

        $db->closeConect();
        return $response;
    }

    public static function getInformacionSecretariaColoresMapa($rqst)
    {
        $secretariaId = isset($rqst['secretariaId']) ? intval($rqst['secretariaId']) : 0;
        $departamento = isset($rqst['codigoMunicipio']) ? intval($rqst['codigoMunicipio']) : 0;
        $accion = isset($rqst['accion']) ? ($rqst['accion']) : 'Capacitacion+Fiscal+y+Financiera';

        // Si es Secretaria Hacienda
        if ($secretariaId == Util::getSecretariaIdHacienda()) {
            return self::getInformacionSecretariaHaciendaColoresMapa($rqst);
        }

        $db = new DbConection();
        $pdo = $db->openConect();

        $colorDefecto = Util::getColorNeutroMapa();
        $tbl_actualizacion = $db->getTable('tbl_ingreso_informacion_x_actualizacion');

        // Informacion de los puntajes de secretaria
        $qPuntaje = "SELECT * FROM " . $db->getTable('tbl_puntajes_secretarias') . " WHERE tbl_secretaria_id = " . $secretariaId;
        $resultPuntajes = $pdo->query($qPuntaje);
        $puntajes = array();
        if ($resultPuntajes) {
            foreach ($resultPuntajes as $valor) {
                $puntajes[] = $valor;
            }
        }

        $q = "
        SELECT 
            tbl_ciudades_accion_unificada.codigo_muncipio, 
            tbl_ciudades_accion_unificada.municipio, 
            tbl_ciudades_accion_unificada.d, 
            tbl_ciudades_accion_unificada.name, 
            tbl_ciudades_accion_unificada.class, 

            SUM(CASE 
                WHEN tbl_factores.tbl_secretaria_id = :secretariaId THEN 
                    
                    COALESCE(
                        t_act.valor_actualizacion, 
                        tbl_ingreso_informacion.valor
                    )
                ELSE 0 
            END) AS suma,
            tbl_ingreso_informacion.codigo_departamento,
            COALESCE(MAX(CASE 
                WHEN tbl_factores.tbl_secretaria_id = :secretariaId THEN tbl_factores.tbl_secretaria_id 
                ELSE 0 
            END), 0) AS tbl_secretaria_id
        FROM 
            " . $db->getTable('tbl_ciudades_accion_unificada') . " 
        LEFT JOIN 
            " . $db->getTable('tbl_ingreso_informacion') . " tbl_ingreso_informacion 
            ON tbl_ingreso_informacion.codigo_municipio = tbl_ciudades_accion_unificada.codigo_muncipio 
        LEFT JOIN 
            " . $db->getTable('tbl_factores') . " tbl_factores 
            ON tbl_ingreso_informacion.tbl_factor_id = tbl_factores.id 
            AND tbl_factores.tbl_secretaria_id = :secretariaId
        

        LEFT JOIN 
            (
                SELECT 
                    tbl_ingreso_informacion_id, 
                    valor_actualizacion
                FROM {$tbl_actualizacion} t1
                WHERE dtcreate = (
                    SELECT MAX(dtcreate) 
                    FROM {$tbl_actualizacion} 
                    WHERE tbl_ingreso_informacion_id = t1.tbl_ingreso_informacion_id
                )
            ) AS t_act 
            ON t_act.tbl_ingreso_informacion_id = tbl_ingreso_informacion.id
        
        WHERE 
            tbl_ciudades_accion_unificada.codigo_departamento = $departamento 
        GROUP BY 
            tbl_ciudades_accion_unificada.codigo_muncipio,
            tbl_ciudades_accion_unificada.municipio, 
            tbl_ciudades_accion_unificada.d, 
            tbl_ciudades_accion_unificada.name, 
            tbl_ciudades_accion_unificada.class,
            tbl_ingreso_informacion.codigo_departamento
        ORDER BY 
            tbl_ciudades_accion_unificada.municipio;
        ";
        $stmt = $pdo->prepare($q);
        $stmt->bindParam(':secretariaId', $secretariaId, PDO::PARAM_INT);
        
        $stmt->execute([':secretariaId' => $secretariaId]); 
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $arr = array();

        if ($result) {
            foreach ($result as $valor) {
                $color = null;
                $suma = floatval($valor['suma']);

                if (!empty($puntajes)) {

                    if ($valor['tbl_secretaria_id'] > 0) {
                        foreach ($puntajes as $p) {
                            if (
                                $suma >= floatval($p['rango_desde']) &&
                                $suma <= floatval($p['rango_hasta'])
                            ) {
                                $color = $p['color'];
                                break;
                            }
                        }
                    }
                }

                if (empty($color)) {
                    $color = $colorDefecto;
                }

                $valor['color'] = $color;
                $arr[] = $valor;
            }

            $arrjson = array('output' => array('valid' => true, 'response' => $arr, 'puntajes' => $puntajes));
        } else {
            $arrjson = Util::error_no_result();
        }

        $db->closeConect();
        return $arrjson;
    }    
    /**
     * igual al getInformacionSecretariaHaciendaColoresMapa
     * para el MAPA INICIAL (Línea Base).
     */
    public static function getInformacionSecretariaHaciendaColoresMapaInicial($rqst)
    {
        $default_accion = 'Operativos Contrabando licores';
        $accion = isset($rqst['accion']) ? urldecode($rqst['accion']) : $default_accion;
        
        
        $secretariaId = Util::getSecretariaIdHacienda();
        $departamento = Util::getDepartamentoPrincipal();

        $accionesPermitidas = [
            'Capacitacion Fiscal y Financiera',
            'Operativos Contrabando licores',
            'Operativos Contrabando cigarrillos',
            'Operativos Contrabando cerveza',
            'Impuesto Vehicular Recaudado',
            'Recaudo del impuesto al consumo',
            'Recaudo del impuesto de registro',
            'Impuesto Estampillas Recaudado',
            'GOA Aprehensiones de Licores',
            'GOA Aprehensión de Cigarrillos',
            'GOA Aprehensión de Cervezas',
            'GOA Aprehensión de Tabaco y Otros',
            'Registro de Visitas a Establecimientos Comerciales',
            'GOA Juridico'
        ];

        if (!in_array($accion, $accionesPermitidas)) {
            return Util::error_missing_data();
        }

        $db = new DbConection();
        $pdo = $db->openConect();

        // Puntajes secretaria
        $qPuntaje = "SELECT * FROM " . $db->getTable('tbl_puntajes_secretarias') . " WHERE tbl_secretaria_id = " . $secretariaId;
        $resultPuntajes = $pdo->query($qPuntaje);
        $puntajes = $resultPuntajes ? $resultPuntajes->fetchAll(PDO::FETCH_ASSOC) : [];


        $query = "
            SELECT 
                ciudades.*,
                SUM(h.total_valor) AS total_valor
            FROM  " . $db->getTable('tbl_ciudades_accion_unificada') . " AS ciudades
            LEFT JOIN (
                SELECT
                    h.tbl_municipio_id,
                    CASE
                        WHEN h.accion = 'Operativos Contrabando licores' THEN h.incautacion_licores
                        WHEN h.accion = 'Operativos Contrabando cigarrillos' THEN (h.incautacion_cigarrillos + h.incautacion_tabaco)
                        WHEN h.accion = 'Operativos Contrabando cerveza' THEN h.incautacion_cerveza
                        WHEN h.accion = 'Capacitacion Fiscal y Financiera' THEN h.cantidad_personas
                        WHEN h.accion = 'Impuesto Vehicular Recaudado' THEN h.valor_recaudo_impuesto_vehicular
                        WHEN h.accion = 'Recaudo del impuesto al consumo' THEN (h.valor_importado + h.valor_nacional)
                        WHEN h.accion = 'Recaudo del impuesto de registro' THEN (h.valor_tramite + h.valor_recaudo)
                        WHEN h.accion = 'Impuesto Estampillas Recaudado' THEN h.valor_estampilla
                        WHEN h.accion = 'GOA Aprehensiones de Licores' THEN h.cantidad_aprehendida
                        WHEN h.accion = 'GOA Aprehensión de Cigarrillos' THEN cantidad_aprehendida
                        WHEN h.accion = 'GOA Aprehensión de Cervezas' THEN cantidad_aprehendida
                        WHEN h.accion = 'GOA Aprehensión de Tabaco y Otros' THEN cantidad_aprehendida
                        WHEN h.accion = 'Registro de Visitas a Establecimientos Comerciales' THEN h.cantidad_visitas_al_municipio
                        WHEN h.accion = 'GOA Juridico' THEN (h.goa_juridico_custodia_cantidad_procesos + h.goa_juridico_destruccion_cantidad_unidades)
                        ELSE 0
                    END AS total_valor
                FROM " . $db->getTable('tbl_hacienda') . " AS h
                WHERE h.accion = :accion_hacienda_filtro -- <<-- ¡USANDO EL PLACEHOLDER CORRECTO!
            ) AS h ON h.tbl_municipio_id = ciudades.codigo_muncipio
            WHERE ciudades.codigo_departamento = :departamento
            GROUP BY ciudades.codigo_muncipio, ciudades.municipio
            ORDER BY ciudades.codigo_muncipio ASC
        ";

        $stmt = $pdo->prepare($query);

        $stmt->bindParam(':accion_hacienda_filtro', $accion, PDO::PARAM_STR); 
        $stmt->bindParam(':departamento', $departamento, PDO::PARAM_INT);
        

        $stmt->execute();
        
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $arr = [];
        if ($result) {

            $colorPorAccion = [
                'Operativos Contrabando licores' => '#62af0a',
                'Operativos Contrabando cigarrillos' => '#27C8F5',
                'Operativos Contrabando cerveza'  => '#FFFF00',
                'Capacitacion Fiscal y Financiera' => '#8A2BE2',
                'Impuesto Vehicular Recaudado' => '#4169E1',
                'Recaudo del impuesto al consumo' =>  "#DC143C",
                'Recaudo del impuesto de registro' => "#FFA500",
                'Impuesto Estampillas Recaudado' => '#EDC0DF',
                'GOA Aprehensiones de Licores' => '#c1c0edff',
                'GOA Aprehensión de Cigarrillos' => '#e427f5ff',
                'GOA Aprehensión de Cervezas'  => '#ff00aaff',
                'GOA Aprehensión de Tabaco y Otros' => '#e28d2bff',
                'Registro de Visitas a Establecimientos Comerciales' => '#2b80e2ff',
                'GOA Juridico' => '#1a6b4a'
            ];
            $accionesGOAAprehensiones = [
                'GOA Aprehensiones de Licores',
                'GOA Aprehensión de Cigarrillos',
                'GOA Aprehensión de Cervezas',
                'GOA Aprehensión de Tabaco y Otros',
            ];
            $esGOAAprehension = in_array($accion, $accionesGOAAprehensiones);
            $esGOAVisitas     = ($accion === 'Registro de Visitas a Establecimientos Comerciales');

            foreach ($result as $valor) {
                $total = intval($valor['total_valor'] ?? 0);

                if ($esGOAAprehension) {
                    $color = getColorByNumGOA($total);
                } elseif ($esGOAVisitas) {
                    $color = getColorByNumGOAVisitas($total);
                } else {
                    $color = Util::getColorNeutroMapa();
                    if ($total > 0 && isset($colorPorAccion[$accion])) {
                        $color = $colorPorAccion[$accion];
                    } elseif ($total > 0) {
                        $color = '#999999';
                    }
                }

                $valor['color']   = $color;
                $valor['num_val'] = $total;
                $arr[] = $valor;
            }

            $response = ['output' => ['valid' => true, 'response' => $arr, 'puntajes' => $puntajes]];
        } else {
            $response = Util::error_no_result();
        }

        $db->closeConect();
        return $response;
    }




/**
     * Metodo para calcular los colores de todos los municipios para el MAPA ACTUAL (AVANCE).
     * NOTA: Esta función se usa para el Mapa ACTUAL en la comparación, a pesar de su nombre.
     */
    public static function getInformacionSecretariaColoresMapaInicial($rqst)
    {
        $secretariaId = isset($rqst['secretariaId']) ? intval($rqst['secretariaId']) : 0;
        $departamento = isset($rqst['codigoMunicipio']) ? intval($rqst['codigoMunicipio']) : 0;
        $accion = isset($rqst['accion']) ? ($rqst['accion']) : 'Capacitacion+Fiscal+y+Financiera';

        if ($secretariaId == Util::getSecretariaIdHacienda()) {
            return self::getInformacionSecretariaHaciendaColoresMapaInicial($rqst);
        }

        $db = new DbConection();
        $pdo = $db->openConect();

        $colorDefecto = Util::getColorNeutroMapa();

        $qPuntaje = "SELECT * FROM " . $db->getTable('tbl_puntajes_secretarias') . " WHERE tbl_secretaria_id = " . $secretariaId;
        $resultPuntajes = $pdo->query($qPuntaje);
        $puntajes = array();
        if ($resultPuntajes) {
            foreach ($resultPuntajes as $valor) {
                $puntajes[] = $valor;
            }
        }

        $q = "
        SELECT 
            tbl_ciudades_accion_unificada.codigo_muncipio, 
            tbl_ciudades_accion_unificada.municipio, 
            tbl_ciudades_accion_unificada.d, 
            tbl_ciudades_accion_unificada.name, 
            tbl_ciudades_accion_unificada.class, 
            -- CLAVE: Sumamos el VALOR ACTUAL (tbl_ingreso_informacion.valor) para que la suma sea BAJA/BUENA
            SUM(CASE 
                WHEN tbl_factores.tbl_secretaria_id = :secretariaId THEN tbl_ingreso_informacion.valor
                ELSE 0 
            END) AS suma,
            tbl_ingreso_informacion.codigo_departamento,
            COALESCE(MAX(CASE 
                WHEN tbl_factores.tbl_secretaria_id = :secretariaId THEN tbl_factores.tbl_secretaria_id 
                ELSE 0 
            END), 0) AS tbl_secretaria_id
        FROM 
            " . $db->getTable('tbl_ciudades_accion_unificada') . " 
        LEFT JOIN 
            " . $db->getTable('tbl_ingreso_informacion') . "  
            ON tbl_ingreso_informacion.codigo_municipio = tbl_ciudades_accion_unificada.codigo_muncipio 
        LEFT JOIN 
            " . $db->getTable('tbl_factores') . "  
            ON tbl_ingreso_informacion.tbl_factor_id = tbl_factores.id 
            AND tbl_factores.tbl_secretaria_id = :secretariaId
        WHERE 
            tbl_ciudades_accion_unificada.codigo_departamento = $departamento 
        GROUP BY 
            tbl_ciudades_accion_unificada.codigo_muncipio,
            tbl_ciudades_accion_unificada.municipio, 
            tbl_ciudades_accion_unificada.d, 
            tbl_ciudades_accion_unificada.name, 
            tbl_ciudades_accion_unificada.class,
            tbl_ingreso_informacion.codigo_departamento
        ORDER BY 
            tbl_ciudades_accion_unificada.municipio;
        ";



        $stmt = $pdo->prepare($q);
        $stmt->bindParam(':secretariaId', $secretariaId, PDO::PARAM_INT);
        $stmt->execute();
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $arr = array();

        if ($result) {
            foreach ($result as $valor) {
                $color = null;
                $suma = floatval($valor['suma']);

                if (!empty($puntajes)) {
                    if ($valor['tbl_secretaria_id'] > 0) {
                        

                        if ($suma == 0 && count($puntajes) > 0) {
                            $puntosOrdenados = $puntajes;
                            usort($puntosOrdenados, function($a, $b) {

                                return floatval($a['rango_desde']) <=> floatval($b['rango_desde']); 
                            });
                            
                            $color = $puntosOrdenados[0]['color'] ?? null;
                        }

                        
                        if (empty($color)) { 
                            foreach ($puntajes as $p) {
                                if (
                                    $suma >= floatval($p['rango_desde']) &&
                                    $suma <= floatval($p['rango_hasta'])
                                ) {
                                    $color = $p['color'];
                                    break;
                                }
                            }
                        }
                    }
                }


                if (empty($color)) {
                    $color = $colorDefecto;
                }

                $valor['color'] = $color;
                $arr[] = $valor;
            }

            $arrjson = array('output' => array('valid' => true, 'response' => $arr, 'puntajes' => $puntajes));
        } else {
            $arrjson = Util::error_no_result();
        }

        $db->closeConect();
        return $arrjson;
    }




public static function calcularColorMapaActualByPilar($municipio, $pilarId)
{
    $db  = new DbConection();
    $pdo = $db->openConect();

    try {

        $sql = "
        SELECT 
            v.id AS vereda_id,
            v.nombre_vereda,
            v.path,
            v.points,
            v.tspan,

            SUM(
                COALESCE(act.valor_actual, base.valor_base, 0)
            ) AS valor_factor

        FROM {$db->getTable('tbl_vereda')} v
      
        INNER JOIN {$db->getTable('tbl_factores')} f 
                ON f.tbl_pilar_id = :pilar

        LEFT JOIN (
            SELECT 
                i.id AS ingreso_id,
                i.tbl_vereda_id,
                i.tbl_factor_id,
                COALESCE(i.valor_inicial, i.valor, 0) AS valor_base
            FROM {$db->getTable('tbl_ingreso_informacion')} i
            WHERE i.codigo_municipio = :mun
        ) base 
            ON base.tbl_vereda_id = v.id
           AND base.tbl_factor_id = f.id

        LEFT JOIN (
            SELECT 
                ax.tbl_ingreso_informacion_id AS ingreso_id,
                ax.valor_actualizacion AS valor_actual
            FROM {$db->getTable('tbl_ingreso_informacion_x_actualizacion')} ax
            INNER JOIN (
                SELECT 
                    tbl_ingreso_informacion_id,
                    MAX(dtcreate) AS max_dt
                FROM {$db->getTable('tbl_ingreso_informacion_x_actualizacion')}
                GROUP BY tbl_ingreso_informacion_id
            ) ult 
              ON ult.tbl_ingreso_informacion_id = ax.tbl_ingreso_informacion_id
             AND ult.max_dt = ax.dtcreate
        ) act 
            ON act.ingreso_id = base.ingreso_id

        WHERE v.codigo_municipio = :mun2

        GROUP BY v.id
        ORDER BY v.nombre_vereda ASC
        ";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            'pilar' => $pilarId,
            'mun'   => $municipio,
            'mun2'  => $municipio
        ]);

        return ['output'=>['valid'=>true,'response'=>$stmt->fetchAll(PDO::FETCH_ASSOC)]];

    } catch (Exception $e) {
        return Util::error_general("Error mapa actual por pilar: ".$e->getMessage());
    }
}




/**
 * MAPA INICIAL – MOSTRAR TODAS LAS VEREDAS QUE TENGAN CUALQUIER FACTOR
 * Se suman TODOS los factores pertenecientes a la secretaría.
 */
public static function calcularColorMapaInicialByFactor($rqst)
{
    $codigoMunicipio = intval($rqst['codigo_municipio'] ?? 0);
    $secretariaId    = intval($rqst['secretariaId'] ?? 0);

    if ($codigoMunicipio == 0 || $secretariaId == 0) {
        return Util::error_missing_data();
    }

    $db  = new DbConection();
    $pdo = $db->openConect();

    try {

        /* -----------------------------------------------------------
         * 1) OBTENER RANGOS/PUNTAJES DE LA SECRETARÍA
         * ----------------------------------------------------------- */
        $sqlPuntajes = "
            SELECT rango_desde, rango_hasta, color
            FROM {$db->getTable('tbl_puntajes_secretarias')}
            WHERE tbl_secretaria_id = :sec
              AND tipo_medicion = 'Cantidad'
            ORDER BY rango_desde ASC
        ";

        $stmtP = $pdo->prepare($sqlPuntajes);
        $stmtP->execute(['sec' => $secretariaId]);
        $puntajes = $stmtP->fetchAll(PDO::FETCH_ASSOC);

        if (empty($puntajes)) {
            return Util::error_general("No hay rangos definidos para la secretaría.");
        }


        /* -----------------------------------------------------------
         * 2) CONSULTA PRINCIPAL
         *    → SUMA valor_inicial/valor de TODOS LOS FACTORES
         *      de esa secretaría en cada vereda.
         * ----------------------------------------------------------- */

        $sql = "
            SELECT 
                v.id AS vereda_id,
                v.nombre_vereda,
                v.path,
                v.points,
                v.tspan,

                /* SUMA de TODOS los factores de la secretaría */
                COALESCE(SUM(COALESCE(i.valor_inicial, i.valor, 0)), 0) AS valor_factor

            FROM {$db->getTable('tbl_vereda')} v

            /* Todos los factores de la secretaría */
            INNER JOIN {$db->getTable('tbl_factores')} f
                    ON f.tbl_secretaria_id = :sec

            /* Registros */
            LEFT JOIN {$db->getTable('tbl_ingreso_informacion')} i
                ON i.tbl_vereda_id = v.id
                AND i.tbl_factor_id = f.id
                AND i.codigo_municipio = :mun

            WHERE v.municipio_id = :mun2

            GROUP BY v.id
            ORDER BY v.nombre_vereda ASC
        ";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            'sec' => $secretariaId,
            'mun' => $codigoMunicipio,
            'mun2'=> $codigoMunicipio
        ]);

        $veredas = $stmt->fetchAll(PDO::FETCH_ASSOC);


        /* -----------------------------------------------------------
         * 3) Asignar color según rangos
         * ----------------------------------------------------------- */
        $colorDefault = Util::getColorNeutroMapa();
        $resultado    = [];

        foreach ($veredas as $v) {

            $valor = floatval($v['valor_factor']);
            $color = $colorDefault;

            foreach ($puntajes as $p) {
                if ($valor >= floatval($p['rango_desde']) &&
                    $valor <= floatval($p['rango_hasta'])) {
                    $color = $p['color'];
                    break;
                }
            }

            $v['color_calculado'] = $color;
            $resultado[] = $v;
        }

        return ['output' => ['valid' => true, 'response' => $resultado]];

    } catch (Exception $e) {
        return Util::error_general("Error mapa inicial veredas: " . $e->getMessage());
    } finally {
        $db->closeConect();
    }
}



   /**
 * MAPA ACTUAL – SUMA DE TODOS LOS FACTORES CON ACTUALIZACIÓN
 * Funciona igual que el mapa inicial, pero usa valor_actual si existe.
 */
public static function calcularColorMapaActualByFactor($rqst)
{
    $codigoMunicipio = intval($rqst['codigo_municipio'] ?? 0);
    $secretariaId    = intval($rqst['secretariaId'] ?? 0);

    if ($codigoMunicipio == 0 || $secretariaId == 0) {
        return Util::error_missing_data();
    }

    $db  = new DbConection();
    $pdo = $db->openConect();

    try {

        /* -----------------------------------------------------------
         * 1) OBTENER RANGOS/PUNTAJES DE LA SECRETARÍA
         * ----------------------------------------------------------- */
        $sqlPuntajes = "
            SELECT rango_desde, rango_hasta, color
            FROM {$db->getTable('tbl_puntajes_secretarias')}
            WHERE tbl_secretaria_id = :sec
              AND tipo_medicion = 'Cantidad'
            ORDER BY rango_desde ASC
        ";

        $stmtP = $pdo->prepare($sqlPuntajes);
        $stmtP->execute(['sec' => $secretariaId]);
        $puntajes = $stmtP->fetchAll(PDO::FETCH_ASSOC);

        if (empty($puntajes)) {
            return Util::error_general("No hay rangos definidos para la secretaría.");
        }

        /* -----------------------------------------------------------
         * 2) CONSULTA PRINCIPAL DEL MAPA ACTUAL
         *    → SUMA valor_actual si existe
         *    → si NO existe: valor_inicial o valor
         *    → para TODOS los factores de la secretaría
         * ----------------------------------------------------------- */

        $sql = "
            SELECT 
                v.id AS vereda_id,
                v.nombre_vereda,
                v.path,
                v.points,
                v.tspan,

                /* SUMA final para pintar */
                SUM(
                    COALESCE(act.valor_actual, base.valor_base, 0)
                ) AS valor_factor

            FROM {$db->getTable('tbl_vereda')} v

            /* Factores de la secretaría */
            INNER JOIN {$db->getTable('tbl_factores')} f
                    ON f.tbl_secretaria_id = :sec

            /* Valores base */
            LEFT JOIN (
                SELECT 
                    i.id AS ingreso_id,
                    i.tbl_vereda_id,
                    COALESCE(i.valor_inicial, i.valor, 0) AS valor_base
                FROM {$db->getTable('tbl_ingreso_informacion')} i
                WHERE i.codigo_municipio = :mun
            ) base ON base.tbl_vereda_id = v.id
                   AND base.ingreso_id IN (
                       SELECT id 
                       FROM {$db->getTable('tbl_ingreso_informacion')}
                       WHERE tbl_factor_id = f.id
                   )

            /* Última actualización */
            LEFT JOIN (
                SELECT 
                    ax.tbl_ingreso_informacion_id AS ingreso_id,
                    ax.valor_actualizacion AS valor_actual
                FROM {$db->getTable('tbl_ingreso_informacion_x_actualizacion')} ax
                INNER JOIN (
                    SELECT 
                        tbl_ingreso_informacion_id,
                        MAX(dtcreate) AS max_dt
                    FROM {$db->getTable('tbl_ingreso_informacion_x_actualizacion')}
                    GROUP BY tbl_ingreso_informacion_id
                ) ult ON ult.tbl_ingreso_informacion_id = ax.tbl_ingreso_informacion_id
                     AND ult.max_dt = ax.dtcreate
            ) act ON act.ingreso_id = base.ingreso_id

            WHERE v.municipio_id = :mun2

            GROUP BY v.id
            ORDER BY v.nombre_vereda ASC
        ";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            'sec'  => $secretariaId,
            'mun'  => $codigoMunicipio,
            'mun2' => $codigoMunicipio
        ]);

        $veredas = $stmt->fetchAll(PDO::FETCH_ASSOC);

        /* -----------------------------------------------------------
         * 3) ASIGNAR COLOR
         * ----------------------------------------------------------- */
        $colorDefault = Util::getColorNeutroMapa();
        $resultado    = [];

        foreach ($veredas as $v) {

            $valor = floatval($v['valor_factor']);
            $color = $colorDefault;

            foreach ($puntajes as $p) {
                if ($valor >= floatval($p['rango_desde']) &&
                    $valor <= floatval($p['rango_hasta'])) {
                    $color = $p['color'];
                    break;
                }
            }

            $v['color_calculado'] = $color;
            $resultado[] = $v;
        }

        return ['output' => ['valid' => true, 'response' => $resultado]];

    } catch (Exception $e) {
        return Util::error_general("Error mapa actual: " . $e->getMessage());
    } finally {
        $db->closeConect();
    }
}

    /**
     * Obtener información administrativa para el mapa
     * De la secretaria administrativa 
     */
    public static function getInformacionAdministrativaColoresMapa($rqst)
    {
        $departamento = isset($rqst['departamento']) ? intval($rqst['departamento']) : 0;

        $db = new DbConection();
        $pdo = $db->openConect();

        $colorDefecto = Util::getColorNeutroMapa();
        $tablaBienes = $db->getTable('tbl_bienes_inmuebles');
        $tablaCiudades = $db->getTable('tbl_ciudades_accion_unificada');

        $q = "
            SELECT 
                c.id,
                c.codigo_departamento,
                c.codigo_muncipio,
                c.path,
                c.name,
                c.class,
                c.d,
                c.latitud,
                c.longitud,
                c.municipio,
                c.porcentaje_participacion,
                c.puntaje,
                c.color,
                c.carpeta_mapa,
                c.carpeta_svg,
                c.nombre_mapa,
                c.mostrar_barrio,
                c.viewbox_svg,
                COUNT(b.codigo_control) AS total_bienes
            FROM $tablaCiudades c
            LEFT JOIN $tablaBienes b ON b.tbl_municipio_id = c.codigo_muncipio
            WHERE c.codigo_departamento = :departamento
            GROUP BY
                c.id,
                c.codigo_departamento,
                c.codigo_muncipio,
                c.path,
                c.name,
                c.class,
                c.d,
                c.latitud,
                c.longitud,
                c.municipio,
                c.porcentaje_participacion,
                c.puntaje,
                c.color,
                c.carpeta_mapa,
                c.carpeta_svg,
                c.nombre_mapa,
                c.mostrar_barrio,
                c.viewbox_svg
        ";

        $stmt = $pdo->prepare($q);
        $stmt->bindParam(':departamento', $departamento, PDO::PARAM_INT);
        $stmt->execute();
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $arr = array();

        if ($result) {
            foreach ($result as $valor) {
                $color = null;
                $totalBienes = floatval($valor['total_bienes']);
                if($totalBienes > 0) {
                    $color = "#62af0a";
                }
                if (empty($color)) {
                    $color = $colorDefecto;
                }
                $valor['color'] = $color;
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
     * Metodo para mostrar mapa, segun los proyectos ingresados
     * Ingreso Información Proyectos Alcaldías con ayuda de Secretarías Gobernación
     * Ingreseo de tbl_ministerios_proyectos en ruta proyectos_alcaldias.php
     */
    public static function getInformacionResumenAlcaldiasBySecretariaColoresMapa($rqst)
    {
        $secretariaId = isset($rqst['secretariaId']) ? intval($rqst['secretariaId']) : 0;
        $departamento = isset($rqst['codigoMunicipio']) ? intval($rqst['codigoMunicipio']) : 0;

        $db = new DbConection();
        $pdo = $db->openConect();

        $tablaCiudades = $db->getTable('tbl_ciudades_accion_unificada');
        $tablaNotificaciones = $db->getTable('tbl_notificaciones_secretaria');

        // Obtenemos todos los municipios del departamento
        // y contaremos total de proyectos y no leídos por separado con subconsultas individuales

        $sql = "
        SELECT 
            c.id,
            c.codigo_departamento,
            c.codigo_muncipio,
            c.path,
            c.name,
            c.class,
            c.d,
            c.latitud,
            c.longitud,
            c.municipio,
            c.porcentaje_participacion,
            c.puntaje,
            c.color,
            c.carpeta_mapa,
            c.carpeta_svg,
            c.nombre_mapa,
            c.mostrar_barrio,
            c.viewbox_svg,

            (
                SELECT COUNT(*) 
                FROM $tablaNotificaciones 
                WHERE codigo_municipio = c.codigo_muncipio 
                AND tbl_secretaria_id = :secretariaId
            ) AS total_proyectos,

            (
                SELECT COUNT(*) 
                FROM $tablaNotificaciones 
                WHERE codigo_municipio = c.codigo_muncipio 
                AND tbl_secretaria_id = :secretariaId 
                AND leido = 'no'
            ) AS proyectos_no_leidos

        FROM $tablaCiudades c

        WHERE c.codigo_departamento = :departamento
    ";

        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':secretariaId', $secretariaId, PDO::PARAM_INT);
        $stmt->bindParam(':departamento', $departamento, PDO::PARAM_INT);
        $stmt->execute();
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $data = [];

        foreach ($result as $fila) {
            $noLeidos = intval($fila['proyectos_no_leidos']);
            $total = intval($fila['total_proyectos']);

            if ($total > 0 && $noLeidos === 0) {
                $fila['color'] = "#62af0a"; // Verde: todos leídos
            } elseif ($noLeidos > 0) {
                $fila['color'] = "#DC143C"; // Rojo: hay sin leer
            } else {
                $fila['color'] = Util::getColorNeutroMapa(); // Neutro: sin proyectos
            }

            $data[] = $fila;
        }

        $db->closeConect();

        if (!empty($data)) {
            return ['output' => ['valid' => true, 'response' => $data]];
        } else {
            return Util::error_no_result();
        }
    }




    // Acá en esta consulta devuelve ese número 
    public static function getDepartamentoByCodigo($rqst)
    {

        $codigo = isset($rqst['codigo']) ? intval($rqst['codigo']) : 0;

        $db = new DbConection();
        $pdo = $db->openConect();

        $qSub = " (SELECT Count(tbVisita.tbl_municipio_id) AS CuentaDetbl_municipio_id FROM " . $db->getTable('tbl_visitas') . " as tbVisita WHERE tbVisita.tbl_municipio_id = tbl_ciudades.codigo_muncipio ) as num_val";

        $q = "SELECT tbl_ciudades.*, $qSub  FROM " . $db->getTable('tbl_ciudades') . " WHERE codigo_departamento = " . $codigo;

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

    /**
     * Metodo para mostrar las visitas y compromisos del gobernador de las ciudades de accion unificada
     */
    public static function getDepartamentoByCodigoCiudadesAccionUnificadaVisitas($rqst)
    {

        $codigo = isset($rqst['codigo']) ? intval($rqst['codigo']) : 0;

        $db = new DbConection();
        $pdo = $db->openConect();

        $qSub = " (SELECT Count(tbVisita.tbl_municipio_id) AS num_val 
        FROM " . $db->getTable('tbl_visitas') . " as tbVisita WHERE tbVisita.tbl_municipio_id = tbl_ciudades_accion_unificada.codigo_muncipio ) as num_val";

        $q = "SELECT tbl_ciudades_accion_unificada.*, $qSub  FROM " . $db->getTable('tbl_ciudades_accion_unificada') . " WHERE codigo_departamento = " . $codigo;

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

    /**
     * Metodo para mostrar las visitas del gobernador de las ciudades de accion unificada Santander
     */
    public static function getDepartamentoByCodigoCiudadesAccionUnificadaVisitasGobernador($rqst)
    {
        $codigo = isset($rqst['codigo']) ? intval($rqst['codigo']) : 0;

        $db = new DbConection();
        $pdo = $db->openConect();

        // solo contar visitas(excluir COMPROMISOS)
        $qSub = " (SELECT Count(tbVisita.tbl_municipio_id) AS num_val 
        FROM " . $db->getTable('tbl_visitas') . " as tbVisita WHERE tbVisita.tbl_municipio_id = tbl_ciudades_accion_unificada.codigo_muncipio 
        AND (tbVisita.tipo_registro = 'Visita' OR tbVisita.tipo_registro = 'visita')) as num_val";

        $q = "SELECT tbl_ciudades_accion_unificada.*, $qSub  
            FROM " . $db->getTable('tbl_ciudades_accion_unificada') . " 
            WHERE codigo_departamento = " . $codigo;

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


    /**
     * Obtiene información de municipios con conteo de visitas del Alcalde
     * Similar a getDepartamentoByCodigoCiudadesAccionUnificadaVisitasGobernador pero para tbl_visitas_alcalde
     * @param array $rqst Array con código de departamento
     * @return array JSON con municipios y conteo de visitas del Alcalde
     */
    public static function getDepartamentoByCodigoCiudadesAccionUnificadaVisitasAlcalde($rqst)
    {
        $codigo = isset($rqst['codigo']) ? intval($rqst['codigo']) : 0;

        $db = new DbConection();
        $pdo = $db->openConect();

        // Contar solo visitas del Alcalde (excluir compromisos)
        $qSub = " (SELECT Count(tbVisita.tbl_municipio_id) AS num_val
        FROM " . $db->getTable('tbl_visitas_alcalde') . " as tbVisita
        WHERE tbVisita.tbl_municipio_id = c.codigo_muncipio
        AND (tbVisita.tipo_registro = 'Visita' OR tbVisita.tipo_registro = 'visita')) as num_val";

        $q = "SELECT c.*, $qSub
            FROM " . $db->getTable('tbl_ciudades_accion_unificada') . " AS c
            WHERE c.codigo_departamento = " . $codigo;

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

    public static function getInformacionParaMapaGestoraSocial($rqst)
    {
        $codigo = isset($rqst['codigo']) ? intval($rqst['codigo']) : 0;

        if ($codigo === 0) {
            return Util::error_missing_data();
        }

        $db = new DbConection();
        $pdo = $db->openConect();

        $query = "
            SELECT 
                tbl_ciudades_accion_unificada.*,  
                SUM(CASE WHEN tbl_gestora.tipo_actividad = 'primera_dama'
                           OR tbl_gestora.tipo_actividad IS NULL
                           OR tbl_gestora.tipo_actividad = ''
                      THEN tbl_gestora.poblacion ELSE 0 END) AS num_val
            FROM 
                " . $db->getTable('tbl_gestora') . " 
            RIGHT JOIN 
                " . $db->getTable('tbl_ciudades_accion_unificada') . "  
            ON 
                tbl_gestora.tbl_municipio_id = tbl_ciudades_accion_unificada.codigo_muncipio 
            WHERE 
                codigo_departamento = :codigo
            GROUP BY 
                tbl_ciudades_accion_unificada.codigo_muncipio";

        try {
            $stmt = $pdo->prepare($query);
            $stmt->bindValue(':codigo', $codigo, PDO::PARAM_INT);
            $stmt->execute();
            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $response = !empty($result)
                ? ['output' => ['valid' => true, 'response' => $result]]
                : Util::error_no_result();
        } catch (PDOException $e) {
            $response = Util::error_general($e->getMessage());
        } finally {
            $db->closeConect();
        }

        return $response;
    }

    /**
     * Obtiene información para el mapa PAE desde la API de ArcGIS
     * Combina datos del mapa local con conteo de sedes desde la API
     *
     * @param array $rqst Parámetros: codigo_departamento, ano (vigencia)
     * @return array Datos del mapa
     */
    public static function getInformacionParaMapaPaeArcgis($rqst)
    {
        $codigo = isset($rqst['codigo_departamento']) ? intval($rqst['codigo_departamento']) : 0;
        $ano = isset($rqst['ano']) ? trim($rqst['ano']) : 'todos';

        if ($codigo === 0) {
            return Util::error_missing_data();
        }

        // Obtener datos de la API de ArcGIS
        $municipiosApi = [];
        try {
            include_once __DIR__ . '/PaeArcgis.php';
            $apiResult = PaeArcgis::getResumenMunicipios(['ano' => $ano]);

            if ($apiResult['output']['valid']) {
                $municipiosApi = $apiResult['output']['response']['municipios'] ?? [];
            }
        } catch (Exception $e) {
            error_log("[Colombia] Error getInformacionParaMapaPaeArcgis: " . $e->getMessage());
        }

        // Obtener datos del mapa desde la BD local
        $db = new DbConection();
        $pdo = $db->openConect();

        $query = "SELECT 
            tbl_ciudades_accion_unificada.municipio,
            tbl_ciudades_accion_unificada.nombre_api_arcgis_pae,
            tbl_ciudades_accion_unificada.d,
            tbl_ciudades_accion_unificada.path,
            tbl_ciudades_accion_unificada.name,
            tbl_ciudades_accion_unificada.class,
            tbl_ciudades_accion_unificada.codigo_muncipio,
            tbl_ciudades_accion_unificada.codigo_departamento
        FROM 
            " . $db->getTable('tbl_ciudades_accion_unificada') . "
        WHERE 
            tbl_ciudades_accion_unificada.codigo_departamento = :codigo
        ORDER BY 
            tbl_ciudades_accion_unificada.municipio";

        try {
            $stmt = $pdo->prepare($query);
            $stmt->bindValue(':codigo', $codigo, PDO::PARAM_INT);
            $stmt->execute();
            $municipios = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Agregar el total de la API a cada municipio
            foreach ($municipios as &$mun) {
                $nombreApi = $mun['nombre_api_arcgis_pae'] ?? '';
                $mun['total'] = isset($municipiosApi[$nombreApi]) ? intval($municipiosApi[$nombreApi]) : 0;
            }

            $response = !empty($municipios)
                ? ['output' => ['valid' => true, 'response' => $municipios]]
                : Util::error_no_result();
        } catch (PDOException $e) {
            $response = Util::error_general($e->getMessage());
        } finally {
            $db->closeConect();
        }

        return $response;
    }


    public static function getInformacionParaMapaPae($rqst)
    {
        $codigo = isset($rqst['codigo_departamento']) ? intval($rqst['codigo_departamento']) : 0;

        if ($codigo === 0) {
            return Util::error_missing_data();
        }

        $db = new DbConection();
        $pdo = $db->openConect();

        $query = " SELECT 
            tbl_pae.tbl_municipio_id,
            tbl_ciudades_accion_unificada.municipio,
            tbl_ciudades_accion_unificada.nombre_api_arcgis_pae,
            tbl_ciudades_accion_unificada.d,
            tbl_ciudades_accion_unificada.path,
            tbl_ciudades_accion_unificada.name,
            tbl_ciudades_accion_unificada.class,
            tbl_ciudades_accion_unificada.codigo_muncipio,
            tbl_ciudades_accion_unificada.codigo_departamento,
            COUNT(CASE WHEN tbl_pae.estado_sede = 'Antiguo_Activo' THEN 1 END) AS total
        FROM 
            " . $db->getTable('tbl_pae') . "
        RIGHT JOIN 
            " . $db->getTable('tbl_ciudades_accion_unificada') . "    
            ON tbl_pae.tbl_municipio_id = tbl_ciudades_accion_unificada.codigo_muncipio
        WHERE 
            tbl_ciudades_accion_unificada.codigo_departamento = :codigo
        GROUP BY 
            tbl_pae.tbl_municipio_id,
            tbl_ciudades_accion_unificada.municipio,
            tbl_ciudades_accion_unificada.nombre_api_arcgis_pae,
            tbl_ciudades_accion_unificada.d,
            tbl_ciudades_accion_unificada.path,
            tbl_ciudades_accion_unificada.name,
            tbl_ciudades_accion_unificada.class,
            tbl_ciudades_accion_unificada.codigo_muncipio,
            tbl_ciudades_accion_unificada.codigo_departamento
        ORDER BY 
            tbl_ciudades_accion_unificada.municipio";

        try {
            $stmt = $pdo->prepare($query);
            $stmt->bindValue(':codigo', $codigo, PDO::PARAM_INT);
            $stmt->execute();
            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $response = !empty($result)
                ? ['output' => ['valid' => true, 'response' => $result]]
                : Util::error_no_result();
        } catch (PDOException $e) {
            $response = Util::error_general($e->getMessage());
        } finally {
            $db->closeConect();
        }

        return $response;
    }

    public static function getInformacionParaMapaGestoraSocialAspas($rqst)
    {

        $codigo = isset($rqst['codigo']) ? intval($rqst['codigo']) : 0;

        $db = new DbConection();
        $pdo = $db->openConect();

        $q = "SELECT tbl_ciudades_accion_unificada.*, SUM(tbl_gestora.poblacion) AS num_val
        FROM " . $db->getTable('tbl_gestora') . " RIGHT JOIN " . $db->getTable('tbl_ciudades_accion_unificada') . "
        ON tbl_gestora.tbl_municipio_id = tbl_ciudades_accion_unificada.codigo_muncipio
           AND tbl_gestora.tipo_actividad = 'aspas'
        WHERE codigo_departamento = :codigo
        GROUP BY tbl_ciudades_accion_unificada.codigo_muncipio";
        try {
            $stmt = $pdo->prepare($q);
            $stmt->bindValue(':codigo', $codigo, PDO::PARAM_INT);
            $stmt->execute();
            $arr = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $arrjson = (!empty($arr))
                ? array('output' => array('valid' => true, 'response' => $arr))
                : Util::error_no_result();
        } catch (PDOException $e) {
            $arrjson = Util::error_general($e->getMessage());
        }

        $db->closeConect();
        return $arrjson;
    }

    public static function getInformacionParaMapaSecretarias($rqst)
    {

        $codigo = isset($rqst['codigo']) ? intval($rqst['codigo']) : 0;

        $db = new DbConection();
        $pdo = $db->openConect();

        $q = "SELECT tbl_ciudades.*,  Sum(tbl_gestora.poblacion) AS num_val
        FROM " . $db->getTable('tbl_gestora') . " RIGHT JOIN " . $db->getTable('tbl_ciudades') . "  ON tbl_gestora.tbl_municipio_id = tbl_ciudades.codigo_muncipio 
        WHERE codigo_departamento = $codigo
        GROUP BY tbl_ciudades.codigo_muncipio";
        try {
            $stmt = $pdo->prepare($q);
            $stmt->execute();
            $arr = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $arrjson = (!empty($arr))
                ? array('output' => array('valid' => true, 'response' => $arr))
                : Util::error_no_result();
        } catch (PDOException $e) {
            $arrjson = Util::error_general($e->getMessage());
        }

        $db->closeConect();
        return $arrjson;
    }

    /**
     * Metodo para listar la informacion de las cuidades para pintar el mapa, de accion unificada
     */
    public static function getInformacionParaMapaAccionUnificada($rqst)
    {
        $codigoMunicipio = isset($rqst['codigo_municipio']) ? intval($rqst['codigo_municipio']) : 0;
        $codigoDepartamento = isset($rqst['codigo']) ? intval($rqst['codigo']) : 0;

        $db = new DbConection();
        $pdo = $db->openConect();

        $baseQuery = "
        SELECT * FROM " . $db->getTable('tbl_ciudades_accion_unificada') . " 
        WHERE 
            codigo_departamento = :codigoDepartamento";

        if ($codigoMunicipio > 0) {
            $baseQuery .= " AND codigo_muncipio = :codigoMunicipio";
        }

        $baseQuery .= " GROUP BY tbl_ciudades_accion_unificada.codigo_muncipio";

        try {
            $stmt = $pdo->prepare($baseQuery);

            // Asignar parámetros a la consulta
            $stmt->bindValue(':codigoDepartamento', $codigoDepartamento, PDO::PARAM_INT);

            // Para mostrar informacion de un municipio en especial
            if ($codigoMunicipio > 0) {
                $stmt->bindValue(':codigoMunicipio', $codigoMunicipio, PDO::PARAM_INT);
            }

            $stmt->execute();
            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Construir respuesta
            $arrjson = !empty($result)
                ? ['output' => ['valid' => true, 'response' => $result]]
                : Util::error_no_result();
        } catch (PDOException $e) {
            $arrjson = Util::error_general($e->getMessage());
        }

        $db->closeConect();
        return $arrjson;
    }
    /**
     * Metodo para obtener la información del municipio con sus veredas.
     */
    public static function getInformacionParaMapaAccionUnificadaMunicipio($rqst)
    {
        $codigoMunicipio = isset($rqst['codigo_municipio']) ? intval($rqst['codigo_municipio']) : 0;
        $codigoDepartamento = isset($rqst['codigo_departamento']) ? intval($rqst['codigo_departamento']) : 0;
        $veredaId = isset($rqst['veredaId']) ? intval($rqst['veredaId']) : 0;

        $db = new DbConection();
        $pdo = $db->openConect();

        $baseQuery = "
        SELECT * FROM " . $db->getTable('tbl_vereda') . " 
        WHERE 
            departamento_id = :codigoDepartamento AND municipio_id = :codigoMunicipio  ";

        if ($veredaId > 0) {
            $baseQuery .= " AND id = :veredaId";
        }

        $baseQuery .= " GROUP BY tbl_vereda.id";

        try {
            $stmt = $pdo->prepare($baseQuery);

            // Asignar parámetros a la consulta
            $stmt->bindValue(':codigoDepartamento', $codigoDepartamento, PDO::PARAM_INT);
            $stmt->bindValue(':codigoMunicipio', $codigoMunicipio, PDO::PARAM_INT);

            // Para mostrar informacion de una vereda en especial
            if ($veredaId > 0) {
                $stmt->bindValue(':veredaId', $veredaId, PDO::PARAM_INT);
            }

            $stmt->execute();
            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Construir respuesta
            $arrjson = !empty($result)
                ? ['output' => ['valid' => true, 'response' => $result]]
                : Util::error_no_result();
        } catch (PDOException $e) {
            $arrjson = Util::error_general($e->getMessage());
        }

        $db->closeConect();
        return $arrjson;
    }

    /**
     * Información de consolidado por municipio de pilar, factor, eje municipios
     * Aqui sacamos los datos dinamicos por pilar y muestra la informacion en los tab que se muestran en municpios 
     */
    public static function consultarConsolidadPilaresFactores($rqst)
    {
        $codigoDepartamento = isset($rqst['codigo_departamento']) ? intval($rqst['codigo_departamento']) : 0;
        $codigoMunicipio = isset($rqst['codigo_municipio']) ? intval($rqst['codigo_municipio']) : 0;
        $pilar = isset($rqst['pilar']) ? intval($rqst['pilar']) : 0;

        if ($codigoDepartamento == 0 || $codigoMunicipio == 0) {
            return Util::error_missing_data();
        }

        $db = new DbConection();

        try {
            $q = "SELECT 
            tbl_ciudades_accion_unificada.*,
            tbl_vereda.codigo_vereda, 
            tbl_vereda.nombre_vereda,
            tbl_ingreso_informacion.valor  as total_cantidad, 
            tbl_ingreso_informacion.longitud, 
            tbl_ingreso_informacion.latitud, 
            tbl_factores.tec_pilar_id,
            tbl_factores.tipo AS factor, 
            tbl_factores.id AS factor_id, 
            tbl_ingreso_informacion.tbl_factor_id AS tbl_factor_id,
            tbl_factores.icono, 
            tbl_factores.tec_area_id AS area_id,
            tbl_factores.tipo_medicion,
            tbl_ingreso_informacion.dtcreate as fecha_ingreso
            FROM " . $db->getTable('tbl_ciudades_accion_unificada') . "
            INNER JOIN " . $db->getTable('tbl_vereda') . " 
                ON tbl_ciudades_accion_unificada.codigo_muncipio = tbl_vereda.municipio_id 
            INNER JOIN " . $db->getTable('tbl_ingreso_informacion') . " 
                ON tbl_vereda.id = tbl_ingreso_informacion.tbl_vereda_id 
            INNER JOIN " . $db->getTable('tbl_factores') . " 
                ON tbl_ingreso_informacion.tbl_factor_id = tbl_factores.id
            WHERE tbl_ciudades_accion_unificada.codigo_departamento = $codigoDepartamento  
                AND tbl_ciudades_accion_unificada.codigo_muncipio = $codigoMunicipio 
                AND tbl_factores.tec_pilar_id = $pilar
                ORDER BY  tbl_vereda.nombre_vereda";

            $consolidados = Util::sb_db_get($q, false);

            // Convertimos los resultados en un array de salida AND tbl_factores.tec_pilar_id = $pilar
            $resultado = [];
            foreach ($consolidados as $cantidad) {
                $resultado[] = $cantidad;
            }

            // Obtener IDs de pilares presentes en response
            $areasIds = array_filter(array_unique(array_column($consolidados, 'area_id')), function ($id) {
                return $id > 0; // Filtrar valores inválidos
            });

            // Obtener información de los pilares
            $tabs = [];
            if (!empty($areasIds)) {
                $qPilares = "SELECT *
                            FROM " . $db->getTable('tbl_area') . " 
                            WHERE id IN (" . implode(',', $areasIds) . ")"; // Solo incluir pilares presentes en response

                $tabs = Util::sb_db_get($qPilares, false);
            }

            $arrjson = [
                'output' => [
                    'valid' => true,
                    'response' => $resultado,
                    'tabs' => $tabs,
                ]
            ];
            return $arrjson;
        } catch (Exception $e) {
            return Util::error_general("Error generando los colores: " . $e->getMessage());
        } finally {
            $db->closeConect();
        }
    }

    /**
     * Información de consolidado por municipio de pilar, factor, eje municipios
     * Aqui aqui se muestra la informacion de los factores y los pilares de los municipios
     * listado_factores_generales.php
     */
    public static function consultarConsolidadPilaresFactoreslistadoGeneral($rqst)
    {
        $codigoDepartamento = isset($rqst['codigo_departamento']) ? intval($rqst['codigo_departamento']) : 0;
        $codigoMunicipio = isset($rqst['codigo_municipio']) ? intval($rqst['codigo_municipio']) : 0;
        $pilar = isset($rqst['pilar']) ? intval($rqst['pilar']) : 0;
        $secretaria = isset($rqst['secretaria']) ? intval($rqst['secretaria']) : 0;

        if ($codigoDepartamento == 0 || $codigoMunicipio == 0) {
            return Util::error_missing_data();
        }

        $db = new DbConection();

        try {
            // Construye la primera parte de la consulta
            $q = "SELECT
                tbl_ciudades_accion_unificada.codigo_muncipio,
                tbl_ciudades_accion_unificada.municipio,
                tbl_factores.tec_pilar_id,
                tbl_factores.tipo AS factor,
                tbl_factores.id AS factor_id,
                tbl_factores.tipo_medicion,
                tbl_factores.icono,
                SUM(tbl_ingreso_informacion.valor) AS total_cantidad
            FROM " . $db->getTable('tbl_ciudades_accion_unificada') . "
            INNER JOIN " . $db->getTable('tbl_vereda') . "
                ON tbl_ciudades_accion_unificada.codigo_muncipio = tbl_vereda.municipio_id
            INNER JOIN " . $db->getTable('tbl_ingreso_informacion') . "
                ON tbl_vereda.id = tbl_ingreso_informacion.tbl_vereda_id
            INNER JOIN " . $db->getTable('tbl_factores') . "
                ON tbl_ingreso_informacion.tbl_factor_id = tbl_factores.id
            WHERE 
                tbl_ciudades_accion_unificada.codigo_departamento = $codigoDepartamento AND
                tbl_ciudades_accion_unificada.codigo_muncipio = $codigoMunicipio";

            // Agrega la condición del pilar solo si no es 10000
            if ($pilar != 10000) {
                $q .= " AND tbl_factores.tec_pilar_id = $pilar";
            }
            // Agrega la condición de secretaria solo si no es 10000
            if ($secretaria != 10000) {
                $q .= " AND tbl_factores.tbl_secretaria_id = $secretaria";
            }

            // Agrega el resto de la consulta
            $q .= " GROUP BY
                tbl_ciudades_accion_unificada.codigo_muncipio,
                tbl_ciudades_accion_unificada.municipio,
                tbl_factores.tec_pilar_id,
                tbl_factores.tipo,
                tbl_factores.id,
                tbl_factores.tipo_medicion,
                tbl_factores.icono
            ORDER BY
                tbl_ciudades_accion_unificada.municipio;";

            $consolidados = Util::sb_db_get($q, false);

            $resultado = array();
            if (is_array($consolidados)) {
                foreach ($consolidados as $valor) {
                    $resultado[] = $valor;
                }
            }
            if (count($resultado) == 0) {
                $arrjson = Util::error_no_result();
            } else {
                $arrjson = array('output' => array('valid' => true, 'response' => $resultado));
            }
            return $arrjson;
        } catch (Exception $e) {
            return Util::error_no_result();
        } finally {
            $db->closeConect();
        }
    }

    public static function consultarConsolidadTodosLosPilaresFactores($rqst)
    {
        $codigoDepartamento = isset($rqst['codigo_departamento']) ? intval($rqst['codigo_departamento']) : 0;
        $codigoMunicipio = isset($rqst['codigo_municipio']) ? intval($rqst['codigo_municipio']) : 0;

        if ($codigoDepartamento == 0 || $codigoMunicipio == 0) {
            return Util::error_missing_data();
        }

        $db = new DbConection();

        try {
            $q = "SELECT 
                tbl_ciudades_accion_unificada.*,
                tbl_vereda.codigo_vereda, 
                tbl_vereda.nombre_vereda,
                tbl_ingreso_informacion.valor AS total_cantidad, 
                tbl_ingreso_informacion.longitud, 
                tbl_ingreso_informacion.latitud, 
                tbl_factores.tec_pilar_id,
                tbl_factores.tipo AS factor, 
                tbl_factores.id AS factor_id, 
                tbl_ingreso_informacion.tbl_factor_id AS tbl_factor_id,
                tbl_factores.icono, 
                tbl_factores.tec_area_id AS area_id,
                tbl_factores.tipo_medicion,
                tbl_ingreso_informacion.dtcreate AS fecha_ingreso
            FROM " . $db->getTable('tbl_ciudades_accion_unificada') . "
            INNER JOIN " . $db->getTable('tbl_vereda') . " 
                ON tbl_ciudades_accion_unificada.codigo_muncipio = tbl_vereda.municipio_id 
            INNER JOIN " . $db->getTable('tbl_ingreso_informacion') . " 
                ON tbl_vereda.id = tbl_ingreso_informacion.tbl_vereda_id 
            INNER JOIN " . $db->getTable('tbl_factores') . " 
                ON tbl_ingreso_informacion.tbl_factor_id = tbl_factores.id
            WHERE 
                tbl_ciudades_accion_unificada.codigo_departamento = $codigoDepartamento  
                AND tbl_ciudades_accion_unificada.codigo_muncipio = $codigoMunicipio 
            ORDER BY tbl_vereda.nombre_vereda";


            $consolidados = Util::sb_db_get($q, false);

            $resultado = [];
            foreach ($consolidados as $cantidad) {
                $resultado[] = $cantidad;
            }

            $areasIds = array_filter(array_unique(array_column($consolidados, 'area_id')), function ($id) {
                return $id > 0;
            });

            $pilares = [];
            if (!empty($areasIds)) {
                $qPilares = "SELECT id, nombre, descripcion, enable, icono 
                             FROM " . $db->getTable('tbl_area') . " 
                             WHERE id IN (" . implode(',', $areasIds) . ")";

                $pilares = Util::sb_db_get($qPilares, false);
            }
            return [
                'output' => [
                    'valid' => true,
                    'response' => $resultado,
                    'tabs' => $pilares,
                ]
            ];
        } catch (Exception $e) {
            return Util::error_general("Error generando los colores: " . $e->getMessage());
        } finally {
            $db->closeConect();
        }
    }

    /**
     * Información de consolidado DEPARTAMENTAL por pilares y factores
     * Sin filtrar por municipio - suma todos los municipios del departamento
     */
    public static function consultarConsolidadPilaresFactoresDepartamental($rqst)
    {
        $codigoDepartamento = isset($rqst['codigo_departamento']) ? intval($rqst['codigo_departamento']) : 0;
        $pilar = isset($rqst['pilar']) ? intval($rqst['pilar']) : 0;
        $codigoTodos = 10000;

        if ($codigoDepartamento == 0) {
            return Util::error_missing_data();
        }

        $db = new DbConection();

        try {
            // Si no es "Todos", filtramos por ese pilar específico
            $wherePilar = ($pilar != $codigoTodos) ? "AND tbl_factores.tec_pilar_id = $pilar" : "";

            $q = "SELECT 
                tbl_factores.tec_pilar_id,
                tbl_factores.id AS tbl_factor_id,
                tbl_factores.tipo AS factor, 
                tbl_factores.icono, 
                tbl_factores.tec_area_id AS area_id,
                tbl_factores.tipo_medicion,
                SUM(tbl_ingreso_informacion.valor) AS total_cantidad
            FROM " . $db->getTable('tbl_ciudades_accion_unificada') . " AS tbl_ciudades_accion_unificada
            INNER JOIN " . $db->getTable('tbl_vereda') . " AS tbl_vereda 
                ON tbl_ciudades_accion_unificada.codigo_muncipio = tbl_vereda.municipio_id 
            INNER JOIN " . $db->getTable('tbl_ingreso_informacion') . " AS tbl_ingreso_informacion 
                ON tbl_vereda.id = tbl_ingreso_informacion.tbl_vereda_id 
            INNER JOIN " . $db->getTable('tbl_factores') . " AS tbl_factores 
                ON tbl_ingreso_informacion.tbl_factor_id = tbl_factores.id
            WHERE tbl_ciudades_accion_unificada.codigo_departamento = $codigoDepartamento
                $wherePilar
            GROUP BY
                tbl_factores.tec_pilar_id,
                tbl_factores.id,
                tbl_factores.tipo,
                tbl_factores.icono,
                tbl_factores.tec_area_id,
                tbl_factores.tipo_medicion
            ORDER BY tbl_factores.tec_pilar_id, tbl_factores.tipo";

            $consolidados = Util::sb_db_get($q, false);

            $resultado = [];
            foreach ($consolidados as $cantidad) {
                $resultado[] = $cantidad;
            }

            // Obtener los area_id únicos de los datos
            $areasIds = array_filter(array_unique(array_column($consolidados, 'area_id')), function ($id) {
                return $id > 0;
            });

            // Obtener información de los pilares basándose en los area_id encontrados
            $tabs = [];
            if (!empty($areasIds)) {
                $qPilares = "SELECT id, nombre, icono, enable 
                            FROM " . $db->getTable('tbl_area') . " 
                            WHERE id IN (" . implode(',', $areasIds) . ")";
                $tabs = Util::sb_db_get($qPilares, false);
            }

            return [
                'output' => [
                    'valid' => true,
                    'response' => $resultado,
                    'tabs' => $tabs,
                ]
            ];
        } catch (Exception $e) {
            return Util::error_general("Error en consultarConsolidadPilaresFactoresDepartamental: " . $e->getMessage());
        } finally {
            $db->closeConect();
        }
    }

    /**
     * Información ACTUAL de consolidado DEPARTAMENTAL por pilares y factores
     * Obtiene los valores más recientes de cada factor en el departamento
     */
    public static function consultarConsolidadPilaresFactoresActualesDepartamental($rqst)
    {
        $codigoDepartamento = isset($rqst['codigo_departamento']) ? intval($rqst['codigo_departamento']) : 0;
        $pilar = isset($rqst['pilar']) ? intval($rqst['pilar']) : 0;
        $codigoTodos = 10000;

        if ($codigoDepartamento == 0) {
            return Util::error_missing_data();
        }

        $db = new DbConection();

        try {
            $pdo = $db->openConect();
            
            $wherePilar = ($pilar != $codigoTodos) ? "AND t_fact.tec_pilar_id = $pilar" : "";

            $q = "
                SELECT 
                    t_fact.tec_pilar_id,
                    t_fact.id AS tbl_factor_id,
                    t_fact.tipo AS factor,
                    t_fact.icono,
                    t_fact.tec_area_id AS area_id,
                    t_fact.tipo_medicion,
                    SUM(
                        t_ingreso.valor - (t_ingreso.valor - COALESCE(t_actual.valor_actualizacion, t_ingreso.valor))
                    ) AS total_cantidad_actual
                FROM " . $db->getTable('tbl_ciudades_accion_unificada') . " AS t_ciu
                INNER JOIN " . $db->getTable('tbl_vereda') . " AS t_v 
                    ON CAST(t_ciu.codigo_muncipio AS CHAR) = CAST(t_v.municipio_id AS CHAR)
                INNER JOIN " . $db->getTable('tbl_ingreso_informacion') . " AS t_ingreso
                    ON t_v.id = t_ingreso.tbl_vereda_id 
                INNER JOIN " . $db->getTable('tbl_factores') . " AS t_fact 
                    ON t_ingreso.tbl_factor_id = t_fact.id
                LEFT JOIN " . $db->getTable('tbl_ingreso_informacion_x_actualizacion') . " AS t_actual
                    ON t_actual.id = (
                        SELECT id 
                        FROM " . $db->getTable('tbl_ingreso_informacion_x_actualizacion') . " 
                        WHERE tbl_ingreso_informacion_id = t_ingreso.id
                        ORDER BY dtcreate DESC, id DESC
                        LIMIT 1
                    )
                WHERE t_ciu.codigo_departamento = $codigoDepartamento
                    $wherePilar
                GROUP BY
                    t_fact.tec_pilar_id,
                    t_fact.id,
                    t_fact.tipo,
                    t_fact.icono,
                    t_fact.tec_area_id,
                    t_fact.tipo_medicion
                ORDER BY t_fact.tec_pilar_id, t_fact.tipo";

            $stmt = $pdo->prepare($q);
            $stmt->execute();
            $resultado = $stmt->fetchAll(PDO::FETCH_ASSOC);

            return [
                'output' => [
                    'valid' => true,
                    'response' => $resultado,
                ]
            ];
        } catch (Exception $e) {
            return Util::error_general("Error en consultarConsolidadPilaresFactoresActualesDepartamental: " . $e->getMessage());
        } finally {
            $db->closeConect();
        }
    }

    /**
     * Consolidado INICIAL por MUNICIPIO – agrupa por ciudad + factor
     */
    public static function consultarConsolidadPilaresFactoresPorMunicipio($rqst)
    {
        $codigoDepartamento = isset($rqst['codigo_departamento']) ? intval($rqst['codigo_departamento']) : 0;
        $pilar = isset($rqst['pilar']) ? intval($rqst['pilar']) : 0;
        $codigoTodos = 10000;

        if ($codigoDepartamento == 0) {
            return Util::error_missing_data();
        }

        $db = new DbConection();

        try {
            $wherePilar = ($pilar != $codigoTodos) ? "AND tbl_factores.tec_pilar_id = $pilar" : "";

            $q = "SELECT 
                tbl_ciudades_accion_unificada.codigo_muncipio,
                tbl_ciudades_accion_unificada.municipio,
                tbl_factores.tec_pilar_id,
                tbl_factores.id AS tbl_factor_id,
                tbl_factores.tipo AS factor, 
                tbl_factores.icono, 
                tbl_factores.tec_area_id AS area_id,
                tbl_factores.tipo_medicion,
                SUM(tbl_ingreso_informacion.valor) AS total_cantidad
            FROM " . $db->getTable('tbl_ciudades_accion_unificada') . " AS tbl_ciudades_accion_unificada
            INNER JOIN " . $db->getTable('tbl_vereda') . " AS tbl_vereda 
                ON tbl_ciudades_accion_unificada.codigo_muncipio = tbl_vereda.municipio_id 
            INNER JOIN " . $db->getTable('tbl_ingreso_informacion') . " AS tbl_ingreso_informacion 
                ON tbl_vereda.id = tbl_ingreso_informacion.tbl_vereda_id 
            INNER JOIN " . $db->getTable('tbl_factores') . " AS tbl_factores 
                ON tbl_ingreso_informacion.tbl_factor_id = tbl_factores.id
            WHERE tbl_ciudades_accion_unificada.codigo_departamento = $codigoDepartamento
                $wherePilar
            GROUP BY
                tbl_ciudades_accion_unificada.codigo_muncipio,
                tbl_ciudades_accion_unificada.municipio,
                tbl_factores.tec_pilar_id,
                tbl_factores.id,
                tbl_factores.tipo,
                tbl_factores.icono,
                tbl_factores.tec_area_id,
                tbl_factores.tipo_medicion
            ORDER BY tbl_ciudades_accion_unificada.municipio, tbl_factores.tipo";

            $consolidados = Util::sb_db_get($q, false);

            $resultado = [];
            foreach ($consolidados as $cantidad) {
                $resultado[] = $cantidad;
            }

            $areasIds = array_filter(array_unique(array_column($consolidados, 'area_id')), function ($id) {
                return $id > 0;
            });

            $tabs = [];
            if (!empty($areasIds)) {
                $qPilares = "SELECT id, nombre, icono, enable 
                            FROM " . $db->getTable('tbl_area') . " 
                            WHERE id IN (" . implode(',', $areasIds) . ")";
                $tabs = Util::sb_db_get($qPilares, false);
            }

            return [
                'output' => [
                    'valid' => true,
                    'response' => $resultado,
                    'tabs' => $tabs,
                ]
            ];
        } catch (Exception $e) {
            return Util::error_general("Error en consultarConsolidadPilaresFactoresPorMunicipio: " . $e->getMessage());
        } finally {
            $db->closeConect();
        }
    }

    /**
     * Consolidado ACTUAL por MUNICIPIO – agrupa por ciudad + factor con actualizaciones
     */
    public static function consultarConsolidadPilaresFactoresActualesPorMunicipio($rqst)
    {
        $codigoDepartamento = isset($rqst['codigo_departamento']) ? intval($rqst['codigo_departamento']) : 0;
        $pilar = isset($rqst['pilar']) ? intval($rqst['pilar']) : 0;
        $codigoTodos = 10000;

        if ($codigoDepartamento == 0) {
            return Util::error_missing_data();
        }

        $db = new DbConection();

        try {
            $pdo = $db->openConect();

            $wherePilar = ($pilar != $codigoTodos) ? "AND t_fact.tec_pilar_id = $pilar" : "";

            $q = "
                SELECT 
                    t_ciu.codigo_muncipio,
                    t_ciu.municipio,
                    t_fact.tec_pilar_id,
                    t_fact.id AS tbl_factor_id,
                    t_fact.tipo AS factor,
                    t_fact.icono,
                    t_fact.tec_area_id AS area_id,
                    t_fact.tipo_medicion,
                    SUM(
                        t_ingreso.valor - (t_ingreso.valor - COALESCE(t_actual.valor_actualizacion, t_ingreso.valor))
                    ) AS total_cantidad_actual
                FROM " . $db->getTable('tbl_ciudades_accion_unificada') . " AS t_ciu
                INNER JOIN " . $db->getTable('tbl_vereda') . " AS t_v 
                    ON CAST(t_ciu.codigo_muncipio AS CHAR) = CAST(t_v.municipio_id AS CHAR)
                INNER JOIN " . $db->getTable('tbl_ingreso_informacion') . " AS t_ingreso
                    ON t_v.id = t_ingreso.tbl_vereda_id 
                INNER JOIN " . $db->getTable('tbl_factores') . " AS t_fact 
                    ON t_ingreso.tbl_factor_id = t_fact.id
                LEFT JOIN " . $db->getTable('tbl_ingreso_informacion_x_actualizacion') . " AS t_actual
                    ON t_actual.id = (
                        SELECT id 
                        FROM " . $db->getTable('tbl_ingreso_informacion_x_actualizacion') . " 
                        WHERE tbl_ingreso_informacion_id = t_ingreso.id
                        ORDER BY dtcreate DESC, id DESC
                        LIMIT 1
                    )
                WHERE t_ciu.codigo_departamento = $codigoDepartamento
                    $wherePilar
                GROUP BY
                    t_ciu.codigo_muncipio,
                    t_ciu.municipio,
                    t_fact.tec_pilar_id,
                    t_fact.id,
                    t_fact.tipo,
                    t_fact.icono,
                    t_fact.tec_area_id,
                    t_fact.tipo_medicion
                ORDER BY t_ciu.municipio, t_fact.tipo";

            $stmt = $pdo->prepare($q);
            $stmt->execute();
            $resultado = $stmt->fetchAll(PDO::FETCH_ASSOC);

            return [
                'output' => [
                    'valid' => true,
                    'response' => $resultado,
                ]
            ];
        } catch (Exception $e) {
            return Util::error_general("Error en consultarConsolidadPilaresFactoresActualesPorMunicipio: " . $e->getMessage());
        } finally {
            $db->closeConect();
        }
    }

    public static function consultarConsolidadPilaresFactoresActuales($rqst)
    {
        $codigoDepartamento = $rqst['codigo_departamento'] ?? 0;
        $codigoMunicipio = $rqst['codigo_municipio'] ?? 0;
        $pilar = $rqst['pilar'] ?? 0;
        $codigoTodos = 10000; 

        if ($codigoDepartamento == 0 || $codigoMunicipio == 0) {
            return Util::error_missing_data();
        }

        $db = new DbConection();

        try {
            $pdo = $db->openConect();
            

            $whereConditions = [
                "CAST(t_ciu.codigo_departamento AS CHAR) = :dep_char",
                "CAST(t_ciu.codigo_muncipio AS CHAR) = :mun_char"
            ];
            $parametros = [
                ':dep_char' => strval($codigoDepartamento),
                ':mun_char' => strval($codigoMunicipio)
            ];
            
            if (intval($pilar) !== $codigoTodos) { 
                $whereConditions[] = "t_fact.tec_pilar_id = :pilar_int";
                $parametros[':pilar_int'] = intval($pilar);
            }
            
            $whereClause = "WHERE " . implode(' AND ', $whereConditions);

            $q = "
                SELECT 
                    t_ingreso.id,
                    t_ingreso.longitud, 
                    t_ingreso.latitud, 
                    t_ingreso.tbl_factor_id AS tbl_factor_id,    

                    (t_ingreso.valor - (t_ingreso.valor - COALESCE(t_actual.valor_actualizacion, t_ingreso.valor))) AS total_cantidad_actual,
                
                    
                    t_fact.tec_pilar_id,
                    t_fact.tipo AS factor, 
                    t_fact.id AS factor_id, 
                    t_fact.icono, 
                    t_fact.tec_area_id AS area_id,
                    t_fact.tipo_medicion,
                    
                    t_ver.codigo_vereda, 
                    t_ver.nombre_vereda,
                    t_actual.dtcreate AS fecha_actualizacion
                    
                FROM " . $db->getTable('tbl_ciudades_accion_unificada') . " t_ciu
                
                LEFT JOIN " . $db->getTable('tbl_vereda') . " t_ver 
                    ON CAST(t_ciu.codigo_muncipio AS CHAR) = CAST(t_ver.municipio_id AS CHAR)
                    
                LEFT JOIN " . $db->getTable('tbl_ingreso_informacion') . " t_ingreso
                    ON t_ver.id = t_ingreso.tbl_vereda_id 
                    
                LEFT JOIN " . $db->getTable('tbl_factores') . " t_fact 
                    ON t_ingreso.tbl_factor_id = t_fact.id
                    
                LEFT JOIN " . $db->getTable('tbl_ingreso_informacion_x_actualizacion') . " t_actual
                    ON t_actual.id = (
                        SELECT id 
                        FROM " . $db->getTable('tbl_ingreso_informacion_x_actualizacion') . " 
                        WHERE tbl_ingreso_informacion_id = t_ingreso.id
                        ORDER BY dtcreate DESC, id DESC
                        LIMIT 1
                    )
                
                " . $whereClause . " 
                
                ORDER BY t_ver.nombre_vereda
            ";
            
            $stmt = $pdo->prepare($q);


            $stmt->execute($parametros);
            
            $consolidados = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            $resultado = [];
            foreach ($consolidados as $cantidad) {
                $resultado[] = $cantidad;
            }

            $areasIds = array_filter(array_unique(array_column($consolidados, 'area_id')), function ($id) {
                return $id > 0;
            });


            $tabs = [];
            if (!empty($areasIds)) {
                $qPilares = "SELECT *
                            FROM " . $db->getTable('tbl_area') . " 
                            WHERE id IN (" . implode(',', $areasIds) . ")"; 

                $tabs = Util::sb_db_get($qPilares, false);
            }

            $arrjson = [
                'output' => [
                    'valid' => true,
                    'response' => $resultado,
                    'tabs' => $tabs,
                ]
            ];
            return $arrjson;


        } catch (Exception $e) {
            return Util::error_general("Error en consultarConsolidadPilaresFactoresActuales: " . $e->getMessage());
        } finally {
            $db->closeConect();
        }
    }    


    /**
     * Información de consolidado por veredas de pilar, factor, eje
     */
    public static function consultarConsolidadPilaresFactoresByVeredaId($rqst)
    {
        $codigoDepartamento = isset($rqst['codigo_departamento']) ? intval($rqst['codigo_departamento']) : 0;
        $codigoMunicipio = isset($rqst['codigo_municipio']) ? intval($rqst['codigo_municipio']) : 0;
        $veredaId = isset($rqst['veredaId']) ? intval($rqst['veredaId']) : 0;
        $pilar = isset($rqst['pilar']) ? intval($rqst['pilar']) : 0;

        if ($codigoDepartamento == 0 || $codigoMunicipio == 0 || $veredaId == 0) {
            return Util::error_missing_data();
        }

        $db = new DbConection();

        try {
            // Información de consolidado de pilar y factor
            $q = "
                SELECT 
                    f.id AS tbl_factor_id,
                    f.tipo AS factor,
                    f.icono,
                    p.nombre AS pilar,
                    v.municipio_id,
                    v.nombre_vereda,
                    v.codigo_vereda,
                    i.longitud,
                    i.latitud,
                    i.valor AS total_cantidad,
                    COALESCE(ua.valor_actualizacion, i.valor) AS total_cantidad_actual,
                    f.puntaje,
                    p.id AS pilar_id,
                    f.tipo_medicion,
                    a.id AS area_id,
                    a.nombre AS area

                FROM " . $db->getTable('tbl_ciudades_accion_unificada') . " c
                INNER JOIN " . $db->getTable('tbl_vereda') . " v
                    ON c.codigo_muncipio = v.municipio_id
                INNER JOIN " . $db->getTable('tbl_ingreso_informacion') . " i
                    ON v.id = i.tbl_vereda_id
                INNER JOIN " . $db->getTable('tbl_factores') . " f
                    ON i.tbl_factor_id = f.id
                INNER JOIN " . $db->getTable('tbl_pilar') . " p
                    ON f.tec_pilar_id = p.id
                INNER JOIN " . $db->getTable('tbl_area') . " a
                    ON f.tec_area_id = a.id

                LEFT JOIN (
                    SELECT 
                        xx.tbl_ingreso_informacion_id,
                        xx.valor_actualizacion
                    FROM " . $db->getTable('tbl_ingreso_informacion_x_actualizacion') . " xx
                    INNER JOIN (
                        SELECT tbl_ingreso_informacion_id, MAX(dtcreate) AS max_fecha
                        FROM " . $db->getTable('tbl_ingreso_informacion_x_actualizacion') . "
                        GROUP BY tbl_ingreso_informacion_id
                    ) ult
                    ON xx.tbl_ingreso_informacion_id = ult.tbl_ingreso_informacion_id
                    AND xx.dtcreate = ult.max_fecha
                ) AS ua
                ON ua.tbl_ingreso_informacion_id = i.id

                WHERE v.municipio_id = $codigoMunicipio
                AND v.departamento_id = $codigoDepartamento
                AND p.id = $pilar
                AND v.id = $veredaId
                AND i.valor > 0

                ORDER BY f.tec_area_id, p.id
                ";

            $consolidados = Util::sb_db_get($q, false);

            // Convertir resultados a array de salida
            $resultado = [];
            foreach ($consolidados as $cantidad) {
                $resultado[] = $cantidad;
            }

            // Obtener IDs de pilares presentes en response
            $areasIds = array_filter(array_unique(array_column($consolidados, 'area_id')), function ($id) {
                return $id > 0; // Filtrar valores inválidos
            });

            // Obtener información de los pilares
            $pilares = [];
            if (!empty($areasIds)) {
                $qPilares = "SELECT id, nombre, descripcion, enable, icono 
                             FROM " . $db->getTable('tbl_area') . " 
                             WHERE id IN (" . implode(',', $areasIds) . ")"; // Solo incluir pilares presentes en response

                $pilares = Util::sb_db_get($qPilares, false);
            }

            $arrjson = [
                'output' => [
                    'valid' => true,
                    'response' => $resultado,
                    'pilares' => $pilares
                ]
            ];

            return $arrjson;
        } catch (Exception $e) {
            return Util::error_general("Error generando los colores: " . $e->getMessage());
        } finally {
            $db->closeConect();
        }
    }    

    /**
     * Funcion para calcular el color por todos los pilares en departamentos
     */
    public static function calcularColorDelDepartamentoTodosLosPilares($rqst)
    {
        $codigoDepartamento = isset($rqst['codigo_departamento']) ? intval($rqst['codigo_departamento']) : Util::getDepartamentoPrincipal();

        if ($codigoDepartamento == 0) {
            return Util::error_missing_data();
        }

        $db = new DbConection();

        try {
            $puntajes = Util::sb_db_get("SELECT * FROM " . $db->getTable('tbl_puntajes'), false);
            $colorDefecto = Util::getColorNeutroMapa();

            $q = "SELECT 
                    tbl_factores.tec_pilar_id AS pilar_id,
                    COALESCE(SUM(tbl_factores.puntaje), 0) AS cantidad,
                    tbl_ciudades_accion_unificada.*
                FROM 
                    " . $db->getTable('tbl_ciudades_accion_unificada') . " AS tbl_ciudades_accion_unificada
                LEFT JOIN 
                    " . $db->getTable('tbl_ingreso_informacion') . " AS tbl_ingreso_informacion 
                    ON tbl_ciudades_accion_unificada.codigo_muncipio = tbl_ingreso_informacion.codigo_municipio
                LEFT JOIN 
                    " . $db->getTable('tbl_factores') . " AS tbl_factores 
                    ON tbl_ingreso_informacion.tbl_factor_id = tbl_factores.id 
                WHERE
                    tbl_ciudades_accion_unificada.codigo_departamento =  $codigoDepartamento
                GROUP BY
                    tbl_ciudades_accion_unificada.id, 
                    tbl_ciudades_accion_unificada.path, 
                    tbl_ciudades_accion_unificada.name, 
                    tbl_ciudades_accion_unificada.class, 
                    tbl_ciudades_accion_unificada.d, 
                    tbl_ciudades_accion_unificada.codigo_departamento, 
                    tbl_factores.tec_pilar_id
                ORDER BY
                    cantidad ASC";
            $municipios = Util::sb_db_get($q, false);
            $resultado = [];

            foreach ($municipios as $municipio) {
                $cantidad = $municipio['cantidad'];
                $color = $colorDefecto;

                foreach ($puntajes as $puntaje) {
                    if ($cantidad >= $puntaje['rango_desde'] && $cantidad <= $puntaje['rango_hasta']) {
                        $color = $puntaje['color'];
                        break;
                    }
                }

                $municipio['color_calculado'] = $color;
                $resultado[] = $municipio;
            }

            return ['output' => ['valid' => true, 'response' => $resultado]];
        } catch (Exception $e) {
            return Util::error_general("Error generando los colores: " . $e->getMessage());
        } finally {
            $db->closeConect();
        }
    }




    /**
     * Información de consolidado por veredas de pilar
     */
    public static function calcularColorDelDepartamentoByPilarId($rqst)
    {
        $codigoDepartamento = isset($rqst['codigo_departamento']) ? intval($rqst['codigo_departamento']) : Util::getDepartamentoPrincipal();
        $pilarId = isset($rqst['pilarId']) ? intval($rqst['pilarId']) : 0;
        $colores = isset($rqst['colores']) ? ($rqst['colores']) : 'no';

        if ($codigoDepartamento == 0 || $pilarId == 0) {
            return Util::error_missing_data();
        }

        $db = new DbConection();

        try {

            $puntajes = Util::sb_db_get("SELECT * FROM " . $db->getTable('tbl_puntajes'), false);

            // Información de las cantidades actuales por Pilar Id


            $q = "SELECT 
                    tbl_factores.tec_pilar_id AS pilar_id,
                    COALESCE(SUM(tbl_factores.puntaje), 0) AS cantidad,
                    tbl_ciudades_accion_unificada.*
                FROM 
                    " . $db->getTable('tbl_ciudades_accion_unificada') . " AS tbl_ciudades_accion_unificada
                LEFT JOIN 
                    " . $db->getTable('tbl_ingreso_informacion') . " AS tbl_ingreso_informacion 
                    ON tbl_ciudades_accion_unificada.codigo_muncipio = tbl_ingreso_informacion.codigo_municipio
                LEFT JOIN 
                    " . $db->getTable('tbl_factores') . " AS tbl_factores 
                    ON tbl_ingreso_informacion.tbl_factor_id = tbl_factores.id 
                    AND tbl_factores.tec_pilar_id = $pilarId
                WHERE
                    tbl_ciudades_accion_unificada.codigo_departamento =  $codigoDepartamento
                GROUP BY
                    tbl_ciudades_accion_unificada.id, 
                    tbl_ciudades_accion_unificada.path, 
                    tbl_ciudades_accion_unificada.name, 
                    tbl_ciudades_accion_unificada.class, 
                    tbl_ciudades_accion_unificada.d, 
                    tbl_ciudades_accion_unificada.codigo_departamento, 
                    tbl_factores.tec_pilar_id
                ORDER BY
                    cantidad ASC";
            $municipios = Util::sb_db_get($q, false);

            // Inicializar color neutro por defecto
            $colorDefecto = Util::getColorNeutroMapa();

            // Array para los resultados finales
            $resultado = [];
            // Inicializar un array para contar colores
            $colorCount = [];

            foreach ($municipios as $municipio) {
                // Calcular el color basado en la cantidad y los puntajes
                $color = $colorDefecto;
                foreach ($puntajes as $puntaje) {
                    if ($municipio['pilar_id'] > 0 && $municipio['cantidad'] >= $puntaje['rango_desde'] && $municipio['cantidad'] <= $puntaje['rango_hasta']) {
                        $color = $puntaje['color'];
                        break;
                    }
                }

                $municipio['color_calculado'] = $color;
                $resultado[] = $municipio;

                if ($color != Util::getColorNeutroMapa()) {
                    if (!isset($colorCount[$color])) {
                        $colorCount[$color] = 0;
                    }
                    $colorCount[$color]++;
                }
            }

            // Retornar la respuesta en formato JSON
            if ($colores == 'no') {
                $arrjson = ['output' => ['valid' => true, 'response' => $resultado]];
            } else {
                $arrjson = ['output' => ['valid' => true, 'cantidad_colores' => $colorCount]];
            }
            return $arrjson;
        } catch (Exception $e) {


            return Util::error_general("Error generando los colores: " . $e->getMessage());
        } finally {
            $db->closeConect();
        }
    }

    /**
     * Información de consolidado por veredas de pilar, factor, eje
     * Version Mejorada ya que se implementa diferente, ya que se obtiene el mayor de la vereda de cada municipio
     */
    public static function calcularColorDelDepartamentoByPilarIdMALAAA($rqst)
    {
        $codigoDepartamento = isset($rqst['codigo_departamento']) ? intval($rqst['codigo_departamento']) : 0;
        $pilarId = isset($rqst['pilarId']) ? intval($rqst['pilarId']) : 0;

        if ($codigoDepartamento == 0 || $pilarId == 0) {
            return Util::error_missing_data();
        }

        $db = new DbConection();

        try {
            // Obtener todos los municipios
            $municipios = Util::sb_db_get("SELECT * FROM " . $db->getTable('tbl_ciudades_accion_unificada') . " WHERE codigo_departamento = $codigoDepartamento", false);

            // Consultamos la configuración de puntaje
            $puntajes = Util::sb_db_get("SELECT * FROM " . $db->getTable('tbl_puntajes'), false);

            // Información de Configuración
            $configuracionAplicacion = Util::getInformacionConfiguracion();
            if (!empty($configuracionAplicacion) && isset($configuracionAplicacion[0]['tipo_configuracion_colores'])) {
                $tipo_configuracion_colores = $configuracionAplicacion[0]['tipo_configuracion_colores'];
            } else {
                $tipo_configuracion_colores = 'Rango';
            }

            // Información de las cantidades actuales por Pilar Id y todos los campos de tbl_vereda
            $q = "SELECT 
                    tbl_ciudades_accion_unificada.*,
                    tbl_factores.tec_area_id AS tec_area_id,
                    COALESCE(tbl_pilar.id, 0) AS pilar_id,
                    tbl_factores.tipo AS factor,
                    tbl_factores.icono,
                    COALESCE(tbl_pilar.nombre, 'Sin Pilar') AS pilar,
                    SUM(tbl_ingreso_informacion.valor) AS cantidad
                FROM 
                    " . $db->getTable('tbl_vereda') . " tbl_vereda
                INNER JOIN 
                    " . $db->getTable('tbl_ciudades_accion_unificada') . " tbl_ciudades_accion_unificada 
                    ON tbl_vereda.municipio_id = tbl_ciudades_accion_unificada.codigo_muncipio
                INNER JOIN 
                    " . $db->getTable('tbl_ingreso_informacion') . " tbl_ingreso_informacion 
                    ON tbl_vereda.id = tbl_ingreso_informacion.tbl_vereda_id 
                    AND tbl_ingreso_informacion.codigo_departamento = $codigoDepartamento 
                INNER JOIN 
                    " . $db->getTable('tbl_factores') . " tbl_factores 
                    ON tbl_ingreso_informacion.tbl_factor_id = tbl_factores.id
                INNER JOIN 
                    " . $db->getTable('tbl_pilar') . " tbl_pilar 
                    ON tbl_factores.tec_pilar_id = tbl_pilar.id
                WHERE  
                    tbl_vereda.departamento_id = $codigoDepartamento 
                    AND tbl_pilar.id = $pilarId 
                GROUP BY 
                    tbl_ciudades_accion_unificada.codigo_muncipio, 
                    tbl_factores.tec_area_id, 
                    tbl_pilar.id, 
                    tbl_pilar.nombre
                ORDER BY 
                    tbl_ciudades_accion_unificada.codigo_muncipio, 
                    tbl_factores.tec_area_id, 
                    tbl_pilar.id";

            $cantidades = Util::sb_db_get($q, false);

            // Inicializar color neutro por defecto
            $colorDefecto = Util::getColorNeutroMapa();

            // Verificar si $puntajes tiene datos o está vacío
            $puntajesVacios = isset($puntajes['output']['response']['code']) && $puntajes['output']['response']['code'] == 104;
            $puntajesValidos = !$puntajesVacios && is_array($puntajes);

            // Convertir cantidades en un array asociativo para acceso rápido por código de municipio
            $cantidadesPorMunicipio = [];
            foreach ($cantidades as $valueDataCantidad) {

                // Información de la cantidad maxima que tiene el municipio
                if ($tipo_configuracion_colores == 'Rango') {
                    $qCantMaximaMuncipio = "
                    SELECT 
                        COALESCE(ingreso.valor, 0) AS cantidad
                    FROM " . $db->getTable('tbl_ingreso_informacion') . " ingreso
                    LEFT JOIN " . $db->getTable('tbl_vereda') . " vereda 
                        ON vereda.id = ingreso.tbl_vereda_id  
                    WHERE ingreso.codigo_departamento = " . (int) $codigoDepartamento . "  
                        AND ingreso.codigo_municipio = " . (int) $valueDataCantidad['codigo_muncipio'] . "  
                        AND vereda.municipio_id = " . (int) $valueDataCantidad['codigo_muncipio'] . "  
                        AND vereda.departamento_id = " . (int) $codigoDepartamento . "  
                    ORDER BY ingreso.valor DESC  
                    LIMIT 1";
                }

                if ($tipo_configuracion_colores == 'Puntaje') {

                    $qCantMaximaMuncipio = "
                    SELECT 
                        SUM(total_puntaje_pilar) AS cantidad
                    FROM (
                        SELECT 
                            i.id, 
                            i.codigo_departamento, 
                            i.codigo_municipio, 
                            i.tbl_vereda_id, 
                            f.tbl_eje_id, 
                            f.tec_area_id, 
                            f.tec_pilar_id, 
                            SUM(f.puntaje) AS total_puntaje_pilar
                        FROM 
                            " . $db->getTable('tbl_ingreso_informacion') . " i
                        LEFT JOIN 
                            " . $db->getTable('tbl_factores') . " f 
                            ON i.tbl_factor_id = f.id
                        GROUP BY 
                            i.id, 
                            i.codigo_departamento, 
                            i.codigo_municipio, 
                            i.tbl_vereda_id, 
                            f.tbl_eje_id, 
                            f.tec_area_id, 
                            f.tec_pilar_id
                    ) AS subquery";
                }


                $cantidadMaxima = Util::sb_db_get($qCantMaximaMuncipio, false);

                $valueDataCantidad['cantidad'] = isset($cantidadMaxima[0]['cantidad']) ? (int) $cantidadMaxima[0]['cantidad'] : 0;

                $cantidadesPorMunicipio[$valueDataCantidad['codigo_muncipio']] = $valueDataCantidad;
            }

            // Array para los resultados finales
            $resultado = [];

            // Recorrer todos los municipios y combinar con las cantidades de la consulta $q
            foreach ($municipios as $municipio) {
                $codigoMunicipio = $municipio['codigo_muncipio'];

                // Si el municipio tiene datos en las cantidades, usar esos datos; si no, asignar valores predeterminados
                if (isset($cantidadesPorMunicipio[$codigoMunicipio])) {
                    $veredaData = $cantidadesPorMunicipio[$codigoMunicipio];
                } else {
                    // Valores predeterminados para municipios que no tienen datos en $q
                    $veredaData = $municipio;
                    $veredaData['tec_area_id'] = null;
                    $veredaData['pilar_id'] = 0;
                    $veredaData['factor'] = null;
                    $veredaData['icono'] = null;
                    $veredaData['pilar'] = 'Sin Pilar';
                    $veredaData['cantidad'] = 0;
                }

                // Calcular el color basado en la cantidad y los puntajes
                $color = $colorDefecto;
                if ($puntajesValidos) {
                    foreach ($puntajes as $puntaje) {
                        if ($veredaData['cantidad'] >= $puntaje['rango_desde'] && $veredaData['cantidad'] <= $puntaje['rango_hasta']) {
                            $color = $puntaje['color'];
                            break;
                        }
                    }
                }

                $veredaData['color_calculado'] = $color;
                $resultado[] = $veredaData;
            }

            // Retornar la respuesta en formato JSON
            $arrjson = ['output' => ['valid' => true, 'response' => $resultado]];
            return $arrjson;
        } catch (Exception $e) {
            return Util::error_general("Error generando los colores: " . $e->getMessage());
        } finally {
            $db->closeConect();
        }
    }
    /**
     * Metodo para calcular los colores de todas las veredes que tiene una vereda, por TODO LOS PILARES
     * Verifica la información de todas sus veredas y calcula cada color de todas las veredas
     */
    public static function consultarConsolidadTodosLosPilaresFactoresByVeredaId($rqst)
    {
        $codigoDepartamento = isset($rqst['codigo_departamento']) ? intval($rqst['codigo_departamento']) : 0;
        $codigoMunicipio = isset($rqst['codigo_municipio']) ? intval($rqst['codigo_municipio']) : 0;
        $veredaId = isset($rqst['veredaId']) ? intval($rqst['veredaId']) : 0;

        if ($codigoDepartamento == 0 || $codigoMunicipio == 0 || $veredaId == 0) {
            return Util::error_missing_data();
        }

        $db = new DbConection();
        $pdo = $db->openConect(); 

        try {
            $tbl_ingreso = $db->getTable('tbl_ingreso_informacion');
            $tbl_factores = $db->getTable('tbl_factores');
            $tbl_pilar = $db->getTable('tbl_pilar');
            $tbl_vereda = $db->getTable('tbl_vereda');
            $tbl_area = $db->getTable('tbl_area');
            $tbl_actualizacion = $db->getTable('tbl_ingreso_informacion_x_actualizacion');

            $q_base = "SELECT 
                t_factores.id as tbl_factor_id,
                t_ingreso.id AS debug_ingreso_id,   
                t_vereda.id AS debug_vereda_id,     
                t_factores.tipo AS factor, 
                t_factores.icono, 
                t_pilar.nombre as pilar, 
                t_vereda.municipio_id, 
                t_vereda.nombre_vereda, 
                t_vereda.codigo_vereda, 
                t_ingreso.longitud, 
                t_ingreso.latitud, 
                t_ingreso.valor as total_cantidad,  
                t_factores.puntaje, 
                t_pilar.id as pilar_id, 
                t_factores.tipo_medicion,
                t_area.id as area_id, 
                t_area.nombre as area
            FROM {$tbl_ingreso} t_ingreso
            INNER JOIN {$tbl_vereda} t_vereda 
                ON t_ingreso.tbl_vereda_id = t_vereda.id 
            INNER JOIN " . $db->getTable('tbl_ciudades_accion_unificada') . " t_ciu
                ON t_ciu.codigo_muncipio = t_vereda.municipio_id 
            INNER JOIN {$tbl_factores} t_factores 
                ON t_ingreso.tbl_factor_id = t_factores.id 
            INNER JOIN {$tbl_pilar} t_pilar 
                ON t_factores.tec_pilar_id = t_pilar.id 
            INNER JOIN {$tbl_area} t_area 
                ON t_factores.tec_area_id = t_area.id
            WHERE t_vereda.municipio_id = :municipioId  
            AND t_vereda.departamento_id = :departamentoId 
            AND t_vereda.id = :veredaId
            AND t_ingreso.valor > 0
            ORDER BY t_factores.tec_area_id, t_pilar.id";

            $params = [
                ':municipioId' => $codigoMunicipio,
                ':departamentoId' => $codigoDepartamento,
                ':veredaId' => $veredaId
            ];

            $stmt = $pdo->prepare($q_base);
            $stmt->execute($params);
            $consolidados = $stmt->fetchAll(PDO::FETCH_ASSOC);


            $ingresoIds = array_filter(array_column($consolidados, 'debug_ingreso_id'));
            $mapaActualizaciones = [];

            if (!empty($ingresoIds)) {
                
                $idList = implode(',', array_map('intval', $ingresoIds)); 
                
                $q_actualizacion = "
                    SELECT 
                        t1.tbl_ingreso_informacion_id, 
                        t1.valor_actualizacion
                    FROM {$tbl_actualizacion} t1
                    INNER JOIN (
                        SELECT tbl_ingreso_informacion_id, MAX(dtcreate) as max_dtcreate
                        FROM {$tbl_actualizacion}
                        GROUP BY tbl_ingreso_informacion_id
                    ) AS t2 
                    ON t1.tbl_ingreso_informacion_id = t2.tbl_ingreso_informacion_id 
                    AND t1.dtcreate = t2.max_dtcreate
                    WHERE t1.tbl_ingreso_informacion_id IN ({$idList}) 
                ";
                
                $stmt_actualizacion = $pdo->query($q_actualizacion);
                $actualizaciones = $stmt_actualizacion->fetchAll(PDO::FETCH_ASSOC);
                
                foreach ($actualizaciones as $act) {
                    $mapaActualizaciones[$act['tbl_ingreso_informacion_id']] = $act['valor_actualizacion'];
                }
            }

            $resultado = [];
            foreach ($consolidados as $item) {
                $ingresoId = $item['debug_ingreso_id'] ?? null;
                $cantidadInicial = floatval($item['total_cantidad'] ?? 0);
                
                $valorActualizado = $mapaActualizaciones[$ingresoId] ?? null;
                $cantidadFinal = $cantidadInicial; 
                
                if ($valorActualizado !== null && floatval($valorActualizado) > 0) {
                    $cantidadFinal = floatval($valorActualizado);
                }

                $item['debug_valor_actualizacion_crudo'] = $valorActualizado;
                $item['total_cantidad_actual'] = $cantidadFinal;             
                
                $resultado[] = $item;
            }

            $areasIds = array_filter(array_unique(array_column($resultado, 'area_id')), fn($id) => $id > 0);
            $pilares = [];

            if (!empty($areasIds)) {
                $idListPilar = implode(',', array_map('intval', $areasIds));

                $qPilares = "SELECT id, nombre, descripcion, enable, icono 
                             FROM " . $db->getTable('tbl_area') . " 
                             WHERE id IN ({$idListPilar})"; 

                $stmt = $pdo->query($qPilares);
                $pilares = $stmt->fetchAll(PDO::FETCH_ASSOC);
            }

            return [
                'output' => [
                    'valid' => true,
                    'response' => $resultado,
                    'pilares' => $pilares
                ]
            ];

        } catch (Exception $e) {
            return Util::error_general("Error generando los colores: " . $e->getMessage());

        } finally {
            $db->closeConect();
        } 
    }

    /**
     * Metodo para calcular los colores de todas las veredes que tiene un Municipio, por TODO LOS PILARES
     * Verifica la información de todas sus veredas y calcula cada color de todas las veredas
     */
    public static function calcularColorPorMunicipioTodosPilares($rqst)
    {
        $codigoDepartamento = isset($rqst['codigo_departamento']) ? intval($rqst['codigo_departamento']) : 0;
        $codigoMunicipio = isset($rqst['codigo_municipio']) ? intval($rqst['codigo_municipio']) : 0;

        if ($codigoDepartamento == 0 || $codigoMunicipio == 0) {
            return Util::error_missing_data();
        }

        $db = new DbConection();

        try {
            $puntajes = Util::sb_db_get("SELECT * FROM " . $db->getTable('tbl_puntajes'), false);

            $qIngresoInformacionCantidad = "
            SELECT 
                tbl_ciudades_accion_unificada.id, 
                tbl_ciudades_accion_unificada.codigo_departamento, 
                tbl_ciudades_accion_unificada.path, 
                tbl_ciudades_accion_unificada.name, 
                tbl_ciudades_accion_unificada.class, 
                tbl_ciudades_accion_unificada.d, 
                tbl_vereda.*, 
                SUM(COALESCE(tbl_factores.puntaje, 0)) AS cantidad
            FROM 
                " . $db->getTable('tbl_ciudades_accion_unificada') . " 
            LEFT JOIN 
                " . $db->getTable('tbl_vereda') . " ON tbl_ciudades_accion_unificada.codigo_muncipio = tbl_vereda.municipio_id 
            LEFT JOIN 
                " . $db->getTable('tbl_ingreso_informacion') . " ON tbl_vereda.id = tbl_ingreso_informacion.tbl_vereda_id 
            LEFT JOIN 
                " . $db->getTable('tbl_factores') . " ON tbl_ingreso_informacion.tbl_factor_id = tbl_factores.id
            WHERE 
                tbl_ciudades_accion_unificada.codigo_departamento = $codigoDepartamento  
                AND tbl_ciudades_accion_unificada.codigo_muncipio = $codigoMunicipio 
            GROUP BY 
                tbl_vereda.id, 
                tbl_ciudades_accion_unificada.id, 
                tbl_ciudades_accion_unificada.codigo_departamento, 
                tbl_ciudades_accion_unificada.path, 
                tbl_ciudades_accion_unificada.name, 
                tbl_ciudades_accion_unificada.class, 
                tbl_ciudades_accion_unificada.d, 
                tbl_vereda.nombre_vereda
            ORDER BY 
                tbl_vereda.nombre_vereda, cantidad ASC
        ";

            $cantidades = Util::sb_db_get($qIngresoInformacionCantidad, false);

            $colorDefecto = Util::getColorNeutroMapa();
            $puntajesVacios = isset($puntajes['output']['response']['code']) && $puntajes['output']['response']['code'] == 104;
            $puntajesValidos = !$puntajesVacios && is_array($puntajes);

            $resultado = [];
            foreach ($cantidades as $cantidad) {
                $color = $colorDefecto;
                $valorCantidad = isset($cantidad['cantidad']) ? (int)$cantidad['cantidad'] : 0;

                if ($puntajesValidos) {
                    foreach ($puntajes as $puntaje) {
                        if ($valorCantidad >= $puntaje['rango_desde'] && $valorCantidad <= $puntaje['rango_hasta']) {
                            $color = $puntaje['color'];
                            break;
                        }
                    }
                }

                $veredaData = $cantidad;
                $veredaData['color_calculado'] = $color;
                $resultado[] = $veredaData;
            }

            $arrjson = array('output' => array('valid' => true, 'response' => $resultado));
            return $arrjson;
        } catch (Exception $e) {
            return Util::error_general("Error generando los colores (todos los pilares): " . $e->getMessage());
        } finally {
            $db->closeConect();
        }
    }

/**
 * Mapa INICIAL – Por PILAR usando valor_inicial → valor
 * Idéntico al método ACTUAL, pero usando valor_inicial.
 */
public static function calcularColorInicialPorMunicipioByPilarId($rqst)
{
    $codigoDepartamento = isset($rqst['codigo_departamento']) ? intval($rqst['codigo_departamento']) : 0;
        $codigoMunicipio = isset($rqst['codigo_municipio']) ? intval($rqst['codigo_municipio']) : 0;
        $pilarId = isset($rqst['pilar']) ? intval($rqst['pilar']) : 0;
        $veredaId = isset($rqst['veredaId']) ? intval($rqst['veredaId']) : 0;

        if ($codigoDepartamento == 0 || $codigoMunicipio == 0 || $pilarId == 0) {
            return Util::error_missing_data();
        }

        $db = new DbConection();

        try {
            $puntajes = Util::sb_db_get("SELECT * FROM " . $db->getTable('tbl_puntajes'), false);

            $qIngresoInformacionCantidad = "
                SELECT 
                    c.id,
                    c.codigo_departamento,
                    c.path,
                    c.name,
                    c.class,
                    c.d,
                    v.*,
                    f.tec_pilar_id AS pilar_id,
                    SUM(i.valor_inicial) AS cantidad
                FROM " . $db->getTable('tbl_ciudades_accion_unificada') . " c
                LEFT JOIN " . $db->getTable('tbl_vereda') . " v
                    ON c.codigo_muncipio = v.municipio_id
                LEFT JOIN " . $db->getTable('tbl_ingreso_informacion') . " i
                    ON v.id = i.tbl_vereda_id
                LEFT JOIN " . $db->getTable('tbl_factores') . " f
                    ON i.tbl_factor_id = f.id
                    AND f.tec_pilar_id = $pilarId

                WHERE 
                    c.codigo_departamento = $codigoDepartamento
                    AND c.codigo_muncipio = $codigoMunicipio

                GROUP BY 
                    v.id,
                    c.id,
                    c.codigo_departamento,
                    c.path,
                    c.name,
                    c.class,
                    c.d,
                    v.nombre_vereda,
                    f.tec_pilar_id

                ORDER BY 
                    v.nombre_vereda, cantidad ASC
            ";


            $cantidades = Util::sb_db_get($qIngresoInformacionCantidad, false);

            if (isset($cantidades['output']['response']['code']) && $cantidades['output']['response']['code'] == 104 && $veredaId > 0) {
                $qVeredaInfo = "SELECT * FROM " . $db->getTable('tbl_vereda') . " WHERE departamento_id = $codigoDepartamento AND municipio_id = $codigoMunicipio   AND id = $veredaId";
                $cantidades = Util::sb_db_get($qVeredaInfo, false);
            }

            $colorDefecto = Util::getColorNeutroMapa();


            $puntajesVacios = isset($puntajes['output']['response']['code']) && $puntajes['output']['response']['code'] == 104;
            $puntajesValidos = !$puntajesVacios && is_array($puntajes);

            $resultado = [];
            foreach ($cantidades as $cantidad) {

                $color = $colorDefecto;

                $valorCantidad = isset($cantidad['cantidad']) ? (int)$cantidad['cantidad'] : 0;
                //$valorCantidad = isset($cantidad['cantidad']) ? intval(round($cantidad['cantidad'])) : 0;


                foreach ($puntajes as $puntaje) {

                    $color = $colorDefecto;

                    if ($puntajesValidos) {
                        if ($cantidad['pilar_id'] > 0 && $valorCantidad >= $puntaje['rango_desde'] && $valorCantidad <= $puntaje['rango_hasta']) {
                            $color = $puntaje['color'];
                            break;
                        }
                    }
                }
                $veredaData = $cantidad;
                $veredaData['color_calculado'] = $color;
                $resultado[] = $veredaData;
            }
            $arrjson = array('output' => array('valid' => true, 'response' => $resultado));
            return $arrjson;
        } catch (Exception $e) {
            return Util::error_general("Error generando los colores: " . $e->getMessage());
        } finally {
            $db->closeConect();
        }
    }


/**
     * Metodo para calcular los colores de todas las veredes que tiene un Municipio, según las visitas del alcalde
     * Verifica la información de todas sus veredas y calcula cada color de todas las veredas
     * ALCALDIA
     */
    public static function calcularColoresDeVisitasPorveredasDeUnaAlcaldia($rqst)
    {
        $codigoDepartamento = isset($rqst['codigo_departamento']) ? intval($rqst['codigo_departamento']) : 0;
        $codigoMunicipio = isset($rqst['codigo_municipio']) ? intval($rqst['codigo_municipio']) : 0;

        if ($codigoDepartamento == 0 || $codigoMunicipio == 0) {
            return Util::error_missing_data();
        }

        $db = new DbConection();

        try {
            // SQL con los campos explícitos de tbl_vereda para evitar conflictos de nombres con tbl_ciudades_accion_unificada
            // IMPORTANTE: No usar tbl_vereda.* porque hay campos con el mismo nombre (path) en ambas tablas
            $sql = "SELECT
                        tbl_ciudades_accion_unificada.id,
                        tbl_ciudades_accion_unificada.codigo_departamento,
                        tbl_ciudades_accion_unificada.name,
                        tbl_ciudades_accion_unificada.class,
                        tbl_ciudades_accion_unificada.d,
                        tbl_vereda.id AS vereda_id,
                        tbl_vereda.nombre_vereda,
                        tbl_vereda.nombre_svg,
                        tbl_vereda.points,
                        tbl_vereda.path,
                        tbl_vereda.tspan,
                        tbl_vereda.municipio_id,
                        tbl_vereda.departamento_id,
                        tbl_vereda.color,
                        COUNT(tbl_visitas_alcalde.id) AS cantidad
                    FROM
                        " . $db->getTable('tbl_ciudades_accion_unificada') . "
                    LEFT JOIN
                        " . $db->getTable('tbl_vereda') . " ON tbl_ciudades_accion_unificada.codigo_muncipio = tbl_vereda.municipio_id
                    LEFT JOIN
                        " . $db->getTable('tbl_visitas_alcalde') . " ON tbl_vereda.id = tbl_visitas_alcalde.tbl_vereda_id
                    WHERE
                        tbl_ciudades_accion_unificada.codigo_departamento = $codigoDepartamento
                        AND tbl_ciudades_accion_unificada.codigo_muncipio = $codigoMunicipio
                    GROUP BY
                        tbl_vereda.id,
                        tbl_ciudades_accion_unificada.id,
                        tbl_ciudades_accion_unificada.codigo_departamento,
                        tbl_ciudades_accion_unificada.name,
                        tbl_ciudades_accion_unificada.class,
                        tbl_ciudades_accion_unificada.d,
                        tbl_vereda.nombre_vereda,
                        tbl_vereda.nombre_svg,
                        tbl_vereda.municipio_id,
                        tbl_vereda.departamento_id,
                        tbl_vereda.color
                    ORDER BY
                        tbl_vereda.nombre_vereda ASC";

            $cantidades = Util::sb_db_get($sql, false);

            // Validar si la consulta falló o no tiene resultados
            if (!is_array($cantidades) || (isset($cantidades['output']['response']['code']) && $cantidades['output']['response']['code'] == 104)) {
                return array('output' => array('valid' => true, 'response' => []));
            }

            $resultado = [];

            foreach ($cantidades as $veredaData) {
                $valorCantidad = (int)$veredaData['cantidad'];

                // Lógica de colores según la cantidad de visitas
                // Gris claro por defecto para que las veredas sin visitas sean visibles en el mapa
                $color = "#d3d3d3"; // Gris claro - 0 visitas

                if ($valorCantidad >= 1 && $valorCantidad <= 2) {
                    $color = "#dc3545"; // Rojo
                } elseif ($valorCantidad >= 3 && $valorCantidad <= 4) {
                    $color = "#ffc107"; // Amarillo
                } elseif ($valorCantidad >= 5 && $valorCantidad <= 6) {
                    $color = "#0d6efd"; // Azul
                } elseif ($valorCantidad >= 7) {
                    $color = "#198754"; // Verde
                }

                $veredaData['color_calculado'] = $color;
                $resultado[] = $veredaData;
            }

            return array('output' => array('valid' => true, 'response' => $resultado));

        } catch (Exception $e) {
            return Util::error_general("Error generando los colores: " . $e->getMessage());
        } finally {
            $db->closeConect();
        }
    }

    /**
     * Método para calcular los colores de veredas basado en compromisos del Alcalde
     * Similar a calcularColoresDeVisitasPorveredasDeUnaAlcaldia pero cuenta compromisos
     *
     * @param array $rqst - Debe contener codigo_departamento y codigo_municipio
     * @return array - Array con información de veredas y colores calculados según compromisos
     */
    public static function calcularColoresDeCompromisosPorveredasDeUnaAlcaldia($rqst)
    {
        $codigoDepartamento = isset($rqst['codigo_departamento']) ? intval($rqst['codigo_departamento']) : 0;
        $codigoMunicipio = isset($rqst['codigo_municipio']) ? intval($rqst['codigo_municipio']) : 0;

        if ($codigoDepartamento == 0 || $codigoMunicipio == 0) {
            return Util::error_missing_data();
        }

        $db = new DbConection();

        try {
            // SQL que cuenta compromisos por vereda en lugar de visitas
            $sql = "SELECT
                        tbl_ciudades_accion_unificada.id,
                        tbl_ciudades_accion_unificada.codigo_departamento,
                        tbl_ciudades_accion_unificada.path,
                        tbl_ciudades_accion_unificada.name,
                        tbl_ciudades_accion_unificada.class,
                        tbl_ciudades_accion_unificada.d,
                        tbl_vereda.*,
                        COUNT(tbl_compromisos_alcalde.id) AS cantidad
                    FROM
                        " . $db->getTable('tbl_ciudades_accion_unificada') . "
                    LEFT JOIN
                        " . $db->getTable('tbl_vereda') . " ON tbl_ciudades_accion_unificada.codigo_muncipio = tbl_vereda.municipio_id
                    LEFT JOIN
                        " . $db->getTable('tbl_compromisos_alcalde') . " ON tbl_vereda.id = tbl_compromisos_alcalde.tbl_vereda_id
                    WHERE
                        tbl_ciudades_accion_unificada.codigo_departamento = $codigoDepartamento
                        AND tbl_ciudades_accion_unificada.codigo_muncipio = $codigoMunicipio
                    GROUP BY
                        tbl_vereda.id,
                        tbl_ciudades_accion_unificada.id,
                        tbl_ciudades_accion_unificada.codigo_departamento,
                        tbl_ciudades_accion_unificada.path,
                        tbl_ciudades_accion_unificada.name,
                        tbl_ciudades_accion_unificada.class,
                        tbl_ciudades_accion_unificada.d,
                        tbl_vereda.nombre_vereda
                    ORDER BY
                        tbl_vereda.nombre_vereda ASC";

            $cantidades = Util::sb_db_get($sql, false);

            // Validar si la consulta falló o no tiene resultados
            if (!is_array($cantidades) || (isset($cantidades['output']['response']['code']) && $cantidades['output']['response']['code'] == 104)) {
                return array('output' => array('valid' => true, 'response' => []));
            }

            $resultado = [];

            foreach ($cantidades as $veredaData) {
                $valorCantidad = (int)$veredaData['cantidad'];

                // Lógica de colores según compromisos
                // 0 compromisos: Blanco (#ffffff)
                // 1 o más compromisos: Azul (#08306b) - color de compromisos
                $color = "#ffffff"; // Por defecto 0 compromisos (Blanco)

                if ($valorCantidad >= 1) {
                    $color = "#08306b"; // Azul - veredas con compromisos
                }

                $veredaData['color_calculado'] = $color;
                $resultado[] = $veredaData;
            }

            return array('output' => array('valid' => true, 'response' => $resultado));

        } catch (Exception $e) {
            return Util::error_general("Error generando los colores de compromisos: " . $e->getMessage());
        } finally {
            $db->closeConect();
        }
    }

    /**
     * Metodo para calcular los colores de todas las veredes que tiene un Municipio, según un pilar seleccionado
     * Verifica la información de todas sus veredas y calcula cada color de todas las veredas
     */
    public static function calcularColorPorMunicipioByPilarId($rqst)
    {
        $codigoDepartamento = isset($rqst['codigo_departamento']) ? intval($rqst['codigo_departamento']) : 0;
        $codigoMunicipio = isset($rqst['codigo_municipio']) ? intval($rqst['codigo_municipio']) : 0;
        $pilarId = isset($rqst['pilar']) ? intval($rqst['pilar']) : 0;
        $veredaId = isset($rqst['veredaId']) ? intval($rqst['veredaId']) : 0;

        if ($codigoDepartamento == 0 || $codigoMunicipio == 0 || $pilarId == 0) {
            return Util::error_missing_data();
        }

        $db = new DbConection();

        try {
            $puntajes = Util::sb_db_get("SELECT * FROM " . $db->getTable('tbl_puntajes'), false);

            $qIngresoInformacionCantidad = "SELECT 
                        tbl_ciudades_accion_unificada.id, 
                        tbl_ciudades_accion_unificada.codigo_departamento, 
                        tbl_ciudades_accion_unificada.path, 
                        tbl_ciudades_accion_unificada.name, 
                        tbl_ciudades_accion_unificada.class, 
                        tbl_ciudades_accion_unificada.d, 
                        tbl_vereda.*, 
                        tbl_factores.tec_pilar_id as pilar_id, 
                        SUM(tbl_factores.puntaje) AS cantidad
                    FROM 
                        " . $db->getTable('tbl_ciudades_accion_unificada') . " 
                    LEFT JOIN 
                        " . $db->getTable('tbl_vereda') . "  ON tbl_ciudades_accion_unificada.codigo_muncipio = tbl_vereda.municipio_id 
                    LEFT JOIN 
                        " . $db->getTable('tbl_ingreso_informacion') . "   ON tbl_vereda.id = tbl_ingreso_informacion.tbl_vereda_id 
                    LEFT JOIN 
                        " . $db->getTable('tbl_factores') . " ON tbl_ingreso_informacion.tbl_factor_id = tbl_factores.id
                            AND tbl_factores.tec_pilar_id = $pilarId
                    WHERE 
                        tbl_ciudades_accion_unificada.codigo_departamento = $codigoDepartamento  
                        AND tbl_ciudades_accion_unificada.codigo_muncipio = $codigoMunicipio 
                    GROUP BY 
                        tbl_vereda.id, 
                        tbl_ciudades_accion_unificada.id, 
                        tbl_ciudades_accion_unificada.codigo_departamento, 
                        tbl_ciudades_accion_unificada.path, 
                        tbl_ciudades_accion_unificada.name, 
                        tbl_ciudades_accion_unificada.class, 
                        tbl_ciudades_accion_unificada.d, 
                        tbl_vereda.nombre_vereda, 
                        tbl_factores.tec_pilar_id
                    ORDER BY 
                        tbl_vereda.nombre_vereda, cantidad ASC";

            $cantidades = Util::sb_db_get($qIngresoInformacionCantidad, false);

            if (isset($cantidades['output']['response']['code']) && $cantidades['output']['response']['code'] == 104 && $veredaId > 0) {
                $qVeredaInfo = "SELECT * FROM " . $db->getTable('tbl_vereda') . " WHERE departamento_id = $codigoDepartamento AND municipio_id = $codigoMunicipio   AND id = $veredaId";
                $cantidades = Util::sb_db_get($qVeredaInfo, false);
            }

            $colorDefecto = Util::getColorNeutroMapa();


            $puntajesVacios = isset($puntajes['output']['response']['code']) && $puntajes['output']['response']['code'] == 104;
            $puntajesValidos = !$puntajesVacios && is_array($puntajes);

            $resultado = [];
            foreach ($cantidades as $cantidad) {

                $color = $colorDefecto;

                $valorCantidad = isset($cantidad['cantidad']) ? (int)$cantidad['cantidad'] : 0;
                //$valorCantidad = isset($cantidad['cantidad']) ? intval(round($cantidad['cantidad'])) : 0;


                foreach ($puntajes as $puntaje) {

                    $color = $colorDefecto;

                    if ($puntajesValidos) {
                        if ($cantidad['pilar_id'] > 0 && $valorCantidad >= $puntaje['rango_desde'] && $valorCantidad <= $puntaje['rango_hasta']) {
                            $color = $puntaje['color'];
                            break;
                        }
                    }
                }
                $veredaData = $cantidad;
                $veredaData['color_calculado'] = $color;
                $resultado[] = $veredaData;
            }
            $arrjson = array('output' => array('valid' => true, 'response' => $resultado));
            return $arrjson;
        } catch (Exception $e) {
            return Util::error_general("Error generando los colores: " . $e->getMessage());
        } finally {
            $db->closeConect();
        }
    }


    /**
     * Metodo para calcular el color de la vereda TODOS LOS PILARES
     */
    public static function calcularColorPorVeredaByTodosLosPilares($rqst)
    {
        $codigoDepartamento = isset($rqst['codigo_departamento']) ? (int) $rqst['codigo_departamento'] : 0;
        $pilarId = isset($rqst['pilar']) ? (int) $rqst['pilar'] : 0;
        $veredaId = isset($rqst['veredaId']) ? (int) $rqst['veredaId'] : 0;

        if ($codigoDepartamento === 0 || $veredaId === 0 || $pilarId === 0) {
            return Util::error_missing_data();
        }

        $db = new DbConection();

        try {
            $puntajes = Util::sb_db_get("SELECT * FROM " . $db->getTable('tbl_puntajes'), false) ?: [];

            $configuracionAplicacion = Util::getInformacionConfiguracion();
            $tipo_configuracion_colores = $configuracionAplicacion[0]['tipo_configuracion_colores'] ?? 'Rango';

            $qIngresoInformacionCantidad = "SELECT tbl_vereda.*, 
                tbl_ingreso_informacion.valor, 
                tbl_ingreso_informacion.longitud, 
                tbl_ingreso_informacion.latitud, 
                tbl_factores.tec_pilar_id AS pilar_id,
                COALESCE(SUM(tbl_factores.puntaje), 0) AS cantidad
            FROM " . $db->getTable('tbl_ciudades_accion_unificada') . " 
            LEFT JOIN " . $db->getTable('tbl_vereda') . " 
                ON tbl_ciudades_accion_unificada.codigo_muncipio = tbl_vereda.municipio_id 
            LEFT JOIN " . $db->getTable('tbl_ingreso_informacion') . " 
                ON tbl_vereda.id = tbl_ingreso_informacion.tbl_vereda_id 
            LEFT JOIN " . $db->getTable('tbl_factores') . " 
                ON tbl_ingreso_informacion.tbl_factor_id = tbl_factores.id
            WHERE tbl_ciudades_accion_unificada.codigo_departamento = $codigoDepartamento
                AND tbl_vereda.id = $veredaId 
                AND tbl_factores.tec_pilar_id IS NOT NULL

            GROUP BY tbl_vereda.id, tbl_ciudades_accion_unificada.codigo_departamento";

            $cantidades = Util::sb_db_get($qIngresoInformacionCantidad, false);

            if ($cantidades['output']['response']['code']  == 104) {
                $qVeredaInfo = "SELECT * FROM " . $db->getTable('tbl_vereda') . " WHERE departamento_id = $codigoDepartamento AND id = $veredaId";
                $cantidades = Util::sb_db_get($qVeredaInfo, false);
            }

            $colorDefecto = Util::getColorNeutroMapa();
            $puntajesValidos = !empty($puntajes) && is_array($puntajes);

            $resultado = array_map(function ($cantidad) use ($puntajes, $puntajesValidos, $colorDefecto) {
                $valorCantidad = (int) ($cantidad['cantidad'] ?? 0);
                $color = $colorDefecto;

                if ($puntajesValidos && $cantidad['pilar_id'] > 0) {
                    foreach ($puntajes as $puntaje) {
                        if ($valorCantidad >= $puntaje['rango_desde'] && $valorCantidad <= $puntaje['rango_hasta']) {
                            $color = $puntaje['color'];
                            break;
                        }
                    }
                }
                $cantidad['cantidad_mostrar'] = $valorCantidad;
                $cantidad['color_calculado'] = $color;
                return $cantidad;
            }, $cantidades);

            return ['output' => ['valid' => true, 'response' => $resultado]];
        } catch (Exception $e) {
            return Util::error_general("Error generando los colores: " . $e->getMessage());
        } finally {
            $db->closeConect();
        }
    }

    /**
     * Metodo para calcular el color de la vereda por medio su Id y pilar Id 
     */
    public static function calcularColorPorVeredaByPilarId($rqst)
    {
        $codigoDepartamento = isset($rqst['codigo_departamento']) ? (int) $rqst['codigo_departamento'] : 0;
        $pilarId = isset($rqst['pilar']) ? (int) $rqst['pilar'] : 0;
        $veredaId = isset($rqst['veredaId']) ? (int) $rqst['veredaId'] : 0;

        if ($codigoDepartamento === 0 || $veredaId === 0 || $pilarId === 0) {
            return Util::error_missing_data();
        }

        $db = new DbConection();

        try {
            $puntajes = Util::sb_db_get("SELECT * FROM " . $db->getTable('tbl_puntajes'), false) ?: [];

            $configuracionAplicacion = Util::getInformacionConfiguracion();
            $tipo_configuracion_colores = $configuracionAplicacion[0]['tipo_configuracion_colores'] ?? 'Rango';

            $qIngresoInformacionCantidad = "SELECT tbl_vereda.*, 
                tbl_ingreso_informacion.valor, 
                tbl_ingreso_informacion.longitud, 
                tbl_ingreso_informacion.latitud, 
                tbl_factores.tec_pilar_id AS pilar_id,
                COALESCE(SUM(tbl_factores.puntaje), 0) AS cantidad
            FROM " . $db->getTable('tbl_ciudades_accion_unificada') . " 
            LEFT JOIN " . $db->getTable('tbl_vereda') . " 
                ON tbl_ciudades_accion_unificada.codigo_muncipio = tbl_vereda.municipio_id 
            LEFT JOIN " . $db->getTable('tbl_ingreso_informacion') . " 
                ON tbl_vereda.id = tbl_ingreso_informacion.tbl_vereda_id 
            LEFT JOIN " . $db->getTable('tbl_factores') . " 
                ON tbl_ingreso_informacion.tbl_factor_id = tbl_factores.id
            WHERE tbl_ciudades_accion_unificada.codigo_departamento = $codigoDepartamento
                AND tbl_vereda.id = $veredaId 
                AND tbl_factores.tec_pilar_id = $pilarId
            GROUP BY tbl_vereda.id, tbl_ciudades_accion_unificada.codigo_departamento";

            $cantidades = Util::sb_db_get($qIngresoInformacionCantidad, false);

            if ($cantidades['output']['response']['code']  == 104) {
                $qVeredaInfo = "SELECT * FROM " . $db->getTable('tbl_vereda') . " WHERE departamento_id = $codigoDepartamento AND id = $veredaId";
                $cantidades = Util::sb_db_get($qVeredaInfo, false);
            }

            $colorDefecto = Util::getColorNeutroMapa();
            $puntajesValidos = !empty($puntajes) && is_array($puntajes);

            $resultado = array_map(function ($cantidad) use ($puntajes, $puntajesValidos, $colorDefecto) {
                $valorCantidad = (int) ($cantidad['cantidad'] ?? 0);
                $color = $colorDefecto;

                if ($puntajesValidos && $cantidad['pilar_id'] > 0) {
                    foreach ($puntajes as $puntaje) {
                        if ($valorCantidad >= $puntaje['rango_desde'] && $valorCantidad <= $puntaje['rango_hasta']) {
                            $color = $puntaje['color'];
                            break;
                        }
                    }
                }

                $cantidad['cantidad_mostrar'] = $valorCantidad;
                $cantidad['color_calculado'] = $color;
                return $cantidad;
            }, $cantidades);

            return ['output' => ['valid' => true, 'response' => $resultado]];
        } catch (Exception $e) {
            return Util::error_general("Error generando los colores: " . $e->getMessage());
        } finally {
            $db->closeConect();
        }
    }

    /**
     * Metodo para calcular los colores de todas las veredes en general del departamento Principal
     * Obtiene la cantidad de veredas por color , solamente el dato , no retorna informacion general de las veredas como mapa y patht points
     */
    public static function calcularColorPorVeredasGeneralByPilarId($rqst)
    {
        $codigoDepartamento = isset($rqst['codigo_departamento']) ? intval($rqst['codigo_departamento']) : Util::getDepartamentoPrincipal();
        $pilarId = isset($rqst['pilarId']) ? intval($rqst['pilarId']) : 0;

        if ($codigoDepartamento == 0 || $pilarId == 0) {
            return Util::error_missing_data();
        }

        $db = new DbConection();

        try {
            $puntajes = Util::sb_db_get("SELECT * FROM " . $db->getTable('tbl_puntajes'), false);

            $qIngresoInformacionCantidad = "SELECT 
                    tbl_ciudades_accion_unificada.id, 
                    tbl_ciudades_accion_unificada.codigo_departamento, 
                    tbl_ciudades_accion_unificada.path, 
                    tbl_ciudades_accion_unificada.name, 
                    tbl_ciudades_accion_unificada.class, 
                    tbl_ciudades_accion_unificada.d, 
                    tbl_vereda.*, 
                    tbl_factores.tec_pilar_id as pilar_id, 
                    SUM(tbl_factores.puntaje) AS cantidad
                FROM 
                    " . $db->getTable('tbl_ciudades_accion_unificada') . " 
                LEFT JOIN 
                    " . $db->getTable('tbl_vereda') . "  ON tbl_ciudades_accion_unificada.codigo_muncipio = tbl_vereda.municipio_id 
                LEFT JOIN 
                    " . $db->getTable('tbl_ingreso_informacion') . "   ON tbl_vereda.id = tbl_ingreso_informacion.tbl_vereda_id 
                LEFT JOIN 
                    " . $db->getTable('tbl_factores') . " ON tbl_ingreso_informacion.tbl_factor_id = tbl_factores.id
                        AND tbl_factores.tec_pilar_id = $pilarId
                WHERE 
                    tbl_ciudades_accion_unificada.codigo_departamento = $codigoDepartamento
                GROUP BY 
                    tbl_vereda.id, 
                    tbl_ciudades_accion_unificada.id, 
                    tbl_ciudades_accion_unificada.codigo_departamento, 
                    tbl_ciudades_accion_unificada.path, 
                    tbl_ciudades_accion_unificada.name, 
                    tbl_ciudades_accion_unificada.class, 
                    tbl_ciudades_accion_unificada.d, 
                    tbl_vereda.nombre_vereda, 
                    tbl_factores.tec_pilar_id
                ORDER BY 
                    tbl_vereda.nombre_vereda, cantidad ASC";

            $cantidades = Util::sb_db_get($qIngresoInformacionCantidad, false);

            // Inicializar color neutro por defecto
            $colorDefecto = Util::getColorNeutroMapa();

            // Verificar si $puntajes tiene datos o está vacío
            $puntajesVacios = isset($puntajes['output']['response']['code']) && $puntajes['output']['response']['code'] == 104;
            $puntajesValidos = !$puntajesVacios && is_array($puntajes);

            $resultado = [];
            foreach ($cantidades as $cantidad) {

                // Asignar color neutro por defecto inicialmente
                $color = $colorDefecto;

                // Obtener la cantidad, asegurándose de que es un valor numérico
                $valorCantidad = isset($cantidad['cantidad']) ? (int)$cantidad['cantidad'] : 0;

                // Recorrer los rangos para encontrar el adecuado
                foreach ($puntajes as $puntaje) {
                    // Mostrar el rango actual en el que estamos iterando
                    $color = $colorDefecto;
                    // Verificar si la cantidad está dentro del rango
                    if ($puntajesValidos) {
                        if ($cantidad['pilar_id'] > 0 && $valorCantidad >= $puntaje['rango_desde'] && $valorCantidad <= $puntaje['rango_hasta']) {
                            $color = $puntaje['color'];
                            break;
                        }
                    }
                }
                $veredaData = $cantidad;
                $veredaData['color_calculado'] = $color;
                $resultado[] = $veredaData;

                if ($color != Util::getColorNeutroMapa()) {
                    if (!isset($colorCount[$color])) {
                        $colorCount[$color] = 0;
                    }
                    $colorCount[$color]++;
                }
            }
            $arrjson = array('output' => array('valid' => true, 'cantidad_colores' => $colorCount));
            return $arrjson;
        } catch (Exception $e) {
            return Util::error_general("Error generando los colores: " . $e->getMessage());
        } finally {
            $db->closeConect();
        }
    }

    public static function getInformacionFactoresGeneralesPorMunicipio($rqst)
    {
        $codigoDepartamento = isset($rqst['codigo_departamento']) ? intval($rqst['codigo_departamento']) : Util::getDepartamentoPrincipal();
        $codigoMunicipio = isset($rqst['codigo_municipio']) ? intval($rqst['codigo_municipio']) : 0;
        $ejeId = isset($rqst['ejeId']) ? intval($rqst['ejeId']) : 0;

        if ($codigoDepartamento === 0 || $codigoMunicipio === 0) {
            return Util::error_missing_data();
        }

        try {
            $db = new DbConection();
            $pdo = $db->openConect();

            $query = "
                SELECT 
                    tbl_ciudades_accion_unificada.codigo_departamento, 
                    tbl_ciudades_accion_unificada.municipio, 
                    tbl_ciudades_accion_unificada.codigo_muncipio, 
                    tbl_vereda.nombre_vereda, 
                    tbl_vereda.codigo_vereda, 
                    tbl_ingreso_informacion.valor, 
                    tbl_factores.tipo, 
                    tbl_factores.tipo_medicion, 
                    tbl_pilar.nombre,
                    tbl_ejes.nombre AS eje
                FROM " . $db->getTable('tbl_ciudades_accion_unificada') . "  
                INNER JOIN " . $db->getTable('tbl_ingreso_informacion') . "    
                    ON tbl_ciudades_accion_unificada.codigo_muncipio = tbl_ingreso_informacion.codigo_municipio
                INNER JOIN " . $db->getTable('tbl_vereda') . "    
                    ON tbl_ingreso_informacion.tbl_vereda_id = tbl_vereda.id
                INNER JOIN " . $db->getTable('tbl_factores') . "     
                    ON tbl_ingreso_informacion.tbl_factor_id = tbl_factores.id
                INNER JOIN " . $db->getTable('tbl_pilar') . "  
                    ON tbl_factores.tec_pilar_id = tbl_pilar.id
                INNER JOIN " . $db->getTable('tbl_ejes') . "     
                    ON tbl_factores.tbl_eje_id = tbl_ejes.id
                WHERE 
                    tbl_ciudades_accion_unificada.codigo_departamento = :codigoDepartamento
                    AND tbl_ciudades_accion_unificada.codigo_muncipio = :codigoMunicipio
                ORDER BY
                    tbl_ciudades_accion_unificada.municipio, tbl_ejes.nombre ASC";

            $stmt = $pdo->prepare($query);
            $stmt->bindValue(':codigoDepartamento', $codigoDepartamento, PDO::PARAM_INT);
            $stmt->bindValue(':codigoMunicipio', $codigoMunicipio, PDO::PARAM_INT);
            $stmt->execute();

            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $db->closeConect();

            return !empty($result)  ? ['output' => ['valid' => true, 'response' => $result]] : Util::error_no_result();
        } catch (Exception $e) {
            return Util::error_general($e->getMessage());
        }
    }

    /**
     * Método para calcular los colores de veredas basado en proyectos de alcaldías
     * Similar a calcularColoresDeVisitasPorveredasDeUnaAlcaldia pero cuenta proyectos
     *
     * @param array $rqst - Debe contener codigo_departamento y codigo_municipio
     * @return array - Array con información de veredas y colores calculados según proyectos
     */
    public static function calcularColoresDeProyectosPorveredasDeUnaAlcaldia($rqst)
    {
        $codigoDepartamento = isset($rqst['codigo_departamento']) ? intval($rqst['codigo_departamento']) : 0;
        $codigoMunicipio = isset($rqst['codigo_municipio']) ? intval($rqst['codigo_municipio']) : 0;
        $tblSecretariasId = isset($rqst['tbl_secretarias_id']) ? intval($rqst['tbl_secretarias_id']) : 0;

        if ($codigoDepartamento == 0 || $codigoMunicipio == 0) {
            return Util::error_missing_data();
        }

        $db = new DbConection();

        try {
            // SQL que cuenta proyectos por vereda
            // Construir LEFT JOIN con condición de secretaría dentro del JOIN
            $joinCondition = "tbl_vereda.id = tbl_proyectos_alcaldias.tbl_vereda_id";
            if ($tblSecretariasId > 0) {
                $joinCondition .= " AND tbl_proyectos_alcaldias.tbl_secretarias_id = $tblSecretariasId";
            }

            // SQL con los campos exactos necesarios y COUNT para obtener la cantidad real
            // IMPORTANTE: Seleccionar explícitamente los campos SVG de tbl_vereda (points, tspan, path)
            // para evitar conflictos de nombres con tbl_ciudades_accion_unificada
            $sql = "SELECT
                        tbl_ciudades_accion_unificada.id,
                        tbl_ciudades_accion_unificada.codigo_departamento,
                        tbl_ciudades_accion_unificada.name,
                        tbl_ciudades_accion_unificada.class,
                        tbl_ciudades_accion_unificada.d,
                        tbl_vereda.id AS vereda_id,
                        tbl_vereda.nombre_vereda,
                        tbl_vereda.nombre_svg,
                        tbl_vereda.points,
                        tbl_vereda.path,
                        tbl_vereda.tspan,
                        tbl_vereda.municipio_id,
                        tbl_vereda.departamento_id,
                        tbl_vereda.color,
                        COUNT(tbl_proyectos_alcaldias.id) AS cantidad
                    FROM
                        " . $db->getTable('tbl_ciudades_accion_unificada') . "
                    LEFT JOIN
                        " . $db->getTable('tbl_vereda') . " ON tbl_ciudades_accion_unificada.codigo_muncipio = tbl_vereda.municipio_id
                    LEFT JOIN
                        " . $db->getTable('tbl_proyectos_alcaldias') . " ON $joinCondition
                    WHERE
                        tbl_ciudades_accion_unificada.codigo_departamento = $codigoDepartamento
                        AND tbl_ciudades_accion_unificada.codigo_muncipio = $codigoMunicipio
                    GROUP BY
                        tbl_vereda.id,
                        tbl_ciudades_accion_unificada.id,
                        tbl_ciudades_accion_unificada.codigo_departamento,
                        tbl_ciudades_accion_unificada.name,
                        tbl_ciudades_accion_unificada.class,
                        tbl_ciudades_accion_unificada.d,
                        tbl_vereda.nombre_vereda,
                        tbl_vereda.nombre_svg,
                        tbl_vereda.municipio_id,
                        tbl_vereda.departamento_id,
                        tbl_vereda.color
                    ORDER BY
                        tbl_vereda.nombre_vereda ASC";

            $cantidades = Util::sb_db_get($sql, false);

            // Validar si la consulta falló o no tiene resultados
            if (!is_array($cantidades) || (isset($cantidades['output']['response']['code']) && $cantidades['output']['response']['code'] == 104)) {
                return array('output' => array('valid' => true, 'response' => []));
            }

            $resultado = [];

            foreach ($cantidades as $veredaData) {
                $valorCantidad = (int)$veredaData['cantidad'];

                // Lógica de colores según cantidad de proyectos
                // Gris claro por defecto para que las veredas sin proyectos sean visibles en el mapa
                $color = "#d3d3d3"; // Gris claro - 0 proyectos

                if ($valorCantidad >= 1 && $valorCantidad <= 2) {
                    $color = "#dc3545"; // Rojo
                } elseif ($valorCantidad >= 3 && $valorCantidad <= 4) {
                    $color = "#ffc107"; // Amarillo
                } elseif ($valorCantidad >= 5 && $valorCantidad <= 6) {
                    $color = "#0d6efd"; // Azul
                } elseif ($valorCantidad >= 7) {
                    $color = "#198754"; // Verde
                }

                $veredaData['color_calculado'] = $color;
                $resultado[] = $veredaData;
            }

            return array('output' => array('valid' => true, 'response' => $resultado));

        } catch (Exception $e) {
            return Util::error_general("Error generando los colores de proyectos: " . $e->getMessage());
        } finally {
            $db->closeConect();
        }
    }

    /**
     * Obtiene los datos SVG de todos los municipios de un departamento
     * Incluye el campo nombre_api_rpc para mapeo con APIs externas
     *
     * @param string $departamento Código del departamento (ej: '68')
     * @return array Lista de municipios con sus paths SVG
     */
    public static function getMunicipiosSvg($departamento)
    {
        $db = new DbConection();
        $pdo = $db->openConect();

        $sql = "SELECT codigo_muncipio, municipio, nombre_api_rpc, d, path,
                       nombre_mapa, codigo_departamento
                FROM " . $db->getTable('tbl_ciudades_accion_unificada') . " 
                WHERE codigo_departamento = :dep
                ORDER BY municipio";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([':dep' => $departamento]);
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $db->closeConect();

        return $result;
    }
}
