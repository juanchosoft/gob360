/**
 * JavaScript para la vista de Logs de API JSON-RPC
 *
 * Muestra historial de peticiones, estadísticas y detalle
 * completo de cada request/response.
 *
 * @author SPIDERSOFTWARE
 * @version 1.0
 */
const LOGS_API_RPC = {

    dataTable: null,
    paginaActual: 0,
    totalRegistros: 0,
    logDetalleActual: null, // Para copiar request/response

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

        // Copiar request/response
        $('#btnCopiarRequest').on('click', function () {
            if (self.logDetalleActual) {
                self.copiarAlPortapapeles(self.logDetalleActual.request_payload || '');
            }
        });

        $('#btnCopiarResponse').on('click', function () {
            if (self.logDetalleActual) {
                self.copiarAlPortapapeles(self.logDetalleActual.response_body || '');
            }
        });
    },

    /**
     * Inicializa DataTable sin paginación interna (se maneja manualmente)
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
                    width: '14%',
                    render: function (data) {
                        return '<small class="font-weight-bold">' + (data || '-') + '</small>';
                    }
                },
                {
                    data: 'metodo_rpc',
                    title: 'Método',
                    width: '14%',
                    render: function (data) {
                        return '<code style="font-size:0.78rem;">' + LOGS_API_RPC.escapeHtml(data || '-') + '</code>';
                    }
                },
                {
                    data: 'request_params',
                    title: 'Parámetros',
                    width: '16%',
                    render: function (data) {
                        if (!data) return '<span class="text-muted">-</span>';
                        try {
                            var params = JSON.parse(data);
                            var partes = [];
                            if (params.vigencia) partes.push('Vig: ' + params.vigencia);
                            if (params.bpin) {
                                var bpin = Array.isArray(params.bpin) ? params.bpin.length + ' BPINs' : params.bpin;
                                partes.push('BPIN: ' + bpin);
                            }
                            return '<small>' + (partes.length > 0 ? partes.join(' | ') : 'Sin filtros') + '</small>';
                        } catch (e) {
                            return '<small>' + LOGS_API_RPC.escapeHtml(data.substring(0, 40)) + '</small>';
                        }
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
                            'ERROR_RPC': 'badge-rpc',
                            'ERROR_CONFIG': 'badge-config'
                        };
                        var cls = clases[data] || 'badge-error';
                        var label = data === 'OK' ? 'OK' : data.replace('ERROR_', '');
                        return '<span class="badge ' + cls + '" style="font-size:0.72rem;">' + label + '</span>';
                    }
                },
                {
                    data: 'total_registros',
                    title: 'Registros',
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
                    data: 'usuario',
                    title: 'Usuario',
                    width: '10%',
                    render: function (data) {
                        return '<small>' + LOGS_API_RPC.escapeHtml(data || '-') + '</small>';
                    }
                },
                {
                    data: 'origen',
                    title: 'Origen',
                    width: '8%',
                    className: 'text-center',
                    render: function (data) {
                        var icons = {
                            'dashboard': 'icon-monitor',
                            'ajax': 'icon-zap',
                            'test': 'icon-terminal',
                            'cron': 'icon-clock'
                        };
                        var icon = icons[data] || 'icon-circle';
                        return '<small><i class="feather ' + icon + '"></i> ' + (data || '-') + '</small>';
                    }
                }
            ],
            order: [],
            paging: false,
            searching: true,
            info: false,
            language: {
                search: 'Buscar en tabla:',
                emptyTable: 'No hay logs registrados aún. Realice una consulta en el Dashboard de Proyectos para generar logs.'
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
            data: { op: 'getEstadisticasLogsApiRpc' },
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
                    $('#statRegistros').text(LOGS_API_RPC.formatNumber(g.total_registros_api || 0));
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
            op: 'getLogsApiRpc',
            fecha_desde: $('#filtroFechaDesde').val(),
            fecha_hasta: $('#filtroFechaHasta').val(),
            estado: $('#filtroEstado').val(),
            metodo_rpc: $('#filtroMetodo').val(),
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
            data: { op: 'getLogDetalleApiRpc', id: logId },
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
        html += '<p><strong>Método RPC:</strong> <code>' + this.escapeHtml(log.metodo_rpc) + '</code></p>';
        html += '<p><strong>Endpoint:</strong> <small>' + this.escapeHtml(log.endpoint_url) + '</small></p>';
        html += '</div>';
        html += '<div class="col-md-6">';
        html += '<p><strong>Estado:</strong> <span class="badge ' + estadoClass + '">' + log.estado + '</span></p>';
        html += '<p><strong>HTTP Code:</strong> <span class="badge badge-' + (log.http_code == 200 ? 'success' : 'danger') + '">' + (log.http_code || '-') + '</span></p>';
        html += '<p><strong>Tiempo:</strong> ' + (log.tiempo_respuesta || '-') + 's</p>';
        html += '<p><strong>Registros:</strong> ' + (log.total_registros || 0) + '</p>';
        html += '</div></div>';

        html += '<div class="row mb-3">';
        html += '<div class="col-md-6"><p><strong>Usuario:</strong> ' + this.escapeHtml(log.usuario || '-') + '</p></div>';
        html += '<div class="col-md-3"><p><strong>IP:</strong> ' + this.escapeHtml(log.ip_solicitante || '-') + '</p></div>';
        html += '<div class="col-md-3"><p><strong>Origen:</strong> ' + this.escapeHtml(log.origen || '-') + '</p></div>';
        html += '</div>';

        // Error detalle
        if (log.error_detalle) {
            html += '<div class="alert alert-danger mb-3">';
            html += '<strong><i class="feather icon-alert-circle"></i> Error:</strong> ' + this.escapeHtml(log.error_detalle);
            html += '</div>';
        }

        // Request Payload
        html += '<h6 class="font-weight-bold mb-2"><i class="feather icon-upload"></i> Request Payload</h6>';
        html += '<div class="json-viewer mb-3" id="jsonRequest">';
        html += this.formatJsonHtml(log.request_payload);
        html += '</div>';

        // Request Params
        html += '<h6 class="font-weight-bold mb-2"><i class="feather icon-sliders"></i> Parámetros de Filtro</h6>';
        html += '<div class="json-viewer mb-3">';
        html += this.formatJsonHtml(log.request_params);
        html += '</div>';

        // Response Body
        html += '<h6 class="font-weight-bold mb-2"><i class="feather icon-download"></i> Response Body</h6>';
        html += '<div class="json-viewer" id="jsonResponse">';
        if (log.response_body) {
            // Si es muy grande, truncar para el visor
            var responsePreview = log.response_body;
            if (responsePreview.length > 50000) {
                responsePreview = responsePreview.substring(0, 50000) + '\n\n... [TRUNCADO - ' + log.response_body.length + ' caracteres totales]';
            }
            html += this.formatJsonHtml(responsePreview);
        } else {
            html += '<span class="json-null">Sin respuesta</span>';
        }
        html += '</div>';

        $('#modalLogDetalleBody').html(html);
        $('#modalLogDetalleTitle').html('<i class="feather icon-file-text"></i> Log #' + log.id + ' - ' + log.metodo_rpc);
    },

    /**
     * Confirma y ejecuta limpieza de logs antiguos
     */
    confirmarLimpieza: function () {
        var self = this;

        Swal.fire({
            title: 'Eliminar Logs',
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
            customClass: {
                popup: 'swal2-popup-custom'
            },
            preConfirm: function () {
                return document.getElementById('selectTipoLimpieza').value;
            }
        }).then(function (result) {
            if (result.isConfirmed) {
                var opcion = result.value;
                var params = { op: 'limpiarLogsApiRpc' };

                if (opcion === 'todos') {
                    params.dias = 0; // Indica eliminar todos
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
     * Formatea JSON como HTML con colores de sintaxis
     */
    formatJsonHtml: function (jsonStr) {
        if (!jsonStr) return '<span class="json-null">null</span>';

        try {
            var obj = JSON.parse(jsonStr);
            var formatted = JSON.stringify(obj, null, 2);
            return this.highlightJson(formatted);
        } catch (e) {
            return this.escapeHtml(jsonStr);
        }
    },

    /**
     * Aplica resaltado de sintaxis a un JSON formateado
     */
    highlightJson: function (json) {
        var escaped = this.escapeHtml(json);
        // Resaltar keys
        escaped = escaped.replace(/"([^"]+)":/g, '<span class="json-key">"$1"</span>:');
        // Resaltar strings (valores)
        escaped = escaped.replace(/: "([^"]*)"/g, ': <span class="json-string">"$1"</span>');
        // Resaltar números
        escaped = escaped.replace(/: (\d+\.?\d*)/g, ': <span class="json-number">$1</span>');
        // Resaltar booleanos
        escaped = escaped.replace(/: (true|false)/g, ': <span class="json-boolean">$1</span>');
        // Resaltar null
        escaped = escaped.replace(/: (null)/g, ': <span class="json-null">$1</span>');
        return escaped;
    },

    /**
     * Copia texto al portapapeles
     */
    copiarAlPortapapeles: function (texto) {
        try {
            // Intentar formatear si es JSON
            var obj = JSON.parse(texto);
            texto = JSON.stringify(obj, null, 2);
        } catch (e) {
            // Usar texto tal cual
        }

        if (navigator.clipboard) {
            navigator.clipboard.writeText(texto).then(function () {
                Swal.fire({
                    icon: 'success',
                    title: 'Copiado',
                    timer: 1500,
                    showConfirmButton: false,
                    toast: true,
                    position: 'top-end'
                });
            });
        } else {
            // Fallback
            var ta = document.createElement('textarea');
            ta.value = texto;
            document.body.appendChild(ta);
            ta.select();
            document.execCommand('copy');
            document.body.removeChild(ta);
            Swal.fire({ icon: 'success', title: 'Copiado', timer: 1500, showConfirmButton: false, toast: true, position: 'top-end' });
        }
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
    LOGS_API_RPC.init();
});
