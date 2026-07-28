$(document).on('ready', init);
var q;

function init() {
    q = {};
}

var ESTADO_BATALLON = {
    /**
     * Metodo para obtener la dat de la brigada
     * @param {*} sigla 
     */
    getDataBatallonBySigla: function(id) {
        q = {};
        q.op = "get_munic_x_batallon";
        q.id = id;
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
                    var res = data.output.response;
                    var porcentaje = data.output.porcentaje;
                    var porcentajeDesplazamiento = data.output.porcentajeDesplazamiento;

                    $("#estable").empty().append(data.output.estable);
                    $("#critico").empty().append(data.output.critico);
                    $("#bajo").empty().append(data.output.bajo);
                    $("#medio").empty().append(data.output.medio);
                    $("#alto").empty().append(data.output.alto);

                    $("#estableEstado").empty().append(data.output.estableEstado);
                    $("#criticoEstado").empty().append(data.output.criticoEstado);
                    $("#bajoEstado").empty().append(data.output.bajoEstado);
                    $("#medioEstado").empty().append(data.output.medioEstado);
                    $("#altoEstado").empty().append(data.output.altoEstado);

                    var divPorcentaje = '<div class="progress-bar progress-bar-warning '+porcentaje+'" style="width: ' + porcentaje + '%"></div>';
                    $("#estado_ocupacion").empty().append(porcentaje);
                    $("#porcentajeDiv").empty().append(divPorcentaje);

                    var divDesplazamiento = '<div class="progress-bar progress-bar-warning '+porcentajeDesplazamiento+'" style="width: ' + porcentajeDesplazamiento + '%"></div>';
                    $("#estado_desplazamiento").empty().append(porcentajeDesplazamiento);
                    $("#desplazamientoDiv").empty().append(divDesplazamiento);

                    options.response = res;
                    options.cantidades = data.output.cantidadesPorDivision;
                    options.cantidades2021 = data.output.cantidadesPorDivision_2021;
                    options.veredas_municipio_x_batallon = data.output.veredas_municipio_x_batallon;
                    var sigla = "";
                    if (res.length > 0) {
                        sigla = res[0]['sigla'];
                        var urlSelect = "admin/mapa_batallones/" + sigla + "/mapa.php";
                        $.post("admin/mapa_batallones/get_mapa.php", { url: urlSelect, options }, function(response, textStatus, xhr) {
                            $("#mapaDataBody").html(response);
                            setTimeout(function() {
                                $("img").each(function(index, el) {
                                    $(this).attr("data-bs-toggle", "tooltip");
                                    $(this).attr("data-bs-placement", "left");
                                    tooltip = new bootstrap.Tooltip($(this)[0], {})
                                });
                            }, 2000);
                        });
                    }
                } else {
                    UTIL.mostrarMensajeError(data.output.response.content);
                }
            },
        });
    },

    showData: function(color, id) {
        q = {};
        q.op = "getveredasbycolorbatallon";
        q.tbl_batallon_id = id;
        q.color = color;
        UTIL.callAjaxRqstPOST(q, this.showDataHandler);
    },
    showData2021: function(color, id) {
        q = {};
        q.op = "getveredasbycolorbatallon2021";
        q.tbl_batallon_id = id;
        q.color = color;
        UTIL.callAjaxRqstPOST(q, this.showDataHandler);
    },
    showDataHandler: function(data) {
        UTIL.cursorNormal();
        if (data.output.valid) {
            var res = data.output.response;
            var table = "";
            for (var j in res) {
                table += "<tr>";
                table += '<td class="text-primary">' + res[j].brigada + "</td>";
                table += '<td class="text-primary">' + res[j].batallon + "</td>";
                table += '<td class="text-primary">' + res[j].departamento + "</td>";
                table += '<td class="text-primary">' + res[j].municipio + "</td>";
                table += '<td class="text-primary">' + res[j].nombre_vereda + "</td>";
                table += "</tr>";
            }
            $("#tablaVeredasColores")
                .empty()
                .append(table);
        }
    },

};