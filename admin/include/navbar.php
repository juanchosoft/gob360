<?php
require_once __DIR__ . '/../classes/NavAuthorization.php';

$userType = SessionData::getUserType();
$isAlcaldeOAuxiliar = ($userType === Util::Alcalde() || $userType === Util::Auxiliar_Alcalde());
$isAdmin = ($userType === Util::Administrador() || $userType === Util::SuperAdministrador() || $userType == Util::Gobernador());
$isSecretario = ($userType === Util::Secretario_Despacho() || $userType === Util::Secretaria_Despacho_Gobernacion() || $userType === Util::Auxiliar() || $userType == Util::Auxiliar_secret_gob());
$isAlcalde = ($userType === Util::Alcalde() || $userType === Util::Auxiliar_Alcalde());
$municipioUsuarioLogueado = SessionData::getCodigoMunicipio();
$isGestorSocial = isset($isGestorSocial) ? (bool)$isGestorSocial : false;

// Informacion de la secretaria del usuario logueado
$secretariaId = SessionData::getSecretaria();
if ($secretariaId == 0) {
    $secretariaId = Util::getSecretariaPrincipal();
}

$nav = 'NavAuthorization';

$navFotoUsuario = SessionData::getFotoUsuario();
$navImg = !empty($navFotoUsuario)
    ? 'assets/img/admin/' . htmlspecialchars((string)$navFotoUsuario, ENT_QUOTES, 'UTF-8')
    : 'assets/img/santander.png';

$navNombreUsuario = trim((string)SessionData::getNombreUsuario());
if ($navNombreUsuario === '') {
    $navNombreUsuario = 'Usuario GOB360';
}

$navRolUsuario = trim((string)SessionData::getUserType());
if ($navRolUsuario === '') {
    $navRolUsuario = 'Usuario';
}

$navNombreUsuarioSeguro = htmlspecialchars($navNombreUsuario, ENT_QUOTES, 'UTF-8');
$navRolUsuarioSeguro = htmlspecialchars($navRolUsuario, ENT_QUOTES, 'UTF-8');

?>

<link rel="stylesheet" href="assets/css/navbar_gob360_premium.css">

<nav
    class="pcoded-navbar navbar-saaspro gob360-sidebar"
    aria-label="Navegación principal GOB360"
>
    <div class="navbar-wrapper">
        <div class="navbar-content scroll-div navbar-saaspro-scroll">

            <div class="g360-sidebar__top">

                <div class="g360-sidebar__brand">
                    <a
                        href="index.php"
                        class="g360-sidebar__brand-link"
                        aria-label="Ir al inicio de GOB360"
                    >
                        <img
                            src="assets/img/gob360l.png"
                            alt="Logo GOB360"
                            class="g360-sidebar__brand-logo"
                        >

                        <span class="g360-sidebar__brand-copy">
                            <small>Plataforma institucional</small>
                            <strong>Gestión pública inteligente</strong>
                        </span>
                    </a>

                    <button
                        type="button"
                        onclick="toggleMenu()"
                        id="menuToggleBtn"
                        class="g360-sidebar__toggle"
                        title="Minimizar menú"
                        aria-label="Minimizar menú"
                    >
                        <i class="feather icon-chevrons-left"></i>
                    </button>
                </div>

                <section class="user-profile user-profile-saas g360-sidebar-profile">
                    <span class="g360-sidebar-profile__avatar">
                        <img
                            src="<?= $navImg ?>"
                            alt="Foto de <?= $navNombreUsuarioSeguro ?>"
                            width="58"
                            height="58"
                        >
                        <span class="g360-sidebar-profile__status"></span>
                    </span>

                    <span class="g360-sidebar-profile__copy">
                        <small>Sesión activa</small>
                        <strong><?= $navNombreUsuarioSeguro ?></strong>
                        <span><?= $navRolUsuarioSeguro ?></span>
                    </span>
                </section>

                <div class="g360-sidebar-search">
                    <label for="menuSearch" class="sr-only">
                        Buscar en el menú
                    </label>

                    <span class="g360-sidebar-search__icon">
                        <i class="feather icon-search"></i>
                    </span>

                    <input
                        type="search"
                        id="menuSearch"
                        placeholder="Buscar módulo o función..."
                        autocomplete="off"
                        oninput="filtrarMenu(this.value)"
                    >

                    <button
                        type="button"
                        id="menuSearchClear"
                        class="g360-sidebar-search__clear"
                        onclick="limpiarBusqueda()"
                        aria-label="Limpiar búsqueda"
                        title="Limpiar búsqueda"
                    >
                        <i class="feather icon-x"></i>
                    </button>
                </div>

                <div id="menuSearchEmpty" class="g360-sidebar-search__empty" hidden>
                    <i class="feather icon-search"></i>
                    <span>No encontramos opciones con ese nombre.</span>
                </div>

            </div>

            <ul class="nav pcoded-inner-navbar navbar-saaspro-inner">

                <!-- Menú -->
                <li class="nav-item pcoded-menu-caption">
                    <label>Navegación</label>
                </li>

                <!-- Dashboard -->
                <li class="nav-item">
                    <?php if ($nav::showDashboardAdmin()): ?>
                        <a href="dashboard.php" class="nav-link">
                            <span class="pcoded-micon"><i class="feather icon-home"></i></span>
                            <span class="pcoded-mtext">Dashboard</span>
                        </a>
                    <?php endif; ?>

                    <?php if ($nav::showDashboardAlcalde()): ?>
                        <a href="dahsboard_alcaldias.php" class="nav-link">
                            <span class="pcoded-micon"><i class="feather icon-home"></i></span>
                            <span class="pcoded-mtext">Dashboard</span>
                        </a>
                    <?php endif; ?>

                    <?php if ($nav::showDashboardSecretario()): ?>
                        <a href="dash_secretarias.php" class="nav-link">
                            <span class="pcoded-micon"><i class="feather icon-home"></i></span>
                            <span class="pcoded-mtext">Dashboard</span>
                        </a>
                    <?php endif; ?>
                </li>

                <!-- Registro Visitas Gobernador -->
                <?php if ($nav::showVisitasGobernador()): ?>
                    <li class="nav-item pcoded-menu-caption">
                        <label>Registro Visitas Gobernador</label>
                    </li>

                    <?php if ($nav::can('visitas.gobernador.mapa.view')): ?>
                    <li class="nav-item pcoded-hasmenu">
                        <a href="#!" class="nav-link ">
                            <span class="pcoded-micon"><i class="feather icon-map"></i></span>
                            <span class="pcoded-mtext">Mapa Visitas</span>
                        </a>
                        <ul class="pcoded-submenu">
                            <li><a href="mapa_visitas_gobernador.php?depto_id=<?php echo Util::getIdentificadorDepartamentoPrincipal(); ?>">Mapa visita gobernador</a></li>
                        </ul>
                    </li>
                    <?php endif; ?>

                    <?php if ($nav::canAny(['visitas.gobernador.view', 'visitas.gobernador.cuadro.view'])): ?>
                    <li class="nav-item pcoded-hasmenu">
                        <a href="#!" class="nav-link ">
                            <span class="pcoded-micon"><i class="feather icon-users"></i></span>
                            <span class="pcoded-mtext">Registro Visitas</span>
                        </a>
                        <ul class="pcoded-submenu">
                            <?php if ($nav::can('visitas.gobernador.view')): ?><li><a href="informacion_visitas.php">Ingreso Visitas</a></li><?php endif; ?>
                            <?php if ($nav::can('visitas.gobernador.cuadro.view')): ?><li><a href="cuadro-control-visitas.php">Cuadro Control Visitas</a></li><?php endif; ?>
                        </ul>
                    </li>
                    <?php endif; ?>

                    <?php if ($nav::canAny(['compromisos.gobernador.view', 'compromisos.gobernador.cumplimiento.view', 'compromisos.gobernador.visor.view', 'compromisos.gobernador.approve'])): ?>
                    <li class="nav-item pcoded-hasmenu">
                        <a href="#!" class="nav-link ">
                            <span class="pcoded-micon"><i class="feather icon-users"></i></span>
                            <span class="pcoded-mtext">Gestión Compromisos</span>
                        </a>
                        <ul class="pcoded-submenu">
                            <?php if ($nav::can('compromisos.gobernador.view')): ?><li><a href="cuadro-control-compromisos.php">Control compromisos</a></li><?php endif; ?>
                            <?php if ($nav::can('compromisos.gobernador.view')): ?><li><a href="cuadro-control-compromisos-cumplidos.php">Compromisos Cumplidos</a></li><?php endif; ?>
                            <?php if ($nav::can('compromisos.gobernador.approve')): ?><li><a href="cuadro-control-compromisos-aprobacion.php">Aprobación compromisos</a></li><?php endif; ?>
                            <?php if ($nav::can('compromisos.gobernador.cumplimiento.view')): ?><li><a href="gestion-cumplimiento.php">Gestión cumplimiento</a></li><?php endif; ?>
                            <?php if ($nav::can('compromisos.gobernador.visor.view')): ?><li><a href="visor_gestion_compromisos.php">Visor Gestión compromisos</a></li><?php endif; ?>
                        </ul>
                    </li>
                    <?php endif; ?>
                <?php endif; ?>

                <?php if ($nav::showGestionSocial()): ?>
                    <li class="nav-item pcoded-menu-caption">
                        <label>Gestión social</label>
                    </li>
                    <li class="nav-item pcoded-hasmenu">
                        <a href="#!" class="nav-link ">
                            <span class="pcoded-micon"><i class="feather icon-file-text"></i></span>
                            <span class="pcoded-mtext">Gestión social</span>
                        </a>
                        <ul class="pcoded-submenu">
                            <li><a href="gestora_social.php?depto_id=<?php echo Util::getIdentificadorDepartamentoPrincipal(); ?>">Actividades</a></li>
                            <li><a href="visitasgestora.php">Registro Actividades</a></li>
                            <li><a href="cuadro_control_visitasg.php">Cuadro Control Actividades</a></li>
                        </ul>
                    </li>
                <?php endif; ?>

                <!-- Gestion social ASPAS -->
                <?php if ($isGestorSocial && $nav::showGestionSocialAspas()): ?>
                    <li class="nav-item pcoded-hasmenu">
                        <a href="#!" class="nav-link ">
                            <span class="pcoded-micon"><i class="feather icon-file-text"></i></span>
                            <span class="pcoded-mtext">Gestion Social2</span>
                        </a>
                        <ul class="pcoded-submenu">
                            <li><a href="aspasactividades.php?depto_id=<?php echo Util::getIdentificadorDepartamentoPrincipal(); ?>">Actividades</a></li>
                            <li><a href="visitasaspas.php">Registro Actividades</a></li>
                            <li><a href="cuadro_control_visitasaspas.php">Cuadro Control Actividades</a></li>
                        </ul>
                    </li>
                <?php endif; ?>

                <!-- Plan Desarrollo -->
                <?php if ($nav::showPlanDesarrollo()): ?>
                        <?php if ($nav::can('plan_desarrollo.mapa_comparativo.view')): ?>
                        <li class="nav-item pcoded-hasmenu">
                            <a href="#!" class="nav-link ">
                                <span class="pcoded-micon"><i class="feather icon-file-text"></i></span>
                                <span class="pcoded-mtext">Mapa Comparativo</span>
                            </a>
                            <ul class="pcoded-submenu">
                                <li><a href="mapa_comparativo.php?depto_id=<?php echo Util::getIdentificadorDepartamentoPrincipal(); ?>">Mapa Comparativo</a></li>
                            </ul>
                        </li>
                        <?php endif; ?>

                        <?php if ($nav::can('plan_desarrollo.view')): ?>
                        <li class="nav-item pcoded-menu-caption">
                            <label>Plan Desarrollo</label>
                        </li>
                        <li class="nav-item pcoded-hasmenu">
                            <a href="#!" class="nav-link ">
                                <span class="pcoded-micon"><i class="feather icon-layers"></i></span>
                                <span class="pcoded-mtext">Plan de Desarrollo</span>
                            </a>
                            <ul class="pcoded-submenu">
                                <li><a href="plan_desarrollo.php">Metas</a></li>
                            </ul>
                        </li>
                        <?php endif; ?>
                <?php endif; ?>

                                <!-- Secretarias -->
                <?php if ($nav::showSecretarias()): ?>
                    <li class="nav-item pcoded-menu-caption">
                        <label>Secretarias</label>
                    </li>
                    <li class="nav-item pcoded-hasmenu">
                        <a href="#!" class="nav-link ">
                            <span class="pcoded-micon"><i class="feather icon-folder"></i></span>
                            <span class="pcoded-mtext">Información Secretarias</span>
                        </a>
                        <ul class="pcoded-submenu">
                            <?php if ($isAdmin || $isAlcalde): ?>
                                <?php if ($secretariaId != Util::getSecretariaIdHacienda() && $nav::can('secretarias.comparativo.view')): ?>
                                    <li><a href="comparativo_secretaria.php?depto_id=<?php echo Util::getIdentificadorDepartamentoPrincipal(); ?>&secretaria=<?php echo $secretariaId; ?>">Comparativo secretarías</a></li>
                                <?php endif; ?>
                                <?php if ($secretariaId == Util::getSecretariaIdHacienda() && $nav::can('secretarias.resumen.view')): ?>
                                    <li><a href="secretaria.php?depto_id=<?php echo Util::getIdentificadorDepartamentoPrincipal(); ?>&secretaria=<?php echo Util::getSecretariaIdHacienda(); ?>&accion=Operativos+Contrabando+licores">Resumen Secretarias</a></li>
                                <?php endif; ?>
                                <?php if ($nav::can('secretarias.pae.view')): ?><li><a href="ingreso_pae.php">Información Pae</a></li><?php endif; ?>
                                <?php if ($nav::can('secretarias.hacienda.view')): ?><li><a href="hacienda.php">Información Hacienda</a></li><?php endif; ?>
                                <?php if ($nav::can('secretarias.hacienda.import')): ?><li><a href="hacienda_carga_masiva.php">Carga Masiva Hacienda</a></li><?php endif; ?>
                                <?php if ($nav::can('secretarias.administrativa.view')): ?><li><a href="bienes.php">Información Administrativa</a></li><?php endif; ?>
                                <?php if ($nav::can('secretarias.pae.dashboard.view')): ?>
                                <li class="pcoded-hasmenu">
                                    <a href="javascript:void(0)">
                                        <span class="pcoded-mtext">Dashboard PAE</span>
                                    </a>
                                    <ul class="pcoded-submenu">
                                        <li><a href="pae_dash.php?mun=<?php echo 'todos'; ?>">PAE (Base de Datos Local)</a></li>
                                        <li><a href="pae_arcgis_dash.php?mun=<?php echo 'todos'; ?>">PAE (ArcGIS Online)</a></li>
                                        <?php if ($nav::can('secretarias.pae.logs.view')): ?><li><a href="logs_api_pae_arcgis.php">Logs API PAE ArcGIS</a></li><?php endif; ?>
                                    </ul>
                                </li>
                                <?php endif; ?>
                                <?php if ($nav::can('secretarias.rpc.view')): ?><li><a href="proyectos_rpc_dash.php">Proyectos API (Por Vigencia y/o BPIN.)</a></li><?php endif; ?>
                                <?php if ($nav::can('secretarias.rpc.logs.view')): ?><li><a href="logs_api_rpc.php">Logs API Proyectos</a></li><?php endif; ?>
                                <?php if ($nav::can('secretarias.tic.view')): ?><li><a href="tic.php">Información Tic</a></li><?php endif; ?>
                                <?php if ($nav::can('secretarias.tic.dashboard.view')): ?><li><a href="tic_dash.php?mun=<?php echo Util::getCodigoMunicipioPrincipal(); ?>&opcion=<?php echo Util::getOpcionPrincipalTIC(); ?>">Dashboard Tic</a></li><?php endif; ?>
                                <?php if ($nav::can('secretarias.proyectos.view')): ?><li><a href="proyectos_secretarias.php">Ingreso Proyectos Secretarias</a></li><?php endif; ?>
                                <?php if ($nav::can('secretarias.proyectos.seguimiento.view')): ?><li><a href="proyectos_seguimiento_secretarias.php">Seguimiento Proyectos Secretarias</a></li><?php endif; ?>
                                <?php if ($nav::can('secretarias.administrativa.dashboard.view')): ?><li><a href="dash_adminitrativa.php">Dashboard Administrativa</a></li><?php endif; ?>
                                <?php if ($nav::can('secretarias.infraestructura.dashboard.view')): ?><li><a href="dashboard_infraestructura.php">Dashboard Infraestructura</a></li><?php endif; ?>
                                <?php if ($nav::can('secretarias.hacienda.dashboard.view')): ?><li><a href="dashboard_hacienda.php">Dashboard Hacienda</a></li><?php endif; ?>
                            <?php else: ?>
                                
                                <?php if ($secretariaId != Util::getSecretariaIdHacienda() && $nav::can('secretarias.resumen.view')): ?>
                                    <li><a href="secretaria.php?depto_id=<?php echo Util::getIdentificadorDepartamentoPrincipal(); ?>&secretaria=<?php echo $secretariaId; ?>">Resumen Secretarias</a></li>
                                <?php endif; ?>
                                
                                <?php if ($secretariaId == Util::getSecretariaIdHacienda() && $nav::can('secretarias.resumen.view')): ?>
                                    <li><a href="secretaria.php?depto_id=<?php echo Util::getIdentificadorDepartamentoPrincipal(); ?>&secretaria=<?php echo $secretariaId; ?>&accion=Operativos+Contrabando+licores">Resumen Secretarias</a></li>
                                <?php endif; ?>
                                
                                <?php if ($secretariaId == Util::getSecretariaIdEducacion() && $nav::can('secretarias.pae.view')): ?>
                                    <li><a href="ingreso_pae.php">Información Pae</a></li>
                                <?php endif; ?>
                                
                                <?php if ($secretariaId == Util::getSecretariaIdHacienda() && $nav::can('secretarias.hacienda.view')): ?>
                                    <li><a href="hacienda.php">Información Hacienda</a></li>
                                <?php endif; ?>
                                <?php if ($secretariaId == Util::getSecretariaIdHacienda() && $nav::can('secretarias.hacienda.import')): ?>
                                    <li><a href="hacienda_carga_masiva.php">Carga Masiva Hacienda</a></li>
                                <?php endif; ?>
                                <?php if ($secretariaId == Util::getSecretariaIdHacienda() && $nav::can('secretarias.hacienda.dashboard.view')): ?>
                                    <li><a href="dashboard_hacienda.php">Dashboard Hacienda</a></li>
                                <?php endif; ?>
                                
                                <?php if ($secretariaId == Util::getSecretariaIdAdministrativa() && $nav::can('secretarias.administrativa.view')): ?>
                                    <li><a href="bienes.php">Información Administrativa</a></li>
                                <?php endif; ?>

                                <?php if ($secretariaId == Util::getSecretariaIdEducacion() && $nav::can('secretarias.pae.dashboard.view')): ?>
                                    <li class="pcoded-hasmenu">
                                        <a href="javascript:void(0)">
                                            <span class="pcoded-mtext">Dashboard PAE</span>
                                        </a>
                                        <ul class="pcoded-submenu">
                                            <li><a href="pae_dash.php?mun=<?php echo 'todos'; ?>">PAE (Base de Datos Local)</a></li>
                                            <li><a href="pae_arcgis_dash.php?mun=<?php echo 'todos'; ?>">PAE (ArcGIS Online)</a></li>
                                            <?php if ($nav::can('secretarias.pae.logs.view')): ?><li><a href="logs_api_pae_arcgis.php">Logs API PAE ArcGIS</a></li><?php endif; ?>
                                        </ul>
                                    </li>
                                <?php endif; ?>
                                <?php if ($secretariaId == Util::getSecretariaIdTIC() && $nav::can('secretarias.tic.view')): ?>
                                    <li><a href="tic.php">Información Tic</a></li>
                                <?php endif; ?>
                                <?php if ($secretariaId == Util::getSecretariaIdTIC() && $nav::can('secretarias.tic.dashboard.view')): ?>
                                    <li><a href="tic_dash.php?mun=<?php echo Util::getCodigoMunicipioPrincipal(); ?>&opcion=<?php echo Util::getOpcionPrincipalTIC(); ?>">Dashboard Tic</a></li>
                                <?php endif; ?>
                                <?php if ($secretariaId == Util::getSecretariaIdTIC() && $nav::can('secretarias.proyectos.view')): ?>
                                    <li><a href="proyectos_secretarias.php">Ingreso Proyectos Secretarias</a></li>
                                <?php endif; ?>

                                <?php if ($nav::can('secretarias.proyectos.seguimiento.view')): ?><li><a href="proyectos_seguimiento_secretarias.php">Seguimiento Proyectos Secretarias</a></li><?php endif; ?>
                                <?php if ($nav::can('secretarias.rpc.view')): ?><li><a href="proyectos_rpc_dash.php">Proyectos API (Rendición de Cuentas)</a></li><?php endif; ?>
                                <?php if ($nav::can('secretarias.rpc.logs.view')): ?><li><a href="logs_api_rpc.php">Logs API Proyectos</a></li><?php endif; ?>
                                <?php if ($nav::can('dashboard.secretario.view')): ?><li><a href="dash_secretarias.php">Dashboard Secretarias</a></li><?php endif; ?>
                                <?php if ($secretariaId == Util::getSecretariaIdAdministrativa() && $nav::can('secretarias.administrativa.dashboard.view')): ?>
                                    <li><a href="dash_adminitrativa.php">Dashboard Administrativa</a></li>
                                <?php endif; ?>
                            <?php endif; ?>
                        </ul>
                    </li>
                <?php endif; ?>

                <!-- Secretaria Interior -->
                <?php if (!$isAlcaldeOAuxiliar && $nav::showInterior() && ($nav::showDashboardAdmin() || $secretariaId == Util::getSecretariaIdInterior())): ?>
                  <li class="nav-item pcoded-hasmenu">
                        <a href="#!" class="nav-link ">
                            <span class="pcoded-micon"><i class="feather icon-settings"></i></span>
                            <span class="pcoded-mtext">Secretaria Interior</span>
                        </a>
                        <ul class="pcoded-submenu">
                            <?php if ($nav::can('interior.formulario.view')): ?><li><a href="dashboard_interior_form.php">Formulario Estadistica de Seguridad</a></li><?php endif; ?>
                            <?php if ($nav::can('interior.boletin.view')): ?><li><a href="dash_interior.php">Boletin Estratégico de Seguridad</a></li><?php endif; ?>
                            <?php if ($nav::can('interior.contratos.view')): ?><li><a href="inversiones_interior.php">Formulario Registro de Contratos</a></li><?php endif; ?>
                            <?php if ($nav::can('interior.resultados.view')): ?><li><a href="dashboard_seguridad.php">Resultados en Materia de Inversión</a></li><?php endif; ?>
                        </ul>
                    </li>
                  <?php endif; ?>
                  <?php if ($nav::showDeportes() && ($nav::showDashboardAdmin() || $secretariaId == Util::getSecretariaIdInder())): ?>
                      <li class="nav-item pcoded-hasmenu">
                        <a href="#!" class="nav-link ">
                            <span class="pcoded-micon"><i class="feather icon-settings"></i></span>
                            <span class="pcoded-mtext">Inder</span>
                        </a>
                        <ul class="pcoded-submenu">
                            <?php if ($nav::can('deportes.deportistas.view')): ?><li><a href="deportistas.php">Deportistas</a></li><?php endif; ?>
                            <?php if ($nav::can('deportes.listado.view')): ?><li><a href="listado_deportistas.php">Listado Deportistas</a></li><?php endif; ?>
                        </ul>
                    </li>
                <?php endif; ?>

                <!-- Configuración General -->
                <?php if ($nav::showConfiguracion()): ?>
                    <li class="nav-item pcoded-menu-caption">
                        <label>Configuración General</label>
                    </li>
                    <li class="nav-item pcoded-hasmenu">
                        <a href="#!" class="nav-link ">
                            <span class="pcoded-micon"><i class="feather icon-settings"></i></span>
                            <span class="pcoded-mtext">Configuración General</span>
                        </a>
                        <ul class="pcoded-submenu">
                            <?php if ($nav::can('configuracion.sistema.view')): ?><li><a href="configuracion.php">Configuración</a></li><?php endif; ?>
                            <?php if ($nav::can('configuracion.usuarios.view')): ?><li><a href="usuarios.php">Usuarios</a></li><?php endif; ?>
                            <?php if ($nav::canAny(['configuracion.roles.view', 'configuracion.roles.manage'])): ?>
                            <li><a href="roles_permisos.php">Roles y Permisos</a></li>
                            <?php endif; ?>
                            <?php if ($nav::can('configuracion.lineas.view')): ?><li><a href="linea.php">Lineas Gestión social</a></li><?php endif; ?>
                            <?php if ($nav::can('configuracion.estrategias.view')): ?><li><a href="estrategia.php">Estrategias</a></li><?php endif; ?>
                            <?php if ($nav::can('configuracion.acciones_gestion.view')): ?><li><a href="acciong.php">Acciones Gestión social</a></li><?php endif; ?>
                            <?php if ($nav::can('configuracion.sesiones.view')): ?><li><a href="usuarios_session.php">Sesión Usuarios</a></li><?php endif; ?>
                            <?php if ($nav::can('configuracion.secretarias.view')): ?><li><a href="secretarias.php">Secretarias y Entidades</a></li><?php endif; ?>
                            <?php if ($nav::can('configuracion.ministerios.view')): ?><li><a href="ministerios.php">Ministerios y Entidades</a></li><?php endif; ?>
                            <?php if ($nav::can('accion_unificada.config.puntajes.view')): ?><li><a href="conf_puntajes.php">Config puntajes</a></li><?php endif; ?>
                            <?php if ($nav::can('secretarias.config_puntajes.view')): ?><li><a href="conf_puntajes_secretarias.php">Config puntajes secretaría</a></li><?php endif; ?>
                            <?php if ($nav::can('configuracion.veredas.manage')): ?>
                            <li><a href="gestion_veredas.php"><i class="feather icon-map-pin me-1"></i> Veredas</a></li>
                            <?php endif; ?>
                        </ul>
                    </li>
                <?php endif; ?>


                <!-- Registro Visitas Alcalde -->
                <?php if ($nav::showVisitasAlcalde()): ?>
                    <li class="nav-item pcoded-menu-caption">
                        <label>Registro Visitas Alcalde</label>
                    </li>

                    <?php if ($nav::can('visitas.alcalde.mapa.view')): ?>
                    <li class="nav-item pcoded-hasmenu">
                        <a href="#!" class="nav-link ">
                            <span class="pcoded-micon"><i class="feather icon-map"></i></span>
                            <span class="pcoded-mtext">Mapa Visitas</span>
                        </a>
                        <ul class="pcoded-submenu">
                            <li><a href="mapa_visitas_alcalde.php?depto_id=<?php echo Util::getIdentificadorDepartamentoPrincipal(); ?>">Mapa visita Alcalde</a></li>
                        </ul>
                    </li>
                    <?php endif; ?>

                    <?php if ($nav::canAny(['visitas.alcalde.view', 'visitas.alcalde.cuadro.view'])): ?>
                    <li class="nav-item pcoded-hasmenu">
                        <a href="#!" class="nav-link ">
                            <span class="pcoded-micon"><i class="feather icon-users"></i></span>
                            <span class="pcoded-mtext">Registro Visitas</span>
                        </a>
                        <ul class="pcoded-submenu">
                            <?php if ($nav::can('visitas.alcalde.view')): ?><li><a href="informacion_visitas_alcalde.php">Ingreso Visitas</a></li><?php endif; ?>
                            <?php if ($nav::can('visitas.alcalde.cuadro.view')): ?><li><a href="cuadro-control-visitas_alcalde.php">Cuadro Control Visitas</a></li><?php endif; ?>
                        </ul>
                    </li>
                    <?php endif; ?>

                    <?php if ($nav::canAny(['compromisos.alcalde.view', 'compromisos.alcalde.cumplimiento.view', 'compromisos.alcalde.visor.view', 'compromisos.alcalde.approve'])): ?>
                    <li class="nav-item pcoded-hasmenu">
                        <a href="#!" class="nav-link ">
                            <span class="pcoded-micon"><i class="feather icon-users"></i></span>
                            <span class="pcoded-mtext">Gestión Compromisos</span>
                        </a>
                        <ul class="pcoded-submenu">
                            <?php if ($nav::can('compromisos.alcalde.view')): ?><li><a href="cuadro-control-compromisos_alcalde.php">Control compromisos</a></li><?php endif; ?>
                            <?php if ($nav::can('compromisos.alcalde.view')): ?><li><a href="cuadro-control-compromisos-cumplidos_alcalde.php">Compromisos Cumplidos</a></li><?php endif; ?>
                            <?php if ($nav::can('compromisos.alcalde.approve')): ?><li><a href="cuadro-control-compromisos-aprobacion_alcalde.php">Aprobación compromisos</a></li><?php endif; ?>
                            <?php if ($nav::can('compromisos.alcalde.cumplimiento.view')): ?><li><a href="gestion-cumplimiento_alcalde.php">Gestión cumplimiento</a></li><?php endif; ?>
                            <?php if ($nav::can('compromisos.alcalde.visor.view')): ?><li><a href="visor_gestion_compromisos_alcalde.php">Visor Gestión compromisos</a></li><?php endif; ?>
                        </ul>
                    </li>
                    <?php endif; ?>

                    <?php if ($nav::canAny(['secretarias.municipales.view', 'secretarias.componentes.view'])): ?>
                    <li class="nav-item pcoded-menu-caption">
                        <label>Configuración Sistema</label>
                    </li>

                    <li class="nav-item pcoded-hasmenu">
                        <a href="#!" class="nav-link ">
                            <span class="pcoded-micon"><i class="feather icon-tool"></i></span>
                            <span class="pcoded-mtext">Configuración Sistema</span>
                        </a>
                        <ul class="pcoded-submenu">
                            <?php if ($nav::can('secretarias.municipales.view')): ?><li><a href="secretarias_municipios.php">Secretarías Municipales</a></li><?php endif; ?>
                            <?php if ($nav::can('secretarias.componentes.view')): ?><li><a href="componente_municipios.php">Componentes Municipales</a></li><?php endif; ?>
                        </ul>
                    </li>
                    <?php endif; ?>
                <?php endif; ?>

                <?php if ($nav::showPlanDesarrolloAlcalde()): ?>
                    <li class="nav-item pcoded-menu-caption">
                        <label>Plan Desarrollo Alacaldias</label>
                    </li>
                    <li class="nav-item pcoded-hasmenu">
                        <a href="#!" class="nav-link ">
                            <span class="pcoded-micon"><i class="feather icon-layers"></i></span>
                            <span class="pcoded-mtext">Plan de Desarrollo Alcaldia</span>
                        </a>
                        <ul class="pcoded-submenu">
                            <li><a href="plan_desarrollo_alcalde.php">Metas</a></li>
                        </ul>
                    </li>
                <?php endif; ?>

                <!-- Planeación Alcaldia -->
                <?php if ($nav::showPlaneacionAlcaldia()): ?>
                    <li class="nav-item pcoded-menu-caption">
                        <label>Planeación Alcaldia</label>
                    </li>
                    <li class="nav-item pcoded-hasmenu">
                        <a href="#!" class="nav-link ">
                            <span class="pcoded-micon"><i class="feather icon-layers"></i></span>
                            <span class="pcoded-mtext">Proyectos Planeación</span>
                        </a>
                        <ul class="pcoded-submenu">
                            <li><a href="proyectos_planeacion_alcaldia.php">Ingreso Proyectos</a></li>
                            <?php if ($nav::can('proyectos.alcaldias.planeacion.dashboard')): ?>
                              <li><a href="dashboard_proyectos_planeacion_alcaldia.php">Dashboard Planeación</a></li>
                            <?php endif; ?>
                            <?php if ($nav::can('proyectos.alcaldias.planeacion.informes')): ?>
                              <li><a href="informes_proyectos_planeacion_alcaldia.php">Informes de gestión</a></li>
                            <?php endif; ?>
                        </ul>
                    </li>
                <?php endif; ?>

                <?php if ($nav::showSecretariasAlcaldias()): ?>
                    <li class="nav-item pcoded-menu-caption">
                        <label>Secretarias Alcaldias</label>
                    </li>
                    <li class="nav-item pcoded-hasmenu">
                        <a href="#!" class="nav-link ">
                            <span class="pcoded-micon"><i class="feather icon-folder"></i></span>
                            <span class="pcoded-mtext">Información Secretarias Alcaldias</span>
                        </a>
                        <ul class="pcoded-submenu">
                            <?php if ($nav::can('secretarias.resumen.view')): ?><li><a href="secretaria_alcalde.php?depto_id=<?php echo Util::getIdentificadorDepartamentoPrincipal(); ?>&secretaria=<?php echo $secretariaId; ?>">Resumen Secretarias</a></li><?php endif; ?>
                            <?php if ($nav::can('proyectos.alcaldias.secretarias.view')): ?><li><a href="proyectos_secretarias_alcalde.php">Ingreso Proyectos Secretarias</a></li><?php endif; ?>
                            <?php if ($nav::can('secretarias.proyectos.seguimiento.view')): ?><li><a href="proyectos_seguimiento_secretarias_alcalde.php">Seguimiento Proyectos Alcaldías</a></li><?php endif; ?>
                        </ul>
                    </li>
                <?php endif; ?>

                <?php if ($nav::showProyectosAlcaldias()): ?>
                    <li class="nav-item pcoded-menu-caption">
                        <label>Alcaldías</label>
                    </li>
                    <li class="nav-item pcoded-hasmenu">
                        <a href="#!" class="nav-link ">
                            <span class="pcoded-micon"><i class="feather icon-clipboard"></i></span>
                            <span class="pcoded-mtext">Proyectos Alcaldías</span>
                        </a>
                        <?php if ($nav::can('proyectos.alcaldias.resumen.view')): ?>
                        <ul class="pcoded-submenu">
                            <li><a href="resumenalcaldias.php?<?php
                                if ($isAlcalde) { echo 'mun=' . urlencode($municipioUsuarioLogueado); }
                                else { echo 'secretaria=' . urlencode(Util::getSecretariaPrincipal()); }
                            ?>">Resumen alcaldías</a></li>
                        </ul>
                        <?php endif; ?>
                        <?php if ($nav::can('proyectos.alcaldias.view')): ?>
                        <ul class="pcoded-submenu">
                            <li><a href="proyectos_alcaldias.php">Ingreso Proyectos</a></li>
                        </ul>
                        <?php endif; ?>
                        <?php if ($nav::can('proyectos.alcaldias.seguimiento.view')): ?>
                        <ul class="pcoded-submenu">
                            <li><a href="proyectos_seguimiento_alcaldias.php">Seguimiento Proyectos</a></li>
                        </ul>
                        <?php endif; ?>
                    </li>
                <?php endif; ?>

                <?php if ($nav::showAccionUnificada()): ?>
                    <li class="nav-item pcoded-menu-caption">
                        <label>Acción Unificada</label>
                    </li>
                    <li class="nav-item pcoded-hasmenu">
                        <a href="#!" class="nav-link ">
                            <span class="pcoded-micon"><i class="feather icon-globe"></i></span>
                            <span class="pcoded-mtext">Acción Unificada</span>
                        </a>
                        <ul class="pcoded-submenu">
                            <?php if ($nav::can('accion_unificada.departamento.view') && !$isAlcalde): ?><li><a href="factores_inestabilidad_general.php?inestabilidad=10000">Departamento</a></li><?php endif; ?>
                            <?php if ($nav::can('accion_unificada.municipios.view')): ?><li><a href="municipios_inestabilidad.php?mun=68001&inestabilidad=10000">Municipios</a></li><?php endif; ?>
                            <?php if ($nav::can('accion_unificada.veredas_criticas.view')): ?><li><a href="veredas_criticas.php">Veredas Criticas</a></li><?php endif; ?>
                            <?php if ($nav::can('accion_unificada.factores_listado.view')): ?><li><a href="listado_factores_generales.php?mun=<?php echo Util::getCodigoMunicipioPrincipal(); ?>&dep=<?php echo Util::getDepartamentoPrincipal(); ?>&pilar=<?php echo Util::codigoTodos(); ?>&secretaria=<?php echo Util::codigoTodos(); ?>">Estado Listado Factores Generales</a></li><?php endif; ?>
                            <?php if ($nav::can('accion_unificada.informes.view')): ?><li><a href="informes.php">Informes</a></li><?php endif; ?>
                            <?php if ($nav::can('accion_unificada.empresas.view')): ?><li><a href="accionunificada.php">Acción unificada</a></li><?php endif; ?>
                            <?php if ($nav::can('accion_unificada.imagenes.view')): ?><li><a href="imagenes.php">Imágenes</a></li><?php endif; ?>
                            <?php if ($nav::can('accion_unificada.avances.view')): ?><li><a href="avances.php#!">Avances</a></li><?php endif; ?>
                            <?php if ($nav::can('accion_unificada.estadisticas_bd.view')): ?><li><a href="consolidado_ciudades.php">Estadísticas BD</a></li><?php endif; ?>
                        </ul>
                    </li>
                <?php endif; ?>

                <?php if ($nav::showConfigAccionUnificada()): ?>
                    <li class="nav-item pcoded-hasmenu">
                        <a href="#!" class="nav-link ">
                            <span class="pcoded-micon"><i class="feather icon-refresh-ccw"></i></span>
                            <span class="pcoded-mtext">Configuración Acción Unificada</span>
                        </a>
                        <ul class="pcoded-submenu">
                            <?php if ($nav::can('accion_unificada.config.areas.view')): ?><li><a href="areas.php">Ingreso Áreas</a></li><?php endif; ?>
                            <?php if ($nav::can('accion_unificada.config.factores.view')): ?><li><a href="ingreso_factores.php">Ingreso Factores</a></li><?php endif; ?>
                            <?php if ($nav::can('accion_unificada.config.factores_gobernacion.view')): ?>
                            <li><a href="factores_inestabilidad_gobernacion.php">Factores Inestabilidad Gobernación</a></li>
                            <?php endif; ?>
                            <?php if ($nav::can('accion_unificada.config.actores.view')): ?><li><a href="actores.php">Ingreso Actores</a></li><?php endif; ?>
                            <?php if ($nav::can('accion_unificada.config.informacion.view')): ?><li><a href="ingreso_informacion.php">Ingreso Información</a></li><?php endif; ?>
                            <?php if ($nav::can('accion_unificada.config.informacion.view')): ?><li><a href="ingreso_informacion_listado.php">Listado Ingreso Información</a></li><?php endif; ?>
                            <?php if ($nav::can('accion_unificada.config.actualizacion.view')): ?><li><a href="actualizacion_informacion.php">Actualización Información</a></li><?php endif; ?>
                        </ul>
                    </li>
                <?php endif; ?>

                <?php if ($nav::showPolicia()): ?>
                    <li class="nav-item pcoded-menu-caption">
                        <label>Comportamiento Delictiva</label>
                    </li>
                    <li class="nav-item pcoded-hasmenu">
                        <a href="#!" class="nav-link ">
                            <span class="pcoded-micon"><i class="feather icon-globe"></i></span>
                            <span class="pcoded-mtext">Información</span>
                        </a>
                        <ul class="pcoded-submenu">
                            <?php if ($nav::can('policia.informes.view')): ?><li><a href="informacion-policia.php">Informes Policía</a></li><?php endif; ?>
                            <?php if ($nav::can('policia.graficos.view')): ?><li><a href="graficos-policia.php">Gráficos Policía</a></li><?php endif; ?>
                        </ul>
                    </li>
                <?php endif; ?>

                <?php if ($nav::showEstrategicos()): ?>
                    <li class="nav-item pcoded-menu-caption">
                        <label>Proyectos Estratégicos</label>
                    </li>
                    <li class="nav-item pcoded-hasmenu">
                        <a href="#!" class="nav-link ">
                            <span class="pcoded-micon"><i class="feather icon-crosshair"></i></span>
                            <span class="pcoded-mtext">Proyectos Estratégicos</span>
                        </a>
                        <ul class="pcoded-submenu">
                            <li><a href="secretaria_estrategicos.php">Departamento</a></li>
                        </ul>
                    </li>
                <?php endif; ?>

                <?php if ($nav::showSeguimientoAlcaldiasAdmin()): ?>
                    <li class="nav-item pcoded-menu-caption">
                        <label>Seguimiento Alcaldías</label>
                    </li>
                    <li class="nav-item">
                        <a href="seguimiento_a_alcaldias_admin.php" class="nav-link">
                            <span class="pcoded-micon"><i class="feather icon-map"></i></span>
                            <span class="pcoded-mtext">Seguimiento Alcaldías</span>
                        </a>
                    </li>
                <?php endif; ?>
                <?php if ($nav::showIA()): ?>
                    <li class="nav-item pcoded-menu-caption">
                        <label>Asistente IA</label>
                    </li>
                    <?php if ($nav::can('ia.asesor_despacho.view')): ?>
                    <li class="nav-item">
                        <a href="abogadoia.php" class="nav-link">
                            <span class="pcoded-micon"><i class="feather icon-map"></i></span>
                            <span class="pcoded-mtext">Asesor Despacho IA</span>
                        </a>
                    </li>
                    <?php endif; ?>
                    <?php if ($nav::can('ia.contratacion.view')): ?>
                     <li class="nav-item">
                        <a href="contratacion_estructurador_ia.php" class="nav-link">
                            <span class="pcoded-micon"><i class="feather icon-map"></i></span>
                            <span class="pcoded-mtext">Asesor Contrataciòn IA</span>
                        </a>
                    </li>
                    
                    <?php endif; ?>
                     <li class="nav-item">
                        <a href="gobia.php" class="nav-link">
                            <span class="pcoded-micon"><i class="feather icon-map"></i></span>
                            <span class="pcoded-mtext">Gobia 360</span>
                        </a>
                    </li>
                <?php endif; ?>

                <li class="g360-sidebar-footer">
                    <div class="g360-sidebar-footer__status">
                        <span></span>

                        <div class="g360-sidebar-footer__copy">
                            <small>Estado de plataforma</small>
                            <strong>Sistema operativo</strong>
                        </div>
                    </div>

                    <span class="g360-sidebar-footer__version">
                        GOB360
                    </span>
                </li>

            </ul>
        </div>
    </div>
</nav>

<!-- NAVEGACIÓN INFERIOR GOB360 · MÓVIL/TABLET -->
<nav class="mobile-navbar fixed-bottom g360-mobile-nav" aria-label="Navegación móvil">
    <a
        href="dashboard.php"
        class="mobile-nav-link <?= basename($_SERVER['PHP_SELF']) === 'dashboard.php' ? 'active' : '' ?>"
    >
        <span class="g360-mobile-nav__icon">
            <i class="feather icon-home"></i>
        </span>
        <span>Inicio</span>
    </a>

    <a
        href="menu_mobile.php"
        class="mobile-nav-link g360-mobile-nav__primary"
        id="toggleMobileMenu"
    >
        <span class="g360-mobile-nav__icon">
            <i class="feather icon-grid"></i>
        </span>
        <span>Menú</span>
    </a>

    <a href="logout.php" class="mobile-nav-link g360-mobile-nav__logout">
        <span class="g360-mobile-nav__icon">
            <i class="feather icon-log-out"></i>
        </span>
        <span>Salir</span>
    </a>
</nav>

<script>
(function () {
    'use strict';

    const STORAGE_KEY = 'gob360-navbar-minimized';

    function getToggleButton() {
        return document.getElementById('menuToggleBtn');
    }

    function syncToggleButton() {
        const button = getToggleButton();
        if (!button) {
            return;
        }

        const minimized = document.body.classList.contains('menu-minimized');

        button.innerHTML = minimized
            ? '<i class="feather icon-chevrons-right"></i>'
            : '<i class="feather icon-chevrons-left"></i>';

        button.title = minimized ? 'Maximizar menú' : 'Minimizar menú';
        button.setAttribute(
            'aria-label',
            minimized ? 'Maximizar menú' : 'Minimizar menú'
        );

        if (window.feather && typeof window.feather.replace === 'function') {
            window.feather.replace();
        }
    }

    window.toggleMenu = function toggleMenu() {
        document.body.classList.toggle('menu-minimized');

        try {
            localStorage.setItem(
                STORAGE_KEY,
                document.body.classList.contains('menu-minimized') ? '1' : '0'
            );
        } catch (error) {
            // El menú continúa funcionando aunque localStorage esté bloqueado.
        }

        syncToggleButton();

        window.dispatchEvent(new Event('resize'));
    };

    window.limpiarBusqueda = function limpiarBusqueda() {
        const input = document.getElementById('menuSearch');

        if (!input) {
            return;
        }

        input.value = '';
        input.focus();
        window.filtrarMenu('');
    };

    window.obtenerTextoItem = function obtenerTextoItem(item) {
        let texto = '';

        item.querySelectorAll('.pcoded-mtext').forEach(function (element) {
            texto += element.textContent.toLowerCase() + ' ';
        });

        item.querySelectorAll('a').forEach(function (element) {
            texto += element.textContent.toLowerCase() + ' ';
        });

        return texto.normalize('NFD').replace(/[\u0300-\u036f]/g, '');
    };

    window.filtrarMenu = function filtrarMenu(query) {
        const normalizedQuery = String(query || '')
            .toLowerCase()
            .normalize('NFD')
            .replace(/[\u0300-\u036f]/g, '')
            .trim();

        const items = document.querySelectorAll(
            '.navbar-saaspro-inner > .nav-item, ' +
            '.navbar-saaspro-inner > .g360-sidebar-footer'
        );

        const clearButton = document.getElementById('menuSearchClear');
        const emptyState = document.getElementById('menuSearchEmpty');

        if (clearButton) {
            clearButton.classList.toggle(
                'is-visible',
                normalizedQuery.length > 0
            );
        }

        const results = [];
        let visibleItems = 0;

        items.forEach(function (item) {
            if (item.classList.contains('g360-sidebar-footer')) {
                item.style.display = normalizedQuery ? 'none' : '';
                return;
            }

            const isCaption = item.classList.contains('pcoded-menu-caption');
            const text = window.obtenerTextoItem(item);
            const match = text.includes(normalizedQuery);

            results.push({
                item: item,
                isCaption: isCaption,
                match: match
            });
        });

        if (normalizedQuery === '') {
            results.forEach(function (result) {
                result.item.style.display = '';

                const submenu = result.item.querySelector('.pcoded-submenu');
                if (
                    submenu &&
                    !result.item.classList.contains('g360-current-parent')
                ) {
                    result.item.classList.remove('pcoded-trigger');
                }
            });

            if (emptyState) {
                emptyState.hidden = true;
            }

            return;
        }

        let lastCaptionIndex = -1;

        results.forEach(function (result, index) {
            if (result.isCaption) {
                result.item.style.display = 'none';
                lastCaptionIndex = index;
                return;
            }

            const submenu = result.item.querySelector('.pcoded-submenu');
            let visible = result.match;

            if (submenu) {
                submenu.querySelectorAll('a').forEach(function (link) {
                    const linkText = link.textContent
                        .toLowerCase()
                        .normalize('NFD')
                        .replace(/[\u0300-\u036f]/g, '');

                    if (linkText.includes(normalizedQuery)) {
                        visible = true;
                    }
                });
            }

            if (visible) {
                result.item.style.display = '';
                visibleItems += 1;

                if (submenu) {
                    result.item.classList.add('pcoded-trigger');
                }

                if (lastCaptionIndex >= 0) {
                    results[lastCaptionIndex].item.style.display = '';
                }
            } else {
                result.item.style.display = '';

                if (submenu) {
                    result.item.classList.remove('pcoded-trigger');
                }
            }
        });

        if (emptyState) {
            emptyState.hidden = visibleItems > 0;
        }
    };

    function normalizePath(value) {
        try {
            const url = new URL(value, window.location.href);
            return url.pathname.replace(/\/+$/, '').toLowerCase();
        } catch (error) {
            return '';
        }
    }

    function markCurrentPage() {
        const currentPath = normalizePath(window.location.href);
        const links = document.querySelectorAll(
            '.navbar-saaspro-inner a[href]:not([href="#!"]):not([href="javascript:void(0)"])'
        );

        links.forEach(function (link) {
            const linkPath = normalizePath(link.getAttribute('href'));

            if (!linkPath || linkPath !== currentPath) {
                return;
            }

            link.classList.add('g360-current-link');

            const parentItem = link.closest('li');
            if (parentItem) {
                parentItem.classList.add('active');
            }

            const parentMenu = link.closest('.pcoded-hasmenu');
            if (parentMenu) {
                parentMenu.classList.add(
                    'active',
                    'pcoded-trigger',
                    'g360-current-parent'
                );
            }
        });
    }

    function restoreMenuState() {
        let minimized = false;

        try {
            minimized = localStorage.getItem(STORAGE_KEY) === '1';
        } catch (error) {
            minimized = false;
        }

        document.body.classList.toggle('menu-minimized', minimized);
        syncToggleButton();
    }

    function initializeNavbar() {
        restoreMenuState();
        markCurrentPage();

        if (window.feather && typeof window.feather.replace === 'function') {
            window.feather.replace();
        }
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initializeNavbar);
    } else {
        initializeNavbar();
    }
})();
</script>
