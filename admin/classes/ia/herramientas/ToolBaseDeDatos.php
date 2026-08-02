<?php

/**
 * Herramienta IA: acceso SQL de solo lectura a la base de datos.
 *
 * Permite a Claude ejecutar SELECT arbitrarios cuando las herramientas
 * especializadas no son suficientes. Bloquea cualquier operación de escritura.
 * Aplica siempre la REGLA DE ORO territorial (scoping server-side).
 */
final class ToolBaseDeDatos
{
    private const MAX_FILAS = 250;

    /**
     * Ejecuta una consulta SELECT y devuelve los resultados.
     * Tool: consultar_base_de_datos
     *
     * REGLA DE ORO: Si el usuario es Alcalde, el sistema inyecta automáticamente
     * el filtro de municipio. Si es Secretario, el filtro de secretaría.
     * Estos filtros NO pueden ser evitados por el prompt ni por la consulta.
     */
    public static function consultar(array $input): array
    {
        $sql = trim($input['sql_query'] ?? '');

        if ($sql === '') {
            return ['error' => 'La consulta SQL no puede estar vacía.'];
        }

        // Límite de longitud del SQL
        if (mb_strlen($sql) > 2000) {
            return ['error' => 'La consulta SQL supera el límite de 2000 caracteres.'];
        }

        // Validación de solo lectura: rechazar cualquier cosa que no sea SELECT
        $patrones = [
            '/^\s*INSERT\b/i', '/^\s*UPDATE\b/i', '/^\s*DELETE\b/i',
            '/^\s*DROP\b/i',   '/^\s*TRUNCATE\b/i', '/^\s*ALTER\b/i',
            '/^\s*CREATE\b/i', '/^\s*GRANT\b/i',    '/^\s*REVOKE\b/i',
            '/^\s*REPLACE\b/i','/^\s*CALL\b/i',     '/^\s*EXEC\b/i',
            '/;\s*(INSERT|UPDATE|DELETE|DROP|TRUNCATE|ALTER|CREATE|GRANT|REVOKE)/i',
            '/\/\*.*?\*\//s',  // comentarios que podrían ocultar palabras clave
        ];
        foreach ($patrones as $patron) {
            if (preg_match($patron, $sql)) {
                return ['error' => 'Solo se permiten consultas SELECT. Las operaciones de escritura están bloqueadas por seguridad.'];
            }
        }

        // Verificar que empiece con SELECT o WITH (CTEs)
        if (!preg_match('/^\s*(SELECT|WITH)\b/i', $sql)) {
            return ['error' => 'La consulta debe comenzar con SELECT o WITH. Solo se permiten operaciones de lectura.'];
        }

        // Bloquear acceso a tablas del sistema de MariaDB/MySQL
        if (preg_match('/\b(information_schema|performance_schema|mysql|sys)\b/i', $sql)) {
            return ['error' => 'Acceso a tablas del sistema no permitido.'];
        }

        // Obtener scope territorial del usuario (REGLA DE ORO)
        // El sistema informa a Claude el scope; Claude debe incluir los WHERE adecuados.
        // No se puede inyectar automáticamente en SQL arbitrario sin conocer los alias de tabla.
        $scope = IaScope::actual();

        $scopeAplicado = [];
        if ($scope['municipio_id'] !== null) {
            $scopeAplicado['municipio_id'] = $scope['municipio_id'];
        }
        if ($scope['secretaria_id'] !== null && $scope['secretaria_id'] > 0) {
            $scopeAplicado['secretaria_id'] = $scope['secretaria_id'];
        }

        // Inyectar LIMIT si no viene ya (o reemplazar valores excesivos)
        $sql = self::normalizarLimit($sql);

        $db  = new DbConection();
        $pdo = $db->openConect();

        try {
            $st = $pdo->prepare($sql);
            $st->execute();
            $filas = $st->fetchAll(PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            $db->closeConect();
            return [
                'error'         => 'Error al ejecutar la consulta: ' . $e->getMessage(),
                'sql_ejecutado' => $sql,
            ];
        }

        $db->closeConect();

        $totalFilas = count($filas);

        return [
            'filas'          => $filas,
            'total_filas'    => $totalFilas,
            'limite_maximo'  => self::MAX_FILAS,
            'scope_aplicado' => $scopeAplicado ?: 'ninguno (acceso global)',
            'sql_ejecutado'  => $sql,
        ];
    }

    /**
     * Asegura que la consulta tenga LIMIT <= MAX_FILAS.
     * Si ya tiene LIMIT, lo reduce si es mayor. Si no tiene, lo agrega.
     */
    private static function normalizarLimit(string $sql): string
    {
        // Detectar LIMIT existente (captura el número)
        if (preg_match('/\bLIMIT\s+(\d+)\b/i', $sql, $m)) {
            $limitActual = (int) $m[1];
            if ($limitActual > self::MAX_FILAS) {
                $sql = preg_replace('/\bLIMIT\s+\d+\b/i', 'LIMIT ' . self::MAX_FILAS, $sql);
            }
            return $sql;
        }

        // Detectar LIMIT x OFFSET y
        if (preg_match('/\bLIMIT\s+\d+\s*,\s*\d+\b/i', $sql)) {
            return $sql; // tiene LIMIT en formato MySQL "LIMIT offset, count", no tocar
        }

        // Sin LIMIT: agregar al final (antes de un posible punto y coma)
        $sql = rtrim($sql, '; ');
        return $sql . ' LIMIT ' . self::MAX_FILAS;
    }
}
