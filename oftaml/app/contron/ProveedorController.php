<?php
header("Content-Type: application/json; charset=UTF-8");
include_once '../../config/Database.php';
include_once '../../app/models/Proveedor.php';
include_once '../../app/core/Auth.php';

session_start();

// --- ACTIVAR ESCUDO DE SEGURIDAD ---
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    Auth::validateCsrfToken();
}
// -----------------------------------

$database = new Database();
$db = $database->getConnection();
$proveedor = new Proveedor($db);
$request_method = $_SERVER["REQUEST_METHOD"];
$action = isset($_GET['action']) ? $_GET['action'] : '';

switch ($request_method) {
    case 'GET':
        if ($action == 'search' && isset($_GET['term'])) {
            $resultados = $proveedor->search($_GET['term']);
            echo json_encode($resultados);
            break;
        }

        if (!Auth::check('proveedores', 'ver')) { http_response_code(403); echo json_encode(["message" => "Acceso denegado."]); break; }
        $stmt = $proveedor->leer();
        $proveedores_arr = ["data" => []];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $proveedores_arr["data"][] = $row;
        }
        http_response_code(200);
        echo json_encode($proveedores_arr);
        break;

    case 'POST':
        if (!Auth::check('proveedores', 'crear')) { http_response_code(403); echo json_encode(["message" => "Acceso denegado."]); break; }
        $data = json_decode(file_get_contents("php://input"), true);
        if ($proveedor->crear($data)) {
            http_response_code(201);
            echo json_encode(["message" => "Proveedor creado."]);
        } else {
            http_response_code(503);
            echo json_encode(["message" => "No se pudo crear el proveedor."]);
        }
        break;

    case 'PUT':
        if (!Auth::check('proveedores', 'editar')) { http_response_code(403); echo json_encode(["message" => "Acceso denegado."]); break; }
        $data = json_decode(file_get_contents("php://input"), true);
        $id = $data['id'];
        unset($data['id']);
        if ($proveedor->actualizar($id, $data)) {
            http_response_code(200);
            echo json_encode(["message" => "Proveedor actualizado."]);
        } else {
            http_response_code(503);
            echo json_encode(["message" => "No se pudo actualizar."]);
        }
        break;

    case 'DELETE':
        if (!Auth::check('proveedores', 'borrar')) { http_response_code(403); echo json_encode(["message" => "Acceso denegado."]); break; }
        $data = json_decode(file_get_contents("php://input"), true);
        if ($proveedor->eliminar($data['id'])) {
            http_response_code(200);
            echo json_encode(["message" => "Proveedor eliminado."]);
        } else {
            http_response_code(503);
            echo json_encode(["message" => "No se pudo eliminar."]);
        }
        break;
}
?>