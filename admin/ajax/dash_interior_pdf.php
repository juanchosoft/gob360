<?php
// admin/ajax/dash_interior_pdf.php
// Genera el Boletín Estratégico de Seguridad y Convivencia – 3 páginas A4 vertical
// Estructura basada en el PDF de referencia "Boletín Gubernamental 05 MARZO"

require_once __DIR__ . '/../include/generic_classes.php';
require_once __DIR__ . '/../include/require_permission.php';
requirePermission('interior.boletin.view');
require_once __DIR__ . '/../classes/DashInterior.php';
require_once __DIR__ . '/../../admin/include/TCPDF-main/tcpdf.php';

$boletinId = isset($_GET['boletin_id']) ? (int)$_GET['boletin_id'] : 0;

// Auto-detectar boletín activo si no se especifica uno
if ($boletinId <= 0) {
    $activeBul = DashInterior::getActiveBulletin();
    if ($activeBul) $boletinId = (int)$activeBul['id'];
}

$payload = $boletinId > 0
    ? DashInterior::getPayloadWithBulletin($boletinId)
    : DashInterior::getPayload();

if (!$payload['ok']) {
    header('Content-Type: text/plain; charset=utf-8');
    echo 'Error: ' . ($payload['msg'] ?? ''); exit;
}

$meta     = $payload['meta']     ?? [];
$datasets = $payload['datasets'] ?? [];
$factors  = $payload['factors']  ?? [];

$anio1          = (int)($meta['anio_1']          ?? 2025);
$anio2          = (int)($meta['anio_2']          ?? 2026);
$boletinNo      = isset($meta['boletin_no']) && $meta['boletin_no'] !== null && $meta['boletin_no'] !== '' ? (int)$meta['boletin_no'] : null;
$fechaCierre    = trim((string)($meta['fecha_cierre']    ?? ''));
$fuente         = trim((string)($meta['fuente']          ?? ''));
$tasaHomicidios = trim((string)($meta['tasa_homicidios'] ?? ''));
$notaHtml              = strip_tags(trim((string)($meta['nota_html'] ?? '')));
$municipiosSinHomicidios = (int)($meta['municipios_sin_homicidios'] ?? 0);

// Total de homicidios Santander (categoría "Homicidio" de dataset santander_politico, serie año 2)
$totalHom = 0;
$catsSant = array_values((array)(($datasets['santander_politico'] ?? [])['cats'] ?? []));
$serieSant2 = array_values((array)(($datasets['santander_politico'] ?? [])['serie_anio_2'] ?? []));
foreach ($catsSant as $i => $cat) {
    if (strtolower(trim((string)$cat)) === 'homicidio') {
        $totalHom = (int)($serieSant2[$i] ?? 0);
        break;
    }
}

// ─── Rutas de imágenes ────────────────────────────────────────────────────────
$BASE    = __DIR__ . '/../../assets/img/';
$escudo  = $BASE . 'sandander_escudo.png';
// gobLogo: se intenta primero la imagen personalizada si el usuario la sube,
// si no existe se dibuja el texto con TCPDF
$gobLogoCustom = $BASE . 'gobernacion_logo_texto.png';  // imagen que puede subir el usuario
$gobLogo       = file_exists($gobLogoCustom) ? $gobLogoCustom : null;
$logoSf  = $BASE . 'logosf.png';

// ─── Colores (R, G, B) ────────────────────────────────────────────────────────
$C_WHITE   = [255, 255, 255];
$C_BLACK   = [  0,   0,   0];
$C_GRAY    = [100, 100, 100];
$C_LGRAY   = [220, 220, 220];
$C_BGPAGE  = [235, 235, 235];   // fondo gris muy claro de la página
$C_DARKBLUE= [ 12,  60, 110];   // azul oscuro institucional
$C_MIDBLUE = [ 22,  90, 160];   // azul medio
$C_GREEN   = [ 20, 140,  60];   // verde barras año2 / variaciones positivas
$C_ORANGE  = [210,  80,   0];   // naranja barras año1
$C_LIME    = [120, 200,  20];   // verde lima cabecera "Secretaria del Interior"
$C_RED     = [200,  30,  30];
$C_YELLOW  = [220, 180,   0];
$C_TEAL    = [ 20, 100, 100];   // verde azulado cabeceras de cards

// ─── Helpers ──────────────────────────────────────────────────────────────────
function fc(TCPDF $p, array $c): void { $p->SetFillColor($c[0],$c[1],$c[2]); }
function dc(TCPDF $p, array $c): void { $p->SetDrawColor($c[0],$c[1],$c[2]); }
function tc(TCPDF $p, array $c): void { $p->SetTextColor($c[0],$c[1],$c[2]); }

function rect(TCPDF $p, float $x, float $y, float $w, float $h, array $fill, string $style='F'): void {
    fc($p, $fill); dc($p, $fill);
    $p->Rect($x, $y, $w, $h, $style);
}

/**
 * Cabecera institucional común a páginas 1 y 2.
 * Retorna la Y donde termina el bloque de cabecera.
 */
function drawPageHeader(TCPDF $pdf, float $pgW, string $escudo, ?string $gobLogo,
    int $anio1, int $anio2, string $titulo,
    array $C_WHITE, array $C_DARKBLUE, array $C_MIDBLUE,
    array $C_LIME, array $C_BGPAGE, bool $showLogo = true): float
{
    $mg = 6;

    // Fondo gris muy claro de toda la página
    rect($pdf, 0, 0, $pgW, 297, $C_BGPAGE);

    if ($showLogo) {
        // Franja blanca superior (solo página 1)
        rect($pdf, 0, 0, $pgW, 28, $C_WHITE);
        dc($pdf, [200,200,200]);
        $pdf->SetLineWidth(0.3);
        $pdf->Line(0, 28, $pgW, 28);

        // Logo escudo
        if (file_exists($escudo)) {
            $pdf->Image($escudo, $mg, 2, 26, 26, 'PNG');
        }

        // Logo texto "GOBERNACIÓN DE SANTANDER"
        if ($gobLogo && file_exists($gobLogo)) {
            $pdf->Image($gobLogo, $mg + 30, 3, 80, 22, 'PNG');
        } else {
            $pdf->SetFont('helvetica', 'B', 12);
            tc($pdf, $C_DARKBLUE);
            $pdf->SetXY($mg + 30, 5);
            $pdf->Cell(90, 7, 'GOBERNACION DE', 0, 2, 'L');
            $pdf->SetFont('helvetica', 'B', 17);
            tc($pdf, $C_DARKBLUE);
            $pdf->SetX($mg + 30);
            $pdf->Cell(90, 10, 'SANTANDER', 0, 0, 'L');
            dc($pdf, $C_DARKBLUE);
            $pdf->SetLineWidth(0.8);
            $pdf->Line($mg + 30, 24, $mg + 120, 24);
            $pdf->SetLineWidth(0.2);
        }

        // "SECRETARIA DEL INTERIOR - DSCC" dentro de la franja blanca (derecha)
        $dscX = $mg + 125;
        $dscW = $pgW - $dscX - $mg;
        rect($pdf, $dscX, 4, $dscW, 18, $C_LIME);
        $pdf->SetFont('helvetica', 'B', 8);
        tc($pdf, $C_WHITE);
        $pdf->SetXY($dscX, 10);
        $pdf->Cell($dscW, 6, 'SECRETARIA DEL INTERIOR', 0, 2, 'C');
        $pdf->SetFont('helvetica', 'B', 7);
        $pdf->SetX($dscX);
        $pdf->Cell($dscW, 5, 'Dirección de Seguridad y Convivencia', 0, 0, 'C');
    }

    // Calcular Y de inicio de las franjas (depende de si hay logo)
    $bandaY = $showLogo ? 28 : 0;

    // Franja verde lima: "SECRETARIA DEL INTERIOR - DSCC" (oculta en página 1, visible en página 2)
    if (!$showLogo) {
        rect($pdf, 0, $bandaY, $pgW, 9, $C_LIME);
        $pdf->SetFont('helvetica', 'B', 9);
        tc($pdf, $C_WHITE);
        $pdf->SetXY(0, $bandaY + 1.5);
        $pdf->Cell($pgW, 6, 'SECRETARIA DEL INTERIOR - DSCC', 0, 0, 'C');
    }

    // Franja azul oscuro: título del boletín
    $tituloY = $showLogo ? $bandaY : ($bandaY + 9);
    rect($pdf, 0, $tituloY, $pgW, 17, $C_DARKBLUE);
    dc($pdf, $C_MIDBLUE);
    $pdf->SetLineWidth(0.5);
    $pdf->Line(0, $tituloY + 17, $pgW, $tituloY + 17);

    $pdf->SetFont('helvetica', 'B', 11);
    tc($pdf, $C_WHITE);
    $pdf->SetXY(0, $tituloY + 2);
    $pdf->MultiCell($pgW, 7, $titulo, 0, 'C', false, 1);

    return (float)($tituloY + 18);  // Y donde empieza el contenido
}

/**
 * Dibuja una card de gráfico (cabecera teal + barras + variaciones).
 * Retorna la Y final del bloque.
 */
function drawCard(
    TCPDF $pdf,
    float $cx, float $cy, float $cw,
    int   $num, string $titulo, string $subtitulo,
    array $cats, array $s1, array $s2,
    int $anio1, int $anio2,
    array $C_WHITE, array $C_TEAL, array $C_DARKBLUE,
    array $C_ORANGE, array $C_GREEN, array $C_RED,
    array $C_YELLOW, array $C_LGRAY, array $C_BGPAGE,
    int $cardH = 74
): float {
    $hdrH    = 11;   // alto de la cabecera de la card
    $kpiH    = 14;   // alto de la tabla de variaciones
    $chartH  = $cardH - $hdrH - $kpiH - 4;  // alto del área de barras (dinámico)
    $padding = 2;

    // Fondo blanco de la card
    dc($pdf, [180,180,180]);
    fc($pdf, $C_WHITE);
    $pdf->SetLineWidth(0.2);
    $pdf->Rect($cx, $cy, $cw, $cardH, 'DF');

    // ── Cabecera card (teal) ────────────────────────────────────────────────
    fc($pdf, $C_TEAL); dc($pdf, $C_TEAL);
    $pdf->Rect($cx, $cy, $cw, $hdrH, 'F');

    // Número (circunferencia blanca)
    $pdf->SetFont('helvetica', 'B', 8);
    tc($pdf, $C_WHITE);
    $pdf->SetXY($cx + 2, $cy + 1.5);
    $pdf->Cell(6, 7, $num . '.', 0, 0, 'C');

    // Título
    $pdf->SetFont('helvetica', 'B', 7);
    tc($pdf, $C_WHITE);
    $pdf->SetXY($cx + 9, $cy + 0.8);
    $pdf->Cell($cw - 35, 5, strtoupper($titulo), 0, 2, 'L');

    // Sub-título
    $pdf->SetFont('helvetica', '', 5.5);
    tc($pdf, [200, 230, 220]);
    $pdf->SetXY($cx + 9, $cy + 5.5);
    $pdf->Cell($cw - 35, 3.5, $subtitulo, 0, 0, 'L');

    // Pills años (pequeños, arriba derecha de la card)
    // Año1 naranja
    fc($pdf, $C_ORANGE); dc($pdf, $C_ORANGE);
    $pdf->Rect($cx + $cw - 24, $cy + 1.5, 10, 4, 'F');
    $pdf->SetFont('helvetica', 'B', 5);
    tc($pdf, $C_WHITE);
    $pdf->SetXY($cx + $cw - 24, $cy + 1.8);
    $pdf->Cell(10, 3.5, (string)$anio1, 0, 0, 'C');
    // Año2 verde
    fc($pdf, $C_GREEN); dc($pdf, $C_GREEN);
    $pdf->Rect($cx + $cw - 13, $cy + 1.5, 10, 4, 'F');
    tc($pdf, $C_WHITE);
    $pdf->SetXY($cx + $cw - 13, $cy + 1.8);
    $pdf->Cell(10, 3.5, (string)$anio2, 0, 0, 'C');

    // ── Área de barras ──────────────────────────────────────────────────────
    $n = count($cats);
    if ($n === 0) {
        return $cy + $cardH;
    }

    while (count($s1) < $n) $s1[] = 0;
    while (count($s2) < $n) $s2[] = 0;

    $allVals = array_merge(array_map('abs', $s1), array_map('abs', $s2));
    $maxVal  = max($allVals ?: [1]);
    if ($maxVal === 0) $maxVal = 1;

    $areaX  = $cx + $padding;
    $areaY  = $cy + $hdrH + $padding;
    $areaW  = $cw - $padding * 2;
    $baseY  = $areaY + $chartH - 8;   // 8mm para etiquetas de cat
    $barAreaH = $chartH - 12;

    $groupW = $areaW / $n;
    $barW   = min($groupW * 0.42, 8.5);
    $gap    = 0.5;  // gap mínimo: barras del mismo grupo quedan casi pegadas

    for ($i = 0; $i < $n; $i++) {
        $v1 = (int)$s1[$i];
        $v2 = (int)$s2[$i];

        $h1 = max(0.8, ($barAreaH * abs($v1)) / $maxVal);
        $h2 = max(0.8, ($barAreaH * abs($v2)) / $maxVal);

        $gx  = $areaX + $i * $groupW;
        $cx1 = $gx + ($groupW - $barW * 2 - $gap) / 2;
        $cx2 = $cx1 + $barW + $gap;

        // Barra año1 (naranja)
        fc($pdf, $C_ORANGE); dc($pdf, $C_ORANGE);
        $pdf->Rect($cx1, $baseY - $h1, $barW, $h1, 'F');
        // Valor año1 encima
        $pdf->SetFont('helvetica', 'B', 10);
        tc($pdf, $C_ORANGE);
        $pdf->SetXY($cx1 - 4, $baseY - $h1 - 9);
        $pdf->Cell($barW + 8, 7, (string)$v1, 0, 0, 'C');

        // Barra año2 (verde oscuro)
        fc($pdf, $C_GREEN); dc($pdf, $C_GREEN);
        $pdf->Rect($cx2, $baseY - $h2, $barW, $h2, 'F');
        // Valor año2 encima
        $pdf->SetFont('helvetica', 'B', 10);
        tc($pdf, $C_GREEN);
        $pdf->SetXY($cx2 - 4, $baseY - $h2 - 9);
        $pdf->Cell($barW + 8, 7, (string)$v2, 0, 0, 'C');

        // Etiqueta categoría
        $pdf->SetFont('helvetica', '', 5);
        tc($pdf, [80,80,80]);
        $pdf->SetXY($gx, $baseY + 0.5);
        $pdf->Cell($groupW, 4, strtoupper(substr($cats[$i], 0, 13)), 0, 0, 'C');
    }

    // ── Tabla variaciones (KPI semáforo) ────────────────────────────────────
    $kpiY    = $areaY + $chartH;
    $kpiGap  = 1.0;                          // espacio blanco entre celdas KPI
    $cellW   = ($areaW - $kpiGap * ($n - 1)) / $n;
    $cellH1  = 6;   // fila diferencia absoluta
    $cellH2  = 5;   // fila porcentaje

    for ($i = 0; $i < $n; $i++) {
        $v1   = (int)$s1[$i];
        $v2   = (int)$s2[$i];
        $diff = $v2 - $v1;
        $sign = $diff > 0 ? '+' : '';
        $pct  = ($v1 != 0) ? round(abs($diff / $v1) * 100) : 0;

        if ($diff < 0)       $bg = $C_GREEN;
        elseif ($diff === 0) $bg = $C_YELLOW;
        else                 $bg = $C_RED;

        $kx = $areaX + $i * ($cellW + $kpiGap);

        // Celda diferencia
        fc($pdf, $bg); dc($pdf, $bg);
        $pdf->Rect($kx, $kpiY, $cellW, $cellH1, 'F');
        $pdf->SetFont('helvetica', 'B', 8);
        tc($pdf, $C_WHITE);
        $pdf->SetXY($kx, $kpiY + 0.5);
        $pdf->Cell($cellW, $cellH1 - 1, $sign . $diff, 0, 0, 'C');

        // Celda porcentaje (mismo color semáforo, tono más claro)
        $bgPct = $diff < 0 ? [40,160,80] : ($diff === 0 ? [200,160,0] : [180,30,30]);
        fc($pdf, $bgPct); dc($pdf, $bgPct);
        $pdf->Rect($kx, $kpiY + $cellH1, $cellW, $cellH2, 'F');
        $pdf->SetFont('helvetica', 'B', 7);
        tc($pdf, $C_WHITE);
        $pdf->SetXY($kx, $kpiY + $cellH1 + 0.5);
        $sign2 = $diff > 0 ? '+' : '';
        $pdf->Cell($cellW, $cellH2 - 1, $sign2 . $pct . '%', 0, 0, 'C');
    }

    return $cy + $cardH;
}

// ─── Configurar TCPDF ─────────────────────────────────────────────────────────
$pdf = new TCPDF('P', 'mm', 'A4', true, 'UTF-8', false);
$pdf->SetCreator('Acción Unificada');
$pdf->SetTitle('Boletín Estratégico – Seguridad y Convivencia');
$pdf->SetMargins(0, 0, 0);
$pdf->SetAutoPageBreak(false);
$pdf->setPrintHeader(false);
$pdf->setPrintFooter(false);
$pdf->SetFont('helvetica', '', 9);

$pgW = 210;  // A4 vertical
$pgH = 297;
$mg  = 6;

$boletinFecha = trim((string)($meta['boletin_fecha'] ?? ''));
$mesesAbrev = ['','Ene','Feb','Mar','Abr','May','Jun','Jul','Ago','Sep','Oct','Nov','Dic'];
$_ts     = $boletinFecha ? strtotime($boletinFecha) : ($fechaCierre ? strtotime($fechaCierre) : time());
$_dia    = (int)date('j', $_ts);
$_mes    = (int)date('n', $_ts);
$_anio   = (int)date('Y', $_ts);
$_diaFmt = str_pad((string)$_dia, 2, '0', STR_PAD_LEFT);
$tituloBoletin = 'BOLETÍN INFORMATIVO DIRECCIÓN DE SEGURIDAD Y CONVIVENCIA CIUDADANA – COMPORTAMIENTO DELICTIVO  ' . $_diaFmt . '/' . $mesesAbrev[$_mes] . '/' . $_anio;

$dsKeys  = array_keys($datasets);
$group1  = array_slice($dsKeys, 0, 6);   // 6 gráficas página 1 (3×2)
$group2  = array_slice($dsKeys, 6, 6);   // hasta 6 gráficas página 2 (3×2)

// ─── PÁGINA 1 ─────────────────────────────────────────────────────────────────
$pdf->AddPage();

$startY = drawPageHeader($pdf, $pgW, $escudo, $gobLogo,
    $anio1, $anio2, $tituloBoletin,
    $C_WHITE, $C_DARKBLUE, $C_MIDBLUE, $C_LIME, $C_BGPAGE);

// Número de boletín
$pdf->SetFont('helvetica', 'B', 5.5);
tc($pdf, [180,220,200]);
$pdf->SetXY($mg, $startY - 4);
$boletinLabel = $boletinNo !== null ? 'BOLETÍN No. ' . $boletinNo : 'BOLETÍN No. ' . $anio2;
$pdf->Cell(30, 3, $boletinLabel, 0, 0, 'L');

// 6 cards en 2×3 (2 columnas, 3 filas)
$gapCard  = 2;
$cardW    = ($pgW - $mg * 2 - $gapCard) / 2;
$cardH    = (int)(($pgH - $startY - $mg - $gapCard * 2) / 3);
$col2p1   = [$mg, $mg + $cardW + $gapCard];
$row1     = [$startY, $startY + $cardH + $gapCard, $startY + ($cardH + $gapCard) * 2];

foreach ($group1 as $idx => $key) {
    $ds = $datasets[$key] ?? [];
    drawCard(
        $pdf,
        $col2p1[$idx % 2], $row1[(int)($idx / 2)], $cardW,
        (int)($ds['card']['card_num'] ?? ($idx + 1)),
        (string)($ds['card']['titulo']    ?? ''),
        (string)($ds['card']['subtitulo'] ?? ''),
        (array)($ds['cats']         ?? []),
        (array)($ds['serie_anio_1'] ?? []),
        (array)($ds['serie_anio_2'] ?? []),
        $anio1, $anio2,
        $C_WHITE, $C_TEAL, $C_DARKBLUE,
        $C_ORANGE, $C_GREEN, $C_RED, $C_YELLOW, $C_LGRAY, $C_BGPAGE,
        $cardH
    );
}

// ─── PÁGINA 2: gráficas restantes + Factores + Nota ──────────────────────────
$pdf->AddPage();
rect($pdf, 0, 0, $pgW, $pgH, $C_BGPAGE);

// Mini-header banda azul
rect($pdf, 0, 0, $pgW, 17, $C_DARKBLUE);
$pdf->SetFont('helvetica', 'B', 10);
tc($pdf, $C_WHITE);
$pdf->SetXY(0, 2);
$pdf->MultiCell($pgW, 6, $tituloBoletin, 0, 'C', false, 1);
$pdf->SetFont('helvetica', 'B', 5.5);
tc($pdf, [180,220,200]);
$pdf->SetXY($mg, 13);
$pdf->Cell(30, 3, $boletinLabel, 0, 0, 'L');

$startY2 = 19;

if (!empty($group2)) {
    $n2      = count($group2);
    $nCols2  = min($n2, 3);
    $cardW2  = ($pgW - $mg * 2 - $gapCard * ($nCols2 - 1)) / $nCols2;
    $cardH2  = (int)(($pgH * 0.32));
    $col2    = [];
    for ($c = 0; $c < $nCols2; $c++) {
        $col2[] = $mg + $c * ($cardW2 + $gapCard);
    }
    foreach ($group2 as $idx => $key) {
        $ds = $datasets[$key] ?? [];
        drawCard(
            $pdf,
            $col2[$idx % $nCols2],
            $startY2 + (int)($idx / $nCols2) * ($cardH2 + $gapCard),
            $cardW2,
            (int)($ds['card']['card_num'] ?? ($idx + 7)),
            (string)($ds['card']['titulo']    ?? ''),
            (string)($ds['card']['subtitulo'] ?? ''),
            (array)($ds['cats']         ?? []),
            (array)($ds['serie_anio_1'] ?? []),
            (array)($ds['serie_anio_2'] ?? []),
            $anio1, $anio2,
            $C_WHITE, $C_TEAL, $C_DARKBLUE,
            $C_ORANGE, $C_GREEN, $C_RED, $C_YELLOW, $C_LGRAY, $C_BGPAGE,
            $cardH2
        );
    }
    $nRows2 = ceil($n2 / $nCols2);
    $factY  = $startY2 + $nRows2 * ($cardH2 + $gapCard) + 3;
} else {
    $factY = $startY2;
}

// ─── Factores de Atención Gubernamental (continúa en página 2) ───────────────

// Sub-banda Factores
rect($pdf, 0, $factY, $pgW, 7, [230, 240, 230]);
dc($pdf, [180,200,180]);
$pdf->SetLineWidth(0.3);
$pdf->Rect(0, $factY, $pgW, 7, 'D');

$fechaMostrar = $boletinFecha ?: $fechaCierre;
if ($fechaMostrar) {
    fc($pdf, $C_GREEN); dc($pdf, $C_GREEN);
    $pdf->Rect($mg, $factY + 1, 38, 5, 'F');
    $pdf->SetFont('helvetica', 'B', 6);
    tc($pdf, $C_WHITE);
    $pdf->SetXY($mg, $factY + 2);
    $pdf->Cell(38, 4, 'Fecha: ' . $fechaMostrar, 0, 0, 'C');
}

$pdf->SetFont('helvetica', 'B', 8);
tc($pdf, $C_DARKBLUE);
$pdf->SetXY(0, $factY + 1);
$pdf->Cell($pgW, 5, 'FACTORES DE ATENCIÓN GUBERNAMENTAL', 0, 0, 'C');

if ($fuente) {
    fc($pdf, $C_GREEN); dc($pdf, $C_GREEN);
    $pdf->Rect($pgW - $mg - 40, $factY + 1, 40, 5, 'F');
    $pdf->SetFont('helvetica', 'B', 6);
    tc($pdf, $C_WHITE);
    $pdf->SetXY($pgW - $mg - 40, $factY + 2);
    $pdf->Cell(40, 4, 'Fuente ' . $fuente, 0, 0, 'C');
}

$factY   = $factY + 8;
$colDerW = 52;
$factW  = ($pgW - $mg * 2 - 4 - $colDerW - 3) / 3;  // 3 columnas + columna derecha
$factH  = 85;
$fkeys  = ['sicariato', 'intolerancia', 'sin_homicidios'];
$fIdx   = 0;

foreach ($fkeys as $fk) {
    if (!isset($factors[$fk])) { $fIdx++; continue; }

    $f   = $factors[$fk];
    $fx  = $mg + $fIdx * ($factW + 2);
    $val = (int)($f['valor'] ?? 0);

    // Borde exterior de la tarjeta
    dc($pdf, [150,150,150]);
    fc($pdf, [248,252,255]);
    $pdf->SetLineWidth(0.3);
    $pdf->RoundedRect($fx, $factY, $factW, $factH, 4, '1111', 'DF');

    // Número grande en el centro superior
    if ($fk === 'sin_homicidios') {
        $pdf->SetFont('helvetica', 'B', 34);
        tc($pdf, $C_DARKBLUE);
        $pdf->SetXY($fx, $factY + 4);
        $pdf->Cell($factW, 20, (string)$municipiosSinHomicidios, 0, 0, 'C');
    } else {
        $pdf->SetFont('helvetica', 'B', 34);
        tc($pdf, $C_DARKBLUE);
        $pdf->SetXY($fx, $factY + 4);
        $pdf->Cell($factW, 20, (string)$val, 0, 0, 'C');
    }

    // Título del factor
    $titulo = strip_tags(str_replace(['"', '"', '"', "'", "'"], ['"', '"', '"', "'", "'"], (string)($f['titulo_html'] ?? '')));
    $titulo = trim($titulo, '"\'');
    $pdf->SetFont('helvetica', 'B', 10);
    tc($pdf, $C_BLACK);
    $pdf->SetXY($fx + 2, $factY + 24);
    $pdf->MultiCell($factW - 4, 6, $titulo, 0, 'C', false, 1);

    // Texto de porcentaje dinámico
    if ($fk === 'sin_homicidios') {
        $pdf->SetFont('helvetica', 'B', 10);
        tc($pdf, [46, 125, 50]); // verde
        $pdf->SetXY($fx + 2, $factY + 52);
        $pdf->MultiCell($factW - 4, 6, '"Municipios de Santander sin homicidios"', 0, 'C', false, 1);
    } elseif ($totalHom > 0) {
        $label = ($fk === 'sicariato') ? 'Sicariato' : 'Intolerancia';
        $pct   = round(($val / $totalHom) * 100, 1);
        $linea1 = "De {$totalHom} homicidios en Santander, {$val} son por {$label}";
        $linea2 = "lo que equivale a un {$pct}%";
        $pdf->SetFont('helvetica', '', 9);
        tc($pdf, $C_BLACK);
        $pdf->SetXY($fx + 3, $factY + 48);
        $pdf->MultiCell($factW - 6, 5.5, $linea1, 0, 'C', false, 1);
        $pdf->SetFont('helvetica', 'B', 11);
        tc($pdf, $C_RED);
        $pdf->SetXY($fx + 3, $pdf->GetY() + 1);
        $pdf->MultiCell($factW - 6, 6, $linea2, 0, 'C', false, 1);
    }

    $fIdx++;
}

// ── Columna derecha: tasa de homicidios (ocupa todo el alto de los factores) ──
$tblX = $mg + 3 * ($factW + 2) + 2;
$tblW = $pgW - $mg - $tblX;

if ($tasaHomicidios) {
    dc($pdf, $C_DARKBLUE);
    fc($pdf, [240, 248, 255]);
    $pdf->SetLineWidth(0.4);
    $pdf->RoundedRect($tblX, $factY, $tblW, $factH, 3, '1111', 'DF');

    // Cabecera del cuadro tasa
    fc($pdf, $C_DARKBLUE); dc($pdf, $C_DARKBLUE);
    $pdf->Rect($tblX, $factY, $tblW, 12, 'F');
    $pdf->SetFont('helvetica', 'B', 8.5);
    tc($pdf, $C_WHITE);
    $pdf->SetXY($tblX + 1, $factY + 1.5);
    $pdf->MultiCell($tblW - 2, 5, 'Tasa de Homicidios x 100.000 Habitantes en Santander ' . $anio2, 0, 'C', false, 1);

    // Valor grande de tasa centrado verticalmente en la card
    $pdf->SetFont('helvetica', 'B', 30);
    tc($pdf, $C_RED);
    $pdf->SetXY($tblX, $factY + 28);
    $pdf->Cell($tblW, 20, $tasaHomicidios, 0, 0, 'C');
}

// ── Factor de Atención (desde Meta del Boletín) ──────────────────────────────
if ($notaHtml !== '') {
    $noteY = $factY + $factH + 4;
    $noteText = $notaHtml;
    $pdf->SetFont('helvetica', 'B', 11);
    $charEstimate = strlen($noteText);
    $lineEstimate = max(2, ceil($charEstimate / 85));
    $noteH = max(25, 7 + $lineEstimate * 6 + 4);
    $noteMax = $pgH - $noteY - 10;
    if ($noteH > $noteMax) $noteH = $noteMax;

    dc($pdf, $C_RED);
    fc($pdf, [255, 250, 250]);
    $pdf->SetLineWidth(0.4);
    $pdf->RoundedRect($mg, $noteY, $pgW - $mg * 2, $noteH, 3, '1111', 'DF');

    $pdf->SetFont('helvetica', 'B', 11);
    tc($pdf, $C_BLACK);
    $pdf->SetXY($mg + 4, $noteY + 3);
    $pdf->Cell(0, 7, 'Factor de atención:', 0, 2, 'L');
    $pdf->SetFont('helvetica', '', 10);
    $pdf->SetX($mg + 4);
    $pdf->MultiCell($pgW - $mg * 2 - 8, 6, $noteText, 0, 'L', false, 1);
}


// ─── Generar descarga ─────────────────────────────────────────────────────────
$fechaArchivo = $boletinFecha ? date('d-m-Y', strtotime($boletinFecha)) : ($fechaCierre ? date('d-m-Y', strtotime($fechaCierre)) : date('d-m-Y'));
$nombre = 'Boletin_Seguridad_' . $anio2 . '_' . $fechaArchivo . '.pdf';
$pdf->Output($nombre, 'D');
