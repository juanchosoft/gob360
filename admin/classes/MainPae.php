<?php

/**
 * Clase que contiene todas las operaciones utilizadas sobre la base de datos
 * @author SPIDERSOFTWARE
 */
class MainPae
{
  public function __construct() {}


  public static function getDataMain($rqst)
  {

    $departamentoId = Util::getDepartamentoPrincipal();
    $codigoMunicipio = isset($rqst['codigoMunicipio']) ? ($rqst['codigoMunicipio']) : '';

    if ($codigoMunicipio == "" || $codigoMunicipio == 'seleccione') {
      return Util::error_missing_data();
    }

    $db = new DbConection();
    $pdo = $db->openConect();

    // Inicialización de variables
    $neveras = 0;
    $neveras_buenas = 0;
    $neveras_fun = 0;
    $neveras_almacenamiento_si = 0;
    $neveras_almacenamiento_no = 0;
    $congeladores = 0;
    $congeladores_funcionando = 0;
    $estufas = 0;
    $estufas_gen = 0;
    $quemadores_estufas = 0;
    $quemadores_estufas_buenos = 0;
    $licuadoras = 0;
    $licuadoras_gen  = 0;
    $licuadoras_industriales = 0;
    $licuadoras_total = 0;
    $cantidad_platos = 0;
    $cantidad_cucharas = 0;
    $cantidad_pocillos = 0;
    $cantidad_tenedores = 0;
    $cantidad_canecas = 0;
    $acceso_alcantarillado_si = 0;
    $acceso_alcantarillado_no = 0;
    $recoleccion_basuras_si = 0;
    $recoleccion_basuras_no = 0;
    $espacio_preparacion_si = 0;
    $espacio_preparacion_no = 0;
    $espacio_almacenamiento_no = 0;
    $espacio_almacenamiento_si = 0;
    $zona_conflicto_si = 0;
    $zona_conflicto_no = 0;
    $cercania_contaminacion_si = 0;
    $cercania_contaminacion_no = 0;
    $concepto_sanitario_si = 0;
    $concepto_sanitario_no = 0;
    $complemento_preparado_sitio_si = 0;
    $complemento_preparado_sitio_no = 0;
    $complemento_industrializado_si = 0;
    $complemento_industrializado_no = 0;
    $almuerzo_preparado_sitio_si = 0;
    $almuerzo_preparado_sitio_no = 0;
    $almuerzo_trasportado_si = 0;
    $almuerzo_trasportado_no = 0;
    $lavamanos_personal_si = 0;
    $lavamanos_personal_no = 0;
    $sanitario_personal_si = 0;
    $sanitario_personal_no = 0;
    $almacenamiento_personal_si = 0;
    $almacenamiento_personal_no = 0;
    $caracterizaciones = 0;
    $zona_rural = 0;
    $zona_urbana = 0;
    $acceso_agua_si = 0;
    $acceso_agua_no = 0;
    $acceso_agua_intermitente = 0;
    $almacena_alto_suelo_si = 0;
    $almacena_alto_suelo_no = 0;
    $almacena_balde = 0;
    $almacena_canasta = 0;
    $almacena_estante = 0;
    $almacena_ninguno = 0;
    $almacena_na = 0;
    $almacena_caja = 0;
    $acueducto = 0;
    $embotellada = 0;
    $lluvia = 0;
    $carrotanque = 0;
    $rios_quebradas = 0;
    $otros_agua = 0;
    $pozo_agua = 0;
    $acceso_electricidad_si = 0;
    $acceso_electricidad_no = 0;
    $acceso_electricidad_intermitente = 0;
    $electricidad = 0;
    $gas_natural = 0;
    $lena = 0;
    $desecho = 0;
    $no_aplica = 0;
    $petroleo_gasolina = 0;
    $comedor_escolar_si = 0;
    $comedor_escolar_no = 0;
    $no_tiene_concepto = 0;
    $si_tiene_favorable = 0;
    $si_favorable_requerimientos = 0;
    $si_desfavorable = 0;
    $estado_sede_antiguo_activo = 0;
    $estado_sede_nuevo_activo = 0;
    $estado_sede_cierre_temporal = 0;
    $estado_techo_almacenamiento_bueno = 0;
    $estado_techo_almacenamiento_malo = 0;
    $estado_techo_almacenamiento_regular = 0;
    $estado_paredes_bueno  = 0;
    $estado_paredes_regular  = 0;
    $estado_paredes_malo  = 0;
    $material_paredes_preparacion_ladrillo  = 0;
    $material_paredes_preparacion_prefabricado  = 0;
    $material_paredes_preparacion_otros  = 0;
    $material_paredes_preparacion_bahareque = 0;
    $estado_piso_bueno = 0;
    $estado_piso_regular = 0;
    $estado_piso_malo = 0;
    $material_piso_preparacion_baldosa = 0;
    $material_piso_cemento = 0;
    $material_piso_ladrillo = 0;
    $material_piso_preparacion_madera = 0;
    $material_piso_preparacion_otros = 0;
    $estado_techo_bueno = 0;
    $estado_techo_regular = 0;
    $estado_techo_malo = 0;
    $material_techo_preparacion_zinc = 0;
    $material_techo_eternit = 0;
    $material_techo_teja_barro = 0;
    $material_techo_preparacion_plastico = 0;
    $material_techo_preparacion_sin_techo = 0;
    $material_techo_preparacion_concreto = 0;
    $material_techo_preparacion_metal_acero = 0;
    $material_techo_preparacion_paja = 0;
    $material_techo_preparacion_otros = 0;
    $estado_paredes_almacenamiento_bueno = 0;
    $estado_paredes_almacenamiento_regular = 0;
    $estado_paredes_almacenamiento_malo = 0;
    $material_paredes_almacenamiento_bloque = 0;
    $material_paredes_almacenamiento_bahareque = 0;
    $material_paredes_almacenamiento_prefabricado = 0;
    $material_paredes__almacenamiento_madera = 0;
    $material_paredes_almacenamiento_otros = 0;
    $estado_piso_almacenamiento_bueno = 0;
    $estado_piso_almacenamiento_regular  = 0;
    $estado_piso_almacenamiento_malo = 0;
    $material_piso_almacenamiento_bloque = 0;
    $material_piso_almacenamiento_cemento = 0;
    $material_piso_almacenamiento_ladrillo = 0;
    $material_piso_almacenamiento_madera = 0;
    $material_piso_almacenamiento_otros = 0;
    $material_piso_almacenamiento_baldosa = 0;
    $estado_techo_almacenamiento_bueno = 0;
    $estado_techo_almacenamiento_regular = 0;
    $estado_techo_almacenamiento_malo = 0;
    $material_techo_almacenamiento_eternit = 0;
    $material_techo_almacenamiento_tejas = 0;
    $material_techo_almacenamiento_plastico = 0;
    $material_techo_almacenamiento_zinc = 0;
    $material_techo_almacenamiento_concreto = 0;
    $material_techo_almacenamiento_otros = 0;
    $material_techo_almacenamiento_metal = 0;
    $material_techo_almacenamiento_paja = 0;
    $disposicion_derechos_pae_enterrado = 0;
    $disposicion_derechos_pae_quemado = 0;
    $disposicion_derechos_pae_reciclan = 0;
    $disposicion_derechos_pae_tiran_lote = 0;
    $disposicion_derechos_pae_lombricultura = 0;
    $disposicion_no_organicos_pae_enterrado = 0;
    $disposicion_no_organicos_pae_quemado = 0;
    $disposicion_no_organicos_pae_reciclan = 0;
    $disposicion_no_organicos_pae_tiran_lote = 0;
    $disposicion_no_organicos_pae_lombricultura = 0;
    $tamano_neveras_principales_nevera_domestica_vertical_400_800L = 0;
    $tamano_neveras_principales_nevera_domestica_vertical_menor_400L = 0;
    $tamano_neveras_principales_nevera_domestica_vertical_2200l = 0;
    $tamano_neveras_principales_nevera_domestica_vertical_1200l = 0;
    $tamano_neveras_principales_nevera_domestica_vertical_otra = 0;
    $tamano_congelador_Congelador_Grande_1400_1600L = 0;
    $tamano_congelador_Congelador_Pequeño_Menor_400L = 0;
    $posee_ollas_pae_si = 0;
    $posee_ollas_pae_no = 0;
    $posee_cuchillos_pae_si = 0;
    $posee_cuchillos_pae_no = 0;
    $posee_cucharones_pae_si = 0;
    $posee_cucharones_pae_no = 0;
    $cant_ninos_pae_sentados_todos = 0;
    $cant_ninos_pae_mas_75 = 0;
    $ninos_foc = 0;


    // Consulta 1: Total de neverass almacenamiento
    $tablaPae = $db->getTable('tbl_pae');
    $tablaMunicipios = $db->getTable('tbl_ciudades_accion_unificada');

    if ($codigoMunicipio == "todos") {
      $join = "INNER JOIN $tablaMunicipios m ON p.tbl_municipio_id = m.codigo_muncipio";
      $where = "m.codigo_departamento = '$departamentoId'";
    } else {
      $codigoMunicipio = (int)$codigoMunicipio;
      $join = "";
      $where = "p.tbl_municipio_id = $codigoMunicipio";
    }



    $q1 = "SELECT
          SUM(CASE WHEN p.neveras_almacenamiento = 'Si' THEN 1 ELSE 0 END) AS neveras_almacenamiento_si,
          SUM(CASE WHEN p.neveras_almacenamiento = 'No' THEN 1 ELSE 0 END) AS neveras_almacenamiento_no
          FROM $tablaPae p
          $join
          WHERE $where";
    $stmt = $pdo->query($q1);
    $result = $stmt->fetch(PDO::FETCH_NUM);
    $neveras_almacenamiento_si = $result[0];
    $neveras_almacenamiento_no = $result[1];

    // Consulta 2: Total de neveras
    $q2 = "SELECT SUM(cant_neveras) AS neveras FROM " . $db->getTable('tbl_pae') . " p $join WHERE $where";
    $neveras = $pdo->query($q2)->fetchColumn();

    // Consulta 3: Total funcionando
    $q3 = "SELECT SUM(neveras_funcionando) AS neveras_buenas FROM " . $db->getTable('tbl_pae') . " p $join WHERE $where";
    $neveras_fun = $pdo->query($q3)->fetchColumn();

    // Consulta 4: Total de congeladores
    $q4 = "SELECT SUM(cantidad_congeladores) AS congeladores FROM " . $db->getTable('tbl_pae') . " p $join WHERE $where";
    $congeladores = $pdo->query($q4)->fetchColumn();

    // Consulta 5: Total de congeladores buenos
    $q5 = "SELECT SUM(congeladores_funcionando) AS congeladores_funcionando FROM " . $db->getTable('tbl_pae') . " p $join WHERE $where";
    $congeladores_funcionando = $pdo->query($q5)->fetchColumn();

    // Consulta 6: Total de estufas
    $q6 = "SELECT SUM(cant_estufas) AS estufas FROM " . $db->getTable('tbl_pae') . " p $join WHERE $where";
    $estufas = $pdo->query($q6)->fetchColumn();

    // Consulta 7: Total de quemadores
    $q7 = "SELECT SUM(cant_quemadores) AS quemadores_estufas FROM " . $db->getTable('tbl_pae') . " p $join WHERE $where";
    $quemadores_estufas = $pdo->query($q7)->fetchColumn();

    // Consulta 8: Total de quemadores buenos
    $q8 = "SELECT SUM(cant_quemadores_buenos) AS quemadores_estufas_buenos FROM " . $db->getTable('tbl_pae') . " p $join WHERE $where";
    $quemadores_estufas_buenos = $pdo->query($q8)->fetchColumn();

    // Consulta 9: Tiene estufa
    $q9 = "SELECT 
    SUM(CASE WHEN estufa = 'Si' THEN 1 ELSE 0 END) AS estufa_si,
    SUM(CASE WHEN estufa = 'No' THEN 1 ELSE 0 END) AS estufa_no
    FROM " . $db->getTable('tbl_pae') . " p $join WHERE $where";
    $stmt = $pdo->query($q9);
    $result = $stmt->fetch(PDO::FETCH_NUM);
    $estufas_gen_si = $result[0];
    $estufas_gen_no = $result[1];

    // Consulta 10: Tiene licuadoras
    $q10 = "SELECT 
    SUM(CASE WHEN licuadora = 'Si' THEN 1 ELSE 0 END) AS licuadora_si,
    SUM(CASE WHEN licuadora = 'No' THEN 1 ELSE 0 END) AS licuadora_no
    FROM " . $db->getTable('tbl_pae') . " p $join WHERE $where";
    $stmt = $pdo->query($q10);
    $result = $stmt->fetch(PDO::FETCH_NUM);
    $licuadoras_gen_si = $result[0];
    $licuadoras_gen_no = $result[1];

    // Consulta 11: Total de licuadoras
    $q11 = "SELECT SUM(cant_licuadora) AS licuadora FROM " . $db->getTable('tbl_pae') . " p $join WHERE $where";
    $licuadoras_total = $pdo->query($q11)->fetchColumn();

    // Consulta 12: Total de licuadoras buenas
    $q12 = "SELECT SUM(cant_licuadoras_buenas) AS licuadoras FROM " . $db->getTable('tbl_pae') . " p $join WHERE $where";
    $licuadoras = $pdo->query($q12)->fetchColumn();

    // Consulta 13: Total de licuadoras industriales
    $q13 = "SELECT SUM(cant_licuadoras_ind) AS licuadoras_industriales FROM " . $db->getTable('tbl_pae') . " p $join WHERE $where";
    $licuadoras_industriales = $pdo->query($q13)->fetchColumn();

    // Consulta 14: Total de platos
    $q14 = "SELECT SUM(cant_platos_pae) AS cantidad_platos FROM " . $db->getTable('tbl_pae') . " p $join WHERE $where";
    $cantidad_platos = $pdo->query($q14)->fetchColumn();

    // Consulta 15: Cantidad de pocillos
    $q15 = "SELECT SUM(pocillos_pae) AS cantidad_pocillos FROM " . $db->getTable('tbl_pae') . " p $join WHERE $where";
    $cantidad_pocillos = $pdo->query($q15)->fetchColumn();


    // Consulta 16: cantidad_cucharas
    $q16 = "SELECT SUM(cucharas_pae) AS cantidad_cucharas FROM " . $db->getTable('tbl_pae') . " p $join WHERE $where";
    $cantidad_cucharas = $pdo->query($q16)->fetchColumn();

    // Consulta 17: cantidad_tenedores
    $q17 = "SELECT SUM(tenedores_pae) AS cantidad_tenedores FROM " . $db->getTable('tbl_pae') . " p $join WHERE $where";
    $cantidad_tenedores = $pdo->query($q17)->fetchColumn();

    // Consulta 18: cantidad_canecas
    $q18 = "SELECT SUM(cant_canecas) AS cantidad_canecas FROM " . $db->getTable('tbl_pae') . " p $join WHERE $where";
    $cantidad_canecas = $pdo->query($q18)->fetchColumn();

    // Consulta 19: acceso_alcantarillado
    $q19 = "SELECT 
    SUM(CASE WHEN sede_alcantarillado = 'Si' THEN 1 ELSE 0 END) AS acceso_alcantarillado_si,
    SUM(CASE WHEN sede_alcantarillado = 'No' THEN 1 ELSE 0 END) AS acceso_alcantarillado_no
    FROM " . $db->getTable('tbl_pae') . " p $join WHERE $where";
    $stmt = $pdo->query($q19);
    $result = $stmt->fetch(PDO::FETCH_NUM);
    $acceso_alcantarillado_si = $result[0];
    $acceso_alcantarillado_no = $result[1];

    // Consulta 20: recoleccion_basura
    $q20 = "SELECT 
    SUM(CASE WHEN tiene_recoleccion_basura = 'Si' THEN 1 ELSE 0 END) AS recoleccion_basuras_si,
    SUM(CASE WHEN tiene_recoleccion_basura = 'No' THEN 1 ELSE 0 END) AS recoleccion_basuras_no
    FROM " . $db->getTable('tbl_pae') . " p $join WHERE $where";
    $stmt = $pdo->query($q20);
    $result = $stmt->fetch(PDO::FETCH_NUM);
    $recoleccion_basuras_si = $result[0];
    $recoleccion_basuras_no = $result[1];

    // Consulta 21: espacio_preparacion
    $q21 = "SELECT 
    SUM(CASE WHEN preparado_sitio = 'Si' THEN 1 ELSE 0 END) AS espacio_preparacion_si,
    SUM(CASE WHEN preparado_sitio = 'No' THEN 1 ELSE 0 END) AS espacio_preparacion_no
    FROM " . $db->getTable('tbl_pae') . " p $join WHERE $where";
    $stmt = $pdo->query($q21);
    $result = $stmt->fetch(PDO::FETCH_NUM);
    $espacio_preparacion_si = $result[0];
    $espacio_preparacion_no = $result[1];

    // Consulta 23: zona_conflicto
    $q23 = "SELECT 
    SUM(CASE WHEN sede_conflicto_armado = 'Si' THEN 1 ELSE 0 END) AS zona_conflicto_si,
    SUM(CASE WHEN sede_conflicto_armado = 'No' THEN 1 ELSE 0 END) AS zona_conflicto_no
    FROM " . $db->getTable('tbl_pae') . " p $join WHERE $where";
    $stmt = $pdo->query($q23);
    $result = $stmt->fetch(PDO::FETCH_NUM);
    $zona_conflicto_si = $result[0];
    $zona_conflicto_no = $result[1];

    // Consulta 24: cercania_contaminacion
    $q24 = "SELECT 
    SUM(CASE WHEN cerca_a_contaminacion = 'Si' THEN 1 ELSE 0 END) AS cercania_contaminacion_si,
    SUM(CASE WHEN cerca_a_contaminacion = 'No' THEN 1 ELSE 0 END) AS cercania_contaminacion_no
    FROM " . $db->getTable('tbl_pae') . " p $join WHERE $where";
    $stmt = $pdo->query($q24);
    $result = $stmt->fetch(PDO::FETCH_NUM);
    $cercania_contaminacion_si = $result[0];
    $cercania_contaminacion_no = $result[1];

    // Consulta 25: concepto_sanitario
    $q25 = "SELECT 
    SUM(CASE WHEN concepto_sanitario = 'Si_Favorable_requerimientos' THEN 1 ELSE 0 END) AS concepto_sanitario_si,
    SUM(CASE WHEN concepto_sanitario = 'No_tiene concepto sanitario' THEN 1 ELSE 0 END) AS concepto_sanitario_no
    FROM " . $db->getTable('tbl_pae') . " p $join WHERE $where";
    $stmt = $pdo->query($q25);
    $result = $stmt->fetch(PDO::FETCH_NUM);
    $concepto_sanitario_si = $result[0];
    $concepto_sanitario_no = $result[1];

    // Consulta 26: complemento_preparado_sitio
    $q26 = "SELECT 
    SUM(CASE WHEN complemento_ampm = 'Si' THEN 1 ELSE 0 END) AS complemento_preparado_sitio_si,
    SUM(CASE WHEN complemento_ampm = 'No' THEN 1 ELSE 0 END) AS complemento_preparado_sitio_no
    FROM " . $db->getTable('tbl_pae') . " p $join WHERE $where";
    $stmt = $pdo->query($q26);
    $result = $stmt->fetch(PDO::FETCH_NUM);
    $complemento_preparado_sitio_si = $result[0];
    $complemento_preparado_sitio_no = $result[1];

    // Consulta 27: complemento_industrializado
    $q27 = "SELECT 
    SUM(CASE WHEN complemento_ampm_indus = 'Si' THEN 1 ELSE 0 END) AS complemento_industrializado_si,
    SUM(CASE WHEN complemento_ampm_indus = 'No' THEN 1 ELSE 0 END) AS complemento_industrializado_no
    FROM " . $db->getTable('tbl_pae') . " p $join WHERE $where";
    $stmt = $pdo->query($q27);
    $result = $stmt->fetch(PDO::FETCH_NUM);
    $complemento_industrializado_si = $result[0];
    $complemento_industrializado_no = $result[1];

    // Consulta 28: almuerzo_preparado_sitio
    $q28 = "SELECT 
    SUM(CASE WHEN preparado_sitio = 'Si' THEN 1 ELSE 0 END) AS almuerzo_preparado_sitio_si,
    SUM(CASE WHEN preparado_sitio = 'No' THEN 1 ELSE 0 END) AS almuerzo_preparado_sitio_no
    FROM " . $db->getTable('tbl_pae') . " p $join WHERE $where";
    $stmt = $pdo->query($q28);
    $result = $stmt->fetch(PDO::FETCH_NUM);
    $almuerzo_preparado_sitio_si = $result[0];
    $almuerzo_preparado_sitio_no = $result[1];

    // Consulta 29: almuerzo_transportado
    // Consulta 29: almuerzo_transportado
$q29 = "SELECT 
SUM(CASE WHEN comida_transportada = 'Si' THEN 1 ELSE 0 END) AS almuerzo_transportado_si,
SUM(CASE WHEN comida_transportada = 'No' THEN 1 ELSE 0 END) AS almuerzo_transportado_no
FROM " . $db->getTable('tbl_pae') . " p $join WHERE $where";

$stmt = $pdo->query($q29);
$result = $stmt->fetch(PDO::FETCH_NUM);
$almuerzo_transportado_si = $result[0];
$almuerzo_transportado_no = $result[1];


    // Consulta 30: lavamanos_personal_pae
    $q30 = "SELECT 
    SUM(CASE WHEN posee_lavamanos_pae = 'Si' THEN 1 ELSE 0 END) AS lavamanos_personal_si,
    SUM(CASE WHEN posee_lavamanos_pae = 'No' THEN 1 ELSE 0 END) AS lavamanos_personal_no
    FROM " . $db->getTable('tbl_pae') . " p $join WHERE $where";
    $stmt = $pdo->query($q30);
    $result = $stmt->fetch(PDO::FETCH_NUM);
    $lavamanos_personal_si = $result[0];
    $lavamanos_personal_no = $result[1];

    // Consulta 31: sanitario_personal_pae
    $q31 = "SELECT 
    SUM(CASE WHEN posee_sanitario_exclusivo_pae = 'Si' THEN 1 ELSE 0 END) AS sanitario_personal_si,
    SUM(CASE WHEN posee_sanitario_exclusivo_pae = 'No' THEN 1 ELSE 0 END) AS sanitario_personal_no
    FROM " . $db->getTable('tbl_pae') . " p $join WHERE $where";
    $stmt = $pdo->query($q31);
    $result = $stmt->fetch(PDO::FETCH_NUM);
    $sanitario_personal_si = $result[0];
    $sanitario_personal_no = $result[1];

    // Consulta 32: espacio_almacenamiento
    $q32 = "SELECT 
    SUM(CASE WHEN espacio_almacenamiento = 'Si' THEN 1 ELSE 0 END),
    SUM(CASE WHEN espacio_almacenamiento = 'No' THEN 1 ELSE 0 END)
    FROM " . $db->getTable('tbl_pae') . " p $join WHERE $where";
    $stmt = $pdo->query($q32);
    $result = $stmt->fetch(PDO::FETCH_NUM);
    $espacio_almacenamiento_si = $result[0];
    $espacio_almacenamiento_no = $result[1];

    // Consulta 33: caracterizaciones
    $q33 = "SELECT COUNT(p.id) AS caracterizaciones FROM " . $db->getTable('tbl_pae') . " p $join WHERE $where";
    $caracterizaciones = $pdo->query($q33)->fetchColumn();



    // Consulta 34: zona de conflicto
    $q34 = "SELECT 
SUM(CASE WHEN frecuencia_conflicto = 'Algo_Frecuente' THEN 1 ELSE 0 END) AS algo_frecuente_conflicto,
SUM(CASE WHEN frecuencia_conflicto = 'No_Aplica' THEN 1 ELSE 0 END) AS no_frecuencia_conflicto,
SUM(CASE WHEN frecuencia_conflicto = 'Poco_Frecuente' THEN 1 ELSE 0 END) AS poco_frecuente_conflicto
FROM " . $db->getTable('tbl_pae') . " p $join 
WHERE $where";
    $stmt = $pdo->query($q34);
    $result = $stmt->fetch(PDO::FETCH_NUM);
    $algo_frecuente_conflicto = $result[0];
    $no_frecuencia_conflicto = $result[1];
    $poco_frecuente_conflicto = $result[2];

    // Consulta 34: zona rural o urbana
    $q34 = "SELECT 
SUM(CASE WHEN sector = 'Rural' THEN 1 ELSE 0 END),
SUM(CASE WHEN sector = 'Urbano' THEN 1 ELSE 0 END)
FROM " . $db->getTable('tbl_pae') . " p $join 
WHERE $where";
    $stmt = $pdo->query($q34);
    $result = $stmt->fetch(PDO::FETCH_NUM);
    $zona_rural = $result[0];
    $zona_urbana = $result[1];

    // Consulta 35: acceso a agua
    $q35 = "SELECT 
SUM(CASE WHEN acceso_agua = 'Si' THEN 1 ELSE 0 END) AS acceso_agua_si,
SUM(CASE WHEN acceso_agua = 'No' THEN 1 ELSE 0 END) AS acceso_agua_no,
SUM(CASE WHEN acceso_agua = 'Intermitente' THEN 1 ELSE 0 END) AS acceso_agua_intermitente
FROM " . $db->getTable('tbl_pae') . " p $join 
WHERE $where";
    $stmt = $pdo->query($q35);
    $result = $stmt->fetch(PDO::FETCH_NUM);
    $acceso_agua_si = $result[0];
    $acceso_agua_no = $result[1];
    $acceso_agua_intermitente = $result[2];

    // Consulta 36: almacenamiento alto del suelo
    $q36 = "SELECT 
SUM(CASE WHEN almacena_alto_suelo = 'Si' THEN 1 ELSE 0 END),
SUM(CASE WHEN almacena_alto_suelo = 'No' THEN 1 ELSE 0 END)
FROM " . $db->getTable('tbl_pae') . " p $join 
WHERE $where";
    $stmt = $pdo->query($q36);
    $result = $stmt->fetch(PDO::FETCH_NUM);
    $almacena_alto_suelo_si = $result[0];
    $almacena_alto_suelo_no = $result[1];

    // Consulta 37: elementos de almacenamiento
    $q37 = "SELECT 
SUM(CASE WHEN elementos_almacenamiento = 'Balde' THEN 1 ELSE 0 END) AS almacena_balde,
SUM(CASE WHEN elementos_almacenamiento = 'Canastilla' THEN 1 ELSE 0 END) AS almacena_canasta,
SUM(CASE WHEN elementos_almacenamiento = 'Ninguno' THEN 1 ELSE 0 END) AS almacena_ninguno,
SUM(CASE WHEN elementos_almacenamiento = 'No_Aplica' THEN 1 ELSE 0 END) AS almacena_na,
SUM(CASE WHEN elementos_almacenamiento = 'Caja' THEN 1 ELSE 0 END) AS almacena_caja,
SUM(CASE WHEN elementos_almacenamiento = 'Estante' THEN 1 ELSE 0 END) AS almacena_estante
FROM " . $db->getTable('tbl_pae') . " p $join 
WHERE $where";
    $stmt = $pdo->query($q37);
    $result = $stmt->fetch(PDO::FETCH_NUM);
    $almacena_balde = $result[0];
    $almacena_canasta = $result[1];
    $almacena_ninguno = $result[2];
    $almacena_na = $result[3];
    $almacena_caja = $result[4];
    $almacena_estante = $result[5];

    // Consulta 38: tipo de agua
    $q38 = "SELECT 
SUM(CASE WHEN tipo_agua = 'Acueducto' THEN 1 ELSE 0 END) AS acueducto,
SUM(CASE WHEN tipo_agua = 'Agua_botella_bolsa' THEN 1 ELSE 0 END) AS embotellada,
SUM(CASE WHEN tipo_agua = 'Agua_Lluvia' THEN 1 ELSE 0 END) AS lluvia,
SUM(CASE WHEN tipo_agua = 'Carrotanque' THEN 1 ELSE 0 END) AS carrotanque,
SUM(CASE WHEN tipo_agua = 'Cuerpos_agua_Rios_Quebradas' THEN 1 ELSE 0 END) AS rios_quebradas,
SUM(CASE WHEN tipo_agua = 'Otro_obtiene_agua' THEN 1 ELSE 0 END) AS otros_agua,
SUM(CASE WHEN tipo_agua = 'Pozo' THEN 1 ELSE 0 END) AS pozo_agua
FROM " . $db->getTable('tbl_pae') . " p $join 
WHERE $where";
    $stmt = $pdo->query($q38);
    $result = $stmt->fetch(PDO::FETCH_NUM);
    $acueducto = $result[0];
    $embotellada = $result[1];
    $lluvia = $result[2];
    $carrotanque = $result[3];
    $rios_quebradas = $result[4];
    $otros_agua = $result[5];
    $pozo_agua = $result[6];

    // Consulta 39: acceso a electricidad
    $q39 = "SELECT 
SUM(CASE WHEN acceso_electricidad = 'Si' THEN 1 ELSE 0 END) AS acceso_electricidad_si,
SUM(CASE WHEN acceso_electricidad = 'No' THEN 1 ELSE 0 END) AS acceso_electricidad_no,
SUM(CASE WHEN acceso_electricidad = 'Intermitente' THEN 1 ELSE 0 END) AS acceso_electricidad_intermitente
FROM " . $db->getTable('tbl_pae') . " p $join 
WHERE $where";
    $stmt = $pdo->query($q39);
    $result = $stmt->fetch(PDO::FETCH_NUM);
    $acceso_electricidad_si = $result[0];
    $acceso_electricidad_no = $result[1];
    $acceso_electricidad_intermitente = $result[2];

    // Consulta 40: tipo de energía usada para cocinar
    $q40 = "SELECT 
SUM(CASE WHEN tipo_gas = 'Electricidad' THEN 1 ELSE 0 END) AS electricidad,
SUM(CASE WHEN tipo_gas = 'Gas_Natural_Pipeta' THEN 1 ELSE 0 END) AS gas_natural,
SUM(CASE WHEN tipo_gas = 'Leña_Madera_Carbon_leña_Carbon_mineral' THEN 1 ELSE 0 END) AS lena,
SUM(CASE WHEN tipo_gas = 'Materiales_desecho' THEN 1 ELSE 0 END) AS desecho,
SUM(CASE WHEN tipo_gas = 'No_Aplica' THEN 1 ELSE 0 END) AS no_aplica,
SUM(CASE WHEN tipo_gas = 'Petroleo_Gasolina_Kerosene_alcohol' THEN 1 ELSE 0 END) AS petroleo_gasolina
FROM " . $db->getTable('tbl_pae') . " p $join 
WHERE $where";
    $stmt = $pdo->query($q40);
    $result = $stmt->fetch(PDO::FETCH_NUM);
    $electricidad = $result[0];
    $gas_natural = $result[1];
    $lena = $result[2];
    $desecho = $result[3];
    $no_aplica = $result[4];
    $petroleo_gasolina = $result[5];

    // Consulta 41: posee comedor escolar
    $q41 = "SELECT 
SUM(CASE WHEN comedor_escolar = 'Si' THEN 1 ELSE 0 END),
SUM(CASE WHEN comedor_escolar = 'No' THEN 1 ELSE 0 END)
FROM " . $db->getTable('tbl_pae') . " p $join 
WHERE $where";
    $stmt = $pdo->query($q41);
    $result = $stmt->fetch(PDO::FETCH_NUM);
    $comedor_escolar_si = $result[0];
    $comedor_escolar_no = $result[1];



    // Consulta 42: tipo concepto sanitario
    $q42 = "SELECT 
SUM(CASE WHEN concepto_sanitario = 'No_tiene concepto sanitario' THEN 1 ELSE 0 END) AS no_tiene_concepto,
SUM(CASE WHEN concepto_sanitario = 'Si_Favorable' THEN 1 ELSE 0 END) AS si_tiene_favorable,
SUM(CASE WHEN concepto_sanitario = 'Si_Favorable_requerimientos' THEN 1 ELSE 0 END) AS si_favorable_requerimientos,
SUM(CASE WHEN concepto_sanitario = 'Si_pero_desfavorable' THEN 1 ELSE 0 END) AS si_desfavorable
FROM " . $db->getTable('tbl_pae') . " p $join 
WHERE $where";
    $stmt = $pdo->query($q42);
    $result = $stmt->fetch(PDO::FETCH_NUM);
    $no_tiene_concepto = $result[0];
    $si_tiene_favorable = $result[1];
    $si_favorable_requerimientos = $result[2];
    $si_desfavorable = $result[3];

    // Consulta 43: estado sede educativa
    $q43 = "SELECT 
SUM(CASE WHEN estado_sede = 'Antiguo_Activo' THEN 1 ELSE 0 END) AS estado_sede_antiguo_activo,
SUM(CASE WHEN estado_sede = 'Nuevo_Activo' THEN 1 ELSE 0 END) AS estado_sede_nuevo_activo,
SUM(CASE WHEN estado_sede = 'Cierre_Temporal' THEN 1 ELSE 0 END) AS estado_sede_cierre_temporal
FROM " . $db->getTable('tbl_pae') . " p $join 
WHERE $where";
    $stmt = $pdo->query($q43);
    $result = $stmt->fetch(PDO::FETCH_NUM);
    $estado_sede_antiguo_activo = $result[0];
    $estado_sede_nuevo_activo = $result[1];
    $estado_sede_cierre_temporal = $result[2];

    // Consulta 44: estado techo sede educativa
    $q44 = "SELECT 
SUM(CASE WHEN estado_techo_almacenamiento = 'Bueno' THEN 1 ELSE 0 END),
SUM(CASE WHEN estado_techo_almacenamiento = 'Malo' THEN 1 ELSE 0 END),
SUM(CASE WHEN estado_techo_almacenamiento = 'Regular' THEN 1 ELSE 0 END)
FROM " . $db->getTable('tbl_pae') . " p $join 
WHERE $where";
    $stmt = $pdo->query($q44);
    $result = $stmt->fetch(PDO::FETCH_NUM);
    $estado_techo_almacenamiento_bueno = $result[0];
    $estado_techo_almacenamiento_malo = $result[1];
    $estado_techo_almacenamiento_regular = $result[2];

    // Consulta 45: estado paredes preparación
    $q45 = "SELECT 
SUM(CASE WHEN estado_paredes_preparacion = 'Bueno' THEN 1 ELSE 0 END) AS estado_paredes_bueno,
SUM(CASE WHEN estado_paredes_preparacion = 'Regular' THEN 1 ELSE 0 END) AS estado_paredes_regular,
SUM(CASE WHEN estado_paredes_preparacion = 'Malo' THEN 1 ELSE 0 END) AS estado_paredes_malo
FROM " . $db->getTable('tbl_pae') . " p $join 
WHERE $where";
    $stmt = $pdo->query($q45);
    $result = $stmt->fetch(PDO::FETCH_NUM);
    $estado_paredes_bueno = $result[0];
    $estado_paredes_regular = $result[1];
    $estado_paredes_malo = $result[2];

    // Consulta 46: tipo paredes preparación
    $q46 = "SELECT 
SUM(CASE WHEN material_paredes_preparacion = 'Bloque_Ladrillo_Piedra_Adobe' THEN 1 ELSE 0 END) AS material_paredes_preparacion_ladrillo,
SUM(CASE WHEN material_paredes_preparacion = 'Material_prefabricado_Drywall_Aglomerado_Lamina_Polietileno' THEN 1 ELSE 0 END) AS material_paredes_preparacion_prefabricado,
SUM(CASE WHEN material_paredes_preparacion = 'Otro_prep_paredes' THEN 1 ELSE 0 END) AS material_paredes_preparacion_otros,
SUM(CASE WHEN material_paredes_preparacion = 'Bahareque_Madera_Tabla_Tablon' THEN 1 ELSE 0 END) AS material_paredes_preparacion_bahareque
FROM " . $db->getTable('tbl_pae') . " p $join 
WHERE $where";
    $stmt = $pdo->query($q46);
    $result = $stmt->fetch(PDO::FETCH_NUM);
    $material_paredes_preparacion_ladrillo = $result[0];
    $material_paredes_preparacion_prefabricado = $result[1];
    $material_paredes_preparacion_otros = $result[2];
    $material_paredes_preparacion_bahareque = $result[3];
// Consulta XX: CONSSULTA PARA ESTADO PAREDES ALMACENAMIENTO
$qXX = "SELECT 
    SUM(CASE WHEN estado_paredes_almacenamiento = 'Bueno' THEN 1 ELSE 0 END) AS estado_paredes_almacenamiento_bueno,
    SUM(CASE WHEN estado_paredes_almacenamiento = 'Regular' THEN 1 ELSE 0 END) AS estado_paredes_almacenamiento_regular,
    SUM(CASE WHEN estado_paredes_almacenamiento = 'Malo' THEN 1 ELSE 0 END) AS estado_paredes_almacenamiento_malo
FROM " . $db->getTable('tbl_pae') . " p $join 
WHERE $where";
$stmt = $pdo->query($qXX);
$result = $stmt->fetch(PDO::FETCH_NUM);
$estado_paredes_almacenamiento_bueno = $result[0];
$estado_paredes_almacenamiento_regular = $result[1];
$estado_paredes_almacenamiento_malo = $result[2];
// Consulta XX2: CONSSULTA PARA Tipo Material Techo Preparación
$qXX2 = "SELECT 
    SUM(CASE WHEN material_techo_preparacion = 'Zinc' THEN 1 ELSE 0 END) AS material_techo_preparacion_zinc,
    SUM(CASE WHEN material_techo_preparacion = 'Teja_Eternit' THEN 1 ELSE 0 END) AS material_techo_eternit,
    SUM(CASE WHEN material_techo_preparacion = 'Tejas_barro_arcilla' THEN 1 ELSE 0 END) AS material_techo_teja_barro,
    SUM(CASE WHEN material_techo_preparacion = 'Tejas_plastico' THEN 1 ELSE 0 END) AS material_techo_preparacion_plastico,
    SUM(CASE WHEN material_techo_preparacion = 'Sin_techo' THEN 1 ELSE 0 END) AS material_techo_preparacion_sin_techo,
    SUM(CASE WHEN material_techo_preparacion = 'Techo_Concreto' THEN 1 ELSE 0 END) AS material_techo_preparacion_concreto,
    SUM(CASE WHEN material_techo_preparacion = 'Techo_Metal_Acero' THEN 1 ELSE 0 END) AS material_techo_preparacion_metal_acero,
    SUM(CASE WHEN material_techo_preparacion = 'Techo_paja_madera' THEN 1 ELSE 0 END) AS material_techo_preparacion_paja,
    SUM(CASE WHEN material_techo_preparacion = 'Otro_prep_techo' THEN 1 ELSE 0 END) AS material_techo_preparacion_otros
FROM " . $db->getTable('tbl_pae') . " p $join 
WHERE $where";
$stmt = $pdo->query($qXX2);
$result = $stmt->fetch(PDO::FETCH_NUM);
$material_techo_preparacion_zinc = $result[0];
$material_techo_eternit = $result[1];
$material_techo_teja_barro = $result[2];
$material_techo_preparacion_plastico = $result[3];
$material_techo_preparacion_sin_techo = $result[4];
$material_techo_preparacion_concreto = $result[5];
$material_techo_preparacion_metal_acero = $result[6];
$material_techo_preparacion_paja = $result[7];
$material_techo_preparacion_otros = $result[8];

    // Consulta 47: estado piso preparación
    $q47 = "SELECT 
SUM(CASE WHEN estado_piso_preparacion = 'Bueno' THEN 1 ELSE 0 END) AS estado_piso_bueno,
SUM(CASE WHEN estado_piso_preparacion = 'Regular' THEN 1 ELSE 0 END) AS estado_piso_regular,
SUM(CASE WHEN estado_piso_preparacion = 'Malo' THEN 1 ELSE 0 END) AS estado_piso_malo
FROM " . $db->getTable('tbl_pae') . " p $join 
WHERE $where";
    $stmt = $pdo->query($q47);
    $result = $stmt->fetch(PDO::FETCH_NUM);
    $estado_piso_bueno = $result[0];
    $estado_piso_regular = $result[1];
    $estado_piso_malo = $result[2];

    // Consulta 48: tipo piso preparación
    $q48 = "SELECT 
SUM(CASE WHEN material_piso_preparacion = 'Baldosa' THEN 1 ELSE 0 END) AS material_piso_preparacion_baldosa,
SUM(CASE WHEN material_piso_preparacion = 'Cemento_Gravilla' THEN 1 ELSE 0 END) AS material_piso_cemento,
SUM(CASE WHEN material_piso_preparacion = 'Ladrillo' THEN 1 ELSE 0 END) AS material_piso_ladrillo,
SUM(CASE WHEN material_piso_preparacion = 'Madera_Tabla_Tablon' THEN 1 ELSE 0 END) AS material_piso_preparacion_madera,
SUM(CASE WHEN material_piso_preparacion = 'Otro_prep_piso' THEN 1 ELSE 0 END) AS material_piso_preparacion_otros
FROM " . $db->getTable('tbl_pae') . " p $join 
WHERE $where";
    $stmt = $pdo->query($q48);
    $result = $stmt->fetch(PDO::FETCH_NUM);
    $material_piso_preparacion_baldosa = $result[0];
    $material_piso_cemento = $result[1];
    $material_piso_ladrillo = $result[2];
    $material_piso_preparacion_madera = $result[3];
    $material_piso_preparacion_otros = $result[4];

    // Consulta 49: estado techo preparación
    $q49 = "SELECT 
SUM(CASE WHEN estado_techo_preparacion = 'Bueno' THEN 1 ELSE 0 END) AS estado_techo_bueno,
SUM(CASE WHEN estado_techo_preparacion = 'Regular' THEN 1 ELSE 0 END) AS estado_techo_regular,
SUM(CASE WHEN estado_techo_preparacion = 'Malo' THEN 1 ELSE 0 END) AS estado_techo_malo
FROM " . $db->getTable('tbl_pae') . " p $join 
WHERE $where";
    $stmt = $pdo->query($q49);
    $result = $stmt->fetch(PDO::FETCH_NUM);
    $estado_techo_bueno = $result[0];
    $estado_techo_regular = $result[1];
    $estado_techo_malo = $result[2];


    // Consulta 50: tipo paredes almacenamiento
    $q50 = "SELECT 
        SUM(CASE WHEN material_paredes = 'Bloque_Ladrillo_Piedra_Adobe' THEN 1 ELSE 0 END) AS material_paredes_almacenamiento_bloque,
        SUM(CASE WHEN material_paredes = 'Bahareque_Madera_Tabla_Tablon' THEN 1 ELSE 0 END)AS material_paredes_almacenamiento_bahareque,
        SUM(CASE WHEN material_paredes = 'Material_prefabricado_Drywall_Aglomerado_Lamina_Polietileno' THEN 1 ELSE 0 END) AS material_paredes_almacenamiento_prefabricado,
        SUM(CASE WHEN material_paredes = 'Madera_Tabla_Tablon' THEN 1 ELSE 0 END) AS material_paredes__almacenamiento_madera,
        SUM(CASE WHEN material_paredes = 'Otro_alm_paredes' THEN 1 ELSE 0 END) AS material_paredes_almacenamiento_otros
        FROM " . $db->getTable('tbl_pae') . " p $join 
WHERE $where";
    $stmt = $pdo->query($q50);
    $result = $stmt->fetch(PDO::FETCH_NUM);
    $material_paredes_almacenamiento_bloque = $result[0];
    $material_paredes_almacenamiento_bahareque = $result[1];
    $material_paredes_almacenamiento_prefabricado = $result[2];
    $material_paredes__almacenamiento_madera = $result[3];
    $material_paredes_almacenamiento_otros = $result[4];



    // Consulta 51: estado piso almacenamiento
    $q51 = "SELECT 
        SUM(CASE WHEN estado_piso_almacenamiento = 'Bueno' THEN 1 ELSE 0 END) AS estado_piso_almacenamiento_bueno,
        SUM(CASE WHEN estado_piso_almacenamiento = 'Regular' THEN 1 ELSE 0 END) AS estado_piso_almacenamiento_regular,
        SUM(CASE WHEN estado_piso_almacenamiento = 'Malo' THEN 1 ELSE 0 END) AS estado_piso_almacenamiento_malo
        FROM " . $db->getTable('tbl_pae') . " p $join 
WHERE $where";
    $stmt = $pdo->query($q51);
    $result = $stmt->fetch(PDO::FETCH_NUM);
    $estado_piso_almacenamiento_bueno = $result[0];
    $estado_piso_almacenamiento_regular = $result[1];
    $estado_piso_almacenamiento_malo = $result[2];


    // Consulta 50: tipo piso almacenamiento
    $q50 = "SELECT 
        SUM(CASE WHEN material_piso = 'Baldosa' THEN 1 ELSE 0 END) AS material_piso_almacenamiento_baldosa,
        SUM(CASE WHEN material_piso = 'Cemento_Gravilla' THEN 1 ELSE 0 END)AS material_piso_almacenamiento_cemento,
        SUM(CASE WHEN material_piso = 'Ladrillo' THEN 1 ELSE 0 END) AS material_piso_almacenamiento_ladrillo,
        SUM(CASE WHEN material_piso = 'Madera_Tabla_Tablon' THEN 1 ELSE 0 END) AS material_piso_almacenamiento_madera,
        SUM(CASE WHEN material_piso = 'Otro_alm_piso' THEN 1 ELSE 0 END) AS material_piso_almacenamiento_otros
        FROM " . $db->getTable('tbl_pae') . " p $join 
WHERE $where";
    $stmt = $pdo->query($q50);
    $result = $stmt->fetch(PDO::FETCH_NUM);
    $material_piso_almacenamiento_baldosa = $result[0];
    $material_piso_almacenamiento_cemento = $result[1];
    $material_piso_almacenamiento_ladrillo = $result[2];
    $material_piso_almacenamiento_madera = $result[3];
    $material_piso_almacenamiento_otros = $result[4];


    // Consulta 52: estado techo almacenamiento
    $q52 = "SELECT 
        SUM(CASE WHEN estado_techo_almacenamiento = 'Bueno' THEN 1 ELSE 0 END) AS estado_techo_almacenamiento_bueno,
        SUM(CASE WHEN estado_techo_almacenamiento = 'Regular' THEN 1 ELSE 0 END) AS estado_techo_almacenamiento_regular,
        SUM(CASE WHEN estado_techo_almacenamiento = 'Malo' THEN 1 ELSE 0 END) AS estado_techo_almacenamiento_malo
        FROM " . $db->getTable('tbl_pae') . " p $join 
WHERE $where";
    $stmt = $pdo->query($q52);
    $result = $stmt->fetch(PDO::FETCH_NUM);
    $estado_techo_almacenamiento_bueno = $result[0];
    $estado_techo_almacenamiento_regular = $result[1];
    $estado_techo_almacenamiento_malo = $result[2];


    // Consulta 53: tipo techo almacenamiento
    $q53 = "SELECT 
          SUM(CASE WHEN material_techo = 'Teja_Eternit' THEN 1 ELSE 0 END) AS material_techo_almacenamiento_eternit,
        SUM(CASE WHEN material_techo = 'Tejas_barro_arcilla' THEN 1 ELSE 0 END)AS material_techo_almacenamiento_tejas,
        SUM(CASE WHEN material_techo = 'Tejas_plastico' THEN 1 ELSE 0 END) AS material_techo_almacenamiento_plastico,
        SUM(CASE WHEN material_techo = 'Zinc' THEN 1 ELSE 0 END) AS material_techo_almacenamiento_zinc,
        SUM(CASE WHEN material_techo = 'Techo_Concreto' THEN 1 ELSE 0 END) AS material_techo_almacenamiento_concreto,
        SUM(CASE WHEN material_techo = 'Techo_Metal_Acero' THEN 1 ELSE 0 END) AS material_techo_almacenamiento_metal,
        SUM(CASE WHEN material_techo = 'Techo_paja_madera' THEN 1 ELSE 0 END) AS material_techo_almacenamiento_paja,       
        SUM(CASE WHEN material_techo = 'Otro_alm_techo' THEN 1 ELSE 0 END) AS material_techo_almacenamiento_otros
        FROM " . $db->getTable('tbl_pae') . " p $join 
WHERE $where";
    $stmt = $pdo->query($q53);
    $result = $stmt->fetch(PDO::FETCH_NUM);
    $material_techo_almacenamiento_eternit = $result[0];
    $material_techo_almacenamiento_tejas = $result[1];
    $material_techo_almacenamiento_plastico = $result[2];
    $material_techo_almacenamiento_zinc = $result[3];
    $material_techo_almacenamiento_concreto = $result[4];
    $material_techo_almacenamiento_metal = $result[5];
    $material_techo_almacenamiento_paja = $result[6];
    $material_techo_almacenamiento_otros = $result[7];

    // Consulta 54: estado techo almacenamiento
    $q54 = "SELECT 
        SUM(CASE WHEN espacio_consumo = 'Espacio_cerrado_exclusivo_comedor' THEN 1 ELSE 0 END) AS espacio_consumo_exclusivo_comedor,
        SUM(CASE WHEN espacio_consumo = 'Espacio_con_techo_compartido_areas_comunes' THEN 1 ELSE 0 END) AS espacio_consumo_compartido_areas_techo,
        SUM(CASE WHEN espacio_consumo = 'Espacio_sin_techo' THEN 1 ELSE 0 END) AS espacio_consumo_sin_techo,
        SUM(CASE WHEN espacio_consumo = 'Otro_espacio_consumo' THEN 1 ELSE 0 END) AS espacio_consumo_otro_espacio,
        SUM(CASE WHEN espacio_consumo = 'salon_clases' THEN 1 ELSE 0 END) AS espacio_consumo_otro_salon_clases
        FROM " . $db->getTable('tbl_pae') . " p $join 
WHERE $where";
    $stmt = $pdo->query($q54);
    $result = $stmt->fetch(PDO::FETCH_NUM);
    $espacio_consumo_exclusivo_comedor = $result[0];
    $espacio_consumo_compartido_areas_techo = $result[1];
    $espacio_consumo_sin_techo = $result[2];
    $espacio_consumo_otro_espacio = $result[3];
    $espacio_consumo_otro_salon_clases = $result[4];


    // Consulta 55: dispocicion desechos pae organicos
    $q55 = "SELECT 
          SUM(CASE WHEN disposicion_derechos_pae = 'Lo_Entierran' THEN 1 ELSE 0 END) AS disposicion_derechos_pae_enterrado,
          SUM(CASE WHEN disposicion_derechos_pae = 'Lo_Queman' THEN 1 ELSE 0 END) AS disposicion_derechos_pae_quemado,
          SUM(CASE WHEN disposicion_derechos_pae = 'Lo_Reciclan' THEN 1 ELSE 0 END) AS disposicion_derechos_pae_reciclan,
          SUM(CASE WHEN disposicion_derechos_pae = 'Lo_tiran_patio_Lote_Zanja_Baldio' THEN 1 ELSE 0 END) AS disposicion_derechos_pae_tiran_lote,
          SUM(CASE WHEN disposicion_derechos_pae = 'Uso_alimento_animales' THEN 1 ELSE 0 END) AS disposicion_derechos_pae_alimento_animales,
          SUM(CASE WHEN disposicion_derechos_pae = 'Uso_compost_Lombricultura' THEN 1 ELSE 0 END) AS disposicion_derechos_pae_lombricultura
          FROM " . $db->getTable('tbl_pae') . " p $join 
WHERE $where";
    $stmt = $pdo->query($q55);
    $result = $stmt->fetch(PDO::FETCH_NUM);
    $disposicion_derechos_pae_enterrado = $result[0];
    $disposicion_derechos_pae_quemado = $result[1];
    $disposicion_derechos_pae_reciclan = $result[2];
    $disposicion_derechos_pae_tiran_lote = $result[3];
    $disposicion_derechos_pae_lombricultura = $result[4];

    // Consulta 55: dispocicion desechos pae no organicos
    $q55 = "SELECT 
          SUM(CASE WHEN disposicion_no_organicos_pae = 'Lo_Entierran' THEN 1 ELSE 0 END) AS disposicion_no_organicos_pae_enterrado,
          SUM(CASE WHEN disposicion_no_organicos_pae = 'Lo_Queman' THEN 1 ELSE 0 END) AS disposicion_no_organicos_pae_quemado,
          SUM(CASE WHEN disposicion_no_organicos_pae = 'Lo_Reciclan' THEN 1 ELSE 0 END) AS disposicion_no_organicos_pae_reciclan,
          SUM(CASE WHEN disposicion_no_organicos_pae = 'Lo_tiran_patio_Lote_Zanja_Baldio' THEN 1 ELSE 0 END) AS disposicion_no_organicos_pae_tiran_lote,
          SUM(CASE WHEN disposicion_no_organicos_pae = 'Uso_alimento_animales' THEN 1 ELSE 0 END) AS disposicion_no_organicos_pae_alimento_animales,
          SUM(CASE WHEN disposicion_no_organicos_pae = 'Uso_compost_Lombricultura' THEN 1 ELSE 0 END) AS disposicion_no_organicos_pae_lombricultura
          FROM " . $db->getTable('tbl_pae') . " p $join 
WHERE $where";
    $stmt = $pdo->query($q55);
    $result = $stmt->fetch(PDO::FETCH_NUM);
    $disposicion_no_organicos_pae_enterrado = $result[0];
    $disposicion_no_organicos_pae_quemado = $result[1];
    $disposicion_no_organicos_pae_reciclan = $result[2];
    $disposicion_no_organicos_pae_tiran_lote = $result[3];
    $disposicion_no_organicos_pae_lombricultura = $result[4];

    // Consulta 56: tamaño Neveras principales
    $q56 = "SELECT 
          SUM(CASE WHEN tamano_neveras_principales = 'Nevera_domestica_vertical_400_800L' THEN 1 ELSE 0 END) AS tamano_neveras_principales_nevera_domestica_vertical_400_800L,
          SUM(CASE WHEN tamano_neveras_principales = 'Nevera_domestica_vertical_menor_400L' THEN 1 ELSE 0 END) AS tamano_neveras_principales_nevera_domestica_vertical_menor_400L,
      SUM(CASE WHEN tamano_neveras_principales = 'Nevera_vertical_1600_2200L' THEN 1 ELSE 0 END) AS tamano_neveras_principales_nevera_domestica_vertical_2200l,
      SUM(CASE WHEN tamano_neveras_principales = 'Nevera_vertical_1200_1600L' THEN 1 ELSE 0 END) AS tamano_neveras_principales_nevera_domestica_vertical_1200l,
        SUM(CASE WHEN tamano_neveras_principales = 'Otro_Tamano_nevera' THEN 1 ELSE 0 END) AS tamano_neveras_principales_nevera_domestica_vertical_otra       
          FROM " . $db->getTable('tbl_pae') . " p $join 
WHERE $where";
    $stmt = $pdo->query($q56);
    $result = $stmt->fetch(PDO::FETCH_NUM);
    $tamano_neveras_principales_nevera_domestica_vertical_400_800L = $result[0];
    $tamano_neveras_principales_nevera_domestica_vertical_menor_400L = $result[1];
    $tamano_neveras_principales_nevera_domestica_vertical_2200l = $result[2];
    $tamano_neveras_principales_nevera_domestica_vertical_1200l = $result[3];
    $tamano_neveras_principales_nevera_domestica_vertical_otra = $result[4];


    // Consulta 57: tamaño Congeladores
    $q57 = "SELECT 
          SUM(CASE WHEN tamano_congelador = 'Congelador_Grande_1400_1600L' THEN 1 ELSE 0 END) AS tamano_congelador_Congelador_Grande_1400_1600L,
          SUM(CASE WHEN tamano_congelador = 'Congelador_Pequeño_Menor_400L' THEN 1 ELSE 0 END) AS tamano_congelador_Congelador_Pequeño_Menor_400L
    
          FROM " . $db->getTable('tbl_pae') . " p $join 
WHERE $where";
    $stmt = $pdo->query($q57);
    $result = $stmt->fetch(PDO::FETCH_NUM);
    $tamano_congelador_Congelador_Grande_1400_1600L = $result[0];
    $tamano_congelador_Congelador_Pequeño_Menor_400L = $result[1];


    // Consulta 58: ollas pae
    $q58 = "SELECT 
          SUM(CASE WHEN posee_ollas_pae = 'Si' THEN 1 ELSE 0 END) AS posee_ollas_pae_si,
          SUM(CASE WHEN posee_ollas_pae = 'No' THEN 1 ELSE 0 END) AS posee_ollas_pae_no  
          FROM " . $db->getTable('tbl_pae') . " p $join 
WHERE $where";
    $stmt = $pdo->query($q58);
    $result = $stmt->fetch(PDO::FETCH_NUM);
    $posee_ollas_pae_si = $result[0];
    $posee_ollas_pae_no = $result[1];

    // Consulta 59: cuchillos pae
    $q59 = "SELECT 
      SUM(CASE WHEN posee_cuchillos_pae = 'Si' THEN 1 ELSE 0 END) AS posee_cuchillos_pae_si,
      SUM(CASE WHEN posee_cuchillos_pae = 'No' THEN 1 ELSE 0 END) AS posee_cuchillos_pae_no  
      FROM " . $db->getTable('tbl_pae') . " p $join 
WHERE $where";
    $stmt = $pdo->query($q59);
    $result = $stmt->fetch(PDO::FETCH_NUM);
    $posee_cuchillos_pae_si = $result[0];
    $posee_cuchillos_pae_no = $result[1];

    // Consulta 59: cucharones pae
    $q59 = "SELECT 
      SUM(CASE WHEN posee_cucharones_pae = 'Si' THEN 1 ELSE 0 END) AS posee_cucharones_pae_si,
      SUM(CASE WHEN posee_cucharones_pae = 'No' THEN 1 ELSE 0 END) AS posee_cucharones_pae_no  
      FROM " . $db->getTable('tbl_pae') . " p $join 
WHERE $where";
    $stmt = $pdo->query($q59);
    $result = $stmt->fetch(PDO::FETCH_NUM);
    $posee_cucharones_pae_si = $result[0];
    $posee_cucharones_pae_no = $result[1];

// Consulta 60: niños PAE sentados
$q60 = "SELECT 
    SUM(CASE WHEN cant_ninos_pae_sentados = 'Todos_100%' THEN 1 ELSE 0 END) AS cant_ninos_pae_sentados_todos,
    SUM(CASE WHEN cant_ninos_pae_sentados = 'Un_poco_mas_mitad_75%' THEN 1 ELSE 0 END) AS cant_ninos_pae_mas_75  
    FROM " . $db->getTable('tbl_pae') . " p $join 
    WHERE $where";
$stmt = $pdo->query($q60);
$result = $stmt->fetch(PDO::FETCH_NUM);
$cant_ninos_pae_sentados_todos = $result[0];
$cant_ninos_pae_mas_75 = $result[1];


    // Consulta 61: Niños Focalizados
    $q61 = "SELECT SUM(ninos_focalizados) AS ninos_foc
      FROM " . $db->getTable('tbl_pae') . " p $join 
WHERE $where";
    $ninos_foc = $pdo->query($q61)->fetchColumn();



    //consulta para mapa y graficos, del pae
    $q61 = "SELECT 
              tbl_pae.id as tbl_pae_id,
              tbl_pae.tbl_municipio_id,
              tbl_ciudades_accion_unificada.municipio,
              tbl_vereda.nombre_vereda,
              tbl_sede_educativa.nombre,
              tbl_pae.concepto_sanitario,
              tbl_pae.estado_techo_almacenamiento,
              tbl_pae.estado_piso_almacenamiento,
              tbl_pae.estado_paredes_almacenamiento,
              tbl_pae.estado_techo_preparacion,
              tbl_pae.estado_piso_preparacion,
              tbl_pae.estado_paredes_preparacion,
              tbl_pae.estado_sede,
              tbl_pae.latitud,
              tbl_pae.longitud, tbl_vereda.path, 
              tbl_vereda.fill
              FROM  " . $db->getTable('tbl_pae') . "
              LEFT JOIN  " . $db->getTable('tbl_sede_educativa') . " ON tbl_pae.tbl_sede_educativa_id = tbl_sede_educativa.id
              LEFT JOIN " . $db->getTable('tbl_ciudades_accion_unificada') . " ON tbl_pae.tbl_municipio_id = tbl_ciudades_accion_unificada.codigo_muncipio
              LEFT JOIN " . $db->getTable('tbl_vereda') . "   ON tbl_ciudades_accion_unificada.codigo_muncipio = tbl_vereda.municipio_id
              WHERE   tbl_pae.estado_paredes_almacenamiento <> 'bueno'
                      AND tbl_pae.estado_techo_preparacion <> 'bueno'
                      AND tbl_pae.estado_piso_preparacion <> 'bueno'
                      AND tbl_pae.estado_paredes_preparacion <> 'bueno'
                      AND tbl_pae.estado_sede <> 'Nuevo_Activo'";
    $arrjson = array(
      'output' => array(
        'valid' => true,
        'neveras' => $neveras,
        'neveras_fun' => $neveras_fun,
        'neveras_buenas' => $neveras_buenas,
        'neveras_almacenamiento_si' => $neveras_almacenamiento_si,
        'neveras_almacenamiento_no' => $neveras_almacenamiento_no,
        'congeladores' => $congeladores,
        'congeladores_funcionando' => $congeladores_funcionando,
        'estufas' => $estufas,
        'estufas_gen,' => $estufas_gen,
        'quemadores_estufas_buenos' => $quemadores_estufas_buenos,
        'quemadores_estufas' => $quemadores_estufas,
        'licuadoras' => $licuadoras,
        'licuadoras_gen,' => $licuadoras_gen,
        'licuadoras_industriales' => $licuadoras_industriales,
        'licuadoras_total' => $licuadoras_total,
        'cantidad_platos' => $cantidad_platos,
        'cantidad_cucharas' => $cantidad_cucharas,
        'cantidad_pocillos' => $cantidad_pocillos,
        'cantidad_tenedores' => $cantidad_tenedores,
        'cantidad_canecas' => $cantidad_canecas,
        'acceso_alcantarillado_si' => $acceso_alcantarillado_si,
        'acceso_alcantarillado_no' => $acceso_alcantarillado_no,
        'recoleccion_basuras_si' => $recoleccion_basuras_si,
        'recoleccion_basuras_no' => $recoleccion_basuras_no,
        'espacio_preparacion_si' => $espacio_preparacion_si,
        'espacio_preparacion_no' => $espacio_preparacion_no,
        'espacio_almacenamiento_no' => $espacio_almacenamiento_no,
        'espacio_almacenamiento_si' => $espacio_almacenamiento_si,
        'zona_conflicto_si' => $zona_conflicto_si,
        'zona_conflicto_no' => $zona_conflicto_no,
        'algo_frecuente_conflicto' => $algo_frecuente_conflicto,
        'no_frecuencia_conflicto' => $no_frecuencia_conflicto,
        'poco_frecuente_conflicto' => $poco_frecuente_conflicto,
        'cercania_contaminacion_si' => $cercania_contaminacion_si,
        'cercania_contaminacion_no' => $cercania_contaminacion_no,
        'concepto_sanitario_si' => $concepto_sanitario_si,
        'concepto_sanitario_no' => $concepto_sanitario_no,
        'complemento_preparado_sitio_si' => $complemento_preparado_sitio_si,
        'complemento_preparado_sitio_no' => $complemento_preparado_sitio_no,
        'complemento_industrializado_si' => $complemento_industrializado_si,
        'complemento_industrializado_no' => $complemento_industrializado_no,
        'almuerzo_preparado_sitio_si' => $almuerzo_preparado_sitio_si,
        'almuerzo_preparado_sitio_no' => $almuerzo_preparado_sitio_no,
        'complemento_inidustrializado_si' => $complemento_industrializado_si,
        'almuerzo_trasportado_si' => $almuerzo_transportado_si,
        'almuerzo_trasportado_no' => $almuerzo_transportado_no,
        'lavamanos_personal_si' => $lavamanos_personal_si,
        'lavamanos_personal_no' => $lavamanos_personal_no,
        'sanitario_personal_si' => $sanitario_personal_si,
        'sanitario_personal_no' => $sanitario_personal_no,
        'almacenamiento_personal_si' => $almacenamiento_personal_si,
        'almacenamiento_personal_no' => $almacenamiento_personal_no,
        'caracterizaciones' => $caracterizaciones,
        'zona_rural' => $zona_rural,
        'zona_urbana' => $zona_urbana,
        'acceso_agua_si' => $acceso_agua_si,
        'acceso_agua_no' => $acceso_agua_no,
        'acceso_agua_intermitente' => $acceso_agua_intermitente,
        'almacena_alto_suelo_si' => $almacena_alto_suelo_si,
        'almacena_alto_suelo_no' => $almacena_alto_suelo_no,
        'almacena_balde' => $almacena_balde,
        'almacena_canasta' => $almacena_canasta,
        'almacena_estante' => $almacena_estante,
        'almacena_ninguno' => $almacena_ninguno,
        'almacena_na' => $almacena_na,
        'almacena_caja' => $almacena_caja,
        'acueducto' => $acueducto,
        'embotellada' => $embotellada,
        'lluvia' => $lluvia,
        'carrotanque' => $carrotanque,
        'otros_agua' => $otros_agua,
        'pozo_agua' => $pozo_agua,
        'rios_quebradas' => $rios_quebradas,
        'acceso_electricidad_si' => $acceso_electricidad_si,
        'acceso_electricidad_no' => $acceso_electricidad_no,
        'acceso_electricidad_intermitente' => $acceso_electricidad_intermitente,
        'electricidad' => $electricidad,
        'lena' => $lena,
        'desecho' => $desecho,
        'no_aplica' => $no_aplica,
        'petroleo_gasolina' => $petroleo_gasolina,
        'gas_natural' => $gas_natural,
        'comedor_escolar_si' => $comedor_escolar_si,
        'comedor_escolar_no' => $comedor_escolar_no,
        'no_tiene_concepto' => $no_tiene_concepto,
        'si_tiene_favorable' => $si_tiene_favorable,
        'si_favorable_requerimientos' => $si_favorable_requerimientos,
        'si_desfavorable' => $si_desfavorable,
        'estado_sede_antiguo_activo' => $estado_sede_antiguo_activo,
        'estado_sede_nuevo_activo' => $estado_sede_nuevo_activo,
        'estado_sede_cierre_temporal' => $estado_sede_cierre_temporal,
        'estado_techo_almacenamiento_bueno' => $estado_techo_almacenamiento_bueno,
        'estado_techo_almacenamiento_malo' => $estado_techo_almacenamiento_malo,
        'estado_techo_almacenamiento_regular' => $estado_techo_almacenamiento_regular,
        'estado_paredes_bueno' => $estado_paredes_bueno,
        'estado_paredes_regular' => $estado_paredes_regular,
        'estado_paredes_malo' => $estado_paredes_malo,
        'material_paredes_preparacion_ladrillo' => $material_paredes_preparacion_ladrillo,
        'material_paredes_preparacion_prefabricado' => $material_paredes_preparacion_prefabricado,
        'material_paredes_preparacion_otros' => $material_paredes_preparacion_otros,
        'material_paredes_preparacion_bahareque' => $material_paredes_preparacion_bahareque,
        'estado_piso_bueno' => $estado_piso_bueno,
        'estado_piso_regular' => $estado_piso_regular,
        'estado_piso_malo' => $estado_piso_malo,
        'material_piso_preparacion_baldosa' => $material_piso_preparacion_baldosa,
        'material_piso_cemento' => $material_piso_cemento,
        'material_piso_ladrillo' => $material_piso_ladrillo,
        'material_piso_preparacion_madera' => $material_piso_preparacion_madera,
        'material_piso_preparacion_otros' => $material_piso_preparacion_otros,
        'estado_techo_bueno' => $estado_techo_bueno,
        'estado_techo_regular' => $estado_techo_regular,
        'estado_techo_malo' => $estado_techo_malo,
        'material_techo_preparacion_zinc' => $material_techo_preparacion_zinc,
        'material_techo_eternit' => $material_techo_eternit,
        'material_techo_teja_barro' => $material_techo_teja_barro,
        'material_techo_preparacion_plastico' => $material_techo_preparacion_plastico,
        'material_techo_preparacion_sin_techo' => $material_techo_preparacion_sin_techo,
        'material_techo_preparacion_concreto' => $material_techo_preparacion_concreto,
        'material_techo_preparacion_metal_acero' => $material_techo_preparacion_metal_acero,
        'material_techo_preparacion_paja' => $material_techo_preparacion_paja,
        'material_techo_preparacion_otros' => $material_techo_preparacion_otros,
        'estado_paredes_almacenamiento_bueno' => $estado_paredes_almacenamiento_bueno,
        'estado_paredes_almacenamiento_regular' => $estado_paredes_almacenamiento_regular,
        'estado_paredes_almacenamiento_malo' => $estado_paredes_almacenamiento_malo,
        'material_paredes_almacenamiento_bloque' => $material_paredes_almacenamiento_bloque,
        'material_paredes_almacenamiento_bahareque' => $material_paredes_almacenamiento_bahareque,
        'material_paredes_almacenamiento_prefabricado' => $material_paredes_almacenamiento_prefabricado,
        'material_paredes__almacenamiento_madera' => $material_paredes__almacenamiento_madera,
        'material_paredes_almacenamiento_otros' => $material_paredes_almacenamiento_otros,
        'estado_piso_almacenamiento_bueno' => $estado_piso_almacenamiento_bueno,
        'estado_piso_almacenamiento_regular' => $estado_piso_almacenamiento_regular,
        'estado_piso_almacenamiento_malo' => $estado_piso_almacenamiento_malo,
        'material_piso_almacenamiento_bloque' => $material_piso_almacenamiento_bloque,
        'material_piso_almacenamiento_cemento' => $material_piso_almacenamiento_cemento,
        'material_piso_almacenamiento_ladrillo' => $material_piso_almacenamiento_ladrillo,
        'material_piso_almacenamiento_madera' => $material_piso_almacenamiento_madera,
        'material_piso_almacenamiento_otros' => $material_piso_almacenamiento_otros,
        'material_piso_almacenamiento_baldosa' => $material_piso_almacenamiento_baldosa,
        'estado_techo_almacenamiento_bueno' => $estado_techo_almacenamiento_bueno,
        'estado_techo_almacenamiento_regular' => $estado_techo_almacenamiento_regular,
        'estado_techo_almacenamiento_malo' => $estado_techo_almacenamiento_malo,
        'material_techo_almacenamiento_eternit' => $material_techo_almacenamiento_eternit,
        'material_techo_almacenamiento_tejas' => $material_techo_almacenamiento_tejas,
        'material_techo_almacenamiento_plastico' => $material_techo_almacenamiento_plastico,
        'material_techo_almacenamiento_zinc' => $material_techo_almacenamiento_zinc,
        'material_techo_almacenamiento_concreto' => $material_techo_almacenamiento_concreto,
        'material_piso_almacenamiento_baldosa' => $material_piso_almacenamiento_baldosa,
        'material_techo_almacenamiento_otros' => $material_techo_almacenamiento_otros,
        'material_techo_almacenamiento_metal' => $material_techo_almacenamiento_metal,
        'material_techo_almacenamiento_paja' => $material_techo_almacenamiento_paja,
        'disposicion_derechos_pae_enterrado' => $disposicion_derechos_pae_enterrado,
        'disposicion_derechos_pae_quemado' => $disposicion_derechos_pae_quemado,
        'disposicion_derechos_pae_reciclan' => $disposicion_derechos_pae_reciclan,
        'disposicion_derechos_pae_tiran_lote' => $disposicion_derechos_pae_tiran_lote,
        'disposicion_derechos_pae_lombricultura' => $disposicion_derechos_pae_lombricultura,
        'disposicion_no_organicos_pae_enterrado' => $disposicion_no_organicos_pae_enterrado,
        'disposicion_no_organicos_pae_quemado' => $disposicion_no_organicos_pae_quemado,
        'disposicion_no_organicos_pae_reciclan' => $disposicion_no_organicos_pae_reciclan,
        'disposicion_no_organicos_pae_tiran_lote' => $disposicion_no_organicos_pae_tiran_lote,
        'disposicion_no_organicos_pae_lombricultura' => $disposicion_no_organicos_pae_lombricultura,
        'tamano_neveras_principales_nevera_domestica_vertical_400_800L' => $tamano_neveras_principales_nevera_domestica_vertical_400_800L,
        'tamano_neveras_principales_nevera_domestica_vertical_menor_400L' => $tamano_neveras_principales_nevera_domestica_vertical_menor_400L,
        'tamano_neveras_principales_nevera_domestica_vertical_2200l' => $tamano_neveras_principales_nevera_domestica_vertical_2200l,
        'tamano_neveras_principales_nevera_domestica_vertical_1200l' => $tamano_neveras_principales_nevera_domestica_vertical_1200l,
        'tamano_neveras_principales_nevera_domestica_vertical_otra' => $tamano_neveras_principales_nevera_domestica_vertical_otra,
        'tamano_congelador_Congelador_Grande_1400_1600L' => $tamano_congelador_Congelador_Grande_1400_1600L,
        'tamano_congelador_Congelador_Pequeño_Menor_400L' => $tamano_congelador_Congelador_Pequeño_Menor_400L,
        'tamano_congelador_Congelador_Pequeño_Menor_400L' => $tamano_congelador_Congelador_Pequeño_Menor_400L,
        'posee_ollas_pae_si' => $posee_ollas_pae_si,
        'posee_ollas_pae_no' => $posee_ollas_pae_no,
        'posee_cuchillos_pae_si' => $posee_cuchillos_pae_si,
        'posee_cuchillos_pae_no' => $posee_cuchillos_pae_no,
        'posee_cucharones_pae_si' => $posee_cucharones_pae_si,
        'posee_cucharones_pae_no' => $posee_cucharones_pae_no,
        'cant_ninos_pae_sentados_todos' => $cant_ninos_pae_sentados_todos,
        'cant_ninos_pae_mas_75' => $cant_ninos_pae_mas_75,
        'ninos_foc' => $ninos_foc
      )
    );

    // Cerrar la conexión
    $db->closeConect();

    return $arrjson;
  }
}
