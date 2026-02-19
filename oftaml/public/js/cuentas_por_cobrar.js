$(document).ready(function() {
    
    // --- HELPER: Formatear Moneda ---
    function formatearMoneda(valor) {
        let numero = parseFloat(valor);
        if (isNaN(numero)) numero = 0;
        return 'L ' + numero.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }

    let tabla = $('#tablaCuentasPorCobrar').DataTable({
        "ajax": { "url": "../app/controllers/FacturaController.php?action=cuentas_por_cobrar", "dataSrc": "data" },
        "columns": [
            { "data": "correlativo" },
            { "data": "paciente_nombre" },
            { "data": "paciente_telefono" },
            { "data": "fecha_emision", "render": function(data) { return moment(data).format('DD/MM/YYYY'); } },
            // MODIFICADO: Usar formatearMoneda
            { "data": "total", "render": function(data) { return formatearMoneda(data); } },
            { "data": "total_pagado", "render": function(data) { return formatearMoneda(data); } },
            { "data": "saldo_pendiente", "render": function(data) { return `<strong class="text-danger">${formatearMoneda(data)}</strong>`; } },
            { 
                "data": null, 
                "render": function(data, type, row) {
                    return `<div class='text-center'>
                                <button class='btn btn-success btn-sm btnRegistrarAbono' title='Registrar Abono'>
                                    <i class='fas fa-plus me-1'></i>Abonar
                                </button>
                            </div>`;
                }
            }
        ],

    });

    let saldoPendienteAPagar = 0;

    function actualizarTotalesPago() {
        let totalPagado = 0;
        $('#detalle-pago-container .pago-monto').each(function() {
            totalPagado += parseFloat($(this).val()) || 0;
        });
        // MODIFICADO: Formato moneda en totales
        $('#pago_total_pagado').text(formatearMoneda(totalPagado));
        
        let cambio = totalPagado - saldoPendienteAPagar;
        if (cambio >= 0.00) {
            $('#fila_cambio').show();
            $('#pago_cambio').text(formatearMoneda(cambio));
        } else {
            $('#fila_cambio').hide();
        }
    }

    function generarHtmlFormaPago(tipo, monto = 0, esMultiple = false) {
        let extraField = '';
        if (tipo === 'Tarjeta') extraField = `<input type="text" class="form-control pago-referencia" placeholder="N° de Voucher" required>`;
        if (tipo === 'Transferencia') extraField = `<input type="text" class="form-control pago-referencia" placeholder="N° de Transacción" required>`;
        let montoInput = esMultiple ? '0.00' : monto.toFixed(2);
        return `<div class="input-group mb-2" data-forma-pago="${tipo}">
                    <span class="input-group-text" style="width: 120px;">${tipo}</span>
                    <input type="number" step="0.01" class="form-control pago-monto" value="${montoInput}" required>
                    ${extraField}
                </div>`;
    }

    $('#forma_pago_principal').on('change', function() {
        let container = $('#detalle-pago-container');
        container.empty();
        let tipo = $(this).val();
        if (tipo === 'Multiple') {
            container.append(generarHtmlFormaPago('Efectivo', 0, true));
            container.append(generarHtmlFormaPago('Tarjeta', 0, true));
        } else {
            container.append(generarHtmlFormaPago(tipo, saldoPendienteAPagar, false));
        }
        actualizarTotalesPago();
    });

    $(document).on('keyup change', '.pago-monto', function() {
        actualizarTotalesPago();
    });

    $(document).on('click', '.btnRegistrarAbono', function() {
        let data = tabla.row($(this).closest('tr')).data();
        saldoPendienteAPagar = parseFloat(data.saldo_pendiente);
        
        $('#pago_id_factura').val(data.id);
        $('#pago_correlativo').text(data.correlativo);
        // MODIFICADO: Formato moneda
        $('#pago_total_factura').text(formatearMoneda(saldoPendienteAPagar));
        
        // Limpiar historial previo
        $('#lista_abonos_previos').empty();
        $('#historial_abonos_container').hide();

        // Solicitar historial de pagos al servidor
        $.ajax({
            url: `../app/controllers/PagoController.php?action=get_pagos_factura&id_factura=${data.id}`,
            type: 'GET',
            dataType: 'json',
            success: function(pagos) {
                if (pagos && pagos.length > 0) {
                    $('#historial_abonos_container').show();
                    pagos.forEach(p => {
                        let fecha = moment(p.fecha_pago).format('DD/MM/YYYY hh:mm A');
                        let ref = p.referencia ? `<br><small class="text-muted">Ref: ${p.referencia}</small>` : '';
                        // MODIFICADO: Formato moneda en lista
                        let itemHtml = `
                            <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                                <div>
                                    <span class="fw-bold">${p.forma_pago}</span>${ref}
                                    <br><small class="text-muted" style="font-size: 0.85em;">${fecha}</small>
                                </div>
                                <span class="badge bg-light text-dark border">${formatearMoneda(p.monto)}</span>
                            </li>`;
                        $('#lista_abonos_previos').append(itemHtml);
                    });
                }
            },
            error: function() {
                console.error("No se pudo cargar el historial de pagos.");
            }
        });

        $('#forma_pago_principal').val('Efectivo').trigger('change');
        $('#modalRegistrarPago').modal('show');
    });

    $('#formRegistrarPago').submit(function(e) {
        e.preventDefault();
        let totalAbonado = 0;
        let metodosDePago = [];
        $('#detalle-pago-container .input-group').each(function() {
            let monto = parseFloat($(this).find('.pago-monto').val()) || 0;
            if (monto > 0) {
                totalAbonado += monto;
                metodosDePago.push({
                    forma_pago: $(this).data('forma-pago'),
                    monto: monto,
                    referencia: $(this).find('.pago-referencia').val() || ''
                });
            }
        });

        if (metodosDePago.length === 0) {
            Swal.fire('Inválido', 'Debe ingresar un monto en al menos un método de pago.', 'warning');
            return;
        }

        const data = {
            id_factura: $('#pago_id_factura').val(),
            monto_total: totalAbonado,
            detalle: metodosDePago
        };
        
        $('#btnGuardarPago').prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Guardando...');

        $.ajax({
            url: '../app/controllers/PagoController.php',
            type: 'POST',
            contentType: 'application/json',
            data: JSON.stringify(data),
            success: function(response) {
                Swal.fire('¡Éxito!', response.message, 'success').then(() => {
                    $('#modalRegistrarPago').modal('hide');
                    tabla.ajax.reload();
                });
            },
            error: function(jqXHR) {
                Swal.fire('Error', jqXHR.responseJSON.message, 'error');
            },
            complete: function() {
                $('#btnGuardarPago').prop('disabled', false).html('Guardar Abono');
            }
        });
    });
});