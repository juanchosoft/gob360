<?php
include './admin/include/head.php';

require './admin/include/generic_classes.php';
include './admin/classes/Bienes.php';
include './admin/classes/Secretarias.php';
include './admin/classes/Departamento.php';

// Permisos RBAC
extract(PagePermissions::crudVarsForCurrentPage());
/* if (!$view) {
    require 'permiso_denegado.php';
} */
$userType = SessionData::getUserType();
$isAdmin = ($userType === Util::Administrador() || $userType === Util::SuperAdministrador());
$isSecretario = ($userType === Util::Secretario_Despacho() || $userType === Util::Auxiliar() || $userType == Util::Auxiliar_secret_gob());
$isAdministrativo = ( intval(Util::getSecretariaAdministrativa()) === intval(SessionData::getSecretaria()));

if(!$isAdmin){
    if (!$isAdministrativo) {
        require 'permiso_denegado.php';
        exit;
    }
}

// Información de Departamentos
$arrDep = Departamento::getAll(null);
$isvalid = $arrDep['output']['valid'];
$arrDep = $arrDep['output']['response'];
$optionDep = Util::getDepartamentoPrincipal();
foreach ($arrDep as $val) {
    $optionDep .= "<option " . ($val["codigo_departamento"] == Util::getDepartamentoPrincipal() ? "selected" : "") . " value='" . $val['codigo_departamento'] . "'>" . $val['codigo_departamento'] . " - " . $val['departamento'] . "</option>";
}

// Informacion de Bienes
$arr = Bienes::getAll(null);
$isvalidBienes = $arr['output']['valid'];
$arr = $arr['output']['response'];

// Informacion de los pilares
$arrSecret = Secretarias::getAll(null);
$isvalid = $arrSecret['output']['valid'];
$arrSecret = $arrSecret['output']['response'];
// Mostrar solo la secretaría con id=3 Administrativa
$optionSecretarias = "";
foreach ($arrSecret as $val) {
    if ($val['id'] == Util::getSecretariaAdministrativa()) {
        $optionSecretarias = "<option selected value='" . $val['id'] . "'>" . $val['secretaria'] . "</option>";
        break;
    }
}
?>
<style>
  :root{
    --nav-blue:#20427F;
    --nav-blue-2:#132b52;
    --nav-blue-3:#2e58a8;

    --white:#fff;
    --ink:#0f172a;
    --muted:#64748b;

    --card:#ffffff;
    --soft:#f6f8ff;

    --radius-xl:22px;
    --radius-lg:16px;
    --radius-md:12px;

    --shadow-soft: 0 12px 30px rgba(2, 6, 23, .12);
    --shadow-mid:  0 18px 40px rgba(2, 6, 23, .18);

    --ring: 0 0 0 .22rem rgba(46, 88, 168, .22);
  }

  /* ====== Fix: separación vs header ====== */
  .pcoded-main-container{ padding-top: 0 !important; }
  .pcoded-content{ padding-top: 18px !important; }

  /* ====== Hero / breadcrumb estilo SaaS ====== */
  .page-header .page-block{
    background: radial-gradient(1200px 300px at 10% 0%, rgba(46,88,168,.28), transparent 55%),
                linear-gradient(135deg, rgba(32,66,127,.10), rgba(19,43,82,.06));
    border: 1px solid rgba(32,66,127,.10);
    border-radius: var(--radius-xl);
    box-shadow: var(--shadow-soft);
    padding: 18px 18px;
  }
  .page-header h5{
    color: var(--ink);
    font-weight: 800;
    letter-spacing: .2px;
  }
  .page-header .breadcrumb{
    background: transparent;
    margin-bottom: 0;
    padding: 0;
  }
  .page-header .breadcrumb-item a{ color: var(--muted); }
  .page-header .breadcrumb-item.active{ color: var(--ink); }
  .page-header .feather{ color: var(--nav-blue); }

  /* ====== Tabs pro (pill) ====== */
  .nav-tabs{
    border: 0 !important;
    gap: 10px;
    margin-top: 14px;
  }
  .nav-tabs .nav-link{
    border: 1px solid rgba(15, 23, 42, .10) !important;
    border-radius: 999px !important;
    padding: .55rem 1rem;
    color: var(--muted);
    background: #fff;
    box-shadow: 0 8px 18px rgba(2,6,23,.06);
    transition: all .18s ease;
    font-weight: 700;
  }
  .nav-tabs .nav-link:hover{
    transform: translateY(-1px);
    border-color: rgba(32,66,127,.22) !important;
  }
  .nav-tabs .nav-link.active{
    color: #fff !important;
    background: linear-gradient(135deg, var(--nav-blue), var(--nav-blue-2)) !important;
    border-color: transparent !important;
    box-shadow: 0 14px 26px rgba(19,43,82,.22);
  }

  /* ====== Cards premium ====== */
  .card{
    border: 1px solid rgba(15,23,42,.10) !important;
    border-radius: var(--radius-xl) !important;
    box-shadow: var(--shadow-soft);
    overflow: hidden;
  }
  .card-header{
    background: linear-gradient(135deg, rgba(32,66,127,.10), rgba(19,43,82,.06)) !important;
    border-bottom: 1px solid rgba(15,23,42,.08) !important;
  }
  .card-header h5{
    font-weight: 800;
    color: var(--ink);
    letter-spacing: .2px;
  }
  .card-body.m-4{ margin: 0 !important; padding: 18px !important; }

  /* ====== Form UI pro ====== */
  label{
    font-weight: 800;
    color: var(--ink);
    margin-bottom: .35rem;
  }
  .form-control{
    border-radius: 14px !important;
    border: 1px solid rgba(15,23,42,.12) !important;
    padding: .65rem .85rem;
    box-shadow: none !important;
    transition: all .15s ease;
    background: #fff;
  }
  .form-control:focus{
    border-color: rgba(46,88,168,.55) !important;
    box-shadow: var(--ring) !important;
  }
  textarea.form-control{ min-height: 92px; }

  .soft-panel{
    background: radial-gradient(900px 200px at 20% 0%, rgba(32,66,127,.10), transparent 60%),
                linear-gradient(135deg, rgba(15,23,42,.02), rgba(15,23,42,.00));
    border: 1px dashed rgba(32,66,127,.22);
    border-radius: var(--radius-xl);
    padding: 14px;
  }

  /* ====== Botones pro ====== */
  .btn{
    border-radius: 14px !important;
    font-weight: 800;
    letter-spacing: .2px;
  }
  .btn-primary{
    background: linear-gradient(135deg, var(--nav-blue), var(--nav-blue-2)) !important;
    border: none !important;
    box-shadow: 0 12px 22px rgba(19,43,82,.22);
  }
  .btn-primary:hover{ transform: translateY(-1px); }
  .btn-danger{
    border: none !important;
    box-shadow: 0 12px 22px rgba(239,68,68,.18);
  }
  .btn-secondary{
    border: 1px solid rgba(15,23,42,.12) !important;
    background: #fff !important;
    color: var(--ink) !important;
  }

  /* ====== Tile geolocalización ====== */
  .geo-tile{
    width: 100%;
    height: 84px;
    border-radius: 18px;
    border: 1px solid rgba(32,66,127,.18);
    background: linear-gradient(135deg, rgba(32,66,127,.12), rgba(19,43,82,.06));
    box-shadow: 0 14px 26px rgba(2,6,23,.10);
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 10px;
    transition: all .18s ease;
  }
  .geo-tile:hover{ transform: translateY(-1px); }
  .geo-tile img{
    width: 42px;
    height: 42px;
    object-fit: contain;
    filter: drop-shadow(0 10px 20px rgba(19,43,82,.18));
  }
  .geo-tile span{
    font-weight: 900;
    color: var(--ink);
    line-height: 1.05;
  }
  .geo-sub{
    font-size: 12px;
    color: var(--muted);
    font-weight: 700;
  }

  /* ====== Upload iframes más limpios ====== */
  iframe{
    border-radius: 14px;
    border: 1px solid rgba(15,23,42,.12);
    background: #fff;
  }

  /* ====== Tabla premium (compacta) ====== */
  .table-responsive{ border-radius: var(--radius-xl); overflow: hidden; }
  table.table{
    margin-bottom: 0;
    font-size: 12px; /* letra más pequeña */
  }
  table.table thead th{
    position: sticky;
    top: 0;
    z-index: 2;
    background: linear-gradient(135deg, var(--nav-blue), var(--nav-blue-2));
    color: #fff;
    font-weight: 900;
    border-bottom: 0 !important;
    white-space: nowrap;
  }
  table.table td{
    vertical-align: middle !important;
    color: #0f172a;
  }
  .table-striped tbody tr:nth-of-type(odd){
    background: rgba(246,248,255,.55);
  }

  /* acciones compactas */
  .btn-ico{
    width: 34px;
    height: 34px;
    padding: 0 !important;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    border-radius: 12px !important;
  }

  .photo-links a{
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 28px;
    height: 28px;
    border-radius: 10px;
    border: 1px solid rgba(32,66,127,.18);
    background: #fff;
    margin-right: 6px;
    box-shadow: 0 10px 18px rgba(2,6,23,.08);
  }
  .photo-links i{ color: var(--nav-blue); }

  /* ====== Modal mapa estilo header ====== */
  #modalGeocalizacion .modal-content{
    border: 0 !important;
    border-radius: 22px !important;
    overflow: hidden;
    box-shadow: var(--shadow-mid);
  }
  #modalGeocalizacion .modal-header{
    background: linear-gradient(135deg, var(--nav-blue), var(--nav-blue-2)) !important;
    color: #fff !important;
    border-bottom: 0 !important;
  }
  #modalGeocalizacion .modal-title{ font-weight: 900; }
  #modalGeocalizacion .close{ color: #fff; opacity: .95; }
  #modalGeocalizacion .modal-body{
    background: radial-gradient(1000px 240px at 10% 0%, rgba(32,66,127,.12), transparent 60%),
                #fff;
  }
  #modalGeocalizacion .controls{
    display:flex;
    flex-wrap:wrap;
    gap: 10px 14px;
    margin-top: 12px;
    padding: 12px;
    border-radius: 16px;
    border: 1px solid rgba(15,23,42,.10);
    background: rgba(246,248,255,.65);
  }
  #modalGeocalizacion .controls label{
    display:flex;
    align-items:center;
    gap: 8px;
    font-weight: 800;
    color: var(--ink);
    margin: 0;
  }
  #modalGeocalizacion .coordinates{
    margin-top: 12px;
    padding: 12px;
    border-radius: 16px;
    border: 1px solid rgba(15,23,42,.10);
    background: #fff;
    font-weight: 800;
    color: var(--ink);
  }

  /* ====== pequeños ajustes responsive ====== */
  @media (max-width: 992px){
    .card-body.m-4{ padding: 14px !important; }
    .page-header .page-block{ padding: 14px; }
  }
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

  <!-- [ Main Content ] start -->
  <div class="pcoded-main-container">
    <div class="pcoded-content">

      <!-- [ breadcrumb ] start -->
      <div class="page-header">
        <div class="page-block">
          <div class="row align-items-center">
            <div class="col-md-12">
              <div class="d-flex flex-wrap justify-content-between align-items-center" style="gap:10px;">
                <div>
                  <h5 class="m-b-10 mb-1">Información administrativa</h5>
                  <div class="text-muted" style="font-weight:700; font-size:12px;">
                    Gestión de bienes inmuebles • control, responsables y geolocalización
                  </div>
                </div>
                <div class="ml-auto">
                  <?php include './admin/include/btn_back.php'; ?>
                </div>
              </div>

              <ul class="breadcrumb mt-2">
                <li class="breadcrumb-item">
                  <a href="index.php"><i class="feather icon-home"></i></a>
                </li>
                <li class="breadcrumb-item">
                  <a href="#!">Configuración General / Información administrativa</a>
                </li>
              </ul>
            </div>
          </div>
        </div>
      </div>

      <!-- Tabs -->
      <ul class="nav nav-tabs" id="myTab" role="tablist">
        <li class="nav-item" role="presentation">
          <button class="nav-link active" id="home-tab" data-toggle="tab" data-target="#home" type="button"
            role="tab" aria-controls="home" aria-selected="true" onclick="emptyDataForm();">
            Ingresar Información
          </button>
        </li>
        <li class="nav-item" role="presentation">
          <button class="nav-link" id="profile-tab" data-toggle="tab" data-target="#profile" type="button"
            role="tab" aria-controls="profile" aria-selected="false">
            Listado
          </button>
        </li>
      </ul>

      <div class="tab-content" id="myTabContent">
        <!-- TAB 1 -->
        <div class="tab-pane fade show active" id="home" role="tabpanel" aria-labelledby="home-tab">
          <div class="card mt-3">
            <div class="card-header d-flex flex-wrap align-items-center justify-content-between py-3">
              <h5 class="mb-0 text-center w-100">Formulario Información administrativa</h5>
              <div class="card-header-right ml-auto">
                <div class="btn-group card-option">
                  <button type="button" class="btn dropdown-toggle btn-icon" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    <i class="feather icon-more-horizontal"></i>
                  </button>
                  <ul class="list-unstyled card-option dropdown-menu dropdown-menu-right">
                    <li class="dropdown-item full-card">
                      <a href="#!">
                        <span><i class="feather icon-maximize"></i> Maximizar</span>
                        <span style="display:none"><i class="feather icon-minimize"></i> Restaurar</span>
                      </a>
                    </li>
                    <li class="dropdown-item minimize-card">
                      <a href="#!">
                        <span><i class="feather icon-minus"></i> Colapsar</span>
                        <span style="display:none"><i class="feather icon-plus"></i> Expandir</span>
                      </a>
                    </li>
                  </ul>
                </div>
              </div>
            </div>

            <div class="card-body m-4">
              <form id="formbienes" role="form" autocomplete="false" enctype="multipart/form-data">
                <input type="hidden" name="op" id="op" />
                <input type="hidden" name="idBienes" id="idBienes" />

                <div class="soft-panel mb-3">
                  <div class="row">
                    <div class="col-12" style="font-weight:900;color:var(--ink);">
                      Datos de control
                      <div class="text-muted" style="font-weight:700;font-size:12px;">Completa los campos obligatorios para registrar el bien.</div>
                    </div>
                  </div>
                </div>

                <div class="form-row">
                  <div class="form-group col-md-4">
                    <label for="codigo_control">Código de Control <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="codigo_control" name="codigo_control" placeholder="Ej: ADM-000123" value="" required>
                  </div>
                  <div class="form-group col-md-4">
                    <label for="calcomania">Calcomanía <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="calcomania" name="calcomania" placeholder="Ej: CAL-98765" value="" required>
                  </div>
                  <div class="form-group col-md-4">
                    <label for="costo_unitario">Costo Unitario <span class="text-danger">*</span></label>
                    <input onKeyPress="return soloNumeros(event);" type="text" class="form-control" id="costo_unitario" name="costo_unitario" placeholder="Ej: 1500000" value="" required>
                  </div>
                </div>

                <div class="form-row">
                  <div class="form-group col-md-4">
                    <label for="tbl_departamento_id">Departamento <span class="text-danger">*</span></label>
                    <select readonly class="form-control ocultar-select" style="width: 100%;" onchange="DEPARTAMENTO.getMunicipios();" id="tbl_departamento_id" name="tbl_departamento_id">
                      <?php echo $optionDep; ?>
                    </select>
                  </div>

                  <div class="form-group col-md-4">
                    <label for="tbl_municipio_id">Municipio <span class="text-danger">*</span></label>
                    <select class="form-control" style="width: 100%;" onchange="DEPARTAMENTO.getInformacionDeMunicipioByIdMunicipio(this.value);" id="tbl_municipio_id" name="tbl_municipio_id"></select>
                  </div>

                  <div class="form-group col-md-4">
                    <label for="secretaria">Secretaría <span class="text-danger">*</span></label>
                    <select readonly class="form-control" id="secretaria" name="secretaria">
                      <?php echo $optionSecretarias; ?>
                    </select>
                  </div>
                </div>

                <div class="form-row">
                  <div class="form-group col-md-4">
                    <label for="longitud">Longitud <span class="text-danger">*</span></label>
                    <input type="email" class="form-control" id="longitud" name="longitud" readonly placeholder="Se autocompleta con el municipio" value="">
                  </div>
                  <div class="form-group col-md-4">
                    <label for="latitud">Latitud <span class="text-danger">*</span></label>
                    <input type="email" class="form-control" id="latitud" name="latitud" readonly placeholder="Se autocompleta con el municipio" value="">
                  </div>

                  <div class="form-group col-md-4 d-flex align-items-end">
                    <button type="button" class="geo-tile" onclick="abrirModal();" title="Abrir geolocalización">
                      <img src="assets/images/geoloca.png" alt="Geolocalización">
                      <div class="text-left">
                        <span>Geolocalizar</span><br>
                        <span class="geo-sub">Mapa • capas • coordenadas</span>
                      </div>
                    </button>
                  </div>
                </div>

                <div class="soft-panel mb-3">
                  <div class="row">
                    <div class="col-12" style="font-weight:900;color:var(--ink);">
                      Responsable y descripción
                      <div class="text-muted" style="font-weight:700;font-size:12px;">Asigna dependencia, identificación y responsable del bien.</div>
                    </div>
                  </div>
                </div>

                <div class="form-row">
                  <div class="form-group col-md-4">
                    <label for="dependencia">Dependencia <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="dependencia" name="dependencia" placeholder="Ej: Archivo, Jurídica, Sistemas..." value="">
                  </div>
                  <div class="form-group col-md-4">
                    <label for="cedula_o_nit">Cédula o Nit <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="cedula_o_nit" name="cedula_o_nit" placeholder="Ej: 900123456-7" value="">
                  </div>
                  <div class="form-group col-md-4">
                    <label for="responsable">Responsable <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="responsable" name="responsable" placeholder="Nombre completo" value="">
                  </div>
                </div>

                <div class="form-row">
                  <div class="form-group col-md-12">
                    <label for="nombre_articulo">Nombre del Artículo <span class="text-danger">*</span></label>
                    <textarea class="form-control" id="nombre_articulo" name="nombre_articulo" placeholder="Describe el artículo de forma clara..." rows="3" required></textarea>
                  </div>
                </div>

                <div class="form-row">
                  <div class="form-group col-md-12">
                    <label for="observacion">Observación</label>
                    <textarea class="form-control" id="observacion" name="observacion" placeholder="Observaciones adicionales (opcional)..." rows="3"></textarea>
                  </div>
                </div>

                <div class="soft-panel mb-3">
                  <div class="row">
                    <div class="col-12" style="font-weight:900;color:var(--ink);">
                      Evidencias fotográficas
                      <div class="text-muted" style="font-weight:700;font-size:12px;">Carga hasta 4 fotos del bien (si aplica).</div>
                    </div>
                  </div>
                </div>

                <div class="form-row">
                  <div class="form-group col-md-3">
                    <label for="">Foto 1</label>
                    <div class="controls">
                      <iframe id='ifm1' name='ifm' src="upload.php" width="200" height="60" scrolling="no" frameborder="0"></iframe>
                    </div>
                  </div>
                  <div class="form-group col-md-3">
                    <label for="">Foto 2</label>
                    <div class="controls">
                      <iframe id='ifm2' name='ifm' src="upload.php" width="200" height="60" scrolling="no" frameborder="0"></iframe>
                    </div>
                  </div>
                  <div class="form-group col-md-3">
                    <label for="">Foto 3</label>
                    <div class="controls">
                      <iframe id='ifm3' name='ifm' src="upload.php" width="200" height="60" scrolling="no" frameborder="0"></iframe>
                    </div>
                  </div>
                  <div class="form-group col-md-3">
                    <label for="">Foto 4</label>
                    <div class="controls">
                      <iframe id='ifm4' name='ifm' src="upload.php" width="200" height="60" scrolling="no" frameborder="0"></iframe>
                    </div>
                  </div>
                </div>

                <div class="form-row pt-3">
                  <div class="col text-center">
                    <button type="button" onclick="UTIL.clearForm('formbienes');" class="btn btn-danger mr-2">
                      Cancelar
                    </button>
                    <button type="button" id="createBienes" onclick="BIENES.validateData();" class="btn btn-primary">
                      Guardar
                    </button>
                  </div>
                </div>

              </form>
            </div>
          </div>
        </div>

        <!-- TAB 2 -->
        <div class="tab-pane fade" id="profile" role="tabpanel" aria-labelledby="profile-tab">
          <div class="card mt-3">
            <div class="card-header d-flex flex-wrap align-items-center justify-content-between py-3">
              <h5 class="mb-0 text-center w-100">Listado Relación Bienes Inmuebles</h5>
              <div class="card-header-right ml-auto">
                <div class="btn-group card-option">
                  <button type="button" class="btn dropdown-toggle btn-icon" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    <i class="feather icon-more-horizontal"></i>
                  </button>
                  <ul class="list-unstyled card-option dropdown-menu dropdown-menu-right">
                    <li class="dropdown-item full-card">
                      <a href="#!">
                        <span><i class="feather icon-maximize"></i> Maximizar</span>
                        <span style="display:none"><i class="feather icon-minimize"></i> Restaurar</span>
                      </a>
                    </li>
                    <li class="dropdown-item minimize-card">
                      <a href="#!">
                        <span><i class="feather icon-minus"></i> Colapsar</span>
                        <span style="display:none"><i class="feather icon-plus"></i> Expandir</span>
                      </a>
                    </li>
                    <li class="dropdown-item reload-card">
                      <a href="#!"><i class="feather icon-refresh-cw"></i> Recargar</a>
                    </li>
                    <li class="dropdown-item close-card">
                      <a href="#!"><i class="feather icon-trash"></i> Eliminar</a>
                    </li>
                  </ul>
                </div>
              </div>
            </div>

            <div class="card-body table-border-style">
              <div class="table-responsive tabla-informacion tabla-scroll">
                <table id="dynamictable" class="table table-striped table-bordered" style="width:100%">
                  <thead>
                    <tr class="border-1">
                      <th style="min-width:90px;">Acciones</th>
                      <th>Municipio</th>
                      <th>Secretaría</th>
                      <th>Código de Control</th>
                      <th>Calcomanía</th>
                      <th style="min-width:260px;">Nombre del Artículo</th>
                      <th>Costo Unitario</th>
                      <th>Dependencia</th>
                      <th>Cédula o Nit</th>
                      <th>Responsable</th>
                      <th style="min-width:110px;">Fotos</th>
                    </tr>
                  </thead>
                  <tbody class="list">
                    <?php if ($isvalidBienes && count($arr) > 0): ?>
                      <?php foreach ($arr as $item): ?>
                        <tr>
                          <td>
                            <button type="button" class="btn btn-primary btn-ico" title="Editar"
                              onclick="BIENES.editData(<?= htmlspecialchars($item['id']) ?>)">
                              <i class="feather icon-edit"></i>
                            </button>
                          </td>
                          <td><?= htmlspecialchars($item['nombre_municipio']) ?></td>
                          <td><?= htmlspecialchars($item['nombre_secretaria']) ?></td>
                          <td><?= htmlspecialchars($item['codigo_control']) ?></td>
                          <td><?= htmlspecialchars($item['calcomania']) ?></td>
                          <td><?= htmlspecialchars($item['nombre_articulo']) ?></td>
                          <td><?= htmlspecialchars(number_format($item['costo_unitario'], 2)) ?></td>
                          <td><?= htmlspecialchars($item['dependencia']) ?></td>
                          <td><?= htmlspecialchars($item['cedula_o_nit']) ?></td>
                          <td><?= htmlspecialchars($item['responsable']) ?></td>
                          <td class="photo-links">
                            <?php for ($i = 1; $i <= 4; $i++): ?>
                              <?php if (!empty($item["img$i"])): ?>
                                <a href="<?= htmlspecialchars($item["img$i"]) ?>" target="_blank" title="Imagen <?= $i ?>">
                                  <i class="feather icon-image"></i>
                                </a>
                              <?php endif; ?>
                            <?php endfor; ?>
                          </td>
                        </tr>
                      <?php endforeach; ?>
                    <?php endif; ?>
                  </tbody>
                </table>
              </div>
            </div>

          </div>
        </div>

      </div>
    </div>

    <!-- Modal de geocalizacion de bienes -->
    <div class="card-body">
      <div id="modalGeocalizacion" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="modalGeocalizacionTitle" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-centered" role="document">
          <div class="modal-content">
            <div class="modal-header">
              <h5 class="modal-title" id="modalGeocalizacionTitle">Geolocalización</h5>
              <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
              </button>
            </div>
            <div class="modal-body">
              <div id="map" style="height: 550px; width: 100%; border-radius: 18px; overflow:hidden; border:1px solid rgba(15,23,42,.10);"></div>
              <div class="controls">
                <label><input type="checkbox" id="trafficLayerToggle"> Capa de Tráfico</label>
                <label><input type="checkbox" id="transitLayerToggle"> Capa de Transporte Público</label>
                <label><input type="checkbox" id="bicycleLayerToggle"> Capa de Bicicleta</label>
                <label><input type="checkbox" id="terrainToggle"> Mostrar Terreno</label>
              </div>
              <div class="coordinates">
                <strong>Latitud:</strong> <span id="lat">N/A</span> &nbsp;|&nbsp;
                <strong>Longitud:</strong> <span id="lng">N/A</span>
              </div>
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
            </div>
          </div>
        </div>
      </div>
    </div>

  </div>

  <!-- Required Js -->
  <?php include 'admin/include/gerenic_script.php'; ?>

  <script src="assets/js/vendor-all.min.js"></script>
  <script src="assets/js/plugins/bootstrap.min.js"></script>
  <script src="assets/js/pcoded.min.js"></script>

  <!-- Google Maps JavaScript API -->
  <script async defer src="https://maps.googleapis.com/maps/api/js?key=AIzaSyAbqZVEnSpIPF5dKe89pju2rmQaM58wvY0&callback=initMap"></script>

  <script type="text/javascript" src="admin/js/departamento.js"></script>
  <script type="text/javascript" src="admin/js/bienes.js"></script>
  <script type="text/javascript" src="admin/js/geocalizacion_bienes.js"></script>

  <script type="text/javascript" src="./admin/js/datatables/jquery.dataTables.min.js"></script>
  <link href="./admin/js/datatables/jquery.dataTables.min.css" rel="stylesheet" />

  <script>
    setTimeout(function() {
      DEPARTAMENTO.getMunicipios();
    }, 100);
  </script>

  <?php include './admin/include/generic_dataTables.php'; ?>
</body>
</html>
