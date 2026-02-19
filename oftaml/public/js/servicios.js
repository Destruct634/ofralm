$(document).ready(function() {
    if (USER_GROUP_ID != 1 && (!PERMISOS.servicios || !PERMISOS.servicios.crear != 1)) {
        $('#btnNuevo').hide();
    }

    let tabla = $('#tablaServicios').DataTable({
        "ajax": { "url": "../app/controllers/ServicioController.php", "dataSrc": "data" },
        "columns": [
            { "data": "id" }, { "data": "codigo" }, { "data": "nombre_servicio" },
            { "data": "nombre_categoria_servicio", "defaultContent": "<i>N/A</i>" },
            { "data": "precio_venta", "render": function(data) { return 'L ' + parseFloat(data).toFixed(2); }},
            { 
                "data": "mostrar_en_citas", 
                "render": function(data) {
                    return data == 1 ? '<span class="badge bg-success">Sí</span>' : '<span class="badge bg-secondary">No</span>';
                }
            },
            { "data": null, "render": function(data, type, row) {
                let botones = "<div class='text-center'><div class='btn-group'>";
                if (USER_GROUP_ID == 1 || (PERMISOS.servicios && PERMISOS.servicios.editar == 1)) {
                    botones += "<button class='btn btn-info btn-sm btnEditar'><i class='fas fa-edit'></i></button>";
                }
                if (USER_GROUP_ID == 1 || (PERMISOS.servicios && PERMISOS.servicios.borrar == 1)) {
                    botones += "<button class='btn btn-danger btn-sm btnBorrar'><i class='fas fa-trash'></i></button>";
                }
                botones += "</div></div>";
                return botones;
            }}
        ],

    });

    function cargarCategorias(selectedId = null) {
        $.get('../app/controllers/ServicioController.php?action=get_categorias', function(categorias) {
            let selector = $('#id_categoria_servicio');
            selector.html('<option value="">Seleccione...</option>');
            categorias.forEach(c => { selector.append(`<option value="${c.id}">${c.nombre_categoria}</option>`); });
            if (selectedId) selector.val(selectedId);
        });
    }
    
    function cargarTiposIsv(selectedId = null) {
        $.get('../app/controllers/ServicioController.php?action=get_isv', function(tipos) {
            let selector = $('#id_isv');
            selector.html('');
            tipos.forEach(t => { selector.append(`<option value="${t.id}">${t.nombre_isv}</option>`); });
            if (selectedId) selector.val(selectedId);
        });
    }

    $("#btnNuevo").click(function() {
        $("#formServicio").trigger("reset");
        $('#id').val(null);
        $("#modalLabel").text("Nuevo Servicio");
        cargarCategorias();
        cargarTiposIsv();
        $("#modalServicio").modal("show");
    });
    
    $('#formServicio').submit(function(e) { e.preventDefault();
        let id = $.trim($('#id').val());
        let data = {
            id: id,
            codigo: $.trim($('#codigo').val()),
            nombre_servicio: $.trim($('#nombre_servicio').val()),
            descripcion: $.trim($('#descripcion').val()),
            id_categoria_servicio: $.trim($('#id_categoria_servicio').val()),
            id_isv: $.trim($('#id_isv').val()),
            precio_venta: $.trim($('#precio_venta').val()),
            mostrar_en_citas: $('#mostrar_en_citas').is(':checked') ? 1 : 0
        };
        $.ajax({
            url: '../app/controllers/ServicioController.php', type: id ? 'PUT' : 'POST',
            contentType: 'application/json', data: JSON.stringify(data),
            success: function(r) { Swal.fire('¡Éxito!', r.message, 'success'); tabla.ajax.reload(null, false); $('#modalServicio').modal('hide'); },
            error: function(jqXHR) { Swal.fire('Error', jqXHR.responseJSON.message, 'error'); }
        });
    });

    $(document).on("click", ".btnEditar", function() {
        let fila = $(this).closest('tr');
        let data = tabla.row(fila).data();
        
        $.get(`../app/controllers/ServicioController.php?id=${data.id}`, function(servicio) {
            $('#id').val(servicio.id);
            $('#codigo').val(servicio.codigo);
            $('#nombre_servicio').val(servicio.nombre_servicio);
            $('#descripcion').val(servicio.descripcion);
            $('#precio_venta').val(servicio.precio_venta);
            $('#mostrar_en_citas').prop('checked', servicio.mostrar_en_citas == 1);
            cargarCategorias(servicio.id_categoria_servicio);
            cargarTiposIsv(servicio.id_isv);
            $("#modalLabel").text("Editar Servicio");
            $("#modalServicio").modal("show");
        });
    });

    $(document).on("click", ".btnBorrar", function() {
        let fila = $(this).closest('tr');
        let data = tabla.row(fila).data();
        Swal.fire({
            title: '¿Estás seguro?', text: `Se eliminará el servicio "${data.nombre_servicio}".`,
            icon: 'warning', showCancelButton: true, confirmButtonText: 'Sí, ¡bórralo!'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: '../app/controllers/ServicioController.php',
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
