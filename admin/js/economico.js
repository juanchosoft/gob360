$(document).on('ready', init);
var q;
/**
 * se activa para inicializar el documento
 */
function init() {
    q = {};
}

var return_page = 'economicos.php';
var ECONOMICO = {
    editData: function(id) {
        q = {};
        q.op = "economicoget";
        q.id = id;
        UTIL.callAjaxRqstPOST(q, this.editdaHandler);
    },
    editdaHandler: function(data) {
        UTIL.cursorNormal();
        if (data.output.valid) {
            var res = data.output.response[0];
            $("#id").val(res.id);
            $("#nombre").val(res.nombre);
            $("#tipo").val(res.tipo);
            $("#puntaje").val(res.puntaje);
            $("#porcentaje").val(res.porcentaje);
        } else {
            Swal.fire({
                icon: 'warning',
                title: 'Por favor revise',
                text: data.output.response.content
            });
        }
    },
    validateData: function() {
        var bValid = true;
        var msj = "Falta ingresar información obligatoria, marcada con asterisco.";
        if (
            $("#nombre").val() == "" ||
            $("#tipo").val() == "" ||
            $("#puntaje").val() == "" ||
            $("#porcenteje").val() == ""
        ) {
            Swal.fire({
                icon: 'warning',
                title: 'Revise bien por favor',
                text: msj
            });
            bValid = false;
            return;
        }
        if (bValid) {
            ECONOMICO.savedata();
        }
    },
    savedata: function() {
        q = {};
        q.op = "economicosave";
        q.id = $("#id").val();
        q.nombre = $("#nombre").val();
        q.tipo = $("#tipo").val();
        q.puntaje = $("#puntaje").val();
        q.porcentaje = $("#porcentaje").val();
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
    }
};