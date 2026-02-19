<?php
// app/controllers/AjaxController.php

header("Content-Type: application/json; charset=UTF-8");
include_once '../../config/Database.php';
include_once '../../app/models/Diagnostico.php';
include_once '../../app/models/Paciente.php';
include_once '../../app/core/Auth.php';
include_once '../../app/models/HistorialRefraccion.php';

session_start();

if (!isset($_SESSION['user_id'])) {
    http_response_code(403);
    echo json_encode(['error' => 'Acceso denegado']);
    exit;
}

$database = new Database();
$db = $database->getConnection();

$diagnostico_model = new Diagnostico($db);
$paciente_model = new Paciente($db);

$action = $_GET['action'] ?? '';

switch ($action) {
    case 'buscarItems':
        buscarServiciosYProductos($db);
        break;

    case 'buscarDiagnosticos':
        buscarDiagnosticos($db, $diagnostico_model);
        break;

    case 'getTopDiagnosticos':
        $datos = $diagnostico_model->getTop10Diagnosticos();
        http_response_code(200);
        echo json_encode($datos);
        break;

    case 'getDistribucionPoblacion':
        $datos = $paciente_model->getDistribucionAntecedentes();
        http_response_code(200);
        echo json_encode($datos);
        break;

    case 'getDistribucionErrores':
        // CORRECCIÓN: Usar permiso de reportes clínicos
        if (!Auth::check('reportes_clinicos', 'ver')) {
            http_response_code(403);
            echo json_encode(["message" => "Acceso denegado."]);
            break;
        }
        
        $refraccion_model = new HistorialRefraccion($db);
        $data = $refraccion_model->obtenerDistribucionErrores();
        
        http_response_code(200);
        echo json_encode($data);
        break;

    default:
        http_response_code(400);
        echo json_encode(['error' => 'Acción no válida']);
        break;
}

function buscarServiciosYProductos($db) {
    $term = $_GET['term'] ?? '';
    if (strlen($term) < 2) {
        echo json_encode([]);
        exit;
    }
    $searchTerm = '%' . $term . '%';
    $results = [];

    $queryServicios = "SELECT id, nombre_servicio as nombre, precio_venta, id_isv, 'Servicio' as tipo
                       FROM servicios WHERE (nombre_servicio LIKE :term1 OR codigo LIKE :term2) LIMIT 5";
    $stmtServicios = $db->prepare($queryServicios);
    $stmtServicios->bindParam(':term1', $searchTerm);
    $stmtServicios->bindParam(':term2', $searchTerm);
    $stmtServicios->execute();
    while ($row = $stmtServicios->fetch(PDO::FETCH_ASSOC)) {
        $results[] = [
            'id' => 'S-' . $row['id'], 'id_item' => $row['id'], 'text' => $row['nombre'],
            'tipo' => $row['tipo'], 'precio_venta' => $row['precio_venta'], 'id_isv' => $row['id_isv']
        ];
    }

    $queryProductos = "SELECT id, nombre_producto as nombre, precio_venta, id_isv, 'Producto' as tipo
                       FROM productos WHERE (nombre_producto LIKE :term1 OR codigo LIKE :term2 OR codigo_barras LIKE :term3)
                       AND es_inventariable = 1 LIMIT 5";
    $stmtProductos = $db->prepare($queryProductos);
    $stmtProductos->bindParam(':term1', $searchTerm);
    $stmtProductos->bindParam(':term2', $searchTerm);
    $stmtProductos->bindParam(':term3', $searchTerm);
    $stmtProductos->execute();
    while ($row = $stmtProductos->fetch(PDO::FETCH_ASSOC)) {
        $results[] = [
            'id' => 'P-' . $row['id'], 'id_item' => $row['id'], 'text' => $row['nombre'],
            'tipo' => $row['tipo'], 'precio_venta' => $row['precio_venta'], 'id_isv' => $row['id_isv']
        ];
    }

    header('Content-Type: application/json');
    echo json_encode($results);
    exit;
}

function buscarDiagnosticos($db, $diagnostico_model) {
    $term = $_GET['term'] ?? '';
    if (strlen($term) < 2) {
        echo json_encode([]);
        exit;
    }
    $searchTerm = '%' . $term . '%';
    $results = [];
    $query = "SELECT id, descripcion, codigo FROM diagnosticos
              WHERE (descripcion LIKE :term1 OR codigo LIKE :term2) AND estado = 'Activo' LIMIT 10";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':term1', $searchTerm);
    $stmt->bindParam(':term2', $searchTerm);
    $stmt->execute();
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $text = $row['codigo'] ? "({$row['codigo']}) {$row['descripcion']}" : $row['descripcion'];
        $results[] = ['id' => $row['id'], 'text' => $text];
    }
    header('Content-Type: application/json');
    echo json_encode($results);
    exit;
}
?>