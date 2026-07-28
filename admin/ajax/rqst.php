<?php
session_start();
/**
 * en este archivo se atienden todas las peticiones AJAX
 */
$rqst = $_REQUEST;
$op = isset($rqst['op']) ? $rqst['op'] : '';
header("Content-type: application/javascript; charset=utf-8");
header("Cache-Control: max-age=15, must-revalidate");
header('Access-Control-Allow-Origin: *');

include '../classes/DbConection.php';
include '../classes/Util.php';
include '../classes/SessionData.php';
require_once '../classes/PermissionGate.php';

$opsSinAuth = ['pms_usrlogin', ''];
if (!in_array($op, $opsSinAuth, true)) {
  PermissionGate::authorizeOperation($op);
}

switch ($op) {

  //Guardar proyectos de secretaría
  case 'proyectos_secretaria_save':
      include __DIR__ . '/../classes/Ingreso_proyectos_secretarias.php';
      echo json_encode(Proyectos_Secretarias::saveProyectoSecretarias($_REQUEST, $_FILES));
      break;

  case 'aprobar_proyecto_secretaria':
      include '../classes/Ingreso_proyectos_secretarias.php';
      $proyectoId = $_REQUEST['id'] ?? null;
      $bpin = $_REQUEST['bpin'] ?? '';
      $observaciones = $_REQUEST['observaciones'] ?? '';
      $usuarioId = $_SESSION['session_user']['id'] ?? null;
      $result = Proyectos_Secretarias::aprobarProyecto($proyectoId, $bpin, $observaciones, $usuarioId);
      echo json_encode($result);
      break;

  case 'rechazar_proyecto_secretaria':
      include '../classes/Ingreso_proyectos_secretarias.php';
      $proyectoId = $_REQUEST['id'] ?? null;
      $observaciones = $_REQUEST['observaciones'] ?? '';
      $usuarioId = $_SESSION['session_user']['id'] ?? null;
      $result = Proyectos_Secretarias::rechazarProyecto($proyectoId, $observaciones, $usuarioId);
      echo json_encode($result);
      break;

  //guarda la anotacion del secretario de planeacion
  case 'guardar_anotacion_secretaria':
      include '../classes/Ingreso_proyectos_secretarias.php';
      $proyectoId = $rqst['id'] ?? null;
      $observaciones = $rqst['observaciones'] ?? '';
      $usuario_id = $_SESSION['session_user']['id'] ?? null;
      echo json_encode(Proyectos_Secretarias::guardarAnotacionSecretaria($proyectoId, $observaciones, $usuario_id));
      break;

  //ver todo el proyecto en el modal a detalles
  case 'obtener_detalles_proyecto':
      include '../classes/Ingreso_proyectos_secretarias.php';
      $proyectoId = (int)($_POST['id'] ?? $_REQUEST['id'] ?? 0);
      $guard = Proyectos_Secretarias::assertPuedeVerProyecto($proyectoId);
      if (!$guard['ok']) {
        echo json_encode(['output' => ['valid' => false, 'response' => ['content' => $guard['message']]]]);
        break;
      }
      echo json_encode(Proyectos_Secretarias::getDetallesProyecto($proyectoId));
      break;

  case 'obtener_logs_proyecto':
      include '../classes/Ingreso_proyectos_secretarias.php';
      $proyectoId = (int)($_REQUEST['id'] ?? 0);
      $guard = Proyectos_Secretarias::assertPuedeVerProyecto($proyectoId);
      if (!$guard['ok']) {
        echo json_encode(['output' => ['valid' => false, 'response' => ['content' => $guard['message']]]]);
        break;
      }
      $result = Proyectos_Secretarias::obtenerLogsProyecto($proyectoId);
      echo json_encode($result);
      break;

  case 'editar_proyecto_rechazado':
      include '../classes/Ingreso_proyectos_secretarias.php';
      echo json_encode(Proyectos_Secretarias::editarProyectoRechazado($_REQUEST));
      break;

  case 'gestionar_proyecto_planeacion':
      include '../classes/Ingreso_proyectos_secretarias.php';
      echo json_encode(Proyectos_Secretarias::gestionarProyecto($_REQUEST, $_FILES));
      break;

  case 'reabrir_proyecto_planeacion':
      include '../classes/Ingreso_proyectos_secretarias.php';
      echo json_encode(Proyectos_Secretarias::reabrirProyecto($_REQUEST));
      break;

  case 'dashboard_proyectos_planeacion_stats':
      include '../classes/Ingreso_proyectos_secretarias.php';
      echo json_encode(Proyectos_Secretarias::getDashboardStats($_REQUEST));
      break;

  case 'planeacion_usuarios_asignables':
      include '../classes/Ingreso_proyectos_secretarias.php';
      echo json_encode(Proyectos_Secretarias::getUsuariosAsignables($_REQUEST));
      break;

  case 'planeacion_asignaciones_get':
      include '../classes/Ingreso_proyectos_secretarias.php';
      echo json_encode(Proyectos_Secretarias::getAsignacionesProyecto((int)($_REQUEST['proyecto_id'] ?? $_REQUEST['id'] ?? 0)));
      break;

  case 'planeacion_asignar_usuarios':
      include '../classes/Ingreso_proyectos_secretarias.php';
      echo json_encode(Proyectos_Secretarias::asignarUsuariosProyecto($_REQUEST));
      break;

  case 'planeacion_informes_gestion':
      include '../classes/Ingreso_proyectos_secretarias.php';
      echo json_encode(Proyectos_Secretarias::getInformesGestion($_REQUEST));
      break;

  case 'planeacion_usuarios_filtro':
      include '../classes/Ingreso_proyectos_secretarias.php';
      echo json_encode(Proyectos_Secretarias::getUsuariosFiltroListado($_REQUEST));
      break;

  // Rutas para el módulo: Bienes
  case 'bienesget':
      include '../classes/Bienes.php';
      echo json_encode(Bienes::getAll($rqst));
      break;

  case 'bienessave':
      include '../classes/Bienes.php';
      echo json_encode(Bienes::save($rqst));
      break;

  case 'bienesdelete':
      include '../classes/Bienes.php';
      echo json_encode(Bienes::delete($rqst));
      break;

  case 'bienes_get_resumen':
      include '../classes/Bienes.php';
      $res = Bienes::getTotalBienes();
      echo json_encode($res);
      break;

  case 'deleteproyecto':
    include '../classes/Ministeriospro.php';
    echo json_encode(Ministeriospro::deleteProyectoVersionNormal($rqst));
    break;
  case 'actuaizarestadoproyecto':
    include '../classes/Ministeriospro.php';
    echo json_encode(Ministeriospro::actualizarEstadoProyecto($rqst));
    break;
  case 'leerproyectoyactualizarestado':
    include '../classes/Ministeriospro.php';
    echo json_encode(Ministeriospro::getAllProyectosSinLeer($rqst));
    break;
  case 'getTotalEjecucionSecretaria':
    include '../classes/Secretarias.php';
    echo json_encode(Secretarias::getTotalEjecucionSecretaria($rqst));
    break;
  case 'getResumenMunicipiosPae':
    include '../classes/PaeArcgis.php';
    echo json_encode(PaeArcgis::getResumenMunicipios($rqst));
    break;
  case 'getSedesInformacionMapaPae':
    include '../classes/Mapa.php';
    echo json_encode(Mapa::getMapaSedesPAE($rqst));
    break;
  case 'getDatosRectorByInstitucionYSede':
    include '../classes/SedesEducativas.php';
    echo json_encode(SedesEducativas::getDatosRectorByInstitucionYSede($rqst));
    break;

  case 'getInstitucionesByCodigoMunicipio':
    include '../classes/SedesEducativas.php';
    echo json_encode(SedesEducativas::getInstitucionesByMunicipio($rqst));
    break;

  case 'sedeEducativasConProblemas':
    include '../classes/SedesEducativas.php';
    echo json_encode(SedesEducativas::getSedesEducativasConProblemas($rqst));
    break;
  case 'getSedeEducativa':
    include '../classes/SedesEducativas.php';
    echo json_encode(SedesEducativas::getSedeEducativaDatos($rqst));
    break;
  case 'getSedeEducativaByCodMunicipio':
    include '../classes/SedesEducativas.php';
    echo json_encode(SedesEducativas::getSedeEducaticasByCodMunicipio($rqst));
    break;
  case 'getFactoresPorMunicipiosByPilarIdPorColores':
    include '../classes/Colombia.php';
    echo json_encode(Colombia::calcularColorDelDepartamentoByPilarId($rqst));
    break;

  // Llamados municipios por provincia
  case 'municipiosbyprovincia':
    include '../classes/Provincias.php';
    echo json_encode(Provincias::getMunicipiosByProvincia($rqst));
    break;

  case 'graficasPorFatoresVeredasPorColor':
    include '../classes/Colombia.php';
    echo json_encode(Colombia::calcularColorPorVeredasGeneralByPilarId($rqst));
    break;
  case 'pms_getconf':
    include '../classes/Configuracion.php';
    echo json_encode(Configuracion::getAll($rqst));
    break;
  case 'pms_confsave':
    include '../classes/Configuracion.php';
    echo json_encode(Configuracion::save($rqst));
    break;

  //llamado AJAX linea
  case 'getlinea':
    include '../classes/Linea.php';
    echo json_encode(Linea::getAll($rqst));
    break;
  case 'savelinea':
    include '../classes/Linea.php';
    echo json_encode(Linea::save($rqst));
    break;
  case 'gettic':
    include_once '../classes/PcTic.php';
    echo json_encode(PcTic::getAll($rqst));
    break;

  case 'savetic':
    // file_put_contents('debug_log.txt', print_r($rqst, true)); // DEBUG TEMPORAL
    include_once '../classes/PcTic.php';
    echo json_encode(PcTic::save($rqst));
    break;

  //llamado AJAX estrategia
  case 'getestrategia':
    include '../classes/Estrategia.php';
    echo json_encode(Estrategia::getAll($rqst));
    break;
  case 'estrategiasave':
    include '../classes/Estrategia.php';
    echo json_encode(Estrategia::save($rqst));
    break;

  //Llamados AJAX Mapa
  case 'getmapainformacionsecretaria':
    include '../classes/Mapa.php';
    echo json_encode(Mapa::getInformacionSecretariaEnMapaGoogle($rqst));
    break;
  case 'getmapainformacionalcaldia':
    include '../classes/Mapa.php';
    echo json_encode(Mapa::getInformacionAlcaldiaEnMapaGoogle($rqst));
    break;
  case 'getmapafactores':
    include '../classes/Mapa.php';
    echo json_encode(Mapa::getMapaByFactores($rqst));
    break;
  case 'getmapapilaresbymunicipioId':
    include '../classes/Mapa.php';
    echo json_encode(Mapa::getMapaByPilarByMunicipioId($rqst));
    break;
  case 'getAllByMunicipio':
    include '../classes/Mapa.php';
    echo json_encode(Mapa::getAllByMunicipio($rqst));
    break;
  //Llamados AJAX Plan de Desarrollo
  case 'update_avance':
    include '../classes/PlanDesarrollo.php';
    echo json_encode(PlanDesarrollo::save($rqst));
    break;
  //Llamados AJAX Actualizacion Ingreso de Información
  case 'actualizacioninformacionsave':
    include '../classes/ActualizacionInformacion.php';
    echo json_encode(ActualizacionInformacion::save($rqst));
    break;

  //Llamados AJAX Actualizacion Ingreso de Información
  case 'actualizacionestrategicossave':
    include '../classes/ActualizacionEstrategicos.php';
    echo json_encode(ActualizacionEstrategicos::save($rqst));
    break;


  //Llamados AJAX para guardar el pae
  case 'savepaedan':
    include '../classes/IngresoPae.php';
    echo json_encode(IngresoPae::save($rqst));
    break;

  //Llamados AJAX para obtener datos de PAE desde ArcGIS
  case 'getDataArcgisPae':
    include '../classes/PaeArcgis.php';
    echo json_encode(PaeArcgis::getDataFromArcgis($rqst));
    break;

  case 'getMunicipiosArcgis':
    include '../classes/PaeArcgisMunicipios.php';
    echo json_encode(PaeArcgisMunicipios::getMunicipiosDisponibles());
    break;

  // Proyectos RPC - API externa JSON-RPC
  case 'getProyectosRpc':
    include '../classes/ProyectosRpc.php';
    echo json_encode(ProyectosRpc::consultarProyectos($rqst));
    break;

  case 'getResumenProyectosRpc':
    include '../classes/ProyectosRpc.php';
    echo json_encode(ProyectosRpc::getResumenProyectos($rqst));
    break;

  case 'getDependenciasRpc':
    include '../classes/ProyectosRpc.php';
    echo json_encode(ProyectosRpc::getDependencias($rqst));
    break;

  // Logs API RPC - Consultar logs
  case 'getLogsApiRpc':
    include '../classes/LogApiRpc.php';
    echo json_encode(LogApiRpc::getLogs($rqst));
    break;

  case 'getLogDetalleApiRpc':
    include '../classes/LogApiRpc.php';
    echo json_encode(LogApiRpc::getLogDetalle($rqst));
    break;

  case 'getEstadisticasLogsApiRpc':
    include '../classes/LogApiRpc.php';
    echo json_encode(LogApiRpc::getEstadisticas($rqst));
    break;

  case 'limpiarLogsApiRpc':
    include '../classes/LogApiRpc.php';
    echo json_encode(LogApiRpc::limpiarLogs($rqst));
    break;

  // Logs API PAE ArcGIS - Consultar logs
  case 'getLogsPaeArcgis':
    include '../classes/LogApiPaeArcgis.php';
    echo json_encode(LogApiPaeArcgis::getLogs($rqst));
    break;

  case 'getLogDetallePaeArcgis':
    include '../classes/LogApiPaeArcgis.php';
    echo json_encode(LogApiPaeArcgis::getLogDetalle($rqst));
    break;

  case 'getEstadisticasLogsPaeArcgis':
    include '../classes/LogApiPaeArcgis.php';
    echo json_encode(LogApiPaeArcgis::getEstadisticas($rqst));
    break;

  case 'limpiarLogsPaeArcgis':
    include '../classes/LogApiPaeArcgis.php';
    echo json_encode(LogApiPaeArcgis::limpiarLogs($rqst));
    break;

  //Llamados AJAX Ingreso de Información
  case 'ingresoinformacionsave':
    include '../classes/IngresoInformacion.php';
    echo json_encode(IngresoInformacion::save($rqst));
    break;

  //Llamados AJAX Ingreso de InformaciónEstrategios
  case 'ingresoestrategicossave':
    include '../classes/IngresoEstrategicos.php';
    echo json_encode(IngresoEstrategicos::save($rqst));
    break;
  //Llamados AJAX Puntaje
  case 'getPuntaje':
    include '../classes/Configuracion_Puntaje.php';
    echo json_encode(Configuracion_Puntaje::getAll($rqst));
    break;
  case 'configuracionpuntajesave':
    include '../classes/Configuracion_Puntaje.php';
    echo json_encode(Configuracion_Puntaje::save($rqst));
    break;
  case 'empresassave':
    include '../classes/Empresas.php';
    echo json_encode(Empresas::save($rqst));
    break;
  case 'empresasdelete':
    include '../classes/Empresas.php';
    echo json_encode(Empresas::delete($rqst));
    break;
  case 'empresafactorgetbyfactor':
    include '../classes/EmpresaFactor.php';
    echo json_encode(EmpresaFactor::getByFactor($rqst));
    break;
  case 'empresafactorgetbyempresa':
    include '../classes/EmpresaFactor.php';
    echo json_encode(EmpresaFactor::getByEmpresa($rqst));
    break;
  case 'empresafactorget':
    include '../classes/EmpresaFactor.php';
    echo json_encode(EmpresaFactor::getById($rqst));
    break;
  case 'empresafactorsave':
    include '../classes/EmpresaFactor.php';
    echo json_encode(EmpresaFactor::save($rqst));
    break;
  case 'empresafactordelete':
    include '../classes/EmpresaFactor.php';
    echo json_encode(EmpresaFactor::delete($rqst));
    break;
  //Llamados AJAX Puntaje Secretaria
  case 'getPuntajeSecretaria':
    include '../classes/ConfiguracionPuntajeSecretaria.php';
    echo json_encode(ConfiguracionPuntajeSecretaria::getAll($rqst));
    break;
  case 'configuracionpuntajesecretariasave':
    include '../classes/ConfiguracionPuntajeSecretaria.php';
    echo json_encode(ConfiguracionPuntajeSecretaria::save($rqst));
    break;
  //Llamados AJAX Factores
  case 'factoressave':
    include '../classes/Factores.php';
    echo json_encode(Factores::save($rqst));
    break;
  case 'getFactores':
    include '../classes/Factores.php';
    echo json_encode(Factores::getAll($rqst));
    break;
  case 'getFactoresDelete':
    include '../classes/Factores.php';
    echo json_encode(Factores::delete($rqst));
    break;
  //Llamados AJAX Factores Inestabilidad Gobernacion
  case 'factoresInestabilidadSave':
    include '../classes/FactoresInestabilidadGobernacion.php';
    echo json_encode(FactoresInestabilidadGobernacion::save($rqst));
    break;
  case 'getFactoresInestabilidad':
    include '../classes/FactoresInestabilidadGobernacion.php';
    echo json_encode(FactoresInestabilidadGobernacion::getAll($rqst));
    break;
  case 'getFactoresInestabilidadDelete':
    include '../classes/FactoresInestabilidadGobernacion.php';
    echo json_encode(FactoresInestabilidadGobernacion::delete($rqst));
    break;
  case 'factoresMassUpdateInestabilidad':
    include '../classes/Factores.php';
    echo json_encode(Factores::massUpdateInestabilidad($rqst));
    break;
  case 'factoresMassUpdateSecretaria':
    include '../classes/Factores.php';
    echo json_encode(Factores::massUpdateSecretaria($rqst));
    break;
  case 'getFactoresInestabilidadList':
    include '../classes/Factores.php';
    echo json_encode(Factores::getInestabilidadOptions());
    break;
  //Llamados AJAX Pilar
  case 'getPilar':
    include '../classes/Pilar.php';
    echo json_encode(Pilar::getAll($rqst));
    break;
  //Llamados AJAX Area
  case 'getArea':
    include '../classes/Area.php';
    echo json_encode(Area::getAll($rqst));
    break;
  case 'savearea':
    include '../classes/Area.php';
    echo json_encode(Area::save($rqst));
    break;

  //Llamados AJAX GestoraSocial
  case 'getTotalDetalladoTipoActividad':
    include '../classes/GestoraSocial.php';
    echo json_encode(GestoraSocial::getTotalDetalladoTipoActividad($rqst));
    break;

  case 'getPoblacionImpactadaPorMunicipio':
    include '../classes/GestoraSocial.php';
    echo json_encode(GestoraSocial::getPoblacionImpactadaPorMunicipio($rqst));
    break;

  //Llamados AJAX Main
  case 'gettotalvisitasporprovincia':
    include '../classes/Main.php';
    echo json_encode(Main::getTotalVisitasPorProvinciasPorAnios($rqst));
    break;
  case 'gettotalvisitaspormesmunicipio':
    include '../classes/Main.php';
    echo json_encode(Main::getTotalVisitasPorMesAMunicipios($rqst));
    break;
  // ✅ SOLO VISITAS POR PROVINCIA (2024-2025)
case 'getTotalVisitasPorProvincias':
    include '../classes/Main.php';
    echo json_encode(Main::getTotalVisitasPorProvinciasPorAnios($rqst));
    break;

// ✅ SOLO VISITAS POR MES (2024-2025)
case 'getSoloVisitasPorMes':
    include '../classes/Main.php';
    echo json_encode(Main::getTotalVisitasPorMesAMunicipios($rqst));
    break;

// ========== ENDPOINTS PARA ALCALDE ==========

// ✅ VISITAS POR VEREDAS ALCALDE (2024-2025)
case 'getTotalVisitasPorVeredasAlcalde':
    include '../classes/MainAlcalde.php';
    echo json_encode(MainAlcalde::getTotalVisitasPorVeredas($rqst));
    break;

// ✅ VISITAS POR MES ALCALDE (2024-2025)
case 'getSoloVisitasPorMesAlcalde':
    include '../classes/MainAlcalde.php';
    echo json_encode(MainAlcalde::getTotalVisitasPorMesAMunicipios($rqst));
    break;

// ✅ VISITAS POR MUNICIPIOS ALCALDE - PARA SUPERADMIN (2024-2025)
case 'getTotalVisitasPorMunicipiosAlcalde':
    include '../classes/MainAlcalde.php';
    echo json_encode(MainAlcalde::getTotalVisitasPorMunicipios($rqst));
    break;

// ========== FIN ENDPOINTS ALCALDE ==========

  //Grafica promedio secretaria
  case 'getpromediops2025porsecretaria':
    include '../classes/Main.php';
    echo json_encode(Main::getPromedioPs2025PorSecretaria($rqst));
    break;


  //Llamados AJAX Permisos
  case 'pms_usrpermission':
    include '../classes/Permiso.php';
    echo json_encode(Permiso::permisos($rqst));
    break;

  case 'pms_usrsavepermission':
    include '../classes/Permiso.php';
    echo json_encode(Permiso::savePermisos($rqst));
    break;

  // ── Roles y permisos (RBAC) ──────────────────────────────────────────
  case 'roleslist':
    include '../classes/Role.php';
    echo json_encode(Role::getAll($rqst));
    break;

  case 'roleget':
    include '../classes/Role.php';
    echo json_encode(Role::getById($rqst));
    break;

  case 'rolesave':
    include '../classes/Role.php';
    echo json_encode(Role::save($rqst));
    break;

  case 'roledelete':
    include '../classes/Role.php';
    echo json_encode(Role::delete($rqst));
    break;

  case 'rolepermissionscatalog':
    include '../classes/Role.php';
    echo json_encode(Role::getPermissionsCatalog());
    break;

  case 'pms_usrlogin':
    include '../classes/Usuario.php';
    echo json_encode(Usuario::login($rqst));
    break;
  case 'acttualizarPerfil':
    include '../classes/Usuario.php';
    echo json_encode(Usuario::acttualizarPerfil($rqst));
    break;


  case 'gestionarimagen_save':
    include '../classes/Galeria.php';
    echo json_encode(Gellery::save($rqst, $_FILES));
    break;

  case 'pms_showimage':
    include '../classes/Galeria.php';
    echo json_encode(Gellery::getAll($rqst));
    break;

  case 'pms_deleteimage':
    include '../classes/Galeria.php';
    echo json_encode(Gellery::deleteFile($rqst));
    break;

  //Llamados AJAX Usuario
  case 'pms_usrsave':
    // Util::verify_user_app_access();
    include '../classes/Usuario.php';
    echo json_encode(Usuario::save($rqst));
    break;

  case 'pms_usrget':
    // Util::verify_user_app_access();
    include '../classes/Usuario.php';
    echo json_encode(Usuario::getAll($rqst));
    break;

  case 'pms_usrdelete':
    // Util::verify_user_app_access();
    include '../classes/Usuario.php';
    echo json_encode(Usuario::delete($rqst));
    break;

  case 'pms_usrenable':
    // Util::verify_user_app_access();
    include '../classes/Usuario.php';
    echo json_encode(Usuario::enable($rqst));
    break;

  case 'pms_usravailable':
    // Util::verify_user_app_access();
    include '../classes/Usuario.php';
    echo json_encode(Usuario::available($rqst));
    break;
  // Fin Llamados AJAX Usuario


  // Llamados de Brigadas
  case 'brigadasave':
    // Util::verify_user_app_access();
    include '../classes/Brigada.php';
    echo json_encode(Brigada::save($rqst));
    break;

  case 'getveredasbycolor':
    // Util::verify_user_app_access();
    include '../classes/Brigada.php';
    echo json_encode(Brigada::getVeredasPorColorSeptimaBrigada($rqst));
    break;

  case 'getveredasbycolorbrigada2021':
    // Util::verify_user_app_access();
    include '../classes/Brigada.php';
    echo json_encode(Brigada::getVeredasPorColorSeptimaBrigada2021($rqst));
    break;

  case 'getveredasbycolorbatallon':
    // Util::verify_user_app_access();
    include '../classes/EstadoBatallon.php';
    echo json_encode(EstadoBatallon::getVeredasPorColorBatallon($rqst));
    break;

  case 'getveredasbycolorbatallon2021':
    // Util::verify_user_app_access();
    include '../classes/EstadoBatallon.php';
    echo json_encode(EstadoBatallon::getVeredasPorColorBatallon2021($rqst));
    break;

  case 'getveredasbycolor_depart':
    // Util::verify_user_app_access();
    include '../classes/Brigada.php';
    echo json_encode(Brigada::getVeredasPorColorSeptimaBrigadaDepartamentoId($rqst));
    break;

  case 'getveredasbycolor_depart2021':
    // Util::verify_user_app_access();
    include '../classes/Brigada.php';
    echo json_encode(Brigada::getVeredasPorColorSeptimaBrigadaDepartamentoId2021($rqst));
    break;

  case 'getveredasbycolor_munic':
    include '../classes/Ciudad.php';
    echo json_encode(Ciudad::getVeredasPorColorCiudadId($rqst));
    break;
  case 'getveredasbycolor_munic2021':
    include '../classes/Ciudad.php';
    echo json_encode(Ciudad::getVeredasPorColorCiudadId2021($rqst));
    break;

  case 'brigadaget':
    // Util::verify_user_app_access();
    include '../classes/Brigada.php';
    echo json_encode(Brigada::getAll($rqst));
    break;

  // Llamados de reportes fe en colombia
  case 'reportefesave':

    include '../classes/Reportefe.php';
    // Util::verify_user_app_access();
    echo json_encode(Reportefe::save($rqst, $_FILES));
    break;

  case 'reportefeget':
    // Util::verify_user_app_access();
    include '../classes/Reportefe.php';
    echo json_encode(Reportefe::getAll($rqst));
    break;

  // Llamados de operatividad
  case 'operatividadsave':
    include '../classes/Operatividad.php';
    echo json_encode(Operatividad::save($rqst));
    break;

  case 'operatividadeget':
    // Util::verify_user_app_access();
    include '../classes/Operatividad.php';
    echo json_encode(Operatividad::getAll($rqst));
    break;

  case 'operatividadupdate':
    // Util::verify_user_app_access();
    include '../classes/Operatividad.php';
    echo json_encode(Operatividad::update($rqst));
    break;

  // Llamados casos de inestabilidad social
  case 'socialessave':
    // Util::verify_user_app_access();
    include '../classes/Sociales.php';
    echo json_encode(Sociales::save($rqst));
    break;

  case 'socialget':
    // Util::verify_user_app_access();
    include '../classes/Sociales.php';
    echo json_encode(Sociales::getAll($rqst));
    break;


  // Llamados casos de inestabilidad ECONOMICO
  case 'economicosave':
    // Util::verify_user_app_access();
    include '../classes/Economico.php';
    echo json_encode(Economico::save($rqst));
    break;

  case 'economicoget':
    // Util::verify_user_app_access();
    include '../classes/Economico.php';
    echo json_encode(Economico::getAll($rqst));
    break;

  // Llamados casos de inestabilidad armada
  case 'armadasave':
    // Util::verify_user_app_access();
    include '../classes/Armada.php';
    echo json_encode(Armada::save($rqst));
    break;

  case 'armadaget':
    // Util::verify_user_app_access();
    include '../classes/Armada.php';
    echo json_encode(Armada::getAll($rqst));
    break;

  // Llamados Secretarias
  case 'secretariasave':
    // Util::verify_user_app_access();
    include '../classes/Secretarias.php';
    echo json_encode(Secretarias::save($rqst));
    break;

  case 'secretariaget':
    // Util::verify_user_app_access();
    include '../classes/Secretarias.php';
    echo json_encode(Secretarias::getAll($rqst));
    break;

  // Llamados Secretarias
  case 'ministeriosave':
    // Util::verify_user_app_access();
    include '../classes/Ministerios.php';
    echo json_encode(Ministerios::save($rqst));
    break;

  case 'ministerioget':
    // Util::verify_user_app_access();
    include '../classes/Ministerios.php';
    echo json_encode(Ministerios::getAll($rqst));
    break;

  // Llamados municipios
  case 'ciudadget':
    // Util::verify_user_app_access();
    include '../classes/Ciudad.php';
    echo json_encode(Ciudad::getAll($rqst));
    break;

  // Obtener secretarías municipales por código de municipio
  case 'secretariasmunicipalespormunicipio':
    include '../classes/SecretariasMunicipios.php';
    echo json_encode(SecretariasMunicipios::getByMunicipio($rqst));
    break;

  case 'metas_plan_desarrollo_por_municipio':
    include '../classes/DesarrolloAlcalde.php';
    echo json_encode(DesarrolloAlcalde::getByMunicipio($rqst));
    break;

  // Llamados veredas
  case 'veredaget':
    // Util::verify_user_app_access();
    include '../classes/Vereda.php';
    echo json_encode(Vereda::getAll($rqst));
    break;

  case 'getVeredasByMunicipioId':
    include '../classes/Vereda.php';
    echo json_encode(Vereda::getAll($rqst));
    break;

  case 'upd_descrip_vereda':
    include '../classes/Vereda.php';
    echo json_encode(Vereda::updateDescripcionVereda($rqst));
    break;

  // Candidatos


  case 'pms_candidatosave':
    include '../classes/Candidatos.php';
    echo json_encode(Candidatos::save($rqst));
    break;

  case 'pms_candidatoget':
    include '../classes/Candidatos.php';
    echo json_encode(Candidatos::getAll($rqst));
    break;

  //PLAN DE DESARROLLO
  case 'pms_desarrollo_get':
    include '../classes/Desarrollo.php';
    echo json_encode(Desarrollo::getAll($rqst));
    break;


  case 'pms_desarrollo_save':
    include '../classes/Desarrollo.php';
    echo json_encode(Desarrollo::save($rqst));
    break;

  case 'pms_desarrollo_filtar_secretaria':
    include '../classes/Desarrollo.php';
    echo json_encode(Desarrollo::setFiltroSecretariaById($rqst));
    break;


  case 'data_map':
    // Util::verify_user_app_access();
    include '../classes/Mapa.php';
    echo json_encode(Mapa::getAll($rqst));
    break;

  // Llamados Factores por veredas
  case 'getfactores':
    // Util::verify_user_app_access();
    include '../classes/Estado.php';
    echo json_encode(Estado::getEstadoFactorArmadoSocialEcon($rqst));
    break;

  // Llamados Factores por municipios
  case 'getfactoresbymunic':
    // Util::verify_user_app_access();
    include '../classes/Estado.php';
    echo json_encode(Estado::getEstadoFactorArmadoSocialEconByMunicipio($rqst));
    break;

  case 'getfactoresbymunicNUEVO':
    include '../classes/Estado.php';
    echo json_encode(Estado::getEstadoFactorArmadoSocialEconByMunicipioNUEVO($rqst));
    // echo json_encode(EstadoMunicipio::getEstadoMunicipio($rqst));
    break;


  case 'getfactoresbymunicVersion2022':
    include '../classes/Ciudad.php';
    include '../classes/Estado.php';
    echo json_encode(Ciudad::getFactoresInestabilidadPorCiudad($rqst));
    break;

  //llamados de proyectos
  case 'hacienda_save':
    include '../classes/Hacienda.php';
    echo json_encode(Hacienda::save($rqst));
    break;

  case 'hacienda_get_consolidado':
    include '../classes/Hacienda.php';
    echo json_encode(Hacienda::getConsolidadoHacienda($rqst));
    break;

  case 'hacienda_update':
    include '../classes/Hacienda.php';
    echo json_encode(Hacienda::update($rqst));
    break;


  case 'ingresoproyectos_save':
    include '../classes/Proyectos.php';
    echo json_encode(Proyectos::save($rqst));
    break;

  // =====================================================
  // Rutas para el módulo: Proyectos de Alcaldías
  // =====================================================
  case 'proyectosalcaldias_getall':
    include '../classes/ProyectosAlcaldias.php';
    echo json_encode(ProyectosAlcaldias::getAll($rqst));
    break;

  case 'proyectosalcaldias_save':
    include '../classes/ProyectosAlcaldias.php';
    echo json_encode(ProyectosAlcaldias::save($rqst));
    break;

  case 'proyectosalcaldias_delete':
    include '../classes/ProyectosAlcaldias.php';
    echo json_encode(ProyectosAlcaldias::delete($rqst));
    break;

  case 'proyectos_alcaldias_update':
    include '../classes/ProyectosAlcaldias.php';
    echo json_encode(ProyectosAlcaldias::updateProyecto($rqst));
    break;

  case 'proyectosalcaldias_inversion_total':
    include '../classes/ProyectosAlcaldias.php';
    echo json_encode(ProyectosAlcaldias::getInversionTotal($rqst));
    break;

  case 'proyectosalcaldias_inversion_secretaria':
    include '../classes/ProyectosAlcaldias.php';
    echo json_encode(ProyectosAlcaldias::getInversionBySecretaria($rqst));
    break;

  case 'proyectosalcaldias_observaciones':
    include '../classes/ProyectosAlcaldias.php';
    echo json_encode(ProyectosAlcaldias::getObservacionesByProyectoId($rqst['id']));
    break;

  case 'proyectosalcaldias_provincias':
    include '../classes/MainProyectosAlcalde.php';
    echo json_encode(MainProyectosAlcalde::getProvincias($rqst));
    break;


  case 'pms_getproyerctos':
    include '../classes/Proyectos.php';
    echo json_encode(Proyectos::getAll($rqst));
    break;


  case 'ingresoproyectos4_save':
    include '../classes/Proyectos4.php';
    echo json_encode(Proyectos4::save($rqst));
    break;

  case 'pms_getproyerctos4':
    include '../classes/Proyectos4.php';
    echo json_encode(Proyectos4::getAll($rqst));
    break;

  case 'ingresoproyectosmin_save':
    include '../classes/Ministeriospro.php';
    echo json_encode(Ministeriospro::save($rqst));
    break;

  case 'editarInformacionProyecto':
    include '../classes/Ministeriospro.php';
    echo json_encode(Ministeriospro::editarInformacionProyecto($rqst));
    break;

  case 'pms_getproyerctosmin':
    include '../classes/Ministeriospro.php';
    echo json_encode(Ministeriospro::getAll($rqst));
    break;


  // ============== LLAMADOS COMPROMISOS=================
  case 'pms_compromisoget':
    include '../classes/Compromisos.php';
    echo json_encode(Compromisos::getAll($rqst));
    break;

  case 'pms_compromisos':
    include '../classes/Compromisos.php';
    echo json_encode(Compromisos::save($rqst));
    break;

  // Llamados Información
  case 'saveinfo':
    // Util::verify_user_app_access();
    include '../classes/Informacion.php';
    include '../classes/Estado.php';
    echo json_encode(Informacion::save($rqst));
    break;
  // Llamados Actualizar Información
  case 'updateinfo':
    // Util::verify_user_app_access();
    include '../classes/ActualizarInformacion.php';
    echo json_encode(ActualizarInformacion::save($rqst, $_FILES));
    break;

  case 'getresultadosmunicipio':
    include '../classes/Resultados.php';
    echo json_encode(Resultados::getAll($rqst));
    break;

  case 'getresultadosvereda':
    include '../classes/Resultados.php';
    echo json_encode(Resultados::getAllVeredaId($rqst));
    break;

  case 'get_veredas_by_nivel':
    // include '../classes/Resultados.php';
    include '../mapa-veredas/veredas.php';
    break;

  case 'get_munic_x_brigadas':
    include '../classes/EstadoBrigada.php';
    echo json_encode(EstadoBrigada::getMunicipiosXBrigada($rqst));
    break;

  case 'get_munic_x_batallon':
    include '../classes/EstadoBatallon.php';
    echo json_encode(EstadoBatallon::getMunicipiosXBatallon($rqst));
    break;

  case 'get_solo_munic_x_batallon':
    include '../classes/EstadoBatallon.php';
    echo json_encode(EstadoBatallon::getSoloMunicipiosByBatallonById($rqst));
    break;

  case 'get_solo_vereda_x_batallon':
    include '../classes/EstadoBatallon.php';
    echo json_encode(EstadoBatallon::getSoloVeredasByBatallonById($rqst));
    break;

  case 'ingresovotaciones_save':
    include '../classes/Votaciones.php';
    echo json_encode(Votaciones::save($rqst));
    break;
  case 'pms_getvotacion':
    include '../classes/Votaciones.php';
    echo json_encode(Votaciones::getAll($rqst));
    break;
  case 'pms_votacionupdate':
    include '../classes/Votaciones.php';
    echo json_encode(Votaciones::update($rqst));
    break;

  case 'spi_actores_save':
    include '../classes/Actores.php';
    echo json_encode(Actores::save($rqst));
    break;

  case 'spi_actores_get':
    include '../classes/Actores.php';
    echo json_encode(Actores::getAll($rqst));
    break;
  /*     metodo para traer los actores por alcaldia */
  case 'getByAlcaldia':
    include '../classes/Actores.php';
    echo json_encode(Actores::getByAlcaldia($rqst));
    break;

  case 'ingresoactores_save':
    include '../classes/IngresoActores.php';
    echo json_encode(IngresoActores::save($rqst, $_FILES));
    break;

  case 'ingresoactoresreg_save':
    include '../classes/IngresoActoresRegion.php';
    echo json_encode(IngresoActoresRegion::save($rqst, $_FILES));
    break;

  // Llamados actores
  case 'actoresget':
    include '../classes/IngresoActores.php';
    echo json_encode(IngresoActores::getAll($rqst));
    break;


  case 'get_graficos':
    include '../classes/Grafico.php';
    echo json_encode(Grafico::getData($rqst));
    break;

  case 'getGraficoTemaInteres':
    include '../classes/Grafico2022.php';
    echo json_encode(Grafico2022::getData($rqst));
    break;

  case 'calcularPuntajeDepartamento':
    include '../classes/Puntaje.php';
    include '../classes/Estado.php';
    include '../classes/EstadoDepartamento.php';
    echo json_encode(Puntaje::calcularPuntajeDepartamento($rqst));
    break;

  case 'calcularPuntajeBrigada':
    include '../classes/Puntaje.php';
    include '../classes/EstadoBrigada.php';
    echo json_encode(Puntaje::calcularPuntajeBrigada($rqst));
    break;

  case 'get_veredas_criticas':
    include '../classes/Vereda.php';
    echo json_encode(Vereda::getVeredasCriticasByBatallonIdOrByBrigadaId($rqst));
    break;

  // obtener los registros relacionado a la consulta de actualizacion
  case 'obtener_registros_existentes':
    include '../classes/ActualizacionInformacion.php';
    echo json_encode(ActualizacionInformacion::obtenerRegistros($rqst));
    break;
  case 'factor_actualizacion_detalle':
    include '../classes/ActualizacionInformacion.php';
    echo json_encode(ActualizacionInformacion::getDetalleActualizacionFactorMunicipio($rqst));
    break;
  // CONSULTA PARA VEREDAS CRITICAS
  case 'getVeredasCriticas':
    include '../classes/Vereda.php';
    echo json_encode(Vereda::veredasCriticasCONSULTA($rqst));
    break;
  case 'getPuntajesInestabilidad':
    include '../classes/FactoresInestabilidadGeneral.php';
    $inestabilidadId = isset($rqst['inestabilidad']) ? intval($rqst['inestabilidad']) : 0;
    if ($inestabilidadId <= 0) {
      echo json_encode(Util::error_missing_data());
      break;
    }
    echo json_encode([
      'output' => [
        'valid' => true,
        'response' => [
          'inicial' => FactoresInestabilidadGeneral::getPuntajes(
            $inestabilidadId,
            FactoresInestabilidadGeneral::TIPO_PUNTAJE_INICIAL
          ),
          'actual' => FactoresInestabilidadGeneral::getPuntajes(
            $inestabilidadId,
            FactoresInestabilidadGeneral::TIPO_PUNTAJE_FINAL
          ),
        ],
      ],
    ]);
    break;
  case 'getFactoresVereda':
    include '../classes/Vereda.php';
    $response = Vereda::getFactoresPorVereda($_REQUEST['veredaId']);
    header('Content-Type: application/json; charset=UTF-8');
    echo json_encode($response, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    break;




  case 'get_veredas_criticas_data_basica':
    include '../classes/Vereda.php';
    echo json_encode(Vereda::getSoloInformacionVeredasCriticasV2($rqst));
    break;

  case 'get_veredas_criticas_seleccion':
    include '../classes/Vereda.php';
    echo json_encode(Vereda::getVeredasSeleccionadasCriticasByBatallonIdOrByBrigadaId($rqst));
    break;

  // Automatizacion
  case 'automatizar_delet_munic':
    include '../classes/Automatizacion.php';
    echo json_encode(Automatizacion::eliminarInfoDeMunicioPorFactores($rqst));
    break;

  case 'automatizar_delet_vereda':
    include '../classes/Automatizacion.php';
    echo json_encode(Automatizacion::eliminarInfoDVeredaPorFactores($rqst));
    break;

  case 'get_veredas_sin_desplazamiento':
    include '../classes/Votaciones.php';
    echo json_encode(Votaciones::getVeredasSinDesplazamiento($rqst));
    break;

  case 'get_veredas_sin_desplazamiento_brigadas':
    include '../classes/Votaciones.php';
    echo json_encode(Votaciones::getVeredasSinDesplazamientoBrigada($rqst));
    break;


  //Llamados Ajax
  case 'saveprensa':
    include '../classes/Prensa.php';
    echo json_encode(Prensa::save($rqst, $_FILES));
    break;

  case 'getprensa':
    include '../classes/Prensa.php';
    echo json_encode(Prensa::getAll($rqst));
    break;

  //Llamados AJAX Lideres
  case 'pms_lidersave':
    include '../classes/Lideres.php';
    echo json_encode(Lideres::save($rqst));
    break;

  case 'pms_liderget':
    include '../classes/Lideres.php';
    echo json_encode(Lideres::getAll($rqst));
    break;



  case 'pms_lideravailable':
    include '../classes/Lideres.php';
    echo json_encode(Lideres::available($rqst));
    break;


  //llamados visitas municipios

  case 'pms_visitamunget':
    include '../classes/Detalle.php';
    echo json_encode(Detalle::getAll($rqst));
    break;

  case 'pms_cuentavisita':
    include '../classes/Cuenta.php';
    echo json_encode(Cuenta::getAll($rqst));
    break;

  case 'pms_cuentaprovincia':
    include '../classes/Cuentapro.php';
    echo json_encode(Cuentapro::getAll($rqst));
    break;


  //Llamados AJAX VISITAS MUNICIPIOS
  case 'pms_visitas':
    include '../classes/Visitas.php';
    echo json_encode(Visitas::save($rqst));
    break;

  case 'pms_visitasget':
    include '../classes/Visitas.php';
    echo json_encode(Visitas::getAll($rqst));
    break;


  case 'spi_visitasg_get':
    include '../classes/Visitasg.php';
    echo json_encode(Visitasg::getAll($rqst));
    break;

  case 'getPoblacionImpactadaPorMunicipioAspas':
    include '../classes/GestoraSocialAspas.php';
    echo json_encode(GestoraSocialAspas::getPoblacionImpactadaPorMunicipio($rqst));
    break;

  case 'spi_visitasg_get_aspas':
    include '../classes/VisitasgAspas.php';
    echo json_encode(VisitasgAspas::getAll($rqst));
    break;


  case 'spi_visitas_save':
    include '../classes/Visitasg.php';
    echo json_encode(Visitasg::save($rqst));
    break;

  case 'spi_visitas_save_aspas':
    include '../classes/VisitasgAspas.php';
    echo json_encode(VisitasgAspas::save($rqst));
    break;

  case 'spi_visitasg_save':
    include '../classes/Visitasg.php';
    echo json_encode(Visitasg::save($_POST));
    break;



  case 'spi_acciong_get':
    include '../classes/Acciong.php';
    echo json_encode(Acciong::getAll($rqst));
    break;

  case 'spi_acciong_save':
    include '../classes/Acciong.php';
    echo json_encode(Acciong::save($rqst, $_FILES));
    break;
  case 'spi_acciong_delete':
    include '../classes/Acciong.php';
    echo json_encode(Acciong::delete($rqst));
    break;
  case 'spi_acciong_get':
    include '../classes/Acciong.php';
    echo json_encode(Acciong::getAll($rqst));
    break;
  case 'savefiltros':
    include '../classes/Filtros.php';
    echo json_encode(Filtros::save($rqst));
    break;

  case 'deletefiltros':
    include '../classes/Filtros.php';
    echo json_encode(Filtros::delete($rqst));
    break;

  case 'getPersonasByFiltroId':
    include '../classes/Filtros.php';
    echo json_encode(Filtros::getPersonasByFiltroId($rqst));
    break;

  case 'actualizar_estadoverificado':
    include '../classes/Comentarios.php';
    echo json_encode(Comentarios::actualizarVerificacion($rqst));
    break;

  // Compromisos
  case 'guardarCompromiso':
    include '../classes/CompromisosFactorPilar.php';
    echo json_encode(CompromisosFactorPilar::guardarCompromiso($rqst));
    break;

  case 'getCompromisosFactores':
    include '../classes/CompromisosFactorPilar.php';
    echo json_encode(CompromisosFactorPilar::getCompromisosFactores($rqst));
    break;

  case 'editarInformacion':
    include '../classes/IngresoInformacion.php';
    echo json_encode(IngresoInformacion::save($rqst));
    break;

  case 'deleteingresoinformacion':
    include '../classes/IngresoInformacion.php';
    echo json_encode(IngresoInformacion::delete($rqst));
    break;

    
  //son los consolidados por factores
  case 'getConsolidadoFactores':
    include '../classes/Secretarias.php';
    include '../classes/Ciudad.php'; 

    $municipioId = $_POST['municipioId'] ?? 0;
    $secretariaId = $_POST['secretariaId'] ?? 0;
    $accion = $_POST['accion'] ?? '';

    $arr = [
        'municipioId' => $municipioId,
        'secretariaId' => $secretariaId,
        'accion' => $accion
    ];
    $response = Secretarias::consultarConsolidadoFactoresPorMunicipioAccion($arr);

    if (!$response['output']['valid']) {
        header('Content-Type: application/json');
        echo json_encode($response);
        exit;
    }
    
    $nombreMunicipio = 'Municipio Desconocido';
    if ($municipioId > 0) {
      $municipioInfo = Ciudad::getInformacionCiudad(['codigo_muncipio' => $municipioId]);
      $nombreMunicipio = $municipioInfo['output']['response'][0]['municipio'] ?? $nombreMunicipio;
    }

    header('Content-Type: application/json');
    echo json_encode([
        'valid' => $response['output']['valid'],
        'response' => $response['output']['response'],
        'nombreMunicipio' => $nombreMunicipio 
    ]);
    exit;
    break;


  case 'fetch_proyectos_por_estado':
      include '../classes/fetch_proyectos_por_estado.php';
      break;

  // ── Módulo Gestión de Veredas (Santander) ──────────────────────────────
  case 'veredas_municipios_santander':
      include '../classes/Vereda.php';
      echo json_encode(Vereda::getMunicipiosSantander());
      break;

  case 'veredas_get_admin':
      include '../classes/Vereda.php';
      echo json_encode(Vereda::getVeredasAdmin($rqst));
      break;

  case 'vereda_get_by_id':
      include '../classes/Vereda.php';
      echo json_encode(Vereda::getVeredaById($rqst));
      break;

  case 'vereda_preview_codigo':
      include '../classes/Vereda.php';
      echo json_encode(Vereda::previewCodigoVereda($rqst));
      break;

  case 'vereda_save':
      include '../classes/Vereda.php';
      echo json_encode(Vereda::saveVereda($rqst));
      break;

  case 'vereda_update':
      include '../classes/Vereda.php';
      echo json_encode(Vereda::updateVereda($rqst));
      break;

  // vereda_delete: eliminación deshabilitada (integridad referencial)
  // ───────────────────────────────────────────────────────────────────────

  case 'inversion_save':
      include '../classes/Inversion.php';
      $rqst['usuario_id'] = $_SESSION['session_user']['id'] ?? 1;
      echo json_encode(Inversion::save($rqst, $_FILES));
      break;

  case 'inversion_list':
      include '../classes/Inversion.php';
      echo json_encode(Inversion::getAll());
      break;

  case 'inversion_get':
      include '../classes/Inversion.php';
      echo json_encode(Inversion::getById($rqst));
      break;

  case 'inversion_update':
      include '../classes/Inversion.php';
      echo json_encode(Inversion::update($rqst, $_FILES));
      break;

  case 'inversion_delete':
      include '../classes/Inversion.php';
      echo json_encode(Inversion::delete($rqst));
      break;

  case 'get_consolidado_departamental':
      include '../classes/Colombia.php';
      $departamento = Util::getDepartamentoPrincipal();
      $pilar = isset($rqst['pilar']) ? intval($rqst['pilar']) : 10000;
      
      $arrInicial = [
          'codigo_departamento' => $departamento,
          'pilar' => $pilar
      ];
      $dataInicial = Colombia::consultarConsolidadPilaresFactoresDepartamental($arrInicial);
      
      $dataActual = Colombia::consultarConsolidadPilaresFactoresActualesDepartamental($arrInicial);
      
      echo json_encode([
          'valid' => $dataInicial['output']['valid'] ?? false,
          'tabs' => $dataInicial['output']['tabs'] ?? [],
          'response' => $dataInicial['output']['response'] ?? [],
          'responseActual' => $dataActual['output']['valid'] ? $dataActual['output']['response'] : [],
          'debug' => [
              'pilar' => $pilar,
              'departamento' => $departamento,
              'countInicial' => count($dataInicial['output']['response'] ?? []),
              'countActual' => count($dataActual['output']['response'] ?? [])
          ]
      ]);
      break;

  case 'get_consolidado_inestabilidad_departamental':
      include '../classes/FactoresInestabilidadGeneral.php';
      $departamento = Util::getDepartamentoPrincipal();
      $inestabilidadId = isset($rqst['inestabilidadId']) ? intval($rqst['inestabilidadId']) : 10000;

      $arrInicial = [
          'codigo_departamento' => $departamento,
          'inestabilidadId' => $inestabilidadId
      ];
      $dataInicial = FactoresInestabilidadGeneral::consultarConsolidadDepartamental($arrInicial);
      $dataActual = FactoresInestabilidadGeneral::consultarConsolidadActualesDepartamental($arrInicial);

      echo json_encode([
          'valid' => $dataInicial['output']['valid'] ?? false,
          'tabs' => $dataInicial['output']['tabs'] ?? [],
          'response' => $dataInicial['output']['response'] ?? [],
          'responseActual' => $dataActual['output']['valid'] ? $dataActual['output']['response'] : [],
          'debug' => [
              'inestabilidadId' => $inestabilidadId,
              'departamento' => $departamento,
              'countInicial' => count($dataInicial['output']['response'] ?? []),
              'countActual' => count($dataActual['output']['response'] ?? [])
          ]
      ], JSON_UNESCAPED_UNICODE);
      break;

  case 'get_inestabilidad_mapa':
      include '../classes/FactoresInestabilidadGeneral.php';
      $departamento = Util::getDepartamentoPrincipal();
      $inestabilidadId = isset($rqst['inestabilidadId']) ? intval($rqst['inestabilidadId']) : 10000;

      $arrMapa = [
          'codigo_departamento' => $departamento,
          'inestabilidadId' => $inestabilidadId,
      ];
      $dataInicial = FactoresInestabilidadGeneral::calcularColorMapaInicial($arrMapa);
      $dataActual = FactoresInestabilidadGeneral::calcularColorMapaActual($arrMapa);

      echo json_encode([
          'output' => [
              'valid' => ($dataInicial['output']['valid'] && $dataActual['output']['valid']),
              'inicial' => $dataInicial['output']['response'] ?? [],
              'actual' => $dataActual['output']['response'] ?? []
          ]
      ]);
      break;

  case 'get_consolidado_por_municipio':
      include '../classes/Colombia.php';
      $departamento = Util::getDepartamentoPrincipal();
      $pilar = isset($rqst['pilar']) ? intval($rqst['pilar']) : 10000;

      $arrInicial = [
          'codigo_departamento' => $departamento,
          'pilar' => $pilar
      ];
      $dataInicial = Colombia::consultarConsolidadPilaresFactoresPorMunicipio($arrInicial);
      $dataActual = Colombia::consultarConsolidadPilaresFactoresActualesPorMunicipio($arrInicial);

      echo json_encode([
          'valid' => $dataInicial['output']['valid'] ?? false,
          'tabs' => $dataInicial['output']['tabs'] ?? [],
          'response' => $dataInicial['output']['response'] ?? [],
          'responseActual' => $dataActual['output']['valid'] ? $dataActual['output']['response'] : [],
          'debug' => [
              'pilar' => $pilar,
              'departamento' => $departamento,
              'countInicial' => count($dataInicial['output']['response'] ?? []),
              'countActual' => count($dataActual['output']['response'] ?? [])
          ]
      ]);
      break;

  case 'getComponentesPorMunicipio':
      include '../classes/ComponenteMunicipios.php';
      $codigo = isset($rqst['codigo_municipio']) ? $rqst['codigo_municipio'] : '';
      echo json_encode(ComponenteMunicipios::getComponentesPorMunicipio($codigo));
      break;

  case 'get_estadisticas_bd':
      require_once '../classes/EstadisticasBD.php';
      echo json_encode(EstadisticasBD::get());
      break;

  default:
    echo 'OPERACION NO DISPONIBLE';
    break;
}
