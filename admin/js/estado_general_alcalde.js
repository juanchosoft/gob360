/**
 * estado_general_alcalde.js
 * Script para manejar los gráficos del Dashboard del Alcalde
 * Adaptado para trabajar con veredas en lugar de provincias
 */
var ESTADO_GENERAL_ALCALDE = {
    /**
     * Inicializa todos los gráficos del dashboard
     */
    mostrarGraficas: function () {
        console.log('🚀 Iniciando carga de gráficos del Alcalde...');
        console.log('📊 Contenedor Veredas existe:', document.querySelector('#containerVeredas') !== null);
        console.log('📊 Contenedor Municipios existe:', document.querySelector('#containerMunicipios') !== null);

        ESTADO_GENERAL_ALCALDE.getTotalVisitasPorVereda();
        ESTADO_GENERAL_ALCALDE.getTotalVisitasPorMesDelDepartamento();
    },

    /**
     * Función genérica para hacer peticiones AJAX
     */
    fetchDataComparativa: function (operation, container, title) {
        const q = { op: operation };

        // Obtener el código de municipio si es usuario municipal
        const isUsuarioMunicipal = $('#isUsuarioMunicipal').val() === '1';
        const municipioUsuario = $('#municipioUsuario').val();

        if (isUsuarioMunicipal && municipioUsuario) {
            q.codigo_municipio = municipioUsuario;
        }

        console.log('📡 Petición AJAX:', operation, 'para contenedor:', container, 'filtro municipio:', isUsuarioMunicipal ? municipioUsuario : 'todos');

        if (typeof UTIL !== 'undefined' && UTIL.cursorBusy) {
            UTIL.cursorBusy();
        }

        return $.ajax({
            data: q,
            type: "GET",
            dataType: "json",
            url: "admin/ajax/rqst.php"
        }).always(() => {
            if (typeof UTIL !== 'undefined' && UTIL.cursorNormal) {
                UTIL.cursorNormal();
            }
        }).then((data) => {
            console.log('✅ Respuesta recibida para', operation, ':', data);
            if (data.output && data.output.valid) {
                return data;
            } else {
                console.error('❌ Error en respuesta:', data);
                if (typeof swal !== 'undefined') {
                    swal("warning", "Error al cargar datos: " + operation, "error");
                }
                return null;
            }
        }).catch((error) => {
            console.error('❌ Error en AJAX:', error);
            return null;
        });
    },

    /**
     * Crea un gráfico comparativo de barras con ApexCharts
     */
    createChartComparativa: function (container, title, jsonData, tipo) {
        // Procesar el JSON para extraer categorías y series (años)
        const response = jsonData.output.response;
        let categories;

        if (tipo === "vereda") {
            categories = Object.values(response)[0].map(item => item.vereda); // Veredas (categorías)
        } else if (tipo === "municipio") {
            categories = Object.values(response)[0].map(item => item.municipio); // Municipios (categorías)
        } else if (tipo === "mes") {
            categories = Object.values(response)[0].map(item => item.mes); // Meses (categorías)
        }

        const series = Object.keys(response).map(year => ({
            name: year, // Nombre del año
            data: response[year].map(item => item.total_visitas) // Datos de total_visitas
        }));

        // Configuración del gráfico
        const options = {
            series: series,
            chart: {
                type: 'bar',
                height: 350
            },
            plotOptions: {
                bar: {
                    horizontal: false,
                    columnWidth: '55%',
                    borderRadius: 5
                }
            },
            dataLabels: {
                enabled: false
            },
            colors: ['#ff5733', '#33ff57', '#33c1ff', '#bdc3c7', '#7f8c8d', '#2c3e50', '#f39c12', '#d35400', '#c0392b', '#ffeb3b', '#7e57c2'],
            stroke: {
                show: true,
                width: 2,
                colors: ['transparent'],
            },
            xaxis: {
                categories: categories // Categorías (Veredas o Meses)
            },
            yaxis: {
                title: {
                    text: title
                }
            },
            fill: {
                opacity: 1
            },
            tooltip: {
                y: {
                    formatter: function (val) {
                        return val + " visitas";
                    }
                }
            }
        };

        // Renderizar el gráfico
        const chart = new ApexCharts(document.querySelector(`#${container}`), options);
        chart.render();
    },

    /**
     * Obtiene y muestra el gráfico de visitas por vereda (Alcalde) o por municipio (SuperAdmin)
     */
    getTotalVisitasPorVereda: function () {
        const isUsuarioMunicipal = $('#isUsuarioMunicipal').val() === '1';

        if (isUsuarioMunicipal) {
            // ALCALDE: Mostrar veredas de su municipio
            console.log('📊 Cargando gráfico de visitas por vereda (Alcalde)...');
            this.fetchDataComparativa("getTotalVisitasPorVeredasAlcalde", "containerVeredas", "Visitas realizadas a Veredas")
                .then((data) => {
                    if (data) {
                        console.log('🎨 Renderizando gráfico de veredas');
                        this.createChartComparativa("containerVeredas", "Visitas realizadas a Veredas", data, "vereda");
                    } else {
                        console.error('❌ No hay datos para el gráfico de veredas');
                    }
                });
        } else {
            // SUPERADMIN: Mostrar municipios de todo el departamento
            console.log('📊 Cargando gráfico de visitas por municipio (SuperAdmin)...');
            this.fetchDataComparativa("getTotalVisitasPorMunicipiosAlcalde", "containerVeredas", "Visitas realizadas a Municipios")
                .then((data) => {
                    if (data) {
                        console.log('🎨 Renderizando gráfico de municipios');
                        this.createChartComparativa("containerVeredas", "Visitas realizadas a Municipios", data, "municipio");
                    } else {
                        console.error('❌ No hay datos para el gráfico de municipios');
                    }
                });
        }
    },

    /**
     * Obtiene y muestra el gráfico de visitas por mes a municipios
     */
    getTotalVisitasPorMesDelDepartamento: function () {
        console.log('📊 Cargando gráfico de visitas por mes...');
        this.fetchDataComparativa("getSoloVisitasPorMesAlcalde", "containerMunicipios", "Visitas por Mes a Municipios")
            .then((data) => {
                if (data) {
                    console.log('🎨 Renderizando gráfico de meses');
                    this.createChartComparativa("containerMunicipios", "Visitas por Mes a Municipios", data, "mes");
                } else {
                    console.error('❌ No hay datos para el gráfico de meses');
                }
            });
    }
};

/**
 * Inicializar gráficos cuando el documento esté listo
 */
$(document).ready(function () {
    ESTADO_GENERAL_ALCALDE.mostrarGraficas();
});
