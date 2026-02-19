$(document).ready(function() {
    
    // Inicializar DateRangePicker
    $('#filtro_fechas').daterangepicker({
        startDate: moment().startOf('month'),
        endDate: moment().endOf('month'),
        locale: { format: 'DD/MM/YYYY', separator: ' - ', applyLabel: 'Aplicar', cancelLabel: 'Cancelar', fromLabel: 'Desde', toLabel: 'Hasta', customRangeLabel: 'Personalizado' }
    });

    // Cargar Médicos y Técnicos
    function cargarListas() {
        $.get('../app/controllers/MedicoController.php?action=get_activos', function(data) {
            let select = $('#filtro_medico');
            if(Array.isArray(data)) {
                data.forEach(m => select.append(new Option(`${m.nombres} ${m.apellidos}`, m.id)));
            }
        }, 'json');

        $.get('../app/controllers/UsuarioController.php?action=get_tecnicos', function(data) {
            let select = $('#filtro_tecnico');
            if(Array.isArray(data)) {
                data.forEach(t => select.append(new Option(t.nombre_completo, t.id)));
            }
        }, 'json');
    }
    cargarListas();

    // Inicializar DataTable
    let tabla = $('#tablaReporteVentas').DataTable({
        dom: 'Bfrtip', 
        buttons: ['copy', 'excel', 'pdf'],
        // CORRECCIÓN CRÍTICA: Usamos una función ajax dummy.
        // Esto evita que la configuración global del footer intente cargar la URL actual.
        ajax: function(data, callback, settings) {
            callback({data: []});
        },
        columns: [
            { data: 'fecha_emision', render: (d) => moment(d).format('DD/MM/YYYY HH:mm') },
            { data: 'correlativo' },
            { data: 'paciente' },
            { data: 'descripcion_item' },
            { data: 'tipo_item', render: (d) => d === 'Producto' ? '<span class="badge bg-info">Prod</span>' : '<span class="badge bg-primary">Serv</span>' },
            { data: null, render: (row) => {
                let html = '';
                if(row.medico) html += `<small><b>Med:</b> ${row.medico}</small><br>`;
                if(row.tecnico) html += `<small><b>Tec:</b> ${row.tecnico}</small>`;
                return html;
            }},
            { data: 'cantidad', className: 'text-end' },
            { data: 'subtotal_item', className: 'text-end', render: (d) => 'L ' + parseFloat(d).toLocaleString('en-US', {minimumFractionDigits: 2}) }
        ],
        language: { 
            "decimal": "",
            "emptyTable": "No hay datos disponibles en la tabla",
            "info": "Mostrando _START_ a _END_ de _TOTAL_ entradas",
            "infoEmpty": "Mostrando 0 a 0 de 0 entradas",
            "infoFiltered": "(filtrado de _MAX_ entradas totales)",
            "infoPostFix": "",
            "thousands": ",",
            "lengthMenu": "Mostrar _MENU_ entradas",
            "loadingRecords": "Cargando...",
            "processing": "Procesando...",
            "search": "Buscar:",
            "zeroRecords": "No se encontraron registros coincidentes",
            "paginate": {
                "first": "Primero",
                "last": "Último",
                "next": "Siguiente",
                "previous": "Anterior"
            },
            "aria": {
                "sortAscending": ": activar para ordenar la columna ascendente",
                "sortDescending": ": activar para ordenar la columna descendente"
            }
        }
    });

    // Generar Reporte
    $('#formFiltrosVentas').submit(function(e) {
        e.preventDefault();
        
        let fechas = $('#filtro_fechas').val().split(' - ');
        let params = {
            action: 'generar',
            fecha_inicio: moment(fechas[0], 'DD/MM/YYYY').format('YYYY-MM-DD'),
            fecha_fin: moment(fechas[1], 'DD/MM/YYYY').format('YYYY-MM-DD'),
            tipo: $('#filtro_tipo').val(),
            id_medico: $('#filtro_medico').val(),
            id_tecnico: $('#filtro_tecnico').val()
        };

        Swal.fire({title: 'Generando...', didOpen: () => Swal.showLoading()});

        $.ajax({
            url: '../app/controllers/ReporteVentaController.php',
            type: 'GET',
            data: params,
            dataType: 'json',
            success: function(response) {
                Swal.close();
                tabla.clear();
                tabla.rows.add(response.data);
                tabla.draw();
                $('#lbl_total_items').text(response.resumen.cantidad_items);
                $('#lbl_total_monto').text('L ' + parseFloat(response.resumen.total_venta).toLocaleString('en-US', {minimumFractionDigits: 2}));
            },
            error: function(xhr, status, error) {
                Swal.fire('Error', 'No se pudo generar el reporte. Verifique la consola.', 'error');
                console.error(xhr.responseText);
            }
        });
    });

    $('#btnImprimirReporte').click(function() {
        let fechas = $('#filtro_fechas').val().split(' - ');
        let fi = moment(fechas[0], 'DD/MM/YYYY').format('YYYY-MM-DD');
        let ff = moment(fechas[1], 'DD/MM/YYYY').format('YYYY-MM-DD');
        let tipo = $('#filtro_tipo').val();
        let med = $('#filtro_medico').val();
        let tec = $('#filtro_tecnico').val();
        let url = `../app/reports/ventas_print.php?fi=${fi}&ff=${ff}&tipo=${tipo}&med=${med}&tec=${tec}`;
        window.open(url, '_blank');
    });
});