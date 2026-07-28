<?php
include './admin/include/head.php';

require './admin/include/generic_classes.php';
include './admin/classes/Secretarias.php';
include './admin/classes/Hacienda.php';
// Permisos
/* extract(PagePermissions::crudVarsForCurrentPage());
if (!$view) {
    require 'permiso_denegado.php';
} */
//$test = Hacienda::getConsolidadoHacienda(null);

$modulo = 'Hacienda';

// Información de secretarias
$arr = Secretarias::getAll(null);
$isvalid = $arr['output']['valid'];
$arr = $arr['output']['response'];
$optionSec = "";
foreach ($arr as $val) {
    $optionSec .= "<option value='" . $val['id'] . "'>" . $val['secretaria'] . " </option>";
}
?>
<!-- Select2 CSS -->
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />

<!-- Select2 JS -->
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<body class="">
    <!-- [ Pre-loader ] start -->
    <div class="loader-bg">
        <div class="loader-track">
            <div class="loader-fill"></div>
        </div>
    </div>
    <!-- [ Pre-loader ] End -->
    <!-- [ navigation menu ] start -->
    <?php
    include './admin/include/navbar.php';
    ?>
    <!-- [ navigation menu ] end -->
    <!-- [ Header ] start -->
    <?php
    include './admin/include/header.php';
    ?>
    <!-- [ Header ] end -->
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

  body{ background: var(--bg) !important; }
  .pcoded-main-container{ background: transparent !important; }
  .pcoded-content{ padding-top: 18px !important; }

  /* ===== Header SaaS ===== */
  .page-block{
    background: linear-gradient(135deg, rgba(32,66,127,.10), rgba(255,255,255,.72));
    border: 1px solid rgba(255,255,255,.70);
    border-radius: var(--radius-xl);
    box-shadow: var(--shadow-soft);
    padding: 16px 18px;
    backdrop-filter: blur(10px);
  }
  .page-block h5{
    font-weight: 900 !important;
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

  /* ===== Cards Pro ===== */
  .card{
    border: 1px solid var(--line) !important;
    border-radius: var(--radius-xl) !important;
    box-shadow: var(--shadow-soft);
    overflow: hidden;
    background: var(--card);
  }
  .card-header{
    border-bottom: 1px solid rgba(255,255,255,.10) !important;
    padding: 14px 16px !important;
    background: linear-gradient(135deg, var(--nav-blue), var(--nav-blue-2)) !important;
    color: #fff !important;
  }
  .card-header h5{
    color:#fff !important;
    margin:0 !important;
    font-weight: 900 !important;
    letter-spacing: .2px;
  }
  .card-body{
    background: linear-gradient(180deg, rgba(32,66,127,.04), rgba(255,255,255,1));
  }

  /* ===== Form Pro ===== */
  label.form-label, .form-group label{
    font-weight: 800;
    color: var(--ink);
    margin-bottom: 8px;
  }
  .form-control{
    border-radius: 14px !important;
    border: 1px solid rgba(15,23,42,.14) !important;
    padding: 12px 12px !important;
    height: auto !important;
    box-shadow: none !important;
    transition: .18s ease;
    background: #fff !important;
  }
  .form-control:focus{
    border-color: rgba(46,88,168,.55) !important;
    box-shadow: var(--ring) !important;
  }
  textarea.form-control{ min-height: 110px; resize: vertical; }

  /* ===== Chips / Helpers ===== */
  .hz-chip{
    display:inline-flex;
    align-items:center;
    gap:8px;
    padding: 8px 12px;
    border-radius: 999px;
    background: rgba(255,255,255,.16);
    border: 1px solid rgba(255,255,255,.18);
    font-size: 12px;
    font-weight: 900;
    color:#fff;
  }

  /* ===== Secciones internas (agrupación) ===== */
  .hz-section{
    border:1px solid rgba(15,23,42,.10);
    border-radius: var(--radius-lg);
    background: rgba(255,255,255,.78);
    box-shadow: 0 10px 24px rgba(2,6,23,.08);
    padding: 14px;
    margin-bottom: 12px;
  }
  .hz-section-title{
    display:flex;
    align-items:center;
    justify-content: space-between;
    gap:12px;
    margin-bottom: 10px;
  }
  .hz-section-title h6{
    margin:0;
    font-weight: 950;
    color: var(--ink);
    letter-spacing: .2px;
  }
  .hz-sub{
    margin:0;
    font-size: 12px;
    color: var(--muted);
    font-weight: 700;
  }

  /* ===== Botones SaaS ===== */
  .btn{
    border-radius: 14px !important;
    font-weight: 900 !important;
    letter-spacing: .2px;
    padding: 10px 14px !important;
    border: none !important;
    box-shadow: 0 10px 20px rgba(2,6,23,.12);
    transition: .18s ease;
  }
  .btn:hover{ transform: translateY(-1px); box-shadow: 0 14px 26px rgba(2,6,23,.16); }
  .btn:active{ transform: translateY(0px); }
  .btn-primary{ background: linear-gradient(135deg, var(--nav-blue-3), var(--nav-blue)) !important; }
  .btn-danger{ background: linear-gradient(135deg, #ef4444, #b91c1c) !important; }

  /* ===== Sticky actions (móvil) ===== */
  .hz-actions{
    display:flex;
    gap: 10px;
    flex-wrap: wrap;
    margin-top: 14px;
  }
  @media (max-width: 767.98px){
    .pcoded-content{ padding-left: 12px !important; padding-right: 12px !important; }
    .card-body.m-4{ margin: 12px !important; }
    input, select, textarea{ font-size: 16px !important; } /* evita zoom iOS */

    .hz-actions{
      position: sticky;
      bottom: 10px;
      z-index: 20;
      background: rgba(246,248,252,.92);
      backdrop-filter: blur(10px);
      padding: 10px;
      border: 1px solid rgba(15,23,42,.10);
      border-radius: 16px;
      box-shadow: 0 16px 40px rgba(2,6,23,.16);
    }
    .hz-actions .btn{ width: 100%; }
  }

  /* ===== Select2 pro ===== */
  .select2-container .select2-selection--multiple{
    min-height: 46px !important;
    border-radius: 14px !important;
    border: 1px solid rgba(15,23,42,.14) !important;
    padding: 6px 10px !important;
    background: #fff !important;
  }
  .select2-container--default.select2-container--focus .select2-selection--multiple{
    border-color: rgba(46,88,168,.55) !important;
    box-shadow: var(--ring) !important;
  }
  .select2-container--default .select2-selection--multiple .select2-selection__choice{
    border: 0 !important;
    border-radius: 999px !important;
    padding: 6px 10px !important;
    font-weight: 900 !important;
    background: rgba(32,66,127,.10) !important;
    color: var(--ink) !important;
  }
  .select2-container{ width: 100% !important; }

  /* ===== iframe upload (foto) ===== */
  .hz-upload iframe{
    width: 100% !important;
    max-width: 240px !important;
    border-radius: 14px;
    border: 1px solid rgba(15,23,42,.10);
    background: #fff;
  }
  @media (max-width: 767.98px){
    .hz-upload iframe{ max-width: 100% !important; }
  }
</style>

    <!-- [ Main Content ] start -->
    <div class="pcoded-main-container">
        <div class="pcoded-content">
            <!-- [ breadcrumb ] start -->
            <div class="page-header">
                <div class="page-block">
                    <div class="row align-items-center">

                        <div class="col-md-12">
                            <div class="d-flex justify-content-between align-items-center">
                                <h5 class="m-b-10">Ejecución Hacienda </h5>
                            <?php include './admin/include/btn_back.php'; ?>
                            </div>
                            <ul class="breadcrumb">
                                <li class="breadcrumb-item"><a href="index.php"><i class="feather icon-home"></i></a>
                                </li>
                                <li class="breadcrumb-item"><a href="#!">Ejecuciòn Hacienda / Seguimiento
                                    </a></li>
                            </ul>
                        </div>

                    </div>
                </div>
            </div>
            <!-- [ breadcrumb ] end -->
            <!-- [ Main Content ] start -->

            <div class="row">
                <div class="col-sm-12">
                    <div class="card">
                        <div class="card-header">
                            <h5>Ejecución Hacienda</h5>
                        </div>
                        <div class="card-body m-4">
  <form id="formsecretaria" class="needs-validation" novalidate>

    <!-- TOP: Consolidado + filtros principales -->
    <div class="hz-section">
      <div class="hz-section-title">
        <div>
          <h6><i class="bi bi-graph-up-arrow me-2"></i>Panel de ejecución</h6>
          <p class="hz-sub">Selecciona acción, fecha y municipio(s) para cargar el consolidado.</p>
        </div>
        <span class="hz-chip"><i class="bi bi-shield-check"></i> Hacienda</span>
      </div>

      <div class="row g-3">
        <div class="col-12">
          <div id="consolidadoHacienda" name="consolidadoHacienda"></div>
        </div>

        <div class="col-12 col-md-6 col-lg-4">
          <label for="accion" class="form-label">Tipo de Acción <span class="text-danger">*</span></label>
          <select class="form-control" id="accion" name="accion" onchange="HACIENDA.getConsolidadoHacienda();">
            <option value="seleccione">Seleccione</option>
            <option value="Capacitacion Fiscal y Financiera">Registro de Capacitaciones del GOA</option>

            <option value="Impuesto Vehicular Recaudado">Impuesto Vehicular Recaudado</option>
            <option value="Recaudo del impuesto al consumo">Recaudo del impuesto al consumo</option>
            <option value="Recaudo del impuesto de registro">Recaudo del impuesto de registro</option>
            <option value="Impuesto Estampillas Recaudado">Impuesto Estampillas Recaudado</option>

            <option value="GOA Aprehensiones de Licores">GOA - Aprehensiones de Licores</option>
            <option value="GOA Aprehensión de Cigarrillos">GOA - Aprehensión de Cigarrillos</option>
            <option value="GOA Aprehensión de Cervezas">GOA - Aprehensión de Cervezas</option>
            <option value="GOA Aprehensión de Tabaco y Otros">GOA - Aprehensión de Tabaco y Otros</option>

            <option value="Registro de Visitas a Establecimientos Comerciales">Registro de Visitas a Establecimientos Comerciales</option>
            <option value="GOA Juridico">GOA Jurídico</option>
          </select>
        </div>

        <div class="col-12 col-md-6 col-lg-4">
          <label for="date" class="form-label">Fecha <span class="text-danger">*</span></label>
          <input type="date" class="form-control" id="date" name="date" required value="<?php echo htmlspecialchars(date('Y-m-d')); ?>">
        </div>

        <div class="col-12 col-lg-4" id="divMunicipio" name="divMunicipio">
          <label for="tbl_municipio_id" class="form-label">Municipio <span class="text-danger">*</span></label>
          <select class="form-control" id="tbl_municipio_id" name="tbl_municipio_id[]" multiple>
            <!-- Opciones dinámicas -->
          </select>
        </div>
      </div>
    </div>

    <!-- DETALLE: campos dinámicos -->
    <div class="hz-section">
      <div class="hz-section-title">
        <div>
          <h6><i class="bi bi-ui-checks-grid me-2"></i>Detalle de registro</h6>
          <p class="hz-sub">Completa solo los campos que aplique según la acción seleccionada.</p>
        </div>
      </div>

      <div class="row g-3">
        <div class="col-12 col-lg-4 campo campo-objeto">
          <label for="objeto" class="form-label">Objeto</label>
          <textarea class="form-control" id="objeto" name="objeto" rows="2"></textarea>
        </div>

        <div class="col-12 col-md-6 col-lg-4 campo campo-tipo">
          <label for="tipo" class="form-label"><span id="TipoTexto"></span></label>
          <select class="form-control" id="tipo" name="tipo">
            <option value="">Seleccione</option>
            <option value="Nacional">Nacional</option>
            <option value="Extranjero">Extranjero</option>
          </select>
        </div>

        <div class="col-12 col-md-6 col-lg-4 campo campo-licores">
          <label for="incautacion_licores" class="form-label">Cantidad Licores Incautados</label>
          <input type="text" onKeyPress="return soloNumeros(event);" class="form-control" id="incautacion_licores" name="incautacion_licores" placeholder="Ingrese cantidad de licores">
        </div>

        <div class="col-12 col-md-6 col-lg-4 campo campo-valor-licores">
          <label for="valor_licores" class="form-label">Valor Incautación Licores</label>
          <input type="text" onKeyPress="return soloNumeros(event);" class="form-control campo-valor campo-valor-licores" id="valor_licores" name="valor_licores" placeholder="Ingrese el valor incautado">
        </div>

        <div class="col-12 col-md-6 col-lg-4 campo campo-cerveza">
          <label for="incautacion_cerveza" class="form-label">Cantidad Incautación Cervezas</label>
          <input type="text" onKeyPress="return soloNumeros(event);" class="form-control" id="incautacion_cerveza" name="incautacion_cerveza" placeholder="Ingrese la cantidad incautada">
        </div>

        <div class="col-12 col-md-6 col-lg-4 campo campo-valor-cerveza">
          <label for="valor_cerveza" class="form-label">Valor Incautación Cervezas</label>
          <input type="text" onKeyPress="return soloNumeros(event);" class="form-control campo-valor campo-valor-cerveza" id="valor_cerveza" name="valor_cerveza" placeholder="Ingrese el valor incautación de cervezas">
        </div>

        <div class="col-12 col-md-6 col-lg-4 campo campo-valor-importado">
          <label for="valor_importado" class="form-label">Valor Licores Importados</label>
          <input type="text" onKeyPress="return soloNumeros(event);" class="form-control campo-valor campo-valor-importado" id="valor_importado" name="valor_importado" placeholder="Ingrese el valor">
        </div>

        <div class="col-12 col-md-6 col-lg-4 campo campo-valor-nacional">
          <label for="valor_nacional" class="form-label">Valor Licores Nacionales</label>
          <input type="text" onKeyPress="return soloNumeros(event);" class="form-control campo-valor campo-valor-nacional" id="valor_nacional" name="valor_nacional" placeholder="Ingrese valor recaudado">
        </div>

        <div class="col-12 col-md-6 col-lg-4 campo campo-valor-tramite">
          <label for="valor_tramite" class="form-label">Valor Recaudo de Trámites</label>
          <input type="text" onKeyPress="return soloNumeros(event);" class="form-control campo-valor campo-valor-tramite" id="valor_tramite" name="valor_tramite" placeholder="Ingrese valor recaudado">
        </div>

        <div class="col-12 col-md-6 col-lg-4 campo campo-valor-recaudo">
          <label for="valor_recaudo" class="form-label">Valor Impuesto Recaudado</label>
          <input type="text" onKeyPress="return soloNumeros(event);" class="form-control campo-valor campo-valor-recaudo" id="valor_recaudo" name="valor_recaudo" placeholder="Ingrese valor del impuesto recaudado">
        </div>

        <div class="col-12 col-md-6 col-lg-4 campo campo-estampilla">
          <label for="estampilla" class="form-label">Tipo Estampilla</label>
          <select class="form-control" id="estampilla" name="estampilla">
            <option value="">Seleccione</option>
            <option value="PRO UIS">PRO UIS</option>
            <option value="OBRA PUBLICA">OBRA PÚBLICA</option>
            <option value="PRO HOSPITAL">PRO HOSPITAL</option>
            <option value="PRO DEPORTE">PRO DEPORTE</option>
            <option value="PRO CULTURA">PRO CULTURA</option>
            <option value="PRO DESARROLLO">PRO DESARROLLO</option>
            <option value="PRO ELECTRIFICACION">PRO ELECTRIFICACION</option>
            <option value="FONDO DE REFORESTACION">FONDO DE REFORESTACION</option>
            <option value="DEGUELLO DE GANADO MAYOR">DEGUELLO DE GANADO MAYOR</option>
            <option value="PRO BIENESTAR ADULTO MAYOR">PRO BIENESTAR ADULTO MAYOR</option>
          </select>
        </div>

        <div class="col-12 col-md-6 col-lg-4 campo campo-valor-estampilla">
          <label for="valor_estampilla" class="form-label">Valor Estampilla Recaudado</label>
          <input type="text" onKeyPress="return soloNumeros(event);" class="form-control campo-valor campo-valor-estampilla" id="valor_estampilla" name="valor_estampilla" placeholder="Ingrese el valor recaudado">
        </div>

        <div class="col-12 col-md-6 col-lg-4 campo campo-tipo-cigarrillo">
          <label for="tipo_cigarrillo" class="form-label">Tipo Cigarrillo</label>
          <select class="form-control" id="tipo_cigarrillo" name="tipo_cigarrillo">
            <option value="Nacional">Nacional</option>
            <option value="Extrajero">Extrajero</option>
          </select>
        </div>

        <div class="col-12 col-md-6 col-lg-4 campo campo-cigarrillos">
          <label for="incautacion_cigarrillos" class="form-label">Cantidad Cigarrillos Incautados</label>
          <input type="text" onKeyPress="return soloNumeros(event);" class="form-control" id="incautacion_cigarrillos" name="incautacion_cigarrillos" placeholder="Ingrese la cantidad">
        </div>

        <div class="col-12 col-md-6 col-lg-4 campo campo-valor-cigarrillos">
          <label for="valor_cigarrillos" class="form-label">Valor Cigarrillos Incautado</label>
          <input type="text" onKeyPress="return soloNumeros(event);" class="form-control campo-valor campo-valor-cigarrillos" id="valor_cigarrillos" name="valor_cigarrillos" placeholder="Ingrese el valor incautado">
        </div>

        <div class="col-12 col-md-6 col-lg-4 campo campo-tipo-tabaco">
          <label for="tipo_tabaco" class="form-label">Tipo Tabaco</label>
          <select class="form-control" id="tipo_tabaco" name="tipo_tabaco">
            <option value="Nacional">Nacional</option>
            <option value="Extrajero">Extrajero</option>
          </select>
        </div>

        <div class="col-12 col-md-6 col-lg-4 campo campo-tabaco">
          <label for="incautacion_tabaco" class="form-label">Cantidad Tabaco Incautado</label>
          <input type="text" onKeyPress="return soloNumeros(event);" class="form-control" id="incautacion_tabaco" name="incautacion_tabaco" placeholder="Ingrese la cantidad">
        </div>

        <div class="col-12 col-md-6 col-lg-4 campo campo-valor-tabaco">
          <label for="valor_tabaco" class="form-label">Valor Tabaco Incautado</label>
          <input type="text" onKeyPress="return soloNumeros(event);" class="form-control campo-valor campo-valor-tabaco" id="valor_tabaco" name="valor_tabaco" placeholder="Ingrese el valor incautado">
        </div>

        <div class="col-12 col-md-6 col-lg-4 campo campo-cantidad-personas">
          <label for="cantidad_personas" class="form-label">Personas Capacitadas</label>
          <input type="text" onKeyPress="return soloNumeros(event);" class="form-control" id="cantidad_personas" name="cantidad_personas" placeholder="Ingrese el número de personas">
        </div>

        <div class="col-12 col-lg-6 campo campo-valor-tramite-impuesto-vehicular">
          <label for="valor_tramite_impuesto_vehicular" class="form-label">Valor Recaudado Trámites</label>
          <input type="text" onKeyPress="return soloNumeros(event);" class="form-control campo-valor campo-valor-tramite-impuesto-vehicular" id="valor_tramite_impuesto_vehicular" name="valor_tramite_impuesto_vehicular" placeholder="Ingrese el valor de los trámites $$$">
        </div>

        <div class="col-12 col-lg-6 campo campo-valor-recaudo-impuesto-vehicular">
          <label for="valor_recaudo_impuesto_vehicular" class="form-label">Valor Recaudado Impuesto Vehicular</label>
          <input type="text" onKeyPress="return soloNumeros(event);" class="form-control campo-valor campo-valor-recaudo-impuesto-vehicular" id="valor_recaudo_impuesto_vehicular" name="valor_recaudo_impuesto_vehicular" placeholder="Ingrese el valor recaudado del impuesto vehicular $$">
        </div>

        <!-- Título Operativos Vehículos -->
        <div class="col-12 campo campo-vehicular-titulo-operativos d-none">
          <h6 class="fw-bold text-secondary border-bottom pb-1 mt-2">
            <i class="bi bi-car-front-fill me-1"></i> Operativos Vehículos
          </h6>
        </div>

        <div class="col-12 col-md-6 campo campo-vehicular-cantidad-operativos d-none">
          <label for="vehicular_cantidad_operativos" class="form-label">Cantidad de Operativos</label>
          <input type="text" onKeyPress="return soloNumeros(event);" class="form-control" id="vehicular_cantidad_operativos" name="vehicular_cantidad_operativos" placeholder="Ingrese el número de operativos realizados">
        </div>

        <div class="col-12 col-md-6 campo campo-vehicular-cantidad-emplazados d-none">
          <label for="vehicular_cantidad_emplazados" class="form-label">Cantidad de Emplazados</label>
          <input type="text" onKeyPress="return soloNumeros(event);" class="form-control" id="vehicular_cantidad_emplazados" name="vehicular_cantidad_emplazados" placeholder="Ingrese el número de vehículos o personas emplazadas">
        </div>

        <div class="col-12 col-md-6 campo campo-vehicular-cantidad-placas d-none">
          <label for="vehicular_cantidad_placas_consultadas" class="form-label">Cantidad de Placas Consultadas</label>
          <input type="text" onKeyPress="return soloNumeros(event);" class="form-control" id="vehicular_cantidad_placas_consultadas" name="vehicular_cantidad_placas_consultadas" placeholder="Ingrese el número de placas consultadas en el sistema">
        </div>

        <div class="col-12 col-md-6 campo campo-vehicular-cantidad-campanas d-none">
          <label for="vehicular_cantidad_campanas_sensibilizacion" class="form-label">Cantidad de Campañas de Sensibilización</label>
          <input type="text" onKeyPress="return soloNumeros(event);" class="form-control" id="vehicular_cantidad_campanas_sensibilizacion" name="vehicular_cantidad_campanas_sensibilizacion" placeholder="Ingrese el número de campañas de sensibilización ejecutadas">
        </div>

        <div class="col-12 col-md-6 col-lg-4 campo campo-cantidad-aprehendida">
          <label for="cantidad_aprehendida" class="form-label">Cantidad aprehendida</label>
          <input type="text" onKeyPress="return soloNumeros(event);" class="form-control" id="cantidad_aprehendida" name="cantidad_aprehendida" value="0" placeholder="Cantidad aprehendida">
        </div>

        <div class="col-12 col-md-6 col-lg-4 campo campo-avaluo-comercial">
          <label for="avaluo_comercial" class="form-label">Avalúo comercial</label>
          <input type="text" onKeyPress="return soloNumeros(event);" class="form-control" id="avaluo_comercial" name="avaluo_comercial" value="0" placeholder="Avalúo comercial">
        </div>

        <div class="col-12 col-md-6 col-lg-4 campo campo-cantidad-visitas-al-municipio">
          <label for="cantidad_visitas_al_municipio" class="form-label">Cantidad de visitas a ese municipio</label>
          <input type="text" onKeyPress="return soloNumeros(event);" class="form-control" id="cantidad_visitas_al_municipio" name="cantidad_visitas_al_municipio" value="0" placeholder="Cantidad de visitas a ese municipio">
        </div>

        <div class="col-12 col-md-6 col-lg-4 campo campo-direccion">
          <label for="direccion" class="form-label">Dirección</label>
          <input type="text" class="form-control" id="direccion" name="direccion" placeholder="Dirección del establecimiento">
        </div>

        <div class="col-12 col-md-6 col-lg-4 campo campo-barrio">
          <label for="barrio" class="form-label">Barrio</label>
          <input type="text" class="form-control" id="barrio" name="barrio" placeholder="Barrio">
        </div>

        <div class="col-12 col-md-6 col-lg-4 campo campo-administrador">
          <label for="administrador" class="form-label">Administrador</label>
          <input type="text" class="form-control" id="administrador" name="administrador" placeholder="Administrador">
        </div>

        <div class="col-12 col-md-6 col-lg-4 campo campo-nombre-establecimiento">
          <label for="nombre_establecimiento" class="form-label">Nombre Establecimiento</label>
          <input type="text" class="form-control" id="nombre_establecimiento" name="nombre_establecimiento" placeholder="Nombre Establecimiento">
        </div>

        <div class="col-12 col-md-6 col-lg-4 campo campo-tipo-establecimiento">
          <label for="tipoe" class="form-label">Tipo capacitaciones del Establecimiento</label>
          <select class="form-control" id="tipoe" name="tipoe">
            <option value="Establecimiento comercial">Establecimiento comercial</option>
          </select>
        </div>

        <div class="col-12 col-md-6 col-lg-4 campo campo-establecimiento">
          <label for="establecimiento" class="form-label">Establecimiento</label>
          <select class="form-control" id="establecimiento" name="establecimiento">
            <option value="Bodega">Bodega</option>
            <option value="Comercializadora">Comercializadora</option>
            <option value="Dulcería">Dulcería</option>
            <option value="Bar/Discoteca">Bar/Discoteca</option>
            <option value="Tienda">Tienda</option>
            <option value="Distribuidora">Distribuidora</option>
            <option value="Supermercado/Fruver">Supermercado/Fruver</option>
            <option value="Restaurante/Comidas">Restaurante/Comidas</option>
            <option value="Cafetería">Cafetería</option>
            <option value="Autoservicio">Autoservicio</option>
            <option value="Estanco/Licorera">Estanco/Licorera</option>
            <option value="Panadería/Pastelería">Panadería/Pastelería</option>
            <option value="Variedades/Abarrotes">Variedades/Abarrotes</option>
            <option value="Quiosco/Caseta">Quiosco/Caseta</option>
            <option value="Otro">Otro</option>
            <option value="Hotel/Hospedaje">Hotel/Hospedaje</option>
            <option value="Miscelánea">Miscelánea</option>
            <option value="Granero">Granero</option>
            <option value="Farmacía/Droguería">Farmacía/Droguería</option>
            <option value="Balneario">Balneario</option>
            <option value="Billar">Billar</option>
            <option value="Agencia">Agencia</option>
            <option value="Asadero">Asadero</option>
          </select>
        </div>

        <div class="col-12 col-md-6 col-lg-4 campo campo-tipo-capacitaciones-goa">
          <label class="form-label">Tipo capacitaciones del GOA</label>
          <select class="form-control" name="tipo_capacitacion_goa">
            <option value="">Seleccione</option>
            <option value="Capacitaciones a Aliados Estratégicos">Capacitaciones a Aliados Estratégicos</option>
            <option value="Capacitaciones a Jóvenes">Capacitaciones a Jóvenes</option>
            <option value="Capacitaciones al GOA">Capacitaciones al GOA</option>
          </select>
        </div>

        <div class="col-12 col-md-6 col-lg-4 campo campo-cantidad-asistentes">
          <label for="numero_asistentes" class="form-label">Número De Asistentes (GOA)</label>
          <input type="text" onKeyPress="return soloNumeros(event);" class="form-control" id="numero_asistentes" name="numero_asistentes" value="0" placeholder="Número De Asistentes">
        </div>

        <!-- ===== GOA JURÍDICO ===== -->
        <!-- Título: Proceso en Custodia -->
        <div class="col-12 campo campo-goa-juridico-titulo-custodia d-none">
          <div class="hz-section-title mt-2 mb-0">
            <div>
              <h6><i class="bi bi-folder2-open me-2"></i>Proceso en Custodia</h6>
            </div>
          </div>
        </div>

        <div class="col-12 col-md-6 col-lg-4 campo campo-goa-juridico-custodia-valor-total d-none">
          <label for="goa_juridico_custodia_valor_total" class="form-label">Valor Total</label>
          <input type="text" onKeyPress="return soloNumeros(event);" class="form-control campo-valor campo-valor-goa-juridico" id="goa_juridico_custodia_valor_total" name="goa_juridico_custodia_valor_total" placeholder="Ingrese el valor total estimado del proceso en custodia $$$">
        </div>

        <div class="col-12 col-md-6 col-lg-4 campo campo-goa-juridico-custodia-cantidad-procesos d-none">
          <label for="goa_juridico_custodia_cantidad_procesos" class="form-label">Cantidad de Procesos</label>
          <input type="text" onKeyPress="return soloNumeros(event);" class="form-control" id="goa_juridico_custodia_cantidad_procesos" name="goa_juridico_custodia_cantidad_procesos" placeholder="Ingrese el número de procesos actualmente en custodia">
        </div>

        <div class="col-12 col-md-6 col-lg-4 campo campo-goa-juridico-custodia-cantidad-unidades d-none">
          <label for="goa_juridico_custodia_cantidad_unidades" class="form-label">Cantidad de Unidades</label>
          <input type="text" onKeyPress="return soloNumeros(event);" class="form-control" id="goa_juridico_custodia_cantidad_unidades" name="goa_juridico_custodia_cantidad_unidades" placeholder="Ingrese el número de unidades bajo custodia">
        </div>

        <!-- Título: Destrucción de Procesos -->
        <div class="col-12 campo campo-goa-juridico-titulo-destruccion d-none">
          <div class="hz-section-title mt-3 mb-0">
            <div>
              <h6><i class="bi bi-trash3 me-2"></i>Destrucción de Procesos</h6>
            </div>
          </div>
        </div>

        <div class="col-12 col-md-6 col-lg-4 campo campo-goa-juridico-destruccion-cantidad-unidades d-none">
          <label for="goa_juridico_destruccion_cantidad_unidades" class="form-label">Cantidad de Unidades</label>
          <input type="text" onKeyPress="return soloNumeros(event);" class="form-control" id="goa_juridico_destruccion_cantidad_unidades" name="goa_juridico_destruccion_cantidad_unidades" placeholder="Ingrese el número de unidades destruidas en el proceso">
        </div>

        <div class="col-12 col-md-6 col-lg-4 campo campo-goa-juridico-destruccion-valor-total d-none">
          <label for="goa_juridico_destruccion_valor_total" class="form-label">Valor Total</label>
          <input type="text" onKeyPress="return soloNumeros(event);" class="form-control campo-valor campo-valor-goa-juridico" id="goa_juridico_destruccion_valor_total" name="goa_juridico_destruccion_valor_total" placeholder="Ingrese el valor total de la mercancía destruida $$$">
        </div>
        <!-- ===== FIN GOA JURÍDICO ===== -->

        <div class="col-12 col-lg-10">
          <label for="observaciones" class="form-label">Observaciones</label>
          <textarea class="form-control" id="observaciones" placeholder="Ingrese una observación" name="observaciones"></textarea>
        </div>

        <div class="col-12 col-lg-2 hz-upload">
          <label class="form-label">Foto</label>
          <iframe id="ifm1" name="ifm1" src="upload.php" height="60" scrolling="no" frameborder="0"></iframe>
        </div>
      </div>
    </div>

    <!-- Acciones -->
    <div class="hz-actions">
      <button type="button" onclick="UTIL.clearForm('formsecretaria');" class="btn btn-danger">
        <i class="bi bi-x-circle me-2"></i>Cancelar
      </button>
      <button type="button" onclick="HACIENDA.saveData();" class="btn btn-primary">
        <i class="bi bi-save2 me-2"></i>Guardar
      </button>
    </div>

  </form>
</div>



                </div>
            </div>

        </div>
    </div>
    </div>
<script>
  // Select2 para municipio (multiselect)
  $(document).ready(function() {
    if ($('#tbl_municipio_id').length) {
      $('#tbl_municipio_id').select2({
        placeholder: "Selecciona municipio(s)",
        width: '100%',
        allowClear: true
      });
    }
  });
</script>

    <!-- Required Js -->
    <?php include 'admin/include/gerenic_script.php'; ?>
    <script src="<?php echo Util::versionar('./admin/js/departamento.js'); ?>"></script>
    <script src="<?php echo Util::versionar('./admin/js/hacienda.js'); ?>"></script>


    <script src="assets/js/vendor-all.min.js"></script>
    <script src="assets/js/plugins/bootstrap.min.js"></script>
    <script src="assets/js/pcoded.min.js"></script>
    <script>
        initHacienda();
        DEPARTAMENTO.getMunicipiosConDepartamentoPrincipal();
    </script>


</body>

</html>