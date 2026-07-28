<?php

/**
 * Clase que contiene todas las operaciones utilizadas sobre la base de datos
 * @author SPIDERSOFTWARE
 */
class Pilar {

    public function __construct(){}


    public static function getAll($rqst)
{
    $id = isset($rqst['id']) ? intval($rqst['id']) : 0;
    $ejeId = isset($rqst['ejeId']) ? intval($rqst['ejeId']) : 0;

    $db = new DbConection();
    $pdo = $db->openConect();

    // Construir la consulta base
    $q = "SELECT * FROM " . $db->getTable('tbl_pilar');

    if ($id > 0) {
        $q .= " WHERE id = " . $id;
    } elseif ($ejeId > 0) {
        $q .= " WHERE tbl_ejes_id = " . $ejeId;
    }

   
    $q .= " ORDER BY nombre ASC"; 
    // Ejecutar la consulta
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
     * Obtiene el Factor (tipo) y la Secretaría (tbl_secretaria_id) principales asociados a un Pilar.
     */
    public static function getFactorPrincipalByPilarId($pilarId) {
        $db = new DbConection();
        $pdo = $db->openConect();
        
        if (empty($pilarId) || $pilarId <= 0) {
            $db->closeConect();
            return ['output' => ['valid' => false, 'error' => "Se requiere un ID de Pilar válido."]];
        }

        try {

            $q = "
                SELECT 
                    tf.tbl_secretaria_id AS secretariaId,
                    tf.tipo AS accionFactor
                FROM 
                    " . $db->getTable('tbl_factores') . " tf
                WHERE 
                    tf.tec_pilar_id = :pilarId 
                AND 
                    tf.tbl_secretaria_id IS NOT NULL 
                ORDER BY 
                    tf.puntaje DESC, tf.id ASC 
                LIMIT 1
            ";

            $stmt = $pdo->prepare($q);
            $stmt->bindValue(':pilarId', $pilarId, PDO::PARAM_INT);
            $stmt->execute();
            
            $result = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($result) {

                $arrjson = ['output' => ['valid' => true, 'response' => $result]];
            } else {

                $arrjson = ['output' => ['valid' => false, 'error' => "No se encontró Factor/Secretaría para el Pilar ID: " . $pilarId]];
            }

        } catch (PDOException $e) {

            error_log("Error en getFactorPrincipalByPilarId: " . $e->getMessage());
            $arrjson = ['output' => ['valid' => false, 'error' => "Error de base de datos: " . $e->getMessage()]];
        } finally {
            $db->closeConect();
        }

        return $arrjson;
    }

}
