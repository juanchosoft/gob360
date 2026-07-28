$(document).on("ready", initFactoresInestabilidad);

function initFactoresInestabilidad() {
    FACTORES_INESTABILIDAD.init();
}

const FACTORES_INESTABILIDAD = {
    init: function () {},

    removeIcono: function() {
        $("#icono_hidden").val("");
        $("#icono-preview-wrap").hide();
        $("#icono-preview").attr("src", "");
    },

    edit: function (id) {
        q = {};
        q.op = "getFactoresInestabilidad";
        q.id = id;
        UTIL.cursorBusy();
        $.ajax({
            data: q,
            type: "GET",
            dataType: "json",
            url: "admin/ajax/rqst.php",
            success: function (data) {
                q = {};
                UTIL.cursorNormal();
                if (data.output.valid) {
                    let res = data.output.response[0];
                    $("#id").val(res.id);
                    $("#nombre_categoria").val(res.nombre_categoria);
                    if (res.icono) {
                        $("#icono_hidden").val(res.icono);
                        $("#icono-preview").attr("src", res.icono);
                        $("#icono-preview-wrap").show();
                    } else {
                        $("#icono_hidden").val("");
                        $("#icono-preview-wrap").hide();
                    }
                } else {
                    UTIL.mostrarMensajeError(data.output.response.content);
                }
            },
        });
    },

    save() {
        const msj = "Falta ingresar información obligatoria, marcada con asterisco.";
        const camposRequeridos = ["#nombre_categoria"];
        if (!this.validarCampos(camposRequeridos)) {
            UTIL.mostrarMensajeValidacion(msj);
            return;
        }

        const iconoVal = $("#icono_hidden").val() || null;

        const datos = {
            op: "factoresInestabilidadSave",
            id: $("#id").val(),
            nombre_categoria: $("#nombre_categoria").val(),
            icono: iconoVal
        };

        UTIL.callAjaxRqstPOST(datos, FACTORES_INESTABILIDAD.savehandler);
    },

    savehandler(data) {
        UTIL.cursorNormal();
        if (data.output.valid) {
            UTIL.clearForm('formfactores');
            UTIL.mostrarMensajeExitoso('Información guardada correctamente');
            setTimeout(() => {
                window.location = 'factores_inestabilidad_gobernacion.php';
            }, 1000);
        } else {
            UTIL.mostrarMensajeError(data.output.response.content || 'Error al guardar la información');
        }
    },

    validarCampos(campos) {
        for (const campo of campos) {
            if ($(campo).val() === "") {
                return false;
            }
        }
        return true;
    }
};
