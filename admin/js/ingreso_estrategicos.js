$(document).on("ready", initingresoinformacion);

function initingresoinformacion() {
    INGRESO_INFORMACION.init();
}

const INGRESO_INFORMACION = {
    openImage: function (src) {
        // Abre la imagen en una nueva ventana o pestaña
        window.open(src, '_blank');
    },

    save() {
        const msj = "Falta ingresar información obligatoria, marcada con asterisco.";

        // Validar campos obligatorios
        const camposRequeridos = [ "#tbl_departamento_id", "#tbl_municipio_id", "#tbl_vereda_id", "#factorId", "#valor"];

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
            op: "ingresoestrategicossave",
            id: $("#id").val(),
            codDepartamento_id: $("#tbl_departamento_id").val(),
            codMunicipio_id: $("#tbl_municipio_id").val(),
            vereda_id: $("#tbl_vereda_id").val(),
            factorId: $("#factorId").val(),
            longitud: $("#longitud").val(),
            observaciones: $("#observaciones").val(),
            latitud: $("#latitud").val(),
            valor: $("#valor").val(),
            foto1: iframe1,
            foto2: iframe2,
            foto3: iframe3,
            foto4: iframe4
        };

        // Llamada AJAX
        UTIL.callAjaxRqstPOST(datos, INGRESO_INFORMACION.savehandler);
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

    showInfoGetFactores: function () {
        let id = $("#factorId").val();
        if(id > 0){
            q = {};
            q.op = "getFactores";
            q.id =  $("#factorId").val();
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
                        $("#eje").val(res.eje);
                        $("#pilar").val(res.pilar);
                        $("#area").val(res.area);
                        $("#tipo_medicion").val(res.tipo_medicion);
    
                        $('#divInformacion').show(); 
                    } else {
                        $('#divInformacion').hide(); 
                    }
                },
            });
        }else{
            $('#divInformacion').hide(); 
            $("#eje").val('');
            $("#pilar").val('');
            $("#area").val('');
            $("#tipo_medicion").val('');
        }
    }
};
