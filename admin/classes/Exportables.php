<?php
include 'Estado.php';
include 'Votaciones.php';
/**
 * Clase que contiene todas las operaciones utilizadas sobre la base de datos
 * @author SPIDERSOFTWARE
 */
class Exportables
{

    public function __construct()
    {
    }

    public static function getAnexoD($rqst)
    {
      
      $db = new DbConection();
      $pdo = $db->openConect();
  
      $q = "SELECT tbl_votaciones.*, tbl_brigadas.sigla AS brigada, tbl_batallones.sigla AS batallon
      FROM (" . $db->getTable('tbl_votaciones') . " INNER JOIN " . $db->getTable('tbl_brigadas') . " ON tbl_votaciones.tbl_brigada_id = tbl_brigadas.id) 
      INNER JOIN " . $db->getTable('tbl_batallones') . " ON tbl_votaciones.tbl_batallon_id = tbl_batallones.id";
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
     * EXPORTACION DE DATOS A EXCEL DE LOS DATOS DE LOS FACTORES DE INESTABILIDAD
    */
    public static function getFactoresInestabilidadPorBatallon($rqst) {

        $tbl_batallon_id = isset($rqst['batallon_id']) ? intval($rqst['batallon_id']) : 0;

        if ($tbl_batallon_id > 0) {

          $db = new DbConection();
          $pdo = $db->openConect();

          $q = "SELECT tbl_departamentos.departamento,
          tbl_ciudades.municipio, 
          tbl_ciudades.codigo_muncipio, 
          tbl_vereda.departamento_id, 
          tbl_batallones.sigla AS batallon, 
          tbl_vereda.nombre_vereda, tbl_vereda.id AS tbl_vereda_id
          FROM (((" . $db->getTable('tbl_vereda') . " INNER JOIN " . $db->getTable('tbl_batallones') . " ON tbl_vereda.tbl_batallon_id = tbl_batallones.id) ) 
          INNER JOIN  " . $db->getTable('tbl_ciudades ') . "  ON tbl_vereda.municipio_id = tbl_ciudades.codigo_muncipio) 
          INNER JOIN " . $db->getTable('tbl_departamentos') . " ON tbl_vereda.departamento_id = tbl_departamentos.codigo_departamento
          WHERE tbl_vereda.tbl_batallon_id = $tbl_batallon_id";


          print_r($q);
          exit();


            $result = $pdo->query($q);
            $arr = array();
            if ($result) {
                foreach ($result as $value) {
                    
                    $rqst =  array(
                        'codigo_departamento' => $value['departamento_id'],
                        'codigo_muncipio' => $value['codigo_muncipio'],
                        'vereda' => $value['nombre_vereda']
                    );
                    $response = Estado::getEstadoFactorArmadoSocialEcon($rqst);
                    if(  $response  && $response['output']['valid'] ) {
                        $arrjson = array(
                            'puntaje' => $response['output']['puntaje'],
                            'armadoResultadoFinal' => $response['output']['armadoResultadoFinal'],
                            'socialResultadoFinal' => $response['output']['socialResultadoFinal'],
                            'economicoResultadoFinal' => $response['output']['economicoResultadoFinal'],
                            'batallon' =>$response['output']['batallon'],
                            'brigada' => $response['output']['brigada'],
                            'nombre_vereda' => $response['output']['nombre_vereda'],
                            'nombre_municipio' => $response['output']['nombre_municipio'],
                        );
                        $arr[] =  $arrjson;
                    }
                }
            } else {
                $db->closeConect();
                return Util::error_no_result();
            }
            $db->closeConect();
            return $arr;

        } else {
            return Util::error_no_result();
        }
    }

    

       

}
