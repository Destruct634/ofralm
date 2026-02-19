<?php
header("Content-Type: application/json; charset=UTF-8");
include_once '../../config/Database.php';
include_once '../../app/models/TextoPredefinido.php';
include_once '../../app/core/Auth.php'; // Aseguramos que Auth esté incluido

session_start();

// --- ACTIVAR ESCUDO DE SEGURIDAD ---
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    Auth::validateCsrfToken();
}
// -----------------------------------

$database = new Database();
$db = $database->getConnection();
$texto = new TextoPredefinido($db);
$request_method = $_SERVER["REQUEST_METHOD"];
$action = isset($_GET['action']) ? $_GET['action'] : '';

switch ($request_method) {
    case 'GET':
        // Verificar permisos de ver (usamos 'textos_predefinidos' o 'historial' para lectura)
        if ($action == 'get_activos') {
             // Permitir si tiene acceso a historial (para usar los textos) o al módulo de config
             if (!Auth::check('historial', 'ver') && !Auth::check('textos_predefinidos', 'ver')) {
                 http_response_code(403); echo json_encode(["message" => "Acceso denegado."]); break;
             }
            $activos = $texto->leerActivos();
            echo json_encode($activos);
        } else {
            if (!Auth::check('textos_predefinidos', 'ver')) {
                http_response_code(403); echo json_encode(["message" => "Acceso denegado."]); break;
            }
            $stmt = $texto->leer();
            $textos_arr = ["data" => []];
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $textos_arr["data"][] = $row;
            }
            http_response_code(200);
            echo json_encode($textos_arr);
        }
        break;

    case 'POST':
        if (!Auth::check('textos_predefinidos', 'crear')) {
            http_response_code(403); echo json_encode(["message" => "Acceso denegado."]); break;
        }
        $data = json_decode(file_get_contents("php://input"), true);
        if ($texto->crear($data)) {
            http_response_code(201);
            echo json_encode(["message" => "Texto predefinido creado."]);
        } else {
            http_response_code(503);
            echo json_encode(["message" => "No se pudo crear el texto."]);
        }
        break;

    case 'PUT':
        if (!Auth::check('textos_predefinidos', 'editar')) {
            http_response_code(403); echo json_encode(["message" => "Acceso denegado."]); break;
        }
        $data = json_decode(file_get_contents("php://input"), true);
        $id = $data['id'];
        unset($data['id']);
        if ($texto->actualizar($id, $data)) {
            http_response_code(200);
            echo json_encode(["message" => "Texto predefinido actualizado."]);
        } else {
            http_response_code(503);
            echo json_encode(["message" => "No se pudo actualizar."]);
        }
        break;

    case 'DELETE':
        if (!Auth::check('textos_predefinidos', 'borrar')) {
            http_response_code(403); echo json_encode(["message" => "Acceso denegado."]); break;
        }
        $data = json_decode(file_get_contents("php://input"), true);
        if ($texto->eliminar($data['id'])) {
            http_response_code(200);
            echo json_encode(["message" => "Texto predefinido eliminado."]);
        } else {
            http_response_code(503);
            echo json_encode(["message" => "No se pudo eliminar."]);
        }
        break;
}
?>