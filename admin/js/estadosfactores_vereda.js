$(document).on("ready", init);
var q;

function init() {
    q = {};
}

var ESTADO = {
    getFactores: function() {
        if ($("#filtroFechas").val() === "si") {
            if ($("#fecha_inicio").val() == "" || $("#fecha_fin").val() == "") {
                UTIL.mostrarMensajeValidacion("Debes ingresar un rango de fechas");
                return;
            }
            if ($("#fecha_inicio").val() > $("#fecha_fin").val()) {
                UTIL.mostrarMensajeValidacion(
                    "La fecha inicial no puede ser mayor a la final"
                );
                return;
            }
        }
        q = {};
        if ($("#filtro").val() === "vereda") {
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
        if ($("#filtroFechas").val() === "si") {
            q.filtro_fechas = "si";
            q.fecha_inicio = $("#fecha_inicio").val();
            q.fecha_fin = $("#fecha_fin").val();
        }

        var options = q;
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
                    var puntaje = data.output.puntaje;
                    var color = data.output.color;
                    var batallon = data.output.batallon;
                    var brigada = data.output.brigada;
                    var actores = data.output.actores;
                    var observaciones = data.output.observaciones != "" ? data.output.observaciones : "";

                    $("#observaciones").empty().append(observaciones);
                    $("#brigada").empty().append(brigada);
                    $("#batallon").empty().append(batallon);

                    // Puntaje de cada Factor Economico
                    $("#puntajeEconomico").empty().append(data.output.puntajeEconomico);
                    $("#puntajeArmado").empty().append(data.output.puntajeArmado);
                    $("#puntajeSocial").empty().append(data.output.puntajeSocial);

                    /**================================================================================================
                     * !                                        PUNTAJES Y COLOR
                     *================================================================================================**/

                    $("#puntajeSpan").empty().append(puntaje);
                    $("#colorSpan").empty().append(color);

                    document.getElementById("divPuntaje").style.backgroundColor = color;

                    var urlSelect = $("#tbl_municipio_id option:selected").data("mapa");
                    if (typeof urlSelect != "undefined" && urlSelect != "") {
                        q.puntaje = puntaje;
                        $.post(
                            "admin/mapa-veredas/get_mapa.php", { url: urlSelect, puntaje, color, options },
                            function(response, textStatus, xhr) {
                                $("#mapaDataBody").html(response);
                                $(".tooltip").hide();
                                setTimeout(function() {
                                    $("img").each(function(index, el) {
                                        $(this).attr("data-bs-toggle", "tooltip");
                                        $(this).attr("data-bs-placement", "left");
                                        tooltip = new bootstrap.Tooltip($(this)[0], {});
                                    });
                                }, 1000);
                            }
                        );
                    } else {
                        $("#mapaDataBody").html(
                            "<h2 class='text-center text-danger'> NO SE REGISTRA MAPA </h2>"
                        );
                    }

                    /**================================================================================================
                     * !            RESULTADOS FINALES DE LOS FACTORES
                     *================================================================================================**/
                    var resultadoFinalArmado = data.output.armadoResultadoFinal;
                    var tableArmadoResultadoFinal = "";
                    for (var j in resultadoFinalArmado) {
                        if (resultadoFinalArmado[j].actual > 0) {
                            tableArmadoResultadoFinal += "<tr>";
                            tableArmadoResultadoFinal +=
                                '<td class="text-primary">' +
                                resultadoFinalArmado[j].nombre +
                                "</td>";
                            /*                         tableArmadoResultadoFinal +=
                                                        '<td class="text-primary">' +
                                                        resultadoFinalArmado[j].cantidad_rat_capturas +
                                                        "</td>";
                                                    tableArmadoResultadoFinal +=
                                                        '<td class="text-primary">' +
                                                        resultadoFinalArmado[j].cantidad_rat_bajas +
                                                        "</td>"; */
                            tableArmadoResultadoFinal +=
                                '<td class="text-primary">' +
                                resultadoFinalArmado[j].frente +
                                "</td>";
                                                    //  tableArmadoResultadoFinal +=
                                                    //     '<td class="text-primary">' +
                                                    //     resultadoFinalArmado[j].anterior +
                                                    //     "</td>"; 
                            tableArmadoResultadoFinal +=
                                '<td class="text-primary">' +
                                resultadoFinalArmado[j].actual +
                                "</td>";

                            tableArmadoResultadoFinal += "</tr>";
                        }
                    }
                    $("#tbodyConsolidadoArmadoFINAL")
                        .empty()
                        .append(tableArmadoResultadoFinal);

                    var resultadoFinalSocial = data.output.socialResultadoFinal;
                    var tableSocialResultadoFinal = "";
                    for (var j in resultadoFinalSocial) {
                        if (resultadoFinalSocial[j].actual > 0) {
                            tableSocialResultadoFinal += "<tr>";
                            tableSocialResultadoFinal +=
                                '<td class="text-primary">' +
                                resultadoFinalSocial[j].tipo +
                                "</td>";
                                                    //  tableSocialResultadoFinal +=
                                                    //     '<td class="text-primary">' +
                                                    //     resultadoFinalSocial[j].anterior +
                                                    //     "</td>"; 
                            tableSocialResultadoFinal +=
                                '<td class="text-primary">' +
                                resultadoFinalSocial[j].actual +
                                "</td>";

                            tableSocialResultadoFinal += "</tr>";
                        }
                    }
                    $("#tbodyConsolidadoSocialFINAL")
                        .empty()
                        .append(tableSocialResultadoFinal);




                    var resultadoFinalEconomico = data.output.economicoResultadoFinal;
                    var tableEconomicoResultadoFinal = "";
                    for (var j in resultadoFinalEconomico) {
                        if (resultadoFinalEconomico[j].actual > 0) {
                            tableEconomicoResultadoFinal += "<tr>";
                            tableEconomicoResultadoFinal +=
                                '<td class="text-primary">' +
                                resultadoFinalEconomico[j].tipo +
                                "</td>";
                                                    //  tableEconomicoResultadoFinal +=
                                                    //     '<td class="text-primary">' +
                                                    //     resultadoFinalEconomico[j].anterior +
                                                    //     "</td>";
                            tableEconomicoResultadoFinal +=
                                '<td class="text-primary">' +
                                resultadoFinalEconomico[j].actual +
                                "</td>";
                            tableEconomicoResultadoFinal += "</tr>";
                        }
                    }


                    $("#tbodyConsolidadoEconFINAL")
                        .empty()
                        .append(tableEconomicoResultadoFinal);
                    /**================================================================================================
                     * !           FIN RESULTADOS FINALES DE LOS FACTORES
                     *================================================================================================**/

                    /**================================================================================================
                     * !           RAT ARMADO
                     *================================================================================================**/
                    var resultadoRAT = data.output.arrFinalArmadoByMunicipioRAT;
                    var tableRATResultadoFinal = "";
                    if (resultadoRAT) {
                        for (var j in resultadoRAT) {
                            tableRATResultadoFinal += "<tr>";
                            tableRATResultadoFinal +=
                                '<td class="text-primary">' +
                                resultadoRAT[j].SumaDecantidad_rat_capturas +
                                "</td>";
                            tableRATResultadoFinal +=
                                '<td class="text-primary">' +
                                resultadoRAT[j].SumaDecantidad_rat_bajas +
                                "</td>";
                            tableRATResultadoFinal += "</tr>";
                        }
                        $("#tbodyResultadosArmadoActualRAT")
                            .empty()
                            .append(tableRATResultadoFinal);
                    }

                    /**================================================================================================
                     * !                                        ACTORES
                     *================================================================================================**/
                    var resultActores = data.output.actores;
                    var tableAtores = "";
                    if (resultActores) {
                        for (var j in resultActores) {
                            tableAtores += "<tr>";
                            tableAtores +=
                                '<td class="text-primary">' +
                                resultActores[j].nombre_vereda +
                                "</td>";
                            tableAtores +=
                                '<td class="text-primary">' +
                                resultActores[j].actor +
                                "</td>";
                            tableAtores +=
                                '<td class="text-primary">' +
                                resultActores[j].tipo +
                                "</td>";
                            tableAtores +=
                                '<td class="text-primary">' +
                                resultActores[j].nombre +
                                "</td>";
                            tableAtores += "</tr>";
                        }
                        $("#tbodyActores")
                            .empty()
                            .append(tableAtores);
                    }

                    /**================================================================================================
                     * !            SE CONSTRUYE TABLA DE ACTORES PARA CHECK LA INFORMACION
                     *================================================================================================**/
                    //ESTADO_MAPA_ACTORES.buildTableActores(resultadoFinalSocial, data.output.mapa_actores_asignados);
                    ESTADO_MAPA_ACTORES.buildTableActoresVersion_2(resultadoFinalSocial, data.output.actoresDinamicos, data.output.mapa_actores_asignados);

                } else {
                    //UTIL.mostrarMensajeError(data.output.response.content);
                    // $("#tableFacArmado").empty().append("");
                    // $("#tableFacSocial").empty().append("");
                    // $("#tableFacEconomico").empty().append("");

                    // $("#tbodyConsolidadoEcon").empty().append("");
                    // $("#tbodyConsolidadoSocial").empty().append("");
                    // $("#tbodyConsolidadoArmado").empty().append("");

                    $("#tbodyConsolidadoArmadoFINAL").empty().append("");
                    $("#tbodyConsolidadoSocialFINAL").empty().append("");
                    $("#tbodyConsolidadoEconFINAL").empty().append("");
                    $("#tbodyResultadosArmadoActualRAT").empty().append("");
                }
            },
        });
    },
    updateDescripcion: function() {
        q.op = "upd_descrip_vereda";
        q.codigo_departamento = $("#tbl_departamento_id").val();
        q.codigo_muncipio = $("#tbl_municipio_id").val();
        q.nombre_vereda = $("#tbl_vereda_id").val();
        q.observaciones = $("#observaciones").val();
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
                    Swal.fire({
                        position: 'top-end',
                        icon: 'success',
                        title: 'Observación Actualizada',
                        showConfirmButton: false,
                        timer: 1000
                    });
                }
            },
        });
    }
};
