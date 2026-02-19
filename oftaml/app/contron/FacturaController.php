<?php
header("Content-Type: application/json; charset=UTF-8");
include_once '../../config/Database.php';
include_once '../../app/models/Factura.php';
include_once '../../app/models/Cita.php';
include_once '../../app/models/Producto.php';
include_once '../../app/models/Servicio.php';
include_once '../../app/models/Pago.php';
include_once '../../app/core/Auth.php';

session_start();

// --- ACTIVAR ESCUDO DE SEGURIDAD ---
// Bloquea cualquier intento de modificación externo
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    Auth::validateCsrfToken();
}
// -----------------------------------

$database = new Database();
$db = $database->getConnection();
$factura = new Factura($db);
$request_method = $_SERVER["REQUEST_METHOD"];
$action = isset($_GET['action']) ? $_GET['action'] : '';

switch ($request_method) {
    case 'GET':
        if ($action == 'search' && isset($_GET['term'])) {
            $resultados = $factura->search($_GET['term']);
            echo json_encode(['results' => $resultados]);
            break;
        }
        if ($action == 'cuentas_por_cobrar') {
            if (!Auth::check('facturacion', 'ver')) { http_response_code(403); echo json_encode(["message" => "Acceso denegado."]); break; }
            $stmt = $factura->leerCuentasPorCobrar();
            $cuentas_arr = ["data" => []];
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) { $cuentas_arr["data"][] = $row; }
            http_response_code(200);
            echo json_encode($cuentas_arr);
            break;
        }

        if ($action == 'get_cita_info' && !empty($_GET['cita_id'])) {
            $cita = new Cita($db);
            $cita_info = $cita->leerUno(intval($_GET['cita_id']));
            
            $stmt = $db->prepare("SELECT CONCAT(p.nombres, ' ', p.apellidos) as paciente_nombre, 
                                  m.id as id_medico_cita, CONCAT(m.nombres, ' ', m.apellidos) as medico_nombre_cita,
                                  s.nombre_servicio as motivo_detalle, s.precio_venta, s.id_isv
                                  FROM citas c 
                                  JOIN pacientes p ON c.id_paciente = p.id
                                  LEFT JOIN servicios s ON c.id_servicio = s.id
                                  LEFT JOIN medicos m ON c.id_medico = m.id 
                                  WHERE c.id = ?");
            $stmt->execute([intval($_GET['cita_id'])]);
            $extra_info = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($cita_info && $extra_info) {
                $cita_info = array_merge($cita_info, $extra_info);
            }
            
            echo json_encode($cita_info);
            break;
        }
        
        if ($action == 'search_servicios' && isset($_GET['term'])) {
            $servicio = new Servicio($db);
            $servicios = $servicio->search($_GET['term']);
            echo json_encode($servicios);
            break;
        }

        if (!Auth::check('facturacion', 'ver')) { http_response_code(403); echo json_encode(["message" => "Acceso denegado."]); break; }
        
        if (!empty($_GET['id'])) {
            $id_factura = intval($_GET['id']);
            $factura_data = $factura->leerUno($id_factura);
            $factura_detalle = $factura->leerDetalle($id_factura);
            $pago_model = new Pago($db);
            $pagos_detalle = $pago_model->leerPorFactura($id_factura);
            if ($factura_data) {
                $factura_data['detalle'] = $factura_detalle;
                $factura_data['pagos'] = $pagos_detalle;
                echo json_encode($factura_data);
            } else {
                http_response_code(404);
                echo json_encode(["message" => "Factura no encontrada."]);
            }
        } else {
            $startDate = $_GET['start_date'] ?? date('Y-m-d');
            $endDate = $_GET['end_date'] ?? date('Y-m-d');
            $estado = $_GET['estado'] ?? null;
            $stmt = $factura->leer($startDate, $endDate, $estado);
            $facturas_arr = ["data" => []];
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) { $facturas_arr["data"][] = $row; }
            http_response_code(200);
            echo json_encode($facturas_arr);
        }
        break;

    case 'POST':
        if (!Auth::check('facturacion', 'crear')) { http_response_code(403); echo json_encode(["message" => "Acceso denegado."]); break; }
        $data = json_decode(file_get_contents("php://input"), true);
        
        $data['id_usuario'] = $_SESSION['user_id'];
        $data['id_medico'] = !empty($data['id_medico']) ? $data['id_medico'] : null;
        $data['id_tecnico'] = !empty($data['id_tecnico']) ? $data['id_tecnico'] : null;

        if (!empty($data['fecha_emision'])) {
            $data['fecha_emision'] = $data['fecha_emision'] . ' ' . date('H:i:s');
        }

        if ($factura->crear($data)) {
            if (!empty($data['cita_id'])) {
                $cita = new Cita($db);
                $cita->marcarComoFacturada($data['cita_id']);
            }
            http_response_code(201);
            echo json_encode(["message" => "Factura creada como borrador."]);
        } else {
            http_response_code(503);
            echo json_encode(["message" => "No se pudo crear la factura."]);
        }
        break;

    case 'PUT':
        if (!Auth::check('facturacion', 'editar')) { http_response_code(403); echo json_encode(["message" => "Acceso denegado."]); break; }
        $data = json_decode(file_get_contents("php://input"), true);

        if ($action == 'cambiar_estado') {
            if ($factura->cambiarEstado($data['id_factura'], $data['estado'])) {
                http_response_code(200);
                echo json_encode(["message" => "Estado de la factura actualizado."]);
            } else {
                http_response_code(503);
                echo json_encode(["message" => "No se pudo actualizar el estado."]);
            }
        } else {
            $id_factura = $data['id'];
            unset($data['id']);
            
            $data['id_medico'] = !empty($data['id_medico']) ? $data['id_medico'] : null;
            $data['id_tecnico'] = !empty($data['id_tecnico']) ? $data['id_tecnico'] : null;
            
            if (!empty($data['fecha_emision'])) {
                $data['fecha_emision'] = $data['fecha_emision'] . ' ' . date('H:i:s');
            }

            if ($factura->actualizar($id_factura, $data)) {
                http_response_code(200);
                echo json_encode(["message" => "Factura actualizada."]);
            } else {
                http_response_code(503);
                echo json_encode(["message" => "No se pudo actualizar la factura."]);
            }
        }
        break;

    case 'DELETE':
        if (!Auth::check('facturacion', 'eliminar')) { http_response_code(403); echo json_encode(["message" => "Acceso denegado."]); break; }
        $data = json_decode(file_get_contents("php://input"), true);

        if ($factura->eliminar($data['id'])) {
            http_response_code(200);
            echo json_encode(["message" => "Factura eliminada."]);
        } else {
            http_response_code(503);
            echo json_encode(["message" => "No se pudo eliminar la factura."]);
        }
        break;

    default:
        http_response_code(405);
        echo json_encode(["message" => "Método no permitido."]);
        break;
}
?>