<?php
// app/controllers/ArchivoController.php
header("Content-Type: application/json; charset=UTF-8");
include_once '../../config/Database.php';
include_once '../../app/models/Archivo.php';
include_once '../../app/models/Paciente.php';
include_once '../../app/core/Auth.php';

session_start();

// --- ACTIVAR ESCUDO DE SEGURIDAD ---
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    Auth::validateCsrfToken();
}
// -----------------------------------

$database = new Database();
$db = $database->getConnection();
$archivo = new Archivo($db);
$paciente_model = new Paciente($db); // Instanciamos el modelo de Paciente

$request_method = $_SERVER["REQUEST_METHOD"];
$action = isset($_GET['action']) ? $_GET['action'] : '';

function sanitizarNombreCarpeta($nombre) {
    $nombre = str_replace([' ', ','], '_', $nombre);
    // Eliminar acentos y caracteres especiales
    $nombre = iconv('UTF-8', 'ASCII//TRANSLIT', $nombre);
    // Eliminar cualquier caracter que no sea letra, número, guion bajo o guion medio
    $nombre = preg_replace('/[^A-Za-z0-9_-]/', '', $nombre);
    // Eliminar guiones bajos duplicados
    $nombre = preg_replace('/_+/', '_', $nombre);
    return $nombre;
}

switch ($request_method) {
    case 'GET':
        if (!empty($_GET['historial_id'])) {
            if (!Auth::check('historial', 'ver')) {
                http_response_code(403); echo json_encode(["message" => "Acceso denegado."]); break;
            }
            $archivos = $archivo->leerPorHistorial($_GET['historial_id']);
            echo json_encode($archivos);
        }
        elseif ($action == 'archivos_paciente' && !empty($_GET['paciente_id'])) {
            if (!Auth::check('historial', 'ver')) {
                http_response_code(403); echo json_encode(["message" => "Acceso denegado."]); break;
            }

            $limit = 10;
            $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
            $offset = ($page - 1) * $limit;
            $id_paciente = $_GET['paciente_id'];

            $archivos = $archivo->leerPorPacientePaginado($id_paciente, $limit, $offset);
            $total_records = $archivo->contarPorPaciente($id_paciente);
            
            echo json_encode([
                'data' => $archivos,
                'total_pages' => ceil($total_records / $limit),
                'current_page' => $page
            ]);
        }
        break;

    case 'POST':
        if (!Auth::check('historial', 'editar')) {
            http_response_code(403); echo json_encode(["message" => "Acceso denegado."]); break;
        }
        
        $id_paciente = $_POST['id_paciente'] ?? 0;
        $id_historial = $_POST['id_historial'] ?? null;
        $id_usuario_subida = $_SESSION['user_id'] ?? 0;
        $id_categoria = $_POST['id_categoria'] ?? null;

        if ($id_paciente && $id_usuario_subida && !empty($_FILES['archivos'])) {
            
            // --- INICIO MODIFICACIÓN: Nombre de carpeta personalizado ---
            // Obtenemos datos del paciente para usar su nombre en la carpeta
            $info_paciente = $paciente_model->leerUno($id_paciente);
            
            if ($info_paciente) {
                // Concatenamos Nombres y Apellidos
                $nombre_completo = $info_paciente['nombres'] . '_' . $info_paciente['apellidos'];
                // Sanitizamos para evitar caracteres raros en el sistema de archivos
                $nombre_sanitizado = sanitizarNombreCarpeta($nombre_completo);
                // Formato: paciente_ID_Nombre_Apellido
                $nombre_carpeta_segura = "paciente_" . intval($id_paciente) . "_" . $nombre_sanitizado;
            } else {
                // Fallback por si no encuentra al paciente
                $nombre_carpeta_segura = "paciente_" . intval($id_paciente);
            }
            // --- FIN MODIFICACIÓN ---

            $target_dir = "../../public/uploads/historial/" . $nombre_carpeta_segura . "/";
            $ruta_relativa = "uploads/historial/" . $nombre_carpeta_segura . "/";

            if (!is_dir($target_dir)) {
                mkdir($target_dir, 0755, true);
                // MANTENIDO: Generar index.html vacío por seguridad
                file_put_contents($target_dir . 'index.html', ''); 
            }

            $subidos = 0;
            foreach ($_FILES['archivos']['name'] as $key => $name) {
                if ($_FILES['archivos']['error'][$key] === UPLOAD_ERR_OK) {
                    $nombre_original = basename($name);
                    $extension = strtolower(pathinfo($nombre_original, PATHINFO_EXTENSION));
                    
                    // Filtro de extensiones peligrosas
                    $denied_ext = ['php', 'php5', 'exe', 'bat', 'sh', 'js', 'html', 'htm'];
                    if (in_array($extension, $denied_ext)) continue;

                    // Nombre único para el archivo
                    $nombre_guardado = uniqid('doc_', true) . '.' . $extension;
                    $target_file = $target_dir . $nombre_guardado;

                    if (move_uploaded_file($_FILES['archivos']['tmp_name'][$key], $target_file)) {
                        if ($archivo->crear($id_paciente, $id_historial, $nombre_original, $nombre_guardado, $ruta_relativa, $id_usuario_subida, $id_categoria)) {
                            $subidos++;
                        }
                    }
                }
            }
            
            if ($subidos > 0) {
                http_response_code(201);
                echo json_encode(["message" => "$subidos archivos subidos exitosamente."]);
            } else {
                http_response_code(500); // O 400 dependiendo de si fue error de servidor o archivos inválidos
                echo json_encode(["message" => "No se pudo subir ningún archivo válido."]);
            }

        } else {
            http_response_code(400);
            echo json_encode(["message" => "Datos incompletos o sesión no válida."]);
        }
        break;

    case 'DELETE':
        if (!Auth::check('historial', 'editar')) {
            http_response_code(403); echo json_encode(["message" => "Acceso denegado."]); break;
        }
        $data = json_decode(file_get_contents("php://input"), true);
        $id_archivo = $data['id'] ?? null;
        
        if ($id_archivo) {
            $archivo_db = $archivo->leerUno($id_archivo);
            if ($archivo_db) {
                $ruta_completa = '../../public/' . $archivo_db['ruta_archivo'] . $archivo_db['nombre_guardado'];
                if (file_exists($ruta_completa)) {
                    unlink($ruta_completa);
                }
                if ($archivo->eliminar($id_archivo)) {
                    http_response_code(200);
                    echo json_encode(["message" => "Archivo eliminado."]);
                } else {
                    http_response_code(503);
                    echo json_encode(["message" => "No se pudo eliminar el registro de la base de datos."]);
                }
            } else {
                http_response_code(404);
                echo json_encode(["message" => "Archivo no encontrado en base de datos."]);
            }
        } else {
            http_response_code(400);
            echo json_encode(["message" => "ID de archivo no proporcionado."]);
        }
        break;
}
?>