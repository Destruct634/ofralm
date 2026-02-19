<?php
header("Content-Type: application/json; charset=UTF-8");
include_once '../../config/Database.php';
include_once '../../app/models/TipoISV.php';
include_once '../../app/core/Auth.php';

session_start();

// --- ACTIVAR ESCUDO DE SEGURIDAD ---
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    Auth::validateCsrfToken();
}
// -----------------------------------

$database = new Database();
$db = $database->getConnection();
$tipoIsv = new TipoIsv($db);
$request_method = $_SERVER["REQUEST_METHOD"];

switch ($request_method) {
    case 'GET':
        if (!Auth::check('tipos_isv', 'ver')) { http_response_code(403); echo json_encode(["message" => "Acceso denegado."]); break; }
        $stmt = $tipoIsv->leer();
        $tipos_arr = ["data" => []];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $tipos_arr["data"][] = $row;
        }
        http_response_code(200);
        echo json_encode($tipos_arr);
        break;
    case 'POST':
        if (!Auth::check('tipos_isv', 'crear')) { http_response_code(403); echo json_encode(["message" => "Acceso denegado."]); break; }
        $data = json_decode(file_get_contents("php://input"), true);
        if ($tipoIsv->crear($data)) {
            http_response_code(201);
            echo json_encode(["message" => "Tipo de ISV creado."]);
        } else {
            http_response_code(503);
            echo json_encode(["message" => "No se pudo crear el tipo de ISV."]);
        }
        break;
    case 'PUT':
        if (!Auth::check('tipos_isv', 'editar')) { http_response_code(403); echo json_encode(["message" => "Acceso denegado."]); break; }
        $data = json_decode(file_get_contents("php://input"), true);
        $id = $data['id'];
        unset($data['id']);
        if ($tipoIsv->actualizar($id, $data)) {
            http_response_code(200);
            echo json_encode(["message" => "Tipo de ISV actualizado."]);
        } else {
            http_response_code(503);
            echo json_encode(["message" => "No se pudo actualizar."]);
        }
        break;
    case 'DELETE':
        if (!Auth::check('tipos_isv', 'borrar')) { http_response_code(403); echo json_encode(["message" => "Acceso denegado."]); break; }
        $data = json_decode(file_get_contents("php://input"), true);
        if ($tipoIsv->eliminar($data['id'])) {
            http_response_code(200);
            echo json_encode(["message" => "Tipo de ISV eliminado."]);
        } else {
            http_response_code(503);
            echo json_encode(["message" => "No se pudo eliminar."]);
        }
        break;
}
?>