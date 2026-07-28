// const API_URL = "./admin/classes/get_chart_data.php"; 


// $(function () {
//     if (typeof Highcharts === 'undefined') {
//         console.log("Script cargado.");
//         $('#graficoCategoria').html('<h3 class="text-danger">ERROR: Highcharts no está cargado.</h3>');
//         console.error("Highcharts library is missing!");
//         return;
//     }


//     // FUNCIÓN EXCLUSIVA PARA EL GRÁFICO PRINCIPAL (Columna/Barra)
//     function refreshMainChart() {
//         const categoria = $("#categoriaSelect").val();
//         const anio1 = $("#anioSelect1").val(); 
//         const anio2 = $("#anioSelect2").val(); 
        

//         if (categoria === 'hurtos'|| categoria === 'amenazas') {
//             $("#divAnioTorta").show(); // mostrar si es hurtos
//         } else {
//             $("#divAnioTorta").hide(); // ocultar para otras categorías
//             $("#graficoDonaHurto").empty(); // limpiar el contenedor del gráfico de torta
//         }


//         cargarGrafico(categoria, anio1, anio2);
        
//         refreshDonutChart(); 
//     }

//     // FUNCIÓN EXCLUSIVA PARA EL GRÁFICO DE TORTA
//     function refreshDonutChart() {
//         const categoria = $("#categoriaSelect").val();
//         const anio3 = $("#anioSelect3").val(); 

//         if (categoria === 'hurtos') {
//             cargarDona('hurto_dona', anio3);
//         }else if (categoria === 'amenazas') {    
//             cargarDona('amenaza_dona', anio3); 
//         }     
        
//         else {
//             // Limpiar la dona si la categoría no es 'hurtos'
//             $("#graficoDonaHurto").empty();
//         }
//     }


//     refreshMainChart(); 
    

//     $("#categoriaSelect").on("change", refreshMainChart);
//     $("#anioSelect1").on("change", refreshMainChart); 
//     $("#anioSelect2").on("change", refreshMainChart); 
    
//     // selector solo actualiza el grafico de torta.
//     $("#anioSelect3").on("change", refreshDonutChart); 
// });


// // ----------------------------------------------------------------------
// // FUNCIÓN DE CARGA DEL GRÁFICO PRINCIPAL (Columna/Barra)
// // ----------------------------------------------------------------------
// function cargarGrafico(categoria, anio1, anio2) {
//     $("#loader").show();
//     $('#graficoCategoria').css('height', '250px').empty(); 

//     fetch(`${API_URL}?categoria=${categoria}&anio1=${anio1}&anio2=${anio2}`)
//         .then(response => {
//             if (!response.ok) throw new Error(`HTTP error! status: ${response.status}`);
//             return response.json();
//         })
//         .then(result => {
//             $("#loader").hide();
            
//             if (!result.valid) {
//                  $('#graficoCategoria').html(`<h4 class="text-center mt-4">🚨 Error: ${result.title}</h4>`);
//                  return;
//             }

//             const contenedorId = 'graficoCategoria';
//             const chartType = result.chart_type || 'column';

//             const dataToPass = chartType === 'pie' ? result.data : result.dataSeries; 
            
//             // Pintar el gráfico principal (columnas o pie)
//             pintarHighchart(contenedorId, {...result, data: dataToPass});

//         })
//         .catch(error => {
//             $("#loader").hide();
//             console.error("Error general:", error);
//             $('#graficoCategoria').html('<h4 class="text-center mt-4">❌ Error de carga de datos.</h4>');
//         });
// }


// // ----------------------------------------------------------------------
// // NUEVA FUNCIÓN DE CARGA PARA EL GRÁFICO DE TORTA (Dona)
// // ----------------------------------------------------------------------
// function cargarDona(categoriaDona,anio) {
//     $("#loader").show();
//     $("#graficoDonaHurto").empty(); // Limpiar solo la dona

//     fetch(`${API_URL}?categoria=${categoriaDona}&anio=${anio}`)
//         .then(r => r.json())
//         .then(donaResult => {
//             $("#loader").hide(); // Ocultar el loader al finalizar la carga


//             // Título base para el gráfico de torta
//             let titleBase = '';
//             if (categoriaDona === 'hurto_dona') {
//                 titleBase = 'Distribución de Hurto por Tipo';
//                 chartTypeOverride = 'bar';
//             } else if (categoriaDona === 'amenaza_dona') {
//                 titleBase = 'Distribución de Amenazas por Medio';
//             } else {
//                 titleBase = 'Gráfico de Torta';
//             }
            
//             const chartTitle = `${titleBase} (${anio})`; 


//             donaResult.title = chartTitle;
//             donaResult.chart_type = chartTypeOverride; 

//             if (donaResult.data && donaResult.data.length > 0) {
//                 pintarHighchart('graficoDonaHurto', donaResult);
//             } else {
//                 $('#graficoDonaHurto').html('<h4 class="text-center mt-4">Sin datos de ' + chartTitle + '.</h4>');

//             }
//         })
//         .catch(e => {
//             $("#loader").hide();
//             console.error("Error Dona:", e)
//             $('#graficoDonaHurto').html('<h4 class="text-center mt-4">❌ Error de carga de datos de Dona.</h4>');
//         });
// }

// function pintarHighchart(contenedorId, result) {
//     const dataIsSeries = result.chart_type !== 'pie' && Array.isArray(result.data) && result.data.length > 0 && typeof result.data[0] === 'object' && result.data[0].name;

//     if (!result.data || result.data.length === 0) {
//         $(`#${contenedorId}`).html(`<h4 class="text-center mt-4">Sin datos disponibles: ${result.title}</h4>`);
//         return;
//     }
    
//     // Configuracion para Highcharts
//     let seriesConfig = [];
//     let xAxisConfig = {};
//     let yAxisTitle = null;
//     let chartType = result.chart_type || 'column';

//     if (chartType === 'pie') {
//         seriesConfig = [{ name: 'Casos', colorByPoint: true, type: 'pie', data: result.data }];
//     }else if (chartType === 'bar' && contenedorId === 'graficoDonaHurto') {

//         // Lógica para el nuevo Gráfico de Barras Horizontal
        
//         // 1. Transformar datos de pie (formato [{name: 'X', y: V}, ...]) a categorías y valores
//         const categories = result.data.map(item => item.name);
//         const dataValues = result.data.map(item => item.y || item.value); 
        
//         seriesConfig = [{ 
//             name: 'Casos', 
//             data: dataValues, 
//             type: 'bar', // Tipo de serie: 'bar'
//             color: '#ff7a45'
//         }];
        
//         // 2. Configurar ejes para Bar Chart (Categorías en el eje X)
//         xAxisConfig = { 
//             categories: categories, 
//             title: { text: null } 
//         };
        
//         yAxisTitle = 'Cantidad de casos'; 


//     }else if (dataIsSeries) {
//         seriesConfig = result.data; 
//         xAxisConfig = { categories: result.categories, crosshair: true, title: { text: 'Mes' } };
//         yAxisTitle = 'Cantidad de casos';

//     }else {
//         seriesConfig = [{ name: 'Total Casos', data: result.data, type: 'column' }];
//         xAxisConfig = { categories: result.categories, crosshair: true, title: { text: 'Mes' } };
//         yAxisTitle = 'Cantidad de casos';


//     }

//     // PINTAR GRÁFICO
//     Highcharts.chart(contenedorId, {
//         chart: { 
//             type: chartType,
//             backgroundColor: '#ffffff',
//             borderRadius: 8
//         },
//         title: { 
//             text: result.title,
//             style: {
//                 color: '#2c3e50',
//                 fontSize: '16px',
//                 fontWeight: 'bold'
//             }
//         },
//         xAxis: xAxisConfig,
//         yAxis: { 
//             min: 0, 
//             title: { 
//                 text: yAxisTitle
//             },
//             allowDecimals: false

//         },

//         // COLORES GLOBALES PARA LAS SERIES Y LEYENDA
//         colors: ['#ff7a45', '#343a40'],

//         tooltip: {
//             backgroundColor: 'rgba(255, 255, 255, 0.95)',
//             borderColor: '#d1d5db',
//             borderRadius: 8,
//             borderWidth: 1,
//             shadow: true,
//             useHTML: true,
//             style: {
//                 color: '#374151',
//                 fontSize: '13px',
//                 fontFamily: 'Arial, sans-serif',
//                 padding: '10px',
//                 lineHeight: '1.4'
//             },
//         },
//         plotOptions: {
//             column: {
//                 borderWidth: 0,
//                 grouping: true
//             },

//             bar: {
//                  dataLabels: {
//                     enabled: true, 
//                     align: 'right',
//                     style: {
//                         fontWeight: 'bold'
//                     }
//                  },
//                  borderRadius: 5,
//                  pointPadding: 0.1,
//                  groupPadding: 0.1
//             }

//         },

//         series: seriesConfig,
//         credits: { enabled: false }
//     });
// }

const API_URL = "./admin/classes/get_chart_data.php"; 


$(function () {
    if (typeof Highcharts === 'undefined') {
        console.log("Script cargado.");
        $('#graficoCategoria').html('<h3 class="text-danger">ERROR: Highcharts no está cargado.</h3>');
        console.error("Highcharts library is missing!");
        return;
    }


    function refreshMainChart() {
        const categoria = $("#categoriaSelect").val();
        const anio1 = $("#anioSelect1").val(); 
        const anio2 = $("#anioSelect2").val(); 
        

        if (categoria === 'hurtos'|| categoria === 'amenazas') {
            $("#divAnioTorta").show(); // mostrar si es hurtos
        } else {
            $("#divAnioTorta").hide(); // ocultar para otras categorías
            $("#graficoDonaHurto").empty(); 
        }


        cargarGrafico(categoria, anio1, anio2);
        
        refreshDonutChart(); 
    }
 
    function refreshDonutChart() {
        const categoria = $("#categoriaSelect").val();
        const anio3 = $("#anioSelect3").val(); 
        const anio4 = $("#anioSelect4").val(); 
        

        if (categoria !== 'hurtos' && categoria !== 'amenazas') {
            $("#graficoDonaHurto").empty(); 
            return; 
        }

        // Lógica para Hurtos
        if (categoria === 'hurtos') {
            cargarDona('hurto_dona_comparativo', anio3, anio4); 
        
        // Lógica para Amenazas (siempre es barra)
        } else if (categoria === 'amenazas') { 
            if (anio4 && anio4 !== anio3) {
                // Comparativo
                cargarDona('amenaza_dona_comparativo', anio3, anio4);
            } else {
                // Un solo año
                cargarDona('amenaza_dona', anio3); 
            }
        }     
    }


    refreshMainChart(); 
    

    $("#categoriaSelect").on("change", refreshMainChart);
    $("#anioSelect1").on("change", refreshMainChart); 
    $("#anioSelect2").on("change", refreshMainChart); 

    $("#anioSelect3").on("change", refreshDonutChart); 
    $("#anioSelect4").on("change", refreshDonutChart); 
});


//carga el grafico principal
function cargarGrafico(categoria, anio1, anio2) {
    $("#loader").show();
    $('#graficoCategoria').css('height', '250px').empty(); 

    fetch(`${API_URL}?categoria=${categoria}&anio1=${anio1}&anio2=${anio2}`)
        .then(response => {
            if (!response.ok) throw new Error(`HTTP error! status: ${response.status}`);
            return response.json();
        })
        .then(result => {
            $("#loader").hide();
            
            if (!result.valid) {
                 $('#graficoCategoria').html(`<h4 class="text-center mt-4">🚨 Error: ${result.title}</h4>`);
                 return;
            }

            const contenedorId = 'graficoCategoria';
            const chartType = result.chart_type || 'column';

            const dataToPass = result.dataSeries && result.dataSeries.length > 0 ? result.dataSeries : result.data;

            if (!dataToPass || dataToPass.length === 0) { 
                $(`#${contenedorId}`).html(`<h4 class="text-center mt-4">Sin datos disponibles: ${result.title}</h4>`);
                return;
            }

            // Pinta acá el gráfico principal 
            pintarHighchart(contenedorId, {...result, data: dataToPass});

        })
        .catch(error => {
            $("#loader").hide();
            console.error("Error general:", error);
            $('#graficoCategoria').html('<h4 class="text-center mt-4">❌ Error de carga de datos.</h4>');
        });
}


//grafico de barra hurtos
function cargarDona(categoriaDona, anio1, anio2 = null) { 
    $("#loader").show();
    $("#graficoDonaHurto").empty(); 

    let apiUrl = `${API_URL}?categoria=${categoriaDona}&anio1=${anio1}`;

    let isComparison = false;

    if ((categoriaDona === 'hurto_dona_comparativo' || categoriaDona === 'amenaza_dona_comparativo') && anio2) {
        apiUrl += `&anio2=${anio2}`; 
        isComparison = true;
    }

    fetch(apiUrl)
        .then(r => r.json())
        .then(donaResult => {
            $("#loader").hide(); 


            let titleBase = '';
            let chartTypeOverride = 'pie'; 

            if (categoriaDona.includes('hurto_dona')) {
                titleBase = 'Distribución de Hurto por Tipo';
                chartTypeOverride = 'bar';
            } else if (categoriaDona.includes('amenaza_dona')) {
                titleBase = 'Distribución de Amenazas por Medio';
                chartTypeOverride = 'bar'; 

            } else {
                titleBase = 'Gráfico';
            }

            if (!donaResult.title) {
                let chartTitle = titleBase;
                if (isComparison) {
                    chartTitle = `Comparativa: ${titleBase} (${anio1} vs ${anio2})`; 

                } else {
                    chartTitle += ` (${anio1})`; 
                }
                donaResult.title = chartTitle;
            }
            
            if (!isComparison && !donaResult.chart_type) {
                donaResult.chart_type = chartTypeOverride; 
            }

            if (donaResult.valid === false) {
                $('#graficoDonaHurto').html('<h4 class="text-center mt-4">🚨 ' + (donaResult.title || 'Error al cargar los datos.') + '</h4>');
                return; 
            }

            let hasData;   
            if (donaResult.chart_type === 'bar' && donaResult.dataSeries && donaResult.dataSeries.length > 0) {

                hasData = true;
            } else if (donaResult.chart_type === 'pie' && donaResult.data && donaResult.data.length > 0) {
                hasData = true;
            } else {
                hasData = false;
            }

            if (hasData) {
                pintarHighchart('graficoDonaHurto', donaResult);
            } else {
                $('#graficoDonaHurto').html('<h4 class="text-center mt-4">' + donaResult.title + '</h4>');
            }


        })
        .catch(e => {
            $("#loader").hide();
            console.error("Error Dona:", e)
            $('#graficoDonaHurto').html('<h4 class="text-center mt-4">❌ Error de carga de datos de Dona.</h4>');
        });
}

function pintarHighchart(contenedorId, result) {
    let yAxisBarConfig = null; 

    const dataIsSeries = result.chart_type !== 'pie' && Array.isArray(result.data) && 
                        result.data.length > 0 && typeof result.data[0] === 'object' && 
                        'data' in result.data[0]; 

    const dataToPass = result.dataSeries && result.dataSeries.length > 0 ? result.dataSeries : result.data; 

    if (!dataToPass || dataToPass.length === 0) { 
        $(`#${contenedorId}`).html(`<h4 class="text-center mt-4">Sin datos disponibles: ${result.title}</h4>`);
        return;
    }
    
    // Configuracion para Highcharts
    let seriesConfig = [];
    let xAxisConfig = {};
    let yAxisTitle = null;
    let chartType = result.chart_type || 'column';


    if (contenedorId === 'graficoDonaHurto') {
        chartType = result.chart_type || 'bar'; 
    }


    if (chartType === 'pie') {
        seriesConfig = [{ name: 'Casos', colorByPoint: true, type: 'pie', data: result.data }];
    }else if (chartType === 'bar' && contenedorId === 'graficoDonaHurto') {
        

        if (dataToPass.length > 0 && result.categories && result.categories.length > 0) {
            
            if (dataToPass.length === 1) {

                seriesConfig = [{
                    name: dataToPass[0].name,
                    data: dataToPass[0].data,
                    type: 'bar', 
                    color: '#ff7a45'
                }];
            } else {
                // Caso Hurtos comparativo
                seriesConfig = dataToPass;
            }
            
            // 1. Eje Y: Categorías 
            yAxisBarConfig = {
                categories: result.categories,
                title: { text: null }
            };
            
            // 2. Eje X: Valores 
            xAxisConfig = { 
                min: 0, 
                title: { 
                    text: 'Cantidad de casos'
                },
                allowDecimals: false
            };
            
            yAxisTitle = null; 

            
        } else {
            
            const categories = dataToPass.map(item => item.name);
            const dataValues = dataToPass.map(item => item.y || item.value); 
            
            seriesConfig = [{ 
                name: 'Casos', 
                data: dataValues, 
                type: 'bar', 
                color: '#ff7a45'
            }];

            yAxisBarConfig = {
                categories: categories,
                title: { text: null }
            };
            
            xAxisConfig = {
                min: 0, 
                title: { 
                    text: 'Cantidad de casos'
                },
                allowDecimals: false
            };
            yAxisTitle = null;
        }
        


    }else if (dataIsSeries || (contenedorId === 'graficoCategoria' && result.chart_type === 'column' && result.categories && result.categories.length > 0)) {
        
        if (contenedorId === 'graficoCategoria' && result.categories && result.categories.length > 12) {
             chartType = 'bar';
             
             yAxisBarConfig = {
                 categories: result.categories, // Meses
                 title: { text: null }
             };
             

             xAxisConfig = { 
                 min: 0, 
                 title: { 
                     text: 'Cantidad de casos'
                 },
                 allowDecimals: false
             };
             seriesConfig = result.data; // en dataSeries

        } else {
             seriesConfig = result.data; 
             xAxisConfig = { categories: result.categories, crosshair: true, title: { text: 'Mes' } };
             yAxisTitle = 'Cantidad de casos';
             chartType = 'column'; 
        }
    
    }else { 
        seriesConfig = [{ name: 'Total Casos', data: result.data, type: 'column' }];
        xAxisConfig = { categories: result.categories, crosshair: true, title: { text: 'Mes' } };
        yAxisTitle = 'Cantidad de casos';
    }

    // PINTAR GRÁFICO
    Highcharts.chart(contenedorId, {
        chart: { 
            type: chartType,
            backgroundColor: 'transparent',
            borderRadius: 8
        },
        title: { 
            text: result.title,
            style: {
                color: 'rgba(255,255,255,.92)',
                fontSize: '16px',
                fontWeight: 'bold'
            }
        },
        xAxis: Object.assign(yAxisBarConfig ? yAxisBarConfig : xAxisConfig, {
            labels: { style: { color: 'rgba(255,255,255,.7)' } },
            title: { style: { color: 'rgba(255,255,255,.7)' } },
            gridLineColor: 'rgba(255,255,255,.08)'
        }),

        yAxis: Object.assign(yAxisBarConfig ? xAxisConfig : { 
            min: 0, 
            title: { 
                text: yAxisTitle
            },
            allowDecimals: false
        }, {
            labels: { style: { color: 'rgba(255,255,255,.7)' } },
            title: { style: { color: 'rgba(255,255,255,.7)' } },
            gridLineColor: 'rgba(255,255,255,.08)'
        }),

        colors: ['#ff7a45', '#5b8def', '#34d399', '#fbbf24', '#a78bfa', '#fb923c'], 

        tooltip: {
            backgroundColor: 'rgba(15,23,42,.92)',
            borderColor: 'rgba(255,255,255,.12)',
            borderRadius: 8,
            borderWidth: 1,
            shadow: true,
            useHTML: true,
            style: {
                color: 'rgba(255,255,255,.92)',
                fontSize: '13px',
                fontFamily: 'Arial, sans-serif',
                padding: '10px',
                lineHeight: '1.4'
            },
        },
        plotOptions: {
            column: {
                borderWidth: 0,
                grouping: true
            },

            bar: {
                 dataLabels: {
                    enabled: true, 
                    align: 'right',
                    style: {
                        fontWeight: 'bold'
                    }
                 },
                 borderRadius: 5,
                 pointPadding: 0.1,
                 groupPadding: 0.1,
            }

        },

        series: seriesConfig,
        credits: { enabled: false }
    });
}