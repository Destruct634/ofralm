<div class="container-fluid px-4">
    <h1 class="mt-4">Gestión de Productos</h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="index.php?page=dashboard">Dashboard</a></li>
        <li class="breadcrumb-item active">Productos</li>
    </ol>
    <div class="card mb-4">
        <div class="card-header">
            <i class="fas fa-boxes me-1"></i>
            Listado de Productos
            <button class="btn btn-primary btn-sm float-end" id="btnNuevo"><i class="fas fa-plus me-1"></i>Nuevo Producto</button>
        </div>
        <div class="card-body">
            <table id="tablaProductos" class="table table-striped table-bordered" style="width:100%">
                <thead><tr><th>ID</th><th>Código</th><th>Producto</th><th>Categoría</th><th>Inventariable</th><th>Stock</th><th>Precio Venta</th><th>Acciones</th></tr></thead>
            </table>
        </div>
    </div>
</div>

<!-- Modal para CRUD de Productos -->
<div class="modal fade" id="modalProducto" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header"><h5 class="modal-title" id="modalLabel"></h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <form id="formProducto">
                <div class="modal-body">
                    <input type="hidden" id="id" name="id">
                    <div class="row">
                        <div class="col-md-6 mb-3"><label for="codigo" class="form-label">Código</label><input type="text" class="form-control" id="codigo" name="codigo"></div>
                        <div class="col-md-6 mb-3"><label for="codigo_barras" class="form-label">Código de Barras</label><input type="text" class="form-control" id="codigo_barras" name="codigo_barras"></div>
                    </div>
                    <div class="mb-3"><label for="nombre_producto" class="form-label">Nombre del Producto</label><input type="text" class="form-control" id="nombre_producto" name="nombre_producto" required></div>
                    <div class="mb-3"><label for="descripcion" class="form-label">Descripción</label><textarea class="form-control" id="descripcion" name="descripcion" rows="2"></textarea></div>
                    <div class="row">
                        <div class="col-md-6 mb-3"><label for="id_categoria" class="form-label">Categoría</label><select class="form-select" id="id_categoria" name="id_categoria" required></select></div>
                        <div class="col-md-6 mb-3"><label for="unidad_medida" class="form-label">Unidad de Medida</label><input type="text" class="form-control" id="unidad_medida" name="unidad_medida" placeholder="Ej: Caja, Unidad"></div>
                    </div>
                    
                    <div class="form-check form-switch mb-3">
                        <input class="form-check-input" type="checkbox" role="switch" id="es_inventariable" name="es_inventariable" checked>
                        <label class="form-check-label" for="es_inventariable">Es Inventariable (Controlar Stock)</label>
                    </div>

                    <div id="campos_stock">
                        <div class="row">
                            <div class="col-md-6 mb-3"><label for="stock_actual" class="form-label">Stock Actual</label><input type="number" class="form-control" id="stock_actual" name="stock_actual" value="0" required></div>
                            <div class="col-md-6 mb-3"><label for="stock_minimo" class="form-label">Stock Mínimo</label><input type="number" class="form-control" id="stock_minimo" name="stock_minimo" value="10" required></div>
                        </div>
                    </div>

                    <hr>
                    <div class="row align-items-end">
                        <div class="col-md-3"><label for="precio_compra" class="form-label">Precio Compra</label><input type="number" step="0.01" class="form-control" id="precio_compra" name="precio_compra" value="0.00" required></div>
                        <div class="col-md-3"><label for="margen" class="form-label">Margen (%)</label><input type="number" step="0.01" class="form-control" id="margen" name="margen" value="30.00"></div>
                        <div class="col-md-3"><label for="id_isv" class="form-label">Tipo ISV</label><select class="form-select" id="id_isv" name="id_isv"></select></div>
                        <div class="col-md-3"><label for="precio_venta" class="form-label">Precio Venta</label><input type="number" step="0.01" class="form-control" id="precio_venta" name="precio_venta" readonly></div>
                    </div>
                </div>
                <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button><button type="submit" class="btn btn-primary">Guardar</button></div>
            </form>
        </div>
    </div>
</div>

<!-- Modal para Kardex -->
<div class="modal fade" id="modalKardex" tabindex="-1">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Kardex de Producto: <span id="nombreProductoKardex"></span></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="filtro_daterange_kardex" class="form-label">Filtrar por Fecha:</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-calendar-alt"></i></span>
                            <input type="text" class="form-control" id="filtro_daterange_kardex">
                        </div>
                    </div>
                </div>
                <table class="table table-sm table-hover">
                    <thead>
                        <tr>
                            <th>Fecha</th>
                            <th>Tipo</th>
                            <th>Cantidad</th>
                            <th>Usuario</th>
                            <th>Proveedor</th>
                            <th>Notas</th>
                        </tr>
                    </thead>
                    <tbody id="kardexBody"></tbody>
                </table>
            </div>
            <div class="modal-footer d-flex justify-content-between">
                <div id="infoPaginasKardex"></div>
                <div>
                    <button class="btn btn-secondary" id="btnPaginaAnteriorKardex" disabled>Anterior</button>
                    <button class="btn btn-secondary" id="btnPaginaSiguienteKardex">Siguiente</button>
                </div>
            </div>
        </div>
    </div>
</div>
