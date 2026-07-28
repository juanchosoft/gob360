class IngresoInformacion {
    public static function getImagesBefore($filtros) {
        $dbConnection = new DbConection();
        $db = $dbConnection->openConect();
        
        $query = "SELECT * FROM tbl_ingreso_informacion WHERE 1=1";
        $params = [];

        if (!empty($filtros['municipio_id'])) {
            $query .= " AND codigo_municipio = ?";
            $params[] = $filtros['municipio_id'];
        }
        if (!empty($filtros['fecha_inicial']) && !empty($filtros['fecha_final'])) {
            $query .= " AND dtcreate BETWEEN ? AND ?";
            $params[] = $filtros['fecha_inicial'];
            $params[] = $filtros['fecha_final'];
        }

        $stmt = $db->prepare($query);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
