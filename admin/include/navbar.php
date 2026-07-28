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
?>

<nav class="pcoded-navbar navbar-saaspro">
    <div class="navbar-wrapper">
        <div class="navbar-content scroll-div navbar-saaspro-scroll">

            <ul class="nav pcoded-inner-navbar navbar-saaspro-inner">

                <!-- Perfil de Usuario -->
                <div class="user-profile user-profile-saas d-flex flex-column align-items-center">
                    <button onclick="toggleMenu()" id="menuToggleBtn" class="menu-toggle-btn" title="Minimizar menú">
                        <i class="feather icon-chevrons-left"></i>
                    </button>
                    <div class="profile-img mb-2">
                        <?php
                        $img = !empty(SessionData::getFotoUsuario())
                            ? "assets/img/admin/" . htmlspecialchars(SessionData::getFotoUsuario())
                            : 'assets/img/santander.png';
                        ?>
                        <img src="<?= $img ?>" alt="user" class="rounded-circle" width="60">
                    </div>
                    <h6 class="text-white fw-bold mb-0 text-center">
                        <?php echo SessionData::getNombreUsuario(); ?>
                    </h6>
                    <span class="text-muted text-center" style="font-size: 12px;color: #ffffff !important;">
                        <?php echo SessionData::getUserType(); ?>
                    </span>
                </div>

                <!-- Buscador de menú -->
                <li class="nav-item" style="padding: 0 15px 8px 15px;">
                    <div style="position:relative;">
                        <i class="feather icon-search" style="position:absolute;left:12px;top:50%;transform:translateY(-50%);color:rgba(255,255,255,.35);font-size:15px;pointer-events:none;"></i>
                        <input type="text" id="menuSearch" placeholder="Buscar en el menú..."
                            style="width:100%;background:rgba(255,255,255,.08);border:1px solid rgba(255,255,255,.10);border-radius:10px;padding:9px 9px 9px 36px;font-size:13px;font-weight:500;outline:none;transition:border-color .15s,box-shadow .15s;"
                            onfocus="this.style.borderColor='rgba(79,124,255,.50)';this.style.boxShadow='0 0 0 3px rgba(79,124,255,.15)'"
                            onblur="this.style.borderColor='rgba(255,255,255,.10)';this.style.boxShadow='none'"
                            oninput="filtrarMenu(this.value)">
                        <span id="menuSearchClear" onclick="limpiarBusqueda()"
                            style="position:absolute;right:8px;top:50%;transform:translateY(-50%);color:rgba(255,255,255,.35);cursor:pointer;font-size:16px;display:none;line-height:1;padding:2px 6px;border-radius:6px;transition:color .15s,background .15s;"
                            onmouseover="this.style.color='#fff';this.style.background='rgba(255,255,255,.10)'"
                            onmouseout="this.style.color='rgba(255,255,255,.35)';this.style.background='transparent'">&times;</span>
                    </div>
                </li>

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
                <?php endif; ?>

            </ul>
        </div>
    </div>
</nav>

<!-- BOTTOM NAV (solo móvil/tablet) -->
<nav class="mobile-navbar fixed-bottom">
    <div class="container">
        <div class="row align-items-center" style="margin:0;">
            <div class="col text-center">
                <a href="dashboard.php" class="mobile-nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'active' : ''; ?>">
                    <i class="feather icon-home"></i>
                    <span class="d-block small">Inicio</span>
                </a>
            </div>
            <div class="col text-center">
                <a href="menu_mobile.php" class="mobile-nav-link" id="toggleMobileMenu">
                    <i class="feather icon-menu"></i>
                    <span class="d-block small">Menú</span>
                </a>
            </div>
            <div class="col text-center">
                <a href="logout.php" class="mobile-nav-link">
                    <i class="feather icon-log-out"></i>
                    <span class="d-block small">Salir</span>
                </a>
            </div>
        </div>
    </div>
</nav>

<style>
/* ================================
   NAVBAR PRO SaaS WOW (PCoded)
   SOLO DISEÑO (NO FUNCIONALIDAD)
==================================*/

:root{
  --nb-1:#050914;
  --nb-2:#07162d;
  --nb-3:#132b52;
  --nb-4:#2e58a8;
  --glass: rgba(255,255,255,.06);
  --glass2: rgba(255,255,255,.10);
  --stroke: rgba(255,255,255,.10);
  --stroke2: rgba(255,255,255,.16);
  --txt: rgba(255,255,255,.92);
  --muted: rgba(255,255,255,.70);
  --hover: rgba(255,255,255,.08);
  --active: rgba(46,88,168,.30);
  --shadow: 0 18px 40px rgba(2,6,23,.35);
  --shadow2: 0 14px 28px rgba(2,6,23,.28);
  --r-xl: 22px;
  --r-lg: 16px;
  --r-md: 12px;
}

/* Sidebar con aire (no pegado al body) */
.pcoded-navbar.navbar-saaspro{
  top: 12px !important;
  left: 12px !important;
  height: calc(100vh - 24px) !important;
  width: 264px;
  border-radius: var(--r-xl);
  overflow: hidden !important;
  border: 1px solid rgba(255,255,255,.10);
  box-shadow: var(--shadow);
  background: transparent !important;
  z-index: 1030;
}

/* Fondo premium */
.pcoded-navbar.navbar-saaspro::before{
  content:"";
  position:absolute;
  inset:0;
  z-index:0;
  background:
    radial-gradient(900px 420px at 12% 8%, rgba(46,88,168,.38), transparent 55%),
    radial-gradient(850px 520px at 88% 22%, rgba(19,43,82,.62), transparent 62%),
    radial-gradient(700px 520px at 50% 120%, rgba(32,66,127,.45), transparent 55%),
    linear-gradient(135deg, #050914 0%, #07162d 38%, #0c1733 68%, #050914 100%);
}

/* Velo */
.pcoded-navbar.navbar-saaspro::after{
  content:"";
  position:absolute;
  inset:0;
  z-index:0;
  pointer-events:none;
  background:
    linear-gradient(180deg, rgba(255,255,255,.06) 0%, rgba(255,255,255,.02) 45%, rgba(0,0,0,.18) 100%),
    radial-gradient(600px 260px at 30% 15%, rgba(255,255,255,.10), transparent 60%);
}

/* Contenido por encima */
.pcoded-navbar.navbar-saaspro .navbar-wrapper,
.pcoded-navbar.navbar-saaspro .navbar-content{
  position: relative;
  z-index: 1;
  height: 100% !important;
}

/* Scroll REAL interno */
.pcoded-navbar.navbar-saaspro .navbar-saaspro-scroll{
  height: 100% !important;
  max-height: 100% !important;
  overflow-y: auto !important;
  overflow-x: hidden !important;
  padding: 10px 10px 18px !important;
  scrollbar-width: thin;
  scrollbar-color: rgba(46,88,168,.65) transparent;
}

.pcoded-navbar.navbar-saaspro .navbar-saaspro-scroll::-webkit-scrollbar{ width: 8px; }
.pcoded-navbar.navbar-saaspro .navbar-saaspro-scroll::-webkit-scrollbar-track{ background: transparent; }
.pcoded-navbar.navbar-saaspro .navbar-saaspro-scroll::-webkit-scrollbar-thumb{
  background: linear-gradient(180deg, rgba(46,88,168,.55), rgba(255,255,255,.12));
  border-radius: 999px;
  border: 2px solid rgba(0,0,0,.15);
}
.pcoded-navbar.navbar-saaspro .navbar-saaspro-scroll::-webkit-scrollbar-thumb:hover{
  background: linear-gradient(180deg, rgba(46,88,168,.85), rgba(255,255,255,.16));
}

/* Perfil sticky NO transparente (no se ve nada por debajo) */
.user-profile-saas{
  position: sticky !important;
  top: 10px !important;
  z-index: 999 !important;
  width: calc(100% - 0px);
  margin: 0 0 14px 0 !important;
  padding: 18px 12px !important;
  border-radius: 18px !important;
  border: 1px solid rgba(255,255,255,.14);
  background: linear-gradient(135deg, rgba(13,18,30,.92), rgba(18,38,78,.92)) !important;
  backdrop-filter: blur(10px);
  -webkit-backdrop-filter: blur(10px);
  box-shadow: var(--shadow2);
  overflow: hidden !important;
}

.user-profile-saas::after{
  content:"";
  position:absolute;
  inset:0;
  background:
    radial-gradient(900px 260px at 20% 10%, rgba(86,132,255,.22), transparent 55%),
    radial-gradient(700px 220px at 90% 40%, rgba(104,255,179,.10), transparent 55%);
  z-index: 1;
  pointer-events:none;
}

.user-profile-saas *{
  position: relative;
  z-index: 2;
}

.user-profile-saas .profile-img img{
  width: 64px;
  height: 64px;
  object-fit: cover;
  border-radius: 999px;
  border: 2px solid rgba(255,255,255,.22);
  box-shadow: 0 12px 26px rgba(2,6,23,.35);
}

.user-profile-saas h6{
  color: var(--txt) !important;
  font-weight: 900 !important;
  margin-top: 10px !important;
  text-shadow: 0 2px 10px rgba(0,0,0,.35);
}

.user-profile-saas span{
  color: var(--muted) !important;
  font-weight: 800 !important;
}

/* Botón minimizar */
#menuToggleBtn{
  display: inline-flex;
  align-items: center;
  justify-content: center;
  width: 34px;
  height: 34px;
  border-radius: 999px;
  border: 1px solid rgba(255,255,255,.14);
  background: rgba(255,255,255,.07);
  color: var(--txt);
  box-shadow: 0 10px 22px rgba(2,6,23,.30);
  transition: transform .16s ease, background .16s ease, border-color .16s ease;
}
#menuToggleBtn:hover{
  transform: translateY(-1px);
  background: rgba(255,255,255,.10);
  border-color: rgba(255,255,255,.22);
}
#menuToggleBtn i{ font-size: 16px; }

/* Captions */
.navbar-saaspro-inner .pcoded-menu-caption{
  margin: 10px 0 6px !important;
  padding: 8px 10px !important;
  border-radius: 12px;
  background: rgba(255,255,255,.05);
  border: 1px solid rgba(255,255,255,.10);
  letter-spacing: .12em;
}
.navbar-saaspro-inner .pcoded-menu-caption label{
  color: rgba(255,255,255,.75) !important;
  font-weight: 900 !important;
  font-size: 11px !important;
  text-transform: uppercase;
}

/* Items */
.pcoded-navbar.navbar-saaspro .pcoded-inner-navbar > li.nav-item{
  margin: 2px 0 !important;
}
.pcoded-navbar.navbar-saaspro .pcoded-inner-navbar > li > a{
  border-radius: 14px !important;
  padding: 10px 12px !important;
  color: var(--txt) !important;
  font-weight: 850 !important;
  display: flex !important;
  align-items: center !important;
  gap: 10px;
  border: 1px solid transparent;
  transition: background .16s ease, transform .16s ease, border-color .16s ease;
  text-shadow: 0 1px 8px rgba(0,0,0,.28);
}
.pcoded-navbar.navbar-saaspro .pcoded-inner-navbar > li > a .pcoded-micon i{
  color: rgba(255,255,255,.92) !important;
  font-size: 16px !important;
}
.pcoded-navbar.navbar-saaspro .pcoded-inner-navbar > li > a:hover{
  background: var(--hover) !important;
  border-color: rgba(255,255,255,.12);
  transform: translateX(2px);
}

/* Active / Trigger */
.pcoded-navbar.navbar-saaspro .pcoded-inner-navbar > li.active > a,
.pcoded-navbar.navbar-saaspro .pcoded-inner-navbar > li.pcoded-trigger > a{
  background: linear-gradient(135deg, rgba(46,88,168,.35), rgba(255,255,255,.06)) !important;
  border-color: rgba(255,255,255,.16) !important;
  box-shadow: 0 12px 26px rgba(2,6,23,.25);
}

/* Submenus */
.pcoded-navbar.navbar-saaspro .pcoded-submenu{
  margin: 6px 0 8px 14px !important;
  padding: 8px !important;
  border-radius: 16px;
  border: 1px solid rgba(255,255,255,.10);
  background: rgba(255,255,255,.05);
  backdrop-filter: blur(10px);
  overflow: hidden;
}
.pcoded-navbar.navbar-saaspro .pcoded-submenu li a{
  display: flex !important;
  align-items: center;
  border-radius: 12px !important;
  padding: 8px 10px !important;
  color: rgba(255,255,255,.86) !important;
  font-weight: 800 !important;
  border: 1px solid transparent;
  transition: background .16s ease, border-color .16s ease, transform .16s ease;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}
.pcoded-navbar.navbar-saaspro .pcoded-submenu li a:hover{
  background: rgba(255,255,255,.08) !important;
  border-color: rgba(255,255,255,.12);
  transform: translateX(2px);
}
.pcoded-navbar.navbar-saaspro .pcoded-submenu li.active > a{
  background: rgba(46,88,168,.28) !important;
  border-color: rgba(255,255,255,.16);
}

/* Modo minimizado (tu lógica intacta) */
body.menu-minimized .pcoded-navbar.navbar-saaspro{
  width: 86px !important;
  transition: width .25s ease;
}
body.menu-minimized .pcoded-navbar.navbar-saaspro .pcoded-mtext,
body.menu-minimized .pcoded-navbar.navbar-saaspro .pcoded-menu-caption,
body.menu-minimized .pcoded-navbar.navbar-saaspro .pcoded-submenu{
  display: none !important;
}
body.menu-minimized .pcoded-navbar.navbar-saaspro .pcoded-inner-navbar > li > a{
  justify-content: center !important;
  padding: 12px 10px !important;
}
body.menu-minimized .pcoded-navbar.navbar-saaspro .pcoded-micon{
  margin: 0 auto !important;
}

/* Separación del contenido principal (sin romper template) */
@media (min-width: 992px){
  .pcoded-main-container{
    margin-left: calc(264px + 24px) !important;
  }
  .pcoded-header{
    left: calc(264px + 24px) !important;
    width: calc(100% - (264px + 24px)) !important;
  }
  body.menu-minimized .pcoded-main-container{
    margin-left: calc(86px + 24px) !important;
  }
  body.menu-minimized .pcoded-header{
    left: calc(86px + 24px) !important;
    width: calc(100% - (86px + 24px)) !important;
  }
}

/* En móvil/tablet oculta sidebar (usas menu_mobile.php + bottom nav) */
@media (max-width: 991.98px){
  .pcoded-navbar.navbar-saaspro{ display:none !important; }
}

/* Bottom nav PRO visible solo en móvil/tablet */
.mobile-navbar{
  z-index: 9999;
  border-radius: 22px;
  margin: 0 auto;
  width: calc(100% - 18px);
  max-width: 560px;
  padding: 8px 10px;
  border: 1px solid rgba(255,255,255,.16);
  background:
    radial-gradient(520px 220px at 10% 10%, rgba(46,88,168,.45), transparent 60%),
    radial-gradient(520px 240px at 90% 20%, rgba(19,43,82,.55), transparent 62%),
    linear-gradient(135deg, #050914 0%, #07162d 40%, #050914 100%);
  box-shadow: 0 -14px 30px rgba(2,6,23,.35);
  backdrop-filter: blur(10px);
}
.mobile-nav-link{
  color: rgba(255,255,255,.82) !important;
  display: flex;
  flex-direction: column;
  align-items: center;
  text-decoration: none !important;
  padding: 6px 6px;
  border-radius: 14px;
  border: 1px solid transparent;
  transition: background .16s ease, border-color .16s ease, transform .16s ease;
}
.mobile-nav-link i{
  font-size: 18px;
  margin-bottom: 2px;
  color: rgba(255,255,255,.92) !important;
}
.mobile-nav-link span{
  font-size: 11px;
  font-weight: 900;
  letter-spacing: .2px;
}
.mobile-nav-link:hover{
  background: rgba(255,255,255,.07);
  border-color: rgba(255,255,255,.14);
  transform: translateY(-1px);
}
.mobile-nav-link.active{
  background: rgba(46,88,168,.26);
  border-color: rgba(255,255,255,.18);
}

/* Evita que el contenido quede tapado por el bottom nav */
@media (max-width: 991.98px){
  body{ padding-bottom: 92px !important; }
}
@media (min-width: 992px){
  .mobile-navbar{ display:none !important; }
}

/* Buscador menú - texto + placeholder blanco forzado */
#menuSearch{ color:#ffffff !important; }
#menuSearch::placeholder{ color:rgba(255,255,255,.45) !important; }
#menuSearch::-webkit-input-placeholder{ color:rgba(255,255,255,.45) !important; }
#menuSearch::-moz-placeholder{ color:rgba(255,255,255,.45) !important; }
#menuSearch:-ms-input-placeholder{ color:rgba(255,255,255,.45) !important; }

/* Seguridad visual */
html, body{ overflow-x: hidden; }
.pcoded-main-container, .pcoded-content{ overflow: visible !important; }
</style>

<script>
  function toggleMenu() {
    const body = document.body;
    const btn = document.getElementById("menuToggleBtn");
    body.classList.toggle("menu-minimized");

    if (body.classList.contains("menu-minimized")) {
      btn.innerHTML = '<i class="feather icon-chevrons-right" title="Maximizar menú"></i>';
    } else {
      btn.innerHTML = '<i class="feather icon-chevrons-left" title="Minimizar menú"></i>';
    }
  }
</script>

<script>
  document.addEventListener("DOMContentLoaded", () => {
    if (window.feather && typeof window.feather.replace === "function") {
      window.feather.replace();
    }
  });

  function limpiarBusqueda() {
    const input = document.getElementById('menuSearch');
    if (input) {
      input.value = '';
      input.focus();
      filtrarMenu('');
    }
  }

  function obtenerTextoItem(item) {
    let texto = '';
    // Buscar en pcoded-mtext (items principales)
    item.querySelectorAll('.pcoded-mtext').forEach(el => { texto += el.textContent.toLowerCase() + ' '; });
    // Buscar en <a> directo (submenu items sin pcoded-mtext)
    item.querySelectorAll('a').forEach(el => { texto += el.textContent.toLowerCase() + ' '; });
    return texto;
  }

  function filtrarMenu(query) {
    query = query.toLowerCase().trim();
    const items = document.querySelectorAll('.pcoded-inner-navbar > .nav-item');
    const clearBtn = document.getElementById('menuSearchClear');
    if (clearBtn) clearBtn.style.display = query ? 'block' : 'none';

    const resultados = [];
    items.forEach(item => {
      if (item.querySelector('#menuSearch')) return;
      const isCaption = item.classList.contains('pcoded-menu-caption');
      const texto = obtenerTextoItem(item);
      const match = texto.includes(query);

      resultados.push({ item, isCaption, match, texto });
    });

    if (query === '') {
      resultados.forEach(r => {
        r.item.style.display = '';
        const sub = r.item.querySelector('.pcoded-submenu');
        if (sub) r.item.classList.remove('pcoded-trigger');
      });
      return;
    }

    let lastCaptionIndex = -1;
    resultados.forEach((r, i) => {
      if (r.isCaption) {
        r.item.style.display = 'none';
        lastCaptionIndex = i;
        return;
      }

      const isSubmenu = r.item.querySelector('.pcoded-submenu');
      let visible = r.match;

      if (isSubmenu) {
        const subs = r.item.querySelectorAll('.pcoded-submenu a');
        subs.forEach(el => {
          if (el.textContent.toLowerCase().includes(query)) visible = true;
        });
      }

      if (visible) {
        r.item.style.display = '';
        if (isSubmenu) r.item.classList.add('pcoded-trigger');
        if (lastCaptionIndex >= 0) resultados[lastCaptionIndex].item.style.display = '';
      } else {
        r.item.style.display = 'none';
        if (isSubmenu) r.item.classList.remove('pcoded-trigger');
      }
    });
  }
</script>
