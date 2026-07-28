/**
 * Script para cargar municipios disponibles en ArcGIS
 * y filtrar datos del dashboard dinámicamente
 */

var PAE_ARCGIS_MUNICIPIOS = {
    // Configuración
    selectMunicipio: null,
    municipiosDisponibles: [],
    municipioActual: null,

    /**
     * Inicializa el módulo
     */
    init: function() {
        console.log('[PAE ArcGIS] Inicializando carga de municipios...');

        this.selectMunicipio = document.getElementById('tbl_municipio_id');

        if (!this.selectMunicipio) {
            console.error('[PAE ArcGIS] No se encontró el select de municipios');
            return;
        }

        // Obtener municipio actual de la URL
        this.municipioActual = this.getUrlParameter('mun') || 'todos';

        // Cargar municipios disponibles
        this.cargarMunicipios();
    },

    /**
     * Carga los municipios disponibles desde el servidor
     */
    cargarMunicipios: function() {
        var self = this;

        $.ajax({
            url: 'admin/ajax/rqst.php',
            type: 'POST',
            data: { op: 'getMunicipiosArcgis' },
            dataType: 'json',
            beforeSend: function() {
                self.selectMunicipio.innerHTML = '<option value="">Cargando municipios...</option>';
                self.selectMunicipio.disabled = true;
            },
            success: function(response) {
                console.log('[PAE ArcGIS] Respuesta del servidor:', response);

                if (response.output && response.output.valid) {
                    self.municipiosDisponibles = response.output.municipios;
                    self.renderizarMunicipios();

                    console.log('[PAE ArcGIS] ✅ Municipios cargados:', response.output.total);
                    console.log('[PAE ArcGIS] Total en ArcGIS:', response.output.total_arcgis);
                    console.log('[PAE ArcGIS] Total en BD Local:', response.output.total_local);

                    if (response.output.municipios_no_disponibles && response.output.municipios_no_disponibles.length > 0) {
                        console.warn('[PAE ArcGIS] ⚠️ Municipios NO disponibles en ArcGIS:',
                            response.output.municipios_no_disponibles.map(m => m.nombre).join(', '));
                    }
                } else {
                    console.error('[PAE ArcGIS] Error al cargar municipios:', response.output ? response.output.error : 'Error desconocido');
                    self.mostrarError('No se pudieron cargar los municipios disponibles');
                }
            },
            error: function(xhr, status, error) {
                console.error('[PAE ArcGIS] Error AJAX:', error);
                self.mostrarError('Error de conexión al cargar municipios');
            },
            complete: function() {
                self.selectMunicipio.disabled = false;
            }
        });
    },

    /**
     * Renderiza las opciones del select con los municipios disponibles
     */
    renderizarMunicipios: function() {
        var html = '<option value="todos">TODOS LOS MUNICIPIOS</option>';

        this.municipiosDisponibles.forEach(function(municipio) {
            var selected = municipio.codigo === this.municipioActual ? 'selected' : '';
            html += '<option value="' + municipio.codigo + '" ' + selected + '>' +
                    municipio.nombre + '</option>';
        }, this);

        this.selectMunicipio.innerHTML = html;

        // Re-inicializar select2 si está presente
        if (typeof $.fn.select2 !== 'undefined') {
            $(this.selectMunicipio).select2({
                placeholder: 'Seleccione un municipio',
                allowClear: false,
                width: '100%'
            });
        }
    },

    /**
     * Muestra un error en el select
     */
    mostrarError: function(mensaje) {
        this.selectMunicipio.innerHTML = '<option value="">⚠️ ' + mensaje + '</option>';

        // Mostrar alerta al usuario
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: mensaje,
                confirmButtonText: 'Entendido'
            });
        } else {
            alert(mensaje);
        }
    },

    /**
     * Obtiene un parámetro de la URL
     */
    getUrlParameter: function(name) {
        name = name.replace(/[\[]/, '\\[').replace(/[\]]/, '\\]');
        var regex = new RegExp('[\\?&]' + name + '=([^&#]*)');
        var results = regex.exec(location.search);
        return results === null ? '' : decodeURIComponent(results[1].replace(/\+/g, ' '));
    },

    /**
     * Navega a un municipio específico
     */
    irAMunicipio: function(codigoMunicipio) {
        var url = 'pae_arcgis_dash.php?mun=' + codigoMunicipio;
        console.log('[PAE ArcGIS] Navegando a:', url);
        window.location.href = url;
    }
};

// Inicializar cuando el DOM esté listo
$(document).ready(function() {
    PAE_ARCGIS_MUNICIPIOS.init();
});

// También inicializar cuando DEPARTAMENTO esté listo (por si se usa)
if (typeof DEPARTAMENTO !== 'undefined') {
    var originalGetMunicipios = DEPARTAMENTO.getMunicipios;
    DEPARTAMENTO.getMunicipios = function() {
        // Llamar al original si existe
        if (typeof originalGetMunicipios === 'function') {
            originalGetMunicipios.call(this);
        }
        // Recargar municipios de ArcGIS
        PAE_ARCGIS_MUNICIPIOS.cargarMunicipios();
    };
}
