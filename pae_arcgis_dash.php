<?php
ob_start(); // solo una vez
include './admin/include/head.php'; // head.php maneja session_start y el DOCTYPE si aplica
require_once './admin/include/generic_classes.php';
?>

<style>
/* =========================================================
   PAE ArcGIS Dashboard - GOV SaaS Premium Skin (NO BACK)
   + Premium Loader + Better Buttons + Charts polish
   ========================================================= */

:root{
  --gov-900:#0b1220;
  --gov-800:#101a2f;
  --gov-700:#14213d;

  --gov-primary:#234162;
  --gov-primary-2:#2f5b86;
  --gov-accent:#19b6d2;

  --text-1:#0f172a;
  --text-2:#334155;
  --muted:#64748b;

  --card:#ffffff;
  --stroke:rgba(15, 23, 42, .10);
  --shadow: 0 10px 26px rgba(2, 6, 23, .10);

  --radius:16px;
  --radius-sm:12px;

  --focus: 0 0 0 .22rem rgba(25,182,210,.22);
}

/* ===== Base ===== */
.dashboard-body{
  background:
    radial-gradient(1200px 650px at 12% -10%, rgba(35,65,98,.18), transparent 55%),
    radial-gradient(900px 520px at 92% 0%, rgba(25,182,210,.14), transparent 58%),
    linear-gradient(180deg, #f7fafc 0%, #f2f6fb 55%, #eef3f9 100%);
  color: var(--text-1);
}

.pcoded-content{ padding-top: 18px; }

*{ -webkit-font-smoothing: antialiased; -moz-osx-font-smoothing: grayscale; }

/* ===== Page header / Breadcrumb ===== */
.page-header .page-block{
  border-radius: var(--radius);
  background: rgba(255,255,255,.76);
  border: 1px solid var(--stroke);
  box-shadow: 0 10px 22px rgba(2,6,23,.07);
  backdrop-filter: blur(12px);
}

.page-header h5{
  font-weight: 900;
  letter-spacing: .2px;
  color: var(--gov-primary);
}

.breadcrumb{ background: transparent !important; margin-bottom: 0; }
.breadcrumb .breadcrumb-item a{ color: var(--gov-primary); font-weight: 700; }
.breadcrumb .breadcrumb-item{ color: var(--muted); }

/* ===== Global card upgrade ===== */
.card{
  border: 1px solid var(--stroke) !important;
  border-radius: var(--radius) !important;
  box-shadow: var(--shadow);
  overflow: hidden;
}

.card .card-header{
  border-bottom: 1px solid rgba(255,255,255,.12);
  padding: 12px 14px;
}

.card .card-body{ padding: 14px; }

/* ===== Elegant headers ===== */
.card-header.bg-primary,
.card-header.bg-info,
.card-header.bg-dark,
.card-header.bg-secondary{
  background: linear-gradient(135deg, var(--gov-primary) 0%, #1c3550 45%, #0e2237 100%) !important;
  color: #fff !important;
}

.card-header.bg-info{
  background: linear-gradient(135deg, #0ea5e9 0%, #1d4ed8 55%, #0b2a57 100%) !important;
}

.card-header.bg-dark{
  background: linear-gradient(135deg, #0b1220 0%, #111a2a 40%, #0b1220 100%) !important;
}

.card-header.bg-secondary{
  background: linear-gradient(135deg, #334155 0%, #1f2937 65%, #0f172a 100%) !important;
}

/* Asegura contraste dentro de headers */
.card-header h6, .card-header h5, .card-header small,
.card-header a, .card-header span { color:#fff !important; }

/* ===== Alerts / Banner ===== */
.alert.alert-info{
  border-radius: var(--radius);
  border: 1px solid rgba(25,182,210,.28) !important;
  background: linear-gradient(135deg, rgba(25,182,210,.12) 0%, rgba(35,65,98,.10) 60%, rgba(255,255,255,.88) 100%) !important;
  box-shadow: 0 12px 26px rgba(2,6,23,.08);
}

.alert .badge{ border-radius: 999px; }

/* ===== Inputs ===== */
.form-control, .select2-container .select2-selection--single{
  border-radius: 14px !important;
  border: 1px solid rgba(15,23,42,.14) !important;
  box-shadow: 0 8px 18px rgba(2,6,23,.06);
  background: rgba(255,255,255,.92) !important;
}

.form-control:focus{
  border-color: rgba(25,182,210,.75) !important;
  box-shadow: var(--focus) !important;
}

/* ===== Badges ===== */
.badge{
  font-weight: 800;
  letter-spacing: .15px;
  padding: .44rem .64rem;
}
.badge-primary{ background: var(--gov-primary) !important; }
.badge-info{ background: #0ea5e9 !important; }
.badge-success{ background: #16a34a !important; }
.badge-warning{ background: #f59e0b !important; color:#0b1220 !important; }
.badge-danger{ background: #ef4444 !important; }

/* ===== Buttons: PRO (sin tocar HTML) ===== */
.btn{
  border-radius: 14px !important;
  font-weight: 800 !important;
  letter-spacing: .2px;
  box-shadow: 0 10px 18px rgba(2,6,23,.10);
  transition: transform .15s ease, box-shadow .15s ease, filter .15s ease, background .15s ease, border-color .15s ease;
}

.btn:focus{ box-shadow: var(--focus) !important; }
.btn:hover{ transform: translateY(-1px); box-shadow: 0 14px 28px rgba(2,6,23,.16); }

.btn-primary{
  border: 0 !important;
  background: linear-gradient(135deg, var(--gov-primary) 0%, var(--gov-primary-2) 55%, #0b2236 100%) !important;
  color:#fff !important;
}
.btn-primary:hover{ filter: brightness(1.02); }

.btn-outline-primary{
  border: 1px solid rgba(35,65,98,.35) !important;
  background: rgba(255,255,255,.78) !important;
  color: var(--gov-primary) !important;
}
.btn-outline-primary:hover{
  background: rgba(35,65,98,.10) !important;
  border-color: rgba(35,65,98,.50) !important;
  color: var(--gov-primary) !important;
}

.btn-outline-secondary{
  border: 1px solid rgba(100,116,139,.35) !important;
  background: rgba(255,255,255,.78) !important;
  color: #1f2937 !important;
}
.btn-outline-secondary:hover{
  background: rgba(15,23,42,.06) !important;
}

/* ===== Submenu pills ===== */
.submenu-personalizado{ gap: 10px; }
.submenu-personalizado .nav-item{
  flex: 1 1 auto;
  min-width: 160px;
  font-size: 14px;
}

.submenu-personalizado .nav-link{
  background: rgba(255,255,255,.82) !important;
  border: 1px solid rgba(15,23,42,.12);
  color: var(--gov-primary) !important;
  font-weight: 900 !important;
  border-radius: 16px !important;
  padding: 10px 12px !important;
  box-shadow: 0 12px 22px rgba(2,6,23,.07);
  transition: transform .18s ease, box-shadow .18s ease, background .18s ease, color .18s ease;
  text-align: center;
}

.submenu-personalizado .nav-link:hover{
  transform: translateY(-2px);
  box-shadow: 0 18px 34px rgba(2,6,23,.13);
  background: rgba(25,182,210,.10) !important;
}

.submenu-personalizado .nav-link.active{
  background: linear-gradient(135deg, var(--gov-primary) 0%, var(--gov-primary-2) 55%, #0b2236 100%) !important;
  color: #fff !important;
  border-color: rgba(255,255,255,.16) !important;
  box-shadow: 0 18px 34px rgba(2,6,23,.18);
}

/* ===== Tables ===== */
.table{ border-color: rgba(15,23,42,.10) !important; }
.table thead.thead-dark th{
  background: linear-gradient(135deg, #0f172a 0%, #111827 100%) !important;
  border-color: rgba(255,255,255,.08) !important;
}
.table td, .table th{ vertical-align: middle !important; }
.table-bordered td, .table-bordered th{ border-color: rgba(15,23,42,.12) !important; }

/* ===== Progress ===== */
.progress{
  border-radius: 999px;
  background: rgba(15,23,42,.08);
  overflow: hidden;
  box-shadow: inset 0 1px 0 rgba(255,255,255,.55);
}
.progress-bar{
  border-radius: 999px;
  font-weight: 900;
  letter-spacing: .2px;
}

/* ===== Map container polish ===== */
.card-body.p-2{
  background: linear-gradient(180deg, rgba(255,255,255,.92) 0%, rgba(255,255,255,.80) 100%);
}
#map, .leaflet-container, .esri-view, .map-container{ border-radius: 16px; }

/* ===== Modal upgrade ===== */
.modal-content{
  border-radius: 18px;
  border: 1px solid rgba(15,23,42,.12);
  box-shadow: 0 26px 70px rgba(2,6,23,.24);
}
.modal-header{
  background: linear-gradient(135deg, var(--gov-primary) 0%, #0e2237 100%);
  color: #fff;
  border-top-left-radius: 18px;
  border-top-right-radius: 18px;
}
.modal-header .close{ color:#fff; opacity:.92; }

/* =========================================================
   NEW PREMIUM LOADER (page + ajax)
   ========================================================= */
#pageLoader{
  position: fixed;
  inset: 0;
  z-index: 99999;
  display: none;
  background:
    radial-gradient(900px 520px at 12% -10%, rgba(35,65,98,.30), transparent 60%),
    radial-gradient(700px 420px at 92% 10%, rgba(25,182,210,.22), transparent 55%),
    rgba(11,18,32,.78);
  backdrop-filter: blur(12px);
}

#pageLoader.active{ display:flex; align-items:center; justify-content:center; }

.loader-card{
  width: min(520px, 92vw);
  border-radius: 22px;
  border: 1px solid rgba(255,255,255,.16);
  background: rgba(255,255,255,.08);
  box-shadow: 0 30px 80px rgba(0,0,0,.35);
  padding: 18px 18px 16px;
}

.loader-top{
  display:flex;
  align-items:center;
  justify-content:space-between;
  gap: 14px;
  margin-bottom: 14px;
}

.loader-brand{
  display:flex;
  align-items:center;
  gap: 12px;
  color:#fff;
}

.loader-dot{
  width: 14px; height: 14px;
  border-radius: 999px;
  background: var(--gov-accent);
  box-shadow: 0 0 0 8px rgba(25,182,210,.12);
  animation: pulseDot 1.15s ease-in-out infinite;
}

@keyframes pulseDot{
  0%,100% { transform: scale(1); filter: brightness(1); }
  50% { transform: scale(1.18); filter: brightness(1.15); }
}

.loader-title{
  font-weight: 1000;
  letter-spacing: .2px;
  margin: 0;
  line-height: 1.2;
  font-size: 15px;
}

.loader-sub{
  margin: 2px 0 0;
  color: rgba(255,255,255,.78);
  font-size: 12px;
}

.loader-chip{
  border-radius: 999px;
  padding: 6px 10px;
  font-size: 12px;
  font-weight: 900;
  color: #07101d;
  background: linear-gradient(135deg, rgba(25,182,210,.95), rgba(14,165,233,.95));
  box-shadow: 0 10px 24px rgba(25,182,210,.25);
}

.loader-bar{
  height: 12px;
  border-radius: 999px;
  background: rgba(255,255,255,.10);
  overflow:hidden;
  box-shadow: inset 0 1px 0 rgba(255,255,255,.20);
}

.loader-bar > span{
  display:block;
  height: 100%;
  width: 45%;
  border-radius: 999px;
  background: linear-gradient(90deg, rgba(255,255,255,.12), rgba(25,182,210,.85), rgba(255,255,255,.12));
  animation: loadingMove 1.05s ease-in-out infinite;
  filter: saturate(1.05);
}

@keyframes loadingMove{
  0% { transform: translateX(-120%); }
  100% { transform: translateX(260%); }
}

.loader-hint{
  margin-top: 10px;
  font-size: 12px;
  color: rgba(255,255,255,.74);
  display:flex;
  justify-content:space-between;
  gap: 10px;
}

/* ===== Responsive tweaks ===== */
@media (max-width: 991px){
  .submenu-personalizado .nav-item{ min-width: 46%; }
}
@media (max-width: 575px){
  .page-header .page-block{ padding: 12px; }
  .submenu-personalizado .nav-item{ min-width: 100%; }
  .card .card-body{ padding: 12px; }
}
/* ==============================
   PAE - Filtro Municipio (PRO)
   Resumen + Badges + Icon badges
   ============================== */

.pae-resumen-title{
  font-size: 1.02rem !important;
  font-weight: 900 !important;
  letter-spacing: .2px;
  color: var(--gov-primary) !important;
  display:flex;
  align-items:center;
  gap: 8px;
  margin-top: 6px;
}

.pae-stat-row{
  display:flex;
  justify-content:space-between;
  align-items:center;
  gap: 10px;
  padding: 8px 10px;
  border-radius: 14px;
  border: 1px solid rgba(15,23,42,.08);
  background: rgba(255,255,255,.78);
  box-shadow: 0 10px 18px rgba(2,6,23,.06);
  margin-bottom: 10px;
}

.pae-stat-row .label{
  font-size: .90rem;
  font-weight: 800;
  color: #334155;
}

.pae-pill{
  min-width: 56px;
  height: 30px;
  padding: 0 12px;
  display:inline-flex;
  align-items:center;
  justify-content:center;
  border-radius: 999px !important;
  font-weight: 1000 !important;
  letter-spacing: .3px;
  box-shadow: 0 10px 22px rgba(2,6,23,.12);
  border: 1px solid rgba(255,255,255,.25);
}

.pae-pill.p-primary{
  color:#fff;
  background: linear-gradient(135deg, var(--gov-primary), var(--gov-primary-2));
}
.pae-pill.p-success{
  color:#06110a;
  background: linear-gradient(135deg, rgba(34,197,94,.95), rgba(16,185,129,.95));
}
.pae-pill.p-warning{
  color:#111827;
  background: linear-gradient(135deg, rgba(245,158,11,.95), rgba(250,204,21,.95));
}
.pae-pill.p-danger{
  color:#fff;
  background: linear-gradient(135deg, rgba(239,68,68,.95), rgba(244,63,94,.95));
}

/* Icon mini-cards */
.pae-mini-grid{
  margin-top: 10px;
}

.pae-mini{
  position: relative;
  border: 1px solid rgba(15,23,42,.08);
  background: rgba(255,255,255,.78);
  border-radius: 16px;
  padding: 10px 8px 12px;
  box-shadow: 0 12px 22px rgba(2,6,23,.07);
  transition: transform .15s ease, box-shadow .15s ease;
}

.pae-mini:hover{
  transform: translateY(-2px);
  box-shadow: 0 18px 34px rgba(2,6,23,.12);
}

.pae-mini img{
  width: 38px !important;
  height: 38px !important;
  object-fit: contain;
  filter: drop-shadow(0 10px 18px rgba(2,6,23,.08));
}

.pae-mini .mini-label{
  font-size: .78rem;
  font-weight: 900;
  color: #334155;
  margin-top: 6px;
  margin-bottom: 0;
}

.pae-mini .mini-badge{
  position: absolute;
  top: 8px;
  right: 8px;
  transform: translateZ(0);
  height: 26px;
  min-width: 44px;
  padding: 0 10px;
  border-radius: 999px;
  display:flex;
  align-items:center;
  justify-content:center;
  font-weight: 1000;
  font-size: .86rem;
  letter-spacing: .3px;
  border: 1px solid rgba(255,255,255,.22);
  box-shadow: 0 12px 26px rgba(2,6,23,.14);
}

/* colores para mini-badges */
.m-primary{ color:#fff; background: linear-gradient(135deg, var(--gov-primary), var(--gov-primary-2)); }
.m-success{ color:#06110a; background: linear-gradient(135deg, rgba(34,197,94,.95), rgba(16,185,129,.95)); }
.m-warning{ color:#111827; background: linear-gradient(135deg, rgba(245,158,11,.95), rgba(250,204,21,.95)); }
.m-danger{ color:#fff; background: linear-gradient(135deg, rgba(239,68,68,.95), rgba(244,63,94,.95)); }
/* ==========================================
   PAE Donut Cards (Gov SaaS Premium)
   Compatible con Highcharts
   ========================================== */

.pae-donut-wrap{
  border-radius: 22px;
  border: 1px solid rgba(15,23,42,.10);
  background: linear-gradient(180deg, rgba(255,255,255,.92), rgba(255,255,255,.82));
  box-shadow: 0 20px 45px rgba(2,6,23,.10);
  overflow: hidden;
  position: relative;
}

.pae-donut-wrap::before{
  content:"";
  position:absolute;
  inset:-2px;
  background:
    radial-gradient(600px 220px at 25% -10%, rgba(25,182,210,.18), transparent 55%),
    radial-gradient(520px 220px at 85% 0%, rgba(35,65,98,.16), transparent 55%);
  pointer-events:none;
}

.pae-donut-head{
  padding: 12px 14px 10px;
  border-bottom: 1px solid rgba(15,23,42,.08);
  display:flex;
  align-items:center;
  justify-content:space-between;
  gap: 10px;
  position: relative;
  z-index: 1;
}

.pae-donut-title{
  margin:0;
  font-weight: 1000;
  letter-spacing: .2px;
  color: #0f172a;
  font-size: 1.02rem;
  line-height: 1.2;
}

.pae-donut-chip{
  border-radius: 999px;
  padding: 6px 10px;
  font-weight: 1000;
  font-size: .74rem;
  letter-spacing: .25px;
  color: #07101d;
  background: linear-gradient(135deg, rgba(25,182,210,.95), rgba(14,165,233,.95));
  box-shadow: 0 10px 20px rgba(25,182,210,.22);
  white-space: nowrap;
}

.pae-donut-body{
  padding: 10px 14px 6px;
  position: relative;
  z-index: 1;
}

.pae-donut-foot{
  padding: 10px 14px 14px;
  border-top: 1px solid rgba(15,23,42,.08);
  position: relative;
  z-index: 1;
}

.pae-foot-title{
  font-weight: 1000;
  color:#0f172a;
  font-size: .95rem;
  margin: 0 0 8px 0;
  letter-spacing: .2px;
}

.pae-foot-chips{
  display:flex;
  gap: 8px;
  flex-wrap: wrap;
}

.pae-foot-pill{
  border-radius: 999px;
  padding: 7px 10px;
  font-weight: 1000;
  font-size: .86rem;
  letter-spacing: .2px;
  background: rgba(255,255,255,.86);
  border: 1px solid rgba(15,23,42,.10);
  box-shadow: 0 12px 22px rgba(2,6,23,.08);
  display:inline-flex;
  align-items:center;
  gap: 8px;
}

.pae-foot-pill b{
  font-weight: 1100;
}

.pae-dot{
  width: 10px; height: 10px;
  border-radius: 999px;
  display:inline-block;
}

/* ---------- Highcharts fine tuning ---------- */
.highcharts-background{ fill: transparent !important; }

.highcharts-title{
  font-weight: 1000 !important;
  letter-spacing: .2px !important;
  fill: #0f172a !important;
}

.highcharts-subtitle{
  fill: #64748b !important;
  font-weight: 800 !important;
}

.highcharts-legend-item text{
  fill: #334155 !important;
  font-weight: 900 !important;
}

.highcharts-data-label text{
  font-weight: 1000 !important;
  text-shadow: 0 1px 0 rgba(0,0,0,.15);
}

.highcharts-tooltip{
  filter: drop-shadow(0 12px 26px rgba(2,6,23,.22));
}

.highcharts-tooltip text{
  font-weight: 900 !important;
}
/* =========================================================
   GOV SaaS - Fuente de Datos (ROW 2) - Pro Layout
   ========================================================= */

.gov-source-card .card-body{
  padding: 16px !important;
}

.gov-source-grid{
  display: grid;
  grid-template-columns: 1.1fr 1.2fr 1fr;
  gap: 14px;
  align-items: start;
}

@media (max-width: 991px){
  .gov-source-grid{ grid-template-columns: 1fr; }
}

/* bloques internos */
.gov-source-box{
  background: rgba(255,255,255,.86);
  border: 1px solid rgba(15,23,42,.10);
  border-radius: 16px;
  box-shadow: 0 12px 24px rgba(2,6,23,.08);
  padding: 14px 14px 12px;
}

.gov-source-title{
  display:flex;
  align-items:center;
  gap: 8px;
  margin: 0 0 8px 0;
  font-weight: 1000;
  color: #0f172a;
  letter-spacing: .2px;
  font-size: 1rem;
}

.gov-source-sub{
  color: #64748b;
  font-weight: 700;
  margin: 0;
  font-size: .9rem;
  line-height: 1.35;
}

.gov-source-list{
  margin: 0;
  padding-left: 18px;
  color: #334155;
  font-weight: 700;
  font-size: .9rem;
}
.gov-source-list li{ margin: 4px 0; }

/* tabla pro */
.gov-mini-table{
  width: 100%;
  border-collapse: separate;
  border-spacing: 0;
  overflow: hidden;
  border-radius: 16px;
  border: 1px solid rgba(15,23,42,.10);
  box-shadow: 0 12px 24px rgba(2,6,23,.08);
  background: rgba(255,255,255,.90);
}

.gov-mini-table td{
  padding: 10px 12px;
  border-bottom: 1px solid rgba(15,23,42,.08);
  font-weight: 800;
  font-size: .9rem;
  color: #0f172a;
}
.gov-mini-table tr:last-child td{ border-bottom: 0; }

.gov-mini-table td:first-child{
  width: 52%;
  color: #334155;
  font-weight: 900;
}

/* badge en tabla: ovalado y pro */
.gov-mini-badge{
  display:inline-flex;
  align-items:center;
  justify-content:center;
  padding: 8px 12px;
  border-radius: 999px;
  font-weight: 1000;
  letter-spacing: .2px;
  box-shadow: 0 10px 20px rgba(2,6,23,.12);
  border: 1px solid rgba(255,255,255,.25);
  white-space: nowrap;
}

/* progress pro + texto legible */
.gov-progress-wrap{
  margin-top: 10px;
  background: rgba(255,255,255,.86);
  border: 1px solid rgba(15,23,42,.10);
  border-radius: 16px;
  box-shadow: 0 12px 24px rgba(2,6,23,.08);
  padding: 10px 12px;
}

.gov-progress-head{
  display:flex;
  align-items:center;
  justify-content:space-between;
  gap: 10px;
  margin-bottom: 8px;
}
.gov-progress-head .label{
  font-weight: 1000;
  color:#0f172a;
  letter-spacing: .2px;
  font-size: .92rem;
}
.gov-progress-head .meta{
  font-weight: 900;
  color:#64748b;
  font-size: .85rem;
}

.gov-progress{
  height: 14px !important;
  border-radius: 999px !important;
  background: rgba(15,23,42,.08) !important;
  overflow: hidden;
  box-shadow: inset 0 1px 0 rgba(255,255,255,.55);
}

.gov-progress .progress-bar{
  border-radius: 999px !important;
  position: relative;
  font-weight: 1000 !important;
}

.gov-progress .progress-bar::after{
  content:"";
  position:absolute;
  inset:0;
  background: linear-gradient(90deg, rgba(255,255,255,.18), rgba(255,255,255,.06), rgba(255,255,255,.18));
  transform: translateX(-60%);
  animation: govShine 1.6s ease-in-out infinite;
  pointer-events:none;
}
@keyframes govShine{
  0%{ transform: translateX(-80%); }
  100%{ transform: translateX(120%); }
}

.gov-progress-text{
  margin-top: 8px;
  display:flex;
  justify-content:flex-end;
  font-weight: 1000;
  color:#0f172a;
  font-size: .85rem;
  letter-spacing: .2px;
}

/* alerta inferior pro */
.gov-realtime-alert{
  border-radius: 18px !important;
  border: 1px solid rgba(25,182,210,.22) !important;
  background: linear-gradient(135deg, rgba(25,182,210,.10), rgba(255,255,255,.82)) !important;
  box-shadow: 0 16px 28px rgba(2,6,23,.10);
}

.gov-realtime-alert strong{
  font-weight: 1000;
  color:#0f172a;
}
.gov-realtime-alert small{
  color:#334155;
  font-weight: 700;
}
/* =========================================================
   GOV SaaS - ROW 2 (Fuente de datos) - SOLO FRONT SAFE
   ========================================================= */

.gov-source-card .card-body{ padding: 16px !important; }

/* mejora spacing */
.gov-source-row > [class*="col-"]{
  margin-bottom: 12px;
}

/* cajas internas (las 3 columnas) */
.gov-source-row > .col-md-4{
  position: relative;
}

.gov-source-row > .col-md-4 > *:first-child{
  /* aplica a tu h6 principal */
  font-size: 1.05rem !important;
  font-weight: 1000 !important;
  letter-spacing: .2px;
  margin-bottom: 10px !important;
}

/* crea un “panel” visual por columna sin cambiar HTML */
.gov-source-row > .col-md-4{
  background: rgba(255,255,255,.86);
  border: 1px solid rgba(15,23,42,.10);
  border-radius: 16px;
  box-shadow: 0 12px 24px rgba(2,6,23,.08);
  padding: 14px 14px 12px;
}

/* textos */
.gov-source-row p,
.gov-source-row li,
.gov-source-row td{
  font-weight: 700;
  color:#334155;
}
.gov-source-row .text-muted{ color:#64748b !important; font-weight:700; }

/* lista más limpia */
.gov-source-row ul.small{
  margin-bottom: 0 !important;
  padding-left: 18px !important;
}
.gov-source-row ul.small li{ margin: 4px 0; }

/* tabla de estadísticas PRO */
.gov-source-row table.table{
  margin-bottom: 10px !important;
  border-radius: 16px;
  overflow: hidden;
  border: 1px solid rgba(15,23,42,.10) !important;
  box-shadow: 0 12px 24px rgba(2,6,23,.08);
}
.gov-source-row table.table td{
  padding: 10px 12px !important;
  border-color: rgba(15,23,42,.08) !important;
  vertical-align: middle !important;
}

/* badges ovalados pro (sin cambiar tus badge-*) */
.gov-source-row .badge{
  border-radius: 999px !important;
  padding: 8px 12px !important;
  font-weight: 1000 !important;
  letter-spacing: .2px;
  box-shadow: 0 10px 20px rgba(2,6,23,.12);
  border: 1px solid rgba(255,255,255,.20);
}

/* progress PRO con texto visible */
.gov-source-card .progress{
  height: 16px !important;
  border-radius: 999px !important;
  background: rgba(15,23,42,.08) !important;
  overflow: hidden;
  box-shadow: inset 0 1px 0 rgba(255,255,255,.55);
  position: relative;
}

.gov-source-card .progress-bar{
  border-radius: 999px !important;
  position: relative;
}

/* brillo animado */
.gov-source-card .progress-bar::after{
  content:"";
  position:absolute;
  inset:0;
  background: linear-gradient(90deg, rgba(255,255,255,.18), rgba(255,255,255,.06), rgba(255,255,255,.18));
  transform: translateX(-80%);
  animation: govShine 1.6s ease-in-out infinite;
  pointer-events:none;
}
@keyframes govShine{
  0%{ transform: translateX(-80%); }
  100%{ transform: translateX(120%); }
}

/* texto dentro de progress legible (tu <small>) */
.gov-source-card .progress-bar small{
  position: absolute;
  left: 50%;
  top: 50%;
  transform: translate(-50%, -50%);
  font-weight: 1000 !important;
  color: #0b1220 !important;   /* visible sobre verde */
  text-shadow: 0 1px 0 rgba(255,255,255,.55);
  white-space: nowrap;
  font-size: .78rem !important;
}

/* alerta final más pro */
.gov-realtime-alert{
  border-radius: 18px !important;
  border: 1px solid rgba(25,182,210,.22) !important;
  background: linear-gradient(135deg, rgba(25,182,210,.10), rgba(255,255,255,.82)) !important;
  box-shadow: 0 16px 28px rgba(2,6,23,.10);
}
.gov-realtime-alert strong{ font-weight: 1000; color:#0f172a; }
.gov-realtime-alert small{ color:#334155; font-weight: 700; }

/* responsive */
@media (max-width: 991px){
  .gov-source-row > .col-md-4{ padding: 12px; }
}
/* ===========================
   DEBUG PRO - 3 CUADROS EN 1 FILA
   =========================== */
.gov-debug-card{ overflow:hidden; }
.gov-debug-head{ border-bottom: 1px solid rgba(255,255,255,.10); }

.gov-debug-grid{
  display: grid;
  grid-template-columns: 1.05fr 1fr 1fr;  /* resumen un poquito más ancho */
  gap: 12px;
  align-items: stretch;
}

.gov-debug-box{
  border: 1px solid rgba(15,23,42,.10);
  border-radius: 16px;
  background: rgba(255,255,255,.92);
  box-shadow: 0 12px 26px rgba(2,6,23,.07);
  overflow: hidden;
  min-height: 420px; /* para que se vean parejos */
}

.gov-box-head{
  display:flex;
  align-items:center;
  justify-content:space-between;
  gap:10px;
  padding: 10px 12px;
  background: linear-gradient(135deg, rgba(35,65,98,.10), rgba(25,182,210,.08));
  border-bottom: 1px solid rgba(15,23,42,.08);
}

.gov-box-head .ttl{
  font-weight: 1000;
  letter-spacing:.15px;
  font-size: 13px;
  color:#0f172a;
}

.chip{
  padding: 6px 10px;
  border-radius: 999px;
  font-size: 12px;
  font-weight: 1000;
  letter-spacing:.2px;
  border: 1px solid rgba(15,23,42,.10);
  background: rgba(255,255,255,.85);
}
.chip-success{ background: rgba(22,163,74,.12); border-color: rgba(22,163,74,.25); color:#166534; }
.chip-warning{ background: rgba(245,158,11,.14); border-color: rgba(245,158,11,.30); color:#7c2d12; }
.chip-info{ background: rgba(14,165,233,.12); border-color: rgba(14,165,233,.28); color:#075985; }

.gov-mini-kv{
  padding: 10px 12px 12px;
  display: grid;
  gap: 8px;
  font-size: 12px;
}
.gov-mini-kv .kv{
  display:flex;
  justify-content:space-between;
  align-items:flex-start;
  gap:10px;
  border: 1px solid rgba(15,23,42,.08);
  border-radius: 12px;
  padding: 8px 10px;
  background: rgba(255,255,255,.95);
}
.gov-mini-kv .kv span:first-child{
  color:#475569;
  font-weight:900;
}
.gov-mini-kv .kv .val{
  text-align:right;
  color:#0f172a;
  font-weight:900;
  max-width: 68%;
  word-break: break-word;
}
.gov-link{ color:#1d4ed8; font-weight:900; text-decoration:none; }
.gov-link:hover{ text-decoration: underline; }

.gov-pill{
  display:inline-flex;
  align-items:center;
  justify-content:center;
  min-width: 56px;
  padding: 6px 10px;
  border-radius: 999px;
  font-weight: 1000;
  color:#fff;
  background: linear-gradient(135deg, #234162, #2f5b86);
  box-shadow: 0 10px 18px rgba(2,6,23,.12);
}

/* Tablas compactas con scroll interno */
.gov-table-wrap{
  padding: 10px 10px 12px;
  height: calc(100% - 48px);
}
.gov-table-mini{
  font-size: 12px;
  margin:0;
}
.gov-table-mini thead th{
  position: sticky;
  top: 0;
  z-index: 2;
  background: linear-gradient(135deg, #0f172a, #111827) !important;
  color:#fff;
  font-weight: 1000;
  border-color: rgba(255,255,255,.10) !important;
  padding: 10px 10px;
}
.gov-table-mini td{
  padding: 9px 10px;
}
.gov-table-wrap{
  overflow: auto;
  max-height: 360px; /* ✅ compacta para que no estire toda la página */
  border-radius: 14px;
}

.gov-note-warning{
  margin: 10px 10px 0;
  padding: 8px 10px;
  border-radius: 12px;
  font-size: 12px;
  font-weight: 900;
  color: #7c2d12;
  background: rgba(245,158,11,.14);
  border: 1px solid rgba(245,158,11,.25);
}

/* Responsive: en tablet 2 columnas, en móvil 1 */
@media (max-width: 1199px){
  .gov-debug-grid{ grid-template-columns: 1fr 1fr; }
  .gov-debug-box{ min-height: 380px; }
}
@media (max-width: 767px){
  .gov-debug-grid{ grid-template-columns: 1fr; }
  .gov-debug-box{ min-height: auto; }
  .gov-table-wrap{ max-height: 320px; }
}
/* evita donuts invisibles por altura 0 */
[id^="grafico"][id$="Container"]{
  min-height: 230px;
  width: 100%;
}

/* ===== Mapa PAE - contenedor compacto ===== */
#contenido-mapa-pae {
  max-height: 520px;
  overflow: hidden;
  display: flex;
  justify-content: center;
  align-items: flex-start;
}
#contenido-mapa-pae svg {
  width: 100%;
  height: auto;
  max-height: 520px;
  display: block;
}
.pae-mapa-municipio:hover {
  opacity: 0.78;
  stroke-width: 1.5px !important;
  stroke: #1e3a5f !important;
}
@media (max-width: 1200px) {
  #contenido-mapa-pae, #contenido-mapa-pae svg { max-height: 440px; }
}
@media (max-width: 992px) {
  #contenido-mapa-pae, #contenido-mapa-pae svg { max-height: 360px; }
}
</style>

<!-- ✅ jQuery (SOLO 1 VEZ) -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<!-- ✅ Script PAE ArcGIS Dashboard (SOLO 1 VEZ) -->
<script src="admin/js/pae_arcgis_dash.js"></script>

<?php
// ======= TU BACK/LOGICA (SIN TOCAR) =======
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
    if ($exists == !false) {
        $final =  substr($final, 0, $exists);
        return $final;
    } else {
        return $final;
    }
}

include './admin/classes/Ciudad.php';
include './admin/classes/Estado.php';
require './admin/classes/Departamento.php';
include './admin/db/colores.php';
include './admin/classes/PaeArcgis.php';
include './admin/classes/PaeArcgisMunicipios.php';

// Información de Departamentos
$arrDep = Departamento::getAll(null);
$isvalid = $arrDep['output']['valid'];
$arrDep = $arrDep['output']['response'];
$optionDep = Util::getDepartamentoPrincipal();
foreach ($arrDep as $val) {
    $optionDep .= "<option " . ($val["codigo_departamento"] == Util::getDepartamentoPrincipal() ? "selected" : "") . " value='" . $val['codigo_departamento'] . "'>" . $val['codigo_departamento'] . " - " . $val['departamento'] . "</option>";
}

// Obtener y validar código de municipio
$codigoMunicipio = isset($_REQUEST['mun']) ? trim($_REQUEST['mun']) : 'todos';
$codigoMunicipio = preg_replace('/[^a-zA-Z0-9_]/', '', $codigoMunicipio);
if (empty($codigoMunicipio)) { $codigoMunicipio = 'todos'; }

// Obtener y validar filtro de año (vigencia)
$filtroAno = isset($_REQUEST['ano']) ? trim($_REQUEST['ano']) : 'todos';
if (!empty($filtroAno) && $filtroAno !== 'todos' && !preg_match('/^\d{4}$/', $filtroAno)) {
    $filtroAno = 'todos';
}

// Años disponibles en ArcGIS (se actualizan manualmente cuando haya nuevas vigencias)
$anosDisponibles = ['2024', '2025', '2026'];

error_log("[PAE ArcGIS Dashboard] Código de municipio: " . $codigoMunicipio . " | Año: " . $filtroAno);

$parametrosPae = ['codigoMunicipio' => $codigoMunicipio, 'ano' => $filtroAno, 'departamentoId' => Util::getDepartamentoPrincipal()];

// Obtener municipios disponibles en ArcGIS (solo Santander)
$arrMunicipiosArcgis = PaeArcgisMunicipios::getMunicipiosDisponibles();
$listaMunicipios = [];
if ($arrMunicipiosArcgis['output']['valid']) {
    foreach ($arrMunicipiosArcgis['output']['municipios'] as $mun) {
        $listaMunicipios[] = [
            'codigo_muncipio' => $mun['codigo'],
            'municipio' => $mun['nombre']
        ];
    }
}

// Llamar a ArcGIS
try {
    $arr = PaeArcgis::getDataFromArcgis($parametrosPae);
    $isvalid = $arr['output']['valid'];
    if (!$isvalid) {
        error_log("[PAE ArcGIS Dashboard] Error al obtener datos: " . ($arr['output']['error'] ?? 'Error desconocido'));
    }
} catch (Exception $e) {
    error_log("[PAE ArcGIS Dashboard] Excepción al obtener datos: " . $e->getMessage());
    $arr = [
        'output' => [
            'valid' => false,
            'error' => 'Error al consultar datos de ArcGIS: ' . $e->getMessage()
        ]
    ];
    $isvalid = false;
}

// ======= VARIABLES (SIN TOCAR) =======
$variables = [
    'disposicion_derechos_pae_enterrado',
    'disposicion_derechos_pae_quemado',
    'disposicion_derechos_pae_reciclan',
    'disposicion_derechos_pae_lombricultura',
    'disposicion_derechos_pae_tiran_lote',
    'disposicion_no_organicos_pae_enterrado',
    'disposicion_no_organicos_pae_quemado',
    'disposicion_no_organicos_pae_reciclan',
    'disposicion_no_organicos_pae_lombricultura',
    'disposicion_no_organicos_pae_tiran_lote',
    'disposicion_no_organicos_pae_otros',
    'posee_ollas_pae_si',
    'posee_ollas_pae_no',
    'posee_cuchillos_pae_si',
    'posee_cuchillos_pae_no',
    'tamano_neveras_principales_nevera_domestica_vertical_2200l',
    'tamano_neveras_principales_nevera_domestica_vertical_1200l',
    'tamano_neveras_principales_nevera_domestica_vertical_400_800L',
    'tamano_neveras_principales_nevera_domestica_vertical_menor_400L',
    'tamano_neveras_principales_nevera_domestica_vertical_otra',
    'tamano_congelador_Congelador_Grande_1400_1600L',
    'tamano_congelador_Congelador_Pequeño_Menor_400L',
    'ninos_foc',
    'neveras',
    'neveras_fun',
    'neveras_buenas',
    'neveras_almacenamiento_si',
    'neveras_almacenamiento_no',
    'congeladores',
    'congeladores_funcionando',
    'estufas',
    'quemadores_estufas',
    'quemadores_estufas_buenos',
    'estufas_gen',
    'licuadoras_industriales',
    'licuadoras_total',
    'licuadoras',
    'cantidad_platos',
    'cantidad_cucharas',
    'cantidad_pocillos',
    'cantidad_tenedores',
    'cantidad_canecas',
    'acceso_alcantarillado_si',
    'acceso_alcantarillado_no',
    'recoleccion_basuras_si',
    'recoleccion_basuras_no',
    'espacio_preparacion_si',
    'espacio_preparacion_no',
    'espacio_almacenamiento_no',
    'espacio_almacenamiento_si',
    'zona_conflicto_si',
    'zona_conflicto_no',
    'algo_frecuente_conflicto',
    'no_frecuencia_conflicto',
    'poco_frecuente_conflicto',
    'cercania_contaminacion_si',
    'cercania_contaminacion_no',
    'concepto_sanitario_si',
    'concepto_sanitario_no',
    'complemento_preparado_sitio_si',
    'complemento_preparado_sitio_no',
    'complemento_industrializado_si',
    'complemento_industrializado_no',
    'almuerzo_preparado_sitio_si',
    'almuerzo_preparado_sitio_no',
    'almuerzo_trasportado_no',
    'almuerzo_trasportado_si',
    'lavamanos_personal_si',
    'lavamanos_personal_no',
    'sanitario_personal_si',
    'sanitario_personal_no',
    'almacenamiento_personal_si',
    'almacenamiento_personal_no',
    'caracterizaciones',
    'zona_rural',
    'zona_urbana',
    'acceso_agua_si',
    'acceso_agua_no',
    'acceso_agua_intermitente',
    'almacena_alto_suelo_si',
    'almacena_alto_suelo_no',
    'almacena_balde',
    'almacena_canasta',
    'almacena_estante',
    'almacena_ninguno',
    'almacena_na',
    'acueducto',
    'embotellada',
    'lluvia',
    'carrotanque',
    'rios_quebradas',
    'otros_agua',
    'pozo_agua',
    'acceso_electricidad_si',
    'acceso_electricidad_no',
    'acceso_electricidad_intermitente',
    'electricidad',
    'gas_natural',
    'lena',
    'desecho',
    'no_aplica',
    'petroleo_gasolina',
    'comedor_escolar_si',
    'comedor_escolar_no',
    'no_tiene_concepto',
    'si_tiene_favorable',
    'si_favorable_requerimientos',
    'si_desfavorable',
    'estado_sede_antiguo_activo',
    'estado_sede_nuevo_activo',
    'estado_sede_cierre_temporal',
    'estado_techo_almacenamiento_bueno',
    'estado_techo_almacenamiento_malo',
    'estado_techo_almacenamiento_regular',
    'estado_paredes_bueno',
    'estado_paredes_regular',
    'estado_paredes_malo',
    'material_paredes_preparacion_ladrillo',
    'material_paredes_preparacion_prefabricado',
    'material_paredes_preparacion_otros',
    'material_paredes_preparacion_bahareque',
    'estado_piso_bueno',
    'estado_piso_regular',
    'estado_piso_malo',
    'material_piso_preparacion_baldosa',
    'material_piso_cemento',
    'material_piso_ladrillo',
    'material_piso_preparacion_madera',
    'material_piso_preparacion_otros',
    'estado_techo_bueno',
    'estado_techo_regular',
    'estado_techo_malo',
    'material_techo_preparacion_zinc',
    'material_techo_eternit',
    'material_techo_teja_barro',
    'material_techo_preparacion_plastico',
    'material_techo_preparacion_sin_techo',
    'material_techo_preparacion_concreto',
    'material_techo_preparacion_metal_acero',
    'material_techo_preparacion_paja',
    'material_techo_preparacion_otros',
    'estado_paredes_almacenamiento_bueno',
    'estado_paredes_almacenamiento_regular',
    'estado_paredes_almacenamiento_malo',
    'material_paredes_almacenamiento_bloque',
    'material_paredes_almacenamiento_bahareque',
    'material_paredes_almacenamiento_prefabricado',
    'material_paredes__almacenamiento_madera',
    'material_paredes_almacenamiento_otros',
    'estado_piso_almacenamiento_bueno',
    'estado_piso_almacenamiento_regular',
    'estado_piso_almacenamiento_malo',
    'material_piso_almacenamiento_bloque',
    'material_piso_almacenamiento_cemento',
    'material_piso_almacenamiento_ladrillo',
    'material_piso_almacenamiento_madera',
    'material_piso_almacenamiento_otros',
    'material_piso_almacenamiento_baldosa',
    'estado_techo_almacenamiento_bueno',
    'estado_techo_almacenamiento_regular',
    'estado_techo_almacenamiento_malo',
    'material_techo_almacenamiento_eternit',
    'material_techo_almacenamiento_tejas',
    'material_techo_almacenamiento_plastico',
    'material_techo_almacenamiento_zinc',
    'material_techo_almacenamiento_concreto',
    'material_techo_almacenamiento_otros',
    'material_techo_almacenamiento_metal',
    'posee_cucharones_pae_si',
    'posee_cucharones_pae_no'
];

foreach ($variables as $variable) {
    $$variable = isset($arr['output'][$variable]) ? $arr['output'][$variable] : 0;
}

//calculos dashboard
function calcular_porcentaje($valor, $total) { return $total > 0 ? ($valor * 100) / $total : 0; }

$neveras_malas = $neveras - $neveras_fun;
$porcentaje_neveras = calcular_porcentaje($neveras_fun, $neveras);

$congeladores_malas = $congeladores - $congeladores_funcionando;
$porcentaje_congeladores = calcular_porcentaje($congeladores_funcionando, $congeladores);

$quemadores_malas = $quemadores_estufas - $quemadores_estufas_buenos;
$porcentaje_quemadores = calcular_porcentaje($quemadores_estufas_buenos, $quemadores_estufas);

$total_licuadoras = $licuadoras_total + $licuadoras_industriales;
$licuadoras_malas = $licuadoras_industriales - $licuadoras;
$porcentaje_licuadoras = calcular_porcentaje($licuadoras, $licuadoras_industriales);

$porcentaje_alm_no = calcular_porcentaje($espacio_almacenamiento_no, $caracterizaciones);
$porcentaje_alm_si = calcular_porcentaje($espacio_almacenamiento_si, $caracterizaciones);

$porcentaje_prepa_si = calcular_porcentaje($espacio_preparacion_si, $caracterizaciones);
$porcentaje_prepa_no = calcular_porcentaje($espacio_preparacion_no, $caracterizaciones);

$porcentaje_prepa_sitio_si = calcular_porcentaje($almuerzo_preparado_sitio_si, $caracterizaciones);
$porcentaje_prepa_sitio_no = calcular_porcentaje($almuerzo_preparado_sitio_no, $caracterizaciones);

$porcentaje_transporte_almuer_si = calcular_porcentaje($almuerzo_trasportado_si, $caracterizaciones);
$porcentaje_transporte_almuer_no = calcular_porcentaje($almuerzo_trasportado_no, $caracterizaciones);

$porcentaje_complemento_prepa_sitio_si = calcular_porcentaje($complemento_preparado_sitio_si, $caracterizaciones);
$porcentaje_complemento_prepa_sitio_no = calcular_porcentaje($complemento_preparado_sitio_no, $caracterizaciones);

$porcentaje_complemento_industri_sitio_si = calcular_porcentaje($complemento_industrializado_si, $caracterizaciones);
$porcentaje_complemento_industri_sitio_no = calcular_porcentaje($complemento_industrializado_no, $caracterizaciones);

$porcentaje_armado_no_frecuente = calcular_porcentaje($no_frecuencia_conflicto, $caracterizaciones);
$porcentaje_armado_poco = calcular_porcentaje($poco_frecuente_conflicto, $caracterizaciones);
$porcentaje_armado_algo = calcular_porcentaje($algo_frecuente_conflicto, $caracterizaciones);

$porcentaje_cercania_contaminacion_si = calcular_porcentaje($cercania_contaminacion_si, $caracterizaciones);
$porcentaje_cercania_contaminacion_no = calcular_porcentaje($cercania_contaminacion_no, $caracterizaciones);

$porcentaje_acceso_agua_si = calcular_porcentaje($acceso_agua_si, $caracterizaciones);
$porcentaje_acceso_agua_no = calcular_porcentaje($acceso_agua_no, $caracterizaciones);
$porcentaje_acceso_agua_intermitente = calcular_porcentaje($acceso_agua_intermitente, $caracterizaciones);

$porcentaje_zona_conflicto_si = calcular_porcentaje($zona_conflicto_si, $caracterizaciones);
$porcentaje_zona_conflicto_no = calcular_porcentaje($zona_conflicto_no, $caracterizaciones);

$porcentaje_almacena_alto_suelo_si = calcular_porcentaje($almacena_alto_suelo_si, $caracterizaciones);
$porcentaje_almacena_alto_suelo_no = calcular_porcentaje($almacena_alto_suelo_no, $caracterizaciones);

$porcentaje_acceso_electricidad_si = calcular_porcentaje($acceso_electricidad_si, $caracterizaciones);
$porcentaje_acceso_electricidad_no = calcular_porcentaje($acceso_electricidad_no, $caracterizaciones);
$porcentaje_acceso_electricidad_intermitente = calcular_porcentaje($acceso_electricidad_intermitente, $caracterizaciones);

$porcentaje_comedor_escolar_si = calcular_porcentaje($comedor_escolar_si, $caracterizaciones);
$porcentaje_comedor_escolar_no = calcular_porcentaje($comedor_escolar_no, $caracterizaciones);

$porcentaje_estado_sede_antiguo_activo = calcular_porcentaje($estado_sede_antiguo_activo, $caracterizaciones);
$porcentaje_estado_sede_nuevo_activo = calcular_porcentaje($estado_sede_nuevo_activo, $caracterizaciones);
$porcentaje_estado_sede_cierre_temporal = calcular_porcentaje($estado_sede_cierre_temporal, $caracterizaciones);

$porcentaje_estado_techo_almacenamiento_bueno = calcular_porcentaje($estado_techo_almacenamiento_bueno, $caracterizaciones);
$porcentaje_estado_techo_almacenamiento_malo = calcular_porcentaje($estado_techo_almacenamiento_malo, $caracterizaciones);
$porcentaje_estado_techo_almacenamiento_regular = calcular_porcentaje($estado_techo_almacenamiento_regular, $caracterizaciones);

$porcentaje_estado_paredes_bueno = calcular_porcentaje($estado_paredes_bueno, $caracterizaciones);
$porcentaje_estado_paredes_malo = calcular_porcentaje($estado_paredes_malo, $caracterizaciones);
$porcentaje_estado_paredes_regular = calcular_porcentaje($estado_paredes_regular, $caracterizaciones);

$porcentaje_estado_piso_bueno = calcular_porcentaje($estado_piso_bueno, $caracterizaciones);
$porcentaje_estado_piso_malo = calcular_porcentaje($estado_piso_malo, $caracterizaciones);
$porcentaje_estado_piso_regular = calcular_porcentaje($estado_piso_regular, $caracterizaciones);

$porcentaje_estado_techo_bueno = calcular_porcentaje($estado_techo_bueno, $caracterizaciones);
$porcentaje_estado_techo_malo = calcular_porcentaje($estado_techo_malo, $caracterizaciones);
$porcentaje_estado_techo_regular = calcular_porcentaje($estado_techo_regular, $caracterizaciones);

$porcentaje_estado_paredes_almacenamiento_bueno = calcular_porcentaje($estado_paredes_almacenamiento_bueno, $caracterizaciones);
$porcentaje_estado_paredes_almacenamiento_regular = calcular_porcentaje($estado_paredes_almacenamiento_regular, $caracterizaciones);
$porcentaje_estado_paredes_almacenamiento_malo = calcular_porcentaje($estado_paredes_almacenamiento_malo, $caracterizaciones);

$porcentaje_estado_piso_almacenamiento_bueno = calcular_porcentaje($estado_piso_almacenamiento_bueno, $caracterizaciones);
$porcentaje_estado_piso_almacenamiento_regular = calcular_porcentaje($estado_piso_almacenamiento_regular, $caracterizaciones);
$porcentaje_estado_piso_almacenamiento_malo = calcular_porcentaje($estado_piso_almacenamiento_malo, $caracterizaciones);

$porcentaje_posee_ollas_pae_si = calcular_porcentaje($posee_ollas_pae_si, $caracterizaciones);
$porcentaje_posee_ollas_pae_no = calcular_porcentaje($posee_ollas_pae_no, $caracterizaciones);

$porcentaje_posee_cuchillos_pae_si = calcular_porcentaje($posee_cuchillos_pae_si, $caracterizaciones);
$porcentaje_posee_cuchillos_pae_no = calcular_porcentaje($posee_cuchillos_pae_no, $caracterizaciones);

$porcentaje_posee_cucharones_pae_si = calcular_porcentaje($posee_cucharones_pae_si, $caracterizaciones);
$porcentaje_posee_cucharones_pae_no = calcular_porcentaje($posee_cucharones_pae_no, $caracterizaciones);

$cant_ninos_pae_sentados_todos = $cant_ninos_pae_sentados_todos ?? 0;
$cant_ninos_pae_mas_75 = $cant_ninos_pae_mas_75 ?? 0;
$porcentaje_cant_ninos_pae_sentados_todos = calcular_porcentaje($cant_ninos_pae_sentados_todos, $caracterizaciones);
$porcentaje_cant_ninos_pae_mas_75 = calcular_porcentaje($cant_ninos_pae_mas_75, $caracterizaciones);

// Valores en porcentaje (0–100)
$valor  = $porcentaje_posee_cucharones_pae_no;
$valor1 = $porcentaje_posee_cuchillos_pae_no;
$valor2 = $porcentaje_posee_ollas_pae_no;
$valor3 = $almacena_ninguno;
$valor4 = $porcentaje_almacena_alto_suelo_no;
$valor5 = $porcentaje_estado_techo_almacenamiento_malo;
$valor6 = $porcentaje_estado_paredes_malo;
$valor7 = $porcentaje_estado_piso_almacenamiento_malo;
$valor8 = $porcentaje_estado_techo_malo;
$valor9 = $porcentaje_estado_paredes_almacenamiento_malo;
$valor10 = $porcentaje_estado_piso_malo;
$valor11 = $porcentaje_acceso_agua_intermitente;
$valor12 =  $porcentaje_acceso_electricidad_intermitente;
$valor13 =  $porcentaje_prepa_sitio_no;
$valor14 = $porcentaje_complemento_prepa_sitio_no;
$valor15 = $porcentaje_complemento_industri_sitio_no;
$valor16 = $porcentaje_comedor_escolar_no;
$valor17 = $no_tiene_concepto;
$valor18 = $porcentaje_cant_ninos_pae_mas_75;
$valor19 = $porcentaje_estado_sede_antiguo_activo;
$valor20 = $estado_techo_almacenamiento_malo;

// Función para determinar la clase según el valor
function getColorClass($valor)
{
    if ($valor >= 1 && $valor <= 20) return 'bg-success text-white';
    elseif ($valor >= 21 && $valor <= 35) return 'bg-warning text-dark';
    elseif ($valor >= 36 && $valor <= 60) return 'bg-orange text-white';
    elseif ($valor >= 61 && $valor <= 1500) return 'bg-danger text-white';
    return '';
}

$colorClase  = getColorClass($valor);
$colorClase1 = getColorClass($valor1);
$colorClase2 = getColorClass($valor2);
$colorClase3 = getColorClass($valor3);
$colorClase4 = getColorClass($valor4);
$colorClase5 = getColorClass($valor5);
$colorClase6 = getColorClass($valor6);
$colorClase7 = getColorClass($valor7);
$colorClase8 = getColorClass($valor8);
$colorClase9 = getColorClass($valor9);
$colorClase10 = getColorClass($valor10);
$colorClase11 = getColorClass($valor11);
$colorClase12 = getColorClass($valor12);
$colorClase13 = getColorClass($valor13);
$colorClase14 = getColorClass($valor14);
$colorClase15 = getColorClass($valor15);
$colorClase16 = getColorClass($valor16);
$colorClase17 = getColorClass($valor17);
$colorClase18 = getColorClass($valor18);
$colorClase19 = getColorClass($valor19);
$colorClase20 = getColorClass($valor20);

// Función para asignar clase de color mal si está bajito
function getColorClassb($valora)
{
    if ($valora >= 1 && $valora <= 20) return 'bg-danger text-white';
    elseif ($valora >= 21 && $valora <= 35) return 'bg-orange text-white';
    elseif ($valora >= 36 && $valora <= 60) return 'bg-warning text-dark';
    elseif ($valora >= 61 && $valora <= 100) return 'bg-success text-white';
    return '';
}

$valora  = $porcentaje_neveras;
$valora1 = $porcentaje_congeladores;
$valora2 = $porcentaje_quemadores;
$valora3 = $porcentaje_licuadoras;
$valora4 = $porcentaje_alm_no;
$valora5 = $porcentaje_prepa_no;
$valora6 = 0;
$valora7 = 0;
$valora8 = 0;
$valora9 = $porcentaje_transporte_almuer_no;
$valora10 = $porcentaje_cercania_contaminacion_no;
$valora11 = $porcentaje_zona_conflicto_no;
$valora12 = $porcentaje_armado_no_frecuente;

$colorClasea  = getColorClassb($valora);
$colorClasea1 = getColorClassb($valora1);
$colorClasea2 = getColorClassb($valora2);
$colorClasea3 = getColorClassb($valora3);
$colorClasea4 = getColorClassb($valora4);
$colorClasea5 = getColorClassb($valora5);
$colorClasea6 = getColorClassb($valora6);
$colorClasea7 = getColorClassb($valora7);
$colorClasea8 = getColorClassb($valora8);
$colorClasea9 = getColorClassb($valora9);
$colorClasea10 = getColorClassb($valora10);
$colorClasea11 = getColorClassb($valora11);
$colorClasea12 = getColorClassb($valora12);

$departamento = new Departamento();
$santander = $departamento->getAll(["id" => 21]);
$santander = $santander["output"]["response"]["0"];
$code = Util::getDepartamentoPrincipal();
$mapa = null;

if (!is_null($code)) {
    $arrCiudad = Ciudad::getAll(array('codigo_departamento' => $code));
    $finalMunicipios = $arrCiudad['output']['response'];
    $arrApoyoDep = Ciudad::getApoyoByCodigoDepartamento(array('codigo_departamento' => $code));
}
?>

<body class="dashboard-body">

<!-- ✅ LOADER PREMIUM (NO SE QUEDA PEGADO) -->
<div id="pageLoader" class="active" aria-live="polite" aria-busy="true">
  <div class="loader-card">
    <div class="loader-top">
      <div class="loader-brand">
        <span class="loader-dot"></span>
        <div>
          <p class="loader-title">Cargando Dashboard PAE · ArcGIS Online</p>
          <p class="loader-sub">Consultando datos en tiempo real y renderizando gráficos…</p>
        </div>
      </div>
      
      <span class="loader-chip">LIVE</span>
    </div>
    <div class="loader-bar"><span></span></div>
    <div class="loader-hint">
      <small><i class="feather icon-cloud"></i> ArcGIS REST API</small>
      <small><i class="feather icon-activity"></i> Render UI</small>
    </div>
  </div>
</div>

<?php include './admin/include/navbar.php'; ?>
<?php include './admin/include/header.php'; ?>

<div class="pcoded-main-container">
  <div class="pcoded-content">

    <!-- [ breadcrumb ] start -->
    <div class="page-header">
      <div class="page-block">
        <div class="row align-items-center">
          <div class="col-md-12">
            <div class="d-flex justify-content-between align-items-center">
              <div>
                <h5 class="m-b-10">Dashboard PAE - ArcGIS Online</h5>
                     <h6 class="mb-0">
                  <i class="feather icon-activity"></i> Dashboard PAE - ArcGIS Online
                </h6>
                   
              </div>
              <?php include './admin/include/btn_back.php'; ?>
            </div>
            <ul class="breadcrumb">
              <li class="breadcrumb-item"><a href="index.php"><i class="feather icon-home"></i></a></li>
              <li class="breadcrumb-item"><a href="#!"> Secretaria de Educación/Dirección PAE (ArcGIS) </a></li>
            </ul>
          </div>
        </div>
      </div>
    </div>

    <!-- BANNER -->
    <div class="row mb-2">
      <div class="col-12">
        <div class="alert alert-info alert-dismissible fade show mb-0 py-2 px-3 gov-banner">
          <div class="d-flex align-items-center justify-content-between flex-wrap">
            <div class="d-flex align-items-center mb-1 mb-md-0">              
              <div>           
                 <button type="button" class="btn btn-primary mb-3" data-toggle="modal" data-target="#modalGeocalizacion">
                       Geolocalización de sedes Educativas
                </button>
              </div>
            </div>
            
            <div class="d-flex align-items-center">
              <span class="badge badge-primary mr-2">
                <i class="feather icon-database"></i> <?php echo number_format($caracterizaciones, 0); ?> sedes
              </span>
              <a href="pae_dash.php?mun=<?php echo $codigoMunicipio; ?>" class="btn btn-outline-primary btn-sm mr-1" title="Ver Dashboard Local">
                <i class="feather icon-database"></i>
              </a>
              <a href="test_arcgis_api.php" target="_blank" class="btn btn-outline-secondary btn-sm" title="Verificar API">
                <i class="feather icon-terminal"></i>
              </a>
            </div>
          </div>
          <button type="button" class="close" data-dismiss="alert" aria-label="Close" style="padding: 0.5rem;">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
      </div>
    </div>

    <!-- ROW 1 -->
    <div class="row mb-3">
      <!-- Filtro -->
      <div class="col-lg-3 col-md-12 mb-3">
        <div class="card h-100">
          <div class="card-header bg-primary text-white py-2">
            <h6 class="mb-0"><i class="feather icon-filter"></i> Filtrar Municipio</h6>
          </div>
          <div class="card-body p-3">
            <input type="hidden" name="op" id="op" />
            <input type="hidden" name="id" id="id" />
            <input type="hidden" name="filtro" id="filtro" value="vereda" />
            <input type="hidden" name="filtroVeredaById" id="filtroVeredaById" value="si" />

            <div class="form-group" style="display:none">
              <label for="tbl_departamento_id">Departamento</label>
              <select onchange="DEPARTAMENTO.getMunicipios();" class="form-control" id="tbl_departamento_id" name="tbl_departamento_id">
                <?php echo $optionDep; ?>
              </select>
            </div>

            <div class="form-group mb-3">
              <label for="filtro_ano" class="small mb-1"><i class="feather icon-calendar"></i> Vigencia <span class="text-danger">*</span></label>
              <select class="form-control" id="filtro_ano" name="filtro_ano"
                onchange="window.location.href='pae_arcgis_dash.php?mun=<?php echo urlencode($codigoMunicipio); ?>&ano=' + this.value;">
                <option value="todos" <?php echo ($filtroAno == 'todos' ? 'selected' : ''); ?>>TODAS LAS VIGENCIAS</option>
                <?php foreach ($anosDisponibles as $ano): ?>
                  <option value="<?php echo $ano; ?>" <?php echo ($filtroAno == $ano ? 'selected' : ''); ?>>
                    Vigencia <?php echo $ano; ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </div>

            <div class="form-group mb-3">
              <label for="tbl_municipio_id" class="small mb-1">Seleccione Municipio <span class="text-danger">*</span></label>
              <select class="form-control select2" id="tbl_municipio_id" name="tbl_municipio_id"
                onchange="window.location.href='pae_arcgis_dash.php?mun=' + this.value + '&ano=<?php echo urlencode($filtroAno); ?>';">
                <option value="todos" <?php echo ($codigoMunicipio == 'todos' ? 'selected' : ''); ?>>TODOS LOS MUNICIPIOS</option>
                <?php
                foreach ($listaMunicipios as $mun) {
                  $selected = ($mun['codigo_muncipio'] == $codigoMunicipio) ? 'selected' : '';
                  echo "<option value='{$mun['codigo_muncipio']}' {$selected}>{$mun['municipio']}</option>";
                }
                ?>
              </select>
            </div>

            <!-- Mini cards con badges -->
<div class="row text-center pae-mini-grid">
  <div class="col-6 mb-2">
    <div class="pae-mini">
      <span class="mini-badge m-primary"><?php echo number_format($caracterizaciones, 0); ?></span>
      <img src="assets/img/sedes.png" alt="Sedes">
      <p class="mini-label">Sedes</p>
    </div>
  </div>

  <div class="col-6 mb-2">
    <div class="pae-mini">
      <span class="mini-badge m-success"><?php echo number_format($zona_rural, 0); ?></span>
      <img src="assets/img/rural.png" alt="Rural">
      <p class="mini-label">Rural</p>
    </div>
  </div>

  <div class="col-6">
    <div class="pae-mini">
      <span class="mini-badge m-warning"><?php echo number_format($zona_urbana, 0); ?></span>
      <img src="assets/img/urban.png" alt="Urbana">
      <p class="mini-label">Urbana</p>
    </div>
  </div>

  <div class="col-6">
    <div class="pae-mini">
      <span class="mini-badge m-danger"><?php echo number_format($ninos_foc, 0); ?></span>
      <img src="assets/img/ninosfocalizados.png" alt="Niños">
      <p class="mini-label">Niños</p>
    </div>
  </div>
</div>
      

            <div class="alert alert-info alert-sm mt-3 mb-0 py-1 px-2" role="alert">
              <small style="font-size: 0.7rem;"><i class="feather icon-cloud"></i> Datos en tiempo real desde ArcGIS</small>
            </div>

          </div>
        </div>
      </div>

      <!-- Gráficos + Secciones -->
      <div class="col-lg-9 col-md-12">
        <div class="card mb-3">
          <div class="card-header py-2" style="background-color: rgb(35, 65, 98);">
            <h6 class="mb-0" style="color: white !important;">
              <i class="feather icon-menu"></i> Secciones del Informe PAE
            </h6>
          </div>

          <div class="card-body p-2">
            <ul class="nav flex-wrap submenu-personalizado" id="myTab">
              <li class="nav-item"><a class="nav-link active" href="#" data-seccion="item1_estado_sedes"><small>1. Estado General</small></a></li>
              <li class="nav-item"><a class="nav-link" href="#" data-seccion="item2_estado_almacenamiento"><small>2. Almacenamiento</small></a></li>
              <li class="nav-item"><a class="nav-link" href="#" data-seccion="item3_comedores"><small>3. Comedores</small></a></li>
              <li class="nav-item"><a class="nav-link" href="#" data-seccion="item4_cocinas"><small>4. Cocinas</small></a></li>
              <li class="nav-item"><a class="nav-link" href="#" data-seccion="item5_concepto_sanitario"><small>5. Sanitario</small></a></li>
              <li class="nav-item"><a class="nav-link" href="#" data-seccion="item6_dotacion"><small>6. Dotación</small></a></li>
              <li class="nav-item"><a class="nav-link" href="#" data-seccion="item7_servicios_publicos"><small>7. Servicios</small></a></li>
              <li class="nav-item"><a class="nav-link" href="#" data-seccion="item8_modalidades"><small>8. Modalidades</small></a></li>
            </ul>
          </div>
        </div>

        <!-- GRAFICOS -->
        <?php include 'admin/include/graficospae.php'; ?>
      </div>
    </div>

    <!-- ROW 2 Fuente datos -->
    <div class="row mb-3">
      <div class="col-12">
        <div class="card shadow-sm gov-source-card">
          <div class="card-header bg-info text-white py-2">
            <h6 class="mb-0"><i class="feather icon-cloud"></i> Información de la Fuente de Datos</h6>
          </div>
          <div class="card-body p-3">
            <div class="row gov-source-row">
              <div class="col-md-4 mb-3 mb-md-0">
                <h6 class="text-primary mb-2" style="font-size: 0.9rem;"><i class="feather icon-database"></i> Fuente Principal</h6>
                <p class="mb-1"><strong>ArcGIS Online REST API</strong></p>
                <p class="text-muted small mb-0">
                  Servicio público de la Gobernación de Santander para interoperabilidad
                  con SIGID, GUANE y Plataforma de Acción Unificada.
                </p>
              </div>

              <div class="col-md-4 mb-3 mb-md-0">
                <h6 class="text-success mb-2" style="font-size: 0.9rem;"><i class="feather icon-check-circle"></i> Datos Disponibles</h6>
                <ul class="small mb-0 pl-3">
                  <li>Equipamiento (neveras, estufas, licuadoras)</li>
                  <li>Servicios públicos (agua, luz, alcantarillado)</li>
                  <li>Espacios e infraestructura</li>
                  <li>Modalidades PAE y concepto sanitario</li>
                </ul>
              </div>

              <div class="col-md-4">
                <h6 class="text-info mb-2" style="font-size: 0.9rem;"><i class="feather icon-info"></i> Estadísticas de Consulta</h6>
                <table class="table table-sm table-bordered mb-2">
                  <tr>
                    <td class="small"><strong>Registros:</strong></td>
                    <td class="text-end"><span class="badge badge-primary"><?php echo number_format($caracterizaciones, 0); ?></span></td>
                  </tr>
                  <tr>
                    <td class="small"><strong>Municipio:</strong></td>
                    <td class="text-end">
                      <?php
                      if ($codigoMunicipio == 'todos') {
                        echo '<span class="badge badge-info">Todos</span>';
                      } else {
                        $db = new DbConection();
                        $pdo = $db->openConect();
                        $stmt = $pdo->prepare("SELECT municipio FROM " . $db->getTable('tbl_ciudades_accion_unificada') . " WHERE codigo_muncipio = :codigo LIMIT 1");
                        $stmt->execute([':codigo' => $codigoMunicipio]);
                        $nombreMunicipio = $stmt->fetchColumn();
                        $db->closeConect();
                        echo '<span class="badge badge-success">' . htmlspecialchars($nombreMunicipio ?: $codigoMunicipio) . '</span>';
                      }
                      ?>
                    </td>
                  </tr>
                  <tr>
                    <td class="small"><strong>Vigencia:</strong></td>
                    <td class="text-end">
                      <?php if ($filtroAno == 'todos'): ?>
                        <span class="badge badge-info">Todas</span>
                      <?php else: ?>
                        <span class="badge badge-primary"><?php echo htmlspecialchars($filtroAno); ?></span>
                      <?php endif; ?>
                    </td>
                  </tr>
                  <tr>
                    <td class="small"><strong>Actualización:</strong></td>
                    <td class="text-end"><span class="badge badge-warning">Tiempo Real</span></td>
                  </tr>
                </table>

                <?php
                $camposConDatos = 0;
                $camposImportantes = [
                  'neveras', 'congeladores', 'estufas_gen', 'zona_rural', 'zona_urbana',
                  'acceso_agua_si', 'acceso_electricidad_si', 'comedor_escolar_si',
                  'complemento_preparado_sitio_si', 'concepto_sanitario_si'
                ];

                foreach ($camposImportantes as $campo) {
                  if (isset($$campo) && $$campo > 0) $camposConDatos++;
                }

                $porcentajeDatos = count($camposImportantes) > 0 ? round(($camposConDatos / count($camposImportantes)) * 100) : 0;
                ?>
                <div class="progress" style="height: 20px;">
                  <div class="progress-bar bg-success" role="progressbar" style="width: <?php echo $porcentajeDatos; ?>%;">
                    <small><?php echo $camposConDatos; ?>/<?php echo count($camposImportantes); ?> campos (<?php echo $porcentajeDatos; ?>%)</small>
                  </div>
                </div>

              </div>
            </div>

            <div class="row mt-3">
              <div class="col-12">
                <div class="alert alert-info mb-0" role="alert">
                  <div class="d-flex align-items-center">
                    <i class="feather icon-refresh-cw" style="font-size: 24px; margin-right: 10px;"></i>
                    <div>
                      <strong>Dashboard en Tiempo Real</strong><br>
                      <small>
                        Los datos se consultan directamente desde ArcGIS Online cada vez que cargas esta página.
                        No se almacenan localmente, garantizando información actualizada.
                      </small>
                    </div>
                  </div>
                </div>
              </div>
            </div>

          </div>
        </div>
      </div>
    </div>

    <!-- ROW 3: Sección DEBUG (Colapsable) -->
<div class="row mb-3">
  <div class="col-12">
    <div class="card border-secondary gov-debug-card">
      <div class="card-header bg-secondary text-white py-2 gov-debug-head" style="cursor:pointer;" id="btnDebugToggle">
        <div class="text-white d-flex justify-content-between align-items-center">
          <h6 class="mb-0">
            <i class="feather icon-code"></i> Datos Crudos de ArcGIS (Debug)
          </h6>
          <i class="feather icon-chevron-down" id="iconDebugToggle"></i>
        </div>
      </div>

      <div style="display:none;" id="collapseDebug">
        <div class="card-body p-2 p-md-3">

          <?php
          // ✅ Construir variables antes de pintar las cards (evita warnings/errores)
          $todasVariables = $variables ?? [];
          $variablesConDatos = [];
          $variablesSinDatos = [];

          foreach ($todasVariables as $var) {
            if (isset($$var)) {
              $v = $$var;
              if ($v > 0) $variablesConDatos[$var] = $v;
              else        $variablesSinDatos[$var] = $v;
            }
          }
          arsort($variablesConDatos);

          // Para el resumen del WHERE (solo visual)
          $whereDebug = ($codigoMunicipio == 'todos')
            ? "Municipio='TODOS'"
            : "Municipio='{$codigoMunicipio}'";
          ?>

          <!-- GRID 3 EN 1 FILA -->
          <div class="gov-debug-grid">

            <!-- 1) Resumen -->
            <div class="gov-debug-box">
              <div class="gov-box-head">
                <div class="ttl">
                  <i class="feather icon-info"></i> Resumen de Consulta
                </div>
                <span class="chip chip-info">ArcGIS</span>
              </div>

              <div class="gov-mini-kv">
                <div class="kv">
                  <span>Endpoint</span>
                  <span class="val">
                    <a href="https://services7.arcgis.com" target="_blank" class="gov-link">
                      https://services7.arcgis.com/.../FeatureServer/0
                    </a>
                  </span>
                </div>
                <div class="kv">
                  <span>Filtro WHERE</span>
                  <span class="val"><code><?php echo htmlspecialchars($whereDebug); ?></code></span>
                </div>
                <div class="kv">
                  <span>Features</span>
                  <span class="val">
                    <span class="gov-pill"><?php echo number_format($caracterizaciones ?? 0, 0); ?></span>
                  </span>
                </div>
                <div class="kv">
                  <span>Timestamp</span>
                  <span class="val"><?php echo date('Y-m-d H:i:s'); ?></span>
                </div>
              </div>
            </div>

            <!-- 2) Campos con Datos -->
            <div class="gov-debug-box">
              <div class="gov-box-head">
                <div class="ttl text-success">
                  <i class="feather icon-check-circle"></i> Campos con Datos
                </div>
                <span class="chip chip-success"><?php echo count($variablesConDatos); ?></span>
              </div>

              <div class="gov-table-wrap">
                <table class="table table-sm table-bordered mb-0 gov-table-mini">
                  <thead>
                    <tr>
                      <th>Variable</th>
                      <th class="text-right">Valor</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php foreach ($variablesConDatos as $var => $valor): ?>
                      <tr>
                        <td class="small"><code><?php echo htmlspecialchars($var); ?></code></td>
                        <td class="text-right"><strong><?php echo number_format((float)$valor, 0); ?></strong></td>
                      </tr>
                    <?php endforeach; ?>
                  </tbody>
                </table>
              </div>
            </div>

            <!-- 3) Campos sin Datos -->
            <div class="gov-debug-box">
              <div class="gov-box-head">
                <div class="ttl text-warning">
                  <i class="feather icon-alert-circle"></i> Campos sin Datos
                </div>
                <span class="chip chip-warning"><?php echo count($variablesSinDatos); ?></span>
              </div>

              <div class="gov-note-warning">
                No disponibles en ArcGIS o sin valores para el municipio
              </div>

              <div class="gov-table-wrap">
                <table class="table table-sm table-bordered mb-0 gov-table-mini">
                  <thead>
                    <tr>
                      <th>Variable</th>
                      <th class="text-right">Valor</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php foreach ($variablesSinDatos as $var => $valor): ?>
                      <tr class="text-muted">
                        <td class="small"><code><?php echo htmlspecialchars($var); ?></code></td>
                        <td class="text-right"><?php echo is_numeric($valor) ? number_format((float)$valor,0) : htmlspecialchars((string)$valor); ?></td>
                      </tr>
                    <?php endforeach; ?>
                  </tbody>
                </table>
              </div>
            </div>

          </div><!-- /gov-debug-grid -->

        </div>
      </div>
    </div>
  </div>
</div>
<!-- Fin ROW 3 -->
        <!-- MODALES geolocalizacion-->
    <div class="card-body">
      <div id="modalGeocalizacion" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
          <div class="modal-content">
            <div class="modal-header">
              <h5 class="modal-title" id="exampleModalCenterTitle">Geolocalización</h5>
          
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

      <!-- Modal datos municipio -->
      <div id="modalMunicipio" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="modalMunicipioTitle" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable" role="document">
          <div class="modal-content">
            <div class="modal-header text-white" style="background: linear-gradient(135deg, #234162 0%, #1c3550 45%, #0e2237 100%);">
              <h5 class="modal-title" id="modalMunicipioTitle"><i class="feather icon-map-pin"></i> Datos del Municipio</h5>
              <button type="button" class="btn-cerrar-modal" aria-label="Close" style="background:none;border:none;color:#fff;font-size:1.5rem;line-height:1;cursor:pointer;padding:0 4px;">&times;</button>
            </div>
            <div class="modal-body" id="modalMunicipioBody" style="max-height: 75vh; overflow-y: auto;">
              <div class="text-center">
                <div class="spinner-border text-primary" role="status">
                  <span class="sr-only">Cargando...</span>
                </div>
                <p class="mt-2">Cargando datos del municipio...</p>
              </div>
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-secondary btn-cerrar-modal">Cerrar</button>
            </div>
          </div>
        </div>
      </div>
      
    <!-- ROW 4 MAPA -->
    <div class="row mb-3">

      <!-- Mapa (8/12) -->
      <div class="col-lg-8 col-md-7">
        <div class="card shadow-sm h-100">
          <div class="card-header d-flex justify-content-between align-items-center" style="background: linear-gradient(135deg, #0b1220 0%, #111a2a 40%, #0b1220 100%);">
            <h6 class="mb-0 text-white"><i class="feather icon-map"></i> Mapa Interactivo PAE - ArcGIS Online</h6>
            <span class="badge badge-light-primary badgef" id="badgeMunicipiosMapa" style="font-size:.78rem;"></span>
          </div>
          <div class="card-body text-center p-2">
            <div id="contenido-mapa-pae" class="w-100">
              <?php
                require_once './admin/classes/Colombia.php';
                $arrMapaPae = ['codigo_departamento' => Util::getDepartamentoPrincipal()];
                $dataMapaPae = Colombia::getInformacionParaMapaPaeArcgis($arrMapaPae);
                $municipiosMapaPae = $dataMapaPae['output']['response'] ?? [];
              ?>
              <!-- Estilos de fuentes para los nombres del mapa -->
              <style>
                #mapa-pae-svg text { font-family: 'IBM Plex Sans', sans-serif; font-size: 10px; fill: #1e293b; pointer-events: none; }
              </style>
              <svg id="mapa-pae-svg" xmlns="http://www.w3.org/2000/svg" viewBox="40 40 1000 1180" width="100%" height="auto">
                <?php foreach ($municipiosMapaPae as $value):
                    $nombreApiSvg = !empty($value['nombre_api_arcgis_pae'])
                        ? $value['nombre_api_arcgis_pae']
                        : strtoupper(str_replace([' ', '-'], '_', $value['municipio']));
                ?>
                <g id="pae_<?php echo strtoupper($value['path']); ?>">
                  <path
                    d="<?php echo $value['d']; ?>"
                    fill="<?php echo getColorByNumPAEArcgis($value['total'] ?? 0); ?>"
                    class="municipios mapaClick pae-mapa-municipio"
                    data-municipio="<?php echo htmlspecialchars($nombreApiSvg); ?>"
                    data-codigo="<?php echo $value['codigo_muncipio']; ?>"
                    data-name="<?php echo strtolower($value['municipio']); ?>"
                    data-total="<?php echo intval($value['total'] ?? 0); ?>"
                    title="<?php echo strtoupper($value['municipio']); ?>"
                    stroke="#94a3b8" stroke-miterlimit="10" stroke-width="0.5px"
                    style="cursor:pointer;transition:fill .2s,opacity .2s;">
                  </path>
                </g>
                <?php endforeach; ?>
                <!-- Nombres de municipios en el mapa -->
                <?php
                  // Leer solo los elementos <text> (líneas 35 en adelante)
                  $nombresFile = file('./nombres_mapa_santander.php');
                  $enStyle = false;
                  foreach ($nombresFile as $linea) {
                      if (strpos($linea, '<style') !== false) { $enStyle = true; }
                      if ($enStyle) {
                          if (strpos($linea, '</style>') !== false) { $enStyle = false; }
                          continue;
                      }
                      echo $linea;
                  }
                ?>
              </svg>
            </div>
          </div>
        </div>
      </div>

      <!-- Leyenda + info lateral (4/12) -->
      <div class="col-lg-4 col-md-5 mt-3 mt-md-0">
        <div class="card shadow-sm h-100">
          <div class="card-header" style="background: linear-gradient(135deg, #0b1220 0%, #111a2a 40%, #0b1220 100%);">
            <h6 class="mb-0 text-white"><i class="feather icon-info"></i> Leyenda: Sedes PAE</h6>
          </div>
          <div class="card-body">
            <div class="d-flex flex-column" style="gap:10px;">
              <div class="d-flex align-items-center" style="gap:10px;">
                <span style="display:inline-block;width:22px;height:22px;border-radius:6px;background:#10b981;flex-shrink:0;box-shadow:inset 0 0 0 1px rgba(0,0,0,.15);"></span>
                <span style="font-weight:700;color:#334155;">1 – 3 sedes</span>
              </div>
              <div class="d-flex align-items-center" style="gap:10px;">
                <span style="display:inline-block;width:22px;height:22px;border-radius:6px;background:#3b82f6;flex-shrink:0;box-shadow:inset 0 0 0 1px rgba(0,0,0,.15);"></span>
                <span style="font-weight:700;color:#334155;">4 – 6 sedes</span>
              </div>
              <div class="d-flex align-items-center" style="gap:10px;">
                <span style="display:inline-block;width:22px;height:22px;border-radius:6px;background:#f59e0b;flex-shrink:0;box-shadow:inset 0 0 0 1px rgba(0,0,0,.15);"></span>
                <span style="font-weight:700;color:#334155;">7 – 10 sedes</span>
              </div>
              <div class="d-flex align-items-center" style="gap:10px;">
                <span style="display:inline-block;width:22px;height:22px;border-radius:6px;background:#f97316;flex-shrink:0;box-shadow:inset 0 0 0 1px rgba(0,0,0,.15);"></span>
                <span style="font-weight:700;color:#334155;">11 – 20 sedes</span>
              </div>
              <div class="d-flex align-items-center" style="gap:10px;">
                <span style="display:inline-block;width:22px;height:22px;border-radius:6px;background:#ef4444;flex-shrink:0;box-shadow:inset 0 0 0 1px rgba(0,0,0,.15);"></span>
                <span style="font-weight:700;color:#334155;">+ 20 sedes</span>
              </div>
              <div class="d-flex align-items-center" style="gap:10px;">
                <span style="display:inline-block;width:22px;height:22px;border-radius:6px;background:#EEF2F7;flex-shrink:0;box-shadow:inset 0 0 0 1px rgba(0,0,0,.15);"></span>
                <span style="font-weight:700;color:#334155;">Sin datos</span>
              </div>
            </div>
            <hr>
            <p class="text-muted mb-1" style="font-size:.82rem;">
              <i class="feather icon-mouse-pointer"></i> Haz clic sobre un municipio del mapa para ver su detalle PAE.
            </p>
            <p class="text-muted mb-0" style="font-size:.82rem;">
              <i class="feather icon-filter"></i> Si seleccionas un municipio en los filtros, solo ese será interactivo.
            </p>
          </div>
        </div>
      </div>

    </div>



      <div class="modal fade bd-example-modal-sm" tabindex="-1" role="dialog" aria-labelledby="mySmallModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-sm">
          <div class="modal-content">
            <div class="modal-header">
              <h5 class="modal-title h4" id="mySmallModalLabel"> Grafico Elementos Utilizados para el almacenamiento de alimentos</h5>
              <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            </div>
            <div class="modal-body">
              <div id="pie-chart-1" style="width:100%"></div>
            </div>
          </div>
        </div>
      </div>
    </div>

  </div><!-- pcoded-content -->
</div><!-- pcoded-main-container -->

<!-- Google Maps JavaScript API -->
<script async defer src="https://maps.googleapis.com/maps/api/js?key=AIzaSyAbqZVEnSpIPF5dKe89pju2rmQaM58wvY0&callback=initMap"></script>

<!-- ✅ Highcharts -->
<script src="https://code.highcharts.com/highcharts.js"></script>

<script>
/* ==========================================
   Highcharts Gov SaaS Defaults (Donuts)
   (IMPORTANTE: va después de highcharts.js)
   ========================================== */
if (window.Highcharts) {
  Highcharts.setOptions({
    credits: { enabled: false },
    chart: {
      backgroundColor: 'transparent',
      style: { fontFamily: 'system-ui, -apple-system, Segoe UI, Roboto, Arial' },
      spacing: [10, 10, 10, 10]
    },
    title: {
      style: { fontWeight: '1000', fontSize: '15px' }
    },
    subtitle: {
      style: { fontWeight: '900', fontSize: '12px' }
    },
    legend: {
      align: 'center',
      verticalAlign: 'bottom',
      itemMarginTop: 6,
      itemMarginBottom: 6,
      itemStyle: { fontWeight: '900' }
    },
    tooltip: {
      borderRadius: 12,
      shadow: true,
      backgroundColor: 'rgba(15,23,42,.92)',
      style: { color: '#fff', fontWeight: '900', fontSize: '12px' }
    },
    plotOptions: {
      pie: {
        borderWidth: 0,
        dataLabels: {
          enabled: true,
          distance: -18,
          style: { fontWeight: '1000', fontSize: '12px', color: '#fff' },
          formatter: function(){
            // solo muestra % si es significativo
            return (this.percentage >= 7) ? (Highcharts.numberFormat(this.percentage, 1) + '%') : '';
          }
        },
        showInLegend: true
      }
    }
  });

  // Helper: centro premium (pill + total)
  window.PAE_STYLE_DONUT_CENTER = function(chart, statusText, totalText){
    try{
      const r = chart.renderer;
      const cx = chart.plotLeft + chart.plotWidth / 2;
      const cy = chart.plotTop + chart.plotHeight / 2;

      // limpia si existe
      if (chart.__paeCenterGroup) chart.__paeCenterGroup.destroy();
      chart.__paeCenterGroup = r.g('paeCenter').add();

      // pill
      const pillW = 120, pillH = 30;
      r.rect(cx - pillW/2, cy - 26, pillW, pillH, 16)
        .attr({
          fill: 'rgba(255,255,255,.92)',
          stroke: 'rgba(15,23,42,.10)',
          'stroke-width': 1
        })
        .add(chart.__paeCenterGroup);

      r.text(statusText || 'Estado', cx, cy - 6)
        .css({ fontSize: '12px', fontWeight: '1000', color: '#0f172a' })
        .attr({ align: 'center' })
        .add(chart.__paeCenterGroup);

      // total
      r.text(String(totalText ?? ''), cx, cy + 22)
        .css({ fontSize: '22px', fontWeight: '1100', color: '#0f172a' })
        .attr({ align: 'center' })
        .add(chart.__paeCenterGroup);

    } catch(e){
      console.log('[PAE] Center style error:', e);
    }
  };
}
</script>

<!-- ✅ ApexCharts -->
<script src="assets/js/plugins/apexcharts.min.js"></script>

<!-- ✅ Script del tema para el menú lateral -->
<script src="assets/js/pcoded.js"></script>

<!-- ✅ Scripts del template -->
<?php include 'admin/include/gerenic_script.php'; ?>

<!-- ✅ Opcional iconos -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css">

<!-- ✅ Mapa geo -->
<script src="<?php echo Util::versionar('./admin/js/pae_mapa_geo.js'); ?>"></script>


<script>
/* ===========================
   Highcharts - Premium defaults
   (DESPUÉS de cargar Highcharts.js)
   =========================== */
if (window.Highcharts) {
  Highcharts.setOptions({
    title: { style: { fontWeight: '900' } },
    subtitle: { style: { fontWeight: '700' } },
    chart: {
      backgroundColor: 'transparent',
      style: { fontFamily: 'system-ui, -apple-system, Segoe UI, Roboto, Arial' },
      spacing: [10, 10, 10, 10]
    },
    credits: { enabled: false },
    legend: {
      itemStyle: { fontWeight: '800' },
      itemHoverStyle: { fontWeight: '900' }
    },
    tooltip: {
      borderRadius: 12,
      shadow: true,
      style: { fontWeight: '700' }
    },
    xAxis: {
      gridLineWidth: 0,
      lineColor: 'rgba(15,23,42,.12)',
      tickColor: 'rgba(15,23,42,.12)',
      labels: { style: { fontWeight: '700' } }
    },
    yAxis: {
      gridLineColor: 'rgba(15,23,42,.10)',
      title: { style: { fontWeight: '800' } },
      labels: { style: { fontWeight: '700' } }
    },
    plotOptions: {
      series: { borderWidth: 0, dataLabels: { style: { fontWeight: '900' } } },
      pie: { allowPointSelect: true, cursor: 'pointer', dataLabels: { enabled: true, style: { fontWeight: '900' } } }
    }
  });
}
</script>

<script>
/* ===========================
   LOADER: NO se queda pegado
   - oculta en DOMContentLoaded
   - oculta en window.load
   - fallback fuerte 2.5s
   - loader durante AJAX
   =========================== */
(function () {
  const loader = document.getElementById('pageLoader');
  if (!loader) return;

  function showLoader(){
    loader.classList.add('active');
    loader.setAttribute('aria-busy', 'true');
    loader.style.display = 'flex';
  }

  function hideLoader(){
    loader.classList.remove('active');
    loader.setAttribute('aria-busy', 'false');
    loader.style.display = 'none';
  }

  // Arranca visible (por si el HTML carga super rápido)
  showLoader();

  // ✅ No espera maps: apenas haya DOM, lo quita
  document.addEventListener('DOMContentLoaded', hideLoader);

  // ✅ Si todo carga normal
  window.addEventListener('load', hideLoader);

  // ✅ Fallback duro
  setTimeout(hideLoader, 2500);

  // ✅ Loader durante AJAX (si hay)
  if (window.jQuery) {
    jQuery(document).ajaxStart(showLoader);
    jQuery(document).ajaxStop(function(){ setTimeout(hideLoader, 250); });
  }
})();
</script>

<script>
/* Tabs: mantiene tu funcionalidad */
(function () {
  function initTabsPAE() {
    const tab = document.getElementById('myTab');
    if (!tab) return;

    tab.addEventListener('click', function (e) {
      const a = e.target.closest('a.nav-link');
      if (!a) return;

      e.preventDefault();

      tab.querySelectorAll('a.nav-link').forEach(x => x.classList.remove('active'));
      a.classList.add('active');

      const seccion = a.getAttribute('data-seccion');
      if (seccion && typeof window.mostrarSeccion === 'function') {
        window.mostrarSeccion(seccion);
      }
    }, true);
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initTabsPAE);
  } else {
    initTabsPAE();
  }
})();
</script>

<script>
// Toggle panel debug
$(document).on('click', '#btnDebugToggle', function() {
  var $panel = $('#collapseDebug');
  var $icon  = $('#iconDebugToggle');
  if ($panel.is(':visible')) {
    $panel.slideUp(200);
    $icon.removeClass('icon-chevron-up').addClass('icon-chevron-down');
  } else {
    $panel.slideDown(200);
    $icon.removeClass('icon-chevron-down').addClass('icon-chevron-up');
  }
});
</script>

</body>
</html>