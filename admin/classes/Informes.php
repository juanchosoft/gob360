<?php

class Informes {

    public function __construct() {}

    public static function getAll($rqst) {
        $db = new DbConection();
        $pdo = $db->openConect();

        $id = isset($rqst['id']) ? intval($rqst['id']) : 0;

        $sql = "
            SELECT 
                informes.*, 
                tbl_factores.tipo, 
                tbl_factores.tipo_medicion, 
                tbl_factores.icono, 
                tbl_secretarias.secretaria, 
                tbl_ciudades_accion_unificada.municipio, 
                tbl_fotos_informes.ruta_imagen
            FROM " . $db->getTable('informes') . "
            INNER JOIN " . $db->getTable('tbl_ciudades_accion_unificada') . " 
                ON informes.tbl_municipio_id = tbl_ciudades_accion_unificada.codigo_muncipio
            INNER JOIN " . $db->getTable('tbl_factores') . " 
                ON informes.tbl_factor_id = tbl_factores.id
            INNER JOIN " . $db->getTable('tbl_secretarias') . " 
                ON tbl_factores.tbl_secretaria_id = tbl_secretarias.id
            INNER JOIN " . $db->getTable('tbl_fotos_informes') . " 
                ON informes.id = tbl_fotos_informes.tbl_id_pae
        GROUP BY informes.id";



        if ($id > 0) {
            $sql .= " WHERE informes.id = :id";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([':id' => $id]);
        } else {
            $stmt = $pdo->prepare($sql);
            $stmt->execute();
        }

        $arr = [];

        while ($valor = $stmt->fetch(PDO::FETCH_ASSOC)) {
            // Traer todas las imágenes del informe
            $stmtFotos = $pdo->prepare("
                SELECT ruta_imagen, fecha_captura
                FROM " . $db->getTable('tbl_fotos_informes') . "
                WHERE tbl_id_pae = :id_informe
                ORDER BY fecha_captura DESC, id DESC
            ");
            $stmtFotos->execute([':id_informe' => $valor['id']]);
            $valor['imagenes'] = $stmtFotos->fetchAll(PDO::FETCH_ASSOC);

            $arr[] = $valor;
        }

        $db->closeConect();

        return count($arr)
            ? ['output' => ['valid' => true, 'response' => $arr]]
            : Util::error_no_result();
    }
}
