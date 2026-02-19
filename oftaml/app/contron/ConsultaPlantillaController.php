<?php
// app/controllers/ConsultaPlantillaController.php
header("Content-Type: application/json; charset=UTF-8");
include_once '../../config/Database.php';
include_once '../../app/models/ConsultaPlantilla.php';
include_once '../../app/core/Auth.php';

session_start();

// --- ACTIVAR ESCUDO DE SEGURIDAD ---
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    Auth::validateCsrfToken();
}
// -----------------------------------

$database = new Database();
$db = $database->getConnection();
$plantilla = new ConsultaPlantilla($db);
$request_method = $_SERVER["REQUEST_METHOD"];

switch ($request_method) {
    case 'GET':
        // Endpoint para cargar plantillas activas en dropdowns
        if (isset($_GET['action']) && $_GET['action'] == 'get_activos') {
             if (!Auth::check('historial', 'ver')) {
                http_response_code(403); echo json_encode(["message" => "Acceso denegado."]); break;
            }
            $plantillas = $plantilla->leerActivos();
            echo json_encode($plantillas);
            break;
        }

        // Endpoint para obtener una SOLA plantilla por su ID
        elseif (isset($_GET['id'])) {
            if (!Auth::check('consulta_plantillas', 'ver')) {
                http_response_code(403); echo json_encode(["message" => "Acceso denegado."]); break;
            }
            $data = $plantilla->leerUno($_GET['id']);
            if ($data) {
                http_response_code(200);
                echo json_encode($data);
            } else {
                http_response_code(404);
                echo json_encode(["message" => "Plantilla no encontrada."]);
            }
            break;
        }

        // Endpoint para la tabla de gestión (todos los registros)
        if (!Auth::check('consulta_plantillas', 'ver')) {
            http_response_code(403); echo json_encode(["message" => "Acceso denegado."]); break;
        }
        $stmt = $plantilla->leer();
        $plantillas_arr = ["data" => []];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $plantillas_arr["data"][] = $row;
        }
        echo json_encode($plantillas_arr);
        break;

    case 'POST':
        if (!Auth::check('consulta_plantillas', 'crear')) {
            http_response_code(403); echo json_encode(["message" => "Acceso denegado."]); break;
        }
        $data = json_decode(file_get_contents("php://input"), true);
        if ($plantilla->crear($data)) {
            http_response_code(201);
            echo json_encode(["message" => "Plantilla creada exitosamente."]);
        } else {
            http_response_code(503);
            echo json_encode(["message" => "No se pudo crear la plantilla."]);
        }
        break;
        
    case 'PUT':
        if (!Auth::check('consulta_plantillas', 'editar')) {
            http_response_code(403); echo json_encode(["message" => "Acceso denegado."]); break;
        }
        $data = json_decode(file_get_contents("php://input"), true);
        $id = $data['id'] ?? null;
        if ($id && $plantilla->actualizar($id, $data)) {
             http_response_code(200);
             echo json_encode(["message" => "Plantilla actualizada exitosamente."]);
        } else {
             http_response_code(503);
             echo json_encode(["message" => "No se pudo actualizar la plantilla."]);
        }
        break;

    case 'DELETE':
        if (!Auth::check('consulta_plantillas', 'borrar')) {
            http_response_code(403); echo json_encode(["message" => "Acceso denegado."]); break;
        }
        $data = json_decode(file_get_contents("php://input"), true);
        $id = $data['id'] ?? null;
        if ($id && $plantilla->eliminar($id)) {
            http_response_code(200);
            echo json_encode(["message" => "Plantilla eliminada exitosamente."]);
        } else {
            http_response_code(503);
            echo json_encode(["message" => "No se pudo eliminar la plantilla."]);
        }
        break;
}
?>