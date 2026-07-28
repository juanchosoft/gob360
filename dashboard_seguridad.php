<?php
include './admin/include/head.php';
require './admin/include/generic_classes.php';

$db  = new DbConection();
$pdo = $db->openConect();

$tablaInversion = $db->getTable('tbl_inversion_seguridad');

$municipiosMap = [
    '68001' => 'Bucaramanga',
    '68020' => 'Albania',
    '68077' => 'Barbosa',
    '68081' => 'Barichara',
    '68190' => 'California',
    '68276' => 'Floridablanca',
    '68307' => 'Girón',
    '68397' => 'Piedecuesta',
    '68679' => 'San Gil',
    '68861' => 'Socorro'
];

if (!function_exists('riesgoColor')) {
    function riesgoColor($valor)
    {
        $valor = (float)$valor;
        if ($valor > 100000000000) return '#ff5252';
        if ($valor > 20000000000) return '#ffc107';
        return '#00e676';
    }
}

if (!function_exists('pesos')) {
    function pesos($n)
    {
        return '$' . number_format((float)$n, 0, ',', '.');
    }
}

if (!function_exists('normalizarTipoSeccion')) {
    function normalizarTipoSeccion($texto)
    {
        $texto = trim((string)$texto);
        $texto = mb_strtolower($texto, 'UTF-8');

        $reemplazos = [
            'á' => 'a',
            'é' => 'e',
            'í' => 'i',
            'ó' => 'o',
            'ú' => 'u',
            'ñ' => 'n'
        ];

        $texto = strtr($texto, $reemplazos);
        $texto = preg_replace('/\s+/', ' ', $texto);

        return $texto;
    }
}

if (!function_exists('obtenerColumnaFechaDisponible')) {
    function obtenerColumnaFechaDisponible(PDO $pdo, string $tabla): ?string
    {
        $candidatas = ['created_at', 'fecha', 'fecha_registro', 'dtcreate', 'created_date', 'fecha_creacion'];

        try {
            $stmt = $pdo->query("DESCRIBE {$tabla}");
            $columnas = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $nombres = array_map(static function ($col) {
                return $col['Field'] ?? '';
            }, $columnas);

            foreach ($candidatas as $candidata) {
                if (in_array($candidata, $nombres, true)) {
                    return $candidata;
                }
            }
        } catch (Throwable $e) {
            return null;
        }

        return null;
    }
}

/* =========================================
   FILTRO FECHAS
========================================= */
$dateColumn = obtenerColumnaFechaDisponible($pdo, $tablaInversion);

$fechaDesde = isset($_GET['fecha_desde']) ? trim((string)$_GET['fecha_desde']) : '';
$fechaHasta = isset($_GET['fecha_hasta']) ? trim((string)$_GET['fecha_hasta']) : '';

$whereParts = [];
$params = [];

if ($dateColumn !== null) {
    if ($fechaDesde !== '' && preg_match('/^\d{4}-\d{2}-\d{2}$/', $fechaDesde)) {
        $whereParts[] = "DATE({$dateColumn}) >= :fecha_desde";
        $params[':fecha_desde'] = $fechaDesde;
    }

    if ($fechaHasta !== '' && preg_match('/^\d{4}-\d{2}-\d{2}$/', $fechaHasta)) {
        $whereParts[] = "DATE({$dateColumn}) <= :fecha_hasta";
        $params[':fecha_hasta'] = $fechaHasta;
    }
}

$whereSql = !empty($whereParts) ? ' WHERE ' . implode(' AND ', $whereParts) : '';

/* =========================
   CONSULTA GENERAL
========================= */
$sql = "
    SELECT 
        tipo_seccion,
        COUNT(*) AS total_registros,
        COALESCE(SUM(valor), 0) AS total_valor
    FROM {$tablaInversion}
    {$whereSql}
    GROUP BY tipo_seccion
";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$data = $stmt->fetchAll(PDO::FETCH_ASSOC);

/* =========================
   MUNICIPIOS GRAFICA
========================= */
$whereMunicipios = [];
$paramsMunicipios = [];

if ($dateColumn !== null) {
    if ($fechaDesde !== '' && preg_match('/^\d{4}-\d{2}-\d{2}$/', $fechaDesde)) {
        $whereMunicipios[] = "DATE({$dateColumn}) >= :fecha_desde";
        $paramsMunicipios[':fecha_desde'] = $fechaDesde;
    }

    if ($fechaHasta !== '' && preg_match('/^\d{4}-\d{2}-\d{2}$/', $fechaHasta)) {
        $whereMunicipios[] = "DATE({$dateColumn}) <= :fecha_hasta";
        $paramsMunicipios[':fecha_hasta'] = $fechaHasta;
    }
}

$whereMunicipios[] = "i.municipio IS NOT NULL";
$whereMunicipios[] = "TRIM(i.municipio) != ''";

$sqlM = "
    SELECT 
        i.municipio,
        COALESCE(SUM(i.valor), 0) AS total,
        COALESCE(NULLIF(TRIM(cau.municipio), ''), NULLIF(TRIM(c.municipio), ''), i.municipio) AS nombre_municipio
    FROM {$tablaInversion} i
    LEFT JOIN " . $db->getTable('tbl_ciudades_accion_unificada') . " cau ON CAST(i.municipio AS CHAR) = CAST(cau.codigo_muncipio AS CHAR)
    LEFT JOIN " . $db->getTable('tbl_ciudades') . " c ON CAST(i.municipio AS CHAR) = CAST(c.codigo_muncipio AS CHAR)
    WHERE " . implode(' AND ', $whereMunicipios) . "
    GROUP BY i.municipio
    ORDER BY total DESC
    LIMIT 10
";
$stmtM = $pdo->prepare($sqlM);
$stmtM->execute($paramsMunicipios);
$dataM = $stmtM->fetchAll(PDO::FETCH_ASSOC);

$dataM = is_array($dataM) ? array_values(array_filter($dataM, function ($m) {
    return isset($m['municipio']) && trim((string)$m['municipio']) !== '';
})) : [];

/* =========================
   INSTITUCIONES GRAFICA
========================= */
require_once './admin/classes/Inversion.php';
$_instResult = Inversion::getByInstitucion([
    'fecha_desde' => $fechaDesde,
    'fecha_hasta' => $fechaHasta,
]);
$dataInst = $_instResult['output']['valid'] ? ($_instResult['output']['response'] ?? []) : [];

$_provResult = Inversion::getByProvincia([
    'fecha_desde' => $fechaDesde,
    'fecha_hasta' => $fechaHasta,
]);
$dataProv = $_provResult['output']['valid'] ? ($_provResult['output']['response'] ?? []) : [];


/* =========================
   CONSULTA TITULOS POR SECCION
========================= */
/* =========================
   CONSULTA TITULO Y DESCRIPCION POR SECCION
========================= */
$detallesPorTipo = [];

try {
    $sqlDetalles = "
        SELECT 
            tipo_seccion,
            titulo,
            descripcion,
            COUNT(*) AS total_items,
            COALESCE(SUM(valor), 0) AS total_valor
        FROM {$tablaInversion}
        {$whereSql}
        WHERE titulo IS NOT NULL
          AND TRIM(titulo) != ''
        GROUP BY tipo_seccion, titulo, descripcion
        ORDER BY tipo_seccion ASC, total_valor DESC, titulo ASC
    ";

    if (!empty($whereParts)) {
        $sqlDetalles = "
            SELECT 
                tipo_seccion,
                titulo,
                descripcion,
                COUNT(*) AS total_items,
                COALESCE(SUM(valor), 0) AS total_valor
            FROM {$tablaInversion}
            {$whereSql}
              AND titulo IS NOT NULL
              AND TRIM(titulo) != ''
            GROUP BY tipo_seccion, titulo, descripcion
            ORDER BY tipo_seccion ASC, total_valor DESC, titulo ASC
        ";
    }

    $stmtDetalles = $pdo->prepare($sqlDetalles);
    $stmtDetalles->execute($params);
    $rowsDetalles = $stmtDetalles->fetchAll(PDO::FETCH_ASSOC);

    foreach ($rowsDetalles as $rowDetalle) {
        $tipoNormalizado = normalizarTipoSeccion($rowDetalle['tipo_seccion'] ?? '');

        if (!isset($detallesPorTipo[$tipoNormalizado])) {
            $detallesPorTipo[$tipoNormalizado] = [];
        }

        $detallesPorTipo[$tipoNormalizado][] = [
            'titulo'      => (string)($rowDetalle['titulo'] ?? ''),
            'descripcion' => (string)($rowDetalle['descripcion'] ?? ''),
            'total_items' => (int)($rowDetalle['total_items'] ?? 0),
            'total_valor' => (float)($rowDetalle['total_valor'] ?? 0),
        ];
    }
} catch (Throwable $e) {
    $detallesPorTipo = [];
}

/* =========================
   ORGANIZAR DATA
========================= */
$datos = [
    'movilidad'       => ['total' => 0, 'valor' => 0],
    'tecnologia'      => ['total' => 0, 'valor' => 0],
    'proyectos'       => ['total' => 0, 'valor' => 0],
    'intendencia'     => ['total' => 0, 'valor' => 0],
    'infraestructura' => ['total' => 0, 'valor' => 0],
    'convenios'       => ['total' => 0, 'valor' => 0],
    'pagos'           => ['total' => 0, 'valor' => 0],
];

$totalGlobal = 0;
$valorGlobal = 0;

foreach ($data as $row) {
    $tipoOriginal = $row['tipo_seccion'] ?? '';
    $tipo = normalizarTipoSeccion($tipoOriginal);

    $totalFila = (int)($row['total_registros'] ?? 0);
    $valorFila = (float)($row['total_valor'] ?? 0);

    $totalGlobal += $totalFila;
    $valorGlobal += $valorFila;

    if (isset($datos[$tipo])) {
        $datos[$tipo]['total'] += $totalFila;
        $datos[$tipo]['valor'] += $valorFila;
    }
}


$fechaActualizacion = date('Y-m-d H:i');
?>

<body class="dashboard-body">
    <div class="loader-bg" id="pageLoader">
        <div class="loader-track">
            <div class="loader-fill"></div>
        </div>
    </div>

    <?php include './admin/include/navbar.php'; ?>
    <?php include './admin/include/header.php'; ?>

    <style>
        body.dashboard-body{
            background:
                radial-gradient(circle at 15% 20%, rgba(61, 90, 254, .18), transparent 22%),
                radial-gradient(circle at 85% 18%, rgba(0, 229, 255, .12), transparent 24%),
                radial-gradient(circle at 50% 100%, rgba(18, 210, 146, .10), transparent 30%),
                linear-gradient(135deg, #07111f 0%, #081726 28%, #0a1324 55%, #07111f 100%) !important;
            color: #fff !important;
            min-height: 100vh;
            overflow-x: hidden;
        }

        .loader-track{
            width: 260px;
            height: 10px;
            border-radius: 999px;
            overflow: hidden;
            background: rgba(255,255,255,.10);
            box-shadow: inset 0 2px 10px rgba(0,0,0,.35);
            position: relative;
        }

        .loader-fill{
            width: 40%;
            height: 100%;
            border-radius: 999px;
            background: linear-gradient(90deg, #3b82f6, #06b6d4, #22c55e);
            animation: loadingMove 1.2s linear infinite;
            box-shadow: 0 0 20px rgba(6,182,212,.35);
        }

        @keyframes loadingMove{
            0%   { transform: translateX(-120%); }
            100% { transform: translateX(320%); }
        }

        .dashboard-wrap{
            padding: 10px 8px 30px;
        }

        .hero-panel{
            position: relative;
            overflow: hidden;
            border-radius: 28px;
            padding: 24px 24px 20px;
            background:
                linear-gradient(135deg, rgba(30,41,59,.88) 0%, rgba(17,24,39,.92) 40%, rgba(18,37,68,.92) 100%);
            border: 1px solid rgba(255,255,255,.10);
            box-shadow:
                0 20px 60px rgba(0,0,0,.35),
                inset 0 1px 0 rgba(255,255,255,.06);
            margin-bottom: 22px;
        }

        .hero-panel::before{
            content:"";
            position:absolute;
            inset:auto -10% -40% auto;
            width:420px;
            height:420px;
            background: radial-gradient(circle, rgba(0,229,255,.14), transparent 65%);
            pointer-events:none;
        }

        .hero-panel::after{
            content:"";
            position:absolute;
            top:-80px;
            left:-60px;
            width:260px;
            height:260px;
            background: radial-gradient(circle, rgba(124,58,237,.16), transparent 70%);
            pointer-events:none;
        }

        .top-badges{
            display:flex;
            flex-wrap:wrap;
            gap:10px;
            margin-bottom:14px;
            position:relative;
            z-index:2;
        }

        .badge-chip{
            display:inline-flex;
            align-items:center;
            gap:8px;
            padding:8px 14px;
            border-radius:999px;
            font-size:12px;
            font-weight:600;
            color:#dbeafe;
            background:rgba(15,23,42,.55);
            border:1px solid rgba(255,255,255,.08);
            backdrop-filter: blur(10px);
        }

        .hero-content{
            display:flex;
            justify-content:space-between;
            gap:18px;
            align-items:center;
            flex-wrap:wrap;
            position:relative;
            z-index:2;
        }

        .hero-title{
            margin:0;
            font-size:38px;
            line-height:1.05;
            font-weight:800;
            color:#ffffff;
            letter-spacing:-0.5px;
        }

        .hero-subtitle{
            margin-top:10px;
            color:#9fb2d4;
            font-size:14px;
            max-width:760px;
        }

        .hero-actions{
            display:flex;
            gap:10px;
            flex-wrap:wrap;
        }

        .hero-btn{
            border:none;
            outline:none;
            cursor:pointer;
            padding:12px 16px;
            border-radius:14px;
            font-size:13px;
            font-weight:700;
            color:#fff;
            transition:.25s ease;
            box-shadow: inset 0 1px 0 rgba(255,255,255,.08);
        }

        .hero-btn.primary{
            background: linear-gradient(135deg, #3b82f6, #4f46e5);
            border:1px solid rgba(255,255,255,.12);
        }

        .hero-btn.success{
            background: linear-gradient(135deg, #0f766e, #16a34a);
            border:1px solid rgba(255,255,255,.12);
        }

        .hero-btn.warning{
            background: linear-gradient(135deg, #3f2a0d, #9a3412);
            border:1px solid rgba(255,255,255,.12);
        }

        .hero-btn.dark{
            background: rgba(15,23,42,.85);
            border:1px solid rgba(255,255,255,.10);
        }

        .hero-btn:hover{
            transform: translateY(-2px);
            box-shadow: 0 10px 24px rgba(0,0,0,.25);
        }

        .alerta-banner{
            margin-top:18px;
            border-radius:18px;
            padding:14px 18px;
            background: linear-gradient(90deg, rgba(127,29,29,.35), rgba(69,10,10,.22));
            border:1px solid rgba(255,82,82,.35);
            color:#ffe2e2;
            font-weight:600;
            box-shadow: 0 8px 24px rgba(255,82,82,.08);
        }

        .filter-panel{
            margin-top:18px;
            position:relative;
            z-index:2;
            border-radius:20px;
            padding:16px;
            background: rgba(8,15,30,.55);
            border:1px solid rgba(255,255,255,.08);
            backdrop-filter: blur(10px);
        }

        .filter-grid{
            display:grid;
            grid-template-columns: 1.2fr 1fr 1fr auto auto;
            gap:12px;
            align-items:end;
        }

        .filter-group label{
            display:block;
            margin-bottom:6px;
            font-size:12px;
            color:#b7c9e8;
            font-weight:700;
            letter-spacing:.5px;
        }

        .filter-input{
            width:100%;
            height:46px;
            border-radius:14px;
            border:1px solid rgba(255,255,255,.10);
            background: rgba(255,255,255,.06);
            color:#fff;
            padding:10px 14px;
            outline:none;
        }

        .filter-input::-webkit-calendar-picker-indicator{
            filter: invert(1);
            opacity:.85;
            cursor:pointer;
        }

        .filter-actions{
            display:flex;
            gap:10px;
            flex-wrap:wrap;
        }

        .filter-btn{
            height:46px;
            border:none;
            border-radius:14px;
            padding:0 18px;
            font-weight:800;
            cursor:pointer;
            transition:.25s ease;
            color:#fff;
        }

        .filter-btn.apply{
            background: linear-gradient(135deg, #2563eb, #06b6d4);
        }

        .filter-btn.clear{
            background: rgba(255,255,255,.08);
            border:1px solid rgba(255,255,255,.10);
        }

        .filter-btn:hover{
            transform: translateY(-2px);
        }

        .filter-note{
            margin-top:10px;
            color:#9fb2d4;
            font-size:12px;
        }

        .metric-card{
            position:relative;
            overflow:hidden;
            min-height:230px;
            border-radius:24px;
            padding:22px 20px;
            background:
                linear-gradient(145deg, rgba(16,24,40,.92), rgba(20,30,58,.86));
            border:1px solid rgba(255,255,255,.08);
            box-shadow:
                0 14px 30px rgba(0,0,0,.25),
                inset 0 1px 0 rgba(255,255,255,.05);
            transition:.25s ease;
            margin-bottom:18px;
            display:flex;
            flex-direction:column;
        }

        .metric-card:hover{
            transform: translateY(-4px);
            border-color: rgba(96,165,250,.30);
            box-shadow:
                0 20px 38px rgba(0,0,0,.32),
                0 0 0 1px rgba(59,130,246,.08) inset;
        }

        .metric-card::before{
            content:"";
            position:absolute;
            top:-50px;
            right:-50px;
            width:140px;
            height:140px;
            border-radius:50%;
            background: radial-gradient(circle, rgba(59,130,246,.16), transparent 68%);
        }

        .metric-top{
            display:flex;
            justify-content:space-between;
            gap:14px;
            align-items:flex-start;
            margin-bottom:14px;
            position:relative;
            z-index:2;
        }

        .metric-label{
            font-size:11px;
            font-weight:700;
            text-transform:uppercase;
            letter-spacing:1px;
            color:#8ba4d0;
            margin-bottom:6px;
        }

        .metric-title{
            font-size:22px;
            font-weight:800;
            color:#fff;
            margin:0;
            text-transform:capitalize;
        }

        .metric-icon{
            width:72px;
            height:72px;
            border-radius:20px;
            display:flex;
            align-items:center;
            justify-content:center;
            font-size:38px;
            background: rgba(255,255,255,.05);
            border:1px solid rgba(255,255,255,.07);
            color:#dbeafe;
            flex-shrink:0;
        }

        .metric-number{
            font-size:32px;
            font-weight:800;
            color:#fff;
            line-height:1;
            margin:10px 0 8px;
        }

        .metric-money{
            font-size:24px;
            font-weight:800;
            color:#dbeafe;
            line-height:1.2;
            word-break: break-word;
            margin:0 0 10px;
        }

        .metric-bottom{
            display:flex;
            justify-content:space-between;
            align-items:center;
            gap:10px;
            flex-wrap:wrap;
            margin-top:auto;
        }

        .metric-status{
            display:inline-flex;
            align-items:center;
            gap:8px;
            padding:7px 12px;
            border-radius:999px;
            font-size:12px;
            font-weight:700;
            background: rgba(15,23,42,.8);
            color:#dbeafe;
            border:1px solid rgba(255,255,255,.08);
        }

        .metric-mini{
            color:#8ea6cd;
            font-size:13px;
        }

        .metric-actions{
            margin-top:14px;
            position:relative;
            z-index:2;
        }

        .metric-detail-btn{
            width:100%;
            border:none;
            border-radius:14px;
            padding:12px 14px;
            font-size:13px;
            font-weight:800;
            color:#fff;
            background: linear-gradient(135deg, rgba(59,130,246,.95), rgba(6,182,212,.92));
            box-shadow: 0 10px 22px rgba(37,99,235,.20);
            transition:.25s ease;
        }

        .metric-detail-btn:hover{
            transform: translateY(-2px);
            box-shadow: 0 16px 30px rgba(37,99,235,.28);
        }

        .section-card{
            border-radius:26px;
            padding:22px;
            background:
                linear-gradient(145deg, rgba(10,18,34,.92), rgba(17,24,39,.86));
            border:1px solid rgba(255,255,255,.08);
            box-shadow:
                0 18px 35px rgba(0,0,0,.28),
                inset 0 1px 0 rgba(255,255,255,.04);
            margin-bottom:20px;
            position:relative;
            overflow:hidden;
        }

        .section-card::before{
            content:"";
            position:absolute;
            width:280px;
            height:280px;
            right:-80px;
            top:-100px;
            background: radial-gradient(circle, rgba(59,130,246,.08), transparent 70%);
            pointer-events:none;
        }

        .section-header{
            display:flex;
            justify-content:space-between;
            align-items:center;
            gap:12px;
            flex-wrap:wrap;
            margin-bottom:18px;
            position:relative;
            z-index:2;
        }

        .section-title{
            margin:0;
            font-size:22px;
            font-weight:800;
            color:#fff;
        }

        .section-desc{
            margin:4px 0 0;
            color:#8ea6cd;
            font-size:13px;
        }

        .section-tag{
            padding:8px 12px;
            border-radius:999px;
            font-size:12px;
            font-weight:700;
            color:#dbeafe;
            background: rgba(15,23,42,.75);
            border:1px solid rgba(255,255,255,.08);
        }

        .progress-row{
            margin-bottom:16px;
            position:relative;
            z-index:2;
        }

        .progress-row:last-child{
            margin-bottom:0;
        }

        .progress-top{
            display:flex;
            justify-content:space-between;
            align-items:center;
            gap:10px;
            margin-bottom:8px;
        }

        .progress-label{
            font-size:14px;
            font-weight:600;
            color:#dbeafe;
        }

        .progress-value{
            font-size:13px;
            font-weight:700;
            color:#ffffff;
        }

        .progress-line{
            width:100%;
            height:13px;
            border-radius:999px;
            overflow:hidden;
            background: rgba(255,255,255,.06);
            border:1px solid rgba(255,255,255,.05);
            box-shadow: inset 0 2px 6px rgba(0,0,0,.25);
        }

        .progress-fill{
            height:100%;
            border-radius:999px;
            background: linear-gradient(90deg, #3b82f6, #06b6d4, #22c55e);
            box-shadow: 0 0 18px rgba(6,182,212,.25);
            transition: width 1.2s ease;
        }

        .chart-wrap{
            position:relative;
            width:100%;
            min-height:380px;
            height:380px;
        }

        /* Highcharts fondo transparente para heredar dark glass del card */
        #chartInstituciones .highcharts-background {
            fill: transparent;
        }
        #chartInstituciones text,
        #chartInstituciones .highcharts-data-label text {
            fill: #e2e8f0 !important;
        }
        #chartInstituciones .highcharts-tooltip-box {
            fill: rgba(16,24,40,0.96) !important;
            stroke: rgba(99,179,237,0.3) !important;
        }
        #chartInstituciones .highcharts-tooltip text,
        #chartInstituciones .highcharts-tooltip tspan {
            fill: #e2e8f0 !important;
            color: #e2e8f0 !important;
        }

        #chartProvincia .highcharts-background,
        #chartMunicipios .highcharts-background {
            fill: transparent;
        }
        #chartProvincia text,
        #chartProvincia .highcharts-data-label text,
        #chartMunicipios text,
        #chartMunicipios .highcharts-data-label text {
            fill: #e2e8f0 !important;
        }
        #chartProvincia .highcharts-tooltip-box,
        #chartMunicipios .highcharts-tooltip-box {
            fill: rgba(16,24,40,0.96) !important;
            stroke: rgba(99,179,237,0.3) !important;
        }
        #chartProvincia .highcharts-tooltip text,
        #chartProvincia .highcharts-tooltip tspan,
        #chartMunicipios .highcharts-tooltip text,
        #chartMunicipios .highcharts-tooltip tspan {
            fill: #e2e8f0 !important;
        }
        #chartProvincia .highcharts-grid-line,
        #chartMunicipios .highcharts-grid-line {
            stroke: rgba(255,255,255,.05) !important;
        }
        #chartProvincia .highcharts-axis-line,
        #chartMunicipios .highcharts-axis-line {
            stroke: rgba(255,255,255,.08) !important;
        }

        .summary-grid{
            display:grid;
            grid-template-columns: repeat(3, 1fr);
            gap:18px;
            margin-bottom:20px;
        }

        .summary-card{
            border-radius:22px;
            padding:22px;
            background:
                linear-gradient(145deg, rgba(20,30,58,.88), rgba(11,19,35,.9));
            border:1px solid rgba(255,255,255,.08);
            box-shadow: 0 16px 28px rgba(0,0,0,.24);
            min-height:140px;
        }

        .summary-card .label{
            color:#8ea6cd;
            font-size:12px;
            text-transform:uppercase;
            letter-spacing:1px;
            font-weight:700;
            margin-bottom:8px;
        }

        .summary-card .value{
            color:#fff;
            font-size:34px;
            font-weight:800;
            line-height:1.05;
            margin-bottom:8px;
        }

        .summary-card .sub{
            color:#bfd0ec;
            font-size:13px;
        }

        .modal-overlay-titulos{
            position:fixed;
            inset:0;
            z-index:99999;
            display:none;
            align-items:center;
            justify-content:center;
            padding:20px;
            background: rgba(4,10,20,.72);
            backdrop-filter: blur(8px);
        }

        .modal-overlay-titulos.active{
            display:flex;
        }

        .modal-titulos-box{
            width:min(920px, 100%);
            max-height:85vh;
            overflow:hidden;
            border-radius:26px;
            border:1px solid rgba(255,255,255,.10);
            background: linear-gradient(145deg, rgba(10,18,34,.98), rgba(17,24,39,.96));
            box-shadow: 0 25px 60px rgba(0,0,0,.45);
        }

        .modal-titulos-header{
            display:flex;
            justify-content:space-between;
            align-items:center;
            gap:12px;
            padding:20px 22px;
            border-bottom:1px solid rgba(255,255,255,.08);
            background: linear-gradient(135deg, rgba(30,41,59,.85), rgba(18,37,68,.88));
        }

        .modal-titulos-title{
            margin:0;
            font-size:24px;
            font-weight:800;
            color:#fff;
        }

        .modal-titulos-sub{
            margin-top:4px;
            color:#9fb2d4;
            font-size:13px;
        }

        .modal-close-btn{
            border:none;
            width:42px;
            height:42px;
            border-radius:14px;
            background: rgba(255,255,255,.08);
            color:#fff;
            font-size:22px;
            font-weight:800;
        }

        .modal-titulos-body{
            padding:20px 22px 22px;
            overflow:auto;
            max-height:calc(85vh - 86px);
        }

        .titulo-list{
            display:grid;
            grid-template-columns:1fr;
            gap:12px;
        }

        .titulo-item{
            border-radius:18px;
            padding:16px;
            border:1px solid rgba(255,255,255,.08);
            background: rgba(255,255,255,.04);
        }

        .titulo-item-top{
            display:flex;
            justify-content:space-between;
            gap:12px;
            align-items:flex-start;
            flex-wrap:wrap;
        }

        .titulo-item-title{
            color:#fff;
            font-size:15px;
            font-weight:700;
            margin:0;
        }

        .titulo-badges{
            display:flex;
            gap:8px;
            flex-wrap:wrap;
        }

        .titulo-badge{
            padding:6px 10px;
            border-radius:999px;
            font-size:12px;
            font-weight:700;
            color:#dbeafe;
            background: rgba(15,23,42,.8);
            border:1px solid rgba(255,255,255,.08);
        }

        .titulo-empty{
            text-align:center;
            color:#9fb2d4;
            padding:28px 14px;
            border:1px dashed rgba(255,255,255,.12);
            border-radius:18px;
            background: rgba(255,255,255,.03);
        }

        @media (max-width: 1199px){
            .filter-grid{
                grid-template-columns: 1fr 1fr;
            }
            .filter-actions{
                grid-column: span 2;
            }
        }

        @media (max-width: 991px){
            .hero-title{
                font-size:30px;
            }

            .summary-grid{
                grid-template-columns: 1fr;
            }

            .metric-card{
                min-height: auto;
            }
        }

        @media (max-width: 767px){
            .hero-panel{
                padding:20px 16px 18px;
                border-radius:22px;
            }

            .hero-title{
                font-size:26px;
            }

            .section-card,
            .metric-card,
            .summary-card{
                border-radius:20px;
            }


            .filter-grid{
                grid-template-columns: 1fr;
            }

            .filter-actions{
                grid-column: span 1;
            }
        }

        .pcoded-navbar{
            position: fixed !important;
            z-index: 1035 !important;
            pointer-events: auto !important;
        }

        .pcoded-header{
            position: relative !important;
            z-index: 1020 !important;
        }

        .pcoded-main-container{
            position: relative !important;
            z-index: 1 !important;
        }

        .pcoded-content{
            position: relative !important;
            z-index: 1 !important;
        }

        .loader-bg{
            pointer-events: auto;
        }

        .loader-bg.hidden{
            opacity: 0 !important;
            visibility: hidden !important;
            pointer-events: none !important;
        }

        .hero-panel::before,
        .hero-panel::after,
        .section-card::before,
        .metric-card::before{
            pointer-events: none !important;
        }

        #modoTVBtn{
            z-index: 999 !important;
        }
        .titulo-item-desc{
    margin-top:12px;
    color:#c9d8f2;
    font-size:13px;
    line-height:1.6;
    background: rgba(255,255,255,.03);
    border:1px solid rgba(255,255,255,.06);
    border-radius:14px;
    padding:12px 14px;
    white-space: pre-line;
}

.titulo-desc-empty{
    color:#8ea6cd;
    font-style: italic;
}
    </style>

    <div class="pcoded-main-container">
        <div class="pcoded-content">
            <div class="dashboard-wrap">

                <div class="hero-panel">
                    <div class="top-badges">
                        <span class="badge-chip">🛡️ Secretaría del Interior</span>
                        <span class="badge-chip">📍 Santander · Seguridad Territorial</span>
                        <span class="badge-chip">🕒 Actualizado: <?= htmlspecialchars($fechaActualizacion, ENT_QUOTES, 'UTF-8') ?></span>
                        <?php if ($dateColumn !== null): ?>
                            <span class="badge-chip">📅 Filtro activo por: <?= htmlspecialchars($dateColumn, ENT_QUOTES, 'UTF-8') ?></span>
                        <?php endif; ?>
                    </div>

                    <div class="hero-content">
                        <div>
                            <h1 class="hero-title">Resultados en Materia de Inversión</h1>
                            <div class="hero-subtitle">
                                Visualiza en una sola vista la inversión, el comportamiento territorial, los registros por línea estratégica y la distribución municipal para toma de decisiones rápida.
                            </div>
                        </div>

                        <div class="hero-actions">
                            <button type="button" class="hero-btn primary">📊 Territorio</button>
                            <button type="button" class="hero-btn success">💰 Inversión</button>
                            <button type="button" class="hero-btn warning">🚨 Alertas</button>
                            <button type="button" class="hero-btn dark" onclick="activarModoTV()">🖥️ Modo TV</button>
                        </div>
                    </div>

                    <div class="filter-panel">
                        <form method="GET" class="filter-grid">
                            <div class="filter-group">
                                <label>Columna usada para fecha</label>
                                <input type="text" class="filter-input" value="<?= $dateColumn !== null ? htmlspecialchars($dateColumn, ENT_QUOTES, 'UTF-8') : 'No se detectó columna de fecha' ?>" readonly>
                            </div>

                            <div class="filter-group">
                                <label for="fecha_desde">Fecha desde</label>
                                <input type="date" id="fecha_desde" name="fecha_desde" class="filter-input" value="<?= htmlspecialchars($fechaDesde, ENT_QUOTES, 'UTF-8') ?>" <?= $dateColumn === null ? 'disabled' : '' ?>>
                            </div>

                            <div class="filter-group">
                                <label for="fecha_hasta">Fecha hasta</label>
                                <input type="date" id="fecha_hasta" name="fecha_hasta" class="filter-input" value="<?= htmlspecialchars($fechaHasta, ENT_QUOTES, 'UTF-8') ?>" <?= $dateColumn === null ? 'disabled' : '' ?>>
                            </div>

                            <div class="filter-actions">
                                <button type="submit" class="filter-btn apply" <?= $dateColumn === null ? 'disabled style="opacity:.5;cursor:not-allowed;"' : '' ?>>Aplicar filtro</button>
                                <a href="<?= htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') ?>" class="filter-btn clear" style="display:inline-flex;align-items:center;text-decoration:none;">Limpiar</a>
                            </div>

                            <div class="filter-note">
                                <?php if ($dateColumn === null): ?>
                                    No se encontró una columna de fecha compatible en la tabla, por eso el filtro no está disponible.
                                <?php else: ?>
                                    El dashboard, ranking, gráfica y Tipos de inversion
                                <?php endif; ?>
                            </div>
                        </form>
                    </div>

                    <?php if ($valorGlobal > 100000000000): ?>
                        <div class="alerta-banner alerta-alta">
                            🚨 ALERTA CRÍTICA: Nivel de inversión extremadamente alto.
                        </div>
                    <?php endif; ?>
                </div>

                <div class="summary-grid">
                    <div class="summary-card">
                        <div class="label">Proyectos Totales</div>
                        <div class="value counter" data-value="<?= (int)$totalGlobal ?>">0</div>
                        <div class="sub">Total consolidado de registros en seguridad</div>
                    </div>

                    <div class="summary-card">
                        <div class="label">Valor Global</div>
                        <div class="value" style="font-size:28px;">
                            <span class="counter-money" data-value="<?= (float)$valorGlobal ?>">$0</span>
                        </div>
                        <div class="sub">Monto total ejecutado en todas las líneas</div>
                    </div>

                </div>

                <?php
                $iconos = [
                    'movilidad'       => '🚓',
                    'tecnologia'      => '💻',
                    'proyectos'       => '📁',
                    'intendencia'     => '🛡️',
                    'infraestructura' => '🏗️',
                    'convenios'       => '🤝',
                    'pagos'           => '💳'
                ];

                $cardTemplate = function ($tipo, $val) use ($iconos, $detallesPorTipo) {
                    $detallesDeTipo = $detallesPorTipo[$tipo] ?? [];
                    $cantidadTitulos = count($detallesDeTipo);
                    ?>
                    <div class="metric-card">
                        <div class="metric-top">
                            <div>
                                <div class="metric-label">Tipo de sección</div>
                                <h3 class="metric-title"><?= htmlspecialchars(ucfirst($tipo), ENT_QUOTES, 'UTF-8') ?></h3>
                            </div>
                            <div class="metric-icon"><?= $iconos[$tipo] ?? '📌' ?></div>
                        </div>

                        <div class="metric-number counter" data-value="<?= (int)$val['total'] ?>">0</div>
                        <div class="metric-mini">Registros cargados</div>

                        <div class="metric-money">
                            <span class="counter-money" data-value="<?= (float)$val['valor'] ?>">$0</span>
                        </div>

                        <div class="metric-bottom">
                            <span class="metric-status">● Operativo</span>
                            <span class="metric-mini"><?= $cantidadTitulos ?> Descripcion</span>
                        </div>

                        <div class="metric-actions">
                            <button 
                                type="button"
                                class="metric-detail-btn"
                                onclick="abrirModalTitulos('<?= htmlspecialchars($tipo, ENT_QUOTES, 'UTF-8') ?>')">
                                📄 Ver Detalle Inversión
                            </button>
                        </div>
                    </div>
                <?php }; ?>

                <div class="row">
                    <?php foreach (['movilidad','tecnologia','proyectos','intendencia','infraestructura','convenios'] as $tipo): ?>
                        <div class="col-xl-4 col-md-6">
                            <?php $cardTemplate($tipo, $datos[$tipo]); ?>
                        </div>
                    <?php endforeach; ?>
                </div>

                <div class="row mt-4">
                    <div class="col-xl-4 col-md-6">
                        <?php $cardTemplate('pagos', $datos['pagos']); ?>
                    </div>
                    <?php if (!empty($dataInst)): 
                        $totalInst = count($dataInst);
                        $topInst = $dataInst[0]['institucion'] ?? '—';
                        $topInstValor = (float)($dataInst[0]['total_valor'] ?? 0);
                    ?>
                    <div class="col-xl-8 col-md-6">
                        <div class="row g-2">
                            <div class="col-4">
                                <div class="metric-card text-center" style="padding:18px 12px;">
                                    <div class="metric-label" style="font-size:11px;">Instituciones</div>
                                    <div style="font-size:28px;font-weight:1100;color:#fff;margin-top:4px;"><?= $totalInst ?></div>
                                    <div class="metric-mini" style="margin-top:4px;">Beneficiadas</div>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="metric-card text-center" style="padding:18px 12px;">
                                    <div class="metric-label" style="font-size:11px;">Inversión Total</div>
                                    <div style="font-size:20px;font-weight:1100;color:#34d399;margin-top:4px;">$<?= number_format(array_sum(array_column($dataInst, 'total_valor')) > 1000000000 ? array_sum(array_column($dataInst, 'total_valor')) / 1000000000 : array_sum(array_column($dataInst, 'total_valor')) / 1000000, 1, ',', '.') ?><?= array_sum(array_column($dataInst, 'total_valor')) > 1000000000 ? 'B' : 'M' ?></div>
                                    <div class="metric-mini" style="margin-top:4px;">Consolidado</div>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="metric-card text-center" style="padding:18px 12px;">
                                    <div class="metric-label" style="font-size:11px;">Top Institución</div>
                                    <div style="font-size:13px;font-weight:1100;color:#60a5fa;margin-top:4px;word-break:break-word;"><?= htmlspecialchars($topInst, ENT_QUOTES, 'UTF-8') ?></div>
                                    <div class="metric-mini" style="margin-top:4px;">$<?= number_format($topInstValor > 1000000000 ? $topInstValor / 1000000000 : $topInstValor / 1000000, 1, ',', '.') ?><?= $topInstValor > 1000000000 ? 'B' : 'M' ?></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>

                <?php if (!empty($dataInst)): ?>
                <div class="row mt-3">
                    <div class="col-md-12">
                        <div class="metric-card" style="padding:28px 24px;">
                            <div class="metric-top mb-3">
                                <div>
                                    <div class="metric-label">Gráfica</div>
                                    <h3 class="metric-title">Inversión por Institución Beneficiada</h3>
                                </div>
                                <div class="metric-icon">🏛️</div>
                            </div>
                            <div id="chartInstituciones" style="min-height:460px;height:460px;width:100%;"></div>
                        </div>
                    </div>
                </div>
                <?php endif; ?>

                <?php if (!empty($dataProv)): ?>
                <div class="row mt-4">
                    <div class="col-12 col-xl-6">
                        <div class="metric-card" style="padding:24px;min-height:420px;">
                            <div class="metric-top mb-3">
                                <div>
                                    <div class="metric-label">Gráfica</div>
                                    <h3 class="metric-title">Inversión por Provincia</h3>
                                </div>
                                <div class="metric-icon">🗺️</div>
                            </div>
                            <div id="chartProvincia" style="height:380px;width:100%;"></div>
                        </div>
                    </div>
                    <div class="col-12 col-xl-6">
                        <div class="metric-card" style="padding:24px;min-height:420px;">
                            <div class="metric-top mb-3">
                                <div>
                                    <div class="metric-label">Gráfica</div>
                                    <h3 class="metric-title">Top Municipios por Inversión</h3>
                                </div>
                                <div class="metric-icon">🏙️</div>
                            </div>
                            <div id="chartMunicipios" style="height:380px;width:100%;"></div>
                        </div>
                    </div>
                </div>
                <?php endif; ?>

            </div>
        </div>
    </div>

    <div class="modal-overlay-titulos" id="modalTitulosOverlay">
        <div class="modal-titulos-box">
            <div class="modal-titulos-header">
                <div>
                    <h3 class="modal-titulos-title" id="modalTitulosTitulo">Títulos relacionados</h3>
                    <div class="modal-titulos-sub" id="modalTitulosSub">Consulta detallada por tipo de sección</div>
                </div>
                <button type="button" class="modal-close-btn" onclick="cerrarModalTitulos()">×</button>
            </div>
            <div class="modal-titulos-body" id="modalTitulosBody">
                <div class="titulo-empty">Cargando información...</div>
            </div>
        </div>
    </div>

    <button id="modoTVBtn" onclick="activarModoTV()">🖥️ MODO TV</button>

    <?php
    if (file_exists('./admin/include/gerenic_footer.php')) {
        include './admin/include/gerenic_footer.php';
    }

    if (file_exists('./admin/include/gerenic_script.php')) {
        include './admin/include/gerenic_script.php';
    }
    ?>
    <script src="assets/js/vendor-all.min.js"></script>
    <script src="assets/js/pcoded.min.js"></script>


    <script>

        const detallesPorTipo = <?= json_encode($detallesPorTipo, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;

        function ocultarLoader() {
            const loader = document.getElementById('pageLoader');
            if (loader) {
                loader.classList.add('hidden');
                setTimeout(() => {
                    loader.style.display = 'none';
                    if (loader.parentNode) {
                        loader.parentNode.removeChild(loader);
                    }
                }, 500);
            }
        }

        function abrirModalTitulos(tipo) {
    const overlay = document.getElementById('modalTitulosOverlay');
    const body = document.getElementById('modalTitulosBody');
    const title = document.getElementById('modalTitulosTitulo');
    const sub = document.getElementById('modalTitulosSub');

    const tipoNormalizado = String(tipo || '').trim().toLowerCase();
    const items = detallesPorTipo[tipoNormalizado] || [];

    title.textContent = 'Detalle de ' + (tipo.charAt(0).toUpperCase() + tipo.slice(1));
    sub.textContent = 'Se encontraron ' + items.length + ' registro(s) asociados con el filtro actual.';

    if (!items.length) {
        body.innerHTML = '<div class="titulo-empty">No hay información relacionada para esta sección con el filtro actual.</div>';
    } else {
        let html = '<div class="titulo-list">';

        items.forEach(item => {
            html += `
                <div class="titulo-item">
                    <div class="titulo-item-top">
                        <h4 class="titulo-item-title">${escapeHtml(item.titulo || '')}</h4>
                        <div class="titulo-badges">
                            <span class="titulo-badge">Registros: ${Number(item.total_items || 0).toLocaleString('es-CO')}</span>
                            <span class="titulo-badge">Valor: $${Number(item.total_valor || 0).toLocaleString('es-CO')}</span>
                        </div>
                    </div>
                    <div class="titulo-item-desc">
                        ${item.descripcion && item.descripcion.trim() !== '' 
                            ? escapeHtml(item.descripcion) 
                            : '<span class="titulo-desc-empty">Sin descripción registrada.</span>'}
                    </div>
                </div>
            `;
        });

        html += '</div>';
        body.innerHTML = html;
    }

    overlay.classList.add('active');
    document.body.style.overflow = 'hidden';
}

        function cerrarModalTitulos() {
            const overlay = document.getElementById('modalTitulosOverlay');
            overlay.classList.remove('active');
            document.body.style.overflow = '';
        }

        function escapeHtml(text) {
            const map = {
                '&': '&amp;',
                '<': '&lt;',
                '>': '&gt;',
                '"': '&quot;',
                "'": '&#039;'
            };
            return String(text).replace(/[&<>"']/g, function(m) { return map[m]; });
        }


        document.addEventListener('DOMContentLoaded', function () {
            document.querySelectorAll('.progress-fill').forEach(el => {
                const width = el.style.width;
                el.style.width = '0%';
                setTimeout(() => {
                    el.style.width = width;
                }, 250);
            });

            document.querySelectorAll('.counter').forEach(el => {
                const value = Number(el.dataset.value || 0);
                let count = 0;
                const step = value > 0 ? value / 80 : 1;

                const interval = setInterval(() => {
                    count += step;
                    if (count >= value) {
                        count = value;
                        clearInterval(interval);
                    }
                    el.innerText = Math.floor(count).toLocaleString('es-CO');
                }, 20);
            });

            document.querySelectorAll('.counter-money').forEach(el => {
                const value = Number(el.dataset.value || 0);
                let count = 0;
                const step = value > 0 ? value / 80 : 1;

                const interval = setInterval(() => {
                    count += step;
                    if (count >= value) {
                        count = value;
                        clearInterval(interval);
                    }
                    el.innerText = '$' + Math.floor(count).toLocaleString('es-CO');
                }, 20);
            });

            const alerta = document.querySelector('.alerta-alta');
            if (alerta) {
                try {
                    const audio = new Audio('https://actions.google.com/sounds/v1/alarms/alarm_clock.ogg');
                    audio.volume = 0.25;
                    setTimeout(() => {
                        audio.play().catch(() => {});
                    }, 1200);
                } catch (e) {}
            }

            const modalOverlay = document.getElementById('modalTitulosOverlay');
            if (modalOverlay) {
                modalOverlay.addEventListener('click', function (e) {
                    if (e.target === modalOverlay) {
                        cerrarModalTitulos();
                    }
                });
            }

            document.addEventListener('keydown', function (e) {
                if (e.key === 'Escape') {
                    cerrarModalTitulos();
                }
            });

            setTimeout(ocultarLoader, 700);
        });

        window.addEventListener('load', function () {
            ocultarLoader();
        });

        setTimeout(function () {
            ocultarLoader();
        }, 2500);

        setInterval(() => {
            document.body.style.boxShadow = 'inset 0 0 90px rgba(59,130,246,0.06)';
            setTimeout(() => {
                document.body.style.boxShadow = 'none';
            }, 350);
        }, 4500);

        function activarModoTV() {
            if (!document.fullscreenElement) {
                document.documentElement.requestFullscreen().catch(() => {});
            } else {
                document.exitFullscreen().catch(() => {});
            }
        }

        window.__instData = <?= json_encode(
            array_map(function ($r) {
                return [
                    'name'  => (string)($r['institucion'] ?? ''),
                    'y'     => (int)($r['total_registros'] ?? 0),
                    'valor' => (float)($r['total_valor'] ?? 0),
                ];
            }, $dataInst),
            JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
        ) ?>;
        window.__instTotalValor = <?= (float)$valorGlobal ?>;
        window.__provData = <?= json_encode($dataProv, JSON_UNESCAPED_UNICODE) ?>;
        window.__munData = <?= json_encode($dataM, JSON_UNESCAPED_UNICODE) ?>;
    </script>
    <script src="admin/js/inversiones.js?v=<?= filemtime('admin/js/inversiones.js') ?>"></script>
</body>
</html>