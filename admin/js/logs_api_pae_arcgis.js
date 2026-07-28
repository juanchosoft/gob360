/**
 * JavaScript para la vista de Logs de API ArcGIS PAE
 *
 * Muestra historial de peticiones a ArcGIS Online,
 * estadísticas y detalle de cada request/response.
 *
 * @author SPIDERSOFTWARE
 * @version 1.0
 */
const LOGS_PAE_ARCGIS = {

    dataTable: null,
    paginaActual: 0,
    totalRegistros: 0,
    logDetalleActual: null,

    /**
     * Inicializa la vista de logs
     */
    init: function () {
        this.initDataTable();
        this.setupEventListeners();
        this.cargarEstadisticas();
        this.cargarLogs();
    },

    /**
     * Configura event listeners
     */
    setupEventListeners: function () {
        var self = this;

        // Botón filtrar
        $('#btnFiltrar').on('click', function () {
            self.paginaActual = 0;
            self.cargarLogs();
        });

        // Paginación
        $('#btnPaginaAnterior').on('click', function () {
            if (self.paginaActual > 0) {
                self.paginaActual--;
                self.cargarLogs();
            }
        });

        $('#btnPaginaSiguiente').on('click', function () {
            var limit = parseInt($('#filtroLimit').val());
            if ((self.paginaActual + 1) * limit < self.totalRegistros) {
                self.paginaActual++;
                self.cargarLogs();
            }
        });

        // Click en fila para ver detalle
        $(document).on('click', '#tablaLogs tbody tr', function () {
            var data = self.dataTable.row(this).data();
            if (data) {
                self.verDetalle(data.id);
            }
        });

        // Limpiar logs antiguos
        $('#btnLimpiarLogs').on('click', function () {
            self.confirmarLimpieza();
        });
    },

    /**
     * Inicializa DataTable
     */
    initDataTable: function () {
        this.dataTable = $('#tablaLogs').DataTable({
            data: [],
            columns: [
                {
                    data: 'id',
                    title: 'ID',
                    width: '5%',
                    className: 'text-center',
                    render: function (data) {
                        return '<span class="font-weight-bold">#' + data + '</span>';
                    }
                },
                {
                    data: 'fecha',
                    title: 'Fecha',
                    width: '13%',
                    render: function (data) {
                        return '<small class="font-weight-bold">' + (data || '-') + '</small>';
                    }
                },
                {
                    data: 'municipio_nombre',
                    title: 'Municipio',
                    width: '13%',
                    render: function (data, type, row) {
                        if (!data && !row.municipio_codigo) {
                            return '<span class="badge badge-info">TODOS</span>';
                        }
                        var nombre = data ? data.replace(/_/g, ' ') : '';
                        var codigo = row.municipio_codigo ? '<small class="text-muted"> (' + row.municipio_codigo + ')</small>' : '';
                        return '<small class="font-weight-bold">' + LOGS_PAE_ARCGIS.escapeHtml(nombre) + '</small>' + codigo;
                    }
                },
                {
                    data: 'where_clause',
                    title: 'WHERE',
                    width: '14%',
                    render: function (data) {
                        if (!data) return '<span class="text-muted">-</span>';
                        var display = data.length > 40 ? data.substring(0, 40) + '...' : data;
                        return '<code style="font-size:0.72rem;">' + LOGS_PAE_ARCGIS.escapeHtml(display) + '</code>';
                    }
                },
                {
                    data: 'http_code',
                    title: 'HTTP',
                    width: '6%',
                    className: 'text-center',
                    render: function (data) {
                        if (!data) return '-';
                        var color = data == 200 ? 'success' : (data >= 400 ? 'danger' : 'warning');
                        return '<span class="badge badge-' + color + '">' + data + '</span>';
                    }
                },
                {
                    data: 'estado',
                    title: 'Estado',
                    width: '10%',
                    className: 'text-center',
                    render: function (data) {
                        var clases = {
                            'OK': 'badge-ok',
                            'ERROR_CURL': 'badge-curl',
                            'ERROR_HTTP': 'badge-http',
                            'ERROR_JSON': 'badge-json',
                            'ERROR_API': 'badge-api',
                            'ERROR_BD': 'badge-bd'
                        };
                        var cls = clases[data] || 'badge-error';
                        var label = data === 'OK' ? 'OK' : data.replace('ERROR_', '');
                        return '<span class="badge ' + cls + '" style="font-size:0.72rem;">' + label + '</span>';
                    }
                },
                {
                    data: 'total_features',
                    title: 'Features',
                    width: '8%',
                    className: 'text-center',
                    render: function (data) {
                        var num = parseInt(data) || 0;
                        return '<strong class="' + (num > 0 ? 'text-primary' : 'text-muted') + '">' + num + '</strong>';
                    }
                },
                {
                    data: 'tiempo_respuesta',
                    title: 'Tiempo (s)',
                    width: '9%',
                    className: 'text-center',
                    render: function (data) {
                        if (!data) return '-';
                        var val = parseFloat(data);
                        var color = val < 2 ? 'text-success' : (val < 5 ? 'text-warning' : 'text-danger');
                        return '<strong class="' + color + '">' + val.toFixed(3) + 's</strong>';
                    }
                },
                {
                    data: 'response_size',
                    title: 'Tamaño',
                    width: '8%',
                    className: 'text-center',
                    render: function (data) {
                        var bytes = parseInt(data) || 0;
                        if (bytes === 0) return '<span class="text-muted">-</span>';
                        if (bytes < 1024) return '<small>' + bytes + ' B</small>';
                        if (bytes < 1048576) return '<small>' + (bytes / 1024).toFixed(1) + ' KB</small>';
                        return '<small>' + (bytes / 1048576).toFixed(1) + ' MB</small>';
                    }
                },
                {
                    data: 'usuario',
                    title: 'Usuario',
                    width: '10%',
                    render: function (data) {
                        return '<small>' + LOGS_PAE_ARCGIS.escapeHtml(data || '-') + '</small>';
                    }
                }
            ],
            order: [],
            paging: false,
            searching: true,
            info: false,
            language: {
                search: 'Buscar en tabla:',
                emptyTable: 'No hay logs registrados. Consulte el Dashboard PAE ArcGIS para generar registros.'
            }
        });
    },

    /**
     * Carga las estadísticas generales
     */
    cargarEstadisticas: function () {
        $.ajax({
            url: 'admin/ajax/rqst.php',
            type: 'POST',
            dataType: 'json',
            data: { op: 'getEstadisticasLogsPaeArcgis' },
            success: function (response) {
                if (response.output && response.output.valid) {
                    var stats = response.output.response;
                    var g = stats.general || {};
                    var h24 = stats.ultimas_24h || {};

                    $('#statTotal').text(g.total_logs || 0);
                    $('#statOk').text(g.total_ok || 0);
                    $('#statErrores').text(g.total_errores || 0);
                    $('#statTiempo').text(g.promedio_tiempo || '-');
                    $('#stat24h').text(h24.total_24h || 0);
                    $('#statFeatures').text(LOGS_PAE_ARCGIS.formatNumber(g.total_features_api || 0));
                }
            }
        });
    },

    /**
     * Carga los logs con los filtros aplicados
     */
    cargarLogs: function () {
        var self = this;
        var limit = parseInt($('#filtroLimit').val());
        var offset = self.paginaActual * limit;

        var params = {
            op: 'getLogsPaeArcgis',
            fecha_desde: $('#filtroFechaDesde').val(),
            fecha_hasta: $('#filtroFechaHasta').val(),
            estado: $('#filtroEstado').val(),
            municipio_codigo: $('#filtroMunicipio').val(),
            limit: limit,
            offset: offset
        };

        $('#btnFiltrar').prop('disabled', true).html('<i class="feather icon-loader"></i> Cargando...');

        $.ajax({
            url: 'admin/ajax/rqst.php',
            type: 'POST',
            dataType: 'json',
            data: params,
            success: function (response) {
                if (response.output && response.output.valid) {
                    var data = response.output.response;
                    self.totalRegistros = data.total;

                    // Llenar tabla
                    self.dataTable.clear();
                    self.dataTable.rows.add(data.logs);
                    self.dataTable.draw();

                    // Actualizar paginación
                    var desde = offset + 1;
                    var hasta = Math.min(offset + limit, data.total);
                    $('#paginacionInfo').text(
                        data.total > 0
                            ? 'Mostrando ' + desde + ' - ' + hasta + ' de ' + data.total + ' registros'
                            : 'Sin registros'
                    );
                    $('#badgeTotalLogs').text(data.total + ' registros');
                    $('#btnPaginaAnterior').prop('disabled', self.paginaActual === 0);
                    $('#btnPaginaSiguiente').prop('disabled', (self.paginaActual + 1) * limit >= data.total);
                } else {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Error al cargar logs',
                        text: response.output ? response.output.error : 'Error desconocido'
                    });
                }
            },
            error: function () {
                Swal.fire({
                    icon: 'error',
                    title: 'Error de conexión',
                    text: 'No se pudo conectar con el servidor'
                });
            },
            complete: function () {
                $('#btnFiltrar').prop('disabled', false).html('<i class="feather icon-search"></i> Filtrar');
            }
        });
    },

    /**
     * Carga el detalle completo de un log y lo muestra en modal
     */
    verDetalle: function (logId) {
        var self = this;

        $.ajax({
            url: 'admin/ajax/rqst.php',
            type: 'POST',
            dataType: 'json',
            data: { op: 'getLogDetallePaeArcgis', id: logId },
            success: function (response) {
                if (response.output && response.output.valid) {
                    var log = response.output.response;
                    self.logDetalleActual = log;
                    self.renderModalDetalle(log);
                    $('#modalLogDetalle').modal('show');
                } else {
                    Swal.fire({ icon: 'error', title: 'Error', text: 'No se pudo cargar el detalle' });
                }
            }
        });
    },

    /**
     * Renderiza el contenido del modal de detalle
     */
    renderModalDetalle: function (log) {
        var estadoClass = log.estado === 'OK' ? 'badge-ok' : 'badge-error';
        var html = '';

        // Info general
        html += '<div class="row mb-3">';
        html += '<div class="col-md-6">';
        html += '<p><strong>ID:</strong> #' + log.id + '</p>';
        html += '<p><strong>Fecha:</strong> ' + log.fecha + '</p>';
        html += '<p><strong>Tipo Consulta:</strong> <code>' + this.escapeHtml(log.tipo_consulta) + '</code></p>';
        html += '<p><strong>Municipio:</strong> ' + this.escapeHtml((log.municipio_nombre || 'TODOS').replace(/_/g, ' '));
        if (log.municipio_codigo) html += ' <small class="text-muted">(' + log.municipio_codigo + ')</small>';
        html += '</p>';
        html += '</div>';
        html += '<div class="col-md-6">';
        html += '<p><strong>Estado:</strong> <span class="badge ' + estadoClass + '">' + log.estado + '</span></p>';
        html += '<p><strong>HTTP Code:</strong> <span class="badge badge-' + (log.http_code == 200 ? 'success' : 'danger') + '">' + (log.http_code || '-') + '</span></p>';
        html += '<p><strong>Tiempo:</strong> ' + (log.tiempo_respuesta || '-') + 's</p>';
        html += '<p><strong>Features:</strong> ' + (log.total_features || 0) + '</p>';
        html += '</div></div>';

        // Más info
        html += '<div class="row mb-3">';
        html += '<div class="col-md-4"><p><strong>Usuario:</strong> ' + this.escapeHtml(log.usuario || '-') + '</p></div>';
        html += '<div class="col-md-4"><p><strong>IP:</strong> ' + this.escapeHtml(log.ip_solicitante || '-') + '</p></div>';
        html += '<div class="col-md-4"><p><strong>Tamaño respuesta:</strong> ' + this.formatBytes(log.response_size || 0) + '</p></div>';
        html += '</div>';

        // Error detalle
        if (log.error_detalle) {
            html += '<div class="alert alert-danger mb-3">';
            html += '<strong><i class="feather icon-alert-circle"></i> Error:</strong> ' + this.escapeHtml(log.error_detalle);
            html += '</div>';
        }

        // Endpoint URL
        html += '<h6 class="font-weight-bold mb-2"><i class="feather icon-link"></i> Endpoint URL</h6>';
        html += '<div class="json-viewer mb-3" style="max-height:80px;">';
        html += this.escapeHtml(log.endpoint_url || '-');
        html += '</div>';

        // WHERE clause
        html += '<h6 class="font-weight-bold mb-2"><i class="feather icon-filter"></i> Cláusula WHERE</h6>';
        html += '<div class="json-viewer mb-3" style="max-height:60px;">';
        html += '<code>' + this.escapeHtml(log.where_clause || '1=1') + '</code>';
        html += '</div>';

        // Request body
        html += '<h6 class="font-weight-bold mb-2"><i class="feather icon-filter"></i> Cuerpo de la Petición</h6>';
        html += '<div class="json-viewer mb-3" style="max-height:60px;">';
        html += '<code>' + this.escapeHtml(log.response_body || '1=1') + '</code>';
        html += '</div>';

        // Request URL completa
        if (log.request_url) {
            html += '<h6 class="font-weight-bold mb-2"><i class="feather icon-external-link"></i> URL de Petición</h6>';
            html += '<div class="json-viewer mb-3" style="max-height:80px; word-break:break-all; font-size:11px;">';
            html += this.escapeHtml(log.request_url);
            html += '</div>';
        }

        // Response body (JSON de ArcGIS) - MOSTRAR COMPLETO
        if (log.response_body && log.response_body.length > 0) {
            var bodyContent = log.response_body;
            
            // Intentar formatear como JSON si es válido
            try {
                var parsed = JSON.parse(log.response_body);
                bodyContent = JSON.stringify(parsed, null, 2);
            } catch(e) {
                // Si falla, mostrar como texto plano (puede estar truncado)
                console.log('JSON parse error:', e.message);
            }

            var kb = log.response_size ? Math.round(log.response_size / 1024) + ' KB' : '';
            html += '<h6 class="font-weight-bold mb-2"><i class="feather icon-code"></i> Respuesta ArcGIS (Response Body)';
            if (kb) html += ' <small class="text-muted">(' + kb + ' - mostrando ' + bodyContent.length + ' caracteres)</small>';
            html += '</h6>';
            html += '<pre class="mb-3" style="max-height:500px; overflow-y:auto; font-size:11px; white-space:pre-wrap; background:#1e1e1e; color:#d4d4d4; padding:10px; border-radius:5px; margin:0;">';
            html += this.escapeHtml(bodyContent);
            html += '</pre>';
        }

        $('#modalLogDetalleBody').html(html);
        var municipioLabel = log.municipio_nombre ? log.municipio_nombre.replace(/_/g, ' ') : 'TODOS';
        $('#modalLogDetalleTitle').html('<i class="feather icon-file-text"></i> Log #' + log.id + ' - ' + municipioLabel);
    },

    /**
     * Confirma y ejecuta limpieza de logs antiguos
     */
    confirmarLimpieza: function () {
        var self = this;

        Swal.fire({
            title: 'Eliminar Logs ArcGIS PAE',
            icon: 'warning',
            html:
                '<div style="text-align:left;padding:0 10px;">' +
                    '<p style="margin-bottom:12px;font-size:0.95rem;">Seleccione qué logs desea eliminar:</p>' +
                    '<select id="selectTipoLimpieza" style="width:100%;padding:10px 12px;border:2px solid #ddd;border-radius:10px;font-size:1rem;font-weight:600;background:#fff;cursor:pointer;">' +
                        '<option value="todos">Eliminar TODOS los logs</option>' +
                        '<option value="7">Más antiguos de 7 días</option>' +
                        '<option value="30">Más antiguos de 30 días</option>' +
                        '<option value="60">Más antiguos de 60 días</option>' +
                        '<option value="90" selected>Más antiguos de 90 días</option>' +
                    '</select>' +
                '</div>',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            cancelButtonColor: '#6c757d',
            confirmButtonText: '<i class="feather icon-trash-2"></i> Eliminar',
            cancelButtonText: 'Cancelar',
            preConfirm: function () {
                return document.getElementById('selectTipoLimpieza').value;
            }
        }).then(function (result) {
            if (result.isConfirmed) {
                var opcion = result.value;
                var params = { op: 'limpiarLogsPaeArcgis' };

                if (opcion === 'todos') {
                    params.dias = 0;
                } else {
                    params.dias = parseInt(opcion);
                }

                $.ajax({
                    url: 'admin/ajax/rqst.php',
                    type: 'POST',
                    dataType: 'json',
                    data: params,
                    success: function (response) {
                        if (response.output && response.output.valid) {
                            var data = response.output.response;
                            var msg = opcion === 'todos'
                                ? 'Se eliminaron ' + data.eliminados + ' registros.'
                                : 'Se eliminaron ' + data.eliminados + ' registros con más de ' + data.dias + ' días.';
                            Swal.fire({ icon: 'success', title: 'Limpieza completada', text: msg });
                            self.cargarEstadisticas();
                            self.cargarLogs();
                        } else {
                            Swal.fire({ icon: 'error', title: 'Error', text: response.output ? response.output.error : 'Error al eliminar' });
                        }
                    }
                });
            }
        });
    },

    /**
     * Formatea bytes a unidad legible
     */
    formatBytes: function (bytes) {
        bytes = parseInt(bytes) || 0;
        if (bytes === 0) return '0 B';
        if (bytes < 1024) return bytes + ' B';
        if (bytes < 1048576) return (bytes / 1024).toFixed(1) + ' KB';
        return (bytes / 1048576).toFixed(1) + ' MB';
    },

    /**
     * Formatea número con separadores
     */
    formatNumber: function (num) {
        return parseInt(num || 0).toLocaleString('es-CO');
    },

    /**
     * Escapa HTML
     */
    escapeHtml: function (str) {
        if (!str) return '';
        var div = document.createElement('div');
        div.appendChild(document.createTextNode(str));
        return div.innerHTML;
    }
};

$(document).ready(function () {
    LOGS_PAE_ARCGIS.init();
});
