/**
 * JavaScript para secretaria_alcalde_proyectos.php
 * Maneja la visualización de proyectos por secretaría de alcaldías
 *
 * @author SPIDERSOFTWARE
 * @version 1.0
 */

$(document).ready(function() {
    console.log('secretaria_alcalde_proyectos.js cargado');
    console.log('Proyectos por secretaría:', proyectosPorSecretaria);
    console.log('Es usuario municipal:', isUsuarioMunicipal);
    console.log('Municipio usuario:', municipioUsuario);

    // Inicializar select2 si está disponible
    if ($.fn.select2) {
        $('#selectSecretaria').select2({
            placeholder: 'Seleccione una secretaría',
            allowClear: true
        });

        $('#selectProvincia').select2({
            placeholder: 'Seleccione una provincia',
            allowClear: true
        });

        $('#selectVereda').select2({
            placeholder: 'Seleccione una vereda',
            allowClear: true
        });
    }

    // Cargar provincias únicas desde los proyectos (solo para admin)
    if (!isUsuarioMunicipal) {
        cargarProvincias();
    }

    // Event listener para selección de secretaría
    $('#selectSecretaria').on('change', function() {
        const secretariaId = $(this).val();
        console.log('Secretaría seleccionada:', secretariaId);

        if (secretariaId) {
            cargarIndicadoresSecretaria(secretariaId);
        } else {
            mostrarMensajeSeleccionarSecretaria();
        }
    });

    // Event listener para selección de provincia (admin)
    $('#selectProvincia').on('change', function() {
        const secretariaId = $('#selectSecretaria').val();
        console.log('Provincia cambiada, secretaría actual:', secretariaId);

        if (secretariaId) {
            cargarIndicadoresSecretaria(secretariaId);
        }
    });

    // Event listener para selección de vereda (alcalde)
    $('#selectVereda').on('change', function() {
        const secretariaId = $('#selectSecretaria').val();
        console.log('Vereda cambiada, secretaría actual:', secretariaId);

        if (secretariaId) {
            cargarIndicadoresSecretaria(secretariaId);
        }
    });
});

/**
 * Carga los indicadores de una secretaría específica
 *
 * @param {number} secretariaId ID de la secretaría
 */
function cargarIndicadoresSecretaria(secretariaId) {
    console.log('=== cargarIndicadoresSecretaria ===');
    console.log('Secretaría ID:', secretariaId);

    const provincia = $('#selectProvincia').val() || '';
    const veredaId = $('#selectVereda').val() || '';
    console.log('Provincia:', provincia);
    console.log('Vereda ID:', veredaId);
    console.log('Es usuario municipal:', isUsuarioMunicipal);
    console.log('Municipio usuario:', municipioUsuario);

    // Mostrar loading
    $('#indicadoresSecretaria').html(`
        <div class="text-center py-3">
            <div class="spinner-border text-primary" role="status">
                <span class="sr-only">Cargando...</span>
            </div>
            <p class="mt-2">Cargando indicadores...</p>
        </div>
    `);

    // Preparar datos para la petición
    const ajaxData = {
        op: 'proyectosalcaldias_getall',
        tbl_secretarias_id: secretariaId,
        provincia: provincia
    };

    // Solo agregar tbl_municipio_id si es usuario municipal
    if (isUsuarioMunicipal && municipioUsuario) {
        ajaxData.tbl_municipio_id = municipioUsuario;

        // Agregar filtro de vereda si está seleccionada
        if (veredaId) {
            ajaxData.tbl_vereda_id = veredaId;
        }
    }

    console.log('Datos AJAX enviados:', ajaxData);

    // Realizar petición AJAX para obtener los proyectos de la secretaría
    $.ajax({
        url: 'admin/ajax/rqst.php',
        type: 'POST',
        dataType: 'json',
        data: ajaxData,
        success: function(response) {
            console.log('=== Respuesta AJAX recibida ===');
            console.log('Respuesta completa:', response);
            console.log('response.output:', response.output);
            console.log('response.output.valid:', response.output ? response.output.valid : 'undefined');
            console.log('response.output.response:', response.output ? response.output.response : 'undefined');

            if (response.output && response.output.valid) {
                if (response.output.response && response.output.response.length > 0) {
                    console.log('Proyectos encontrados:', response.output.response.length);
                    console.log('Llamando a mostrarIndicadoresSecretaria...');
                    mostrarIndicadoresSecretaria(response.output.response, secretariaId);
                } else {
                    console.log('Array de proyectos vacío');
                    $('#indicadoresSecretaria').html(`
                        <div class="card">
                            <div class="card-body text-center text-muted py-4">
                                <i class="feather icon-inbox" style="font-size: 36px;"></i>
                                <p class="mt-2 mb-0">No hay proyectos para esta secretaría</p>
                            </div>
                        </div>
                    `);
                }
            } else {
                console.error('Respuesta inválida:', response);
                $('#indicadoresSecretaria').html(`
                    <div class="card">
                        <div class="card-body text-center text-warning py-4">
                            <i class="feather icon-alert-circle" style="font-size: 36px;"></i>
                            <p class="mt-2 mb-0">No se pudieron cargar los indicadores</p>
                            <small class="text-muted">Respuesta inválida del servidor</small>
                        </div>
                    </div>
                `);
            }
        },
        error: function(xhr, status, error) {
            console.error('=== Error AJAX ===');
            console.error('Error:', error);
            console.error('XHR:', xhr);
            console.error('Status:', status);
            console.error('Response Text:', xhr.responseText);

            $('#indicadoresSecretaria').html(`
                <div class="card">
                    <div class="card-body text-center text-danger py-4">
                        <i class="feather icon-alert-triangle" style="font-size: 36px;"></i>
                        <p class="mt-2 mb-0">Error al cargar los indicadores</p>
                        <small class="text-muted">${error}</small>
                    </div>
                </div>
            `);
        }
    });
}

/**
 * Muestra los indicadores de proyectos de una secretaría
 *
 * @param {Array} proyectos Array de proyectos
 * @param {number} secretariaId ID de la secretaría
 */
function mostrarIndicadoresSecretaria(proyectos, secretariaId) {
    console.log('=== mostrarIndicadoresSecretaria ===');
    console.log('Total proyectos recibidos:', proyectos.length);
    console.log('Secretaría ID:', secretariaId);
    console.log('Proyectos:', proyectos);

    // Calcular estadísticas
    const totalProyectos = proyectos.length;
    let valorTotal = 0;
    let estadisticas = {
        'En Formulación': 0,
        'En Ejecución': 0,
        'Terminado': 0,
        'Entregado': 0
    };

    proyectos.forEach(function(proyecto) {
        valorTotal += parseFloat(proyecto.valor_proyecto || 0);

        const estado = proyecto.estado || 'Sin estado';
        if (estado in estadisticas) {
            estadisticas[estado]++;
        } else {
            // Intentar clasificar por nombre similar
            if (estado.toLowerCase().indexOf('formulaci') !== -1) {
                estadisticas['En Formulación']++;
            } else if (estado.toLowerCase().indexOf('ejecuci') !== -1 || estado.toLowerCase().indexOf('trámite') !== -1) {
                estadisticas['En Ejecución']++;
            } else if (estado.toLowerCase().indexOf('terminado') !== -1 || estado.toLowerCase().indexOf('entregado') !== -1) {
                estadisticas['Terminado']++;
            }
        }
    });

    console.log('Estadísticas calculadas:', estadisticas);
    console.log('Valor total:', valorTotal);

    // Construir HTML de indicadores - diseño para sidebar
    let html = `
        <div class="card bg-primary text-white shadow-sm mb-3">
            <div class="card-body text-center p-4">
                <h1 class="display-4 font-weight-bold mb-2">${totalProyectos}</h1>
                <h6 class="mb-0">TOTAL PROYECTOS</h6>
                <small>Todas las Provincias</small>
            </div>
        </div>

        <div class="card bg-success text-white shadow-sm mb-3">
            <div class="card-body text-center p-4">
                <h1 class="display-4 font-weight-bold mb-2">${estadisticas['Terminado'] + estadisticas['Entregado']}</h1>
                <h6 class="mb-0">Terminados (${calcularPorcentaje(estadisticas['Terminado'] + estadisticas['Entregado'], totalProyectos)}%)</h6>
            </div>
        </div>

        <div class="card bg-warning text-white shadow-sm mb-3">
            <div class="card-body text-center p-4">
                <h1 class="display-4 font-weight-bold mb-2">${estadisticas['En Ejecución']}</h1>
                <h6 class="mb-0">En ejecución (${calcularPorcentaje(estadisticas['En Ejecución'], totalProyectos)}%)</h6>
            </div>
        </div>

        <div class="card bg-info text-white shadow-sm mb-3">
            <div class="card-body text-center p-4">
                <h1 class="display-4 font-weight-bold mb-2">${estadisticas['En Formulación']}</h1>
                <h6 class="mb-0">En contratación (${calcularPorcentaje(estadisticas['En Formulación'], totalProyectos)}%)</h6>
            </div>
        </div>

        <div class="card bg-secondary text-white shadow-sm mb-3">
            <div class="card-body text-center p-4">
                <h1 class="display-4 font-weight-bold mb-2">0</h1>
                <h6 class="mb-0">En formulación (27.3%)</h6>
            </div>
        </div>

        <div class="card bg-success text-white shadow-sm">
            <div class="card-body text-center p-4">
                <h2 class="font-weight-bold mb-2">$${formatearNumero(valorTotal)}</h2>
                <h6 class="mb-0">Inversión Total</h6>
            </div>
        </div>
    `;

    console.log('HTML generado (primeros 200 caracteres):', html.substring(0, 200));
    console.log('Elemento #indicadoresSecretaria encontrado:', $('#indicadoresSecretaria').length);

    $('#indicadoresSecretaria').html(html);

    console.log('HTML inyectado correctamente en #indicadoresSecretaria');
    console.log('Contenido después de inyectar:', $('#indicadoresSecretaria').html().substring(0, 200));
}

/**
 * Muestra el mensaje inicial de seleccionar secretaría
 */
function mostrarMensajeSeleccionarSecretaria() {
    $('#indicadoresSecretaria').html(`
        <div class="col-12 text-center text-muted py-5">
            <i class="feather icon-info" style="font-size: 48px;"></i>
            <p class="mt-3">Seleccione una secretaría para ver los indicadores</p>
        </div>
    `);
}

/**
 * Calcula el porcentaje
 *
 * @param {number} valor Valor parcial
 * @param {number} total Valor total
 * @returns {string} Porcentaje formateado
 */
function calcularPorcentaje(valor, total) {
    if (total === 0) return '0.0';
    return ((valor / total) * 100).toFixed(1);
}

/**
 * Formatea un número con separadores de miles
 *
 * @param {number} numero Número a formatear
 * @returns {string} Número formateado
 */
function formatearNumero(numero) {
    return new Intl.NumberFormat('es-CO', {
        minimumFractionDigits: 0,
        maximumFractionDigits: 0
    }).format(numero);
}

/**
 * Carga las provincias disponibles desde los municipios con proyectos
 */
function cargarProvincias() {
    $.ajax({
        url: 'admin/ajax/rqst.php',
        type: 'POST',
        dataType: 'json',
        data: {
            op: 'proyectosalcaldias_provincias',
            codigo_municipio: isUsuarioMunicipal ? municipioUsuario : null
        },
        success: function(response) {
            if (response.output && response.output.valid) {
                const provincias = response.output.response;
                const selectProvincia = $('#selectProvincia');

                // Limpiar opciones existentes excepto "Todas"
                selectProvincia.find('option:not(:first)').remove();

                // Agregar las provincias
                provincias.forEach(function(item) {
                    if (item.provincia) {
                        selectProvincia.append(`<option value="${item.provincia}">${item.provincia}</option>`);
                    }
                });
            }
        },
        error: function(xhr, status, error) {
            console.error('Error al cargar provincias:', error);
        }
    });
}
