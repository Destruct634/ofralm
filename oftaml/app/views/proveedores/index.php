<div class="container-fluid px-4">
    <h1 class="mt-4">Gestión de Proveedores</h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="index.php?page=dashboard">Dashboard</a></li>
        <li class="breadcrumb-item active">Proveedores</li>
    </ol>
    <div class="card mb-4">
        <div class="card-header">
            <i class="fas fa-truck me-1"></i>
            Listado de Proveedores
            <button class="btn btn-primary btn-sm float-end" id="btnNuevo"><i class="fas fa-plus me-1"></i>Nuevo Proveedor</button>
        </div>
        <div class="card-body">
            <table id="tablaProveedores" class="table table-striped table-bordered" style="width:100%">
                <thead><tr><th>ID</th><th>Nombre</th><th>Contacto</th><th>Teléfono</th><th>Email</th><th>Estado</th><th>Acciones</th></tr></thead>
            </table>
        </div>
    </div>
</div>

<div class="modal fade" id="modalProveedor" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header"><h5 class="modal-title" id="modalLabel"></h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <form id="formProveedor">
                <div class="modal-body">
                    <input type="hidden" id="id" name="id">
                    <div class="mb-3"><label for="nombre_proveedor" class="form-label">Nombre del Proveedor</label><input type="text" class="form-control" id="nombre_proveedor" name="nombre_proveedor" required></div>
                    <div class="mb-3"><label for="contacto" class="form-label">Persona de Contacto</label><input type="text" class="form-control" id="contacto" name="contacto"></div>
                    <div class="mb-3"><label for="telefono" class="form-label">Teléfono</label><input type="tel" class="form-control" id="telefono" name="telefono"></div>
                    <div class="mb-3"><label for="email" class="form-label">Email</label><input type="email" class="form-control" id="email" name="email"></div>
                    <div class="mb-3"><label for="estado" class="form-label">Estado</label><select class="form-select" id="estado" name="estado" required><option value="Activo">Activo</option><option value="Inactivo">Inactivo</option></select></div>
                </div>
                <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button><button type="submit" class="btn btn-primary">Guardar</button></div>
            </form>
        </div>
    </div>
</div>