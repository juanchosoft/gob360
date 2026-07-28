var DEPORTISTA = (function () {
    'use strict';

    var modulo = {};

    modulo.config = {
        formId: '#formdeportista',
        url: './admin/classes/Deportistas.php',
        opCreate: 'save',
        allowedImageTypes: ['image/jpeg', 'image/png', 'image/jpg', 'image/webp']
    };

    modulo.validateData = function () {
        var errores = [];
        var $form = $(modulo.config.formId);

        if (!$form.length) {
            modulo.showError('No se encontró el formulario.');
            return false;
        }

        var tipoDocumento  = ($form.find('[name="tipo_documento"]').val() || '').trim();
        var cc             = ($form.find('[name="cc"]').val() || '').trim();
        var nombre         = ($form.find('[name="nombre"]').val() || '').trim();
        var disciplina     = ($form.find('[name="tbl_disciplina_id"]').val() || '').trim();
        var contacto            = ($form.find('[name="contacto"]').val() || '').trim();
        var nacimiento     = ($form.find('[name="nacimiento"]').val() || '').trim();
        var liga           = ($form.find('[name="tbl_liga_id"]').val() || '').trim();
        var valor          = ($form.find('[name="valor"]').val() || '').trim();
        var plazo          = ($form.find('[name="plazo"]').val() || '').trim();
        var tipoDeportista = ($form.find('[name="tipo_deportista"]').val() || '').trim();
        var inputImg       = $form.find('[name="img"]')[0];

        console.log('DEBUG tipo_documento:', tipoDocumento);
        console.log('DEBUG cc:', cc);
        console.log('DEBUG nombre:', nombre);
        console.log('DEBUG disciplina:', disciplina);
        console.log('DEBUG contacto:', contacto);
        console.log('DEBUG nacimiento:', nacimiento);
        console.log('DEBUG liga:', liga);
        console.log('DEBUG valor:', valor);
        console.log('DEBUG plazo:', plazo);
        console.log('DEBUG tipo_deportista:', tipoDeportista);

        if (tipoDocumento === '') {
            errores.push('Debe seleccionar el tipo de documento.');
        }

        if (cc === '') {
            errores.push('Debe ingresar la cédula.');
        } else if (!/^\d{5,20}$/.test(cc)) {
            errores.push('La cédula debe contener solo números válidos.');
        }

        if (nombre === '') {
            errores.push('Debe ingresar los nombres completos.');
        }

        if (disciplina === '') {
            errores.push('Debe seleccionar la disciplina.');
        }

        if (contacto === '') {
            errores.push('Debe ingresar el número de contacto.');
        } else if (!/^\d{7,20}$/.test(contacto)) {
            errores.push('El número de contacto debe contener solo números válidos.');
        }

        if (nacimiento === '') {
            errores.push('Debe seleccionar la fecha de nacimiento.');
        }

        if (liga === '') {
            errores.push('Debe seleccionar la liga.');
        }

        if (valor === '') {
            errores.push('Debe ingresar el valor.');
        } else if (!/^\d+$/.test(valor)) {
            errores.push('El valor debe ser numérico.');
        }

        if (plazo === '') {
            errores.push('Debe ingresar el plazo.');
        } else if (!/^\d+$/.test(plazo)) {
            errores.push('El plazo debe ser numérico.');
        }

        if (tipoDeportista === '') {
            errores.push('Debe seleccionar el tipo de deportista.');
        }

        if (inputImg && inputImg.files && inputImg.files.length > 0) {
            var archivo = inputImg.files[0];

            if (modulo.config.allowedImageTypes.indexOf(archivo.type) === -1) {
                errores.push('La imagen debe ser JPG, JPEG, PNG o WEBP.');
            }

            if (archivo.size > 5 * 1024 * 1024) {
                errores.push('La imagen no debe superar los 5 MB.');
            }
        }

        if (errores.length > 0) {
            modulo.showError(errores.join('\n'));
            return false;
        }

        modulo.save();
        return true;
    };

    modulo.save = function () {
        var form = document.querySelector(modulo.config.formId);

        if (!form) {
            modulo.showError('No se encontró el formulario.');
            return;
        }

        var formData = new FormData(form);

        if (!formData.get('op') || formData.get('op') === '') {
            formData.set('op', modulo.config.opCreate);
        }

        formData.set('valor', ($('#valor').val() || '').trim());

        $.ajax({
            url: modulo.config.url,
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            cache: false,
            dataType: 'json',
            beforeSend: function () {
                modulo.blockButton(true);
            },
            success: function (resp) {
                console.log('Respuesta guardar deportista:', resp);

                if (!resp) {
                    modulo.showError('El servidor no devolvió respuesta.');
                    return;
                }

                if (resp.ok === true || resp.status === true || resp.success === true || resp.code === 200) {
                    modulo.showSuccess(resp.msg || 'Deportista guardado correctamente.');
                    modulo.resetForm();

                    if (typeof tablaDeportistas !== 'undefined' && tablaDeportistas) {
                        tablaDeportistas.ajax.reload(null, false);
                    }

                    if (typeof abrirTab === 'function') {
                        abrirTab('profile');
                    }

                    return;
                }

                modulo.showError(resp.msg || 'No fue posible guardar el deportista.');
            },
            error: function (xhr, textStatus, errorThrown) {
                console.error('Error al guardar deportista');
                console.error('status:', xhr.status);
                console.error('textStatus:', textStatus);
                console.error('errorThrown:', errorThrown);
                console.error('responseText:', xhr.responseText);

                var mensaje = 'Ocurrió un error al guardar la información.';

                if (xhr.responseJSON && xhr.responseJSON.msg) {
                    mensaje = xhr.responseJSON.msg;
                } else if (xhr.responseText) {
                    mensaje = xhr.responseText;
                    try {
                        var obj = JSON.parse(xhr.responseText);
                        if (obj.msg) {
                            mensaje = obj.msg;
                        }
                    } catch (e) {}
                }

                modulo.showError(mensaje);
            },
            complete: function () {
                modulo.blockButton(false);
            }
        });
    };

    modulo.resetForm = function () {
        var form = document.querySelector(modulo.config.formId);

        if (form) {
            form.reset();
        }

        $('#id').val('');
        $('#op').val('');
        $('#valor').val('');
        $('#valor_view').val('');
        $('#previewImage').html('');
    };

    modulo.blockButton = function (estado) {
        $('#createUser').prop('disabled', estado);
        $('#createUser').text(estado ? 'Guardando...' : 'Guardar');
    };

    modulo.showSuccess = function (mensaje) {
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                icon: 'success',
                title: 'Proceso exitoso',
                text: mensaje,
                confirmButtonText: 'Aceptar'
            });
        } else {
            alert(mensaje);
        }
    };

    modulo.showError = function (mensaje) {
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                icon: 'error',
                title: 'Atención',
                text: mensaje,
                confirmButtonText: 'Aceptar'
            });
        } else {
            alert(mensaje);
        }
    };

    modulo.previewImage = function () {
        var input = document.getElementById('img');
        var preview = document.getElementById('previewImage');

        if (!input || !preview) return;

        preview.innerHTML = '';

        if (input.files && input.files[0]) {
            var file = input.files[0];

            if (modulo.config.allowedImageTypes.indexOf(file.type) === -1) {
                modulo.showError('La imagen debe ser JPG, JPEG, PNG o WEBP.');
                input.value = '';
                return;
            }

            var reader = new FileReader();
            reader.onload = function (e) {
                preview.innerHTML = '<img src="' + e.target.result + '" style="max-width:180px; max-height:180px; border-radius:14px; border:1px solid rgba(255,255,255,.15); object-fit:cover;">';
            };
            reader.readAsDataURL(file);
        }
    };

    modulo.onlyNumbers = function () {
        $('#cc, #contacto, #plazo').on('input', function () {
            this.value = this.value.replace(/\D/g, '');
        });
    };

    modulo.init = function () {
        modulo.onlyNumbers();

        $('#img').off('change').on('change', function () {
            modulo.previewImage();
        });
    };

    return modulo;
})();

$(document).ready(function () {
    USUARIO.init();
});