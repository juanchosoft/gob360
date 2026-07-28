<?php

/**
 * Clase que contiene todas las operaciones utilizadas sobre la base de datos
 * @author SPIDERSOFTWARE
 */
class IngresoPae
{

    
    public function __construct() {}



    public static function getIngresoPaeByMunicipioCodigo($rqst)
    {

        $tbl_municipio_id = isset($rqst['tbl_municipio_id']) ? intval($rqst['tbl_municipio_id']) : 0;

    
        $db = new DbConection();
        $pdo = $db->openConect();


            $q="SELECT tbl_pae.*, tbl_sede_educativa.nombre, tbl_sede_educativa.codigo_sede, tbl_sede_educativa.longitud, tbl_sede_educativa.latitud, tbl_sede_educativa.icono, tbl_instituciones_educativas.nombre_institucion, tbl_instituciones_educativas.codigo_institucion, tbl_ciudades_accion_unificada.codigo_muncipio, tbl_vereda.nombre_veredatbl_usuarios.nombre AS Usuario
            FROM " . $db->getTable('tbl_pae') . "
            LEFT JOIN " . $db->getTable('tbl_instituciones_educativas') . "  ON tbl_pae.tbl_instituciones_educativas_id = tbl_instituciones_educativas.id 
            LEFT JOIN " . $db->getTable('tbl_sede_educativa') . "  ON tbl_pae.tbl_sede_educativa_id = tbl_sede_educativa.id 
            LEFT JOIN " . $db->getTable('tbl_ciudades_accion_unificada') . "  ON tbl_sede_educativa.tbl_ciudad_id = tbl_ciudades_accion_unificada.codigo_muncipio 
            LEFT JOIN " . $db->getTable('tbl_vereda') . " ON tbl_sede_educativa.tbl_vereda_id = tbl_vereda.id
            LEFT JOIN " . $db->getTable('tbl_usuarios') . " ON tbl_pae.tbl_usuario_id = tbl_usuarios.id
            WHERE tbl_pae.tbl_municipio_id = $tbl_municipio_id GROUP BY tbl_pae.id";       

     
                  
          
        try {
            $stmt = $pdo->prepare($q);
            $stmt->execute($params);
            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
            $db->closeConect();
    
            return [
                'output' => [
                    'valid' => true,
                    'response' => $result
                ]
            ];
        } catch (PDOException $e) {
            $db->closeConect();
            return Util::error_general('Error en la consulta: ' . $e->getMessage());
        }
    }



    public static function getAll($rqst)
    {
        $id = isset($rqst['id']) ? intval($rqst['id']) : 0;
        $tbl_municipio_id = isset($rqst['tbl_municipio_id']) ? intval($rqst['tbl_municipio_id']) : 0;
    
        $db = new DbConection();
        $pdo = $db->openConect();
    

            $q="SELECT tbl_pae.*, tbl_sede_educativa.nombre, tbl_sede_educativa.codigo_sede, tbl_sede_educativa.longitud, tbl_sede_educativa.latitud, tbl_sede_educativa.icono, tbl_instituciones_educativas.nombre_institucion, tbl_instituciones_educativas.codigo_institucion, tbl_ciudades_accion_unificada.codigo_muncipio, tbl_vereda.nombre_veredatbl_usuarios.nombre AS Usuario
            FROM " . $db->getTable('tbl_pae') . "
            LEFT JOIN " . $db->getTable('tbl_instituciones_educativas') . "  ON tbl_pae.tbl_instituciones_educativas_id = tbl_instituciones_educativas.id 
            LEFT JOIN " . $db->getTable('tbl_sede_educativa') . "  ON tbl_pae.tbl_sede_educativa_id = tbl_sede_educativa.id 
            LEFT JOIN " . $db->getTable('tbl_ciudades_accion_unificada') . "  ON tbl_sede_educativa.tbl_ciudad_id = tbl_ciudades_accion_unificada.codigo_muncipio 
            LEFT JOIN " . $db->getTable('tbl_vereda') . " ON tbl_sede_educativa.tbl_vereda_id = tbl_vereda.id
            LEFT JOIN " . $db->getTable('tbl_usuarios') . " ON tbl_pae.tbl_usuario_id = tbl_usuarios.id
            WHERE 1 = 1 GROUP BY tbl_sede_educativa.id";


      

            if($id > 0){
            $q="SELECT tbl_pae.*, tbl_sede_educativa.nombre, tbl_sede_educativa.codigo_sede, tbl_sede_educativa.longitud, tbl_sede_educativa.latitud, tbl_sede_educativa.icono, tbl_instituciones_educativas.nombre_institucion, tbl_instituciones_educativas.codigo_institucion, tbl_ciudades_accion_unificada.codigo_muncipio, tbl_vereda.nombre_veredatbl_usuarios.nombre AS Usuario
            FROM " . $db->getTable('tbl_pae') . "
            LEFT JOIN " . $db->getTable('tbl_instituciones_educativas') . "  ON tbl_pae.tbl_instituciones_educativas_id = tbl_instituciones_educativas.id 
            LEFT JOIN " . $db->getTable('tbl_sede_educativa') . "  ON tbl_pae.tbl_sede_educativa_id = tbl_sede_educativa.id 
            LEFT JOIN " . $db->getTable('tbl_ciudades_accion_unificada') . "  ON tbl_sede_educativa.tbl_ciudad_id = tbl_ciudades_accion_unificada.codigo_muncipio 
            LEFT JOIN " . $db->getTable('tbl_vereda') . " ON tbl_sede_educativa.tbl_vereda_id = tbl_vereda.id
            LEFT JOIN " . $db->getTable('tbl_usuarios') . " ON tbl_pae.tbl_usuario_id = tbl_usuarios.id
                WHERE  tbl_pae.id = $id
                GROUP BY tbl_sede_educativa.id";
            }
  
            try {
                $stmt = $pdo->prepare($q);
                $stmt->execute($params);
                $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
                $db->closeConect();
        
                return [
                    'output' => [
                        'valid' => true,
                        'response' => $result
                    ]
                ];
            } catch (PDOException $e) {
                $db->closeConect();
                return Util::error_general('Error en la consulta: ' . $e->getMessage());
            }
        }
    




    public static function foto ($rqst)
{
            
        // guardar_foto.php
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['foto'])) {
            $id_documento = $_POST['id_documento'] ?? null;
            
            if (!$id_documento) {
                echo json_encode(['valid' => false, 'message' => 'Falta ID del documento']);
                exit;
            }

            $ruta = 'fotos/';
            if (!file_exists($ruta)) {
                mkdir($ruta, 0777, true);
            }

            $nombreArchivo = $ruta . 'foto_' . $id_documento . '_' . time() . '.jpg';

            if (move_uploaded_file($_FILES['foto']['tmp_name'], $nombreArchivo)) {
                echo json_encode(['valid' => true, 'message' => 'Foto guardada correctamente', 'path' => $nombreArchivo]);
            } else {
                echo json_encode(['valid' => false, 'message' => 'No se pudo guardar la imagen']);
            }
            exit;
        }

        }


    public static function save($rqst)
    {
        $id = isset($rqst['id']) ? intval($rqst['id']) : 0;
        $date = isset($rqst['date']) ? ($rqst['date']) : '';
        $provincia = isset($rqst['provincia']) ? ($rqst['provincia']) : '';
        $tbl_departamento_id = isset($rqst['tbl_departamento_id']) ? ($rqst['tbl_departamento_id']) : '';
        $tbl_municipio_id =  isset($rqst['tbl_municipio_id']) ? ($rqst['tbl_municipio_id']) : '';
        $tbl_sede_educativa_id = isset($rqst['tbl_sede_educativa_id']) ? ($rqst['tbl_sede_educativa_id']) : '';
        $tbl_instituciones_educativas_id = isset($rqst['tbl_instituciones_educativas_id']) ? ($rqst['tbl_instituciones_educativas_id']) : '';
        $rector = isset($rqst['rector']) ? ($rqst['rector']) : '';
        $cc = isset($rqst['cc']) ? ($rqst['cc']) : '';
        $tel = isset($rqst['tel']) ? ($rqst['tel']) : '';
        $sector = isset($rqst['sector']) ? ($rqst['sector']) : '';
        $atencion_mayor_indigena = isset($rqst['atencion_mayor_indigena']) ? ($rqst['atencion_mayor_indigena']) : '';
        $complemento_ampm = isset($rqst['complemento_ampm']) ? ($rqst['complemento_ampm']) : '';
        $complemento_ampm_indus = isset($rqst['complemento_ampm_indus']) ? ($rqst['complemento_ampm_indus']) : '';
        $preparado_sitio = isset($rqst['preparado_sitio']) ? ($rqst['preparado_sitio']) : '';
        $comida_transportada = isset($rqst['comida_transportada']) ? ($rqst['comida_transportada']) : '';
        $comedor_escolar = isset($rqst['comedor_escolar']) ? ($rqst['comedor_escolar']) : '';
        $concepto_sanitario = isset($rqst['concepto_sanitario']) ? ($rqst['concepto_sanitario']) : '';
        $fecha_expedicion = isset($rqst['fecha_expedicion']) ? ($rqst['fecha_expedicion']) : '';
        $cerca_a_contaminacion = isset($rqst['cerca_a_contaminacion']) ? ($rqst['cerca_a_contaminacion']) : '';
        $sede_conflicto_armado = isset($rqst['sede_conflicto_armado']) ? ($rqst['sede_conflicto_armado']) : '';
        $frecuencia_conflicto = isset($rqst['frecuencia_conflicto']) ? ($rqst['frecuencia_conflicto']) : '';
        $espacio_almacenamiento = isset($rqst['espacio_almacenamiento']) ? ($rqst['espacio_almacenamiento']) : '';
        $material_techo = isset($rqst['material_techo']) ? ($rqst['material_techo']) : '';
        $material_piso = isset($rqst['material_piso']) ? ($rqst['material_piso']) : '';
        $material_paredes = isset($rqst['material_paredes']) ? ($rqst['material_paredes']) : '';
        $espacio_dedicado = isset($rqst['espacio_dedicado']) ? ($rqst['espacio_dedicado']) : '';
        $material_techo_preparacion = isset($rqst['material_techo_preparacion']) ? ($rqst['material_techo_preparacion']) : '';
        $material_piso_preparacion = isset($rqst['material_piso_preparacion']) ? ($rqst['material_piso_preparacion']) : '';
        $material_paredes_preparacion = isset($rqst['material_paredes_preparacion']) ? ($rqst['material_paredes_preparacion']) : '';
        $espacio_consumo = isset($rqst['espacio_consumo']) ? ($rqst['espacio_consumo']) : '';
        $acceso_electricidad = isset($rqst['acceso_electricidad']) ? ($rqst['acceso_electricidad']) : '';
        $acceso_agua = isset($rqst['acceso_agua']) ? ($rqst['acceso_agua']) : '';
        $tipo_agua = isset($rqst['tipo_agua']) ? ($rqst['tipo_agua']) : '';
        $tipo_gas = isset($rqst['tipo_gas']) ? ($rqst['tipo_gas']) : '';
        $tiene_recoleccion_basura = isset($rqst['tiene_recoleccion_basura']) ? ($rqst['tiene_recoleccion_basura']) : '';
        $disposicion_derechos_pae = isset($rqst['disposicion_derechos_pae']) ? ($rqst['disposicion_derechos_pae']) : '';
        $disposicion_no_organicos_pae = isset($rqst['disposicion_no_organicos_pae']) ? ($rqst['disposicion_no_organicos_pae']) : '';
        $clasificacion_residuos = isset($rqst['clasificacion_residuos']) ? ($rqst['clasificacion_residuos']) : '';
        $neveras_almacenamiento = isset($rqst['neveras_almacenamiento']) ? ($rqst['neveras_almacenamiento']) : '';
        $cant_neveras = isset($rqst['cant_neveras']) ? ($rqst['cant_neveras']) : '';
        $neveras_funcionando = isset($rqst['neveras_funcionando']) ? ($rqst['neveras_funcionando']) : '';
        $tamano_neveras_principales = isset($rqst['tamano_neveras_principales']) ? ($rqst['tamano_neveras_principales']) : '';
        $congeladores = isset($rqst['congeladores']) ? ($rqst['congeladores']) : '';
        $cantidad_congeladores = isset($rqst['cantidad_congeladores']) ? ($rqst['cantidad_congeladores']) : '';
        $congeladores_funcionando = isset($rqst['congeladores_funcionando']) ? ($rqst['congeladores_funcionando']) : '';
        $tamano_congelador = isset($rqst['tamano_congelador']) ? ($rqst['tamano_congelador']) : '';
        $almacena_alto_suelo = isset($rqst['almacena_alto_suelo']) ? ($rqst['almacena_alto_suelo']) : '';
        $elementos_almacenamiento = isset($rqst['elementos_almacenamiento']) ? ($rqst['elementos_almacenamiento']) : '';
        $estufa = isset($rqst['estufa']) ? ($rqst['estufa']) : '';
        $cant_estufas = isset($rqst['cant_estufas']) ? ($rqst['cant_estufas']) : '';
        $cant_quemadores = isset($rqst['cant_quemadores']) ? ($rqst['cant_quemadores']) : '';
        $cant_quemadores_buenos = isset($rqst['cant_quemadores_buenos']) ? ($rqst['cant_quemadores_buenos']) : '';
        $licuadora = isset($rqst['licuadora']) ? ($rqst['licuadora']) : '';
        $cant_licuadora = isset($rqst['cant_licuadora']) ? ($rqst['cant_licuadora']) : '';
        $cant_licuadoras_buenas = isset($rqst['cant_licuadoras_buenas']) ? ($rqst['cant_licuadoras_buenas']) : '';
        $cant_licuadoras_ind = isset($rqst['cant_licuadoras_ind']) ? ($rqst['cant_licuadoras_ind']) : '';
        $posee_ollas_pae = isset($rqst['posee_ollas_pae']) ? ($rqst['posee_ollas_pae']) : '';
        $posee_cuchillos_pae = isset($rqst['posee_cuchillos_pae']) ? ($rqst['posee_cuchillos_pae']) : '';
        $posee_cucharones_pae = isset($rqst['posee_cucharones_pae']) ? ($rqst['posee_cucharones_pae']) : '';
        $cant_ninos_pae_sentados = isset($rqst['cant_ninos_pae_sentados']) ? ($rqst['cant_ninos_pae_sentados']) : '';
        $cant_platos_pae = isset($rqst['cant_platos_pae']) ? ($rqst['cant_platos_pae']) : '';
        $pocillos_pae = isset($rqst['pocillos_pae']) ? ($rqst['pocillos_pae']) : '';
        $cucharas_pae = isset($rqst['cucharas_pae']) ? ($rqst['cucharas_pae']) : '';
        $posee_sanitario_exclusivo_pae = isset($rqst['posee_sanitario_exclusivo_pae']) ? ($rqst['posee_sanitario_exclusivo_pae']) : '';
        $posee_lavamanos_pae = isset($rqst['posee_lavamanos_pae']) ? ($rqst['posee_lavamanos_pae']) : '';
        $estado_sede = isset($rqst['estado_sede']) ? ($rqst['estado_sede']) : '';
        $estado_techo_almacenamiento = isset($rqst['estado_techo_almacenamiento']) ? ($rqst['estado_techo_almacenamiento']) : '';
        $estado_piso_almacenamiento = isset($rqst['estado_piso_almacenamiento']) ? ($rqst['estado_piso_almacenamiento']) : '';
        $estado_paredes_almacenamiento = isset($rqst['estado_paredes_almacenamiento']) ? ($rqst['estado_paredes_almacenamiento']) : '';
        $estado_techo_preparacion = isset($rqst['estado_techo_preparacion']) ? ($rqst['estado_techo_preparacion']) : '';
        $estado_piso_preparacion = isset($rqst['estado_piso_preparacion']) ? ($rqst['estado_piso_preparacion']) : '';
        $material_piso_preparacion = isset($rqst['material_piso_preparacion']) ? ($rqst['material_piso_preparacion']) : '';
        $cargue_info = isset($rqst['cargue_info']) ? ($rqst['cargue_info']) : '';
        $clasificacion = isset($rqst['clasificacion']) ? ($rqst['clasificacion']) : '';
        $sede_alcantarillado = isset($rqst['sede_alcantarillado']) ? ($rqst['sede_alcantarillado']) : '';
        $visita = isset($rqst['visita']) ? ($rqst['visita']) : '';
        $ano = isset($rqst['ano']) ? ($rqst['ano']) : '';
        $latitud = isset($rqst['latitud']) ? ($rqst['latitud']) : '';
        $longitud = isset($rqst['longitud']) ? ($rqst['longitud']) : '';
        $observaciones = isset($rqst['observaciones']) ? ($rqst['observaciones']) : '';
        $estado_paredes_preparacion = isset($rqst['estado_paredes_preparacion']) ? ($rqst['estado_paredes_preparacion']) : '';
        $tenedores_pae = isset($rqst['tenedores_pae']) ? ($rqst['tenedores_pae']) : '';
        $cant_canecas = isset($rqst['cant_canecas']) ? ($rqst['cant_canecas']) : '';
        $ninos_focalizados = isset($rqst['ninos_focalizados']) ? ($rqst['ninos_focalizados']) : '';
        $disposicion_desechos_pae = isset($rqst['disposicion_desechos_pae']) ? ($rqst['disposicion_desechos_pae']) : '';
        $tbl_user_id = $_SESSION['session_user']['id'];

        // Firma digital
        $firma = isset($rqst['firma']) ? ($rqst['firma']) : '';
        $filename = '';
        if ($firma != "") {
            // Limpiar el encabezado de data:image para convertir la firma a imagen
            $data = explode(',', $firma);
            $encodedSignature = $data[1];

            // Decodificar la imagen base64
            $decodedSignature = base64_decode($encodedSignature);

            // Nombre del archivo
            $filename = 'signature_' . time() . '.png';
            $route = '../firmas/' . $filename;

            // Guardar la imagen
            if (!file_put_contents($route, $decodedSignature)) {
                return Util::error_general("Error guardando la firma digital");
            }
        } else {
            return Util::error_general("Por favor, firma antes de guardar.");
        }
        $firma = $filename;

        $db = new DbConection();
        $pdo = $db->openConect();


        if ($id > 0) {
            //actualiza la informacion
            $q = "SELECT id FROM " . $db->getTable('tbl_pae') . " WHERE id = " . $id;
            $result = $pdo->query($q);
            if ($result) {
                $table = $db->getTable('tbl_pae');
                $arrfieldscomma = array(
                    'firma' => $firma,
                    'date' => $date,
                    'provincia' => $provincia,
                    'tbl_departamento_id' => $tbl_departamento_id,
                    'tbl_municipio_id' => $tbl_municipio_id,
                    'tbl_sede_educativa_id' => $tbl_sede_educativa_id,                    
                    'tbl_instituciones_educativas_id' => $tbl_instituciones_educativas_id,
                    'rector' => $rector,
                    'cc' => $cc,
                    'tel' => $tel,
                    'sector' => $sector,
                    'atencion_mayor_indigena' => $atencion_mayor_indigena,
                    'complemento_ampm' => $complemento_ampm,
                    'complemento_ampm_indus' => $complemento_ampm_indus,
                    'preparado_sitio' => $preparado_sitio,
                    'comida_transportada' => $comida_transportada,
                    'comedor_escolar' => $comedor_escolar,
                    'concepto_sanitario' => $concepto_sanitario,
                    'fecha_expedicion' => $fecha_expedicion,
                    'cerca_a_contaminacion' => $cerca_a_contaminacion,
                    'sede_conflicto_armado' => $sede_conflicto_armado,
                    'frecuencia_conflicto' => $frecuencia_conflicto,
                    'espacio_almacenamiento' => $espacio_almacenamiento,
                    'material_techo' => $material_techo,
                    'material_piso' => $material_piso,
                    'material_paredes' => $material_paredes,
                    'espacio_dedicado' => $espacio_dedicado,
                    'material_techo_preparacion' => $material_techo_preparacion,
                    'material_piso_preparacion' => $material_piso_preparacion,
                    'material_paredes_preparacion' => $material_paredes_preparacion,
                    'espacio_consumo' => $espacio_consumo,
                    'acceso_electricidad' => $acceso_electricidad,
                    'acceso_agua' => $acceso_agua,
                    'tipo_agua' => $tipo_agua,
                    'tipo_gas' => $tipo_gas,
                    'tiene_recoleccion_basura' => $tiene_recoleccion_basura,
                    'disposicion_derechos_pae' => $disposicion_derechos_pae,
                    'disposicion_no_organicos_pae' => $disposicion_no_organicos_pae,
                    'clasificacion_residuos' => $clasificacion_residuos,
                    'neveras_almacenamiento' => $neveras_almacenamiento,
                    'cant_neveras' => $cant_neveras,
                    'neveras_funcionando' => $neveras_funcionando,
                    'tamano_neveras_principales' => $tamano_neveras_principales,
                    'congeladores' => $congeladores,
                    'cantidad_congeladores' => $cantidad_congeladores,
                    'congeladores_funcionando' => $congeladores_funcionando,
                    'tamano_congelador' => $tamano_congelador,
                    'almacena_alto_suelo' => $almacena_alto_suelo,
                    'elementos_almacenamiento' => $elementos_almacenamiento,
                    'estufa' => $estufa,
                    'cant_estufas' => $cant_estufas,
                    'cant_quemadores' => $cant_quemadores,
                    'cant_quemadores_buenos' => $cant_quemadores_buenos,
                    'licuadora' => $licuadora,
                    'cant_licuadora' => $cant_licuadora,
                    'cant_licuadoras_buenas' => $cant_licuadoras_buenas,
                    'cant_licuadoras_ind' => $cant_licuadoras_ind,
                    'posee_ollas_pae' => $posee_ollas_pae,
                    'posee_cuchillos_pae' => $posee_cuchillos_pae,
                    'posee_cucharones_pae' => $posee_cucharones_pae,
                    'cant_ninos_pae_sentados' => $cant_ninos_pae_sentados,
                    'cant_platos_pae' => $cant_platos_pae,
                    'pocillos_pae' => $pocillos_pae,
                    'cucharas_pae' => $cucharas_pae,
                    'posee_sanitario_exclusivo_pae' => $posee_sanitario_exclusivo_pae,
                    'posee_lavamanos_pae' => $posee_lavamanos_pae,
                    'date' => $date,
                    'tbl_user_id' => $tbl_user_id,
                    'estado_sede' => $estado_sede,
                    'estado_techo_almacenamiento' => $estado_techo_almacenamiento,
                    'estado_piso_almacenamiento' => $estado_piso_almacenamiento,
                    'estado_paredes_almacenamiento' => $estado_paredes_almacenamiento,
                    'estado_techo_preparacion' => $estado_techo_preparacion,
                    'estado_piso_preparacion' => $estado_piso_preparacion,                    
                    'material_paredes_preparacion' => $material_paredes_preparacion,
                    'cargue_info' => $cargue_info,
                    'clasificacion' => $clasificacion,
                    'sede_alcantarillado' => $sede_alcantarillado,
                    'visita' => $visita,
                    'ano' => $ano,
                    'latitud' => $latitud,
                    'longitud' => $longitud,
                    'observaciones' => $observaciones,                 
                    'ninos_focalizados' => $ninos_focalizados,
                    'tenedores_pae' => $tenedores_pae,
                    'cant_canecas' => $cant_canecas,
                    'disposicion_desechos_pae' => $disposicion_desechos_pae,

                    
                );
                $arrfieldsnocomma = array('update_at' => Util::date_now_server());
                $q = Util::make_query_update($table, "id = '$id'", $arrfieldscomma, $arrfieldsnocomma);
                $result = $pdo->query($q);
                if (!$result) {
                    $arrjson = Util::error_general('Actualizando los datos del batallon');
                } else {
                    $arrjson = array('output' => array('valid' => true, 'id' => $id));
                }
            } else {
                $arrjson = Util::error_general();
            }
        } else {
            if ($provincia> 0) {
                $q = "INSERT INTO " . $db->getTable('tbl_pae') . " (created_at, date, provincia, tbl_municipio_id, tbl_sede_educativa_id, tbl_instituciones_educativas_id, rector, cc, tel, sector, atencion_mayor_indigena, 
                complemento_ampm, complemento_ampm_indus, preparado_sitio, comida_transportada, comedor_escolar, concepto_sanitario, fecha_expedicion, cerca_a_contaminacion, sede_conflicto_armado, frecuencia_conflicto, espacio_almacenamiento,  material_techo, 
                material_piso, material_paredes,  espacio_dedicado, material_techo_preparacion, material_piso_preparacion, material_paredes_preparacion,espacio_consumo, acceso_electricidad, acceso_agua, tipo_agua, tipo_gas, tiene_recoleccion_basura, 
                disposicion_derechos_pae, disposicion_no_organicos_pae, clasificacion_residuos, neveras_almacenamiento, cant_neveras, neveras_funcionando, tamano_neveras_principales, congeladores, cantidad_congeladores, congeladores_funcionando, 
                tamano_congelador, almacena_alto_suelo, elementos_almacenamiento, estufa, cant_estufas, cant_quemadores, cant_quemadores_buenos, licuadora, cant_licuadora, cant_licuadoras_buenas, cant_licuadoras_ind, posee_ollas_pae, posee_cuchillos_pae, 
                posee_cucharones_pae, cant_ninos_pae_sentados, cant_platos_pae, pocillos_pae, cucharas_pae, posee_sanitario_exclusivo_pae, posee_lavamanos_pae, tbl_user_id, estado_sede, estado_techo_almacenamiento, estado_piso_almacenamiento, 
                estado_paredes_almacenamiento, estado_techo_preparacion, estado_piso_preparacion, cargue_info, clasificacion, sede_alcantarillado, visita, ano, latitud, longitud, observaciones, ninos_focalizados, tenedores_pae,cant_canecas, estado_paredes_preparacion, disposicion_desechos_pae, firma )
                
                VALUES ( " . Util::date_now_server() . ", :date, :provincia,  :tbl_municipio_id, :tbl_sede_educativa_id, :tbl_instituciones_educativas_id, :rector, :cc, :tel, :sector, :atencion_mayor_indigena, 
                :complemento_ampm, :complemento_ampm_indus, :preparado_sitio, :comida_transportada, :comedor_escolar, :concepto_sanitario, :fecha_expedicion, :cerca_a_contaminacion, :sede_conflicto_armado, :frecuencia_conflicto, :espacio_almacenamiento, :material_techo, 
                :material_piso, :material_paredes,  :espacio_dedicado, :material_techo_preparacion, :material_piso_preparacion, :material_paredes_preparacion, :espacio_consumo, :acceso_electricidad, :acceso_agua, :tipo_agua, :tipo_gas, :tiene_recoleccion_basura, 
                :disposicion_derechos_pae, :disposicion_no_organicos_pae, :clasificacion_residuos, :neveras_almacenamiento, :cant_neveras, :neveras_funcionando, :tamano_neveras_principales, :congeladores, :cantidad_congeladores, :congeladores_funcionando, :tamano_congelador,
                :almacena_alto_suelo, :elementos_almacenamiento, :estufa, :cant_estufas, :cant_quemadores, :cant_quemadores_buenos, :licuadora, :cant_licuadora, :cant_licuadoras_buenas, :cant_licuadoras_ind, :posee_ollas_pae, :posee_cuchillos_pae, :posee_cucharones_pae, 
                :cant_ninos_pae_sentados, :cant_platos_pae, :pocillos_pae, :cucharas_pae, :posee_sanitario_exclusivo_pae, :posee_lavamanos_pae, :tbl_user_id, :estado_sede, :estado_techo_almacenamiento, :estado_piso_almacenamiento, 
                :estado_paredes_almacenamiento, :estado_techo_preparacion, :estado_piso_preparacion, :cargue_info, :clasificacion, :sede_alcantarillado, :visita, :ano, :latitud, :longitud, :observaciones, :ninos_focalizados, :tenedores_pae, :cant_canecas, :estado_paredes_preparacion, :disposicion_desechos_pae, :firma )";
                $result = $pdo->prepare($q);
                $arrparam = array(
                    ':date' => $date,
                    ':provincia' => $provincia,                   
                    ':tbl_municipio_id' => $tbl_municipio_id,
                    ':tbl_sede_educativa_id' => $tbl_sede_educativa_id,
                    ':tbl_instituciones_educativas_id' => $tbl_instituciones_educativas_id,
                    ':rector' => $rector,
                    ':cc' => $cc,
                    ':tel' => $tel,
                    ':sector' => $sector,
                    ':atencion_mayor_indigena' => $atencion_mayor_indigena,
                    ':complemento_ampm' => $complemento_ampm,
                    ':complemento_ampm_indus' => $complemento_ampm_indus,
                    ':preparado_sitio' => $preparado_sitio,
                    ':comida_transportada' => $comida_transportada,
                    ':comedor_escolar' => $comedor_escolar,
                    ':concepto_sanitario' => $concepto_sanitario,
                    ':fecha_expedicion' => $fecha_expedicion,
                    ':cerca_a_contaminacion' => $cerca_a_contaminacion,
                    ':sede_conflicto_armado' => $sede_conflicto_armado,
                    ':frecuencia_conflicto' => $frecuencia_conflicto,
                    ':espacio_almacenamiento' => $espacio_almacenamiento,
                    ':material_techo' => $material_techo,
                    ':material_piso' => $material_piso,
                    ':material_paredes' => $material_paredes,
                    ':espacio_dedicado' => $espacio_dedicado,
                    ':material_techo_preparacion' => $material_techo_preparacion,
                    ':material_piso_preparacion' => $material_piso_preparacion,
                    ':material_paredes_preparacion' => $material_paredes_preparacion,
                    ':espacio_consumo' => $espacio_consumo,
                    ':acceso_electricidad' => $acceso_electricidad,
                    ':acceso_agua' => $acceso_agua,
                    ':tipo_agua' => $tipo_agua,
                    ':tipo_gas' => $tipo_gas,
                    ':tiene_recoleccion_basura' => $tiene_recoleccion_basura,
                    ':disposicion_derechos_pae' => $disposicion_derechos_pae,
                    ':disposicion_no_organicos_pae' => $disposicion_no_organicos_pae,
                    ':clasificacion_residuos' => $clasificacion_residuos,
                    ':neveras_almacenamiento' => $neveras_almacenamiento,
                    ':cant_neveras' => $cant_neveras,
                    ':neveras_funcionando' => $neveras_funcionando,
                    ':tamano_neveras_principales' => $tamano_neveras_principales,
                    ':congeladores' => $congeladores,
                    ':cantidad_congeladores' => $cantidad_congeladores,
                    ':congeladores_funcionando' => $congeladores_funcionando,
                    ':tamano_congelador' => $tamano_congelador,
                    ':almacena_alto_suelo' => $almacena_alto_suelo,
                    ':elementos_almacenamiento' => $elementos_almacenamiento,
                    ':estufa' => $estufa,
                    ':cant_estufas' => $cant_estufas,
                    ':cant_quemadores' => $cant_quemadores,
                    ':cant_quemadores_buenos' => $cant_quemadores_buenos,
                    ':licuadora' => $licuadora,
                    ':cant_licuadora' => $cant_licuadora,
                    ':cant_licuadoras_buenas' => $cant_licuadoras_buenas,
                    ':cant_licuadoras_ind' => $cant_licuadoras_ind,
                    ':posee_ollas_pae' => $posee_ollas_pae,
                    ':posee_cuchillos_pae' => $posee_cuchillos_pae,
                    ':posee_cucharones_pae' => $posee_cucharones_pae,
                    ':cant_ninos_pae_sentados' => $cant_ninos_pae_sentados,
                    ':cant_platos_pae' => $cant_platos_pae,
                    ':pocillos_pae' => $pocillos_pae,
                    ':cucharas_pae' => $cucharas_pae,
                    ':posee_sanitario_exclusivo_pae' => $posee_sanitario_exclusivo_pae,
                    ':posee_lavamanos_pae' => $posee_lavamanos_pae,
                    ':date' => $date,
                    ':tbl_user_id' => $tbl_user_id,
                    ':estado_sede' => $estado_sede,              
                    ':estado_techo_almacenamiento' => $estado_techo_almacenamiento,
                    ':estado_piso_almacenamiento' => $estado_piso_almacenamiento,
                    ':estado_paredes_almacenamiento' => $estado_paredes_almacenamiento,
                    ':estado_techo_preparacion' => $estado_techo_preparacion,
                    ':estado_piso_preparacion' => $estado_piso_preparacion,
                    ':estado_paredes_preparacion' => $estado_paredes_preparacion,             
                    ':cargue_info' => $cargue_info,
                    ':clasificacion' => $clasificacion,
                    ':sede_alcantarillado' => $sede_alcantarillado,
                    ':visita' => $visita,
                    ':ano' => $ano,
                    ':latitud' => $latitud,
                    ':longitud' => $longitud,
                    ':observaciones' => $observaciones,               
                    'ninos_focalizados' => $ninos_focalizados,
                    'tenedores_pae' => $tenedores_pae,
                    'cant_canecas' => $cant_canecas,
                    'disposicion_desechos_pae' => $disposicion_desechos_pae,
                    'firma' => $firma
                    );
                if ($result->execute($arrparam)) {
                    $arrjson = array('output' => array('valid' => true, 'response' => $pdo->lastInsertId()));
                } else {
                    $arrjson = Util::error_general(' Al guardar los datos');
                }
            } else {
                $arrjson = Util::error_missing_data();
            }
        }
        $db->closeConect();
        return $arrjson;
    }
}

