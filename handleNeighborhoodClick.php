<?php

// Información de departamentos, municipios y veredas para Santander
$departamentos = [
    'santander' => [
        'municipios' => [
            'Bucaramanga' => ['Vereda 1', 'Vereda 2'],
            'Floridablanca' => ['Vereda 3', 'Vereda 4'],
            'Piedecuesta' => ['Vereda 5', 'Vereda 6'],
        ]
    ]
];

// Valores seleccionados
$selectedDepartamento = $_POST['departamento'] ?? '';
$selectedMunicipio = $_POST['municipio'] ?? '';
$selectedVereda = $_POST['vereda'] ?? '';

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Filtros de Ubicación</title>
</head>
<body>
    <form method="POST">
        <div class="filters md:flex gap-4 mb-5 w-full text-green-dark bg-white">
            <!-- Filtro de Departamento -->
            <div class="selectGroup w-full">
                <div class="title">
                    <h5 class="text-lg font-semibold">Departamento</h5>
                </div>
                <select
                    class="select select-bordered select-sm w-full max-w-md bg-gray-700"
                    name="departamento"
                    disabled
                >
                    <option value="INIT" disabled>Departamento</option>
                    <option value="santander" <?php echo $selectedDepartamento === 'santander' ? 'selected' : ''; ?>>Santander</option>
                </select>
            </div>

            <!-- Filtro de Municipio -->
            <div class="selectGroup w-full text-green-dark">
                <div class="title">
                    <h5 class="text-lg font-semibold">Sub Región</h5>
                </div>
                <select
                    class="select select-bordered select-sm w-full max-w-md"
                    name="municipio"
                    onchange="this.form.submit()"
                >
                    <option value="">Seleccione un municipio</option>
                    <?php
                    if ($selectedDepartamento === 'santander') {
                        foreach ($departamentos['santander']['municipios'] as $municipio => $veredas) {
                            $isSelected = $municipio === $selectedMunicipio ? 'selected' : '';
                            echo "<option value=\"$municipio\" $isSelected>$municipio</option>";
                        }
                    }
                    ?>
                </select>
            </div>

            <!-- Filtro de Vereda -->
            <div class="selectGroup w-full text-green-dark">
                <div class="title">
                    <h5 class="text-lg font-semibold">Vereda</h5>
                </div>
                <select
                    class="select select-bordered select-sm w-full"
                    name="vereda"
                >
                    <option value="">Seleccione una vereda</option>
                    <?php
                    if ($selectedMunicipio && isset($departamentos['santander']['municipios'][$selectedMunicipio])) {
                        foreach ($departamentos['santander']['municipios'][$selectedMunicipio] as $vereda) {
                            $isSelected = $vereda === $selectedVereda ? 'selected' : '';
                            echo "<option value=\"$vereda\" $isSelected>$vereda</option>";
                        }
                    }
                    ?>
                </select>
            </div>
        </div>
    </form>
</body>
</html>
