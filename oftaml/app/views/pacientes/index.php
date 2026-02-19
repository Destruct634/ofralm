<div class="container-fluid px-4">
    <h1 class="mt-4">Gestión de Pacientes</h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="index.php?page=dashboard">Dashboard</a></li>
        <li class="breadcrumb-item active">Pacientes</li>
    </ol>
    <div class="card mb-4">
        <div class="card-header">
            <i class="fas fa-table me-1"></i>
            Listado de Pacientes
            <button class="btn btn-primary btn-sm float-end" id="btnNuevo"><i class="fas fa-plus me-1"></i>Nuevo Paciente</button>
        </div>
        <div class="card-body">
            <table id="tablaPacientes" class="table table-striped table-bordered" style="width:100%">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nombres</th>
                        <th>Apellidos</th>
                        <th>N° Identidad</th>
                        <th>Edad</th>
                        <th>Teléfono</th>
                        <th>Fecha de Creación</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>
</div>

<div class="modal fade" id="modalCRUD" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel"></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="formPaciente">
                
                <div class="modal-body">
                    <input type="hidden" id="id" name="id">
                    
                    <ul class="nav nav-tabs" id="pacienteTab" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="tab-btn-personales" data-bs-toggle="tab" data-bs-target="#tab-personales" type="button" role="tab" aria-controls="tab-personales" aria-selected="true">
                                <i class="fas fa-user me-1"></i> Datos Personales
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="tab-btn-antecedentes" data-bs-toggle="tab" data-bs-target="#tab-antecedentes" type="button" role="tab" aria-controls="tab-antecedentes" aria-selected="false">
                                <i class="fas fa-heartbeat me-1"></i> Antecedentes Médicos
                            </button>
                        </li>
                    </ul>

                    <div class="tab-content" id="pacienteTabContent">
                        
                        <div class="tab-pane fade show active" id="tab-personales" role="tabpanel" aria-labelledby="tab-btn-personales">
                            <div class="py-3">
                                <div class="row">
                                    <div class="col-md-6 mb-3"><label for="nombres" class="form-label">Nombres</label><input type="text" class="form-control" id="nombres" name="nombres" required></div>
                                    <div class="col-md-6 mb-3"><label for="apellidos" class="form-label">Apellidos</label><input type="text" class="form-control" id="apellidos" name="apellidos" required></div>
                                </div>
                                <div class="row">
                                    <div class="col-md-6 mb-3"><label for="numero_identidad" class="form-label">Número de Identidad</label><input type="text" class="form-control" id="numero_identidad" name="numero_identidad" required></div>
                                    <div class="col-md-6 mb-3"><label for="sexo" class="form-label">Sexo</label><select class="form-select" id="sexo" name="sexo" required><option value="Masculino">Masculino</option><option value="Femenino">Femenino</option></select></div>
                                </div>
                                <div class="row">
                                    <div class="col-md-6 mb-3"><label for="telefono" class="form-label">Teléfono</label><input type="tel" class="form-control" id="telefono" name="telefono" required></div>
                                    <div class="col-md-6 mb-3"><label for="email" class="form-label">Email</label><input type="email" class="form-control" id="email" name="email"></div>
                                </div>
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="fecha_nacimiento" class="form-label">Fecha de Nacimiento</label>
                                        <div class="input-group">
                                            <input type="date" class="form-control" id="fecha_nacimiento" name="fecha_nacimiento" required>
                                            <span class="input-group-text" id="edad_paciente" style="min-width: 70px;"></span>
                                        </div>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="tiene_seguro" class="form-label">¿Tiene Seguro?</label>
                                        <select class="form-select" id="tiene_seguro" name="tiene_seguro" required>
                                            <option value="No">No</option>
                                            <option value="Sí">Sí</option>
                                        </select>
                                    </div>
                                </div>
                                <div id="campos_seguro" style="display: none;">
                                    <div class="row">
                                        <div class="col-md-6 mb-3"><label for="id_aseguradora" class="form-label">Aseguradora</label><select class="form-select" id="id_aseguradora" name="id_aseguradora"></select></div>
                                        <div class="col-md-6 mb-3"><label for="numero_poliza" class="form-label">Número de Póliza</label><input type="text" class="form-control" id="numero_poliza" name="numero_poliza"></div>
                                    </div>
                                </div>
                                <div class="mb-3"><label for="direccion" class="form-label">Dirección</label><textarea class="form-control" id="direccion" name="direccion" rows="2" required></textarea></div>
                                <div class="mb-3"><label for="observaciones" class="form-label">Observaciones (Generales del Paciente)</label><textarea class="form-control" id="observaciones" name="observaciones" rows="3"></textarea></div>
                            </div>
                        </div>

                        <div class="tab-pane fade" id="tab-antecedentes" role="tabpanel" aria-labelledby="tab-btn-antecedentes">
                            <div class="py-3">
                                <h5 class="mb-3">Antecedentes Médicos</h5>
                                
                                <div class="row mb-3">
                                    <div class="col-md-3 col-6">
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" role="switch" id="antecedente_dm" name="antecedente_dm" value="1">
                                            <label class="form-check-label" for="antecedente_dm">Diabetes (DM)</label>
                                        </div>
                                    </div>
                                    <div class="col-md-3 col-6">
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" role="switch" id="antecedente_hta" name="antecedente_hta" value="1">
                                            <label class="form-check-label" for="antecedente_hta">Hipertensión (HTA)</label>
                                        </div>
                                    </div>
                                    <div class="col-md-3 col-6">
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" role="switch" id="antecedente_glaucoma" name="antecedente_glaucoma" value="1">
                                            <label class="form-check-label" for="antecedente_glaucoma">Glaucoma</label>
                                        </div>
                                    </div>
                                    <div class="col-md-3 col-6">
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" role="switch" id="antecedente_asma" name="antecedente_asma" value="1">
                                            <label class="form-check-label" for="antecedente_asma">Asma</label>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="alergias" class="form-label">Alergias</label>
                                        <textarea class="form-control" id="alergias" name="alergias" rows="2" placeholder="Ej. Penicilina, Sulfa..."></textarea>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="antecedente_cirugias" class="form-label">Cirugías Previas</label>
                                        <textarea class="form-control" id="antecedente_cirugias" name="antecedente_cirugias" rows="2" placeholder="Ej. Apendicectomía 2010..."></textarea>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="antecedente_trauma" class="form-label">Traumas Oculares</label>
                                        <textarea class="form-control" id="antecedente_trauma" name="antecedente_trauma" rows="2" placeholder="Ej. Golpe OD 2015..."></textarea>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="antecedente_otros" class="form-label">Otras Enfermedades Relevantes</label>
                                        <textarea class="form-control" id="antecedente_otros" name="antecedente_otros" rows="2" placeholder="Ej. Hipotiroidismo..."></textarea>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Guardar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="modalEstadoCuenta" tabindex="-1" aria-labelledby="modalEstadoCuentaLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalEstadoCuentaLabel">Generar Estado de Cuenta</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="formEstadoCuenta">
                <div class="modal-body">
                    <input type="hidden" id="id_paciente_estado_cuenta">
                    <p>Seleccione el rango de fechas para el reporte del paciente <strong id="nombre_paciente_estado_cuenta"></strong>.</p>
                    <div class="mb-3">
                        <label for="daterange_estado_cuenta" class="form-label">Periodo del Reporte</label>
                        <input type="text" id="daterange_estado_cuenta" class="form-control">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary"><i class="fas fa-file-pdf me-1"></i>Generar Reporte</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="modalPdfViewer" tabindex="-1" aria-labelledby="modalPdfViewerLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-fullscreen-lg-down">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalPdfViewerLabel">Visor de Reporte</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-0" style="height: 80vh;">
                <iframe id="pdfFrame" src="" width="100%" height="100%" frameborder="0"></iframe>
            </div>
        </div>
    </div>
</div>