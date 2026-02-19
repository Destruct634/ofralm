<?php
header("Content-Type: application/json; charset=UTF-8");
include_once '../../config/Database.php';
include_once '../../app/models/Movimiento.php';
include_once '../../app/models/Producto.php';
include_once '../../app/models/Proveedor.php';
include_once '../../app/core/Auth.php';

session_start();

// --- ACTIVAR ESCUDO DE SEGURIDAD ---
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    Auth::validateCsrfToken();
}
// -----------------------------------

$database = new Database();
$db = $database->getConnection();
$movimiento = new Movimiento($db);
$request_method = $_SERVER["REQUEST_METHOD"];
$action = isset($_GET['action']) ? $_GET['action'] : '';

switch ($request_method) {
    case 'GET':
        if ($action == 'get_productos') {
            $producto = new Producto($db);
            $stmt = $producto->leer();
            $productos_arr = [];
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) { $productos_arr[] = $row; }
            echo json_encode($productos_arr);
            break;
        }
        if ($action == 'get_proveedores') {
            $proveedor = new Proveedor($db);
            $stmt = $proveedor->leer();
            $proveedores_arr = [];
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                if ($row['estado'] == 'Activo') $proveedores_arr[] = $row;
            }
            echo json_encode($proveedores_arr);
            break;
        }

        if (!Auth::check('inventario', 'ver')) { http_response_code(403); echo json_encode(["message" => "Acceso denegado."]); break; }
        
        $stmt = $movimiento->leer();
        $movimientos_arr = ["data" => []];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $movimientos_arr["data"][] = $row;
        }
        http_response_code(200);
        echo json_encode($movimientos_arr);
        break;

    case 'POST':
        if (!Auth::check('inventario', 'crear')) { http_response_code(403); echo json_encode(["message" => "Acceso denegado."]); break; }
        $data = json_decode(file_get_contents("php://input"), true);
        $data['id_usuario'] = $_SESSION['user_id'] ?? 0;

        $tipo_movimiento = $data['tipo_movimiento'] ?? 'entrada';

        if ($tipo_movimiento === 'entrada') {
            if ($movimiento->crearEntrada($data)) {
                http_response_code(201);
                echo json_encode(["message" => "Entrada de inventario registrada."]);
            } else {
                http_response_code(503);
                echo json_encode(["message" => "No se pudo registrar la entrada."]);
            }
        } elseif ($tipo_movimiento === 'salida') {
            try {
                if ($movimiento->crearSalida($data)) {
                    http_response_code(201);
                    echo json_encode(["message" => "Salida de inventario registrada."]);
                }
            } catch (Exception $e) {
                http_response_code(400);
                echo json_encode(["message" => $e->getMessage()]);
            }
        } elseif ($tipo_movimiento === 'ajuste') {
            if ($movimiento->crearAjuste($data)) {
                http_response_code(201);
                echo json_encode(["message" => "Ajuste de inventario registrado."]);
            } else {
                http_response_code(503);
                echo json_encode(["message" => "No se pudo registrar el ajuste."]);
            }
        } else {
            http_response_code(400);
            echo json_encode(["message" => "Tipo de movimiento no válido."]);
        }
        break;
}
?>