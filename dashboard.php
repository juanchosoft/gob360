<?php
$isAjaxHacienda = isset($_GET['ajax']) && $_GET['ajax'] === 'hacienda';

if ($isAjaxHacienda) {
    // Carga mínima para la sección Hacienda vía AJAX
    require_once './admin/include/generic_classes.php';
    include './admin/classes/Secretarias.php';
    include './admin/classes/Colombia.php';
    include './admin/classes/AccionSecretaria.php';
    include './admin/db/coloress.php';
    include './admin/classes/Main.php';
} else {
    include './admin/include/head.php';
}

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
    // FIX: comparación correcta
    if ($exists !== false) {
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
include_once './admin/db/coloress.php';
include_once './admin/classes/Main.php';
include_once './admin/classes/Detalle.php';
include_once './admin/classes/Cuenta.php';
include_once './admin/classes/Cuentapro.php';
include_once './admin/classes/Secreinversion.php';
include_once './admin/classes/Munnovisitados.php';
include_once './admin/classes/Pilar.php';
include_once './admin/classes/Inversion.php';
include_once './admin/classes/Secretarias.php';
include_once './admin/classes/Colombia.php';
include_once './admin/classes/AccionSecretaria.php';

// ==============================
// Constantes Hacienda
// ==============================
$haciendaId = Util::getSecretariaIdHacienda();
$GOALicores  = 'GOA Aprehensiones de Licores';
$GOACigarrillos = 'GOA Aprehensión de Cigarrillos';
$GOACervezas = 'GOA Aprehensión de Cervezas';
$GOATabaco  = 'GOA Aprehensión de Tabaco y Otros';
$registroVisitas = 'Registro de Visitas a Establecimientos Comerciales';

$accionHacienda = $_REQUEST['accion'] ?? 'GOA - Aprehensiones';

// Proxy: para 'GOA - Aprehensiones' el backend necesita una acción concreta
$accionHaciendaConsulta = ($accionHacienda === 'GOA - Aprehensiones')
    ? 'GOA Aprehensiones de Licores'
    : $accionHacienda;

$arrEjecucionHacienda = [
    'codigoMunicipio' => Util::getDepartamentoPrincipal(),
    'secretariaId' => $haciendaId,
    'accion' => $accionHaciendaConsulta
];
$responseTotalEjecucionSecretarias = Secretarias::getTotalEjecucionSecretaria($arrEjecucionHacienda);
$haciendaDatos = $responseTotalEjecucionSecretarias['output']['response'] ?? [];
$datosHac = $haciendaDatos[0] ?? [];

// GOA licores (desde respuesta separada, no desde $datosHac)
$GOALicores_arr = $responseTotalEjecucionSecretarias['output']['GOALicores'][0] ?? [];
$GOALicores_cantidad = $GOALicores_arr['cantidad_aprehendida'] ?? 0;
$GOALicores_valor   = $GOALicores_arr['avaluo_comercial'] ?? 0;

// GOA cigarrillos
$GOACigarrillos_arr = $responseTotalEjecucionSecretarias['output']['GOACigarrillos'][0] ?? [];
$GOACigarrillos_cantidad = $GOACigarrillos_arr['cantidad_aprehendida'] ?? 0;
$GOACigarrillos_valor    = $GOACigarrillos_arr['avaluo_comercial'] ?? 0;

// GOA cervezas
$GOACervezas_arr = $responseTotalEjecucionSecretarias['output']['GOACervezas'][0] ?? [];
$GOACervezas_cantidad = $GOACervezas_arr['cantidad_aprehendida'] ?? 0;
$GOACervezas_valor    = $GOACervezas_arr['avaluo_comercial'] ?? 0;

// GOA tabaco
$GOATabaco_arr = $responseTotalEjecucionSecretarias['output']['GOATabaco'][0] ?? [];
$GOATabaco_cantidad  = $GOATabaco_arr['cantidad_aprehendida'] ?? 0;
$GOATabaco_valor     = $GOATabaco_arr['avaluo_comercial'] ?? 0;

$GOATotal_cantidad_aprehendida = $GOALicores_cantidad + $GOACigarrillos_cantidad + $GOACervezas_cantidad + $GOATabaco_cantidad;
$GOATotal_avaluo_comercial = $GOALicores_valor + $GOACigarrillos_valor + $GOACervezas_valor + $GOATabaco_valor;

// Visitas
$registroVisitas_arr = $responseTotalEjecucionSecretarias['output']['registroVisitas'][0] ?? [];
$GOAcantidad_visitas_al_municipio = $registroVisitas_arr['cantidad_visitas_al_municipio'] ?? 0;

// GOA jurídico
$goaJuridico_arr = $responseTotalEjecucionSecretarias['output']['GOAJuridico'][0] ?? [];
$goaJuridicoCustodiaValorTotal          = $goaJuridico_arr['goa_juridico_custodia_valor_total'] ?? 0;
$goaJuridicoCustodiaCantidadProcesos    = $goaJuridico_arr['goa_juridico_custodia_cantidad_procesos'] ?? 0;
$goaJuridicoCustodiaCantidadUnidades    = $goaJuridico_arr['goa_juridico_custodia_cantidad_unidades'] ?? 0;
$goaJuridicoDestruccionCantidadUnidades = $goaJuridico_arr['goa_juridico_destruccion_cantidad_unidades'] ?? 0;
$goaJuridicoDestruccionValorTotal       = $goaJuridico_arr['goa_juridico_destruccion_valor_total'] ?? 0;

// Impuesto vehicular (columnas del query principal)
$vehicular_total_recaudo                  = $datosHac['TOTAL_RECAUDO_IMPUESTO_VEHICULAR'] ?? 0;
$vehicular_total_tramites                 = $datosHac['TOTAL_TRAMITES_IMPUESTO_VEHICULAR'] ?? 0;
$vehicular_total_recaudo_y_tramite        = $datosHac['IMPUESTO_VEHICULAR_TOTAL_RECAUDO_Y_TRAMITE'] ?? 0;
$vehicular_total_operativos               = $datosHac['TOTAL_VEHICULAR_OPERATIVOS'] ?? 0;
$vehicular_total_emplazados               = $datosHac['TOTAL_VEHICULAR_EMPLAZADOS'] ?? 0;
$vehicular_total_placas_consultadas       = $datosHac['TOTAL_VEHICULAR_PLACAS_CONSULTADAS'] ?? 0;
$vehicular_total_campanas_sensibilizacion = $datosHac['TOTAL_VEHICULAR_CAMPANAS_SENSIBILIZACION'] ?? 0;

// Estampillas
$ESTAMPILLAS = $responseTotalEjecucionSecretarias['output']['estampillas'] ?? [];

// Mapa Hacienda
$arrMapaHac = [
    'codigoMunicipio' => Util::getDepartamentoPrincipal(),
    'secretariaId' => $haciendaId,
    'accion' => $accionHaciendaConsulta
];
$mapData = Colombia::getInformacionSecretariaColoresMapa($arrMapaHac);
$santander = $mapData['output']['response'] ?? [];
$puntajes = $mapData['output']['puntajes'] ?? '';

// ==============================
// AJAX: devolver solo la sección Hacienda
// ==============================
if ($isAjaxHacienda) {
    include 'hacienda_section.php';
    exit;
}

// ==============================
// DATA PRINCIPAL (NO TOCAR BACKEND)
// ==============================
$arr = Main::getDataMain(null);
$isvalid = $arr['output']['valid'] ?? false;

$visitas = (int)($arr['output']['visitas'] ?? 0);
$apoyos = (int)($arr['output']['apoyos'] ?? 0);
$municipios = (int)($arr['output']['municipios'] ?? 0);
$veredas = (int)($arr['output']['veredas'] ?? 0);
$provincia = (int)($arr['output']['provincia'] ?? 0);
$porcentaje_veredas = (float)($arr['output']['porcentaje_veredas'] ?? 0);
$porcentaje_municipios = (float)($arr['output']['porcentaje_municipios'] ?? 0);

$inversionsec = (float)($arr['output']['inversionsec'] ?? 0);
$valorproyectos = (float)($arr['output']['valorproyectos'] ?? 0);
$secretaria = (int)($arr['output']['secretaria'] ?? 0);
$sumaproyectos = (int)($arr['output']['sumaproyectos'] ?? 0);

// Semana actual del Consejo de Gobierno
$semanaActual = Util::getSemanaActual();

// Factores de seguridad (Secretaría Interior)
$arrFS = Main::getFactoresSeguridad(null);
$anioSeguridad    = (int)($arrFS['output']['anio']           ?? date('Y'));
$homSicariato     = (int)($arrFS['output']['sicariato']      ?? 0);
$homIntolerancia  = (int)($arrFS['output']['intolerancia']   ?? 0);
$munSinHomicidios = (int)($arrFS['output']['sin_homicidios'] ?? 0);

// PAE ArcGIS - resumen departamental
$arrPAE = Main::getResumenPaeArcgis(null);
$paeSedes  = (int)($arrPAE['output']['sedes']  ?? 0);
$paeRural  = (int)($arrPAE['output']['rural']  ?? 0);
$paeUrbana = (int)($arrPAE['output']['urbana'] ?? 0);
$paeNinos  = (int)($arrPAE['output']['ninos']  ?? 0);

// Total compromisos
$arrTC = Main::getTotalCompromisos(null);
$totalCompromisos = (int)($arrTC['output']['total_compromisos'] ?? 0);

// Compromisos sin cumplir
$arrCSC = Main::getTotalCompromisosSinCumplir(null);
$compromisosSinCumplir = (int)($arrCSC['output']['total_sin_cumplir'] ?? 0);

// ==============================
// Inversión Seguridad (tbl_inversion_seguridad)
// ==============================
$dbInv  = new DbConection();
$pdoInv = $dbInv->openConect();
$tablaInvSeg = $dbInv->getTable('tbl_inversion_seguridad');
$qInvSeg = "SELECT COUNT(*) AS total_registros, COALESCE(SUM(valor), 0) AS total_valor FROM {$tablaInvSeg}";
$rowInvSeg = $pdoInv->query($qInvSeg)->fetch(PDO::FETCH_ASSOC);
$proyectosTotalesInv = (int)($rowInvSeg['total_registros'] ?? 0);
$valorGlobalInv      = (float)($rowInvSeg['total_valor'] ?? 0);
$dbInv->closeConect();

// Inversión por Institución Beneficiada (gráfica)
$_instResult = Inversion::getByInstitucion([]);
$dataInst = $_instResult['output']['valid'] ? ($_instResult['output']['response'] ?? []) : [];

// ==============================
// Detalle inversión por categoría (Infraestructura)
// ==============================
$infraDetalle = [];
$chartCatLabels = [];
$chartCatValues = [];
$infraTotalProyectos = 0;
try {
    $dbInfra  = new DbConection();
    $pdoInfra = $dbInfra->openConect();
    $tblInfraInv = $dbInfra->getTable('tbl_infra_inversion');
    $tblInfraInd = $dbInfra->getTable('tbl_infra_indicadores');
    $infraDetalle = $pdoInfra->query("SELECT * FROM {$tblInfraInv} ORDER BY bloque ASC, recurso_total DESC")->fetchAll(PDO::FETCH_ASSOC);
    foreach ($infraDetalle as $d) {
        $chartCatLabels[] = $d['categoria'];
        $chartCatValues[] = (float)$d['recurso_total'];
    }
    $infraIndicadores = $pdoInfra->query("SELECT * FROM {$tblInfraInd}")->fetchAll(PDO::FETCH_ASSOC);
    $infraIndMap = [];
    foreach ($infraIndicadores as $item) {
        $infraIndMap[$item['codigo']] = $item;
    }
    $infraTotalProyectos = (int)($infraIndMap['proyectos_radicados_total']['valor_numerico'] ?? 0);
    $dbInfra->closeConect();
} catch (Throwable $e) {
    $infraDetalle = [];
    $chartCatLabels = [];
    $chartCatValues = [];
}

// ==============================
// Helpers UI
// ==============================
date_default_timezone_set('America/Bogota');
$fechaHoy = date('Y-m-d');
$horaHoy  = date('H:i');

$fmtMoney = function($n){
  return '$ ' . number_format((float)$n / 1000000, 0, ',', '.') . ' MM';
};
$clampPct = function($p){
  $p = (float)$p;
  if ($p < 0) $p = 0;
  if ($p > 100) $p = 100;
  return $p;
};

$porcV = $clampPct($porcentaje_veredas);
$porcM = $clampPct($porcentaje_municipios);

// Alertas (sin backend)
$alertas = [];
if ($porcM < 50) $alertas[] = ["type"=>"danger","title"=>"Cobertura municipal baja","msg"=>"La cobertura de municipios está en <b>{$porcM}%</b>. Prioriza agenda territorial y refuerza equipo en provincia."];
if ($porcV < 40) $alertas[] = ["type"=>"warning","title"=>"Cobertura veredal por fortalecer","msg"=>"Cobertura veredal en <b>{$porcV}%</b>. Activa plan de visitas por veredas (foco en zonas rezagadas)."];
if ($visitas <= 0) $alertas[] = ["type"=>"info","title"=>"Sin visitas registradas","msg"=>"Aún no hay visitas registradas. Verifica fuentes y cargue de datos del módulo."];
if ($sumaproyectos > 0 && $valorproyectos <= 0) $alertas[] = ["type"=>"warning","title"=>"Proyectos sin valor acumulado","msg"=>"Hay proyectos registrados, pero el valor acumulado está en 0. Revisa campos de inversión."];
if (empty($alertas)) $alertas[] = ["type"=>"success","title"=>"Panel saludable","msg"=>"Los indicadores están en un rango coherente. Sigue monitoreando para mantener el ritmo."];

// Visitas por mes (BD real)
$arrVisitas = Main::getVisitasUltimosMeses(null);
if ($arrVisitas['output']['valid']) {
  $visitasSerie = $arrVisitas['output']['valores'];
  $mesesSerie = $arrVisitas['output']['etiquetas'];
} else {
  $visitasSerie = [0];
  $mesesSerie = ['Sin datos'];
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Dashboard Ejecutivo - Mandatario</title>

  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css">

  <style>
    :root{
      --gob-bg-950:#030814;
      --gob-bg-900:#06101f;
      --gob-bg-850:#09162a;
      --gob-surface:rgba(10,24,44,.78);
      --gob-surface-strong:rgba(12,30,54,.94);
      --gob-surface-soft:rgba(255,255,255,.045);
      --gob-border:rgba(129,211,255,.14);
      --gob-border-strong:rgba(52,211,255,.26);
      --gob-text:#f7fbff;
      --gob-muted:#9fb0c8;
      --gob-muted-2:#72849d;
      --gob-cyan:#25d9ff;
      --gob-blue:#2388ff;
      --gob-teal:#2cf5c4;
      --gob-green:#35e58b;
      --gob-yellow:#ffd166;
      --gob-red:#ff637d;
      --gob-shadow:0 24px 70px rgba(0,0,0,.38);
      --gob-shadow-soft:0 14px 38px rgba(0,0,0,.25);
      --gob-radius-xl:28px;
      --gob-radius-lg:22px;
      --gob-radius-md:16px;

      /* Compatibilidad con los nombres usados por la vista */
      --bg0:var(--gob-bg-950);
      --bg1:var(--gob-bg-900);
      --card:var(--gob-surface-soft);
      --stroke:var(--gob-border);
      --stroke2:var(--gob-border-strong);
      --txt:var(--gob-text);
      --muted:var(--gob-muted);
      --muted2:var(--gob-muted-2);
      --good:var(--gob-green);
      --warn:var(--gob-yellow);
      --bad:var(--gob-red);
      --info:var(--gob-cyan);
      --brand:var(--gob-blue);
      --brand2:var(--gob-cyan);
      --shadow:var(--gob-shadow);
    }

    *{ box-sizing:border-box; }
    html{ scroll-behavior:smooth; }

    body.dashboard-body{
      min-height:100vh;
      color:var(--gob-text);
      background:
        radial-gradient(900px 520px at 8% 0%, rgba(35,136,255,.24), transparent 64%),
        radial-gradient(850px 500px at 95% 10%, rgba(37,217,255,.13), transparent 62%),
        radial-gradient(900px 580px at 48% 105%, rgba(44,245,196,.075), transparent 66%),
        linear-gradient(155deg, var(--gob-bg-950) 0%, var(--gob-bg-900) 46%, #08182b 100%);
      background-attachment:fixed;
      overflow-x:hidden;
      font-family:Inter, "Segoe UI", Roboto, Arial, sans-serif;
    }

    body.dashboard-body::before{
      content:"";
      position:fixed;
      inset:0;
      z-index:-1;
      pointer-events:none;
      opacity:.18;
      background-image:
        linear-gradient(rgba(255,255,255,.055) 1px, transparent 1px),
        linear-gradient(90deg, rgba(255,255,255,.055) 1px, transparent 1px);
      background-size:64px 64px;
      mask-image:linear-gradient(to bottom, rgba(0,0,0,.95), transparent 88%);
    }

    .pcoded-main-container{ background:transparent !important; }
    .pcoded-content{
      padding:24px 24px 42px !important;
      max-width:1720px;
      margin:0 auto;
    }

    /* =========================
       CABECERA GOB360
       ========================= */
    .gob360-dashboard-hero{
      position:relative;
      overflow:hidden;
      border:1px solid var(--gob-border-strong);
      border-radius:var(--gob-radius-xl);
      background:
        linear-gradient(130deg, rgba(8,25,47,.96), rgba(7,18,35,.88)),
        radial-gradient(560px 260px at 15% 10%, rgba(35,136,255,.25), transparent 64%);
      box-shadow:var(--gob-shadow);
      isolation:isolate;
      margin-bottom:18px;
    }

    .gob360-dashboard-hero::before{
      content:"";
      position:absolute;
      width:560px;
      height:560px;
      right:-170px;
      top:-300px;
      border-radius:50%;
      background:radial-gradient(circle, rgba(37,217,255,.25), rgba(35,136,255,.08) 42%, transparent 70%);
      filter:blur(4px);
      z-index:-1;
    }

    .gob360-dashboard-hero::after{
      content:"";
      position:absolute;
      inset:0;
      z-index:-1;
      background:
        linear-gradient(90deg, transparent 0 49%, rgba(255,255,255,.025) 50%, transparent 51%) 0 0/80px 80px,
        linear-gradient(transparent 0 49%, rgba(255,255,255,.025) 50%, transparent 51%) 0 0/80px 80px;
      opacity:.45;
    }

    .gob360-dashboard-hero__main{
      display:grid;
      grid-template-columns:minmax(0,1fr) auto;
      gap:28px;
      align-items:center;
      padding:28px 30px 24px;
    }

    .gob360-dashboard-brand{
      display:flex;
      align-items:center;
      gap:24px;
      min-width:0;
    }

    .gob360-dashboard-brand__logo-wrap{
      position:relative;
      flex:0 0 auto;
      display:flex;
      align-items:center;
      justify-content:center;
      min-width:220px;
    }

    .gob360-dashboard-brand__logo-wrap::before{
      content:"";
      position:absolute;
      width:210px;
      height:120px;
      border-radius:50%;
      background:radial-gradient(circle, rgba(37,217,255,.22), transparent 70%);
      filter:blur(18px);
    }

    .gob360-dashboard-brand__logo{
      position:relative;
      z-index:1;
      display:block;
      width:clamp(190px, 15vw, 285px);
      max-height:125px;
      object-fit:contain;
      border-radius:18px;
      filter:
        drop-shadow(0 18px 26px rgba(0,0,0,.42))
        drop-shadow(0 0 20px rgba(37,217,255,.25));
      transition:transform .25s ease, filter .25s ease;
    }

    .gob360-dashboard-brand__logo:hover{
      transform:scale(1.025);
      filter:
        drop-shadow(0 20px 30px rgba(0,0,0,.48))
        drop-shadow(0 0 28px rgba(37,217,255,.38));
    }

    .gob360-dashboard-copy{ min-width:0; }

    .gob360-dashboard-copy__eyebrow{
      display:inline-flex;
      align-items:center;
      gap:8px;
      margin:0 0 8px;
      color:var(--gob-cyan);
      font-size:12px;
      font-weight:900;
      letter-spacing:2.4px;
      text-transform:uppercase;
    }

    .gob360-dashboard-copy__eyebrow::before{
      content:"";
      width:24px;
      height:2px;
      border-radius:999px;
      background:linear-gradient(90deg, var(--gob-blue), var(--gob-cyan));
      box-shadow:0 0 12px rgba(37,217,255,.5);
    }

    .gob360-dashboard-copy h1{
      margin:0;
      color:#fff;
      font-size:clamp(25px, 2.6vw, 42px);
      line-height:1.06;
      font-weight:950;
      letter-spacing:-.8px;
      text-wrap:balance;
    }

    .gob360-dashboard-copy p{
      max-width:760px;
      margin:10px 0 0;
      color:var(--gob-muted);
      font-size:14px;
      line-height:1.65;
    }

    .gob360-dashboard-actions{
      display:flex;
      flex-wrap:wrap;
      justify-content:flex-end;
      gap:10px;
      max-width:430px;
    }

    .gob360-dashboard-status{
      display:grid;
      grid-template-columns:repeat(3,minmax(0,1fr));
      border-top:1px solid rgba(129,211,255,.11);
      background:rgba(0,0,0,.12);
    }

    .gob360-dashboard-status__item{
      display:flex;
      align-items:center;
      gap:12px;
      min-width:0;
      padding:16px 22px;
      border-right:1px solid rgba(129,211,255,.1);
    }

    .gob360-dashboard-status__item:last-child{ border-right:0; }

    .gob360-dashboard-status__icon{
      width:38px;
      height:38px;
      flex:0 0 38px;
      display:grid;
      place-items:center;
      border:1px solid rgba(37,217,255,.22);
      border-radius:13px;
      color:var(--gob-cyan);
      background:rgba(37,217,255,.075);
      box-shadow:inset 0 0 18px rgba(37,217,255,.04);
    }

    .gob360-dashboard-status__label{
      margin:0;
      color:var(--gob-muted-2);
      font-size:10px;
      font-weight:900;
      letter-spacing:1.25px;
      text-transform:uppercase;
    }

    .gob360-dashboard-status__value{
      margin:2px 0 0;
      color:#fff;
      font-size:13px;
      font-weight:850;
      white-space:nowrap;
      overflow:hidden;
      text-overflow:ellipsis;
    }

    .dashboard-quicknav{
      display:flex;
      align-items:center;
      gap:8px;
      overflow-x:auto;
      padding:2px 2px 10px;
      scrollbar-width:thin;
      scrollbar-color:rgba(37,217,255,.24) transparent;
    }

    .dashboard-quicknav a{
      display:inline-flex;
      align-items:center;
      gap:8px;
      flex:0 0 auto;
      min-height:38px;
      padding:8px 13px;
      border:1px solid var(--gob-border);
      border-radius:999px;
      color:var(--gob-muted);
      background:rgba(8,22,40,.72);
      text-decoration:none;
      font-size:12px;
      font-weight:800;
      transition:.2s ease;
    }

    .dashboard-quicknav a:hover{
      color:#fff;
      border-color:rgba(37,217,255,.36);
      background:rgba(37,217,255,.09);
      transform:translateY(-1px);
    }

    /* =========================
       BOTONES Y ETIQUETAS
       ========================= */
    .btn-wow{
      display:inline-flex;
      align-items:center;
      justify-content:center;
      gap:8px;
      min-height:42px;
      padding:.66rem .95rem;
      border:1px solid var(--gob-border);
      border-radius:13px;
      color:var(--gob-text) !important;
      background:rgba(255,255,255,.045);
      box-shadow:0 10px 26px rgba(0,0,0,.18);
      font-size:12px;
      font-weight:900;
      text-decoration:none !important;
      transition:transform .2s ease, background .2s ease, border-color .2s ease, box-shadow .2s ease;
    }

    .btn-wow:hover{
      transform:translateY(-2px);
      border-color:rgba(37,217,255,.34);
      background:rgba(37,217,255,.09);
      box-shadow:0 14px 32px rgba(0,0,0,.24), 0 0 22px rgba(37,217,255,.08);
    }

    .btn-wow.primary{
      border-color:rgba(37,217,255,.38);
      background:linear-gradient(135deg, rgba(35,136,255,.32), rgba(37,217,255,.16));
    }

    .btn-wow.good{
      border-color:rgba(44,245,196,.34);
      background:linear-gradient(135deg, rgba(44,245,196,.17), rgba(35,136,255,.12));
    }

    .btn-wow.tv{
      border-color:rgba(255,209,102,.35);
      background:linear-gradient(135deg, rgba(255,209,102,.16), rgba(35,136,255,.13));
    }

    .chip,
    .trend,
    .mini-pill,
    .corner-badge{
      display:inline-flex;
      align-items:center;
      gap:.42rem;
      border:1px solid var(--gob-border);
      border-radius:999px;
      background:rgba(0,0,0,.18);
      color:var(--gob-muted);
      white-space:nowrap;
    }

    .chip{ padding:.38rem .68rem; font-size:11.5px; }
    .chip b{ color:#fff; font-weight:900; }

    .trend{
      padding:.24rem .56rem;
      color:#eef8ff;
      font-size:11px;
      font-weight:900;
    }

    .trend.good,
    .mini-pill{ border-color:rgba(53,229,139,.32); }
    .trend.warn{ border-color:rgba(255,209,102,.35); }
    .trend.bad{ border-color:rgba(255,99,125,.42); }

    .mini-pill{
      padding:.25rem .58rem;
      color:#eaf9ff;
      font-size:11px;
      font-weight:900;
    }

    /* =========================
       SECCIONES Y BENTO
       ========================= */
    .bento-grid{
      display:grid;
      grid-template-columns:repeat(12,minmax(0,1fr));
      gap:16px;
      margin-top:8px;
    }

    .dashboard-section-heading{
      grid-column:span 12;
      display:flex;
      align-items:flex-end;
      justify-content:space-between;
      gap:18px;
      padding:18px 4px 2px;
      scroll-margin-top:92px;
    }

    .dashboard-section-heading__title{
      display:flex;
      align-items:center;
      gap:12px;
      margin:0;
      color:#fff;
      font-size:clamp(17px,1.5vw,22px);
      font-weight:950;
      letter-spacing:-.25px;
    }

    .dashboard-section-heading__icon{
      width:38px;
      height:38px;
      display:grid;
      place-items:center;
      flex:0 0 38px;
      border:1px solid rgba(37,217,255,.23);
      border-radius:13px;
      color:var(--gob-cyan);
      background:linear-gradient(135deg, rgba(35,136,255,.15), rgba(37,217,255,.075));
    }

    .dashboard-section-heading__copy{
      margin:4px 0 0 50px;
      color:var(--gob-muted-2);
      font-size:12px;
    }

    .dashboard-section-heading__line{
      flex:1;
      height:1px;
      margin-bottom:17px;
      background:linear-gradient(90deg, rgba(37,217,255,.28), transparent);
    }

    .span-12{ grid-column:span 12; }
    .span-8{ grid-column:span 8; }
    .span-6{ grid-column:span 6; }
    .span-4{ grid-column:span 4; }
    .span-3{ grid-column:span 3; }
    .span-2{ grid-column:span 2; }

    /* =========================
       TARJETAS
       ========================= */
    .kpi-card,
    .panel-card{
      position:relative;
      height:100%;
      overflow:hidden;
      border:1px solid var(--gob-border);
      border-radius:var(--gob-radius-lg);
      background:linear-gradient(145deg, rgba(13,31,55,.82), rgba(8,20,37,.76));
      box-shadow:var(--gob-shadow-soft);
      backdrop-filter:blur(16px);
      -webkit-backdrop-filter:blur(16px);
    }

    .kpi-card{
      min-height:138px;
      padding:18px;
      transition:transform .22s ease, border-color .22s ease, box-shadow .22s ease;
    }

    .kpi-card::before{
      content:"";
      position:absolute;
      inset:0;
      pointer-events:none;
      background:
        radial-gradient(320px 150px at 0% 0%, rgba(35,136,255,.12), transparent 62%),
        radial-gradient(260px 140px at 100% 100%, rgba(37,217,255,.055), transparent 62%);
    }

    .kpi-card::after{
      content:"";
      position:absolute;
      left:18px;
      right:18px;
      bottom:0;
      height:2px;
      border-radius:999px 999px 0 0;
      background:linear-gradient(90deg, transparent, rgba(37,217,255,.62), transparent);
      opacity:.55;
    }

    .kpi-card > *,
    .panel-card > *{ position:relative; z-index:1; }

    .kpi-card:hover{
      transform:translateY(-4px);
      border-color:rgba(37,217,255,.28);
      box-shadow:0 22px 52px rgba(0,0,0,.34), 0 0 28px rgba(35,136,255,.07);
    }

    .kpi-card.feature{
      border-color:rgba(37,217,255,.24);
      background:linear-gradient(145deg, rgba(20,62,105,.42), rgba(8,23,42,.86));
    }

    .kpi-card.critical{
      border-color:rgba(255,99,125,.27);
      background:linear-gradient(145deg, rgba(93,27,48,.35), rgba(29,19,33,.82));
    }

    .kpi-card.critical::after{
      background:linear-gradient(90deg, transparent, rgba(255,99,125,.78), transparent);
    }

    .kpi-card.soft{
      background:linear-gradient(145deg, rgba(12,30,52,.72), rgba(7,18,34,.8));
    }

    .kpi-card.mega{
      min-height:154px;
      padding:22px;
      border-radius:24px;
    }

    .kpi-card.mega.exec{
      border-color:rgba(53,229,139,.27);
      background:linear-gradient(135deg, rgba(22,98,72,.34), rgba(10,29,48,.86));
    }

    .kpi-card.mega.inv{
      border-color:rgba(35,136,255,.3);
      background:linear-gradient(135deg, rgba(24,74,130,.38), rgba(9,25,46,.88));
    }

    .kpi-card.avance-center{
      min-height:180px;
      display:flex;
      flex-direction:column;
      align-items:center;
      justify-content:center;
      text-align:center;
      padding:25px 18px;
      border-color:rgba(37,217,255,.28);
      background:
        radial-gradient(500px 190px at 50% 0%, rgba(37,217,255,.15), transparent 65%),
        linear-gradient(145deg, rgba(18,48,82,.58), rgba(8,22,40,.86));
    }

    .kpi-top{
      display:flex;
      align-items:flex-start;
      justify-content:space-between;
      gap:14px;
    }

    .kpi-ico{
      width:48px;
      height:48px;
      flex:0 0 48px;
      display:grid;
      place-items:center;
      border:1px solid rgba(37,217,255,.2);
      border-radius:16px;
      color:var(--gob-cyan);
      background:linear-gradient(145deg, rgba(35,136,255,.13), rgba(37,217,255,.06));
      box-shadow:inset 0 0 18px rgba(37,217,255,.035);
      font-size:19px;
    }

    .critical .kpi-ico{
      color:#ff8da0;
      border-color:rgba(255,99,125,.25);
      background:rgba(255,99,125,.075);
    }

    .kpi-label{
      max-width:100%;
      margin:0;
      color:var(--gob-muted);
      font-size:11px;
      font-weight:850;
      line-height:1.4;
      letter-spacing:.72px;
      text-transform:uppercase;
    }

    .kpi-value{
      margin:7px 0 0;
      color:#fff;
      font-size:clamp(24px,2vw,38px);
      line-height:1.06;
      font-weight:950;
      letter-spacing:-.7px;
    }

    .kpi-card.mega .kpi-value{
      font-size:clamp(25px,2.15vw,42px);
      overflow-wrap:anywhere;
    }

    .kpi-card.avance-center .kpi-value{
      margin-top:10px;
      font-size:clamp(40px,4vw,66px);
      background:linear-gradient(90deg,#fff,var(--gob-cyan),var(--gob-teal));
      -webkit-background-clip:text;
      background-clip:text;
      color:transparent;
    }

    .kpi-meta{
      display:flex;
      align-items:center;
      justify-content:space-between;
      gap:10px;
      margin-top:15px;
      color:var(--gob-muted-2);
      font-size:11.5px;
    }

    .kpi-card.avance-center .kpi-meta{
      justify-content:center;
      flex-wrap:wrap;
      margin-top:14px;
    }

    .panel-card{
      padding:20px;
    }

    .panel-title{
      display:flex;
      align-items:flex-start;
      justify-content:space-between;
      gap:14px;
      margin-bottom:16px;
    }

    .panel-title h6{
      display:flex;
      align-items:center;
      gap:9px;
      margin:0;
      color:#fff;
      font-size:15px;
      font-weight:950;
      line-height:1.35;
      letter-spacing:-.15px;
    }

    .panel-title h6 i{ color:var(--gob-cyan); }
    .panel-title small{ color:var(--gob-muted-2); font-size:11px; }

    /* Progreso */
    .progress-wow{
      height:11px;
      overflow:hidden;
      border:1px solid rgba(129,211,255,.12);
      border-radius:999px;
      background:rgba(255,255,255,.055);
    }

    .progress-wow > span{
      display:block;
      height:100%;
      border-radius:999px;
      background:linear-gradient(90deg,var(--gob-blue),var(--gob-cyan));
      box-shadow:0 0 18px rgba(37,217,255,.22);
    }

    .progress-wow.green > span{
      background:linear-gradient(90deg,var(--gob-teal),var(--gob-green));
      box-shadow:0 0 18px rgba(44,245,196,.18);
    }

    /* Alertas */
    .alert-wow{
      position:relative;
      overflow:hidden;
      margin-bottom:11px;
      padding:14px 15px 14px 18px;
      border:1px solid var(--gob-border);
      border-radius:15px;
      background:rgba(0,0,0,.15);
    }

    .alert-wow::before{
      content:"";
      position:absolute;
      left:0;
      top:0;
      bottom:0;
      width:3px;
      background:var(--gob-cyan);
    }

    .alert-wow.danger{ border-color:rgba(255,99,125,.28); }
    .alert-wow.danger::before{ background:var(--gob-red); }
    .alert-wow.warning{ border-color:rgba(255,209,102,.29); }
    .alert-wow.warning::before{ background:var(--gob-yellow); }
    .alert-wow.info{ border-color:rgba(37,217,255,.27); }
    .alert-wow.success{ border-color:rgba(53,229,139,.27); }
    .alert-wow.success::before{ background:var(--gob-green); }

    .alert-wow .t{ margin:0; color:#fff; font-size:13px; font-weight:950; }
    .alert-wow .m{ margin:5px 0 0; color:var(--gob-muted); font-size:12px; line-height:1.55; }

    /* Estados SIGID */
    .panel-card.estados-pro{ border-radius:24px; }

    .estado-row{
      display:grid;
      grid-template-columns:minmax(150px,220px) 1fr 70px 64px;
      align-items:center;
      gap:12px;
      padding:11px 0;
      border-bottom:1px solid rgba(129,211,255,.09);
    }

    .estado-row:last-child{ border-bottom:0; }
    .estado-label{ color:var(--gob-muted); font-size:12px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap; }
    .estado-count{ color:#fff; font-size:13px; font-weight:950; text-align:right; }
    .estado-pct{ color:var(--gob-muted-2); font-size:11px; font-weight:850; text-align:right; }

    .estado-bar-track{
      position:relative;
      height:10px;
      overflow:hidden;
      border:1px solid rgba(129,211,255,.1);
      border-radius:999px;
      background:rgba(255,255,255,.05);
    }

    .estado-bar-fill{
      display:block;
      height:100%;
      border-radius:999px;
      background:linear-gradient(90deg,var(--gob-blue),var(--gob-cyan),var(--gob-teal));
      box-shadow:0 0 18px rgba(37,217,255,.18);
      transition:width .55s ease;
    }

    .estado-row.big{ grid-template-columns:minmax(160px,230px) 1fr 72px 66px; padding:11px 0; }
    .estado-bar-track.big{ height:11px; }
    .estado-label.big{ color:var(--gob-muted); font-size:12px; }
    .estado-count.big{ font-size:13px; }
    .estado-pct.big{ color:var(--gob-muted-2); font-size:11px; }

    .estado-bar-fill.big{ position:relative; }
    .estado-bar-fill.big::after{
      content:"";
      position:absolute;
      top:0;
      left:-45%;
      width:38%;
      height:100%;
      background:linear-gradient(90deg,transparent,rgba(255,255,255,.34),transparent);
      transform:skewX(-18deg);
      animation:shimmerMove 2.5s infinite;
      opacity:.55;
    }

    @keyframes shimmerMove{ from{left:-45%;} to{left:112%;} }

    /* Gráficas */
    .chart-wrap{ position:relative; height:300px; }

    .chart-shell{
      position:relative;
      width:100%;
      overflow:hidden;
      border:1px solid rgba(129,211,255,.12);
      border-radius:20px;
      background:
        radial-gradient(480px 240px at 50% 0%, rgba(35,136,255,.09), transparent 68%),
        rgba(4,14,28,.58);
      padding:14px;
    }

    .chart-shell--large{ min-height:520px; height:520px; }

    #chartInstituciones .highcharts-background,
    #chartCategoriasInfra .highcharts-background{ fill:transparent; }

    #chartInstituciones text,
    #chartInstituciones .highcharts-data-label text,
    #chartCategoriasInfra text,
    #chartCategoriasInfra .highcharts-data-label text{
      fill:#dce9f8 !important;
    }

    #chartInstituciones .highcharts-tooltip-box,
    #chartCategoriasInfra .highcharts-tooltip-box{
      fill:rgba(5,15,29,.97) !important;
      stroke:rgba(37,217,255,.28) !important;
    }

    #chartInstituciones .highcharts-tooltip text,
    #chartInstituciones .highcharts-tooltip tspan,
    #chartCategoriasInfra .highcharts-tooltip text,
    #chartCategoriasInfra .highcharts-tooltip tspan{
      fill:#eef8ff !important;
      color:#eef8ff !important;
    }

    /* Barra TV */
    .tv-floatbar{
      position:fixed;
      top:14px;
      right:14px;
      z-index:99999;
      display:none;
      gap:8px;
    }

    body.tv-mode .tv-floatbar{ display:flex; }

    body.tv-mode{
      background:
        radial-gradient(1100px 520px at 8% 5%, rgba(35,136,255,.25), transparent 62%),
        radial-gradient(1000px 520px at 92% 10%, rgba(37,217,255,.14), transparent 62%),
        linear-gradient(180deg,#020611,#071426);
    }

    body.tv-mode .pcoded-navbar,
    body.tv-mode .navbar,
    body.tv-mode header,
    body.tv-mode .page-header,
    body.tv-mode .pcoded-header,
    body.tv-mode .m-header,
    body.tv-mode .mobile-menu{
      display:none !important;
      visibility:hidden !important;
      height:0 !important;
      overflow:hidden !important;
    }

    body.tv-mode .pcoded-main-container{ margin-left:0 !important; width:100% !important; }
    body.tv-mode .pcoded-content{ max-width:none; padding-top:18px !important; }
    body.tv-mode .gob360-dashboard-copy h1{ font-size:clamp(32px,3.1vw,48px); }
    body.tv-mode .kpi-value{ font-size:38px; }
    body.tv-mode .kpi-label{ font-size:13px; }
    body.tv-mode .kpi-meta{ font-size:13px; }
    body.tv-mode .chart-wrap{ height:370px; }

    /* Breadcrumb heredado */
    .breadcrumb .breadcrumb-item a{ color:var(--gob-muted) !important; }
    .breadcrumb .breadcrumb-item.active{ color:#fff !important; }

    /* Loader */
    .loader-track{ background:rgba(255,255,255,.06) !important; }
    .loader-fill{ background:linear-gradient(90deg,var(--gob-blue),var(--gob-cyan),var(--gob-teal)) !important; }

    /* Responsive */
    @media (max-width:1199px){
      .bento-grid{ grid-template-columns:repeat(8,minmax(0,1fr)); }
      .span-12,.span-8,.dashboard-section-heading{ grid-column:span 8; }
      .span-6,.span-4,.span-3,.span-2{ grid-column:span 4; }
      .gob360-dashboard-hero__main{ grid-template-columns:1fr; }
      .gob360-dashboard-actions{ justify-content:flex-start; max-width:none; }
    }

    @media (max-width:991px){
      .pcoded-content{ padding:18px 16px 34px !important; }
      .gob360-dashboard-brand{ align-items:flex-start; }
      .gob360-dashboard-brand__logo-wrap{ min-width:180px; }
      .gob360-dashboard-brand__logo{ width:190px; }
      .chart-wrap{ height:270px; }
    }

    @media (max-width:767px){
      .bento-grid{ grid-template-columns:repeat(4,minmax(0,1fr)); gap:12px; }
      .span-12,.span-8,.span-6,.span-4,.span-3,.span-2,.dashboard-section-heading{ grid-column:span 4; }
      .gob360-dashboard-hero{ border-radius:22px; }
      .gob360-dashboard-hero__main{ padding:22px 18px 18px; }
      .gob360-dashboard-brand{ flex-direction:column; gap:14px; }
      .gob360-dashboard-brand__logo-wrap{ min-width:0; width:100%; justify-content:flex-start; }
      .gob360-dashboard-brand__logo{ width:min(270px,78vw); }
      .gob360-dashboard-status{ grid-template-columns:1fr; }
      .gob360-dashboard-status__item{ border-right:0; border-bottom:1px solid rgba(129,211,255,.1); }
      .gob360-dashboard-status__item:last-child{ border-bottom:0; }
      .dashboard-section-heading{ align-items:center; }
      .dashboard-section-heading__line{ display:none; }
      .dashboard-section-heading__copy{ margin-left:50px; }
      .kpi-card{ min-height:130px; }
      .panel-title{ flex-direction:column; }
      .estado-row,
      .estado-row.big{ grid-template-columns:minmax(110px,145px) 1fr 48px; gap:8px; }
      .estado-pct{ display:none; }
      .chart-shell--large{ min-height:430px; height:430px; }
    }

    @media (max-width:480px){
      .pcoded-content{ padding:14px 12px 28px !important; }
      .gob360-dashboard-copy h1{ font-size:27px; }
      .gob360-dashboard-copy p{ font-size:13px; }
      .gob360-dashboard-actions{ width:100%; }
      .gob360-dashboard-actions .btn-wow{ flex:1 1 calc(50% - 6px); }
      .kpi-card,.panel-card{ border-radius:18px; }
      .kpi-card{ padding:16px; }
      .kpi-value{ font-size:28px; }
      .kpi-meta{ align-items:flex-start; flex-direction:column; }
      .kpi-card.avance-center .kpi-meta{ flex-direction:row; }
      .dashboard-section-heading__title{ font-size:18px; }
      .dashboard-section-heading__copy{ margin-left:0; }
      .estado-row,
      .estado-row.big{ grid-template-columns:1fr 52px; }
      .estado-bar-track{ grid-column:1 / -1; grid-row:2; }
    }
  </style>
</head>

<body class="dashboard-body">
  <div class="loader-bg">
    <div class="loader-track"><div class="loader-fill"></div></div>
  </div>

  <?php include './admin/include/navbar.php'; ?>
  <?php include './admin/include/header.php'; ?>

  <div class="tv-floatbar">
    <button type="button" class="btn btn-wow" id="btnExitTV">
      <i class="bi bi-x-circle"></i> Salir TV
    </button>
    <button type="button" class="btn btn-wow primary" id="btnFullscreenTV">
      <i class="bi bi-arrows-fullscreen"></i> Fullscreen
    </button>
  </div>

  <div class="pcoded-main-container">
    <div class="pcoded-content">

      <!-- CABECERA EJECUTIVA GOB360 -->
      <section class="gob360-dashboard-hero" aria-labelledby="dashboardTitle">
        <div class="gob360-dashboard-hero__main">
          <div class="gob360-dashboard-brand">
            <div class="gob360-dashboard-brand__logo-wrap">
              <img
                src="assets/img/gob360l.png"
                alt="Logo GOB360"
                class="gob360-dashboard-brand__logo"
              >
            </div>

            <div class="gob360-dashboard-copy">
              <p class="gob360-dashboard-copy__eyebrow">Plataforma institucional</p>
              <h1 id="dashboardTitle">Panel Ejecutivo del Mandatario</h1>
              <p>
                Información territorial, compromisos, seguridad, inversión y ejecución pública
                organizada en una sola vista para facilitar decisiones oportunas.
              </p>
            </div>
          </div>

          <div class="gob360-dashboard-actions">
            <a class="btn btn-wow primary" href="#secGobierno"><i class="bi bi-speedometer2"></i> Resumen</a>
            <a class="btn btn-wow good" href="#panelTerritorio"><i class="bi bi-map"></i> Territorio</a>
            <a class="btn btn-wow" href="#panelInversion"><i class="bi bi-cash-stack"></i> Inversión</a>
            <button type="button" class="btn btn-wow tv" id="btnTVMode">
              <i class="bi bi-tv"></i> Modo TV
            </button>
            <?php include './admin/include/btn_back.php'; ?>
          </div>
        </div>

        <div class="gob360-dashboard-status">
          <div class="gob360-dashboard-status__item">
            <span class="gob360-dashboard-status__icon"><i class="bi bi-shield-check"></i></span>
            <div>
              <p class="gob360-dashboard-status__label">Estado del sistema</p>
              <p class="gob360-dashboard-status__value"><?= $isvalid ? 'Operativo y disponible' : 'Sin validación' ?></p>
            </div>
          </div>

          <div class="gob360-dashboard-status__item">
            <span class="gob360-dashboard-status__icon"><i class="bi bi-clock-history"></i></span>
            <div>
              <p class="gob360-dashboard-status__label">Última actualización</p>
              <p class="gob360-dashboard-status__value"><?= $fechaHoy ?> · <?= $horaHoy ?></p>
            </div>
          </div>

          <div class="gob360-dashboard-status__item">
            <span class="gob360-dashboard-status__icon"><i class="bi bi-geo-alt"></i></span>
            <div>
              <p class="gob360-dashboard-status__label">Cobertura institucional</p>
              <p class="gob360-dashboard-status__value">Santander · Control estratégico</p>
            </div>
          </div>
        </div>
      </section>

      <nav class="dashboard-quicknav" aria-label="Navegación rápida del dashboard">
        <a href="#secGobierno"><i class="bi bi-bank"></i> Gobierno</a>
        <a href="#secSeguridad"><i class="bi bi-shield-check"></i> Seguridad</a>
        <a href="#secPae"><i class="bi bi-mortarboard"></i> PAE</a>
        <a href="#secProyectos"><i class="bi bi-kanban"></i> Proyectos</a>
        <a href="#panelAlertas"><i class="bi bi-bell"></i> Alertas</a>
        <a href="#panelTerritorio"><i class="bi bi-map"></i> Territorio</a>
        <a href="#panelInversion"><i class="bi bi-currency-dollar"></i> Inversión</a>
        <a href="#secHacienda"><i class="bi bi-building-check"></i> Hacienda</a>
      </nav>

      <!-- ==========================
           KPIs ORGANIZADOS EN BENTO GRID
           ========================== -->
      <div class="bento-grid">

        <div class="dashboard-section-heading span-12" id="secGobierno">
          <div>
            <h2 class="dashboard-section-heading__title">
              <span class="dashboard-section-heading__icon"><i class="bi bi-bank"></i></span>
              Gobierno y compromisos
            </h2>
            <p class="dashboard-section-heading__copy">Seguimiento ejecutivo, agenda institucional y cumplimiento.</p>
          </div>
          <span class="dashboard-section-heading__line"></span>
        </div>

        <div class="kpi-card feature span-6">
          <div class="kpi-top">
            <div>
              <p class="kpi-label">Consejo de Gobierno</p>
              <p class="kpi-value"><?= $semanaActual !== null ? 'Consejo de Gobierno N.º ' . $semanaActual : '—' ?></p>
            </div>
            <div class="kpi-ico"><i class="bi bi-people-fill"></i></div>
          </div>
          <div class="kpi-meta">
            <span>Seguimiento semanal</span>
            <span class="trend <?= $semanaActual !== null ? 'good' : 'warn' ?>">
              <i class="bi bi-calendar-week"></i> <?= $semanaActual !== null ? 'En curso' : 'Fuera de rango' ?>
            </span>
          </div>
        </div>

        <div class="kpi-card feature span-6">
          <div class="kpi-top">
            <div>
              <p class="kpi-label">Visitas realizadas</p>
              <p class="kpi-value"><?= number_format($visitas, 0, ',', '.') ?></p>
            </div>
            <div class="kpi-ico"><i class="bi bi-calendar2-check"></i></div>
          </div>
          <div class="kpi-meta">
            <span>Gestión en territorio</span>
            <span class="trend good"><i class="bi bi-arrow-up"></i> En marcha</span>
          </div>
        </div>

        <div class="kpi-card critical span-4">
          <div class="kpi-top">
            <div>
              <p class="kpi-label">Compromisos sin cumplir</p>
              <p class="kpi-value"><?= number_format($compromisosSinCumplir, 0, ',', '.') ?></p>
            </div>
            <div class="kpi-ico"><i class="bi bi-exclamation-circle"></i></div>
          </div>
          <div class="kpi-meta">
            <span>Pendientes</span>
            <span class="trend <?= ($compromisosSinCumplir > 0 ? 'bad' : 'good') ?>">
              <i class="bi bi-clock-history"></i> <?= ($compromisosSinCumplir > 0 ? 'Atención' : 'Al día') ?>
            </span>
          </div>
        </div>

        <div class="kpi-card span-4">
          <div class="kpi-top">
            <div>
              <p class="kpi-label">Total compromisos</p>
              <p class="kpi-value"><?= number_format($totalCompromisos, 0, ',', '.') ?></p>
            </div>
            <div class="kpi-ico"><i class="bi bi-clipboard-check"></i></div>
          </div>
          <div class="kpi-meta">
            <span>Registrados</span>
            <span class="trend <?= ($totalCompromisos > 0 ? 'good' : 'warn') ?>">
              <i class="bi bi-list-check"></i> <?= ($totalCompromisos > 0 ? 'Activo' : 'Sin datos') ?>
            </span>
          </div>
        </div>

        <div class="dashboard-section-heading span-12" id="secSeguridad">
          <div>
            <h2 class="dashboard-section-heading__title">
              <span class="dashboard-section-heading__icon"><i class="bi bi-shield-check"></i></span>
              Seguridad territorial
            </h2>
            <p class="dashboard-section-heading__copy">Indicadores prioritarios para la protección y convivencia ciudadana.</p>
          </div>
          <span class="dashboard-section-heading__line"></span>
        </div>

        <div class="kpi-card span-4">
          <div class="kpi-top">
            <div>
              <p class="kpi-label">Municipios sin homicidios</p>
              <p class="kpi-value"><?= number_format($munSinHomicidios, 0, ',', '.') ?></p>
            </div>
            <div class="kpi-ico"><i class="bi bi-patch-check"></i></div>
          </div>
          <div class="kpi-meta">
            <span><?= $anioSeguridad ?></span>
            <span class="trend <?= ($munSinHomicidios > 0 ? 'good' : 'warn') ?>">
              <i class="bi bi-geo-alt"></i> <?= ($munSinHomicidios > 0 ? 'Positivo' : 'Sin datos') ?>
            </span>
          </div>
        </div>

        <div class="kpi-card critical span-4">
          <div class="kpi-top">
            <div>
              <p class="kpi-label">Homicidio por Sicariato</p>
              <p class="kpi-value"><?= number_format($homSicariato, 0, ',', '.') ?></p>
            </div>
            <div class="kpi-ico"><i class="bi bi-exclamation-circle"></i></div>
          </div>
          <div class="kpi-meta">
            <span></span>
            <span class="trend <?= ($homSicariato > 0 ? 'bad' : 'good') ?>">
              <i class="bi bi-clock-history"></i> <?= ($homSicariato > 0 ? 'Atención' : 'Al día') ?>
            </span>
          </div>
        </div>


        <div class="kpi-card critical span-4">
          <div class="kpi-top">
            <div>
              <p class="kpi-label">Homicidios por Intolerancia</p>
              <p class="kpi-value"><?= number_format($homIntolerancia, 0, ',', '.') ?></p>
            </div>
            <div class="kpi-ico"><i class="bi bi-exclamation-circle"></i></div>
          </div>
          <div class="kpi-meta">
            <span></span>
            <span class="trend <?= ($homIntolerancia > 0 ? 'bad' : 'good') ?>">
              <i class="bi bi-clock-history"></i> <?= ($homIntolerancia > 0 ? 'Atención' : 'Al día') ?>
            </span>
          </div>
        </div>

        <div class="kpi-card span-4">
          <div class="kpi-top">
            <div>
              <p class="kpi-label">Tasa de homicidios x 100.000 habitantes · Santander 2026</p>
              <p class="kpi-value">3,4%</p>
            </div>
            <div class="kpi-ico"><i class="bi bi-exclamation-triangle"></i></div>
          </div>
          <div class="kpi-meta">
            <span><?= $anioSeguridad ?></span>
            <span class="trend bad"><i class="bi bi-graph-up-arrow"></i> Monitoreo</span>
          </div>
        </div>

        <div class="dashboard-section-heading span-12" id="secPae">
          <div>
            <h2 class="dashboard-section-heading__title">
              <span class="dashboard-section-heading__icon"><i class="bi bi-mortarboard"></i></span>
              Programa de Alimentación Escolar
            </h2>
            <p class="dashboard-section-heading__copy">Cobertura departamental consolidada para sedes y población estudiantil.</p>
          </div>
          <span class="dashboard-section-heading__line"></span>
        </div>

        <div class="kpi-card soft span-3">
          <div class="kpi-top">
            <div>
              <p class="kpi-label">PAE · Sedes</p>
              <p class="kpi-value"><?= number_format($paeSedes, 0, ',', '.') ?></p>
            </div>
            <div class="kpi-ico"><i class="bi bi-building"></i></div>
          </div>
          <div class="kpi-meta">
            <span>ArcGIS</span>
            <span class="trend <?= ($paeSedes > 0 ? 'good' : 'warn') ?>"><i class="bi bi-geo-alt-fill"></i> <?= ($paeSedes > 0 ? 'En línea' : 'Sin datos') ?></span>
          </div>
        </div>

        <div class="kpi-card soft span-3">
          <div class="kpi-top">
            <div>
              <p class="kpi-label">PAE · Niños</p>
              <p class="kpi-value"><?= number_format($paeNinos, 0, ',', '.') ?></p>
            </div>
            <div class="kpi-ico"><i class="bi bi-people-fill"></i></div>
          </div>
          <div class="kpi-meta">
            <span>Focalizados</span>
            <span class="trend <?= ($paeNinos > 0 ? 'good' : 'warn') ?>"><i class="bi bi-heart-pulse"></i> <?= ($paeNinos > 0 ? 'Activo' : 'Sin datos') ?></span>
          </div>
        </div>

        <div class="kpi-card soft span-3">
          <div class="kpi-top">
            <div>
              <p class="kpi-label">PAE · Rural</p>
              <p class="kpi-value"><?= number_format($paeRural, 0, ',', '.') ?></p>
            </div>
            <div class="kpi-ico"><i class="bi bi-tree"></i></div>
          </div>
          <div class="kpi-meta">
            <span>Sedes</span>
            <span class="trend good"><i class="bi bi-check-circle"></i> ArcGIS</span>
          </div>
        </div>

        <div class="kpi-card soft span-3">
          <div class="kpi-top">
            <div>
              <p class="kpi-label">PAE · Urbana</p>
              <p class="kpi-value"><?= number_format($paeUrbana, 0, ',', '.') ?></p>
            </div>
            <div class="kpi-ico"><i class="bi bi-buildings"></i></div>
          </div>
          <div class="kpi-meta">
            <span>Sedes</span>
            <span class="trend good"><i class="bi bi-check-circle"></i> ArcGIS</span>
          </div>
        </div>

        <div class="dashboard-section-heading span-12" id="secProyectos">
          <div>
            <h2 class="dashboard-section-heading__title">
              <span class="dashboard-section-heading__icon"><i class="bi bi-kanban"></i></span>
              Proyectos, ejecución e inversión
            </h2>
            <p class="dashboard-section-heading__copy">Avance físico, financiero y distribución sectorial de los recursos.</p>
          </div>
          <span class="dashboard-section-heading__line"></span>
        </div>

        <!-- RPC: Total proyectos -->
        <div class="kpi-card feature span-3">
          <div class="kpi-top">
            <div>
              <p class="kpi-label">Proyectos 2025</p>
              <p class="kpi-value" id="dash_kpi_total_proyectos">—</p>
            </div>
            <div class="kpi-ico"><i class="bi bi-folder2-open"></i></div>
          </div>
          <div class="kpi-meta">
            <span>Total registrados</span>
            <span class="trend good"><i class="bi bi-bar-chart-line"></i> Resumen</span>
          </div>
        </div>

        <!-- MEGA Ejecutado -->
        <div class="kpi-card mega exec span-4">
         
          <div class="kpi-top">
            <div>
              <p class="kpi-label">Valor Ejecutado 2025</p>
              <p class="kpi-value" id="dash_kpi_valor_ejecutado" style="word-break:break-word;">—</p>
            </div>
            <div class="kpi-ico"><i class="bi bi-cash-stack"></i></div>
          </div>
          <div class="kpi-meta">
            <span>Ejecución presupuestal</span>
            <span class="mini-pill"><i class="bi bi-check2-circle"></i> Visible</span>
          </div>
        </div>

        <!-- MEGA Inversión -->
        <div class="kpi-card mega inv span-4">
         
          <div class="kpi-top">
            <div>
              <p class="kpi-label">Inversión Total 2025</p>
              <p class="kpi-value" id="dash_kpi_valor_total" style="word-break:break-word;">—</p>
            </div>
            <div class="kpi-ico"><i class="bi bi-currency-dollar"></i></div>
          </div>
          <div class="kpi-meta">
            <span>Valor total proyectos</span>
            <span class="mini-pill"><i class="bi bi-stars"></i> Prioridad</span>
          </div>
        </div>

        <!-- AVANCE centrado -->
        <div class="kpi-card avance-center span-12">
          <p class="kpi-label mb-0">Avance Proyectos 2025</p>
          <p class="kpi-value" id="dash_kpi_avance_fisico">—</p>
          <div class="kpi-meta">
            <span class="chip"><i class="bi bi-activity"></i> Físico</span>
            <span class="chip"><i class="bi bi-currency-exchange"></i> <span id="dash_kpi_avance_finan_mini">—</span> Financiero</span>
            <span class="trend good"><i class="bi bi-award"></i> Promedio</span>
          </div>
        </div>

        <div class="dashboard-section-heading span-12" id="secAnalitica">
          <div>
            <h2 class="dashboard-section-heading__title">
              <span class="dashboard-section-heading__icon"><i class="bi bi-bar-chart-line"></i></span>
              Analítica sectorial
            </h2>
            <p class="dashboard-section-heading__copy">Gráficas consolidadas para comparar instituciones, categorías y estados.</p>
          </div>
          <span class="dashboard-section-heading__line"></span>
        </div>

        <!-- Resultados en Materia de Inversión -->
        <div class="panel-card span-12">
          <div class="panel-title">
            <h6><i class="bi bi-pie-chart"></i> Secretaría de Interior - Resultados en Materia de Inversión</h6>
            <small>Secretaría del Interior</small>
          </div>
          <div class="row g-3">
            <div class="col-12 col-md-6">
              <div class="kpi-card soft">
                <p class="kpi-label">Proyectos Totales</p>
                <p class="kpi-value"><?= number_format($proyectosTotalesInv, 0, ',', '.') ?></p>
                <div class="kpi-meta">
                  <span>Total consolidado de registros en seguridad</span>
                  <span class="trend <?= ($proyectosTotalesInv > 0 ? 'good' : 'warn') ?>"><i class="bi bi-folder2-open"></i> Registrados</span>
                </div>
              </div>
            </div>
            <div class="col-12 col-md-6">
              <div class="kpi-card soft">
                <p class="kpi-label">Valor Global</p>
                <p class="kpi-value"><?= $fmtMoney($valorGlobalInv) ?></p>
                <div class="kpi-meta">
                  <span>Monto total ejecutado en todas las líneas</span>
                  <span class="trend <?= ($valorGlobalInv > 0 ? 'good' : 'warn') ?>"><i class="bi bi-currency-dollar"></i> Inversión</span>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Inversión por Institución Beneficiada -->
        <div class="kpi-card feature span-12" style="padding:22px 20px;">
          <div class="kpi-top mb-3">
            <div>
              <p class="kpi-label">Gráfica</p>
              <p class="kpi-value" style="font-size:clamp(16px,1.8vw,24px);">Secretaría de Interior - Inversión por Institución Beneficiada</p>
            </div>
            <div class="kpi-ico"><i class="bi bi-bank2"></i></div>
          </div>
          <?php if (!empty($dataInst)): ?>
          <div id="chartInstituciones" style="min-height:380px;height:380px;width:100%;"></div>
          <?php else: ?>
          <p style="color:var(--muted2);font-size:14px;">No hay datos de instituciones disponibles.</p>
          <?php endif; ?>
        </div>

        <!-- Detalle de inversión por categoría -->
        <div class="panel-card span-12" style="padding:20px;border-radius:22px;">
          <div class="panel-title" style="margin-bottom:14px;">
            <div>
              <h6 style="font-size:18px;"><i class="bi bi-bar-chart"></i>Secretaría de Infraestructura - Detalle de inversión por categoría</h6>
              <p style="margin:4px 0 0;color:var(--muted);font-size:13px;">Secretaría de Infraestructura · Ranking por categoría, recursos y cobertura municipal.</p>
            </div>
            <span class="chip"><?= count($infraDetalle) ?> categorías</span>
          </div>
          <?php if (!empty($infraDetalle)): ?>
          <div class="chart-shell chart-shell--large">
            <div id="chartCategoriasInfra" style="width:100%;height:100%;"></div>
          </div>
          <?php else: ?>
          <p style="color:var(--muted2);font-size:14px;">No hay datos de inversión disponibles.</p>
          <?php endif; ?>
        </div>

        <!-- ESTADOS dentro del bento (FIX: ya no va dentro de .row) -->
        <div class="panel-card estados-pro span-12">
          <div class="panel-title">
            <h6><i class="bi bi-kanban"></i>SIGID - Estados de Proyectos 2025</h6>
            <small id="dash_estados_total_lbl">Cargando…</small>
          </div>

          <div id="dash_panel_estados">
            <div class="d-flex align-items-center gap-2" style="color:var(--muted);font-size:13px;">
              <span class="spinner-border spinner-border-sm" role="status"></span>
              Obteniendo datos de la API…
            </div>
          </div>
        </div>

      </div><!-- /bento-grid -->

      <div class="dashboard-section-heading" id="secTerritorio">
        <div>
          <h2 class="dashboard-section-heading__title">
            <span class="dashboard-section-heading__icon"><i class="bi bi-map"></i></span>
            Alertas y cobertura territorial
          </h2>
          <p class="dashboard-section-heading__copy">Prioridades del día y nivel de presencia institucional en el departamento.</p>
        </div>
        <span class="dashboard-section-heading__line"></span>
      </div>

      <!-- ALERTAS + COBERTURA -->
      <div class="row mt-1">
        <div class="col-12 col-lg-5 mb-3" id="panelAlertas">
          <div class="panel-card">
            <div class="panel-title">
              <h6><i class="bi bi-bell"></i> Alertas estratégicas</h6>
              <small>Prioridades del día</small>
            </div>

            <?php foreach($alertas as $a): ?>
              <div class="alert-wow <?= htmlspecialchars($a['type']) ?>">
                <p class="t mb-0"><?= $a['title'] ?></p>
                <p class="m"><?= $a['msg'] ?></p>
              </div>
            <?php endforeach; ?>

            <div class="mt-2 d-flex flex-wrap gap-2">
              <span class="chip"><i class="bi bi-check2-circle"></i> Menos reporte, más decisión</span>
              <span class="chip"><i class="bi bi-graph-up-arrow"></i> KPIs visibles</span>
            </div>
          </div>
        </div>

        <div class="col-12 col-lg-7 mb-3" id="panelTerritorio">
          <div class="panel-card">
            <div class="panel-title">
              <h6><i class="bi bi-compass"></i> Territorio y cobertura</h6>
              <small>Municipios • Veredas • Provincias</small>
            </div>

            <div class="row g-3">
              <div class="col-12 col-md-6">
                <div class="mb-2 d-flex justify-content-between align-items-center">
                  <span class="chip"><i class="bi bi-buildings"></i> Cobertura Municipal</span>
                  <span class="chip"><b><?= $porcM ?>%</b></span>
                </div>
                <div class="progress-wow">
                  <span style="width: <?= $porcM ?>%;"></span>
                </div>
                <div class="kpi-meta mt-2">
                  <span>Municipios: <b class="text-white"><?= number_format($municipios, 0, ',', '.') ?></b></span>
                  <span class="trend <?= ($porcM>=70?'good':($porcM>=45?'warn':'bad')) ?>"><i class="bi bi-bullseye"></i> Meta</span>
                </div>
              </div>

              <div class="col-12 col-md-6">
                <div class="mb-2 d-flex justify-content-between align-items-center">
                  <span class="chip"><i class="bi bi-signpost-2"></i> Cobertura Veredal</span>
                  <span class="chip"><b><?= $porcV ?>%</b></span>
                </div>
                <div class="progress-wow green">
                  <span style="width: <?= $porcV ?>%;"></span>
                </div>
                <div class="kpi-meta mt-2">
                  <span>Veredas: <b class="text-white"><?= number_format($veredas, 0, ',', '.') ?></b></span>
                  <span class="trend <?= ($porcV>=70?'good':($porcV>=45?'warn':'bad')) ?>"><i class="bi bi-pin-map"></i> Enfoque</span>
                </div>
              </div>

              <div class="col-12">
                <div class="chart-wrap mt-1">
                  <canvas id="chartCobertura"></canvas>
                </div>
              </div>

              <div class="col-12">
                <div class="d-flex flex-wrap gap-2">
                  <span class="chip"><i class="bi bi-diagram-3"></i> Provincias: <b><?= number_format($provincia, 0, ',', '.') ?></b></span>
                  <span class="chip"><i class="bi bi-geo"></i> Control territorial</span>
                  <span class="chip"><i class="bi bi-eye"></i> Enfoque por impacto</span>
                </div>
              </div>
            </div>

          </div>
        </div>
      </div>

      <div class="dashboard-section-heading">
        <div>
          <h2 class="dashboard-section-heading__title">
            <span class="dashboard-section-heading__icon"><i class="bi bi-cash-stack"></i></span>
            Gestión financiera y decisión
          </h2>
          <p class="dashboard-section-heading__copy">Consolidado de inversión, ejecución y acciones recomendadas.</p>
        </div>
        <span class="dashboard-section-heading__line"></span>
      </div>

      <!-- INVERSION + EJECUCION -->
      <div class="row">
        <div class="col-12 col-lg-7 mb-3" id="panelInversion">
          <div class="panel-card">
            <div class="panel-title">
              <h6><i class="bi bi-cash-stack"></i>Inversión y ejecución por secretaría</h6>
              <small>Control financiero ejecutivo</small>
            </div>

            <div class="row g-3">
              <div class="col-12 col-md-4">
                <div class="kpi-card soft">
                  <p class="kpi-label">Valor total proyectos</p>
                  <p class="kpi-value"><?= $fmtMoney($valorproyectos) ?></p>
                  <div class="kpi-meta">
                    <span>Impacto</span>
                    <span class="trend good"><i class="bi bi-currency-dollar"></i> OK</span>
                  </div>
                </div>
              </div>

              <div class="col-12 col-md-4">
                <div class="kpi-card soft">
                  <p class="kpi-label">Promedio por secretaría</p>
                  <p class="kpi-value"><?= $fmtMoney($inversionsec) ?></p>
                  <div class="kpi-meta">
                    <span>Control</span>
                    <span class="trend <?= ($inversionsec>0?'good':'warn') ?>"><i class="bi bi-bank2"></i> Seguimiento</span>
                  </div>
                </div>
              </div>

              <div class="col-12 col-md-4">
                <div class="kpi-card soft">
                  <p class="kpi-label">Secretarías activas</p>
                  <p class="kpi-value"><?= number_format($secretaria, 0, ',', '.') ?></p>
                  <div class="kpi-meta">
                    <span>Responsables</span>
                    <span class="trend <?= ($secretaria>0?'good':'warn') ?>"><i class="bi bi-person-badge"></i> Gestión</span>
                  </div>
                </div>
              </div>

              <div class="col-12">
                <div class="d-flex align-items-center justify-content-between mt-2 mb-1">
                  <small style="color:var(--muted); font-weight:800; text-transform:uppercase; letter-spacing:.2px;">
                    <i class="bi bi-graph-up me-1"></i>Visitas por mes (últimos 6 meses)
                  </small>
                  <small style="color:var(--muted2);">Fuente: tbl_visitas</small>
                </div>
                <div class="chart-wrap">
                  <canvas id="chartVisitas"></canvas>
                </div>
              </div>

              <div class="col-12">
                <div class="d-flex flex-wrap gap-2">
                  <a href="#!" class="btn btn-wow"><i class="bi bi-file-earmark-text"></i> Informe ejecutivo</a>
                  <a href="#!" class="btn btn-wow"><i class="bi bi-download"></i> Exportar</a>
                  <a href="#!" class="btn btn-wow primary"><i class="bi bi-send"></i> Enviar resumen</a>
                </div>
              </div>
            </div>

          </div>
        </div>

        <div class="col-12 col-lg-5 mb-3">
          <div class="panel-card">
            <div class="panel-title">
              <h6><i class="bi bi-lightning-charge"></i> Resumen de decisión</h6>
              <small>Acciones recomendadas</small>
            </div>

            <div class="alert-wow info">
              <p class="t mb-0">1) Prioridad territorial</p>
              <p class="m">Subir cobertura con agenda dirigida: <b>+10 pts</b> en 30 días.</p>
            </div>

            <div class="alert-wow warning">
              <p class="t mb-0">2) Control de ejecución</p>
              <p class="m">Cruzar proyectos vs inversión para detectar <b>brechas</b> (datos incompletos / sin avance).</p>
            </div>

            <div class="alert-wow success">
              <p class="t mb-0">3) Gestión y comunicación</p>
              <p class="m">Resumen semanal: <b>3 KPIs</b>, <b>2 alertas</b>, <b>1 acción</b>.</p>
            </div>

            <div class="mt-2">
              <div class="chip"><i class="bi bi-stars"></i> Modo Gobernador: <b>ON</b></div>
            </div>
          </div>
        </div>
      </div>

      <div class="dashboard-section-heading" id="secHacienda">
        <div>
          <h2 class="dashboard-section-heading__title">
            <span class="dashboard-section-heading__icon"><i class="bi bi-building-check"></i></span>
            Secretaría de Hacienda
          </h2>
          <p class="dashboard-section-heading__copy">Recaudo, operativos, aprehensiones y control tributario.</p>
        </div>
        <span class="dashboard-section-heading__line"></span>
      </div>

      <?php include 'hacienda_section.php'; ?>

    </div><!-- /pcoded-content -->
  </div><!-- /pcoded-main-container -->

  <!-- JS base -->
  <?php include 'admin/include/gerenic_script.php'; ?>
  <script src="assets/js/vendor-all.min.js"></script>
  <script src="assets/js/plugins/bootstrap.min.js"></script>
  <script src="assets/js/pcoded.min.js"></script>
  <script src="assets/js/plugins/prism.js"></script>
  <script src="assets/js/plugins/apexcharts.min.js"></script>

  <!-- Chart.js -->
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

  <!-- Highcharts (recargado al final, como en infra) -->
  <script src="https://code.highcharts.com/highcharts.js"></script>

  <script>
    (function(){
      if(!window.Chart) return;

      Chart.defaults.color = 'rgba(255,255,255,.86)';
      Chart.defaults.font.family = 'system-ui, -apple-system, Segoe UI, Roboto, Arial';
      Chart.defaults.plugins.legend.labels.usePointStyle = true;
      Chart.defaults.plugins.legend.labels.pointStyle = 'circle';

      Chart.defaults.plugins.tooltip.backgroundColor = 'rgba(255,255,255,.95)';
      Chart.defaults.plugins.tooltip.titleColor = '#0B1222';
      Chart.defaults.plugins.tooltip.bodyColor  = '#0B1222';
      Chart.defaults.plugins.tooltip.borderColor = 'rgba(0,0,0,.06)';
      Chart.defaults.plugins.tooltip.borderWidth = 1;

      Chart.defaults.scale.grid.color = 'rgba(255,255,255,.10)';
      Chart.defaults.scale.ticks.backdropColor = 'rgba(0,0,0,0)';
    })();

    const porcM = <?= json_encode($porcM) ?>;
    const porcV = <?= json_encode($porcV) ?>;
    const meses = <?= json_encode($mesesSerie) ?>;
    const visitasSerie = <?= json_encode($visitasSerie) ?>;

    let __chartCobertura = null;
    let __chartVisitas = null;

    function buildCharts(){
      if(!window.Chart) return;

      const el1 = document.getElementById('chartCobertura');
      if(el1){
        if(__chartCobertura){ __chartCobertura.destroy(); }
        __chartCobertura = new Chart(el1, {
          type: 'doughnut',
          data: {
            labels: ['Cobertura Municipal', 'Pendiente Municipal', 'Cobertura Veredal', 'Pendiente Veredal'],
            datasets: [{
              data: [porcM, Math.max(0, 100-porcM), porcV, Math.max(0, 100-porcV)],
              backgroundColor: ['#2388ff', 'rgba(35,136,255,.12)', '#2cf5c4', 'rgba(44,245,196,.10)'],
              borderColor: ['#2388ff', 'rgba(35,136,255,.18)', '#2cf5c4', 'rgba(44,245,196,.16)'],
              hoverOffset: 5,
              borderWidth: 1,
            }]
          },
          options: {
            responsive: true,
            maintainAspectRatio: false,
            cutout: '68%',
            plugins: {
              legend: { position: 'bottom' },
              tooltip: {
                callbacks: {
                  label: (ctx) => {
                    const v = ctx.raw ?? 0;
                    return ` ${ctx.label}: ${Number(v).toFixed(0)}%`;
                  }
                }
              }
            }
          }
        });
      }

      const el2 = document.getElementById('chartVisitas');
      if(el2){
        if(__chartVisitas){ __chartVisitas.destroy(); }
        __chartVisitas = new Chart(el2, {
          type: 'line',
          data: {
            labels: meses,
            datasets: [{
              label: 'Visitas registradas',
              data: visitasSerie,
              tension: .38,
              fill: true,
              borderColor: '#25d9ff',
              backgroundColor: 'rgba(37,217,255,.10)',
              pointBackgroundColor: '#2cf5c4',
              pointBorderColor: '#071426',
              borderWidth: 2.5,
              pointRadius: 3,
              pointHoverRadius: 5,
            }]
          },
          options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
              legend: { position: 'bottom' },
              tooltip: {
                callbacks: {
                  label: (ctx) => ` ${ctx.dataset.label}: ${ctx.parsed.y ?? 0}`
                }
              }
            },
            scales: { y: { beginAtZero: true } }
          }
        });
      }
    }

    // ==========================
    // MODO TV: toggle + fullscreen + persist
    // ==========================
    const KEY_TV = 'gov_dashboard_tv_mode';

    function isFullscreen(){
      return !!(document.fullscreenElement || document.webkitFullscreenElement);
    }

    async function requestFS(){
      const el = document.documentElement;
      try{
        if(el.requestFullscreen) return await el.requestFullscreen();
        if(el.webkitRequestFullscreen) return await el.webkitRequestFullscreen();
      }catch(e){}
    }

    async function exitFS(){
      try{
        if(document.exitFullscreen) return await document.exitFullscreen();
        if(document.webkitExitFullscreen) return await document.webkitExitFullscreen();
      }catch(e){}
    }

    function setTVMode(on, autoFullscreen = false){
      document.body.classList.toggle('tv-mode', !!on);
      localStorage.setItem(KEY_TV, on ? '1' : '0');

      setTimeout(() => {
        try{
          if(__chartCobertura) __chartCobertura.resize();
          if(__chartVisitas) __chartVisitas.resize();
        }catch(e){}
      }, 200);

      if(on && autoFullscreen){
        requestFS();
      }
    }

    document.addEventListener('DOMContentLoaded', () => {
      buildCharts();

      const saved = localStorage.getItem(KEY_TV) === '1';
      if(saved){
        setTVMode(true, false);
        setTimeout(() => {
          try{
            if(__chartCobertura) __chartCobertura.resize();
            if(__chartVisitas) __chartVisitas.resize();
          }catch(e){}
        }, 350);
      }

      const btnTV = document.getElementById('btnTVMode');
      if(btnTV){
        btnTV.addEventListener('click', () => {
          const willOn = !document.body.classList.contains('tv-mode');
          setTVMode(willOn, true);
        });
      }

      const btnExit = document.getElementById('btnExitTV');
      if(btnExit){
        btnExit.addEventListener('click', async () => {
          setTVMode(false, false);
          if(isFullscreen()) await exitFS();
        });
      }

      const btnFS = document.getElementById('btnFullscreenTV');
      if(btnFS){
        btnFS.addEventListener('click', async () => {
          if(isFullscreen()) return exitFS();
          return requestFS();
        });
      }

      window.addEventListener('resize', () => {
        try{
          if(__chartCobertura) __chartCobertura.resize();
          if(__chartVisitas) __chartVisitas.resize();
        }catch(e){}
      });
    });
  </script>

  <!-- KPIs Proyectos 2025 (AJAX) -->
  <script>
  (function(){
    function fmtMoney(v){
      v = parseFloat(v) || 0;
      return '$ ' + Math.round(v).toLocaleString('es-CO');
    }
    function fmtPct(v){
      v = parseFloat(v) || 0;
      if(v > 0 && v <= 1) v = v * 100;
      return v.toFixed(1) + '%';
    }

    $.ajax({
      url: 'admin/ajax/rqst.php',
      type: 'POST',
      dataType: 'json',
      data: { op: 'getResumenProyectosRpc', vigencia: '2025' },
      timeout: 35000,
      success: function(res){
        if(!res || !res.output || !res.output.valid) return;
        var r = res.output.response;

        // KPIs principales
        $('#dash_kpi_total_proyectos').text(r.total_proyectos || 0);
        $('#dash_kpi_valor_ejecutado').text(fmtMoney(r.valor_ejecutado_total));
        $('#dash_kpi_valor_total').text(fmtMoney(r.valor_total));

        $('#dash_kpi_avance_fisico').text(fmtPct(r.promedio_avance_fisico));
        $('#dash_kpi_avance_finan_mini').text(fmtPct(r.promedio_avance_financiero));

        // Estados de proyectos
        var estados = r.estados || {};
        var total   = r.total_proyectos || 0;
        var keys    = Object.keys(estados);

        if(keys.length === 0){
          $('#dash_panel_estados').html(
            '<p style="color:var(--muted);font-size:13px;">Sin datos de estados.</p>'
          );
          return;
        }

        $('#dash_estados_total_lbl').text(total + ' proyectos · ' + keys.length + ' estados');

        var html = '';
        keys.forEach(function(nombre){
          var cnt = parseInt(estados[nombre]) || 0;
          var pct = total > 0 ? ((cnt / total) * 100).toFixed(1) : '0.0';

          // ✅ BIG + shimmer (solo UI)
          html +=
            '<div class="estado-row big">' +
              '<span class="estado-label big" title="' + nombre + '">' + nombre + '</span>' +
              '<div class="estado-bar-track big"><span class="estado-bar-fill big" style="width:' + pct + '%;"></span></div>' +
              '<span class="estado-count big">' + cnt + '</span>' +
              '<span class="estado-pct big">' + pct + '%</span>' +
            '</div>';
        });

        $('#dash_panel_estados').html(html);
      },
      error: function(){
        $('#dash_panel_estados').html(
          '<p style="color:var(--muted2);font-size:13px;">No se pudo conectar con la API de proyectos.</p>'
        );
        $('#dash_estados_total_lbl').text('Sin conexión');
      }
    });
  })();

  // =========================================
  // Inversión por Institución Beneficiada (Highcharts)
  // =========================================
  window.__instData = <?= json_encode(
      array_map(function ($r) {
          return [
              'name'  => (string)($r['institucion'] ?? ''),
              'y'     => (int)($r['total_registros'] ?? 0),
              'valor' => (float)($r['total_valor'] ?? 0),
          ];
      }, $dataInst),
      JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
  ) ?>;
  window.__instTotalValor = <?= (float)$valorGlobalInv ?>;

  function initChartInstituciones() {
    var chartId = 'chartInstituciones';
    if (typeof Highcharts === 'undefined' || !document.getElementById(chartId)) return;
    var rawData = window.__instData || [];
    if (!rawData.length) return;

    var normalizeText = function(text) {
      return String(text).trim().toUpperCase().replace(/\s+/g, ' ').normalize('NFD').replace(/[\u0300-\u036f]/g, '');
    };

    var colorMap = {
      'POLICIA DESAN': '#25d9ff', 'POLICIA MEBUC': '#2388ff', 'POLICIA DEMAM': '#4ca7ff',
      'EJERCITO NACIONAL': '#35e58b', 'ARMADA NACIONAL': '#2cf5c4', 'FISCALIA': '#a78bfa',
      'MIGRACION COLOMBIA': '#22c7e8', 'INPEC': '#ff8da0', 'UNP': '#f5b95b',
      'DEPARTAMENTO DE SANTANDER': '#ffd166', 'OTRO': '#7890aa', 'SIN INSTITUCION': '#54657a'
    };

    var finalData = rawData.map(function(p) {
      var nameKey = normalizeText(p.name);
      return { name: p.name, y: Number(p.y) || 0, valor: Number(p.valor) || 0, color: colorMap[nameKey] || '#58b8ff' };
    });

    Highcharts.chart(chartId, {
      chart: { type: 'pie', backgroundColor: 'transparent' },
      title: { text: null },
      credits: { enabled: false },
      legend: { enabled: false },
      plotOptions: {
        pie: {
          innerSize: '50%', colorByPoint: true, showInLegend: true,
          dataLabels: {
            enabled: true, format: '<b>{point.name}</b><br>{point.percentage:.1f}%',
            style: { color: '#ffffff', textOutline: 'none', fontSize: '12px' }
          }
        }
      },
      tooltip: {
        pointFormatter: function() {
          var valor = Number(this.valor) || 0;
          var v = '$' + valor.toLocaleString('es-CO');
          return '<b>' + this.name + '</b><br/>Registros: <b>' + this.y + '</b><br/>Inversión total: <b>' + v + '</b>';
        }
      },
      series: [{ name: 'Instituciones', data: finalData }]
    });
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initChartInstituciones);
  } else {
    initChartInstituciones();
  }

  // =========================================
  // Detalle de inversión por categoría (Infraestructura)
  // =========================================
  var chartCatLabels = <?= json_encode($chartCatLabels, JSON_UNESCAPED_UNICODE) ?>;
  var chartCatValues = <?= json_encode($chartCatValues, JSON_UNESCAPED_UNICODE) ?>;

  function formatCOP(v) {
    v = Number(v || 0);
    return '$' + v.toLocaleString('es-CO', { maximumFractionDigits: 0 });
  }

  if (typeof Highcharts !== 'undefined' && document.getElementById('chartCategoriasInfra') && chartCatLabels.length) {

    Highcharts.chart('chartCategoriasInfra', {
      chart: { type: 'bar', backgroundColor: 'transparent', borderRadius: 16, spacingLeft: 10, spacingRight: 25, spacingTop: 10, spacingBottom: 18, style: { fontFamily: 'Inter, Arial, sans-serif' } },
      title: { text: null },
      credits: { enabled: false },
      xAxis: {
        categories: chartCatLabels,
        labels: { style: { color: '#c8d7e8', fontSize: '12px', fontWeight: '700' } },
        lineColor: 'rgba(129,211,255,.14)',
        tickColor: 'rgba(129,211,255,.14)'
      },
      yAxis: {
        title: { text: null },
        labels: {
          style: { color: '#9fb0c8', fontSize: '12px', fontWeight: '700' },
          formatter: function() { return '$' + Highcharts.numberFormat(this.value / 1000000000, 0) + ' MM'; }
        },
        gridLineColor: 'rgba(129,211,255,.08)'
      },
      legend: { enabled: false },
      tooltip: {
        backgroundColor: 'rgba(5,15,29,.97)',
        borderColor: 'rgba(37,217,255,.28)',
        borderRadius: 12,
        style: { color: '#eef8ff', fontSize: '13px' },
        formatter: function() {
          return '<b style="font-size:14px;color:#ffffff;">' + this.x + '</b><br>Recurso: <b style="color:#2563eb;">' + formatCOP(this.y) + '</b>';
        }
      },
      plotOptions: {
        bar: {
          borderRadius: 6, borderWidth: 0, colorByPoint: true,
          dataLabels: {
            enabled: true, align: 'right', inside: false,
            style: { color: '#dce9f8', fontSize: '11px', fontWeight: '700', textOutline: 'none' },
            formatter: function() { return '$' + Highcharts.numberFormat(this.y / 1000000000, 0) + ' MM'; }
          }
        }
      },
      series: [{ name: 'Recurso', data: chartCatValues }]
    });
  }
  </script>
</body>
</html>