<?php

/**
 * Seguimiento de informes PDF generados por ALMA.
 * Todas las operaciones verifican propiedad del usuario en sesión.
 */
final class IaReporte
{
    /**
     * Registra un informe generado para el usuario en sesión.
     * @return int ID del nuevo registro
     */
    public static function crear(string $titulo, string $archivo): int
    {
        $usuarioId = (int) SessionData::getUserId();
        $db  = new DbConection();
        $pdo = $db->openConect();

        $st = $pdo->prepare(
            "INSERT INTO tbl_ia_reportes (tbl_usuario_id, titulo, archivo, created_at)
             VALUES (:uid, :titulo, :archivo, :now)"
        );
        $st->execute([
            ':uid'     => $usuarioId,
            ':titulo'  => $titulo,
            ':archivo' => $archivo,
            ':now'     => Util::date(),
        ]);
        $id = (int) $pdo->lastInsertId();
        $db->closeConect();
        return $id;
    }

    /**
     * Obtiene la ruta absoluta del PDF verificando propiedad del usuario.
     * @throws RuntimeException si el informe no existe o no le pertenece
     */
    public static function obtenerRuta(int $id): string
    {
        $usuarioId = (int) SessionData::getUserId();
        $db  = new DbConection();
        $pdo = $db->openConect();

        $st = $pdo->prepare(
            "SELECT archivo FROM tbl_ia_reportes WHERE id = :id AND tbl_usuario_id = :uid"
        );
        $st->execute([':id' => $id, ':uid' => $usuarioId]);
        $row = $st->fetch(PDO::FETCH_ASSOC);
        $db->closeConect();

        if (!$row) {
            throw new RuntimeException('Informe no encontrado o sin acceso.');
        }
        return __DIR__ . '/../../../uploads/ia_reportes/' . $row['archivo'];
    }
}
