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


    <table class="table tabla-estilizada">
        <thead>
            <tr>
                <th scope="col">Desde</th>
                <th scope="col">Hasta</th>
                <th scope="col">Color</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>0</td>
                <td>0</td>
                <td><div class="color-circle" style="background-color: white;"></div></td>
            </tr>
            <tr>
                <td>1</td>
                <td>50</td>
                <td><div class="color-circle" style="background-color: #f7c5ae;"></div></td>
            </tr>
            <tr>
                <td>51</td>
                <td>100</td>
                <td><div class="color-circle" style="background-color: #ffa5ae;"></div></td>
            </tr>
            <tr>
                <td>100</td>
                <td>----</td>
                <td><div class="color-circle" style="background-color: #ea9abd;"></div></td>
            </tr>
        </tbody>
    </table>

