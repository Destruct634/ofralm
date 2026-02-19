<?php
header("Content-Type: application/json; charset=UTF-8");
include_once '../../config/Database.php';
include_once '../../app/models/ReporteMedico.php';
include_once '../../app/models/Medico.php';
include_once '../../app/core/Auth.php';

session_start();

// Verificar permisos (CORREGIDO: Usa el permiso específico)
if (!Auth::check('reportes_medicos', 'ver')) {
    http_response_code(403);
    echo json_encode(["message" => "Acceso denegado."]);
    exit();
}

$database = new Database();
$db = $database->getConnection();
$reporte = new ReporteMedico($db);

$action = $_GET['action'] ?? '';

switch ($action) {
    case 'get_medicos':
        $medicoModel = new Medico($db);
        $stmt = $medicoModel->leer();
        $medicos = $stmt->fetchAll(PDO::FETCH_ASSOC);
        // Filtramos solo activos
        $activos = array_filter($medicos, function($m) { return $m['estado'] == 'Activo'; });
        echo json_encode(array_values($activos));
        break;

    case 'generar_reporte':
        $medico_id = $_GET['medico_id'] ?? 0;
        $inicio = $_GET['start_date'] ?? date('Y-m-01');
        $fin = $_GET['end_date'] ?? date('Y-m-t');

        if ($medico_id == 0) {
            echo json_encode(['error' => 'Seleccione un médico']);
            break;
        }

        $citas_servicio = $reporte->obtenerCitasPorServicio($medico_id, $inicio, $fin);
        $total_ingresos = $reporte->obtenerIngresos($medico_id, $inicio, $fin);
        $ingresos_dia = $reporte->obtenerIngresosPorDia($medico_id, $inicio, $fin);
        $presentismo = $reporte->obtenerPresentismo($medico_id, $inicio, $fin);

        echo json_encode([
            'citas_servicio' => $citas_servicio,
            'total_ingresos' => $total_ingresos,
            'ingresos_dia' => $ingresos_dia,
            'presentismo' => $presentismo
        ]);
        break;
}
?>