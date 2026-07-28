$(document).on('ready', init);
var q;

var munSelect;

function init() {
    q = {};
}

var return_page = 'cuadro_control_visitasg.php';
var VISITASG = {
    editData: function (id) {
        q = {};
        q.op = "spi_visitasg_get";
        q.id = id;
        UTIL.callAjaxRqstPOST(q, this.editDataHandler);
    },
    editDataHandler: function (data) {
        UTIL.cursorNormal();
        if (data.output.valid) {
            var res = data.output.response[0];
            $("#id").val(res.id);
            $("#date").val(res.date);
            $("#provincia").val(res.provincia);
            $("#tbl_acciong_id").val(res.tbl_acciong_id);
            $("#poblacion").val(res.poblacion);
            $("#inversion").val(res.inversion);
            $("#desc_actividad").val(res.desc_actividad);
            $("#tipo_actividad").val(res.tipo_actividad || "primera_dama").trigger("change");

            $("#linea").val(res.linea);
            $("#estrategia").val(res.estrategia);
            $("#campana").val(res.campana);
            $("#actividad").val(res.actividad);
            $("#tbl_linea_id").val(res.tbl_linea_id);
            $("#tbl_estrategia_id").val(res.tbl_estrategia_id);

            // Mostrar fotos actuales guardadas
            var fotos = [res.foto1, res.foto2, res.foto3, res.foto4];
            var grid = $("#grid-fotos-actuales").empty();
            var hayFotos = false;
            $.each(fotos, function(idx, ruta) {
                if (ruta && ruta.trim() !== '') {
                    hayFotos = true;
                    var num = idx + 1;
                    grid.append(
                        '<div class="col-6 col-md-3">' +
                            '<div class="foto-actual-thumb">' +
                                '<a href="' + ruta + '" target="_blank">' +
                                    '<img src="' + ruta + '" alt="Foto ' + num + '" title="Ver foto ' + num + ' en tamaño completo">' +
                                '</a>' +
                            '</div>' +
                            '<div class="foto-actual-label">Foto ' + num + '</div>' +
                        '</div>'
                    );
                }
            });
            $("#seccion-fotos-actuales").toggle(hayFotos);

            munSelect = res.tbl_municipio_id;

            $("#tbl_departamento_id").val(res.tbl_departamento_id).trigger('change');
            setTimeout(function () {
                $("#tbl_municipio_id").val(res.tbl_municipio_id).trigger('change');
                departMentElemtn.select2('val', res.tbl_departamento_id)
                departMentElemtn.select2('destroy')
                $("#tbl_departamento_id").val(res.tbl_departamento_id)
                departMentElemtn.select2();
            }, 2500);

        } else {
            UTIL.mostrarMensajeError(data.output.response.content);
        }
    },
    validateData: function () {
        var bValid = true;
        var msj = "Falta ingresar información obligatoria, marcada con asterisco.";

        if (
            $("#tbl_departamento_id").val() == "" ||
            $("#tbl_municipio_id").val() == "" ||
            $("#date").val() == "" ||
            $("#tipo_actividad").val() == ""
        ) {
            UTIL.mostrarMensajeValidacion(msj);
            bValid = false;
            return;
        } else {
            VISITASG.savedata();
        }
    },
    savedata: function () {
        var tipoVal = $("#tipo_actividad").val();
        var tipoLabel = (tipoVal === "aspas")
            ? "Red de Valor Social 2"
            : (tipoVal === "primera_dama")
                ? "Red de Valor Social 1"
                : tipoVal;

        Swal.fire({
            title: "¿Confirmar guardado?",
            html: "¿Está seguro de guardar esta actividad como<br><strong>" + tipoLabel + "</strong>?",
            icon: "warning",
            showCancelButton: true,
            confirmButtonText: "Sí, guardar",
            cancelButtonText: "Cancelar",
        }).then((result) => {
            if (result.value) {

                const iframe1 = $("#ifm1").attr("data-url") || null;
                const iframe2 = $("#ifm2").attr("data-url") || null;
                const iframe3 = $("#ifm3").attr("data-url") || null;
                const iframe4 = $("#ifm4").attr("data-url") || null;

                q = {};
                q.op = "spi_visitas_save";
                q.id = $("#id").val();
                q.date = $("#date").val();
                q.provincia = $("#provincia").val();
                q.tbl_acciong_id = $("#tbl_acciong_id").val();
                q.poblacion = $("#poblacion").val();
                q.inversion = $("#inversion").val();
                q.desc_actividad = $("#desc_actividad").val();
                q.tbl_departamento_id = $("#tbl_departamento_id").val();
                q.tbl_municipio_id = $("#tbl_municipio_id").val();
                q.tipo_actividad = $("#tipo_actividad").val();
                q.foto1 = iframe1;
                q.foto2 = iframe2;
                q.foto3 = iframe3;
                q.foto4 = iframe4;
                q.linea =  $("#linea").val();
                q.estrategia =  $("#estrategia").val();
                q.campana =  $("#campana").val();
                q.actividad =  $("#actividad").val();
                q.link =  $("#link").val();
                q.tbl_linea = $("#tbl_linea_id").val();
                q.tbl_estrategia = $("#tbl_estrategia_id").val();
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
                            UTIL.mostrarMensajeExitoso('Información guardada correctamente');
                            window.location = return_page;
                        } else {
                            UTIL.mostrarMensajeError(data.output.response.content);
                        }
                    },
                });
            }
        });
    },


};