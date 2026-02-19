<?php
// app/views/reportes/stock.php
// VERIFICACIÓN DE SEGURIDAD ESPECÍFICA
if (!Auth::check('reporte_stock', 'ver')) {
    echo "<div class='container-fluid px-4'><div class='alert alert-danger mt-4'><h3>Acceso Denegado</h3><p>No tienes permiso para ver este reporte.</p></div></div>";
    include_once '../app/views/templates/footer.php';
    exit;
}
?>

<div class="container-fluid px-4">
    <h1 class="mt-4">Reporte de Stock de Inventario</h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="index.php?page=dashboard">Dashboard</a></li>
        <li class="breadcrumb-item active">Reporte de Stock</li>
    </ol>
    <div class="card mb-4">
        <div class="card-header">
            <i class="fas fa-boxes me-1"></i>
            Estado Actual del Inventario
        </div>
        <div class="card-body">
            <table id="tablaStock" class="table table-striped table-bordered" style="width:100%">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Producto</th>
                        <th>Categoría</th>
                        <th>Stock Actual</th>
                        <th>Stock Mínimo</th>
                        <th>Estado</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>
</div>