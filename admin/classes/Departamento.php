<?php

/**
 * Clase que contiene todas las operaciones utilizadas sobre la base de datos
 * @author SPIDERSOFTWARE
 */
class Departamento {

    public function __construct(){}


    public static function getAll($rqst)
    {
        $id = isset($rqst['id']) ? intval($rqst['id']) : 0;

        $db = new DbConection();
        $pdo = $db->openConect();

        $q = "SELECT * FROM " . $db->getTable('tbl_departamentos') . " WHERE habilitado = 'si' and id = " . Util::idDepartamentoObjetivo();
        if ($id > 0) {
            $q = "SELECT * FROM " . $db->getTable('tbl_departamentos') . " WHERE id = " . $id;
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


    /**
     * esto tiene todos los municipios de un departamento dado su id departamento
     * @param string $codigoDepartamento El código del departamento (ej: '68' para Santander).
     * @return array
     */
    public function getMunicipiosByDeptoId(string $codigoDepartamento)
    {
        $db = new DbConection();
        $pdo = $db->openConect();

        $q = "SELECT codigo_muncipio, municipio FROM " . $db->getTable('tbl_ciudades_accion_unificada') . " 
              WHERE codigo_departamento = :codigoDepto
              ORDER BY municipio ASC";
              

        $stmt = $pdo->prepare($q);
        $stmt->bindParam(':codigoDepto', $codigoDepartamento, PDO::PARAM_STR); 
        $stmt->execute();

        $arr = array();
        while ($valor = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $arr[] = $valor;
        }

        if (!empty($arr)) {
            $arrjson = array('output' => array('valid' => true, 'response' => $arr));
        } else {
            $arrjson = Util::error_no_result();
        }

        $db->closeConect();
        return $arrjson;
    }




}
