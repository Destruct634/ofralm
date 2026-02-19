<div class="container-fluid px-4">
    <h1 class="mt-4">Gestión de Usuarios</h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="index.php?page=dashboard">Dashboard</a></li>
        <li class="breadcrumb-item active">Usuarios</li>
    </ol>
    <div class="card mb-4">
        <div class="card-header">
            <i class="fas fa-user-shield me-1"></i>
            Listado de Usuarios
            <button class="btn btn-primary btn-sm float-end" id="btnNuevo"><i class="fas fa-plus me-1"></i>Nuevo Usuario</button>
        </div>
        <div class="card-body">
            <table id="tablaUsuarios" class="table table-striped table-bordered" style="width:100%">
                <thead><tr><th>ID</th><th>Nombre Completo</th><th>Usuario</th><th>Grupo</th><th>Estado</th><th>Acciones</th></tr></thead>
            </table>
        </div>
    </div>
</div>

<div class="modal fade" id="modalUsuario" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header"><h5 class="modal-title" id="modalLabel"></h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <form id="formUsuario">
                <div class="modal-body">
                    <input type="hidden" id="id" name="id">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3"><label for="nombre_completo" class="form-label">Nombre Completo</label><input type="text" class="form-control" id="nombre_completo" name="nombre_completo" required></div>
                            <div class="mb-3"><label for="usuario" class="form-label">Usuario (para login)</label><input type="text" class="form-control" id="usuario" name="usuario" required></div>
                            <div class="mb-3"><label for="id_grupo" class="form-label">Grupo de Permisos</label><select class="form-select" id="id_grupo" name="id_grupo" required></select></div>
                            <div class="mb-3"><label for="password" class="form-label">Contraseña</label><input type="password" class="form-control" id="password" name="password"><small class="form-text text-muted">Dejar en blanco para no cambiar</small></div>
                            <div class="mb-3"><label for="estado" class="form-label">Estado</label><select class="form-select" id="estado" name="estado" required><option value="Activo">Activo</option><option value="Inactivo">Inactivo</option></select></div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="form-check form-switch mb-3">
                                <input class="form-check-input" type="checkbox" role="switch" id="es_medico" name="es_medico">
                                <label class="form-check-label" for="es_medico">Es Médico (crear/actualizar perfil)</label>
                            </div>
                            <div id="campos_medico" style="display: none;">
                                <div class="mb-3"><label for="id_especialidad" class="form-label">Especialidad</label><select class="form-select" id="id_especialidad" name="id_especialidad"></select></div>
                                <div class="mb-3"><label for="telefono" class="form-label">Teléfono de Médico</label><input type="tel" class="form-control" id="telefono" name="telefono"></div>
                                <div class="mb-3"><label for="email_medico" class="form-label">Email de Médico</label><input type="email" class="form-control" id="email_medico" name="email_medico"></div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button><button type="submit" class="btn btn-primary">Guardar</button></div>
            </form>
        </div>
    </div>
</div>