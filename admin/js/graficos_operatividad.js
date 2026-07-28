$(document).on("ready", init);
var q, filtro;
var dataGraficoEconomico;
var dataGraficoSocial;
var dataGraficoSocialArboles;
var dataGraficoArmado;
var dataGraficoArmadoMuniLiquidos;
var res = {};

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

                    dataGraficoArmadoMuniLiquidos = [res.liquidos, res.municiones];

                    dataGraficoSocial = [res.capturas_soc, res.madera];

                    dataGraficoSocialArboles = [res.siembra];

                    dataGraficoArmado = [
                        res.presentaciones,
                        res.mdom,
                        res.sometimiento,
                        res.capturas_gao,
                        res.capturas_gdo,
                        res.bajas_delco,
                        res.capturas_delco,
                        res.menores
                    ];
                    dataGraficoEconomico = [
                        res.upm,
                        res.dragas,
                        res.motores,
                        res.mercurio,
                        res.explosivos,
                        res.armas_cortas,
                        res.lab_ch,
                        res.semilleros,
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
                        res.combates,
                        res.hoja,
                    ];
                    GRAFICOS.graficar();
                } else {
                    UTIL.mostrarMensajeError(data.output.response.content);

                    GRAFICOS.cleanCanvas();
                }
            },
        });
    },
    graficarArmadoMuniciLiquidos: function() {
        // LIMPIAR CANVAN
        var pieChartContentArmado = document.getElementById('pieChartContentArmadoEconomicoMunucionesLiquidos');
        pieChartContentArmado.innerHTML = '';
        $('#pieChartContentArmadoEconomicoMunucionesLiquidos').append('<canvas id="economiconarcoMunicionesLiquidos" width="400" height="400"><canvas>');
        ctx = $("#economiconarcoMunicionesLiquidos").get(0).getContext("2d");

        var donutData = {
            labels: [
                "Liquidos",
                "Municiones"
            ],
            datasets: [{
                data: dataGraficoArmadoMuniLiquidos,
                backgroundColor: ['#f56954', '#00a65a'],
            }]
        }
        var pieChartCanvas = $('#economiconarcoMunicionesLiquidos').get(0).getContext('2d')
        var pieData = donutData;
        var pieOptions = {
            maintainAspectRatio: false,
            responsive: true,
        }
        var pieChart = new Chart(pieChartCanvas, {
            type: 'pie',
            data: pieData,
            options: pieOptions
        });
    },
    graficarArmado: function() {
        // LIMPIAR CANVAN
        var pieChartContentArmado = document.getElementById('pieChartContentArmado');
        pieChartContentArmado.innerHTML = '';
        $('#pieChartContentArmado').append('<canvas id="armado" width="400" height="400"><canvas>');
        ctx = $("#armado").get(0).getContext("2d");


        var donutData = {
            labels: [
                "Presentaciones Voluntarias",
                "MDOM",
                "Sometimiento justicia",
                "Capturas GAO",
                "Capturas GDO",
                "Bajas Delco",
                "Capturas Delco",
                "Combates",
                "Menores Recuperados",
            ],
            datasets: [{
                data: dataGraficoArmado,
                backgroundColor: ['#f56954', '#00a65a', '#f39c12', '#00c0ef', '#E23F16', '#E91DE9', '#0F59EC', '#29EC0F'],
            }]
        }
        var pieChartCanvas = $('#armado').get(0).getContext('2d')
        var pieData = donutData;
        var pieOptions = {
            maintainAspectRatio: false,
            responsive: true,
        }
        var pieChart = new Chart(pieChartCanvas, {
            type: 'pie',
            data: pieData,
            options: pieOptions
        });

    },
    graficarEconomicoArmado: function() {
        // LIMPIAR CANVAN
        var pieChartContentArmadoEconomico = document.getElementById('pieChartContentArmadoEconomico');
        pieChartContentArmadoEconomico.innerHTML = '';
        $('#pieChartContentArmadoEconomico').append('<canvas id="economiconarco" width="400" height="400"><canvas>');
        ctx = $("#economiconarco").get(0).getContext("2d");


        var donutData = {
            labels: [
                "Upm",
                "Dragas",
                "Motores",
                "Mercurio",
                "Explosivos",
                "Armas cortas",
                "Lab ch",
                "Semilleros",
                "Depositos",
                "Campamentos",
                "Lab Pcb",
                "Pasta coca",
                "Armas largas",
                "Erradicación",
                "Marihuana",
                "Pasta proceso",
                "Cloridrato",
                "Vehiculos",
                "retroescavadoras",
                "Otras Maq",
                "Otras Sustancias Alucinogenas",
                "Dinero Incautado",
                "Hoja de Coca",
            ],
            datasets: [{
                data: dataGraficoEconomico,
                backgroundColor: [
                    '#f56954',
                    '#00a65a',
                    '#f39c12',
                    '#6cf312',
                    '#00c0ef',
                    '#EE37C6',
                    '#F54914',
                    '#155BE7',
                    '#37EE6E',
                    '#CFF34C',
                    '#501A05',
                    '#ECE90D',
                    '#0C43B1',
                    '#351002',
                    '#E20F0F',
                    '#E74141',
                    '#A58772',
                    '#175DEB',
                    '#F8E007F3',
                    '#681706',
                    '#08700F',
                    '#084570',
                    '#4C1D5B',
                ],
            }]
        }
        var pieChartCanvas = $('#economiconarco').get(0).getContext('2d')
        var pieData = donutData;
        var pieOptions = {
            maintainAspectRatio: false,
            responsive: true,
        }
        var pieChart = new Chart(pieChartCanvas, {
            type: 'pie',
            data: pieData,
            options: pieOptions
        });
    },

    cleanCanvas: function() {
        var pieChartContentSocial = document.getElementById('pieChartContentSocial');
        pieChartContentSocial.innerHTML = '';
        $('#pieChartContentSocial').append('<canvas id="social" width="400" height="400"><canvas>');
        ctx = $("#social").get(0).getContext("2d");

        var pieChartContentArmadoEconomico = document.getElementById('pieChartContentArmadoEconomico');
        pieChartContentArmadoEconomico.innerHTML = '';
        $('#pieChartContentArmadoEconomico').append('<canvas id="economiconarco" width="400" height="400"><canvas>');
        ctx = $("#economiconarco").get(0).getContext("2d");

        var pieChartContentArmado = document.getElementById('pieChartContentArmado');
        pieChartContentArmado.innerHTML = '';
        $('#pieChartContentArmado').append('<canvas id="armado" width="400" height="400"><canvas>');
        ctx = $("#armado").get(0).getContext("2d");

        var pieChartContentSocialArboles = document.getElementById('pieChartContentSocialArboles');
        pieChartContentSocialArboles.innerHTML = '';
        $('#pieChartContentSocialArboles').append('<canvas id="socialArboles" width="400" height="400"><canvas>');
        ctx = $("#socialArboles").get(0).getContext("2d");
    },

    graficarSocial: function() {
        // LIMPIAR CANVAN
        var pieChartContentSocial = document.getElementById('pieChartContentSocial');
        pieChartContentSocial.innerHTML = '';
        $('#pieChartContentSocial').append('<canvas id="social" width="400" height="400"><canvas>');
        ctx = $("#social").get(0).getContext("2d");

        var donutData = {
            labels: [
                "Capturados", "Madera Mt3"
            ],
            datasets: [{
                data: dataGraficoSocial,
                backgroundColor: [
                    '#00a65a',
                    '#155BE7',
                ],
            }]
        }
        var pieChartCanvas = $('#social').get(0).getContext('2d')
        var pieData = donutData;
        var pieOptions = {
            maintainAspectRatio: false,
            responsive: true,
        }
        var pieChart = new Chart(pieChartCanvas, {
            type: 'pie',
            data: pieData,
            options: pieOptions
        });
    },
    graficarSocialArboles: function() {
        // LIMPIAR CANVAN
        var pieChartContentSocialArboles = document.getElementById('pieChartContentSocialArboles');
        pieChartContentSocialArboles.innerHTML = '';
        $('#pieChartContentSocialArboles').append('<canvas id="socialArboles" width="400" height="400"><canvas>');
        ctx = $("#socialArboles").get(0).getContext("2d");

        var donutData = {
            labels: [
                "Siembra"
            ],
            datasets: [{
                data: dataGraficoSocialArboles,
                backgroundColor: [
                    '#EE37C6',
                ],
            }]
        }
        var pieChartCanvas = $('#socialArboles').get(0).getContext('2d')
        var pieData = donutData;
        var pieOptions = {
            maintainAspectRatio: false,
            responsive: true,
        }
        var pieChart = new Chart(pieChartCanvas, {
            type: 'pie',
            data: pieData,
            options: pieOptions
        });
    },
    graficar: function() {

        GRAFICOS.graficarArmado();

        GRAFICOS.graficarEconomicoArmado();

        GRAFICOS.graficarSocial();

        GRAFICOS.graficarSocialArboles();

        GRAFICOS.graficarArmadoMuniciLiquidos();
    },
};