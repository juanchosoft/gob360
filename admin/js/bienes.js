$(document).on("ready", init);
var q;

function init() {
  q = {};
}

var return_page = "bienes.php";
var BIENES = {
  editData: function (id) {
    q = {};
    q.op = "bienesget";
    q.id = id;
    UTIL.callAjaxRqstPOST(q, this.editdataHandler);
  },

  editdataHandler: function (data) {
    UTIL.cursorNormal();
    if (data.output.valid) {
      var res = data.output.response[0];
      informacionMunicipio = {};
      informacionMunicipio.latitud = res.latitud;
      informacionMunicipio.longitud = res.longitud;
      
      $("#idBienes").val(res.id);
      $("#codigo_control").val(res.codigo_control);
      $("#calcomania").val(res.calcomania);
      $("#nombre_articulo").val(res.nombre_articulo);
      $("#costo_unitario").val(res.costo_unitario);
      $("#tbl_departamento_id").val(res.tbl_departamento_id);
      $("#tbl_municipio_id").val(res.tbl_municipio_id);
      $("#secretaria").val(res.tbl_secretaria_id);
      $("#dependencia").val(res.dependencia);
      $("#cedula_o_nit").val(res.cedula_o_nit);
      $("#responsable").val(res.responsable);
      $("#observacion").val(res.observacion);
      $("#latitud").val(res.latitud);
      $("#longitud").val(res.longitud);

      initMap(res.latitud, res.longitud, null);

      switchToFormTab();
    } else {
      UTIL.mostrarMensajeError(data.output.response.content);
    }
  },

  validateData: function () {
    var bValid = true;
    var msj = "Falta ingresar información obligatoria, marcada con asterisco.";
    if ($("#codigo_control").val() === "") {
      bValid = false;
      UTIL.mostrarMensajeValidacion(msj);
      return;
    }
    if ($("#calcomania").val() === "") {
      bValid = false;
      UTIL.mostrarMensajeValidacion(msj);
      return;
    }
    if ($("#nombre_articulo").val() === "") {
      bValid = false;
      UTIL.mostrarMensajeValidacion(msj);
      return;
    }
    if ($("#costo_unitario").val() === "") {
      bValid = false;
      UTIL.mostrarMensajeValidacion(msj);
      return;
    }
    if ($("#tbl_departamento_id").val() === "") {
      bValid = false;
      UTIL.mostrarMensajeValidacion(msj);
      return;
    }
    if ($("#tbl_municipio_id").val() === "") {
      bValid = false;
      UTIL.mostrarMensajeValidacion(msj);
      return;
    }

    if (bValid) {
      BIENES.savedata();
    }
  },

  savedata: function () {
    const iframe1 = $("#ifm1").attr("data-url") || null;
    const iframe2 = $("#ifm2").attr("data-url") || null;
    const iframe3 = $("#ifm3").attr("data-url") || null;
    const iframe4 = $("#ifm4").attr("data-url") || null;
    q = {};
    q.op = "bienessave";
    q.id = $("#idBienes").val();
    q.codigo_control = $("#codigo_control").val();
    q.calcomania = $("#calcomania").val();
    q.nombre_articulo = $("#nombre_articulo").val();
    q.costo_unitario = $("#costo_unitario").val();
    q.tbl_departamento_id = $("#tbl_departamento_id").val();
    q.tbl_municipio_id = $("#tbl_municipio_id").val();
    q.secretaria = $("#secretaria").val();
    q.dependencia = $("#dependencia").val();
    q.cedula_o_nit = $("#cedula_o_nit").val();
    q.responsable = $("#responsable").val();
    q.observacion = $("#observacion").val();
    q.latitud = $("#latitud").val();
    q.longitud = $("#longitud").val();
    q.img1 = iframe1;
    q.img2 = iframe2;
    q.img3 = iframe3;
    q.img4 = iframe4;
    UTIL.cursorBusy();
    $.ajax({
      data: q,
      type: "POST",
      dataType: "json",
      url: "admin/ajax/rqst.php",
      success: function (data) {
        UTIL.cursorNormal();
        if (data.output.valid) {
          UTIL.mostrarMensajeExitoso("Información guardada correctamente");
          setTimeout(function () {
            window.location = return_page;
          }, 1500);
        } else {
          UTIL.mostrarMensajeError(data.output.response.content);
        }
      },
      error: function () {
        UTIL.cursorNormal();
        UTIL.mostrarMensajeError(
          "Ha ocurrido un error en la operación ejecutada"
        );
      },
    });
  },
  deleteData: function (id) {
    if (!confirm("¿Está seguro de que desea eliminar este registro?")) {
      return;
    }
    q = {};
    q.op = "bienesdelete";
    q.id = id;

    UTIL.cursorBusy();
    $.ajax({
      data: q,
      type: "POST",
      dataType: "json",
      url: "admin/ajax/rqst.php",
      success: function (data) {
        UTIL.cursorNormal();
        if (data.output.valid) {
          UTIL.mostrarMensajeExitoso("Registro eliminado correctamente.");
          setTimeout(function () {
            window.location.reload();
          }, 1500);
        } else {
          UTIL.mostrarMensajeError(
            data.output.response.content || "No se pudo eliminar el registro."
          );
        }
      },
      error: function () {
        UTIL.cursorNormal();
        UTIL.mostrarMensajeError(
          "Ha ocurrido un error en la operación ejecutada"
        );
      },
    });
  },
  checkAvailability: function (input) {
    var fieldValue = $(input).val();
    var recordId = $("#idBienes").val();

    if (fieldValue.trim() === "") {
      return;
    }

    q = {};
    q.op = "bienesavailable";
    q.fieldValue = fieldValue;
    q.id = recordId;

    $.ajax({
      data: q,
      type: "POST",
      dataType: "json",
      url: "admin/ajax/rqst.php",
      success: function (data) {
        if (!data.output.valid) {
          UTIL.mostrarMensajeError(data.output.response.content);
          $(input).focus();
        }
      },
    });
  },
};

function switchToFormTab() {
  const tabTrigger = document.querySelector("#home-tab");
  const tab = new bootstrap.Tab(tabTrigger); // Bootstrap 5
  tab.show();
}

function emptyDataForm() {
  $("#idBienes").val("");
  $("#codigo_control").val("");
  $("#calcomania").val("");
  $("#nombre_articulo").val("");
  $("#costo_unitario").val("");
  $("#dependencia").val("");
  $("#cedula_o_nit").val("");
  $("#responsable").val("");
  $("#observacion").val("");
  $("#latitud").val("");
  $("#longitud").val("");
}
