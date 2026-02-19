<?php
// app/views/historial/index.php

// Incluir el verificador de permisos al principio
include_once '../app/core/Auth.php';

// VERIFICACIÓN DE PERMISOS
if (!Auth::check('historial', 'ver')) {
    echo "<div class='container-fluid px-4'><div class='alert alert-danger mt-4'><h3>Acceso Denegado</h3><p>No tienes permiso para ver esta sección.</p></div></div>";
    // detenemos la ejecución del resto de la página
    include_once '../app/views/templates/footer.php';
    exit;
}

if (!isset($_GET['paciente_id'])) {
    echo "<div class='alert alert-danger'>Error: No se ha especificado un paciente.</div>";
    exit;
}
$paciente_id = intval($_GET['paciente_id']);

// Incluir todos los modelos necesarios
include_once '../app/models/Paciente.php';
include_once '../app/models/Cita.php';
include_once '../app/models/Historial.php';
include_once '../app/models/Archivo.php';

// Obtener datos
$paciente_model = new Paciente($db);
$paciente = $paciente_model->leerUno($paciente_id);

$cita_model = new Cita($db);
$todas_las_citas = $cita_model->leerPorPaciente($paciente_id)->fetchAll(PDO::FETCH_ASSOC);
$totalVisitas = count($todas_las_citas);

$historial_model = new Historial($db);
$historial = $historial_model->leerPorPaciente($paciente_id)->fetchAll(PDO::FETCH_ASSOC);

$archivo_model = new Archivo($db);
$totalArchivos = $archivo_model->contarPorPaciente($paciente_id);

if (!$paciente) {
    echo "<div class='alert alert-danger'>Error: Paciente no encontrado.</div>";
    exit;
}

// Lógica para el breadcrumb
if (isset($_SESSION['is_medico']) && $_SESSION['is_medico'] === true) {
    $breadcrumb_link = 'index.php?page=mis_citas';
    $breadcrumb_text = 'Mis Citas';
} else {
    $breadcrumb_link = 'index.php?page=pacientes';
    $breadcrumb_text = 'Pacientes';
}

// Función para calcular la edad del paciente
function calcularEdadPaciente($fechaNacimiento) {
    if (!$fechaNacimiento) return 'N/A';
    $hoy = new DateTime();
    $nacimiento = new DateTime($fechaNacimiento);
    $edad = $hoy->diff($nacimiento);
    return $edad->y;
}
$edadPaciente = calcularEdadPaciente($paciente['fecha_nacimiento']);

// Separar citas pasadas/hoy de futuras
$hoy = date('Y-m-d');
$citas_pasadas_y_hoy = [];
$citas_futuras = [];
foreach ($todas_las_citas as $cita) {
    if ($cita['fecha_cita'] <= $hoy) {
        $citas_pasadas_y_hoy[] = $cita;
    } else {
        $citas_futuras[] = $cita;
    }
}
?>

<div class="container-fluid px-4">
    <h1 class="mt-4">Historia Clínica</h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="<?php echo $breadcrumb_link; ?>"><?php echo $breadcrumb_text; ?></a></li>
        <li class="breadcrumb-item active">Historial de <?php echo htmlspecialchars($paciente['nombres'] . ' ' . $paciente['apellidos']); ?></li>
    </ol>

    <div class="row">
        <div class="col-lg-3">
            <div class="card mb-4">
                <div class="card-header bg-light">
                    <h5 class="mb-0"><i class="fas fa-user me-2"></i>Información del Paciente</h5>
                </div>
                <div class="card-body">
                    <strong>Nombre:</strong>
                    <p class="text-muted"><?php echo htmlspecialchars($paciente['nombres'] . ' ' . $paciente['apellidos']); ?></p>
                    <strong>Identidad:</strong>
                    <p class="text-muted"><?php echo htmlspecialchars($paciente['numero_identidad']); ?></p>
                    <strong>Fecha de Nacimiento:</strong>
                    <p class="text-muted"><?php echo date("d/m/Y", strtotime($paciente['fecha_nacimiento'])); ?></p>
                    <strong>Teléfono:</strong>
                    <p class="text-muted"><?php echo htmlspecialchars($paciente['telefono']); ?></p>
                    <strong>Email:</strong>
                    <p class="text-muted"><?php echo htmlspecialchars($paciente['email']); ?></p>
                </div>
                <div class="card-footer text-center">
                    <button class="btn btn-outline-primary btn-sm" data-bs-toggle="modal" data-bs-target="#modalInfoPaciente">
                        Ver Ficha Completa
                    </button>
                </div>
            </div>

            <div class="d-grid gap-2">
                <button class="btn btn-info text-white" id="btnVerArchivosPaciente" 
                        data-paciente-id="<?php echo $paciente_id; ?>" 
                        data-bs-toggle="modal" data-bs-target="#modalListaArchivosPaciente">
                    <i class="fas fa-folder-open me-2"></i>
                    Archivos del Paciente
                    <span class="badge bg-light text-dark ms-2"><?php echo $totalArchivos; ?></span>
                </button>
                <button id="btnGenerarPdf" class="btn btn-danger" 
                        data-url="../app/reports/historial_pdf.php?paciente_id=<?php echo $paciente_id; ?>">
                    <i class="fas fa-file-pdf me-2"></i>
                    Generar PDF
                </button>
                
                <?php if (Auth::check('historial', 'crear')): // Solo si puede crear historial ?>
                <button class="btn btn-success" id="btnVisitaRapida" 
                        data-paciente-id="<?php echo $paciente_id; ?>">
                    <i class="fas fa-plus-circle me-2"></i>
                    Agregar Visita Rápida
                </button>
                <?php endif; ?>

                <?php if (isset($_SESSION['is_medico']) && $_SESSION['is_medico'] === true): ?>
                <a href="index.php?page=citas&action=nuevo&paciente_id=<?php echo $paciente_id; ?>" class="btn btn-primary mt-2">
                    <i class="fas fa-calendar-plus me-2"></i>
                    Nueva Cita para este Paciente
                </a>
                <?php endif; ?>
            </div>
            </div>

        <div class="col-lg-6">
            <div class="card mb-4">
                <div class="card-body">
                    <div class="row gx-2">
                        <div class="col-md-6">
                            <label for="filtroBusquedaHistorial" class="form-label">Buscar en Historial:</label>
                            <input type="text" id="filtroBusquedaHistorial" class="form-control" placeholder="Escriba para buscar...">
                        </div>
                        <div class="col-md-3">
                            <label for="filtroFechaDesde" class="form-label">Desde:</label>
                            <input type="date" id="filtroFechaDesde" class="form-control">
                        </div>
                        <div class="col-md-3">
                            <label for="filtroFechaHasta" class="form-label">Hasta:</label>
                            <input type="date" id="filtroFechaHasta" class="form-control">
                        </div>
                    </div>
                    <div class="row gx-2 mt-2 align-items-center">
                        <div class="col-12 text-end">
                            <small class="me-2 text-muted fw-bold">Vista de Archivos:</small>
                            <div class="btn-group" role="group" aria-label="Vista Imágenes">
                                <input type="radio" class="btn-check" name="viewImgOption" id="viewImgGrid" autocomplete="off" checked>
                                <label class="btn btn-outline-secondary btn-sm" for="viewImgGrid" title="Cuadrícula"><i class="fas fa-th"></i> Cuadrícula</label>

                                <input type="radio" class="btn-check" name="viewImgOption" id="viewImgFull" autocomplete="off">
                                <label class="btn btn-outline-secondary btn-sm" for="viewImgFull" title="Expandida"><i class="fas fa-stop"></i> Expandida</label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

             <div class="timeline">
                <?php if (count($historial) > 0): ?>
                    <?php foreach ($historial as $index => $entrada): ?>
                        <div class="timeline-item item-historial" data-fecha="<?php echo date("Y-m-d", strtotime($entrada['fecha_registro'])); ?>" <?php if ($index >= 3) echo 'style="display: none;"'; ?>>
                            <div class="timeline-dot"></div>
                            <div class="timeline-content">
                                <div class="card">
                                    <div class="card-header d-flex justify-content-between align-items-center">
                                        <strong><?php echo htmlspecialchars($entrada['especialidad']); ?></strong>
                                        <div>
                                            <small class="text-muted me-2"><?php echo date("d/m/Y", strtotime($entrada['fecha_registro'])); ?></small>
                                            
                                            <?php if (Auth::check('pacientes', 'editar')): ?>
                                                <button class="btn btn-info btn-sm btn-ver-log" title="Ver Historial de Cambios"
                                                        data-historial-id="<?php echo $entrada['id']; ?>">
                                                    <i class="fas fa-history"></i>
                                                </button>
                                                <button class="btn btn-warning btn-sm btn-editar-historial" title="Editar Entrada"
                                                        data-historial-id="<?php echo $entrada['id']; ?>">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                            <?php endif; ?>
                                        </div>
                                    </div>

                                    <div class="card-body">
                                        <p class="card-subtitle mb-2 text-muted">Atendido por: Dr(a). <?php echo htmlspecialchars($entrada['medico']); ?></p>

                                        <?php if (!empty($entrada['hea'])): ?>
                                            <p><strong>HEA:</strong> <?php echo nl2br(htmlspecialchars($entrada['hea'])); ?></p>
                                        <?php endif; ?>

                                        <?php if (!empty($entrada['av_sc_od']) || !empty($entrada['pio_od'])): ?>
                                            <div class="mb-2">
                                                <strong>Agudeza Visual y PIO:</strong>
                                                <div class="row gx-2">
                                                    <div class="col-lg-6 col-md-12">
                                                        <small><strong>OD:</strong> 
                                                            AVsc: <?php echo htmlspecialchars($entrada['av_sc_od'] ?? 'N/A'); ?> | 
                                                            AVcc: <?php echo htmlspecialchars($entrada['av_cc_od'] ?? 'N/A'); ?> | 
                                                            PIO: <?php echo htmlspecialchars($entrada['pio_od'] ?? 'N/A'); ?>
                                                        </small>
                                                    </div>
                                                    <div class="col-lg-6 col-md-12">
                                                        <small><strong>OS:</strong> 
                                                            AVsc: <?php echo htmlspecialchars($entrada['av_sc_os'] ?? 'N/A'); ?> | 
                                                            AVcc: <?php echo htmlspecialchars($entrada['av_cc_os'] ?? 'N/A'); ?> | 
                                                            PIO: <?php echo htmlspecialchars($entrada['pio_os'] ?? 'N/A'); ?>
                                                        </small>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php endif; ?>
                                        
                                        <?php if (!empty($entrada['od_esfera']) || !empty($entrada['os_esfera'])): ?>
                                            <div class="mb-2">
                                                <strong>Refracción (<?php echo htmlspecialchars($entrada['tipo_refraccion']); ?>):</strong>
                                                <div class="row gx-2">
                                                    <div class="col-lg-6 col-md-12">
                                                        <small><strong>OD:</strong> 
                                                            <?php echo htmlspecialchars($entrada['od_esfera'] ?? '0.00'); ?> / 
                                                            <?php echo htmlspecialchars($entrada['od_cilindro'] ?? '0.00'); ?> / 
                                                            <?php echo htmlspecialchars($entrada['od_eje'] ?? '0'); ?>° 
                                                            (AV: <?php echo htmlspecialchars($entrada['od_av'] ?? 'N/A'); ?>)
                                                        </small>
                                                    </div>
                                                    <div class="col-lg-6 col-md-12">
                                                        <small><strong>OS:</strong> 
                                                            <?php echo htmlspecialchars($entrada['os_esfera'] ?? '0.00'); ?> / 
                                                            <?php echo htmlspecialchars($entrada['os_cilindro'] ?? '0.00'); ?> / 
                                                            <?php echo htmlspecialchars($entrada['os_eje'] ?? '0'); ?>° 
                                                            (AV: <?php echo htmlspecialchars($entrada['os_av'] ?? 'N/A'); ?>)
                                                        </small>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php endif; ?>
                                        
                                        <?php if (!empty($entrada['biomicroscopia']) || !empty($entrada['fondo_ojo']) || !empty($entrada['observaciones'])): ?>
                                            <hr class="my-2">
                                            <?php if (!empty($entrada['biomicroscopia'])): ?>
                                                <div class="mb-2"><strong>Biomicroscopía:</strong> <?php echo $entrada['biomicroscopia']; ?></div>
                                            <?php endif; ?>
                                            <?php if (!empty($entrada['fondo_ojo'])): ?>
                                                <div class="mb-2"><strong>Fondo de Ojo:</strong> <?php echo $entrada['fondo_ojo']; ?></div>
                                            <?php endif; ?>
                                            <?php if (!empty($entrada['observaciones'])): ?>
                                                <div><strong>Observaciones:</strong> <?php echo $entrada['observaciones']; ?></div>
                                            <?php endif; ?>
                                        <?php endif; ?>

                                        <hr class="my-2">
                                        <div><strong>Diagnóstico (IDx):</strong> <?php echo htmlspecialchars($entrada['diagnostico']); ?></div>
                                        <div><strong>Plan:</strong> <?php echo $entrada['tratamiento']; ?></div>
                                        
                                        <?php if (!empty($entrada['archivos_concatenados'])): ?>
                                            <hr class="my-2">
                                            <strong>Archivos Adjuntos:</strong>
                                            <div class="row gx-2 mt-2">
                                                <?php 
                                                // 1. Explotamos por el separador principal '||'
                                                $lista_items_archivos = explode('||', $entrada['archivos_concatenados']);
                                                $img_exts = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
                                                
                                                foreach ($lista_items_archivos as $item_archivo):
                                                    // 2. Explotamos por el separador secundario '::'
                                                    $partes = explode('::', $item_archivo);
                                                    $archivo_ruta = $partes[0] ?? '';
                                                    $archivo_nombre = $partes[1] ?? 'Sin Título';

                                                    $ext = strtolower(pathinfo($archivo_ruta, PATHINFO_EXTENSION));
                                                ?>
                                                    <?php if (in_array($ext, $img_exts)): ?>
                                                        <div class="col-md-6 col-lg-4 mb-3 container-img-historial">
                                                            <a href="#" class="btn-ver-archivo" data-url="<?php echo htmlspecialchars($archivo_ruta); ?>">
                                                                <img src="<?php echo htmlspecialchars($archivo_ruta); ?>" class="img-fluid rounded shadow-sm border" style="max-height: 250px; object-fit: cover; width: 100%;" alt="<?php echo htmlspecialchars($archivo_nombre); ?>">
                                                            </a>
                                                            <div class="text-center small text-muted text-truncate mt-1" title="<?php echo htmlspecialchars($archivo_nombre); ?>">
                                                                <i class="fas fa-image me-1"></i> <?php echo htmlspecialchars($archivo_nombre); ?>
                                                            </div>
                                                        </div>
                                                    
                                                    <?php elseif ($ext === 'pdf'): ?>
                                                        <div class="col-md-6 col-lg-4 mb-3 container-img-historial">
                                                            <div class="border shadow-sm rounded overflow-hidden" style="height: 210px; position: relative;">
                                                                <embed src="<?php echo htmlspecialchars($archivo_ruta); ?>" type="application/pdf" width="100%" height="100%" />
                                                                
                                                                </div>
                                                            <a href="#" class="btn btn-sm btn-outline-danger w-100 mt-1 btn-ver-archivo" data-url="<?php echo htmlspecialchars($archivo_ruta); ?>">
                                                                <i class="fas fa-file-pdf me-1"></i> Ver PDF Completo
                                                            </a>
                                                            <div class="text-center small text-muted text-truncate mt-1" title="<?php echo htmlspecialchars($archivo_nombre); ?>">
                                                                <?php echo htmlspecialchars($archivo_nombre); ?>
                                                            </div>
                                                        </div>

                                                    <?php endif; ?>

                                                <?php endforeach; ?>
                                            </div>
                                        <?php endif; ?>
                                        
                                    </div>

                                    <div class="card-footer bg-light">
                                        <button class="btn btn-success btn-sm btn-adjuntar-archivos" 
                                                data-historial-id="<?php echo $entrada['id']; ?>"
                                                data-paciente-id="<?php echo $paciente_id; ?>">
                                            <i class="fas fa-paperclip me-1"></i> 
                                            Adjuntar/Ver Archivos
                                            <span class="badge bg-dark ms-2"><?php echo $entrada['total_archivos']; ?></span>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>

                    <?php if (count($historial) > 3): ?>
                        <div class="text-center mt-3" id="contenedor-ver-mas-historial">
                            <button class="btn btn-outline-primary btn-sm" id="btnVerMasHistorial">Ver más</button>
                        </div>
                    <?php endif; ?>

                <?php else: ?>
                    <div class="alert alert-info">No hay entradas en el historial clínico para este paciente.</div>
                <?php endif; ?>
            </div>
        </div>

        <div class="col-lg-3">
            <div class="card mb-4">
                 <div class="card-header bg-light"><h5 class="mb-0"><i class="fas fa-calendar-alt me-2"></i>Visitas</h5></div>
                <div class="list-group list-group-flush">
                    <div class="list-group-item list-group-item-secondary"><strong>Pasadas y de Hoy</strong></div>
                    <?php if (count($citas_pasadas_y_hoy) > 0): ?>
                        <?php foreach ($citas_pasadas_y_hoy as $index => $cita): ?>
                            <div class="list-group-item item-visita-pasada" <?php if ($index >= 3) echo 'style="display: none;"'; ?>>
                                <div class="d-flex w-100 justify-content-between">
                                    <h6 class="mb-1"><?php echo htmlspecialchars($cita['motivo_detalle']); ?></h6>
                                    <small><?php echo date("d/m/Y", strtotime($cita['fecha_cita'])); ?></small>
                                </div>
                                <small class="text-muted fst-italic">Atendido por: Dr(a). <?php echo htmlspecialchars($cita['medico_nombre'] ?? 'No asignado'); ?></small>
                                <div class="mt-2">
                                <?php if ($cita['tiene_historial'] > 0): ?>
                                    <button class="btn btn-success btn-sm" disabled><i class="fas fa-check-circle me-1"></i> Registrado</button>
                                <?php else: ?>
                                    <button class="btn btn-primary btn-sm btn-registrar-historial" 
                                            data-cita-id="<?php echo $cita['id']; ?>"
                                            data-paciente-id="<?php echo $paciente_id; ?>"
                                            data-medico-id="<?php echo $cita['id_medico']; ?>"><i class="fas fa-plus me-1"></i> Registrar</button>
                                <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                        
                        <?php if (count($citas_pasadas_y_hoy) > 3): ?>
                            <div class="list-group-item text-center" id="contenedor-ver-mas">
                                <button class="btn btn-outline-primary btn-sm" id="btnVerMas">Ver más</button>
                            </div>
                        <?php endif; ?>

                    <?php else: ?>
                        <div class="list-group-item text-muted small">No hay citas pasadas.</div>
                    <?php endif; ?>

                    <div class="list-group-item list-group-item-secondary"><strong>Programadas</strong></div>
                    <?php if (count($citas_futuras) > 0): ?>
                        <?php foreach ($citas_futuras as $cita): ?>
                            <div class="list-group-item">
                                <div class="d-flex w-100 justify-content-between">
                                    <h6 class="mb-1"><?php echo htmlspecialchars($cita['motivo_detalle']); ?></h6>
                                    <small><?php echo date("d/m/Y", strtotime($cita['fecha_cita'])); ?></small>
                                </div>
                                <small class="text-muted fst-italic">Atendido por: Dr(a). <?php echo htmlspecialchars($cita['medico_nombre'] ?? 'No asignado'); ?></small>
                                <div class="mt-2">
                                    <button class="btn btn-outline-secondary btn-sm" disabled title="Solo se puede registrar el historial el día de la cita o después.">
                                        <i class="fas fa-plus me-1"></i> Registrar
                                    </button>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="list-group-item text-muted small">No hay citas futuras programadas.</div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

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
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="tab-btn-consulta" data-bs-toggle="tab" data-bs-target="#tab-consulta" type="button" role="tab" aria-controls="tab-consulta" aria-selected="true">
                                <i class="fas fa-comments me-1"></i> Consulta
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="tab-btn-av" data-bs-toggle="tab" data-bs-target="#tab-av" type="button" role="tab" aria-controls="tab-av" aria-selected="false">
                                <i class="fas fa-eye me-1"></i> AV y PIO
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="tab-btn-refraccion" data-bs-toggle="tab" data-bs-target="#tab-refraccion" type="button" role="tab" aria-controls="tab-refraccion" aria-selected="false">
                                <i class="fas fa-glasses me-1"></i> Refracción
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="tab-btn-examen" data-bs-toggle="tab" data-bs-target="#tab-examen" type="button" role="tab" aria-controls="tab-examen" aria-selected="false">
                                <i class="fas fa-microscope me-1"></i> Examen Clínico
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="tab-btn-plan" data-bs-toggle="tab" data-bs-target="#tab-plan" type="button" role="tab" aria-controls="tab-plan" aria-selected="false">
                                <i class="fas fa-clipboard-list me-1"></i> Diagnóstico y Plan
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="tab-btn-items" data-bs-toggle="tab" data-bs-target="#tab-items" type="button" role="tab" aria-controls="tab-items" aria-selected="false">
                                <i class="fas fa-box me-1"></i> Servicios y Productos
                            </button>
                        </li>
                    </ul>

                    <div class="tab-content" id="consultaTabContent">
                        
                        <div class="tab-pane fade show active" id="tab-consulta" role="tabpanel" aria-labelledby="tab-btn-consulta">
                            <div class="py-3">
                                <div class="mb-3">
                                    <label for="selectPlantilla" class="form-label">Cargar Plantilla (Opcional):</label>
                                    <select class="form-select" id="selectPlantilla">
                                        <option value="">-- Seleccionar una plantilla --</option>
                                    </select>
                                </div>
                                <hr>
                                <div class="mb-3">
                                    <label for="hea" class="form-label">Historia de la Enfermedad Actual (HEA)</label>
                                    <textarea class="form-control" id="hea" name="hea" rows="8"></textarea>
                                </div>
                            </div>
                        </div>

                        <div class="tab-pane fade" id="tab-av" role="tabpanel" aria-labelledby="tab-btn-av">
                            <div class="py-3">
                                <h5 class="mb-3">Agudeza Visual y Presión Intraocular</h5>
                                <div class="row gx-2 text-center fw-bold">
                                    <div class="col-3"></div>
                                    <div class="col-3"><label class="form-label">AV sin Corrección (SC)</label></div>
                                    <div class="col-3"><label class="form-label">AV con Corrección (CC)</label></div>
                                    <div class="col-3"><label class="form-label">PIO (mmHg)</label></div>
                                </div>
                                <div class="row gx-2 mb-2 align-items-center">
                                    <div class="col-3 text-end fw-bold">
                                        <label class="form-label">Ojo Derecho (OD)</label>
                                    </div>
                                    <div class="col-3">
                                        <input type="text" class="form-control" id="av_sc_od" name="av_sc_od" placeholder="Ej. 20/40">
                                    </div>
                                    <div class="col-3">
                                        <input type="text" class="form-control" id="av_cc_od" name="av_cc_od" placeholder="Ej. 20/20">
                                    </div>
                                    <div class="col-3">
                                        <input type="text" class="form-control" id="pio_od" name="pio_od" placeholder="Ej. 15">
                                    </div>
                                </div>
                                <div class="row gx-2 mb-3 align-items-center">
                                    <div class="col-3 text-end fw-bold">
                                        <label class="form-label">Ojo Izquierdo (OS)</label>
                                    </div>
                                    <div class="col-3">
                                        <input type="text" class="form-control" id="av_sc_os" name="av_sc_os" placeholder="Ej. 20/30">
                                    </div>
                                    <div class="col-3">
                                        <input type="text" class="form-control" id="av_cc_os" name="av_cc_os" placeholder="Ej. 20/20">
                                    </div>
                                    <div class="col-3">
                                        <input type="text" class="form-control" id="pio_os" name="pio_os" placeholder="Ej. 16">
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="tab-pane fade" id="tab-refraccion" role="tabpanel" aria-labelledby="tab-btn-refraccion">
                            <div class="py-3">
                                <div class="row">
                                    <div class="col-md-4">
                                        <label for="tipo_refraccion" class="form-label">Tipo de Refracción</label>
                                        <select id="tipo_refraccion" name="tipo_refraccion" class="form-select">
                                            <option value="Refracción Actual" selected>Refracción Actual</option>
                                            <option value="Lentes Anteriores">Lentes Anteriores</option>
                                            <option value="Lentes de Contacto">Lentes de Contacto</option>
                                        </select>
                                    </div>
                                </div>
                                <hr>
                                <div class="row gx-2 text-center fw-bold">
                                    <div class="col-2"></div>
                                    <div class="col-2"><label class="form-label">Esfera</label></div>
                                    <div class="col-2"><label class="form-label">Cilindro</label></div>
                                    <div class="col-2"><label class="form-label">Eje</label></div>
                                    <div class="col-2"><label class="form-label">Agudeza Visual</label></div>
                                </div>
                                <div class="row gx-2 mb-2 align-items-center">
                                    <div class="col-2 text-end fw-bold"><label class="form-label">Ojo Derecho (OD)</label></div>
                                    <div class="col-2"><input type="number" step="0.25" class="form-control" id="od_esfera" name="od_esfera" placeholder="Ej. -1.25"></div>
                                    <div class="col-2"><input type="number" step="0.25" class="form-control" id="od_cilindro" name="od_cilindro" placeholder="Ej. -0.75"></div>
                                    <div class="col-2"><input type="number" step="1" min="0" max="180" class="form-control" id="od_eje" name="od_eje" placeholder="Ej. 90"></div>
                                    <div class="col-2"><input type="text" class="form-control" id="od_av" name="od_av" placeholder="Ej. 20/20"></div>
                                </div>
                                <div class="row gx-2 mb-3 align-items-center">
                                    <div class="col-2 text-end fw-bold"><label class="form-label">Ojo Izquierdo (OS)</label></div>
                                    <div class="col-2"><input type="number" step="0.25" class="form-control" id="os_esfera" name="os_esfera" placeholder="Ej. -1.00"></div>
                                    <div class="col-2"><input type="number" step="0.25" class="form-control" id="os_cilindro" name="os_cilindro" placeholder="Ej. -0.50"></div>
                                    <div class="col-2"><input type="number" step="1" min="0" max="180" class="form-control" id="os_eje" name="os_eje" placeholder="Ej. 100"></div>
                                    <div class="col-2"><input type="text" class="form-control" id="os_av" name="os_av" placeholder="Ej. 20/25"></div>
                                </div>
                                <hr>
                                <div class="row gx-2">
                                    <div class="col-md-3">
                                        <label for="add" class="form-label">Adición (ADD)</label>
                                        <input type="number" step="0.25" class="form-control" id="add" name="add" placeholder="Ej. +2.00">
                                    </div>
                                    <div class="col-md-9">
                                        <label for="refraccion_obs" class="form-label">Observaciones (Ej. DIP)</label>
                                        <input type="text" class="form-control" id="refraccion_obs" name="refraccion_obs">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="tab-pane fade" id="tab-examen" role="tabpanel" aria-labelledby="tab-btn-examen">
                             <div class="py-3">
                                <h5 class="mb-3">Hallazgos del Examen (Nota Médica)</h5>
                                <div class="mb-3">
                                    <label for="biomicroscopia" class="form-label">Biomicroscopía (Segmento Anterior)</label>
                                    <textarea class="form-control summernote-lite" id="biomicroscopia" name="biomicroscopia" rows="5"></textarea>
                                </div>
                                <div class="mb-3">
                                    <label for="fondo_ojo" class="form-label">Fondo de Ojo (Segmento Posterior)</label>
                                    <textarea class="form-control summernote-lite" id="fondo_ojo" name="fondo_ojo" rows="5"></textarea>
                                </div>
                                <div class="mb-3">
                                    <label for="observaciones" class="form-label">Observaciones Generales del Examen</label>
                                    <textarea class="form-control" id="observaciones" name="observaciones" rows="3"></textarea>
                                </div>
                             </div>
                        </div>

                        <div class="tab-pane fade" id="tab-plan" role="tabpanel" aria-labelledby="tab-btn-plan">
                            <div class="py-3">
                                <div class="mb-3">
                                    <label for="selectDiagnosticos" class="form-label">IDx (Diagnósticos)</label>
                                    <select class="form-select" id="selectDiagnosticos" name="diagnosticos[]" multiple="multiple" style="width: 100%;">
                                        </select>
                                </div>
                                <div class="mb-3">
                                    <label for="tratamiento" class="form-label">Plan (Tratamiento e Indicaciones)</label>
                                    <textarea class="form-control summernote-full" id="tratamiento" name="tratamiento" rows="6" required></textarea>
                                </div>
                            </div>
                        </div>

                        <div class="tab-pane fade" id="tab-items" role="tabpanel" aria-labelledby="tab-btn-items">
                            <div class="py-3">
                                <h5 class="mb-3">Añadir Servicios y Productos</h5>
                                <div class="row gx-3">
                                    <div class="col-md-5">
                                        <label for="selectItem" class="form-label">Buscar Servicio o Producto:</label>
                                        <select class="form-select" id="selectItem" style="width: 100%;">
                                            <option value="">Buscar...</option>
                                        </select>
                                    </div>
                                    <div class="col-md-2">
                                        <label for="itemCantidad" class="form-label">Cantidad:</label>
                                        <input type="number" class="form-control" id="itemCantidad" value="1" min="1">
                                    </div>
                                    <div class="col-md-2">
                                        <label for="itemDescuento" class="form-label">Descuento (L.)</label>
                                        <input type="number" class="form-control" id="itemDescuento" value="0.00" min="0" step="0.01">
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label">&nbsp;</label> 
                                        <button type="button" class="btn btn-success w-100" id="btnAnadirItemHistorial">
                                            <i class="fas fa-plus me-1"></i> Añadir
                                        </button>
                                    </div>
                                </div>

                                <div class="table-responsive mt-3">
                                    <table class="table table-bordered table-sm" id="tablaItemsHistorial">
                                        <thead class="table-light">
                                            <tr>
                                                <th>Tipo</th>
                                                <th>Descripción</th>
                                                <th style="width: 80px;">Cant.</th>
                                                <th style="width: 100px;">Precio</th>
                                                <th style="width: 100px;">Desc.</th>
                                                <th style="width: 100px;">Subtotal</th>
                                                <th style="width: 50px;"></th> 
                                            </tr>
                                        </thead>
                                        <tbody id="tbodyItemsHistorial">
                                            </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                    </div> </div> <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button><button type="submit" class="btn btn-primary">Guardar Entrada</button></div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="modalInfoPaciente" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header"><h5 class="modal-title">Ficha Completa del Paciente</h5><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button></div>
            <div class="modal-body">
                
                <ul class="nav nav-tabs" id="fichaPacienteTab" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="ficha-tab-personales" data-bs-toggle="tab" data-bs-target="#ficha-personales" type="button" role="tab" aria-controls="ficha-personales" aria-selected="true">
                            <i class="fas fa-user me-1"></i> Datos Personales
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="ficha-tab-antecedentes" data-bs-toggle="tab" data-bs-target="#ficha-antecedentes" type="button" role="tab" aria-controls="ficha-antecedentes" aria-selected="false">
                            <i class="fas fa-heartbeat me-1"></i> Antecedentes Médicos
                        </button>
                    </li>
                </ul>

                <div class="tab-content" id="fichaPacienteTabContent">
                    
                    <div class="tab-pane fade show active" id="ficha-personales" role="tabpanel" aria-labelledby="ficha-tab-personales">
                        <div class="py-3">
                            <div class="row">
                                <div class="col-md-6">
                                    <p><strong>Nombres:</strong><br><?php echo htmlspecialchars($paciente['nombres']); ?></p>
                                    <p><strong>Apellidos:</strong><br><?php echo htmlspecialchars($paciente['apellidos']); ?></p>
                                    <p><strong>Número de Identidad:</strong><br><?php echo htmlspecialchars($paciente['numero_identidad']); ?></p>
                                    <p><strong>Sexo:</strong><br><?php echo htmlspecialchars($paciente['sexo']); ?></p>
                                </div>
                                <div class="col-md-6">
                                    <p><strong>Fecha de Nacimiento:</strong><br><?php echo date("d/m/Y", strtotime($paciente['fecha_nacimiento'])); ?></p>
                                    <p><strong>Edad:</strong><br><?php echo $edadPaciente; ?> años</p>
                                    <p><strong>Teléfono:</strong><br><?php echo htmlspecialchars($paciente['telefono']); ?></p>
                                    <p><strong>Email:</strong><br><?php echo htmlspecialchars($paciente['email']); ?></p>
                                </div>
                                <div class="col-12"><p><strong>Dirección:</strong><br><?php echo htmlspecialchars($paciente['direccion']); ?></p></div>
                                <div class="col-12"><p><strong>Observaciones Generales:</strong><br><?php echo nl2br(htmlspecialchars($paciente['observaciones'] ?? 'N/A')); ?></p></div>
                                <hr class="my-3">
                                <div class="col-md-6">
                                    <p><strong>Fecha de Alta:</strong><br><?php echo date("d/m/Y h:i A", strtotime($paciente['fecha_creacion'])); ?></p>
                                </div>
                                <div class="col-md-6">
                                    <p><strong>Visitas Registradas:</strong><br><?php echo $totalVisitas; ?></p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="tab-pane fade" id="ficha-antecedentes" role="tabpanel" aria-labelledby="ficha-tab-antecedentes">
                        <div class="py-3">
                            <h5 class="mb-3">Antecedentes Médicos</h5>
                            <div class="row">
                                <div class="col-md-3"><p><strong>Diabetes (DM):</strong><br><?php echo ($paciente['antecedente_dm'] ?? 0) == 1 ? 'Sí' : 'No'; ?></p></div>
                                <div class="col-md-3"><p><strong>Hipertensión (HTA):</strong><br><?php echo ($paciente['antecedente_hta'] ?? 0) == 1 ? 'Sí' : 'No'; ?></p></div>
                                <div class="col-md-3"><p><strong>Glaucoma:</strong><br><?php echo ($paciente['antecedente_glaucoma'] ?? 0) == 1 ? 'Sí' : 'No'; ?></p></div>
                                <div class="col-md-3"><p><strong>Asma:</strong><br><?php echo ($paciente['antecedente_asma'] ?? 0) == 1 ? 'Sí' : 'No'; ?></p></div>
                            </div>
                            <hr>
                            <div class="row">
                                <div class="col-md-6"><p><strong>Alergias:</strong><br><?php echo nl2br(htmlspecialchars($paciente['alergias'] ?? 'N/A')); ?></p></div>
                                <div class="col-md-6"><p><strong>Cirugías Previas:</strong><br><?php echo nl2br(htmlspecialchars($paciente['antecedente_cirugias'] ?? 'N/A')); ?></p></div>
                                <div class="col-md-6"><p><strong>Traumas Oculares:</strong><br><?php echo nl2br(htmlspecialchars($paciente['antecedente_trauma'] ?? 'N/A')); ?></p></div>
                                <div class="col-md-6"><p><strong>Otras Enfermedades:</strong><br><?php echo nl2br(htmlspecialchars($paciente['antecedente_otros'] ?? 'N/A')); ?></p></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button></div>
        </div>
    </div>
</div>

<div class="modal fade" id="modalListaArchivosPaciente" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Todos los Archivos del Paciente</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <input type="text" id="filtroBusquedaArchivos" class="form-control mb-3" placeholder="Buscar archivos por nombre...">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Nombre del Archivo</th>
                            <th>Categoría</th>
                            <th>Subido por</th>
                            <th>Fecha</th>
                            <th class="text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody id="tablaBodyArchivosPaciente"></tbody>
                </table>
            </div>
            <div class="modal-footer justify-content-between">
                <div id="paginacionArchivos">
                    <button class="btn btn-secondary btn-sm" id="btnPaginaAnterior" disabled>Anterior</button>
                    <span class="align-middle mx-2" id="infoPaginas"></span>
                    <button class="btn btn-secondary btn-sm" id="btnPaginaSiguiente">Siguiente</button>
                </div>
                <div id="subidaDirectaPaciente">
                    <form id="formSubirArchivoPaciente" class="d-flex align-items-center" enctype="multipart/form-data">
                        <input type="hidden" name="id_paciente" value="<?php echo $paciente_id; ?>">
                        <select class="form-select form-select-sm me-2" name="id_categoria" id="selectCategoriaPaciente" style="width: 150px;"></select>
                        <input class="form-control form-control-sm me-2" type="file" name="archivos[]" id="inputArchivoPaciente" multiple required>
                        <button type="submit" class="btn btn-primary btn-sm">Subir</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modalArchivos" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header"><h5 class="modal-title" id="modalArchivosLabel">Archivos Adjuntos</h5><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button></div>
            <div class="modal-body">
                <h6>Archivos Existentes</h6>
                <ul class="list-group mb-4" id="listaArchivosAdjuntos"></ul>
                <h6>Subir Nuevos Archivos</h6>
                <form id="formSubirArchivos" enctype="multipart/form-data">
                    <input type="hidden" name="id_historial" id="upload_id_historial">
                    <input type="hidden" name="id_paciente" id="upload_id_paciente">
                    <div class="mb-3">
                        <label for="selectCategoriaArchivo" class="form-label">Categoría (Opcional):</label>
                        <select class="form-select" name="id_categoria" id="selectCategoriaArchivo">
                            <option value="">-- Sin Categoría --</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="archivos" class="form-label">Seleccionar archivos (PDF, JPG, PNG...)</label>
                        <input class="form-control" type="file" name="archivos[]" id="archivos" multiple>
                    </div>
                    <button type="submit" class="btn btn-primary">Subir Archivos</button>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modalHistorialLog" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalHistorialLogLabel">Historial de Cambios</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="logBody">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>