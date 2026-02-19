$(document).ready(function() {
    if (USER_GROUP_ID != 1 && (!PERMISOS.tipos_consulta || PERMISOS.tipos_consulta.crear != 1)) {
        $('#btnNuevo').hide();
    }

    let tabla = $('#tablaTiposConsulta').DataTable({
        "ajax": { "url": "../app/controllers/TipoConsultaController.php", "dataSrc": "data" },
        "columns": [
            { "data": "id" }, { "data": "nombre" },
            { "data": "estado", "render": function(data) {
                return data === 'Activo' ? '<span class="badge bg-success">Activo</span>' : '<span class="badge bg-danger">Inactivo</span>';
            }},
            { 
                "data": null, 
                "render": function(data, type, row) {
                    let botones = "<div class='text-center'><div class='btn-group'>";
                    if (USER_GROUP_ID == 1 || (PERMISOS.tipos_consulta && PERMISOS.tipos_consulta.editar == 1)) {
                        botones += "<button class='btn btn-info btn-sm btnEditar'><i class='fas fa-edit'></i></button>";
                    }
                    if (USER_GROUP_ID == 1 || (PERMISOS.tipos_consulta && PERMISOS.tipos_consulta.borrar == 1)) {
                        botones += "<button class='btn btn-danger btn-sm btnBorrar'><i class='fas fa-trash'></i></button>";
                    }
                    botones += "</div></div>";
                    return botones;
                }
            }
        ],

    });

    $("#btnNuevo").click(function() {
        $("#formTipoConsulta").trigger("reset");
        $('#id').val(null);
        $('#estado').val('Activo');
        $("#modalLabel").text("Nuevo Tipo de Consulta");
        $("#modalTipoConsulta").modal("show");
    });

    $('#formTipoConsulta').submit(function(e) {
        e.preventDefault();
        let id = $.trim($('#id').val());
        let data = {
            id: id,
            nombre: $.trim($('#nombre').val()),
            estado: $.trim($('#estado').val())
        };
        $.ajax({
            url: '../app/controllers/TipoConsultaController.php',
            type: id ? 'PUT' : 'POST',
            contentType: 'application/json',
            data: JSON.stringify(data),
            success: function(response) {
                Swal.fire('¡Éxito!', response.message, 'success');
                tabla.ajax.reload(null, false);
                $('#modalTipoConsulta').modal('hide');
            },
            error: function() {
                Swal.fire('Error', 'No se pudo completar la operación.', 'error');
            }
        });
    });

    $(document).on("click", ".btnEditar", function() {
        let data = tabla.row($(this).closest("tr")).data();
        $('#id').val(data.id);
        $('#nombre').val(data.nombre);
        $('#estado').val(data.estado);
        $("#modalLabel").text("Editar Tipo de Consulta");
        $("#modalTipoConsulta").modal("show");
    });

    $(document).on("click", ".btnBorrar", function() {
        let data = tabla.row($(this).closest('tr')).data();
        Swal.fire({
            title: '¿Estás seguro?', text: "¡No podrás revertir esto!",
            icon: 'warning', showCancelButton: true, confirmButtonText: 'Sí, ¡bórralo!'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: '../app/controllers/TipoConsultaController.php',
                    type: 'DELETE',
                    contentType: 'application/json',
                    data: JSON.stringify({ id: data.id }),
                    success: function(response) {
                        Swal.fire('¡Eliminado!', response.message, 'success');
                        tabla.ajax.reload();
                    },
                    error: function() {
                        Swal.fire('Error', 'No se pudo completar la operación.', 'error');
                    }
                });
            }
        });
    });
});