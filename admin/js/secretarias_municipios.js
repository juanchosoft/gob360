$(function () {
  cargaData();
  function cargaData() {
    // Obtener municipio del usuario para filtrar (si es Alcalde)
    const municipioUsuario = $("#municipioUsuario").val();

    secretariaMunicipal = $("#dynamictable").DataTable({
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
        url: "./admin/controllers/secretariasMunicipiosCtrl.php",
        type: "POST",
        contentType: "application/json",
        data: function (d) {
          // Agregar filtro de municipio si es Alcalde
          d.municipio_filtro = municipioUsuario;
          return JSON.stringify({
            method: "load",
            data: d,
          });
        },
        dataSrc: function (json) {
          // Validar que la respuesta tenga el formato esperado por DataTables
          if (json && json.data) {
            return json.data;
          }
          return [];
        },
        error: function (xhr, error, thrown) {
          console.error("Error al cargar secretarías:", error, thrown);
        },
      },
      columns: [
        {
          data: "id",
        },
        {
          data: "id",
          render: function (data, type, row) {
            return `<button class="btn btn-sm btn-transparent editar-informacion"
                  onclick="editSecretaria('${data}');"
                  title="Editar" data-toggle="modal">
              <i class="feather icon-edit"></i>
            </button>`;
          },
        },
        {
          data: "municipio",
          render: function (data, type, row) {
            return data || 'N/A';
          },
        },
        {
          data: "secretaria",
        },
        {
          data: "secretario",
        },
        {
          data: "correo",
        },
        {
          data: "habilitado",
        },
      ],
    });
    $("#customSearch")
      .off()
      .on("keyup", function () {
        if (secretariaMunicipal) {
          secretariaMunicipal.search(this.value).draw();
        }
      });
  }
});

function ingresarSecretaria() {
  $("#formNewSecretaria")[0].reset();
  $("#editId").val("");
  $(".modal-title").text("Ingresar Nueva Secretaría Municipal");
  $("#btnSaveSecretaria")
    .text("Guardar")
    .attr("onclick", "saveNewSecretaria();");

  // Obtener datos del usuario
  const isAdmin = $("#isAdmin").val() === "1";
  const municipioUsuario = $("#municipioUsuario").val();

  // Si es Alcalde, pre-seleccionar su municipio y deshabilitar los campos
  if (!isAdmin && municipioUsuario) {
    // Obtener el departamento (68 - Santander)
    const codigoDepartamento = $("#tbl_departamento_id").val();
    $("#tbl_departamento_id").val(codigoDepartamento).prop("disabled", true);

    // Cargar municipios y luego seleccionar el del alcalde
    var q = {};
    q.op = "ciudadget";
    q.codigo_departamento = codigoDepartamento;

    $.ajax({
      url: "./admin/ajax/rqst.php",
      type: "POST",
      data: q,
      dataType: "json",
      success: function(data) {
        if (data.output.valid) {
          let info = '<option value="seleccione">Seleccione</option>';
          data.output.response.forEach(function(municipio) {
            const selected = municipio.codigo_muncipio === municipioUsuario ? 'selected' : '';
            info += `<option value="${municipio.codigo_muncipio}" ${selected}>${municipio.codigo_muncipio} - ${municipio.municipio}</option>`;
          });
          $("#tbl_municipio_id").html(info);
          $("#tbl_municipio_id").val(municipioUsuario).prop("disabled", true);

          // Abrir modal después de cargar los municipios
          $("#newModalSecretaria").modal("show");
        }
      }
    });
  } else {
    // Si es Admin, permitir seleccionar cualquier municipio
    $("#tbl_departamento_id").prop("disabled", false);
    $("#tbl_municipio_id").prop("disabled", false);

    $("#newModalSecretaria").modal("show");

    // Cargar municipios del departamento seleccionado por defecto DESPUÉS de mostrar el modal
    $("#newModalSecretaria").on('shown.bs.modal', function() {
      if (typeof DEPARTAMENTO !== 'undefined' && typeof DEPARTAMENTO.getMunicipios === 'function') {
        DEPARTAMENTO.getMunicipios();
      }
    });
  }
}

function saveNewSecretaria() {
  // Habilitar temporalmente los campos para poder leer sus valores
  const deptDisabled = $("#tbl_departamento_id").prop("disabled");
  const munDisabled = $("#tbl_municipio_id").prop("disabled");

  $("#tbl_departamento_id").prop("disabled", false);
  $("#tbl_municipio_id").prop("disabled", false);

  const secretaria = $("#newSecretaria").val().trim();
  const secretario = $("#newSecretario").val().trim();
  const correo = $("#newEmail").val().trim();
  const codigo_departamento = $("#tbl_departamento_id").val();
  const codigo_municipio = $("#tbl_municipio_id").val();
  const habilitado = $("#newHabilitado").val();

  if (!codigo_departamento || codigo_departamento === "Seleccione" || codigo_departamento === "seleccione") {
    $("#tbl_departamento_id").prop("disabled", deptDisabled);
    $("#tbl_municipio_id").prop("disabled", munDisabled);
    Swal.fire({
      icon: "warning",
      title: "Campo requerido",
      text: "Por favor selecciona el departamento.",
    });
    $("#tbl_departamento_id").focus();
    return;
  }

  if (!codigo_municipio || codigo_municipio === "" || codigo_municipio === "seleccione") {
    $("#tbl_departamento_id").prop("disabled", deptDisabled);
    $("#tbl_municipio_id").prop("disabled", munDisabled);
    Swal.fire({
      icon: "warning",
      title: "Campo requerido",
      text: "Por favor selecciona el municipio.",
    });
    $("#tbl_municipio_id").focus();
    return;
  }

  if (!secretaria) {
    $("#tbl_departamento_id").prop("disabled", deptDisabled);
    $("#tbl_municipio_id").prop("disabled", munDisabled);
    Swal.fire({
      icon: "warning",
      title: "Campo requerido",
      text: "Por favor ingresa el nombre de la secretaría.",
    });
    $("#newSecretaria").focus();
    return;
  }

  if (!secretario) {
    $("#tbl_departamento_id").prop("disabled", deptDisabled);
    $("#tbl_municipio_id").prop("disabled", munDisabled);
    Swal.fire({
      icon: "warning",
      title: "Campo requerido",
      text: "Por favor ingresa el nombre del secretario.",
    });
    $("#newSecretario").focus();
    return;
  }

  if (!correo) {
    $("#tbl_departamento_id").prop("disabled", deptDisabled);
    $("#tbl_municipio_id").prop("disabled", munDisabled);
    Swal.fire({
      icon: "warning",
      title: "Campo requerido",
      text: "Por favor ingresa el correo electrónico.",
    });
    $("#newEmail").focus();
    return;
  }

  const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
  if (!emailRegex.test(correo)) {
    $("#tbl_departamento_id").prop("disabled", deptDisabled);
    $("#tbl_municipio_id").prop("disabled", munDisabled);
    Swal.fire({
      icon: "error",
      title: "Correo inválido",
      text: "Por favor ingresa un correo electrónico válido.",
    });
    $("#newEmail").focus();
    return;
  }

  if (habilitado === "Seleccione") {
    $("#tbl_departamento_id").prop("disabled", deptDisabled);
    $("#tbl_municipio_id").prop("disabled", munDisabled);
    Swal.fire({
      icon: "warning",
      title: "Campo requerido",
      text: "Por favor selecciona si está habilitado.",
    });
    $("#newHabilitado").focus();
    return;
  }

  const data = {
    secretaria,
    secretario,
    correo,
    codigo_departamento,
    codigo_municipio,
    mostrar: habilitado,
  };

  $.ajax({
    url: "./admin/controllers/secretariasMunicipiosCtrl.php",
    type: "POST",
    contentType: "application/json",
    data: JSON.stringify({
      method: "newSecretaria",
      data: data,
    }),
    dataType: "json",
    success: function (response) {
      if (response.state) {
        Swal.fire({
          icon: "success",
          title: "¡Registrado!",
          text:
            response.message || "La secretaría se ha guardado correctamente.",
          confirmButtonColor: "#3085d6",
          confirmButtonText: "Aceptar",
        });

        $("#formNewSecretaria")[0].reset();
        $("#newModalSecretaria").modal("hide");

        // Restaurar estado de los campos antes de cerrar
        $("#tbl_departamento_id").prop("disabled", false);
        $("#tbl_municipio_id").prop("disabled", false);

        if (typeof secretariaMunicipal !== "undefined") {
          secretariaMunicipal.ajax.reload();
        }
      } else {
        // Restaurar estado de los campos en caso de error
        $("#tbl_departamento_id").prop("disabled", deptDisabled);
        $("#tbl_municipio_id").prop("disabled", munDisabled);

        Swal.fire({
          icon: "error",
          title: "Error al guardar",
          text:
            response.message || "Ocurrió un problema al guardar la secretaría.",
          confirmButtonColor: "#d33",
          confirmButtonText: "Cerrar",
        });
      }
    },
    error: function () {
      // Restaurar estado de los campos en caso de error
      $("#tbl_departamento_id").prop("disabled", deptDisabled);
      $("#tbl_municipio_id").prop("disabled", munDisabled);

      Swal.fire({
        icon: "error",
        title: "Error de servidor",
        text: "No se pudo procesar la solicitud.",
      });
    },
  });
}

function editSecretaria(id) {
  $.ajax({
    url: "./admin/controllers/secretariasMunicipiosCtrl.php",
    type: "POST",
    contentType: "application/json",
    data: JSON.stringify({
      method: "editSecretaria",
      data: id,
    }),
    dataType: "json",
    success: function (response) {
      console.log(response.data[0]);
      if (response.state) {
        const secretaria = response.data[0];
        $(".modal-title").text("Editar Secretaría Municipal");
        $("#editId").val(secretaria.id);

        // Cargar departamento primero
        $("#tbl_departamento_id").val(secretaria.codigo_departamento);

        // Llenar los demás campos primero
        $("#newSecretaria").val(secretaria.secretaria);
        $("#newSecretario").val(secretaria.secretario);
        $("#newEmail").val(secretaria.correo);
        $("#newHabilitado").val(secretaria.habilitado);

        $("#btnSaveSecretaria")
          .text("Actualizar")
          .attr("onclick", `updateSecretaria(${secretaria.id});`);

        // Cargar municipios usando AJAX y luego seleccionar el municipio
        var q = {};
        q.op = "ciudadget";
        q.codigo_departamento = secretaria.codigo_departamento;

        $.ajax({
          url: "./admin/ajax/rqst.php",
          type: "POST",
          data: q,
          dataType: "json",
          success: function(data) {
            if (data.output.valid) {
              let info = '<option value="seleccione">Seleccione</option>';
              data.output.response.forEach(function(municipio) {
                const selected = municipio.codigo_muncipio === secretaria.codigo_municipio ? 'selected' : '';
                info += `<option value="${municipio.codigo_muncipio}" ${selected}>${municipio.codigo_muncipio} - ${municipio.municipio}</option>`;
              });
              $("#tbl_municipio_id").html(info);

              // Verificar si es Alcalde - si lo es, deshabilitar campos de departamento y municipio
              const isAdmin = $("#isAdmin").val() === "1";
              const municipioUsuario = $("#municipioUsuario").val();

              if (!isAdmin && municipioUsuario) {
                $("#tbl_departamento_id").prop("disabled", true);
                $("#tbl_municipio_id").prop("disabled", true);
              } else {
                $("#tbl_departamento_id").prop("disabled", false);
                $("#tbl_municipio_id").prop("disabled", false);
              }

              // Abrir el modal DESPUÉS de cargar los municipios
              $("#newModalSecretaria").modal("show");
            } else {
              $("#newModalSecretaria").modal("show");
            }
          },
          error: function() {
            $("#newModalSecretaria").modal("show");
          }
        });

      } else {
        Swal.fire({
          icon: "error",
          title: "Error al editar la secretaría",
          text:
            response.message || "Ocurrió un problema al editar la secretaría.",
          confirmButtonColor: "#d33",
          confirmButtonText: "Cerrar",
        });
      }
    },
  });
}

function updateSecretaria(id) {
  // Habilitar temporalmente los campos para poder leer sus valores
  const deptDisabled = $("#tbl_departamento_id").prop("disabled");
  const munDisabled = $("#tbl_municipio_id").prop("disabled");

  $("#tbl_departamento_id").prop("disabled", false);
  $("#tbl_municipio_id").prop("disabled", false);

  const secretaria = $("#newSecretaria").val().trim();
  const secretario = $("#newSecretario").val().trim();
  const correo = $("#newEmail").val().trim();
  const codigo_departamento = $("#tbl_departamento_id").val();
  const codigo_municipio = $("#tbl_municipio_id").val();
  const habilitado = $("#newHabilitado").val();

  if (!codigo_departamento || codigo_departamento === "Seleccione" || codigo_departamento === "seleccione") {
    $("#tbl_departamento_id").prop("disabled", deptDisabled);
    $("#tbl_municipio_id").prop("disabled", munDisabled);
    Swal.fire({
      icon: "warning",
      title: "Campo requerido",
      text: "Por favor selecciona el departamento.",
    });
    $("#tbl_departamento_id").focus();
    return;
  }

  if (!codigo_municipio || codigo_municipio === "" || codigo_municipio === "seleccione") {
    $("#tbl_departamento_id").prop("disabled", deptDisabled);
    $("#tbl_municipio_id").prop("disabled", munDisabled);
    Swal.fire({
      icon: "warning",
      title: "Campo requerido",
      text: "Por favor selecciona el municipio.",
    });
    $("#tbl_municipio_id").focus();
    return;
  }

  if (!secretaria) {
    $("#tbl_departamento_id").prop("disabled", deptDisabled);
    $("#tbl_municipio_id").prop("disabled", munDisabled);
    Swal.fire({
      icon: "warning",
      title: "Campo requerido",
      text: "Por favor ingresa el nombre de la secretaría.",
    });
    $("#newSecretaria").focus();
    return;
  }

  if (!secretario) {
    $("#tbl_departamento_id").prop("disabled", deptDisabled);
    $("#tbl_municipio_id").prop("disabled", munDisabled);
    Swal.fire({
      icon: "warning",
      title: "Campo requerido",
      text: "Por favor ingresa el nombre del secretario.",
    });
    $("#newSecretario").focus();
    return;
  }

  if (!correo) {
    $("#tbl_departamento_id").prop("disabled", deptDisabled);
    $("#tbl_municipio_id").prop("disabled", munDisabled);
    Swal.fire({
      icon: "warning",
      title: "Campo requerido",
      text: "Por favor ingresa el correo electrónico.",
    });
    $("#newEmail").focus();
    return;
  }

  const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
  if (!emailRegex.test(correo)) {
    $("#tbl_departamento_id").prop("disabled", deptDisabled);
    $("#tbl_municipio_id").prop("disabled", munDisabled);
    Swal.fire({
      icon: "error",
      title: "Correo inválido",
      text: "Por favor ingresa un correo electrónico válido.",
    });
    $("#newEmail").focus();
    return;
  }

  if (habilitado === "Seleccione") {
    $("#tbl_departamento_id").prop("disabled", deptDisabled);
    $("#tbl_municipio_id").prop("disabled", munDisabled);
    Swal.fire({
      icon: "warning",
      title: "Campo requerido",
      text: "Por favor selecciona si está habilitado.",
    });
    $("#newHabilitado").focus();
    return;
  }

  $.ajax({
    url: "./admin/controllers/secretariasMunicipiosCtrl.php",
    type: "POST",
    contentType: "application/json",
    data: JSON.stringify({
      method: "updateSecretaria",
      data: {
        id,
        secretaria,
        secretario,
        correo,
        codigo_departamento,
        codigo_municipio,
        mostrar: habilitado,
      },
    }),
    dataType: "json",
    success: function (response) {
      if (response.state) {
        Swal.fire({
          icon: "success",
          title: "Actualizado",
          text: "La secretaría fue actualizada correctamente.",
          timer: 2000,
          showConfirmButton: false,
        });

        $("#formNewSecretaria")[0].reset();
        $("#newModalSecretaria").modal("hide");

        // Restaurar estado de los campos antes de cerrar
        $("#tbl_departamento_id").prop("disabled", false);
        $("#tbl_municipio_id").prop("disabled", false);

        if (typeof secretariaMunicipal !== "undefined") {
          secretariaMunicipal.ajax.reload();
        }

        $("#btnSaveSecretaria")
          .text("Guardar")
          .attr("onclick", "saveNewSecretaria();");
      } else {
        // Restaurar estado de los campos en caso de error
        $("#tbl_departamento_id").prop("disabled", deptDisabled);
        $("#tbl_municipio_id").prop("disabled", munDisabled);

        Swal.fire({
          icon: "error",
          title: "Error",
          text: response.message || "No se pudo actualizar la secretaría.",
        });
      }
    },
    error: function () {
      // Restaurar estado de los campos en caso de error
      $("#tbl_departamento_id").prop("disabled", deptDisabled);
      $("#tbl_municipio_id").prop("disabled", munDisabled);

      Swal.fire({
        icon: "error",
        title: "Error de servidor",
        text: "No se pudo procesar la solicitud.",
      });
    },
  });
}
