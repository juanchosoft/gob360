$(document).on("ready", init);
var q, filtro;
var dataGraficoEconomico;
var dataGraficoSocial;
var dataGraficoSocialArboles;
var dataGraficoArmado;
var dataGraficoArmadoMuniLiquidos;
var res = {};

// Plan Artemisa
var siembrea = 0;
var madera = 0;
var capturas_soc = 0;

// Armado
var presentaciones = 0;
var mdom = 0;
var sometimiento = 0;
var capturas_gao = 0;
var capturas_gdo = 0;
var bajas_delco = 0;
var capturas_delco = 0;
var menores = 0;
var lab_ch = 0;

// Factor economico
var lab_pbc = 0;
var pasta_coca = 0;
var mariguana = 0;
var pasta_proceso = 0;
var cloridrato = 0;
var hoja = 0;
var otras_sustancias = 0;
var depositos = 0;
var dinero = 0;
var fauna = 0;
var dominio = 0;

// Factor economico 2
var erradicacion = 0;
var semilleros = 0;
var liquidos = 0;
var municiones = 0;
var proveedores = 0;
var semilleros_matas = 0;

// Factor econimico 3 otras
var armas_largas = 0;
var armas_cortas = 0;

// Factor ecnomico minas
var upm = 0;
var dragas = 0;
var motores = 0;
var mercurio = 0;
var explosivos = 0;
var retroescavadoras = 0;
var otras_maq = 0;
var vehiculos = 0;
var minas = 0;

var opsic = 0;

function init() {
    q = {};
}
$("#tipo_filtro").val("Seleccione");

var GRAFICOS = {
    validate: function() {
        var brigada = $("#tbl_brigada_id").val();
        var batallon = $("#tbl_batallon_id").val();
        var filtro = $("#filtro").val();
        var bValid = true;
        var msj = "Falta ingresar información obligatoria, marcada con asterisco.";
        q = {};
        q.op = "get_graficos";

        if (filtro != "" && filtro != null) {
            if (filtro == 'Batallon') {

                $("#divBatallon").show();
                $("#divBrigada").hide();

                if (batallon == "" || batallon == null) {
                    UTIL.mostrarMensajeValidacion(msj);
                    bValid = false;
                    return;
                } else {
                    q.tbl_batallon_id = batallon;
                }
            }
            if (filtro == 'Brigada') {
                $("#divBrigada").show();
                $("#divBatallon").hide();

                if (brigada == "" || brigada == null) {
                    UTIL.mostrarMensajeValidacion(msj);
                    bValid = false;
                    return;
                } else {
                    q.tbl_brigada_id = brigada;
                }
            }
        } else {
            UTIL.mostrarMensajeValidacion("Debe seleccionar un filtro.");
            bValid = false;
            return;
        }
        GRAFICOS.getData(q);
    },
    getData: function(q) {
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

                    // PLan artemisa
                    siembrea = parseFloat(res.siembra);
                    madera = parseFloat(res.madera);
                    capturas_soc = parseFloat(res.capturas_soc);

                    // Armado
                    presentaciones = parseFloat(res.presentaciones);
                    mdom = parseFloat(res.mdom);
                    sometimiento = parseFloat(res.sometimiento);
                    capturas_gao = parseFloat(res.capturas_gao);
                    capturas_gdo = parseFloat(res.capturas_gdo);
                    bajas_delco = parseFloat(res.bajas_delco);
                    capturas_delco = parseFloat(res.capturas_delco);
                    menores = parseFloat(res.menores);

                    // Factor economico
                    lab_pbc = parseFloat(res.lab_pbc);
                    pasta_coca = parseFloat(res.pasta_coca);
                    mariguana = parseFloat(res.mariguana);
                    pasta_proceso = parseFloat(res.pasta_proceso);
                    cloridrato = parseFloat(res.cloridrato);
                    hoja = parseFloat(res.hoja);
                    otras_sustancias = parseFloat(res.otras_sustancias);
                    depositos = parseFloat(res.depositos);
                    lab_ch = parseFloat(res.lab_ch);

                    // Factpr economico 2
                    erradicacion = parseFloat(res.erradicacion);
                    semilleros = parseFloat(res.semilleros);
                    semilleros_matas = parseFloat(res.semilleros_matas);
                    liquidos = parseFloat(res.liquidos);
                    solidos = parseFloat(res.solidos);
                    municiones = parseFloat(res.municiones);
                    dinero = parseFloat(res.dinero);
                    fauna = parseFloat(res.fauna);
                    proveedores = parseFloat(res.proveedores);
                    dominio = parseFloat(res.dominio);

                    // Factor ecnomico y otros
                    armas_largas = parseFloat(res.armas_largas);
                    armas_cortas = parseFloat(res.armas_cortas);

                    // Factor ecnomico minas
                    upm = parseFloat(res.upm);
                    dragas = parseFloat(res.dragas);
                    motores = parseFloat(res.motores);
                    mercurio = parseFloat(res.mercurio);
                    explosivos = parseFloat(res.explosivos);
                    minas = parseFloat(res.minas);
                    retroescavadoras = parseFloat(res.retroescavadoras);
                    otras_maq = parseFloat(res.otras_maq);
                    vehiculos = parseFloat(res.vehiculos);

                    // Psicologico
                    opsic = parseFloat(res.opsic);

                    dataGraficoArmadoMuniLiquidos = [res.liquidos, res.municiones];
                    dataGraficoSocial = [res.capturas_soc, res.madera];
                    dataGraficoSocialArboles = [res.siembra];
                    dataGraficoEconomico = [
                        res.upm,
                        res.dragas,
                        res.motores,
                        res.mercurio,
                        res.explosivos,
                        res.minas,
                        res.armas_cortas,
                        res.lab_ch,
                        res.semilleros,
                        res.semilleros_matas,
                        res.depositos,
                        res.campamentos,
                        res.lab_pbc,
                        res.pasta_coca,
                        res.armas_largas,
                        res.erradicacion,
                        res.mariguana,
                        res.pasta_proceso,
                        res.cloridrato,
                        res.vehiculos,
                        res.retroescavadoras,
                        res.otras_maq,
                        res.otras_sustancias,
                        res.dinero,
                        res.proveedores,
                        res.hoja,
                        res.fauna,
                        res.dominio,
                    ];
                    GRAFICOS.graficar();
                } else {
                    UTIL.mostrarMensajeError(data.output.response.content);

                    GRAFICOS.cleanCanvas();
                }
            },
        });
    },
    graficar: function() {

        GRAFICOS.graficarPlanArtemisa();

        GRAFICOS.graficarPlanArtemisa1();

        GRAFICOS.graficarArmado();

        GRAFICOS.graficarArmado1();

        GRAFICOS.graficarEconomicoLaboratorio();

        GRAFICOS.graficarEconomicoLaboratorio1();

        GRAFICOS.graficarEconomicoLaboratorioErradicacion();

        GRAFICOS.graficarEconomicoOtros();

        GRAFICOS.graficarEconomicoOtros1();

        GRAFICOS.graficarEconomicoMinas();

        GRAFICOS.graficarEconomicoMinas1();

        GRAFICOS.graficarPsicologico();

        GRAFICOS.graficarOtros();
    },
    graficarPsicologico: function() {
        Highcharts.chart('psicologico', {
            chart: {
                type: 'column'
            },
            title: {
                text: 'OPSIC'
            },
            subtitle: {
                text: ''
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
                min: 0,
                title: {
                    text: 'Cantidades'
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
                        format: '{point.y}'
                    }
                }
            },

            tooltip: {
                headerFormat: '<span style="font-size:11px">{series.name}</span><br>',
                pointFormat: '<span style="color:{point.color}">{point.name}</span>: <b>{point.y}</b> Totales<br/>'
            },
            series: [{
                name: 'Population',
                data: [
                    ['OPSIC', opsic],
                ],

            }]
        });
    },


    // ________grafico viejo_______
    graficarPlanArtemisa: function() {
        Highcharts.chart('artemisa', {
            chart: {
                type: 'column'
            },
            title: {
                text: 'PLAN ARTEMISA'
            },
            subtitle: {
                text: 'CAPTURAS, MADERA INCAUTADA'
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
                min: 0,
                title: {
                    text: 'Cantidades'
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
                        format: '{point.y}'
                    }
                }
            },

            tooltip: {
                headerFormat: '<span style="font-size:11px">{series.name}</span><br>',
                pointFormat: '<span style="color:{point.color}">{point.name}</span>: <b>{point.y}</b> Totales<br/>'
            },
            series: [{
                name: 'Cantidad de:',
                colorByPoint: true,
                data: [
                    ['CAPTURAS', capturas_soc],
                    ['MADERA INCAUTADA', madera]


                ],


            }]
        });
    },
    graficarPlanArtemisa1: function() {
        Highcharts.chart('artemisa1', {
            chart: {
                type: 'column'
            },
            title: {
                text: 'PLAN ARTEMISA'
            },
            subtitle: {
                text: 'ARBOLES SEMBRADOS'
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
                min: 0,
                title: {
                    text: 'Cantidades'
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
                        format: '{point.y}'
                    }
                }
            },

            tooltip: {
                headerFormat: '<span style="font-size:11px">{series.name}</span><br>',
                pointFormat: '<span style="color:{point.color}">{point.name}</span>: <b>{point.y}</b> Totales<br/>'
            },
            series: [{
                name: 'Cantidad de:',
                colorByPoint: true,
                data: [
                    ['ARBOLES SEMBRADOS', siembrea]
                ],

            }]
        });
    },

    graficarArmado: function() {
        Highcharts.chart('armado', {
            chart: {
                type: 'column'
            },
            title: {
                text: 'FACTOR ARMADO'
            },
            subtitle: {
                text: 'AFECTACIONES A ESTRUCTURAS ARMADAS'
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
                min: 0,
                title: {
                    text: 'Cantidades'
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
                        format: '{point.y}'
                    }
                }
            },

            tooltip: {
                headerFormat: '<span style="font-size:11px">{series.name}</span><br>',
                pointFormat: '<span style="color:{point.color}">{point.name}</span>: <b>{point.y}</b> Totales<br/>'
            },
            series: [{
                name: 'Cantidad de:',
                colorByPoint: true,
                data: [
                    ['PRESENTACIONES', presentaciones],
                    ['MDOM', mdom],
                    ['SOMETIMIENTO', sometimiento],
                    ['BAJAS DELCO', bajas_delco],
                    ['MENORES RECUPERADOS', menores]
                ],

            }]
        });
    },

    graficarArmado1: function() {
        Highcharts.chart('armado1', {
            chart: {
                type: 'column'
            },
            title: {
                text: 'FACTOR ARMADO'
            },
            subtitle: {
                text: 'AFECTACIONES A ESTRUCTURAS ARMADAS - CAPTURAS'
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
                min: 0,
                title: {
                    text: 'Cantidades'
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
                        format: '{point.y}'
                    }
                }
            },

            tooltip: {
                headerFormat: '<span style="font-size:11px">{series.name}</span><br>',
                pointFormat: '<span style="color:{point.color}">{point.name}</span>: <b>{point.y}</b> Totales<br/>'
            },
            series: [{
                name: 'Cantidad de:',
                colorByPoint: true,
                data: [

                    ['CAPTURAS GAO', capturas_gao],
                    ['CAPTURAS GDO', capturas_gdo],
                    ['CAPTURAS DELCO', capturas_delco]

                ],

            }]
        });
    },
    graficarEconomicoLaboratorio: function() {
        Highcharts.chart('economico_min', {
            chart: {
                type: 'column'
            },
            title: {
                text: 'FACTOR ECONOMICO'
            },
            subtitle: {
                text: 'AFECTACIONES A LABORATORIOS Y CULTIVOS ILICITOS'
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
                min: 0,
                title: {
                    text: 'Cantidades'
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
                        format: '{point.y}'
                    }
                }
            },

            tooltip: {
                headerFormat: '<span style="font-size:11px">{series.name}</span><br>',
                pointFormat: '<span style="color:{point.color}">{point.name}</span>: <b>{point.y}</b> Totales<br/>'
            },
            series: [{
                name: 'Population',
                colorByPoint: true,
                data: [
                    ['OTRAS SUSTANCIAS', otras_sustancias],
                    ['PASTA EN PROCESO', pasta_proceso],
                    ['PASTA DE COCA', pasta_coca],
                    ['CLORIDRATO COCAINA', cloridrato]

                ],

            }]
        });


    },

    graficarEconomicoLaboratorio1: function() {
        Highcharts.chart('economico_min1', {
            chart: {
                type: 'column'
            },
            title: {
                text: 'FACTOR ECONOMICO'
            },
            subtitle: {
                text: 'AFECTACIONES A LABORATORIOS Y CULTIVOS ILICITOS'
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
                min: 0,
                title: {
                    text: 'Cantidades'
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
                        format: '{point.y}'
                    }
                }
            },

            tooltip: {
                headerFormat: '<span style="font-size:11px">{series.name}</span><br>',
                pointFormat: '<span style="color:{point.color}">{point.name}</span>: <b>{point.y}</b> Totales<br/>'
            },
            series: [{
                name: 'Cantidad de:',
                colorByPoint: true,
                data: [
                    ['LABORATORIOS PBC', lab_pbc],
                    ['LABORATORIOS CH', lab_ch],
                    ['DEPOSITOS', depositos],

                ],

            }]
        });


    },
    graficarEconomicoLaboratorioErradicacion: function() {
        Highcharts.chart('economico_2', {
            chart: {
                type: 'column'
            },
            title: {
                text: 'FACTOR ECONOMICO CULTIVOS ILICITOS'
            },
            subtitle: {
                text: 'AFECTACIONES A LABORATORIOS Y CULTIVOS ILICITOS Y ERRADICACIÓN'
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
                min: 0,
                title: {
                    text: 'Cantidades'
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
                        format: '{point.y}'
                    }
                }
            },

            tooltip: {
                headerFormat: '<span style="font-size:11px">{series.name}</span><br>',
                pointFormat: '<span style="color:{point.color}">{point.name}</span>: <b>{point.y}</b> Totales<br/>'
            },
            series: [{
                name: 'Cantidad de:',
                colorByPoint: true,
                data: [
                    ['MARIHUANA', mariguana],
                    ['ERRADICACION', erradicacion],
                    ['SEMILLEROS', semilleros],
                    ['SEMILLEROS MATAS', semilleros_matas],
                    ['INSUMOS LIQUIDOS', liquidos],
                    ['INSUMOS SOLIDOS', solidos],
                    ['MUNICIONES', municiones],
                    ['HOJA DE COCA', hoja]
                ],

            }]
        });
    },
    graficarEconomicoOtros: function() {
        Highcharts.chart('economico_3', {
            chart: {
                type: 'column'
            },
            title: {
                text: 'FACTOR ECONOMICO y OTROS'
            },
            subtitle: {
                text: ''
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
                min: 0,
                title: {
                    text: 'Cantidades'
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
                        format: '{point.y}'
                    }
                }
            },

            tooltip: {
                headerFormat: '<span style="font-size:11px">{series.name}</span><br>',
                pointFormat: '<span style="color:{point.color}">{point.name}</span>: <b>{point.y}</b> Totales<br/>'
            },
            series: [{
                name: 'Cantidad de:',
                colorByPoint: true,
                data: [
                    ['PROVEEDORES', proveedores],
                    ['EXTINCIONES DE DOMINIO', dominio],
                    ['ARMAS LARGAS', armas_largas],
                    ['ARMAS CORTAS', armas_cortas],
                    ['FAUNA RECUPERADA', fauna]
                ],

            }]
        });
    },
    graficarEconomicoOtros1: function() {
        Highcharts.chart('economico_4', {
            chart: {
                type: 'column'
            },
            title: {
                text: 'FACTOR ECONOMICO y DINERO INCAUTADO'
            },
            subtitle: {
                text: ''
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
                min: 0,
                title: {
                    text: 'Cantidades'
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
                        format: '{point.y}'
                    }
                }
            },

            tooltip: {
                headerFormat: '<span style="font-size:11px">{series.name}</span><br>',
                pointFormat: '<span style="color:{point.color}">{point.name}</span>: <b>{point.y}</b> Totales<br/>'
            },
            series: [{
                name: 'Cantidad de:',
                colorByPoint: true,
                data: [
                    ['DINERO INCAUTADO', dinero]

                ],

            }]
        });
    },

    graficarEconomicoMinas1: function() {
        Highcharts.chart('economico_minas1', {
            chart: {
                type: 'column'
            },
            title: {
                text: 'FACTOR ECONOMICO MINAS'
            },
            subtitle: {
                text: 'AFECTACIONES A EXPLOTACIONES ILICITAS DE YACIMIENTOS MINEROS'
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
                min: 0,
                title: {
                    text: 'Cantidades'
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
                        format: '{point.y}'
                    }
                }
            },

            tooltip: {
                headerFormat: '<span style="font-size:11px">{series.name}</span><br>',
                pointFormat: '<span style="color:{point.color}">{point.name}</span>: <b>{point.y}</b> Totales<br/>'
            },
            series: [{
                name: 'Cantidad de:',
                colorByPoint: true,
                data: [
                    ['MOTORES', motores],
                    ['MATERIAL EXPLOSIVO', explosivos],
                    ['ARTEFACTOS EXPLOSIVOS',minas]
                ],

            }]
        });
    },

    graficarEconomicoMinas: function() {
        Highcharts.chart('economico_minas', {
            chart: {
                type: 'column'
            },
            title: {
                text: 'FACTOR ECONOMICO MINAS'
            },
            subtitle: {
                text: 'AFECTACIONES A EXPLOTACIONES ILICITAS DE YACIMIENTOS MINEROS'
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
                min: 0,
                title: {
                    text: 'Cantidades'
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
                        format: '{point.y}'
                    }
                }
            },

            tooltip: {
                headerFormat: '<span style="font-size:11px">{series.name}</span><br>',
                pointFormat: '<span style="color:{point.color}">{point.name}</span>: <b>{point.y}</b> Totales<br/>'
            },
            series: [{
                name: 'Cantidad de:',
                colorByPoint: true,
                data: [
                    ['UPM', upm],
                    ['DRAGAS', dragas],
                    ['MERCURIO', mercurio],
                    ['RETROESCAVADORAS', retroescavadoras],
                    ['OTRAS MAQUINAS', otras_maq],
                    ['VEHICULOS', vehiculos]
                ],

            }]
        });
    },

};