<?php
/* error_reporting(E_ALL);
session_start(); */
error_reporting(E_ALL);
require_once 'SessionData.php';
require_once 'DbConection.php';
require_once 'Util.php';
require_once 'Role.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

/**
 * Clase que contiene todas las operaciones utilizadas sobre la base de datos
 * @author SPIDERSOFTWARE
 */
class Usuario
{

    public function __construct() {}

    public function guardaUsuario($data)
    {
        var_dump($data);
        exit();
    }

    public static function getAllInicioSession($data)
    {
        try {
            $db = new DbConection();
            $pdo = $db->openConect();

            $draw = $data['draw'] ?? 1;
            $start = $data['start'] ?? 0;
            $length = $data['length'] ?? 10;
            $searchValue = $data['search']['value'] ?? '';
            $orderColumnIndex = $data['order'][0]['column'] ?? 0;
            $orderDirection = $data['order'][0]['dir'] ?? 'asc';

            $columns = ['a.id', 'a.navegador', 'a.dtcreate', 'b.nickname', 'b.nombre', 'b.apellido', 'b.ip'];
            $orderColumn = $columns[$orderColumnIndex] ?? 'a.dtcreate';

            $where = '';
            if (!empty($searchValue)) {
                $where = "WHERE a.navegador LIKE :search OR b.nickname LIKE :search OR b.nombre LIKE :search OR b.apellido LIKE :search OR b.id LIKE :search OR a.ip LIKE :search";
            }

            $stmt = $pdo->prepare("SELECT COUNT(*) FROM " . $db->getTable('tbl_historial_session') . "");
            $stmt->execute();
            $recordsTotal = $stmt->fetchColumn();

            $stmt = $pdo->prepare("SELECT COUNT(*) 
            FROM " . $db->getTable('tbl_historial_session') . " a
            INNER JOIN " . $db->getTable('tbl_usuarios') . " b ON a.tec_usuario_id = b.id
            $where");

            if (!empty($searchValue)) {
                $stmt->bindValue(':search', '%' . $searchValue . '%');
            }
            $stmt->execute();
            $recordsFiltered = $stmt->fetchColumn();

            $sql = "SELECT
                    b.id,
                    a.ip, 
                    a.navegador, 
                    a.dtcreate,
                    b.nickname, 
                    b.nombre, 
                    b.apellido,
                    CONCAT(b.nombre, ' ', b.apellido) AS nombre_completo
                FROM " . $db->getTable('tbl_historial_session') . " a
                INNER JOIN " . $db->getTable('tbl_usuarios') . " b ON a.tec_usuario_id = b.id
                $where
                ORDER BY $orderColumn $orderDirection
                LIMIT :start, :length";

            $stmt = $pdo->prepare($sql);
            if (!empty($searchValue)) {
                $stmt->bindValue(':search', '%' . $searchValue . '%');
            }
            $stmt->bindValue(':start', (int)$start, PDO::PARAM_INT);
            $stmt->bindValue(':length', (int)$length, PDO::PARAM_INT);
            $stmt->execute();
            $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

            return [
                'draw' => intval($draw),
                'recordsTotal' => intval($recordsTotal),
                'recordsFiltered' => intval($recordsFiltered),
                'data' => $data
            ];
        } catch (PDOException $th) {
            return [
                'state' => false,
                'message' => $th->getMessage()
            ];
        }
    }


    public static function getAll($rqst)
    {
        $data = $rqst ?? [];

        $draw = $data['draw'] ?? 1;
        $start = $data['start'] ?? 0;
        $length = $data['length'] ?? 10;
        $searchValue = $data['search']['value'] ?? '';

        $id = isset($data['id']) ? intval($data['id']) : 0;

        try {

            $db = new DbConection();
            $pdo = $db->openConect();

            $municipioUsuarioLogueado = SessionData::getCodigoMunicipio();
            $secretariaUsuarioLogueado = SessionData::getSecretaria();
            $userType = SessionData::getUserType();
            
            $isAdmin = ($userType === Util::Administrador() || $userType === Util::SuperAdministrador() || $userType === Util::Gobernador());

            $table = $db->getTable('tbl_usuarios');
            $table_secretaria = $db->getTable('tbl_secretarias');

            $where = "";
            $params = [];

            if ($id > 0) {

                $q = "SELECT * FROM " . $db->getTable('tbl_usuarios') . " WHERE id = :id";
                $stmt = $pdo->prepare($q);
                $stmt->bindValue(':id', $id, PDO::PARAM_INT);
                $stmt->execute();
                $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

                $stmt = null;
                $db->closeConect();

                return [
                    'valid' => true,
                    'data' => $result
                ];
            } else {

                if (!$isAdmin) {
                    
                    $roles_municipales = [Util::Alcalde(), Util::Auxiliar_Alcalde()];
                    $roles_secretaria = [Util::Secretario_Despacho(), Util::Auxiliar(), Util::Secretaria_Despacho_Gobernacion(), Util::Auxiliar_secret_gob()];

                    if (in_array($userType, $roles_municipales) && $municipioUsuarioLogueado && $municipioUsuarioLogueado != 0) {
                        $where .= " WHERE u.tbl_municipio_id = :municipio ";
                        $params[':municipio'] = $municipioUsuarioLogueado;
                    } else if (in_array($userType, $roles_secretaria) && $secretariaUsuarioLogueado && $secretariaUsuarioLogueado != 0) {

                        $where .= " WHERE u.tbl_secretarias_id = :secretaria ";
                        $params[':secretaria'] = $secretariaUsuarioLogueado;
                    } else {
                        $where .= " WHERE u.id = 0 ";
                    }
                }


                $table_roles = $db->getTable('tbl_roles');

                if (!empty($searchValue)) {
                    $searchCondition = " (u.nombre LIKE :search OR u.apellido LIKE :search OR u.tipo LIKE :search OR r.name LIKE :search OR s.secretaria LIKE :search OR u.nickname LIKE :search) ";
                    if ($where) {
                        $where .= " AND " . $searchCondition;
                    } else {
                        $where = " WHERE " . $searchCondition;
                    }
                    $params[':search'] = "%" . $searchValue . "%";
                }

                $stmtTotal = $pdo->prepare("SELECT COUNT(*) as total FROM $table");
                $stmtTotal->execute();
                $recordsTotal = $stmtTotal->fetchColumn();

                $sqlFiltered = "SELECT COUNT(*) FROM $table u
                    LEFT JOIN $table_secretaria s ON u.tbl_secretarias_id = s.id
                    LEFT JOIN $table_roles r ON u.role_id = r.id
                    $where";
                $stmtFiltered = $pdo->prepare($sqlFiltered);
                $stmtFiltered->execute($params);
                $recordsFiltered = $stmtFiltered->fetchColumn();


                $sql = "SELECT u.id, u.nombre, u.apellido, u.tipo, u.role_id,
                               COALESCE(r.name, u.tipo) AS rol_nombre,
                               s.secretaria, u.nickname, u.habilitado, u.img, u.email, u.dtcreate 
                        FROM $table u 
                        LEFT JOIN $table_secretaria s ON u.tbl_secretarias_id = s.id 
                        LEFT JOIN $table_roles r ON u.role_id = r.id
                        $where 
                        ORDER BY u.id DESC 
                        LIMIT :start, :length";

                $stmt = $pdo->prepare($sql);
                foreach ($params as $key => $value) {

                    if (strpos($key, 'search') !== false) {
                        $stmt->bindValue($key, $value);
                    } else {
                        $stmt->bindValue($key, $value, is_int($value) ? PDO::PARAM_INT : PDO::PARAM_STR);
                    }
                }
                $stmt->bindValue(':start', (int)$start, PDO::PARAM_INT);
                $stmt->bindValue(':length', (int)$length, PDO::PARAM_INT);
                $stmt->execute();
                $result = $stmt->fetchAll(PDO::FETCH_ASSOC);


                $data = [];
                foreach ($result as $row) {
                    $data[] = [
                        'id' => $row['id'],
                        'nombre' => $row['nombre'],
                        'apellido' => $row['apellido'],
                        'tipo' => $row['rol_nombre'],
                        'role_id' => $row['role_id'] ?? null,
                        'secretaria' => $row['secretaria'],
                        'email' => $row['nickname'],
                        'habilitado' => $row['habilitado'] ? 'Sí' : 'No',
                        'dtcreate' => !empty($row['dtcreate']) ? date('Y-m-d H:i', strtotime($row['dtcreate'])) : '',
                        'foto' => !empty($row['img'])
                            ? '<img src="assets/img/admin/' . htmlspecialchars($row['img'], ENT_QUOTES, 'UTF-8') . '" width="40" height="40" class="rounded-circle" />'
                            : '',
                    ];
                }
                $db->closeConect();
                return [
                    'draw' => intval($draw),
                    'recordsTotal' => intval($recordsTotal),
                    'recordsFiltered' => intval($recordsFiltered),
                    'data' => $data
                ];
            }
        } catch (PDOException $e) {
            return [
                'error' => true,
                'message' => 'Error al obtener los datos',
                'details' => $e->getMessage()
            ];
        }
    }

    public static function editUser($data)
    {
        try {

            $db = new DbConection();
            $pdo = $db->openConect();
            $id = (int)$data;

            $q = "SELECT * FROM " . $db->getTable('tbl_usuarios') . " WHERE id = :id";
            $stmt = $pdo->prepare($q);
            $stmt->bindValue(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $stmt = null;
            $db->closeConect();

            return [
                'valid' => true,
                'data' => $result
            ];
        } catch (PDOException $e) {
            return [
                'valid' => false,
                'message' => 'Error al obtener los datos',
                'details' => $e->getMessage()
            ];
        }
    }

    public static function editUserSave($data, $files)
    {
        try {

            $id = isset($data['id']) ? intval($data['id']) : 0;
            $secretaria = isset($data['tbl_secretarias_id']) && is_numeric($data['tbl_secretarias_id'])
                ? (int) $data['tbl_secretarias_id']
                : 30;
            $nickname = isset($data['nickname']) ? trim($data['nickname']) : '';
            $hashpass = isset($data['hashpass']) ? trim($data['hashpass']) : '';
            $passConfirm = isset($data['hashpass1']) ? trim($data['hashpass1']) : '';
            $nombre = isset($data['nombre']) ? trim($data['nombre']) : '';
            $apellido = isset($data['apellido']) ? trim($data['apellido']) : '';
            $habilitado = isset($data['habilitado']) ? trim($data['habilitado']) : '';
            $email = isset($data['email']) ? trim($data['email']) : '';
            $departamento = isset($data['tbl_departamento_id']) ? intval($data['tbl_departamento_id']) : Util::getDepartamentoPrincipal();

            $tbl_municipio_id = isset($data['tbl_municipio_id']) && is_numeric($data['tbl_municipio_id'])
                ? (int) $data['tbl_municipio_id']
                : null;

            if (isset($files['imagen']['name']) && $files['imagen']['name']) {
                $uploadDir = '../../assets/img/admin/';
                if (!file_exists($uploadDir)) {
                    mkdir($uploadDir, 0755, true);
                }

                $allowedExts = ['jpg', 'jpeg', 'png', 'gif'];
                $ext = strtolower(pathinfo($files['imagen']['name'], PATHINFO_EXTENSION));

                if (in_array($ext, $allowedExts)) {
                    if (getimagesize($files['imagen']['tmp_name']) === false) {
                        return ['state' => false, 'message' => 'El archivo no es una imagen válida.'];
                    }

                    $maxSize = 5 * 1024 * 1024;
                    if ($files['imagen']['size'] > $maxSize) {
                        return ['state' => false, 'message' => 'La imagen supera el tamaño máximo permitido.'];
                    }

                    $imgName = uniqid() . '.' . $ext;
                    $imgTarget = $uploadDir . $imgName;

                    if (move_uploaded_file($files['imagen']['tmp_name'], $imgTarget)) {
                        $imgPath = $imgName;
                    } else {
                        return ['state' => false, 'message' => 'Error al guardar la imagen.'];
                    }
                } else {
                    return ['state' => false, 'message' => 'Formato de imagen no permitido.'];
                }
            }

            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                return ['state' => false, 'message' => 'El correo electrónico no es válido.'];
            }

            $requiredFields = [
                'id',
                'nombre',
                'apellido',
                'nickname',
                'habilitado'
            ];

            foreach ($requiredFields as $field) {
                if (empty($data[$field])) {
                    return [
                        'error' => true,
                        'message' => "El campo '$field' es obligatorio."
                    ];
                }
            }

            $roleIdReq = isset($data['role_id']) ? (int) $data['role_id'] : 0;
            if ($roleIdReq <= 0 && (empty($data['tipo']) || $data['tipo'] === 'Seleccione')) {
                return [
                    'error' => true,
                    'message' => "Debe seleccionar un rol válido."
                ];
            }

            $cambio = 0;
            $hashpass = trim($hashpass);
            $passConfirm = trim($passConfirm);
            if ($hashpass !== '' && $passConfirm !== '') {
                if ($hashpass !==  $passConfirm) {
                    return [
                        'error' => true,
                        'message' => "Las contraseñas no coinciden."
                    ];
                }
                $cambio = 1;
                $pass = Util::make_hash_pass($hashpass);
            }

            $db = new DbConection();
            $pdo = $db->openConect();

            $resolved = Role::resolveRoleFromRequest($pdo, $db, $data);
            $roleId = $resolved['role_id'];
            $tipo = $resolved['tipo'];
            if ($roleId === null || $tipo === '') {
                $db->closeConect();
                return ['error' => true, 'message' => 'Debe seleccionar un rol válido.'];
            }

            // Validar nickname duplicado solo si cambió realmente
            $stmtCur = $pdo->prepare("SELECT nickname FROM " . $db->getTable('tbl_usuarios') . " WHERE id = :id");
            $stmtCur->bindValue(':id', $id, PDO::PARAM_INT);
            $stmtCur->execute();
            $currentNickname = $stmtCur->fetchColumn();

            if ($currentNickname !== false && $currentNickname !== $nickname) {
                $stmtNick = $pdo->prepare("SELECT id FROM " . $db->getTable('tbl_usuarios') . " WHERE nickname = :nickname AND id != :id");
                $stmtNick->bindValue(':nickname', $nickname);
                $stmtNick->bindValue(':id', $id, PDO::PARAM_INT);
                $stmtNick->execute();
                if ($stmtNick->rowCount() > 0) {
                    $db->closeConect();
                    return ['state' => false, 'message' => 'El nombre de usuario (nickname) ya está registrado por otro usuario.'];
                }
            }

            $q = "UPDATE " . $db->getTable('tbl_usuarios') . "
            SET
                nombre = :nombre,
                apellido = :apellido,
                nickname = :nickname,
                tipo = :tipo,
                role_id = :role_id,
                tbl_secretarias_id = :secretaria,
                tbl_departamento_id = :departamento,
                tbl_municipio_id = :municipio,
                habilitado = :habilitado,
                email = :email";
            if (isset($imgPath)) {
                $q .= ", img = :img";
            }

            if ($cambio) {
                $q .= ", hashpass = :hashpass";
            }

            $q .= " WHERE id = :id";

            $stmt = $pdo->prepare($q);
            $stmt->bindValue(':id', $id, PDO::PARAM_INT);
            $stmt->bindValue(':nombre', $nombre);
            $stmt->bindValue(':apellido', $apellido);
            $stmt->bindValue(':nickname', $nickname);
            $stmt->bindValue(':tipo', $tipo);
            $stmt->bindValue(':role_id', $roleId, $roleId !== null ? PDO::PARAM_INT : PDO::PARAM_NULL);
            $stmt->bindValue(':secretaria', $secretaria, PDO::PARAM_INT);
            $stmt->bindValue(':departamento', $departamento, PDO::PARAM_INT);
            $stmt->bindValue(':municipio', $tbl_municipio_id, PDO::PARAM_INT);
            $stmt->bindValue(':habilitado', $habilitado);
            $stmt->bindValue(':email', $email);
            if (isset($imgPath)) {
                $stmt->bindValue(':img', $imgPath);
            }
            if ($cambio) {
                $stmt->bindValue(':hashpass', $pass);
            }

            $stmt->execute();

            $stmt = null;
            $db->closeConect();

            return [
                'state' => true,
                'message' => 'Usuario actualizado correctamente.'
            ];
        } catch (PDOException $e) {
            return [
                'state' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    public static function RestablecerContrasena($data)
    {

        try {


            $email = isset($data) ? trim($data) : '';
            if (empty($email)) {
                return [
                    'error' => true,
                    'message' => 'El campo email es obligatorio.'
                ];
            }

            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                return [
                    'error' => true,
                    'message' => 'El correo electrónico no es válido.'
                ];
            }

            $db = new DbConection();
            $pdo = $db->openConect();

            $q = "SELECT id, nombre FROM " . $db->getTable('tbl_usuarios') . " WHERE email = :email";
            $stmt = $pdo->prepare($q);
            $stmt->bindValue(':email', $email);
            $stmt->execute();
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$user) {
                $db->closeConect();
                return [
                    'error' => true,
                    'message' => 'No existe un usuario registrado con ese correo electrónico.'
                ];
            }

            $token = bin2hex(random_bytes(32));
            $expira = date('Y-m-d H:i:s', strtotime('+1 hour'));

            $q = "INSERT INTO " . $db->getTable('tbl_password_resets') . " (user_id, token, expires_at) VALUES (:user_id, :token, :expires_at)";
            $stmt = $pdo->prepare($q);
            $stmt->bindValue(':user_id', $user['id'], PDO::PARAM_INT);
            $stmt->bindValue(':token', $token);
            $stmt->bindValue(':expires_at', $expira);
            $stmt->execute();

            require '../../vendor/autoload.php';

            $mail = new PHPMailer(true);
            $mail->isSMTP();
            $mail->Host       = 'smtp.hostinger.com';
            $mail->SMTPAuth   = true;
            $mail->Username   = 'email@spidersoftware.co';
            $mail->Password   = 'Marti3933++$$++';
            $mail->SMTPSecure = 'ssl';
            $mail->Port       = 465;


            $mail->setFrom('email@spidersoftware.co', 'Restablecer Contraseña');
            $mail->addAddress($email, $user['nombre']);


            $resetLink = "http://{$_SERVER['HTTP_HOST']}/gobernacion/actualiza-contrasena.html?token=$token";
            $mail->isHTML(true);
            $mail->CharSet = 'UTF-8';
            $mail->Encoding = 'base64';
            $mail->Subject = "Restablecimiento de contraseña - Gobernación de Santander";

            $mail->Body = '
                            <!DOCTYPE html>
                            <html lang="es">
                            <head>
                                <meta charset="UTF-8">
                                <style>
                                .container {
                                    max-width: 600px;
                                    margin: auto;
                                    font-family: "Segoe UI", sans-serif;
                                    border: 1px solid #e0e0e0;
                                    border-radius: 8px;
                                    padding: 20px;
                                    background-color: #ffffff;
                                    color: #333;
                                }
                                .header {
                                    background-color: #234162;
                                    color: white;
                                    padding: 15px;
                                    text-align: center;
                                    border-radius: 8px 8px 0 0;
                                }
                                .header h1 {
                                    margin: 0;
                                    font-size: 20px;
                                }
                                .content {
                                    padding: 20px 10px;
                                }
                                .content p {
                                    line-height: 1.6;
                                }
                                .btn {
                                    display: inline-block;
                                    margin-top: 20px;
                                    padding: 12px 20px;
                                    background-color: #00796b;
                                    color: white;
                                    text-decoration: none;
                                    border-radius: 6px;
                                    font-weight: bold;
                                }
                                .footer {
                                    margin-top: 30px;
                                    font-size: 12px;
                                    text-align: center;
                                    color: #888;
                                }
                                </style>
                            </head>
                            <body>
                                <div class="container">
                                <div class="header">
                                    <h1>Gobernación de Santander</h1>
                                </div>
                                <div class="content">
                                    <p>Hola <strong>' . htmlspecialchars($user['nombre']) . '</strong>,</p>
                                    <p>Hemos recibido una solicitud para restablecer tu contraseña. Haz clic en el botón a continuación para establecer una nueva:</p>
                                    <p style="text-align: center;">
                                    <a class="btn" href="' . $resetLink . '">Restablecer contraseña</a>
                                    </p>
                                    <p>Este enlace expirará en 1 hora. Si no solicitaste este cambio, puedes ignorar este mensaje.</p>
                                    <p>Cordialmente,<br>Equipo de soporte - Accion Unificada, <br> Gobernación de Santander</p>
                                </div>
                                <div class="footer">
                                    Este mensaje fue enviado automáticamente. Por favor, no respondas a este correo.
                                </div>
                                </div>
                            </body>
                            </html>';


            $sent = $mail->send();
            if (!$sent) {
                return [
                    'state' => false,
                    'message' => 'Error al enviar el correo: ' . $mail->ErrorInfo
                ];
            }

            $db->closeConect();
            /* email@spidersoftware.co
            Marti3933++$$++ */

            return [
                'state' => true,
                'message' => 'Se ha enviado un correo con instrucciones para restablecer la contraseña.'
            ];
        } catch (PDOException $e) {
            return [
                'state' => false,
                'message' => 'Error al restablecer la contraseña ' . $e->getMessage()
            ];
        }
    }

    public static function actualizaContrasena($data)
    {
        try {
            $token = isset($data['token']) ? trim($data['token']) : '';
            $password = isset($data['password']) ? trim($data['password']) : '';
            $confirm = isset($data['confirm']) ? trim($data['confirm']) : '';

            if (empty($token)) {
                return [
                    'state' => false,
                    'message' => 'Token inválido.'
                ];
            }

            if (empty($password) || empty($confirm)) {
                return [
                    'state' => false,
                    'message' => 'Los campos de contraseña no pueden estar vacíos.'
                ];
            }

            if (strlen($password) < 4) {
                return [
                    'state' => false,
                    'message' => 'La contraseña debe tener al menos 4 caracteres.'
                ];
            }


            if ($password !== $confirm) {
                return [
                    'state' => false,
                    'message' => 'Las contraseñas no coinciden.'
                ];
            }

            $db = new DbConection();
            $pdo = $db->openConect();

            $stmt = $pdo->prepare("SELECT user_id, expires_at FROM " . $db->getTable('tbl_password_resets') . "  WHERE token = ?");
            $stmt->execute([$token]);
            $resetData = $stmt->fetch();


            if (!$resetData || strtotime($resetData['expires_at']) < time()) {
                return [
                    'state' => false,
                    'message' => 'El enlace ha expirado o no es válido.'
                ];
            }

            $id = $resetData['user_id'];
            $hashpass = Util::make_hash_pass($password);
            $fecha = date('Y-m-d H:i:s');

            $stmt = $pdo->prepare("UPDATE " . $db->getTable('tbl_usuarios') . " SET hashpass = :hashpass, dtcreate = :dtcreate WHERE id = :id");
            $stmt->bindValue(':hashpass', $hashpass);
            $stmt->bindValue(':id', $id, PDO::PARAM_INT);
            $stmt->bindValue(':dtcreate', $fecha);
            $stmt->execute();

            if ($stmt->rowCount() > 0) {
                $db->closeConect();
                return [
                    'state' => true,
                    'message' => 'Contraseña actualizada correctamente.'
                ];
            } else {
                return [
                    'state' => false,
                    'message' => 'No se pudo actualizar la contraseña. Por favor, inténtalo de nuevo.'
                ];
            }
        } catch (PDOException $e) {
            return [
                'state' => false,
                'message' => 'Error al actualizar la contraseña: ' . $e->getMessage()
            ];
        }
    }

    public static function getPaginated($limit, $offset)
    {
        $db = Database::getConnection(); // Conexión a la base de datos (PDO)
        $query = "SELECT SQL_CALC_FOUND_ROWS * FROM tbl_usuarios LIMIT :offset, :limit";
        $stmt = $db->prepare($query);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();

        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $total = $db->query("SELECT FOUND_ROWS()")->fetchColumn();

        return [
            'data' => $data,
            'total' => $total,
        ];
    }

    public static function available($rqst)
    {
        $nickname = isset($rqst['nickname']) ? ($rqst['nickname']) : '';
        $id = isset($rqst['id']) ? ($rqst['id']) : 0;

        $db = new DbConection();
        $pdo = $db->openConect();

        $q = "SELECT * FROM " . $db->getTable('tbl_usuarios') . " WHERE nickname = :nickname";

        if ($id > 0) {
            $q = "SELECT * FROM " . $db->getTable('tbl_usuarios') . " WHERE nickname = :nickname AND id != :id";
            $result = $pdo->prepare($q);
            $arr = array();
            $arrparam = array(":nickname" => $nickname, ":id" => $id);
        } else {
            $q = "SELECT * FROM " . $db->getTable('tbl_usuarios') . " WHERE nickname = :nickname";
            $result = $pdo->prepare($q);
            $arr = array();
            $arrparam = array(":nickname" => $nickname);
        }

        if ($result->execute($arrparam)) {
            foreach ($result as $valor) {
                $arr[] = $valor;
            }
            if (count($arr) > 0) {
                $arrjson = Util::error_general('El email de usuario ya existe');
            } else {
                $arrjson = array('output' => array('valid' => true, 'response' => 'available'));
            }
        } else {
            $arrjson = Util::error_general('');
        }
        $db->closeConect();
        return $arrjson;
    }

    public static function login($rqst)
    {
        // Obtención de parámetros de entrada
        $nickname = isset($rqst['nickname']) ? $rqst['nickname'] : '';
        $hashpass = isset($rqst['hashpass']) ? $rqst['hashpass'] : '';

        $db = new DbConection();
        $pdo = $db->openConect();

        // Si la contraseña tiene más de 2 caracteres, se realiza el hash
        if (strlen($hashpass) > 2) {
            $hashpass = Util::make_hash_pass($hashpass);
        }

        // Consulta para verificar usuario y contraseña
        $q = "SELECT * FROM " . $db->getTable('tbl_usuarios') . " WHERE nickname = :nickname AND hashpass = :hashpass AND habilitado='si'";
        $arrparam = [":nickname" => $nickname, ":hashpass" => $hashpass];

        $result = $pdo->prepare($q);
        if ($result->execute($arrparam)) {
            $arr = $result->fetchAll(PDO::FETCH_ASSOC);

            if (count($arr) > 0) {
                $user = $arr[0];
                $user['application'][] = Util::get_app_id();

                require_once __DIR__ . '/Authorization.php';
                $auth = Authorization::loadUserAuth($pdo, $db, (int) $user['id'], (string) $user['tipo']);

                $route = self::validateUserAndReturnHomePageSecretaria($user['tbl_secretarias_id'], $user['tipo']);

                $user['permisos'] = $auth['legacy_ids'];
                $user['permission_keys'] = $auth['permission_keys'];
                $user['role'] = $auth['role'];
                $arrjson = [
                    'output' => [
                        'valid' => true,
                        'response' => [$user],
                        'permisos' => $user['permisos'],
                        'permission_keys' => $user['permission_keys'],
                        'route' => $route
                    ]
                ];

                // Guardar información en la sesión
                Util::trace_session_user(['usuarioId' => $user['id']]);
            } else {
                $arrjson = Util::error_wrong_data_login();
            }
        } else {
            $arrjson = Util::error_wrong_data_login();
        }

        $db->closeConect();
        return $arrjson;
    }

    /**
     * Validar usuario y devolver la página de inicio correspondiente de secretaria
     * Funcionalidad:
     * - Verificar si el usuario tiene permisos para acceder a la aplicación.
     * - Redirigir al usuario a la página de inicio correspondiente según su rol.
     */
    public static function validateUserAndReturnHomePageSecretaria($secretariaId, $userType)
    {
        $secretariaId = intval($secretariaId);
        if ($secretariaId <= 0) {
            return 'dashboard.php';
        }

        $isSecretario = ($userType === Util::Secretario_Despacho() || $userType === Util::Auxiliar() || $userType === Util::Auxiliar_secret_gob());
        $route = 'dashboard.php';

        if ($isSecretario) {
            if ($secretariaId == Util::getSecretariaIdHacienda()) {
                $route = 'secretaria.php?depto_id=21&secretaria=4&accion=Operativos+Contrabando+licores';
            }
            else {
                try {
                    $db = new DbConection();
                    $pdo = $db->openConect();
    
                    // Consultar directamente la secretaría con el ID específico
                    $query = "SELECT id FROM " . $db->getTable('tbl_secretarias') . " WHERE id = :secretariaId";
                    $stmt = $pdo->prepare($query);
                    $stmt->bindValue(':secretariaId', $secretariaId, PDO::PARAM_INT);
                    $stmt->execute();
                    $result = $stmt->fetch(PDO::FETCH_ASSOC);

                    if ($result) {
                        $route = 'secretaria.php?depto_id=' . Util::getIdentificadorDepartamentoPrincipal() . '&secretaria=' . $secretariaId;
                    } else {
                        // Si no se encuentra, se redirige al dashboard general
                        $route = 'dashboard.php';
                    }
                } catch (PDOException $e) {
                    $route = 'dashboard.php';
                }
            }
        }
        return $route;
    }

    public static function save($rqst, $files)
    {
        $id = isset($rqst['id']) ? intval($rqst['id']) : 0;
        $tbl_secretarias_id = isset($rqst['tbl_secretarias_id']) && is_numeric($rqst['tbl_secretarias_id'])
            ? (int) $rqst['tbl_secretarias_id']
            : '';
        $nickname = isset($rqst['nickname']) ? ($rqst['nickname']) : '';
        $hashpass = isset($rqst['hashpass']) ? ($rqst['hashpass']) : '';
        $nombre = isset($rqst['nombre']) ? ($rqst['nombre']) : '';
        $apellido = isset($rqst['apellido']) ? ($rqst['apellido']) : '';
        $habilitado = isset($rqst['habilitado']) ? ($rqst['habilitado']) : '';
        $email = isset($rqst['email']) ? ($rqst['email']) : '';
        $tbl_departamento_id = isset($rqst['departamentoId']) ? intval($rqst['departamentoId']) : Util::getDepartamentoPrincipal();
        $tbl_municipio_id = isset($rqst['tbl_municipio_id']) && is_numeric($rqst['tbl_municipio_id'])
            ? (int) $rqst['tbl_municipio_id']
            : null;
        $imgPath = '';
        if (isset($files['imagen']['name'])) {
            $uploadDir = '../../assets/img/admin/';
            if (!file_exists($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }

            $allowedExts = ['jpg', 'jpeg', 'png', 'gif'];
            $ext = strtolower(pathinfo($files['imagen']['name'], PATHINFO_EXTENSION));

            if (in_array($ext, $allowedExts)) {
                if (getimagesize($files['imagen']['tmp_name']) === false) {
                    return ['state' => false, 'message' => 'El archivo no es una imagen válida.'];
                }

                $maxSize = 5 * 1024 * 1024;
                if ($files['imagen']['size'] > $maxSize) {
                    return ['state' => false, 'message' => 'La imagen supera el tamaño máximo permitido.'];
                }

                $imgName = uniqid() . '.' . $ext;
                $imgTarget = $uploadDir . $imgName;

                if (move_uploaded_file($files['imagen']['tmp_name'], $imgTarget)) {
                    $imgPath = $imgName;
                } else {
                    return ['state' => false, 'message' => 'Error al guardar la imagen.'];
                }
            } else {
                return ['state' => false, 'message' => 'Formato de imagen no permitido.'];
            }
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return ['state' => false, 'message' => 'El correo electrónico no es válido.'];
        }

        $db = new DbConection();
        $pdo = $db->openConect();

        $resolved = Role::resolveRoleFromRequest($pdo, $db, $rqst);
        $roleId = $resolved['role_id'];
        $tipo = $resolved['tipo'];
        if ($roleId === null || $tipo === '') {
            $db->closeConect();
            return ['state' => false, 'message' => 'Debe seleccionar un rol válido.'];
        }

        if (strlen($hashpass) > 2) {
            $hashpass = Util::make_hash_pass($hashpass);
        }
        if ($tbl_departamento_id == 0) {
            return  Util::error_general(' Identificador del departamento no está presente.');
        }


        $stmt = $pdo->prepare("SELECT * FROM " . $db->getTable('tbl_usuarios') . " WHERE email = :email");
        $stmt->bindValue(':email', $email);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            return [
                'state' => false,
                'message' => 'El correo electrónico ya está registrado.'
            ];
        }

        // Validar nickname duplicado
        $stmtNick = $pdo->prepare("SELECT id FROM " . $db->getTable('tbl_usuarios') . " WHERE nickname = :nickname");
        $stmtNick->bindValue(':nickname', $nickname);
        $stmtNick->execute();
        if ($stmtNick->rowCount() > 0) {
            return [
                'state' => false,
                'message' => 'El nombre de usuario (nickname) ya está registrado.'
            ];
        }

        if ($nombre != "" && $apellido != "" && $tipo != "" && $tbl_departamento_id > 0) {
            if ($tipo == "Administrador") {
                $tbl_secretarias_id = 30; // Administrador siempre en la secretaria de gobernación
            }
            $q = "INSERT INTO " . $db->getTable('tbl_usuarios') . " (dtcreate, nickname, hashpass, nombre, apellido, tipo, role_id, img, tbl_secretarias_id, habilitado, tbl_departamento_id, tbl_municipio_id, email ) 
                VALUES ( " . Util::date_now_server() . ", :nickname, :hashpass, :nombre, :apellido, :tipo, :role_id, :img, :tbl_secretarias_id, :habilitado, :tbl_departamento_id, :tbl_municipio_id, :email)";
            $result = $pdo->prepare($q);
            $arrparam = array(
                ':nickname' => $nickname,
                ':hashpass' => $hashpass,
                ':nombre' => $nombre,
                ':apellido' => $apellido,
                ':tipo' => $tipo,
                ':role_id' => $roleId,
                ':img' => $imgPath,
                ':tbl_secretarias_id' => $tbl_secretarias_id,
                ':habilitado' => $habilitado,
                ':tbl_departamento_id' => $tbl_departamento_id,
                ':tbl_municipio_id' => $tbl_municipio_id,
                ':email' => $email
            );
            if ($result->execute($arrparam)) {
                $newUserId = $pdo->lastInsertId();

                require '../../vendor/autoload.php';

                $mail = new PHPMailer(true);
                $mail = new PHPMailer(true);
                $mail->isSMTP();
                $mail->Host       = 'smtp.hostinger.com';
                $mail->SMTPAuth   = true;
                $mail->Username   = 'email@spidersoftware.co';
                $mail->Password   = 'Marti3933++$$++';
                $mail->SMTPSecure = 'ssl';
                $mail->Port       = 465;


                $mail->setFrom('email@spidersoftware.co', 'Bienvenido');
                $mail->addAddress($email, $nombre . ' ' . $apellido);

                $mail->isHTML(true);
                $mail->CharSet = 'UTF-8';
                $mail->Encoding = 'base64';
                $mail->Subject = "Bienvenido - Gobernación de Santander";

                $mail->Body = '
                            <!DOCTYPE html>
                            <html lang="es">
                            <head>
                                <meta charset="UTF-8">
                                <style>
                                .container {
                                    max-width: 600px;
                                    margin: auto;
                                    font-family: "Segoe UI", sans-serif;
                                    border: 1px solid #e0e0e0;
                                    border-radius: 8px;
                                    padding: 20px;
                                    background-color: #ffffff;
                                    color: #333;
                                }
                                .header {
                                    background-color: #234162;
                                    color: white;
                                    padding: 15px;
                                    text-align: center;
                                    border-radius: 8px 8px 0 0;
                                }
                                .header h1 {
                                    margin: 0;
                                    font-size: 20px;
                                }
                                .content {
                                    padding: 20px 10px;
                                }
                                .content p {
                                    line-height: 1.6;
                                }
                                .btn {
                                    display: inline-block;
                                    margin-top: 20px;
                                    padding: 12px 20px;
                                    background-color: #00796b;
                                    color: white;
                                    text-decoration: none;
                                    border-radius: 6px;
                                    font-weight: bold;
                                }
                                .footer {
                                    margin-top: 30px;
                                    font-size: 12px;
                                    text-align: center;
                                    color: #888;
                                }
                                </style>
                            </head>
                            <body>
                                <div class="container">
                                <div class="header">
                                    <h1>Gobernación de Santander</h1>
                                </div>
                                <div class="content">
                                    <p>¡Bienvenido a la Plataforma de Acción Unificada! 🎉
                                    <br><br>
                                    Es un gusto tenerte con nosotros.
                                    A partir de ahora, estás más cerca de la Administración Departamental, con acceso directo a herramientas que facilitan la gestión, la planificación y la toma de decisiones en tu municipio.
                                    <br><br>
                                    Aquí podrás trabajar de manera más eficiente, conectarte con las diferentes secretarías, hacer seguimiento a proyectos y contribuir activamente al desarrollo territorial.
                                    <br><br>
                                    Gracias por ser parte del cambio.
                                    Juntos estamos construyendo un gobierno más digital, más cercano y más eficiente.
                                    </p>
                                    <br>
                                    <br>
                                    <p>Cordialmente,<br>Equipo de soporte - Accion Unificada, <br> Gobernación de Santander</p>
                                </div>
                                <div class="footer">
                                    Este mensaje fue enviado automáticamente. Por favor, no respondas a este correo.
                                </div>
                                </div>
                            </body>
                            </html>';


                $sent = $mail->send();
                if (!$sent) {
                    return ['state' => true, 'message' => 'Ocurrio un error al enviar el correo pero el usuario se creo con exito.'];
                }
                return ['state' => true, 'message' => 'Usuario guardado correctamente.'];
            } else {
                return ['state' => false, 'message' => 'Error al guardar el usuario.'];
            }
        } else {
            $arrjson = Util::error_missing_data();
        }

        $db->closeConect();
        return $arrjson;
    }

    public static function acttualizarPerfil($rqst)
    {
        $id = isset($rqst['id']) ? intval($rqst['id']) : 0;
        $nickname = isset($rqst['nickname']) ? ($rqst['nickname']) : '';
        $hashpass = isset($rqst['hashpass']) ? ($rqst['hashpass']) : '';
        $nombre = isset($rqst['nombre']) ? ($rqst['nombre']) : '';
        $apellido = isset($rqst['apellido']) ? ($rqst['apellido']) : '';
        $img = isset($_SESSION['file']['nombrearchivo']) ? ($_SESSION['file']['nombrearchivo']) : '';

        $db = new DbConection();
        $pdo = $db->openConect();

        if (strlen($hashpass) > 2) {
            $hashpass = Util::make_hash_pass($hashpass);
        }
        if ($id > 0) {
            //actualiza la informacion
            $q0 = "SELECT id, img FROM " . $db->getTable('tbl_usuarios') . " WHERE id = " . $id;
            $result0 = $pdo->query($q0);
            if ($result0) {
                $table = $db->getTable('tbl_usuarios');
                $arrfieldscomma = array(
                    'nickname' => $nickname,
                    'hashpass' => $hashpass,
                    'nombre' => $nombre,
                    'apellido' => $apellido,
                    'img' => $img,
                );
                $arrfieldsnocomma = array('dtcreate' => Util::date_now_server());
                $q = Util::make_query_update($table, "id = '$id'", $arrfieldscomma, $arrfieldsnocomma);
                $result = $pdo->query($q);

                // Obtemos el valor de la imagen del producto
                $file = "";
                foreach ($result0 as $valor0) {
                    $file = $valor0['img'];
                }

                if (!$result) {
                    $arrjson = Util::error_general('Actualizando los datos del usuario');
                } else {
                    $arrjson = array('output' => array('valid' => true, 'id' => $id, 'img' => $file));
                    // Eliminamos el archivo anterior siempre y cuando se halla actualizado la imagen
                    if ($file != "" && file_exists("../../assets/img/admin/usuarios/" . $file)) {
                        unlink("../../assets/img/admin/usuarios/" . $file);
                    }
                }
            } else {
                $arrjson = Util::error_general();
            }
        }
        $db->closeConect();
        return $arrjson;
    }

    public static function search($rqst)
    {

        $search = isset($rqst['search']) ? ($rqst['search']) : '';

        $db = new DbConection();
        $pdo = $db->openConect();

        $q = "SELECT * FROM " . $db->getTable('tbl_usuarios') . " 
        WHERE nombre  LIKE '%$search%'  OR
            apellido  LIKE '%$search%' OR
            tipo  LIKE '%$search%' OR
            nickname  LIKE '%$search%' LIMIT 200 ";
        $result = $pdo->query($q);

        $arr = array();

        if ($result) {
            foreach ($result as $valor) {
                $arr[] = $valor;
            }
            $arrjson = array('output' => array('valid' => true, 'response' => $arr));
        } else {
            $arrjson = Util::error_no_result();
        }
        $db->closeConect();

        return $arrjson;
    }

    public static function deleteUser($rqst)
    {
        $sessionType = SessionData::getUserType();
        if ($sessionType !== Util::SuperAdministrador()) {
            return ['state' => false, 'message' => 'No tiene permisos para eliminar usuarios.'];
        }

        $id = isset($rqst['id']) ? intval($rqst['id']) : 0;
        if ($id <= 0) {
            return ['state' => false, 'message' => 'ID de usuario inválido.'];
        }

        $db = new DbConection();
        $pdo = $db->openConect();

        try {
            // Obtener datos del usuario antes de eliminar
            $qUser = "SELECT id, nombre, apellido, nickname, tipo, email, dtcreate FROM " . $db->getTable('tbl_usuarios') . " WHERE id = :id";
            $stmt = $pdo->prepare($qUser);
            $stmt->bindValue(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            $userData = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$userData) {
                return ['state' => false, 'message' => 'Usuario no encontrado.'];
            }

            $deletedBy = SessionData::getUserId();

            // Insertar en auditoría
            $qInsert = "INSERT INTO " . $db->getTable('tbl_usuarios_eliminados') . "
                (usuario_id, nombre, apellido, nickname, tipo, email, deleted_at, deleted_by)
                VALUES (:usuario_id, :nombre, :apellido, :nickname, :tipo, :email, NOW(), :deleted_by)";
            $stmt = $pdo->prepare($qInsert);
            $stmt->execute([
                ':usuario_id' => $userData['id'],
                ':nombre' => $userData['nombre'],
                ':apellido' => $userData['apellido'],
                ':nickname' => $userData['nickname'],
                ':tipo' => $userData['tipo'],
                ':email' => $userData['email'],
                ':deleted_by' => $deletedBy,
            ]);

            // Eliminar usuario (permisos legacy ya no se usan; role_id se elimina en cascada lógica)
            $tableUsers = $db->getTable('tbl_usuarios');
            $pdo->exec("DELETE FROM {$tableUsers} WHERE id = {$id}");

            $db->closeConect();
            return ['state' => true, 'message' => 'Usuario eliminado correctamente.'];
        } catch (PDOException $e) {
            $db->closeConect();
            return ['state' => false, 'message' => 'Error al eliminar: ' . $e->getMessage()];
        }
    }

    public static function getDeletedUsers($rqst)
    {
        $sessionType = SessionData::getUserType();
        if ($sessionType !== Util::SuperAdministrador()) {
            return ['state' => false, 'message' => 'No tiene permisos para ver esta información.'];
        }

        $data = $rqst ?? [];
        $draw = $data['draw'] ?? 1;
        $start = $data['start'] ?? 0;
        $length = $data['length'] ?? 10;
        $searchValue = $data['search']['value'] ?? '';

        $db = new DbConection();
        $pdo = $db->openConect();

        try {
            $table = $db->getTable('tbl_usuarios_eliminados');
            $tableUsers = $db->getTable('tbl_usuarios');

            $where = '';
            $params = [];

            if (!empty($searchValue)) {
                $where = " WHERE e.nombre LIKE :search OR e.apellido LIKE :search OR e.nickname LIKE :search OR e.tipo LIKE :search";
                $params[':search'] = "%{$searchValue}%";
            }

            $stmtTotal = $pdo->query("SELECT COUNT(*) FROM {$table}");
            $recordsTotal = $stmtTotal->fetchColumn();

            $sqlFiltered = "SELECT COUNT(*) FROM {$table} e {$where}";
            $stmtFiltered = $pdo->prepare($sqlFiltered);
            $stmtFiltered->execute($params);
            $recordsFiltered = $stmtFiltered->fetchColumn();

            $sql = "SELECT e.*, 
                    COALESCE(u.nombre, 'Eliminado') AS eliminado_por_nombre
                    FROM {$table} e
                    LEFT JOIN {$tableUsers} u ON e.deleted_by = u.id
                    {$where}
                    ORDER BY e.deleted_at DESC
                    LIMIT :start, :length";

            $stmt = $pdo->prepare($sql);
            foreach ($params as $key => $value) {
                $stmt->bindValue($key, $value);
            }
            $stmt->bindValue(':start', (int)$start, PDO::PARAM_INT);
            $stmt->bindValue(':length', (int)$length, PDO::PARAM_INT);
            $stmt->execute();
            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $data = [];
            foreach ($result as $row) {
                $data[] = [
                    'id' => $row['id'],
                    'usuario_id' => $row['usuario_id'],
                    'nombre' => $row['nombre'],
                    'apellido' => $row['apellido'],
                    'nickname' => $row['nickname'],
                    'tipo' => $row['tipo'],
                    'email' => $row['email'],
                    'deleted_at' => $row['deleted_at'],
                    'eliminado_por' => $row['eliminado_por_nombre'],
                ];
            }

            $db->closeConect();
            return [
                'draw' => intval($draw),
                'recordsTotal' => intval($recordsTotal),
                'recordsFiltered' => intval($recordsFiltered),
                'data' => $data
            ];
        } catch (PDOException $e) {
            $db->closeConect();
            return [
                'error' => true,
                'message' => 'Error al obtener datos: ' . $e->getMessage()
            ];
        }
    }

    public static function getDuplicatedUsers($rqst)
    {
        $sessionType = SessionData::getUserType();
        if ($sessionType !== Util::SuperAdministrador()) {
            return ['state' => false, 'message' => 'No tiene permisos.'];
        }

        $data = $rqst ?? [];
        $draw = $data['draw'] ?? 1;
        $start = $data['start'] ?? 0;
        $length = $data['length'] ?? 10;
        $searchValue = $data['search']['value'] ?? '';

        $db = new DbConection();
        $pdo = $db->openConect();

        try {
            $table = $db->getTable('tbl_usuarios');
            $table_secretaria = $db->getTable('tbl_secretarias');
            $table_roles = $db->getTable('tbl_roles');

            $where = '';
            $params = [];

            if (!empty($searchValue)) {
                $where = " AND (u.nombre LIKE :search OR u.apellido LIKE :search OR u.nickname LIKE :search OR u.tipo LIKE :search OR r.name LIKE :search)";
                $params[':search'] = "%{$searchValue}%";
            }

            $stmtTotal = $pdo->query("SELECT COUNT(*) FROM {$table}");
            $recordsTotal = $stmtTotal->fetchColumn();

            $sqlFiltered = "SELECT COUNT(*) FROM {$table} u
                LEFT JOIN {$table_roles} r ON u.role_id = r.id
                INNER JOIN (
                    SELECT nickname FROM {$table} GROUP BY nickname HAVING COUNT(*) > 1
                ) dup ON u.nickname = dup.nickname
                {$where}";
            $stmtFiltered = $pdo->prepare($sqlFiltered);
            $stmtFiltered->execute($params);
            $recordsFiltered = $stmtFiltered->fetchColumn();

            $sql = "SELECT u.id, u.nombre, u.apellido, u.nickname, u.tipo, u.habilitado, u.dtcreate,
                           COALESCE(r.name, u.tipo) AS rol_nombre,
                           COALESCE(s.secretaria, 'N/A') AS secretaria, u.tbl_municipio_id
                    FROM {$table} u
                    LEFT JOIN {$table_secretaria} s ON u.tbl_secretarias_id = s.id
                    LEFT JOIN {$table_roles} r ON u.role_id = r.id
                    INNER JOIN (
                        SELECT nickname FROM {$table} GROUP BY nickname HAVING COUNT(*) > 1
                    ) dup ON u.nickname = dup.nickname
                    {$where}
                    ORDER BY u.nickname ASC, u.id ASC
                    LIMIT :start, :length";

            $stmt = $pdo->prepare($sql);
            foreach ($params as $key => $value) {
                $stmt->bindValue($key, $value);
            }
            $stmt->bindValue(':start', (int)$start, PDO::PARAM_INT);
            $stmt->bindValue(':length', (int)$length, PDO::PARAM_INT);
            $stmt->execute();
            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $data = [];
            foreach ($result as $row) {
                $data[] = [
                    'id' => $row['id'],
                    'nickname' => $row['nickname'],
                    'nombre' => $row['nombre'],
                    'apellido' => $row['apellido'],
                    'tipo' => $row['rol_nombre'] ?? $row['tipo'],
                    'habilitado' => $row['habilitado'] ? 'Sí' : 'No',
                    'secretaria' => $row['secretaria'],
                    'dtcreate' => !empty($row['dtcreate']) ? date('Y-m-d H:i', strtotime($row['dtcreate'])) : '',
                ];
            }

            $db->closeConect();
            return [
                'draw' => intval($draw),
                'recordsTotal' => intval($recordsTotal),
                'recordsFiltered' => intval($recordsFiltered),
                'data' => $data
            ];
        } catch (PDOException $e) {
            $db->closeConect();
            return [
                'error' => true,
                'message' => 'Error al obtener datos: ' . $e->getMessage()
            ];
        }
    }
}
