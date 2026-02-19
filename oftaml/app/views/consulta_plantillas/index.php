<?php
// Incluir el verificador de permisos
include_once '../app/core/Auth.php';
if (!Auth::check('consulta_plantillas', 'ver')) {
    echo "<div class='container-fluid px-4'><div class='alert alert-danger mt-4'><h3>Acceso Denegado</h3><p>No tienes permiso para ver esta sección.</p></div></div>";
    include_once '../app/views/templates/footer.php';
    exit;
}
?>

<div class="container-fluid px-4">
    <h1 class="mt-4">Plantillas de Consulta</h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="index.php">Dashboard</a></li>
        <li class="breadcrumb-item active">Plantillas de Consulta</li>
    </ol>

    <div class="card mb-4">
        <div class="card-header">
            <i class="fas fa-file-medical-alt me-1"></i>
            Listado de Plantillas
            <?php if (Auth::check('consulta_plantillas', 'crear')): ?>
                <button class="btn btn-primary btn-sm float-end" id="btnNuevaPlantilla">
                    <i class="fas fa-plus me-1"></i> Nueva Plantilla
                </button>
            <?php endif; ?>
        </div>
        <div class="card-body">
            <table id="tablaPlantillas" class="table table-striped table-bordered" style="width:100%">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Título</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    </tbody>
            </table>
        </div>
    </div>
</div>

<div class="modal fade" id="modalPlantilla" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form id="formPlantilla">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalPlantillaLabel">Nueva Plantilla</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="plantilla_id" name="id">
                    
                    <div class="mb-3">
                        <label for="titulo" class="form-label">Título de la Plantilla</label>
                        <input type="text" class="form-control" id="titulo" name="titulo" required>
                    </div>

                    <div class="mb-3">
                        <label for="contenido" class="form-label">Contenido de la Plantilla</label>
                        <textarea class="form-control" id="contenido" name="contenido"></textarea>
                    </div>

                    <div class="mb-3">
                        <label for="estado_plantilla" class="form-label">Estado</label>
                        <select class="form-select" id="estado_plantilla" name="estado" required>
                            <option value="Activo">Activo</option>
                            <option value="Inactivo">Inactivo</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Guardar Cambios</button>
                </div>
            </form>
        </div>
    </div>
</div>