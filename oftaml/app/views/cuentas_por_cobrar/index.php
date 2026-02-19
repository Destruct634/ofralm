<div class="container-fluid px-4">
    <h1 class="mt-4">Cuentas por Cobrar</h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="index.php?page=dashboard">Dashboard</a></li>
        <li class="breadcrumb-item">Ventas</li>
        <li class="breadcrumb-item active">Cuentas por Cobrar</li>
    </ol>

    <div class="card mb-4">
        <div class="card-header">
            <i class="fas fa-hand-holding-usd me-1"></i>
            Listado de Facturas con Saldo Pendiente
        </div>
        <div class="card-body">
            <table id="tablaCuentasPorCobrar" class="table table-striped table-bordered" style="width:100%">
                <thead>
                    <tr>
                        <th>Factura #</th>
                        <th>Paciente</th>
                        <th>Teléfono</th>
                        <th>Fecha Emisión</th>
                        <th>Total Factura</th>
                        <th>Total Pagado</th>
                        <th>Saldo Pendiente</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>
</div>

<div class="modal fade" id="modalRegistrarPago" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Registrar Abono a Factura <span id="pago_correlativo" class="badge bg-primary ms-2"></span></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="formRegistrarPago">
                <div class="modal-body">
                    <input type="hidden" id="pago_id_factura">
                    <input type="hidden" id="pago_saldo_pendiente">
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
                            <h6>Resumen de la Deuda</h6>
                            <ul class="list-group mb-3">
                                <li class="list-group-item d-flex justify-content-between align-items-center list-group-item-info">
                                    <strong>Saldo Pendiente:</strong>
                                    <strong id="pago_total_factura">L 0.00</strong>
                                </li>
                            </ul>
                            
                            <h6>Resumen del Abono Actual</h6>
                            <ul class="list-group mb-3">
                                <li class="list-group-item d-flex justify-content-between align-items-center">Total Abonado:<span id="pago_total_pagado">L 0.00</span></li>
                                <li class="list-group-item d-flex justify-content-between align-items-center list-group-item-success" id="fila_cambio" style="display: none;">
                                    <strong>Cambio:</strong>
                                    <strong id="pago_cambio">L 0.00</strong>
                                </li>
                            </ul>

                            <div id="historial_abonos_container" style="display: none;">
                                <hr>
                                <h6 class="text-muted"><i class="fas fa-history me-1"></i> Historial de Abonos</h6>
                                <ul class="list-group list-group-flush" id="lista_abonos_previos" style="max-height: 150px; overflow-y: auto; font-size: 0.9em;">
                                    </ul>
                            </div>
                            </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary" id="btnGuardarPago">Guardar Abono</button>
                </div>
            </form>
        </div>
    </div>
</div>