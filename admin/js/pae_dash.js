$(function () {
    const urlParams = new URLSearchParams(window.location.search);
    const munParam = urlParams.get('mun');
    if (munParam) {
        MUN = munParam;
        waitForMunicipioSelectToHaveOption(munParam, () => {
            $("#tbl_municipio_id").val(munParam).trigger("change");
        });
    }
});

let updateHistoryDebounce;
let MUN;
let isUpdating = false;

function waitForMunicipioSelectToHaveOption(valueToCheck, callback) {
    const interval = setInterval(() => {
        const select = $("#tbl_municipio_id");
        if (select.length && select.find(`option[value="${valueToCheck}"]`).length > 0) {
            clearInterval(interval);
            callback();
        }
    }, 100);
}

const PAE_DASHBOARD = {
    /**
     * Metodo que obtiene las provincias que tiene problemas en sedes educativas
     */
    generarGraficaBarraProvinceasEnSedeEducativasConProblemas() {
        q = {};
        q.op = "sedeEducativasConProblemas";
        q.nickname = $("#nickname").val();
        q.id = $("#id").val();
        UTIL.cursorBusy();
        $.ajax({
            data: q,
            type: "GET",
            dataType: "json",
            url: "admin/ajax/rqst.php",
            success: function (data) {
                q = {};
                UTIL.cursorNormal();
                if (data.output.valid) {
                    let res = data.output.response;

                    // Extraer provincias (categorías del eje X) y totales (valores del eje Y)
                    let categories = res.map(item => item.provincia);
                    let dataSeries = res.map(item => item.total);

                    // Opciones del gráfico
                    var options = {
                        chart: {
                            type: 'bar',
                            height: 350
                        },
                        series: [{
                            name: 'La cantidad de escuelas:',
                            data: dataSeries
                        }],
                        xaxis: {
                            categories: categories
                        },
                        title: {
                            text: 'Cantidad de escuelas',
                            align: 'center'
                        },
                        colors: ['#FF4560'],
                        plotOptions: {
                            bar: {
                                distributed: true,
                                horizontal: false,
                                columnWidth: '45%',
                                endingShape: 'rounded'
                            }
                        },
                        dataLabels: {
                            enabled: true
                        }
                    };
                    // Renderizar gráfico
                    var chart = new ApexCharts(document.querySelector("#barchartesteo"), options);
                    chart.render();
                } else {

                }
            },
        });
    },

    updateUrlMunicipio(item) {
        const selectedMunicipio = item.value || MUN;
        // Obtiene la URL actual del navegador
        const currentUrl = new URL(window.location.href);
        const actualMun = currentUrl.searchParams.get('mun');
        // Si el municipio seleccionado es el mismo que el actual, no se realiza ninguna acción
        if (selectedMunicipio === actualMun) return;
        clearTimeout(updateHistoryDebounce);
        updateHistoryDebounce = setTimeout(() => {
            currentUrl.searchParams.set('mun', selectedMunicipio);
            // Se recarga la página con la nueva URL
            window.location.href = currentUrl.toString();
        }, 300);
    },

    updateSelectWithoutTrigger(selectId, value) {
        const selectElement = $(selectId);

        isUpdating = true;
        selectElement.off("change");
        selectElement.val(value).trigger("change");
        selectElement.on("change", (event) => this.updateUrlMunicipio(event.target));

        setTimeout(() => {
            isUpdating = false;
        }, 300);
    },

    loadContenido(url) {
        $.ajax({
            url: url.toString(),
            type: "GET",
            success(response) {
                const updatedContent = $(response).find("#containerDataPae").html();
                $("#containerDataPae").html(updatedContent);
            },
            error(error) {
                console.error("Error al cargar contenido:", error);
            }
        });
    }

};
const PAE_GRAFICAS = {
    chartMenaje: null,

};

$(function () {
    PAE_GRAFICAS.iniciar();
});
