// public/js/configuracion.js

$(document).ready(function() {
    const CONTROLLER = '../app/controllers/ConfiguracionController.php';

    function normalizePublicPathMaybe(pathOrName, defaultPrefix) {
        if (!pathOrName) return null;
        pathOrName = String(pathOrName).trim();
        if (/^(https?:)?\/\//i.test(pathOrName)) return pathOrName;
        if (pathOrName.indexOf('/') !== -1) {
            if (pathOrName.indexOf('public/') === 0) return '../' + pathOrName;
            return (pathOrName.indexOf('..') === 0) ? pathOrName : ('../' + pathOrName);
        }
        if (defaultPrefix) return defaultPrefix + pathOrName;
        return pathOrName;
    }

    function cargarConfiguracion() {
        $.ajax({
            url: CONTROLLER,
            type: 'GET',
            dataType: 'json',
            success: function(config) {
                if (!config) return;

                $('#nombre_clinica').val(config.nombre_clinica || '');
                $('#direccion').val(config.direccion || '');
                $('#telefono').val(config.telefono || '');
                $('#email').val(config.email || '');
                $('#rtn').val(config.rtn || '');
                
                // --- MODIFICACIÓN: Cargar zona horaria ---
                if (config.zona_horaria) {
                    $('#zona_horaria').val(config.zona_horaria);
                }
                // -----------------------------------------

                if (config.theme_mode) {
                    $(`input[name="theme_mode"][value="${config.theme_mode}"]`).prop('checked', true);
                }
                if (typeof config.background_color !== 'undefined' && config.background_color !== null) {
                    $('#background_color').val(config.background_color);
                }
                if (typeof config.navbar_color !== 'undefined' && config.navbar_color !== null) {
                    $('#navbar_color').val(config.navbar_color);
                }
                $('#navbar_sticky').prop('checked', config.navbar_sticky == '1' || config.navbar_sticky === 1);

                if (config.logo) {
                    var logoSrc = normalizePublicPathMaybe(config.logo, '../public/uploads/logos/');
                    $('#logo_preview_container').html(`<img src="${logoSrc}" class="img-thumbnail" width="150">`);
                } else {
                    $('#logo_preview_container').empty();
                }

                if (config.login_background_color) {
                    $('#login_background_color').val(config.login_background_color);
                }

                if (config.login_background_image) {
                    var loginImgSrc = normalizePublicPathMaybe(config.login_background_image, '../public/uploads/');
                    var $lp = $('#login_bg_preview');
                    if ($lp.length) {
                        $lp.attr('data-src', loginImgSrc);
                        $lp.attr('src', loginImgSrc);
                        $lp.show();
                    }
                } else {
                    var $lp = $('#login_bg_preview');
                    if ($lp.length) {
                        $lp.attr('src', '');
                        $lp.hide();
                    }
                }
            },
            error: function() {
                console.error("No se pudo cargar la configuración.");
            }
        });
    }

    cargarConfiguracion();

    $('#logo').on('change', function() {
        if (this.files && this.files[0]) {
            var reader = new FileReader();
            reader.onload = function(e) {
                $('#logo_preview_container').html(`<img src="${e.target.result}" class="img-thumbnail" width="150">`);
            };
            reader.readAsDataURL(this.files[0]);
        }
    });

    $('#login_background_image').on('change', function() {
        if (this.files && this.files[0]) {
            var reader = new FileReader();
            reader.onload = function(e) {
                var $lp = $('#login_bg_preview');
                if ($lp.length) {
                    $lp.attr('src', e.target.result);
                    $lp.show();
                }
            };
            reader.readAsDataURL(this.files[0]);
        }
    });

    // Submit de configuración general
    $('#formConfiguracion').submit(function(e) {
        e.preventDefault();
        var formData = new FormData(this);
        
        if (!$('#navbar_sticky').is(':checked')) {
            formData.set('navbar_sticky', '0');
        } else {
            formData.set('navbar_sticky', '1');
        }

        $.ajax({
            url: CONTROLLER,
            type: 'POST',
            data: formData,
            contentType: false,
            processData: false,
            dataType: 'json',
            success: function(response) {
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        icon: 'success',
                        title: '¡Guardado!',
                        text: response.message,
                        timer: 1500,
                        showConfirmButton: false
                    }).then(() => {
                        location.reload();
                    });
                } else {
                    alert(response.message || 'Configuración guardada.');
                    location.reload();
                }
            },
            error: function(jqXHR) {
                var msg = 'Error guardando configuración.';
                try {
                    if (jqXHR.responseJSON && jqXHR.responseJSON.message) msg = jqXHR.responseJSON.message;
                } catch (err) {}
                if (typeof Swal !== 'undefined') {
                    Swal.fire('Error', msg, 'error');
                } else {
                    alert(msg);
                }
            }
        });
    });

    // --- NUEVO: LÓGICA DE RESPALDO Y RESTAURACIÓN ---

    // 1. Generar Backup
    $('#btnGenerarBackup').click(function() {
        Swal.fire({
            title: 'Generando Respaldo',
            text: 'Preparando archivo SQL, por favor espere...',
            icon: 'info',
            allowOutsideClick: false,
            showConfirmButton: false,
            willOpen: () => {
                Swal.showLoading();
                // Redirigir al controlador para forzar la descarga
                window.location.href = CONTROLLER + '?action=backup_db';
                
                // Cerramos la alerta después de unos segundos
                setTimeout(() => {
                    Swal.close();
                }, 2000);
            }
        });
    });

    // 2. Restaurar Backup
    $('#formRestaurarDB').submit(function(e) {
        e.preventDefault();
        
        Swal.fire({
            title: '¿Estás absolutamente seguro?',
            text: "¡Esta acción ELIMINARÁ todos los datos actuales y los reemplazará con los del archivo! No se puede deshacer.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Sí, restaurar base de datos',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.fire({
                    title: 'Última advertencia',
                    text: "Confirme nuevamente que desea sobrescribir la base de datos.",
                    icon: 'error',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    confirmButtonText: '¡HACERLO!',
                    focusCancel: true
                }).then((resultFinal) => {
                    if (resultFinal.isConfirmed) {
                        ejecutarRestauracion();
                    }
                });
            }
        });
    });

    function ejecutarRestauracion() {
        let formData = new FormData($('#formRestaurarDB')[0]);
        
        Swal.fire({
            title: 'Restaurando...',
            html: 'Procesando archivo SQL. <b>No cierres esta ventana.</b>',
            allowOutsideClick: false,
            didOpen: () => { Swal.showLoading(); }
        });

        $.ajax({
            url: CONTROLLER + '?action=restore_db',
            type: 'POST',
            data: formData,
            contentType: false,
            processData: false,
            success: function(response) {
                Swal.fire('¡Restauración Exitosa!', 'El sistema se ha restaurado correctamente. Se cerrará la sesión por seguridad.', 'success')
                .then(() => {
                    window.location.href = '../public/login.php'; // Forzar logout
                });
            },
            error: function(jqXHR) {
                let msg = jqXHR.responseJSON ? jqXHR.responseJSON.message : 'Error desconocido al restaurar.';
                Swal.fire('Error Crítico', msg, 'error');
            }
        });
    }
    // --- FIN NUEVO ---
});