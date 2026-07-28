$(document).on('ready', init);
var q;
/**
 * se activa para inicializar el documento
 */
function init() {
    q = {};
}

var return_page = 'operatividad.php';
var OPERATIVIDAD = {
    validateData: function() {
        var bValid = true;
        var msj = "Falta ingresar información obligatoria, marcada con asterisco.";
        if (
            $("#tbl_departamento_id").val() == "" ||
            $("#tbl_municipio_id").val() == "" ||
            $("#tbl_brigada_id").val() == ""
        ) {
            UTIL.mostrarMensajeValidacion(msj);
            bValid = false;
            return;
        }
        if (bValid) {
            OPERATIVIDAD.savedata();
        }
    },
    getOperatividadById: function(id) {
        if (id != "" && id > 0) {
            q = {};
            q.op = "operatividadeget";
            q.id = id;
            UTIL.cursorBusy();
            $.ajax({
                data: q,
                type: "GET",
                dataType: "json",
                url: "admin/ajax/rqst.php",
                success: function(data) {
                    q = {};
                    UTIL.cursorNormal();
                    if (data.output.valid) {
                        var res = data.output.response[0];
                        console.log(res);
                        OPERATIVIDAD.edit(res);
                    } else {
                        UTIL.mostrarMensajeError(data.output.response.content);
                    }
                },
            });
        }
    },
    /**
     * Método para setear la informacion de Departamento, Municipio y Vereda
     */
    setearDepMunVer(res) {
        $("#tbl_departamento_id").select2().val(res.codigo_departamento).trigger("change");

        DEPARTAMENTO.getMunicipios();

        setTimeout(function() {
            $("#tbl_municipio_id").select2().val(res.codigo_muncipio).trigger("change");
        }, 1500);

        $("#tbl_vereda_id").select2().val(res.tbl_vereda_id).trigger("change");
    },
    edit: function(res) {
        $("#id").val(res.id);
        $("#presentaciones").val(res.presentaciones);
        $("#mdom").val(res.mdom);
        $("#sometimiento").val(res.sometimiento);
        $("#capturas_gao").val(res.capturas_gao);
        $("#capturas_gdo").val(res.capturas_gdo);
        $("#capturas_delco").val(res.capturas_delco);
        $("#bajas_delco").val(res.bajas_delco);
        $("#menores").val(res.menores);
        $("#upm").val(res.upm);
        $("#dragas").val(res.dragas);
        $("#motores").val(res.motores);
        $("#municipio_id").val(res.municipio_id);
        $("#combates").val(res.combates);
        $("#explosivos").val(res.explosivos);
        $("#armas_cortas").val(res.armas_cortas);
        $("#armas_largas").val(res.armas_largas);
        $("#campamentos").val(res.campamentos);
        $("#municiones").val(res.municiones);
        $("#comunicaciones").val(res.comunicaciones);
        $("#intendencia").val(res.intendencia);
        $("#lab_ch").val(res.lab_ch);
        $("#semilleros").val(res.semilleros);
        $("#depositos").val(res.depositos);
        $("#tbl_departamento_id").val(res.tbl_departamento_id);
        $("#tbl_municipio_id").val(res.tbl_municipio_id);
        $("#tbl_vereda_id").val(res.tbl_vereda_id);
        $("#lab_pbc").val(res.lab_pbc);
        $("#pasta_coca").val(res.pasta_coca);
        $("#erradicacion").val(res.erradicacion);
        $("#mariguana").val(res.mariguana);
        $("#pasta_proceso").val(res.pasta_proceso);
        $("#cloridrato").val(res.cloridrato);
        $("#hoja").val(res.hoja);
        $("#dinero").val(res.dinero);
        $("#solidos").val(res.solidos);
        $("#liquidos").val(res.liquidos);
        $("#capturas_soc").val(res.capturas_soc);
        $("#madera").val(res.madera);
        $("#siembra").val(res.siembra);
        $("#otras_sustancias").val(res.otras_sustancias);
        $("#otras_maq").val(res.otras_maq);
        $("#retro").val(res.retro);
        $("#fauna").val(res.fauna);
        $("#dominio").val(res.dominio);
        $("#vehiculos").val(res.vehiculos);
        $("#proveedores").val(res.proveedores);
        $("#opsic").val(res.opsic);
        $("#mercurio").val(res.mercurio);
        $("#semilleros_matas").val(res.semilleros_matas);
        $("#minas").val(res.minas);
        $("#gaulavol").val(res.gaulavol);
        $("#gaularadio").val(res.gaularadio);
        $("#gaulareunion").val(res.gaulareunion);
    },
    savedata: function() {
        q = {};
        q.op = "operatividadsave";
        if ($("#id").val() != "") {
            q.id = $("#id").val();
            q.op = "operatividadupdate";
        }
        q.presentaciones = $("#presentaciones").val();
        q.mdom = $("#mdom").val();
        q.sometimiento = $("#sometimiento").val();
        q.capturas_gao = $("#capturas_gao").val();
        q.capturas_gdo = $("#capturas_gdo").val();
        q.capturas_delco = $("#capturas_delco").val();
        q.bajas_delco = $("#bajas_delco").val();
        q.menores = $("#menores").val();
        q.upm = $("#upm").val();
        q.dragas = $("#dragas").val();
        q.motores = $("#motores").val();
        q.municipio_id = $("#municipio_id").val();
        q.combates = $("#combates").val();
        q.explosivos = $("#explosivos").val();
        q.armas_cortas = $("#armas_cortas").val();
        q.armas_largas = $("#armas_largas").val();
        q.campamentos = $("#campamentos").val();
        q.municiones = $("#municiones").val();
        q.comunicaciones = $("#comunicaciones").val();
        q.intendencia = $("#intendencia").val();
        q.lab_ch = $("#lab_ch").val();
        q.semilleros = $("#semilleros").val();
        q.depositos = $("#depositos").val();
        q.tbl_departamento_id = $("#tbl_departamento_id").val();
        q.tbl_municipio_id = $("#tbl_municipio_id").val();
        q.tbl_vereda_id = $("#tbl_vereda_id").val();
        q.lab_pbc = $("#lab_pbc").val();
        q.pasta_coca = $("#pasta_coca").val();
        q.erradicacion = $("#erradicacion").val();
        q.mariguana = $("#mariguana").val();
        q.pasta_proceso = $("#pasta_proceso").val();
        q.cloridrato = $("#cloridrato").val();
        q.hoja = $("#hoja").val();
        q.dinero = $("#dinero").val();
        q.solidos = $("#solidos").val();
        q.liquidos = $("#liquidos").val();
        q.capturas_soc = $("#capturas_soc").val();
        q.madera = $("#madera").val();
        q.siembra = $("#siembra").val();
        q.otras_sustancias = $("#otras_sustancias").val();
        q.otras_maq = $("#otras_maq").val();
        q.retro = $("#retro").val();
        q.fauna = $("#fauna").val();
        q.dominio = $("#dominio").val();
        q.vehiculos = $("#vehiculos").val();
        q.proveedores = $("#proveedores").val();
        q.opsic = $("#opsic").val();
        q.semilleros_matas = $("#semilleros_matas").val();
        q.minas = $("#minas").val();
        q.gaulavol = $("#gaulavol").val();
        q.gaularadio = $("#gaularadio").val();
        q.gaulareunion = $("#gaulareunion").val();
        q.mercurio = $("#mercurio").val();
        q.tbl_departamento_id_jurisdiccion = $("#tbl_departamento_id_jurisdiccion").val();
        q.tbl_municipio_id_jurisdiccion = $("#tbl_municipio_id_jurisdiccion").val();
        q.tbl_vereda_id_jurisdiccion = $("#tbl_vereda_id_jurisdiccion").val();
        q.coordenadas = $("#coordenadas").val();
        q.resultado_jurisdiccion = $("#resultado_jurisdiccion").val();
        q.hr1 = $("#hr1").val();
        q.fecha_hr1 = $("#fecha_hr1").val();
        q.hr2 = $("#hr2").val();
        q.fecha_hr2 = $("#fecha_hr2").val();
        q.hr3 = $("#hr3").val();
        q.fecha_hr3 = $("#fecha_hr3").val();
        q.hr4 = $("#hr4").val();
        q.fecha_hr4 = $("#fecha_hr4").val();
        q.hr5 = $("#hr5").val();
        q.fecha_hr5 = $("#fecha_hr5").val();
        UTIL.callAjaxRqstPOST(q, OPERATIVIDAD.savedataHandler);
    },
    savedataHandler: function(data) {
        UTIL.cursorNormal();
        if (data.output.valid) {
            UTIL.mostrarMensajeExitoso('Información guardada correctamente');
            setTimeout(function() {
                window.location = 'operatividad.php';
            }, 1000);
        } else {
            UTIL.mostrarMensajeError(data.output.response.content);
        }
    },
};