$(document).on("ready", init);
var q, filtro;
var dataGraficoSeguridad;
var dataGraficoAgricultura;
var dataGraficoFamilia;
var dataGraficoSocial;
var dataGraficoEconomia;
var res = {};
var inclusion = 0;
var ambiente = 0;
var seguridad = 0;
var agricultura = 0;
var economia = 0;
var salud = 0;
var cultura = 0;
var infraestructura = 0;
var corrupcion = 0;
var comunicaciones = 0;
var educacion = 0;
var familia = 0;
var recreacion = 0;

document.querySelector('#tipo').addEventListener('change', () => GRAFICOS.cleanCanvas());

function init() {
    q = {};
}

$("#tipo_filtro").val("seleccione");

var GRAFICOS = {
    validatTipoFiltro: function() {
        var tipo = $("#tipo").val();
        switch (tipo) {
            case "municipio":
                $("#divDep").show();
                $("#divButton").show();
                $("#divMunicipio").show();
                $("#divVereda").hide();
                $("#filtro").val('novereda');
                DEPARTAMENTO.getMunicipios();
                break;

            case "vereda":
                $("#divDep").show();
                $("#divButton").show();
                $("#divMunicipio").show();
                $("#filtro").val('vereda');
                DEPARTAMENTO.getVeredasByMunicipioId();
                $("#divVereda").show();
                break;

            default:
                $("#divDep").hide();
                $("#divMunicipio").hide();
                $("#divVereda").hide();
                $("#divButton").hide();
                GRAFICOS.initVariables();
                break;
        }
    },
    validate: function() {
        var municipio = $("#tbl_municipio_id").val();
        var vereda = $("#tbl_vereda_id").val();
        q = {};
        q.op = "getGraficoTemaInteres";
        q.tbl_municipio_id = municipio;
        q.tbl_vereda_id = vereda;
        q.tipo = $("#tipo").val();

        if ($("#tipo").val() != '') {
            if ($("#tipo").val() == 'vereda') {
                if (municipio == "" || municipio == null || vereda == "" || vereda == null) {
                    return;
                }
            }
            if ($("#tipo").val() == 'municipio') {
                if (municipio == "" || municipio == null) {
                    return;
                }
            }
            GRAFICOS.getData(q);
        }

    },
    initVariables: function() {
        inclusion = 0;
        ambiente = 0;
        seguridad = 0;
        agricultura = 0;
        economia = 0;
        salud = 0;
        cultura = 0;
        infraestructura = 0;
        corrupcion = 0;
        comunicaciones = 0;
        educacion = 0;
        familia = 0;
        recreacion = 0;

    },
    getData: function(q) {
        GRAFICOS.initVariables();

        UTIL.cursorBusy();
        $.ajax({
            data: q,
            type: "POST",
            dataType: "json",
            url: "admin/ajax/rqst.php",
            success: function(data) {
                q = {};
                UTIL.cursorNormal();
                if (data.output.valid) {
                    res = data.output.response[0];
                    inclusion = parseFloat(res.inclusion);
                    ambiente = parseFloat(res.ambiente);
                    mdom = parseFloat(res.mdom);
                    seguridad = parseFloat(res.seguridad);
                    agricultura = parseFloat(res.agricultura);
                    economia = parseFloat(res.economia);
                    salud = parseFloat(res.salud);
                    infraestructura = parseFloat(res.infraestructura);
                    corrupcion = parseFloat(res.corrupcion);
                    comunicaciones = parseFloat(res.comunicaciones);
                    educacion = parseFloat(res.educacion);
                    familia = parseFloat(res.familia);

                    GRAFICOS.graficar();
                } else {
                    UTIL.mostrarMensajeError(data.output.response.content);

                    GRAFICOS.cleanCanvas();
                }
            },
        });
    },
    cleanCanvas: function() {
        var grafico = document.getElementById('grafico');
        grafico.innerHTML = '';
        $('#grafico').append('<canvas id="social" width="400" height="400"><canvas>');
        ctx = $("#grafico").get(0).getContext("2d");
    },
    validateSelect: function(select) {
        switch (select) {
            case 'departamento':
                DEPARTAMENTO.getMunicipios();
                break;
            case 'municipio':
                DEPARTAMENTO.getVeredasByMunicipioId();
                break;
            default:
                break;
        }
        GRAFICOS.cleanCanvas();
    },

    graficar: function() {
        Highcharts.chart("grafico", {
            chart: {
                type: "column",
            },
            title: {
                text: "",
            },
            subtitle: {
                text: "Información de interés 2022",
            },
            xAxis: {
                categories: ["seguridad", "agricultura", "salud", "inclusion", "ambiente", "economia", "infraestructura",  "corupcion", "comunicaciones", "educacion", "familia", "recreacion"],
                crosshair: true,
            },
            yAxis: {
                min: 0,
                title: {
                    text: "Garfica con los datos relevantes",
                },
            },
            tooltip: {
                headerFormat: '<span style="font-size:11px">{series.name}</span><br>',
                pointFormat: '<span style="color:{point.color}">{point.name}</span>: <b>{point.y}</b> Totales<br/>',
                footerFormat: "</table>",
                shared: true,
                useHTML: true,
            },
            plotOptions: {
                series: {
                    borderWidth: 0,
                    dataLabels: {
                        enabled: true,
                        format: "{point.y}",
                    },
                },
            },
            series: [{
                name: "Información de interés",
                data: [
                    seguridad,
                    agricultura,
                    salud,
                    inclusion,
                    ambiente,
                    economia,
                    infraestructura,
                    corrupcion,
                    comunicaciones,
                    educacion,
                    familia,
                    recreacion
                ],
            }, ],
        });
    },
};