<?php
header("Content-Type: application/json; charset=UTF-8");
include_once '../../config/Database.php';
include_once '../../app/models/Medico.php';
include_once '../../app/core/Auth.php';

session_start();

// --- ACTIVAR ESCUDO DE SEGURIDAD ---
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    Auth::validateCsrfToken();
}
// -----------------------------------

$database = new Database();
$db = $database->getConnection();
$medico = new Medico($db);

$request_method = $_SERVER["REQUEST_METHOD"];
$action = isset($_GET['action']) ? $_GET['action'] : '';

switch ($request_method) {
    case 'GET':
        // Acción para el select de facturación
        if ($action == 'get_activos') {
            $stmt = $medico->leer();
            $medicos_activos = [];
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                if ($row['estado'] === 'Activo') {
                    $medicos_activos[] = [
                        'id' => $row['id'],
                        'nombres' => $row['nombres'],
                        'apellidos' => $row['apellidos']
                    ];
                }
            }
            echo json_encode($medicos_activos);
            break;
        }

        // Acción para listar especialidades
        if ($action == 'especialidades') {
            $query = "SELECT * FROM especialidades WHERE estado = 'Activo'";
            $stmt = $db->prepare($query);
            $stmt->execute();
            echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
            break;
        }

        // --- CORRECCIÓN: Acción para buscar médico por ID de Usuario (FALTABA ESTO) ---
        if ($action == 'get_by_user' && !empty($_GET['user_id'])) {
            // No requerimos permiso estricto aquí porque se usa al editar usuarios
            $data = $medico->leerPorUsuarioId($_GET['user_id']);
            // Si no encuentra nada, devuelve false, lo cual JS interpreta correctamente como "No es médico"
            echo json_encode($data);
            break;
        }
        // ---------------------------------------------------------------------------

        // Verificación de seguridad para el CRUD completo
        if (!Auth::check('medicos', 'ver')) {
            http_response_code(403);
            echo json_encode(["message" => "Acceso denegado."]);
            break;
        }

        $stmt = $medico->leer();
        $medicos_arr = ["data" => []];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $medicos_arr["data"][] = $row;
        }
        http_response_code(200);
        echo json_encode($medicos_arr);
        break;

    case 'POST':
        if (!Auth::check('medicos', 'crear')) { http_response_code(403); echo json_encode(["message" => "Acceso denegado."]); break; }
        $data = json_decode(file_get_contents("php://input"), true);
        if ($medico->crear($data)) {
            http_response_code(201);
            echo json_encode(["message" => "Médico creado."]);
        } else {
            http_response_code(503);
            echo json_encode(["message" => "No se pudo crear el médico."]);
        }
        break;

    case 'PUT':
        if (!Auth::check('medicos', 'editar')) { http_response_code(403); echo json_encode(["message" => "Acceso denegado."]); break; }
        $data = json_decode(file_get_contents("php://input"), true);
        if ($medico->actualizar($data)) {
            http_response_code(200);
            echo json_encode(["message" => "Médico actualizado."]);
        } else {
            http_response_code(503);
            echo json_encode(["message" => "No se pudo actualizar."]);
        }
        break;

    case 'DELETE':
        if (!Auth::check('medicos', 'borrar')) { http_response_code(403); echo json_encode(["message" => "Acceso denegado."]); break; }
        $data = json_decode(file_get_contents("php://input"), true);
        if ($medico->eliminar($data['id'])) {
            http_response_code(200);
            echo json_encode(["message" => "Médico eliminado."]);
        } else {
            http_response_code(503);
            echo json_encode(["message" => "No se pudo eliminar."]);
        }
        break;
}
?>