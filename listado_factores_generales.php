<?php
include './admin/include/head.php';
require './admin/include/generic_classes.php';
include './admin/classes/Colombia.php';
include './admin/classes/Departamento.php';
include './admin/classes/Pilar.php';
include './admin/classes/Secretarias.php';


$codigoTodos = Util::codigoTodos();
$isvalidConsolidado = false;
$listadoPorFactoresPilares = [];
if (isset($_REQUEST['mun'], $_REQUEST['dep'], $_REQUEST['pilar'], $_REQUEST['secretaria']) && !empty(trim($_REQUEST['mun'])) && !empty(trim($_REQUEST['dep'])) && !empty(trim($_REQUEST['pilar'])) && !empty(trim($_REQUEST['secretaria'])) ) {

    $municipio = trim($_REQUEST['mun']);
    $departamento = trim($_REQUEST['dep']);
    $pilar = trim($_REQUEST['pilar']);
    $secretaria = trim($_REQUEST['secretaria']);

    $arr = [
        'codigo_departamento' => Util::getDepartamentoPrincipal(),
        'codigo_municipio' => $municipio,
        'pilar' => $pilar,
        'secretaria' => $secretaria
    ];
    $dataConsolidado = Colombia::consultarConsolidadPilaresFactoreslistadoGeneral($arr);
    $isvalidConsolidado = $dataConsolidado['output']['valid'];
    $listadoPorFactoresPilares = $dataConsolidado['output']['response'];
} else { ?>
<script type='text/javascript'>
    alert('Información enviada no es correcta');
    window.location =
        'listado_factores_generales.php?mun=<?php echo Util::getCodigoMunicipioPrincipal(); ?>&dep=<?php echo Util::getDepartamentoPrincipal(); ?>&pilar=<?php echo Util::getIdentificadorPilarPrincipal(); ?>';
</script>
<?php
}


// Información de Departamentos
$arrDep = Departamento::getAll(null);
$isvalid = $arrDep['output']['valid'];
$arrDep = $arrDep['output']['response'];
$optionDep = Util::getDepartamentoPrincipal();
foreach ($arrDep as $val) {
    $optionDep .= "<option " . ($val["codigo_departamento"] == Util::getDepartamentoPrincipal() ? "selected" : "") . " value='" . $val['codigo_departamento'] . "'>" . $val['codigo_departamento'] . " - " . $val['departamento'] . "</option>";
}

// Obtener listado de secretarías
$responseSecretarias = Secretarias::getAll(null);
if (!empty($responseSecretarias['output']['valid'])) {
    $arrSecretarias = $responseSecretarias['output']['response'];

    $optionSecretarias = "<option value='$codigoTodos'" . ($pilar == $codigoTodos ? " selected" : "") . ">Todos</option>";

    $optionSecretarias .= array_reduce($arrSecretarias, function ($carry, $val) use ($pilar) {
        $selected = ($val['id'] == $pilar) ? ' selected' : '';
        return $carry . "<option value='{$val['id']}'{$selected}>{$val['secretaria']}</option>";
    }, '');
} else {
    $optionSecretarias = "<option value='$codigoTodos '" . ($pilar == $codigoTodos ? " selected" : "") . ">Todos</option>";
}

// Información de Pilares
$response = Pilar::getAll(null);
if (!empty($response['output']['valid'])) {
    $arrPilar = $response['output']['response'];

    $optionPilar = "<option value='$codigoTodos'" . ($pilar == $codigoTodos ? " selected" : "") . ">Todos</option>";

    $optionPilar .= array_reduce($arrPilar, function ($carry, $val) use ($pilar) {
        $selected = ($val['id'] == $pilar) ? ' selected' : '';
        return $carry . "<option value='{$val['id']}'{$selected}>{$val['nombre']}</option>";
    }, '');
} else {
    $optionPilar = "<option value='$codigoTodos '" . ($pilar == $codigoTodos ? " selected" : "") . ">Todos</option>";
}
?>
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">

<style>
:root {
  --bg0: #070A12;
  --bg1: #0B1222;
  --card: rgba(255,255,255,.06);
  --stroke: rgba(255,255,255,.10);
  --stroke2: rgba(255,255,255,.14);
  --txt: rgba(255,255,255,.92);
  --muted: rgba(255,255,255,.66);
  --muted2: rgba(255,255,255,.50);
  --good: #18ff6d;
  --warn: #ffd166;
  --bad: #ff5b7a;
  --info: #56ccff;
  --brand: #4f7cff;
  --brand2: #9b5cff;
  --shadow: 0 20px 60px rgba(0,0,0,.35);
}

body.dashboard-body {
  background:
    radial-gradient(900px 420px at 10% 10%, rgba(79,124,255,.28), transparent 60%),
    radial-gradient(900px 420px at 80% 20%, rgba(155,92,255,.22), transparent 60%),
    radial-gradient(900px 520px at 50% 100%, rgba(24,255,109,.10), transparent 60%),
    linear-gradient(180deg, var(--bg0), var(--bg1));
  color: var(--txt);
  overflow-x: hidden;
}
.pcoded-main-container { background: transparent !important; }
.pcoded-content { padding-bottom: 2rem; }

.kpi-card {
  border: 1px solid var(--stroke);
  background: rgba(255,255,255,.06);
  border-radius: 18px;
  padding: 14px;
  height: 100%;
  box-shadow: 0 14px 40px rgba(0,0,0,.25);
  position: relative;
  overflow: hidden;
  backdrop-filter: blur(10px);
}
.kpi-card:before {
  content: "";
  position: absolute; inset: -2px;
  background:
    radial-gradient(260px 140px at 10% 0%, rgba(79,124,255,.18), transparent 60%),
    radial-gradient(260px 140px at 100% 20%, rgba(155,92,255,.12), transparent 60%);
  opacity: .85;
  pointer-events: none;
}
.kpi-card > * { position: relative; z-index: 1; }
.kpi-card.feature {
  background: linear-gradient(135deg, rgba(79,124,255,.18), rgba(155,92,255,.10));
  border-color: rgba(79,124,255,.25);
}

.form-wow select, .form-wow .form-control {
  background: rgba(255,255,255,.06);
  border: 1px solid var(--stroke);
  border-radius: 14px;
  padding: 10px 14px;
  color: #fff;
  font-weight: 700;
  font-size: 13px;
  backdrop-filter: blur(10px);
  box-shadow: 0 10px 24px rgba(0,0,0,.20);
  transition: border-color .15s ease, box-shadow .15s ease;
}
.form-wow select:focus, .form-wow .form-control:focus {
  border-color: var(--brand);
  box-shadow: 0 0 0 4px rgba(79,124,255,.18);
  outline: none;
}
.form-wow select option { background: #1e293b; color: #fff; }
.form-wow label {
  color: var(--muted);
  font-weight: 800;
  font-size: 11px;
  text-transform: uppercase;
  letter-spacing: .3px;
  margin-bottom: 6px;
  display: block;
}

.btn-wow {
  border: 1px solid var(--stroke2);
  background: rgba(255,255,255,.06);
  color: var(--txt);
  border-radius: 12px;
  padding: .6rem .85rem;
  font-weight: 900;
  transition: .2s ease;
  box-shadow: 0 10px 24px rgba(0,0,0,.25);
  display: inline-flex;
  align-items: center;
  gap: .5rem;
}
.btn-wow:hover { transform: translateY(-1px); background: rgba(255,255,255,.10); color: var(--txt); }
.btn-wow.good {
  background: linear-gradient(135deg, rgba(24,255,109,.18), rgba(86,204,255,.10));
  border-color: rgba(24,255,109,.35);
}

.breadcrumb .breadcrumb-item a{ color: var(--muted) !important; }
.breadcrumb .breadcrumb-item.active{ color: var(--txt) !important; }
.page-header h5 { color: #ffffff !important; }

.table-wow {
  background: transparent !important;
  border-radius: 14px;
  overflow: hidden;
}
.table-wow table { margin: 0; font-size: 12.5px; }
.table-wow thead th {
  background: rgba(255,255,255,.08) !important;
  color: #ffffff !important;
  font-weight:900 !important;
  font-size:11.5px;
  letter-spacing:.3px;
  text-transform: uppercase;
  padding:11px 10px !important;
  border:0 !important;
}
.table-wow tbody tr {
  transition: background .12s ease;
  border-bottom: 1px solid var(--stroke);
  background: transparent !important;
}
#dynamicTable tbody tr,
#dynamicTable tbody tr:nth-child(even),
#dynamicTable tbody tr:nth-child(odd) {
  background: transparent !important;
}
.table-wow tbody tr:hover{ background: rgba(255,255,255,.04) !important; }
#dynamicTable tbody tr:hover{ background: rgba(255,255,255,.04) !important; }
.table-wow td {
  color: #ffffff !important;
  font-weight:600;
  font-size:12.5px;
  padding:11px 10px !important;
  border:0 !important;
  vertical-align: middle;
}
#dynamicTable td {
  color: #ffffff !important;
  background: transparent !important;
}
#dynamicTable {
  color: #ffffff;
}

.table-wow td img[alt="Icono"] {
  width:32px; height:32px;
  background: rgba(255,255,255,.10);
  border-radius:10px;
  padding:4px;
  object-fit: contain;
}
.table-wow td img:not([alt]) {
  width:32px; height:32px;
  background: rgba(255,255,255,.10);
  border-radius:10px;
  padding:4px;
  object-fit: contain;
}

.geo-btn {
  background: rgba(255,255,255,.06);
  border: 1px solid var(--stroke);
  border-radius: 12px;
  padding: 6px 12px;
  transition: .15s ease;
  cursor: pointer;
  display: inline-flex;
  align-items: center;
}
.geo-btn:hover { transform: translateY(-1px); box-shadow: 0 6px 14px rgba(0,0,0,.25); }

.badge-cant {
  font-size: 13px; font-weight: bold; color: #d1fae5;
  background: rgba(24,255,109,.15); padding: 6px 14px;
  border-radius: 12px;
  border: 1px solid rgba(24,255,109,.25);
  display: inline-block; min-width: 60px;
}

.fade-in{ animation: fadeIn .35s ease-in-out; }
@keyframes fadeIn{ from{ opacity: 0; transform: translateY(10px); } to{ opacity: 1; transform: translateY(0); } }
</style>

<body class="dashboard-body">
    <div class="loader-bg">
        <div class="loader-track">
            <div class="loader-fill"></div>
        </div>
    </div>
    <?php include './admin/include/navbar.php'; ?>
    <?php include './admin/include/header.php'; ?>

    <div class="pcoded-main-container">
        <div class="pcoded-content">
            <div class="page-header">
                <div class="page-block">
                    <div class="row align-items-center">
                        <div class="col-md-12">
                            <div class="d-flex justify-content-between align-items-center">
                                <h5 class="m-b-10">Listado de Factores Generales</h5>
                                <?php include './admin/include/btn_back.php'; ?>
                            </div>
                            <ul class="breadcrumb">
                                <li class="breadcrumb-item"><a href="index.html"><i class="feather icon-home"></i></a></li>
                                <li class="breadcrumb-item"><a href="#!">Acción Unificada</a></li>
                                <li class="breadcrumb-item"><a href="#!">Listado de Factores Generales</a></li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-12">
                    <div class="kpi-card feature fade-in">
                        <div class="form-wow">
                            <div class="row g-3">
                                <div class="col-md-3">
                                    <label>Departamento</label>
                                    <select onchange="DEPARTAMENTO.getMunicipios()" class="form-control" id="tbl_departamento_id" name="tbl_departamento_id">
                                        <?php echo $optionDep; ?>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label>Municipio</label>
                                    <select onchange="MUNICIPIO.updateUrlMunicipio(this)" class="form-control" id="tbl_municipio_id" name="tbl_municipio_id"></select>
                                </div>
                                <div class="col-md-3">
                                    <label>Pilar</label>
                                    <select class="form-control" id="pilarId" name="pilarId" onchange="MUNICIPIO.updateUrlPilar(this, false)">
                                        <?php echo $optionPilar; ?>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label>Secretaría</label>
                                    <select class="form-control" id="secretariaId" name="secretariaId" onchange="MUNICIPIO.updateUrlSecretaria(this, false)">
                                        <?php echo $optionSecretarias; ?>
                                    </select>
                                </div>
                            </div>
                            <div class="mt-3 d-flex justify-content-end">
                                <a href="listado_factores_generales_excel.php?codigo_departamento=<?= htmlspecialchars($departamento) ?>&codigo_municipio=<?= htmlspecialchars($municipio) ?>&pilar=<?= htmlspecialchars($pilar) ?>&secretaria=<?= htmlspecialchars($secretaria) ?>" class="btn-wow good">
                                    <i class="bi bi-file-earmark-excel"></i> Descargar Excel
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row mt-3">
                <div class="col-12">
                    <div class="kpi-card fade-in">
                        <div class="d-flex align-items-center gap-2 mb-3">
                            <i class="bi bi-list-check" style="font-size: 1.2rem; color: var(--muted);"></i>
                            <h6 style="margin:0; font-weight: 950; letter-spacing: .2px; color: var(--txt);">Factores del Municipio</h6>
                        </div>
                        <div id="divConsolidado">
                            <div class="table-wow">
                                <div class="table-responsive">
                                    <table id="dynamicTable" class="table table-hover align-middle text-center" style="width: 100%">
                                        <thead>
                                            <tr>
                                                <th>Icono</th>
                                                <th>Factor</th>
                                                <th>Cantidad</th>
                                                <th>Unidad de medida</th>
                                                <th>Geocalización</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php if (!empty($listadoPorFactoresPilares) && $listadoPorFactoresPilares[0]['response']['code'] != 104): ?>
                                            <?php foreach ($listadoPorFactoresPilares as $data): ?>
                                            <tr>
                                                <td><img src="<?= htmlspecialchars($data['icono']) ?>" alt="Icono" width="32px" onerror="this.src='assets/iconos/gobierno.png';this.onerror=null;"></td>
                                                <td class="text-start fw-semibold" style="word-break: break-word; white-space: normal;">
                                                    <?= htmlspecialchars($data['factor']) ?>
                                                </td>
                                                <td>
                                                    <span class="badge-cant">
                                                        <?= htmlspecialchars($data['total_cantidad']) ?>
                                                    </span>
                                                </td>
                                                <td><span style="font-size:13px;"><?= htmlspecialchars($data['tipo_medicion']) ?></span></td>
                                                <td>
                                                    <span class="geo-btn" data-toggle="modal" data-target="#modalGeocalizacion" onclick="mostrarInformacionPilarByMunicipio('<?= $data['tbl_factor_id'] ?>')">
                                                        <img src="assets/iconos/geo.png" alt="Geo" width="22px">
                                                    </span>
                                                </td>
                                            </tr>
                                            <?php endforeach; ?>
                                            <?php else: ?>
                                            <tr><td colspan="5" class="text-center py-4" style="color:var(--muted);">No hay datos disponibles.</td></tr>
                                            <?php endif; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card-body">
                <div id="modalGeocalizacion" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="modalGeocalizacionTitle" aria-hidden="true">
                    <div class="modal-dialog modal-xl centered" role="document">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="modalGeocalizacionTitle">Geolocalización : <span id="nombrePilar"></span></h5>
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
            </div>

            <?php include 'admin/include/gerenic_script.php'; ?>
            <script src="assets/js/vendor-all.min.js"></script>
            <script src="assets/js/plugins/bootstrap.min.js"></script>
            <script src="assets/js/pcoded.min.js"></script>
            <script type="text/javascript" src="admin/js/departamento.js"></script>
            <script type="text/javascript" src="admin/js/municipios.js"></script>
            <script>
                MUNICIPIO.init();
            </script>
            <script async defer src="https://maps.googleapis.com/maps/api/js?key=AIzaSyAbqZVEnSpIPF5dKe89pju2rmQaM58wvY0&callback=initMap"></script>
            <script type="text/javascript" src="admin/js/mapa_municipio_geo.js"></script>
            <script>
                setTimeout(() => { initMap(); }, 3000);
            </script>
        </div>
    </div>
</body>
</html>
