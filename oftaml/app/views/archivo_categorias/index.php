<?php
// Incluir el verificador de permisos
include_once '../app/core/Auth.php';
if (!Auth::check('archivo_categorias', 'ver')) {
    echo "<div class='container-fluid px-4'><div class='alert alert-danger mt-4'><h3>Acceso Denegado</h3><p>No tienes permiso para ver esta sección.</p></div></div>";
    include_once '../app/views/templates/footer.php';
    exit;
}
?>

<div class="container-fluid px-4">
    <h1 class="mt-4">Categorías de Archivos</h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="index.php">Dashboard</a></li>
        <li class="breadcrumb-item active">Categorías de Archivos</li>
    </ol>

    <div class="card mb-4">
        <div class="card-header">
            <i class="fas fa-tags me-1"></i>
            Listado de Categorías
            <?php if (Auth::check('archivo_categorias', 'crear')): ?>
                <button class="btn btn-primary btn-sm float-end" id="btnNuevaCategoria">
                    <i class="fas fa-plus me-1"></i> Nueva Categoría
                </button>
            <?php endif; ?>
        </div>
        <div class="card-body">
            <table id="tablaCategorias" class="table table-striped table-bordered" style="width:100%">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nombre de la Categoría</th>
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

<div class="modal fade" id="modalCategoria" tabindex="-1" aria-labelledby="modalCategoriaLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="formCategoria">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalCategoriaLabel">Nueva Categoría</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="categoria_id" name="id">
                    
                    <div class="mb-3">
                        <label for="nombre_categoria" class="form-label">Nombre de la Categoría</label>
                        <input type="text" class="form-control" id="nombre_categoria" name="nombre_categoria" required>
                    </div>

                    <div class="mb-3">
                        <label for="estado" class="form-label">Estado</label>
                        <select class="form-select" id="estado" name="estado" required>
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