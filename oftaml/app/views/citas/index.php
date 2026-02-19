<div class="container-fluid px-4">
    <h1 class="mt-4">Gestión de Citas</h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="index.php?page=dashboard">Dashboard</a></li>
        <li class="breadcrumb-item active">Citas</li>
    </ol>

    <ul class="nav nav-tabs mb-3" id="citasTabs" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active" id="tab-consultas" data-bs-toggle="tab" data-bs-target="#content-citas" type="button" role="tab" data-categoria="1">
                <i class="fas fa-stethoscope me-2"></i>Consultas
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="tab-cirugias" data-bs-toggle="tab" data-bs-target="#content-citas" type="button" role="tab" data-categoria="2">
                <i class="fas fa-procedures me-2"></i>Cirugías
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="tab-todas" data-bs-toggle="tab" data-bs-target="#content-citas" type="button" role="tab" data-categoria="">
                <i class="fas fa-list me-2"></i>Todas
            </button>
        </li>
    </ul>
    <div class="tab-content" id="citasTabContent">
        <div class="tab-pane fade show active" id="content-citas" role="tabpanel">
            
            <div class="card mb-3">
                <div class="card-body">
                    <div class="row g-3 align-items-end">
                        <div class="col-md-5">
                            <label for="filtro_daterange" class="form-label">Rango de Fechas:</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-calendar-alt"></i></span>
                                <input type="text" class="form-control" id="filtro_daterange">
                            </div>
                        </div>
                        <div class="col-md-5">
                            <label for="filtro_medico" class="form-label">Médico:</label>
                            <select class="form-select" id="filtro_medico">
                                <option value="">Todos los médicos</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <button class="btn btn-primary w-100" id="btnFiltrar" type="button">
                                <i class="fas fa-filter me-1"></i> Filtrar
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card mb-4">
                <div class="card-header"><i class="fas fa-calendar-alt me-1"></i>Listado de Citas<button class="btn btn-primary btn-sm float-end" id="btnNuevaCita"><i class="fas fa-plus me-1"></i>Nueva Cita</button></div>
                <div class="card-body">
                    <table id="tablaCitas" class="table table-striped table-bordered" style="width:100%">
                        <thead>
                            <tr>
                                <th>ID</th><th>Paciente</th><th>Especialidad</th><th>Médico</th><th>Servicio</th><th>Fecha y Hora</th><th>Estado</th><th>Notificado</th><th>Acciones</th>
                            </tr>
                        </thead>
                    </table>
                </div>
            </div>

        </div>
    </div>
</div>

<div class="modal fade" id="modalCita" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header"><h5 class="modal-title" id="modalCitaLabel"></h5><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button></div>
            <form id="formCita">
                <div class="modal-body">
                    <input type="hidden" id="id" name="id">
                    <div class="mb-3"><label for="id_paciente" class="form-label">Paciente</label><select class="form-select" id="id_paciente" name="id_paciente" required style="width: 100%;"></select></div>
                    <div class="mb-3"><label for="id_especialidad" class="form-label">Especialidad del Médico</label><select class="form-select" id="id_especialidad" name="id_especialidad" required></select></div>
                    <div class="mb-3"><label for="id_medico" class="form-label">Médico</label><select class="form-select" id="id_medico" name="id_medico" required disabled></select></div>
                    <div class="mb-3"><label for="id_servicio" class="form-label">Servicio (Motivo de la Cita)</label><select class="form-select" id="id_servicio" name="id_servicio" required></select></div>
                    <div class="row">
                        <div class="col-md-6 mb-3"><label for="fecha_cita" class="form-label">Fecha</label><input type="date" class="form-control" id="fecha_cita" name="fecha_cita" required></div>
                        <div class="col-md-6 mb-3"><label for="hora_cita" class="form-label">Hora</label><input type="time" class="form-control" id="hora_cita" name="hora_cita" required></div>
                    </div>
                    <div class="mb-3"><label for="estado" class="form-label">Estado</label><select class="form-select" id="estado" name="estado" required><option value="Programada">Programada</option><option value="Completada">Completada</option><option value="Cancelada">Cancelada</option><option value="No se presentó">No se presentó</option></select></div>
                </div>
                <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button><button type="submit" class="btn btn-primary">Guardar</button></div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="modalNuevoPacienteRapido" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header"><h5 class="modal-title">Crear Nuevo Paciente</h5><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button></div>
            <form id="formNuevoPacienteRapido">
                <div class="modal-body">
                    <p>Complete los campos esenciales. Podrá editar el resto más tarde.</p>
                    <div class="mb-3"><label for="rapido_nombres" class="form-label">Nombres</label><input type="text" class="form-control" id="rapido_nombres" name="nombres" required></div>
                    <div class="mb-3"><label for="rapido_apellidos" class="form-label">Apellidos</label><input type="text" class="form-control" id="rapido_apellidos" name="apellidos" required></div>
                    <div class="mb-3"><label for="rapido_numero_identidad" class="form-label">Número de Identidad</label><input type="text" class="form-control" id="rapido_numero_identidad" name="numero_identidad" required></div>
                    <input type="hidden" name="sexo" value="No especificado"><input type="hidden" name="direccion" value="N/A"><input type="hidden" name="telefono" value="N/A"><input type="hidden" name="email" value=""><input type="hidden" name="fecha_nacimiento" value="1900-01-01"><input type="hidden" name="tiene_seguro" value="No">
                </div>
                <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button><button type="submit" class="btn btn-primary">Guardar Paciente</button></div>
            </form>
        </div>
    </div>
</div>