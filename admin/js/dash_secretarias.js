$(function () {
    secretariasData.forEach(item => {
        const labels = [
            'Finalizado',
            'Suspendido',
            'Terminado',
            'Ejecutado',
            'En Contratación',
            'En Formulación',
            'Entregado',
            'En Ejecución'
        ];

        const series = [
            item.finalizado,
            item.suspendido,
            item.terminado,
            item.ejecutado,
            item.en_contratacion,
            item.en_formulacion,
            item.entregado,
            item.en_ejecucion
        ];

        const options = {
            chart: {
                height: 320,
                type: 'pie'
            },
            labels: labels,
            series: series,
            colors: ["#1abc9c", "#0e9e4a", "#00acc1", "#f1c40f", "#e74c3c", "#9b59b6", "#3498db", "#FFA500"],
            legend: {
                show: true,
                position: 'bottom'
            },
            dataLabels: {
                enabled: true,
                dropShadow: {
                    enabled: false
                }
            },
            responsive: [{
                breakpoint: 480,
                options: {
                    legend: {
                        position: 'bottom'
                    }
                }
            }]
        };

        const chart = new ApexCharts(document.querySelector(`#chart_${item.secretaria_id}`), options);
        chart.render();
    });

   const acciones = secretariasDataHacienda.map(item => item.accion);

    // Gráfica 1 - Cantidades por acción
    const seriesCantidad = [{
        name: 'Cantidad',
        data: secretariasDataHacienda.map(item => item.cantidad || 0)
    }];

    const chartCantidad = new ApexCharts(document.querySelector("#grafica_cantidad"), {
    chart: {
        type: 'bar',
        height: 350
    },
    title: {
    text: 'Cantidad de acciones por tipo',
        align: 'left',
        style: {
            fontSize: '18px',
            fontWeight: 'bold'
        }
    },
        xaxis: {
            categories: acciones,
            labels: {
                rotate: 0,
                style: {
                    fontSize: '12px'
                }
            }
        },
        series: seriesCantidad,
        dataLabels: {
            enabled: true
        },
        legend: {
            position: 'bottom'
        }
    });


    chartCantidad.render();

    // Gráfica 2 - Detalles por acción (Valores total)
    /* const accionesValores = dataValoresHacienda
        .filter(item => getCantidadPorAccionValor(item.accion) > 0)
        .map(item => item.accion);

    // Crear una única serie con todos los valores en orden
    const serieUnica = {
        name: 'Valor',
        data: accionesValores.map(accion => getCantidadPorAccionValor(accion))
    };

    const chartValores = new ApexCharts(document.querySelector("#grafica_valores"), {
        chart: {
            type: 'bar',
            height: 400
        },
        title: {
            text: 'Detalles por acción',
            align: 'left',
            style: {
                fontSize: '18px',
                fontWeight: 'bold'
            }
        },
        xaxis: {
            categories: accionesValores,
            labels: {
                rotate: 0,
                style: {
                    fontSize: '12px'
                }
            }
        },
        series: [serieUnica],
        dataLabels: {
            enabled: true,
            formatter: function (value) {
                return new Intl.NumberFormat('es-CO', {
                    style: 'currency',
                    currency: 'COP',
                    minimumFractionDigits: 0
                }).format(value);
            },
            style: {
                fontSize: '12px'
            }
        },
        tooltip: {
            y: {
                formatter: function (value) {
                    return new Intl.NumberFormat('es-CO', {
                        style: 'currency',
                        currency: 'COP',
                        minimumFractionDigits: 0
                    }).format(value);
                }
            }
        },
        yaxis: {
            labels: {
                formatter: function (value) {
                    return new Intl.NumberFormat('es-CO', {
                        style: 'currency',
                        currency: 'COP',
                        minimumFractionDigits: 0
                    }).format(value);
                },
                style: {
                    fontSize: '12px'
                }
            }
        },
        legend: {
            position: 'bottom'
        }
    });

    chartValores.render(); */

    // Filtrar acciones con valores > 0
    const accionesValores = dataValoresHacienda
        .filter(item => getCantidadPorAccionValor(item.accion) > 0)
        .map(item => item.accion);

    // Crear el arreglo de valores (series) correspondiente
    const valores = accionesValores.map(accion => getCantidadPorAccionValor(accion));

    // Opciones del gráfico tipo torta
    const optionsPie = {
        chart: {
            height: 400,
            type: 'pie'
        },
        labels: accionesValores,
        series: valores,
        colors: ["#1abc9c", "#0e9e4a", "#00acc1", "#f1c40f", "#e74c3c", "#9b59b6", "#3498db", "#FFA500"],
        legend: {
            show: true,
            position: 'bottom'
        },
        dataLabels: {
            enabled: true,
            style: {
                fontSize: '12px'
            }
        },
        tooltip: {
            y: {
                formatter: function (value) {
                    return new Intl.NumberFormat('es-CO', {
                        style: 'currency',
                        currency: 'COP',
                        minimumFractionDigits: 0
                    }).format(value);
                }
            }
        },
        responsive: [{
            breakpoint: 480,
            options: {
                legend: {
                    position: 'bottom'
                }
            }
        }]
    };

    // Renderizar gráfico tipo torta
    const chartPie = new ApexCharts(document.querySelector("#grafica_valores"), optionsPie);
    chartPie.render();


    });

// Utilidad para buscar por nombre de acción
function getCantidadPorAccion(nombreAccion) {
    const obj = secretariasDataHacienda.find(item => item.accion === nombreAccion);
    return obj ? parseFloat(obj.cantidad) : 0;
}
function getCantidadPorAccionValor(nombreAccion) {
    const obj = dataValoresHacienda.find(item => item.accion === nombreAccion);
    return obj ? parseFloat(obj.valor) : 0;
}
