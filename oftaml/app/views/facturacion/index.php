<div class="container-fluid px-4">
    <h1 class="mt-4">Facturación</h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="index.php?page=dashboard">Dashboard</a></li>
        <li class="breadcrumb-item active">Facturación</li>
    </ol>

    <div class="card mb-3">
        <div class="card-body">
            <div class="row">
                <div class="col-md-4">
                    <label for="filtro_daterange_factura" class="form-label">Filtrar por Fecha:</label>
                    <input type="text" id="filtro_daterange_factura" class="form-control">
                </div>
                <div class="col-md-3">
                    <label for="filtro_estado_factura" class="form-label">Filtrar por Estado:</label>
                    <select id="filtro_estado_factura" class="form-select">
                        <option value="" selected>Todos</option>
                        <option value="Pagada">Pagada</option>
                        <option value="Pago Parcial">Pago Parcial</option>
                        <option value="Anulada">Anulada</option>
                        <option value="Borrador">Borrador</option>
                    </select>
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <button id="btnFiltrarFacturas" class="btn btn-primary w-100">Filtrar</button>
                </div>
            </div>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-header">
            <i class="fas fa-file-invoice-dollar me-1"></i>
            Listado de Facturas
            <button class="btn btn-primary btn-sm float-end" id="btnNuevaFactura"><i class="fas fa-plus me-1"></i>Nueva Factura</button>
        </div>
        <div class="card-body">
            <table id="tablaFacturas" class="table table-striped table-bordered" style="width:100%">
                <thead><tr><th>Correlativo</th><th>Paciente</th><th>Fecha</th><th>Total</th><th>Estado</th><th>Acciones</th></tr></thead>
            </table>
        </div>
    </div>
</div>

<div class="modal fade" id="modalFactura" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header"><h5 class="modal-title" id="modalLabel"></h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <form id="formFactura">
                <div class="modal-body">
                    <input type="hidden" id="id_factura" name="id">
                    <input type="hidden" id="id_cita_factura">
                    
                    <div class="row mb-3">
                        <div class="col-md-8">
                            <label for="id_paciente_factura" class="form-label">Paciente</label>
                            <select class="form-select" id="id_paciente_factura" name="id_paciente" required></select>
                        </div>
                        <div class="col-md-4">
                            <label for="fecha_emision" class="form-label">Fecha</label>
                            <input type="date" class="form-control" id="fecha_emision" name="fecha_emision" required>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="id_medico_factura" class="form-label">Médico Tratante (Opcional)</label>
                            <select class="form-select" id="id_medico_factura" name="id_medico"></select>
                        </div>
                        <div class="col-md-6">
                            <label for="id_tecnico_factura" class="form-label">Técnico / Optometrista (Opcional)</label>
                            <select class="form-select" id="id_tecnico_factura" name="id_tecnico"></select>
                        </div>
                    </div>

                    <hr>
                    <h6>Detalle de la Factura</h6>
                    <table class="table table-sm">
                        <thead><tr><th style="width: 40%;">Descripción</th><th>Cantidad</th><th>Precio</th><th>Descuento</th><th>ISV</th><th>Subtotal</th><th></th></tr></thead>
                        <tbody id="detalleFacturaBody"></tbody>
                    </table>
                    <div class="btn-group">
                        <button type="button" class="btn btn-success btn-sm" id="btnAgregarProductoFactura"><i class="fas fa-box me-1"></i>Agregar Producto</button>
                        <button type="button" class="btn btn-info btn-sm" id="btnAgregarServicioFactura"><i class="fas fa-concierge-bell me-1"></i>Agregar Servicio</button>
                    </div>
                    <hr>
                    <div class="row justify-content-end">
                        <div class="col-md-4">
                            <ul class="list-group">
                                <li class="list-group-item d-flex justify-content-between align-items-center">Subtotal<span id="factura_subtotal">L 0.00</span></li>
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    Descuento
                                    <div class="input-group" style="width: 120px;">
                                        <span class="input-group-text">L</span>
                                        <input type="number" step="0.01" class="form-control form-control-sm" id="factura_descuento_total" value="0.00" min="0">
                                    </div>
                                </li>
                                <li class="list-group-item d-flex justify-content-between align-items-center">ISV<span id="factura_isv">L 0.00</span></li>
                                <li class="list-group-item d-flex justify-content-between align-items-center"><strong>Total</strong><strong id="factura_total">L 0.00</strong></li>
                            </ul>
                        </div>
                    </div>
                </div>
                <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button><button type="submit" class="btn btn-primary">Guardar Factura</button></div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="modalVerFactura" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Detalles de la Factura <span id="ver_correlativo_factura" class="badge bg-primary ms-2"></span></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row mb-3">
                    <div class="col-md-6"><p><strong>Paciente:</strong> <span id="ver_paciente_factura"></span></p></div>
                    <div class="col-md-6"><p><strong>Fecha:</strong> <span id="ver_fecha_factura"></span></p></div>
                    <div class="col-md-6"><p><strong>Estado:</strong> <span id="ver_estado_factura"></span></p></div>
                    <div class="col-md-6"><p><strong>Registrado por:</strong> <span id="ver_usuario_factura"></span></p></div>
                    <div class="col-md-6"><p><strong>Médico:</strong> <span id="ver_medico_factura"></span></p></div>
                    <div class="col-md-6"><p><strong>Técnico:</strong> <span id="ver_tecnico_factura"></span></p></div>
                </div>
                <h6>Items Facturados</h6>
                <table class="table table-bordered">
                    <thead><tr><th>Descripción</th><th>Cantidad</th><th>Precio</th><th>Descuento</th><th>ISV</th><th>Total</th></tr></thead>
                    <tbody id="ver_detalle_body_factura"></tbody>
                </table>
                <div class="row justify-content-between mt-3">
                    <div class="col-md-6">
                        <div id="contenedor_pagos_detalle">
                            <h6>Formas de Pago Utilizadas</h6>
                            <ul class="list-group" id="ver_detalle_pago_body">
                            </ul>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <ul class="list-group">
                            <li class="list-group-item d-flex justify-content-between align-items-center">Subtotal<span id="ver_subtotal_general_factura">L 0.00</span></li>
                            <li class="list-group-item d-flex justify-content-between align-items-center">Descuento<span id="ver_descuento_total_factura">L 0.00</span></li>
                            <li class="list-group-item d-flex justify-content-between align-items-center">ISV<span id="ver_isv_general_factura">L 0.00</span></li>
                            <li class="list-group-item d-flex justify-content-between align-items-center"><strong>Total Factura</strong><strong id="ver_total_general_factura">L 0.00</strong></li>
                            
                            <li class="list-group-item d-flex justify-content-between align-items-center list-group-item-success">
                                <strong>Total Abonado</strong>
                                <strong id="ver_total_abonado_factura">L 0.00</strong>
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-center list-group-item-warning">
                                <strong>Saldo Pendiente</strong>
                                <strong id="ver_saldo_pendiente_factura">L 0.00</strong>
                            </li>
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

<div class="modal fade" id="modalCambiarEstadoFactura" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header"><h5 class="modal-title">Cambiar Estado de la Factura</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <form id="formCambiarEstadoFactura">
                <div class="modal-body">
                    <input type="hidden" id="id_factura_estado">
                    <p>Seleccione el nuevo estado para la factura <strong id="correlativo_factura_estado"></strong>.</p>
                    <div class="mb-3">
                        <label for="nuevo_estado_factura" class="form-label">Nuevo Estado</label>
                        <select class="form-select" id="nuevo_estado_factura" required>
                            <option value="Borrador">Borrador</option>
                            <option value="Pagada">Pagada</option>
                            <option value="Anulada">Anulada</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button><button type="submit" class="btn btn-primary">Guardar Estado</button></div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="modalRegistrarPago" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Registrar Pago de Factura <span id="pago_correlativo" class="badge bg-primary ms-2"></span></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="formRegistrarPago">
                <div class="modal-body">
                    <input type="hidden" id="pago_id_factura">
                    <div class="row">
                        <div class="col-md-7">
                            <div class="mb-3">
                                <label for="forma_pago_principal" class="form-label">Forma de Pago</label>
                                <select id="forma_pago_principal" class="form-select">
                                    <option value="Efectivo">Efectivo</option>
                                    <option value="Tarjeta">Tarjeta</option>
                                    <option value="Transferencia">Transferencia</option>
                                    <option value="Multiple">Múltiple</option>
                                </select>
                            </div>
                            <div id="detalle-pago-container"></div>
                        </div>
                        <div class="col-md-5">
                            <h6>Resumen de la Factura</h6>
                            <ul class="list-group">
                                <li class="list-group-item d-flex justify-content-between align-items-center">Subtotal:<span id="pago_subtotal">L 0.00</span></li>
                                <li class="list-group-item d-flex justify-content-between align-items-center">ISV:<span id="pago_isv_total">L 0.00</span></li>
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    <strong>Total Factura:</strong>
                                    <strong id="pago_total_original">L 0.00</strong>
                                </li>
                                <li class="list-group-item d-flex justify-content-between align-items-center list-group-item-success">
                                    <strong>Ya Abonado:</strong>
                                    <strong id="pago_ya_abonado">L 0.00</strong>
                                </li>
                                <li class="list-group-item d-flex justify-content-between align-items-center list-group-item-info">
                                    <strong>Saldo Pendiente:</strong>
                                    <strong id="pago_saldo_pendiente_real">L 0.00</strong>
                                </li>
                                </ul>
                            <h6 class="mt-3">Pago Actual</h6>
                            <ul class="list-group">
                                <li class="list-group-item d-flex justify-content-between align-items-center">Monto a Ingresar:<span id="pago_total_pagado">L 0.00</span></li>
                                <li class="list-group-item d-flex justify-content-between align-items-center list-group-item-danger">
                                    <strong>Restante:</strong>
                                    <strong id="pago_pendiente">L 0.00</strong>
                                </li>
                                <li class="list-group-item d-flex justify-content-between align-items-center list-group-item-success" id="fila_cambio" style="display: none;">
                                    <strong>Cambio:</strong>
                                    <strong id="pago_cambio">L 0.00</strong>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary" id="btnGuardarPago">Guardar Pago</button>
                    <button type="submit" class="btn btn-success" id="btnGuardarImprimirPago"><i class="fas fa-print me-1"></i>Guardar e Imprimir</button>
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