$(document).on('ready', init);
var q;

var return_page = 'plan_desarrollo.php';
var DESARROLLO = {
    editData: function (id) {
        q = {};
        q.op = "pms_desarrollo_get";
        q.id = id;
        UTIL.callAjaxRqstPOST(q, this.editDataHandler);
    },
    editDataHandler: function (data) {
        UTIL.cursorNormal();
        if (data.output.valid) {
            var res = data.output.response[0];
            $("#id").val(res.id);
            $("#eje_estrategico").val(res.eje_estrategico);
            $("#sector_pdd").val(res.sector_pdd);
            $("#sector_cat_prod").val(res.sector_cat_prod);
            $("#producto_servicio_pdd").val(res.producto_servicio_pdd);
            $("#tbl_secretaria_id").val(res.tbl_secretaria_id);
            $("#direccion_resp").val(res.direccion_resp);
            $("#ps2024").val(res.ps2024);
            $("#ps2025").val(res.ps2025);
            $("#ps2026").val(res.ps2026);
            $("#ps2027").val(res.ps2027);
        } else {
            UTIL.mostrarMensajeError(data.output.response.content);
        }
    },
    savedata() {
        q = {};
        q.op = "pms_desarrollo_save";
        q.id = $("#id").val();
        q.eje_estrategico = $("#eje_estrategico").val();
        q.sector_pdd = $("#sector_pdd").val();
        q.tbl_secretaria_id = $("#tbl_secretaria_id").val();
        q.sector_cat_prod = $("#sector_cat_prod").val();
        q.direccion_resp = $("#direccion_resp").val();
        q.producto_servicio_pdd = $("#producto_servicio_pdd").val();
        q.ps2024 = $("#ps2024").val();
        q.ps2025 = $("#ps2025").val();
        q.ps2026 = $("#ps2026").val();
        q.ps2027 = $("#ps2027").val();
        UTIL.callAjaxRqstPOST(q, DESARROLLO.savedatahandler);
    },
    savedatahandler: function (data) {
        UTIL.cursorNormal();
        if (data.output.valid) {
            UTIL.mostrarMensajeExitoso('Información guardada correctamente');
        } else {
            UTIL.mostrarMensajeError(data.output.response.content);
        }
    },
    setFilter() {
        var secretaria = $("#tbl_secretarias_id").val();
        q = {};
        q.op = "pms_desarrollo_filtar_secretaria";
        if (secretaria != null) {
            q.secretaria_id = secretaria;
            UTIL.callAjaxRqstPOST(q, DESARROLLO.setFilterHandler);
        }
    },
    setFilterHandler: function (data) {
        if (data.output.valid) {
            window.location = return_page;
        }
    },
    setSelectSecretaria() {
        $("#tbl_secretarias_id").val($("#filtroSecretaria").val());
    },
    updateAvance: function (id) {
        var avance = $("#avance_2025_" + id).val();
        if (avance == "") {
            UTIL.mostrarMensajeError("Debes ingresar un valor para el avance");
            return;
        }
        q = {};
        q.op = "update_avance";
        q.id = id;
        q.avance = avance;
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
                    $("#avance_2025_" + id).val('');
                    UTIL.mostrarMensajeExitoso('Avance actualizada correctamente');
                    setTimeout(function () {
                        window.location = return_page;
                    }, 1000);
                } else {
                    UTIL.mostrarMensajeError(data.output.response.content);
                }
            },
        });
    },
};
function init() {
    q = {};
    // DESARROLLO.setSelectSecretaria();
}