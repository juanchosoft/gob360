var ESTADO_GENERAL = {
    mostrarGraficas: function () {
        ESTADO_GENERAL.getTotalVisitasPorProvincia();
        ESTADO_GENERAL.getTotalVisitasPorMesDelDepartamento();
        ESTADO_GENERAL.getPromedioPs2025PorSecretaria();
        ESTADO_GENERAL.mostarFactoresDeEstabilidadMunicipiosByPilarIdPorColores();
        ESTADO_GENERAL.mostarFactoresDeEstabilidadVeredasByPilarIdPorColores();
    },
    mostrarGraficasMunicipiosVeredasPorColor: function () {
        $('#migraficasPorFatoresMunicipiosPorColorDiv').html(''); 
        $('#graficasPorFatoresVeredasPorColor').html(''); 
        ESTADO_GENERAL.mostarFactoresDeEstabilidadMunicipiosByPilarIdPorColores();
        ESTADO_GENERAL.mostarFactoresDeEstabilidadVeredasByPilarIdPorColores();
    },
    mostarFactoresDeEstabilidadMunicipiosByPilarIdPorColores: function () {
        q = {};
        q.op = "getFactoresPorMunicipiosByPilarIdPorColores";
        q.pilarId = $("#pilarId").val();
        q.colores = 'si';
        UTIL.cursorBusy();
        $.ajax({
            data: q,
            type: "GET",
            dataType: "json",
            url: "admin/ajax/rqst.php",
            success: function (data) {
                q = {};
                UTIL.cursorNormal();
                if (data.output.valid) {
                    let resp = data.output.cantidad_colores;
    
                    // Mapeo de colores a nombres
                    const colorMap = {
                        "#cd162c": "Rojo",
                        "#cd7d16": "Naranja",
                        "#dbd509": "Amarillo",
                        "#2774f1": "Azul",
                        "#62af0a": "Verde"
                    };
    
                    const labels = Object.keys(resp).map(color => colorMap[color] || color); // Convertir códigos en nombres
                    const values = Object.values(resp);
                    const backgroundColors = Object.keys(resp); // Mantener colores originales
    
                    document.querySelectorAll("#graficasPorFatoresMunicipiosPorColor").forEach(container => {
                        container.innerHTML = "";
    
                        const wrapper = document.createElement("div");
                        wrapper.style.display = "flex";
                        wrapper.style.flexDirection = "column";
                        wrapper.style.alignItems = "center";
                        wrapper.style.width = "100%";
    
                        const canvasContainer = document.createElement("div");
                        canvasContainer.style.width = "100%";
                        canvasContainer.style.height = "250px";
                        canvasContainer.style.display = "flex";
                        canvasContainer.style.justifyContent = "center";
                        canvasContainer.style.alignItems = "center";
    
                        const canvas = document.createElement("canvas");
                        canvas.style.maxWidth = "100%";
                        canvas.style.height = "100%";
    
                        canvasContainer.appendChild(canvas);
                        wrapper.appendChild(canvasContainer);
                        container.appendChild(wrapper);
    
                        const ctx = canvas.getContext("2d");
    
                        new Chart(ctx, {
                            type: "bar",
                            data: {
                                labels: labels, // Ahora usa nombres en lugar de códigos hexadecimales
                                datasets: [{
                                    label:  $("#pilarId").find(":selected").text(),
                                    data: values,
                                    backgroundColor: backgroundColors, // Mantiene los colores visualmente
                                    borderRadius: 10,
                                    borderWidth: 1
                                }]
                            },
                            options: {
                                responsive: true,
                                maintainAspectRatio: false,
                                plugins: {
                                    legend: {
                                        display: true
                                    }
                                },
                                scales: {
                                    y: {
                                        beginAtZero: true
                                    }
                                }
                            }
                        });
                    });
                }
            },
        });
    },
    mostarFactoresDeEstabilidadVeredasByPilarIdPorColores: function () {
        q = {};
        q.op = "graficasPorFatoresVeredasPorColor";
        q.pilarId = $("#pilarId").val();
        q.colores = 'si';
        UTIL.cursorBusy();
        $.ajax({
            data: q,
            type: "GET",
            dataType: "json",
            url: "admin/ajax/rqst.php",
            success: function (data) {
                q = {};
                UTIL.cursorNormal();
                if (data.output.valid) {
                    let resp = data.output.cantidad_colores;
    
                    // Mapeo de colores a nombres
                    const colorMap = {
                        "#cd162c": "Rojo",
                        "#cd7d16": "Naranja",
                        "#dbd509": "Amarillo",
                        "#2774f1": "Azul",
                        "#62af0a": "Verde"
                    };
    
                    const labels = Object.keys(resp).map(color => colorMap[color] || color); // Convertir códigos en nombres
                    const values = Object.values(resp);
                    const backgroundColors = Object.keys(resp); // Mantener colores originales
    
                    document.querySelectorAll("#graficasPorFatoresVeredasPorColor").forEach(container => {
                        container.innerHTML = "";
    
                        const wrapper = document.createElement("div");
                        wrapper.style.display = "flex";
                        wrapper.style.flexDirection = "column";
                        wrapper.style.alignItems = "center";
                        wrapper.style.width = "100%";
    
                        const canvasContainer = document.createElement("div");
                        canvasContainer.style.width = "100%";
                        canvasContainer.style.height = "250px";
                        canvasContainer.style.display = "flex";
                        canvasContainer.style.justifyContent = "center";
                        canvasContainer.style.alignItems = "center";
    
                        const canvas = document.createElement("canvas");
                        canvas.style.maxWidth = "100%";
                        canvas.style.height = "100%";
    
                        canvasContainer.appendChild(canvas);
                        wrapper.appendChild(canvasContainer);
                        container.appendChild(wrapper);
    
                        const ctx = canvas.getContext("2d");
    
                        new Chart(ctx, {
                            type: "bar",
                            data: {
                                labels: labels, // Ahora usa nombres en lugar de códigos hexadecimales
                                datasets: [{
                                    label:  $("#pilarId").find(":selected").text(),
                                    data: values,
                                    backgroundColor: backgroundColors, // Mantiene los colores visualmente
                                    borderRadius: 10,
                                    borderWidth: 1
                                }]
                            },
                            options: {
                                responsive: true,
                                maintainAspectRatio: false,
                                plugins: {
                                    legend: {
                                        display: true
                                    }
                                },
                                scales: {
                                    y: {
                                        beginAtZero: true
                                    }
                                }
                            }
                        });
                    });
                }
            },
        });
    },
    fetchDataComparativa: function (operation, container, title) {
        const q = { op: operation };
        UTIL.cursorBusy();

        return $.ajax({
            data: q,
            type: "GET",
            dataType: "json",
            url: "admin/ajax/rqst.php"
        }).always(() => {
            UTIL.cursorNormal();
        }).then((data) => {
            if (data.output.valid) {
                return data;
            } else {
                swal("warning", data.output.response.content, "error");
                return [];
            }
        });
    },
    createChartComparativa: function (container, title, jsonData, tipo) {

        // Procesar el JSON para extraer categorías (provincias) y series (años)
        const response = jsonData.output.response;
        let categories;
        if (tipo === "provincia") {
            categories =  Object.values(response)[0].map(item => item.provincia); // Provincias (categorías)
        } else if (tipo === "mes") {
            categories =  Object.values(response)[0].map(item => item.mes); // Provincias (categorías)
        }
        const series = Object.keys(response).map(year => ({
            name: year, // Nombre del año
            data: response[year].map(item => item
                .total_visitas) // Datos de total_visitas por provincia
        }));
        // Configuración del gráfico
        const options = {
            series: series,
            chart: {
                type: 'bar',
                height: 350
            },
            plotOptions: {
                bar: {
                    horizontal: false,
                    columnWidth: '55%',
                    borderRadius: 5
                }
            },
            dataLabels: {
                enabled: false
            },
            colors: ['#ff5733', '#33ff57', '#33c1ff', '#bdc3c7', '#7f8c8d', '#2c3e50', '#f39c12', '#d35400', '#c0392b', '#ffeb3b', '#7e57c2'],
            stroke: {
                show: true,
                width: 2,
                colors: ['transparent'],
            },
            xaxis: {
                categories: categories // Categorías (Provincias, Mes)
            },
            yaxis: {
                title: {
                    text: title
                }
            },
            fill: {
                opacity: 1
            },
            tooltip: {
                y: {
                    formatter: function (val) {
                        return val + " visitas";
                    }
                }
            }
        };
        // Renderizar el gráfico
        const chart = new ApexCharts(document.querySelector(`#${container}`), options);
        chart.render();
    },
    createChart: function (container, title, seriesData) {
        const options = {
            chart: {
                height: 350,
                type: 'bar',
            },
            plotOptions: {
                bar: {
                    horizontal: false,
                    columnWidth: '55%',
                    endingShape: 'rounded',
                },
            },
            dataLabels: {
                enabled: false,
            },
            colors: ['#0e9e4a', '#1abc9c', '#e74c3c'],
            stroke: {
                show: true,
                width: 2,
                colors: ['transparent'],
            },
            series: [{
                name: title,
                data: seriesData.map(item => item.y),
            }],
            xaxis: {
                categories: seriesData.map(item => item.name),
            },
            yaxis: {
                title: {
                    text: 'Total de visitas',
                },
            },
            fill: {
                opacity: 1,
            },
            tooltip: {
                y: {
                    formatter: (val) => val.toLocaleString(), // Formato numérico legible
                },
            },
        };

        const chart = new ApexCharts(document.querySelector(`#${container}`), options);
        chart.render();
    },

    getTotalVisitasPorProvincia: function () {
    this.fetchDataComparativa("getTotalVisitasPorProvincias", "containerProvincias", "Visitas realizadas a Provincias")
        .then((data) => {
            this.createChartComparativa("containerProvincias", "Visitas realizadas a Provincias", data, "provincia");
        });
},


    getTotalVisitasPorMesDelDepartamento: function () {
    this.fetchDataComparativa("getSoloVisitasPorMes", "containerMunicipios", "Visitas por Mes a Municipios")
        .then((data) => {
            this.createChartComparativa("containerMunicipios", "Visitas por Mes a Municipios", data, "mes");
        });
},

    createChartSimple: function (container, title, seriesData) {
        const options = {
            chart: {
                height: 400,
                type: 'bar',
                animations: {
                    enabled: true,
                    easing: 'easeinout',
                    speed: 800,
                    animateGradually: {
                        enabled: true,
                        delay: 150
                    },
                    dynamicAnimation: {
                        enabled: true,
                        speed: 350
                    }
                }
            },
            plotOptions: {
                bar: {
                    horizontal: true,
                    columnWidth: '60%',  // Ajuste de ancho de barra
                    borderRadius: 8,  // Bordes más suaves
                },
            },
            dataLabels: {
                enabled: true,
                style: {
                    fontSize: '0px', 
                    fontWeight: 'bold',
                    colors: ['#fff']
                },
                background: {
                    enabled: true,
                    foreColor: '#333',
                    borderRadius: 3
                }
            },
            colors: ['#27AE60', '#F1C40F', '#E74C3C'],  // 🔹 Verde, Amarillo, Rojo
            stroke: {
                show: true,
                width: 1,
                colors: ['#fff'],
            },
            series: [{
                name: title,
                data: seriesData.map(item => item.y),
            }],
            xaxis: {
                categories: seriesData.map(item => item.name),
                labels: {
                    style: {
                        fontSize: '12px',
                        fontWeight: 'bold',
                    }
                }
            },
            yaxis: {
                title: {
                    text: title,
                    style: {
                        fontSize: '14px',
                        fontWeight: 'bold',
                    }
                }
            },
            fill: {
                type: 'gradient',
                gradient: {
                    shade: 'light',
                    type: "horizontal",
                    shadeIntensity: 0.5,
                    gradientToColors: ['#F39C12', '#E67E22', '#C0392B'],  // 🔹 Degradado Amarillo a Rojo
                    inverseColors: true,
                    opacityFrom: 0.8,
                    opacityTo: 1,
                    stops: [0, 100]
                }
            },
            tooltip: {
                y: {
                    formatter: function (val) {
                        return val.toLocaleString() + " puntos"; 
                    }
                }
            }
        };
    
        const chart = new ApexCharts(document.querySelector(`#${container}`), options);
        chart.render();
    },

    getPromedioPs2025PorSecretaria: function () {
        this.fetchDataComparativa("getpromediops2025porsecretaria", "containerSecretarias", "Promedio PS2025 por Secretaría")
            .then((data) => {
                // console.log("Datos recibidos:", data); // 🛠 Ver qué datos llegan
                if (data.output.valid) {
                    const seriesData = data.output.response.map(item => ({
                        name: item.nombre_secretaria,
                        y: parseFloat(item.promedio_ps2025)
                    }));
    
                    //console.log("Datos procesados para gráfico:", seriesData); // 🛠 Ver datos antes de graficar
                    
                    this.createChartSimple("containerSecretarias", "Promedio PS2025 por Secretaría", seriesData);
                }
            });
    },

    showData: function (color) {
        const q = {
            op: "getveredasbycolor_depart",
            color: color,
            codigo_departamento: this.getDepartamentoCodigo(),
        };
        UTIL.callAjaxRqstPOST(q, this.showDataHandler.bind(this));
    },

    showDataHandler: function (data) {
        UTIL.cursorNormal();
        if (data.output.valid) {
            this.populateTable(data.output.response);
        }
    },

    populateTable: function (data) {
        const tableRows = data.map(item => `
            <tr>
                <td class="text-primary">${item.brigada}</td>
                <td class="text-primary">${item.batallon}</td>
                <td class="text-primary">${item.departamento}</td>
                <td class="text-primary">${item.municipio}</td>
                <td class="text-primary">${item.nombre_vereda}</td>
            </tr>
        `).join('');

        $("#tablaVeredasColores").empty().append(tableRows);
    },
    getDepartamentoCodigo: function () {
        const municipioSelect = $('#municipioSelect').val();
        const codigoDepartamentoMap = {
            'antioquia': 5,
            'choco': 27,
        };

        return codigoDepartamentoMap[municipioSelect] || null;
    }
};

$(document).ready(function () {
    ESTADO_GENERAL.mostrarGraficas();
});
