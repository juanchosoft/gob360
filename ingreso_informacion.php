<?php
include './admin/include/head.php';
include './admin/include/dark_theme.php';

require './admin/include/generic_classes.php';
include './admin/classes/Departamento.php';
include './admin/classes/Factores.php';

// Permisos
/* extract(PagePermissions::crudVarsForCurrentPage());
if (!$view) {
    require 'permiso_denegado.php';
} */

// Información de Departamentos
$arrDep = Departamento::getAll(null);
$isvalid = $arrDep['output']['valid'];
$arrDep = $arrDep['output']['response'];
$optionDep = Util::getDepartamentoPrincipal();
foreach ($arrDep as $val) {
    $optionDep .= "<option " . ($val["codigo_departamento"] == Util::getDepartamentoPrincipal() ? "selected" : "") . " value='" . $val['codigo_departamento'] . "'>" . $val['codigo_departamento'] . " - " . $val['departamento'] . "</option>";
}

// Información de Factores
$arrFactores = Factores::getAll(null);
$isvalid = $arrFactores['output']['valid'];
$arrFactores = $arrFactores['output']['response'];
$optionFactores = '<option value="seleccione">Seleccione...</option>';
foreach ($arrFactores as $val) {
    $optionFactores .= "<option  class='" . $val['icono'] . "'  value='" . $val['id'] . "'>" . $val['tipo'] . "</option>";
}
?>
<style>
  /* ============================
     ACCIÓN UNIFICADA - SAAS PRO (LIGHT)
     Fix: labels visibles + inputs blancos + mejor card
     (NO toca back / ids / js)
  ============================ */

  :root{
    --au-primary: #4f7cff;
    --au-primary-2:#6b8cff;
    --au-bg: #070A12;
    --au-card: rgba(255,255,255,.06);
    --au-border: rgba(255,255,255,.10);
    --au-text: rgba(255,255,255,.92);
    --au-muted: rgba(255,255,255,.66);
    --au-shadow: 0 20px 60px rgba(0,0,0,.35);
    --au-shadow-soft: 0 14px 40px rgba(0,0,0,.25);
    --au-radius-xl: 22px;
    --au-radius-lg: 18px;
    --au-radius-md: 14px;
    --au-radius-sm: 12px;

    --au-input-bg: rgba(255,255,255,.06);
    --au-input-border: rgba(255,255,255,.12);
    --au-input-border-focus: rgba(79,124,255,.45);

    --au-danger: #ff5b7a;
    --au-success: #18ff6d;
  }

  /* Fondo oscuro */
  body{
    background:
      radial-gradient(900px 420px at 10% 10%, rgba(79,124,255,.28), transparent 60%),
      radial-gradient(900px 420px at 80% 20%, rgba(155,92,255,.22), transparent 60%),
      radial-gradient(900px 520px at 50% 100%, rgba(24,255,109,.10), transparent 60%),
      linear-gradient(180deg, var(--au-bg), #0B1222) !important;
  }
  .pcoded-main-container .pcoded-content{
    position: relative;
    padding-top: 18px;
  }
  .pcoded-main-container .pcoded-content:before{
    content:"";
    position:absolute;
    left: -18px;
    right: -18px;
    top: -24px;
    height: 220px;
    background:
      radial-gradient(900px 220px at 18% 0%, rgba(79,124,255,.22), transparent 60%),
      radial-gradient(900px 220px at 82% 0%, rgba(79,124,255,.18), transparent 60%),
      linear-gradient(180deg, rgba(79,124,255,.10), transparent);
    pointer-events:none;
    z-index:0;
  }
  .pcoded-main-container .pcoded-content > *{ position:relative; z-index:1; }

  /* Page header */
  .page-header .page-block{
    border-radius: var(--au-radius-xl) !important;
    background: linear-gradient(180deg, rgba(255,255,255,.08), rgba(255,255,255,.04)) !important;
    border: 1px solid var(--au-border) !important;
    box-shadow: var(--au-shadow-soft) !important;
    overflow: hidden;
  }
  .page-header h5{
    color: #fff !important;
    font-weight: 900 !important;
    letter-spacing: .2px;
  }
  .breadcrumb{ background: transparent !important; }

  /* Card principal */
  .card{
    border-radius: var(--au-radius-xl) !important;
    border: 1px solid var(--au-border) !important;
    background: var(--au-card) !important;
    box-shadow: var(--au-shadow) !important;
    overflow: hidden;
  }
  .card-header{
    padding: 18px 20px !important;
    border-bottom: 1px solid rgba(255,255,255,.10) !important;
    background:
      radial-gradient(900px 220px at 0% 0%, rgba(79,124,255,.16), transparent 60%),
      linear-gradient(180deg, rgba(255,255,255,.95), rgba(255,255,255,.88)) !important;
  }
  .card-header h5{
    font-size: 20px !important;
    margin:0 !important;
    color: #fff !important;
    font-weight: 950 !important;
    display:flex;
    align-items:center;
    gap:10px;
  }
  .card-header h5 i{
    width: 40px;
    height: 40px;
    border-radius: 14px;
    display:inline-flex;
    align-items:center;
    justify-content:center;
    background: rgba(79,124,255,.12);
    border: 1px solid rgba(79,124,255,.18);
    color: var(--au-primary);
  }
  .card-body{
    padding: 18px 20px !important;
  }

  /* ✅ FIX: Labels visibles */
  label{
    display:block !important;
    color: #fff !important;
    font-weight: 800 !important;
    font-size: 13px !important;
    margin-bottom: 6px !important;
  }
  .text-danger{
    color: var(--au-danger) !important;
    font-weight: 900;
  }

  /* ✅ FIX: Inputs blancos (NO grises) */
  .form-control{
    background: var(--au-input-bg) !important;
    color: #fff !important;
    border: 1px solid var(--au-input-border) !important;
    border-radius: var(--au-radius-md) !important;
    height: 46px !important;
    padding: 10px 12px !important;
    box-shadow: 0 6px 14px rgba(0,0,0,.20) !important;
    transition: .18s ease;
  }
  .form-control::placeholder{
    color: rgba(100,116,139,.85) !important;
  }
  .form-control:focus{
    border-color: var(--au-input-border-focus) !important;
    box-shadow: 0 0 0 .2rem rgba(79,124,255,.18) !important;
    outline: none !important;
  }
  select.form-control{
    padding-right: 34px !important;
  }

  /* Grid limpio (sin sombras raras) */
  .saas-form-grid{
    display:grid;
    grid-template-columns: repeat(12, 1fr);
    gap: 14px;
  }
  .saas-col-2{ grid-column: span 2; }
  .saas-col-4{ grid-column: span 4; }
  .saas-col-12{ grid-column: span 12; }

  @media (max-width: 1200px){
    .saas-col-2{ grid-column: span 6; }
    .saas-col-4{ grid-column: span 6; }
  }
  @media (max-width: 768px){
    .saas-form-grid{ gap: 12px; }
    .saas-col-2,.saas-col-4,.saas-col-12{ grid-column: span 12; }
  }

  /* Panel info factores (más pro, sin bordes duros) */
  #divInformacion{
    border: 1px solid rgba(255,255,255,.12) !important;
    border-radius: var(--au-radius-lg) !important;
    background: rgba(255,255,255,.04) !important;
    box-shadow: 0 10px 25px rgba(0,0,0,.25) !important;
    padding: 14px !important;
    margin-top: 8px !important;
  }
  #divInformacion label{
    font-size: 12px !important;
    color: var(--au-muted) !important;
    font-weight: 900 !important;
    text-transform: uppercase;
    letter-spacing: .35px;
  }
  #divInformacion .form-control{
    height: 44px !important;
    background: rgba(255,255,255,.06) !important;
  }

  /* Observaciones (más alto y legible) */
  #observaciones{
    height: 50px !important;
  }

  /* Botonera PRO */
  .saas-actions{
    display:flex;
    align-items:center;
    justify-content:center;
    gap: 14px;
    flex-wrap: wrap;
    margin-top: 10px;
    padding-top: 14px;
    border-top: 1px dashed rgba(255,255,255,.14);
  }
  .btn{
    border-radius: 14px !important;
    font-weight: 900 !important;
    padding: 10px 16px !important;
    box-shadow: 0 12px 22px rgba(0,0,0,.30) !important;
    transition: transform .12s ease, box-shadow .12s ease, filter .12s ease;
  }
  .btn:hover{ transform: translateY(-1px); filter: brightness(1.02); }
  .btn:active{ transform: translateY(0px) scale(.99); }

  .btn-primary{
    background: linear-gradient(135deg, var(--au-primary-2), var(--au-primary)) !important;
    border: 1px solid rgba(255,255,255,.18) !important;
  }
  .btn-danger{
    background: linear-gradient(135deg, #fb7185, #ef4444) !important;
    border: 1px solid rgba(255,255,255,.18) !important;
  }

  /* Botón geolocalización grande pro */
  .saas-geo-btn{
    width: 78px !important;
    height: 78px !important;
    padding: 0 !important;
    border-radius: 18px !important;
    overflow:hidden;
    border: 1px solid rgba(255,255,255,.18) !important;
    box-shadow: 0 16px 30px rgba(0,0,0,.35) !important;
    position: relative;
  }
  .saas-geo-btn img{
    width: 100% !important;
    height: 100% !important;
    object-fit: cover !important;
  }

  /* Uploads: tarjetas claras y ordenadas */
  .saas-upload-grid{
    display:grid;
    grid-template-columns: repeat(12, 1fr);
    gap: 14px;
    padding-top: 10px;
  }
  .saas-upload-item{
    grid-column: span 3;
    background: rgba(255,255,255,.06) !important;
    border: 1px solid rgba(255,255,255,.12) !important;
    border-radius: 16px !important;
    box-shadow: 0 10px 22px rgba(0,0,0,.25) !important;
    padding: 12px;
  }
  .saas-upload-item label{
    margin-bottom: 8px !important;
    font-size: 13px !important;
    color: #fff !important;
  }
  .saas-upload-item iframe{
    width: 100% !important;
    height: 64px !important;
    border-radius: 12px !important;
    background: rgba(255,255,255,.06) !important;
    border: 1px solid rgba(15,23,42,.12) !important;
  }
  @media (max-width: 1200px){
    .saas-upload-item{ grid-column: span 6; }
  }
  @media (max-width: 768px){
    .saas-upload-item{ grid-column: span 12; }
  }

  /* Modal mapa PRO */
  #modalGeocalizacion .modal-dialog{ max-width: 980px; }
  #modalGeocalizacion .modal-content{
    border-radius: 22px !important;
    border: 1px solid rgba(255,255,255,.12) !important;
    background: rgba(255,255,255,.06) !important;
    box-shadow: 0 35px 70px rgba(2,6,23,.22) !important;
    overflow:hidden;
  }
  #modalGeocalizacion .modal-header{
    border-bottom: 1px solid rgba(255,255,255,.10) !important;
    background: linear-gradient(135deg, rgba(79,124,255,.35), rgba(79,124,255,.10)) !important;
  }
  #modalGeocalizacion .modal-title{
    color: #fff !important;
    font-weight: 950 !important;
  }
  #map{
    border-radius: 18px !important;
    border: 1px solid rgba(255,255,255,.12) !important;
    overflow:hidden;
  }
  .saas-map-tools{
    margin-top: 12px;
    display:flex;
    flex-wrap: wrap;
    gap: 10px;
    padding: 10px;
    border-radius: 16px;
    border: 1px solid rgba(255,255,255,.10);
    background: rgba(79,124,255,.05);
  }
  .saas-map-tools label{
    margin:0 !important;
    padding: 8px 10px;
    border-radius: 999px;
    border: 1px solid rgba(255,255,255,.10);
    background: #fff;
    font-size: 12px !important;
    font-weight: 900 !important;
    color: #fff !important;
    text-transform: uppercase;
    letter-spacing: .25px;
    display:flex !important;
    align-items:center;
    gap: 8px;
  }
  .saas-map-tools input[type="checkbox"]{
    width: 18px;
    height: 18px;
    accent-color: var(--au-primary-2);
  }
  .coordinates{
    margin-top: 10px;
    padding: 10px 12px;
    border-radius: 14px;
    border: 1px solid rgba(255,255,255,.10);
    background: #fff;
    color: #fff !important;
    font-weight: 900;
  }
/* =========================
   NUEVA ORGANIZACIÓN LIMPIA
========================= */

.section-block{
    padding: 10px 0;
}

.section-title{
    font-weight: 800;
    font-size: 15px;
    letter-spacing: .4px;
    text-transform: uppercase;
    color: rgba(255,255,255,.92);
    position: relative;
    margin-bottom: 10px;
}

.section-title:after{
    content: "";
    width: 60px;
    height: 3px;
    background: linear-gradient(90deg,rgba(79,124,255,.50),rgba(155,92,255,.30));
    display:block;
    margin: 6px auto 0;
    border-radius: 3px;
}

/* Inputs pequeños superiores */
.input-small{
    height: 40px !important;
    font-size: 14px !important;
}

/* Campo valor más pequeño */
.input-mini{
    height: 40px !important;
    font-size: 14px !important;
    text-align: center;
}

/* Centrado visual */
.text-center select,
.text-center input{
    text-align: center;
}

/* Separación elegante */
.section-block + .section-block{
    border-top: 1px dashed rgba(0,0,0,.08);
    padding-top: 25px;
}
  /* ✅ IMPORTANTE: NO mates padding interno (quita ese "card-body .card-body {padding:0}" que te dañó todo) */
  .card-body .card-body{ padding: 12px 0 !important; }
</style>

<body class="">
    <!-- [ Pre-loader ] start -->
    <div class="loader-bg">
        <div class="loader-track">
            <div class="loader-fill"></div>
        </div>
    </div>
    <!-- [ Pre-loader ] End -->

    <!-- [ navigation menu ] start -->
    <?php include './admin/include/navbar.php'; ?>
    <!-- [ navigation menu ] end -->

    <!-- [ Header ] start -->
    <?php include './admin/include/header.php'; ?>
    <!-- [ Header ] end -->

    <!-- [ Main Content ] start -->
    <div class="pcoded-main-container">
        <div class="pcoded-content">

            <!-- [ breadcrumb ] start -->
            <div class="page-header">
                <div class="page-block">
                    <div class="row align-items-center">
                        <div class="col-md-12">
                            <div class="d-flex justify-content-between align-items-center">
                                <h5 class="m-b-10">Ingreso información</h5>
                                <?php include './admin/include/btn_back.php'; ?>
                            </div>
                            <ul class="breadcrumb">
                                <li class="breadcrumb-item"><a href="index.html"><i class="feather icon-home"></i></a></li>
                                <li class="breadcrumb-item"><a href="#!">Configuración Acción Unificada / Ingreso información</a></li>
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
                        <form id="formingresoinformacion" autocomplete="off">

                            <div class="card-header">
                                <h5><i class="feather icon-edit"></i> Ingreso de información</h5>

                                <div class="card-header-right">
                                    <div class="btn-group card-option">
                                        <button type="button" class="btn dropdown-toggle btn-icon"
                                            data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                            <i class="feather icon-more-horizontal"></i>
                                        </button>
                                        <ul class="list-unstyled card-option dropdown-menu dropdown-menu-right">
                                            <li class="dropdown-item full-card"><a href="#!"><span><i
                                                            class="feather icon-maximize"></i> Maximizar</span><span
                                                        style="display:none"><i class="feather icon-minimize"></i>
                                                        Restaurar</span></a></li>
                                            <li class="dropdown-item minimize-card"><a href="#!"><span><i
                                                            class="feather icon-minus"></i> Colapsa</span><span
                                                        style="display:none"><i class="feather icon-plus"></i>
                                                        Expandir</span></a></li>
                                            <li class="dropdown-item reload-card"><a href="#!"><i
                                                        class="feather icon-refresh-cw"></i> Recargar</a></li>
                                            <li class="dropdown-item close-card"><a href="#!"><i
                                                        class="feather icon-trash"></i> Remover</a></li>
                                        </ul>
                                    </div>
                                </div>
                            </div>

                            <div class="card-body">

                                <!-- hidden (NO tocar) -->
                                <input type="hidden" name="op" id="op" />
                                <input type="hidden" name="id" id="id" />
                                <input type="hidden" name="filtro" id="filtro" value="vereda" />
                                <input type="hidden" name="filtroVeredaById" id="filtroVeredaById" value="si" />

                                <!-- GRID PRO -->
                                <!-- ===============================
     UBICACIÓN PRINCIPAL
================================ -->
<div class="section-block">

    <h6 class="section-title text-center">
        Ubicación del Registro
    </h6>

    <div class="row justify-content-center mb-4">

        <div class="col-md-3">
            <div class="form-group text-center">
                <label>Departamento <span class="text-danger">*</span></label>
                <select onchange="DEPARTAMENTO.getMunicipios();"
                    class="form-control input-small text-center"
                    id="tbl_departamento_id"
                    name="tbl_departamento_id">
                    <?php echo $optionDep; ?>
                </select>
            </div>
        </div>

        <div class="col-md-3">
            <div class="form-group text-center">
                <label>Municipio <span class="text-danger">*</span></label>
                <select class="form-control input-small text-center"
                    id="tbl_municipio_id"
                    onchange="DEPARTAMENTO.getVeredasByMunicipioId();"
                    name="tbl_municipio_id">
                </select>
            </div>
        </div>

        <div class="col-md-3">
            <div class="form-group text-center">
                <label>Vereda <span class="text-danger">*</span></label>
                <select class="form-control input-small text-center"
                    id="tbl_vereda_id"
                    name="tbl_vereda_id">
                </select>
            </div>
        </div>

    </div>

</div>


<!-- ===============================
     INGRESO FACTOR
================================ -->
<div class="section-block">

    <h6 class="section-title text-center mt-4">
        Ingreso Factor
    </h6>

    <div class="row mt-3">

        <div class="col-md-4">
            <div class="form-group">
                <label>Factores <span class="text-danger">*</span></label>
                <select class="form-control"
                    id="factorId"
                    name="factorId"
                    onchange="INGRESO_INFORMACION.showInfoGetFactores();">
                    <?php echo $optionFactores; ?>
                </select>
            </div>
        </div>

        <div class="col-md-2">
            <div class="form-group">
                <label>Valor <span class="text-danger">*</span></label>
                <input type="text"
                    class="form-control input-mini"
                    id="valor"
                    name="valor"
                    placeholder="Valor">
            </div>
        </div>

        <div class="col-md-3">
            <div class="form-group">
                <label>Longitud <span class="text-danger">*</span></label>
                <input type="text"
                    class="form-control"
                    id="longitud"
                    name="longitud">
            </div>
        </div>

        <div class="col-md-3">
            <div class="form-group">
                <label>Latitud <span class="text-danger">*</span></label>
                <input type="text"
                    class="form-control"
                    id="latitud"
                    name="latitud">
            </div>
        </div>

    </div>

</div>
                                    <!-- INFO FACTOR (NO tocar ids) -->
                                    <div class="saas-col-12">
                                        <div id="divInformacion" class="card-body" style="display:none;">
                                            <div class="row" style="margin:0; gap: 12px;">
                                                <div class="col-sm-3">
                                                    <label class="floating-label" for="eje">Eje</label>
                                                    <div class="form-group">
                                                        <input id="eje" name="eje" class="form-control" type="text" placeholder="" readonly="">
                                                    </div>
                                                </div>
                                                <div class="col-sm-3">
                                                    <label class="floating-label" for="pilar">Pilar</label>
                                                    <div class="form-group">
                                                        <input id="pilar" name="pilar" class="form-control" type="text" placeholder="" readonly="">
                                                    </div>
                                                </div>
                                                <div class="col-sm-3">
                                                    <label class="floating-label" for="area">Área</label>
                                                    <div class="form-group">
                                                        <input id="area" name="area" class="form-control" type="text" placeholder="" readonly="">
                                                    </div>
                                                </div>
                                                <div class="col-sm-3">
                                                    <label class="floating-label" for="tipo_medicion">Tipo Medición</label>
                                                    <div class="form-group">
                                                        <input id="tipo_medicion" name="tipo_medicion" class="form-control" type="text" placeholder="" readonly="">
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- OBS -->
                                    <div class="saas-col-12">
                                        <div class="form-group" style="text-align:center;">
                                            <label for="observaciones" style="display:block;">Observaciones</label>
                                            <input type="email" class="form-control" id="observaciones" name="observaciones" placeholder="" value="">
                                        </div>
                                    </div>

                                    <!-- ACTIONS -->
                                    <div class="saas-col-12">
                                        <div class="saas-actions">
                                            <button type="button" onclick="UTIL.clearForm('formingresoinformacion');" class="btn btn-danger">
                                                Cancelar
                                            </button>

                                            <button type="button" class="btn btn-primary saas-geo-btn" onclick="abrirModal();">
                                                <img src="assets/images/geoloca.png" alt="Geolocalización">
                                            </button>

                                            <button onclick="INGRESO_INFORMACION.save();" type="button" class="btn btn-primary">
                                                <i class="feather mr-2 icon-check-circle"></i>Guardar
                                            </button>
                                        </div>
                                    </div>

                                    <!-- UPLOADS -->
                                    <div class="saas-col-12">
                                        <div class="saas-upload-grid">

                                            <div class="saas-upload-item">
                                                <label for="ifm1">Foto 1 <small>Adjuntar</small></label>
                                                <div class="controls">
                                                    <iframe id='ifm1' name='ifm' src="upload.php" scrolling="no" frameborder="0"></iframe>
                                                </div>
                                            </div>

                                            <div class="saas-upload-item">
                                                <label for="ifm2">Foto 2 <small>Adjuntar</small></label>
                                                <div class="controls">
                                                    <iframe id='ifm2' name='ifm' src="upload.php" scrolling="no" frameborder="0"></iframe>
                                                </div>
                                            </div>

                                            <div class="saas-upload-item">
                                                <label for="ifm3">Foto 3 <small>Adjuntar</small></label>
                                                <div class="controls">
                                                    <iframe id='ifm3' name='ifm' src="upload.php" scrolling="no" frameborder="0"></iframe>
                                                </div>
                                            </div>

                                            <div class="saas-upload-item">
                                                <label for="ifm4">Foto 4 <small>Adjuntar</small></label>
                                                <div class="controls">
                                                    <iframe id='ifm4' name='ifm' src="upload.php" scrolling="no" frameborder="0"></iframe>
                                                </div>
                                            </div>

                                        </div>
                                    </div>

                                </div><!-- /grid -->

                            </div><!-- /card-body -->

                        </form>
                    </div><!-- /card -->

                </div>
            </div>
            <!-- [ Main Content ] end -->

        </div>
    </div>
    <!-- [ Main Content ] end -->

    <!-- MODAL GEO -->
    <div class="card-body">
        <div id="modalGeocalizacion" class="modal fade" tabindex="-1" role="dialog"
            aria-labelledby="modalGeocalizacionTitle" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="modalGeocalizacionTitle">Geolocalización</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>

                    <div class="modal-body">
                        <div id="map" style="height: 550px; width: 100%;"></div>

                        <div class="saas-map-tools">
                            <label><input type="checkbox" id="trafficLayerToggle"> Capa de Tráfico</label>
                            <label><input type="checkbox" id="transitLayerToggle"> Transporte Público</label>
                            <label><input type="checkbox" id="bicycleLayerToggle"> Bicicleta</label>
                            <label><input type="checkbox" id="terrainToggle"> Mostrar Terreno</label>
                        </div>

                        <div class="coordinates">
                            <strong>Latitud:</strong> <span id="lat">N/A</span> &nbsp; | &nbsp;
                            <strong>Longitud:</strong> <span id="lng">N/A</span>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Google Maps JavaScript API (dejar SOLO este, para que initMap ya exista) -->
        <script async defer
            src="https://maps.googleapis.com/maps/api/js?key=AIzaSyAbqZVEnSpIPF5dKe89pju2rmQaM58wvY0&callback=initMap">
        </script>

        <script>
            let map;
            let trafficLayer, transitLayer, bicycleLayer;
            let LATITUD = 7.10543;
            let LONGITUD = -73.122234;

            // Función para inicializar el mapa
            function initMap(lat, lng, icono = "assets/iconos/maps/geo.png") {
                if (typeof google !== 'undefined' && google.maps) {
                    if (lat !== undefined || lng !== undefined) {
                        LATITUD = +lat;
                        LONGITUD = +lng;
                    }

                    const initialLocation = { lat: LATITUD, lng: LONGITUD };

                    const map = new google.maps.Map(document.getElementById("map"), {
                        center: initialLocation,
                        zoom: 12,
                    });

                    map.addListener("click", (event) => {
                        const lat = event.latLng.lat();
                        const lng = event.latLng.lng();

                        $("#latitud").val(lat);
                        $("#longitud").val(lng);

                        document.getElementById("lat").innerText = lat.toFixed(6);
                        document.getElementById("lng").innerText = lng.toFixed(6);

                        const iconUrl = icono;

                        new google.maps.Marker({
                            position: event.latLng,
                            map: map,
                            icon: iconUrl,
                        });
                    });

                    const trafficLayer = new google.maps.TrafficLayer();
                    const transitLayer = new google.maps.TransitLayer();
                    const bicycleLayer = new google.maps.BicyclingLayer();

                    const toggleLayer = (layer, isChecked) => {
                        layer.setMap(isChecked ? map : null);
                    };

                    document.getElementById("trafficLayerToggle").addEventListener("change", (e) => {
                        toggleLayer(trafficLayer, e.target.checked);
                    });
                    document.getElementById("transitLayerToggle").addEventListener("change", (e) => {
                        toggleLayer(transitLayer, e.target.checked);
                    });
                    document.getElementById("bicycleLayerToggle").addEventListener("change", (e) => {
                        toggleLayer(bicycleLayer, e.target.checked);
                    });
                    document.getElementById("terrainToggle").addEventListener("change", (e) => {
                        map.setMapTypeId(e.target.checked ? "terrain" : "roadmap");
                    });

                } else {
                    console.error('Google Maps API no está disponible.');
                }
            }

            function abrirModal() {
                const msj = "Debes seleccionar todas la opciones para poder abrir la geocalización";
                const camposRequeridos = ["#tbl_departamento_id", "#tbl_municipio_id", "#tbl_vereda_id", "#factorId"];

                if (!UTIL.validarCampos(camposRequeridos)) {
                    UTIL.mostrarMensajeValidacion(msj);
                    return;
                }

                if (informacionMunicipio.latitud && informacionMunicipio.longitud) {
                    const latitud = informacionMunicipio.latitud === undefined ? LATITUD : informacionMunicipio.latitud;
                    const longitud = informacionMunicipio.longitud === undefined ? LONGITUD : informacionMunicipio.longitud;
                    const factorClass = $("#factorId").find(":selected").attr("class");

                    initMap(latitud, longitud, factorClass);
                }

                setTimeout(function() {
                    $('#modalGeocalizacion').modal();
                }, 1000);
            }
        </script>
    </div>

    <?php include 'admin/include/gerenic_script.php'; ?>
    <!-- Required Js -->
    <script src="assets/js/vendor-all.min.js"></script>
    <script src="assets/js/plugins/bootstrap.min.js"></script>
    <script src="assets/js/pcoded.min.js"></script>

    <script type="text/javascript" src="admin/js/departamento.js"></script>
    <script type="text/javascript" src="admin/js/ingreso_informacion.js"></script>
    <script>
        setTimeout(function() {
            DEPARTAMENTO.getMunicipios();
        }, 500);
    </script>
</body>
</html>