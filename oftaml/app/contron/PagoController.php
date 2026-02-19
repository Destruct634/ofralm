<?php
// app/controllers/PagoController.php
header("Content-Type: application/json; charset=UTF-8");
session_start();

// Incluir archivos necesarios
include_once '../../config/Database.php';
include_once '../../app/models/Pago.php';
include_once '../../app/models/Factura.php';
include_once '../../app/core/Auth.php';

// --- ACTIVAR ESCUDO DE SEGURIDAD ---
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    Auth::validateCsrfToken();
}
// -----------------------------------

if (!isset($_SESSION['user_id'])) {
    http_response_code(403);
    echo json_encode(["message" => "Acceso denegado."]);
    exit();
}

$database = new Database();
$db = $database->getConnection();
$pago = new Pago($db);
$facturaModel = new Factura($db);

$request_method = $_SERVER["REQUEST_METHOD"];
$action = $_GET['action'] ?? '';

switch ($request_method) {
    case 'GET':
        // 1. Obtener lista de pagos de una factura
        if ($action === 'get_pagos_factura') {
            if (!Auth::check('facturacion', 'ver')) { 
                http_response_code(403); echo json_encode(["message" => "Acceso denegado."]); break; 
            }
            
            $id_factura = isset($_GET['id_factura']) ? intval($_GET['id_factura']) : 0;
            
            if ($id_factura > 0) {
                $resultados = $pago->leerPorFactura($id_factura);
                echo json_encode($resultados);
            } else {
                echo json_encode([]);
            }
            break;
        }

        // 2. Obtener monto pendiente
        if ($action === 'get_monto_pendiente') {
            if (!Auth::check('facturacion', 'crear')) { 
                http_response_code(403); echo json_encode(["message" => "Acceso denegado."]); break; 
            }

            $id_factura = isset($_GET['id_factura']) ? intval($_GET['id_factura']) : 0;

            if ($id_factura > 0) {
                $facturaData = $facturaModel->leerUno($id_factura);
                
                if ($facturaData) {
                    $totalFactura = (float)$facturaData['total'];
                    
                    $pagosRealizados = $pago->leerPorFactura($id_factura);
                    $totalPagado = 0;
                    foreach ($pagosRealizados as $p) {
                        $totalPagado += (float)$p['monto'];
                    }
                    
                    $pendiente = $totalFactura - $totalPagado;
                    
                    if ($pendiente < 0) $pendiente = 0;

                    echo json_encode([
                        "total_factura" => $totalFactura,
                        "total_pagado" => $totalPagado,
                        "monto_pendiente" => $pendiente
                    ]);
                } else {
                    http_response_code(404);
                    echo json_encode(["message" => "Factura no encontrada."]);
                }
            } else {
                http_response_code(400);
                echo json_encode(["message" => "ID de factura inválido."]);
            }
            break;
        }
        break;

    case 'POST':
        // Verificamos permiso de crear (pagar es crear un pago)
        if (!Auth::check('facturacion', 'crear')) { 
            http_response_code(403); echo json_encode(["message" => "Acceso denegado."]); break; 
        }

        $data = json_decode(file_get_contents("php://input"), true);
        
        if (empty($data['id_factura']) || empty($data['detalle'])) {
            http_response_code(400);
            echo json_encode(["message" => "Datos incompletos para registrar el pago."]);
            break;
        }

        try {
            if ($pago->crear($data)) {
                http_response_code(201);
                echo json_encode(["message" => "Pago registrado exitosamente.", "id_factura" => $data['id_factura']]);
            } else {
                throw new Exception("No se pudo registrar el pago en la base de datos.");
            }
        } catch (Exception $e) {
            http_response_code(503);
            echo json_encode(["message" => $e->getMessage()]);
        }
        break;
    
    default:
        http_response_code(405);
        echo json_encode(["message" => "Método no permitido."]);
        break;
}
?>