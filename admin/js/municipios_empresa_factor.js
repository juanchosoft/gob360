/* Asociación N:N Empresa ↔ Factor en municipios_inestabilidad */
var EMPRESA_FACTOR = (function () {
  function esc(s) {
    return String(s == null ? "" : s)
      .replace(/&/g, "&amp;")
      .replace(/</g, "&lt;")
      .replace(/>/g, "&gt;")
      .replace(/"/g, "&quot;");
  }

  function perms() {
    return window.EF_PERMS || { view: false, create: false, update: false, delete: false };
  }

  function mun() {
    return String(window.EF_MUNICIPIO || "");
  }

  function empresasOpts(selectedId, excludePairs, factorId) {
    var html = '<option value="">Seleccione empresa</option>';
    var list = window.EF_EMPRESAS || [];
    list.forEach(function (e) {
      var blocked = false;
      if (excludePairs && factorId) {
        blocked = excludePairs.some(function (p) {
          return Number(p.tbl_empresa_id) === Number(e.id) && Number(p.tbl_factor_id) === Number(factorId);
        });
      }
      if (blocked && Number(e.id) !== Number(selectedId)) return;
      var sel = Number(e.id) === Number(selectedId) ? " selected" : "";
      html +=
        '<option value="' +
        e.id +
        '"' +
        sel +
        ">" +
        esc(e.nombre_empresa) +
        (e.nit ? " (" + esc(e.nit) + ")" : "") +
        "</option>";
    });
    return html;
  }

  function factoresOpts(selectedId, excludePairs, empresaId) {
    var html = '<option value="">Seleccione factor</option>';
    var list = window.EF_FACTORES || [];
    list.forEach(function (f) {
      var blocked = false;
      if (excludePairs && empresaId) {
        blocked = excludePairs.some(function (p) {
          return Number(p.tbl_factor_id) === Number(f.id) && Number(p.tbl_empresa_id) === Number(empresaId);
        });
      }
      if (blocked && Number(f.id) !== Number(selectedId)) return;
      var sel = Number(f.id) === Number(selectedId) ? " selected" : "";
      html += '<option value="' + f.id + '"' + sel + ">" + esc(f.nombre) + "</option>";
    });
    return html;
  }

  function showModal(id) {
    var el = document.getElementById(id);
    if (!el) return;
    // Preferir BS4 (jQuery): este proyecto no usa getOrCreateInstance (BS5).
    if (window.jQuery && jQuery.fn && typeof jQuery.fn.modal === "function") {
      jQuery(el).modal("show");
      return;
    }
    if (
      window.bootstrap &&
      window.bootstrap.Modal &&
      typeof window.bootstrap.Modal.getOrCreateInstance === "function"
    ) {
      window.bootstrap.Modal.getOrCreateInstance(el).show();
    }
  }

  function hideModal(id) {
    var el = document.getElementById(id);
    if (!el) return;
    if (window.jQuery && jQuery.fn && typeof jQuery.fn.modal === "function") {
      jQuery(el).modal("hide");
      return;
    }
    if (
      window.bootstrap &&
      window.bootstrap.Modal &&
      typeof window.bootstrap.Modal.getInstance === "function"
    ) {
      var inst = window.bootstrap.Modal.getInstance(el);
      if (inst) inst.hide();
    }
  }

  function destroySelect2($el) {
    if ($el.length && $el.hasClass("select2-hidden-accessible")) {
      try {
        $el.select2("destroy");
      } catch (e) {}
    }
  }

  function initSelect2($el, placeholder) {
    destroySelect2($el);
    if (!$el.length || typeof $el.select2 !== "function") return;
    $el.select2({
      dropdownParent: jQuery("#modalEmpresaFactorForm"),
      width: "100%",
      placeholder: placeholder || "Buscar…",
      allowClear: true,
      language: {
        noResults: function () {
          return "Sin resultados";
        },
        searching: function () {
          return "Buscando…";
        },
      },
    });
  }

  function ajax(op, data) {
    return jQuery.ajax({
      url: "admin/ajax/rqst.php",
      type: "POST",
      dataType: "json",
      data: Object.assign({ op: op }, data || {}),
    });
  }

  function toast(ok, msg) {
    if (typeof Swal !== "undefined") {
      Swal.fire({
        icon: ok ? "success" : "error",
        title: ok ? "Listo" : "Error",
        text: msg || (ok ? "Operación exitosa" : "Ocurrió un error"),
        timer: ok ? 1800 : undefined,
        showConfirmButton: !ok,
      });
      return;
    }
    alert(msg || (ok ? "OK" : "Error"));
  }

  function openFormAsociar(opts) {
    var p = perms();
    var isEdit = !!(opts && opts.id);
    if (isEdit && !p.update) {
      toast(false, "No tiene permiso para editar asociaciones.");
      return;
    }
    if (!isEdit && !p.create) {
      toast(false, "No tiene permiso para crear asociaciones.");
      return;
    }

    jQuery("#ef_id").val(opts.id || "");
    jQuery("#ef_codigo_muncipio").val(mun());
    jQuery("#ef_contexto").text(opts.contexto || "");

    var mode = opts.mode || "from_factor";
    jQuery("#ef_mode").val(mode);

    var $factorSelect = jQuery("#ef_factor_id_select");
    var $empresaSelect = jQuery("#ef_empresa_id");

    destroySelect2($factorSelect);
    destroySelect2($empresaSelect);

    // Siempre Select2 para factor y empresa (búsqueda); se preselecciona según origen.
    jQuery("#ef_wrap_factor_fixed").hide();
    jQuery("#ef_wrap_empresa_fixed").hide();
    jQuery("#ef_wrap_factor_select").show();
    jQuery("#ef_wrap_empresa_select").show();

    $factorSelect.html(factoresOpts(opts.factorId || "", null, null));
    $empresaSelect.html(empresasOpts(opts.empresaId || "", null, null));

    if (opts.factorId) $factorSelect.val(String(opts.factorId));
    if (opts.empresaId) $empresaSelect.val(String(opts.empresaId));

    // Contexto visual opcional (nombre completo con wrap)
    if (opts.factorNombre) {
      jQuery("#ef_factor_label").text(opts.factorNombre);
    }
    if (opts.empresaNombre) {
      jQuery("#ef_empresa_label").text(opts.empresaNombre);
    }

    jQuery("#ef_compromiso").val(opts.compromiso || "");
    jQuery("#efModalTitle").text(isEdit ? "Editar asociación" : "Asociar empresa a factor");

    var $modal = jQuery("#modalEmpresaFactorForm");
    $modal.off("shown.bs.modal.efSelect2").one("shown.bs.modal.efSelect2", function () {
      initSelect2($factorSelect, "Buscar factor…");
      initSelect2($empresaSelect, "Buscar empresa…");
      if (opts.factorId) $factorSelect.val(String(opts.factorId)).trigger("change");
      if (opts.empresaId) $empresaSelect.val(String(opts.empresaId)).trigger("change");
    });
    $modal.off("hidden.bs.modal.efSelect2").one("hidden.bs.modal.efSelect2", function () {
      destroySelect2($factorSelect);
      destroySelect2($empresaSelect);
    });

    showModal("modalEmpresaFactorForm");
  }

  function guardar() {
    var id = jQuery("#ef_id").val() || "";
    var empresaId = jQuery("#ef_empresa_id").val();
    var factorId = jQuery("#ef_factor_id_select").val();
    var compromiso = jQuery("#ef_compromiso").val() || "";

    if (!empresaId || !factorId) {
      toast(false, "Debe seleccionar empresa y factor.");
      return;
    }
    if (compromiso.length > 500) {
      toast(false, "El compromiso no puede superar 500 caracteres.");
      return;
    }

    jQuery("#btnGuardarEmpresaFactor").prop("disabled", true);
    ajax("empresafactorsave", {
      id: id,
      tbl_empresa_id: empresaId,
      tbl_factor_id: factorId,
      codigo_muncipio: mun(),
      compromiso: compromiso,
    })
      .done(function (res) {
        if (res && res.output && res.output.valid) {
          hideModal("modalEmpresaFactorForm");
          toast(true, typeof res.output.response === "string" ? res.output.response : "Guardado.");
          setTimeout(function () {
            location.reload();
          }, 900);
        } else {
          var msg =
            (res && res.output && res.output.response && res.output.response.content) ||
            (res && res.output && res.output.response) ||
            "No se pudo guardar.";
          toast(false, typeof msg === "string" ? msg : "No se pudo guardar.");
        }
      })
      .fail(function () {
        toast(false, "Error de comunicación al guardar.");
      })
      .always(function () {
        jQuery("#btnGuardarEmpresaFactor").prop("disabled", false);
      });
  }

  function renderLista(rows, tipo) {
    var p = perms();
    if (!rows || !rows.length) {
      return '<div class="ef-lista-empty">No hay asociaciones registradas.</div>';
    }

    // Mismo patrón visual que la tabla "Avance por Factor" (estilos inline, sin .table Bootstrap).
    var th =
      "padding:10px 8px;font-size:11px;font-weight:800;text-transform:uppercase;letter-spacing:.04em;" +
      "color:#ffffff;text-align:left;border:none;background:rgba(255,255,255,.04);";
    var thCenter = th + "text-align:center;";
    var td =
      "padding:10px 8px;vertical-align:middle;color:#ffffff;font-size:12px;font-weight:600;" +
      "border:none;border-bottom:1px solid rgba(255,255,255,.06);background:transparent;";
    var tdCenter = td + "text-align:center;";
    var tdNombre =
      td +
      "text-align:left;line-height:1.35;word-break:break-word;overflow-wrap:anywhere;white-space:normal;";

    var html =
      '<div style="border:1px solid rgba(255,255,255,.08);border-radius:14px;overflow:hidden;background:transparent;">' +
      '<table style="width:100%;border-collapse:collapse;background:transparent;margin:0;">' +
      "<thead><tr style=\"background:rgba(255,255,255,.04);border-bottom:2px solid rgba(255,255,255,.10);\">";

    if (tipo === "empresas") {
      html +=
        '<th style="' + th + '">Empresa</th>' +
        '<th style="' + thCenter + 'width:120px;">NIT</th>' +
        '<th style="' + th + '">Compromiso</th>' +
        '<th style="' + thCenter + 'width:110px;">Acciones</th>';
    } else {
      html +=
        '<th style="' + th + '">Factor</th>' +
        '<th style="' + th + '">Compromiso</th>' +
        '<th style="' + thCenter + 'width:110px;">Acciones</th>';
    }
    html += "</tr></thead><tbody>";

    rows.forEach(function (r) {
      var compromiso = r.compromiso
        ? esc(r.compromiso)
        : '<span style="color:rgba(255,255,255,.45);">—</span>';
      html +=
        '<tr style="background:transparent;border-bottom:1px solid rgba(255,255,255,.06);">';
      if (tipo === "empresas") {
        html +=
          '<td style="' + tdNombre + '">' + esc(r.nombre_empresa) + "</td>" +
          '<td style="' + tdCenter + '">' + esc(r.nit || "—") + "</td>" +
          '<td style="' + tdNombre + "font-weight:500;font-size:12px;color:rgba(255,255,255,.85);\">" +
          compromiso +
          "</td>";
      } else {
        html +=
          '<td style="' + tdNombre + '">' + esc(r.nombre_factor || "") + "</td>" +
          '<td style="' + tdNombre + "font-weight:500;font-size:12px;color:rgba(255,255,255,.85);\">" +
          compromiso +
          "</td>";
      }
      html += '<td style="' + tdCenter + 'white-space:nowrap;">';
      if (p.update) {
        html +=
          '<button type="button" class="btn btn-sm btn-info ef-btn-edit" data-id="' +
          r.id +
          '" title="Editar" style="margin:0 2px;"><i class="feather icon-edit"></i></button>';
      }
      if (p.delete) {
        html +=
          '<button type="button" class="btn btn-sm btn-danger ef-btn-del" data-id="' +
          r.id +
          '" title="Eliminar" style="margin:0 2px;"><i class="feather icon-trash-2"></i></button>';
      }
      if (!p.update && !p.delete) {
        html += '<span style="color:rgba(255,255,255,.45);">—</span>';
      }
      html += "</td></tr>";
    });

    html += "</tbody></table></div>";
    return html;
  }

  function verEmpresasFactor(factorId, factorNombre) {
    if (!perms().view) {
      toast(false, "No tiene permiso para ver asociaciones.");
      return;
    }
    jQuery("#efListaTitulo").text("Empresas asociadas — " + (factorNombre || "Factor"));
    jQuery("#efListaBody").html('<div class="text-center p-3">Cargando…</div>');
    jQuery("#efListaContext").data("tipo", "factor").data("factorId", factorId).data("factorNombre", factorNombre || "");
    showModal("modalEmpresaFactorLista");

    ajax("empresafactorgetbyfactor", {
      tbl_factor_id: factorId,
      codigo_muncipio: mun(),
    }).done(function (res) {
      var rows = res && res.output && res.output.valid ? res.output.response || [] : [];
      jQuery("#efListaBody").html(renderLista(rows, "empresas"));
    }).fail(function () {
      jQuery("#efListaBody").html('<div class="alert alert-danger m-2">Error al cargar.</div>');
    });
  }

  function verFactoresEmpresa(empresaId, empresaNombre) {
    if (!perms().view) {
      toast(false, "No tiene permiso para ver asociaciones.");
      return;
    }
    jQuery("#efListaTitulo").text("Factores asociados — " + (empresaNombre || "Empresa"));
    jQuery("#efListaBody").html('<div class="text-center p-3">Cargando…</div>');
    jQuery("#efListaContext")
      .data("tipo", "empresa")
      .data("empresaId", empresaId)
      .data("empresaNombre", empresaNombre || "");
    showModal("modalEmpresaFactorLista");

    ajax("empresafactorgetbyempresa", { tbl_empresa_id: empresaId }).done(function (res) {
      var rows = res && res.output && res.output.valid ? res.output.response || [] : [];
      jQuery("#efListaBody").html(renderLista(rows, "factores"));
    }).fail(function () {
      jQuery("#efListaBody").html('<div class="alert alert-danger m-2">Error al cargar.</div>');
    });
  }

  function editarDesdeLista(id) {
    ajax("empresafactorget", { id: id }).done(function (res) {
      var row = res && res.output && res.output.valid ? (res.output.response || [])[0] : null;
      if (!row) {
        toast(false, "No se encontró la asociación.");
        return;
      }
      var ctx = jQuery("#efListaContext");
      var tipo = ctx.data("tipo");
      hideModal("modalEmpresaFactorLista");
      if (tipo === "empresa") {
        openFormAsociar({
          id: row.id,
          mode: "from_empresa",
          empresaId: row.tbl_empresa_id,
          empresaNombre: row.nombre_empresa,
          factorId: row.tbl_factor_id,
          compromiso: row.compromiso || "",
          contexto: "Editar compromiso / factor de la empresa",
        });
      } else {
        openFormAsociar({
          id: row.id,
          mode: "from_factor",
          factorId: row.tbl_factor_id,
          factorNombre: row.nombre_factor,
          empresaId: row.tbl_empresa_id,
          compromiso: row.compromiso || "",
          contexto: "Editar compromiso / empresa del factor",
        });
      }
    });
  }

  function eliminar(id) {
    var p = perms();
    if (!p.delete) {
      toast(false, "No tiene permiso para eliminar.");
      return;
    }
    var ask = function () {
      return ajax("empresafactordelete", { id: id }).done(function (res) {
        if (res && res.output && res.output.valid) {
          toast(true, "Asociación eliminada.");
          setTimeout(function () {
            location.reload();
          }, 800);
        } else {
          toast(false, "No se pudo eliminar.");
        }
      });
    };
    if (typeof Swal !== "undefined") {
      Swal.fire({
        title: "¿Eliminar asociación?",
        text: "Solo se elimina esta relación empresa-factor. Las demás se conservan.",
        icon: "warning",
        showCancelButton: true,
        confirmButtonColor: "#dc3545",
        confirmButtonText: "Sí, eliminar",
        cancelButtonText: "Cancelar",
      }).then(function (r) {
        if (r.isConfirmed) ask();
      });
      return;
    }
    if (confirm("¿Eliminar esta asociación?")) ask();
  }

  function bind() {
    jQuery(document).on("click", ".ef-asociar-factor", function () {
      openFormAsociar({
        mode: "from_factor",
        factorId: jQuery(this).data("factor-id"),
        factorNombre: jQuery(this).data("factor-nombre"),
        contexto: "Indique opcionalmente a qué se compromete la empresa con este factor.",
      });
    });
    jQuery(document).on("click", ".ef-asociar-empresa", function () {
      openFormAsociar({
        mode: "from_empresa",
        empresaId: jQuery(this).data("empresa-id"),
        empresaNombre: jQuery(this).data("empresa-nombre"),
        contexto: "Indique opcionalmente a qué se compromete la empresa con el factor.",
      });
    });
    jQuery(document).on("click", ".ef-ver-empresas", function () {
      verEmpresasFactor(jQuery(this).data("factor-id"), jQuery(this).data("factor-nombre"));
    });
    jQuery(document).on("click", ".ef-ver-factores", function () {
      verFactoresEmpresa(jQuery(this).data("empresa-id"), jQuery(this).data("empresa-nombre"));
    });
    jQuery(document).on("click", ".ef-btn-edit", function () {
      editarDesdeLista(jQuery(this).data("id"));
    });
    jQuery(document).on("click", ".ef-btn-del", function () {
      eliminar(jQuery(this).data("id"));
    });
    jQuery("#btnGuardarEmpresaFactor").on("click", guardar);
  }

  return { bind: bind, openFormAsociar: openFormAsociar };
})();

jQuery(function () {
  EMPRESA_FACTOR.bind();
});
