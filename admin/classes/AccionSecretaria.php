<?php

/**
 * Clase que contiene todas las operaciones utilizadas sobre la base de datos
 * @author SPIDERSOFTWARE
 */
class AccionSecretaria {

    public function __construct(){}

    public static function getAll($rqst){

        $id       = isset($rqst['id'])        ? intval($rqst['id'])        : 0;
        $municipio = isset($rqst['municipio']) ? intval($rqst['municipio']) : 0;

        if ($id > 0) {
            $db = new DbConection();
            $pdo = $db->openConect();

            // Filtro adicional por municipio cuando se proporciona
            $municipioWhere = $municipio > 0
                ? "AND tbl_ingreso_informacion.codigo_municipio = $municipio"
                : "";

            $q="SELECT
                tbl_ingreso_informacion.*,
                tbl_secretarias.secretaria,
                tbl_secretarias.secretario,
                tbl_ciudades_accion_unificada.municipio,
                tbl_vereda.nombre_vereda,
                tbl_factores.tipo,
                tbl_ejes.nombre AS nombre_eje,
                tbl_secretarias.id AS id_secretaria,
                tbl_factores.tipo_medicion
            FROM
                " . $db->getTable('tbl_ingreso_informacion') . "
            INNER JOIN
                " . $db->getTable('tbl_ciudades_accion_unificada') . "
                ON tbl_ingreso_informacion.codigo_municipio = tbl_ciudades_accion_unificada.codigo_muncipio
            INNER JOIN
                " . $db->getTable('tbl_vereda') . "
                ON tbl_ingreso_informacion.tbl_vereda_id = tbl_vereda.id
            INNER JOIN
                " . $db->getTable('tbl_factores') . "
                ON tbl_ingreso_informacion.tbl_factor_id = tbl_factores.id
            INNER JOIN
                 " . $db->getTable('tbl_ejes') . "
                ON tbl_factores.tbl_eje_id = tbl_ejes.id
            INNER JOIN
                " . $db->getTable('tbl_secretarias') . "
                ON tbl_factores.tbl_secretaria_id = tbl_secretarias.id
               WHERE
                    tbl_secretarias.id = " . $id . "
                    $municipioWhere
            GROUP BY
                tbl_ingreso_informacion.id
            ORDER BY
                tbl_ciudades_accion_unificada.municipio DESC";

             
                    

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
    }

    }