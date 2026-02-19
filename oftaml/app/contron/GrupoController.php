<?php
// app/controllers/GrupoController.php
header("Content-Type: application/json; charset=UTF-8");
include_once '../../config/Database.php';
include_once '../../app/models/Grupo.php';
include_once '../../app/core/Auth.php';

session_start();

// --- ACTIVAR ESCUDO DE SEGURIDAD ---
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    Auth::validateCsrfToken();
}
// -----------------------------------

$database = new Database();
$db = $database->getConnection();
$grupo = new Grupo($db);
$request_method = $_SERVER["REQUEST_METHOD"];
$action = isset($_GET['action']) ? $_GET['action'] : '';

switch ($request_method) {
    case 'GET':
        if (!Auth::check('grupos', 'ver')) {
            http_response_code(403); echo json_encode(["message" => "Acceso denegado."]); break;
        }
        if ($action == 'get_permisos' && !empty($_GET['id'])) {
            $permisos = $grupo->obtenerPermisos($_GET['id']);
            echo json_encode($permisos);
        } else {
            $stmt = $grupo->leer();
            $grupos_arr = ["data" => []];
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $grupos_arr["data"][] = $row;
            }
            http_response_code(200);
            echo json_encode($grupos_arr);
        }
        break;
    case 'POST':
        if ($action == 'update_permisos' && !empty($_POST['id_grupo'])) {
            if (!Auth::check('grupos', 'editar')) {
                http_response_code(403); echo json_encode(["message" => "Acceso denegado."]); break;
            }
            $permisos = json_decode($_POST['permisos'], true);
            if ($grupo->actualizarPermisos($_POST['id_grupo'], $permisos)) {
                http_response_code(200);
                echo json_encode(["message" => "Permisos actualizados."]);
            } else {
                http_response_code(503);
                echo json_encode(["message" => "Error de base de datos al actualizar permisos."]);
            }
        } else {
            if (!Auth::check('grupos', 'crear')) {
                http_response_code(403); echo json_encode(["message" => "Acceso denegado."]); break;
            }
            $data = json_decode(file_get_contents("php://input"), true);
            if ($grupo->crear($data)) {
                http_response_code(201);
                echo json_encode(["message" => "Grupo creado."]);
            } else {
                http_response_code(503);
                echo json_encode(["message" => "No se pudo crear el grupo."]);
            }
        }
        break;
    case 'PUT':
        if (!Auth::check('grupos', 'editar')) {
            http_response_code(403); echo json_encode(["message" => "Acceso denegado."]); break;
        }
        $data = json_decode(file_get_contents("php://input"), true);
        $id = $data['id'];
        unset($data['id']);
        if ($grupo->actualizar($id, $data)) {
            http_response_code(200);
            echo json_encode(["message" => "Grupo actualizado."]);
        } else {
            http_response_code(503);
            echo json_encode(["message" => "No se pudo actualizar."]);
        }
        break;
    case 'DELETE':
        if (!Auth::check('grupos', 'borrar')) {
            http_response_code(403); echo json_encode(["message" => "Acceso denegado."]); break;
        }
        $data = json_decode(file_get_contents("php://input"), true);
        if ($grupo->eliminar($data['id'])) {
            http_response_code(200);
            echo json_encode(["message" => "Grupo eliminado."]);
        } else {
            http_response_code(503);
            echo json_encode(["message" => "No se pudo eliminar."]);
        }
        break;
}
?>