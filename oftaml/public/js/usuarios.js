$(document).ready(function() {
    let tabla = $('#tablaUsuarios').DataTable({
        "ajax": { "url": "../app/controllers/UsuarioController.php", "dataSrc": "data" },
        "columns": [
            { "data": "id" }, { "data": "nombre_completo" }, { "data": "usuario" }, 
            { "data": "nombre_grupo", "defaultContent": "<i>Sin Asignar</i>" },
            { "data": "estado", "render": function(data) { return data === 'Activo' ? '<span class="badge bg-success">Activo</span>' : '<span class="badge bg-danger">Inactivo</span>'; }},
            { "data": null, "render": function(data, type, row) {
                if (row.id == 1) return "<div class='text-center'><div class='btn-group'><button class='btn btn-info btn-sm btnEditar'><i class='fas fa-edit'></i></button><button class='btn btn-danger btn-sm' disabled><i class='fas fa-trash'></i></button></div></div>";
                return "<div class='text-center'><div class='btn-group'><button class='btn btn-info btn-sm btnEditar'><i class='fas fa-edit'></i></button><button class='btn btn-danger btn-sm btnBorrar'><i class='fas fa-trash'></i></button></div></div>";
            }}
        ]
    });
    
    function cargarGrupos(selectedId = null) {
        $.get('../app/controllers/UsuarioController.php?action=get_grupos', function(grupos) {
            let selector = $('#id_grupo');
            selector.html('<option value="">Seleccione un grupo...</option>');
            grupos.forEach(g => { selector.append(`<option value="${g.id}">${g.nombre_grupo}</option>`); });
            if (selectedId) selector.val(selectedId);
        });
    }

    $('#es_medico').on('change', function() {
        if ($(this).is(':checked')) {
            $('#campos_medico').slideDown();
            $('#id_especialidad, #telefono, #email_medico').prop('required', true);
            if ($('#id_especialidad option').length <= 1) {
                $.get('../app/controllers/MedicoController.php?action=especialidades', function(especialidades) {
                    let selector = $('#id_especialidad');
                    selector.html('<option value="">Seleccione especialidad...</option>');
                    especialidades.forEach(e => { selector.append(`<option value="${e.id}">${e.nombre}</option>`); });
                });
            }
        } else {
            $('#campos_medico').slideUp();
            $('#id_especialidad, #telefono, #email_medico').prop('required', false).val('');
        }
    });

    $("#btnNuevo").click(function() {
        $("#formUsuario").trigger("reset");
        $('#id').val(null);
        $('#password').prop('required', true);
        $("#modalLabel").text("Nuevo Usuario");
        $('#es_medico').prop('checked', false).trigger('change');
        $('#nombre_completo, #usuario, #id_grupo, #estado').prop('disabled', false);
        cargarGrupos();
        $("#modalUsuario").modal("show");
    });

    $('#formUsuario').submit(function(e) { e.preventDefault();
        let id = $.trim($('#id').val());
        let data = {
            id: id, nombre_completo: $.trim($('#nombre_completo').val()),
            usuario: $.trim($('#usuario').val()), password: $.trim($('#password').val()),
            estado: $.trim($('#estado').val()), id_grupo: $.trim($('#id_grupo').val()),
            es_medico: $('#es_medico').is(':checked'),
            id_especialidad: $.trim($('#id_especialidad').val()),
            telefono: $.trim($('#telefono').val()),
            email_medico: $.trim($('#email_medico').val())
        };
        $.ajax({
            url: '../app/controllers/UsuarioController.php', type: id ? 'PUT' : 'POST',
            contentType: 'application/json', data: JSON.stringify(data),
            success: function(r) { Swal.fire('¡Éxito!', r.message, 'success'); tabla.ajax.reload(null, false); $('#modalUsuario').modal('hide'); },
            error: function(jqXHR) { Swal.fire('Error', jqXHR.responseJSON.message, 'error');}
        });
    });

    $(document).on("click", ".btnEditar", function() {
        let id = tabla.row($(this).closest("tr")).data().id;
        
        // Limpiamos el switch y los campos antes de cargar
        $('#es_medico').prop('checked', false).trigger('change');
        $('#telefono, #email_medico, #id_especialidad').val('');

        $.get(`../app/controllers/UsuarioController.php?id=${id}`, function(data) {
            $('#id').val(data.id); $('#nombre_completo').val(data.nombre_completo);
            $('#usuario').val(data.usuario); $('#estado').val(data.estado);
            $('#password').val('').prop('required', false);
            $("#modalLabel").text("Editar Usuario");
            cargarGrupos(data.id_grupo);

            if (data.id == 1) {
                $('#usuario, #id_grupo, #estado, #es_medico').prop('disabled', true);
            } else {
                $('#usuario, #id_grupo, #estado, #es_medico').prop('disabled', false);
            }

            // Verificamos si existe un perfil médico
            $.get(`../app/controllers/MedicoController.php?action=get_by_user&user_id=${id}`, function(medicoData){
                if(medicoData && medicoData !== false){
                    // Si devuelve datos, activamos el switch
                    $('#es_medico').prop('checked', true).trigger('change');
                    $('#telefono').val(medicoData.telefono);
                    $('#email_medico').val(medicoData.email);
                    
                    // Cargar especialidades y seleccionar la correcta
                    $.get('../app/controllers/MedicoController.php?action=especialidades', function(especialidades) {
                        let selector = $('#id_especialidad');
                        selector.html('<option value="">Seleccione especialidad...</option>');
                        especialidades.forEach(e => { selector.append(`<option value="${e.id}">${e.nombre}</option>`); });
                        selector.val(medicoData.id_especialidad);
                    });
                } else {
                    // Si devuelve false o null, nos aseguramos de desactivarlo
                    $('#es_medico').prop('checked', false).trigger('change');
                }
            });
            $("#modalUsuario").modal("show");
        });
    });
    
    $(document).on("click", ".btnBorrar", function() {
        let fila = $(this).closest('tr');
        let data = tabla.row(fila).data();
        Swal.fire({
            title: '¿Estás seguro?', text: `¡Se eliminará el usuario "${data.usuario}"!`,
            icon: 'warning', showCancelButton: true, confirmButtonText: 'Sí, ¡bórralo!'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: '../app/controllers/UsuarioController.php', type: 'DELETE',
                    contentType: 'application/json', data: JSON.stringify({ id: data.id }),
                    success: function(r) { Swal.fire('¡Eliminado!', r.message, 'success'); tabla.row(fila).remove().draw(); },
                    error: function(jqXHR) { Swal.fire('Error', jqXHR.responseJSON.message, 'error'); }
                });
            }
        });
    });
});