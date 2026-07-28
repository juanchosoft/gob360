/**
 * JavaScript específico para Dashboard PAE ArcGIS
 *
 * Este archivo contiene las funciones necesarias para el dashboard ArcGIS
 */

// Objeto PAE_DASHBOARD con funciones para el mapa
const PAE_DASHBOARD = {
    /**
     * Carga el mapa de municipios con datos de ArcGIS
     */
    cargarMapaMunicipios: function() {
        var self = this;
        var ano = $('#filtro_ano').val() || 'todos';
        
        $.ajax({
            url: 'admin/ajax/rqst.php',
            type: 'POST',
            dataType: 'json',
            data: { 
                op: 'getResumenMunicipiosPae',
                ano: ano
            },
            success: function(response) {
                console.log('[PAE ArcGIS] Respuesta del mapa:', response);
                if (response.output && response.output.valid) {
                    self.renderMapa(response.output.response);
                } else {
                    console.error('[PAE ArcGIS] Error al cargar mapa:', response.output.error);
                }
            },
            error: function(xhr, status, error) {
                console.error('[PAE ArcGIS] Error AJAX al cargar mapa:', error);
            },
            complete: function() {
                // Ocultar loader manualmente cuando termine la llamada
                var loader = document.getElementById('pageLoader');
                if (loader) {
                    loader.classList.remove('active');
                    loader.style.display = 'none';
                }
            }
        });
    },

    /**
     * Pinta el mapa SVG según los datos de la API
     */
    renderMapa: function(data) {
        var municipios = data.municipios || {};
        var totalSedes = data.total_sedes || 0;
        var totalMunicipios = data.total_municipios || 0;
        var municipioSeleccionado = String($('#tbl_municipio_id').val() || '');
        var soloUno = municipioSeleccionado && municipioSeleccionado !== 'todos';

        // Colores según cantidad de sedes
        function getColor(count) {
            if (count <= 0)  return '#EEF2F7';
            if (count <= 3)  return '#10b981';
            if (count <= 6)  return '#3b82f6';
            if (count <= 10) return '#f59e0b';
            if (count <= 20) return '#f97316';
            return '#ef4444';
        }

        // Índice O(1): clave en minúsculas → conteo
        var idx = {};
        for (var k in municipios) {
            idx[k.toLowerCase()] = municipios[k];
        }

        // Una sola pasada sobre todos los paths
        $('.pae-mapa-municipio').each(function() {
            var el         = this;
            var nombreApi  = el.dataset.municipio || '';
            var codigo     = String(el.dataset.codigo || '');
            var esSel      = soloUno && codigo === municipioSeleccionado;

            if (soloUno && !esSel) {
                // Municipio no seleccionado: gris opaco
                el.setAttribute('fill', '#EEF2F7');
                el.setAttribute('stroke', '#ccc');
                el.setAttribute('stroke-width', '0.5');
            } else {
                var count = idx[nombreApi.toLowerCase()] || 0;
                el.setAttribute('fill', getColor(count));
                el.setAttribute('stroke', esSel ? '#000' : '#94a3b8');
                el.setAttribute('stroke-width', esSel ? '3' : '0.5');
            }
        });

        if ($('#badgeMunicipios').length) {
            $('#badgeMunicipios').text(totalMunicipios + ' municipio' + (totalMunicipios !== 1 ? 's' : '') + ' con ' + totalSedes + ' sedes');
        }
    },

    /**
     * Carga los datos de un municipio específico
     * @param {string} codigoMunicipio Código DANE del municipio (ej: "68745")
     * @param {string} nombreMostrar   Nombre para mostrar en el modal
     */
    cargarDatosMunicipio: function(codigoMunicipio, nombreMostrar) {
        var ano = $('#filtro_ano').val() || 'todos';

        // Mostrar modal con loading
        $('#modalMunicipioTitle').html('<i class="feather icon-map-pin"></i> ' + nombreMostrar);
        $('#modalMunicipioBody').html(`
            <div class="text-center">
                <div class="spinner-border text-primary" role="status">
                    <span class="sr-only">Cargando...</span>
                </div>
                <p class="mt-2">Cargando datos de ${nombreMostrar}...</p>
            </div>
        `);
        $('#modalMunicipio').modal('show');

        $.ajax({
            url: 'admin/ajax/rqst.php',
            type: 'POST',
            dataType: 'json',
            data: {
                op: 'getDataArcgisPae',
                codigoMunicipio: codigoMunicipio,
                ano: ano
            },
            success: function(response) {
                console.log('[PAE ArcGIS] Datos del municipio:', response);
                if (response.output && response.output.valid) {
                    PAE_DASHBOARD.renderDatosMunicipio(response.output);
                } else {
                    $('#modalMunicipioBody').html(`
                        <div class="alert alert-danger">
                            <i class="feather icon-alert-circle"></i> 
                            Error al cargar datos: ${response.output.error || 'Sin datos disponibles'}
                        </div>
                    `);
                }
            },
            error: function(xhr, status, error) {
                console.error('[PAE ArcGIS] Error AJAX:', xhr.responseText);
                $('#modalMunicipioBody').html(`
                    <div class="alert alert-danger">
                        <i class="feather icon-alert-circle"></i> 
                        Error de conexión: ${error}<br>
                        <small>${xhr.responseText}</small>
                    </div>
                `);
            }
        });
    },

    /**
     * Renderiza los datos del municipio en el modal
     */
    renderDatosMunicipio: function(data) {
        var html = `
            <div class="row">
                <div class="col-md-6">
                    <div class="card bg-light mb-3">
                        <div class="card-header bg-primary text-white">
                            <h6 class="mb-0"><i class="feather icon-home"></i> Infraestructura</h6>
                        </div>
                        <div class="card-body">
                            <table class="table table-sm table-bordered">
                                <tr><td><strong>Total Sedes</strong></td><td>${data.caracterizaciones || 0}</td></tr>
                                <tr><td><strong>Zona Rural</strong></td><td>${data.zona_rural || 0}</td></tr>
                                <tr><td><strong>Zona Urbana</strong></td><td>${data.zona_urbana || 0}</td></tr>
                                <tr><td><strong>Niños PAE</strong></td><td>${data.ninos_foc || 0}</td></tr>
                            </table>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card bg-light mb-3">
                        <div class="card-header bg-success text-white">
                            <h6 class="mb-0"><i class="feather icon-box"></i> Equipamiento</h6>
                        </div>
                        <div class="card-body">
                            <table class="table table-sm table-bordered">
                                <tr><td><strong>Neveras</strong></td><td>${data.neveras_fun || 0} / ${data.neveras || 0}</td></tr>
                                <tr><td><strong>Congeladores</strong></td><td>${data.congeladores_fun || 0} / ${data.congeladores || 0}</td></tr>
                                <tr><td><strong>Estufas</strong></td><td>${data.quemadores_estufas_buenos || 0} / ${data.quemadores_estufas || 0}</td></tr>
                                <tr><td><strong>Licuadoras</strong></td><td>${data.licuadoras_industriales || 0}</td></tr>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-6">
                    <div class="card bg-light mb-3">
                        <div class="card-header bg-info text-white">
                            <h6 class="mb-0"><i class="feather icon-droplet"></i> Servicios Públicos</h6>
                        </div>
                        <div class="card-body">
                            <table class="table table-sm table-bordered">
                                <tr><td><strong>Agua</strong></td><td>${data.acceso_agua_si || 0} (Sí) / ${data.acceso_agua_no || 0} (No)</td></tr>
                                <tr><td><strong>Electricidad</strong></td><td>${data.acceso_electricidad_si || 0} (Sí) / ${data.acceso_electricidad_no || 0} (No)</td></tr>
                                <tr><td><strong>Alcantarillado</strong></td><td>${data.acceso_alcantarillado_si || 0} (Sí) / ${data.acceso_alcantarillado_no || 0} (No)</td></tr>
                            </table>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card bg-light mb-3">
                        <div class="card-header bg-warning text-dark">
                            <h6 class="mb-0"><i class="feather icon-book"></i> Modalidades PAE</h6>
                        </div>
                        <div class="card-body">
                            <table class="table table-sm table-bordered">
                                <tr><td><strong>Almuerzo Preparado</strong></td><td>${data.almuerzo_preparado_sitio_si || 0}</td></tr>
                                <tr><td><strong>Almuerzo Transportado</strong></td><td>${data.almuerzo_trasportado_si || 0}</td></tr>
                                <tr><td><strong>Complemento Preparado</strong></td><td>${data.complemento_preparado_sitio_si || 0}</td></tr>
                                <tr><td><strong>Complemento Industrializado</strong></td><td>${data.complemento_industrializado_si || 0}</td></tr>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        `;
        
        $('#modalMunicipioBody').html(html);
    }
};

// Objeto PAE_GRAFICAS (placeholder si se necesita)
const PAE_GRAFICAS = {
    chartMenaje: null,

    iniciar: function() {
        console.log('[PAE ArcGIS] Gráficas inicializadas');
    }
};

// Inicializar cuando el DOM esté listo
$(function () {
    console.log('[PAE ArcGIS Dashboard] JavaScript cargado correctamente');

    // Inicializar PAE_GRAFICAS
    if (typeof PAE_GRAFICAS !== 'undefined' && PAE_GRAFICAS.iniciar) {
        PAE_GRAFICAS.iniciar();
    }

    // Cargar el mapa de municipios cuando esté disponible
    if (typeof PAE_DASHBOARD !== 'undefined' && PAE_DASHBOARD.cargarMapaMunicipios) {
        setTimeout(function() {
            PAE_DASHBOARD.cargarMapaMunicipios();
        }, 1500);
    }

    // Evento click en municipios del mapa
    $(document).on('click', '.pae-mapa-municipio', function() {
        var codigoClick    = String($(this).data('codigo'));
        var nombreMostrar  = $(this).data('municipio');
        var municipioSel   = $('#tbl_municipio_id').val();

        // Si hay un municipio seleccionado (distinto de "todos"), solo permitir
        // el click sobre ese municipio
        if (municipioSel && municipioSel !== 'todos' && String(municipioSel) !== codigoClick) {
            return; // ignorar click en otro municipio
        }

        console.log('[PAE] Click en municipio:', nombreMostrar, 'código:', codigoClick);
        if (codigoClick) {
            PAE_DASHBOARD.cargarDatosMunicipio(codigoClick, nombreMostrar);
        }
    });

    // Cierre del modal (compatibilidad con doble carga de Bootstrap)
    $(document).on('click', '.btn-cerrar-modal', function() {
        $('#modalMunicipio').modal('hide');
    });

    // Recargar mapa cuando cambie el select de municipio
    $('#tbl_municipio_id').on('change', function() {
        console.log('[PAE] Cambio en select municipio, recargando mapa...');
        setTimeout(function() {
            PAE_DASHBOARD.cargarMapaMunicipios();
        }, 500);
    });
});
