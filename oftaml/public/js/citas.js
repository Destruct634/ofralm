$(document).ready(function() {

    const urlParams = new URLSearchParams(window.location.search);
    const action = urlParams.get('action');
    const pacienteIdParaCita = urlParams.get('paciente_id');

    // Variable global para controlar la pestaña activa (1=Consulta, 2=Cirugía, ''=Todas)
    let categoriaActual = 1; 

    if (action === 'nuevo' && pacienteIdParaCita) {
        setTimeout(function() {
            $('#btnNuevaCita').click();
            $('#modalCita').one('shown.bs.modal', function() {
                $.ajax({
                    url: `../app/controllers/PacienteController.php?action=search_by_id&id=${pacienteIdParaCita}`,
                    type: 'GET',
                    success: function(paciente) {
                        if (paciente) {
                            var option = new Option(paciente.text, paciente.id, true, true);
                            $('#id_paciente').append(option).trigger('change');
                        }
                    }
                });
            });
        }, 500);
    }

    if (USER_GROUP_ID != 1 && (!PERMISOS.citas || PERMISOS.citas.crear != 1)) {
        $('#btnNuevaCita').hide();
    }

    function cargarMedicosFiltro() {
        $.ajax({
            url: '../app/controllers/CitaController.php?action=get_todos_medicos',
            type: 'GET',
            success: function(medicos) {
                let selector = $('#filtro_medico');
                medicos.forEach(function(medico) {
                    selector.append(`<option value="${medico.id}">${medico.apellidos}, ${medico.nombres}</option>`);
                });
            }
        });
    }
    cargarMedicosFiltro();

    $('#filtro_daterange').daterangepicker({
        startDate: moment(),
        endDate: moment(),
        locale: {
            "format": "DD/MM/YYYY", "separator": " - ", "applyLabel": "Aplicar", "cancelLabel": "Cancelar", "fromLabel": "Desde",
            "toLabel": "Hasta", "customRangeLabel": "Personalizado", "weekLabel": "S",
            "daysOfWeek": ["Do", "Lu", "Ma", "Mi", "Ju", "Vi", "Sá"],
            "monthNames": ["Enero", "Febrero", "Marzo", "Abril", "Mayo", "Junio", "Julio", "Agosto", "Septiembre", "Octubre", "Noviembre", "Diciembre"],
            "firstDay": 1
        },
        ranges: {
           'Hoy': [moment(), moment()],
           'Ayer': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
           'Últimos 7 Días': [moment().subtract(6, 'days'), moment()],
           'Este Mes': [moment().startOf('month'), moment().endOf('month')],
           'Mes Pasado': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')]
        }
    });

    let tablaCitas = $('#tablaCitas').DataTable({
        "ajax": {
            "url": "../app/controllers/CitaController.php",
            "type": "GET",
            "data": function (d) {
                let picker = $('#filtro_daterange').data('daterangepicker');
                d.start_date = picker.startDate.format('YYYY-MM-DD');
                d.end_date = picker.endDate.format('YYYY-MM-DD');
                d.medico_id = $('#filtro_medico').val();
                // Enviamos la categoría actual al controlador
                d.categoria_id = categoriaActual;
            },
            "dataSrc": "data"
        },
        "columns": [
            { "data": "id" }, 
            { 
                "data": "paciente",
                "render": function(data, type, row) {
                    if (USER_GROUP_ID == 1 || (PERMISOS.historial && PERMISOS.historial.ver == 1)) {
                        return `<a href="index.php?page=historial&paciente_id=${row.id_paciente}">${data}</a>`;
                    }
                    return data;
                }
            }, 
            { "data": "especialidad" },
            { "data": "medico" }, 
            
            // MODIFICACIÓN VISUAL: Badge de color según el tipo de cita
            { 
                "data": "motivo_detalle",
                "render": function(data, type, row) {
                    // row.tipo_cita viene de la base de datos (Categoría: Consulta o Cirugía)
                    let badgeClass = 'bg-info text-dark'; // Azul por defecto (Consulta)
                    let icon = 'fas fa-stethoscope';

                    if (row.tipo_cita && row.tipo_cita.includes('Cirugía')) {
                        badgeClass = 'bg-danger text-white'; // Rojo para Cirugía
                        icon = 'fas fa-procedures';
                    }

                    return `<span class="badge ${badgeClass}"><i class="${icon} me-1"></i>${data}</span>`;
                }
            },

            { "data": null, "render": function (data) { return `${moment(data.fecha_cita).format('DD/MM/YYYY')} ${data.hora_cita}`; }},
            { 
                "data": "estado",
                "render": function(data) {
                    let badgeClass = '';
                    switch (data) {
                        case 'Programada': badgeClass = 'bg-primary'; break;
                        case 'Completada': badgeClass = 'bg-success'; break;
                        case 'Cancelada': badgeClass = 'bg-danger'; break;
                        case 'No se presentó': badgeClass = 'bg-warning text-dark'; break;
                        default: badgeClass = 'bg-secondary';
                    }
                    return `<span class="badge ${badgeClass}">${data}</span>`;
                }
            },
            { 
                "data": "notificado",
                "render": function(data, type, row) {
                    let isChecked = data == 1 ? 'checked' : '';
                    return `<div class="form-check form-switch d-flex justify-content-center"><input class="form-check-input switch-notificado" type="checkbox" role="switch" data-id="${row.id}" ${isChecked}></div>`;
                }
            },
            { 
                "data": null, 
                "render": function(data, type, row) {
                    let botones = "<div class='text-center'><div class='btn-group'>";
                    
                    if (row.estado === 'Completada') {
                        if (row.facturada == 1) {
                            botones += "<button class='btn btn-success btn-sm' disabled title='Ya Facturada'><i class='fas fa-check'></i></button>";
                        } else if (USER_GROUP_ID == 1 || (PERMISOS.facturacion && PERMISOS.facturacion.crear == 1)) {
                            botones += `<a href="index.php?page=facturacion&action=nuevo&cita_id=${row.id}" class="btn btn-info btn-sm" title="Facturar Cita"><i class="fas fa-dollar-sign"></i></a>`;
                        }
                    }
                    if (USER_GROUP_ID == 1 || (PERMISOS.citas && PERMISOS.citas.editar == 1)) {
                        
                        let notificado = row.notificado == 1;
                        let btnWhatsappDisabled = notificado ? 'disabled' : '';
                        let btnWhatsappTitle = notificado ? 'Paciente ya notificado' : 'Notificar por WhatsApp';

                        botones += `<button class='btn btn-success btn-sm btnWhatsapp' title='${btnWhatsappTitle}' ${btnWhatsappDisabled}><i class='fab fa-whatsapp'></i></button>`;

                        botones += "<button class='btn btn-info btn-sm btnEditar'><i class='fas fa-edit'></i></button>";
                    }
                    if (USER_GROUP_ID == 1 || (PERMISOS.citas && PERMISOS.citas.borrar == 1)) {
                        botones += "<button class='btn btn-danger btn-sm btnBorrar'><i class='fas fa-trash'></i></button>";
                    }
                    botones += "</div></div>";
                    return botones;
                }
            }
        ],

    });

    $('button[data-bs-toggle="tab"]').on('shown.bs.tab', function (e) {
        categoriaActual = $(e.target).data('categoria');
        
        // MODIFICACIÓN VISUAL: Cambiar el título de la tarjeta según la pestaña
        let cardHeader = $('.card-header').first();
        // Guardamos el botón para no perderlo al cambiar el texto
        let btnHtml = cardHeader.find('button').prop('outerHTML') || '<button class="btn btn-primary btn-sm float-end" id="btnNuevaCita"><i class="fas fa-plus me-1"></i>Nueva Cita</button>';
        
        let nuevoTitulo = 'Listado de Citas';
        let icono = 'fa-calendar-alt';

        if (categoriaActual == 1) { // Consulta
            nuevoTitulo = 'Listado de Consultas';
            icono = 'fa-stethoscope';
        } else if (categoriaActual == 2) { // Cirugía
            nuevoTitulo = 'Listado de Cirugías';
            icono = 'fa-procedures';
        }

        cardHeader.html(`<i class="fas ${icono} me-1"></i>${nuevoTitulo} ${btnHtml}`);
        
        // Reactivamos el evento click del botón porque al reemplazar HTML se pierde
        $('#btnNuevaCita').click(abrirModalNuevaCita);

        tablaCitas.ajax.reload();
    });

    $('#btnFiltrar').on('click', function() {
        tablaCitas.ajax.reload();
    });
    
    $('#filtro_daterange').on('apply.daterangepicker', function(ev, picker) {
        tablaCitas.ajax.reload();
    });
    
    $(document).on('change', '.switch-notificado', function() {
        let switchPresionado = $(this); 
        let id = switchPresionado.data('id');
        let notificado = switchPresionado.is(':checked') ? 1 : 0;
        
        let fila = switchPresionado.closest('tr');
        let botonWhatsapp = fila.find('.btnWhatsapp');

        botonWhatsapp.prop('disabled', switchPresionado.is(':checked'));
        if (switchPresionado.is(':checked')) {
            botonWhatsapp.attr('title', 'Paciente ya notificado');
        } else {
            botonWhatsapp.attr('title', 'Notificar por WhatsApp');
        }

        $.ajax({
            url: `../app/controllers/CitaController.php?action=toggle_notificacion`,
            type: 'PUT',
            contentType: 'application/json',
            data: JSON.stringify({ id: id, notificado: notificado }),
            error: function() {
                Swal.fire('Error', 'No se pudo actualizar el estado.', 'error');
                tablaCitas.ajax.reload(); 
            }
        });
    });

    function inicializarSelectPaciente() {
        $('#id_paciente').select2({
            theme: "bootstrap-5",
            dropdownParent: $('#modalCita'),
            placeholder: 'Escriba para buscar un paciente...',
            minimumInputLength: 2,
            tags: true,
            createTag: function(params) {
                let term = $.trim(params.term);
                if (term === '') return null;
                return { id: term, text: term, newTag: true }
            },
            ajax: {
                url: '../app/controllers/PacienteController.php?action=search',
                dataType: 'json', delay: 250,
                data: function(params) { return { term: params.term }; },
                processResults: function(data) { return { results: data }; }
            }
        }).on('select2:select', function(e) {
            if (e.params.data.newTag) {
                $('#rapido_nombres').val(e.params.data.text);
                $('#rapido_apellidos, #rapido_numero_identidad').val('');
                $('#modalNuevoPacienteRapido').modal('show');
                $('#id_paciente').val(null).trigger('change');
            }
        });
    }

    $('#formNuevoPacienteRapido').submit(function(e) {
        e.preventDefault();
        let form = $(this);
        let data = {
            nombres: $('#rapido_nombres').val(), apellidos: $('#rapido_apellidos').val(),
            numero_identidad: $('#rapido_numero_identidad').val(), sexo: form.find('input[name="sexo"]').val(),
            direccion: form.find('input[name="direccion"]').val(), telefono: form.find('input[name="telefono"]').val(),
            email: form.find('input[name="email"]').val(), fecha_nacimiento: form.find('input[name="fecha_nacimiento"]').val(),
            tiene_seguro: 'No', observaciones: ""
        };
        $.ajax({
            url: '../app/controllers/PacienteController.php', type: 'POST',
            contentType: 'application/json', data: JSON.stringify(data),
            success: function(response) {
                if (response.paciente) {
                    $('#modalNuevoPacienteRapido').modal('hide');
                    Swal.fire('¡Éxito!', response.message, 'success');
                    let newOption = new Option(response.paciente.text, response.paciente.id, true, true);
                    $('#id_paciente').append(newOption).trigger('change');
                } else {
                    Swal.fire('Error', 'No se pudo obtener la información del nuevo paciente.', 'error');
                }
            },
            error: function() { Swal.fire('Error', 'No se pudo crear el nuevo paciente.', 'error'); }
        });
    });

    function cargarEspecialidades(selectedId = null) {
        $.ajax({
            url: '../app/controllers/MedicoController.php?action=especialidades', type: 'GET',
            success: function(especialidades) {
                let selector = $('#id_especialidad');
                selector.html('<option value="">Seleccione una especialidad...</option>');
                especialidades.forEach(function(esp) { selector.append(`<option value="${esp.id}">${esp.nombre}</option>`); });
                if (selectedId) selector.val(selectedId);
            }
        });
    }

    $('#id_especialidad').on('change', function() {
        let especialidadId = $(this).val();
        let selectorMedico = $('#id_medico');
        if (especialidadId) {
            $.ajax({
                url: `../app/controllers/CitaController.php?action=medicos&especialidad_id=${especialidadId}`, type: 'GET',
                success: function(medicos) {
                    selectorMedico.html('<option value="">Seleccione un médico...</option>');
                    medicos.forEach(function(medico) { selectorMedico.append(`<option value="${medico.id}">${medico.nombres} ${medico.apellidos}</option>`); });
                    selectorMedico.prop('disabled', false);
                }
            });
        } else {
            selectorMedico.html('<option value="">Esperando especialidad...</option>').prop('disabled', true);
        }
    });

    function cargarServiciosParaCitas(selectedId = null, categoriaId = null) {
        let url = '../app/controllers/CitaController.php?action=get_servicios_citas';
        if (categoriaId) {
            url += `&categoria_id=${categoriaId}`;
        }

        $.get(url, function(servicios) {
            let selector = $('#id_servicio');
            selector.html('<option value="">Seleccione un servicio...</option>');
            
            let categorias = {};
            servicios.forEach(s => {
                if (!categorias[s.nombre_categoria]) {
                    categorias[s.nombre_categoria] = [];
                }
                categorias[s.nombre_categoria].push(s);
            });

            for (const categoria in categorias) {
                let optgroup = $(`<optgroup label="${categoria}"></optgroup>`);
                categorias[categoria].forEach(s => {
                    optgroup.append(`<option value="${s.id}">${s.nombre_servicio}</option>`);
                });
                selector.append(optgroup);
            }

            if (selectedId) {
                selector.val(selectedId);
            }
        });
    }

    // Extrajimos la función para poder reasignarla al cambiar el HTML del header
    function abrirModalNuevaCita() {
        $("#formCita").trigger("reset");
        
        let modalHeader = $("#modalCita .modal-header");
        let modalTitle = $("#modalCitaLabel");
        
        modalHeader.removeClass("bg-primary bg-danger bg-info text-white");

        if (categoriaActual == 2) { // 2 = Cirugía
            modalHeader.addClass("bg-danger text-white");
            modalTitle.text("Programar Cirugía");
        } else {
            modalHeader.addClass("bg-primary text-white");
            modalTitle.text("Nueva Consulta");
        }

        $('#id').val(null);
        $('#id_paciente').html('');
        inicializarSelectPaciente();
        cargarEspecialidades();
        
        cargarServiciosParaCitas(null, categoriaActual);
        
        $('#id_medico').html('<option value="">Esperando especialidad...</option>').prop('disabled', true);
        $("#modalCita").modal("show");
    }

    $("#btnNuevaCita").click(abrirModalNuevaCita);

    $('#formCita').submit(function(e) {
        e.preventDefault();
        let id = $.trim($('#id').val());
        let data = {
            id: id, id_paciente: $.trim($('#id_paciente').val()), id_medico: $.trim($('#id_medico').val()),
            id_servicio: $.trim($('#id_servicio').val()),
            fecha_cita: $.trim($('#fecha_cita').val()), hora_cita: $.trim($('#hora_cita').val()), estado: $.trim($('#estado').val())
        };
        $.ajax({
            url: '../app/controllers/CitaController.php', type: id ? 'PUT' : 'POST',
            contentType: 'application/json', data: JSON.stringify(data),
            success: function(response) {
                $('#modalCita').modal('hide');
                Swal.fire('¡Éxito!', response.message, 'success');
                tablaCitas.ajax.reload();
            },
            error: function() { Swal.fire('Error', 'No se pudo completar la operación', 'error'); }
        });
    });

    $(document).on("click", ".btnEditar", function() {
        let citaData = tablaCitas.row($(this).closest("tr")).data();
        let id = citaData.id;
        $.ajax({
            url: `../app/controllers/CitaController.php?id=${id}`, type: 'GET',
            success: function(data) {
                $('#id').val(data.id);
                $('#fecha_cita').val(data.fecha_cita);
                
                let horaSinSegundos = data.hora_cita;
                if (horaSinSegundos && horaSinSegundos.length > 5) {
                    horaSinSegundos = horaSinSegundos.substring(0, 5);
                }
                $('#hora_cita').val(horaSinSegundos);
                
                $('#estado').val(data.estado);
                
                let selectorPaciente = $('#id_paciente');
                selectorPaciente.html('');
                let pacienteOption = new Option(citaData.paciente, data.id_paciente, true, true);
                selectorPaciente.append(pacienteOption).trigger('change');
                inicializarSelectPaciente();

                cargarEspecialidades(data.id_especialidad);
                
                cargarServiciosParaCitas(data.id_servicio, data.id_categoria_servicio);

                let especialidadId = data.id_especialidad;
                let medicoActualId = data.id_medico;
                if (especialidadId) {
                    $.ajax({
                        url: `../app/controllers/CitaController.php?action=medicos&especialidad_id=${especialidadId}&medico_actual_id=${medicoActualId}`,
                        type: 'GET',
                        success: function(medicos) {
                            let selectorMedico = $('#id_medico');
                            selectorMedico.html(''); 
                            medicos.forEach(function(medico) {
                                let textoOpcion = `${medico.nombres} ${medico.apellidos}`;
                                let estiloOpcion = "";
                                if (medico.estado === 'Inactivo') {
                                    textoOpcion += ' (Inactivo)';
                                    estiloOpcion = 'style="color: #6c757d; font-style: italic;"';
                                }
                                selectorMedico.append(`<option value="${medico.id}" ${estiloOpcion}>${textoOpcion}</option>`);
                            });
                            selectorMedico.val(medicoActualId);
                            selectorMedico.prop('disabled', false);
                        }
                    });
                }

                let modalHeader = $("#modalCita .modal-header");
                let modalTitle = $("#modalCitaLabel");
                
                modalHeader.removeClass("bg-primary bg-danger bg-info text-white");

                if (data.id_categoria_servicio == 2) { 
                    modalHeader.addClass("bg-danger text-white");
                    modalTitle.text("Editar Cirugía");
                } else {
                    modalHeader.addClass("bg-info text-white");
                    modalTitle.text("Editar Consulta");
                }

                $("#modalCita").modal("show");
            }
        });
    });

    $(document).on("click", ".btnBorrar", function() {
        let fila = $(this).closest('tr');
        let data = tablaCitas.row(fila).data();
        Swal.fire({
            title: '¿Estás seguro?', text: `Se eliminará la cita con ID ${data.id}`,
            icon: 'warning', showCancelButton: true, confirmButtonColor: '#d33',
            confirmButtonText: 'Sí, ¡bórrala!'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: '../app/controllers/CitaController.php', type: 'DELETE',
                    contentType: 'application/json', data: JSON.stringify({ id: data.id }),
                    success: function(response) {
                        Swal.fire('¡Eliminada!', response.message, 'success');
                        tablaCitas.row(fila).remove().draw();
                    }
                });
            }
        });
    });

    $(document).on('click', '.btnWhatsapp', function() {
        let data = tablaCitas.row($(this).closest('tr')).data();
        if (!data.paciente_telefono || data.paciente_telefono === 'N/A') {
            Swal.fire('Error', 'Este paciente no tiene un número de teléfono registrado.', 'error');
            return;
        }
        let telefonoLimpio = data.paciente_telefono.replace(/[^0-9]/g, '');
        const codigoPais = '504';
        if (telefonoLimpio.length === 8) {
            telefonoLimpio = codigoPais + telefonoLimpio;
        }
        
        let fechaFormateada = moment(data.fecha_cita).format('DD/MM/YYYY');
        let mensaje = `Hola ${data.paciente}, le recordamos su cita de "${data.motivo_detalle}" programada para el día ${fechaFormateada} a las ${data.hora_cita}. Saludos cordiales, Clínica MVC.`;
        
        let mensajeCodificado = encodeURIComponent(mensaje);
        let url = `https://wa.me/${telefonoLimpio}?text=${mensajeCodificado}`;
        window.open(url, '_blank');
    });
});