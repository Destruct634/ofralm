$(document).ready(function() {
    let tablaPlantillas = $('#tablaPlantillas').DataTable({
        "ajax": {
            "url": "../app/controllers/ConsultaPlantillaController.php",
            "type": "GET",
            "dataSrc": "data"
        },
        "columns": [
            { "data": "id" },
            { "data": "titulo" },
            { 
                "data": "estado",
                "render": function(data, type, row) {
                    return data === 'Activo' 
                        ? '<span class="badge bg-success">Activo</span>' 
                        : '<span class="badge bg-secondary">Inactivo</span>';
                }
            },
            {
                "data": null,
                "orderable": false,
                "render": function(data, type, row) {
                    let botones = '';
                    if (PERMISOS.consulta_plantillas && PERMISOS.consulta_plantillas.editar == 1) {
                         botones += `<button class='btn btn-warning btn-sm btnEditar me-1' data-id='${row.id}' title='Editar'><i class='fas fa-edit'></i></button>`;
                    }
                    if (PERMISOS.consulta_plantillas && PERMISOS.consulta_plantillas.borrar == 1) {
                        botones += `<button class='btn btn-danger btn-sm btnBorrar' data-id='${row.id}' title='Eliminar'><i class='fas fa-trash'></i></button>`;
                    }
                    return `<div class='text-center'>${botones}</div>`;
                }
            }
        ],

    });

    // --- Manejo de Summernote en el Modal ---
    $('#modalPlantilla').on('shown.bs.modal', function () {
        $('#contenido').summernote({
            placeholder: 'Escribe el contenido de la plantilla aquí...',
            lang: 'es-ES',
            height: 200,
            toolbar: [
                ['style', ['bold', 'italic', 'underline', 'clear']],
                ['font', ['strikethrough']],
                ['para', ['ul', 'ol', 'paragraph']]
            ]
        });
    }).on('hidden.bs.modal', function () {
        $('#contenido').summernote('destroy');
    });


    // --- Lógica CRUD ---

    // Abrir modal para NUEVA plantilla
    $('#btnNuevaPlantilla').click(function() {
        $('#formPlantilla').trigger("reset");
        $('#plantilla_id').val('');
        $('#modalPlantillaLabel').text('Nueva Plantilla');
        $('#contenido').val(''); // Limpiar textarea antes de mostrar
        $('#modalPlantilla').modal('show');
    });

    // Abrir modal para EDITAR plantilla
    $('#tablaPlantillas tbody').on('click', '.btnEditar', function() {
        let id = $(this).data('id');
        
        // Hacemos una llamada para obtener todos los datos, incluido el 'contenido'
        $.ajax({
            url: `../app/controllers/ConsultaPlantillaController.php?id=${id}`,
            type: 'GET',
            success: function(plantilla) {
                $('#plantilla_id').val(plantilla.id);
                $('#titulo').val(plantilla.titulo);
                $('#estado_plantilla').val(plantilla.estado);
                
                // Limpiamos el contenido anterior antes de poblar
                $('#contenido').val(plantilla.contenido);
                
                $('#modalPlantillaLabel').text('Editar Plantilla');
                $('#modalPlantilla').modal('show');
                
                // Forzamos la actualización del editor Summernote con el nuevo contenido
                $('#modalPlantilla').one('shown.bs.modal', function () {
                    $('#contenido').summernote('code', plantilla.contenido);
                });
            },
            error: function() {
                Swal.fire('Error', 'No se pudieron cargar los datos de la plantilla.', 'error');
            }
        });
    });

    // SUBMIT del formulario (Crear o Editar)
    $('#formPlantilla').submit(function(e) {
        e.preventDefault();
        let id = $('#plantilla_id').val();
        let method = id ? 'PUT' : 'POST';

        let data = {
            titulo: $('#titulo').val(),
            contenido: $('#contenido').summernote('code'), // Obtenemos el HTML del editor
            estado: $('#estado_plantilla').val()
        };
        if (id) { data.id = id; }

        $.ajax({
            url: '../app/controllers/ConsultaPlantillaController.php',
            type: method,
            contentType: 'application/json',
            data: JSON.stringify(data),
            success: function(response) {
                $('#modalPlantilla').modal('hide');
                Swal.fire('¡Éxito!', response.message, 'success');
                tablaPlantillas.ajax.reload();
            },
            error: function(jqXHR) {
                let msg = jqXHR.responseJSON ? jqXHR.responseJSON.message : 'Error en la operación.';
                Swal.fire('Error', msg, 'error');
            }
        });
    });

    // BORRAR plantilla
    $('#tablaPlantillas tbody').on('click', '.btnBorrar', function() {
        let id = $(this).data('id');
        Swal.fire({
            title: '¿Estás seguro?',
            text: "Esta acción no se puede revertir.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Sí, ¡bórrala!'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: `../app/controllers/ConsultaPlantillaController.php`,
                    type: 'DELETE',
                    contentType: 'application/json',
                    data: JSON.stringify({ id: id }),
                    success: function(response) {
                        Swal.fire('¡Eliminada!', response.message, 'success');
                        tablaPlantillas.ajax.reload();
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