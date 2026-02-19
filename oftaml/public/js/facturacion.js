$(document).ready(function() {
    
    // --- HELPER: Formatear Moneda (L 1,234.56) ---
    function formatearMoneda(valor) {
        let numero = parseFloat(valor);
        if (isNaN(numero)) numero = 0;
        return 'L ' + numero.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }

    // --- INICIALIZACIÓN DE SELECT2 ---
    $('#id_medico_factura').select2({
        theme: "bootstrap-5",
        dropdownParent: $('#modalFactura'),
        placeholder: 'Seleccione un médico...',
        allowClear: true
    });
    
    $('#id_tecnico_factura').select2({
        theme: "bootstrap-5",
        dropdownParent: $('#modalFactura'),
        placeholder: 'Seleccione un técnico...',
        allowClear: true
    });

    // --- FUNCIÓN: Carga de Médicos ---
    function cargarMedicosFactura(selectedId = null) {
        let selector = $('#id_medico_factura');
        $.ajax({
            url: '../app/controllers/MedicoController.php?action=get_activos', 
            type: 'GET',
            dataType: 'json',
            success: function(medicos) {
                selector.empty();
                selector.append(new Option("Seleccione un médico...", "", true, true));
                if (Array.isArray(medicos)) {
                    medicos.forEach(function(medico) {
                        let nombreCompleto = `${medico.nombres} ${medico.apellidos}`;
                        let option = new Option(nombreCompleto, medico.id, false, false);
                        selector.append(option);
                    });
                }
                if (selectedId) selector.val(selectedId).trigger('change');
                else selector.val('').trigger('change');
            },
            error: function(jqXHR, textStatus, errorThrown) {
                console.error("Error al cargar médicos:", textStatus, errorThrown);
            }
        });
    }

    // --- FUNCIÓN: Carga de Técnicos ---
    function cargarTecnicosFactura(selectedId = null) {
        let selector = $('#id_tecnico_factura');
        $.ajax({
            url: '../app/controllers/UsuarioController.php?action=get_tecnicos', 
            type: 'GET',
            dataType: 'json',
            success: function(tecnicos) {
                selector.empty();
                selector.append(new Option("Seleccione un técnico...", "", true, true));
                if (Array.isArray(tecnicos)) {
                    tecnicos.forEach(function(tec) {
                        let option = new Option(tec.nombre_completo, tec.id, false, false);
                        selector.append(option);
                    });
                }
                if (selectedId) selector.val(selectedId).trigger('change');
                else selector.val('').trigger('change');
            },
            error: function() { console.error("Error al cargar técnicos"); }
        });
    }

    // --- INICIALIZACIÓN DE FILTROS ---
    $('#filtro_daterange_factura').daterangepicker({
        startDate: moment(),
        endDate: moment(),
        locale: {
            "format": "DD/MM/YYYY", "separator": " - ", "applyLabel": "Aplicar",
            "cancelLabel": "Cancelar", "fromLabel": "Desde", "toLabel": "Hasta",
            "customRangeLabel": "Personalizado", "weekLabel": "S",
            "daysOfWeek": ["Do", "Lu", "Ma", "Mi", "Ju", "Vi", "Sá"],
            "monthNames": ["Enero", "Febrero", "Marzo", "Abril", "Mayo", "Junio", "Julio", "Agosto", "Septiembre", "Octubre", "Noviembre", "Diciembre"],
            "firstDay": 1
        },
        ranges: {
           'Hoy': [moment(), moment()],
           'Ayer': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
           'Últimos 7 Días': [moment().subtract(6, 'days'), moment()],
           'Este Mes': [moment().startOf('month'), moment().endOf('month')],
           'Mes Pasado': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')]
        }
    });

    // --- CORRECCIÓN 1: Evitar error de reinicialización ---
    if ($.fn.DataTable.isDataTable('#tablaFacturas')) {
        $('#tablaFacturas').DataTable().destroy();
    }

    // --- DATATABLE PRINCIPAL ---
    let tabla = $('#tablaFacturas').DataTable({
        "ajax": { 
            "url": "../app/controllers/FacturaController.php", 
            "type": "GET",
            "data": function(d) {
                let range = $('#filtro_daterange_factura').val().split(' - ');
                if (range.length === 2) {
                    d.start_date = moment(range[0], 'DD/MM/YYYY').format('YYYY-MM-DD');
                    d.end_date = moment(range[1], 'DD/MM/YYYY').format('YYYY-MM-DD');
                }
                d.estado = $('#filtro_estado_factura').val();
            },
            "dataSrc": "data" 
        },
        "columns": [
            { "data": "correlativo" },
            { "data": "paciente_nombre" },
            { "data": "fecha_emision", "render": function(data) { return moment(data).format('DD/MM/YYYY'); } },
            { "data": "total", "render": function(data) { return formatearMoneda(data); } },
            { "data": "estado", "render": function(data) {
                let badge = 'bg-secondary';
                if (data === 'Pagada') badge = 'bg-success';
                else if (data === 'Anulada') badge = 'bg-danger';
                else if (data === 'Borrador') badge = 'bg-warning text-dark';
                else if (data === 'Pago Parcial') badge = 'bg-info text-dark';
                return `<span class="badge ${badge}">${data}</span>`;
            }},
            { 
                "data": null, 
                "render": function(data, type, row) {
                    let botones = "<div class='text-center'><div class='btn-group'>";
                    if (USER_GROUP_ID == 1 || (PERMISOS.facturacion && PERMISOS.facturacion.ver == 1)) {
                        botones += "<button class='btn btn-info btn-sm btnVerFactura' title='Ver Detalles'><i class='fas fa-eye'></i></button>";
                        botones += `<a href="../app/reports/factura_report.php?id=${row.id}" target="_blank" class="btn btn-secondary btn-sm" title="Imprimir PDF"><i class="fas fa-print"></i></a>`;
                    }
                    
                    if ((row.estado === 'Borrador' || row.estado === 'Pago Parcial') && (USER_GROUP_ID == 1 || (PERMISOS.facturacion && PERMISOS.facturacion.editar == 1))) {
                        if (row.estado === 'Borrador') {
                            botones += "<button class='btn btn-primary btn-sm btnEditarFactura' title='Editar Factura'><i class='fas fa-edit'></i></button>";
                            botones += "<button class='btn btn-warning btn-sm btnCambiarEstadoFactura text-white' title='Cambiar Estado'><i class='fas fa-sync-alt'></i></button>";
                        }
                        botones += "<button class='btn btn-success btn-sm btnPagarFactura' title='Registrar Pago'><i class='fas fa-cash-register'></i></button>";
                    }
                    botones += "</div></div>";
                    return botones;
                }
            }
        ],
        "order": [[ 0, "asc" ]]
    });
    
    $('#btnFiltrarFacturas').on('click', function() { tabla.ajax.reload(); });
    $('#filtro_daterange_factura').on('apply.daterangepicker', function() { tabla.ajax.reload(); });
    $('#filtro_estado_factura').on('change', function() { tabla.ajax.reload(); });

    let tipos_isv_disponibles = [];
    $.get('../app/controllers/ProductoController.php?action=get_isv', function(data) { tipos_isv_disponibles = data; });

    function inicializarSelectPaciente(paciente = null) {
        let select = $('#id_paciente_factura');
        if (select.data('select2')) { select.select2('destroy'); }
        
        select.select2({
            theme: "bootstrap-5",
            dropdownParent: $('#modalFactura'),
            placeholder: 'Buscar o crear un paciente...',
            tags: true,
            createTag: function(params) {
                var term = $.trim(params.term);
                if (term === '') { return null; }
                return { id: term, text: `Crear nuevo paciente: "${term}"`, isNew: true };
            },
            ajax: {
                url: '../app/controllers/PacienteController.php?action=search',
                dataType: 'json', delay: 250,
                data: function(params) { return { term: params.term }; },
                processResults: function(data) { return { results: data }; }
            }
        }).on('select2:select', function(e) {
            let data = e.params.data;
            if (data.isNew) {
                let nombreCompleto = data.text.match(/"([^"]+)"/)[1];
                $('#modalCRUD .modal-title').text("Crear Nuevo Paciente");
                $('#formPaciente').trigger('reset');
                $('#id').val(null);
                $('#nombres').val(nombreCompleto);
                $('#tiene_seguro').val('No').trigger('change');
                $('#modalCRUD').modal('show');
            }
        });
        
        if (paciente) {
            var option = new Option(paciente.text, paciente.id, true, true);
            select.append(option).trigger('change');
        }
    }

    function inicializarSelectProducto(selector, producto = null) {
        if (selector.data('select2')) { selector.select2('destroy'); }

        selector.select2({
            theme: "bootstrap-5",
            dropdownParent: $('#modalFactura'),
            placeholder: 'Buscar producto...',
            ajax: {
                url: '../app/controllers/ProductoController.php?action=search',
                dataType: 'json', delay: 250,
                data: function(params) { return { term: params.term }; },
                processResults: function(data) { return { results: data }; }
            }
        }).on('select2:select', function(e){
            let data = e.params.data;
            let fila = $(this).closest('tr');
            fila.find('.precio').val(parseFloat(data.precio_venta).toFixed(2));
            fila.find('.select-isv').val(data.id_isv);
            actualizarTotalFactura();
        });
        if (producto) {
            var option = new Option(producto.text, producto.id, true, true);
            selector.append(option).trigger('change');
        }
    }

    function actualizarTotalFactura() {
        let subtotalGeneral = 0;
        let isvGeneral = 0;
        $('#detalleFacturaBody tr').each(function() {
            let fila = $(this);
            let cantidad = parseFloat(fila.find('.cantidad').val()) || 0;
            let precio = parseFloat(fila.find('.precio').val()) || 0;
            let descuento = parseFloat(fila.find('.descuento').val()) || 0;
            let isv_porcentaje = parseFloat(fila.find('.select-isv option:selected').data('porcentaje')) || 0;
            
            let subtotalBruto = cantidad * precio;
            let subtotalNeto = subtotalBruto - descuento;
            let isvItem = subtotalNeto * (isv_porcentaje / 100);
            
            // Formato moneda en la celda de subtotal
            fila.find('.subtotal').text(formatearMoneda(subtotalNeto).replace('L ', ''));
            subtotalGeneral += subtotalNeto;
            isvGeneral += isvItem;
        });

        let descuentoGeneral = parseFloat($('#factura_descuento_total').val()) || 0;
        let totalFactura = (subtotalGeneral + isvGeneral) - descuentoGeneral;

        $('#factura_subtotal').text(formatearMoneda(subtotalGeneral));
        $('#factura_isv').text(formatearMoneda(isvGeneral));
        $('#factura_total').text(formatearMoneda(totalFactura));
    }

    function calcularTotales() {
        let subtotal = parseFloat($('#factura_subtotal').text().replace(/[L,\s]/g, '')) || 0;
        let isvTotal = parseFloat($('#factura_isv').text().replace(/[L,\s]/g, '')) || 0;
        let descuentoTotal = parseFloat($('#factura_descuento_total').val()) || 0;
        let total = parseFloat($('#factura_total').text().replace(/[L,\s]/g, '')) || 0;
        
        return { subtotal, isvTotal, descuentoTotal, total };
    }

    $('#btnAgregarProductoFactura').click(function() {
        let isv_options = tipos_isv_disponibles.map(i => `<option value="${i.id}" data-porcentaje="${i.porcentaje}">${i.nombre_isv}</option>`).join('');
        let filaHtml = `<tr data-tipo="Producto">
            <td><select class="form-select form-select-sm select-item" style="width:100%;"></select></td>
            <td><input type="number" class="form-control form-control-sm cantidad" value="1" min="1"></td>
            <td><input type="number" step="0.01" class="form-control form-control-sm precio" value="0.00" min="0"></td>
            <td><input type="number" step="0.01" class="form-control form-control-sm descuento" value="0.00" min="0"></td>
            <td><select class="form-select form-select-sm select-isv">${isv_options}</select></td>
            <td>L <span class="subtotal">0.00</span></td>
            <td><button type="button" class="btn btn-danger btn-sm btnEliminarFila"><i class="fas fa-trash"></i></button></td>
        </tr>`;
        let nuevaFila = $(filaHtml);
        $('#detalleFacturaBody').append(nuevaFila);
        inicializarSelectProducto(nuevaFila.find('.select-item'));
    });
    
    $('#btnAgregarServicioFactura').click(function() {
        let isv_options = tipos_isv_disponibles.map(i => `<option value="${i.id}" data-porcentaje="${i.porcentaje}">${i.nombre_isv}</option>`).join('');
        let filaHtml = `<tr data-tipo="Servicio">
            <td><select class="form-select form-select-sm select-item" style="width:100%;"></select></td>
            <td><input type="number" class="form-control form-control-sm cantidad" value="1" min="1" readonly></td>
            <td><input type="number" step="0.01" class="form-control form-control-sm precio" value="0.00" min="0"></td>
            <td><input type="number" step="0.01" class="form-control form-control-sm descuento" value="0.00" min="0"></td>
            <td><select class="form-select form-select-sm select-isv">${isv_options}</select></td>
            <td>L <span class="subtotal">0.00</span></td>
            <td><button type="button" class="btn btn-danger btn-sm btnEliminarFila"><i class="fas fa-trash"></i></button></td>
        </tr>`;
        let nuevaFila = $(filaHtml);
        $('#detalleFacturaBody').append(nuevaFila);
        nuevaFila.find('.select-item').select2({
            theme: "bootstrap-5", dropdownParent: $('#modalFactura'), placeholder: 'Buscar servicio...',
            ajax: {
                url: '../app/controllers/ServicioController.php?action=search',
                dataType: 'json', delay: 250,
                data: function(params) { return { term: params.term }; },
                processResults: function(data) { return { results: data }; }
            }
        }).on('select2:select', function(e){
            let data = e.params.data;
            let fila = $(this).closest('tr');
            fila.find('.precio').val(parseFloat(data.precio_venta).toFixed(2));
            fila.find('.select-isv').val(data.id_isv);
            actualizarTotalFactura();
        });
    });

    // --- CORRECCIÓN 2: Usar .off() para evitar duplicación de eventos en botones ---
    
    $(document).off('change keyup', '.cantidad, .precio, .descuento, .select-isv, #factura_descuento_total')
               .on('change keyup', '.cantidad, .precio, .descuento, .select-isv, #factura_descuento_total', function() {
        actualizarTotalFactura();
    });

    $(document).off('click', '.btnEliminarFila').on('click', '.btnEliminarFila', function() {
        $(this).closest('tr').remove();
        actualizarTotalFactura();
    });

    // --- CRUD FACTURA ---
    
    $('#modalFactura').on('hidden.bs.modal', function () {
        $('#formFactura').trigger('reset');
        $('#id_factura').val('');
        $('#id_cita_factura').val('');
        $('#id_paciente_factura').val(null).trigger('change');
        $('#id_medico_factura').val(null).trigger('change');
        $('#id_tecnico_factura').val(null).trigger('change');
        $('#detalleFacturaBody').empty();
        actualizarTotalFactura();
        $('#modalLabel').text('Nueva Factura');
    });

    $('#btnNuevaFactura').click(function() {
        $('#formFactura').trigger('reset');
        $('#id_factura').val('');
        $('#modalLabel').text('Nueva Factura');
        $('#detalleFacturaBody').empty();
        actualizarTotalFactura();
        $('#id_paciente_factura').html('');
        
        inicializarSelectPaciente();
        cargarMedicosFactura(); 
        cargarTecnicosFactura(); 
        
        $('#fecha_emision').val(moment().format('YYYY-MM-DD'));
        $('#modalFactura').modal('show');
    });

    $('#formFactura').submit(function(e){
        e.preventDefault();
        let id = $.trim($('#id_factura').val());
        let detalle = [];
        
        $('#detalleFacturaBody tr').each(function() {
            let fila = $(this);
            let tipo = fila.data('tipo');
            let item = {
                tipo: tipo,
                cantidad: fila.find('.cantidad').val(),
                precio: fila.find('.precio').val(),
                descuento: fila.find('.descuento').val(),
                id_isv: fila.find('.select-isv').val(),
                id: fila.data('id-item') || fila.find('.select-item').val(),
                descripcion: tipo === 'Servicio' && fila.find('.select-item').length === 0 ? fila.find('input[type="text"]').val() : fila.find('.select-item option:selected').text()
            };
            detalle.push(item);
        });
        
        if (detalle.length === 0) {
            Swal.fire('Error', 'Debe agregar al menos un item a la factura.', 'error');
            return;
        }

        let totales = calcularTotales();
        
        let data = {
            id: id,
            id_paciente: $('#id_paciente_factura').val(),
            id_medico: $('#id_medico_factura').val(),
            id_tecnico: $('#id_tecnico_factura').val(),
            fecha_emision: $('#fecha_emision').val(),
            subtotal: totales.subtotal,
            isv_total: totales.isvTotal,
            descuento_total: totales.descuentoTotal,
            total: totales.total,
            detalle: detalle,
            cita_id: $('#id_cita_factura').val()
        };

        let url = '../app/controllers/FacturaController.php';
        let method = id ? 'PUT' : 'POST';

        $('#btnGuardarFactura').prop('disabled', true).text('Guardando...');

        $.ajax({
            url: url, type: method, contentType: 'application/json',
            data: JSON.stringify(data),
            success: function(response) {
                Swal.fire('¡Éxito!', response.message, 'success');
                $('#modalFactura').modal('hide');
                tabla.ajax.reload();
            },
            error: function(jqXHR) {
                let errorMessage = jqXHR.responseJSON ? jqXHR.responseJSON.message : 'No se pudo completar la operación.';
                Swal.fire('Error', errorMessage, 'error');
            },
            complete: function() {
                $('#btnGuardarFactura').prop('disabled', false).text(id ? 'Guardar Cambios' : 'Guardar Borrador');
            }
        });
    });

    const urlParams = new URLSearchParams(window.location.search);
    const action = urlParams.get('action');
    const citaIdParaFactura = urlParams.get('cita_id');
    
    if (action === 'nuevo' && citaIdParaFactura) {
        setTimeout(function() {
            $('#btnNuevaFactura').click();
            $('#modalFactura').one('shown.bs.modal', function() {
                $.get(`../app/controllers/FacturaController.php?action=get_cita_info&cita_id=${citaIdParaFactura}`, function(cita) {
                    if (cita) {
                        $('#id_cita_factura').val(citaIdParaFactura);
                        inicializarSelectPaciente({id: cita.id_paciente, text: cita.paciente_nombre});
                        cargarMedicosFactura(cita.id_medico_cita);
                        cargarTecnicosFactura();

                        let isv_options = tipos_isv_disponibles.map(i => `<option value="${i.id}" data-porcentaje="${i.porcentaje}">${i.nombre_isv}</option>`).join('');
                        let filaHtml = `<tr data-tipo="Servicio" data-id-item="${cita.id_servicio}">
                            <td><input type="text" class="form-control form-control-sm" value="${cita.motivo_detalle}" readonly></td>
                            <td><input type="number" class="form-control form-control-sm cantidad" value="1" min="1"></td>
                            <td><input type="number" step="0.01" class="form-control form-control-sm precio" value="${parseFloat(cita.precio_venta).toFixed(2)}" min="0"></td>
                            <td><input type="number" step="0.01" class="form-control form-control-sm descuento" value="0.00" min="0"></td>
                            <td><select class="form-select form-select-sm select-isv">${isv_options}</select></td>
                            <td>L <span class="subtotal">0.00</span></td>
                            <td><button type="button" class="btn btn-danger btn-sm btnEliminarFila"><i class="fas fa-trash"></i></button></td>
                        </tr>`;
                        let nuevaFila = $(filaHtml);
                        nuevaFila.find('.select-isv').val(cita.id_isv);
                        $('#detalleFacturaBody').append(nuevaFila);
                        actualizarTotalFactura();
                    }
                });
            });
        }, 500);
    }

    // Editar Factura - CORRECCIÓN 2: Limpieza de eventos
    $(document).off("click", ".btnEditarFactura").on("click", ".btnEditarFactura", function() {
        let data = tabla.row($(this).closest('tr')).data();
        
        $.get(`../app/controllers/FacturaController.php?id=${data.id}`, function(factura) {
            $('#id_factura').val(factura.id);
            $('#modalLabel').text(`Editar Factura ${factura.correlativo}`);
            
            var optionPaciente = new Option(factura.paciente_nombre, factura.id_paciente, true, true);
            $('#id_paciente_factura').append(optionPaciente).trigger('change');
            
            cargarMedicosFactura(factura.id_medico);
            cargarTecnicosFactura(factura.id_tecnico);

            let fechaSinHora = moment(factura.fecha_emision).format('YYYY-MM-DD');
            $('#fecha_emision').val(fechaSinHora);

            $('#factura_descuento_total').val(parseFloat(factura.descuento_total).toFixed(2));
            
            let detalleBody = $('#detalleFacturaBody');
            detalleBody.empty();
            factura.detalle.forEach(item => {
                let isv_options = tipos_isv_disponibles.map(i => `<option value="${i.id}" data-porcentaje="${i.porcentaje}">${i.nombre_isv}</option>`).join('');
                let itemHtml;
                if(item.tipo_item === 'Producto'){
                    itemHtml = `<select class="form-select form-select-sm select-item"></select>`;
                } else {
                    itemHtml = `<input type="text" class="form-control form-control-sm" value="${item.descripcion_item}" readonly>`;
                }
                let filaHtml = `<tr data-tipo="${item.tipo_item}" data-id-item="${item.id_item}">
                    <td>${itemHtml}</td>
                    <td><input type="number" class="form-control form-control-sm cantidad" value="${item.cantidad}" min="1"></td>
                    <td><input type="number" step="0.01" class="form-control form-control-sm precio" value="${item.precio_unitario}" min="0"></td>
                    <td><input type="number" step="0.01" class="form-control form-control-sm descuento" value="${item.descuento}" min="0"></td>
                    <td><select class="form-select form-select-sm select-isv">${isv_options}</select></td>
                    <td>L <span class="subtotal">0.00</span></td>
                    <td><button type="button" class="btn btn-danger btn-sm btnEliminarFila"><i class="fas fa-trash"></i></button></td>
                </tr>`;
                let nuevaFila = $(filaHtml);
                nuevaFila.find('.select-isv').val(item.id_isv);
                detalleBody.append(nuevaFila);
                if(item.tipo_item === 'Producto') {
                    inicializarSelectProducto(nuevaFila.find('.select-item'), {id: item.id_item, text: item.descripcion_item});
                }
            });
            actualizarTotalFactura();
            $('#modalFactura').modal('show');
        });
    });

    // Ver Factura - CORRECCIÓN 2
    $(document).off("click", ".btnVerFactura").on("click", ".btnVerFactura", function() {
        let data = tabla.row($(this).closest('tr')).data();
        $.get(`../app/controllers/FacturaController.php?id=${data.id}`, function(factura) {
            $('#ver_correlativo_factura').text(factura.correlativo);
            $('#ver_paciente_factura').text(factura.paciente_nombre);
            $('#ver_fecha_factura').text(moment(factura.fecha_emision).format('DD/MM/YYYY'));
            $('#ver_estado_factura').text(factura.estado);
            $('#ver_usuario_factura').text(factura.usuario_nombre);
            $('#ver_medico_factura').text(factura.medico_nombre || 'N/A');
            $('#ver_tecnico_factura').text(factura.tecnico_nombre || 'N/A');

            let detalleBody = $('#ver_detalle_body_factura');
            detalleBody.empty();
            let subtotalCalc = 0;
            let isvTotalCalc = 0;
            let descuentoTotalCalc = 0;
            
            factura.detalle.forEach(item => {
                let precioNeto = parseFloat(item.cantidad) * parseFloat(item.precio_unitario);
                let descuentoMonto = precioNeto * (parseFloat(item.descuento) / 100);
                let subtotalItem = precioNeto - descuentoMonto;
                let isvMonto = subtotalItem * (parseFloat(item.isv_porcentaje || 0) / 100);
                let totalItem = subtotalItem + isvMonto;

                subtotalCalc += subtotalItem;
                isvTotalCalc += isvMonto;
                descuentoTotalCalc += descuentoMonto;

                detalleBody.append(`<tr>
                    <td>${item.descripcion_item}</td>
                    <td>${item.cantidad}</td>
                    <td>${formatearMoneda(item.precio_unitario)}</td>
                    <td>${parseFloat(item.descuento).toFixed(2)}%</td>
                    <td>${item.nombre_isv || 'N/A'}</td>
                    <td>${formatearMoneda(totalItem)}</td>
                </tr>`);
            });

            $('#ver_subtotal_general_factura').text(formatearMoneda(subtotalCalc));
            $('#ver_descuento_total_factura').text(formatearMoneda(descuentoTotalCalc));
            $('#ver_isv_general_factura').text(formatearMoneda(isvTotalCalc));
            $('#ver_total_general_factura').text(formatearMoneda(factura.total));

            $.get(`../app/controllers/PagoController.php?action=get_pagos_factura&id_factura=${factura.id}`, function(pagos) {
                let pagosBody = $('#ver_detalle_pago_body');
                let contenedorPagos = $('#contenedor_pagos_detalle');
                pagosBody.empty();
                
                let totalPagado = 0;
                if (pagos && pagos.length > 0) {
                    contenedorPagos.show();
                    pagos.forEach(pago => {
                        let montoPago = parseFloat(pago.monto);
                        totalPagado += montoPago;
                        let referencia = pago.referencia ? `<br><small class="text-muted">Ref: ${pago.referencia}</small>` : '';
                        let fecha = moment(pago.fecha_pago).format('DD/MM/YYYY hh:mm A');
                        
                        let itemPago = `<li class="list-group-item d-flex justify-content-between align-items-center">
                                            <div>
                                                <strong>${pago.forma_pago}</strong> ${referencia}
                                                <br><small class="text-muted"><i class="fas fa-clock me-1"></i>${fecha}</small>
                                            </div>
                                            <span>${formatearMoneda(montoPago)}</span>
                                        </li>`;
                        pagosBody.append(itemPago);
                    });
                } else {
                    contenedorPagos.hide();
                }
                
                $('#ver_total_abonado_factura').text(formatearMoneda(totalPagado));
                let saldoPendiente = parseFloat(factura.total) - totalPagado;
                if(saldoPendiente < 0.01) saldoPendiente = 0;
                $('#ver_saldo_pendiente_factura').text(formatearMoneda(saldoPendiente));
            });

            $('#modalVerFactura').modal('show');
        });
    });

    // Cambiar Estado - CORRECCIÓN 2
    $(document).off("click", ".btnCambiarEstadoFactura").on("click", ".btnCambiarEstadoFactura", function() {
        let data = tabla.row($(this).closest('tr')).data();
        $('#id_factura_estado').val(data.id);
        $('#correlativo_factura_estado').text(data.correlativo);
        $('#nuevo_estado_factura').val(data.estado);
        $('#modalCambiarEstadoFactura').modal('show');
    });

    $('#formCambiarEstadoFactura').submit(function(e) {
        e.preventDefault();
        let data = {
            id_factura: $('#id_factura_estado').val(),
            estado: $('#nuevo_estado_factura').val()
        };
        $.ajax({
            url: '../app/controllers/FacturaController.php?action=cambiar_estado',
            type: 'PUT',
            contentType: 'application/json',
            data: JSON.stringify(data),
            success: function(r) { Swal.fire('¡Éxito!', r.message, 'success'); tabla.ajax.reload(); $('#modalCambiarEstadoFactura').modal('hide'); },
            error: function(jqXHR) { Swal.fire('Error', jqXHR.responseJSON.message, 'error'); }
        });
    });

    // --- LÓGICA DE PAGO ---
    let totalFacturaAPagar = 0;
    
    function actualizarTotalesPago() {
        let totalPagado = 0;
        $('#detalle-pago-container .pago-monto').each(function() {
            totalPagado += parseFloat($(this).val()) || 0;
        });
        
        let restante = totalFacturaAPagar - totalPagado;
        
        $('#pago_total_pagado').text(formatearMoneda(totalPagado));
        
        if (restante > 0.01) {
            $('#pago_pendiente').text(formatearMoneda(restante));
            $('#fila_cambio').hide();
            if (totalPagado > 0) {
                 $('#btnGuardarPago, #btnGuardarImprimirPago').prop('disabled', false);
            } else {
                 $('#btnGuardarPago, #btnGuardarImprimirPago').prop('disabled', true);
            }
        } else {
            $('#pago_pendiente').text('L 0.00');
            $('#fila_cambio').show();
            let cambio = (restante < 0 ? -restante : 0);
            $('#pago_cambio').text(formatearMoneda(cambio));
            $('#btnGuardarPago, #btnGuardarImprimirPago').prop('disabled', false);
        }
    }
    
    function generarHtmlFormaPago(tipo, monto = 0, esMultiple = false) {
        let extraField = (tipo === 'Tarjeta' || tipo === 'Transferencia' || tipo === 'Cheque') ? `<input type="text" class="form-control pago-referencia" placeholder="N° de Referencia" required>` : '';
        let montoInput = esMultiple ? '0.00' : monto.toFixed(2);
        return `<div class="input-group mb-2" data-forma-pago="${tipo}"><span class="input-group-text" style="width: 120px;">${tipo}</span><input type="number" step="0.01" class="form-control pago-monto" value="${montoInput}" required>${extraField}</div>`;
    }
    
    $('#forma_pago_principal').on('change', function() {
        let container = $('#detalle-pago-container'); 
        container.empty(); 
        let tipo = $(this).val();
        if (tipo === 'Multiple') { 
            container.append(generarHtmlFormaPago('Efectivo', 0, true)); 
            container.append(generarHtmlFormaPago('Tarjeta', 0, true)); 
        } else { 
            container.append(generarHtmlFormaPago(tipo, totalFacturaAPagar, false)); 
        }
        actualizarTotalesPago();
    });
    
    $(document).off('keyup change', '.pago-monto').on('keyup change', '.pago-monto', function() { actualizarTotalesPago(); });
    
    // CORRECCIÓN 2: Pagar Factura con .off()
    $(document).off('click', '.btnPagarFactura').on('click', '.btnPagarFactura', function() {
        let data = tabla.row($(this).closest('tr')).data();
        
        $.get(`../app/controllers/FacturaController.php?id=${data.id}`, function(factura) {
            let totalOriginal = parseFloat(factura.total);
            let abonadoPrevio = 0;
            
            if (factura.pagos && factura.pagos.length > 0) {
                factura.pagos.forEach(p => { abonadoPrevio += parseFloat(p.monto); });
            }
            
            let saldoPendiente = totalOriginal - abonadoPrevio;
            if (saldoPendiente < 0) saldoPendiente = 0;
            
            totalFacturaAPagar = saldoPendiente;

            $('#pago_id_factura').val(factura.id);
            $('#pago_correlativo').text(factura.correlativo);
            
            $('#pago_subtotal').text(formatearMoneda(factura.subtotal));
            $('#pago_isv_total').text(formatearMoneda(factura.isv_total));
            $('#pago_total_original').text(formatearMoneda(totalOriginal));
            $('#pago_ya_abonado').text(formatearMoneda(abonadoPrevio));
            $('#pago_saldo_pendiente_real').text(formatearMoneda(saldoPendiente));
            
            $('#forma_pago_principal').val('Efectivo').trigger('change');
            $('#modalRegistrarPago').modal('show');
        });
    });
    
    $('#formRegistrarPago').submit(function(e) {
        e.preventDefault(); const botonPresionado = $(document.activeElement); let totalPagadoAhora = 0; let metodosDePago = [];
        $('#detalle-pago-container .input-group').each(function() { let monto = parseFloat($(this).find('.pago-monto').val()) || 0; if (monto > 0) { totalPagadoAhora += monto; metodosDePago.push({ forma_pago: $(this).data('forma-pago'), monto: monto, referencia: $(this).find('.pago-referencia').val() || '' }); } });
        
        if (metodosDePago.length === 0) { Swal.fire('Inválido', 'Debe ingresar un monto.', 'warning'); return; }
        
        const idFactura = $('#pago_id_factura').val(); 
        const data = { id_factura: idFactura, monto_total: totalPagadoAhora, detalle: metodosDePago };
        
        botonPresionado.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Procesando...');
        $.ajax({
            url: '../app/controllers/PagoController.php', type: 'POST', contentType: 'application/json', data: JSON.stringify(data),
            success: function(response) { Swal.fire('¡Éxito!', response.message, 'success').then(() => { $('#modalRegistrarPago').modal('hide'); tabla.ajax.reload();
                if (botonPresionado.is('#btnGuardarImprimirPago')) { window.open(`../app/reports/factura_report.php?id=${idFactura}&action=print`, '_blank'); } }); },
            error: function(jqXHR) { Swal.fire('Error', jqXHR.responseJSON.message, 'error'); },
            complete: function() { $('#btnGuardarPago, #btnGuardarImprimirPago').prop('disabled', false); $('#btnGuardarImprimirPago').html('<i class="fas fa-print me-1"></i>Guardar e Imprimir'); $('#btnGuardarPago').html('Guardar Pago'); }
        });
    });

    $('#formPaciente').submit(function(e) {
        e.preventDefault();
        let data = {
            id: null, 
            nombres: $.trim($('#modalCRUD #nombres').val()), 
            apellidos: $.trim($('#modalCRUD #apellidos').val()),
            numero_identidad: $.trim($('#modalCRUD #numero_identidad').val()), 
            sexo: $.trim($('#modalCRUD #sexo').val()),
            direccion: $.trim($('#modalCRUD #direccion').val()), 
            telefono: $.trim($('#modalCRUD #telefono').val()), 
            email: $.trim($('#modalCRUD #email').val()),
            fecha_nacimiento: $.trim($('#modalCRUD #fecha_nacimiento').val()), 
            tiene_seguro: $.trim($('#modalCRUD #tiene_seguro').val()),
            id_aseguradora: $.trim($('#modalCRUD #id_aseguradora').val()),
            numero_poliza: $.trim($('#modalCRUD #numero_poliza').val()),
            observaciones: $.trim($('#modalCRUD #observaciones').val())
        };
        $.ajax({
            url: '../app/controllers/PacienteController.php', type: 'POST', contentType: 'application/json', data: JSON.stringify(data),
            success: function(response) {
                Swal.fire('¡Éxito!', response.message, 'success'); $('#modalCRUD').modal('hide');
                if (response.paciente) { var newOption = new Option(response.paciente.text, response.paciente.id, true, true); $('#id_paciente_factura').append(newOption).trigger('change'); }
            },
            error: function(jqXHR) { Swal.fire('Error', jqXHR.responseJSON.message || 'No se pudo crear el paciente.', 'error'); }
        });
    });

    if (window.location.hash) {
        let idFromHash = window.location.hash.substring(1).replace('factura-', '');
        if (!isNaN(idFromHash) && idFromHash > 0) {
            $.get(`../app/controllers/FacturaController.php?id=${idFromHash}`, function(factura) {
                if (factura) {
                    tabla.on('draw.dt', function() {
                         $('.btnVerFactura').first().click();
                    });
                    tabla.ajax.reload();
                }
            });
        }
    }
});