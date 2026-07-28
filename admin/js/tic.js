$(document).on('ready', init);
var q;

function init() {
    q = {};
}

var return_page = 'tic.php';
var TIC = {
    editData: function (id) {
        console.log(id)
        q = {};
        q.op = "gettic";
        q.id = id;
        UTIL.callAjaxRqstPOST(q, this.editHandler);
    },
   editHandler: function (data) {

    UTIL.cursorNormal();
    if (data.output.valid) {
        var res = data.output.response[0];

        $("#id").val(res.id);
        $("#date").val(res.date);
        $("#provincia").val(res.provincia); // <-- faltaba
        $("#zona").val(res.zona);
        $("#robotica").val(res.robotica);
        $("#computadores_institucion").val(res.computadores_institucion);
        $("#computador_alumno").val(res.computador_alumno);
        $("#observaciones").val(res.observaciones);
        $("#laboratorio_innovacion").val(res.laboratorio_innovacion);
        $("#tbl_sede_educativa_id").val(res.tbl_sede_educativa_id).trigger('change');
        $("#tbl_departamento_id").val(res.tbl_departamento_id).trigger('change');

        // Delay para cascada de municipios y veredas
       $("#tbl_municipio_id").val(res.tbl_municipio_id).trigger('change');

// Esperar a que las veredas se carguen dinámicamente antes de asignar
setTimeout(function () {
    // Solo cuando las veredas estén listas se asigna el valor
    $("#tbl_vereda_id").val(res.tbl_vereda_id).trigger('change');
    console.log("Asignando vereda:", res.tbl_vereda_id);
}, 1000);


        // ✅ Mostrar imagen si existe
        if (res.img && res.img !== "") {
            let imgPath = "assets/img/admin/" + res.img;
            if ($("#preview-img").length === 0) {
                $("#formsecretaria").append('<div class="form-group"><label>Vista previa imagen</label><br><img id="preview-img" src="" width="100" class="border rounded mt-1" /></div>');
            }
            $("#preview-img").attr("src", imgPath);
        }

    } else {
        UTIL.mostrarMensajeError(data.output.response.content);
    }
}
,
    validateData: function () {
    var bValid = true;
    var msj = "Falta ingresar información obligatoria, marcada con asterisco.";
    if (
    $("#date").val() == "" ||
    $("#zona").val() == "" ||
    $("#provincia").val() == "" ||
    $("#tbl_vereda_id").val() == ""
) {

        UTIL.mostrarMensajeValidacion(msj);
        bValid = false;
        return;
    }
    if (bValid) {
        TIC.savedata();
    }
},


   savedata: function () {
    const vereda = $("#tbl_vereda_id").val();

    if (!vereda || vereda === "0") {
        UTIL.mostrarMensajeValidacion("Debes seleccionar una vereda.");
        return;
    }

    q = {
        op: "savetic",
        id: $("#id").val(),
        date: $("#date").val(),
        provincia: $("#provincia").val(),
        tbl_departamento_id: $("#tbl_departamento_id").val(),
        tbl_municipio_id: $("#tbl_municipio_id").val(),
        tbl_vereda_id: $("#tbl_vereda_id").val(),
        tbl_sede_educativa_id: $("#tbl_sede_educativa_id").val(),
        zona: $("#zona").val(),
        robotica: $("#robotica").val(),
        computadores_institucion: $("#computadores_institucion").val(),
        computador_alumno: $("#computador_alumno").val(),
        laboratorio_innovacion: $("#laboratorio_innovacion").val(),
        observaciones: $("#observaciones").val(),
        cod_dane: $("#cod_dane").val()
    };

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
                setTimeout(() => window.location = return_page, 1000);
            } else {
                UTIL.mostrarMensajeError(data.output.response.content);
            }
        }
    });
}


};