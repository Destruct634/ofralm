<?php
header("Content-Type: application/json; charset=UTF-8");
include_once '../../config/Database.php';
include_once '../../app/models/Producto.php';
include_once '../../app/core/Auth.php';

session_start();

// --- ACTIVAR ESCUDO DE SEGURIDAD ---
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    Auth::validateCsrfToken();
}
// -----------------------------------

$database = new Database();
$db = $database->getConnection();
$producto = new Producto($db);
$request_method = $_SERVER["REQUEST_METHOD"];
$action = isset($_GET['action']) ? $_GET['action'] : '';

switch ($request_method) {
    case 'GET':
        // Acción para la búsqueda con Select2
        if ($action == 'search' && isset($_GET['term'])) {
            $resultados = $producto->search($_GET['term']);
            echo json_encode($resultados);
            break;
        }
        if ($action == 'get_categorias') {
            $categorias = $producto->obtenerCategorias();
            echo json_encode($categorias);
            break;
        }
        if ($action == 'get_isv') {
            $tipos_isv = $producto->obtenerTiposIsv();
            echo json_encode($tipos_isv);
            break;
        }

        if (!Auth::check('productos', 'ver')) { http_response_code(403); echo json_encode(["message" => "Acceso denegado."]); break; }
        
        if (!empty($_GET['id'])) {
            $prod = $producto->leerUno(intval($_GET['id']));
            if($prod) {
                echo json_encode($prod);
            } else {
                http_response_code(404);
                echo json_encode(["message" => "Producto no encontrado."]);
            }
        } else {
            $stmt = $producto->leer();
            $productos_arr = ["data" => []];
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $productos_arr["data"][] = $row;
            }
            http_response_code(200);
            echo json_encode($productos_arr);
        }
        break;

    case 'POST':
        if (!Auth::check('productos', 'crear')) { http_response_code(403); echo json_encode(["message" => "Acceso denegado."]); break; }
        $data = json_decode(file_get_contents("php://input"), true);
        if ($producto->crear($data)) {
            http_response_code(201);
            echo json_encode(["message" => "Producto creado."]);
        } else {
            http_response_code(503);
            echo json_encode(["message" => "No se pudo crear el producto."]);
        }
        break;

    case 'PUT':
        if (!Auth::check('productos', 'editar')) { http_response_code(403); echo json_encode(["message" => "Acceso denegado."]); break; }
        $data = json_decode(file_get_contents("php://input"), true);
        $id = $data['id'];
        unset($data['id']);
        if ($producto->actualizar($id, $data)) {
            http_response_code(200);
            echo json_encode(["message" => "Producto actualizado."]);
        } else {
            http_response_code(503);
            echo json_encode(["message" => "No se pudo actualizar."]);
        }
        break;

    case 'DELETE':
        if (!Auth::check('productos', 'borrar')) { http_response_code(403); echo json_encode(["message" => "Acceso denegado."]); break; }
        $data = json_decode(file_get_contents("php://input"), true);
        if ($producto->eliminar($data['id'])) {
            http_response_code(200);
            echo json_encode(["message" => "Producto eliminado."]);
        } else {
            http_response_code(503);
            echo json_encode(["message" => "No se pudo eliminar."]);
        }
        break;
}
?>