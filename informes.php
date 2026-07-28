<?php
include './admin/include/head.php';
require './admin/include/generic_classes.php';

include './admin/classes/Informes.php';

//Información de Vistas
$arr = Informes::getAll(null);
$isvalid = $arr['output']['valid'];
$arr = $arr['output']['response'];
$modulo = 'Informes';

?>

<body class="dashboard-body">
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
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">

    <style>
        :root {
            --bg0: #070A12;
            --bg1: #0B1222;
            --card: rgba(255,255,255,.06);
            --stroke: rgba(255,255,255,.10);
            --stroke2: rgba(255,255,255,.14);
            --txt: rgba(255,255,255,.92);
            --muted: rgba(255,255,255,.66);
            --muted2: rgba(255,255,255,.50);
            --good: #18ff6d;
            --warn: #ffd166;
            --bad: #ff5b7a;
            --info: #56ccff;
            --brand: #4f7cff;
            --brand2: #9b5cff;
            --shadow: 0 20px 60px rgba(0,0,0,.35);
        }

        body.dashboard-body {
            background:
                radial-gradient(900px 420px at 10% 10%, rgba(79,124,255,.28), transparent 60%),
                radial-gradient(900px 420px at 80% 20%, rgba(155,92,255,.22), transparent 60%),
                radial-gradient(900px 520px at 50% 100%, rgba(24,255,109,.10), transparent 60%),
                linear-gradient(180deg, var(--bg0), var(--bg1));
            color: var(--txt);
            overflow-x: hidden;
        }
        .pcoded-main-container { background: transparent !important; }
        .pcoded-content { padding-bottom: 2rem; }

        .breadcrumb .breadcrumb-item a{ color: var(--muted) !important; }
        .breadcrumb .breadcrumb-item.active{ color: var(--txt) !important; }

        .page-header h5, .page-header .m-b-10 { color: #ffffff !important; }

        .card {
            border: 1px solid var(--stroke);
            background: rgba(255,255,255,.06);
            border-radius: 18px;
            box-shadow: var(--shadow);
            backdrop-filter: blur(10px);
        }
        .card-header {
            background: transparent !important;
            border-bottom: 1px solid var(--stroke);
        }
        .card-header h5 {
            color: #ffffff !important;
            font-weight: 900;
        }
        .card-body {
            background: transparent;
        }

        #dynamictable {
            width: 100% !important;
            table-layout: auto;
            white-space: normal;
            color: #ffffff;
        }
        #dynamictable thead th {
            background: rgba(0,0,0,.40) !important;
            color: #ffffff !important;
            font-weight: 900 !important;
            font-size: 11.5px;
            letter-spacing: .3px;
            text-transform: uppercase;
            padding: 11px 10px !important;
            border: 0 !important;
        }
        #dynamictable tbody tr {
            transition: background .12s ease;
            border-bottom: 1px solid var(--stroke);
            background: transparent !important;
        }
        #dynamictable tbody tr:nth-child(even),
        #dynamictable tbody tr:nth-child(odd) {
            background: transparent !important;
        }
        #dynamictable tbody tr:hover { background: rgba(255,255,255,.04) !important; }
        #dynamictable td {
            color: #ffffff !important;
            font-weight: 600;
            font-size: 12.5px;
            padding: 11px 10px !important;
            border: 0 !important;
            vertical-align: top;
            white-space: normal !important;
            word-break: break-word !important;
            max-width: 300px;
        }
        #dynamictable td img {
            width: 32px; height: 32px;
            background: rgba(255,255,255,.10);
            border-radius: 10px;
            padding: 4px;
            object-fit: contain;
        }
        #dynamictable td img.img-thumbnail {
            width: 40px; height: 40px;
            background: rgba(255,255,255,.10);
            border-radius: 8px;
            padding: 2px;
            object-fit: cover;
            cursor: pointer;
        }

        .table-responsive {
            overflow-x: auto;
            width: 100%;
        }

        .btn-geo {
            background: rgba(255,255,255,.06);
            border: 1px solid var(--stroke);
            border-radius: 12px;
            padding: 6px 12px;
            transition: .15s ease;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
        }
        .btn-geo:hover { transform: translateY(-1px); box-shadow: 0 6px 14px rgba(0,0,0,.25); }

        .btn.btn-secondary {
            border: 1px solid var(--stroke2);
            background: rgba(255,255,255,.06);
            color: #ffffff !important;
            border-radius: 12px;
            padding: .6rem .85rem;
            font-weight: 900;
            transition: .2s ease;
            box-shadow: 0 10px 24px rgba(0,0,0,.25);
        }
        .btn.btn-secondary:hover {
            transform: translateY(-1px);
            background: rgba(255,255,255,.10);
            color: #ffffff !important;
        }

        #modalGeocalizacion .modal-content {
            background: rgba(11,18,34,.98);
            border: 1px solid var(--stroke);
            border-radius: 18px;
        }
        #modalGeocalizacion .modal-header {
            border-bottom: 1px solid var(--stroke);
        }
        #modalGeocalizacion .modal-title { color: #ffffff !important; font-weight: 900; }
        #modalGeocalizacion .close { color: #ffffff !important; }
        #modalGeocalizacion .modal-body { color: var(--txt); }
        #modalGeocalizacion .modal-footer {
            border-top: 1px solid var(--stroke);
        }

        #imagenModal {
            transition: opacity 0.3s ease-in-out;
            opacity: 0;
        }

        #modalImagen.show #imagenModal {
            opacity: 1;
        }

        html, body {
            overflow-x: hidden !important;
        }
    </style>

    <div class="pcoded-main-container">
        <div class="pcoded-content">
            <!-- [ breadcrumb ] start -->
            <div class="page-header">
                <div class="page-block">
                    <div class="row align-items-center">
                        <div class="col-md-12">
                            <div class="d-flex justify-content-between align-items-center">
                                <h5 class="m-b-10">Informes </h5>
<?php include './admin/include/btn_back.php'; ?>
                            </div>
                            <ul class="breadcrumb">
                                <li class="breadcrumb-item"><a href="index.html"><i class="feather icon-home"></i></a>
                                </li>
                                <li class="breadcrumb-item"><a href="#!">Información suministrada por la población</a>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
            <!-- [ breadcrumb ] end -->
            <!-- [ Main Content ] start -->
            <div class="row">
                <!-- [ sample-page ] start -->
                <div class="col-sm-12">
                    <div class="card">

                        <div class="card-header">
                            <h5>Listado</h5>
                            <div class="card-header-right">
                                <div class="btn-group card-option">
                                    <button type="button" class="btn dropdown-toggle btn-icon" data-toggle="dropdown"
                                        aria-haspopup="true" aria-expanded="false">
                                        <i class="feather icon-more-horizontal"></i>
                                    </button>
                                    <ul class="list-unstyled card-option dropdown-menu dropdown-menu-right">
                                        <li class="dropdown-item full-card"><a href="#!"><span><i
                                                        class="feather icon-maximize"></i> maximize</span><span
                                                    style="display:none"><i class="feather icon-minimize"></i>
                                                    Restore</span></a></li>
                                        <li class="dropdown-item minimize-card"><a href="#!"><span><i
                                                        class="feather icon-minus"></i> collapse</span><span
                                                    style="display:none"><i class="feather icon-plus"></i>
                                                    expand</span></a></li>
                                        <li class="dropdown-item reload-card"><a href="#!"><i
                                                    class="feather icon-refresh-cw"></i> reload</a></li>
                                        <li class="dropdown-item close-card"><a href="#!"><i
                                                    class="feather icon-trash"></i> remove</a></li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="col-lg-12">

                                <div class="card-body table-border-style">
                                    <!-- Tabla de datos -->
                                    <div class="table-responsive">
                                        <table id="dynamictable" class="table table-hover mb-0">
                                            <thead>
                                                <tr class="border-1">
                                                     <th>id</th>
                                                    <th>Mapa</th>
                                                    <th>Municipio</th>
                                                    <th>Vereda</th>
                                                    <th>Tipo</th>
                                                    <th>Zona</th>
                                                    <th>Nombre</th>
                                                    <th>Modo Reporte</th>
                                                    <th>Secretaria</th>
                                                    <th>Secretario</th>
                                                    <th>Observaciones </th>
                                                    <th>Fotos </th>
                                                    <th>Fecha </th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php if ($isvalid): ?>
                                                <?php
                                                    $imgBasePath = "assets/img/admin/";
                                                    foreach ($arr as $item):
                                                        $img = !empty($item["img"]) ? $imgBasePath . htmlspecialchars($item["img"]) : 'dist/img/santander.png';
                                                    ?>
                                                <tr>
                                                    <td>
                                                        <?php if (!empty($item['latitud']) && !empty($item['longitud'])): ?>
                                                        <button type="button" class="btn btn-primary btn-geo"
                                                            data-toggle="modal" data-target="#modalGeocalizacion"
                                                            onclick="initMap('<?= htmlspecialchars($item['longitud']) ?>','<?= htmlspecialchars($item['latitud']) ?>','<?= htmlspecialchars($item['icono']) ?>')">
                                                            <img src="assets/iconos/geo.png" alt="" width="30px">
                                                        </button>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td><?php echo htmlspecialchars($item['id']); ?></td>
                                                    <td><?php echo htmlspecialchars($item['municipio']); ?></td>
                                                    <td><?php echo htmlspecialchars($item['nombre_vereda']); ?></td>
                                                    <td><?php echo htmlspecialchars($item['tipo']); ?></td>
                                                    <td><?php echo htmlspecialchars($item['zona']); ?></td>
                                                    <td><?php echo htmlspecialchars($item['nombre']); ?></td>
                                                    <td><?php echo htmlspecialchars($item['modoReporte']); ?></td>
                                                    <td><?php echo htmlspecialchars($item['secretaria']); ?></td>
                                                    <td><?php echo htmlspecialchars($item['secretario']); ?></td>
                                                    <td><?php echo htmlspecialchars($item['observaciones']); ?></td>
                                                    <td>
                                                        <?php if (!empty($item['imagenes']) && is_array($item['imagenes'])): ?>
                                                        <?php foreach ($item['imagenes'] as $img): ?>
                                                        <img src="<?php echo htmlspecialchars($img['ruta_imagen']); ?>"
                                                            alt="Imagen informe" width="40"
                                                            class="img-thumbnail img-click"
                                                            data-img="<?php echo htmlspecialchars($img['ruta_imagen']); ?>">
                                                        <?php endforeach; ?>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td><?php echo htmlspecialchars($item['created_at']); ?></td>
                                                </tr>
                                                <?php endforeach; ?>
                                                <?php endif; ?>
                                            </tbody>
                                        </table>

                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- ============================================================== -->
                        <!-- end campaign activities   -->
                        <!-- ============================================================== -->
                    </div>
                </div>
                <!-- ============================================================== -->
                <!-- footer -->
                <!-- ============================================================== -->

            </div>
        </div>
    </div>
    <!-- [ sample-page ] end -->
    </div>
    <!-- [ Main Content ] end -->
    </div>
    </div>
    <!-- [ Main Content ] end -->

    <!-- modal de geocalizacion -->
    <div class="card-body">
        <div id="modalGeocalizacion" class="modal fade" tabindex="-1" role="dialog"
            aria-labelledby="modalGeocalizacionTitle" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="modalGeocalizacionTitle">Geolocalización Informe<span
                                id="nombrePilar"></span></h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                                aria-hidden="true">&times;</span></button>
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

    </div>

    <!-- Modal para mostrar imagen -->
    <div class="modal fade" id="modalImagen" tabindex="-1" role="dialog" aria-labelledby="modalImagenLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content bg-white">
                <div class="modal-header border-0">
                    <h5 class="modal-title">Vista previa</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body text-center">
                    <img id="imagenModal" src="" alt="Imagen ampliada" class="img-fluid rounded shadow-sm"
                        style="max-height: 80vh; object-fit: contain;">
                </div>
            </div>
        </div>
    </div>

    <!-- Required Js -->
    <?php include 'admin/include/gerenic_script.php'; ?>
    <script type="text/javascript" src="admin/js/departamento.js"></script>
    <script type="text/javascript" src="admin/js/detalle_visitas.js"></script>

    <script src="assets/js/vendor-all.min.js"></script>
    <script src="assets/js/plugins/bootstrap.min.js"></script>
    <script src="assets/js/pcoded.min.js"></script>

    <?php include './admin/include/generic_dataTables.php'; ?>
    <style>
        .dataTables_wrapper .dataTables_info,
        .dataTables_wrapper .dataTables_length label {
            color: #ffffff !important;
            font-weight: 700;
        }
        .dataTables_wrapper .dataTables_paginate {
            margin-top: 10px;
        }
        .dataTables_wrapper .dataTables_paginate .paginate_button {
            color: rgba(255,255,255,.86) !important;
            background: rgba(255,255,255,.06) !important;
            border: 1px solid rgba(255,255,255,.10) !important;
            border-radius: 8px !important;
            font-weight: 800 !important;
            padding: 0.4em 0.9em !important;
            margin: 0 3px !important;
        }
        .dataTables_wrapper .dataTables_paginate .paginate_button:hover {
            color: #fff !important;
            background: rgba(255,255,255,.12) !important;
            border: 1px solid rgba(255,255,255,.20) !important;
        }
        .dataTables_wrapper .dataTables_paginate .paginate_button.current,
        .dataTables_wrapper .dataTables_paginate .paginate_button.current:hover {
            color: #fff !important;
            background: rgba(31,111,235,.35) !important;
            border: 1px solid rgba(31,111,235,.50) !important;
        }
        .dataTables_wrapper .dataTables_paginate .paginate_button.disabled {
            color: rgba(255,255,255,.30) !important;
            background: transparent !important;
            border: 1px solid transparent !important;
            opacity: .4 !important;
            cursor: not-allowed !important;
        }
    </style>
    <!-- Google Maps JavaScript API -->
    <!-- Google Maps JavaScript API -->
    <script async defer src="https://maps.googleapis.com/maps/api/js?key=AIzaSyAbqZVEnSpIPF5dKe89pju2rmQaM58wvY0">
    </script>
    <script>
        let map;
        let trafficLayer, transitLayer, bicycleLayer;
        var informacionMapaFactores = [];

        function initMap(longitud, latitud, icono) {
            if (typeof google !== 'undefined' && google.maps) {
                // Coordenadas por defecto
                const defaultLocation = {
                    lat: 7.0830880750303935,
                    lng: -73.02794598535458
                };
                // Si las coordenadas están definidas, usarlas; sino, usar las coordenadas por defecto
                const initialLocation = {
                    lat: latitud ? parseFloat(latitud) : defaultLocation.lat,
                    lng: longitud ? parseFloat(longitud) : defaultLocation.lng
                };
                // Determinar el icono a usar
                let iconUrl = "assets/iconos/maps/geo.png";
                if (icono && icono.trim() !== "") {
                    iconUrl = icono;
                }
                // Crear el mapa
                map = new google.maps.Map(document.getElementById("map"), {
                    center: initialLocation,
                    zoom: 15,
                });
                // Agregar un solo marcador en el punto seleccionado
                const marker = new google.maps.Marker({
                    position: initialLocation,
                    map: map,
                    icon: {
                        url: iconUrl,
                        scaledSize: new google.maps.Size(60, 60)
                    }
                });
            } else {
                console.error('Google Maps API no está disponible.');
            }
        }
        document.addEventListener("DOMContentLoaded", function() {
            document.querySelectorAll('.img-click').forEach(function(img) {
                img.addEventListener('click', function() {
                    const src = this.getAttribute('data-img');
                    document.getElementById('imagenModal').src = src;
                    $('#modalImagen').modal('show');
                });
            });
        });
    </script>
</body>

</html>