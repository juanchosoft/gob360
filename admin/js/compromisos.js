$(document).on('ready', init);
var q;

var munSelect;

function init() {
    q = {};
}

var munSelect;

var return_page = 'compromisos.php';
var COMPROMISOS = {
    editData: function(id) {
        q = {};
        q.op = "pms_compromisoget";
        q.id = id;
        UTIL.callAjaxRqstPOST(q, this.editDataHandler);
    },
    editDataHandler: function(data) {
        UTIL.cursorNormal();
        console.log(data)
        if (data.output.valid) {
            var res = data.output.response[0];
            console.log(res)
            $("#id").val(res.id);
            $("#date").val(res.date);
            $("#provincia").val(res.provincia);
            $("#cumplimiento").val(res.cumplimiento);
            $("#tbl_secretarias_id").val(res.tbl_secretarias_id);
            $("#compromisos").val(res.compromisos);
            $("#respuesta").val(res.respuesta);
            munSelect = res.tbl_municipio_id;
            
            $("#tbl_departamento_id").val(res.tbl_departamento_id);
            setTimeout(function() {
                $("#tbl_departamento_id").trigger('change');
                $("#tbl_municipio_id").val(res.tbl_municipio_id).trigger('change');
                departMentElemtn.select2('val',res.tbl_departamento_id)
                departMentElemtn.select2('destroy')
                $("#tbl_departamento_id").val(res.tbl_departamento_id)
                departMentElemtn.select2();
            }, 2500);
           

        } else {
            UTIL.mostrarMensajeError(data.output.response.content);
        }
    },
    validateData: function() {
        var bValid = true;
        var msj = "Falta ingresar información obligatoria, marcada con asterisco.";

        console.log(
        $("#tbl_departamento_id").val(),
        $("#tbl_municipio_id").val(),
        $("#date").val()  == "" )
       
        if (
            $("#tbl_departamento_id").val() == "" ||
            $("#tbl_municipio_id").val() == "" ||
            $("#date").val() == "" 
           
        ) 
        {
            UTIL.mostrarMensajeValidacion(msj);
            bValid = false;
            return;
        }else{
            COMPROMISOS.savedata();
        }

        
    },
    savedata() {
        q = {};
        q.op = "pms_compromisos";
        q.id = $("#id").val();
        q.date = $("#date").val();
        q.provincia = $("#provincia").val();
        q.compromisos = $("#compromisos").val();
        q.cumplimiento = $("#cumplimiento").val();
        q.respuesta = $("#respuesta").val();
        q.tbl_secretarias_id = $("#tbl_secretarias_id").val();
        q.tbl_departamento_id = $("#tbl_departamento_id").val();
        q.tbl_municipio_id = $("#tbl_municipio_id").val();
        UTIL.callAjaxRqstPOST(q, COMPROMISOS.savedatahandler);
    },
    savedatahandler: function(data) {
        UTIL.cursorNormal();
        if (data.output.valid) {
            UTIL.mostrarMensajeExitoso('Información guardada correctamente');
            // setTimeout(function() {
            //     window.location = return_page;
            // }, 1000);
        } else {
            UTIL.mostrarMensajeError(data.output.response.content);
        }
    },

};