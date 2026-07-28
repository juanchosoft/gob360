<?php
include_once './admin/classes/Configuracion_Puntaje.php';


// Obtener datos de Configuracion_Puntaje
$arr = Configuracion_Puntaje::getAll(null);
$isvalid = $arr['output']['valid'];
$data = $arr['output']['response'];

// Ordenar los datos por "Desde" (rango_desde) de menor a mayor
usort($data, function ($a, $b) {
    return $a['rango_desde'] - $b['rango_desde'];
});
?>

<table class="table-colores-mapa">
    <thead>
        <tr>
            <th colspan="3" class="titulo-tabla">Rango de Colores</th>
        </tr>
        <tr>
            <th scope="col">Desde</th>
            <th scope="col">Hasta</th>
            <!-- <th scope="col">Color</th> -->
        </tr>
    </thead>
    <tbody>
        <?php if ($isvalid && count($data) > 0): ?>
            <?php foreach ($data as $index => $item): ?>
                <tr style="background: linear-gradient(to right, <?php echo htmlspecialchars($item['color'], ENT_QUOTES, 'UTF-8'); ?>, <?php echo htmlspecialchars($item['color'], ENT_QUOTES, 'UTF-8'); ?>); color: <?php echo ($index == 2) ? 'black' : 'white'; ?>">
                    <td><?php echo htmlspecialchars($item['rango_desde'], ENT_QUOTES, 'UTF-8'); ?></td>
                    <td><?php echo htmlspecialchars($item['rango_hasta'], ENT_QUOTES, 'UTF-8'); ?></td>
                    <!-- <td>
                        <span class="color-circle" style="background-color: <?php echo htmlspecialchars($item['color'], ENT_QUOTES, 'UTF-8'); ?>;"></span>
                        <?php echo htmlspecialchars($item['color'], ENT_QUOTES, 'UTF-8'); ?>
                    </td> -->
                </tr>
            <?php endforeach; ?>
        <?php else: ?>
            <tr>
                <td colspan="3">No hay datos disponibles</td>
            </tr>
        <?php endif; ?>
    </tbody>
</table>