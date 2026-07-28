<?php
include './admin/include/head.php';
include './admin/include/dark_theme.php';
require './admin/include/generic_classes.php';

// Permisos
/* extract(PagePermissions::crudVarsForCurrentPage());
if (!$view) {
    require 'permiso_denegado.php';
}
 */

?>
<style>
    #categoriaSelect {
        margin: 0;
    }

    .loader-overlay {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(7,11,20,.55);
        z-index: 9999;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .spinner {
        border: 8px solid #f3f3f3;
        /* gris claro */
        border-top: 8px solid #3498db;
        /* azul */
        border-radius: 50%;
        width: 60px;
        height: 60px;
        animation: spin 0.8s linear infinite;
    }

    @keyframes spin {
        0% {
            transform: rotate(0deg);
        }

        100% {
            transform: rotate(360deg);
        }
    }

    #graficoCategoria {
        width: 100%;
        max-width: 800px;
        height: 250px;
        margin: auto;
    }
    <style>
/* =========================================================
   ✅ POLICÍA THEME (COMANDO) — PRO WOW
   - Fondo oscuro premium + gradiente visible
   - Cards glass y bordes suaves
   - Selects/labels con alto contraste
   - Gráficos en contenedores pro
   - Loader premium
========================================================= */

:root{
  --police-1:#0b1220;       /* base */
  --police-2:#0a1d3a;       /* navy */
  --police-3:#103b73;       /* azul mando */
  --police-4:#1f6feb;       /* azul acento */

  --ink:#eaf1ff;            /* texto principal */
  --muted:rgba(234,241,255,.74);

  --card: rgba(255,255,255,.06);
  --card-2: rgba(255,255,255,.08);
  --line: rgba(255,255,255,.12);

  --radius-xl: 18px;
  --radius-lg: 14px;
  --radius-md: 12px;

  --shadow-soft: 0 12px 36px rgba(0,0,0,.35);
  --shadow-mid:  0 18px 50px rgba(0,0,0,.45);

  --ring: 0 0 0 .25rem rgba(31,111,235,.28);

  --ok:#22c55e;
  --warn:#f59e0b;
  --bad:#ef4444;
}

/* =========================================================
   ✅ GRADIENTE SIEMPRE VISIBLE (PCoded FIX)
========================================================= */
html{
  min-height: 100% !important;
  background:
    radial-gradient(1200px 520px at 10% -10%, rgba(31,111,235,.26), transparent 62%),
    radial-gradient(980px 520px at 95% 0%, rgba(16,59,115,.22), transparent 68%),
    radial-gradient(900px 520px at 50% 112%, rgba(10,29,58,.28), transparent 62%),
    linear-gradient(180deg, var(--police-1) 0%, var(--police-2) 55%, #070b14 100%) !important;
  background-attachment: fixed !important;
}

body{
  overflow-x: hidden !important;
}

/* wrappers PCoded transparentes (para ver el gradiente) */
.pcoded-main-container,
.pcoded-wrapper,
.pcoded-content,
.pcoded-inner-content,
.main-body,
.page-wrapper{
  background: transparent !important;
  background-color: transparent !important;
}

/* velo de enfoque para que no “ensucie” */
.pcoded-content::before{
  content:"";
  position: fixed;
  inset: 0;
  z-index: -1;
  pointer-events:none;
  background:
    radial-gradient(900px 420px at 15% 15%, rgba(255,255,255,.06), transparent 60%),
    radial-gradient(720px 360px at 85% 22%, rgba(255,255,255,.05), transparent 65%);
}

/* =========================================================
   ✅ PAGE HEADER (oscuro premium)
========================================================= */
.page-header .page-block{
  background: linear-gradient(135deg, rgba(255,255,255,.10), rgba(255,255,255,.04)) !important;
  border: 1px solid rgba(255,255,255,.14) !important;
  border-radius: var(--radius-xl) !important;
  box-shadow: var(--shadow-soft) !important;
  padding: 16px 18px !important;
  backdrop-filter: blur(10px);
  -webkit-backdrop-filter: blur(10px);
}

.page-header h5,
.page-header .m-b-10{
  color: rgba(255,255,255,.86) !important;
  font-weight: 1000 !important;
  letter-spacing: .2px;
}

.breadcrumb,
.breadcrumb a{
  color: rgba(234,241,255,.74) !important;
  font-weight: 800;
}
.breadcrumb a:hover{ color: #ffffff !important; }

/* botón atrás (si viene como btn) */
.page-header .btn,
.page-header button{
  border-radius: 999px !important;
}

/* =========================================================
   ✅ CARD PRINCIPAL (glass)
========================================================= */
.card{
  border-radius: var(--radius-xl) !important;
  border: 1px solid rgba(255,255,255,.12) !important;
  background: var(--card) !important;
  box-shadow: var(--shadow-soft) !important;
  overflow:hidden;
  backdrop-filter: blur(12px);
  -webkit-backdrop-filter: blur(12px);
}

.card-header{
  background: linear-gradient(135deg, rgba(31,111,235,.22), rgba(255,255,255,.06)) !important;
  border-bottom: 1px solid rgba(255,255,255,.12) !important;
}

.card-header h5{
  margin:0;
  color: #ffffff !important;
  font-weight: 1000 !important;
  letter-spacing: .2px;
  text-shadow: 0 8px 24px rgba(0,0,0,.35);
}

/* opción 3 puntos */
.card-header-right .btn.btn-icon{
  background: rgba(255,255,255,.08) !important;
  border: 1px solid rgba(255,255,255,.14) !important;
  border-radius: 12px !important;
  color: #fff !important;
}
.dropdown-menu{
  border-radius: 14px !important;
  border: 1px solid rgba(255,255,255,.18) !important;
  box-shadow: 0 18px 50px rgba(0,0,0,.22) !important;
}

/* =========================================================
   ✅ TITULOS SECCIONES
========================================================= */
.card-body h5, .card-body h6{
  color: #fff !important;
  font-weight: 1000 !important;
}

.card-body.m-4{ margin: 18px !important; }
@media (max-width: 768px){
  .card-body.m-4{ margin: 12px !important; }
}

/* =========================================================
   ✅ FORM / SELECTS (alto contraste y pro)
========================================================= */
#categoriaSelect{ margin: 0; }

.form-control{
  border-radius: var(--radius-md) !important;
  border: 1px solid rgba(255,255,255,.16) !important;
  background: rgba(255,255,255,.08) !important;
  color: #ffffff !important;
  font-weight: 900 !important;
  min-height: 42px !important;
  box-shadow: 0 10px 26px rgba(0,0,0,.18) !important;
  transition: transform .14s ease, box-shadow .14s ease, border-color .14s ease;
}

.form-control:focus{
  border-color: rgba(31,111,235,.55) !important;
  box-shadow: var(--ring) !important;
  transform: translateY(-1px);
}

.form-control option{
  color: rgba(255,255,255,.86) !important; /* dropdown interno del navegador */
}

/* labels tipo “control” */
.card-body h5 strong{
  color: rgba(234,241,255,.92) !important;
  font-weight: 1000 !important;
}

/* =========================================================
   ✅ BLOQUES DE FILTROS (grid pro)
========================================================= */
.card-body .row.mb-3{
  background: rgba(255,255,255,.04);
  border: 1px solid rgba(255,255,255,.10);
  border-radius: var(--radius-xl);
  padding: 14px 14px 10px;
  margin-left: 0;
  margin-right: 0;
}

/* separaciones internas */
.card-body .row.mb-3 > [class*="col-"]{
  margin-bottom: 10px;
}

/* =========================================================
   ✅ CONTENEDORES DE GRÁFICOS (pro)
========================================================= */
#graficoCategoria,
#graficoDonaHurto{
  width: 100%;
  min-height: 380px;
  border-radius: var(--radius-xl);
  border: 1px solid rgba(255,255,255,.12);
  background: rgba(255,255,255,.05);
  box-shadow: var(--shadow-soft);
  overflow:hidden;
  padding: 10px;
}

/* Highcharts: SVG background transparent */
.highcharts-background{
  fill: transparent !important;
}
/* Highcharts: texto/blancos y tooltips legibles */
.highcharts-title,
.highcharts-subtitle,
.highcharts-axis-title,
.highcharts-axis-labels text,
.highcharts-legend-item text{
  fill: rgba(234,241,255,.92) !important;
  color: rgba(234,241,255,.92) !important;
  font-weight: 800 !important;
}

.highcharts-tooltip text{
  fill: rgba(255,255,255,.92) !important;
  color: rgba(255,255,255,.92) !important;
  font-weight: 800 !important;
}

/* =========================================================
   ✅ LOADER PREMIUM (no blanco agresivo)
========================================================= */
.loader-overlay{
  position: fixed;
  inset: 0;
  background: rgba(7,11,20,.55);
  backdrop-filter: blur(8px);
  -webkit-backdrop-filter: blur(8px);
  z-index: 99999;
  display:flex;
  align-items:center;
  justify-content:center;
}

.spinner{
  width: 66px;
  height: 66px;
  border-radius: 50%;
  border: 7px solid rgba(255,255,255,.12);
  border-top-color: rgba(31,111,235,.95);
  animation: spin .8s linear infinite;
  box-shadow: 0 22px 60px rgba(0,0,0,.55);
}

@keyframes spin{
  0%{ transform: rotate(0deg); }
  100%{ transform: rotate(360deg); }
}

/* =========================================================
   ✅ RESPONSIVE
========================================================= */
@media (max-width: 992px){
  .page-header .page-block{ padding: 14px 14px !important; }
}

@media (max-width: 576px){
  .card-body .row.mb-3{ padding: 12px; }
  #graficoCategoria, #graficoDonaHurto{ min-height: 340px; }
}

/* =========================================================
   ✅ MINI DETALLES (iconos del template más visibles)
========================================================= */
.feather{
  stroke: rgba(234,241,255,.92) !important;
}
/* =========================================================
   ✅ FIX CONTRASTE (TEXTOS + HIGHCHARTS) — PÉGALO AL FINAL
========================================================= */

/* Si por lo que sea el template sigue en claro, no dejes texto blanco */
body.police-theme,
body.police-theme .pcoded-main-container,
body.police-theme .pcoded-content{
  color: rgba(255,255,255,.86); /* fallback en claro */
}

/* Cards: fondo oscuro translúcido */
body.police-theme .card{
  background: rgba(255,255,255,.06) !important;
  backdrop-filter: blur(12px);
  border: 1px solid rgba(255,255,255,.10) !important;
  color: rgba(255,255,255,.86) !important;
}

body.police-theme .card-header{
  background: linear-gradient(135deg, rgba(31,111,235,.22), rgba(255,255,255,.06)) !important;
  border-bottom: 1px solid rgba(255,255,255,.10) !important;
}

body.police-theme .card-header h5,
body.police-theme .page-header h5,
body.police-theme .page-header .m-b-10,
body.police-theme .breadcrumb,
body.police-theme .breadcrumb a{
  color: rgba(255,255,255,.86) !important;     /* ✅ títulos y breadcrumb visibles */
  text-shadow: none !important;
}

/* Labels/strong (los que en tu captura casi no se ven) */
body.police-theme h5 strong,
body.police-theme label,
body.police-theme .form-label,
body.police-theme strong{
  color: rgba(255,255,255,.86) !important;
  font-weight: 900 !important;
}

/* Selects: texto visible siempre */
body.police-theme .form-control{
  background: transparent !important;
  color: rgba(255,255,255,.86) !important;
  border: 1px solid rgba(255,255,255,.12) !important;
}
body.police-theme .form-control:focus{
  border-color: rgba(32,66,127,.45) !important;
  box-shadow: 0 0 0 .25rem rgba(32,66,127,.18) !important;
}
body.police-theme .form-control option{
  color: rgba(255,255,255,.86) !important;
}

/* Contenedores de gráficos: borde suave y fondo limpio */
body.police-theme #graficoCategoria,
body.police-theme #graficoDonaHurto{
  background: transparent !important;
  border: 1px solid rgba(255,255,255,.10) !important;
  box-shadow: 0 12px 30px rgba(2,6,23,.10) !important;
}

/* =========================================================
   ✅ HIGHCHARTS: TEXTOS NEGROS EN TEMA CLARO (tu caso)
========================================================= */
body.police-theme .highcharts-title,
body.police-theme .highcharts-subtitle,
body.police-theme .highcharts-axis-title,
body.police-theme .highcharts-axis-labels text,
body.police-theme .highcharts-legend-item text,
body.police-theme .highcharts-data-label text{
  fill: rgba(255,255,255,.92) !important;
  color: rgba(255,255,255,.92) !important;
  font-weight: 800 !important;
}

/* Grid/axis más suave */
body.police-theme .highcharts-grid-line{
  stroke: rgba(255,255,255,.10) !important;
}
body.police-theme .highcharts-axis-line{
  stroke: rgba(255,255,255,.18) !important;
}

/* Tooltip: texto negro (legible) */
body.police-theme .highcharts-tooltip text{
  fill: #0f172a !important;
  color: rgba(255,255,255,.86) !important;
  font-weight: 800 !important;
}

/* Botón hamburguesa (context menu) visible */
body.police-theme .highcharts-contextbutton .highcharts-button-box{
  fill: rgba(255,255,255,.06) !important;
  stroke: rgba(255,255,255,.18) !important;
}
body.police-theme .highcharts-contextbutton .highcharts-button-symbol{
  stroke: rgba(255,255,255,.80) !important;
}

/* Loader overlay no “lava” el texto */
body.police-theme .loader-overlay{
  background: rgba(7,11,20,.55) !important;
}
/* =========================================================
   ✅ FIX CONTRASTE (TEXTOS + HIGHCHARTS) — PÉGALO AL FINAL
========================================================= */

/* Si por lo que sea el template sigue en claro, no dejes texto blanco */
body.police-theme,
body.police-theme .pcoded-main-container,
body.police-theme .pcoded-content{
  color: rgba(255,255,255,.86); /* fallback en claro */
}

/* Cards: fondo oscuro translúcido */
body.police-theme .card{
  background: rgba(255,255,255,.06) !important;
  backdrop-filter: blur(12px);
  border: 1px solid rgba(255,255,255,.10) !important;
  color: rgba(255,255,255,.86) !important;
}

body.police-theme .card-header{
  background: linear-gradient(135deg, rgba(31,111,235,.22), rgba(255,255,255,.06)) !important;
  border-bottom: 1px solid rgba(255,255,255,.10) !important;
}

body.police-theme .card-header h5,
body.police-theme .page-header h5,
body.police-theme .page-header .m-b-10,
body.police-theme .breadcrumb,
body.police-theme .breadcrumb a{
  color: rgba(255,255,255,.86) !important;     /* ✅ títulos y breadcrumb visibles */
  text-shadow: none !important;
}

/* Labels/strong (los que en tu captura casi no se ven) */
body.police-theme h5 strong,
body.police-theme label,
body.police-theme .form-label,
body.police-theme strong{
  color: rgba(255,255,255,.86) !important;
  font-weight: 900 !important;
}

/* Selects: texto visible siempre */
body.police-theme .form-control{
  background: transparent !important;
  color: rgba(255,255,255,.86) !important;
  border: 1px solid rgba(255,255,255,.12) !important;
}
body.police-theme .form-control:focus{
  border-color: rgba(32,66,127,.45) !important;
  box-shadow: 0 0 0 .25rem rgba(32,66,127,.18) !important;
}
body.police-theme .form-control option{
  color: rgba(255,255,255,.86) !important;
}

/* Contenedores de gráficos: borde suave y fondo limpio */
body.police-theme #graficoCategoria,
body.police-theme #graficoDonaHurto{
  background: transparent !important;
  border: 1px solid rgba(255,255,255,.10) !important;
  box-shadow: 0 12px 30px rgba(2,6,23,.10) !important;
}

/* =========================================================
   ✅ HIGHCHARTS: TEXTOS NEGROS EN TEMA CLARO (tu caso)
========================================================= */
body.police-theme .highcharts-title,
body.police-theme .highcharts-subtitle,
body.police-theme .highcharts-axis-title,
body.police-theme .highcharts-axis-labels text,
body.police-theme .highcharts-legend-item text,
body.police-theme .highcharts-data-label text{
  fill: rgba(255,255,255,.92) !important;
  color: rgba(255,255,255,.92) !important;
  font-weight: 800 !important;
}

/* Grid/axis más suave */
body.police-theme .highcharts-grid-line{
  stroke: rgba(255,255,255,.10) !important;
}
body.police-theme .highcharts-axis-line{
  stroke: rgba(255,255,255,.18) !important;
}

/* Tooltip: texto negro (legible) */
body.police-theme .highcharts-tooltip text{
  fill: #0f172a !important;
  color: rgba(255,255,255,.86) !important;
  font-weight: 800 !important;
}

/* Botón hamburguesa (context menu) visible */
body.police-theme .highcharts-contextbutton .highcharts-button-box{
  fill: rgba(255,255,255,.06) !important;
  stroke: rgba(255,255,255,.18) !important;
}
body.police-theme .highcharts-contextbutton .highcharts-button-symbol{
  stroke: rgba(255,255,255,.80) !important;
}

/* Loader overlay no “lava” el texto */
body.police-theme .loader-overlay{
  background: rgba(7,11,20,.55) !important;
}

</style>

</style>

<body>


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

    <!-- [ Main Content ] start -->
    <div class="pcoded-main-container">
        <div class="pcoded-content">
            <!-- [ breadcrumb ] start -->
            <div class="page-header">
                <div class="page-block">
                    <div class="row align-items-center">
                        <div class="col-md-12">
                            <div class="d-flex justify-content-between align-items-center">
                                <h5 class="m-b-10">Información policía </h5>
                                <?php include './admin/include/btn_back.php'; ?>
                            </div>
                            <ul class="breadcrumb">
                                <li class="breadcrumb-item"><a href="index.php"><i class="feather icon-home"></i></a>
                                </li>
                                <li class="breadcrumb-item"><a href="#!">Registro visitas / Gráficos policía</a></li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>

            <div class="tab-content" id="myTabContent">
                <div class="tab-pane fade show active" id="home" role="tabpanel" aria-labelledby="home-tab">
                    <br>
                    <div class="card">
                        <div class="card-header d-flex flex-wrap align-items-center justify-content-between py-3">
                            <h5 class="mb-0 text-center w-100">Informe gobierno Santander</h5>
                            <div class="card-header-right ml-auto">
                                <div class="btn-group card-option">
                                    <button type="button" class="btn dropdown-toggle btn-icon" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                        <i class="feather icon-more-horizontal"></i>
                                    </button>
                                    <ul class="list-unstyled card-option dropdown-menu dropdown-menu-right">
                                        <li class="dropdown-item full-card">
                                            <a href="#!">
                                                <span><i class="feather icon-maximize"></i> Maximizar</span>
                                                <span style="display:none"><i class="feather icon-minimize"></i> Restaurar</span>
                                            </a>
                                        </li>
                                        <li class="dropdown-item minimize-card">
                                            <a href="#!">
                                                <span><i class="feather icon-minus"></i> Colapsar</span>
                                                <span style="display:none"><i class="feather icon-plus"></i> Expandir</span>
                                            </a>
                                        </li>
                                        <li class="dropdown-item reload-card">
                                            <a href="#!"><i class="feather icon-refresh-cw"></i> Recargar</a>
                                        </li>
                                        <li class="dropdown-item close-card">
                                            <a href="#!"><i class="feather icon-trash"></i> Eliminar</a>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>

                        <div class="card-body m-4">
                            <div class="card-body table-border-style" style="font-size: 14px;">
                                <div class="row mb-3">
                                    <!-- Selección de Categoría -->
                                    <div class="col-md-4">
                                        <h5 for="categoriaSelect"><strong>Seleccionar categoría:</strong></h5>
                                        <select class="form-control" id="categoriaSelect">
                                            <option value="hurtos">Hurtos</option>
                                            <option value="amenazas">Amenazas</option>
                                            <!-- <option value="desplazamientos">Desplazamientos</option> -->
                                            <option value="desaparecidos_desp">Desaparecidos</option>
                                            <option value="homicidios">Homicidios</option>
                                            <option value="secuestros">Secuestros</option>
                                        </select>
                                    </div>

                                    <div class="col-md-4">
                                        <h5 class="text-center"><strong>Comparativa de gráfico 1</strong></h5>
                                        <div class="row">
                                            <!-- Cuadro Año 1 -->
                                            <div class="col-md-6">
                                                <select class="form-control" id="anioSelect1">
                                                    <?php
                                                        $currentYear = date('Y');
                                                        for ($year = $currentYear; $year >= 2021; $year--) { 
                                                            $selected = ($year == $currentYear - 1) ? 'selected' : ''; 
                                                            echo "<option value=\"$year\" $selected>$year</option>";
                                                        }
                                                    ?>
                                                </select>
                                            </div>

                                            <!-- Cuadro Año 2 -->
                                            <div class="col-md-6">
                                                <select class="form-control" id="anioSelect2">
                                                    <?php
                                                        $currentYear = date('Y');
                                                        for ($year = $currentYear; $year >= 2021; $year--) { 
                                                            $selected = ($year == $currentYear - 2) ? 'selected' : ''; 
                                                            echo "<option value=\"$year\" $selected>$year</option>";
                                                        }
                                                    ?>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                    <!-- Segundo cuadro -->
                                    <div class="col-md-4">
                                        <h5 class="text-center"><strong>Comparativa de gráfico 2</strong></h5>
                                        <div class="row">
                                            <div class="col-md-6">
                                                <select class="form-control" id="anioSelect3">
                                                    <?php
                                                        $currentYear = date('Y');
                                                        for ($year = $currentYear; $year >= 2021; $year--) { 
                                                            $selected = ($year == $currentYear - 1) ? 'selected' : ''; 
                                                            echo "<option value=\"$year\" $selected>$year</option>";
                                                        }
                                                    ?>
                                                </select>
                                            </div>
                                            <div class="col-md-6">
                                                <select class="form-control" id="anioSelect4">
                                                    <?php
                                                        $currentYear = date('Y');
                                                        for ($year = $currentYear; $year >= 2021; $year--) { 
                                                            $selected = ($year == $currentYear - 2) ? 'selected' : ''; 
                                                            echo "<option value=\"$year\" $selected>$year</option>";
                                                        }
                                                    ?>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div id="loader" class="loader-overlay" style="display: none;">
                                    <div class="spinner"></div>
                                </div>

                                <!-- Gráficos -->
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <div id="graficoCategoria" style="min-height:400px;"></div>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <div id="graficoDonaHurto" style="min-height:400px;"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
         <?php include 'admin/include/footer.php'; ?>
    </div>

    <!-- Warning Section Ends -->
    <!-- Required Js -->
    <?php include 'admin/include/gerenic_script.php'; ?>

    <script src="assets/js/vendor-all.min.js"></script>
    <script src="assets/js/plugins/bootstrap.min.js"></script>
    <script src="assets/js/pcoded.min.js"></script>

    <!-- <script>
        document.addEventListener('DOMContentLoaded', function () {
            const categoriaSelect = document.getElementById('categoriaSelect');
            const loader = document.getElementById('loader');

            async function loadAndDrawChart(categoria) {
                loader.style.display = 'flex'; 
                
                const apiEndpoint = './admin/classes/get_chart_data.php?categoria=' + categoria; 

                try {
                    const response = await fetch(apiEndpoint);
                    
                    if (!response.ok) {
                        throw new Error('Error de red al cargar la data.');
                    }
                    
                    const result = await response.json(); 
                    
                    if (!result.valid) {

                        Highcharts.chart('graficoCategoria', {
                            title: { text: '🚨 Error de Carga 🚨' },
                            subtitle: { text: result.title || 'Verifica la consola para más detalles.' },
                            series: [{ data: [] }]
                        });
                        return;
                    }

                    const data = result.data;
                    const title = result.title;
                    const categories = result.categories;
                    const chartType = result.chart_type || 'column'; 
                    
                    if (data.length === 0) {
                        throw new Error(`La API no devolvió datos para esta categoría.`);
                    }

                    let seriesConfig = [];
                    let xAxisConfig = {};
                    let plotOptions = {};

                    if (chartType === 'pie') {

                        seriesConfig = [{
                            name: 'Casos',
                            colorByPoint: true,
                            type: 'pie', 
                            data: data 
                        }];
                        xAxisConfig = { categories: [], title: { text: null } }; 
                        plotOptions = {
                            pie: {
                                allowPointSelect: true,
                                cursor: 'pointer',
                                dataLabels: {
                                    enabled: true,
                                    format: '<b>{point.name}</b>: {point.percentage:.1f} %' 
                                },
                                showInLegend: true
                            }
                        };
                    } else {

                        seriesConfig = [{
                            name: 'Total Casos',
                            data: data, 
                            color: '#3498db',
                            type: 'column'
                        }];
                        xAxisConfig = {
                            categories: categories, 
                            crosshair: true,
                            title: { text: 'Mes' }
                        };
                        plotOptions = {
                            column: {
                                pointPadding: 0.2,
                                borderWidth: 0,
                                borderRadius: 5,
                                color: 'rgba(75, 192, 192, 0.8)',
                                states: { hover: { color: 'rgba(54, 162, 235, 1)' } }
                            }
                        };
                    }


                    Highcharts.chart('graficoCategoria', {
                        chart: {
                            type: chartType, 
                            renderTo: 'graficoCategoria'
                        },
                        title: {
                            text: title
                        },
                        xAxis: xAxisConfig,
                        yAxis: {
                            min: 0,
                            title: {
                                text: chartType === 'column' ? 'Cantidad de casos' : null 
                            },
                            allowDecimals: false
                        },
                        plotOptions: plotOptions,
                        series: seriesConfig,
                        credits: {
                            enabled: false 
                        }
                    });

                } catch (error) {
                    console.error("Error al pintar el gráfico:", error);
                    Highcharts.chart('graficoCategoria', {
                        title: { text: '🚨 Error de Carga / Sin Datos 🚨' },
                        subtitle: { text: error.message || 'Verifica la consola para más detalles.' },
                        series: [{ data: [] }]
                    });
                } finally {
                    loader.style.display = 'none';
                }
            }

            // ----------------------------------------------------
            // INICIALIZACIÓN Y EVENTO
            // ----------------------------------------------------
            loadAndDrawChart(categoriaSelect.value);

            categoriaSelect.addEventListener('change', function() {
                loadAndDrawChart(this.value);
            });
        });
    </script> -->
   
    <script src="https://code.highcharts.com/highcharts.js"></script> 
    <script type="text/javascript" src="admin/js/graficos-policia.js"></script>
    <script type="text/javascript" src="./admin/js/datatables/jquery.dataTables.min.js"></script>
    <link href="./admin/js/datatables/jquery.dataTables.min.css" rel="stylesheet" />


</body>

</html>