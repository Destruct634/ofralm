$(document).ready(function() {
    if (USER_GROUP_ID != 1 && (!PERMISOS.especialidades || PERMISOS.especialidades.crear != 1)) {
        $('#btnNuevaEspecialidad').hide();
    }
    
    let tablaEspecialidades = $('#tablaEspecialidades').DataTable({
        "ajax": { "url": "../app/controllers/EspecialidadController.php", "dataSrc": "data" },
        "columns": [
            { "data": "id" }, 
            { "data": "nombre" },
            { "data": "estado", "render": function(data) {
                return data === 'Activo' ? '<span class="badge bg-success">Activo</span>' : '<span class="badge bg-danger">Inactivo</span>';
            }},
            { 
                "data": null, 
                "render": function(data, type, row) {
                    let botones = "<div class='text-center'><div class='btn-group'>";
                    if (USER_GROUP_ID == 1 || (PERMISOS.especialidades && PERMISOS.especialidades.editar == 1)) {
                        botones += "<button class='btn btn-info btn-sm btnEditar'><i class='fas fa-edit'></i></button>";
                    }
                    if (USER_GROUP_ID == 1 || (PERMISOS.especialidades && PERMISOS.especialidades.borrar == 1)) {
                        botones += "<button class='btn btn-danger btn-sm btnBorrar'><i class='fas fa-trash'></i></button>";
                    }
                    botones += "</div></div>";
                    return botones;
                }
            }
        ],

    });

    $("#btnNuevaEspecialidad").click(function() {
        $("#formEspecialidad").trigger("reset");
        $('#id').val(null);
        $('#estado').val('Activo');
        $("#modalEspecialidadLabel").text("Nueva Especialidad");
        $("#modalEspecialidad").modal("show");
    });

    $('#formEspecialidad').submit(function(e) {
        e.preventDefault();
        let id = $.trim($('#id').val());
        let data = {
            id: id,
            nombre: $.trim($('#nombre').val()),
            estado: $.trim($('#estado').val())
        };
        
        $.ajax({
            url: '../app/controllers/EspecialidadController.php',
            type: id ? 'PUT' : 'POST',
            contentType: 'application/json',
            data: JSON.stringify(data),
            success: function(response) {
                Swal.fire('¡Éxito!', response.message, 'success');
                tablaEspecialidades.ajax.reload(null, false);
                $('#modalEspecialidad').modal('hide');
            },
            error: function(jqXHR) {
                let msg = jqXHR.responseJSON ? jqXHR.responseJSON.message : 'No se pudo completar la operación.';
                Swal.fire('Error', msg, 'error');
            }
        });
    });

    $(document).on("click", ".btnEditar", function() {
        let data = tablaEspecialidades.row($(this).closest("tr")).data();
        $('#id').val(data.id);
        $('#nombre').val(data.nombre);
        $('#estado').val(data.estado);
        $("#modalEspecialidadLabel").text("Editar Especialidad");
        $("#modalEspecialidad").modal("show");
    });

    $(document).on("click", ".btnBorrar", function() {
        let data = tablaEspecialidades.row($(this).closest('tr')).data();
        Swal.fire({
            title: '¿Estás seguro?', text: "¡No podrás revertir esto!",
            icon: 'warning', showCancelButton: true, confirmButtonText: 'Sí, ¡bórralo!'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: '../app/controllers/EspecialidadController.php',
                    type: 'DELETE',
                    contentType: 'application/json',
                    data: JSON.stringify({ id: data.id }),
                    success: function(response) {
                        Swal.fire('¡Eliminado!', response.message, 'success');
                        tablaEspecialidades.ajax.reload();
                    }
                });
            }
        });
    });
});