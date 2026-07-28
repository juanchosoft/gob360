(function () {
    'use strict';

    var CAMPOS_POR_ACCION = {
        'Capacitacion Fiscal y Financiera': [
            'observaciones', 'capacitacion-fiscal'
        ],
        'Recaudo del impuesto al consumo': [
            'observaciones', 'consumo'
        ],
        'Recaudo del impuesto de registro': [
            'observaciones', 'registro'
        ],
        'Impuesto Vehicular Recaudado': [
            'observaciones', 'vehicular'
        ],
        'Impuesto Estampillas Recaudado': [
            'observaciones', 'estampilla'
        ],
        'GOA Aprehensiones de Licores': [
            'observaciones', 'establecimiento', 'aprehensiones'
        ],
        'GOA Aprehensión de Cigarrillos': [
            'observaciones', 'establecimiento', 'aprehensiones'
        ],
        'GOA Aprehensión de Cervezas': [
            'observaciones', 'establecimiento', 'aprehensiones'
        ],
        'GOA Aprehensión de Tabaco y Otros': [
            'observaciones', 'establecimiento', 'aprehensiones'
        ],
        'Registro de Visitas a Establecimientos Comerciales': [
            'observaciones', 'establecimiento', 'visitas'
        ],
        'Registro de Capacitaciones del GOA': [
            'observaciones', 'capacitacion-goa'
        ],
        'GOA Juridico': [
            'observaciones', 'goa-juridico'
        ]
    };

    var HACIENDA_INFO = {

        init: function () {
            document.addEventListener('click', function (e) {
                var btn = e.target.closest('.btn-editar-hacienda');
                if (btn) HACIENDA_INFO.abrirModal(btn);
            });

            var form = document.getElementById('formEditarHacienda');
            if (form) {
                form.addEventListener('submit', function (e) {
                    e.preventDefault();
                    HACIENDA_INFO.guardar();
                });
            }
        },

        mostrarGrupos: function (accion) {
            // Ocultar todos los grupos
            document.querySelectorAll('#modalEditarHacienda .edit-grupo').forEach(function (el) {
                el.classList.add('d-none');
            });

            var grupos = CAMPOS_POR_ACCION[accion] || ['observaciones'];
            grupos.forEach(function (grupo) {
                document.querySelectorAll('#modalEditarHacienda .edit-grupo-' + grupo).forEach(function (el) {
                    el.classList.remove('d-none');
                });
            });
        },

        abrirModal: function (btn) {
            var data = btn.dataset;

            var campos = [
                'id', 'accion', 'date', 'observaciones', 'barrio', 'direccion',
                'administrador', 'nombre_establecimiento', 'tipoe', 'establecimiento',
                'cantidad_aprehendida', 'avaluo_comercial', 'cantidad_visitas_al_municipio',
                'cantidad_personas', 'valor_importado', 'valor_nacional',
                'valor_tramite', 'valor_recaudo',
                'valor_tramite_impuesto_vehicular', 'valor_recaudo_impuesto_vehicular',
                'vehicular_cantidad_operativos', 'vehicular_cantidad_emplazados',
                'vehicular_cantidad_placas_consultadas', 'vehicular_cantidad_campanas_sensibilizacion',
                'estampilla', 'valor_estampilla',
                'goa_juridico_custodia_valor_total', 'goa_juridico_custodia_cantidad_procesos',
                'goa_juridico_custodia_cantidad_unidades', 'goa_juridico_destruccion_cantidad_unidades',
                'goa_juridico_destruccion_valor_total', 'tipo_capacitacion_goa', 'numero_asistentes'
            ];

            campos.forEach(function (campo) {
                var el = document.getElementById('edit_' + campo);
                if (el) el.value = data[campo] || '';
            });

            HACIENDA_INFO.mostrarGrupos(data.accion || '');

            $('#modalEditarHacienda').modal('show');
        },

        guardar: function () {
            var form   = document.getElementById('formEditarHacienda');
            var inputs = form.querySelectorAll('input, select, textarea');
            var rqst   = { op: 'hacienda_update' };

            inputs.forEach(function (el) {
                if (el.name) rqst[el.name] = el.value;
            });

            var btnGuardar = document.getElementById('btnGuardarHacienda');
            btnGuardar.disabled = true;
            btnGuardar.innerHTML = '<span class="spinner-border spinner-border-sm mr-1"></span> Guardando...';

            $.ajax({
                url:      'admin/ajax/rqst.php',
                type:     'POST',
                data:     rqst,
                dataType: 'json',
                success: function (resp) {
                    var out = resp && resp.output ? resp.output : {};
                    if (out.valid) {
                        $('#modalEditarHacienda').modal('hide');
                        Swal.fire({
                            icon: 'success',
                            title: '¡Actualizado!',
                            text: out.response || 'Registro actualizado correctamente.',
                            timer: 2500,
                            showConfirmButton: false
                        }).then(function () {
                            location.reload();
                        });
                    } else {
                        Swal.fire({ icon: 'error', title: 'Error', text: out.response || 'No se pudo actualizar.' });
                    }
                },
                error: function () {
                    Swal.fire({ icon: 'error', title: 'Error de red', text: 'No se pudo conectar con el servidor.' });
                },
                complete: function () {
                    btnGuardar.disabled = false;
                    btnGuardar.innerHTML = '<i class="feather icon-save mr-1"></i> Guardar Cambios';
                }
            });
        }
    };

    document.addEventListener('DOMContentLoaded', function () {
        HACIENDA_INFO.init();
    });
})();
