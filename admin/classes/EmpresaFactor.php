<?php

require_once __DIR__ . '/SessionData.php';
require_once __DIR__ . '/DbConection.php';
require_once __DIR__ . '/Util.php';

/**
 * Relación muchos-a-muchos Empresa ↔ Factor (tbl_empresa_factor).
 * Cada fila se crea/edita/elimina por su id; nunca se vacía el set al editar una asociación.
 */
class EmpresaFactor
{
    private const COMPROMISO_MAX = 500;

    public static function getByFactor($rqst)
    {
        $factorId = isset($rqst['tbl_factor_id']) ? (int) $rqst['tbl_factor_id'] : 0;
        $codigoMunicipio = isset($rqst['codigo_muncipio']) ? (int) $rqst['codigo_muncipio'] : 0;

        if ($factorId <= 0 || $codigoMunicipio <= 0) {
            return Util::error_missing_data();
        }

        $db = new DbConection();
        $pdo = $db->openConect();

        try {
            $q = 'SELECT ef.id, ef.tbl_empresa_id, ef.tbl_factor_id, ef.codigo_muncipio, ef.compromiso,
                         ef.dt_create, ef.dt_update,
                         e.nombre_empresa, e.nit
                  FROM ' . $db->getTable('tbl_empresa_factor') . ' ef
                  INNER JOIN ' . $db->getTable('tbl_empresas') . ' e ON e.id = ef.tbl_empresa_id
                  WHERE ef.tbl_factor_id = :factor_id
                    AND ef.codigo_muncipio = :codigo_muncipio
                  ORDER BY e.nombre_empresa ASC';
            $stmt = $pdo->prepare($q);
            $stmt->bindValue(':factor_id', $factorId, PDO::PARAM_INT);
            $stmt->bindValue(':codigo_muncipio', $codigoMunicipio, PDO::PARAM_INT);
            $stmt->execute();

            return ['output' => ['valid' => true, 'response' => $stmt->fetchAll(PDO::FETCH_ASSOC)]];
        } catch (Exception $e) {
            return Util::error_general('Error al listar empresas del factor: ' . $e->getMessage());
        } finally {
            $db->closeConect();
        }
    }

    public static function getByEmpresa($rqst)
    {
        $empresaId = isset($rqst['tbl_empresa_id']) ? (int) $rqst['tbl_empresa_id'] : 0;

        if ($empresaId <= 0) {
            return Util::error_missing_data();
        }

        $db = new DbConection();
        $pdo = $db->openConect();

        try {
            $q = 'SELECT ef.id, ef.tbl_empresa_id, ef.tbl_factor_id, ef.codigo_muncipio, ef.compromiso,
                         ef.dt_create, ef.dt_update,
                         f.tipo AS nombre_factor, f.tipo_medicion, f.icono
                  FROM ' . $db->getTable('tbl_empresa_factor') . ' ef
                  INNER JOIN ' . $db->getTable('tbl_factores') . ' f ON f.id = ef.tbl_factor_id
                  WHERE ef.tbl_empresa_id = :empresa_id
                  ORDER BY f.tipo ASC';
            $stmt = $pdo->prepare($q);
            $stmt->bindValue(':empresa_id', $empresaId, PDO::PARAM_INT);
            $stmt->execute();

            return ['output' => ['valid' => true, 'response' => $stmt->fetchAll(PDO::FETCH_ASSOC)]];
        } catch (Exception $e) {
            return Util::error_general('Error al listar factores de la empresa: ' . $e->getMessage());
        } finally {
            $db->closeConect();
        }
    }

    public static function getById($rqst)
    {
        $id = isset($rqst['id']) ? (int) $rqst['id'] : 0;
        if ($id <= 0) {
            return Util::error_missing_data();
        }

        $db = new DbConection();
        $pdo = $db->openConect();

        try {
            $q = 'SELECT ef.*, e.nombre_empresa, f.tipo AS nombre_factor
                  FROM ' . $db->getTable('tbl_empresa_factor') . ' ef
                  INNER JOIN ' . $db->getTable('tbl_empresas') . ' e ON e.id = ef.tbl_empresa_id
                  INNER JOIN ' . $db->getTable('tbl_factores') . ' f ON f.id = ef.tbl_factor_id
                  WHERE ef.id = :id
                  LIMIT 1';
            $stmt = $pdo->prepare($q);
            $stmt->bindValue(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            $row = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$row) {
                return Util::error_general('Asociación no encontrada.');
            }

            return ['output' => ['valid' => true, 'response' => [$row]]];
        } catch (Exception $e) {
            return Util::error_general($e->getMessage());
        } finally {
            $db->closeConect();
        }
    }

    /**
     * Crear o actualizar UNA asociación por id.
     * No elimina otras filas del factor ni de la empresa.
     */
    public static function save($rqst)
    {
        $id = isset($rqst['id']) ? (int) $rqst['id'] : 0;
        $empresaId = isset($rqst['tbl_empresa_id']) ? (int) $rqst['tbl_empresa_id'] : 0;
        $factorId = isset($rqst['tbl_factor_id']) ? (int) $rqst['tbl_factor_id'] : 0;
        $codigoMunicipio = isset($rqst['codigo_muncipio']) ? (int) $rqst['codigo_muncipio'] : 0;
        $compromiso = isset($rqst['compromiso']) ? trim((string) $rqst['compromiso']) : '';
        $userId = SessionData::getUserId() ?: 2;

        if ($empresaId <= 0 || $factorId <= 0 || $codigoMunicipio <= 0) {
            return Util::error_missing_data();
        }

        if (mb_strlen($compromiso) > self::COMPROMISO_MAX) {
            return Util::error_general('El compromiso no puede superar ' . self::COMPROMISO_MAX . ' caracteres.');
        }

        $db = new DbConection();
        $pdo = $db->openConect();

        try {
            $empresa = self::fetchEmpresa($pdo, $db, $empresaId);
            if (!$empresa) {
                return Util::error_general('La empresa no existe.');
            }
            if ((int) $empresa['codigo_muncipio'] !== $codigoMunicipio) {
                return Util::error_general('La empresa no pertenece a este municipio.');
            }

            $factor = self::fetchFactor($pdo, $db, $factorId);
            if (!$factor) {
                return Util::error_general('El factor no existe.');
            }

            if (self::pairExists($pdo, $db, $empresaId, $factorId, $id)) {
                return Util::error_general('Esta empresa ya está asociada a ese factor.');
            }

            if ($id > 0) {
                $stmtCheck = $pdo->prepare(
                    'SELECT id FROM ' . $db->getTable('tbl_empresa_factor') . ' WHERE id = :id LIMIT 1'
                );
                $stmtCheck->bindValue(':id', $id, PDO::PARAM_INT);
                $stmtCheck->execute();
                if (!$stmtCheck->fetch(PDO::FETCH_ASSOC)) {
                    return Util::error_general('No se encontró la asociación a actualizar.');
                }

                $q = 'UPDATE ' . $db->getTable('tbl_empresa_factor') . '
                      SET tbl_empresa_id = :empresa_id,
                          tbl_factor_id = :factor_id,
                          codigo_muncipio = :codigo_muncipio,
                          compromiso = :compromiso,
                          user_id = :user_id,
                          dt_update = ' . Util::date_now_server() . '
                      WHERE id = :id';
                $stmt = $pdo->prepare($q);
                $stmt->bindValue(':empresa_id', $empresaId, PDO::PARAM_INT);
                $stmt->bindValue(':factor_id', $factorId, PDO::PARAM_INT);
                $stmt->bindValue(':codigo_muncipio', $codigoMunicipio, PDO::PARAM_INT);
                $stmt->bindValue(':compromiso', $compromiso !== '' ? $compromiso : null, $compromiso !== '' ? PDO::PARAM_STR : PDO::PARAM_NULL);
                $stmt->bindValue(':user_id', (int) $userId, PDO::PARAM_INT);
                $stmt->bindValue(':id', $id, PDO::PARAM_INT);

                if (!$stmt->execute()) {
                    return Util::error_general('No fue posible actualizar la asociación.');
                }

                return ['output' => ['valid' => true, 'id' => $id, 'response' => 'Asociación actualizada.']];
            }

            $q = 'INSERT INTO ' . $db->getTable('tbl_empresa_factor') . '
                  (tbl_empresa_id, tbl_factor_id, codigo_muncipio, compromiso, user_id, dt_create, dt_update)
                  VALUES (:empresa_id, :factor_id, :codigo_muncipio, :compromiso, :user_id, '
                . Util::date_now_server() . ', NULL)';
            $stmt = $pdo->prepare($q);
            $stmt->bindValue(':empresa_id', $empresaId, PDO::PARAM_INT);
            $stmt->bindValue(':factor_id', $factorId, PDO::PARAM_INT);
            $stmt->bindValue(':codigo_muncipio', $codigoMunicipio, PDO::PARAM_INT);
            $stmt->bindValue(':compromiso', $compromiso !== '' ? $compromiso : null, $compromiso !== '' ? PDO::PARAM_STR : PDO::PARAM_NULL);
            $stmt->bindValue(':user_id', (int) $userId, PDO::PARAM_INT);

            if (!$stmt->execute()) {
                return Util::error_general('No fue posible crear la asociación.');
            }

            return [
                'output' => [
                    'valid' => true,
                    'id' => (int) $pdo->lastInsertId(),
                    'response' => 'Asociación creada.',
                ],
            ];
        } catch (Exception $e) {
            return Util::error_general($e->getMessage());
        } finally {
            $db->closeConect();
        }
    }

    /**
     * Elimina solo la fila indicada (no afecta otras asociaciones del factor o empresa).
     */
    public static function delete($rqst)
    {
        $id = isset($rqst['id']) ? (int) $rqst['id'] : 0;
        if ($id <= 0) {
            return Util::error_missing_data();
        }

        $db = new DbConection();
        $pdo = $db->openConect();

        try {
            $stmt = $pdo->prepare('DELETE FROM ' . $db->getTable('tbl_empresa_factor') . ' WHERE id = :id');
            $stmt->bindValue(':id', $id, PDO::PARAM_INT);
            if (!$stmt->execute()) {
                return Util::error_general('No fue posible eliminar la asociación.');
            }
            if ($stmt->rowCount() < 1) {
                return Util::error_general('Asociación no encontrada.');
            }

            return ['output' => ['valid' => true, 'response' => 'Asociación eliminada.']];
        } catch (Exception $e) {
            return Util::error_general($e->getMessage());
        } finally {
            $db->closeConect();
        }
    }

    /** Contadores por factor/empresa en un municipio (para badges del listado). */
    public static function countsByMunicipio(int $codigoMunicipio): array
    {
        if ($codigoMunicipio <= 0) {
            return ['by_factor' => [], 'by_empresa' => []];
        }

        $db = new DbConection();
        $pdo = $db->openConect();

        try {
            $q = 'SELECT tbl_factor_id, COUNT(*) AS total
                  FROM ' . $db->getTable('tbl_empresa_factor') . '
                  WHERE codigo_muncipio = :mun
                  GROUP BY tbl_factor_id';
            $stmt = $pdo->prepare($q);
            $stmt->bindValue(':mun', $codigoMunicipio, PDO::PARAM_INT);
            $stmt->execute();
            $byFactor = [];
            foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
                $byFactor[(int) $row['tbl_factor_id']] = (int) $row['total'];
            }

            $q2 = 'SELECT tbl_empresa_id, COUNT(*) AS total
                   FROM ' . $db->getTable('tbl_empresa_factor') . '
                   WHERE codigo_muncipio = :mun
                   GROUP BY tbl_empresa_id';
            $stmt2 = $pdo->prepare($q2);
            $stmt2->bindValue(':mun', $codigoMunicipio, PDO::PARAM_INT);
            $stmt2->execute();
            $byEmpresa = [];
            foreach ($stmt2->fetchAll(PDO::FETCH_ASSOC) as $row) {
                $byEmpresa[(int) $row['tbl_empresa_id']] = (int) $row['total'];
            }

            return ['by_factor' => $byFactor, 'by_empresa' => $byEmpresa];
        } catch (Exception $e) {
            return ['by_factor' => [], 'by_empresa' => []];
        } finally {
            $db->closeConect();
        }
    }

    private static function fetchEmpresa(PDO $pdo, DbConection $db, int $id): ?array
    {
        $stmt = $pdo->prepare(
            'SELECT id, codigo_muncipio FROM ' . $db->getTable('tbl_empresas') . ' WHERE id = :id LIMIT 1'
        );
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    private static function fetchFactor(PDO $pdo, DbConection $db, int $id): ?array
    {
        $stmt = $pdo->prepare(
            'SELECT id FROM ' . $db->getTable('tbl_factores') . ' WHERE id = :id LIMIT 1'
        );
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    private static function pairExists(PDO $pdo, DbConection $db, int $empresaId, int $factorId, int $excludeId): bool
    {
        $q = 'SELECT id FROM ' . $db->getTable('tbl_empresa_factor') . '
              WHERE tbl_empresa_id = :empresa_id AND tbl_factor_id = :factor_id';
        if ($excludeId > 0) {
            $q .= ' AND id <> :exclude_id';
        }
        $q .= ' LIMIT 1';
        $stmt = $pdo->prepare($q);
        $stmt->bindValue(':empresa_id', $empresaId, PDO::PARAM_INT);
        $stmt->bindValue(':factor_id', $factorId, PDO::PARAM_INT);
        if ($excludeId > 0) {
            $stmt->bindValue(':exclude_id', $excludeId, PDO::PARAM_INT);
        }
        $stmt->execute();
        return (bool) $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
