$(document).on("ready", initactualizarinformacion);

function initactualizarinformacion() {
    ACTUALIZACION_INFORMACION.init();
}

const ACTUALIZACION_INFORMACION = {
    save() {
        const msj = "Falta ingresar información obligatoria, marcada con asterisco.";

        // Validar campos obligatorios
        const camposRequeridos = [ "#tbl_departamento_id", "#tbl_municipio_id", "#tbl_vereda_id", "#actoresId", "#cantidad_nueva", "#actoresId", "#accion_realizada", "#accion_realizada"];

        if (!UTIL.validarCampos(camposRequeridos)) {
            UTIL.mostrarMensajeValidacion(msj);
            return;
        }

        const iframe1 = $("#ifm1").attr("data-url") || null;
        const iframe2 = $("#ifm2").attr("data-url") || null;
        const iframe3 = $("#ifm3").attr("data-url") || null;
        const iframe4 = $("#ifm4").attr("data-url") || null;

        // Crear objeto con datos
        const datos = {
            op: "actualizacionestrategicossave",
            id: $("#id").val(),
            codDepartamento_id: $("#tbl_departamento_id").val(),
            codMunicipio_id: $("#tbl_municipio_id").val(),
            vereda_id: $("#tbl_vereda_id").val(),
            factorId: $("#factorId").val(),
            actoresId: $("#actoresId").val(),
            accion_realizada: $("#accion_realizada").val(),
            valor_actualizacion: $("#cantidad_nueva").val(),
            image: iframe1,
            image2: iframe2,
            image3: iframe3,
            image4: iframe4
        };
        // Llamada AJAX
        UTIL.callAjaxRqstPOST(datos, ACTUALIZACION_INFORMACION.savehandler);
    },

    savehandler(data) {
        UTIL.cursorNormal();

        if (data.output.valid) {
            UTIL.mostrarMensajeExitoso('Información guardada correctamente');
            setTimeout(() => {
                window.location = '';
            }, 1000);
        } else {
            UTIL.mostrarMensajeError(data.output.response.content || 'Error al guardar la información');
        }
    },

};
