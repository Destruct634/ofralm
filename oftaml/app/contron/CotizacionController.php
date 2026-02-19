<?php
header("Content-Type: application/json; charset=UTF-8");
include_once '../../config/Database.php';
include_once '../../app/models/Cotizacion.php';
include_once '../../app/core/Auth.php';

session_start();

// --- ACTIVAR ESCUDO DE SEGURIDAD ---
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    Auth::validateCsrfToken();
}
// -----------------------------------

$database = new Database();
$db = $database->getConnection();
$cotizacion = new Cotizacion($db);
$request_method = $_SERVER["REQUEST_METHOD"];

switch ($request_method) {
    case 'GET':
        if (!Auth::check('cotizaciones', 'ver')) { http_response_code(403); echo json_encode(["message" => "Acceso denegado."]); break; }
        
        if (!empty($_GET['id'])) {
            $id_cotizacion = intval($_GET['id']);
            $cotizacion_data = $cotizacion->leerUno($id_cotizacion);
            if ($cotizacion_data) {
                $cotizacion_data['detalle'] = $cotizacion->leerDetalle($id_cotizacion);
                echo json_encode($cotizacion_data);
            } else {
                http_response_code(404);
                echo json_encode(["message" => "Cotización no encontrada."]);
            }
        } else {
            $startDate = $_GET['start_date'] ?? null;
            $endDate = $_GET['end_date'] ?? null;
            $stmt = $cotizacion->leer($startDate, $endDate);

            $cotizaciones_arr = ["data" => []];
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $cotizaciones_arr["data"][] = $row;
            }
            http_response_code(200);
            echo json_encode($cotizaciones_arr);
        }
        break;

    case 'POST':
        $action = $_GET['action'] ?? '';
        if ($action === 'convertir') {
            if (!Auth::check('facturacion', 'crear')) { 
                http_response_code(403); 
                echo json_encode(["message" => "No tiene permiso para crear facturas."]); 
                break; 
            }
            $data = json_decode(file_get_contents("php://input"), true);
            $id_cotizacion = $data['id_cotizacion'];
            $id_usuario = $_SESSION['user_id'];
            
            $id_factura_nueva = $cotizacion->convertirAFactura($id_cotizacion, $id_usuario);
            
            if ($id_factura_nueva) {
                http_response_code(200);
                echo json_encode(["message" => "Cotización convertida a factura exitosamente.", "id_factura" => $id_factura_nueva]);
            } else {
                http_response_code(503);
                echo json_encode(["message" => "No se pudo convertir la cotización."]);
            }
            break;
        }

        if (!Auth::check('cotizaciones', 'crear')) { http_response_code(403); echo json_encode(["message" => "Acceso denegado."]); break; }
        $data = json_decode(file_get_contents("php://input"), true);
        $data['id_usuario'] = $_SESSION['user_id'];

        $newId = $cotizacion->crear($data);
        if ($newId) {
            http_response_code(201);
            echo json_encode(["message" => "Cotización creada.", "id" => $newId]);
        } else {
            http_response_code(503);
            echo json_encode(["message" => "No se pudo crear la cotización."]);
        }
        break;

    case 'PUT':
        if (!Auth::check('cotizaciones', 'editar')) { http_response_code(403); echo json_encode(["message" => "Acceso denegado."]); break; }
        $data = json_decode(file_get_contents("php://input"), true);
        $id_cotizacion = $data['id'];
        unset($data['id']);
        if ($cotizacion->actualizar($id_cotizacion, $data)) {
            http_response_code(200);
            echo json_encode(["message" => "Cotización actualizada."]);
        } else {
            http_response_code(503);
            echo json_encode(["message" => "No se pudo actualizar la cotización."]);
        }
        break;

    case 'DELETE':
        if (!Auth::check('cotizaciones', 'borrar')) { http_response_code(403); echo json_encode(["message" => "Acceso denegado."]); break; }
        $data = json_decode(file_get_contents("php://input"), true);
        $id_cotizacion = $data['id'];
        if ($cotizacion->eliminar($id_cotizacion)) {
            http_response_code(200);
            echo json_encode(["message" => "Cotización eliminada."]);
        } else {
            http_response_code(503);
            echo json_encode(["message" => "No se pudo eliminar la cotización."]);
        }
        break;
}
?>