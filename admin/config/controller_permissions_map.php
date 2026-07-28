<?php

/**
 * Mapa controller => método/op/_entrypoint => permiso.
 *
 * Formatos:
 * - '' => solo sesión
 * - 'key' => un permiso
 * - ['create'=>..,'update'=>..] => según id
 *
 * Fail-closed: método o script ausente = 403.
 *
 * @return array<string, array<string, mixed>>
 */
return [
    'accionGCtrl.php' => [
        'load' => 'configuracion.acciones_gestion.view',
        'getAccionG' => 'configuracion.acciones_gestion.view',
        'createAccionG' => 'configuracion.acciones_gestion.create',
        'updateAccionG' => 'configuracion.acciones_gestion.update',
    ],

    'alcaldiaCtrl.php' => [
        'getAllproyectos' => 'proyectos.alcaldias.view',
        'editProyecto' => 'proyectos.alcaldias.view',
        'delete' => 'proyectos.alcaldias.delete',
    ],

    'apiCtrl.php' => [
        'cargaHurto' => 'policia.informes.view',
        'cargaCategoria' => 'policia.informes.view',
        'cargaCategoriaGrafico' => 'policia.graficos.view',
    ],

    'componenteMunicipiosCtrl.php' => [
        'load' => 'secretarias.componentes.view',
        'getById' => 'secretarias.componentes.view',
        'editComponente' => 'secretarias.componentes.view',
        'newComponente' => 'secretarias.componentes.create',
        'updateComponente' => 'secretarias.componentes.update',
        'delete' => 'secretarias.componentes.manage',
    ],

    'configuracionCtrl.php' => [
        'load' => 'accion_unificada.config.puntajes.view',
        'ejes' => 'accion_unificada.config.puntajes.view',
        'categoriasInestabilidad' => 'accion_unificada.config.factores_gobernacion.view',
        'insertPuntaje' => 'accion_unificada.config.puntajes.create',
        'editPuntaje' => 'accion_unificada.config.puntajes.view',
        'loadConfigSecretaria' => 'secretarias.config_puntajes.view',
        'editConfigSecretaria' => 'secretarias.config_puntajes.view',
        'configuracionSecretariaPuntajeSave' => 'secretarias.config_puntajes.create',
        'editConfiguracionSecretariaPuntajeSave' => 'secretarias.config_puntajes.update',
    ],

    'compromisoMunicipioCtrl.php' => [
        'guardaVisita' => 'visitas.gobernador.create',
        'actualizarVisita' => 'visitas.gobernador.update',
        'getAllVisitas' => 'visitas.gobernador.view',
        'getVisitaId' => 'visitas.gobernador.view',
        'indicadores' => 'visitas.gobernador.view',
        'indicadoresTipoVisita' => 'visitas.gobernador.view',
        'actualizarCompromiso' => 'compromisos.gobernador.update',
        'guardarObservacion' => 'compromisos.gobernador.update',
        'ejecutarTrasladoPorCompetencia' => 'compromisos.gobernador.update',
        'deleteCompromiso' => 'compromisos.gobernador.update',
        'exportarCompromisosExcel' => 'compromisos.gobernador.view',
        'getAllCompromise' => 'compromisos.gobernador.view',
        'getAllCompromiseFiltrosSelect' => 'compromisos.gobernador.view',
        'getAllCompromiseEnEspera' => 'compromisos.gobernador.view',
        'getAllCompromiseFiltrosSelectEnEstadoEspera' => 'compromisos.gobernador.view',
        'getAllCompromisecumplidos' => 'compromisos.gobernador.view',
        'getAllCompromisecumplidosFiltrosSelect' => 'compromisos.gobernador.view',
        'getCompromisoId' => 'compromisos.gobernador.view',
        'getCompromisoHistorial' => 'compromisos.gobernador.view',
        'graficoSeguimiento' => 'compromisos.gobernador.cumplimiento.view',
        'porcentaje' => 'compromisos.gobernador.cumplimiento.view',
        'dataMapa' => 'compromisos.gobernador.cumplimiento.view',
        'dataPorMunicipioSecretaria' => 'compromisos.gobernador.cumplimiento.view',
        'dataPorMunicipioSecretariaTodosLosEstados' => 'compromisos.gobernador.cumplimiento.view',
        'mapa' => 'gestion_social.view',
    ],

    'compromisoMunicipioAlcaldeCtrl.php' => [
        'guardaVisita' => 'visitas.alcalde.create',
        'actualizarVisita' => 'visitas.alcalde.update',
        'getAllVisitas' => 'visitas.alcalde.view',
        'getVisitaId' => 'visitas.alcalde.view',
        'indicadoresTipoVisita' => 'visitas.alcalde.view',
        'actualizarCompromiso' => 'compromisos.alcalde.update',
        'saveCompromiso' => [
            'create' => 'compromisos.alcalde.create',
            'update' => 'compromisos.alcalde.update',
        ],
        'deleteCompromiso' => 'compromisos.alcalde.update',
        'aprobarCompromiso' => 'compromisos.alcalde.approve',
        'ejecutarTrasladoPorCompetencia' => 'compromisos.alcalde.update',
        'getAllCompromisos' => 'compromisos.alcalde.view',
        'getAllCompromisosEnTramite' => 'compromisos.alcalde.view',
        'getAllCompromisosSinCumplir' => 'compromisos.alcalde.view',
        'getAllCompromisosCumplidos' => 'compromisos.alcalde.view',
        'getAllCompromiseEnEspera' => 'compromisos.alcalde.view',
        'getAllCompromiseFiltrosSelectEnEstadoEspera' => 'compromisos.alcalde.view',
        'getCompromisoId' => 'compromisos.alcalde.view',
        'getIndicadoresCompromisosSecretaria' => 'compromisos.alcalde.view',
        'graficoSeguimiento' => 'compromisos.alcalde.cumplimiento.view',
        'porcentaje' => 'compromisos.alcalde.cumplimiento.view',
        'dataMapa' => 'compromisos.alcalde.cumplimiento.view',
        'dataPorMunicipioSecretaria' => 'compromisos.alcalde.cumplimiento.view',
        'dataPorMunicipioSecretariaTodosLosEstados' => 'compromisos.alcalde.cumplimiento.view',
    ],

    'compromisoPlantillaCtrl.php' => [
        '_entrypoint' => 'compromisos.gobernador.view',
    ],

    'empresasCtrl.php' => [
        'load' => 'accion_unificada.empresas.view',
        'editEmpresa' => 'accion_unificada.empresas.view',
    ],

    'estrategiaCtrl.php' => [
        'load' => 'configuracion.estrategias.view',
        'editEstrategia' => 'configuracion.estrategias.view',
        'newEstrategia' => 'configuracion.estrategias.create',
        'updateEstrategia' => 'configuracion.estrategias.update',
    ],

    'get_municipios.php' => [
        '_entrypoint' => '',
    ],

    'haciendaPlantillaCtrl.php' => [
        '_entrypoint' => 'secretarias.hacienda.import',
    ],

    'ingresoInformacionCtrl.php' => [
        'load' => 'accion_unificada.config.informacion.view',
    ],

    'inversionCtrl.php' => [
        'inversion_list' => 'interior.contratos.view',
        'inversion_get' => 'interior.contratos.view',
        'inversion_save' => 'interior.contratos.create',
        'inversion_update' => 'interior.contratos.update',
        'inversion_delete' => 'interior.contratos.update',
    ],

    'lineaCtrl.php' => [
        'load' => 'configuracion.lineas.view',
        'getLinea' => 'configuracion.lineas.view',
        'createLinea' => 'configuracion.lineas.create',
        'updateLinea' => 'configuracion.lineas.update',
    ],

    'ministeriosCtrl.php' => [
        'load' => 'configuracion.ministerios.view',
        'getMinisterio' => 'configuracion.ministerios.view',
        'createMinisterio' => 'configuracion.ministerios.create',
        'updateMinisterio' => 'configuracion.ministerios.update',
    ],

    'planDesarrolloAlcaldeCtrl.php' => [
        'downloadTemplate' => 'plan_desarrollo.view',
        'uploadExcel' => 'plan_desarrollo.create',
        'getAll' => 'plan_desarrollo.view',
        'load' => 'plan_desarrollo.view',
        'delete' => 'plan_desarrollo.update',
        'deleteMultiple' => 'plan_desarrollo.update',
    ],

    'secretariaCtrl.php' => [
        'load' => 'configuracion.secretarias.view',
        'editSecretaria' => 'configuracion.secretarias.view',
        'newSecretaria' => 'configuracion.secretarias.create',
        'updateSecretaria' => 'configuracion.secretarias.update',
    ],

    'secretariasMunicipiosCtrl.php' => [
        'load' => 'secretarias.municipales.view',
        'editSecretaria' => 'secretarias.municipales.view',
        'newSecretaria' => 'secretarias.municipales.create',
        'updateSecretaria' => 'secretarias.municipales.update',
    ],

    'utilsCtrl.php' => [
        'ciudades' => '',
        'secretaria' => '',
        'getVeredasByMunicipioId' => '',
    ],

    'usuarioCtrl.php' => [
        'ingresaUsuario' => 'configuracion.usuarios.create',
        'editUserSave' => 'configuracion.usuarios.update',
        'editUser' => 'configuracion.usuarios.update',
        'load' => 'configuracion.usuarios.view',
        'deleteUser' => 'configuracion.usuarios.update',
        'getDeletedUsers' => 'configuracion.usuarios.view',
        'getDuplicatedUsers' => 'configuracion.usuarios.view',
        'RestablecerContrasena' => 'configuracion.usuarios.update',
        'actualizaContrasena' => 'configuracion.usuarios.update',
        'getAllInicioSession' => 'configuracion.sesiones.view',
    ],
];
