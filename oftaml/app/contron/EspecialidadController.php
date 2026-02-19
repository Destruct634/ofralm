<?php
// app/controllers/EspecialidadController.php
header("Content-Type: application/json; charset=UTF-8");
include_once '../../config/Database.php';
include_once '../../app/models/Especialidad.php';
include_once '../../app/core/Auth.php';

session_start();

// --- ACTIVAR ESCUDO DE SEGURIDAD ---
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    Auth::validateCsrfToken();
}
// -----------------------------------

$database = new Database();
$db = $database->getConnection();
$especialidad = new Especialidad($db);
$request_method = $_SERVER["REQUEST_METHOD"];

switch ($request_method) {
    case 'GET':
        if (!Auth::check('especialidades', 'ver')) { http_response_code(403); echo json_encode(["message" => "Acceso denegado."]); break; }
        $stmt = $especialidad->leer();
        $especialidades_arr = ["data" => []];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            array_push($especialidades_arr["data"], $row);
        }
        http_response_code(200);
        echo json_encode($especialidades_arr);
        break;

    case 'POST':
        if (!Auth::check('especialidades', 'crear')) { http_response_code(403); echo json_encode(["message" => "Acceso denegado."]); break; }
        $data = json_decode(file_get_contents("php://input"), true);
        if ($especialidad->crear($data)) {
            http_response_code(201);
            echo json_encode(["message" => "Especialidad creada."]);
        } else {
            http_response_code(503);
            echo json_encode(["message" => "No se pudo crear. El nombre puede ya existir."]);
        }
        break;

    case 'PUT':
        if (!Auth::check('especialidades', 'editar')) { http_response_code(403); echo json_encode(["message" => "Acceso denegado."]); break; }
        $data = json_decode(file_get_contents("php://input"), true);
        if (!empty($data['id']) && !empty($data['nombre']) && $especialidad->actualizar($data['id'], $data)) {
            http_response_code(200);
            echo json_encode(["message" => "Especialidad actualizada."]);
        } else {
            http_response_code(503);
            echo json_encode(["message" => "No se pudo actualizar."]);
        }
        break;

    case 'DELETE':
        if (!Auth::check('especialidades', 'borrar')) { http_response_code(403); echo json_encode(["message" => "Acceso denegado."]); break; }
        $data = json_decode(file_get_contents("php://input"), true);
        if (!empty($data['id']) && $especialidad->eliminar($data['id'])) {
            http_response_code(200);
            echo json_encode(["message" => "Especialidad eliminada."]);
        } else {
            http_response_code(503);
            echo json_encode(["message" => "No se pudo eliminar."]);
        }
        break;
}
?>