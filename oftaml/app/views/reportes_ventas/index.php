<?php
if (!Auth::check('reportes_ventas', 'ver')) {
    echo "<div class='container-fluid px-4'><div class='alert alert-danger mt-4'><h3>Acceso Denegado</h3><p>No tienes permiso para ver este reporte.</p></div></div>";
    include_once '../app/views/templates/footer.php';
    exit;
}
?>
<div class="container-fluid px-4">
    <h1 class="mt-4">Reporte Detallado de Ventas</h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="index.php?page=dashboard">Dashboard</a></li>
        <li class="breadcrumb-item active">Reportes de Ventas</li>
    </ol>

    <div class="card mb-4">
        <div class="card-header bg-primary text-white">
            <i class="fas fa-filter me-1"></i> Filtros de Búsqueda
        </div>
        <div class="card-body">
            <form id="formFiltrosVentas">
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label">Rango de Fechas</label>
                        <input type="text" id="filtro_fechas" class="form-control">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Tipo</label>
                        <select id="filtro_tipo" class="form-select">
                            <option value="Todos">Todos</option>
                            <option value="Producto">Solo Productos</option>
                            <option value="Servicio">Solo Servicios</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Médico</label>
                        <select id="filtro_medico" class="form-select">
                            <option value="">Todos</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Técnico</label>
                        <select id="filtro_tecnico" class="form-select">
                            <option value="">Todos</option>
                        </select>
                    </div>
                </div>
                <div class="row mt-3">
                    <div class="col-md-12 text-end">
                        <button type="submit" class="btn btn-primary"><i class="fas fa-search me-1"></i> Generar Reporte</button>
                        <button type="button" id="btnImprimirReporte" class="btn btn-secondary"><i class="fas fa-print me-1"></i> Imprimir</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="row mb-3">
        <div class="col-md-12">
            <div class="alert alert-info d-flex justify-content-between align-items-center">
                <span><strong>Resultados encontrados:</strong> <span id="lbl_total_items">0</span> registros.</span>
                <span class="fs-5"><strong>Total Venta:</strong> <span id="lbl_total_monto">L 0.00</span></span>
            </div>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-body">
            <table id="tablaReporteVentas" class="table table-striped table-bordered table-sm" style="width:100%">
                <thead>
                    <tr>
                        <th style="width: 120px;">Fecha</th>
                        <th style="width: 100px;">Factura</th>
                        <th>Paciente</th>
                        <th>Descripción</th>
                        <th style="width: 50px;">Tipo</th>
                        <th style="width: 150px;">Responsable</th>
                        <th class="text-end" style="width: 50px;">Cant.</th>
                        <th class="text-end" style="width: 100px;">Total</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>
</div>