<?php
// app/controllers/TipoCirugiaController.php
header("Content-Type: application/json; charset=UTF-8");
include_once '../../config/Database.php';
include_once '../../app/models/TipoCirugia.php';
include_once '../../app/core/Auth.php';

session_start();

// --- ACTIVAR ESCUDO DE SEGURIDAD ---
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    Auth::validateCsrfToken();
}
// -----------------------------------

$database = new Database();
$db = $database->getConnection();
$tipoCirugia = new TipoCirugia($db);
$request_method = $_SERVER["REQUEST_METHOD"];

switch ($request_method) {
    case 'GET':
        if (!Auth::check('tipos_cirugia', 'ver')) {
            http_response_code(403); echo json_encode(["message" => "Acceso denegado."]); break;
        }
        $stmt = $tipoCirugia->leer();
        $tipos_arr = ["data" => []];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $tipos_arr["data"][] = $row;
        }
        http_response_code(200);
        echo json_encode($tipos_arr);
        break;

    case 'POST':
        if (!Auth::check('tipos_cirugia', 'crear')) {
            http_response_code(403); echo json_encode(["message" => "Acceso denegado."]); break;
        }
        $data = json_decode(file_get_contents("php://input"), true);
        if ($tipoCirugia->crear($data)) {
            http_response_code(201);
            echo json_encode(["message" => "Tipo de cirugía creado."]);
        } else {
            http_response_code(503);
            echo json_encode(["message" => "No se pudo crear el tipo de cirugía."]);
        }
        break;

    case 'PUT':
        if (!Auth::check('tipos_cirugia', 'editar')) {
            http_response_code(403); echo json_encode(["message" => "Acceso denegado."]); break;
        }
        $data = json_decode(file_get_contents("php://input"), true);
        $id = $data['id'];
        unset($data['id']);
        if ($tipoCirugia->actualizar($id, $data)) {
            http_response_code(200);
            echo json_encode(["message" => "Tipo de cirugía actualizado."]);
        } else {
            http_response_code(503);
            echo json_encode(["message" => "No se pudo actualizar."]);
        }
        break;

    case 'DELETE':
        if (!Auth::check('tipos_cirugia', 'borrar')) {
            http_response_code(403); echo json_encode(["message" => "Acceso denegado."]); break;
        }
        $data = json_decode(file_get_contents("php://input"), true);
        if ($tipoCirugia->eliminar($data['id'])) {
            http_response_code(200);
            echo json_encode(["message" => "Tipo de cirugía eliminado."]);
        } else {
            http_response_code(503);
            echo json_encode(["message" => "No se pudo eliminar."]);
        }
        break;
}
?>