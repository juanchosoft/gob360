<?php
    include './admin/include/head.php';
    require './admin/include/generic_classes.php';

    // Solo Admin, Gobernador y Secretaría de Interior (id=2)
    $userType = SessionData::getUserType();
    $secretariaId = SessionData::getSecretaria();
    $isAdmin = ($userType === Util::Administrador() || $userType === Util::SuperAdministrador() || $userType === Util::Gobernador());
    if (!$isAdmin && $secretariaId != Util::getSecretariaIdInterior()) {
        header('Location: dashboard.php');
        exit;
    }
?>
<!-- ✅ Highcharts LOCAL (orden obligatorio) -->
<script src="assets/js/highcharts/highcharts.js"></script>
<script src="assets/js/highcharts/highcharts-more.js"></script>

<!-- ✅ módulos -->
<script src="assets/js/highcharts/modules/solid-gauge.js"></script>
<script src="assets/js/highcharts/modules/exporting.js"></script>
<script src="assets/js/highcharts/modules/export-data.js"></script>
<script src="assets/js/highcharts/modules/accessibility.js"></script>
  <!-- jQuery (tu versión) -->
  <script src="https://code.jquery.com/jquery-3.7.1.js"></script>

  <!-- Popper -->
  <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.3/umd/popper.min.js"></script>

  <!-- Icons -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">

  <link rel="stylesheet" href="assets/css/interior_dash.css" />
  <link rel="stylesheet" href="assets/css/dashboard_interior_seguridad_gob360.css" />
  
</head>

<body class="gob360-security-dashboard">
  <!-- loader (intacto) -->
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

              <div class="page-header">
                <div class="page-block">
                  <div class="row align-items-center">
                    <div class="col-md-12">
                      <div class="d-flex justify-content-between align-items-center">
                        <div>
                          <h5 class="m-b-10 title">
                            <i class="feather icon-shield"></i>
                            Boletín Estratégico de Seguridad
                          </h5>
                          <p class="g360-page-subtitle">
                            Comparativo anual, factores de atención y análisis territorial.
                          </p>
                        </div>
                        <?php include './admin/include/btn_back.php'; ?>
                      </div>
                      <ul class="breadcrumb">
                        <li class="breadcrumb-item title"><a href="index.html"><i class="feather icon-home"></i></a></li>
                        <li class="breadcrumb-item title"><a href="#!">Información Secretaría del Interior</a></li>
                        <li class="breadcrumb-item title"><a href="#!">Dirección de seguridad y convivencia</a></li>
                      </ul>
                    </div>
                  </div>
                </div>
              </div>

              <!-- HERO -->
              <section class="g360-security-dashboard-hero nombres" aria-label="Boletín Estratégico de Seguridad">
                <div class="g360-security-dashboard-hero__grid">

                  <aside class="g360-security-dashboard-brand">
                    <span class="g360-security-dashboard-brand__eyebrow">
                      Plataforma institucional
                    </span>

                    <img
                      src="assets/img/gob360l.png"
                      alt="Logo GOB360"
                      class="g360-security-dashboard-brand__logo"
                    >

                    <span class="g360-security-dashboard-brand__caption">
                      Gestión pública inteligente y territorial
                    </span>

                    <div class="g360-security-dashboard-brand__status">
                      <span></span>
                      Boletín activo
                    </div>
                  </aside>

                  <div class="g360-security-dashboard-hero__content">
                    <div class="g360-security-dashboard-hero__eyebrow">
                      <i class="bi bi-shield-check"></i>
                      Secretaría del Interior
                    </div>

                    <h1 class="g360-security-dashboard-hero__title">
                      Boletín Estratégico de Seguridad
                    </h1>

                    <p class="g360-security-dashboard-hero__description">
                      Comparativo anual por jurisdicción, lectura ejecutiva de
                      indicadores priorizados, variaciones por delito y factores
                      de atención gubernamental.
                    </p>

                    <div class="g360-security-dashboard-meta">
                      <span class="g360-meta-pill" id="hero_boletin_no" style="display:none">
                        <i class="bi bi-hash"></i>
                        Boletín No. <strong id="hero_boletin_no_txt"></strong>
                      </span>

                      <span class="g360-meta-pill" id="hero_boletin_fecha" style="display:none">
                        <i class="bi bi-calendar3"></i>
                        <strong id="hero_boletin_fecha_txt"></strong>
                      </span>

                      <span class="g360-meta-pill" id="hero_fecha_cierre" style="display:none">
                        <i class="bi bi-calendar2-check"></i>
                        <strong id="hero_fecha_txt"></strong>
                      </span>

                      <span class="g360-meta-pill" id="hero_fuente" style="display:none">
                        <i class="bi bi-database-check"></i>
                        <strong id="hero_fuente_txt"></strong>
                      </span>
                    </div>

                    <div class="g360-security-dashboard-actions">
                      <a
                        href="admin/ajax/dash_interior_pdf.php"
                        target="_blank"
                        rel="noopener noreferrer"
                        class="g360-pdf-button"
                        title="Descargar Boletín PDF"
                        id="pdf_download_link"
                      >
                        <i class="bi bi-file-earmark-pdf-fill"></i>
                        Descargar PDF
                      </a>

                      <span class="g360-year-chip" id="chip_year_1">
                        <span class="dot dot-2025"></span>
                        2025
                      </span>

                      <span class="g360-year-chip" id="chip_year_2">
                        <span class="dot dot-2026"></span>
                        2026
                      </span>
                    </div>

                    <div class="g360-security-dashboard-features" aria-hidden="true">
                      <article>
                        <i class="bi bi-bar-chart-line"></i>
                        <span>8 comparativos</span>
                      </article>

                      <article>
                        <i class="bi bi-arrow-left-right"></i>
                        <span>Variación anual</span>
                      </article>

                      <article>
                        <i class="bi bi-speedometer2"></i>
                        <span>Factores críticos</span>
                      </article>

                      <article>
                        <i class="bi bi-file-earmark-pdf"></i>
                        <span>Reporte oficial</span>
                      </article>
                    </div>
                  </div>

                </div>
              </section>

              <!-- ✅ GRID 8 GRAFICOS -->
              <section class="g360-dashboard-section g360-dashboard-section--charts">
                <header class="g360-dashboard-section__header">
                  <div class="g360-dashboard-section__heading">
                    <span class="g360-dashboard-section__icon">
                      <i class="bi bi-bar-chart-line-fill"></i>
                    </span>

                    <div>
                      <span class="g360-dashboard-section__eyebrow">
                        Analítica comparativa
                      </span>
                      <h2>Comportamiento de seguridad por jurisdicción</h2>
                      <p>
                        Cada panel compara los dos periodos configurados y presenta
                        la diferencia absoluta y porcentual de cada indicador.
                      </p>
                    </div>
                  </div>

                  <span class="g360-dashboard-section__badge">
                    <i class="bi bi-grid-3x3-gap"></i>
                    8 paneles
                  </span>
                </header>

                <div class="boletin-grid nombres">

                <!-- 1 -->
                <div class="boletin-card">
                  <div class="boletin-head">
                    <div class="boletin-head-left">
                      <div class="boletin-num">1</div>
                      <div style="min-width:0">
                        <p class="boletin-title">Santander Político</p>
                        <p class="boletin-sub">Indicadores priorizados por comportamiento anual</p>
                      </div>
                    </div>
                    <div class="boletin-head-right">
                      <span class="mini-chip"><span class="dot dot-2025"></span> <span class="y1">2025</span></span>
                      <span class="mini-chip"><span class="dot dot-2026"></span> <span class="y2">2026</span></span>
                    </div>
                  </div>
                  <div class="boletin-body">
                    <div class="chart-wrap"><div id="chart_santander_politico" style="height:320px;"></div>
                    <div class="kpi-grid" id="kpis_santander_politico"></div></div>
                  </div>
                </div>

                <!-- 2 -->
                <div class="boletin-card">
                  <div class="boletin-head">
                    <div class="boletin-head-left">
                      <div class="boletin-num">2</div>
                      <div style="min-width:0">
                        <p class="boletin-title">Santander</p>
                        <p class="boletin-sub">Resumen departamental comparativo</p>
                      </div>
                    </div>
                    <div class="boletin-head-right">
                      <span class="mini-chip"><span class="dot dot-2025"></span> <span class="y1">2025</span></span>
                      <span class="mini-chip"><span class="dot dot-2026"></span> <span class="y2">2026</span></span>
                    </div>
                  </div>
                  <div class="boletin-body">
                    <div class="chart-wrap"><div id="chart_santander" style="height:320px;"></div>
                    <div class="kpi-grid" id="kpis_santander"></div></div>
                    <div id="factor_santander"></div>
                  </div>
                </div>

                <!-- 3 -->
                <div class="boletin-card">
                  <div class="boletin-head">
                    <div class="boletin-head-left">
                      <div class="boletin-num">3</div>
                      <div style="min-width:0">
                        <p class="boletin-title">Metropolitana de B/Manga</p>
                        <p class="boletin-sub">Comportamiento en área metropolitana</p>
                      </div>
                    </div>
                    <div class="boletin-head-right">
                      <span class="mini-chip"><span class="dot dot-2025"></span> <span class="y1">2025</span></span>
                      <span class="mini-chip"><span class="dot dot-2026"></span> <span class="y2">2026</span></span>
                    </div>
                  </div>
                  <div class="boletin-body">
                    <div class="chart-wrap"><div id="chart_metro" style="height:320px;"></div>
                    <div class="kpi-grid" id="kpis_metro"></div></div>
                    <div id="factor_metro"></div>
                  </div>
                </div>

                <!-- 4 -->
                <div class="boletin-card">
                  <div class="boletin-head">
                    <div class="boletin-head-left">
                      <div class="boletin-num">4</div>
                      <div style="min-width:0">
                        <p class="boletin-title">Bucaramanga</p>
                        <p class="boletin-sub">Indicadores ciudad capital</p>
                      </div>
                    </div>
                    <div class="boletin-head-right">
                      <span class="mini-chip"><span class="dot dot-2025"></span> <span class="y1">2025</span></span>
                      <span class="mini-chip"><span class="dot dot-2026"></span> <span class="y2">2026</span></span>
                    </div>
                  </div>
                  <div class="boletin-body">
                    <div class="chart-wrap"><div id="chart_bucaramanga" style="height:320px;"></div>
                    <div class="kpi-grid" id="kpis_bucaramanga"></div></div>
                    <div id="factor_bucaramanga"></div>
                  </div>
                </div>

                <!-- 5 -->
                <div class="boletin-card">
                  <div class="boletin-head">
                    <div class="boletin-head-left">
                      <div class="boletin-num">5</div>
                      <div style="min-width:0">
                        <p class="boletin-title">Magdalena Medio Santandereano</p>
                        <p class="boletin-sub">Comparativo regional</p>
                      </div>
                    </div>
                    <div class="boletin-head-right">
                      <span class="mini-chip"><span class="dot dot-2025"></span> <span class="y1">2025</span></span>
                      <span class="mini-chip"><span class="dot dot-2026"></span> <span class="y2">2026</span></span>
                    </div>
                  </div>
                  <div class="boletin-body">
                    <div class="chart-wrap"><div id="chart_magdalena" style="height:320px;"></div>
                    <div class="kpi-grid" id="kpis_magdalena"></div></div>
                    <div id="factor_magdalena"></div>
                  </div>
                </div>

                <!-- 6 -->
                <div class="boletin-card">
                  <div class="boletin-head">
                    <div class="boletin-head-left">
                      <div class="boletin-num">6</div>
                      <div style="min-width:0">
                        <p class="boletin-title">Barrancabermeja</p>
                        <p class="boletin-sub">Comportamiento local</p>
                      </div>
                    </div>
                    <div class="boletin-head-right">
                      <span class="mini-chip"><span class="dot dot-2025"></span> <span class="y1">2025</span></span>
                      <span class="mini-chip"><span class="dot dot-2026"></span> <span class="y2">2026</span></span>
                    </div>
                  </div>
                  <div class="boletin-body">
                    <div class="chart-wrap"><div id="chart_barranca" style="height:320px;"></div>
                    <div class="kpi-grid" id="kpis_barranca"></div></div>
                    <div id="factor_barranca"></div>
                  </div>
                </div>

                <!-- 7 -->
                <div class="boletin-card">
                  <div class="boletin-head">
                    <div class="boletin-head-left">
                      <div class="boletin-num">7</div>
                      <div style="min-width:0">
                        <p class="boletin-title">Sicariato</p>
                        <p class="boletin-sub">Distribución por jurisdicción</p>
                      </div>
                    </div>
                    <div class="boletin-head-right">
                      <span class="mini-chip"><span class="dot dot-2025"></span> <span class="y1">2025</span></span>
                      <span class="mini-chip"><span class="dot dot-2026"></span> <span class="y2">2026</span></span>
                    </div>
                  </div>
                  <div class="boletin-body">
                    <div class="chart-wrap"><div id="chart_sicariato" style="height:320px;"></div>
                    <div class="kpi-grid" id="kpis_sicariato"></div></div>
                    <div id="factor_sicariato"></div>
                  </div>
                </div>

                <!-- 8 -->
                <div class="boletin-card">
                  <div class="boletin-head">
                    <div class="boletin-head-left">
                      <div class="boletin-num">8</div>
                      <div style="min-width:0">
                        <p class="boletin-title">Intolerancia Social</p>
                        <p class="boletin-sub">Distribución por jurisdicción</p>
                      </div>
                    </div>
                    <div class="boletin-head-right">
                      <span class="mini-chip"><span class="dot dot-2025"></span> <span class="y1">2025</span></span>
                      <span class="mini-chip"><span class="dot dot-2026"></span> <span class="y2">2026</span></span>
                    </div>
                  </div>
                  <div class="boletin-body">
                    <div class="chart-wrap"><div id="chart_intolerancia" style="height:320px;"></div>
                    <div class="kpi-grid" id="kpis_intolerancia"></div></div>
                    <div id="factor_intolerancia"></div>
                  </div>
                </div>

              </div><!-- /boletin-grid -->
              </section>

              <!-- ✅ FACTORES -->
              <section class="factores-wrap nombres g360-dashboard-section g360-dashboard-section--factors" id="factores_atencion">
                <div class="factores-head">
                  <div>
                      <span class="g360-dashboard-section__eyebrow">
                        Alertas estratégicas
                      </span>
                      <h3>Factores de Atención Gubernamental</h3>
                      <p class="g360-factors-description">
                        Medición de factores críticos, municipios sin homicidios,
                        tasa departamental y observaciones de seguimiento.
                      </p>
                    </div>
                  <div class="factores-meta">
                    <div class="factores-badge" id="meta_fecha"><i class="bi bi-calendar2-check"></i> Fecha cierre: <b>--</b></div>
                    <div class="factores-badge" id="meta_fuente"><i class="bi bi-database-check"></i> Fuente: <b>--</b></div>
                  </div>
                </div>

                <div class="factores-grid">
                  <div class="factor-card">
                    <div class="factor-inner">
                      <div class="factor-gauge"><div id="gauge_sicariato" style="height:220px;"></div></div>
                      <div class="factor-text" id="txt_gauge_sicariato"></div>
                    </div>
                  </div>

                  <div class="factor-card">
                    <div class="factor-inner">
                      <div class="factor-gauge"><div id="gauge_intolerancia" style="height:220px;"></div></div>
                      <div class="factor-text" id="txt_gauge_intolerancia"></div>
                    </div>
                  </div>

                  <div class="factor-card">
                    <div class="factor-inner">
                      <div class="factor-gauge"><div id="gauge_sin_homicidios" style="height:220px;"></div></div>
                      <div class="factor-text" id="txt_gauge_sin_homicidios"></div>
                    </div>
                  </div>

                  <div class="factor-side">
                    <div class="factor-rate">
                        <div class="rate-title" id="meta_tasa_title">Tasa de homicidios x 100.000 habitantes · Santander</div>
                        <div class="rate-val" id="meta_tasa_val">--</div>
                    </div>

                    <!-- ✅ ESTE ES EL CUADRO GRANDE (Observaciones) -->
                    <div class="factor-note" id="meta_observaciones">
                        <!-- aquí se inyecta el texto -->
                    </div>
                    </div>
                </div>
              </section>

              <?php include 'admin/include/footer.php'; ?>
            </div>
          </div>
        </div>
      </div>
    </div>

    <?php include 'admin/include/gerenic_script.php'; ?>

    <!-- ✅ IMPORTANTE: vendor-all antes de pcoded -->
    <script src="assets/js/vendor-all.min.js"></script>
<script src="assets/js/plugins/bootstrap.min.js"></script>
<script src="assets/js/pcoded.min.js"></script>

   <script src="<?php echo Util::versionar('./admin/js/mapa_secretaria_administrativa.js'); ?>"></script>
   <script src="<?php echo Util::versionar('./admin/js/dash_adminitrativa.js'); ?>"></script>

    <script>
/* ============================
   DEBUG OVERLAY (opcional)
============================ */
function showDashError(title, detail){
  let box = document.getElementById('dash_debug_error');
  if(!box){
    box = document.createElement('div');
    box.id = 'dash_debug_error';
    box.style.cssText = `
      position:fixed; z-index:99999; left:18px; right:18px; bottom:18px;
      padding:14px 16px; border-radius:14px;
      background:rgba(10,14,18,.92); color:#fff;
      border:1px solid rgba(255,255,255,.18);
      box-shadow:0 24px 70px rgba(0,0,0,.55);
      font-family:system-ui, -apple-system, Segoe UI, Roboto, Arial;
    `;
    document.body.appendChild(box);
  }
  box.innerHTML = `
    <div style="font-weight:1000;letter-spacing:.3px;margin-bottom:6px">⚠️ ${title}</div>
    <div style="font-size:12px;opacity:.9;white-space:pre-wrap">${detail || ''}</div>
  `;
}
window.addEventListener('error', function(e){
  showDashError('Error JavaScript', (e.message || 'Error') + '\n' + (e.filename || '') + ':' + (e.lineno || ''));
});

/* ============================
   HELPERS
============================ */
function esc(s){
  return String(s ?? '').replace(/[&<>"']/g, m => ({
    '&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#039;'
  }[m]));
}
function safeArr(a){ return Array.isArray(a) ? a : []; }
function safeStr(s){ return (typeof s === 'string') ? s : ''; }

/* ============================
   THEME BASE (sin romper tu CSS)
============================ */
function applyHighchartsBase(){
  if(typeof Highcharts === 'undefined') return;
  Highcharts.setOptions({
    chart:{ backgroundColor:'transparent' },
    credits:{ enabled:false },
    title:{ style:{ fontFamily:'IBM Plex Sans, sans-serif', fontWeight:'900' } },
    subtitle:{ style:{ fontFamily:'IBM Plex Sans, sans-serif', fontWeight:'700' } },
    legend:{ itemStyle:{ fontWeight:'900' } }
  });
}

/* ============================
   CHART COLUMN (con números arriba)
============================ */
function crearGrafico(idChart, cats, serie1, serie2, titulo='', anio1=2025, anio2=2026){
  applyHighchartsBase();

  Highcharts.chart(idChart,{
    chart:{ type:'column', spacing:[14,10,8,10] },
    title:{ text: (titulo && String(titulo).trim() !== '') ? titulo : null },
    xAxis:{ categories: cats, labels:{ style:{ fontWeight:'900' } } },
    yAxis:{ title:{ text:null }, labels:{ style:{ fontWeight:'900' } } },

    plotOptions:{
      column:{
        borderRadius:7,
        groupPadding:0.12,
        pointPadding:0.06,
        borderWidth:0
      },
      series:{
        dataLabels:{
          enabled:true,
          inside:false,
          allowOverlap:true,
          crop:false,
          overflow:'none',
          y:-10,                 // 👈 encima de la barra
          style:{
            textOutline:'none',
            fontWeight:'1000',
            fontSize:'12px',
            color:'#111'
          },
          formatter:function(){
            const v = (this.y ?? 0);
            return v === 0 ? '' : v; // opcional: oculta ceros
          }
        }
      }
    },

    tooltip:{
      shared:true,
      backgroundColor:'rgba(10,14,18,.92)',
      borderColor:'rgba(255,255,255,.18)',
      style:{ color:'#fff', fontWeight:'900' }
    },

    series:[
      { name:String(anio1), color:'#ff7a00', data: serie1 },
      { name:String(anio2), color:'#2d572c', data: serie2 }
    ]
  });
}

/* ============================
   GAUGE (número un poco más pequeño + textos como imagen)
   - Soporta valores >100 (ej 110) sin dañarse
============================ */
function makeGauge(container, value, titleHtml, subtitleHtml){
  applyHighchartsBase();

  const val = Number(value) || 0;
  const max = Math.max(100, Math.ceil(val / 10) * 10);

  const safeTitle = (titleHtml && String(titleHtml).trim() !== '') ? titleHtml : '';
  const safeSub   = (subtitleHtml && String(subtitleHtml).trim() !== '') ? subtitleHtml : '';

  Highcharts.chart(container,{
    chart:{ type:'solidgauge', backgroundColor:'transparent', spacing:[0,0,0,0] },
    title:{ text:null },
    tooltip:{ enabled:false },

    pane:{
      center:['50%','52%'],
      size:'120%',
      startAngle:-140,
      endAngle:140,
      background:[{
        outerRadius:'100%',
        innerRadius:'80%',
        backgroundColor:'#f0f2f4',
        borderWidth:0
      }]
    },

    yAxis:{
      min:0, max:max,
      lineWidth:0, tickWidth:0, minorTickInterval:null, tickAmount:0,
      labels:{ enabled:false },
      stops:[
        [0.00,'#1976d2'],
        [0.33,'#2e7d32'],
        [0.66,'#f9a825'],
        [1.00,'#d32f2f']
      ]
    },

    plotOptions:{
      solidgauge:{
        rounded:true,
        dataLabels:{
          y: 10,                 // ✅ baja el bloque al centro
          borderWidth:0,
          useHTML:true
        }
      }
    },

    series:[{
      data:[val],
      dataLabels:{
        format: `
          <div style="text-align:center; transform: translateY(6px);">
            <div style="
              font-size:28px;          /* ✅ más pequeño */
              font-weight:1000;
              line-height:1;
              color:#7a1212;
              text-shadow:0 2px 10px rgba(0,0,0,.10);
              margin-bottom:6px;
            ">${val}</div>

            ${safeTitle ? `
              <div style="
                font-size:12px;
                font-weight:1000;
                line-height:1.15;
                color:#111;
              ">${safeTitle}</div>
            ` : ``}

            ${safeSub ? `
              <div style="
                margin-top:4px;
                font-size:12px;
                font-weight:900;
                color:rgba(0,0,0,.60);
              ">${safeSub}</div>
            ` : ``}
          </div>
        `
      }
    }],

    credits:{ enabled:false }
  });
}


/* ============================
   VARIACIONES POR DELITO
   Fila 1: diferencia absoluta (v2 - v1)
   Fila 2: variación porcentual respecto a v1
   ROJO = aumentó (más delitos = peor)
   AMARILLO = igual
   VERDE = bajó (menos delitos = mejor)
============================ */
function renderVariaciones(containerId, chartId, cats, s1, s2){
  const container = document.getElementById(containerId);
  if(!container) return;

  // Pre-calcular diffs, pcts y clases para cada categoría
  const items = cats.map((cat, i) => {
    const v1   = Number(s1[i] ?? 0);
    const v2   = Number(s2[i] ?? 0);
    const diff = v2 - v1;
    let pct;
    if(v1 === 0 && v2 === 0)  pct = 0;
    else if(v1 === 0)          pct = 100;
    else                       pct = Math.round(((v2 - v1) / v1) * 100);

    const cls = diff > 0 ? 'var-rojo' : (diff < 0 ? 'var-verde' : 'var-amarillo');
    const diffTxt = diff > 0 ? '+' + diff : String(diff);
    const pctTxt  = pct  > 0 ? '+' + pct + '%' : pct + '%';
    return { cat, cls, diffTxt, pctTxt };
  });

  const headCols = items.map(it => `<div class="var-th">${esc(it.cat)}</div>`).join('');
  const row1Cols = items.map(it => `<div class="var-td ${it.cls}">${it.diffTxt}</div>`).join('');
  const row2Cols = items.map(it => `<div class="var-td ${it.cls}">${it.pctTxt}</div>`).join('');

  container.innerHTML = `
    <div class="var-wrap">
      <div class="var-axis-spacer"></div>
      <div class="var-tabla">
        <div class="var-tabla-head">${headCols}</div>
        <div class="var-tabla-row">${row1Cols}</div>
        <div class="var-tabla-row">${row2Cols}</div>
      </div>
    </div>`;

  // Alinear spacer con el eje Y del gráfico Highcharts
  const hcChart = Highcharts.charts.find(c => c && c.renderTo && c.renderTo.id === chartId);
  if(hcChart){
    const axisW = hcChart.plotLeft; // px desde borde izquierdo hasta inicio del área de plot
    const spacer = container.querySelector('.var-axis-spacer');
    if(spacer) spacer.style.flexBasis = axisW + 'px';
  }
}

/* ============================
   CARGAR TODO DESDE BD
============================ */
function loadDashboard(){
  if(typeof Highcharts === 'undefined'){
    showDashError('Highcharts no cargó', 'Revisa que NO esté duplicado y que cargue desde assets/js/highcharts/.');
    return;
  }

  // Buscar boletín activo
  return $.ajax({
    url: 'admin/ajax/dash_interior_save.php',
    method: 'POST',
    dataType: 'json',
    data: { action: 'list_boletines' }
  }).then(function(bulRes){
    let boletinId = 0;
    if(bulRes && bulRes.ok && bulRes.boletines && bulRes.boletines.length > 0){
      const active = bulRes.boletines.find(b => b.activo == 1) || bulRes.boletines[0];
      boletinId = active.id;
    }

    return $.ajax({
      url: 'admin/ajax/dash_interior_payload.php',
      method: 'GET',
      dataType: 'json',
      cache: false,
      data: boletinId > 0 ? { boletin_id: boletinId } : {}
    });
  }).done(function(res){

    if(!res){ showDashError('Respuesta vacía','No devolvió JSON.'); return; }
    if(!res.ok){ showDashError('Payload ok=false', res.msg || JSON.stringify(res)); return; }

    const meta = res.meta || {};
    const anio1 = parseInt(meta.anio_1 || 2025, 10);
    const anio2 = parseInt(meta.anio_2 || 2026, 10);

    // chips años
    $('#chip_year_1').html(`<span class="dot dot-2025"></span> ${anio1}`);
    $('#chip_year_2').html(`<span class="dot dot-2026"></span> ${anio2}`);
    $('.y1').text(anio1);
    $('.y2').text(anio2);

    // meta badges (sección factores, abajo)
    if(meta.fecha_cierre){
      const mesesAbrevMeta = ['Ene','Feb','Mar','Abr','May','Jun','Jul','Ago','Sep','Oct','Nov','Dic'];
      const pf = meta.fecha_cierre.split('-');
      const fechaFmtMeta = pf.length === 3
        ? `${pf[2]}/${mesesAbrevMeta[parseInt(pf[1],10)-1]}/${pf[0]}`
        : meta.fecha_cierre;
      $('#meta_fecha').html(`<i class="bi bi-calendar2-check"></i> Fecha cierre: <b>${fechaFmtMeta}</b>`);
    }
    if(meta.fuente){
      $('#meta_fuente').html(`<i class="bi bi-database-check"></i> Fuente: <b>${esc(meta.fuente)}</b>`);
    }

    // meta pills (header hero, zona superior)
    if(meta.boletin_no){
      $('#hero_boletin_no_txt').text(meta.boletin_no);
      $('#hero_boletin_no').show();
    }
    if(meta.boletin_fecha){
      const pf = meta.boletin_fecha.split('-');
      const fechaBul = pf.length === 3 ? `${pf[2]}/${pf[1]}/${pf[0]}` : meta.boletin_fecha;
      $('#hero_boletin_fecha_txt').text(fechaBul);
      $('#hero_boletin_fecha').show();
    }
    // Actualizar enlace PDF con boletin_id si hay boletín activo
    if(meta.boletin_id){
      const pdfLink = $('#pdf_download_link');
      pdfLink.attr('href', 'admin/ajax/dash_interior_pdf.php?boletin_id=' + meta.boletin_id);
    }
    if(meta.fecha_cierre){
      const mesesAbrev = ['Ene','Feb','Mar','Abr','May','Jun','Jul','Ago','Sep','Oct','Nov','Dic'];
      const partesFecha = meta.fecha_cierre.split('-');
      const fechaFmt = partesFecha.length === 3
        ? `${partesFecha[2]}/${mesesAbrev[parseInt(partesFecha[1],10)-1]}/${partesFecha[0]}`
        : meta.fecha_cierre;
      $('#hero_fecha_txt').text(fechaFmt);
      $('#hero_fecha_cierre').show();
    }
    if(meta.fuente){
      $('#hero_fuente_txt').text(meta.fuente);
      $('#hero_fuente').show();
    }
    
// ✅ TASA homicidios
if(meta.tasa_homicidios !== undefined && meta.tasa_homicidios !== null && String(meta.tasa_homicidios).trim() !== ''){
  $('#meta_tasa_title').text(`Tasa de homicidios x 100.000 habitantes · Santander ${anio2}`);
  $('#meta_tasa_val').text(String(meta.tasa_homicidios));
} else {
  $('#meta_tasa_val').text('--');
}

// ✅ Nota HTML (Factores de Atención)
$('#meta_observaciones').html(meta.nota_html && String(meta.nota_html).trim()
  ? meta.nota_html
  : '<span style="opacity:.75;font-weight:900">Sin observaciones registradas.</span>');

// 8 charts
    const datasets = res.datasets || {};
    Object.keys(datasets).forEach(cardKey=>{
      const ds = datasets[cardKey] || {};
      const cats = safeArr(ds.cats);
      const s1   = safeArr(ds.serie_anio_1);
      const s2   = safeArr(ds.serie_anio_2);

      const chartId = 'chart_' + cardKey;
      if(document.getElementById(chartId)){
        crearGrafico(chartId, cats, s1, s2, (ds?.card?.titulo || ''), anio1, anio2);
      }

      const kpiId = 'kpis_' + cardKey;
      if(document.getElementById(kpiId)){
        renderVariaciones(kpiId, chartId, cats, s1, s2);
      }
    });

    // FACTORES (3 gauges + textos)
    const f = res.factors || {};

    // Total de homicidios Santander (serie año 2, categoría “Homicidio” del dataset santander)
    const dsSantanderPol  = (res.datasets || {}).santander_politico || {};
    const catsSantanderPol = safeArr(dsSantanderPol.cats);
    const serieSantPol2   = safeArr(dsSantanderPol.serie_anio_2);
    const idxHom          = catsSantanderPol.findIndex(c => c.toLowerCase().trim() === 'homicidio');
    const totalHom        = idxHom >= 0 ? (parseInt(serieSantPol2[idxHom], 10) || 0) : 0;

    if(f.sicariato && document.getElementById('gauge_sicariato')){
      const valSic = parseInt(f.sicariato.valor || 0, 10);
      makeGauge(
        'gauge_sicariato',
        valSic,
        safeStr(f.sicariato.titulo_html || '”Homicidio por Sicariato”'),
        ''
      );
      if(totalHom > 0){
        const pct = ((valSic / totalHom) * 100).toFixed(1);
        $('#txt_gauge_sicariato').html(
          `De <strong>${totalHom}</strong> homicidios en Santander, <strong>${valSic}</strong> son por Sicariato lo que equivale a un <span style=”color:#d32f2f;font-weight:900”>${pct}%</span>`
        );
      }
    }

    if(f.intolerancia && document.getElementById('gauge_intolerancia')){
      const valInt = parseInt(f.intolerancia.valor || 0, 10);
      makeGauge(
        'gauge_intolerancia',
        valInt,
        safeStr(f.intolerancia.titulo_html || '”Homicidios por Intolerancia”'),
        ''
      );
      if(totalHom > 0){
        const pct = ((valInt / totalHom) * 100).toFixed(1);
        $('#txt_gauge_intolerancia').html(
          `De <strong>${totalHom}</strong> homicidios en Santander, <strong>${valInt}</strong> son por Intolerancia lo que equivale a un <span style=”color:#d32f2f;font-weight:900”>${pct}%</span>`
        );
      }
    }

    if(f.sin_homicidios && document.getElementById('gauge_sin_homicidios')){
      const valSH = parseInt(meta.municipios_sin_homicidios ?? f.sin_homicidios.valor ?? 0, 10);
      makeGauge(
        'gauge_sin_homicidios',
        valSH,
        safeStr(f.sin_homicidios.titulo_html || '”Municipios de Santander”'),
        ''
      );
      $('#txt_gauge_sin_homicidios').html(
        `<strong>${valSH}</strong> <span style=”color:#2e7d32;font-weight:900”>”Municipios de Santander sin homicidios”</span>`
      );
    }

  }).fail(function(xhr){
    showDashError(
      'Falló AJAX payload',
      (xhr.status ? `HTTP ${xhr.status}\n` : '') + (xhr.responseText || 'Sin responseText')
    );
  });
}

$(document).ready(function(){
  setTimeout(loadDashboard, 250);
});
</script>
  </div>

  <!-- MODAL (intacto) -->
  <div class="modal fade" id="modalMunicipio" tabindex="-1" aria-labelledby="modalMunicipioLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered g360-security-modal-dialog" role="document">
      <div class="modal-content g360-security-modal">
        <div class="modal-header">
          <h5 class="modal-title" id="modalMunicipioLabel">
            <span class="g360-modal-title-icon">
              <i class="bi bi-geo-alt"></i>
            </span>
            Información de seguridad por municipio
          </h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar" onclick="cerrarModalmodalMunicipio()">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">
          <div class="row"></div>
          <div class="table-responsive">
            <table id="dynamictable" class="table table-bordered table-hover" width="100%"></table>
          </div>
        </div>
      </div>
    </div>
  </div>

</body>
</html>