<?php
require './admin/include/generic_classes.php';
include './admin/classes/ConsultasIA.php';
include './admin/classes/MainPae.php';
//require_once 'tcpdf.php';

class GobiernoSantanderAssistant {
    private $db;
    private $pdo;
    private $apiKey;
    private $nombre;
    private $textoReconocido;
    private $consultasIA;
    private $openAIClient;

    public function __construct($textoReconocido) {
        $this->db = new DbConection();
        $this->pdo = $this->db->openConect();
        $this->textoReconocido = $textoReconocido;
        $config = parse_ini_file('config.ini', true);
        $this->apiKey = $config['openai']['api_key'];
        $this->nombre = $_SESSION['session_user']['nombre'];

        $this->openAIClient = $this->initializeOpenAIClient($this->apiKey);
        // Ahora pasamos $this a ConsultasIA para que pueda acceder a los métodos de búsqueda de datos
        $this->consultasIA = new ConsultasIA($this->db, $this->pdo, $this->openAIClient, $this); 
    }

    private function initializeOpenAIClient($apiKey) {
        // Simulación de un cliente OpenAI básico para propósitos de demostración.
        // En una aplicación real, se usaría una librería cliente dedicada.
        return new class($apiKey) {
            private $apiKey;

            public function __construct($apiKey) {
                $this->apiKey = $apiKey;
            }

            public function chat(array $params) {
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, 'https://api.openai.com/v1/chat/completions');
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                curl_setopt($ch, CURLOPT_POST, 1);
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($params));
                curl_setopt($ch, CURLOPT_HTTPHEADER, [
                    'Content-Type: application/json',
                    'Authorization: Bearer ' . $this->apiKey
                ]);

                $response = curl_exec($ch);
                if (curl_errno($ch)) {
                    throw new Exception('OpenAI API Error: ' . curl_error($ch));
                }
                curl_close($ch);

                $decodedResponse = json_decode($response, true);
                if (isset($decodedResponse['error'])) {
                    throw new Exception('OpenAI API returned an error: ' . $decodedResponse['error']['message']);
                }
                return $decodedResponse;
            }
        };
    }

    private function detectReportType() {
        if (stripos($this->textoReconocido, 'secretar')) return 'secretarias';
        if (stripos($this->textoReconocido, 'proyecto')) return 'proyectos';
        if (stripos($this->textoReconocido, 'pae')) return 'pae';
        return 'general';
    }

    public function processRequest() {
        try {
            // 1. Manejo del nombre del usuario (prioritario)
            $nameResponse = $this->handleNameManagement();
            if ($nameResponse) {
                return $this->personalizarRespuesta($nameResponse); 
            }

            if ($this->isReportRequest()) {
                $reportType = $this->detectReportType();
                $this->generateReport($reportType, true);
                return $this->handleReportRequest();
            }

            // 4. Para todas las demás preguntas, usar ConsultasIA de forma inteligente
            // Esto ahora incluye la lógica para detectar preguntas específicas
            $aiResponse = $this->consultasIA->obtenerRespuestaInteligente($this->textoReconocido);
            return $this->personalizarRespuesta($this->nombre . ", " .$aiResponse);

        } catch (Exception $e) {
            error_log("Error en el asistente: " . $e->getMessage());
            return $this->personalizarRespuesta("Ocurrió un error procesando tu solicitud. Por favor intenta nuevamente.");
        }
    }

    private function handleNameManagement() {
        if (stripos($this->textoReconocido, 'olvida mi nombre') !== false) {
            unset($_SESSION['nombre_usuario']);
            return "De acuerdo, ya olvidé tu nombre. ¿Cómo te llamas?";
        }

        if (!$this->nombre) {
            $nombreDetectado = $this->detectarNombreDesdeTexto($this->textoReconocido);
            if ($nombreDetectado) {
                $this->nombre = $nombreDetectado;
                $_SESSION['nombre_usuario'] = $this->nombre;
                return "Eres muy amable, encantado de conocerte, {$this->nombre}. ¿En qué puedo ayudarte el día de hoy?";
            }
            return "Primero necesito saber cuál es su nombre. ¿Cómo te llamas?";
        }
        return null; 
    }

    private function isReportRequest() {
        return stripos($this->textoReconocido, 'reporte') !== false ||
               stripos($this->textoReconocido, 'informe') !== false;
    }

private function handleReportRequest() {
    $reportType = $this->detectReportType();
    $reportData = $this->getReportData($reportType);

    $tempFileName = 'reporte_' . $reportType . '_' . date('Ymd_His') . '.pdf';
    $tempFilePath = sys_get_temp_dir() . DIRECTORY_SEPARATOR . $tempFileName;

    $this->generatePdfFile($tempFilePath, $reportType, $reportData);

    $aiPrompt = "El usuario ha solicitado un reporte de tipo '{$reportType}'. Los datos han sido recopilados. Genera un mensaje amigable y profesional informando al usuario que su reporte está listo para descargar, y proporciona un enlace directo. Por ejemplo: '¡Excelente! Tu reporte de [tipo de reporte] está listo para descargar. Aquí tienes el enlace: [enlace]'. No incluyas el contexto de datos aquí, solo el mensaje para el usuario.";

    $messages = [
        ['role' => 'system', 'content' => "Eres un asistente virtual del gobierno de Santander que proporciona información precisa. Genera mensajes amigables y profesionales."],
        ['role' => 'user', 'content' => $aiPrompt]
    ];
    
    $response = $this->openAIClient->chat([
        'model' => 'gpt-4o',
        'messages' => $messages,
        'temperature' => 0.7,
        'max_tokens' => 200,
        'assistant_thread_id' => 'asst_jjRXq6nG1wTuWW4g6Xa6Ab4m', // Make sure this property is set
        'vector_store_id' => 'vs_684eec536f4481919ede26e60aaa5451' // Make sure this property is set
    ]);
    
    $aiMessage = $response['choices'][0]['message']['content'];
    return $this->personalizarRespuesta($aiMessage);
}

    private function generatePdfFile($filePath, $reportType, $reportData) {
        $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
        $pdf->SetCreator('Gobierno de Santander');
        $pdf->SetTitle("Reporte {$reportType}");

        $pdf->AddPage();
        $pdf->SetFont('helvetica', 'B', 16);
        $pdf->Cell(0, 15, "Reporte de " . ucfirst($reportType), 0, 1, 'C');
        $pdf->Ln(10);

        $pdf->SetFont('helvetica', '', 10);
        $html = $this->generateHtmlTable($reportData);
        $pdf->writeHTML($html, true, false, true, false, '');

        $this->addChartToPdf($pdf, $reportData);
        $pdf->Output($filePath, 'F');
    }

    private function generateReport($reportType, $directDownload = true) {
        $reportData = $this->getReportData($reportType);

        $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
        $pdf->SetCreator('Gobierno de Santander');
        $pdf->SetTitle("Reporte {$reportType}");

        $pdf->AddPage();
        $pdf->SetFont('helvetica', 'B', 16);
        $pdf->Cell(0, 15, "Reporte de " . ucfirst($reportType), 0, 1, 'C');
        $pdf->Ln(10);

        $pdf->SetFont('helvetica', '', 10);
        $html = $this->generateHtmlTable($reportData);
        $pdf->writeHTML($html, true, false, true, false, '');

        $this->addChartToPdf($pdf, $reportData);

        if ($directDownload) {
            header('Content-Type: application/pdf');
            header('Content-Disposition: attachment; filename="reporte_'.date('Ymd').'.pdf"');
            $pdf->Output('php://output', 'D'); 
            exit;
        } else {
            $pdfContent = $pdf->Output('', 'S');
            return $this->personalizarRespuesta("Reporte generado (no directo).");
        }
    }

    // Database query methods (kept as they perform direct DB lookups, not AI)
    // Estos métodos ahora serán llamados directamente por ConsultasIA
    public function getSecretarioInfo($secretaria) {
        $stmt = $this->pdo->prepare("SELECT secretario FROM ".$this->db->getTable('tbl_secretarias'). " WHERE LOWER(secretaria) LIKE ?");
        $stmt->execute(["%".strtolower($secretaria)."%"]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['secretario'] ?? null;
    }

    public function getPaeResumen($ubicacion) {
        // Normaliza el texto a minúsculas para facilitar la búsqueda
        $texto = strtolower($ubicacion);

        $codigo_municipio =  $this->obtenerCodigoMunicipio($ubicacion);
        if (!$codigo_municipio) {
            return null;
        }
        $data = MainPae::getDataMain(['codigoMunicipio' => $codigo_municipio]);

        if ($data && $data['output']['valid']) {
            return "Sedes: {$data['output']['id']}, Niños: {$data['output']['ninos_foc']}, Neveras: {$data['output']['neveras_fun']}/{$data['output']['neveras']} funcionando.";
            // return "Sedes: {$data['output']['caracterizaciones']}, Niños focalizados: {$data['output']['ninos_foc']}, Neveras: {$data['output']['neveras_fun']}/{$data['output']['neveras']} funcionando. Por otro lado, El estado de las sedes Educacativas: Tenemos en estado activas pero nuevas, tenemos {$data['output']['estado_sede_nuevo_activo']} sedes, En estado Antiguo y activas {$data['output']['estado_sede_antiguo_activo']} sedes, y las que cerraron temporalmente fueron {$data['output']['estado_sede_cierre_temporal']} sedes";
        }
        return null;
    }

    public function getProyectosInstitucionZona($institucion, $zona) {
        $stmt = $this->pdo->prepare(
            "SELECT p.nombre_proyecto
             FROM ".$this->db->getTable('tbl_proyectos')." p
             JOIN ".$this->db->getTable('tbl_secretarias')." s ON p.tbl_secretarias_id = s.id
             WHERE LOWER(s.secretaria) LIKE ? AND LOWER(p.provincia) LIKE ?
             LIMIT 10"
        );
        $stmt->execute([
            "%".strtolower($institucion)."%",
            "%".strtolower($zona)."%"
        ]);
        $proyectos = $stmt->fetchAll(PDO::FETCH_COLUMN);
        return $proyectos;
    }

    public function getProductosServiciosOld($institucion) {
        $stmt = $this->pdo->prepare(
            "SELECT producto_servicio_pdd
             FROM ".$this->db->getTable('tbl_plandesarrollo')."
             WHERE LOWER(institucion) LIKE ?"
        );
        $stmt->execute(["%".strtolower($institucion)."%"]);
        $resultados = $stmt->fetchAll(PDO::FETCH_COLUMN);
        return $resultados;
    }
    // Método para obtener productos y servicios de desarrollo
    public function getProductosServicios() {
        $stmt = $this->pdo->prepare("SELECT producto_servicio_pdd FROM ".$this->db->getTable('tbl_plandesarrollo'));
        $stmt->execute();
        $resultados = $stmt->fetchAll(PDO::FETCH_COLUMN);
        return $resultados;
    }

    public function getListadoSecretarias($institucion = null) {
        $query = "SELECT secretaria, secretario FROM ".$this->db->getTable('tbl_secretarias');
        $params = [];

        if ($institucion) {
            $query .= " WHERE LOWER(institucion) LIKE ?";
            $params = ["%".strtolower($institucion)."%"];
        }

        $query .= " LIMIT 10";
        $stmt = $this->pdo->prepare($query);
        $stmt->execute($params);
        $secretarias = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $secretarias;
    }

    // Consulta principal: estados de los proyectos agrupados por secretaría
    public function getListadoProyectoPorSecretarias() {
        $query = " 
        SELECT
            tbl_secretarias.id AS secretaria_id,
            tbl_secretarias.secretaria,

            SUM(tbl_proyectos.valor_proyecto) AS valor_proyecto_total, 
            SUM(tbl_proyectos.aporte_municipio) AS valor_municipio_total, 
            SUM(tbl_proyectos.aporte_nacion) AS valor_nacion_total, 
            SUM(tbl_proyectos.aporte_gobernacion) AS valor_departamento_total,

            COUNT(CASE WHEN tbl_proyectos.estado = 'Suspendido' THEN 1 END) AS suspendido,
            COUNT(CASE WHEN tbl_proyectos.estado = 'Terminado' THEN 1 END) AS terminado,
            COUNT(CASE WHEN tbl_proyectos.estado = 'Ejecutado' THEN 1 END) AS ejecutado,
            COUNT(CASE WHEN tbl_proyectos.estado = 'En Contrataciòn' THEN 1 END) AS en_contratacion,
            COUNT(CASE WHEN tbl_proyectos.estado = 'En Formulación' THEN 1 END) AS en_formulacion,
            COUNT(CASE WHEN tbl_proyectos.estado = 'Entregado' THEN 1 END) AS entregado,
            COUNT(CASE
                WHEN tbl_proyectos.estado NOT IN (
                    'Suspendido', 
                    'Terminado', 
                    'Ejecutado', 
                    'En Contrataciòn', 
                    'En Formulación', 
                    'Entregado'
                ) THEN 1 
            END) AS en_ejecucion
        FROM 
            " . $this->db->getTable('tbl_proyectos') . "
        INNER JOIN " . $this->db->getTable('tbl_ciudades_accion_unificada') . "  
            ON tbl_proyectos.tbl_municipio_id = tbl_ciudades_accion_unificada.codigo_muncipio
        INNER JOIN " . $this->db->getTable('tbl_secretarias') . "   
            ON tbl_proyectos.tbl_secretarias_id = tbl_secretarias.id
        GROUP BY
            tbl_secretarias.id,
            tbl_secretarias.secretaria";
        $stmt = $this->pdo->query($query);
        $secretarias = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $secretarias;
    }

    public function getFuncionarioInfo($cargo) {
        $stmt = $this->pdo->prepare(
            "SELECT nombre FROM ".$this->db->getTable('tbl_funcionarios').
            " WHERE LOWER(cargo) LIKE ?"
        );
        $stmt->execute(["%".strtolower($cargo)."%"]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['nombre'] ?? null;
    }

/*     public function getFactoresInestabilidadPorMunicipioOLD(string $municipio): ?array {
        $stmt = $this->pdo->prepare(
            "SELECT municipio, puntaje_soc, puntaje_arm, puntaje_eco, fecha
             FROM ".$this->db->getTable('tbl_ciudades_puntos')."
             WHERE LOWER(municipio) LIKE ?
             ORDER BY fecha DESC
             LIMIT 1"
        );
        $stmt->execute(["%".strtolower($municipio)."%"]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ?: null;
    } */

    public function getFactoresInestabilidadPorMunicipio(string $codigo_muncipio): ?array {
        $stmt = $this->pdo->prepare(
            "SELECT 
            tbl_ciudades_accion_unificada.municipio, 
            tbl_ingreso_informacion.valor AS total_cantidad, 
            tbl_factores.tec_pilar_id, 
            tbl_factores.tipo AS factor, 
            tbl_factores.tipo_medicion, 
            tbl_ingreso_informacion.dtcreate AS fecha_ingreso 
            FROM " . $this->db->getTable('tbl_ciudades_accion_unificada') . " 
            INNER JOIN " . $this->db->getTable('tbl_vereda') . " 
            ON tbl_ciudades_accion_unificada.codigo_muncipio = tbl_vereda.municipio_id 
            INNER JOIN " . $this->db->getTable('tbl_ingreso_informacion') . " 
            ON tbl_vereda.id = tbl_ingreso_informacion.tbl_vereda_id 
            INNER JOIN " . $this->db->getTable('tbl_factores') . " 
            ON tbl_ingreso_informacion.tbl_factor_id = tbl_factores.id
            WHERE tbl_ciudades_accion_unificada.codigo_muncipio = :codigo_muncipio
            LIMIT 5"
        );
        $stmt->execute([':codigo_muncipio' => $codigo_muncipio]);
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return !empty($result) ? $result : null;
    }

    /**
     * Obtiene el código del municipio a partir de una ubicación dada.
     * 
     * @param string $ubicacion La ubicación en formato texto.
     * @return string|null El código del municipio o null si no se encuentra.
     */
    public function obtenerCodigoMunicipio(string $ubicacion) {

        $texto = strtolower($ubicacion);

        // Busca si contiene "municipio de X" o "ciudad de X"
        if (preg_match('/municipio de ([a-záéíóúñ\s]+)/i', $texto, $match)) {
            $ubicacion = trim($match[1]);
        } elseif (preg_match('/ciudad de ([a-záéíóúñ\s]+)/i', $texto, $match)) {
            $ubicacion = trim($match[1]);
        } elseif (preg_match('/([a-záéíóúñ\s]+)/i', $texto, $match)) {
        } elseif (preg_match('/en la ciudad de ([a-záéíóúñ\s]+)/i', $texto, $match)) {
            $ubicacion = trim($match[1]);
        } elseif (preg_match('/([a-záéíóúñ\s]+)/i', $texto, $match)) {
        } elseif (preg_match('/en el municipio de ([a-záéíóúñ\s]+)/i', $texto, $match)) {
            $ubicacion = trim($match[1]);
        } elseif (preg_match('/([a-záéíóúñ\s]+)/i', $texto, $match)) {
            $ubicacion = trim($match[1]);
        }

        // Ahora $municipio tiene el valor detectado (o null si no se encontró)
        $result = Util::sb_db_get("SELECT codigo_muncipio FROM ".$this->db->getTable('tbl_ciudades_accion_unificada'). " WHERE LOWER(municipio) LIKE '$ubicacion'", false);
        if (!$result) {
            return null;
        }
        return $result[0]['codigo_muncipio'];
    }


    public function getResumenContratosProyectosSecretaria(string $secretariaNombre): ?array {
        // First, get the secretary's ID
        $stmtId = $this->pdo->prepare("SELECT id FROM ".$this->db->getTable('tbl_secretarias')." WHERE LOWER(secretaria) LIKE ? LIMIT 1");
        $stmtId->execute(["%".strtolower($secretariaNombre)."%"]);
        $secretariaId = $stmtId->fetchColumn();

        if (!$secretariaId) {
            return null; // Secretary not found
        }

        $resumen = ['secretaria' => $secretariaNombre];

        // Summary of Commitments (Compromisos)
        $stmtCompromisos = $this->pdo->prepare(
            "SELECT COUNT(*) AS total_compromisos,
                    SUM(CASE WHEN cumplimiento = 'Cumplido' THEN 1 ELSE 0 END) AS compromisos_cumplidos
             FROM ".$this->db->getTable('tbl_compromisos')."
             WHERE tbl_secretarias_id = ?"
        );
        $stmtCompromisos->execute([$secretariaId]);
        $resumen['compromisos'] = $stmtCompromisos->fetch(PDO::FETCH_ASSOC);

        // Summary of Projects
        $stmtProyectos = $this->pdo->prepare(
            "SELECT COUNT(*) AS total_proyectos,
                    SUM(valor_proyecto) AS inversion_total_proyectos,
                    GROUP_CONCAT(DISTINCT estado) AS estados_proyectos
             FROM ".$this->db->getTable('tbl_proyectos')."
             WHERE tbl_secretarias_id = ?"
        );
        $stmtProyectos->execute([$secretariaId]);
        $resumen['proyectos'] = $stmtProyectos->fetch(PDO::FETCH_ASSOC);

        return $resumen;
    }


    private function getReportData($type) {
        switch($type) {
            case 'secretarias':
                return $this->pdo->query(
                    "SELECT secretaria, secretario FROM ".
                    $this->db->getTable('tbl_secretarias')." LIMIT 20"
                )->fetchAll(PDO::FETCH_ASSOC);

            case 'proyectos':
                return $this->pdo->query(
                    "SELECT provincia, secretaria, COUNT(*) as cantidad, ".
                    "SUM(valor_proyecto) as inversion FROM ".$this->db->getTable('tbl_proyectos').
                    " GROUP BY provincia, secretaria LIMIT 20"
                )->fetchAll(PDO::FETCH_ASSOC);

            case 'pae':
                return $this->pdo->query(
                    "SELECT 
                        SUM(tbl_pae.id) AS sedes,
                        SUM(tbl_pae.ninos_focalizados) AS ninos,
                        SUM(tbl_pae.cant_neveras) AS neveras,
                        SUM(tbl_pae.neveras_funcionando) AS neveras_funcionando,
                        tbl_ciudades_accion_unificada.municipio as municipio
                    FROM " . $this->db->getTable('tbl_pae') . "
                    JOIN ". $this->db->getTable('tbl_ciudades_accion_unificada') . "
                    ON tbl_ciudades_accion_unificada.codigo_muncipio = tbl_pae.tbl_municipio_id
                    GROUP BY tbl_ciudades_accion_unificada.municipio  LIMIT 1"
                )->fetchAll(PDO::FETCH_ASSOC);

            default:
                return $this->pdo->query(
                    "SELECT 'Total Secretarías' as item, COUNT(*) as valor FROM ".$this->db->getTable('tbl_secretarias')." ".
                    "UNION SELECT 'Total Proyectos', COUNT(*) FROM ".$this->db->getTable('tbl_proyectos')." ".
                    "UNION SELECT 'Total Municipios PAE', COUNT(DISTINCT tbl_municipio_id) FROM ".$this->db->getTable('tbl_pae')
                )->fetchAll(PDO::FETCH_ASSOC);
        }
    }

    private function generateHtmlTable($data) {
        if (empty($data)) return '<p>No hay datos disponibles</p>';

        $html = '<table border="1" cellpadding="4"><tr>';
        foreach(array_keys($data[0]) as $header) {
            $html .= '<th>'.htmlspecialchars($header).'</th>';
        }
        $html .= '</tr>';

        foreach($data as $row) {
            $html .= '<tr>';
            foreach($row as $cell) {
                $html .= '<td>'.htmlspecialchars($cell).'</td>';
            }
            $html .= '</tr>';
        }

        return $html.'</table>';
    }

    private function addChartToPdf($pdf, $data) {
        if (empty($data)) return;

        $firstNumericColumn = null;
        foreach ($data[0] as $key => $value) {
            if (is_numeric($value)) {
                $firstNumericColumn = $key;
                break;
            }
        }

        if ($firstNumericColumn === null) {
            return;
        }

        $values = array_column($data, $firstNumericColumn);
        $numericValues = array_filter($values, 'is_numeric');
        if (empty($numericValues)) {
            return;
        }
        $maxValue = max($numericValues);
        if ($maxValue == 0) $maxValue = 1;

        $pdf->Ln(10);
        $pdf->SetFont('helvetica', 'B', 12);
        $pdf->Cell(0, 10, 'Resumen Gráfico', 0, 1);

        $chartWidth = 150;
        $chartHeight = 80;
        $chartX = 30;
        $chartY = $pdf->GetY();

        $barWidth = $chartWidth / count($data);
        $pdf->SetFillColor(79, 129, 189);

        foreach($data as $i => $row) {
            $barHeight = (isset($row[$firstNumericColumn]) && is_numeric($row[$firstNumericColumn])) ?
                         ($row[$firstNumericColumn] / $maxValue) * $chartHeight : 0;
            $pdf->Rect(
                $chartX + ($i * $barWidth),
                $chartY + ($chartHeight - $barHeight),
                $barWidth - 2,
                $barHeight,
                'F'
            );
        }

        $pdf->SetDrawColor(0, 0, 0);
        $pdf->Line($chartX, $chartY, $chartX, $chartY + $chartHeight);
        $pdf->Line($chartX, $chartY + $chartHeight, $chartX + $chartWidth, $chartY + $chartHeight);
    }

    private function detectarNombreDesdeTexto($texto) {
        $patrones = [
            '/(?:me llamo|mi nombre es|soy|claro que sí,? mi nombre es|claro que sí,? me llamo|claro mi nombre es|claro que mi, nombre es)\s+([a-záéíóúñ]+(?:\s+[a-záéíóúñ]+)?)/i',
            '/([a-záéíóúñ]+(?:\s+[a-záéíóúñ]+)?)\s+es mi nombre/i'
        ];

        foreach ($patrones as $patron) {
            if (preg_match($patron, $texto, $match)) {
                return ucwords(strtolower(trim($match[1])));
            }
        }
        return null;
    }

    // Modificado según tus instrucciones de personalización
    private function personalizarRespuesta($respuesta) {
        // Según la información guardada, no debo usar el nombre del usuario.
        return $respuesta;
    }
}

try {
    // Instantiate the GobiernoSantanderAssistant class
    $assistant = new GobiernoSantanderAssistant($textoReconocido);
    
    // Process the request and get the response
    $respuesta = $assistant->processRequest();
    
    // Output the response
    echo $respuesta;
    
} catch (Exception $e) {
    // Handle any errors that might occur
    $respuesta = "Error: " . $e->getMessage();
    echo $respuesta;
}
