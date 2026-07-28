let PAYLOAD = null;
let BOLETIN_ACTIVO_ID = 0;

function esc(s){
  return String(s ?? '').replace(/[&<>"']/g, m => ({
    '&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#039;'
  }[m]));
}

function showLoading(){
  $('.loader-bg').show();
  $('#btnGuardar, #btnCargar').prop('disabled', true);
}

function hideLoading(){
  $('.loader-bg').fadeOut(150);
  $('#btnGuardar, #btnCargar').prop('disabled', false);
}

/* =====================================================
   BOLETINES
====================================================== */
function loadBoletines(selectId){
  return $.ajax({
    url: 'admin/ajax/dash_interior_save.php',
    method: 'POST',
    dataType: 'json',
    data: { action: 'list_boletines' }
  }).done(function(res){
    const sel = $('#boletin_select');
    if(!res || !res.ok){
      console.error('Error al cargar boletines:', res?.msg || 'Sin respuesta');
      sel.empty().append('<option value="">-- Error al cargar boletines --</option>');
      return;
    }

    const prevVal = sel.val();
    sel.empty().append('<option value="">-- Datos globales por año --</option>');

    (res.boletines || []).forEach(function(b){
      const star = b.activo == 1 ? '★ ' : '';
      sel.append(`<option value="${b.id}" ${b.activo == 1 ? 'data-activo=1' : ''}>${star}No. ${b.numero} - ${b.fecha}</option>`);
    });

    if (selectId && $(`#boletin_select option[value="${selectId}"]`).length) {
      sel.val(String(selectId));
    } else if (prevVal !== null && prevVal !== undefined && prevVal !== '' && $(`#boletin_select option[value="${prevVal}"]`).length) {
      sel.val(prevVal);
    } else if (res.boletines && res.boletines.length > 0) {
      const active = res.boletines.find(b => b.activo == 1) || res.boletines[0];
      sel.val(active.id);
    }
    sel.trigger('change');
  }).fail(function(jqXHR){
    console.error('Error AJAX al cargar boletines:', jqXHR.status, jqXHR.statusText);
    $('#boletin_select').empty().append('<option value="">-- Error de conexión --</option>');
  });
}

function crearBoletin(){
  const today = new Date().toISOString().split('T')[0];

  Swal.fire({
    title: 'Nuevo Boletín Diario',
    html: `<label style="font-weight:900;margin-bottom:8px">Fecha del boletín:</label>
           <input type="date" class="form-control" id="swal_fecha" value="${today}" style="background:rgba(0,0,0,.25)!important;color:#fff!important;border:1px solid rgba(255,255,255,.15)!important;border-radius:12px!important;font-weight:800">`,
    showCancelButton: true,
    confirmButtonText: '<i class="bi bi-plus-circle"></i> Crear',
    cancelButtonText: 'Cancelar',
    confirmButtonColor: '#00e5ff',
    cancelButtonColor: '#666',
    preConfirm: function(){
      const f = $('#swal_fecha').val();
      if(!f){ Swal.showValidationMessage('Selecciona una fecha'); return false; }
      return f;
    }
  }).then(function(result){
    if(!result.isConfirmed) return;

    const fecha = result.value;
    showLoading();

    $.ajax({
      url: 'admin/ajax/dash_interior_save.php',
      method: 'POST',
      dataType: 'json',
      data: { action: 'create_boletin', fecha: fecha }
    }).done(function(res){
      if(res && res.ok){
        Swal.fire({
          icon: 'success',
          title: 'Boletín creado',
          text: `No. ${res.boletin.numero} - ${res.boletin.fecha}`
        }).then(function(){
          loadBoletines(res.boletin.id);
        });
      } else {
        Swal.fire({ icon:'error', title:'Error', text: res?.msg || 'No se pudo crear' });
      }
    }).always(function(){ hideLoading(); });
  });
}

function seleccionarBoletin(id){
  if(!id || id <= 0){
    BOLETIN_ACTIVO_ID = 0;
    $('#card_boletin_label').text('Datos globales por año');
    $('#anio_label').show();
    $('#anio_boletin_hint').hide();
    $('#anio').prop('disabled', false);
    $('#boletin_select').val('');
    $('#btnDescargarPDF').hide();
    $('#btnActivarBoletin').hide();
    loadYearData();
    return;
  }

  BOLETIN_ACTIVO_ID = parseInt(id, 10);
  showLoading();

  $.ajax({
    url: 'admin/ajax/dash_interior_get.php',
    method: 'GET',
    dataType: 'json',
    data: { boletin_id: id }
  }).always(function(){
    hideLoading();
  }).done(function(res){
    if(!res || !res.ok){
      if(res?.msg) Swal.fire({ icon:'warning', title:'Error', text: res.msg });
      return;
    }

    PAYLOAD = res;

    const b = res.boletin || {};
    $('#card_boletin_label').text('Boletín No. ' + (b.numero || '') + ' · ' + (b.fecha || ''));
    $('#btnDescargarPDF').show();
    $('#btnActivarBoletin').show().data('id', id).data('activo', b.activo == 1);
    if(b.activo == 1){ $('#btnActivarBoletin').prop('disabled', true).html('<i class="bi bi-star-fill" style="color:#ffc107"></i> Activo'); }
    else { $('#btnActivarBoletin').prop('disabled', false).html('<i class="bi bi-star-fill" style="color:#ffc107"></i> Activar'); }

    if(b.anio_2){
      const selAnio = $('#anio');
      if(!selAnio.find(`option[value="${b.anio_2}"]`).length){
        selAnio.append(`<option value="${b.anio_2}">${b.anio_2}</option>`);
      }
      selAnio.val(b.anio_2);
      $('#anio_label').hide();
      $('#anio_boletin_hint').show();
    }

    const keys = Object.keys(res.datasets || {});
    if(!keys.length){
      $('#card_key').html('<option value="">(Sin gráficos)</option>');
      $('#editor').html('<div class="alert alert-warning">No hay datasets.</div>');
      return;
    }

    let htmlOpts = '';
    keys.forEach(k=>{
      const c = res.datasets[k].card;
      htmlOpts += `<option value="${esc(k)}">${c.card_num}. ${esc(c.titulo)}</option>`;
    });
    $('#card_key').html(htmlOpts);
    $('#card_key').val(keys[0]);
    buildEditor(keys[0]);
  }).fail(function(){
    Swal.fire({ icon:'error', title:'Error de carga', text:'No se pudo cargar el boletín.' });
  });
}

function setActiveBoletin(id){
  showLoading();
  $.ajax({
    url: 'admin/ajax/dash_interior_save.php',
    method: 'POST',
    dataType: 'json',
    data: { action: 'set_active_boletin', id: id }
  }).always(function(){
    hideLoading();
  }).done(function(res){
    if(res && res.ok){
      Swal.fire({ icon:'success', title:'Boletín activo', text:'El boletín se activó correctamente.', timer:1500, showConfirmButton:false });
      loadBoletines(id);
    } else {
      Swal.fire({ icon:'error', title:'Error', text: res?.msg || 'No se pudo activar' });
    }
  }).fail(function(){
    Swal.fire({ icon:'error', title:'Error', text:'Error de conexión al activar boletín' });
  });
}

/* =====================================================
   EDITOR
====================================================== */
function buildEditor(cardKey){
  if(!PAYLOAD || !PAYLOAD.datasets || !PAYLOAD.datasets[cardKey]) {
    $('#card_sub').text('');
    $('#editor').html('<div class="alert alert-warning">No hay datos para el gráfico seleccionado.</div>');
    return;
  }

  const ds = PAYLOAD.datasets[cardKey];
  $('#card_sub').text(ds?.card?.subtitulo || '');

  const cats  = Array.isArray(ds.cats)  ? ds.cats  : [];
  const serie = Array.isArray(ds.serie) ? ds.serie : [];
  const factorAtencion = (typeof ds.factor_atencion === 'string') ? ds.factor_atencion : '';

  let html = `
    <div class="small-muted mb-2">
      Completa los valores para: <b>${esc(ds?.card?.titulo || '')}</b>
    </div>
    <div class="card-pro" style="padding:14px">
  `;

  if(!cats.length){
    html += `<div class="alert alert-info mb-3">Este gráfico no tiene categorías configuradas.</div>`;
  } else {
    cats.forEach((cat, i)=>{
      const val = parseInt((serie[i] ?? 0), 10);
      html += `
        <div class="kv">
          <div><b>${esc(cat)}</b></div>
          <div style="width:180px">
            <input
              type="number"
              class="form-control form-control-sm val"
              data-cat="${esc(cat)}"
              value="${isNaN(val) ? 0 : val}"
            >
          </div>
        </div>
      `;
    });
  }

  html += `
      <div class="mt-4">
        <label class="mb-1"><b>Factor de Atención</b></label>
        <textarea
          class="form-control"
          id="factor_atencion"
          rows="6"
          placeholder="Escribe aquí el texto largo que se verá en el dashboard..."
        >${esc(factorAtencion)}</textarea>
        <div class="small-muted mt-1">
          ${BOLETIN_ACTIVO_ID > 0
            ? 'Se guarda por <b>boletín</b> y por <b>gráfico</b>.'
            : 'Se guarda por <b>año</b> y por <b>gráfico</b>.'}
        </div>
      </div>
    </div>
  `;

  $('#editor').html(html);
}

/* =====================================================
   CARGA DE DATOS
====================================================== */
function loadYearData(){
  const anio = parseInt($('#anio').val() || 2026, 10);

  showLoading();

  return $.ajax({
    url: 'admin/ajax/dash_interior_get.php',
    method: 'GET',
    dataType: 'json',
    data: { anio }
  }).done(function(res){
    if(!res || !res.ok){
      var msgCarga = res?.msg || 'No se pudo cargar';
      if(typeof Swal !== 'undefined'){
        Swal.fire({ icon:'warning', title:'Sin permiso o error', text: msgCarga, confirmButtonText:'Ok' });
      } else {
        alert(msgCarga);
      }
      return;
    }

    PAYLOAD = res;

    const keys = Object.keys(res.datasets || {});
    if(!keys.length){
      $('#card_key').html('<option value="">(Sin gráficos)</option>');
      $('#editor').html('<div class="alert alert-warning">No hay datasets.</div>');
      return;
    }

    let htmlOpts = '';
    keys.forEach(k=>{
      const c = res.datasets[k].card;
      htmlOpts += `<option value="${esc(k)}">${c.card_num}. ${esc(c.titulo)}</option>`;
    });

    $('#card_key').html(htmlOpts);
    $('#card_key').val(keys[0]);
    buildEditor(keys[0]);

  }).fail(function(xhr){
    if(typeof Swal !== 'undefined'){
      Swal.fire({ icon:'warning', title:'Error de Carga', text:'No se pudieron cargar los datos.', confirmButtonText:'Reintentar' });
    } else {
      alert('No se pudieron cargar los datos. Revisa la consola (F12).');
    }
  }).always(function(){
    hideLoading();
  });
}

/* =====================================================
   GUARDAR
====================================================== */
function saveValues(){
  const anio = parseInt($('#anio').val() || 2026, 10);
  const cardKey = $('#card_key').val();
  const boletinId = BOLETIN_ACTIVO_ID;

  if(!cardKey){
    if(typeof Swal !== 'undefined'){
      Swal.fire({ icon:'warning', title:'Selecciona un gráfico', text:'Debes elegir un gráfico antes de guardar.', confirmButtonText:'Ok' });
    } else {
      alert('Debes elegir un gráfico antes de guardar.');
    }
    return;
  }

  const values = {};
  $('.val').each(function(){
    const k = $(this).data('cat');
    values[k] = parseInt($(this).val() || 0, 10);
  });

  const factorAtencion = ($('#factor_atencion').length ? $('#factor_atencion').val() : '').trim();

  showLoading();

  const data = {
    action: 'save_boletin',
    card_key: cardKey,
    values: JSON.stringify(values),
    factor_atencion: factorAtencion
  };

  if(boletinId > 0){
    data.boletin_id = boletinId;
  } else {
    data.anio = anio;
  }

  $.ajax({
    url: 'admin/ajax/dash_interior_save.php',
    method: 'POST',
    dataType: 'json',
    data: data
  }).done(function(res){
    if(typeof res === 'string'){
      try { res = JSON.parse(res); } catch(e) { /* ignore */ }
    }

    if(!res || !res.ok){
      var msg = res?.msg || 'No se pudo guardar la información.';
      if(typeof Swal !== 'undefined'){
        Swal.fire({ icon:'error', title:'Error al guardar', text: msg, confirmButtonText:'Cerrar' });
      } else {
        alert('Error al guardar: ' + msg);
      }
      return;
    }

    if(typeof Swal !== 'undefined'){
      Swal.fire({
        icon: 'success',
        title: 'Datos guardados',
        text: 'La información se actualizó correctamente.',
        confirmButtonText: 'Continuar'
      }).then(() => {
        if(boletinId > 0){
          seleccionarBoletin(boletinId);
        } else {
          loadYearData().then(() => {
            $('#card_key').val(cardKey);
            buildEditor(cardKey);
          });
        }
      });
    } else {
      alert('Datos guardados correctamente.');
      if(boletinId > 0){
        seleccionarBoletin(boletinId);
      } else {
        loadYearData().then(() => {
          $('#card_key').val(cardKey);
          buildEditor(cardKey);
        });
      }
    }

  }).fail(function(xhr){
    if(typeof Swal !== 'undefined'){
      Swal.fire({
        icon: 'error',
        title: 'Error de servidor',
        text: 'No se pudo guardar. Revisa Network/Console.',
        confirmButtonText: 'Cerrar'
      });
    } else {
      alert('Error de servidor al guardar. Revisa la consola (F12).');
    }

  }).always(function(){
    hideLoading();
  });
}

/* =====================================================
   META — Configurar tbl_dash_interior_meta
====================================================== */
function loadMeta(){
  const bid = BOLETIN_ACTIVO_ID;
  const url = bid > 0 ? 'admin/ajax/dash_interior_meta.php?boletin_id=' + bid : 'admin/ajax/dash_interior_meta.php';

  if(bid > 0){
    $('#meta_fecha_boletin_group').show();
  } else {
    $('#meta_fecha_boletin_group').hide();
  }

  $.ajax({
    url: url,
    method: 'GET',
    dataType: 'json'
  }).done(function(res){
    if(!res || !res.ok){ return; }
    const m = res.meta || {};
    $('#meta_anio_1').val(m.anio_1 || '');
    $('#meta_anio_2').val(m.anio_2 || '');
    $('#meta_boletin_no').val(m.boletin_no || '');
    $('#meta_fecha_cierre').val(m.fecha_cierre || '');
    $('#meta_fecha_boletin').val(m.fecha || '');
    $('#meta_fuente').val(m.fuente || '');
    $('#meta_tasa_homicidios').val(m.tasa_homicidios || '');
    $('#meta_municipios_sin_homicidios').val(m.municipios_sin_homicidios || 0);
    $('#meta_nota_html').val(m.nota_html || '');
  }).fail(function(){
    if(typeof Swal !== 'undefined'){
      Swal.fire({ icon:'error', title:'Error', text:'No se pudo cargar la meta.', confirmButtonText:'Cerrar' });
    } else {
      alert('No se pudo cargar la meta.');
    }
  });
}

function saveMeta(){
  const bid = BOLETIN_ACTIVO_ID;
  const data = {
    boletin_id:               bid > 0 ? bid : 0,
    anio_1:                    parseInt($('#meta_anio_1').val() || 2025, 10),
    anio_2:                    parseInt($('#meta_anio_2').val() || 2026, 10),
    boletin_no:                $('#meta_boletin_no').val().trim() || '',
    fecha_cierre:              $('#meta_fecha_cierre').val(),
    fecha:                     bid > 0 ? $('#meta_fecha_boletin').val() : '',
    fuente:                    $('#meta_fuente').val().trim(),
    tasa_homicidios:           $('#meta_tasa_homicidios').val().trim(),
    municipios_sin_homicidios: parseInt($('#meta_municipios_sin_homicidios').val() || 0, 10),
    nota_html:                 $('#meta_nota_html').val().trim()
  };

  $('#btnGuardarMeta').prop('disabled', true);

  $.ajax({
    url:      'admin/ajax/dash_interior_meta.php',
    method:   'POST',
    dataType: 'json',
    data:     data
  }).done(function(res){
    if(!res || !res.ok){
      var msgMeta = res?.msg || 'No se pudo guardar.';
      if(typeof Swal !== 'undefined'){
        Swal.fire({ icon:'error', title:'Error', text: msgMeta, confirmButtonText:'Cerrar' });
      } else {
        alert('Error: ' + msgMeta);
      }
      return;
    }
    $('#modalMeta').modal('hide');
    if(bid > 0){
      const nuevoNum = $('#meta_boletin_no').val().trim();
      const nuevaFecha = $('#meta_fecha_boletin').val();
      const opt = $(`#boletin_select option[value="${bid}"]`);
      if(opt.length){
        const star = opt.data('activo') ? '★ ' : '';
        opt.text(`${star}No. ${nuevoNum} - ${nuevaFecha}`);
      }
      $('#card_boletin_label').text(`Boletín No. ${nuevoNum} · ${nuevaFecha}`);
    }
    if(typeof Swal !== 'undefined'){
      Swal.fire({ icon:'success', title:'Meta guardada', text:'La configuración se actualizó correctamente.', confirmButtonText:'Ok' });
    } else {
      alert('Meta guardada correctamente.');
    }
  }).fail(function(){
    if(typeof Swal !== 'undefined'){
      Swal.fire({ icon:'error', title:'Error de servidor', text:'No se pudo guardar la meta.', confirmButtonText:'Cerrar' });
    } else {
      alert('Error de servidor al guardar la meta.');
    }
  }).always(function(){
    $('#btnGuardarMeta').prop('disabled', false);
  });
}

/* =====================================================
   INIT
====================================================== */
$(function(){
  hideLoading();

  loadBoletines();

  $('#btnCargar').on('click', function(){
    if(BOLETIN_ACTIVO_ID > 0){
      seleccionarBoletin(BOLETIN_ACTIVO_ID);
    } else {
      loadYearData();
    }
  });

  $('#card_key').on('change', function(){
    buildEditor($(this).val());
  });

  $('#btnGuardar').on('click', function(){
    saveValues();
  });

  $('#boletin_select').on('change', function(){
    seleccionarBoletin(parseInt($(this).val() || 0, 10));
  });

  $('#anio').on('change', function(){
    if(BOLETIN_ACTIVO_ID > 0){
      $('#boletin_select').val('');
      BOLETIN_ACTIVO_ID = 0;
    }
    loadYearData();
  });

  $('#btnDescargarPDF').on('click', function(){
    const id = BOLETIN_ACTIVO_ID || 0;
    $(this).attr('href', 'admin/ajax/dash_interior_pdf.php' + (id > 0 ? '?boletin_id=' + id : ''));
  });

  $('#btnNuevoBoletin').on('click', function(){
    crearBoletin();
  });

  $(document).on('click', '#btnActivarBoletin', function(){
    const id = $(this).data('id');
    if(!id) return;
    Swal.fire({
      title: '¿Activar este boletín?',
      text: 'El dashboard mostrará este boletín como predeterminado.',
      icon: 'question',
      showCancelButton: true,
      confirmButtonText: 'Sí, activar',
      cancelButtonText: 'Cancelar'
    }).then(function(r){
      if(r.isConfirmed) setActiveBoletin(id);
    });
  });

  $('#modalMeta').on('show.bs.modal', function(){
    if(BOLETIN_ACTIVO_ID > 0){
      $('#modalMetaTitle').text('Meta del Boletín No. ' + $('#boletin_select option:selected').text().replace(/^[★ ]*/, ''));
    } else {
      $('#modalMetaTitle').text('Configurar Meta Global');
    }
    loadMeta();
  });

  $(document).on('click', '#btnAbrirMeta', function(){
    $('#modalMeta').modal('show');
  });

  $(document).on('click', '#btnGuardarMeta', function(){
    saveMeta();
  });
});
