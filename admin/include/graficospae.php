<?php
// Neveras
$estado_neveras = ($neveras == $neveras_fun) ? 'Aceptable' :
                  ($neveras_fun == 0 ? 'Inaceptable' : 'Insuficiente');

// Congeladores
$estado_congeladores = ($congeladores == $congeladores_funcionando) ? 'Aceptable' :
                       ($congeladores_funcionando == 0 ? 'Inaceptable' : 'Insuficiente');

// Estufas y quemadores
$estado_estufas = ($quemadores_estufas == $quemadores_estufas_buenos) ? 'Aceptable' :
                  ($quemadores_estufas_buenos == 0 ? 'Inaceptable' : 'Insuficiente');

// Licuadoras
$estado_licuadoras = ($licuadoras_industriales == $licuadoras) ? 'Aceptable' :
                     ($licuadoras == 0 ? 'Inaceptable' : 'Insuficiente');

// Conteo para estado general
$estados_individuales = [$estado_neveras, $estado_congeladores, $estado_estufas, $estado_licuadoras];
$conteo_aceptables = count(array_filter($estados_individuales, fn($e) => $e == 'Aceptable'));
$conteo_inaceptables = count(array_filter($estados_individuales, fn($e) => $e == 'Inaceptable'));
$conteo_totales = count($estados_individuales);

// Estado general
if ($conteo_aceptables == $conteo_totales) {
    $estado_general = 'Aceptable';
    $clase_general = 'bg-success text-white';
} elseif ($conteo_inaceptables > 0) {
    $estado_general = 'Inaceptable';
    $clase_general = 'bg-danger text-white';
} else {
    $estado_general = 'Insuficiente';
    $clase_general = 'bg-warning text-dark';
}
// Estado general del techo de la sede educativa
$estado_techo_general = 'Insuficiente'; // Por defecto
$techo_total = $estado_techo_almacenamiento_bueno + $estado_techo_almacenamiento_regular + $estado_techo_almacenamiento_malo;

if ($estado_techo_almacenamiento_bueno > $estado_techo_almacenamiento_regular && $estado_techo_almacenamiento_bueno > $estado_techo_almacenamiento_malo) {
    $estado_techo_general = 'Aceptable';
    $clase_estado_techo = 'bg-success text-white';
} elseif ($estado_techo_almacenamiento_malo > $estado_techo_almacenamiento_bueno && $estado_techo_almacenamiento_malo > $estado_techo_almacenamiento_regular) {
    $estado_techo_general = 'Inaceptable';
    $clase_estado_techo = 'bg-danger text-white';
} else {
    $estado_techo_general = 'Insuficiente';
    $clase_estado_techo = 'bg-warning text-dark';
}

?>


<div class="card-body">


    </div>

    <div class="row">
        <!-- Estado sedes educativas-->
        <div class="col-sm-12">
            <div id="item1_estado_sedes" class="seccion"  class="card">
                <div class="card-body">
                    <!-- <div class="text-center mb-4">
                        <img src="assets/img/colegio.png" alt="" width="100px">
                        <h4 class="card-title mt-2">Estado General de las Sedes Educativas</h4>
                    </div> -->
                    <!-- Cards internas organizadas horizontalmente -->
                    <div class="row justify-content-center g-3">

                        <!-- Card: estado techos sedes educativas -->
                        <?php if (($estado_techo_almacenamiento_bueno + $estado_techo_almacenamiento_regular + $estado_techo_almacenamiento_malo) > 0): ?>
                        <div class="col-12 col-sm-6 col-md-4 col-lg-4">
                            <div class="card h-100 shadow-sm">
                                <div class="card-body">
                                    <center>
                                        <h5 class="card-title">Estado Techo Sede Educativa</h5>
                                        <div id="graficoEstadoTechoSedeEducativaContainer" style="height: 180px; margin-bottom: 20px;"></div>

                                        <h6 class="card-text"> Bueno =
                                            <b><?php echo number_format($estado_techo_almacenamiento_bueno, 0); ?></b>
                                            <br> Regular =
                                            <b><?php echo number_format($estado_techo_almacenamiento_regular, 0); ?></b>
                                            <br> Malo =
                                            <b><?php echo number_format($estado_techo_almacenamiento_malo, 0); ?></b>
                                        </h6>
                                        <hr>
                                    </center>
                                </div>
                            </div>
                        </div>
                        <?php elseif ($caracterizaciones == 0): ?>
                        <div class="col-12">
                            <div class="alert alert-warning text-center py-3 mb-0" role="alert">
                                <i class="feather icon-info"></i>
                                <strong>Sin datos disponibles</strong><br>
                                <small>No hay información PAE registrada para el municipio y vigencia seleccionados.</small>
                            </div>
                        </div>
                        <?php else: ?>
                        <div class="col-12">
                            <div class="alert alert-info text-center py-2 mb-0" role="alert">
                                <i class="feather icon-info"></i>
                                <small>No hay información de estado de infraestructura (techo/piso/paredes) registrada para este municipio y vigencia.</small>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div> <!-- end row -->
                </div>
            </div>
        </div>
    </div>
<!-- Estado del Almacenamiento, Preparación y Consumo -->
<div class="col-sm-12">
    <div id="item2_estado_almacenamiento" class="seccion" style="display:none;">
        <div class="card-body">
            <!-- <div class="text-center mb-4">
                <img src="assets/img/elementococina.png" alt="Restaurante" width="120px">
                <h5 class="card-title mt-2">Estado del Almacenamiento, Preparación y Consumo</h5>
            </div> -->

            <!-- Cards internas organizadas horizontalmente -->
            <div class="row justify-content-center g-4">
                <!-- Card: almacenamiento PAE -->
                <?php if (($espacio_almacenamiento_si + $espacio_almacenamiento_no) > 0): ?>
                <div class="col-12 col-sm-6 col-md-4 col-lg-4 mb-4">
                    <div class="card h-100 shadow-sm">
                        <div class="card-body text-center">
                            <h5 class="card-title">Espacio de Almacenamiento</h5>
                            <div id="graficoAlmacenamientoContainer" style="height: 180px; margin-bottom: 20px;"></div>
                            <h6 class="card-text">Tienen Espacio de Almacenamiento:<br>
                                Si = <b><?php echo number_format($espacio_almacenamiento_si, 0); ?></b><br>
                                No = <b><?php echo number_format($espacio_almacenamiento_no, 0); ?></b>
                            </h6>
                        </div>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Card: almacenamiento en tarimas -->
                <?php if (($almacena_alto_suelo_si + $almacena_alto_suelo_no) > 0): ?>
                <div class="col-12 col-sm-6 col-md-4 col-lg-4 mb-4">
                    <div class="card h-100 shadow-sm">
                        <div class="card-body text-center">
                            <h5 class="card-title">Almacenamiento en Tarimas o Estibas Alto del Suelo</h5>
                            <div id="graficoAlmacenamientoTarimasContainer" style="height: 180px; margin-bottom: 20px;"></div>
                            <h6 class="card-text">Si = <b><?php echo number_format($almacena_alto_suelo_si, 0); ?></b><br>
                                No = <b><?php echo number_format($almacena_alto_suelo_no, 0); ?></b>
                            </h6>
                        </div>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Card: estado techo almacenamiento -->
                <?php if (($estado_techo_almacenamiento_bueno + $estado_techo_almacenamiento_regular + $estado_techo_almacenamiento_malo) > 0): ?>
                <div class="col-12 col-sm-6 col-md-4 col-lg-4 mb-4">
                    <div class="card h-100 shadow-sm">
                        <div class="card-body text-center">
                            <h5 class="card-title">Estado Techo Almacenamiento</h5>
                            <div id="graficoEstadoTechoAlmacenamientoContainer" style="height: 180px; margin-bottom: 20px;"></div>
                            <h6 class="card-text">Bueno = <b><?php echo number_format($estado_techo_almacenamiento_bueno, 0); ?></b><br>
                                Regular = <b><?php echo number_format($estado_techo_almacenamiento_regular, 0); ?></b><br>
                                Malo = <b><?php echo number_format($estado_techo_almacenamiento_malo, 0); ?></b>
                            </h6>
                        </div>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Card: estado paredes almacenamiento -->
                <?php if (($estado_paredes_almacenamiento_bueno + $estado_paredes_almacenamiento_regular + $estado_paredes_almacenamiento_malo) > 0): ?>
                <div class="col-12 col-sm-6 col-md-4 col-lg-4 mb-4">
                    <div class="card h-100 shadow-sm">
                        <div class="card-body text-center">
                            <h5 class="card-title">Estado Paredes Almacenamiento</h5>
                            <div id="graficoEstadoParedesAlmacenamientoContainer" style="height: 180px; margin-bottom: 20px;"></div>
                            <h6 class="card-text">Bueno = <b><?php echo number_format($estado_paredes_almacenamiento_bueno, 0); ?></b><br>
                                Regular = <b><?php echo number_format($estado_paredes_almacenamiento_regular, 0); ?></b><br>
                                Malo = <b><?php echo number_format($estado_paredes_almacenamiento_malo, 0); ?></b>
                            </h6>
                        </div>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Card: estado piso almacenamiento -->
                <?php if (($estado_piso_almacenamiento_bueno + $estado_piso_almacenamiento_regular + $estado_piso_almacenamiento_malo) > 0): ?>
                <div class="col-12 col-sm-6 col-md-4 col-lg-4 mb-4">
                    <div class="card h-100 shadow-sm">
                        <div class="card-body text-center">
                            <h5 class="card-title">Estado Piso Almacenamiento</h5>
                            <div id="graficoEstadoPisoAlmacenamientoContainer" style="height: 180px; margin-bottom: 20px;"></div>
                            <h6 class="card-text">Bueno = <b><?php echo number_format($estado_piso_almacenamiento_bueno, 0); ?></b><br>
                                Regular = <b><?php echo number_format($estado_piso_almacenamiento_regular, 0); ?></b><br>
                                Malo = <b><?php echo number_format($estado_piso_almacenamiento_malo, 0); ?></b>
                            </h6>
                        </div>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Card: estado techo preparación -->
                <?php if (($estado_techo_bueno + $estado_techo_regular + $estado_techo_malo) > 0): ?>
                <div class="col-12 col-sm-6 col-md-4 col-lg-4 mb-4">
                    <div class="card h-100 shadow-sm">
                        <div class="card-body text-center">
                            <h5 class="card-title">Estado Techo Preparación</h5>
                            <div id="graficoEstadoTechoPreparacionContainer" style="height: 180px; margin-bottom: 20px;"></div>
                            <h6 class="card-text">Bueno = <b><?php echo number_format($estado_techo_bueno, 0); ?></b><br>
                                Regular = <b><?php echo number_format($estado_techo_regular, 0); ?></b><br>
                                Malo = <b><?php echo number_format($estado_techo_malo, 0); ?></b>
                            </h6>
                        </div>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Card: estado paredes preparación -->
                <?php if (($estado_paredes_bueno + $estado_paredes_regular + $estado_paredes_malo) > 0): ?>
                <div class="col-12 col-sm-6 col-md-4 col-lg-4 mb-4">
                    <div class="card h-100 shadow-sm">
                        <div class="card-body text-center">
                            <h5 class="card-title">Estado Paredes Preparación</h5>
                            <div id="graficoEstadoParedesPreparacionContainer" style="height: 180px; margin-bottom: 20px;"></div>
                            <h6 class="card-text">Bueno = <b><?php echo number_format($estado_paredes_bueno, 0); ?></b><br>
                                Regular = <b><?php echo number_format($estado_paredes_regular, 0); ?></b><br>
                                Malo = <b><?php echo number_format($estado_paredes_malo, 0); ?></b>
                            </h6>
                        </div>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Card: estado piso preparación -->
                <?php if (($estado_piso_bueno + $estado_piso_regular + $estado_piso_malo) > 0): ?>
                <div class="col-12 col-sm-6 col-md-4 col-lg-4 mb-4">
                    <div class="card h-100 shadow-sm">
                        <div class="card-body text-center">
                            <h5 class="card-title">Estado Piso Preparación</h5>
                            <div id="graficoEstadoPisoPreparacionContainer" style="height: 180px; margin-bottom: 20px;"></div>
                            <h6 class="card-text">Bueno = <b><?php echo number_format($estado_piso_bueno, 0); ?></b><br>
                                Regular = <b><?php echo number_format($estado_piso_regular, 0); ?></b><br>
                                Malo = <b><?php echo number_format($estado_piso_malo, 0); ?></b>
                            </h6>
                        </div>
                    </div>
                </div>
                <?php endif; ?>

                <?php
                // Si ninguna card de almacenamiento tiene datos, mostrar aviso
                $sinDatosAlmacenamiento = (
                    ($espacio_almacenamiento_si + $espacio_almacenamiento_no) === 0 &&
                    ($almacena_alto_suelo_si + $almacena_alto_suelo_no) === 0 &&
                    ($estado_techo_almacenamiento_bueno + $estado_techo_almacenamiento_regular + $estado_techo_almacenamiento_malo) === 0 &&
                    ($estado_paredes_almacenamiento_bueno + $estado_paredes_almacenamiento_regular + $estado_paredes_almacenamiento_malo) === 0 &&
                    ($estado_piso_almacenamiento_bueno + $estado_piso_almacenamiento_regular + $estado_piso_almacenamiento_malo) === 0 &&
                    ($estado_techo_bueno + $estado_techo_regular + $estado_techo_malo) === 0 &&
                    ($estado_paredes_bueno + $estado_paredes_regular + $estado_paredes_malo) === 0 &&
                    ($estado_piso_bueno + $estado_piso_regular + $estado_piso_malo) === 0
                );
                if ($sinDatosAlmacenamiento): ?>
                <div class="col-12">
                    <div class="alert alert-warning text-center py-3" role="alert">
                        <i class="feather icon-info"></i>
                        <strong>Sin datos de almacenamiento</strong><br>
                        <small>No hay información de estado de infraestructura disponible para el municipio y vigencia seleccionados.</small>
                    </div>
                </div>
                <?php endif; ?>

            </div> <!-- end row -->
        </div>
    </div>
</div>


<!-- POSEE COMEDOR ESCOLAR -->
<div class="col-sm-12">
    <div id="item3_comedores" class="seccion" style="display:none;">
        <div class="card-body">
            <!-- <div class="text-center mb-4">
                <img src="assets/img/elementococina.png" alt="Restaurante" width="120px">
                <h5 class="card-title mt-2">Instituciones con comedor escolar</h5>
            </div> -->

            <!-- Cards internas organizadas horizontalmente -->
            <div class="row justify-content-center g-3">
                  <!-- Card: posee comedor escolar -->
                    <div class="col-12 col-sm-6 col-md-4 col-lg-4">
                        <div class="card h-100 shadow-sm">
                            <div class="card-body">
                                <center>
                                    <h4 class="card-title">Posee Comedor Escolar</h4>
                                    <div id="graficoComedorEscolarContainer" style="height: 180px; margin-bottom: 20px;"></div>

                                    <h6 class="card-text"> Si=
                                        <b><?php echo number_format($comedor_escolar_si, 0); ?></b>
                                        <br> No=
                                        <b><?php echo number_format($comedor_escolar_no, 0); ?></b>
                                    </h6>
                                    <hr>
                            </div>
                        </div>
                    </div>
            </div> <!-- end row -->
        </div>
    </div>
</div>
<!-- POSEE COMEDOR ESCOLAR -->
<div class="col-sm-12">
    <div id="item3_comedores" class="seccion" style="display:none;">
        <div class="card-body">
            <div class="text-center mb-4">
                <img src="assets/img/elementococina.png" alt="Restaurante" width="120px">
                <h5 class="card-title mt-2">Instituciones con comedor escolar</h5>
            </div>

            <!-- Cards internas organizadas horizontalmente -->
            <div class="row justify-content-center g-3">
                  <!-- Card: posee comedor escolar -->
                    <div class="col-12 col-sm-6 col-md-4 col-lg-4">
                        <div class="card h-100 shadow-sm">
                            <div class="card-body">
                                <center>
                                    <h4 class="card-title">Posee Comedor Escolar</h4>
                                    <div id="graficoComedorEscolarContainer" style="height: 180px; margin-bottom: 20px;"></div>

                                    <h6 class="card-text"> Si=
                                        <b><?php echo number_format($comedor_escolar_si, 0); ?></b>
                                        <br> No=
                                        <b><?php echo number_format($comedor_escolar_no, 0); ?></b>
                                    </h6>
                                    <hr>
                                    <br>
                                    <h6 class="card-text">Porcentaje: <br> <br> Si=
                                        <b><?php echo number_format($porcentaje_comedor_escolar_si, 2); ?>%</b>
                                        <br> No =
                                        <div class="p-2 rounded <?= $colorClase16; ?>"
                                            style="display: inline-block; min-width: 100px; text-align: center;">
                                            <div class="p-2 rounded <?= $colorClase16; ?>">
                                                <b><?= number_format($valor16, 2); ?>%</b>
                                            </div>
                                        </div>
                                    </h6>
                            </div>
                        </div>
                    </div>
            </div> <!-- end row -->
        </div>
    </div>
</div>
<!-- Instituciones con Cocinas para Preparación -->
<div class="col-sm-12">
    <div id="item4_cocinas" class="seccion" style="display:none;">
        <div class="card-body">
            <!-- <div class="text-center mb-4">
                <img src="assets/img/elementococina.png" alt="Restaurante" width="120px">
                <h5 class="card-title mt-2">Instituciones con Cocinas para Preparación</h5>
            </div> -->

            <!-- Cards internas organizadas horizontalmente -->
            <div class="row justify-content-center g-3">
                <!-- Card: Espacio de Preparación -->
                <div class="col-12 col-sm-6 col-md-4 col-lg-4">
                    <div class="card h-100 shadow-sm">
                        <div class="card-body">
                            <center>
                                <h4 class="card-title">Espacio de Preparación</h4>
                                <div id="graficoPreparacionContainer" style="height: 180px; margin-bottom: 20px;"></div>

                                <h6 class="card-text">Tienen Espacio de Preparación:<br>
                                    Si:
                                    <b><?php echo number_format($espacio_preparacion_si, 0); ?></b><br>
                                    No:
                                    <b><?php echo number_format($espacio_preparacion_no, 0); ?></b>
                                </h6>
                               
                        </div>
                    </div>
                </div>
            </div> <!-- end row -->
        </div>
    </div>
</div>
   <!-- concepto sanitario -->
    <div class="col-sm-12">
        <div id="item5_concepto_sanitario" class="seccion" style="display:none;" class="card">
            <div class="card-body">
                <!-- <div class="text-center mb-4">
                    <img src="assets/img/luz.png" alt="" width="100px">
                    <h4 class="card-title mt-2">Acceso a servicios publicos y tipo</h4>
                </div> -->
                <!-- Cards internas organizadas horizontalmente -->
                <div class="row justify-content-center g-3">
                    <!-- Card: discposicion de basuras y alcantarillado PAE -->
                    <div class="col-12 col-sm-6 col-md-4 col-lg-4">
                        <div class="card h-100 shadow-sm">
                            <div class="card-body">
                                <center>
                                    <h4 class="card-title">Disposición Basuras y alcantarillado
                                    </h4>
                                    <div id="graficoBasuraAlcantarilladoContainer" style="height: 180px; margin-bottom: 20px;"></div>

                                    <h6 class="card-text">Recolección Basuras: <br>
                                        Si=
                                        <b><?php echo number_format($recoleccion_basuras_si, 0); ?></b>
                                        <br>
                                        No=
                                        <b><?php echo number_format($recoleccion_basuras_no, 0); ?></b>
                                    </h6>
                                    <hr>
                                    <h6 class="card-text">Acceso Alcantarillado:
                                        <br>Si=
                                        <b><?php echo number_format($acceso_alcantarillado_si, 0); ?></b>
                                        <br>No=
                                        <b><?php echo number_format($acceso_alcantarillado_no, 0); ?></b>
                                    </h6>
                            </div>
                        </div>
                    </div>
                    <!-- Card: discposicion de residusos organicos PAE -->
                    <div class="col-12 col-sm-6 col-md-4 col-lg-4">
                        <div class="card h-100 shadow-sm">
                            <div class="card-body">
                                <center>
                                    <h4 class="card-title">Disposición de Desechos Orgánicos
                                    </h4>
                                    <div id="graficoDesechosOrganicosContainer" style="height: 180px; margin-bottom: 20px;"></div>

                                    <h6 class="card-text">
                                        Enterrados =
                                        <b><?php echo number_format($disposicion_derechos_pae_enterrado, 0); ?></b>
                                        <br>
                                        Quemados =
                                        <b><?php echo number_format($disposicion_derechos_pae_quemado, 0); ?></b>
                                        <br>
                                        Rciclados =
                                        <b><?php echo number_format($disposicion_derechos_pae_reciclan, 0); ?></b>

                                        <br>
                                        para lombricultura =
                                        <b><?php echo number_format($disposicion_derechos_pae_lombricultura, 0); ?></b>
                                        <hr>
                                        <br>
                                        Botados en lotes, <br> o otras zonas =
                                        <b><?php echo number_format($disposicion_derechos_pae_tiran_lote, 0); ?></b>
                                    </h6>
                            </div>
                        </div>
                    </div>

                    <!-- Card: discposicion de desechos no organicos PAE -->
                    <div class="col-12 col-sm-6 col-md-4 col-lg-4">
                        <div class="card h-100 shadow-sm">
                            <div class="card-body">
                                <center>
                                    <h4 class="card-title">Disposición de Desechos No Organicos
                                    </h4>
                                    <div id="graficoDesechosNoOrganicosContainer" style="height: 180px; margin-bottom: 20px;"></div>

                                    <h6 class="card-text">
                                        Enterrados =
                                        <b><?php echo number_format($disposicion_no_organicos_pae_enterrado, 0); ?></b>
                                        <br>
                                        Quemados =
                                        <b><?php echo number_format($disposicion_no_organicos_pae_quemado, 0); ?></b>
                                        <br>
                                        Rciclados =
                                        <b><?php echo number_format($disposicion_no_organicos_pae_reciclan, 0); ?></b>

                                        <br>
                                        para lombricultura =
                                        <b><?php echo number_format($disposicion_no_organicos_pae_lombricultura, 0); ?></b>
                                        <br>
                                        <hr>
                                        Botados en lotes, o otras zonas =
                                        <b><?php echo number_format($disposicion_no_organicos_pae_tiran_lote, 0); ?></b>
                                    </h6>
                            </div>
                        </div>
                    </div>
                </div> <!-- end row -->
            </div>
        </div>
    </div>
<!-- dotación y equipos -->
<div class="col-sm-12">
    <div id="item6_dotacion" class="seccion" style="display:none;">
        <div class="card-body">
            <!-- <div class="text-center mb-4">
                <img src="assets/img/utensilios.png" alt="Restaurante" width="130px">
                <h5 class="card-title mt-2">Dotación y equipos</h5>
            </div> -->

            <?php
                // Cálculo del estado para dotación
                $total_si = $posee_cucharones_pae_si + $posee_cuchillos_pae_si + $posee_ollas_pae_si;
                $total_no = $posee_cucharones_pae_no + $posee_cuchillos_pae_no + $posee_ollas_pae_no;

                if ($total_si > $total_no) {
                    $estado = 'Aceptable';
                    $claseEstado = 'bg-success text-white';
                } elseif ($total_no > $total_si) {
                    $estado = 'Inaceptable';
                    $claseEstado = 'bg-danger text-white';
                } else {
                    $estado = 'Insuficiente';
                    $claseEstado = 'bg-warning text-dark';
                }
            ?>

            <!-- Row que contiene ambas tarjetas una al lado de otra -->
            <div class="row justify-content-center g-3">

                <!-- Tarjeta: Dotación PAE -->
                <div class="col-12 col-md-6">
                    <div class="card h-100 shadow-sm">
                        <div class="card-body">
                            <center>
                                <h4 class="card-title">Estado General Dotación PAE</h4>

                                <!-- Gráfico donut -->
                                <div id="graficoDotacionContainer" data-grafico-estado="<?= $estado ?>" style="margin-top: 20px;"></div>

                                <div class="mt-4">
                                    <h6><b>Cucharones:</b> Sí: <b><?= number_format($posee_cucharones_pae_si, 0, ',', '.'); ?></b> &nbsp;
                                        No: <b><?= number_format($posee_cucharones_pae_no, 0, ',', '.'); ?></b></h6>
                                    <h6><b>Cuchillos:</b> Sí: <b><?= number_format($posee_cuchillos_pae_si, 0, ',', '.'); ?></b> &nbsp;
                                        No: <b><?= number_format($posee_cuchillos_pae_no, 0, ',', '.'); ?></b></h6>
                                    <h6><b>Ollas:</b> Sí: <b><?= number_format($posee_ollas_pae_si, 0, ',', '.'); ?></b> &nbsp;
                                        No: <b><?= number_format($posee_ollas_pae_no, 0, ',', '.'); ?></b></h6>
                                    <hr>
                                    <h6><b>Menaje:</b></h6>
                                    <h6>Platos: <b><?= number_format($cantidad_platos, 0, ',', '.'); ?></b></h6>
                                    <h6>Cucharas: <b><?= number_format($cantidad_cucharas, 0, ',', '.'); ?></b></h6>
                                    <h6>Tenedores: <b><?= number_format($cantidad_tenedores, 0, ',', '.'); ?></b></h6>
                                    <h6>Pocillos: <b><?= number_format($cantidad_pocillos, 0, ',', '.'); ?></b></h6>
                                </div>

                                <hr>
                                <h5 class="mt-3">
                                    Resultado:
                                    <span class="p-2 rounded <?= $claseEstado; ?>">
                                        <b><?= $estado; ?></b>
                                    </span>
                                </h5>
                            </center>
                        </div>
                    </div>
                </div>

                <!-- Tarjeta: Estado General Equipos PAE -->
                <div class="col-12 col-md-6">
                    <div class="card h-100 shadow-sm">
                        <div class="card-body">
                            <center>
                                <h4 class="card-title">Estado General Equipos PAE</h4>

                                <!-- Gráfico donut -->
                                <div id="graficoEquiposContainer" data-grafico-estado="<?= $estado_general ?>" style="margin-top: 20px;"></div>

                                <div class="mt-4">
                                    <h6><b>Neveras:</b> <?= $estado_neveras; ?></h6>
                                    <h6><b>Congeladores:</b> <?= $estado_congeladores; ?></h6>
                                    <h6><b>Estufas y Quemadores:</b> <?= $estado_estufas; ?></h6>
                                    <h6><b>Licuadoras:</b> <?= $estado_licuadoras; ?></h6>
                                </div>

                                <hr>
                                <h5 class="mt-3">
                                    Resultado general:
                                    <span class="p-2 rounded <?= $clase_general; ?>">
                                        <b><?= $estado_general; ?></b>
                                    </span>
                                </h5>
                            </center>
                        </div>
                    </div>
                </div>

            </div> <!-- end row -->
        </div>
    </div>
</div>
    <!-- Servicios Publicos-->
    <div class="col-sm-12">
        <div id="item7_servicios_publicos" class="seccion" style="display:none;" class="card">
            <div class="card-body">
                <!-- <div class="text-center mb-4">
                    <img src="assets/img/luz.png" alt="" width="100px">
                    <h4 class="card-title mt-2">Acceso a servicios publicos y tipo</h4>
                </div> -->
                <!-- Cards internas organizadas horizontalmente -->
                <div class="row justify-content-center g-3">

                    <!-- Card: accceso a agua PAE -->
                    <div class="col-12 col-sm-6 col-md-4 col-lg-4">
                        <div class="card h-100 shadow-sm">
                            <div class="card-body">
                                <center>
                                    <h4 class="card-title">Acceso al sistema de Agua</h4>
                                    <div id="graficoAccesoAguaContainer" style="height: 180px; margin-bottom: 20px;"></div>

                                    <h6 class="card-text"> si=
                                        <b><?php echo number_format($acceso_agua_si, 0); ?></b>
                                        <br>
                                        No=
                                        <b><?php echo number_format($acceso_agua_no, 0); ?></b>
                                        <br>
                                        Intermitente=
                                        <b><?php echo number_format($acceso_agua_intermitente, 0); ?></b>
                                    </h6>
                                    <hr>
                                    <h6 class="card-text">Porcentaje: <br> Si=
                                        <b><?php echo number_format($porcentaje_acceso_agua_si, 2); ?>%</b>
                                        <br> No =
                                        <b><?php echo number_format($porcentaje_acceso_agua_no, 2); ?>%</b>
                                        <br> Intermitente =
                                        <b>
                                            <div class="p-2 rounded <?= $colorClase11; ?>"
                                                style="display: inline-block; min-width: 100px; text-align: center;">
                                                <div class="p-2 rounded <?= $colorClase11; ?>">
                                                    <b><?= number_format($valor11, 2); ?>%</b>
                                                </div>
                                        </b>
                                    </h6>
                            </div>
                        </div>
                    </div>

                    <!-- Card: tipo  accceso a agua PAE -->
                    <div class="col-12 col-sm-6 col-md-4 col-lg-4">
                        <div class="card h-100 shadow-sm">
                            <div class="card-body">
                                <center>
                                    <h4 class="card-title">Obtención del agua para el PAE</h4>
                                    <div id="graficoObtencionAguaContainer" style="height: 180px; margin-bottom: 20px;"></div>

                                    <h6 class="card-text"> Acueducto=
                                        <b><?php echo number_format($acueducto, 0); ?></b>
                                        <br>
                                        Rios, Quebradas =
                                        <b><?php echo number_format($rios_quebradas, 0); ?></b>
                                        <br>
                                        Agua embotellada=
                                        <b><?php echo number_format($embotellada, 0); ?></b>
                                        <br>
                                        Agua LLuvia=
                                        <b><?php echo number_format($lluvia, 0); ?></b>
                                        <br>
                                        <hr>
                                        Carrotanque=
                                        <b><?php echo number_format($carrotanque, 0); ?></b>
                                        <br>
                                        Pozos=
                                        <b><?php echo number_format($pozo_agua, 0); ?></b>
                                        <br>
                                        Otros Metodos=
                                        <b><?php echo number_format($otros_agua, 0); ?></b>
                                    </h6>
                            </div>
                        </div>
                    </div>
                    <!-- Card: tipo  accceso a electricidad PAE -->
                    <div class="col-12 col-sm-6 col-md-4 col-lg-4">
                        <div class="card h-100 shadow-sm">
                            <div class="card-body">
                                <center>
                                    <h4 class="card-title">Acceso a Electricidad</h4>

                                    <!-- Contenedor del gráfico -->
                                    <div id="graficoAccesoElectricidadContainer" style="height: 180px; margin-bottom: 10px;"></div>

                                    <h6 class="card-text"> Si=
                                        <b><?php echo number_format($acceso_electricidad_si, 0); ?></b>
                                        <br> No=
                                        <b><?php echo number_format($acceso_electricidad_no, 0); ?></b>
                                        <br> Intermitente=
                                        <b><?php echo number_format($acceso_electricidad_intermitente, 0); ?></b>
                                    </h6>
                                    <hr>
                                    <h6 class="card-text">Porcentaje: <br> Si=
                                        <b><?php echo number_format($porcentaje_acceso_electricidad_si, 2); ?>%</b>
                                        <br> No =
                                        <b><?php echo number_format($porcentaje_acceso_electricidad_no, 2); ?>%</b>
                                        <br> Intermitente =
                                        <div class="p-2 rounded <?= $colorClase12; ?>"
                                            style="display: inline-block; min-width: 100px; text-align: center;">
                                            <div class="p-2 rounded <?= $colorClase12; ?>">
                                                <b><?= number_format($valor12, 2); ?>%</b>
                                            </div>
                                        </div>
                                    </h6>
                                </center>
                            </div>
                        </div>
                    </div>
                </div> <!-- end row -->
            </div>
        </div>
    </div>    
<!-- Modalidades Pae -->
<div class="col-sm-12">
        <div id="item8_modalidades" class="seccion" style="display:none;" class="card">
            <div class="card-body">
                <!-- <div class="text-center mb-4">
                    <img src="assets/img/tosta.png" alt="Restaurante" width="150px">
                    <h5 class="card-title mt-2">Elementos Cocinas</h5>
                </div> -->

                <!-- Cards internas organizadas horizontalmente -->
                <div class="row justify-content-center g-3">
                    <div class="col-12 col-sm-6 col-md-4 col-lg-4">
                    <div class="card h-100 shadow-sm">
                        <div class="card-body">
                            <center>
                                <h4 class="card-title">Almuerzo Preparado en sitio</h4>
                                <div id="graficoAlmuerzoPreparadoContainer" style="height: 180px; margin-bottom: 20px;"></div>

                                <h6 class="card-text">Almuerzo <br> Si=
                                    <b><?php echo number_format($almuerzo_preparado_sitio_si, 0); ?></b>
                                    <br>
                                    No=<b><?php echo number_format($almuerzo_preparado_sitio_no, 0); ?></b>
                                </h6>
                                
                        </div>
                    </div>
                </div>

                <!-- Card: transporte almuerzo -->
                <div class="col-12 col-sm-6 col-md-4 col-lg-4">
                    <div class="card h-100 shadow-sm">
                        <div class="card-body">
                            <center>
                                <h4 class="card-title">Almuerzo transportado (Comida
                                    Caliente)
                                </h4>
                                <div id="graficoAlmuerzoTransportadoContainer" style="height: 180px; margin-bottom: 20px;"></div>

                                <h6 class="card-text">Almuerzo : <br>
                                    Si=
                                    <b><?php echo number_format($almuerzo_trasportado_si, 0); ?></b>
                                    <br>
                                    No=
                                    <b><?php echo number_format($almuerzo_trasportado_no, 0); ?></b>
                                </h6>
                                
                        </div>
                    </div>
                </div>

                <!-- Card: preparacion complento -->
                <div class="col-12 col-sm-6 col-md-4 col-lg-4">
                    <div class="card h-100 shadow-sm">
                        <div class="card-body">
                            <center>
                                <h4 class="card-title">Preparación Complemento</h4>
                                <div id="graficoComplementoPreparadoContainer" style="height: 180px; margin-bottom: 20px;"></div>

                                <h6 class="card-text">Preparado en sitio:
                                    <br> Si=
                                    <b><?php echo number_format($complemento_preparado_sitio_si, 0); ?></b>
                                    <br>
                                    No=<b><?php echo number_format($complemento_preparado_sitio_no, 0); ?></b>
                                </h6>
                               
                        </div>
                    </div>
                </div>
                <!-- Card: preparacion complento -->
                <div class="col-12 col-sm-6 col-md-4 col-lg-4">
                    <div class="card h-100 shadow-sm">
                        <div class="card-body">
                            <center>
                                <h4 class="card-title">Complemento industrializado</h4>
                                <div id="graficoComplementoIndustrializadoContainer" style="height: 180px; margin-bottom: 20px;"></div>

                                <h6 class="card-text">Complemento (Industrializado):
                                    <br>
                                    Si=
                                    <b><?php echo number_format($complemento_industrializado_si, 0); ?></b>
                                    <br>
                                    No=
                                    <b><?php echo number_format($complemento_industrializado_no, 0); ?></b>
                                </h6>
                                
                        </div>
                    </div>
                </div>
                    </div> <!-- end row -->
                </div>
            </div>
        </div>
   
</div> <!-- fin todos gráficos -->
<!-- Librería ApexCharts -->
<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>



<script>
    const datosEstadoTecho = {
        bueno: <?= (int)$estado_techo_almacenamiento_bueno ?>,
        regular: <?= (int)$estado_techo_almacenamiento_regular ?>,
        malo: <?= (int)$estado_techo_almacenamiento_malo ?>
    };
        const graficosEstadoSimple = {
        graficoEstadoTechoSedeEducativaContainer: {
            bueno: <?= $estado_techo_almacenamiento_bueno ?>,
            regular: <?= $estado_techo_almacenamiento_regular ?>,
            malo: <?= $estado_techo_almacenamiento_malo ?>
        },
        graficoAlmacenamientoContainer: {
            bueno: <?= $espacio_almacenamiento_si ?>,
            malo: <?= $espacio_almacenamiento_no ?>,
            regular: 0
        },
        graficoPreparacionContainer: {
            bueno: <?= $espacio_preparacion_si ?>,
            malo: <?= $espacio_preparacion_no ?>,
            regular: 0
        },
        graficoAlmacenamientoTarimasContainer: {
            bueno: <?= $almacena_alto_suelo_si ?>,
            malo: <?= $almacena_alto_suelo_no ?>,
            regular: 0
        },
        graficoEstadoTechoAlmacenamientoContainer: {
            bueno: <?= $estado_techo_almacenamiento_bueno ?>,
            regular: <?= $estado_techo_almacenamiento_regular ?>,
            malo: <?= $estado_techo_almacenamiento_malo ?>
        },
        graficoEstadoParedesAlmacenamientoContainer: {
            bueno: <?= $estado_paredes_almacenamiento_bueno ?>,
            regular: <?= $estado_paredes_almacenamiento_regular ?>,
            malo: <?= $estado_paredes_almacenamiento_malo ?>
        },
        graficoEstadoPisoAlmacenamientoContainer: {
            bueno: <?= $estado_piso_almacenamiento_bueno ?>,
            regular: <?= $estado_piso_almacenamiento_regular ?>,
            malo: <?= $estado_piso_almacenamiento_malo ?>
        },
        graficoEstadoTechoPreparacionContainer: {
            bueno: <?= $estado_techo_bueno ?>,
            regular: <?= $estado_techo_regular ?>,
            malo: <?= $estado_techo_malo ?>
        },
        graficoEstadoParedesPreparacionContainer: {
            bueno: <?= $estado_paredes_bueno ?>,
            regular: <?= $estado_paredes_regular ?>,
            malo: <?= $estado_paredes_malo ?>
        },
        graficoEstadoPisoPreparacionContainer: {
            bueno: <?= $estado_piso_bueno ?>,
            regular: <?= $estado_piso_regular ?>,
            malo: <?= $estado_piso_malo ?>
        }
    };
        graficosEstadoSimple["graficoComedorEscolarContainer"] = {
        bueno: <?= $comedor_escolar_si ?>,
        malo: <?= $comedor_escolar_no ?>,
        regular: 0
    };
        graficosEstadoSimple["graficoPreparacionContainer"] = {
        bueno: <?= $espacio_preparacion_si ?>,
        malo: <?= $espacio_preparacion_no ?>,
        regular: 0
    };
     graficosEstadoSimple["graficoBasuraAlcantarilladoContainer"] = {
        bueno: <?= $recoleccion_basuras_si + $acceso_alcantarillado_si ?>,
        malo: <?= $recoleccion_basuras_no + $acceso_alcantarillado_no ?>,
        regular: 0
    };

    graficosEstadoSimple["graficoDesechosOrganicosContainer"] = {
        bueno: <?= $disposicion_derechos_pae_reciclan + $disposicion_derechos_pae_lombricultura ?>,
        regular: <?= $disposicion_derechos_pae_enterrado + $disposicion_derechos_pae_quemado ?>,
        malo: <?= $disposicion_derechos_pae_tiran_lote ?>
    };

    graficosEstadoSimple["graficoDesechosNoOrganicosContainer"] = {
        bueno: <?= $disposicion_no_organicos_pae_reciclan + $disposicion_no_organicos_pae_lombricultura ?>,
        regular: <?= $disposicion_no_organicos_pae_enterrado + $disposicion_no_organicos_pae_quemado ?>,
        malo: <?= $disposicion_no_organicos_pae_tiran_lote ?>
    };
        graficosEstadoSimple["graficoDotacionContainer"] = {
        bueno: <?= $posee_cucharones_pae_si + $posee_cuchillos_pae_si + $posee_ollas_pae_si ?>,
        malo: <?= $posee_cucharones_pae_no + $posee_cuchillos_pae_no + $posee_ollas_pae_no ?>,
        regular: 0
    };
    graficosEstadoSimple["graficoEquiposContainer"] = {
        estadoTexto: "<?= $estado_general ?>",
      
        bueno: "<?= strtolower($estado_general) ?>" === "aceptable" ? 2 : 0,
        regular: "<?= strtolower($estado_general) ?>" === "insuficiente" ? 2 : 0,
        malo: "<?= strtolower($estado_general) ?>" === "inaceptable" ? 2 : 0
    };
     graficosEstadoSimple["graficoAccesoAguaContainer"] = {
        bueno: <?= $acceso_agua_si ?>,
        regular: <?= $acceso_agua_intermitente ?>,
        malo: <?= $acceso_agua_no ?>
    };

    graficosEstadoSimple["graficoObtencionAguaContainer"] = {
        
        bueno: <?= $acueducto ?>,
        regular: <?= $embotellada + $lluvia + $pozo_agua + $otros_agua ?>,
        malo: <?= $rios_quebradas + $carrotanque ?>
    };

    graficosEstadoSimple["graficoAccesoElectricidadContainer"] = {
        bueno: <?= $acceso_electricidad_si ?>,
        regular: <?= $acceso_electricidad_intermitente ?>,
        malo: <?= $acceso_electricidad_no ?>
    };

    graficosEstadoSimple["graficoTipoCoccionContainer"] = {
      
        bueno: <?= $gas_natural + $electricidad ?>,
        regular: <?= $lena + $petroleo_gasolina ?>,
        malo: <?= $desecho ?>,
       
    };
      graficosEstadoSimple["graficoAlmuerzoPreparadoContainer"] = {
        bueno: <?= $almuerzo_preparado_sitio_si ?>,
        malo: <?= $almuerzo_preparado_sitio_no ?>,
        regular: 0
    };

    graficosEstadoSimple["graficoAlmuerzoTransportadoContainer"] = {
        bueno: <?= $almuerzo_trasportado_si ?>,
        malo: <?= $almuerzo_trasportado_no ?>,
        regular: 0
    };

    graficosEstadoSimple["graficoComplementoPreparadoContainer"] = {
        bueno: <?= $complemento_preparado_sitio_si ?>,
        malo: <?= $complemento_preparado_sitio_no ?>,
        regular: 0
    };

    graficosEstadoSimple["graficoComplementoIndustrializadoContainer"] = {
        bueno: <?= $complemento_industrializado_si ?>,
        malo: <?= $complemento_industrializado_no ?>,
        regular: 0
    };
</script>
<!-- Archivo JS personalizado solo para graficos -->
<script src="admin/js/graficosdashboardpae.js"></script>
<script>
function mostrarSeccion(id) {
    // Oculta todas las secciones
    const secciones = document.querySelectorAll('.seccion');
    secciones.forEach(seccion => {
        seccion.style.display = 'none';
    });

    // Muestra solo la sección seleccionada
    document.getElementById(id).style.display = 'block';

    // Actualiza las pestañas activas
    const tabs = document.querySelectorAll('.nav-link');
    tabs.forEach(tab => {
        tab.classList.remove('active');
    });
    document.getElementById('btn-' + id).classList.add('active');
}

PAE_DASHBOARD.generarGraficaBarraProvinceasEnSedeEducativasConProblemas();
</script>