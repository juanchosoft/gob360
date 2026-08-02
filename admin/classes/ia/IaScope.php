<?php

/**
 * Calcula el alcance de datos permitido para el usuario en sesión.
 *
 * REGLA DE ORO: estas restricciones se aplican SIEMPRE en servidor.
 * Ningún parámetro del modelo o del navegador puede ampliarlas.
 */
final class IaScope
{
    /**
     * @return array{municipio_id: ?string, secretaria_id: ?int, ve_todo: bool, rol: string,
     *               nombre: string, apellido: string}
     */
    public static function actual(): array
    {
        $tipo = SessionData::getUserType();

        $veTodo = in_array($tipo, [
            'SuperAdministrador',
            Util::ADMINISTRADOR,
            Util::GOBERNADOR,
            Util::Secretaria_Despacho_Gobernacion,
        ], true);

        $esAlcalde  = in_array($tipo, [Util::ALCALDE, Util::AUXILIAR_ALCALDE], true);
        $esSecretario = in_array($tipo, [Util::SECRETARIO_DESPACHO, Util::AUXILIAR, Util::AUXILIAR_SECRET_GOB], true);

        return [
            'rol'          => $tipo,
            've_todo'      => $veTodo,
            'municipio_id' => $esAlcalde ? (string) SessionData::getCodigoMunicipio() : null,
            'secretaria_id'=> $esSecretario ? SessionData::getSecretaria() : null,
            'nombre'       => $_SESSION['session_user']['nombre'] ?? '',
            'apellido'     => $_SESSION['session_user']['apellido'] ?? '',
        ];
    }

    /**
     * Genera cláusula WHERE y parámetros para filtrar por municipio.
     *
     * Si el usuario es alcalde: fuerza su municipio (ignora lo pedido).
     * Si el usuario ve todo: respeta el municipio solicitado (o ningún filtro si es null).
     *
     * @param  ?string $municipioSolicitado  Código del municipio pedido por la IA (puede ser null)
     * @param  string  $columna             Nombre de la columna SQL (ej. "m.tbl_municipio_id")
     * @return array{0: string, 1: array<string,mixed>}  [sql_extra, params_extra]
     */
    public static function filtroMunicipio(?string $municipioSolicitado, string $columna): array
    {
        $scope = self::actual();

        if ($scope['municipio_id'] !== null) {
            // Alcalde: fuerza siempre su propio municipio
            return [" AND {$columna} = :scope_municipio", [':scope_municipio' => $scope['municipio_id']]];
        }

        if ($municipioSolicitado !== null && $municipioSolicitado !== '') {
            // Admin/Gobernador con filtro solicitado
            return [" AND {$columna} = :scope_municipio", [':scope_municipio' => $municipioSolicitado]];
        }

        return ['', []];
    }

    /**
     * Genera cláusula WHERE y parámetros para filtrar por secretaría.
     *
     * Si el usuario es secretario: fuerza su secretaría (ignora lo pedido).
     * Si el usuario ve todo: respeta la secretaría solicitada (o ningún filtro si es null).
     *
     * @param  ?int   $secretariaSolicitada  ID de secretaría pedida (puede ser null)
     * @param  string $columna               Nombre de la columna SQL
     * @return array{0: string, 1: array<string,mixed>}
     */
    public static function filtroSecretaria(?int $secretariaSolicitada, string $columna): array
    {
        $scope = self::actual();

        if ($scope['secretaria_id'] !== null && $scope['secretaria_id'] > 0) {
            return [" AND {$columna} = :scope_secretaria", [':scope_secretaria' => $scope['secretaria_id']]];
        }

        if ($secretariaSolicitada !== null && $secretariaSolicitada > 0) {
            return [" AND {$columna} = :scope_secretaria", [':scope_secretaria' => $secretariaSolicitada]];
        }

        return ['', []];
    }

    /**
     * Genera el texto descriptivo del alcance para incluir en el prompt dinámico.
     */
    public static function descripcionParaPrompt(): string
    {
        $scope = self::actual();

        if ($scope['municipio_id'] !== null) {
            $nomMunicipio = self::nombreMunicipio($scope['municipio_id']);
            return "Alcance territorial: solo municipio {$nomMunicipio} (código {$scope['municipio_id']}).";
        }

        if ($scope['secretaria_id'] !== null && $scope['secretaria_id'] > 0) {
            $nomSec = self::nombreSecretaria($scope['secretaria_id']);
            return "Alcance funcional: solo secretaría {$nomSec}.";
        }

        return "Alcance: todo el departamento.";
    }

    /**
     * Mensaje de bienvenida del widget, específico al alcance real del usuario.
     * Declara explícitamente tanto lo que SÍ puede consultar como lo que NO,
     * para que ningún usuario asuma que tiene acceso a datos de otro municipio,
     * otra secretaría o del nivel departamental.
     *
     * @return array{titulo: string, texto: string}
     */
    public static function mensajeBienvenida(): array
    {
        $scope = self::actual();

        if ($scope['municipio_id'] !== null) {
            $nombre = self::nombreMunicipio($scope['municipio_id']);
            return [
                'titulo' => 'ALMA',
                'texto'  => "Puedo ayudarte con información del municipio de <strong>{$nombre}</strong>: "
                          . "compromisos, proyectos, visitas y factores de inestabilidad de tu municipio. "
                          . "No tengo acceso a información de otros municipios ni de la Gobernación.",
            ];
        }

        if ($scope['secretaria_id'] !== null && $scope['secretaria_id'] > 0) {
            $nombre = self::nombreSecretaria($scope['secretaria_id']);
            return [
                'titulo' => 'ALMA',
                'texto'  => "Puedo ayudarte con información de la Secretaría de <strong>{$nombre}</strong>: "
                          . "compromisos, proyectos y plan de desarrollo dentro de tu secretaría. "
                          . "No tengo acceso a información de otras secretarías ni de las alcaldías.",
            ];
        }

        return [
            'titulo' => 'ALMA',
            'texto'  => "Puedo ayudarte con información de todo el departamento: compromisos, proyectos, "
                      . "visitas y factores de inestabilidad territorial de los 87 municipios de Santander.",
        ];
    }

    private static function nombreMunicipio(string $codigo): string
    {
        try {
            $db  = new DbConection();
            $pdo = $db->openConect();
            $st  = $pdo->prepare(
                "SELECT municipio FROM tbl_ciudades_accion_unificada WHERE codigo_muncipio = :c LIMIT 1"
            );
            $st->execute([':c' => $codigo]);
            $row = $st->fetch(PDO::FETCH_ASSOC);
            $db->closeConect();
            return $row ? $row['municipio'] : $codigo;
        } catch (Throwable $e) {
            return $codigo;
        }
    }

    private static function nombreSecretaria(int $id): string
    {
        try {
            $db  = new DbConection();
            $pdo = $db->openConect();
            $st  = $pdo->prepare(
                "SELECT secretaria FROM tbl_secretarias WHERE id = :id LIMIT 1"
            );
            $st->execute([':id' => $id]);
            $row = $st->fetch(PDO::FETCH_ASSOC);
            $db->closeConect();
            return $row ? $row['secretaria'] : "#{$id}";
        } catch (Throwable $e) {
            return "#{$id}";
        }
    }
}
