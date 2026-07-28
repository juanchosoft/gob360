$(document).on('ready', init);
var q;

function init() {
    q = {};
}


var APOYOSRECIBIDOS = {
    validateData: function() {
        var bValid = true;
        var msj = "Falta ingresar información obligatoria, marcada con asterisco.";
        if (
            $("#tbl_comentario_id").val() == "" ||
            $("#autorizo_comunicados").val() == null ||
            $("#autorizo_comunicados").val() == "" ||
            $("#tbl_departamento_id").val() == "" ||
            $("#tbl_vererda_id").val() == "" ||
            $("#nombre").val() == "" ||
            $("#email").val() == "" ||
            $("#telefono").val() == "" ||
            $("#email").val() == "" ||
            $("#cedula").val() == "" ||
            $("#profesion").val() == ""

            
        ) {
            UTIL.mostrarMensajeError(msj);
            bValid = false;
            return;
        }
        if (!$('#acepto_terminos').is(':checked')) {
            UTIL.mostrarMensajeError('Debes aceptar los términos de tratamiento de datos');
            bValid = false;
            return;
        }
        if (bValid) {
            APORTES.save();
        }
    },
    save: function() {
        q = {};
        q.op = "saveapoyos";
        q.tbl_comentario_id = $("#tbl_comentario_id").val();
        q.economico = $("#economico").val();
        q.logistica = $("#logistica").val();
        q.redes = $("#redes").val();
        q.equipossec = $("#equipossec").val();
        q.personal = $("#personal").val();
        q.amplificacion = $("#amplificacion").val();
        q.otros = $("#otros").val();
        q.detalles = $("#detalles").val();
        q.acepto_terminos = $('input[name="acepto_terminos"]').is(':checked') ? 'si' : 'no';
        q.autorizo_comunicados = $('input[name="autorizo_comunicados"]').is(':checked') ? 'si' : 'no';
        // Mensaje
                var mensaje = `señor(a) ${q.nombre} Gracias por los apoyos que nos puede brindar porque es tiempo de Colombia`;

        UTIL.cursorBusy();
        $.ajax({
            data: q,
            type: "GET",
            dataType: "json",
            url: "admin/ajax/rqst.php",
            success: function(data) {
                q = {};
                UTIL.cursorNormal();
                if (data.output.valid) {
                    UTIL.mostrarMensajeExitoso(mensaje);
                    setTimeout(function() {
                        window.location = 'apoya.php';
                    }, 5000);
                } else {
                    UTIL.mostrarMensajeError(data.output.response.content);
                }
            },
        });
    },
    checkAll: function(formulario, nombreCheckbox) {
        if ($(`#${nombreCheckbox}`).is(":checked")) {
            $(`#${formulario} :input`).each(function() {
                this.checked = true;
            });
        } else {
            $(`#${formulario} :input`).each(function() {
                this.checked = false;
            });
        }
    },
};