<?php
include_once './admin/classes/Configuracion_Puntaje.php';

// Obtener datos
$arr = Configuracion_Puntaje::getAll(null);
$isvalid = $arr['output']['valid'];
$data = $arr['output']['response'];

// Ordenar por "Desde"
usort($data, function ($a, $b) {
    return $a['rango_desde'] - $b['rango_desde'];
});
?>

<?php if (isset($puntajes) && count($puntajes) > 0): ?>
    <table class="table-colores-mapa Rango_Colores">
        <thead>
            <tr>
                <th colspan="2" class="titulo-tabla">Rango de Colores</th>
            </tr>
            <tr>
                <th scope="col">Desde</th>
                <th scope="col">Hasta</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($puntajes as $item): ?>
                <?php
                    $color = htmlspecialchars($item['color'], ENT_QUOTES, 'UTF-8');
                    $desde = htmlspecialchars($item['rango_desde'], ENT_QUOTES, 'UTF-8');
                    $hasta = htmlspecialchars($item['rango_hasta'], ENT_QUOTES, 'UTF-8');
                ?>
                <tr>
                    <td style="background-color: <?= $color ?>; color: white;"><?= $desde ?></td>
                    <td style="background-color: <?= $color ?>; color: white;"><?= $hasta ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>

