<?php
header("Content-Type: application/json; charset=UTF-8");
include_once '../../config/Database.php';
include_once '../../app/models/CategoriaServicio.php';
include_once '../../app/core/Auth.php';

session_start();

// --- ACTIVAR ESCUDO DE SEGURIDAD ---
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    Auth::validateCsrfToken();
}
// -----------------------------------

$database = new Database();
$db = $database->getConnection();
$categoria = new CategoriaServicio($db);
$request_method = $_SERVER["REQUEST_METHOD"];

switch ($request_method) {
    case 'GET':
        if (!Auth::check('categorias_servicio', 'ver')) { http_response_code(403); echo json_encode(["message" => "Acceso denegado."]); break; }
        $stmt = $categoria->leer();
        $categorias_arr = ["data" => []];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $categorias_arr["data"][] = $row;
        }
        http_response_code(200);
        echo json_encode($categorias_arr);
        break;

    case 'POST':
        if (!Auth::check('categorias_servicio', 'crear')) { http_response_code(403); echo json_encode(["message" => "Acceso denegado."]); break; }
        $data = json_decode(file_get_contents("php://input"), true);
        if ($categoria->crear($data)) {
            http_response_code(201);
            echo json_encode(["message" => "Categoría creada."]);
        } else {
            http_response_code(503);
            echo json_encode(["message" => "No se pudo crear la categoría."]);
        }
        break;

    case 'PUT':
        if (!Auth::check('categorias_servicio', 'editar')) { http_response_code(403); echo json_encode(["message" => "Acceso denegado."]); break; }
        $data = json_decode(file_get_contents("php://input"), true);
        $id = $data['id'];
        unset($data['id']);
        if ($categoria->actualizar($id, $data)) {
            http_response_code(200);
            echo json_encode(["message" => "Categoría actualizada."]);
        } else {
            http_response_code(503);
            echo json_encode(["message" => "No se pudo actualizar."]);
        }
        break;

    case 'DELETE':
        if (!Auth::check('categorias_servicio', 'borrar')) { http_response_code(403); echo json_encode(["message" => "Acceso denegado."]); break; }
        $data = json_decode(file_get_contents("php://input"), true);
        if ($categoria->eliminar($data['id'])) {
            http_response_code(200);
            echo json_encode(["message" => "Categoría eliminada."]);
        } else {
            http_response_code(503);
            echo json_encode(["message" => "No se pudo eliminar."]);
        }
        break;
}
?>