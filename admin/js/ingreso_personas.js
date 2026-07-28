$(document).on("ready", init);
var q;

function init() {
    q = {};
}

var INGRESO_PERSONAS = {
    actualizarVerificado(id) {
        q = {};
        q.op = "actualizar_estadoverificado";
        q.id = id;
        q.verificado = $("#select_" + id).val();
        UTIL.callAjaxRqstPOST(q, INGRESO_PERSONAS.actualizarVerificadoHandler);
    },
    actualizarVerificadoHandler: function(data) {
        UTIL.cursorNormal();
        if (data.output.valid) {
            UTIL.mostrarMensajeExitoso('Información actualizada correctamente');
        } else {
            UTIL.mostrarMensajeError(data.output.response.content);
        }
    },
};