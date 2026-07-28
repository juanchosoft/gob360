<?php
include './admin/include/head.php';
include './admin/include/dark_theme.php';
require './admin/include/generic_classes.php';

// Permisos RBAC
extract(PagePermissions::crudVarsForCurrentPage());

/* if (!$view) { require 'permiso_denegado.php'; } */

include './admin/classes/Visitasg.php';
include './admin/classes/Linea.php';
include './admin/classes/Estrategia.php';

// Ambos tipos (Red 1 y Red 2) desde tbl_gestora
$arr = Visitasg::getAll(null);
$isvalid = $arr['output']['valid'] ?? false;
$arr = $arr['output']['response'] ?? [];

$labelsTipo = [
  Visitasg::TIPO_PRIMERA_DAMA => 'Red de Valor Social 1',
  Visitasg::TIPO_ASPAS => 'Red de Valor Social 2',
];

$codigoDepartamento = Util::getDepartamentoPrincipal(); // siempre Santander (68)

// Líneas
$lineas = Linea::getAll(null);
$lineasResponse = $lineas['output']['response'] ?? [];
$optionLineas = "";
foreach ($lineasResponse as $linea) {
  $optionLineas .= "<option value='" . $linea['id'] . "'>" . htmlspecialchars($linea['nombre'], ENT_QUOTES, 'UTF-8') . "</option>";
}

// Estrategias
$estrategias = Estrategia::getAll(null);
$estrategiasResponse = $estrategias['output']['response'] ?? [];
$optionEstrategias = "";
foreach ($estrategiasResponse as $estrategia) {
  $optionEstrategias .= "<option value='" . $estrategia['id'] . "'>" . htmlspecialchars($estrategia['nombre'], ENT_QUOTES, 'UTF-8') . "</option>";
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Cuadro control — Red de Valor Social</title>
  <style>
    :root{
      --bg0:#070A12;
      --bg1:#0B1222;
      --stroke: rgba(255,255,255,.10);
      --stroke2: rgba(255,255,255,.14);
      --txt: rgba(255,255,255,.92);
      --muted: rgba(255,255,255,.66);
      --brand:#4f7cff;
      --brand2:#9b5cff;
      --r-xl:18px;
      --shadow: 0 20px 60px rgba(0,0,0,.35);
      --shadow2: 0 14px 40px rgba(0,0,0,.25);
    }

    body{
      background:
        radial-gradient(900px 420px at 10% 10%, rgba(79,124,255,.28), transparent 60%),
        radial-gradient(900px 420px at 80% 20%, rgba(155,92,255,.22), transparent 60%),
        radial-gradient(900px 520px at 50% 100%, rgba(24,255,109,.10), transparent 60%),
        linear-gradient(180deg, var(--bg0), var(--bg1)) !important;
      color: var(--txt);
      overflow-x:hidden;
    }

    .pcoded-main-container{ background: transparent !important; }
    .pcoded-content{ padding: 16px 16px !important; }
    @media(min-width:768px){ .pcoded-content{ padding: 24px 24px !important; } }
    @media(min-width:1200px){ .pcoded-content{ padding: 34px 42px !important; } }

    .page-header .page-block{
      border:1px solid var(--stroke);
      background: rgba(255,255,255,.05);
      border-radius: 16px;
      padding: 14px 14px;
      box-shadow: var(--shadow2);
      overflow:hidden;
      position: relative;
    }
    .page-header .page-block:before{
      content:"";
      position:absolute; inset:-2px;
      background:
        radial-gradient(320px 180px at 10% 10%, rgba(79,124,255,.25), transparent 65%),
        radial-gradient(320px 180px at 90% 20%, rgba(155,92,255,.18), transparent 65%);
      pointer-events:none;
    }
    .page-header .page-block > *{ position:relative; z-index:1; }
    .page-header h5, .breadcrumb .breadcrumb-item, .breadcrumb .breadcrumb-item a{
      color: var(--txt) !important;
    }
    .breadcrumb .breadcrumb-item a{ color: var(--muted) !important; }

    .card{
      border: 1px solid var(--stroke) !important;
      border-radius: var(--r-xl) !important;
      background: linear-gradient(135deg, rgba(255,255,255,.08), rgba(255,255,255,.04)) !important;
      box-shadow: var(--shadow);
      overflow: hidden;
      position: relative;
    }
    .card:before{
      content:"";
      position:absolute; inset:-2px;
      background:
        radial-gradient(320px 180px at 10% 10%, rgba(79,124,255,.35), transparent 65%),
        radial-gradient(320px 180px at 90% 20%, rgba(155,92,255,.25), transparent 65%),
        radial-gradient(520px 220px at 50% 120%, rgba(24,255,109,.10), transparent 60%);
      pointer-events:none;
    }
    .card > *{ position:relative; z-index:1; }
    .card-header{
      background: rgba(0,0,0,.14) !important;
      border-bottom: 1px solid var(--stroke) !important;
      padding: 18px 18px !important;
    }
    .card-header h5{
      font-weight: 900 !important;
      letter-spacing: .2px;
      color: var(--txt) !important;
      margin:0 !important;
    }
    .card-body{ padding: 18px !important; }
    @media(min-width:768px){ .card-body{ padding: 22px !important; } }

    .form-control, select.form-control, textarea.form-control,
    #editModal select, #editModal textarea, #editModal .form-control{
      border-radius: 14px !important;
      border: 1px solid rgba(255,255,255,.14) !important;
      background: rgba(0,0,0,.28) !important;
      color: var(--txt) !important;
      padding: 12px 14px !important;
      min-height: 46px;
      box-shadow:none !important;
    }
    .form-control::placeholder{ color: rgba(255,255,255,.50) !important; }
    .form-control:focus, select.form-control:focus, textarea.form-control:focus{
      border-color: rgba(79,124,255,.55) !important;
      box-shadow: 0 0 0 .2rem rgba(79,124,255,.18) !important;
      outline: none !important;
    }
    select.form-control option, #editModal select option{ color:#0B1B38; background:#fff; }
    label{ color: rgba(255,255,255,.72) !important; font-weight: 900; }

    .btn{
      border-radius: 14px !important;
      padding: 10px 22px !important;
      font-weight: 900 !important;
      border: 1px solid rgba(255,255,255,.14) !important;
      box-shadow: 0 10px 24px rgba(0,0,0,.25);
    }
    .btn-sm{ padding: 6px 12px !important; }
    .btn-primary{
      border-color: rgba(79,124,255,.45) !important;
      background: linear-gradient(135deg, rgba(79,124,255,.35), rgba(155,92,255,.22)) !important;
      color:#fff !important;
    }
    .btn-warning{
      border-color: rgba(255,209,102,.45) !important;
      background: linear-gradient(135deg, rgba(255,209,102,.28), rgba(0,0,0,.22)) !important;
      color:#fff !important;
    }
    .btn-danger{
      border-color: rgba(255,91,122,.45) !important;
      background: linear-gradient(135deg, rgba(255,91,122,.22), rgba(0,0,0,.22)) !important;
      color:#fff !important;
    }
    .btn-secondary{
      background: rgba(255,255,255,.06) !important;
      color: var(--txt) !important;
    }

    .gs-filter-bar{
      display:flex;
      flex-wrap:wrap;
      align-items:center;
      gap:10px;
      margin-bottom:14px;
      padding:12px 14px;
      border-radius:14px;
      border:1px solid var(--stroke);
      background: rgba(0,0,0,.18);
      box-shadow: var(--shadow2);
    }
    .gs-filter-label{ font-weight:800; color:var(--muted); font-size:13px; }
    .gs-filter-btn{
      border-radius:999px !important;
      border:1px solid var(--stroke2) !important;
      background: rgba(0,0,0,.22) !important;
      color: rgba(255,255,255,.86) !important;
      font-weight:800 !important;
      padding:7px 12px !important;
      box-shadow:none !important;
    }
    .gs-filter-btn.active,
    .gs-filter-btn:hover{
      background: linear-gradient(135deg, rgba(79,124,255,.40), rgba(155,92,255,.28)) !important;
      border-color: rgba(79,124,255,.50) !important;
      color:#fff !important;
    }

    .badge-tipo{
      display:inline-block;
      padding:4px 10px;
      border-radius:999px;
      font-size:11px;
      font-weight:800;
      white-space:nowrap;
    }
    .badge-tipo-1{
      background: rgba(79,124,255,.18);
      color: #9ec0ff;
      border: 1px solid rgba(79,124,255,.35);
    }
    .badge-tipo-2{
      background: rgba(155,92,255,.18);
      color: #d2b4ff;
      border: 1px solid rgba(155,92,255,.35);
    }

    .table-responsive{
      border-radius: 16px;
      border: 1px solid var(--stroke) !important;
      background: rgba(0,0,0,.16);
      overflow:auto;
      margin-top: 14px;
    }
    .table{
      margin-bottom: 0 !important;
      color: var(--txt) !important;
      font-size: 12.5px !important;
    }
    .table thead th{
      background: rgba(255,255,255,.06) !important;
      color: rgba(255,255,255,.88) !important;
      border-bottom: 1px solid var(--stroke) !important;
      white-space: nowrap;
      font-size: 12px !important;
      letter-spacing: .2px;
      text-transform: uppercase;
    }
    .table tbody tr{
      background: transparent !important;
      transition: background .15s ease, color .15s ease;
    }
    .table tbody td{
      color: rgba(255,255,255,.86) !important;
      border-top: 1px solid rgba(255,255,255,.06) !important;
      vertical-align: middle !important;
      white-space: normal;
      word-break: break-word;
    }
    .table-hover tbody tr:hover,
    table.dataTable.hover tbody tr:hover{
      background: rgba(255,255,255,.06) !important;
    }
    .table tbody td a{ color: rgba(255,255,255,.86) !important; }
    .table tbody td i.feather{ color: rgba(255,255,255,.86) !important; }

    .dataTables_wrapper .dataTables_filter label,
    .dataTables_wrapper .dataTables_length label,
    .dataTables_wrapper .dataTables_info{
      color: var(--muted) !important;
    }
    .dataTables_wrapper .dataTables_paginate .paginate_button{
      color: var(--txt) !important;
      border-radius: 12px !important;
      border: 1px solid var(--stroke2) !important;
      background: rgba(0,0,0,.20) !important;
    }
    .dataTables_wrapper .dataTables_paginate .paginate_button.current{
      background: linear-gradient(135deg, rgba(79,124,255,.35), rgba(155,92,255,.22)) !important;
      border-color: rgba(79,124,255,.45) !important;
    }

    /* Modal dark */
    .modal-backdrop{ background:#000 !important; }
    .modal-backdrop.show{ opacity:.90 !important; }
    .modal-content{
      border-radius: 18px !important;
      border: 1px solid rgba(255,255,255,.14) !important;
      background: linear-gradient(135deg, rgba(10,12,18,.96), rgba(0,0,0,.94)) !important;
      color: var(--txt) !important;
      box-shadow: var(--shadow);
      overflow:hidden;
    }
    .modal-header{
      background: linear-gradient(135deg, rgba(79,124,255,.35), rgba(155,92,255,.22)) !important;
      border-bottom: 1px solid rgba(255,255,255,.12) !important;
    }
    .modal-title{ font-weight: 900 !important; color:#fff !important; margin:0; }
    .close, .close span{ color:#fff !important; opacity: 1 !important; text-shadow:none !important; }

    .foto-actual-thumb{
      border-radius: 12px;
      overflow: hidden;
      border: 1px solid rgba(255,255,255,.14);
      background: rgba(0,0,0,.25);
      display: flex;
      align-items: center;
      justify-content: center;
    }
    .foto-actual-thumb img{
      width: 100%;
      height: 120px;
      object-fit: cover;
      display: block;
      cursor: pointer;
      transition: transform .15s;
    }
    .foto-actual-thumb img:hover{ transform: scale(1.04); }
    .foto-actual-label{
      font-size: 11px;
      color: var(--muted);
      text-align: center;
      margin-top: 4px;
    }
    .upload-card{
      border: 1px dashed rgba(255,255,255,.22);
      border-radius: 16px;
      padding: 10px;
      background: rgba(0,0,0,.18);
    }
    .upload-card .preview{
      display:flex;
      align-items:center;
      gap:10px;
      margin-bottom: 8px;
      color: var(--txt);
    }
    .upload-card img{
      width: 58px;
      height: 58px;
      border-radius: 12px;
      object-fit: cover;
      border: 1px solid rgba(255,255,255,.14);
      background: rgba(0,0,0,.25);
    }
    .upload-card iframe{
      width: 100% !important;
      height: 62px !important;
      border-radius: 12px;
      background: rgba(255,255,255,.92);
    }
    .modal-actions{
      position: sticky;
      bottom: 0;
      background: linear-gradient(180deg, rgba(0,0,0,0), rgba(0,0,0,.85) 22%);
      padding-top: 12px;
      margin-top: 6px;
    }
    .modal-actions .bar{
      display:flex;
      gap:10px;
      justify-content:flex-end;
      padding: 10px;
      border-radius: 16px;
      border: 1px solid var(--stroke);
      box-shadow: var(--shadow2);
      background: rgba(0,0,0,.35);
    }
    .section-label-muted{
      font-weight:700;
      margin-bottom:8px;
      color: var(--muted);
    }
    @media (max-width: 992px){
      #editModal .modal-dialog{ max-width: 92vw; }
    }
    @media (max-width: 768px){
      .modal-actions .bar{ justify-content: space-between; }
      .modal-actions .bar .btn{ width: 49%; }
    }
  </style>
</head>

<body class="">
  <div class="loader-bg">
    <div class="loader-track"><div class="loader-fill"></div></div>
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
                <h5 class="m-b-10">Cuadro detalle visitas gestora social</h5>
                <?php include './admin/include/btn_back.php'; ?>
              </div>
              <ul class="breadcrumb">
                <li class="breadcrumb-item"><a href="index.php"><i class="feather icon-home"></i></a></li>
                <li class="breadcrumb-item"><a href="#!">Gestión Social</a></li>
                <li class="breadcrumb-item"><a href="#!">Cuadro control actividades</a></li>
              </ul>
            </div>
          </div>
        </div>
      </div>

      <div class="row">
        <div class="col-sm-12">
          <div class="card">
            <div class="card-header d-flex align-items-center justify-content-between">
              <h5><i class="feather icon-list"></i> Tabla de Acciones</h5>
            </div>

            <div class="card-body">
              <div class="gs-filter-bar">
                <div class="gs-filter-label"><i class="feather icon-filter"></i> Vista</div>
                <div>
                  <button type="button" class="btn btn-sm gs-filter-btn" data-view="primera_dama">Ver Red de Valor Social 1</button>
                  <button type="button" class="btn btn-sm gs-filter-btn" data-view="aspas">Ver Red de Valor Social 2</button>
                  <button type="button" class="btn btn-sm gs-filter-btn active" data-view="ambos">Red de Valor Social 1 y 2</button>
                </div>
              </div>

              <div class="table-responsive">
                <table id="dynamictable" class="table table-hover table-bordered" style="width:100%">
                  <thead>
                    <tr>
                      <th>Ver</th>
                      <th>Tipo</th>
                      <th>Provincia</th>
                      <th>Municipio</th>
                      <th>Población Impactada</th>
                      <th>Inversión</th>
                      <th>Linea</th>
                      <th>Estrategia</th>
                      <th>Nombre</th>
                      <th>Actividad</th>
                      <th>Fecha</th>
                      <th>Link</th>
                      <th>Imagen</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php if ($isvalid && !empty($arr)): ?>
                      <?php foreach ($arr as $item): ?>
                        <?php
                          $tipoItem = $item['tipo_actividad'] ?? Visitasg::TIPO_PRIMERA_DAMA;
                          $labelTipo = $labelsTipo[$tipoItem] ?? $tipoItem;
                          $badgeClass = ($tipoItem === Visitasg::TIPO_ASPAS) ? 'badge-tipo-2' : 'badge-tipo-1';
                        ?>
                        <tr data-tipo="<?= htmlspecialchars($tipoItem, ENT_QUOTES, 'UTF-8'); ?>">
                          <td style="min-width:88px">
                            <form action="reporte_visitag.php" method="POST" target="_blank" style="display:inline;">
                              <input type="hidden" name="reporte" value="<?= htmlspecialchars($item['id']); ?>">
                              <button type="submit" class="btn btn-sm btn-primary" title="Ver">
                                <i class="feather icon-eye"></i>
                              </button>
                              <button type="button" class="btn btn-sm btn-warning mt-2" title="Editar"
                                onclick="VISITASG.editData(<?= (int)$item['id'] ?>)">
                                <i class="feather icon-edit"></i>
                              </button>
                            </form>
                          </td>
                          <td>
                            <span class="badge-tipo <?= $badgeClass ?>"><?= htmlspecialchars($labelTipo, ENT_QUOTES, 'UTF-8'); ?></span>
                          </td>
                          <td><?= htmlspecialchars($item['provincia'] ?? ''); ?></td>
                          <td><?= htmlspecialchars($item['municipio'] ?? ''); ?></td>
                          <td><?= htmlspecialchars($item['poblacion'] ?? ''); ?></td>
                          <td><?= htmlspecialchars($item['inversion'] ?? ''); ?></td>
                          <td><?= htmlspecialchars($item['linea_nombre'] ?? ''); ?></td>
                          <td><?= htmlspecialchars($item['estrategia_nombre'] ?? ''); ?></td>
                          <td><?= htmlspecialchars($item['campana'] ?? ''); ?></td>
                          <td><?= htmlspecialchars($item['actividad'] ?? ''); ?></td>
                          <td><?= htmlspecialchars($item['date'] ?? ''); ?></td>
                          <td style="text-align:center; min-width:72px">
                            <?php if (!empty($item['link'])): ?>
                              <button type="button" class="btn btn-sm btn-danger" title="Abrir link"
                                onclick="window.open('<?= htmlspecialchars($item['link'], ENT_QUOTES, 'UTF-8'); ?>', '_blank')">
                                <i class="feather icon-external-link"></i>
                              </button>
                            <?php endif; ?>
                          </td>
                          <td style="min-width:92px">
                            <?php for ($i = 1; $i <= 4; $i++): ?>
                              <?php if (!empty($item["foto$i"])): ?>
                                <a href="<?= htmlspecialchars($item["foto$i"], ENT_QUOTES, 'UTF-8') ?>" target="_blank" title="Imagen <?= $i ?>" style="margin-right:8px;">
                                  <i class="feather icon-image"></i>
                                </a>
                              <?php endif; ?>
                            <?php endfor; ?>
                          </td>
                        </tr>
                      <?php endforeach; ?>
                    <?php else: ?>
                      <tr>
                        <td colspan="13" class="text-center">No hay datos disponibles</td>
                      </tr>
                    <?php endif; ?>
                  </tbody>
                </table>
              </div>
            </div>
          </div>
        </div>
      </div>

    </div>
  </div>

  <!-- Modal Editar -->
  <div class="modal fade" id="editModal" tabindex="-1" role="dialog" aria-labelledby="editModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
      <form id="editForm" class="w-100">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="editModalLabel" style="display:flex; align-items:center; gap:10px;">
              <i class="fas fa-edit"></i> Editar Visita
            </h5>
            <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar">
              <span aria-hidden="true">&times;</span>
            </button>
          </div>

          <div class="modal-body" style="padding: 18px;">
            <input type="hidden" id="id" name="id">
            <input type="hidden" id="date" name="date">
            <input type="hidden" id="tbl_departamento_id" name="tbl_departamento_id" value="<?php echo htmlspecialchars($codigoDepartamento, ENT_QUOTES, 'UTF-8'); ?>">

            <div class="row g-3">
              <div class="col-md-6">
                <label for="tipo_actividad"><i class="fas fa-layer-group"></i> Tipo de actividad <span class="text-danger">*</span></label>
                <select class="form-control" id="tipo_actividad" name="tipo_actividad" required>
                  <option value="">Seleccione</option>
                  <option value="primera_dama">Red de Valor Social 1</option>
                  <option value="aspas">Red de Valor Social 2</option>
                </select>
              </div>

              <div class="col-md-6">
                <label for="tbl_municipio_id"><i class="fas fa-map-pin"></i> Municipio</label>
                <select class="form-control" id="tbl_municipio_id" name="tbl_municipio_id" onchange="DEPARTAMENTO.getVeredasByMunicipioId();"></select>
              </div>

              <div class="col-md-6">
                <label for="provincia"><i class="fas fa-map"></i> Provincia</label>
                <select class="form-control" id="provincia" name="provincia">
                  <option value="Seleccione">Seleccione</option>
                  <option value="Soto_Norte">Soto Norte</option>
                  <option value="Guanenta">Guanentá</option>
                  <option value="Garcia_Rovira">García Rovira</option>
                  <option value="Comunera">Comunera</option>
                  <option value="Velez">Velez</option>
                  <option value="Metropolitana">Metropolitana</option>
                  <option value="Yariguíes">Yariguíes</option>
                </select>
              </div>

              <div class="col-md-6">
                <label for="poblacion"><i class="fas fa-users"></i> Población Impactada</label>
                <input type="text" class="form-control" id="poblacion" name="poblacion">
              </div>

              <div class="col-md-12">
                <label for="desc_actividad"><i class="fas fa-align-left"></i> Descripción Actividad</label>
                <textarea class="form-control" id="desc_actividad" name="desc_actividad" rows="4"></textarea>
              </div>

              <div class="col-md-6">
                <label for="inversion"><i class="fas fa-dollar-sign"></i> Inversión Estimada</label>
                <input type="text" class="form-control" id="inversion" name="inversion">
              </div>

              <div class="col-md-3">
                <label for="tbl_linea_id"><i class="fas fa-stream"></i> Línea</label>
                <select class="form-control" id="tbl_linea_id" name="tbl_linea_id">
                  <option value="">Seleccione</option>
                  <?php echo $optionLineas; ?>
                </select>
              </div>

              <div class="col-md-3">
                <label for="tbl_estrategia_id"><i class="fas fa-lightbulb"></i> Estrategia</label>
                <select class="form-control" id="tbl_estrategia_id" name="tbl_estrategia_id">
                  <option value="">Seleccione</option>
                  <?php echo $optionEstrategias; ?>
                </select>
              </div>

              <div class="col-md-6">
                <label for="campana"><i class="fas fa-bullhorn"></i> Nombre</label>
                <select class="form-control" id="campana" name="campana">
                  <option value="Seleccione">Seleccione</option>
                  <option value="Niños al estadio">Niños al estadio</option>
                  <option value="Niños al cine">Niños al cine</option>
                  <option value="Niños al teatro">Niños al teatro</option>
                  <option value="Es tiempo de aprender">Es tiempo de aprender</option>
                  <option value="Niños al estadio - Optometría">Niños al estadio - Optometría</option>
                  <option value="Metale mente">Metale mente</option>
                </select>
              </div>

              <div class="col-md-6">
                <label for="actividad"><i class="fas fa-tasks"></i> Actividad</label>
                <input type="text" class="form-control" id="actividad" name="actividad">
              </div>

              <div class="col-md-12">
                <label for="link"><i class="fas fa-link"></i> Link Mediático</label>
                <input type="text" class="form-control" id="link" name="link">
              </div>

              <div class="col-12" id="seccion-fotos-actuales" style="display:none;">
                <div class="section-label-muted">
                  <i class="feather icon-image"></i> Fotos actuales
                </div>
                <div class="row g-2" id="grid-fotos-actuales"></div>
              </div>

              <div class="col-12">
                <div class="row g-3">
                  <?php for ($i = 1; $i <= 4; $i++): ?>
                    <div class="col-md-6">
                      <div class="upload-card">
                        <div class="preview">
                          <img id="preview-foto<?= $i ?>" src="" alt="Foto <?= $i ?>" style="display:none;">
                          <div>
                            <div style="font-weight:900">Editar foto <?= $i ?></div>
                            <div style="font-size:12px; color: rgba(255,255,255,.55);">Sube la imagen y se actualizará el registro</div>
                          </div>
                        </div>
                        <iframe id="ifm<?= $i ?>" name="ifm<?= $i ?>" src="upload.php?foto=foto<?= $i ?>" scrolling="no" frameborder="0"></iframe>
                      </div>
                    </div>
                  <?php endfor; ?>
                </div>
              </div>
            </div>

            <div class="modal-actions">
              <div class="bar">
                <button type="button" class="btn btn-danger" data-dismiss="modal">Cerrar</button>
                <button type="button" class="btn btn-primary" onclick="VISITASG.saveData();">Guardar</button>
              </div>
            </div>
          </div>
        </div>
      </form>
    </div>
  </div>

  <?php include 'admin/include/gerenic_script.php'; ?>
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
  <script src="assets/js/vendor-all.min.js"></script>
  <script src="assets/js/plugins/bootstrap.min.js"></script>
  <script src="assets/js/pcoded.min.js"></script>
  <script src="<?php echo Util::versionar('./admin/js/departamento.js'); ?>"></script>
  <script src="assets/js/plugins/prism.js"></script>
  <script src="<?php echo Util::versionar('./admin/js/departamentoDama.js'); ?>"></script>
  <script>
    setTimeout(function() {
      DEPARTAMENTO.getMunicipios();
    }, 1000);
  </script>
  <?php include './admin/include/generic_dataTables.php'; ?>
  <style>
    table.dataTable tbody tr{ background-color: transparent !important; }
    table.dataTable.stripe tbody tr.odd,
    table.dataTable.display tbody tr.odd{ background-color: rgba(255,255,255,.03) !important; }
    table.dataTable tbody td{ color: rgba(255,255,255,.86) !important; }
    table.dataTable tbody td a{ color: rgba(255,255,255,.86) !important; }
    table.dataTable tbody td i.feather{ color: rgba(255,255,255,.86) !important; }
    .dataTables_wrapper .dataTables_paginate .paginate_button{
      color: rgba(255,255,255,.86) !important;
      background: rgba(255,255,255,.06) !important;
      border: 1px solid rgba(255,255,255,.10) !important;
      border-radius: 8px !important;
    }
    .dataTables_wrapper .dataTables_paginate .paginate_button.current,
    .dataTables_wrapper .dataTables_paginate .paginate_button.current:hover{
      background: linear-gradient(135deg, rgba(79,124,255,.35), rgba(155,92,255,.22)) !important;
      border-color: rgba(79,124,255,.45) !important;
      color: #fff !important;
    }
    .dataTables_wrapper .dataTables_info,
    .dataTables_wrapper .dataTables_length label,
    .dataTables_wrapper .dataTables_filter label{
      color: rgba(255,255,255,.66) !important;
    }
  </style>
  <script src="<?php echo Util::versionar('./admin/js/cuadro_control_visitasg.js'); ?>"></script>
</body>
</html>
