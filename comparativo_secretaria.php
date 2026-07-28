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

    <body class="">
        <style>
            .nombres {
                font-family: "IBM Plex Sans", sans-serif !important;
            }

            .fondo {
                background-color: #FC0707;
                padding: 2px 4px;
                /* Añade un poco de espacio alrededor del texto */
                color: white;
                display: inline-block;
            }
      </style>

</style>

<!-- [ Pre-loader ] start -->
<div class="loader-bg">
  <div class="loader-track">
    <div class="loader-fill"></div>
  </div>
</div>

<?php include './admin/include/navbar.php'; ?>
<?php include './admin/include/header.php'; ?>

<style>
  :root{
    --nav-blue:#20427F;
    --nav-blue-2:#132b52;
    --nav-blue-3:#2e58a8;

    --bg:#f6f8fc;
    --card:#ffffff;
    --ink:#0f172a;
    --muted:#64748b;
    --line:rgba(15,23,42,.10);

    --radius-xl:22px;
    --radius-lg:16px;
    --radius-md:12px;

    --shadow-soft:0 12px 30px rgba(2,6,23,.10);
    --shadow-mid:0 18px 40px rgba(2,6,23,.14);

    --ring: 0 0 0 4px rgba(46,88,168,.16);
  }

  /* ====== CONTENEDOR GENERAL ====== */
  body{ background: var(--bg) !important; }
  .pcoded-main-container{ background: transparent !important; }
  .pcoded-content{ padding-top: 18px !important; }
  .page-wrapper{ padding-top: 6px !important; }
  .page-header{ margin-bottom: 16px !important; }

  /* ====== HEADER DE PÁGINA (SaaS) ====== */
  .page-block{
    background: linear-gradient(135deg, rgba(32,66,127,.10), rgba(255,255,255,.70));
    border: 1px solid rgba(255,255,255,.70);
    border-radius: var(--radius-xl);
    box-shadow: var(--shadow-soft);
    padding: 16px 18px;
    backdrop-filter: blur(10px);
  }
  .page-block h5{
    font-weight: 800 !important;
    letter-spacing: .2px;
    color: var(--ink);
  }
  .breadcrumb{
    margin-top: 6px !important;
    margin-bottom: 0 !important;
    background: transparent !important;
    padding: 0 !important;
  }
  .breadcrumb .breadcrumb-item a{ color: var(--muted) !important; }
  .breadcrumb .breadcrumb-item{ font-size: 13px; }

  /* ====== CARDS PRO (SIN MOVIMIENTOS) ====== */
  .card{
    border: 1px solid var(--line) !important;
    border-radius: var(--radius-xl) !important;
    box-shadow: var(--shadow-soft);
    overflow: hidden;
    background: var(--card);
    transition: box-shadow .18s ease; /* NO transform */
  }
  .card:hover{
    box-shadow: var(--shadow-mid);
    transform: none !important; /* QUITA el "salto" */
  }

  .card-header{
    border-bottom: 1px solid rgba(255,255,255,.10) !important;
    padding: 14px 16px !important;
    background: linear-gradient(135deg, var(--nav-blue), var(--nav-blue-2)) !important;
    color: #fff !important;
  }
  .card-header h5{
    color: #fff !important;
    margin: 0 !important;
    font-weight: 800 !important;
    letter-spacing: .2px;
  }

  .card-info-complementaria .card-body{
    padding: 16px !important;
    background: linear-gradient(180deg, rgba(32,66,127,.04), rgba(255,255,255,1));
  }

  /* ====== FORM PRO ====== */
  .form-group label{
    font-weight: 700;
    color: var(--ink);
    margin-bottom: 8px;
  }
  .form-control{
    border-radius: 14px !important;
    border: 1px solid rgba(15,23,42,.14) !important;
    padding: 12px 12px !important;
    height: auto !important;
    box-shadow: none !important;
    transition: border-color .18s ease, box-shadow .18s ease;
    background: #fff !important;
  }
  .form-control:focus{
    border-color: rgba(46,88,168,.55) !important;
    box-shadow: var(--ring) !important;
  }

  /* ====== BOTONES SAAS (SIN MOVIMIENTOS) ====== */
  .btn{
    border-radius: 14px !important;
    font-weight: 800 !important;
    letter-spacing: .2px;
    padding: 10px 14px !important;
    border: none !important;
    box-shadow: 0 10px 20px rgba(2,6,23,.12);
    transition: box-shadow .18s ease, filter .18s ease; /* NO transform */
  }
  .btn:hover{
    transform: none !important; /* QUITA el "salto" */
    box-shadow: 0 14px 26px rgba(2,6,23,.16);
    filter: brightness(0.98);
  }
  .btn:active{ transform: none !important; }

  .btn-primary{
    background: linear-gradient(135deg, var(--nav-blue-3), var(--nav-blue)) !important;
  }
  .btn-success{
    background: linear-gradient(135deg, #20c997, #198754) !important;
  }
  .btn-secondary{
    background: linear-gradient(135deg, #64748b, #475569) !important;
  }

  /* ====== MAPAS ====== */
  .maps-grid{ margin-top: 8px; }

  .map-card .card-header{
    display: flex !important;
    align-items: center !important;
    justify-content: space-between !important;
    gap: 12px;
    flex-wrap: wrap;
  }
  .map-card .card-header .title-wrap{
    display:flex; align-items:center; gap:10px;
  }
  .map-badge{
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 6px 10px;
    border-radius: 999px;
    background: rgba(255,255,255,.14);
    border: 1px solid rgba(255,255,255,.18);
    font-size: 12px;
    font-weight: 800;
  }

  .map-body{
    padding: 14px !important;
    background:
      radial-gradient(circle at 20% 10%, rgba(46,88,168,.08), transparent 40%),
      radial-gradient(circle at 80% 20%, rgba(32,66,127,.08), transparent 45%),
      linear-gradient(180deg, rgba(255,255,255,1), rgba(246,248,252,1));
  }

  .map-frame{
    width: 100%;
    border-radius: var(--radius-lg);
    border: 1px solid rgba(15,23,42,.10);
    background: rgba(255,255,255,.78);
    box-shadow: 0 10px 24px rgba(2,6,23,.10);
    padding: 12px;
    overflow: auto;
  }

  #contenido-mapa,
  #contenido-mapa-nuevo{
    width: 100% !important;
    max-width: 900px !important;
    margin: 0 auto !important;
  }
  #contenido-mapa svg,
  #contenido-mapa-nuevo svg{
    max-width: 100% !important;
    height: auto !important;
    display: block;
  }

  /* =========================================================
     ✅ MAPA: SOLO ESTE HOVER (sin transform, sin otras animaciones)
     ========================================================= */
  .municipios,
  .municipios-nuevo{
    transition: filter .12s ease, stroke .12s ease, stroke-width .12s ease !important;
  }

  /* Elimina cualquier animación/movimiento previo */
  .municipios:hover,
  .municipios-nuevo:hover{
    transform: none !important;
  }

  /* CRÍTICO */
  .municipios.critico:hover,
  .municipios-nuevo.critico:hover{
    filter: brightness(0.85);
    stroke: #B71C1C;
    stroke-width: .9px;
    cursor: pointer;
  }

  /* ESTABLE */
  .municipios.estable:hover,
  .municipios-nuevo.estable:hover{
    filter: brightness(0.90);
    stroke: #1B5E20;
    stroke-width: .8px;
    cursor: pointer;
  }

  /* ALTO */
  .municipios.alto:hover,
  .municipios-nuevo.alto:hover{
    filter: brightness(0.88);
    stroke: #EF6C00;
    stroke-width: .8px;
    cursor: pointer;
  }

  /* MEDIO */
  .municipios.medio:hover,
  .municipios-nuevo.medio:hover{
    filter: brightness(0.92);
    stroke: #C9A600;
    stroke-width: .7px;
    cursor: pointer;
  }

  /* NEUTRO */
  .municipios.neutro:hover,
  .municipios-nuevo.neutro:hover{
    filter: brightness(0.94);
    stroke: rgba(15,23,42,.45);
    stroke-width: .6px;
    cursor: pointer;
  }

  /* Si NO tiene clase (fallback), aplica neutro */
  .municipios:hover:not(.critico):not(.estable):not(.alto):not(.medio):not(.neutro),
  .municipios-nuevo:hover:not(.critico):not(.estable):not(.alto):not(.medio):not(.neutro){
    filter: brightness(0.94);
    stroke: rgba(15,23,42,.45);
    stroke-width: .6px;
    cursor: pointer;
  }

  /* ====== MODALES PRO ====== */
  .modal-content{
    border-radius: 18px !important;
    overflow: hidden;
    border: 1px solid rgba(15,23,42,.10) !important;
    box-shadow: 0 22px 60px rgba(2,6,23,.24);
  }
  .modal-header{
    background: linear-gradient(135deg, var(--nav-blue), var(--nav-blue-2)) !important;
    color: #fff !important;
    border-bottom: 1px solid rgba(255,255,255,.12) !important;
  }
  .modal-header .modal-title{ font-weight: 900 !important; }
  .modal-header .close{ color:#fff !important; opacity: .95 !important; text-shadow: none !important; }
  .modal-body{
    padding: 18px !important;
    background: linear-gradient(180deg, rgba(32,66,127,.04), rgba(255,255,255,1));
  }
  .modal-footer{
    background: #fff;
    border-top: 1px solid rgba(15,23,42,.10) !important;
  }

  .modal-dialog.modal-xl.centered{
    max-width: 96vw;
    margin: 1.25rem auto;
  }

  .resumen-table{
    max-height: 280px;
    overflow-y: auto;
    border-radius: 12px;
    border: 1px solid rgba(15,23,42,.10);
  }
  .table th{
    background-color: #f1f5f9 !important;
    font-weight: 800;
    font-size: 13px;
  }
  .table td{ font-size: 13px; }

  @media (max-width: 992px){
    .page-block{ padding: 14px; }
    .map-frame{ padding: 10px; }
  }
</style>


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

            <!-- Filtro principal -->
            <div class="row">
              <div class="col-12">
                <div class="card mb-4 card-info-complementaria">
                  <div class="card-header text-white">
                    <h5 class="mb-0">
                      <i class="bi bi-sliders me-2" style="font-size:1.2rem;"></i>
                      Informe comparativo
                    </h5>
                  </div>

                  <div class="card-body">
                    <div class="row">
                      <div class="col-md-12">
                        <div class="form-group mb-0">
                          <label for="secretariaUnicaId">
                            <i class="bi bi-building me-1" style="font-size:1.05rem;"></i>
                            Secretaria (Mapa Principal) <span class="text-danger">*</span>
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
                <div class="card h-100 w-100 map-card card-mapa-nuevo">
                  <div class="card-header">
                    <div class="title-wrap">
                      <span class="map-badge">
                        <i class="bi bi-map-fill"></i> Mapa Inicial
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
                    <div class="d-flex flex-wrap gap-2 justify-content-center mb-3">
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
                <div class="card h-100 w-100 map-card card-mapa">
                  <div class="card-header">
                    <div class="title-wrap">
                      <span class="map-badge">
                        <i class="bi bi-map-fill"></i> Mapa Actual
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
                          <div class="d-flex flex-wrap gap-2 justify-content-center mb-3">
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
        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
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
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
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