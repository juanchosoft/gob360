<?php
include './admin/include/head.php';
function getUrl()
{
    $port = $_SERVER["SERVER_PORT"];
    $nameServer = $port != "80" ? $_SERVER['SERVER_NAME'] . ":" . $port : $_SERVER['SERVER_NAME'];
    $url = sprintf(
        "%s://%s%s",
        isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off' ? 'https' : 'http',
        $nameServer,
        $_SERVER['REQUEST_URI']
    );
    $final =  str_replace(basename($_SERVER["SCRIPT_FILENAME"], '.php') . ".php", "", $url);
    $exists = strpos($final, "?");
    if ($exists == !false) {
        $final =  substr($final, 0, $exists);
        return $final;
    } else {
        return $final;
    }
}

require_once './admin/include/generic_classes.php';
include './admin/classes/Ciudad.php';
include './admin/classes/Estado.php';
require './admin/classes/Departamento.php';
include './admin/db/colores.php';
include './admin/classes/Main.php';
include './admin/classes/Detalle.php';
include './admin/classes/Cuenta.php';
include './admin/classes/Cuentapro.php';
include './admin/classes/Secreinversion.php';
include './admin/classes/Munnovisitados.php';




// Obtener permisos
$permissions = PagePermissions::crudForCurrentPage();

// Validación de permiso de visualización
/* if (!$permissions['view']) {
    require_once 'permiso_denegado.php';
    exit;
} */

//Obtener solo VISITAS 
$arrVisitas = Main::getSoloVisitas(null);
$visitas = $arrVisitas['output']['total_visitas'] ?? 0;

//Obtener solo municipios visitados
$arrMunicipios = Main::getSoloMunicipiosVisitados(null);
$municipios = $arrMunicipios['output']['municipios_visitados'] ?? 0;

//Calcular los restantes
$visitarpendiente = 87 - $municipios;


$departamento = new Departamento();
// $santander = $departamento->getAll(["id" => 21]);
// $santander = $santander["output"]["response"]["0"];
$code = null;
$mapa = null;

if (isset($_GET['depto_id']) && in_array($_GET['depto_id'], [1, 12, 21])) {
    switch ($_GET['depto_id']) {

        case '21':
            $code = $santander["codigo_departamento"];
            $mapa = "admin/mapa-santander/mapa.php";
            break;
    }
}
if (!is_null($code)) {
    $arr = Ciudad::getAll(array('codigo_departamento' => $code));
    $finalMunicipios = $arr['output']['response'];
    $arrApoyoDep = Ciudad::getApoyoByCodigoDepartamento(array('codigo_departamento' => $code));
}
?>

<body class="dashboard-body">

<div class="card mt-3 mb-3 p-4">
    <h3 class="text-center mb-3">TESTEO DE SVG — BUCARAMANGA</h3>

    <?php

    $codigoMunicipio = 68001;

    $db = new DbConection();
    $pdo = $db->openConect();

    if (!$pdo) {
        echo "<p class='text-danger'> Error: No se pudo abrir la conexión PDO.</p>";
    } else {
$q = "SELECT * FROM " . $db->getTable('tbl_ciudades_accion_unificada') . 
     " WHERE codigo_muncipio = $codigoMunicipio";
        $stm = $pdo->prepare($q);

        try {
            $stm->execute();
            $municipio = $stm->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            echo "<p class='text-danger'> Error SQL: " . $e->getMessage() . "</p>";
            $municipio = null;
        }
    }
    ?>

    <?php if (!$municipio): ?>
        <p class="text-danger fw-bold text-center">
             No se encontró Bucaramanga 
        </p>

    <?php else: ?>

         <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1080 1251.4"
             style="width:100%;max-width:900px;border:1px solid #ccc;display:block;margin:auto;">

            <g id="bucaramanga">
                <path d="<?= htmlspecialchars($municipio['d']); ?>"
                      fill="#c7c7c7"
                      stroke="#000"
                      stroke-width="0.5"></path>

                <text x="690" y="520" font-size="16" fill="#000">
                    <?= strtoupper($municipio['municipio']); ?>
                </text>
            </g>

        </svg>
        <!-- <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1080 1251.4"
             style="width:100%;max-width:900px;border:1px solid #ccc;display:block;margin:auto;">
	
				<g id="bucaramanga">
					<path id="<?php echo strtoupper($value['path']); ?>" 
						d="<?php echo $value['d']; ?>"
						fill="<?php echo getColorByNum($value['num_val']); ?>"
						class="municipios mapaClick <?php echo getClasePorcentaje($value['porcentaje_participacion']); ?>"
						data-url="<?php echo getUrl() . 'estado_municipios.php?mun=' . $value['codigo_muncipio'] . '&dep=' . $value['codigo_departamento']; ?>"
						data-name="<?php echo strtolower($value['municipio']); ?>"
						title="<?php echo strtoupper(str_replace("-", " ", $value['nombre_mapa'])); ?>" stroke="#000000"
						stroke-miterlimit="10"
						stroke-width="0.3px"
						></path><text transform="translate(264.48 382.8)"
						font-family="IBM Plex Sans" font-size="10" font-weight="500">
					</text>
				</g>
	

				
		</svg> -->

    <?php endif; ?>
</div>
<!-- === FIN TESTEO SVG === -->

    <?php include 'admin/include/gerenic_script.php'; ?>
    <script src="assets/js/vendor-all.min.js"></script>
    <script src="assets/js/plugins/bootstrap.min.js"></script>
    <script src="assets/js/pcoded.min.js"></script>

    <!-- prism Js -->
    <script src="assets/js/plugins/prism.js"></script>
    <script src="assets/js/plugins/apexcharts.min.js"></script>

    <script src="admin/js/estado_general.js"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css">

    </script>

</body>

</html>