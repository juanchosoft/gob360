<?php
    include './admin/include/head.php';
    require './admin/include/generic_classes.php';

    $vigencia = date('Y');

    // Sin consulta a la API al cargar la página.
    $totalProyectos       = 0;
    $valorTotal           = 0;
    $valorEjecutado       = 0;
    $aporteGobernacion    = 0;
    $aporteMunicipal      = 0;
    $aporteNacional       = 0;
    $promedioAvanceFisico = 0;
    $promedioAvanceFinan  = 0;
    $estados              = [];
    $provincias           = [];
    $proyectos            = [];
    $resumenData          = null;
    $errorApi             = '';

    // Si en tu código real existe $provincia, evitamos warning:
    $provincia = $provincia ?? 'Todas';

    // Cargar municipios con nombre_api_rpc para el filtro del formulario
    require_once './admin/classes/Colombia.php';
    $municipiosSelect = Colombia::getMunicipiosSvg('68');
    usort($municipiosSelect, fn($a, $b) => strcmp($a['municipio'], $b['municipio']));
?>

<style>
    :root {
        --nav-blue: #20427F;      /* color base */
        --nav-blue-2: #132b52;
        --nav-blue-3: #2e58a8;

        --ink: #0f172a;
        --muted: #0f151f;
        --line: rgba(2,6,23,.10);
        --bg: #f7f9fc;

        --radius-xl: 18px;
        --radius-lg: 14px;
        --radius-md: 12px;

        --shadow-soft: 0 10px 28px rgba(2,6,23,.10);
        --shadow-mid: 0 16px 42px rgba(2,6,23,.16);

        --glass: rgba(255,255,255,.72);
        --glass-line: rgba(255,255,255,.45);
    }

    body { background: radial-gradient(1200px 600px at 20% -10%, rgba(32,66,127,.14), transparent 55%),
                      radial-gradient(900px 500px at 90% 0%, rgba(46,88,168,.10), transparent 60%),
                      var(--bg) !important; }

    /* ====== HERO HEADER (WOW) ====== */
    .rpc-hero {
        border-radius: 22px;
        border: 1px solid rgba(2,6,23,.08);
        background: linear-gradient(135deg, rgba(32,66,127,.12), rgba(19,43,82,.06));
        box-shadow: var(--shadow-soft);
        padding: 18px 18px 14px;
        position: relative;
        overflow: hidden;
    }
    .rpc-hero:before {
        content:"";
        position:absolute;
        inset:-2px;
        background: radial-gradient(800px 200px at 20% 0%, rgba(32,66,127,.20), transparent 60%),
                    radial-gradient(800px 200px at 80% 10%, rgba(46,88,168,.16), transparent 62%);
        pointer-events:none;
        opacity:.9;
    }
    .rpc-hero > * { position: relative; z-index: 1; }

    .rpc-title {
        font-weight: 1000;
        color: var(--ink);
        margin: 0;
        letter-spacing: .2px;
    }
    .rpc-sub {
        color: var(--muted);
        margin: 6px 0 0;
        font-weight: 600;
        font-size: .92rem;
    }

    .rpc-pill {
        display:inline-flex;
        align-items:center;
        gap:.5rem;
        padding: 8px 12px;
        border-radius: 999px;
        font-weight: 900;
        font-size: .82rem;
        border: 1px solid rgba(2,6,23,.10);
        background: rgba(255,255,255,.75);
        backdrop-filter: blur(10px);
        box-shadow: 0 10px 20px rgba(2,6,23,.08);
    }
    .rpc-pill i { color: var(--nav-blue); }

    /* ====== Cards ====== */
    .card {
        border-radius: var(--radius-xl) !important;
        border: 1px solid var(--line) !important;
        box-shadow: var(--shadow-soft) !important;
        overflow: hidden;
        background: #fff;
    }
    .card:hover { box-shadow: var(--shadow-mid) !important; }

    .card-header {
        background: linear-gradient(135deg, rgba(32,66,127,.10), rgba(19,43,82,.06)) !important;
        border-bottom: 1px solid rgba(2,6,23,.10) !important;
        font-weight: 1000;
    }
    .card-header h5, .card-header h6 { font-weight: 1000 !important; margin: 0; color: var(--ink); }

    /* ====== Filter bar (premium) ====== */
    .rpc-filters {
        border-radius: 18px;
        border: 1px solid rgba(2,6,23,.10);
        background: linear-gradient(180deg, rgba(255,255,255,.85), rgba(255,255,255,.70));
        backdrop-filter: blur(10px);
        box-shadow: 0 16px 34px rgba(2,6,23,.10);
    }

    .form-control {
        border-radius: var(--radius-md) !important;
        border: 1px solid rgba(15,23,42,.16) !important;
        font-weight: 800 !important;
        min-height: 44px;
    }
    .form-control:focus {
        border-color: rgba(32,66,127,.55) !important;
        box-shadow: 0 0 0 .2rem rgba(32,66,127,.18) !important;
    }

    .btn.btn-primary {
        background: linear-gradient(135deg, var(--nav-blue), var(--nav-blue-2)) !important;
        border: none !important;
        font-weight: 900 !important;
        border-radius: 14px !important;
        box-shadow: 0 14px 28px rgba(32,66,127,.25);
        min-height: 44px;
    }
    .btn.btn-primary:hover { filter: brightness(1.03); transform: translateY(-1px); }
    .btn.btn-outline-secondary {
        border-radius: 14px !important;
        min-height: 44px;
        font-weight: 900 !important;
    }

    .header-api-badge {
        background: #101169;
        color: #fff;
        padding: 7px 14px;
        border-radius: 999px;
        font-size: 0.82rem;
        font-weight: 900;
        box-shadow: 0 10px 22px rgba(2,6,23,.10);
    }

    /* ====== Map ====== */
    .mapa-rpc-municipio { cursor: pointer; transition: fill 0.25s ease, opacity 0.2s ease; }
    .mapa-rpc-municipio:hover { opacity: 0.78; stroke-width: 1px !important; }

    .leyenda-item { display: inline-flex; align-items: center; font-size: 0.82rem; font-weight: 800; }
    .leyenda-color { width: 18px; height: 18px; border-radius: 6px; margin-right: 6px; box-shadow: inset 0 0 0 1px rgba(0,0,0,.15); }

    #tooltipMapa {
        position: fixed;
        background: rgba(15, 23, 42, 0.92);
        color: #fff;
        padding: 6px 12px;
        border-radius: 10px;
        font-size: 0.82rem;
        font-weight: 800;
        pointer-events: none;
        z-index: 10000;
        display: none;
        white-space: nowrap;
        box-shadow: 0 8px 22px rgba(0,0,0,0.25);
    }

    /* ====== Top municipios (premium list) ====== */
    #topMunicipiosContainer { max-height: 520px; overflow-y: auto; }
    #topMunicipiosContainer::-webkit-scrollbar { width: 6px; }
    #topMunicipiosContainer::-webkit-scrollbar-thumb { background: rgba(32,66,127,.35); border-radius: 10px; }

    .top-mun-item {
        display:flex; justify-content:space-between; align-items:center;
        padding: 10px 14px;
        border-bottom: 1px solid rgba(2,6,23,.06);
        transition: background .15s ease;
    }
    .top-mun-item:hover { background: rgba(32,66,127,0.04); }
    .top-mun-item:last-child { border-bottom: none; }

    /* ====== Loader overlay (sin tocar tu preloader original) ====== */
    #dashLoader {
        position: fixed; inset: 0;
        background: rgba(255,255,255,0.75);
        z-index: 9999;
        display: none;
        justify-content: center;
        align-items: center;
        backdrop-filter: blur(6px);
    }
    #dashLoader .spinner-border { width: 3rem; height: 3rem; color: var(--nav-blue); }

    @media (max-width: 992px) {
        .rpc-hero { padding: 16px; }
        .rpc-pill { margin-top: 10px; }
        #topMunicipiosContainer { max-height: 360px; }
    }
    /* ===========================
   UI UPGRADE PRO (SAFE)
   =========================== */

/* Inputs compactos premium */
.rpc-filters .form-control{
  min-height: 40px !important;
  font-size: .90rem !important;
  padding: .55rem .75rem !important;
}
.rpc-filters textarea.form-control{
  min-height: 70px !important;
  line-height: 1.25 !important;
  resize: vertical;
}

/* Select “premium” (sin plugins) */
.rpc-filters select.form-control{
  appearance: none;
  -webkit-appearance: none;
  -moz-appearance: none;
  padding-right: 2.25rem !important;
  background-image:
    linear-gradient(45deg, transparent 50%, rgba(15,23,42,.75) 50%),
    linear-gradient(135deg, rgba(15,23,42,.75) 50%, transparent 50%),
    linear-gradient(to right, rgba(15,23,42,.10), rgba(15,23,42,.10));
  background-position:
    calc(100% - 18px) 50%,
    calc(100% - 12px) 50%,
    calc(100% - 2.35rem) 50%;
  background-size:
    6px 6px,
    6px 6px,
    1px 70%;
  background-repeat: no-repeat;
}

/* Labels más “SaaS” */
.rpc-filters .form-label{
  font-size: .82rem;
  letter-spacing: .2px;
  color: rgba(15,23,42,.85);
}

/* Botones compactos */
.rpc-filters .btn{
  min-height: 40px !important;
  padding: .55rem .9rem !important;
}
.rpc-filters .btn.btn-outline-secondary{
  width: 44px;
  padding: .55rem .6rem !important;
}

/* Textarea BPIN: estilo terminal pro */
#inputMultiplesBpin{
  background: linear-gradient(180deg, rgba(2,6,23,.03), rgba(255,255,255,1)) !important;
  border: 1px solid rgba(15,23,42,.16) !important;
  font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono", "Courier New", monospace !important;
  font-size: .88rem !important;
}

/* ========== MAPA más compacto, sin tocar SVG ========== */
#contenido-mapa-rpc{
  /* limita altura del bloque del mapa */
  max-height: 520px;
  overflow: hidden;
  display: flex;
  justify-content: center;
  align-items: center;
}
#contenido-mapa-rpc svg{
  /* encaja sin deformar */
  width: 100%;
  height: auto;
  max-height: 520px;
}

/* En tablets baja un poco más */
@media (max-width: 1200px){
  #contenido-mapa-rpc, #contenido-mapa-rpc svg{ max-height: 550px; }
}
@media (max-width: 992px){
  #contenido-mapa-rpc, #contenido-mapa-rpc svg{ max-height: 550px; }
}

/* Leyenda más fina */
#leyendaMapa{
  padding: 10px 12px !important;
}
.leyenda-item{
  font-size: .80rem !important;
}

/* ========== TOP MUNICIPIOS más PRO ========== */
.top-mun-item{
  gap: 10px;
}
.top-mun-item .progress{
  border-radius: 999px;
  background: rgba(2,6,23,.06);
  overflow: hidden;
}
.top-mun-item .progress-bar{
  border-radius: 999px;
}

/* ===== MODALES PRO ===== */
.modal-content{
  border-radius: 20px !important;
  border: 1px solid rgba(2,6,23,.12) !important;
  overflow: hidden !important;
  box-shadow: 0 22px 60px rgba(2,6,23,.25) !important;
}
.modal-header{
  padding: 14px 16px !important;
  background: linear-gradient(135deg, var(--nav-blue), var(--nav-blue-2)) !important;
  color: #fff !important;
}
.modal-header .modal-title{
  font-weight: 1000 !important;
  letter-spacing: .2px;
}
.modal-header .close{
  opacity: 1 !important;
  text-shadow: none !important;
}
.modal-header .close span{
  color: #fff !important;
  font-size: 1.5rem;
}
.modal-body{
  background:
    radial-gradient(700px 220px at 20% 0%, rgba(32,66,127,.10), transparent 60%),
    radial-gradient(700px 220px at 80% 0%, rgba(46,88,168,.08), transparent 62%),
    #fff;
}
.modal-footer{
  border-top: 1px solid rgba(2,6,23,.08) !important;
  background: rgba(255,255,255,.75);
  backdrop-filter: blur(8px);
}

/* Tablas dentro de modal más premium */
.modal-body .table{
  border-radius: 14px;
  overflow: hidden;
}
.modal-body .thead-light th{
  background: rgba(32,66,127,.08) !important;
  border-color: rgba(2,6,23,.08) !important;
  font-weight: 900 !important;
  color: rgba(15,23,42,.9) !important;
}
.modal-body .table td, .modal-body .table th{
  border-color: rgba(2,6,23,.06) !important;
  vertical-align: middle !important;
}

/* KPIs del modal “card” (los que renderiza tu JS) */
.modal-body .p-3.rounded{
  border-radius: 16px !important;
  box-shadow: 0 12px 26px rgba(2,6,23,.08);
}

/* Badge pro */
.modal-body .badge{
  border-radius: 999px !important;
  font-weight: 900 !important;
  padding: .35rem .6rem !important;
}
/* ===========================
   TOP MUNICIPIOS = PLAY / RANKING (SAFE)
   =========================== */

#topMunicipiosContainer{
  padding: 10px 10px 6px;
  background:
    radial-gradient(520px 180px at 10% 0%, rgba(32,66,127,.12), transparent 60%),
    radial-gradient(520px 180px at 90% 10%, rgba(46,88,168,.10), transparent 62%),
    linear-gradient(180deg, rgba(255,255,255,.90), rgba(255,255,255,.72));
}

.top-mun-item{
  position: relative;
  padding: 12px 12px 12px 12px;
  border-radius: 16px;
  margin: 8px 2px;
  border: 1px solid rgba(2,6,23,.08);
  background: rgba(255,255,255,.82);
  backdrop-filter: blur(10px);
  box-shadow: 0 10px 22px rgba(2,6,23,.08);
  overflow: hidden;
  transition: transform .16s ease, box-shadow .16s ease, border-color .16s ease;
}

.top-mun-item:hover{
  transform: translateY(-2px);
  box-shadow: 0 16px 36px rgba(2,6,23,.14);
  border-color: rgba(32,66,127,.22);
}

/* Glow suave “play” */
.top-mun-item:before{
  content:"";
  position:absolute;
  inset:-2px;
  background:
    radial-gradient(280px 80px at 15% 0%, rgba(32,66,127,.18), transparent 60%),
    radial-gradient(240px 70px at 85% 10%, rgba(46,88,168,.14), transparent 62%);
  opacity:.9;
  pointer-events:none;
}

/* Contenido por encima del glow */
.top-mun-item > *{
  position: relative;
  z-index: 1;
}

/* Nombre + ranking más pro */
.top-mun-item small.font-weight-bold{
  font-size: .92rem !important;
  font-weight: 1000 !important;
  color: #0f172a !important;
  letter-spacing: .1px;
}

/* Badge conteo tipo “chip” */
.top-mun-item .badge{
  border-radius: 12px !important;
  font-weight: 1000 !important;
  padding: .30rem .55rem !important;
  font-size: .78rem !important;
  box-shadow: 0 10px 18px rgba(2,6,23,.12);
  border: 1px solid rgba(255,255,255,.55);
}

/* Barra tipo “player” */
.top-mun-item .progress{
  height: 10px !important;
  border-radius: 999px !important;
  background: rgba(2,6,23,.06) !important;
  overflow: hidden;
  box-shadow: inset 0 0 0 1px rgba(2,6,23,.06);
}

/* Barra con degradado + brillo */
.top-mun-item .progress-bar{
  border-radius: 999px !important;
  position: relative;
  box-shadow: 0 10px 18px rgba(32,66,127,.18);
}

/* “Highlight” animado (tipo equalizer) */
.top-mun-item .progress-bar:after{
  content:"";
  position:absolute;
  top:-40%;
  left:-35%;
  width: 55%;
  height: 180%;
  background: rgba(255,255,255,.25);
  transform: rotate(18deg);
  filter: blur(0.5px);
  animation: topMunShine 2.8s ease-in-out infinite;
  opacity: .85;
}

@keyframes topMunShine{
  0%   { left: -45%; opacity:.0; }
  20%  { opacity:.85; }
  60%  { left: 115%; opacity:.35; }
  100% { left: 115%; opacity:0; }
}

/* “Top 1” destacado automáticamente (primer item) */
#topMunicipiosContainer .top-mun-item:first-child{
  border-color: rgba(16,185,129,.25);
  box-shadow: 0 18px 44px rgba(16,185,129,.12), 0 14px 30px rgba(2,6,23,.10);
}

#topMunicipiosContainer .top-mun-item:first-child:before{
  background:
    radial-gradient(300px 90px at 20% 0%, rgba(16,185,129,.18), transparent 60%),
    radial-gradient(240px 70px at 85% 10%, rgba(32,66,127,.14), transparent 62%);
}

/* Scrollbar pro */
#topMunicipiosContainer::-webkit-scrollbar{
  width: 8px;
}
#topMunicipiosContainer::-webkit-scrollbar-track{
  background: rgba(2,6,23,.05);
  border-radius: 999px;
}
#topMunicipiosContainer::-webkit-scrollbar-thumb{
  background: rgba(32,66,127,.35);
  border-radius: 999px;
  border: 2px solid rgba(255,255,255,.7);
}
#topMunicipiosContainer::-webkit-scrollbar-thumb:hover{
  background: rgba(32,66,127,.55);
}
/* iconito play a la izquierda del nombre */
.top-mun-item small.font-weight-bold:before{
  content:"▶";
  display:inline-block;
  margin-right:8px;
  font-size:.72rem;
  color: rgba(32,66,127,.85);
  transform: translateY(-1px);
}
#topMunicipiosContainer .top-mun-item:first-child small.font-weight-bold:before{
  content:"★";
  color: rgba(16,185,129,.95);
}
/* ===========================
   MODAL NEXT LEVEL (SaaS)
   =========================== */

.modal-dialog{ padding: 18px; }
.modal-content{
  border-radius: 26px !important;
  border: 1px solid rgba(2,6,23,.12) !important;
  box-shadow: 0 36px 90px rgba(2,6,23,.30) !important;
  overflow: hidden !important;
}

.modal-header{
  padding: 18px 22px !important;
  background:
    radial-gradient(800px 140px at 15% 0%, rgba(255,255,255,.14), transparent 60%),
    linear-gradient(135deg, #1e3a8a, #0b1220) !important;
  color:#fff !important;
  border-bottom: none !important;
}
.modal-header .modal-title{
  font-weight: 1000 !important;
  letter-spacing: .35px;
  font-size: 1.08rem;
}
.modal-header .close span{ color:#fff !important; font-size: 1.7rem !important; }

.modal-body{
  padding: 28px !important;
  background:
    radial-gradient(700px 220px at 15% 0%, rgba(32,66,127,.10), transparent 60%),
    radial-gradient(600px 180px at 90% 0%, rgba(46,88,168,.08), transparent 62%),
    #ffffff;
}

.modal-footer{
  padding: 16px 22px !important;
  border-top: 1px solid rgba(2,6,23,.08) !important;
  background: rgba(248,250,252,.92);
  backdrop-filter: blur(10px);
}

/* Animación entrada */
.modal.fade .modal-dialog{
  transform: translateY(18px) scale(.98);
  transition: transform .18s ease;
}
.modal.show .modal-dialog{
  transform: translateY(0) scale(1);
}

/* ===========================
   KPI CARDS (arriba del modal)
   =========================== */
.modal-body .p-3.rounded{
  border-radius: 20px !important;
  box-shadow: 0 16px 42px rgba(2,6,23,.12);
  border: 1px solid rgba(2,6,23,.08);
}
.modal-body .p-3.rounded:hover{
  transform: translateY(-2px);
  transition: transform .14s ease;
}

/* ===========================
   ESTADOS: chips pro
   =========================== */
.estado-pill{
  display:inline-flex;
  align-items:center;
  gap:.45rem;
  padding: .42rem .74rem;
  border-radius: 999px;
  font-weight: 1000;
  font-size: .74rem;
  letter-spacing: .2px;
  border: 1px solid rgba(255,255,255,.40);
  box-shadow: 0 10px 22px rgba(2,6,23,.14);
  color:#fff;
  white-space: nowrap;
}
.estado-pill:before{
  content:"";
  width:8px; height:8px;
  border-radius:999px;
  background: rgba(255,255,255,.85);
  box-shadow: 0 0 0 3px rgba(255,255,255,.18);
}

/* Colores por estado */
.estado--proceso{ background: linear-gradient(135deg,#f59e0b,#d97706); }
.estado--liquidacion{ background: linear-gradient(135deg,#22c55e,#15803d); }
.estado--terminado{ background: linear-gradient(135deg,#2563eb,#1e40af); }
.estado--suspendido{ background: linear-gradient(135deg,#ef4444,#b91c1c); }
.estado--formulacion{ background: linear-gradient(135deg,#64748b,#334155); }
.estado--contratacion{ background: linear-gradient(135deg,#06b6d4,#0ea5e9); }
.estado--otro{ background: linear-gradient(135deg,#6b7280,#374151); }

/* ===========================
   TABLA tipo cards + aire
   =========================== */
.modal-body .table-responsive{
  border-radius: 18px;
  overflow: hidden;
  border: 1px solid rgba(2,6,23,.08);
  box-shadow: 0 12px 34px rgba(2,6,23,.10);
  background: rgba(255,255,255,.92);
}
.modal-body table.table{
  margin: 0 !important;
}
.modal-body .table thead th{
  position: sticky; top: 0;
  z-index: 2;
  background: rgba(32,66,127,.08) !important;
  border: none !important;
  font-weight: 1000 !important;
  font-size: .80rem !important;
  text-transform: uppercase;
  letter-spacing: .35px;
  padding: 11px 14px !important;
}
.modal-body .table td{
  border-top: 1px solid rgba(2,6,23,.06) !important;
  padding: 14px !important;
  vertical-align: middle !important;
}
.modal-body .table tbody tr:hover{
  background: rgba(32,66,127,.04) !important;
}

/* ===========================
   PROGRESS BAR next-level
   =========================== */
.pb{
  height: 14px !important;
  border-radius: 999px !important;
  background: rgba(2,6,23,.08) !important;
  overflow: hidden;
  box-shadow: inset 0 0 0 1px rgba(2,6,23,.08);
}
.pb .progress-bar{
  border-radius: 999px !important;
  font-weight: 1000;
  font-size: .72rem;
  letter-spacing: .15px;
  box-shadow: 0 10px 24px rgba(2,6,23,.16);
  position: relative;
}

/* brillo “player” */
.pb .progress-bar:after{
  content:"";
  position:absolute;
  top:-50%;
  left:-40%;
  width:50%;
  height:200%;
  background: rgba(255,255,255,.22);
  transform: rotate(18deg);
  animation: pbShine 2.7s ease-in-out infinite;
}
@keyframes pbShine{
  0%{ left:-50%; opacity:0; }
  20%{ opacity:.9; }
  60%{ left:120%; opacity:.35; }
  100%{ left:120%; opacity:0; }
}

/* Colores por porcentaje */
.pb--ok .progress-bar{ background: linear-gradient(90deg,#22c55e,#15803d) !important; }
.pb--warn .progress-bar{ background: linear-gradient(90deg,#fbbf24,#d97706) !important; }
.pb--bad .progress-bar{ background: linear-gradient(90deg,#ef4444,#b91c1c) !important; }
.pb--zero .progress-bar{ background: linear-gradient(90deg,#9ca3af,#6b7280) !important; }

/* ===========================
   Footer buttons premium
   =========================== */
.modal-footer .btn-primary{
  background: linear-gradient(135deg,#1e3a8a,#0b1220) !important;
  border: none !important;
  border-radius: 16px !important;
  font-weight: 1000 !important;
  padding: .62rem 1.35rem !important;
  box-shadow: 0 14px 30px rgba(2,6,23,.25);
}
.modal-footer .btn-secondary{
  border-radius: 16px !important;
  font-weight: 900 !important;
  padding: .62rem 1.25rem !important;
}
.badgef{
         background: #101169;
        color: #fff;
}
.badge-estado{
  display:inline-flex;
  align-items:center;
  gap:8px;
  padding:.35rem .6rem;
  border-radius:999px;
  color:#fff;
  font-weight:900;
  font-size:.78rem;
  line-height:1;
  white-space:nowrap;
  box-shadow:0 10px 22px rgba(2,6,23,.12);
}
.badge-estado .dot{
  width:8px;height:8px;border-radius:999px;
  background:rgba(255,255,255,.85);
  box-shadow:0 0 0 2px rgba(255,255,255,.25);
}
.progress.pb{ height:18px; border-radius:999px; overflow:hidden; background:rgba(2,6,23,.08); }
.progress.pb .progress-bar{ font-weight:900; color:#fff; text-shadow:0 1px 2px rgba(0,0,0,.25); }
<style>
/* ============================
   MODAL: PROYECTO (Ver más)
   SAFE: solo UI
============================ */

/* La celda proyecto: que envuelva y no se corte feo */
#modalMunicipioBody .table td:nth-child(2){
  white-space: normal !important;
  word-break: break-word;
}

/* Contenedor del texto + toggle */
.proj-wrap{
  display:flex;
  flex-direction:column;
  gap:6px;
  max-width: 520px; /* ajusta si quieres más ancho */
}

/* Por defecto: 3 líneas (SaaS) */
.proj-text{
  display:-webkit-box;
  -webkit-box-orient: vertical;
  -webkit-line-clamp: 3;
  overflow:hidden;
  line-height: 1.25;
}

/* Expandido: muestra completo */
.proj-wrap.is-open .proj-text{
  display:block;
  overflow: visible;
}

/* Botón ver más */
.proj-toggle{
  align-self:flex-start;
  border: 0;
  background: transparent;
  padding: 0;
  font-weight: 900;
  font-size: .78rem;
  color: #1e3a8a;
  cursor:pointer;
  text-decoration: underline;
  opacity:.95;
}
.proj-toggle:hover{ opacity:1; }
/* ===== Modal Municipio: Columna Proyecto con Ver más ===== */
#modalMunicipioBody .proj-wrap{
  display:flex;
  flex-direction:column;
  gap:6px;
  max-width: 620px;
}

#modalMunicipioBody .proj-text{
  display:-webkit-box;
  -webkit-box-orient: vertical;
  -webkit-line-clamp: 3;
  overflow:hidden;
  line-height:1.25;
  white-space: normal;
  word-break: break-word;
}

#modalMunicipioBody .proj-wrap.is-open .proj-text{
  display:block;
  overflow:visible;
}

#modalMunicipioBody .proj-toggle{
  align-self:flex-start;
  border:0;
  background:transparent;
  padding:0;
  font-weight:900;
  font-size:.78rem;
  color:#1e3a8a;
  cursor:pointer;
  text-decoration:underline;
  opacity:.95;
}

#modalMunicipioBody .proj-toggle:hover{ opacity:1; }
</style>


<body class="">
    <div id="tooltipMapa"></div>

    <!-- Loader overlay -->
    <div id="dashLoader">
        <div class="d-flex flex-column align-items-center justify-content-center h-100">
            <div class="spinner-border" role="status"></div>
            <p class="mt-3 font-weight-bold text-dark">Consultando API de Proyectos...</p>
        </div>
    </div>

    <!-- Pre-loader (se queda) -->
    <div class="loader-bg">
        <div class="loader-track">
            <div class="loader-fill"></div>
        </div>
    </div>

    <?php include './admin/include/navbar.php'; ?>
    <?php include './admin/include/header.php'; ?>

    <div class="pcoded-main-container">
        <div class="pcoded-content">

            <!-- HERO -->
            <div class="rpc-hero mb-3">
                <div class="d-flex flex-wrap align-items-start justify-content-between" style="gap:12px;">
                    <div>
                        <h4 class="rpc-title">
                            <i class="feather icon-map mr-1"></i>
                            Proyectos · Mapa Interactivo (API Rendición de Cuentas)
                        </h4>
                        <div class="rpc-sub">
                            Consulta en tiempo real desde API JSON-RPC externa · visualización por municipios
                        </div>
                    </div>

                    <div class="d-flex flex-wrap align-items-center" style="gap:10px;">
                        <span class="header-api-badge">
                            <i class="feather icon-zap"></i> API JSON-RPC 2.0
                        </span>

                        <span class="rpc-pill">
                            <i class="feather icon-layers"></i>
                            <span id="pillTotalProyectos"><?php echo $totalProyectos; ?></span> proyectos
                        </span>

                        <a href="panel_proyectos.php" class="btn btn-primary">
                            <i class="feather icon-bar-chart-2"></i> Abrir Panel
                        </a>
                    </div>
                </div>
            </div>

            <!-- Panel de Búsqueda Avanzada (Premium) -->
            <div class="card rpc-filters mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h6 class="mb-0"><i class="feather icon-search"></i> Consulta de Proyectos</h6>
                    <small class="text-muted font-weight-bold">sigid.santander.gov.co</small>
                </div>
                <div class="card-body">

                    <div class="row mb-3">
                        <div class="col-md-2">
                            <label class="form-label font-weight-bold">Vigencia</label>
                            <select class="form-control form-control-sm" id="selectVigencia">
                                <option value="">-- Todas --</option>
                                <?php for ($y = 2020; $y <= 2026; $y++): ?>
                                    <option value="<?php echo $y; ?>" <?php echo ($vigencia == $y ? 'selected' : ''); ?>>
                                        <?php echo $y; ?>
                                    </option>
                                <?php endfor; ?>
                            </select>
                        </div>

                        <div class="col-md-2">
                            <label class="form-label font-weight-bold">Código BPIN</label>
                            <input type="text" class="form-control form-control-sm" id="inputBpin" placeholder="Ej: 2024004680222" maxlength="20">
                        </div>

                        <div class="col-md-3">
                            <label class="form-label font-weight-bold">Dependencia</label>
                            <select class="form-control form-control-sm" id="selectDependencia">
                                <option value="Todas">Todas</option>
                            </select>
                        </div>

                        <div class="col-md-2">
                            <label class="form-label font-weight-bold">Municipio</label>
                            <select class="form-control form-control-sm" id="selectMunicipio">
                                <option value="Todos">Todos</option>
                                <?php foreach ($municipiosSelect as $mun):
                                    if (empty($mun['nombre_api_rpc'])) continue; ?>
                                    <option value="<?php echo htmlspecialchars($mun['nombre_api_rpc']); ?>">
                                        <?php echo htmlspecialchars(ucwords(strtolower($mun['municipio']))); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="col-md-1">
                            <label class="form-label font-weight-bold">Provincia</label>
                            <select class="form-control form-control-sm" id="selectProvincia">
                                <option value="Todas">Todas</option>
                                <?php foreach ($provincias as $prov): ?>
                                    <option value="<?php echo htmlspecialchars($prov); ?>" <?php echo ($provincia === $prov ? 'selected' : ''); ?>>
                                        <?php echo htmlspecialchars($prov); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="col-md-2 d-flex align-items-end">
                            <button type="button" class="btn btn-primary btn-block mr-1" id="btnBuscar">
                                <i class="feather icon-search"></i> Buscar
                            </button>
                            <button type="button" class="btn btn-outline-secondary" id="btnLimpiar" title="Limpiar filtros">
                                <i class="feather icon-x"></i>
                            </button>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-8">
                            <label class="form-label font-weight-bold">
                                <i class="feather icon-layers"></i> Múltiples BPIN
                                <small class="text-muted font-weight-normal">(opcional - separados por coma, punto y coma o Enter)</small>
                            </label>
                            <textarea class="form-control form-control-sm" id="inputMultiplesBpin" rows="2"
                                      placeholder="2024004680041, 2024004680010, 2024004680006"
                                      style="font-family: monospace; font-size: 0.9rem;"></textarea>
                        </div>
                        <div class="col-md-4 d-flex align-items-end">
                            <div class="w-100">
                                <span id="contadorBpin" class="badge badge-light-primary badgef" style="font-size:0.85rem;">0 BPIN ingresados</span>
                                <small class="text-muted d-block mt-1">
                                    Si ingresa BPIN aquí, se consultarán todos. Si también selecciona vigencia, se combinará la búsqueda.
                                </small>
                            </div>
                        </div>
                    </div>

                    <div class="row mt-2">
                        <div class="col-md-12">
                            <span id="infoConsulta" class="text-muted" style="font-size:0.82rem;">
                                <i class="feather icon-info"></i> Seleccione vigencia, ingrese BPIN, o combine ambos filtros.
                            </span>
                        </div>
                    </div>

                    <div class="mt-3 text-muted" style="font-size:.82rem;">
                        <i class="feather icon-arrow-right"></i>
                        Tip: después de buscar, abre el <b>Panel</b> para ver KPIs, tabla y gráficos.
                    </div>
                </div>
            </div>

            <?php if (!empty($errorApi)): ?>
                <div class="alert alert-warning" role="alert">
                    <h5><i class="feather icon-alert-triangle"></i> No se pudieron obtener los datos</h5>
                    <p><?php echo htmlspecialchars($errorApi); ?></p>
                    <hr>
                    <p class="mb-0">
                        Verifique URL/credenciales en <code>config.ini</code> sección <code>[proyectos_rpc]</code>.
                    </p>
                </div>
            <?php endif; ?>

            <!-- SOLO MAPA + TOP MUNICIPIOS -->
            <div id="dashContent">

                <div class="row">
                    <div class="col-lg-8 col-md-7">
                        <div class="card">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h6><i class="feather icon-map"></i> Mapa de Municipios con Proyectos</h6>
                                <span class="badge badge-light-primary badgef" id="badgeMunicipios">0 municipios</span>
                            </div>

                            <div class="card-body text-center p-2">

                                <div class="d-flex justify-content-center flex-wrap mb-3 p-2 rounded"
                                     id="leyendaMapa"
                                     style="background:rgba(255,255,255,.72);border:1px solid rgba(2,6,23,.10);gap:16px;backdrop-filter: blur(10px);">
                                    <span class="leyenda-item">
                                        <span class="leyenda-color" style="background:#EEF2F7;"></span>
                                        <span style="color:#334155;">Sin proyectos</span>
                                    </span>
                                    <span class="leyenda-item">
                                        <span class="leyenda-color" style="background:#93c5fd;"></span>
                                        <span style="color:#334155;">1 - 5</span>
                                    </span>
                                    <span class="leyenda-item">
                                        <span class="leyenda-color" style="background:#3b82f6;"></span>
                                        <span style="color:#334155;">6 - 15</span>
                                    </span>
                                    <span class="leyenda-item">
                                        <span class="leyenda-color" style="background:#1e40af;"></span>
                                        <span style="color:#334155;">16+</span>
                                    </span>
                                </div>

                                <div id="contenido-mapa-rpc" class="w-100">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="40 40 1000 1200" width="100%" height="auto">
                                        <?php
                                        require_once './admin/classes/Colombia.php';
                                        $municipiosSvg = Colombia::getMunicipiosSvg('68');
                                        foreach ($municipiosSvg as $value): ?>
                                            <g id="rpc_<?= strtoupper($value['path']) ?>">
                                                <path
                                                    d="<?= $value['d'] ?>"
                                                    fill="#EEF2F7"
                                                    class="municipios mapa-rpc-municipio"
                                                    data-municipio="<?= htmlspecialchars($value['nombre_api_rpc'] ?? '') ?>"
                                                    data-codigo="<?= $value['codigo_muncipio'] ?>"
                                                    data-nombre="<?= strtolower($value['municipio']) ?>"
                                                    title="<?= strtoupper(str_replace('-', ' ', $value['nombre_mapa'])) ?>"
                                                    stroke="#000" stroke-miterlimit="10" stroke-width="0.3px">
                                                </path>
                                            </g>
                                        <?php endforeach; ?>
                                        <?php require_once 'nombres_mapa_santander.php' ?>
                                    </svg>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-4 col-md-5">
                        <div class="card">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h6><i class="feather icon-map-pin"></i> Top Municipios</h6>
                                <a href="panel_proyectos.php" class="btn btn-sm btn-primary">
                                    <i class="feather icon-bar-chart-2"></i>
                                </a>
                            </div>

                            <div class="card-body p-0" id="topMunicipiosContainer">
                                <div class="text-center py-4">
                                    <i class="feather icon-inbox text-muted" style="font-size:2rem;"></i>
                                    <p class="text-muted mt-2 mb-0">Realice una búsqueda para ver los municipios</p>
                                </div>
                            </div>

                            <div class="p-3" style="border-top:1px solid rgba(2,6,23,.08);">
                                <small class="text-muted d-block">
                                    <i class="feather icon-info"></i> Para KPIs, tabla y gráficos entra al <b>Panel</b>.
                                </small>
                            </div>
                        </div>
                    </div>
                </div>

            </div><!-- /dashContent -->

        </div>
    </div>

    <!-- Modal Detalle de Municipio (clic en mapa) -->
    <div class="modal fade" id="modalMunicipio" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable" role="document">
            <div class="modal-content" style="border-radius:18px;overflow:hidden;border:1px solid rgba(15,23,42,.12);box-shadow:0 16px 42px rgba(2,6,23,.18);">
                <div class="modal-header" style="background:linear-gradient(135deg, var(--nav-blue), var(--nav-blue-2));color:#fff;">
                    <h5 class="modal-title" id="modalMunicipioTitle">
                        <i class="feather icon-map-pin"></i> Municipio
                    </h5>
                    <button type="button" class="close" data-dismiss="modal">
                        <span style="color:#fff;">&times;</span>
                    </button>
                </div>
                <div class="modal-body" id="modalMunicipioBody"></div>
                <div class="modal-footer">
                    <a href="panel_proyectos.php" class="btn btn-primary">
                        <i class="feather icon-bar-chart-2"></i> Ver Panel
                    </a>
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                </div>
            </div>
        </div>
    </div>

    <?php include './admin/include/footer.php'; ?>

    <!-- Scripts (igual que antes) -->
    <script src="assets/js/vendor-all.min.js"></script>
    <script src="assets/js/plugins/bootstrap.min.js"></script>
    <script src="assets/js/pcoded.min.js"></script>
    <script src="assets/js/plugins/apexcharts.min.js"></script>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <!-- Script del dashboard -->
    <script src="admin/js/proyectos_rpc_dash.js"></script>
   <script>
/**
 * Modal Municipio - "Proyecto" con Ver más / Ver menos
 * SAFE: no toca backend ni tus consultas, solo maquilla el HTML ya renderizado.
 */
(function(){
  // Envolvemos el contenido de la columna PROYECTO (2da columna) cuando el modal se abre
  $('#modalMunicipio').on('shown.bs.modal', function () {
    try{
      const $body = $('#modalMunicipioBody');
      const $rows = $body.find('table tbody tr');

      $rows.each(function(){
        const $tdProyecto = $(this).find('td:nth-child(2)'); // 2da columna = PROYECTO
        if(!$tdProyecto.length) return;

        // Evita doble-wrapping
        if($tdProyecto.find('.proj-wrap').length) return;

        const htmlOriginal = $tdProyecto.html();
        const textoPlano   = ($tdProyecto.text() || '').trim();

        // Si está vacío, no hacemos nada
        if(!textoPlano) return;

        // Armamos el wrapper + clamp + toggle
        const $wrap = $(`
          <div class="proj-wrap">
            <div class="proj-text"></div>
            <button type="button" class="proj-toggle">Ver más</button>
          </div>
        `);

        // Conserva saltos y contenido HTML (si viene con <br>, etc.)
        $wrap.find('.proj-text').html(htmlOriginal);

        // Si es muy corto, ocultamos el toggle
        // (regla simple por longitud, para evitar cálculos pesados)
        if(textoPlano.length < 80){
          $wrap.find('.proj-toggle').remove();
        }

        $tdProyecto.empty().append($wrap);
      });
    }catch(e){
      console.warn('UI Proyecto VerMás:', e);
    }
  });

  // Delegación: toggle dentro del body del modal
  $(document).on('click', '#modalMunicipioBody .proj-toggle', function(){
    const $wrap = $(this).closest('.proj-wrap');
    const open = $wrap.toggleClass('is-open').hasClass('is-open');
    $(this).text(open ? 'Ver menos' : 'Ver más');
  });
})();
</script>
</body>
</html>