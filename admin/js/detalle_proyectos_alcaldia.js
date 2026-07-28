$(document).on('ready', init);
var q;

function init() {
    q = {};
}

var return_page = 'proyecto_x_secretaria_alcalde.php';
var DETALLE_PROYECTO_ALCALDIA = {
    updatedata: function() {
        // Validar que los campos requeridos estén llenos
        var estado = $("#estado").val();
        var porcentaje_ejecucion = $("#porcentaje_ejecucion").val();
        var porcentaje_financiero = $("#porcentaje_financiero").val();
        var observaciones = $("#observaciones").val();

        if (!observaciones || observaciones.trim() === '') {
            UTIL.mostrarMensajeError('Debe ingresar una observación');
            return;
        }

        if (!porcentaje_ejecucion || porcentaje_ejecucion.trim() === '') {
            UTIL.mostrarMensajeError('Debe ingresar el nuevo porcentaje de ejecución');
            return;
        }

        if (!porcentaje_financiero || porcentaje_financiero.trim() === '') {
            UTIL.mostrarMensajeError('Debe ingresar el nuevo porcentaje de ejecución financiera');
            return;
        }

        if (estado === 'Seleccione') {
            UTIL.mostrarMensajeError('Debe seleccionar un nuevo estado para el proyecto');
            return;
        }

        q = {};
        q.op = "proyectos_alcaldias_update";
        q.id = $("#idProyecto").val();
        q.fecha_entrega = $("#fecha_entrega").val();
        q.estado = estado;
        q.porcentaje_ejecucion = porcentaje_ejecucion;
        q.porcentaje_financiero = porcentaje_financiero;
        q.observaciones = observaciones;

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
                    UTIL.mostrarMensajeExitoso('Información actualizada correctamente');
                    setTimeout(function() {
                        window.location.reload();
                    }, 1500);
                } else {
                    UTIL.mostrarMensajeError(data.output.response.content);
                }
            },
            error: function(xhr, status, error) {
                UTIL.cursorNormal();
                UTIL.mostrarMensajeError('Error al actualizar la información: ' + error);
            }
        });
    }
};
