$(document).on("ready", init);
var q;
var ejecutarBrigada;

function init() {
    q = {};
}

var GENERAR_PUNTAJE = {
    validateCheck: function() {
        ejecutarBrigada = $("#ejecutarBrigada").is(":checked");
        if (ejecutarBrigada) {
            $("#divBrigada").show();
            $("#divDepartamento").hide();
        } else {
            $("#divBrigada").hide();
            $("#divDepartamento").show();
        }
    },
    generarPuntaje: function() {
        q = {};
        if (ejecutarBrigada) {
            q.op = "calcularPuntajeBrigada";
            q.tbl_brigada_id = $("#tbl_brigada_id").val();
            UTIL.callAjaxRqstPOST(q, this.generarPuntajeHandler);
        } else {
            q.op = "calcularPuntajeDepartamento";
            q.codigo_departamento = $("#tbl_departamento_id").val();
            UTIL.callAjaxRqstPOST(q, this.generarPuntajeHandler);
        }
    },
    generarPuntajeHandler: function(data) {
        UTIL.cursorNormal();
        if (data.output.valid) {
            UTIL.mostrarMensajeExitoso('Información generada correctamente');
        }
    }
};