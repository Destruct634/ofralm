<?php
include_once '../app/models/ConfiguracionFacturacion.php';
$config_model = new ConfiguracionFacturacion($db);
$config = $config_model->leer();
?>
<div class="container-fluid px-4">
    <h1 class="mt-4">Configuración de Facturación</h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="index.php?page=dashboard">Dashboard</a></li>
        <li class="breadcrumb-item active">Configuración de Facturación</li>
    </ol>
    
    <div class="card">
        <div class="card-header"><i class="fas fa-cogs me-1"></i>Parámetros de Facturación</div>
        <div class="card-body">
            <form id="formConfigFacturacion">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="prefijo_correlativo" class="form-label">Prefijo del Correlativo</label>
                        <input type="text" class="form-control" id="prefijo_correlativo" value="<?php echo htmlspecialchars($config['prefijo_correlativo']); ?>" required>
                        <small class="form-text text-muted">Ej: FACT-, FAC-, etc.</small>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="siguiente_numero" class="form-label">Siguiente Número de Factura</label>
                        <input type="number" class="form-control" id="siguiente_numero" value="<?php echo htmlspecialchars($config['siguiente_numero']); ?>" required>
                        <small class="form-text text-muted">La próxima factura generada usará este número.</small>
                    </div>
                </div>
                <hr>
                <button type="submit" class="btn btn-primary"><i class="fas fa-save me-2"></i>Guardar Cambios</button>
            </form>
        </div>
    </div>
</div>
