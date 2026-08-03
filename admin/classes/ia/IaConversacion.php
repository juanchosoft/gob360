<?php

/**
 * Gestión de conversaciones y mensajes del asistente IA.
 * Todas las operaciones verifican propiedad del usuario en sesión.
 */
final class IaConversacion
{
    /**
     * Crea una conversación nueva para el usuario en sesión.
     * @param  string $canal 'widget' | 'voz_gobia'
     * @return int ID de la nueva conversación
     */
    public static function crear(string $canal = 'widget'): int
    {
        $usuarioId = (int) SessionData::getUserId();
        $db  = new DbConection();
        $pdo = $db->openConect();

        $st = $pdo->prepare(
            "INSERT INTO tbl_ia_conversaciones (tbl_usuario_id, canal, created_at, updated_at)
             VALUES (:uid, :canal, :now, :now)"
        );
        $st->execute([':uid' => $usuarioId, ':canal' => $canal, ':now' => Util::date()]);
        $id = (int) $pdo->lastInsertId();
        $db->closeConect();
        return $id;
    }

    /**
     * Devuelve la conversación de HOY del usuario para el canal indicado, creándola si no
     * existe. Usado por el canal 'voz_gobia': una sola sesión por usuario por día, para no
     * acumular historial largo ni mezclarse con el historial del widget de chat.
     *
     * @param  string $canal 'voz_gobia' (u otro canal de sesión única diaria en el futuro)
     * @return int ID de la conversación de hoy
     */
    public static function obtenerOCrearSesionDiaria(string $canal): int
    {
        $usuarioId = (int) SessionData::getUserId();
        $db  = new DbConection();
        $pdo = $db->openConect();

        $st = $pdo->prepare(
            "SELECT id FROM tbl_ia_conversaciones
              WHERE tbl_usuario_id = :uid AND canal = :canal AND activa = 1
                AND DATE(created_at) = CURDATE()
              ORDER BY id DESC LIMIT 1"
        );
        $st->execute([':uid' => $usuarioId, ':canal' => $canal]);
        $id = $st->fetchColumn();
        $db->closeConect();

        if ($id !== false) {
            return (int) $id;
        }

        return self::crear($canal);
    }

    /**
     * Lista las conversaciones propias del usuario en el widget de chat (máx. 30, ordenadas
     * por reciente). Las sesiones de voz de gobia.php (canal 'voz_gobia') no se listan aquí.
     * @return array<int, array{id:int, titulo:string, updated_at:string}>
     */
    public static function listarPropias(): array
    {
        $usuarioId = (int) SessionData::getUserId();
        $db  = new DbConection();
        $pdo = $db->openConect();

        $st = $pdo->prepare(
            "SELECT id, COALESCE(titulo, 'Nueva conversación') AS titulo, updated_at
               FROM tbl_ia_conversaciones
              WHERE tbl_usuario_id = :uid AND activa = 1 AND canal = 'widget'
              ORDER BY updated_at DESC
              LIMIT 30"
        );
        $st->execute([':uid' => $usuarioId]);
        $rows = $st->fetchAll(PDO::FETCH_ASSOC);
        $db->closeConect();
        return $rows ?: [];
    }

    /**
     * Carga los mensajes de una conversación, verificando que pertenece al usuario.
     * @return array<int, array{id:int, rol:string, contenido:string, origen:string, created_at:string}>
     * @throws RuntimeException si la conversación no existe o no pertenece al usuario
     */
    public static function cargarMensajes(int $conversacionId): array
    {
        if (!self::verificarPropiedad($conversacionId)) {
            throw new RuntimeException('Conversación no encontrada o sin acceso.');
        }

        $db  = new DbConection();
        $pdo = $db->openConect();

        $st = $pdo->prepare(
            "SELECT id, rol, contenido, origen, created_at
               FROM tbl_ia_mensajes
              WHERE conversacion_id = :cid
              ORDER BY id ASC"
        );
        $st->execute([':cid' => $conversacionId]);
        $rows = $st->fetchAll(PDO::FETCH_ASSOC);
        $db->closeConect();
        return $rows ?: [];
    }

    /**
     * Carga los mensajes en formato API (contenido_api) para reconstruir el contexto.
     * Devuelve los últimos N mensajes para evitar exceder el contexto.
     * @return array Mensajes en formato [role, content] para la API de Claude
     */
    public static function cargarContextoApi(int $conversacionId, int $limite = 20): array
    {
        if (!self::verificarPropiedad($conversacionId)) {
            return [];
        }

        $db  = new DbConection();
        $pdo = $db->openConect();

        // Subconsulta para tomar los últimos N y luego ordenarlos ASC
        $st = $pdo->prepare(
            "SELECT rol, contenido_api FROM (
                SELECT id, rol, contenido_api
                  FROM tbl_ia_mensajes
                 WHERE conversacion_id = :cid
                 ORDER BY id DESC
                 LIMIT :lim
             ) sub ORDER BY id ASC"
        );
        $st->bindValue(':cid', $conversacionId, PDO::PARAM_INT);
        $st->bindValue(':lim', $limite, PDO::PARAM_INT);
        $st->execute();
        $rows = $st->fetchAll(PDO::FETCH_ASSOC);
        $db->closeConect();

        $mensajes = [];
        foreach ($rows as $row) {
            if (empty($row['contenido_api'])) {
                continue;
            }
            $contenido = json_decode($row['contenido_api'], true);
            if ($contenido !== null) {
                $mensajes[] = [
                    'role'    => $row['rol'],
                    'content' => $contenido,
                ];
            }
        }
        return $mensajes;
    }

    /**
     * Verifica que la conversación existe y pertenece al usuario en sesión.
     */
    public static function verificarPropiedad(int $conversacionId): bool
    {
        if ($conversacionId <= 0) {
            return false;
        }
        $usuarioId = (int) SessionData::getUserId();
        $db  = new DbConection();
        $pdo = $db->openConect();

        $st = $pdo->prepare(
            "SELECT COUNT(*) FROM tbl_ia_conversaciones
              WHERE id = :cid AND tbl_usuario_id = :uid AND activa = 1"
        );
        $st->execute([':cid' => $conversacionId, ':uid' => $usuarioId]);
        $existe = (int) $st->fetchColumn() > 0;
        $db->closeConect();
        return $existe;
    }

    /**
     * Guarda un mensaje en la BD y devuelve su ID.
     */
    public static function guardarMensaje(
        int    $conversacionId,
        string $rol,           // 'user' | 'assistant'
        string $contenido,     // texto plano para UI
        mixed  $contenidoApi,  // array de bloques para la API (se serializa como JSON)
        int    $tokensEntrada,
        int    $tokensSalida,
        string $origen = 'texto'  // 'texto' | 'voz'
    ): int {
        $db  = new DbConection();
        $pdo = $db->openConect();

        $st = $pdo->prepare(
            "INSERT INTO tbl_ia_mensajes
                (conversacion_id, rol, contenido, contenido_api, tokens_entrada, tokens_salida, origen, created_at)
             VALUES (:cid, :rol, :contenido, :api, :te, :ts, :origen, :now)"
        );
        $st->execute([
            ':cid'      => $conversacionId,
            ':rol'      => $rol,
            ':contenido'=> $contenido,
            ':api'      => json_encode($contenidoApi, JSON_UNESCAPED_UNICODE),
            ':te'       => $tokensEntrada,
            ':ts'       => $tokensSalida,
            ':origen'   => $origen,
            ':now'      => Util::date(),
        ]);
        $id = (int) $pdo->lastInsertId();

        // Actualizar updated_at y título de la conversación
        $pdo->prepare(
            "UPDATE tbl_ia_conversaciones SET updated_at = :now WHERE id = :cid"
        )->execute([':now' => Util::date(), ':cid' => $conversacionId]);

        $db->closeConect();
        return $id;
    }

    /**
     * Actualiza el título de la conversación con el primer mensaje del usuario (truncado a 80 chars).
     */
    public static function actualizarTitulo(int $conversacionId, string $primerMensaje): void
    {
        $titulo = mb_substr(trim($primerMensaje), 0, 80);
        if (mb_strlen($primerMensaje) > 80) {
            $titulo .= '…';
        }
        $db  = new DbConection();
        $pdo = $db->openConect();
        $pdo->prepare(
            "UPDATE tbl_ia_conversaciones SET titulo = :titulo WHERE id = :cid AND titulo IS NULL"
        )->execute([':titulo' => $titulo, ':cid' => $conversacionId]);
        $db->closeConect();
    }

    /**
     * Cuenta mensajes del usuario en la última hora (para rate limiting).
     */
    public static function contarMensajesUltimaHora(): int
    {
        $usuarioId = (int) SessionData::getUserId();
        $db  = new DbConection();
        $pdo = $db->openConect();

        $st = $pdo->prepare(
            "SELECT COUNT(*) FROM tbl_ia_mensajes m
               JOIN tbl_ia_conversaciones c ON c.id = m.conversacion_id
              WHERE c.tbl_usuario_id = :uid
                AND m.rol = 'user'
                AND m.created_at >= DATE_SUB(NOW(), INTERVAL 1 HOUR)"
        );
        $st->execute([':uid' => $usuarioId]);
        $count = (int) $st->fetchColumn();
        $db->closeConect();
        return $count;
    }

    /**
     * Obtiene el texto de un mensaje verificando propiedad del usuario.
     * @throws RuntimeException si el mensaje no existe o no le pertenece
     */
    public static function obtenerTextoMensaje(int $mensajeId): string
    {
        $usuarioId = (int) SessionData::getUserId();
        $db  = new DbConection();
        $pdo = $db->openConect();

        $st = $pdo->prepare(
            "SELECT m.contenido FROM tbl_ia_mensajes m
               JOIN tbl_ia_conversaciones c ON c.id = m.conversacion_id
              WHERE m.id = :mid AND c.tbl_usuario_id = :uid AND m.rol = 'assistant'"
        );
        $st->execute([':mid' => $mensajeId, ':uid' => $usuarioId]);
        $row = $st->fetch(PDO::FETCH_ASSOC);
        $db->closeConect();

        if (!$row) {
            throw new RuntimeException('Mensaje no encontrado o sin acceso.');
        }
        return $row['contenido'];
    }
}
