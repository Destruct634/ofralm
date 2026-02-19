$(document).ready(function() {
    // Configuración del rango de fechas
    $('#filtro_daterange_medico').daterangepicker({
        startDate: moment(),
        endDate: moment(),
        locale: {
            "format": "DD/MM/YYYY",
            "separator": " - ",
            "applyLabel": "Aplicar",
            "cancelLabel": "Cancelar",
            "fromLabel": "Desde",
            "toLabel": "Hasta",
            "customRangeLabel": "Personalizado",
            "weekLabel": "S",
            "daysOfWeek": ["Do", "Lu", "Ma", "Mi", "Ju", "Vi", "Sá"],
            "monthNames": ["Enero", "Febrero", "Marzo", "Abril", "Mayo", "Junio", "Julio", "Agosto", "Septiembre", "Octubre", "Noviembre", "Diciembre"],
            "firstDay": 1
        },
        ranges: {
           'Hoy': [moment(), moment()],
           'Mañana': [moment().add(1, 'days'), moment().add(1, 'days')],
           'Próximos 7 Días': [moment(), moment().add(6, 'days')],
           'Este Mes': [moment().startOf('month'), moment().endOf('month')]
        }
    });

    // Función para cargar los KPIs
    function cargarKPIs() {
        $.ajax({
            url: '../app/controllers/CitaController.php?action=get_kpis_medico',
            type: 'GET',
            dataType: 'json',
            success: function(data) {
                $('#kpi_total_hoy').text(data.total);
                $('#kpi_pendientes').text(data.pendientes);
                $('#kpi_atendidos').text(data.atendidos);
            }
        });
    }
    
    cargarKPIs();

    // --- CORRECCIÓN: Evitar error de reinicialización ---
    if ($.fn.DataTable.isDataTable('#tablaCitasMedico')) {
        $('#tablaCitasMedico').DataTable().destroy();
    }
    // --------------------------------------------------

    let tablaCitas = $('#tablaCitasMedico').DataTable({
        "ajax": { 
            "url": "../app/controllers/CitaController.php?action=mis_citas",
            "type": "GET",
            "data": function(d) {
                let picker = $('#filtro_daterange_medico').data('daterangepicker');
                d.start_date = picker.startDate.format('YYYY-MM-DD');
                d.end_date = picker.endDate.format('YYYY-MM-DD');
            },
            "dataSrc": "data" 
        },
        "columns": [
            // Columna Hora con AM/PM
            { 
                "data": "fecha_cita", 
                "render": function (data, type, row) { 
                    let fecha = moment(data);
                    let hoy = moment();
                    let esHoy = fecha.isSame(hoy, 'day');
                    
                    // Formato AM/PM
                    let hora = moment(row.hora_cita, 'HH:mm:ss').format('h:mm A');

                    if (esHoy) {
                        return `<span class="fs-5 fw-bold text-dark">${hora}</span>`;
                    } else {
                        return `<span class="fw-bold">${hora}</span><br><small class="text-muted">${fecha.format('DD/MM')}</small>`;
                    }
                }
            },
            { 
                "data": "paciente",
                "render": function(data, type, row) {
                    return `<div>
                                <div class="fw-bold text-primary">${data}</div>
                                <div class="small text-muted"><i class="fas fa-phone-alt me-1"></i> ${row.paciente_telefono || 'N/A'}</div>
                            </div>`;
                }
            },
            { 
                "data": "motivo_detalle",
                "render": function(data, type, row) {
                    return `<div>
                                <div>${data}</div>
                                <span class="badge bg-light text-secondary border">${row.tipo_cita || 'Cita'}</span>
                            </div>`;
                }
            },
            { "data": "estado", "render": function(data) {
                let badgeClass = '';
                let icon = '';
                switch (data) {
                    case 'Programada': badgeClass = 'bg-primary'; icon = '<i class="fas fa-clock me-1"></i>'; break;
                    case 'Completada': badgeClass = 'bg-success'; icon = '<i class="fas fa-check me-1"></i>'; break;
                    case 'Cancelada': badgeClass = 'bg-danger'; icon = '<i class="fas fa-times me-1"></i>'; break;
                    default: badgeClass = 'bg-secondary';
                }
                return `<span class="badge ${badgeClass}">${icon} ${data}</span>`;
            }},
            // --- Columna Acciones con DOS botones ---
            { 
                "data": null,
                "className": "text-center",
                "render": function(data, type, row) {
                    if (row.estado === 'Programada') {
                        return `<div class="d-flex gap-1">
                                    <button class="btn btn-success flex-grow-1 btn-registrar-historial" 
                                        data-cita-id="${row.id}" 
                                        data-paciente-id="${row.id_paciente}" 
                                        data-medico-id="${row.id_medico}"
                                        title="Iniciar Consulta">
                                        <i class="fas fa-stethoscope me-1"></i> Atender
                                    </button>
                                    <a href="index.php?page=historial&paciente_id=${row.id_paciente}" 
                                       class="btn btn-outline-primary" 
                                       target="_blank" 
                                       title="Ver Expediente Anterior">
                                        <i class="fas fa-file-medical-alt"></i>
                                    </a>
                                </div>`;
                    } 
                    else if (row.estado === 'Completada') {
                        return `<a href="index.php?page=historial&paciente_id=${row.id_paciente}" class="btn btn-outline-secondary w-100">
                                    <i class="fas fa-file-medical-alt me-2"></i> Ver Expediente
                                </a>`;
                    }
                    else {
                        return `<button class="btn btn-secondary w-100 btn-sm" disabled>Sin acciones</button>`;
                    }
                }
            }
        ],
        "order": [[ 0, "asc" ]]
    });

    $('#btnFiltrarMedico').on('click', function() {
        tablaCitas.ajax.reload();
        cargarKPIs(); 
    });
    
    $('#filtro_daterange_medico').on('apply.daterangepicker', function(ev, picker) {
        tablaCitas.ajax.reload();
        cargarKPIs();
    });
    
    // Auto-refresco
    setInterval(function() {
        if ($.fn.DataTable.isDataTable('#tablaCitasMedico')) {
            tablaCitas.ajax.reload(null, false);
            cargarKPIs();
        }
    }, 60000);
});