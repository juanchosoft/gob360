<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include './admin/include/head.php';

require './admin/include/generic_classes.php';

include './admin/classes/Pilar.php';
include './admin/classes/Colombia.php';
include './admin/db/coloress.php';


$responsePilares = Pilar::getAll(null);
$arrPilares = $responsePilares['output']['response'] ?? [];
$optionPilares = '<option value="">Seleccione un Pilar</option>';

foreach ($arrPilares as $pilar) {
    $nombrePilar = $pilar['nombre_pilar'] ?? $pilar['nombre'] ?? 'Pilar sin nombre'; 
    $optionPilares .= '<option value="' . htmlspecialchars($pilar['id']) . '">' . htmlspecialchars($nombrePilar) . '</option>';
    
}
?>

<body class="">
    <div class="loader-bg">
        <div class="loader-track">
            <div class="loader-fill"></div>
        </div>
    </div>
    <?php
    include './admin/include/navbar.php';
    ?>
    <?php
    include './admin/include/header.php';
    ?>
    <div class="pcoded-main-container">
        <div class="pcoded-content">
            <div class="page-header">
                <div class="page-block">
                    <div class="row align-items-center">
                        <div class="col-md-12">
                            <div class="d-flex justify-content-between align-items-center">
                                <h5 class="m-b-10">Avances por Tiempo</h5>
                                <?php include './admin/include/btn_back.php'; ?>
                            </div>
                            <ul class="breadcrumb">
                                <li class="breadcrumb-item"><a href="index.html"><i class="feather icon-home"></i></a></li>
                                <li class="breadcrumb-item"><a href="#!">Avances por Tiempo</a></li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-sm-12">
                    <div class="card">
                        <div class="card-header">
                            <h5>Seleccione un municipio de su interés</h5>
                            <div class="card-header-right">
                                <div class="btn-group card-option">
                                    <button type="button" class="btn dropdown-toggle btn-icon" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                        <i class="feather icon-more-horizontal"></i>
                                    </button>
                                    <ul class="list-unstyled card-option dropdown-menu dropdown-menu-right">
                                        <li class="dropdown-item full-card"><a href="#!"><span><i class="feather icon-maximize"></i> maximize</span><span style="display:none"><i class="feather icon-minimize"></i> Restore</span></a></li>
                                        <li class="dropdown-item minimize-card"><a href="#!"><span><i class="feather icon-minus"></i> collapse</span><span style="display:none"><i class="feather icon-plus"></i> expand</span></a></li>
                                        <li class="dropdown-item reload-card"><a href="#!"><i class="feather icon-refresh-cw"></i> reload</a></li>
                                        <li class="dropdown-item close-card"><a href="#!"><i class="feather icon-trash"></i> remove</a></li>
                                    </ul>
                                </div>
                            </div>
                        </div>

                        <div class="card-body">
                            <form id="formBuscador">

                                <div class="row justify-content-center">

                                    <!-- Select Pilar -->
                                    <div class="col-md-5 mb-3">
                                        <label for="pilarSelectId" class="font-weight-bold small">Pilar</label>
                                        <select class="form-control" id="pilarSelectId" name="pilar_id">
                                            <?php echo $optionPilares; ?>
                                        </select>
                                    </div>

                                    <!-- Select Municipio -->
                                    <div class="col-md-5 mb-3">
                                        <label for="tbl_municipio_id" class="font-weight-bold small">Municipio</label>
                                        <select class="form-control" id="tbl_municipio_id" name="tbl_municipio_id">
                                            <option value="">Cargando municipios...</option>
                                        </select>
                                    </div>

                                </div>

                                <!-- Botón centrado -->
                                <div class="row mt-2">
                                    <div class="col-12 d-flex justify-content-center">
                                        <button type="button" class="btn btn-primary px-4 py-2" id="btnCargarMapas">
                                            <i data-feather="search" class="me-2"></i>Buscar
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>

                        <div class="card-body">
                            <div id="exampleModalCenter" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
                                <div class="modal-dialog modal-lg" role="document">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title" id="exampleModalCenterTitle">Factores Inestabilidad Vereda</h5>
<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                        </div>
                                        <div class="modal-body">
                                            <div class="table-container">
                                                <table class="table table-striped table-bordered">
                                                    <thead class="table-dark">
                                                        <tr>
                                                            <th>Factor</th>
                                                            <th>Acción Realizada</th>
                                                            <th>Cantidad</th>
                                                            <th>Responsables</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <tr>
                                                            <td>
                                                                <div class="factor-icon">
                                                                    <img src="assets/iconos/agua.png" alt="Escasez de agua Icono" width="30px">
                                                                    <span>Escasez de agua</span>
                                                                </div>
                                                            </td>
                                                            <td>
                                                                Recolección y aprovechamiento de aguas lluvias, práctica que permite disminuir la presión sobre fuentes tradicionales de abastecimiento y mitigar los efectos de la escasez de agua. Además, es esencial la conservación de ecosistemas y procesos hidrológicos que sustentan la oferta de agua.
                                                            </td>
                                                            <td>30</td>
                                                            <td>
                                                                Ministerio de Ambiente y Desarrollo Sostenible, Corporaciones Autónomas Regionales (CAR), Comisión de Regulación de Agua Potable y Saneamiento Básico (CRA), Alcaldía, Gobernación
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <td>
                                                                <div class="factor-icon">
                                                                    <img src="assets/iconos/agricola.png" alt="Conflictos por uso del suelo Icono" width="30px">
                                                                    <span>Conflictos por uso del suelo</span>
                                                                </div>
                                                            </td>
                                                            <td>
                                                                Actualizar el POT en consulta con comunidades locales, expertos ambientales y sectores productivos. Establecer zonas de protección estricta en ecosistemas sensibles, como microcuencas y áreas de recarga hídrica.
                                                            </td>
                                                            <td>20</td>
                                                            <td>
                                                                Alcaldía de Barichara, Concejo Municipal, Empresas agroindustriales y turísticas responsables
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <td>
                                                                <div class="factor-icon">
                                                                    <img src="assets/iconos/incendios_forestales.png" alt="Incendios forestales Icono" width="30px">
                                                                    <span>Incendios forestales</span>
                                                                </div>
                                                            </td>
                                                            <td>
                                                                Establecer cortafuegos en áreas estratégicas de las veredas con mayor riesgo de incendios. Promover reforestación con especies nativas resistentes al fuego en zonas degradadas. Dotar al Cuerpo de Bomberos de Barichara con vehículos adecuados, bombas de agua portátiles y herramientas especializadas para combatir incendios forestales.
                                                            </td>
                                                            <td>50</td>
                                                            <td>
                                                                Alcaldía de Barichara, Cuerpo de Bomberos de Barichara, Juntas de Acción Comunal (JAC)
                                                            </td>
                                                        </tr>
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
<!-- modal comparativo -->
                <div id="mapaComparativoModal" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="mapaComparativoModalTitle" aria-hidden="true">
                    <div class="modal-dialog modal-xl" role="document"> <div class="modal-content">
                        <div class="modal-header bg-primary text-white">
                            <h5 class="modal-title" id="modalMapasTitle">Cargando Mapas Comparativos...</h5>
                            <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                        </div>
                        <div class="modal-body" style="padding: 45px;">
                            <div id="modalMapasBody" >
                                <p class="text-center text-muted">Contenido de los mapas se cargará aquí...</p>
                            </div>
                        </div>
                    </div>
                </div>
<!-- final modal comparativo -->      
        </div>
    </div>
</div>
    <!-- Required Js -->
    <script src="assets/js/vendor-all.min.js"></script>
    <script src="assets/js/plugins/bootstrap.min.js"></script>
    <script src="assets/js/pcoded.min.js"></script> 
    <script type="text/javascript" src="admin/js/lib/util.js"></script>
    <script type="text/javascript" src="admin/js/departamento.js"></script>
    <script src="https://unpkg.com/feather-icons"></script>
<script>feather.replace();</script>

    <script>
        const CODIGO_DEPARTAMENTO_AVANCES = '68'; 

        
        const CARGA_AVANCES = {
            loadMunicipios: function() {
                
                let q = {
                    op: "ciudadget",
                    codigo_departamento: CODIGO_DEPARTAMENTO_AVANCES,
                };

                $.ajax({
                    data: q,
                    type: "GET",
                    dataType: "json",
                    url: "admin/ajax/rqst.php", 
                    
                    beforeSend: function() {
                    },

                    success: function(data) {

                        if (typeof DEPARTAMENTO !== 'undefined' && typeof DEPARTAMENTO.getMunicipiosHandler === 'function') {
                            DEPARTAMENTO.getMunicipiosHandler(data);
                        } else {
                            $("#tbl_municipio_id").html('<option value="">Error: Librería de municipios no cargada</option>');
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error("DEBUG ERROR: AJAX Error al cargar municipios:", status, error, xhr.responseText);
                        $("#tbl_municipio_id").html('<option value="">Error al cargar municipios</option>');
                    }
                });
            }
        };

        $(function() {
            
            AVANCES_MAPA.init();
            
            if (typeof DEPARTAMENTO !== 'undefined' && typeof DEPARTAMENTO.getMunicipiosHandler === 'function') {
                CARGA_AVANCES.loadMunicipios();
            } else {
                console.error("DEBUG ERROR: El módulo DEPARTAMENTO (de departamento.js) no se cargó correctamente.");
                $("#tbl_municipio_id").html('<option value="">Error al cargar municipios (Módulo DEPARTAMENTO no definido)</option>');
            }
        });

        const AVANCES_MAPA = {
            MODAL_ID: '#mapaComparativoModal',
            ENDPOINT: './admin/classes/get_secretaria_accion_por_pilar.php',
            CODIGO_DEPARTAMENTO_FIJO: CODIGO_DEPARTAMENTO_AVANCES, 

            init() {
                $("#btnCargarMapas").on("click", function (e) {
                    e.preventDefault(); 
                    AVANCES_MAPA.loadMapasPorPilar();
                });
            },

            loadMapasPorPilar() {
                const pilarSelect = $("#pilarSelectId");
                const municipioSelect = $("#tbl_municipio_id"); 
                const pilarId = pilarSelect.val();
                const municipioId = municipioSelect.val(); 
                const departamentoId = AVANCES_MAPA.CODIGO_DEPARTAMENTO_FIJO; 
                const btnSelector = $("#btnCargarMapas");

                if (!pilarId || pilarId === '') { alert('Por favor, seleccione un **Pilar** primero.'); return; }
                if (!municipioId || municipioId === '' || municipioId === 'seleccione') { alert('Por favor, seleccione un **Municipio**.'); return; }


                const pilarNombre = pilarSelect.find('option:selected').text();
btnSelector.html('<i class="feather mr-2 icon-check-circle"></i>Seleccionar');
                btnSelector.prop('disabled', true);
                
                const datos = { pilar_id: pilarId, codigo_municipio: municipioId, codigo_departamento: departamentoId };

                $.ajax({
                    url: AVANCES_MAPA.ENDPOINT,
                    method: 'GET',
                    data: datos,
                    success: function(response) {
                        $("#modalMapasBody").html(response);
                        $("#modalMapasTitle").text(`Comparativo de Avances - ${pilarNombre}`);
                        $('#mapaComparativoModal').modal('show'); 
                    },
                    error: function(xhr, status, error) {
                        let errorMessage = 'Error al cargar la información: ' + error;
                        const errorDiv = `<div class="alert alert-danger" role="alert">${errorMessage}</div>`;
                        $("#modalMapasBody").html(errorDiv);
                        console.error("AJAX Error:", status, error, xhr.responseText);
                        $('#mapaComparativoModal').modal('show');
                    },
                    complete: function() {
    btnSelector.html('<i data-feather="search" class="me-2"></i>Buscar');
    feather.replace();
    btnSelector.prop('disabled', false);
}

                });
            }
        };
    </script>
</body>

</html>