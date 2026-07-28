<?php
require_once __DIR__ . '/../include/controller_boot.php';
ControllerGate::authorizeScript(__FILE__);

error_reporting(0);
header('Access-Control-Allow-Origin: *');
header("Access-Control-Allow-Credentials: true");
header('Access-Control-Allow-Methods: GET, PUT, POST, DELETE, OPTIONS');
header('Access-Control-Max-Age: 1000');
header('Access-Control-Allow-Headers: Origin, Content-Type, X-Auth-Token , Authorization');

/**
 * Controlador para gestionar visitas y compromisos del Alcalde
 * Utiliza la clase VisitasAlcalde que trabaja con tbl_visitas_alcalde
 */

// Métodos POST con archivos
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['method']) && $_POST['method'] === 'actualizarCompromiso') {
    require_once '../classes/DbConection.php';
    require_once '../classes/Util.php';
    require_once '../classes/CompromisoMunicipioAlcalde.php';
    echo json_encode(CompromisoMunicipioAlcalde::actualizarCompromiso($_POST, $_FILES));
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['method']) && $_POST['method'] === 'guardaVisita') {
    require_once '../classes/VisitasAlcalde.php';
    echo json_encode(VisitasAlcalde::save($_POST, $_FILES));
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['method']) && $_POST['method'] === 'actualizarVisita') {
    require_once '../classes/VisitasAlcalde.php';
    echo json_encode(VisitasAlcalde::actualizarVisita($_POST, $_FILES));
    exit;
}

// Métodos JSON
$data = ControllerGate::jsonBody();

if (isset($data['method'])) {
    switch ($data['method']) {
        case 'getVisitaId':
            require_once '../classes/VisitasAlcalde.php';
            echo json_encode(VisitasAlcalde::getVisitaId($data['data']));
            break;

        case 'getAllVisitas':
            require_once '../classes/VisitasAlcalde.php';
            echo json_encode(VisitasAlcalde::getAll($data['data']));
            break;

        case 'indicadoresTipoVisita':
            require_once '../classes/VisitasAlcalde.php';
            $municipioId = isset($data['data']['municipio_id']) ? $data['data']['municipio_id'] : '';
            echo json_encode(VisitasAlcalde::indicadoresTipoVisita($municipioId));
            break;

        // Métodos para compromisos de alcalde
        case 'getAllCompromisos':
            require_once '../classes/DbConection.php';
            require_once '../classes/Util.php';
            require_once '../classes/CompromisoMunicipioAlcalde.php';
            echo json_encode(CompromisoMunicipioAlcalde::getAll($data['data'] ?? []));
            break;

        case 'getAllCompromisosEnTramite':
            require_once '../classes/CompromisoMunicipioAlcalde.php';
            echo json_encode(CompromisoMunicipioAlcalde::getAllEnTramite($data['data'] ?? []));
            break;

        case 'getAllCompromisosSinCumplir':
            require_once '../classes/CompromisoMunicipioAlcalde.php';
            echo json_encode(CompromisoMunicipioAlcalde::getAllSinCumplir($data['data'] ?? []));
            break;

        case 'getAllCompromisosCumplidos':
            require_once '../classes/CompromisoMunicipioAlcalde.php';
            echo json_encode(CompromisoMunicipioAlcalde::getAllCumplidos($data['data'] ?? []));
            break;

        case 'saveCompromiso':
            require_once '../classes/CompromisoMunicipioAlcalde.php';
            echo json_encode(CompromisoMunicipioAlcalde::save($data['data'] ?? []));
            break;

        case 'deleteCompromiso':
            require_once '../classes/CompromisoMunicipioAlcalde.php';
            echo json_encode(CompromisoMunicipioAlcalde::delete($data['data'] ?? []));
            break;

        case 'getIndicadoresCompromisosSecretaria':
            require_once '../classes/CompromisoMunicipioAlcalde.php';
            echo json_encode(CompromisoMunicipioAlcalde::getIndicadoresPorSecretaria($data['data'] ?? []));
            break;

        case 'getAllCompromiseEnEspera':
            require_once '../classes/CompromisoMunicipioAlcalde.php';
            echo json_encode(CompromisoMunicipioAlcalde::getAllCompromiseEnEspera($data['data'] ?? []));
            break;

        case 'getAllCompromiseFiltrosSelectEnEstadoEspera':
            require_once '../classes/CompromisoMunicipioAlcalde.php';
            echo json_encode(CompromisoMunicipioAlcalde::getAllCompromiseFiltrosSelectEnEstadoEspera($data['data'] ?? []));
            break;

        case 'getCompromisoId':
            require_once '../classes/CompromisoMunicipioAlcalde.php';
            echo json_encode(CompromisoMunicipioAlcalde::getCompromisoId($data['data'] ?? []));
            break;

        case 'aprobarCompromiso':
            require_once '../classes/CompromisoMunicipioAlcalde.php';
            echo json_encode(CompromisoMunicipioAlcalde::aprobarCompromiso($data['data'] ?? []));
            break;

        case 'ejecutarTrasladoPorCompetencia':
            require_once '../classes/CompromisoMunicipioAlcalde.php';
            echo json_encode(CompromisoMunicipioAlcalde::ejecutarTrasladoPorCompetencia($data['data'] ?? []));
            break;

        case 'graficoSeguimiento':
            require_once '../classes/CompromisoMunicipioAlcalde.php';
            echo json_encode(CompromisoMunicipioAlcalde::graficoSeguimiento(
                $data['idSecretaria'] ?? null,
                $data['componente'] ?? null,
                $data['tipo_ejecucion'] ?? null,
                $data['codigo_municipio'] ?? null
            ));
            break;

        case 'porcentaje':
            require_once '../classes/CompromisoMunicipioAlcalde.php';
            echo json_encode(CompromisoMunicipioAlcalde::porcentaje(
                $data['idSecretaria'] ?? null,
                $data['componente'] ?? null,
                $data['tipo_ejecucion'] ?? null,
                $data['codigo_municipio'] ?? null
            ));
            break;

        case 'dataMapa':
            require_once '../classes/CompromisoMunicipioAlcalde.php';
            echo json_encode(CompromisoMunicipioAlcalde::dataMapa(
                $data['idSecretaria'] ?? null,
                $data['componente'] ?? null,
                $data['tipo_ejecucion'] ?? null,
                $data['codigo_municipio'] ?? null
            ));
            break;

        case 'dataPorMunicipioSecretaria':
            require_once '../classes/CompromisoMunicipioAlcalde.php';
            echo json_encode(CompromisoMunicipioAlcalde::dataPorMunicipioSecretaria($data));
            break;

        case 'dataPorMunicipioSecretariaTodosLosEstados':
            require_once '../classes/CompromisoMunicipioAlcalde.php';
            echo json_encode(CompromisoMunicipioAlcalde::dataPorMunicipioSecretariaTodosLosEstados($data));
            break;

        default:
            echo json_encode(['error' => 'Ninguna opción válida.', 'method' => $data['method']]);
            break;
    }
} else {
    echo json_encode(['error' => 'Método no especificado']);
}
