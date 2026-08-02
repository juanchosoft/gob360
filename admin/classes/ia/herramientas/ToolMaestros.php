<?php

/**
 * Herramientas IA: datos maestros (municipios, secretarías).
 * No requieren permisos especiales — disponibles para toda sesión válida.
 *
 * tbl_ciudades_accion_unificada: el código DANE está en `codigo_muncipio` (typo real
 * de la BD, sin la segunda 'i'). El nombre está en `municipio`, no existe `provincia`
 * ni `nombre`. El filtro departamental es `codigo_departamento` (Santander = '68').
 * tbl_secretarias: el nombre está en la columna `secretaria`, no `nombre`.
 */
final class ToolMaestros
{
    /**
     * Busca municipios de Santander por nombre (parcial).
     * Devuelve código DANE + nombre + subregión.
     * Tool: buscar_municipio
     */
    public static function buscarMunicipio(array $input): array
    {
        $nombre = trim($input['nombre'] ?? '');
        if ($nombre === '') {
            return ['error' => 'Se requiere el nombre del municipio.'];
        }

        $db  = new DbConection();
        $pdo = $db->openConect();

        $st = $pdo->prepare(
            "SELECT
                codigo_muncipio  AS codigo_dane,
                municipio        AS nombre,
                subregion,
                nombre_alcalde,
                puntaje          AS puntaje_actual,
                color            AS color_actual
               FROM tbl_ciudades_accion_unificada
              WHERE municipio LIKE :n
                AND codigo_departamento = '68'
              ORDER BY municipio ASC
              LIMIT 250"
        );
        $st->execute([':n' => '%' . $nombre . '%']);
        $rows = $st->fetchAll(PDO::FETCH_ASSOC);
        $db->closeConect();

        if (empty($rows)) {
            return ['nota' => "No se encontraron municipios de Santander con el nombre '{$nombre}'.", 'municipios' => []];
        }
        return ['municipios' => $rows];
    }

    /**
     * Lista todas las secretarías del departamento.
     * Tool: listar_secretarias
     */
    public static function listarSecretarias(array $input): array
    {
        $db  = new DbConection();
        $pdo = $db->openConect();

        $st = $pdo->prepare(
            "SELECT id, secretaria AS nombre, secretario
               FROM tbl_secretarias
              ORDER BY secretaria ASC"
        );
        $st->execute();
        $rows = $st->fetchAll(PDO::FETCH_ASSOC);
        $db->closeConect();

        return ['secretarias' => $rows ?: []];
    }
}
