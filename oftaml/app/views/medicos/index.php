<div class="container-fluid px-4">
    <h1 class="mt-4">Gestión de Médicos</h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="index.php?page=dashboard">Dashboard</a></li>
        <li class="breadcrumb-item active">Médicos</li>
    </ol>
    <div class="card mb-4">
        <div class="card-header">
            <i class="fas fa-user-md me-1"></i>
            Listado de Médicos
            <button class="btn btn-primary btn-sm float-end" id="btnNuevoMedico"><i class="fas fa-plus me-1"></i>Nuevo Médico</button>
        </div>
        <div class="card-body">
            <table id="tablaMedicos" class="table table-striped table-bordered" style="width:100%">
                <thead>
                    <tr>
                        <th>ID</th><th>Nombres</th><th>Apellidos</th><th>Especialidad</th><th>Teléfono</th><th>Email</th><th>Estado</th><th>Acciones</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>
</div>
<div class="modal fade" id="modalMedico" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header"><h5 class="modal-title" id="modalMedicoLabel"></h5><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button></div>
            <form id="formMedico">
                <div class="modal-body">
                    <input type="hidden" id="id" name="id">
                    <div class="row">
                        <div class="col-md-6 mb-3"><label for="nombres" class="form-label">Nombres</label><input type="text" class="form-control" id="nombres" name="nombres" required></div>
                        <div class="col-md-6 mb-3"><label for="apellidos" class="form-label">Apellidos</label><input type="text" class="form-control" id="apellidos" name="apellidos" required></div>
                    </div>
                    <div class="mb-3"><label for="id_especialidad" class="form-label">Especialidad</label><select class="form-select" id="id_especialidad" name="id_especialidad" required></select></div>
                     <div class="mb-3"><label for="telefono" class="form-label">Teléfono</label><input type="tel" class="form-control" id="telefono" name="telefono" required></div>
                    <div class="mb-3"><label for="email" class="form-label">Email</label><input type="email" class="form-control" id="email" name="email" required></div>
                    <div class="mb-3"><label for="estado" class="form-label">Estado</label><select class="form-select" id="estado" name="estado" required><option value="Activo">Activo</option><option value="Inactivo">Inactivo</option></select></div>
                </div>
                <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button><button type="submit" class="btn btn-primary">Guardar</button></div>
            </form>
        </div>
    </div>
</div>