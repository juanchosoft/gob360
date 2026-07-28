$(document).ready(function () {
    $('#proyecto_id').on('change', function () {
        $(this).closest('form').submit();
    });

    $('.js-open-project-modal').on('click', function () {
        if ($.fn.modal) {
            $('#modalActualizarProyecto').modal('show');
        } else {
            $('#modalActualizarProyecto').addClass('show').css('display', 'block').attr('aria-hidden', 'false');
            $('body').addClass('modal-open').append('<div class="modal-backdrop fade show"></div>');
        }
    });

    $('[data-dismiss="modal"], .se-modal-close').on('click', function () {
        if ($.fn.modal) {
            $('#modalActualizarProyecto').modal('hide');
        } else {
            $('#modalActualizarProyecto').removeClass('show').css('display', 'none').attr('aria-hidden', 'true');
            $('body').removeClass('modal-open');
            $('.modal-backdrop').remove();
        }
    });

    $('.money-field').on('input', function () {
        let value = this.value.replace(/\D/g, '');
        if (value === '') { this.value = ''; return; }
        this.value = new Intl.NumberFormat('es-CO').format(value);
    });

    $('.decimal-field').on('input', function () {
        this.value = this.value.replace(/[^0-9,.]/g, '');
        let value = this.value.replace(',', '.');
        let number = parseFloat(value);
        if (!isNaN(number) && number > 100) { this.value = '100'; }
    });

    /* ---- Slider de avances ---- */
    function initSlider() {
        var track = document.getElementById('galeriaTrack');
        if (!track) return;
        var dots = [].slice.call(document.querySelectorAll('.se-galeria-dot'));
        var prevBtn = document.getElementById('galeriaPrev');
        var nextBtn = document.getElementById('galeriaNext');
        var idxLabel = document.getElementById('galeriaIdx');
        var total = dots.length;
        var current = 0;
        var autoplayTimer = null;

        if (total < 2) {
            if (prevBtn) prevBtn.style.display = 'none';
            if (nextBtn) nextBtn.style.display = 'none';
            var dotsContainer = document.getElementById('galeriaDots');
            if (dotsContainer) dotsContainer.style.display = 'none';
            return;
        }

        function goTo(index) {
            if (index < 0) index = total - 1;
            if (index >= total) index = 0;
            current = index;
            track.style.transform = 'translateX(-' + (current * 100) + '%)';
            idxLabel.textContent = current + 1;
            dots.forEach(function (d, i) {
                d.classList.toggle('active', i === current);
            });
        }

        function startAutoplay() {
            autoplayTimer = setInterval(function () { goTo(current + 1); }, 4000);
        }
        function stopAutoplay() { clearInterval(autoplayTimer); }
        function restartAutoplay() { stopAutoplay(); startAutoplay(); }

        dots.forEach(function (dot) {
            dot.addEventListener('click', function () {
                goTo(parseInt(this.getAttribute('data-slide')));
                restartAutoplay();
            });
        });

        prevBtn.addEventListener('click', function () { goTo(current - 1); restartAutoplay(); });
        nextBtn.addEventListener('click', function () { goTo(current + 1); restartAutoplay(); });

        var sliderEl = track.closest('.se-galeria-slider');
        sliderEl.addEventListener('mouseenter', stopAutoplay);
        sliderEl.addEventListener('mouseleave', startAutoplay);
        sliderEl.addEventListener('touchstart', stopAutoplay);
        sliderEl.addEventListener('touchend', startAutoplay);

        startAutoplay();
    }
    initSlider();

    /* ---- Upload imagen principal ---- */
    (function () {
        var fileInput = document.getElementById('inputImgPrincipal');
        var preview = document.getElementById('imgPrincipalPreview');
        var fileName = document.getElementById('imgPrincipalFileName');
        var clearBtn = document.getElementById('btnClearPrincipalImg');
        var hiddenUrl = document.getElementById('inputImgUrl');
        var manualInput = document.getElementById('inputImgUrlManual');

        if (!fileInput) return;

        document.getElementById('imgPrincipalArea').addEventListener('click', function () { fileInput.click(); });

        fileInput.addEventListener('change', function () {
            var file = this.files[0];
            if (!file) return;
            fileName.textContent = file.name;
            clearBtn.style.display = 'inline-flex';
            var reader = new FileReader();
            reader.onload = function (e) {
                preview.innerHTML = '<img src="' + e.target.result + '" alt=""><div class="se-img-upload-overlay"><span>🖱️ Cambiar imagen</span></div>';
            };
            reader.readAsDataURL(file);
            hiddenUrl.value = '';
            if (manualInput) manualInput.value = '';
        });

        clearBtn.addEventListener('click', function (e) {
            e.stopPropagation();
            fileInput.value = '';
            hiddenUrl.value = '';
            if (manualInput) manualInput.value = '';
            preview.innerHTML = '<div class="se-img-upload-empty"><span class="se-img-upload-icon">🖼️</span><span class="se-img-upload-text">Haz clic para subir imagen</span><span class="se-img-upload-hint">JPG, PNG, GIF, WebP • Máx 10MB</span></div>';
            fileName.textContent = '';
            clearBtn.style.display = 'none';
        });

        if (manualInput) {
            manualInput.addEventListener('input', function () {
                hiddenUrl.value = this.value;
                if (this.value) {
                    fileInput.value = '';
                    preview.innerHTML = '<img src="' + this.value + '" alt="" onerror="this.parentElement.innerHTML=\'<div class=se-img-upload-empty><span class=se-img-upload-icon>🖼️</span><span class=se-img-upload-text>Vista previa no disponible</span></div>\'"><div class="se-img-upload-overlay"><span>🖱️ Cambiar imagen</span></div>';
                    fileName.textContent = '';
                    clearBtn.style.display = 'inline-flex';
                }
            });
        }
    })();

    /* ---- Mostrar nombre del archivo seleccionado ---- */
    document.getElementById('inputAvanceImg').addEventListener('change', function () {
        document.getElementById('avanceImgName').textContent = this.files[0] ? this.files[0].name : 'Ninguno';
    });

    /* ---- Subir imagen de avance ---- */
    $('#btnSubirAvance').on('click', function () {
        var fileInput = document.getElementById('inputAvanceImg');
        var fecha = $('#inputAvanceFecha').val();
        var file = fileInput.files[0];

        if (!file) {
            Swal.fire({ icon: 'warning', title: 'Selecciona una imagen', confirmButtonColor: '#315fc0' });
            return;
        }

        var formData = new FormData();
        formData.append('archivo', file);
        formData.append('proyecto_id', window.proyectoId);
        formData.append('fecha_avance', fecha);

        $('#btnSubirAvance').prop('disabled', true).text('Subiendo…');

        $.ajax({
            url: 'admin/ajax/imagen_avance.php',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            dataType: 'json',
            success: function (res) {
                if (res.state) {
                    Swal.fire({ icon: 'success', title: 'Imagen subida', timer: 1500, showConfirmButton: false });
                    var item = $('<div class="se-galeria-mini-item" data-id="' + res.data.id + '">' +
                        '<img src="' + res.data.imagen_url + '" alt="">' +
                        '<span class="se-galeria-mini-fecha">' + (res.data.fecha_avance || 'Sin fecha') + '</span>' +
                        '<button type="button" class="se-galeria-mini-del" data-id="' + res.data.id + '" title="Eliminar">×</button>' +
                    '</div>');
                    $('#seGaleriaActual').find('p').remove();
                    $('#seGaleriaActual').append(item);
                    fileInput.value = '';
                    $('#inputAvanceFecha').val('');
                } else {
                    Swal.fire({ icon: 'error', title: 'Error', text: res.message });
                }
            },
            error: function () {
                Swal.fire({ icon: 'error', title: 'Error de conexión' });
            },
            complete: function () {
                $('#btnSubirAvance').prop('disabled', false).text('Subir');
            }
        });
    });

    /* ---- Eliminar imagen de avance ---- */
    $(document).on('click', '.se-galeria-mini-del', function () {
        var id = $(this).data('id');
        var $item = $(this).closest('.se-galeria-mini-item');

        Swal.fire({
            title: '¿Eliminar imagen?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#e44c61',
            confirmButtonText: 'Eliminar',
            cancelButtonText: 'Cancelar'
        }).then(function (result) {
            if (result.isConfirmed) {
                $.ajax({
                    url: 'admin/ajax/imagen_avance.php',
                    type: 'POST',
                    data: { action: 'delete', id: id },
                    dataType: 'json',
                    success: function (res) {
                        if (res.state) {
                            $item.remove();
                            Swal.fire({ icon: 'success', title: 'Eliminada', timer: 1200, showConfirmButton: false });
                        }
                    }
                });
            }
        });
    });
});
