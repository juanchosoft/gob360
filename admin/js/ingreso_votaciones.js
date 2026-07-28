$(document).on("ready", init);
var q;

function init() {
    q = {};
}
var INGRESOVOTACIONES = {
    validateData: function() {
        var bValid = true;
        var msj = "Falta ingresar información obligatoria, marcada con asterisco.";
        if (
            $("#tbl_departamento_id").val() == "" ||
            $("#tbl_departamento_id").val() == null ||
            $("#tbl_municipio_id").val() == "" ||
            $("#tbl_municipio_id").val() == null ||
            $("#tbl_vereda_id").val() == null ||
            $("#estado").val() == ""

        ) {
            UTIL.mostrarMensajeValidacion(msj);
            bValid = false;
            return;
        }
        if (bValid) {
            INGRESOVOTACIONES.saveInformacion();
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
                q.op = 'ingresovotaciones_save';
                q.codigo_muncipio = $("#tbl_municipio_id").val();
                q.vereda = $("#tbl_vereda_id").val();
                q.codigo_departamento = $("#tbl_departamento_id").val();
                q.habilitada_para_votar = $("#estado").val();
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
                        } else {
                            UTIL.mostrarMensajeError(data.output.response.content);
                        }
                    },
                });
            }
        });
    },
};