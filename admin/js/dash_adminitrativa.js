// cargar a dashboard de Bienes
function loadBienesData() {
    $.ajax({
        url: "admin/ajax/rqst.php",
        type: "POST",
        dataType: "json",
        data: {
            op: "bienes_get_resumen"
        },
        success: function (response) {
            if (response.output.valid) {
                const data = response.output.response;
                const summary = data.summary;
                
                $('#total-bienes').text(summary.total_bienes);
                
                const inversionTotal = parseFloat(summary.inversion_total);

                const formattedInversion = `$${inversionTotal.toLocaleString('es-CO', { 
                    minimumFractionDigits: 2, 
                    maximumFractionDigits: 2 
                })}`;
                
                $('#total-inversion').text(formattedInversion);
          
                if (data.min_max_costos) {
                    const maxCosto = parseFloat(data.min_max_costos.max.costo_unitario);
                    const minCosto = parseFloat(data.min_max_costos.min.costo_unitario);

                    // formateo a COP
                    const formattedMax = new Intl.NumberFormat('es-CO', {
                        style: 'currency',
                        currency: 'COP',
                        minimumFractionDigits: 0,
                    }).format(maxCosto);
                    const formattedMin = new Intl.NumberFormat('es-CO', {
                        style: 'currency',
                        currency: 'COP',
                        minimumFractionDigits: 0,
                    }).format(minCosto);


                    $('#costo-max').text(formattedMax);
                    $('#municipio-costo-maximo').text(data.min_max_costos.max.municipio);

                    
                    $('#costo-min').text(formattedMin);
                    $('#municipio-costo-minimo').text(data.min_max_costos.min.municipio);

                }
                
                
            } else {
                console.error("Error del servidor: ", response.output.response);
            }
        },
        error: function (xhr, status, error) {
            console.error("Error de la petición AJAX: ", status, error);
        }
    });
}

// llamado a la funcion
$(document).ready(function() {
    loadBienesData();
});