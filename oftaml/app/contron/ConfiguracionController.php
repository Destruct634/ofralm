<?php
header("Content-Type: application/json; charset=UTF-8");
session_start();
include_once '../../config/Database.php';
include_once '../../app/models/Configuracion.php';
include_once '../../app/core/Auth.php';

// --- ACTIVAR ESCUDO DE SEGURIDAD ---
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    Auth::validateCsrfToken();
}
// -----------------------------------

if (!Auth::check('configuracion', 'ver')) {
    http_response_code(403);
    echo json_encode(["message" => "Acceso denegado."]);
    exit();
}

$database = new Database();
$db = $database->getConnection();
$config = new Configuracion($db);
$request_method = $_SERVER["REQUEST_METHOD"];
$action = $_GET['action'] ?? null;

// --- MÓDULO DE RESPALDO (GET) ---
// Nota: Los GET no llevan CSRF token usualmente, pero verificamos permisos estrictos
if ($request_method === 'GET' && $action === 'backup_db') {
    if (!Auth::check('configuracion', 'editar')) {
        http_response_code(403); exit;
    }
    exportarBaseDatos($db);
    exit;
}

// --- MÓDULO DE RESTAURACIÓN (POST) ---
if ($request_method === 'POST' && $action === 'restore_db') {
    if (!Auth::check('configuracion', 'editar')) {
        http_response_code(403); echo json_encode(["message" => "Acceso denegado."]); exit;
    }
    importarBaseDatos($db);
    exit;
}

// Path for login customization JSON
$login_custom_file = __DIR__ . '/../../config/login_custom.json';
if (!file_exists($login_custom_file)) {
    if (!is_dir(dirname($login_custom_file))) {
        mkdir(dirname($login_custom_file), 0755, true);
    }
    file_put_contents($login_custom_file, json_encode(new stdClass()));
}

switch ($request_method) {
    case 'GET':
        $data = $config->leer();
        $login_custom = json_decode(@file_get_contents($login_custom_file), true);
        if (!is_array($login_custom)) $login_custom = [];
        $data['login_background_color'] = isset($login_custom['color']) ? $login_custom['color'] : null;
        $data['login_background_image'] = isset($login_custom['image']) ? $login_custom['image'] : null;
        http_response_code(200);
        echo json_encode($data);
        break;
    
    case 'POST':
        if (!Auth::check('configuracion', 'editar')) {
            http_response_code(403);
            echo json_encode(["message" => "No tiene permiso para editar la configuración."]);
            break;
        }

        $logo_filename = null;
        if (isset($_FILES['logo']) && isset($_FILES['logo']['error']) && $_FILES['logo']['error'] == 0) {
            $upload_dir = '../../public/uploads/logos/';
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0755, true);
            }
            // Validación básica de extensión
            $ext = strtolower(pathinfo($_FILES['logo']['name'], PATHINFO_EXTENSION));
            if (in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp'])) {
                $logo_filename = "logo." . $ext;
                move_uploaded_file($_FILES['logo']['tmp_name'], $upload_dir . $logo_filename);
            }
        }
        
        $login_bg_filename = null;
        if (isset($_FILES['login_background_image']) && isset($_FILES['login_background_image']['error']) && $_FILES['login_background_image']['error'] === UPLOAD_ERR_OK) {
            $upload_dir_login = '../../public/uploads/';
            if (!is_dir($upload_dir_login)) {
                mkdir($upload_dir_login, 0755, true);
            }
            $ext = strtolower(pathinfo($_FILES['login_background_image']['name'], PATHINFO_EXTENSION));
            if (in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp'])) {
                $login_bg_filename = "login_bg." . $ext;
                move_uploaded_file($_FILES['login_background_image']['tmp_name'], $upload_dir_login . $login_bg_filename);
            }
        }

        $login_custom = json_decode(@file_get_contents($login_custom_file), true);
        if (!is_array($login_custom)) $login_custom = [];

        if (isset($_POST['login_background_color'])) {
            $color = trim($_POST['login_background_color']);
            $login_custom['color'] = ($color === '') ? null : $color;
        }

        if ($login_bg_filename) {
            $login_custom['image'] = 'public/uploads/' . $login_bg_filename;
        }

        if (isset($_POST['login_bg_remove']) && ($_POST['login_bg_remove'] === '1' || $_POST['login_bg_remove'] == 1)) {
            if (isset($login_custom['image']) && $login_custom['image']) {
                $file_to_remove = __DIR__ . '/../../' . $login_custom['image'];
                if (file_exists($file_to_remove)) {
                    @unlink($file_to_remove);
                }
            }
            $login_custom['image'] = null;
        }

        @file_put_contents($login_custom_file, json_encode($login_custom));

        if ($config->actualizar($_POST, $logo_filename)) {
            // Actualizar sesión inmediatamente si cambió la zona horaria
            if (isset($_POST['zona_horaria'])) {
                $_SESSION['time_zone'] = $_POST['zona_horaria'];
            }
            http_response_code(200);
            echo json_encode(["message" => "Configuración guardada exitosamente."]);
        } else {
            http_response_code(503);
            echo json_encode(["message" => "No se pudo guardar la configuración."]);
        }
        break;

    default:
        http_response_code(405);
        echo json_encode(["message" => "Método no soportado."]);
        break;
}

// --- FUNCIONES HELPER OPTIMIZADAS (STREAMING) ---

function exportarBaseDatos($db) {
    $filename = 'backup_hospital_' . date('Y-m-d_H-i') . '.sql';
    header('Content-Type: application/octet-stream');
    header("Content-Transfer-Encoding: Binary");
    header("Content-disposition: attachment; filename=\"" . $filename . "\"");

    // Deshabilitar buffering para evitar consumo de RAM
    if (ob_get_level()) ob_end_clean();

    $output = fopen('php://output', 'w');

    fwrite($output, "-- RESPALDO DE BASE DE DATOS HOSPITAL_DB \n");
    fwrite($output, "-- Fecha: " . date('Y-m-d H:i:s') . "\n");
    fwrite($output, "-- Generado por el Sistema ERP\n\n");
    fwrite($output, "SET FOREIGN_KEY_CHECKS=0;\n\n");

    $tables = [];
    $stmt = $db->query("SHOW TABLES");
    while ($row = $stmt->fetch(PDO::FETCH_NUM)) {
        $tables[] = $row[0];
    }

    foreach ($tables as $table) {
        // Estructura
        $stmt = $db->query("SHOW CREATE TABLE `$table`");
        $row = $stmt->fetch(PDO::FETCH_NUM);
        fwrite($output, "DROP TABLE IF EXISTS `$table`;\n");
        fwrite($output, $row[1] . ";\n\n");

        // Datos (usando un cursor para no cargar todo en RAM)
        $stmt = $db->query("SELECT * FROM `$table`");
        $columnCount = $stmt->columnCount();

        while ($row = $stmt->fetch(PDO::FETCH_NUM)) {
            $line = "INSERT INTO `$table` VALUES(";
            for ($j = 0; $j < $columnCount; $j++) {
                $row[$j] = addslashes($row[$j]);
                $row[$j] = str_replace("\n", "\\n", $row[$j]);
                if (isset($row[$j])) {
                    $line .= '"' . $row[$j] . '"';
                } else {
                    $line .= 'NULL';
                }
                if ($j < ($columnCount - 1)) {
                    $line .= ',';
                }
            }
            $line .= ");\n";
            fwrite($output, $line);
        }
        fwrite($output, "\n");
    }
    
    fwrite($output, "SET FOREIGN_KEY_CHECKS=1;");
    fclose($output);
    exit;
}

function importarBaseDatos($db) {
    // Aumentar límites solo para esta operación
    ini_set('memory_limit', '256M');
    set_time_limit(300);

    if (!isset($_FILES['archivo_sql']) || $_FILES['archivo_sql']['error'] != UPLOAD_ERR_OK) {
        http_response_code(400);
        echo json_encode(["message" => "Error al subir el archivo."]);
        return;
    }

    $filename = $_FILES['archivo_sql']['tmp_name'];
    
    // Validar extensión real (no solo mime type)
    $ext = strtolower(pathinfo($_FILES['archivo_sql']['name'], PATHINFO_EXTENSION));
    if ($ext !== 'sql') {
        http_response_code(400);
        echo json_encode(["message" => "Solo se permiten archivos .sql"]);
        return;
    }
    
    $lines = file($filename);
    if (!$lines) {
        http_response_code(500);
        echo json_encode(["message" => "No se pudo leer el archivo."]);
        return;
    }

    $db->exec("SET FOREIGN_KEY_CHECKS=0");

    $templine = '';
    
    try {
        $db->beginTransaction();
        
        foreach ($lines as $line) {
            if (substr($line, 0, 2) == '--' || $line == '') continue;

            $templine .= $line;
            
            if (substr(trim($line), -1, 1) == ';') {
                try {
                    $db->exec($templine);
                } catch (Exception $e) {
                    // Log error but continue
                }
                $templine = '';
            }
        }

        if ($db->inTransaction()) {
            $db->commit();
        }

        $db->exec("SET FOREIGN_KEY_CHECKS=1");
        
        session_destroy(); // Cerrar sesión para obligar re-login con datos nuevos
        http_response_code(200);
        echo json_encode(["message" => "Restauración completada. Sesión cerrada."]);

    } catch (Exception $e) {
        if ($db->inTransaction()) {
            $db->rollBack();
        }
        $db->exec("SET FOREIGN_KEY_CHECKS=1");
        http_response_code(500);
        echo json_encode(["message" => "Error crítico al procesar SQL: " . $e->getMessage()]);
    }
}
?>