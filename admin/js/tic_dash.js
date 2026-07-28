$(function () {
    const urlParams = new URLSearchParams(window.location.search);
    const munParam = urlParams.get('mun');
    if (munParam) {
        MUN = munParam;
        waitForMunicipioSelectToHaveOption(munParam, () => {
            $("#tbl_municipio_id").val(munParam).trigger("change");
        });
    }
    const opcionParam = urlParams.get('opcion');
    if (opcionParam) {
        OPCION = opcionParam;
        waitForMunicipioSelectToHaveOption(opcionParam, () => {
            $("#opcion").val(opcionParam).trigger("change");
        });
    }
});

let updateHistoryDebounce;
let MUN, OPCION;
let isUpdating = false;

function waitForMunicipioSelectToHaveOption(valueToCheck, callback) {
    const interval = setInterval(() => {
        const select = $("#tbl_municipio_id");
        if (select.length && select.find(`option[value="${valueToCheck}"]`).length > 0) {
            clearInterval(interval);
            callback();
        }
    }, 100);
}

const TIC_DASHBOARD = {
    updateUrlMunicipio(item) {
        if (isUpdating) return;

        const selectedMunicipio = item.value || MUN;
        const currentUrl = new URL(window.location.href);
        const actualMun = currentUrl.searchParams.get('mun');

        if (selectedMunicipio === actualMun) return;

        this.updateSelectWithoutTrigger("#tbl_municipio_id", selectedMunicipio);

        clearTimeout(updateHistoryDebounce);
        updateHistoryDebounce = setTimeout(() => {
            currentUrl.searchParams.set('mun', selectedMunicipio);
            window.history.pushState({}, '', currentUrl);

            console.log("URL actualizada:", currentUrl.toString());
            this.loadContenido(currentUrl);
        }, 700);
    },

    updateSelectWithoutTrigger(selectId, value) {
        const selectElement = $(selectId);

        isUpdating = true;
        selectElement.off("change");
        selectElement.val(value).trigger("change");
        selectElement.on("change", (event) => this.updateUrlMunicipio(event.target));

        setTimeout(() => {
            isUpdating = false;
        }, 300);
    },

    loadContenido(url) {
        $.ajax({
            url: url.toString(),
            type: "GET",
            success(response) {
                const $response = $(response);
                $("#containerDataTic").html($response.find("#containerDataTic").html());
                $("#contenido-mapa").html($response.find("#contenido-mapa").html());
            },
            error(error) {
                console.error("Error al cargar contenido:", error);
            }
        });
    },
    updateUrlOpcion(item) {
        if (isUpdating) return;

        const selectedOpcion = item.value || OPCION;
        const currentUrl = new URL(window.location.href);
        const actualOpcion = currentUrl.searchParams.get('opcion');

        if (selectedOpcion === actualOpcion) return;

        this.updateSelectWithoutTrigger("#opcion", selectedOpcion);

        clearTimeout(updateHistoryDebounce);
        updateHistoryDebounce = setTimeout(() => {
            currentUrl.searchParams.set('opcion', selectedOpcion);
            window.history.pushState({}, '', currentUrl);
            this.loadContenido(currentUrl);
        }, 700);
    },
};
