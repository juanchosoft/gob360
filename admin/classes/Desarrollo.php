<?php

/**
 * Clase que contiene todas las operaciones utilizadas sobre la base de datos
 * @author SPIDERSOFTWARE
 */
class Desarrollo
{
  //usar tabla para guardar plandesarrollo dado un roll 
  private static function _getTable($db)
    {
      $rol_usuario = isset($_SESSION['session_user']['tipo']) ? $_SESSION['session_user']['tipo'] : '';
      
      if ($rol_usuario === 'Gobernador' || $rol_usuario === 'Secretaria_Despacho_Gobernacion') {
          return $db->getTable('tbl_plandesarrollo_gob');
      } else {
          return $db->getTable('tbl_plandesarrollo');
      }
    }

  /**
   * Metodo para setear la variable de session y realizar filtros.
   */
  public static function setFiltroSecretariaById($rqst)
  {
      $tbl_secretarias_id = isset($rqst['secretaria_id']) ? intval($rqst['secretaria_id']) : ''; 

      if($tbl_secretarias_id > 0){
        $_SESSION['session_user']['secretaria'] = $tbl_secretarias_id;
      }

      return array('output' => array('valid' => true));
  }


  public static function getAll($rqst)
  {  
    $id = isset($rqst['id']) ? intval($rqst['id']) : 0;
    $secretaria_id = isset($rqst['secretaria_id']) ? intval($rqst['secretaria_id']) : 0;
    $municipio_id = isset($rqst['tbl_municipio_id']) ? intval($rqst['tbl_municipio_id']) : 0; 
    $tipo = isset($rqst['tipo']) ? ($rqst['tipo']) : '';

    $db = new DbConection();
    $pdo = $db->openConect();
    
    $main_table = self::_getTable($db); 

    $where_clauses = [];
    $is_gob_table = ($main_table === $db->getTable('tbl_plandesarrollo_gob'));
    

    $join_type = $is_gob_table ? 'LEFT JOIN' : 'INNER JOIN';

    $base_query="SELECT pd.*, s.secretaria
      FROM " . $main_table . " pd
      " . $join_type . " " . $db->getTable('tbl_secretarias') . " s ON pd.tbl_secretaria_id = s.id";  

    
    if ($id > 0) {
      $where_clauses[] = "pd.id = " . $id;  
    } 
    

    if (!$is_gob_table && $id == 0) {
        
      //por ID de Secretaria
      if ($secretaria_id > 0) {
          $where_clauses[] = "s.id = " . $secretaria_id;  
      }
      
      //por ID de Municipio
      if ($municipio_id > 0) {
        $where_clauses[] = "pd.tbl_municipio_id = " . $municipio_id;  
      }
    }
    
    $where = '';
    if (!empty($where_clauses)) {
        $where = ' WHERE ' . implode(' AND ', $where_clauses);
    }
    
    $q = $base_query . $where;
  
    $result = $pdo->query($q);
    $arr = array();
    if ($result) {
      foreach ($result as $valor) {

        if ($is_gob_table && empty($valor['secretaria'])) {
            $valor['secretaria'] = 'Gobernación';
        }
        $arr[] = $valor;
      }
      $arrjson = array('output' => array('valid' => true, 'response' => $arr));
    } else {
      $arrjson = Util::error_no_result();
    }
    $db->closeConect();
    return $arrjson;
  }

  public static function save($rqst)
  {
    $id = isset($rqst['id']) ? intval($rqst['id']) : 0;
    $tbl_secretaria_id = isset($rqst['tbl_secretaria_id']) ? intval($rqst['tbl_secretaria_id']) : 0;
    $eje_estrategico =  isset($rqst['eje_estrategico']) ? ($rqst['eje_estrategico']) : '';
    $sector_pdd =  isset($rqst['sector_pdd']) ? ($rqst['sector_pdd']) : '';
    $sector_cat_prod =  isset($rqst['sector_cat_prod']) ? ($rqst['sector_cat_prod']) : '';
    $producto_servicio_pdd = isset($rqst['producto_servicio_pdd']) ? intval($rqst['producto_servicio_pdd']) : 0;
    $direccion_resp = isset($rqst['direccion_resp']) ? ($rqst['direccion_resp']) : [];
    $ps2024 = isset($rqst['ps2024']) ? floatval($rqst['ps2024']) : 0;
    $ps2025 = isset($rqst['ps2025']) && $rqst['ps2025'] !="" ? ($rqst['ps2025']) : null;
    $ps2026 = isset($rqst['ps2026']) && $rqst['ps2026'] !="" ? ($rqst['ps2026']) : null;
    $ps2027 = isset($rqst['ps2027']) && $rqst['ps2027'] !=""  ? ($rqst['ps2027']) : null;
    $user_id =  intval($_SESSION['session_user']['id']);

    $tbl_municipio_id = isset($_SESSION['session_user']['tbl_municipio_id']) ? intval($_SESSION['session_user']['tbl_municipio_id']) : null;

    
    $db = new DbConection();
    $pdo = $db->openConect();
    
    $table = self::_getTable($db); 
    
    if ($table === $db->getTable('tbl_plandesarrollo_gob')) {
        $tbl_secretaria_id = 0;
        $tbl_municipio_id = 0;
    }

    if ($id > 0) {
      $q = "SELECT * FROM " . $table . " WHERE id = " . $id;
      $result = $pdo->query($q);
      if ($result) {
        $arrfieldscomma = array(
          'eje_estrategico' => $eje_estrategico,
          'sector_pdd' => $sector_pdd,
          'sector_cat_prod' => $sector_cat_prod,
          'tbl_secretaria_id' => $tbl_secretaria_id,
          'direccion_resp' => $direccion_resp,
          'ps2024' => $ps2024,
          'ps2025' => $ps2025,
          'ps2026' => $ps2026,
          'ps2027' => $ps2027,
          'user_id' => $user_id,
          'tbl_municipio_id' => $tbl_municipio_id 
        );
        $arrfieldsnocomma = array('dtcreate' => Util::date_now_server());
                $q = Util::make_query_update($table, "id = '$id'", $arrfieldscomma, $arrfieldsnocomma);
                $result = $pdo->query($q);
                if(!$result){
                    $arrjson = Util::error_general('Actualizando los datos de brigada');
                }else{
                    $arrjson = array('output' => array('valid' => true, 'id' => $id));
                }
            } else {
                $arrjson = Util::error_general();
            }
        } else {
            if ($eje_estrategico != "") {
                $q = "INSERT INTO " . $table . " (dtcreate, eje_estrategico, sector_pdd, sector_cat_prod, tbl_secretaria_id, direccion_resp, ps2024, ps2025, ps2026, ps2027, user_id, tbl_municipio_id)
                VALUES ( " . Util::date_now_server() . ", :eje_estrategico, :sector_pdd, :sector_cat_prod, :tbl_secretaria_id, :direccion_resp, :ps2024, :ps2025, :ps2026, :ps2027, :user_id, :tbl_municipio_id)";
                $result = $pdo->prepare($q);
                
                $arrparam = array(
                    ':eje_estrategico' => $eje_estrategico,
                    ':sector_pdd' => $sector_pdd,
                    ':sector_cat_prod' => $sector_cat_prod,
                    ':tbl_secretaria_id' => $tbl_secretaria_id,
                    ':direccion_resp' => $direccion_resp,
                    ':ps2024' => $ps2024,
                    ':ps2025' => $ps2025,
                    ':ps2026' => $ps2026,
                    ':ps2027' => $ps2027,
                    ':user_id' => $user_id,
                    ':tbl_municipio_id' => $tbl_municipio_id 
                );
                if ($result->execute($arrparam)) {
                    $arrjson = array('output' => array('valid' => true, 'response' => $pdo->lastInsertId()));
                } else {
                    $arrjson = Util::error_general(' Al guardar los datos de brigada');
                }
            } else {
                $arrjson = Util::error_missing_data();
            }
        }
        $db->closeConect();
        return $arrjson;
    }

    public static function updateStatus($id, $status) {
      $db = new DbConection();
      $pdo = $db->openConect();

      $q = "UPDATE " . $db->getTable('avance_2024') . " SET id = :status WHERE id = :id";
      $stmt = $pdo->prepare($q);
      $stmt->bindParam(':status', $status, PDO::PARAM_STR);
      $stmt->bindParam(':id', $id, PDO::PARAM_INT);
      $result = $stmt->execute();
      $db->closeConect();

      return $result;
  }
  
  public static function saveMany($dataArray, $targetTable = 'tbl_plandesarrollo')
  {
      $db = new DbConection();
      $pdo = $db->openConect();

      try {
          // Prepara la sentencia INSERT con los 14 campos
          $sql = "INSERT INTO " . $db->getTable( $targetTable) . " (
              eje_estrategico, 
              sector_pdd, 
              sector_cat_prod, 
              producto_servicio_pdd, 
              tbl_secretaria_id, 
              direccion_resp, 
              ps2024, 
              avance_2024,
              avance_2025,
              ps2025,
              ps2026,
              ps2027,
              user_id,
              tbl_municipio_id
          ) VALUES (
              :eje_estrategico, 
              :sector_pdd, 
              :sector_cat_prod, 
              :producto_servicio_pdd, 
              :tbl_secretaria_id, 
              :direccion_resp, 
              :ps2024, 
              :avance_2024,
              :avance_2025,
              :ps2025,
              :ps2026,
              :ps2027,
              :user_id,
              :tbl_municipio_id
          )";
          
          $stmt = $pdo->prepare($sql);
          
          $insertedCount = 0;
          
          
          $pdo->beginTransaction();

          foreach ($dataArray as $row) {
              
              $stmt->execute([
                  ':eje_estrategico' => $row[0],
                  ':sector_pdd' => $row[1],
                  ':sector_cat_prod' => $row[2],
                  ':producto_servicio_pdd' => $row[3],
                  ':tbl_secretaria_id' => $row[4],
                  ':direccion_resp' => $row[5],
                  ':ps2024' => $row[6],
                  ':avance_2024' => $row[7],
                  ':avance_2025' => $row[8],
                  ':ps2025' => $row[9],
                  ':ps2026' => $row[10],
                  ':ps2027' => $row[11],
                  ':user_id' => $row[12],
                  ':tbl_municipio_id' => $row[13]
              ]);
              $insertedCount++;
          }
          
          $pdo->commit(); // Confirma la transacción
          
          return array('output' => array('valid' => true, 'count' => $insertedCount));

      } catch (PDOException $e) {
          $pdo->rollBack(); 
          return array('output' => array('valid' => false, 'message' => "Error al insertar: " . $e->getMessage()));
      } finally {
          $db->closeConect();
      }
  }



    
}