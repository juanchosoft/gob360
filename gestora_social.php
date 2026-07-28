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
include './admin/classes/Ciudad.php';
include './admin/classes/Estado.php';
require './admin/classes/Departamento.php';
include './admin/db/coloresg.php';
include './admin/classes/Maing.php';
include './admin/classes/Detalle.php';
include './admin/classes/Cuenta.php';
include './admin/classes/Cuentapro.php';
include './admin/classes/Secreinversion.php';
include './admin/classes/Munnovisitados.php';
include './admin/classes/GestoraSocial.php';

// Permisos
$permissions = PagePermissions::crudForCurrentPage();

// Info general
$arr = Maing::getDataMain(null);
$isvalid = $arr['output']['valid'];
$visitas = $arr['output']['visitas'];
$impactada = $arr['output']['impactada'];
$inversion = $arr['output']['inversion'];
$modulo = 'Primera Dama';

$departamento = new Departamento();
$santander = $departamento->getAll(["id" => Util::getIdentificadorDepartamentoPrincipal()]);
$santander = $santander["output"]["response"]["0"];

$code = null;
$mapa = null;

if (isset($_GET['depto_id']) && in_array($_GET['depto_id'], [1, 12, 21])) {
    switch ($_GET['depto_id']) {
        case Util::getIdentificadorDepartamentoPrincipal():
            $code = $santander["codigo_departamento"];
            $mapa = "admin/mapa-santander/mapa_gestora_social.php";
            break;
    }
}

if (!is_null($code)) {
    $arr = Ciudad::getAll(array('codigo_departamento' => $code));
    $finalMunicipios = $arr['output']['response'];
    $arrApoyoDep = Ciudad::getApoyoByCodigoDepartamento(array('codigo_departamento' => $code));
}
?>

<!-- jQuery -->
<script src="https://code.jquery.com/jquery-3.7.1.js"></script>
<!-- Popper.js -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.3/umd/popper.min.js"></script>

<!-- DataTables -->
<script src="https://cdn.datatables.net/2.0.3/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/2.0.3/js/dataTables.bootstrap4.min.js"></script>
<script src="https://cdn.datatables.net/select/2.0.0/js/dataTables.select.min.js"></script>
<script src="https://cdn.datatables.net/select/2.0.0/js/select.bootstrap4.min.js"></script>

<script src="https://code.highcharts.com/highcharts.js"></script>
<script src="https://code.highcharts.com/modules/data.js"></script>
<script src="https://code.highcharts.com/modules/drilldown.js"></script>
<script src="https://code.highcharts.com/modules/exporting.js"></script>
<script src="https://code.highcharts.com/modules/export-data.js"></script>
<script src="https://code.highcharts.com/modules/accessibility.js"></script>

<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

<link href="assets/css/red_valor_social_gob360_final.css" rel="stylesheet">

<body class="gob360-social-value-dashboard">
  <div class="loader-bg">
    <div class="loader-track"><div class="loader-fill"></div></div>
  </div>

  <?php include './admin/include/navbar.php'; ?>
  <?php include './admin/include/header.php'; ?>

  <div class="pcoded-main-container">
    <div class="pcoded-wrapper">
      <div class="pcoded-content">
        <div class="pcoded-inner-content">
          <div class="main-body">
            <div class="page-wrapper">

              <!-- HERO GOB360 -->
              <section class="g360-social-hero" aria-label="Red de Valor Social GOB360">
                <div class="g360-social-hero__grid">

                  <div>
                    <img
                      src="assets/img/gob360l.png"
                      alt="Logo GOB360"
                      class="g360-social-hero__logo"
                    >
                  </div>

                  <div>
                    <div class="g360-social-hero__eyebrow">
                      <i class="feather icon-heart"></i>
                      Gestión social territorial
                    </div>

                    <h1 class="g360-social-hero__title">
                      Red de Valor Social
                    </h1>

                    <p class="g360-social-hero__description">
                      Consulta las visitas realizadas, la población impactada y la
                      distribución territorial de las actividades sociales mediante
                      el mapa y las gráficas provinciales existentes.
                    </p>

                    <div class="g360-social-hero__chips">
                      <span class="g360-chip g360-chip--success">
                        <i class="feather icon-check-circle"></i>
                        Información consolidada
                      </span>

                      <span class="g360-chip">
                        <i class="feather icon-map-pin"></i>
                        Departamento de Santander
                      </span>

                      <span class="g360-chip">
                        <i class="feather icon-bar-chart-2"></i>
                        Análisis por provincias
                      </span>
                    </div>
                  </div>

                  <div class="g360-social-hero__actions">
                    <?php include './admin/include/btn_back.php'; ?>
                  </div>

                </div>
              </section>

              <div class="row">

                <!-- MAPA -->
                <div class="col-lg-6 col-xl-6 mb-4">
                  <div class="card g360-panel-card g360-map-card">
                    <div class="card-header d-flex align-items-center justify-content-between">
                      <h5 class="mb-0"><i class="feather icon-map mr-2"></i>Mapa territorial</h5>
                      <div class="card-header-right">
                        <div class="btn-group card-option">
                          <button type="button" class="btn dropdown-toggle btn-icon" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <i class="feather icon-more-horizontal"></i>
                          </button>
                          <ul class="list-unstyled card-option dropdown-menu dropdown-menu-right">
                            <li class="dropdown-item full-card"><a href="#!"><span><i class="feather icon-maximize"></i> Maximizar</span><span style="display:none"><i class="feather icon-minimize"></i> Restaurar</span></a></li>
                            <li class="dropdown-item minimize-card"><a href="#!"><span><i class="feather icon-minus"></i> Colapsar</span><span style="display:none"><i class="feather icon-plus"></i> Expandir</span></a></li>
                            <li class="dropdown-item reload-card"><a href="#!"><i class="feather icon-refresh-cw"></i> Recargar</a></li>
                            <li class="dropdown-item close-card"><a href="#!"><i class="feather icon-trash"></i> Eliminar</a></li>
                          </ul>
                        </div>
                      </div>
                    </div>

                    <div class="card-body">
                      <div class="cuerpoMapa">
                        <?php if (!is_null($mapa)) : ?>
                          <div class="santander munis">
                            <?php echo require_once "admin/mapa-santander/mapa_gestora_social.php"; ?>
                          </div>
                        <?php endif ?>
                      </div>
                    </div>
                  </div>
                </div>

                <!-- KPI + TABS -->
                <div class="col-lg-6 col-xl-6 mb-4">

                  <!-- KPI -->
                  <div class="card mb-4 g360-panel-card g360-kpi-card">
                    <div class="card-body">
                      <div class="kpi-grid">
                        <div class="kpi">
                          <div class="ico"><i class="feather icon-map-pin"></i></div>
                          <div>
                            <div class="lbl">Total visitas departamento</div>
                            <div class="val"><?php echo $visitas; ?></div>
                            <div class="sub">Red de Valor Social 1</div>
                          </div>
                        </div>

                        <div class="kpi">
                          <div class="ico"><i class="feather icon-users"></i></div>
                          <div>
                            <div class="lbl">Total población impactada</div>
                            <div class="val"><?php echo $impactada; ?></div>
                            <div class="sub">Conteo general del territorio</div>
                          </div>
                        </div>

                      </div>
                    </div>
                  </div>

                  <!-- Tabs + charts -->
                  <div class="card g360-panel-card g360-tabs-card">
                    <div class="card-body">

                      <ul class="nav nav-tabs mb-3" id="myTab" role="tablist">
                        <li class="nav-item"><a class="nav-link active text-uppercase" id="Soto_Norte-tab" data-toggle="tab" href="#Soto_Norte" role="tab">Soto Norte</a></li>
                        <li class="nav-item"><a class="nav-link text-uppercase" id="Guanenta-tab" data-toggle="tab" href="#Guanenta" role="tab">Guanentá</a></li>
                        <li class="nav-item"><a class="nav-link text-uppercase" id="Garcia_Rovira-tab" data-toggle="tab" href="#Garcia_Rovira" role="tab">García Rovira</a></li>
                        <li class="nav-item"><a class="nav-link text-uppercase" id="Comunera-tab" data-toggle="tab" href="#Comunera" role="tab">Comunera</a></li>
                        <li class="nav-item"><a class="nav-link text-uppercase" id="Velez-tab" data-toggle="tab" href="#Velez" role="tab">Velez</a></li>
                        <li class="nav-item"><a class="nav-link text-uppercase" id="Metropolitana-tab" data-toggle="tab" href="#Metropolitana" role="tab">Metropolitana</a></li>
                        <li class="nav-item"><a class="nav-link text-uppercase" id="Yariguíes-tab" data-toggle="tab" href="#Yariguíes" role="tab">Yariguíes</a></li>
                      </ul>

                      <div class="tab-content" id="myTabContent">
                        <div class="tab-pane fade show active" id="Soto_Norte" role="tabpanel">
                          <div class="card-header"><h5 class="mb-0">Total Población Impactada Soto Norte</h5></div>
                          <div class="card-body"><div class="chart-wrap"><div id="bar-chart-Soto_Norte"></div></div></div>
                        </div>

                        <div class="tab-pane fade" id="Guanenta" role="tabpanel">
                          <div class="card-header"><h5 class="mb-0">Total Población Impactada Guanentá</h5></div>
                          <div class="card-body"><div class="chart-wrap"><div id="bar-chart-Guanenta"></div></div></div>
                        </div>

                        <div class="tab-pane fade" id="Garcia_Rovira" role="tabpanel">
                          <div class="card-header"><h5 class="mb-0">Total Población Impactada García Rovira</h5></div>
                          <div class="card-body"><div class="chart-wrap"><div id="bar-chart-Garcia_Rovira"></div></div></div>
                        </div>

                        <div class="tab-pane fade" id="Comunera" role="tabpanel">
                          <div class="card-header"><h5 class="mb-0">Total Población Impactada Comunera</h5></div>
                          <div class="card-body"><div class="chart-wrap"><div id="bar-chart-Comunera"></div></div></div>
                        </div>

                        <div class="tab-pane fade" id="Velez" role="tabpanel">
                          <div class="card-header"><h5 class="mb-0">Total Población Impactada Velez</h5></div>
                          <div class="card-body"><div class="chart-wrap"><div id="bar-chart-Velez"></div></div></div>
                        </div>

                        <div class="tab-pane fade" id="Metropolitana" role="tabpanel">
                          <div class="card-header"><h5 class="mb-0">Total Población Impactada Metropolitana</h5></div>
                          <div class="card-body"><div class="chart-wrap"><div id="bar-chart-Metropolitana"></div></div></div>
                        </div>

                        <div class="tab-pane fade" id="Yariguíes" role="tabpanel">
                          <div class="card-header"><h5 class="mb-0">Total Población Impactada Yariguíes</h5></div>
                          <div class="card-body"><div class="chart-wrap"><div id="bar-chart-Yariguíes"></div></div></div>
                        </div>
                      </div>

                    </div>
                  </div>

                  <!-- Tabla referencia -->
                  <div class="mt-3">
                    <div class="card g360-panel-card g360-reference-card">
                      <h5 class="card-header mb-0"><i class="feather icon-info mr-2"></i>Valores de referencia</h5>
                      <div class="card-body table-border-style">
                        <div class="table-responsive">
                          <table class="table table-hover tabla-estilizada mb-0">
                            <thead>
                              <tr>
                                <th scope="col">Desde</th>
                                <th scope="col">Hasta</th>
                                <th scope="col">Color</th>
                              </tr>
                            </thead>
                            <tbody>
                              <tr><td>0</td><td>0</td><td><span class="color-circle" style="background-color:#BDBDBD;"></span></td></tr>
                              <tr><td>1</td><td>49</td><td><span class="color-circle" style="background-color:#e53935;"></span></td></tr>
                              <tr><td>50</td><td>99</td><td><span class="color-circle" style="background-color:#FDD835;"></span></td></tr>
                              <tr><td>100</td><td>149</td><td><span class="color-circle" style="background-color:#1565C0;"></span></td></tr>
                              <tr><td>150</td><td>----</td><td><span class="color-circle" style="background-color:#2E7D32;"></span></td></tr>
                            </tbody>
                          </table>
                        </div>
                      </div>
                    </div>
                  </div>

                </div><!-- col right -->
              </div><!-- row -->

            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <?php include 'admin/include/footer.php'; ?>
  <?php include 'admin/include/gerenic_script.php'; ?>

  <script src="assets/js/vendor-all.min.js"></script>
  <script src="assets/js/plugins/bootstrap.min.js"></script>
  <script src="assets/js/pcoded.min.js"></script>
  <script src="assets/js/plugins/prism.js"></script>
  <script src="assets/js/plugins/apexcharts.min.js"></script>

  <script src="admin/js/gestora_social.js"></script>

</body>
</html>