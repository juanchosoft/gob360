$(document).on("ready", init);
var q;

function init() {
    q = {};
}
var INGRESOACTORESREGION = {
    validateData: function() {
        var bValid = true;
        var msj = "Falta ingresar información obligatoria, marcada con asterisco.";
        if (
            $("#tbl_departamento_id").val() == "" ||
            $("#tbl_departamento_id").val() == null ||
            $("#tbl_municipio_id").val() == "" ||
            $("#tbl_municipio_id").val() == null ||
            $("#tbl_actores_mapa_id").val() == "" ||
            $("#tbl_actores_mapa_id").val() == null

        ) {
            UTIL.mostrarMensajeValidacion(msj);
            bValid = false;
            return;
        }
        if (bValid) {
            INGRESOACTORESREGION.saveInformacion();
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
                q.op = 'ingresoactoresreg_save';
                q.municipio_id = $("#tbl_municipio_id").val();
                q.departamento_id = $("#tbl_departamento_id").val();
                q.actor_id = $("#tbl_actores_mapa_id").val();
                var logo = $("#logo")[0].files;
                var formData = new FormData();

                Object.keys(q).forEach(function eachKey(key) {
                    formData.append(key, q[key]);
                });
                if (logo.length > 0) {
                    formData.append("logo", logo[0]);
                }
                UTIL.cursorBusy();
                $.ajax({
                    data: formData,
                    type: "POST",
                    // dataType: "json",
                    contentType: false,
                    processData: false,
                    url: "admin/ajax/rqst.php",
                    success: function(data) {
                        data = JSON.parse(data);
                        q = {};
                        UTIL.cursorNormal();
                        if (data.output.valid) {
                            UTIL.mostrarMensajeExitoso(
                                "Información guardada correctamente"
                            );
                            /*                             setTimeout(function() {
                                                            window.location = "ingreso_actores_region.php";
                                                        }, 1000); */
                        } else {
                            UTIL.mostrarMensajeError(data.output.response.content);
                        }
                    },
                });
            }
        });
    },
};
