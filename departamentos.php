<?php

include './admin/include/head.php';

require './admin/include/generic_classes.php';

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
include './admin/classes/Colombia.php';
include './admin/classes/Ciudad.php';
include './admin/classes/Estado.php';
require './admin/classes/Departamento.php';
require './admin/classes/Maing.php';
require './admin/classes/Pilar.php';
require './admin/classes/Mapa.php';
include './admin/db/coloress.php';

// Obtener permisos
/* $permissions = PagePermissions::crudForCurrentPage();

// Validación de permiso de visualización
if (!$permissions['view']) {
    require_once 'permiso_denegado.php';
    exit;
} */

// Restringir acceso a usuarios tipo Alcalde y Auxiliar Alcalde
$userType = SessionData::getUserType();
if ($userType === Util::Alcalde() || $userType === Util::Auxiliar_Alcalde()) {
    header('Location: dashboard.php');
    exit;
}

// Informacion del Main
$arr = Maing::getDataMain(null);
$visitas = $arr['output']['visitas'];
$impactada = $arr['output']['impactada'];
$inversion = $arr['output']['inversion'];


$pilar = $_REQUEST['pilar'];
$codigoTodos = Util::codigoTodos();

// Información de Pilares
$response = Pilar::getAll(null);
if (!empty($response['output']['valid'])) {
    $arrPilar = $response['output']['response'];

    // Agregar opción "Todos" al inicio
    $optionPilar = "<option value='$codigoTodos'" . ($pilar == $codigoTodos ? " selected" : "") . ">Todos</option>";

    // Generar las demás opciones
    $optionPilar .= array_reduce($arrPilar, function ($carry, $val) use ($pilar) {
        $selected = ($val['id'] == $pilar) ? ' selected' : '';
        return $carry . "<option value='{$val['id']}'{$selected}>{$val['nombre']}</option>";
    }, '');
} else {
    // Solo la opción "Todos" si no hay datos
    $optionPilar = "<option value='$codigoTodos '" . ($pilar == $codigoTodos ? " selected" : "") . ">Todos</option>";
}


// Informacion del departmento
if (isset($pilar) && !empty(trim($pilar))) {
    // Obtener información de mapa
    $arr = ['codigo_departamento' => Util::getDepartamentoPrincipal(), 'pilarId' => $pilar];
    if ($pilar == $codigoTodos) {
        $dataDepartamento = Colombia::calcularColorDelDepartamentoTodosLosPilares($arr);
    } else {
        $dataDepartamento = Colombia::calcularColorDelDepartamentoByPilarId($arr);
    }

    $isvalidDepartamento = $arr['output']['valid'] ?? false;
    $santander = $dataDepartamento['output']['response'] ?? null;
} else { ?>
    <script type='text/javascript'>
        alert('Información enviada no es correcta');
        window.location =
            'departamentos.php?pilar=<?php echo $pilar; ?>';
    </script>
<?php
}

$municipioUsuarioLogueado = SessionData::getCodigoMunicipio();
$userType = SessionData::getUserType();
$isAdmin = ($userType === Util::Administrador() || $userType === Util::SuperAdministrador());
$isSecretario = ($userType === Util::Secretario_Despacho() || $userType === Util::Auxiliar()|| $userType == Util::Auxiliar_secret_gob());
$isAlcalde = ($userType === Util::Alcalde() || $userType === Util::Auxiliar_Alcalde());
?>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
<style>
    .infowindow-mini {
        max-width: 220px;
        font-size: 12px;
        padding: 5px;
    }

    .infowindow-mini h4 {
        margin: 0 0 5px 0;
        font-size: 14px;
    }

    .infowindow-mini p {
        margin: 2px 0;
    }
</style>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">

<style>
  /* ==========================================================
     ACCION UNIFICADA - UI UPGRADE PRO SaaS (SIN ROMPER NADA)
     - No se cambian IDs, ni clases, ni JS, ni estructura lógica
  ========================================================== */

  :root{
    --au-bg-0:#070A12;
    --au-bg-1:#0B1020;
    --au-card: rgba(255,255,255,.06);
    --au-card-2: rgba(255,255,255,.085);
    --au-stroke: rgba(255,255,255,.10);
    --au-text: rgba(255,255,255,.92);
    --au-muted: rgba(255,255,255,.68);
    --au-primary:#2E6BFF;
    --au-primary-2:#19D3FF;
    --au-success:#18ff6d;
    --au-shadow: 0 18px 55px rgba(0,0,0,.55);
    --au-radius: 18px;
    --au-radius-lg: 22px;
  }

  /* Fondo premium (sin tocar tu template pcoded) */
  body{
    background:
      radial-gradient(1200px 900px at 12% 8%, rgba(46,107,255,.28), transparent 55%),
      radial-gradient(900px 700px at 88% 18%, rgba(25,211,255,.20), transparent 50%),
      radial-gradient(700px 600px at 65% 86%, rgba(24,255,109,.10), transparent 55%),
      linear-gradient(180deg, var(--au-bg-0), var(--au-bg-1));
    color: var(--au-text);
  }

  /* Header/breadcrumb más premium */
  .page-header .page-block{
    background: transparent;
  }

  .page-header h5{
    font-weight: 800;
    letter-spacing: .2px;
    font-size: 1.05rem;
    margin: 0;
  }

  .breadcrumb{
    background: transparent !important;
    border-radius: 999px;
    padding: .4rem .75rem;
    gap: .35rem;
  }
  .breadcrumb-item a{ color: var(--au-muted) !important; }
  .breadcrumb-item.active,
  .breadcrumb-item:last-child a{
    color: var(--au-text) !important;
  }

  /* Cards: glass + borde + sombras pro */
  .card{
    border: 1px solid var(--au-stroke) !important;
    background: linear-gradient(180deg, var(--au-card), rgba(255,255,255,.035)) !important;
    box-shadow: var(--au-shadow);
    border-radius: var(--au-radius-lg) !important;
    overflow: hidden;
  }
  .card-header{
    border-bottom: 1px solid rgba(255,255,255,.09) !important;
    background: linear-gradient(90deg, rgba(46,107,255,.14), rgba(25,211,255,.10), rgba(255,255,255,.02)) !important;
    padding: 1rem 1.15rem !important;
  }
  .card-header h5{
    font-weight: 850 !important;
    margin: 0 !important;
    display: flex;
    align-items: center;
    gap: .6rem;
  }

  /* Título con “chip” sutil (sin modificar HTML) */
  .card-header h5::before{
    content:"";
    width: 10px;
    height: 10px;
    border-radius: 999px;
    background: linear-gradient(135deg, var(--au-primary), var(--au-primary-2));
    box-shadow: 0 0 0 6px rgba(46,107,255,.12);
    flex: 0 0 auto;
  }

  .card-body{
    padding: 1.15rem !important;
  }

  /* Columna del mapa (que se sienta dashboard pro) */
  #contenido-mapa{
    position: relative;
    border-radius: 18px;
    background:
      radial-gradient(600px 360px at 50% 35%, rgba(255,255,255,.07), transparent 60%),
      linear-gradient(180deg, rgba(255,255,255,.04), rgba(255,255,255,.02));
    border: 1px solid rgba(255,255,255,.08);
    box-shadow: inset 0 0 0 1px rgba(0,0,0,.15);
    padding: .75rem;
    overflow: hidden;
    min-height: 520px; /* evita “flashes” feos */
  }

  /* Glow suave alrededor del mapa */
  #contenido-mapa::before{
    content:"";
    position:absolute;
    inset:-2px;
    background:
      radial-gradient(900px 420px at 50% 20%, rgba(46,107,255,.14), transparent 55%),
      radial-gradient(700px 360px at 80% 70%, rgba(25,211,255,.10), transparent 55%);
    pointer-events:none;
  }

  /* SVG responsivo y centrado */
  #contenido-mapa svg{
    width: 100%;
    height: auto;
    display: block;
    position: relative;
    z-index: 1;
    filter: drop-shadow(0 18px 25px rgba(0,0,0,.45));
  }

  /* Interacción municipios (sin cambiar tus clases) */
  .municipios{
    transition: filter .18s ease, transform .18s ease, opacity .18s ease;
    cursor: pointer;
  }
  .municipios:hover{
    filter: brightness(1.08) saturate(1.08);
    transform: translateY(-0.4px);
  }

  /* Panel derecho (selector) con look premium */
  .form-group label{
    color: var(--au-muted) !important;
    font-weight: 700;
    letter-spacing: .2px;
  }

  .form-control{
    border-radius: 14px !important;
    border: 1px solid rgba(255,255,255,.12) !important;
    background: rgba(10,14,28,.55) !important;
    color: var(--au-text) !important;
    box-shadow: inset 0 0 0 1px rgba(0,0,0,.15);
    transition: border-color .15s ease, box-shadow .15s ease, transform .15s ease;
  }
  .form-control:focus{
    border-color: rgba(46,107,255,.65) !important;
    box-shadow: 0 0 0 4px rgba(46,107,255,.18) !important;
    transform: translateY(-1px);
  }

  /* Botón Geolocalización: más “call to action” */
  .btn.btn-primary{
    border: 0 !important;
    border-radius: 999px !important;
    padding: .62rem 1.15rem !important;
    font-weight: 800;
    letter-spacing: .2px;
    background: linear-gradient(135deg, var(--au-primary), var(--au-primary-2)) !important;
    box-shadow: 0 14px 40px rgba(46,107,255,.25);
    transition: transform .15s ease, box-shadow .15s ease, filter .15s ease;
  }
  .btn.btn-primary:hover{
    transform: translateY(-1px);
    filter: brightness(1.03);
    box-shadow: 0 18px 55px rgba(46,107,255,.32);
  }

  /* Modal: más enterprise */
  .modal-content{
    border-radius: 20px !important;
    border: 1px solid rgba(255,255,255,.14) !important;
    background: linear-gradient(180deg, rgba(16,20,36,.92), rgba(10,12,24,.96)) !important;
    box-shadow: 0 30px 90px rgba(0,0,0,.65);
    overflow: hidden;
  }
  .modal-header{
    border-bottom: 1px solid rgba(255,255,255,.10) !important;
    background: linear-gradient(90deg, rgba(46,107,255,.14), rgba(25,211,255,.10), rgba(255,255,255,.02)) !important;
  }
  .modal-title{
    font-weight: 900 !important;
    letter-spacing: .2px;
  }
  .modal-footer{
    border-top: 1px solid rgba(255,255,255,.08) !important;
  }

  /* Contenedor Google Map (no cambia ID) */
  #map{
    border-radius: 18px;
    border: 1px solid rgba(255,255,255,.10);
    overflow: hidden;
    box-shadow: inset 0 0 0 1px rgba(0,0,0,.16);
  }

  /* Tu infowindow mini (se mantiene + upgrade) */
  .infowindow-mini{
    max-width: 240px;
    font-size: 12px;
    padding: 10px;
    border-radius: 14px;
  }
  .infowindow-mini h4{
    margin: 0 0 6px 0;
    font-size: 13px;
    font-weight: 900;
    letter-spacing: .2px;
  }
  .infowindow-mini p{
    margin: 2px 0;
    opacity: .9;
  }

  /* Responsive: mejor para LED y pantallas grandes */
  @media (min-width: 1400px){
    #contenido-mapa{ min-height: 640px; }
  }
  @media (max-width: 991px){
    #contenido-mapa{ min-height: 460px; }
  }

  /* Quita bordes raros del template (sin romper) */
  .pcoded-content{
    padding-top: 14px;
  }
/* ==========================================================
   FIX CONTRASTE TEXTO – BLANCO PURO / SaaS PRO
   ========================================================== */

/* Títulos de las cards (Mapa / Seleccionar Pilar) */
.card-header h5{
  color: #ffffff !important;
  opacity: 1 !important;
  text-shadow: 0 2px 8px rgba(0,0,0,.55);
}

/* Quita cualquier opacidad heredada */
.card-header,
.card-header *{
  opacity: 1 !important;
}

/* Label Pilar */
.form-group label,
label.floating-label{
  color: #ffffff !important;
  opacity: 0.95 !important;
  font-weight: 800;
  text-shadow: 0 1px 6px rgba(0,0,0,.45);
}

/* Asterisco rojo más visible */
.form-group label span.text-danger{
  color: #ff4d4f !important;
  font-weight: 900;
  text-shadow: none;
}

/* Breadcrumb activo (por si también se ve apagado) */
.breadcrumb-item.active,
.breadcrumb-item:last-child a{
  color: #ffffff !important;
  font-weight: 700;
}

/* Extra: mejora contraste general de headers */
.card-header{
  background: linear-gradient(
    90deg,
    rgba(46,107,255,.22),
    rgba(25,211,255,.16),
    rgba(255,255,255,.03)
  ) !important;
}

/* =======================
   TABLA CONSOLIDADO DEPARTAMENTAL
======================= */
#divConsolidado table{
  background:#ffffff !important;
  border-collapse: separate;
  border-spacing: 0 6px;
  font-size: 12px;
}

#divConsolidado table thead th{
  background:#1f3a56 !important;
  color:#ffffff !important;
  font-weight:900 !important;
  font-size:12px;
  letter-spacing:.3px;
  text-transform: uppercase;
  padding:10px 8px !important;
  border:0 !important;
}

#divConsolidado table tbody tr{
  background:#ffffff;
  box-shadow: 0 6px 14px rgba(0,0,0,.08);
  border-radius:12px;
}

#divConsolidado table tbody tr:hover{
  background:#f4f7fb !important;
}

#divConsolidado table td{
  color:#0f172a !important;
  font-weight:700;
  font-size:12px;
  padding:10px 8px !important;
  border:0 !important;
  vertical-align: middle;
}

#divConsolidado table td:nth-child(2){
  font-weight:800;
  font-size:12px;
  color:#020617 !important;
  line-height:1.25;
}

#divConsolidado table td img[alt="Icono"]{
  width:28px;
  height:28px;
  background:#f8fafc;
  border-radius:10px;
  padding:4px;
  box-shadow:0 6px 12px rgba(0,0,0,.12);
}

#divConsolidado .table-responsive{
  overflow-x:hidden !important;
}

/* =======================
   TABS PILARES - Tarjetas uniformes
======================= */
#myTabDep.nav-tabs {
  display: flex;
  flex-wrap: wrap;
  list-style: none;
  padding: 0.5rem 0 1.25rem 0;
  margin-bottom: 1rem;
  border-bottom: 2px solid rgba(255,255,255,.10) !important;
  gap: 0.6rem;
  align-items: stretch;
}

#myTabDep.nav-tabs .nav-item {
  position: relative;
  flex: 1 1 80px;
  max-width: 110px;
}

#myTabDep.nav-tabs .nav-link {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: flex-start;
  padding: 0.8rem 0.5rem 0.7rem;
  width: 100%;
  height: 100%;
  min-height: 95px;
  cursor: pointer;
  background: rgba(255,255,255,.08) !important;
  backdrop-filter: blur(8px);
  border-radius: 14px !important;
  border: 1.5px solid rgba(255,255,255,.14) !important;
  box-shadow: 0 3px 10px rgba(0,0,0,.20);
  transition: transform .18s ease, box-shadow .18s ease, background .18s ease, border-color .18s ease;
  gap: 8px;
  color: rgba(255,255,255,.65) !important;
  text-decoration: none !important;
  white-space: normal !important;
}

#myTabDep.nav-tabs .nav-link .tab-icon-wrap {
  width: 44px;
  height: 44px;
  border-radius: 11px;
  background: rgba(255,255,255,.10);
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
  transition: background .18s ease, box-shadow .18s ease;
}

#myTabDep.nav-tabs .nav-link .tab-icon-wrap img {
  width: 28px;
  height: 28px;
  object-fit: contain;
  filter: brightness(0.85) saturate(0.80);
  transition: filter .18s ease, transform .18s ease;
  display: block;
}

#myTabDep.nav-tabs .tab-label {
  font-size: 0.65rem;
  font-weight: 600;
  line-height: 1.3;
  text-align: center;
  word-break: break-word;
  white-space: normal;
  width: 100%;
  color: inherit;
  display: block;
}

#myTabDep.nav-tabs .nav-link:hover {
  transform: translateY(-3px);
  background: rgba(255,255,255,.15) !important;
  border-color: rgba(255,255,255,.28) !important;
  color: #fff !important;
  box-shadow: 0 8px 20px rgba(0,0,0,.28);
}

#myTabDep.nav-tabs .nav-link.active {
  background: linear-gradient(155deg, rgba(46,107,255,.90) 0%, rgba(25,211,255,.55) 100%) !important;
  color: #fff !important;
  border-color: rgba(25,211,255,.50) !important;
  box-shadow: 0 6px 22px rgba(46,107,255,.45), inset 0 1px 0 rgba(255,255,255,.22);
  transform: translateY(-3px);
}

#myTabDep.nav-tabs .nav-link.active .tab-icon-wrap {
  background: rgba(255,255,255,.22);
  box-shadow: 0 0 14px rgba(25,211,255,.45);
}

#myTabDep.nav-tabs .nav-link.active .tab-icon-wrap img {
  filter: brightness(1.2) saturate(1.3) drop-shadow(0 0 5px rgba(25,211,255,.60));
  transform: scale(1.06);
}

#myTabDep.nav-tabs .nav-link.active::after {
  content: '';
  position: absolute;
  bottom: -15px;
  left: 50%;
  transform: translateX(-50%);
  width: 8px;
  height: 8px;
  background: rgba(25,211,255,.95);
  border-radius: 50%;
  box-shadow: 0 0 10px rgba(25,211,255,.75);
}

.tab-content {
  padding: .75rem .25rem !important;
  animation: fadeIn 0.3s ease-in-out;
  background: transparent !important;
}

@keyframes fadeIn {
  from {
    opacity: 0;
    transform: translateY(10px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
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
    <?php
    include './admin/include/navbar.php';
    ?>
    <!-- [ navigation menu ] end -->
    <!-- [ Header ] start -->
    <?php
    include './admin/include/header.php';
    ?>
    <!-- [ Header ] end -->

    <!-- [ Header ] end -->
    <!-- [ Main Content ] start -->
    <div class="pcoded-main-container">
        <div class="pcoded-wrapper">
            <div class="pcoded-content">
                <div class="pcoded-inner-content">
                    <div class="main-body">
                        <div class="page-wrapper">
                            <!-- [ breadcrumb ] start -->
                            <div class="page-header">
                                <div class="page-block">
                                    <div class="row align-items-center">
                                        <div class="col-md-12">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <h5 class="m-b-10">Acción Unificada Santander</h5>
                                                <?php include './admin/include/btn_back.php'; ?>
                                            </div>
                                            <ul class="breadcrumb">
                                                <li class="breadcrumb-item"><a href="index.html"><i
                                                            class="feather icon-home"></i></a></li>
                                                <li class="breadcrumb-item"><a href="#!">Acción Unificada Santander </a>
                                                </li>
                                                <li class="breadcrumb-item"><a href="#!">Estado Departamento</a></li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!-- [ breadcrumb ] end -->
                            <!-- [ Main Content ] start -->

                            <div class="row">


                                <!-- Card para el mapa -->
                                <div class="col-md-8">
                                    <div class="card h-100">
                                        <div class="card-header">
                                            <h5>Mapa Interactivo</h5>

                                        </div>
                                        <div class="card-body">
                                            <div id="contenido-mapa" class="cuerpoMapa w-12">

                                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="-90 10 1400 1400.68" >
                                                    <?php foreach ($santander as $key => $value) : ?>
                                                        <g id="<?php echo strtoupper($value['path']); ?>">
                                                            <path id="<?php echo strtoupper($value['path']); ?>"
                                                                d="<?php echo $value['d']; ?>"
                                                                fill="<?php echo ($value['color_calculado']); ?>"
                                                                class="municipios mapaClick <?php echo getClasePorcentaje(0.2); ?>"
                                                                data-url="<?php echo getUrl() . 'municipios.php?mun=' . $value['codigo_muncipio'] . '&dep=' . $value['codigo_departamento']; ?>&pilar=<?php echo $pilar; ?>"
                                                                data-name="<?php echo strtolower($value['municipio']); ?>"
                                                                title="<?php echo strtoupper(str_replace("-", " ", $value['nombre_mapa'])); ?>"
                                                                stroke="#000" stroke-miterlimit="10" stroke-width="0.1px"></path>
                                                        </g>
                                                    <?php endforeach; ?>
                                                    <?php require_once 'nombres_mapa_santander.php' ?>
                                                </svg>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <!-- Card para el selector de Pilar -->
                                <div class="col-md-4">
                                    <div class="card">
                                        <div class="card-header">
                                            <h5>Seleccionar Pilar</h5>
                                        </div>


                                        <div class="card-body">
                                            <div class="form-group">
                                                <label class="floating-label" for="Pilar">Pilar <span class="text-danger mb-1">*</span></label>
                                                <select class="form-control" id="pilarId" name="pilarId" onchange="updateUrlPilar(this)">
                                                    <?php echo $optionPilar ?>
                                                </select>
                                            </div>

                                            <!-- Botón centrado con más altura -->
                                            <div class="d-flex justify-content-center mt-3">
                                                <button type="button" class="btn btn-primary px-4 py-2" style="font-size: 0.9rem;" data-toggle="modal" data-target="#modalGeocalizacion">
                                                    <i class="bi bi-geo-alt-fill me-1"></i> Geolocalización
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="row mt-4">
                                <div class="col-12">
                                    <div class="card">
                                        <div class="card-header">
                                            <h5>Consolidado Departamental - Factores por Pilar</h5>
                                        </div>
                                        <div class="card-body">
                                            <div id="divConsolidado"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card-body">
                    <div id="modalGeocalizacion" class="modal fade" tabindex="-1" role="dialog"
                        aria-labelledby="modalGeocalizacionTitle" aria-hidden="true">
                        <!-- <div class="modal-dialog modal-dialog-centered" role="document"> -->
                        <div class="modal-dialog modal-xl modal-dialog-centered" role="document">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="modalGeocalizacionTitle">Geolocalización para pilar: <span
                                            id="nombrePilar"></span></h5>
                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
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

                </div>

                <!--  Script -->
                <?php if (isset($_GET["route_map"])): ?>
                <?php endif ?>
                <?php include 'admin/include/footer.php'; ?>
                <script>
                    document.getElementById("btnAumentar").onclick = function() {
                        aumentarTransform();
                    };
                    document.getElementById("btnReducir").onclick = function() {
                        reducirTransform();
                    };

                    function aumentarTransform() {
                        var elemento = document.getElementById("contenidoTransformado");
                        var escalaActual = parseFloat(window.getComputedStyle(elemento).getPropertyValue("transform").split(
                            ",")[3]);
                        var nuevaEscala = escalaActual + 0.1; // Aumentar la escala en 0.1
                        elemento.style.transform = "scale(" + nuevaEscala + ")";
                    }

                    function reducirTransform() {
                        var elemento = document.getElementById("contenidoTransformado");
                        var escalaActual = parseFloat(window.getComputedStyle(elemento).getPropertyValue("transform").split(
                            ",")[3]);
                        var nuevaEscala = escalaActual - 0.1; // Reducir la escala en 0.1
                        if (nuevaEscala >= 0.1) { // Evitar escala negativa
                            elemento.style.transform = "scale(" + nuevaEscala + ")";
                        }
                    }
                </script>

                <?php include 'admin/include/gerenic_script.php'; ?>

                <!-- Required Js -->
                <script src="assets/js/vendor-all.min.js"></script>
                <script src="assets/js/plugins/bootstrap.min.js"></script>
                <script src="assets/js/pcoded.min.js"></script>
                <!-- Google Maps JavaScript API -->
                <script async defer
                    src="https://maps.googleapis.com/maps/api/js?key=AIzaSyAbqZVEnSpIPF5dKe89pju2rmQaM58wvY0&callback=initMap">
                </script>

                <script src="<?php echo Util::versionar('./admin/js/mapa_departamento.js'); ?>"></script>

                <script>
                    function renderConsolidadoTable(data) {
                        const tabs = data.tabs || [];
                        const response = data.response || [];
                        const responseActual = data.responseActual || [];
                        
                        if (!data.valid || tabs.length === 0) {
                            return '<div class="text-center text-muted p-4"><i class="bi bi-inbox" style="font-size: 2rem;"></i><p class="mt-2">No hay datos disponibles para este pilar</p></div>';
                        }
                        
                        let tabsHtml = '<ul class="nav nav-tabs mb-3" id="myTabDep" role="tablist">';
                        let contentHtml = '<div class="tab-content" id="myTabDepContent">';
                        
                        tabs.forEach((tab, index) => {
                            if (tab.enable !== 'si') return;
                            
                            const img = tab.icono || 'assets/iconos/gobierno.png';
                            const isActive = index === 0 ? 'active' : '';
                            const showActive = index === 0 ? 'show active' : '';
                            
                            tabsHtml += `
                                <li class="nav-item">
                                    <a class="nav-link ${isActive}"
                                        id="tab-dep-${tab.id}" data-toggle="tab"
                                        href="#content-dep-${tab.id}" role="tab"
                                        aria-controls="content-dep-${tab.id}"
                                        aria-selected="${index === 0 ? 'true' : 'false'}">
                                        <span class="tab-icon-wrap">
                                            <img src="${img}" alt="${tab.nombre}">
                                        </span>
                                        <span class="tab-label">${tab.nombre}</span>
                                    </a>
                                </li>
                            `;
                            
                            // Filtrar datos para este pilar/area
                            const areaDataInicial = response.filter(item => item.area_id == tab.id);
                            const areaDataActual = responseActual.filter(item => item.area_id == tab.id);
                            
                            // Agrupar por factor
                            const agrupado = {};
                            areaDataInicial.forEach(item => {
                                const factor = (item.factor || '').toUpperCase().trim();
                                const medicion = (item.tipo_medicion || '').toUpperCase().trim();
                                const clave = factor + '|' + medicion;
                                const cantidadInicial = parseFloat(item.total_cantidad) || 0;
                                
                                if (!agrupado[clave]) {
                                    agrupado[clave] = {
                                        factor: factor,
                                        tipo_medicion: medicion,
                                        total_cantidad_inicial: cantidadInicial,
                                        total_cantidad_actual: 0,
                                        icono: item.icono || ''
                                    };
                                } else {
                                    agrupado[clave].total_cantidad_inicial += cantidadInicial;
                                }
                            });
                            
                            areaDataActual.forEach(item => {
                                const factor = (item.factor || '').toUpperCase().trim();
                                const medicion = (item.tipo_medicion || '').toUpperCase().trim();
                                const clave = factor + '|' + medicion;
                                const cantidadActual = parseFloat(item.total_cantidad_actual) || 0;
                                
                                if (agrupado[clave]) {
                                    agrupado[clave].total_cantidad_actual += cantidadActual;
                                } else {
                                    agrupado[clave] = {
                                        factor: factor,
                                        tipo_medicion: medicion,
                                        total_cantidad_inicial: 0,
                                        total_cantidad_actual: cantidadActual,
                                        icono: item.icono || ''
                                    };
                                }
                            });
                            
                            let tableRows = '';
                            Object.values(agrupado).forEach(data => {
                                tableRows += `
                                    <tr>
                                        <td><img src="${data.icono}" alt="Icono" width="32px"></td>
                                        <td class="text-start fw-semibold" style="font-size: 13px; word-break: break-word; white-space: normal; font-weight: bold;">
                                            ${data.factor}
                                        </td>
                                        <td>
                                            <span style="font-size: 13px; font-weight: 500; color: #145a32; background-color: #f1f8e9; padding: 6px 14px; border-radius: 8px; display: inline-block;">
                                                ${number_format(data.total_cantidad_inicial)}
                                            </span>
                                        </td>
                                        <td>
                                            <span style="font-size: 13px; font-weight: bold; color: #0e6655; background-color: #a2ded0; padding: 6px 14px; border-radius: 12px; box-shadow: 0 0 6px rgba(26, 188, 156, 0.4); display: inline-block; min-width: 70px;">
                                                ${number_format(data.total_cantidad_actual)}
                                            </span>
                                        </td>
                                        <td><span class="text-muted" style="font-size: 12px;">${data.tipo_medicion}</span></td>
                                    </tr>
                                `;
                            });
                            
                            const tableContent = tableRows 
                                ? `<div class="table-responsive">
                                    <table class="table table-hover table-bordered align-middle text-center" style="width: 100%">
                                        <thead class="thead-dark bg-primary text-white">
                                            <tr>
                                                <th>Ícono</th>
                                                <th style="min-width: 200px;">Factor</th>
                                                <th style="min-width: 130px;">Cantidad Inicial</th>
                                                <th style="min-width: 130px;">Cantidad Actual</th>
                                                <th style="min-width: 100px;">Unidad</th>
                                            </tr>
                                        </thead>
                                        <tbody>${tableRows}</tbody>
                                    </table>
                                   </div>`
                                : '<div class="text-center text-muted p-4"><i class="bi bi-inbox" style="font-size: 2rem;"></i><p class="mt-2">No hay datos disponibles para este pilar</p></div>';
                            
                            contentHtml += `
                                <div class="tab-pane fade ${showActive}"
                                    id="content-dep-${tab.id}" role="tabpanel"
                                    aria-labelledby="tab-dep-${tab.id}">
                                    ${tableContent}
                                </div>
                            `;
                        });
                        
                        tabsHtml += '</ul>';
                        contentHtml += '</div>';
                        
                        return tabsHtml + contentHtml;
                    }
                    
                    function number_format(num) {
                        return num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
                    }
                    
                    function cargarConsolidado(pilar) {
                        const container = document.getElementById('divConsolidado');
                        if (!container) return;
                        
                        container.innerHTML = '<div class="text-center p-4"><i class="fa fa-spinner fa-spin" style="font-size: 2rem;"></i><p class="mt-2">Cargando datos...</p></div>';
                        
                        fetch('admin/ajax/rqst.php', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/x-www-form-urlencoded',
                            },
                            body: 'op=get_consolidado_departamental&pilar=' + pilar
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.valid) {
                                container.innerHTML = renderConsolidadoTable(data);
                                initTabsEvents();
                            } else {
                                container.innerHTML = '<div class="text-center text-muted p-4"><i class="bi bi-inbox" style="font-size: 2rem;"></i><p class="mt-2">Error al cargar los datos</p></div>';
                            }
                        })
                        .catch(error => {
                            console.error('Error:', error);
                            container.innerHTML = '<div class="text-center text-muted p-4"><i class="bi bi-exclamation-triangle" style="font-size: 2rem;"></i><p class="mt-2">Error de conexión</p></div>';
                        });
                    }
                    
                    function initTabsEvents() {
                        $('#myTabDep a[data-toggle="tab"]').off('click').on('click', function(e) {
                            e.preventDefault();
                            $(this).tab('show');
                        });
                    }
                    
                    //Cuando se selecciona un pilar se actualiza la tabla dinámicamente
                    document.getElementById("pilarId").addEventListener("change", function() {
                        var selectedValue = this.value;
                        cargarConsolidado(selectedValue);
                    });
                    
                    // Cargar datos al iniciar la página
                    $(document).ready(function() {
                        var pilarInicial = document.getElementById("pilarId").value;
                        cargarConsolidado(pilarInicial);
                    });
                </script>
</body>

</html>