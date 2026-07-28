function initPillSelector(selectId, containerId) {
    var $select = $(selectId);
    var $container = $(containerId);
    if (!$select.length || !$container.length) return;

    function buildPills() {
        $container.empty();
        var hasOptions = false;
        $select.find('option').each(function () {
            if (!$(this).val()) return;
            hasOptions = true;
            var val = $(this).val();
            var text = $(this).text();
            var selected = $(this).prop('selected');
            var pill = $('<span class="pill-option" data-value="' + val + '" style="display:inline-flex;align-items:center;gap:6px;padding:6px 12px;border-radius:999px;font-size:12px;font-weight:800;cursor:pointer;transition:.15s ease;border:1px solid rgba(255,255,255,.15);user-select:none;">' +
                $('<div/>').text(text).html() +
                '</span>');
            if (selected) {
                pill.css({
                    'background': 'linear-gradient(135deg,#3b82f6,#4f46e5)',
                    'color': '#fff',
                    'border-color': 'rgba(255,255,255,.25)'
                });
            } else {
                pill.css({
                    'background': 'rgba(255,255,255,.06)',
                    'color': 'rgba(255,255,255,.8)',
                    'border-color': 'rgba(255,255,255,.12)'
                });
            }
            pill.on('click', function () {
                var opt = $select.find('option[value="' + val + '"]');
                opt.prop('selected', !opt.prop('selected'));
                buildPills();
                $select.trigger('change');
            });
            $container.append(pill);
        });
        if (!hasOptions) {
            $container.append(
                '<span class="text-muted" style="font-size:12px;font-weight:700;">Seleccione un municipio para cargar opciones.</span>'
            );
        }
    }

    $select.off('change.pillRebuild').on('change.pillRebuild', buildPills);
    buildPills();
    $select.data('pill-inited', true);
}

function refreshPillSelector(selectId) {
    var $select = $(selectId);
    if ($select.data('pill-inited')) {
        $select.trigger('change.pillRebuild');
    } else if (selectId === '#tbl_secretarias_id') {
        initPillSelector('#tbl_secretarias_id', '#secrePills');
    } else if (selectId === '#tbl_meta_id') {
        initPillSelector('#tbl_meta_id', '#metaPills');
    }
}

var q;

var PROYECTOSSECRETARIA = {
    initHandlers: function () {
        $(document)
            .off('change.proySecDep', '#tbl_departamento_id')
            .on('change.proySecDep', '#tbl_departamento_id', function () {
                if (typeof DEPARTAMENTO !== 'undefined') {
                    DEPARTAMENTO.getMunicipios();
                }
            });

        $(document)
            .off('change.proySecMun', '#tbl_municipio_id')
            .on('change.proySecMun', '#tbl_municipio_id', function () {
                // Evitar recargar y perder selección mientras se precarga edición
                if ($('#formsecretaria').data('modo') === 'editar' && $('#formsecretaria').data('skip-mun-reload')) {
                    return;
                }
                PROYECTOSSECRETARIA.onMunicipioChange();
            });

        $(document)
            .off('click.proySecSave', '#btnIngresarProyecto')
            .on('click.proySecSave', '#btnIngresarProyecto', function () {
                PROYECTOSSECRETARIA.validateData();
            });

        $(document)
            .off('click.proySecCancel', '#btnCancelarProyecto')
            .on('click.proySecCancel', '#btnCancelarProyecto', function () {
                PROYECTOSSECRETARIA.cancelarEdicion();
            });

        var munFijo = $("#tbl_municipio_id").val();
        if (munFijo && typeof isUsuarioAlcalde !== 'undefined' && isUsuarioAlcalde) {
            PROYECTOSSECRETARIA.onMunicipioChange();
        }
    },

    isEditMode: function () {
        return $('#formsecretaria').data('modo') === 'editar' && !!$('#proyecto_edit_id').val();
    },

    setModoUI: function (modo, proyectoId) {
        var editando = modo === 'editar';
        $('#formsecretaria').data('modo', modo);
        $('#proyecto_edit_id').val(editando ? (proyectoId || '') : '');

        if (editando) {
            $('#formPlaneacionTitle').text('Editar y reenviar proyecto rechazado');
            $('#formPlaneacionSubtitle').html('Modifica los datos necesarios y guarda para reenviar a Planeación. Foto y PDF son opcionales si ya existen.');
            $('#bannerEdicionRechazado').show();
            $('#bannerEditId').text(proyectoId);
            $('#btnIngresarProyectoLabel').text('Guardar y reenviar');
            $('.create-only-req').hide();
            $('.edit-file-hint').show();
            $('#foto2, #documento2').prop('required', false);
        } else {
            $('#formPlaneacionTitle').text('Formulario de Ingreso de Proyecto Planeación Alcaldía');
            $('#formPlaneacionSubtitle').html('Completa la información y adjunta <b>foto</b> + <b>PDF</b> para radicar el proyecto.');
            $('#bannerEdicionRechazado').hide();
            $('#bannerEditId').text('');
            $('#btnIngresarProyectoLabel').text('Ingresar Proyecto');
            $('.create-only-req').show();
            $('.edit-file-hint').hide();
            $('#link_foto_actual, #link_documento_actual').hide().empty();
            $('#foto2, #documento2').prop('required', true);
        }
    },

    cancelarEdicion: function () {
        if (typeof UTIL !== 'undefined' && UTIL.clearForm) {
            UTIL.clearForm('formsecretaria');
        } else {
            $('#formsecretaria')[0].reset();
        }
        $('#proyecto_edit_id').val('');
        $('#tbl_secretarias_id').find('option').prop('selected', false);
        $('#tbl_meta_id').find('option').prop('selected', false);
        refreshPillSelector('#tbl_secretarias_id');
        refreshPillSelector('#tbl_meta_id');
        PROYECTOSSECRETARIA.setModoUI('crear');
        if (typeof isUsuarioAlcalde !== 'undefined' && isUsuarioAlcalde) {
            PROYECTOSSECRETARIA.onMunicipioChange();
        }
    },

    onMunicipioChange: function () {
        var codigo = $("#tbl_municipio_id").val();
        if (typeof DEPARTAMENTO !== 'undefined' && typeof DEPARTAMENTO.getVeredasByMunicipioId === 'function') {
            try { DEPARTAMENTO.getVeredasByMunicipioId(); } catch (e) {}
        }
        PROYECTOSSECRETARIA.loadSecretariasByMunicipio(codigo);
        PROYECTOSSECRETARIA.loadMetasByMunicipio(codigo);
    },

    loadSecretariasByMunicipio: function (codigoMunicipio, selectedIds, done) {
        var $sel = $("#tbl_secretarias_id");
        if (!$sel.length) return;

        selectedIds = (selectedIds || []).map(String);

        if (!codigoMunicipio || codigoMunicipio === '' || codigoMunicipio === 'seleccione') {
            $sel.empty();
            refreshPillSelector('#tbl_secretarias_id');
            if (typeof done === 'function') done();
            return;
        }

        $.ajax({
            url: 'admin/ajax/rqst.php',
            type: 'POST',
            dataType: 'json',
            data: {
                op: 'secretariasmunicipalespormunicipio',
                codigo_municipio: codigoMunicipio
            },
            success: function (data) {
                $sel.empty();
                if (data && data.output && data.output.valid && data.output.response && data.output.response.length) {
                    data.output.response.forEach(function (secretaria) {
                        var $opt = $('<option/>')
                            .val(secretaria.id)
                            .text(secretaria.secretaria || ('Secretaría #' + secretaria.id));
                        if (selectedIds.indexOf(String(secretaria.id)) !== -1) {
                            $opt.prop('selected', true);
                        }
                        $sel.append($opt);
                    });
                }
                refreshPillSelector('#tbl_secretarias_id');
                if (typeof done === 'function') done();
            },
            error: function () {
                $sel.empty();
                refreshPillSelector('#tbl_secretarias_id');
                if (typeof UTIL !== 'undefined' && UTIL.mostrarMensajeError) {
                    UTIL.mostrarMensajeError('No se pudieron cargar las secretarías del municipio.');
                }
                if (typeof done === 'function') done();
            }
        });
    },

    loadMetasByMunicipio: function (codigoMunicipio, selectedIds, done) {
        var $sel = $("#tbl_meta_id");
        if (!$sel.length) return;

        selectedIds = (selectedIds || []).map(String);

        if (!codigoMunicipio || codigoMunicipio === '' || codigoMunicipio === 'seleccione') {
            $sel.empty();
            refreshPillSelector('#tbl_meta_id');
            if (typeof done === 'function') done();
            return;
        }

        $.ajax({
            url: 'admin/ajax/rqst.php',
            type: 'POST',
            dataType: 'json',
            data: {
                op: 'metas_plan_desarrollo_por_municipio',
                codigo_municipio: codigoMunicipio
            },
            success: function (data) {
                $sel.empty();
                if (data && data.output && data.output.valid && data.output.response && data.output.response.length) {
                    data.output.response.forEach(function (meta) {
                        var label = ((meta.eje_estrategico || '') + ' - ' + (meta.sector_pdd || '')).replace(/^\s*-\s*|\s*-\s*$/g, '');
                        if (!label) {
                            label = meta.producto_bien_servicio || ('Meta #' + meta.id);
                        }
                        var $opt = $('<option/>').val(meta.id).text(label);
                        if (selectedIds.indexOf(String(meta.id)) !== -1) {
                            $opt.prop('selected', true);
                        }
                        $sel.append($opt);
                    });
                }
                refreshPillSelector('#tbl_meta_id');
                if (typeof done === 'function') done();
            },
            error: function () {
                $sel.empty();
                refreshPillSelector('#tbl_meta_id');
                if (typeof UTIL !== 'undefined' && UTIL.mostrarMensajeError) {
                    UTIL.mostrarMensajeError('No se pudieron cargar las metas del plan de desarrollo.');
                }
                if (typeof done === 'function') done();
            }
        });
    },

    /** Precarga el formulario de creación con un proyecto rechazado */
    cargarEdicionRechazado: function (proyectoId) {
        if (!$('#formsecretaria').length) {
            Swal.fire('Atención', 'No tiene permiso para editar desde el formulario de ingreso.', 'warning');
            return;
        }

        $.ajax({
            url: 'admin/ajax/rqst.php',
            type: 'POST',
            dataType: 'json',
            data: { op: 'obtener_detalles_proyecto', id: proyectoId },
            success: function (response) {
                if (!(response.output && response.output.valid)) {
                    Swal.fire('Error', (response.output && response.output.response && response.output.response.content) || 'No se pudo cargar', 'error');
                    return;
                }

                var p = response.output.response || {};
                if ((p.estado_proyecto || '') !== 'Rechazado') {
                    Swal.fire('Atención', 'Solo se pueden editar proyectos en estado Rechazado.', 'warning');
                    return;
                }

                PROYECTOSSECRETARIA.setModoUI('editar', p.id);

                var fecha = p.fecha || '';
                if (fecha.indexOf('/') !== -1) {
                    fecha = fecha.split('/').reverse().join('-');
                } else if (fecha.length > 10) {
                    fecha = fecha.substring(0, 10);
                }
                $('#date').val(fecha);
                $('#proyecto').val(p.proyecto || '');
                $('#valor_proyecto').val(p.valor_proyecto || '');
                $('#observaciones').val(p.observaciones || '');

                var codigoMun = p.tbl_municipio_id || '';
                var secIds = p.secretarias_ids || (p.tbl_secretarias_id ? [p.tbl_secretarias_id] : []);
                var metaIds = p.metas_ids || (p.tbl_meta_id ? [p.tbl_meta_id] : []);

                $('#formsecretaria').data('skip-mun-reload', true);
                var $mun = $('#tbl_municipio_id');
                if ($mun.is('select')) {
                    // Asegurar opción si no existe
                    if (codigoMun && !$mun.find('option[value="' + codigoMun + '"]').length) {
                        $mun.append($('<option/>').val(codigoMun).text(p.municipio || codigoMun));
                    }
                    $mun.val(String(codigoMun));
                } else {
                    $mun.val(codigoMun);
                }

                var pending = 2;
                var finish = function () {
                    pending--;
                    if (pending <= 0) {
                        $('#formsecretaria').data('skip-mun-reload', false);
                        $('html, body').animate({ scrollTop: $('#formsecretaria').offset().top - 120 }, 400);
                        Swal.fire({
                            toast: true,
                            position: 'top-end',
                            icon: 'info',
                            title: 'Proyecto cargado en el formulario',
                            showConfirmButton: false,
                            timer: 2200
                        });
                    }
                };

                PROYECTOSSECRETARIA.loadSecretariasByMunicipio(codigoMun, secIds, finish);
                PROYECTOSSECRETARIA.loadMetasByMunicipio(codigoMun, metaIds, finish);

                // Links a adjuntos actuales
                if (p.foto2) {
                    var fotoUrl = 'uploads/proyectos_secretarias/' + String(p.foto2).split('/').pop();
                    $('#link_foto_actual').html('<a href="' + fotoUrl + '" target="_blank" class="pdf-pill foto-pill" style="display:inline-flex;">Ver foto actual</a>').show();
                } else {
                    $('#link_foto_actual').hide().empty();
                }
                if (p.documento2) {
                    var docUrl = 'uploads/proyectos_secretarias/' + String(p.documento2).split('/').pop();
                    $('#link_documento_actual').html('<a href="' + docUrl + '" target="_blank" class="pdf-pill" style="display:inline-flex;">Ver PDF actual</a>').show();
                } else {
                    $('#link_documento_actual').hide().empty();
                }
            },
            error: function () {
                Swal.fire('Error', 'No se pudo conectar con el servidor.', 'error');
            }
        });
    },

    validateData: function () {
        var msj = "Falta ingresar información obligatoria, marcada con asterisco.";
        var secretariasVal = $("#tbl_secretarias_id").val();
        var metasVal = $("#tbl_meta_id").val();
        var editando = PROYECTOSSECRETARIA.isEditMode();

        if (
            !$("#date").val() ||
            !$("#tbl_municipio_id").val() ||
            !$("#proyecto").val() ||
            !secretariasVal || secretariasVal.length === 0 ||
            !metasVal || metasVal.length === 0 ||
            !$("#valor_proyecto").val() ||
            $("#valor_proyecto").val() === "0"
        ) {
            UTIL.mostrarMensajeValidacion(msj);
            return;
        }

        if (!editando && (!$("#foto2").val() || !$("#documento2").val())) {
            UTIL.mostrarMensajeValidacion(msj);
            return;
        }

        PROYECTOSSECRETARIA.saveInformacion();
    },

    saveInformacion: function () {
        var editando = PROYECTOSSECRETARIA.isEditMode();
        Swal.fire({
            title: editando ? "¿Guardar y reenviar el proyecto?" : "¿Estás seguro de ingresar la información?",
            text: editando ? "El proyecto volverá a estado Enviado y se notificará a Planeación." : "¿Desea continuar?",
            icon: "warning",
            showCancelButton: true,
            confirmButtonText: "Sí",
            cancelButtonText: "Cancelar!",
            reverseButtons: true
        }).then(function (result) {
            if (!result.isConfirmed) return;

            var formData = new FormData($("#formsecretaria")[0]);
            formData.append('op', editando ? 'editar_proyecto_rechazado' : 'proyectos_secretaria_save');
            if (editando) {
                formData.set('id', $('#proyecto_edit_id').val());
            }

            UTIL.cursorBusy();
            $.ajax({
                url: "admin/ajax/rqst.php",
                type: "POST",
                data: formData,
                dataType: "json",
                processData: false,
                contentType: false,
                success: function (data) {
                    UTIL.cursorNormal();
                    if (data.output && data.output.valid) {
                        UTIL.mostrarMensajeExitoso(
                            editando
                                ? "Proyecto actualizado y reenviado correctamente"
                                : "Información guardada correctamente"
                        );
                        setTimeout(function () { window.location.reload(); }, 1000);
                    } else {
                        UTIL.mostrarMensajeError((data.output && data.output.response && data.output.response.content) || 'Error al guardar');
                    }
                },
                error: function () {
                    UTIL.cursorNormal();
                    UTIL.mostrarMensajeError("Error al conectar con el servidor. Intente de nuevo.");
                }
            });
        });
    }
};

$(function () {
    initPillSelector('#tbl_secretarias_id', '#secrePills');
    initPillSelector('#tbl_meta_id', '#metaPills');
    q = {};
    PROYECTOSSECRETARIA.setModoUI('crear');
    PROYECTOSSECRETARIA.initHandlers();
});
