<?php

/**
 * Clase que contiene todas las operaciones utilizadas sobre la base de datos
 * @author SPIDERSOFTWARE
 */
class Reportefe {

    public function __construct(){}

    public static function getAll($rqst){

        $id = isset($rqst['id']) ? intval($rqst['id']) : 0;

        $db = new DbConection();
        $pdo = $db->openConect();

        $q = "SELECT * FROM " . $db->getTable('tbl_reporte_fe');
        if ($id > 0) {
            $q = "SELECT tbl_reporte_fe.*,
            tbl_departamentos.departamento,
            tbl_departamentos.codigo_departamento,
            tbl_ciudades.codigo_muncipio,
            tbl_ciudades.municipio,
            tbl_ciudades.aap,
            tbl_ciudades.pdet,
            tbl_ciudades.zf,
            tbl_vereda.nombre_vereda as vereda
            FROM " .
            $db->getTable('tbl_reporte_fe') . ", " .
            $db->getTable('tbl_departamentos') . ", " .
            $db->getTable('tbl_ciudades') . ", " .
            $db->getTable('tbl_vereda') . "
            WHERE
            tbl_reporte_fe.tbl_departamento_id = tbl_departamentos.id AND
            tbl_reporte_fe.tbl_municipio_id = tbl_ciudades.id AND
            tbl_reporte_fe.tbl_vereda_id = tbl_vereda.id AND
            tbl_reporte_fe.id = " . $id;
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

    public static function loadDocumentPdf($documento){
        include_once "../../constants.php";
        if ($documento['size'] > 0) {
            if ($documento['error'] < 1) {
                $name_file = explode(".",$documento['name']);
                if (mb_strtolower($name_file[count($name_file) - 1]) == 'pdf') {
                    $ruta_img = WWW_ROOT_FE;
                    if (!file_exists($ruta_img)) {
                        mkdir($ruta_img, 0777, true);
                    }
                    $nombre_archivo = rand().'.'.$name_file[count($name_file) - 1];
                    if(move_uploaded_file($documento['tmp_name'], $ruta_img.$nombre_archivo)) {
                        return $nombre_archivo;
                    } else{
                        return null;
                    }
                } else {
                    return null;
                }
            } else {
                return null;
            }
        } else {
            return null;
        }
    }


    public static function loadPhoto($imagen){
        include_once "../../constants.php";
        if ($imagen['size'] > 0) {
            if ($imagen['error'] < 1) {
                $type_file = explode("/",$imagen['type']);
                if ($type_file['0'] == 'image') {
                    $ruta_img = WWW_ROOT_FE;
                    if (!file_exists($ruta_img)) {
                        mkdir($ruta_img, 0777, true);
                    }
                    $nombre_archivo = rand().'.'.$type_file['1'];
                    if(move_uploaded_file($imagen['tmp_name'], $ruta_img.$nombre_archivo)) {
                        return $nombre_archivo;
                    } else{
                        return null;
                    }
                } else {
                    return null;
                }
            } else {
                return null;
            }
        } else {
            return null;
        }
    }


    public static function save($rqst,$files = [])
    {
        $id = isset($rqst['id']) ? intval($rqst['id']) : 0;
        $tbl_departamento_id =  isset($rqst['tbl_departamento_id']) ? intval($rqst['tbl_departamento_id']) : 0; // llega el codigo del municipio
        $tbl_municipio_id =  isset($rqst['tbl_municipio_id']) ? intval($rqst['tbl_municipio_id']) : 0; // llega el codigo del municipio
        $tbl_vereda_id = isset($rqst['tbl_vereda_id']) ? ($rqst['tbl_vereda_id']) : '';
        $fecha = isset($rqst['fecha']) ? ($rqst['fecha']) : '';
        $latitud =  isset($rqst['latitud']) ? ($rqst['latitud']) : '';
        $entidades =  isset($rqst['entidades']) ? ($rqst['entidades']) : '';
        $pilar = isset($rqst['pilar']) ? ($rqst['pilar']) : '';
        $linea = isset($rqst['linea']) ? ($rqst['linea']) : '';
        $detalle_linea = isset($rqst['detalle_linea']) ? ($rqst['detalle_linea']) : '';
        $detalle_lugar = isset($rqst['detalle_lugar']) ? ($rqst['detalle_lugar']) : '';
        $participantes = isset($rqst['participantes']) ? ($rqst['participantes']) : '';
        $nombre_act = isset($rqst['nombre_act']) ? ($rqst['nombre_act']) : '';
        $estrategia = isset($rqst['estrategia']) ? ($rqst['estrategia']) : '';
        $unidad = isset($rqst['unidad']) ? ($rqst['unidad']) : '';
        $longitud = isset($rqst['longitud']) ? ($rqst['longitud']) : '';
        $actividad = isset($rqst['actividad']) ? ($rqst['actividad']) : '';
        $costo = isset($rqst['costo']) ? ($rqst['costo']) : '';
        $beneficiadas = isset($rqst['beneficiadas']) ? ($rqst['beneficiadas']) : '';
        $responsable = isset($rqst['responsable']) ? ($rqst['responsable']) : '';
        $vereda_id = isset($rqst['vereda_id']) ? ($rqst['vereda_id']) : '';
        $descripcion_actividad = isset($rqst['descripcion_actividad']) ? ($rqst['descripcion_actividad']) : '';
        $proxima_reunion = isset($rqst['proxima_reunion']) ? ($rqst['proxima_reunion']) : '';
        $tbl_usuario_id = $_SESSION['session_user']['id'];
        $foto_fe = isset($files["foto_fe"]) ? Reportefe::loadPhoto($files["foto_fe"]) : null;
        $foto_fe2 = isset($files["foto_fe2"]) ? Reportefe::loadPhoto($files["foto_fe2"]) : null;
        $archivo_fe = isset($files["archivo_fe"]) ? Reportefe::loadDocumentPdf($files["archivo_fe"]) : null;

        $aplica_vereda = isset($rqst['aplica_vereda']) ? ($rqst['aplica_vereda']) : '';

        $db = new DbConection();
        $pdo = $db->openConect();

        //Consultamos los datos del municipio
        $q = "SELECT id, codigo_departamento FROM " . $db->getTable('tbl_ciudades') . " WHERE codigo_muncipio = " . $tbl_municipio_id;
        $result = $pdo->query($q);
        if ($result) {
            foreach ($result as $valor) {
              $tbl_municipio_id = $valor['id'];
            }
        }

        $q0 = "SELECT id FROM " . $db->getTable('tbl_departamentos') . " WHERE codigo_departamento = " . $tbl_departamento_id;
        $result0 = $pdo->query($q0);
        $tbl_departamento_id = 0;
        if ($result0) {
            foreach ($result0 as $valor0) {
              $tbl_departamento_id = $valor0['id']; // Remplazamos el codigo del municpio y ponemos el Id
            }
        }

        $q1 = "SELECT id FROM " . $db->getTable('tbl_vereda') . " WHERE nombre_vereda = '$tbl_vereda_id' ";
        $result1 = $pdo->query($q1);
        if ($result1) {
            foreach ($result1 as $valor0) {
              $tbl_vereda_id = $valor0['id']; // Remplazamos el codigo del municpio y ponemos el Id
            }
        }

        if($aplica_vereda === 'no'){
            $tbl_vereda_id = 0;
        }

        if ($id > 0) {
            //actualiza la informacion
            $q = "SELECT id FROM " . $db->getTable('tbl_reporte_fe') . " WHERE id = " . $id;
            $result = $pdo->query($q);
            if ($result) {
                $table = $db->getTable('tbl_reporte_fe');
                $arrfieldscomma = array(
                    'fecha' => $fecha,
                    'latitud' => $latitud,
                    'entidades' => $entidades,
                    'pilar' => $pilar,
                    'linea' => $linea,
                    'detalle_linea' => $detalle_linea,
                    'estrategia' => $estrategia,
                    'unidad' => $unidad,
                    'detalle_lugar' => $detalle_lugar,
                    'longitud' => $longitud ,
                    'actividad' => $actividad ,
                    'beneficiadas' => $beneficiadas ,
                    'costo' => $costo ,
                    'nombre_act' => $nombre_act ,
                    'responsable' => $responsable,
                    'vereda_id' => $vereda_id ,
                    'participantes' => $participantes ,
                    'descripcion_actividad' => $descripcion_actividad ,
                    'tbl_usuario_id' => $tbl_usuario_id,
                    'tbl_departamento_id' => $tbl_departamento_id,
                    'tbl_municipio_id' => $tbl_municipio_id,
                    'proxima_reunion' => $proxima_reunion,
                    'tbl_vereda_id' => $tbl_vereda_id);
                if (!is_null($foto_fe)) {
                    $arrfieldscomma["imagen"] = $foto_fe;
                }
                if (!is_null($foto_fe2)) {
                    $arrfieldscomma["imagen_2"] = $foto_fe2;
                }
                if (!is_null($archivo_fe)) {
                    $arrfieldscomma["archivo"] = $archivo_fe;
                }
                $arrfieldsnocomma = array('dtcreate' => Util::date_now_server());
                $q = Util::make_query_update($table, "id = '$id'", $arrfieldscomma, $arrfieldsnocomma);
                $result = $pdo->query($q);
                if(!$result){
                    $arrjson = Util::error_general('Actualizando los datos de reporte de fe');
                }else{
                    $arrjson = array('output' => array('valid' => true, 'id' => $id));
                }
            } else {
                $arrjson = Util::error_general();
            }
        } else {
            if ($tbl_departamento_id > 0 || $tbl_municipio_id > 0 ||  $tbl_vereda_id > 0 || $nombre_act != "" ||  $latitud !="" || $pilar !="" || $unidad !="" || $actividad !="") {
                $q = "INSERT INTO " . $db->getTable('tbl_reporte_fe') . " (created_at, fecha, latitud, entidades, pilar, linea, participantes, detalle_linea, estrategia, unidad, longitud, detalle_lugar, actividad, costo, beneficiadas,  nombre_act, responsable, descripcion_actividad, tbl_usuario_id, tbl_departamento_id, tbl_municipio_id, tbl_vereda_id, proxima_reunion,imagen,imagen_2,archivo)
                VALUES ( " . Util::date_now_server() . ", :fecha, :latitud, :entidades, :pilar, :linea, :participantes,:detalle_linea, :estrategia, :unidad, :longitud, :detalle_lugar, :actividad, :costo, :beneficiadas, :nombre_act, :responsable, :descripcion_actividad, :tbl_usuario_id, :tbl_departamento_id, :tbl_municipio_id, :tbl_vereda_id, :proxima_reunion,:imagen,:imagen_2,:archivo)";
                $result = $pdo->prepare($q);
                $arrparam = array(':fecha' => $fecha,
                    ':latitud' => $latitud,
                    ':entidades' => $entidades,
                    ':pilar' => $pilar,
                    ':linea' => $linea,
                    ':detalle_linea' => $detalle_linea,
                    ':detalle_lugar' => $detalle_lugar,
                    ':estrategia' => $estrategia,
                    ':participantes' => $participantes,
                    ':unidad' => $unidad,
                    ':nombre_act' => $nombre_act,
                    ':longitud' => $longitud,
                    ':actividad' => $actividad,
                    ':costo' => $costo,
                    ':beneficiadas' => $beneficiadas,
                    ':responsable' => $responsable,
                    ':descripcion_actividad' => $descripcion_actividad,
                    ':tbl_usuario_id' => $tbl_usuario_id,
                    ':tbl_departamento_id' => $tbl_departamento_id,
                    ':tbl_municipio_id' => $tbl_municipio_id,
                    ':tbl_vereda_id' => $tbl_vereda_id,
                    ':proxima_reunion' => $proxima_reunion,
                    ':imagen' => $foto_fe,
                    ':imagen_2' => $foto_fe2,
                    ':archivo' => $archivo_fe,
                  );
                if ($result->execute($arrparam)) {
                    $arrjson = array('output' => array('valid' => true, 'response' => $pdo->lastInsertId()));
                } else {
                    var_dump($arrparam);
                    var_dump($foto_fe);
                    var_dump($archivo_fe);
                    var_dump($result->errorInfo());
                    $arrjson = Util::error_general(' Al guardar los datos de reportes de fe');
                }
            } else {
                $arrjson = Util::error_missing_data();
            }
        }
        $db->closeConect();
        return $arrjson;
    }
}
