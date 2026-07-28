$(document).on('ready', initAreas);

let queryParams;
const returnPage = 'areas.php';

function initAreas() {
    queryParams = {};
}

const AREAS = {
    editData: function (id) {
        queryParams = { op: "getArea", id };
        UTIL.callAjaxRqstPOST(queryParams, this.editDataHandler);
    },

    editDataHandler: function (data) {
        UTIL.cursorNormal();
        if (data?.output?.valid) {
            const res = data.output.response[0];
            $("#id").val(res.id || '');
            $("#nombre").val(res.nombre || '');
            $("#pilarId").val(res.tbl_pilar_id || '');
            $("#descripion").val(res.descripion || '');
            $("#enable").val(res.enable || '');
        } else {
            Swal.fire({
                icon: 'warning',
                title: 'Por favor revise',
                text: data?.output?.response?.content || 'Ocurrió un error inesperado.',
            });
        }
    },

    save: function () {
        const requeryParamsuiredFields = ["#nombre", "#pilarId", "#enable"];
        const missingFieldMessage = "Falta ingresar información obligatoria, marcada con asterisco.";

        if (!UTIL.validarCampos(requeryParamsuiredFields)) {
            UTIL.mostrarMensajeValidacion(missingFieldMessage);
            return;
        }

        const iconoUrl = $("#ifm1").attr("data-url") || null;
        const datos = {
            op: "savearea",
            id: $("#id").val(),
            pilarId: $("#pilarId").val(),
            nombre: $("#nombre").val(),
            enable: $("#enable").val(),
            descripcion: $("#descripcion").val(),
            icono: iconoUrl,
        };

        UTIL.cursorBusy();
        $.ajax({
            data: datos,
            type: "POST",
            dataType: "json",
            url: "admin/ajax/rqst.php",
            success: function (data) {
                queryParams = {};
                UTIL.cursorNormal();
                if (data?.output?.valid) {
                    UTIL.clearForm('formarea');
                    UTIL.mostrarMensajeExitoso('Información guardada correctamente');
                    setTimeout(() => window.location = returnPage, 800);
                } else {
                    UTIL.mostrarMensajeError(data?.output?.response?.content || 'Ocurrió un error inesperado.');
                }
            },
            error: function (xhr, status, error) {
                UTIL.cursorNormal();
                UTIL.mostrarMensajeError(`Error al guardar: ${error}`);
            }
        });
    },
};
