<div class="container-fluid px-4">
    <h1 class="mt-4">Tipos de Cirugía</h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="index.php?page=dashboard">Dashboard</a></li>
        <li class="breadcrumb-item active">Tipos de Cirugía</li>
    </ol>
    <div class="card mb-4">
        <div class="card-header"><i class="fas fa-procedures me-1"></i>Listado de Tipos de Cirugía<button class="btn btn-primary btn-sm float-end" id="btnNuevo"><i class="fas fa-plus me-1"></i>Nuevo</button></div>
        <div class="card-body">
            <table id="tablaTiposCirugia" class="table table-striped table-bordered" style="width:100%">
                <thead><tr><th>ID</th><th>Nombre</th><th>Estado</th><th>Acciones</th></tr></thead>
            </table>
        </div>
    </div>
</div>
<div class="modal fade" id="modalTipoCirugia" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header"><h5 class="modal-title" id="modalLabel"></h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <form id="formTipoCirugia">
                <div class="modal-body">
                    <input type="hidden" id="id" name="id">
                    <div class="mb-3"><label for="nombre" class="form-label">Nombre del Tipo de Cirugía</label><input type="text" class="form-control" id="nombre" name="nombre" required></div>
                    <div class="mb-3"><label for="estado" class="form-label">Estado</label><select class="form-select" id="estado" name="estado" required><option value="Activo">Activo</option><option value="Inactivo">Inactivo</option></select></div>
                </div>
                <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button><button type="submit" class="btn btn-primary">Guardar</button></div>
            </form>
        </div>
    </div>
</div>