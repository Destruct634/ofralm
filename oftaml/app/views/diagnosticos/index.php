<?php
// app/views/diagnosticos/index.php

// Usamos el permiso de 'consulta_plantillas' como referencia
if (!Auth::check('consulta_plantillas', 'ver') && !Auth::check('pacientes', 'ver')) {
    echo "<div class='container-fluid px-4'><div class='alert alert-danger mt-4'><h3>Acceso Denegado</h3><p>No tienes permiso para ver esta sección.</p></div></div>";
    include_once '../app/views/templates/footer.php';
    exit;
}
?>

<div class="container-fluid px-4">
    <h1 class="mt-4">Gestión de Diagnósticos (IDx)</h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="index.php?page=dashboard">Dashboard</a></li>
        <li class="breadcrumb-item"><a href="index.php?page=configuracion">Configuraciones</a></li>
        <li class="breadcrumb-item active">Diagnósticos</li>
    </ol>

    <div class="card mb-4">
        <div class="card-header">
            <i class="fas fa-clipboard-list me-1"></i>
            Catálogo de Diagnósticos
            <?php if (Auth::check('consulta_plantillas', 'crear') || Auth::check('pacientes', 'crear')): ?>
                <button class="btn btn-primary btn-sm float-end" id="btnNuevoDiagnostico"><i class="fas fa-plus me-1"></i> Nuevo Diagnóstico</button>
            <?php endif; ?>
        </div>
        <div class="card-body">
            <table id="tablaDiagnosticos" class="table table-striped table-bordered" style="width:100%">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Código (CIE-10)</th>
                        <th>Descripción</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>
</div>

<div class="modal fade" id="modalDiagnostico" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalDiagnosticoLabel"></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="formDiagnostico">
                <div class="modal-body">
                    <input type="hidden" id="diagnostico_id" name="id">
                    
                    <div class="mb-3">
                        <label for="diagnostico_codigo" class="form-label">Código (CIE-10, Opcional)</label>
                        <input type="text" class="form-control" id="diagnostico_codigo" name="codigo" placeholder="Ej. H25.1">
                    </div>

                    <div class="mb-3">
                        <label for="diagnostico_descripcion" class="form-label">Descripción del Diagnóstico</label>
                        <input type="text" class="form-control" id="diagnostico_descripcion" name="descripcion" required>
                    </div>

                    <div class="mb-3">
                        <label for="diagnostico_estado" class="form-label">Estado</label>
                        <select class="form-select" id="diagnostico_estado" name="estado" required>
                            <option value="Activo">Activo</option>
                            <option value="Inactivo">Inactivo</option>
                        </select>
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