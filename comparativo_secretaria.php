<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include './admin/include/head.php';

    require './admin/include/generic_classes.php';
    include './admin/classes/Pilar.php';
    include './admin/classes/Puntaje.php';

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
        if ($exists !== false) {
            $final =  substr($final, 0, $exists);
        }
        return $final;
    }

    require_once './admin/include/generic_classes.php';
    include './admin/classes/Colombia.php';
    include './admin/classes/Ciudad.php';
    require './admin/classes/Departamento.php';
    include './admin/db/coloress.php';
    include './admin/classes/Secretarias.php';
    include './admin/classes/AccionSecretaria.php';
    include './admin/classes/SecretariasMunicipio.php';

    // Obtener secretaría y acción
    $secretaria_unica_raw = $_REQUEST['secretaria_unica'] ?? Util::getSecretariaPrincipal();
    $secretaria_unica = intval($secretaria_unica_raw);


    // Acciones por secretaría
    $responseAccionSecretarias = [];
    $isAccionSecretaria = false;

    if ($secretaria_unica > 0) {
        $accionSecretaria = AccionSecretaria::getAll(['id' => $secretaria_unica]);
        $isAccionSecretaria = $accionSecretaria['output']['valid'] ?? false;
        $responseAccionSecretarias = $accionSecretaria['output']['response'] ?? null;
    } else {
        echo "<script>alert('Información enviada no es correcta'); window.location = 'dashboard.php';</script>";
        exit;
    }

    
    $accion_base = $_REQUEST['accion'] ?? ''; 
    $accion_actual_final = $accion_base;
    $datosTablaConsolidadoRaw = []; 
    $accion_por_defecto_ciego = '';


    $pilar_id_actual = 0; 
    $factor_a_buscar = $accion_base; 

    // factor ciego como fallback
    if ($isAccionSecretaria && !empty($responseAccionSecretarias)) {
        $first_item = $responseAccionSecretarias[0];
        if (isset($first_item['tipo'])) {
            $accion_por_defecto_ciego = $first_item['tipo']; 
        } elseif (isset($first_item['factor'])) {
            $accion_por_defecto_ciego = $first_item['factor']; 
        }elseif (isset($first_item['nombre'])) {
            $accion_por_defecto_ciego = $first_item['nombre'];
        }elseif (isset($first_item['accion'])) {
            $accion_por_defecto_ciego = $first_item['accion'];
        }
    } 

    $arrConsolidadoCargaInicial = [
        'municipioId' => Util::getDepartamentoPrincipal(),
        'secretariaId' => $secretaria_unica,
    ];


    $responseConsolidado = Secretarias::getFactoresPrincipalesConsolidado($arrConsolidadoCargaInicial); 
    $consolidadoCargaInicialExitoso = false;

    if (isset($responseConsolidado['output']['response']) && is_array($responseConsolidado['output']['response'])) {
        $datosTablaConsolidadoRaw = $responseConsolidado['output']['response'];
        $consolidadoCargaInicialExitoso = true;
    }


    if (empty($accion_base)) {
        
        if ($consolidadoCargaInicialExitoso && !empty($datosTablaConsolidadoRaw)) {
            $primerFactor = $datosTablaConsolidadoRaw[0]; 

            $accion_actual_final = $primerFactor['factor'] ?? $accion_por_defecto_ciego;
            $pilar_id_actual = intval($primerFactor['tec_pilar_id'] ?? 0);
            
        } else {

            $accion_actual_final = $accion_por_defecto_ciego ?: ''; 
            if (!empty($responseAccionSecretarias)) {
                $pilar_id_actual = intval($responseAccionSecretarias[0]['tec_pilar_id'] ?? 0);
            }
        }
    }

    if ($consolidadoCargaInicialExitoso && !empty($datosTablaConsolidadoRaw) && !empty($factor_a_buscar)) {

        $factor_encontrado = array_filter($datosTablaConsolidadoRaw, function($item) use ($factor_a_buscar) {
            return ($item['factor'] == $factor_a_buscar);
        });
        
        if (!empty($factor_encontrado)) {
            $pilar_id_actual = intval(reset($factor_encontrado)['tbl_pilar_id'] ?? $pilar_id_actual);
        }
    }



    $accion = $accion_actual_final; 
    $accion_nuevo = $accion; 
    $accionActual = $accion; 
    $pilar_final = $pilar_id_actual;
    $secretaria = $secretaria_unica;


    if ($secretaria_unica == Util::getSecretariaIdHacienda()) {
        
        $accionesHacienda = [
            'Capacitacion Fiscal y Financiera',
            'Operativos Contrabando licores',
            'Operativos Contrabando cigarrillos',
            'Operativos Contrabando cerveza',
            'Impuesto Vehicular Recaudado',
            'Recaudo del impuesto al consumo',
            'Recaudo del impuesto de registro',
            'Impuesto Estampillas Recaudado',
            'GOA Aprehensiones de Licores',
            'GOA Aprehensión de Cigarrillos',
            'GOA Aprehensión de Cervezas',
            'GOA Aprehensión de Tabaco y Otros',
            'Registro de Visitas a Establecimientos Comerciales'
        ];
        $accionHaciendaDefault = 'Capacitacion Fiscal y Financiera'; 
        
        if (!in_array($accion, $accionesHacienda) || empty($accion)) {
            $accion = $accionHaciendaDefault;
            $accion_nuevo = $accionHaciendaDefault; 
            $accionActual = $accionHaciendaDefault; 
        }
    }

    $userType = SessionData::getUserType();
    $isSecretario = ($userType === Util::Secretario_Despacho() || $userType === Util::Auxiliar() || $$userType == Util::Auxiliar_secret_gob());
    $isAlcalde = ($userType === Util::Alcalde() || $userType === Util::Auxiliar_Alcalde());

    $isDisabled = '';
    if ($isSecretario || $isAlcalde) {
        $isDisabled = 'disabled';
    }


    $arr = Secretarias::getAll(null);
    $isvalid = $arr['output']['valid'];
    $arr = $arr['output']['response'];
    $optionSecretarias = "";
    foreach ($arr as $val) {
        $selected = ($val['id'] == $secretaria_unica) ? "selected" : "";
        $optionSecretarias .= "<option value='" . $val['id'] . "' $selected>" . $val['secretaria'] . "</option>";
    }

    $secretariaParaConsulta = $secretaria_unica;
    $secretariaParaConsultaMapa = $secretariaParaConsulta;

    $clicHaciendaDeshabilitado = ($secretaria_unica == Util::getSecretariaIdHacienda());
    $claseDeshabilitada = $clicHaciendaDeshabilitado ? 'municipio-no-click' : '';

    //mapa actual
    $arrMapa = [
        'codigoMunicipio' => Util::getDepartamentoPrincipal(),
        'secretariaId' => $secretariaParaConsultaMapa, 
        'accion' => $accion
    ];


    if ($secretariaParaConsulta == Util::getSecretariaIdHacienda()) {
        
        $data = Colombia::getInformacionSecretariaColoresMapa($arrMapa); 
        $santander = (isset($data['output']['response']) && is_array($data['output']['response'])) 
            ? $data['output']['response'] 
            : []; 
        
        $puntajes = []; 
    } else {
        $data = Colombia::getInformacionSecretariaColoresMapa($arrMapa); 

        $santander = (isset($data['output']['response']) && is_array($data['output']['response'])) 
            ? $data['output']['response'] 
            : [];
        $puntajes = $data['output']['puntajes']?? [];
    }


    //mapa incial
    $arrMapaNuevo = [
        'codigoMunicipio' => Util::getDepartamentoPrincipal(),
        'secretariaId' => $secretaria_unica, 
        'accion' => $accion 
    ];

    if ($secretaria_unica == Util::getSecretariaIdHacienda()) {
        
        $accionInicialHacienda = 'Operativos Contrabando licores';
        $arrMapaTemporal = [
            'codigoMunicipio' => Util::getDepartamentoPrincipal(),
            'secretariaId' => Util::getSecretariaIdHacienda(), 
            'accion' => $accionInicialHacienda
        ];
        
        $data_nuevo = Colombia::getInformacionSecretariaColoresMapaInicial($arrMapaTemporal); 

        $santander_nuevo = (isset($data_nuevo['output']['response']) && is_array($data_nuevo['output']['response']))
            ? $data_nuevo['output']['response']
            : [];
        
        $puntajes_nuevo = []; 

    } else {
        $data_nuevo = Colombia::getInformacionSecretariaColoresMapaInicial($arrMapaNuevo);

        $santander_nuevo = (isset($data_nuevo['output']['response']) && is_array($data_nuevo['output']['response']))
            ? $data_nuevo['output']['response']
            : [];
                         
        $puntajes_nuevo = $data_nuevo['output']['puntajes'] ?? [];
    }


    // Información del select de acciones
    $selectLicores = "Operativos Contrabando licores";
    $selectCigarrillos = "Operativos Contrabando cigarrillos";
    $selectFiscalYFinanciera = "Capacitacion Fiscal y Financiera";
    $selectCervezas = "Operativos Contrabando cerveza";

    // Información de los proyectos en ejecución
    $arrEjecucion = [
        'codigoMunicipio' => Util::getDepartamentoPrincipal(),
        'secretariaId' => $secretariaParaConsulta,
        'accion' => $accion
    ];

    if ($secretariaParaConsulta == Util::getSecretariaIdHacienda()) {
        $responseTotalEjecucionSecretarias = ['output' => ['valid' => true, 'response' => []]];
        $dataTotalEjecucionSecretarias = [];
    } else {
        $responseTotalEjecucionSecretarias = Secretarias::getTotalEjecucionSecretaria($arrEjecucion);
        $dataTotalEjecucionSecretarias = $responseTotalEjecucionSecretarias['output']['response'];
    }

    // Variables adicionales para Hacienda
    $infoCigarrillos = [];
    $infoTabacos = [];
    $infoLicores = [];
    $infoCerveza = [];

    ?>

    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.7.1.js"></script>
    <!-- Popper.js -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.3/umd/popper.min.js"></script>
    <!-- DataTables -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">

    <link href="assets/css/informe_comparativo_secretarias_gob360.css" rel="stylesheet">

<body class="gob360-secretary-comparison">

<!-- [ Pre-loader ] start -->
<div class="loader-bg">
  <div class="loader-track">
    <div class="loader-fill"></div>
  </div>
</div>

<?php include './admin/include/navbar.php'; ?>
<?php include './admin/include/header.php'; ?>




<div class="pcoded-main-container">
  <div class="pcoded-wrapper">
    <div class="pcoded-content">
      <div class="pcoded-inner-content">
        <div class="main-body">
          <div class="page-wrapper">

            <div class="page-header">
              <div class="page-block">
                <div class="row align-items-center">
                  <div class="col-md-12">
                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                      <h5 class="m-b-0">Informes Secretarias</h5>
                      <?php include './admin/include/btn_back.php'; ?>
                    </div>

                    <ul class="breadcrumb">
                      <li class="breadcrumb-item">
                        <a href="index.html"><i class="feather icon-home"></i></a>
                      </li>
                      <li class="breadcrumb-item"><a href="#!">Informe Secretaria</a></li>
                      <li class="breadcrumb-item"><a href="#!">Actividades</a></li>
                    </ul>

                  </div>
                </div>
              </div>
            </div>

            <!-- HERO VISUAL GOB360 -->
            <section class="g360-comparison-hero" aria-label="Informe comparativo de secretarías GOB360">
              <div class="g360-comparison-hero__grid">

                <div>
                  <img
                    src="assets/img/gob360l.png"
                    alt="Logo GOB360"
                    class="g360-comparison-hero__logo"
                  >
                </div>

                <div>
                  <div class="g360-comparison-hero__eyebrow">
                    <i class="feather icon-layers"></i>
                    Análisis territorial institucional
                  </div>

                  <h1 class="g360-comparison-hero__title">
                    Informe comparativo de secretarías
                  </h1>

                  <p class="g360-comparison-hero__description">
                    Compara el estado territorial inicial y actual de cada
                    secretaría mediante mapas interactivos, niveles de
                    clasificación, geolocalización y consulta consolidada por municipio.
                  </p>

                  <div class="g360-comparison-hero__chips">
                    <span class="g360-chip g360-chip--success">
                      <i class="feather icon-check-circle"></i>
                      Información consolidada
                    </span>

                    <span class="g360-chip">
                      <i class="feather icon-map"></i>
                      Comparativo territorial
                    </span>

                    <span class="g360-chip">
                      <i class="feather icon-map-pin"></i>
                      Consulta municipal
                    </span>
                  </div>
                </div>

                <div class="g360-comparison-hero__visual" aria-hidden="true">
                  <div class="g360-mini-card">
                    <i class="feather icon-briefcase"></i>
                    <span>Secretarías</span>
                  </div>

                  <div class="g360-mini-card">
                    <i class="feather icon-map"></i>
                    <span>Mapa inicial</span>
                  </div>

                  <div class="g360-mini-card">
                    <i class="feather icon-trending-up"></i>
                    <span>Mapa actual</span>
                  </div>

                  <div class="g360-mini-card">
                    <i class="feather icon-navigation"></i>
                    <span>Geolocalizar</span>
                  </div>
                </div>

              </div>
            </section>

            <!-- Filtro principal -->
            <div class="row">
              <div class="col-12">
                <div class="card mb-4 card-info-complementaria g360-panel-card g360-filter-card">
                  <div class="card-header text-white">
                    <div>
                      <h5>
                        <i class="bi bi-sliders mr-2"></i>
                        Configuración del comparativo
                      </h5>
                      <p>Selecciona la secretaría utilizada por los mapas territoriales.</p>
                    </div>
                  </div>

                  <div class="card-body">
                    <div class="row">
                      <div class="col-md-12">
                        <div class="form-group mb-0">
                          <label for="secretariaUnicaId">
                            <i class="bi bi-building me-1" style="font-size:1.05rem;"></i>
                            Secretaría del mapa principal <span class="text-danger">*</span>
                          </label>

                          <select <?php echo $isDisabled; ?>
                            class="form-control"
                            id="secretariaUnicaId"
                            name="secretaria_unica"
                            onchange="updateUrlUnica(this)">
                            <?php echo $optionSecretarias; ?>
                          </select>

                        </div>
                      </div>
                    </div>
                  </div>

                </div>
              </div>
            </div>

            <!-- Mapas -->
            <div class="row maps-grid">

              <!-- MAPA INICIAL -->
              <div class="col-lg-6 mb-4">
                <div class="card h-100 w-100 map-card card-mapa-nuevo g360-panel-card g360-map-card">
                  <div class="card-header">
                    <div class="title-wrap">
                      <span class="map-badge">
                        <i class="bi bi-map-fill"></i> Línea base territorial
                      </span>
                    </div>

                    <button
                      id="botonGeocalizacionNuevo"
                      name="botonGeocalizacionNuevo"
                      type="button"
                      class="btn btn-success"
                      data-toggle="modal"
                      data-target="#modalGeocalizacion">
                      <i class="bi bi-geo-alt-fill me-1"></i> Geolocalización
                    </button>
                  </div>

                  <div class="card-body map-body">
                    <div class="g360-map-legend">
                        <span class="badge rounded-pill px-3 py-2" style="background:#EEF2F7;color:#0f172a;border:1px solid rgba(15,23,42,.10);font-weight:800;">Neutro</span>
                        <span class="badge rounded-pill px-3 py-2" style="background:#E53935;color:#fff;font-weight:800;">Crítico</span>
                        <span class="badge rounded-pill px-3 py-2" style="background:#FB8C00;color:#fff;font-weight:800;">Alto</span>
                        <span class="badge rounded-pill px-3 py-2" style="background:#F6C026;color:#111827;font-weight:900;">Medio</span>
                        <span class="badge rounded-pill px-3 py-2" style="background:#2E7D32;color:#fff;font-weight:800;">Estable</span>
                        <span class="badge rounded-pill px-3 py-2" style="background:#1E66F5;color:#fff;font-weight:800;">Info</span>
                    </div>

                    <div class="map-frame">
                      <div id="contenido-mapa-nuevo" class="cuerpoMapa w-100">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="30 50 1000 1200" width="100%" height="auto">
                          <?php foreach ($santander_nuevo as $key => $value): ?>
                            <?php if (is_array($value)): ?>
                              <g id="NUEVO_<?= strtoupper($value['path']) ?>">
                                <path
                                  id="NUEVO_<?= strtoupper($value['path']) ?>"
                                  d="<?= $value['d'] ?>"
                                  fill="<?= $value['color'] ?>"
                                  class="municipios-nuevo mapaClickNuevo <?= $claseDeshabilitada ?>"
                                  data-municipio-id="<?= $value['codigo_muncipio'] ?>"
                                  data-secretaria-id="<?= $secretaria_unica ?>"
                                  data-accion="<?= htmlspecialchars($accion) ?>"
                                  data-base-url="<?= getUrl() . 'municipios_informacion_nuevo.php?mun=' . $value['codigo_muncipio'] . '&dep=' . $value['codigo_departamento'] . '&secretaria_nuevo=' . $secretaria_unica . '&accion_nuevo=' . $accion_nuevo ?>"
                                  data-url="<?= getUrl() . 'municipios_informacion_nuevo.php?mun=' . $value['codigo_muncipio'] . '&dep=' . $value['codigo_departamento'] . '&secretaria_nuevo=' . $secretaria_unica . '&accion_nuevo=' . $accion_nuevo . '&pilar_id=' . $pilar_final ?>"
                                  data-name="<?= strtolower($value['municipio']) ?>"
                                  title="<?= strtoupper(str_replace("-", " ", $value['nombre_mapa'])) ?>"
                                  stroke="#000" stroke-miterlimit="10" stroke-width="0.3px">
                                </path>
                              </g>
                            <?php endif; ?>
                          <?php endforeach; ?>
                          <?php require_once 'nombres_mapa_santander.php' ?>
                        </svg>
                      </div>
                    </div>
                  </div>

                </div>
              </div>

              <!-- MAPA ACTUAL -->
              <div class="col-lg-6 mb-4">
                <div class="card h-100 w-100 map-card card-mapa g360-panel-card g360-map-card">
                  <div class="card-header">
                    <div class="title-wrap">
                      <span class="map-badge">
                        <i class="bi bi-map-fill"></i> Estado territorial actual
                      </span>
                    </div>

                    <button
                      id="botonGeocalizacion"
                      name="botonGeocalizacion"
                      type="button"
                      class="btn btn-primary"
                      data-toggle="modal"
                      data-target="#modalGeocalizacion">
                      <i class="bi bi-geo-alt-fill me-1"></i> Geolocalización
                    </button>
                  </div>

                  <div class="card-body map-body">
                          <div class="g360-map-legend">
                        <span class="badge rounded-pill px-3 py-2" style="background:#EEF2F7;color:#0f172a;border:1px solid rgba(15,23,42,.10);font-weight:800;">Neutro</span>
                        <span class="badge rounded-pill px-3 py-2" style="background:#E53935;color:#fff;font-weight:800;">Crítico</span>
                        <span class="badge rounded-pill px-3 py-2" style="background:#FB8C00;color:#fff;font-weight:800;">Alto</span>
                        <span class="badge rounded-pill px-3 py-2" style="background:#F6C026;color:#111827;font-weight:900;">Medio</span>
                        <span class="badge rounded-pill px-3 py-2" style="background:#2E7D32;color:#fff;font-weight:800;">Estable</span>
                        <span class="badge rounded-pill px-3 py-2" style="background:#1E66F5;color:#fff;font-weight:800;">Info</span>
                    </div>
                    <div class="map-frame">
                      <div id="contenido-mapa" class="cuerpoMapa w-100">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="30 50 1000 1200" width="100%" height="auto">
                          <?php foreach ($santander as $key => $value): ?>
                            <?php if (is_array($value)): ?>
                              <g id="<?= strtoupper($value['path']) ?>">
                                <path
                                  id="<?= strtoupper($value['path']) ?>"
                                  d="<?= $value['d'] ?>"
                                  fill="<?= $value['color'] ?>"
                                  class="municipios mapaClick <?= getClasePorcentaje(0.2) ?> <?= $claseDeshabilitada ?>"
                                  data-municipio-id="<?= $value['codigo_muncipio'] ?>"
                                  data-departamento-id="<?= $value['codigo_departamento'] ?>"
                                  data-secretaria-id="<?= $secretaria_unica ?>"
                                  data-accion="<?= htmlspecialchars($accion) ?>"
                                  data-base-url="<?= getUrl() . 'municipios_secretaria_informacion.php?mun=' . $value['codigo_muncipio'] . '&dep=' . $value['codigo_departamento'] ?>"
                                  data-url="<?= getUrl() . 'municipios_secretaria_informacion.php?mun=' . $value['codigo_muncipio'] . '&dep=' . $value['codigo_departamento'] . '&pilar_id=' . $pilar_final ?>"
                                  data-name="<?= strtolower($value['municipio']) ?>"
                                  title="<?= strtoupper(str_replace("-", " ", $value['nombre_mapa'])) ?>"
                                  stroke="#000" stroke-miterlimit="10" stroke-width="0.3px">
                                </path>
                              </g>
                            <?php endif; ?>
                          <?php endforeach; ?>
                          <?php require 'nombres_mapa_santander.php' ?>
                        </svg>
                      </div>
                    </div>
                  </div>

                </div>
              </div>

            </div><!-- /row maps -->

          </div><!-- /page-wrapper -->
        </div>
      </div>
    </div>
  </div>
</div>

<!-- ===== MODAL CONSOLIDADO ===== -->
<div class="modal fade" id="modalConsolidado" tabindex="-1" role="dialog" aria-labelledby="modalConsolidadoTitle" aria-hidden="true">
  <div class="modal-dialog modal-xl centered" role="document">
    <div class="modal-content">

      <div class="modal-header">
        <h5 class="modal-title" id="modalConsolidadoTitle">
          Resumen de Ejecución en: <span id="modalMunicipioNombre"></span>
        </h5>
        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Cerrar">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>

      <div class="modal-body">
        <div id="modalConsolidadoBody" class="px-1">
          <p class="text-center text-muted mb-0">Cargando datos...</p>
        </div>
      </div>

      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
      </div>

    </div>
  </div>
</div>

<!-- ===== MODAL GEOLOCALIZACIÓN ===== -->
<div class="modal fade" id="modalGeocalizacion" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" role="document">
    <div class="modal-content">

      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalCenterTitle">Geolocalización</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>

      <div class="modal-body">
        <div id="map" style="height:600px; width:100%; border-radius:14px; border:1px solid rgba(15,23,42,.10); overflow:hidden;"></div>
      </div>

      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
      </div>

    </div>
  </div>
</div>

<?php include 'admin/include/footer.php'; ?>
<?php include 'admin/include/gerenic_script.php'; ?>

<!-- Required Js -->
<script src="assets/js/vendor-all.min.js"></script>
<script src="assets/js/plugins/bootstrap.min.js"></script>
<script src="assets/js/pcoded.min.js"></script>
<script type="text/javascript" src="admin/js/secretarias.js"></script>
<script type="text/javascript" src="admin/js/mapa_secretaria.js"></script>





        <script>
            // Funciones para actualizar URLs
            function updateUrlUnica(select, isAccionHacienda = false) {
                let url = new URL(window.location.href);
                
                if (isAccionHacienda) {
                    let newAccion = select.value;
                    url.searchParams.set('accion', newAccion);
                } else {
                    let newSecretariaId = select.value;
                    url.searchParams.set('secretaria_unica', newSecretariaId);

                    url.searchParams.delete('accion'); 
                }
                
                window.location.href = url.href;
            }


            $("img").each(function(index, el) {
                $(this).attr("data-bs-toggle", "tooltip");
                $(this).attr("data-bs-placement", "left");
                tooltip = new bootstrap.Tooltip($(this)[0], {})
            });


            document.querySelectorAll('.tab-list .tab').forEach(tab => {
                tab.addEventListener('click', () => {
                    document.querySelectorAll('.tab-list .tab').forEach(t => t.classList.remove('active'));
                    document.querySelectorAll('.tab-content').forEach(tc => tc.classList.remove('active'));
                    tab.classList.add('active');
                    document.getElementById(tab.getAttribute('data-tab')).classList.add('active');
                });
            });
            
        </script>

<script>
const SECRETARIA_UNICA = '<?= $secretaria_unica ?>';
const ACCION_ACTUAL = '<?= htmlspecialchars($accion) ?>';
const DEPARTAMENTO_PRINCIPAL = '<?= Util::getDepartamentoPrincipal() ?>';

let lastClickedMunicipio = null;

document.addEventListener("DOMContentLoaded", () => {

    function handleMunicipioClick(e) {
        const el = e.currentTarget;
        const codMun = el.dataset.municipioId;
        const codDep = el.dataset.departamentoId || DEPARTAMENTO_PRINCIPAL;
        const nombre = el.dataset.name?.toUpperCase() || "MUNICIPIO";
        if (lastClickedMunicipio) {
            lastClickedMunicipio.style.fill = lastClickedMunicipio.dataset.originalFill || "#ccc";
            lastClickedMunicipio.classList.remove("municipio-resaltado");
        }
        el.dataset.originalFill = el.getAttribute("fill");
        el.style.fill = "#FFD700";
        el.classList.add("municipio-resaltado");
        lastClickedMunicipio = el;

        document.getElementById("modalMunicipioNombre").textContent = nombre;
        $("#modalConsolidado").modal("show");

        const body = $("#modalConsolidadoBody");
        body.prepend(`
            <p id="loading-mapa-temp" class="text-center text-muted">
                <i class="bi bi-arrow-clockwise fa-spin"></i> Cargando mapa…
            </p>
        `);

        $.ajax({
            url: "./admin/classes/get_mapa_municipio_modal.php",
            method: "GET",
            data: {
                codigo_departamento: codDep,
                codigo_municipio: codMun,
                secretaria_unica: SECRETARIA_UNICA,
                accion: encodeURIComponent(ACCION_ACTUAL)
            },
            success: res => {
                $("#loading-mapa-temp").remove();
                body.prepend(`
                    <div id="contenedor-mapa-modal" class="mb-3">
                        ${res}
                        <hr>
                    </div>
                `);

                initVeredaLogic();
            },
            error: () => {
                $("#loading-mapa-temp").remove();
                body.prepend(`<div class="alert alert-danger">Error al cargar el mapa.</div>`);
            }
        });
    }

    document.querySelectorAll(".mapaClick:not(.municipio-no-click), .mapaClickNuevo:not(.municipio-no-click)")
        .forEach(m => m.addEventListener("click", handleMunicipioClick));
    $("#modalConsolidado").on("hidden.bs.modal", () => {
        if (lastClickedMunicipio) {
            lastClickedMunicipio.style.fill = lastClickedMunicipio.dataset.originalFill || "#ccc";
            lastClickedMunicipio.classList.remove("municipio-resaltado");
            lastClickedMunicipio = null;
        }

        $("#contenedor-mapa-modal").remove();
        $("#loading-mapa-temp").remove();
    });
});
</script>


</body>
</html>