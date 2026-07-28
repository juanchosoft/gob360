$(document).on('ready', init);
var q;

function init() {
    q = {};
}

var return_page = 'seguridad.php';
var ARMADA = {
    editData: function(id) {
        q = {};
        q.op = "armadaget";
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
            $("#comision").val(res.comision);
            $("#hombres").val(res.hombres);
            $("#frente").val(res.frente);
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
            $("#tipo").val() == ""


        ) {
            UTIL.mostrarMensajeValidacion(msj);
            bValid = false;
            return;
        }
        if (bValid) {
            ARMADA.savedata();
        }
    },
    savedata: function() {
        q = {};
        q.op = "armadasave";
        q.id = $("#id").val();
        q.nombre = $("#nombre").val();
        q.tipo = $("#tipo").val();
        q.puntaje = $("#puntaje").val();
        q.comision = $("#comision").val();
        q.hombres = $("#hombres").val();
        q.porcentaje = $("#porcentaje").val();
        q.frente = $("#frente").val();
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