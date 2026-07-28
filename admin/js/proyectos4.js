$(document).on("ready", init);
var q;

function init() {
    q = {};
}
var PROYECTOS4 = {
    validateData: function() {
        var bValid = true;
        var msj = "Falta ingresar información obligatoria, marcada con asterisco.";
        if (
            $("#tbl_brigada_id").val() == "" ||
            $("#proyecto").val() == "" ||
            $("#financiacion").val() == null ||
            $("#valor_proyecto").val() == null ||
            $("#estado").val() == ""

        ) {
            UTIL.mostrarMensajeValidacion(msj);
            bValid = false;
            return;
        }
        if (bValid) {
            PROYECTOS4.saveInformacion();
        }
    },
    saveInformacion: function() {
        Swal.fire({
            title: 'Estás seguro ingresar la información?',
            showDenyButton: true,
            showCancelButton: true,
            confirmButtonText: `Guardar`,
        }).then((result) => {
            if (result.value) {
                q = {};
                q.op = 'ingresoproyectos4_save';
                q.id =  $("#id").val();
                q.proyecto = $("#proyecto").val();
                q.tbl_brigada_id = $("#tbl_brigada_id").val();
                q.tbl_batallon_id = $("#tbl_batallon_id").val();
                q.financiacion = $("#financiacion").val();
                q.financiacion1 = $("#financiacion1").val();
                q.valor_proyecto = $("#valor_proyecto").val();
                q.contratista = $("#contratista").val();
                q.interventoria = $("#interventoria").val();
                q.plazo_construccion = $("#plazo_construccion").val();
                q.fecha_entrega = $("#fecha_entrega").val();
                q.estado = $("#estado").val();
                q.porcentaje_ejecucion = $("#porcentaje_ejecucion").val();
                q.observaciones = $("#observaciones").val();
                q.objetivo_proyecto = $("#objetivo_proyecto").val();
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
                            UTIL.mostrarMensajeExitoso(
                                "Información guardada correctamente"
                            );
                            setTimeout(function() {
                                window.location = 'nuevo_proyecto4.php';
                            }, 1000);
                        } else {
                            UTIL.mostrarMensajeError(data.output.response.content);
                        }
                    },
                });
            }
        });
    },
};