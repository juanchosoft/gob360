document.addEventListener("DOMContentLoaded", function () {
    if (typeof graficosEstadoSimple === "undefined") return;

    for (const [id, datos] of Object.entries(graficosEstadoSimple)) {
        const container = document.getElementById(id);
        if (!container) continue;

        const { bueno = 0, regular = 0, malo = 0 } = datos;
        let etiqueta = 'Sin datos';

        if (bueno >= regular && bueno >= malo) {
            etiqueta = 'Aceptable';
        } else if (regular >= bueno && regular >= malo) {
            etiqueta = 'Insuficiente';
        } else if (malo >= bueno && malo >= regular) {
            etiqueta = 'Inaceptable';
        }

        const series = regular === 0 && malo === 0
            ? [bueno, malo]
            : [bueno, regular, malo];

        const labels = regular === 0 && malo === 0
            ? ['Sí', 'No']
            : ['Bueno', 'Regular', 'Malo'];

        const colors = regular === 0 && malo === 0
            ? ['#28a745', '#dc3545']
            : ['#28a745', '#ffc107', '#dc3545'];

        const options = {
            chart: {
                type: 'donut',
                height: 250
            },
            series: series,
            labels: labels,
            colors: colors,
            legend: {
                show: true,
                position: 'bottom'
            },
            dataLabels: {
                enabled: true,
                style: {
                    fontSize: '14px',
                    fontWeight: 'bold'
                }
            },
            plotOptions: {
                pie: {
                    donut: {
                        labels: {
                            show: true,
                            total: {
                                show: true,
                                label: etiqueta,
                                fontSize: '16px',
                                fontWeight: 'bold'
                            }
                        }
                    }
                }
            }
        };

        const chart = new ApexCharts(container, options);
        chart.render();
    }
});
