<?php
include './admin/include/head.php';
include './admin/include/dark_theme.php';
require './admin/include/generic_classes.php';

// Permisos RBAC
extract(PagePermissions::crudVarsForCurrentPage());

// Solo Admin, Gobernador y Secretaría de Interior (id=2)
$userTypeInt = SessionData::getUserType();
$secretariaIdInt = SessionData::getSecretaria();
$isAdminInt = ($userTypeInt === Util::Administrador() || $userTypeInt === Util::SuperAdministrador() || $userTypeInt === Util::Gobernador());
if (!$isAdminInt && $secretariaIdInt != Util::getSecretariaIdInterior()) {
    header('Location: dashboard.php');
    exit;
}

include './admin/classes/Departamento.php';
include './admin/classes/Provincias.php';
include './admin/classes/Ciudad.php';

$provArr = Provincias::getProvinciasByDepartamento(Util::getDepartamentoPrincipal());
$provinciasJSON = '[]';
if (!empty($provArr['output']['valid'])) {
    $provinciasJSON = json_encode($provArr['output']['response']);
}

$modulo = 'Registro Visitas';



// Información de Departamentos
$arrDep = Departamento::getAll(null);
$isvalid = $arrDep['output']['valid'] ?? false;
$arrDep = $arrDep['output']['response'] ?? [];
$optionDep = Util::getDepartamentoPrincipal();
foreach ($arrDep as $val) {
    $optionDep .= "<option value='" . htmlspecialchars($val['codigo_departamento']) . "'>" .
        htmlspecialchars($val['codigo_departamento']) . " - " . htmlspecialchars($val['departamento']) . "</option>";
}
function h($s){ return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }
?>

<body class="">
    <!-- [ Pre-loader ] start -->
    <div class="loader-bg">
        <div class="loader-track">
            <div class="loader-fill"></div>
        </div>
    </div>
    <!-- [ Pre-loader ] End -->

    <?php include './admin/include/navbar.php'; ?>
    <?php include './admin/include/header.php'; ?>

    <style>
/* ===============================
   ACCIÓN UNIFICADA – GOVTECH WOW
   (SOLO VISTA, NO TOCA LÓGICA)
================================ */

/* ====== THEME DARK GLASS ====== */
:root{
  --bg0:#070A12;
  --bg1:#0B1222;

  --stroke: rgba(255,255,255,.10);
  --stroke2: rgba(255,255,255,.14);

  --txt: rgba(255,255,255,.92);
  --muted: rgba(255,255,255,.66);

  --brand:#4f7cff;
  --brand2:#9b5cff;

  --danger:#ff5b7a;
  --ok:#18ff6d;

  --radius-xl:22px;
  --radius-lg:16px;

  --shadow-soft: 0 14px 40px rgba(0,0,0,.25);
  --shadow-mid: 0 22px 60px rgba(0,0,0,.35);
}

/* fondo */
body{
  background:
    radial-gradient(900px 420px at 10% 10%, rgba(79,124,255,.28), transparent 60%),
    radial-gradient(900px 420px at 80% 20%, rgba(155,92,255,.22), transparent 60%),
    radial-gradient(900px 520px at 50% 100%, rgba(24,255,109,.10), transparent 60%),
    linear-gradient(180deg, var(--bg0), var(--bg1)) !important;
  color: var(--txt);
  overflow-x:hidden;
}

/* spacing */
.pcoded-main-container{ background: transparent !important; }
.pcoded-content{ padding: 16px 16px !important; }
@media(min-width:768px){ .pcoded-content{ padding: 24px 24px !important; } }
@media(min-width:1200px){ .pcoded-content{ padding: 34px 42px !important; } }

/* TOPBAR */
.au-topbar{
  display:flex; flex-direction:column; gap:10px;
  margin-bottom:18px;
  padding-top: 20px;
}
@media(min-width:768px){
  .au-topbar{ flex-direction:row; align-items:center; justify-content:space-between; }
}
.au-title{
  margin:0;
  font-weight:900;
  font-size:1.55rem;
  letter-spacing:.2px;
  color: #fff !important;
}
.au-subtitle{
  margin:4px 0 0;
  color: rgba(255,255,255,.70);
  font-size:.92rem;
}

/* tabs */
.au-tabs{
  display:flex;
  gap:8px;
  flex-wrap:wrap;
  background: rgba(255,255,255,.06);
  border: 1px solid var(--stroke);
  padding:6px;
  border-radius: var(--radius-lg);
  box-shadow: var(--shadow-soft);
  width: fit-content;
  margin-bottom: 18px;
}
.au-tabs .nav-link{
  border:0 !important;
  border-radius: 14px !important;
  padding: 10px 18px !important;
  font-weight:900;
  color: var(--muted);
  background: transparent;
}
.au-tabs .nav-link.active{
  background: linear-gradient(135deg, rgba(79,124,255,.40), rgba(155,92,255,.25)) !important;
  color:#fff !important;
  box-shadow: 0 12px 26px rgba(0,0,0,.30);
  border: 1px solid rgba(255,255,255,.14) !important;
}

/* cards */
.card{
  border: 1px solid var(--stroke) !important;
  border-radius: var(--radius-xl) !important;
  background: linear-gradient(135deg, rgba(255,255,255,.09), rgba(255,255,255,.04)) !important;
  box-shadow: var(--shadow-mid);
  overflow:hidden;
  position:relative;
}
.card:before{
  content:””;
  position:absolute; inset:-2px;
  background:
    radial-gradient(320px 180px at 10% 10%, rgba(79,124,255,.30), transparent 65%),
    radial-gradient(320px 180px at 90% 20%, rgba(155,92,255,.22), transparent 65%);
  pointer-events:none;
}
.card > *{ position:relative; z-index:1; }

.card-header{
  background: rgba(0,0,0,.18) !important;
  border-bottom: 1px solid var(--stroke) !important;
  padding: 18px 22px !important;
}
.card-header h5{
  font-weight:900 !important;
  color: var(--txt) !important;
  margin:0 !important;
}
.card-body{ padding: 22px !important; }

/* form grid */
.au-form-grid{
  display:grid;
  grid-template-columns: 1fr;
  gap: 14px;
}
@media(min-width:768px){
  .au-form-grid.md-3{ grid-template-columns: repeat(3, 1fr); }
}

/* inputs */
.form-control, .form-control-file, select.form-control{
  border-radius: 14px !important;
  padding: 12px 14px !important;
  border: 1px solid var(--stroke2) !important;
  background: rgba(0,0,0,.22) !important;
  color: var(--txt) !important;
  min-height: 44px;
}
.form-control::placeholder{ color: rgba(255,255,255,.55) !important; }
.form-control:focus, select.form-control:focus{
  border-color: rgba(79,124,255,.55) !important;
  box-shadow: 0 0 0 .15rem rgba(79,124,255,.18) !important;
}
label{
  color: rgba(255,255,255,.72) !important;
  font-weight: 900;
}

/* ojo password */
.input-group .input-group-text{
  border-radius: 0 14px 14px 0 !important;
  cursor: pointer;
  border: 1px solid var(--stroke2) !important;
  background: rgba(0,0,0,.30) !important;
  color: var(--txt) !important;
}

/* buttons */
.btn{
  border-radius: 14px !important;
  padding: 10px 22px !important;
  font-weight: 900 !important;
  border: 1px solid var(--stroke2) !important;
  box-shadow: 0 10px 24px rgba(0,0,0,.25);
}
.btn-primary{
  border-color: rgba(79,124,255,.50) !important;
  background: linear-gradient(135deg, rgba(79,124,255,.40), rgba(155,92,255,.25)) !important;
  color:#fff !important;
}
.btn-danger{
  border-color: rgba(255,91,122,.45) !important;
  background: rgba(255,91,122,.14) !important;
  color:#fff !important;
}
.btn-secondary{
  background: rgba(255,255,255,.06) !important;
  color: var(--txt) !important;
}

/* buscador */
#customSearchInversiones{ border-radius: 14px 0 0 14px !important; }
.buscador-2 .input-group-text{ border-radius: 0 14px 14px 0 !important; }

/* ====== TABLA DARK GLASS PREMIUM ====== */
.table-shell{
  background: rgba(255,255,255,.06) !important;
  border-radius:24px; overflow:hidden;
  border:1px solid rgba(255,255,255,.12);
  box-shadow: 0 22px 70px rgba(0,0,0,.34);
}
.table-shell__top{
  display:flex; align-items:center; justify-content:space-between; gap:18px;
  padding:20px 24px 16px;
  border-bottom:1px solid rgba(255,255,255,.10);
  background:rgba(0,0,0,.14);
}
.table-shell__eyebrow{
  display:inline-flex; align-items:center; gap:8px; margin-bottom:6px;
  color:rgba(255,255,255,.7); font-size:11px; font-weight:1000;
  letter-spacing:.14em; text-transform:uppercase;
}
.table-shell__eyebrow:before{
  content:""; width:9px; height:9px; border-radius:999px;
  background:linear-gradient(135deg,#22c1ff,#20427F);
  box-shadow:0 0 0 5px rgba(34,193,255,.12);
}
.table-shell__title{ margin:0; color:#fff; font-size:1.3rem; font-weight:1000; letter-spacing:-.02em; }
.table-shell__subtitle{ margin-top:4px; color:rgba(255,255,255,.6); font-size:.92rem; line-height:1.45; }
.table-shell__badge{
  display:inline-flex; align-items:center; justify-content:center;
  min-width:92px; padding:.7rem 1rem; border-radius:16px;
  background:linear-gradient(135deg,#203e5c,#2f3f6e); color:#fff;
  font-size:.78rem; font-weight:1000; letter-spacing:.06em; text-transform:uppercase;
  box-shadow:0 16px 36px rgba(32,62,92,.20);
}
.table-shell__body{ padding:18px 18px 14px; }
.table-wrap{ display:flex; justify-content:center; padding:8px 0 2px; }

.table-responsive{
  border-radius:18px; border:1px solid rgba(255,255,255,.10); overflow:auto;
}
#dynamictable{ margin:0 !important; font-size:11px !important; width:100% !important; }
#dynamictable thead th{
  color:#fff !important;
  background: linear-gradient(135deg, #203e5c, #2f3f6e) !important;
  text-transform:uppercase; letter-spacing:.1px;
  font-size:10px !important; white-space:nowrap;
  text-align:center; vertical-align:middle !important;
  padding:8px 5px !important;
  border-color:rgba(255,255,255,.06) !important;
}
#dynamictable tbody tr{ background:transparent !important; }
#dynamictable tbody td{
  color:rgba(255,255,255,.86) !important;
  background:transparent !important;
  border-top:1px solid rgba(255,255,255,.06) !important;
  vertical-align:middle; padding:6px 4px !important;
  line-height:1.25; font-size:10.5px !important; font-weight:700 !important;
}
#dynamictable tbody tr:nth-child(even) td{ background:rgba(255,255,255,.03) !important; }
#dynamictable tbody tr:hover td{ background:rgba(255,255,255,.06) !important; }
#dynamictable .btn-sm{ border-radius:8px !important; padding:4px 8px !important; min-width:32px; font-size:10px !important; }
#dynamictable .btn-sm i{ color:#fff !important; }
#dynamictable .badge{ border-radius:999px !important; padding:.25rem .5rem !important; font-weight:1000 !important; font-size:10px !important; border:1px solid rgba(255,255,255,.12); background:rgba(79,124,255,.25); color:#fff !important; }

/* ====== MODALES OSCUROS ====== */
.modal-backdrop.show{ opacity: .85 !important; }
.modal-backdrop{ background: #000 !important; }

.modal-content{
  border-radius: 18px !important;
  border: 1px solid rgba(255,255,255,.14) !important;
  background: linear-gradient(135deg, rgba(20,24,35,.92), rgba(10,12,18,.92)) !important;
  color: var(--txt) !important;
  box-shadow: var(--shadow-mid);
  overflow:hidden;
}
.modal-header{
  border-bottom: 1px solid rgba(255,255,255,.12) !important;
  background: rgba(0,0,0,.35) !important;
}
.modal-title{
  font-weight: 900 !important;
  letter-spacing:.2px;
  color:#fff !important;
}
.modal-footer{
  border-top: 1px solid rgba(255,255,255,.12) !important;
  background: rgba(0,0,0,.25) !important;
}
.close, .close span{
  color:#fff !important;
  opacity: 1 !important;
  text-shadow:none !important;
}

/* botón guardar */
.btn-pro{
  border: 0 !important;
  border-radius: 16px !important;
  padding: 12px 22px !important;
  font-weight: 900 !important;
  color: #fff !important;
  background: linear-gradient(135deg, rgba(79,124,255,.50), rgba(155,92,255,.35)) !important;
  box-shadow: 0 14px 26px rgba(79,124,255,.28);
  transition: all .18s ease;
}
.btn-pro:hover{
  transform: translateY(-2px);
  box-shadow: 0 18px 32px rgba(79,124,255,.34);
}

small, .text-muted{ color: var(--muted) !important; }

/* DataTables pagination dark */
.dataTables_wrapper{ padding:4px 4px 0; }
.dataTables_wrapper .row:first-child,
.dataTables_wrapper .row:last-child{ margin-left:0; margin-right:0; }
.dataTables_wrapper .row:first-child{ padding:0 2px 14px; align-items:center; }
.dataTables_wrapper .row:last-child{ padding:14px 2px 2px; align-items:center; }
.dataTables_wrapper .dataTables_filter input,
.dataTables_wrapper .dataTables_length select{
  border-radius:14px !important;
  border:1px solid rgba(255,255,255,.14) !important;
  padding:9px 12px !important; font-size:12.5px !important;
  outline:none !important;
  background:rgba(255,255,255,.06) !important;
  color:#fff !important;
}
.dataTables_wrapper .dataTables_filter label,
.dataTables_wrapper .dataTables_length label{ color:#fff !important; font-weight:800; font-size:12.5px; margin-bottom:0; }
.dataTables_wrapper .dataTables_filter{ text-align:right; }
.dataTables_wrapper .dataTables_info{ font-size:12.5px; color:#fff !important; padding:10px 6px; font-weight:700; }
.dataTables_wrapper .dataTables_paginate .paginate_button{
  border-radius:12px !important;
  color:rgba(255,255,255,.86) !important;
  border:1px solid rgba(255,255,255,.14) !important;
  background:rgba(255,255,255,.06) !important;
  padding:0.4em 0.9em !important;
  font-weight:800 !important;
}
.dataTables_wrapper .dataTables_paginate .paginate_button.current,
.dataTables_wrapper .dataTables_paginate .paginate_button.current:hover{
  background:linear-gradient(135deg,#203e5c,#2f3f6e) !important;
  color:#fff !important;
  border:1px solid rgba(255,255,255,.20) !important;
  box-shadow:0 10px 24px rgba(32,62,92,.18);
}
.dataTables_wrapper .dataTables_paginate .paginate_button:hover{
  background:rgba(255,255,255,.10) !important;
  color:#fff !important;
  border:1px solid rgba(255,255,255,.20) !important;
}
.dataTables_wrapper .dataTables_paginate .paginate_button.disabled,
.dataTables_wrapper .dataTables_paginate .paginate_button.disabled:hover{
  color:rgba(255,255,255,.30) !important;
  background:transparent !important;
  border:1px solid transparent !important;
}
#dynamictable td:nth-child(8) {
    min-width: 200px;
    max-width: 280px;
}
.desc-short {
    white-space: pre-wrap;
    word-break: break-word;
}
.desc-toggle {
    color: #4f7cff;
    cursor: pointer;
    font-weight: 600;
}
.desc-toggle:hover {
    text-decoration: underline;
}
.mun-pill:hover{ filter:brightness(1.1); }
    </style>

    <div class="pcoded-main-container">
        <div class="pcoded-content">

            <div class="au-topbar">
                <div>
                    <h1 class="au-title">Inversiones en Seguridad</h1>
                    <div class="au-subtitle">Secretaría del Interior · Registro y gestión de contratos</div>
                </div>
                <div>
                    <?php include './admin/include/btn_back.php'; ?>
                </div>
            </div>

            <!-- NAV TABS -->
            <ul class="nav nav-tabs au-tabs" id="inversionTabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="tab-form-btn" data-toggle="tab" data-target="#tab-form"
                        type="button" role="tab">Ingresar contrato</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="tab-lista-btn" data-toggle="tab" data-target="#tab-lista"
                        type="button" role="tab" onclick="cargarTablaInversiones()">Listado de contratos</button>
                </li>
            </ul>

            <div class="tab-content" id="inversionTabContent">

                <!-- TAB FORMULARIO -->
                <div class="tab-pane fade show active" id="tab-form" role="tabpanel">
                    <div class="card mt-3">
                        <div class="card-header d-flex flex-wrap align-items-center justify-content-between">
                            <h5 class="mb-0"><i class="feather icon-file-plus"></i> Formulario Registro de Contratos</h5>
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
                            <form class="needs-validation" novalidate id="ingresoVisita" enctype="multipart/form-data">

                                <div class="au-form-grid md-3">
                                    <div class="form-group">
                                        <label>Fecha <span class="text-danger">*</span></label>
                                        <input type="date" name="fecha" class="form-control" required>
                                    </div>
                                    <div class="form-group">
                                        <label>Tipo Inversión <span class="text-danger">*</span></label>
                                        <select name="tipo_seccion" class="form-control" required>
                                            <option value="">Seleccione</option>
                                            <option value="movilidad">Movilidad</option>
                                            <option value="tecnologia">Tecnología</option>
                                            <option value="proyectos">Proyectos Estratégicos</option>
                                            <option value="intendencia">Intendencia</option>
                                            <option value="infraestructura">Infraestructura</option>
                                            <option value="pagos">Pagos Recompensas</option>
                                            <option value="convenios">Convenios</option>
                                        </select>
                                    </div>
                                    <div class="form-group">
                                        <label>Contrato <span class="text-danger">*</span></label>
                                        <input type="text" name="titulo" class="form-control" required>
                                    </div>
                                </div>

                                <div class="au-form-grid md-3">
                                    <div class="form-group">
                                        <label>Institución Beneficiada <span class="text-danger">*</span></label>
                                        <select id="institucion" name="institucion" class="form-control" required>
                                            <option value="">-- Seleccionar --</option>
                                            <option value="POLICIA MEBUC">POLICIA MEBUC</option>
                                            <option value="POLICIA DESAN">POLICIA DESAN</option>
                                            <option value="POLICIA DEMAM">POLICIA DEMAM</option>
                                            <option value="EJERCITO NACIONAL">EJERCITO NACIONAL</option>
                                            <option value="ARMADA NACIONAL">ARMADA NACIONAL</option>
                                            <option value="FISCALIA">FISCALIA</option>
                                            <option value="MIGRACION COLOMBIA">MIGRACION COLOMBIA</option>
                                            <option value="INPEC">INPEC</option>
                                            <option value="UNP">UNP</option>
                                            <option value="DEPARTAMENTO DE SANTANDER">DEPARTAMENTO DE SANTANDER</option>
                                            <option value="OTRO">OTRO</option>
                                        </select>
                                    </div>
                                    <div class="form-group">
                                        <label>Dirección <span class="text-danger">*</span></label>
                                        <select id="direccion" name="direccion" class="form-control" required>
                                            <option value="Dirección de Seguridad y Convivencia">Dirección de Seguridad y Convivencia</option>
                                        </select>
                                    </div>
                                    <div class="form-group">
                                        <label>Valor ($) <span class="text-danger">*</span></label>
                                        <input type="text" id="valor" name="valor" class="form-control" required>
                                    </div>
                                </div>

                                <div class="form-group mt-3">
                                    <label>Provincia / Municipio <span class="text-danger">*</span></label>
                                    <div id="municipios-container" class="p-3" style="background:rgba(0,0,0,.15);border-radius:16px;border:1px solid rgba(255,255,255,.08);">
                                        <div class="row-municipio mb-3 pb-2" style="border-bottom:1px solid rgba(255,255,255,.08);">
                                            <div class="d-flex align-items-center gap-2 mb-2" style="gap:8px;">
                                                <select class="form-control provincia-select" style="flex:0 0 220px;width:220px;">
                                                    <option value="">-- Provincia --</option>
                                                </select>
                                                <button type="button" class="btn btn-success btn-sm btn-add-municipio" style="flex-shrink:0;padding:6px 14px;border-radius:10px;" title="Agregar otra provincia">
                                                    <i class="feather icon-plus"></i> Agregar
                                                </button>
                                            </div>
                                            <select class="municipio-select-hidden" name="municipios[]" multiple style="display:none;"></select>
                                            <div class="municipio-pills d-flex flex-wrap" style="gap:6px;min-height:32px;padding:4px 0;"></div>
                                        </div>
                                    </div>
                                    <small class="text-muted">Selecciona una provincia, luego haz clic en los municipios para seleccionarlos. Usa "Agregar" para añadir más provincias.</small>
                                </div>

                                <div class="au-form-grid md-3 mt-3">
                                    <div class="form-group">
                                        <label>Descripción</label>
                                        <textarea name="descripcion" rows="3" class="form-control"></textarea>
                                    </div>
                                    <div class="form-group">
                                        <label>Imagen</label>
                                        <input type="file" id="imagen" name="imagen" class="form-control">
                                        <div id="previewImagen" class="mt-2"></div>
                                    </div>
                                </div>

                                <div class="pt-2">
                                    <button type="submit" class="btn btn-pro"><i class="feather icon-save"></i> Guardar Inversión</button>
                                </div>

                            </form>
                        </div>
                    </div>
                </div>

                <!-- TAB LISTADO -->
                <div class="tab-pane fade" id="tab-lista" role="tabpanel">
                    <div class="card mt-3">
                        <div class="card-header d-flex flex-wrap align-items-center justify-content-between">
                            <h5 class="mb-0">Listado de contratos</h5>
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
                        <div class="card-body p-0">
                            <div class="table-wrap">
                                <div class="table-shell">
                                    <div class="table-shell__top">
                                        <div>
                                            <div class="table-shell__eyebrow">Contratos registrados</div>
                                            <h3 class="table-shell__title">Listado de Contratos</h3>
                                            <div class="table-shell__subtitle">Consulta, edita y administra los contratos de inversión en seguridad.</div>
                                        </div>
                                        <div class="table-shell__badge">Vista Pro</div>
                                    </div>
                                    <div class="table-shell__body">
                                        <div class="table-responsive">
                                            <table id="dynamictable" class="table table-hover mb-0 w-100">
                                                <thead>
                                                    <tr>
                                                        <th><i class="feather icon-settings"></i></th>
                                                        <th>#</th>
                                                        <th>Fecha</th>
                                                        <th>Tipo</th>
                                                        <th>Contrato</th>
                                                        <th>Institución</th>
                                                        <th>Municipio</th>
                                                        <th>Dirección</th>
                                                        <th>Valor ($)</th>
                                                    </tr>
                                                </thead>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div><!-- /tab-content -->

        </div>
    </div>

    <!-- MODAL VER INVERSIÓN -->
    <div class="modal fade" id="modalVerInversion" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="feather icon-eye"></i> Detalle del Contrato</h5>
                    <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                </div>
                <div class="modal-body" id="modalVerBody"></div>
            </div>
        </div>
    </div>

    <!-- MODAL EDITAR INVERSIÓN -->
    <div class="modal fade" id="modalEditarInversion" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-xl" role="document" style="max-width:1400px;">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="feather icon-edit"></i> Editar Contrato</h5>
                    <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                </div>
                <div class="modal-body">
                    <form id="formEditarInversion" enctype="multipart/form-data">
                        <input type="hidden" name="id" id="edit_id">

                        <div class="au-form-grid md-3">
                            <div class="form-group">
                                <label>Fecha <span class="text-danger">*</span></label>
                                <input type="date" name="fecha" id="edit_fecha" class="form-control" required>
                            </div>
                            <div class="form-group">
                                <label>Tipo Inversión <span class="text-danger">*</span></label>
                                <select name="tipo_seccion" id="edit_tipo_seccion" class="form-control" required>
                                    <option value="">Seleccione</option>
                                    <option value="movilidad">Movilidad</option>
                                    <option value="tecnologia">Tecnología</option>
                                    <option value="proyectos">Proyectos Estratégicos</option>
                                    <option value="intendencia">Intendencia</option>
                                    <option value="infraestructura">Infraestructura</option>
                                    <option value="pagos">Pagos Recompensas</option>
                                    <option value="convenios">Convenios</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label>Contrato <span class="text-danger">*</span></label>
                                <input type="text" name="titulo" id="edit_titulo" class="form-control" required>
                            </div>
                        </div>

                        <div class="au-form-grid md-3">
                            <div class="form-group">
                                <label>Institución <span class="text-danger">*</span></label>
                                <select name="institucion" id="edit_institucion" class="form-control" required>
                                    <option value="">-- Seleccionar --</option>
                                    <option value="POLICIA MEBUC">POLICIA MEBUC</option>
                                    <option value="POLICIA DESAN">POLICIA DESAN</option>
                                    <option value="POLICIA DEMAM">POLICIA DEMAM</option>
                                    <option value="EJERCITO NACIONAL">EJERCITO NACIONAL</option>
                                    <option value="ARMADA NACIONAL">ARMADA NACIONAL</option>
                                    <option value="FISCALIA">FISCALIA</option>
                                    <option value="MIGRACION COLOMBIA">MIGRACION COLOMBIA</option>
                                    <option value="INPEC">INPEC</option>
                                    <option value="UNP">UNP</option>
                                    <option value="DEPARTAMENTO DE SANTANDER">DEPARTAMENTO DE SANTANDER</option>
                                    <option value="OTRO">OTRO</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label>Provincia / Municipio <span class="text-danger">*</span></label>
                                <div id="edit-municipios-container" class="p-3" style="background:rgba(0,0,0,.15);border-radius:16px;border:1px solid rgba(255,255,255,.08);">
                                    <div class="row-municipio mb-3 pb-2" style="border-bottom:1px solid rgba(255,255,255,.08);">
                                        <div class="d-flex align-items-center gap-2 mb-2" style="gap:8px;">
                                            <select class="form-control provincia-select" style="flex:0 0 220px;width:220px;">
                                                <option value="">-- Provincia --</option>
                                            </select>
                                            <button type="button" class="btn btn-success btn-sm btn-add-municipio" style="flex-shrink:0;padding:6px 14px;border-radius:10px;" title="Agregar otra provincia">
                                                <i class="feather icon-plus"></i> Agregar
                                            </button>
                                        </div>
                                        <select class="municipio-select-hidden" name="municipios[]" multiple style="display:none;"></select>
                                        <div class="municipio-pills d-flex flex-wrap" style="gap:6px;min-height:32px;padding:4px 0;"></div>
                                    </div>
                                </div>
                                <small class="text-muted">Selecciona una provincia, luego haz clic en los municipios para seleccionarlos.</small>
                            </div>
                            <div class="form-group">
                                <label>Dirección <span class="text-danger">*</span></label>
                                <select name="direccion" id="edit_direccion" class="form-control" required>
                                    <option value="Dirección de Seguridad y Convivencia">Dirección de Seguridad y Convivencia</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label>Valor ($)</label>
                                <input type="text" name="valor" id="edit_valor" class="form-control">
                            </div>
                        </div>

                        <div class="au-form-grid md-3">
                            <div class="form-group">
                                <label>Cantidad</label>
                                <input type="number" name="cantidad" id="edit_cantidad" class="form-control" min="0">
                            </div>
                            <div class="form-group">
                                <label>Descripción</label>
                                <textarea name="descripcion" id="edit_descripcion" rows="5" class="form-control" style="min-height: 120px; resize: vertical;"></textarea>
                            </div>
                            <div class="form-group">
                                <label>Imagen <small class="text-muted">(vacío = conservar actual)</small></label>
                                <input type="file" name="imagen" id="edit_imagen" class="form-control">
                                <div id="edit_previewImagen" class="mt-2"></div>
                            </div>
                        </div>

                        <div class="pt-2 text-right">
                            <button type="button" class="btn btn-secondary mr-2" data-dismiss="modal">Cancelar</button>
                            <button type="submit" class="btn btn-primary"><i class="feather icon-save"></i> Guardar Cambios</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Required Js -->
<!-- Required Js -->
<?php include 'admin/include/gerenic_script.php'; ?>

<script src="assets/js/vendor-all.min.js"></script>
<script src="assets/js/plugins/bootstrap.min.js"></script>
<script type="text/javascript" src="admin/js/departamento.js"></script>
<script src="assets/js/pcoded.min.js"></script>

<script>
var PROVINCIAS_DATA = <?= $provinciasJSON ?>;
</script>

<!-- Select2 CSS -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css">

<!-- Select2 JS -->
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<!-- DataTables CSS/JS -->
<link rel="stylesheet" href="./admin/js/datatables/jquery.dataTables.min.css">
<script src="./admin/js/datatables/jquery.dataTables.min.js"></script>

<style>
  .dataTables_wrapper .dataTables_paginate .paginate_button{
    color:rgba(255,255,255,.86) !important;
    background:rgba(255,255,255,.06) !important;
    border:1px solid rgba(255,255,255,.14) !important;
    border-radius:12px !important;
    padding:0.4em 0.9em !important;
    font-weight:800 !important;
  }
  .dataTables_wrapper .dataTables_paginate .paginate_button.current,
  .dataTables_wrapper .dataTables_paginate .paginate_button.current:hover{
    background:linear-gradient(135deg,#203e5c,#2f3f6e) !important;
    color:#fff !important;
    border:1px solid rgba(255,255,255,.20) !important;
  }
  .dataTables_wrapper .dataTables_paginate .paginate_button:hover{
    background:rgba(255,255,255,.10) !important;
    color:#fff !important;
    border:1px solid rgba(255,255,255,.20) !important;
  }
  .dataTables_wrapper .dataTables_paginate .paginate_button.disabled,
  .dataTables_wrapper .dataTables_paginate .paginate_button.disabled:hover{
    color:rgba(255,255,255,.30) !important;
    background:transparent !important;
    border:1px solid transparent !important;
  }
  .dataTables_wrapper .dataTables_info,
  .dataTables_wrapper .dataTables_length label,
  .dataTables_wrapper .dataTables_filter label{ color:#fff !important; font-weight:800; }
  .dataTables_wrapper .dataTables_info{ font-size:12.5px; padding:10px 6px; }
  .dataTables_wrapper .dataTables_filter input,
  .dataTables_wrapper .dataTables_length select{
    border-radius:14px !important;
    border:1px solid rgba(255,255,255,.14) !important;
    padding:9px 12px !important;
    background:rgba(255,255,255,.06) !important;
    color:#fff !important;
  }
</style>

<script src="<?php echo Util::versionar('./admin/js/inversiones.js'); ?>"></script>

</body>

</html>