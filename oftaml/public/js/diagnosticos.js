$(document).ready(function() {
    let tablaDiagnosticos = $('#tablaDiagnosticos').DataTable({
        "ajax": { 
            "url": "../app/controllers/DiagnosticoController.php", 
            "dataSrc": "data" 
        },
        "columns": [
            { "data": "id" },
            { "data": "codigo" },
            { "data": "descripcion" },
            { 
                "data": "estado",
                "render": function(data, type, row) {
                    let badgeClass = data === 'Activo' ? 'bg-success' : 'bg-danger';
                    return `<span class="badge ${badgeClass}">${data}</span>`;
                }
            },
            { 
                "data": null,
                "render": function(data, type, row) {
                    let botones = "<div class='text-center'><div class='btn-group'>";
                    
                    // Asumimos permisos de 'consulta_plantillas' o 'pacientes'
                    if (USER_GROUP_ID == 1 || (PERMISOS.consulta_plantillas && PERMISOS.consulta_plantillas.editar == 1) || (PERMISOS.pacientes && PERMISOS.pacientes.editar == 1)) {
                        botones += "<button class='btn btn-info btn-sm btnEditar' title='Editar Diagnóstico'><i class='fas fa-edit'></i></button>";
                    }
                    if (USER_GROUP_ID == 1 || (PERMISOS.consulta_plantillas && PERMISOS.consulta_plantillas.borrar == 1) || (PERMISOS.pacientes && PERMISOS.pacientes.borrar == 1)) {
                        botones += "<button class='btn btn-danger btn-sm btnBorrar' title='Eliminar Diagnóstico'><i class='fas fa-trash'></i></button>";
                    }
                    botones += "</div></div>";
                    return botones;
                },
                "orderable": false
            }
        ],

    });

    // 1. Abrir modal para NUEVO
    $("#btnNuevoDiagnostico").click(function() {
        $("#formDiagnostico").trigger("reset");
        $('#diagnostico_id').val(null);
        $('#diagnostico_estado').val('Activo');
        $(".modal-header").css("background-color", "#0d6efd").css("color", "white");
        $(".modal-title").text("Nuevo Diagnóstico");
        $("#modalDiagnostico").modal("show");
    });

    // 2. Abrir modal para EDITAR
    $(document).on("click", ".btnEditar", function() {
        let data = tablaDiagnosticos.row($(this).closest("tr")).data();
        
        $('#diagnostico_id').val(data.id);
        $('#diagnostico_codigo').val(data.codigo);
        $('#diagnostico_descripcion').val(data.descripcion);
        $('#diagnostico_estado').val(data.estado);
        
        $(".modal-header").css("background-color", "#17a2b8").css("color", "white");
        $(".modal-title").text("Editar Diagnóstico");
        $("#modalDiagnostico").modal("show");
    });

    // 3. Submit para CREAR o EDITAR
    $('#formDiagnostico').submit(function(e) {
        e.preventDefault();
        let id = $.trim($('#diagnostico_id').val());
        let ajaxType = id ? 'PUT' : 'POST';
        
        let data = {
            id: id,
            codigo: $.trim($('#diagnostico_codigo').val()),
            descripcion: $.trim($('#diagnostico_descripcion').val()),
            estado: $.trim($('#diagnostico_estado').val())
        };

        $.ajax({
            url: '../app/controllers/DiagnosticoController.php',
            type: ajaxType,
            contentType: 'application/json',
            data: JSON.stringify(data),
            success: function(response) {
                Swal.fire('¡Éxito!', response.message, 'success');
                tablaDiagnosticos.ajax.reload(null, false);
                $('#modalDiagnostico').modal('hide');
            },
            error: function(jqXHR) {
                let errorMessage = jqXHR.responseJSON ? jqXHR.responseJSON.message : 'No se pudo completar la operación.';
                Swal.fire('Error', errorMessage, 'error');
            }
        });
    });

    // 4. Eliminar
    $(document).on("click", ".btnBorrar", function() {
        let fila = $(this).closest('tr');
        let data = tablaDiagnosticos.row(fila).data();
        
        Swal.fire({
            title: '¿Estás seguro?',
            text: `¿Eliminar el diagnóstico "${data.descripcion}"? (ID: ${data.id})`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            confirmButtonText: 'Sí, ¡bórralo!'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: '../app/controllers/DiagnosticoController.php',
                    type: 'DELETE',
                    contentType: 'application/json',
                    data: JSON.stringify({ id: data.id }),
                    success: function(response) {
                        Swal.fire('¡Eliminado!', response.message, 'success');
                        tablaDiagnosticos.row(fila).remove().draw();
                    },
                    error: function(jqXHR) {
                        let errorMessage = jqXHR.responseJSON ? jqXHR.responseJSON.message : 'No se pudo eliminar.';
                        Swal.fire('Error', errorMessage, 'error');
                    }
                });
            }
        });
    });
});