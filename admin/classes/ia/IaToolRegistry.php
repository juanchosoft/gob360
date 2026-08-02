<?php

/**
 * Catálogo de herramientas IA con doble control de permisos.
 *
 * Principios:
 * 1. Solo se anuncian a Claude las tools que el usuario PUEDE usar (por RBAC).
 * 2. Al ejecutar, se revalida el permiso (fail-closed).
 * 3. Cada ejecución queda registrada en tbl_ia_tool_logs.
 */
final class IaToolRegistry
{
    /**
     * Define todas las herramientas disponibles.
     * Estructura de cada entry:
     *   'definicion' → array para el parámetro `tools` de la API (nombre, descripción, inputSchema)
     *   'permisos'   → string o string[] (OR lógico entre ellos; '' = solo sesión activa)
     *   'ejecutor'   → callable que recibe array $input y devuelve array
     *
     * @return array<string, array{definicion: array, permisos: string|string[], ejecutor: callable}>
     */
    public static function todas(): array
    {
        return [

            // ── Maestros ─────────────────────────────────────────────────────
            'buscar_municipio' => [
                'definicion' => [
                    'name'        => 'buscar_municipio',
                    'description' => 'Busca municipios del departamento por nombre (búsqueda parcial). '
                                   . 'Úsala primero cuando el usuario mencione el nombre de un municipio '
                                   . 'para obtener su código DANE.',
                    'input_schema' => [
                        'type'       => 'object',
                        'properties' => [
                            'nombre' => [
                                'type'        => 'string',
                                'description' => 'Nombre o parte del nombre del municipio (ej. "Bucaram", "Girón")',
                            ],
                        ],
                        'required' => ['nombre'],
                    ],
                ],
                'permisos' => '',
                'ejecutor' => static fn(array $input) => ToolMaestros::buscarMunicipio($input),
            ],

            'listar_secretarias' => [
                'definicion' => [
                    'name'        => 'listar_secretarias',
                    'description' => 'Lista todas las secretarías del departamento con sus IDs. '
                                   . 'Úsala cuando el usuario pregunte por una secretaría específica.',
                    'input_schema' => [
                        'type'       => 'object',
                        'properties' => new \stdClass(), // sin parámetros
                        'required'   => [],
                    ],
                ],
                'permisos' => '',
                'ejecutor' => static fn(array $input) => ToolMaestros::listarSecretarias($input),
            ],

            // ── Compromisos ──────────────────────────────────────────────────
            'consultar_compromisos' => [
                'definicion' => [
                    'name'        => 'consultar_compromisos',
                    'description' => 'Consulta compromisos del gobernador con municipios y secretarías. '
                                   . 'Devuelve estado (Cumplido/En Trámite/Sin Cumplir), fecha y secretaría responsable. '
                                   . 'Los datos ya vienen filtrados según tu perfil de acceso.',
                    'input_schema' => [
                        'type'       => 'object',
                        'properties' => [
                            'municipio_codigo' => [
                                'type'        => 'string',
                                'description' => 'Código DANE del municipio (usar buscar_municipio si solo tienes el nombre)',
                            ],
                            'estado' => [
                                'type'        => 'string',
                                'enum'        => ['Cumplido', 'En Trámite', 'Sin Cumplir'],
                                'description' => 'Filtrar por estado del compromiso',
                            ],
                            'secretaria_id' => [
                                'type'        => 'integer',
                                'description' => 'ID de la secretaría responsable (usar listar_secretarias para obtenerlo)',
                            ],
                            'limite' => [
                                'type'        => 'integer',
                                'description' => 'Máximo de registros a devolver (por defecto 50, máx 100)',
                            ],
                        ],
                        'required' => [],
                    ],
                ],
                'permisos' => ['compromisos.gobernador.view', 'compromisos.alcalde.view'],
                'ejecutor' => static fn(array $input) => ToolCompromisos::consultarCompromisos($input),
            ],

            'consultar_compromisos_alcalde' => [
                'definicion' => [
                    'name'        => 'consultar_compromisos_alcalde',
                    'description' => 'Consulta compromisos específicos de la alcaldía. '
                                   . 'Filtra automáticamente por el municipio de tu perfil.',
                    'input_schema' => [
                        'type'       => 'object',
                        'properties' => [
                            'municipio_codigo' => [
                                'type'        => 'string',
                                'description' => 'Código DANE del municipio (solo aplica para roles con acceso total)',
                            ],
                            'estado' => [
                                'type'        => 'string',
                                'enum'        => ['Cumplido', 'En Trámite', 'Sin Cumplir'],
                            ],
                            'limite' => ['type' => 'integer'],
                        ],
                        'required' => [],
                    ],
                ],
                'permisos' => 'compromisos.alcalde.view',
                'ejecutor' => static fn(array $input) => ToolCompromisos::consultarCompromisosAlcalde($input),
            ],

            // ── Dashboard ─────────────────────────────────────────────────────
            'resumen_dashboard' => [
                'definicion' => [
                    'name'        => 'resumen_dashboard',
                    'description' => 'Obtiene los indicadores clave del dashboard: totales de compromisos, '
                                   . 'proyectos, visitas y municipios según el perfil del usuario.',
                    'input_schema' => [
                        'type'       => 'object',
                        'properties' => new \stdClass(),
                        'required'   => [],
                    ],
                ],
                'permisos' => '',
                'ejecutor' => static fn(array $input) => ToolDashboard::resumenDashboard($input),
            ],

            // ── Visitas ──────────────────────────────────────────────────────
            'consultar_visitas' => [
                'definicion' => [
                    'name'        => 'consultar_visitas',
                    'description' => 'Consulta visitas municipales del gobernador. '
                                   . 'Filtra por municipio, tipo de visita y año. '
                                   . 'Los datos se restringen según tu perfil de acceso.',
                    'input_schema' => [
                        'type'       => 'object',
                        'properties' => [
                            'municipio_codigo' => ['type' => 'string', 'description' => 'Código DANE del municipio'],
                            'tipo_visita'      => ['type' => 'string', 'description' => 'Tipo de visita (ej. Gobernador, Secretario)'],
                            'anio'             => ['type' => 'integer', 'description' => 'Año a consultar (ej. 2025)'],
                            'limite'           => ['type' => 'integer'],
                        ],
                        'required' => [],
                    ],
                ],
                'permisos' => ['visitas.gobernador.view', 'compromisos.alcalde.view'],
                'ejecutor' => static fn(array $input) => ToolVisitas::consultarVisitas($input),
            ],

            'resumen_visitas_por_provincia' => [
                'definicion' => [
                    'name'        => 'resumen_visitas_por_provincia',
                    'description' => 'Resumen de visitas agrupadas por provincia para un año dado. '
                                   . 'Útil para comparar cobertura territorial.',
                    'input_schema' => [
                        'type'       => 'object',
                        'properties' => [
                            'anio' => ['type' => 'integer', 'description' => 'Año a consultar (default: año actual)'],
                        ],
                        'required' => [],
                    ],
                ],
                'permisos' => 'visitas.gobernador.view',
                'ejecutor' => static fn(array $input) => ToolVisitas::resumenVisitasPorProvincia($input),
            ],

            // ── Proyectos ────────────────────────────────────────────────────
            'consultar_proyectos_secretarias' => [
                'definicion' => [
                    'name'        => 'consultar_proyectos_secretarias',
                    'description' => 'Consulta proyectos de secretarías departamentales con inversión y avance. '
                                   . 'Filtra por secretaría y estado.',
                    'input_schema' => [
                        'type'       => 'object',
                        'properties' => [
                            'secretaria_id' => ['type' => 'integer', 'description' => 'ID de la secretaría'],
                            'estado'        => ['type' => 'string', 'description' => 'Estado del proyecto'],
                            'limite'        => ['type' => 'integer'],
                        ],
                        'required' => [],
                    ],
                ],
                'permisos' => ['secretarias.proyectos.view', 'proyectos.alcaldias.view'],
                'ejecutor' => static fn(array $input) => ToolProyectos::consultarProyectosSecretarias($input),
            ],

            'consultar_proyectos_alcaldias' => [
                'definicion' => [
                    'name'        => 'consultar_proyectos_alcaldias',
                    'description' => 'Consulta proyectos de alcaldías con inversión. '
                                   . 'Para alcaldes filtra automáticamente por su municipio.',
                    'input_schema' => [
                        'type'       => 'object',
                        'properties' => [
                            'municipio_codigo' => ['type' => 'string', 'description' => 'Código DANE del municipio'],
                            'estado'           => ['type' => 'string'],
                            'limite'           => ['type' => 'integer'],
                        ],
                        'required' => [],
                    ],
                ],
                'permisos' => 'proyectos.alcaldias.view',
                'ejecutor' => static fn(array $input) => ToolProyectos::consultarProyectosAlcaldias($input),
            ],

            // ── PAE ──────────────────────────────────────────────────────────
            'consultar_pae' => [
                'definicion' => [
                    'name'        => 'consultar_pae',
                    'description' => 'Consulta el Programa de Alimentación Escolar (PAE): '
                                   . 'sedes, niños focalizados, acceso a agua, electricidad y comedores. '
                                   . 'Agrupa por municipio.',
                    'input_schema' => [
                        'type'       => 'object',
                        'properties' => [
                            'municipio_codigo' => ['type' => 'string'],
                            'anio'             => ['type' => 'integer'],
                            'limite'           => ['type' => 'integer'],
                        ],
                        'required' => [],
                    ],
                ],
                'permisos' => 'secretarias.pae.view',
                'ejecutor' => static fn(array $input) => ToolPae::consultarPae($input),
            ],

            // ── Hacienda ─────────────────────────────────────────────────────
            'consultar_hacienda' => [
                'definicion' => [
                    'name'        => 'consultar_hacienda',
                    'description' => 'Consulta datos de hacienda: recaudo, valor tramitado y operatividad fiscal '
                                   . 'por municipio y tipo de acción.',
                    'input_schema' => [
                        'type'       => 'object',
                        'properties' => [
                            'municipio_codigo' => ['type' => 'string'],
                            'tipo'             => ['type' => 'string', 'description' => 'Tipo de acción fiscal'],
                            'anio'             => ['type' => 'integer'],
                            'limite'           => ['type' => 'integer'],
                        ],
                        'required' => [],
                    ],
                ],
                'permisos' => 'secretarias.hacienda.view',
                'ejecutor' => static fn(array $input) => ToolHacienda::consultarHacienda($input),
            ],

            // ── Factores de inestabilidad ─────────────────────────────────────
            'consultar_factores_municipio' => [
                'definicion' => [
                    'name'        => 'consultar_factores_municipio',
                    'description' => 'Consulta los factores de inestabilidad de un municipio: '
                                   . 'puntaje actual vs inicial por categoría (armado, social, económico). '
                                   . 'Requiere código de municipio.',
                    'input_schema' => [
                        'type'       => 'object',
                        'properties' => [
                            'municipio_codigo' => ['type' => 'string', 'description' => 'Código DANE del municipio (obligatorio)'],
                        ],
                        'required' => ['municipio_codigo'],
                    ],
                ],
                'permisos' => '',
                'ejecutor' => static fn(array $input) => ToolFactores::consultarFactoresMunicipio($input),
            ],

            'consultar_puntajes_departamento' => [
                'definicion' => [
                    'name'        => 'consultar_puntajes_departamento',
                    'description' => 'Ranking de municipios por puntaje de inestabilidad territorial a nivel departamental. '
                                   . 'Solo disponible para admin y gobernador.',
                    'input_schema' => [
                        'type'       => 'object',
                        'properties' => new \stdClass(),
                        'required'   => [],
                    ],
                ],
                'permisos' => '',
                'ejecutor' => static fn(array $input) => ToolFactores::consultarPuntajesDepartamento($input),
            ],

            // ── Plan de Desarrollo ────────────────────────────────────────────
            'consultar_plan_desarrollo' => [
                'definicion' => [
                    'name'        => 'consultar_plan_desarrollo',
                    'description' => 'Consulta metas del Plan de Desarrollo departamental: '
                                   . 'eje estratégico, sector, meta, avance por año y secretaría responsable.',
                    'input_schema' => [
                        'type'       => 'object',
                        'properties' => [
                            'secretaria_id'   => ['type' => 'integer'],
                            'eje_estrategico' => ['type' => 'string', 'description' => 'Parte del nombre del eje estratégico'],
                            'anio'            => ['type' => 'string', 'enum' => ['2024', '2025', '2026', '2027']],
                            'limite'          => ['type' => 'integer'],
                        ],
                        'required' => [],
                    ],
                ],
                'permisos' => 'plan_desarrollo.view',
                'ejecutor' => static fn(array $input) => ToolDesarrollo::consultarPlanDesarrollo($input),
            ],

            // ── Gestión Social ───────────────────────────────────────────────
            'consultar_gestion_social' => [
                'definicion' => [
                    'name'        => 'consultar_gestion_social',
                    'description' => 'Consulta actividades de la Gestora Social (ASPAS): '
                                   . 'tipo de acción, descripción, municipio y población impactada.',
                    'input_schema' => [
                        'type'       => 'object',
                        'properties' => [
                            'municipio_codigo' => ['type' => 'string'],
                            'anio'             => ['type' => 'integer'],
                            'limite'           => ['type' => 'integer'],
                        ],
                        'required' => [],
                    ],
                ],
                'permisos' => 'gestion_social.view',
                'ejecutor' => static fn(array $input) => ToolGestionSocial::consultarGestionSocial($input),
            ],

            // ── Estadísticas globales ────────────────────────────────────────
            'estadisticas_sistema' => [
                'definicion' => [
                    'name'        => 'estadisticas_sistema',
                    'description' => 'Devuelve estadísticas globales del sistema: '
                                   . 'total de compromisos, proyectos, visitas, PAE y gestión social del año actual. '
                                   . 'Se adapta al perfil del usuario.',
                    'input_schema' => [
                        'type'       => 'object',
                        'properties' => new \stdClass(),
                        'required'   => [],
                    ],
                ],
                'permisos' => '',
                'ejecutor' => static fn(array $input) => ToolEstadisticas::estadisticasSistema($input),
            ],

            // ── Reportes ─────────────────────────────────────────────────────
            'generar_reporte_pdf' => [
                'definicion' => [
                    'name'        => 'generar_reporte_pdf',
                    'description' => 'Genera un informe en PDF con la identidad gráfica institucional a '
                                   . 'partir de datos ya consultados en la conversación. Úsala SOLO cuando el '
                                   . 'usuario pida explícitamente un informe/reporte en PDF o para descargar. '
                                   . 'Antes de llamarla, reúne los datos necesarios con las demás tools. '
                                   . 'Escribe contenido_html usando SOLO estas etiquetas: h2, h3, h4, p, table '
                                   . '(con thead/tbody/tr/th/td), ul/ol/li, b/strong, i/em, br, hr — sin '
                                   . 'atributos, sin imágenes ni enlaces externos. Sé lo más completo y '
                                   . 'ordenado posible. La herramienta devuelve una URL: compártela con el '
                                   . 'usuario tal cual, como texto plano, para que sea clicable en el chat.',
                    'input_schema' => [
                        'type'       => 'object',
                        'properties' => [
                            'titulo' => [
                                'type'        => 'string',
                                'description' => 'Título del informe',
                            ],
                            'contenido_html' => [
                                'type'        => 'string',
                                'description' => 'Cuerpo del informe en HTML restringido (ver descripción de la tool)',
                            ],
                        ],
                        'required' => ['titulo', 'contenido_html'],
                    ],
                ],
                'permisos' => 'asistente_ia.pdf.use',
                'ejecutor' => static fn(array $input) => ToolReportes::generarReportePdf($input),
            ],

            // ── Acceso SQL directo (solo SELECT) ─────────────────────────────
            'consultar_base_de_datos' => [
                'definicion' => [
                    'name'        => 'consultar_base_de_datos',
                    'description' => 'Ejecuta una consulta SQL SELECT directa en la base de datos del sistema. '
                                   . 'ÚSALA cuando las herramientas especializadas no cubran la necesidad. '
                                   . 'Solo permite SELECT/WITH. Máximo 250 filas devueltas. '
                                   . 'IMPORTANTE: siempre incluye filtros WHERE de municipio y/o secretaría '
                                   . 'según el scope del usuario (disponible en el bloque dinámico del system prompt). '
                                   . 'Las tablas principales y sus columnas están documentadas en el system prompt. '
                                   . 'Ejemplos de uso: consultas cruzadas entre tablas, cálculos personalizados, '
                                   . 'datos de tablas secundarias como tbl_lideres, tbl_boletin, tbl_prensa, etc.',
                    'input_schema' => [
                        'type'       => 'object',
                        'properties' => [
                            'sql_query' => [
                                'type'        => 'string',
                                'description' => 'Consulta SQL SELECT válida. Debe empezar con SELECT o WITH. '
                                              . 'Solo lectura — INSERT/UPDATE/DELETE/DROP están bloqueados. '
                                              . 'El sistema agrega LIMIT 250 automáticamente si no lo incluyes. '
                                              . 'Usa parámetros literales en el SQL (no placeholders :param).',
                            ],
                        ],
                        'required' => ['sql_query'],
                    ],
                ],
                'permisos' => '',
                'ejecutor' => static fn(array $input) => ToolBaseDeDatos::consultar($input),
            ],
        ];
    }

    /**
     * Retorna solo las tools que el usuario en sesión tiene permiso de usar.
     * Esto es lo que se envía a Claude como lista de herramientas disponibles.
     *
     * @return array[] Definiciones en formato API (solo el campo 'definicion')
     */
    public static function paraUsuario(): array
    {
        $visibles = [];
        foreach (self::todas() as $nombre => $entry) {
            if (self::tienePermiso($entry['permisos'])) {
                $visibles[] = $entry['definicion'];
            }
        }
        // Ordenar alfabéticamente por nombre para que el cache de prompts sea determinista
        usort($visibles, static fn($a, $b) => strcmp($a['name'], $b['name']));
        return $visibles;
    }

    /**
     * Ejecuta una herramienta con revalidación de permiso y auditoría.
     *
     * @param  string $nombre Nombre de la tool (tal como la llamó Claude)
     * @param  array  $input  Parámetros JSON de la tool
     * @return array  Resultado serializable como JSON
     */
    public static function ejecutar(string $nombre, array $input): array
    {
        $todas = self::todas();

        if (!isset($todas[$nombre])) {
            return ['error' => "Herramienta '{$nombre}' no reconocida."];
        }

        $entry = $todas[$nombre];

        // Revalidación de permiso (fail-closed)
        if (!self::tienePermiso($entry['permisos'])) {
            self::registrarLog($nombre, $input, 0, 0, false, 'Sin permisos RBAC');
            return ['error' => 'No tienes permiso para acceder a esta información.'];
        }

        $inicio = microtime(true);
        try {
            $resultado = ($entry['ejecutor'])($input);
            $ms   = (int) ((microtime(true) - $inicio) * 1000);
            $filas = isset($resultado['compromisos']) ? count($resultado['compromisos'])
                   : (isset($resultado['municipios']) ? count($resultado['municipios'])
                   : (isset($resultado['secretarias']) ? count($resultado['secretarias']) : 0));
            self::registrarLog($nombre, $input, $filas, $ms, true);
            return $resultado;
        } catch (\Throwable $e) {
            $ms = (int) ((microtime(true) - $inicio) * 1000);
            self::registrarLog($nombre, $input, 0, $ms, false, $e->getMessage());
            return ['error' => 'Error al consultar la información. Intenta de nuevo.'];
        }
    }

    // ── Helpers privados ─────────────────────────────────────────────────────

    private static function tienePermiso(string|array $permisos): bool
    {
        // '' significa solo sesión activa (sin permiso específico)
        if ($permisos === '') {
            return isset($_SESSION['session_user']);
        }
        if (is_array($permisos)) {
            // OR: cualquiera de los permisos es suficiente
            foreach ($permisos as $p) {
                if (SessionData::hasPermission($p)) {
                    return true;
                }
            }
            return false;
        }
        return SessionData::hasPermission($permisos);
    }

    private static function registrarLog(
        string $nombre,
        array  $input,
        int    $filas,
        int    $ms,
        bool   $exito,
        string $error = ''
    ): void {
        $usuarioId = (int) (SessionData::getUserId() ?? 0);
        try {
            $db  = new DbConection();
            $pdo = $db->openConect();
            $st  = $pdo->prepare(
                "INSERT INTO tbl_ia_tool_logs
                    (tbl_usuario_id, tool_nombre, tool_input, filas_devueltas, duracion_ms, exito, error, created_at)
                 VALUES (:uid, :tool, :input, :filas, :ms, :exito, :error, :now)"
            );
            $st->execute([
                ':uid'   => $usuarioId,
                ':tool'  => $nombre,
                ':input' => json_encode($input, JSON_UNESCAPED_UNICODE),
                ':filas' => $filas,
                ':ms'    => $ms,
                ':exito' => $exito ? 1 : 0,
                ':error' => $error,
                ':now'   => Util::date(),
            ]);
            $db->closeConect();
        } catch (\Throwable $e) {
            // No romper el flujo principal por un error de auditoría
            error_log("IaToolRegistry::registrarLog error: " . $e->getMessage());
        }
    }
}
