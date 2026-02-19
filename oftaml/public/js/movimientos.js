$(document).ready(function() {
    if (USER_GROUP_ID != 1 && (!PERMISOS.inventario || !PERMISOS.inventario.crear)) {
        $('#btnNuevaEntrada, #btnNuevaSalida, #btnAjustarStock').hide();
    }

    let tabla = $('#tablaMovimientos').DataTable({
        "ajax": { "url": "../app/controllers/MovimientoController.php", "dataSrc": "data" },
        
        // --- INICIO DE CORRECCIÓN: Ordenar por Fecha (índice 1) en Descendente ---
        "order": [[ 1, "desc" ]],
        // --- FIN DE CORRECCIÓN ---

        "columns": [
            { "data": "id" }, // Índice 0
            { "data": "fecha_movimiento", "render": function(data){ return moment(data).format('DD/MM/YYYY hh:mm A'); } }, // Índice 1 (Fecha)
            { "data": "nombre_producto" },
            { "data": "tipo_movimiento", "render": function(data){
                let badge = '';
                if (data === 'Entrada') badge = 'bg-success';
                else if (data === 'Salida') badge = 'bg-danger';
                else if (data === 'Ajuste') badge = 'bg-warning text-dark';
                else if (data === 'Venta') badge = 'bg-primary'; 
                return `<span class="badge ${badge}">${data}</span>`;
            }},
            { "data": "cantidad", "render": function(data, type, row) {
                if (row.tipo_movimiento === 'Entrada' || (row.tipo_movimiento === 'Ajuste' && data > 0)) {
                    return `+${data}`;
                }
                if (row.tipo_movimiento === 'Salida') {
                    return `-${data}`;
                }
                return data;
            }},
            { "data": "usuario" },
            { "data": "nombre_proveedor", "render": function(data, type, row){
                return data ? data : '<em>Interno</em>';
            }},
            { "data": "notas" }
        ],

    });

    let productos_disponibles = [];
    $.get('../app/controllers/MovimientoController.php?action=get_productos', function(data) { productos_disponibles = data; });

    function inicializarSelectProducto(selector) {
        selector.select2({
            theme: "bootstrap-5",
            dropdownParent: selector.closest('.modal'),
            placeholder: 'Buscar un producto...',
            data: productos_disponibles.map(p => ({id: p.id, text: p.nombre_producto}))
        });
    }

    // --- LÓGICA PARA ENTRADAS ---
    $("#btnNuevaEntrada").click(function() {
        $("#formEntrada").trigger("reset");
        $('#detalleEntradaBody').empty();
        $("#modalEntrada").modal("show");
    });

    $('#btnAgregarProductoEntrada').click(function() {
        let filaHtml = `<tr>
            <td><select class="form-select form-select-sm select-producto" style="width:100%;"></select></td>
            <td><input type="number" class="form-control form-control-sm cantidad" value="1" min="1"></td>
            <td><button type="button" class="btn btn-danger btn-sm btnEliminarFila"><i class="fas fa-trash"></i></button></td>
        </tr>`;
        let nuevaFila = $(filaHtml);
        $('#detalleEntradaBody').append(nuevaFila);
        inicializarSelectProducto(nuevaFila.find('.select-producto'));
    });

    $('#formEntrada').submit(function(e) {
        e.preventDefault();
        let detalle = [];
        $('#detalleEntradaBody tr').each(function() {
            detalle.push({
                id_producto: $(this).find('.select-producto').val(),
                cantidad: $(this).find('.cantidad').val()
            });
        });
        let data = {
            tipo_movimiento: 'entrada',
            notas: $('#notas_entrada').val(),
            detalle: detalle
        };
        $.ajax({
            url: '../app/controllers/MovimientoController.php', type: 'POST',
            contentType: 'application/json', data: JSON.stringify(data),
            success: function(r) { Swal.fire('¡Éxito!', r.message, 'success'); tabla.ajax.reload(); $('#modalEntrada').modal('hide'); },
            error: function(jqXHR) { Swal.fire('Error', jqXHR.responseJSON.message, 'error'); }
        });
    });

    // --- LÓGICA PARA SALIDAS ---
    $("#btnNuevaSalida").click(function() {
        $("#formSalida").trigger("reset");
        $('#detalleSalidaBody').empty();
        $("#modalSalida").modal("show");
    });

    $('#btnAgregarProductoSalida').click(function() {
        let filaHtml = `<tr>
            <td><select class="form-select form-select-sm select-producto" style="width:100%;"></select></td>
            <td><input type="number" class="form-control form-control-sm cantidad" value="1" min="1"></td>
            <td><button type="button" class="btn btn-danger btn-sm btnEliminarFila"><i class="fas fa-trash"></i></button></td>
        </tr>`;
        let nuevaFila = $(filaHtml);
        $('#detalleSalidaBody').append(nuevaFila);
        inicializarSelectProducto(nuevaFila.find('.select-producto'));
    });

    $('#formSalida').submit(function(e) {
        e.preventDefault();
        let detalle = [];
        $('#detalleSalidaBody tr').each(function() {
            detalle.push({
                id_producto: $(this).find('.select-producto').val(),
                cantidad: $(this).find('.cantidad').val()
            });
        });
        let data = {
            tipo_movimiento: 'salida',
            notas: $('#notas_salida').val(),
            detalle: detalle
        };
        $.ajax({
            url: '../app/controllers/MovimientoController.php', type: 'POST',
            contentType: 'application/json', data: JSON.stringify(data),
            success: function(r) { Swal.fire('¡Éxito!', r.message, 'success'); tabla.ajax.reload(); $('#modalSalida').modal('hide'); },
            error: function(jqXHR) { Swal.fire('Error', jqXHR.responseJSON.message, 'error'); }
        });
    });

    // --- LÓGICA PARA AJUSTES ---
    $('#btnAjustarStock').click(function() {
        $('#formAjuste').trigger('reset');
        let selector = $('#ajuste_id_producto');
        selector.html('<option value="">Seleccione un producto...</option>');
        productos_disponibles.forEach(p => {
             if(p.es_inventariable == 1) {
                selector.append(`<option value="${p.id}" data-stock="${p.stock_actual}">${p.nombre_producto}</option>`)
             }
        });
        selector.select2({ theme: "bootstrap-5", dropdownParent: $('#modalAjuste') });
        $('#ajuste_diferencia').removeClass('text-success text-danger');
        $('#modalAjuste').modal('show');
    });

    $('#ajuste_id_producto').on('change', function(){
        let stockSistema = $(this).find(':selected').data('stock') || 0;
        $('#ajuste_stock_sistema').val(stockSistema);
        $('#ajuste_stock_real').trigger('keyup');
    });

    $('#ajuste_stock_real').on('keyup change', function() {
        let stockSistema = parseFloat($('#ajuste_stock_sistema').val()) || 0;
        let stockReal = parseFloat($(this).val()) || 0;
        let diferencia = stockReal - stockSistema;

        let inputDiferencia = $('#ajuste_diferencia');
        inputDiferencia.val(diferencia);

        inputDiferencia.removeClass('text-success text-danger');
        if (diferencia > 0) {
            inputDiferencia.addClass('text-success').val(`+${diferencia}`);
        } else if (diferencia < 0) {
            inputDiferencia.addClass('text-danger');
        }
    });

    $('#formAjuste').submit(function(e) {
        e.preventDefault();
        let diferencia = parseFloat($('#ajuste_diferencia').val()) || 0;
        let data = {
            tipo_movimiento: 'ajuste',
            id_producto: $('#ajuste_id_producto').val(),
            cantidad: diferencia,
            notas: $('#ajuste_notas').val()
        };

        $.ajax({
            url: '../app/controllers/MovimientoController.php', type: 'POST',
            contentType: 'application/json', data: JSON.stringify(data),
            success: function(r) { Swal.fire('¡Éxito!', r.message, 'success'); tabla.ajax.reload(); $('#modalAjuste').modal('hide'); },
            error: function(jqXHR) { Swal.fire('Error', jqXHR.responseJSON.message, 'error'); }
        });
    });

    // Botón eliminar fila (común para entradas/salidas)
    $(document).on('click', '.btnEliminarFila', function() {
        $(this).closest('tr').remove();
    });
});