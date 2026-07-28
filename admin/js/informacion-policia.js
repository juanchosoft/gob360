let tablaDinamica = null;
let categoriaActual = "hurtos";
let municipioActual = ""; 
let fechaInicioActual = "";
let fechaFinActual = "";


$(function () {

  cargarCategoria(categoriaActual);

  $("#categoriaSelect").on("change", function () {
    categoriaActual = this.value;

    recargarTabla(); 
  });
  
  $("#aplicarFiltrosBtn").on("click", function () {



    municipioActual = $("#municipioSelect").val();
    fechaInicioActual = $("#fechaInicio").val();
    fechaFinActual = $("#fechaFin").val();
    

    recargarTabla();
  });


  $("#customSearch").on("keyup", function () {
    if (tablaDinamica) {
      tablaDinamica.search(this.value).draw();
    }
  });
});

/**
 * funcion que recarga la tabla con losfiltros actuales.
 */
function recargarTabla() {
    if (tablaDinamica) {

        $("#loader").show(); 

        tablaDinamica.ajax.reload(function() {

            $("#loader").hide(); 
        }, true); 
    } else {

        cargarCategoria(categoriaActual);
    }
}


/**
 * Inicializa DataTables, primero con una llamada AJAX para obtener las columnas y luego con el servidor-side.
 * @param {string} categoria - La categoría de delito actual.
 */
function cargarCategoria(categoria) {
  if (tablaDinamica) {
    tablaDinamica.destroy();
    $("#dynamictable").empty();
  }

  $("#loader").show();


  $.ajax({
    url: "./admin/controllers/apiCtrl.php",
    type: "POST",
    dataType: "json",
    data:{
      method: "cargaCategoria",
      categoria: categoria,
      draw: 1, 
      start: 0,
      length: 0, 
      search: { value: "" },
      order: [],
      municipio: municipioActual,
      fechaInicio: fechaInicioActual,
      fechaFin: fechaFinActual,
    },
    success: function (response) {
      if (response.error) {
        alert("Error: " + response.error);
        $("#loader").hide();
        return;
      }

      const headers = response.headers || [];
      const columns = response.columns || [];

      // Generar encabezado dinámico
      let thead = "<thead><tr>";
      headers.forEach((header) => {
        thead += `<th>${header}</th>`;
      });
      thead += "</tr></thead>";

      $("#dynamictable").html(thead);

      tablaDinamica = $("#dynamictable").DataTable({
        order: [[0, "desc"]],
        dom: "lrtip", 
        processing: true,
        serverSide: true,
        responsive: true,
        language: {
          url: '//cdn.datatables.net/plug-ins/1.10.25/i18n/Spanish.json' // Usar idioma español
        },
        ajax: {
          url: "./admin/controllers/apiCtrl.php",
          type: "POST",
          data: function (d) {
            const dataToSend = {
              method: "cargaCategoria",
              categoria: categoria,
              draw: d.draw,
              start: d.start,
              length: d.length,
              search: d.search,
              order: d.order,
              municipio: municipioActual,
              fechaInicio: fechaInicioActual,
              fechaFin: fechaFinActual,
            };
            return dataToSend;
          },
          dataSrc: function (json) {
            $("#loader").hide();
            return json.data;
          },
        },
        columns: columns.map((col) => ({
          data: col,
          name: col,
          defaultContent: "",
        })),
      });
      
      tablaDinamica.one("xhr", function () {
        $("#loader").hide();
      });
    },
    error: function (xhr) {
      console.error("Error AJAX en carga inicial:", xhr.responseText);
      alert("Error al cargar la estructura de la tabla.");
      $("#loader").hide();
    },
  });
}