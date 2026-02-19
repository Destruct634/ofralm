<?php
header("Content-Type: application/json; charset=UTF-8");
include_once '../../config/Database.php';
include_once '../../app/models/ReporteVenta.php';
include_once '../../app/core/Auth.php';

session_start();

// CORRECCIÓN: Validar permiso específico del reporte
if (!Auth::check('reportes_ventas', 'ver')) {
    http_response_code(403);
    echo json_encode(["message" => "Acceso denegado."]);
    exit();
}

$database = new Database();
$db = $database->getConnection();
$reporte = new ReporteVenta($db);

$action = $_GET['action'] ?? '';

if ($action === 'generar') {
    $filtros = [
        'fecha_inicio' => $_GET['fecha_inicio'] ?? date('Y-m-d'),
        'fecha_fin' => $_GET['fecha_fin'] ?? date('Y-m-d'),
        'tipo' => $_GET['tipo'] ?? 'Todos',
        'id_medico' => $_GET['id_medico'] ?? null,
        'id_tecnico' => $_GET['id_tecnico'] ?? null
    ];

    $data = $reporte->generarReporte($filtros);
    
    // Calcular totales para enviar resumen rápido
    $total_ventas = 0;
    foreach ($data as $row) {
        $total_ventas += (float)$row['subtotal_item'];
    }

    echo json_encode([
        'data' => $data,
        'resumen' => [
            'total_venta' => $total_ventas,
            'cantidad_items' => count($data)
        ]
    ]);
}
?>