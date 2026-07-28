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
    if ($exists == !false) {
        $final =  substr($final, 0, $exists);
        return $final;
    } else {
        return $final;
    }
}

require_once './admin/include/generic_classes.php';
include './admin/classes/Colombia.php';
include './admin/classes/Ciudad.php';
include './admin/classes/Pilar.php';
require './admin/classes/Departamento.php';
include './admin/db/coloress.php';
include './admin/classes/Secretarias.php';

// Obtener permisos
/* $permissions = PagePermissions::crudForCurrentPage();

// Validación de permiso de visualización
if (!$permissions['view']) {
    require_once 'permiso_denegado.php';
    exit;
}
 */

// Validación del tipo de usuario
$userType = SessionData::getUserType();
$isSecretario = ($userType === Util::Secretario_Despacho());
$secretariaUsuarioId = SessionData::getSecretaria();

// Obtener secretarías disponibles
$arr = Secretarias::getAll(null);
$isvalid = $arr['output']['valid'];
$arr = $arr['output']['response'];

// Armar opciones del <select>
$optionSecretarias = "";

if ($isSecretario) {
    // Solo mostrar la secretaría asignada al secretario
    foreach ($arr as $val) {
        if ($val['id'] == $secretariaUsuarioId) {
            $optionSecretarias = "<option value='" . $val['id'] . "' selected>" . $val['secretaria'] . "</option>";
            break;
        }
    }
} else {
    // Mostrar todas las secretarías
    foreach ($arr as $val) {
        $selected = ($val['id'] == $_REQUEST['secretaria']) ? "selected" : "";
        $optionSecretarias .= "<option value='" . $val['id'] . "' $selected>" . $val['secretaria'] . "</option>";
    }
}

// Cambia manualmente la URL cuando es secretario
if ($isSecretario) : ?>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var select = document.getElementById('secretariaId');
            if (select) {
                // Si la URL no tiene el parámetro 'secretaria', lo agrega y recarga la página
                var params = new URLSearchParams(window.location.search);
                if (params.get('secretaria') != select.value) {
                    params.set('secretaria', select.value);
                    window.location.search = params.toString();
                }
            }
        });
    </script>
<?php endif; ?>
<?php
// Información resumen Alcaldías
$arr = array('codigoMunicipio' => Util::getDepartamentoPrincipal(), 'secretariaId' => $_REQUEST['secretaria']);

$data = Colombia::getInformacionResumenAlcaldiasBySecretariaColoresMapa($arr);
$santander =  $data['output']['response'];
$municipioUsuarioLogueado = SessionData::getCodigoMunicipio();
$userType = SessionData::getUserType();
$isAdmin = ($userType === Util::Administrador() || $userType === Util::SuperAdministrador());
$isSecretario = ($userType === Util::Secretario_Despacho() || $userType === Util::Auxiliar()|| $userType == Util::Auxiliar_secret_gob());
$isAlcalde = ($userType === Util::Alcalde() || $userType === Util::Auxiliar_Alcalde());
?>

<body class="">
    <!-- [ Pre-loader ] start -->
    <div class="loader-bg">
        <div class="loader-track">
            <div class="loader-fill"></div>
        </div>
    </div>
    <!-- [ Pre-loader ] End -->
    <!-- [ navigation menu ] start -->
    <?php
    include './admin/include/navbar.php';
    ?>
    <!-- [ navigation menu ] end -->
    <!-- [ Header ] start -->
    <?php
    include './admin/include/header.php';
    ?>
    <!-- [ Header ] end -->

    <!-- [ Header ] end -->
    <!-- [ Main Content ] start -->
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
                                                <h5 class="m-b-10">Resumen alcaldías</h5>
<?php include './admin/include/btn_back.php'; ?>
                                            </div>
                                            <ul class="breadcrumb">
                                                <li class="breadcrumb-item"><a href="index.html"><i
                                                            class="feather icon-home"></i></a></li>
                                                <li class="breadcrumb-item"><a href="#!">Resumen alcaldías</a></li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <!-- Card con el Mapa -->
                                <div class="col-md-6">
                                    <div class="card">
                                        <div class="card-header">
                                            <h5>Mapa de proyectos no leídos</h5>
                                        </div>
                                        <div class="card-body text-center">
                                            <div class="cuerpoMapa w-100">
                                                <div class="santander munis">
                                                    <!-- SVG del mapa -->
                                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="30 50 1000 1200" width="100%" height="100%">
                                                        <?php foreach ($santander as $key => $value) : ?>
                                                            <g id="<?php echo strtoupper($value['path']); ?>">
                                                                <path id="<?php echo strtoupper($value['path']); ?>"
                                                                    d="<?php echo $value['d']; ?>"
                                                                    fill="<?php echo ($value["color"]) ?>"
                                                                    onClick="handlePolygonClick(this)"
                                                                    class="municipios mapaClick <?php echo getClasePorcentaje(0, 2); ?>"
                                                                    data-base-url="<?php echo getUrl() . 'proyectos_seguimiento_alcaldias.php?mun=' . $value['codigo_muncipio'] . '&dep=' . $value['codigo_departamento']; ?>"
                                                                    data-url="<?php echo getUrl() . 'proyectos_seguimiento_alcaldias.php?mun=' . $value['codigo_muncipio'] . '&dep=' . $value['codigo_departamento']; ?>"
                                                                    data-name="<?php echo strtolower($value['municipio']); ?>"
                                                                    title="<?php echo strtoupper(str_replace("-", " ", $value['nombre_mapa'])); ?>"
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
                                </div>

                                <!-- Card derecha con Select y Tabla -->
                                <div class="col-md-6">
                                    <div class="card">
                                        <div class="card-header">
                                            <h5>Opciones y Referencia</h5>
                                        </div>
                                        <div class="card-body">
                                            <!-- Select -->
                                            <div class="form-group">
                                                <label class="floating-label" for="Eje">Secretaría <span class="text-danger mb-1">*</span></label>
                                                <select class="form-control" onchange="updateUrlSecretaria(this)" id="secretariaId" name="secretariaId">
                                                    <?php echo $optionSecretarias; ?>
                                                </select>
                                            </div>

                                            <!-- Tabla de valores -->
                                            <div class="form-group">
                                                <h6 class="mb-2">Tabla de Valores de Referencia</h6>
                                                <div class="table-responsive">
                                                    <table class="table table-sm" style="width: auto; font-size: 0.85rem;">
                                                        <thead>
                                                            <tr>
                                                                <th scope="col">Proyecto no Leído</th>
                                                                <th scope="col">Proyecto Leído</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            <tr>
                                                                <td><span class="color-circle color-red"></span></td>
                                                                <td><span class="color-circle color-green"></span></td>
                                                            </tr>
                                                        <tbody>
                                                            <tr>
                                                            </tr>
                                                        </tbody>
                                                    </table>
                                                </div>
                                            </div>

                                        </div>
                                    </div>
                                </div>
                            </div>


                            <div class="card-body">
                                <div id="exampleModalCenter" class="modal fade" tabindex="-1" role="dialog"
                                    aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
                                    <div class="modal-dialog modal-dialog-centered" role="document">
                                        <div class="modal-content">
                                            <!-- <div class="modal-header">
                            <h5 class="modal-title" id="exampleModalCenterTitle">Geolocalización</h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                                    aria-hidden="true">&times;</span></button>
                        </div> -->
                                            <div class="modal-body">
                                                <div id="map" style="height: 600px; width: 100%;"></div>

                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>

                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <?php include 'admin/include/footer.php'; ?>
                            <?php include 'admin/include/gerenic_script.php'; ?>

                            <!-- Required Js -->
                            <script src="assets/js/vendor-all.min.js"></script>
                            <script src="assets/js/plugins/bootstrap.min.js"></script>
                            <script src="assets/js/pcoded.min.js"></script>
                            <script type="text/javascript" src="admin/js/mapa_resumenalcaldias.js"></script>
                            <script>
                                //Cuando se selecciona una secretaria se recarga para no tener que hacer doble click en el mapa 
                                document.getElementById("secretariaId").addEventListener("change", function() {
                                    var selectedValue = this.value;
                                    var url = new URL(window.location.href);
                                    url.searchParams.set('secretaria', selectedValue);
                                    window.location.href = url.toString();
                                });

                                function handlePolygonClick(element) {
                                    const url = element.getAttribute('data-url');

                                    const queryString = url.split("?")[1];
                                    const param = new URLSearchParams(queryString);
                                    const mun = param.get("mun");
                                    if (<?= $isAlcalde ? 'true' : 'false' ?>) {
                                        if (mun !== "<?= $municipioUsuarioLogueado ?>") {
                                            Swal.fire({
                                                title: "¡Atención!",
                                                text: "No tiene permiso para ver los proyectos de este municipio.",
                                                icon: "warning"
                                            });
                                            return;
                                        }
                                    }
                                    // Obtener el valor de secretaria de la URL actual
                                    const params = new URLSearchParams(window.location.search);
                                    const secretaria = params.get('secretaria');
                                    const form = document.createElement('form');
                                    form.method = 'POST';
                                    form.action = url;

                                    // Agregar el parámetro secretaria si existe
                                    if (secretaria) {
                                        const inputSecretaria = document.createElement('input');
                                        inputSecretaria.type = 'hidden';
                                        inputSecretaria.name = 'secretaria';
                                        inputSecretaria.value = secretaria;
                                        form.appendChild(inputSecretaria);
                                    }

                                    // Si hay otros parámetros en la URL base, agrégalos como campos ocultos
                                    if (url.includes('?')) {
                                        const urlParams = new URLSearchParams(url.split('?')[1]);
                                        for (const [key, value] of urlParams.entries()) {
                                            const input = document.createElement('input');
                                            input.type = 'hidden';
                                            input.name = key;
                                            input.value = value;
                                            form.appendChild(input);
                                        }
                                        form.action = url.split('?')[0];
                                    }

                                    document.body.appendChild(form);
                                    form.submit();
                                }
                            </script>
</body>

</html>
<style>
    .nombres {
        font-family: "IBM Plex Sans", sans-serif !important;
    }

    .fondo {
        background-color: #FC0707;
        padding: 2px 4px;
        /* Añade un poco de espacio alrededor del texto */
        color: white;
        /* Asegura que el texto sea legible */
        display: inline-block;
        /* Asegura que el fondo solo cubra el texto */
    }

    .content-map {
        background-color: #ffffff !important;
        padding: 20px 0;
    }


    #mapa {
        background-color: transparent;
        background-repeat: no-repeat;
        background-position: center;
        width: 100%;
        height: auto;
        margin: 0 auto;
        text-align: center;
        padding: 0.1px 0;
    }

    #mapa svg {
        max-width: 950px;

        width: 100%;

    }

    #mapa svg path {
        fill: #fff;
        transition: all .4s;
    }

    #mapa svg path:hover {
        fill: #636363
    }

    #mapa img {
        position: absolute;
    }
</style>