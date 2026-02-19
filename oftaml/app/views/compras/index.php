<div class="container-fluid px-4">
    <h1 class="mt-4">Gestión de Compras</h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="index.php?page=dashboard">Dashboard</a></li>
        <li class="breadcrumb-item active">Compras</li>
    </ol>
    <div class="card mb-4">
        <div class="card-header">
            <i class="fas fa-shopping-cart me-1"></i>
            Listado de Compras
            <button class="btn btn-primary btn-sm float-end" id="btnNuevaCompra"><i class="fas fa-plus me-1"></i>Nueva Compra</button>
        </div>
        <div class="card-body">
            <table id="tablaCompras" class="table table-striped table-bordered" style="width:100%">
                <thead><tr><th>Correlativo</th><th>Proveedor</th><th>N° Factura</th><th>Fecha</th><th>Total</th><th>Estado</th><th>Acciones</th></tr></thead>
            </table>
        </div>
    </div>
</div>

<div class="modal fade" id="modalCompra" tabindex="-1" style="display: none;" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header"><h5 class="modal-title" id="modalLabel"></h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <form id="formCompra">
                <div class="modal-body">
                    <input type="hidden" id="id" name="id">
                    <div class="row">
                        <div class="col-md-4"><label for="id_proveedor" class="form-label">Proveedor</label><select class="form-select" id="id_proveedor" required></select></div>
                        <div class="col-md-3"><label for="numero_factura" class="form-label">N° de Factura</label><input type="text" class="form-control" id="numero_factura"></div>
                        <div class="col-md-3"><label for="numero_orden" class="form-label">N° Orden de Compra</label><input type="text" class="form-control" id="numero_orden"></div>
                        <div class="col-md-2"><label for="fecha_compra" class="form-label">Fecha</label><input type="date" class="form-control" id="fecha_compra" required></div>
                    </div>
                    <hr>
                    <h6>Detalle de la Compra</h6>
                    <table class="table table-sm">
                        <thead><tr><th style="width: 40%;">Producto</th><th>Cantidad</th><th>Precio Compra</th><th>ISV</th><th>Subtotal</th><th></th></tr></thead>
                        <tbody id="detalleCompraBody"></tbody>
                    </table>
                    <button type="button" class="btn btn-success btn-sm" id="btnAgregarProducto"><i class="fas fa-plus me-1"></i>Agregar Producto</button>
                    <hr>
                    <div class="row justify-content-end">
                        <div class="col-md-4">
                            <ul class="list-group">
                                <li class="list-group-item d-flex justify-content-between align-items-center">Subtotal<span id="subtotalGeneral">L 0.00</span></li>
                                <li class="list-group-item d-flex justify-content-between align-items-center">ISV<span id="isvGeneral">L 0.00</span></li>
                                <li class="list-group-item d-flex justify-content-between align-items-center"><strong>Total</strong><strong id="totalCompra">L 0.00</strong></li>
                            </ul>
                        </div>
                    </div>
                </div>
                <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button><button type="submit" class="btn btn-primary">Guardar Compra</button></div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="modalCambiarEstado" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header"><h5 class="modal-title">Cambiar Estado de la Compra</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <form id="formCambiarEstado">
                <div class="modal-body">
                    <input type="hidden" id="id_compra_estado">
                    <p>Seleccione el nuevo estado para la compra <strong id="correlativo_estado"></strong>.</p>
                    <div class="mb-3">
                        <label for="nuevo_estado" class="form-label">Nuevo Estado</label>
                        <select class="form-select" id="nuevo_estado" required>
                            <option value="Borrador">Borrador</option>
                            <option value="Recibida">Recibida (Esto actualizará el stock)</option>
                            <option value="Cancelada">Cancelada</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button><button type="submit" class="btn btn-primary">Guardar Estado</button></div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="modalVerCompra" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Detalles de la Compra <span id="ver_correlativo" class="badge bg-primary ms-2"></span></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row mb-3">
                    <div class="col-md-4"><p><strong>Proveedor:</strong> <span id="ver_proveedor"></span></p></div>
                    <div class="col-md-4"><p><strong>N° Factura:</strong> <span id="ver_factura"></span></p></div>
                    <div class="col-md-4"><p><strong>N° Orden:</strong> <span id="ver_orden"></span></p></div>
                    <div class="col-md-4"><p><strong>Fecha:</strong> <span id="ver_fecha"></span></p></div>
                    <div class="col-md-4"><p><strong>Estado:</strong> <span id="ver_estado"></span></p></div>
                    <div class="col-md-4"><p><strong>Registrado por:</strong> <span id="ver_usuario"></span></p></div>
                </div>
                <h6>Productos Incluidos</h6>
                <table class="table table-bordered">
                    <thead><tr><th>Producto</th><th>Cantidad</th><th>Precio Unitario</th><th>ISV</th><th>Total</th></tr></thead>
                    <tbody id="ver_detalle_body"></tbody>
                </table>
                <div class="row justify-content-end mt-3">
                    <div class="col-md-4">
                        <ul class="list-group">
                            <li class="list-group-item d-flex justify-content-between align-items-center">Subtotal<span id="ver_subtotal_general">L 0.00</span></li>
                            <li class="list-group-item d-flex justify-content-between align-items-center">ISV<span id="ver_isv_general">L 0.00</span></li>
                            <li class="list-group-item d-flex justify-content-between align-items-center"><strong>Total</strong><strong id="ver_total_general">L 0.00</strong></li>
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

<div class="modal fade" id="modalPdfViewer" tabindex="-1" aria-labelledby="modalPdfViewerLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-fullscreen-lg-down">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalPdfViewerLabel">Visor de Documento</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-0" style="height: 80vh;">
                <iframe id="pdfFrame" src="" width="100%" height="100%" frameborder="0"></iframe>
            </div>
        </div>
    </div>
</div>