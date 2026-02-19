<div class="container-fluid px-4">
    <h1 class="mt-4">Gestión de Tipos de ISV</h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="index.php?page=dashboard">Dashboard</a></li>
        <li class="breadcrumb-item active">Tipos de ISV</li>
    </ol>
    <div class="card mb-4">
        <div class="card-header">
            <i class="fas fa-percentage me-1"></i>
            Listado de Tipos de ISV
            <button class="btn btn-primary btn-sm float-end" id="btnNuevo"><i class="fas fa-plus me-1"></i>Nuevo Tipo</button>
        </div>
        <div class="card-body">
            <table id="tablaTiposIsv" class="table table-striped table-bordered" style="width:100%">
                <thead><tr><th>ID</th><th>Nombre</th><th>Porcentaje (%)</th><th>Acciones</th></tr></thead>
            </table>
        </div>
    </div>
</div>

<div class="modal fade" id="modalTipoIsv" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header"><h5 class="modal-title" id="modalLabel"></h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <form id="formTipoIsv">
                <div class="modal-body">
                    <input type="hidden" id="id" name="id">
                    <div class="mb-3"><label for="nombre_isv" class="form-label">Nombre del ISV (Ej: ISV 15%)</label><input type="text" class="form-control" id="nombre_isv" name="nombre_isv" required></div>
                    <div class="mb-3"><label for="porcentaje" class="form-label">Porcentaje (Ej: 15.00)</label><input type="number" step="0.01" class="form-control" id="porcentaje" name="porcentaje" required></div>
                </div>
                <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button><button type="submit" class="btn btn-primary">Guardar</button></div>
            </form>
        </div>
    </div>
</div>