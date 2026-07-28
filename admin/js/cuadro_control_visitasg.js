var VISITASG = {
    _tipoView: 'ambos',
    _filterRegistered: false,

    labelTipo: function (tipoVal) {
        if (tipoVal === 'aspas') return 'Red de Valor Social 2';
        if (tipoVal === 'primera_dama') return 'Red de Valor Social 1';
        return tipoVal || '';
    },

    registerDataTableFilter: function () {
        if (VISITASG._filterRegistered) {
            return;
        }
        if (!$.fn.dataTable || !$.fn.dataTable.ext || !$.fn.dataTable.ext.search) {
            return;
        }
        VISITASG._filterRegistered = true;
        $.fn.dataTable.ext.search.push(function (settings, data, dataIndex) {
            if (!settings.nTable || settings.nTable.id !== 'dynamictable') {
                return true;
            }
            var view = VISITASG._tipoView || 'ambos';
            if (view === 'ambos') {
                return true;
            }
            var row = settings.aoData[dataIndex] ? settings.aoData[dataIndex].nTr : null;
            if (!row) {
                return true;
            }
            return String($(row).attr('data-tipo') || '') === String(view);
        });
    },

    applyTipoFilter: function (view) {
        VISITASG._tipoView = view || 'ambos';
        $('.gs-filter-btn').removeClass('active');
        $('.gs-filter-btn[data-view="' + VISITASG._tipoView + '"]').addClass('active');

        VISITASG.registerDataTableFilter();

        if ($.fn.DataTable && $.fn.DataTable.isDataTable('#dynamictable')) {
            $('#dynamictable').DataTable().draw();
            return;
        }

        // Fallback si DataTables aún no está listo
        $('#dynamictable tbody tr[data-tipo]').each(function () {
            var tipo = String($(this).attr('data-tipo') || '');
            var visible = VISITASG._tipoView === 'ambos' || tipo === VISITASG._tipoView;
            $(this).toggle(visible);
        });
    },


    editData: function (id) {
        $('#editModal').modal('show');
        $('#edit-loading').show();
        $('#editForm')[0].reset();

        // Conservar departamento fijo (68)
        $('#tbl_departamento_id').val(UTIL.getDepartamentoPrincipal());

        // Limpiar data-url de los iframes para que no persistan fotos del registro anterior
        for (var i = 1; i <= 4; i++) {
            $('#ifm' + i).removeAttr('data-url').removeAttr('data-loaded');
        }
        // Ocultar sección de fotos anteriores mientras carga
        $('#seccion-fotos-actuales').hide();
        $('#grid-fotos-actuales').empty();

        const q = {
            op: "spi_visitasg_get",
            id: id
        };
        UTIL.callAjaxRqstPOST(q, VISITASG.editDataHandler);
    },

    editDataHandler: function (data) {
        $('#edit-loading').hide();
        UTIL.cursorNormal();
        if (data.output.valid) {
            const res = data.output.response[0];

            $('#id').val(res.id);
            $('#date').val(res.date);
            $('#tbl_departamento_id').val(res.tbl_departamento_id || UTIL.getDepartamentoPrincipal());
            $('#tbl_municipio_id').val(res.tbl_municipio_id);
            $('#provincia').val(res.provincia);
            $('#tipo_actividad').val(res.tipo_actividad || 'primera_dama');
            $('#linea').val(res.linea);
            $('#estrategia').val(res.estrategia);
            $('#campana').val(res.campana);
            $('#actividad').val(res.actividad);
            $('#poblacion').val(res.poblacion);
            $('#desc_actividad').val(res.desc_actividad);
            $('#inversion').val(res.inversion);
            $('#link').val(res.link);
            $("#tbl_linea_id").val(res.tbl_linea_id);
            $("#tbl_estrategia_id").val(res.tbl_estrategia_id);

            // Mostrar fotos actuales guardadas
            const fotos = [res.foto1, res.foto2, res.foto3, res.foto4];
            const grid = $("#grid-fotos-actuales").empty();
            let hayFotos = false;
            $.each(fotos, function(idx, ruta) {
                if (ruta && ruta.trim() !== '') {
                    hayFotos = true;
                    const num = idx + 1;
                    const nombreArchivo = ruta.split('/').pop();
                    grid.append(
                        '<div class="col-6 col-md-3">' +
                            '<div class="foto-actual-thumb">' +
                                '<a href="' + ruta + '" target="_blank">' +
                                    '<img src="' + ruta + '" alt="Foto ' + num + '" title="Ver foto ' + num + ' en tamaño completo">' +
                                '</a>' +
                            '</div>' +
                            '<div class="foto-actual-label" style="display:flex;justify-content:space-between;align-items:center;margin-top:4px;">' +
                                '<span>Foto ' + num + '</span>' +
                                '<div style="display:flex;gap:6px;">' +
                                    '<a href="' + ruta + '" download="' + nombreArchivo + '" title="Descargar" style="color:#234162;font-size:15px;" onclick="event.stopPropagation();">' +
                                        '<i class="feather icon-download"></i>' +
                                    '</a>' +
                                    '<span title="Copiar imagen" style="color:#234162;font-size:15px;cursor:pointer;" onclick="VISITASG.copiarImagen(\'' + ruta + '\', this)">' +
                                        '<i class="feather icon-copy"></i>' +
                                    '</span>' +
                                '</div>' +
                            '</div>' +
                        '</div>'
                    );
                }
            });
            $("#seccion-fotos-actuales").toggle(hayFotos);

        } else {
            alert("Error al cargar datos.");
        }
    },

    saveData: function () {
        var tipoVal = $('#tipo_actividad').val();
        if (!tipoVal) {
            UTIL.mostrarMensajeValidacion('Debe seleccionar el tipo de actividad.');
            return;
        }

        var tipoLabel = VISITASG.labelTipo(tipoVal);

        Swal.fire({
          title: "¿Confirmar guardado?",
          html: "¿Está seguro de guardar esta actividad como<br><strong>" + tipoLabel + "</strong>?",
          icon: "warning",
          showCancelButton: true,
          confirmButtonText: "Sí, guardar",
          cancelButtonText: "Cancelar",
        }).then((result) => {
          if (result.isConfirmed || result.value) {
            const f1 = $('#ifm1').attr('data-url') || '';
            const f2 = $('#ifm2').attr('data-url') || '';
            const f3 = $('#ifm3').attr('data-url') || '';
            const f4 = $('#ifm4').attr('data-url') || '';

            const data = {
              op: "spi_visitasg_save",
              id: $('#id').val(),
              date: $('#date').val(),
              tbl_departamento_id: $('#tbl_departamento_id').val(),
              tbl_municipio_id: $('#tbl_municipio_id').val(),
              provincia: $('#provincia').val(),
              poblacion: $('#poblacion').val(),
              desc_actividad: $('#desc_actividad').val(),
              inversion: $('#inversion').val(),
              linea: $('#linea').val(),
              estrategia: $('#estrategia').val(),
              campana: $('#campana').val(),
              actividad: $('#actividad').val(),
              link: $('#link').val(),
              tbl_linea: $('#tbl_linea_id').val(),
              tbl_estrategia: $('#tbl_estrategia_id').val(),
              tipo_actividad: tipoVal,
              foto1: f1,
              foto2: f2,
              foto3: f3,
              foto4: f4,
            };

            UTIL.cursorBusy();

            $.ajax({
              type: "POST",
              url: "admin/ajax/rqst.php",
              data: data,
              dataType: "json",
              success: function (response) {
                UTIL.cursorNormal();
                if (response.output.valid) {
                  UTIL.mostrarMensajeExitoso("Información guardada correctamente.");
                  $('#editModal').modal('hide');
                  location.reload();
                } else {
                  UTIL.mostrarMensajeError(response.output.response.content);
                }
              },
              error: function (xhr, status, error) {
                UTIL.cursorNormal();
                UTIL.mostrarMensajeError("Error de conexión: " + error);
              }
            });
          }
        });
      },

    copiarImagen: function(ruta, el) {
        const img = new Image();
        img.crossOrigin = 'anonymous';
        img.onload = function() {
            const canvas = document.createElement('canvas');
            canvas.width = img.naturalWidth;
            canvas.height = img.naturalHeight;
            canvas.getContext('2d').drawImage(img, 0, 0);
            canvas.toBlob(function(blob) {
                if (!blob) {
                    window.open(ruta, '_blank');
                    UTIL.mostrarMensajeValidacion('No se pudo copiar automáticamente. La imagen se abrió en una nueva pestaña — haz clic derecho y selecciona "Copiar imagen".');
                    return;
                }
                try {
                    navigator.clipboard.write([
                        new ClipboardItem({ 'image/png': blob })
                    ]).then(function() {
                        const icon = el.querySelector('i');
                        if (icon) { icon.className = 'feather icon-check'; }
                        setTimeout(function() {
                            if (icon) { icon.className = 'feather icon-copy'; }
                        }, 2000);
                    }).catch(function() {
                        window.open(ruta, '_blank');
                        UTIL.mostrarMensajeValidacion('Sin HTTPS no se puede copiar automáticamente. La imagen se abrió en una nueva pestaña — haz clic derecho → "Copiar imagen".');
                    });
                } catch(e) {
                    window.open(ruta, '_blank');
                    UTIL.mostrarMensajeValidacion('La imagen se abrió en una nueva pestaña — haz clic derecho → "Copiar imagen".');
                }
            }, 'image/png');
        };
        img.onerror = function() {
            window.open(ruta, '_blank');
            UTIL.mostrarMensajeValidacion('La imagen se abrió en una nueva pestaña — haz clic derecho → "Copiar imagen".');
        };
        img.src = ruta + '?t=' + Date.now();
    }

};

$(function () {
    VISITASG.registerDataTableFilter();

    $(document).on('click', '.gs-filter-btn', function () {
        VISITASG.applyTipoFilter($(this).data('view'));
    });

    $(document).on('init.dt', function (e, settings) {
        if (settings.nTable && settings.nTable.id === 'dynamictable') {
            VISITASG.registerDataTableFilter();
            VISITASG.applyTipoFilter(VISITASG._tipoView || 'ambos');
        }
    });

    // Si DataTables ya inicializó antes de este script
    if ($.fn.DataTable && $.fn.DataTable.isDataTable('#dynamictable')) {
        VISITASG.applyTipoFilter('ambos');
    }
});
