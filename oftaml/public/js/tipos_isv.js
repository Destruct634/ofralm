$(document).ready(function() {
    if (USER_GROUP_ID != 1 && (!PERMISOS.tipos_isv || PERMISOS.tipos_isv.crear != 1)) {
        $('#btnNuevo').hide();
    }

    let tabla = $('#tablaTiposIsv').DataTable({
        "ajax": { "url": "../app/controllers/TipoIsvController.php", "dataSrc": "data" },
        "columns": [
            { "data": "id" }, { "data": "nombre_isv" }, { "data": "porcentaje" },
            { "data": null, "render": function(data, type, row) {
                let botones = "<div class='text-center'><div class='btn-group'>";
                if (USER_GROUP_ID == 1 || (PERMISOS.tipos_isv && PERMISOS.tipos_isv.editar == 1)) {
                    botones += "<button class='btn btn-info btn-sm btnEditar'><i class='fas fa-edit'></i></button>";
                }
                if (USER_GROUP_ID == 1 || (PERMISOS.tipos_isv && PERMISOS.tipos_isv.borrar == 1)) {
                    botones += "<button class='btn btn-danger btn-sm btnBorrar'><i class='fas fa-trash'></i></button>";
                }
                botones += "</div></div>";
                return botones;
            }}
        ],

    });

    $("#btnNuevo").click(function() {
        $("#formTipoIsv").trigger("reset");
        $('#id').val(null);
        $("#modalLabel").text("Nuevo Tipo de ISV");
        $("#modalTipoIsv").modal("show");
    });

    $('#formTipoIsv').submit(function(e) {
        e.preventDefault();
        let id = $.trim($('#id').val());
        let data = {
            id: id,
            nombre_isv: $.trim($('#nombre_isv').val()),
            porcentaje: $.trim($('#porcentaje').val())
        };
        $.ajax({
            url: '../app/controllers/TipoIsvController.php',
            type: id ? 'PUT' : 'POST',
            contentType: 'application/json',
            data: JSON.stringify(data),
            success: function(r) { Swal.fire('¡Éxito!', r.message, 'success'); tabla.ajax.reload(null, false); $('#modalTipoIsv').modal('hide'); },
            error: function(jqXHR) { Swal.fire('Error', jqXHR.responseJSON.message, 'error'); }
        });
    });

    $(document).on("click", ".btnEditar", function() {
        let data = tabla.row($(this).closest("tr")).data();
        $('#id').val(data.id);
        $('#nombre_isv').val(data.nombre_isv);
        $('#porcentaje').val(data.porcentaje);
        $("#modalLabel").text("Editar Tipo de ISV");
        $("#modalTipoIsv").modal("show");
    });

    $(document).on("click", ".btnBorrar", function() {
        let fila = $(this).closest('tr');
        let data = tabla.row(fila).data();
        Swal.fire({
            title: '¿Estás seguro?', text: `Se eliminará el tipo de ISV "${data.nombre_isv}".`,
            icon: 'warning', showCancelButton: true, confirmButtonText: 'Sí, ¡bórralo!'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: '../app/controllers/TipoIsvController.php',
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