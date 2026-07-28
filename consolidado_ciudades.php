<?php

include './admin/include/head.php';

require './admin/include/generic_classes.php';

include './admin/classes/Colombia.php';
include './admin/classes/Ciudad.php';
include './admin/classes/Estado.php';
require './admin/classes/Departamento.php';
require './admin/classes/Maing.php';
require './admin/classes/Pilar.php';
require './admin/classes/Mapa.php';
include './admin/db/coloress.php';

$userType = SessionData::getUserType();
if ($userType === Util::Alcalde() || $userType === Util::Auxiliar_Alcalde()) {
    header('Location: dashboard.php');
    exit;
}

$municipioUsuarioLogueado = SessionData::getCodigoMunicipio();
$isAdmin = ($userType === Util::Administrador() || $userType === Util::SuperAdministrador());
$isSecretario = ($userType === Util::Secretario_Despacho() || $userType === Util::Auxiliar()|| $userType == Util::Auxiliar_secret_gob());
?>
<style>
  :root{
    --au-bg-0:#070A12;
    --au-bg-1:#0B1020;
    --au-card: rgba(255,255,255,.06);
    --au-card-2: rgba(255,255,255,.085);
    --au-stroke: rgba(255,255,255,.10);
    --au-text: rgba(255,255,255,.92);
    --au-muted: rgba(255,255,255,.68);
    --au-primary:#2E6BFF;
    --au-primary-2:#19D3FF;
    --au-success:#18ff6d;
    --au-shadow: 0 18px 55px rgba(0,0,0,.55);
    --au-radius: 18px;
    --au-radius-lg: 22px;
  }
  body{
    background:
      radial-gradient(1200px 900px at 12% 8%, rgba(46,107,255,.28), transparent 55%),
      radial-gradient(900px 700px at 88% 18%, rgba(25,211,255,.20), transparent 50%),
      radial-gradient(700px 600px at 65% 86%, rgba(24,255,109,.10), transparent 55%),
      linear-gradient(180deg, var(--au-bg-0), var(--au-bg-1));
    color: var(--au-text);
  }
  .page-header .page-block{ background: transparent; }
  .page-header h5{
    font-weight: 800;
    letter-spacing: .2px;
    font-size: 1.05rem;
    margin: 0;
  }
  .breadcrumb{
    background: transparent !important;
    border-radius: 999px;
    padding: .4rem .75rem;
    gap: .35rem;
  }
  .breadcrumb-item a{ color: var(--au-muted) !important; }
  .breadcrumb-item.active,
  .breadcrumb-item:last-child a{ color: var(--au-text) !important; }

  .card{
    border: 1px solid var(--au-stroke) !important;
    background: linear-gradient(180deg, var(--au-card), rgba(255,255,255,.035)) !important;
    box-shadow: var(--au-shadow);
    border-radius: var(--au-radius-lg) !important;
    overflow: hidden;
  }
  .card-header{
    border-bottom: 1px solid rgba(255,255,255,.09) !important;
    background: linear-gradient(90deg, rgba(46,107,255,.14), rgba(25,211,255,.10), rgba(255,255,255,.02)) !important;
    padding: 1rem 1.15rem !important;
  }
  .card-header h5{
    font-weight: 850 !important;
    margin: 0 !important;
    display: flex;
    align-items: center;
    gap: .6rem;
    color: #ffffff !important;
    opacity: 1 !important;
    text-shadow: 0 2px 8px rgba(0,0,0,.55);
  }
  .card-header h5::before{
    content:"";
    width: 10px;
    height: 10px;
    border-radius: 999px;
    background: linear-gradient(135deg, var(--au-primary), var(--au-primary-2));
    box-shadow: 0 0 0 6px rgba(46,107,255,.12);
    flex: 0 0 auto;
  }
  .card-body{ padding: 1.15rem !important; }
  .stat-card{
    background: rgba(255,255,255,.07) !important;
    border: 1px solid rgba(255,255,255,.10) !important;
    border-radius: 16px !important;
    padding: 1.25rem 1rem;
    text-align: center;
    transition: transform .15s ease, box-shadow .15s ease;
  }
  .stat-card:hover{
    transform: translateY(-2px);
    box-shadow: 0 12px 30px rgba(0,0,0,.25);
  }
  .stat-card .stat-num{
    font-size: 1.8rem;
    font-weight: 900;
    background: linear-gradient(135deg, #2E6BFF, #19D3FF);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
    line-height: 1.2;
  }
  .stat-card .stat-label{
    font-size: .75rem;
    color: var(--au-muted);
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: .3px;
    margin-top: .25rem;
  }
  .stat-card .stat-detail{
    font-size: .65rem;
    color: rgba(255,255,255,.45);
    margin-top: .15rem;
  }
  .stat-card.gold .stat-num{
    background: linear-gradient(135deg, #FFD700, #FFA500);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
  }
  .stat-card.green .stat-num{
    background: linear-gradient(135deg, #18ff6d, #00c853);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
  }
  .stat-card.pink .stat-num{
    background: linear-gradient(135deg, #ff6b9d, #c44dff);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
  }

  .table-scroll{
    background:#ffffff !important;
    border-radius: 14px;
    box-shadow: 0 6px 20px rgba(0,0,0,.12);
  }
  .table-scroll table{
    margin: 0;
    font-size: 12.5px;
  }
  .table-scroll thead th{
    background:#1f3a56 !important;
    color:#ffffff !important;
    font-weight:900 !important;
    font-size:11.5px;
    letter-spacing:.3px;
    text-transform: uppercase;
    padding:11px 10px !important;
    border:0 !important;
  }
  .table-scroll tbody tr{
    transition: background .12s ease;
    border-bottom: 1px solid #eef0f4;
  }
  .table-scroll tbody tr:hover{ background:#f4f7fb !important; }
  .table-scroll td{
    color:#0f172a !important;
    font-weight:600;
    font-size:12.5px;
    padding:11px 10px !important;
    border:0 !important;
    vertical-align: middle;
  }
  .badge-count{
    background: #eef2ff;
    color: #1e3a8a;
    font-weight: 800;
    font-size: 13px;
    padding: 5px 14px;
    border-radius: 8px;
    display: inline-block;
  }
  .badge-act{
    background: #fef3c7;
    color: #92400e;
    font-weight: 700;
    font-size: 12px;
    padding: 5px 12px;
    border-radius: 8px;
    display: inline-block;
  }
  .total-row td{
    background: #e8f0fe !important;
    font-weight: 900 !important;
    font-size: 13px !important;
    border-top: 2px solid #2E6BFF !important;
  }
  .table-scroll{
    max-height: 520px;
    overflow-y: auto;
    border-radius: 14px;
  }
  .table-scroll thead{ position: sticky; top: 0; z-index: 3; }

  .fade-in{ animation: fadeIn .35s ease-in-out; }
  @keyframes fadeIn{ from{ opacity: 0; transform: translateY(10px); } to{ opacity: 1; transform: translateY(0); } }
</style>

<body class="">
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
                            <div class="page-header">
                                <div class="page-block">
                                    <div class="row align-items-center">
                                        <div class="col-md-12">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <h5 class="m-b-10">Acción Unificada Santander</h5>
                                                <?php include './admin/include/btn_back.php'; ?>
                                            </div>
                                            <ul class="breadcrumb">
                                                <li class="breadcrumb-item"><a href="index.html"><i class="feather icon-home"></i></a></li>
                                                <li class="breadcrumb-item"><a href="#!">Acción Unificada Santander</a></li>
                                                <li class="breadcrumb-item"><a href="#!">Estadísticas BD</a></li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div id="statsContainer" class="fade-in"></div>
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

    <script>
    function number_format(num) {
        return num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
    }

    function renderStats(data) {
        if (!data.valid) {
            return '<div class="text-center text-muted p-5"><i class="bi bi-exclamation-triangle" style="font-size: 2rem;"></i><p class="mt-2">Error al cargar estadísticas</p></div>';
        }

        const totales = data.totales || [];
        const porMunicipio = data.por_municipio || [];
        const resumen = data.resumen || {};

        const tablaTotal = totales.reduce((acc, t) => acc + parseInt(t.total), 0);

        let cardsHtml = '<div class="row g-3 mb-4">';
        totales.forEach(t => {
            let extraClass = '';
            if (t.tabla === 'tbl_ingreso_informacion') extraClass = 'gold';
            else if (t.tabla === 'tbl_ingreso_informacion_x_actualizacion') extraClass = 'green';
            else if (t.tabla === 'tbl_vereda') extraClass = 'pink';
            cardsHtml += `
                <div class="col-6 col-md-3 col-lg-2">
                    <div class="stat-card ${extraClass}">
                        <div class="stat-num">${number_format(t.total)}</div>
                        <div class="stat-label">${t.tabla.replace('tbl_', '').replace(/_/g, ' ')}</div>
                    </div>
                </div>
            `;
        });
        cardsHtml += `
                <div class="col-6 col-md-3 col-lg-2">
                    <div class="stat-card gold">
                        <div class="stat-num">${number_format(tablaTotal)}</div>
                        <div class="stat-label">GRAN TOTAL</div>
                        <div class="stat-detail">${resumen.municipios_con_datos || 0} municipios</div>
                    </div>
                </div>
            </div>
        `;

        let tableRows = '';
        let sumIngresos = 0;
        let sumActualizaciones = 0;
        let sumFactores = 0;

        porMunicipio.forEach(m => {
            const ingresos = parseInt(m.total_ingresos) || 0;
            const actualizaciones = parseInt(m.total_actualizaciones) || 0;
            const factores = parseInt(m.total_factores_distintos) || 0;
            sumIngresos += ingresos;
            sumActualizaciones += actualizaciones;
            sumFactores += factores;

            tableRows += `
                <tr>
                    <td><span class="badge-count">${m.municipio}</span></td>
                    <td><strong>${number_format(ingresos)}</strong></td>
                    <td><span class="badge-act">${number_format(actualizaciones)}</span></td>
                    <td>${number_format(factores)}</td>
                </tr>
            `;
        });

        const municipiosConDatos = porMunicipio.filter(m => parseInt(m.total_ingresos) > 0).length;

        let html = cardsHtml;

        html += `
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h5>Registros por Municipio - Santander</h5>
                        </div>
                        <div class="card-body p-0" style="background:transparent;">
                            <div class="table-scroll">
                                <table class="table table-hover align-middle text-center" style="width:100%; margin:0;">
                                    <thead>
                                        <tr>
                                            <th style="min-width:160px;">Municipio</th>
                                            <th style="min-width:130px;">Ingresos</th>
                                            <th style="min-width:130px;">Actualizaciones</th>
                                            <th style="min-width:120px;">Factores Distintos</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        ${tableRows}
                                        <tr class="total-row">
                                            <td><strong>TOTAL (${municipiosConDatos} municipios)</strong></td>
                                            <td><strong>${number_format(sumIngresos)}</strong></td>
                                            <td><strong>${number_format(sumActualizaciones)}</strong></td>
                                            <td><strong>${number_format(sumFactores)}</strong></td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        `;

        return html;
    }

    function cargarEstadisticas() {
        const container = document.getElementById('statsContainer');
        if (!container) return;

        container.innerHTML = '<div class="text-center p-5"><i class="fa fa-spinner fa-spin" style="font-size: 2rem;"></i><p class="mt-3">Cargando estadísticas...</p></div>';

        fetch('admin/ajax/rqst.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: 'op=get_estadisticas_bd'
        })
        .then(response => response.json())
        .then(data => {
            container.innerHTML = renderStats(data);
        })
        .catch(error => {
            console.error('Error:', error);
            container.innerHTML = '<div class="text-center text-muted p-5"><i class="bi bi-exclamation-triangle" style="font-size: 2rem;"></i><p class="mt-2">Error de conexión</p></div>';
        });
    }

    $(document).ready(function() {
        cargarEstadisticas();
    });
    </script>
</body>
</html>
