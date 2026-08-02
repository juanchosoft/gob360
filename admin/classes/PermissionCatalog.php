<?php

/**
 * Catálogo único de permisos del sistema.
 * Fuente de verdad para seeds SQL, documentación y bridge legacy ID → KEY.
 */
final class PermissionCatalog
{
    /**
     * @return array<int, array{key: string, module: string, action: string, name: string, legacy_id: int|null}>
     */
    public static function definitions(): array
    {
        $items = [];

        $add = static function (
            string $key,
            string $module,
            string $action,
            string $name,
            ?int $legacyId = null
        ) use (&$items): void {
            $items[] = [
                'key' => $key,
                'module' => $module,
                'action' => $action,
                'name' => $name,
                'legacy_id' => $legacyId,
            ];
        };

        // ── Configuración general ────────────────────────────────────────
        $add('configuracion.usuarios.view', 'configuracion', 'view', 'Usuarios - Ver', 1);
        $add('configuracion.usuarios.create', 'configuracion', 'create', 'Usuarios - Crear', 2);
        $add('configuracion.usuarios.update', 'configuracion', 'update', 'Usuarios - Editar', 3);
        $add('configuracion.usuarios.manage', 'configuracion', 'manage', 'Usuarios - Gestionar permisos', 4);
        $add('configuracion.roles.view', 'configuracion', 'view', 'Roles - Ver');
        $add('configuracion.roles.manage', 'configuracion', 'manage', 'Roles - Gestionar');
        $add('configuracion.sistema.view', 'configuracion', 'view', 'Configuración sistema - Ver');
        $add('configuracion.sistema.update', 'configuracion', 'update', 'Configuración sistema - Editar');
        $add('configuracion.sesiones.view', 'configuracion', 'view', 'Sesiones de usuarios - Ver');
        $add('configuracion.secretarias.view', 'configuracion', 'view', 'Secretarías entidades - Ver', 5);
        $add('configuracion.secretarias.create', 'configuracion', 'create', 'Secretarías entidades - Crear', 6);
        $add('configuracion.secretarias.update', 'configuracion', 'update', 'Secretarías entidades - Editar', 7);
        $add('configuracion.ministerios.view', 'configuracion', 'view', 'Ministerios entidades - Ver');
        $add('configuracion.ministerios.create', 'configuracion', 'create', 'Ministerios entidades - Crear');
        $add('configuracion.ministerios.update', 'configuracion', 'update', 'Ministerios entidades - Editar');
        $add('configuracion.lineas.view', 'configuracion', 'view', 'Líneas gestión social - Ver');
        $add('configuracion.lineas.create', 'configuracion', 'create', 'Líneas gestión social - Crear');
        $add('configuracion.lineas.update', 'configuracion', 'update', 'Líneas gestión social - Editar');
        $add('configuracion.estrategias.view', 'configuracion', 'view', 'Estrategias - Ver', 83);
        $add('configuracion.estrategias.create', 'configuracion', 'create', 'Estrategias - Crear', 84);
        $add('configuracion.estrategias.update', 'configuracion', 'update', 'Estrategias - Editar', 85);
        $add('configuracion.acciones_gestion.view', 'configuracion', 'view', 'Acciones gestión social - Ver');
        $add('configuracion.acciones_gestion.create', 'configuracion', 'create', 'Acciones gestión social - Crear');
        $add('configuracion.acciones_gestion.update', 'configuracion', 'update', 'Acciones gestión social - Editar');
        $add('configuracion.acciones_gestion.delete', 'configuracion', 'delete', 'Acciones gestión social - Eliminar');
        $add('configuracion.veredas.manage', 'configuracion', 'manage', 'Gestión veredas (admin) - Gestionar');

        // ── Dashboards ─────────────────────────────────────────────────
        $add('dashboard.admin.view', 'dashboard', 'view', 'Dashboard administrador - Ver');
        $add('dashboard.alcalde.view', 'dashboard', 'view', 'Dashboard alcalde - Ver');
        $add('dashboard.secretario.view', 'dashboard', 'view', 'Dashboard secretaría - Ver');
        $add('dashboard.mapa.view', 'dashboard', 'view', 'Mapa / índice general - Ver', 70);
        $add('dashboard.mapa.create', 'dashboard', 'create', 'Mapa / índice general - Crear', 71);
        $add('dashboard.mapa.update', 'dashboard', 'update', 'Mapa / índice general - Editar', 72);

        // ── Visitas gobernador ───────────────────────────────────────────
        $add('visitas.gobernador.view', 'visitas', 'view', 'Visitas gobernador - Ver', 14);
        $add('visitas.gobernador.create', 'visitas', 'create', 'Visitas gobernador - Crear', 15);
        $add('visitas.gobernador.update', 'visitas', 'update', 'Visitas gobernador - Editar', 16);
        $add('visitas.gobernador.mapa.view', 'visitas', 'view', 'Mapa visitas gobernador - Ver');
        $add('visitas.gobernador.cuadro.view', 'visitas', 'view', 'Cuadro control visitas gobernador - Ver');

        // ── Visitas alcalde ──────────────────────────────────────────────
        $add('visitas.alcalde.view', 'visitas', 'view', 'Visitas alcalde - Ver');
        $add('visitas.alcalde.create', 'visitas', 'create', 'Visitas alcalde - Crear');
        $add('visitas.alcalde.update', 'visitas', 'update', 'Visitas alcalde - Editar');
        $add('visitas.alcalde.mapa.view', 'visitas', 'view', 'Mapa visitas alcalde - Ver');
        $add('visitas.alcalde.cuadro.view', 'visitas', 'view', 'Cuadro control visitas alcalde - Ver');

        // ── Compromisos ──────────────────────────────────────────────────
        $add('compromisos.gobernador.view', 'compromisos', 'view', 'Compromisos gobernador - Ver');
        $add('compromisos.gobernador.create', 'compromisos', 'create', 'Compromisos gobernador - Crear');
        $add('compromisos.gobernador.update', 'compromisos', 'update', 'Compromisos gobernador - Editar');
        $add('compromisos.gobernador.approve', 'compromisos', 'approve', 'Compromisos gobernador - Aprobar');
        $add('compromisos.gobernador.cumplimiento.view', 'compromisos', 'view', 'Gestión cumplimiento gobernador - Ver');
        $add('compromisos.gobernador.visor.view', 'compromisos', 'view', 'Visor compromisos gobernador - Ver');
        $add('compromisos.alcalde.view', 'compromisos', 'view', 'Compromisos alcalde - Ver');
        $add('compromisos.alcalde.create', 'compromisos', 'create', 'Compromisos alcalde - Crear');
        $add('compromisos.alcalde.update', 'compromisos', 'update', 'Compromisos alcalde - Editar');
        $add('compromisos.alcalde.approve', 'compromisos', 'approve', 'Compromisos alcalde - Aprobar');
        $add('compromisos.alcalde.cumplimiento.view', 'compromisos', 'view', 'Gestión cumplimiento alcalde - Ver');
        $add('compromisos.alcalde.visor.view', 'compromisos', 'view', 'Visor compromisos alcalde - Ver');

        // ── Gestión social ───────────────────────────────────────────────
        $add('gestion_social.view', 'gestion_social', 'view', 'Gestión social - Ver', 29);
        $add('gestion_social.create', 'gestion_social', 'create', 'Gestión social - Crear', 30);
        $add('gestion_social.update', 'gestion_social', 'update', 'Gestión social - Editar', 31);
        $add('gestion_social.delete', 'gestion_social', 'delete', 'Gestión social - Eliminar', 32);
        $add('gestion_social.aspas.view', 'gestion_social', 'view', 'Gestión social ASPAS - Ver');
        $add('gestion_social.aspas.create', 'gestion_social', 'create', 'Gestión social ASPAS - Crear');
        $add('gestion_social.aspas.update', 'gestion_social', 'update', 'Gestión social ASPAS - Editar');

        // ── Plan desarrollo ──────────────────────────────────────────────
        $add('plan_desarrollo.view', 'plan_desarrollo', 'view', 'Plan desarrollo - Ver');
        $add('plan_desarrollo.create', 'plan_desarrollo', 'create', 'Plan desarrollo - Crear');
        $add('plan_desarrollo.update', 'plan_desarrollo', 'update', 'Plan desarrollo - Editar');
        $add('plan_desarrollo.mapa_comparativo.view', 'plan_desarrollo', 'view', 'Mapa comparativo - Ver');

        // ── Secretarías ──────────────────────────────────────────────────
        $add('secretarias.resumen.view', 'secretarias', 'view', 'Resumen secretarías - Ver');
        $add('secretarias.comparativo.view', 'secretarias', 'view', 'Comparativo secretarías - Ver');
        $add('secretarias.pae.view', 'secretarias', 'view', 'Información PAE - Ver');
        $add('secretarias.pae.create', 'secretarias', 'create', 'Información PAE - Crear');
        $add('secretarias.pae.update', 'secretarias', 'update', 'Información PAE - Editar');
        $add('secretarias.pae.dashboard.view', 'secretarias', 'view', 'Dashboard PAE - Ver');
        $add('secretarias.pae.logs.view', 'secretarias', 'view', 'Logs API PAE - Ver');
        $add('secretarias.pae.logs.manage', 'secretarias', 'manage', 'Logs API PAE - Limpiar');
        $add('secretarias.hacienda.view', 'secretarias', 'view', 'Información Hacienda - Ver');
        $add('secretarias.hacienda.create', 'secretarias', 'create', 'Información Hacienda - Crear');
        $add('secretarias.hacienda.update', 'secretarias', 'update', 'Información Hacienda - Editar');
        $add('secretarias.hacienda.import', 'secretarias', 'create', 'Carga masiva Hacienda - Importar');
        $add('secretarias.hacienda.dashboard.view', 'secretarias', 'view', 'Dashboard Hacienda - Ver');
        $add('secretarias.administrativa.view', 'secretarias', 'view', 'Información administrativa - Ver');
        $add('secretarias.administrativa.create', 'secretarias', 'create', 'Información administrativa - Crear');
        $add('secretarias.administrativa.update', 'secretarias', 'update', 'Información administrativa - Editar');
        $add('secretarias.administrativa.dashboard.view', 'secretarias', 'view', 'Dashboard administrativa - Ver');
        $add('secretarias.tic.view', 'secretarias', 'view', 'Información TIC - Ver');
        $add('secretarias.tic.create', 'secretarias', 'create', 'Información TIC - Crear');
        $add('secretarias.tic.update', 'secretarias', 'update', 'Información TIC - Editar');
        $add('secretarias.tic.dashboard.view', 'secretarias', 'view', 'Dashboard TIC - Ver');
        $add('secretarias.proyectos.view', 'secretarias', 'view', 'Proyectos secretarías - Ver', 73);
        $add('secretarias.proyectos.create', 'secretarias', 'create', 'Proyectos secretarías - Crear', 74);
        $add('secretarias.proyectos.update', 'secretarias', 'update', 'Proyectos secretarías - Editar', 75);
        $add('secretarias.proyectos.approve', 'secretarias', 'approve', 'Proyectos secretarías - Aprobar');
        $add('secretarias.proyectos.seguimiento.view', 'secretarias', 'view', 'Seguimiento proyectos secretarías - Ver');
        $add('secretarias.rpc.view', 'secretarias', 'view', 'Proyectos API RPC - Ver');
        $add('secretarias.rpc.logs.view', 'secretarias', 'view', 'Logs API RPC - Ver');
        $add('secretarias.rpc.logs.manage', 'secretarias', 'manage', 'Logs API RPC - Limpiar');
        $add('secretarias.infraestructura.dashboard.view', 'secretarias', 'view', 'Dashboard infraestructura - Ver');
        $add('secretarias.config_puntajes.view', 'secretarias', 'view', 'Config puntajes secretaría - Ver', 80);
        $add('secretarias.config_puntajes.create', 'secretarias', 'create', 'Config puntajes secretaría - Crear', 81);
        $add('secretarias.config_puntajes.update', 'secretarias', 'update', 'Config puntajes secretaría - Editar', 82);
        $add('secretarias.municipales.view', 'secretarias', 'view', 'Secretarías municipales - Ver');
        $add('secretarias.municipales.create', 'secretarias', 'create', 'Secretarías municipales - Crear');
        $add('secretarias.municipales.update', 'secretarias', 'update', 'Secretarías municipales - Editar');
        $add('secretarias.componentes.view', 'secretarias', 'view', 'Componentes municipales - Ver');
        $add('secretarias.componentes.create', 'secretarias', 'create', 'Componentes municipales - Crear');
        $add('secretarias.componentes.update', 'secretarias', 'update', 'Componentes municipales - Editar');
        $add('secretarias.componentes.manage', 'secretarias', 'manage', 'Componentes municipales - Gestionar');

        // ── Interior / seguridad ─────────────────────────────────────────
        $add('interior.formulario.view', 'interior', 'view', 'Formulario estadística seguridad - Ver');
        $add('interior.formulario.create', 'interior', 'create', 'Formulario estadística seguridad - Crear');
        $add('interior.formulario.update', 'interior', 'update', 'Formulario estadística seguridad - Editar');
        $add('interior.boletin.view', 'interior', 'view', 'Boletín estratégico seguridad - Ver');
        $add('interior.contratos.view', 'interior', 'view', 'Registro contratos interior - Ver');
        $add('interior.contratos.create', 'interior', 'create', 'Registro contratos interior - Crear');
        $add('interior.contratos.update', 'interior', 'update', 'Registro contratos interior - Editar');
        $add('interior.resultados.view', 'interior', 'view', 'Resultados inversión seguridad - Ver');

        // ── Deportes ─────────────────────────────────────────────────────
        $add('deportes.deportistas.view', 'deportes', 'view', 'Deportistas - Ver');
        $add('deportes.deportistas.create', 'deportes', 'create', 'Deportistas - Crear');
        $add('deportes.deportistas.update', 'deportes', 'update', 'Deportistas - Editar');
        $add('deportes.listado.view', 'deportes', 'view', 'Listado deportistas - Ver');

        // ── Proyectos alcaldías ──────────────────────────────────────────
        $add('proyectos.alcaldias.view', 'proyectos', 'view', 'Proyectos alcaldías - Ver', 17);
        $add('proyectos.alcaldias.create', 'proyectos', 'create', 'Proyectos alcaldías - Crear', 18);
        $add('proyectos.alcaldias.update', 'proyectos', 'update', 'Proyectos alcaldías - Editar', 19);
        $add('proyectos.alcaldias.delete', 'proyectos', 'delete', 'Proyectos alcaldías - Eliminar');
        $add('proyectos.alcaldias.seguimiento.view', 'proyectos', 'view', 'Seguimiento proyectos alcaldías - Ver', 20);
        $add('proyectos.alcaldias.seguimiento.create', 'proyectos', 'create', 'Seguimiento proyectos alcaldías - Crear', 21);
        $add('proyectos.alcaldias.seguimiento.update', 'proyectos', 'update', 'Seguimiento proyectos alcaldías - Editar', 22);
        $add('proyectos.alcaldias.resumen.view', 'proyectos', 'view', 'Resumen alcaldías - Ver');
        $add('proyectos.alcaldias.planeacion.view', 'proyectos', 'view', 'Proyectos planeación alcaldía - Ver');
        $add('proyectos.alcaldias.planeacion.create', 'proyectos', 'create', 'Proyectos planeación alcaldía - Crear');
        $add('proyectos.alcaldias.planeacion.detail', 'proyectos', 'view', 'Proyectos planeación alcaldía - Ver detalle');
        $add('proyectos.alcaldias.planeacion.manage', 'proyectos', 'approve', 'Proyectos planeación alcaldía - Gestionar');
        $add('proyectos.alcaldias.planeacion.reopen', 'proyectos', 'update', 'Proyectos planeación alcaldía - Reabrir');
        $add('proyectos.alcaldias.planeacion.view_all', 'proyectos', 'view', 'Proyectos planeación - Ver todos del departamento');
        $add('proyectos.alcaldias.planeacion.view_all_alcaldia', 'proyectos', 'view', 'Proyectos planeación - Ver todos de la alcaldía');
        $add('proyectos.alcaldias.planeacion.assign', 'proyectos', 'manage', 'Proyectos planeación - Asignar usuarios');
        $add('proyectos.alcaldias.planeacion.dashboard', 'proyectos', 'view', 'Proyectos planeación alcaldía - Dashboard');
        $add('proyectos.alcaldias.planeacion.informes', 'proyectos', 'view', 'Proyectos planeación - Informes de gestión');
        $add('proyectos.alcaldias.secretarias.view', 'proyectos', 'view', 'Proyectos secretarías alcaldía - Ver');
        $add('seguimiento.alcaldias.admin.view', 'seguimiento', 'view', 'Seguimiento alcaldías (admin) - Ver');

        // ── Acción unificada ─────────────────────────────────────────────
        $add('accion_unificada.departamento.view', 'accion_unificada', 'view', 'Mapa departamento inestabilidad - Ver', 39);
        $add('accion_unificada.municipios.view', 'accion_unificada', 'view', 'Mapa municipios inestabilidad - Ver', 40);
        $add('accion_unificada.municipios.create', 'accion_unificada', 'create', 'Municipios acción unificada - Crear', 41);
        $add('accion_unificada.municipios.update', 'accion_unificada', 'update', 'Municipios acción unificada - Editar', 42);
        $add('accion_unificada.veredas.view', 'accion_unificada', 'view', 'Veredas acción unificada - Ver', 43);
        $add('accion_unificada.veredas.create', 'accion_unificada', 'create', 'Veredas acción unificada - Crear', 44);
        $add('accion_unificada.veredas.update', 'accion_unificada', 'update', 'Veredas acción unificada - Editar', 45);
        $add('accion_unificada.veredas_criticas.view', 'accion_unificada', 'view', 'Veredas críticas - Ver', 46);
        $add('accion_unificada.veredas_criticas.create', 'accion_unificada', 'create', 'Veredas críticas - Crear', 47);
        $add('accion_unificada.veredas_criticas.update', 'accion_unificada', 'update', 'Veredas críticas - Editar', 48);
        $add('accion_unificada.factores_listado.view', 'accion_unificada', 'view', 'Listado factores generales - Ver');
        $add('accion_unificada.informes.view', 'accion_unificada', 'view', 'Informes acción unificada - Ver');
        $add('accion_unificada.empresas.view', 'accion_unificada', 'view', 'Empresas municipio - Ver');
        $add('accion_unificada.empresas.create', 'accion_unificada', 'create', 'Empresas municipio - Crear');
        $add('accion_unificada.empresas.update', 'accion_unificada', 'update', 'Empresas municipio - Editar');
        $add('accion_unificada.empresas.delete', 'accion_unificada', 'delete', 'Empresas municipio - Eliminar');
        $add('accion_unificada.empresa_factor.view', 'accion_unificada', 'view', 'Empresa-Factor municipio - Ver');
        $add('accion_unificada.empresa_factor.create', 'accion_unificada', 'create', 'Empresa-Factor municipio - Crear');
        $add('accion_unificada.empresa_factor.update', 'accion_unificada', 'update', 'Empresa-Factor municipio - Editar');
        $add('accion_unificada.empresa_factor.delete', 'accion_unificada', 'delete', 'Empresa-Factor municipio - Eliminar');
        $add('accion_unificada.avances.view', 'accion_unificada', 'view', 'Avances - Ver', 49);
        $add('accion_unificada.avances.create', 'accion_unificada', 'create', 'Avances - Crear', 50);
        $add('accion_unificada.avances.update', 'accion_unificada', 'update', 'Avances - Editar', 51);
        $add('accion_unificada.imagenes.view', 'accion_unificada', 'view', 'Imágenes acción unificada - Ver', 24);
        $add('accion_unificada.imagenes.create', 'accion_unificada', 'create', 'Imágenes acción unificada - Crear', 23);
        $add('accion_unificada.imagenes.update', 'accion_unificada', 'update', 'Imágenes acción unificada - Editar', 25);
        $add('accion_unificada.imagenes_historial.view', 'accion_unificada', 'view', 'Imágenes historial - Ver', 52);
        $add('accion_unificada.imagenes_historial.create', 'accion_unificada', 'create', 'Imágenes historial - Crear', 53);
        $add('accion_unificada.imagenes_historial.update', 'accion_unificada', 'update', 'Imágenes historial - Editar', 54);
        $add('accion_unificada.estadisticas_bd.view', 'accion_unificada', 'view', 'Estadísticas BD - Ver');
        $add('accion_unificada.config.areas.view', 'accion_unificada', 'view', 'Ingreso áreas - Ver', 76);
        $add('accion_unificada.config.areas.create', 'accion_unificada', 'create', 'Ingreso áreas - Crear', 77);
        $add('accion_unificada.config.areas.update', 'accion_unificada', 'update', 'Ingreso áreas - Editar', 79);
        $add('accion_unificada.config.factores.view', 'accion_unificada', 'view', 'Ingreso factores - Ver', 55);
        $add('accion_unificada.config.factores.create', 'accion_unificada', 'create', 'Ingreso factores - Crear', 56);
        $add('accion_unificada.config.factores.update', 'accion_unificada', 'update', 'Ingreso factores - Editar', 57);
        $add('accion_unificada.config.factores_gobernacion.view', 'accion_unificada', 'view', 'Factores inestabilidad gobernación - Ver');
        $add('accion_unificada.config.factores_gobernacion.create', 'accion_unificada', 'create', 'Factores inestabilidad gobernación - Crear');
        $add('accion_unificada.config.factores_gobernacion.update', 'accion_unificada', 'update', 'Factores inestabilidad gobernación - Editar');
        $add('accion_unificada.config.actores.view', 'accion_unificada', 'view', 'Ingreso actores - Ver', 58);
        $add('accion_unificada.config.actores.create', 'accion_unificada', 'create', 'Ingreso actores - Crear', 59);
        $add('accion_unificada.config.actores.update', 'accion_unificada', 'update', 'Ingreso actores - Editar', 60);
        $add('accion_unificada.config.informacion.view', 'accion_unificada', 'view', 'Ingreso información - Ver', 61);
        $add('accion_unificada.config.informacion.create', 'accion_unificada', 'create', 'Ingreso información - Crear', 62);
        $add('accion_unificada.config.informacion.update', 'accion_unificada', 'update', 'Ingreso información - Editar', 63);
        $add('accion_unificada.config.actualizacion.view', 'accion_unificada', 'view', 'Actualización información - Ver', 64);
        $add('accion_unificada.config.actualizacion.create', 'accion_unificada', 'create', 'Actualización información - Crear', 65);
        $add('accion_unificada.config.actualizacion.update', 'accion_unificada', 'update', 'Actualización información - Editar', 66);
        $add('accion_unificada.config.puntajes.view', 'accion_unificada', 'view', 'Config puntajes gobernación - Ver', 67);
        $add('accion_unificada.config.puntajes.create', 'accion_unificada', 'create', 'Config puntajes gobernación - Crear', 68);
        $add('accion_unificada.config.puntajes.update', 'accion_unificada', 'update', 'Config puntajes gobernación - Editar', 69);

        // ── Estado municipios (legacy mapa) ──────────────────────────────
        $add('estado.municipios.view', 'estado', 'view', 'Estado municipios - Ver', 11);
        $add('estado.municipios.create', 'estado', 'create', 'Estado municipios - Crear', 12);
        $add('estado.municipios.update', 'estado', 'update', 'Estado municipios - Editar', 13);

        // ── Policía ──────────────────────────────────────────────────────
        $add('policia.informes.view', 'policia', 'view', 'Informes policía - Ver');
        $add('policia.graficos.view', 'policia', 'view', 'Gráficos policía - Ver');

        // ── Proyectos estratégicos ─────────────────────────────────────────
        $add('estrategicos.departamento.view', 'estrategicos', 'view', 'Proyectos estratégicos departamento - Ver');
        $add('estrategicos.departamento.create', 'estrategicos', 'create', 'Proyectos estratégicos - Crear');
        $add('estrategicos.departamento.update', 'estrategicos', 'update', 'Proyectos estratégicos - Editar');

        // ── Prensa (legacy) ──────────────────────────────────────────────
        $add('prensa.view', 'prensa', 'view', 'Prensa - Ver', 26);
        $add('prensa.create', 'prensa', 'create', 'Prensa - Crear', 28);
        $add('prensa.update', 'prensa', 'update', 'Prensa - Editar', 27);

        // ── Asistente IA (legacy) ─────────────────────────────────────────
        $add('ia.asesor_despacho.view', 'ia', 'view', 'Asesor despacho IA - Ver');
        $add('ia.contratacion.view', 'ia', 'view', 'Asesor contratación IA - Ver');

        // ── Asistente IA Claude (widget nuevo) ───────────────────────────
        $add('asistente_ia.chat.use',  'asistente_ia', 'view', 'Asistente IA - Usar chat de texto');
        $add('asistente_ia.voz.use',   'asistente_ia', 'view', 'Asistente IA - Usar modo voz/en vivo');
        $add('asistente_ia.pdf.use',   'asistente_ia', 'view', 'Asistente IA - Exportar informes a PDF');
        $add('asistente_ia.logs.view', 'asistente_ia', 'view', 'Asistente IA - Ver logs de auditoría');

        return $items;
    }

    /** @return array<string, int> key => legacy_id */
    public static function legacyKeyToId(): array
    {
        $map = [];
        foreach (self::definitions() as $def) {
            if ($def['legacy_id'] !== null) {
                $map[$def['key']] = $def['legacy_id'];
            }
        }
        return $map;
    }

    /** @return array<int, string> legacy_id => key (primer match) */
    public static function legacyIdToKey(): array
    {
        $map = [];
        foreach (self::definitions() as $def) {
            if ($def['legacy_id'] !== null && !isset($map[$def['legacy_id']])) {
                $map[$def['legacy_id']] = $def['key'];
            }
        }
        return $map;
    }

    /** @return string[] */
    public static function allKeys(): array
    {
        return array_column(self::definitions(), 'key');
    }

    /** @return array<string, array<int, array>> */
    public static function groupedByModule(): array
    {
        $grouped = [];
        foreach (self::definitions() as $def) {
            $grouped[$def['module']][] = $def;
        }
        ksort($grouped);
        return $grouped;
    }
}
