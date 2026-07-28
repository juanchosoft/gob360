<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mapa Información administrativa</title>
    
    <?php
    include './admin/include/head.php';
    require './admin/include/generic_classes.php';
    
    function getUrl()
    {
        $port = $_SERVER["SERVER_PORT"];
        $nameServer = $port != "80" ? $_SERVER['SERVER_NAME'] . ":" . $port : $_SERVER['SERVER_NAME'];
        $url = sprintf(
            "%s://%s%s",
            isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off' ? 'https' : 'http',
            $nameServer,
            $_SERVER['REQUEST_URI']
        );
        $final =  str_replace(basename($_SERVER["SCRIPT_FILENAME"], '.php') . ".php", "", $url);
        $exists = strpos($final, "?");
        if ($exists !== false) {
            $final =  substr($final, 0, $exists);
        }
        return $final;
    }
    
    require_once './admin/include/generic_classes.php';
    include './admin/classes/Colombia.php';
    include './admin/db/coloress.php';
    include './admin/classes/Secretarias.php';
    include './admin/classes/AccionSecretaria.php';
    
    // Obtener secretaría y acción
    $secretaria = intval($_REQUEST['secretaria']) ?? Util::getSecretariaPrincipal();
    $accion = $_REQUEST['accion'] ?? 'Capacitacion Fiscal y Financiera';
    
    // Obtener listado de secretarías
    $arr = Secretarias::getAll(null);
    $isvalid = $arr['output']['valid'];
    $arr = $arr['output']['response'];
    $optionSecretarias = "";
    foreach ($arr as $val) {
        $selected = ($val['id'] == $secretaria) ? "selected" : "";
        $optionSecretarias .= "<option value='" . $val['id'] . "' $selected>" . $val['secretaria'] . "</option>";
    }
    
    $secretariaParaConsulta = $secretaria;
    $arrMapa = [
        'departamento' => Util::getDepartamentoPrincipal()
    ];
    $data = Colombia::getInformacionAdministrativaColoresMapa($arrMapa);
    $santander = $data['output']['response'];

    //pintando el grafico de barras
    include './admin/classes/Bienes.php'; 
    $distribucionData = Bienes::getDistribucionPorProvincia();
    $distribucionValid = $distribucionData['output']['valid'];
    $distribucion = $distribucionValid ? $distribucionData['output']['response'] : [];

    $distribucionJSON = json_encode($distribucion);
    
    ?>
    
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.7.1.js"></script>
    <!-- Popper.js -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.3/umd/popper.min.js"></script>
    <!-- DataTables -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    
    <style>
        .nombres {
            font-family: "IBM Plex Sans", sans-serif !important;
        }
        
        .fondo {
            background-color: #FC0707;
            padding: 2px 4px;
            color: white;
            display: inline-block;
        }
        
        .card {
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            border-radius: 0.5rem;
        }
        
        .card-header {
            font-weight: bold;
        }
        
        .progress {
            height: 20px;
            border-radius: 10px;
        }
        
        .progress-bar {
            line-height: 20px;
            font-size: 12px;
        }
        
        .text-xs {
            font-size: 0.75rem;
        }
        
        .bg-cumplidos {
            background-color: #0d5fa7 !important;
            color: white !important;
        }
        
        .bg-cumplidos small {
            color: white !important;
        }
        
        .mapaClick {
            transition: all 0.2s ease-in-out;
            transform-origin: center;
        }
        
        .mapaClick:hover,
        .mapaClick:focus,
        .mapaClick:focus-visible {
            stroke: rgb(0, 238, 255);
            stroke-width: 2px;
            filter: drop-shadow(0 0 4px rgba(0, 0, 0, 0.7));
            cursor: pointer;
            outline: none;
        }
    </style>
</head>

<body class="">
    <!-- [ Pre-loader ] start -->
    <div class="loader-bg">
        <div class="loader-track">
            <div class="loader-fill"></div>
        </div>
    </div>

    <?php include './admin/include/navbar.php'; ?>
    <?php include './admin/include/header.php'; ?>

    <div class="pcoded-main-container">
        <div class="pcoded-wrapper">
            <div class="pcoded-content">
                <div class="pcoded-inner-content">
                    <div class="main-body">
                        <div class="page-wrapper">
                            <!-- [ breadcrumb ] start -->
                            <div class="page-header">
                                <div class="page-block">
                                    <div class="row align-items-center">
                                        <div class="col-md-12">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <h5 class="m-b-10">Mapa Información administrativa</h5>
                                                <?php include './admin/include/btn_back.php'; ?>
                                            </div>
                                            <ul class="breadcrumb">
                                                <li class="breadcrumb-item"><a href="index.html"><i class="feather icon-home"></i></a></li>
                                                <li class="breadcrumb-item"><a href="#!">Información administrativa </a></li>
                                                <li class="breadcrumb-item"><a href="#!">Bienes</a></li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- [ Main Content ] start -->
                            <div class="card">
                                <div class="card-header d-flex flex-wrap align-items-center justify-content-between py-3">
                                    <h5 class="mb-0 text-center w-100">Información administrativa</h5>
                                    <div class="card-header-right ml-auto">
                                        <div class="btn-group card-option">
                                            <button type="button" class="btn dropdown-toggle btn-icon" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                <i class="feather icon-more-horizontal"></i>
                                            </button>
                                            <ul class="list-unstyled card-option dropdown-menu dropdown-menu-right">
                                                <li class="dropdown-item full-card">
                                                    <a href="#!">
                                                        <span><i class="feather icon-maximize"></i> Maximizar</span>
                                                        <span style="display:none"><i class="feather icon-minimize"></i> Restaurar</span>
                                                    </a>
                                                </li>
                                                <li class="dropdown-item minimize-card">
                                                    <a href="#!">
                                                        <span><i class="feather icon-minus"></i> Colapsar</span>
                                                        <span style="display:none"><i class="feather icon-plus"></i> Expandir</span>
                                                    </a>
                                                </li>
                                                <li class="dropdown-item reload-card">
                                                    <a href="#!"><i class="feather icon-refresh-cw"></i> Recargar</a>
                                                </li>
                                                <li class="dropdown-item close-card">
                                                    <a href="#!"><i class="feather icon-trash"></i> Eliminar</a>
                                                </li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <!-- Indicadores IZQUIERDA -->
                                        <div class="col-md-3">
                                            <div class="card text-center mb-3" style="cursor: pointer;">
                                                <div class="card-body p-2">
                                                    <h3 class="font-weight-bold mb-0 text-dark" id="total-inversion">0</h3>
                                                    <small class="text-uppercase text-muted">Total Inversión</small>
                                                </div>
                                            </div>

                                            <div class="card text-center mb-3">
                                                <div class="card-body p-2">
                                                    <h4 class="mb-1" id="total-bienes">0</h4>
                                                    <small class="text-muted">Total Bienes</small>
                                                </div>
                                            </div>

                                            <div class="card text-dark bg-success mb-3" style="cursor: pointer;">
                                                <div class="card-body p-2 text-center">
                                                    <h4 class="mb-0" id="costo-max">0</h4>
                                                    <small class="d-block">Costo Máximo</small> 
                                                    <small class="d-block" id="municipio-costo-maximo">Municipio: N/A</small>
                                                </div>
                                            </div>

                                            <div class="card text-white bg-danger mb-3" style="cursor: pointer;">
                                                <div class="card-body p-2 text-center">
                                                    <h4 class="mb-0" id="costo-min">0</h4>
                                                    <small class="d-block">Costo Mínimo</small>
                                                    <small class="d-block" id="municipio-costo-minimo">Municipio: N/A</small>
                                                </div>
                                            </div>  

                                            <!-- <div class="card text-center mb-3">
                                                <div class="card-body p-2">
                                                    <h6 class="mb-1" id="total-municipios">0</h6>
                                                    <small class="text-muted">Municipios</small>
                                                </div>
                                            </div> -->
                                        </div>

                                        <!-- MAPA CENTRO -->
                                        <div class="col-md-6">
                                            <div class="card h-100 w-100 card-mapa">
                                                <style>
                                                    .card-mapa {
                                                        max-width: 100% !important;
                                                    }
                                                    
                                                    #contenido-mapa {
                                                        width: 100% !important;
                                                        max-width: 800px !important;
                                                        margin: 0 auto !important;
                                                        overflow-x: auto !important;
                                                        padding: 1rem !important;
                                                    }
                                                    
                                                    #contenido-mapa svg {
                                                        max-width: 100% !important;
                                                        height: auto !important;
                                                    }
                                                </style>
                                                <div class="card-header d-flex justify-content-center align-items-center gap-3 flex-wrap text-center">
                                                    <h5 class="mb-0 fw-bold">Mapa</h5>
                                                    <button class="btn btn-primary px-4 py-2 fs-6 fw-semibold" data-toggle="modal">
                                                        <i class="bi bi-geo-alt-fill me-1"></i> Geolocalización
                                                    </button>
                                                </div>

                                                <div class="card-body text-center">
                                                    <div id="contenido-mapa" class="cuerpoMapa w-100">
                                                        <!-- SVG del mapa -->
                                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="30 50 1000 1200" width="100%" height="auto">
                                                            <?php foreach ($santander as $key => $value): ?>
                                                            <g id="<?= strtoupper($value['path']) ?>">
                                                                <path id="<?= strtoupper($value['path']) ?>"
                                                                    d="<?= $value['d'] ?>" fill="<?= $value['color'] ?>"
                                                                    class="municipios mapaClick <?= getClasePorcentaje(0.2) ?>"
                                                                    data-base-url="<?= getUrl() . 'municipios_secretaria_informacion.php?mun=' . $value['codigo_muncipio'] . '&dep=' . $value['codigo_departamento'] ?>"
                                                                    data-url="<?= getUrl() . 'municipios_secretarias_administrativa.php?mun=' . $value['codigo_muncipio'] . '&dep=' . $value['codigo_departamento'] ?>"
                                                                    data-name="<?= strtolower($value['municipio']) ?>"
                                                                    title="<?= strtoupper(str_replace("-", " ", $value['nombre_mapa'])) ?>"
                                                                    stroke="#000" stroke-miterlimit="10" stroke-width="0.3px">
                                                                </path>
                                                            </g>
                                                            <?php endforeach; ?>
                                                            <?php require_once 'nombres_mapa_santander.php' ?>
                                                        </svg>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Indicadores DERECHA -->
                                        <div class="col-md-3">
                                            <div>
                                                <!-- <div class="card-body py-2 px-3">
                                                    <div class="row align-items-center">
                                                        <div class="col-md-12 col-xs-12">
                                                            <label for="tbl_secretarias_id" class="form-label fw-bold mb-1">
                                                                Seleccionar Secretaría
                                                            </label>
                                                            <select name="tbl_secretarias_id" id="tbl_secretarias_id" class="form-select form-control">
                                                                <option value="">Seleccione</option>
                                                                <?= $optionSecretarias ?>
                                                            </select>
                                                        </div>
                                                    </div>
                                                </div> -->
                                            </div>

                                            <div>
                                                <!-- <div class="card-body py-2 px-3">
                                                    <div class="row align-items-center">
                                                        <div class="col-md-12 col-xs-12">
                                                            <label for="componente">Dependencia<span class="text-danger mb-1">*</span></label>
                                                            <select class="form-control" id="componente" name="componente">
                                                            </select>
                                                        </div>
                                                    </div>
                                                </div> -->
                                            </div>

                                            <div>
                                                <!-- <div class="card-body py-2 px-3">
                                                    <div class="row align-items-center">
                                                        <div class="col-md-12 col-xs-12">
                                                            <label for="componente">Responsables<span class="text-danger mb-1">*</span></label>
                                                            <select class="form-control" id="componente" name="componente">
                                                            </select>
                                                        </div>
                                                    </div>
                                                </div> -->
                                            </div>

                                            <div class="card mb-3">
                                                <div class="card-header text-white p-2 text-center">
                                                    <h6 class="mb-0">Distribución por Provincia</h6>
                                                </div>
                                                <div class="card-body p-2">
                                                    <canvas id="graficoProvincias2" height="460"></canvas>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card-body">
                        <div id="modalGeocalizacion" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
                            <div class="modal-dialog modal-dialog-centered" role="document">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="exampleModalCenterTitle">Geolocalización</h5>
                                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                                    </div>
                                    <div class="modal-body">
                                        <div id="map" style="height: 600px; width: 100%;"></div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Google Maps JavaScript API -->
                        <script async defer src="https://maps.googleapis.com/maps/api/js?key=AIzaSyAbqZVEnSpIPF5dKe89pju2rmQaM58wvY0&callback=initMap"></script>
                    </div>
                </div>
            </div>
            <?php include 'admin/include/footer.php'; ?>
        </div>

        <?php include 'admin/include/gerenic_script.php'; ?>        
        <script src="https://code.highcharts.com/highcharts.js"></script>
        <script src="https://code.highcharts.com/modules/exporting.js"></script>
        <script src="https://code.highcharts.com/modules/export-data.js"></script>
        <script src="https://code.highcharts.com/modules/accessibility.js"></script>
                

        <!-- Required Js -->
        <script src="assets/js/vendor-all.min.js"></script>
        <script src="assets/js/plugins/bootstrap.min.js"></script>
        <script src="assets/js/pcoded.min.js"></script>
        <script type="text/javascript" src="admin/js/mapa_secretaria_administrativa.js"></script>
        <script type="text/javascript" src="admin/js/dash_adminitrativa.js"></script>   

        <script>
            $(document).off("click", ".mapaClick").on("click", ".mapaClick", function() {
                window.location.href = $(this).data("url");
            });
            
            $("img").each(function(index, el) {
                $(this).attr("data-bs-toggle", "tooltip");
                $(this).attr("data-bs-placement", "left");
                tooltip = new bootstrap.Tooltip($(this)[0], {})
            });
            
            document.querySelectorAll('.tab-list .tab').forEach(tab => {
                tab.addEventListener('click', () => {
                    document.querySelectorAll('.tab-list .tab').forEach(t => t.classList.remove('active'));
                    document.querySelectorAll('.tab-content').forEach(tc => tc.classList.remove('active'));
                    tab.classList.add('active');
                    document.getElementById(tab.getAttribute('data-tab')).classList.add('active');
                });
            });
        

            $(document).ready(function() {

                var distribucionCompleta = JSON.parse('<?= $distribucionJSON ?>');
                var distribucionData = distribucionCompleta && distribucionCompleta.output && distribucionCompleta.output.response && distribucionCompleta.output.response.bienes_por_municipio;

                if (distribucionData && distribucionData.length > 0) {
                    var chartData = distribucionData.map(function(item) {
                        return {
                            name: item.nombre_municipio,
                            y: parseInt(item.total_bienes)
                        };
                    });
                    
                    Highcharts.chart('graficoProvincias2', {
                        chart: {
                            type: 'bar',
                            height: 460  //alto 
                        },
                        title: {
                            text: 'Distribución por Municipios', // 
                            align: 'center'
                        },
                        xAxis: {
                            categories: chartData.map(item => item.name),
                            title: {
                                text: 'Municipios'
                            }
                        },
                        yAxis: {
                            min: 0,
                            title: {
                                text: 'Total de Bienes'
                            },
                            labels: {
                                overflow: 'justify'
                            }
                        },
                        legend: {
                            enabled: false
                        },
                        tooltip: {
                            pointFormat: 'Total de bienes: <b>{point.y}</b>'
                        },
                        plotOptions: {
                            bar: {
                                dataLabels: {
                                    enabled: true
                                }
                            }
                        },
                        series: [{
                            name: 'Bienes',
                            data: chartData.map(item => item.y)
                        }]
                    });
                }
            });             
            
        </script>
    </div>

    <div class="modal fade" id="modalMunicipio" tabindex="-1" aria-labelledby="modalMunicipioLabel" aria-hidden="true">
        <div class="modal-dialog modal-fullscreen modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalMunicipioLabel">Información Administrativa por Municipios</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar" onclick="cerrarModalmodalMunicipio()">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body" style="padding: 15px;">
                    <div class="row">
                        </div>
                    <div class="table-responsive">
                        <table id="dynamictable" class="table table-bordered table-hover" width="100%">
                            </table>
                    </div>
                </div>
            </div>
        </div>
    </div>


</body>

</html>