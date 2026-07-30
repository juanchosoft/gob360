 <?php
    include './admin/include/head.php';
    function getUrl()
    {
        $port = $_SERVER["SERVER_PORT"];
        $nameServer = $port != "80" ? $_SERVER['SERVER_NAME'] . ":" . $port : $_SERVER['SERVER_NAME'];
        $url = sprintf(
            "%s://%s%s",
            isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off' ? 'https' : 'http',
            $nameServer,
            $_SERVER['REQUEST_URI']
        );
        $final =  str_replace(basename($_SERVER["SCRIPT_FILENAME"], '.php') . ".php", "", $url);
        $exists = strpos($final, "?");
        if ($exists == !false) {
            $final =  substr($final, 0, $exists);
            return $final;
        } else {
            return $final;
        }
    }

    require_once './admin/include/generic_classes.php';
    include './admin/classes/Ciudad.php';
    include './admin/classes/Estado.php';
    require './admin/classes/Departamento.php';
    include './admin/db/colores.php';
    include './admin/classes/MainPae.php';


    // Obtener permisos
    /*     $permissions = PagePermissions::crudForCurrentPage();

    // Validación de permiso de visualización
    if (!$permissions['view']) {
        require_once 'permiso_denegado.php';
        exit;
    } */


    // Información de Departamentos
    $arrDep = Departamento::getAll(null);
    $isvalid = $arrDep['output']['valid'];
    $arrDep = $arrDep['output']['response'];
    $optionDep = Util::getDepartamentoPrincipal();
    foreach ($arrDep as $val) {
        $optionDep .= "<option " . ($val["codigo_departamento"] == Util::getDepartamentoPrincipal() ? "selected" : "") . " value='" . $val['codigo_departamento'] . "'>" . $val['codigo_departamento'] . " - " . $val['departamento'] . "</option>";
    }

    $codigoMunicipio = $_REQUEST['mun'];
    $parametrosPae = ['codigoMunicipio' => $codigoMunicipio, 'departamentoId' => Util::getDepartamentoPrincipal()];

    //informacion del mail
    $arr = MainPae::getDataMain($parametrosPae);
    $isvalid = $arr['output']['valid'];
    $variables = [
        'disposicion_derechos_pae_enterrado',
        'disposicion_derechos_pae_quemado',
        'disposicion_derechos_pae_reciclan',
        'disposicion_derechos_pae_lombricultura',
        'disposicion_derechos_pae_tiran_lote',
        'disposicion_no_organicos_pae_enterrado',
        'disposicion_no_organicos_pae_quemado',
        'disposicion_no_organicos_pae_reciclan',
        'disposicion_no_organicos_pae_lombricultura',
        'disposicion_no_organicos_pae_tiran_lote',
        'disposicion_no_organicos_pae_otros',
        'posee_ollas_pae_si',
        'posee_ollas_pae_no',
        'posee_cuchillos_pae_si',
        'posee_cuchillos_pae_no',
        'tamano_neveras_principales_nevera_domestica_vertical_2200l',
        'tamano_neveras_principales_nevera_domestica_vertical_1200l',
        'tamano_neveras_principales_nevera_domestica_vertical_400_800L',
        'tamano_neveras_principales_nevera_domestica_vertical_menor_400L',
        'tamano_neveras_principales_nevera_domestica_vertical_otra',
        'tamano_congelador_Congelador_Grande_1400_1600L',
        'tamano_congelador_Congelador_Pequeño_Menor_400L',
        'ninos_foc',
        'neveras',
        'neveras_fun',
        'neveras_buenas',
        'neveras_almacenamiento_si',
        'neveras_almacenamiento_no',
        'congeladores',
        'congeladores_funcionando',
        'estufas',
        'quemadores_estufas',
        'quemadores_estufas_buenos',
        'estufas_gen',
        'licuadoras_industriales',
        'licuadoras_total',
        'licuadoras',
        'cantidad_platos',
        'cantidad_cucharas',
        'cantidad_pocillos',
        'cantidad_tenedores',
        'cantidad_canecas',
        'acceso_alcantarillado_si',
        'acceso_alcantarillado_no',
        'recoleccion_basuras_si',
        'recoleccion_basuras_no',
        'espacio_preparacion_si',
        'espacio_preparacion_no',
        'espacio_almacenamiento_no',
        'espacio_almacenamiento_si',
        'zona_conflicto_si',
        'zona_conflicto_no',
        'algo_frecuente_conflicto',
        'no_frecuencia_conflicto',
        'poco_frecuente_conflicto',
        'cercania_contaminacion_si',
        'cercania_contaminacion_no',
        'concepto_sanitario_si',
        'concepto_sanitario_no',
        'complemento_preparado_sitio_si',
        'complemento_preparado_sitio_no',
        'complemento_industrializado_si',
        'complemento_industrializado_no',
        'almuerzo_preparado_sitio_si',
        'almuerzo_preparado_sitio_no',
        'almuerzo_trasportado_no',
        'almuerzo_trasportado_si',
        'lavamanos_personal_si',
        'lavamanos_personal_no',
        'sanitario_personal_si',
        'sanitario_personal_no',
        'almacenamiento_personal_si',
        'almacenamiento_personal_no',
        'caracterizaciones',
        'zona_rural',
        'zona_urbana',
        'acceso_agua_si',
        'acceso_agua_no',
        'acceso_agua_intermitente',
        'almacena_alto_suelo_si',
        'almacena_alto_suelo_no',
        'almacena_balde',
        'almacena_canasta',
        'almacena_estante',
        'almacena_ninguno',
        'almacena_na',
        'acueducto',
        'embotellada',
        'lluvia',
        'carrotanque',
        'rios_quebradas',
        'otros_agua',
        'pozo_agua',
        'acceso_electricidad_si',
        'acceso_electricidad_no',
        'acceso_electricidad_intermitente',
        'electricidad',
        'gas_natural',
        'lena',
        'desecho',
        'no_aplica',
        'petroleo_gasolina',
        'comedor_escolar_si',
        'comedor_escolar_no',
        'no_tiene_concepto',
        'si_tiene_favorable',
        'si_favorable_requerimientos',
        'si_desfavorable',
        'estado_sede_antiguo_activo',
        'estado_sede_nuevo_activo',
        'estado_sede_cierre_temporal',
        'estado_techo_almacenamiento_bueno',
        'estado_techo_almacenamiento_malo',
        'estado_techo_almacenamiento_regular',
        'estado_paredes_bueno',
        'estado_paredes_regular',
        'estado_paredes_malo',
        'material_paredes_preparacion_ladrillo',
        'material_paredes_preparacion_prefabricado',
        'material_paredes_preparacion_otros',
        'material_paredes_preparacion_bahareque',
        'estado_piso_bueno',
        'estado_piso_regular',
        'estado_piso_malo',
        'material_piso_preparacion_baldosa',
        'material_piso_cemento',
        'material_piso_ladrillo',
        'material_piso_preparacion_madera',
        'material_piso_preparacion_otros',
        'estado_techo_bueno',
        'estado_techo_regular',
        'estado_techo_malo',
        'material_techo_preparacion_zinc',
        'material_techo_eternit',
        'material_techo_teja_barro',
        'material_techo_preparacion_plastico',
        'material_techo_preparacion_sin_techo',
        'material_techo_preparacion_concreto',
        'material_techo_preparacion_metal_acero',
        'material_techo_preparacion_paja',
        'material_techo_preparacion_otros',
        'estado_paredes_almacenamiento_bueno',
        'estado_paredes_almacenamiento_regular',
        'estado_paredes_almacenamiento_malo',
        'material_paredes_almacenamiento_bloque',
        'material_paredes_almacenamiento_bahareque',
        'material_paredes_almacenamiento_prefabricado',
        'material_paredes__almacenamiento_madera',
        'material_paredes_almacenamiento_otros',
        'estado_piso_almacenamiento_bueno',
        'estado_piso_almacenamiento_regular',
        'estado_piso_almacenamiento_malo',
        'material_piso_almacenamiento_bloque',
        'material_piso_almacenamiento_cemento',
        'material_piso_almacenamiento_ladrillo',
        'material_piso_almacenamiento_madera',
        'material_piso_almacenamiento_otros',
        'material_piso_almacenamiento_baldosa',
        'estado_techo_almacenamiento_bueno',
        'estado_techo_almacenamiento_regular',
        'estado_techo_almacenamiento_malo',
        'material_techo_almacenamiento_eternit',
        'material_techo_almacenamiento_tejas',
        'material_techo_almacenamiento_plastico',
        'material_techo_almacenamiento_zinc',
        'material_techo_almacenamiento_concreto',
        'material_techo_almacenamiento_otros',
        'material_techo_almacenamiento_metal',
        'posee_cucharones_pae_si',
        'posee_cucharones_pae_no'
    ];


    foreach ($variables as $variable) {
        $$variable = isset($arr['output'][$variable]) ? $arr['output'][$variable] : 0;
    }


    //calculos dashboard
    function calcular_porcentaje($valor, $total)
    {
        return $total > 0 ? ($valor * 100) / $total : 0;
    }

    $neveras_malas = $neveras - $neveras_fun;
    $porcentaje_neveras = calcular_porcentaje($neveras_fun, $neveras);

    $congeladores_malas = $congeladores - $congeladores_funcionando;
    $porcentaje_congeladores = calcular_porcentaje($congeladores_funcionando, $congeladores);

    $quemadores_malas = $quemadores_estufas - $quemadores_estufas_buenos;
    $porcentaje_quemadores = calcular_porcentaje($quemadores_estufas_buenos, $quemadores_estufas);

    $total_licuadoras = $licuadoras_total + $licuadoras_industriales;
    $licuadoras_malas = $licuadoras_industriales - $licuadoras;
    $porcentaje_licuadoras = calcular_porcentaje($licuadoras, $licuadoras_industriales);

    $porcentaje_alm_no = calcular_porcentaje($espacio_almacenamiento_no, $caracterizaciones);
    $porcentaje_alm_si = calcular_porcentaje($espacio_almacenamiento_si, $caracterizaciones);

    $porcentaje_prepa_si = calcular_porcentaje($espacio_preparacion_si, $caracterizaciones);
    $porcentaje_prepa_no = calcular_porcentaje($espacio_preparacion_no, $caracterizaciones);

    $porcentaje_prepa_sitio_si = calcular_porcentaje($almuerzo_preparado_sitio_si, $caracterizaciones);
    $porcentaje_prepa_sitio_no = calcular_porcentaje($almuerzo_preparado_sitio_no, $caracterizaciones);

    $porcentaje_transporte_almuer_si = calcular_porcentaje($almuerzo_trasportado_si, $caracterizaciones);
    $porcentaje_transporte_almuer_no = calcular_porcentaje($almuerzo_trasportado_no, $caracterizaciones);

    $porcentaje_complemento_prepa_sitio_si = calcular_porcentaje($complemento_preparado_sitio_si, $caracterizaciones);
    $porcentaje_complemento_prepa_sitio_no = calcular_porcentaje($complemento_preparado_sitio_no, $caracterizaciones);

    $porcentaje_complemento_industri_sitio_si = calcular_porcentaje($complemento_industrializado_si, $caracterizaciones);
    $porcentaje_complemento_industri_sitio_no = calcular_porcentaje($complemento_industrializado_no, $caracterizaciones);

    $porcentaje_armado_no_frecuente = calcular_porcentaje($no_frecuencia_conflicto, $caracterizaciones);
    $porcentaje_armado_poco = calcular_porcentaje($poco_frecuente_conflicto, $caracterizaciones);
    $porcentaje_armado_algo = calcular_porcentaje($algo_frecuente_conflicto, $caracterizaciones);

    $porcentaje_cercania_contaminacion_si = calcular_porcentaje($cercania_contaminacion_si, $caracterizaciones);
    $porcentaje_cercania_contaminacion_no = calcular_porcentaje($cercania_contaminacion_no, $caracterizaciones);

    $porcentaje_acceso_agua_si = calcular_porcentaje($acceso_agua_si, $caracterizaciones);
    $porcentaje_acceso_agua_no = calcular_porcentaje($acceso_agua_no, $caracterizaciones);
    $porcentaje_acceso_agua_intermitente = calcular_porcentaje($acceso_agua_intermitente, $caracterizaciones);

    $porcentaje_zona_conflicto_si = calcular_porcentaje($zona_conflicto_si, $caracterizaciones);
    $porcentaje_zona_conflicto_no = calcular_porcentaje($zona_conflicto_no, $caracterizaciones);

    $porcentaje_almacena_alto_suelo_si = calcular_porcentaje($almacena_alto_suelo_si, $caracterizaciones);
    $porcentaje_almacena_alto_suelo_no = calcular_porcentaje($almacena_alto_suelo_no, $caracterizaciones);

    $porcentaje_acceso_electricidad_si = calcular_porcentaje($acceso_electricidad_si, $caracterizaciones);
    $porcentaje_acceso_electricidad_no = calcular_porcentaje($acceso_electricidad_no, $caracterizaciones);
    $porcentaje_acceso_electricidad_intermitente = calcular_porcentaje($acceso_electricidad_intermitente, $caracterizaciones);

    $porcentaje_comedor_escolar_si = calcular_porcentaje($comedor_escolar_si, $caracterizaciones);
    $porcentaje_comedor_escolar_no = calcular_porcentaje($comedor_escolar_no, $caracterizaciones);

    $porcentaje_estado_sede_antiguo_activo = calcular_porcentaje($estado_sede_antiguo_activo, $caracterizaciones);
    $porcentaje_estado_sede_nuevo_activo = calcular_porcentaje($estado_sede_nuevo_activo, $caracterizaciones);
    $porcentaje_estado_sede_cierre_temporal = calcular_porcentaje($estado_sede_cierre_temporal, $caracterizaciones);

    $porcentaje_estado_techo_almacenamiento_bueno = calcular_porcentaje($estado_techo_almacenamiento_bueno, $caracterizaciones);
    $porcentaje_estado_techo_almacenamiento_malo = calcular_porcentaje($estado_techo_almacenamiento_malo, $caracterizaciones);
    $porcentaje_estado_techo_almacenamiento_regular = calcular_porcentaje($estado_techo_almacenamiento_regular, $caracterizaciones);

    $porcentaje_estado_paredes_bueno = calcular_porcentaje($estado_paredes_bueno, $caracterizaciones);
    $porcentaje_estado_paredes_malo = calcular_porcentaje($estado_paredes_malo, $caracterizaciones);
    $porcentaje_estado_paredes_regular = calcular_porcentaje($estado_paredes_regular, $caracterizaciones);

    $porcentaje_estado_piso_bueno = calcular_porcentaje($estado_piso_bueno, $caracterizaciones);
    $porcentaje_estado_piso_malo = calcular_porcentaje($estado_piso_malo, $caracterizaciones);
    $porcentaje_estado_piso_regular = calcular_porcentaje($estado_piso_regular, $caracterizaciones);

    $porcentaje_estado_techo_bueno = calcular_porcentaje($estado_techo_bueno, $caracterizaciones);
    $porcentaje_estado_techo_malo = calcular_porcentaje($estado_techo_malo, $caracterizaciones);
    $porcentaje_estado_techo_regular = calcular_porcentaje($estado_techo_regular, $caracterizaciones);

    $porcentaje_estado_paredes_almacenamiento_bueno = calcular_porcentaje($estado_paredes_almacenamiento_bueno, $caracterizaciones);
    $porcentaje_estado_paredes_almacenamiento_regular = calcular_porcentaje($estado_paredes_almacenamiento_regular, $caracterizaciones);
    $porcentaje_estado_paredes_almacenamiento_malo = calcular_porcentaje($estado_paredes_almacenamiento_malo, $caracterizaciones);

    $porcentaje_estado_piso_almacenamiento_bueno = calcular_porcentaje($estado_piso_almacenamiento_bueno, $caracterizaciones);
    $porcentaje_estado_piso_almacenamiento_regular = calcular_porcentaje($estado_piso_almacenamiento_regular, $caracterizaciones);
    $porcentaje_estado_piso_almacenamiento_malo = calcular_porcentaje($estado_piso_almacenamiento_malo, $caracterizaciones);

    $porcentaje_posee_ollas_pae_si = calcular_porcentaje($posee_ollas_pae_si, $caracterizaciones);
    $porcentaje_posee_ollas_pae_no = calcular_porcentaje($posee_ollas_pae_no, $caracterizaciones);

    $porcentaje_posee_cuchillos_pae_si = calcular_porcentaje($posee_cuchillos_pae_si, $caracterizaciones);
    $porcentaje_posee_cuchillos_pae_no = calcular_porcentaje($posee_cuchillos_pae_no, $caracterizaciones);

    $porcentaje_posee_cucharones_pae_si = calcular_porcentaje($posee_cucharones_pae_si, $caracterizaciones);
    $porcentaje_posee_cucharones_pae_no = calcular_porcentaje($posee_cucharones_pae_no, $caracterizaciones);

    $porcentaje_cant_ninos_pae_sentados_todos = calcular_porcentaje($cant_ninos_pae_sentados_todos, $caracterizaciones);
    $porcentaje_cant_ninos_pae_mas_75 = calcular_porcentaje($cant_ninos_pae_mas_75, $caracterizaciones);



    // Valores en porcentaje (0–100)
    $valor  = $porcentaje_posee_cucharones_pae_no;
    $valor1 = $porcentaje_posee_cuchillos_pae_no;
    $valor2 = $porcentaje_posee_ollas_pae_no;
    $valor3 = $almacena_ninguno;
    $valor4 = $porcentaje_almacena_alto_suelo_no;
    $valor5 = $porcentaje_estado_techo_almacenamiento_malo;
    $valor6 = $porcentaje_estado_paredes_malo;
    $valor7 = $porcentaje_estado_piso_almacenamiento_malo;
    $valor8 = $porcentaje_estado_techo_malo;
    $valor9 = $porcentaje_estado_paredes_almacenamiento_malo;
    $valor10 = $porcentaje_estado_piso_malo;
    $valor11 = $porcentaje_acceso_agua_intermitente;
    $valor12 =  $porcentaje_acceso_electricidad_intermitente;
    $valor13 =  $porcentaje_prepa_sitio_no;
    $valor14 = $porcentaje_complemento_prepa_sitio_no;
    $valor15 = $porcentaje_complemento_industri_sitio_no;
    $valor16 = $porcentaje_comedor_escolar_no;
    $valor17 = $no_tiene_concepto;
    $valor18 = $porcentaje_cant_ninos_pae_mas_75;
    $valor19 = $porcentaje_estado_sede_antiguo_activo;
    $valor20 = $estado_techo_almacenamiento_malo;


    // Función para determinar la clase según el valor bien si esta bajito
    function getColorClass($valor)
    {
        if ($valor >= 1 && $valor <= 20) {
            return 'bg-success text-white'; // Verde
        } elseif ($valor >= 21 && $valor <= 35) {
            return 'bg-warning text-dark';  // Amarillo
        } elseif ($valor >= 36 && $valor <= 60) {
            return 'bg-orange text-white';  // Naranja (agregar clase personalizada)
        } elseif ($valor >= 61 && $valor <= 1500) {
            return 'bg-danger text-white';  // Rojo
        } else {
            return ''; // Sin clase si está fuera de rango
        }
    }

    // Asignar clases de color por cada valor
    $colorClase  = getColorClass($valor);
    $colorClase1 = getColorClass($valor1);
    $colorClase2 = getColorClass($valor2);
    $colorClase3 = getColorClass($valor3);
    $colorClase4 = getColorClass($valor4);
    $colorClase5 = getColorClass($valor5);
    $colorClase6 = getColorClass($valor6);
    $colorClase7 = getColorClass($valor7);
    $colorClase8 = getColorClass($valor8);
    $colorClase9 = getColorClass($valor9);
    $colorClase10 = getColorClass($valor10);
    $colorClase11 = getColorClass($valor11);
    $colorClase12 = getColorClass($valor12);
    $colorClase13 = getColorClass($valor13);
    $colorClase14 = getColorClass($valor14);
    $colorClase15 = getColorClass($valor15);
    $colorClase16 = getColorClass($valor16);
    $colorClase17 = getColorClass($valor17);
    $colorClase18 = getColorClass($valor18);
    $colorClase19 = getColorClass($valor19);
    $colorClase20 = getColorClass($valor20);
    //=========================================================================//==================================

    // Función para asignar clase de color mal si esta bajito

    function getColorClassb($valora)
    {
        if ($valora >= 1 && $valora <= 20) {
            return 'bg-danger text-white'; // Rojo
        } elseif ($valora >= 21 && $valora <= 35) {
            return 'bg-orange text-white'; // Naranja
        } elseif ($valora >= 36 && $valora <= 60) {
            return 'bg-warning text-dark'; // Amarillo
        } elseif ($valora >= 61 && $valora <= 100) {
            return 'bg-success text-white'; // Verde
        }
        return ''; // Por si el valor está fuera de rango
    }

    // Definir valores
    $valora  = $porcentaje_neveras;
    $valora1 = $porcentaje_congeladores;
    $valora2 = $porcentaje_quemadores;
    $valora3 = $porcentaje_licuadoras;
    $valora4 = $porcentaje_alm_no;
    $valora5 = $porcentaje_prepa_no;
    $valora9 = $porcentaje_transporte_almuer_no;
    $valora10 = $porcentaje_cercania_contaminacion_no;
    $valora11 = $porcentaje_zona_conflicto_no;
    $valora12 = $porcentaje_armado_no_frecuente;


    // Aplicar colores a cada uno
    $colorClasea = getColorClassb($valora);
    $colorClasea1 = getColorClassb($valora1);
    $colorClasea2 = getColorClassb($valora2);
    $colorClasea3 = getColorClassb($valora3);
    $colorClasea4 = getColorClassb($valora4);
    $colorClasea5 = getColorClassb($valora5);
    $colorClasea6 = getColorClassb($valora6);
    $colorClasea7 = getColorClassb($valora7);
    $colorClasea8 = getColorClassb($valora8);
    $colorClasea9 = getColorClassb($valora9);
    $colorClasea10 = getColorClassb($valora10);
    $colorClasea11 = getColorClassb($valora11);
    $colorClasea12 = getColorClassb($valora12);

    $departamento = new Departamento();
    $santander = $departamento->getAll(["id" => 21]);
    $santander = $santander["output"]["response"]["0"];
    $code = Util::getDepartamentoPrincipal();
    $mapa = null;

    if (!is_null($code)) {
        $arr = Ciudad::getAll(array('codigo_departamento' => $code));
        $finalMunicipios = $arr['output']['response'];
        $arrApoyoDep = Ciudad::getApoyoByCodigoDepartamento(array('codigo_departamento' => $code));
    }
    ?>
 <link href="assets/css/dashboard_pae_gob360.css" rel="stylesheet">

<body class="dashboard-body gob360-pae-dashboard">
   
     <div class="loader-bg">
         <div class="loader-track">
             <div class="loader-fill"></div>
         </div>
     </div>
     <?php
        include './admin/include/navbar.php';
        ?>
     <?php
        include './admin/include/header.php';
        ?>
     <div class="pcoded-main-container">
         <div class="pcoded-content">
             <!-- [ breadcrumb ] start -->
             <div class="page-header">
                 <div class="page-block">
                     <div class="row align-items-center">
                         <div class="col-md-12">
                             <div class="d-flex justify-content-between align-items-center">
                                 <h5 class="m-b-10">Analítica del Programa de Alimentación Escolar</h5>
<?php include './admin/include/btn_back.php'; ?>
                             </div>
                             <ul class="breadcrumb">
                                 <li class="breadcrumb-item"><a href="index.php"><i class="feather icon-home"></i></a>
                                 </li>
                                 <li class="breadcrumb-item"><a href="#!"> Secretaría de Educación / Dirección PAE </a>
                                 </li>
                             </ul>
                         </div>
                     </div>
                 </div>
             </div>

             <!-- HERO VISUAL GOB360 -->
             <section class="g360-pae-dashboard-hero" aria-label="Dashboard PAE GOB360">
                 <div class="g360-pae-dashboard-hero__grid">

                     <div>
                         <img
                             src="assets/img/gob360l.png"
                             alt="Logo GOB360"
                             class="g360-pae-dashboard-hero__logo"
                         >
                     </div>

                     <div>
                         <div class="g360-pae-dashboard-hero__eyebrow">
                             <i class="feather icon-pie-chart"></i>
                             Analítica del Programa de Alimentación Escolar
                         </div>

                         <h1 class="g360-pae-dashboard-hero__title">
                             Dashboard PAE
                         </h1>

                         <p class="g360-pae-dashboard-hero__description">
                             Analiza la caracterización de las sedes educativas,
                             infraestructura, almacenamiento, comedores, cocinas,
                             dotación, servicios públicos y modalidades del PAE
                             mediante indicadores, gráficas y mapa territorial.
                         </p>

                         <div class="g360-pae-dashboard-hero__chips">
                             <span class="g360-chip g360-chip--success">
                                 <i class="feather icon-check-circle"></i>
                                 Información consolidada
                             </span>

                             <span class="g360-chip">
                                 <i class="feather icon-filter"></i>
                                 Filtro municipal
                             </span>

                             <span class="g360-chip">
                                 <i class="feather icon-map"></i>
                                 Análisis territorial
                             </span>
                         </div>
                     </div>

                     <div class="g360-pae-dashboard-hero__visual" aria-hidden="true">
                         <div class="g360-mini-card">
                             <i class="feather icon-home"></i>
                             <span>Sedes</span>
                         </div>

                         <div class="g360-mini-card">
                             <i class="feather icon-bar-chart-2"></i>
                             <span>Indicadores</span>
                         </div>

                         <div class="g360-mini-card">
                             <i class="feather icon-grid"></i>
                             <span>Dotación</span>
                         </div>

                         <div class="g360-mini-card">
                             <i class="feather icon-map-pin"></i>
                             <span>Mapa</span>
                         </div>
                     </div>

                 </div>
             </section>

    <div class="row mb-4 g360-dashboard-grid">
    <div class="col-md-8 g360-main-column">
    <div class="card g360-analytics-shell">

        <!-- Submenú de Secciones -->
            <div class="card shadow-sm border mb-4 g360-section-menu-card">
                <div class="card-header">
                    <div>
                        <h5><i class="feather icon-layers mr-2"></i>Secciones del informe PAE</h5>
                        <p>Selecciona el componente que deseas analizar en las gráficas.</p>
                    </div>
                </div>
                <div class="card-body p-0">
                    <ul class="nav nav-tabs nav-justified flex-column flex-sm-row submenu-personalizado" id="myTab" role="tablist">
                        <li class="nav-item" role="presentation">
                            <a class="nav-link active" id="btn-item1" href="javascript:void(0);" onclick="mostrarSeccion('item1_estado_sedes')">
                                1. Estado general de las sedes educativas
                            </a>
                        </li>
                        <li class="nav-item" role="presentation">
                            <a class="nav-link" id="btn-item2" href="javascript:void(0);" onclick="mostrarSeccion('item2_estado_almacenamiento')">
                                2. Almacenamiento, preparación y consumo
                            </a>
                        </li>
                        <li class="nav-item" role="presentation">
                            <a class="nav-link" id="btn-item3" href="javascript:void(0);" onclick="mostrarSeccion('item3_comedores')">
                                3. Instituciones con comedor escolar
                            </a>
                        </li>
                        <li class="nav-item" role="presentation">
                            <a class="nav-link" id="btn-item4" href="javascript:void(0);" onclick="mostrarSeccion('item4_cocinas')">
                                4. Cocinas para preparación de alimentos
                            </a>
                        </li>
                        <li class="nav-item" role="presentation">
                            <a class="nav-link" id="btn-item5" href="javascript:void(0);" onclick="mostrarSeccion('item5_concepto_sanitario')">
                                5. Concepto higiénico-sanitario
                            </a>
                        </li>
                        <li class="nav-item" role="presentation">
                            <a class="nav-link" id="btn-item6" href="javascript:void(0);" onclick="mostrarSeccion('item6_dotacion')">
                                6. Dotación y equipos
                            </a>
                        </li>
                        <li class="nav-item" role="presentation">
                            <a class="nav-link" id="btn-item7" href="javascript:void(0);" onclick="mostrarSeccion('item7_servicios_publicos')">
                                7. Servicios públicos
                            </a>
                        </li>
                        <li class="nav-item" role="presentation">
                            <a class="nav-link" id="btn-item8" href="javascript:void(0);" onclick="mostrarSeccion('item8_modalidades')">
                                8. Modalidades del PAE
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
            

            <!-- INFORMACION DE LOS GRAFICOS PAE -->
                <?php include 'admin/include/graficospae.php'; ?>
            <!-- FIN INFORMACION DE LOS GRAFICOS PAE -->

    </div>

<div class="col-md-4">
  <div class="card shadow-sm g360-filter-card">         
    <div class="card-header">
        <div>
            <h4><i class="feather icon-filter mr-2"></i>Filtro territorial</h4>
            <p>Selecciona un municipio para actualizar el dashboard.</p>
        </div>
    </div>  
    <div class="card-body text-center">



      <div id="containerDataPae" name="containerDataPae">
        <div class="row">
          <div class="card-body">


            <input type="hidden" name="op" id="op" />
            <input type="hidden" name="id" id="id" />
            <input type="hidden" name="filtro" id="filtro" value="vereda" />
            <input type="hidden" name="filtroVeredaById" id="filtroVeredaById" value="si" />

          <div class="row justify-content-center">
            <!-- Departamento oculto -->
            <div class="col-md-6 mb-3" style="display:none">
                <div class="form-group">
                <label for="tbl_departamento_id">Departamento <span class="text-danger">*</span></label>
                <select onchange="DEPARTAMENTO.getMunicipios();" class="form-control" id="tbl_departamento_id" name="tbl_departamento_id">
                    <?php echo $optionDep; ?>
                </select>
                </div>
            </div>

            <!-- Municipio centrado -->
            <div class="col-12 col-md-8 mb-3">
                <div class="form-group text-center">
                <label for="tbl_municipio_id">Municipio <span class="text-danger">*</span></label>
                <select class="form-control text-center" id="tbl_municipio_id" onchange="PAE_DASHBOARD.updateUrlMunicipio(this);" name="tbl_municipio_id">
                </select>
                </div>
            </div>
            </div>

    <!-- Datos estadísticos con gráficos quemados -->
           <?php include 'admin/include/dataquemadadashpae.php'; ?> 
            <!-- Indicadores en una fila horizontal -->
            <div class="row text-center justify-content-center align-items-stretch mt-4 g360-kpi-grid">

              <div class="col-md-6 mb-3">
                <div class="g360-kpi-item">
                <img src="assets/img/sedes.png" alt="Sedes" width="50">
                <h6 class="mt-2">Sedes Caracterizadas</h6>
                <h5 class="text-primary"><?php echo number_format($caracterizaciones, 0); ?></h5>
                </div>
              </div>

              <div class="col-md-6 mb-3">
                <div class="g360-kpi-item">
                <img src="assets/img/rural.png" alt="Rural" width="50">
                <h6 class="mt-2">Zona Rural</h6>
                <h5 class="text-success"><?php echo number_format($zona_rural, 0); ?></h5>
                </div>
              </div>

              <div class="col-md-6 mb-3">
                <div class="g360-kpi-item">
                <img src="assets/img/urban.png" alt="Urbana" width="50">
                <h6 class="mt-2">Zona Urbana</h6>
                <h5 class="text-warning"><?php echo number_format($zona_urbana, 0); ?></h5>
                </div>
              </div>

              <div class="col-md-6 mb-3">
                <div class="g360-kpi-item">
                <a href="plan_desarrollo.php">
                  <img src="assets/img/ninosfocalizados.png" alt="Niños" width="50">
                </a>
                <h6 class="mt-2">Niños Focalizados</h6>
                <h5 class="text-danger"><?php echo number_format($ninos_foc, 0); ?></h5>
                </div>
              </div>

            </div>
            <!-- Fin indicadores -->
          </div>
        </div>
      </div>

    </div>
  </div>
</div>
 
<!-- Nueva fila con un card con los gráficos abajo -->
<div class="row">
  <div class="col-md-11 mx-auto">
    <div class="card mt-3 shadow-sm g360-map-card">
      <div class="card-header">
        <div>
            <h5><i class="feather icon-map mr-2"></i>Información territorial por mapa</h5>
            <p>Consulta la distribución geográfica de los indicadores del PAE.</p>
        </div>
      </div>
      <div class="g360-map-surface">
                  <!-- ========== INICIO MAPA ========== -->
                <?php include 'admin/include/mapadashboardpae.php'; ?>
                <!-- ========== FIN MAPA ========== -->
      </div>
        <!-- <p>CARD CON SUBMENU PAE.</p> -->
      </div>
    </div>
  </div>
</div>



<!-- INICIO DE LOS MODALES -->
     <div class="card-body">
         <div id="modalGeocalizacion" class="modal fade" tabindex="-1" role="dialog"
             aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
             <div class="modal-dialog modal-dialog-centered" role="document">
                 <div class="modal-content">
                     <div class="modal-header">
                         <h5 class="modal-title" id="exampleModalCenterTitle"><i class="feather icon-map-pin mr-2"></i>Geolocalización PAE</h5>
                         <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar"><span
                                 aria-hidden="true">&times;</span></button>
                     </div>
                     <div class="modal-body">
                         <div id="map" style="height: 600px; width: 100%;"></div>

                     </div>
                     <div class="modal-footer">
                         <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>

                     </div>
                 </div>
             </div>
         </div>

     <div class="modal fade bd-example-modal-sm" tabindex="-1" role="dialog" aria-labelledby="mySmallModalLabel"
         aria-hidden="true">
         <div class="modal-dialog modal-sm">
             <div class="modal-content">
                 <div class="modal-header">
                     <h5 class="modal-title h4" id="mySmallModalLabel">
                         Elementos utilizados para el almacenamiento de alimentos
                     </h5>
                     <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar"><span
                             aria-hidden="true">&times;</span></button>
                 </div>
                 <div class="modal-body">
                     <div id="pie-chart-1" style="width:100%"></div>
                 </div>
             </div>
         </div>
     </div>
     </div>
         <!-- Google Maps JavaScript API -->
         <script async defer
             src="https://maps.googleapis.com/maps/api/js?key=AIzaSyAbqZVEnSpIPF5dKe89pju2rmQaM58wvY0&callback=initMap">
         </script>
     </div>

     <?php include 'admin/include/gerenic_script.php'; ?>
     <script src="assets/js/vendor-all.min.js"></script>
     <script src="assets/js/plugins/bootstrap.min.js"></script>
     <script src="assets/js/pcoded.min.js"></script>

     <!-- prism Js -->
     <script src="assets/js/plugins/prism.js"></script>
     <script src="assets/js/plugins/apexcharts.min.js"></script>

     <script src="admin/js/pae_mapa_geo.js"></script>
     <script src="admin/js/pae_dash.js"></script>
     <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css">
     <script src="assets/js/plugins/apexcharts.min.js"></script>
     <script src="assets/js/pages/chart-apex.js"></script>

     <script type="text/javascript" src="admin/js/departamento.js"></script>
     <script>
         setTimeout(function() {
             DEPARTAMENTO.getMunicipiosOpcionSelectTodos();
         }, 500);
     </script>


 </body>

 </html>