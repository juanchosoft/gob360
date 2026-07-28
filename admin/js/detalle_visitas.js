$(document).on("ready", init);
var q;

var munSelect;

function init() {
  q = {};
}

var return_page = "cuadro_control_visitas.php";
var VISITAS = {
  editData: function (id) {
    q = {};
    q.op = "pms_visitasget";
    q.id = id;
    UTIL.callAjaxRqstPOST(q, this.editDataHandler);
  },
  editDataHandler: function (data) {
    UTIL.cursorNormal();
    if (data.output.valid) {
      $("#id").val(res.id);
      $("#date").val(res.date);
      $("#provincia").val(res.provincia);
      $("#tbl_secretarias_id").val(res.tbl_secretarias_id);
      $("#tipo").val(res.tipo);
      $("#entidad").val(res.entidad);
      $("#cargo").val(res.cargo);
      $("#observaciones").val(res.observaciones);
      $("#compromisos").val(res.compromisos);
      $("#compromisopac").val(res.compromisopac);
      $("#respuesta").val(res.respuesta);
      $("#beneficiario").val(res.beneficiario);
      munSelect = res.tbl_municipio_id;

      $("#tbl_departamento_id").val(res.tbl_departamento_id).trigger("change");
      setTimeout(function () {
        $("#tbl_municipio_id").val(res.tbl_municipio_id).trigger("change");
        departMentElemtn.select2("val", res.tbl_departamento_id);
        departMentElemtn.select2("destroy");
        $("#tbl_departamento_id").val(res.tbl_departamento_id);
        departMentElemtn.select2();
      }, 2500);
    } else {
      UTIL.mostrarMensajeError(data.output.response.content);
    }
  },
  validateData: function () {
    var msj = "Falta ingresar información obligatoria, marcada con asterisco.";
    if (!$("#date").val()) {
      UTIL.mostrarMensajeValidacion("Seleccione una fecha");
      bValid = false;
      return;
    }
    if ($("#tipo_registro").val() == "") {
      UTIL.mostrarMensajeValidacion("Seleccione el tipo de registro");
      $("#tipo_registro").focus();
      bValid = false;
      return;
    }
    if ($("#tipo_registro").val() == "Visita") {
      if ($("#provincia").val() == "") {
        UTIL.mostrarMensajeValidacion("Seleccione la provincia");
        $("#provincia").focus();
        bValid = false;
        return;
      }
      if ($("#tipo_visita").val() == "") {
        UTIL.mostrarMensajeValidacion("Seleccione el tipo de visita");
        $("#tipo_visita").focus();
        bValid = false;
        return;
      }
      if ($("#compromisos").val() == "") {
        UTIL.mostrarMensajeValidacion("Ingrese detalles");
        $("#compromisos").focus();
        bValid = false;
        return;
      }
    } else if ($("#tipo_registro").val() == "Compromiso") {
      if ($("#provincia").val() == "") {
        UTIL.mostrarMensajeValidacion("Seleccione la provincia");
        $("#provincia").focus();
        bValid = false;
        return;
      }
      if ($("#requiere_respuesta").val() == "") {
        UTIL.mostrarMensajeValidacion("Ingrese si requiere respuesta");
        $("#requiere_respuesta").focus();
        bValid = false;
        return;
      }
      if ($("#componente").val() == "") {
        UTIL.mostrarMensajeValidacion("Seleccione el componente");
        $("#componente").focus();
        bValid = false;
        return;
      }
      if ($("#tipo_ejecucion").val() == "") {
        UTIL.mostrarMensajeValidacion("Seleccione el tipo de tipo ejecución");
        $("#tipo_ejecucion").focus();
        bValid = false;
        return;
      }
      if ($("#compromisopac").val() == "") {
        UTIL.mostrarMensajeValidacion("Ingrese compromisos pactados");
        $("#compromisopac").focus();
        bValid = false;
        return;
      }
    }
    VISITAS.savedata();
  },
  savedata() {
    $("#guardaVisita").prop("disabled", true);
    UTIL.cursorBusy();
    const formData = new FormData();

    formData.append("method", "guardaVisita");
    formData.append("id", $("#id").val());
    formData.append("date", $("#date").val());
    formData.append("provincia", $("#provincia").val());
    formData.append("entidad", $("#entidad").val());
    formData.append("consecuencia", $("#consecuencia").val());
    formData.append("tipo_registro", $("#tipo_registro").val());
    formData.append("estado", $("#estado").val());
    formData.append("tipo_visita", $("#tipo_visita").val());
    formData.append("observaciones", $("#observaciones").val());
    formData.append("compromisos", $("#compromisos").val());
    formData.append("compromisopac", $("#compromisopac").val());
    formData.append("respuesta", $("#respuesta").val());
    formData.append("tbl_secretarias_id", $("#tbl_secretarias_id").val());
    formData.append("tbl_departamento_id", $("#tbl_departamento_id").val());
    formData.append("tbl_municipio_id", $("#tbl_municipio_id").val());
    formData.append("descripcion_hecho", $("#descripcion_hecho").val());
    formData.append("requiere_respuesta", $("#requiere_respuesta").val());
    formData.append("tipo_ejecucion", $("#tipo_ejecucion").val());
    formData.append("componente", $("#componente").val());

    const imageFile = $("#img")[0].files[0];
    if (imageFile) {
      formData.append("imagen", imageFile);
    }

    $.ajax({
      url: "./admin/controllers/compromisoMunicipioCtrl.php",
      type: "POST",
      data: formData,
      contentType: false,
      processData: false,
      dataType: "json",
      success: function (response) {
        if (response.state) {
          // Construir el mensaje con el ID si está disponible
          var mensaje = response.message;
          if (response.id) {
            mensaje += " - ID: " + response.id;
          }

          Swal.fire({
            icon: "success",
            title: "¡Bien!",
            text: mensaje,
            confirmButtonColor: "#28a745",
          }).then(() => {
            $("#ingresoVisita")[0].reset();
            $("#previewImage").html("");
            $("#guardaVisita").prop("disabled", false);
            UTIL.cursorNormal();
          });
        } else {
          $("#guardaVisita").prop("disabled", false);
          Swal.fire({
            icon: "error",
            title: "¡Error!",
            text: response.message,
            confirmButtonColor: "#28a745",
          });
        }
      },
      error: function () {},
    });
  },
};

$("#tipo_registro").on("change", function () {
  var tipo = $(this).val();
  if (tipo === "Visita") {
    $(".campo-visita").show();
    $(".campo-compromiso").hide();
  } else if (tipo === "Compromiso") {
    $(".campo-visita").hide();
    $(".campo-compromiso").show();
  } else {
    $(".campo-visita, .campo-compromiso").hide();
  }
});
$("#tipo_registro").trigger("change");

$("#img").on("change", function () {
  const file = this.files[0];
  if (file && file.type.startsWith("image/")) {
    const reader = new FileReader();
    reader.onload = function (e) {
      $("#previewImage").html(
        `<img src="${e.target.result}" class="img-fluid" style="max-height: 200px;">`
      );
    };
    reader.readAsDataURL(file);
  } else {
    $("#previewImage").html(`<p class="text-danger">Archivo no válido</p>`);
  }
});
