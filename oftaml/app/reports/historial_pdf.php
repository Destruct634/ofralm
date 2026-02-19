<?php
// app/reports/historial_pdf.php

if (isset($_GET['downloadToken'])) {
    setcookie('downloadToken', $_GET['downloadToken'], time() + 20, "/");
}

require_once __DIR__ . '/../../vendor/autoload.php';
include_once __DIR__ . '/../../config/Database.php';
include_once __DIR__ . '/../core/Auth.php';
include_once __DIR__ . '/../models/Paciente.php';
include_once __DIR__ . '/../models/Historial.php';
include_once __DIR__ . '/../models/Archivo.php';

session_start();

if (!Auth::check('historial', 'ver')) {
    die('Acceso Denegado. No tiene permiso para ver esta sección.');
}
if (!isset($_GET['paciente_id'])) {
    die("Error: No se ha especificado un paciente.");
}

$paciente_id = intval($_GET['paciente_id']);

$database = new Database();
$db = $database->getConnection();

$paciente_model = new Paciente($db);
$paciente = $paciente_model->leerUno($paciente_id);

$historial_model = new Historial($db);
// Esta función ya trae todos los datos nuevos gracias a nuestra modificación anterior
$historial = $historial_model->leerPorPaciente($paciente_id)->fetchAll(PDO::FETCH_ASSOC);

$archivo_model = new Archivo($db);

// Función para calcular la edad
function calcularEdadPaciente($fechaNacimiento) {
    if (!$fechaNacimiento) return 'N/A';
    $hoy = new DateTime();
    $nacimiento = new DateTime($fechaNacimiento);
    $edad = $hoy->diff($nacimiento);
    return $edad->y;
}
$edadPaciente = calcularEdadPaciente($paciente['fecha_nacimiento']);

ob_start();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Historial Clínico de <?php echo htmlspecialchars($paciente['nombres'] . ' ' . $paciente['apellidos']); ?></title>
    <style>
        body { font-family: sans-serif; font-size: 12px; }
        h1 { color: #333; text-align: center; border-bottom: 2px solid #ccc; padding-bottom: 10px; }
        .paciente-info { background-color: #f2f2f2; padding: 15px; border-radius: 5px; margin-bottom: 20px; }
        .paciente-info p { margin: 0; padding: 2px 0; }
        .historia { border: 1px solid #ccc; margin-bottom: 15px; border-radius: 5px; page-break-inside: avoid; }
        .historia-header { background-color: #e9ecef; padding: 8px; font-weight: bold; }
        .historia-body { padding: 10px; }
        .historia-body p { margin-top: 0; }
        .historia-body div { margin-bottom: 5px; }
        .archivos a { text-decoration: none; color: #007bff; }
        .imagen-con-caption { page-break-inside: avoid; margin-bottom: 15px; text-align: center; border-bottom: 1px solid #eee; padding-bottom: 15px; }
        .imagen-con-caption img { max-width: 80%; height: auto; border: 1px solid #ddd; margin-bottom: 5px; }
        .caption { background-color: #f2f2f2; padding: 5px; font-size: 10px; font-style: italic; color: #555; display: inline-block; border-radius: 3px; }
        
        .seccion-titulo { font-weight: bold; margin-bottom: 3px; }
        .seccion-contenido { padding-left: 10px; }
        .seccion-contenido p { margin: 0; }
        .row { margin-bottom: 5px; }
        .col-6 { display: inline-block; width: 49%; }
        hr { border: 0; border-top: 1px solid #eee; margin: 10px 0; }

    </style>
</head>
<body>
    <h1>Historial Clínico</h1>
    <div class="paciente-info">
        <p><strong>Paciente:</strong> <?php echo htmlspecialchars($paciente['nombres'] . ' ' . $paciente['apellidos']); ?></p>
        <p><strong>Identidad:</strong> <?php echo htmlspecialchars($paciente['numero_identidad']); ?></p>
        <p><strong>Fecha de Nacimiento:</strong> <?php echo date("d/m/Y", strtotime($paciente['fecha_nacimiento'])); ?></p>
        <p><strong>Edad:</strong> <?php echo $edadPaciente; ?> años</p>
    </div>

    <h2>Entradas del Historial</h2>
    <?php if (count($historial) > 0): ?>
        <?php foreach ($historial as $entrada): ?>
            <div class="historia">
                <div class="historia-header">
                    Visita del <?php echo date("d/m/Y", strtotime($entrada['fecha_registro'])); ?> - 
                    Dr(a). <?php echo htmlspecialchars($entrada['medico']); ?> 
                    (<?php echo htmlspecialchars($entrada['especialidad']); ?>)
                </div>
                
                <div class="historia-body">
                    
                    <?php if (!empty($entrada['hea'])): ?>
                        <div class="seccion-titulo">Historia de la Enfermedad Actual (HEA):</div>
                        <div class="seccion-contenido"><?php echo nl2br(htmlspecialchars($entrada['hea'])); ?></div>
                        <hr>
                    <?php endif; ?>

                    <?php if (!empty($entrada['av_sc_od']) || !empty($entrada['pio_od'])): ?>
                        <div class="seccion-titulo">Agudeza Visual y PIO:</div>
                        <div class="seccion-contenido">
                            <div class="row">
                                <div class="col-6">
                                    <p><strong>OD:</strong> 
                                        AVsc: <?php echo htmlspecialchars($entrada['av_sc_od'] ?? 'N/A'); ?> | 
                                        AVcc: <?php echo htmlspecialchars($entrada['av_cc_od'] ?? 'N/A'); ?> | 
                                        PIO: <?php echo htmlspecialchars($entrada['pio_od'] ?? 'N/A'); ?>
                                    </p>
                                </div>
                                <div class="col-6">
                                    <p><strong>OS:</strong> 
                                        AVsc: <?php echo htmlspecialchars($entrada['av_sc_os'] ?? 'N/A'); ?> | 
                                        AVcc: <?php echo htmlspecialchars($entrada['av_cc_os'] ?? 'N/A'); ?> | 
                                        PIO: <?php echo htmlspecialchars($entrada['pio_os'] ?? 'N/A'); ?>
                                    </p>
                                </div>
                            </div>
                        </div>
                        <hr>
                    <?php endif; ?>

                    <?php if (!empty($entrada['od_esfera']) || !empty($entrada['os_esfera'])): ?>
                        <div class="seccion-titulo">Refracción (<?php echo htmlspecialchars($entrada['tipo_refraccion']); ?>):</div>
                        <div class="seccion-contenido">
                            <div class="row">
                                <div class="col-6">
                                    <p><strong>OD:</strong> 
                                        <?php echo htmlspecialchars($entrada['od_esfera'] ?? '0.00'); ?> / 
                                        <?php echo htmlspecialchars($entrada['od_cilindro'] ?? '0.00'); ?> / 
                                        <?php echo htmlspecialchars($entrada['od_eje'] ?? '0'); ?>° 
                                        (AV: <?php echo htmlspecialchars($entrada['od_av'] ?? 'N/A'); ?>)
                                    </p>
                                </div>
                                <div class="col-6">
                                    <p><strong>OS:</strong> 
                                        <?php echo htmlspecialchars($entrada['os_esfera'] ?? '0.00'); ?> / 
                                        <?php echo htmlspecialchars($entrada['os_cilindro'] ?? '0.00'); ?> / 
                                        <?php echo htmlspecialchars($entrada['os_eje'] ?? '0'); ?>° 
                                        (AV: <?php echo htmlspecialchars($entrada['os_av'] ?? 'N/A'); ?>)
                                    </p>
                                </div>
                            </div>
                        </div>
                        <hr>
                    <?php endif; ?>
                    
                    <div class="seccion-titulo">Examen Clínico:</div>
                    <div class="seccion-contenido">
                        <?php if (!empty($entrada['biomicroscopia'])): ?>
                            <div style="margin-bottom: 5px;"><strong>Biomicroscopía:</strong> <?php echo $entrada['biomicroscopia']; ?></div>
                        <?php endif; ?>
                        <?php if (!empty($entrada['fondo_ojo'])): ?>
                            <div style="margin-bottom: 5px;"><strong>Fondo de Ojo:</strong> <?php echo $entrada['fondo_ojo']; ?></div>
                        <?php endif; ?>
                        <?php if (!empty($entrada['observaciones'])): ?>
                            <div><strong>Observaciones:</strong> <?php echo $entrada['observaciones']; ?></div>
                        <?php endif; ?>
                        </div>
                    <hr>

                    <div class="seccion-titulo">Diagnóstico (IDx):</div>
                    <div class="seccion-contenido"><?php echo $entrada['diagnostico']; ?></div>
                    <br>
                    
                    <div class="seccion-titulo">Plan:</div>
                    <div class="seccion-contenido"><?php echo $entrada['tratamiento']; ?></div>

                    <?php 
                        $archivos = $archivo_model->leerPorHistorial($entrada['id']);
                        if (count($archivos) > 0):
                    ?>
                        <hr>
                        <p><strong>Archivos Adjuntos:</strong></p>
                        <div class="archivos">
                            <?php foreach($archivos as $archivo): 
                                $extension = strtolower(pathinfo($archivo['nombre_original'], PATHINFO_EXTENSION));
                                $ruta_absoluta_servidor = __DIR__ . '/../../public/' . $archivo['ruta_archivo'] . $archivo['nombre_guardado'];
                            ?>
                                <?php if (in_array($extension, ['jpg', 'jpeg', 'png', 'gif']) && file_exists($ruta_absoluta_servidor)): ?>
                                    <div class="imagen-con-caption">
                                        <img src="<?php echo $ruta_absoluta_servidor; ?>">
                                        <div class="caption"><?php echo htmlspecialchars($archivo['nombre_original']); ?></div>
                                    </div>
                                <?php else: ?>
                                    <p><?php echo htmlspecialchars($archivo['nombre_original']); ?> (Archivo no visualizable en PDF)</p>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <p>No hay entradas en el historial para este paciente.</p>
    <?php endif; ?>
</body>
</html>
<?php
$html = ob_get_clean();

$mpdf = new \Mpdf\Mpdf([
    'mode' => 'utf-8',
    'format' => 'A4',
    'margin_left' => 10,
    'margin_right' => 10,
    'margin_top' => 10,
    'margin_bottom' => 10,
]);

$mpdf->WriteHTML($html);

$nombre_archivo = 'Historial-' . str_replace(' ', '_', $paciente['apellidos']) . '.pdf';
$mpdf->Output($nombre_archivo, 'D');

?>