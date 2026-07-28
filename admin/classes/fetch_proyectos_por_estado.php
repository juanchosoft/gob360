<?php

include 'DbConection.php'; 
include 'Util.php';
include 'Secretarias.php';
include 'Proyectos.php';

// --- LÓGICA DE PAGINACIÓN ---
$limit = 10;
$page = isset($_POST['page']) ? intval($_POST['page']) : 1; 
$offset = ($page - 1) * $limit; // Calcular el desplazamiento 


$estado = trim($_POST['estado'] ?? null);
$secretariaId = trim($_POST['secretaria'] ?? null); 
$provincia = trim($_POST['provincia'] ?? null); 

$secretariaId = intval($secretariaId);

try {

    $db = new DbConection();
    $pdo = $db->openConect();

    $whereConditions = [];
    $params = [];
    
    $whereConditions[] = "tbl_proyectos.tbl_secretarias_id = :secretariaId";
    $params[':secretariaId'] = $secretariaId;

    if (trim($provincia) !== 'Todos') {
        $whereConditions[] = "LOWER(TRIM(tbl_proyectos.provincia)) = LOWER(:provincia)";
        $params[':provincia'] = $provincia;
    }
    
    $whereConditions[] = "LOWER(TRIM(tbl_proyectos.estado)) = LOWER(:estado)";
    $params[':estado'] = $estado;

    $whereClause = 'WHERE ' . implode(' AND ', $whereConditions);

    // PARA CONTAR EL TOTAL DE PROYECTOS
    $sqlCount = "
        SELECT COUNT(tbl_proyectos.id) 
        FROM " . $db->getTable('tbl_proyectos') . " AS tbl_proyectos
        LEFT JOIN " . $db->getTable('tbl_ciudades_accion_unificada') . " AS tbl_ciudades_accion_unificada
            ON tbl_proyectos.tbl_municipio_id = tbl_ciudades_accion_unificada.id
        {$whereClause}"; 
    
    $stmtCount = $pdo->prepare($sqlCount);

    $stmtCount->execute($params); 
    $totalRows = $stmtCount->fetchColumn(); 
    $totalPages = ceil($totalRows / $limit);


    //PARA OBTENER LOS PROYECTOS DE LA PÁGINA ACTUAL
    $sql = "
        SELECT
            tbl_proyectos.id,
            tbl_proyectos.proyecto AS nombre_proyecto,
            tbl_proyectos.valor_proyecto AS inversion,
            tbl_proyectos.estado,
            tbl_proyectos.provincia,
            tbl_ciudades_accion_unificada.municipio
        FROM
            " . $db->getTable('tbl_proyectos') . " AS tbl_proyectos
        LEFT JOIN
            " . $db->getTable('tbl_ciudades_accion_unificada') . " AS tbl_ciudades_accion_unificada
            ON tbl_proyectos.tbl_municipio_id = tbl_ciudades_accion_unificada.id
        {$whereClause}
        ORDER BY
            tbl_proyectos.provincia, tbl_proyectos.proyecto
        LIMIT :limit OFFSET :offset"; 

    $stmt = $pdo->prepare($sql);
    
    $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
    $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
    

    foreach ($params as $key => &$val) {
        $stmt->bindParam($key, $val);
    }

    $stmt->execute();
    $proyectos = $stmt->fetchAll(PDO::FETCH_ASSOC);

    
    if (empty($proyectos)) {
        echo '<div class="alert alert-info" role="alert">No se encontraron proyectos en estado <strong>"' . htmlspecialchars($estado) . '"</strong> para ' . htmlspecialchars($provincia) . '.</div>';
    } else {
        $totalProyectos = count($proyectos);
        
        echo '<h5 class="mb-3">Listado de Proyectos: <strong>' . htmlspecialchars($estado) . '</strong> (' . htmlspecialchars($provincia) . ')</h5>';

        echo '<p class="text-muted">Mostrando ' . (($offset) + 1) . '-' . (($offset) + $totalProyectos) . ' de ' . $totalRows . ' proyectos.</p>'; 

        echo '<div class="table-responsive">';
        echo '<table class="table table-striped table-hover table-sm">';

        echo '<thead>';
        echo '<tr>';
        echo '<th>Proyecto</th>';
        echo '<th>Municipio</th>';
        echo '<th>Inversión Total</th>';
        echo '<th>Estado</th>';
        echo '</tr>';
        echo '</thead>';
        echo '<tbody>';

        foreach ($proyectos as $proyecto) {
            // Formateo de datos
            $idProyecto = htmlspecialchars($proyecto['id'] ?? '#');
            $nombreProyecto = htmlspecialchars($proyecto['nombre_proyecto'] ?? 'N/A');

            $municipio = htmlspecialchars($proyecto['municipio'] ?? $proyecto['provincia'] ?? 'N/A'); 
            $inversion = '$' . number_format($proyecto['inversion'] ?? 0, 0, ',', '.');
            $estadoActual = htmlspecialchars($proyecto['estado'] ?? 'Desconocido');

            echo '<tr>';
            echo '<td class="fw-bold">' . $nombreProyecto . '</td>';
            echo '<td>' . $municipio . '</td>';
            echo '<td>' . $inversion . '</td>';
            echo '<td><span class="badge badge-primary">' . $estadoActual . '</span></td>'; 
            echo '</tr>';
        }

        echo '</tbody>';
        echo '</table>';
        echo '</div>';
        
        // --- GENERAR BOTONES DE PAGINACIÓN ---
        if ($totalPages > 1) {
            echo '<nav aria-label="Navegación de Proyectos" class="mt-4">';
            echo '<ul class="pagination pagination-sm justify-content-center">';

            // Botón Anterior
            $prevPage = $page - 1;
            $prevDisabled = ($page == 1) ? 'disabled' : '';
            echo '<li class="page-item ' . $prevDisabled . '"><a class="page-link page-link-ajax" href="#" data-page="' . $prevPage . '">Anterior</a></li>';

            // Links de Páginas
            for ($i = 1; $i <= $totalPages; $i++) {
                $active = ($i == $page) ? 'active' : '';
                echo '<li class="page-item ' . $active . '"><a class="page-link page-link-ajax" href="#" data-page="' . $i . '">' . $i . '</a></li>';
            }

            // Botón Siguiente
            $nextPage = $page + 1;
            $nextDisabled = ($page == $totalPages) ? 'disabled' : '';
            echo '<li class="page-item ' . $nextDisabled . '"><a class="page-link page-link-ajax" href="#" data-page="' . $nextPage . '">Siguiente</a></li>';

            echo '</ul></nav>';
        }
    }

} catch (PDOException $e) {
    // Manejo de errores de base de datos
    echo '<div class="alert alert-danger" role="alert">Error al ejecutar la consulta: ' . htmlspecialchars($e->getMessage()) . '</div>';
} finally {
    if (isset($db)) {
        $db->closeConect();
    }
}
?>