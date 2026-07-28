<?php

/**
 * Clase que contiene todas las operaciones utilizadas sobre la base de datos
 * @author SPIDERSOFTWARE
 */
class SedesEducativas
{

    public function __construct() {}

    /**
     * Obtiene el total de sedes educativas con estado 'Antiguo_Activo' agrupadas por provincia.
     * @param array $rqst
     * @return array
     */
    public static function getSedesEducativasConProblemas($rqst)
    {
        $db = new DbConection();
        $pdo = $db->openConect();

        $table = $db->getTable('tbl_pae');
        $query = "SELECT provincia, COUNT(*) AS total
                FROM $table
                WHERE estado_sede = :estado
                GROUP BY provincia
                ORDER BY provincia";
        $stmt = $pdo->prepare($query);
        $estado = 'Antiguo_Activo';
        $stmt->bindParam(':estado', $estado, PDO::PARAM_STR);
        $stmt->execute();

        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $response = $data ? ['output' => ['valid' => true, 'response' => $data]] : Util::error_no_result();

        $db->closeConect();
        return $response;
    }


    public static function getAll($rqst)
    {
        $id = isset($rqst['id']) ? intval($rqst['id']) : 0;

        $db = new DbConection();
        $pdo = $db->openConect();

        $query = "SELECT * FROM " . $db->getTable('tbl_sede_educativa');
        $stmt = $pdo->prepare($query);
        if ($id > 0) {
            $query = "SELECT * FROM " . $db->getTable('tbl_sede_educativa') . " WHERE id = :id";
            $stmt = $pdo->prepare($query);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        }
        $stmt->execute();

        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $response = $data ? ['output' => ['valid' => true, 'response' => $data]] : Util::error_no_result();

        $db->closeConect();
        return $response;
    }

    /**
     * Obtiene los datos de la sede educativa
     * @param array $rqst
     * @return array
     */
    public static function getSedeEducativaDatos($rqst)
    {
        $id = isset($rqst['id']) ? intval($rqst['id']) : 0;

        if ($id > 0) {

            $db = new DbConection();
            $pdo = $db->openConect();


            $query = "SELECT tbl_pae.*, 
                tbl_sede_educativa.nombre, 
                tbl_sede_educativa.codigo_sede, 
                tbl_instituciones_educativas.id as institucion_id, 
                tbl_sede_educativa.longitud, 
                tbl_sede_educativa.latitud, 
                tbl_sede_educativa.id as sede_educativa_id, 
                tbl_sede_educativa.icono, 
                tbl_instituciones_educativas.nombre_institucion, 
                tbl_instituciones_educativas.codigo_institucion, 
                tbl_ciudades_accion_unificada.codigo_muncipio, 
                tbl_vereda.nombre_vereda,  
                tbl_usuarios.nombre AS Usuario
            FROM " . $db->getTable('tbl_pae') . "
            LEFT JOIN " . $db->getTable('tbl_instituciones_educativas') . "  ON tbl_pae.tbl_instituciones_educativas_id = tbl_instituciones_educativas.id 
            LEFT JOIN " . $db->getTable('tbl_sede_educativa') . "  ON tbl_pae.tbl_sede_educativa_id = tbl_sede_educativa.id 
            LEFT JOIN " . $db->getTable('tbl_ciudades_accion_unificada') . "  ON tbl_sede_educativa.tbl_ciudad_id = tbl_ciudades_accion_unificada.codigo_muncipio 
            LEFT JOIN " . $db->getTable('tbl_vereda') . " ON tbl_sede_educativa.tbl_vereda_id = tbl_vereda.id
            LEFT JOIN " . $db->getTable('tbl_usuarios') . " ON tbl_pae.tbl_usuario_id = tbl_usuarios.id  
            WHERE tbl_sede_educativa.id = :id 
            GROUP BY tbl_sede_educativa.id";
            $stmt = $pdo->prepare($query);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);

            $stmt->execute();

            $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $response = $data ? ['output' => ['valid' => true, 'response' => $data]] : Util::error_no_result();

            $db->closeConect();
            return $response;
        } else {
            return  Util::error_no_result();
        }
    }
    public static function getInstitucionesByMunicipio($rqst)
    {
        $codigoMunicipio = isset($rqst['codigo_municipio']) ? trim($rqst['codigo_municipio']) : '';

        if (!empty($codigoMunicipio)) {
            $db = new DbConection();
            $pdo = $db->openConect();

            $query = "SELECT 
                        ie.id AS institucion_id,
                        ie.nombre_institucion AS nombre,
                        ie.codigo_institucion
                    FROM " . $db->getTable('tbl_instituciones_educativas') . " ie
                    JOIN " . $db->getTable('tbl_sede_educativa') . " se ON se.tbl_instituciones_educativas_id = ie.id
                    JOIN " . $db->getTable('tbl_ciudades_accion_unificada') . " m ON se.tbl_ciudad_id = m.codigo_muncipio
                    WHERE m.codigo_muncipio = :codigo_municipio
                    GROUP BY ie.id
                    ORDER BY ie.nombre_institucion ASC";

            $stmt = $pdo->prepare($query);
            $stmt->bindParam(':codigo_municipio', $codigoMunicipio);
            $stmt->execute();

            $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $response = $data ? ['output' => ['valid' => true, 'response' => $data]] : Util::error_no_result();

            $db->closeConect();
            return $response;
        } else {
            return Util::error_no_result();
        }
    }
public static function getDatosRectorByInstitucionYSede($rqst)
{
    $institucion_id = $rqst['institucion_id'];
    $sede_id = $rqst['sede_id'];

    $sql = "SELECT p.rector, p.cc, p.tel 
            FROM tbl_pae p
            INNER JOIN tbl_instituciones_educativas i ON p.tbl_instituciones_educativas_id = i.id
            INNER JOIN tbl_sede_educativa s ON s.tbl_instituciones_educativas_id = i.id
            WHERE i.id = ?
              AND s.id = ?
              AND p.rector IS NOT NULL 
              AND TRIM(p.rector) != '' 
              AND p.cc IS NOT NULL 
              AND TRIM(p.cc) != '' 
              AND p.tel IS NOT NULL 
              AND TRIM(p.tel) != ''
            LIMIT 1";

    $db = new DbConection();
    $pdo = $db->openConect();

    $stmt = $pdo->prepare($sql);
    $stmt->execute([$institucion_id, $sede_id]);
    $data = $stmt->fetch(PDO::FETCH_ASSOC);

    $db->closeConect();

    return $data ?: Util::error_no_result();
}

    /**
     * Obtiene las sedes educativas por municipio
     * @param array $rqst
     * @return array
     */
    public static function getSedeEducaticasByCodMunicipio($rqst)
    {
        $codigo_muncipio = isset($rqst['codigo_muncipio']) ? intval($rqst['codigo_muncipio']) : 0;

        if ($codigo_muncipio > 0) {

            $db = new DbConection();
            $pdo = $db->openConect();

            $query = "SELECT 
                    tbl_pae.rector, 
                    tbl_pae.cc, 
                    tbl_pae.tel, 
                    tbl_pae.sector, 
                    tbl_instituciones_educativas.id as institucion_id, 
                    tbl_instituciones_educativas.nombre_institucion, 
                    tbl_sede_educativa.nombre, 
                    tbl_sede_educativa.id as sede_educativa_id, 
                    tbl_ciudades.id municipio_id,
                    tbl_ciudades.codigo_muncipio, 
                    tbl_ciudades.municipio, 
                    tbl_vereda.nombre_vereda
                FROM " . $db->getTable('tbl_pae') . " 
                INNER JOIN " . $db->getTable('tbl_instituciones_educativas') . " 
                    ON tbl_pae.tbl_instituciones_educativas_id = tbl_instituciones_educativas.id 
                INNER JOIN " . $db->getTable('tbl_sede_educativa') . " 
                    ON tbl_instituciones_educativas.id = tbl_sede_educativa.tbl_instituciones_educativas_id
                LEFT JOIN " . $db->getTable('tbl_ciudades') . " 
                    ON tbl_sede_educativa.tbl_ciudad_id = tbl_ciudades.codigo_muncipio
                LEFT JOIN " . $db->getTable('tbl_vereda') . " 
                    ON tbl_sede_educativa.tbl_vereda_id = tbl_vereda.id
                WHERE tbl_sede_educativa.tbl_ciudad_id = :codigo_muncipio
                GROUP BY tbl_sede_educativa.id";
            $stmt = $pdo->prepare($query);
            $stmt->bindParam(':codigo_muncipio', $codigo_muncipio, PDO::PARAM_INT);

            $stmt->execute();

            $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $response = $data ? ['output' => ['valid' => true, 'response' => $data]] : Util::error_no_result();

            $db->closeConect();
            return $response;
        } else {
            return  Util::error_no_result();
        }
    }
}
