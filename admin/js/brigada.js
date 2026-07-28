$(document).on('ready', init);
var q;
/**
 * se activa para inicializar el documento
 */
function init() {
    q = {};
}

var return_page = 'brigadas.php';
var BRIGADA = {
    editData: function(id) {
        q = {};
        q.op = "brigadaget";
        q.id = id;
        UTIL.callAjaxRqstPOST(q, this.editdaHandler);
    },
    editdaHandler: function(data) {
        UTIL.cursorNormal();
        if (data.output.valid) {
            var res = data.output.response[0];
            $("#id").val(res.id);
            $("#nombre").val(res.nombre);
            $("#sigla").val(res.sigla);
            $("#ubicacion").val(res.ubicacion);
            $("#direccion").val(res.direccion);
            $("#telefono").val(res.telefono);
            $("#email").val(res.email);
            $("#responsable").val(res.responsable);
            $("#comandante").val(res.comandante);
        } else {
            UTIL.mostrarMensajeError(data.output.response.content);
        }
    },
    validateData: function() {
        var bValid = true;
        var msj = "Falta ingresar información obligatoria, marcada con asterisco.";
        if (
            $("#nombre").val() == "" ||
            $("#sigla").val() == "" ||
            $("#ubicacion").val() == "" ||
            $("#direccion").val() == "" ||
            $("#telefono").val() == "" ||
            $("#email").val() == "" ||
            $("#responsable").val() == "" ||
            $("#comandante").val() == ""
        ) {
            UTIL.mostrarMensajeValidacion(msj);
            bValid = false;
            return;
        }
        if (bValid) {
            BRIGADA.savedata();
        }
    },
    savedata: function() {
        q = {};
        q.op = "brigadasave";
        q.id = $("#id").val();
        q.nombre = $("#nombre").val();
        q.sigla = $("#sigla").val();
        q.ubicacion = $("#ubicacion").val();
        q.direccion = $("#direccion").val();
        q.telefono = $("#telefono").val();
        q.email = $("#email").val();
        q.categoria_id = $("#categoria_id").val();
        q.responsable = $("#responsable").val();
        q.comandante = $("#comandante").val();
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
                        window.location = return_page;
                    }, 1500);
                } else {
                    UTIL.mostrarMensajeError(data.output.response.content);
                }
            },
        });
    },
    showVeredasSinDesplazamientoBrigada: function(id, tipo) {
        q = {};
        q.op = "get_veredas_sin_desplazamiento_brigadas";
        q.id = id;
        q.tipo = tipo;
        UTIL.callAjaxRqstPOST(q, this.showVeredasSinDesplazamientoHandler);
    },
    showVeredasSinDesplazamientoHandler: function(data) {
        UTIL.cursorNormal();
        if (data.output.valid) {
            var res = data.output.response;
            console.log(res);
            var table = "";
            for (var j in res) {
                table += "<tr>";
                table += '<td class="text-primary">' + res[j].nombre_puesto + "</td>";
                table += '<td class="text-primary">' + res[j].porcentaje + "%</td>";
                table += '<td class="barcel" style="width: 50% !important;">';
                table += '<div class="progress progress-xs">';
                table += '<div class="progress-bar progress-bar-warning ' + res[j].porcentaje + '" style="width : ' + res[j].porcentaje + '%" ></div>';
                table += '</div>';
                table += '</td>';
                table += "</tr>";
            }
            $("#tablaVeredas")
                .empty()
                .append(table);
        }
    },
};