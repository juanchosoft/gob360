$(document).on('ready', init);
var q;

//Initialize Select2 Elements
$('.select2').select2()

//Initialize Select2 Elements
$('.select2bs4').select2({
    theme: 'bootstrap4'
})

$("#tbl_comuna_id").select2();
$("#tbl_barrio_id").select2();


function init() {
    q = {};
}
var COMUNA = {
    
    getBarriosByComunaId: function(changeValue = false) {

        if(changeValue){
            let urlParts = URLToArray(ACTUAL_URL);
            urlParts.mun = $("#tbl_barriobuc_id").val();

            var newURL = window.location.href.split('?')[0]+"?"+$.param(urlParts);
            location.href = newURL;
        }

        if ($("#tbl_comuna_id").val() != "seleccione") {
            q = {};
            q.op = "veredaget";
            q.municipio_id = $("#tbl_barriobuc_id").val();
            UTIL.callAjaxRqstPOST(q, this.getBarriosByComunaIdHandler);

            Comunas.emptyTable();

        } else {
            $("#tbl_vereda_id").empty().append('');
        }
    },
}