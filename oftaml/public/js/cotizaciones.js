$(document).ready(function() {
    $('#filtro_daterange_cotizacion').daterangepicker({
        startDate: moment().startOf('month'),
        endDate: moment().endOf('month'),
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
           'Últimos 7 Días': [moment().subtract(6, 'days'), moment()],
           'Este Mes': [moment().startOf('month'), moment().endOf('month')],
           'Mes Pasado': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')]
        }
    });

    let tabla = $('#tablaCotizaciones').DataTable({
        "ajax": { 
            "url": "../app/controllers/CotizacionController.php",
            "type": "GET",
            "data": function(d) {
                let picker = $('#filtro_daterange_cotizacion').data('daterangepicker');
                d.start_date = picker.startDate.format('YYYY-MM-DD');
                d.end_date = picker.endDate.format('YYYY-MM-DD');
            },
            "dataSrc": "data" 
        },
        "columns": [
            { "data": "correlativo" },
            { "data": "paciente_nombre" },
            { "data": "fecha_emision", "render": function(data) { return moment(data).format('DD/MM/YYYY'); } },
            { "data": "fecha_vencimiento", "render": function(data) { return moment(data).format('DD/MM/YYYY'); } },
            { "data": "total", "render": function(data) { return 'L ' + parseFloat(data).toFixed(2); } },
            { "data": "estado" },
            { 
                "data": null, 
                "render": function(data, type, row) {
                    let botones = "<div class='text-center'><div class='btn-group'>";
                    if (row.estado !== 'Facturada') {
                        botones += `<button class='btn btn-success btn-sm btnConvertirFactura' data-id='${row.id}' title='Convertir a Factura'><i class='fas fa-receipt'></i></button>`;
                    }
                    botones += `<button class="btn btn-secondary btn-sm btnVerPdf" data-id="${row.id}" title="Ver PDF"><i class="fas fa-print"></i></button>`;
                    if (row.estado === 'Borrador') {
                         botones += `<button class='btn btn-primary btn-sm btnEditarCotizacion' title='Editar Cotización'><i class='fas fa-edit'></i></button>`;
                         botones += `<button class='btn btn-danger btn-sm btnEliminarCotizacion' title='Eliminar Cotización'><i class='fas fa-trash'></i></button>`;
                    }
                    botones += "</div></div>";
                    return botones;
                }
            }
        ],

    });

    $('#btnFiltrarCotizaciones').on('click', function() {
        tabla.ajax.reload();
    });
    $('#filtro_daterange_cotizacion').on('apply.daterangepicker', function(ev, picker) {
        tabla.ajax.reload();
    });

    let tipos_isv_disponibles = [];
    $.get('../app/controllers/ProductoController.php?action=get_isv', function(data) { tipos_isv_disponibles = data; });

    function inicializarSelectPaciente(paciente = null) {
        let select = $('#id_paciente_cotizacion');
        select.select2({
            theme: "bootstrap-5",
            dropdownParent: $('#modalCotizacion'),
            placeholder: 'Buscar o crear un paciente...',
            tags: true,
            createTag: function(params) {
                var term = $.trim(params.term);
                if (term === '') { return null; }
                return { id: term, text: `Crear nuevo paciente: "${term}"`, isNew: true };
            },
            ajax: {
                url: '../app/controllers/PacienteController.php?action=search',
                dataType: 'json',
                delay: 250,
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
        selector.select2({
            theme: "bootstrap-5",
            dropdownParent: $('#modalCotizacion'),
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
            actualizarTotalCotizacion();
        });
        if (producto) {
            var option = new Option(producto.text, producto.id, true, true);
            selector.append(option).trigger('change');
        }
    }

    function actualizarTotalCotizacion() {
        let subtotalGeneral = 0;
        let isvGeneral = 0;
        $('#detalleCotizacionBody tr').each(function() {
            let fila = $(this);
            let cantidad = parseFloat(fila.find('.cantidad').val()) || 0;
            let precio = parseFloat(fila.find('.precio').val()) || 0;
            let descuento = parseFloat(fila.find('.descuento').val()) || 0;
            let isv_porcentaje = parseFloat(fila.find('.select-isv option:selected').data('porcentaje')) || 0;
            
            let subtotalBruto = cantidad * precio;
            let subtotalNeto = subtotalBruto - descuento;
            let isvItem = subtotalNeto * (isv_porcentaje / 100);
            
            fila.find('.subtotal').text(subtotalNeto.toFixed(2));
            subtotalGeneral += subtotalNeto;
            isvGeneral += isvItem;
        });

        let descuentoGeneral = parseFloat($('#cotizacion_descuento_total').val()) || 0;
        let totalCotizacion = (subtotalGeneral + isvGeneral) - descuentoGeneral;

        $('#cotizacion_subtotal').text('L ' + subtotalGeneral.toFixed(2));
        $('#cotizacion_isv').text('L ' + isvGeneral.toFixed(2));
        $('#cotizacion_total').text('L ' + totalCotizacion.toFixed(2));
    }

    $('#btnAgregarProductoCotizacion').click(function() {
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
        $('#detalleCotizacionBody').append(nuevaFila);
        inicializarSelectProducto(nuevaFila.find('.select-item'));
    });
    
    $('#btnAgregarServicioCotizacion').click(function() {
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
        $('#detalleCotizacionBody').append(nuevaFila);
        nuevaFila.find('.select-item').select2({
            theme: "bootstrap-5", dropdownParent: $('#modalCotizacion'), placeholder: 'Buscar servicio...',
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
            actualizarTotalCotizacion();
        });
    });

    $(document).on('change keyup', '.cantidad, .precio, .descuento, .select-isv, #cotizacion_descuento_total', function() {
        actualizarTotalCotizacion();
    });

    $(document).on('click', '.btnEliminarFila', function() {
        $(this).closest('tr').remove();
        actualizarTotalCotizacion();
    });

    $('#btnNuevaCotizacion').click(function() {
        $('#formCotizacion').trigger('reset');
        $('#id_cotizacion').val('');
        $('#modalLabel').text('Nueva Cotización');
        $('#detalleCotizacionBody').empty();
        actualizarTotalCotizacion();
        $('#id_paciente_cotizacion').html('');
        inicializarSelectPaciente();
        $('#fecha_emision_cotizacion').val(moment().format('YYYY-MM-DD'));
        $('#fecha_vencimiento_cotizacion').val(moment().add(15, 'days').format('YYYY-MM-DD'));
        $('#modalCotizacion').modal('show');
    });

    $('#formCotizacion').submit(function(e){
        e.preventDefault();
        let id = $.trim($('#id_cotizacion').val());
        let detalle = [];
        $('#detalleCotizacionBody tr').each(function() {
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
        
        let data = {
            id: id,
            id_paciente: $('#id_paciente_cotizacion').val(),
            fecha_emision: $('#fecha_emision_cotizacion').val(),
            fecha_vencimiento: $('#fecha_vencimiento_cotizacion').val(),
            subtotal: parseFloat($('#cotizacion_subtotal').text().replace('L ', '')),
            isv_total: parseFloat($('#cotizacion_isv').text().replace('L ', '')),
            descuento_total: parseFloat($('#cotizacion_descuento_total').val()),
            total: parseFloat($('#cotizacion_total').text().replace('L ', '')),
            notas: $('#notas_cotizacion').val(),
            detalle: detalle
        };
        
        $.ajax({
            url: '../app/controllers/CotizacionController.php',
            type: id ? 'PUT' : 'POST',
            contentType: 'application/json',
            data: JSON.stringify(data),
            success: function(r) { Swal.fire('¡Éxito!', r.message, 'success'); tabla.ajax.reload(); $('#modalCotizacion').modal('hide'); },
            error: function(jqXHR) {
                let errorMessage = 'No se pudo completar la operación.';
                if (jqXHR.responseJSON && jqXHR.responseJSON.message) {
                    errorMessage = jqXHR.responseJSON.message;
                }
                Swal.fire('Error', errorMessage, 'error');
            }
        });
    });

    $(document).on('click', '.btnEditarCotizacion', function() {
        let data = tabla.row($(this).closest('tr')).data();
        $.get(`../app/controllers/CotizacionController.php?id=${data.id}`, function(cotizacion) {
            $('#formCotizacion').trigger('reset');
            $('#modalLabel').text(`Editar Cotización ${cotizacion.correlativo}`);
            $('#id_cotizacion').val(cotizacion.id);
            
            inicializarSelectPaciente({id: cotizacion.id_paciente, text: cotizacion.paciente_nombre});
            $('#fecha_emision_cotizacion').val(cotizacion.fecha_emision);
            $('#fecha_vencimiento_cotizacion').val(cotizacion.fecha_vencimiento);
            $('#cotizacion_descuento_total').val(parseFloat(cotizacion.descuento_total).toFixed(2));
            $('#notas_cotizacion').val(cotizacion.notas);

            let detalleBody = $('#detalleCotizacionBody');
            detalleBody.empty();
            cotizacion.detalle.forEach(item => {
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
            actualizarTotalCotizacion();
            $('#modalCotizacion').modal('show');
        });
    });

    $(document).on('click', '.btnEliminarCotizacion', function() {
        let data = tabla.row($(this).closest('tr')).data();
        Swal.fire({
            title: '¿Estás seguro?',
            text: `Se eliminará la cotización ${data.correlativo}. ¡Esta acción no se puede revertir!`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Sí, ¡eliminar!',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: '../app/controllers/CotizacionController.php',
                    type: 'DELETE',
                    contentType: 'application/json',
                    data: JSON.stringify({ id: data.id }),
                    success: function(r) {
                        Swal.fire('¡Eliminada!', r.message, 'success');
                        tabla.ajax.reload();
                    },
                    error: function(jqXHR) {
                        Swal.fire('Error', jqXHR.responseJSON.message, 'error');
                    }
                });
            }
        });
    });
    
    $(document).on('click', '.btnConvertirFactura', function() {
        let idCotizacion = $(this).data('id');
        Swal.fire({
            title: '¿Convertir a Factura?',
            text: "Se creará una nueva factura a partir de esta cotización. La cotización será marcada como 'Facturada'. ¿Desea continuar?",
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#28a745',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Sí, convertir',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: `../app/controllers/CotizacionController.php?action=convertir`,
                    type: 'POST',
                    contentType: 'application/json',
                    data: JSON.stringify({ id_cotizacion: idCotizacion }),
                    success: function(response) {
                        Swal.fire({
                            title: '¡Éxito!',
                            text: response.message,
                            icon: 'success'
                        }).then(() => {
                            window.location.href = `index.php?page=facturacion&action=edit&id=${response.id_factura}`;
                        });
                    },
                    error: function(jqXHR) {
                        Swal.fire('Error', jqXHR.responseJSON.message, 'error');
                    }
                });
            }
        });
    });

    $(document).on('click', '.btnVerPdf', function() {
        let idCotizacion = $(this).data('id');
        let url = `../app/reports/cotizacion_report.php?id=${idCotizacion}`;
        
        $('#pdfFrame').attr('src', url);
        $('#modalPdfViewer').modal('show');
    });

    $('#modalPdfViewer').on('hidden.bs.modal', function () {
        $('#pdfFrame').attr('src', 'about:blank');
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
            url: '../app/controllers/PacienteController.php',
            type: 'POST',
            contentType: 'application/json',
            data: JSON.stringify(data),
            success: function(response) {
                Swal.fire('¡Éxito!', response.message, 'success');
                $('#modalCRUD').modal('hide');
                
                if (response.paciente) {
                    var newOption = new Option(response.paciente.text, response.paciente.id, true, true);
                    $('#id_paciente_cotizacion').append(newOption).trigger('change');
                }
            },
            error: function(jqXHR) {
                Swal.fire('Error', jqXHR.responseJSON.message || 'No se pudo crear el paciente.', 'error');
            }
        });
    });
});