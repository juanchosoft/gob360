

            // Data retrieved from https://gs.statcounter.com/browser-market-share#monthly-202201-202201-bar

            // Create the chart
            Highcharts.chart('container1', {
                chart: {
                    type: 'column'
                },
                title: {
                    align: 'left',
                    text: 'Compromisos pactados por Secretaria en estado Sin Cumplir'
                },
                subtitle: {
                    align: 'left',
                    text: ' <a href="http://statcounter.com" target="_blank"></a>'
                },
                accessibility: {
                    announceNewData: {
                        enabled: true
                    }
                },
                xAxis: {
                    type: 'category'
                },
                yAxis: {
                    title: {
                        text: 'Total de Compromisos'
                    }

                },
                legend: {
                    enabled: false
                },
                plotOptions: {
                    series: {
                        borderWidth: 0,
                        dataLabels: {
                            enabled: true,
                            format: '{point.y:.1f}'
                        }
                    }
                },

                tooltip: {
                    headerFormat: '<span style="font-size:11px">{series.name}</span><br>',
                    pointFormat: '<span style="color:{point.color}">{point.name}</span>: <b>{point.y:.2f}</b> Compromisos<br/>'
                },

                series: [{
                    name: 'Secretarias',
                    colorByPoint: true,
                    data: [{
                            name: 'Ambiental',
                            y: 1,
                            drilldown: 'Ambiental'
                        },
                        {
                            name: 'CAS',
                            y: 2,
                            drilldown: 'CAS'
                        },
                        {
                            name: 'Competividad',
                            y: 3,
                            drilldown: 'Competividad'
                        },
                        {
                            name: 'Cultura y Turismo',
                            y: 11,
                            drilldown: 'Cultura y Turismo'
                        },
                        {
                            name: 'Desarrollo Social',
                            y: 9,
                            drilldown: 'Desarrollo Social'
                        },
                        {
                            name: 'Educación',
                            y: 26,
                            drilldown: 'Educación'
                        },
                        {
                            name: 'Esant',
                            y: 12,
                            drilldown: 'Esant'
                        },
                        {
                            name: 'Gestión del Riesgo',
                            y: 2,
                            drilldown: 'Gestión del Riesgo'
                        },
                        {
                            name: 'InderSantader',
                            y: 26,
                            drilldown: 'InderSantader'
                        },
                        {
                            name: 'Infraestructura',
                            y: 34,
                            drilldown: 'Infraestructura'
                        },
                        {
                            name: 'Interior',
                            y: 4,
                            drilldown: 'Interior'
                        },
                        {
                            name: 'Mujer y Genero',
                            y: 9,
                            drilldown: 'Mujer y Genero'
                        },
                        {
                            name: 'Oficina Juridica',
                            y: 1,
                            drilldown: 'Oficina Juridica'
                        },
                        {
                            name: 'Privada',
                            y: 1,
                            drilldown: 'Privada'
                        },
                        {
                            name: 'Salud',
                            y: 6,
                            drilldown: 'Salud'
                        }

                    ]
                }],

            });

            // Data retrieved from https://gs.statcounter.com/browser-market-share#monthly-202201-202201-bar
            // Create the chart
            Highcharts.chart('container', {
                chart: {
                    type: 'column'
                },
                title: {
                    align: 'left',
                    text: 'Total de Compromisos pactados por Secretaria'
                },
                subtitle: {
                    align: 'left',
                    text: ' <a href="http://statcounter.com" target="_blank"></a>'
                },
                accessibility: {
                    announceNewData: {
                        enabled: true
                    }
                },
                xAxis: {
                    type: 'category'
                },
                yAxis: {
                    title: {
                        text: 'Total de Compromisos'
                    }

                },
                legend: {
                    enabled: false
                },
                plotOptions: {
                    series: {
                        borderWidth: 0,
                        dataLabels: {
                            enabled: true,
                            format: '{point.y:.1f}'
                        }
                    }
                },

                tooltip: {
                    headerFormat: '<span style="font-size:11px">{series.name}</span><br>',
                    pointFormat: '<span style="color:{point.color}">{point.name}</span>: <b>{point.y:.2f}</b> Compromisos<br/>'
                },

                series: [{
                    name: 'Secretarias',
                    colorByPoint: true,
                    data: [{
                            name: 'Ambiental',
                            y: 1,
                            drilldown: 'Ambiental'
                        },
                        {
                            name: 'CAS',
                            y: 2,
                            drilldown: 'CAS'
                        },
                        {
                            name: 'Competividad',
                            y: 3,
                            drilldown: 'Competividad'
                        },
                        {
                            name: 'Cultura y Turismo',
                            y: 11,
                            drilldown: 'Cultura y Turismo'
                        },
                        {
                            name: 'Desarrollo Social',
                            y: 9,
                            drilldown: 'Desarrollo Social'
                        },
                        {
                            name: 'Educación',
                            y: 26,
                            drilldown: 'Educación'
                        },
                        {
                            name: 'Esant',
                            y: 12,
                            drilldown: 'Esant'
                        },
                        {
                            name: 'Gestión del Riesgo',
                            y: 2,
                            drilldown: 'Gestión del Riesgo'
                        },
                        {
                            name: 'InderSantader',
                            y: 26,
                            drilldown: 'InderSantader'
                        },
                        {
                            name: 'Infraestructura',
                            y: 34,
                            drilldown: 'Infraestructura'
                        },
                        {
                            name: 'Interior',
                            y: 4,
                            drilldown: 'Interior'
                        },
                        {
                            name: 'Mujer y Genero',
                            y: 9,
                            drilldown: 'Mujer y Genero'
                        },
                        {
                            name: 'Oficina Juridica',
                            y: 1,
                            drilldown: 'Oficina Juridica'
                        },
                        {
                            name: 'Privada',
                            y: 1,
                            drilldown: 'Privada'
                        },
                        {
                            name: 'Salud',
                            y: 6,
                            drilldown: 'Salud'
                        }

                    ]
                }],

            });
            document.getElementById("btnAumentar").onclick = function() {
                aumentarTransform();
            };

            document.getElementById("btnReducir").onclick = function() {
                reducirTransform();
            };

            function aumentarTransform() {
                var elemento = document.getElementById("contenidoTransformado");
                var escalaActual = parseFloat(window.getComputedStyle(elemento).getPropertyValue("transform").split(
                    ",")[3]);
                var nuevaEscala = escalaActual + 0.1; // Aumentar la escala en 0.1
                elemento.style.transform = "scale(" + nuevaEscala + ")";
            }

            function reducirTransform() {
                var elemento = document.getElementById("contenidoTransformado");
                var escalaActual = parseFloat(window.getComputedStyle(elemento).getPropertyValue("transform").split(
                    ",")[3]);
                var nuevaEscala = escalaActual - 0.1; // Reducir la escala en 0.1
                if (nuevaEscala >= 0.1) { // Evitar escala negativa
                    elemento.style.transform = "scale(" + nuevaEscala + ")";
                }
            }