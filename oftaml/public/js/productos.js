$(document).ready(function() {
    if (USER_GROUP_ID != 1 && (!PERMISOS.productos || !PERMISOS.productos.crear != 1)) {
        $('#btnNuevo').hide();
    }

    let tabla = $('#tablaProductos').DataTable({
        "ajax": { "url": "../app/controllers/ProductoController.php", "dataSrc": "data" },
        "columns": [
            { "data": "id" }, { "data": "codigo" }, { "data": "nombre_producto" },
            { "data": "nombre_categoria", "defaultContent": "<i>N/A</i>" },
            { "data": "es_inventariable", "render": function(data){
                return data == 1 ? '<span class="badge bg-success">Sí</span>' : '<span class="badge bg-secondary">No</span>';
            }},
            { "data": "stock_actual" },
            { "data": "precio_venta", "render": function(data) { return 'L ' + parseFloat(data).toFixed(2); }},
            { "data": null, "render": function(data, type, row) {
                let botones = "<div class='text-center'><div class='btn-group'>";
                if (USER_GROUP_ID == 1 || (PERMISOS.productos && PERMISOS.productos.ver == 1)) {
                    botones += `<button class='btn btn-secondary btn-sm btnKardex' data-id='${row.id}' data-nombre='${row.nombre_producto}' title='Ver Kardex'><i class='fas fa-history'></i></button>`;
                }
                if (USER_GROUP_ID == 1 || (PERMISOS.productos && PERMISOS.productos.editar == 1)) {
                    botones += "<button class='btn btn-info btn-sm btnEditar'><i class='fas fa-edit'></i></button>";
                }
                if (USER_GROUP_ID == 1 || (PERMISOS.productos && PERMISOS.productos.borrar == 1)) {
                    botones += "<button class='btn btn-danger btn-sm btnBorrar'><i class='fas fa-trash'></i></button>";
                }
                botones += "</div></div>";
                return botones;
            }}
        ],

    });

    function cargarCategorias(selectedId = null) {
        $.get('../app/controllers/ProductoController.php?action=get_categorias', function(categorias) {
            let selector = $('#id_categoria');
            selector.html('<option value="">Seleccione...</option>');
            categorias.forEach(c => { selector.append(`<option value="${c.id}">${c.nombre_categoria}</option>`); });
            if (selectedId) selector.val(selectedId);
        });
    }
    
    function cargarTiposIsv(selectedId = null) {
        $.get('../app/controllers/ProductoController.php?action=get_isv', function(tipos) {
            let selector = $('#id_isv');
            selector.html('');
            tipos.forEach(t => { selector.append(`<option value="${t.id}" data-porcentaje="${t.porcentaje}">${t.nombre_isv}</option>`); });
            if (selectedId) selector.val(selectedId);
            calcularPrecioVenta();
        });
    }

    function calcularPrecioVenta() {
        let compra = parseFloat($('#precio_compra').val()) || 0;
        let margen = parseFloat($('#margen').val()) || 0;
        let isv_porcentaje = parseFloat($('#id_isv option:selected').data('porcentaje')) || 0;
        let precio_sin_isv = compra * (1 + (margen / 100));
        let monto_isv = precio_sin_isv * (isv_porcentaje / 100);
        let precio_final = precio_sin_isv + monto_isv;
        $('#precio_venta').val(precio_final.toFixed(2));
    }

    $('#precio_compra, #margen, #id_isv').on('change keyup', calcularPrecioVenta);

    $('#es_inventariable').on('change', function(){
        if ($(this).is(':checked')) {
            $('#campos_stock').slideDown();
            $('#stock_actual, #stock_minimo').prop('required', true);
        } else {
            $('#campos_stock').slideUp();
            $('#stock_actual, #stock_minimo').prop('required', false).val('0');
        }
    });

    $("#btnNuevo").click(function() {
        $("#formProducto").trigger("reset");
        $('#id').val(null);
        $('#es_inventariable').prop('checked', true).trigger('change');
        $("#modalLabel").text("Nuevo Producto");
        cargarCategorias();
        cargarTiposIsv();
        setTimeout(calcularPrecioVenta, 200);
        $("#modalProducto").modal("show");
    });
    
    $('#formProducto').submit(function(e) { e.preventDefault();
        let id = $.trim($('#id').val());
        let data = {
            id: id,
            codigo: $.trim($('#codigo').val()),
            codigo_barras: $.trim($('#codigo_barras').val()),
            nombre_producto: $.trim($('#nombre_producto').val()),
            descripcion: $.trim($('#descripcion').val()),
            id_categoria: $.trim($('#id_categoria').val()),
            es_inventariable: $('#es_inventariable').is(':checked') ? 1 : 0,
            stock_actual: $.trim($('#stock_actual').val()),
            stock_minimo: $.trim($('#stock_minimo').val()),
            id_isv: $.trim($('#id_isv').val()),
            precio_compra: $.trim($('#precio_compra').val()),
            precio_venta: $.trim($('#precio_venta').val()),
            unidad_medida: $.trim($('#unidad_medida').val())
        };
        $.ajax({
            url: '../app/controllers/ProductoController.php', type: id ? 'PUT' : 'POST',
            contentType: 'application/json', data: JSON.stringify(data),
            success: function(r) { Swal.fire('¡Éxito!', r.message, 'success'); tabla.ajax.reload(null, false); $('#modalProducto').modal('hide'); },
            error: function(jqXHR) { Swal.fire('Error', jqXHR.responseJSON.message, 'error'); }
        });
    });

    $(document).on("click", ".btnEditar", function() {
        let fila = $(this).closest('tr');
        let data = tabla.row(fila).data();
        $.get(`../app/controllers/ProductoController.php?id=${data.id}`, function(producto) {
            $('#id').val(producto.id);
            $('#codigo').val(producto.codigo);
            $('#codigo_barras').val(producto.codigo_barras);
            $('#nombre_producto').val(producto.nombre_producto);
            $('#descripcion').val(producto.descripcion);
            $('#unidad_medida').val(producto.unidad_medida);
            $('#stock_actual').val(producto.stock_actual);
            $('#stock_minimo').val(producto.stock_minimo);
            $('#precio_compra').val(producto.precio_compra);
            $('#es_inventariable').prop('checked', producto.es_inventariable == 1).trigger('change');
            cargarCategorias(producto.id_categoria);
            cargarTiposIsv(producto.id_isv);
            setTimeout(function() {
                let compra = parseFloat(producto.precio_compra);
                let venta = parseFloat(producto.precio_venta);
                if(compra > 0 && venta > 0) {
                    let isv_porcentaje = parseFloat($('#id_isv option:selected').data('porcentaje')) || 0;
                    let precio_sin_isv = venta / (1 + (isv_porcentaje / 100));
                    let margen = ((precio_sin_isv / compra) - 1) * 100;
                    $('#margen').val(margen.toFixed(2));
                }
                calcularPrecioVenta();
            }, 300);
            $("#modalLabel").text("Editar Producto");
            $("#modalProducto").modal("show");
        });
    });

    $(document).on("click", ".btnBorrar", function() {
        let fila = $(this).closest('tr');
        let data = tabla.row(fila).data();
        Swal.fire({
            title: '¿Estás seguro?', text: `Se eliminará el producto "${data.nombre_producto}".`,
            icon: 'warning', showCancelButton: true, confirmButtonText: 'Sí, ¡bórralo!'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: '../app/controllers/ProductoController.php',
                    type: 'DELETE',
                    contentType: 'application/json',
                    data: JSON.stringify({ id: data.id }),
                    success: function(r) { Swal.fire('¡Eliminado!', r.message, 'success'); tabla.row(fila).remove().draw(); },
                    error: function(jqXHR) { Swal.fire('Error', jqXHR.responseJSON.message, 'error'); }
                });
            }
        });
    });

    let kardexCurrentPage = 1;
    let kardexTotalPages = 1;
    let kardexCurrentProductoId = null;

    $('#filtro_daterange_kardex').daterangepicker({
        startDate: moment().subtract(29, 'days'),
        endDate: moment(),
        locale: {
            "format": "DD/MM/YYYY", "separator": " - ", "applyLabel": "Aplicar", "cancelLabel": "Cancelar", "fromLabel": "Desde",
            "toLabel": "Hasta", "customRangeLabel": "Personalizado", "weekLabel": "S",
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

    $(document).on('click', '.btnKardex', function() {
        kardexCurrentProductoId = $(this).data('id');
        let productoNombre = $(this).data('nombre');
        $('#nombreProductoKardex').text(productoNombre);
        kardexCurrentPage = 1;
        cargarKardexPaginado(kardexCurrentProductoId, kardexCurrentPage);
        $('#modalKardex').modal('show');
    });

    $('#btnPaginaSiguienteKardex').on('click', function() {
        if (kardexCurrentPage < kardexTotalPages) {
            kardexCurrentPage++;
            cargarKardexPaginado(kardexCurrentProductoId, kardexCurrentPage);
        }
    });
    $('#btnPaginaAnteriorKardex').on('click', function() {
        if (kardexCurrentPage > 1) {
            kardexCurrentPage--;
            cargarKardexPaginado(kardexCurrentProductoId, kardexCurrentPage);
        }
    });

    $('#filtro_daterange_kardex').on('apply.daterangepicker', function(ev, picker) {
        kardexCurrentPage = 1;
        cargarKardexPaginado(kardexCurrentProductoId, kardexCurrentPage);
    });

    function cargarKardexPaginado(productoId, page) {
        let kardexBody = $('#kardexBody');
        kardexBody.html('<tr><td colspan="6" class="text-center">Cargando...</td></tr>');
        
        let picker = $('#filtro_daterange_kardex').data('daterangepicker');
        let startDate = picker.startDate.format('YYYY-MM-DD');
        let endDate = picker.endDate.format('YYYY-MM-DD');

        $.get(`../app/controllers/MovimientoController.php?action=kardex&producto_id=${productoId}&page=${page}&start_date=${startDate}&end_date=${endDate}`, function(response) {
            kardexBody.empty();
            kardexTotalPages = response.total_pages;
            kardexCurrentPage = response.current_page;

            $('#infoPaginasKardex').text(`Página ${kardexCurrentPage} de ${kardexTotalPages}`);
            $('#btnPaginaAnteriorKardex').prop('disabled', kardexCurrentPage <= 1);
            $('#btnPaginaSiguienteKardex').prop('disabled', kardexCurrentPage >= kardexTotalPages);

            if (response.data.length > 0) {
                response.data.forEach(function(mov) {
                    let tipoBadge = mov.tipo_movimiento === 'Entrada' ? 'bg-success' : 'bg-danger';
                    let cantidad = mov.tipo_movimiento === 'Entrada' ? `+${mov.cantidad}` : `-${mov.cantidad}`;
                    let proveedor = mov.nombre_proveedor || '<em>Interno</em>';
                    let notas = mov.notas || '';

                    let fila = `<tr>
                        <td>${moment(mov.fecha_movimiento).format('DD/MM/YYYY hh:mm A')}</td>
                        <td><span class="badge ${tipoBadge}">${mov.tipo_movimiento}</span></td>
                        <td>${cantidad}</td>
                        <td>${mov.usuario}</td>
                        <td>${proveedor}</td>
                        <td>${notas}</td>
                    </tr>`;
                    kardexBody.append(fila);
                });
            } else {
                kardexBody.html('<tr><td colspan="6" class="text-center">No hay movimientos para este producto en el rango de fechas seleccionado.</td></tr>');
            }
        });
    }
});
