$(document).ready(initusuario);
var q;

function initusuario() {
  q = {};
  USUARIO.cargaData();
  USUARIO.toggleCamposPorTipo();
  $("#tipo").on("change", function () {
    USUARIO.toggleCamposPorTipo();
  });
  $("#editTipo").on("change", function () {
    USUARIO.toggleCamposPorTipoEdit();
  });
  // Cargar municipios y secretarías al iniciar
  $('#tbl_departamento_id').val('68');
  DEPARTAMENTO.getMunicipios();
  secretarias();
}
let tablaUsuarios = null;
let modalEditUsuario = null;
var return_page = "usuarios.php";
var USUARIO = {
  /**
   * Asigna un valor a un <select>; si no existe la opción (datos legacy), la crea.
   */
  setSelectValue: function (selector, value) {
    var $el = $(selector);
    var tipo = value == null ? "" : String(value).trim();
    if (!tipo) {
      $el.val("");
      return;
    }
    var exists = $el.find("option").filter(function () {
      return String($(this).val()) === tipo;
    }).length > 0;
    if (!exists) {
      $el.append($("<option>", { value: tipo, text: tipo }));
    }
    $el.val(tipo).trigger("change");
  },
  toggleCamposPorTipo: function () {
    const tipo = $("#tipo").val();
  
    //secretario Despacho Alcalde y Auxiliar (Secretaría Y Alcaldía)
    if (tipo === "Secretario_Despacho" || tipo === "Auxiliar") {
      $("#tbl_secretarias_id").closest(".form-group").show();
      $("#tbl_municipio_id").closest(".form-group").show();
      // Las opciones ya están cargadas por ciudades()

    //alcalde y Auxiliar Alcalde (Solo Alcaldía, NO Secretaría)
    } else if (tipo === "Alcalde" || tipo === "Auxiliar_Alcalde") {
      $("#tbl_secretarias_id").closest(".form-group").hide();
      $("#tbl_municipio_id").closest(".form-group").show();
      $("#tbl_secretarias_id").val("");

    // secretario Gobernación y Auxiliar (Solo Secretaría, NO Alcaldía)
    } else if (tipo === "Secretaria_Despacho_Gobernacion" || tipo === "Auxiliar_secret_gob") {
      $("#tbl_secretarias_id").closest(".form-group").show();
      $("#tbl_municipio_id").closest(".form-group").hide();
      $("#tbl_municipio_id").val(""); // Limpiar Alcaldía

    // gobernador, Administrador y SuperAdministrador (Ni Secretaría ni Alcaldía)
    } else if (tipo === "Gobernador" || tipo === "Administrador" || tipo === "SuperAdministrador") {
      $("#tbl_secretarias_id").closest(".form-group").hide();
      $("#tbl_municipio_id").closest(".form-group").hide();
      $("#tbl_municipio_id").val(""); // Limpiar Alcaldía
      $("#tbl_secretarias_id").val(""); // Limpiar Secretaría

    // otros
    } else {
      $("#tbl_secretarias_id").closest(".form-group").hide();
      $("#tbl_municipio_id").closest(".form-group").hide();
      $("#tbl_municipio_id").val("");
      $("#tbl_secretarias_id").val("");
    }

  },
  toggleCamposPorTipoEdit: function () {
    const tipo = $("#editTipo").val();

    //secretario Despacho Alcalde y Auxiliar (Secretaría Y Alcaldía)
    if (tipo === "Secretario_Despacho" || tipo === "Auxiliar") {
      $("#editTbl_secretarias_id").closest(".form-group").show();
      $("#editTbl_municipio_id_copy").closest(".form-group").show();
      // Las opciones ya están cargadas por ciudades()

    //alcalde y Auxiliar Alcalde (Solo Alcaldía, NO Secretaría)
    } else if (tipo === "Alcalde" || tipo === "Auxiliar_Alcalde") {
      $("#editTbl_secretarias_id").closest(".form-group").hide();
      $("#editTbl_municipio_id_copy").closest(".form-group").show();
      $("#editTbl_secretarias_id").val("");

    //secretario Gobernación y Auxiliar (Solo Secretaría, NO Alcaldía)
    } else if (tipo === "Secretaria_Despacho_Gobernacion" || tipo === "Auxiliar_secret_gob") {
      $("#editTbl_secretarias_id").closest(".form-group").show();
      $("#editTbl_municipio_id_copy").closest(".form-group").hide();
      $("#editTbl_municipio_id_copy").val(""); // Limpiar Alcaldía

    //gobernador, Administrador y SuperAdministrador (Ni Secretaría ni Alcaldía)
    } else if (tipo === "Gobernador" || tipo === "Administrador" || tipo === "SuperAdministrador") {
      $("#editTbl_secretarias_id").closest(".form-group").hide();
      $("#editTbl_municipio_id_copy").closest(".form-group").hide();
      $("#editTbl_secretarias_id").val(""); // Limpiar Secretaría
      $("#editTbl_municipio_id_copy").val(""); // Limpiar Alcaldía

    //otros
    } else {
      $("#editTbl_secretarias_id").closest(".form-group").hide();
      $("#editTbl_municipio_id_copy").closest(".form-group").hide();
      $("#editTbl_secretarias_id").val("");
      $("#editTbl_municipio_id_copy").val("");
    }

  },
  editData: function (id) {
    q = {};
    q.op = "pms_usrget";
    q.id = id;
    UTIL.callAjaxRqstPOST(q, this.editDataHandler);
  },
  editDataHandler: function (data) {
    UTIL.cursorNormal();
    if (data.output.valid) {
      var res = data.output.response[0];
      $("#id").val(res.id);
      $("#tbl_departamento_id").val(res.tbl_departamento_id);
      $("#nombre").val(res.nombre);
      $("#tbl_secretarias_id").val(res.tbl_secretarias_id);
      $("#apellido").val(res.apellido);
      $("#nickname").val(res.nickname);
      $("#nickname2").val(res.nickname);
      $("#hashpass").val("");
      $("#hashpass1").val("");
      $("#tipo").val(res.tipo);
      $("#habilitado").val(res.habilitado);
      setTimeout(function () {
        $("#tbl_municipio_id").val(res.tbl_municipio_id).trigger("change"); // Cambia el valor
      }, 1500);
    } else {
      UTIL.mostrarMensajeError(data.output.response.content);
    }
  },
  validateData: function () {
    if (typeof USER_PERMS !== 'undefined' && !USER_PERMS.create) {
      UTIL.mostrarMensajeError("No tiene permiso para crear usuarios.");
      return;
    }
    if ($("#nombre").val() == "") {
      UTIL.mostrarMensajeValidacion("Ingrese el nombre del usuario");
      $("#nombre").focus();
      return;
    }
    if ($("#apellido").val() == "") {
      UTIL.mostrarMensajeValidacion("Ingrese el apellido del usuario");
      $("#apellido").focus();
      return;
    }

    if ($("#tipo").val() == "") {
      UTIL.mostrarMensajeValidacion("Seleccione el tipo de usuario");
      $("#tipo").focus();
      return;
    }

    if ($("#nickname").val() == "") {
      UTIL.mostrarMensajeValidacion("Ingrese el campo de usuario");
      $("#nickname").focus();
      return;
    }

    if ($("#nickname").val() != "") {
      var nickname = UTIL.isEmail($("#nickname").val());
      if (!nickname) {
        UTIL.mostrarMensajeValidacion(
          "El nombre de usuario debe ser un email válido."
        );
        $("#nickname").focus();
        return;
      }
    }

    if ($("#email").val() == "") {
      UTIL.mostrarMensajeValidacion("Ingrese el correo de usuario");
      $("#email").focus();
      return;
    }

    if ($("#email").val() != "") {
      var nickname = UTIL.isEmail($("#nickname").val());
      if (!nickname) {
        UTIL.mostrarMensajeValidacion(
          "El correo de usuario debe ser un email válido."
        );
        $("#email").focus();
        return;
      }
    }

    if ($("#habilitado").val() == "") {
      UTIL.mostrarMensajeValidacion("Seleccione si esta habilitado");
      $("#habilitado").focus();
      return;
    }

    if ($("#hashpass").val() == "") {
      UTIL.mostrarMensajeValidacion("Ingrese su contraseña");
      $("#hashpass").focus();
      return;
    }

    if ($("#hashpass1").val() == "" && $("#id").val() == "") {
      UTIL.mostrarMensajeValidacion("Debe confirmar su contraseña");
      $("#hashpass1").focus();
      return;
    }

    var hashpass = $("#hashpass").val();
    var hashpass1 = $("#hashpass1").val();

    if (hashpass.length > 1) {
      if (hashpass == hashpass1) {
        $("#hashpass").val(hex_md5(hashpass));
        $("#hashpass1").val(hex_md5(hashpass1));
      } else {
        UTIL.mostrarMensajeError(
          "Las contraseñas no coinciden. Inténtelo de nuevo."
        );
        return;
      }
    }
    const secretaria = $("#tbl_secretarias_id").val() || ""; 
    const municipio = $("#tbl_municipio_id").val() || "";

    UTIL.cursorBusy();
    $("#createUser").prop("disabled", true);
    const formData = new FormData();

    formData.append("method", "ingresaUsuario");
    formData.append("id", $("#id").val());
    formData.append("nombre", $("#nombre").val());
    formData.append("apellido", $("#apellido").val());
    formData.append("nickname", $("#nickname").val());
    formData.append("nickname2", $("#nickname2").val());
    formData.append("hashpass", $("#hashpass").val());
    formData.append("hashpass1", $("#hashpass1").val());
    formData.append("tbl_secretarias_id", secretaria);
    formData.append("habilitado", $("#habilitado").val());
    formData.append("tipo", $("#tipo").val());
    formData.append("departamentoId", UTIL.getDepartamentoPrincipal());
    formData.append("tbl_municipio_id", municipio);
    formData.append("email", $("#email").val());

    const imageFile = $("#img")[0].files[0];
    if (imageFile) {
      formData.append("imagen", imageFile);
    }

    $.ajax({
      url: "admin/controllers/usuarioCtrl.php",
      type: "POST",
      data: formData,
      contentType: false,
      processData: false,
      dataType: "json",
      success: function (response) {
        if (response.state) {
          Swal.fire({
            icon: "success",
            title: "¡Usuario creado!",
            text: response.message,
            confirmButtonColor: "#28a745",
          }).then(() => {
            window.location.reload();
          });
        } else {
          UTIL.cursorNormal();
          $("#createUser").prop("disabled", false);
          Swal.fire({
            icon: "error",
            title: "¡Error!",
            text: response.message || "Ocurrió un error inesperado",
            confirmButtonColor: "#dc3545",
          });
        }
      },
      error: function (err) {
        console.log(err);
      },
    });
  },

  getSeleccionarPermisos: function () {
    if ($("#check_permisos").is(":checked")) {
      $("#formpermission :input").each(function () {
        this.checked = true;
      });
    } else {
      $("#formpermission :input").each(function () {
        this.checked = false;
      });
    }
  },
  cargaDeleted: function () {
    if (typeof currentUserType === 'undefined' || currentUserType !== 'SuperAdministrador') return;
    if ($.fn.DataTable.isDataTable('#dynamictable-deleted')) {
      $('#dynamictable-deleted').DataTable().destroy();
    }
    $('#dynamictable-deleted').DataTable({
      dom: "lrtip",
      processing: true,
      serverSide: true,
      responsive: true,
      ajax: {
        url: "./admin/controllers/usuarioCtrl.php",
        type: "POST",
        contentType: "application/json",
        data: function (d) {
          return JSON.stringify({ method: "getDeletedUsers", data: d });
        },
      },
      columns: [
        { data: "id" },
        { data: "nombre" },
        { data: "apellido" },
        { data: "nickname" },
        { data: "tipo" },
        { data: "deleted_at" },
        { data: "eliminado_por" },
      ],
    });
  },
  cargaDuplicados: function () {
    if (typeof currentUserType === 'undefined' || currentUserType !== 'SuperAdministrador') return;
    if ($.fn.DataTable.isDataTable('#dynamictable-duplicated')) {
      $('#dynamictable-duplicated').DataTable().destroy();
    }
    $('#dynamictable-duplicated').DataTable({
      dom: "lrtip",
      processing: true,
      serverSide: true,
      responsive: true,
      ajax: {
        url: "./admin/controllers/usuarioCtrl.php",
        type: "POST",
        contentType: "application/json",
        data: function (d) {
          return JSON.stringify({ method: "getDuplicatedUsers", data: d });
        },
      },
      columns: [
        { data: "id" },
        { data: "nickname" },
        { data: "nombre" },
        { data: "apellido" },
        { data: "tipo" },
        { data: "secretaria" },
        { data: "habilitado" },
        { data: "dtcreate" },
      ],
    });
  },
  cargaData: function () {
    if (tablaUsuarios !== null) {
      tablaUsuarios.destroy();
    }
    tablaUsuarios = $("#dynamictable").DataTable({
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
        url: "./admin/controllers/usuarioCtrl.php",
        type: "POST",
        contentType: "application/json",
        data: function (d) {
          return JSON.stringify({
            method: "load",
            data: d,
          });
        },
      },
      columns: [
        {
          data: "id",
          render: function (data, type, row) {
            var btns = '';
            if (typeof USER_PERMS !== 'undefined' && USER_PERMS.edit) {
              btns += `<button class="btn btn-sm btn-info editar-informacion" 
                  onclick="USUARIO.editDataNuevo('${data}');"
                  title="Editar" data-toggle="modal">
              <i class="feather icon-edit"></i>
            </button>`;
            }
            if (typeof currentUserType !== 'undefined' && currentUserType === 'SuperAdministrador') {
              btns += ` <button class="btn btn-sm btn-danger" onclick="USUARIO.deleteUser('${data}', this);" title="Eliminar">
                <i class="feather icon-trash-2"></i>
              </button>`;
            }
            return btns || '<span class="text-muted">—</span>';
          },
        },
        {
          data: "id",
          render: function (data) {
            return data;
          },
        },
        {
          data: "dtcreate",
        },
        {
          data: "nombre",
        },
        {
          data: "apellido",
        },
        {
          data: "tipo",
        },
        {
          data: "secretaria",
        },
        {
          data: "email",
        },
        {
          data: "habilitado",
        },
        {
          data: "foto",
          orderable: false,
          searchable: false,
          render: function (data, type, row) {
            if (type !== "display" || !data) return "";

            const match = data.match(/src="([^"]+)"/);
            const src = match ? match[1] : "";

            return `<img src="${src}" width="40" height="40" class="rounded-circle" style="cursor:pointer;" onclick="mostrarImagenGrande('${src}')" />`;
          },
        },
      ],
      createdRow: function (row, data, dataIndex) {
        $(row).attr("id", "fila_" + data.id);
      },
    });
    $("#customSearch")
      .off()
      .on("keyup", function () {
        if (tablaUsuarios) {
          tablaUsuarios.search(this.value).draw();
        }
      });
  },
  editDataNuevo: function (data) {
    if (typeof USER_PERMS !== 'undefined' && !USER_PERMS.edit) {
      UTIL.mostrarMensajeError("No tiene permiso para editar usuarios.");
      return;
    }
    $.ajax({
      url: "./admin/controllers/usuarioCtrl.php",
      type: "POST",
      contentType: "application/json",
      data: JSON.stringify({
        method: "editUser",
        data: data,
      }),
      dataType: "json",
      success: function (response) {
        const usuario = response.data[0];

        // Mostrar imagen si existe
        const fileName = usuario.img;
        if (fileName) {
          const encodedFileName = encodeURIComponent(fileName);
          const fullSrc = `assets/img/admin/${encodedFileName}`;
          console.log("Imagen edit:", fullSrc);

          $("#editPreviewImage").html(`
      <img src="${fullSrc}" class="img-fluid mb-2" style="max-height: 200px;" alt="Imagen actual">
    `);
        } else {
          $("#editPreviewImage").html(
            '<p class="text-muted">No hay imagen disponible.</p>'
          );
        }

        $("#editNombre").val(usuario.nombre);
        $("#editApellido").val(usuario.apellido);
        USUARIO.setSelectValue("#editTipo", usuario.tipo);
        $("#editTbl_departamento_id").val(usuario.tbl_departamento_id);
        $("#editNickname").val(usuario.nickname);
        $("#editHabilitado").val(usuario.habilitado);
        $("#editId").val(usuario.id);
        $("#editEmail").val(usuario.email);
        $("#editHashpass").val("");
        $("#editHashpass1").val("");
        document.getElementById('editImg').value = '';

        // Cargar municipios en el select del modal y setear el valor guardado
        $.ajax({
          url: "admin/ajax/rqst.php",
          type: "GET",
          dataType: "json",
          data: { op: "ciudadget", codigo_departamento: 68 },
          success: function(data) {
            if (data.output.valid) {
              var html = '<option value="">Seleccione</option>';
              data.output.response.forEach(function(c) {
                html += '<option value="' + c.codigo_muncipio + '">' + c.municipio + '</option>';
              });
              $("#editTbl_municipio_id_copy").html(html);

              const municipioId = usuario.tbl_municipio_id;
              const tieneMunicipio = municipioId && municipioId != 0;
              const tieneSecretaria = usuario.tbl_secretarias_id && usuario.tbl_secretarias_id != 0;

              // Ocultar ambos por defecto y luego mostrar según el tipo
              $("#editTbl_municipio_id_copy").closest(".form-group").hide();
              $("#editTbl_secretarias_id").closest(".form-group").hide();

              if (usuario.tipo === "Administrador" || usuario.tipo === "Gobernador" || usuario.tipo === "SuperAdministrador") {
                // No mostrar ninguno
              } else if (usuario.tipo === "Secretaria_Despacho_Gobernacion" || usuario.tipo === "Auxiliar_secret_gob") {
                // Solo secretaría
                $("#editTbl_secretarias_id").val(usuario.tbl_secretarias_id);
                $("#editTbl_secretarias_id").closest(".form-group").show();
              } else if (usuario.tipo === "Alcalde" || usuario.tipo === "Auxiliar_Alcalde") {
                // Solo municipio
                $("#editTbl_municipio_id_copy").val(String(municipioId));
                $("#editTbl_municipio_id_copy").closest(".form-group").show();
              } else if (usuario.tipo === "Secretario_Despacho" || usuario.tipo === "Auxiliar") {
                // Ambos
                if (tieneSecretaria) $("#editTbl_secretarias_id").val(usuario.tbl_secretarias_id);
                if (tieneMunicipio) $("#editTbl_municipio_id_copy").val(String(municipioId));
                $("#editTbl_secretarias_id").closest(".form-group").show();
                $("#editTbl_municipio_id_copy").closest(".form-group").show();
              }
            }
          }
        });

        modalEditUsuario = new bootstrap.Modal(
          document.getElementById("editModal")
        );
        modalEditUsuario.show();
      },
      error: function (xhr) {
        console.error("Error al cargar", xhr);
      },
    });
  },
  deleteUser: function (id, row) {
    Swal.fire({
      title: "¿Eliminar usuario?",
      text: "Esta acción no se puede deshacer.",
      icon: "warning",
      showCancelButton: true,
      confirmButtonColor: "#dc3545",
      cancelButtonColor: "#6c757d",
      confirmButtonText: "Sí, eliminar",
      cancelButtonText: "Cancelar",
    }).then(function (result) {
      if (!result.isConfirmed) return;
      $.ajax({
        url: "./admin/controllers/usuarioCtrl.php",
        type: "POST",
        contentType: "application/json",
        data: JSON.stringify({ method: "deleteUser", data: { id: id } }),
        dataType: "json",
        success: function (response) {
          if (response.state) {
            Swal.fire({
              icon: "success",
              title: "Eliminado",
              text: response.message,
              confirmButtonColor: "#28a745",
            }).then(function () {
              if (tablaUsuarios) tablaUsuarios.ajax.reload(null, false);
            });
          } else {
            Swal.fire({
              icon: "error",
              title: "Error",
              text: response.message || "Ocurrió un error",
              confirmButtonColor: "#dc3545",
            });
          }
        },
        error: function () {
          Swal.fire({
            icon: "error",
            title: "Error",
            text: "Error de conexión con el servidor",
          });
        },
      });
    });
  },
  editUserSave: function () {
    if (typeof USER_PERMS !== 'undefined' && !USER_PERMS.edit) {
      UTIL.mostrarMensajeError("No tiene permiso para editar usuarios.");
      return;
    }
    UTIL.cursorBusy();
    $("#btnGuardarEditar").prop("disabled", true);

    const errores = [];

    const nombre = $("#editNombre").val().trim();
    const apellido = $("#editApellido").val().trim();
    const tipo = $("#editTipo").val();
    const secretaria = $("#editTbl_secretarias_id").val();
    const municipio = $("#editTbl_municipio_id_copy").val();
    const departamento = $("#editTbl_departamento_id").val();
    const nickname = $("#editNickname").val().trim();
    const pass1 = $("#editHashpass").val().trim();
    const passConfirm1 = $("#editHashpass1").val().trim();
    const habilitado = $("#editHabilitado").val();
    const editId = $("#editId").val();
    const email = $("#editEmail").val().trim();

    if (!nombre) errores.push("El nombre es obligatorio.");
    if (!apellido) errores.push("El apellido es obligatorio.");
    if (!tipo || tipo === "Seleccione")
      errores.push("Debe seleccionar un tipo de usuario.");

    //AMBOS: Secretaría Y Alcaldía
    if (tipo === "Secretario_Despacho" || tipo === "Auxiliar") {
      if (secretaria === "" || secretaria === "Seleccione") {
        errores.push("Debe seleccionar una secretaría.");
      }
      if (municipio === "" || municipio === "Seleccione") {
        errores.push("Debe seleccionar una alcaldía.");
      }
    
    //SOLO Alcaldía
    } else if (tipo === "Alcalde" || tipo === "Auxiliar_Alcalde") {
      if (municipio === "" || municipio === "Seleccione") {
        errores.push("Debe seleccionar una alcaldía.");
      }
    
    //SOLO Secretaría
    } else if (tipo === "Secretaria_Despacho_Gobernacion" || tipo === "Auxiliar_secret_gob") {
      if (secretaria === "" || secretaria === "Seleccione") {
        errores.push("Debe seleccionar una secretaría.");
      }
    }

    if (!nickname) errores.push("El usuario (xxx@correo.com) es obligatorio.");
    if (!email) errores.push("El correo es obligatorio.");

    if (pass1 && passConfirm1 && pass1 !== passConfirm1)
      errores.push("Las contraseñas no coinciden.");
    if (!habilitado || habilitado === "Seleccione")
      errores.push("Seleccione un estado.");

    if (errores.length > 0) {
      $("#btnGuardarEditar").prop("disabled", false);
      Swal.fire({
        icon: "error",
        title: "Errores en el formulario",
        html: errores.map((e) => `<div>${e}</div>`).join(""),
        confirmButtonText: "Entendido",
      });
      return;
    }

    const formData = new FormData();

    formData.append("method", "editUserSave");
    formData.append("id", editId);
    formData.append("nombre", nombre);
    formData.append("apellido", apellido);
    formData.append("nickname", nickname);
    if (pass1 && passConfirm1) {
      formData.append("hashpass", hex_md5(pass1));
      formData.append("hashpass1", hex_md5(passConfirm1));
    } else {
      formData.append("hashpass", "");
      formData.append("hashpass1", "");
    }
    formData.append("tbl_secretarias_id", secretaria);
    formData.append("tbl_departamento_id", departamento);
    formData.append("tbl_municipio_id", municipio);
    formData.append("email", email);
    formData.append("tipo", tipo);
    formData.append("habilitado", habilitado);

    const imageFile = $("#editImg")[0].files[0];
    if (imageFile) {
      formData.append("imagen", imageFile);
    }

    $.ajax({
      url: "./admin/controllers/usuarioCtrl.php",
      type: "POST",
      data: formData,
      processData: false,
      contentType: false,
      dataType: "json",
      success: function (response) {
        $("#btnGuardarEditar").prop("disabled", false);
        UTIL.cursorNormal();
        if (response.state) {
          Swal.fire({
            icon: "success",
            title: "Datos actualizados",
            text: response.message,
          }).then(() => {
            if (tablaUsuarios) {
              tablaUsuarios.ajax.reload(null, false);
            }
            if (modalEditUsuario) modalEditUsuario.hide();
          });
        } else {
          Swal.fire({
            icon: "error",
            title: "Error del servidor",
            text: response.message || "Ocurrió un error inesperado",
          });
        }
      },
      error: function (xhr) {
        console.error("AJAX Error:", xhr);
      },
    });
  },
};

function mostrarImagenGrande(src) {
  document.getElementById("imagenGrande").src = src;
  new bootstrap.Modal(document.getElementById("modalImagen")).show();
}

$("#img").on("change", function () {
  const file = this.files[0];
  if (file && file.type.startsWith("image/")) {
    const reader = new FileReader();
    reader.onload = function (e) {
      $("#previewImage").html(
        `<img src="${e.target.result}" class="img-fluid" style="max-height: 150PX;">`
      );
    };
    reader.readAsDataURL(file);
  } else {
    $("#previewImage").html(`<p class="text-danger">Archivo no válido</p>`);
  }
});

$("#editImg").on("change", function () {
  const file = this.files[0];
  if (file && file.type.startsWith("image/")) {
    const reader = new FileReader();
    reader.onload = function (e) {
      $("#editPreviewImage").html(
        `<img src="${e.target.result}" class="img-fluid" style="max-height: 150PX;">`
      );
    };
    reader.readAsDataURL(file);
  } else {
    $("#editPreviewImage").html(`<p class="text-danger">Archivo no válido</p>`);
  }
});


function secretarias() {
  return new Promise((resolve) => {
    $.ajax({
      url: "./admin/controllers/utilsCtrl.php",
      type: "POST",
      data: JSON.stringify({ method: "secretaria" }),
      dataType: "json",
      contentType: "application/json",
      success: function (response) {
        const $select = $("#tbl_secretarias_id");
        const $select1 = $("#editTbl_secretarias_id");

        $select.empty().append('<option value="" selected>Seleccione</option>');
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
