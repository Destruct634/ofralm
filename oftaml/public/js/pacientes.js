$(document).ready(function() {
    if (USER_GROUP_ID != 1 && (!PERMISOS.pacientes || PERMISOS.pacientes.crear != 1)) {
        $('#btnNuevo').hide();
    }

    function calcularEdad(fecha) {
        if (!fecha) return '';
        const hoy = new Date();
        const fechaNacimiento = new Date(fecha);
        let edad = hoy.getFullYear() - fechaNacimiento.getFullYear();
        const mes = hoy.getMonth() - fechaNacimiento.getMonth();
        if (mes < 0 || (mes === 0 && hoy.getDate() < fechaNacimiento.getDate())) {
            edad--;
        }
        return edad >= 0 ? edad : 0;
    }

    $('#fecha_nacimiento').on('change', function() {
        const edad = calcularEdad($(this).val());
        $('#edad_paciente').text(edad ? edad + ' años' : '');
    });

    let tablaPacientes = $('#tablaPacientes').DataTable({
        "ajax": { "url": "../app/controllers/PacienteController.php", "dataSrc": "data" },
        "columns": [
            { "data": "id" }, 
            { "data": "nombres" }, 
            { "data": "apellidos" }, 
            { "data": "numero_identidad" },
            { 
                "data": "fecha_nacimiento",
                "render": function(data, type, row) {
                    if (!data) return '';
                    return calcularEdad(data) + ' años';
                }
            }, 
            { "data": "telefono" }, 
            { 
                "data": "fecha_creacion",
                "render": function(data, type, row) {
                    return moment(data).format('DD/MM/YYYY');
                }
            },
            { 
                "data": null,
                "render": function(data, type, row) {
                    let botones = "<div class='text-center'><div class='btn-group'>";
                    
                    botones += "<button class='btn btn-success btn-sm btnEstadoCuenta' title='Estado de Cuenta'><i class='fas fa-file-invoice-dollar'></i></button>";
                    
                    if (USER_GROUP_ID == 1 || (PERMISOS.pacientes && PERMISOS.pacientes.editar == 1)) {
                        botones += "<button class='btn btn-info btn-sm btnEditar' title='Editar Paciente'><i class='fas fa-edit'></i></button>";
                    }
                    if (USER_GROUP_ID == 1 || (PERMISOS.historial && PERMISOS.historial.ver == 1)) { 
                         botones += "<button class='btn btn-secondary btn-sm btnHistorial' title='Historial Clínico'><i class='fas fa-notes-medical'></i></button>";
                    }
                    if (USER_GROUP_ID == 1 || (PERMISOS.pacientes && PERMISOS.pacientes.borrar == 1)) {
                        botones += "<button class='btn btn-danger btn-sm btnBorrar' title='Eliminar Paciente'><i class='fas fa-trash'></i></button>";
                    }
                    botones += "</div></div>";
                    return botones;
                }
            }
        ]
        // NOTA: Se eliminó la línea de "language" para usar la configuración global del footer.
    });
    
    $('#tiene_seguro').on('change', function() {
        if ($(this).val() === 'Sí') {
            $.get('../app/controllers/PacienteController.php?action=get_aseguradoras', function(aseguradoras) {
                let selector = $('#id_aseguradora');
                selector.html('<option value="">Seleccione...</option>');
                aseguradoras.forEach(a => {
                    selector.append(`<option value="${a.id}">${a.nombre}</option>`);
                });
            });
            $('#campos_seguro').slideDown();
            $('#id_aseguradora, #numero_poliza').prop('required', true);
        } else {
            $('#campos_seguro').slideUp();
            $('#id_aseguradora, #numero_poliza').prop('required', false).val('');
        }
    });

    $("#btnNuevo").click(function() {
        $("#formPaciente").trigger("reset");
        $(".modal-header").css("background-color", "#0d6efd").css("color", "white");
        $(".modal-title").text("Nuevo Paciente");
        $('#id').val(null);
        $('#edad_paciente').text('');
        $('#tiene_seguro').val('No').trigger('change');
        
        // Asegurarse de que los switches estén apagados
        $('#antecedente_dm').prop('checked', false);
        $('#antecedente_hta').prop('checked', false);
        $('#antecedente_glaucoma').prop('checked', false);
        $('#antecedente_asma').prop('checked', false);

        $("#modalCRUD").modal("show");
    });
    
    $('#formPaciente').submit(function(e) {
        e.preventDefault();
        let id = $.trim($('#id').val());
        let data = {
            id: id, 
            nombres: $.trim($('#nombres').val()), 
            apellidos: $.trim($('#apellidos').val()),
            numero_identidad: $.trim($('#numero_identidad').val()), 
            sexo: $.trim($('#sexo').val()),
            direccion: $.trim($('#direccion').val()), 
            telefono: $.trim($('#telefono').val()), 
            email: $.trim($('#email').val()),
            fecha_nacimiento: $.trim($('#fecha_nacimiento').val()), 
            tiene_seguro: $.trim($('#tiene_seguro').val()),
            id_aseguradora: $.trim($('#id_aseguradora').val()),
            numero_poliza: $.trim($('#numero_poliza').val()),
            observaciones: $.trim($('#observaciones').val()),

            // Nuevos campos de Antecedentes
            antecedente_dm: $('#antecedente_dm').is(':checked') ? 1 : 0,
            antecedente_hta: $('#antecedente_hta').is(':checked') ? 1 : 0,
            antecedente_glaucoma: $('#antecedente_glaucoma').is(':checked') ? 1 : 0,
            antecedente_asma: $('#antecedente_asma').is(':checked') ? 1 : 0,
            alergias: $.trim($('#alergias').val()),
            antecedente_cirugias: $.trim($('#antecedente_cirugias').val()),
            antecedente_trauma: $.trim($('#antecedente_trauma').val()),
            antecedente_otros: $.trim($('#antecedente_otros').val())
        };
        
        $.ajax({
            url: '../app/controllers/PacienteController.php', type: id ? 'PUT' : 'POST',
            contentType: 'application/json', data: JSON.stringify(data),
            success: function(response) {
                Swal.fire('¡Éxito!', response.message, 'success');
                tablaPacientes.ajax.reload(null, false);
                $('#modalCRUD').modal('hide');
            },
            error: function(jqXHR) {
                let errorMessage = 'No se pudo completar la operación.';
                if (jqXHR.responseJSON && jqXHR.responseJSON.message) {
                    errorMessage = jqXHR.responseJSON.message;
                }
                Swal.fire('Error', errorMessage, 'error');
            }
        });
    });

    $(document).on("click", ".btnEditar", function() {
        let data = tablaPacientes.row($(this).closest("tr")).data();
        $.ajax({
            url: `../app/controllers/PacienteController.php?id=${data.id}`, type: 'GET',
            success: function(data) {
                $('#id').val(data.id); 
                $('#nombres').val(data.nombres); 
                $('#apellidos').val(data.apellidos);
                $('#numero_identidad').val(data.numero_identidad); 
                $('#sexo').val(data.sexo);
                $('#direccion').val(data.direccion); 
                $('#telefono').val(data.telefono); 
                $('#email').val(data.email);
                $('#fecha_nacimiento').val(data.fecha_nacimiento);
                $('#observaciones').val(data.observaciones);
                
                $('#antecedente_dm').prop('checked', data.antecedente_dm == 1);
                $('#antecedente_hta').prop('checked', data.antecedente_hta == 1);
                $('#antecedente_glaucoma').prop('checked', data.antecedente_glaucoma == 1);
                $('#antecedente_asma').prop('checked', data.antecedente_asma == 1);
                
                $('#alergias').val(data.alergias);
                $('#antecedente_cirugias').val(data.antecedente_cirugias);
                $('#antecedente_trauma').val(data.antecedente_trauma);
                $('#antecedente_otros').val(data.antecedente_otros);

                $('#tiene_seguro').val(data.tiene_seguro);
                $('#tiene_seguro').trigger('change'); 
                
                setTimeout(() => {
                    $('#id_aseguradora').val(data.id_aseguradora);
                }, 300);
                $('#numero_poliza').val(data.numero_poliza);
                
                $('#fecha_nacimiento').trigger('change'); 
                
                $(".modal-header").css("background-color", "#17a2b8").css("color", "white");
                $(".modal-title").text("Editar Paciente");
                $("#modalCRUD").modal("show");
            }
        });
    });
    
    $(document).on('click', '.btnHistorial', function() {
        let data = tablaPacientes.row($(this).closest('tr')).data();
        window.location.href = `index.php?page=historial&paciente_id=${data.id}`;
    });

    $(document).on("click", ".btnBorrar", function() {
        let fila = $(this).closest('tr');
        let data = tablaPacientes.row(fila).data();
        Swal.fire({
            title: '¿Estás seguro?', text: `Se eliminará el paciente con ID ${data.id}`,
            icon: 'warning', showCancelButton: true, confirmButtonColor: '#d33', confirmButtonText: 'Sí, ¡bórralo!'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: '../app/controllers/PacienteController.php', type: 'DELETE',
                    contentType: 'application/json', data: JSON.stringify({ id: data.id }),
                    success: function(response) {
                        Swal.fire('¡Eliminado!', response.message, 'success');
                        tablaPacientes.row(fila).remove().draw();
                    },
                    error: function(jqXHR) {
                        let errorMessage = 'No se pudo completar la operación.';
                        if (jqXHR.responseJSON && jqXHR.responseJSON.message) {
                            errorMessage = jqXHR.responseJSON.message;
                        }
                        Swal.fire('Error', errorMessage, 'error');
                    }
                });
            }
        });
    });

    $('#daterange_estado_cuenta').daterangepicker({
        locale: { "format": "DD/MM/YYYY", "separator": " - ", "applyLabel": "Aplicar", "cancelLabel": "Cancelar" },
        ranges: {
           'Este Mes': [moment().startOf('month'), moment().endOf('month')],
           'Mes Pasado': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')],
           'Este Año': [moment().startOf('year'), moment().endOf('year')],
           'Año Pasado': [moment().subtract(1, 'year').startOf('year'), moment().subtract(1, 'year').endOf('year')]
        }
    });

    $(document).on('click', '.btnEstadoCuenta', function() {
        let data = tablaPacientes.row($(this).closest('tr')).data();
        $('#id_paciente_estado_cuenta').val(data.id);
        $('#nombre_paciente_estado_cuenta').text(`${data.nombres} ${data.apellidos}`);
        $('#modalEstadoCuenta').modal('show');
    });

    $('#formEstadoCuenta').submit(function(e) {
        e.preventDefault();
        
        let pacienteId = $('#id_paciente_estado_cuenta').val();
        let picker = $('#daterange_estado_cuenta').data('daterangepicker');
        let startDate = picker.startDate.format('YYYY-MM-DD');
        let endDate = picker.endDate.format('YYYY-MM-DD');

        let url = `../app/reports/estado_cuenta_report.php?paciente_id=${pacienteId}&start_date=${startDate}&end_date=${endDate}`;

        $('#pdfFrame').attr('src', url);
        $('#modalPdfViewer').modal('show');
        $('#modalEstadoCuenta').modal('hide');
    });

    $('#modalPdfViewer').on('hidden.bs.modal', function () {
        $('#pdfFrame').attr('src', 'about:blank');
    });
});