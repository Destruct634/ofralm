<?php
// INCLUIR LOS MODELOS NECESARIOS
include_once __DIR__ . '/../../models/DashboardWidget.php';
include_once __DIR__ . '/../../models/Cita.php';
include_once __DIR__ . '/../../models/Factura.php';
include_once __DIR__ . '/../../models/Paciente.php';
include_once __DIR__ . '/../../models/Producto.php';
include_once __DIR__ . '/../../models/Historial.php';
// HistorialRefraccion.php ya no es necesario aquí

// 1. OBTENER WIDGETS ACTIVOS
$widget_model = new DashboardWidget($db);
$active_widgets = $widget_model->leerActivos($_SESSION['user_group_id']);

// 2. PREPARAR MODELOS Y OBTENER DATOS PARA LOS WIDGETS
$dashboard_data = [];
$cita_model = new Cita($db);
$factura_model = new Factura($db);
$paciente_model = new Paciente($db);
$producto_model = new Producto($db);
$historial_model = new Historial($db);
// $refraccion_model ya no es necesario aquí

// --- INICIO DE MODIFICACIÓN 1: Re-clasificar el widget ---
$kpi_widgets = [];
$main_chart_widgets = [];
$list_widgets = [];
$secondary_chart_widgets = [];

foreach ($active_widgets as $widget) {
    switch ($widget['widget_key']) {
        case 'kpi_citas_hoy':
        case 'kpi_facturacion_dia':
        case 'kpi_pacientes_nuevos':
        case 'kpi_cuentas_por_cobrar':
            // 'kpi_pio_alta' se quitó de aquí
            $kpi_widgets[] = $widget;
            break;
        
        case 'chart_ingresos_semana':
            $main_chart_widgets[] = $widget;
            break;

        case 'list_proximas_citas':
        case 'list_stock_bajo':
        case 'kpi_pio_alta': // <-- Se movió aquí
            $list_widgets[] = $widget;
            break;

        case 'chart_ingresos_mensuales':
            $secondary_chart_widgets[] = $widget;
            break;
        
        // case 'chart_errores_refractivos' HA SIDO ELIMINADO
    }
}
// --- FIN DE MODIFICACIÓN 1 ---

// 3. OBTENER DATOS (ahora basado en los arrays clasificados)
// (Optimizador: Solo cargamos los datos que los widgets activos realmente necesitan)

// Datos para KPIs
if (!empty($kpi_widgets)) {
    foreach($kpi_widgets as $w) {
        if ($w['widget_key'] == 'kpi_citas_hoy' && !isset($dashboard_data['citas_hoy'])) $dashboard_data['citas_hoy'] = $cita_model->contarCitasHoy();
        if ($w['widget_key'] == 'kpi_facturacion_dia' && !isset($dashboard_data['facturacion_dia'])) $dashboard_data['facturacion_dia'] = $factura_model->sumarFacturacionHoy();
        if ($w['widget_key'] == 'kpi_pacientes_nuevos' && !isset($dashboard_data['pacientes_nuevos'])) $dashboard_data['pacientes_nuevos'] = $paciente_model->contarNuevosEsteMes();
        if ($w['widget_key'] == 'kpi_cuentas_por_cobrar' && !isset($dashboard_data['cuentas_por_cobrar'])) $dashboard_data['cuentas_por_cobrar'] = $factura_model->obtenerSaldoTotalCuentasPorCobrar();
    }
}
// Datos para Gráficos Principales
if (!empty($main_chart_widgets)) {
    foreach($main_chart_widgets as $w) {
        if ($w['widget_key'] == 'chart_ingresos_semana' && !isset($dashboard_data['ingresos_semana'])) $dashboard_data['ingresos_semana'] = $factura_model->obtenerIngresosUltimos7Dias();
    }
}
// Datos para Listas
if (!empty($list_widgets)) {
     foreach($list_widgets as $w) {
        if ($w['widget_key'] == 'list_proximas_citas' && !isset($dashboard_data['proximas_citas'])) $dashboard_data['proximas_citas'] = $cita_model->leerProximasCitas(5);
        if ($w['widget_key'] == 'list_stock_bajo' && !isset($dashboard_data['stock_bajo'])) $dashboard_data['stock_bajo'] = $producto_model->leerProductosConBajoStock(5);
        if ($w['widget_key'] == 'kpi_pio_alta' && !isset($dashboard_data['pio_alta_hoy'])) $dashboard_data['pio_alta_hoy'] = $historial_model->contarPioAltaHoy(); // <-- Se carga aquí
    }
}
// Datos para Gráficos Secundarios
if (!empty($secondary_chart_widgets)) {
    foreach($secondary_chart_widgets as $w) {
        // === INICIO DE MODIFICACIÓN ===
        // Cargamos AMBOS datos
        if ($w['widget_key'] == 'chart_ingresos_mensuales') {
            if (!isset($dashboard_data['ingresos_mensuales'])) {
                $dashboard_data['ingresos_mensuales'] = $factura_model->obtenerIngresosMensualesPorCategoria();
            }
            // Añadimos la carga para el nuevo gráfico de citas
            if (!isset($dashboard_data['citas_por_tipo'])) {
                $dashboard_data['citas_por_tipo'] = $cita_model->obtenerConteoCitasPorCategoriaSemana();
            }
        }
        // === FIN DE MODIFICACIÓN ===
    }
}

?>

<div class="container-fluid px-4">
    <h1 class="mt-4">Dashboard</h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item active">Resumen General</li>
    </ol>

    <div class="row">
        <?php foreach ($kpi_widgets as $widget): ?>
            <div class="col-xl-3 col-md-6 mb-4">
                <?php if ($widget['widget_key'] == 'kpi_citas_hoy'): ?>
                    <div class="card border-left-primary shadow h-100 py-2">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Citas para Hoy</div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $dashboard_data['citas_hoy']; ?></div>
                                </div>
                                <div class="col-auto"><i class="fas fa-calendar-day fa-2x text-gray-300"></i></div>
                            </div>
                        </div>
                    </div>
                <?php elseif ($widget['widget_key'] == 'kpi_facturacion_dia'): ?>
                    <div class="card border-left-success shadow h-100 py-2">
                         <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Facturación del Día</div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800">L <?php echo number_format($dashboard_data['facturacion_dia'], 2); ?></div>
                                </div>
                                <div class="col-auto"><i class="fas fa-dollar-sign fa-2x text-gray-300"></i></div>
                            </div>
                        </div>
                    </div>
                <?php elseif ($widget['widget_key'] == 'kpi_pacientes_nuevos'): ?>
                     <div class="card border-left-info shadow h-100 py-2">
                         <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Pacientes Nuevos (Mes)</div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $dashboard_data['pacientes_nuevos']; ?></div>
                                </div>
                                <div class="col-auto"><i class="fas fa-user-plus fa-2x text-gray-300"></i></div>
                            </div>
                        </div>
                    </div>
                <?php elseif ($widget['widget_key'] == 'kpi_cuentas_por_cobrar'): ?>
                    <div class="card border-left-warning shadow h-100 py-2">
                         <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Cuentas por Cobrar</div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800">L <?php echo number_format($dashboard_data['cuentas_por_cobrar'], 2); ?></div>
                                </div>
                                <div class="col-auto"><i class="fas fa-comments-dollar fa-2x text-gray-300"></i></div>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    </div>

    <div class="row">
        <div class="col-xl-8">
            <?php foreach ($main_chart_widgets as $widget): ?>
                <?php if ($widget['widget_key'] == 'chart_ingresos_semana'): ?>
                    <div class="card shadow mb-4">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-primary">Ingresos de los Últimos 7 Días</h6>
                        </div>
                        <div class="card-body">
                            <canvas id="ingresosChart"></canvas>
                        </div>
                    </div>
                <?php endif; ?>
            <?php endforeach; ?>
        </div>

        <div class="col-xl-4">
            <?php foreach ($list_widgets as $widget): ?>
                
                <?php if ($widget['widget_key'] == 'list_proximas_citas'): ?>
                     <div class="card shadow mb-4">
                        <div class="card-header py-3">
                             <h6 class="m-0 font-weight-bold text-primary">Próximas Citas del Día</h6>
                        </div>
                        <div class="card-body">
                            <?php if (empty($dashboard_data['proximas_citas'])): ?>
                                <p class="text-center text-muted">No hay más citas programadas para hoy.</p>
                            <?php else: ?>
                                <ul class="list-group list-group-flush">
                                <?php foreach($dashboard_data['proximas_citas'] as $cita): ?>
                                    <li class="list-group-item d-flex justify-content-between align-items-center">
                                        <div>
                                            <strong><?php echo htmlspecialchars($cita['paciente_nombre']); ?></strong>
                                            <small class="d-block text-muted"><?php echo htmlspecialchars($cita['motivo']); ?></small>
                                        </div>
                                        <span class="badge bg-primary rounded-pill"><?php echo date("g:i A", strtotime($cita['hora_cita'])); ?></span>
                                    </li>
                                <?php endforeach; ?>
                                </ul>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endif; ?>

                <?php if ($widget['widget_key'] == 'list_stock_bajo'): ?>
                     <div class="card shadow mb-4 border-left-danger">
                        <div class="card-header py-3">
                             <h6 class="m-0 font-weight-bold text-danger">Alerta de Inventario Bajo</h6>
                        </div>
                        <div class="card-body">
                            <?php if (empty($dashboard_data['stock_bajo'])): ?>
                                <p class="text-center text-muted">No hay productos con bajo stock.</p>
                            <?php else: ?>
                                <div class="table-responsive">
                                    <table class="table table-sm table-hover">
                                        <thead><tr><th>Producto</th><th class="text-center">Actual</th><th class="text-center">Mínimo</th></tr></thead>
                                        <tbody>
                                        <?php foreach($dashboard_data['stock_bajo'] as $producto): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($producto['nombre_producto']); ?></td>
                                                <td class="text-center"><span class="badge bg-danger"><?php echo $producto['stock_actual']; ?></span></td>
                                                <td class="text-center"><?php echo $producto['stock_minimo']; ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endif; ?>

                <?php if ($widget['widget_key'] == 'kpi_pio_alta'): ?>
                    <div class="card border-left-danger shadow mb-4 py-2">
                         <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">Alerta: PIO Alta (Hoy)</div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $dashboard_data['pio_alta_hoy']; ?> Pacientes</div>
                                </div>
                                <div class="col-auto"><i class="fas fa-exclamation-triangle fa-2x text-gray-300"></i></div>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>

            <?php endforeach; ?>
            </div>
    </div>

    <div class="row">
        <?php foreach ($secondary_chart_widgets as $widget): ?>
            <div class="col-lg-6">
                <?php if ($widget['widget_key'] == 'chart_ingresos_mensuales'): ?>
                    <div class="card shadow mb-4">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-primary">Ingresos Mensuales por Categoría (Últimos 12 Meses)</h6>
                        </div>
                        <div class="card-body">
                            <div class="chart-area" style="height: 320px;">
                                <canvas id="ingresosMensualesChart"></canvas>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>

            <div class="col-lg-6">
                <?php if ($widget['widget_key'] == 'chart_ingresos_mensuales'): // Reutilizamos el mismo widget/permiso ?>
                    <div class="card shadow mb-4">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-primary">Citas por Tipo (Últimos 7 Días)</h6>
                        </div>
                        <div class="card-body">
                            <div class="chart-area" style="height: 320px;">
                                <canvas id="citasPorTipoChart"></canvas>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
            <?php endforeach; ?>
    </div>

</div>

<?php if (isset($dashboard_data['ingresos_semana'])): ?>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const ctx = document.getElementById('ingresosChart');
    const ingresosData = <?php echo json_encode($dashboard_data['ingresos_semana']); ?>;
    
    const labels = ingresosData.map(item => moment(item.fecha).format('dddd DD'));
    const data = ingresosData.map(item => item.total);

    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [{
                label: 'Ingresos (L)',
                data: data,
                backgroundColor: 'rgba(78, 115, 223, 0.8)',
                borderColor: 'rgba(78, 115, 223, 1)',
                borderWidth: 1
            }]
        },
        options: {
            scales: {
                y: { beginAtZero: true }
            },
            plugins: {
                legend: { display: false }
            }
        }
    });
});
</script>
<?php endif; ?>

<?php if (isset($dashboard_data['ingresos_mensuales'])): ?>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const ctxMensual = document.getElementById('ingresosMensualesChart');
    const ingresosData = <?php echo json_encode($dashboard_data['ingresos_mensuales']); ?>;

    const labels = ingresosData.map(item => item.label);
    const productosData = ingresosData.map(item => item.Producto);
    const serviciosData = ingresosData.map(item => item.Servicio);

    new Chart(ctxMensual, {
        type: 'line',
        data: {
            labels: labels,
            datasets: [
                {
                    label: 'Ingresos por Productos (L)',
                    data: productosData,
                    borderColor: 'rgba(78, 115, 223, 1)',
                    backgroundColor: 'rgba(78, 115, 223, 0.1)',
                    fill: true,
                    tension: 0.3
                },
                {
                    label: 'Ingresos por Servicios (L)',
                    data: serviciosData,
                    borderColor: 'rgba(28, 200, 138, 1)',
                    backgroundColor: 'rgba(28, 200, 138, 0.1)',
                    fill: true,
                    tension: 0.3
                }
            ]
        },
        options: {
            maintainAspectRatio: false,
            scales: {
                y: { 
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) { return 'L ' + value; }
                    }
                }
            },
            plugins: {
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            let label = context.dataset.label || '';
                            if (label) {
                                label += ': ';
                            }
                            if (context.parsed.y !== null) {
                                label += new Intl.NumberFormat('es-HN', { style: 'currency', currency: 'HNL' }).format(context.parsed.y);
                            }
                            return label;
                        }
                    }
                }
            }
        }
    });
});
</script>
<?php endif; ?>

<?php if (isset($dashboard_data['citas_por_tipo'])): ?>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const ctxCitasTipo = document.getElementById('citasPorTipoChart');
    const citasData = <?php echo json_encode($dashboard_data['citas_por_tipo']); ?>;
    
    new Chart(ctxCitasTipo, {
        type: 'bar', // Gráfico de Barras
        data: {
            labels: citasData.labels,
            datasets: citasData.datasets // Los datasets ya vienen listos desde el modelo
        },
        options: {
            maintainAspectRatio: false,
            responsive: true,
            scales: {
                x: { stacked: true }, // Apilar en el eje X
                y: { 
                    stacked: true, // Apilar en el eje Y
                    beginAtZero: true,
                    ticks: { 
                        stepSize: 1 // Asegura que el eje Y muestre 1, 2, 3...
                    }
                }
            },
            plugins: { 
                legend: { position: 'top' }, // Leyenda arriba
                tooltip: { 
                    mode: 'index', // Mostrar tooltip para toda la pila
                    intersect: false 
                }
            }
        }
    });
});
</script>
<?php endif; ?>
<style>
/* Estilos adicionales para las tarjetas del dashboard */
.card .border-left-primary { border-left: 0.25rem solid #4e73df !important; }
.card .border-left-success { border-left: 0.25rem solid #1cc88a !important; }
.card .border-left-info { border-left: 0.25rem solid #36b9cc !important; }
.card .border-left-warning { border-left: 0.25rem solid #f6c23e !important; }
.card .border-left-danger { border-left: 0.25rem solid #e74a3b !important; }
.text-xs { font-size: 0.7rem; }
.text-gray-300 { color: #dddfeb !important; }
.text-gray-800 { color: #5a5c69 !important; }
.font-weight-bold { font-weight: 700 !important; }
</style>