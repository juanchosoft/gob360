$(document).on('ready', init);
var q;
/**
 * se activa para inicializar el documento
 */
function init() {
    q = {};
}

var return_page = 'reporte_fe.php';
var REPORTEFE = {
    editData: function(id) {
        q = {};
        q.op = "reportefeget";
        q.id = id;
        UTIL.callAjaxRqstPOST(q, this.editdaHandler);
    },
    editdaHandler: function(data) {
        UTIL.cursorNormal();
        if (data.output.valid) {
            var res = data.output.response[0];
            $("#id").val(res.id);
            $("#fecha").val(res.fecha);
            $("#latitud").val(res.latitud);
            $("#pilar").val(res.pilar);
            $("#linea").val(res.linea);
            $("#detalle_linea").val(res.detalle_linea);
            $("#detalle_lugar").val(res.detalle_lugar);
            $("#participantes").val(res.participantes);
            $("#estrategia").val(res.estrategia);
            $("#nombre_act").val(res.nombre_act);
            $("#unidad").val(res.unidad);
            $("#proxima_reunion").val(res.proxima_reunion);
            $("#beneficiadas").val(res.beneficiadas);
            $("#costo").val(res.costo);

            REPORTEFE.setearDepMunVer(res);

            $("#longitud").val(res.longitud);
            $("#actividad").val(res.actividad);
            $("#responsable").val(res.responsable);
            $("#descripcion_actividad").val(res.descripcion_actividad);
            $("#entidades").val(res.entidades);
        } else {
            UTIL.mostrarMensajeError(data.output.response.content);
        }
    },
    /**
     * Método para setear la informacion de Departamento, Municipio y Vereda
     */
    setearDepMunVer(res) {
        $("#tbl_departamento_id").select2().val(res.codigo_departamento).trigger("change");

        DEPARTAMENTO.getMunicipios();

        setTimeout(function() {
            $("#tbl_municipio_id").select2().val(res.codigo_muncipio).trigger("change");
        }, 1500);

        $("#tbl_vereda_id").select2().val(res.tbl_vereda_id).trigger("change");
    },
    validateData: function() {
        var bValid = true;
        var msj = "Falta ingresar información obligatoria, marcada con asterisco.";
        if (
            $("#fecha").val() == "" ||
            $("#latitud").val() == "" ||
            $("#pilar").val() == "" ||
            $("#linea").val() == "" ||
            $("#detalle_linea").val() == "" ||
            $("#estrategia").val() == "" ||
            $("#unidad").val() == "" ||
            $("#municipio_id").val() == "" ||
            $("#detalle_lugar").val() == "" ||
            $("#participantes").val() == "" ||
            $("#longitud").val() == "" ||
            $("#actividad").val() == "" ||
            $("#nombre_act").val() == "" ||
            $("#responsable").val() == "" ||
            $("#costo").val() == "" ||
            $("#beneficiadas").val() == "" ||
            $("#tbl_departamento_id").val() == "" ||
            $("#tbl_municipio_id").val() == "" ||
            $("#proxima_reunion").val() == "" ||
            $("#descripcion_actividad").val() == ""
        ) {
            UTIL.mostrarMensajeValidacion(msj);
            bValid = false;
            return;
        }
        if (bValid) {
            REPORTEFE.savedata();
        }
    },
    savedata: function() {
        q = {};
        q.op = "reportefesave";
        q.id = $("#id").val();
        q.fecha = $("#fecha").val();
        q.latitud = $("#latitud").val();
        q.pilar = $("#pilar").val();
        q.linea = $("#linea").val();
        q.detalle_linea = $("#detalle_linea").val();
        q.estrategia = $("#estrategia").val();
        q.unidad = $("#unidad").val();
        q.participantes = $("#participantes").val();
        q.municipio_id = $("#municipio_id").val();
        q.detalle_lugar = $("#detalle_lugar").val();
        q.longitud = $("#longitud").val();
        q.actividad = $("#actividad").val();
        q.nombre_act = $("#nombre_act").val();
        q.responsable = $("#responsable").val();
        q.beneficiadas = $("#beneficiadas").val();
        q.costo = $("#costo").val();
        q.tbl_departamento_id = $("#tbl_departamento_id").val();
        q.tbl_municipio_id = $("#tbl_municipio_id").val();
        q.tbl_vereda_id = $("#tbl_vereda_id").val();
        q.descripcion_actividad = $("#descripcion_actividad").val();
        q.entidades = $("#entidades").val();
        q.proxima_reunion = $("#proxima_reunion").val();

        var foto_fe = $('#fotoFe')[0].files;
        var foto_fe2 = $('#fotoFe2')[0].files;
        var archivo_fe = $('#archivoFe')[0].files;

        var formData = new FormData();

        Object.keys(q)
            .forEach(function eachKey(key) {
                formData.append(key, q[key])
            });

        if (foto_fe.length > 0) {
            formData.append("foto_fe", foto_fe[0]);
        }

        if (foto_fe2.length > 0) {
            formData.append("foto_fe2", foto_fe2[0]);
        }

        if (archivo_fe.length > 0) {
            formData.append("archivo_fe", archivo_fe[0]);
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
                    UTIL.mostrarMensajeExitoso('Información guardada correctamente');
                    setTimeout(function() {
                        window.location = 'reporte_fe.php';
                    }, 1500);
                } else {
                    UTIL.mostrarMensajeError(data.output.response.content);
                }
            },
        });
    }
};