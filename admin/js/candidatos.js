$(document).on('ready', init);
var q;

function init() {
    q = {};
}


var return_page = 'candidatos.php';
var CANDIDATOS = {
    editData: function(id) {
        q = {};
        q.op = "pms_candidatoget";
        q.id = id;
        UTIL.callAjaxRqstPOST(q, this.editDataHandler);
    },
    editDataHandler: function(data) {
        UTIL.cursorNormal();
        if (data.output.valid) {
            var res = data.output.response[0];
            $("#id").val(res.id);
            $("#nombre").val(res.nombre);
            $("#apellido").val(res.apellido);
            $("#telefono").val(res.telefono);
            $("#identificacion_tipo").val(res.identificacion_tipo);
            $("#identificacion_num").val(res.identificacion_num);
            $("#partido").val(res.partido);
            $("#aspirante").val(res.aspirante);
            $("#habilitado").val(res.habilitado);

            $("#tbl_departamento_id").val(res.tbl_departamento_id).trigger('change');

            setTimeout(function() {
                $("#tbl_municipio_id").val(res.tbl_municipio_id).trigger('change');
            }, 1500);

        } else {
            UTIL.mostrarMensajeError(data.output.response.content);
        }
    },
    validateData: function() {
        var bValid = true;
        var msj = "Falta ingresar información obligatoria, marcada con asterisco.";
        if (
            $("#tbl_departamento_id").val() == "seleccione" ||
            $("#tbl_municipio_id").val() == "seleccione" ||
            $("#nombre").val() == "" ||
            $("#apellido").val() == "" ||
            $("#telefono").val() == "" ||
            $("#partido").val() == "" ||
            $("#aspirante").val() == "" ||
            $("#habilitado").val() == "" ||
            $("#identificacion_tipo").val() == "" ||
            $("#identificacion_num").val() == ""
        ) 
        {
            UTIL.mostrarMensajeValidacion(msj);
            bValid = false;
            return;
          
        }else{
            CANDIDATOS.savedata();
        }

        
    },
    savedata() {
        q = {};
        q.op = "pms_candidatosave";
        q.id = $("#id").val();
        q.nombre = $("#nombre").val();
        q.apellido = $("#apellido").val();
        q.telefono = $("#telefono").val();
        q.partido = $("#partido").val();
        q.aspirante = $("#aspirante").val();
        q.identificacion_tipo = $("#identificacion_tipo").val();
        q.identificacion_num = $("#identificacion_num").val();
        q.habilitado = $("#habilitado").val();
        q.tbl_departamento_id = $("#tbl_departamento_id").val();
        q.tbl_municipio_id = $("#tbl_municipio_id").val();
        q.habilitado = $("#habilitado").val();
        UTIL.callAjaxRqstPOST(q, CANDIDATOS.savedatahandler);
    },
    savedatahandler: function(data) {
        UTIL.cursorNormal();
        if (data.output.valid) {
            UTIL.mostrarMensajeExitoso('Información guardada correctamente');
            setTimeout(function() {
                window.location = return_page;
            }, 1000);
        } else {
            UTIL.mostrarMensajeError(data.output.response.content);
        }
    },

};