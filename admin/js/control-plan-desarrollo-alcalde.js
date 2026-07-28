/**
 * JavaScript para controlar el módulo de Plan de Desarrollo del Alcalde
 * DataTables server-side — no recarga completa de página al eliminar
 */

$(document).ready(function() {
    var hasCheckbox = ($('#dynamictable thead th:first-child input[type=checkbox]').length > 0);
    var table;

    function truncate(val, maxLen, colTitle) {
        if (!val || val.length <= maxLen) return val;
        var safe = $('<div>').text(val).html();
        return '<a href="#" class="link-ver-mas" data-col-title="' + colTitle + '" title="' + safe + '">' + val.substring(0, maxLen) + '... Ver m&aacute;s</a>';
    }

    var columns = [];
    if (hasCheckbox) {
        columns.push({
            data: null,
            orderable: false,
            searchable: false,
            render: function(data) {
                return '<input type="checkbox" class="chkItem" data-id="' + data.id + '" style="width:16px;height:16px;cursor:pointer;">';
            }
        });
    }
    columns.push({ data: 'id' });
    columns.push({ data: 'eje_estrategico' });
    columns.push({ data: 'sector_pdd' });
    columns.push({ data: 'sector_catalogo' });
    columns.push({
        data: 'producto_bien_servicio',
        render: function(data, type) {
            if (type === 'display') return truncate(data, 100, 'Producto, Bien o Servicio PDD');
            return data;
        }
    });
    columns.push({
        data: 'anio_2024',
        render: function(data, type) {
            if (type === 'display') return truncate(data, 50, '2024');
            return data;
        }
    });
    columns.push({ data: 'avance_2024' });
    columns.push({ data: 'avance_2025' });
    columns.push({
        data: 'anio_2025',
        render: function(data, type) {
            if (type === 'display') return truncate(data, 50, '2025');
            return data;
        }
    });
    columns.push({
        data: 'anio_2026',
        render: function(data, type) {
            if (type === 'display') return truncate(data, 50, '2026');
            return data;
        }
    });
    columns.push({
        data: 'anio_2027',
        render: function(data, type) {
            if (type === 'display') return truncate(data, 50, '2027');
            return data;
        }
    });
    columns.push({ data: 'secretaria' });
    if (hasCheckbox) {
        columns.push({
            data: null,
            orderable: false,
            searchable: false,
            render: function(data) {
                return '<button class="btn btn-sm btn-danger btnEliminar" data-id="' + data.id + '" title="Eliminar"><i class="feather icon-trash-2"></i></button>';
            }
        });
    }

    table = $('#dynamictable').DataTable({
        serverSide: true,
        processing: true,
        responsive: true,
        pageLength: 10,
        order: [[hasCheckbox ? 1 : 0, 'desc']],
        language: {
            url: '//cdn.datatables.net/plug-ins/1.11.5/i18n/es-ES.json'
        },
        ajax: {
            url: 'admin/controllers/planDesarrolloAlcaldeCtrl.php',
            type: 'POST',
            contentType: 'application/json',
            data: function(d) {
                return JSON.stringify({
                    method: 'load',
                    data: Object.assign({}, d, {
                        filtroSectorPDD: $('#filtroSectorPDD').val(),
                        filtroSectorCatalogo: $('#filtroSectorCatalogo').val()
                    })
                });
            }
        },
        columns: columns,
        drawCallback: function() {
            $('#chkTodos').prop('checked', false);
            actualizarContador();
        }
    });

    // === SELECCIÓN MÚLTIPLE ===

    function actualizarContador() {
        var n = $('.chkItem:checked').length;
        $('#contSeleccionados').text(n);
    }

    $(document).on('change', '#chkTodos', function() {
        var checked = $(this).is(':checked');
        table.rows({ page: 'current' }).nodes().to$().find('.chkItem').prop('checked', checked);
        actualizarContador();
    });

    $(document).on('change', '.chkItem', function() {
        if (!$(this).is(':checked')) {
            $('#chkTodos').prop('checked', false);
        } else {
            var total = table.rows({ page: 'current' }).nodes().to$().find('.chkItem').length;
            var checked = table.rows({ page: 'current' }).nodes().to$().find('.chkItem:checked').length;
            if (total === checked) $('#chkTodos').prop('checked', true);
        }
        actualizarContador();
    });

    table.on('page.dt', function() {
        $('#chkTodos').prop('checked', false);
        actualizarContador();
    });

    // === ELIMINAR INDIVIDUAL ===

    $(document).on('click', '.btnEliminar', function() {
        var id = $(this).data('id');

        Swal.fire({
            title: '¿Estás seguro?',
            text: 'Esta acción eliminará el registro permanentemente',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Sí, eliminar',
            cancelButtonText: 'Cancelar'
        }).then(function(result) {
            if (!result.isConfirmed) return;

            Swal.fire({
                title: 'Eliminando...',
                text: 'Por favor espera',
                allowOutsideClick: false,
                didOpen: function() { Swal.showLoading(); }
            });

            $.ajax({
                url: 'admin/controllers/planDesarrolloAlcaldeCtrl.php',
                type: 'POST',
                contentType: 'application/json',
                data: JSON.stringify({ method: 'delete', data: { id: id } }),
                success: function(response) {
                    try {
                        var data = typeof response === 'string' ? JSON.parse(response) : response;
                        if (data.output && data.output.valid) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Eliminado',
                                text: data.output.message,
                                timer: 1500
                            }).then(function() {
                                table.ajax.reload(null, false);
                            });
                        } else {
                            Swal.fire('Error', data.output.message || 'Error al eliminar', 'error');
                        }
                    } catch (e) {
                        Swal.fire('Error', 'Error al procesar la respuesta', 'error');
                    }
                },
                error: function(xhr, status, error) {
                    Swal.fire('Error', 'Error al comunicarse con el servidor: ' + error, 'error');
                }
            });
        });
    });

    // === ELIMINAR MÚLTIPLE ===

    $(document).on('click', '#btnEliminarSeleccionados', function() {
        var ids = [];
        $('.chkItem:checked').each(function() {
            ids.push(parseInt($(this).data('id')));
        });

        if (ids.length === 0) {
            Swal.fire({
                icon: 'info',
                title: 'Ningún registro seleccionado',
                text: 'Selecciona uno o más registros usando los checkboxes de la tabla.',
                confirmButtonText: 'Aceptar'
            });
            return;
        }

        Swal.fire({
            title: '¿Eliminar ' + ids.length + ' registro(s)?',
            text: 'Esta acción es irreversible.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Sí, eliminar todos',
            cancelButtonText: 'Cancelar'
        }).then(function(result) {
            if (!result.isConfirmed) return;

            Swal.fire({
                title: 'Eliminando...',
                text: 'Por favor espera',
                allowOutsideClick: false,
                didOpen: function() { Swal.showLoading(); }
            });

            $.ajax({
                url: 'admin/controllers/planDesarrolloAlcaldeCtrl.php',
                type: 'POST',
                contentType: 'application/json',
                data: JSON.stringify({ method: 'deleteMultiple', data: { ids: ids } }),
                success: function(response) {
                    try {
                        var data = typeof response === 'string' ? JSON.parse(response) : response;
                        if (data.output && data.output.valid) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Eliminados',
                                text: data.output.message,
                                timer: 1500
                            }).then(function() {
                                table.ajax.reload(null, false);
                            });
                        } else {
                            Swal.fire('Error', data.output.message || 'Error al eliminar', 'error');
                        }
                    } catch (e) {
                        Swal.fire('Error', 'Error al procesar la respuesta', 'error');
                    }
                },
                error: function(xhr, status, error) {
                    Swal.fire('Error', 'Error al comunicarse con el servidor: ' + error, 'error');
                }
            });
        });
    });

    // === VER MÁS (texto completo en modal) ===

    $(document).on('click', '.link-ver-mas', function(e) {
        e.preventDefault();
        var fullText = $(this).attr('title');
        var colTitle = $(this).data('col-title') || 'Contenido completo';
        Swal.fire({
            title: colTitle,
            html: '<div style="text-align:left;max-height:400px;overflow-y:auto;">' + fullText + '</div>',
            width: '600px',
            confirmButtonText: 'Cerrar'
        });
    });
});
