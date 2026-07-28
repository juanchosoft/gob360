<!-- ============================================================ -->
<!-- HACIENDA - Ejecución y Mapa                                  -->
<!-- ============================================================ -->
<div class="row mt-4" id="haciendaSectionWrap">
  <div class="col-12">
    <div class="panel-card" style="overflow:visible;">
      <div class="panel-title">
        <h6><i class="bi bi-shield-check"></i> Ejecución Secretaría de Hacienda</h6>
        <small>GOA · Ingresos · Tesorería · Automotores</small>
      </div>

      <style>
        .hz-group{ display:flex; flex-wrap:wrap; gap:10px; margin-bottom:14px; }
        .hz-group select{ background:rgba(255,255,255,.06); border:1px solid rgba(255,255,255,.12); border-radius:14px; padding:10px 14px; color:#fff; font-weight:700; font-size:13px; }
        .hz-group select option{ background:#1e293b; color:#fff; }
        .hz-kpi-grid{ display:grid; grid-template-columns:repeat(auto-fit,minmax(150px,1fr)); gap:10px; margin-bottom:14px; }
        .hz-kpi{ background:rgba(255,255,255,.04); border:1px solid rgba(255,255,255,.08); border-radius:16px; padding:14px; }
        .hz-kpi small{ color:var(--muted); font-size:11px; font-weight:800; text-transform:uppercase; letter-spacing:.3px; }
        .hz-kpi strong{ display:block; color:#fff; font-size:22px; font-weight:950; margin-top:4px; }
        .hz-kpi .sub{ color:var(--muted2); font-size:12px; }
        .hz-map-wrap{ background:rgba(255,255,255,.04); border:1px solid rgba(255,255,255,.08); border-radius:22px; padding:16px; margin-top:14px; }
        #contenido-mapa svg{ width:100%; height:auto; max-height:480px; }
        #contenido-mapa .municipios{ cursor:pointer; transition:opacity .15s; }
        #contenido-mapa .municipios:hover{ opacity:.75; }
      </style>

      <!-- Filtros -->
      <div class="hz-group">
        <select id="grupoHaciendaDash" onchange="cambiarGrupoHaciendaDash(this.value)" style="min-width:220px;">
          <option value="GOA" <?= $accionHacienda === 'GOA - Aprehensiones' || strpos($accionHacienda, 'GOA') === 0 ? 'selected' : '' ?>>GRUPO OPERATIVO ANTICONTRABANDO</option>
          <option value="INGRESOS" <?= strpos($accionHacienda, 'Capacitacion') === 0 ? 'selected' : '' ?>>DIRECCION DE INGRESOS</option>
          <option value="TESORERIA" <?= strpos($accionHacienda, 'Recaudo') === 0 || strpos($accionHacienda, 'Impuesto Estampillas') === 0 ? 'selected' : '' ?>>TESORERIA</option>
          <option value="AUTOMOTORES" <?= strpos($accionHacienda, 'Impuesto Vehicular') === 0 ? 'selected' : '' ?>>IMPUESTO UNIFICADO DE AUTOMOTORES</option>
        </select>

        <select id="accionHaciendaDash" onchange="cargarHaciendaAjax(this.value)" style="min-width:280px;">
          <?php if (strpos($accionHacienda, 'GOA') === 0 || $accionHacienda === 'GOA - Aprehensiones'): ?>
          <option value="GOA - Aprehensiones" <?= $accionHacienda === 'GOA - Aprehensiones' ? 'selected' : '' ?>>GOA - Aprehensiones (Todas)</option>
          <option value="GOA Aprehensiones de Licores" <?= $accionHacienda === 'GOA Aprehensiones de Licores' ? 'selected' : '' ?>>GOA Aprehensiones de Licores</option>
          <option value="GOA Aprehensión de Cigarrillos" <?= $accionHacienda === 'GOA Aprehensión de Cigarrillos' ? 'selected' : '' ?>>GOA Aprehensión de Cigarrillos</option>
          <option value="GOA Aprehensión de Cervezas" <?= $accionHacienda === 'GOA Aprehensión de Cervezas' ? 'selected' : '' ?>>GOA Aprehensión de Cervezas</option>
          <option value="GOA Aprehensión de Tabaco y Otros" <?= $accionHacienda === 'GOA Aprehensión de Tabaco y Otros' ? 'selected' : '' ?>>GOA Aprehensión de Tabaco y Otros</option>
          <option value="Registro de Visitas a Establecimientos Comerciales" <?= $accionHacienda === 'Registro de Visitas a Establecimientos Comerciales' ? 'selected' : '' ?>>Registro de Visitas</option>
          <option value="GOA Juridico" <?= $accionHacienda === 'GOA Juridico' ? 'selected' : '' ?>>GOA Jurídico</option>
          <?php elseif (strpos($accionHacienda, 'Capacitacion') === 0): ?>
          <option value="Capacitacion Fiscal y Financiera" selected>Capacitación Fiscal y Financiera</option>
          <?php elseif (strpos($accionHacienda, 'Recaudo') === 0 || strpos($accionHacienda, 'Impuesto Estampillas') === 0): ?>
          <option value="Recaudo del impuesto al consumo" <?= $accionHacienda === 'Recaudo del impuesto al consumo' ? 'selected' : '' ?>>Recaudo del impuesto al consumo</option>
          <option value="Recaudo del impuesto de registro" <?= $accionHacienda === 'Recaudo del impuesto de registro' ? 'selected' : '' ?>>Recaudo del impuesto de registro</option>
          <option value="Impuesto Estampillas Recaudado" <?= $accionHacienda === 'Impuesto Estampillas Recaudado' ? 'selected' : '' ?>>Impuesto Estampillas Recaudado</option>
          <?php elseif (strpos($accionHacienda, 'Impuesto Vehicular') === 0): ?>
          <option value="Impuesto Vehicular Recaudado" selected>Impuesto Vehicular Recaudado</option>
          <?php endif; ?>
        </select>
      </div>

      <!-- KPIs -->
      <div class="hz-kpi-grid">
        <?php if ($accionHacienda === 'GOA - Aprehensiones'): ?>
        <div class="hz-kpi"><small>Cantidad Total Aprehendida</small><strong><?= number_format($GOATotal_cantidad_aprehendida, 0, ',', '.') ?></strong><span class="sub">Unidades</span></div>
        <div class="hz-kpi"><small>Valor Total Avalúo Comercial</small><strong>$<?= number_format($GOATotal_avaluo_comercial, 0, ',', '.') ?></strong><span class="sub">COP</span></div>
        <div class="hz-kpi" style="border-left-color:#0295F0;"><small>Licores</small><strong><?= number_format($GOALicores_cantidad, 0, ',', '.') ?></strong><span class="sub">$<?= number_format($GOALicores_valor, 0, ',', '.') ?></span></div>
        <div class="hz-kpi" style="border-left-color:#b8a2ff;"><small>Cigarrillos</small><strong><?= number_format($GOACigarrillos_cantidad, 0, ',', '.') ?></strong><span class="sub">$<?= number_format($GOACigarrillos_valor, 0, ',', '.') ?></span></div>
        <div class="hz-kpi" style="border-left-color:#f1c40f;"><small>Cervezas</small><strong><?= number_format($GOACervezas_cantidad, 0, ',', '.') ?></strong><span class="sub">$<?= number_format($GOACervezas_valor, 0, ',', '.') ?></span></div>
        <div class="hz-kpi" style="border-left-color:#e67e22;"><small>Tabaco y Otros</small><strong><?= number_format($GOATabaco_cantidad, 0, ',', '.') ?></strong><span class="sub">$<?= number_format($GOATabaco_valor, 0, ',', '.') ?></span></div>
        <?php elseif ($accionHacienda === 'GOA Aprehensiones de Licores'): ?>
        <div class="hz-kpi" style="border-left-color:#0295F0;"><small>Licores - Cantidad</small><strong><?= number_format($GOALicores_cantidad, 0, ',', '.') ?></strong></div>
        <div class="hz-kpi" style="border-left-color:#0295F0;"><small>Licores - Valor</small><strong>$<?= number_format($GOALicores_valor, 0, ',', '.') ?></strong></div>
        <?php elseif ($accionHacienda === 'GOA Aprehensión de Cigarrillos'): ?>
        <div class="hz-kpi" style="border-left-color:#b8a2ff;"><small>Cigarrillos - Cantidad</small><strong><?= number_format($GOACigarrillos_cantidad, 0, ',', '.') ?></strong></div>
        <div class="hz-kpi" style="border-left-color:#b8a2ff;"><small>Cigarrillos - Valor</small><strong>$<?= number_format($GOACigarrillos_valor, 0, ',', '.') ?></strong></div>
        <?php elseif ($accionHacienda === 'GOA Aprehensión de Cervezas'): ?>
        <div class="hz-kpi" style="border-left-color:#f1c40f;"><small>Cervezas - Cantidad</small><strong><?= number_format($GOACervezas_cantidad, 0, ',', '.') ?></strong></div>
        <div class="hz-kpi" style="border-left-color:#f1c40f;"><small>Cervezas - Valor</small><strong>$<?= number_format($GOACervezas_valor, 0, ',', '.') ?></strong></div>
        <?php elseif ($accionHacienda === 'GOA Aprehensión de Tabaco y Otros'): ?>
        <div class="hz-kpi" style="border-left-color:#e67e22;"><small>Tabaco - Cantidad</small><strong><?= number_format($GOATabaco_cantidad, 0, ',', '.') ?></strong></div>
        <div class="hz-kpi" style="border-left-color:#e67e22;"><small>Tabaco - Valor</small><strong>$<?= number_format($GOATabaco_valor, 0, ',', '.') ?></strong></div>
        <?php elseif ($accionHacienda === 'Registro de Visitas a Establecimientos Comerciales'): ?>
        <div class="hz-kpi" style="border-left-color:#2b80e2;"><small>Visitas a Establecimientos</small><strong><?= number_format($GOAcantidad_visitas_al_municipio, 0, ',', '.') ?></strong></div>
        <?php elseif ($accionHacienda === 'GOA Juridico'): ?>
        <div class="hz-kpi" style="border-left-color:#1a6b4a;"><small>Custodia - Valor Total</small><strong>$<?= number_format($goaJuridicoCustodiaValorTotal, 0, ',', '.') ?></strong></div>
        <div class="hz-kpi"><small>Custodia - Procesos</small><strong><?= number_format($goaJuridicoCustodiaCantidadProcesos, 0, ',', '.') ?></strong></div>
        <div class="hz-kpi"><small>Custodia - Unidades</small><strong><?= number_format($goaJuridicoCustodiaCantidadUnidades, 0, ',', '.') ?></strong></div>
        <div class="hz-kpi" style="border-left-color:#dc3545;"><small>Destrucción - Unidades</small><strong><?= number_format($goaJuridicoDestruccionCantidadUnidades, 0, ',', '.') ?></strong></div>
        <div class="hz-kpi"><small>Destrucción - Valor Total</small><strong>$<?= number_format($goaJuridicoDestruccionValorTotal, 0, ',', '.') ?></strong></div>
        <?php elseif ($accionHacienda === 'Impuesto Vehicular Recaudado'): ?>
        <div class="hz-kpi" style="border-left-color:#4169E1;"><small>Total Recaudo + Trámite</small><strong>$<?= number_format($vehicular_total_recaudo_y_tramite, 0, ',', '.') ?></strong></div>
        <div class="hz-kpi"><small>Valor Recaudo</small><strong>$<?= number_format($vehicular_total_recaudo, 0, ',', '.') ?></strong></div>
        <div class="hz-kpi"><small>Valor Trámites</small><strong>$<?= number_format($vehicular_total_tramites, 0, ',', '.') ?></strong></div>
        <div class="hz-kpi"><small>Operativos</small><strong><?= number_format($vehicular_total_operativos, 0, ',', '.') ?></strong></div>
        <div class="hz-kpi"><small>Emplazados</small><strong><?= number_format($vehicular_total_emplazados, 0, ',', '.') ?></strong></div>
        <div class="hz-kpi"><small>Placas Consultadas</small><strong><?= number_format($vehicular_total_placas_consultadas, 0, ',', '.') ?></strong></div>
        <div class="hz-kpi"><small>Campañas Sensibilización</small><strong><?= number_format($vehicular_total_campanas_sensibilizacion, 0, ',', '.') ?></strong></div>
        <?php elseif (in_array($accionHacienda, ['Recaudo del impuesto al consumo', 'Recaudo del impuesto de registro', 'Impuesto Estampillas Recaudado'])): ?>
        <div class="hz-kpi"><small>Datos de <?= htmlspecialchars($accionHacienda) ?></small><strong>Ver detalle</strong><span class="sub">Selecciona municipio en el mapa</span></div>
        <?php elseif ($accionHacienda === 'Capacitacion Fiscal y Financiera'): ?>
        <div class="hz-kpi"><small>Capacitación Fiscal y Financiera</small><strong>Activo</strong><span class="sub">Selecciona municipio en el mapa</span></div>
        <?php endif; ?>
      </div>

      <!-- Mapa SVG -->
      <div class="hz-map-wrap">
        <div id="contenido-mapa" class="cuerpoMapa w-100">
            <?php if (!empty($santander)): ?>
          <svg xmlns="http://www.w3.org/2000/svg" viewBox="40 40 1000 1200" style="width:100%;height:auto;">
            <?php foreach ($santander as $key => $value): ?>
            <path class="municipios mapaClick" data-mun="<?= $value['codigo_muncipio'] ?? '' ?>" data-dep="<?= $value['codigo_departamento'] ?? '' ?>" d="<?= $value['d'] ?? '' ?>" title="<?= htmlspecialchars($value['municipio'] ?? '') ?>" fill="<?= $value['color'] ?? '#ccc' ?>" stroke="#fff" stroke-width=".5"/>
            <?php endforeach; ?>
            <?php require_once 'nombres_mapa_santander.php'; ?>
          </svg>
          <?php else: ?>
          <p style="color:var(--muted2);text-align:center;padding:40px 0;">No hay datos cartográficos disponibles.</p>
          <?php endif; ?>
        </div>
      </div>

      <?php
      $accionesGOAAprehLeyenda = ['GOA Aprehensiones de Licores','GOA Aprehensión de Cigarrillos','GOA Aprehensión de Cervezas','GOA Aprehensión de Tabaco y Otros','GOA - Aprehensiones'];
      $esLeyendaAprehensiones  = in_array($accionHacienda, $accionesGOAAprehLeyenda);
      $esLeyendaVisitas        = ($accionHacienda === 'Registro de Visitas a Establecimientos Comerciales');
      if ($esLeyendaAprehensiones || $esLeyendaVisitas):
      ?>
      <div style="margin-top:10px;background:rgba(255,255,255,.04);border:1px solid rgba(255,255,255,.08);border-radius:16px;padding:14px;">
        <p style="font-weight:800;font-size:0.78rem;margin:0 0 8px 0;color:#fff;">
          <?= $esLeyendaVisitas ? 'Escala – Visitas a Establecimientos' : 'Escala – Aprehensiones GOA' ?>
        </p>
        <table style="font-size:0.75rem;width:100%;border-collapse:collapse;">
          <thead><tr><th style="text-align:left;padding:4px 8px;color:var(--muted);">Color</th><th style="text-align:left;padding:4px 8px;color:var(--muted);">Rango</th></tr></thead>
          <tbody>
            <tr><td style="padding:4px 8px;"><span style="display:inline-block;width:18px;height:18px;background:#EEF2F7;border:1px solid #555;border-radius:3px;"></span></td><td style="padding:4px 8px;color:#fff;">Sin datos (0)</td></tr>
            <tr><td style="padding:4px 8px;"><span style="display:inline-block;width:18px;height:18px;background:#E53935;border-radius:3px;"></span></td><td style="padding:4px 8px;color:#fff;"><?= $esLeyendaVisitas ? '1 – 28' : '1 – 2' ?></td></tr>
            <tr><td style="padding:4px 8px;"><span style="display:inline-block;width:18px;height:18px;background:#FB8C00;border-radius:3px;"></span></td><td style="padding:4px 8px;color:#fff;"><?= $esLeyendaVisitas ? '29 – 56' : '3 – 4' ?></td></tr>
            <tr><td style="padding:4px 8px;"><span style="display:inline-block;width:18px;height:18px;background:#1E66F5;border-radius:3px;"></span></td><td style="padding:4px 8px;color:#fff;"><?= $esLeyendaVisitas ? '57 – 84' : '5 – 6' ?></td></tr>
            <tr><td style="padding:4px 8px;"><span style="display:inline-block;width:18px;height:18px;background:#2E7D32;border-radius:3px;"></span></td><td style="padding:4px 8px;color:#fff;"><?= $esLeyendaVisitas ? '85 o más' : '7 o más' ?></td></tr>
          </tbody>
        </table>
      </div>
      <?php endif; ?>

    </div>
  </div>
</div>

<script>
function cargarHaciendaAjax(accion){
  var wrap = document.getElementById('haciendaSectionWrap');
  if(wrap) wrap.style.opacity = '.4';
  fetch('?accion=' + encodeURIComponent(accion) + '&ajax=hacienda')
    .then(function(r){ return r.text(); })
    .then(function(html){
      var nuevo = document.createElement('div');
      nuevo.innerHTML = html;
      var nuevoContenido = nuevo.querySelector('#haciendaSectionWrap');
      if(nuevoContenido && wrap){
        wrap.parentNode.replaceChild(nuevoContenido, wrap);
      } else {
        window.location = '?accion=' + encodeURIComponent(accion);
      }
    })
    .catch(function(){
      window.location = '?accion=' + encodeURIComponent(accion);
    });
}

function cambiarGrupoHaciendaDash(grupo){
  var sel = document.getElementById('accionHaciendaDash');
  sel.innerHTML = '';
  if(grupo === 'GOA'){
    sel.innerHTML = '<option value="GOA - Aprehensiones">GOA - Aprehensiones (Todas)</option><option value="GOA Aprehensiones de Licores">GOA Aprehensiones de Licores</option><option value="GOA Aprehensión de Cigarrillos">GOA Aprehensión de Cigarrillos</option><option value="GOA Aprehensión de Cervezas">GOA Aprehensión de Cervezas</option><option value="GOA Aprehensión de Tabaco y Otros">GOA Aprehensión de Tabaco y Otros</option><option value="Registro de Visitas a Establecimientos Comerciales">Registro de Visitas</option><option value="GOA Juridico">GOA Jurídico</option>';
  } else if(grupo === 'INGRESOS'){
    sel.innerHTML = '<option value="Capacitacion Fiscal y Financiera">Capacitación Fiscal y Financiera</option>';
  } else if(grupo === 'TESORERIA'){
    sel.innerHTML = '<option value="Recaudo del impuesto al consumo">Recaudo del impuesto al consumo</option><option value="Recaudo del impuesto de registro">Recaudo del impuesto de registro</option><option value="Impuesto Estampillas Recaudado">Impuesto Estampillas Recaudado</option>';
  } else if(grupo === 'AUTOMOTORES'){
    sel.innerHTML = '<option value="Impuesto Vehicular Recaudado">Impuesto Vehicular Recaudado</option>';
  }
  cargarHaciendaAjax(sel.value);
}

document.addEventListener('click', function(e){
  var el = e.target.closest('#contenido-mapa .mapaClick');
  if(!el) return;
  var mun = el.getAttribute('data-mun');
  var dep = el.getAttribute('data-dep');
  var accion = document.getElementById('accionHaciendaDash').value;
  if(mun && dep){
    window.open('municipios_secretaria_informacion_hacienda.php?mun=' + mun + '&dep=' + dep + '&accion=' + encodeURIComponent(accion), '_blank');
  }
});
</script>
