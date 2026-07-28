<?php
include './admin/include/head.php';
include './admin/include/dark_theme.php';
require './admin/include/generic_classes.php';

// Permisos RBAC
extract(PagePermissions::crudVarsForCurrentPage());

//Validación
/* if (!$view) {
    require 'permiso_denegado.php';
} */

include './admin/classes/Departamento.php';
include './admin/classes/Provincias.php';
include './admin/classes/Secretarias.php';
include './admin/classes/ComponenteMunicipios.php';
include './admin/classes/SecretariasMunicipios.php';

$codigoMunicipio = SessionData::getCodigoMunicipio();
$componentesArr = ComponenteMunicipios::getComponentesPorMunicipio($codigoMunicipio)['output']['response'] ?? [];
$optionComponentes = '<option value="">Seleccione</option>';
foreach ($componentesArr as $comp) {
    $nombre = is_string($comp) ? $comp : ($comp['nombre_componente'] ?? '');
    if (!empty($nombre)) {
        $optionComponentes .= '<option value="' . htmlspecialchars($nombre) . '">' . htmlspecialchars($nombre) . '</option>';
    }
}

$modulo = 'Registro Visitas';

// Secretarías del municipio del usuario
$optionSec = '<option value="">Seleccione</option>';
if (!empty($codigoMunicipio)) {
    $arrSecMun = SecretariasMunicipios::getByMunicipio(['codigo_municipio' => $codigoMunicipio]);
    $secRows = $arrSecMun['output']['response'] ?? [];
    foreach ($secRows as $s) {
        $optionSec .= '<option value="' . $s['id'] . '">' . htmlspecialchars($s['secretaria']) . '</option>';
    }
}

// Información de Departamentos
$arrDep   = Departamento::getAll(null);
$isvalid  = $arrDep['output']['valid'];
$arrDep   = $arrDep['output']['response'];
$optionDep = Util::getDepartamentoPrincipal();
foreach ($arrDep as $val) {
  $optionDep .= "<option value='" . $val['codigo_departamento'] . "'>" . $val['codigo_departamento'] . " - " . $val['departamento'] . "</option>";
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">

  <style>
    :root{
      --au-primary:#20427F;
      --au-primary-dark:#132b52;

      --au-ink:#0f172a;
      --au-muted:#64748b;

      --au-radius-xl:22px;
      --au-radius-lg:16px;

      --au-border:1px solid rgba(255,255,255,.12);
      --au-shadow-soft: 0 10px 30px rgba(0,0,0,.25);
      --au-shadow-mid: 0 18px 45px rgba(0,0,0,.35);

      /* ✅ evita que el header fijo tape el título */
      --safe-top: 96px;

      --ring: 0 0 0 .25rem rgba(46,88,168,.35);
    }

    /* ✅ FONDO DARK GRADIENT (igual al de la imagen) */
    body.dashboard-body{
      background:
        radial-gradient(900px 360px at 50% 115%, rgba(12, 35, 39, .95) 0%, rgba(12, 35, 39, 0) 55%),
        linear-gradient(135deg,
          #0b1221 0%,
          #0a1b24 35%,
          #0c2327 50%,
          #0b1321 75%,
          #0a1121 100%
        );
      color: rgba(255,255,255,.92);
    }

    .pcoded-content{
      padding: calc(var(--safe-top) + 16px) 16px 16px !important;
    }
    @media (min-width:768px){
      :root{ --safe-top: 112px; }
      .pcoded-content{ padding: calc(var(--safe-top) + 18px) 24px 24px !important; }
    }
    @media (min-width:1200px){
      :root{ --safe-top: 120px; }
      .pcoded-content{ padding: calc(var(--safe-top) + 22px) 42px 34px !important; }
    }

    /* Breadcrumb header PRO (dark glass) */
    .page-header .page-block{
      background: rgba(255,255,255,.06);
      border: var(--au-border);
      border-radius: var(--au-radius-xl);
      box-shadow: var(--au-shadow-soft);
      padding: 16px 16px;
      backdrop-filter: blur(10px);
    }
    .page-header h5{
      font-weight:1000 !important;
      color: rgba(255,255,255,.95);
      margin: 0;
    }
    .help-mini{
      font-size:.82rem;
      color: rgba(255,255,255,.70) !important;
    }
    .breadcrumb{
      background: transparent !important;
      padding: 0;
      margin: .35rem 0 0 !important;
    }
    .breadcrumb a,
    .breadcrumb-item,
    .breadcrumb-item a{
      color: rgba(255,255,255,.85) !important;
    }
    .breadcrumb-item.active{
      color: rgba(255,255,255,.65) !important;
    }

    /* Card SaaS (glass dark) */
    .au-card{
      background: rgba(255,255,255,.06);
      border: var(--au-border) !important;
      border-radius: var(--au-radius-xl) !important;
      box-shadow: var(--au-shadow-mid) !important;
      overflow: hidden;
      backdrop-filter: blur(10px);
    }
    .au-card .card-header{
      background: rgba(255,255,255,.06);
      border-bottom: 1px solid rgba(255,255,255,.12) !important;
      padding: 16px 18px !important;
      color: rgba(255,255,255,.92);
    }
    .au-card .card-header .title{
      font-weight:1000;
      color: rgba(255,255,255,.95);
      margin:0;
      font-size: 1.05rem;
    }
    .au-card .card-header .sub{
      margin: 6px 0 0;
      color: rgba(255,255,255,.70);
      font-size: .9rem;
    }
    .au-card .card-body{
      padding: 18px !important;
      color: rgba(255,255,255,.92);
    }

    /* Accent bar */
    .au-accent::before{
      content:"";
      position:absolute;
      top:0; left:0;
      width:100%;
      height:4px;
      background: linear-gradient(90deg, var(--au-primary), rgba(32,66,127,.35));
    }

    /* Labels en blanco */
    label{
      font-weight: 800;
      color: rgba(255,255,255,.92) !important;
      margin-bottom: 6px;
    }

    /* Inputs/Selects dark + texto blanco */
    .form-control, .custom-select, select.form-control, input.form-control, textarea.form-control{
      border-radius: 14px !important;
      border: 1px solid rgba(255,255,255,.14) !important;
      min-height: 44px;
      box-shadow: none !important;
      background: rgba(10,17,33,.55) !important;
      color: rgba(255,255,255,.95) !important;
      transition: box-shadow .15s ease, transform .15s ease, border-color .15s ease;
    }
    .form-control::placeholder{
      color: rgba(255,255,255,.55) !important;
    }
    .form-control:focus{
      border-color: rgba(255,255,255,.22) !important;
      box-shadow: var(--ring) !important;
      transform: translateY(-1px);
    }

    /* Required asterisco visible */
    .text-danger{ font-weight: 900; }

    /* Section badge (dark) */
    .au-badge{
      padding: 6px 10px;
      border-radius: 999px;
      font-size: .78rem;
      font-weight: 900;
      background: rgba(255,255,255,.08);
      color: rgba(255,255,255,.90);
      border: 1px solid rgba(255,255,255,.14);
      white-space: nowrap;
    }

    /* Form grid spacing */
    .au-form-grid .form-group{
      margin-bottom: 14px;
    }

    /* File input oscuro */
    .form-control-file{
      width: 100%;
      border-radius: 14px;
      border: 1px solid rgba(255,255,255,.14);
      padding: 10px 12px;
      background: rgba(10,17,33,.55);
      color: rgba(255,255,255,.85);
    }

    /* Preview box dark */
    #previewImage{
      border-radius: 16px;
      border: 1px dashed rgba(255,255,255,.18);
      padding: 10px;
      background: rgba(255,255,255,.04);
      min-height: 58px;
      color: rgba(255,255,255,.75);
    }

    /* Primary button SaaS */
    .btn-au-primary{
      border: none !important;
      border-radius: 999px !important;
      padding: 11px 18px !important;
      font-weight: 950 !important;
      letter-spacing: .2px;
      background: linear-gradient(135deg, var(--au-primary), var(--au-primary-dark)) !important;
      box-shadow: 0 14px 28px rgba(32,66,127,.22);
      transition: transform .15s ease, box-shadow .15s ease, filter .15s ease;
      min-width: 190px;
      color:#fff !important;
    }
    .btn-au-primary:hover{
      transform: translateY(-1px);
      filter: brightness(1.03);
      box-shadow: 0 18px 40px rgba(32,66,127,.28);
    }
    .btn-au-primary:active{
      transform: translateY(0px);
      box-shadow: 0 10px 22px rgba(32,66,127,.22);
    }

    /* Dropdown (3 punticos) dark */
    .card-header-right .btn.btn-icon{
      background: rgba(255,255,255,.08) !important;
      border: 1px solid rgba(255,255,255,.12) !important;
      color: rgba(255,255,255,.92) !important;
      border-radius: 12px !important;
    }
    .dropdown-menu{
      background: rgba(10,17,33,.96) !important;
      border: 1px solid rgba(255,255,255,.12) !important;
      box-shadow: 0 18px 45px rgba(0,0,0,.45) !important;
    }
    .dropdown-item,
    .dropdown-item a,
    .dropdown-menu a{
      color: rgba(255,255,255,.90) !important;
    }
    .dropdown-item:hover{
      background: rgba(255,255,255,.08) !important;
    }

    /* Responsive */
    @media (max-width:576px){
      .au-card .card-body{ padding: 14px !important; }
      .btn-au-primary{ width: 100%; min-width: 100%; }
    }

    /* Optional: hide departamento select but keep value */
    .ocultar-select{
      display:none !important;
    }
  </style>
</head>

<body class="dashboard-body">
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
      <div class="page-header mb-3">
        <div class="page-block">
          <div class="row align-items-center">
            <div class="col-md-12">
              <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
                <div>
                  <h5 class="m-b-10 mb-0">Registro Visitas Alcalde</h5>
                  <div class="help-mini">Registra visitas y compromisos con trazabilidad y soporte</div>
                </div>
                <div class="d-flex align-items-center gap-2">
                  <span class="au-badge"><?php echo date('d/m/Y'); ?></span>
                  <?php include './admin/include/btn_back.php'; ?>
                </div>
              </div>
              <ul class="breadcrumb mt-2">
                <li class="breadcrumb-item"><a href="index.html"><i class="feather icon-home"></i></a></li>
                <li class="breadcrumb-item"><a href="#!">Registro Visitas Alcalde</a></li>
              </ul>
            </div>
          </div>
        </div>
      </div>
      <!-- [ breadcrumb ] end -->

      <div class="row">
        <div class="col-12">
          <div class="card au-card position-relative au-accent">
            <div class="card-header d-flex flex-wrap align-items-center justify-content-between">
              <div class="w-100 text-center">
                <h5 class="title mb-0">Ingreso visitas Alcalde</h5>
                <p class="sub mb-0">Completa los campos obligatorios y guarda el registro</p>
              </div>

              <!-- Mantengo tu menú de opciones (pcoded) -->
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

            <div class="card-body">

              <form class="needs-validation au-form-grid" novalidate id="ingresoVisita" autocomplete="off" enctype="multipart/form-data">
                <input type="hidden" name="filtro" id="filtro" value="vereda" />
                <input type="hidden" name="filtroVeredaById" id="filtroVeredaById" value="si" />

                <div class="row">

                  <div class="form-group col-12 col-md-6 col-xl-4">
                    <label for="date">Fecha <span class="text-danger">*</span></label>
                    <input type="date" class="form-control" id="date" name="date" required>
                  </div>

                  <div class="form-group col-12 col-md-6 col-xl-4">
                    <label for="tipo_registro">Tipo de Registro <span class="text-danger">*</span></label>
                    <select class="form-control" id="tipo_registro" name="tipo_registro" required>
                      <option value="">Seleccione</option>
                      <option value="Visita">Visita</option>
                      <option value="Compromiso">Compromiso</option>
                    </select>
                  </div>

                  <div class="form-group col-12 col-md-6 col-xl-4">
                    <label for="tbl_departamento_id">Departamento <span class="text-danger">*</span></label>
                    <!-- Se mantiene (pero oculto por diseño, ya lo fijas a 68 por JS) -->
                    <select class="form-control ocultar-select" style="width:100%;" onchange="DEPARTAMENTO.getMunicipios();" id="tbl_departamento_id" name="tbl_departamento_id">
                      <?php echo $optionDep; ?>
                    </select>

                    <!-- mini etiqueta visible para el usuario -->
                    <div class="help-mini mt-2">
                      <span class="au-badge">Santander (68)</span>
                    </div>
                  </div>

                  <div class="form-group col-12 col-md-6 col-xl-4">
                    <label for="tbl_municipio_id">Municipio <span class="text-danger">*</span></label>
                    <select class="form-control" style="width:100%;" onchange="DEPARTAMENTO.getVeredasByMunicipioId(); DEPARTAMENTO.getSecretariasMunicipales();" id="tbl_municipio_id" name="tbl_municipio_id" required></select>
                  </div>

                  <div class="form-group col-12 col-md-6 col-xl-4">
                    <label for="tbl_vereda_id">Vereda <span class="text-danger">*</span></label>
                    <select class="form-control" id="tbl_vereda_id" name="tbl_vereda_id" required></select>
                  </div>

                  <div class="form-group col-12 col-md-6 col-xl-4 campo-visita">
                    <label for="tipo_visita">Tipo Visita</label>
                    <select class="form-control" id="tipo_visita" name="tipo_visita">
                      <option value="">Seleccione</option>
                      <option value="Reunión">Reunión</option>
                      <option value="Ruta 25">Ruta 25</option>
                      <option value="Brigada Civico Social">Brigada Cívico Social</option>
                      <option value="Consejo de Seguridad">Concejo de Seguridad</option>
                      <option value="Concejos y/o Juntas Directivas">Concejos y/o Juntas Directivas</option>
                      <option value="Inauguración de festividades">Inauguración de festividades</option>
                      <option value="Seguimiento de Obras">Seguimiento de Obras</option>
                      <option value="Seguimiento de Planes, Programas y Proyectos">Seguimiento de Planes, Programas y Proyectos</option>
                    </select>
                  </div>

                  <div class="form-group col-12 col-md-6 col-xl-4">
                    <label for="tbl_secretarias_id">Secretaria o Dependencia Encargada</label>
                    <select class="form-control" id="tbl_secretarias_id" name="tbl_secretarias_id">
                      <?php echo $optionSec; ?>
                    </select>
                  </div>

                  <div class="form-group col-12 col-md-6 col-xl-4 campo-compromiso">
                    <label for="requiere_respuesta">Requiere respuesta <span class="text-danger">*</span></label>
                    <select class="form-control" id="requiere_respuesta" name="requiere_respuesta">
                      <option value="">Seleccione</option>
                      <option value="Si">Si</option>
                      <option value="No">No</option>
                    </select>
                  </div>

                  <div class="form-group col-12 col-md-6 col-xl-4 campo-compromiso">
                    <label for="componente">Componente <span class="text-danger">*</span></label>
                    <select class="form-control" id="componente" name="componente">
                      <?php echo $optionComponentes; ?>
                    </select>
                  </div>

                  <div class="form-group col-12 col-md-6 col-xl-4 campo-compromiso">
                    <label for="tipo_ejecucion">Tipo ejecución <span class="text-danger">*</span></label>
                    <select class="form-control" id="tipo_ejecucion" name="tipo_ejecucion">
                      <option value="">Seleccione</option>
                      <option value="GESTIÓN">GESTIÓN</option>
                      <option value="INVERSIÓN">INVERSIÓN</option>
                    </select>
                  </div>

                  <div class="form-group col-12 col-md-6 col-xl-4 campo-compromiso">
                    <label for="compromisopac">Compromisos Pactados</label>
                    <textarea required placeholder="Ingrese el compromiso de la reunión" class="form-control" id="compromisopac" name="compromisopac" rows="3"></textarea>
                  </div>

                  <div class="form-group col-12 col-md-6 col-xl-4 campo-visita">
                    <label for="compromisos">Detalles</label>
                    <textarea required placeholder="Ingrese detalles de la visita" class="form-control" id="compromisos" name="compromisos" rows="3"></textarea>
                  </div>

                  <div class="form-group col-12 col-md-6 col-xl-4">
                    <label for="img">Subir imagen</label>
                    <input type="file" class="form-control-file" id="img" accept="image/*">
                    <div id="previewImage" class="mt-2"></div>
                  </div>

                </div>

                <div class="row mt-2">
                  <div class="col-12 text-center">
                    <button type="button" class="btn btn-au-primary" id="guardaVisita">
                      Guardar
                    </button>
                  </div>
                </div>

              </form>

            </div>
          </div>
        </div>
      </div>

    </div>
  </div>
  <!-- [ Main Content ] end -->

  <!-- Required Js -->
  <?php include 'admin/include/gerenic_script.php'; ?>
  <script type="text/javascript" src="admin/js/detalle_visitas_alcalde.js"></script>
  <script src="assets/js/vendor-all.min.js"></script>
  <script src="assets/js/plugins/bootstrap.min.js"></script>
  <script type="text/javascript" src="admin/js/departamento.js"></script>
  <script src="assets/js/pcoded.min.js"></script>

  <script>
    window.userMunicipio = '<?php echo $codigoMunicipio; ?>';

    setTimeout(function() {
      $('#tbl_departamento_id').val('68');
      DEPARTAMENTO.getMunicipios();
      var checkMun = setInterval(function() {
        if ($('#tbl_municipio_id option').length > 1) {
          clearInterval(checkMun);
          if (window.userMunicipio) {
            $('#tbl_municipio_id').val(window.userMunicipio).trigger('change');
          }
        }
      }, 100);
    }, 100);
  </script>

</body>
</html>
