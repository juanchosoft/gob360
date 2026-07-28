$(document).on("ready", init);

var q;
let MUN = null;              // fallback si lo necesitas más adelante
let selectedMunicipio = null;

function init() {
  q = {};

  // Auto-detect municipio desde la URL y setearlo si el select ya tiene opciones
  selectedMunicipio = UTIL?.getParamsFromUrlDepartamentoMunicipio?.()?.mun || null;

  // Cuando el select tenga valor (si ya fue cargado por DEPARTAMENTO.js), lo sincronizamos
  const $sel = $("#tbl_municipio_id");
  if ($sel.length && selectedMunicipio) {
    // Si el option existe ya, lo selecciona sin disparar reload
    const exists = $sel.find(`option[value="${selectedMunicipio}"]`).length > 0;
    if (exists) $sel.val(selectedMunicipio);
  }
}

var ESTADO_MUN_GOBER = {
  /**
   * Actualiza la URL al cambiar municipio SIN repetir recargas.
   * Mantiene el resto de query params.
   */
  updateUrlMunicipio: function (item) {
    const nextMun = (item && item.value) ? String(item.value) : (MUN ? String(MUN) : "");
    if (!nextMun) return;

    // URL actual
    const url = new URL(window.location.href);

    // Si ya está el mismo municipio, no hace nada
    const currentMun = url.searchParams.get("mun");
    if (currentMun === nextMun) return;

    // Actualiza parámetro mun
    url.searchParams.set("mun", nextMun);

    // UX: reemplazar (no ensucia historial)
    // Nota: replace con string para compatibilidad total
    window.location.replace(url.toString());
  },

  /**
   * Helper: permite forzar municipio desde código si lo necesitas (sin tocar HTML)
   */
  setMunicipio: function (munCode) {
    const $sel = $("#tbl_municipio_id");
    if (!$sel.length) return;

    const code = String(munCode || "");
    if (!code) return;

    // Si el option existe, setea y dispara el onchange normal
    const exists = $sel.find(`option[value="${code}"]`).length > 0;
    if (exists) {
      $sel.val(code);
      // Dispara el flujo estándar
      ESTADO_MUN_GOBER.updateUrlMunicipio($sel[0]);
    }
  }
};
