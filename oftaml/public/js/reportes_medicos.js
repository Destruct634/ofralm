$(document).ready(function() {
    
    // 1. Inicializar DateRangePicker
    $('#reporte_rango_fechas').daterangepicker({
        startDate: moment().startOf('month'),
        endDate: moment().endOf('month'),
        locale: {
            "format": "DD/MM/YYYY", "separator": " - ", "applyLabel": "Aplicar", "cancelLabel": "Cancelar", "fromLabel": "Desde", "toLabel": "Hasta",
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

    // 2. Cargar lista de médicos
    $.ajax({
        url: '../app/controllers/ReporteMedicoController.php?action=get_medicos',
        type: 'GET',
        dataType: 'json',
        success: function(medicos) {
            let select = $('#reporte_medico_id');
            select.empty();
            select.append('<option value="">-- Seleccione un Médico --</option>');
            medicos.forEach(m => {
                select.append(`<option value="${m.id}">${m.nombres} ${m.apellidos}</option>`);
            });
        }
    });

    // Variables globales para los gráficos
    let chartCitas = null;
    let chartPresentismo = null;
    let chartIngresos = null;

    // 3. Generar Reporte
    $('#btnGenerarReporte').click(function() {
        let medicoId = $('#reporte_medico_id').val();
        let range = $('#reporte_rango_fechas').val().split(' - ');
        
        if(!medicoId) {
            Swal.fire('Error', 'Por favor seleccione un médico.', 'warning');
            return;
        }

        let startDate = moment(range[0], 'DD/MM/YYYY').format('YYYY-MM-DD');
        let endDate = moment(range[1], 'DD/MM/YYYY').format('YYYY-MM-DD');

        const btn = $(this);
        btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Generando...');
        $('#contenedor_resultados').hide();

        $.ajax({
            url: '../app/controllers/ReporteMedicoController.php',
            type: 'GET',
            data: {
                action: 'generar_reporte',
                medico_id: medicoId,
                start_date: startDate,
                end_date: endDate
            },
            dataType: 'json',
            success: function(data) {
                $('#contenedor_resultados').fadeIn();
                actualizarKPIs(data);
                renderizarGraficos(data);
            },
            error: function() {
                Swal.fire('Error', 'Hubo un problema al generar el reporte.', 'error');
            },
            complete: function() {
                btn.prop('disabled', false).html('<i class="fas fa-chart-pie me-1"></i> Generar Gráficos');
            }
        });
    });

    function actualizarKPIs(data) {
        let totalIngresos = parseFloat(data.total_ingresos);
        $('#kpi_ingresos').text('L ' + totalIngresos.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2}));
        
        let totalCitas = 0;
        if(data.presentismo) {
             data.presentismo.forEach(p => totalCitas += parseInt(p.cantidad));
        }
        $('#kpi_total_citas').text(totalCitas);
    }

    function renderizarGraficos(data) {
        // 1. Gráfico de Barras (Citas por Servicio)
        const ctxCitas = document.getElementById('chartCitasServicio').getContext('2d');
        if (chartCitas) chartCitas.destroy();

        let labelsServ = data.citas_servicio.map(x => x.nombre_servicio);
        let dataServ = data.citas_servicio.map(x => x.total);

        chartCitas = new Chart(ctxCitas, {
            type: 'bar',
            data: {
                labels: labelsServ,
                datasets: [{
                    label: 'Citas Atendidas',
                    data: dataServ,
                    backgroundColor: 'rgba(54, 162, 235, 0.7)',
                    borderColor: 'rgba(54, 162, 235, 1)',
                    borderWidth: 1
                }]
            },
            options: {
                scales: { y: { beginAtZero: true, ticks: { stepSize: 1 } } },
                plugins: { legend: { display: false } }
            }
        });

        // 2. Gráfico de Dona (Presentismo) - CON PORCENTAJES
        const ctxPres = document.getElementById('chartPresentismo').getContext('2d');
        if (chartPresentismo) chartPresentismo.destroy();

        let labelsPres = data.presentismo.map(x => x.estado);
        let dataPres = data.presentismo.map(x => parseInt(x.cantidad)); // Asegurar que sean números
        
        let colormap = { 'Completada': '#198754', 'Programada': '#0d6efd', 'Cancelada': '#dc3545', 'No se presentó': '#ffc107' };
        let bgColors = labelsPres.map(label => colormap[label] || '#6c757d');

        chartPresentismo = new Chart(ctxPres, {
            type: 'doughnut',
            data: {
                labels: labelsPres,
                datasets: [{
                    data: dataPres,
                    backgroundColor: bgColors,
                    hoverOffset: 4
                }]
            },
            options: {
                maintainAspectRatio: false,
                plugins: { 
                    legend: { position: 'right' },
                    // --- MODIFICACIÓN: Tooltip con Porcentaje ---
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                let label = context.label || '';
                                let value = context.raw || 0;
                                let dataset = context.chart.data.datasets[0];
                                let total = dataset.data.reduce((a, b) => a + b, 0);
                                let percentage = Math.round((value / total) * 100) + '%';
                                return `${label}: ${value} (${percentage})`;
                            }
                        }
                    }
                    // --- FIN MODIFICACIÓN ---
                }
            }
        });

        // 3. Gráfico de Línea (Ingresos Diarios)
        const ctxIng = document.getElementById('chartIngresosDiarios').getContext('2d');
        if (chartIngresos) chartIngresos.destroy();

        let labelsIng = data.ingresos_dia.map(x => moment(x.fecha).format('DD/MM'));
        let dataIng = data.ingresos_dia.map(x => x.total);

        chartIngresos = new Chart(ctxIng, {
            type: 'line',
            data: {
                labels: labelsIng,
                datasets: [{
                    label: 'Ingresos (L)',
                    data: dataIng,
                    borderColor: '#198754',
                    backgroundColor: 'rgba(25, 135, 84, 0.1)',
                    fill: true,
                    tension: 0.3
                }]
            },
            options: {
                scales: { y: { beginAtZero: true } }
            }
        });
    }
});