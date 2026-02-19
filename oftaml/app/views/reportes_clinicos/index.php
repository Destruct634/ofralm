<?php
// app/views/reportes_clinicos/index.php
if (!Auth::check('reportes_clinicos', 'ver')) {
    echo "<div class='container-fluid px-4'><div class='alert alert-danger mt-4'><h3>Acceso Denegado</h3><p>No tienes permiso para ver esta sección.</p></div></div>";
    include_once '../app/views/templates/footer.php';
    exit;
}
?>

<div class="container-fluid px-4">
    <h1 class="mt-4">Reportes Clínicos</h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="index.php?page=dashboard">Dashboard</a></li>
        <li class="breadcrumb-item active">Reportes Clínicos</li>
    </ol>

    <div class="row">
        <div class="col-lg-8">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Top 10 Diagnósticos Más Comunes</h6>
                </div>
                <div class="card-body">
                    <p class="text-muted small">Este reporte cuenta el número total de veces que cada diagnóstico ha sido "etiquetado" en todas las visitas del historial.</p>
                    <div class="chart-area" style="height: 400px; position: relative;">
                        <canvas id="chartTopDiagnosticos"></canvas>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-lg-4">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Distribución de Antecedentes</h6>
                </div>
                <div class="card-body">
                    <p class="text-muted small">Pacientes con antecedentes patológicos (DM, HTA, Glaucoma, Asma) en toda la base de datos.</p>
                    <div class="chart-area" style="height: 400px; position: relative;">
                        <canvas id="chartDistribucionPoblacion"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-6">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Distribución de Errores Refractivos</h6>
                </div>
                <div class="card-body">
                    <p class="text-muted small">Clasificación de todos los ojos (OD y OS) registrados en refracciones actuales, según su esfera (Miopía vs. Hipermetropía).</p>
                    <div class="chart-area" style="height: 350px; position: relative;">
                        <canvas id="chartErroresRefractivos"></canvas>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-6">
            </div>
    </div>
</div>