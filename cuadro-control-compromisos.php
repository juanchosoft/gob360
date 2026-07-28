<?php
include './admin/include/head.php';
require './admin/include/generic_classes.php';
include './admin/classes/Provincias.php';

// Obtener tipo de usuario para controlar acceso a campo Estado
$userType = SessionData::getUserType();
$isAdminOrSuper = (
    $userType === Util::SuperAdministrador() ||
    $userType === Util::Administrador()
);
$esSecretarioGobernacion = ($userType === Util::Secretaria_Despacho_Gobernacion() || $userType === Util::Auxiliar_secret_gob());
$secretariaUsuarioId = SessionData::getSecretaria();

$arrProv = Provincias::getProvinciasByDepartamento(Util::getDepartamentoPrincipal());
$isvalidProv = $arrProv['output']['valid'] ?? false;
$arrProv = $arrProv['output']['response'] ?? [];
$optionProv = "<option value=''>Seleccione</option>";

if ($isvalidProv && !empty($arrProv)) {
    foreach ($arrProv as $val) {
        $optionProv .= "<option value='" . htmlspecialchars($val['provincia']) . "'>" . htmlspecialchars($val['provincia']) . "</option>";
    }
}

function h($s){ return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }
?>

<body class="dashboard-premium gob360-commitments-page">
  <!-- [ Pre-loader ] start -->
  <div class="loader-bg">
    <div class="loader-track">
      <div class="loader-fill"></div>
    </div>
  </div>
  <!-- [ Pre-loader ] End -->

  <?php include './admin/include/navbar.php'; ?>
  <?php include './admin/include/header.php'; ?>


  <style>
    :root{
      --g360-bg-0:#050914;
      --g360-bg-1:#071426;
      --g360-bg-2:#08262d;
      --g360-panel:rgba(255,255,255,.065);
      --g360-panel-strong:rgba(255,255,255,.09);
      --g360-border:rgba(255,255,255,.13);
      --g360-border-soft:rgba(255,255,255,.09);
      --g360-text:rgba(255,255,255,.95);
      --g360-muted:rgba(255,255,255,.66);
      --g360-muted-2:rgba(255,255,255,.48);
      --g360-cyan:#27d9ff;
      --g360-blue:#3978ff;
      --g360-green:#35efa1;
      --g360-yellow:#ffd166;
      --g360-red:#ff6685;
      --g360-radius-xl:28px;
      --g360-radius-lg:20px;
      --g360-radius-md:15px;
      --g360-shadow:0 28px 85px rgba(0,0,0,.42);
      --g360-shadow-soft:0 16px 45px rgba(0,0,0,.28);
      --g360-safe-top:112px;
    }

    *,*::before,*::after{box-sizing:border-box;}

    html,body{
      overflow-x:hidden !important;
    }

    body.gob360-commitments-page{
      min-height:100vh;
      color:var(--g360-text);
      background:
        radial-gradient(950px 520px at 8% 4%, rgba(57,120,255,.27), transparent 62%),
        radial-gradient(850px 500px at 94% 8%, rgba(39,217,255,.14), transparent 60%),
        radial-gradient(950px 620px at 50% 112%, rgba(53,239,161,.11), transparent 58%),
        linear-gradient(155deg,var(--g360-bg-0),var(--g360-bg-1) 55%,var(--g360-bg-2)) !important;
    }

    body.gob360-commitments-page::before{
      content:"";
      position:fixed;
      inset:0;
      z-index:-1;
      pointer-events:none;
      opacity:.14;
      background-image:
        linear-gradient(rgba(255,255,255,.08) 1px,transparent 1px),
        linear-gradient(90deg,rgba(255,255,255,.08) 1px,transparent 1px);
      background-size:68px 68px;
      -webkit-mask-image:linear-gradient(to bottom,#000,transparent 96%);
      mask-image:linear-gradient(to bottom,#000,transparent 96%);
    }

    .pcoded-main-container{
      background:transparent !important;
    }

    .pcoded-main-container .pcoded-content{
      max-width:1480px;
      margin:0 auto;
      padding:calc(var(--g360-safe-top) + 18px) 24px 42px !important;
    }

    /* Encabezado de navegación */
    .page-header .page-block{
      position:relative;
      overflow:hidden;
      padding:18px 20px;
      border:1px solid var(--g360-border) !important;
      border-radius:var(--g360-radius-xl) !important;
      background:linear-gradient(135deg,rgba(255,255,255,.09),rgba(255,255,255,.04)) !important;
      box-shadow:var(--g360-shadow-soft) !important;
      backdrop-filter:blur(16px);
    }

    .page-header .page-block::before{
      content:"";
      position:absolute;
      inset:0;
      pointer-events:none;
      background:
        radial-gradient(430px 170px at 8% 0%,rgba(57,120,255,.28),transparent 65%),
        radial-gradient(430px 170px at 95% 20%,rgba(39,217,255,.15),transparent 66%);
    }

    .page-header .page-block>*{
      position:relative;
      z-index:1;
    }

    .page-header h5{
      margin:0 !important;
      color:#fff !important;
      font-size:clamp(18px,2.4vw,28px);
      font-weight:950 !important;
      letter-spacing:-.35px;
    }

    .breadcrumb{
      padding:0 !important;
      margin:8px 0 0 !important;
      background:transparent !important;
    }

    .breadcrumb a,
    .breadcrumb-item,
    .breadcrumb-item.active{
      color:var(--g360-muted) !important;
    }

    /* Hero principal */
    .commitment-hero{
      position:relative;
      overflow:hidden;
      margin-bottom:18px;
      padding:25px;
      border:1px solid var(--g360-border);
      border-radius:var(--g360-radius-xl);
      background:
        radial-gradient(680px 260px at 0% 0%,rgba(57,120,255,.27),transparent 66%),
        radial-gradient(680px 260px at 100% 100%,rgba(39,217,255,.13),transparent 66%),
        rgba(255,255,255,.055);
      box-shadow:var(--g360-shadow);
      backdrop-filter:blur(18px);
    }

    .commitment-hero__grid{
      display:grid;
      grid-template-columns:minmax(230px,350px) 1fr;
      align-items:center;
      gap:28px;
    }

    .commitment-hero__logo{
      display:block;
      width:min(100%,340px);
      height:auto;
      border-radius:18px;
      filter:
        drop-shadow(0 18px 36px rgba(0,0,0,.42))
        drop-shadow(0 0 28px rgba(39,217,255,.24));
    }

    .commitment-hero__eyebrow{
      display:inline-flex;
      align-items:center;
      gap:8px;
      margin-bottom:8px;
      color:var(--g360-cyan);
      font-size:12px;
      font-weight:900;
      letter-spacing:1.7px;
      text-transform:uppercase;
    }

    .commitment-hero__title{
      margin:0;
      color:#fff;
      font-size:clamp(27px,3.5vw,48px);
      font-weight:1000;
      line-height:1.04;
      letter-spacing:-1.25px;
    }

    .commitment-hero__text{
      max-width:800px;
      margin:11px 0 0;
      color:var(--g360-muted);
      font-size:14px;
      line-height:1.65;
    }

    .commitment-hero__chips{
      display:flex;
      flex-wrap:wrap;
      gap:8px;
      margin-top:17px;
    }

    .g360-chip{
      display:inline-flex;
      align-items:center;
      gap:7px;
      min-height:34px;
      padding:7px 11px;
      color:rgba(255,255,255,.87);
      font-size:12px;
      font-weight:850;
      border:1px solid rgba(255,255,255,.14);
      border-radius:999px;
      background:rgba(2,8,20,.28);
    }

    .g360-chip--success{border-color:rgba(53,239,161,.32);}
    .g360-chip--info{border-color:rgba(39,217,255,.28);}

    .commitment-kpis{
      display:grid;
      grid-template-columns:repeat(3,minmax(0,1fr));
      gap:12px;
      margin-top:21px;
    }

    .commitment-kpi{
      position:relative;
      overflow:hidden;
      min-height:104px;
      padding:15px 16px;
      border:1px solid var(--g360-border-soft);
      border-radius:18px;
      background:rgba(3,10,23,.25);
    }

    .commitment-kpi::after{
      content:"";
      position:absolute;
      right:-35px;
      bottom:-42px;
      width:105px;
      height:105px;
      border-radius:50%;
      background:rgba(39,217,255,.07);
    }

    .commitment-kpi__top{
      display:flex;
      align-items:center;
      justify-content:space-between;
      gap:10px;
    }

    .commitment-kpi__icon{
      width:38px;
      height:38px;
      display:inline-flex;
      align-items:center;
      justify-content:center;
      color:var(--g360-cyan);
      border:1px solid rgba(39,217,255,.18);
      border-radius:12px;
      background:rgba(39,217,255,.08);
    }

    .commitment-kpi__label{
      margin:0;
      color:var(--g360-muted);
      font-size:11px;
      font-weight:900;
      letter-spacing:.7px;
      text-transform:uppercase;
    }

    .commitment-kpi__value{
      position:relative;
      z-index:1;
      margin:7px 0 0;
      color:#fff;
      font-size:clamp(21px,2.2vw,32px);
      font-weight:1000;
      line-height:1;
    }

    /* Tarjeta principal */
    .saas-card{
      overflow:hidden;
      border:1px solid var(--g360-border) !important;
      border-radius:var(--g360-radius-xl) !important;
      background:rgba(255,255,255,.055) !important;
      box-shadow:var(--g360-shadow) !important;
      backdrop-filter:blur(18px);
    }

    .saas-card>.card-header{
      min-height:76px;
      padding:16px 18px !important;
      color:#fff !important;
      border-bottom:1px solid var(--g360-border) !important;
      background:
        linear-gradient(135deg,rgba(57,120,255,.24),rgba(39,217,255,.08)),
        rgba(3,10,23,.34) !important;
    }

    .saas-card>.card-header h5{
      margin:0;
      color:#fff !important;
      font-size:19px;
      font-weight:950 !important;
    }

    .saas-card>.card-body{
      padding:18px !important;
    }

    .saas-card .small{
      color:var(--g360-muted) !important;
    }

    .g360-toolbar{
      display:flex;
      align-items:center;
      justify-content:flex-end;
      flex-wrap:wrap;
      gap:9px;
    }

    .g360-action{
      min-height:40px;
      display:inline-flex;
      align-items:center;
      justify-content:center;
      gap:8px;
      padding:8px 13px !important;
      color:#fff !important;
      font-size:12px;
      font-weight:900 !important;
      text-decoration:none !important;
      border:1px solid rgba(255,255,255,.14) !important;
      border-radius:13px !important;
      background:rgba(255,255,255,.07) !important;
      box-shadow:0 10px 24px rgba(0,0,0,.18);
      transition:transform .18s ease,background .18s ease;
    }

    .g360-action:hover{
      transform:translateY(-1px);
      background:rgba(255,255,255,.12) !important;
    }

    .g360-action--primary{
      color:#06101d !important;
      border:0 !important;
      background:linear-gradient(135deg,var(--g360-cyan),#79eaff) !important;
    }

    .g360-action--success{
      color:#071610 !important;
      border:0 !important;
      background:linear-gradient(135deg,var(--g360-green),#78f6c0) !important;
    }

    /* Tabs SaaS */
    .nav-tabs{
      gap:8px;
      padding:5px;
      border:1px solid var(--g360-border-soft) !important;
      border-radius:17px;
      background:rgba(2,8,20,.24);
    }

    .nav-tabs .nav-item{
      margin:0 !important;
    }

    .nav-tabs .nav-link{
      min-height:43px;
      display:inline-flex;
      align-items:center;
      gap:8px;
      padding:9px 15px !important;
      color:var(--g360-muted) !important;
      font-weight:900 !important;
      border:1px solid transparent !important;
      border-radius:13px !important;
      background:transparent !important;
      transition:.18s ease;
    }

    .nav-tabs .nav-link:hover{
      color:#fff !important;
      background:rgba(255,255,255,.06) !important;
    }

    .nav-tabs .nav-link.active{
      color:#06101d !important;
      border:0 !important;
      background:linear-gradient(135deg,var(--g360-cyan),#74e8ff) !important;
      box-shadow:0 12px 25px rgba(39,217,255,.17);
    }

    .tab-content{
      padding-top:4px;
    }

    /* Filtros */
    .filter-panel{
      position:relative;
      overflow:hidden;
      margin:15px 0;
      padding:18px;
      border:1px solid var(--g360-border-soft);
      border-radius:22px;
      background:
        radial-gradient(520px 180px at 0% 0%,rgba(57,120,255,.11),transparent 68%),
        rgba(2,8,20,.25);
      box-shadow:0 16px 40px rgba(0,0,0,.20);
    }

    .filter-panel::before{
      content:"Filtros inteligentes";
      display:block;
      margin-bottom:13px;
      color:var(--g360-cyan);
      font-size:11px;
      font-weight:950;
      letter-spacing:1.2px;
      text-transform:uppercase;
    }

    .filter-panel .row.g-3{
      margin-left:-7px;
      margin-right:-7px;
    }

    .filter-panel .row.g-3>[class*="col-"]{
      padding-left:7px;
      padding-right:7px;
      margin-bottom:13px;
    }

    .filter-panel label{
      display:block;
      margin-bottom:7px;
      color:rgba(255,255,255,.80) !important;
      font-size:11px;
      font-weight:900 !important;
      letter-spacing:.45px;
      text-transform:uppercase;
    }

    .form-control,
    select.form-control,
    input.form-control{
      min-height:47px;
      padding:10px 13px !important;
      color:rgba(255,255,255,.94) !important;
      font-weight:750;
      border:1px solid rgba(255,255,255,.14) !important;
      border-radius:14px !important;
      background:rgba(7,17,34,.70) !important;
      box-shadow:none !important;
      transition:border-color .18s ease,box-shadow .18s ease,transform .18s ease;
    }

    .form-control::placeholder{
      color:rgba(255,255,255,.43) !important;
    }

    .form-control:focus{
      transform:translateY(-1px);
      border-color:rgba(39,217,255,.48) !important;
      box-shadow:0 0 0 5px rgba(39,217,255,.10) !important;
    }

    select.form-control option{
      color:#0f172a;
      background:#fff;
    }

    .filter-footer{
      display:flex;
      align-items:center;
      justify-content:space-between;
      flex-wrap:wrap;
      gap:10px;
      padding-top:3px;
    }

    .filter-footer__hint{
      color:var(--g360-muted-2);
      font-size:12px;
    }

    /* Tabla */
    .table-border-style{
      padding-top:6px !important;
    }

    .table-responsive{
      width:100%;
      max-height:680px;
      overflow:auto;
      border:1px solid var(--g360-border-soft);
      border-radius:22px;
      background:rgba(81, 119, 189, 0.32);
      box-shadow:0 18px 45px rgba(94, 163, 219, 0.24);
    }

    #dynamictable{
      width:100% !important;
      min-width:1550px;
      margin:0 !important;
      color:var(--g360-text) !important;
      background:transparent !important;
      border-collapse:separate !important;
      border-spacing:0 !important;
    }

    #dynamictable thead th{
      position:sticky;
      top:0;
      z-index:5;
      padding:14px 12px !important;
      color:rgba(255,255,255,.84) !important;
      font-size:11px !important;
      font-weight:950 !important;
      letter-spacing:.55px;
      text-transform:uppercase;
      white-space:nowrap;
      vertical-align:middle;
      border:0 !important;
      border-bottom:1px solid var(--g360-border) !important;
      background:rgba(7,18,37,.97) !important;
      backdrop-filter:blur(15px);
    }

    #dynamictable tbody td{
      padding:12px !important;
      color:rgba(6, 6, 6, 0.8) !important;
      font-size:12px !important;
      font-weight:650;
      line-height:1.45;
      white-space:normal !important;
      word-break:break-word !important;
      vertical-align:top;
      border:0 !important;
      border-bottom:1px solid rgba(255,255,255,.065) !important;
      background:transparent !important;
    }

    #dynamictable tbody tr{
      transition:background .16s ease,transform .16s ease;
    }

    #dynamictable tbody tr:hover{
      background:rgba(39,217,255,.055) !important;
    }

    #dynamictable tbody tr:last-child td{
      border-bottom:0 !important;
    }

    #dynamictable td img{
      width:54px !important;
      height:54px !important;
      object-fit:cover;
      border:1px solid rgba(50, 131, 182, 0.16);
      border-radius:14px;
      box-shadow:0 10px 22px rgba(0,0,0,.28);
    }

    #dynamictable td .btn,
    #dynamictable td button,
    #dynamictable td a.btn{
      min-width:36px;
      min-height:36px;
      display:inline-flex;
      align-items:center;
      justify-content:center;
      padding:7px 9px !important;
      color:#fff !important;
      border:1px solid rgba(255,255,255,.14) !important;
      border-radius:11px !important;
      background:rgba(255,255,255,.08) !important;
      box-shadow:none !important;
    }

    #dynamictable td .btn-primary{
      background:rgba(57,120,255,.25) !important;
      border-color:rgba(57,120,255,.36) !important;
    }

    #dynamictable td .btn-danger{
      background:rgba(255,102,133,.18) !important;
      border-color:rgba(255,102,133,.34) !important;
    }

    #dynamictable td i,
    #dynamictable td svg,
    #dynamictable td .feather{
      color:#000 !important;
      stroke:currentColor !important;
    }

    /* DataTables */
    .dataTables_wrapper{
      color:var(--g360-muted) !important;
    }

    .dataTables_wrapper .dataTables_length,
    .dataTables_wrapper .dataTables_filter{
      margin-bottom:13px;
    }

    .dataTables_wrapper .dataTables_length label,
    .dataTables_wrapper .dataTables_filter label,
    .dataTables_wrapper .dataTables_info{
      color:var(--g360-muted) !important;
      font-size:12px;
      font-weight:800;
    }

    .dataTables_wrapper .dataTables_filter input,
    .dataTables_wrapper .dataTables_length select{
      min-height:39px;
      margin-left:7px;
      padding:7px 10px !important;
      color:#fff !important;
      border:1px solid rgba(255,255,255,.14) !important;
      border-radius:12px !important;
      background:rgba(7,17,34,.70) !important;
      outline:none !important;
    }

    .dataTables_wrapper .dataTables_length select option{
      color:#111827;
      background:#fff;
    }

    .dataTables_wrapper .dataTables_paginate{
      margin-top:13px;
    }

    .dataTables_wrapper .dataTables_paginate .paginate_button{
      min-width:36px;
      margin:0 3px !important;
      padding:7px 10px !important;
      color:var(--g360-muted) !important;
      border:1px solid rgba(255,255,255,.10) !important;
      border-radius:11px !important;
      background:rgba(255,255,255,.045) !important;
    }

    .dataTables_wrapper .dataTables_paginate .paginate_button.current,
    .dataTables_wrapper .dataTables_paginate .paginate_button.current:hover{
      color:#06101d !important;
      border:0 !important;
      background:linear-gradient(135deg,var(--g360-cyan),#75e8ff) !important;
    }

    .dataTables_wrapper .dataTables_paginate .paginate_button:hover{
      color:#fff !important;
      background:rgba(255,255,255,.10) !important;
    }

    /* Indicadores */
    .subcard{
      overflow:hidden;
      border:1px solid var(--g360-border) !important;
      border-radius:22px !important;
      background:rgba(2,8,20,.24) !important;
      box-shadow:var(--g360-shadow-soft) !important;
    }

    .subcard>.card-header{
      padding:16px 18px !important;
      color:#fff !important;
      border-bottom:1px solid var(--g360-border) !important;
      background:rgba(255,255,255,.055) !important;
    }

    .subcard>.card-body{
      min-height:380px;
      padding:18px !important;
      color:var(--g360-text);
      background:transparent !important;
    }

    #indicadoresContainer{
      min-height:330px;
    }

    /* Modales */
    .modal-content{
      overflow:hidden;
      color:#132238;
      border:1px solid rgba(255,255,255,.12) !important;
      border-radius:24px !important;
      background:#fff !important;
      box-shadow:0 35px 100px rgba(0,0,0,.48) !important;
    }

    .modal-header{
      min-height:69px;
      color:#fff !important;
      border-bottom:0 !important;
      background:
        linear-gradient(135deg,rgba(57,120,255,.95),rgba(20,71,167,.96)) !important;
    }

    .modal-title{
      color:#fff !important;
      font-weight:950 !important;
    }

    .modal .close{
      color:#fff !important;
      opacity:.95 !important;
      text-shadow:none !important;
    }

    .modal-body{
      color:#132238 !important;
      background:#fff !important;
    }

    .modal-footer{
      border-top:1px solid #e8edf3 !important;
      background:#f8fafc;
    }

    .modal .form-control{
      color:#132238 !important;
      border-color:#d9e1eb !important;
      background:#fff !important;
    }

    .modal .form-control::placeholder{
      color:#8795a8 !important;
    }

    #contenidoCompromiso{
      max-height:70vh;
      overflow-y:auto;
      margin:0;
      color:#233751;
      line-height:1.65;
      white-space:pre-wrap;
      overflow-wrap:anywhere;
    }

    #contenidoHistorial{
      max-height:75vh;
      overflow-y:auto;
    }

    .au-hist-wrap{
      display:flex;
      flex-direction:column;
      gap:12px;
    }

    .au-hist-card{
      overflow:hidden;
      border:1px solid #e3e9f1;
      border-radius:16px;
      background:#fff;
      box-shadow:0 8px 22px rgba(2,6,23,.06);
    }

    .au-hist-card__head{
      display:flex;
      flex-wrap:wrap;
      justify-content:space-between;
      gap:8px 16px;
      padding:12px 14px;
      border-bottom:1px solid #e8edf3;
      background:#f8fafc;
    }

    .au-hist-chip{
      display:inline-block;
      padding:4px 10px;
      color:#b30500;
      font-size:12px;
      font-weight:800;
      text-transform:capitalize;
      border-radius:999px;
      background:rgba(225,6,0,.08);
    }

    .au-hist-meta{
      display:flex;
      flex-wrap:wrap;
      align-items:center;
      gap:8px 14px;
      color:#64748b;
      font-size:12px;
    }

    .au-hist-card__body{
      display:grid;
      grid-template-columns:1fr 1fr;
    }

    .au-hist-col{
      min-width:0;
      padding:12px 14px;
      border-right:1px solid #edf1f5;
    }

    .au-hist-col--new{
      border-right:0;
      background:rgba(16,185,129,.03);
    }

    .au-hist-label{
      margin-bottom:6px;
      color:#64748b;
      font-size:11px;
      font-weight:800;
      letter-spacing:.04em;
      text-transform:uppercase;
    }

    .au-hist-text{
      max-height:180px;
      overflow-y:auto;
      color:#0f172a;
      font-size:13px;
      line-height:1.45;
      white-space:pre-wrap;
      overflow-wrap:anywhere;
    }

    /* Importación */
    .import-dropzone{
      padding:18px;
      text-align:center;
      border:1px dashed #a9b8ca;
      border-radius:18px;
      background:#f8fbff;
    }

    .import-dropzone__icon{
      width:52px;
      height:52px;
      display:inline-flex;
      align-items:center;
      justify-content:center;
      margin-bottom:10px;
      color:#2563eb;
      font-size:23px;
      border-radius:16px;
      background:rgba(37,99,235,.09);
    }

    @media(max-width:1100px){
      .commitment-hero__grid{
        grid-template-columns:250px 1fr;
      }

      .commitment-kpis{
        grid-template-columns:repeat(3,minmax(0,1fr));
      }
    }

    @media(max-width:767.98px){
      :root{--g360-safe-top:90px;}

      .pcoded-main-container .pcoded-content{
        padding-left:14px !important;
        padding-right:14px !important;
      }

      .commitment-hero{
        padding:20px;
      }

      .commitment-hero__grid{
        grid-template-columns:1fr;
        text-align:center;
      }

      .commitment-hero__logo{
        width:min(320px,92%);
        margin:0 auto;
      }

      .commitment-hero__chips{
        justify-content:center;
      }

      .commitment-kpis{
        grid-template-columns:1fr;
        text-align:left;
      }

      .saas-card>.card-header{
        align-items:flex-start !important;
      }

      .g360-toolbar{
        width:100%;
        justify-content:flex-start;
      }

      .nav-tabs{
        display:grid;
        grid-template-columns:1fr;
      }

      .nav-tabs .nav-link{
        width:100%;
        justify-content:center;
      }

      .filter-footer{
        align-items:stretch;
        flex-direction:column;
      }

      .filter-footer .g360-action{
        width:100%;
      }

      .au-hist-card__body{
        grid-template-columns:1fr;
      }

      .au-hist-col{
        border-right:0;
        border-bottom:1px solid #edf1f5;
      }

      .au-hist-col--new{
        border-bottom:0;
      }
    }

    @media(max-width:575.98px){
      .saas-card>.card-body{
        padding:13px !important;
      }

      .report-actions{
        width:100%;
      }
    }
  </style>


  <div class="pcoded-main-container">
    <div class="pcoded-content">

      <div class="page-header">
        <div class="page-block">
          <div class="row align-items-center">
            <div class="col-md-12">
              <div class="d-flex justify-content-between align-items-center gap-2 flex-wrap">
                <h5 class="m-b-10 mb-0">Cuadro control Compromiso</h5>
                <?php include './admin/include/btn_back.php'; ?>
              </div>
              <ul class="breadcrumb">
                <li class="breadcrumb-item"><a href="index.html"><i class="feather icon-home"></i></a></li>
                <li class="breadcrumb-item"><a href="#!">Registro de compromisos / Cuadro Control Compromisos</a></li>
              </ul>
            </div>
          </div>
        </div>
      </div>

      <section class="commitment-hero">
        <div class="commitment-hero__grid">
          <div>
            <img
              src="assets/img/gob360l.png"
              alt="Logo GOB360"
              class="commitment-hero__logo"
            >
          </div>

          <div>
            <div class="commitment-hero__eyebrow">
              <i class="feather icon-clipboard"></i>
              Seguimiento institucional
            </div>

            <h1 class="commitment-hero__title">
              Control de compromisos
            </h1>

            <p class="commitment-hero__text">
              Consulta, filtra y supervisa compromisos territoriales por secretaría,
              municipio, provincia, componente y estado, manteniendo la trazabilidad
              completa de cada actualización.
            </p>

            <div class="commitment-hero__chips">
              <span class="g360-chip g360-chip--success">
                <i class="feather icon-shield"></i>
                Acceso según permisos
              </span>

              <span class="g360-chip g360-chip--info">
                <i class="feather icon-map-pin"></i>
                Santander
              </span>

              <span class="g360-chip">
                <i class="feather icon-activity"></i>
                Información en tiempo real
              </span>
            </div>

            <div class="commitment-kpis">
              <div class="commitment-kpi">
                <div class="commitment-kpi__top">
                  <div>
                    <p class="commitment-kpi__label">Registros visibles</p>
                    <p class="commitment-kpi__value" id="g360KpiRegistros">—</p>
                  </div>
                  <span class="commitment-kpi__icon">
                    <i class="feather icon-list"></i>
                  </span>
                </div>
              </div>

              <div class="commitment-kpi">
                <div class="commitment-kpi__top">
                  <div>
                    <p class="commitment-kpi__label">Filtros activos</p>
                    <p class="commitment-kpi__value" id="g360KpiFiltros">0</p>
                  </div>
                  <span class="commitment-kpi__icon">
                    <i class="feather icon-filter"></i>
                  </span>
                </div>
              </div>

              <div class="commitment-kpi">
                <div class="commitment-kpi__top">
                  <div>
                    <p class="commitment-kpi__label">Perfil de acceso</p>
                    <p class="commitment-kpi__value" style="font-size:18px;">
                      <?= $isAdminOrSuper ? 'Administrador' : ($esSecretarioGobernacion ? 'Secretaría' : 'Usuario'); ?>
                    </p>
                  </div>
                  <span class="commitment-kpi__icon">
                    <i class="feather icon-user-check"></i>
                  </span>
                </div>
              </div>
            </div>
          </div>
        </div>
      </section>

      <div class="row">
        <div class="col-12">
          <div class="card saas-card">

            <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
              <h5>Listado de compromisos</h5>
              <div class="g360-toolbar">
                <button type="button" class="btn g360-action g360-action--success" onclick="descargarExcel()" title="Descargar Excel">
                  <i class="feather icon-download"></i> Descargar Excel
                </button>
                <?php if ($isAdminOrSuper): ?>
                  <button type="button" class="btn g360-action g360-action--primary" data-toggle="modal" data-target="#modalImportExcel">
                    <i class="feather icon-upload"></i> Importar Excel
                  </button>
                <?php endif; ?>
                <div class="small" style="opacity:.9;">
                  <i class="feather icon-clipboard me-1"></i>
                  Filtros inteligentes + DataGrid
                </div>
              </div>
            </div>

            <div class="card-body">
              <div class="col-lg-12">

                <ul class="nav nav-tabs" id="myTab" role="tablist">
                  <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="home-tab" data-toggle="tab" data-target="#home" type="button"
                      role="tab" aria-controls="home" aria-selected="true" onclick="cargarCompromiso()">
                      <i class="feather icon-clipboard"></i>
                      Compromisos
                    </button>
                  </li>
                  <li class="nav-item" role="presentation">
                    <button class="nav-link" id="profile-tab" data-toggle="tab" data-target="#profile" type="button"
                      role="tab" aria-controls="profile" aria-selected="false" onclick="indicadores()">
                      <i class="feather icon-pie-chart"></i>
                      Indicadores por secretaría
                    </button>
                  </li>
                </ul>

                <div class="tab-content" id="myTabContent">

                  <!-- TAB COMPROMISOS -->
                  <div class="tab-pane fade show active" id="home" role="tabpanel" aria-labelledby="home-tab">

                    <div class="filter-panel">
                      <div class="row g-3">

                        <div class="col-12 col-md-3">
                          <label for="idFiltro">ID del Compromiso</label>
                          <input type="number" name="idFiltro" id="idFiltro" class="form-control"
                            placeholder="Buscar por ID" onkeyup="filtrarTabla()">
                        </div>

                        <div class="col-12 col-md-3">
                          <label for="secretariaIdFiltro">Seleccionar Secretaría</label>
                          <select name="secretariaIdFiltro" id="secretariaIdFiltro" class="form-control"
                            onchange="filtrarTabla()">
                            <option value="">Seleccione</option>
                          </select>
                          <input type="hidden" id="tbl_departamento_id" name="tbl_departamento_id" value="68">
                        </div>

                        <div class="col-12 col-md-3">
                          <label for="municipioFiltro">Seleccionar Municipio</label>
                          <select name="municipioFiltro" id="municipioFiltro" class="form-control"
                            onchange="filtrarTabla()"></select>
                        </div>

                        <div class="col-12 col-md-3">
                          <label for="provinciaFiltro">Seleccionar Provincia</label>
                          <select name="provinciaFiltro" id="provinciaFiltro" class="form-control"
                            onchange="filtrarTabla()">
                            <?php echo $optionProv; ?>
                          </select>
                        </div>

                        <div class="col-12 col-md-4">
                          <label for="componenteFiltro">Componente</label>
                          <select class="form-control" id="componenteFiltro" name="componenteFiltro"
                            onchange="filtrarTabla()">
                            <option value="" selected>Todas</option>
                            <option value="JURÍDICO">JURÍDICO</option>
                            <option value="MEJORAMIENTO SERVICIO DE SALUD">MEJORAMIENTO SERVICIO DE SALUD</option>
                            <option value="INFRAESTRUCTURA HOSPITALARIA">INFRAESTRUCTURA HOSPITALARIA</option>
                            <option value="DOTACIÓN EN SALUD">DOTACIÓN EN SALUD</option>
                            <option value="INFRAESTRUCTURA PARA CULTURA Y TURISMO">INFRAESTRUCTURA PARA CULTURA Y TURISMO</option>
                            <option value="ATENCIÓN POBLACIÓN VULNERABLE">ATENCIÓN POBLACIÓN VULNERABLE</option>
                            <option value="TRANSPORTE ESCOLAR">TRANSPORTE ESCOLAR</option>
                            <option value="INFRAESTRUCTURA EDUCATIVA">INFRAESTRUCTURA EDUCATIVA</option>
                            <option value="VÍAS SECUNDARIAS Y TERCIARIAS">VÍAS SECUNDARIAS Y TERCIARIAS</option>
                            <option value="INFRAESTRUCTURA INSTITUCIONES">INFRAESTRUCTURA INSTITUCIONES</option>
                            <option value="INFRAESTRUCTURA AEROPORTUARIA">INFRAESTRUCTURA AEROPORTUARIA</option>
                            <option value="AGUA POTABLE - ALCANTARILLADO - PTAR">AGUA POTABLE - ALCANTARILLADO - PTAR</option>
                            <option value="PROMOCIÓN DEL TURISMO">PROMOCIÓN DEL TURISMO</option>
                            <option value="MEJORAMIENTO SERVICIO EDUCATIVO">MEJORAMIENTO SERVICIO EDUCATIVO</option>
                            <option value="DOTACIÓN EDUCATIVA">DOTACIÓN EDUCATIVA</option>
                            <option value="PUENTES">PUENTES</option>
                            <option value="FORTALECIMIENTO INSTITUCIONAL">FORTALECIMIENTO INSTITUCIONAL</option>
                            <option value="GESTIÓN DE RIESGO">GESTIÓN DE RIESGO</option>
                            <option value="KIT HERRAMIENTAS">KIT HERRAMIENTAS</option>
                            <option value="PROTECCIÓN MEDIO AMBIENTE">PROTECCIÓN MEDIO AMBIENTE</option>
                            <option value="INSTRUMENTOS MUSICALES">INSTRUMENTOS MUSICALES</option>
                            <option value="MEJORAMIENTO VIVIENDA">MEJORAMIENTO VIVIENDA</option>
                            <option value="ESCENARIOS DEPORTIVOS">ESCENARIOS DEPORTIVOS</option>
                            <option value="TIC">TIC</option>
                            <option value="APOYO AL DEPORTE">APOYO AL DEPORTE</option>
                            <option value="MINERO - ENERGÉTICO">MINERO - ENERGÉTICO</option>
                            <option value="SEGURIDAD Y CONVIVENCIA">SEGURIDAD Y CONVIVENCIA</option>
                            <option value="APOYO AL AGRO">APOYO AL AGRO</option>
                            <option value="ELECTRIFICACIÓN RURAL">ELECTRIFICACIÓN RURAL</option>
                             <option value="COMPROMISOS NUEVOS">COMPROMISOS NUEVOS</option>
                          </select>
                        </div>

                        <div class="col-12 col-md-4">
                          <label for="estadoFiltro">Estado</label>
                          <select class="form-control" id="estadoFiltro" name="estadoFiltro"
                            onchange="filtrarTabla()">
                            <option value="" selected>Todos</option>
                            <option value="Cumplido">Cumplido</option>
                            <option value="En Trámite">En Trámite</option>
                            <option value="Sin Cumplir">Sin Cumplir</option>
                            <option value="EN ESPERA">En Espera</option>
                          </select>
                        </div>

                      </div>

                      <div class="filter-footer">
                        <span class="filter-footer__hint">
                          <i class="feather icon-info"></i>
                          Los resultados se actualizan al cambiar cualquier filtro.
                        </span>

                        <button
                          type="button"
                          class="btn g360-action"
                          onclick="limpiarFiltrosGOB360();"
                        >
                          <i class="feather icon-x-circle"></i>
                          Limpiar filtros
                        </button>
                      </div>
                    </div>

                    <div class="card-body table-border-style px-0">
                      <div class="table-responsive tabla-informacion tabla-scroll">
                        <table class="table table-hover mb-0" id="dynamictable">
                          <thead>
                            <tr class="border-1">
                              <th>Item</th>
                              <th>Secretaria</th>
                              <th>Compromiso Pact.</th>
                              <th>Consecuencia</th>
                              <th>Respuesta</th>
                              <th>Estado</th>
                              <th>Municipio</th>
                              <th>Provincia</th>
                              <th>Componente</th>
                              <th>Tipo ejec.</th>
                              <th>Imagen</th>
                              <th>Fecha</th>
                              <th>Editar</th>
                              <th>Ver</th>
                              <th>Eliminar</th>
                            </tr>
                          </thead>
                        </table>
                      </div>
                    </div>

                  </div>

                  <!-- TAB INDICADORES -->
                  <div class="tab-pane fade" id="profile" role="tabpanel" aria-labelledby="profile-tab">
                    <div class="card subcard mt-3">
                      <div class="card-header">
                        <h5 class="mb-0" style="color:rgba(255,255,255,.92); font-weight:1000;">Indicadores por Secretaría</h5>
                      </div>
                      <div class="card-body">
                        <div class="col-sm-12">
                          <div id="indicadoresContainer" class="mt-4 text-center"></div>
                        </div>
                      </div>
                    </div>
                  </div>

                </div><!-- tab-content -->
              </div>
            </div>

          </div><!-- saas-card -->
        </div>
      </div>

    </div>

    <!-- Modal de importación de Excel -->
    <div class="modal fade" id="modalImportExcel" tabindex="-1" role="dialog" aria-labelledby="modalImportExcelLabel" aria-hidden="true">
      <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">Importar compromisos desde Excel</h5>
            <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar">
              <span aria-hidden="true">&times;</span>
            </button>
          </div>
          <div class="modal-body" style="padding: 20px;">
            <form id="importCompromisosForm" enctype="multipart/form-data">
              <div class="import-dropzone mb-3">
                <span class="import-dropzone__icon">
                  <i class="feather icon-upload-cloud"></i>
                </span>
                <label for="excel_file" class="d-block font-weight-bold mb-2">
                  Selecciona el archivo Excel
                </label>
                <input type="file" id="excel_file" name="excel_file" accept=".xlsx" class="form-control" required>
                <small class="d-block mt-2 text-muted">
                  Solo se permiten archivos con extensión .xlsx.
                </small>
              </div>
              <div class="mb-3">
                <a href="admin/controllers/compromisoPlantillaCtrl.php" target="_blank" class="btn btn-outline-primary btn-sm">
                  <i class="feather icon-download mr-1"></i> Descargar plantilla
                </a>
              </div>
              <div class="form-text mb-3">
                Encabezados requeridos: ID, Secretaría, Fecha, Provincia, Municipio, Estado, Compromiso Pactado, Respuesta, Componente, Tipo Ejecución, Observaciones.
              </div>
              <div id="importExcelMessages"></div>
              <div id="importExcelErrors" class="mt-3"></div>
            </form>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
            <button type="button" id="importButtonSubmit" class="btn btn-primary">Subir y procesar</button>
          </div>
        </div>
      </div>
    </div>

    <!-- Modal compromiso -->
    <div class="modal fade" id="modalCompromiso" tabindex="-1" role="dialog" aria-labelledby="modalCompromisoLabel" aria-hidden="true">
      <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">Detalle del Compromiso</h5>
            <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar">
              <span aria-hidden="true">&times;</span>
            </button>
          </div>
          <div class="modal-body" style="padding: 20px;">
            <p id="contenidoCompromiso"></p>
          </div>
        </div>
      </div>
    </div>

    <!-- Modal para adjuntos -->
    <div class="modal fade" id="modalAdjunto" tabindex="-1" role="dialog" aria-labelledby="modalAdjuntoLabel" aria-hidden="true">
      <div class="modal-dialog modal-xl modal-dialog-centered" role="document">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">Adjunto</h5>
            <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar">
              <span aria-hidden="true">&times;</span>
            </button>
          </div>
          <div class="modal-body text-center" id="contenidoAdjunto" style="padding: 20px;"></div>
        </div>
      </div>
    </div>

    <!-- Modal para historial de trazabilidad -->
    <div class="modal fade" id="modalHistorial" tabindex="-1" role="dialog" aria-labelledby="modalHistorialLabel" aria-hidden="true">
      <div class="modal-dialog modal-xl modal-dialog-centered" role="document">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">Historial de cambios</h5>
            <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar">
              <span aria-hidden="true">&times;</span>
            </button>
          </div>
          <div class="modal-body" id="contenidoHistorial" style="padding: 20px;"></div>
        </div>
      </div>
    </div>

    <?php include 'admin/include/footer.php'; ?>
  </div>

  <?php include 'admin/include/gerenic_script.php'; ?>
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

  <script src="assets/js/vendor-all.min.js"></script>
  <script src="assets/js/plugins/bootstrap.min.js"></script>
  <script src="assets/js/pcoded.min.js"></script>

  <script>
    // Variable global para controlar acceso al campo Estado
    const isAdminOrSuper = <?php echo $isAdminOrSuper ? 'true' : 'false'; ?>;
    const esSecretarioGobernacion = <?php echo $esSecretarioGobernacion ? 'true' : 'false'; ?>;
    const secretariaUsuarioId = <?php echo intval($secretariaUsuarioId); ?>;
  </script>

  <script src="<?php echo Util::versionar('./admin/js/control-municipio.js'); ?>"></script>

  <script>
    function mostrarImportAlert(message, type) {
      $('#importExcelMessages').html(
        '<div class="alert alert-' + type + '" role="alert">' +
        message +
        '</div>'
      );
    }

    function mostrarImportErrors(errors) {
      if (!Array.isArray(errors) || errors.length === 0) {
        $('#importExcelErrors').html('');
        return;
      }

      var html = '<div class="alert alert-danger" role="alert">';
      html += '<h6 class="mb-2">Errores de validación</h6>';
      html += '<ul class="mb-0">';
      errors.forEach(function(error) {
        html += '<li>' + $('<div>').text(error).html() + '</li>';
      });
      html += '</ul>';
      html += '</div>';
      $('#importExcelErrors').html(html);
    }

    $(document).ready(function() {
      $('#modalImportExcel').on('show.bs.modal', function() {
        $('#importCompromisosForm')[0].reset();
        $('#importExcelMessages').empty();
        $('#importExcelErrors').empty();
      });

      $('#importButtonSubmit').on('click', function() {
        $('#importCompromisosForm').submit();
      });

      $('#importCompromisosForm').on('submit', function(event) {
        event.preventDefault();

        var fileInput = $('#excel_file')[0];
        if (!fileInput.files || fileInput.files.length === 0) {
          mostrarImportAlert('Seleccione un archivo Excel (.xlsx) para importar.', 'warning');
          return;
        }

        var file = fileInput.files[0];
        if (!/\.xlsx$/i.test(file.name)) {
          mostrarImportAlert('El archivo debe tener extensión .xlsx.', 'danger');
          return;
        }

        var formData = new FormData(this);
        $('#importButtonSubmit').prop('disabled', true).text('Procesando...');
        mostrarImportAlert('Procesando archivo, espere por favor...', 'info');
        mostrarImportErrors([]);

        $.ajax({
          url: 'admin/ajax/importar_compromisos_excel.php',
          type: 'POST',
          data: formData,
          cache: false,
          contentType: false,
          processData: false,
          dataType: 'json'
        })
          .done(function(response) {
            if (response.success) {
              mostrarImportAlert(response.message || 'Importación completada.', 'success');
              mostrarImportErrors([]);
              $('#excel_file').val('');
              if (typeof cargarCompromiso === 'function') {
                cargarCompromiso();
              }
            } else {
              mostrarImportAlert(response.message || 'Ocurrió un error al importar.', 'danger');
              mostrarImportErrors(response.errors || []);
            }
          })
          .fail(function(xhr) {
            var message = 'Error en la petición. Intente nuevamente.';
            if (xhr.responseJSON && xhr.responseJSON.message) {
              message = xhr.responseJSON.message;
            }
            mostrarImportAlert(message, 'danger');
          })
          .always(function() {
            $('#importButtonSubmit').prop('disabled', false).text('Subir y procesar');
          });
      });
    });
  </script>

  <script>
    function limpiarFiltrosGOB360() {
      var campos = [
        '#idFiltro',
        '#secretariaIdFiltro',
        '#municipioFiltro',
        '#provinciaFiltro',
        '#componenteFiltro',
        '#estadoFiltro'
      ];

      campos.forEach(function(selector) {
        var campo = $(selector);
        if (!campo.length) return;

        campo.val('');
        campo.trigger('change.select2');
      });

      if (typeof filtrarTabla === 'function') {
        filtrarTabla();
      }

      setTimeout(actualizarResumenGOB360, 250);
    }

    function contarFiltrosGOB360() {
      var total = 0;

      [
        '#idFiltro',
        '#secretariaIdFiltro',
        '#municipioFiltro',
        '#provinciaFiltro',
        '#componenteFiltro',
        '#estadoFiltro'
      ].forEach(function(selector) {
        var valor = $(selector).val();

        if (valor !== null && String(valor).trim() !== '') {
          total++;
        }
      });

      return total;
    }

    function actualizarResumenGOB360() {
      var totalRegistros = 0;

      try {
        if (
          $.fn.dataTable &&
          $.fn.dataTable.isDataTable('#dynamictable')
        ) {
          var tabla = $('#dynamictable').DataTable();
          totalRegistros = tabla.rows({ search: 'applied' }).count();
        } else {
          totalRegistros = $('#dynamictable tbody tr').length;
        }
      } catch (error) {
        totalRegistros = $('#dynamictable tbody tr').length;
      }

      $('#g360KpiRegistros').text(
        Number(totalRegistros || 0).toLocaleString('es-CO')
      );

      $('#g360KpiFiltros').text(contarFiltrosGOB360());
    }

    $(document).ready(function() {
      $('#idFiltro, #secretariaIdFiltro, #municipioFiltro, #provinciaFiltro, #componenteFiltro, #estadoFiltro')
        .on('change keyup', function() {
          setTimeout(actualizarResumenGOB360, 180);
        });

      $('#dynamictable').on('draw.dt', function() {
        actualizarResumenGOB360();
      });

      var observerTarget = document.getElementById('dynamictable');

      if (observerTarget && window.MutationObserver) {
        var observer = new MutationObserver(function() {
          actualizarResumenGOB360();
        });

        observer.observe(observerTarget, {
          childList: true,
          subtree: true
        });
      }

      setTimeout(actualizarResumenGOB360, 700);
      setTimeout(actualizarResumenGOB360, 1800);
    });
  </script>

  <script type="text/javascript" src="admin/js/datatables/jquery.dataTables.min.js"></script>
  <link href="admin/js/datatables/jquery.dataTables.min.css" rel="stylesheet" />
</body>
</html>
