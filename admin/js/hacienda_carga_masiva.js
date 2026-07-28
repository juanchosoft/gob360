(function () {
  var selectAccion    = document.getElementById('selectAccion');
  var btnDescarga     = document.getElementById('btnDescarga');
  var wrapPlantilla   = document.getElementById('wrapPlantilla');
  var wrapUpload      = document.getElementById('wrapUpload');
  var dropZone        = document.getElementById('dropZone');
  var fileInput       = document.getElementById('fileInput');
  var dzFilename      = document.getElementById('dzFilename');
  var btnSubir        = document.getElementById('btnSubir');
  var btnSubirText    = document.getElementById('btnSubirText');
  var btnSubirSpinner = document.getElementById('btnSubirSpinner');
  var cardResultados  = document.getElementById('cardResultados');
  var cardVacio       = document.getElementById('cardVacio');
  var summaryWrap     = document.getElementById('summaryWrap');
  var erroresWrap     = document.getElementById('erroresWrap');
  var tbodyErrores    = document.getElementById('tbodyErrores');
  var sinErrores      = document.getElementById('sinErrores');

  var archivoSeleccionado = null;

  selectAccion.addEventListener('change', function () {
    var accion = this.value;
    if (!accion) {
      wrapPlantilla.style.display = 'none';
      wrapUpload.style.display    = 'none';
      btnSubir.disabled           = true;
      return;
    }
    btnDescarga.href            = 'admin/controllers/haciendaPlantillaCtrl.php?accion=' + encodeURIComponent(accion);
    wrapPlantilla.style.display = 'block';
    wrapUpload.style.display    = 'block';
    resetArchivo();
  });

  dropZone.addEventListener('click', function () { fileInput.click(); });

  ['dragenter', 'dragover'].forEach(function (evt) {
    dropZone.addEventListener(evt, function (e) {
      e.preventDefault();
      dropZone.classList.add('drag-over');
    });
  });

  ['dragleave', 'drop'].forEach(function (evt) {
    dropZone.addEventListener(evt, function (e) {
      e.preventDefault();
      dropZone.classList.remove('drag-over');
    });
  });

  dropZone.addEventListener('drop', function (e) {
    var file = e.dataTransfer.files[0];
    if (file) setArchivo(file);
  });

  fileInput.addEventListener('change', function () {
    if (this.files[0]) setArchivo(this.files[0]);
  });

  function setArchivo(file) {
    if (!file.name.endsWith('.xlsx')) {
      Swal.fire({ icon: 'error', title: 'Formato inválido', text: 'Solo se permiten archivos .xlsx' });
      return;
    }
    archivoSeleccionado  = file;
    dzFilename.textContent = '📄 ' + file.name;
    btnSubir.disabled    = (selectAccion.value === '');
  }

  function resetArchivo() {
    archivoSeleccionado    = null;
    fileInput.value        = '';
    dzFilename.textContent = '';
    btnSubir.disabled      = true;
  }

  btnSubir.addEventListener('click', function () {
    if (!selectAccion.value || !archivoSeleccionado) return;

    var formData = new FormData();
    formData.append('excelFile', archivoSeleccionado);
    formData.append('accion', selectAccion.value);

    btnSubirText.style.display    = 'none';
    btnSubirSpinner.style.display = '';
    btnSubir.disabled             = true;

    fetch('admin/ajax/hacienda_import.php', { method: 'POST', body: formData })
      .then(function (r) { return r.json(); })
      .then(function (data) { mostrarResultados(data); })
      .catch(function () {
        Swal.fire({ icon: 'error', title: 'Error de red', text: 'No se pudo conectar con el servidor.' });
      })
      .finally(function () {
        btnSubirText.style.display    = '';
        btnSubirSpinner.style.display = 'none';
        btnSubir.disabled             = false;
      });
  });

  function mostrarResultados(data) {
    cardVacio.style.display       = 'none';
    cardResultados.style.display  = 'block';
    summaryWrap.innerHTML         = '';
    tbodyErrores.innerHTML        = '';
    erroresWrap.style.display     = 'none';
    sinErrores.style.display      = 'none';

    if (!data.valid) {
      summaryWrap.innerHTML = '<div class="summary-box err w-100">' +
        '<i class="feather icon-alert-circle" style="font-size:28px;color:#dc2626;"></i>' +
        '<div><div class="sb-num" style="color:#dc2626;">Error</div>' +
        '<div class="sb-label">' + escapeHtml(data.message || 'Error desconocido') + '</div></div></div>';
      return;
    }

    var inserted = data.inserted || 0;
    var errors   = data.errors   || [];

    summaryWrap.innerHTML =
      '<div class="summary-box ok mr-2"><div>' +
        '<div class="sb-num" style="color:#065f46;">' + inserted + '</div>' +
        '<div class="sb-label">Registros insertados</div>' +
      '</div></div>' +
      '<div class="summary-box ' + (errors.length > 0 ? 'err' : 'ok') + '"><div>' +
        '<div class="sb-num" style="color:' + (errors.length > 0 ? '#dc2626' : '#065f46') + ';">' + errors.length + '</div>' +
        '<div class="sb-label">Filas con error</div>' +
      '</div></div>';

    if (errors.length > 0) {
      erroresWrap.style.display = 'block';
      errors.forEach(function (err) {
        var tr = document.createElement('tr');
        tr.innerHTML = '<td class="text-center font-weight-bold">' + escapeHtml(String(err.fila)) + '</td>' +
                       '<td>' + escapeHtml(err.mensaje) + '</td>';
        tbodyErrores.appendChild(tr);
      });
    }

    if (errors.length === 0 && inserted > 0) {
      sinErrores.style.display = 'block';
    }

    if (inserted > 0) {
      Swal.fire({
        icon: 'success',
        title: '¡Carga completada!',
        text: 'Se insertaron ' + inserted + ' registro(s). ' + (errors.length > 0 ? errors.length + ' fila(s) con error.' : ''),
        timer: 4000,
        showConfirmButton: false,
      });
    }
  }

  function escapeHtml(str) {
    return String(str)
      .replace(/&/g, '&amp;')
      .replace(/</g, '&lt;')
      .replace(/>/g, '&gt;')
      .replace(/"/g, '&quot;');
  }
})();
