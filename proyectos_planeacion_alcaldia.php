<?php
include './admin/include/head.php';
include './admin/include/dark_theme.php';
require './admin/include/generic_classes.php';

$modulo = 'Ingreso De Proyectos Planeación Alcaldía';

include './admin/classes/Proyectos.php';
include './admin/classes/Departamento.php';
include './admin/classes/Secretarias.php';
include './admin/classes/DesarrolloAlcalde.php';
include './admin/classes/Ingreso_proyectos_secretarias.php';
include './admin/classes/SecretariasMunicipios.php';
require_once './admin/classes/Authorization.php';

function escapeHtml($value) {
  return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
}

// ================================
// Usuario logueado
// ================================
$user_rol = $_SESSION['session_user']['tipo'] ?? '';
$id_secretaria_usuario = (int)($_SESSION['session_user']['tbl_secretarias_id'] ?? 0);
$codigo_municipio_usuario = $_SESSION['session_user']['tbl_municipio_id'] ?? '';

// Tipos de usuario municipales (Alcaldía)
$tiposUsuarioAlcalde = [
  'Alcalde',
  'Auxiliar_Alcalde',
  'Secretario_Despacho_Alcalde',
  'Auxiliar_Secretario_Despacho_Alcalde'
];
$isUsuarioAlcalde = in_array($user_rol, $tiposUsuarioAlcalde, true);

$tiposAdmin = ['Administrador', 'SuperAdministrador', 'Gobernador'];
$isAdmin = in_array($user_rol, $tiposAdmin, true);

// ================================
// Metas del plan de desarrollo
// (solo precarga para usuario alcaldía con municipio fijo; admin carga por AJAX al elegir municipio)
// ================================
$arrMetas = [];
if ($isUsuarioAlcalde && !empty($codigo_municipio_usuario)) {
  $resultFromDb_Metas = DesarrolloAlcalde::getByMunicipio(['codigo_municipio' => $codigo_municipio_usuario]);
  if (!empty($resultFromDb_Metas['output']['valid'])) {
    $arrMetas = $resultFromDb_Metas['output']['response'] ?? [];
  }
}

// ================================
// Proyectos (filtro por alcaldía; view_all / SuperAdmin ven todos)
// ================================
$arrProyectos = [];
$isvalidProyectos = false;
$canDetail = Authorization::can('proyectos.alcaldias.planeacion.detail');
$canManage = Authorization::can('proyectos.alcaldias.planeacion.manage')
    || Authorization::can('secretarias.proyectos.approve');
$canReopen = Authorization::can('proyectos.alcaldias.planeacion.reopen');
$canDashboard = Authorization::can('proyectos.alcaldias.planeacion.dashboard');
$canViewAll = Authorization::can('proyectos.alcaldias.planeacion.view_all');
$canViewAllAlcaldia = Authorization::can('proyectos.alcaldias.planeacion.view_all_alcaldia');
$canAssign = Authorization::can('proyectos.alcaldias.planeacion.assign');
$canInformes = Authorization::can('proyectos.alcaldias.planeacion.informes');
$canCreate = Authorization::can('proyectos.alcaldias.planeacion.create')
    || Authorization::can('secretarias.proyectos.create');
$scopeActual = Proyectos_Secretarias::resolveListScope();

// Filtros listado (GET)
$filtroMunicipio = $_GET['filtro_municipio'] ?? '';
$filtroUsuarios = isset($_GET['filtro_usuarios']) ? (array)$_GET['filtro_usuarios'] : [];
$filtroUsuarios = array_values(array_filter(array_map('intval', $filtroUsuarios)));
// Departamento (view_all / SuperAdmin / Admin): municipio + usuarios.
// Alcaldía con gestión: solo usuarios.
$mostrarFiltroMunicipio = SessionData::superAdministrador()
    || $canViewAll
    || in_array($user_rol, ['Administrador', 'Gobernador'], true);
$mostrarFiltroUsuarios = $mostrarFiltroMunicipio || $canManage || $canViewAllAlcaldia || $canAssign;
$mostrarFiltrosListado = $mostrarFiltroMunicipio || $mostrarFiltroUsuarios;

$resultFromDb_Proyectos = Proyectos_Secretarias::getProyectosParaListado([
  'municipio_id' => $filtroMunicipio,
  'usuario_ids' => $filtroUsuarios,
]);
if (!empty($resultFromDb_Proyectos)) {
  $isvalidProyectos = true;
  $arrProyectos = $resultFromDb_Proyectos;
}

// Municipios para filtro departamental
$optionFiltroMunicipios = "<option value=''>Todos los municipios</option>";
if ($mostrarFiltroMunicipio) {
  $dbMun = new DbConection();
  $pdoMun = $dbMun->openConect();
  try {
    $depto = Util::getDepartamentoPrincipal();
    $stMun = $pdoMun->prepare(
      "SELECT codigo_muncipio, municipio FROM " . $dbMun->getTable('tbl_ciudades_accion_unificada') . "
       WHERE codigo_departamento = :d ORDER BY municipio"
    );
    $stMun->execute([':d' => $depto]);
    foreach ($stMun->fetchAll(PDO::FETCH_ASSOC) as $m) {
      $sel = ((string)$filtroMunicipio === (string)$m['codigo_muncipio']) ? 'selected' : '';
      $optionFiltroMunicipios .= "<option $sel value='".escapeHtml($m['codigo_muncipio'])."'>".escapeHtml($m['municipio'])."</option>";
    }
  } catch (Throwable $e) {
    // silencioso
  }
  $dbMun->closeConect();
}

// Usuarios iniciales para filtro
$optionFiltroUsuarios = '';
if ($mostrarFiltroUsuarios) {
  $usuariosFiltroResp = Proyectos_Secretarias::getUsuariosFiltroListado([
    'municipio_id' => $mostrarFiltroMunicipio ? $filtroMunicipio : ($codigo_municipio_usuario ?: ''),
  ]);
  $usuariosFiltro = ($usuariosFiltroResp['output']['valid'] ?? false) ? ($usuariosFiltroResp['output']['response'] ?? []) : [];
  foreach ($usuariosFiltro as $u) {
    $uid = (int)($u['id'] ?? 0);
    $label = trim(($u['nombre'] ?? '') . ' ' . ($u['apellido'] ?? ''));
    if ($label === '') $label = $u['nickname'] ?? ('Usuario #' . $uid);
    if (!empty($u['nombre_municipio']) && $mostrarFiltroMunicipio) {
      $label .= ' — ' . $u['nombre_municipio'];
    }
    $sel = in_array($uid, $filtroUsuarios, true) ? 'selected' : '';
    $optionFiltroUsuarios .= "<option $sel value='{$uid}'>".escapeHtml($label)."</option>";
  }
}

// ================================
// Departamentos
// ================================
$arrDep = Departamento::getAll(null);
$arrDepList = $arrDep['output']['response'] ?? [];
$optionDep = Util::getDepartamentoPrincipal();
foreach ($arrDepList as $val) {
  $selected = ($val["codigo_departamento"] == Util::getDepartamentoPrincipal()) ? "selected" : "";
  $optionDep .= "<option $selected value='".escapeHtml($val['codigo_departamento'])."'>".escapeHtml($val['codigo_departamento'])." - ".escapeHtml($val['departamento'])."</option>";
}

// ================================
// Secretarías por municipio (Alcalde)
// ================================
$optionSecretariasMunicipios = "<option value=''>Seleccione</option>";
$responseSecretariasMunicipio = [];

if ($isUsuarioAlcalde && !empty($codigo_municipio_usuario)) {
  $arrSecMunicipio = SecretariasMunicipios::getByMunicipio(['codigo_municipio' => $codigo_municipio_usuario]);
  $isValidSecMunicipio = $arrSecMunicipio['output']['valid'] ?? false;
  $responseSecretariasMunicipio = $arrSecMunicipio['output']['response'] ?? [];
  if ($isValidSecMunicipio) {
    foreach ($responseSecretariasMunicipio as $linea) {
      $optionSecretariasMunicipios .= "<option value='".escapeHtml($linea['id'])."'>".escapeHtml($linea['secretaria'])."</option>";
    }
  }
}

// ================================
// Nombre municipio del usuario Alcalde
// ================================
$nombreMunicipioUsuario = '';
if ($isUsuarioAlcalde && !empty($codigo_municipio_usuario)) {
  $db = new DbConection();
  $pdo = $db->openConect();
  $stmtMun = $pdo->prepare("SELECT municipio FROM " . $db->getTable('tbl_ciudades_accion_unificada') . " WHERE codigo_muncipio = :codigo LIMIT 1");
  $stmtMun->bindParam(':codigo', $codigo_municipio_usuario);
  $stmtMun->execute();
  $resultMun = $stmtMun->fetch(PDO::FETCH_ASSOC);
  if ($resultMun) $nombreMunicipioUsuario = $resultMun['municipio'] ?? '';
  $db->closeConect();
}
?>

<body>

  <!-- Loader -->
  <div class="loader-bg">
    <div class="loader-track"><div class="loader-fill"></div></div>
  </div>

  <?php include './admin/include/navbar.php'; ?>
  <?php include './admin/include/header.php'; ?>

  <style>
    :root{
      --nav-blue:#20427F; --nav-blue-2:#132b52; --nav-blue-3:#2e58a8;
      --radius-xl:22px; --radius-lg:16px; --radius-md:14px;
      --shadow-soft: 0 14px 40px rgba(0,0,0,.28); --shadow-mid: 0 22px 70px rgba(0,0,0,.34);
      --ring: 0 0 0 .25rem rgba(96,165,250,.35);
      --safe-top: 96px;
    }
    html, body{ overflow-x: hidden !important; }
    .pcoded-content{ padding: calc(var(--safe-top) + 16px) 16px 18px !important; }
    @media(min-width:768px){ :root{ --safe-top: 112px; } .pcoded-content{ padding: calc(var(--safe-top) + 18px) 24px 24px !important; } }
    @media(min-width:1200px){ :root{ --safe-top: 120px; } .pcoded-content{ padding: calc(var(--safe-top) + 22px) 42px 34px !important; } }

    .form-section{
      background: rgba(255,255,255,.06) !important;
      border-radius: var(--radius-xl) !important;
      border: 1px solid rgba(255,255,255,.12) !important;
      padding: 20px !important;
    }
    .help-muted{ color: rgba(255,255,255,.6) !important; font-size:.82rem !important; font-weight:800 !important; margin-top:.35rem !important; }
    .file-pro{ padding:.65rem .75rem; border-radius:var(--radius-md); border:1px dashed rgba(255,255,255,.22); background:rgba(255,255,255,.06); }

    .btn-brutal{
      border-radius:14px !important; padding:.62rem 1.05rem !important;
      font-weight:1000 !important; letter-spacing:.2px;
      box-shadow:0 14px 34px rgba(0,0,0,.25);
      transition:transform .16s ease, box-shadow .16s ease, filter .16s ease;
      display:inline-flex; align-items:center; gap:8px; white-space:nowrap;
      color:#fff !important;
    }
    .btn-brutal.btn-sm{ padding:.32rem .55rem !important; border-radius:10px !important; gap:4px; font-size:11px !important; box-shadow:0 8px 18px rgba(0,0,0,.18); }
    .btn-brutal:hover{ transform:translateY(-1px); filter:brightness(1.04); box-shadow:0 18px 40px rgba(0,0,0,.28); }
    .btn-primary.btn-brutal{ background:linear-gradient(135deg,#3b82f6,#4f46e5) !important; border:1px solid rgba(255,255,255,.14) !important; }
    .btn-danger.btn-brutal{ background:linear-gradient(135deg,#ef4444,#b91c1c) !important; border:1px solid rgba(255,255,255,.14) !important; }
    .btn-secondary.btn-brutal{ background:rgba(255,255,255,.09) !important; border:1px solid rgba(255,255,255,.17) !important; }
    .btn-info.btn-brutal{ background:linear-gradient(135deg,#38bdf8,#0ea5e9) !important; border:1px solid rgba(255,255,255,.14) !important; }
    .btn-warning.btn-brutal{ background:linear-gradient(135deg,#f6c23e,#f59e0b) !important; border:1px solid rgba(255,255,255,.14) !important; color:#111827 !important; }

    .table-wrap{ display:flex; justify-content:center; padding:8px 0 2px; }
    .table-shell{
      width:min(100%,1520px);
      background: rgba(255,255,255,.06) !important;
      border-radius:24px; overflow:hidden;
      border:1px solid rgba(255,255,255,.12);
      box-shadow:var(--shadow-mid);
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
    .table-responsive--premium{ border-radius:18px; border:1px solid rgba(255,255,255,.10); overflow:auto; }

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
    #dynamictable .col-id,
    #dynamictable .col-fecha,
    #dynamictable .col-valor,
    #dynamictable .col-estado{ white-space:nowrap; }
    #dynamictable .col-proyecto,
    #dynamictable .col-obs{ white-space:normal; word-break:break-word; }
    #dynamictable .col-secretaria,
    #dynamictable .col-meta{ white-space:normal; word-break:break-word; font-size:9px !important; }
    #dynamictable .col-secretaria .sec-pill{ font-size:8.5px !important; padding:1px 4px; gap:2px; border-radius:5px; }
    #dynamictable .col-meta .sec-pill{ font-size:8.5px !important; padding:1px 4px; gap:2px; border-radius:5px; }
    #dynamictable .col-secretaria .sec-dot,
    #dynamictable .col-meta .meta-dot{ width:5px; height:5px; }
    #dynamictable tbody tr:nth-child(even) td{ background:rgba(255,255,255,.03) !important; }
    #dynamictable tbody tr:hover td{ background:rgba(255,255,255,.06) !important; }

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
    .dataTables_wrapper .dataTables_info,
    .dataTables_wrapper .dataTables_length label,
    .dataTables_wrapper .dataTables_filter label{ color:#fff !important; font-weight:800; }
    .dataTables_wrapper .dataTables_info{ font-size:12.5px; padding:10px 6px; }
    .table-responsive--premium{ overflow-x:auto; -webkit-overflow-scrolling:touch; }

    /* Filtros listado */
    .planeacion-filtros{
      display:flex; flex-wrap:wrap; gap:12px; align-items:flex-end;
      padding:14px 16px 4px; border-bottom:1px solid rgba(255,255,255,.08);
      background:rgba(0,0,0,.12);
    }
    .planeacion-filtros .filtro-field{ flex:1 1 220px; min-width:180px; }
    .planeacion-filtros .filtro-field--users{ flex:2 1 320px; }
    .planeacion-filtros .filtro-actions{ flex:0 0 auto; display:flex; gap:8px; padding-bottom:12px; }
    .planeacion-filtros label{
      display:block; font-size:11px; font-weight:800; letter-spacing:.04em;
      text-transform:uppercase; color:rgba(255,255,255,.72); margin-bottom:6px;
    }
    .planeacion-filtros .form-control,
    .planeacion-filtros select{
      border-radius:12px !important;
      border:1px solid rgba(255,255,255,.14) !important;
      background:rgba(11,27,56,.88) !important;
      color:#fff !important;
      min-height:40px;
    }

    /* Select2 dark — scoped a filtros (sin depender de clase JS) */
    .planeacion-filtros .select2-container,
    #modalAsignarProyecto .select2-container{
      width:100% !important;
    }
    .planeacion-filtros .select2-container .select2-selection--single,
    .planeacion-filtros .select2-container .select2-selection--multiple,
    #modalAsignarProyecto .select2-container .select2-selection--single,
    #modalAsignarProyecto .select2-container .select2-selection--multiple{
      position:relative !important;
      border-radius:12px !important;
      border:1px solid rgba(255,255,255,.22) !important;
      background:#0B1B38 !important;
      background-color:#0B1B38 !important;
      background-image:none !important;
      min-height:44px !important;
      color:#fff !important;
      box-shadow:inset 0 1px 0 rgba(255,255,255,.05) !important;
    }
    .planeacion-filtros .select2-container .select2-selection--single .select2-selection__rendered,
    #modalAsignarProyecto .select2-container .select2-selection--single .select2-selection__rendered{
      color:#fff !important;
      line-height:42px !important;
      padding-left:12px !important;
      padding-right:52px !important;
    }
    .planeacion-filtros .select2-container .select2-selection__placeholder,
    #modalAsignarProyecto .select2-container .select2-selection__placeholder{
      color:rgba(255,255,255,.55) !important;
    }
    .planeacion-filtros .select2-container .select2-selection--single .select2-selection__arrow,
    #modalAsignarProyecto .select2-container .select2-selection--single .select2-selection__arrow{
      height:42px !important;
    }
    .planeacion-filtros .select2-container .select2-selection--single .select2-selection__arrow b,
    #modalAsignarProyecto .select2-container .select2-selection--single .select2-selection__arrow b{
      border-color:#fff transparent transparent transparent !important;
    }
    .planeacion-filtros .select2-container .select2-selection--multiple .select2-selection__rendered,
    #modalAsignarProyecto .select2-container .select2-selection--multiple .select2-selection__rendered{
      display:flex !important;
      flex-wrap:wrap !important;
      gap:6px !important;
      padding:6px 36px 6px 8px !important;
      color:#fff !important;
    }
    .planeacion-filtros .select2-container .select2-selection--multiple .select2-selection__choice,
    #modalAsignarProyecto .select2-container .select2-selection--multiple .select2-selection__choice{
      background:#20427F !important;
      background-image:linear-gradient(135deg,#20427F,#2e58a8) !important;
      border:1px solid rgba(255,255,255,.25) !important;
      color:#fff !important;
      border-radius:999px !important;
      font-weight:800 !important;
      font-size:11px !important;
      padding:4px 10px 4px 8px !important;
      margin:0 !important;
      line-height:1.35 !important;
    }
    .planeacion-filtros .select2-container .select2-selection--multiple .select2-selection__choice__display,
    #modalAsignarProyecto .select2-container .select2-selection--multiple .select2-selection__choice__display{
      color:#fff !important;
    }
    .planeacion-filtros .select2-container .select2-selection--multiple .select2-selection__choice__remove,
    #modalAsignarProyecto .select2-container .select2-selection--multiple .select2-selection__choice__remove{
      color:#fff !important;
      margin-right:6px !important;
      font-weight:900 !important;
      border:none !important;
      background:transparent !important;
    }
    .planeacion-filtros .select2-container .select2-selection--multiple .select2-search--inline .select2-search__field,
    #modalAsignarProyecto .select2-container .select2-selection--multiple .select2-search--inline .select2-search__field{
      color:#fff !important;
      background:transparent !important;
      margin-top:4px !important;
      min-width:10em !important;
      caret-color:#fff !important;
    }
    .planeacion-filtros .select2-container .select2-selection--multiple .select2-search--inline .select2-search__field::placeholder,
    #modalAsignarProyecto .select2-container .select2-selection--multiple .select2-search--inline .select2-search__field::placeholder{
      color:rgba(255,255,255,.5) !important;
    }
    .planeacion-filtros .select2-container .select2-selection__clear,
    #modalAsignarProyecto .select2-container .select2-selection__clear{
      color:#fff !important;
      font-size:18px !important;
      font-weight:900 !important;
      position:absolute !important;
      right:10px !important;
      top:50% !important;
      transform:translateY(-50%) !important;
      z-index:3 !important;
      cursor:pointer !important;
      line-height:1 !important;
      opacity:.9 !important;
      margin:0 !important;
      float:none !important;
    }
    .planeacion-filtros .select2-container .select2-selection__clear:hover,
    #modalAsignarProyecto .select2-container .select2-selection__clear:hover{
      color:#fbbf24 !important;
    }
    .planeacion-filtros .select2-container.select2-container--focus .select2-selection--multiple,
    .planeacion-filtros .select2-container.select2-container--focus .select2-selection--single,
    .planeacion-filtros .select2-container.select2-container--open .select2-selection--multiple,
    .planeacion-filtros .select2-container.select2-container--open .select2-selection--single,
    #modalAsignarProyecto .select2-container.select2-container--focus .select2-selection--multiple,
    #modalAsignarProyecto .select2-container.select2-container--open .select2-selection--multiple{
      border-color:rgba(96,165,250,.65) !important;
      box-shadow:0 0 0 .15rem rgba(79,124,255,.2) !important;
    }

    /* Dropdown (se renderiza en body) */
    .select2-dropdown.planeacion-s2-drop,
    .select2-container--open .select2-dropdown.planeacion-s2-drop{
      background:#132b52 !important;
      background-color:#132b52 !important;
      border:1px solid rgba(255,255,255,.2) !important;
      color:#fff !important;
      border-radius:12px !important;
      overflow:hidden;
      box-shadow:0 18px 40px rgba(0,0,0,.5) !important;
      z-index:10050 !important;
    }
    .planeacion-s2-drop .select2-results__option{
      color:#fff !important;
      padding:8px 12px !important;
      font-weight:700 !important;
      background:transparent !important;
    }
    .planeacion-s2-drop .select2-results__option--highlighted[aria-selected],
    .planeacion-s2-drop .select2-results__option--highlighted[aria-selected=true]{
      background:#20427F !important;
      color:#fff !important;
    }
    .planeacion-s2-drop .select2-results__option[aria-selected=true]{
      background:rgba(32,66,127,.55) !important;
      color:#fff !important;
    }
    .planeacion-s2-drop .select2-search--dropdown{
      padding:8px !important;
      background:#132b52 !important;
    }
    .planeacion-s2-drop .select2-search--dropdown .select2-search__field{
      background:#0B1B38 !important;
      border:1px solid rgba(255,255,255,.22) !important;
      color:#fff !important;
      border-radius:10px !important;
      padding:8px 10px !important;
      caret-color:#fff !important;
    }
    .table-responsive--premium::after{ content:'↔ Desliza para ver más'; display:block; text-align:center; font-size:10px; color:rgba(255,255,255,.4); padding:4px 0 2px; }
    @media(min-width:992px){ .table-responsive--premium::after{ display:none; } }
    .table-empty{
      padding:26px 12px !important; text-align:center;
      color:rgba(255,255,255,.6) !important; font-weight:800;
    }

    @media (max-width:576px){
      .table-shell__top{ padding:16px; } .table-shell__body{ padding:12px; }
      .table-shell__badge{ width:100%; }
      .dataTables_wrapper .dataTables_filter{ text-align:left; margin-top:10px; }
      .btn-brutal{ width:100% !important; justify-content:center !important; }
    }

    .badge{ border-radius:999px !important; padding:.25rem .5rem !important; font-weight:1000 !important; letter-spacing:.2px; border:1px solid rgba(255,255,255,.12); font-size:10.5px !important; }
    .badge-warning-soft{ background:rgba(245,158,11,.25) !important; color:#fbbf24 !important; }
    .badge-success-soft{ background:rgba(22,163,74,.20) !important; color:#34d399 !important; }
    .badge-danger-soft{ background:rgba(220,38,38,.20) !important; color:#ef4444 !important; }
    .badge-secondary-soft{ background:rgba(148,163,184,.18) !important; color:#94a3b8 !important; }

    .sec-pill{
      display:inline-flex; align-items:center; gap:5px; padding:3px 7px;
      border-radius:8px; font-weight:800 !important; font-size:10.5px !important;
      border:1px solid rgba(255,255,255,.12); background:rgba(255,255,255,.06);
      color:rgba(255,255,255,.86); white-space:nowrap; margin-bottom:3px;
    }
    .sec-pill:last-child{ margin-bottom:0; }
    .sec-dot{ flex-shrink:0; width:6px; height:6px; border-radius:50%; background:linear-gradient(135deg,#60a5fa,#4f46e5); }
    .meta-pill{ background:rgba(52,211,153,.12); border-color:rgba(52,211,153,.20); color:#34d399; }
    .meta-dot{ flex-shrink:0; width:6px; height:6px; border-radius:50%; background:linear-gradient(135deg,#34d399,#15803d); }
    .pdf-pill{
      display:inline-flex; align-items:center; gap:5px; padding:4px 6px; border-radius:8px;
      border:1px solid rgba(239,68,68,.25); background:rgba(239,68,68,.10);
      color:#fca5a5; font-weight:700; font-size:11px;
      text-decoration:none; margin-bottom:2px; transition:background .15s;
    }
    .pdf-pill:hover{ background:rgba(239,68,68,.18); color:#fca5a5; text-decoration:none; }
    .pdf-pill:last-child{ margin-bottom:0; }
    .pdf-pill .pdf-icon{
      flex-shrink:0; width:18px; height:18px;
      background:linear-gradient(135deg,#ef4444,#b91c1c); border-radius:4px;
      display:flex; align-items:center; justify-content:center;
      color:#fff; font-size:.65rem;
    }
    .log-entry{
      background:rgba(255,255,255,.06) !important;
      border:1px solid rgba(255,255,255,.10) !important;
      border-radius:14px !important;
    }
  </style>

  <div class="pcoded-main-container">
    <div class="pcoded-content">

      <!-- Header -->
      <div class="page-header">
        <div class="page-block">
          <div class="row align-items-center">
            <div class="col-md-12">
              <div class="d-flex justify-content-between align-items-center">
                <h5 class="m-b-10">Ingreso Proyectos Planeación</h5>
                <div class="d-flex align-items-center" style="gap:8px;">
                  <?php if ($canInformes): ?>
                    <a href="informes_proyectos_planeacion_alcaldia.php" class="btn btn-warning btn-brutal btn-sm">
                      <i class="feather icon-pie-chart"></i> Informes
                    </a>
                  <?php endif; ?>
                  <?php if ($canDashboard): ?>
                    <a href="dashboard_proyectos_planeacion_alcaldia.php" class="btn btn-info btn-brutal btn-sm">
                      <i class="feather icon-bar-chart-2"></i> Dashboard
                    </a>
                  <?php endif; ?>
                  <?php include './admin/include/btn_back.php'; ?>
                </div>
              </div>
              <ul class="breadcrumb">
                <li class="breadcrumb-item"><a href="index.php"><i class="feather icon-home"></i></a></li>
                <li class="breadcrumb-item"><a href="#!">Banco de Proyectos / Ingreso Proyectos Planeación</a></li>
              </ul>
            </div>
          </div>
        </div>
      </div>

      <!-- FORM -->
      <?php if ($canCreate): ?>
      <div class="row">
        <div class="col-sm-12">
          <div class="card">
            <div class="card-header flex-wrap">
              <div>
                <h5 id="formPlaneacionTitle">Formulario de Ingreso de Proyecto Planeación Alcaldía</h5>
                <div class="text-muted" id="formPlaneacionSubtitle" style="font-weight:800; font-size:.85rem; margin-top:4px;">
                  Completa la información y adjunta <b>foto</b> + <b>PDF</b> para radicar el proyecto.
                </div>
                <div id="bannerEdicionRechazado" class="mt-2" style="display:none;">
                  <span class="badge badge-warning-soft">Editando proyecto rechazado #<span id="bannerEditId"></span> — al guardar se reenvía a Planeación</span>
                </div>
              </div>
              <div class="card-header-right">
                <div class="btn-group card-option">
                  <button type="button" class="btn dropdown-toggle btn-icon" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    <i class="feather icon-more-horizontal"></i>
                  </button>
                  <ul class="list-unstyled card-option dropdown-menu dropdown-menu-right">
                    <li class="dropdown-item full-card"><a href="#!"><span><i class="feather icon-maximize"></i> Maximizar</span><span style="display:none"><i class="feather icon-minimize"></i> Restaurar</span></a></li>
                    <li class="dropdown-item minimize-card"><a href="#!"><span><i class="feather icon-minus"></i> Colapsar</span><span style="display:none"><i class="feather icon-plus"></i> Expandir</span></a></li>
                  </ul>
                </div>
              </div>
            </div>

            <div class="card-body">
              <div class="form-section">
                <form id="formsecretaria" role="form" autocomplete="off" data-modo="crear">
                  <input type="hidden" name="id" id="proyecto_edit_id" value="">

                  <!-- hidden dep (para JS) -->
                  <div style="display:none;">
                    <select class="form-control" onchange="DEPARTAMENTO.getMunicipios();" id="tbl_departamento_id" name="tbl_departamento_id" disabled>
                      <?php echo $optionDep; ?>
                    </select>
                  </div>

                  <div class="form-row">
                    <div class="form-group col-md-3">
                      <label for="date">Fecha<span class="text-danger">*</span></label>
                      <input class="form-control" name="date" id="date" type="date" required>
                    </div>
                    <div class="form-group col-md-9">
                      <label for="proyecto">Nombre del Proyecto<span class="text-danger">*</span></label>
                      <input type="text" class="form-control" id="proyecto" name="proyecto" placeholder="Describa el objeto del proyecto brevemente" autocomplete="off" required/>
                      <div class="help-muted">Tip: inicia con "Construcci&oacute;n de&hellip;", "Mejoramiento de&hellip;", "Dotaci&oacute;n de&hellip;".</div>
                    </div>
                  </div>

                  <div class="form-row">
                    <div class="form-group col-md-6">
                      <label for="tbl_municipio_id">Municipio Beneficiado<span class="text-danger">*</span></label>
                      <?php if ($isUsuarioAlcalde && !empty($codigo_municipio_usuario)) : ?>
                        <input type="text" class="form-control" value="<?= escapeHtml($nombreMunicipioUsuario) ?>" readonly disabled>
                        <input type="hidden" id="tbl_municipio_id" name="tbl_municipio_id" value="<?= escapeHtml($codigo_municipio_usuario) ?>">
                        <div class="help-muted">Este municipio est&aacute; fijo por tu rol.</div>
                      <?php else : ?>
                        <select class="form-control" id="tbl_municipio_id" name="tbl_municipio_id" required></select>
                        <div class="help-muted">Selecciona el municipio/alcald&iacute;a para cargar secretar&iacute;as y metas del PDD.</div>
                      <?php endif; ?>
                    </div>

                    <div class="form-group col-md-6">
                      <label>Secretar&iacute;a Alcald&iacute;a<span class="text-danger">*</span></label>
                      <div id="secrePills" class="pill-select" style="display:flex;flex-wrap:wrap;gap:8px;padding:10px 12px;border-radius:14px;border:1px solid rgba(255,255,255,.14);background:rgba(255,255,255,.06);min-height:46px;"></div>
                      <select class="form-control" id="tbl_secretarias_id" name="tbl_secretarias_id[]" multiple style="display:none;" required>
                        <?php
                        // Evitar option vacía en multi-select; las opciones reales ya vienen en $optionSecretariasMunicipios
                        echo str_replace("<option value=''>Seleccione</option>", '', $optionSecretariasMunicipios);
                        ?>
                      </select>
                      <div class="help-muted">Dependencia responsable del proyecto. Haz clic para seleccionar.</div>
                    </div>
                  </div>

                  <div class="form-row">
                    <div class="form-group col-md-8">
                      <label>Meta del Plan de Desarrollo Relacionada<span class="text-danger">*</span></label>
                      <div id="metaPills" class="pill-select" style="display:flex;flex-wrap:wrap;gap:8px;padding:10px 12px;border-radius:14px;border:1px solid rgba(255,255,255,.14);background:rgba(255,255,255,.06);min-height:46px;max-height:220px;overflow-y:auto;"></div>
                      <select class="form-control" id="tbl_meta_id" name="tbl_meta_id[]" multiple style="display:none;" required>
                        <?php if (!empty($arrMetas)) : ?>
                          <?php foreach ($arrMetas as $meta) : ?>
                            <option value="<?= escapeHtml($meta['id'] ?? '') ?>">
                              <?= escapeHtml(trim(($meta['eje_estrategico'] ?? '') . ' - ' . ($meta['sector_pdd'] ?? ''), ' -')) ?>
                            </option>
                          <?php endforeach; ?>
                        <?php endif; ?>
                      </select>
                      <div class="help-muted">Conecta el proyecto con el PDD para trazabilidad. Haz clic para seleccionar.</div>
                    </div>

                    <div class="form-group col-md-4">
                      <label for="valor_proyecto">Valor del Proyecto ($)<span class="text-danger">*</span></label>
                      <input type="number" class="form-control" id="valor_proyecto" name="valor_proyecto" placeholder="Ingrese el valor del proyecto" required onKeyPress="return soloNumeros(event);" />
                      <div class="help-muted">Solo n&uacute;meros (seg&uacute;n tu validaci&oacute;n).</div>
                    </div>
                  </div>

                  <div class="form-row">
                    <div class="form-group col-md-12">
                      <label for="observaciones">Observaciones</label>
                      <textarea class="form-control" id="observaciones" name="observaciones" placeholder="Ingrese observaciones adicionales del proyecto" rows="3"></textarea>
                      <div class="help-muted">Incluye alcance, poblaci&oacute;n beneficiada, localizaci&oacute;n, estado actual, etc.</div>
                    </div>
                  </div>

                  <div class="form-row">
                    <div class="form-group col-md-6">
                      <label for="foto2">Fotograf&iacute;a del Proyecto<span class="text-danger create-only-req">*</span></label>
                      <div class="file-pro">
                        <input type="file" class="form-control-file" id="foto2" name="foto2" accept=".jpg,.jpeg,.png,.gif,.bmp,.webp"/>
                        <div class="help-muted">JPG, PNG, GIF, WEBP <span class="edit-file-hint" style="display:none;">· opcional al editar (se conserva la actual si no cambia)</span></div>
                        <div id="link_foto_actual" class="mt-1" style="display:none;"></div>
                      </div>
                    </div>

                    <div class="form-group col-md-6">
                      <label for="documento2">Documento PDF<span class="text-danger create-only-req">*</span></label>
                      <div class="file-pro">
                        <input type="file" class="form-control-file" id="documento2" name="documento2" accept=".pdf"/>
                        <div class="help-muted">PDF <span class="edit-file-hint" style="display:none;">· opcional al editar (se conserva el actual si no cambia)</span></div>
                        <div id="link_documento_actual" class="mt-1" style="display:none;"></div>
                      </div>
                    </div>
                  </div>

                  <div class="form-row">
                    <div class="form-group col-md-6">
                      <label for="documento3">Documento PDF 2 <span class="text-muted small">(Opcional)</span></label>
                      <div class="file-pro">
                        <input type="file" class="form-control-file" id="documento3" name="documento3" accept=".pdf"/>
                        <div class="help-muted">PDF</div>
                      </div>
                    </div>
                    <div class="form-group col-md-6">
                      <label for="documento4">Documento PDF 3 <span class="text-muted small">(Opcional)</span></label>
                      <div class="file-pro">
                        <input type="file" class="form-control-file" id="documento4" name="documento4" accept=".pdf"/>
                        <div class="help-muted">PDF</div>
                      </div>
                    </div>
                  </div>

                  <div class="form-row">
                    <div class="form-group col-md-6">
                      <label for="documento5">Documento PDF 4 <span class="text-muted small">(Opcional)</span></label>
                      <div class="file-pro">
                        <input type="file" class="form-control-file" id="documento5" name="documento5" accept=".pdf"/>
                        <div class="help-muted">PDF</div>
                      </div>
                    </div>
                    <div class="form-group col-md-6">
                      <label for="documento6">Documento PDF 5 <span class="text-muted small">(Opcional)</span></label>
                      <div class="file-pro">
                        <input type="file" class="form-control-file" id="documento6" name="documento6" accept=".pdf"/>
                        <div class="help-muted">PDF</div>
                      </div>
                    </div>
                  </div>

                  <div class="form-row pt-3">
                    <div class="col text-center">
                      <button type="button" id="btnCancelarProyecto" class="btn btn-danger btn-brutal mr-3">
                        <i class="feather icon-x-circle"></i> Cancelar
                      </button>
                      <button type="button" id="btnIngresarProyecto" class="btn btn-primary btn-brutal ml-3">
                        <i class="feather icon-check-circle"></i> <span id="btnIngresarProyectoLabel">Ingresar Proyecto</span>
                      </button>
                    </div>
                  </div>

                </form>
              </div>
            </div>

          </div>
        </div>
      </div>
      <?php endif; ?>

      <!-- LISTADO -->
      <div class="row">
        <div class="col-sm-12">
          <div class="card">
            <div class="card-header flex-wrap">
              <div>
                <h5>Listado de Proyectos de Secretaría</h5>
                <div class="text-muted" style="font-weight:800; font-size:.85rem; margin-top:4px;">
                  Consulta el estado, descarga PDF y revisa el historial de acciones.
                  <?php if ($canViewAll): ?>
                    <span class="badge badge-info ml-1">Vista departamental</span>
                  <?php elseif ($canViewAllAlcaldia || ($scopeActual['mode'] ?? '') === 'municipio'): ?>
                    <span class="badge badge-success-soft ml-1">Vista alcaldía</span>
                  <?php elseif (($scopeActual['mode'] ?? '') === 'asignados'): ?>
                    <span class="badge badge-warning-soft ml-1">Solo asignados</span>
                  <?php endif; ?>
                </div>
              </div>

              <div class="card-header-right">
                <div class="btn-group card-option">
                  <button type="button" class="btn dropdown-toggle btn-icon" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    <i class="feather icon-more-horizontal"></i>
                  </button>
                  <ul class="list-unstyled card-option dropdown-menu dropdown-menu-right">
                    <li class="dropdown-item full-card"><a href="#!"><span><i class="feather icon-maximize"></i> Maximizar</span><span style="display:none"><i class="feather icon-minimize"></i> Restaurar</span></a></li>
                    <li class="dropdown-item minimize-card"><a href="#!"><span><i class="feather icon-minus"></i> Colapsar</span><span style="display:none"><i class="feather icon-plus"></i> Expandir</span></a></li>
                  </ul>
                </div>
              </div>
            </div>

            <?php if ($mostrarFiltrosListado): ?>
            <form method="get" action="proyectos_planeacion_alcaldia.php" id="formFiltrosPlaneacion" class="planeacion-filtros">
              <?php if ($mostrarFiltroMunicipio): ?>
              <div class="filtro-field">
                <label for="filtro_municipio">Municipio</label>
                <select name="filtro_municipio" id="filtro_municipio" class="form-control">
                  <?= $optionFiltroMunicipios ?>
                </select>
              </div>
              <?php endif; ?>

              <?php if ($mostrarFiltroUsuarios): ?>
              <div class="filtro-field filtro-field--users">
                <label for="filtro_usuarios">Usuario(s)</label>
                <select name="filtro_usuarios[]" id="filtro_usuarios" class="form-control" multiple="multiple"
                        data-placeholder="Todos los usuarios">
                  <?= $optionFiltroUsuarios ?>
                </select>
              </div>
              <?php endif; ?>

              <div class="filtro-actions">
                <button type="submit" class="btn btn-primary btn-brutal btn-sm">
                  <i class="feather icon-filter"></i> Filtrar
                </button>
                <a href="proyectos_planeacion_alcaldia.php" class="btn btn-secondary btn-brutal btn-sm">
                  <i class="feather icon-x"></i> Limpiar
                </a>
              </div>
            </form>
            <?php endif; ?>

            <div class="card-body p-0">
              <div class="table-wrap">
                <div class="table-shell">
                  <div class="table-shell__top">
                    <div>
                      <div class="table-shell__eyebrow">Banco de Proyectos</div>
                      <h3 class="table-shell__title">Listado de Proyectos de Secret&aacute;r&iacute;a</h3>
                      <div class="table-shell__subtitle">Consulta el estado, descarga el PDF y revisa el historial de acciones de cada proyecto.</div>
                    </div>
                    <div class="table-shell__badge">Vista Pro</div>
                  </div>
                  <div class="table-shell__body">
                    <div class="table-responsive table-responsive--premium p-0">
                      <table id="dynamictable" class="table table-hover table-bordered table-sm w-100">
                        <thead>
                          <tr>
                            <th>ID</th>
                            <th>Fecha</th>
                            <th>Municipio</th>
                            <th>Nombre del Proyecto</th>
                            <th>Secret&aacute;r&iacute;a</th>
                            <th>Meta Relacionada</th>
                            <th>Valor ($)</th>
                            <th>Obs. Planeaci&oacute;n</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                          </tr>
                        </thead>
                        <tbody>
                          <?php if ($isvalidProyectos && !empty($arrProyectos)) : ?>
                            <?php foreach ($arrProyectos as $proyecto) : ?>
                              <tr>
                                <td class="col-id text-center"><?= escapeHtml($proyecto['id'] ?? '') ?></td>
                                <td class="col-fecha text-center"><?= escapeHtml($proyecto['fecha'] ?? '') ?></td>
                                <td><?= escapeHtml($proyecto['nombre_municipio'] ?? '') ?></td>
                                <td class="col-proyecto" title="<?= escapeHtml($proyecto['proyecto'] ?? '') ?>"><?= escapeHtml($proyecto['proyecto'] ?? '') ?></td>

                                <td class="col-secretaria">
                                  <?php
                                    $secretarias = array_filter(array_map('trim', explode(',', $proyecto['nombre_secretaria'] ?? '')));
                                    foreach ($secretarias as $sec):
                                      $sec = htmlspecialchars($sec, ENT_QUOTES, 'UTF-8');
                                  ?>
                                    <span class="sec-pill" title="<?= $sec ?>">
                                      <span class="sec-dot"></span>
                                      <?= strtoupper($sec) ?>
                                    </span>
                                  <?php endforeach; ?>
                                </td>

                                <td class="col-meta">
                                  <?php
                                    $metas = array_filter(array_map('trim', explode(',', $proyecto['nombre_meta'] ?? '')));
                                    foreach ($metas as $meta):
                                      $meta = htmlspecialchars($meta, ENT_QUOTES, 'UTF-8');
                                  ?>
                                    <span class="sec-pill meta-pill" title="<?= $meta ?>">
                                      <span class="meta-dot"></span>
                                      <?= $meta ?>
                                    </span>
                                  <?php endforeach; ?>
                                </td>

                                <td class="col-valor text-center">$<?= number_format((float)($proyecto['valor_proyecto'] ?? 0), 0, ',', '.') ?></td>
                                <td class="col-obs" title="<?= escapeHtml($proyecto['secretario_planeacion'] ?? '') ?>"><?= escapeHtml($proyecto['secretario_planeacion'] ?? '') ?></td>

                                <td class="col-estado text-center">
                                  <?php
                                    $estado = $proyecto['estado_proyecto'] ?? '';
                                    $cls = 'badge-secondary-soft';
                                    if ($estado === 'Enviado')   $cls = 'badge-warning-soft';
                                    elseif ($estado === 'Rechazado') $cls = 'badge-danger-soft';
                                    elseif ($estado === 'Aprobado')  $cls = 'badge-success-soft';
                                  ?>
                                  <span class="badge <?= $cls ?>"><?= escapeHtml($estado) ?></span>
                                </td>

                                <td class="text-center" style="white-space:nowrap;vertical-align:middle;">
                                  <?php if ($canAssign): ?>
                                    <button type="button" class="btn btn-info btn-brutal btn-sm" onclick="abrirAsignarProyecto(<?= (int)($proyecto['id'] ?? 0) ?>, '<?= escapeHtml($proyecto['proyecto'] ?? '') ?>')" title="Asignar usuarios">
                                      <i class="feather icon-user-plus"></i>
                                    </button>
                                  <?php endif; ?>
                                  <?php if ($canDetail): ?>
                                    <a class="btn btn-primary btn-brutal btn-sm" href="reporte-proyecto-planeacion-alcaldia.php?id=<?= (int)($proyecto['id'] ?? 0) ?>" title="Ver detalle">
                                      <i class="feather icon-eye"></i>
                                    </a>
                                  <?php endif; ?>
                                  <?php if ($canManage && $estado === 'Enviado'): ?>
                                    <a class="btn btn-success btn-brutal btn-sm" href="reporte-proyecto-planeacion-alcaldia.php?id=<?= (int)($proyecto['id'] ?? 0) ?>&modo=gestionar" title="Gestionar">
                                      <i class="feather icon-check-square"></i>
                                    </a>
                                  <?php endif; ?>
                                  <?php if ($canReopen && $estado === 'Aprobado'): ?>
                                    <button type="button" class="btn btn-warning btn-brutal btn-sm" onclick="reabrirProyectoPlaneacion(<?= (int)($proyecto['id'] ?? 0) ?>)" title="Reabrir">
                                      <i class="feather icon-refresh-cw"></i>
                                    </button>
                                  <?php endif; ?>
                                  <?php if ($estado === 'Rechazado') : ?>
                                    <button class="btn btn-warning btn-brutal btn-sm" onclick="editarProyectoRechazado(<?= (int)($proyecto['id'] ?? 0) ?>)" title="Editar y reenviar">
                                      <i class="feather icon-edit"></i>
                                    </button>
                                    <button class="btn btn-danger btn-brutal btn-sm" onclick="verMotivoRechazo(<?= (int)($proyecto['id'] ?? 0) ?>)" title="Ver motivo de rechazo">
                                      <i class="feather icon-alert-circle"></i>
                                    </button>
                                  <?php else : ?>
                                    <?php
                                      $docKeys = ['documento2','documento3','documento4','documento5','documento6'];
                                      $docNum  = 1;
                                      foreach ($docKeys as $dk):
                                        $docUrl = $proyecto[$dk] ?? '';
                                        if (empty($docUrl)) continue;
                                        $fn = basename($docUrl);
                                        $fu = 'uploads/proyectos_secretarias/' . $fn;
                                    ?>
                                      <a href="<?= escapeHtml($fu) ?>" target="_blank" class="btn btn-info btn-brutal btn-sm" title="PDF <?= $docNum ?>: <?= escapeHtml($fn) ?>" style="padding:.28rem .45rem !important;border-radius:8px !important;">
                                        <i class="feather icon-file-text"></i>
                                      </a>
                                    <?php $docNum++; endforeach; ?>
                                  <?php endif; ?>
                                  <button class="btn btn-secondary btn-brutal btn-sm" onclick="verLogs(<?= (int)($proyecto['id'] ?? 0) ?>, '<?= escapeHtml($proyecto['proyecto'] ?? '') ?>')" title="Historial">
                                    <i class="feather icon-clock"></i>
                                  </button>
                                </td>
                              </tr>
                            <?php endforeach; ?>
                          <?php else : ?>
                            <tr>
                              <td colspan="10" class="table-empty">No hay proyectos ingresados en tu secretar&iacute;a.</td>
                            </tr>
                          <?php endif; ?>
                        </tbody>
                      </table>
                    </div>
                  </div>
                </div>
              </div>
            </div>

          </div>
        </div>
      </div>

    </div>
  </div>

  <?php include './admin/include/footer.php'; ?>
  <?php include 'admin/include/gerenic_script.php'; ?>

  <script>
    const userSecretariaId = <?php echo json_encode($id_secretaria_usuario); ?>;
    const isUsuarioAlcalde = <?php echo json_encode($isUsuarioAlcalde); ?>;
    const codigoMunicipioUsuario = <?php echo json_encode($codigo_municipio_usuario); ?>;
    const mostrarFiltroMunicipio = <?php echo json_encode((bool)$mostrarFiltroMunicipio); ?>;
    const mostrarFiltroUsuarios = <?php echo json_encode((bool)$mostrarFiltroUsuarios); ?>;
  </script>

  <script src="assets/js/vendor-all.min.js"></script>
  <script src="assets/js/plugins/bootstrap.min.js"></script>
  <script src="assets/js/pcoded.min.js"></script>
  <!-- Select2 tras vendor-all: este último reescribe jQuery y pierde el plugin -->
  <script src="./plugins/select2/js/select2.full.min.js"></script>

  <script src="<?php echo Util::versionar('./admin/js/departamento.js'); ?>"></script>
  <script src="<?php echo Util::versionar('./admin/js/proyectos_planeacion_alcalde.js'); ?>"></script>

  <script>
    <?php if (!$isUsuarioAlcalde) : ?>
      setTimeout(function() {
        if (typeof UTIL !== 'undefined' && UTIL.getDepartamentoPrincipal) {
          $("#tbl_departamento_id").val(UTIL.getDepartamentoPrincipal());
        }
        if (typeof DEPARTAMENTO !== 'undefined') {
          DEPARTAMENTO.getMunicipios();
        }
      }, 400);
    <?php else : ?>
      setTimeout(function() {
        if (typeof PROYECTOSSECRETARIA !== 'undefined') {
          PROYECTOSSECRETARIA.onMunicipioChange();
        }
      }, 400);
    <?php endif; ?>
  </script>

  <script>
    function verLogs(proyectoId, nombreProyecto) {
      $.ajax({
        url: 'admin/ajax/rqst.php',
        type: 'POST',
        dataType: 'json',
        data: { op: 'obtener_logs_proyecto', id: proyectoId },
        success: function(response) {
          if (response.output.valid) {
            const logs = response.output.response || [];
            let logsHtml = '';
            let edicionHtml = '';
            const logsEdicion = logs.filter(log => log.accion === 'Reenviado/Editado');

            if (logsEdicion.length > 0) {
              edicionHtml = `
                <h6 style="font-weight:1000;">Historial de Reenvío/Edición</h6>
                <div class="table-responsive mb-4">
                  <table class="table table-sm table-bordered table-striped mb-0">
                    <thead>
                      <tr>
                        <th>Fecha de Reenvío</th>
                        <th>Usuario</th>
                        <th>Acción</th>
                        <th>Documento</th>
                      </tr>
                    </thead>
                    <tbody>
              `;
              logsEdicion.forEach(function(log) {
                const documentoRuta = log.documento_ruta;
                const fileUrl = documentoRuta ? `${documentoRuta}` : '#';
                const fileName = documentoRuta ? documentoRuta.split('/').pop() : 'N/A';

                edicionHtml += `
                  <tr>
                    <td>${log.dtcreated}</td>
                    <td>${log.usuario || 'N/A'}</td>
                    <td>${log.accion}</td>
                    <td>
                      ${documentoRuta ?
                        `<a href="${fileUrl}" target="_blank" class="btn btn-sm btn-info btn-brutal" title="Descargar ${fileName}">
                          <i class="uil uil-file-download-alt fs-5"></i> PDF
                        </a>`
                        : `<span class="text-muted">No Aplica</span>`
                      }
                    </td>
                  </tr>
                `;
              });
              edicionHtml += `
                    </tbody>
                  </table>
                </div>
              `;
            } else {
              edicionHtml = '<p class="alert" style="background:rgba(100,116,139,.16); color:#0f172a; border-radius:12px; font-weight:1000; border:1px solid rgba(15,23,42,.10);">Este proyecto aún no ha sido reenviado o editado después de ser rechazado.</p>';
            }

            logsHtml += `<h6 style="font-weight:1000;">Historial Completo del Proyecto</h6>`;
            if (logs.length > 0) {
              logs.forEach(function(log) {
                const badge = (log.accion === 'Rechazado') ? 'badge-danger-soft' : (log.accion === 'Aprobado' ? 'badge-success-soft' : 'badge-secondary-soft');
                logsHtml += `
                  <div class="log-entry p-3 mb-2">
                    <p class="mb-1"><strong>Fecha:</strong> ${log.dtcreated}</p>
                    <p class="mb-1"><strong>Acción:</strong> <span class="badge ${badge}">${log.accion}</span></p>
                    <p class="mb-1"><strong>Observación:</strong> ${log.observacion}</p>
                    <p class="mb-0"><strong>Usuario:</strong> ${log.usuario || 'N/A'}</p>
                  </div>
                `;
              });
            } else {
              logsHtml += '<p class="text-muted">No se encontraron logs para este proyecto.</p>';
            }

            const finalHtml = edicionHtml + '<hr>' + logsHtml;
            $('#logs_title').text(`Acciones del proyecto: ${nombreProyecto}`);
            $('#logs_body').html(finalHtml);
            $('#btn_descargar_logs').attr('href', `descargar_logs_planeacion_alcaldia.php?id=${proyectoId}&proyecto=${encodeURIComponent(nombreProyecto)}`);
            $('#logsModal').modal('show');
          } else {
            Swal.fire('Error', 'No se pudieron cargar las acciones: ' + (response.output.response?.content || ''), 'error');
          }
        },
        error: function() {
          Swal.fire('Error de conexión', 'No se pudo conectar con el servidor para obtener los logs.', 'error');
        }
      });
    }

    function reabrirProyectoPlaneacion(proyectoId) {
      Swal.fire({
        title: 'Reabrir proyecto',
        input: 'textarea',
        inputLabel: 'Nota de reapertura',
        inputValue: 'Proyecto reabierto para nueva gestión.',
        showCancelButton: true,
        confirmButtonText: 'Reabrir',
        cancelButtonText: 'Cancelar',
        preConfirm: (nota) => {
          if (!nota || !String(nota).trim()) {
            Swal.showValidationMessage('La nota es obligatoria');
          }
          return nota;
        }
      }).then((result) => {
        if (!result.isConfirmed) return;
        $.ajax({
          url: 'admin/ajax/rqst.php',
          type: 'POST',
          dataType: 'json',
          data: { op: 'reabrir_proyecto_planeacion', id: proyectoId, nota: result.value },
          success: function(resp) {
            if (resp.output && resp.output.valid) {
              Swal.fire('OK', resp.output.response.content || 'Reabierto', 'success').then(() => location.reload());
            } else {
              Swal.fire('Error', (resp.output && resp.output.response && resp.output.response.content) || 'No se pudo reabrir', 'error');
            }
          },
          error: function() {
            Swal.fire('Error', 'No se pudo conectar con el servidor.', 'error');
          }
        });
      });
    }

    function verMotivoRechazo(proyectoId) {
      $.ajax({
        url: 'admin/ajax/rqst.php',
        type: 'POST',
        dataType: 'json',
        data: { op: 'obtener_detalles_proyecto', id: proyectoId },
        success: function(response) {
          if (!(response.output && response.output.valid)) {
            Swal.fire('Error', (response.output && response.output.response && response.output.response.content) || 'Sin datos', 'error');
            return;
          }
          const p = response.output.response || {};
          const nota = p.gestion_nota || p.secretario_planeacion || 'Sin nota registrada';
          Swal.fire({
            title: 'Motivo del rechazo',
            html: '<div style="text-align:left;white-space:pre-wrap;">' + $('<div/>').text(nota).html() + '</div>',
            icon: 'warning',
            confirmButtonText: 'Cerrar',
            showDenyButton: true,
            denyButtonText: 'Editar y reenviar'
          }).then((r) => {
            if (r.isDenied) editarProyectoRechazado(proyectoId);
          });
        },
        error: function() {
          Swal.fire('Error', 'No se pudo cargar el motivo.', 'error');
        }
      });
    }

    function abrirAsignarProyecto(proyectoId, nombreProyecto) {
      $('#asignar_proyecto_id').val(proyectoId);
      $('#asignar_proyecto_nombre').text(nombreProyecto || ('#' + proyectoId));
      $('#asignar_usuarios_list').html('<div class="help-muted">Cargando usuarios con permiso de gestión…</div>');
      $('#modalAsignarProyecto').modal('show');

      $.ajax({
        url: 'admin/ajax/rqst.php',
        type: 'POST',
        dataType: 'json',
        data: { op: 'planeacion_usuarios_asignables', proyecto_id: proyectoId },
        success: function (resp) {
          if (!(resp.output && resp.output.valid)) {
            $('#asignar_usuarios_list').html('<div class="text-danger">' + ((resp.output && resp.output.response && resp.output.response.content) || 'Error') + '</div>');
            return;
          }
          const users = resp.output.response || [];
          if (!users.length) {
            $('#asignar_usuarios_list').html('<div class="help-muted">No hay usuarios de esta alcaldía con permiso de gestión.</div>');
            return;
          }

          let html = '<label for="asignar_usuarios_select" class="d-block mb-2" style="color:rgba(255,255,255,.72);font-weight:800;font-size:.78rem;text-transform:uppercase;">Usuarios</label>';
          html += '<select id="asignar_usuarios_select" class="form-control" multiple="multiple" data-placeholder="Seleccione uno o varios usuarios">';
          users.forEach(function (u) {
            const selected = u.asignado ? 'selected' : '';
            const label = ((u.nombre || '') + ' ' + (u.apellido || '')).trim() || u.nickname;
            const sub = (u.nickname || '') + (u.tipo ? (' · ' + u.tipo) : '');
            const text = sub ? (label + ' — ' + sub) : label;
            html += '<option value="' + u.id + '" ' + selected + '>' + $('<div/>').text(text).html() + '</option>';
          });
          html += '</select>';
          $('#asignar_usuarios_list').html(html);

          if (typeof $.fn.select2 === 'function') {
            planeacionSelect2Theme($('#asignar_usuarios_select'), {
              placeholder: 'Seleccione uno o varios usuarios',
              allowClear: true,
              closeOnSelect: false,
              dropdownParent: $('#modalAsignarProyecto')
            });
          }
        },
        error: function () {
          $('#asignar_usuarios_list').html('<div class="text-danger">Error de conexión</div>');
        }
      });
    }

    function guardarAsignacionProyecto() {
      const proyectoId = $('#asignar_proyecto_id').val();
      let ids = [];
      if ($('#asignar_usuarios_select').length) {
        ids = ($('#asignar_usuarios_select').val() || []).filter(function (v) {
          return v !== null && v !== undefined && String(v).trim() !== '';
        });
      } else {
        $('.asign-user-check:checked').each(function () { ids.push($(this).val()); });
      }

      $.ajax({
        url: 'admin/ajax/rqst.php',
        type: 'POST',
        dataType: 'json',
        data: {
          op: 'planeacion_asignar_usuarios',
          proyecto_id: proyectoId,
          accion: 'set',
          'usuario_ids[]': ids
        },
        traditional: true,
        success: function (resp) {
          if (resp.output && resp.output.valid) {
            Swal.fire('OK', resp.output.response.content || 'Asignación guardada', 'success').then(function () {
              location.reload();
            });
          } else {
            Swal.fire('Error', (resp.output && resp.output.response && resp.output.response.content) || 'Error', 'error');
          }
        },
        error: function () {
          Swal.fire('Error', 'No se pudo conectar', 'error');
        }
      });
    }

    function editarProyectoRechazado(proyectoId) {
      if (typeof PROYECTOSSECRETARIA !== 'undefined' && PROYECTOSSECRETARIA.cargarEdicionRechazado) {
        PROYECTOSSECRETARIA.cargarEdicionRechazado(proyectoId);
      } else {
        Swal.fire('Error', 'No se pudo cargar el formulario de edición.', 'error');
      }
    }
  </script>

  <?php include 'admin/include/scriptsgober360.php'; ?>
  <script src="vendors/flatpickr/flatpickr.min.js"></script>

  <script>
    function planeacionSelect2Theme($el, opts) {
      if (typeof $.fn.select2 !== 'function' || !$el.length) return;
      if ($el.hasClass('select2-hidden-accessible')) {
        $el.select2('destroy');
      }
      // allowClear en multiple requiere placeholder + opción vacía
      if ($el.prop('multiple') && !$el.find('option[value=""]').length) {
        $el.prepend('<option value=""></option>');
      }
      var cfg = $.extend({
        width: '100%',
        allowClear: true,
        closeOnSelect: false,
        placeholder: $el.data('placeholder') || 'Seleccione…',
        dropdownCssClass: 'planeacion-s2-drop',
        language: {
          noResults: function () { return 'Sin resultados'; },
          searching: function () { return 'Buscando…'; },
          removeAllItems: function () { return 'Borrar selección'; }
        }
      }, opts || {});
      $el.select2(cfg);
      // Forzar clase en el contenedor real de Select2 4.0
      var $container = ($el.data('select2') && $el.data('select2').$container)
        ? $el.data('select2').$container
        : $el.nextAll('.select2-container').first();
      $container.addClass('planeacion-s2');
      // Parche inline por si algún CSS externo pisa el fondo
      $container.find('.select2-selection').css({
        background: '#0B1B38',
        backgroundColor: '#0B1B38',
        color: '#fff',
        borderColor: 'rgba(255,255,255,.22)'
      });
      $container.find('.select2-selection__rendered').css({
        color: '#fff',
        backgroundColor: 'transparent',
        background: 'transparent',
        border: 'none',
        height: 'auto'
      });
      $container.find('.select2-selection__placeholder, .select2-search__field').css({
        color: '#fff'
      });
    }

    function initFiltrosPlaneacionListado() {
      if (typeof mostrarFiltroUsuarios === 'undefined' || !mostrarFiltroUsuarios) return;

      var $usuarios = $('#filtro_usuarios');
      if (!$usuarios.length) return;

      planeacionSelect2Theme($usuarios, {
        placeholder: $usuarios.data('placeholder') || 'Todos los usuarios',
        allowClear: true
      });

      if (typeof mostrarFiltroMunicipio !== 'undefined' && mostrarFiltroMunicipio) {
        planeacionSelect2Theme($('#filtro_municipio'), {
          placeholder: 'Todos los municipios',
          closeOnSelect: true,
          allowClear: true
        });

        $('#filtro_municipio').off('change.planeacionFiltro').on('change.planeacionFiltro', function () {
          var mun = $(this).val() || '';
          $.ajax({
            url: 'admin/ajax/rqst.php',
            type: 'POST',
            dataType: 'json',
            data: { op: 'planeacion_usuarios_filtro', municipio_id: mun },
            success: function (resp) {
              var rows = (resp && resp.output && resp.output.valid) ? (resp.output.response || []) : [];
              var selected = $usuarios.val() || [];
              $usuarios.empty().append('<option value=""></option>');
              rows.forEach(function (u) {
                var uid = String(u.id);
                var label = ((u.nombre || '') + ' ' + (u.apellido || '')).trim();
                if (!label) label = u.nickname || ('Usuario #' + uid);
                if (u.nombre_municipio) label += ' — ' + u.nombre_municipio;
                var opt = new Option(label, uid, false, selected.indexOf(uid) !== -1);
                $usuarios.append(opt);
              });
              $usuarios.trigger('change');
            }
          });
        });
      }
    }

    $(function () {
      initFiltrosPlaneacionListado();
      $('#modalAsignarProyecto').on('hidden.bs.modal', function () {
        var $sel = $('#asignar_usuarios_select');
        if ($sel.length && $sel.hasClass('select2-hidden-accessible')) {
          $sel.select2('destroy');
        }
      });
    });
  </script>

  <!-- MODAL LOGS -->
  <div class="modal fade" id="logsModal" tabindex="-1" role="dialog" aria-labelledby="logsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="logs_title" style="color:#fff !important;">Historial de mi proyecto</h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        </div>
        <div class="modal-body" id="logs_body" style="color:#fff !important;"></div>
        <div class="modal-footer">
          <a id="btn_descargar_logs" href="#" class="btn btn-primary btn-brutal" download>
            <i class="feather icon-download"></i> Descargar acciones
          </a>
          <button type="button" class="btn btn-secondary btn-brutal" data-dismiss="modal">
            <i class="feather icon-x"></i> Cerrar
          </button>
        </div>
      </div>
    </div>
  </div>
  <style>
    #logsModal .modal-body,
    #logsModal .modal-body p,
    #logsModal .modal-body strong,
    #logsModal .modal-body span,
    #logsModal .modal-body td,
    #logsModal .modal-body th,
    #logsModal .modal-body h6{ color:#fff !important; }
    #logsModal .modal-body .badge{ color:#fff !important; }
    #logsModal .modal-body .log-entry{ border-color:rgba(255,255,255,.12) !important; background:rgba(255,255,255,.06) !important; }
    #logsModal .modal-body .text-muted{ color:rgba(255,255,255,.6) !important; }
  </style>

    </style>

  <!-- MODAL ASIGNAR -->
  <div class="modal fade" id="modalAsignarProyecto" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" style="color:#fff !important;">Asignar usuarios · <span id="asignar_proyecto_nombre"></span></h5>
          <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
        </div>
        <div class="modal-body">
          <input type="hidden" id="asignar_proyecto_id" value="">
          <p class="help-muted mb-2">Solo se listan usuarios de la misma alcaldía con permiso de <b>gestionar</b> proyectos.</p>
          <div id="asignar_usuarios_list"></div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary btn-brutal" data-dismiss="modal">Cancelar</button>
          <button type="button" class="btn btn-primary btn-brutal" onclick="guardarAsignacionProyecto()">
            <i class="feather icon-save"></i> Guardar asignación
          </button>
        </div>
      </div>
    </div>
  </div>
  <style>
    .help-muted{ color:rgba(255,255,255,.6) !important; font-weight:800; font-size:.85rem; }
  </style>

  <?php include './admin/include/generic_dataTables.php'; ?>
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

    /*
     * Override exacto de plugins/select2/css/select2.min.css
     * (.select2-selection__rendered trae background #fff y color #495057)
     */
    .planeacion-filtros .select2-container--default .select2-selection--single,
    .planeacion-filtros .select2-container--default .select2-selection--multiple,
    #modalAsignarProyecto .select2-container--default .select2-selection--single,
    #modalAsignarProyecto .select2-container--default .select2-selection--multiple{
      background-color:#0B1B38 !important;
      background:#0B1B38 !important;
      border:1px solid rgba(255,255,255,.22) !important;
      border-radius:12px !important;
      min-height:44px !important;
      color:#fff !important;
    }

    .planeacion-filtros .select2-container--default .select2-selection--single .select2-selection__rendered,
    .planeacion-filtros .select2-container--default .select2-selection--multiple .select2-selection__rendered,
    #modalAsignarProyecto .select2-container--default .select2-selection--single .select2-selection__rendered,
    #modalAsignarProyecto .select2-container--default .select2-selection--multiple .select2-selection__rendered{
      color:#fff !important;
      background-color:transparent !important;
      background:transparent !important;
      border:none !important;
      border-radius:0 !important;
      height:auto !important;
      min-height:42px !important;
      line-height:1.4 !important;
      padding:8px 40px 8px 12px !important;
      font-size:0.875rem !important;
      font-weight:700 !important;
      width:100% !important;
      display:block !important;
      box-shadow:none !important;
    }

    .planeacion-filtros .select2-container--default .select2-selection--multiple .select2-selection__rendered{
      display:flex !important;
      flex-wrap:wrap !important;
      align-items:center !important;
      gap:6px !important;
    }

    .planeacion-filtros .select2-container--default .select2-selection--single .select2-selection__placeholder,
    .planeacion-filtros .select2-container--default .select2-selection--multiple .select2-selection__placeholder,
    #modalAsignarProyecto .select2-container--default .select2-selection--single .select2-selection__placeholder,
    #modalAsignarProyecto .select2-container--default .select2-selection--multiple .select2-selection__placeholder{
      color:rgba(255,255,255,.55) !important;
    }

    .planeacion-filtros .select2-container--default .select2-selection--single .select2-selection__clear,
    .planeacion-filtros .select2-container--default .select2-selection--multiple .select2-selection__clear,
    #modalAsignarProyecto .select2-container--default .select2-selection--single .select2-selection__clear,
    #modalAsignarProyecto .select2-container--default .select2-selection--multiple .select2-selection__clear{
      color:#fff !important;
      font-size:18px !important;
      font-weight:900 !important;
      float:none !important;
      position:absolute !important;
      right:28px !important;
      top:50% !important;
      transform:translateY(-50%) !important;
      margin:0 !important;
      z-index:5 !important;
    }

    .planeacion-filtros .select2-container--default .select2-selection--multiple .select2-selection__choice,
    #modalAsignarProyecto .select2-container--default .select2-selection--multiple .select2-selection__choice{
      background:#20427F !important;
      background-image:linear-gradient(135deg,#20427F,#2e58a8) !important;
      border:1px solid rgba(255,255,255,.25) !important;
      color:#fff !important;
      border-radius:999px !important;
      font-weight:800 !important;
    }

    .planeacion-filtros .select2-container--default .select2-selection--multiple .select2-selection__choice__remove,
    #modalAsignarProyecto .select2-container--default .select2-selection--multiple .select2-selection__choice__remove{
      color:#fff !important;
      border:none !important;
      background:transparent !important;
    }

    .planeacion-filtros .select2-container--default .select2-selection--multiple .select2-search--inline .select2-search__field,
    #modalAsignarProyecto .select2-container--default .select2-selection--multiple .select2-search--inline .select2-search__field{
      color:#fff !important;
      background:transparent !important;
      border:none !important;
      caret-color:#fff !important;
    }

    .planeacion-filtros .select2-container--default .select2-selection--single .select2-selection__arrow b{
      border-color:#fff transparent transparent transparent !important;
    }
  </style>
</body>
</html>