<style>
  :root{
    --ink:#EAF0FF; --muted:rgba(234,240,255,.72);
    --glass: rgba(255,255,255,.08); --glass2: rgba(255,255,255,.06);
    --stroke: rgba(255,255,255,.12); --stroke2: rgba(255,255,255,.08);
    --radius-xl:22px; --radius-lg:16px;
    --shadow-soft: 0 14px 40px rgba(0,0,0,.28); --shadow-mid: 0 22px 70px rgba(0,0,0,.34);
    --safe-top: 96px;
    --brand:#60A5FA; --brand2:#A78BFA; --ok:#34D399; --warn:#FBBF24; --danger:#FB7185; --cyan:#22D3EE;
  }
  body{
    background: radial-gradient(1200px 600px at 10% 10%, rgba(96,165,250,.22), transparent 55%),
                radial-gradient(900px 500px at 90% 15%, rgba(167,139,250,.18), transparent 55%),
                radial-gradient(900px 520px at 70% 90%, rgba(52,211,153,.14), transparent 55%),
                linear-gradient(135deg, #081226 0%, #0B1B38 45%, #070B16 100%) !important;
    color: var(--ink) !important; min-height:100vh;
  }
  .pcoded-content{ padding: calc(var(--safe-top) + 16px) 16px 18px !important; }
  @media(min-width:768px){ :root{ --safe-top: 112px; } .pcoded-content{ padding: calc(var(--safe-top) + 18px) 24px 24px !important; } }
  @media(min-width:1200px){ :root{ --safe-top: 120px; } .pcoded-content{ padding: calc(var(--safe-top) + 22px) 42px 34px !important; } }
  .card, .au-card{
    background: linear-gradient(180deg, rgba(255,255,255,.10), rgba(255,255,255,.06)) !important;
    border-radius: var(--radius-xl) !important;
    border: 1px solid var(--stroke) !important;
    box-shadow: var(--shadow-mid);
    overflow:hidden;
    backdrop-filter: blur(12px);
  }
  .card .card-header{
    background: linear-gradient(180deg, rgba(0,0,0,.20), transparent) !important;
    border-bottom: 1px solid var(--stroke) !important;
    color: #fff !important;
  }
  .card .card-header h5{ color: #fff !important; font-weight: 1000 !important; margin: 0; }
  .card .card-body{ color: rgba(255,255,255,.86); }
  .form-control, select.form-control, input.form-control, textarea.form-control{
    background: rgba(255,255,255,.06) !important;
    border: 1px solid var(--stroke) !important;
    color: #fff !important;
    border-radius: 16px !important;
    font-weight: 800 !important;
    box-shadow: 0 10px 30px rgba(0,0,0,.18);
    padding: .65rem .95rem !important;
  }
  .form-control:focus{
    outline: none !important;
    box-shadow: 0 0 0 .20rem rgba(96,165,250,.22) !important;
    border-color: rgba(96,165,250,.45) !important;
  }
  select.form-control option{ color: #0B1B38; background: #fff; }
  .btn{
    border-radius: 14px !important;
    font-weight: 900 !important;
    padding: 10px 18px !important;
  }
  .btn-primary{
    background: linear-gradient(135deg, #3b82f6, #4f46e5) !important;
    border: 1px solid rgba(255,255,255,.14) !important;
    color: #fff !important;
  }
  .btn-secondary{
    background: rgba(255,255,255,.09) !important;
    border: 1px solid var(--stroke) !important;
    color: #fff !important;
  }
  .btn-success{
    background: linear-gradient(135deg, #0f766e, #16a34a) !important;
    border: 1px solid rgba(255,255,255,.14) !important;
    color: #fff !important;
  }
  .modal-content{
    background: linear-gradient(180deg, rgba(255,255,255,.10), rgba(255,255,255,.06)) !important;
    border: 1px solid var(--stroke) !important;
    border-radius: var(--radius-xl) !important;
    box-shadow: var(--shadow-mid);
    backdrop-filter: blur(12px);
  }
  .modal-header{
    border-bottom: 1px solid var(--stroke) !important;
    background: rgba(0,0,0,.14) !important;
  }
  .modal-title{ color: #fff !important; font-weight: 1000 !important; }
  .modal-body{ color: rgba(255,255,255,.86); }
  .modal-footer{ border-top: 1px solid var(--stroke) !important; background: rgba(0,0,0,.10); }
  .close, .close span{ color: #fff !important; opacity: .8; text-shadow: none !important; }
  table{ color: rgba(255,255,255,.86); }
  .page-header .page-block{
    background: linear-gradient(135deg, rgba(255,255,255,.10), rgba(255,255,255,.06)) !important;
    border: 1px solid var(--stroke) !important;
    border-radius: var(--radius-xl) !important;
    box-shadow: var(--shadow-soft);
    backdrop-filter: blur(12px);
  }
  .page-header-title h5,
  .page-block h5{ color: #fff !important; font-weight: 1000 !important; }
  .page-block div[style*="font-size"]{ color: rgba(255,255,255,.7) !important; }
  .breadcrumb{ background: transparent !important; }
  .breadcrumb-item a, .breadcrumb-item{ color: rgba(255,255,255,.78) !important; font-weight: 800; }
  .breadcrumb-item.active{ color: rgba(255,255,255,.92) !important; }
  .page-header .page-block{ padding: 16px 20px !important; }
  label{ color: rgba(255,255,255,.8) !important; font-weight: 800 !important; font-size: 13px !important; }
  .table td, .table th{ border-top: 1px solid rgba(255,255,255,.08) !important; color: rgba(255,255,255,.86) !important; }
  .table thead th{ background: rgba(255,255,255,.04) !important; border-bottom: 1px solid rgba(255,255,255,.12) !important; color: #fff; font-weight: 900; letter-spacing: .05em; white-space: nowrap; }
  .table-hover tbody tr:hover td{ background: rgba(255,255,255,.04) !important; }
  .dataTables_wrapper .dataTables_info{ color: rgba(255,255,255,.7) !important; font-weight:700; }
  .dataTables_wrapper .dataTables_paginate{ margin-top:10px; }
  .dataTables_wrapper .dataTables_paginate .paginate_button{
    border-radius:999px !important; border:1px solid rgba(255,255,255,.14) !important;
    margin:0 3px !important; background:rgba(255,255,255,.06) !important;
    color:rgba(255,255,255,.8) !important; font-weight:800 !important;
    padding:0.4em 0.9em !important;
  }
  .dataTables_wrapper .dataTables_paginate .paginate_button:hover{
    background:rgba(255,255,255,.12) !important; color:#fff !important;
  }
  .dataTables_wrapper .dataTables_paginate .paginate_button.current{
    background:rgba(32,66,127,.35) !important;
    border-color:rgba(46,88,168,.45) !important; color:#fff !important; font-weight:1000;
  }
  .dataTables_wrapper .dataTables_paginate .paginate_button.disabled{
    opacity:.4 !important; cursor:not-allowed !important;
    background:transparent !important; border-color:transparent !important;
  }
</style>
