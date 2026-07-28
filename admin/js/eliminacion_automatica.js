$(document).on("ready", init);
var q, filtro;
var res = {};

function init() {
    q = {};
}

var AUTOMATICACION = {
    validateCheck: function() {
        if ($("#chkFilter").is(":checked")) {
            $("#tbl_departamento_id").val("seleccione").trigger("change");
            $("#tbl_vereda_id").empty().append("");

            $("#divMunicipio").hide();
            $("#divVereda").show();
        } else {
            $("#divMunicipio").show();
            $("#divVereda").hide();
        }
    },
    getMunicipiosByBatallonId: function() {
        if ($("#tbl_batallon_id").val() != "seleccione") {
            q = {};
            q.op = "get_solo_munic_x_batallon";
            q.id = $("#tbl_batallon_id").val();
            UTIL.callAjaxRqstPOST(q, this.getMunicipiosByBatallonIdHandler);
        } else {
            $("#tbl_ciudad_id").empty().append("");
        }
    },
    getMunicipiosByBatallonIdHandler: function(data) {
        UTIL.cursorNormal();
        if (data.output.valid) {
            var res = data.output.response;
            var option = "";
            if (res.length > 0) {
                for (var j in res) {
                    option +=
                        "<option value=" +
                        res[j].tbl_ciudad_id +
                        " >" +
                        res[j].municipio +
                        "</option>";
                }
                $("#tbl_ciudad_id").empty().append(option);
            } else {
                $("#tbl_ciudad_id").empty().append("");
                UTIL.mostrarMensajeError("No se encontraron Municipios");
            }
        } else {
            UTIL.mostrarMensajeError(data.output.response.content);
        }
    },
    confirmar: function() {
        Swal.fire({
            title: "Va e eliminar información de forma irreversible",
            text: "¿Desea continuar con el proceso?",
            showDenyButton: true,
            showCancelButton: true,
            confirmButtonText: `Guardar`,
        }).then((result) => {
            if (result.value) {
                q = {};
                if ($("#chkFilter").is(":checked")) {
                    q.op = "automatizar_delet_vereda";
                    q.codigo_departamento = $("#tbl_departamento_id").val();
                    q.codigo_muncipio = $("#tbl_municipio_id").val();
                    q.vereda = $("#tbl_vereda_id").val();
                } else {
                    q.op = "automatizar_delet_munic";
                    q.batallon_id = $("#tbl_batallon_id").val();
                    q.municipio_id = $("#tbl_ciudad_id").val();
                }
                q.factor_social = $("#factor_social").is(":checked");
                q.factor_armado = $("#factor_armado").is(":checked");
                q.factor_economico = $("#factor_economico").is(":checked");
                UTIL.cursorBusy();
                $.ajax({
                    data: q,
                    type: "GET",
                    dataType: "json",
                    url: "admin/ajax/rqst.php",
                    success: function(data) {
                        q = {};
                        UTIL.cursorNormal();
                        if (data.output.valid) {
                            UTIL.mostrarMensajeExitoso(
                                "Información procesada correctamente, No olvides por último ejecutar el calculo de puntajes manualmente"
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