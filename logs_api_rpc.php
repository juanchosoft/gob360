<?php
    include './admin/include/head.php';
    require './admin/include/generic_classes.php';
?>

<style>
    :root {
        --nav-blue: #20427F;
        --nav-blue-2: #132b52;
        --ink: #0f172a;
        --muted: #64748b;
        --line: rgba(2,6,23,.10);
        --bg: #f7f9fc;
        --radius-xl: 18px;
        --radius-lg: 14px;
        --radius-md: 12px;
        --shadow-soft: 0 10px 28px rgba(2,6,23,.10);
        --ok: #16a34a;
        --warn: #f59e0b;
        --bad: #dc2626;
        --info: #0ea5e9;
    }

    body { background: var(--bg) !important; }

    .card {
        border-radius: var(--radius-xl) !important;
        border: 1px solid var(--line) !important;
        box-shadow: var(--shadow-soft) !important;
        overflow: hidden;
        background: #fff;
    }
    .card-header {
        background: linear-gradient(135deg, rgba(32,66,127,.10), rgba(19,43,82,.06)) !important;
        border-bottom: 1px solid rgba(2,6,23,.10) !important;
        font-weight: 900;
    }
    .card-header h5, .card-header h6 {
        font-weight: 900 !important;
        margin: 0;
        color: var(--ink);
    }

    .form-control {
        border-radius: var(--radius-md) !important;
        border: 1px solid rgba(15,23,42,.16) !important;
        font-weight: 700 !important;
        min-height: 38px;
    }

    .stat-card {
        border-radius: 14px;
        padding: 16px 20px;
        text-align: center;
        border: 1px solid rgba(15,23,42,.10);
        background: linear-gradient(180deg, #fff, #fbfdff);
        box-shadow: 0 6px 16px rgba(2,6,23,.06);
    }
    .stat-card .stat-number {
        font-size: 1.8rem;
        font-weight: 1000;
    }
    .stat-card .stat-label {
        font-size: 0.85rem;
        color: var(--ink);
        font-weight: 800;
        line-height: 1.2;
        margin-top: 4px;
    }

    .badge-ok { background: #d4edda; color: #155724; font-weight: 800; }
    .badge-error { background: #f8d7da; color: #721c24; font-weight: 800; }
    .badge-curl { background: #fff3cd; color: #856404; font-weight: 800; }
    .badge-http { background: #d1ecf1; color: #0c5460; font-weight: 800; }
    .badge-json { background: #e2e3e5; color: #383d41; font-weight: 800; }
    .badge-rpc { background: #f5c6cb; color: #721c24; font-weight: 800; }
    .badge-config { background: #ffe5b4; color: #8b4513; font-weight: 800; }

    .modal-content {
        border-radius: 18px !important;
        overflow: hidden;
        border: 1px solid rgba(15,23,42,.12) !important;
    }
    .modal-header {
        background: linear-gradient(135deg, var(--nav-blue), var(--nav-blue-2)) !important;
        color: #fff !important;
    }
    .modal-header .modal-title { font-weight: 1000 !important; }
    .modal-header .close span { color: #fff !important; }

    .json-viewer {
        background: #1e1e2e;
        color: #cdd6f4;
        padding: 16px;
        border-radius: 12px;
        font-family: 'Fira Code', 'Consolas', monospace;
        font-size: 0.82rem;
        max-height: 400px;
        overflow: auto;
        white-space: pre-wrap;
        word-break: break-all;
    }
    .json-viewer .json-key { color: #89b4fa; }
    .json-viewer .json-string { color: #a6e3a1; }
    .json-viewer .json-number { color: #fab387; }
    .json-viewer .json-boolean { color: #f38ba8; }
    .json-viewer .json-null { color: #6c7086; }

    #tablaLogs tbody tr { cursor: pointer; }
    #tablaLogs tbody tr:hover { background: rgba(32,66,127,.06) !important; }

    .header-api-badge {
        background: linear-gradient(135deg, #7c4dff, #536dfe);
        color: #fff;
        padding: 6px 14px;
        border-radius: 20px;
        font-size: 0.8rem;
        font-weight: 700;
    }
</style>

<body class="">
    <!-- Pre-loader -->
    <div class="loader-bg">
        <div class="loader-track">
            <div class="loader-fill"></div>
        </div>
    </div>

    <?php include './admin/include/navbar.php'; ?>
    <?php include './admin/include/header.php'; ?>

    <div class="pcoded-main-container">
        <div class="pcoded-content">
            <!-- Breadcrumb -->
            <div class="page-header">
                <div class="page-block">
                    <div class="row align-items-center">
                        <div class="col-md-12">
                            <div class="page-header-title d-flex align-items-center justify-content-between">
                                <div>
                                    <h5 class="m-b-10">
                                        <i class="feather icon-activity"></i>
                                        Logs API JSON-RPC - Proyectos
                                    </h5>
                                    <p class="text-muted mb-0">
                                        Registro de todas las peticiones y respuestas de la API externa
                                    </p>
                                </div>
                                <div>
                                    <span class="header-api-badge">
                                        <i class="feather icon-server"></i> Monitor de API
                                    </span>
                                    <a href="proyectos_rpc_dash.php" class="btn btn-outline-primary btn-sm ml-2">
                                        <i class="feather icon-arrow-left"></i> Volver al Dashboard
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tarjetas de estadísticas -->
            <div class="row mb-4" id="statsContainer">
                <div class="col-lg-2 col-md-4 col-6 mb-3">
                    <div class="stat-card">
                        <div class="stat-label"><i class="feather icon-send"></i> Total Peticiones a la API</div>
                        <div class="stat-number text-primary" id="statTotal">-</div>
                    </div>
                </div>
                <div class="col-lg-2 col-md-4 col-6 mb-3">
                    <div class="stat-card">
                        <div class="stat-label"><i class="feather icon-check-circle" style="color:var(--ok);"></i> Peticiones Exitosas</div>
                        <div class="stat-number text-success" id="statOk">-</div>
                    </div>
                </div>
                <div class="col-lg-2 col-md-4 col-6 mb-3">
                    <div class="stat-card">
                        <div class="stat-label"><i class="feather icon-alert-triangle" style="color:var(--bad);"></i> Peticiones con Error</div>
                        <div class="stat-number text-danger" id="statErrores">-</div>
                    </div>
                </div>
                <div class="col-lg-2 col-md-4 col-6 mb-3">
                    <div class="stat-card">
                        <div class="stat-label"><i class="feather icon-clock" style="color:var(--info);"></i> Tiempo Promedio (seg)</div>
                        <div class="stat-number text-info" id="statTiempo">-</div>
                    </div>
                </div>
                <div class="col-lg-2 col-md-4 col-6 mb-3">
                    <div class="stat-card">
                        <div class="stat-label"><i class="feather icon-activity" style="color:var(--warn);"></i> Peticiones Últimas 24h</div>
                        <div class="stat-number text-warning" id="stat24h">-</div>
                    </div>
                </div>
                <div class="col-lg-2 col-md-4 col-6 mb-3">
                    <div class="stat-card">
                        <div class="stat-label"><i class="feather icon-database"></i> Registros Retornados</div>
                        <div class="stat-number text-dark" id="statRegistros">-</div>
                    </div>
                </div>
            </div>

            <!-- Filtros -->
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h6><i class="feather icon-filter"></i> Filtros</h6>
                    <button class="btn btn-sm btn-outline-danger" id="btnLimpiarLogs" title="Limpiar logs antiguos">
                        <i class="feather icon-trash-2"></i> Limpiar Logs Antiguos
                    </button>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-2">
                            <label class="font-weight-bold">Fecha Desde</label>
                            <input type="date" class="form-control" id="filtroFechaDesde">
                        </div>
                        <div class="col-md-2">
                            <label class="font-weight-bold">Fecha Hasta</label>
                            <input type="date" class="form-control" id="filtroFechaHasta">
                        </div>
                        <div class="col-md-2">
                            <label class="font-weight-bold">Estado</label>
                            <select class="form-control" id="filtroEstado">
                                <option value="Todos">Todos</option>
                                <option value="OK">OK</option>
                                <option value="ERROR_CURL">Error cURL</option>
                                <option value="ERROR_HTTP">Error HTTP</option>
                                <option value="ERROR_JSON">Error JSON</option>
                                <option value="ERROR_RPC">Error RPC</option>
                                <option value="ERROR_CONFIG">Error Config</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="font-weight-bold">Método</label>
                            <select class="form-control" id="filtroMetodo">
                                <option value="Todos">Todos</option>
                                <option value="consultarProyectos">consultarProyectos</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="font-weight-bold">Registros</label>
                            <select class="form-control" id="filtroLimit">
                                <option value="25">25</option>
                                <option value="50" selected>50</option>
                                <option value="100">100</option>
                                <option value="200">200</option>
                            </select>
                        </div>
                        <div class="col-md-2 d-flex align-items-end">
                            <button class="btn btn-primary btn-block" id="btnFiltrar">
                                <i class="feather icon-search"></i> Filtrar
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tabla de logs -->
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h6><i class="feather icon-list"></i> Historial de Peticiones</h6>
                    <span class="badge badge-primary" id="badgeTotalLogs">0 registros</span>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="tablaLogs" class="table table-hover table-striped" style="width:100%;">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Fecha</th>
                                    <th>Método</th>
                                    <th>Parámetros</th>
                                    <th>HTTP</th>
                                    <th>Estado</th>
                                    <th>Registros</th>
                                    <th>Tiempo (s)</th>
                                    <th>Usuario</th>
                                    <th>Origen</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>

                    <!-- Paginación manual -->
                    <div class="d-flex justify-content-between align-items-center mt-3">
                        <div>
                            <span class="text-muted" id="paginacionInfo">Mostrando 0 de 0</span>
                        </div>
                        <div>
                            <button class="btn btn-sm btn-outline-primary mr-1" id="btnPaginaAnterior" disabled>
                                <i class="feather icon-chevron-left"></i> Anterior
                            </button>
                            <button class="btn btn-sm btn-outline-primary" id="btnPaginaSiguiente" disabled>
                                Siguiente <i class="feather icon-chevron-right"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Detalle del Log -->
    <div class="modal fade" id="modalLogDetalle" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalLogDetalleTitle">
                        <i class="feather icon-file-text"></i> Detalle del Log
                    </h5>
                    <button type="button" class="close" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body" id="modalLogDetalleBody">
                    <!-- Contenido cargado por JS -->
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-primary" id="btnCopiarRequest" title="Copiar Request">
                        <i class="feather icon-copy"></i> Copiar Request
                    </button>
                    <button type="button" class="btn btn-outline-info" id="btnCopiarResponse" title="Copiar Response">
                        <i class="feather icon-copy"></i> Copiar Response
                    </button>
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                </div>
            </div>
        </div>
    </div>

    <?php include './admin/include/footer.php'; ?>

    <!-- Scripts -->
    <script src="assets/js/vendor-all.min.js"></script>
    <script src="assets/js/plugins/bootstrap.min.js"></script>
    <script src="assets/js/pcoded.min.js"></script>

    <!-- DataTables -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap4.min.css">
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap4.min.js"></script>

    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <!-- Script de logs -->
    <script src="admin/js/logs_api_rpc.js"></script>
</body>
</html>
