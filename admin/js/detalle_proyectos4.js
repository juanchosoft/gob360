$(document).on('ready', init);
var q;

function init() {
    q = {};
}

var return_page = 'proyectos4.php';
var DETALLE_PROYECTO4 = {
    edit: function(id) {
        q = {};
        q.op = "pms_getproyerctos4";
        q.id = id;
        UTIL.callAjaxRqstPOST(q, this.editHandler);
    },
    editHandler: function(data) {
        UTIL.cursorNormal();
        if (data.output.valid) {
            var res = data.output.response[0];
            var obs = data.output.observaciones;
            $("#id").val(res.id);
            $("#proyecto").val(res.proyecto);
            $("#batallon").val(res.batallon);
            $("#financiacion").val(res.financiacion);
            $("#objetivo_proyecto").val(res.objetivo_proyecto);
            $("#valor_proyecto").val(res.valor_proyecto);
            $("#plazo_construccion").val(res.plazo_construccion);
            $("#contratista").val(res.contratista);
            $("#interventoria").val(res.interventoria);
            $("#fecha_entrega").val(res.fecha_entrega);
            $("#estado").val(res.estado);
            $("#observaciones").val(res.observaciones);
            $("#porcentaje_ejecucion").val(res.porcentaje_ejecucion);


            var table = "";
            for (var j in obs) {
                table += "<tr>";
                table += '<td class="text-primary">' + obs[j].id + "</td>";
                table += '<td class="text-primary">' + obs[j].observaciones + "</td>";
                table += '<td class="text-primary">' + obs[j].dtcreate + "</td>";
                table += "</tr>";
            }
            $("#tablaObservaciones")
                .empty()
                .append(table);

        } else {
            UTIL.mostrarMensajeError(data.output.response.content);
        }
    },
    updatedata: function() {
        q = {};
        q.op = "ingresoproyectos4_save";
        q.id = $("#id").val();
        q.proyecto = $("#proyecto").val();
        q.objetivo_proyecto = $("#objetivo_proyecto").val();
        q.valor_proyecto = $("#valor_proyecto").val();
        q.plazo_construccion = $("#plazo_construccion").val();
        q.fecha_entrega = $("#fecha_entrega").val();
        q.estado = $("#estado").val();
        q.porcentaje_ejecucion = $("#porcentaje_ejecucion").val();
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
                    UTIL.mostrarMensajeExitoso('Información guardada correctamente');
                    setTimeout(function() {
                        window.location = 'proyectos_brigadas4.php';
                    }, 1500);
                } else {
                    UTIL.mostrarMensajeError(data.output.response.content);
                }
            },
        });
    }
};