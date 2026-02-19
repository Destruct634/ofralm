$(document).ready(function() {
    // Asumiremos que el módulo de permisos se llama 'aseguradoras'
    if (USER_GROUP_ID != 1 && (!PERMISOS.aseguradoras || !PERMISOS.aseguradoras.crear)) {
        $('#btnNuevo').hide();
    }

    let tabla = $('#tablaAseguradoras').DataTable({
        "ajax": { "url": "../app/controllers/AseguradoraController.php", "dataSrc": "data" },
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
                    if (USER_GROUP_ID == 1 || (PERMISOS.aseguradoras && PERMISOS.aseguradoras.editar == 1)) {
                        botones += "<button class='btn btn-info btn-sm btnEditar'><i class='fas fa-edit'></i></button>";
                    }
                    if (USER_GROUP_ID == 1 || (PERMISOS.aseguradoras && PERMISOS.aseguradoras.borrar == 1)) {
                        botones += "<button class='btn btn-danger btn-sm btnBorrar'><i class='fas fa-trash'></i></button>";
                    }
                    botones += "</div></div>";
                    return botones;
                }
            }
        ],

    });

    $("#btnNuevo").click(function() {
        $("#formAseguradora").trigger("reset");
        $('#id').val(null);
        $('#estado').val('Activo');
        $("#modalLabel").text("Nueva Aseguradora");
        $("#modalAseguradora").modal("show");
    });

    $('#formAseguradora').submit(function(e) {
        e.preventDefault();
        let id = $.trim($('#id').val());
        let data = {
            id: id,
            nombre: $.trim($('#nombre').val()),
            estado: $.trim($('#estado').val())
        };
        $.ajax({
            url: '../app/controllers/AseguradoraController.php',
            type: id ? 'PUT' : 'POST',
            contentType: 'application/json',
            data: JSON.stringify(data),
            success: function(response) {
                Swal.fire('¡Éxito!', response.message, 'success');
                tabla.ajax.reload(null, false);
                $('#modalAseguradora').modal('hide');
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
        $("#modalLabel").text("Editar Aseguradora");
        $("#modalAseguradora").modal("show");
    });

    $(document).on("click", ".btnBorrar", function() {
        let data = tabla.row($(this).closest('tr')).data();
        Swal.fire({
            title: '¿Estás seguro?', text: "¡No podrás revertir esto!",
            icon: 'warning', showCancelButton: true, confirmButtonText: 'Sí, ¡bórralo!'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: '../app/controllers/AseguradoraController.php',
                    type: 'DELETE',
                    contentType: 'application/json',
                    data: JSON.stringify({ id: data.id }),
                    success: function(response) {
                        Swal.fire('¡Eliminado!', response.message, 'success');
                        tabla.ajax.reload();
                    }
                });
            }
        });
    });
});