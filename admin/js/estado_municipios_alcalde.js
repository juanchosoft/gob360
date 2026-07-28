$(document).on('ready', init);
var q;
let MUN;
let selectedMunicipio;
function init() {
    q = {};
}

var ESTADO_MUN_ALCALDE = {
    updateUrlMunicipio: function (item) {
        const selectedMunicipio = item?.value || MUN;
        const url = new URL(window.location.href);

        if (url.searchParams.get('mun') === selectedMunicipio) return; // Evitar recargas innecesarias

        url.searchParams.set('mun', selectedMunicipio);
        window.location.replace(url); // replace evita agregar una nueva entrada en el historial
    }

}
