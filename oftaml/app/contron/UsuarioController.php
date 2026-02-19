<?php
header("Content-Type: application/json; charset=UTF-8");
include_once '../../config/Database.php';
include_once '../../app/models/Usuario.php';
include_once '../../app/models/Grupo.php';
include_once '../../app/models/Medico.php';
include_once '../../app/core/Auth.php';

session_start();

// --- ACTIVAR ESCUDO DE SEGURIDAD ---
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    Auth::validateCsrfToken();
}
// -----------------------------------

$database = new Database();
$db = $database->getConnection();
$usuario = new Usuario($db);
$request_method = $_SERVER["REQUEST_METHOD"];
$action = isset($_GET['action']) ? $_GET['action'] : '';

switch ($request_method) {
    case 'GET':
        if ($action == 'get_tecnicos') {
            $query = "SELECT id, nombre_completo FROM usuarios WHERE id_grupo = 4 AND estado = 'Activo'";
            $stmt = $db->prepare($query);
            $stmt->execute();
            $tecnicos = $stmt->fetchAll(PDO::FETCH_ASSOC);
            echo json_encode($tecnicos);
            break;
        }

        if (!Auth::check('usuarios', 'ver')) { http_response_code(403); echo json_encode(["message" => "Acceso denegado."]); break; }
        if ($action == 'get_grupos') {
            $grupo = new Grupo($db);
            $stmt = $grupo->leer();
            $grupos_arr = [];
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) { $grupos_arr[] = $row; }
            echo json_encode($grupos_arr);
        } elseif (!empty($_GET['id'])) {
            $user = $usuario->leerUno(intval($_GET['id']));
            if($user){ echo json_encode($user); } 
            else { http_response_code(404); echo json_encode(["message" => "Usuario no encontrado."]); }
        } else {
            $stmt = $usuario->leer();
            $usuarios_arr = ["data" => []];
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) { $usuarios_arr["data"][] = $row; }
            http_response_code(200);
            echo json_encode($usuarios_arr);
        }
        break;

    case 'POST':
        if (!Auth::check('usuarios', 'crear')) { http_response_code(403); echo json_encode(["message" => "Acceso denegado."]); break; }
        $data = json_decode(file_get_contents("php://input"), true);
        try {
            $db->beginTransaction();
            if (!$usuario->crear($data)) { throw new Exception("No se pudo crear el usuario."); }
            $id_usuario = $db->lastInsertId();
            if (!empty($data['es_medico'])) {
                $medico = new Medico($db);
                $data_medico = [
                    'nombre_completo' => $data['nombre_completo'], 
                    'id_especialidad' => $data['id_especialidad'],
                    'telefono' => $data['telefono'], 
                    'email' => $data['email_medico'],
                    'estado' => $data['estado']
                ];
                if (!$medico->guardarDesdeUsuario($id_usuario, $data_medico)) { throw new Exception("Usuario creado, pero no se pudo crear el perfil de médico.");}
            }
            $db->commit();
            http_response_code(201);
            echo json_encode(["message" => "Usuario creado exitosamente."]);
        } 
        catch (Exception $e) {
            $db->rollBack();
            http_response_code(503);
            echo json_encode(["message" => $e->getMessage()]);
        }
        break;

    case 'PUT':
        if (!Auth::check('usuarios', 'editar')) { http_response_code(403); echo json_encode(["message" => "Acceso denegado."]); break; }
        $data = json_decode(file_get_contents("php://input"), true);
        $id = $data['id'];
        unset($data['id']);
        try {
            $db->beginTransaction();
            if (!$usuario->actualizar($id, $data)) { throw new Exception("No se pudo actualizar el usuario.");}
            $medico = new Medico($db);
            if (!empty($data['es_medico'])) {
                $data_medico = [
                    'nombre_completo' => $data['nombre_completo'], 
                    'id_especialidad' => $data['id_especialidad'],
                    'telefono' => $data['telefono'], 
                    'email' => $data['email_medico'],
                    'estado' => $data['estado']
                ];
                if (!$medico->guardarDesdeUsuario($id, $data_medico)) { throw new Exception("Usuario actualizado, pero no se pudo sincronizar el perfil de médico.");}
            } else {
                $medico->eliminarPorUsuario($id);
            }
            $db->commit();
            http_response_code(200);
            echo json_encode(["message" => "Usuario actualizado exitosamente."]);
        } catch (Exception $e) {
            $db->rollBack();
            http_response_code(503);
            echo json_encode(["message" => $e->getMessage()]);
        }
        break;

    case 'DELETE':
        if (!Auth::check('usuarios', 'borrar')) { http_response_code(403); echo json_encode(["message" => "Acceso denegado."]); break; }
        $data = json_decode(file_get_contents("php://input"), true);
        if ($data['id'] == 1) { http_response_code(403); echo json_encode(["message" => "El usuario administrador no puede ser eliminado."]); break; }
        if ($usuario->eliminar($data['id'])) { http_response_code(200); echo json_encode(["message" => "Usuario eliminado."]); } 
        else { http_response_code(503); echo json_encode(["message" => "No se pudo eliminar el usuario."]); }
        break;
}
?>