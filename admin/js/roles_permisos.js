let catalogoPermisos = {};
let rolesData = [];

$(function () {
  cargarCatalogoPermisos()
    .catch(function () {
      catalogoPermisos = {};
    })
    .always(cargarRoles);

  $("#customSearchRoles").on("keyup", function () {
    renderTablaRoles();
  });
});

function getRolesFiltrados() {
  const q = ($("#customSearchRoles").val() || "").trim().toLowerCase();
  if (!q) {
    return rolesData;
  }
  return rolesData.filter(function (rol) {
    const haystack = [
      rol.name,
      rol.role_key,
      rol.description,
    ].join(" ").toLowerCase();
    return haystack.indexOf(q) !== -1;
  });
}

function renderTablaRoles() {
  const $tbody = $("#tablaRoles tbody");
  $tbody.empty();

  const lista = getRolesFiltrados();

  if (!lista.length) {
    $tbody.append(
      '<tr><td colspan="6" class="text-center au-empty-row py-4">No hay roles para mostrar</td></tr>'
    );
    return;
  }

  lista.forEach((rol) => {
    const acciones = window.ROLES_PERMISOS.canManage
      ? `<button type="button" class="btn btn-icon-table" onclick="editarRol(${rol.id})" title="Editar"><i class="feather icon-edit"></i></button>
         ${parseInt(rol.is_system, 10) === 0 ? `<button type="button" class="btn btn-icon-table text-danger" onclick="eliminarRol(${rol.id})" title="Eliminar"><i class="feather icon-trash-2"></i></button>` : ""}`
      : `<button type="button" class="btn btn-icon-table" onclick="editarRol(${rol.id})" title="Ver"><i class="feather icon-eye"></i></button>`;

    $tbody.append(`
      <tr>
        <td class="text-nowrap">${acciones}</td>
        <td>${escapeHtml(rol.name)}</td>
        <td><code>${escapeHtml(rol.role_key)}</code></td>
        <td>${parseInt(rol.is_system, 10) === 1 ? '<span class="badge badge-system">Sistema</span>' : '<span class="badge badge-custom">Personalizado</span>'}</td>
        <td>${rol.permisos_count || 0}</td>
        <td>${rol.usuarios_count || 0}</td>
      </tr>
    `);
  });

  if (typeof feather !== "undefined") {
    feather.replace();
  }
}

function parseRqstResponse(res) {
  if (typeof res === "string") {
    try {
      return JSON.parse(res);
    } catch (e) {
      return null;
    }
  }
  return res;
}

function isValidOutput(data) {
  return !!(data && data.output && (data.output.valid === true || data.output.valid === 1 || data.output.valid === "1"));
}

function cargarCatalogoPermisos() {
  return $.post("admin/ajax/rqst.php", { op: "rolepermissionscatalog" }).then(function (res) {
    const data = parseRqstResponse(res);
    if (!isValidOutput(data)) {
      const msg = (data && data.output && data.output.response)
        ? data.output.response
        : "No se pudo cargar el catálogo de permisos";
      Swal.fire("Error", msg, "error");
      catalogoPermisos = {};
      return;
    }
    catalogoPermisos = data.output.response || {};
    let withId = 0;
    Object.keys(catalogoPermisos).forEach((module) => {
      (catalogoPermisos[module] || []).forEach((perm) => {
        if (parseInt(perm.id || 0, 10) > 0) withId++;
      });
    });
    if (withId === 0) {
      Swal.fire(
        "Catálogo inválido",
        "El catálogo de permisos no trajo IDs de base de datos. No edite roles hasta recargar.",
        "error"
      );
    }
  });
}

function cargarRoles() {
  $.post("admin/ajax/rqst.php", { op: "roleslist" })
    .done(function (res) {
      const data = parseRqstResponse(res);
      if (!isValidOutput(data)) {
        const msg = (data && data.output && data.output.response) ? data.output.response : "No se pudieron cargar los roles";
        Swal.fire("Error", msg, "error");
        return;
      }
      rolesData = data.output.response || [];
      renderTablaRoles();
    })
    .fail(function (xhr) {
      let msg = "No se pudieron cargar los roles";
      try {
        const data = parseRqstResponse(xhr.responseText);
        if (data && data.output && data.output.response) {
          msg = data.output.response;
        }
      } catch (e) { /* ignore */ }
      Swal.fire("Error", msg, "error");
    });
}

function nuevoRol() {
  $("#roleId").val(0);
  $("#roleName").val("");
  $("#roleKey").val("").prop("readonly", false);
  $("#roleDescription").val("");
  $("#modalRolTitulo").text("Nuevo rol");
  renderPermisosCheckboxes([]);
  $("#modalRol").modal("show");
}

function editarRol(id) {
  $.post("admin/ajax/rqst.php", { op: "roleget", id: id }, function (res) {
    const data = parseRqstResponse(res);
    if (!isValidOutput(data)) {
      Swal.fire("Error", "No se pudo cargar el rol", "error");
      return;
    }
    const rol = data.output.response;
    $("#roleId").val(rol.id);
    $("#roleName").val(rol.name);
    $("#roleKey").val(rol.role_key);
    $("#roleDescription").val(rol.description || "");
    $("#roleKey").prop("readonly", parseInt(rol.is_system, 10) === 1);
    $("#modalRolTitulo").text(parseInt(rol.is_system, 10) === 1 ? "Editar rol sistema" : "Editar rol");
    renderPermisosCheckboxes(rol.permission_ids || []);
    $("#modalRol").modal("show");
  });
}

function renderPermisosCheckboxes(selectedIds) {
  const selected = new Set((selectedIds || []).map((id) => parseInt(id, 10)));
  const $container = $("#permisosContainer");
  $container.empty();

  Object.keys(catalogoPermisos).sort().forEach((module) => {
    const items = catalogoPermisos[module] || [];
    const moduleId = module.replace(/[^a-z0-9_]/gi, "_");
    let checks = "";
    items.forEach((perm) => {
      const checked = selected.has(parseInt(perm.id || 0, 10)) ? "checked" : "";
      const permId = perm.id || "";
      checks += `
        <div class="perm-item">
          <input type="checkbox" class="perm-check" data-perm-id="${permId}" id="perm_${permId}" value="${permId}" ${checked}
            ${window.ROLES_PERMISOS.canManage ? "" : "disabled"}>
          <label for="perm_${permId}">${escapeHtml(perm.name)} <code>${escapeHtml(perm.key)}</code></label>
        </div>`;
    });

    $container.append(`
      <div class="perm-module" id="mod_${moduleId}">
        <div class="perm-module-header" onclick="toggleModulo('${moduleId}')">${escapeHtml(module)} (${items.length})</div>
        <div class="perm-module-body">${checks}</div>
      </div>
    `);
  });
}

function toggleModulo(moduleId) {
  $(`#mod_${moduleId}`).toggleClass("open");
}

function toggleTodosPermisos(checked) {
  $(".perm-check").prop("checked", checked);
}

function guardarRol() {
  const permissionIds = [];
  let catalogHasIds = false;
  $(".perm-check").each(function () {
    const id = parseInt($(this).val(), 10);
    if (id > 0) {
      catalogHasIds = true;
      if ($(this).is(":checked")) {
        permissionIds.push(id);
      }
    }
  });

  if (!catalogHasIds) {
    Swal.fire(
      "Catálogo inválido",
      "Los permisos no tienen ID de base de datos. Recargue la página e intente de nuevo.",
      "error"
    );
    return;
  }

  const payload = {
    op: "rolesave",
    id: $("#roleId").val(),
    name: $("#roleName").val().trim(),
    role_key: $("#roleKey").val().trim(),
    description: $("#roleDescription").val().trim(),
    permission_ids: permissionIds.join("-"),
  };

  if (!payload.name || !payload.role_key) {
    Swal.fire("Validación", "Nombre y clave del rol son obligatorios", "warning");
    return;
  }

  if (payload.id && permissionIds.length === 0) {
    Swal.fire(
      "Validación",
      "Debe dejar al menos un permiso marcado. Vaciar el rol no está permitido desde aquí.",
      "warning"
    );
    return;
  }

  $.post("admin/ajax/rqst.php", payload, function (res) {
    const data = typeof res === "string" ? JSON.parse(res) : res;
    if (data.output && data.output.valid) {
      Swal.fire("Guardado", "Rol actualizado correctamente", "success");
      $("#modalRol").modal("hide");
      cargarRoles();
      return;
    }
    const msg = (data.output && data.output.response) ? data.output.response : "No se pudo guardar";
    Swal.fire("Error", msg, "error");
  });
}

function eliminarRol(id) {
  Swal.fire({
    title: "¿Eliminar rol?",
    text: "Esta acción no se puede deshacer",
    icon: "warning",
    showCancelButton: true,
    confirmButtonText: "Eliminar",
    cancelButtonText: "Cancelar",
  }).then((result) => {
    if (!result.isConfirmed) return;
    $.post("admin/ajax/rqst.php", { op: "roledelete", id: id }, function (res) {
      const data = typeof res === "string" ? JSON.parse(res) : res;
      if (data.output && data.output.valid) {
        Swal.fire("Eliminado", "Rol eliminado", "success");
        cargarRoles();
        return;
      }
      const msg = (data.output && data.output.response) ? data.output.response : "No se pudo eliminar";
      Swal.fire("Error", msg, "error");
    });
  });
}

function escapeHtml(str) {
  return String(str || "")
    .replace(/&/g, "&amp;")
    .replace(/</g, "&lt;")
    .replace(/>/g, "&gt;")
    .replace(/"/g, "&quot;");
}
