<?php
// app/controllers/DashboardWidgetController.php
header("Content-Type: application/json; charset=UTF-8");
include_once '../../config/Database.php';
include_once '../../app/models/DashboardWidget.php';
include_once '../../app/core/Auth.php';

session_start();

// --- ACTIVAR ESCUDO DE SEGURIDAD ---
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    Auth::validateCsrfToken();
}
// -----------------------------------

$database = new Database();
$db = $database->getConnection();
$widget_model = new DashboardWidget($db);

$request_method = $_SERVER["REQUEST_METHOD"];
$action = $_GET['action'] ?? '';

switch ($request_method) {
    case 'GET':
        if ($action === 'get_ingresos_por_categoria') {
            if (!Auth::check('configuracion', 'ver')) {
                http_response_code(403);
                echo json_encode(["message" => "Acceso denegado."]);
                break;
            }
            
            $year = $_GET['year'] ?? date('Y');
            $month = $_GET['month'] ?? date('m');

            include_once '../../app/models/Factura.php';
            $factura_model = new Factura($db);
            $data = $factura_model->obtenerIngresosPorCategoria($year, $month);

            http_response_code(200);
            echo json_encode($data);
        } else {
            if (!Auth::check('configuracion', 'ver')) {
                http_response_code(403); echo json_encode(["message" => "Acceso denegado."]); break;
            }
            $widgets = $widget_model->leerTodos();
            http_response_code(200);
            echo json_encode($widgets);
        }
        break;

    case 'PUT':
        if (!Auth::check('configuracion', 'editar')) {
            http_response_code(403); echo json_encode(["message" => "Acceso denegado."]); break;
        }
        $data = json_decode(file_get_contents("php://input"), true);
        if ($widget_model->actualizarConfiguracion($data)) {
            http_response_code(200);
            echo json_encode(["message" => "Configuración del dashboard actualizada."]);
        } else {
            http_response_code(503);
            echo json_encode(["message" => "No se pudo actualizar la configuración."]);
        }
        break;
}
?>