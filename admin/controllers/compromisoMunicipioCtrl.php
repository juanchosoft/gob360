<?php
require_once __DIR__ . '/../include/controller_boot.php';
ControllerGate::authorizeScript(__FILE__);

error_reporting(0);
header('Access-Control-Allow-Origin: *');
header("Access-Control-Allow-Credentials: true");
header('Access-Control-Allow-Methods: GET, PUT, POST, DELETE, OPTIONS');
header('Access-Control-Max-Age: 1000');
header('Access-Control-Allow-Headers: Origin, Content-Type, X-Auth-Token , Authorization');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['method']) && $_POST['method'] === 'actualizarCompromiso') {
    require_once '../classes/Visitas.php';
    $user = new Visitas();
    echo json_encode($user->actualizarCompromiso($_POST, $_FILES));
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['method']) && $_POST['method'] === 'guardaVisita') {
    require_once '../classes/Visitas.php';
    $user = new Visitas();
    echo json_encode($user->save($_POST, $_FILES));
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['method']) && $_POST['method'] === 'actualizarVisita') {
    require_once '../classes/Visitas.php';
    $user = new Visitas();
    echo json_encode($user->actualizarVisita($_POST, $_FILES));
    exit;
}


// Exportar compromisos a Excel (CSV) - se maneja antes del JSON decode general
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['method']) && $_POST['method'] === 'exportarCompromisosExcel') {
    require_once '../classes/Visitas.php';
    $user = new Visitas();
    $user->exportarCompromisosExcel($_POST);
    exit;
}

$data = ControllerGate::jsonBody();
if (isset($data['method'])) {
    switch ($data['method']) {
        case 'guardarObservacion':
            require_once '../classes/Visitas.php';
            $user = new Visitas();
            echo json_encode($user->guardarObservacion($data['data']));
            break;

        case 'ejecutarTrasladoPorCompetencia':
            require_once '../classes/Visitas.php';
            $user = new Visitas();
            echo json_encode($user->ejecutarTrasladoPorCompetencia($data['data']));
            break;

        case 'getAllCompromiseFiltrosSelectEnEstadoEspera':
            require_once '../classes/Visitas.php';
            $user = new Visitas();
            echo json_encode($user->getAllCompromiseFiltrosSelectEnEstadoEspera($data['data']));
            break;
        case 'getAllCompromiseFiltrosSelect':
            require_once '../classes/Visitas.php';
            $user = new Visitas();
            // echo json_encode($user->getAllCompromiseFiltrosSelect($data['data']));
            echo json_encode($user->getAllCompromiseFiltrosSelectSecretariaSessionUsuario($data['data']));
            break;
        case 'getAllCompromise':
            require_once '../classes/Visitas.php';
            $user = new Visitas();
            // echo json_encode($user->getAllCompromise($data['data']));
            echo json_encode($user->getAllCompromiseBySecretariaSessionUsuario($data['data']));
            break;

case 'getAllCompromisecumplidos':
    require_once '../classes/Visitas.php';
    $user = new Visitas();
    echo json_encode($user->getAllCompromisecumplidos($data['data']));
    break;

case 'getAllCompromisecumplidosFiltrosSelect':
    require_once '../classes/Visitas.php';
    $user = new Visitas();
    echo json_encode($user->getAllCompromisecumplidosFiltrosSelect($data['data']));
    break;




        case 'getAllCompromiseEnEspera':
            require_once '../classes/Visitas.php';
            $user = new Visitas();
            echo json_encode($user->getAllCompromisosEnEstadoEspera($data['data']));
            break;
        case 'getAllVisitas':
            require_once '../classes/Visitas.php';
            $user = new Visitas();
            echo json_encode($user->getAllVisitas($data['data']));
            break;
        case 'indicadores':
            require_once '../classes/Visitas.php';
            $user = new Visitas();
            echo json_encode($user->indicadores());
            break;
        case 'indicadoresTipoVisita':
            require_once '../classes/Visitas.php';
            $user = new Visitas();
            echo json_encode($user->indicadoresTipoVisita());
            break;
        case 'getVisitaId':
            require_once '../classes/Visitas.php';
            $user = new Visitas();
            echo json_encode($user->getVisitaId($data['data']));
            break;
        case 'getCompromisoId':
            require_once '../classes/Visitas.php';
            $user = new Visitas();
            echo json_encode($user->getCompromisoId($data['data']));
            break;

        case 'getCompromisoHistorial':
            require_once '../classes/Visitas.php';
            echo json_encode(Visitas::getCompromisoHistorial((int)($data['data']['id'] ?? 0)));
            break;

        case 'deleteCompromiso':
            require_once '../classes/Visitas.php';
            echo json_encode(Visitas::deleteCompromiso($data['data']));
            break;
            
        case 'mapa':
            require_once '../classes/Colombia.php';
            $user = new Colombia();
            echo json_encode($user->getInformacionParaMapaGestoraSocial($data['data']));
            break;
        case 'graficoSeguimiento':
            require_once '../classes/Visitas.php';
            $user = new Visitas();
            echo json_encode($user->graficoSeguimiento(
                $data['idSecretaria'] ?? null,
                $data['componente'] ?? null,
                $data['tipo_ejecucion'] ?? null
            ));
            break;
        case 'porcentaje':
            require_once '../classes/Visitas.php';
            $user = new Visitas();
            echo json_encode($user->porcentaje(
                $data['idSecretaria'] ?? null,
                $data['componente'] ?? null,
                $data['tipo_ejecucion'] ?? null
            ));
            break;
        case 'dataMapa':
            require_once '../classes/Visitas.php';
            $user = new Visitas();
            echo json_encode($user->dataMapa(
                $data['idSecretaria'] ?? null,
                $data['componente'] ?? null,
                $data['tipo_ejecucion'] ?? null
            ));
            break;
        case 'dataPorMunicipioSecretaria':
            require_once '../classes/Visitas.php';
            $user = new Visitas();
            echo json_encode($user->dataPorMunicipioSecretaria($data));
            break;
        case 'dataPorMunicipioSecretariaTodosLosEstados':
            require_once '../classes/Visitas.php';
            $user = new Visitas();
            echo json_encode($user->dataPorMunicipioSecretariaTodosLosEstados($data));
            break;

        default:
            echo 'ninguna opción valida.' . $data['method'];
            break;
    }
} else {
    echo 'ninguna opción valida.' . $data['method'];
}
