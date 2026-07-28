$(function () {
  cargaData();
  secretarias();
});

function cargaData() {
  if ($.fn.DataTable.isDataTable("#dynamictable")) {
    $("#dynamictable").DataTable().destroy();
  }

  tablaConfiguracionSecretaria = $("#dynamictable").DataTable({
    order: [[0, "desc"]],
    dom: "lrtip",
    processing: true,
    serverSide: true,
    responsive: true,
    columnDefs: [
      {
        targets: ["_all"],
        className: "mdc-data-table__cell",
      },
    ],
    ajax: {
      url: "./admin/controllers/configuracionCtrl.php",
      type: "POST",
      contentType: "application/json",
      data: function (d) {
        return JSON.stringify({
          method: "loadConfigSecretaria",
          data: d,
        });
      },
      dataSrc: "data",
      error: function (xhr, status, error) {
        console.error("Error en DataTable:", error);
      },
    },
    columns: [
      {
        data: "id",
        render: function (data) {
          return `<button class="btn btn-sm btn-transparent" 
                onclick="editConfigSecretaria('${data}');"
                title="Editar">
          <i class="feather icon-edit"></i>
        </button>`;
        },
      },
      { data: "secretaria" },
      { data: "tipo_medicion" },
      { data: "rango_desde" },
      { data: "rango_hasta" },
      {
        data: "color",
        render: function (data, type, row) {
          if (!data) return "";
          return `
            <div style="display: flex; gap: 6px; justify-content: center;">
                <div 
                style="width: 20px; height: 20px; background-color: ${data}; border: 1px solid #ccc; border-radius: 3px;"
                ></div>
            </div>`;
        },
      },
    ],
  });

  if ($("#customSearch").length) {
    $("#customSearch")
      .off()
      .on("keyup", function () {
        tablaConfiguracionSecretaria.search(this.value).draw();
      });
  }
}

function editConfigSecretaria(id) {
  UTIL.cursorBusy();

  $.ajax({
    url: "admin/controllers/configuracionCtrl.php",
    type: "POST",
    data: JSON.stringify({ method: "editConfigSecretaria", data: id }),
    contentType: "application/json",
    dataType: "json",
    success: function (data) {
      console.group(data, " ppe");
      UTIL.cursorNormal();

      if (data.state) {
        let res = data.data[0];
        $("#idPuntajeEdit").val(res.id);
        $("#secretariaIdEdit").val(res.tbl_secretaria_id);
        $("#tipo_medicionEdit").val(res.tipo_medicion);
        $("#desdeEdit").val(res.rango_desde);
        $("#hastaEdit").val(res.rango_hasta);
        $("#colorEdit").val(res.color);
        $("#colorBoxEdit").css("background-color", res.color);
        $("#modalEditSecretario").modal("show");
      } else {
        UTIL.mostrarMensajeError(data.output.response.content);
      }
    },
    error: function () {
      UTIL.cursorNormal();
      UTIL.mostrarMensajeError("Error al obtener el puntaje.");
    },
  });
}

function getPilarByEjeId() {
  const ejeId = $("#ejeId").val();
  const $pilarId = $("#pilarId");

  if (ejeId <= 0 || ejeId === "seleccione") {
    $pilarId.empty().prop("disabled", true);
    return;
  }

  UTIL.cursorBusy();
  $.ajax({
    url: "admin/ajax/rqst.php",
    type: "POST",
    data: { op: "getPilar", ejeId },
    dataType: "json",
    success: function (response) {
      UTIL.cursorNormal();
      $pilarId.empty();

      if (response.output.valid && response.output.response.length > 0) {
        const options =
          `<option value="">Seleccione</option>` +
          response.output.response
            .map((item) => `<option value="${item.id}">${item.nombre}</option>`)
            .join("");
        $pilarId.append(options).prop("disabled", false);
      } else {
        $pilarId
          .html('<option value="">Seleccione</option>')
          .prop("disabled", true);
      }
    },
    error: function () {
      UTIL.cursorNormal();
      $pilarId.empty().prop("disabled", true);
    },
  });
}

function getPilarByEjeIdEdit() {
  const ejeId = $("#ejeIdEdit").val();
  const $pilarId = $("#pilarIdEdit");

  if (ejeId <= 0 || ejeId === "seleccione") {
    $pilarId.empty().prop("disabled", true);
    return;
  }

  UTIL.cursorBusy();
  $.ajax({
    url: "admin/ajax/rqst.php",
    type: "POST",
    data: { op: "getPilar", ejeId },
    dataType: "json",
    success: function (response) {
      UTIL.cursorNormal();
      $pilarId.empty();

      if (response.output.valid && response.output.response.length > 0) {
        const options =
          `<option value="">Seleccione</option>` +
          response.output.response
            .map((item) => `<option value="${item.id}">${item.nombre}</option>`)
            .join("");
        $pilarId.append(options).prop("disabled", false);
      } else {
        $pilarId
          .html('<option value="">Seleccione</option>')
          .prop("disabled", true);
      }
    },
    error: function () {
      UTIL.cursorNormal();
      $pilarId.empty().prop("disabled", true);
    },
  });
}

function saveEdit() {
  const msj = "Falta ingresar información obligatoria, marcada con asterisco.";

  // Validar campos obligatorios
  const camposRequeridos = [
    "#idPuntajeEdit",
    "#secretariaIdEdit",
    "#tipo_medicionEdit",
    "#desdeEdit",
    "#hastaEdit",
    "#colorEdit",
  ];
  if (!UTIL.validarCampos(camposRequeridos)) {
    UTIL.mostrarMensajeValidacion(msj);
    return;
  }

  const datos = {
    method: "editConfiguracionSecretariaPuntajeSave",
    data: {
      id: $("#idPuntajeEdit").val(),
      secretaria: $("#secretariaIdEdit").val(),
      desde: $("#desdeEdit").val(),
      hasta: $("#hastaEdit").val(),
      tipo_medicion: $("#tipo_medicionEdit").val(),
      color: $("#colorEdit").val(),
    },
  };

  $.ajax({
    url: "admin/controllers/configuracionCtrl.php",
    type: "POST",
    data: JSON.stringify(datos),
    contentType: "application/json",
    dataType: "json",
    success: function (response) {
      if (response.state) {
        Swal.fire({
          icon: "success",
          title: "¡Bien!",
          text: response.message || "Actualizado correctamente.",
          confirmButtonColor: "#28a745",
        }).then(() => {
          cargaData();
          document.getElementById("formupuntajesEdit").reset();
          $("#modalEditSecretario").modal("hide");
        });
      } else {
        Swal.fire({
          icon: "error",
          title: "Error al actualizar",
          text: response.message || "Ocurrió un problema al actualizar.",
          confirmButtonColor: "#d33",
        });
      }
    },
  });
}

function save() {
  const msj = "Falta ingresar información obligatoria, marcada con asterisco.";

  const camposRequeridos = [
    "#secretariaId",
    "#tipo_medicion",
    "#desde",
    "#hasta",
    "#color",
  ];
  if (!UTIL.validarCampos(camposRequeridos)) {
    UTIL.mostrarMensajeValidacion(msj);
    return;
  }

  const datos = {
    method: "configuracionSecretariaPuntajeSave",
    data: {
      secretaria: $("#secretariaId").val(),
      tipo_medicion: $("#tipo_medicion").val(),
      desde: $("#desde").val(),
      hasta: $("#hasta").val(),
      color: $("#color").val(),
    },
  };

  $.ajax({
    url: "admin/controllers/configuracionCtrl.php",
    type: "POST",
    data: JSON.stringify(datos),
    contentType: "application/json",
    dataType: "json",
    success: function (response) {
      if (response.state) {
        Swal.fire({
          icon: "success",
          title: "¡Bien!",
          text: response.message || "Compromiso actualizado correctamente.",
          confirmButtonColor: "#28a745",
        }).then(() => {
          document.getElementById("formupuntajes").reset();
        });
      } else {
        Swal.fire({
          icon: "error",
          title: "Error al actualizar",
          text: response.message || "Ocurrió un problema al actualizar.",
          confirmButtonColor: "#d33",
        });
      }
    },
  });
}

function updateColorBox() {
  const color = document.getElementById("color").value;
  const colorBox = document.getElementById("colorBox");
  if (color) {
    colorBox.style.backgroundColor = color;
    colorBox.innerText = " ";
  } else {
    colorBox.style.backgroundColor = "#f1f1f1";
    colorBox.innerText = "Color seleccionado";
  }
}

function updateColorBoxEdit() {
  const color = document.getElementById("colorEdit").value;
  const colorBox = document.getElementById("colorBoxEdit");
  if (color) {
    colorBox.style.backgroundColor = color;
    colorBox.innerText = " ";
  } else {
    colorBox.style.backgroundColor = "#f1f1f1";
    colorBox.innerText = "Color seleccionado";
  }
}

function secretarias() {
  return new Promise((resolve) => {
    $.ajax({
      url: "./admin/controllers/utilsCtrl.php",
      type: "POST",
      data: JSON.stringify({ method: "secretaria" }),
      dataType: "json",
      contentType: "application/json",
      success: function (response) {
        const $select = $("#secretariaId");
        const $select1 = $("#secretariaIdEdit");

        $select.empty().append('<option value="">Seleccione</option>');
        $select1.empty().append('<option value="">Seleccione</option>');

        response.data.forEach(function (ciudad) {
          $select.append(
            `<option value="${ciudad.id}">${ciudad.secretaria}</option>`
          );
          $select1.append(
            `<option value="${ciudad.id}">${ciudad.secretaria}</option>`
          );
        });

        resolve();
      },
    });
  });
}
