<div class="container-fluid px-4">
    <h1 class="mt-4">Gestión de Categorías de Producto</h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="index.php?page=dashboard">Dashboard</a></li>
        <li class="breadcrumb-item active">Categorías de Producto</li>
    </ol>
    <div class="card mb-4">
        <div class="card-header">
            <i class="fas fa-tags me-1"></i>
            Listado de Categorías
            <button class="btn btn-primary btn-sm float-end" id="btnNuevo"><i class="fas fa-plus me-1"></i>Nueva Categoría</button>
        </div>
        <div class="card-body">
            <table id="tablaCategorias" class="table table-striped table-bordered" style="width:100%">
                <thead><tr><th>ID</th><th>Nombre de la Categoría</th><th>Acciones</th></tr></thead>
            </table>
        </div>
    </div>
</div>

<div class="modal fade" id="modalCategoria" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header"><h5 class="modal-title" id="modalLabel"></h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <form id="formCategoria">
                <div class="modal-body">
                    <input type="hidden" id="id" name="id">
                    <div class="mb-3"><label for="nombre_categoria" class="form-label">Nombre de la Categoría</label><input type="text" class="form-control" id="nombre_categoria" name="nombre_categoria" required></div>
                </div>
                <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button><button type="submit" class="btn btn-primary">Guardar</button></div>
            </form>
        </div>
    </div>
</div>