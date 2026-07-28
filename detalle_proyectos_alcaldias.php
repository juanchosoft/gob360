<?php
include './admin/include/head.php';
require './admin/include/generic_classes.php';
include './admin/classes/ProyectosAlcaldias.php';

// Información de Proyectos por Id
$arr = ProyectosAlcaldias::getAll(["id" => $_REQUEST["id"]]);
$isvalid  = $arr['output']['valid'] ?? false;
$proyecto = $arr['output']['response'][0] ?? [];

//Información de Observaciones
$arrobser = ProyectosAlcaldias::getObservacionesByProyectoId($_REQUEST["id"]) ?? [];

function h($v){
  return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8');
}

// helpers UI (sin tocar backend)
$valorProyecto = (float)($proyecto["valor_proyecto"] ?? 0);
$porcEjec      = (float)($proyecto["porcentaje_ejecucion"] ?? 0);
$porcFin       = (float)($proyecto["porcentaje_financiero"] ?? 0);

$porcEjecInt = (int)max(0, min(100, round($porcEjec)));
$porcFinInt  = (int)max(0, min(100, round($porcFin)));

function estadoDot($estado){
  $s = mb_strtolower((string)$estado);
  if (strpos($s,'termin')!==false || strpos($s,'finaliz')!==false || strpos($s,'entreg')!==false || strpos($s,'liquid')!==false) return 'ok';
  if (strpos($s,'suspend')!==false || strpos($s,'desist')!==false) return 'bad';
  return 'warn';
}
$estadoActual = (string)($proyecto["estado"] ?? 'En Formulación');
$dot = estadoDot($estadoActual);
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
  <?php include './admin/include/navbar.php'; ?>
  <!-- [ navigation menu ] end -->

  <!-- [ Header ] start -->
  <?php include './admin/include/header.php'; ?>
  <!-- [ Header ] end -->

  <style>
    :root{
      --radius-xl: 20px;
      --radius-lg: 16px;
      --radius-md: 12px;

      --shadow-soft: 0 10px 28px rgba(2, 6, 23, .10);
      --shadow-mid:  0 16px 42px rgba(2, 6, 23, .14);

      --ink: #0f172a;
      --muted: #64748b;
      --line: rgba(2, 6, 23, .10);

      --brand-1: #0d6efd;
      --brand-2: #2e58a8;

      --success-1:#16a34a;
      --success-2:#065f46;

      --warn-1:#f59e0b;
      --warn-2:#92400e;

      --danger-1:#ef4444;
      --danger-2:#991b1b;
    }

    .card{
      border-radius: var(--radius-xl);
      border: 1px solid var(--line);
      box-shadow: var(--shadow-soft);
      overflow: hidden;
      background: #fff;
    }
    .card .card-body{ padding: 18px; }
    .card-header-pro{
      padding: 16px 18px;
      border-bottom: 1px solid var(--line);
      background: linear-gradient(180deg, #fff 0%, #fbfdff 100%);
      position: relative;
    }
    .card-header-pro:before{
      content:'';
      position:absolute; inset:0;
      background: radial-gradient(900px 120px at 10% 0%, rgba(13,110,253,.16), transparent 60%);
      pointer-events:none;
    }
    .card-title-pro{
      margin:0;
      font-weight: 1200;
      color: var(--ink);
      letter-spacing:.2px;
      display:flex;
      align-items:center;
      gap:10px;
      position:relative;
      z-index:1;
    }
    .sub-pro{
      color: var(--muted);
      font-weight: 850;
      margin-top: 6px;
      position:relative;
      z-index:1;
    }

    .pill{
      display:inline-flex;
      align-items:center;
      gap:8px;
      padding: 7px 10px;
      border-radius: 999px;
      border: 1px solid rgba(15,23,42,.10);
      background: rgba(248,250,252,.95);
      font-weight: 1100;
      color: var(--ink);
      box-shadow: 0 10px 24px rgba(2,6,23,.08);
      white-space: nowrap;
      position:relative;
      z-index:1;
    }
    .dot{
      width:10px;height:10px;border-radius:999px;background:#94a3b8;
      box-shadow: 0 0 0 3px rgba(148,163,184,.20);
    }
    .dot.ok{ background: var(--success-1); box-shadow: 0 0 0 3px rgba(22,163,74,.20); }
    .dot.warn{ background: var(--warn-1); box-shadow: 0 0 0 3px rgba(245,158,11,.22); }
    .dot.bad{ background: var(--danger-1); box-shadow: 0 0 0 3px rgba(239,68,68,.22); }

    label{
      font-weight: 1000;
      color: #334155;
      margin-bottom: .35rem;
    }
    .form-control, select.form-control, textarea.form-control{
      border-radius: var(--radius-md);
      border: 1px solid rgba(15, 23, 42, .16);
      box-shadow: 0 6px 16px rgba(2, 6, 23, .04);
      transition: transform .14s ease, box-shadow .14s ease, border-color .14s ease;
      min-height: 42px;
      font-weight: 750;
      color: var(--ink);
    }
    .form-control:focus{
      border-color: rgba(13,110,253,.55);
      box-shadow: 0 0 0 .2rem rgba(13,110,253,.18);
      transform: translateY(-1px);
    }
    .readonly-soft{
      background: #f8fafc !important;
      cursor: not-allowed;
      opacity: .98;
    }

    .kpi-wrap{
      display:grid;
      grid-template-columns: repeat(3, minmax(0,1fr));
      gap: 12px;
      margin: 14px 0 4px;
    }
    .kpi{
      border: 1px solid rgba(15,23,42,.10);
      border-radius: var(--radius-lg);
      box-shadow: 0 12px 26px rgba(2,6,23,.07);
      padding: 12px 14px;
      background: #fff;
      position: relative;
      overflow: hidden;
    }
    .kpi:before{
      content:'';
      position:absolute; inset:0;
      background: radial-gradient(900px 120px at 10% 0%, rgba(13,110,253,.12), transparent 60%);
      pointer-events:none;
    }
    .kpi .label{
      color: var(--muted);
      font-weight: 1100;
      font-size: .78rem;
      margin-bottom: 6px;
      display:flex;
      align-items:center;
      gap:8px;
      position: relative;
      z-index: 1;
    }
    .kpi .value{
      color: var(--ink);
      font-weight: 1250;
      font-size: 1.15rem;
      position: relative;
      z-index: 1;
      line-height: 1.15;
    }

    .progress{
      height: 16px;
      border-radius: 999px;
      background: rgba(15,23,42,.08);
      overflow: hidden;
      box-shadow: inset 0 2px 6px rgba(2,6,23,.10);
      margin-bottom: 0;
    }
    .progress-bar{
      font-weight: 1100;
      font-size: .74rem;
      line-height: 16px;
      border-radius: 999px;
      padding: 0 8px;
      white-space: nowrap;
    }
    .bar-ok{ background: linear-gradient(135deg, var(--success-1), var(--success-2)); }
    .bar-warn{ background: linear-gradient(135deg, var(--warn-1), var(--warn-2)); }
    .bar-bad{ background: linear-gradient(135deg, var(--danger-1), var(--danger-2)); }

    .actions-bar{
      display:flex;
      gap:10px;
      flex-wrap: wrap;
      justify-content: center;
      padding-top: 10px;
    }
    .btn-brutal{
      border-radius: 14px !important;
      padding: .62rem 1.05rem !important;
      font-weight: 1100 !important;
      letter-spacing: .2px;
      box-shadow: 0 14px 34px rgba(2, 6, 23, .16);
      transition: transform .16s ease, box-shadow .16s ease, filter .16s ease;
      border: 1px solid rgba(255,255,255,.16);
      display: inline-flex;
      align-items: center;
      gap: 8px;
    }
    .btn-brutal:hover{ transform: translateY(-1px); filter: brightness(1.02); box-shadow: 0 18px 40px rgba(2, 6, 23, .18); }
    .btn-brutal:active{ transform: translateY(0px); box-shadow: 0 12px 28px rgba(2, 6, 23, .16); }
    .btn-primary.btn-brutal{ background: linear-gradient(135deg, var(--brand-1), var(--brand-2)) !important; border-color: transparent !important; }
    .btn-outline-secondary.btn-brutal{ background:#fff !important; border: 1px solid rgba(15,23,42,.14) !important; }

    .table-shell{
      border-radius: var(--radius-lg);
      border: 1px solid rgba(15,23,42,.10);
      overflow: hidden;
      box-shadow: 0 14px 34px rgba(2,6,23,.08);
      background: #fff;
    }
    table.table thead th{
      background: linear-gradient(135deg, rgba(13,110,253,.12), rgba(46,88,168,.08));
      color: var(--ink);
      font-weight: 1200;
      border-bottom: 1px solid rgba(15,23,42,.10) !important;
      vertical-align: middle;
      white-space: nowrap;
      padding-top: 14px;
      padding-bottom: 14px;
    }
    table.table tbody td{
      vertical-align: middle;
      font-weight: 750;
      color: var(--ink);
      border-top: 1px solid rgba(15,23,42,.06);
      background: #fff;
    }
    table.table tbody tr:hover td{
      background: rgba(13,110,253,.03);
    }

    .empty-state{
      padding: 22px 16px;
      text-align:center;
      color: var(--muted);
      background: linear-gradient(180deg, rgba(13,110,253,.04), #fff);
      font-weight: 950;
    }

    @media (max-width: 992px){
      .kpi-wrap{ grid-template-columns: 1fr; }
    }
    @media (max-width: 576px){
      .actions-bar .btn-brutal{ width:100%; justify-content:center; }
    }
  </style>

  <!-- [ Main Content ] start -->
  <div class="pcoded-main-container">
    <div class="pcoded-content">

      <!-- [ breadcrumb ] start -->
      <div class="page-header">
        <div class="page-block">
          <div class="row align-items-center">
            <div class="col-md-12">
              <div class="d-flex justify-content-between align-items-center">
                <h5 class="m-b-10">Proyectos Alcaldías</h5>
                <?php include './admin/include/btn_back.php'; ?>
              </div>
              <ul class="breadcrumb">
                <li class="breadcrumb-item"><a href="index.php"><i class="feather icon-home"></i></a></li>
                <li class="breadcrumb-item"><a href="#!">Seguimiento Proyectos / Detalle Proyectos Alcaldía / Detalle y actualización del proyecto</a></li>
              </ul>
            </div>
          </div>
        </div>
      </div>
      <!-- [ breadcrumb ] end -->

      <div class="row">
        <div class="col-sm-12">

          <!-- ====== CARD DETALLE ====== -->
          <div class="card">
            <div class="card-header-pro">
              <div class="d-flex align-items-start justify-content-between gap-2 flex-wrap">
                <div>
                  <h5 class="card-title-pro">
                    <i data-feather="clipboard"></i>
                    Detalle y actualización del proyecto
                  </h5>
                  <div class="sub-pro">
                    Revisa la información, actualiza estado y registra observaciones del proyecto.
                  </div>
                </div>

                <div class="d-flex align-items-center gap-2 flex-wrap" style="position:relative; z-index:1;">
                  <span class="pill">
                    <i data-feather="hash" width="16" height="16"></i>
                    <span style="opacity:.75;">ID:</span> <?php echo h($proyecto["id"] ?? ''); ?>
                  </span>
                  <span class="pill">
                    <span class="dot <?php echo h($dot); ?>"></span>
                    <?php echo h($estadoActual); ?>
                  </span>
                </div>
              </div>

              <!-- KPIs -->
              <div class="kpi-wrap" style="position:relative; z-index:1;">
                <div class="kpi">
                  <div class="label"><i data-feather="dollar-sign"></i> Total inversión</div>
                  <div class="value" id="kpiValor"><?php echo '$ ' . number_format($valorProyecto, 0, ',', '.'); ?></div>
                </div>
                <div class="kpi">
                  <div class="label"><i data-feather="trending-up"></i> Ejecución física</div>
                  <div class="value"><?php echo $porcEjecInt; ?>%</div>
                </div>
                <div class="kpi">
                  <div class="label"><i data-feather="activity"></i> Ejecución financiera</div>
                  <div class="value"><?php echo $porcFinInt; ?>%</div>
                </div>
              </div>
            </div>

            <div class="card-body">

              <form class="needs-validation" novalidate>
                <div class="row">
                  <div class="form-group col-md-4">
                    <label>Fecha</label>
                    <input type="hidden" name="idProyecto" id="idProyecto" value="<?php echo h($proyecto["id"] ?? '') ?>">
                    <input type="date" class="form-control readonly-soft" id="date" name="date" value="<?php echo h($proyecto["date"] ?? '') ?>" readonly>
                  </div>

                  <div class="form-group col-md-4">
                    <label>Departamento</label>
                    <input type="text" class="form-control readonly-soft" id="departamento" name="departamento" value="<?php echo h($proyecto["departamento"] ?? '') ?>" readonly>
                  </div>

                  <div class="form-group col-md-4">
                    <label>Municipio</label>
                    <input type="text" class="form-control readonly-soft" id="municipio" name="municipio" value="<?php echo h($proyecto["municipio"] ?? '') ?>" readonly>
                    <input type="hidden" class="form-control" id="tbl_municipio_id" name="tbl_municipio_id" value="<?php echo h($proyecto["tbl_municipio_id"] ?? '') ?>" readonly>
                  </div>

                  <div class="form-group col-md-4">
                    <label>Vereda</label>
                    <input type="text" class="form-control readonly-soft" id="vereda" name="vereda" value="<?php echo h(isset($proyecto["vereda"]) ? $proyecto["vereda"] : 'N/A') ?>" readonly>
                  </div>

                  <div class="form-group col-md-8">
                    <label>Objeto del proyecto</label>
                    <input type="text" class="form-control readonly-soft" id="proyecto" name="proyecto" value="<?php echo h($proyecto["proyecto"] ?? '') ?>" readonly>
                  </div>

                  <div class="form-group col-md-6">
                    <label>Secretaría Encargada</label>
                    <input type="text" class="form-control readonly-soft" id="secretaria_text" name="secretaria_text" value="<?php echo h($proyecto["secretaria"] ?? '') ?>" readonly>
                    <input type="hidden" class="form-control" id="tbl_secretarias_id" name="tbl_secretarias_id" value="<?php echo h($proyecto["tbl_secretarias_id"] ?? '') ?>" readonly>
                  </div>

                  <div class="form-group col-md-6">
                    <label>Total Inversión</label>
                    <!-- ✅ Se deja como number para NO tocar backend; solo se muestra formateado con JS (visual). -->
                    <input type="number" class="form-control readonly-soft" id="valor_proyecto" name="valor_proyecto" value="<?php echo h($proyecto["valor_proyecto"] ?? 0) ?>" readonly>
                    <div class="help-muted" style="color:var(--muted);font-weight:800;font-size:.8rem;margin-top:.35rem;">
                      Visual: <span id="valorProyectoCOP">$ 0</span>
                    </div>
                  </div>

                  <div class="form-group col-md-4">
                    <label>Fecha Entrega</label>
                    <input type="date" class="form-control" id="fecha_entrega" name="fecha_entrega" value="<?php echo h($proyecto["fecha_entrega"] ?? '') ?>">
                  </div>

                  <div class="form-group col-md-4">
                    <label>Estado Actual</label>
                    <input type="text" class="form-control readonly-soft" id="estado_actual" name="estado_actual" value="<?php echo h($proyecto["estado"] ?? '') ?>" readonly>
                  </div>

                  <div class="form-group col-md-4">
                    <label>Nuevo Estado Proyecto</label>
                    <select class="form-control" id="estado" name="estado">
                      <option value="Seleccione">Seleccione</option>
                      <option value="En Formulación">En Formulación</option>
                      <option value="Radicado en Banco de Proyectos">Radicado en Banco de Proyectos</option>
                      <option value="Aprobado Banco de Proyectos">Aprobado Banco de Proyectos</option>
                      <option value="Estudios Previos">Estudios Previos</option>
                      <option value="Proceso de Giro">Proceso de Giro</option>
                      <option value="En subsanación por parte del DAF">En subsanación por parte del DAF</option>
                      <option value="En subsanación por parte del Municipio">En subsanación por parte del Municipio</option>
                      <option value="En Contrataciòn">En Contrataciòn</option>
                      <option value="Sin Iniciar">Sin Iniciar</option>
                      <option value="Ejecución">Ejecución</option>
                      <option value="Ejecutado">Ejecutado</option>
                      <option value="Terminado">Terminado</option>
                      <option value="Terminado - Sin Liquidar">Terminado - Sin Liquidar</option>
                      <option value="Terminado - Liquidado">Terminado - Liquidado</option>
                      <option value="Liquidación">Liquidación</option>
                      <option value="Entregado">Entregado</option>
                      <option value="Finalizado">Finalizado</option>
                      <option value="Suspendido">Suspendido</option>
                      <option value="A la espera por parte del Municipio">A la espera por parte del Municipio</option>
                    </select>
                    <div class="help-muted" style="color:var(--muted);font-weight:800;font-size:.8rem;margin-top:.35rem;">
                      Si no seleccionas, se mantiene el estado actual.
                    </div>
                  </div>

                  <div class="form-group col-md-6">
                    <label>Porcentaje de ejecución actual</label>
                    <input type="text" class="form-control readonly-soft" id="porcentaje_ejecucion_actual" name="porcentaje_ejecucion_actual" value="<?php echo h($proyecto["porcentaje_ejecucion"] ?? 0) ?>" readonly>
                    <div class="progress mt-2" title="<?php echo $porcEjecInt; ?>%">
                      <div class="progress-bar <?php echo ($porcEjecInt>=70?'bar-ok':($porcEjecInt>=35?'bar-warn':'bar-bad')); ?>" style="width:<?php echo $porcEjecInt; ?>%">
                        <?php echo $porcEjecInt; ?>%
                      </div>
                    </div>
                  </div>

                  <div class="form-group col-md-6">
                    <label>Nuevo porcentaje de ejecución</label>
                    <input onKeyPress="return soloNumeros(event);" type="text" class="form-control" placeholder="0 a 100" id="porcentaje_ejecucion" name="porcentaje_ejecucion" required inputmode="numeric">
                    <div class="help-muted" style="color:var(--muted);font-weight:800;font-size:.8rem;margin-top:.35rem;">
                      Solo número (sin %). Ej: 25
                    </div>
                  </div>

                  <div class="form-group col-md-6">
                    <label>Porcentaje Ejecución Financiera actual</label>
                    <input type="text" class="form-control readonly-soft" id="porcentaje_financiero_actual" name="porcentaje_financiero_actual" value="<?php echo h($proyecto["porcentaje_financiero"] ?? 0) ?>" readonly>
                    <div class="progress mt-2" title="<?php echo $porcFinInt; ?>%">
                      <div class="progress-bar <?php echo ($porcFinInt>=70?'bar-ok':($porcFinInt>=35?'bar-warn':'bar-bad')); ?>" style="width:<?php echo $porcFinInt; ?>%">
                        <?php echo $porcFinInt; ?>%
                      </div>
                    </div>
                  </div>

                  <div class="form-group col-md-6">
                    <label>Nuevo porcentaje de Ejecución Financiera</label>
                    <input onKeyPress="return soloNumeros(event);" type="text" class="form-control" placeholder="0 a 100" id="porcentaje_financiero" name="porcentaje_financiero" required inputmode="numeric">
                    <div class="help-muted" style="color:var(--muted);font-weight:800;font-size:.8rem;margin-top:.35rem;">
                      Solo número (sin %). Ej: 40
                    </div>
                  </div>

                  <div class="form-group col-md-12">
                    <label>Observaciones</label>
                    <textarea required placeholder="Ingrese observaciones de la obra" class="form-control" id="observaciones" name="observaciones" rows="4"></textarea>
                  </div>
                </div>

                <div class="actions-bar">
                  <button type="button" class="btn btn-outline-secondary btn-brutal" onclick="location.reload();">
                    <i data-feather="refresh-cw"></i> Limpiar
                  </button>

                  <!-- ✅ MISMA FUNCIÓN backend -->
                  <button class="btn btn-primary btn-brutal" type="button" onclick="DETALLE_PROYECTO_ALCALDIA.updatedata();">
                    <i data-feather="save"></i> Actualizar información
                  </button>
                </div>
              </form>

            </div>
          </div>
          <!-- ====== /CARD DETALLE ====== -->

          <!-- ====== CARD OBSERVACIONES ====== -->
          <div class="card mt-3">
            <div class="card-header-pro">
              <h5 class="card-title-pro"><i data-feather="message-square"></i> Historial de observaciones</h5>
              <div class="sub-pro">Registro cronológico de comentarios asociados al proyecto.</div>
            </div>

            <div class="card-body p-0">
              <div class="table-shell">
                <div class="table-responsive">
                  <table class="table table-hover mb-0">
                    <thead>
                      <tr>
                        <th style="width:70%;">Observación</th>
                        <th>Fecha</th>
                      </tr>
                    </thead>
                    <tbody>
                      <?php
                      if (!empty($arrobser)) {
                        $hasRows = false;
                        foreach ($arrobser as $obs) {
                          if (!empty($obs['observaciones'])) {
                            $hasRows = true;
                            echo '<tr>';
                            echo '<td>' . h($obs['observaciones']) . '</td>';
                            echo '<td>' . h($obs['dtcreate'] ?? '') . '</td>';
                            echo '</tr>';
                          }
                        }
                        if(!$hasRows){
                          echo "<tr><td colspan='2' class='empty-state'>No hay observaciones registradas para este proyecto.</td></tr>";
                        }
                      } else {
                        echo "<tr><td colspan='2' class='empty-state'>No hay observaciones registradas para este proyecto.</td></tr>";
                      }
                      ?>
                    </tbody>
                  </table>
                </div>
              </div>
            </div>

          </div>
          <!-- ====== /CARD OBSERVACIONES ====== -->

        </div>
      </div>

    </div>
  </div>

  <?php include 'admin/include/gerenic_script.php'; ?>

  <!-- Required Js -->
  <script src="assets/js/vendor-all.min.js"></script>
  <script src="assets/js/plugins/bootstrap.min.js"></script>
  <script src="assets/js/pcoded.min.js"></script>

  <script type="text/javascript" src="admin/js/detalle_proyectos_alcaldia.js"></script>

  <script>
    // ✅ Solo UI: formatea COP del total inversión sin cambiar el input number (backend intacto)
    (function(){
      function formatCOP(n){
        try{
          const x = Math.round(Number(n) || 0);
          return '$ ' + x.toLocaleString('es-CO');
        }catch(e){ return '$ 0'; }
      }

      document.addEventListener('DOMContentLoaded', function(){
        if (window.feather) window.feather.replace({ width: 16, height: 16 });

        const el = document.getElementById('valor_proyecto');
        const out = document.getElementById('valorProyectoCOP');
        const kpi = document.getElementById('kpiValor');

        if(el && out){
          out.textContent = formatCOP(el.value);
        }
        if(kpi && el){
          kpi.textContent = formatCOP(el.value);
        }

        // Endurece %: si pegan texto raro
        ['porcentaje_ejecucion','porcentaje_financiero'].forEach(function(id){
          const x = document.getElementById(id);
          if(!x) return;
          x.addEventListener('input', function(){
            this.value = (this.value || '').toString().replace(/\D+/g,'');
            const n = parseInt(this.value || '0', 10);
            if(n > 100) this.value = '100';
          });
        });
      });
    })();
  </script>
</body>
</html>
