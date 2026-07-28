<?php
include './admin/include/head.php';
require './admin/include/generic_classes.php';

$db  = new DbConection();
$pdo = $db->openConect();
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

function tableName($db, $name)
{
    try {
        return $db->getTable($name);
    } catch (Throwable $e) {
        return $name;
    }
}

$tblIndicadores = tableName($db, 'tbl_infra_indicadores');
$tblInversion   = tableName($db, 'tbl_infra_inversion');
$tblProyectos   = tableName($db, 'tbl_infra_proyectos_estrategicos');
$tblRegistros   = tableName($db, 'tbl_infra_registros');

function moneyCOP($value)
{
    if ($value === null || $value === '') {
        return '$0';
    }
    return '$' . number_format((float)$value, 0, ',', '.');
}

function numFormat($value, $decimals = 0)
{
    if ($value === null || $value === '') {
        return '0';
    }
    return number_format((float)$value, $decimals, ',', '.');
}

function safe($value)
{
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

function percentValue($value)
{
    return number_format(((float)$value) * 100, 1, ',', '.') . '%';
}

/* ============================================================
   CREACION DE TABLAS
============================================================ */

$pdo->exec("
CREATE TABLE IF NOT EXISTS {$tblIndicadores} (
    id INT AUTO_INCREMENT PRIMARY KEY,
    codigo VARCHAR(80) NOT NULL UNIQUE,
    titulo VARCHAR(180) NOT NULL,
    valor_numerico DECIMAL(20,4) NULL,
    valor_texto VARCHAR(120) NULL,
    unidad VARCHAR(80) NULL,
    descripcion TEXT NULL,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
");

$pdo->exec("
CREATE TABLE IF NOT EXISTS {$tblInversion} (
    id INT AUTO_INCREMENT PRIMARY KEY,
    bloque VARCHAR(160) NOT NULL,
    categoria VARCHAR(220) NOT NULL,
    medida_label VARCHAR(80) NULL,
    medida_valor DECIMAL(20,4) NULL,
    municipios INT NULL,
    recurso_total DECIMAL(20,2) NULL,
    recurso_2024 DECIMAL(20,2) NULL,
    recurso_2025 DECIMAL(20,2) NULL,
    observacion VARCHAR(255) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
");

$pdo->exec("
CREATE TABLE IF NOT EXISTS {$tblProyectos} (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(180) NOT NULL,
    estado VARCHAR(80) NOT NULL DEFAULT 'En estructuracion',
    porcentaje DECIMAL(5,2) NOT NULL DEFAULT 0,
    municipio VARCHAR(160) NULL,
    responsable VARCHAR(160) NULL,
    valor DECIMAL(20,2) NULL,
    fecha_inicio DATE NULL,
    fecha_fin DATE NULL,
    descripcion TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
");

$pdo->exec("
CREATE TABLE IF NOT EXISTS {$tblRegistros} (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tipo VARCHAR(120) NOT NULL,
    categoria VARCHAR(180) NOT NULL,
    nombre VARCHAR(220) NOT NULL,
    municipio VARCHAR(160) NULL,
    valor DECIMAL(20,2) NULL,
    avance DECIMAL(5,2) NULL,
    fecha_inicio DATE NULL,
    fecha_corte DATE NULL,
    descripcion TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
");

/* ============================================================
   SEED INICIAL SEGUN EXCEL
============================================================ */

$countIndicadores = (int)$pdo->query("SELECT COUNT(*) FROM {$tblIndicadores}")->fetchColumn();

if ($countIndicadores === 0) {
    $indicadores = [
        ['proyectos_radicados_2024', 'Proyectos radicados 2024', 204, null, 'registros', 'Número de proyectos radicados durante la vigencia 2024.'],
        ['proyectos_radicados_2025', 'Proyectos radicados 2025', 268, null, 'registros', 'Número de proyectos radicados durante la vigencia 2025.'],
        ['proyectos_radicados_2026', 'Proyectos radicados 2026', 91, null, 'registros', 'Número de proyectos radicados durante la vigencia 2026.'],
        ['proyectos_radicados_total', 'Total proyectos radicados', 563, null, 'registros', 'Total consolidado de proyectos radicados.'],

        ['proyectos_viabilizados_2024', 'Proyectos viabilizados 2024', 77, null, 'registros', 'Número de proyectos viabilizados durante la vigencia 2024.'],
        ['proyectos_viabilizados_2025', 'Proyectos viabilizados 2025', 187, null, 'registros', 'Número de proyectos viabilizados durante la vigencia 2025.'],
        ['proyectos_viabilizados_2026', 'Proyectos viabilizados 2026', 56, null, 'registros', 'Número de proyectos viabilizados durante la vigencia 2026.'],
        ['proyectos_viabilizados_total', 'Total proyectos viabilizados', 320, null, 'registros', 'Total consolidado de proyectos viabilizados.'],

        ['contratos_ejecucion_marzo_2026', 'Contratos en ejecución a marzo 2026', 417, null, 'contratos', 'Contratos en ejecución reportados a marzo de 2026.'],
        ['seguimiento_plan_desarrollo', 'Seguimiento Plan de Desarrollo', 0.4461, null, 'porcentaje', 'Avance del seguimiento del Plan de Desarrollo a marzo de 2026.'],

        ['fallecidos_2025', 'Personas fallecidas 2025', 95, null, 'personas', 'Cifra de seguridad vial reportada para 2025.'],
        ['fallecidos_2026', 'Personas fallecidas 2026', 118, null, 'personas', 'Cifra de seguridad vial reportada para 2026.'],
        ['lesionados_2026', 'Personas lesionadas 2026', 287, null, 'personas', 'Número de personas lesionadas reportadas para 2026.'],
        ['lesionados_hombres_2026', 'Porcentaje hombres lesionados 2026', 0.61, null, 'porcentaje', 'Distribución porcentual de hombres lesionados.'],
        ['lesionados_mujeres_2026', 'Porcentaje mujeres lesionadas 2026', 0.39, null, 'porcentaje', 'Distribución porcentual de mujeres lesionadas.'],
    ];

    $stmt = $pdo->prepare("
        INSERT INTO {$tblIndicadores}
        (codigo, titulo, valor_numerico, valor_texto, unidad, descripcion)
        VALUES (?, ?, ?, ?, ?, ?)
    ");

    foreach ($indicadores as $row) {
        $stmt->execute($row);
    }
}

$countInversion = (int)$pdo->query("SELECT COUNT(*) FROM {$tblInversion}")->fetchColumn();

if ($countInversion === 0) {
    $inversiones = [
        ['Inversión en vías del departamento', 'Vía primaria', 'KMS', 3.7, 5, 33722812963.72, 15361406481.86, 18361406481.86, null],
        ['Inversión en vías del departamento', 'Vía secundaria', 'KMS', 621.4, 44, 190280132163.08, 71477733553.90, 118802398609.18, null],
        ['Inversión en vías del departamento', 'Vía terciaria', 'KMS', 3011.37, 50, 53855795790.04, 12933438169.43, 40922357620.61, null],
        ['Inversión en vías del departamento', 'Vía urbana', 'KMS', 2.88, 5, 2216608211.26, 0, 2216608211.26, null],

        ['Inversión en puentes del departamento', 'Puentes en vía secundaria intervenidos', 'Cantidad', 5, 5, 3611257061, 3497555951, 113701110, null],
        ['Inversión en puentes del departamento', 'Puentes en vía terciaria intervenidos', 'Cantidad', 2, 2, 4708701621.68, 2867796069.34, 1840905552.34, null],

        ['Inversión área mineroenergética', 'Electrificación', 'Beneficiarios', 3304, 13, 29160201926, 18460383274.92, 10699818651.08, null],
        ['Inversión área mineroenergética', 'Gasificación', 'Beneficiarios', 3780, 9, 4486755151, 0, 4486755151, null],

        ['Inversión en maquinaria', 'Maquinaria amarilla', 'Kits', 6, null, 33000000000, null, null, '33 mil millones'],

        ['Inversión estudios y diseños', 'Agua', 'Cantidad', 16, null, 1423125822.59, null, 1423125822.59, null],
        ['Inversión estudios y diseños', 'Equipamiento', 'Cantidad', 2, null, 7867330537.76, 157262182.62, 7710068355.14, null],
        ['Inversión estudios y diseños', 'Vías', 'Cantidad', 6, null, 2848257263.88, 1810842797, 1037414466.88, null],
        ['Inversión estudios y diseños', 'Aeropuerto', 'Cantidad', 1, null, null, null, null, 'Gestión'],

        ['Inversión cultural', 'Infraestructura deportiva', 'Cantidad', 5, 5, 17462245204.33, 6931939061.71, 10530306142.62, null],
        ['Inversión cultural', 'Parques intervenidos', 'Cantidad', 4, 4, 725576445.14, 0, 725576445.14, null],
    ];

    $stmt = $pdo->prepare("
        INSERT INTO {$tblInversion}
        (bloque, categoria, medida_label, medida_valor, municipios, recurso_total, recurso_2024, recurso_2025, observacion)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");

    foreach ($inversiones as $row) {
        $stmt->execute($row);
    }
}

$countProyectos = (int)$pdo->query("SELECT COUNT(*) FROM {$tblProyectos}")->fetchColumn();

if ($countProyectos === 0) {
    $proyectos = [
        ['Anillo vial', 'En seguimiento', 35, null, 'Secretaría de Infraestructura', null, null, null, 'Proyecto estratégico de conectividad territorial.'],
        ['CEO', 'En estructuración', 20, null, 'Secretaría de Infraestructura', null, null, null, 'Proyecto estratégico priorizado para seguimiento institucional.'],
        ['Maquinaria', 'En ejecución', 55, null, 'Secretaría de Infraestructura', 33000000000, null, null, 'Fortalecimiento del banco de maquinaria amarilla.'],
        ['Topocoro', 'En formulación', 18, null, 'Secretaría de Infraestructura', null, null, null, 'Proyecto estratégico asociado a desarrollo territorial.'],
        ['Carmen Yarima', 'En seguimiento', 30, null, 'Secretaría de Infraestructura', null, null, null, 'Proyecto estratégico con impacto regional.'],
        ['Onzama', 'En formulación', 15, null, 'Secretaría de Infraestructura', null, null, null, 'Proyecto estratégico priorizado.'],
    ];

    $stmt = $pdo->prepare("
        INSERT INTO {$tblProyectos}
        (nombre, estado, porcentaje, municipio, responsable, valor, fecha_inicio, fecha_fin, descripcion)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");

    foreach ($proyectos as $row) {
        $stmt->execute($row);
    }
}

/* ============================================================
   GUARDAR FORMULARIO
============================================================ */

$alertMessage = '';
$alertType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['guardar_registro_infra'])) {
    $tipo         = trim($_POST['tipo'] ?? '');
    $categoria    = trim($_POST['categoria'] ?? '');
    $nombre       = trim($_POST['nombre'] ?? '');
    $municipio    = trim($_POST['municipio'] ?? '');
    $valor        = str_replace(['.', ',', '$', ' '], ['', '.', '', ''], $_POST['valor'] ?? '');
    $avance       = str_replace(',', '.', $_POST['avance'] ?? '');
    $fechaInicio  = $_POST['fecha_inicio'] ?? null;
    $fechaCorte   = $_POST['fecha_corte'] ?? null;
    $descripcion  = trim($_POST['descripcion'] ?? '');

    if ($tipo !== '' && $categoria !== '' && $nombre !== '') {
        $stmt = $pdo->prepare("
            INSERT INTO {$tblRegistros}
            (tipo, categoria, nombre, municipio, valor, avance, fecha_inicio, fecha_corte, descripcion)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");

        $stmt->execute([
            $tipo,
            $categoria,
            $nombre,
            $municipio,
            $valor !== '' ? $valor : null,
            $avance !== '' ? $avance : null,
            $fechaInicio ?: null,
            $fechaCorte ?: null,
            $descripcion
        ]);

        $alertMessage = 'Registro guardado correctamente para la Secretaría de Infraestructura.';
        $alertType = 'success';
    } else {
        $alertMessage = 'Debe completar tipo, categoría y nombre del registro.';
        $alertType = 'error';
    }
}

/* ============================================================
   FILTROS
============================================================ */

$fechaDesde = $_GET['fecha_desde'] ?? '';
$fechaHasta = $_GET['fecha_hasta'] ?? '';

$whereRegistros = [];
$paramsRegistros = [];

if ($fechaDesde !== '') {
    $whereRegistros[] = "DATE(created_at) >= ?";
    $paramsRegistros[] = $fechaDesde;
}

if ($fechaHasta !== '') {
    $whereRegistros[] = "DATE(created_at) <= ?";
    $paramsRegistros[] = $fechaHasta;
}

$whereSQL = count($whereRegistros) ? 'WHERE ' . implode(' AND ', $whereRegistros) : '';

/* ============================================================
   CONSULTAS DASHBOARD
============================================================ */

$indicadoresRaw = $pdo->query("SELECT * FROM {$tblIndicadores}")->fetchAll(PDO::FETCH_ASSOC);
$indicadores = [];

foreach ($indicadoresRaw as $item) {
    $indicadores[$item['codigo']] = $item;
}

$totalInversionBase = (float)$pdo->query("SELECT COALESCE(SUM(recurso_total),0) FROM {$tblInversion}")->fetchColumn();

$stmtTotalReg = $pdo->prepare("SELECT COUNT(*) FROM {$tblRegistros} {$whereSQL}");
$stmtTotalReg->execute($paramsRegistros);
$totalRegistrosUsuario = (int)$stmtTotalReg->fetchColumn();

$stmtValorReg = $pdo->prepare("SELECT COALESCE(SUM(valor),0) FROM {$tblRegistros} {$whereSQL}");
$stmtValorReg->execute($paramsRegistros);
$totalValorRegistrosUsuario = (float)$stmtValorReg->fetchColumn();

$totalGeneral = $totalInversionBase + $totalValorRegistrosUsuario;

$bloques = $pdo->query("
    SELECT 
        bloque,
        COUNT(*) AS total_items,
        COALESCE(SUM(recurso_total),0) AS total_recurso,
        COALESCE(SUM(recurso_2024),0) AS total_2024,
        COALESCE(SUM(recurso_2025),0) AS total_2025,
        COALESCE(SUM(medida_valor),0) AS total_medida,
        COALESCE(SUM(municipios),0) AS total_municipios
    FROM {$tblInversion}
    GROUP BY bloque
    ORDER BY total_recurso DESC
")->fetchAll(PDO::FETCH_ASSOC);

$inversionDetalle = $pdo->query("
    SELECT *
    FROM {$tblInversion}
    ORDER BY bloque ASC, recurso_total DESC
")->fetchAll(PDO::FETCH_ASSOC);

$proyectos = $pdo->query("
    SELECT *
    FROM {$tblProyectos}
    ORDER BY porcentaje DESC, nombre ASC
")->fetchAll(PDO::FETCH_ASSOC);

$stmtUltimos = $pdo->prepare("
    SELECT *
    FROM {$tblRegistros}
    {$whereSQL}
    ORDER BY created_at DESC
    LIMIT 10
");
$stmtUltimos->execute($paramsRegistros);
$ultimosRegistros = $stmtUltimos->fetchAll(PDO::FETCH_ASSOC);

$chartBloques = [];
$chartValores = [];
$chartCategorias = [];
$chartCategoriaValores = [];

foreach ($bloques as $b) {
    $chartBloques[] = $b['bloque'];
    $chartValores[] = (float)$b['total_recurso'];
}

foreach ($inversionDetalle as $d) {
    $chartCategorias[] = $d['categoria'];
    $chartCategoriaValores[] = (float)$d['recurso_total'];
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
                radial-gradient(circle at 15% 20%, rgba(59, 130, 246, .18), transparent 24%),
                radial-gradient(circle at 88% 15%, rgba(34, 197, 94, .14), transparent 26%),
                radial-gradient(circle at 55% 100%, rgba(6, 182, 212, .10), transparent 32%),
                linear-gradient(135deg, #07111f 0%, #081726 32%, #0a1324 60%, #07111f 100%) !important;
            color:#fff !important;
            min-height:100vh;
            overflow-x:hidden;
        }

        .loader-bg{
            position:fixed;
            inset:0;
            z-index:999999;
            display:flex;
            align-items:center;
            justify-content:center;
            background:#07111f;
            transition:.35s ease;
        }

        .loader-bg.hidden{
            opacity:0;
            visibility:hidden;
            pointer-events:none;
        }

        .loader-track{
            width:280px;
            height:10px;
            border-radius:999px;
            overflow:hidden;
            background:rgba(255,255,255,.10);
            box-shadow:inset 0 2px 10px rgba(0,0,0,.35);
        }

        .loader-fill{
            width:42%;
            height:100%;
            border-radius:999px;
            background:linear-gradient(90deg, #3b82f6, #06b6d4, #22c55e);
            animation:loadingMove 1.1s linear infinite;
            box-shadow:0 0 20px rgba(6,182,212,.35);
        }

        @keyframes loadingMove{
            0%{transform:translateX(-120%);}
            100%{transform:translateX(320%);}
        }

        .dashboard-wrap{
            padding:10px 8px 35px;
        }

        .hero-panel{
            position:relative;
            overflow:hidden;
            border-radius:30px;
            padding:26px;
            background:
                linear-gradient(135deg, rgba(30,41,59,.88), rgba(17,24,39,.94) 42%, rgba(18,52,86,.90));
            border:1px solid rgba(255,255,255,.10);
            box-shadow:0 24px 65px rgba(0,0,0,.38), inset 0 1px 0 rgba(255,255,255,.06);
            margin-bottom:22px;
        }

        .hero-panel::before{
            content:"";
            position:absolute;
            right:-120px;
            top:-120px;
            width:430px;
            height:430px;
            background:radial-gradient(circle, rgba(6,182,212,.19), transparent 68%);
            pointer-events:none;
        }

        .hero-panel::after{
            content:"";
            position:absolute;
            left:-90px;
            bottom:-160px;
            width:370px;
            height:370px;
            background:radial-gradient(circle, rgba(34,197,94,.12), transparent 70%);
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
            font-weight:800;
            color:#dbeafe;
            background:rgba(15,23,42,.60);
            border:1px solid rgba(255,255,255,.10);
            backdrop-filter:blur(10px);
        }

        .hero-content{
            display:flex;
            justify-content:space-between;
            align-items:center;
            gap:18px;
            flex-wrap:wrap;
            position:relative;
            z-index:2;
        }

        .hero-title{
            margin:0;
            font-size:38px;
            line-height:1.05;
            font-weight:900;
            letter-spacing:-.7px;
            color:#fff;
        }

        .hero-subtitle{
            margin:10px 0 0;
            color:#a9bddb;
            font-size:14px;
            max-width:820px;
            line-height:1.6;
        }

        .hero-actions{
            display:flex;
            flex-wrap:wrap;
            gap:10px;
        }

        .hero-btn{
            border:none;
            outline:none;
            cursor:pointer;
            padding:12px 16px;
            border-radius:15px;
            font-size:13px;
            font-weight:900;
            color:#fff;
            transition:.25s ease;
            box-shadow:inset 0 1px 0 rgba(255,255,255,.09);
        }

        .hero-btn.primary{background:linear-gradient(135deg, #3b82f6, #4f46e5);}
        .hero-btn.success{background:linear-gradient(135deg, #0f766e, #16a34a);}
        .hero-btn.warning{background:linear-gradient(135deg, #7c2d12, #ea580c);}
        .hero-btn.dark{background:rgba(15,23,42,.88); border:1px solid rgba(255,255,255,.10);}

        .hero-btn:hover{
            transform:translateY(-2px);
            box-shadow:0 12px 28px rgba(0,0,0,.30);
        }

        .filter-panel{
            margin-top:18px;
            position:relative;
            z-index:2;
            border-radius:21px;
            padding:16px;
            background:rgba(8,15,30,.62);
            border:1px solid rgba(255,255,255,.08);
            backdrop-filter:blur(12px);
        }

        .filter-grid{
            display:grid;
            grid-template-columns:1fr 1fr auto auto;
            gap:12px;
            align-items:end;
        }

        .filter-group label{
            display:block;
            margin-bottom:7px;
            color:#b7c9e8;
            font-size:12px;
            font-weight:900;
            letter-spacing:.5px;
        }

        .filter-input{
            width:100%;
            height:46px;
            border-radius:14px;
            border:1px solid rgba(255,255,255,.11);
            background:rgba(255,255,255,.065);
            color:#fff !important;
            padding:10px 14px;
            outline:none;
        }

        .filter-input::-webkit-calendar-picker-indicator{
            filter:invert(1);
            opacity:.9;
        }

        .filter-btn{
            height:46px;
            border:none;
            border-radius:14px;
            padding:0 18px;
            font-weight:900;
            cursor:pointer;
            transition:.25s ease;
            color:#fff;
        }

        .filter-btn.apply{background:linear-gradient(135deg, #2563eb, #06b6d4);}
        .filter-btn.clear{background:rgba(255,255,255,.08); border:1px solid rgba(255,255,255,.10);}

        .filter-btn:hover{transform:translateY(-2px);}

        .summary-grid{
            display:grid;
            grid-template-columns:repeat(4, 1fr);
            gap:18px;
            margin-bottom:20px;
        }

        .summary-card{
            position:relative;
            overflow:hidden;
            min-height:145px;
            border-radius:24px;
            padding:22px;
            background:linear-gradient(145deg, rgba(16,24,40,.94), rgba(20,35,65,.86));
            border:1px solid rgba(255,255,255,.08);
            box-shadow:0 16px 34px rgba(0,0,0,.28), inset 0 1px 0 rgba(255,255,255,.05);
        }

        .summary-card::before{
            content:"";
            position:absolute;
            right:-55px;
            top:-55px;
            width:150px;
            height:150px;
            border-radius:50%;
            background:radial-gradient(circle, rgba(59,130,246,.18), transparent 70%);
        }

        .summary-card .label{
            position:relative;
            z-index:2;
            color:#8ea6cd;
            font-size:12px;
            text-transform:uppercase;
            letter-spacing:1px;
            font-weight:900;
            margin-bottom:8px;
        }

        .summary-card .value{
            position:relative;
            z-index:2;
            color:#fff;
            font-size:33px;
            font-weight:900;
            line-height:1.05;
            margin-bottom:8px;
            word-break:break-word;
        }

        .summary-card .sub{
            position:relative;
            z-index:2;
            color:#bfd0ec;
            font-size:13px;
            line-height:1.45;
        }

        .dashboard-grid{
            display:grid;
            grid-template-columns:repeat(3, 1fr);
            gap:18px;
            margin-bottom:20px;
        }

        .metric-card{
            position:relative;
            overflow:hidden;
            min-height:270px;
            border-radius:25px;
            padding:22px 20px;
            background:linear-gradient(145deg, rgba(16,24,40,.94), rgba(20,30,58,.88));
            border:1px solid rgba(255,255,255,.08);
            box-shadow:0 16px 34px rgba(0,0,0,.28), inset 0 1px 0 rgba(255,255,255,.05);
            transition:.25s ease;
            display:flex;
            flex-direction:column;
        }

        .metric-card:hover{
            transform:translateY(-4px);
            border-color:rgba(96,165,250,.32);
            box-shadow:0 22px 44px rgba(0,0,0,.34);
        }

        .metric-card::before{
            content:"";
            position:absolute;
            top:-55px;
            right:-55px;
            width:150px;
            height:150px;
            border-radius:50%;
            background:radial-gradient(circle, rgba(6,182,212,.17), transparent 70%);
            pointer-events:none;
        }

        .metric-top{
            display:flex;
            justify-content:space-between;
            gap:15px;
            align-items:flex-start;
            margin-bottom:14px;
            position:relative;
            z-index:2;
        }

        .metric-label{
            font-size:11px;
            font-weight:900;
            text-transform:uppercase;
            letter-spacing:1px;
            color:#8ba4d0;
            margin-bottom:7px;
        }

        .metric-title{
            margin:0;
            color:#fff;
            font-size:21px;
            font-weight:900;
            line-height:1.18;
        }

        .metric-icon{
            width:72px;
            height:72px;
            border-radius:20px;
            display:flex;
            align-items:center;
            justify-content:center;
            font-size:36px;
            background:rgba(255,255,255,.055);
            border:1px solid rgba(255,255,255,.08);
            flex-shrink:0;
        }

        .metric-number{
            position:relative;
            z-index:2;
            font-size:31px;
            font-weight:900;
            color:#fff;
            line-height:1;
            margin:10px 0 8px;
        }

        .metric-money{
            position:relative;
            z-index:2;
            color:#dbeafe;
            font-size:22px;
            font-weight:900;
            line-height:1.25;
            margin:0 0 10px;
            word-break:break-word;
        }

        .metric-bottom{
            position:relative;
            z-index:2;
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
            font-weight:900;
            background:rgba(15,23,42,.82);
            color:#dbeafe;
            border:1px solid rgba(255,255,255,.08);
        }

        .metric-mini{
            color:#8ea6cd;
            font-size:13px;
            font-weight:700;
        }

        .metric-detail-btn{
            width:100%;
            border:none;
            border-radius:14px;
            padding:12px 14px;
            font-size:13px;
            font-weight:900;
            color:#fff;
            background:linear-gradient(135deg, rgba(59,130,246,.96), rgba(6,182,212,.93));
            box-shadow:0 12px 24px rgba(37,99,235,.20);
            transition:.25s ease;
            margin-top:14px;
        }

        .metric-detail-btn:hover{
            transform:translateY(-2px);
            box-shadow:0 16px 30px rgba(37,99,235,.28);
        }

        .section-card{
            position:relative;
            overflow:hidden;
            border-radius:27px;
            padding:22px;
            background:linear-gradient(145deg, rgba(10,18,34,.94), rgba(17,24,39,.88));
            border:1px solid rgba(255,255,255,.08);
            box-shadow:0 18px 38px rgba(0,0,0,.30), inset 0 1px 0 rgba(255,255,255,.04);
            margin-bottom:20px;
        }

        .section-card::before{
            content:"";
            position:absolute;
            width:280px;
            height:280px;
            right:-90px;
            top:-110px;
            background:radial-gradient(circle, rgba(59,130,246,.09), transparent 70%);
            pointer-events:none;
        }

        .section-header{
            position:relative;
            z-index:2;
            display:flex;
            justify-content:space-between;
            align-items:center;
            gap:12px;
            flex-wrap:wrap;
            margin-bottom:18px;
        }

        .section-title{
            margin:0;
            color:#fff;
            font-size:23px;
            font-weight:900;
        }

        .section-desc{
            margin:5px 0 0;
            color:#8ea6cd;
            font-size:13px;
            line-height:1.5;
        }

        .section-tag{
            padding:8px 12px;
            border-radius:999px;
            font-size:12px;
            font-weight:900;
            color:#dbeafe;
            background:rgba(15,23,42,.78);
            border:1px solid rgba(255,255,255,.08);
        }

        .two-col{
            display:grid;
            grid-template-columns:1.25fr .75fr;
            gap:18px;
        }

        .chart-wrap{
            position:relative;
            z-index:2;
            width:100%;
            min-height:390px;
            height:390px;
        }

        .projects-grid{
            position:relative;
            z-index:2;
            display:grid;
            grid-template-columns:repeat(3, 1fr);
            gap:14px;
        }

        .project-card{
            border-radius:20px;
            padding:16px;
            background:rgba(255,255,255,.045);
            border:1px solid rgba(255,255,255,.08);
            transition:.25s ease;
        }

        .project-card:hover{
            transform:translateY(-3px);
            border-color:rgba(34,197,94,.25);
        }

        .project-title{
            color:#fff;
            font-size:16px;
            font-weight:900;
            margin:0 0 8px;
        }

        .project-meta{
            color:#9fb2d4;
            font-size:12px;
            line-height:1.45;
            margin-bottom:12px;
        }

        .progress-line{
            width:100%;
            height:12px;
            border-radius:999px;
            overflow:hidden;
            background:rgba(255,255,255,.07);
            border:1px solid rgba(255,255,255,.05);
        }

        .progress-fill{
            height:100%;
            border-radius:999px;
            background:linear-gradient(90deg, #3b82f6, #06b6d4, #22c55e);
            box-shadow:0 0 18px rgba(6,182,212,.25);
        }

        .form-grid{
            position:relative;
            z-index:2;
            display:grid;
            grid-template-columns:repeat(3, 1fr);
            gap:14px;
        }

        .form-group{
            display:flex;
            flex-direction:column;
            gap:7px;
        }

        .form-group.full{
            grid-column:1 / -1;
        }

        .form-group label{
            color:#b7c9e8;
            font-size:12px;
            font-weight:900;
            letter-spacing:.4px;
        }

        .form-control-wow{
            width:100%;
            min-height:46px;
            border-radius:15px;
            border:1px solid rgba(255,255,255,.10);
            background:rgba(255,255,255,.065);
            color:#fff !important;
            padding:11px 14px;
            outline:none;
        }

        .form-control-wow option{
            background:#0f172a;
            color:#fff !important;
        }

        textarea.form-control-wow{
            min-height:105px;
            resize:vertical;
        }

        .submit-wow{
            border:none;
            min-height:48px;
            border-radius:16px;
            padding:12px 18px;
            color:#fff;
            font-size:14px;
            font-weight:900;
            cursor:pointer;
            background:linear-gradient(135deg, #2563eb, #06b6d4, #22c55e);
            box-shadow:0 14px 28px rgba(37,99,235,.24);
            transition:.25s ease;
        }

        .submit-wow:hover{
            transform:translateY(-2px);
            box-shadow:0 18px 36px rgba(37,99,235,.32);
        }

        .table-responsive-wow{
            position:relative;
            z-index:2;
            overflow:auto;
            border-radius:20px;
            border:1px solid rgba(255,255,255,.08);
        }

        .table-wow{
            width:100%;
            border-collapse:separate;
            border-spacing:0;
            min-width:900px;
            color:#dbeafe;
            margin:0;
        }

        .table-wow thead th{
            position:sticky;
            top:0;
            z-index:2;
            padding:14px 14px;
            background:rgba(15,23,42,.95);
            color:#fff;
            font-size:12px;
            text-transform:uppercase;
            letter-spacing:.8px;
            border-bottom:1px solid rgba(255,255,255,.08);
            white-space:nowrap;
        }

        .table-wow tbody td{
            padding:14px;
            border-bottom:1px solid rgba(255,255,255,.06);
            font-size:13px;
            vertical-align:top;
        }

        .table-wow tbody tr{
            background:rgba(255,255,255,.025);
        }

        .table-wow tbody tr:hover{
            background:rgba(59,130,246,.08);
        }

        .pill{
            display:inline-flex;
            align-items:center;
            gap:6px;
            padding:6px 10px;
            border-radius:999px;
            font-size:12px;
            font-weight:900;
            color:#dbeafe;
            background:rgba(15,23,42,.78);
            border:1px solid rgba(255,255,255,.08);
        }

        .modal-infra{
            position:fixed;
            inset:0;
            z-index:99999;
            display:none;
            align-items:center;
            justify-content:center;
            padding:20px;
            background:rgba(4,10,20,.74);
            backdrop-filter:blur(8px);
        }

        .modal-infra.active{
            display:flex;
        }

        .modal-box-infra{
            width:min(1050px, 100%);
            max-height:86vh;
            overflow:hidden;
            border-radius:27px;
            border:1px solid rgba(255,255,255,.10);
            background:linear-gradient(145deg, rgba(10,18,34,.98), rgba(17,24,39,.96));
            box-shadow:0 28px 70px rgba(0,0,0,.50);
        }

        .modal-head-infra{
            display:flex;
            justify-content:space-between;
            align-items:center;
            gap:12px;
            padding:20px 22px;
            border-bottom:1px solid rgba(255,255,255,.08);
            background:linear-gradient(135deg, rgba(30,41,59,.88), rgba(18,52,86,.88));
        }

        .modal-title-infra{
            margin:0;
            color:#fff;
            font-size:24px;
            font-weight:900;
        }

        .modal-sub-infra{
            margin-top:5px;
            color:#9fb2d4;
            font-size:13px;
        }

        .modal-close-infra{
            border:none;
            width:43px;
            height:43px;
            border-radius:15px;
            background:rgba(255,255,255,.08);
            color:#fff;
            font-size:22px;
            font-weight:900;
            cursor:pointer;
        }

        .modal-body-infra{
            padding:20px 22px 22px;
            max-height:calc(86vh - 86px);
            overflow:auto;
        }

        .detail-list{
            display:grid;
            grid-template-columns:1fr;
            gap:12px;
        }

        .detail-item{
            border-radius:18px;
            padding:16px;
            border:1px solid rgba(255,255,255,.08);
            background:rgba(255,255,255,.045);
        }

        .detail-title{
            margin:0 0 10px;
            color:#fff;
            font-size:16px;
            font-weight:900;
        }

        .detail-grid{
            display:grid;
            grid-template-columns:repeat(4, 1fr);
            gap:10px;
        }

        .detail-kpi{
            border-radius:15px;
            padding:12px;
            background:rgba(15,23,42,.75);
            border:1px solid rgba(255,255,255,.07);
        }

        .detail-kpi span{
            display:block;
            color:#8ea6cd;
            font-size:11px;
            text-transform:uppercase;
            font-weight:900;
            letter-spacing:.5px;
            margin-bottom:5px;
        }

        .detail-kpi strong{
            color:#fff;
            font-size:14px;
            font-weight:900;
        }

        .empty-state{
            position:relative;
            z-index:2;
            padding:28px 14px;
            text-align:center;
            border-radius:20px;
            border:1px dashed rgba(255,255,255,.15);
            background:rgba(255,255,255,.03);
            color:#9fb2d4;
            font-weight:700;
        }

        .pcoded-navbar{
            position:fixed !important;
            z-index:1035 !important;
            pointer-events:auto !important;
        }

        .pcoded-header{
            position:relative !important;
            z-index:1020 !important;
        }

        .pcoded-main-container,
        .pcoded-content{
            position:relative !important;
            z-index:1 !important;
        }

        @media(max-width:1199px){
            .summary-grid{
                grid-template-columns:repeat(2, 1fr);
            }

            .dashboard-grid{
                grid-template-columns:repeat(2, 1fr);
            }

            .two-col{
                grid-template-columns:1fr;
            }

            .projects-grid{
                grid-template-columns:repeat(2, 1fr);
            }

            .filter-grid{
                grid-template-columns:1fr 1fr;
            }
        }

        @media(max-width:991px){
            .hero-title{
                font-size:31px;
            }

            .form-grid{
                grid-template-columns:1fr 1fr;
            }
        }

        @media(max-width:767px){
            .dashboard-wrap{
                padding:8px 4px 28px;
            }

            .hero-panel{
                padding:20px 16px;
                border-radius:23px;
            }

            .hero-title{
                font-size:26px;
            }

            .hero-actions,
            .top-badges{
                width:100%;
            }

            .hero-btn{
                flex:1;
                text-align:center;
            }

            .summary-grid,
            .dashboard-grid,
            .projects-grid,
            .form-grid,
            .filter-grid{
                grid-template-columns:1fr;
            }

            .summary-card,
            .metric-card,
            .section-card{
                border-radius:21px;
            }

            .metric-card{
                min-height:auto;
            }

            .chart-wrap{
                height:330px;
                min-height:330px;
            }

            .detail-grid{
                grid-template-columns:1fr;
            }
        }
        .chart-wrap{
    position:relative;
    z-index:2;
    width:100%;
    min-height:390px;
    height:390px;
    background:linear-gradient(145deg, rgba(15,23,42,.72), rgba(30,41,59,.48));
    border:1px solid rgba(255,255,255,.08);
    border-radius:22px;
    padding:12px;
    overflow:hidden;
}
.chart-wrap{
    position:relative;
    z-index:2;
    width:100%;
    min-height:520px;
    height:520px;
    background:linear-gradient(145deg, rgba(15,23,42,.72), rgba(30,41,59,.48));
    border:1px solid rgba(255,255,255,.08);
    border-radius:22px;
    padding:16px;
    overflow:hidden;
}
    </style>

    <div class="pcoded-main-container">
        <div class="pcoded-content">
            <div class="dashboard-wrap">

                <section class="hero-panel">
                    <div class="top-badges">
                        <span class="badge-chip">🏗️ Secretaría de Infraestructura</span>
                        <span class="badge-chip">📍 Santander</span>
                        <span class="badge-chip">📅 Actualizado: <?php echo safe($fechaActualizacion); ?></span>
                    </div>

                    <div class="hero-content">
                        <div>
                            <h1 class="hero-title">Dashboard Secretaría de Infraestructura</h1>
                            <p class="hero-subtitle">
                                Visualiza proyectos radicados, viabilizados, contratos en ejecución, inversión por líneas estratégicas,
                                seguridad vial y proyectos estratégicos priorizados.
                            </p>
                        </div>

                        <div class="hero-actions">
                            <button type="button" class="hero-btn primary" onclick="scrollToSection('seccionDashboard')">📊 Dashboard</button>
                            <button type="button" class="hero-btn success" onclick="scrollToSection('seccionFormulario')">➕ Registrar</button>
                            <button type="button" class="hero-btn warning" onclick="scrollToSection('seccionProyectos')">🚧 Proyectos</button>
                            <button type="button" class="hero-btn dark" id="modoTVBtn">🖥️ Modo TV</button>
                        </div>
                    </div>

                    <form class="filter-panel" method="GET">
                        <div class="filter-grid">
                            <div class="filter-group">
                                <label>Fecha desde</label>
                                <input type="date" name="fecha_desde" class="filter-input" value="<?php echo safe($fechaDesde); ?>">
                            </div>

                            <div class="filter-group">
                                <label>Fecha hasta</label>
                                <input type="date" name="fecha_hasta" class="filter-input" value="<?php echo safe($fechaHasta); ?>">
                            </div>

                            <button type="submit" class="filter-btn apply">Aplicar filtro</button>
                            <a href="<?php echo safe(basename($_SERVER['PHP_SELF'])); ?>" class="filter-btn clear" style="display:flex;align-items:center;text-decoration:none;">Limpiar</a>
                        </div>
                    </form>
                </section>

                <section id="seccionDashboard" class="summary-grid">
                    <div class="summary-card">
                        <div class="label">Proyectos radicados</div>
                        <div class="value"><?php echo numFormat($indicadores['proyectos_radicados_total']['valor_numerico'] ?? 0); ?></div>
                        <div class="sub">Total consolidado 2024, 2025 y 2026.</div>
                    </div>

                    <div class="summary-card">
                        <div class="label">Proyectos viabilizados</div>
                        <div class="value"><?php echo numFormat($indicadores['proyectos_viabilizados_total']['valor_numerico'] ?? 0); ?></div>
                        <div class="sub">Proyectos con viabilidad reportada.</div>
                    </div>

                    <div class="summary-card">
                        <div class="label">Contratos en ejecución</div>
                        <div class="value"><?php echo numFormat($indicadores['contratos_ejecucion_marzo_2026']['valor_numerico'] ?? 0); ?></div>
                        <div class="sub">Corte a marzo de 2026.</div>
                    </div>

                    <div class="summary-card">
                        <div class="label">Inversión consolidada</div>
                        <div class="value"><?php echo moneyCOP($totalGeneral); ?></div>
                        <div class="sub">Incluye base Excel y registros nuevos filtrados.</div>
                    </div>
                </section>

                <section class="dashboard-grid">
                    <?php
                    $icons = ['🛣️', '🌉', '⚡', '🚜', '📐', '🏟️'];
                    $i = 0;
                    foreach ($bloques as $bloque):
                        $icon = $icons[$i % count($icons)];
                        $i++;
                    ?>
                        <article class="metric-card">
                            <div class="metric-top">
                                <div>
                                    <div class="metric-label">Línea estratégica</div>
                                    <h3 class="metric-title"><?php echo safe($bloque['bloque']); ?></h3>
                                </div>
                                <div class="metric-icon"><?php echo $icon; ?></div>
                            </div>

                            <div class="metric-number"><?php echo numFormat($bloque['total_items']); ?></div>
                            <p class="metric-money"><?php echo moneyCOP($bloque['total_recurso']); ?></p>

                            <div class="metric-bottom">
                                <span class="metric-status">● Operativo</span>
                                <span class="metric-mini">
                                    <?php echo numFormat($bloque['total_municipios']); ?> municipios
                                </span>
                            </div>

                            <button
                                type="button"
                                class="metric-detail-btn"
                                onclick="openInfraModal('<?php echo safe($bloque['bloque']); ?>')">
                                📄 Ver detalle inversión
                            </button>
                        </article>
                    <?php endforeach; ?>
                </section>

                <section class="two-col">
                    <div class="section-card">
                        <div class="section-header">
                            <div>
                                <h2 class="section-title">Inversión por línea estratégica</h2>
                                <p class="section-desc">Distribución de recursos reportados para infraestructura.</p>
                            </div>
                            <span class="section-tag">Recurso total</span>
                        </div>

                        <div class="chart-wrap" id="chartBloques"></div>
                    </div>

                    <div class="section-card">
                        <div class="section-header">
                            <div>
                                <h2 class="section-title">Plan de Desarrollo</h2>
                                <p class="section-desc">Avance consolidado a marzo de 2026.</p>
                            </div>
                            <span class="section-tag"><?php echo percentValue($indicadores['seguimiento_plan_desarrollo']['valor_numerico'] ?? 0); ?></span>
                        </div>

                        <div class="summary-card" style="margin-bottom:14px;">
                            <div class="label">Avance</div>
                            <div class="value"><?php echo percentValue($indicadores['seguimiento_plan_desarrollo']['valor_numerico'] ?? 0); ?></div>
                            <div class="sub">Seguimiento Plan de Desarrollo Secretaría de Infraestructura.</div>
                        </div>

                        <div class="summary-card">
                            <div class="label">Seguridad vial 2026</div>
                            <div class="value"><?php echo numFormat($indicadores['lesionados_2026']['valor_numerico'] ?? 0); ?></div>
                            <div class="sub">
                                Lesionados: hombres <?php echo percentValue($indicadores['lesionados_hombres_2026']['valor_numerico'] ?? 0); ?> /
                                mujeres <?php echo percentValue($indicadores['lesionados_mujeres_2026']['valor_numerico'] ?? 0); ?>.
                            </div>
                        </div>
                    </div>
                </section>

                <section class="section-card">
                    <div class="section-header">
                        <div>
                            <h2 class="section-title">Detalle de inversión por categoría</h2>
                            <p class="section-desc">Ranking por categoría, recursos y cobertura municipal.</p>
                        </div>
                        <span class="section-tag"><?php echo count($inversionDetalle); ?> registros base</span>
                    </div>

                    <div class="chart-wrap" id="chartCategorias"></div>
                </section>

                <section id="seccionFormulario" class="section-card">
                    <div class="section-header">
                        <div>
                            <h2 class="section-title">Formulario de registro infraestructura</h2>
                            <p class="section-desc">Registra nuevos proyectos, avances, inversiones, contratos o reportes de seguimiento.</p>
                        </div>
                        <span class="section-tag">Registro dinámico</span>
                    </div>

                    <form method="POST" class="form-grid" autocomplete="off">
                        <input type="hidden" name="guardar_registro_infra" value="1">

                        <div class="form-group">
                            <label>Tipo de registro</label>
                            <select name="tipo" class="form-control-wow" required>
                                <option value="">Seleccione</option>
                                <option value="Proyecto">Proyecto</option>
                                <option value="Contrato">Contrato</option>
                                <option value="Inversión">Inversión</option>
                                <option value="Seguimiento">Seguimiento</option>
                                <option value="Seguridad vial">Seguridad vial</option>
                                <option value="Estudios y diseños">Estudios y diseños</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label>Categoría</label>
                            <select name="categoria" class="form-control-wow" required>
                                <option value="">Seleccione</option>
                                <option value="Vías">Vías</option>
                                <option value="Puentes">Puentes</option>
                                <option value="Mineroenergética">Mineroenergética</option>
                                <option value="Maquinaria">Maquinaria</option>
                                <option value="Estudios y diseños">Estudios y diseños</option>
                                <option value="Infraestructura deportiva">Infraestructura deportiva</option>
                                <option value="Parques">Parques</option>
                                <option value="Proyecto estratégico">Proyecto estratégico</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label>Nombre del registro</label>
                            <input type="text" name="nombre" class="form-control-wow" placeholder="Ej: Mejoramiento vía terciaria" required>
                        </div>

                        <div class="form-group">
                            <label>Municipio</label>
                            <input type="text" name="municipio" class="form-control-wow" placeholder="Ej: Bucaramanga">
                        </div>

                        <div class="form-group">
                            <label>Valor inversión</label>
                            <input type="text" name="valor" class="form-control-wow money-input" placeholder="Ej: 150000000">
                        </div>

                        <div class="form-group">
                            <label>Avance %</label>
                            <input type="number" name="avance" class="form-control-wow" min="0" max="100" step="0.01" placeholder="Ej: 45">
                        </div>

                        <div class="form-group">
                            <label>Fecha inicio</label>
                            <input type="date" name="fecha_inicio" class="form-control-wow">
                        </div>

                        <div class="form-group">
                            <label>Fecha corte / cumplimiento</label>
                            <input type="date" name="fecha_corte" class="form-control-wow">
                        </div>

                        <div class="form-group">
                            <label>Acción</label>
                            <button type="submit" class="submit-wow">💾 Guardar registro</button>
                        </div>

                        <div class="form-group full">
                            <label>Descripción / observaciones</label>
                            <textarea name="descripcion" class="form-control-wow" placeholder="Describe el avance, estado, impacto, gestión realizada o información relevante."></textarea>
                        </div>
                    </form>
                </section>

                <section class="section-card">
                    <div class="section-header">
                        <div>
                            <h2 class="section-title">Últimos registros cargados</h2>
                            <p class="section-desc">Registros agregados desde el formulario de infraestructura.</p>
                        </div>
                        <span class="section-tag"><?php echo numFormat($totalRegistrosUsuario); ?> registros filtrados</span>
                    </div>

                    <?php if (count($ultimosRegistros) > 0): ?>
                        <div class="table-responsive-wow">
                            <table class="table-wow">
                                <thead>
                                    <tr>
                                        <th>Fecha</th>
                                        <th>Tipo</th>
                                        <th>Categoría</th>
                                        <th>Nombre</th>
                                        <th>Municipio</th>
                                        <th>Valor</th>
                                        <th>Avance</th>
                                        <th>Descripción</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($ultimosRegistros as $r): ?>
                                        <tr>
                                            <td><?php echo safe(date('Y-m-d', strtotime($r['created_at']))); ?></td>
                                            <td><span class="pill"><?php echo safe($r['tipo']); ?></span></td>
                                            <td><?php echo safe($r['categoria']); ?></td>
                                            <td><strong style="color:#fff;"><?php echo safe($r['nombre']); ?></strong></td>
                                            <td><?php echo safe($r['municipio'] ?: 'No registra'); ?></td>
                                            <td><?php echo moneyCOP($r['valor']); ?></td>
                                            <td><?php echo $r['avance'] !== null ? numFormat($r['avance'], 1) . '%' : '0%'; ?></td>
                                            <td><?php echo safe($r['descripcion']); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="empty-state">
                            Aún no hay registros nuevos cargados con los filtros actuales.
                        </div>
                    <?php endif; ?>
                </section>

            </div>
        </div>
    </div>

    <div class="modal-infra" id="modalInfra">
        <div class="modal-box-infra">
            <div class="modal-head-infra">
                <div>
                    <h3 class="modal-title-infra" id="modalInfraTitle">Detalle inversión</h3>
                    <div class="modal-sub-infra">Información consolidada según línea estratégica.</div>
                </div>
                <button type="button" class="modal-close-infra" onclick="closeInfraModal()">×</button>
            </div>

            <div class="modal-body-infra">
                <div class="detail-list" id="modalInfraBody"></div>
            </div>
        </div>
    </div>

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
    <script src="https://code.highcharts.com/highcharts.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        const infraDetalle = <?php echo json_encode($inversionDetalle, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?>;
        const chartBloquesLabels = <?php echo json_encode($chartBloques, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?>;
        const chartBloquesValores = <?php echo json_encode($chartValores, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?>;
        const chartCategoriasLabels = <?php echo json_encode($chartCategorias, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?>;
        const chartCategoriasValores = <?php echo json_encode($chartCategoriaValores, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?>;

        function formatCOP(value) {
            value = Number(value || 0);
            return '$' + value.toLocaleString('es-CO', {
                maximumFractionDigits: 0
            });
        }

        function scrollToSection(id) {
            const el = document.getElementById(id);
            if (el) {
                el.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }
        }

        function openInfraModal(bloque) {
            const modal = document.getElementById('modalInfra');
            const title = document.getElementById('modalInfraTitle');
            const body = document.getElementById('modalInfraBody');

            title.textContent = bloque;
            const items = infraDetalle.filter(item => item.bloque === bloque);

            if (!items.length) {
                body.innerHTML = `<div class="empty-state">No hay información registrada para esta línea.</div>`;
            } else {
                body.innerHTML = items.map(item => `
                    <article class="detail-item">
                        <h4 class="detail-title">${item.categoria || 'Sin categoría'}</h4>

                        <div class="detail-grid">
                            <div class="detail-kpi">
                                <span>${item.medida_label || 'Medida'}</span>
                                <strong>${Number(item.medida_valor || 0).toLocaleString('es-CO')}</strong>
                            </div>

                            <div class="detail-kpi">
                                <span>Municipios</span>
                                <strong>${Number(item.municipios || 0).toLocaleString('es-CO')}</strong>
                            </div>

                            <div class="detail-kpi">
                                <span>Recurso total</span>
                                <strong>${formatCOP(item.recurso_total)}</strong>
                            </div>

                            <div class="detail-kpi">
                                <span>Observación</span>
                                <strong>${item.observacion || 'Sin observación'}</strong>
                            </div>

                            <div class="detail-kpi">
                                <span>Recurso 2024</span>
                                <strong>${formatCOP(item.recurso_2024)}</strong>
                            </div>

                            <div class="detail-kpi">
                                <span>Recurso 2025</span>
                                <strong>${formatCOP(item.recurso_2025)}</strong>
                            </div>
                        </div>
                    </article>
                `).join('');
            }

            modal.classList.add('active');
        }

        function closeInfraModal() {
            document.getElementById('modalInfra').classList.remove('active');
        }

        document.getElementById('modalInfra').addEventListener('click', function(e) {
            if (e.target === this) {
                closeInfraModal();
            }
        });

        document.getElementById('modoTVBtn').addEventListener('click', function() {
            const elem = document.documentElement;
            if (!document.fullscreenElement) {
                elem.requestFullscreen?.();
            } else {
                document.exitFullscreen?.();
            }
        });

        document.querySelectorAll('.money-input').forEach(input => {
            input.addEventListener('input', function() {
                this.value = this.value.replace(/[^\d]/g, '');
            });
        });

        window.addEventListener('load', function() {
            setTimeout(function() {
                const loader = document.getElementById('pageLoader');
                if (loader) loader.classList.add('hidden');
            }, 450);
        });

       if (typeof Highcharts !== 'undefined') {

    Highcharts.setOptions({
        chart: {
            backgroundColor: 'transparent',
            style: {
                fontFamily: 'Inter, Arial, sans-serif'
            }
        },
        title: {
            style: {
                color: '#100735',
                fontWeight: '900'
            }
        },
        subtitle: {
            style: {
                color: '#100735'
            }
        },
        xAxis: {
            labels: {
                style: {
                    color: '#100735',
                    fontSize: '12px',
                    fontWeight: '700',
                    textOutline: 'none'
                }
            },
            title: {
                style: {
                    color: '#100735',
                    fontWeight: '800'
                }
            },
            lineColor: 'rgba(255,255,255,.22)',
            tickColor: 'rgba(255,255,255,.22)',
            gridLineColor: 'rgba(255,255,255,.08)'
        },
        yAxis: {
            labels: {
                style: {
                    color: '#100735',
                    fontSize: '12px',
                    fontWeight: '700',
                    textOutline: 'none'
                }
            },
            title: {
                style: {
                    color: '#100735',
                    fontWeight: '800'
                }
            },
            gridLineColor: 'rgba(255,255,255,.10)'
        },
        legend: {
            itemStyle: {
                color: '#100735',
                fontWeight: '800'
            },
            itemHoverStyle: {
                color: '#100735'
            }
        },
        tooltip: {
            backgroundColor: 'rgba(15,23,42,.98)',
            borderColor: 'rgba(56,189,248,.45)',
            borderRadius: 14,
            shadow: true,
            style: {
                color: '#100735',
                fontSize: '13px'
            }
        },
        credits: {
            enabled: false
        }
    });

    Highcharts.chart('chartBloques', {
        chart: {
            type: 'column',
            backgroundColor: 'transparent'
        },
        title: {
            text: null
        },
        xAxis: {
            categories: chartBloquesLabels,
            labels: {
                style: {
                    color: '#100735',
                    fontSize: '12px',
                    fontWeight: '800',
                    textOutline: 'none'
                }
            }
        },
        yAxis: {
            title: {
                text: 'Recursos',
                style: {
                    color: '#100735',
                    fontSize: '13px',
                    fontWeight: '900'
                }
            },
            labels: {
                style: {
                    color: '#100735',
                    fontSize: '12px',
                    fontWeight: '800',
                    textOutline: 'none'
                },
                formatter: function() {
                    return '$' + Highcharts.numberFormat(this.value / 1000000000, 0) + ' MM';
                }
            }
        },
        legend: {
            enabled: false
        },
        tooltip: {
            formatter: function() {
                return `
                    <div style="color:#fff;">
                        <b style="font-size:14px;">${this.x}</b><br>
                        Recurso: <b style="color:#38bdf8;">${formatCOP(this.y)}</b>
                    </div>
                `;
            }
        },
        plotOptions: {
            column: {
                borderRadius: 10,
                borderWidth: 0,
                colorByPoint: true,
                dataLabels: {
                    enabled: true,
                    inside: false,
                    style: {
                        color: '#100735',
                        fontSize: '11px',
                        fontWeight: '900',
                        textOutline: '2px contrast'
                    },
                    formatter: function() {
                        return '$' + Highcharts.numberFormat(this.y / 1000000000, 0) + ' MM';
                    }
                }
            }
        },
        series: [{
            name: 'Recurso',
            data: chartBloquesValores
        }]
    });

    Highcharts.chart('chartCategorias', {
        chart: {
            type: 'bar',
            backgroundColor: 'transparent',
            spacingLeft: 10,
            spacingRight: 25,
            spacingTop: 10,
            spacingBottom: 18
        },
        title: {
            text: null
        },
        xAxis: {
            categories: chartCategoriasLabels,
            labels: {
                style: {
                    color: '#100735',
                    fontSize: '12px',
                    fontWeight: '900',
                    textOutline: 'none'
                }
            },
            lineColor: 'rgba(255,255,255,.25)',
            tickColor: 'rgba(255,255,255,.25)'
        },
        yAxis: {
            title: {
                text: 'Recurso total',
                style: {
                    color: '#100735',
                    fontSize: '13px',
                    fontWeight: '900'
                }
            },
            labels: {
                style: {
                    color: '#100735',
                    fontSize: '12px',
                    fontWeight: '800',
                    textOutline: 'none'
                },
                formatter: function() {
                    return '$' + Highcharts.numberFormat(this.value / 1000000000, 0) + ' MM';
                }
            },
            gridLineColor: 'rgba(255,255,255,.12)'
        },
        legend: {
            enabled: false
        },
        tooltip: {
            formatter: function() {
                return `
                    <div style="color:#100735;">
                        <b style="font-size:14px;">${this.x}</b><br>
                        Recurso: <b style="color:#100735;">${formatCOP(this.y)}</b>
                    </div>
                `;
            }
        },
        plotOptions: {
            bar: {
                borderRadius: 8,
                borderWidth: 0,
                colorByPoint: true,
                dataLabels: {
                    enabled: true,
                    align: 'right',
                    inside: false,
                    style: {
                        color: '#100735',
                        fontSize: '11px',
                        fontWeight: '900',
                        textOutline: '2px contrast'
                    },
                    formatter: function() {
                        return '$' + Highcharts.numberFormat(this.y / 1000000000, 0) + ' MM';
                    }
                }
            }
        },
        series: [{
            name: 'Recurso',
            data: chartCategoriasValores
        }]
    });
}

        <?php if ($alertMessage !== ''): ?>
        Swal.fire({
            icon: '<?php echo $alertType; ?>',
            title: '<?php echo $alertType === 'success' ? 'Registro exitoso' : 'Atención'; ?>',
            text: '<?php echo safe($alertMessage); ?>',
            background: '#0f172a',
            color: '#100735',
            confirmButtonColor: '#100735',
            confirmButtonText: 'Entendido'
        });
        <?php endif; ?>
    </script>
</body>
</html>