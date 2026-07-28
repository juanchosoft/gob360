$(document).on('ready', init);
var q;

function init() {
    q = {};
}


var return_page = 'detalle_proyectos.php';
var DETALLE_PROYECTO = {
    edit: function(id) {
        q = {};
        q.op = "pms_getproyerctos";
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
            $("#date").val(res.date);
            $("#provincia").val(res.provincia);
            $("#valor_proyecto").val(res.valor_proyecto);
            $("#plazo_construccion").val(res.plazo_construccion);
            $("#contratista").val(res.contratista);
            $("#interventoria").val(res.interventoria);
            $("#fecha_entrega").val(res.fecha_entrega);
            $("#estado").val(res.estado);
            $("#observaciones").val(res.observaciones);
            $("#porcentaje_ejecucion").val(res.porcentaje_ejecucion);
            $("#dias_prorroga").val(res.dias_prorroga);
            $("#date_prorroga").val(res.date_prorroga);
            $("#tbl_departamento_id").val(res.tbl_departamento_id);
            $("#tbl_municipio_id").val(res.tbl_municipio_id);
            $("#date_inicio").val(res.date_inicio);
            $("#adicion").val(res.adicion);           


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
        q.op = "ingresoproyectos_save";
        q.id = $("#idProyecto").val();
        q.contratista = $("#contratista").val();
        q.interventoria = $("#interventoria").val();
        q.plazo_construccion = $("#plazo_construccion").val();
        q.fecha_entrega = $("#fecha_entrega").val();
        q.estado = $("#estado").val();
        q.porcentaje_ejecucion = $("#porcentaje_ejecucion").val();
        q.dias_prorroga = $("#dias_prorroga").val();
        q.date_prorroga = $("#date_prorroga").val();
        q.adicion = $("#adicion").val();
        q.observaciones = $("#observaciones").val();

        q.aporte_municipio = $("#aporte_municipio").val();
        q.aporte_gobernacion = $("#aporte_gobernacion").val();
        q.aporte_nacion = $("#aporte_nacion").val();
        q.porcentaje_financiero = $("#porcentaje_financiero").val();
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
                        window.location = 'proyecto_x_secretaria.php';
                    }, 1500);
                } else {
                    UTIL.mostrarMensajeError(data.output.response.content);
                }
            },
        });
    }
};