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
$secretaria = isset($_REQUEST['secretaria']) && $_REQUEST['secretaria'] !== ''
    ? (int)$_REQUEST['secretaria']
    : (int)Util::getSecretariaPrincipal();

$accion = isset($_REQUEST['accion']) && $_REQUEST['accion'] !== ''
    ? $_REQUEST['accion']
    : 'Capacitacion Fiscal y Financiera';

// Para la opción virtual "GOA - Aprehensiones" (unificada) se usa Licores como proxy
// en las consultas al backend, ya que el mapa y los totales individuales se obtienen
// siempre por tipo; la tarjeta unificada suma los 4 tipos directamente en PHP.
$accionParaConsulta = ($accion === 'GOA - Aprehensiones')
    ? 'GOA Aprehensiones de Licores'
    : $accion;

// Acciones por secretaría
$responseAccionSecretarias = [];
$isAccionSecretaria = false;
if (!empty($secretaria)) {
    $accionSecretaria = AccionSecretaria::getAll(['id' => $secretaria]);
    $isAccionSecretaria = $accionSecretaria['output']['valid'] ?? false;
    $responseAccionSecretarias = $accionSecretaria['output']['response'] ?? null;
} else {
    echo "<script>alert('Información enviada no es correcta'); window.location = 'dashboard.php';</script>";
    exit;
}

$userType = SessionData::getUserType();
$isSecretario = ($userType === Util::Secretario_Despacho() || $userType === Util::Auxiliar()|| $userType == Util::Auxiliar_secret_gob() ||$userType===Util::Secretaria_Despacho_Gobernacion());
$isAlcalde = ($userType === Util::Alcalde() || $userType === Util::Auxiliar_Alcalde());

// Se determina si el select debe estar deshabilitado
$isDisabled = '';
if ($isSecretario) {
    $isDisabled = 'disabled';
}

// Obtener listado de secretarías
$arr = Secretarias::getAll(null);
$isvalid = $arr['output']['valid'];
$arr = $arr['output']['response'];
$optionSecretarias = "";
foreach ($arr as $val) {
    $selected = ($val['id'] == $secretaria) ? "selected" : "";
    $optionSecretarias .= "<option value='" . $val['id'] . "' $selected>" . $val['secretaria'] . "</option>";
}

$secretariaParaConsulta = $secretaria;
/***************************************************************************************/
// Información del mapa y colores
/***************************************************************************************/
$arrMapa = [
    'codigoMunicipio' => Util::getDepartamentoPrincipal(),
    'secretariaId' => $secretariaParaConsulta,
    'accion' => $accionParaConsulta
];
$data = Colombia::getInformacionSecretariaColoresMapa($arrMapa);
$santander = $data['output']['response'];
$puntajes = $data['output']['puntajes'];

// Información del select de acciones
$selectLicores = "Operativos Contrabando licores";
$selectCigarrillos = "Operativos Contrabando cigarrillos";
$selectFiscalYFinanciera = "Capacitacion Fiscal y Financiera";
$selectCervezas = "Operativos Contrabando cerveza";

//GOA
$GOALicores= "GOA Aprehensiones de Licores";
$GOACigarrillos = "GOA Aprehensión de Cigarrillos";
$GOACervezas ="GOA Aprehensión de Cervezas";
$GOATabaco ="GOA Aprehensión de Tabaco y Otros";

// Visitas de establecimientos comerciales GOA
$registroVisitas = "Registro de Visitas a Establecimientos Comerciales";


// Información de los proyectos en ejecución
$arrEjecucion = [
    'codigoMunicipio' => Util::getDepartamentoPrincipal(),
    'secretariaId' => $secretariaParaConsulta,
    'accion' => $accionParaConsulta
];

/***************************************************************************************/
// Datos totales de ejecución por secretaría
/***************************************************************************************/
$responseTotalEjecucionSecretarias = Secretarias::getTotalEjecucionSecretaria($arrEjecucion);
$dataTotalEjecucionSecretarias = $responseTotalEjecucionSecretarias['output']['response'];

// Obtener lista de provincias para el select
$provinciasList = [];
$provinciaSeleccionada = $_REQUEST['provincia'] ?? 'Todos';
$provinciaDataSeleccionada = null;

if (!empty($dataTotalEjecucionSecretarias)) {
    foreach ($dataTotalEjecucionSecretarias as $provinciaData) {
        $nombreProvincia = htmlspecialchars(str_replace('_', ' ', $provinciaData['provincia']));
        $provinciasList[$nombreProvincia] = $provinciaData;
        
        if ($provinciaSeleccionada === $nombreProvincia) {
            $provinciaDataSeleccionada = $provinciaData;
        }
    }
}

// Solo ejecutamos la lógica de búsqueda si la provincia seleccionada es 'Todos'
if ($provinciaSeleccionada === 'Todos' && !empty($dataTotalEjecucionSecretarias)) {
    // Iteramos sobre el array para encontrar el elemento donde 'provincia' es 'Todos'
    foreach ($dataTotalEjecucionSecretarias as $data) {
        if ($data['provincia'] === 'Todos') {
            $provinciaDataSeleccionada = $data;
            break;
        }
    }
    // Si encontramos el elemento 'Todos', actualizamos la variable de la provincia
    if ($provinciaDataSeleccionada !== null) {
        $provinciaSeleccionada = htmlspecialchars(str_replace('_', ' ', $provinciaDataSeleccionada['provincia']));
    }
}

// Función auxiliar para calcular total de proyectos
function calcularTotalProyectos($provinciaData) {
    return ($provinciaData['suspendido'] ?? 0) + 
           ($provinciaData['terminado'] ?? 0) + 
           ($provinciaData['terminado_sin_liquidar'] ?? 0) + 
           ($provinciaData['terminado_liquidado'] ?? 0) + 
           ($provinciaData['ejecutado'] ?? 0) + 
           ($provinciaData['ejecucion'] ?? 0) + 
           ($provinciaData['en_contratacion'] ?? 0) + 
           ($provinciaData['en_formulacion'] ?? 0) + 
           ($provinciaData['entregado'] ?? 0) + 
           ($provinciaData['finalizado'] ?? 0) + 
           ($provinciaData['actualizacion'] ?? 0) + 
           ($provinciaData['radicado_banco'] ?? 0) + 
           ($provinciaData['aprobado_banco'] ?? 0) + 
           ($provinciaData['estudios_previos'] ?? 0) + 
           ($provinciaData['proceso_giro'] ?? 0) + 
           ($provinciaData['sin_iniciar'] ?? 0) + 
           ($provinciaData['pendiente_recursos'] ?? 0) + 
           ($provinciaData['liquidacion'] ?? 0) + 
           ($provinciaData['viabilizado_minsalud'] ?? 0) +
           ($provinciaData['desistio'] ?? 0) +
           ($provinciaData['radicacion'] ?? 0) +
           ($provinciaData['precontractual'] ?? 0) +
           ($provinciaData['otros_estados'] ?? 0);
}

// Calcular datos para la provincia seleccionada O para todas las provincias
if ($provinciaDataSeleccionada || $provinciaSeleccionada === 'Todos') {
    
    if ($provinciaSeleccionada === 'Todos') {
        // Calcular totales consolidados de todas las provincias
        $totalProyectosProvincia = 0;
        $totalTerminadosProvincia = 0;
        $totalEjecucionProvincia = 0;
        $totalContratacionProvincia = 0;
        $totalFormulacionProvincia = 0;
        $totalInversionProvincia = 0;
        
        // Variables para cada estado individual
        $totalSuspendido = 0;
        $totalTerminado = 0;
        $totalTerminadoSinLiquidar = 0;
        $totalTerminadoLiquidado = 0;
        $totalEjecutado = 0;
        $totalEjecucion = 0;
        $totalEnContratacion = 0;
        $totalEnFormulacion = 0;
        $totalEntregado = 0;
        $totalFinalizado = 0;
        $totalActualizacion = 0;
        $totalRadicadoBanco = 0;
        $totalAprobadoBanco = 0;
        $totalEstudiosPrevios = 0;
        $totalProcesoGiro = 0;
        $totalSinIniciar = 0;
        $totalPendienteRecursos = 0;
        $totalLiquidacion = 0;
        $totalViabilizadoMinsalud = 0;
        $totalDesistioConvenio = 0;
        $totalRadicacion = 0;
        $totalPrecontractual= 0;
        $totalOtrosEstados = 0;
        
        foreach ($dataTotalEjecucionSecretarias as $provinciaData) {
            // Saltar el registro 'Todos' para evitar duplicación
            if ($provinciaData['provincia'] === 'Todos') {
                continue;
            }
            
            // Sumar cada estado individualmente
            $totalSuspendido += $provinciaData['suspendido'] ?? 0;
            $totalTerminado += $provinciaData['terminado'] ?? 0;
            $totalTerminadoSinLiquidar += $provinciaData['terminado_sin_liquidar'] ?? 0;
            $totalTerminadoLiquidado += $provinciaData['terminado_liquidado'] ?? 0;
            $totalEjecutado += $provinciaData['ejecutado'] ?? 0;
            $totalEjecucion += $provinciaData['ejecucion'] ?? 0;
            $totalEnContratacion += $provinciaData['en_contratacion'] ?? 0;
            $totalEnFormulacion += $provinciaData['en_formulacion'] ?? 0;
            $totalEntregado += $provinciaData['entregado'] ?? 0;
            $totalFinalizado += $provinciaData['finalizado'] ?? 0;
            $totalActualizacion += $provinciaData['actualizacion'] ?? 0;
            $totalRadicadoBanco += $provinciaData['radicado_banco'] ?? 0;
            $totalAprobadoBanco += $provinciaData['aprobado_banco'] ?? 0;
            $totalEstudiosPrevios += $provinciaData['estudios_previos'] ?? 0;
            $totalProcesoGiro += $provinciaData['proceso_giro'] ?? 0;
            $totalSinIniciar += $provinciaData['sin_iniciar'] ?? 0;
            $totalPendienteRecursos += $provinciaData['pendiente_recursos'] ?? 0;
            $totalLiquidacion += $provinciaData['liquidacion'] ?? 0;
            $totalViabilizadoMinsalud += $provinciaData['viabilizado_minsalud'] ?? 0;
            $totalDesistioConvenio += $provinciaData['desistio'] ?? 0;
            $totalRadicacion += $provinciaData['radicacion'] ?? 0;
            $totalPrecontractual += $provinciaData['precontractual'] ?? 0;
            $totalOtrosEstados += $provinciaData['otros_estados'] ?? 0;
            
            // Sumar inversión
            $totalInversionProvincia += $provinciaData['valor_proyecto_total'] ?? 0;
        }
        
        // Calcular el total de proyectos sumando todos los estados individuales
        $totalProyectosProvincia = $totalSuspendido + $totalTerminado + $totalTerminadoSinLiquidar + 
                                   $totalTerminadoLiquidado + $totalEjecutado + $totalEjecucion + 
                                   $totalEnContratacion + $totalEnFormulacion + $totalEntregado + 
                                   $totalFinalizado + $totalActualizacion + $totalRadicadoBanco + 
                                   $totalAprobadoBanco + $totalEstudiosPrevios + $totalProcesoGiro + 
                                   $totalSinIniciar + $totalPendienteRecursos + $totalLiquidacion + 
                                   $totalViabilizadoMinsalud + $totalDesistioConvenio + $totalRadicacion + $totalPrecontractual + $totalOtrosEstados;
        
        // Calcular totales agrupados para las tarjetas
        $totalTerminadosProvincia = $totalTerminado + $totalTerminadoSinLiquidar + 
                                   $totalTerminadoLiquidado + $totalFinalizado + $totalEntregado;
        
        $totalEjecucionProvincia = $totalEjecutado + $totalEjecucion + $totalLiquidacion + 
                                  $totalProcesoGiro + $totalActualizacion + $totalViabilizadoMinsalud;
        
        $totalContratacionProvincia = $totalEnContratacion + $totalEstudiosPrevios;
        
        $totalFormulacionProvincia = $totalEnFormulacion + $totalRadicadoBanco + $totalAprobadoBanco;
        
        $nombreMostrar = "Todas las Provincias";
        
    } else {
        // Cálculo para provincia específica
        $totalProyectosProvincia = calcularTotalProyectos($provinciaDataSeleccionada);
        
        // Totales agrupados para las tarjetas
        $totalTerminadosProvincia = 
            ($provinciaDataSeleccionada['terminado'] ?? 0) + 
            ($provinciaDataSeleccionada['terminado_sin_liquidar'] ?? 0) + 
            ($provinciaDataSeleccionada['terminado_liquidado'] ?? 0) + 
            ($provinciaDataSeleccionada['finalizado'] ?? 0) + 
            ($provinciaDataSeleccionada['entregado'] ?? 0);
        
        $totalEjecucionProvincia = 
            ($provinciaDataSeleccionada['ejecutado'] ?? 0) + 
            ($provinciaDataSeleccionada['ejecucion'] ?? 0) + 
            ($provinciaDataSeleccionada['liquidacion'] ?? 0) + 
            ($provinciaDataSeleccionada['proceso_giro'] ?? 0) + 
            ($provinciaDataSeleccionada['actualizacion'] ?? 0) + 
            ($provinciaDataSeleccionada['viabilizado_minsalud'] ?? 0);

        $totalContratacionProvincia = 
            ($provinciaDataSeleccionada['en_contratacion'] ?? 0) + 
            ($provinciaDataSeleccionada['estudios_previos'] ?? 0);

        $totalFormulacionProvincia = 
            ($provinciaDataSeleccionada['en_formulacion'] ?? 0) + 
            ($provinciaDataSeleccionada['radicado_banco'] ?? 0) + 
            ($provinciaDataSeleccionada['aprobado_banco'] ?? 0);

        $totalInversionProvincia = $provinciaDataSeleccionada['valor_proyecto_total'] ?? 0;
        
        $nombreMostrar = $provinciaSeleccionada;
    }
    
    // Calcular porcentajes
    $porcentajeTerminadosProvincia = $totalProyectosProvincia > 0 ? round(($totalTerminadosProvincia / $totalProyectosProvincia) * 100, 1) : 0;
    $porcentajeEjecucionProvincia = $totalProyectosProvincia > 0 ? round(($totalEjecucionProvincia / $totalProyectosProvincia) * 100, 1) : 0;
    $porcentajeContratacionProvincia = $totalProyectosProvincia > 0 ? round(($totalContratacionProvincia / $totalProyectosProvincia) * 100, 1) : 0;
    $porcentajeFormulacionProvincia = $totalProyectosProvincia > 0 ? round(($totalFormulacionProvincia / $totalProyectosProvincia) * 100, 1) : 0;
}

// Variables adicionales para Hacienda
$infoCigarrillos = [];
$infoTabacos = [];
$infoLicores = [];
$infoCerveza = [];

$isHacienda = false;
if ($secretaria == Util::getSecretariaIdHacienda()) {
    $isHacienda = true;
    // Datos adicionales para Hacienda
    $haciendaDatos = $responseTotalEjecucionSecretarias['output']['response'];
    $estampillas = $responseTotalEjecucionSecretarias['output']['estampillas'];

    $infoCigarrillos = $responseTotalEjecucionSecretarias['output']['cigarrillos'];
    $infoTabacos = $responseTotalEjecucionSecretarias['output']['tabaco'];
    $infoLicores = $responseTotalEjecucionSecretarias['output']['licores'];
    $infoCerveza = $responseTotalEjecucionSecretarias['output']['cerveza'];

    //GOA
    $GOALicores_cantidad_aprehendida = $responseTotalEjecucionSecretarias['output']['GOALicores'][0]['cantidad_aprehendida'] ?? 0;
    $GOALicores_avaluo_comercial = $responseTotalEjecucionSecretarias['output']['GOALicores'][0]['avaluo_comercial'] ?? 0;

    $GOACigarrillos_cantidad_aprehendida = $responseTotalEjecucionSecretarias['output']['GOACigarrillos'][0]['cantidad_aprehendida'] ?? 0;
    $GOACigarrillos_avaluo_comercial = $responseTotalEjecucionSecretarias['output']['GOACigarrillos'][0]['avaluo_comercial'] ?? 0;

    $GOACervezas_cantidad_aprehendida = $responseTotalEjecucionSecretarias['output']['GOACervezas'][0]['cantidad_aprehendida'] ?? 0;
    $GOACervezas_avaluo_comercial = $responseTotalEjecucionSecretarias['output']['GOACervezas'][0]['avaluo_comercial'] ?? 0;

    $GOATabaco_cantidad_aprehendida = $responseTotalEjecucionSecretarias['output']['GOATabaco'][0]['cantidad_aprehendida'] ?? 0;
    $GOATabaco_avaluo_comercial = $responseTotalEjecucionSecretarias['output']['GOATabaco'][0]['avaluo_comercial'] ?? 0;

    // Totales unificados GOA – Aprehensiones (suma de los 4 tipos)
    $GOATotal_cantidad_aprehendida = $GOALicores_cantidad_aprehendida + $GOACigarrillos_cantidad_aprehendida + $GOACervezas_cantidad_aprehendida + $GOATabaco_cantidad_aprehendida;
    $GOATotal_avaluo_comercial     = $GOALicores_avaluo_comercial + $GOACigarrillos_avaluo_comercial + $GOACervezas_avaluo_comercial + $GOATabaco_avaluo_comercial;

    // Registro de visitas a establecimientos comerciales
    $GOAcantidad_visitas_al_municipio = $responseTotalEjecucionSecretarias['output']['registroVisitas'][0]['cantidad_visitas_al_municipio'] ?? 0;

    // GOA Jurídico
    $GOAJuridico_custodia_valor_total          = $responseTotalEjecucionSecretarias['output']['GOAJuridico'][0]['goa_juridico_custodia_valor_total']          ?? 0;
    $GOAJuridico_custodia_cantidad_procesos    = $responseTotalEjecucionSecretarias['output']['GOAJuridico'][0]['goa_juridico_custodia_cantidad_procesos']    ?? 0;
    $GOAJuridico_custodia_cantidad_unidades    = $responseTotalEjecucionSecretarias['output']['GOAJuridico'][0]['goa_juridico_custodia_cantidad_unidades']    ?? 0;
    $GOAJuridico_destruccion_cantidad_unidades = $responseTotalEjecucionSecretarias['output']['GOAJuridico'][0]['goa_juridico_destruccion_cantidad_unidades'] ?? 0;
    $GOAJuridico_destruccion_valor_total       = $responseTotalEjecucionSecretarias['output']['GOAJuridico'][0]['goa_juridico_destruccion_valor_total']       ?? 0;

    $datos = $haciendaDatos[0] ?? [];

    $totalUnidades = $datos['TOTAL_UNIDADES'] ?? 0;
    $TOTAL_RECAUDO_CIG_CERV_LIC_TABAC = $datos['TOTAL_RECAUDO_CIG_CERV_LIC_TABAC'] ?? 0;

    // Impuesto Vehicular Recaudado
    $vehicular_total_recaudo                   = $datos['TOTAL_RECAUDO_IMPUESTO_VEHICULAR']              ?? 0;
    $vehicular_total_tramites                  = $datos['TOTAL_TRAMITES_IMPUESTO_VEHICULAR']             ?? 0;
    $vehicular_total_recaudo_y_tramite         = $datos['IMPUESTO_VEHICULAR_TOTAL_RECAUDO_Y_TRAMITE']   ?? 0;
    $vehicular_total_operativos                = $datos['TOTAL_VEHICULAR_OPERATIVOS']                    ?? 0;
    $vehicular_total_emplazados                = $datos['TOTAL_VEHICULAR_EMPLAZADOS']                    ?? 0;
    $vehicular_total_placas_consultadas        = $datos['TOTAL_VEHICULAR_PLACAS_CONSULTADAS']            ?? 0;
    $vehicular_total_campanas_sensibilizacion  = $datos['TOTAL_VEHICULAR_CAMPANAS_SENSIBILIZACION']      ?? 0;
}


// Definir las clases dinámicamente
$classMapaInterativo = $isHacienda ? 'col-md-6' : 'col-md-6';
$displayInformacionDiferente = $isHacienda ? 'style="display: none;"' : '';
?>

<!-- jQuery -->
<script src="https://code.jquery.com/jquery-3.7.1.js"></script>
<script src="/plugins/datatables/jquery.dataTables.min.js"></script>

<!-- Popper.js -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.3/umd/popper.min.js"></script>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">

<style>
  :root{
    --nav-blue: #20427F;
    --nav-blue-2:#132b52;
    --nav-blue-3:#2e58a8;

    --ink:#0f172a;
    --muted:#64748b;
    --line:rgba(2,6,23,.10);
    --bg:#f7f9fc;

    --radius-xl:18px;
    --radius-lg:14px;
    --radius-md:12px;

    --shadow-soft:0 10px 28px rgba(2,6,23,.10);
    --shadow-mid:0 14px 38px rgba(2,6,23,.14);

    --ok:#16a34a;
    --warn:#f59e0b;
    --bad:#dc2626;
    --info:#0ea5e9;
  }

  body{ background: var(--bg) !important; }

  /* ===== CARDS PREMIUM ===== */
  .card{
    border-radius: var(--radius-xl) !important;
    border: 1px solid var(--line) !important;
    box-shadow: var(--shadow-soft) !important;
    overflow: hidden;
    transition: transform .18s ease, box-shadow .18s ease, border-color .18s ease;
    background: #fff;
  }
  .card:hover{
    transform: translateY(-1px);
    box-shadow: var(--shadow-mid) !important;
    border-color: rgba(32,66,127,.22) !important;
  }

  /* Header general (tus card-header) */
  .card-header{
    background: linear-gradient(135deg, rgba(32,66,127,.10), rgba(19,43,82,.06)) !important;
    border-bottom: 1px solid rgba(2,6,23,.10) !important;
    font-weight: 900;
  }
  .card-header h5, .card-header h6{
    font-weight: 900 !important;
    margin: 0;
    color: var(--ink);
    letter-spacing: .2px;
  }

  /* Breadcrumb title */
  .page-header .m-b-10{ font-weight: 900; letter-spacing: .2px; }

  /* ===== INPUTS / SELECTS PRO ===== */
  .form-control{
    border-radius: var(--radius-md) !important;
    border: 1px solid rgba(15,23,42,.16) !important;
    box-shadow: 0 6px 16px rgba(2,6,23,.04);
    font-weight: 700 !important;
    color: var(--ink) !important;
    min-height: 42px;
    transition: transform .14s ease, box-shadow .14s ease, border-color .14s ease;
  }
  .form-control:focus{
    border-color: rgba(32,66,127,.55) !important;
    box-shadow: 0 0 0 .2rem rgba(32,66,127,.18) !important;
    transform: translateY(-1px);
  }
  label.form-label, .form-label{
    font-weight: 900 !important;
    color: #334155 !important;
  }

  /* ===== BOTONES PRO (sin romper bootstrap) ===== */
  .btn{
    border-radius: 14px !important;
    font-weight: 900 !important;
    letter-spacing: .2px;
    transition: transform .16s ease, filter .16s ease, box-shadow .16s ease;
  }
  .btn:hover{ transform: translateY(-1px); filter: brightness(1.02); }
  .btn:active{ transform: translateY(0px); }

  .btn-primary{
    background: linear-gradient(135deg, var(--nav-blue), var(--nav-blue-2)) !important;
    border: none !important;
    box-shadow: 0 14px 34px rgba(2,6,23,.16);
  }
  .btn-success{
    background: linear-gradient(135deg, #16a34a, #0f766e) !important;
    border: none !important;
    box-shadow: 0 14px 34px rgba(2,6,23,.16);
  }
  .btn-secondary{
    background: linear-gradient(135deg, #64748b, #334155) !important;
    border: none !important;
    box-shadow: 0 14px 34px rgba(2,6,23,.14);
  }

  /* ===== BADGES / COLORES ===== */
  .bg-rojo{ background:#DC143C !important; }
  .bg-amarillo{ background:#f1c40f !important; color:#111827 !important; }
  .bg-verde{ background:#2ecc71 !important; }
  .bg-azul{ background:#3498db !important; }
  .bg-gris{ background:#898989 !important; }
  .bg-naranja{ background:#e67e22 !important; }

  .badge{
    border-radius: 999px !important;
    font-weight: 900 !important;
    padding: .38rem .70rem !important;
    border: 1px solid rgba(15,23,42,.12);
    letter-spacing: .2px;
  }

  /* ===== SECCIÓN RESUMEN / SCROLL ===== */
  .resumen-container{
    max-height: 72vh;
    overflow-y: auto;
    padding-right: 2px;
  }
  .resumen-container::-webkit-scrollbar{ width: 6px; }
  .resumen-container::-webkit-scrollbar-track{ background: #eef2f7; border-radius: 10px; }
  .resumen-container::-webkit-scrollbar-thumb{
    background: rgba(32,66,127,.45);
    border-radius: 10px;
  }
  .resumen-container::-webkit-scrollbar-thumb:hover{
    background: rgba(32,66,127,.70);
  }

  .valor-grande{ font-size: 1.6rem; font-weight: 1000; }
  .valor-mediano{ font-size: 1.25rem; font-weight: 1000; }
  .valor-pequeno{ font-size: 1.0rem; font-weight: 1000; }

  /* ===== PANEL IZQUIERDO: ITEMS CLICK ESTADO ===== */
  .modal-trigger-estado{
    border: 1px solid rgba(15,23,42,.10) !important;
    border-radius: 14px !important;
    background: linear-gradient(180deg, #fff, #fbfdff) !important;
    box-shadow: 0 10px 24px rgba(2,6,23,.08);
    transition: transform .16s ease, box-shadow .16s ease, border-color .16s ease;
  }
  .modal-trigger-estado:hover{
    transform: translateY(-2px);
    border-color: rgba(32,66,127,.30) !important;
    box-shadow: 0 16px 34px rgba(2,6,23,.12);
  }

  /* ===== MAPA: contenedor más limpio ===== */
  #contenido-mapa{
    border-radius: var(--radius-xl);
    overflow: hidden;
  }

  /* ===== MODALES PRO ===== */
  .modal-content{
    border-radius: 18px !important;
    overflow: hidden;
    border: 1px solid rgba(15,23,42,.12) !important;
    box-shadow: var(--shadow-mid) !important;
  }
  .modal-header{
    background: linear-gradient(135deg, var(--nav-blue), var(--nav-blue-2)) !important;
    color:#fff !important;
    border-bottom: 1px solid rgba(255,255,255,.12) !important;
  }
  .modal-header .modal-title{
    font-weight: 1000 !important;
    letter-spacing: .2px;
  }
  .modal-header .close span{ color:#fff !important; opacity:.9; }

  /* Modal body: look premium */
  .modal-body{
    background: #fff !important;
  }

  /* ===== RESPONSIVE ===== */
  @media (max-width: 992px){
    .resumen-container{ max-height: none; }
  }
  @media (max-width: 576px){
    .btn{ width:100%; }
    .card-header{ text-align:center; }
    #botonGeocalizacion{ width:100%; }
  }
</style>


<body class="">
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
                            <!-- [ breadcrumb ] start -->
                            <div class="page-header">
                                <div class="page-block">
                                    <div class="row align-items-center">
                                        <div class="col-md-12">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <h5 class="m-b-10">Informes Secretarias</h5>
                                                <?php include './admin/include/btn_back.php'; ?>
                                            </div>
                                            <ul class="breadcrumb">
                                                <li class="breadcrumb-item"><a href="index.html"><i class="feather icon-home"></i></a></li>
                                                <li class="breadcrumb-item"><a href="#!">Informe Secretaria</a></li>
                                                <li class="breadcrumb-item"><a href="#!">Actividades</a></li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="card">
                                <div class="card-header d-flex flex-wrap align-items-center justify-content-between py-3">
                                    <h5 class="mb-0 text-center w-100">Informes Secretarias</h5>
                                    <?php
                                    $userTypeS = SessionData::getUserType();
                                    $secretariaSessionId = SessionData::getSecretaria();
                                    $isAdminS = ($userTypeS === Util::Administrador() || $userTypeS === Util::SuperAdministrador() || $userTypeS === Util::Gobernador());
                                    $esDeInterior = ($secretariaSessionId == Util::getSecretariaIdInterior());
                                    if ($secretaria == Util::getSecretariaIdInterior() && ($isAdminS || $esDeInterior)): ?>
                                    <div class="ml-auto mr-2">
                                        <a href="admin/ajax/dash_interior_pdf.php" target="_blank"
                                           class="btn btn-sm"
                                           style="background:rgba(255,122,0,.15);border:1px solid #ff7a00;color:#ff7a00;font-size:12px;"
                                           title="Descargar Boletín de Seguridad y Convivencia Ciudadana PDF">
                                            <i class="feather icon-file-text mr-1"></i> Boletín de Seguridad y Convivencia Ciudadana
                                        </a>
                                    </div>
                                    <?php endif; ?>
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
                                    <div class="row" style="justify-content: space-evenly;">
                                         <!-- Lado Izquierdo -->
                                        <div id="divInformacionDiferenteDeHacienda" class="col-md-3" <?= $displayInformacionDiferente ?>>
                                            <!-- Resumen Información -->
                                            <?php if ( ($provinciaDataSeleccionada || $provinciaSeleccionada === 'Todos') && !$isHacienda): ?>
                                            <div class="card mb-3">
                                                <div class="card-header text-white p-2 text-center">
                                                    <h6 class="mb-0">
                                                        <i class="bi bi-info-circle-fill me-2"></i>
                                                        Resumen Información - <?= $nombreMostrar ?? $provinciaSeleccionada ?>
                                                    </h6>
                                                </div>
                                                <div class="card-body p-2 resumen-container">
                                                    <!-- Valores Financieros -->
                                                    <div class="mb-3">
                                                        <h6 class="text-center mb-3" style="color: #414d58;">
                                                            <i class="bi bi-currency-dollar me-2"></i>
                                                            Valores Financieros
                                                        </h6>
                                                        
                                                        <div class="mb-2 p-2 border rounded bg-light">
                                                            <div class="d-flex justify-content-between align-items-center">
                                                                <label class="fw-bold mb-0">Valor Proyecto Total:</label>
                                                                <strong>$<?= Util::formatBigMoneyString($totalInversionProvincia) ?></strong>
                                                            </div>
                                                        </div>

                                                        <?php if ($provinciaSeleccionada !== 'Todos'): ?>

                                                        <div class="mb-2 p-2 border rounded bg-light">
                                                            <div class="d-flex justify-content-between align-items-center">
                                                                <label class="fw-bold mb-0">Aporte Municipio:</label>
                                                                <strong>$<?= number_format($provinciaDataSeleccionada['valor_municipio_total'], 0, ',', '.') ?></strong>
                                                            </div>
                                                        </div>

                                                        <div class="mb-2 p-2 border rounded bg-light">
                                                            <div class="d-flex justify-content-between align-items-center">
                                                                <label class="fw-bold mb-0">Aporte Nación:</label>
                                                                <strong>$<?= number_format($provinciaDataSeleccionada['valor_nacion_total'], 0, ',', '.') ?></strong>
                                                            </div>
                                                        </div>

                                                        <div class="mb-3 p-2 border rounded bg-light">
                                                            <div class="d-flex justify-content-between align-items-center">
                                                                <label class="fw-bold mb-0">Aporte Departamento:</label>
                                                                <strong>$<?= number_format($provinciaDataSeleccionada['valor_departamento_total'], 0, ',', '.') ?></strong>
                                                            </div>
                                                        </div>

                                                        <?php endif; ?>

                                                    </div>

                                                    <!-- Estados de los Proyectos -->
                                                    <div>
                                                        <h6 class="text-center mb-3" style="color: #414d58;">
                                                            <i class="bi bi-list-check me-2"></i>
                                                            Estados de Proyectos
                                                        </h6>
                                                        
                                                        <?php
                                                        $estadosFijos = [
                                                            ['label' => 'En Formulación', 'icon' => 'bi-pencil-square', 'color' => 'danger', 'key' => 'en_formulacion'],
                                                            ['label' => 'En Contratación', 'icon' => 'bi-clipboard-data', 'color' => 'info', 'key' => 'en_contratacion'],
                                                            ['label' => 'En Ejecución', 'icon' => 'bi-play-circle', 'color' => 'primary', 'key' => 'ejecucion'],
                                                            ['label' => 'Ejecutado', 'icon' => 'bi-check2-all', 'color' => 'success', 'key' => 'ejecutado'],
                                                            ['label' => 'Terminado - NO liquidado', 'icon' => 'bi-check2-circle', 'color' => 'warning', 'key' => 'terminado_sin_liquidar'],
                                                            ['label' => 'Terminado - Liquidado', 'icon' => 'bi-check2-circle', 'color' => 'success', 'key' => 'terminado_liquidado'],
                                                            ['label' => 'Terminado', 'icon' => 'bi-flag-fill', 'color' => 'success', 'key' => 'terminado'],
                                                            ['label' => 'Suspendido', 'icon' => 'bi-pause-circle', 'color' => 'warning', 'key' => 'suspendido'],
                                                            ['label' => 'Entregado', 'icon' => 'bi-box-arrow-in-down', 'color' => 'danger', 'key' => 'entregado'],
                                                            ['label' => 'Pendiente Recursos', 'icon' => 'bi-wallet2', 'color' => 'warning', 'key' => 'pendiente_recursos'],
                                                            ['label' => 'En proceso de Giro', 'icon' => 'bi-arrow-repeat', 'color' => 'info', 'key' => 'proceso_giro'],
                                                            ['label' => 'Sin Iniciar', 'icon' => 'bi-file-earmark-text', 'color' => 'danger', 'key' => 'sin_iniciar'],
                                                            ['label' => 'Proyectos viabilizados en min salud', 'icon' => 'bi-clipboard-check', 'color' => 'info', 'key' => 'viabilizado_minsalud'],
                                                            ['label' => 'Actualización', 'icon' => 'bi-arrow-clockwise', 'color' => 'danger', 'key' => 'actualizacion'],
                                                            ['label' => 'Desistió del convenio', 'icon' => 'bi-x-circle', 'color' => 'danger', 'key' => 'desistio'], 
                                                            ['label' => 'En liquidacion', 'icon' => 'bi-x-circle', 'color' => 'danger', 'key' => 'liquidacion'], 
                                                            ['label' => 'Radicación', 'icon' => 'bi-x-circle', 'color' => 'danger', 'key' => 'radicacion'], 
                                                            ['label' => 'Proceso Precontractual', 'icon' => 'bi-x-circle', 'color' => 'danger', 'key' => 'precontractual'], 
                                                        ];

                                                        // Filtrar solo los estados que tienen valores mayores a 0
                                                        $estadosConDatos = [];
                                                        foreach ($estadosFijos as $estado) {
                                                            $key = $estado['key'];
                                                            $valor = isset($provinciaDataSeleccionada[$key]) ? intval($provinciaDataSeleccionada[$key]) : 0;
                                                            if ($valor > 0) {
                                                                $estado['value'] = $valor;
                                                                $estadosConDatos[] = $estado;
                                                            }
                                                        }
                                                        ?>

                                                        <?php if (!empty($estadosConDatos)): ?>
                                                            <div class="row">
                                                                <?php foreach ($estadosConDatos as $index => $estado): ?>
                                                                    <div class="col-md-6 mb-2">
                                                                        <div class="p-2 border rounded text-center bg-light modal-trigger-estado"
                                                                        data-toggle="modal"
                                                                        data-target="#modalListaProyectos"
                                                                        data-estado="<?= htmlspecialchars($estado['label']) ?>"
                                                                        data-secretaria="<?= htmlspecialchars($secretaria) ?>"
                                                                        data-provincia="<?= htmlspecialchars($provinciaSeleccionada) ?>"
                                                                        style="cursor: pointer;">
                                                                            <div class="d-flex align-items-center justify-content-center mb-1">
                                                                                <i class="bi <?= $estado['icon'] ?> text-<?= $estado['color'] ?> me-2"></i>
                                                                                <label class="fw-bold text-dark"><?= $estado['label'] ?>
                                                                            </div>
                                                                            <div class="mt-1" style="font-size: 13px !important;">
                                                                                <span class="text-black badge bg-<?= $estado['color'] ?> fs-6 px-3 py-2">
                                                                                    <?= $estado['value'] ?>
                                                                                </span>
                                                                            </div>
                                                                        </div>
                                                                        </div>
                                                                    
                                                                        <?php if (($index + 1) % 2 == 0): ?>
                                                                        <div class="w-100"></div>
                                                                    <?php endif; ?>
                                                                <?php endforeach; ?>
                                                            </div>
                                                        <?php else: ?>
                                                            <div class="text-center p-3">
                                                                <p class="text-muted mb-0">
                                                                    <i class="bi bi-info-circle me-2"></i>
                                                                    No hay proyectos registrados en esta secretaría
                                                                </p>
                                                            </div>
                                                        <?php endif; ?>

                                                    </div>
                                                </div>
                                            </div>
                                            <?php else: ?>
                                            <?php if (!$isHacienda): ?>
                                            <div class="card mb-3">
                                                <div class="card-body text-center">
                                                    <p class="text-muted mb-0">Seleccione una provincia para ver el resumen</p>
                                                </div>
                                            </div>
                                            <?php endif; ?>
                                            <?php endif; ?>
                                        </div>

                                        <!-- MAPA CENTRO - Cambia de col-md-6 a col-md-9 cuando es Hacienda -->
                                        <div id="divMapaInterativo" class="<?= $classMapaInterativo ?>">
                                            <div class="card">
                                                <div class="card-header d-flex justify-content-center align-items-center gap-3 flex-wrap text-center">
                                                    <h5 class="mb-0 fw-bold">Mapa de Actividades - <?= $nombreMostrar ?? $provinciaSeleccionada ?></h5> <!-- Actualizado aquí -->
                                                    <button id="botonGeocalizacion" name="botonGeocalizacion" type="button"
                                                        class="btn btn-primary px-4 py-2 fs-6 fw-semibold" data-toggle="modal"
                                                        data-target="#modalGeocalizacion">
                                                        <i class="bi bi-geo-alt-fill me-1"></i> Geolocalización
                                                    </button>
                                                </div>
                                                <div class="card-body text-center">
                                                    <div id="contenido-mapa" class="cuerpoMapa w-100">
                                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="40 40 1000 1200" width="100%" height="auto">
                                                            <?php foreach ($santander as $key => $value): ?>
                                                                <g id="<?= strtoupper($value['path']) ?>">
                                                                    <path id="<?= strtoupper($value['path']) ?>"
                                                                        d="<?= $value['d'] ?>" fill="<?= $value['color'] ?>"
                                                                        class="municipios mapaClick <?= getClasePorcentaje(0.2) ?>"
                                                                        data-base-url="<?= getUrl() . 'municipios_secretaria_informacion.php?mun=' . $value['codigo_muncipio'] . '&dep=' . $value['codigo_departamento'] ?>"
                                                                        data-url="<?= getUrl() . 'municipios_secretaria_informacion.php?mun=' . $value['codigo_muncipio'] . '&dep=' . $value['codigo_departamento'] ?>"
                                                                        data-name="<?= strtolower($value['municipio']) ?>"
                                                                        title="<?= strtoupper(str_replace("-", " ", $value['nombre_mapa'])) ?>"
                                                                        stroke="#000" stroke-miterlimit="10" stroke-width="0.3px">
                                                                    </path>
                                                                </g>
                                                            <?php endforeach; ?>
                                                            <?php require_once 'nombres_mapa_santander.php' ?>
                                                        </svg>
                                                    </div>
                                                </div>
                                            </div>
                                            <?php
                                            $accionesGOAAprehLeyenda = ['GOA Aprehensiones de Licores','GOA Aprehensión de Cigarrillos','GOA Aprehensión de Cervezas','GOA Aprehensión de Tabaco y Otros','GOA - Aprehensiones'];
                                            $esLeyendaAprehensiones  = in_array($accion, $accionesGOAAprehLeyenda);
                                            $esLeyendaVisitas        = ($accion === 'Registro de Visitas a Establecimientos Comerciales');
                                            if ($secretaria == Util::getSecretariaIdHacienda() && ($esLeyendaAprehensiones || $esLeyendaVisitas)):
                                            ?>
                                            <div class="card mt-2">
                                                <div class="card-body py-2 px-3">
                                                    <p class="fw-bold mb-2" style="font-size:0.78rem;">
                                                        <?= $esLeyendaVisitas ? 'Escala – Visitas a Establecimientos' : 'Escala – Aprehensiones GOA' ?>
                                                    </p>
                                                    <table class="table table-sm table-bordered mb-0" style="font-size:0.75rem;">
                                                        <thead><tr><th>Color</th><th>Rango</th></tr></thead>
                                                        <tbody>
                                                            <tr>
                                                                <td><span style="display:inline-block;width:18px;height:18px;background:#EEF2F7;border:1px solid #ccc;border-radius:3px;"></span></td>
                                                                <td>Sin datos (0)</td>
                                                            </tr>
                                                            <tr>
                                                                <td><span style="display:inline-block;width:18px;height:18px;background:#E53935;border-radius:3px;"></span></td>
                                                                <td><?= $esLeyendaVisitas ? '1 – 28' : '1 – 2' ?></td>
                                                            </tr>
                                                            <tr>
                                                                <td><span style="display:inline-block;width:18px;height:18px;background:#FB8C00;border-radius:3px;"></span></td>
                                                                <td><?= $esLeyendaVisitas ? '29 – 56' : '3 – 4' ?></td>
                                                            </tr>
                                                            <tr>
                                                                <td><span style="display:inline-block;width:18px;height:18px;background:#1E66F5;border-radius:3px;"></span></td>
                                                                <td><?= $esLeyendaVisitas ? '57 – 84' : '5 – 6' ?></td>
                                                            </tr>
                                                            <tr>
                                                                <td><span style="display:inline-block;width:18px;height:18px;background:#2E7D32;border-radius:3px;"></span></td>
                                                                <td><?= $esLeyendaVisitas ? '85 o más' : '7 o más' ?></td>
                                                            </tr>
                                                        </tbody>
                                                    </table>
                                                </div>
                                            </div>
                                            <?php endif; ?>
                                        </div>

                                        <!-- Lado Derecho - Indicadores de la Provincia -->
                                        <div id="divInformacionSelect" class="<?= $isHacienda ? 'col-md-4' : 'col-md-3' ?>">
                                            <!-- Selector de Secretaría -->
                                            <div class="card mb-3">
                                                <div class="card-body py-2 px-3">
                                                    <div class="row align-items-center">

                                                       <?php if ($secretaria == Util::getSecretariaIdHacienda()): ?>
                                                        <div class="col-md-12 col-xs-12">
                                                            <div class="d-grid gap-2">
                                                                <a href="secretaria_exportar_excel.php?secretariaId=<?= $secretaria ?>&accion=<?= urlencode($accionParaConsulta) ?>"class="btn btn-success btn-block" role="button" style="Border-radius: 10px;" >
                                                                    <i class="bi bi-file-earmark-excel-fill me-2"></i> Exportar Datos a Excel
                                                                </a>
                                                        </div>
                                                        <?php endif; ?>

                                                        <div class="col-md-12 col-xs-12">
                                                            <label for="secretariaId" class="form-label fw-bold mb-1">
                                                                Seleccionar Secretaría
                                                            </label>
                                                            <select <?php echo $isDisabled; ?> class="form-control" id="secretariaId" name="secretariaId" onchange="updateUrlSecretaria(this)">
                                                                <?php echo $optionSecretarias; ?>
                                                            </select>
                                                        </div>

                                            <?php
                                                // Determinar el grupo activo según la acción actual
                                                $accionesGOA         = ['GOA Aprehensiones de Licores', 'GOA Aprehensión de Cigarrillos', 'GOA Aprehensión de Cervezas', 'GOA Aprehensión de Tabaco y Otros', 'GOA - Aprehensiones', 'GOA Juridico', 'Registro de Visitas a Establecimientos Comerciales'];
                                                $accionesIngresos    = ['Capacitacion Fiscal y Financiera'];
                                                $accionesTesoreria   = ['Recaudo del impuesto al consumo', 'Recaudo del impuesto de registro', 'Impuesto Estampillas Recaudado'];
                                                $accionesAutomotores = ['Impuesto Vehicular Recaudado'];

                                                if (in_array($accion, $accionesGOA)) {
                                                    $grupoActivo = 'GRUPO OPERATIVO ANTICONTRABANDO';
                                                } elseif (in_array($accion, $accionesIngresos)) {
                                                    $grupoActivo = 'DIRECCION DE INGRESOS';
                                                } elseif (in_array($accion, $accionesTesoreria)) {
                                                    $grupoActivo = 'TESORIA';
                                                } elseif (in_array($accion, $accionesAutomotores)) {
                                                    $grupoActivo = 'IMPUESTO UNIFICADO DE AUTOMOTORES';
                                                } else {
                                                    $grupoActivo = '';
                                                }
                                            ?>
                                            <?php if ($secretaria == Util::getSecretariaIdHacienda()): ?>
                                                <!-- Selector Nivel 1: Grupo de Hacienda -->
                                                <div class="card-body py-2 px-3">
                                                    <div class="row align-items-center">
                                                        <div class="col-md-12 col-xs-12 mb-2">
                                                            <label for="grupoHacienda" class="form-label fw-bold mb-1">
                                                                Área / Grupo
                                                            </label>
                                                            <select class="form-control" id="grupoHacienda" name="grupoHacienda" onchange="cambiarGrupoHacienda(this.value)">
                                                                <option value="" <?= $grupoActivo == '' ? 'selected' : '' ?>>-- Seleccionar área --</option>
                                                                <option value="GRUPO OPERATIVO ANTICONTRABANDO" <?= $grupoActivo == 'GRUPO OPERATIVO ANTICONTRABANDO' ? 'selected' : '' ?>>
                                                                    Grupo Operativo Anticontrabando
                                                                </option>
                                                                <option value="DIRECCION DE INGRESOS" <?= $grupoActivo == 'DIRECCION DE INGRESOS' ? 'selected' : '' ?>>
                                                                    Dirección de Ingresos
                                                                </option>
                                                                <option value="TESORIA" <?= $grupoActivo == 'TESORIA' ? 'selected' : '' ?>>
                                                                    Tesorería
                                                                </option>
                                                                <option value="IMPUESTO UNIFICADO DE AUTOMOTORES" <?= $grupoActivo == 'IMPUESTO UNIFICADO DE AUTOMOTORES' ? 'selected' : '' ?>>
                                                                    Impuesto Unificado de Automotores
                                                                </option>
                                                            </select>
                                                        </div>

                                                        <!-- Selector Nivel 2: Sub-acción (GOA) -->
                                                        <div class="col-md-12 col-xs-12" id="wrapperSubAccionGOA" style="<?= $grupoActivo == 'GRUPO OPERATIVO ANTICONTRABANDO' ? '' : 'display:none;' ?>">
                                                            <label for="accionHacienda" class="form-label fw-bold mb-1">
                                                                Tipo de Acción
                                                            </label>
                                                            <select onchange="updateUrlAccionHacienda(this)" class="form-control" id="accionHacienda" name="accionHacienda">
                                                                <option value="GOA - Aprehensiones" style="display:none;" <?= $accion == 'GOA - Aprehensiones' ? 'selected' : '' ?>>
                                                                    GOA – Aprehensiones (Todas)
                                                                </option>
                                                                <option value="GOA Aprehensiones de Licores" <?= $accion == 'GOA Aprehensiones de Licores' ? 'selected' : '' ?>>
                                                                    GOA – Aprehensiones de Licores
                                                                </option>
                                                                <option value="GOA Aprehensión de Cigarrillos" <?= $accion == 'GOA Aprehensión de Cigarrillos' ? 'selected' : '' ?>>
                                                                    GOA – Aprehensión de Cigarrillos
                                                                </option>
                                                                <option value="GOA Aprehensión de Cervezas" <?= $accion == 'GOA Aprehensión de Cervezas' ? 'selected' : '' ?>>
                                                                    GOA – Aprehensión de Cervezas
                                                                </option>
                                                                <option value="GOA Aprehensión de Tabaco y Otros" <?= $accion == 'GOA Aprehensión de Tabaco y Otros' ? 'selected' : '' ?>>
                                                                    GOA – Aprehensión de Tabaco y Otros
                                                                </option>
                                                                <option value="Registro de Visitas a Establecimientos Comerciales" <?= $accion == 'Registro de Visitas a Establecimientos Comerciales' ? 'selected' : '' ?>>
                                                                    Registro de Visitas a Establecimientos Comerciales
                                                                </option>
                                                                <option value="GOA Juridico" <?= $accion == 'GOA Juridico' ? 'selected' : '' ?>>
                                                                    GOA – Jurídico
                                                                </option>
                                                            </select>
                                                        </div>

                                                        <!-- Selector Nivel 2: Sub-acción (INGRESOS) -->
                                                        <div class="col-md-12 col-xs-12" id="wrapperSubAccionIngresos" style="<?= $grupoActivo == 'DIRECCION DE INGRESOS' ? '' : 'display:none;' ?>">
                                                            <label for="accionIngresos" class="form-label fw-bold mb-1">
                                                                Tipo de Acción
                                                            </label>
                                                            <select onchange="updateUrlAccionHacienda(this)" class="form-control" id="accionIngresos" name="accionIngresos">
                                                                <option value="Capacitacion Fiscal y Financiera" <?= $accion == 'Capacitacion Fiscal y Financiera' ? 'selected' : '' ?>>
                                                                    Capacitación Fiscal y Financiera
                                                                </option>
                                                            </select>
                                                        </div>

                                                        <!-- Selector Nivel 2: Sub-acción (TESORERÍA) -->
                                                        <div class="col-md-12 col-xs-12" id="wrapperSubAccionTesoreria" style="<?= $grupoActivo == 'TESORIA' ? '' : 'display:none;' ?>">
                                                            <label for="accionTesoreria" class="form-label fw-bold mb-1">
                                                                Tipo de Acción
                                                            </label>
                                                            <select onchange="updateUrlAccionHacienda(this)" class="form-control" id="accionTesoreria" name="accionTesoreria">
                                                                <option value="Recaudo del impuesto al consumo" <?= $accion == 'Recaudo del impuesto al consumo' ? 'selected' : '' ?>>
                                                                    Recaudo del Impuesto al Consumo
                                                                </option>
                                                                <option value="Recaudo del impuesto de registro" <?= $accion == 'Recaudo del impuesto de registro' ? 'selected' : '' ?>>
                                                                    Recaudo del Impuesto de Registro
                                                                </option>
                                                                <option value="Impuesto Estampillas Recaudado" <?= $accion == 'Impuesto Estampillas Recaudado' ? 'selected' : '' ?>>
                                                                    Impuesto Estampillas Recaudado
                                                                </option>
                                                            </select>
                                                        </div>

                                                        <!-- Selector Nivel 2: Sub-acción (IMPUESTO UNIFICADO DE AUTOMOTORES) -->
                                                        <div class="col-md-12 col-xs-12" id="wrapperSubAccionAutomotores" style="<?= $grupoActivo == 'IMPUESTO UNIFICADO DE AUTOMOTORES' ? '' : 'display:none;' ?>">
                                                            <label for="accionAutomotores" class="form-label fw-bold mb-1">
                                                                Tipo de Acción
                                                            </label>
                                                            <select onchange="updateUrlAccionHacienda(this)" class="form-control" id="accionAutomotores" name="accionAutomotores">
                                                                <option value="Impuesto Vehicular Recaudado" <?= $accion == 'Impuesto Vehicular Recaudado' ? 'selected' : '' ?>>
                                                                    Impuesto Vehicular Recaudado
                                                                </option>
                                                            </select>
                                                        </div>
                                                    </div>
                                                </div>
                                            <?php endif; ?>
                                                    </div>
                                                </div>
                                            </div>
                                            <!-- Selector de Provincia -->
                                            <?php if (!$isHacienda): ?>
                                            <div class="card mb-3">
                                                <div class="card-header text-white p-2 text-cente">
                                                    <h6 class="mb-0">Seleccionar Provincia</h6>
                                                </div>
                                                <div class="card-body p-2">
                                                    <select class="form-control" id="selectProvincia" name="provincia" onchange="cambiarProvincia(this.value)">
                                                        <?php foreach ($provinciasList as $nombreProvincia => $data): ?>
                                                            <option value="<?= $nombreProvincia ?>" <?= $provinciaSeleccionada === $nombreProvincia ? 'selected' : '' ?>>
                                                                <?= $nombreProvincia ?>
                                                            </option>
                                                        <?php endforeach; ?>
                                                    </select>
                                                </div>
                                            </div>
                                            <?php endif; ?>

                                            <!-- Indicadores de la Provincia Seleccionada -->
                                            <?php if ($provinciaDataSeleccionada || $provinciaSeleccionada === 'Todos'): ?>

                                            <?php if (!$isHacienda): ?>
                                            <div>
                                                <!-- Total Proyectos -->
                                                <div class="card text-center mb-3 bg-azul text-white">
                                                    <div class="card-body p-2">
                                                        <h3 class="font-weight-bold mb-0 valor-grande"><?= number_format($totalProyectosProvincia) ?></h3>
                                                        <small class="text-uppercase">Total Proyectos</small>
                                                        <div class="mt-1">
                                                            <small><?= $nombreMostrar ?? $provinciaSeleccionada ?></small>
                                                        </div>
                                                    </div>
                                                </div>

                                                <!-- Proyectos Terminados -->
                                                <div class="card text-white bg-success mb-3">
                                                    <div class="card-body p-2 text-center">
                                                        <h4 class="mb-0 valor-mediano"><?= number_format($totalTerminadosProvincia) ?></h4>
                                                        <small>Terminados (<?= $porcentajeTerminadosProvincia ?>%)</small>
                                                    </div>
                                                </div>

                                                <!-- Proyectos en Ejecución -->
                                                <div class="card text-dark bg-warning mb-3">
                                                    <div class="card-body p-2 text-center">
                                                        <h4 class="mb-0 valor-mediano"><?= number_format($totalEjecucionProvincia) ?></h4>
                                                        <small>En ejecución (<?= $porcentajeEjecucionProvincia ?>%)</small>
                                                    </div>
                                                </div>

                                                <!-- Proyectos en Contratación -->
                                                <div class="card text-white bg-primary mb-3">
                                                    <div class="card-body p-2 text-center">
                                                        <h4 class="mb-0 valor-mediano"><?= number_format($totalContratacionProvincia) ?></h4>
                                                        <small>En contratación (<?= $porcentajeContratacionProvincia ?>%)</small>
                                                    </div>
                                                </div>

                                                <!-- Proyectos en Formulación -->
                                                <div class="card text-white bg-gris mb-3">
                                                    <div class="card-body p-2 text-center">
                                                        <h4 class="mb-0 valor-mediano"><?= number_format($totalFormulacionProvincia) ?></h4>
                                                        <small>En formulación (<?= $porcentajeFormulacionProvincia ?>%)</small>
                                                    </div>
                                                </div>

                                                <!-- Inversión Total -->
                                                <div class="card text-center mb-3 bg-verde text-white">
                                                    <div class="card-body p-2">
                                                        <h5 class="mb-0 text-white valor-mediano">$<?= number_format($totalInversionProvincia, 0, ',', '.') ?></h5>
                                                        <small class="text-white">Inversión Total</small>
                                                    </div>
                                                </div>
                                            </div>
                                            <?php endif; ?>

                                            <?php /* Resumen Financiero (Totales Generales) - oculto porque siempre muestra 0 */ ?>

                                            <!-- 2. Detalle de Licores -->
                                            <?php if (isset($accion) && $accion == $selectLicores && !empty($infoLicores)): ?>
                                            <div class="card mb-3 mx-3">
                                                <!-- Encabezado de la Tarjeta (Licores) -->
                                                <div class="card-header text-white p-2 text-center" style="background-color: #7b9de6; color: #414d58 !important;">
                                                    <h6 class="mb-0" style="color: #414d58 !important;">
                                                        <i class="bi bi-cup-straw me-2"></i>
                                                        Detalle de Licores
                                                    </h6>
                                                </div>
                                                
                                                <!-- Cuerpo de la Tarjeta (Transformado: Cada fila es un DIV) -->
                                                <div class="card-body p-2 resumen-container">
                                                    
                                                    <!-- === ETIQUETAS DE ENCABEZADO PARA CONTEXTO === -->
                                                    <!-- Opcional: Esto imita la cabecera de la tabla sin usar TABLE -->
                                                    <div class="d-flex justify-content-between text-muted small fw-bold mb-1 px-2">
                                                        <span class="text-start" style="width: 40%;">TIPO</span>
                                                        <span class="text-center" style="width: 30%;">CANT.</span>
                                                        <span class="text-end" style="width: 30%;">VALOR</span>
                                                    </div>
                                                    
                                                    <!-- === CICLO PHP: CADA FILA DE DATOS ES UN DIV === -->
                                                    <?php foreach ($infoLicores as $row): ?>
                                                        <!-- Estilo de cada fila: fondo claro, borde redondeado, padding -->
                                                        <div class="mb-2 p-2 border rounded bg-light item-licor-detalle">
                                                            <div class="d-flex justify-content-between align-items-center">
                                                                
                                                                <!-- Columna 1: Tipo (Alineado a la izquierda) -->
                                                                <small class="fw-bold text-dark text-start" style="width: 40%;">
                                                                    <?= htmlspecialchars($row['tipo']) ?>
                                                                </small>
                                                                
                                                                <!-- Columna 2: Cantidad Incautada (Alineado al centro/derecha) -->
                                                                <strong class="text-center" style="width: 30%;">
                                                                    <?= number_format($row['total_incautacion_licores']) ?>
                                                                </strong>
                                                                
                                                                <!-- Columna 3: Valor (Alineado a la derecha, color de énfasis) -->
                                                                <strong class="text-end" style="width: 30%;">
                                                                    $<?= number_format($row['total_valor_licores']) ?>
                                                                </strong>
                                                            </div>
                                                        </div>
                                                    <?php endforeach; ?>
                                                    <!-- === FIN DEL CICLO === -->
                                                    
                                                </div>
                                            </div>
                                            <?php endif; ?>

                                            <!-- 3. Detalle de Cigarrillos -->
                                            <?php if (isset($accion) && $accion == $selectCigarrillos && !empty($infoCigarrillos)): ?>
                                            <div class="card mb-3 mx-3" style="border: 1px solid #f5e327;">
                                                <!-- Encabezado de la Tarjeta (Cigarrillos) -->
                                                <div class="card-header text-white p-2 text-center" style="background-color: #f5e327;">
                                                    <h6 class="mb-0">
                                                        <i class="bi bi-fire me-2"></i>
                                                        Detalle de Cigarrillos
                                                    </h6>
                                                </div>
                                                
                                                <!-- Cuerpo de la Tarjeta con diseño de resumen (Flexbox) -->
                                                <div class="card-body p-2 resumen-container">
                                                    
                                                    <!-- ETIQUETAS DE ENCABEZADO PARA CONTEXTO (Simula la cabecera de la tabla) -->
                                                    <div class="d-flex justify-content-between text-muted small fw-bold mb-1 px-2">
                                                        <span class="text-start" style="width: 40%;">TIPO</span>
                                                        <span class="text-center" style="width: 30%;">CANT.</span>
                                                        <span class="text-end" style="width: 30%;">VALOR</span>
                                                    </div>
                                                    
                                                    <!-- CICLO PHP: CADA FILA DE CIGARRILLOS ES UN DIV -->
                                                    <?php foreach ($infoCigarrillos as $row): ?>
                                                        <!-- Estilo de cada fila: fondo claro, borde redondeado, padding -->
                                                        <div class="mb-2 p-2 border rounded bg-light item-cigarrillo-detalle">
                                                            <div class="d-flex justify-content-between align-items-center">
                                                                
                                                                <!-- Columna 1: Tipo (Alineado a la izquierda) -->
                                                                <small class="fw-bold text-dark text-start" style="width: 40%;">
                                                                    <?= htmlspecialchars($row['tipo_cigarrillo']) ?>
                                                                </small>
                                                                
                                                                <!-- Columna 2: Cantidad Incautada (Alineado al centro/derecha, color azul) -->
                                                                <strong class="text-center" style="width: 30%;">
                                                                    <?= number_format($row['total_incautacion_cigarrillos']) ?>
                                                                </strong>
                                                                
                                                                <!-- Columna 3: Valor (Alineado a la derecha, color de éxito/verde) -->
                                                                <strong class="text-end" style="width: 30%;">
                                                                    $<?= number_format($row['total_valor_cigarrillos']) ?>
                                                                </strong>
                                                            </div>
                                                        </div>
                                                    <?php endforeach; ?>
                                                    <!-- FIN DEL CICLO -->
                                                    
                                                </div>
                                            </div>
                                            <?php endif; ?>

                                            <!-- 4. Detalle de Tabacos -->
                                            <?php if (isset($accion) && $accion == $selectCigarrillos && !empty($infoTabacos)): ?>
                                            <div class="card mb-3 mx-3" style="border: 1px solid #e74c3c;">
                                                <!-- Encabezado de la Tarjeta (Tabacos) -->
                                                <div class="card-header text-white p-2 text-center" style="background-color: #e74c3c;">
                                                    <h6 class="mb-0">
                                                        <i class="bi bi-fire me-2"></i>
                                                        Detalle de Tabacos
                                                    </h6>
                                                </div>

                                                <!-- Cuerpo de la Tarjeta con diseño de resumen (Flexbox) -->
                                                <div class="card-body p-2 resumen-container">

                                                    <!-- ETIQUETAS DE ENCABEZADO PARA CONTEXTO (Simula la cabecera de la tabla) -->
                                                    <div class="d-flex justify-content-between text-muted small fw-bold mb-1 px-2">
                                                        <span class="text-start" style="width: 40%;">TIPO</span>
                                                        <span class="text-center" style="width: 30%;">CANT.</span>
                                                        <span class="text-end" style="width: 30%;">VALOR</span>
                                                    </div>

                                                    <!-- CICLO PHP: CADA FILA DE CIGARRILLOS ES UN DIV -->
                                                    <?php foreach ($infoTabacos as $row): ?>
                                                        <!-- Estilo de cada fila: fondo claro, borde redondeado, padding -->
                                                        <div class="mb-2 p-2 border rounded bg-light item-cigarrillo-detalle">
                                                            <div class="d-flex justify-content-between align-items-center">

                                                                <!-- Columna 1: Tipo (Alineado a la izquierda) -->
                                                                <small class="fw-bold text-dark text-start" style="width: 40%;">
                                                                    <?= htmlspecialchars($row['tipo_tabaco']) ?>
                                                                </small>

                                                                <!-- Columna 2: Cantidad Incautada (Alineado al centro/derecha, color azul) -->
                                                                <strong class="text-center" style="width: 30%;">
                                                                    <?= number_format($row['total_incautacion_tabaco']) ?>
                                                                </strong>

                                                                <!-- Columna 3: Valor (Alineado a la derecha, color de éxito/verde) -->
                                                                <strong class="text-end" style="width: 30%;">
                                                                    $<?= number_format($row['total_valor_tabaco']) ?>
                                                                </strong>
                                                            </div>
                                                        </div>
                                                    <?php endforeach; ?>
                                                </div>
                                            </div>
                                            <?php endif; ?>

                                            <!-- 5. Detalle de Cervezas -->
                                            <?php if (isset($accion) && $accion == $selectCervezas && !empty($infoCerveza)): ?>
                                            <div class="card mb-3 mx-3" style="border: 1px solid #FFFF00;">

                                                <div class="card-header text-white p-3 text-center rounded-top" style="background-color: #FFFF00;">
                                                    <h6 class="mb-0 fw-bold">
                                                        <i class="bi bi-beer-mug-fill me-2"></i>
                                                        Detalle de Cervezas
                                                    </h6>
                                                </div>

                                                <!-- Cuerpo de la Tarjeta con diseño de resumen (Flexbox) -->
                                                <div class="card-body p-2 resumen-container">

                                                    <!-- ETIQUETAS DE ENCABEZADO PARA CONTEXTO (Simula la cabecera de la tabla) -->
                                                    <div class="d-flex justify-content-between text-muted small fw-bold mb-1 px-2">
                                                        <span class="text-start" style="width: 40%;">TIPO</span>
                                                        <span class="text-center" style="width: 30%;">CANT.</span>
                                                        <span class="text-end" style="width: 30%;">VALOR</span>
                                                    </div>
                                                    
                                                    <!-- CICLO PHP: Iterando correctamente sobre $infoCerveza -->
                                                    <?php foreach ($infoCerveza as $row): ?>
                                                        <!-- Estilo de cada fila: fondo claro, borde redondeado, padding -->
                                                        <div class="mb-2 p-2 border rounded bg-light item-cerveza-detalle">
                                                            <div class="d-flex justify-content-between align-items-center">
                                                                
                                                                <!-- Columna 1: Tipo (Alineado a la izquierda) -->
                                                                <small class="fw-bold text-dark text-start" style="width: 40%;">
                                                                    <?= htmlspecialchars($row['tipo']) ?>
                                                                </small>
                                                                
                                                                <!-- Columna 2: Cantidad Incautada (Alineado al centro/derecha) -->
                                                                <strong class="text-secondary text-center" style="width: 30%;">
                                                                    <?= number_format($row['total_incautacion_cerveza']) ?>
                                                                </strong>
                                                                
                                                                <!-- Columna 3: Valor (Alineado a la derecha, color de éxito/verde) -->
                                                                <strong class="text-end" style="width: 30%;">
                                                                    $<?= number_format($row['total_valor_cerveza']) ?>
                                                                </strong>
                                                            </div>
                                                        </div>
                                                    <?php endforeach; ?>

                                                </div>
                                            </div>
                                            <?php endif; ?>

                                            <!-- Información para Hacienda GOA – Aprehensiones (TODAS unificadas) -->
                                            <?php if ($secretaria == Util::getSecretariaIdHacienda() && isset($accion) && $accion == 'GOA - Aprehensiones'): ?>
                                            <div class="card mb-3 mx-3" style="border: 2px solid #20427F;">
                                                <div class="card-header text-white p-3 text-center rounded-top" style="background-color: #20427F;">
                                                    <h6 class="mb-0 fw-bold">
                                                        <i class="bi bi-shield-fill-check me-2"></i>
                                                        GOA – Aprehensiones Totales (Licores + Cigarrillos + Cervezas + Tabaco)
                                                    </h6>
                                                </div>
                                                <div class="card-body p-3">
                                                    <div class="d-flex flex-column flex-md-row justify-content-around align-items-center">
                                                        <div class="text-center p-2 flex-fill border-bottom border-md-0 mb-3 mb-md-0">
                                                            <h4 class="fw-bolder display-6">
                                                                <?= number_format($GOATotal_cantidad_aprehendida ?? 0, 0, ',', '.') ?>
                                                            </h4>
                                                            <small class="text-muted fw-semibold d-block mt-1">Cantidad Total Aprehendida (Unidades)</small>
                                                        </div>
                                                        <div class="vr mx-3 d-none d-md-block" style="height: 50px;"></div>
                                                        <div class="text-center p-2 flex-fill">
                                                            <h4 class="fw-bolder display-6">
                                                                $<?= number_format($GOATotal_avaluo_comercial ?? 0, 0, ',', '.') ?>
                                                            </h4>
                                                            <small class="text-muted fw-semibold d-block mt-1">Valor Total Avalúo Comercial</small>
                                                        </div>
                                                    </div>
                                                </div>
                                                <!-- Detalle por tipo -->
                                                <div class="card-footer p-3">
                                                    <div class="row text-center g-2">
                                                        <div class="col-6 col-md-3">
                                                            <div class="p-2 rounded" style="background:#e8f4fd;">
                                                                <div class="fw-bold text-primary"><i class="bi bi-wine-glass-fill"></i> Licores</div>
                                                                <div><?= number_format($GOALicores_cantidad_aprehendida ?? 0, 0, ',', '.') ?> und.</div>
                                                                <small class="text-muted">$<?= number_format($GOALicores_avaluo_comercial ?? 0, 0, ',', '.') ?></small>
                                                            </div>
                                                        </div>
                                                        <div class="col-6 col-md-3">
                                                            <div class="p-2 rounded" style="background:#f5e8ff;">
                                                                <div class="fw-bold" style="color:#7c3aed;"><i class="bi bi-fire"></i> Cigarrillos</div>
                                                                <div><?= number_format($GOACigarrillos_cantidad_aprehendida ?? 0, 0, ',', '.') ?> und.</div>
                                                                <small class="text-muted">$<?= number_format($GOACigarrillos_avaluo_comercial ?? 0, 0, ',', '.') ?></small>
                                                            </div>
                                                        </div>
                                                        <div class="col-6 col-md-3">
                                                            <div class="p-2 rounded" style="background:#fef9e7;">
                                                                <div class="fw-bold text-warning"><i class="bi bi-cup-fill"></i> Cervezas</div>
                                                                <div><?= number_format($GOACervezas_cantidad_aprehendida ?? 0, 0, ',', '.') ?> und.</div>
                                                                <small class="text-muted">$<?= number_format($GOACervezas_avaluo_comercial ?? 0, 0, ',', '.') ?></small>
                                                            </div>
                                                        </div>
                                                        <div class="col-6 col-md-3">
                                                            <div class="p-2 rounded" style="background:#fef5e7;">
                                                                <div class="fw-bold" style="color:#d35400;"><i class="bi bi-box-seam"></i> Tabaco y Otros</div>
                                                                <div><?= number_format($GOATabaco_cantidad_aprehendida ?? 0, 0, ',', '.') ?> und.</div>
                                                                <small class="text-muted">$<?= number_format($GOATabaco_avaluo_comercial ?? 0, 0, ',', '.') ?></small>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <?php endif; ?>

                                            <!-- Información para Hacienda GOA Licores -->
                                            <?php if ($secretaria == Util::getSecretariaIdHacienda() && isset($accion) && $accion == $GOALicores): ?>
                                            <div class="card mb-3 mx-3" style="border: 1px solid #0295F0;">
                                                
                                                <!-- Encabezado con color distintivo para Licores -->
                                                <div class="card-header text-white p-3 text-center rounded-top" style="background-color: #0295F0;">
                                                    <h6 class="mb-0 fw-bold">
                                                        <i class="bi bi-wine-glass-fill me-2"></i>
                                                        Totales GOA Aprehensiones de Licores
                                                    </h6>
                                                </div>
                                                
                                                <!-- Cuerpo de la Tarjeta con diseño Responsivo (Flex) -->
                                                <div class="card-body p-3">
                                                    <!-- Contenedor Flex: Se convierte en fila en pantallas medias (md) y superiores -->
                                                    <div class="d-flex flex-column flex-md-row justify-content-around align-items-center">
                                                        
                                                        <!-- Indicador 1: Cantidad Total Aprehendida -->
                                                        <div class="text-center p-2 flex-fill border-end-md border-bottom border-md-0 mb-3 mb-md-0">
                                                            <h4 class="fw-bolder display-6">
                                                                <?= number_format($GOALicores_cantidad_aprehendida ?? 0, 0, ',', '.') ?>
                                                            </h4>
                                                            <small class="text-muted fw-semibold d-block mt-1">Cantidad Total Aprehendida (Unidades)</small>
                                                        </div>
                                                        
                                                        <!-- Separador vertical (solo en desktop) -->
                                                        <div class="vr mx-3 d-none d-md-block" style="height: 50px;"></div>

                                                        <!-- Indicador 2: Valor Total Avalúo Comercial -->
                                                        <div class="text-center p-2 flex-fill">
                                                            <h4 class="fw-bolder display-6">
                                                                $<?= number_format($GOALicores_avaluo_comercial ?? 0, 0, ',', '.') ?>
                                                            </h4>
                                                            <small class="text-muted fw-semibold d-block mt-1">Valor Total Avalúo Comercial</small>
                                                        </div>
                                                        
                                                    </div>
                                                </div>
                                            </div>
                                            <?php endif; ?>

                                            <!-- Información para Hacienda GOA Cigarrillos -->
                                            <?php if ($secretaria == Util::getSecretariaIdHacienda() && isset($accion) && $accion == $GOACigarrillos): ?>
                                            <div class="card mb-4 shadow-lg border-dark-subtle mx-3" style="border: 1px solid #e427f5ff;">
                                                
                                                <!-- Encabezado con color distintivo (Gris humo para Cigarrillos) -->
                                                <div class="card-header text-white p-3 text-center rounded-top" style="background-color: #b8a2ff;">
                                                    <h6 class="mb-0 fw-bold">
                                                        <i class="bi bi-fire me-2"></i>
                                                        Totales GOA Aprehensiones de Cigarrillos
                                                    </h6>
                                                </div>
                                                
                                                <!-- Cuerpo de la Tarjeta con diseño Responsivo (Flex) -->
                                                <div class="card-body p-3">
                                                    <!-- Contenedor Flex: Se convierte en fila en pantallas medias (md) y superiores -->
                                                    <div class="d-flex flex-column flex-md-row justify-content-around align-items-center">
                                                        
                                                        <!-- Indicador 1: Cantidad Total Aprehendida -->
                                                        <div class="text-center p-2 flex-fill border-end-md border-bottom border-md-0 mb-3 mb-md-0">
                                                            <h4 class="fw-bolder display-6">
                                                                <?= number_format($GOACigarrillos_cantidad_aprehendida ?? 0, 0, ',', '.') ?>
                                                            </h4>
                                                            <small class="text-muted fw-semibold d-block mt-1">Cantidad Total Aprehendida (Unidades)</small>
                                                        </div>
                                                        
                                                        <!-- Separador vertical (solo en desktop) -->
                                                        <div class="vr mx-3 d-none d-md-block" style="height: 50px;"></div>

                                                        <!-- Indicador 2: Valor Total Avalúo Comercial -->
                                                        <div class="text-center p-2 flex-fill">
                                                            <h4 class="fw-bolder display-6">
                                                                $<?= number_format($GOACigarrillos_avaluo_comercial ?? 0, 0, ',', '.') ?>
                                                            </h4>
                                                            <small class="text-muted fw-semibold d-block mt-1">Valor Total Avalúo Comercial</small>
                                                        </div>
                                                        
                                                    </div>
                                                </div>
                                            </div>
                                            <?php endif; ?>

                                            <!-- Información para Hacienda GOA Cervezas -->
                                            <?php if ($secretaria == Util::getSecretariaIdHacienda() && isset($accion) && $accion == $GOACervezas): ?>
                                            <div class="card mb-4 shadow-lg border-warning mx-3">
                                                
                                                <!-- Encabezado con color distintivo (Dorado/Ámbar para Cervezas) -->
                                                <div class="card-header text-dark p-3 text-center rounded-top" style="background-color: #f1c40f;">
                                                    <h6 class="mb-0 fw-bold">
                                                        <i class="bi bi-cup-fill me-2"></i>
                                                        Totales GOA Aprehensiones de Cervezas
                                                    </h6>
                                                </div>
                                                
                                                <!-- Cuerpo de la Tarjeta con diseño Responsivo (Flex) -->
                                                <div class="card-body p-3">
                                                    <!-- Contenedor Flex: Se convierte en fila en pantallas medias (md) y superiores -->
                                                    <div class="d-flex flex-column flex-md-row justify-content-around align-items-center">
                                                        
                                                        <!-- Indicador 1: Cantidad Total Aprehendida -->
                                                        <div class="text-center p-2 flex-fill border-end-md border-bottom border-md-0 mb-3 mb-md-0">
                                                            <h4 class="fw-bolder display-6">
                                                                <?= number_format($GOACervezas_cantidad_aprehendida ?? 0, 0, ',', '.') ?>
                                                            </h4>
                                                            <small class="text-muted fw-semibold d-block mt-1">Cantidad Total Aprehendida (Unidades)</small>
                                                        </div>
                                                        
                                                        <!-- Separador vertical (solo en desktop) -->
                                                        <div class="vr mx-3 d-none d-md-block" style="height: 50px;"></div>

                                                        <!-- Indicador 2: Valor Total Avalúo Comercial -->
                                                        <div class="text-center p-2 flex-fill">
                                                            <h4 class="fw-bolder display-6">
                                                                $<?= number_format($GOACervezas_avaluo_comercial ?? 0, 0, ',', '.') ?>
                                                            </h4>
                                                            <small class="text-muted fw-semibold d-block mt-1">Valor Total Avalúo Comercial</small>
                                                        </div>
                                                        
                                                    </div>
                                                </div>
                                            </div>
                                            <?php endif; ?>

                                            <!-- Información para Hacienda GOA Tabaco y Otros -->
                                            <?php if ($secretaria == Util::getSecretariaIdHacienda() && isset($accion) && $accion == $GOATabaco): ?>
                                                <div class="card mb-4 shadow-sm mx-3" style="border: 1px solid #f1c40f;">
                                                
                                                
                                                <!-- Encabezado con color distintivo (Marrón Oscuro/Terracota para Tabaco y Otros) -->
                                                <div class="card-header text-white p-2 text-center rounded-top" style="Background-color: #f1c40f;">
                                                    <h6 class="mb-0 fw-bold">
                                                        <i class="bi bi-box-seam me-2"></i>
                                                        Totales GOA Aprehensiones de Tabaco y Otros
                                                    </h6>
                                                </div>
                                                
                                                <!-- Cuerpo de la Tarjeta con diseño Responsivo (Flex) -->
                                                <div class="card-body p-3">
                                                    <!-- Contenedor Flex: Se convierte en fila en pantallas medias (md) y superiores -->
                                                    <div class="d-flex flex-column flex-md-row justify-content-around align-items-center">
                                                        
                                                        <!-- Indicador 1: Cantidad Total Aprehendida -->
                                                        <div class="text-center p-2 flex-fill border-end-md border-bottom border-md-0 mb-3 mb-md-0">
                                                            <h4 class="fw-bolder display-6">
                                                                <?= number_format($GOATabaco_cantidad_aprehendida ?? 0, 0, ',', '.') ?>
                                                            </h4>
                                                            <small class="text-muted fw-semibold d-block mt-1">Cantidad Total Aprehendida (Unidades)</small>
                                                        </div>
                                                        
                                                        <!-- Separador vertical (solo en desktop) -->
                                                        <div class="vr mx-3 d-none d-md-block" style="height: 50px;"></div>

                                                        <!-- Indicador 2: Valor Total Avalúo Comercial -->
                                                        <div class="text-center p-2 flex-fill">
                                                            <h4 class="fw-bolder display-6">
                                                                $<?= number_format($GOATabaco_avaluo_comercial ?? 0, 0, ',', '.') ?>
                                                            </h4>
                                                            <small class="text-muted fw-semibold d-block mt-1">Valor Total Avalúo Comercial</small>
                                                        </div>
                                                        
                                                    </div>
                                                </div>
                                            </div>
                                            <?php endif; ?>


                                            <!-- Información para Hacienda Impuesto Vehicular Recaudado -->
                                            <?php if ($secretaria == Util::getSecretariaIdHacienda() && isset($accion) && $accion == 'Impuesto Vehicular Recaudado'): ?>
                                            <div class="card mb-3 mx-3" style="border: 2px solid #4169E1;">
                                                <div class="card-header text-white p-3 text-center rounded-top" style="background-color: #4169E1;">
                                                    <h6 class="mb-0 fw-bold">
                                                        <i class="bi bi-car-front-fill me-2"></i>
                                                        Impuesto Vehicular Recaudado
                                                    </h6>
                                                </div>
                                                <div class="card-body p-3">

                                                    <!-- Sección: Recaudo -->
                                                    <h6 class="fw-bold text-secondary mb-2 border-bottom pb-1">
                                                        <i class="bi bi-cash-coin me-1"></i> Recaudo
                                                    </h6>
                                                    <div class="row text-center g-2 mb-3">
                                                        <div class="col-4">
                                                            <div class="p-2 rounded" style="background:#e8eaf6;">
                                                                <div class="fw-bold small" style="color:#4169E1;">Total Recaudo + Trámite</div>
                                                                <div class="fw-bolder">$<?= number_format($vehicular_total_recaudo_y_tramite ?? 0, 0, ',', '.') ?></div>
                                                            </div>
                                                        </div>
                                                        <div class="col-4">
                                                            <div class="p-2 rounded" style="background:#e8eaf6;">
                                                                <div class="fw-bold small" style="color:#4169E1;">Valor Recaudo</div>
                                                                <div class="fw-bolder">$<?= number_format($vehicular_total_recaudo ?? 0, 0, ',', '.') ?></div>
                                                            </div>
                                                        </div>
                                                        <div class="col-4">
                                                            <div class="p-2 rounded" style="background:#e8eaf6;">
                                                                <div class="fw-bold small" style="color:#4169E1;">Valor Trámites</div>
                                                                <div class="fw-bolder">$<?= number_format($vehicular_total_tramites ?? 0, 0, ',', '.') ?></div>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <!-- Sección: Operativos Vehículos -->
                                                    <h6 class="fw-bold text-secondary mb-2 border-bottom pb-1">
                                                        <i class="bi bi-shield-check me-1"></i> Operativos Vehículos
                                                    </h6>
                                                    <div class="row text-center g-2">
                                                        <div class="col-6 col-md-3">
                                                            <div class="p-2 rounded" style="background:#fce4ec;">
                                                                <div class="fw-bold text-danger small">Cant. Operativos</div>
                                                                <div class="fw-bolder"><?= number_format($vehicular_total_operativos ?? 0, 0, ',', '.') ?></div>
                                                            </div>
                                                        </div>
                                                        <div class="col-6 col-md-3">
                                                            <div class="p-2 rounded" style="background:#fce4ec;">
                                                                <div class="fw-bold text-danger small">Cant. Emplazados</div>
                                                                <div class="fw-bolder"><?= number_format($vehicular_total_emplazados ?? 0, 0, ',', '.') ?></div>
                                                            </div>
                                                        </div>
                                                        <div class="col-6 col-md-3">
                                                            <div class="p-2 rounded" style="background:#fce4ec;">
                                                                <div class="fw-bold text-danger small">Placas Consultadas</div>
                                                                <div class="fw-bolder"><?= number_format($vehicular_total_placas_consultadas ?? 0, 0, ',', '.') ?></div>
                                                            </div>
                                                        </div>
                                                        <div class="col-6 col-md-3">
                                                            <div class="p-2 rounded" style="background:#fce4ec;">
                                                                <div class="fw-bold text-danger small">Campañas Sensibilización</div>
                                                                <div class="fw-bolder"><?= number_format($vehicular_total_campanas_sensibilizacion ?? 0, 0, ',', '.') ?></div>
                                                            </div>
                                                        </div>
                                                    </div>

                                                </div>
                                            </div>
                                            <?php endif; ?>

                                            <!-- Información para Hacienda GOA Jurídico -->
                                            <?php if ($secretaria == Util::getSecretariaIdHacienda() && isset($accion) && $accion == 'GOA Juridico'): ?>
                                            <div class="card mb-3 mx-3" style="border: 2px solid #1a6b4a;">
                                                <div class="card-header text-white p-3 text-center rounded-top" style="background-color: #1a6b4a;">
                                                    <h6 class="mb-0 fw-bold">
                                                        <i class="bi bi-briefcase-fill me-2"></i>
                                                        GOA Jurídico
                                                    </h6>
                                                </div>
                                                <div class="card-body p-3">

                                                    <!-- Sección: Proceso en Custodia -->
                                                    <h6 class="fw-bold text-secondary mb-2 border-bottom pb-1">
                                                        <i class="bi bi-archive-fill me-1"></i> Proceso en Custodia
                                                    </h6>
                                                    <div class="row text-center g-2 mb-3">
                                                        <div class="col-4">
                                                            <div class="p-2 rounded" style="background:#e8f5e9;">
                                                                <div class="fw-bold text-success small">Valor Total</div>
                                                                <div class="fw-bolder">$<?= number_format($GOAJuridico_custodia_valor_total ?? 0, 0, ',', '.') ?></div>
                                                            </div>
                                                        </div>
                                                        <div class="col-4">
                                                            <div class="p-2 rounded" style="background:#e8f5e9;">
                                                                <div class="fw-bold text-success small">Cant. Procesos</div>
                                                                <div class="fw-bolder"><?= number_format($GOAJuridico_custodia_cantidad_procesos ?? 0, 0, ',', '.') ?></div>
                                                            </div>
                                                        </div>
                                                        <div class="col-4">
                                                            <div class="p-2 rounded" style="background:#e8f5e9;">
                                                                <div class="fw-bold text-success small">Cant. Unidades</div>
                                                                <div class="fw-bolder"><?= number_format($GOAJuridico_custodia_cantidad_unidades ?? 0, 0, ',', '.') ?></div>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <!-- Sección: Destrucción de Procesos -->
                                                    <h6 class="fw-bold text-secondary mb-2 border-bottom pb-1">
                                                        <i class="bi bi-trash3-fill me-1"></i> Destrucción de Procesos
                                                    </h6>
                                                    <div class="row text-center g-2">
                                                        <div class="col-6">
                                                            <div class="p-2 rounded" style="background:#fce4ec;">
                                                                <div class="fw-bold text-danger small">Cant. Unidades</div>
                                                                <div class="fw-bolder"><?= number_format($GOAJuridico_destruccion_cantidad_unidades ?? 0, 0, ',', '.') ?></div>
                                                            </div>
                                                        </div>
                                                        <div class="col-6">
                                                            <div class="p-2 rounded" style="background:#fce4ec;">
                                                                <div class="fw-bold text-danger small">Valor Total</div>
                                                                <div class="fw-bolder">$<?= number_format($GOAJuridico_destruccion_valor_total ?? 0, 0, ',', '.') ?></div>
                                                            </div>
                                                        </div>
                                                    </div>

                                                </div>
                                            </div>
                                            <?php endif; ?>

                                            <!-- Información para Hacienda Registro de Capacitaciones del GOA -->
                                            <?php if ($secretaria == Util::getSecretariaIdHacienda() && isset($accion) && $accion == $registroVisitas ): ?>
                                            <div class="card mb-4 shadow-lg border-brown-dark mx-3">
                                                
                                                <!-- Encabezado Registro de Capacitaciones del GOA -->
                                                <div class="card-header text-white p-3 text-center rounded-top" style="background-color: #2b80e2ff;">
                                                    <h6 class="mb-0 fw-bold">
                                                        <i class="bi bi-box-seam me-2"></i>
                                                        Total Visitas Establecimientos GOA
                                                    </h6>
                                                </div>
                                                
                                                <!-- Cuerpo de la Tarjeta con diseño Responsivo (Flex) -->
                                                <div class="card-body p-3">
                                                    <!-- Contenedor Flex: Se convierte en fila en pantallas medias (md) y superiores -->
                                                    <div class="d-flex flex-column flex-md-row justify-content-around align-items-center">
                                                        
                                                        <!-- Indicador 1: Cantidad Total Registro de Capacitaciones del GOA -->
                                                        <div class="text-center p-2 flex-fill border-end-md border-bottom border-md-0 mb-3 mb-md-0">
                                                            <h4 class="fw-bolder display-6">
                                                                <?= number_format($GOAcantidad_visitas_al_municipio ?? 0, 0, ',', '.') ?>
                                                            </h4>
                                                            <small class="text-muted fw-semibold d-block mt-1">Cantidad Total (visitas)</small>
                                                        </div>

                                                    </div>
                                                </div>
                                            </div>
                                            <?php endif; ?>


                                            <?php else: ?>
                                            <div class="card text-center mb-3">
                                                <div class="card-body">
                                                    <p class="text-muted">No hay datos disponibles </p>
                                                </div>
                                            </div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="modal fade" id="modalListaProyectos" tabindex="-1" role="dialog" aria-labelledby="modalListaProyectosLabel" aria-hidden="true">
            <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="modalListaProyectosLabel">Listado de Proyectos</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div id="modal-body-content">
                            <div class="text-center p-5">
                                <i class="bi bi-arrow-clockwise fa-spin fs-4"></i>
                                <p class="mt-2">Cargando proyectos...</p>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                    </div>
                </div>
            </div>
        </div>


    <?php include 'admin/include/footer.php'; ?>
    </div>

    <!-- Modal de Geolocalización -->
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
                    <div id="map" style="height: 600px; width: 100%;"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Google Maps JavaScript API -->
    <script async defer src="https://maps.googleapis.com/maps/api/js?key=AIzaSyAbqZVEnSpIPF5dKe89pju2rmQaM58wvY0&callback=initMap"></script>

    <?php include 'admin/include/gerenic_script.php'; ?>
    
    <!-- Required Js -->
    <script src="assets/js/vendor-all.min.js"></script>
    <script src="assets/js/plugins/bootstrap.min.js"></script>
    <script src="assets/js/pcoded.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
    <script type="text/javascript" src="admin/js/mapa_secretaria.js"></script>
    <script type="text/javascript" src="admin/js/secretarias.js"></script>

    <script>
        // Función para cambiar provincia
        function cambiarProvincia(provincia) {
            const url = new URL(window.location.href);
            url.searchParams.set('provincia', provincia);
            window.location.href = url.toString();
        }

        // Función para actualizar URL cuando cambia la secretaría
        function updateUrlSecretaria(select) {
            const secretariaId = select.value;
            const url = new URL(window.location.href);
            url.searchParams.set('secretaria', secretariaId);
            // Limpiar provincia al cambiar secretaría
            url.searchParams.delete('provincia');
            window.location.href = url.toString();
        }

        // Función para actualizar URL cuando cambia la acción en Hacienda (nivel 2)
        function updateUrlAccionHacienda(select) {
            const accion = select.value;
            const secretariaId = document.getElementById('secretariaId').value;
            const url = new URL(window.location.href);
            url.searchParams.set('secretaria', secretariaId);
            url.searchParams.set('accion', accion);
            window.location.href = url.toString();
        }

        // Función para manejar cambio de grupo Hacienda (nivel 1)
        function cambiarGrupoHacienda(grupo) {
            const wrapperGOA         = document.getElementById('wrapperSubAccionGOA');
            const wrapperIngresos    = document.getElementById('wrapperSubAccionIngresos');
            const wrapperTesoreria   = document.getElementById('wrapperSubAccionTesoreria');
            const wrapperAutomotores = document.getElementById('wrapperSubAccionAutomotores');

            // Ocultar todos los sub-selectores
            if (wrapperGOA)         wrapperGOA.style.display         = 'none';
            if (wrapperIngresos)    wrapperIngresos.style.display     = 'none';
            if (wrapperTesoreria)   wrapperTesoreria.style.display    = 'none';
            if (wrapperAutomotores) wrapperAutomotores.style.display  = 'none';

            if (grupo === 'GRUPO OPERATIVO ANTICONTRABANDO') {
                if (wrapperGOA) wrapperGOA.style.display = '';
                const selectGOA = document.getElementById('accionHacienda');
                if (selectGOA) {
                    selectGOA.value = 'GOA - Aprehensiones';
                    updateUrlAccionHacienda(selectGOA);
                }
            } else if (grupo === 'DIRECCION DE INGRESOS') {
                if (wrapperIngresos) wrapperIngresos.style.display = '';
                const selectIngresos = document.getElementById('accionIngresos');
                if (selectIngresos) {
                    updateUrlAccionHacienda(selectIngresos);
                }
            } else if (grupo === 'TESORIA') {
                if (wrapperTesoreria) wrapperTesoreria.style.display = '';
                const selectTesoreria = document.getElementById('accionTesoreria');
                if (selectTesoreria) {
                    updateUrlAccionHacienda(selectTesoreria);
                }
            } else if (grupo === 'IMPUESTO UNIFICADO DE AUTOMOTORES') {
                if (wrapperAutomotores) wrapperAutomotores.style.display = '';
                const selectAutomotores = document.getElementById('accionAutomotores');
                if (selectAutomotores) {
                    updateUrlAccionHacienda(selectAutomotores);
                }
            }
        }

        // Evento de click en el mapa
        document.addEventListener('click', function(e) {
            const target = e.target.closest('.mapaClick');
            if (target) {
                const secretaria = document.getElementById('secretariaId').value;
                let baseUrl = target.getAttribute('data-base-url');
                let newUrl = '';
                if (secretaria == UTIL.getItemHacienda()) {
                    baseUrl = baseUrl.replace('municipios_secretaria_informacion.php', 'municipios_secretaria_informacion_hacienda.php');
                    // Obtener accion del sub-selector visible (GOA, Ingresos, Tesorería o Automotores)
                    const selGOA         = document.getElementById('accionHacienda');
                    const selIngresos    = document.getElementById('accionIngresos');
                    const selTesoreria   = document.getElementById('accionTesoreria');
                    const selAutomotores = document.getElementById('accionAutomotores');
                    const wGOA  = document.getElementById('wrapperSubAccionGOA');
                    const wIng  = document.getElementById('wrapperSubAccionIngresos');
                    const wTes  = document.getElementById('wrapperSubAccionTesoreria');
                    const wAut  = document.getElementById('wrapperSubAccionAutomotores');
                    let accion = '';
                    if (selGOA         && wGOA  && wGOA.style.display  !== 'none') accion = selGOA.value;
                    else if (selIngresos    && wIng  && wIng.style.display  !== 'none') accion = selIngresos.value;
                    else if (selTesoreria   && wTes  && wTes.style.display  !== 'none') accion = selTesoreria.value;
                    else if (selAutomotores && wAut  && wAut.style.display  !== 'none') accion = selAutomotores.value;
                    const separator = baseUrl.includes('?') ? '&' : '?';
                    newUrl = `${baseUrl}${separator}secretaria=${secretaria}&accion=${encodeURIComponent(accion)}`;
                } else {
                    const separator = baseUrl.includes('?') ? '&' : '?';
                    newUrl = `${baseUrl}${separator}secretaria=${secretaria}`;
                }
                if (newUrl) {
                    location.href = newUrl;
                }
            }
        });

        //modal de proyectos listables

        function fetchProyectos(estado, secretaria, provincia, page = 1) {
            const modalBody = $('#modal-body-content');
            const $modalListaProyectos = $('#modalListaProyectos');
            const modalTitle = $modalListaProyectos.find('.modal-title');

            modalTitle.text(`Proyectos en estado: ${estado} (${provincia})`); 

            modalBody.html(`
                <div class="text-center p-5">
                    <i class="bi bi-arrow-clockwise fa-spin fs-4"></i>
                    <p class="mt-2">Cargando proyectos en ${estado} (Página ${page})...</p>
                </div>
            `);

            fetch('admin/classes/fetch_proyectos_por_estado.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },

                body: `estado=${encodeURIComponent(estado)}&secretaria=${encodeURIComponent(secretaria)}&provincia=${encodeURIComponent(provincia)}&page=${page}`
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error(`Error HTTP: ${response.status}`);
                }
                return response.text();
            })
            .then(data => {
                // respuesta en modal
                modalBody.html(data);
                setupPaginationListeners(estado, secretaria, provincia); 
            })
            .catch(error => {
                console.error('Error al cargar la lista de proyectos:', error);
                modalBody.html('<div class="alert alert-danger">Error al cargar los datos. Intente de nuevo.</div>');
            });
        }
        
        function setupPaginationListeners(estado, secretaria, provincia) {
            $('.page-link-ajax').off('click').on('click', function(e) {
                e.preventDefault();
                const newPage = $(this).data('page');
                
                if (!$(this).parent().hasClass('disabled') && !$(this).parent().hasClass('active')) {
                    fetchProyectos(estado, secretaria, provincia, newPage);
                }
            });
        }
        

        $(document).ready(function() {

            const $modalListaProyectos = $('#modalListaProyectos');
            
            $modalListaProyectos.on('show.bs.modal', function (event) {
                
                const button = $(event.relatedTarget); 
                
                const estado = button.data('estado');
                const secretaria = button.data('secretaria');
                const provincia = button.data('provincia');

                fetchProyectos(estado, secretaria, provincia, 1);
            });


            // Inicializar tooltips
            $("img").each(function(index, el) {
                $(this).attr("data-toggle", "tooltip"); 
                $(this).attr("data-placement", "left");
                tooltip = new bootstrap.Tooltip($(this)[0], {}) 
            });
            
        });
    </script>
</body>
</html>