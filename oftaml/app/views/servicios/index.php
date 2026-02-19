<div class="container-fluid px-4">
    <h1 class="mt-4">Gestión de Servicios</h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="index.php?page=dashboard">Dashboard</a></li>
        <li class="breadcrumb-item active">Servicios</li>
    </ol>
    <div class="card mb-4">
        <div class="card-header">
            <i class="fas fa-concierge-bell me-1"></i>
            Listado de Servicios
            <button class="btn btn-primary btn-sm float-end" id="btnNuevo"><i class="fas fa-plus me-1"></i>Nuevo Servicio</button>
        </div>
        <div class="card-body">
            <table id="tablaServicios" class="table table-striped table-bordered" style="width:100%">
                <thead><tr><th>ID</th><th>Código</th><th>Servicio</th><th>Categoría</th><th>Precio Venta</th><th>Mostrar en Citas</th><th>Acciones</th></tr></thead>
            </table>
        </div>
    </div>
</div>

<!-- Modal -->
<div class="modal fade" id="modalServicio" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header"><h5 class="modal-title" id="modalLabel"></h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <form id="formServicio">
                <div class="modal-body">
                    <input type="hidden" id="id" name="id">
                    <div class="mb-3"><label for="codigo" class="form-label">Código</label><input type="text" class="form-control" id="codigo" name="codigo"></div>
                    <div class="mb-3"><label for="nombre_servicio" class="form-label">Nombre del Servicio</label><input type="text" class="form-control" id="nombre_servicio" name="nombre_servicio" required></div>
                    <div class="mb-3"><label for="descripcion" class="form-label">Descripción</label><textarea class="form-control" id="descripcion" name="descripcion" rows="2"></textarea></div>
                    <div class="row">
                        <div class="col-md-4"><label for="id_categoria_servicio" class="form-label">Categoría</label><select class="form-select" id="id_categoria_servicio" name="id_categoria_servicio" required></select></div>
                        <div class="col-md-4"><label for="id_isv" class="form-label">Tipo ISV</label><select class="form-select" id="id_isv" name="id_isv"></select></div>
                        <div class="col-md-4"><label for="precio_venta" class="form-label">Precio Venta</label><input type="number" step="0.01" class="form-control" id="precio_venta" name="precio_venta" required></div>
                    </div>
                    <div class="form-check form-switch mt-3">
                        <input class="form-check-input" type="checkbox" role="switch" id="mostrar_en_citas" name="mostrar_en_citas">
                        <label class="form-check-label" for="mostrar_en_citas">Mostrar este servicio en el formulario de Citas</label>
                    </div>
                </div>
                <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button><button type="submit" class="btn btn-primary">Guardar</button></div>
            </form>
        </div>
    </div>
</div>
