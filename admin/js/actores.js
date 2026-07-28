$(document).on("ready", init);
var q;

function init() {
  q = {};
}

var return_page = "actores.php";
var ACTORES = {
  editData: function (id) {
    q = {};
    q.op = "spi_actores_get";
    q.id = id;
    UTIL.callAjaxRqstPOST(q, this.editdaHandler);
  },
  editdaHandler: function (data) {
    UTIL.cursorNormal();
    if (data.output.valid) {
      var res = data.output.response[0];
      $("#id").val(res.id);
      $("#nombre").val(res.nombre);
      $("#pertenece").val(res.pertenece);
      $("#tbl_municipio_id").val(res.municipio_id);
    } else {
      Swal.fire({
        icon: "warning",
        title: "Por favor revise",
        text: data.output.response.content,
      });
    }
  },
  validateData: function () {
    var bValid = true;
    var msj = "Falta ingresar información obligatoria, marcada con asterisco.";
    if ($("#nombre").val() == "" || $("#pertenece").val() == "") {
      UTIL.mostrarMensajeValidacion(msj);
      bValid = false;
      return;
    }
    if (bValid) {
      ACTORES.savedata();
    }
  },
  savedata: function () {
    q = {};
    q.op = "spi_actores_save";
    q.id = $("#id").val();
    q.nombre = $("#nombre").val();
    q.pertenece = $("#pertenece").val();
    q.alcaldia_id = $("#tbl_municipio_id").val();
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
          UTIL.clearForm("formuactores");
          UTIL.mostrarMensajeExitoso("Información guardada correctamente");
          setTimeout(function () {
            window.location = return_page;
          }, 1500);
        } else {
          UTIL.mostrarMensajeError(data.output.response.content);
        }
      },
    });
  },
};
