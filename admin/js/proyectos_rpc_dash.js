/**
 * JavaScript para Dashboard de Proyectos - API JSON-RPC
 *
 * Detecta automáticamente el tipo de consulta según los campos con valor:
 * - Solo vigencia → Consulta ID:1
 * - Solo BPIN único → Consulta ID:2
 * - Vigencia + BPIN(s) → Consulta ID:3
 * - Múltiples BPIN sin vigencia → Consulta ID:4
 *
 * Filtros adicionales en frontend: dependencia, provincia
 *
 * @author SPIDERSOFTWARE
 * @version 3.1.1 (Split-safe: MAPA + PANEL) + UI Panel Pro
 */
const PROYECTOS_RPC = {

    dataTable: null,
    chartEstados: null,
    chartDependencias: null,
    proyectosData: [],
    filtroEstadoActivo: null,

    // ======================= GUARDS (MAPA / PANEL) =======================
    el: function (sel) { return document.querySelector(sel); },
    exists: function (sel) { return !!document.querySelector(sel); },
    $exists: function (sel) { return (window.jQuery && $(sel).length > 0); },

    setTextIf: function (sel, value) { if (this.$exists(sel)) $(sel).text(value); },
    setHtmlIf: function (sel, value) { if (this.$exists(sel)) $(sel).html(value); },
    setCssIf: function (sel, prop, value) { if (this.$exists(sel)) $(sel).css(prop, value); },

    /**
     * Inicializa el dashboard
     */
    init: function () {
        this.initDataTable();
        this.setupEventListeners();

        if (typeof window.DATOS_INICIALES !== 'undefined' && window.DATOS_INICIALES) {
            this.procesarResumen(window.DATOS_INICIALES);
        }
    },

    /**
     * Configura todos los event listeners
     */
    setupEventListeners: function () {
        var self = this;
        // Toggle "Ver más/Ver menos" (Modal Municipio)
$(document).on('click', '#modalMunicipioBody .proj-toggle', function(e){
  e.preventDefault();
  e.stopPropagation();

  var id = $(this).data('target');
  var $wrap = $('#' + id);

  var open = !$wrap.hasClass('is-open');
  $wrap.toggleClass('is-open', open);
  $(this).text(open ? 'Ver menos' : 'Ver más');
});

        // Botón buscar
        if (this.$exists('#btnBuscar')) {
            $('#btnBuscar').on('click', function () {
                self.filtroEstadoActivo = null;
                self.cargarDatos();
            });
        }

        // Botón limpiar
        if (this.$exists('#btnLimpiar')) {
            $('#btnLimpiar').on('click', function () {
                self.limpiarFiltros();
            });
        }

        // Enter en BPIN
        if (this.$exists('#inputBpin')) {
            $('#inputBpin').on('keypress', function (e) {
                if (e.which === 13) {
                    self.filtroEstadoActivo = null;
                    self.cargarDatos();
                }
            });
        }

        // Contador de BPIN en textarea
        if (this.$exists('#inputMultiplesBpin') && this.$exists('#contadorBpin')) {
            $('#inputMultiplesBpin').on('input', function () {
                var bpins = self.parsearMultiplesBpin();
                var n = bpins.length;
                $('#contadorBpin').text(n + ' BPIN ingresado' + (n !== 1 ? 's' : ''));
            });
        }

        // Info consulta
        if (this.$exists('#selectVigencia') || this.$exists('#inputBpin') || this.$exists('#inputMultiplesBpin')) {
            $('#selectVigencia, #inputBpin, #inputMultiplesBpin').on('change input', function () {
                self.actualizarInfoConsulta();
            });
        }

        // Vigencia: recargar al cambiar año
        if (this.$exists('#selectVigencia')) {
            $('#selectVigencia').on('change', function () {
                self.cargarDatos();
            });
        }

        // Dependencia / provincia / municipio: recargar
        if (this.$exists('#selectDependencia') || this.$exists('#selectProvincia') || this.$exists('#selectMunicipio')) {
            $('#selectDependencia, #selectProvincia, #selectMunicipio').on('change', function () {
                self.filtroEstadoActivo = null;
                self.cargarDatos();
            });
        }

        // Click estados
        $(document).on('click', '.estado-card-click', function () {
            var estado = $(this).data('estado');
            self.filtrarPorEstado(estado);
        });

        // Click fila tabla (solo si existe tabla y DT)
        $(document).on('click', '#tablaProyectos tbody tr', function (e) {
            // 👇 importante: si el click fue en "Ver más/menos", NO abrir modal
            if ($(e.target).closest('.proj-toggle').length) return;

            if (!self.dataTable) return;
            var data = self.dataTable.row(this).data();
            if (data) self.mostrarDetalle(data);
        });

        // Toggle "Ver más/Ver menos" en columna Proyecto (solo PANEL)
        $(document).on('click', '.proj-toggle', function (e) {
            e.preventDefault();
            e.stopPropagation();

            var id = $(this).data('id');
            var $s = $('#' + id + '_s');
            var $f = $('#' + id + '_f');

            var abierto = !$f.hasClass('d-none');
            if (abierto) {
                $f.addClass('d-none');
                $s.removeClass('d-none');
                $(this).text('Ver más');
            } else {
                $s.addClass('d-none');
                $f.removeClass('d-none');
                $(this).text('Ver menos');
            }
        });

        // Click municipio del mapa
        $(document).on('click', '.mapa-rpc-municipio', function () {
            var nombreApi = $(this).data('municipio');
            var titulo = $(this).attr('title') || nombreApi || 'Sin nombre';
            if (nombreApi) self.mostrarDetalleMunicipio(nombreApi, titulo);
        });

        this.actualizarInfoConsulta();
    },

    // ======================= ESTILOS NEXT-LEVEL (FRONT ONLY) =======================
    getEstadoClass: function (estado) {
        var e = (estado || '').toLowerCase();

        if (e.includes('liquid')) return 'estado--liquidacion';
        if (e.includes('en proceso') || e.includes('ejecuc') || e.includes('proceso')) return 'estado--proceso';
        if (e.includes('termin') || e.includes('final') || e.includes('entreg') || e.includes('liquidado')) return 'estado--terminado';
        if (e.includes('suspend')) return 'estado--suspendido';
        if (e.includes('formul')) return 'estado--formulacion';
        if (e.includes('contrat')) return 'estado--contratacion';

        return 'estado--otro';
    },

    getBarClass: function (pct) {
        var p = parseFloat(pct || 0);
        if (p <= 0) return 'pb--zero';
        if (p >= 80) return 'pb--ok';
        if (p >= 50) return 'pb--warn';
        return 'pb--bad';
    },

    /**
     * Actualiza texto informativo según campos
     */
    actualizarInfoConsulta: function () {
        if (!this.$exists('#infoConsulta')) return;

        var vigencia = this.$exists('#selectVigencia') ? ($('#selectVigencia').val() || '') : '';
        var bpinUnico = this.$exists('#inputBpin') ? ($('#inputBpin').val().trim()) : '';
        var bpinsMultiples = this.parsearMultiplesBpin();
        var totalBpin = (bpinUnico ? 1 : 0) + bpinsMultiples.length;

        var msg = '<i class="feather icon-info"></i> ';

        if (vigencia && totalBpin > 0) {
            msg += 'Consulta ID:3 - Vigencia ' + vigencia + ' + ' + totalBpin + ' BPIN(s)';
        } else if (!vigencia && totalBpin > 1) {
            msg += 'Consulta ID:4 - ' + totalBpin + ' BPIN sin vigencia';
        } else if (!vigencia && totalBpin === 1) {
            msg += 'Consulta ID:2 - Búsqueda por BPIN específico';
        } else if (vigencia && totalBpin === 0) {
            msg += 'Consulta ID:1 - Todos los proyectos de vigencia ' + vigencia;
        } else {
            msg += 'Seleccione vigencia, ingrese BPIN, o combine ambos filtros.';
        }

        $('#infoConsulta').html(msg);
    },

    /**
     * Parsea textarea múltiples BPIN
     */
    parsearMultiplesBpin: function () {
        if (!this.$exists('#inputMultiplesBpin')) return [];
        var texto = $('#inputMultiplesBpin').val().trim();
        if (!texto) return [];

        var bpins = texto.split(/[\n,;]+/);
        var resultado = [];

        bpins.forEach(function (b) {
            var limpio = b.trim();
            if (limpio.length > 0) resultado.push(limpio);
        });

        return resultado;
    },

    /**
     * Construye params
     */
    construirParametros: function () {
        var params = {};

        var vigencia = this.$exists('#selectVigencia') ? ($('#selectVigencia').val() || '') : '';
        var bpinUnico = this.$exists('#inputBpin') ? $('#inputBpin').val().trim() : '';
        var bpinsMultiples = this.parsearMultiplesBpin();

        var todosBpin = [];
        if (bpinUnico) todosBpin.push(bpinUnico);
        todosBpin = todosBpin.concat(bpinsMultiples);

        // duplicados
        todosBpin = todosBpin.filter(function (val, idx, arr) {
            return arr.indexOf(val) === idx;
        });

        if (!vigencia && todosBpin.length === 0) {
            Swal.fire({
                icon: 'warning',
                title: 'Ingrese al menos un filtro',
                text: 'Seleccione una vigencia o ingrese al menos un código BPIN para consultar.',
                confirmButtonColor: '#20427f'
            });
            return null;
        }

        if (vigencia) params.vigencia = vigencia;

        if (todosBpin.length === 1) params.bpin = todosBpin[0];
        else if (todosBpin.length > 1) params.bpin = JSON.stringify(todosBpin);

        // filtros adicionales
        if (this.$exists('#selectDependencia')) {
            var dep = $('#selectDependencia').val();
            if (dep && dep !== 'Todas') params.dependencia = dep;
        }

        if (this.$exists('#selectProvincia')) {
            var prov = $('#selectProvincia').val();
            if (prov && prov !== 'Todas') params.provincia = prov;
        }

        if (this.$exists('#selectMunicipio')) {
            var mun = $('#selectMunicipio').val();
            if (mun && mun !== 'Todos') params.municipio = mun;
        }

        return params;
    },

    /**
     * Inicializa DataTable (solo PANEL)
     */
    initDataTable: function () {

    if (!this.$exists('#tablaProyectos')) return;

    if ($.fn.DataTable.isDataTable('#tablaProyectos')) {
        this.dataTable = $('#tablaProyectos').DataTable();
        return;
    }

    this.dataTable = $('#tablaProyectos').DataTable({
        data: [],
        autoWidth: false,
        scrollX: false,
        responsive: false,
      columns: [
{
    data: 'bpin',
    width: '12%',
    className: 'text-center'
},
{
    data: 'nombre',
    width: '32%'   // un poco menos
},
{
    data: 'dependencia',
    width: '14%'
},
{
    data: 'estado',
    width: '14%',  // un poco más para que quepa completo
    className: 'text-center',
    render: function (data) {
        return PROYECTOS_RPC.renderEstadoBadge(data);
    }
},
{
    data: 'avance_fisico',
    width: '9%'
},
{
    data: 'avance_financiero',
    width: '9%'
},
{
    data: 'valor_total',
    width: '12%',
    className: 'text-right',
    render: function (data) {
        return '<strong style="white-space:nowrap;">' + 
               PROYECTOS_RPC.formatMoneyFull(data) + 
               '</strong>';
    }
},
{
    data: 'vigencia',
    width: '8%',
    className: 'text-center',
    render: function(data){
        return '<span style="white-space:nowrap;font-weight:600;">' + (data || '-') + '</span>';
    }
}
],
        order: [[6, 'desc']],
        pageLength: 15,
        language: {
            search: 'Buscar en tabla:',
            lengthMenu: 'Mostrar _MENU_ registros',
            info: 'Mostrando _START_ a _END_ de _TOTAL_ proyectos',
            infoEmpty: 'Sin proyectos disponibles',
            emptyTable: 'Realice una consulta para ver los proyectos',
            paginate: { first: 'Primera', last: 'Última', next: 'Sig.', previous: 'Ant.' }
        }
    });
},
    /**
     * Carga datos desde API via AJAX
     */
    cargarDatos: function () {
        var self = this;
        var params = this.construirParametros();
        if (!params) return;

        params.op = 'getResumenProyectosRpc';

        if (this.$exists('#dashLoader')) $('#dashLoader').css('display', 'flex');
        if (this.$exists('#dashContent')) $('#dashContent').css('opacity', '0.5');
        if (this.$exists('#btnBuscar')) $('#btnBuscar').prop('disabled', true).html('<i class="feather icon-loader"></i> Consultando...');

        $.ajax({
            url: 'admin/ajax/rqst.php',
            type: 'POST',
            dataType: 'json',
            data: params,
            timeout: 35000,
            success: function (response) {
                if (response.output && response.output.valid) {
                    self.procesarResumen(response.output.response);

                    var total = response.output.response.total_proyectos || 0;
                    Swal.fire({
                        icon: 'success',
                        title: total + ' proyectos encontrados',
                        timer: 2000,
                        showConfirmButton: false,
                        toast: true,
                        position: 'top-end'
                    });
                } else {
                    var errorMsg = response.output ? response.output.error : 'Error desconocido';
                    Swal.fire({
                        icon: 'warning',
                        title: 'Sin resultados',
                        text: errorMsg,
                        confirmButtonColor: '#20427f'
                    });
                    self.limpiarDashboard();
                }
            },
            error: function (xhr, status, error) {
                console.error('[ProyectosRPC] Error AJAX:', status, error);
                var msg = 'No se pudo conectar con el servidor.';
                if (status === 'timeout') msg = 'La consulta tardó demasiado. Intente con filtros más específicos.';

                Swal.fire({
                    icon: 'error',
                    title: 'Error de conexión',
                    text: msg,
                    confirmButtonColor: '#20427f'
                });
                self.limpiarDashboard();
            },
            complete: function () {
                if (self.$exists('#dashLoader')) $('#dashLoader').hide();
                if (self.$exists('#dashContent')) $('#dashContent').css('opacity', '1');
                if (self.$exists('#btnBuscar')) $('#btnBuscar').prop('disabled', false).html('<i class="feather icon-search"></i> Buscar');
            }
        });
    },

    /**
     * Procesa resumen y actualiza componentes
     */
    procesarResumen: function (resumen) {
        this.proyectosData = resumen.proyectos || [];

        this.renderKPIs(resumen);
        this.renderTabla(this.proyectosData);
        this.renderGrafico(resumen.estados || {});
        this.renderGraficoDependencias(resumen.dependencias || {});
        this.renderProvincias(resumen.provincias || []);
        this.renderMapa(resumen.municipios || {});

        // Dependencias (select)
        if (this.$exists('#selectDependencia')) {
            var dependencias = Object.keys(resumen.dependencias || {});
            dependencias.sort();

            var select = $('#selectDependencia');
            var valorActual = select.val();
            select.find('option:not(:first)').remove();

            dependencias.forEach(function (dep) {
                select.append('<option value="' + PROYECTOS_RPC.escapeHtml(dep) + '">' +
                    PROYECTOS_RPC.escapeHtml(dep) + '</option>');
            });

            if (valorActual && select.find('option[value="' + valorActual + '"]').length) {
                select.val(valorActual);
            }

            // Si hay una dependencia pendiente (venida por GET desde el panel), aplicarla
            if (typeof PROYECTOS_RPC._pendingDependencia !== 'undefined' && PROYECTOS_RPC._pendingDependencia) {
                var pendDep = PROYECTOS_RPC._pendingDependencia;
                PROYECTOS_RPC._pendingDependencia = null;
                if (select.find('option[value="' + pendDep + '"]').length) {
                    select.val(pendDep);
                    // Relanzar búsqueda con el filtro de dependencia aplicado
                    setTimeout(function () { PROYECTOS_RPC.cargarDatos(); }, 100);
                    return;
                }
            }
        }

        // Actualizar botón "Ver Panel" con los parámetros del filtro actual
        this.actualizarBotonesPanel();
    },

    /**
     * Construye y actualiza el href del botón Ver Panel con los filtros activos
     */
    actualizarBotonesPanel: function () {
        var params = [];
        var vig = this.$exists('#selectVigencia') ? $('#selectVigencia').val() : '';
        var mun = this.$exists('#selectMunicipio') ? $('#selectMunicipio').val() : '';
        var dep = this.$exists('#selectDependencia') ? $('#selectDependencia').val() : '';
        var prov = this.$exists('#selectProvincia') ? $('#selectProvincia').val() : '';

        if (vig)  params.push('vigencia=' + encodeURIComponent(vig));
        if (mun && mun !== 'Todos')  params.push('municipio=' + encodeURIComponent(mun));
        if (dep && dep !== 'Todas')  params.push('dependencia=' + encodeURIComponent(dep));
        if (prov && prov !== 'Todas') params.push('provincia=' + encodeURIComponent(prov));

        var url = 'panel_proyectos.php' + (params.length ? '?' + params.join('&') : '');

        $('a[href^="panel_proyectos.php"], a[href="panel_proyectos.php"]').attr('href', url);
    },

    /**
     * KPIs
     */
    renderKPIs: function (resumen) {
        if (this.$exists('#kpiTotalProyectos')) $('#kpiTotalProyectos').text(resumen.total_proyectos || 0);
        if (this.$exists('#kpiValorTotal')) $('#kpiValorTotal').text(this.formatMoney(resumen.valor_total));
        if (this.$exists('#kpiValorEjecutado')) $('#kpiValorEjecutado').text(this.formatMoney(resumen.valor_ejecutado_total));
        if (this.$exists('#kpiAporteGobernacion')) $('#kpiAporteGobernacion').text(this.formatMoney(resumen.aporte_gobernacion));
        if (this.$exists('#kpiAporteMunicipal')) $('#kpiAporteMunicipal').text(this.formatMoney(resumen.aporte_municipal));
        if (this.$exists('#kpiAporteNacional')) $('#kpiAporteNacional').text(this.formatMoney(resumen.aporte_nacional));

        if (this.$exists('#pillTotalProyectos')) $('#pillTotalProyectos').text(resumen.total_proyectos || 0);

        var avFisico = parseFloat(resumen.promedio_avance_fisico || 0);
        var avFinanciero = parseFloat(resumen.promedio_avance_financiero || 0);
        var pctFisico = (avFisico <= 1 ? avFisico * 100 : avFisico).toFixed(1);
        var pctFinanciero = (avFinanciero <= 1 ? avFinanciero * 100 : avFinanciero).toFixed(1);

        // ✅ barras
        if (this.$exists('#kpiAvanceFisico')) $('#kpiAvanceFisico').css('width', pctFisico + '%').text(pctFisico + '%');
        if (this.$exists('#kpiAvanceFinanciero')) $('#kpiAvanceFinanciero').css('width', Math.min(pctFinanciero, 100) + '%').text(pctFinanciero + '%');

        // ✅ textos (tu vista Panel los tiene)
        if (this.$exists('#kpiAvanceFisicoTxt')) $('#kpiAvanceFisicoTxt').text(pctFisico + '%');
        if (this.$exists('#kpiAvanceFinancieroTxt')) $('#kpiAvanceFinancieroTxt').text(pctFinanciero + '%');

        // Estados
        if (!this.$exists('#contenedorEstados')) return;

        var estadosHtml = '';
        var estados = resumen.estados || {};
        var totalProyectos = resumen.total_proyectos || 1;

        Object.keys(estados).forEach(function (estado) {
            var count = estados[estado];
            var pct = ((count / totalProyectos) * 100).toFixed(1);
            var color = PROYECTOS_RPC.getColorEstado(estado);
            var activo = PROYECTOS_RPC.filtroEstadoActivo === estado ? 'border: 2px solid ' + color + ';' : '';

            estadosHtml += '<div class="estado-card-click d-flex justify-content-between align-items-center p-2 mb-2 rounded" ' +
                'style="cursor:pointer;' + activo + '" data-estado="' + PROYECTOS_RPC.escapeHtml(estado) + '">' +
                '<span style="font-size:0.85rem;">' + PROYECTOS_RPC.escapeHtml(estado) + '</span>' +
                '<span class="badge" style="background-color:' + color + ';color:#fff;">' + count +
                ' <small>(' + pct + '%)</small></span></div>';
        });

        $('#contenedorEstados').html(estadosHtml || '<p class="text-muted text-center">Sin datos</p>');
    },

    /**
     * Tabla
     */
    renderTabla: function (proyectos) {
        if (!this.dataTable) return;
        this.dataTable.clear();
        this.dataTable.rows.add(proyectos || []);
        this.dataTable.draw();

        if (this.$exists('#badgeTotalTabla')) $('#badgeTotalTabla').text((proyectos || []).length + ' proyectos');
    },

    /**
     * Gráfico dona (PRO)
     */
    renderGrafico: function (estados) {
        if (!this.exists('#chartEstados')) return;

        var labels = Object.keys(estados || {});
        var series = Object.values(estados || {});
        var colors = labels.map(function (e) { return PROYECTOS_RPC.getColorEstado(e); });

        if (this.chartEstados) {
            this.chartEstados.destroy();
            this.chartEstados = null;
        }

        if (labels.length === 0) return;

        var options = {
            chart: {
                type: 'donut',
                height: 340,
                toolbar: { show: false },
                dropShadow: { enabled: true, top: 6, left: 0, blur: 12, opacity: 0.18 }
            },
            series: series,
            labels: labels,
            colors: colors,
            stroke: { width: 2, colors: ['#fff'] },
            legend: {
                position: 'bottom',
                fontSize: '12px',
                fontWeight: 800,
                markers: { width: 10, height: 10, radius: 10 }
            },
            dataLabels: { enabled: true, style: { fontWeight: 900 } },
            plotOptions: {
                pie: {
                    donut: {
                        size: '62%',
                        labels: {
                            show: true,
                            name: { show: true, fontSize: '12px', fontWeight: 900 },
                            value: { show: true, fontSize: '20px', fontWeight: 1000 },
                            total: {
                                show: true,
                                label: 'Total proyectos',
                                fontSize: '12px',
                                fontWeight: 900,
                                formatter: function () {
                                    return series.reduce(function (a, b) { return a + b; }, 0);
                                }
                            }
                        }
                    }
                }
            },
            tooltip: { y: { formatter: function (val) { return val + ' proyectos'; } } },
            responsive: [{ breakpoint: 480, options: { chart: { height: 280 } } }]
        };

        var el = document.querySelector('#chartEstados');
        if (el) {
            this.chartEstados = new ApexCharts(el, options);
            this.chartEstados.render();
        }
    },

    /**
     * Gráfico dependencias (PRO)
     */
    renderGraficoDependencias: function (dependencias) {
        if (!this.exists('#chartDependencias')) return;

        if (this.chartDependencias) {
            this.chartDependencias.destroy();
            this.chartDependencias = null;
        }

        var labels = Object.keys(dependencias || {});
        var series = Object.values(dependencias || {});
        if (labels.length === 0) return;

        var pares = labels.map(function (l, i) { return { label: l, value: series[i] }; });
        pares.sort(function (a, b) { return b.value - a.value; });
        pares = pares.slice(0, 10);

        var labelsCortos = pares.map(function (p) {
            var n = p.label.replace('Secretaría de ', 'Sec. ')
                .replace('Secretaría del ', 'Sec. ')
                .replace('Oficina para la ', 'Of. ');
            return n.length > 28 ? n.substring(0, 26) + '...' : n;
        });

        var options = {
            chart: {
                type: 'bar',
                height: Math.max(pares.length * 36, 260),
                toolbar: { show: false },
                dropShadow: { enabled: true, top: 6, left: 0, blur: 10, opacity: 0.14 }
            },
            series: [{ name: 'Proyectos', data: pares.map(function (p) { return p.value; }) }],
            xaxis: {
                categories: labelsCortos,
                labels: { style: { fontSize: '12px', fontWeight: 800 } }
            },
            plotOptions: {
                bar: {
                    horizontal: true,
                    barHeight: '70%',
                    borderRadius: 10,
                    distributed: true,
                    dataLabels: { position: 'top' }
                }
            },
            dataLabels: {
                enabled: true,
                offsetX: 10,
                style: { fontSize: '12px', fontWeight: 1000 },
                formatter: function (val) { return val; }
            },
            grid: {
                borderColor: 'rgba(2,6,23,.08)',
                xaxis: { lines: { show: true } },
                yaxis: { lines: { show: false } }
            },
            tooltip: {
                y: { formatter: function (val) { return val + ' proyectos'; } },
                x: { formatter: function (val, opts) { return pares[opts.dataPointIndex].label; } }
            }
        };

        var el = document.querySelector('#chartDependencias');
        if (el) {
            this.chartDependencias = new ApexCharts(el, options);
            this.chartDependencias.render();
        }
    },

    /**
     * Mapa + Top municipios (solo MAPA)
     */
    renderMapa: function (municipiosConteo) {
        if (!this.$exists('.mapa-rpc-municipio') && !this.$exists('#topMunicipiosContainer')) return;

        // Municipio seleccionado en el filtro
        var munFiltro = this.$exists('#selectMunicipio') ? ($('#selectMunicipio').val() || 'Todos') : 'Todos';
        var soloUno   = munFiltro && munFiltro !== 'Todos';

        if (this.$exists('.mapa-rpc-municipio')) $('.mapa-rpc-municipio').attr('fill', '#EEF2F7');

        var totalConProyectos = 0;

        if (this.$exists('.mapa-rpc-municipio')) {
            $('.mapa-rpc-municipio').each(function () {
                var nombre = $(this).data('municipio');
                var count  = (municipiosConteo && municipiosConteo[nombre]) ? municipiosConteo[nombre] : 0;

                if (soloUno) {
                    // Solo colorear el municipio filtrado; el resto queda gris
                    if (nombre === munFiltro) {
                        totalConProyectos++;
                        var color;
                        if (count >= 16) color = '#1e40af';
                        else if (count >= 6) color = '#3b82f6';
                        else if (count > 0) color = '#93c5fd';
                        else color = '#EEF2F7';
                        $(this).attr('fill', color).attr('stroke', '#1e40af').attr('stroke-width', '2');
                    } else {
                        $(this).attr('stroke', '#ccc').attr('stroke-width', '0.5');
                    }
                } else {
                    if (count > 0) {
                        totalConProyectos++;
                        var color;
                        if (count >= 16) color = '#1e40af';
                        else if (count >= 6) color = '#3b82f6';
                        else color = '#93c5fd';
                        $(this).attr('fill', color);
                    }
                }
            });
        }

        if (this.$exists('#badgeMunicipios')) {
            $('#badgeMunicipios').text(totalConProyectos + ' municipio' + (totalConProyectos !== 1 ? 's' : ''));
        }

        if (this.$exists('#topMunicipiosContainer')) {
            var pares = [];
            Object.keys(municipiosConteo || {}).forEach(function (nombre) {
                pares.push({ nombre: nombre, total: municipiosConteo[nombre] });
            });
            pares.sort(function (a, b) { return b.total - a.total; });

            var html = '';
            if (pares.length === 0) {
                html = '<div class="text-center py-4">' +
                    '<i class="feather icon-inbox text-muted" style="font-size:2rem;"></i>' +
                    '<p class="text-muted mt-2 mb-0">Sin datos de municipios</p></div>';
            } else {
                var maxVal = pares[0].total || 1;
                var self = this;

                pares.forEach(function (item, idx) {
                    var pct = ((item.total / maxVal) * 100).toFixed(0);
                    var color = item.total >= 16 ? '#1e40af' : (item.total >= 6 ? '#3b82f6' : '#93c5fd');

                    html += '<div class="top-mun-item">' +
                        '<div style="flex:1;">' +
                        '<div class="d-flex justify-content-between mb-1">' +
                        '<small class="font-weight-bold" style="color:var(--ink);">' + (idx + 1) + '. ' + self.escapeHtml(item.nombre) + '</small>' +
                        '<span class="badge" style="background:' + color + ';color:#fff;font-size:0.75rem;">' + item.total + '</span>' +
                        '</div>' +
                        '<div class="progress" style="height:5px;">' +
                        '<div class="progress-bar" style="width:' + pct + '%;background:' + color + ';"></div>' +
                        '</div>' +
                        '</div></div>';
                });
            }

            $('#topMunicipiosContainer').html(html);
        }

        this.setupTooltipMapa(municipiosConteo || {});
    },

    /**
     * Tooltip mapa
     */
    setupTooltipMapa: function (municipiosConteo) {
        if (!this.$exists('#tooltipMapa') || !this.$exists('.mapa-rpc-municipio')) return;

        var $tooltip = $('#tooltipMapa');

        $('.mapa-rpc-municipio').off('mouseenter.rpcTooltip mousemove.rpcTooltip mouseleave.rpcTooltip');

        $('.mapa-rpc-municipio').on('mouseenter.rpcTooltip', function (e) {
            var nombre = $(this).data('municipio') || $(this).attr('title') || 'Sin nombre';
            var count = municipiosConteo[nombre] || 0;
            var texto = nombre + ': ' + count + ' proyecto' + (count !== 1 ? 's' : '');
            $tooltip.text(texto).css({
                display: 'block',
                left: e.clientX + 12 + 'px',
                top: e.clientY - 30 + 'px'
            });
        });

        $('.mapa-rpc-municipio').on('mousemove.rpcTooltip', function (e) {
            $tooltip.css({
                left: e.clientX + 12 + 'px',
                top: e.clientY - 30 + 'px'
            });
        });

        $('.mapa-rpc-municipio').on('mouseleave.rpcTooltip', function () {
            $tooltip.hide();
        });
    },

    /**
     * Provincias
     */
    renderProvincias: function (provincias) {
        if (!this.$exists('#selectProvincia')) return;

        var select = $('#selectProvincia');
        var valorActual = select.val();
        select.find('option:not(:first)').remove();

        (provincias || []).forEach(function (prov) {
            select.append('<option value="' + PROYECTOS_RPC.escapeHtml(prov) + '">' +
                PROYECTOS_RPC.escapeHtml(prov) + '</option>');
        });

        if (valorActual && select.find('option[value="' + valorActual + '"]').length) {
            select.val(valorActual);
        }
    },

    /**
     * Filtra por estado
     */
    filtrarPorEstado: function (estado) {
        if (this.filtroEstadoActivo === estado) {
            this.filtroEstadoActivo = null;
            this.renderTabla(this.proyectosData);
        } else {
            this.filtroEstadoActivo = estado;
            var filtrados = this.proyectosData.filter(function (p) { return p.estado === estado; });
            this.renderTabla(filtrados);
        }

        if (this.$exists('.estado-card-click')) {
            $('.estado-card-click').css('border', '1px solid rgba(15,23,42,.10)');
            if (this.filtroEstadoActivo) {
                var color = this.getColorEstado(this.filtroEstadoActivo);
                $('.estado-card-click[data-estado="' + this.filtroEstadoActivo + '"]').css('border', '2px solid ' + color);
            }
        }
    },
getBarColorFisico: function(pct){
  var p = parseFloat(pct || 0);
  if (p >= 80) return '#16a34a';      // verde
  if (p >= 50) return '#f59e0b';      // amarillo
  if (p > 0)   return '#ef4444';      // rojo
  return '#94a3b8';                   // gris
},
getBarColorFinan: function(pct){
  var p = parseFloat(pct || 0);
  if (p >= 80) return '#2563eb';      // azul
  if (p >= 50) return '#7c3aed';      // morado
  if (p > 0)   return '#0ea5e9';      // celeste
  return '#94a3b8';
},
renderBar: function(pct, color){
  pct = Math.max(0, Math.min(parseFloat(pct || 0), 100)).toFixed(1);
  return '<div class="progress pb" title="'+pct+'%">' +
           '<div class="progress-bar" style="width:'+pct+'%;background:'+color+';">'+pct+'%</div>' +
         '</div>';
},
renderEstadoBadge: function(estado){
  var txt = (estado || '-');
  var color = this.getColorEstado(txt);
  return '<span class="badge-estado" style="background:'+color+';">' +
           '<span class="dot"></span>' + this.escapeHtml(txt) +
         '</span>';
},
    /**
     * Modal municipio
     */
    mostrarDetalleMunicipio: function (nombreApi, tituloMapa) {
        var self = this;

        if (!this.$exists('#modalMunicipio') || !this.$exists('#modalMunicipioBody') || !this.$exists('#modalMunicipioTitle')) return;

        var proyectosMun = this.proyectosData.filter(function (p) {
            var lista = p.municipios_lista || [];
            for (var i = 0; i < lista.length; i++) {
                if ((lista[i] || '').trim() === nombreApi) return true;
            }
            return false;
        });

        var totalProyectos = proyectosMun.length;

        var valorTotal = 0, valorEjecutado = 0;
        var estadosConteo = {};
        proyectosMun.forEach(function (p) {
            valorTotal += parseFloat(p.valor_total || 0);
            valorEjecutado += parseFloat(p.valor_ejecutado || 0);
            var est = p.estado || 'Sin Estado';
            estadosConteo[est] = (estadosConteo[est] || 0) + 1;
        });

        var html = '';
        html += '<div class="row mb-3">';
        html += '<div class="col-md-3 text-center"><div class="p-3 rounded" style="background:#eff6ff;border:1px solid #93c5fd;">';
        html += '<div style="font-size:2rem;font-weight:900;color:#1e40af;">' + totalProyectos + '</div>';
        html += '<small class="text-muted font-weight-bold">Proyectos</small></div></div>';

        html += '<div class="col-md-4 text-center"><div class="p-3 rounded" style="background:#f0fdf4;border:1px solid #86efac;">';
        html += '<div style="font-size:1.1rem;font-weight:900;color:#16a34a;">' + this.formatMoney(valorTotal) + '</div>';
        html += '<small class="text-muted font-weight-bold">Valor Total</small></div></div>';

        html += '<div class="col-md-5 text-center"><div class="p-3 rounded" style="background:#fffbeb;border:1px solid #fcd34d;">';
        html += '<div style="font-size:1.1rem;font-weight:900;color:#d97706;">' + this.formatMoney(valorEjecutado) + '</div>';
        html += '<small class="text-muted font-weight-bold">Valor Ejecutado</small></div></div>';
        html += '</div>';

        var estadosKeys = Object.keys(estadosConteo);
        if (estadosKeys.length > 0) {
            html += '<div class="mb-3"><strong>Estados:</strong> ';
            estadosKeys.forEach(function (est) {
                var estClass = self.getEstadoClass(est);
                html += '<span class="estado-pill ' + estClass + ' mr-1 mb-1">' +
                    self.escapeHtml(est) + ' (' + estadosConteo[est] + ')</span>';
            });
            html += '</div>';
        }

        if (totalProyectos === 0) {
            html += '<div class="text-center py-4">';
            html += '<i class="feather icon-inbox text-muted" style="font-size:2.5rem;"></i>';
            html += '<p class="text-muted mt-2">Este municipio no tiene proyectos en la consulta actual</p>';
            html += '</div>';
        } else {
            html += '<div class="table-responsive" style="max-height:400px;overflow-y:auto;">';
            html += '<table class="table table-sm table-hover table-striped mb-0">';
            html += '<thead class="thead-light"><tr>';
            html += '<th>BPIN</th><th>Proyecto</th><th>Dependencia</th><th>Estado</th>';
            html += '<th class="text-right">Valor Total</th><th>Av. Físico</th>';
            html += '</tr></thead><tbody>';

            proyectosMun.forEach(function (p, idx) {
                var avFisico = parseFloat(p.avance_fisico || 0);
                var pctFisico = (avFisico <= 1 ? avFisico * 100 : avFisico).toFixed(1);

                var nombre = p.nombre || 'Sin nombre';
                var nombreCorto = nombre.length > 60 ? nombre.substring(0, 58) + '...' : nombre;

            var nombre = (p.nombre || 'Sin nombre').toString().trim();
var safeNombre = self.escapeHtml(nombre);

// id único para toggle por fila (bpin + index)
var pid = 'mun_proj_' + (p.bpin || 'x') + '_' + (idx || 0);

// Corto SOLO para la vista clamp (pero guardamos completo)
var nombreCorto = nombre.length > 80 ? nombre.substring(0, 78) + '...' : nombre;
var safeCorto = self.escapeHtml(nombreCorto);

html += '<tr style="cursor:pointer;" class="fila-proyecto-mun" data-bpin="' + self.escapeHtml(p.bpin || '') + '">';
html += '<td><span class="badge badge-light-primary" style="font-size:0.75rem;">' + self.escapeHtml(p.bpin || '-') + '</span></td>';

// ✅ Proyecto con Ver más / Ver menos dentro de la celda
html += '<td>';
html += '  <div class="proj-wrap" id="'+pid+'">';
html += '    <div class="proj-text" title="'+safeNombre+'">'+ safeNombre +'</div>';
// Solo mostramos botón si es largo
if (nombre.length > 120) {
  html += '    <button type="button" class="proj-toggle" data-target="'+pid+'">Ver más</button>';
}
html += '  </div>';
html += '</td>';
                html += '<td><small title="' + self.escapeHtml(nombre) + '">' + self.escapeHtml(nombreCorto) + '</small></td>';
                html += '<td><small>' + self.escapeHtml(p.dependencia || '-') + '</small></td>';

                var estadoTxt = (p.estado || '-');
                var estadoClass = self.getEstadoClass(estadoTxt);
                html += '<td><span class="estado-pill ' + estadoClass + '">' + self.escapeHtml(estadoTxt) + '</span></td>';

                html += '<td class="text-right"><strong>' + self.formatMoney(p.valor_total) + '</strong></td>';

                var barClass = self.getBarClass(pctFisico);
                html += '<td>';
                html += '<div class="progress pb ' + barClass + '" title="' + pctFisico + '%">';
                html += '<div class="progress-bar" style="width:' + pctFisico + '%;">' + pctFisico + '%</div>';
                html += '</div></td>';

                html += '</tr>';
            });

            html += '</tbody></table></div>';
            html += '<small class="text-muted mt-2 d-block">Haga clic en una fila para ver el detalle completo del proyecto</small>';
        }

        $('#modalMunicipioBody').html(html);
        $('#modalMunicipioTitle').html('<i class="feather icon-map-pin"></i> ' + tituloMapa +
            ' <span class="badge badge-primary ml-2">' + totalProyectos + ' proyecto' + (totalProyectos !== 1 ? 's' : '') + '</span>');
        $('#modalMunicipio').modal('show');

        $('#modalMunicipio').off('click', '.fila-proyecto-mun').on('click', '.fila-proyecto-mun', function () {
            var bpin = $(this).data('bpin');
            var proyecto = self.proyectosData.find(function (p) { return p.bpin === bpin; });
            if (proyecto && self.$exists('#modalDetalle')) {
                $('#modalMunicipio').modal('hide');
                setTimeout(function () { self.mostrarDetalle(proyecto); }, 300);
            }
        });
    },

    /**
     * Modal detalle proyecto (solo PANEL)
     */
    mostrarDetalle: function (proyecto) {
        if (!this.$exists('#modalDetalle') || !this.$exists('#modalDetalleBody') || !this.$exists('#modalDetalleTitle')) return;

        var html = '';

        html += '<div class="row mb-3">';
        html += '<div class="col-md-6">';
        html += '<p><strong>BPIN:</strong> <span class="badge badge-primary" style="font-size:0.9rem;">' + this.escapeHtml(proyecto.bpin || '-') + '</span></p>';
        html += '<p><strong>Vigencia:</strong> ' + (proyecto.vigencia || '-') + '</p>';
        html += '<p><strong>Dependencia:</strong> ' + this.escapeHtml(proyecto.dependencia || '-') + '</p>';

        var eTxt = (proyecto.estado || '-');
        html += '<p><strong>Estado:</strong> <span class="estado-pill ' + this.getEstadoClass(eTxt) + '">' +
            this.escapeHtml(eTxt) + '</span></p>';

        html += '</div>';
        html += '<div class="col-md-6">';
        html += '<p><strong>Avance Físico:</strong></p>' + this.renderProgressBar(proyecto.avance_fisico, true);
        html += '<p class="mt-2"><strong>Avance Financiero:</strong></p>' + this.renderProgressBar(proyecto.avance_financiero, false);
        html += '</div></div>';

        html += '<div class="row mb-3"><div class="col-12"><h6 class="font-weight-bold">Nombre del Proyecto</h6>';
        html += '<p>' + this.escapeHtml(proyecto.nombre || '-') + '</p></div></div>';

        html += '<div class="row mb-3"><div class="col-12"><h6 class="font-weight-bold">Desglose Financiero</h6>';
        html += '<table class="table table-sm table-bordered"><tbody>';
        html += '<tr><td>Valor Total</td><td class="text-right"><strong>' + this.formatMoneyFull(proyecto.valor_total) + '</strong></td></tr>';
        html += '<tr><td>Valor Ejecutado</td><td class="text-right">' + this.formatMoneyFull(proyecto.valor_ejecutado) + '</td></tr>';
        html += '<tr><td>Aporte Gobernación</td><td class="text-right">' + this.formatMoneyFull(proyecto.aporte_gobernacion) + '</td></tr>';
        html += '<tr><td>Aporte Municipal</td><td class="text-right">' + this.formatMoneyFull(proyecto.aporte_municipal) + '</td></tr>';
        html += '<tr><td>Aporte Nacional</td><td class="text-right">' + this.formatMoneyFull(proyecto.aporte_nacional) + '</td></tr>';
        html += '</tbody></table></div></div>';

        html += '<div class="row mb-3">';
        html += '<div class="col-md-6"><h6 class="font-weight-bold">Municipios</h6><ul class="mb-0">';
        (proyecto.municipios_lista || []).forEach(function (m) { html += '<li>' + PROYECTOS_RPC.escapeHtml(m) + '</li>'; });
        if (!(proyecto.municipios_lista || []).length) html += '<li class="text-muted">Sin información</li>';
        html += '</ul></div>';

        html += '<div class="col-md-6"><h6 class="font-weight-bold">Provincias</h6><ul class="mb-0">';
        (proyecto.provincias_lista || []).forEach(function (p) { html += '<li>' + PROYECTOS_RPC.escapeHtml(p) + '</li>'; });
        if (!(proyecto.provincias_lista || []).length) html += '<li class="text-muted">Sin información</li>';
        html += '</ul></div></div>';

        if (proyecto.observaciones) {
            html += '<div class="row"><div class="col-12"><h6 class="font-weight-bold">Observaciones</h6>';
            html += '<div class="alert alert-info mb-0">' + this.escapeHtml(proyecto.observaciones) + '</div>';
            html += '</div></div>';
        }

        $('#modalDetalleBody').html(html);
        $('#modalDetalleTitle').html('<i class="feather icon-file-text"></i> Proyecto: ' + (proyecto.bpin || 'Sin BPIN'));
        $('#modalDetalle').modal('show');
    },

    /**
     * Limpia filtros
     */
    limpiarFiltros: function () {
        if (this.$exists('#selectVigencia')) $('#selectVigencia').val(new Date().getFullYear());
        if (this.$exists('#inputBpin')) $('#inputBpin').val('');
        if (this.$exists('#inputMultiplesBpin')) $('#inputMultiplesBpin').val('');
        if (this.$exists('#selectDependencia')) $('#selectDependencia').val('Todas');
        if (this.$exists('#selectProvincia')) $('#selectProvincia').val('Todas');
        if (this.$exists('#selectMunicipio')) $('#selectMunicipio').val('Todos');
        if (this.$exists('#contadorBpin')) $('#contadorBpin').text('0 BPIN ingresados');

        this.filtroEstadoActivo = null;
        this.actualizarInfoConsulta();
        this.limpiarDashboard();
    },

    /**
     * Limpia dashboard (según vista)
     */
    limpiarDashboard: function () {
        this.proyectosData = [];

        this.renderTabla([]);

        if (this.$exists('#kpiTotalProyectos')) $('#kpiTotalProyectos').text('0');
        if (this.$exists('#pillTotalProyectos')) $('#pillTotalProyectos').text('0');

        if (this.$exists('#kpiValorTotal')) $('#kpiValorTotal').text('$0');
        if (this.$exists('#kpiValorEjecutado')) $('#kpiValorEjecutado').text('$0');
        if (this.$exists('#kpiAporteGobernacion')) $('#kpiAporteGobernacion').text('$0');
        if (this.$exists('#kpiAporteMunicipal')) $('#kpiAporteMunicipal').text('$0');
        if (this.$exists('#kpiAporteNacional')) $('#kpiAporteNacional').text('$0');

        if (this.$exists('#kpiAvanceFisico')) $('#kpiAvanceFisico').css('width', '0%').text('0%');
        if (this.$exists('#kpiAvanceFinanciero')) $('#kpiAvanceFinanciero').css('width', '0%').text('0%');

        if (this.$exists('#kpiAvanceFisicoTxt')) $('#kpiAvanceFisicoTxt').text('0%');
        if (this.$exists('#kpiAvanceFinancieroTxt')) $('#kpiAvanceFinancieroTxt').text('0%');

        if (this.$exists('#contenedorEstados')) $('#contenedorEstados').html('<p class="text-muted text-center">Realice una consulta</p>');

        if (this.chartEstados) { this.chartEstados.destroy(); this.chartEstados = null; }
        if (this.chartDependencias) { this.chartDependencias.destroy(); this.chartDependencias = null; }

        if (this.$exists('.mapa-rpc-municipio')) $('.mapa-rpc-municipio').attr('fill', '#EEF2F7');
        if (this.$exists('#badgeMunicipios')) $('#badgeMunicipios').text('0 municipios');

        if (this.$exists('#topMunicipiosContainer')) {
            $('#topMunicipiosContainer').html('<div class="text-center py-4">' +
                '<i class="feather icon-inbox text-muted" style="font-size:2rem;"></i>' +
                '<p class="text-muted mt-2 mb-0">Realice una búsqueda para ver los municipios</p></div>');
        }
    },

    // ======================= UTILIDADES =======================
formatMoney: function (valor) {
    var num = parseFloat(valor || 0);

    return num.toLocaleString('es-CO', {
        style: 'currency',
        currency: 'COP',
        minimumFractionDigits: 0,
        maximumFractionDigits: 0
    });
},
    formatMoneyFull: function (valor) {
        var num = parseFloat(valor || 0);
        return '$' + num.toLocaleString('es-CO', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    },

  renderProgressBar: function (valor, esFisico) {
    var val = parseFloat(valor || 0);
    var pct = (val <= 1 ? val * 100 : val);
    pct = Math.min(pct, 100);

    var color = esFisico 
        ? this.getBarColorFisico(pct) 
        : this.getBarColorFinan(pct);

    return this.renderBar(pct, color);
},

  getColorEstado: function (estado) {
  var e = (estado || '').toLowerCase().trim();

  // ✅ Terminación / Terminado / Finalizado / Entregado
  if (e.includes('termin') || e.includes('final') || e.includes('entreg')) return '#16a34a'; // verde

  // ✅ Liquidación / Liquidado
  if (e.includes('liquid')) return '#0f766e'; // teal (premium)

  // ✅ Suspendido / Suspensión
  if (e.includes('suspend')) return '#dc2626'; // rojo

  // ✅ Ejecución / En ejecución
  if (e.includes('ejecuc')) return '#f59e0b'; // ámbar

  // ✅ Contratación / Precontractual
  if (e.includes('contrat') || e.includes('precontract')) return '#2563eb'; // azul

  // ✅ En proceso (por si viene así)
  if (e.includes('proceso')) return '#7c3aed'; // morado

  // ✅ Formulación
  if (e.includes('formul')) return '#64748b'; // gris slate

  // ✅ Default
  return '#6b7280';
},

    escapeHtml: function (str) {
        if (!str) return '';
        var div = document.createElement('div');
        div.appendChild(document.createTextNode(str));
        return div.innerHTML;
    }
};

$(document).ready(function () {
    PROYECTOS_RPC.init();
});