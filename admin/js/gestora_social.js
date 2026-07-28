$(document).on("ready", init);

function init() {
    q = {};
}

var GESTORA_SOCIAL = {
    charts: {}, // Almacena instancias de gráficos

    init: function () {
        // Carga los datos iniciales
        GESTORA_SOCIAL.fetchData("getPoblacionImpactadaPorMunicipio")
            .then((dataMostrar) => {
                // Click para cambiar graficas
                $('a[data-toggle="tab"]').on('click', function (e) {
                    e.preventDefault();

                    const provincia = $(this).attr("href").replace("#", ""); // ID de la provincia
                    GESTORA_SOCIAL.cambiarGrafica(provincia, dataMostrar); // Cambia la gráfica
                });

                // Renderiza la gráfica inicial
                GESTORA_SOCIAL.cambiarGrafica("Soto_Norte", dataMostrar);
            })
            .catch((error) => {
                console.error("Error al cargar los datos de población:", error);
            });
    },

    fetchData: function (operation) {
        const q = { op: operation };

        return $.ajax({
            data: q,
            type: "GET",
            dataType: "json",
            url: "admin/ajax/rqst.php",
        }).then((data) => {
            if (data.output.valid) {
                return data.output.response.map(item => ({
                    name: item.municipio,
                    provincia: item.provincia || null,
                    y: parseFloat(item.total),
                }));
            } else {
                return [];
            }
        });
    },

    cambiarGrafica: function (provincia, dataMostrar) {
        console.log(`Cambiando gráfica para la provincia: ${provincia}`);
        const containerId = `#bar-chart-Soto_Norte`; // ID genérico

        // Filtra los datos para la provincia seleccionada
        const dataFiltrada = dataMostrar.filter(item => item.provincia === provincia);

        console.log(`Datos filtrados para ${provincia}:`, dataFiltrada);

        // Actualiza el subtítulo de la gráfica
        const subtitulo = `Total Población Impactada ${provincia}`;
        $(`${containerId}`).closest(".card").find(".card-header h5").text(subtitulo);

        // Destruye cualquier gráfica previa
        if (GESTORA_SOCIAL.charts[provincia]) {
            GESTORA_SOCIAL.charts[provincia].destroy();
        }

        // Limpia el contenedor y renderiza una nueva gráfica si hay datos
        $(containerId).html("");
        if (dataFiltrada.length > 0) {
            GESTORA_SOCIAL.renderChart(containerId, subtitulo, dataFiltrada, provincia);
        } else {
            $(containerId).html(`<p>No hay datos disponibles para ${provincia}.</p>`);
        }
    },

    renderChart: function (container, title, seriesData, chartKey) {
    if (seriesData.length === 1) {
        seriesData.unshift({
            name: "Inicio",
            provincia: seriesData[0].provincia,
            y: 0
        });
    }

    const options = {
        chart: {
            type: 'area',
            height: 300,
            fontFamily: 'Poppins, sans-serif',
            toolbar: { show: false },
            animations: {
                enabled: true,
                easing: 'easeinout',
                speed: 600,
            }
        },
        stroke: {
            curve: 'smooth',
            width: 3,
            colors: ['#1abc9c']
        },
        fill: {
            type: 'gradient',
            gradient: {
                shadeIntensity: 1,
                opacityFrom: 0.4,
                opacityTo: 0.05,
                stops: [0, 90, 100]
            }
        },
        markers: {
            size: 4,
            colors: ['#ffffff'],
            strokeColor: '#1abc9c',
            strokeWidth: 2,
            hover: {
                size: 6
            }
        },
        dataLabels: {
            enabled: false
        },
        series: [{
            name: title,
            data: seriesData.map(item => item.y),
        }],
        xaxis: {
            categories: seriesData.map(item => item.name),
            labels: {
                style: {
                    fontSize: '12px',
                    colors: '#6c757d'
                },
                rotate: -35
            },
            axisBorder: { show: false },
            axisTicks: { show: false },
        },
        yaxis: {
            min: 0,
            labels: {
                style: {
                    fontSize: '12px',
                    colors: '#6c757d'
                },
                formatter: val => val.toLocaleString()
            },
            axisBorder: { show: false },
            axisTicks: { show: false }
        },
        grid: {
            borderColor: '#e0e0e0',
            strokeDashArray: 4,
            yaxis: { lines: { show: true } },
            xaxis: { lines: { show: false } }
        },
        tooltip: {
            theme: 'dark',
            style: { fontSize: '13px', fontFamily: 'inherit' },
            background: '#0f172a',
            y: {
                formatter: val => val.toLocaleString(),
                title: { formatter: () => 'Total:' }
            },
            custom: function({ series, seriesIndex, dataPointIndex, w }) {
                const val = series[seriesIndex][dataPointIndex];
                const label = w.globals.categoryLabels[dataPointIndex] || w.globals.labels[dataPointIndex] || '';
                return `<div style="background:#0f172a;color:#fff;padding:8px 12px;border-radius:6px;border:1px solid rgba(255,255,255,.15);font-size:13px;">
                    <strong>${label}</strong><br>Total: <b>${Number(val).toLocaleString()}</b>
                </div>`;
            }
        }
    };
    // =====================================================
// ✅ Highcharts DARK MODE: textos visibles en fondo oscuro
// (Pegar una sola vez antes de crear charts)
// =====================================================
if (window.Highcharts) {
  Highcharts.setOptions({
    chart: {
      backgroundColor: 'transparent',
      style: { fontFamily: 'system-ui, -apple-system, Segoe UI, Roboto, Arial' }
    },
    title: {
      style: { color: 'rgba(255,255,255,.92)', fontWeight: '800' }
    },
    subtitle: {
      style: { color: 'rgba(255,255,255,.70)' }
    },
    xAxis: {
      labels: { style: { color: 'rgba(255,255,255,.78)', fontSize: '11px' } },
      title: { style: { color: 'rgba(255,255,255,.80)' } },
      lineColor: 'rgba(255,255,255,.18)',
      tickColor: 'rgba(255,255,255,.18)'
    },
    yAxis: {
      labels: { style: { color: 'rgba(255,255,255,.78)', fontSize: '11px' } },
      title: { style: { color: 'rgba(255,255,255,.80)' } },
      gridLineColor: 'rgba(255,255,255,.12)'
    },
    legend: {
      itemStyle: { color: 'rgba(255,255,255,.82)', fontWeight: '700' },
      itemHoverStyle: { color: '#ffffff' }
    },
    tooltip: {
      backgroundColor: 'rgba(15,23,42,.92)',
      borderColor: 'rgba(255,255,255,.14)',
      style: { color: '#ffffff' }
    },
    credits: { enabled: false }
  });
}

    const chart = new ApexCharts(document.querySelector(container), options);
    chart.render();
    GESTORA_SOCIAL.charts[chartKey] = chart;
}
,
};

$(document).ready(function () {
    GESTORA_SOCIAL.init();
});
