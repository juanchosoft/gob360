<?php
include './admin/include/head.php';
include './admin/include/dark_theme.php';
require './admin/include/generic_classes.php';
require './admin/classes/SeguimientoAlcaldias.php';
require_once './admin/db/colores.php';

$userType = SessionData::getUserType();
$isSuperAdmin = ($userType === Util::SuperAdministrador() || $userType === Util::Gobernador());
if (!$isSuperAdmin) { require 'permiso_denegado.php'; exit; }

$data = SeguimientoAlcaldias::getResumenMunicipios();
$rows = $data['output']['valid'] ? ($data['output']['response'] ?? []) : [];
$totales = SeguimientoAlcaldias::getTotales($rows);

function h($v) { return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }
function sh($v) { return $v ? number_format((int)$v, 0, ',', '.') : '0'; }
?>
<body>
  <div class="loader-bg"><div class="loader-track"><div class="loader-fill"></div></div></div>
  <?php include './admin/include/navbar.php'; ?>
  <?php include './admin/include/header.php'; ?>

  <style>
    :root{ --safe-top:96px; }
    .page-header .page-block{
      background: rgba(255,255,255,.06) !important;
      border:1px solid rgba(255,255,255,.12) !important;
      border-radius:22px !important; padding:16px 20px !important;
      backdrop-filter:blur(10px);
    }
    .page-header h5{ color:#fff !important; font-weight:1000 !important; margin:0; }
    .breadcrumb{ background:transparent !important; padding:0; margin:.35rem 0 0 !important; }
    .breadcrumb a,.breadcrumb-item{ color:rgba(255,255,255,.78) !important; font-weight:800; }

    .kpi-grid{ display:grid; grid-template-columns:repeat(auto-fit,minmax(160px,1fr)); gap:12px; margin-bottom:18px; }
    .kpi-card{
      border-radius:16px; padding:14px;
      background:rgba(255,255,255,.06); backdrop-filter:blur(10px);
      border:1px solid rgba(255,255,255,.10);
      text-align:center;
    }
    .kpi-card .label{ color:rgba(255,255,255,.7); font-weight:900; font-size:11px; text-transform:uppercase; }
    .kpi-card .value{ color:#fff; font-weight:1100; font-size:24px; margin-top:4px; }
    .kpi-card .sub{ color:rgba(255,255,255,.5); font-weight:700; font-size:11px; margin-top:2px; }

    .map-wrap{
      background:rgba(255,255,255,.06); backdrop-filter:blur(10px);
      border:1px solid rgba(255,255,255,.10); border-radius:22px;
      padding:20px; margin-bottom:18px;
    }
    .map-wrap svg{ width:100%; height:auto; max-height:680px; display:block; margin:0 auto; }
    .map-wrap svg text{ fill:#fff !important; font-weight:700; }
    .map-wrap .municipio-path{ cursor:pointer; transition:opacity .15s, transform .15s; stroke:#0f172a; stroke-width:.8; }
    .map-wrap .municipio-path:hover{ opacity:.8; transform:translateY(-1px); filter:drop-shadow(0 4px 12px rgba(0,0,0,.4)); }

    .legend{ display:flex; flex-wrap:wrap; gap:14px; margin-top:14px; padding:12px 16px; border-radius:14px; background:rgba(0,0,0,.2); }
    .legend-item{ display:flex; align-items:center; gap:8px; font-size:12px; font-weight:800; color:rgba(255,255,255,.8); }
    .legend-dot{ width:14px; height:14px; border-radius:4px; flex-shrink:0; }

    .tabla-wrap{
      background:rgba(255,255,255,.06); backdrop-filter:blur(10px);
      border:1px solid rgba(255,255,255,.10); border-radius:22px;
      padding:18px; overflow:hidden;
    }
    #tablaMunicipios{ width:100% !important; border-collapse:collapse; }
    #tablaMunicipios thead th{
      background:rgba(255,255,255,.08) !important; color:#fff !important; font-weight:1000;
      border-bottom:1px solid rgba(255,255,255,.14) !important;
      padding:12px 10px !important; font-size:12px; text-align:center; white-space:nowrap;
      position:sticky; top:0; z-index:2;
    }
    #tablaMunicipios thead th:first-child{ text-align:left; }
    #tablaMunicipios td{
      color:rgba(255,255,255,.86) !important; padding:10px !important;
      border-bottom:1px solid rgba(255,255,255,.05) !important;
      font-size:13px; text-align:center; font-weight:700;
    }
    #tablaMunicipios td:first-child{ text-align:left; font-weight:800; }
    #tablaMunicipios tbody tr:hover td{ background:rgba(255,255,255,.04) !important; }
    #tablaMunicipios tbody tr{ background:transparent !important; }
    .badge-datos{ display:inline-block; padding:2px 12px; border-radius:999px; font-size:11px; font-weight:900; text-transform:uppercase; }
    .badge-estable{ background:rgba(46,125,50,.2); color:#4ade80; border:1px solid rgba(46,125,50,.3); }
    .badge-info{ background:rgba(30,102,245,.2); color:#60a5fa; border:1px solid rgba(30,102,245,.3); }
    .badge-alto{ background:rgba(251,140,0,.2); color:#fb923c; border:1px solid rgba(251,140,0,.3); }
    .badge-critico{ background:rgba(229,57,53,.2); color:#f87171; border:1px solid rgba(229,57,53,.3); }
    .badge-neutro{ background:rgba(51,65,85,.2); color:#94a3b8; border:1px solid rgba(51,65,85,.3); }
  </style>

  <div class="pcoded-main-container">
    <div class="pcoded-content">
      <div class="page-header mb-3">
        <div class="page-block">
          <div class="row align-items-center">
            <div class="col-md-12">
              <h5 class="m-b-10">Seguimiento a Alcaldías</h5>
              <ul class="breadcrumb">
                <li class="breadcrumb-item"><a href="index.php"><i class="feather icon-home"></i></a></li>
                <li class="breadcrumb-item"><a href="#!">Administración</a></li>
                <li class="breadcrumb-item">Seguimiento Alcaldías</li>
              </ul>
            </div>
          </div>
        </div>
      </div>

      <div class="kpi-grid">
        <div class="kpi-card">
          <div class="label">Municipios</div>
          <div class="value"><?= $totales['municipios'] ?></div>
          <div class="sub">de Santander</div>
        </div>
        <div class="kpi-card">
          <div class="label">Con datos</div>
          <div class="value" style="color:#22c55e;"><?= $totales['con_datos'] ?></div>
          <div class="sub"><?= $totales['municipios'] > 0 ? round(($totales['con_datos'] / $totales['municipios']) * 100) : 0 ?>% del total</div>
        </div>
        <div class="kpi-card">
          <div class="label">Proyectos</div>
          <div class="value"><?= sh($totales['proyectos']) ?></div>
          <div class="sub">Registrados</div>
        </div>
        <div class="kpi-card">
          <div class="label">Visitas</div>
          <div class="value"><?= sh($totales['visitas']) ?></div>
          <div class="sub">Realizadas</div>
        </div>
        <div class="kpi-card">
          <div class="label">Compromisos</div>
          <div class="value"><?= sh($totales['compromisos']) ?></div>
          <div class="sub">Registrados</div>
        </div>
        <div class="kpi-card">
          <div class="label">Metas PDD</div>
          <div class="value"><?= sh($totales['metas']) ?></div>
          <div class="sub">Plan Desarrollo</div>
        </div>
      </div>

      <div class="map-wrap">
        <svg viewBox="0 80 1280 1400" xmlns="http://www.w3.org/2000/svg" style="width:100%;height:auto;">
          <?php foreach ($rows as $m): 
            $cod = $m['codigo_muncipio'];
          ?>
            <path class="municipio-path"
                  d="<?= h($m['d'] ?? '') ?>"
                  fill="<?= h($m['color'] ?? '#1e293b') ?>"
                  data-mun="<?= h($cod) ?>"
                  data-nombre="<?= h($m['municipio']) ?>"
                  data-proy="<?= (int)$m['total_proyectos'] ?>"
                  data-metas="<?= (int)$m['total_metas'] ?>"
                  data-visitas="<?= (int)$m['total_visitas'] ?>"
                  data-compromisos="<?= (int)$m['total_compromisos'] ?>"
                  data-componentes="<?= (int)$m['total_componentes'] ?>"
                  data-secretarias="<?= (int)$m['total_secretarias'] ?>"
                  data-total="<?= (int)$m['total_general'] ?>"
                  title="<?= h($m['municipio']) ?>: <?= (int)$m['total_general'] ?> registros"/>
          <?php endforeach; ?>
          <?php require 'nombres_mapa_santander.php'; ?>
        </svg>

        <div class="legend">
          <span class="legend-item"><span class="legend-dot" style="background:#2E7D32;"></span> <b>Estable</b> = 30 o más registros</span>
          <span class="legend-item"><span class="legend-dot" style="background:#1E66F5;"></span> <b>Info</b> = entre 15 y 29 registros</span>
          <span class="legend-item"><span class="legend-dot" style="background:#FB8C00;"></span> <b>Alto</b> = entre 5 y 14 registros</span>
          <span class="legend-item"><span class="legend-dot" style="background:#E53935;"></span> <b>Crítico</b> = entre 1 y 4 registros</span>
          <span class="legend-item"><span class="legend-dot" style="background:#334155;"></span> <b>Neutro</b> = sin registros</span>
        </div>
      </div>

      <div class="tabla-wrap">
          <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:14px;flex-wrap:wrap;gap:12px;">
          <div>
            <h4 style="color:#fff;font-weight:1000;margin:0;font-size:16px;">Detalle por Municipio</h4>
            <p style="color:rgba(255,255,255,.6);font-size:12px;margin:4px 0 0;">Cantidad de registros ingresados por cada alcaldía</p>
          </div>
          <div style="display:flex;gap:10px;">
            <button onclick="descargarExcel()" style="padding:10px 18px;border-radius:14px;border:1px solid rgba(255,255,255,.14);background:linear-gradient(135deg,#3b82f6,#4f46e5);color:#fff;font-weight:900;cursor:pointer;font-size:13px;white-space:nowrap;">
              <i class="feather icon-download"></i> Exportar Excel
            </button>
            <input type="text" id="buscarMunicipio" placeholder="Buscar municipio..." style="padding:10px 14px;border-radius:14px;border:1px solid rgba(255,255,255,.14);background:rgba(255,255,255,.06);color:#fff;font-weight:800;outline:none;width:250px;max-width:100%;">
          </div>
        </div>

        <?php if (!empty($rows)): ?>
        <div style="overflow-x:auto;">
          <table id="tablaMunicipios">
            <thead>
              <tr>
                <th>Municipio</th>
                <th>Proyectos</th>
                <th>Metas PDD</th>
                <th>Visitas</th>
                <th>Compromisos</th>
                <th>Componentes</th>
                <th>Secretarías</th>
                <th>Total</th>
                <th>Estado</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($rows as $m): 
                $total = (int)$m['total_general'];
                $color = $m['color'] ?? '#1e293b';
                $clase = $m['clase'] ?? 'nula';
              ?>
                <tr data-nombre="<?= h(strtolower($m['municipio'])) ?>">
                  <td><strong><?= h($m['municipio']) ?></strong></td>
                  <td><?= sh($m['total_proyectos']) ?></td>
                  <td><?= sh($m['total_metas']) ?></td>
                  <td><?= sh($m['total_visitas']) ?></td>
                  <td><?= sh($m['total_compromisos']) ?></td>
                  <td><?= sh($m['total_componentes']) ?></td>
                  <td><?= sh($m['total_secretarias']) ?></td>
                  <td><strong><?= sh($total) ?></strong></td>
                  <td><span class="badge-datos badge-<?= $clase ?>"><?= $clase ?></span></td>
                </tr>
              <?php endforeach; ?>
            </tbody>
            <tfoot>
              <tr style="background:rgba(255,255,255,.06);font-weight:1100;">
                <td style="color:#fff;padding:12px 10px;border-top:2px solid rgba(255,255,255,.15);font-size:13px;"><strong>TOTALES</strong></td>
                <td style="color:#fff;padding:12px 10px;border-top:2px solid rgba(255,255,255,.15);font-size:13px;text-align:center;"><strong><?= sh($totales['proyectos']) ?></strong></td>
                <td style="color:#fff;padding:12px 10px;border-top:2px solid rgba(255,255,255,.15);font-size:13px;text-align:center;"><strong><?= sh($totales['metas']) ?></strong></td>
                <td style="color:#fff;padding:12px 10px;border-top:2px solid rgba(255,255,255,.15);font-size:13px;text-align:center;"><strong><?= sh($totales['visitas']) ?></strong></td>
                <td style="color:#fff;padding:12px 10px;border-top:2px solid rgba(255,255,255,.15);font-size:13px;text-align:center;"><strong><?= sh($totales['compromisos']) ?></strong></td>
                <td style="color:#fff;padding:12px 10px;border-top:2px solid rgba(255,255,255,.15);font-size:13px;text-align:center;"><strong><?= sh($totales['componentes']) ?></strong></td>
                <td style="color:#fff;padding:12px 10px;border-top:2px solid rgba(255,255,255,.15);font-size:13px;text-align:center;"><strong><?= sh($totales['secretarias']) ?></strong></td>
                <td style="color:#fff;padding:12px 10px;border-top:2px solid rgba(255,255,255,.15);font-size:13px;text-align:center;"><strong><?= sh($totales['proyectos'] + $totales['metas'] + $totales['visitas'] + $totales['compromisos'] + $totales['componentes'] + $totales['secretarias']) ?></strong></td>
                <td style="padding:12px 10px;border-top:2px solid rgba(255,255,255,.15);text-align:center;"></td>
              </tr>
            </tfoot>
          </table>
        </div>
        <?php endif; ?>
      </div>
    </div>
  </div>

  <?php include 'admin/include/gerenic_script.php'; ?>
  <script src="assets/js/vendor-all.min.js"></script>
  <script src="assets/js/plugins/bootstrap.min.js"></script>
  <script src="assets/js/pcoded.min.js"></script>

  <script>
    $(window).on('load', function() { $('.loader-bg').fadeOut('slow', function() { $(this).remove(); }); });
    setTimeout(function() { $('.loader-bg').fadeOut('slow', function() { $(this).remove(); }); }, 2000);

    // Descargar Excel
    function descargarExcel() {
      var rows = [];
      $('#tablaMunicipios thead th').each(function() { rows.push($(this).text().trim()); });
      var csv = '\uFEFF' + rows.join(';') + '\n';
      $('#tablaMunicipios tbody tr, #tablaMunicipios tfoot tr').each(function() {
        var row = [];
        $(this).find('td').each(function() { row.push($(this).text().trim()); });
        csv += row.join(';') + '\n';
      });
      var blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
      var link = document.createElement('a');
      link.href = URL.createObjectURL(blob);
      link.download = 'seguimiento_alcaldias_' + new Date().toISOString().slice(0,10) + '.csv';
      link.click();
    }

    // Filtro de búsqueda
    $('#buscarMunicipio').on('keyup', function() {
      var q = this.value.toLowerCase().trim();
      $('#tablaMunicipios tbody tr').each(function() {
        $(this).toggle($(this).data('nombre').indexOf(q) !== -1);
      });
    });

    // Click en mapa -> scroll y resaltar fila
    document.querySelectorAll('.municipio-path').forEach(function(path) {
      path.addEventListener('click', function() {
        var nombre = this.getAttribute('data-nombre');
        var rows = document.querySelectorAll('#tablaMunicipios tbody tr');
        rows.forEach(function(r) {
          if (r.getAttribute('data-nombre') === nombre.toLowerCase()) {
            r.style.background = 'rgba(59,130,246,.15)';
            r.scrollIntoView({ behavior: 'smooth', block: 'center' });
            setTimeout(function() { r.style.background = ''; }, 2000);
          }
        });
      });
    });
  </script>
</body>
</html>
