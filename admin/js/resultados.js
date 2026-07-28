$(document).on('ready', init);
var q;
var dataInfo;

function init() {
    q = {};
    dataInfo = {};
}

var RESULTADOS = {
    clearForm() {
        $("#tbodyConsolidadoEcon").empty().append("");
        $("#tbodyConsolidadoSocial").empty().append("");
        $("#tbodyConsolidadoArmado").empty().append("");

        $("#tbodyResultadosArmado").empty().append("");
        $("#tbodyResultadosSocial").empty().append("");
        $("#tbodyResultadosEconomico").empty().append("");
    },
    getFactores: function() {
        if ($("#filtroFechas").val() === 'si') {
            if ($("#fecha_inicio").val() == "" || $("#fecha_fin").val() == "") {
                UTIL.mostrarMensajeValidacion('Debes ingresar un rango de fechas');
                return;
            }
            if ($("#fecha_inicio").val() > $("#fecha_fin").val()) {
                UTIL.mostrarMensajeValidacion('La fecha inicial no puede ser mayor a la final');
                return;
            }
        }
        q = {};
        if ($("#filtro").val() === 'vereda') {
            q.op = "getfactores";
            q.codigo_departamento = $("#tbl_departamento_id").val();
            q.codigo_muncipio = $("#tbl_municipio_id").val();
            q.vereda = $("#tbl_vereda_id").val();
        } else {
            q.op = "getfactoresbymunic";
            q.codigo_departamento = $("#tbl_departamento_id").val();
            q.codigo_muncipio = $("#tbl_municipio_id").val();
        }
        //Resultados
        if ($("#filtroFechas").val() === 'si') {
            q.filtro_fechas = 'si';
            q.fecha_inicio = $("#fecha_inicio").val();
            q.fecha_fin = $("#fecha_fin").val();
        }
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
                    /**================================================================================================
                     * !                                        CONSOLIDADOS
                     *================================================================================================**/
                    var resConsEco = data.output.consolidadoEconomico;
                    var tableConsEcon = "";
                    for (var j in resConsEco) {
                        tableConsEcon += "<tr>";
                        tableConsEcon += '<td class="text-primary">' + resConsEco[j].tipo + "</td>";
                        tableConsEcon += '<td class="text-primary">' + resConsEco[j].cantidad + "</td>";
                        tableConsEcon += "</tr>";
                    }

                    var resConsSoc = data.output.consolidadoSocial;
                    var tableConsSoc = "";
                    for (var j in resConsSoc) {
                        tableConsSoc += "<tr>";
                        tableConsSoc += '<td class="text-primary">' + resConsSoc[j].tipo + "</td>";
                        tableConsSoc += '<td class="text-primary">' + resConsSoc[j].cantidad + "</td>";
                        tableConsSoc += "</tr>";
                    }

                    var resConsArm = data.output.consolidadoArmado;
                    var tableConsArm = "";
                    for (var j in resConsArm) {
                        tableConsArm += "<tr>";
                        tableConsArm += '<td class="text-primary">' + resConsArm[j].factor_armado + "</td>";
                        tableConsArm += '<td class="text-primary">' + resConsArm[j].comision + "</td>";
                        tableConsArm += '<td class="text-primary">' + resConsArm[j].cantidad + "</td>";
                        tableConsArm += "</tr>";
                    }

                    $("#tbodyConsolidadoEcon").empty().append(tableConsEcon);
                    $("#tbodyConsolidadoSocial").empty().append(tableConsSoc);
                    $("#tbodyConsolidadoArmado").empty().append(tableConsArm);

                    dataInfo = {};
                    dataInfo.resConsEco = resConsEco;
                    dataInfo.resConsSoc = resConsSoc;
                    dataInfo.resConsArm = resConsArm;

                    RESULTADOS.getResultados();

                    /**================================================================================================
                     * !                                   FIN      CONSOLIDADOS
                     *================================================================================================**/
                } else {
                    UTIL.mostrarMensajeError(data.output.response.content);

                    RESULTADOS.clearForm();
                }
            },
        });
    },
    getResultados: function() {
        q.op = "getresultadosmunicipio";
        q.codigo_departamento = $("#tbl_departamento_id").val();
        q.codigo_muncipio = $("#tbl_municipio_id").val();
        q.fecha_inicio = $("#fecha_inicio").val();
        q.fecha_fin = $("#fecha_fin").val();
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

                    /**================================================================================================
                     * !            RESULTADOS ARMADO
                     *================================================================================================**/
                    var resConsArm = data.output.armado;
                    var tableResulArm = "";
                    for (var j in resConsArm) {
                        tableResulArm += "<tr>";
                        tableResulArm += '<td class="text-primary">' + resConsArm[j].nombre + "</td>";
                        tableResulArm += '<td class="text-primary">' + resConsArm[j].comision + "</td>";
                        tableResulArm += '<td class="text-primary">' + resConsArm[j].cant_baja + "</td>";
                        tableResulArm += '<td class="text-primary">' + resConsArm[j].cant_capturas + "</td>";
                        tableResulArm += '<td class="text-primary">' + resConsArm[j].rat_capturas + "</td>";
                        tableResulArm += '<td class="text-primary">' + resConsArm[j].rat_bajas + "</td>";
                        tableResulArm += "</tr>";
                    }

                    /**================================================================================================
                     * !            RESULTADOS SOCIAL
                     *================================================================================================**/
                    var resConsSocial = data.output.social;
                    var tableResulSocial = "";
                    for (var j in resConsSocial) {
                        tableResulSocial += "<tr>";
                        tableResulSocial += '<td class="text-primary">' + resConsSocial[j].tipo + "</td>";
                        tableResulSocial += '<td class="text-primary">' + resConsSocial[j].cantidad + "</td>";
                        tableResulSocial += "</tr>";
                    }

                    /**================================================================================================
                     * !            RESULTADOS ECONOMICO
                     *================================================================================================**/
                    var resConsEconomico = data.output.economico;
                    var tableResulEconomico = "";
                    for (var j in resConsEconomico) {
                        tableResulEconomico += "<tr>";
                        tableResulEconomico += '<td class="text-primary">' + resConsEconomico[j].tipo + "</td>";
                        tableResulEconomico += '<td class="text-primary">' + resConsEconomico[j].cantidad + "</td>";
                        tableResulEconomico += "</tr>";
                    }

                    $("#tbodyResultadosArmado").empty().append(tableResulArm);
                    $("#tbodyResultadosSocial").empty().append(tableResulSocial);
                    $("#tbodyResultadosEconomico").empty().append(tableResulEconomico);

                    /* dataInfo.resConsEcoActualizacion = resConsEconomico;
                     dataInfo.resConsSocActualizacion = resConsSocial;
                     dataInfo.resConsArmActualizacion = resConsArm;
                     RESULTADOS.calcularCantidades();*/

                    /**================================================================================================
                     * !            RESULTADOS FINALES DE LOS FACTORES
                     *================================================================================================**/
                    var resultadoFinalArmado = data.output.armadoResultadoFinal;
                    var tableArmadoResultadoFinal = "";
                    for (var j in resultadoFinalArmado) {
                        tableArmadoResultadoFinal += "<tr>";
                        tableArmadoResultadoFinal += '<td class="text-primary">' + resultadoFinalArmado[j].nombre + "</td>";
                        tableArmadoResultadoFinal += '<td class="text-primary">' + resultadoFinalArmado[j].resultado + "</td>";
                        tableArmadoResultadoFinal += "</tr>";
                    }
                    $("#tbodyResultadosArmadoActual").empty().append(tableArmadoResultadoFinal);

                    var resultadoFinalSocial = data.output.socialResultadoFinal;
                    var tableSocialResultadoFinal = "";
                    for (var j in resultadoFinalSocial) {
                        tableSocialResultadoFinal += "<tr>";
                        tableSocialResultadoFinal += '<td class="text-primary">' + resultadoFinalSocial[j].tipo + "</td>";
                        tableSocialResultadoFinal += '<td class="text-primary">' + resultadoFinalSocial[j].resultado + "</td>";
                        tableSocialResultadoFinal += "</tr>";
                    }
                    $("#tbodyResultadosSocialActual").empty().append(tableSocialResultadoFinal);

                    var resultadoFinalEconomico = data.output.economicoResultadoFinal;
                    var tableEconomicoResultadoFinal = "";
                    for (var j in resultadoFinalEconomico) {
                        tableEconomicoResultadoFinal += "<tr>";
                        tableEconomicoResultadoFinal += '<td class="text-primary">' + resultadoFinalEconomico[j].tipo + "</td>";
                        tableEconomicoResultadoFinal += '<td class="text-primary">' + resultadoFinalEconomico[j].resultado + "</td>";
                        tableEconomicoResultadoFinal += "</tr>";
                    }
                    $("#tbodyResultadosEconomicoActual").empty().append(tableEconomicoResultadoFinal);

                } else {
                    UTIL.mostrarMensajeError(data.output.response.content);
                    $("#tbodyResultadosArmado").empty().append("");
                    $("#tbodyResultadosSocial").empty().append("");
                    $("#tbodyResultadosEconomico").empty().append("");
                }
            }
        });
    },
    /**
     * Metodo para cruzar la informnacíon ingresada con la informacion actualizada de cada inestabilidad
     */
    calcularCantidades: function() {
        // Calculos para el factor social
        var tableSocialActual = "";
        for (var i in dataInfo.resConsSoc) {

            var cantidad = 0;
            var id = dataInfo.resConsSoc[i]['id'];
            var factor = dataInfo.resConsSoc[i]['tipo'];

            for (var j in dataInfo.resConsSocActualizacion) {
                if (id == dataInfo.resConsSocActualizacion[j]['tbl_social_id']) {
                    cantidad = parseFloat(dataInfo.resConsSoc[i]['cantidad']) - parseFloat(dataInfo.resConsSocActualizacion[j]['cantidad'])
                }
            }
            if (cantidad > 0) {
                tableSocialActual += "<tr>";
                tableSocialActual += '<td class="text-primary">' + factor + "</td>";
                tableSocialActual += '<td class="text-primary">' + cantidad + "</td>";
                tableSocialActual += "</tr>";
            }
        }
        $("#tbodyResultadosSocialActual").empty().append(tableSocialActual);

        // Calculos para el factor economico
        var tableEconoActual = "";
        for (var i in dataInfo.resConsEco) {

            var cantidad = 0;
            var id = dataInfo.resConsEco[i]['id'];
            var factor = dataInfo.resConsEco[i]['tipo'];

            for (var j in dataInfo.resConsEcoActualizacion) {
                if (id == dataInfo.resConsEcoActualizacion[j]['tbl_economico_id']) {
                    cantidad = parseFloat(dataInfo.resConsEco[i]['cantidad']) - parseFloat(dataInfo.resConsEcoActualizacion[j]['cantidad'])
                }
            }
            if (cantidad > 0) {
                tableEconoActual += "<tr>";
                tableEconoActual += '<td class="text-primary">' + factor + "</td>";
                tableEconoActual += '<td class="text-primary">' + cantidad + "</td>";
                tableEconoActual += "</tr>";
            }
        }
        $("#tbodyResultadosEconomicoActual").empty().append(tableEconoActual);
    }
}