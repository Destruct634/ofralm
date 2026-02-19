$(document).ready(function() {
    if (USER_GROUP_ID != 1 && (!PERMISOS.textos_predefinidos || PERMISOS.textos_predefinidos.crear != 1)) {
        $('#btnNuevo').hide();
    }

    $('#modalTexto').on('shown.bs.modal', function () {
        $('#contenido').summernote({
            placeholder: 'Escriba el contenido con formato aquí...',
            lang: 'es-ES',
            height: 200,
            toolbar: [
                ['style', ['bold', 'italic', 'underline', 'clear']],
                ['para', ['ul', 'ol', 'paragraph']],
                ['insert', ['link']]
            ]
        });
    }).on('hidden.bs.modal', function(){
        $('#contenido').summernote('destroy');
    });

    let tabla = $('#tablaTextos').DataTable({
        "ajax": { "url": "../app/controllers/TextoPredefinidoController.php", "dataSrc": "data" },
        "columns": [
            { "data": "id" }, { "data": "titulo" },
            { "data": "estado", "render": function(data) {
                return data === 'Activo' ? '<span class="badge bg-success">Activo</span>' : '<span class="badge bg-danger">Inactivo</span>';
            }},
            { 
                "data": null, 
                "render": function(data, type, row) {
                    let botones = "<div class='text-center'><div class='btn-group'>";
                    if (USER_GROUP_ID == 1 || (PERMISOS.textos_predefinidos && PERMISOS.textos_predefinidos.editar == 1)) {
                        botones += "<button class='btn btn-info btn-sm btnEditar'><i class='fas fa-edit'></i></button>";
                    }
                    if (USER_GROUP_ID == 1 || (PERMISOS.textos_predefinidos && PERMISOS.textos_predefinidos.borrar == 1)) {
                        botones += "<button class='btn btn-danger btn-sm btnBorrar'><i class='fas fa-trash'></i></button>";
                    }
                    botones += "</div></div>";
                    return botones;
                }
            }
        ],

    });

    $("#btnNuevo").click(function() {
        $("#formTexto").trigger("reset");
        $('#id').val(null);
        $('#estado').val('Activo');
        if ($('#contenido').hasClass('note-editor')) {
            $('#contenido').summernote('code', '');
        }
        $("#modalLabel").text("Nuevo Texto Predefinido");
        $("#modalTexto").modal("show");
    });

    $('#formTexto').submit(function(e) {
        e.preventDefault();
        let id = $.trim($('#id').val());
        let data = {
            id: id,
            titulo: $.trim($('#titulo').val()),
            contenido: $('#contenido').summernote('code'),
            estado: $.trim($('#estado').val())
        };
        $.ajax({
            url: '../app/controllers/TextoPredefinidoController.php',
            type: id ? 'PUT' : 'POST',
            contentType: 'application/json',
            data: JSON.stringify(data),
            success: function(response) {
                Swal.fire('¡Éxito!', response.message, 'success');
                tabla.ajax.reload(null, false);
                $('#modalTexto').modal('hide');
            },
            error: function(){
                Swal.fire('Error', 'No se pudo completar la operación.', 'error');
            }
        });
    });

    $(document).on("click", ".btnEditar", function() {
        let data = tabla.row($(this).closest("tr")).data();
        $('#id').val(data.id);
        $('#titulo').val(data.titulo);
        $('#estado').val(data.estado);
        $("#modalLabel").text("Editar Texto Predefinido");
        $("#modalTexto").modal("show");
        setTimeout(() => $('#contenido').summernote('code', data.contenido), 200);
    });

    $(document).on("click", ".btnBorrar", function() {
        let data = tabla.row($(this).closest('tr')).data();
        Swal.fire({
            title: '¿Estás seguro?', text: "¡No podrás revertir esto!",
            icon: 'warning', showCancelButton: true, confirmButtonText: 'Sí, ¡bórralo!'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: '../app/controllers/TextoPredefinidoController.php',
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