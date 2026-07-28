$(document).on('ready', init);
var q;
let MUN;
let selectedMunicipio;
function init() {
    q = {};
}

var ESTADO_MUN_GESTORA = {
    updateUrlMunicipio: function (item) {
        const selectedMunicipio = item.value || MUN;

        const currentUrl = new URL(window.location.href);
        const actualMunicipio = currentUrl.searchParams.get('mun');
        if (selectedMunicipio === actualMunicipio) return;

        currentUrl.searchParams.set('mun', selectedMunicipio);
        // Conserva tipo (y dep) si ya vienen en la URL
        window.location = currentUrl;
    },

    updateUrlTipo: function (item) {
        const selectedTipo = item.value || 'ambos';
        const currentUrl = new URL(window.location.href);
        const actualTipo = currentUrl.searchParams.get('tipo') || 'ambos';
        if (selectedTipo === actualTipo) return;

        currentUrl.searchParams.set('tipo', selectedTipo);
        window.location = currentUrl;
    }
};
