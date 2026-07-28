<?php

/**
 * Class that contains all operations used on the database, now powered by AI
 * @author SPIDERSOFTWARE
 */
class ConsultasIA
{
    private $openAI;
    private $db;
    private $pdo;
    private $assistantInstance; // Referencia a GobiernoSantanderAssistant

    public function __construct($db, $pdo, $openAIClient, GobiernoSantanderAssistant $assistantInstance)
    {
        $this->db = $db;
        $this->pdo = $pdo;
        $this->openAI = $openAIClient;
        $this->assistantInstance = $assistantInstance; // Almacena la instancia del asistente
    }

    /**
     * Get context data for AI responses (now only for generic fallback)
     * Las tablas usadas aquí (tbl_secretarias, tbl_proyectos, tbl_pae, tbl_funcionarios, tbl_ingreso_informacion, tbl_vereda)
     * se asumen existentes en la base de datos completa, aunque algunas no estén en el fragmento del diccionario proporcionado.
     */
    private function getContextData()
    {
        return [
            'secretarias' => $this->getSecretariesSummary(),
            'proyectos' => $this->getProjectsSummary(),
            'pae' => $this->getPaeStatus(),
            //'funcionarios' => $this->getFuncionariosSummary(),
            'factores_generales' => $this->getFactoresData()
        ];
    }

    /**
     * Get summary of secretaries
     */
    private function getSecretariesSummary()
    {
        $query = "SELECT secretaria, secretario FROM " . $this->db->getTable('tbl_secretarias');
        $stmt = $this->pdo->query($query);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get summary of projects
     */
    private function getProjectsSummary()
    {
        $query = "SELECT 
                    tbl_proyectos.provincia, 
                    tbl_secretarias.secretaria,
                    SUM(tbl_proyectos.valor_proyecto) AS valor_total,
                    COUNT(*) AS total_proyectos,
                    GROUP_CONCAT(DISTINCT tbl_proyectos.estado) AS estados
                FROM " . $this->db->getTable('tbl_proyectos') . "
                INNER JOIN " . $this->db->getTable('tbl_secretarias') . "
                    ON tbl_proyectos.tbl_secretarias_id = tbl_secretarias.id
                GROUP BY tbl_proyectos.provincia, tbl_secretarias.secretaria";
        $stmt = $this->pdo->query($query);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get PAE status data
     */
    private function getPaeStatus()
    {
        $query = "SELECT 
            SUM(tbl_pae.id) AS sedes,
            SUM(tbl_pae.ninos_focalizados) AS ninos,
            SUM(tbl_pae.cant_neveras) AS neveras,
            SUM(tbl_pae.neveras_funcionando) AS neveras_funcionando,
            tbl_ciudades_accion_unificada.municipio as municipio
        FROM " . $this->db->getTable('tbl_pae') . "
        JOIN " . $this->db->getTable('tbl_ciudades_accion_unificada') . "
        ON tbl_ciudades_accion_unificada.codigo_muncipio = tbl_pae.tbl_municipio_id
        GROUP BY tbl_ciudades_accion_unificada.municipio";
        $stmt = $this->pdo->query($query);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get factores data (general, no específicos de inestabilidad)
     */
    private function getFactoresData()
    {
        $query = "SELECT 
                    c.municipio, 
                    f.tipo, 
                    f.tipo_medicion,
                    SUM(i.valor) AS total
                FROM " . $this->db->getTable('tbl_ingreso_informacion') . " i
                JOIN " . $this->db->getTable('tbl_factores') . " f ON i.tbl_factor_id = f.id
                JOIN " . $this->db->getTable('tbl_vereda') . " v ON i.tbl_vereda_id = v.id
                JOIN " . $this->db->getTable('tbl_ciudades_accion_unificada') . " c ON v.municipio_id = c.codigo_muncipio
                GROUP BY c.municipio, f.tipo, f.tipo_medicion";
        $stmt = $this->pdo->query($query);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get funcionarios summary
     */
    private function getFuncionariosSummary()
    {
        $query = "SELECT secretario, secretaria FROM " . $this->db->getTable('tbl_secretarias');
        $stmt = $this->pdo->query($query);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }


    /**
     * Genera la respuesta de la IA basada en el texto reconocido,
     * ahora con contexto opcional y específico.
     */
    public function obtenerRespuesta(string $preguntaUsuario, string $contextoEspecifico = ''): string
    {
        $messages = [
            ['role' => 'system', 'content' => "Eres un asistente virtual del gobierno de Santander. Responde de manera clara, concisa, amigable y profesional. Si te doy información específica, úsala para responder. Si no tienes información suficiente, indícalo educadamente."],
        ];

        if (!empty($contextoEspecifico)) {
            $messages[] = [
                'role' => 'user',
                'content' => "Dada la siguiente información: '{$contextoEspecifico}'. Responde a la pregunta del usuario: '{$preguntaUsuario}'."
            ];
        } else {
            $contextData = $this->getContextData();
            $messages[] = [
                'role' => 'user',
                'content' => "Contexto general de datos: " . json_encode($contextData, JSON_PRETTY_PRINT) . "\n\nPregunta del usuario: '{$preguntaUsuario}'."
            ];
        }

        // 🔁 Aquí recortamos los mensajes antes de enviarlos
        $messages = $this->recortarMensajes($messages, 14000); // o 12000 si usas `max_tokens` grandes

        try {
            $response = $this->openAI->chat([
                'model' => 'gpt-4o',
                'messages' => $messages,
                'temperature' => 0.7,
                'max_tokens' => 500
            ]);

            return $response['choices'][0]['message']['content'];
        } catch (Exception $e) {
            error_log("OpenAI API error: " . $e->getMessage());
            return "Lo siento, estoy teniendo dificultades para procesar tu solicitud. Por favor intenta nuevamente más tarde.";
        }
    }

    /**
     * Recorta los mensajes para que no superen el límite estimado de tokens.
     * Estimación aproximada: 1 token ≈ 4 caracteres (inglés) / 3.5 español.
     */
    private function recortarMensajes(array $messages, int $limiteTokens = 14000): array
    {
        $tokensAcumulados = 0;
        $result = [];

        // Estimación básica: 1 token ≈ 4 caracteres (puedes ajustar a 3.5 si es en español)
        $factorEstimacion = 4;

        foreach ($messages as $mensaje) {
            $tokensEstimados = intval(strlen($mensaje['content']) / $factorEstimacion);

            if (($tokensAcumulados + $tokensEstimados) > $limiteTokens) {
                // Cortar el contenido para que no supere el límite
                $tokensRestantes = $limiteTokens - $tokensAcumulados;
                $caracteresPermitidos = $tokensRestantes * $factorEstimacion;

                if ($caracteresPermitidos > 0) {
                    $mensaje['content'] = substr($mensaje['content'], 0, $caracteresPermitidos);
                    $result[] = $mensaje;
                }
                break; // Ya no se pueden añadir más mensajes
            } else {
                $tokensAcumulados += $tokensEstimados;
                $result[] = $mensaje;
            }
        }

        return $result;
    }

    /**
     * Public method para detectar el tipo de consulta y generar una respuesta apropiada.
     * Esto centraliza la lógica de "enrutamiento" y llamada a las funciones específicas.
     */
    public function obtenerRespuestaInteligente(string $textoReconocido): string
    {
        $normalizedText = self::normalizeText($textoReconocido);
        // --- Lógica para detectar intenciones y llamar a funciones específicas ---

        // 7. Factores de inestabilidad de un municipio*
        foreach ($this->getFrasesFactoresInestabilidad() as $pattern) {
            if (preg_match($pattern, $normalizedText, $matches)) {
                // Detectar nombre del municipio
                $municipio = isset($matches[1]) ? trim($matches[1]) : '';

                // Obtener código del municipio
                $codigoMunicipio = $this->assistantInstance->obtenerCodigoMunicipio($municipio);

                // Obtener datos de factores de inestabilidad
                $inestabilidadData = $this->assistantInstance->getFactoresInestabilidadPorMunicipio($codigoMunicipio);

                if (!empty($inestabilidadData)) {
                    $resumenPorFactor = [];

                    foreach ($inestabilidadData as $fila) {
                        $factor = ucfirst(strtolower(trim($fila['factor'])));
                        $cantidad = (float)$fila['total_cantidad'];
                        $fecha = $fila['fecha_ingreso'];
                        $medicion = strtolower($fila['tipo_medicion']);

                        if (!isset($resumenPorFactor[$factor])) {
                            $resumenPorFactor[$factor] = [
                                'cantidad' => $cantidad,
                                'fecha' => $fecha,
                                'medicion' => $medicion,
                            ];
                        } else {
                            $resumenPorFactor[$factor]['cantidad'] += $cantidad;
                            if (strtotime($fecha) > strtotime($resumenPorFactor[$factor]['fecha'])) {
                                $resumenPorFactor[$factor]['fecha'] = $fecha;
                            }
                        }
                    }

                    // Armar el texto plano y continuo
                    $municipioNombre = ucwords(strtolower($inestabilidadData[0]['municipio']));
                    $fechaUltima = date('j \d\e F \d\e Y', strtotime($inestabilidadData[0]['fecha_ingreso']));

                    $partes = [];
                    foreach ($resumenPorFactor as $factor => $datos) {
                        $cantidad = (int) $datos['cantidad'];
                        $unidad = $datos['medicion'];

                        if ($unidad === 'porcentaje') {
                            $parte = "ha habido un incremento del {$cantidad} por ciento en {$factor}";
                        } else {
                            $unidadTexto = $cantidad === 1 ? 'caso' : 'casos';
                            $parte = "se han registrado {$cantidad} {$unidadTexto} relacionados con {$factor}";
                        }

                        $partes[] = $parte;
                    }

                    $contexto = "Hasta el {$fechaUltima}, los factores de inestabilidad reportados para el municipio de {$municipioNombre} son los siguientes: ";
                    $contexto .= implode(", ", $partes) . ".";

                } else {
                    $contexto = "No se encontraron datos de inestabilidad para el municipio especificado.";
                }

                return $this->obtenerRespuesta($textoReconocido, $contexto);
            }
        }

        // 8. Resumen de contratos y proyectos por secretaría
        foreach ($this->getFrasesResumenProyectos() as $pattern) {
            if (preg_match($pattern, $normalizedText, $matches)) {
                $proyectosPorSecretarias = $this->assistantInstance->getListadoProyectoPorSecretarias();
                $secretariaNombreOriginal = trim($matches[1]);
                $secretariaNombre = self::normalizeText($secretariaNombreOriginal);

                $filaEncontrada = null;

                foreach ($proyectosPorSecretarias as $fila) {
                    $nombreFila = self::normalizeText($fila['secretaria']);
                    if ($nombreFila === $secretariaNombre) {
                        $filaEncontrada = $fila;
                        break;
                    }
                }

                if ($filaEncontrada) {
                    // Estados relevantes
                    $estados = [
                        'suspendido'        => 'suspendido',
                        'terminado'         => 'terminado',
                        'ejecutado'         => 'ejecutado',
                        'en_contratacion'   => 'en contratación',
                        'en_formulacion'    => 'en formulación',
                        'entregado'         => 'entregado',
                        'en_ejecucion'      => 'en ejecución',
                    ];

                    $frases = [];
                    foreach ($estados as $campo => $etiqueta) {
                        $cantidad = (int)$filaEncontrada[$campo];
                        if ($cantidad > 0) {
                            $frases[] = "{$cantidad} en estado {$etiqueta}";
                        }
                    }

                    $resumenTextual = !empty($frases)
                        ? "Para la Secretaría de {$filaEncontrada['secretaria']} hay " . implode(', ', $frases) . "."
                        : "No se encontraron proyectos activos para la Secretaría de {$filaEncontrada['secretaria']}.";

                    // Si tienes valores económicos agregados a la consulta, inclúyelos
                    $format = fn($num) => '$' . number_format((float)$num, 0, ',', '.');

                    $valores = "";
                    if (isset($filaEncontrada['valor_proyecto_total'])) {
                        $valores .= " Inversión total: " . $format($filaEncontrada['valor_proyecto_total']);
                        $valores .= " (Municipio: " . $format($filaEncontrada['valor_municipio_total']);
                        $valores .= ", Departamento: " . $format($filaEncontrada['valor_departamento_total']);
                        $valores .= ", Nación: " . $format($filaEncontrada['valor_nacion_total']) . ").";
                    }

                    $contexto = "Resumen para la Secretaría de {$filaEncontrada['secretaria']}: " . $resumenTextual . $valores;
                } else {
                    $contexto = "No se encontró información para la Secretaría de {$secretariaNombreOriginal}.";
                }

                return $this->obtenerRespuesta($textoReconocido, $contexto);
            }
        }

        // 1. Quién es el secretario de una secretaría VERSION DEV AI
        /*  if (preg_match('/(quien es el secretario|secretario de|secretaria de) (la )?secretaria de (.*)/', $normalizedText, $matches)) {
            $secretaria = trim($matches[3]);
            $secretario = $this->assistantInstance->getSecretarioInfo($secretaria); 
            $contexto = $secretario ? "El secretario de la Secretaría de {$secretaria} es {$secretario}." : "No se encontró información del secretario para la Secretaría de {$secretaria}.";
            return $this->obtenerRespuesta($textoReconocido, $contexto);
        } */

        // 1. Quién es el secretario de una secretaría
        foreach ($this->getFrasesSecretarioSecretaria() as $pattern) {
            if (preg_match($pattern, $normalizedText, $matches)) {
                $secretaria = trim($matches[1]); // ahora es $matches[1] porque tu regex solo tiene un grupo (.*)
                $secretario = $this->assistantInstance->getSecretarioInfo($secretaria);
                $contexto = $secretario
                    ? "El secretario de la Secretaría de {$secretaria} es {$secretario}."
                    : "No se encontró información del secretario para la Secretaría de {$secretaria}.";
                return $this->obtenerRespuesta($textoReconocido, $contexto);
            }
        }

        // 2. Resumen o estado del PAE   VERSION DEV AI
        /*         if (preg_match('/(resumen|estado|informacion) del pae (.*)/', $normalizedText, $matches)) {
            $ubicacion = trim($matches[2]);
            $paeResumen = $this->assistantInstance->getPaeResumen($ubicacion); 
            $contexto = $paeResumen ? "Resumen PAE para {$ubicacion}: {$paeResumen}" : "No se encontraron datos del PAE para {$ubicacion}.";
            return $this->obtenerRespuesta($textoReconocido, $contexto);
        } */

        // 2. Resumen o estado del PAE
        foreach ($this->getFrasesResumenPAE() as $pattern) {
            if (preg_match($pattern, $normalizedText, $matches)) {
                $ubicacion = trim($matches[1]); // NOTA: aquí se usa $matches[1] porque el patrón captura solo un grupo
                $paeResumen = $this->assistantInstance->getPaeResumen($ubicacion);
                $contexto = $paeResumen ? "Resumen PAE para {$ubicacion}: {$paeResumen}" : "No se encontraron datos del PAE para {$ubicacion}.";
                return $this->obtenerRespuesta($textoReconocido, $contexto);
            }
        }


        // 3. Proyectos por institución y zona
        if (preg_match('/(proyectos|cuales proyectos|que proyectos tiene) (.*) en (la )?(zona|provincia) de (.*)/', $normalizedText, $matches)) {
            $institucion = trim($matches[2]);
            $zona = trim($matches[5]);
            $proyectos = $this->assistantInstance->getProyectosInstitucionZona($institucion, $zona);
            $total = count($proyectos);
            $contexto = "";
            if ($total > 0) {
                $contexto = "La institución {$institucion} tiene {$total} proyectos en {$zona}. Los proyectos son: " . implode(", ", $proyectos);
                if ($total >= 10) {
                    $contexto .= ", entre otros.";
                }
            } else {
                $contexto = "No se encontraron proyectos para {$institucion} en {$zona}.";
            }
            return $this->obtenerRespuesta($textoReconocido, $contexto);
        }

        // 4. Productos y servicios de una institución
        /* if (preg_match('/(productos y servicios|que ofrece|que servicios ofrece|servicios) (.*)/', $normalizedText, $matches)) {
            $institucion = trim($matches[2]);
            $productos = $this->assistantInstance->getProductosServicios($institucion);
            $contexto = "";
            if ($productos) {
                $total = count($productos);
                $muestra = array_slice($productos, 0, 5);
                $contexto = "La institución {$institucion} ofrece {$total} productos o servicios. Algunos de ellos son: " . implode(", ", $muestra);
                if ($total > 5) {
                    $contexto .= ", entre otros.";
                }
            } else {
                $contexto = "No se encontraron productos o servicios registrados para {$institucion}.";
            }
            return $this->obtenerRespuesta($textoReconocido, $contexto);
        } */

        // 4. Productos y servicios de una institución
        foreach ($this->getFrasesProductosYServicios() as $pattern) {
            if (preg_match($pattern, $normalizedText, $matches)) {
                $productos = $this->assistantInstance->getProductosServicios();
                $contexto = "";
                if ($productos) {
                    $total = count($productos);
                    $muestra = array_slice($productos, 0, 5);
                    $contexto = "La gobernación actualmente ofrece {$total} productos o servicios en el plan de desarrollo. Algunos de ellos son: " . implode(", ", $muestra);
                    if ($total > 5) {
                        $contexto .= ", entre otros.";
                    }
                } else {
                    $contexto = "No se encontraron productos o servicios registrados para la gobernación.";
                }
                return $this->obtenerRespuesta($textoReconocido, $contexto);
            }
        }

        // 5. Listado de secretarías  VERSION DEV AI
        /*         if (preg_match('/(listado de secretarias|listado de todas las secretarias)/', $normalizedText, $matches)) {
            $secretarias = $this->assistantInstance->getListadoSecretarias();
            $contexto = "";
            if ($secretarias) {
                $listado = array_map(function($item) {
                    return "{$item['secretaria']}: {$item['secretario']}";
                }, $secretarias);
                $contexto = "Listado de secretarías: ".implode("; ", $listado);
            } else {
                $contexto = "No se encontraron secretarías registradas.";
            }
            return $this->obtenerRespuesta($textoReconocido, $contexto);
        } */

        // 5. Listado de secretarías
        foreach ($this->getFrasesListadoSecretarias() as $pattern) {
            $secretarias = $this->assistantInstance->getListadoSecretarias();
            $contexto = "";
            if ($secretarias) {
                $listado = array_map(function ($item) {
                    return "{$item['secretaria']}: {$item['secretario']}";
                }, $secretarias);
                $contexto = "Listado de secretarías: " . implode("; ", $listado);
            } else {
                $contexto = "No se encontraron secretarías registradas.";
            }
            return $this->obtenerRespuesta($textoReconocido, $contexto);
        }


        // 6. Información de un funcionario por cargo
        if (preg_match('/(quien es el|quien es la|informacion de) (.*)/', $normalizedText, $matches)) {
            $cargo = trim($matches[2]);
            $nombreFuncionario = $this->assistantInstance->getFuncionarioInfo($cargo);
            $contexto = $nombreFuncionario ? "El/La {$cargo} es {$nombreFuncionario}." : "No se encontró información sobre el cargo {$cargo}.";
            return $this->obtenerRespuesta($textoReconocido, $contexto);
        }

        // 7. Factores de inestabilidad de un municipio**  OLD
        /*         if (preg_match('/(factores de inestabilidad|indice de inestabilidad|niveles de inestabilidad|inestabilidad) de (el |la )?municipio de (.*)/', $normalizedText, $matches)) {
            $municipio = trim($matches[3]);
            $inestabilidadData = $this->assistantInstance->getFactoresInestabilidadPorMunicipio($municipio);
            $contexto = "";
            if ($inestabilidadData) {
                $contexto = "Para el municipio de {$inestabilidadData['municipio']} (datos al {$inestabilidadData['fecha']}): ";
                $contexto .= "Puntaje Social: {$inestabilidadData['puntaje_soc']}, ";
                $contexto .= "Puntaje Armado: {$inestabilidadData['puntaje_arm']}, ";
                $contexto .= "Puntaje Económico: {$inestabilidadData['puntaje_eco']}.";
            } else {
                $contexto = "No se encontraron datos de inestabilidad para el municipio de {$municipio}.";
            }
            return $this->obtenerRespuesta($textoReconocido, $contexto);
        } */

        // 8. Resumen de contratos y proyectos por secretaría** VERSION DEV AI
        /* if (preg_match('/(resumen de contratos y proyectos|contratos y proyectos) de la (secretaria|secretaría) de (.*)/', $normalizedText, $matches)) {
            $secretariaNombre = trim($matches[3]);
            $resumenData = $this->assistantInstance->getResumenContratosProyectosSecretaria($secretariaNombre);
            $contexto = "";
            if ($resumenData) {
                $contexto = "Resumen para la Secretaría de {$resumenData['secretaria']}: ";
                $contexto .= "Compromisos: Total {$resumenData['compromisos']['total_compromisos']} (Cumplidos: {$resumenData['compromisos']['compromisos_cumplidos']}). ";
                $contexto .= "Proyectos: Total {$resumenData['proyectos']['total_proyectos']} (Inversión Total: {$resumenData['proyectos']['inversion_total_proyectos']}), Estados: {$resumenData['proyectos']['estados_proyectos']}.";
            } else {
                $contexto = "No se encontró información de contratos y proyectos para la Secretaría de {$secretariaNombre}.";
            }
            return $this->obtenerRespuesta($textoReconocido, $contexto);
        } */

        // Si no se detectó ninguna intención específica, usamos el método general de ConsultasIA
        return $this->obtenerRespuesta($textoReconocido);
    }

    /**
     * Normaliza el texto para facilitar la comparación y búsqueda.
     * Convierte a minúsculas, elimina acentos y caracteres especiales,
     * y reemplaza múltiples espacios por uno solo.
     */
    public static function normalizeText($text)
    {
        $text = strtolower($text);
        // Eliminar acentos y caracteres especiales para la comparación de patrones
        $text = iconv('UTF-8', 'ASCII//TRANSLIT', $text);
        $text = preg_replace('/[^a-z0-9\s]/', '', $text);
        // Reemplazar múltiples espacios por uno solo
        $text = preg_replace('/\s+/', ' ', $text);
        return $text;
    }

    /**
     * Get frases para detectar el resumen del PAE
     * Estas frases se usan para detectar si el usuario está preguntando por el PAE en una ubicación específica.
     *
     * Preguntas de ejemplo:
     * "Dame un resumen del PAE en Bucaramanga."
     * "Dame el resumen del PAE en Bucaramanga."
     * "Estado del PAE en Barrancabermeja."
     * "¿Cómo va el PAE en San Gil?"
     */
    private function getFrasesResumenPAE(): array
    {
        return [
            '/resumen del pae en (.*)/i',
            '/dame un reumen del pae en (.*)/i',
            '/dame un resumen del pae de (.*)/i',
            '/dame un resumen del pae en el (.*)/i',
            '/ahora un resumen del pae en el (.*)/i',
            '/ahora un resumen de pae (.*)/i',
            '/estado del pae en (.*)/i',
            '/informacion del pae en (.*)/i',
            '/reporte pae en (.*)/i',
            '/detalles del pae en (.*)/i',
            '/como va el pae en (.*)/i',
        ];
    }

    /**
     * Get frases para detectar el listado de secretarías
     * Estas frases se usan para detectar si el usuario está pidiendo un listado de secretarías.
     * 
     * Preguntas de ejemplo:
     * "Dame un listado de las secretarías."
     * "¿Cuáles son las secretarías disponibles?"
     * "Muéstrame el listado de secretarías."
     * "¿Qué secretarías hay?"  
     * "¿Cuáles son todas las secretarías?"
     * "Dime el listado de secretarías."
     */
    private function getFrasesListadoSecretarias(): array
    {
        return [
            '/cuáles son todas las secretarías/i',
            '/dime el listado de secretarías/i',
            '/qué secretarías hay/i',
            '/que secretarias hay/i',
            '/dame un listado de las secretarias/i',
            '/dame un listado de secretarias/i',
            '/dame el listado de secretarias/i',
            '/podrías darme un listado de secretarias/i',
            '/listado de secretarias/i',
            '/dime el listado de secretarias/i',
            '/lista de secretarias/i',
            '/cuales son las secretarias/i',
            '/cuales son las secretarias de la gobernacion/i',
            '/cuales son las secretarias de la gobernacion/i',
            '/cuantas secretarias tiene la gobernacion/i',
            '/cuales son todas las secretarias/i',
            '/mostrar todas las secretarias/i',
            '/secretarias disponibles/i',
        ];
    }

    /*     * Get frases para detectar el resumen de contratos y proyectos de una secretaría
     * Estas frases se usan para detectar si el usuario está preguntando por un resumen proyectos de una secretaría específica.
     *
     * Preguntas de ejemplo:
     * "Dame un resumen de los proyectos de la Secretaría de Educación."
     * "¿Cuáles son los proyectos de la Secretaría de Salud?"
     * "Dime un resumen de proyectos de la Secretaría de Cultura."
     * Numero de proyectos de la secretaria de Infraestructura.
     * "¿Cuántos proyectos tiene la Secretaría de Infraestructura?"
     * 
     * url http://localhost:3000/Github/Gobernacion/santanderunificada/secretaria.php?depto_id=21&secretaria=7
     */
    private function getFrasesResumenProyectos(): array
    {
        return [
            '/proyectos de la secretaria de (.*)/i',
            '/numero de proyectos de la secretaria de (.*)/i',
            '/cuanntos de proyectos tiene de la secretaria de (.*)/i',
            '/informacion de proyectos y en la secretaria de (.*)/i',
            '/dame informacion de proyectos y en la secretaria de (.*)/i',
            '/cuales son los proyectos de la secretaria de (.*)/i',
            '/detalles de proyectos de la secretaria de (.*)/i',
            '/dime un resumen de los proyectos de la secretaria de (.*)/i',
            '/dime un resumen de proyectos de la secretaria de (.*)/i',
            '/dame un resumen proyectos de la secretaria de (.*)/i',
            '/dame un resumen de los proyectos de la secretaria de (.*)/i',
            '/dime el estado de los proyectos de la secretaria de (.*)/i',
            '/dame el estado de los proyectos de la secretaria de (.*)/i',
            '/resumen de  los proyectos de la secretaria de (.*)/i',
            '/proyectos de la secretaria de (.*)/i',
            '/estado de proyectos de la secretaria de (.*)/i',
        ];
    }


    /**
     * Get frases para detectar quién es el secretario de una secretaría
     * Estas frases se usan para detectar si el usuario está preguntando por el secretario de una secretaría específica.
     *
     * Preguntas de ejemplo:
     * "¿Quién es el secretario de la Secretaría de Educación?"
     * "Dime quién es el secretario de la Secretaría de Salud."
     * "¿Quién dirige la Secretaría de Cultura?"
     * "¿Quién está a cargo de la Secretaría de Infraestructura?"
     */
    private function getFrasesSecretarioSecretaria(): array
    {
        return [
            '/como se llama el secretaria de (.*)/i',
            '/cual es el secretaria de (.*)/i',
            '/cual es el secretario de secretaria (.*)/i',
            '/cual es el secretario de la secretaria (.*)/i',
            '/cual es el nombre del secretario de (.*)/i',
            '/cual es el nombre del secretario de la secretaria (.*)/i',
            '/cual es el nombre del secretario de la secretaria (.*)/i',
            '/cual es el secretaria de (.*)/i',
            '/dime quien es el secretario de la secretaria de (.*)/i',
            '/dime quien es el secretario de la secretaria de (.*)/i',
            '/sabes quien es el secretario de la secretaria de (.*)/i',
            '/cual quien es el secretario de la secretaria de (.*)/i',
            '/quien es el secretario de la secretaria de (.*)/i',
            '/quien es la secretaria de (.*)/i',
            '/quien está a cargo de la secretaria de (.*)/i',
            '/secretario de la secretaria de (.*)/i',
            '/cual es el secretario de la secretaria de (.*)/i',
            '/secretaria de (.*)/i',
            '/quien dirige la secretaria de (.*)/i',
            '/quien dirige la secretaria de (.*)/i',
            '/dime quien dirige la secretaria de (.*)/i',
            '/quien esta a cargo de la secretaria de (.*)/i',
            '/dime quien esta a cargo de la secretaria de (.*)/i',
        ];
    }

    /**
     * Get frases para detectar productos y servicios de la gobernación
     * Estas frases se usan para detectar si el usuario está preguntando por los productos y servicios que ofrece la gobernación.
     *
     * Preguntas de ejemplo:
     * "¿Qué productos y servicios ofrece la gobernación?"
     * "Dime los productos y servicios de la gobernación."
     * "¿Cuáles son los servicios que ofrece la gobernación?"
     * "¿Qué productos tiene la gobernación?"
     */
    private function getFrasesProductosYServicios(): array
    {
        return [
            '/productos y servicios que ofrece',
            '/productos y servicios de la gobernacion',
            '/productos y los servicios que ofrece',
            '/productos y servicios que ofrece',
            '/servicios ofrece',
        ];
    }

    /**
     * Get frases para detectar factores de inestabilidad
     * Estas frases se usan para detectar si el usuario está preguntando por los factores de inestabilidad de un municipio.
     *
     * Preguntas de ejemplo:
     * "Dime los factores de inestabilidad del municipio de Bucaramanga."
     * "Factores de inestabilidad en Barrancabermeja."
     * "¿Cuáles son los factores de inestabilidad en San Gil?"
     * "Índice de inestabilidad en Floridablanca."
     */
    private function getFrasesFactoresInestabilidad(): array
    {
        return [
            '/dime los factores de inestabilidad (.*)/i',
            '/factores de inestabilidad (.*)/i',
            '/cuales son los factores de inestabilidad (.*)/i',
            '/indice de inestabilidad (.*)/i',
            '/niveles de inestabilidad (.*)/i',
            '/inestabilidad (.*)/i',
        ];
    }
}
