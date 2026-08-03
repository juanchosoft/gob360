<?php

/**
 * Herramienta IA: exportación de informes a PDF con identidad gráfica institucional.
 * El HTML que compone Claude se sanitiza (whitelist de etiquetas + remoción de
 * atributos) antes de pasar por TCPDF — el estilo visual lo controla únicamente
 * el template de esta clase, nunca el contenido generado por el modelo.
 */
final class ToolReportes
{
    private const TAGS_PERMITIDAS = '<h2><h3><h4><p><table><thead><tbody><tr><th><td>'
        . '<ul><ol><li><b><strong><i><em><br><hr>';
    private const MAX_CONTENIDO = 50000;
    private const COLOR_PRIMARIO = [0x23, 0x41, 0x62]; // #234162

    /**
     * Genera un informe PDF a partir de HTML restringido y devuelve su URL de descarga.
     * Tool: generar_reporte_pdf
     */
    public static function generarReportePdf(array $input): array
    {
        $titulo    = trim((string) ($input['titulo'] ?? ''));
        $contenido = (string) ($input['contenido_html'] ?? '');

        if ($titulo === '' || trim($contenido) === '') {
            return ['error' => 'Se requieren título y contenido para generar el informe.'];
        }
        if (mb_strlen($contenido) > self::MAX_CONTENIDO) {
            $contenido = mb_substr($contenido, 0, self::MAX_CONTENIDO);
        }

        $html = self::sanitizarHtml($contenido);

        require_once __DIR__ . '/../../../../admin/include/TCPDF-main/tcpdf.php';

        $pdf = self::construirPdf($titulo, $html);

        $nombreArchivo = bin2hex(random_bytes(16)) . '.pdf';
        $directorio    = __DIR__ . '/../../../../uploads/ia_reportes';
        if (!is_dir($directorio)) {
            mkdir($directorio, 0755, true);
        }
        $rutaAbsoluta = $directorio . '/' . $nombreArchivo;
        $pdf->Output($rutaAbsoluta, 'F');

        $id  = IaReporte::crear($titulo, $nombreArchivo);
        $url = self::construirUrl($id);

        return [
            'url'     => $url,
            'titulo'  => $titulo,
            'mensaje' => "El informe se generó correctamente. Comparte esta URL con el usuario "
                       . "tal cual, como texto plano, para que pueda descargarlo: {$url}",
        ];
    }

    /**
     * Elimina etiquetas fuera de la whitelist y todos los atributos de las permitidas.
     */
    private static function sanitizarHtml(string $html): string
    {
        $html = strip_tags($html, self::TAGS_PERMITIDAS);
        // Quita atributos de toda etiqueta permitida (evita style/on* inyectados por el modelo)
        return preg_replace('/<(\/?)(\w+)[^>]*>/', '<$1$2>', $html) ?? '';
    }

    private static function construirPdf(string $titulo, string $contenidoHtml): TCPDF
    {
        $scope        = IaScope::actual();
        $alcanceTexto = self::alcanceTexto($scope);
        $fecha        = date('d/m/Y H:i');

        // TCPDF 6.10 no expone setHeaderCallback/setFooterCallback (eso es de otros forks):
        // el patrón real de esta versión es sobrescribir Header()/Footer() en una subclase.
        $pdf = new class('P', 'mm', 'A4', true, 'UTF-8', false) extends TCPDF {
            public string $tituloInforme  = '';
            public string $alcanceInforme = '';
            public string $fechaInforme   = '';

            public function Header()
            {
                ToolReportes::dibujarEncabezado($this, $this->tituloInforme, $this->alcanceInforme, $this->fechaInforme);
            }

            public function Footer()
            {
                ToolReportes::dibujarPie($this);
            }
        };
        $pdf->tituloInforme  = $titulo;
        $pdf->alcanceInforme = $alcanceTexto;
        $pdf->fechaInforme   = $fecha;

        $pdf->SetCreator('ALMA');
        $pdf->SetTitle($titulo);
        $pdf->SetMargins(12, 38, 12);
        $pdf->SetHeaderMargin(8);
        $pdf->SetFooterMargin(12);
        $pdf->SetAutoPageBreak(true, 20);
        $pdf->setPrintHeader(true);
        $pdf->setPrintFooter(true);

        $pdf->AddPage();
        $pdf->SetFont('helvetica', '', 10);

        $estilo = '<style>'
            . 'table{border-collapse:collapse;width:100%;margin-bottom:4mm;}'
            . 'th{background-color:#234162;color:#ffffff;padding:2mm;text-align:left;}'
            . 'td{padding:2mm;border-bottom:0.2mm solid #cccccc;}'
            . 'h2{color:#234162;font-size:14pt;margin-top:4mm;}'
            . 'h3{color:#234162;font-size:12pt;margin-top:3mm;}'
            . 'h4{color:#234162;font-size:11pt;}'
            . 'p{font-size:10pt;line-height:1.4;}'
            . 'ul,ol{font-size:10pt;}'
            . '</style>';

        $pdf->writeHTML($estilo . $contenidoHtml, true, false, true, false, '');

        return $pdf;
    }

    public static function dibujarEncabezado(TCPDF $pdf, string $titulo, string $alcance, string $fecha): void
    {
        $pgW    = $pdf->getPageWidth();
        $escudo = __DIR__ . '/../../../../assets/img/sandander_escudo.png';
        $logo   = __DIR__ . '/../../../../assets/img/gobernacion_logo_texto.png';

        $pdf->SetFillColor(...self::COLOR_PRIMARIO);
        $pdf->Rect(0, 0, $pgW, 30, 'F');

        if (file_exists($escudo)) {
            $pdf->Image($escudo, 12, 4, 20, 20, 'PNG');
        }
        if (file_exists($logo)) {
            $pdf->Image($logo, 34, 6, 55, 16, 'PNG');
        }

        $pdf->SetTextColor(255, 255, 255);
        $pdf->SetFont('helvetica', 'B', 9);
        $pdf->SetXY(0, 5);
        $pdf->Cell($pgW - 12, 5, 'Generado por ALMA · ' . $fecha, 0, 2, 'R');
        $pdf->SetFont('helvetica', '', 8);
        $pdf->Cell($pgW - 12, 5, $alcance, 0, 0, 'R');

        $pdf->SetFont('helvetica', 'B', 12);
        $pdf->SetXY(12, 22);
        $pdf->Cell($pgW - 24, 6, $titulo, 0, 0, 'L');

        $pdf->SetTextColor(0, 0, 0);
    }

    public static function dibujarPie(TCPDF $pdf): void
    {
        $pdf->SetY(-15);
        $pdf->SetFont('helvetica', '', 7);
        $pdf->SetTextColor(120, 120, 120);
        $pdf->Cell(0, 5, 'Generado automáticamente por ALMA — uso interno', 0, 0, 'L');
        $pdf->Cell(0, 5, 'Página ' . $pdf->getAliasNumPage() . ' de ' . $pdf->getAliasNbPages(), 0, 0, 'R');
    }

    private static function alcanceTexto(array $scope): string
    {
        if ($scope['municipio_id'] !== null) {
            return 'Alcaldía de ' . self::nombreMunicipio($scope['municipio_id']);
        }
        if ($scope['secretaria_id'] !== null && $scope['secretaria_id'] > 0) {
            return 'Secretaría de ' . self::nombreSecretaria((int) $scope['secretaria_id']);
        }
        return 'Gobernación de Santander — vista departamental';
    }

    private static function nombreMunicipio(string $codigo): string
    {
        $db  = new DbConection();
        $pdo = $db->openConect();
        $st  = $pdo->prepare("SELECT municipio FROM tbl_ciudades_accion_unificada WHERE codigo_muncipio = :c LIMIT 1");
        $st->execute([':c' => $codigo]);
        $row = $st->fetch(PDO::FETCH_ASSOC);
        $db->closeConect();
        return $row ? $row['municipio'] : $codigo;
    }

    private static function nombreSecretaria(int $id): string
    {
        $db  = new DbConection();
        $pdo = $db->openConect();
        $st  = $pdo->prepare("SELECT secretaria FROM tbl_secretarias WHERE id = :id LIMIT 1");
        $st->execute([':id' => $id]);
        $row = $st->fetch(PDO::FETCH_ASSOC);
        $db->closeConect();
        return $row ? $row['secretaria'] : "#{$id}";
    }

    private static function construirUrl(int $id): string
    {
        $https  = !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';
        $scheme = $https ? 'https' : 'http';
        $host   = $_SERVER['HTTP_HOST'] ?? 'localhost';

        // Quita el script actual bajo admin/ajax/ (puede ser cualquiera de los que llaman esta tool)
        $script   = $_SERVER['SCRIPT_NAME'] ?? '/admin/ajax/ia_chat.php';
        $basePath = preg_replace('#admin/ajax/[^/]+\.php$#', '', $script);

        return "{$scheme}://{$host}{$basePath}admin/ajax/ia_reporte_pdf.php?id={$id}";
    }
}
