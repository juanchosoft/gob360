<?php
define("DS",DIRECTORY_SEPARATOR);
    $arr = Brigada::getAll(null);
    $brigadas =  $arr['output']['response'];

    $arr2 = Brigada::getNumeroDeColoresPorDivision(null);
    $cantidadesPorDivision =  $arr2['output']['response'];
    $cantidadesPorDivision2022 =  $arr2['output']['response_2022'];
?>

<!DOCTYPE html>
<html lang="es">

<head>
  <title>Mapa</title>
  <meta charset="UTF-8">
  <meta name="title" content="">
  <meta name="description" content="">
  <link rel="stylesheet" href="./dist/css/estilomapa.css">
  <link rel="stylesheet" href="./dist/css/textos.css">

</head>

<body>

  <div class="content-map">
    <div id="mapa">
      <?php foreach ($brigadas as $key => $value): ?>
      <?php if (!empty($value["carpeta_svg"])): ?>
      <div class="content_<?php echo strtolower($value["sigla"]) ?>">
        <img src="admin/division/img/<?php echo $value["nombre_mapa"] ?>.svg"
          class="<?php echo $value["nombre_mapa"] ?> <?php echo getClaseColorBrigada($value["sigla"]); ?> <?php echo $value["puntaje"] ?> <?php echo str_replace("-", " ", $value["sigla"]) ?> mapaClick "
          title="<?php echo str_replace("-", " ", $value["nombre"]) ?>"
          data-url="<?php echo getUrl() ?>estado_brigadas.php?id=<?php echo $value["id"] ?>"
          data-sub="<?php echo getClase($value["puntaje"]); ?>" data-url="<?php echo getUrl() ?>"
          data-name="<?php echo strtolower($value["sigla"]) ?>">
      </div>
      <?php endif ?>
      <?php endforeach ?>
    </div>
  </div>

  <div class="row">

    <div class="col-sm-4 pt-5 pb-5">
      <div class="card-body p-0">
        <!-- Municipios por Brigada -->
        <table class="table table-striped table-sm">
          <center>
            <h5 class="tittle">MUNICIPIOS POR BRIGADA</h5>
          </center>
          <tr>
            <th scope="col">Nombre</th>
            <th scope="col">Cantidad</th>
          </tr>
          <tbody>
            <tr>
              <td>BRIGADA 04</td>
              <th scope="row">91</th>
            </tr>
            <tr>
              <td>BRIGADA 11</td>
              <th scope="row">33</th>
            </tr>
            <tr>
              <td>BRIGADA 14</td>
              <th scope="row">23</th>
            </tr>
            <tr>
              <td>BRIGADA 17</td>
              <th scope="row">12</th>
            </tr>
            <tr>
              <td>FUERZA DE TAREA CONJUNTA TITAN</td>
              <th scope="row">24</th>
            </tr>
            <tr>
              <td>FUERZA DE TAREA CONJUNTA AQUILES</td>
              <th scope="row">14</th>
            </tr>
            <tr>
              <td>TOTAL</td>
              <th scope="row">197</th>
            </tr>

          </tbody>
        </table>
        <!-- fin Municipios por Brigada -->
      </div>
    </div>

    <div class="col-sm-4 pt-5 pb-5">
      <div class="card-body p-0">

        <!-- Municipios por Departamento -->
        <table class="table table-striped table-sm">

          <center>
            <h5 class="tittle">MUNICIPIOS POR DEPARTAMENTO</h5>
          </center>

          <tr>
            <th scope="col">Nombre</th>
            <th scope="col">Cantidad</th>
          </tr>

          <tbody>
            <tr>
              <td>ANTIOQUIA</td>
              <th scope="row">124</th>
            </tr>
            <tr>
              <td>CHOCÓ</td>
              <th scope="row">31</th>
            </tr>
            <tr>
              <td>CÓRDOBA</td>
              <th scope="row">30</th>
            </tr>
            <tr>
              <td>SUCRE</td>
              <th scope="row">25</th>
            </tr>
            <tr>
              <td>BOLÍVAR</td>
              <th scope="row">2</th>
            </tr>
             <tr>
              <td>BOYACÁ</td>
              <th scope="row">1</th>
            </tr>
            <tr>
              <td>TOTAL</td>
              <th scope="row">213</th>
            </tr>
          </tbody>
        </table>

      </div>
    </div>
    <!-- fin Municipios por Departamento -->
    <div class="col-sm-4 pt-5 pb-5">
      <div class="card-body p-0">

        <!-- Municipios por Veredas -->
        <table class="table table-striped table-sm">

          <center>
            <h5 class="tittle">VEREDAS POR DEPARTAMENTO</h5>
          </center>

          <tr>
            <th scope="col">Nombre</th>
            <th scope="col">Cantidad</th>
          </tr>

          <tbody>
            <tr>
              <td>ANTIOQUIA</td>
              <th scope="row">4.448</th>
            </tr>
            <tr>
              <td>CHOCÓ</td>
              <th scope="row">895</th>
            </tr>
            <tr>
              <td>CÓRDOBA</td>
              <th scope="row">907</th>
            </tr>
            <tr>
              <td>SUCRE</td>
              <th scope="row">374</th>
            </tr>
            <tr>
              <td>BOLÍVAR</td>
              <th scope="row">168</th>
            </tr>
            <tr>
              <td>BOYACÁ</td>
              <th scope="row">72</th>
            </tr>
            <tr>
              <td>TOTAL</td>
              <th scope="row">6.864</th>
            </tr>
          </tbody>
        </table>
        <!-- fin Municipios por Veredas -->
      </div>
    </div>

    <div class="col-sm-6">
      <div class="">
        <!-- /.card-header -->

        <div class="card-body p-0">

          <table class="table table-sm" style="display: none">
            <thead>
              <center>
                <tr>
                  <th>Cantidad Veredas - Séptima División</th>
                </tr>

              </center>
            </thead>
            <tbody>

              <?php
						$c1 = count($cantidadesPorDivision);
						for ($i = 0; $i < $c1; $i++) {
							if($cantidadesPorDivision[$i]['color'] !="" && $cantidadesPorDivision[$i]['cuenta'] > 0){ ?>
              <tr>
                <th bgcolor="<?php echo $cantidadesPorDivision[$i]['color']; ?>">
                  <center>Veredas <?php echo intval( $cantidadesPorDivision[$i]['cuenta'] ); ?>
                </th>

                <th> <a href="#"
                    onclick="ESTADO_DIVISION.showData('<?php echo $cantidadesPorDivision[$i]['color']; ?>')"
                    role="button" data-target="#dato_veredas" class="btn btn-xs  btn-primary btn-w-100p btn-mw-300"
                    data-toggle="modal">Ver</a></th>
                </center>
              </tr>
              <?php
							}
						}
                	?>
            </tbody>
          </table>
        </div>

        <!-- /.card-body -->

      </div>
    </div>
  </div>
  <!-- Modal / veredas a mostrar -->
  <div id="dato_veredas" class="modal fade">
    <form id="formarmada" autocomplete="off">
      <div class="modal-dialog modal-lg">
        <div class="modal-content">
          <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>

          </div>
          <div class="modal-body">
            <div class="table-wrapper-scroll-y my-custom-scrollbar">
              <table class="table">
                <thead class="thead-green">
                  <th class="header" scope="col">Brigada</th>
                  <th class="header" scope="col">Batallon</th>
                  <th class="header" scope="col">Departamento</th>
                  <th class="header" scope="col">Municipio</th>
                  <th class="header" scope="col">Vereda</th>
                </thead>
                <tbody id="tablaVeredasColores">
                </tbody>
              </table>
            </div>
          </div>

          <div class="modal-footer">
            <button type="button" class="btn btn-danger" data-dismiss="modal">Cerrar</button>
          </div>
        </div>
      </div>
    </form>
  </div>

  <script>
    $("img").each(function(index, el) {
      if ($(this).data("url")) {
        $(this).attr("data-bs-toggle", "tooltip");
        $(this).attr("data-bs-placement", "left");
        tooltip = new bootstrap.Tooltip($(this)[0], {})
      }
    });
    $(".mapaClick").click(function(event) {
      location.href = $(this).data("url");
    });
  </script>

</body>

</html>

<style>
  .content_br04,
  .content_br17,
  .content_br11,
  .content_br14,
  .content_br15,
  .content_ftcaq,
  .content_ftcti {
    position: relative;
  }

  .content_br04:after,
  .content_br17:after,
  .content_br11:after,
  .content_br14:after,
  .content_br15:after,
  .content_ftcaq:after,
  .content_ftcti:after {
    background: #ffeb3b;
    padding: 0px 8px;
    border-radius: 4px;
    border: 1px solid #000;
    position: absolute;
    z-index: 0;
    box-shadow: 0px 0px 5px 4px #0000004a;
    font-weight: 700;
  }

  .content_br04:before,
  .content_br17:before,
  .content_br11:before,
  .content_br14:before,
  .content_br15:before,
  .content_ftcaq:before,
  .content_ftcti:before {
    width: 50px;
    background-size: cover;
    height: 50px;
    content: "";
    position: absolute;
    z-index: 1;
  }

  .content_br04:before {
    background-image: url(assets/img/br04.png);
    top: 440px;
    left: 47.5%;
  }

  .content_br04:after {
    content: " BR 04";
    top: 493px;
    left: 47.3%;
  }

  .content_br17:before {
    background-image: url(assets/img/br17.png);
    top: 249px;
    left: 32.5%;
  }

  .content_br17:after {
    content: "BR 17";
    top: 304px;
    left: 32%;
  }

  .content_br11:before {
    background-image: url(assets/img/br11.png);
    top: 74px;
    left: 52%;
  }

  .content_br11:after {
    content: "BR 11";
    top: 127px;
    left: 51.5%;
  }

  .content_br14:before {
    background-image: url(assets/img/br14.png);
    top: 438px;
    left: 72.5%;
  }

  .content_br14:after {
    content: "BR 14";
    top: 493px;
    left: 72%;
  }

  .content_ftcaq:before {
    background-image: url(assets/img/aquiles.png);
    top: 236px;
    left: 58%;
  }

  .content_ftcaq:after {
    content: "FTAQ";
    top: 291px;
    left: 57.7%;
  }

  .content_ftcti:before {
    background-image: url(assets/img/titan.png);
    top: 551px;
    left: 25.5%;
  }

  .content_ftcti:after {
    content: "FTCTI";
    top: 606px;
    left: 25%;
  }

  .content-map {
    background-color: #efefef;
    padding: 20px 0;
    background-image: url(00.jpg);
    background-size: cover;
    background-position: center center;
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
    filter: invert(78%) sepia(99%) saturate(996%) hue-rotate(80deg) brightness(88%) contrast(119%);
  }

  .BR11 {
    top: 0px;
    left: 283px;
    width: 387px;
    filter: invert(150%) sepia(109%) saturate(511%) hue-rotate(18046deg) brightness(118%) contrast(19%);
  }

  .BR14 {
    top: 326px;
    left: 513px;
    width: 306px;
    filter: invert(70%) sepia(100%) saturate(701%) hue-rotate(129deg) brightness(88%) contrast(50%);
  }

  .BR17 {
    top: 121px;
    left: 137px;
    width: 304px;
    filter: invert(70%) sepia(100%) saturate(701%) hue-rotate(195deg) brightness(88%) contrast(50%);

  }

  .FTCAQ {
    top: 162px;
    left: 384px;
    width: 284px;
    filter: invert(70%) sepia(100%) saturate(701%) hue-rotate(250deg) brightness(88%) contrast(50%);

  }

  .FTCTI {
    top: 249px;
    left: 61px;
    width: 334px;
    filter: invert(78%) sepia(109%) saturate(96%) hue-rotate(810deg) brightness(88%) contrast(79%);
  }

  .MAR {
    top: 366px;
    left: 50px;
    width: 125px;
    filter: invert(150%) sepia(109%) saturate(511%) hue-rotate(18046deg) brightness(118%) contrast(19%);
  }
</style>
