<?php
header("Content-Type: application/json; charset=UTF-8");
include_once '../../config/Database.php';
include_once '../../app/models/Paciente.php';
include_once '../../app/core/Auth.php';

session_start();

// --- ACTIVAR ESCUDO DE SEGURIDAD ---
// Validamos el token CSRF para cualquier petición que no sea GET (POST, PUT, DELETE)
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    Auth::validateCsrfToken();
}
// -----------------------------------

$database = new Database();
$db = $database->getConnection();
$paciente = new Paciente($db);
$request_method = $_SERVER["REQUEST_METHOD"];
$action = isset($_GET['action']) ? $_GET['action'] : '';

switch ($request_method) {
    case 'GET':
        if (!Auth::check('pacientes', 'ver')) {
            http_response_code(403); echo json_encode(["message" => "Acceso denegado."]); break;
        }

        if ($action == 'search' && isset($_GET['term'])) {
            $resultados = $paciente->search($_GET['term']);
            echo json_encode($resultados);
        } elseif ($action == 'get_aseguradoras') {
            include_once '../../app/models/Aseguradora.php';
            $aseguradora = new Aseguradora($db);
            $activas = $aseguradora->leerActivas();
            echo json_encode($activas);
        } elseif ($action == 'search_by_id' && !empty($_GET['id'])) {
            $pacienteData = $paciente->searchById(intval($_GET['id']));
            if ($pacienteData) {
                echo json_encode($pacienteData);
            } else {
                http_response_code(404);
                echo json_encode(null);
            }
        } elseif (!empty($_GET['id'])) {
            $stmt = $paciente->leerUno(intval($_GET['id']));
            if ($stmt) {
                echo json_encode($stmt);
            } else {
                http_response_code(404);
                echo json_encode(array("message" => "Paciente no encontrado."));
            }
        } else {
            $stmt = $paciente->leer();
            $pacientes_arr = ["data" => []];
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $pacientes_arr["data"][] = $row;
            }
            echo json_encode($pacientes_arr);
        }
        break;
        
    case 'POST':
        if (!Auth::check('pacientes', 'crear')) {
            http_response_code(403); echo json_encode(["message" => "Acceso denegado."]); break;
        }
        $data = json_decode(file_get_contents("php://input"), true);
        
        if ($paciente->crear($data)) {
            $nuevoPacienteId = $db->lastInsertId();
            // Buscamos el paciente recién creado para devolverlo en el formato que Select2 necesita (id, text)
            $nuevoPaciente = $paciente->searchById($nuevoPacienteId); 
            http_response_code(201);
            echo json_encode(["message" => "Paciente fue creado exitosamente.", "paciente" => $nuevoPaciente]);
        } else {
            http_response_code(503);
            echo json_encode(["message" => "No se pudo crear el paciente."]);
        }
        break;

    case 'PUT':
        if (!Auth::check('pacientes', 'editar')) {
            http_response_code(403); echo json_encode(["message" => "Acceso denegado."]); break;
        }
        $data = json_decode(file_get_contents("php://input"), true);
        $id = $data['id'];
        unset($data['id']);
        if ($paciente->actualizar($id, $data)) {
            http_response_code(200);
            echo json_encode(["message" => "Paciente fue actualizado."]);
        } else {
            http_response_code(503);
            echo json_encode(["message" => "No se pudo actualizar el paciente."]);
        }
        break;

    case 'DELETE':
        if (!Auth::check('pacientes', 'borrar')) {
            http_response_code(403); echo json_encode(["message" => "Acceso denegado."]); break;
        }
        $data = json_decode(file_get_contents("php://input"), true);
        if ($paciente->eliminar($data['id'])) {
            http_response_code(200);
            echo json_encode(["message" => "Paciente fue eliminado."]);
        } else {
            http_response_code(503);
            echo json_encode(["message" => "No se pudo eliminar."]);
        }
        break;
}
?>