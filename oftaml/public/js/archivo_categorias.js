$(document).ready(function() {
    let tablaCategorias = $('#tablaCategorias').DataTable({
        "ajax": {
            "url": "../app/controllers/ArchivoCategoriaController.php",
            "type": "GET",
            "dataSrc": "data"
        },
        "columns": [
            { "data": "id" },
            { "data": "nombre_categoria" },
            { 
                "data": "estado",
                "render": function(data, type, row) {
                    if (data === 'Activo') {
                        return '<span class="badge bg-success">Activo</span>';
                    } else {
                        return '<span class="badge bg-secondary">Inactivo</span>';
                    }
                }
            },
            {
                "data": null,
                "defaultContent": "",
                "orderable": false,
                "render": function(data, type, row) {
                    let botones = '';
                    // Asumimos que tienes una variable global PERMISOS o similar
                    if (PERMISOS.archivo_categorias && PERMISOS.archivo_categorias.editar == 1) {
                         botones += `<button class='btn btn-warning btn-sm btnEditar me-1' data-id='${row.id}' title='Editar'><i class='fas fa-edit'></i></button>`;
                    }
                    if (PERMISOS.archivo_categorias && PERMISOS.archivo_categorias.borrar == 1) {
                        botones += `<button class='btn btn-danger btn-sm btnBorrar' data-id='${row.id}' title='Eliminar'><i class='fas fa-trash'></i></button>`;
                    }
                    return `<div class='text-center'>${botones}</div>`;
                }
            }
        ],

    });

    // Abrir modal para NUEVA categoría
    $('#btnNuevaCategoria').click(function() {
        $('#formCategoria').trigger("reset");
        $('#categoria_id').val('');
        $('#modalCategoriaLabel').text('Nueva Categoría');
        $('#modalCategoria').modal('show');
    });

    // Abrir modal para EDITAR categoría
    $('#tablaCategorias tbody').on('click', '.btnEditar', function() {
        let fila = tablaCategorias.row($(this).parents('tr')).data();
        $('#categoria_id').val(fila.id);
        $('#nombre_categoria').val(fila.nombre_categoria);
        $('#estado').val(fila.estado);
        $('#modalCategoriaLabel').text('Editar Categoría');
        $('#modalCategoria').modal('show');
    });

    // SUBMIT del formulario (Crear o Editar)
    $('#formCategoria').submit(function(e) {
        e.preventDefault();
        let id = $('#categoria_id').val();
        let url = id ? `../app/controllers/ArchivoCategoriaController.php` : '../app/controllers/ArchivoCategoriaController.php';
        let method = id ? 'PUT' : 'POST';

        let data = {
            nombre_categoria: $('#nombre_categoria').val(),
            estado: $('#estado').val()
        };
        if (id) { data.id = id; }

        $.ajax({
            url: url,
            type: method,
            contentType: 'application/json',
            data: JSON.stringify(data),
            success: function(response) {
                $('#modalCategoria').modal('hide');
                Swal.fire('¡Éxito!', response.message, 'success');
                tablaCategorias.ajax.reload();
            },
            error: function(jqXHR) {
                let msg = jqXHR.responseJSON ? jqXHR.responseJSON.message : 'Error en la operación.';
                Swal.fire('Error', msg, 'error');
            }
        });
    });

    // BORRAR categoría
    $('#tablaCategorias tbody').on('click', '.btnBorrar', function() {
        let id = $(this).data('id');
        Swal.fire({
            title: '¿Estás seguro?',
            text: "¡Esta acción no se puede revertir!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Sí, ¡bórralo!'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: `../app/controllers/ArchivoCategoriaController.php`,
                    type: 'DELETE',
                    contentType: 'application/json',
                    data: JSON.stringify({ id: id }),
                    success: function(response) {
                        Swal.fire('¡Eliminado!', response.message, 'success');
                        tablaCategorias.ajax.reload();
                    },
                    error: function(jqXHR) {
                         let msg = jqXHR.responseJSON ? jqXHR.responseJSON.message : 'Error en la operación.';
                         Swal.fire('Error', msg, 'error');
                    }
                });
            }
        });
    });
});