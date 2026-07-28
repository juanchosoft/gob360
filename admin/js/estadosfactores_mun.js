$(document).on('ready', init);
var q;

function init() {
    q = {};
}

var ESTADO = {
    getFactores: function() {
        q = {};
        q.op = "getfactoresbymunicNUEVO";
        // q.op = "getfactoresbymunicVersion2022";
        q.codigo_departamento = $("#tbl_departamento_id").val();
        q.codigo_muncipio = $("#tbl_municipio_id").val();
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
                    var color = data.output.color;
                    var ciudad = data.output.ciudadInfo;
                    var ciudadApoyo = data.output.ciudadInfoApoyo;
                    var ciudadApoyoLideres = data.output.ciudadInfoLideres;

                    // Init variables
                    $("#hombres").empty().append("0");
                    $("#mujeres").empty().append("0");
                    $("#total").empty().append("0");
                    $("#apoyo").empty().append("0");
                    $("#lideres").empty().append("0");

                    //Poblacion Habilitada para votar ciudad
                    if (ciudad != null && ciudad.length > 0) {
                        $("#hombres").empty().append(ciudad[0]['hombres']);
                        $("#mujeres").empty().append(ciudad[0]['mujeres']);
                        $("#total").empty().append(ciudad[0]['total']);
                    }

                    // Totales Apoyo
                    if (ciudadApoyo != null && ciudadApoyo.length > 0) {
                        $("#apoyo").empty().append(ciudadApoyo[0]['cantidad']);
                        for ( var j in ciudadApoyo[0] ){

                            if ( j.indexOf('apoyo_') != -1 ) {
                                var label = j.replace("apoyo_", "");
                                $("#trLabelsInfo").append("<th>"+label.toUpperCase()+"</th>");
                                $("#trValuesInfo").append("<td>"+ciudadApoyo[0][j]+"</td>");
                            }

                        }
                        
                    }
                    // Totales Apoyo Lideres
                    if (ciudadApoyoLideres != null && ciudadApoyoLideres.length > 0) {
                        $("#lideres").empty().append(ciudadApoyoLideres[0]['cantidad']);
                    }


                  

                    // Totales Apoyo Lideres

                    /**================================================================================================
                     * !                                        PUNTAJES Y COLOR
                     *================================================================================================**/
                    // $("#puntajeSpan").empty().append(puntaje);
                    $("#colorSpan").empty().append(color);

                    document.getElementById("divPuntaje").style.backgroundColor = color;

                    var urlSelect = $("#tbl_municipio_id option:selected").data("mapa");

                    if (typeof urlSelect != "undefined" && urlSelect != "") {
                        $.post("admin/mapa-veredas/get_mapa.php", { url: urlSelect, color, options }, function(response, textStatus, xhr) {
                            $("#mapaDataBody").html(response);
                            setTimeout(function() {
                                $("img").each(function(index, el) {
                                    $(this).attr("data-bs-toggle", "tooltip");
                                    $(this).attr("data-bs-placement", "left");
                                    tooltip = new bootstrap.Tooltip($(this)[0], {})
                                });
                            }, 2000);
                        });
                    } else {
                        $("#mapaDataBody").html("<h2 class='text-center text-danger'> NO SE REGISTRA MAPA  </h2>");
                    }
                } else {
                    UTIL.mostrarMensajeError(data.output.response.content);
                }
            },
        });
    }
};

/**
 * Evento para direccionarlo a estado de la vereda con su puntaje e información
 */
$("body").on('click', ".veredaMun", function(event) {
    var dept = $(this).data("dept");
    var mun = $(this).data("mun");
    var vereda = $(this).data("id");

    location.href = "estado_vereda.php?mun=" + mun + "&vereda=" + vereda + "&dep=" + dept;
});