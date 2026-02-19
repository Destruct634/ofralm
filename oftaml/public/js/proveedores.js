$(document).ready(function() {
    if (USER_GROUP_ID != 1 && (!PERMISOS.proveedores || PERMISOS.proveedores.crear != 1)) {
        $('#btnNuevo').hide();
    }

    let tabla = $('#tablaProveedores').DataTable({
        "ajax": { "url": "../app/controllers/ProveedorController.php", "dataSrc": "data" },
        "columns": [
            { "data": "id" }, { "data": "nombre_proveedor" }, { "data": "contacto" },
            { "data": "telefono" }, { "data": "email" },
            { "data": "estado", "render": function(data) { return data === 'Activo' ? '<span class="badge bg-success">Activo</span>' : '<span class="badge bg-danger">Inactivo</span>'; }},
            { "data": null, "render": function(data, type, row) {
                let botones = "<div class='text-center'><div class='btn-group'>";
                if (USER_GROUP_ID == 1 || (PERMISOS.proveedores && PERMISOS.proveedores.editar == 1)) {
                    botones += "<button class='btn btn-info btn-sm btnEditar'><i class='fas fa-edit'></i></button>";
                }
                if (USER_GROUP_ID == 1 || (PERMISOS.proveedores && PERMISOS.proveedores.borrar == 1)) {
                    botones += "<button class='btn btn-danger btn-sm btnBorrar'><i class='fas fa-trash'></i></button>";
                }
                botones += "</div></div>";
                return botones;
            }}
        ],

    });

    $("#btnNuevo").click(function() {
        $("#formProveedor").trigger("reset");
        $('#id').val(null);
        $('#estado').val('Activo');
        $("#modalLabel").text("Nuevo Proveedor");
        $("#modalProveedor").modal("show");
    });

    $('#formProveedor').submit(function(e) {
        e.preventDefault();
        let id = $.trim($('#id').val());
        let data = {
            id: id, nombre_proveedor: $.trim($('#nombre_proveedor').val()),
            contacto: $.trim($('#contacto').val()), telefono: $.trim($('#telefono').val()),
            email: $.trim($('#email').val()), estado: $.trim($('#estado').val())
        };
        $.ajax({
            url: '../app/controllers/ProveedorController.php',
            type: id ? 'PUT' : 'POST',
            contentType: 'application/json',
            data: JSON.stringify(data),
            success: function(r) { Swal.fire('¡Éxito!', r.message, 'success'); tabla.ajax.reload(null, false); $('#modalProveedor').modal('hide'); },
            error: function(jqXHR) { Swal.fire('Error', jqXHR.responseJSON.message, 'error'); }
        });
    });

    $(document).on("click", ".btnEditar", function() {
        let data = tabla.row($(this).closest("tr")).data();
        $('#id').val(data.id);
        $('#nombre_proveedor').val(data.nombre_proveedor);
        $('#contacto').val(data.contacto);
        $('#telefono').val(data.telefono);
        $('#email').val(data.email);
        $('#estado').val(data.estado);
        $("#modalLabel").text("Editar Proveedor");
        $("#modalProveedor").modal("show");
    });

    $(document).on("click", ".btnBorrar", function() {
        let fila = $(this).closest('tr');
        let data = tabla.row(fila).data();
        Swal.fire({
            title: '¿Estás seguro?', text: `Se eliminará el proveedor "${data.nombre_proveedor}".`,
            icon: 'warning', showCancelButton: true, confirmButtonText: 'Sí, ¡bórralo!'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: '../app/controllers/ProveedorController.php',
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