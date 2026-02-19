<?php
header("Content-Type: application/json; charset=UTF-8");
include_once '../../config/Database.php';
include_once '../../app/models/Servicio.php';
include_once '../../app/core/Auth.php';

session_start();

// --- ACTIVAR ESCUDO DE SEGURIDAD ---
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    Auth::validateCsrfToken();
}
// -----------------------------------

$database = new Database();
$db = $database->getConnection();
$servicio = new Servicio($db);
$request_method = $_SERVER["REQUEST_METHOD"];
$action = isset($_GET['action']) ? $_GET['action'] : '';

switch ($request_method) {
    case 'GET':
        if ($action == 'search' && isset($_GET['term'])) {
            $resultados = $servicio->search($_GET['term']);
            echo json_encode($resultados);
            break;
        }
        if ($action == 'get_categorias') {
            $categorias = $servicio->obtenerCategorias();
            echo json_encode($categorias);
            break;
        }
        if ($action == 'get_isv') {
            $tipos_isv = $servicio->obtenerTiposIsv();
            echo json_encode($tipos_isv);
            break;
        }

        if (!Auth::check('servicios', 'ver')) { http_response_code(403); echo json_encode(["message" => "Acceso denegado."]); break; }
        
        if (!empty($_GET['id'])) {
            $serv = $servicio->leerUno(intval($_GET['id']));
            if ($serv) {
                echo json_encode($serv);
            } else {
                http_response_code(404);
                echo json_encode(["message" => "Servicio no encontrado."]);
            }
        } else {
            $stmt = $servicio->leer();
            $servicios_arr = ["data" => []];
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $servicios_arr["data"][] = $row;
            }
            http_response_code(200);
            echo json_encode($servicios_arr);
        }
        break;

    case 'POST':
        if (!Auth::check('servicios', 'crear')) { http_response_code(403); echo json_encode(["message" => "Acceso denegado."]); break; }
        $data = json_decode(file_get_contents("php://input"), true);
        if ($servicio->crear($data)) {
            http_response_code(201);
            echo json_encode(["message" => "Servicio creado."]);
        } else {
            http_response_code(503);
            echo json_encode(["message" => "No se pudo crear el servicio."]);
        }
        break;

    case 'PUT':
        if (!Auth::check('servicios', 'editar')) { http_response_code(403); echo json_encode(["message" => "Acceso denegado."]); break; }
        $data = json_decode(file_get_contents("php://input"), true);
        $id = $data['id'];
        unset($data['id']);
        if ($servicio->actualizar($id, $data)) {
            http_response_code(200);
            echo json_encode(["message" => "Servicio actualizado."]);
        } else {
            http_response_code(503);
            echo json_encode(["message" => "No se pudo actualizar."]);
        }
        break;

    case 'DELETE':
        if (!Auth::check('servicios', 'borrar')) { http_response_code(403); echo json_encode(["message" => "Acceso denegado."]); break; }
        $data = json_decode(file_get_contents("php://input"), true);
        if ($servicio->eliminar($data['id'])) {
            http_response_code(200);
            echo json_encode(["message" => "Servicio eliminado."]);
        } else {
            http_response_code(503);
            echo json_encode(["message" => "No se pudo eliminar."]);
        }
        break;
}
?>