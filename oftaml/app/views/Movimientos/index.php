<div class="container-fluid px-4">
    <h1 class="mt-4">Movimientos de Inventario</h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="index.php">Dashboard</a></li>
        <li class="breadcrumb-item active">Inventario</li>
    </ol>
    <div class="card mb-4">
        <div class="card-header">
            <i class="fas fa-exchange-alt me-1"></i>
            Historial de Movimientos
            <div class="float-end">
                <button class="btn btn-warning btn-sm text-white" id="btnAjustarStock"><i class="fas fa-tasks me-1"></i>Ajustar Stock</button>
                <button class="btn btn-danger btn-sm" id="btnNuevaSalida"><i class="fas fa-minus me-1"></i>Registrar Salida</button>
                <button class="btn btn-success btn-sm" id="btnNuevaEntrada"><i class="fas fa-plus me-1"></i>Registrar Entrada</button>
            </div>
        </div>
        <div class="card-body">
            <table id="tablaMovimientos" class="table table-striped table-bordered" style="width:100%">
                <thead><tr><th>ID</th><th>Fecha</th><th>Producto</th><th>Tipo</th><th>Cantidad</th><th>Usuario</th><th>Proveedor</th><th>Notas</th></tr></thead>
            </table>
        </div>
    </div>
</div>

<!-- Modal para Registrar Entrada -->
<div class="modal fade" id="modalEntrada" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header"><h5 class="modal-title">Registrar Entrada de Productos</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <form id="formEntrada">
                <div class="modal-body">
                    <div class="mb-3"><label for="notas_entrada" class="form-label">Notas / Referencia</label><input type="text" class="form-control" id="notas_entrada" placeholder="Ej: Ajuste de inventario, Donación"></div>
                    <hr>
                    <h6>Productos a Ingresar</h6>
                    <table class="table table-sm">
                        <thead><tr><th style="width: 70%;">Producto</th><th>Cantidad</th><th></th></tr></thead>
                        <tbody id="detalleEntradaBody"></tbody>
                    </table>
                    <button type="button" class="btn btn-success btn-sm" id="btnAgregarProductoEntrada"><i class="fas fa-plus me-1"></i>Agregar Producto</button>
                </div>
                <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button><button type="submit" class="btn btn-primary">Guardar Entrada</button></div>
            </form>
        </div>
    </div>
</div>

<!-- Modal para Registrar Salida -->
<div class="modal fade" id="modalSalida" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header"><h5 class="modal-title">Registrar Salida de Productos</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <form id="formSalida">
                <div class="modal-body">
                    <div class="mb-3"><label for="notas_salida" class="form-label">Notas / Referencia</label><input type="text" class="form-control" id="notas_salida" placeholder="Ej: Uso en procedimiento, Vencimiento"></div>
                    <hr>
                    <h6>Productos a Egresar</h6>
                    <table class="table table-sm">
                        <thead><tr><th style="width: 70%;">Producto</th><th>Cantidad</th><th></th></tr></thead>
                        <tbody id="detalleSalidaBody"></tbody>
                    </table>
                    <button type="button" class="btn btn-success btn-sm" id="btnAgregarProductoSalida"><i class="fas fa-plus me-1"></i>Agregar Producto</button>
                </div>
                <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button><button type="submit" class="btn btn-primary">Guardar Salida</button></div>
            </form>
        </div>
    </div>
</div>

<!-- Modal para Ajuste de Stock -->
<div class="modal fade" id="modalAjuste" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header"><h5 class="modal-title">Ajuste de Stock</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <form id="formAjuste">
                <div class="modal-body">
                    <div class="mb-3"><label for="ajuste_id_producto" class="form-label">Producto</label><select class="form-select" id="ajuste_id_producto" required style="width: 100%;"></select></div>
                    <div class="row">
                        <div class="col-6"><label>Stock en Sistema</label><input type="number" class="form-control" id="ajuste_stock_sistema" readonly></div>
                        <div class="col-6"><label for="ajuste_stock_real" class="form-label">Conteo Físico Real</label><input type="number" class="form-control" id="ajuste_stock_real" required></div>
                    </div>
                    <div class="mt-3">
                        <label>Diferencia</label>
                        <input type="text" class="form-control" id="ajuste_diferencia" readonly>
                    </div>
                    <div class="mt-3"><label for="ajuste_notas" class="form-label">Motivo del Ajuste</label><textarea class="form-control" id="ajuste_notas" rows="2" required></textarea></div>
                </div>
                <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button><button type="submit" class="btn btn-primary" id="btnGuardarAjuste">Guardar Ajuste</button></div>
            </form>
        </div>
    </div>
</div>