<div class="container-fluid px-4">
    <h1 class="mt-4">Notas de Crédito</h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="index.php?page=dashboard">Dashboard</a></li>
        <li class="breadcrumb-item">Ventas</li>
        <li class="breadcrumb-item active">Notas de Crédito</li>
    </ol>

    <div class="card mb-4">
        <div class="card-header">
            <i class="fas fa-undo-alt me-1"></i>
            Listado de Notas de Crédito
            <button class="btn btn-primary btn-sm float-end" id="btnNuevaNotaCredito"><i class="fas fa-plus me-1"></i>Nueva Nota de Crédito</button>
        </div>
        <div class="card-body">
            <table id="tablaNotasCredito" class="table table-striped table-bordered" style="width:100%">
                <thead>
                    <tr>
                        <th>Correlativo</th>
                        <th>Factura Asociada</th>
                        <th>Paciente</th>
                        <th>Fecha</th>
                        <th>Total</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>
</div>

<div class="modal fade" id="modalNotaCredito" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Nueva Nota de Crédito</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="formNotaCredito">
                <div class="modal-body">
                    <input type="hidden" id="id_paciente_nc">
                    <div class="mb-3">
                        <label for="id_factura_asociada" class="form-label"><b>Paso 1:</b> Buscar y seleccionar la factura a la que se aplicará la nota de crédito</label>
                        <select class="form-select" id="id_factura_asociada" required></select>
                    </div>
                    <div id="detalleFacturaContainer" class="d-none">
                        <hr>
                        <div class="row mb-3">
                            <div class="col-md-6"><strong>Paciente:</strong> <span id="info_paciente"></span></div>
                            <div class="col-md-6"><strong>Fecha Factura:</strong> <span id="info_fecha"></span></div>
                        </div>
                        <div class="mb-3">
                             <label class="form-label"><b>Paso 2:</b> Ingresa la cantidad a devolver de cada item</label>
                             <div class="table-responsive">
                                <table class="table table-sm table-bordered">
                                    <thead>
                                        <tr>
                                            <th>Descripción</th>
                                            <th class="text-center">Cant. Facturada</th>
                                            <th style="width: 150px;">Cant. a Devolver</th>
                                            <th class="text-end">Precio Unit.</th>
                                            <th class="text-end">Subtotal NC</th>
                                        </tr>
                                    </thead>
                                    <tbody id="itemsFacturaBody"></tbody>
                                </table>
                             </div>
                        </div>
                        <div class="row">
                            <div class="col-md-8">
                                <label for="motivo_nc" class="form-label"><b>Paso 3:</b> Motivo de la Nota de Crédito</label>
                                <textarea id="motivo_nc" class="form-control" rows="3" required></textarea>
                            </div>
                            <div class="col-md-4">
                                <h6>Resumen de la Nota de Crédito</h6>
                                <ul class="list-group">
                                    <li class="list-group-item d-flex justify-content-between">Subtotal:<span id="nc_subtotal">L 0.00</span></li>
                                    <li class="list-group-item d-flex justify-content-between">ISV:<span id="nc_isv">L 0.00</span></li>
                                    <li class="list-group-item d-flex justify-content-between"><strong>Total:</strong><strong id="nc_total">L 0.00</strong></li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary" id="btnGuardarNC">Guardar Nota de Crédito</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="modalVerNotaCredito" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Detalles de la Nota de Crédito <span id="ver_nc_correlativo" class="badge bg-primary ms-2"></span></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row mb-3">
                    <div class="col-md-6"><p><strong>Paciente:</strong> <span id="ver_nc_paciente"></span></p></div>
                    <div class="col-md-6"><p><strong>Fecha Emisión:</strong> <span id="ver_nc_fecha"></span></p></div>
                    <div class="col-md-6"><p><strong>Factura Afectada:</strong> <span id="ver_nc_factura_asociada"></span></p></div>
                    <div class="col-md-6"><p><strong>Estado:</strong> <span id="ver_nc_estado"></span></p></div>
                </div>
                <h6>Items Devueltos/Ajustados</h6>
                <table class="table table-bordered">
                    <thead><tr><th>Descripción</th><th>Cantidad</th><th>Precio Unitario</th><th>Subtotal</th></tr></thead>
                    <tbody id="ver_nc_detalle_body"></tbody>
                </table>
                <div class="row mt-3">
                    <div class="col-md-8">
                        <strong>Motivo:</strong>
                        <p id="ver_nc_motivo" class="text-muted"></p>
                    </div>
                    <div class="col-md-4">
                         <ul class="list-group">
                            <li class="list-group-item d-flex justify-content-between">Subtotal:<span id="ver_nc_subtotal">L 0.00</span></li>
                            <li class="list-group-item d-flex justify-content-between">ISV:<span id="ver_nc_isv">L 0.00</span></li>
                            <li class="list-group-item d-flex justify-content-between"><strong>Total Crédito:</strong><strong id="ver_nc_total">L 0.00</strong></li>
                        </ul>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modalPdfViewer" tabindex="-1">
    <div class="modal-dialog modal-xl modal-fullscreen-lg-down">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Visor de Documento</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-0" style="height: 80vh;">
                <iframe id="pdfFrame" src="" width="100%" height="100%" frameborder="0"></iframe>
            </div>
        </div>
    </div>
</div>