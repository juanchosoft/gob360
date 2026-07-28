<?php
class Bienes
{
    public function __construct() {}

    public static function getAll($rqst)
    {
        $id = isset($rqst['id']) ? intval($rqst['id']) : 0;
        $municipioId = isset($rqst['municipioId']) ? ($rqst['municipioId']) : 0;

        $db = new DbConection();
        $pdo = $db->openConect();

        // Consulta modificada para relacionar las tablas
        $q = "SELECT 
            b.*, 
            c.municipio AS nombre_municipio,
            s.secretaria AS nombre_secretaria
        FROM " . $db->getTable('tbl_bienes_inmuebles') . " AS b
        LEFT JOIN " . $db->getTable('tbl_ciudades_accion_unificada') . " AS c 
            ON b.tbl_municipio_id = c.codigo_muncipio
        LEFT JOIN " . $db->getTable('tbl_secretarias') . " AS s 
            ON b.tbl_secretaria_id = s.id";

        $params = [];
        if ($id > 0) {
            $q .= " WHERE b.id = :id";
            $params[':id'] = $id;
        }
        if($municipioId > 0){
            $q .= " WHERE b.tbl_municipio_id = :municipioId";
            $params[':municipioId'] = $municipioId;
        }
        $q .= " ORDER BY b.id DESC";

        try {
            $stmt = $pdo->prepare($q);
            $stmt->execute($params);
            $arr = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $arrjson = array('output' => array('valid' => true, 'response' => $arr ? $arr : []));
        } catch (PDOException $e) {
            $arrjson = Util::error_general('Al obtener los datos de Bienes: ' . $e->getMessage());
        } finally {
            $db->closeConect();
        }
        return $arrjson;
    }

    public static function save($rqst)
    {
        $id = isset($rqst['id']) ? intval($rqst['id']) : 0;
        $codigo_control = isset($rqst['codigo_control']) ? trim($rqst['codigo_control']) : '';
        $calcomania = isset($rqst['calcomania']) ? trim($rqst['calcomania']) : '';
        $nombre_articulo = isset($rqst['nombre_articulo']) ? trim($rqst['nombre_articulo']) : '';
        $costo_unitario = isset($rqst['costo_unitario']) ? trim($rqst['costo_unitario']) : '';
        $tbl_departamento_id = isset($rqst['tbl_departamento_id']) ? trim($rqst['tbl_departamento_id']) : '';
        $tbl_municipio_id = isset($rqst['tbl_municipio_id']) ? trim($rqst['tbl_municipio_id']) : '';
        $tbl_secretaria_id = isset($rqst['secretaria']) ? trim($rqst['secretaria']) : '';
        $dependencia = isset($rqst['dependencia']) ? trim($rqst['dependencia']) : '';
        $cedula_o_nit = isset($rqst['cedula_o_nit']) ? trim($rqst['cedula_o_nit']) : '';
        $responsable = isset($rqst['responsable']) ? trim($rqst['responsable']) : '';
        $observacion = isset($rqst['observacion']) ? trim($rqst['observacion']) : '';
        $latitud = isset($rqst['latitud']) ? trim($rqst['latitud']) : '';
        $longitud = isset($rqst['longitud']) ? trim($rqst['longitud']) : '';
        $img1 = $rqst['img1'] ?? null;
        $img2 = $rqst['img2'] ?? null;
        $img3 = $rqst['img3'] ?? null;
        $img4 = $rqst['img4'] ?? null;

        // === Validaciones del servidor ===
        if (empty($codigo_control)) {
            return Util::error_missing_data_description('El campo "Código de Control" es requerido.');
        }
        if (empty($calcomania)) {
            return Util::error_missing_data_description('El campo "Calcomanía" es requerido.');
        }
        if (empty($nombre_articulo)) {
            return Util::error_missing_data_description('El campo "Nombre del Artículo" es requerido.');
        }
        if (empty($costo_unitario)) {
            return Util::error_missing_data_description('El campo "Costo Unitario" es requerido.');
        }
        if (empty($tbl_departamento_id)) {
            return Util::error_missing_data_description('El campo "Departamento" es requerido.');
        }
        if (empty($tbl_municipio_id)) {
            return Util::error_missing_data_description('El campo "Municipio" es requerido.');
        }
        if (empty($tbl_secretaria_id)) {
            return Util::error_missing_data_description('El campo "Secretaría" es requerido.');
        }
        if (empty($dependencia)) {
            return Util::error_missing_data_description('El campo "Dependencia" es requerido.');
        }
        if (empty($cedula_o_nit)) {
            return Util::error_missing_data_description('El campo "Cédula o NIT" es requerido.');
        }
        if (empty($responsable)) {
            return Util::error_missing_data_description('El campo "Responsable" es requerido.');
        }

        $db = new DbConection();
        $pdo = $db->openConect();

        try {
            // -- INICIA TRANSACCIÓN --
            $pdo->beginTransaction();

            if ($id > 0) {
                // --- UPDATE ---
                $table = $db->getTable('tbl_bienes_inmuebles');
                $arrfieldscomma = [
                    'codigo_control' => $codigo_control,
                    'calcomania' => $calcomania,
                    'nombre_articulo' => $nombre_articulo,
                    'costo_unitario' => $costo_unitario,
                    'tbl_departamento_id' => $tbl_departamento_id,
                    'tbl_municipio_id' => $tbl_municipio_id,
                    'tbl_secretaria_id' => $tbl_secretaria_id,
                    'dependencia' => $dependencia,
                    'cedula_o_nit' => $cedula_o_nit,
                    'responsable' => $responsable,
                    'observacion' => $observacion,
                    'latitud' => $latitud,
                    'longitud' => $longitud,
                ];

                // Agregar imágenes si vienen en el request
                if (!empty($img1)) {
                    $arrfieldscomma['img1'] = $img1;
                }
                if (!empty($img2)) {
                    $arrfieldscomma['img2'] = $img2;
                }
                if (!empty($img3)) {
                    $arrfieldscomma['img3'] = $img3;
                }
                if (!empty($img4)) {
                    $arrfieldscomma['img4'] = $img4;
                }

                $arrfieldsnocomma = array('dtcreate' => Util::date_now_server());
                $q_update = Util::make_query_update($table, "id = '$id'", $arrfieldscomma, $arrfieldsnocomma);
                $pdo->query($q_update);
                $arrjson = array('output' => array('valid' => true, 'id' => $id));
            } else {
                // --- INSERT ---
                $image = (isset($_SESSION['file']['nombrearchivo']) && !empty($_SESSION['file']['nombrearchivo'])) ? $_SESSION['file']['nombrearchivo'] : 'default.png';

                // Construir la consulta SQL dinámicamente para incluir campos de imágenes si existen
                $campos = "codigo_control, calcomania, nombre_articulo, costo_unitario, tbl_departamento_id, tbl_municipio_id, tbl_secretaria_id, dependencia, cedula_o_nit, responsable, observacion, latitud, longitud";
                $valores = ":codigo_control, :calcomania, :nombre_articulo, :costo_unitario, :tbl_departamento_id, :tbl_municipio_id, :tbl_secretaria_id, :dependencia, :cedula_o_nit, :responsable, :observacion, :latitud, :longitud";

                $arrparam = [
                    ':codigo_control' => $codigo_control,
                    ':calcomania' => $calcomania,
                    ':nombre_articulo' => $nombre_articulo,
                    ':costo_unitario' => $costo_unitario,
                    ':tbl_departamento_id' => $tbl_departamento_id,
                    ':tbl_municipio_id' => $tbl_municipio_id,
                    ':tbl_secretaria_id' => $tbl_secretaria_id,
                    ':dependencia' => $dependencia,
                    ':cedula_o_nit' => $cedula_o_nit,
                    ':responsable' => $responsable,
                    ':observacion' => $observacion,
                    ':latitud' => $latitud,
                    ':longitud' => $longitud,
                ];

                // Agregar imágenes si vienen en el request
                if (!empty($img1)) {
                    $campos .= ", img1";
                    $valores .= ", :img1";
                    $arrparam[':img1'] = $img1;
                }
                if (!empty($img2)) {
                    $campos .= ", img2";
                    $valores .= ", :img2";
                    $arrparam[':img2'] = $img2;
                }
                if (!empty($img3)) {
                    $campos .= ", img3";
                    $valores .= ", :img3";
                    $arrparam[':img3'] = $img3;
                }
                if (!empty($img4)) {
                    $campos .= ", img4";
                    $valores .= ", :img4";
                    $arrparam[':img4'] = $img4;
                }

                $q = "INSERT INTO " . $db->getTable('tbl_bienes_inmuebles') . " (" . $campos . ") VALUES (" . $valores . ")";
                $stmt = $pdo->prepare($q);

                $stmt->execute($arrparam);
                $arrjson = array('output' => array('valid' => true, 'response' => $pdo->lastInsertId()));
            }

            $pdo->commit();
        } catch (PDOException $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            $arrjson = Util::error_general('Guardando datos en Bienes');
        } finally {
            $db->closeConect();
        }
        return $arrjson;
    }

    public static function delete($rqst)
    {
        $id = isset($rqst['id']) ? intval($rqst['id']) : 0;
        if ($id <= 0) {
            return Util::error_missing_data();
        }

        $db = new DbConection();
        $pdo = $db->openConect();
        try {
            $q = "DELETE FROM " . $db->getTable('tbl_bienes_inmuebles') . " WHERE id = :id";
            $stmt = $pdo->prepare($q);
            if ($stmt->execute([':id' => $id])) {
                $arrjson = array('output' => array('valid' => true));
            } else {
                $arrjson = Util::error_generaldelete();
            }
        } catch (PDOException $e) {
            $arrjson = Util::error_general('Error al eliminar el registro.');
        } finally {
            $db->closeConect();
        }
        return $arrjson;
    }

    public static function getTotalBienes()
    {
        $db = new DbConection();
        $pdo = $db->openConect();

        $q = "SELECT 
                COUNT(id) AS total_bienes, 
                SUM(costo_unitario) AS inversion_total
            FROM " . $db->getTable('tbl_bienes_inmuebles');
        
        // bienes por municipio
        $q_municipios = "SELECT 
                        c.municipio AS nombre_municipio,
                        COUNT(b.id) AS total_bienes,
                        SUM(b.costo_unitario) AS inversion_por_municipio
                        FROM " . $db->getTable('tbl_bienes_inmuebles') . " AS b
                        LEFT JOIN " . $db->getTable('tbl_ciudades_accion_unificada') . " AS c ON b.tbl_municipio_id = c.codigo_muncipio
                        GROUP BY c.municipio";

        try {
            $stmt_total = $pdo->query($q);
            $summary = $stmt_total->fetch(PDO::FETCH_ASSOC);

            $stmt_municipios = $pdo->query($q_municipios);
            $bienes_por_municipio = $stmt_municipios->fetchAll(PDO::FETCH_ASSOC);

            $minMaxCostos = self::getMinMaxCosto();


            $arrjson = array(
                'output' => array(
                    'valid' => true, 
                    'response' => [
                        'summary' => $summary, 
                        'bienes_por_municipio' => $bienes_por_municipio,
                        'min_max_costos' => $minMaxCostos

                    ]
                )
            );
        } catch (PDOException $e) {
            $arrjson = Util::error_general('Al obtener el resumen de bienes: ' . $e->getMessage());
        } finally {
            $db->closeConect();
        }
        return $arrjson;
    }


    public static function getMinMaxCosto() {
        $db = new DbConection();
        $conn = $db->openConect();
    
        // costo unitario MÁXIMO
        $sqlMax = "SELECT b.costo_unitario, m.municipio AS nombre_municipio
                FROM " . $db->getTable('tbl_bienes_inmuebles') . " b
                INNER JOIN " . $db->getTable('tbl_ciudades_accion_unificada') . " m ON b.tbl_municipio_id = m.codigo_muncipio
                ORDER BY b.costo_unitario DESC
                LIMIT 1";
    
        $stmtMax = $conn->prepare($sqlMax);
        $stmtMax->execute();
        $maxCosto = $stmtMax->fetch(PDO::FETCH_ASSOC);
    
        // costo unitario MÍNIMO
        $sqlMin = "SELECT b.costo_unitario, m.municipio AS nombre_municipio
                FROM " . $db->getTable('tbl_bienes_inmuebles') . " b
                INNER JOIN " . $db->getTable('tbl_ciudades_accion_unificada') . " m ON b.tbl_municipio_id = m.codigo_muncipio
                ORDER BY b.costo_unitario ASC
                LIMIT 1";
    
        $stmtMin = $conn->prepare($sqlMin);
        $stmtMin->execute();
        $minCosto = $stmtMin->fetch(PDO::FETCH_ASSOC);
    
        return [
            'max' => [
                'costo_unitario' => $maxCosto['costo_unitario'] ?? '0',
                'municipio' => $maxCosto['nombre_municipio'] ?? 'N/A'
            ],
            'min' => [
                'costo_unitario' => $minCosto['costo_unitario'] ?? '0',
                'municipio' => $minCosto['nombre_municipio'] ?? 'N/A'
            ]
        ];
    }    

    public static function getDistribucionPorProvincia()
    {
        $db = new DbConection();
        $pdo = $db->openConect();

        try {
            // conteo de bienes por municipio
            $q = "SELECT 
                    c.municipio AS nombre_municipio,
                    COUNT(b.id) AS total_bienes
                FROM " . $db->getTable('tbl_bienes_inmuebles') . " AS b
                JOIN " . $db->getTable('tbl_ciudades_accion_unificada') . " AS c ON b.tbl_municipio_id = c.codigo_muncipio
                GROUP BY c.municipio
                ORDER BY total_bienes DESC";

            $stmt = $pdo->query($q);
            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $arrjson = array('output' => array('valid' => true, 'response' => $result));
        } catch (PDOException $e) {
            $arrjson = Util::error_general('Al obtener la distribución por municipios: ' . $e->getMessage());
        } finally {
            $db->closeConect();
        }
        return $arrjson;
    }

}
