// Data retrieved from https://gs.statcounter.com/browser-market-share#monthly-202201-202201-bar

// Create the chart
Highcharts.chart('container3', {
    chart: {
        type: 'column'
    },
    title: {
        align: 'left',
        text: 'Visitas por Mes a Municipios'
    },
    subtitle: {
        align: 'left',
        text: ' <a href="http://statcounter.com" target="_blank"></a>'
    },
    accessibility: {
        announceNewData: {
            enabled: true
        }
    },
    xAxis: {
        type: 'category'
    },
    yAxis: {
        title: {
            text: 'Total de Compromisos'
        }

    },
    legend: {
        enabled: false
    },
    plotOptions: {
        series: {
            borderWidth: 0,
            dataLabels: {
                enabled: true,
                format: '{point.y:.1f}'
            }
        }
    },

    tooltip: {
        headerFormat: '<span style="font-size:11px">{series.name}</span><br>',
        pointFormat: '<span style="color:{point.color}">{point.name}</span>: <b>{point.y:.2f}</b> Compromisos<br/>'
    },

    series: [
        {
            name: 'Mes',
            colorByPoint: true,
            data: [
                {
                    name: 'Enero',
                    y: 14,
                    drilldown: 'Enero'
                },
                {
                    name: 'Febrero',
                    y: 17,
                    drilldown: 'Febrero'
                },
                {
                    name: 'Marzo',
                    y: 5,
                    drilldown: 'Marzo'
                },
                {
                    name: 'Abril',
                    y: 9,
                    drilldown: 'Abril'
                },
                {
                    name: 'Mayo',
                    y: 7,
                    drilldown: 'Mayo'
                },
                {
                    name: 'Junio',
                    y: 7,
                    drilldown: 'Junio'
                },
                {
                    name: 'Julio',
                    y: 2,
                    drilldown: 'Julio'
                },
                {
                    name: 'Agosto',
                    y: 0,
                    drilldown: 'Agosto'
                },
                {
                    name: 'Septiembre',
                    y: 0,
                    drilldown: 'Septiembre'
                },
                {
                    name: 'Octubre',
                    y: 0,
                    drilldown: 'Octubre'
                },
                {
                    name: 'Noviembre',
                    y: 0,
                    drilldown: 'Noviembre'
                },
                {
                    name: 'Diciembre',
                    y: 0,
                    drilldown: 'Diciembre'
                }
               
              
            ]
        }
    ],
   
});


// Data retrieved from https://gs.statcounter.com/browser-market-share#monthly-202201-202201-bar

// Create the chart
Highcharts.chart('container2', {
    chart: {
        type: 'column'
    },
    title: {
        align: 'left',
        text: 'Visitas realizadas por mes a Provincias'
    },
    subtitle: {
        align: 'left',
        text: ' <a href="http://statcounter.com" target="_blank"></a>'
    },
    accessibility: {
        announceNewData: {
            enabled: true
        }
    },
    xAxis: {
        type: 'category'
    },
    yAxis: {
        title: {
            text: 'Total de Compromisos'
        }

    },
    legend: {
        enabled: false
    },
    plotOptions: {
        series: {
            borderWidth: 0,
            dataLabels: {
                enabled: true,
                format: '{point.y:.1f}'
            }
        }
    },

    tooltip: {
        headerFormat: '<span style="font-size:11px">{series.name}</span><br>',
        pointFormat: '<span style="color:{point.color}">{point.name}</span>: <b>{point.y:.2f}</b> Compromisos<br/>'
    },

    series: [
        {
            name: 'Provincias',
            colorByPoint: true,
            data: [
                {
                    name: 'Comunera',
                    y: 11,
                    drilldown: 'Comunera'
                },
                {
                    name: 'García Rovira',
                    y: 7,
                    drilldown: 'García Rovira'
                },
                {
                    name: 'Guanetá',
                    y: 13,
                    drilldown: 'Guanetá'
                },
                {
                    name: 'Metropolitana',
                    y: 19,
                    drilldown: 'Metropolitana'
                },
                {
                    name: 'Soto Norte',
                    y: 6,
                    drilldown: 'Soto Norte'
                },
                {
                    name: 'Vélez',
                    y: 18,
                    drilldown: 'Vélez'
                },
                {
                    name: 'Yariguíes',
                    y: 13,
                    drilldown: 'Yariguíes'
                }
              
            ]
        }
    ],
   
});


    // Data retrieved from https://gs.statcounter.com/browser-market-share#monthly-202201-202201-bar

// Create the chart
Highcharts.chart('container1', {
    chart: {
        type: 'column'
    },
    title: {
        align: 'left',
        text: 'Compromisos pactados por Secretaria en estado Sin Cumplir'
    },
    subtitle: {
        align: 'left',
        text: ' <a href="http://statcounter.com" target="_blank"></a>'
    },
    accessibility: {
        announceNewData: {
            enabled: true
        }
    },
    xAxis: {
        type: 'category'
    },
    yAxis: {
        title: {
            text: 'Total de Compromisos'
        }

    },
    legend: {
        enabled: false
    },
    plotOptions: {
        series: {
            borderWidth: 0,
            dataLabels: {
                enabled: true,
                format: '{point.y:.1f}'
            }
        }
    },

    tooltip: {
        headerFormat: '<span style="font-size:11px">{series.name}</span><br>',
        pointFormat: '<span style="color:{point.color}">{point.name}</span>: <b>{point.y:.2f}</b> Compromisos<br/>'
    },

    series: [
        {
            name: 'Secretarias',
            colorByPoint: true,
            data: [
                {
                    name: 'Ambiental',
                    y: 1,
                    drilldown: 'Ambiental'
                },
                {
                    name: 'CAS',
                    y: 2,
                    drilldown: 'CAS'
                },
                {
                    name: 'Competividad',
                    y: 3,
                    drilldown: 'Competividad'
                },
                {
                    name: 'Cultura y Turismo',
                    y: 11,
                    drilldown: 'Cultura y Turismo'
                },
                {
                    name: 'Desarrollo Social',
                    y: 9,
                    drilldown: 'Desarrollo Social'
                },
                {
                    name: 'Educación',
                    y: 26,
                    drilldown: 'Educación'
                },
                {
                    name: 'Esant',
                    y: 12,
                    drilldown: 'Esant'
                },
                {
                    name: 'Gestión del Riesgo',
                    y: 2,
                    drilldown: 'Gestión del Riesgo'
                },
                {
                    name: 'InderSantader',
                    y: 26,
                    drilldown: 'InderSantader'
                },
                {
                    name: 'Infraestructura',
                    y: 34,
                    drilldown: 'Infraestructura'
                },
                {
                    name: 'Interior',
                    y: 4,
                    drilldown: 'Interior'
                },
                {
                    name: 'Mujer y Genero',
                    y: 9,
                    drilldown: 'Mujer y Genero'
                },
                {
                    name: 'Oficina Juridica',
                    y: 1,
                    drilldown: 'Oficina Juridica'
                },
                {
                    name: 'Privada',
                    y: 1,
                    drilldown: 'Privada'
                },
                {
                    name: 'Salud',
                    y: 6,
                    drilldown: 'Salud'
                }
              
            ]
        }
    ],
   
});


    new DataTable('#example', {
    select: true
});

    // Data retrieved from https://gs.statcounter.com/browser-market-share#monthly-202201-202201-bar

// Create the chart
Highcharts.chart('container', {
    chart: {
        type: 'column'
    },
    title: {
        align: 'left',
        text: 'Total de Compromisos pactados por Secretaria'
    },
    subtitle: {
        align: 'left',
        text: ' <a href="http://statcounter.com" target="_blank"></a>'
    },
    accessibility: {
        announceNewData: {
            enabled: true
        }
    },
    xAxis: {
        type: 'category'
    },
    yAxis: {
        title: {
            text: 'Total de Compromisos'
        }

    },
    legend: {
        enabled: false
    },
    plotOptions: {
        series: {
            borderWidth: 0,
            dataLabels: {
                enabled: true,
                format: '{point.y:.1f}'
            }
        }
    },

    tooltip: {
        headerFormat: '<span style="font-size:11px">{series.name}</span><br>',
        pointFormat: '<span style="color:{point.color}">{point.name}</span>: <b>{point.y:.2f}</b> Compromisos<br/>'
    },

    series: [
        {
            name: 'Secretarias',
            colorByPoint: true,
            data: [
                {
                    name: 'Ambiental',
                    y: 1,
                    drilldown: 'Ambiental'
                },
                {
                    name: 'CAS',
                    y: 2,
                    drilldown: 'CAS'
                },
                {
                    name: 'Competividad',
                    y: 3,
                    drilldown: 'Competividad'
                },
                {
                    name: 'Cultura y Turismo',
                    y: 11,
                    drilldown: 'Cultura y Turismo'
                },
                {
                    name: 'Desarrollo Social',
                    y: 9,
                    drilldown: 'Desarrollo Social'
                },
                {
                    name: 'Educación',
                    y: 26,
                    drilldown: 'Educación'
                },
                {
                    name: 'Esant',
                    y: 12,
                    drilldown: 'Esant'
                },
                {
                    name: 'Gestión del Riesgo',
                    y: 2,
                    drilldown: 'Gestión del Riesgo'
                },
                {
                    name: 'InderSantader',
                    y: 26,
                    drilldown: 'InderSantader'
                },
                {
                    name: 'Infraestructura',
                    y: 34,
                    drilldown: 'Infraestructura'
                },
                {
                    name: 'Interior',
                    y: 4,
                    drilldown: 'Interior'
                },
                {
                    name: 'Mujer y Genero',
                    y: 9,
                    drilldown: 'Mujer y Genero'
                },
                {
                    name: 'Oficina Juridica',
                    y: 1,
                    drilldown: 'Oficina Juridica'
                },
                {
                    name: 'Privada',
                    y: 1,
                    drilldown: 'Privada'
                },
                {
                    name: 'Salud',
                    y: 6,
                    drilldown: 'Salud'
                }
              
            ]
        }
    ],
    
});


