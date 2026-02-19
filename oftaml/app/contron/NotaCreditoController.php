<?php
// app/controllers/NotaCreditoController.php
header("Content-Type: application/json; charset=UTF-8");
include_once '../../config/Database.php';
include_once '../../app/models/NotaCredito.php';
include_once '../../app/models/Factura.php';
include_once '../../app/core/Auth.php';

session_start();

// --- ACTIVAR ESCUDO DE SEGURIDAD ---
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    Auth::validateCsrfToken();
}
// -----------------------------------

$database = new Database();
$db = $database->getConnection();
$nota_credito = new NotaCredito($db);

$request_method = $_SERVER["REQUEST_METHOD"];
$action = $_GET['action'] ?? '';

switch ($request_method) {
    case 'GET':
        if ($action == 'get_factura_detalle' && !empty($_GET['id_factura'])) {
            if (!Auth::check('facturacion', 'ver')) {
                http_response_code(403);
                echo json_encode(["message" => "Acceso denegado."]);
                break;
            }
            $factura_model = new Factura($db);
            $id_factura = intval($_GET['id_factura']);
            $factura_data = $factura_model->leerUno($id_factura);
            if ($factura_data) {
                $factura_data['detalle'] = $factura_model->leerDetalle($id_factura);
                echo json_encode($factura_data);
            } else {
                http_response_code(404);
                echo json_encode(["message" => "Factura no encontrada."]);
            }
        } 
        elseif (!empty($_GET['id'])) {
            if (!Auth::check('facturacion', 'ver')) {
                http_response_code(403); echo json_encode(["message" => "Acceso denegado."]); break;
            }
            $id_nc = intval($_GET['id']);
            $nc_data = $nota_credito->leerUno($id_nc);
            if ($nc_data) {
                $nc_data['detalle'] = $nota_credito->leerDetalle($id_nc);
                echo json_encode($nc_data);
            } else {
                http_response_code(404);
                echo json_encode(["message" => "Nota de crédito no encontrada."]);
            }
        }
        else {
            if (!Auth::check('facturacion', 'ver')) { 
                http_response_code(403);
                echo json_encode(["message" => "Acceso denegado."]);
                break;
            }
            $stmt = $nota_credito->leer();
            $results_arr = ["data" => []];
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $results_arr["data"][] = $row;
            }
            http_response_code(200);
            echo json_encode($results_arr);
        }
        break;

    case 'POST':
        if (!Auth::check('facturacion', 'crear')) {
            http_response_code(403);
            echo json_encode(["message" => "No tiene permiso para crear notas de crédito."]);
            break;
        }

        $data = json_decode(file_get_contents("php://input"), true);
        $data['id_usuario'] = $_SESSION['user_id'];

        $newId = $nota_credito->crear($data);
        if ($newId) {
            http_response_code(201);
            echo json_encode(["message" => "Nota de Crédito creada exitosamente.", "id" => $newId]);
        } else {
            http_response_code(503);
            echo json_encode(["message" => "No se pudo crear la nota de crédito."]);
        }
        break;
}
?>