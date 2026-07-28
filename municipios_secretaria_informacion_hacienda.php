<?php
include './admin/include/head.php';
require './admin/include/generic_classes.php';
include './admin/classes/Departamento.php';
include './admin/classes/Hacienda.php';

$accion = '';
if (isset($_REQUEST['mun'], $_REQUEST['dep']) && !empty(trim($_REQUEST['mun'])) && !empty(trim($_REQUEST['dep']))) {
    $municipio = trim($_REQUEST['mun']);
    $departamento = trim($_REQUEST['dep']);
    $accion = trim($_REQUEST['accion']);

    $informacionPorSecretaria = Hacienda::getAllHaciendaByMunicipio(array('codigoMunicipio' => $municipio, 'accion' => $accion));
} else { ?>
<script type='text/javascript'>
    alert('Información enviada no es correcta');
    window.location =
        'secretaria.php?depto_id=<?php echo Util::getDepartamentoPrincipal(); ?>&secretaria=<?php echo Util::getSecretariaIdHacienda(); ?>';
</script>
<?php
}

// Información de Departamentos
$arrDep = Departamento::getAll(null);
$isvalid = $arrDep['output']['valid'];
$arrDep = $arrDep['output']['response'];
$optionDep = Util::getDepartamentoPrincipal();
foreach ($arrDep as $val) {
    $optionDep .= "<option " . ($val["codigo_departamento"] == Util::getDepartamentoPrincipal() ? "selected" : "") . " value='" . $val['codigo_departamento'] . "'>" . $val['codigo_departamento'] . " - " . $val['departamento'] . "</option>";
}

// print_r(json_encode( $informacionPorSecretaria));
// exit();

?>  

<body class="">
    <!-- [ Pre-loader ] start -->
    <div class="loader-bg">
        <div class="loader-track">
            <div class="loader-fill"></div>
        </div>
    </div>
    <style>
        .table th, .table td { vertical-align: middle; }
        .table td:last-child { white-space: normal !important; word-wrap: break-word; max-width: 200px; }
    </style>
    <?php
    if (isset($_SERVER['HTTP_USER_AGENT']) && preg_match('/(Android|iPhone|iPad|iPod|Windows Phone)/i', $_SERVER['HTTP_USER_AGENT'])) {
        include './admin/include/menu_movil.php';
    } else {
        echo '<style>.menu-movil-container { display: none !important; }</style>';
    }
    ?>
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

    <!-- [ Main Content ] start -->
    <div class="pcoded-main-container">
        <div class="pcoded-content">
            <!-- [ breadcrumb ] start -->
            <div class="page-header">
                <div class="page-block">
                    <div class="row align-items-center">
                        <div class="col-md-12">
                            <div class="d-flex justify-content-between align-items-center">
                                <h5 class="m-b-10">Municipio secretaría Hacienda</h5>
                                <?php include './admin/include/btn_back.php'; ?>
                            </div>
                            <ul class="breadcrumb">
                                <li class="breadcrumb-item"><a href="index.html"><i class="feather icon-home"></i></a>
                                </li>
                                <li class="breadcrumb-item"><a href="#!">Resumen secretaría Hacienda</a>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
            <!-- [ breadcrumb ] end -->
            <!-- [ Main Content ] start -->
            <div class="row">
                <!-- [ sample-page ] start -->
                <div class="col-sm-12">
                    <div class="card">

                        <div class="card-header">

                            <div class="col-sm-12">
                                <div class="card-body">

                                    <input type="hidden" name="op" id="op" />
                                    <input type="hidden" name="id" id="id" />
                                    <input type="hidden" name="filtro" id="filtro" value="no" />
                                    <input type="hidden" name="filtroVeredaById" id="filtroVeredaById" value="no" />
                                    <div class="row">

                                        <div class="col-sm-4">
                                            <div class="form-group">
                                                <h5>Departamento</h5>
                                                <label for="tbl_departamento_id">Departamento</label>
                                                <select onchange=" DEPARTAMENTO.getMunicipios()" class="form-control"
                                                    id="tbl_departamento_id" name="tbl_departamento_id">
                                                    <?php echo $optionDep; ?>
                                                </select>
                                            </div>
                                        </div>

                                        <div class="col-sm-4">
                                            <div class="form-group">
                                                <h5>Municipio</h5>
                                                <select onchange="MUNICIPIO.updateUrlMunicipio(this)"
                                                    class="form-control" id="tbl_municipio_id"
                                                    name="tbl_municipio_id"></select>
                                            </div>
                                        </div>
                                        <!-- <div class="col-sm-4">
                                            <div class="form-group">
                                                <h5>Tipo de Acción</h5>
                                                 <select
                                                        class="form-control" id="accionHacienda" name="accionHacienda">
                                                        <option value="Capacitacion Fiscal y Financiera">Capacitación Fiscal y Financiera</option>
                                                        <option value="Operativos Contrabando licores">Operativos Contrabando licores</option>
                                                        <option value="Operativos Contrabando cigarrillos">Operativos Contrabando cigarrillos</option>
                                                        <option value="Operativos Contrabando cerveza">Operativos Contrabando cerveza</option>
                                                        <option value="Impuesto Vehicular Recaudado">Impuesto Vehicular Recaudado</option>
                                                        <option value="Recaudo del impuesto al consumo">Recaudo del impuesto al consumo</option>
                                                        <option value="Recaudo del impuesto de registro">Recaudo del impuesto de registro</option>
                                                        <option value="Impuesto Estampillas Recaudado">Impuesto Estampillas Recaudado</option>
                                                    </select>
                                            </div>
                                        </div> -->
                                    </div>
                                </div>
                            </div>

                            <div class="card-header-right">
                                <div class="btn-group card-option">
                                    <button type="button" class="btn dropdown-toggle btn-icon" data-toggle="dropdown"
                                        aria-haspopup="true" aria-expanded="false">
                                        <i class="feather icon-more-horizontal"></i>
                                    </button>
                                    <ul class="list-unstyled card-option dropdown-menu dropdown-menu-right">
                                        <li class="dropdown-item full-card"><a href="#!"><span><i
                                                        class="feather icon-maximize"></i> Maximizar</span><span
                                                    style="display:none"><i class="feather icon-minimize"></i>
                                                    Restaurar</span></a></li>
                                        <li class="dropdown-item minimize-card"><a href="#!"><span><i
                                                        class="feather icon-minus"></i> Colapsar</span><span
                                                    style="display:none"><i class="feather icon-plus"></i>
                                                    Expandir</span></a></li>
                                        <li class="dropdown-item reload-card"><a href="#!"><i
                                                    class="feather icon-refresh-cw"></i> Recargar</a></li>
                                        <li class="dropdown-item close-card"><a href="#!"><i
                                                    class="feather icon-trash"></i> Remover</a></li>
                                    </ul>
                                </div>
                            </div>
                        </div>

                        <div class="col-sm-12" id="divConsolidado">
                            <?php
                            $responseAccionSecretarias = $informacionPorSecretaria['output']['response'];
                            $isAccionSecretaria = $informacionPorSecretaria['output']['valid'];
                            ?>
                            <div>
                                <h5 class="mb-3">Información Hacienda</h5>
                                <?php
                                /**
                                 * Función auxiliar para renderizar celdas de datos condicionalmente
                                 */
                                function renderAccionData($accion, $item) {
                                    // Definir tipos GOA que comparten la misma estructura
                                    $tiposGOA = [
                                        'GOA Aprehensiones de Licores',
                                        'GOA Aprehensión de Cigarrillos',
                                        'GOA Aprehensión de Cervezas',
                                        'GOA Aprehensión de Tabaco y Otros',
                                        'GOA - Aprehensiones',
                                    ];

                                    // Manejar tipos GOA (todos tienen la misma estructura)
                                    if (in_array($accion, $tiposGOA)) {
                                        echo '<td>' . number_format($item['cantidad_aprehendida'] ?? 0, 0, ',', '.') . '</td>';
                                        echo '<td>$' . number_format($item['avaluo_comercial'] ?? 0, 0, ',', '.') . '</td>';
                                        return;
                                    }
                                    
                                    // Manejar otros casos específicos
                                    switch ($accion) {
                                        case 'Capacitacion Fiscal y Financiera':
                                            echo '<td>' . htmlspecialchars($item['cantidad_personas'] ?? '0') . '</td>';
                                            break;
                                            
                                        case 'Operativos Contrabando licores':
                                            echo '<td>' . htmlspecialchars($item['incautacion_licores'] ?? '0') . '</td>';
                                            echo '<td>$' . number_format($item['valor_licores'] ?? 0, 0, ',', '.') . '</td>';
                                            break;
                                            
                                        case 'Operativos Contrabando cigarrillos':
                                            echo '<td>' . htmlspecialchars($item['incautacion_cigarrillos'] ?? '0') . '</td>';
                                            echo '<td>$' . number_format($item['valor_cigarrillos'] ?? 0, 0, ',', '.') . '</td>';
                                            echo '<td>' . htmlspecialchars($item['incautacion_tabaco'] ?? '0') . '</td>';
                                            echo '<td>$' . number_format($item['valor_tabaco'] ?? 0, 0, ',', '.') . '</td>';
                                            break;
                                            
                                        case 'Operativos Contrabando cerveza':
                                            echo '<td>' . htmlspecialchars($item['incautacion_cerveza'] ?? '0') . '</td>';
                                            echo '<td>$' . number_format($item['valor_cerveza'] ?? 0, 0, ',', '.') . '</td>';
                                            break;
                                            
                                        case 'Recaudo del impuesto al consumo':
                                            echo '<td>$' . number_format($item['valor_importado'] ?? 0, 0, ',', '.') . '</td>';
                                            echo '<td>$' . number_format($item['valor_nacional'] ?? 0, 0, ',', '.') . '</td>';
                                            break;
                                        
                                        case 'Registro de Visitas a Establecimientos Comerciales':
                                            echo '<td>' . number_format($item['cantidad_visitas_al_municipio'] ?? 0, 0, ',', '.') . '</td>';
                                            break;

                                        default:
                                            echo '<td class="text-muted">-</td>';
                                            break;
                                    }
                                }
                                ?>
                                <div class="table-responsive">
                                   <table id="dynamictable" class="table table-bordered table-hover" style="font-size: 0.85em;">
                                   <thead class="thead-dark">
        <?php
        // Definir tipos GOA al inicio
        $tiposGOA = [
            'GOA Aprehensiones de Licores',
            'GOA Aprehensión de Cigarrillos',
            'GOA Aprehensión de Cervezas',
            'GOA Aprehensión de Tabaco y Otros',
            'GOA - Aprehensiones',
        ];
        $esAccionGOA = in_array($accion, $tiposGOA);
        ?>

        <?php
        $accionesConEstablecimiento = [
            'GOA Aprehensiones de Licores', 'GOA Aprehensión de Cigarrillos',
            'GOA Aprehensión de Cervezas', 'GOA Aprehensión de Tabaco y Otros',
            'Registro de Visitas a Establecimientos Comerciales',
        ];
        $esAccionEstablecimiento = in_array($accion, $accionesConEstablecimiento);
        ?>
        <tr>
            <th>Id</th>
            <th>Fecha</th>
            <th>Acción</th>
            <th>Observaciones</th>

            <?php if ($accion === 'Capacitacion Fiscal y Financiera'): ?>
                <th>Cant. Personas Capacitadas</th>

            <?php elseif ($accion === 'Recaudo del impuesto al consumo'): ?>
                <th>Valor Importado</th>
                <th>Valor Nacional</th>

            <?php elseif ($accion === 'Recaudo del impuesto de registro'): ?>
                <th>Valor Trámite</th>
                <th>Valor Recaudo</th>

            <?php elseif ($accion === 'Impuesto Vehicular Recaudado'): ?>
                <th>Valor Trámite Vehicular</th>
                <th>Valor Recaudo Vehicular</th>
                <th>Cant. Operativos</th>
                <th>Cant. Emplazados</th>
                <th>Placas Consultadas</th>
                <th>Campañas Sensibilización</th>

            <?php elseif ($accion === 'Impuesto Estampillas Recaudado'): ?>
                <th>Estampilla</th>
                <th>Valor Estampilla</th>

            <?php elseif ($esAccionEstablecimiento): ?>
                <th>Nombre Establecimiento</th>
                <th>Tipo Establecimiento</th>
                <th>Establecimiento</th>
                <th>Dirección</th>
                <th>Barrio</th>
                <th>Administrador</th>
                <?php if ($esAccionGOA): ?>
                <th>Cant. Aprehendida</th>
                <th>Avalúo Comercial</th>
                <?php else: ?>
                <th>Visitas al Municipio</th>
                <?php endif; ?>

            <?php elseif ($accion === 'GOA Juridico'): ?>
                <th>Custodia Valor Total</th>
                <th>Custodia Cant. Procesos</th>
                <th>Custodia Cant. Unidades</th>
                <th>Destrucción Cant. Unidades</th>
                <th>Destrucción Valor Total</th>

            <?php elseif ($accion === 'Registro de Capacitaciones del GOA'): ?>
                <th>Tipo Capacitación GOA</th>
                <th>Núm. Asistentes</th>
            <?php endif; ?>

            <th>Foto</th>
            <?php if (in_array($_SESSION['session_user']['tipo'] ?? '', ['SuperAdministrador', 'Administrador'])): ?>
            <th>Acciones</th>
            <?php endif; ?>
        </tr>
    </thead>
    <tbody>
        <?php if (isset($isAccionSecretaria) && $isAccionSecretaria && !empty($responseAccionSecretarias)): ?>
        <?php foreach ($responseAccionSecretarias as $item): ?>
        <tr>
            <td><?= htmlspecialchars($item['id'] ?? '') ?></td>
            <td><?= htmlspecialchars($item['dtcreate_at'] ?? '') ?></td>
            <td><?= htmlspecialchars($item['accion'] ?? '') ?></td>
            <td><?= htmlspecialchars($item['observaciones'] ?? '') ?></td>

            <?php if ($accion === 'Capacitacion Fiscal y Financiera'): ?>
                <td><?= htmlspecialchars($item['cantidad_personas'] ?? '0') ?></td>

            <?php elseif ($accion === 'Recaudo del impuesto al consumo'): ?>
                <td>$<?= number_format($item['valor_importado'] ?? 0, 0, ',', '.') ?></td>
                <td>$<?= number_format($item['valor_nacional'] ?? 0, 0, ',', '.') ?></td>

            <?php elseif ($accion === 'Recaudo del impuesto de registro'): ?>
                <td>$<?= number_format($item['valor_tramite'] ?? 0, 0, ',', '.') ?></td>
                <td>$<?= number_format($item['valor_recaudo'] ?? 0, 0, ',', '.') ?></td>

            <?php elseif ($accion === 'Impuesto Vehicular Recaudado'): ?>
                <td>$<?= number_format($item['valor_tramite_impuesto_vehicular'] ?? 0, 0, ',', '.') ?></td>
                <td>$<?= number_format($item['valor_recaudo_impuesto_vehicular'] ?? 0, 0, ',', '.') ?></td>
                <td><?= htmlspecialchars($item['vehicular_cantidad_operativos'] ?? '0') ?></td>
                <td><?= htmlspecialchars($item['vehicular_cantidad_emplazados'] ?? '0') ?></td>
                <td><?= htmlspecialchars($item['vehicular_cantidad_placas_consultadas'] ?? '0') ?></td>
                <td><?= htmlspecialchars($item['vehicular_cantidad_campanas_sensibilizacion'] ?? '0') ?></td>

            <?php elseif ($accion === 'Impuesto Estampillas Recaudado'): ?>
                <td><?= htmlspecialchars($item['estampilla'] ?? '') ?></td>
                <td>$<?= number_format($item['valor_estampilla'] ?? 0, 0, ',', '.') ?></td>

            <?php elseif ($esAccionEstablecimiento): ?>
                <td><?= htmlspecialchars($item['nombre_establecimiento'] ?? '') ?></td>
                <td><?= htmlspecialchars($item['tipoe'] ?? '') ?></td>
                <td><?= htmlspecialchars($item['establecimiento'] ?? '') ?></td>
                <td><?= htmlspecialchars($item['direccion'] ?? '') ?></td>
                <td><?= htmlspecialchars($item['barrio'] ?? '') ?></td>
                <td><?= htmlspecialchars($item['administrador'] ?? '') ?></td>
                <?php if ($esAccionGOA): ?>
                <td><?= number_format($item['cantidad_aprehendida'] ?? 0, 0, ',', '.') ?></td>
                <td>$<?= number_format($item['avaluo_comercial'] ?? 0, 0, ',', '.') ?></td>
                <?php else: ?>
                <td><?= number_format($item['cantidad_visitas_al_municipio'] ?? 0, 0, ',', '.') ?></td>
                <?php endif; ?>

            <?php elseif ($accion === 'GOA Juridico'): ?>
                <td>$<?= number_format($item['goa_juridico_custodia_valor_total'] ?? 0, 0, ',', '.') ?></td>
                <td><?= htmlspecialchars($item['goa_juridico_custodia_cantidad_procesos'] ?? '0') ?></td>
                <td><?= htmlspecialchars($item['goa_juridico_custodia_cantidad_unidades'] ?? '0') ?></td>
                <td><?= htmlspecialchars($item['goa_juridico_destruccion_cantidad_unidades'] ?? '0') ?></td>
                <td>$<?= number_format($item['goa_juridico_destruccion_valor_total'] ?? 0, 0, ',', '.') ?></td>

            <?php elseif ($accion === 'Registro de Capacitaciones del GOA'): ?>
                <td><?= htmlspecialchars($item['tipo_capacitacion_goa'] ?? '') ?></td>
                <td><?= htmlspecialchars($item['numero_asistentes'] ?? '0') ?></td>
            <?php endif; ?>
            <td>
                <?php if (!empty($item['foto'])): ?>
                <a href="<?= htmlspecialchars($item['foto']) ?>" target="_blank" title="Ver Foto">
                    <i class="feather icon-image"></i> Ver Foto
                </a>
                <?php endif; ?>
            </td>
            <?php
            $rolUsuario = $_SESSION['session_user']['tipo'] ?? '';
            if (in_array($rolUsuario, ['SuperAdministrador', 'Administrador'])):
                $dataAttrs = implode(' ', array_map(function($k, $v) {
                    return 'data-' . htmlspecialchars($k) . '="' . htmlspecialchars($v ?? '') . '"';
                }, array_keys($item), $item));
            ?>
            <td>
                <button class="btn btn-sm btn-warning btn-editar-hacienda"
                    <?= $dataAttrs ?>
                    title="Editar registro">
                    <i class="feather icon-edit"></i>
                </button>
            </td>
            <?php else: ?>
            <td></td>
            <?php endif; ?>
        </tr>
        <?php endforeach; ?>
        <?php endif; ?>
    </tbody>
</table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Warning Section Ends -->

    <!-- Modal Editar Hacienda -->
    <?php if (in_array($_SESSION['session_user']['tipo'] ?? '', ['SuperAdministrador', 'Administrador'])): ?>
    <style>
      #modalEditarHacienda .modal-content {
        max-height: 85vh;
      }
      #modalEditarHacienda select.form-control,
      #modalEditarHacienda select.form-control-sm {
        height:38px;
        padding:6px 12px;
        font-size:14px;
        border:1px solid #ced4da;
        border-radius:6px;
        background-color:#fff;
        appearance:auto;
        -webkit-appearance:auto;
      }
      #modalEditarHacienda select.form-control:focus,
      #modalEditarHacienda select.form-control-sm:focus {
        border-color:#20427F;
        box-shadow:0 0 0 0.2rem rgba(32,66,127,0.2);
        outline:none;
      }
      #modalEditarHacienda .form-control-sm:not(select) {
        height:36px;
        font-size:14px;
        border-radius:6px;
      }
      #modalEditarHacienda textarea.form-control-sm {
        height:auto;
      }
    </style>
    <div class="modal fade" id="modalEditarHacienda" tabindex="-1" role="dialog" aria-labelledby="modalEditarHaciendaLabel" aria-hidden="true">
  <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable" role="document">
    <div class="modal-content border-0 shadow-lg" style="border-radius:12px;">

      <!-- Header -->
      <div class="modal-header border-0 px-4 py-3" style="background:linear-gradient(135deg,#20427F 0%,#132b52 100%); flex-shrink: 0;">
        <div class="d-flex align-items-center">
          <div class="d-flex align-items-center justify-content-center rounded-circle mr-3"
               style="width:38px;height:38px;background:rgba(255,255,255,0.15);">
            <i class="feather icon-edit-2 text-white" style="font-size:16px;"></i>
          </div>
          <div>
            <h5 class="modal-title text-white mb-0 font-weight-bold" id="modalEditarHaciendaLabel">Editar Registro</h5>
            <small class="text-white-50">Secretaría de Hacienda</small>
          </div>
        </div>
        <button type="button" class="close text-white" data-dismiss="modal" style="opacity:.8;">
          <i class="feather icon-x" style="font-size:20px;"></i>
        </button>
      </div>

      <form id="formEditarHacienda" class="d-flex flex-column" style="overflow: hidden; margin-bottom: 0;">
        
        <!-- Modal Body -->
        <div class="modal-body px-4 py-3" style="background:#f8f9fc; overflow-y: auto; max-height: calc(100vh - 200px);">
          <input type="hidden" name="id" id="edit_id">
          <input type="hidden" name="accion" id="edit_accion">

          <!-- Fecha -->
          <div class="card border-0 shadow-sm mb-3" style="border-radius:10px;">
            <div class="card-body p-3">
              <div class="row">
                <div class="col-md-6">
                  <label class="text-muted small font-weight-bold text-uppercase mb-1">
                    <i class="feather icon-calendar mr-1"></i>Fecha
                  </label>
                  <input type="date" class="form-control form-control-sm" name="date" id="edit_date">
                </div>
              </div>
            </div>
          </div>

          <!-- Observaciones -->
          <div class="card border-0 shadow-sm mb-3 edit-grupo edit-grupo-observaciones d-none" style="border-radius:10px;">
            <div class="card-body p-3">
              <label class="text-muted small font-weight-bold text-uppercase mb-1">
                <i class="feather icon-message-square mr-1"></i>Observaciones
              </label>
              <textarea class="form-control form-control-sm" name="observaciones" id="edit_observaciones" rows="2" placeholder="Observaciones opcionales..."></textarea>
            </div>
          </div>

          <!-- Establecimiento -->
          <div class="card border-0 shadow-sm mb-3 edit-grupo edit-grupo-establecimiento d-none" style="border-radius:10px;">
            <div class="card-header border-0 pb-0 pt-3 px-3" style="background:transparent;">
              <span class="text-muted small font-weight-bold text-uppercase">
                <i class="feather icon-home mr-1"></i>Datos del Establecimiento
              </span>
            </div>
            <div class="card-body p-3">
              <div class="row">
                <div class="col-md-6 form-group mb-2">
                  <label class="small text-muted mb-1">Nombre Establecimiento</label>
                  <input type="text" class="form-control form-control-sm" name="nombre_establecimiento" id="edit_nombre_establecimiento" placeholder="Nombre...">
                </div>
                <div class="col-md-6 form-group mb-2">
                  <label class="small text-muted mb-1">Administrador</label>
                  <input type="text" class="form-control form-control-sm" name="administrador" id="edit_administrador" placeholder="Administrador...">
                </div>
                <div class="col-md-6 form-group mb-2">
                  <label class="small text-muted mb-1">Tipo Establecimiento</label>
                  <select class="form-control form-control-sm" name="tipoe" id="edit_tipoe">
                    <option value="Establecimiento comercial">Establecimiento comercial</option>
                  </select>
                </div>
                <div class="col-md-6 form-group mb-2">
                  <label class="small text-muted mb-1">Establecimiento</label>
                  <select class="form-control form-control-sm" name="establecimiento" id="edit_establecimiento">
                    <option value="Bodega">Bodega</option>
                    <option value="Comercializadora">Comercializadora</option>
                    <option value="Dulcería">Dulcería</option>
                    <option value="Bar/Discoteca">Bar/Discoteca</option>
                    <option value="Tienda">Tienda</option>
                    <option value="Distribuidora">Distribuidora</option>
                    <option value="Supermercado/Fruver">Supermercado/Fruver</option>
                    <option value="Restaurante/Comidas">Restaurante/Comidas</option>
                    <option value="Cafetería">Cafetería</option>
                    <option value="Autoservicio">Autoservicio</option>
                    <option value="Estanco/Licorera">Estanco/Licorera</option>
                    <option value="Panadería/Pastelería">Panadería/Pastelería</option>
                    <option value="Variedades/Abarrotes">Variedades/Abarrotes</option>
                    <option value="Quiosco/Caseta">Quiosco/Caseta</option>
                    <option value="Otro">Otro</option>
                    <option value="Hotel/Hospedaje">Hotel/Hospedaje</option>
                    <option value="Miscelánea">Miscelánea</option>
                    <option value="Granero">Granero</option>
                    <option value="Farmacía/Droguería">Farmacía/Droguería</option>
                    <option value="Balneario">Balneario</option>
                    <option value="Billar">Billar</option>
                    <option value="Agencia">Agencia</option>
                    <option value="Asadero">Asadero</option>
                  </select>
                </div>
                <div class="col-md-6 form-group mb-2">
                  <label class="small text-muted mb-1">Dirección</label>
                  <input type="text" class="form-control form-control-sm" name="direccion" id="edit_direccion" placeholder="Dirección...">
                </div>
                <div class="col-md-6 form-group mb-0">
                  <label class="small text-muted mb-1">Barrio</label>
                  <input type="text" class="form-control form-control-sm" name="barrio" id="edit_barrio" placeholder="Barrio...">
                </div>
              </div>
            </div>
          </div>

          <!-- GOA Aprehensiones -->
          <div class="card border-0 shadow-sm mb-3 edit-grupo edit-grupo-aprehensiones d-none" style="border-radius:10px;">
            <div class="card-header border-0 pb-0 pt-3 px-3" style="background:transparent;">
              <span class="text-muted small font-weight-bold text-uppercase">
                <i class="feather icon-package mr-1"></i>Aprehensiones
              </span>
            </div>
            <div class="card-body p-3">
              <div class="row">
                <div class="col-md-6 form-group mb-0">
                  <label class="small text-muted mb-1">Cantidad Aprehendida</label>
                  <input type="number" class="form-control form-control-sm" name="cantidad_aprehendida" id="edit_cantidad_aprehendida" min="0">
                </div>
                <div class="col-md-6 form-group mb-0">
                  <label class="small text-muted mb-1">Avalúo Comercial ($)</label>
                  <input type="number" class="form-control form-control-sm" name="avaluo_comercial" id="edit_avaluo_comercial" min="0" step="0.01">
                </div>
              </div>
            </div>
          </div>

          <!-- Visitas -->
          <div class="card border-0 shadow-sm mb-3 edit-grupo edit-grupo-visitas d-none" style="border-radius:10px;">
            <div class="card-body p-3">
              <label class="text-muted small font-weight-bold text-uppercase mb-1">
                <i class="feather icon-map-pin mr-1"></i>Cantidad Visitas al Municipio
              </label>
              <input type="number" class="form-control form-control-sm" name="cantidad_visitas_al_municipio" id="edit_cantidad_visitas_al_municipio" min="0">
            </div>
          </div>

          <!-- Capacitación Fiscal -->
          <div class="card border-0 shadow-sm mb-3 edit-grupo edit-grupo-capacitacion-fiscal d-none" style="border-radius:10px;">
            <div class="card-body p-3">
              <label class="text-muted small font-weight-bold text-uppercase mb-1">
                <i class="feather icon-users mr-1"></i>Personas Capacitadas
              </label>
              <input type="number" class="form-control form-control-sm" name="cantidad_personas" id="edit_cantidad_personas" min="0">
            </div>
          </div>

          <!-- Capacitación GOA -->
          <div class="card border-0 shadow-sm mb-3 edit-grupo edit-grupo-capacitacion-goa d-none" style="border-radius:10px;">
            <div class="card-header border-0 pb-0 pt-3 px-3" style="background:transparent;">
              <span class="text-muted small font-weight-bold text-uppercase">
                <i class="feather icon-book-open mr-1"></i>Capacitación GOA
              </span>
            </div>
            <div class="card-body p-3">
              <div class="row">
                <div class="col-md-8 form-group mb-0">
                  <label class="small text-muted mb-1">Tipo Capacitación</label>
                  <select class="form-control form-control-sm" name="tipo_capacitacion_goa" id="edit_tipo_capacitacion_goa">
                    <option value="">Seleccione</option>
                    <option value="Capacitaciones a Aliados Estratégicos">Capacitaciones a Aliados Estratégicos</option>
                    <option value="Capacitaciones a Jóvenes">Capacitaciones a Jóvenes</option>
                    <option value="Capacitaciones al GOA">Capacitaciones al GOA</option>
                  </select>
                </div>
                <div class="col-md-4 form-group mb-0">
                  <label class="small text-muted mb-1">N° Asistentes</label>
                  <input type="number" class="form-control form-control-sm" name="numero_asistentes" id="edit_numero_asistentes" min="0">
                </div>
              </div>
            </div>
          </div>

          <!-- Impuesto al Consumo -->
          <div class="card border-0 shadow-sm mb-3 edit-grupo edit-grupo-consumo d-none" style="border-radius:10px;">
            <div class="card-header border-0 pb-0 pt-3 px-3" style="background:transparent;">
              <span class="text-muted small font-weight-bold text-uppercase">
                <i class="feather icon-dollar-sign mr-1"></i>Impuesto al Consumo
              </span>
            </div>
            <div class="card-body p-3">
              <div class="row">
                <div class="col-md-6 form-group mb-0">
                  <label class="small text-muted mb-1">Valor Importado ($)</label>
                  <input type="number" class="form-control form-control-sm" name="valor_importado" id="edit_valor_importado" min="0" step="0.01">
                </div>
                <div class="col-md-6 form-group mb-0">
                  <label class="small text-muted mb-1">Valor Nacional ($)</label>
                  <input type="number" class="form-control form-control-sm" name="valor_nacional" id="edit_valor_nacional" min="0" step="0.01">
                </div>
              </div>
            </div>
          </div>

          <!-- Impuesto de Registro -->
          <div class="card border-0 shadow-sm mb-3 edit-grupo edit-grupo-registro d-none" style="border-radius:10px;">
            <div class="card-header border-0 pb-0 pt-3 px-3" style="background:transparent;">
              <span class="text-muted small font-weight-bold text-uppercase">
                <i class="feather icon-file-text mr-1"></i>Impuesto de Registro
              </span>
            </div>
            <div class="card-body p-3">
              <div class="row">
                <div class="col-md-6 form-group mb-0">
                  <label class="small text-muted mb-1">Valor Trámite ($)</label>
                  <input type="number" class="form-control form-control-sm" name="valor_tramite" id="edit_valor_tramite" min="0" step="0.01">
                </div>
                <div class="col-md-6 form-group mb-0">
                  <label class="small text-muted mb-1">Valor Recaudo ($)</label>
                  <input type="number" class="form-control form-control-sm" name="valor_recaudo" id="edit_valor_recaudo" min="0" step="0.01">
                </div>
              </div>
            </div>
          </div>

          <!-- Vehicular -->
          <div class="card border-0 shadow-sm mb-3 edit-grupo edit-grupo-vehicular d-none" style="border-radius:10px;">
            <div class="card-header border-0 pb-0 pt-3 px-3" style="background:transparent;">
              <span class="text-muted small font-weight-bold text-uppercase">
                <i class="feather icon-truck mr-1"></i>Impuesto Vehicular
              </span>
            </div>
            <div class="card-body p-3">
              <div class="row">
                <div class="col-md-6 form-group mb-2">
                  <label class="small text-muted mb-1">Valor Trámite ($)</label>
                  <input type="number" class="form-control form-control-sm" name="valor_tramite_impuesto_vehicular" id="edit_valor_tramite_impuesto_vehicular" min="0" step="0.01">
                </div>
                <div class="col-md-6 form-group mb-2">
                  <label class="small text-muted mb-1">Valor Recaudo ($)</label>
                  <input type="number" class="form-control form-control-sm" name="valor_recaudo_impuesto_vehicular" id="edit_valor_recaudo_impuesto_vehicular" min="0" step="0.01">
                </div>
                <div class="col-md-3 form-group mb-0">
                  <label class="small text-muted mb-1">Operativos</label>
                  <input type="number" class="form-control form-control-sm" name="vehicular_cantidad_operativos" id="edit_vehicular_cantidad_operativos" min="0">
                </div>
                <div class="col-md-3 form-group mb-0">
                  <label class="small text-muted mb-1">Emplazados</label>
                  <input type="number" class="form-control form-control-sm" name="vehicular_cantidad_emplazados" id="edit_vehicular_cantidad_emplazados" min="0">
                </div>
                <div class="col-md-3 form-group mb-0">
                  <label class="small text-muted mb-1">Placas Consultadas</label>
                  <input type="number" class="form-control form-control-sm" name="vehicular_cantidad_placas_consultadas" id="edit_vehicular_cantidad_placas_consultadas" min="0">
                </div>
                <div class="col-md-3 form-group mb-0">
                  <label class="small text-muted mb-1">Campañas Sensib.</label>
                  <input type="number" class="form-control form-control-sm" name="vehicular_cantidad_campanas_sensibilizacion" id="edit_vehicular_cantidad_campanas_sensibilizacion" min="0">
                </div>
              </div>
            </div>
          </div>

          <!-- Estampillas -->
          <div class="card border-0 shadow-sm mb-3 edit-grupo edit-grupo-estampilla d-none" style="border-radius:10px;">
            <div class="card-header border-0 pb-0 pt-3 px-3" style="background:transparent;">
              <span class="text-muted small font-weight-bold text-uppercase">
                <i class="feather icon-award mr-1"></i>Estampillas
              </span>
            </div>
            <div class="card-body p-3">
              <div class="row">
                <div class="col-md-6 form-group mb-0">
                  <label class="small text-muted mb-1">Estampilla</label>
                  <input type="text" class="form-control form-control-sm" name="estampilla" id="edit_estampilla" placeholder="Nombre estampilla...">
                </div>
                <div class="col-md-6 form-group mb-0">
                  <label class="small text-muted mb-1">Valor Estampilla ($)</label>
                  <input type="number" class="form-control form-control-sm" name="valor_estampilla" id="edit_valor_estampilla" min="0" step="0.01">
                </div>
              </div>
            </div>
          </div>

          <!-- GOA Jurídico -->
          <div class="edit-grupo edit-grupo-goa-juridico d-none">
            <div class="card border-0 shadow-sm mb-3" style="border-radius:10px;">
              <div class="card-header border-0 pb-0 pt-3 px-3" style="background:transparent;">
                <span class="text-muted small font-weight-bold text-uppercase">
                  <i class="feather icon-lock mr-1"></i>Custodia
                </span>
              </div>
              <div class="card-body p-3">
                <div class="row">
                  <div class="col-md-4 form-group mb-0">
                    <label class="small text-muted mb-1">Valor Total ($)</label>
                    <input type="number" class="form-control form-control-sm" name="goa_juridico_custodia_valor_total" id="edit_goa_juridico_custodia_valor_total" min="0" step="0.01">
                  </div>
                  <div class="col-md-4 form-group mb-0">
                    <label class="small text-muted mb-1">Cant. Procesos</label>
                    <input type="number" class="form-control form-control-sm" name="goa_juridico_custodia_cantidad_procesos" id="edit_goa_juridico_custodia_cantidad_procesos" min="0">
                  </div>
                  <div class="col-md-4 form-group mb-0">
                    <label class="small text-muted mb-1">Cant. Unidades</label>
                    <input type="number" class="form-control form-control-sm" name="goa_juridico_custodia_cantidad_unidades" id="edit_goa_juridico_custodia_cantidad_unidades" min="0">
                  </div>
                </div>
              </div>
            </div>
            <div class="card border-0 shadow-sm mb-3" style="border-radius:10px;">
              <div class="card-header border-0 pb-0 pt-3 px-3" style="background:transparent;">
                <span class="text-muted small font-weight-bold text-uppercase">
                  <i class="feather icon-trash-2 mr-1"></i>Destrucción
                </span>
              </div>
              <div class="card-body p-3">
                <div class="row">
                  <div class="col-md-6 form-group mb-0">
                    <label class="small text-muted mb-1">Cant. Unidades</label>
                    <input type="number" class="form-control form-control-sm" name="goa_juridico_destruccion_cantidad_unidades" id="edit_goa_juridico_destruccion_cantidad_unidades" min="0">
                  </div>
                  <div class="col-md-6 form-group mb-0">
                    <label class="small text-muted mb-1">Valor Total ($)</label>
                    <input type="number" class="form-control form-control-sm" name="goa_juridico_destruccion_valor_total" id="edit_goa_juridico_destruccion_valor_total" min="0" step="0.01">
                  </div>
                </div>
              </div>
            </div>
          </div>

        </div><!-- /modal-body -->

        <!-- Footer con botones en blanco -->
        <div class="modal-footer border-0 px-4 py-3" style="background:#f8f9fc; flex-shrink: 0;">
          <button type="button" class="btn px-4 text-white" data-dismiss="modal" style="border-radius:8px; background: #6c757d; border: none; color: white !important;">
            <i class="feather icon-x mr-1"></i>Cancelar
          </button>
          <button type="submit" id="btnGuardarHacienda" class="btn px-4 text-white" style="border-radius:8px; background:linear-gradient(135deg,#20427F,#132b52); border:none; box-shadow: 0 4px 6px rgba(0,0,0,0.1); color: white !important;">
            <i class="feather icon-save mr-1"></i>Guardar Cambios
          </button>
        </div>
      </form>

    </div>
  </div>
</div>
    <?php endif; ?>

    <?php include 'admin/include/gerenic_script.php'; ?>
    <!-- Required Js -->
    <script src="assets/js/vendor-all.min.js"></script>
    <script src="assets/js/plugins/bootstrap.min.js"></script>
    <script src="assets/js/pcoded.min.js"></script>
    <?php include 'admin/include/generic_dataTables.php'; ?>
    <script type="text/javascript" src="admin/js/departamento.js"></script>
    <script type="text/javascript" src="admin/js/municipios.js"></script>
    <script type="text/javascript" src="admin/js/hacienda_info.js"></script>
    <script>
        MUNICIPIO.init();

        function handlePolygonClick(element) {
            const url = element.getAttribute('data-url'); // Obtén la URL del atributo data-url
            if (url) {
                window.location.href = url; // Redirige al enlace
            } else {
                console.error('No se encontró una URL válida.');
            }
        }
    </script>
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const tabLinks = document.querySelectorAll('.nav-tabs .nav-link');
            tabLinks.forEach(tab => {
                tab.addEventListener('click', function(event) {
                    event.preventDefault();
                    tabLinks.forEach(link => link.classList.remove('active'));
                    const tabPanes = document.querySelectorAll('.tab-pane');
                    tabPanes.forEach(pane => pane.classList.remove('show', 'active'));
                    this.classList.add('active');
                    const targetPane = document.querySelector(this.getAttribute('href'));
                    if (targetPane) {
                        targetPane.classList.add('show', 'active');
                    }
                });
            });
        });
    </script>
</body>

</html>