<?php
if (!Auth::check('reportes_medicos', 'ver')) {
    echo "<div class='container-fluid px-4'><div class='alert alert-danger mt-4'><h3>Acceso Denegado</h3><p>No tienes permiso para ver este reporte.</p></div></div>";
    include_once '../app/views/templates/footer.php';
    exit;
}
?>
<div class="container-fluid px-4">
    <h1 class="mt-4">Reportes Médicos</h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="index.php?page=dashboard">Dashboard</a></li>
        <li class="breadcrumb-item active">Reportes de Productividad</li>
    </ol>

    <div class="card mb-4">
        <div class="card-header bg-primary text-white">
            <i class="fas fa-filter me-1"></i> Filtros de Reporte
        </div>
        <div class="card-body">
            <div class="row align-items-end">
                <div class="col-md-4">
                    <label for="reporte_medico_id" class="form-label">Seleccionar Médico:</label>
                    <select id="reporte_medico_id" class="form-select">
                        <option value="">Cargando...</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label for="reporte_rango_fechas" class="form-label">Rango de Fechas:</label>
                    <input type="text" id="reporte_rango_fechas" class="form-control">
                </div>
                <div class="col-md-4">
                    <button id="btnGenerarReporte" class="btn btn-success w-100">
                        <i class="fas fa-chart-pie me-1"></i> Generar Gráficos
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div id="contenedor_resultados" style="display: none;">
        
        <div class="row mb-4">
            <div class="col-xl-4 col-md-6">
                <div class="card bg-success text-white mb-4 h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <div class="small text-white-50">Ingresos Facturados (Periodo)</div>
                                <div class="fs-4 fw-bold" id="kpi_ingresos">L 0.00</div>
                            </div>
                            <i class="fas fa-money-bill-wave fa-3x opacity-25"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-4 col-md-6">
                <div class="card bg-info text-white mb-4 h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <div class="small text-white-50">Total Citas Atendidas</div>
                                <div class="fs-4 fw-bold" id="kpi_total_citas">0</div>
                            </div>
                            <i class="fas fa-user-md fa-3x opacity-25"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-6">
                <div class="card mb-4">
                    <div class="card-header"><i class="fas fa-chart-bar me-1"></i> Citas por Tipo de Servicio</div>
                    <div class="card-body"><canvas id="chartCitasServicio" width="100%" height="50"></canvas></div>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="card mb-4">
                    <div class="card-header"><i class="fas fa-chart-pie me-1"></i> Presentismo de Pacientes</div>
                    <div class="card-body">
                        <div class="chart-pie-container" style="position: relative; height:300px; width:100%">
                             <canvas id="chartPresentismo"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-12">
                <div class="card mb-4">
                    <div class="card-header"><i class="fas fa-chart-line me-1"></i> Tendencia de Ingresos Diarios</div>
                    <div class="card-body"><canvas id="chartIngresosDiarios" width="100%" height="30"></canvas></div>
                </div>
            </div>
        </div>

    </div>
</div>