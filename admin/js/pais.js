$(document).on('ready', init);
var q;

function init() {
    q = {};
}

var ESTADO_BRIGADA = {
    /**
     * Metodo para obtener la dat de la brigada
     * @param {*} sigla 
     */
    getDataBrigadaBySigla: function(id) {
        q = {};
        q.op = "get_munic_x_brigadas";
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
                    options.response = res;
                    options.cantidades = data.output.cantidadesPorDivision;
                    options.cantidades2021 = data.output.cantidadesPorDivision2021;
                    options.veredas_municipio_x_brigada = data.output.veredas_municipio_x_brigada;

                    options.bajo = data.output.bajo;
                    options.bajoEstado = data.output.bajoEstado;

                    options.medio = data.output.medio;
                    options.medioEstado = data.output.medioEstado;

                    options.alto = data.output.alto;
                    options.altoEstado = data.output.altoEstado;

                    options.critico = data.output.critico;
                    options.criticoEstado = data.output.criticoEstado;

                    options.estable = data.output.estable;
                    options.estableEstado = data.output.estableEstado;

                    var sigla = "";
                    if (res.length > 0) {
                        sigla = res[0]['sigla'];
                        var urlSelect = "admin/mapa-colombia/" + sigla + "/colombia.php";
                        $.post("admin/mapa-colombia/get_mapa.php", {
                            url: urlSelect,
                            options
                        }, function(response, textStatus, xhr) {
                            $("#mapaDataBody").html(response);
                            setTimeout(function() {
                                $("img").each(function(index, el) {
                                    $(this).attr("data-bs-toggle", "tooltip");
                                    $(this).attr("data-bs-placement", "left");
                                    tooltip = new bootstrap.Tooltip($(this)[0], {})
                                });
                            }, 2000);

                            setTimeout(function() {

                                ESTADO_BRIGADA.showTableVeredasColor(options);

                                $("#estable").empty().append(options.estable);
                                $("#critico").empty().append(options.critico);
                                $("#bajo").empty().append(options.bajo);
                                $("#medio").empty().append(options.medio);
                                $("#alto").empty().append(options.alto);

                                $("#estableEstado").empty().append(options.estableEstado);
                                $("#criticoEstado").empty().append(options.criticoEstado);
                                $("#bajoEstado").empty().append(options.bajoEstado);
                                $("#medioEstado").empty().append(options.medioEstado);
                                $("#altoEstado").empty().append(options.altoEstado);
                            }, 2000);
                        });
                    }
                } else {
                    UTIL.mostrarMensajeError(data.output.response.content);
                }
            },
        });
    },
    showTableVeredasColor: function(data) {
        // ESTADOS VEREDAS POR COLOR 2021
        var table = "";
        var res = data.cantidades2021;
        for (var j in res) {
            var color2021 = res[j].color2021;
            var brigada = res[j].tbl_brigada_id;
            var cuenta = res[j].cuenta;
            if (color2021 != "" && cuenta > 0) {
                let click = `ESTADO_BRIGADA.showData2021('${color2021}', '${brigada}')`;
                table += "<tr>";
                table += '<th><span class="elementcolor" style="background-color: ' + color2021 + ' "></span>' + cuenta + " VEREDAS</th>";
                table += '<th> <a href="#" onclick="' + click + '" role="button" data-target="#dato_veredas" class="btn btn-xs  btn-primary btn-w-100p btn-mw-300" data-toggle="modal">Ver</a></th>';
                table += "</tr>";
            }
        }
        $("#divTable2021")
            .empty()
            .append(table);

        // ESTADOS VEREDAS POR COLOR Actual 2022
        var table = "";
        var res2022 = data.cantidades;
        for (var j in res2022) {
            var color2022 = res2022[j].color;
            var brigada = res2022[j].tbl_brigada_id;
            var cuenta = res2022[j].cuenta;
            if (color2022 != "" && cuenta > 0) {
                let click = `ESTADO_BRIGADA.showData('${color2022}', '${brigada}')`;
                table += "<tr>";
                table += '<th><span class="elementcolor" style="background-color: ' + color2022 + ' "></span>' + cuenta + " VEREDAS</th>";
                table += '<th> <a href="#" onclick="' + click + '" role="button" data-target="#dato_veredas" class="btn btn-xs  btn-primary btn-w-100p btn-mw-300" data-toggle="modal">Ver</a></th>';
                table += "</tr>";
            }
        }
        $("#divTable2022")
            .empty()
            .append(table);
    },
    showData: function(color, id) {
        q = {};
        q.op = "getveredasbycolor";
        q.tbl_brigada_id = id;
        q.color = color;
        UTIL.callAjaxRqstPOST(q, this.showDataHandler);
    },
    showData2021: function(color, id) {
        q = {};
        q.op = "getveredasbycolorbrigada2021";
        q.tbl_brigada_id = id;
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
