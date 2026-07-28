<?php

/**
 * Clase que contiene todas las operaciones utilizadas sobre la base de datos
 * @author SPIDERSOFTWARE
 */
class Operatividad {

    public function __construct(){}

    public static function getAll($rqst)
    {

        $id = isset($rqst['id']) ? intval($rqst['id']) : 0;
        $tipo = isset($rqst['tipo']) ? ($rqst['tipo']) : '';

        $db = new DbConection();
        $pdo = $db->openConect();

        $q = "SELECT * FROM " . $db->getTable('tbl_operatividad') . " ORDER BY id DESC LIMIT 100";
        if ($id > 0) {
            $q = "SELECT * FROM " . $db->getTable('tbl_operatividad') . " WHERE id = " . $id;
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

    public static function update($rqst) {

      $id = isset($rqst['id']) ? intval($rqst['id']) : 0;
      $presentaciones = isset($rqst['presentaciones']) ? intval($rqst['presentaciones']) : 0;
      $mdom =  isset($rqst['mdom']) ? intval($rqst['mdom']) : 0;
      $sometimiento =  isset($rqst['sometimiento']) ? intval($rqst['sometimiento']) : 0;
      $capturas_gao = isset($rqst['capturas_gao']) ? intval($rqst['capturas_gao']) : 0;
      $capturas_gdo = isset($rqst['capturas_gdo']) ? intval($rqst['capturas_gdo']) : 0;
      $capturas_delco = isset($rqst['capturas_delco']) ? intval($rqst['capturas_delco']) : 0;
      $bajas_delco = isset($rqst['bajas_delco']) ? intval($rqst['bajas_delco']) : 0;
      $menores = isset($rqst['menores']) ? intval($rqst['menores']) : 0;
      $total = $presentaciones + $mdom + $sometimiento + $capturas_gao + $capturas_gdo;
      $upm = isset($rqst['upm']) ? intval($rqst['upm']) : 0;
      $dragas = isset($rqst['dragas']) ? intval($rqst['dragas']) : 0;
      $motores = isset($rqst['motores']) ? intval($rqst['motores']) : 0;
      $explosivos = isset($rqst['explosivos']) ? intval($rqst['explosivos']) : 0;
      $armas_cortas = isset($rqst['armas_cortas']) ? intval($rqst['armas_cortas']) : 0;
      $municiones = isset($rqst['municiones']) ? intval($rqst['municiones']) : 0;
      $comunicaciones = isset($rqst['comunicaciones']) ? intval($rqst['comunicaciones']) : 0;
      $intendencia = isset($rqst['intendencia']) ? intval($rqst['intendencia']) : 0;
      $lab_ch = isset($rqst['lab_ch']) ? intval($rqst['lab_ch']) : 0;
      $semilleros = isset($rqst['semilleros']) ? intval($rqst['semilleros']) : 0;
      $depositos = isset($rqst['depositos']) ? intval($rqst['depositos']) : 0;
      $campamentos = isset($rqst['campamentos']) ? intval($rqst['campamentos']) : 0;
      $lab_pbc = isset($rqst['lab_pbc']) ? intval($rqst['lab_pbc']) : 0;
      $pasta_coca = isset($rqst['pasta_coca']) ? intval($rqst['pasta_coca']) : 0;
      $capturas_soc = isset($rqst['capturas_soc']) ? intval($rqst['capturas_soc']) : 0;
      $madera = isset($rqst['madera']) ? intval($rqst['madera']) : 0;
      $siembra = isset($rqst['siembra']) ? intval($rqst['siembra']) : 0;
      $solidos = isset($rqst['solidos']) ? intval($rqst['solidos']) : 0;
      $proveedores = isset($rqst['proveedores']) ? intval($rqst['proveedores']) : 0;
      $combates = isset($rqst['combates']) ? intval($rqst['combates']) : 0;
      $vehiculos = isset($rqst['vehiculos']) ? intval($rqst['vehiculos']) : 0;
      $retro = isset($rqst['retro']) ? intval($rqst['retro']) : 0;
      $otras_maq = isset($rqst['otras_maq']) ? intval($rqst['otras_maq']) : 0;
      $otras_sustancias = isset($rqst['otras_sustancias']) ? intval($rqst['otras_sustancias']) : 0;
      $dinero = isset($rqst['dinero']) ? intval($rqst['dinero']) : 0;
      $hoja = isset($rqst['hoja']) ? intval($rqst['hoja']) : 0;
      $fauna = isset($rqst['fauna']) ? intval($rqst['fauna']) : 0;
      $dominio = isset($rqst['dominio']) ? intval($rqst['dominio']) : 0;
      $tbl_usuario_id = $_SESSION['session_user']['id'];
      $armas_largas = isset($rqst['armas_largas']) ? intval($rqst['armas_largas']) : 0;
      $mercurio = isset($rqst['mercurio']) ? intval($rqst['mercurio']) : 0;
      $mariguana = isset($rqst['mariguana']) ? intval($rqst['mariguana']) : 0;
      $pasta_proceso = isset($rqst['pasta_proceso']) ? intval($rqst['pasta_proceso']) : 0;
      $cloridrato = isset($rqst['cloridrato']) ? intval($rqst['cloridrato']) : 0;
      $liquidos = isset($rqst['liquidos']) ? intval($rqst['liquidos']) : 0;
      $opsic = isset($rqst['opsic']) ? intval($rqst['opsic']) : 0;
      $semilleros_matas = isset($rqst['semilleros_matas']) ? intval($rqst['semilleros_matas']) : 0;
      $minas = isset($rqst['minas']) ? intval($rqst['minas']) : 0;
      $gaulavol = isset($rqst['gaulavol']) ? intval($rqst['gaulavol']) : 0;
      $gaularadio = isset($rqst['gaularadio']) ? intval($rqst['gaularadio']) : 0;
      $gaulareunion = isset($rqst['gaulareunion']) ? intval($rqst['gaulareunion']) : 0;

        if ($id > 0) {
          $db = new DbConection();
          $pdo = $db->openConect();

            //actualiza la informacion
            $q0 = "SELECT id FROM " . $db->getTable('tbl_operatividad') . " WHERE id = " . $id;
            $result0 = $pdo->query($q0);
            if ($result0) {
                $table = $db->getTable('tbl_operatividad');
                $arrfieldscomma = array(
                  'mdom' => $mdom,
                  'upm' => $upm,
                  'sometimiento' => $sometimiento ,
                  'capturas_delco' => $capturas_delco ,
                  'bajas_delco' => $bajas_delco ,
                  'menores' => $menores ,
                  'dragas' => $dragas,
                  'explosivos' => $explosivos,
                  'lab_ch' => $lab_ch,
                  'armas_cortas' => $armas_cortas,
                  'motores' => $motores,
                  'capturas_gao' => $capturas_gao,
                  'presentaciones' => $presentaciones,
                  'municiones' => $municiones,
                  'combates' => $combates,
                  'comunicaciones' => $comunicaciones,
                  'intendencia' => $intendencia,
                  'capturas_gdo' => $capturas_gdo,
                  'semilleros' => $semilleros,
                  'depositos' => $depositos,
                  'campamentos' => $campamentos,
                  'lab_pbc' => $lab_pbc,
                  'tbl_usuario_id' => $tbl_usuario_id,
                  'pasta_coca' => $pasta_coca,
                  'capturas_soc' => $capturas_soc,
                  'madera' => $madera,
                  'siembra' => $siembra,
                  'total' => $total,
                  'proveedores' => $proveedores,
                  'armas_largas' => $armas_largas,
                  'mercurio' => $mercurio,
                  'mariguana' => $mariguana,
                  'pasta_proceso' => $pasta_proceso,
                  'cloridrato' => $cloridrato,
                  'liquidos' => $liquidos,
                  'vehiculos' => $vehiculos,
                  'retro' => $retro,
                  'otras_maq' => $otras_maq,
                  'otras_sustancias' => $otras_sustancias,
                  'dinero' => $dinero,
                  'fauna' => $fauna,
                  'dominio' => $dominio,
                  'solidos' => $solidos,
                  'hoja' => $hoja,
                  'opsic' => $opsic,
                  'semilleros_matas' => $semilleros_matas,
                  'minas' => $minas,
                  'gaularadio' => $gaularadio,
                  'gaulavol' => $gaulavol,
                  'gaulareunion' => $gaulareunion,
                );
                $arrfieldsnocomma = array('dtcreate' => Util::date_now_server());
                $q = Util::make_query_update($table, "id = '$id'", $arrfieldscomma, $arrfieldsnocomma);
                $result = $pdo->query($q);
                $db->closeConect();
                $arrjson = array('output' => array('valid' => true, 'id' => $id));
                return $arrjson;
            } else {
                return Util::error_general();
            }
        } else {
            return Util::error_missing_data();
        }

    }

    public static function save($rqst){

        $id = isset($rqst['id']) ? intval($rqst['id']) : 0;
        $tbl_departamento_id =  isset($rqst['tbl_departamento_id']) ? intval($rqst['tbl_departamento_id']) : 0; // llega el codigo del departamento
        $tbl_municipio_id =  isset($rqst['tbl_municipio_id']) ? intval($rqst['tbl_municipio_id']) : 0; // llega el codigo del municipio
        $tbl_vereda_id = isset($rqst['tbl_vereda_id']) ? ($rqst['tbl_vereda_id']) : '';
        $presentaciones = isset($rqst['presentaciones']) ? intval($rqst['presentaciones']) : 0;
        $mdom =  isset($rqst['mdom']) ? intval($rqst['mdom']) : 0;
        $sometimiento =  isset($rqst['sometimiento']) ? intval($rqst['sometimiento']) : 0;
        $capturas_gao = isset($rqst['capturas_gao']) ? intval($rqst['capturas_gao']) : 0;
        $capturas_gdo = isset($rqst['capturas_gdo']) ? intval($rqst['capturas_gdo']) : 0;
        $capturas_delco = isset($rqst['capturas_delco']) ? intval($rqst['capturas_delco']) : 0;
        $bajas_delco = isset($rqst['bajas_delco']) ? intval($rqst['bajas_delco']) : 0;
        $menores = isset($rqst['menores']) ? intval($rqst['menores']) : 0;
        $total = $presentaciones + $mdom + $sometimiento + $capturas_gao + $capturas_gdo;
        $upm = isset($rqst['upm']) ? intval($rqst['upm']) : 0;
        $dragas = isset($rqst['dragas']) ? intval($rqst['dragas']) : 0;
        $motores = isset($rqst['motores']) ? intval($rqst['motores']) : 0;
        $explosivos = isset($rqst['explosivos']) ? intval($rqst['explosivos']) : 0;
        $armas_cortas = isset($rqst['armas_cortas']) ? intval($rqst['armas_cortas']) : 0;
        $municiones = isset($rqst['municiones']) ? intval($rqst['municiones']) : 0;
        $comunicaciones = isset($rqst['comunicaciones']) ? intval($rqst['comunicaciones']) : 0;
        $intendencia = isset($rqst['intendencia']) ? intval($rqst['intendencia']) : 0;
        $lab_ch = isset($rqst['lab_ch']) ? intval($rqst['lab_ch']) : 0;
        $semilleros = isset($rqst['semilleros']) ? intval($rqst['semilleros']) : 0;
        $depositos = isset($rqst['depositos']) ? intval($rqst['depositos']) : 0;
        $campamentos = isset($rqst['campamentos']) ? intval($rqst['campamentos']) : 0;
        $lab_pbc = isset($rqst['lab_pbc']) ? intval($rqst['lab_pbc']) : 0;
        $pasta_coca = isset($rqst['pasta_coca']) ? intval($rqst['pasta_coca']) : 0;
        $capturas_soc = isset($rqst['capturas_soc']) ? intval($rqst['capturas_soc']) : 0;
        $madera = isset($rqst['madera']) ? intval($rqst['madera']) : 0;
        $siembra = isset($rqst['siembra']) ? intval($rqst['siembra']) : 0;
        $solidos = isset($rqst['solidos']) ? intval($rqst['solidos']) : 0;
        $proveedores = isset($rqst['proveedores']) ? intval($rqst['proveedores']) : 0;
        $combates = isset($rqst['combates']) ? intval($rqst['combates']) : 0;

        $vehiculos = isset($rqst['vehiculos']) ? intval($rqst['vehiculos']) : 0;
        $retro = isset($rqst['retro']) ? intval($rqst['retro']) : 0;
        $otras_maq = isset($rqst['otras_maq']) ? intval($rqst['otras_maq']) : 0;
        $otras_sustancias = isset($rqst['otras_sustancias']) ? intval($rqst['otras_sustancias']) : 0;
        $dinero = isset($rqst['dinero']) ? intval($rqst['dinero']) : 0;
        $hoja = isset($rqst['hoja']) ? intval($rqst['hoja']) : 0;
        $fauna = isset($rqst['fauna']) ? intval($rqst['fauna']) : 0;
        $dominio = isset($rqst['dominio']) ? intval($rqst['dominio']) : 0;
        $tbl_usuario_id = $_SESSION['session_user']['id'];
        $armas_largas = isset($rqst['armas_largas']) ? intval($rqst['armas_largas']) : 0;
        $mercurio = isset($rqst['mercurio']) ? intval($rqst['mercurio']) : 0;
        $mariguana = isset($rqst['mariguana']) ? floatval($rqst['mariguana']) : 0;
        $pasta_proceso = isset($rqst['pasta_proceso']) ? intval($rqst['pasta_proceso']) : 0;
        $cloridrato = isset($rqst['cloridrato']) ? intval($rqst['cloridrato']) : 0;
        $liquidos = isset($rqst['liquidos']) ? intval($rqst['liquidos']) : 0;
        $opsic = isset($rqst['opsic']) ? intval($rqst['opsic']) : 0;
        $semilleros_matas = isset($rqst['semilleros$semilleros_matas']) ? intval($rqst['semilleros$semilleros_matas']) : 0;
        $minas = isset($rqst['minas']) ? intval($rqst['minas']) : 0;
        $gaularadio = isset($rqst['gaularadio']) ? intval($rqst['gaularadio']) : 0;
        $gaulareunion = isset($rqst['gaulareunion']) ? intval($rqst['gaulareunion']) : 0;
        $gaulavol = isset($rqst['gaulavol']) ? intval($rqst['gaulavol']) : 0;
        $tbl_departamento_id_jurisdiccion =  isset($rqst['tbl_departamento_id_jurisdiccion']) ? intval($rqst['tbl_departamento_id_jurisdiccion']) : 0; // llega el codigo del municipio
        $tbl_municipio_id_jurisdiccion =  isset($rqst['tbl_municipio_id_jurisdiccion']) ? intval($rqst['tbl_municipio_id_jurisdiccion']) : 0; // llega el codigo del municipio
        $tbl_vereda_id_jurisdiccion = isset($rqst['tbl_vereda_id_jurisdiccion']) ? intval($rqst['tbl_vereda_id_jurisdiccion']) : 0; // Id de la vereda
        $coordenadas = isset($rqst['coordenadas']) ? ($rqst['coordenadas']) :'';
        $resultado_jurisdiccion = isset($rqst['resultado_jurisdiccion']) ? ($rqst['resultado_jurisdiccion']) : 'si';
        $hr1 = isset($rqst['hr1']) ? ($rqst['hr1']) : '';
        $fecha_hr1 = isset($rqst['fecha_hr1']) ? ($rqst['fecha_hr1']) : '';
        $hr2 = isset($rqst['hr2']) ? ($rqst['hr2']) : '';
        $fecha_hr2 = isset($rqst['fecha_hr2']) ? ($rqst['fecha_hr2']) : '';
        $hr3 = isset($rqst['hr3']) ? ($rqst['hr3']) : '';
        $fecha_hr3 = isset($rqst['fecha_hr3']) ? ($rqst['fecha_hr3']) : '';
        $hr4 = isset($rqst['hr4']) ? ($rqst['hr4']) : '';
        $fecha_hr4 = isset($rqst['fecha_hr4']) ? ($rqst['fecha_hr4']) : '';
        $hr5 = isset($rqst['hr5']) ? ($rqst['hr5']) : '';
        $fecha_hr5 = isset($rqst['fecha_hr5']) ? ($rqst['fecha_hr5']) : '';
        
        $db = new DbConection();
        $pdo = $db->openConect();

        if ($tbl_municipio_id != "" && $hr1 !="" && $fecha_hr1 !="") {

                //Consultamos los datos de la vereda
                $q0 = "SELECT * FROM " . $db->getTable('tbl_vereda') . " WHERE id = " . $tbl_vereda_id;
                $result = $pdo->query($q0);
                if ($result) {
                    foreach ($result as $valor) {
                      if ($_SESSION['session_user']['es_gaula'] == 'no') {
                        if (!SessionData::superAdministrador()) {
                          if (intval($_SESSION['session_user']['tbl_batallon_id']) != $valor['tbl_batallon_id']) {
                            $db->closeConect();
                            return Util::error_general("Su Batallón no corresponde a la información que va a ingresar");
                          }
                        }
                      } 
                    }
                }
                
                $mensajeHrs = "RECUERDE QUE DEBE INGRESAR LOS ULTIMOS 5 DIGITOS DEL HR QUE ESTA REPORTANDO";
                if (!Util::isValidHr($hr1)) {
                  return Util::info_general( $mensajeHrs . " EN EL HR 1" );
                }
                if (!Util::isValidHr($hr2)) {
                  return Util::info_general( $mensajeHrs . " EN EL HR 2" );
                }
                if (!Util::isValidHr($hr3)) {
                  return Util::info_general( $mensajeHrs . " EN EL HR 3" );
                }
                if (!Util::isValidHr($hr4)) {
                  return Util::info_general( $mensajeHrs . " EN EL HR 4" );
                }
                if (!Util::isValidHr($hr5)) {
                  return Util::info_general( $mensajeHrs . " EN EL HR 5" );
                }

                $q = "INSERT INTO " . $db->getTable('tbl_operatividad') . " (created_at,  mdom, upm, sometimiento , dragas, motores, combates, explosivos, otras_sustancias, otras_maq, retro, vehiculos, proveedores, armas_cortas, capturas_gao, capturas_delco, bajas_delco, menores, municiones,comunicaciones, intendencia, lab_ch, capturas_gdo, hoja, dinero, solidos, fauna, dominio, semilleros, depositos,  presentaciones, campamentos, lab_pbc, tbl_usuario_id, tbl_departamento_id, tbl_municipio_id, tbl_vereda_id, pasta_coca,capturas_soc,madera,siembra, total, armas_largas, mercurio, mariguana, pasta_proceso, cloridrato, liquidos, opsic, semilleros_matas, minas, gaularadio, gaulareunion, gaulavol )
                VALUES ( " . Util::date_now_server() . ",  :mdom, :upm, :sometimiento , :dragas, :motores, :combates, :explosivos, :otras_sustancias, :otras_maq, :retro, :vehiculos, :proveedores, :armas_cortas, :capturas_gao, :capturas_delco, :bajas_delco, :menores, :municiones,:comunicaciones, :intendencia, :lab_ch, :capturas_gdo, :hoja, :dinero, :solidos, :fauna, :dominio, :semilleros, :depositos, :presentaciones, :campamentos, :lab_pbc, :tbl_usuario_id, :tbl_departamento_id, :tbl_municipio_id, :tbl_vereda_id, :pasta_coca,:capturas_soc,:madera,:siembra, :total, :armas_largas, :mercurio, :mariguana, :pasta_proceso, :cloridrato, :liquidos, :opsic, :semilleros_matas, :minas, :gaularadio, :gaulareunion, :gaulavol )";
                $result = $pdo->prepare($q);
                $arrparam = array(
                    ':mdom' => $mdom,
                    ':upm' => $upm,
                    ':sometimiento' => $sometimiento ,
                    ':capturas_delco' => $capturas_delco ,
                    ':bajas_delco' => $bajas_delco ,
                    ':menores' => $menores ,
                    ':dragas' => $dragas,
                    ':explosivos' => $explosivos,
                    ':lab_ch' => $lab_ch,
                    ':armas_cortas' => $armas_cortas,
                    ':motores' => $motores,
                    ':capturas_gao' => $capturas_gao,
                    ':presentaciones' => $presentaciones,
                    ':municiones' => $municiones,
                    ':combates' => $combates,
                    ':comunicaciones' => $comunicaciones,
                    ':intendencia' => $intendencia,
                    ':capturas_gdo' => $capturas_gdo,
                    ':semilleros' => $semilleros,
                    ':depositos' => $depositos,
                    ':campamentos' => $campamentos,
                    ':lab_pbc' => $lab_pbc,
                    ':tbl_usuario_id' => $tbl_usuario_id,
                    ':tbl_departamento_id' => $tbl_departamento_id,
                    ':tbl_municipio_id' => $tbl_municipio_id,
                    ':tbl_vereda_id' => $tbl_vereda_id,
                    ':pasta_coca' => $pasta_coca,
                    ':capturas_soc' => $capturas_soc,
                    ':madera' => $madera,
                    ':siembra' => $siembra,
                    ':total' => $total,
                    ':proveedores' => $proveedores,
                    ':armas_largas' => $armas_largas,
                    ':mercurio' => $mercurio,
                    ':mariguana' => $mariguana,
                    ':pasta_proceso' => $pasta_proceso,
                    ':cloridrato' => $cloridrato,
                    ':liquidos' => $liquidos,
                    ':vehiculos' => $vehiculos,
                    ':retro' => $retro,
                    ':otras_maq' => $otras_maq,
                    ':otras_sustancias' => $otras_sustancias,
                    ':dinero' => $dinero,
                    ':fauna' => $fauna,
                    ':dominio' => $dominio,
                    ':solidos' => $solidos,
                    ':hoja' => $hoja,
                    ':opsic' => $opsic,
                    ':semilleros_matas' => $semilleros_matas,
                    ':minas' => $minas,
                    ':gaularadio' => $gaularadio,
                    ':gaulavol' => $gaulavol,
                    ':gaulareunion' => $gaulareunion,
                  );

                if ($result->execute($arrparam)) {
                      
                      $lastInsertId = $pdo->lastInsertId();

                      // Información de Jurisdiccion
                      if( $resultado_jurisdiccion == 'no') {
                        $qJurisdiccion = "INSERT INTO " . $db->getTable('tbl_jurisdiccion_operatividad') . "  (created_at, tbl_operatividad_id, departamento_id, municipio_id, vereda_id, coordenadas) VALUES ( " . Util::date_now_server() . ", :tbl_operatividad_id, :departamento_id, :municipio_id, :vereda_id, :coordenadas)";
                        $resultJurisdiccion = $pdo->prepare($qJurisdiccion);
                        $arrparamJurisdiccion = array(
                          ':tbl_operatividad_id' => $lastInsertId,
                          ':departamento_id' => $tbl_departamento_id_jurisdiccion,
                          ':municipio_id' => $tbl_municipio_id_jurisdiccion,
                          ':vereda_id' => $tbl_vereda_id_jurisdiccion,
                          ':coordenadas' => $coordenadas
                        );
                        if (!$resultJurisdiccion->execute($arrparamJurisdiccion)) {
                          $db->closeConect();
                          return Util::error_general('Registrando informacion de Jurisdiccion');
                        }
                      }
                      // Fin Ingreso de Información de Jurisdiccion

                      // Ingreso de información de Hrs
                      $qHrs = "INSERT INTO " . $db->getTable('tbl_hrs_operatividad') . "  (created_at, tbl_operatividad_id, hr1, fecha_hr1, hr2, fecha_hr2, hr3, fecha_hr3, hr4, fecha_hr4, hr5, fecha_hr5) VALUES ( " . Util::date_now_server() . ", :tbl_operatividad_id, :hr1, :fecha_hr1, :hr2, :fecha_hr2, :hr3, :fecha_hr3, :hr4, :fecha_hr4, :hr5, :fecha_hr5)";
                      $resultHr = $pdo->prepare($qHrs);
                      $arrparamHr = array(
                        ':tbl_operatividad_id' => $lastInsertId,
                        ':hr1' => $hr1,
                        ':fecha_hr1' =>  $fecha_hr1,
                        ':hr2' => $hr2,
                        ':fecha_hr2' =>  $fecha_hr2,
                        ':hr3' => $hr3,
                        ':fecha_hr3' => $fecha_hr3,
                        ':hr4' => $hr4,
                        ':fecha_hr4' => $fecha_hr4,
                        ':hr5' => $hr5,
                        ':fecha_hr5' => $fecha_hr5
                      );

                      if (!$resultHr->execute($arrparamHr)) {
                        $db->closeConect();
                        return Util::error_general('Ingresando información de hrs');
                      }
                      // FIN Ingreso de información de Hrs

                    $arrjson = array('output' => array('valid' => true, 'response' => $lastInsertId));
                } else {
                    $arrjson = Util::error_general(' Guardar la Operatividad ');
                }
        } else {
            $arrjson = Util::error_missing_data();
        }
        $db->closeConect();
        return $arrjson;
    }
}
