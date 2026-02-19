$(document).ready(function() {
    let tablaNC = $('#tablaNotasCredito').DataTable({
        "ajax": { "url": "../app/controllers/NotaCreditoController.php", "dataSrc": "data" },
        "columns": [
            { "data": "correlativo" },
            { "data": "factura_asociada" },
            { "data": "paciente_nombre" },
            { "data": "fecha_emision", "render": function(data) { return moment(data).format('DD/MM/YYYY'); } },
            { "data": "total", "render": function(data) { return 'L ' + parseFloat(data).toFixed(2); } },
            { "data": "estado" },
            { 
                "data": null, 
                "render": function(data, type, row) {
                    return `<div class="text-center"><div class="btn-group">
                        <button class="btn btn-info btn-sm btnVerNotaCredito" data-id="${row.id}" title="Ver Detalles"><i class="fas fa-eye"></i></button>
                        <button class="btn btn-danger btn-sm btnImprimirNotaCredito" data-id="${row.id}" title="Imprimir PDF"><i class="fas fa-print"></i></button>
                    </div></div>`;
                }
            }
        ],

    });

    let tipos_isv_disponibles = [];
    $.get('../app/controllers/ProductoController.php?action=get_isv', function(data) { tipos_isv_disponibles = data; });

    function calcularTotalesNC() {
        let subtotalGeneral = 0;
        let isvGeneral = 0;
        $('#itemsFacturaBody tr').each(function() {
            let fila = $(this);
            let cantidad = parseInt(fila.find('.cantidad-devolver').val()) || 0;
            let precio = parseFloat(fila.data('precio')) || 0;
            let isv_porcentaje = parseFloat(fila.data('isv-porcentaje')) || 0;
            let subtotalItem = cantidad * precio;
            let isvItem = subtotalItem * (isv_porcentaje / 100);
            fila.find('.subtotal-nc').text('L ' + subtotalItem.toFixed(2));
            subtotalGeneral += subtotalItem;
            isvGeneral += isvItem;
        });
        let totalNC = subtotalGeneral + isvGeneral;
        $('#nc_subtotal').text('L ' + subtotalGeneral.toFixed(2));
        $('#nc_isv').text('L ' + isvGeneral.toFixed(2));
        $('#nc_total').text('L ' + totalNC.toFixed(2));
    }

    $('#btnNuevaNotaCredito').click(function() {
        $('#formNotaCredito').trigger('reset');
        $('#detalleFacturaContainer').addClass('d-none');
        $('#itemsFacturaBody').empty();
        $('#id_factura_asociada').select2({
            theme: "bootstrap-5",
            dropdownParent: $('#modalNotaCredito'),
            placeholder: 'Escribe el correlativo de la factura (ej. F-00123)...',
            ajax: {
                url: '../app/controllers/FacturaController.php?action=search',
                dataType: 'json',
                delay: 250,
                data: function(params) { return { term: params.term }; },
                processResults: function(data) { return { results: data }; }
            }
        }).val(null).trigger('change');
        $('#modalNotaCredito').modal('show');
    });

    $('#id_factura_asociada').on('select2:select', function(e) {
        let idFactura = e.params.data.id;
        $.get(`../app/controllers/NotaCreditoController.php?action=get_factura_detalle&id_factura=${idFactura}`, function(factura) {
            $('#info_paciente').text(factura.paciente_nombre);
            $('#info_fecha').text(moment(factura.fecha_emision).format('DD/MM/YYYY'));
            $('#id_paciente_nc').val(factura.id_paciente);
            let itemsBody = $('#itemsFacturaBody');
            itemsBody.empty();
            factura.detalle.forEach(item => {
                let filaHtml = `<tr 
                                data-id-item="${item.id_item}"
                                data-tipo-item="${item.tipo_item}"
                                data-precio="${item.precio_unitario}"
                                data-id-isv="${item.id_isv}"
                                data-isv-porcentaje="${item.porcentaje || 0}"
                                data-descripcion="${item.descripcion_item}">
                    <td>${item.descripcion_item}</td>
                    <td class="text-center">${item.cantidad}</td>
                    <td><input type="number" class="form-control form-control-sm cantidad-devolver" value="0" min="0" max="${item.cantidad}"></td>
                    <td class="text-end">L ${parseFloat(item.precio_unitario).toFixed(2)}</td>
                    <td class="text-end subtotal-nc">L 0.00</td>
                </tr>`;
                itemsBody.append(filaHtml);
            });
            $('#detalleFacturaContainer').removeClass('d-none');
            calcularTotalesNC();
        });
    });

    $(document).on('change keyup', '.cantidad-devolver', function() {
        calcularTotalesNC();
    });

    $('#formNotaCredito').submit(function(e) {
        e.preventDefault();
        let detalle = [];
        $('#itemsFacturaBody tr').each(function() {
            let fila = $(this);
            let cantidad = parseInt(fila.find('.cantidad-devolver').val()) || 0;
            if (cantidad > 0) {
                let precio = parseFloat(fila.data('precio'));
                let subtotal = cantidad * precio;
                detalle.push({
                    id_item: fila.data('id-item'),
                    tipo_item: fila.data('tipo-item'),
                    descripcion_item: fila.data('descripcion'),
                    cantidad: cantidad,
                    precio_unitario: precio,
                    id_isv: fila.data('id-isv'),
                    subtotal: subtotal
                });
            }
        });
        
        if (detalle.length === 0) {
            Swal.fire('Error', 'Debes especificar una cantidad a devolver para al menos un item.', 'warning');
            return;
        }

        let data = {
            id_factura_asociada: $('#id_factura_asociada').val(),
            id_paciente: $('#id_paciente_nc').val(),
            fecha_emision: new Date().toISOString().slice(0, 10),
            motivo: $('#motivo_nc').val(),
            subtotal: parseFloat($('#nc_subtotal').text().replace('L ', '')),
            isv_total: parseFloat($('#nc_isv').text().replace('L ', '')),
            total: parseFloat($('#nc_total').text().replace('L ', '')),
            detalle: detalle
        };
        
        $('#btnGuardarNC').prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Guardando...');

        $.ajax({
            url: '../app/controllers/NotaCreditoController.php',
            type: 'POST',
            contentType: 'application/json',
            data: JSON.stringify(data),
            success: function(r) { 
                Swal.fire('¡Éxito!', r.message, 'success');
                tablaNC.ajax.reload();
                $('#modalNotaCredito').modal('hide');
            },
            error: function(jqXHR) {
                let errorMessage = 'No se pudo completar la operación.';
                if (jqXHR.responseJSON && jqXHR.responseJSON.message) {
                    errorMessage = jqXHR.responseJSON.message;
                } else if (jqXHR.responseText) {
                    console.error("Server Response:", jqXHR.responseText);
                }
                Swal.fire('Error', errorMessage, 'error');
            },
            complete: function() {
                $('#btnGuardarNC').prop('disabled', false).html('Guardar Nota de Crédito');
            }
        });
    });

    // --- LÓGICA PARA LOS BOTONES DE ACCIONES ---
    $(document).on('click', '.btnVerNotaCredito', function() {
        let id = $(this).data('id');
        $.get(`../app/controllers/NotaCreditoController.php?id=${id}`, function(nc) {
            $('#ver_nc_correlativo').text(nc.correlativo);
            $('#ver_nc_paciente').text(nc.paciente_nombre);
            $('#ver_nc_fecha').text(moment(nc.fecha_emision).format('DD/MM/YYYY'));
            $('#ver_nc_factura_asociada').text(nc.factura_asociada);
            $('#ver_nc_estado').text(nc.estado);
            $('#ver_nc_motivo').text(nc.motivo);
            let detalleBody = $('#ver_nc_detalle_body');
            detalleBody.empty();
            nc.detalle.forEach(item => {
                let fila = `<tr>
                    <td>${item.descripcion_item}</td>
                    <td>${item.cantidad}</td>
                    <td>L ${parseFloat(item.precio_unitario).toFixed(2)}</td>
                    <td>L ${parseFloat(item.subtotal).toFixed(2)}</td>
                </tr>`;
                detalleBody.append(fila);
            });
            $('#ver_nc_subtotal').text('L ' + parseFloat(nc.subtotal).toFixed(2));
            $('#ver_nc_isv').text('L ' + parseFloat(nc.isv_total).toFixed(2));
            $('#ver_nc_total').text('L ' + parseFloat(nc.total).toFixed(2));
            $('#modalVerNotaCredito').modal('show');
        });
    });

    $(document).on('click', '.btnImprimirNotaCredito', function() {
        let id = $(this).data('id');
        let url = `../app/reports/nota_credito_report.php?id=${id}`;
        $('#pdfFrame').attr('src', url);
        $('#modalPdfViewer').modal('show');
    });

    $('#modalPdfViewer').on('hidden.bs.modal', function () {
        $('#pdfFrame').attr('src', 'about:blank');
    });
});