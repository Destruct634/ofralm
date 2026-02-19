<?php
// app/controllers/HistorialController.php
header("Content-Type: application/json; charset=UTF-8");
include_once '../../config/Database.php';
include_once '../../app/models/Historial.php';
include_once '../../app/models/Cita.php';
include_once '../../app/core/Auth.php';

// Modelos existentes
include_once '../../app/models/HistorialRefraccion.php';
include_once '../../app/models/Diagnostico.php';
include_once '../../app/models/HistorialItem.php';
include_once '../../app/models/Medico.php';

// Incluir Modelo de Factura
include_once '../../app/models/Factura.php';

session_start();

// --- ACTIVAR ESCUDO DE SEGURIDAD ---
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    Auth::validateCsrfToken();
}
// -----------------------------------

$database = new Database();
$db = $database->getConnection();
$historial = new Historial($db);
$cita_model = new Cita($db); 

$refraccion_model = new HistorialRefraccion($db);
$diagnostico_model = new Diagnostico($db);
$item_model = new HistorialItem($db);
$medico_model = new Medico($db);

$factura_model = new Factura($db);

$request_method = $_SERVER["REQUEST_METHOD"];
$action = $_GET['action'] ?? null;

switch ($request_method) {
    case 'GET':
        if (!Auth::check('pacientes', 'ver')) {
            http_response_code(403); echo json_encode(["message" => "Acceso denegado."]); break;
        }
      
        if ($action === 'get_medicos_activos') {
            $stmt = $medico_model->leer(); 
            $medicos = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $medicos_activos = array_filter($medicos, function($m) { return $m['estado'] == 'Activo'; });
            $options = [];
            foreach ($medicos_activos as $medico) {
                $options[$medico['id']] = $medico['nombres'] . ' ' . $medico['apellidos'];
            }
            http_response_code(200);
            echo json_encode($options);
            break;
        }
        if (!empty($_GET['get_log_for_id'])) {
            $log_entries = $historial->leerLogPorHistorial($_GET['get_log_for_id']);
            http_response_code(200);
            echo json_encode($log_entries);
        }
        elseif (!empty($_GET['paciente_id'])) {
            $stmt = $historial->leerPorPaciente($_GET['paciente_id']);
            $num = $stmt->rowCount();
            if ($num > 0) {
                $historial_arr = [];
                while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    array_push($historial_arr, $row);
                }
                http_response_code(200);
                echo json_encode($historial_arr);
            } else {
                http_response_code(200);
                echo json_encode([]);
            }
        }
        elseif(!empty($_GET['check_cita_id'])) {
            if ($historial->existePorCita($_GET['check_cita_id'])) {
                echo json_encode(['existe' => true]);
            } 
            else {
                echo json_encode(['existe' => false]);
            }
        }
        elseif (!empty($_GET['id'])) {
            $id_historial_buscado = intval($_GET['id']);
            $entry = $historial->leerUno($id_historial_buscado);
            if ($entry) {
                $entry_refraccion = $refraccion_model->leerUnoPorHistorial($id_historial_buscado);
                if ($entry_refraccion) $entry['refraccion'] = $entry_refraccion;
                $entry_diagnosticos = $historial->leerDiagnosticos($id_historial_buscado);
                if ($entry_diagnosticos) $entry['diagnosticos'] = $entry_diagnosticos;
                $entry_items = $item_model->leerPorHistorial($id_historial_buscado);
                if ($entry_items) $entry['items_consulta'] = $entry_items;
                http_response_code(200);
                echo json_encode($entry);
            } else {
                http_response_code(404);
                echo json_encode(['message' => 'Entrada no encontrada.']);
            }
        }
        break;

    case 'POST':
        if (!Auth::check('pacientes', 'editar')) {
            http_response_code(403); echo json_encode(["message" => "Acceso denegado."]); break;
        }
        $data = json_decode(file_get_contents("php://input"), true);
        
        $post_action = $data['action'] ?? null; 

        if ($post_action === 'crear_visita_rapida') {
            $id_paciente = $data['id_paciente'] ?? null;
            $id_medico = $data['id_medico'] ?? null;
            if (!$id_paciente || !$id_medico) {
                http_response_code(400);
                echo json_encode(["message" => "Faltan datos (paciente o médico)."]);
                break;
            }
            try {
                $nuevo_cita_id = $cita_model->crearVisitaRapida($id_paciente, $id_medico);
                if ($nuevo_cita_id) {
                    http_response_code(201);
                    echo json_encode([
                        "message" => "Cita rápida creada.",
                        "cita_id" => $nuevo_cita_id,
                        "paciente_id" => $id_paciente,
                        "medico_id" => $id_medico
                    ]);
                } else {
                    throw new Exception("No se pudo crear la cita rápida en la base de datos.");
                }
            } catch (Exception $e) {
                http_response_code(503);
                echo json_encode(["message" => $e->getMessage()]);
            }
            break;
        }

        
        // --- Lógica POST para guardar historial Y FACTURA ---
        if (
            !empty($data['id_cita']) && !empty($data['id_paciente']) &&
            !empty($data['id_medico']) && isset($data['tratamiento'])
        ) {
            $db->beginTransaction();
            try {
                // 1. Crear la entrada de historial principal
                if (!$historial->crear($data)) {
                    throw new Exception("No se pudo crear el historial. Es posible que ya exista uno para esta cita.");
                }
                $nuevo_historial_id = $db->lastInsertId();

                // 2. Crear Refracción
                if (isset($data['refraccion']) && $nuevo_historial_id) {
                    $refraccion_model->crear($nuevo_historial_id, $data['refraccion']);
                }
                
                // 3. Crear Diagnósticos
                if (isset($data['diagnosticos_ids']) && is_array($data['diagnosticos_ids']) && $nuevo_historial_id) {
                    $query_diag = "INSERT INTO historial_diagnosticos (id_historial, id_diagnostico) VALUES (:id_historial, :id_diagnostico)";
                    $stmt_diag = $db->prepare($query_diag);
                    foreach ($data['diagnosticos_ids'] as $diag_item) {
                        $diag_id = $diagnostico_model->encontrarOCrear($diag_item);
                        if ($diag_id) {
                            $stmt_diag->execute(['id_historial' => $nuevo_historial_id, 'id_diagnostico' => $diag_id]);
                        }
                    }
                }
                
                // 4. Crear Items
                if (isset($data['items_consulta']) && is_array($data['items_consulta']) && $nuevo_historial_id) {
                    foreach ($data['items_consulta'] as $item) {
                        $item_model->crear($nuevo_historial_id, $item);
                    }
                }

                // 5. Marcar la cita como completada
                $cita_model->marcarComoCompletada($data['id_cita']);
                
                // 6. GENERAR LA FACTURA
                $mensaje_factura = "";
                if (isset($data['items_consulta']) && is_array($data['items_consulta']) && count($data['items_consulta']) > 0) {
                    
                    $factura_data = prepararDatosFactura($data, $db, $_SESSION['user_id']);
                    $nueva_factura_id = $factura_model->crearDesdeHistorial($factura_data);
                    
                    if ($nueva_factura_id) {
                        $cita_model->marcarComoFacturada($data['id_cita']);
                        $mensaje_factura = " y se generó la factura borrador.";
                    } else {
                        throw new Exception("Error al crear la factura borrador.");
                    }
                }
                
                $db->commit();
                http_response_code(201);
                echo json_encode(["message" => "Historial guardado" . $mensaje_factura]);

            } catch (Exception $e) {
                $db->rollBack();
                http_response_code(503);
                echo json_encode(["message" => $e->getMessage()]);
            }
        } else {
            http_response_code(400);
            echo json_encode(["message" => "Datos incompletos para guardar historial."]);
        }
        break;

    case 'PUT':
        if (!Auth::check('pacientes', 'editar')) {
            http_response_code(403); echo json_encode(["message" => "Acceso denegado."]); break;
        }
        $data = json_decode(file_get_contents("php://input"), true);
        $id = $data['id'] ?? null;
        $id_usuario = $_SESSION['user_id'] ?? 0;
        if ($id && $id_usuario) {
            $db->beginTransaction();
            try {
                if (!$historial->actualizar($id, $data, $id_usuario)) {
                    throw new Exception("No se pudo actualizar la entrada del historial.");
                }
                if (isset($data['refraccion'])) {
                    $refraccion_model->actualizar($id, $data['refraccion']);
                }
                $stmt_delete = $db->prepare("DELETE FROM historial_diagnosticos WHERE id_historial = ?");
                $stmt_delete->execute([$id]);
                if (isset($data['diagnosticos_ids']) && is_array($data['diagnosticos_ids'])) {
                    $query_diag = "INSERT INTO historial_diagnosticos (id_historial, id_diagnostico) VALUES (:id_historial, :id_diagnostico)";
                    $stmt_diag = $db->prepare($query_diag);
                    foreach ($data['diagnosticos_ids'] as $diag_item) {
                        $diag_id = $diagnostico_model->encontrarOCrear($diag_item);
                        if ($diag_id) {
                            $stmt_diag->execute(['id_historial' => $id, 'id_diagnostico' => $diag_id]);
                        }
                    }
                }
                $item_model->eliminarPorHistorial($id);
                if (isset($data['items_consulta']) && is_array($data['items_consulta'])) {
                    foreach ($data['items_consulta'] as $item) {
                        $item_model->crear($id, $item);
                    }
                }
                $db->commit();
                http_response_code(200);
                echo json_encode(["message" => "Entrada de historial actualizada."]);
            } catch (Exception $e) {
                $db->rollBack();
                http_response_code(503);
                echo json_encode(["message" => $e->getMessage() . " en " . $e->getFile() . ":" . $e->getLine()]);
            }
        } else {
            http_response_code(400);
            echo json_encode(["message" => "Datos incompletos (ID o Usuario)."]);
        }
        break;
}

// --- FUNCIÓN HELPER CORREGIDA ---
// Ahora busca la fecha de la cita original
function prepararDatosFactura($data, $db, $id_usuario) {
    
    $stmt_isv = $db->prepare("SELECT id, porcentaje FROM tipos_isv");
    $stmt_isv->execute();
    $isv_map = $stmt_isv->fetchAll(PDO::FETCH_KEY_PAIR);

    $total_subtotal = 0;
    $total_isv = 0;
    $detalle_factura = [];

    foreach ($data['items_consulta'] as $item) {
        $cantidad = (int)($item['cantidad'] ?? 0);
        $precio = (float)($item['precio'] ?? 0);
        $descuento = (float)($item['descuento'] ?? 0);
        $id_isv = (int)($item['id_isv'] ?? 1);
        
        $subtotal_item = ($precio * $cantidad) - $descuento;
        $porcentaje_isv = (float)($isv_map[$id_isv] ?? 0);
        $isv_item = $subtotal_item * ($porcentaje_isv / 100);

        $total_subtotal += $subtotal_item;
        $total_isv += $isv_item;

        $detalle_factura[] = [
            'tipo' => $item['tipo'],
            'id' => $item['id_item'],
            'descripcion' => $item['descripcion'],
            'cantidad' => $cantidad,
            'precio' => $precio,
            'descuento' => $descuento,
            'id_isv' => $id_isv
        ];
    }
    
    $total_factura = $total_subtotal + $total_isv;

    // --- CORRECCIÓN: Obtener fecha de la CITA, no la fecha actual ---
    $fecha_para_db = date('Y-m-d H:i:s'); // Fallback fecha actual

    if (!empty($data['id_cita'])) {
        $stmt_cita = $db->prepare("SELECT fecha_cita, hora_cita FROM citas WHERE id = ?");
        $stmt_cita->execute([$data['id_cita']]);
        $info_cita = $stmt_cita->fetch(PDO::FETCH_ASSOC);
        if ($info_cita) {
            // Combinamos fecha y hora de la cita
            $hora = !empty($info_cita['hora_cita']) ? $info_cita['hora_cita'] : date('H:i:s');
            $fecha_para_db = $info_cita['fecha_cita'] . ' ' . $hora;
        }
    }
    // ---------------------------------------------------------------

    $id_medico = !empty($data['id_medico']) ? $data['id_medico'] : null;
    $id_tecnico = null; 

    return [
        'id_paciente' => $data['id_paciente'],
        'id_medico' => $id_medico,
        'id_tecnico' => $id_tecnico,
        'fecha_emision' => $fecha_para_db,
        'subtotal' => $total_subtotal,
        'isv_total' => $total_isv,
        'descuento_total' => 0,
        'total' => $total_factura,
        'id_usuario' => $id_usuario,
        'detalle' => $detalle_factura
    ];
}
?>