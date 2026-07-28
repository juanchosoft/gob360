$(document).on('ready', init);
var q;
let selectedMunicipio;
let MUN;
let updateHistoryDebounce;
let isUpdating = false;

function init() {
    q = {};
}

var ESTADO_MUN_PAE = {
    updateUrlMunicipio: function (item) {

        const selectedMunicipio = item.value || MUN;

        // Validar si el valor seleccionado ya está en la URL
        const currentUrl = new URL(window.location.href);
        const actualMunicipio = currentUrl.searchParams.get('mun');
        if (selectedMunicipio === actualMunicipio) return; // Evitar cambios innecesarios

        currentUrl.searchParams.set('mun', selectedMunicipio);
        window.history.pushState({}, '', currentUrl);

        window.location = currentUrl;

        ESTADO_MUN_PAE.loadContentidoMapa(currentUrl);
    },

    updateUrlMunicipio: function (item) {
        if (isUpdating) return; // Prevenir loop infinito

        const selectedMunicipio = item.value || MUN;

        // Validar si el valor seleccionado ya está en la URL
        const currentUrl = new URL(window.location.href);
        const actualMunicipio = currentUrl.searchParams.get('mun');
        if (selectedMunicipio === actualMunicipio) return; // Evitar cambios innecesarios

        // Actualizar el valor del select y evitar loop infinito
        ESTADO_MUN_PAE.updateSelectWithoutTrigger("#tbl_municipio_id", selectedMunicipio);

        // Debounce para limitar llamadas a pushState
        clearTimeout(updateHistoryDebounce);
        updateHistoryDebounce = setTimeout(() => {
            currentUrl.searchParams.set('mun', selectedMunicipio);
            window.history.pushState({}, '', currentUrl);

            ESTADO_MUN_PAE.loadContentidoMapa(currentUrl);
        }, 500);
    },
    updateSelectWithoutTrigger: function (selectId, value) {
        const selectElement = $(selectId);

        isUpdating = true; // Activar flag para evitar loop
        selectElement.off("change"); // Desactivar temporalmente eventos onchange
        selectElement.val(value).trigger("change"); // Actualizar valor
        selectElement.on("change", function () {
            ESTADO_MUN_PAE.updateUrlMunicipio(this); // Restaurar evento
        });

        setTimeout(() => {
            isUpdating = false; // Desactivar flag después de un breve retraso
        }, 300);
    },
    loadContentidoMapa: function (url) {
        $.ajax({
            url: url.toString(),
            type: "GET",
            success: function (response) {
                const updatedContent = $(response).find("#tablaContenidoPae").html();
                $("#tablaContenidoPae").html(updatedContent);
            },
            error: function (error) {
                console.error("Error al cargar contenido:", error);
            }
        });
    }
}
