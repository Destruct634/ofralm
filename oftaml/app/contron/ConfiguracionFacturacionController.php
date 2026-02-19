<?php
header("Content-Type: application/json; charset=UTF-8");
include_once '../../config/Database.php';
include_once '../../app/models/ConfiguracionFacturacion.php';
include_once '../../app/core/Auth.php';

session_start();

// --- ACTIVAR ESCUDO DE SEGURIDAD ---
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    Auth::validateCsrfToken();
}
// -----------------------------------

$database = new Database();
$db = $database->getConnection();
$config = new ConfiguracionFacturacion($db);
$request_method = $_SERVER["REQUEST_METHOD"];

switch ($request_method) {
    case 'PUT':
        if (!Auth::check('configuracion_facturacion', 'editar')) { 
            http_response_code(403); 
            echo json_encode(["message" => "Acceso denegado."]); 
            break; 
        }
        
        $data = json_decode(file_get_contents("php://input"), true);

        if ($config->actualizar($data)) {
            http_response_code(200);
            echo json_encode(["message" => "Configuración de facturación actualizada."]);
        } else {
            http_response_code(503);
            echo json_encode(["message" => "No se pudo actualizar la configuración."]);
        }
        break;
}
?>