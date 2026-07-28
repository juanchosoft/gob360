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
include './admin/classes/Ciudad.php';
require './admin/classes/Departamento.php';
include './admin/db/coloress.php';
include './admin/classes/Secretarias.php';
include './admin/classes/AccionSecretaria.php';
include './admin/classes/SecretariasMunicipio.php';

// Obtener secretaría y acción
$secretaria = intval($_REQUEST['secretaria']) ?? Util::getSecretariaPrincipal();
$accion = $_REQUEST['accion'] ?? 'Capacitacion Fiscal y Financiera';

// Acciones por secretaría
$responseAccionSecretarias = [];
$isAccionSecretaria = false;
if (!empty($secretaria)) {
    $accionSecretaria = AccionSecretaria::getAll(['id' => $secretaria]);
    $isAccionSecretaria = $accionSecretaria['output']['valid'] ?? false;
    $responseAccionSecretarias = $accionSecretaria['output']['response'] ?? null;
} else {
    echo "<script>alert('Información enviada no es correcta'); window.location = 'dashboard.php';</script>";
    exit;
}

$userType = SessionData::getUserType();
$isSecretario = ($userType === Util::Secretario_Despacho() || $userType === Util::Auxiliar()|| $userType == Util::Auxiliar_secret_gob());
$isAlcalde = ($userType === Util::Alcalde() || $userType === Util::Auxiliar_Alcalde());

// Se determina si el select debe estar deshabilitado
$isDisabled = '';
if ($isSecretario || $isAlcalde) {
    $isDisabled = 'disabled';
}


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
// Información del mapa y colores
$arrMapa = [
    'codigoMunicipio' => Util::getDepartamentoPrincipal(),
    'secretariaId' => $secretariaParaConsulta,
    'accion' => $accion
];
$data = Colombia::getInformacionSecretariaColoresMapa($arrMapa);
$santander = $data['output']['response'];
$puntajes = $data['output']['puntajes'];

// Información del select de acciones
$selectLicores = "Operativos Contrabando licores";
$selectCigarrillos = "Operativos Contrabando cigarrillos";
$selectFiscalYFinanciera = "Capacitacion Fiscal y Financiera";
$selectCervezas = "Operativos Contrabando cerveza";

// Información de los proyectos en ejecución
$arrEjecucion = [
    'codigoMunicipio' => Util::getDepartamentoPrincipal(),
    'secretariaId' => $secretariaParaConsulta,
    'accion' => $accion
];
$responseTotalEjecucionSecretarias = Secretarias::getTotalEjecucionSecretaria($arrEjecucion);
$dataTotalEjecucionSecretarias = $responseTotalEjecucionSecretarias['output']['response'];

// Variables adicionales para Hacienda
$infoCigarrillos = [];
$infoTabacos = [];
$infoLicores = [];
$infoCerveza = [];

if ($secretaria == Util::getSecretariaIdHacienda()) {
    // Datos adicionales para Hacienda
    $haciendaDatos = $responseTotalEjecucionSecretarias['output']['response'];
    $estampillas = $responseTotalEjecucionSecretarias['output']['estampillas'];

    $infoCigarrillos = $responseTotalEjecucionSecretarias['output']['cigarrillos'];
    $infoTabacos = $responseTotalEjecucionSecretarias['output']['tabaco'];
    $infoLicores = $responseTotalEjecucionSecretarias['output']['licores'];
    $infoCerveza = $responseTotalEjecucionSecretarias['output']['cerveza'];

    $datos = $haciendaDatos[0] ?? [];
}
?>

<!-- jQuery -->
<script src="https://code.jquery.com/jquery-3.7.1.js"></script>
<!-- Popper.js -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.3/umd/popper.min.js"></script>
<!-- DataTables -->
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">

<body class="">
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
    </style>
    <!-- [ Pre-loader ] start -->
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
                                                <h5 class="m-b-10">Informes Secretarias</h5>
                                                <?php include './admin/include/btn_back.php'; ?>
                                            </div>
                                            <ul class="breadcrumb">
                                                <li class="breadcrumb-item"><a href="index.html"><i
                                                            class="feather icon-home"></i></a></li>
                                                <li class="breadcrumb-item"><a href="#!">Informe Secretaria </a></li>
                                                <li class="breadcrumb-item"><a href="#!">Actividades</a></li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <!-- Columna izquierda: Mapa en una card -->
                                <div class="col-lg-8 mb-4">
                                    <div class="card h-100 w-100 card-mapa">
                                        <style>
                                            /* Estilos internos solo para esta tarjeta */
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
                                        <div
                                            class="card-header d-flex justify-content-center align-items-center gap-3 flex-wrap text-center">
                                            <h5 class="mb-0 fw-bold">Mapa</h5>
                                            <button id="botonGeocalizacion" name="botonGeocalizacion" type="button"
                                                class="btn btn-primary px-4 py-2 fs-6 fw-semibold" data-toggle="modal"
                                                data-target="#modalGeocalizacion">
                                                <i class="bi bi-geo-alt-fill me-1"></i> Geolocalización
                                            </button>
                                        </div>

                                        <div class="card-body text-center">

                                            <div id="contenido-mapa" class="cuerpoMapa w-100">
                                                <!-- SVG del mapa -->
                                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="-50 15 850 900"
                                                    width="100%" height="auto">
                                                    <?php foreach ($santander as $key => $value): ?>
                                                        <g id="<?= strtoupper($value['path']) ?>">
                                                            <path id="<?= strtoupper($value['path']) ?>"
                                                                d="<?= $value['d'] ?>" fill="<?= $value['color'] ?>"
                                                                class="municipios mapaClick <?= getClasePorcentaje(0.2) ?>"
                                                                data-base-url="<?= getUrl() . 'municipios_secretaria_informacion.php?mun=' . $value['codigo_muncipio'] . '&dep=' . $value['codigo_departamento'] ?>"
                                                                data-url="<?= getUrl() . 'municipios_secretaria_informacion.php?mun=' . $value['codigo_muncipio'] . '&dep=' . $value['codigo_departamento'] ?>"
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

                                <!-- Columna derecha: Selector y cuadros -->
                                <div class="col-4">
                                    <div class="card mb-4 card-info-complementaria">

                                        <div class="card-body">

                                            <div class="form-group">
                                                <label class="floating-label" for="Eje">Secretaria<span
                                                        class="text-danger mb-1">*</span></label>
                                                <select <?php echo $isDisabled; ?> class="form-control" id="secretariaId" name="secretariaId"
                                                    onchange="updateUrlSecretaria(this)">
                                                    <?php echo $optionSecretarias; ?>
                                                </select>
                                            </div>
                                            <div class="form-group" id="divHacienda">

                                                <?php if ($secretaria == Util::getSecretariaIdHacienda()): ?>
                                                    <div class="form-group">
                                                        <label for="accion" class="form-label">Tipo de Acción - Filtrar en
                                                            Mapa</label>
                                                        <select onchange="updateUrlAccionHacienda(this)"
                                                            class="form-control" id="accionHacienda" name="accionHacienda">
                                                            <option value="Operativos Contrabando licores">Operativos
                                                                Contrabando licores</option>
                                                            <option value="Operativos Contrabando cigarrillos">Operativos
                                                                Contrabando cigarrillos</option>
                                                            <option value="Capacitacion Fiscal y Financiera">Capacitación
                                                                Fiscal y Financiera</option>
                                                            <option value="Operativos Contrabando cerveza">Operativos
                                                                Contrabando cerveza</option>
                                                            <!-- 
                                                        <option value="Impuesto Vehicular Recaudado">Impuesto Vehicular Recaudado</option>
                                                        <option value="Recaudo del impuesto al consumo">Recaudo del impuesto al consumo</option>
                                                        <option value="Recaudo del impuesto de registro">Recaudo del impuesto de registro</option>
                                                        <option value="Impuesto Estampillas Recaudado">Impuesto Estampillas Recaudado</option> -->
                                                        </select>
                                                    </div>
                                                    <?php
                                                    $datos = $haciendaDatos[0] ?? [];

                                                    $totalValorImpuestoRegistro = $datos['total_valor_recaudo_registro'] ?? 0;
                                                    $totalValorImpuestoAlConsumo = $datos['total_valor_recaudo_impuesto_al_consumo'] ?? 0;
                                                    $totalValorEstampilla = $datos['total_valor_estampilla'] ?? 0;
                                                    $totalValorRecaudoImpuestoVehicular = $datos['total_valor_recaudo_impuesto_vehicular'] ?? 0;


                                                    $totalIncautacionCigarrillos = $datos['total_incautacion_cigarrillos'] ?? 0;
                                                    $totalIncautacionCervezas = $datos['total_incautacion_cervezas'] ?? 0;
                                                    $totalIncautacionTabaco = $datos['total_incautacion_tabaco'] ?? 0;
                                                    $totalIncautacionLicor = $datos['total_incautacion_licores'] ?? 0;

                                                    $totalUnidades = $datos['TOTAL_UNIDADES'] ?? 0;

                                                    // TOTAL RECAUDO CIGARRILLOS, CERVEZAS, TABACO, LICOR
                                                    $TOTAL_RECAUDO_CIG_CERV_LIC_TABAC = $datos['TOTAL_RECAUDO_CIG_CERV_LIC_TABAC'] ?? 0;

                                                    // Impuesto vehicular
                                                    $TOTAL_RECAUDO_IMPUESTO_VEHICULAR = $datos['TOTAL_RECAUDO_IMPUESTO_VEHICULAR'] ?? 0;
                                                    $TOTAL_TRAMITES_IMPUESTO_VEHICULAR = $datos['TOTAL_TRAMITES_IMPUESTO_VEHICULAR'] ?? 0;
                                                    $IMPUESTO_VEHICULAR_TOTAL_RECAUDO_Y_TRAMITE = $datos['IMPUESTO_VEHICULAR_TOTAL_RECAUDO_Y_TRAMITE'] ?? 0;

                                                    // Variacion impuesto vehicular recaudo
                                                    $VARIACION_RECAUDO_IMPUESTO_VEHICULAR = $datos['VARIACION_RECAUDO_IMPUESTO_VEHICULAR'] ?? 0;
                                                    $VARIACION_TRAMITES_IMPUESTO_VEHICULAR = $datos['VARIACION_TRAMITES_IMPUESTO_VEHICULAR'] ?? 0;

                                                    $TOTAL_RECAUDO_IMPUESTO_VEHICULAR_AYER = $datos['TOTAL_RECAUDO_IMPUESTO_VEHICULAR_AYER'] ?? 0;
                                                    $TOTAL_TRAMITES_IMPUESTO_VEHICULAR_AYER = $datos['TOTAL_TRAMITES_IMPUESTO_VEHICULAR_AYER'] ?? 0;
                                                    $IMPUESTO_VEHICULAR_TOTAL_RECAUDO_Y_TRAMITE_AYER = $datos['IMPUESTO_VEHICULAR_TOTAL_RECAUDO_Y_TRAMITE_AYER'] ?? 0;

                                                    // Impuesto al registro
                                                    $TOTAL_RECAUDO_IMPUESTO_REGISTRO_TRAMITES = $datos['TOTAL_RECAUDO_IMPUESTO_REGISTRO_TRAMITES'] ?? 0;
                                                    $TOTAL_RECAUDO_IMPUESTO_REGISTRO = $datos['TOTAL_RECAUDO_IMPUESTO_REGISTRO'] ?? 0;
                                                    $TOTAL_RECAUDO_IMPUESTO_REGISTRO_Y_TRAMITE = $datos['TOTAL_RECAUDO_IMPUESTO_REGISTRO_Y_TRAMITE'] ?? 0;

                                                    // Impuesto al Consumo
                                                    $TOTAL_RECAUDO_IMPUESTO_CONSUMO_IMPORTANDO = $datos['TOTAL_RECAUDO_IMPUESTO_CONSUMO_IMPORTANDO'] ?? 0;
                                                    $TOTAL_RECAUDO_IMPUESTO_CONSUMO_NACIONAL = $datos['TOTAL_RECAUDO_IMPUESTO_CONSUMO_NACIONAL'] ?? 0;
                                                    $TOTAL_RECAUDO_IMPUESTO_CONSUMO_IMPORTANDO_Y_NACIONAL = $datos['TOTAL_RECAUDO_IMPUESTO_CONSUMO_IMPORTANDO_Y_NACIONAL'] ?? 0;

                                                    // Variacion
                                                    $VARIACION_RECAUDO_IMPORTADO_PORCENTAJE = $datos['VARIACION_RECAUDO_IMPORTADO_PORCENTAJE'] ?? 0;
                                                    $VARIACION_RECAUDO_NACIONAL_PORCENTAJE = $datos['VARIACION_RECAUDO_NACIONAL_PORCENTAJE'] ?? 0;

                                                    // Estampilla
                                                    $TOTAL_RECAUDO_ESTAMPILLA = $datos['TOTAL_RECAUDO_ESTAMPILLA'] ?? 0;

                                                    ?>

                                                    <div id="InformacionGeneral">
                                                        <?php if (isset($accion) && $accion != $selectFiscalYFinanciera): ?>
                                                            <div class="row g-3 mt-3" id="seccion-campos-visibles">

                                                                <div class="col-12">
                                                                    <h5 class="text-center mb-4">
                                                                        <i class="bi bi-shield-lock-fill me-2"></i>
                                                                        Operativos Contrabando
                                                                    </h5>

                                                                    <?php if (isset($accion) && $accion == $selectCigarrillos): ?>
                                                                        <?php if (!empty($infoCigarrillos)): ?>
                                                                            <h6>Cigarrillos</h6>
                                                                            <div class="table-responsive mb-3">
                                                                                <table class="table table-striped table-bordered">
                                                                                    <thead class="table-dark" style="background-color: #1abc9c !important;">
                                                                                        <tr>
                                                                                            <th>Tipo de Cigarrillo</th>
                                                                                            <th>Cant. Incautada</th>
                                                                                            <th>Valor</th>
                                                                                        </tr>
                                                                                    </thead>
                                                                                    <tbody>
                                                                                        <?php foreach ($infoCigarrillos as $row): ?>
                                                                                            <tr>
                                                                                                <td><?= htmlspecialchars($row['tipo_cigarrillo']) ?></td>
                                                                                                <td><?= number_format($row['total_incautacion_cigarrillos']) ?></td>
                                                                                                <td>$<?= number_format($row['total_valor_cigarrillos']) ?></td>
                                                                                            </tr>
                                                                                        <?php endforeach; ?>
                                                                                    </tbody>
                                                                                </table>
                                                                            </div>
                                                                        <?php endif; ?>

                                                                        <?php if (!empty($infoTabacos)): ?>
                                                                            <div class="">
                                                                                <div class="card shadow-sm mb-4">
                                                                                    <div class="card-body">
                                                                                        <h5 class="card-title fw-bold text-primary mb-3">
                                                                                            <i class="bi bi-box-seam me-2"></i> Tabacos
                                                                                        </h5>

                                                                                        <div class="table-responsive">
                                                                                            <table class="table table-striped table-bordered table-sm align-middle w-100 mb-0">
                                                                                                <thead class="table-dark text-center">
                                                                                                    <tr>
                                                                                                        <th>Tipo de Tabaco</th>
                                                                                                        <th>Cant. Incautada</th>
                                                                                                        <th>Valor</th>
                                                                                                    </tr>
                                                                                                </thead>
                                                                                                <tbody>
                                                                                                    <?php foreach ($infoTabacos as $row): ?>
                                                                                                        <tr>
                                                                                                            <td><?= htmlspecialchars($row['tipo_tabaco']) ?></td>
                                                                                                            <td class="text-center"><?= number_format($row['total_incautacion_tabaco']) ?></td>
                                                                                                            <td class="text-end">$<?= number_format($row['total_valor_tabaco']) ?></td>
                                                                                                        </tr>
                                                                                                    <?php endforeach; ?>
                                                                                                </tbody>
                                                                                            </table>
                                                                                        </div>
                                                                                    </div>
                                                                                </div>
                                                                            </div>

                                                                        <?php endif; ?>
                                                                    <?php endif; ?>

                                                                    <?php if (isset($accion) && $accion == $selectLicores && !empty($infoLicores)): ?>
                                                                        <h6 class="mt-4">Licores</h6>
                                                                        <div class="table-responsive mb-3">
                                                                            <table class="table table-striped table-bordered">
                                                                                <thead class="table-dark">
                                                                                    <tr>
                                                                                        <th>Tipo</th>
                                                                                        <th>Cant. Incautada</th>
                                                                                        <th>Valor</th>
                                                                                    </tr>
                                                                                </thead>
                                                                                <tbody>
                                                                                    <?php foreach ($infoLicores as $row): ?>
                                                                                        <tr>
                                                                                            <td><?= htmlspecialchars($row['tipo']) ?></td>
                                                                                            <td><?= number_format($row['total_incautacion_licores']) ?></td>
                                                                                            <td>$<?= number_format($row['total_valor_licores']) ?></td>
                                                                                        </tr>
                                                                                    <?php endforeach; ?>
                                                                                </tbody>
                                                                            </table>
                                                                        </div>
                                                                    <?php endif; ?>

                                                                    <?php if (isset($accion) && $accion == $selectCervezas && !empty($infoCerveza)): ?>
                                                                        <h6 class="mt-4">Cervezas</h6>
                                                                        <div class="table-responsive mb-3">
                                                                            <table class="table table-striped table-bordered">
                                                                                <thead class="table-dark">
                                                                                    <tr>
                                                                                        <th>Tipo de Cerveza</th>
                                                                                        <th>Cantidad Incautada</th>
                                                                                        <th>Valor</th>
                                                                                    </tr>
                                                                                </thead>
                                                                                <tbody>
                                                                                    <?php foreach ($infoCerveza as $row): ?>
                                                                                        <tr>
                                                                                            <td><?= htmlspecialchars($row['tipo']) ?></td>
                                                                                            <td><?= number_format($row['total_incautacion_cerveza']) ?></td>
                                                                                            <td>$<?= number_format($row['total_valor_cerveza']) ?></td>
                                                                                        </tr>
                                                                                    <?php endforeach; ?>
                                                                                </tbody>
                                                                            </table>
                                                                        </div>
                                                                    <?php endif; ?>

                                                                    <div class="">
                                                                        <div class="card shadow-sm mb-2">
                                                                            <div class="card-body">
                                                                                <h5 class="card-title fw-bold text-primary mb-3">
                                                                                    <i class="bi bi-cash-coin me-2"></i> Totales Generales
                                                                                </h5>

                                                                                <div class="table-responsive">
                                                                                    <table class="table table-bordered table-sm align-middle w-100 mb-0">
                                                                                        <thead class="table-light text-center">
                                                                                            <tr>
                                                                                                <th>Total Unidades</th>
                                                                                                <th>Valor Avalúo Total</th>
                                                                                            </tr>
                                                                                        </thead>
                                                                                        <tbody>
                                                                                            <tr>
                                                                                                <td class="text-center"><?= number_format($totalUnidades, 0, ',', '.') ?></td>
                                                                                                <td class="text-end">$<?= number_format($TOTAL_RECAUDO_CIG_CERV_LIC_TABAC, 0, ',', '.') ?></td>
                                                                                            </tr>
                                                                                        </tbody>
                                                                                    </table>
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                    </div>

                                                                </div>

                                                            </div>
                                                        <?php endif; ?>
                                                    </div>


                                                    <div class="row g-4 mt-4">
                                                        <!-- Recaudo Impuesto Vehicular -->
                                                        <div class="col-12" style="display: none" >
                                                            <div class="card shadow-sm mb-4">
                                                                <div class="card-body">
                                                                    <h5 class="card-title fw-bold text-primary mb-1">
                                                                        <i class="bi bi-cash-coin me-2"></i> Recaudo Impuesto Vehicular
                                                                    </h5>
                                                                    <div class="table-responsive">
                                                                        <table class="table table-sm table-bordered align-middle mb-0 w-100">
                                                                            <thead class="table-light text-center">
                                                                                <tr>
                                                                                    <th>Concepto</th>
                                                                                    <th>Valor</th>
                                                                                </tr>
                                                                            </thead>
                                                                            <tbody>
                                                                                <tr>
                                                                                    <td>Por Trámites</td>
                                                                                    <td class="text-end">
                                                                                        $<?= number_format($TOTAL_TRAMITES_IMPUESTO_VEHICULAR, 0, ',', '.') ?>
                                                                                    </td>
                                                                                </tr>
                                                                                <tr>
                                                                                    <td>Variación Trámites</td>
                                                                                    <td class="text-end" style="color: <?= ($VARIACION_TRAMITES_IMPUESTO_VEHICULAR < 0) ? 'red' : 'inherit'; ?>;">
                                                                                        <?= number_format($VARIACION_TRAMITES_IMPUESTO_VEHICULAR, 2, ',', '.') ?>%
                                                                                    </td>
                                                                                </tr>
                                                                                <tr>
                                                                                    <td>Por Recaudo</td>
                                                                                    <td class="text-end">
                                                                                        $<?= number_format($TOTAL_RECAUDO_IMPUESTO_VEHICULAR, 0, ',', '.') ?>
                                                                                    </td>
                                                                                </tr>
                                                                                <tr>
                                                                                    <td>Variación Recaudo</td>
                                                                                    <td class="text-end" style="color: <?= ($VARIACION_RECAUDO_IMPUESTO_VEHICULAR < 0) ? 'red' : 'inherit'; ?>;">
                                                                                        <?= number_format($VARIACION_RECAUDO_IMPUESTO_VEHICULAR, 2, ',', '.') ?>%
                                                                                    </td>
                                                                                </tr>
                                                                            </tbody>
                                                                            <tfoot class="table-light fw-bold">
                                                                                <tr>
                                                                                    <td>Total</td>
                                                                                    <td class="text-end">
                                                                                        $<?= number_format($IMPUESTO_VEHICULAR_TOTAL_RECAUDO_Y_TRAMITE, 0, ',', '.') ?>
                                                                                    </td>
                                                                                </tr>
                                                                            </tfoot>
                                                                        </table>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>


                                                        <!-- Resumen Día Anterior -->
                                                        <div class="col-12" style="display: none">
                                                            <div class="card shadow-sm mb-4" >
                                                                <div class="card-body">
                                                                    <h5 class="card-title fw-bold text-primary mb-1">
                                                                        <i class="bi bi-cash-coin me-2"></i> Resumen Día Anterior - Impuesto Vehicular
                                                                    </h5>

                                                                    <div class="table-responsive">
                                                                        <table class="table table-sm table-bordered align-middle mb-0 w-100">
                                                                            <thead class="table-light text-center">
                                                                                <tr>
                                                                                    <th>Concepto</th>
                                                                                    <th>Valor</th>
                                                                                </tr>
                                                                            </thead>
                                                                            <tbody>
                                                                                <tr>
                                                                                    <td>Por Trámites</td>
                                                                                    <td class="text-end">
                                                                                        $<?= number_format($TOTAL_TRAMITES_IMPUESTO_VEHICULAR_AYER, 0, ',', '.') ?>
                                                                                    </td>
                                                                                </tr>
                                                                                <tr>
                                                                                    <td>Por Recaudo</td>
                                                                                    <td class="text-end">
                                                                                        $<?= number_format($TOTAL_RECAUDO_IMPUESTO_VEHICULAR_AYER, 0, ',', '.') ?>
                                                                                    </td>
                                                                                </tr>
                                                                            </tbody>
                                                                        </table>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>


                                                        <!-- Recaudo Impuesto Registro -->
                                                        <div class="col-12">
                                                            <div class="card shadow-sm mb-1" style="display: none">
                                                                <div class="card-body">
                                                                    <h5 class="card-title fw-bold text-primary mb-1">
                                                                        <i class="bi bi-cash-coin me-2"></i> Recaudo Impuesto de Registro
                                                                    </h5>

                                                                    <div class="table-responsive">
                                                                        <table class="table table-sm table-bordered align-middle mb-0 w-100">
                                                                            <thead class="table-light text-center">
                                                                                <tr>
                                                                                    <th>Concepto</th>
                                                                                    <th>Valor</th>
                                                                                </tr>
                                                                            </thead>
                                                                            <tbody>
                                                                                <tr>
                                                                                    <td>En Trámites</td>
                                                                                    <td class="text-end">
                                                                                        $<?= number_format($TOTAL_RECAUDO_IMPUESTO_REGISTRO_TRAMITES, 0, ',', '.') ?>
                                                                                    </td>
                                                                                </tr>
                                                                                <tr>
                                                                                    <td>General</td>
                                                                                    <td class="text-end">
                                                                                        $<?= number_format($TOTAL_RECAUDO_IMPUESTO_REGISTRO, 0, ',', '.') ?>
                                                                                    </td>
                                                                                </tr>
                                                                            </tbody>
                                                                            <tfoot class="table-light fw-bold">
                                                                                <tr>
                                                                                    <td>Total</td>
                                                                                    <td class="text-end">
                                                                                        $<?= number_format($TOTAL_RECAUDO_IMPUESTO_REGISTRO_Y_TRAMITE, 0, ',', '.') ?>
                                                                                    </td>
                                                                                </tr>
                                                                            </tfoot>
                                                                        </table>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>


                                                        <!-- Recaudo Impuesto Consumo -->
                                                        <div class="col-12" style="display: none">
                                                            <div class="card shadow-sm mb-4">
                                                                <div class="card-body">
                                                                    <h5 class="card-title fw-bold text-primary mb-1">
                                                                        <i class="bi bi-cash-coin me-2"></i> Recaudo Impuesto al Consumo
                                                                    </h5>

                                                                    <div class="table-responsive">
                                                                        <table class="table table-sm table-bordered align-middle mb-0 w-100">
                                                                            <thead class="table-light text-center">
                                                                                <tr>
                                                                                    <th>Concepto</th>
                                                                                    <th>Valor</th>
                                                                                    <th>Variación</th>
                                                                                </tr>
                                                                            </thead>
                                                                            <tbody>
                                                                                <tr>
                                                                                    <td>Licor Importado</td>
                                                                                    <td class="text-end">
                                                                                        $<?= number_format($TOTAL_RECAUDO_IMPUESTO_CONSUMO_IMPORTANDO, 0, ',', '.') ?>
                                                                                    </td>
                                                                                    <td class="text-end" style="color: <?= ($VARIACION_RECAUDO_IMPORTADO_PORCENTAJE < 0) ? 'red' : 'inherit'; ?>;">
                                                                                        <?= number_format($VARIACION_RECAUDO_IMPORTADO_PORCENTAJE, 2, ',', '.') ?>%
                                                                                    </td>
                                                                                </tr>
                                                                                <tr>
                                                                                    <td>Licor Nacional</td>
                                                                                    <td class="text-end">
                                                                                        $<?= number_format($TOTAL_RECAUDO_IMPUESTO_CONSUMO_NACIONAL, 0, ',', '.') ?>
                                                                                    </td>
                                                                                    <td class="text-end" style="color: <?= ($VARIACION_RECAUDO_NACIONAL_PORCENTAJE < 0) ? 'red' : 'inherit'; ?>;">
                                                                                        <?= number_format($VARIACION_RECAUDO_NACIONAL_PORCENTAJE, 2, ',', '.') ?>%
                                                                                    </td>
                                                                                </tr>
                                                                            </tbody>
                                                                            <tfoot class="table-light fw-bold">
                                                                                <tr>
                                                                                    <td>Total</td>
                                                                                    <td class="text-end" colspan="2">
                                                                                        $<?= number_format($TOTAL_RECAUDO_IMPUESTO_CONSUMO_IMPORTANDO_Y_NACIONAL, 0, ',', '.') ?>
                                                                                    </td>
                                                                                </tr>
                                                                            </tfoot>
                                                                        </table>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>


                                                        <!-- Estampillas -->
                                                        <div class="col-12" style="display: none">
                                                            <div class="card shadow-sm mb-4">
                                                                <div class="card-body">
                                                                    <h5 class="card-title fw-bold text-primary mb-1">
                                                                        <i class="bi bi-cash-coin me-2"></i> Impuesto Estampillas
                                                                    </h5>

                                                                    <div class="table-responsive">
                                                                        <table class="table table-sm table-bordered align-middle mb-0 w-100">
                                                                            <thead class="table-light text-center">
                                                                                <tr>
                                                                                    <th>Concepto</th>
                                                                                    <th>Valor</th>
                                                                                </tr>
                                                                            </thead>
                                                                            <tbody>
                                                                                <tr>
                                                                                    <td>Recaudado</td>
                                                                                    <td class="text-end">
                                                                                        $<?= number_format($TOTAL_RECAUDO_ESTAMPILLA, 0, ',', '.') ?>
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
                                        </div>
                                    </div>
                                </div>

                            <?php endif; ?>
                            </div>

                            <div class="tabs" id="DivTotalEjecucionSecretarias">
                                <?php if ($secretaria != Util::getSecretariaIdHacienda()): ?>
                                    <ul class="tab-list">
                                        <?php foreach ($dataTotalEjecucionSecretarias as $index => $provinciaData): ?>
                                            <li class="tab <?= $index === 0 ? 'active' : '' ?>"
                                                data-tab="tab-<?= $index ?>">
                                                <?= htmlspecialchars(str_replace('_', ' ', $provinciaData['provincia'])) ?>
                                            </li>
                                        <?php endforeach; ?>
                                    </ul>
                                    <?php foreach ($dataTotalEjecucionSecretarias as $index => $provinciaData): ?>
                                        <div class="tab-content <?= $index === 0 ? 'active' : '' ?>"
                                            id="tab-<?= $index ?>">
                                            <h4 class="mb-4 text-center">
                                                <i class="bi bi-geo-alt-fill text-danger me-2"></i>
                                                <span class="fw-bold">
                                                    Provincia:
                                                    <?= htmlspecialchars(str_replace('_', ' ', $provinciaData['provincia'])) ?>
                                                </span>
                                            </h4>
                                            <!-- RESUMEN DE VALORES -->
                                            <div class="row g-3 mb-4">
                                                <?php
                                                $resumenCards = [
                                                    ['icon' => 'bi-pie-chart-fill', 'color' => 'success', 'label' => 'Valor Proyecto Total', 'value' => $provinciaData['valor_proyecto_total']],
                                                    ['icon' => 'bi-bank', 'color' => 'primary', 'label' => 'Valor Aporte Municipio Total', 'value' => $provinciaData['valor_municipio_total']],
                                                    ['icon' => 'bi-flag', 'color' => 'warning', 'label' => 'Valor Aporte Nación', 'value' => $provinciaData['valor_nacion_total']],
                                                    ['icon' => 'bi-building', 'color' => 'danger', 'label' => 'Valor Aporte Departamento', 'value' => $provinciaData['valor_departamento_total']],
                                                ];
                                                foreach ($resumenCards as $card): ?>
                                                    <div class="col-md-12 d-flex align-items-center justify-content-between p-3 border rounded bg-white shadow-sm">
                                                        <!-- Icono + texto con separación -->
                                                        <div class="d-flex align-items-center" style="gap: 5px;">
                                                            <i class="bi <?= $card['icon'] ?> text-<?= $card['color'] ?> fs-2 me-3"></i>
                                                            <div class="fw-semibold text-dark"><?= $card['label'] ?></div>
                                                        </div>

                                                        <!-- Valor a la derecha -->
                                                        <div class="fw-bold text-dark text-end">
                                                            $<?= number_format($card['value'], 2, ',', '.') ?>
                                                        </div>
                                                    </div>


                                                <?php endforeach; ?>
                                            </div>

                                            <!-- ESTADOS DE LOS PROYECTOS -->
                                            <div class="row g-3">
                                                <div class="col-12 text-center">
                                                    <h5 class="fw-bold mb-3">Estados</h5>
                                                </div>

                                                <?php
                                                $estados = [
                                                    ['label' => 'Suspendido', 'icon' => 'bi-pause-circle', 'color' => 'warning', 'value' => $provinciaData['suspendido']],
                                                    ['label' => 'Terminado', 'icon' => 'bi-check2-circle', 'color' => 'success', 'value' => $provinciaData['terminado']],
                                                    ['label' => 'Ejecutado', 'icon' => 'bi-play-circle', 'color' => 'primary', 'value' => $provinciaData['ejecutado']],
                                                    ['label' => 'En contratación', 'icon' => 'bi-clipboard-data', 'color' => 'info', 'value' => $provinciaData['en_contratacion']],
                                                    ['label' => 'En formulación', 'icon' => 'bi-pencil-square', 'color' => 'secondary', 'value' => $provinciaData['en_formulacion']],
                                                    ['label' => 'Entregado', 'icon' => 'bi-box-arrow-in-down', 'color' => 'dark', 'value' => $provinciaData['entregado']],
                                                    ['label' => 'En ejecución', 'icon' => 'bi-hourglass-split', 'color' => 'warning', 'value' => $provinciaData['en_ejecucion']],
                                                ];

                                                foreach ($estados as $estado): ?>
                                                    <div class="col-md-6 d-flex align-items-center justify-content-between p-3 border rounded bg-light shadow-sm">

                                                        <!-- Icono y texto -->
                                                        <div class="d-flex align-items-center" style="gap: 5px;">
                                                            <i class="bi <?= $estado['icon'] ?> text-<?= $estado['color'] ?> fs-4 me-3"></i>
                                                            <div class="fw-semibold"><?= $estado['label'] ?></div>
                                                        </div>

                                                        <!-- Valor -->
                                                        <div class="text-muted fw-bold fs-7"><b><?= $estado['value'] ?></b></div>

                                                    </div>
                                                <?php endforeach; ?>
                                            </div>

                                            <!-- DETALLE POR MUNICIPIO -->
                                            <?php if (!empty($provinciaData['detalle']) && is_array($provinciaData['detalle'])): ?>
                                                <div class="mt-4">
                                                    <h5 class="fw-bold mb-3">Detalle por Municipio</h5>
                                                    <div class="table-responsive">
                                                        <table class="table table-striped table-bordered table-sm align-middle">
                                                            <thead class="table-dark text-center">
                                                                <tr>
                                                                    <th>Municipio</th>
                                                                    <th>Valor Proyecto</th>
                                                                    <th>Aporte Municipio</th>
                                                                    <th>Aporte Nación</th>
                                                                    <th>Aporte Departamento</th>
                                                                    <th>Suspendido</th>
                                                                    <th>Terminado</th>
                                                                    <th>Ejecutado</th>
                                                                    <th>En contratación</th>
                                                                    <th>En formulación</th>
                                                                    <th>Entregado</th>
                                                                    <th>En ejecución</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody>
                                                                <?php foreach ($provinciaData['detalle'] as $detalle): ?>
                                                                    <tr class="d-grid gap-3">
                                                                        <td><?= htmlspecialchars($detalle['municipio']) ?></td>
                                                                        <td class="text-end">$<?= number_format($detalle['valor_proyecto'], 2, ',', '.') ?></td>
                                                                        <td class="text-end">$<?= number_format($detalle['valor_municipio'], 2, ',', '.') ?></td>
                                                                        <td class="text-end">$<?= number_format($detalle['valor_nacion'], 2, ',', '.') ?></td>
                                                                        <td class="text-end">$<?= number_format($detalle['valor_departamento'], 2, ',', '.') ?></td>
                                                                        <td class="text-center"><?= $detalle['suspendido'] ?></td>
                                                                        <td class="text-center"><?= $detalle['terminado'] ?></td>
                                                                        <td class="text-center"><?= $detalle['ejecutado'] ?></td>
                                                                        <td class="text-center"><?= $detalle['en_contratacion'] ?></td>
                                                                        <td class="text-center"><?= $detalle['en_formulacion'] ?></td>
                                                                        <td class="text-center"><?= $detalle['entregado'] ?></td>
                                                                        <td class="text-center"><?= $detalle['en_ejecucion'] ?></td>
                                                                    </tr>
                                                                <?php endforeach; ?>
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                </div>
                                            <?php endif; ?>

                                        </div>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                                <?php if (!empty($dataTotalEjecucionSecretarias)): ?>
                                    <div class="card mt-4" style="display: none">
                                        <div class="card-header">
                                            <h5 class="mb-0">Inversión por Secretaría</h5>
                                        </div>
                                        <div class="card-body">
                                            <div class="row">
                                                <?php foreach ($dataTotalEjecucionSecretarias as $item): ?>
                                                    <div class="col-md-4 mb-3">
                                                        <div class="border p-3 rounded shadow-sm bg-light">
                                                            <strong><?= trim($item['nombre']) ?></strong><br>
                                                            Valor: $<?= number_format($item['valor'], 0, ',', '.') ?>
                                                        </div>
                                                    </div>
                                                <?php endforeach; ?>
                                            </div>
                                        </div>
                                    </div>
                                <?php else: ?>
                                    <p class="mt-4">No hay datos de ejecución disponibles para esta secretaría.</p>
                                <?php endif; ?>

                            </div>
                        </div>
                    </div>


                    <div class="card-body">
                        <div id="modalGeocalizacion" class="modal fade" tabindex="-1" role="dialog"
                            aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
                            <div class="modal-dialog modal-dialog-centered" role="document">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="exampleModalCenterTitle">Geolocalización
                                        </h5>
                                        <button type="button" class="close" data-dismiss="modal"
                                            aria-label="Close"><span aria-hidden="true">&times;</span></button>
                                    </div>
                                    <div class="modal-body">
                                        <div id="map" style="height: 600px; width: 100%;"></div>

                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary"
                                            data-dismiss="modal">Cerrar</button>

                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Google Maps JavaScript API -->
                        <script async defer
                            src="https://maps.googleapis.com/maps/api/js?key=AIzaSyAbqZVEnSpIPF5dKe89pju2rmQaM58wvY0&callback=initMap">
                        </script>
                    </div>
                </div>
            </div>
            <?php include 'admin/include/footer.php'; ?>
        </div>



        <?php include 'admin/include/gerenic_script.php'; ?>
        <!-- Required Js -->
        <script src="assets/js/vendor-all.min.js"></script>
        <script src="assets/js/plugins/bootstrap.min.js"></script>
        <script src="assets/js/pcoded.min.js"></script>
        <script type="text/javascript" src="admin/js/mapa_secretaria.js"></script>
        <script type="text/javascript" src="admin/js/secretarias.js"></script>
        <style>
            /* Tabs estéticas */
            .tabs {
                width: 100%;
            }

            .tab-list {
                display: flex;
                flex-wrap: wrap;
                list-style: none;
                padding: 0;
                margin-bottom: 1rem;
                border-bottom: 2px solid #dee2e6;
                gap: 0.5rem;
            }

            .tab-list .tab {
                padding: 0.6rem 1rem;
                cursor: pointer;
                background-color: #f8f9fa;
                border-radius: 8px 8px 0 0;
                border: 1px solid #dee2e6;
                border-bottom: none;
                transition: background-color 0.3s, color 0.3s;
                font-weight: 500;
                font-size: 0.95rem;
                white-space: nowrap;
            }

            .tab-list .tab.active {
                background-color: #fff;
                color: #212529;
                border-bottom: 2px solid #fff;
                font-weight: 600;
                background-image: linear-gradient(to top, #1abc9c 2px, rgba(255, 255, 255, 0) 2px);
            }

            .tab-content {
                display: none;
                padding: 1rem 0.5rem;
                animation: fadeIn 0.3s ease-in-out;
                background-color: #fff;
            }

            .tab-content.active {
                display: block;
            }

            @keyframes fadeIn {
                from {
                    opacity: 0;
                    transform: translateY(10px);
                }

                to {
                    opacity: 1;
                    transform: translateY(0);
                }
            }

            /* Tablas dentro de la tarjeta */
            .table {
                margin-bottom: 1rem;
            }

            .table thead th {
                background-color: #e9ecef;
                font-weight: bold;
                text-align: center;
            }

            .table td,
            .table th {
                vertical-align: middle;
            }

            /* Responsive */
            @media (max-width: 768px) {
                .tab-list {
                    flex-direction: column;
                    align-items: stretch;
                }

                .tab-list .tab {
                    border-radius: 8px;
                    border-bottom: 1px solid #dee2e6 !important;
                }

                .row .col-md-6 {
                    width: 100%;
                }
            }
        </style>
        <!-- JS para actualizar data-url dinámicamente al cambiar el select -->
        <script>
            // Evento de click en el mapa
            document.addEventListener('click', function(e) {
                const target = e.target.closest('.mapaClick');
                if (target) {
                    const secretaria = document.getElementById('secretariaId').value;
                    let baseUrl = target.getAttribute('data-base-url');
                    let newUrl = '';
                    if (secretaria == UTIL.getItemHacienda()) {
                        // Cambiar la ruta y agregar accion
                        baseUrl = baseUrl.replace('municipios_secretaria_informacion.php',
                            'municipios_secretaria_informacion_hacienda.php');
                        // Obtener el valor de accionHacienda
                        const accion = document.getElementById('accionHacienda') ? document
                            .getElementById('accionHacienda').value : '';
                        const separator = baseUrl.includes('?') ? '&' : '?';
                        newUrl =
                            `${baseUrl}${separator}secretaria=${secretaria}&accion=${encodeURIComponent(accion)}`;
                    } else {
                        const separator = baseUrl.includes('?') ? '&' : '?';
                        newUrl = `${baseUrl}${separator}secretaria=${secretaria}`;
                    }
                    if (newUrl) {
                        location.href = newUrl;
                    }
                }
            });
            $("img").each(function(index, el) {
                $(this).attr("data-bs-toggle", "tooltip");
                $(this).attr("data-bs-placement", "left");
                tooltip = new bootstrap.Tooltip($(this)[0], {})
            });
            document.querySelectorAll('.tab-list .tab').forEach(tab => {
                tab.addEventListener('click', () => {
                    // Quitar active de todos
                    document.querySelectorAll('.tab-list .tab').forEach(t => t
                        .classList.remove(
                            'active'));
                    document.querySelectorAll('.tab-content').forEach(tc => tc
                        .classList.remove(
                            'active'));
                    // Activar el tab y su contenido
                    tab.classList.add('active');
                    document.getElementById(tab.getAttribute('data-tab')).classList
                        .add('active');
                });
            });
        </script>
        <style>
            /* Estilos generales para el cuerpo o el contenedor si lo tienes */
            body {
                font-family: Arial, sans-serif;
                background-color: #f4f7f6;
                /* Un fondo suave */
                margin: 20px;
                /* Un poco de margen para que no esté pegado a los bordes */
            }

            h2 {
                text-align: center;
                color: #333;
                margin-bottom: 20px;
            }

            /* Estilos clave para centrar y estilizar la tabla */
            table {
                width: 80%;
                /* Define un ancho para la tabla */
                max-width: 800px;
                /* Un ancho máximo para pantallas grandes */
                margin-left: auto;
                /* Centra la tabla horizontalmente */
                margin-right: auto;
                /* Centra la tabla horizontalmente */
                border-collapse: collapse;
                box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
                /* Sombra suave */
                border-radius: 8px;
                /* Bordes redondeados para toda la tabla */
                overflow: hidden;
                /* Asegura que los bordes redondeados se apliquen a los contenidos */
                background-color: #ffffff;
                /* Fondo blanco para la tabla */
            }

            th,
            td {
                text-align: left;
                padding: 12px 15px;
                border-bottom: 1px solid #ddd;
            }

            th {
                background-color: #4CAF50;
                color: white;
                font-weight: bold;
                text-transform: uppercase;
            }

            tr:nth-child(even) {
                background-color: #f9f9f9;
            }

            tr:hover {
                background-color: #f1f1f1;
            }

            /* Alineación especial para números */
            td:nth-child(2),
            td:nth-child(3) {
                text-align: right;
            }

            p.no-data {
                /* Estilo para el mensaje de "no hay datos" */
                text-align: center;
                color: #555;
                margin-top: 20px;
            }
        </style>        
</body>

</html>