$(document).ready(function () {
    cargarTablaInversiones();

    $('#valor').on('input', function () {
        const raw = this.value.replace(/\D/g, '');
        this.value = raw ? new Intl.NumberFormat('es-CO').format(raw) : '';
    });

    $('#edit_valor').on('input', function () {
        const raw = this.value.replace(/\D/g, '');
        this.value = raw ? new Intl.NumberFormat('es-CO').format(raw) : '';
    });

    $('#customSearchInversiones').on('keyup', function () {
        if (tablaInversiones) {
            tablaInversiones.search(this.value).draw();
        }
    });

    $('#formEditarInversion').on('submit', function (e) {
        e.preventDefault();
        guardarEdicionInversion(this);
    });

    $('#edit_imagen').on('change', function () {
        const preview = document.getElementById('edit_previewImagen');
        preview.innerHTML = '';
        const file = this.files[0];
        if (!file) return;
        const img = document.createElement('img');
        img.style.cssText = 'max-width:180px;border-radius:10px;margin-top:8px;';
        img.src = URL.createObjectURL(file);
        preview.appendChild(img);
    });

    $('#imagen').on('change', function () {
        const preview = document.getElementById('previewImagen');
        preview.innerHTML = '';
        const file = this.files[0];
        if (!file) return;
        const img = document.createElement('img');
        img.style.cssText = 'max-width:220px;border-radius:16px;margin-top:10px;box-shadow:0 12px 25px rgba(0,0,0,.25)';
        img.src = URL.createObjectURL(file);
        preview.appendChild(img);
    });

    $('#ingresoVisita').on('submit', function (e) {
        e.preventDefault();
        guardarInversion(this);
    });
});

let tablaInversiones = null;

function cargarTablaInversiones() {
    if (tablaInversiones !== null) {
        tablaInversiones.destroy();
    }
    tablaInversiones = $("#dynamictable").DataTable({
        dom: "lrtip",
        processing: true,
        serverSide: true,
        responsive: true,
        ajax: {
            url: "./admin/controllers/inversionCtrl.php",
            type: "POST",
            contentType: "application/json",
            data: function (d) {
                return JSON.stringify({
                    method: "inversion_list",
                    data: d
                });
            },
            dataSrc: function (json) {
                // console.log('Response:', json);
                if (json.data) return json.data;
                return [];
            },
            error: function(xhr, error, thrown) {
                console.log('AJAX Error:', xhr.responseText);
            }
        },
        columns: [
            {
                data: "id",
                render: function (data) {
                    return `<button class="btn btn-sm btn-info mr-1" onclick="verInversion(${data})" title="Ver"><i class="feather icon-eye"></i></button>
                            <button class="btn btn-sm btn-warning mr-1" onclick="editarInversion(${data})" title="Editar"><i class="feather icon-edit"></i></button>
                            <button class="btn btn-sm btn-danger" onclick="eliminarInversion(${data})" title="Eliminar"><i class="feather icon-trash-2"></i></button>`;
                }
            },
            { data: "id" },
            { data: "fecha" },
            {
                data: "tipo_seccion",
                render: function(data) {
                    const tipoLabel = {
                        movilidad: 'Movilidad', tecnologia: 'Tecnología', proyectos: 'Proyectos',
                        intendencia: 'Intendencia', infraestructura: 'Infraestructura',
                        pagos: 'Pagos', convenios: 'Convenios'
                    };
                    return `<span class="badge badge-secondary">${tipoLabel[data] || data}</span>`;
                }
            },
            { data: "titulo" },
            { data: "institucion" },
            { data: "municipios_str" },
            { data: "direccion" },
            {
                data: "valor",
                render: function(data) { return formatPesos(data); },
                className: 'text-right'
            }
        ]
    });
}

function formatPesos(v) {
    if (!v) return '$0';
    return '$' + new Intl.NumberFormat('es-CO').format(v);
}

function toggleDesc(link, id, fullText) {
    const span = document.getElementById(id);
    if (span.dataset.expanded === 'true') {
        span.textContent = fullText.length > 80 ? fullText.substring(0, 80) + '...' : fullText;
        span.dataset.expanded = 'false';
        link.textContent = ' Ver más';
    } else {
        span.textContent = fullText;
        span.dataset.expanded = 'true';
        link.textContent = ' Ver menos';
    }
}

// =========================================
// VER INVERSIÓN
// =========================================
function verInversion(id) {
    fetch('admin/ajax/rqst.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `op=inversion_get&id=${id}`
    })
    .then(r => r.json())
    .then(data => {
        const r = data?.output?.response;
        if (!r) return Swal.fire('Error', 'No se encontró el registro', 'error');

        const imgHtml = r.imagen
            ? `<img src="admin/uploads/inversiones/${r.imagen}" style="max-width:220px;border-radius:10px;margin-top:8px;">`
            : '<em>Sin imagen</em>';

        var municipios = r.municipios && r.municipios.length ? r.municipios.join(', ') : r.municipio || '—';
        document.getElementById('modalVerBody').innerHTML = `
            <div class="row">
                <div class="col-md-6"><strong>ID:</strong> ${r.id}</div>
                <div class="col-md-6"><strong>Fecha:</strong> ${r.fecha}</div>
                <div class="col-md-6 mt-2"><strong>Tipo:</strong> ${r.tipo_seccion}</div>
                <div class="col-md-6 mt-2"><strong>Contrato:</strong> ${r.titulo}</div>
                <div class="col-md-6 mt-2"><strong>Institución:</strong> ${r.institucion || '—'}</div>
                <div class="col-md-6 mt-2"><strong>Dirección:</strong> ${r.direccion || '—'}</div>
                <div class="col-md-12 mt-2"><strong>Municipios:</strong> ${municipios}</div>
                <div class="col-md-6 mt-2"><strong>Valor:</strong> ${formatPesos(r.valor)}</div>
                <div class="col-md-6 mt-2"><strong>Cantidad:</strong> ${r.cantidad || 0}</div>
                <div class="col-md-12 mt-2"><strong>Descripción:</strong><br>${r.descripcion || '—'}</div>
                <div class="col-md-12 mt-2"><strong>Imagen:</strong><br>${imgHtml}</div>
                <div class="col-md-12 mt-2"><small class="text-muted">Creado: ${r.created_at}</small></div>
            </div>
        `;
        $('#modalVerInversion').modal('show');
    })
    .catch(() => Swal.fire('Error de conexión', '', 'error'));
}

// =========================================
// EDITAR INVERSIÓN — cargar datos en modal
// =========================================
function editarInversion(id) {
    fetch('admin/ajax/rqst.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `op=inversion_get&id=${id}`
    })
    .then(r => r.json())
    .then(data => {
        const r = data?.output?.response;
        if (!r) return Swal.fire('Error', 'No se encontró el registro', 'error');

        document.getElementById('edit_id').value          = r.id;
        document.getElementById('edit_fecha').value        = r.fecha;
        document.getElementById('edit_tipo_seccion').value = r.tipo_seccion;
        document.getElementById('edit_titulo').value       = r.titulo;
        document.getElementById('edit_institucion').value  = r.institucion;
        document.getElementById('edit_direccion').value    = r.direccion;
        document.getElementById('edit_valor').value        = r.valor ? new Intl.NumberFormat('es-CO').format(r.valor) : '';
        document.getElementById('edit_cantidad').value     = r.cantidad || '';
        document.getElementById('edit_descripcion').value  = r.descripcion || '';
        document.getElementById('edit_previewImagen').innerHTML = r.imagen
            ? `<img src="admin/uploads/inversiones/${r.imagen}" style="max-width:180px;border-radius:10px;">`
            : '';

        // Restore municipios agrupados por provincia
        var $container = $('#edit-municipios-container');
        $container.find('.row-municipio').not(':first').remove();
        if (r.municipios && r.municipios.length) {
            var cods = r.municipios;
            $.ajax({
                url: 'admin/ajax/rqst.php',
                type: 'POST',
                dataType: 'json',
                data: { op: 'municipiosbyprovincia', provincia: '__ALL__' },
                success: function(res) {
                    if (!res.output || !res.output.valid) return;
                    var allMun = res.output.response;
                    // Group municipio codes by province
                    var grouped = {};
                    $.each(allMun, function(i, m) {
                        if (cods.indexOf(m.codigo_muncipio) !== -1) {
                            if (!grouped[m.subregion]) grouped[m.subregion] = [];
                            grouped[m.subregion].push(m.codigo_muncipio);
                        }
                    });
                    var provs = Object.keys(grouped);
                    if (!provs.length) return;

                    // Helper: select municipios in a hidden select
                    function selectMun($hidden, codigos) {
                        $hidden.find('option').each(function() {
                            $(this).prop('selected', codigos.indexOf($(this).val()) !== -1);
                        });
                        buildMunPills($hidden);
                    }

                    // Helper: create a new row for a provincia
                    function crearFilaProvincia(prov, codigos, $insertBefore) {
                        var $row = $(
                            '<div class="row-municipio mb-3 pb-2" style="border-bottom:1px solid rgba(255,255,255,.08);">' +
                                '<div class="d-flex align-items-center gap-2 mb-2" style="gap:8px;">' +
                                    '<select class="form-control provincia-select" style="flex:0 0 220px;width:220px;">' +
                                        '<option value="">-- Provincia --</option>' +
                                    '</select>' +
                                    '<button type="button" class="btn btn-danger btn-sm btn-remove-municipio" style="flex-shrink:0;padding:6px 12px;border-radius:10px;" title="Quitar">' +
                                        '<i class="feather icon-minus"></i>' +
                                    '</button>' +
                                '</div>' +
                                '<select class="municipio-select-hidden" name="municipios[]" multiple style="display:none;"></select>' +
                                '<div class="municipio-pills d-flex flex-wrap" style="gap:6px;min-height:32px;padding:4px 0;"></div>' +
                            '</div>'
                        );
                        $row.find('.btn-remove-municipio').on('click', function() {
                            $row.remove();
                            if (typeof recargarTodasLasProvincias === 'function') recargarTodasLasProvincias();
                        });
                        $row.find('.provincia-select').on('change', function() {
                            if (typeof cargarMunicipios === 'function') cargarMunicipios($row, $(this).val());
                            if (typeof recargarTodasLasProvincias === 'function') recargarTodasLasProvincias();
                        });
                        // Fill provincia dropdown
                        var $sel = $row.find('.provincia-select');
                        $sel.empty().append('<option value="">-- Provincia --</option>');
                        $.each(PROVINCIAS_DATA, function(i, p) {
                            $sel.append('<option value="' + p.provincia + '">' + p.provincia + '</option>');
                        });
                        $sel.val(prov);
                        if ($insertBefore) $row.insertBefore($insertBefore);
                        else $container.find('.row-municipio').first().after($row);
                        return $row;
                    }

                    // Load first provincia in the existing first row
                    var $firstRow = $container.find('.row-municipio').first();
                    var $firstSel = $firstRow.find('.provincia-select');
                    $firstSel.val(provs[0]);

                    // Get municipios for first provincia via AJAX
                    $.ajax({
                        url: 'admin/ajax/rqst.php',
                        type: 'POST',
                        dataType: 'json',
                        data: { op: 'municipiosbyprovincia', provincia: provs[0] },
                        success: function(res2) {
                            if (res2.output && res2.output.valid) {
                                var $hidden = $firstRow.find('.municipio-select-hidden');
                                $hidden.empty();
                                $.each(res2.output.response, function(i, m) {
                                    $hidden.append('<option value="' + m.codigo_muncipio + '">' + m.municipio + '</option>');
                                });
                                selectMun($hidden, grouped[provs[0]]);
                            }
                            // Create rows for remaining provincias sequentially
                            var idx = 1;
                            function cargarSiguiente() {
                                if (idx >= provs.length) {
                                    if (typeof recargarTodasLasProvincias === 'function') recargarTodasLasProvincias();
                                    return;
                                }
                                var prov = provs[idx];
                                var $newRow = crearFilaProvincia(prov, grouped[prov], null);
                                // Load municipios for this provincia
                                $.ajax({
                                    url: 'admin/ajax/rqst.php',
                                    type: 'POST',
                                    dataType: 'json',
                                    data: { op: 'municipiosbyprovincia', provincia: prov },
                                    success: function(res3) {
                                        if (res3.output && res3.output.valid) {
                                            var $h = $newRow.find('.municipio-select-hidden');
                                            $h.empty();
                                            $.each(res3.output.response, function(i, m) {
                                                $h.append('<option value="' + m.codigo_muncipio + '">' + m.municipio + '</option>');
                                            });
                                            selectMun($h, grouped[prov]);
                                        }
                                        idx++;
                                        cargarSiguiente();
                                    },
                                    error: function() { idx++; cargarSiguiente(); }
                                });
                            }
                            cargarSiguiente();
                        }
                    });
                }
            });
        }

        $('#modalEditarInversion').modal('show');
    })
    .catch(() => Swal.fire('Error de conexión', '', 'error'));
}

// =========================================
// GUARDAR EDICIÓN
// =========================================
function guardarEdicionInversion(form) {
    const valor = document.getElementById('edit_valor').value.replace(/\./g, '');
    document.getElementById('edit_valor').value = valor;

    Swal.fire({ title: 'Guardando...', allowOutsideClick: false, didOpen: () => Swal.showLoading() });

    const formData = new FormData(form);
    formData.append('op', 'inversion_update');

    fetch('admin/ajax/rqst.php', { method: 'POST', body: formData })
    .then(r => r.json())
    .then(data => {
        const ok = data?.output?.valid ?? false;
        if (ok) {
            Swal.fire('Actualizado correctamente', '', 'success');
            $('#modalEditarInversion').modal('hide');
            cargarTablaInversiones();
        } else {
            const msg = data?.output?.response?.content ?? 'Error al actualizar';
            Swal.fire('Error', msg, 'error');
        }
    })
    .catch(() => Swal.fire('Error de conexión', '', 'error'));
}

// =========================================
// ELIMINAR INVERSIÓN
// =========================================
function eliminarInversion(id) {
    Swal.fire({
        title: '¿Eliminar este registro?',
        text: 'Esta acción no se puede deshacer.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonText: 'Cancelar',
        confirmButtonText: 'Sí, eliminar'
    }).then(result => {
        if (!result.isConfirmed) return;

        fetch('admin/ajax/rqst.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: `op=inversion_delete&id=${id}`
        })
        .then(r => r.json())
        .then(data => {
            const ok = data?.output?.valid ?? false;
            if (ok) {
                Swal.fire('Eliminado', '', 'success');
                cargarTablaInversiones();
            } else {
                Swal.fire('Error', 'No se pudo eliminar', 'error');
            }
        })
        .catch(() => Swal.fire('Error de conexión', '', 'error'));
    });
}

// =========================================
// GUARDAR INVERSIÓN (formulario principal)
// =========================================
// =========================================
// GRÁFICA INSTITUCIÓN BENEFICIADA (dashboard_seguridad)
// =========================================
function initChartInstituciones() {
    const chartId  = 'chartInstituciones';

    if (typeof Highcharts === 'undefined' || !document.getElementById(chartId)) {
        return;
    }

    const rawData = window.__instData || [];

    if (!rawData.length) {
        return;
    }

    const normalizeText = (text = '') => {
        return String(text)
            .trim()
            .toUpperCase()
            .replace(/\s+/g, ' ')
            .normalize('NFD')
            .replace(/[\u0300-\u036f]/g, '');
    };

    const colorMap = {
        'POLICIA DESAN': '#ff0000',             // rojo
        'POLICIA MEBUC': '#0000ff',             // azul
        'POLICIA DEMAM': '#ffff00',             // amarillo
        'EJERCITO NACIONAL': '#00ff00',         // verde
        'ARMADA NACIONAL': '#ff8000',           // naranja
        'FISCALIA': '#8000ff',                  // violeta
        'MIGRACION COLOMBIA': '#00ffff',        // cyan
        'INPEC': '#ff1493',                     // rosa fuerte
        'UNP': '#8b4513',                       // café
        'DEPARTAMENTO DE SANTANDER': '#ffd700', // dorado
        'OTRO': '#000000',                      // negro
        'SIN INSTITUCION': '#808080'            // gris
    };

    const finalData = rawData.map((p) => {
        const nameKey = normalizeText(p.name);
        return {
            name: p.name,
            y: Number(p.y) || 0,
            valor: Number(p.valor) || 0,
            color: colorMap[nameKey] || '#f1ed08'
        };
    });


    Highcharts.chart(chartId, {
        chart: {
            type: 'pie',
            backgroundColor: 'transparent'
        },
        title: { text: null },
        credits: { enabled: false },
        legend: { enabled: false },
        plotOptions: {
            pie: {
                innerSize: '0%',
                colorByPoint: true,
                showInLegend: true,
                dataLabels: {
                    enabled: true,
                    format: '<b>{point.name}</b><br>{point.percentage:.1f}%',
                    style: {
                        color: '#ffffff',
                        textOutline: 'none',
                        fontSize: '12px'
                    }
                },
            }
        },
        tooltip: {
            pointFormatter: function () {
                const valor = Number(this.valor) || 0;
                const v = '$' + valor.toLocaleString('es-CO');
                return '<b>' + this.name + '</b><br/>Registros: <b>' + this.y + '</b><br/>Inversión total: <b>' + v + '</b>';
            }
        },
        series: [{
            name: 'Instituciones',
            data: finalData
        }]
    });
}


function initChartsSeguridad() {
    /* ---- Inversión por Provincia ---- */
    var provData = window.__provData || [];
    if (provData.length > 0 && document.getElementById('chartProvincia')) {
        Highcharts.chart('chartProvincia', {
            chart: { type: 'bar', backgroundColor: 'transparent' },
            title: { text: null },
            credits: { enabled: false },
            legend: { enabled: false },
            xAxis: {
                categories: provData.map(function (p) { return p.provincia || 'Sin provincia'; }),
                labels: { style: { color: '#e2e8f0', fontWeight: 'bold', fontSize: '12px' } },
                gridLineColor: 'rgba(255,255,255,.05)'
            },
            yAxis: {
                title: { text: 'Inversión (COP)', style: { color: '#94a3b8' } },
                labels: { style: { color: '#94a3b8', fontSize: '11px' }, formatter: function () { return '$' + (this.value / 1000000).toFixed(0) + 'M'; } },
                gridLineColor: 'rgba(255,255,255,.05)'
            },
            tooltip: {
                backgroundColor: 'rgba(16,24,40,0.96)', style: { color: '#e2e8f0' },
                borderColor: 'rgba(99,179,237,0.3)',
                formatter: function () {
                    var inv = Number(this.point.valor_inversion) || 0;
                    return '<b>' + this.point.name + '</b><br/>' +
                           'Registros: <b>' + (this.point.registros || 0) + '</b><br/>' +
                           'Inversión: <b>$' + inv.toLocaleString('es-CO') + '</b>';
                }
            },
            plotOptions: {
                bar: { borderRadius: 6, borderWidth: 0 },
                series: { colorByPoint: true, colors: ['#3b82f6','#06b6d4','#22c55e','#f59e0b','#ef4444','#a78bfa','#ec4899','#14b8a6','#f97316','#6366f1'] }
            },
            series: [{
                name: 'Inversión',
                data: provData.map(function (p) {
                    var inv = Number(p.total_valor) || 0;
                    return {
                        name: p.provincia,
                        y: inv,
                        registros: Number(p.total_registros) || 0,
                        valor_inversion: inv
                    };
                })
            }]
        });
    }

    /* ---- Top Municipios por Inversión ---- */
    var munData = window.__munData || [];
    if (munData.length > 0 && document.getElementById('chartMunicipios')) {
        Highcharts.chart('chartMunicipios', {
            chart: { type: 'bar', backgroundColor: 'transparent' },
            title: { text: null },
            credits: { enabled: false },
            legend: { enabled: false },
            xAxis: {
                categories: munData.map(function (m) { return m.nombre_municipio || m.municipio; }),
                labels: { style: { color: '#e2e8f0', fontWeight: 'bold', fontSize: '11px' } },
                gridLineColor: 'rgba(255,255,255,.05)'
            },
            yAxis: {
                title: { text: null },
                labels: { style: { color: '#94a3b8', fontSize: '11px' }, formatter: function () { return '$' + (this.value / 1000000).toFixed(0) + 'M'; } },
                gridLineColor: 'rgba(255,255,255,.05)'
            },
            tooltip: {
                backgroundColor: 'rgba(16,24,40,0.96)', style: { color: '#e2e8f0' },
                borderColor: 'rgba(99,179,237,0.3)',
                formatter: function () {
                    return '<b>' + (this.point.nombreMunicipio || this.point.name) + '</b><br/>Inversión: <b>$' + Number(this.point.y).toLocaleString('es-CO') + '</b>';
                }
            },
            plotOptions: {
                bar: { borderRadius: 6, borderWidth: 0 },
                series: { colorByPoint: true, colors: ['#3b82f6','#06b6d4','#22c55e','#f59e0b','#ef4444','#a78bfa','#ec4899','#14b8a6','#f97316','#6366f1'] }
            },
            series: [{
                name: 'Inversión',
                data: munData.map(function (m) {
                    var nombre = m.nombre_municipio || m.municipio;
                    return { name: nombre, y: Number(m.total) || 0, nombreMunicipio: nombre };
                })
            }]
        });
    }
}

if (document.readyState === 'loading') {
    document.addEventListener(
        'DOMContentLoaded',
        initChartInstituciones
    );
} else {
    initChartInstituciones();
}
document.addEventListener('DOMContentLoaded', initChartsSeguridad);
if (document.readyState !== 'loading') { setTimeout(initChartsSeguridad, 100); }


function guardarInversion(form) {
    const tipo       = form.tipo_seccion.value;
    const titulo     = form.titulo.value;
    const fecha      = form.fecha.value;
    const institucion = form.institucion.value;
    const direccion  = form.direccion.value;
    const valor      = $('#valor').val().replace(/\./g, '');

    if (!tipo)        return Swal.fire('Tipo requerido', '', 'warning');
    if (!fecha)       return Swal.fire('Fecha requerida', '', 'warning');
    if (!titulo)      return Swal.fire('Contrato requerido', '', 'warning');
    if (!institucion) return Swal.fire('Institución requerida', '', 'warning');
    if (!direccion)   return Swal.fire('Dirección requerida', '', 'warning');
    if (!valor || parseInt(valor, 10) <= 0) return Swal.fire('Valor inválido', '', 'warning');

    $('#valor').val(valor);

    Swal.fire({ title: 'Guardando inversión...', allowOutsideClick: false, didOpen: () => Swal.showLoading() });

    const formData = new FormData(form);
    formData.append('op', 'inversion_save');

    fetch('admin/ajax/rqst.php', {
        method: 'POST',
        body:   formData
    })
    .then(r => r.json())
    .then(data => {
        const ok = data?.output?.valid ?? false;
        if (ok) {
            Swal.fire('Guardado correctamente.', '', 'success');
            form.reset();
            $('#previewImagen').html('');
            $('#imagen').val('');
            $('#valor').val('');
            // Limpiar provincia/municipios dinámicos
            $('#municipios-container .row-municipio').not(':first').remove();
            var $first = $('#municipios-container .row-municipio').first();
            $first.find('.provincia-select').val('');
            $first.find('.municipio-select-hidden').empty();
            $first.find('.municipio-pills').empty();
            cargarTablaInversiones();
        } else {
            const msg = data?.output?.response?.content ?? 'Error al guardar';
            Swal.fire('Error', msg, 'error');
        }
    })
    .catch(() => Swal.fire('Error de conexión', '', 'error'));
}

// =========================================
// PROVINCIA / MUNICIPIO PILL-SELECTOR
// =========================================
function buildMunPills($hiddenSelect) {
    var $container = $hiddenSelect.siblings('.municipio-pills');
    $container.empty();
    $hiddenSelect.find('option').each(function() {
        if (!$(this).val()) return;
        var val = $(this).val();
        var text = $(this).text();
        var selected = $(this).prop('selected');
        var pill = $('<span class="mun-pill" data-value="' + val + '" style="display:inline-flex;align-items:center;gap:4px;padding:4px 10px;border-radius:999px;font-size:11px;font-weight:800;cursor:pointer;transition:.15s ease;border:1px solid rgba(255,255,255,.15);user-select:none;background:rgba(255,255,255,.06);color:rgba(255,255,255,.8);">' + text + '</span>');
        if (selected) {
            pill.css({ 'background': 'linear-gradient(135deg,#3b82f6,#4f46e5)', 'color': '#fff', 'border-color': 'rgba(255,255,255,.25)' });
        }
        pill.on('click', function() {
            var opt = $hiddenSelect.find('option[value="' + val + '"]');
            opt.prop('selected', !opt.prop('selected'));
            buildMunPills($hiddenSelect);
            $hiddenSelect.trigger('change');
        });
        $container.append(pill);
    });
}

function initProvinciaMunicipio(containerSelector) {
    var $container = $(containerSelector);

    function provinciasSeleccionadasEnOtrasFilas($exceptRow) {
        var seleccionadas = [];
        $container.find('.row-municipio').not($exceptRow).each(function() {
            var val = $(this).find('.provincia-select').val();
            if (val) seleccionadas.push(val);
        });
        return seleccionadas;
    }

    function cargarProvincias($row) {
        var $sel = $row.find('.provincia-select');
        var currentVal = $sel.val();
        var ocupadas = provinciasSeleccionadasEnOtrasFilas($row);
        $sel.empty().append('<option value="">-- Provincia --</option>');
        $.each(PROVINCIAS_DATA, function(i, p) {
            var disabled = ocupadas.indexOf(p.provincia) !== -1;
            $sel.append('<option value="' + p.provincia + '"' + (disabled ? ' disabled' : '') + (p.provincia === currentVal ? ' selected' : '') + '>' + p.provincia + '</option>');
        });
    }

    function recargarTodasLasProvincias() {
        $container.find('.row-municipio').each(function() {
            cargarProvincias($(this));
        });
    }

    function cargarMunicipios($row, provincia, preseleccionados) {
        var $hidden = $row.find('.municipio-select-hidden');
        $hidden.empty();
        if (!provincia) { $row.find('.municipio-pills').empty(); return; }
        $.ajax({
            url: 'admin/ajax/rqst.php',
            type: 'POST',
            dataType: 'json',
            data: { op: 'municipiosbyprovincia', provincia: provincia },
            success: function(res) {
                if (res.output && res.output.valid && res.output.response) {
                    $.each(res.output.response, function(i, m) {
                        var selected = preseleccionados && preseleccionados.indexOf(m.codigo_muncipio) !== -1;
                        $hidden.append('<option value="' + m.codigo_muncipio + '"' + (selected ? ' selected' : '') + '>' + m.municipio + '</option>');
                    });
                    buildMunPills($hidden);
                } else {
                    $row.find('.municipio-pills').empty();
                }
            },
            error: function() {
                $row.find('.municipio-pills').empty();
            }
        });
    }

    function addRow(preseleccionados) {
        var $row = $(
            '<div class="row-municipio mb-3 pb-2" style="border-bottom:1px solid rgba(255,255,255,.08);">' +
                '<div class="d-flex align-items-center gap-2 mb-2" style="gap:8px;">' +
                    '<select class="form-control provincia-select" style="flex:0 0 220px;width:220px;">' +
                        '<option value="">-- Provincia --</option>' +
                    '</select>' +
                    '<button type="button" class="btn btn-danger btn-sm btn-remove-municipio" style="flex-shrink:0;padding:6px 12px;border-radius:10px;" title="Quitar">' +
                        '<i class="feather icon-minus"></i>' +
                    '</button>' +
                '</div>' +
                '<select class="municipio-select-hidden" name="municipios[]" multiple style="display:none;"></select>' +
                '<div class="municipio-pills d-flex flex-wrap" style="gap:6px;min-height:32px;padding:4px 0;"></div>' +
            '</div>'
        );

        $row.find('.btn-remove-municipio').on('click', function() {
            $row.remove();
            recargarTodasLasProvincias();
        });
        $row.find('.provincia-select').on('change', function() {
            cargarMunicipios($row, $(this).val(), preseleccionados);
            recargarTodasLasProvincias();
        });
        cargarProvincias($row);

        $container.children('.row-municipio').first().after($row);
        return $row;
    }

    $container.find('.row-municipio').each(function() {
        var $row = $(this);
        cargarProvincias($row);
        $row.find('.provincia-select').on('change', function() {
            cargarMunicipios($row, $(this).val());
            recargarTodasLasProvincias();
        });
        $row.find('.btn-add-municipio').on('click', function() {
            addRow();
        });
        $row.find('.btn-remove-municipio').on('click', function() {
            $row.remove();
            recargarTodasLasProvincias();
        });
    });
}

$(document).ready(function() {
    initProvinciaMunicipio('#municipios-container');
    initProvinciaMunicipio('#edit-municipios-container');
});
