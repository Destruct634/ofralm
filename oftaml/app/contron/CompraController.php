<?php
header("Content-Type: application/json; charset=UTF-8");
include_once '../../config/Database.php';
include_once '../../app/models/Compra.php';
include_once '../../app/core/Auth.php';

session_start();

// --- ACTIVAR ESCUDO DE SEGURIDAD ---
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    Auth::validateCsrfToken();
}
// -----------------------------------

$database = new Database();
$db = $database->getConnection();
$compra = new Compra($db);
$request_method = $_SERVER["REQUEST_METHOD"];
$action = isset($_GET['action']) ? $_GET['action'] : '';

switch ($request_method) {
    case 'GET':
        if (!Auth::check('compras', 'ver')) { http_response_code(403); echo json_encode(["message" => "Acceso denegado."]); break; }
        
        if (!empty($_GET['id'])) {
            $id_compra = intval($_GET['id']);
            $compra_data = $compra->leerUno($id_compra);
            $compra_detalle = $compra->leerDetalle($id_compra);

            if ($compra_data) {
                $compra_data['detalle'] = $compra_detalle;
                echo json_encode($compra_data);
            } else {
                http_response_code(404);
                echo json_encode(["message" => "Compra no encontrada."]);
            }
        } else {
            $stmt = $compra->leer();
            $compras_arr = ["data" => []];
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $compras_arr["data"][] = $row;
            }
            http_response_code(200);
            echo json_encode($compras_arr);
        }
        break;

    case 'POST':
        if (!Auth::check('compras', 'crear')) { http_response_code(403); echo json_encode(["message" => "Acceso denegado."]); break; }
        $data = json_decode(file_get_contents("php://input"), true);
        $data['id_usuario'] = $_SESSION['user_id'];

        if ($compra->crear($data)) {
            http_response_code(201);
            echo json_encode(["message" => "Compra registrada como borrador."]);
        } else {
            http_response_code(503);
            echo json_encode(["message" => "No se pudo registrar la compra."]);
        }
        break;

    case 'PUT':
        if (!Auth::check('compras', 'editar')) { http_response_code(403); echo json_encode(["message" => "Acceso denegado."]); break; }
        $data = json_decode(file_get_contents("php://input"), true);

        if ($action == 'cambiar_estado') {
            $id_compra = $data['id_compra'];
            $nuevo_estado = $data['estado'];
            $id_usuario = $_SESSION['user_id'];

            $success = false;
            if ($nuevo_estado == 'Recibida') {
                $success = $compra->recibirCompra($id_compra, $id_usuario);
            } else {
                $success = $compra->cambiarEstado($id_compra, $nuevo_estado);
            }

            if ($success) {
                http_response_code(200);
                echo json_encode(["message" => "Estado de la compra actualizado."]);
            } else {
                http_response_code(503);
                echo json_encode(["message" => "No se pudo actualizar el estado."]);
            }
        } else {
            $id_compra = $data['id'];
            unset($data['id']);
            if ($compra->actualizar($id_compra, $data)) {
                http_response_code(200);
                echo json_encode(["message" => "Compra actualizada exitosamente."]);
            } else {
                http_response_code(503);
                echo json_encode(["message" => "No se pudo actualizar la compra."]);
            }
        }
        break;
}
?>