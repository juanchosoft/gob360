$(document).on("ready", init);
var q, filtro;
var res = {};

//Initialize Select2 Elements
$('.select2').select2()

//Initialize Select2 Elements
$('.select2bs4').select2({
    theme: 'bootstrap4'
})

$("#tbl_departamento_id").select2();
$("#tbl_vereda_id").select2();

function init() {
    q = {};
}

var AUTOMATICACION = {};