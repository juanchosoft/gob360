<?php
include './admin/include/head.php';
require './admin/include/generic_classes.php';
include './admin/classes/IngresoPae.php';

if (!empty($_GET['reporte']) && isset($_GET['reporte']) && $_GET['reporte'] > 0) {
  $rqst = array('id' => $_GET['reporte']);
  $arr = IngresoPae::getAll($rqst);

  $isvalid = $arr['output']['valid'];
  $data = $arr['output']['response'];

  if (count($data) > 0) {

        // Información del cliente y usuario
        $data = $data[0];
        $id = $data['id'] ? $data['id'] : '';
        
       
        $date = isset($data['date']) ? ($data['date']) : '';
        $provincia = isset($data['provincia']) ? ($data['provincia']) : '';
        $municipio = isset($data['municipio']) ? ($data['municipio']) : '';
        $tbl_departamento_id = isset($data['tbl_departamento_id']) ? ($data['tbl_departamento_id']) : '';
        $tbl_municipio_id =  isset($data['tbl_municipio_id']) ? ($data['tbl_municipio_id']) : '';
        $tbl_sede_educativa_id = isset($data['tbl_sede_educativa_id']) ? ($data['tbl_sede_educativa_id']) : '';
        $tbl_instituciones_educativas_id = isset($data['tbl_instituciones_educativas_id']) ? ($data['tbl_instituciones_educativas_id']) : '';
        $rector = isset($data['rector']) ? ($data['rector']) : '';
        $cc = isset($data['cc']) ? ($data['cc']) : '';
        $tel = isset($data['tel']) ? ($data['tel']) : '';
        $sector = isset($data['sector']) ? ($data['sector']) : '';
        $atencion_mayor_indigena = isset($data['atencion_mayor_indigena']) ? ($data['atencion_mayor_indigena']) : '';
        $complemento_ampm = isset($data['complemento_ampm']) ? ($data['complemento_ampm']) : '';
        $complemento_ampm_indus = isset($data['complemento_ampm_indus']) ? ($data['complemento_ampm_indus']) : '';
        $preparado_sitio = isset($data['preparado_sitio']) ? ($data['preparado_sitio']) : '';
        $comida_transportada = isset($data['comida_transportada']) ? ($data['comida_transportada']) : '';
        $comedor_escolar = isset($data['comedor_escolar']) ? ($data['comedor_escolar']) : '';
        $concepto_sanitario = isset($data['concepto_sanitario']) ? ($data['concepto_sanitario']) : '';
        $fecha_expedicion = isset($data['fecha_expedicion']) ? ($data['fecha_expedicion']) : '';
        $cerca_a_contaminacion = isset($data['cerca_a_contaminacion']) ? ($data['cerca_a_contaminacion']) : '';
        $sede_conflicto_armado = isset($data['sede_conflicto_armado']) ? ($data['sede_conflicto_armado']) : '';
        $frecuencia_conflicto = isset($data['frecuencia_conflicto']) ? ($data['frecuencia_conflicto']) : '';
        $espacio_almacenamiento = isset($data['espacio_almacenamiento']) ? ($data['espacio_almacenamiento']) : '';
        $material_techo = isset($data['material_techo']) ? ($data['material_techo']) : '';
        $material_piso = isset($data['material_piso']) ? ($data['material_piso']) : '';
        $material_paredes = isset($data['material_paredes']) ? ($data['material_paredes']) : '';
        $espacio_dedicado = isset($data['espacio_dedicado']) ? ($data['espacio_dedicado']) : '';
        $material_techo_preparacion = isset($data['material_techo_preparacion']) ? ($data['material_techo_preparacion']) : '';
        $material_piso_preparacion = isset($data['material_piso_preparacion']) ? ($data['material_piso_preparacion']) : '';
        $material_paredes_preparacion = isset($data['material_paredes_preparacion']) ? ($data['material_paredes_preparacion']) : '';
        $espacio_consumo = isset($data['espacio_consumo']) ? ($data['espacio_consumo']) : '';
        $acceso_electricidad = isset($data['acceso_electricidad']) ? ($data['acceso_electricidad']) : '';
        $acceso_agua = isset($data['acceso_agua']) ? ($data['acceso_agua']) : '';
        $tipo_agua = isset($data['tipo_agua']) ? ($data['tipo_agua']) : '';
        $tipo_gas = isset($data['tipo_gas']) ? ($data['tipo_gas']) : '';
        $tiene_recoleccion_basura = isset($data['tiene_recoleccion_basura']) ? ($data['tiene_recoleccion_basura']) : '';
        $disposicion_derechos_pae = isset($data['disposicion_derechos_pae']) ? ($data['disposicion_derechos_pae']) : '';
        $disposicion_no_organicos_pae = isset($data['disposicion_no_organicos_pae']) ? ($data['disposicion_no_organicos_pae']) : '';
        $clasificacion_residuos = isset($data['clasificacion_residuos']) ? ($data['clasificacion_residuos']) : '';
        $neveras_almacenamiento = isset($data['neveras_almacenamiento']) ? ($data['neveras_almacenamiento']) : '';
        $cant_neveras = isset($data['cant_neveras']) ? ($data['cant_neveras']) : '';
        $neveras_funcionando = isset($data['neveras_funcionando']) ? ($data['neveras_funcionando']) : '';
        $tamano_neveras_principales = isset($data['tamano_neveras_principales']) ? ($data['tamano_neveras_principales']) : '';
        $congeladores = isset($data['congeladores']) ? ($data['congeladores']) : '';
        $cantidad_congeladores = isset($data['cantidad_congeladores']) ? ($data['cantidad_congeladores']) : '';
        $congeladores_funcionando = isset($data['congeladores_funcionando']) ? ($data['congeladores_funcionando']) : '';
        $tamano_congelador = isset($data['tamano_congelador']) ? ($data['tamano_congelador']) : '';
        $almacena_alto_suelo = isset($data['almacena_alto_suelo']) ? ($data['almacena_alto_suelo']) : '';
        $elementos_almacenamiento = isset($data['elementos_almacenamiento']) ? ($data['elementos_almacenamiento']) : '';
        $estufa = isset($data['estufa']) ? ($data['estufa']) : '';
        $cant_estufas = isset($data['cant_estufas']) ? ($data['cant_estufas']) : '';
        $cant_quemadores = isset($data['cant_quemadores']) ? ($data['cant_quemadores']) : '';
        $cant_quemadores_buenos = isset($data['cant_quemadores_buenos']) ? ($data['cant_quemadores_buenos']) : '';
        $licuadora = isset($data['licuadora']) ? ($data['licuadora']) : '';
        $cant_licuadora = isset($data['cant_licuadora']) ? ($data['cant_licuadora']) : '';
        $cant_licuadoras_buenas = isset($data['cant_licuadoras_buenas']) ? ($data['cant_licuadoras_buenas']) : '';
        $cant_licuadoras_ind = isset($data['cant_licuadoras_ind']) ? ($data['cant_licuadoras_ind']) : '';
        $posee_ollas_pae = isset($data['posee_ollas_pae']) ? ($data['posee_ollas_pae']) : '';
        $posee_cuchillos_pae = isset($data['posee_cuchillos_pae']) ? ($data['posee_cuchillos_pae']) : '';
        $posee_cucharones_pae = isset($data['posee_cucharones_pae']) ? ($data['posee_cucharones_pae']) : '';
        $cant_ninos_pae_sentados = isset($data['cant_ninos_pae_sentados']) ? ($data['cant_ninos_pae_sentados']) : '';
        $cant_platos_pae = isset($data['cant_platos_pae']) ? ($data['cant_platos_pae']) : '';
        $pocillos_pae = isset($data['pocillos_pae']) ? ($data['pocillos_pae']) : '';
        $cucharas_pae = isset($data['cucharas_pae']) ? ($data['cucharas_pae']) : '';
        $posee_sanitario_exclusivo_pae = isset($data['posee_sanitario_exclusivo_pae']) ? ($data['posee_sanitario_exclusivo_pae']) : '';
        $posee_lavamanos_pae = isset($data['posee_lavamanos_pae']) ? ($data['posee_lavamanos_pae']) : '';
        $estado_sede = isset($data['estado_sede']) ? ($data['estado_sede']) : '';
        $estado_techo_almacenamiento = isset($data['estado_techo_almacenamiento']) ? ($data['estado_techo_almacenamiento']) : '';
        $estado_piso_almacenamiento = isset($data['estado_piso_almacenamiento']) ? ($data['estado_piso_almacenamiento']) : '';
        $estado_paredes_almacenamiento = isset($data['estado_paredes_almacenamiento']) ? ($data['estado_paredes_almacenamiento']) : '';
        $estado_techo_preparacion = isset($data['estado_techo_preparacion']) ? ($data['estado_techo_preparacion']) : '';
        $estado_piso_preparacion = isset($data['estado_piso_preparacion']) ? ($data['estado_piso_preparacion']) : '';
        $material_piso_preparacion = isset($data['material_piso_preparacion']) ? ($data['material_piso_preparacion']) : '';
        $cargue_info = isset($data['cargue_info']) ? ($data['cargue_info']) : '';
        $clasificacion = isset($data['clasificacion']) ? ($data['clasificacion']) : '';
        $sede_alcantarillado = isset($data['sede_alcantarillado']) ? ($data['sede_alcantarillado']) : '';
        $visita = isset($data['visita']) ? ($data['visita']) : '';
        $ano = isset($data['ano']) ? ($data['ano']) : '';
        $latitud = isset($data['latitud']) ? ($data['latitud']) : '';
        $longitud = isset($data['longitud']) ? ($data['longitud']) : '';
        $observaciones = isset($data['observaciones']) ? ($data['observaciones']) : '';
        $estado_paredes_preparacion = isset($data['estado_paredes_preparacion']) ? ($data['estado_paredes_preparacion']) : '';
        $tenedores_pae = isset($data['tenedores_pae']) ? ($data['tenedores_pae']) : '';
        $cant_canecas = isset($data['cant_canecas']) ? ($data['cant_canecas']) : '';
        $ninos_focalizados = isset($data['ninos_focalizados']) ? ($data['ninos_focalizados']) : '';
        $disposicion_desechos_pae = isset($data['disposicion_desechos_pae']) ? ($data['disposicion_desechos_pae']) : '';
        $nombre_sede = isset($data['nombre']) ? ($data['nombre']) : '';
        $nombre_institucion = isset($data['nombre_institucion']) ? ($data['nombre_institucion']) : '';
        $nombre_vereda = isset($data['nombre_vereda']) ? ($data['nombre_vereda']) : '';
        $tbl_user_id = $_SESSION['session_user']['id'];
        $texto = $nombre_sede; // Texto del globo
        $usuario_nombre = isset($data['Usuario']) ? ($data['Usuario']) : '';
        $usuario_apellido = isset($data['apellido']) ? ($data['apellido']) : '';
        $usuario_cc = isset($data['usuario_cc']) ? ($data['usuario_cc']) : '';
        $usuario_cc_numero = isset($data['usuario_cc_num']) ? ($data['usuario_cc_num']) : '';
        $usuario_email = isset($data['usuario_email']) ? ($data['usuario_email']) : '';

    } else {
?>
<script type='text/javascript'>
alert('Sin resultados');
window.location = 'detalle_visitas.php';
</script>
<?php
        return;
    }
} else { ?>
<script type='text/javascript'>
alert('Debes enviar una reporte para generar el documento');
window.location = 'detalle_visitas.php';
</script>
<?php
    return;
}
?>




<body class="">
    <!-- [ Pre-loader ] start -->
    <div class="loader-bg">
        <div class="loader-track">
            <div class="loader-fill"></div>
        </div>
    </div>
    <!-- [ Pre-loader ] End -->
    <!-- [ navigation menu ] start -->
    <?php
    include './admin/include/navbar.php';
    ?>
    <!-- [ navigation menu ] end -->
    <!-- [ Header ] start -->
    <?php
    include './admin/include/header.php';
    ?>
    <!-- [ Header ] end -->

    <style>
        #map {
            height: 500px;
            width: 600px;
            border-radius: 10px;
        }
    </style>
    <!-- [ Header ] end -->
    <!-- [ Main Content ] start -->
    <div class="pcoded-main-container">
        <div class="pcoded-wrapper">
            <div class="pcoded-content">
                <div class="pcoded-inner-content">
                    <div class="main-body">
                        <div class="page-wrapper">
                            <!-- [ breadcrumb ] start -->
                            <div class="page-header">
                                <div class="page-block">
                                    <div class="row align-items-center">
                                        <div class="col-md-12">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <h5 class="m-b-10">Reporte de Visitas a Sedes Escolares</h5>
<?php include './admin/include/btn_back.php'; ?>
                                            </div>
                                            <ul class="breadcrumb">
                                                <li class="breadcrumb-item"><a href="index.php"><i
                                                            class="feather icon-home"></i></a></li>
                                                <li class="breadcrumb-item"><a href="#!">Secretaria de Educación</a></li>
                                                <li class="breadcrumb-item"><a href="#!">Caraterización Pae</a></li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!-- [ breadcrumb ] end -->
                            <!-- [ Main Content ] start -->
                            <div class="col-md-12 col-xl-12">
                                <div class="card">
                                    <div class="card-header p-4">
                                    <div class="d-flex align-items-center justify-content-center gap-4 mb-3">
                                        <?php include 'admin/include/generinc_brand_logo.php'; ?>
                                        <img src="assets/img/pae.png" alt="Logo PAE" width="100px">
                                 
                                            <h4 class="mb-0 ">ACTA DE CARACTERIZACION NO <?php echo $id; ?></h4>
                                        </div>
                                    </div>
                                    <div class="card-body text-center">
                                        <div class="row mb-4">
                                            <div class="col-sm-6">
                                                <h5 class="mb-3"></h5>
                                                <h3 class="text-dark mb-1"></h3>

                                                <h5 class="mb-0"> REPUBLICA DE COLOMBIA</h5>
                                                <h5 class="mb-0"> DEPARTAMENTO DE SANTANDER</h5>
                                                <h5 class="mb-0"> GOBERNACIÓN DE SANTANDER</h5>
                                                <h5 class="mb-0"> SECRETARIA DE EDUCACIÓN</h5>

                                            </div>
                                            <div class="col-sm-6">
                                                <h5 class="mb-3"> </h5>
                                                <h3 class="text-dark mb-1"> </h3>
                                                <div><strong>Pág. 1</strong> de 1</div>
                                                <div><strong>Código:005</strong> </div>
                                                <div><strong>Versión:</strong> 7</div>
                                                <div><strong>Fecha de creación:</strong> <?php echo $date; ?>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="table-responsive">
                                            <table class="table">
                                                <thead>
                                                    <tr>
                                                        <th>Fecha visita</th>
                                                        <th>Provincia</th>
                                                        <th>Municipio</th>
                                                        <th>Vereda</th>
                                                        <th>Sector</th>
                                                        <th>Institución Educativa</th>
                                                        <th>Sede Educativa</th>

                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <tr>
                                                        <td class="left strong"><?php echo $date; ?></td>
                                                        <td class="left"><?php echo $provincia; ?></td>                                                 
                                                        <td class="right"><?php echo $municipio; ?></td>
                                                        <td class="right"><?php echo $nombre_vereda; ?></td>
                                                        <td class="left"><?php echo $sector; ?></td>
                                                        <td class="right"><?php echo $nombre_institucion; ?></td>
                                                        <td class="right"><?php echo $nombre_sede; ?></td>
                                                    </tr>

                                                </tbody>
                                            </table>
                                        </div>
                                        <div class="table-responsive">
                                            <table class="table">
                                                <thead>
                                                    <tr>
                                                        <th>Nombre Rector</th>
                                                        <th>Identificación Rector</th>
                                                        <th>Telefono</th>
                                                        <th>Email</th>    
                                                        <th>Niños Focalizados</th>       
                                                        <th>Atencion Mayoritaria Indigena</th>                                         

                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <tr>
                                                        <td class="left strong"><?php echo $rector; ?></td>
                                                        <td class="left"><?php echo $cc; ?></td>
                                                        <td class="right"><?php echo $tel; ?></td>
                                                        <td class="right">rector@correo.com</td>  
                                                        <td class="right"><b><?php echo $ninos_focalizados; ?></b> </td>   
                                                        <td class="right"><b><?php echo $atencion_mayor_indigena; ?></b> </td>                                                
                                                    </tr>
                                                </tbody>
                                            </table>
                                           <hr>
                                           <center>
                                           <h5>Resultado Caracterización</h5> 
                                           </center>
                                           <hr>
                                        </div><h5 class="red-background">Utensilios de Restaurante</h5> 
                                <table class="table table-bordered table-sm">                               
                                <tbody>
                                    <tr>
                                    <td>Posee Cucharones Pae</td>
                                    <td><?php echo $posee_cucharones_pae; ?></td>
                                    <td>Posee Cuchillos PAE</td>
                                    <td><?php echo $posee_cuchillos_pae; ?></td>
                                    </tr>
                                    <tr>
                                    <td>Posee Ollas</td>
                                    <td><?php echo $posee_ollas_pae; ?></td>
                                    <td>Cantidad de Platos</td>
                                    <td><?php echo $cant_platos_pae; ?></td>
                                    </tr>
                                    <tr>
                                    <td>Candidad Cucharas</td>
                                    <td><?php echo $cucharas_pae; ?></td>
                                    <td>Cantidad Tenedores</td>
                                    <td><?php echo $tenedores_pae; ?></td>
                                    </tr>
                                    <tr>
                                    <td>Cantidad Pocillos</td>
                                    <td><?php echo $pocillos_pae; ?></td>
                                    <td></td>
                                    <td></td>
                                    </tr>
                                   
                                </tbody>
                                </table>
                                <h5 class="red-background">Elementos Cocinas</h5> 
                                <table class="table table-bordered table-sm">                                 
                                <tbody>
                                    <tr>
                                    <td>Tiene Neveras Almacenamiento</td>
                                    <td><?php echo $neveras_almacenamiento; ?></td>
                                    <td>Cantidad Neveras</td>
                                    <td><?php echo $cant_neveras; ?></td>
                                    </tr>
                                    <tr>
                                    <td>Neveras Funcionando</td>
                                    <td><?php echo $neveras_funcionando; ?></td>
                                    <td>Tamaño Neveras</td>
                                    <td><?php echo $tamano_neveras_principales; ?></td>
                                    </tr>
                                    <tr>
                                    <td>Cantidad de Congeladores</td>
                                    <td><?php echo $cantidad_congeladores; ?></td>
                                    <td>Congelarores Buenos</td>
                                    <td><?php echo $congeladores_funcionando; ?></td>
                                    </tr>
                                    <tr>
                                    <td>Tamaño Congeladores</td>
                                    <td><?php echo $tamano_congelador; ?></td>
                                    <td></td>
                                    <td></td>                                   
                                    </tr>
                                    <tr>
                                    <td>Posee Estufas</td>
                                    <td><?php echo $estufa ?></td>
                                    <td>Total Quemadores</td>
                                    <td><?php echo $cant_quemadores?></td>
                                    </tr>
                                    <tr>
                                        <td>Quemadores Buenos</td>
                                        <td> <?php echo $cant_quemadores; ?> </td>
                                        <td>Posee Licuadoras</td>
                                        <td><?php echo $licuadora; ?></td>
                                    </tr>
                                    <tr>
                                        <td>Cantidad Licuadoras</td>
                                        <td><?php echo $cant_licuadora?></td>
                                        <td>Licuadoras Buenas</td>
                                        <td><?php echo $cant_licuadoras_buenas?></td>
                                    </tr>
                                    <tr>
                                        <td>Posee Licuadoras Industriales</td>
                                        <td><?php echo $cant_licuadoras_ind?></td>
                                        <td></td>
                                        <td></td>
                                    </tr>
                                  </tbody>
                                </table>
                                <h5 class="red-background">Estado Lugares de Preparación y Almacenamiento </h5> 
                                <table class="table table-bordered table-sm">                                 
                                <tbody>
                                    <tr>
                                    <td>Tiene Espacio de Almacenamiento</td>
                                    <td><?php echo $espacio_almacenamiento; ?></td>
                                    <td>Tiene Espacio de Preparación</td>
                                    <td><?php echo $espacio_preparacion; ?></td>
                                    </tr>
                                    <tr>
                                    <td>Almacena en Tarimas o Estibas Alto del Suelo</td>
                                    <td><?php echo $almacena_alto_suelo; ?></td>
                                    <td>Elemento Usado Para Almacenamiento</td>
                                    <td><?php echo $elementos_almacenamiento; ?></td>
                                    </tr>
                                    <tr>
                                    <td>Estado Techo Almacenamiento</td>
                                    <td><?php echo $estado_techo_almacenamiento; ?></td>
                                    <td>Estado Piso Almacenamiento</td>
                                    <td><?php echo $estado_piso_almacenamiento; ?></td>
                                    </tr>
                                    <tr>
                                    <td>Estado paredes Almacenamiento</td>
                                    <td><?php echo $estado_paredes_almacenamiento; ?></td>
                                    <td>Estado techo Preparación</td>
                                    <td><?php echo $estado_techo_preparacion; ?></td>
                                    </tr>
                                    <tr>
                                    <td>Estado piso Preparación</td>
                                    <td><?php echo $estado_piso_preparacion; ?></td>
                                    <td>Estado paredes Preparación</td>
                                    <td><?php echo $estado_paredes_preparacion; ?></td>
                                    </tr>
                                   
                                </tbody>
                                </table>

                                <h5 class="red-background">Materiales Preparación y Almacenamiento PAE</h5> 
                                <table class="table table-bordered table-sm">                                 
                                <tbody>
                                    <tr>
                                    <td>Tipo Material Techo Almacenamiento</td>
                                    <td><?php echo $material_techo; ?></td>
                                    <td>Tipo Material Paredes Almacenamiento</td>
                                    <td><?php echo $material_paredes; ?></td>
                                    </tr>
                                    <tr>
                                    <td>Tipo Material Piso Almacenamiento</td>
                                    <td><?php echo $material_piso; ?></td>
                                    <td>Tipo Material Techo Preparación</td>
                                    <td><?php echo $material_techo_preparacion; ?></td>
                                    </tr>
                                    <tr>
                                    <td>Tipo Material Paredes Preparación </td>
                                    <td><?php echo $material_paredes_preparacion; ?></td>
                                    <td>Tipo Material Piso Preparación</td>
                                    <td><?php echo $material_piso_preparacion; ?></td>
                                    </tr>
                                   
                                </tbody>
                                </table>


                                <h5 class="red-background">Acceso a servicios publicos y tipo</h5> 
                                <table class="table table-bordered table-sm">                                 
                                <tbody>
                                    <tr>
                                    <td>Tiene Acceso al sistema de Agua</td>
                                    <td><?php echo $acceso_agua; ?></td>
                                    <td>Obtención del agua para el PAE</td>
                                    <td><?php echo $acceso_agua; ?></td>
                                    </tr>
                                    <tr>
                                    <td>Tiene Acceso a Electricidad</td>
                                    <td><?php echo $acceso_electricidad; ?></td>
                                    <td>Tipo de elemento de cocción Alimentos </td>
                                    <td><?php echo $tipo_gas; ?></td>
                                    </tr>
                                    <tr>
                                    <td>Tiene Acceso a alcantarillado </td>
                                    <td><?php echo $sede_alcantarillado; ?></td>
                                    <td>Recoleción de Basuras</td>
                                    <td><?php echo $tiene_recoleccion_basura; ?></td>
                                    </tr>
                                    <tr>
                                    <td>Disposición de Desechos Orgánicos </td>
                                    <td><?php echo $disposicion_desechos_pae; ?></td>
                                    <td>Disposición de Desechos No Organicos</td>
                                    <td><?php echo $disposicion_no_organicos_pae; ?></td>
                                    </tr>
                                    <tr>
                                    <td>Clasifica Residuos PAE </td>
                                    <td><?php echo $clasificacion_residuos; ?></td>
                                    <td>Disposición de Desechos No Organicos</td>
                                    <td><?php echo $disposicion_no_organicos_pae; ?></td>
                                    </tr>
                                    <tr>
                                    <td>Cantidad Canecas</td>
                                    <td><?php echo $cant_canecas; ?></td>
                                    <td>Disposición de Desechos No Organicos</td>
                                    <td><?php echo $disposicion_no_organicos_pae; ?></td>
                                    </tr>
                                </tbody>
                                </table>


                                <h5 class="red-background">Restaurante PAE</h5> 
                                <table class="table table-bordered table-sm">                                 
                                <tbody>
                                    <tr>
                                    <td>Almuerzo Preparado en sitio</td>
                                    <td><?php echo $preparado_sitio; ?></td>
                                    <td>Almuerzo transportado (Comida Caliente) </td>
                                    <td><?php echo $comida_transportada; ?></td>
                                    </tr>
                                    <tr>
                                    <td>Preparación Complemento en Sitio</td>
                                    <td><?php echo $complemento_ampm; ?></td>
                                    <td>Complemento industrializado</td>
                                    <td><?php echo $complemento_ampm_indus; ?></td>
                                    </tr>
                                    <tr>
                                    <td>Posee Comedor Escolar</td>
                                    <td><?php echo $comedor_escolar; ?></td>
                                    <td>Tiene Concepto Sanitario</td>
                                    <td><?php echo $concepto_sanitario; ?></td>
                                    </tr>
                                    <tr>
                                    <td>Fecha Concepto Sanitario</td>
                                    <td><?php echo $fecha_expedicion; ?></td>
                                    <td>Baterias Sanitarias para uso Exclusivo PAE </td>
                                    <td><?php echo $posee_sanitario_exclusivo_pae; ?></td>
                                    </tr>
                                    <tr>
                                    <tr>
                                    <td>Lavamanos para uso Exclusivo PAE</td>
                                    <td><?php echo $posee_lavamanos_pae; ?></td>
                                    <td>Cercanía Fuentes de contaminación PAE </td>
                                    <td><?php echo $cerca_a_contaminacion; ?></td>
                                    </tr>
                                    <tr>
                                    <td>Ubicación en Zona de Conflicto Armado </td>
                                    <td><?php echo $sede_conflicto_armado; ?></td>
                                    <td>Afectación PAE por Conflicto Armado  </td>
                                    <td><?php echo $frecuencia_conflicto; ?></td>
                                    </tr>
                                   
                                   
                                </tbody>
                                </table>

                                <h5 class="red-background">Estado Sedes Educativas</h5> 
                                <table class="table table-bordered table-sm">                                 
                                <tbody>
                                    <tr>
                                    <td>Estado Sede Educativa</td>
                                    <td><?php echo $estado_sede; ?></td>
                                    <td>Estado Techo Sede Educativa</td>
                                    <td><?php echo $estado_techo_almacenamiento; ?></td>
                                    </tr>
                                                                
                                </tbody>
                                </table>


                                <div class="col-xl-8 col-lg-10 col-md-10 col-sm-12 col-12 mx-auto">
                                    <h6 class="text-dark text-center mb-3">Geolocalización</h6>
                                    <div id="map" style="height: 400px; width: 100%; border: 1px solid #ccc; border-radius: 8px;"></div>
                                </div>
                                    <hr>
                                     <div class="offset-xl-2 col-xl-8 col-lg-12 col-md-12 col-sm-12 col-12">
                                        <div style="background-color: #f8f9fa; border: 1px solid #ccc; border-radius: 8px; padding: 20px;">
                                            <h6 class="text-danger mb-2" style="font-weight: bold;">Observaciones</h6>
                                            <p class="mb-0" style="color: #333;"><?php echo htmlspecialchars($observaciones); ?></p>
                                        </div>
                                    </div>
                                    <div class="offset-xl-2 col-xl-8 col-lg-12 col-md-12 col-sm-12 col-12">
                                        <div style="background-color: #f8f9fa; border: 1px solid #ccc; border-radius: 8px; padding: 20px;">
                                            <h6 class="text-danger mb-2" style="font-weight: bold;">Imagénes</h6>
                                            <p class="mb-0" style="color: #333;"></p>
                                        </div>
                                    </div>
                                    
                                    <h6>Informacion recolectada por: <?php echo $usuario_nombre; ?> <?php echo $usuario_apellido; ?> </h6>
                                    <h6>Tipo de documento y numero: <?php echo $usuario_cc; ?> <?php echo $usuario_cc_numero; ?> </h6>
                                    <h6>Email:<?php echo $usuario_email; ?> </h6>
                                        
                                    </div>
                                  

    
                                </div>

                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <?php include 'admin/include/gerenic_script.php'; ?>
        <!-- Required Js -->
        <script src="assets/js/vendor-all.min.js"></script>
        <script src="assets/js/plugins/bootstrap.min.js"></script>
        <script src="assets/js/pcoded.min.js"></script>

        <!-- prism Js -->
        <script src="assets/js/plugins/prism.js"></script>
        <script src="assets/js/plugins/apexcharts.min.js"></script>

        <script src="admin/js/ingreso_pae.js"></script>
        <script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>
        <script>
          function initMap() {
            const location = { lat: <?= $latitud ?>, lng: <?= $longitud ?> };

            const map = new google.maps.Map(document.getElementById("map"), {
                center: location,
                zoom: 18,
                mapTypeId: 'satellite',
                streetViewControl: true // Habilita Street View
            });

            const icon = {
                url: "assets/img/pae.png",
                scaledSize: new google.maps.Size(50, 50),
                origin: new google.maps.Point(0, 0),
                anchor: new google.maps.Point(25, 50)
            };

            const marker = new google.maps.Marker({
                position: location,
                map: map,
                icon: icon,
                title: "Sede PAE"
            });

            const infoWindow = new google.maps.InfoWindow({
                content: `<strong><?= addslashes($texto) ?></strong>`
            });

            marker.addListener("click", () => {
                infoWindow.open(map, marker);
            });
        }
</script>
    <!-- Reemplaza TU_API_KEY con tu clave de Google Maps -->
    <script async
        src="https://maps.googleapis.com/maps/api/js?key=AIzaSyAbqZVEnSpIPF5dKe89pju2rmQaM58wvY0&callback=initMap">
    </script>

        <script>
        setTimeout(function() {
            $("#tbl_departamento_id").val('68')
        }, 500);

        setTimeout(function() {
            DEPARTAMENTO.getMunicipios();
        }, 1000);
        APORTES.onchangePais();
        </script>
</body>

</html>