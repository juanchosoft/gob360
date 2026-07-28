<?php

require_once __DIR__ . '/SessionData.php';

/**
 * Helpers de visibilidad del menú lateral basados en permisos KEY.
 * El alcance por secretaría/municipio se combina en navbar.php cuando aplica.
 */
final class NavAuthorization
{
    public static function can(string $permissionKey): bool
    {
        return SessionData::hasPermission($permissionKey);
    }

    /** @param string[] $permissionKeys */
    public static function canAny(array $permissionKeys): bool
    {
        foreach ($permissionKeys as $key) {
            if (self::can($key)) {
                return true;
            }
        }
        return false;
    }

    public static function showDashboardAdmin(): bool
    {
        return self::can('dashboard.admin.view');
    }

    public static function showDashboardAlcalde(): bool
    {
        return self::can('dashboard.alcalde.view');
    }

    public static function showDashboardSecretario(): bool
    {
        return self::can('dashboard.secretario.view');
    }

    public static function showVisitasGobernador(): bool
    {
        return self::canAny([
            'visitas.gobernador.view',
            'visitas.gobernador.mapa.view',
            'visitas.gobernador.cuadro.view',
            'compromisos.gobernador.view',
            'compromisos.gobernador.cumplimiento.view',
            'compromisos.gobernador.visor.view',
        ]);
    }

    public static function showGestionSocial(): bool
    {
        return self::canAny([
            'gestion_social.view',
            'gestion_social.create',
            'gestion_social.update',
        ]);
    }

    public static function showGestionSocialAspas(): bool
    {
        return self::canAny([
            'gestion_social.aspas.view',
            'gestion_social.aspas.create',
            'gestion_social.aspas.update',
        ]);
    }

    public static function showPlanDesarrollo(): bool
    {
        return self::canAny([
            'plan_desarrollo.view',
            'plan_desarrollo.mapa_comparativo.view',
        ]);
    }

    public static function showSecretarias(): bool
    {
        return self::canAny([
            'secretarias.resumen.view',
            'secretarias.comparativo.view',
            'secretarias.pae.view',
            'secretarias.hacienda.view',
            'secretarias.administrativa.view',
            'secretarias.tic.view',
            'secretarias.proyectos.view',
            'secretarias.rpc.view',
        ]);
    }

    public static function showInterior(): bool
    {
        return self::canAny([
            'interior.formulario.view',
            'interior.boletin.view',
            'interior.contratos.view',
            'interior.resultados.view',
        ]);
    }

    public static function showDeportes(): bool
    {
        return self::canAny([
            'deportes.deportistas.view',
            'deportes.listado.view',
        ]);
    }

    public static function showConfiguracion(): bool
    {
        return self::canAny([
            'configuracion.sistema.view',
            'configuracion.usuarios.view',
            'configuracion.roles.view',
            'configuracion.roles.manage',
            'configuracion.secretarias.view',
            'configuracion.ministerios.view',
            'configuracion.lineas.view',
            'configuracion.estrategias.view',
            'configuracion.acciones_gestion.view',
            'configuracion.sesiones.view',
            'accion_unificada.config.puntajes.view',
            'secretarias.config_puntajes.view',
            'configuracion.veredas.manage',
        ]);
    }

    public static function showVisitasAlcalde(): bool
    {
        return self::canAny([
            'visitas.alcalde.view',
            'visitas.alcalde.mapa.view',
            'visitas.alcalde.cuadro.view',
            'compromisos.alcalde.view',
            'compromisos.alcalde.cumplimiento.view',
            'compromisos.alcalde.visor.view',
            'secretarias.municipales.view',
            'secretarias.componentes.view',
        ]);
    }

    public static function showPlanDesarrolloAlcalde(): bool
    {
        return self::can('plan_desarrollo.view');
    }

    public static function showPlaneacionAlcaldia(): bool
    {
        return self::can('proyectos.alcaldias.planeacion.view');
    }

    public static function showSecretariasAlcaldias(): bool
    {
        return self::canAny([
            'secretarias.resumen.view',
            'secretarias.proyectos.view',
            'proyectos.alcaldias.secretarias.view',
        ]);
    }

    public static function showProyectosAlcaldias(): bool
    {
        return self::canAny([
            'proyectos.alcaldias.view',
            'proyectos.alcaldias.resumen.view',
            'proyectos.alcaldias.seguimiento.view',
        ]);
    }

    public static function showAccionUnificada(): bool
    {
        return self::canAny([
            'accion_unificada.departamento.view',
            'accion_unificada.municipios.view',
            'accion_unificada.veredas_criticas.view',
            'accion_unificada.factores_listado.view',
            'accion_unificada.informes.view',
            'accion_unificada.empresas.view',
            'accion_unificada.imagenes.view',
            'accion_unificada.avances.view',
            'accion_unificada.estadisticas_bd.view',
        ]);
    }

    public static function showConfigAccionUnificada(): bool
    {
        return self::canAny([
            'accion_unificada.config.areas.view',
            'accion_unificada.config.factores.view',
            'accion_unificada.config.factores_gobernacion.view',
            'accion_unificada.config.actores.view',
            'accion_unificada.config.informacion.view',
            'accion_unificada.config.actualizacion.view',
        ]);
    }

    public static function showPolicia(): bool
    {
        return self::canAny([
            'policia.informes.view',
            'policia.graficos.view',
        ]);
    }

    public static function showEstrategicos(): bool
    {
        return self::can('estrategicos.departamento.view');
    }

    public static function showSeguimientoAlcaldiasAdmin(): bool
    {
        return self::can('seguimiento.alcaldias.admin.view');
    }

    public static function showIA(): bool
    {
        return self::canAny([
            'ia.asesor_despacho.view',
            'ia.contratacion.view',
        ]);
    }
}
