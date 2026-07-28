$(function () {
  cargarCategoriasInestabilidad();
  cargaData();

  $("#filtroTipo").on("change", function () {
    cargaData();
  });
});

function cargaData() {
  if ($.fn.DataTable.isDataTable("#dynamictable")) {
    $("#dynamictable").DataTable().destroy();
  }
  tablaConfiguracion = $("#dynamictable").DataTable({
    order: [[1, "asc"]],
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
        d.filtroTipo = $("#filtroTipo").val() || 0;
        return JSON.stringify({
          method: "load",
          data: d,
        });
      },
    },
    columns: [
      {
        data: "id",
        orderable: false,
        render: function (data) {
          return `<button class="btn btn-sm btn-transparent"
                  onclick="edit('${data}');"
                  title="Editar" data-toggle="modal">
              <i class="feather icon-edit"></i>
            </button>`;
        },
      },
      { data: "name", defaultContent: "" },
      {
        data: "categoria",
        defaultContent: "",
        render: function (data, type, row) {
          if (data) return data;
          if (row.tbl_factores_gobernacion_id == 10000) return "General (Todos)";
          return "Sin categoría";
        },
      },
      {
        data: "tipo",
        defaultContent: "",
        render: function (data) {
          if (parseInt(data, 10) === 2) {
            return '<span class="badge badge-info">Final</span>';
          }
          return '<span class="badge badge-primary">Inicial</span>';
        },
      },
      { data: "tipo_medicion" },
      { data: "rango_desde" },
      { data: "rango_hasta" },
      {
        data: "color",
        orderable: false,
        render: function (data) {
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
  $("#customSearch")
    .off()
    .on("keyup", function () {
      if (tablaConfiguracion) {
        tablaConfiguracion.search(this.value).draw();
      }
    });
  $("#dynamictable_length select").addClass(
    "form-control form-control-sm w-auto d-inline-block mx-2"
  );
}

function cargarCategoriasInestabilidad() {
  $.ajax({
    url: "admin/controllers/configuracionCtrl.php",
    type: "POST",
    data: JSON.stringify({ method: "categoriasInestabilidad", data: "" }),
    contentType: "application/json",
    dataType: "json",
    success: function (data) {
      const $select = $("#factorGobernacionId");
      const $selectEdit = $("#factorGobernacionIdEdit");

      $select.empty().append('<option value="">Seleccione</option>');
      $selectEdit.empty().append('<option value="">Seleccione</option>');
      $select.append('<option value="10000">General (Todos)</option>');
      $selectEdit.append('<option value="10000">General (Todos)</option>');

      if (data.output.valid && data.output.response.length > 0) {
        data.output.response.forEach((item) => {
          const option = `<option value="${item.id}">${item.nombre_categoria}</option>`;
          $select.append(option);
          $selectEdit.append(option);
        });
        $select.prop("disabled", false);
        $selectEdit.prop("disabled", false);
      } else {
        $select.prop("disabled", true);
        $selectEdit.prop("disabled", true);
        UTIL.mostrarMensajeError("No se encontraron factores de inestabilidad.");
      }
    },
    error: function () {
      UTIL.mostrarMensajeError("Error al cargar los factores de inestabilidad.");
    },
  });
}

function edit(id) {
  UTIL.cursorBusy();

  $.ajax({
    url: "admin/controllers/configuracionCtrl.php",
    type: "POST",
    data: JSON.stringify({ method: "editPuntaje", data: id }),
    contentType: "application/json",
    dataType: "json",
    success: function (data) {
      UTIL.cursorNormal();

      if (data.state) {
        const res = data.data[0];

        $("#idPuntajeEdit").val(res.id);
        $("#nameEdit").val(res.name || "");
        $("#factorGobernacionIdEdit").val(res.tbl_factores_gobernacion_id);
        $("#tipoEdit").val(res.tipo || 1);
        $("#tipo_medicionEdit").val(res.tipo_medicion);
        $("#colorEdit").val(res.color);
        $("#desdeEdit").val(res.rango_desde);
        $("#hastaEdit").val(res.rango_hasta);
        $("#colorBoxEdit").css("background-color", res.color);

        $("#modalPuntaje").modal("show");
      } else {
        UTIL.mostrarMensajeError(data.message);
      }
    },
    error: function () {
      UTIL.cursorNormal();
      UTIL.mostrarMensajeError("Error al obtener el puntaje.");
    },
  });
}

function save() {
  const msj = "Falta ingresar información obligatoria, marcada con asterisco.";

  const camposRequeridos = [
    "#name",
    "#factorGobernacionId",
    "#tipo",
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
    op: "configuracionpuntajesave",
    id: $("#idPuntaje").val(),
    name: $("#name").val().trim(),
    factorGobernacionId: $("#factorGobernacionId").val(),
    tipo: $("#tipo").val(),
    desde: $("#desde").val(),
    hasta: $("#hasta").val(),
    tipo_medicion: $("#tipo_medicion").val(),
    color: $("#color").val(),
  };

  UTIL.callAjaxRqstPOST(datos, savehandler);
}

function editSave() {
  const msj = "Falta ingresar información obligatoria, marcada con asterisco.";

  const camposRequeridos = [
    "#nameEdit",
    "#factorGobernacionIdEdit",
    "#tipoEdit",
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
    op: "configuracionpuntajesave",
    id: $("#idPuntajeEdit").val(),
    name: $("#nameEdit").val().trim(),
    factorGobernacionId: $("#factorGobernacionIdEdit").val(),
    tipo: $("#tipoEdit").val(),
    desde: $("#desdeEdit").val(),
    hasta: $("#hastaEdit").val(),
    tipo_medicion: $("#tipo_medicionEdit").val(),
    color: $("#colorEdit").val(),
  };

  UTIL.callAjaxRqstPOST(datos, savehandler);
}

function savehandler(data) {
  UTIL.cursorNormal();

  if (data.output.valid) {
    UTIL.mostrarMensajeExitoso("Información guardada correctamente");
    UTIL.clearForm("formEdit");
    UTIL.clearForm("formupuntajes");
    cargaData();
    $("#modalPuntaje").modal("hide");
    resetColorBoxes();
  } else {
    UTIL.mostrarMensajeError(
      data.output.response.content || data.output.response || "Error al guardar la información"
    );
  }
}

function updateColorBox() {
  const color = document.getElementById("color").value;
  const colorBox = document.getElementById("colorBox");
  if (color) {
    colorBox.style.backgroundColor = color;
    colorBox.innerText = " ";
  } else {
    colorBox.style.backgroundColor = "#0b0f1a";
    colorBox.innerText = " ";
  }
}

function updateColorBoxEdit() {
  const color = document.getElementById("colorEdit").value;
  const colorBox = document.getElementById("colorBoxEdit");
  if (color) {
    colorBox.style.backgroundColor = color;
    colorBox.innerText = " ";
  } else {
    colorBox.style.backgroundColor = "#0b0f1a";
    colorBox.innerText = " ";
  }
}

function resetColorBoxes() {
  document.getElementById("colorBox").style.backgroundColor = "#0b0f1a";
  document.getElementById("colorBoxEdit").style.backgroundColor = "#0b0f1a";
}
