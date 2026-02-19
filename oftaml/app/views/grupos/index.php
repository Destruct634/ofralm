<div class="container-fluid px-4">
    <h1 class="mt-4">Gestión de Grupos y Permisos</h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="index.php?page=dashboard">Dashboard</a></li>
        <li class="breadcrumb-item active">Grupos y Permisos</li>
    </ol>
    <div class="card mb-4">
        <div class="card-header">
            <i class="fas fa-users-cog me-1"></i>
            Listado de Grupos
            <button class="btn btn-primary btn-sm float-end" id="btnNuevo"><i class="fas fa-plus me-1"></i>Nuevo Grupo</button>
        </div>
        <div class="card-body">
            <table id="tablaGrupos" class="table table-striped table-bordered" style="width:100%">
                <thead><tr><th>ID</th><th>Nombre del Grupo</th><th>Acciones</th></tr></thead>
            </table>
        </div>
    </div>
</div>

<div class="modal fade" id="modalGrupo" tabindex="-1"><div class="modal-dialog"><div class="modal-content"><div class="modal-header"><h5 class="modal-title" id="modalLabel"></h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div><form id="formGrupo"><div class="modal-body"><input type="hidden" id="id" name="id"><div class="mb-3"><label for="nombre_grupo" class="form-label">Nombre del Grupo</label><input type="text" class="form-control" id="nombre_grupo" name="nombre_grupo" required></div></div><div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button><button type="submit" class="btn btn-primary">Guardar</button></div></form></div></div></div>

<div class="modal fade" id="modalPermisos" tabindex="-1">
    <div class="modal-dialog modal-xl"> <div class="modal-content">
            <div class="modal-header bg-dark text-white">
                <h5 class="modal-title">Gestionar Permisos: <span id="nombreGrupoPermisos" class="fw-bold text-warning"></span></h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="formPermisos">
                <div class="modal-body">
                    <input type="hidden" id="id_grupo_permiso">
                    
                    <ul class="nav nav-tabs mb-3" id="permisosTabs" role="tablist">
                        </ul>
                    
                    <div class="tab-content" id="permisosTabsContent">
                        </div>

                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary px-4"><i class="fas fa-save me-2"></i>Guardar Permisos</button>
                </div>
            </form>
        </div>
    </div>
</div>