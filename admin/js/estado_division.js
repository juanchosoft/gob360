$(document).on("ready", init);
var q;

function init() {
    q = {};
}
var ESTADO_DIVISION = {
    showData: function(color) {
        q = {};
        q.op = "getveredasbycolor";
        q.color = color;
        UTIL.callAjaxRqstPOST(q, this.showDataHandler);
    },
    showDataHandler: function(data) {
        UTIL.cursorNormal();
        if (data.output.valid) {
            var res = data.output.response;
            var table = "";
            for (var j in res) {
                table += "<tr>";
                table += '<td class="text-primary">' + res[j].brigada + "</td>";
                table += '<td class="text-primary">' + res[j].batallon + "</td>";
                table += '<td class="text-primary">' + res[j].departamento + "</td>";
                table += '<td class="text-primary">' + res[j].municipio + "</td>";
                table += '<td class="text-primary">' + res[j].nombre_vereda + "</td>";
                table += "</tr>";
            }
            $("#tablaVeredasColores")
                .empty()
                .append(table);
        }
    },
};