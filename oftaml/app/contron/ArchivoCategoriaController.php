<?php
// app/controllers/ArchivoCategoriaController.php
header("Content-Type: application/json; charset=UTF-8");
include_once '../../config/Database.php';
include_once '../../app/models/ArchivoCategoria.php';
include_once '../../app/core/Auth.php';

session_start();

// --- ACTIVAR ESCUDO DE SEGURIDAD ---
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    Auth::validateCsrfToken();
}
// -----------------------------------

$database = new Database();
$db = $database->getConnection();
$categoria = new ArchivoCategoria($db);
$request_method = $_SERVER["REQUEST_METHOD"];

switch ($request_method) {
    case 'GET':
        if (isset($_GET['action']) && $_GET['action'] == 'get_activos') {
             if (!Auth::check('historial', 'ver')) {
                http_response_code(403); echo json_encode(["message" => "Acceso denegado."]); break;
            }
            $categorias = $categoria->leerActivos();
            echo json_encode($categorias);
            break;
        }
        if (!Auth::check('archivo_categorias', 'ver')) {
            http_response_code(403); echo json_encode(["message" => "Acceso denegado."]); break;
        }
        $stmt = $categoria->leer();
        $categorias_arr = ["data" => []]; 
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $categorias_arr["data"][] = $row;
        }
        echo json_encode($categorias_arr);
        break;

    case 'POST':
        if (!Auth::check('archivo_categorias', 'crear')) {
            http_response_code(403); echo json_encode(["message" => "Acceso denegado."]); break;
        }
        $data = json_decode(file_get_contents("php://input"), true);
        if ($categoria->crear($data)) {
            http_response_code(201);
            echo json_encode(["message" => "Categoría creada exitosamente."]);
        } else {
            http_response_code(503);
            echo json_encode(["message" => "No se pudo crear la categoría."]);
        }
        break;
        
    case 'PUT':
        if (!Auth::check('archivo_categorias', 'editar')) {
            http_response_code(403); echo json_encode(["message" => "Acceso denegado."]); break;
        }
        $data = json_decode(file_get_contents("php://input"), true);
        $id = $data['id'] ?? null;
        if ($id && $categoria->actualizar($id, $data)) {
             http_response_code(200);
             echo json_encode(["message" => "Categoría actualizada exitosamente."]);
        } else {
             http_response_code(503);
             echo json_encode(["message" => "No se pudo actualizar la categoría."]);
        }
        break;

    case 'DELETE':
        if (!Auth::check('archivo_categorias', 'borrar')) {
            http_response_code(403); echo json_encode(["message" => "Acceso denegado."]); break;
        }
        $data = json_decode(file_get_contents("php://input"), true);
        $id = $data['id'] ?? null;
        if ($id && $categoria->eliminar($id)) {
            http_response_code(200);
            echo json_encode(["message" => "Categoría eliminada exitosamente."]);
        } else {
            http_response_code(503);
            echo json_encode(["message" => "No se pudo eliminar la categoría."]);
        }
        break;
}
?>