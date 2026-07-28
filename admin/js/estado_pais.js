$(document).on('ready', init);
var q;

function init() {
    q = {};
}

var ESTADO_PAIS = {
    /**
     * Metodo para obtener la dat de la brigada
     * @param {*} sigla 
     */
    getDataBatallonBySigla: function(id) {
        q = {};
        q.op = "get_pais";
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
                                       if (res.length > 0) {
                        var urlSelect = "admin/mapa_colombia/" + "/mapa.php";
                        $.post("admin/mapa_colombia/get_mapa.php", { url: urlSelect, options }, function(response, textStatus, xhr) {
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


$("body").on('click', 'img.colombiaClick', function(event) {
    var currentLocation = window.location.href;
    var url = currentLocation.replace('estado_pais.php', 'estado_general.php');
    var id_depto = $(this).data("depto");
    url = url + "?depto_id="+id_depto;
    location.href = url;
});