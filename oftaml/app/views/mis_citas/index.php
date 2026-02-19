<div class="container-fluid px-4">
    <div class="d-flex justify-content-between align-items-center mt-4 mb-4">
        <div>
            <h1 class="mb-0">Mi Agenda</h1>
            <div class="text-muted small">Gestión de pacientes y citas del día</div>
        </div>
        <div class="d-flex align-items-center">
            <div class="input-group">
                <span class="input-group-text bg-white"><i class="fas fa-calendar-alt text-primary"></i></span>
                <input type="text" class="form-control" id="filtro_daterange_medico" style="min-width: 220px;">
                <button class="btn btn-primary" id="btnFiltrarMedico" type="button">
                    <i class="fas fa-sync-alt"></i>
                </button>
            </div>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-xl-4 col-md-6">
            <div class="card border-start border-4 border-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Citas para Hoy</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800" id="kpi_total_hoy">0</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-calendar-day fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-4 col-md-6">
            <div class="card border-start border-4 border-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                Pendientes de Atender</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800" id="kpi_pendientes">0</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-user-clock fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-4 col-md-6">
            <div class="card border-start border-4 border-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                Pacientes Atendidos</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800" id="kpi_atendidos">0</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-check-circle fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card mb-4 shadow-sm">
        <div class="card-header bg-white">
            <i class="fas fa-list-ul me-1 text-primary"></i>
            Listado de Pacientes
        </div>
        <div class="card-body">
            <table id="tablaCitasMedico" class="table table-hover align-middle" style="width:100%">
                <thead class="table-light">
                    <tr>
                        <th>Hora</th>
                        <th>Paciente</th>
                        <th>Motivo / Servicio</th>
                        <th>Estado</th>
                        <th class="text-center">Acción</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>
</div>

<style>
    .text-gray-300 { color: #dddfeb!important; }
    .border-start.border-4 { border-left-width: 4px !important; }
    .fs-5 { font-size: 1.25rem !important; }
</style>

<div class="modal fade" id="modalNuevaEntrada" tabindex="-1" aria-hidden="true" data-bs-focus="false">
    <div class="modal-dialog modal-xl"> 
        <div class="modal-content">
            <div class="modal-header"><h5 class="modal-title" id="modalEntradaLabel"></h5><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button></div>
            <form id="formNuevaEntradaHistorial">
                <div class="modal-body">
                    <input type="hidden" id="historial_id"> 
                    <input type="hidden" id="historial_id_cita">
                    <input type="hidden" id="historial_id_paciente">
                    <input type="hidden" id="historial_id_medico">

                    <ul class="nav nav-tabs" id="consultaTab" role="tablist">
                        <li class="nav-item"><button class="nav-link active" id="tab-btn-consulta" data-bs-toggle="tab" data-bs-target="#tab-consulta" type="button"><i class="fas fa-comments me-1"></i> Consulta</button></li>
                        <li class="nav-item"><button class="nav-link" id="tab-btn-av" data-bs-toggle="tab" data-bs-target="#tab-av" type="button"><i class="fas fa-eye me-1"></i> AV y PIO</button></li>
                        <li class="nav-item"><button class="nav-link" id="tab-btn-refraccion" data-bs-toggle="tab" data-bs-target="#tab-refraccion" type="button"><i class="fas fa-glasses me-1"></i> Refracción</button></li>
                        <li class="nav-item"><button class="nav-link" id="tab-btn-examen" data-bs-toggle="tab" data-bs-target="#tab-examen" type="button"><i class="fas fa-microscope me-1"></i> Examen</button></li>
                        <li class="nav-item"><button class="nav-link" id="tab-btn-plan" data-bs-toggle="tab" data-bs-target="#tab-plan" type="button"><i class="fas fa-clipboard-list me-1"></i> Diagnóstico</button></li>
                        <li class="nav-item"><button class="nav-link" id="tab-btn-items" data-bs-toggle="tab" data-bs-target="#tab-items" type="button"><i class="fas fa-box me-1"></i> Servicios</button></li>
                    </ul>

                    <div class="tab-content" id="consultaTabContent">
                        <div class="tab-pane fade show active" id="tab-consulta">
                            <div class="py-3">
                                 <div class="mb-3">
                                    <label for="selectPlantilla" class="form-label">Cargar Plantilla:</label>
                                    <select class="form-select" id="selectPlantilla"><option value="">-- Seleccionar --</option></select>
                                </div>
                                <hr>
                                 <div class="mb-3">
                                    <label for="hea" class="form-label">Historia de la Enfermedad Actual (HEA)</label>
                                    <textarea class="form-control" id="hea" name="hea" rows="8"></textarea>
                                </div>
                             </div>
                        </div>
                        <div class="tab-pane fade" id="tab-av">
                            <div class="py-3">
                                <h5 class="mb-3">Agudeza Visual y PIO</h5>
                                <div class="row gx-2 mb-2 align-items-center">
                                    <div class="col-3 text-end fw-bold">OD</div>
                                    <div class="col-3"><input type="text" class="form-control" id="av_sc_od" name="av_sc_od" placeholder="AV sc"></div>
                                    <div class="col-3"><input type="text" class="form-control" id="av_cc_od" name="av_cc_od" placeholder="AV cc"></div>
                                    <div class="col-3"><input type="text" class="form-control" id="pio_od" name="pio_od" placeholder="PIO"></div>
                                </div>
                                <div class="row gx-2 mb-3 align-items-center">
                                    <div class="col-3 text-end fw-bold">OS</div>
                                    <div class="col-3"><input type="text" class="form-control" id="av_sc_os" name="av_sc_os" placeholder="AV sc"></div>
                                    <div class="col-3"><input type="text" class="form-control" id="av_cc_os" name="av_cc_os" placeholder="AV cc"></div>
                                    <div class="col-3"><input type="text" class="form-control" id="pio_os" name="pio_os" placeholder="PIO"></div>
                                </div>
                            </div>
                        </div>
                        <div class="tab-pane fade" id="tab-refraccion">
                            <div class="py-3">
                                <div class="row mb-2"><div class="col-md-4"><select id="tipo_refraccion" name="tipo_refraccion" class="form-select"><option value="Refracción Actual">Refracción Actual</option><option value="Lentes Anteriores">Lentes Anteriores</option></select></div></div>
                                <div class="row gx-2 mb-2 align-items-center">
                                    <div class="col-2 text-end fw-bold">OD</div>
                                    <div class="col-2"><input type="number" step="0.25" class="form-control" id="od_esfera" placeholder="Esfera"></div>
                                    <div class="col-2"><input type="number" step="0.25" class="form-control" id="od_cilindro" placeholder="Cilindro"></div>
                                    <div class="col-2"><input type="number" step="1" class="form-control" id="od_eje" placeholder="Eje"></div>
                                    <div class="col-2"><input type="text" class="form-control" id="od_av" placeholder="AV"></div>
                                </div>
                                <div class="row gx-2 mb-2 align-items-center">
                                    <div class="col-2 text-end fw-bold">OS</div>
                                    <div class="col-2"><input type="number" step="0.25" class="form-control" id="os_esfera" placeholder="Esfera"></div>
                                    <div class="col-2"><input type="number" step="0.25" class="form-control" id="os_cilindro" placeholder="Cilindro"></div>
                                    <div class="col-2"><input type="number" step="1" class="form-control" id="os_eje" placeholder="Eje"></div>
                                    <div class="col-2"><input type="text" class="form-control" id="os_av" placeholder="AV"></div>
                                </div>
                                <div class="row gx-2"><div class="col-md-3"><input type="number" step="0.25" class="form-control" id="add" placeholder="ADD"></div><div class="col-md-9"><input type="text" class="form-control" id="refraccion_obs" placeholder="Observaciones"></div></div>
                            </div>
                        </div>
                        <div class="tab-pane fade" id="tab-examen">
                            <div class="py-3">
                                <div class="mb-3"><label class="form-label">Biomicroscopía</label><textarea class="form-control summernote-lite" id="biomicroscopia" name="biomicroscopia"></textarea></div>
                                <div class="mb-3"><label class="form-label">Fondo de Ojo</label><textarea class="form-control summernote-lite" id="fondo_ojo" name="fondo_ojo"></textarea></div>
                                <div class="mb-3"><label class="form-label">Observaciones</label><textarea class="form-control" id="observaciones" name="observaciones"></textarea></div>
                            </div>
                        </div>
                        <div class="tab-pane fade" id="tab-plan">
                            <div class="py-3">
                                <div class="mb-3"><label class="form-label">IDx</label><select class="form-select" id="selectDiagnosticos" multiple style="width: 100%;"></select></div>
                                <div class="mb-3"><label class="form-label">Plan / Tratamiento</label><textarea class="form-control summernote-full" id="tratamiento" name="tratamiento"></textarea></div>
                            </div>
                        </div>
                        <div class="tab-pane fade" id="tab-items">
                            <div class="py-3">
                                <div class="row gx-3 mb-3">
                                    <div class="col-md-6"><select class="form-select" id="selectItem" style="width: 100%;"><option value="">Buscar...</option></select></div>
                                    <div class="col-md-2"><input type="number" class="form-control" id="itemCantidad" value="1" min="1" placeholder="Cant"></div>
                                    <div class="col-md-2"><input type="number" class="form-control" id="itemDescuento" value="0.00" placeholder="Desc"></div>
                                    <div class="col-md-2"><button type="button" class="btn btn-success w-100" id="btnAnadirItemHistorial"><i class="fas fa-plus"></i></button></div>
                                </div>
                                <table class="table table-bordered table-sm"><thead class="table-light"><tr><th>Descripción</th><th>Cant.</th><th>Precio</th><th>Total</th><th></th></tr></thead><tbody id="tbodyItemsHistorial"></tbody></table>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button><button type="submit" class="btn btn-primary">Guardar Entrada</button></div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="modalArchivos" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header"><h5 class="modal-title" id="modalArchivosLabel">Archivos</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body">
                <ul class="list-group mb-4" id="listaArchivosAdjuntos"></ul>
                <form id="formSubirArchivos" enctype="multipart/form-data">
                    <input type="hidden" name="id_historial" id="upload_id_historial">
                    <input type="hidden" name="id_paciente" id="upload_id_paciente">
                    <div class="mb-3"><label class="form-label">Categoría</label><select class="form-select" name="id_categoria" id="selectCategoriaArchivo"><option value="">-- Sin Categoría --</option></select></div>
                    <div class="mb-3"><input class="form-control" type="file" name="archivos[]" id="archivos" multiple></div>
                    <button type="submit" class="btn btn-primary">Subir</button>
                </form>
            </div>
        </div>
    </div>
</div>