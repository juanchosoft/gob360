<?php
define("DS",DIRECTORY_SEPARATOR);

    
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <title>Mapa</title>
    <meta charset="UTF-8">
    <meta name="title" content="">
    <meta name="description" content="">
    <!-- <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0-beta2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-BmbxuPwQa2lc/FVzBcNJ7UAyJxM6wuqIj61tLrc4wSX0szH/Ev+nYRRuWlolflfl" crossorigin="anonymous"> -->
</head>

<body>
    <div id="mapa-division">
        <?php foreach ($municipios as $key => $value): ?>
        <?php if (!empty($value["carpeta_svg"])): ?>
        <img src="admin/<?php echo $value["carpeta_svg"] ?>/<?php echo $value["nombre_mapa"] ?>.svg"
            class="<?php echo $value["nombre_mapa"] ?> <?php echo getClase($finalMunicipios[$value["id"]]["puntaje"]); ?> <?php echo $finalMunicipios[$value["id"]]["puntaje"] ?> <?php echo $value["subregion"] ?> <?php echo $value["aap"] == "si" ? "AAP" : "" ?> <?php echo $value["pdet"] == "si" ? "PDET" : "" ?> <?php echo $value["zf"] == "si" ? "ZF" : "" ?> municipios mapaClick"
            alt="<?php echo str_replace("-", " ", $value["municipio"]) ?>"
            title="<?php echo str_replace("-", " ", $value["municipio"]) ?>"
            data-sub="<?php echo getClase($finalMunicipios[$value["id"]]["puntaje"]); ?>"
            data-url="<?php echo getUrl() ?>estado_municipios.php?mun=<?php echo $value["codigo_muncipio"] ?>"
            data-name="<?php echo strtolower($value["municipio"]) ?>">
        <?php else: ?>
        <img src="admin/mapa/<?php echo strtolower($value["municipio"]) ?>.svg"
            class="<?php echo strtolower($value["municipio"]) ?> <?php echo getClase($finalMunicipios[$value["id"]]["puntaje"]); ?> <?php echo $finalMunicipios[$value["id"]]["puntaje"] ?> <?php echo $value["subregion"] ?> <?php echo $value["aap"] == "si" ? "AAP" : "" ?> <?php echo $value["pdet"] == "si" ? "PDET" : "" ?> <?php echo $value["zf"] == "si" ? "ZF" : "" ?> municipios"
            alt="<?php echo str_replace("-", " ", $value["municipio"]) ?>"
            title="<?php echo str_replace("-", " ", $value["municipio"]) ?>"
            data-sub="<?php echo getClase($finalMunicipios[$value["id"]]["puntaje"]); ?>"
            data-url="<?php echo getUrl() ?>estado_municipios.php?mun=<?php echo $value["codigo_muncipio"] ?>"
            data-name="<?php echo strtolower($value["municipio"]) ?>">
        <?php endif ?>
        <?php endforeach ?>
    </div>
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"
        integrity="sha256-9/aliU8dGd2tb6OSsuzixeV4y/faTqgFtohetphbbj0=" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0-beta2/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-b5kHyXgcpbZJO/tY9Ul7kGkf1S0CWuKcCD38l8YkeH8z8QjE0GmW1gYU5S9FOnJ0" crossorigin="anonymous">
    </script>

    <script>
    $("img").each(function(index, el) {
        if ($(this).data("url")) {
            $(this).attr("data-bs-toggle", "tooltip");
            $(this).attr("data-bs-placement", "left");
            tooltip = new bootstrap.Tooltip($(this)[0], {})
        }
    });
    </script>
</body>

</html>

<style>
.content-map {
    background-color: #efefef;
    padding: 20px 0;
}

#mapa {
    position: relative;
    width: 900px;
    height: 900px;
    margin: 0 auto;
}


.BR04 {
    top: 303px;
    left: 290px;
    width: 345px;
    filter: invert(78%) sepia(99%) saturate(996%) hue-rotate(80deg) brightness(88%) contrast(119%) !important;
}

.BR11 {
    top: 0px;
    left: 283px;
    width: 387px;
    filter: invert(150%) sepia(109%) saturate(511%) hue-rotate(18046deg) brightness(118%) contrast(19%) !important;
}

.BR14 {
    top: 326px;
    left: 513px;
    width: 306px;
    filter: invert(70%) sepia(100%) saturate(701%) hue-rotate(129deg) brightness(88%) contrast(50%) !important;
}

.BR17 {
    top: 121px;
    left: 137px;
    width: 304px;
    filter: invert(70%) sepia(100%) saturate(701%) hue-rotate(195deg) brightness(88%) contrast(50%) !important;

}

.FTCAQ {
    top: 162px;
    left: 384px;
    width: 284px;
    filter: invert(70%) sepia(100%) saturate(701%) hue-rotate(250deg) brightness(88%) contrast(50%) !important;

}

.FTCTI {
    top: 249px;
    left: 61px;
    width: 334px;
    filter: invert(78%) sepia(109%) saturate(96%) hue-rotate(810deg) brightness(88%) contrast(79%) !important;
}

.MAR {
    top: 366px;
    left: 50px;
    width: 125px;
    filter: invert(150%) sepia(109%) saturate(511%) hue-rotate(18046deg) brightness(118%) contrast(19%) !important;
}
</style>