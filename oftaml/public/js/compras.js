$(document).ready(function() {
    if (USER_GROUP_ID != 1 && (!PERMISOS.compras || !PERMISOS.compras.crear)) {
        $('#btnNuevaCompra').hide();
    }

    let tabla = $('#tablaCompras').DataTable({
        "ajax": { "url": "../app/controllers/CompraController.php", "dataSrc": "data" },
        "columns": [
            { "data": "correlativo" }, 
            { "data": "nombre_proveedor" }, 
            { "data": "numero_factura" },
            { "data": "fecha_compra", "render": function(data) { return moment(data).format('DD/MM/YYYY'); } },
            { "data": "total_compra", "render": function(data) { return 'L ' + parseFloat(data).toFixed(2); } },
            { 
                "data": "estado", 
                "render": function(data) {
                    let badge = 'bg-secondary';
                    if (data === 'Recibida') badge = 'bg-success';
                    if (data === 'Cancelada') badge = 'bg-danger';
                    return `<span class="badge ${badge}">${data}</span>`; 
                }
            },
            { 
                "data": null, 
                "render": function(data, type, row) {
                    let botones = "<div class='text-center'><div class='btn-group'>";
                    
                    if (USER_GROUP_ID == 1 || (PERMISOS.compras && PERMISOS.compras.ver == 1)) {
                        botones += "<button class='btn btn-info btn-sm btnVer' title='Ver Detalles'><i class='fas fa-eye'></i></button>";
                        // BOTÓN DE IMPRIMIR MODIFICADO PARA ABRIR EL MODAL
                        botones += `<button class='btn btn-danger btn-sm btnVerPdf' data-id='${row.id}' title='Imprimir Compra'><i class='fas fa-print'></i></button>`;
                    }
                    if (row.estado === 'Borrador') {
                         if (USER_GROUP_ID == 1 || (PERMISOS.compras && PERMISOS.compras.editar == 1)) {
                            botones += "<button class='btn btn-primary btn-sm btnEditar' title='Editar Compra'><i class='fas fa-edit'></i></button>";
                            botones += "<button class='btn btn-warning btn-sm btnCambiarEstado text-white' title='Cambiar Estado'><i class='fas fa-sync-alt'></i></button>";
                        }
                    }
                    botones += "</div></div>";
                    return botones;
                }
            }
        ],

    });

    let tipos_isv_disponibles = [];
    $.get('../app/controllers/ProductoController.php?action=get_isv', function(data) { tipos_isv_disponibles = data; });
    
    function inicializarSelectProveedor(proveedor = null) {
        let select = $('#id_proveedor');
        select.select2({
            theme: "bootstrap-5",
            dropdownParent: $('#modalCompra'),
            placeholder: 'Buscar un proveedor...',
            ajax: {
                url: '../app/controllers/ProveedorController.php?action=search',
                dataType: 'json',
                delay: 250,
                data: function(params) { return { term: params.term }; },
                processResults: function(data) { return { results: data }; }
            }
        });
        if (proveedor) {
            var option = new Option(proveedor.text, proveedor.id, true, true);
            select.append(option).trigger('change');
        }
    }

    function inicializarSelectProducto(selector, producto = null) {
        selector.select2({
            theme: "bootstrap-5",
            dropdownParent: $('#modalCompra'),
            placeholder: 'Buscar un producto...',
            ajax: {
                url: '../app/controllers/ProductoController.php?action=search',
                dataType: 'json',
                delay: 250,
                data: function(params) { return { term: params.term }; },
                processResults: function(data) { return { results: data }; }
            }
        }).on('select2:select', function(e){
            let data = e.params.data;
            let fila = $(this).closest('tr');
            fila.find('.precio').val(parseFloat(data.precio_compra).toFixed(2));
            fila.find('.select-isv').val(data.id_isv);
            actualizarTotal();
        });

        if (producto) {
            var option = new Option(producto.text, producto.id, true, true);
            selector.append(option).trigger('change');
        }
    }

    function actualizarTotal() {
        let subtotalGeneral = 0;
        let isvGeneral = 0;

        $('#detalleCompraBody tr').each(function() {
            let fila = $(this);
            let cantidad = parseFloat(fila.find('.cantidad').val()) || 0;
            let precio = parseFloat(fila.find('.precio').val()) || 0;
            let isv_porcentaje = parseFloat(fila.find('.select-isv option:selected').data('porcentaje')) || 0;

            let subtotalItem = cantidad * precio;
            let isvItem = subtotalItem * (isv_porcentaje / 100);

            fila.find('.subtotal').text(subtotalItem.toFixed(2));

            subtotalGeneral += subtotalItem;
            isvGeneral += isvItem;
        });

        let totalCompra = subtotalGeneral + isvGeneral;

        $('#subtotalGeneral').text('L ' + subtotalGeneral.toFixed(2));
        $('#isvGeneral').text('L ' + isvGeneral.toFixed(2));
        $('#totalCompra').text('L ' + totalCompra.toFixed(2));
    }

    $('#btnAgregarProducto').click(function() {
        let isv_options = tipos_isv_disponibles.map(i => `<option value="${i.id}" data-porcentaje="${i.porcentaje}">${i.nombre_isv}</option>`).join('');
        let filaHtml = `<tr>
            <td><select class="form-select form-select-sm select-producto" style="width:100%;"></select></td>
            <td><input type="number" class="form-control form-control-sm cantidad" value="1" min="1"></td>
            <td><input type="number" step="0.01" class="form-control form-control-sm precio" value="0.00" min="0"></td>
            <td><select class="form-select form-select-sm select-isv">${isv_options}</select></td>
            <td>L <span class="subtotal">0.00</span></td>
            <td><button type="button" class="btn btn-danger btn-sm btnEliminarFila"><i class="fas fa-trash"></i></button></td>
        </tr>`;
        let nuevaFila = $(filaHtml);
        $('#detalleCompraBody').append(nuevaFila);
        inicializarSelectProducto(nuevaFila.find('.select-producto'));
    });

    $(document).on('change keyup', '.cantidad, .precio, .select-isv', function() {
        let fila = $(this).closest('tr');
        let cantidad = parseFloat(fila.find('.cantidad').val()) || 0;
        let precio = parseFloat(fila.find('.precio').val()) || 0;
        let subtotal = cantidad * precio;
        fila.find('.subtotal').text(subtotal.toFixed(2));
        actualizarTotal();
    });

    $(document).on('click', '.btnEliminarFila', function() {
        $(this).closest('tr').remove();
        actualizarTotal();
    });

    $('#btnNuevaCompra').click(function() {
        $('#formCompra').trigger('reset');
        $('#id').val('');
        $('#modalLabel').text('Nueva Compra');
        $('#detalleCompraBody').empty();
        actualizarTotal();
        $('#id_proveedor').html('');
        inicializarSelectProveedor();
        $('#fecha_compra').val(new Date().toISOString().slice(0, 10));
        $('#modalCompra').modal('show');
    });

    $('#formCompra').submit(function(e){
        e.preventDefault();
        let id = $.trim($('#id').val());
        let detalle = [];
        $('#detalleCompraBody tr').each(function() {
            detalle.push({
                id_producto: $(this).find('.select-producto').val(),
                cantidad: $(this).find('.cantidad').val(),
                precio: $(this).find('.precio').val(),
                id_isv: $(this).find('.select-isv').val()
            });
        });
        
        let data = {
            id: id,
            id_proveedor: $('#id_proveedor').val(),
            numero_factura: $('#numero_factura').val(),
            numero_orden: $('#numero_orden').val(),
            fecha_compra: $('#fecha_compra').val(),
            total_compra: parseFloat($('#totalCompra').text().replace('L ', '')),
            detalle: detalle
        };
        
        $.ajax({
            url: '../app/controllers/CompraController.php',
            type: id ? 'PUT' : 'POST',
            contentType: 'application/json',
            data: JSON.stringify(data),
            success: function(r) { Swal.fire('¡Éxito!', r.message, 'success'); tabla.ajax.reload(); $('#modalCompra').modal('hide'); },
            error: function(jqXHR) {
                let errorMessage = 'No se pudo completar la operación.';
                if (jqXHR.responseJSON && jqXHR.responseJSON.message) {
                    errorMessage = jqXHR.responseJSON.message;
                }
                Swal.fire('Error', errorMessage, 'error');
            }
        });
    });

    $(document).on('click', '.btnEditar', function() {
        let data = tabla.row($(this).closest('tr')).data();
        $.get(`../app/controllers/CompraController.php?id=${data.id}`, function(compra) {
            $('#formCompra').trigger('reset');
            $('#id').val(compra.id);
            $('#modalLabel').text(`Editar Compra ${compra.correlativo}`);
            
            inicializarSelectProveedor({id: compra.id_proveedor, text: compra.nombre_proveedor});
            $('#numero_factura').val(compra.numero_factura);
            $('#numero_orden').val(compra.numero_orden);
            $('#fecha_compra').val(compra.fecha_compra);

            let detalleBody = $('#detalleCompraBody');
            detalleBody.empty();
            let isv_options = tipos_isv_disponibles.map(i => `<option value="${i.id}" data-porcentaje="${i.porcentaje}">${i.nombre_isv}</option>`).join('');
            compra.detalle.forEach(item => {
                let filaHtml = `<tr>
                    <td><select class="form-select form-select-sm select-producto" style="width:100%;"></select></td>
                    <td><input type="number" class="form-control form-control-sm cantidad" value="${item.cantidad}" min="1"></td>
                    <td><input type="number" step="0.01" class="form-control form-control-sm precio" value="${item.precio_compra}" min="0"></td>
                    <td><select class="form-select form-select-sm select-isv">${isv_options}</select></td>
                    <td>L <span class="subtotal">${(item.cantidad * item.precio_compra).toFixed(2)}</span></td>
                    <td><button type="button" class="btn btn-danger btn-sm btnEliminarFila"><i class="fas fa-trash"></i></button></td>
                </tr>`;
                let nuevaFila = $(filaHtml);
                nuevaFila.find('.select-isv').val(item.id_isv);
                detalleBody.append(nuevaFila);
                inicializarSelectProducto(nuevaFila.find('.select-producto'), {id: item.id_producto, text: item.nombre_producto});
            });
            actualizarTotal();
            $('#modalCompra').modal('show');
        });
    });

    $(document).on('click', '.btnCambiarEstado', function() {
        let data = tabla.row($(this).closest('tr')).data();
        $('#id_compra_estado').val(data.id);
        $('#correlativo_estado').text(data.correlativo);
        $('#nuevo_estado').val(data.estado);
        $('#modalCambiarEstado').modal('show');
    });

    $('#formCambiarEstado').submit(function(e) {
        e.preventDefault();
        let data = {
            id_compra: $('#id_compra_estado').val(),
            estado: $('#nuevo_estado').val()
        };
        $.ajax({
            url: '../app/controllers/CompraController.php?action=cambiar_estado',
            type: 'PUT',
            contentType: 'application/json',
            data: JSON.stringify(data),
            success: function(r) { Swal.fire('¡Éxito!', r.message, 'success'); tabla.ajax.reload(); $('#modalCambiarEstado').modal('hide'); },
            error: function(jqXHR) { Swal.fire('Error', jqXHR.responseJSON.message, 'error'); }
        });
    });

    $(document).on('click', '.btnVer', function() {
        let data = tabla.row($(this).closest('tr')).data();
        $.get(`../app/controllers/CompraController.php?id=${data.id}`, function(compra) {
            $('#ver_correlativo').text(compra.correlativo);
            $('#ver_proveedor').text(compra.nombre_proveedor);
            $('#ver_factura').text(compra.numero_factura);
            $('#ver_orden').text(compra.numero_orden);
            $('#ver_fecha').text(moment(compra.fecha_compra).format('DD/MM/YYYY'));
            $('#ver_estado').text(compra.estado);
            $('#ver_usuario').text(compra.usuario_nombre);

            let detalleBody = $('#ver_detalle_body');
            detalleBody.empty();
            let subtotalGeneral = 0;
            let isvGeneral = 0;

            compra.detalle.forEach(item => {
                let subtotalItem = parseFloat(item.cantidad) * parseFloat(item.precio_compra);
                let isvItem = subtotalItem * (parseFloat(item.porcentaje) / 100);
                let totalItem = subtotalItem + isvItem;
                
                subtotalGeneral += subtotalItem;
                isvGeneral += isvItem;

                let fila = `<tr>
                    <td>${item.nombre_producto}</td>
                    <td>${item.cantidad}</td>
                    <td>L ${parseFloat(item.precio_compra).toFixed(2)}</td>
                    <td>${item.nombre_isv} (${parseFloat(item.porcentaje).toFixed(2)}%)</td>
                    <td>L ${totalItem.toFixed(2)}</td>
                </tr>`;
                detalleBody.append(fila);
            });

            let totalGeneral = subtotalGeneral + isvGeneral;
            $('#ver_subtotal_general').text('L ' + subtotalGeneral.toFixed(2));
            $('#ver_isv_general').text('L ' + isvGeneral.toFixed(2));
            $('#ver_total_general').text('L ' + totalGeneral.toFixed(2));

            $('#modalVerCompra').modal('show');
        });
    });
    
    // LÓGICA PARA ABRIR EL PDF EN EL MODAL
    $(document).on('click', '.btnVerPdf', function() {
        let idCompra = $(this).data('id');
        let url = `../app/reports/compra_pdf.php?id=${idCompra}`;
        
        $('#pdfFrame').attr('src', url);
        $('#modalPdfViewer').modal('show');
    });

    $('#modalPdfViewer').on('hidden.bs.modal', function () {
        $('#pdfFrame').attr('src', 'about:blank');
    });
});