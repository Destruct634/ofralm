<?php
// app/controllers/DiagnosticoController.php
header("Content-Type: application/json; charset=UTF-8");

include_once '../../config/Database.php';
include_once '../../app/models/Diagnostico.php';
include_once '../../app/core/Auth.php';

session_start();

// --- ACTIVAR ESCUDO DE SEGURIDAD ---
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    Auth::validateCsrfToken();
}
// -----------------------------------

// Validar permisos (Usaremos los mismos que 'consulta_plantillas' o un permiso de admin)
if (!Auth::check('consulta_plantillas', 'ver') && !Auth::check('pacientes', 'ver')) {
    http_response_code(403);
    echo json_encode(["message" => "Acceso denegado."]);
    exit;
}

$database = new Database();
$db = $database->getConnection();
$diagnostico = new Diagnostico($db);

$request_method = $_SERVER["REQUEST_METHOD"];

switch ($request_method) {
    case 'GET':
        if (!empty($_GET['id'])) {
            // Obtener un solo diagnóstico (para el modal de editar)
            $entry = $diagnostico->leerUno(intval($_GET['id']));
            if ($entry) {
                http_response_code(200);
                echo json_encode($entry);
            } else {
                http_response_code(404);
                echo json_encode(['message' => 'Diagnóstico no encontrado.']);
            }
        } else {
            // Obtener todos los diagnósticos (para la tabla principal)
            $stmt = $diagnostico->leer();
            $diagnosticos_arr = ["data" => []];
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $diagnosticos_arr["data"][] = $row;
            }
            http_response_code(200);
            echo json_encode($diagnosticos_arr);
        }
        break;

    case 'POST':
        if (!Auth::check('consulta_plantillas', 'crear') && !Auth::check('pacientes', 'crear')) {
             http_response_code(403); echo json_encode(["message" => "Acceso denegado."]); break;
        }
        $data = json_decode(file_get_contents("php://input"), true);
        
        if (!empty($data['descripcion']) && !empty($data['estado'])) {
            if ($diagnostico->crear($data)) {
                http_response_code(201);
                echo json_encode(["message" => "Diagnóstico creado exitosamente."]);
            } else {
                http_response_code(503);
                echo json_encode(["message" => "No se pudo crear el diagnóstico."]);
            }
        } else {
            http_response_code(400);
            echo json_encode(["message" => "Datos incompletos. 'Descripción' y 'Estado' son requeridos."]);
        }
        break;

    case 'PUT':
        if (!Auth::check('consulta_plantillas', 'editar') && !Auth::check('pacientes', 'editar')) {
             http_response_code(403); echo json_encode(["message" => "Acceso denegado."]); break;
        }
        $data = json_decode(file_get_contents("php://input"), true);
        $id = $data['id'] ?? null;

        if ($id && !empty($data['descripcion']) && !empty($data['estado'])) {
            if ($diagnostico->actualizar($id, $data)) {
                http_response_code(200);
                echo json_encode(["message" => "Diagnóstico actualizado."]);
            } else {
                http_response_code(503);
                echo json_encode(["message" => "No se pudo actualizar el diagnóstico."]);
            }
        } else {
            http_response_code(400);
            echo json_encode(["message" => "Datos incompletos."]);
        }
        break;

    case 'DELETE':
        if (!Auth::check('consulta_plantillas', 'borrar') && !Auth::check('pacientes', 'borrar')) {
             http_response_code(403); echo json_encode(["message" => "Acceso denegado."]); break;
        }
        $data = json_decode(file_get_contents("php://input"), true);
        $id = $data['id'] ?? null;

        if ($id) {
            if ($diagnostico->eliminar($id)) {
                http_response_code(200);
                echo json_encode(["message" => "Diagnóstico eliminado."]);
            } else {
                http_response_code(503);
                echo json_encode(["message" => "No se pudo eliminar el diagnóstico. Es posible que esté en uso."]);
            }
        } else {
            http_response_code(400);
            echo json_encode(["message" => "ID no proporcionado."]);
        }
        break;
}
?>