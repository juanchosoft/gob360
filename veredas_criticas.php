<?php
include './admin/include/head.php';
include './admin/classes/Departamento.php';
include './admin/classes/Vereda.php';
require './admin/include/generic_classes.php';
require './admin/classes/FactoresInestabilidadGobernacion.php';

$inestabilidadId = isset($_REQUEST['inestabilidad']) ? intval($_REQUEST['inestabilidad']) : 10000;

$responseInest = FactoresInestabilidadGobernacion::getAll(null);
$optionInest = "<option value='10000'" . ($inestabilidadId == 10000 ? " selected" : "") . ">Todos</option>";
if (!empty($responseInest['output']['valid'])) {
    foreach ($responseInest['output']['response'] as $val) {
        $selected = ($val['id'] == $inestabilidadId) ? ' selected' : '';
        $optionInest .= "<option value='{$val['id']}'{$selected}>" . htmlspecialchars($val['nombre_categoria'], ENT_QUOTES, 'UTF-8') . "</option>";
    }
}

// Obtén todos los departamentos
$arrDep = Departamento::getAll(null);
$isvalid = $arrDep['output']['valid'];
$arrDep = $arrDep['output']['response'];
$optionDep = Util::getDepartamentoPrincipal();

foreach ($arrDep as $val) {
    $optionDep .= "<option " . ($val["codigo_departamento"] == Util::getDepartamentoPrincipal() ? "selected" : "") . " value='" . $val['codigo_departamento'] . "'>" . $val['codigo_departamento'] . " - " . $val['departamento'] . "</option>";
}

$optionMunicipio = $optionDep;

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

.breadcrumb .breadcrumb-item a{ color: var(--muted) !important; }
.breadcrumb .breadcrumb-item.active{ color: var(--txt) !important; }

.page-header h5, .page-header .m-b-10 { color: #ffffff !important; }

.card {
  border: 1px solid var(--stroke);
  background: rgba(255,255,255,.06);
  border-radius: 18px;
  box-shadow: var(--shadow);
  backdrop-filter: blur(10px);
}
.card-header {
  background: transparent !important;
  border-bottom: 1px solid var(--stroke);
}
.card-header h5 {
  color: #ffffff !important;
  font-weight: 900;
}
.card-body {
  background: transparent;
}

.form-control, select.form-control {
  background: #0B1222 !important;
  border: 1px solid var(--stroke);
  border-radius: 14px;
  padding: 10px 14px;
  color: #fff !important;
  font-weight: 700;
  font-size: 13px;
  transition: border-color .15s ease, box-shadow .15s ease;
}
.form-control:focus, select.form-control:focus {
  border-color: var(--brand);
  box-shadow: 0 0 0 4px rgba(79,124,255,.18);
  outline: none;
  color: #fff !important;
  background: #0B1222 !important;
}
select.form-control option { background: #1e293b; color: #fff; }
.form-control::placeholder { color: var(--muted2); }
label {
  color: var(--muted);
  font-weight: 800;
  font-size: 11px;
  text-transform: uppercase;
  letter-spacing: .3px;
  margin-bottom: 6px;
  display: block;
}

.btn-success {
  border: 1px solid var(--stroke2);
  background: linear-gradient(135deg, rgba(24,255,109,.18), rgba(86,204,255,.10));
  color: var(--txt);
  border-radius: 12px;
  padding: .6rem .85rem;
  font-weight: 900;
  transition: .2s ease;
  box-shadow: 0 10px 24px rgba(0,0,0,.25);
  border-color: rgba(24,255,109,.35);
}
.btn-success:hover {
  transform: translateY(-1px);
  color: var(--txt);
}

.tablecriticas {
  width: 100%;
  border-collapse: collapse;
}
.tablecriticas thead th {
  background: rgba(255,255,255,.08) !important;
  color: #ffffff !important;
  font-weight: 900 !important;
  font-size: 11.5px;
  letter-spacing: .3px;
  text-transform: uppercase;
  padding: 11px 10px !important;
  border: 0 !important;
}
.tablecriticas tbody tr {
  transition: background .12s ease;
  border-bottom: 1px solid var(--stroke);
  background: transparent !important;
}
#tablaVeredas tbody tr,
#tablaVeredas tbody tr:nth-child(even),
#tablaVeredas tbody tr:nth-child(odd) {
  background: transparent !important;
}
.tablecriticas tbody tr:hover { background: rgba(255,255,255,.04) !important; }
#tablaVeredas tbody tr:hover { background: rgba(255,255,255,.04) !important; }
.tablecriticas td {
  color: #ffffff !important;
  font-weight: 600;
  font-size: 12.5px;
  padding: 11px 10px !important;
  border: 0 !important;
  vertical-align: middle;
}
#tablaVeredas td { color: #ffffff !important; }
#tablaVeredas thead th {
  background: rgba(0,0,0,.40) !important;
  color: #ffffff !important;
}
#tablaVeredas,
#modalVeredaDetalles .tablecriticas {
  background: transparent !important;
}
#modalVeredaDetalles .tablecriticas tbody tr,
#modalVeredaDetalles .tablecriticas tbody tr:nth-child(even),
#modalVeredaDetalles .tablecriticas tbody tr:nth-child(odd) {
  background: transparent !important;
}
#modalVeredaDetalles .tablecriticas td {
  color: #ffffff !important;
  background: transparent !important;
}

.table-responsive {
  width: 100%;
  overflow-x: auto;
}

.modal-content {
  background: rgba(11,18,34,.98);
  border: 1px solid var(--stroke);
  border-radius: 18px;
}
.modal-header {
  border-bottom: 1px solid var(--stroke);
  background: transparent;
}
.modal-header .modal-title { color: #ffffff !important; font-weight: 900; }
.modal-header .close { color: #ffffff !important; }
.modal-body { color: var(--txt); }
#modalVeredaDetalles .modal-dialog {
  max-width: 900px;
}
#modalVeredaDetalles .tablecriticas thead th {
  background: rgba(0,0,0,.40) !important;
  color: #ffffff !important;
}

.puntaje-badge {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  font-size: 13px;
  font-weight: 800;
  color: #fff !important;
}
.color-dot {
  width: 12px;
  height: 12px;
  border-radius: 50%;
  border: 1px solid rgba(255,255,255,.35);
  flex-shrink: 0;
}
.vereda-link {
  color: #56ccff !important;
  font-weight: 700;
  text-decoration: none;
}
.vereda-link:hover {
  color: #9bdcff !important;
  text-decoration: underline;
}
.filtro-ayuda {
  font-size: 13px;
  color: var(--muted);
  text-align: center;
  margin: 0 0 14px;
}
.filtro-ayuda .badge-rango {
  display: inline-block;
  margin: 0 4px;
  padding: 4px 10px;
  border-radius: 999px;
  font-weight: 800;
  font-size: 11px;
}
</style>

<body class="dashboard-body">
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
    <!-- [ Main Content ] start -->
    <div class="pcoded-main-container">
        <div class="pcoded-content">
            <!-- [ breadcrumb ] start -->
            <div class="page-header">
                <div class="page-block">
                    <div class="row align-items-center">
                        <div class="col-md-12">
                            <div class="d-flex justify-content-between align-items-center">
                                <h5 class="m-b-10">Veredas Críticas por Factor de Inestabilidad</h5>
<?php include './admin/include/btn_back.php'; ?>
                            </div>
                            <ul class="breadcrumb">
                                <li class="breadcrumb-item"><a href="index.html"></i><i class="feather icon-home"></i></a>
                                </li>
                                <li class="breadcrumb-item"><a href="#!">Veredas Críticas</a></li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
            <!-- [ breadcrumb ] end -->
            <!-- [ Main Content ] start -->
            <div class="row">
                <!-- [ sample-page ] start -->
                <div class="col-sm-12">
                    <div class="card">

                        <div class="card-header">
                            <div class="card-header-right">
                                <div class="btn-group card-option">
                                    <button type="button" class="btn dropdown-toggle btn-icon" data-toggle="dropdown"
                                        aria-haspopup="true" aria-expanded="false">
                                        <i class="feather icon-more-horizontal"></i>
                                    </button>
                                    <ul class="list-unstyled card-option dropdown-menu dropdown-menu-right">
                                        <li class="dropdown-item full-card"><a href="#!"><span><i
                                                        class="feather icon-maximize"></i> maximize</span><span
                                                    style="display:none"><i class="feather icon-minimize"></i>
                                                    Restore</span></a></li>
                                        <li class="dropdown-item minimize-card"><a href="#!"><span><i
                                                        class="feather icon-minus"></i> collapse</span><span
                                                    style="display:none"><i class="feather icon-plus"></i>
                                                    expand</span></a></li>
                                        <li class="dropdown-item reload-card"><a href="#!"><i
                                                    class="feather icon-refresh-cw"></i> reload</a></li>
                                        <li class="dropdown-item close-card"><a href="#!"><i
                                                    class="feather icon-trash"></i> remove</a></li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                        <div class="card-body">
                            <!-- ================================== INICIO CONTENIDO================================================ -->
                            <div class="col-sm-12">

                                <div class="card-header">
                                    <h5 style="font-size:20px">
                                        <i data-feather="map-pin" style="margin-right: 10px; color:red"></i> <!-- Ícono de Font Awesome -->
                                        Seleccione El Municipio de su interés
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <form>
                                        <div id="formFiltroVeredas" class="row">
                                            <select onchange="DEPARTAMENTO.getMunicipios();" class="form-control d-none" id="tbl_departamento_id" name="departamento">
                                                <?php echo $optionDep; ?>
                                            </select>
                                            <div class="col-12 d-flex justify-content-between flex-wrap">
                                                <div class="form-group col-md-6">
                                                    <label for="inestabilidadId" style="display: block; text-align: center; width: 100%;">Factor Inestabilidad
                                                        <span class="text-danger mb-1">*</span>
                                                    </label>
                                                    <select class="form-control" id="inestabilidadId" name="inestabilidad" onchange="updateUrlInestabilidad(this)" style="text-align: center; text-align-last: center;">
                                                        <?php echo $optionInest; ?>
                                                    </select>
                                                </div>

                                                <div class="form-group col-md-6">
                                                    <label style="display: block; text-align: center; width: 100%;" for="tbl_municipio_id">
                                                        Municipio<span class="text-danger mb-1">*</span>
                                                    </label>
                                                    <select class="form-control" id="tbl_municipio_id" name="municipio" style="text-align: center; text-align-last: center;">
                                                    </select>
                                                </div>
                                            </div>
                                            <p class="filtro-ayuda">
                                                Se listan automáticamente las veredas con <strong>puntaje inicial</strong> en:
                                                <span class="badge-rango" style="background:#F6C026;color:#111827;">Medio</span>
                                                <span class="badge-rango" style="background:#FB8C00;color:#fff;">Alto</span>
                                                <span class="badge-rango" style="background:#E53935;color:#fff;">Crítico</span>
                                            </p>
                                        </div>
                                        <button id="btnSeleccionar" type="button" class="btn btn-success" style="display: block; margin: 0 auto; text-align: center;">
                                            <i class="feather mr-2 icon-check-circle"></i>Buscar
                                        </button>
                                    </form>
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="card">
                                    <div class="card-header">
                                        <h5>
                                            <i data-feather="triangle" style="margin-right: 10px; color:red"></i>
                                            Resultados de Veredas Críticas
                                        </h5>

                                    </div>
                                    <div class="card-body table-border-style">
                                        <div class="table-responsive">
                                            <table class="table table-hover tablecriticas" id="tablaVeredas">
                                                <thead>
                                                    <tr>
                                                        <th style="text-align:left">Vereda</th>
                                                        <th style="text-align:center">Municipio</th>
                                                        <th style="text-align:center">Puntaje Inicial</th>
                                                        <th style="text-align:center">Puntaje Actual</th>
                                                        <th style="text-align:center">Ver</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <tr id="mensajeInicial">
                                                        <td style="font-size:15px;color:var(--muted2) !important" colspan="5" class="text-center">
                                                            Seleccione el factor de inestabilidad y el municipio para listar las veredas con puntaje inicial en Medio, Alto o Crítico.
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
                <!-- ================================== FIN CONTENIDO================================================ -->
            </div>
        </div>
    </div>
    <!-- [ sample-page ] end -->
    </div>
    <!-- [ Main Content ] end -->
    </div>

    </div>
    <!-- [ Main Content ] end -->
    <!-- Required Js -->
    <?php include 'admin/include/gerenic_script.php'; ?>
    <script src="assets/js/vendor-all.min.js"></script>
    <script src="assets/js/plugins/bootstrap.min.js"></script>
    <script src="assets/js/pcoded.min.js"></script>
    <script src="admin/js/veredas_criticas.js"></script>
    <script type="text/javascript" src="admin/js/departamento.js"></script>
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        function updateUrlInestabilidad(item) {
            var params = new URLSearchParams(window.location.search);
            params.set('inestabilidad', item.value);
            window.location.search = params.toString();
        }

        setTimeout(function() {
            DEPARTAMENTO.getMunicipios();
        }, 1000);
    </script>

</body>

</html>