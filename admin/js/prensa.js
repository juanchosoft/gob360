$(document).on("ready", init);
var q;

function init() {
    q = {};
}
var PRENSA = {
    validateData: function() {
        var bValid = true;
        var msj = "Falta ingresar información obligatoria, marcada con asterisco.";
        if (
            $("#titulo").val() == "" ||
            $("#titulo").val() == null

        ) {
            UTIL.mostrarMensajeValidacion(msj);
            bValid = false;
            return;
        }
        if (bValid) {
            PRENSA.save();
        }
    },

    editData: function(id) {
        q = {};
        q.op = "getprensa";
        q.id = id;
        UTIL.callAjaxRqstPOST(q, this.editDataHandler);
    },
    editDataHandler: function(data) {
        UTIL.cursorNormal();
        if (data.output.valid) {
            var res = data.output.response[0];
            $("#id").val(res.id);
            $("#titulo").val(res.titulo);
            $("#descripcion").val(res.descripcion);
            $("#enable").val(res.enable);
        } else {
            UTIL.mostrarMensajeError(data.output.response.content);
        }
    },
    save: function() {
        q = {};
        q.op = "saveprensa";
        q.id = $("#id").val();
        q.titulo = $("#titulo").val();
        q.descripcion = $("#descripcion").val();
        q.enable = $("#enable").val();

        var file = $("#file")[0].files;
        var formData = new FormData();

        Object.keys(q).forEach(function eachKey(key) {
            formData.append(key, q[key]);
        });
        if (file.length > 0) {
            formData.append("pdf", file[0]);
        }
        UTIL.cursorBusy();
        $.ajax({
            data: formData,
            type: "POST",
            contentType: false,
            processData: false,
            url: "admin/ajax/rqst.php",
            success: function(data) {
                data = JSON.parse(data);
                q = {};
                UTIL.cursorNormal();
                if (data.output.valid) {
                    UTIL.mostrarMensajeExitoso("Información guardada correctamente");
                    setTimeout(function() {
                        window.location = 'prensa.php';
                    }, 1000);
                } else {
                    UTIL.mostrarMensajeError(data.output.response.content);
                }
            },
        });
    }
};