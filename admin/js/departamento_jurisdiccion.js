$(document).on('ready', init);
var q;


//Initialize Select2 Elements
$('.select2').select2()

//Initialize Select2 Elements
$('.select2bs4').select2({
    theme: 'bootstrap4'
})

$("#tbl_departamento_id_jurisdiccion").select2();
$("#tbl_municipio_id_jurisdiccion").select2();
$("#tbl_vereda_id_jurisdiccion").select2();

function init() {
    q = {};
}

var DEPARTAMENTO_JURISDICCION = {
    esResultadoJurisdiccion: function() {
        if ($("#resultado_jurisdiccion").val() == "si") {
            $("#divJurisdiccion").hide();
        } else {
            $("#divJurisdiccion").show();
        }
    },
    getMunicipiosConParametros: function() {

        if (depSelect != "") {
            $("#tbl_departamento_id_jurisdiccion").val(depSelect);
        }
        $("#tbl_departamento_id_jurisdiccion").val(depSelect).trigger('change');

        if (depSelect != "seleccione") {
            q = {};
            q.op = "ciudadget";
            q.codigo_departamento = depSelect;
            UTIL.callAjaxRqstPOST(q, this.getMunicipiosHandler);
            DEPARTAMENTO_JURISDICCION.emptyTable();
        } else {
            $("#tbl_municipio_id_jurisdiccion").empty().append('');
        }
    },
    getMunicipios: function() {
        if ($("#tbl_departamento_id_jurisdiccion").val() != "seleccione") {
            q = {};
            q.op = "ciudadget";
            q.codigo_departamento = $("#tbl_departamento_id_jurisdiccion").val();
            UTIL.callAjaxRqstPOST(q, this.getMunicipiosHandler);
        } else {
            $("#tbl_municipio_id_jurisdiccion").empty().append('');
        }
    },
    getMunicipiosHandler: function(data) {
        var depto = $("#tbl_departamento_id_jurisdiccion").val();
        UTIL.cursorNormal();
        if (data.output.valid) {
            var res = data.output.response;
            var info = '';
            for (var j in res) {
                info += "<option value=" + res[j].codigo_muncipio + " >" + res[j].municipio + "</option>";
            }
            $("#tbl_municipio_id_jurisdiccion").empty().append(info);

            if ($("#filtro").val() === 'vereda') {
                DEPARTAMENTO_JURISDICCION.getVeredasByMunicipioId();
            }
        } else {
            UTIL.mostrarMensajeError(data.output.response.content);
        }
    },

    getVeredasByMunicipioId: function() {
        if ($("#tbl_departamento_id_jurisdiccion").val() != "seleccione") {
            q = {};
            q.op = "veredaget";
            q.municipio_id = $("#tbl_municipio_id_jurisdiccion").val();
            UTIL.callAjaxRqstPOST(q, this.getVeredasByMunicipioIdHandler);
        } else {
            $("#tbl_vereda_id_jurisdiccion").empty().append('');
        }
    },
    getVeredasByMunicipioIdHandler: function(data) {
        UTIL.cursorNormal();
        if (data.output.valid) {
            var res = data.output.response;
            var info = '';
            for (var j in res) {
                info += "<option value='" + res[j].id + "' selected>" + res[j].nombre_vereda + "</option>";
            }
            $("#tbl_vereda_id_jurisdiccion").empty().append(info);
        } else {
            UTIL.mostrarMensajeError(data.output.response.content);
        }
    },
};