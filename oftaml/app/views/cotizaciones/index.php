<div class="container-fluid px-4">
    <h1 class="mt-4">Cotizaciones</h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="index.php?page=dashboard">Dashboard</a></li>
        <li class="breadcrumb-item">Ventas</li>
        <li class="breadcrumb-item active">Cotizaciones</li>
    </ol>

    <div class="card mb-3">
        <div class="card-body">
            <div class="row">
                <div class="col-md-5">
                    <label for="filtro_daterange_cotizacion" class="form-label">Filtrar por Fecha de Emisión:</label>
                    <input type="text" id="filtro_daterange_cotizacion" class="form-control">
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <button id="btnFiltrarCotizaciones" class="btn btn-primary w-100">Filtrar</button>
                </div>
            </div>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-header">
            <i class="fas fa-file-alt me-1"></i>
            Listado de Cotizaciones
            <button class="btn btn-primary btn-sm float-end" id="btnNuevaCotizacion"><i class="fas fa-plus me-1"></i>Nueva Cotización</button>
        </div>
        <div class="card-body">
            <table id="tablaCotizaciones" class="table table-striped table-bordered" style="width:100%">
                <thead><tr><th>Correlativo</th><th>Paciente</th><th>Fecha Emisión</th><th>Vencimiento</th><th>Total</th><th>Estado</th><th>Acciones</th></tr></thead>
            </table>
        </div>
    </div>
</div>

<div class="modal fade" id="modalCotizacion" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header"><h5 class="modal-title" id="modalLabel"></h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <form id="formCotizacion">
                <div class="modal-body">
                    <input type="hidden" id="id_cotizacion" name="id">
                    <div class="row">
                        <div class="col-md-6"><label for="id_paciente_cotizacion" class="form-label">Paciente</label><select class="form-select" id="id_paciente_cotizacion" required></select></div>
                        <div class="col-md-3"><label for="fecha_emision_cotizacion" class="form-label">Fecha Emisión</label><input type="date" class="form-control" id="fecha_emision_cotizacion" required></div>
                        <div class="col-md-3"><label for="fecha_vencimiento_cotizacion" class="form-label">Fecha Vencimiento</label><input type="date" class="form-control" id="fecha_vencimiento_cotizacion" required></div>
                    </div>
                    <hr>
                    <h6>Detalle de la Cotización</h6>
                    <table class="table table-sm">
                        <thead><tr><th style="width: 40%;">Descripción</th><th>Cantidad</th><th>Precio</th><th>Descuento</th><th>ISV</th><th>Subtotal</th><th></th></tr></thead>
                        <tbody id="detalleCotizacionBody"></tbody>
                    </table>
                    <div class="btn-group">
                        <button type="button" class="btn btn-success btn-sm" id="btnAgregarProductoCotizacion"><i class="fas fa-box me-1"></i>Agregar Producto</button>
                        <button type="button" class="btn btn-info btn-sm" id="btnAgregarServicioCotizacion"><i class="fas fa-concierge-bell me-1"></i>Agregar Servicio</button>
                    </div>
                    <hr>
                    <div class="row">
                        <div class="col-md-8">
                            <label for="notas_cotizacion" class="form-label">Notas Adicionales</label>
                            <textarea class="form-control" id="notas_cotizacion" rows="3"></textarea>
                        </div>
                        <div class="col-md-4">
                            <ul class="list-group">
                                <li class="list-group-item d-flex justify-content-between align-items-center">Subtotal<span id="cotizacion_subtotal">L 0.00</span></li>
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    Descuento
                                    <div class="input-group" style="width: 120px;">
                                        <span class="input-group-text">L</span>
                                        <input type="number" step="0.01" class="form-control form-control-sm" id="cotizacion_descuento_total" value="0.00" min="0">
                                    </div>
                                </li>
                                <li class="list-group-item d-flex justify-content-between align-items-center">ISV<span id="cotizacion_isv">L 0.00</span></li>
                                <li class="list-group-item d-flex justify-content-between align-items-center"><strong>Total</strong><strong id="cotizacion_total">L 0.00</strong></li>
                            </ul>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Guardar Cotización</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="modalCRUD" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="formPaciente">
                <div class="modal-body">
                    <input type="hidden" id="id" name="id">
                    <div class="row">
                        <div class="col-md-6 mb-3"><label for="nombres" class="form-label">Nombres</label><input type="text" class="form-control" id="nombres" name="nombres" required></div>
                        <div class="col-md-6 mb-3"><label for="apellidos" class="form-label">Apellidos</label><input type="text" class="form-control" id="apellidos" name="apellidos" required></div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3"><label for="numero_identidad" class="form-label">Número de Identidad</label><input type="text" class="form-control" id="numero_identidad" name="numero_identidad" required></div>
                        <div class="col-md-6 mb-3"><label for="sexo" class="form-label">Sexo</label><select class="form-select" id="sexo" name="sexo" required><option value="">Seleccione...</option><option value="Masculino">Masculino</option><option value="Femenino">Femenino</option></select></div>
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
                    <div class="mb-3"><label for="observaciones" class="form-label">Observaciones</label><textarea class="form-control" id="observaciones" name="observaciones" rows="3"></textarea></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Guardar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="modalPdfViewer" tabindex="-1" aria-labelledby="modalPdfViewerLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-fullscreen-lg-down">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalPdfViewerLabel">Visor de Cotización</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-0" style="height: 80vh;">
                <iframe id="pdfFrame" src="" width="100%" height="100%" frameborder="0"></iframe>
            </div>
        </div>
    </div>
</div>