$(document).on("ready", init);
var q;

function init() {
    q = {};
}
var PROYECTOS = {
    validateData: function () {
        var bValid = true;
        var msj = "Falta ingresar información obligatoria, marcada con asterisco.";
        if (
            $("#date").val() == "" ||
            $("#provincia").val() == "" ||
            $("#provincia").val() == null ||
            $("#tbl_secretarias_id").val() == null ||
            $("#tbl_secretarias_id").val() == "" ||
            $("#proyecto").val() == null ||
            $("#proyecto").val() == "" ||
            $("#valor_proyecto").val() == "" ||
            $("#date_inicio").val() == "" ||
            $("#fecha_entrega").val() == "" ||
            $("#estado").val() == "" ||
            $("#valor_proyecto").val() == "" ||
            $("#valor_proyecto").val() == "0" ||
            $("#valor_proyecto").val() == null

        ) {
            UTIL.mostrarMensajeValidacion(msj);
            bValid = false;
            return;
        }
        if (bValid) {
            PROYECTOS.saveInformacion();
        }
    },
    saveInformacion: function () {
        Swal.fire({
            title: "Estás seguro ingresar la información?",
            text: "¿Desea continuar?",
            type: "warning",
            showCancelButton: true,
            confirmButtonText: "Si",
            cancelButtonText: "Cancelar!",
            closeOnConfirm: false,
        }).then((result) => {
            if (result.value) {
                q = {};
                q.op = 'ingresoproyectos_save';
                q.id = $("#id").val();
                q.tbl_secretarias_id = $("#tbl_secretarias_id").val();
                q.proyecto = $("#proyecto").val();
                q.date = $("#date").val();
                q.provincia = $("#provincia").val();
                q.tbl_departamento_id = $("#tbl_departamento_id").val();
                q.tbl_municipio_id = $("#tbl_municipio_id").val();
                q.valor_proyecto = $("#valor_proyecto").val();
                q.contratista = $("#contratista").val();
                q.nit = $("#nit").val();
                q.interventoria = $("#interventoria").val();
                q.adicion = $("#adicion").val();
                q.date_inicio = $("#date_inicio").val();
                q.dias_prorroga = $("#dias_prorroga").val();
                q.date_prorroga = $("#date_prorroga").val();
                q.plazo_construccion = $("#plazo_construccion").val();
                q.fecha_entrega = $("#fecha_entrega").val();
                q.estado = $("#estado").val();
                q.porcentaje_ejecucion = $("#porcentaje_ejecucion").val();
                q.observaciones = $("#observaciones").val();
                q.entidad_otros = $("#entidad_otros").val();
                q.otros_aportes = $("#otros_aportes").val();

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
                    success: function (data) {
                        q = {};
                        UTIL.cursorNormal();
                        if (data.output.valid) {
                            UTIL.mostrarMensajeExitoso(
                                "Información guardada correctamente"
                            );
                            setTimeout(function () {
                                window.location = 'proyectos_secretarias.php';
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