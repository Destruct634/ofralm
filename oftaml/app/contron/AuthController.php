<?php
session_start();
include_once '../../config/Database.php';
include_once '../../app/models/Usuario.php';
include_once '../../app/models/Grupo.php';
include_once '../../app/models/Medico.php';
// Incluir Auth para poder generar el token
include_once '../../app/core/Auth.php';

$database = new Database();
$db = $database->getConnection();

$action = $_POST['action'] ?? '';

if ($action === 'login') {
    $usuario = new Usuario($db);
    $user = $usuario->login($_POST['usuario'], $_POST['password']);
    
    if ($user) {
        // 1. Guardar datos esenciales en la sesión
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_name'] = $user['nombre_completo'];
        $_SESSION['user_group_id'] = $user['id_grupo'];
        $_SESSION['loggedin'] = true;
        
        // --- NUEVO: Generar Token de Seguridad ---
        Auth::generateCsrfToken(); 
        // ----------------------------------------
        
        // 2. Cargar permisos para TODOS los usuarios que tengan un grupo
        if ($user['id_grupo']) {
            $grupo = new Grupo($db);
            $permisos_db = $grupo->obtenerPermisos($user['id_grupo']);
            $permisos_procesados = [];
            foreach ($permisos_db as $p) {
                $permisos_procesados[$p['nombre_modulo']] = [
                    'ver' => $p['ver'], 'crear' => $p['crear'],
                    'editar' => $p['editar'], 'borrar' => $p['borrar']
                ];
            }
            $_SESSION['permisos'] = $permisos_procesados;
        } else {
            $_SESSION['permisos'] = []; // Si no tiene grupo, no tiene permisos
        }

        // 3. Decidir a dónde redirigir al usuario
        $medico_model = new Medico($db);
        $medico_profile = $medico_model->leerPorUsuarioId($user['id']);

        if ($medico_profile) {
            // Si tiene un perfil de médico, va a su portal
            $_SESSION['is_medico'] = true;
            $_SESSION['medico_id'] = $medico_profile['id'];
            header("Location: ../../public/index.php?page=mis_citas");
        } else {
            // Si no, es un usuario administrativo y va al dashboard principal
            $_SESSION['is_medico'] = false;
            header("Location: ../../public/index.php?page=dashboard");
        }
        exit();

    } else {
        $_SESSION['error_login'] = "Usuario o contraseña incorrectos.";
        header("Location: ../../public/login.php");
        exit();
    }
}

if ($action === 'logout') {
    session_destroy();
    header("Location: ../../public/login.php");
    exit();
}
?>