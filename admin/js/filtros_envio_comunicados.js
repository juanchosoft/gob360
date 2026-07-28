$(document).on("ready", init);
var q;

function init() {
    q = {};
}

var FILTRO_ENVIO = {
    validatTipoFiltro: function() {
        var tipo = $("#tipo").val();
        switch (tipo) {
            case "Departamento":
                $("#divDep").show();
                $("#divMunicipio").hide();
                $("#divVereda").hide();
                break;

            case "Municipio":
                $("#divDep").show();
                $("#divMunicipio").show();
                $("#divVereda").hide();
                break;

            case "Vereda":
                $("#divDep").show();
                $("#divMunicipio").show();
                $("#divVereda").show();
                break;

            case "Todos":
                $("#divDep").hide();
                $("#divMunicipio").hide();
                $("#divVereda").hide();
                break;

            default:
                $("#divDep").hide();
                $("#divMunicipio").hide();
                $("#divVereda").hide();
                break;
        }
    },
    save: function() {
        var msj = "Falta ingresar información obligatoria, marcada con asterisco.";
        if ($("#nombre_filtro").val() == "" || $("#tipo").val() == "") {
            UTIL.mostrarMensajeValidacion(msj);
            bValid = false;
            return;
        }
        q = {};
        q.op = "savefiltros";
        q.nombre_filtro = $("#nombre_filtro").val();
        q.tipo = $("#tipo").val();
        q.tbl_departamento_id = $("#tbl_departamento_id").val();
        q.tbl_municipio_id = $("#tbl_municipio_id").val();
        q.tbl_vereda_id = $("#tbl_vereda_id").val();
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
                    UTIL.mostrarMensajeExitoso('Información guardada correctamente');
                    setTimeout(function() {
                        window.location = 'filtros_envio_comunicados.php';
                    }, 1000);
                } else {
                    UTIL.mostrarMensajeError(data.output.response.content);
                }
            },
        });
    },
    show: function(id) {
        q = {};
        q.op = "getPersonasByFiltroId";
        q.id = id;
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
                    var response = data.output.response;
                    console.log("TCL: response", response)
                } else {
                    UTIL.mostrarMensajeError(data.output.response.content);
                }
            },
        });
    },
    delete: function(id) {
        Swal.fire({
            title: "Va a eliminar información de forma irreversible!",
            text: "¿Desea continuar?",
            type: "warning",
            showCancelButton: true,
            confirmButtonText: "Si",
            cancelButtonText: "Cancelar!",
            closeOnConfirm: false,
        }).then((result) => {
            if (result.value) {
                q = {};
                q.op = "deletefiltros";
                q.id = id;
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
                            UTIL.mostrarMensajeExitoso('Registro eliminado correctamente');
                            setTimeout(function() {
                                window.location = 'filtros_envio_comunicados.php';
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