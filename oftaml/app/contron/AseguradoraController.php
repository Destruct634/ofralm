<?php
header("Content-Type: application/json; charset=UTF-8");
include_once '../../config/Database.php';
include_once '../../app/models/Aseguradora.php';
include_once '../../app/core/Auth.php';

session_start();

// --- ACTIVAR ESCUDO DE SEGURIDAD ---
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    Auth::validateCsrfToken();
}
// -----------------------------------

$database = new Database();
$db = $database->getConnection();
$aseguradora = new Aseguradora($db);
$request_method = $_SERVER["REQUEST_METHOD"];
$action = isset($_GET['action']) ? $_GET['action'] : '';

switch ($request_method) {
    case 'GET':
        if (!Auth::check('aseguradoras', 'ver')) {
            http_response_code(403); echo json_encode(["message" => "Acceso denegado."]); break;
        }
        $stmt = $aseguradora->leer();
        $aseguradoras_arr = ["data" => []];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $aseguradoras_arr["data"][] = $row;
        }
        http_response_code(200);
        echo json_encode($aseguradoras_arr);
        break;
    case 'POST':
        if (!Auth::check('aseguradoras', 'crear')) {
            http_response_code(403); echo json_encode(["message" => "Acceso denegado."]); break;
        }
        $data = json_decode(file_get_contents("php://input"), true);
        if ($aseguradora->crear($data)) {
            http_response_code(201);
            echo json_encode(["message" => "Aseguradora creada."]);
        } else {
            http_response_code(503);
            echo json_encode(["message" => "No se pudo crear la aseguradora."]);
        }
        break;
    case 'PUT':
        if (!Auth::check('aseguradoras', 'editar')) {
            http_response_code(403); echo json_encode(["message" => "Acceso denegado."]); break;
        }
        $data = json_decode(file_get_contents("php://input"), true);
        $id = $data['id'];
        unset($data['id']);
        if ($aseguradora->actualizar($id, $data)) {
            http_response_code(200);
            echo json_encode(["message" => "Aseguradora actualizada."]);
        } else {
            http_response_code(503);
            echo json_encode(["message" => "No se pudo actualizar."]);
        }
        break;
    case 'DELETE':
        if (!Auth::check('aseguradoras', 'borrar')) {
            http_response_code(403); echo json_encode(["message" => "Acceso denegado."]); break;
        }
        $data = json_decode(file_get_contents("php://input"), true);
        if ($aseguradora->eliminar($data['id'])) {
            http_response_code(200);
            echo json_encode(["message" => "Aseguradora eliminada."]);
        } else {
            http_response_code(503);
            echo json_encode(["message" => "No se pudo eliminar."]);
        }
        break;
}
?>