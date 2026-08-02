<?php

require_once __DIR__ . '/SessionData.php';

/**
 * Guard central de permisos por archivo de vista (basename sin .php).
 * Complementa validaciones locales; no reemplaza checks CRUD en cada vista.
 */
final class PagePermissions
{
    /** Páginas con sesión pero sin permiso específico */
    private const SESSION_ONLY = [
        'menu_mobile',
        'index',
        'logout',
        'permiso_denegado',
    ];

    /** Sin validación (login público) */
    private const PUBLIC_PAGES = [
        'login',
    ];

    /**
     * @var array<string, string|string[]>
     */
    private const PAGE_VIEW = [
        // Dashboards
        'dashboard' => 'dashboard.admin.view',
        'dahsboard_alcaldias' => 'dashboard.alcalde.view',
        'dash_secretarias' => 'dashboard.secretario.view',
        'dash_adminitrativa' => 'secretarias.administrativa.dashboard.view',
        'dashboard_hacienda' => 'secretarias.hacienda.dashboard.view',
        'dashboard_infraestructura' => 'secretarias.infraestructura.dashboard.view',
        'dashboard_seguridad' => 'interior.resultados.view',
        'dashboard_seguridad_pro' => 'interior.resultados.view',
        'dashboard_interior_form' => 'interior.formulario.view',
        'dash_interior' => 'interior.boletin.view',
        'pae_dash' => 'secretarias.pae.dashboard.view',
        'pae_arcgis_dash' => 'secretarias.pae.dashboard.view',
        'tic_dash' => 'secretarias.tic.dashboard.view',
        'proyectos_rpc_dash' => 'secretarias.rpc.view',

        // Visitas gobernador
        'mapa_visitas_gobernador' => 'visitas.gobernador.mapa.view',
        'informacion_visitas' => 'visitas.gobernador.view',
        'cuadro-control-visitas' => 'visitas.gobernador.cuadro.view',
        'cuadro_control_visitas' => 'visitas.gobernador.cuadro.view',
        'graficacion_compromisos' => 'compromisos.gobernador.view',

        // Compromisos gobernador
        'cuadro-control-compromisos' => 'compromisos.gobernador.view',
        'cuadro-control-compromisos-cumplidos' => 'compromisos.gobernador.view',
        'cuadro-control-compromisos-aprobacion' => 'compromisos.gobernador.approve',
        'gestion-cumplimiento' => 'compromisos.gobernador.cumplimiento.view',
        'visor_gestion_compromisos' => 'compromisos.gobernador.visor.view',

        // Visitas alcalde
        'mapa_visitas_alcalde' => 'visitas.alcalde.mapa.view',
        'informacion_visitas_alcalde' => 'visitas.alcalde.view',
        'cuadro-control-visitas_alcalde' => 'visitas.alcalde.cuadro.view',
        'cuadro-control-compromisos_alcalde' => 'compromisos.alcalde.view',
        'cuadro-control-compromisos-cumplidos_alcalde' => 'compromisos.alcalde.view',
        'cuadro-control-compromisos-aprobacion_alcalde' => 'compromisos.alcalde.approve',
        'gestion-cumplimiento_alcalde' => 'compromisos.alcalde.cumplimiento.view',
        'visor_gestion_compromisos_alcalde' => 'compromisos.alcalde.visor.view',

        // Gestión social
        'gestora_social' => 'gestion_social.view',
        'visitasgestora' => 'gestion_social.view',
        'cuadro_control_visitasg' => 'gestion_social.view',
        'aspasactividades' => 'gestion_social.aspas.view',
        'visitasaspas' => 'gestion_social.aspas.view',
        'cuadro_control_visitasaspas' => 'gestion_social.aspas.view',

        // Plan desarrollo
        'mapa_comparativo' => 'plan_desarrollo.mapa_comparativo.view',
        'plan_desarrollo' => 'plan_desarrollo.view',
        'plan_desarrollo_alcalde' => 'plan_desarrollo.view',
        'comparativo_secretaria' => 'secretarias.comparativo.view',

        // Secretarías
        'secretaria' => 'secretarias.resumen.view',
        'secretaria_alcalde' => 'secretarias.resumen.view',
        'ingreso_pae' => 'secretarias.pae.view',
        'hacienda' => 'secretarias.hacienda.view',
        'hacienda_carga_masiva' => 'secretarias.hacienda.import',
        'bienes' => 'secretarias.administrativa.view',
        'tic' => 'secretarias.tic.view',
        'proyectos_secretarias' => 'secretarias.proyectos.view',
        'proyectos_seguimiento_secretarias' => 'secretarias.proyectos.seguimiento.view',
        'proyectos_secretarias_alcalde' => 'proyectos.alcaldias.secretarias.view',
        'proyectos_seguimiento_secretarias_alcalde' => 'secretarias.proyectos.seguimiento.view',
        'logs_api_pae_arcgis' => 'secretarias.pae.logs.view',
        'logs_api_rpc' => 'secretarias.rpc.logs.view',
        'secretarias_municipios' => 'secretarias.municipales.view',
        'componente_municipios' => 'secretarias.componentes.view',

        // Interior
        'inversiones_interior' => 'interior.contratos.view',

        // Deportes
        'deportistas' => 'deportes.deportistas.view',
        'listado_deportistas' => 'deportes.listado.view',

        // Configuración (vistas ya con require local — refuerzo)
        'configuracion' => 'configuracion.sistema.view',
        'usuarios' => 'configuracion.usuarios.view',
        'roles_permisos' => ['configuracion.roles.view', 'configuracion.roles.manage'],
        'secretarias' => 'configuracion.secretarias.view',
        'ministerios' => 'configuracion.ministerios.view',
        'linea' => 'configuracion.lineas.view',
        'estrategia' => 'configuracion.estrategias.view',
        'acciong' => 'configuracion.acciones_gestion.view',
        'usuarios_session' => 'configuracion.sesiones.view',
        'conf_puntajes' => 'accion_unificada.config.puntajes.view',
        'conf_puntajes_secretarias' => 'secretarias.config_puntajes.view',
        'gestion_veredas' => 'configuracion.veredas.manage',

        // Proyectos alcaldías
        'resumenalcaldias' => 'proyectos.alcaldias.resumen.view',
        'proyectos_alcaldias' => 'proyectos.alcaldias.view',
        'proyectos_seguimiento_alcaldias' => 'proyectos.alcaldias.seguimiento.view',
        'proyectos_planeacion_alcaldia' => 'proyectos.alcaldias.planeacion.view',
        'reporte-proyecto-planeacion-alcaldia' => 'proyectos.alcaldias.planeacion.detail',
        'dashboard_proyectos_planeacion_alcaldia' => 'proyectos.alcaldias.planeacion.dashboard',
        'informes_proyectos_planeacion_alcaldia' => 'proyectos.alcaldias.planeacion.informes',
        'municipios_alcaldias' => 'proyectos.alcaldias.resumen.view',
        'seguimiento_a_alcaldias_admin' => 'seguimiento.alcaldias.admin.view',

        // Acción unificada
        'factores_inestabilidad_general' => 'accion_unificada.departamento.view',
        'municipios_inestabilidad' => 'accion_unificada.municipios.view',
        'veredas_criticas' => 'accion_unificada.veredas_criticas.view',
        'veredas_inestabilidad' => 'accion_unificada.municipios.view',
        'listado_factores_generales' => 'accion_unificada.factores_listado.view',
        'informes' => 'accion_unificada.informes.view',
        'accionunificada' => 'accion_unificada.empresas.view',
        'imagenes' => 'accion_unificada.imagenes.view',
        'avances' => 'accion_unificada.avances.view',
        'consolidado_ciudades' => 'accion_unificada.estadisticas_bd.view',
        'departamentos' => 'accion_unificada.departamento.view',
        'municipios' => 'accion_unificada.municipios.view',
        'veredas' => 'accion_unificada.veredas.view',

        // Config acción unificada
        'areas' => 'accion_unificada.config.areas.view',
        'ingreso_factores' => 'accion_unificada.config.factores.view',
        'factores_inestabilidad_gobernacion' => 'accion_unificada.config.factores_gobernacion.view',
        'actores' => 'accion_unificada.config.actores.view',
        'ingreso_informacion' => 'accion_unificada.config.informacion.view',
        'ingreso_informacion_listado' => 'accion_unificada.config.informacion.view',
        'actualizacion_informacion' => 'accion_unificada.config.actualizacion.view',
        'actualizacion_estrategicos' => 'estrategicos.departamento.update',

        // Policía / estratégicos / IA
        'informacion-policia' => 'policia.informes.view',
        'graficos-policia' => 'policia.graficos.view',
        'secretaria_estrategicos' => 'estrategicos.departamento.view',
        'proyectos_estrategicos' => 'estrategicos.departamento.view',
        'ingreso_estrategicos' => 'estrategicos.departamento.create',
        'abogadoia' => 'ia.asesor_despacho.view',
        'contratacion_estructurador_ia' => 'ia.contratacion.view',

        // Vistas adicionales (reportes, IA, secretarías por municipio)
        'factores_inestabilidad_gobernacion' => 'accion_unificada.config.factores_gobernacion.view',
        'ingreso_informacion_listado' => 'accion_unificada.config.informacion.view',
        'municipios_secretarias' => 'secretarias.municipales.view',
        'municipios_secretarias_tic' => 'secretarias.tic.view',
        'municipios_secretarias_administrativa' => 'secretarias.administrativa.view',
        'municipios_secretaria_informacion' => 'secretarias.resumen.view',
        'municipios_secretaria_informacion_alcalde' => 'secretarias.resumen.view',
        'panel_proyectos' => 'proyectos.alcaldias.view',
        'proyecto_x_alcaldia' => 'proyectos.alcaldias.view',
        'proyecto_x_secretaria' => 'secretarias.proyectos.view',
        'proyectos_secretarias_alcalde' => 'proyectos.alcaldias.secretarias.view',
        'proyectos_seguimiento_secretarias_alcalde' => 'secretarias.proyectos.seguimiento.view',
        'proyectos_seguimiento_alcaldias_no_leidos' => 'proyectos.alcaldias.seguimiento.view',
        'detalle_proyectos_alcaldias' => 'proyectos.alcaldias.view',
        'estado_municipio' => 'estado.municipios.view',
        'santaia' => 'ia.asesor_despacho.view',
        'listado_preguntas_ai_mejorado' => 'ia.asesor_despacho.view',
        'consultar_plan' => 'ia.asesor_despacho.view',
        'reporte-visita-alcalde' => 'visitas.alcalde.view',
        'reporte-compromiso' => 'compromisos.gobernador.view',
        'reporte-compromiso-alcalde' => 'compromisos.alcalde.view',
        'procesar_excel' => 'secretarias.hacienda.import',

        // Mapas legacy
        'estado_municipios' => 'estado.municipios.view',
        'estado_municipios_alcalde' => 'estado.municipios.view',
        'estado_municipios_gestora' => 'estado.municipios.view',
        'estado_municipios_aspas' => 'estado.municipios.view',
        'estado_pae_municipios' => 'secretarias.pae.view',

        // Mapas legacy
        'mapa_visitas_departamento' => 'dashboard.mapa.view',
        'mapa_prueba' => 'dashboard.mapa.view',
    ];

    /**
     * Prefijo de permiso CRUD por vista (basename sin .php).
     * Usado por crudForCurrentPage() para reemplazar getPermission(ID).
     *
     * @var array<string, string>
     */
    private const PAGE_CRUD_PREFIX = [
        'gestora_social' => 'gestion_social',
        'visitasgestora' => 'gestion_social',
        'cuadro_control_visitasg' => 'gestion_social',
        'aspasactividades' => 'gestion_social.aspas',
        'visitasaspas' => 'gestion_social.aspas',
        'cuadro_control_visitasaspas' => 'gestion_social.aspas',
        'gestion-cumplimiento' => 'compromisos.gobernador',
        'gestion-cumplimiento_alcalde' => 'compromisos.alcalde',
        'informacion_visitas' => 'visitas.gobernador',
        'cuadro_control_visitas' => 'visitas.gobernador',
        'cuadro-control-visitas' => 'visitas.gobernador',
        'graficacion_compromisos' => 'compromisos.gobernador',
        'informacion_visitas_alcalde' => 'visitas.alcalde',
        'mapa_visitas_gobernador' => 'dashboard.mapa',
        'mapa_visitas_alcalde' => 'dashboard.mapa',
        'mapa_visitas_departamento' => 'dashboard.mapa',
        'mapa_prueba' => 'dashboard.mapa',
        'mapa_comparativo' => 'plan_desarrollo',
        'tic_dash' => 'secretarias.tic',
        'pae_dash' => 'secretarias.pae',
        'proyectos_estrategicos' => 'estrategicos.departamento',
        'secretaria_estrategicos' => 'estrategicos.departamento',
        'actualizacion_estrategicos' => 'estrategicos.departamento',
        'ingreso_estrategicos' => 'estrategicos.departamento',
        'estado_municipios' => 'estado.municipios',
        'estado_municipios_alcalde' => 'estado.municipios',
        'estado_municipios_gestora' => 'estado.municipios',
        'estado_municipios_aspas' => 'estado.municipios',
        'estado_municipio' => 'estado.municipios',
        'estado_pae_municipios' => 'secretarias.pae',
        'resumenalcaldias' => 'proyectos.alcaldias',
        'municipios_alcaldias' => 'proyectos.alcaldias',
        'departamentos' => 'accion_unificada.departamento',
        'proyectos_alcaldias' => 'proyectos.alcaldias',
        'proyectos_seguimiento_alcaldias' => 'proyectos.alcaldias.seguimiento',
        'proyectos_secretarias' => 'secretarias.proyectos',
        'proyectos_seguimiento_secretarias' => 'secretarias.proyectos.seguimiento',
        'hacienda' => 'secretarias.hacienda',
        'ingreso_pae' => 'secretarias.pae',
        'tic' => 'secretarias.tic',
        'bienes' => 'secretarias.administrativa',
        'inversiones_interior' => 'interior.contratos',
        'deportistas' => 'deportes.deportistas',
        'areas' => 'accion_unificada.config.areas',
        'ingreso_factores' => 'accion_unificada.config.factores',
        'actores' => 'accion_unificada.config.actores',
        'ingreso_informacion' => 'accion_unificada.config.informacion',
        'actualizacion_informacion' => 'accion_unificada.config.actualizacion',
        'veredas' => 'accion_unificada.veredas',
        'plan_desarrollo_alcalde' => 'plan_desarrollo',
        'secretarias_municipios' => 'secretarias.municipales',
        'componente_municipios' => 'secretarias.componentes',
        'informacion-policia' => 'policia.informes',
        'graficos-policia' => 'policia.graficos',
        'dashboard_interior_form' => 'interior.formulario',
        'dash_interior' => 'interior.boletin',
        'dash_interior_get' => 'interior.boletin',
        'dash_interior_save' => 'interior.boletin',
        'dash_interior_pdf' => 'interior.boletin',
        'dash_interior_payload' => 'interior.boletin',
        'dash_interior_meta' => 'interior.boletin',
        'dashboard_seguridad' => 'interior.resultados',
        'dashboard_seguridad_pro' => 'interior.resultados',
        'procesar_excel' => 'secretarias.hacienda',
        'cimitarra' => 'configuracion.usuarios',
        'index' => 'dashboard.mapa',
    ];

    public static function guardCurrentRequest(): void
    {
        if (PHP_SAPI === 'cli') {
            return;
        }

        $script = basename($_SERVER['SCRIPT_FILENAME'] ?? '', '.php');
        if ($script === '' || in_array($script, self::PUBLIC_PAGES, true)) {
            return;
        }

        if (!isset($_SESSION['session_user'])) {
            return;
        }

        if (in_array($script, self::SESSION_ONLY, true)) {
            return;
        }

        if (!isset(self::PAGE_VIEW[$script])) {
            return;
        }

        $required = self::PAGE_VIEW[$script];
        if (is_array($required)) {
            foreach ($required as $key) {
                if (SessionData::hasPermission($key)) {
                    return;
                }
            }
            self::deny();
        }

        if (!SessionData::hasPermission($required)) {
            self::deny();
        }
    }

    /**
     * URL de inicio post-login según permisos reales del rol.
     * Evita mandar roles personalizados a dashboard.php sin dashboard.admin.view.
     *
     * @param string[] $permissionKeys
     */
    public static function resolveHomeUrl(
        array $permissionKeys,
        string $userType = '',
        ?string $preferredRoute = null
    ): string {
        $keys = array_values(array_unique(array_map('strval', $permissionKeys)));
        $has = static function (string $key) use ($keys): bool {
            return in_array($key, $keys, true);
        };
        $canScript = static function (string $script) use ($has): bool {
            if (!isset(self::PAGE_VIEW[$script])) {
                return true;
            }
            $required = self::PAGE_VIEW[$script];
            if (is_array($required)) {
                foreach ($required as $key) {
                    if ($has($key)) {
                        return true;
                    }
                }
                return false;
            }
            return $has($required);
        };
        $canUrl = static function (string $url) use ($canScript): bool {
            $path = parse_url($url, PHP_URL_PATH);
            $script = basename((string) ($path !== null && $path !== '' ? $path : $url), '.php');
            return $script !== '' && $canScript($script);
        };

        $candidates = [];

        // Preferencias por tipo legacy (roles de sistema)
        $isAlcalde = in_array($userType, ['Alcalde', 'Auxiliar_Alcalde'], true);
        $isSecretario = in_array($userType, [
            'Secretario_Despacho',
            'Secretaria_Despacho_Gobernacion',
            'Auxiliar',
            'Auxiliar_secret_gob',
        ], true);

        if ($isAlcalde) {
            $candidates[] = 'dahsboard_alcaldias.php';
        }
        if ($isSecretario) {
            if ($preferredRoute) {
                $candidates[] = $preferredRoute;
            }
            $candidates[] = 'dash_secretarias.php';
        }
        if (in_array($userType, ['Administrador', 'SuperAdministrador', 'Gobernador'], true)) {
            $candidates[] = 'dashboard.php';
        }

        // Landing por permisos (roles personalizados / sin tipo legacy)
        $byPermission = [
            'dashboard.admin.view' => 'dashboard.php',
            'dashboard.alcalde.view' => 'dahsboard_alcaldias.php',
            'dashboard.secretario.view' => 'dash_secretarias.php',
            'proyectos.alcaldias.planeacion.dashboard' => 'dashboard_proyectos_planeacion_alcaldia.php',
            'proyectos.alcaldias.planeacion.view' => 'proyectos_planeacion_alcaldia.php',
            'proyectos.alcaldias.planeacion.informes' => 'informes_proyectos_planeacion_alcaldia.php',
            'proyectos.alcaldias.view' => 'proyectos_alcaldias.php',
            'proyectos.alcaldias.resumen.view' => 'resumenalcaldias.php',
            'plan_desarrollo.view' => 'plan_desarrollo.php',
            'plan_desarrollo.mapa_comparativo.view' => 'mapa_comparativo.php',
            'interior.formulario.view' => 'dashboard_interior_form.php',
            'interior.boletin.view' => 'dash_interior.php',
            'interior.contratos.view' => 'inversiones_interior.php',
            'secretarias.resumen.view' => 'secretaria.php',
            'accion_unificada.departamento.view' => 'departamentos.php',
            'configuracion.usuarios.view' => 'usuarios.php',
        ];
        foreach ($byPermission as $perm => $url) {
            if ($has($perm)) {
                $candidates[] = $url;
            }
        }

        if ($preferredRoute) {
            $candidates[] = $preferredRoute;
        }

        foreach ($candidates as $url) {
            if ($canUrl($url)) {
                return $url;
            }
        }

        // Cualquier vista mapeada a la que el rol sí pueda entrar
        foreach (self::PAGE_VIEW as $script => $required) {
            if ($canScript($script)) {
                return $script . '.php';
            }
        }

        // Sesión válida pero sin páginas accesibles
        return 'menu_mobile.php';
    }

    /**
     * Flags CRUD para la vista actual según PAGE_CRUD_PREFIX.
     *
     * @return array{view: bool, create: bool, update: bool, delete: bool, edit: bool, permits: bool}
     */
    public static function crudForCurrentPage(): array
    {
        if (PHP_SAPI === 'cli') {
            return self::emptyCrudFlags();
        }

        $script = basename($_SERVER['SCRIPT_FILENAME'] ?? '', '.php');
        return self::crudForScript($script);
    }

    /**
     * @return array{view: bool, create: bool, update: bool, delete: bool, edit: bool, permits: bool}
     */
    public static function crudForScript(string $script): array
    {
        if (!isset(self::PAGE_CRUD_PREFIX[$script])) {
            return self::emptyCrudFlags();
        }

        return self::crudFlags(self::PAGE_CRUD_PREFIX[$script]);
    }

    /**
     * Variables $view, $create, $edit, $delete, $permits para vistas legacy.
     *
     * @return array{view: bool, create: bool, edit: bool, update: bool, delete: bool, permits: bool}
     */
    public static function crudVarsForCurrentPage(): array
    {
        $flags = self::crudForCurrentPage();
        return [
            'view' => $flags['view'],
            'create' => $flags['create'],
            'edit' => $flags['edit'],
            'update' => $flags['update'],
            'delete' => $flags['delete'],
            'permits' => $flags['permits'],
        ];
    }

    /**
     * Flags CRUD para vistas (reemplazo de getPermission por ID).
     *
     * @return array{view: bool, create: bool, update: bool, delete: bool, edit: bool, permits: bool}
     */
    public static function crudFlags(string $permissionPrefix): array
    {
        return [
            'view' => SessionData::hasPermission($permissionPrefix . '.view'),
            'create' => SessionData::hasPermission($permissionPrefix . '.create'),
            'update' => SessionData::hasPermission($permissionPrefix . '.update'),
            'delete' => SessionData::hasPermission($permissionPrefix . '.delete'),
            'edit' => SessionData::hasPermission($permissionPrefix . '.update'),
            'permits' => SessionData::hasPermission($permissionPrefix . '.manage'),
        ];
    }

    /** @return array{view: bool, create: bool, update: bool, delete: bool, edit: bool, permits: bool} */
    private static function emptyCrudFlags(): array
    {
        return [
            'view' => false,
            'create' => false,
            'update' => false,
            'delete' => false,
            'edit' => false,
            'permits' => false,
        ];
    }

    private static function deny(): void
    {
        $denied = dirname(__DIR__, 2) . '/permiso_denegado.php';
        if (is_file($denied)) {
            require $denied;
        } else {
            http_response_code(403);
            echo 'Acceso denegado';
        }
        exit;
    }
}
