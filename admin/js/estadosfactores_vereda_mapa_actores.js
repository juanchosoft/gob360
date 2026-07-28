$(document).on("ready", init);
var q;

function init() {
    q = {};
}

var ESTADO_MAPA_ACTORES = {
    buildTableActores: function(data, mapa_actores_asignados) {
        var data = data;
        var table = "";
        var actors = ["USAID", "MAPOEA", "ONU", "DH", "EPM", "MINEROS SA", "EMIJON SAS", "CLARO", "SUBACAUCA", "COLANTA", "FUTURASEO", "INVIMA", "EJERCITO", "MINISTERIOS", "ALCALDIA", "AGENCIA DR", "PROSPERIDAD SOCIAL", "CORANTIOQUIA", "UAEOS", "ASAPIBAS", "PONAL", "GOBERNACION", "RTT", "ANT", "ICBF", "DANE", "SENA"];
        for (var j in data) {
            var tipo = data[j].tipo;
            var actual = data[j].actual;
            var tbl_social_id = data[j].tbl_social_id;
            if (actual > 0) {
                table += "<tr>";
                table += '<td>' + tipo + "</td>";
                table += '<td>' + actual + "</td>";
                for (var i in actors) {
                    var id = tbl_social_id + "_" + actors[i];
                    table += '<td><div class="icheck-primary d-inline"><input onchange="ESTADO_MAPA_ACTORES.save(this)" type="checkbox"value="' + id + '" name="chk' + id + '" id="chk' + id + '"><label for="chk' + id + '"></label></div></td>';
                }
                table += "</tr>";
            }
        }
        $("#tableActores").empty().append(table);

        // Se verifica si hay asignaciones previas
        for (var j in mapa_actores_asignados) {
            var idchk = "chk" + mapa_actores_asignados[j].tbl_social_id + "_" + mapa_actores_asignados[j].actor;
            $('#' + idchk)[0].checked = true;
        }
    },
    buildTableActoresVersion_2: function(dataIngresoActores, actores_mapa, asignados) {
        var data = dataIngresoActores;
        var table = "";
        for (var j in data) {
            var tipo = data[j].tipo;
            var actual = data[j].actual;
            var tbl_social_id = data[j].tbl_social_id;
            if (actual > 0) {
                table += "<tr>";
                table += '<td>' + tipo + "</td>";
                table += '<td>' + actual + "</td>";
                for (var i in actores_mapa) {
                    var id = tbl_social_id + "_" + actores_mapa[i].actor_id;
                    table += '<td><div class="icheck-primary d-inline"><input onchange="ESTADO_MAPA_ACTORES.save(this)" type="checkbox"value="' + id + '" name="chk' + id + '" id="chk' + id + '"><label for="chk' + id + '"></label></div></td>';
                }
                table += "</tr>";
                var th = "";
                th += '<th>CARENCIA</th>';
                th += '<th>CANTIDAD</th>';
                for (var i in actores_mapa) {
                    th += '<th>' + actores_mapa[i].nombre + '</th>';
                }
                $("#trActores").empty().append(th);
            }

        }
        $("#tableActoresV2").empty().append(table);

        // Asignados
        for (var i in asignados) {
            var id = asignados[i].tbl_social_id + "_" + asignados[i].actor_id;
            var idchk = "chk" + id;
            $("#" + idchk)[0].checked = true;
        }
    },
    save: function(data) {
        q = {};
        q.op = 'actores_save';
        q.municipio_id = $("#tbl_municipio_id").val();
        q.vereda_id = $("#tbl_vereda_id").val();
        q.departamento_id = $("#tbl_departamento_id").val();
        const myArr = data.value.split("_");
        q.tbl_social_id = myArr[0];
        q.actor = myArr[1];
        q.accion = $(data).is(":checked") ? 'save' : 'delete';
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
                        title: 'Operación ejecutada',
                        showConfirmButton: false,
                        timer: 1000
                    });
                } else {
                    UTIL.mostrarMensajeError(data.output.response.content);
                }
            },
        });
    }
};