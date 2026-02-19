<?php
// app/controllers/CitaController.php
header("Content-Type: application/json; charset=UTF-8");

include_once '../../config/Database.php';
include_once '../../app/models/Cita.php';
include_once '../../app/core/Auth.php';

session_start();

// --- ACTIVAR ESCUDO DE SEGURIDAD ---
// Validamos el token CSRF para cualquier petición que no sea GET (POST, PUT, DELETE)
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    Auth::validateCsrfToken();
}
// -----------------------------------

$database = new Database();
$db = $database->getConnection();
$cita = new Cita($db);

$request_method = $_SERVER["REQUEST_METHOD"];
$action = isset($_GET['action']) ? $_GET['action'] : '';

switch ($request_method) {
    case 'GET':
    // --- NUEVO: Estadísticas para el Portal Médico ---
        if ($action == 'get_kpis_medico') {
            if (!isset($_SESSION['is_medico']) || !$_SESSION['is_medico']) {
                http_response_code(403); echo json_encode(["message" => "Acceso denegado."]); break;
            }
            
            $medicoId = $_SESSION['medico_id'];
            $hoy = date('Y-m-d');

            // Consulta optimizada para contar estados de hoy
            $query = "SELECT 
                        COUNT(*) as total,
                        SUM(CASE WHEN estado = 'Programada' THEN 1 ELSE 0 END) as pendientes,
                        SUM(CASE WHEN estado = 'Completada' THEN 1 ELSE 0 END) as atendidos
                      FROM citas 
                      WHERE id_medico = :medicoId AND fecha_cita = :hoy AND estado != 'Cancelada'";
            
            $stmt = $db->prepare($query);
            $stmt->execute([':medicoId' => $medicoId, ':hoy' => $hoy]);
            $data = $stmt->fetch(PDO::FETCH_ASSOC);
            
            echo json_encode([
                'total' => $data['total'] ?? 0,
                'pendientes' => $data['pendientes'] ?? 0,
                'atendidos' => $data['atendidos'] ?? 0
            ]);
            break;
        }
        // -------------------------------------------------
        if ($action == 'mis_citas') {
            if (isset($_SESSION['is_medico']) && $_SESSION['is_medico'] === true) {
                $medicoId = $_SESSION['medico_id'];
                $startDate = $_GET['start_date'] ?? null;
                $endDate = $_GET['end_date'] ?? null;
                // Nota: Para médicos, quizás quieras ver todas las categorías o filtrar también.
                // Por ahora se mantiene igual para no romper la vista "Mis Citas".
                $stmt = $cita->leer($startDate, $endDate, $medicoId); 
                $citas_arr = ["data" => []];
                while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    $row['hora_cita'] = !empty($row['hora_cita']) ? date("g:i A", strtotime($row['hora_cita'])) : '';
                    $citas_arr["data"][] = $row;
                }
                echo json_encode($citas_arr);
            } else {
                http_response_code(403);
                echo json_encode(["message" => "Acceso denegado."]);
            }
            break;
        }

        if ($action == 'get_servicios_citas') {
            // MODIFICACIÓN: Se acepta categoria_id
            $categoriaId = $_GET['categoria_id'] ?? null;
            $servicios = $cita->obtenerServiciosParaCitas($categoriaId);
            echo json_encode($servicios);
            break;
        }

        if (!Auth::check('citas', 'ver')) {
            http_response_code(403); echo json_encode(["message" => "Acceso denegado."]); break;
        }

        if ($action == 'pacientes') {
            $pacientes = $cita->obtenerPacientes();
            echo json_encode($pacientes);
        } elseif ($action == 'medicos' && !empty($_GET['especialidad_id'])) {
            $idMedicoActual = $_GET['medico_actual_id'] ?? null;
            $medicos = $cita->obtenerMedicosPorEspecialidad($_GET['especialidad_id'], $idMedicoActual);
            echo json_encode($medicos);
        } elseif ($action == 'get_todos_medicos') {
            $medicos = $cita->obtenerTodosLosMedicos();
            echo json_encode($medicos);
        } elseif (!empty($_GET['id'])) {
            $stmt = $cita->leerUno(intval($_GET['id']));
            if ($stmt) {
                echo json_encode($stmt);
            } else {
                http_response_code(404);
                echo json_encode(["message" => "Cita no encontrada."]);
            }
        } else {
            // MODIFICACIÓN: Captura de filtros para listado
            $startDate = $_GET['start_date'] ?? null;
            $endDate = $_GET['end_date'] ?? null;
            $medicoId = $_GET['medico_id'] ?? null;
            $categoriaId = $_GET['categoria_id'] ?? null; // Nuevo filtro
            
            $stmt = $cita->leer($startDate, $endDate, $medicoId, $categoriaId);
            $num = $stmt->rowCount();
            if ($num > 0) {
                $citas_arr = ["data" => []];
                while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    $row['hora_cita'] = !empty($row['hora_cita']) ? date("g:i A", strtotime($row['hora_cita'])) : '';
                    $citas_arr["data"][] = $row;
                }
                http_response_code(200);
                echo json_encode($citas_arr);
            } else {
                http_response_code(200);
                echo json_encode(["data" => []]);
            }
        }
        break;

    case 'POST':
        if (!Auth::check('citas', 'crear')) {
            http_response_code(403); echo json_encode(["message" => "Acceso denegado."]); break;
        }
        $data = json_decode(file_get_contents("php://input"), true);
        $newId = $cita->crear($data);
        if ($newId) {
            http_response_code(201);
            echo json_encode(["message" => "Cita fue creada.", "id" => $newId]);
        } else {
            http_response_code(503);
            echo json_encode(["message" => "No se pudo crear la cita."]);
        }
        break;

    case 'PUT':
        if ($action == 'toggle_notificacion') {
            if (!Auth::check('citas', 'editar')) {
                http_response_code(403); echo json_encode(["message" => "Acceso denegado."]); break;
            }
            $data = json_decode(file_get_contents("php://input"), true);
            if (isset($data['id']) && isset($data['notificado'])) {
                if ($cita->actualizarNotificacion($data['id'], $data['notificado'])) {
                    http_response_code(200);
                    echo json_encode(["message" => "Estado de notificación actualizado."]);
                } else {
                    http_response_code(503);
                    echo json_encode(["message" => "No se pudo actualizar."]);
                }
            }
            break;
        }

        if (!Auth::check('citas', 'editar')) {
            http_response_code(403); echo json_encode(["message" => "Acceso denegado."]); break;
        }
        $data = json_decode(file_get_contents("php://input"), true);
        $id = $data['id'];
        unset($data['id']);
        if ($cita->actualizar($id, $data)) {
            http_response_code(200);
            echo json_encode(["message" => "Cita fue actualizada."]);
        } else {
            http_response_code(503);
            echo json_encode(["message" => "No se pudo actualizar la cita."]);
        }
        break;

    case 'DELETE':
        if (!Auth::check('citas', 'borrar')) {
            http_response_code(403); echo json_encode(["message" => "Acceso denegado."]); break;
        }
        $data = json_decode(file_get_contents("php://input"), true);
        $id = $data['id'];
        if ($cita->eliminar($id)) {
            http_response_code(200);
            echo json_encode(["message" => "Cita fue eliminada."]);
        } else {
            http_response_code(503);
            echo json_encode(["message" => "No se pudo eliminar la cita."]);
        }
        break;
}
?>