<?php
include './admin/include/head.php';
include './admin/include/dark_theme.php';
require './admin/include/generic_classes.php';
include './admin/classes/Departamento.php';


$optionMunicipios = '<option value="">Todos los Municipios</option>';

try {


    $codigo_departamento_santander = Util::getDepartamentoPrincipal();

    if ($codigo_departamento_santander) {
        $departamento_obj = new Departamento();
        // Llamado al metodo getMunicipiosByDeptoId
        $arrMunicipios = $departamento_obj->getMunicipiosByDeptoId($codigo_departamento_santander); 

        if (isset($arrMunicipios['output']['response']) && is_array($arrMunicipios['output']['response'])) {
            foreach ($arrMunicipios['output']['response'] as $val) {
                $nombre_municipio = $val['municipio'] ?? ''; 
                $valor_municipio = strtoupper($nombre_municipio);
                
                if (!empty($nombre_municipio)) {
                    $optionMunicipios .= "<option value='" . htmlspecialchars($valor_municipio) . "'>" . htmlspecialchars($nombre_municipio) . "</option>";
                }
            }
        }
    }
} catch (Exception $e) {
    error_log("Error cargando municipios: " . $e->getMessage()); 
}


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
        background: rgba(255, 255, 255, 0.6);
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
    <style>
/* =========================================================
   ✅ TOKENS PRO
========================================================= */
:root{
  --nav-blue:#20427F;
  --nav-blue-2:#132b52;
  --nav-blue-3:#2e58a8;

  --ink:rgba(255,255,255,.86);
  --muted:#64748b;

  --bg:transparent;
  --card: rgba(255,255,255,.06);
  --line: rgba(255,255,255,.10);

  --radius-xl: 18px;
  --radius-lg: 14px;
  --radius-md: 12px;

  --shadow-soft: 0 10px 28px rgba(2,6,23,.10);
  --shadow-mid:  0 18px 44px rgba(2,6,23,.16);

  --ring: 0 0 0 .25rem rgba(46,88,168,.22);

  --ok:#16a34a;
  --warn:#f59e0b;
  --bad:#dc2626;
  --info:#0ea5e9;
}

/* =========================================================
   ✅ GRADIENTE PREMIUM (SIEMPRE VISIBLE - PCoded FIX)
========================================================= */
html{
  min-height: 100% !important;
  background:
    radial-gradient(1400px 600px at 12% -10%, rgba(46,88,168,.36), transparent 60%),
    radial-gradient(1100px 560px at 92% 0%, rgba(32,66,127,.28), transparent 65%),
    radial-gradient(900px 520px at 50% 112%, rgba(19,43,82,.24), transparent 62%),
    linear-gradient(180deg, transparent 0%, transparent 45%, transparent 100%) !important;
  background-attachment: fixed !important;
}

body{
  background: transparent;
  color: rgba(255,255,255,.86);
  overflow-x:hidden !important;
}

/* wrappers PCoded transparentes */
.pcoded-main-container,
.pcoded-wrapper,
.pcoded-content,
.pcoded-inner-content,
.main-body,
.page-wrapper{
  background: transparent;
  background-color: transparent !important;
}

/* velo premium (no ensucia) */
.pcoded-content::before{
  content:"";
  position: fixed;
  inset: 0;
  z-index: -1;
  pointer-events: none;
  background:
    radial-gradient(900px 420px at 14% 12%, rgba(255,255,255,.55), transparent 58%),
    radial-gradient(700px 360px at 86% 18%, rgba(255,255,255,.38), transparent 64%);
}

/* =========================================================
   ✅ PAGE HEADER PRO
========================================================= */
.page-header .page-block{
  background: linear-gradient(135deg, rgba(32,66,127,.20), rgba(255,255,255,.06)) !important;
  border: 1px solid rgba(255,255,255,.08) !important;
  border-radius: var(--radius-xl) !important;
  box-shadow: var(--shadow-soft) !important;
  padding: 16px 18px !important;
}
.page-header h5,
.page-header .m-b-10{
  font-weight: 1000 !important;
  letter-spacing: .2px;
  color: rgba(255,255,255,.86) !important;
}
.breadcrumb,
.breadcrumb a{
  color: rgba(255,255,255,.72) !important;
  font-weight: 700;
}
.breadcrumb a:hover{ color: var(--nav-blue) !important; }

/* =========================================================
   ✅ CARD PRINCIPAL (WOW)
========================================================= */
.card{
  border-radius: var(--radius-xl) !important;
  border: 1px solid var(--line) !important;
  box-shadow: var(--shadow-soft) !important;
  overflow: hidden;
  background: var(--card) !important;
}
.card-header{
  background: linear-gradient(135deg, rgba(32,66,127,.12), rgba(19,43,82,.06)) !important;
  border-bottom: 1px solid var(--line) !important;
}
.card-header h5{
  margin:0;
  font-weight: 1000 !important;
  color: rgba(255,255,255,.86) !important;
}

/* =========================================================
   ✅ CONTENEDOR INTERNO (mejor márgenes responsive)
========================================================= */
.card-body.m-4{ margin: 18px !important; }
@media (max-width: 768px){
  .card-body.m-4{ margin: 12px !important; }
}

/* =========================================================
   ✅ FORM / FILTERS PRO
========================================================= */
.form-label strong{ font-weight: 1000 !important; color: rgba(255,255,255,.85) !important; }

label{ font-weight: 1000 !important; color: rgba(255,255,255,.82) !important; }

.form-control{
  border-radius: var(--radius-md) !important;
  border: 1px solid rgba(255,255,255,.12) !important;
  background: rgba(255,255,255,.06) !important;
  color: #fff !important;
  font-weight: 800 !important;
  min-height: 42px !important;
  box-shadow: 0 6px 16px rgba(0,0,0,.2) !important;
  transition: transform .14s ease, box-shadow .14s ease, border-color .14s ease;
}

.form-control:focus{
  border-color: rgba(32,66,127,.55) !important;
  box-shadow: var(--ring) !important;
  transform: translateY(-1px);
}

/* =========================================================
   ✅ SEARCH CORPORATE (tu HTML ya lo usa)
========================================================= */
.search-corporate{
  position: relative;
}
.search-corporate i{
  position:absolute;
  left: 12px;
  top: 50%;
  transform: translateY(-50%);
  color: rgba(255,255,255,.55);
  font-size: 16px;
  pointer-events:none;
}
.search-corporate input{
  padding-left: 38px !important;
}

/* =========================================================
   ✅ BOTÓN APLICAR FILTROS (BRUTAL)
========================================================= */
#aplicarFiltrosBtn{
  border-radius: 14px !important;
  padding: .68rem 1.05rem !important;
  font-weight: 1000 !important;
  letter-spacing: .2px;
  border: none !important;
  background: linear-gradient(135deg, var(--nav-blue), var(--nav-blue-2)) !important;
  color: #fff !important;
  box-shadow: 0 16px 40px rgba(2,6,23,.18) !important;
  transition: transform .16s ease, box-shadow .16s ease, filter .16s ease;
}
#aplicarFiltrosBtn:hover{
  transform: translateY(-1px);
  filter: brightness(1.02);
  box-shadow: 0 20px 46px rgba(2,6,23,.22) !important;
}
#aplicarFiltrosBtn:active{
  transform: translateY(0px);
  box-shadow: 0 12px 30px rgba(2,6,23,.18) !important;
}

/* =========================================================
   ✅ TABLA PRO (compacta + negro + sin scroll lateral feo)
========================================================= */
.table-responsive.tabla-informacion{
  border-radius: var(--radius-xl) !important;
  border: 1px solid rgba(255,255,255,.10) !important;
  box-shadow: 0 10px 24px rgba(2,6,23,.10) !important;
  background: transparent !important;
  overflow: hidden !important;
}

/* Si usas .tabla-scroll, controlamos el scroll */
.tabla-scroll{
  overflow:auto !important;
  -webkit-overflow-scrolling: touch;
}

/* Si DataTable inyecta table dentro de #dynamictable, forzamos estilos */
#dynamictable{
  width: 100% !important;
  min-width: 900px;
  background: transparent !important;
}

/* DataTables suele crear thead/tbody, aplicamos a todo dentro */
#dynamictable,
#dynamictable table{
  font-size: 12px !important;
  background: transparent !important;
}

#dynamictable tbody tr{
  background: transparent !important;
}
#dynamictable tbody tr:nth-child(even){
  background: rgba(255,255,255,.03) !important;
}

#dynamictable thead th{
  background: linear-gradient(180deg, transparent 0%, rgba(255,255,255,.06) 100%) !important;
  border-bottom: 1px solid rgba(255,255,255,.10) !important;
  color: rgba(255,255,255,.92) !important;
  font-weight: 1000 !important;
  letter-spacing: .08em;
  text-transform: uppercase;
  white-space: nowrap;
  padding: 10px 10px !important;
  vertical-align: middle;
}

#dynamictable tbody td{
  color: rgba(255,255,255,.86) !important;
  font-weight: 800 !important;
  padding: 9px 10px !important;
  line-height: 1.25 !important;
  vertical-align: top;
}

/* Hover fila */
#dynamictable tbody tr:hover{
  background: rgba(32,66,127,.05) !important;
}

/* =========================================================
   ✅ DATATABLES CONTROLS
========================================================= */
.dataTables_wrapper .dataTables_filter,
.dataTables_wrapper .dataTables_length,
.dataTables_wrapper .dataTables_info,
.dataTables_wrapper .dataTables_paginate{
  font-size: 12px !important;
  font-weight: 800 !important;
  color: rgba(255,255,255,.86) !important;
}
.dataTables_wrapper .dataTables_filter input,
.dataTables_wrapper .dataTables_length select{
  border-radius: 12px !important;
  border: 1px solid rgba(255,255,255,.12) !important;
  padding: 6px 10px !important;
  font-size: 12px !important;
  font-weight: 800 !important;
  color: rgba(255,255,255,.86) !important;
  background: rgba(255,255,255,.06) !important;
}

.dataTables_wrapper .dataTables_paginate .paginate_button{
  background: rgba(255,255,255,.06) !important;
  border: 1px solid rgba(255,255,255,.10) !important;
  color: rgba(255,255,255,.86) !important;
  border-radius: 8px !important;
  margin: 0 2px !important;
  padding: 4px 10px !important;
}
.dataTables_wrapper .dataTables_paginate .paginate_button a{
  color: #fff !important;
}
.dataTables_wrapper .dataTables_info{
  color: #fff !important;
}
.dataTables_wrapper .dataTables_length label{
  color: #fff !important;
}
.dataTables_wrapper .dataTables_paginate .paginate_button:hover{
  background: rgba(255,255,255,.12) !important;
  border: 1px solid rgba(255,255,255,.20) !important;
  color: #fff !important;
}
.dataTables_wrapper .dataTables_paginate .paginate_button.current{
  background: rgba(31,111,235,.35) !important;
  border: 1px solid rgba(31,111,235,.50) !important;
  color: #fff !important;
}
.dataTables_wrapper .dataTables_paginate .paginate_button.disabled{
  background: transparent !important;
  border: 1px solid transparent !important;
  color: rgba(255,255,255,.40) !important;
}

/* =========================================================
   ✅ LOADER OVERLAY (más premium)
========================================================= */
.loader-overlay{
  position: fixed;
  inset: 0;
  background: rgba(7,11,20,.55);
  backdrop-filter: blur(6px);
  -webkit-backdrop-filter: blur(6px);
  z-index: 99999;
  display: flex;
  align-items: center;
  justify-content: center;
}

/* Spinner pro */
.spinner{
  width: 64px;
  height: 64px;
  border-radius: 50%;
  border: 7px solid rgba(255,255,255,.10);
  border-top-color: rgba(32,66,127,.85);
  animation: spin .8s linear infinite;
  box-shadow: 0 18px 44px rgba(2,6,23,.18);
}

@keyframes spin{
  0%{ transform: rotate(0deg); }
  100%{ transform: rotate(360deg); }
}

/* =========================================================
   ✅ RESPONSIVE (tablet/cel)
========================================================= */
@media (max-width: 992px){
  .page-header .page-block{ padding: 14px 14px !important; }
}
@media (max-width: 576px){
  #aplicarFiltrosBtn{ width: 100% !important; }
  .card-header h5{ font-size: 15px; }
  #dynamictable{ min-width: 820px; } /* un poquito menos en móvil */
}
button.filtro{
  background: linear-gradient(135deg, var(--nav-blue), var(--nav-blue-2)) !important;
  color:#fff !important;
  border:none !important;
  border-radius:14px !important;
  font-weight:1000 !important;
}

</style>


<body>


    <div class="loader-bg">
        <div class="loader-track">
            <div class="loader-fill"></div>
        </div>
    </div>
    <?php
    include './admin/include/navbar.php';
    ?>
    <?php
    include './admin/include/header.php';
    ?>

    <div class="pcoded-main-container">
        <div class="pcoded-content">
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
                                <li class="breadcrumb-item"><a href="#!">Registro visitas / Información policía</a></li>
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
                            <h5 class="mb-0 text-center w-100">Informe policía Santander</h5>
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
                            <div class="card-body table-border-style">
                                <div class="row mb-4">
                                    <div class="col-lg-8 col-md-7">
                                        <div class="row g-3">
                                            <div class="col-md-6">
                                                <label for="categoriaSelect" class="form-label"><strong>Seleccionar Categoría:</strong></label>
                                                <select class="form-control" id="categoriaSelect">
                                                    <option value="hurtos">Hurtos</option>
                                                    <option value="amenazas">Amenazas</option>
                                                    <option value="desplazamientos">Desplazamientos</option>
                                                    <option value="homicidios">Homicidios</option>
                                                    <option value="secuestros">Secuestros</option>
                                                </select>
                                            </div>
                                            <div class="col-md-6">
                                                <label for="municipioSelect" class="form-label"><strong>Filtrar por Municipio:</strong></label>
                                                <select class="form-control" id="municipioSelect">
                                                    <?= $optionMunicipios ?>
                                                </select>
                                            </div>
                                            <div class="col-md-6">
                                                <label for="fechaInicio" class="form-label"><strong>Rango Desde:</strong></label>
                                                <input type="date" class="form-control" id="fechaInicio">
                                            </div>
                                            <div class="col-md-6">
                                                <label for="fechaFin" class="form-label"><strong>Rango Hasta:</strong></label>
                                                <input type="date" class="form-control" id="fechaFin">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-lg-4 col-md-5">
                                        <div class="d-flex flex-column h-100 justify-content-between">
                                            <div class="mb-3">
                                                <label class="form-label"><strong>Acciones:</strong></label>
                                                <button type="button" class="btn w-100 d-flex align-items-center justify-content-center gap-2 filtro" 
                                                        id="aplicarFiltrosBtn">
                                                    <i class="feather icon-filter"></i>
                                                    <span>Aplicar Filtros</span>
                                                </button>
                                            </div>
                                            <div>
                                                <label for="customSearch" class="form-label mb-2"><strong>Búsqueda Rápida:</strong></label>
                                                <div class="search-corporate">
                                                    <i class="feather icon-file-text"></i>
                                                    <input type="text" id="customSearch" class="form-control" placeholder="Buscar en los registros...">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div id="loader" class="loader-overlay" style="display: none;">
                                    <div class="spinner"></div>
                                </div>
                                <div class="table-responsive tabla-informacion tabla-scroll">
                                    <table class="table table-hover mb-0" id="dynamictable"></table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
    <?php include 'admin/include/footer.php'; ?>
    </div>

    <?php include 'admin/include/gerenic_script.php'; ?>

    <script src="assets/js/vendor-all.min.js"></script>
    <script src="assets/js/plugins/bootstrap.min.js"></script>
    <script src="assets/js/pcoded.min.js"></script>

    <script type="text/javascript" src="admin/js/informacion-policia.js"></script>
    <script type="text/javascript" src="./admin/js/datatables/jquery.dataTables.min.js"></script>
    <link href="./admin/js/datatables/jquery.dataTables.min.css" rel="stylesheet" />

                <style>
      table.dataTable tbody tr{
        background-color: transparent !important;
      }
      table.dataTable.stripe tbody tr.odd,
      table.dataTable.display tbody tr.odd{
        background-color: rgba(255,255,255,.03) !important;
      }
      table.dataTable tbody td{
        color: rgba(255,255,255,.86) !important;
      }
      table.dataTable tbody td a{
        color: rgba(255,255,255,.86) !important;
      }
      table.dataTable tbody td i.feather,
      table.dataTable tbody td i.bi{
        color: rgba(255,255,255,.86) !important;
      }
      #tblVeredas td i.feather{
        color: rgba(255,255,255,.86) !important;
      }
      .dataTables_wrapper .dataTables_paginate .paginate_button{
        color: rgba(255,255,255,.86) !important;
        background: rgba(255,255,255,.06) !important;
        border: 1px solid rgba(255,255,255,.10) !important;
        border-radius: 8px !important;
      }
      .dataTables_wrapper .dataTables_paginate .paginate_button.current,
      .dataTables_wrapper .dataTables_paginate .paginate_button.current:hover{
        color: #fff !important;
        background: rgba(31,111,235,.35) !important;
        border: 1px solid rgba(31,111,235,.50) !important;
      }
      .dataTables_wrapper .dataTables_paginate .paginate_button:hover{
        color: #fff !important;
        background: rgba(255,255,255,.12) !important;
        border: 1px solid rgba(255,255,255,.20) !important;
      }
      .dataTables_wrapper .dataTables_paginate .paginate_button.disabled{
        color: rgba(255,255,255,.30) !important;
        background: transparent !important;
        border: 1px solid transparent !important;
      }
      .dataTables_wrapper .dataTables_info,
      .dataTables_wrapper .dataTables_length label{
        color: #fff !important;
      }
      table.dataTable tbody tr.selected{
        background-color: rgba(31,111,235,.25) !important;
      }
    </style>

</body>

</html>