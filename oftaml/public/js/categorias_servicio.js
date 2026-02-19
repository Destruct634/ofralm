$(document).ready(function() {
    if (USER_GROUP_ID != 1 && (!PERMISOS.categorias_servicio || !PERMISOS.categorias_servicio.crear)) {
        $('#btnNuevo').hide();
    }

    let tabla = $('#tablaCategoriasServicio').DataTable({
        "ajax": { "url": "../app/controllers/CategoriaServicioController.php", "dataSrc": "data" },
        "columns": [
            { "data": "id" }, { "data": "nombre_categoria" },
            { "data": null, "render": function(data, type, row) {
                let botones = "<div class='text-center'><div class='btn-group'>";
                if (USER_GROUP_ID == 1 || (PERMISOS.categorias_servicio && PERMISOS.categorias_servicio.editar == 1)) {
                    botones += "<button class='btn btn-info btn-sm btnEditar'><i class='fas fa-edit'></i></button>";
                }
                if (USER_GROUP_ID == 1 || (PERMISOS.categorias_servicio && PERMISOS.categorias_servicio.borrar == 1)) {
                    botones += "<button class='btn btn-danger btn-sm btnBorrar'><i class='fas fa-trash'></i></button>";
                }
                botones += "</div></div>";
                return botones;
            }}
        ],

    });

    $("#btnNuevo").click(function() {
        $("#formCategoriaServicio").trigger("reset");
        $('#id').val(null);
        $("#modalLabel").text("Nueva Categoría de Servicio");
        $("#modalCategoriaServicio").modal("show");
    });

    $('#formCategoriaServicio').submit(function(e) {
        e.preventDefault();
        let id = $.trim($('#id').val());
        let data = {
            id: id,
            nombre_categoria: $.trim($('#nombre_categoria').val())
        };
        $.ajax({
            url: '../app/controllers/CategoriaServicioController.php',
            type: id ? 'PUT' : 'POST',
            contentType: 'application/json',
            data: JSON.stringify(data),
            success: function(r) { Swal.fire('¡Éxito!', r.message, 'success'); tabla.ajax.reload(null, false); $('#modalCategoriaServicio').modal('hide'); },
            error: function(jqXHR) { Swal.fire('Error', jqXHR.responseJSON.message, 'error'); }
        });
    });

    $(document).on("click", ".btnEditar", function() {
        let data = tabla.row($(this).closest("tr")).data();
        $('#id').val(data.id);
        $('#nombre_categoria').val(data.nombre_categoria);
        $("#modalLabel").text("Editar Categoría de Servicio");
        $("#modalCategoriaServicio").modal("show");
    });

    $(document).on("click", ".btnBorrar", function() {
        let fila = $(this).closest('tr');
        let data = tabla.row(fila).data();
        Swal.fire({
            title: '¿Estás seguro?',
            text: `Se eliminará la categoría "${data.nombre_categoria}".`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Sí, ¡bórralo!'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: '../app/controllers/CategoriaServicioController.php',
                    type: 'DELETE',
                    contentType: 'application/json',
                    data: JSON.stringify({ id: data.id }),
                    success: function(r) { Swal.fire('¡Eliminado!', r.message, 'success'); tabla.row(fila).remove().draw(); },
                    error: function(jqXHR) { Swal.fire('Error', jqXHR.responseJSON.message, 'error'); }
                });
            }
        });
    });
});
