<?php
class Auth {
    /**
     * Verifica si el usuario actual tiene un permiso específico para un módulo.
     * Ejemplo de uso: Auth::check('pacientes', 'crear')
     *
     * @param string $modulo El nombre técnico del módulo (ej. 'pacientes').
     * @param string $permiso El permiso a verificar (ej. 'ver', 'crear', 'editar', 'borrar').
     * @return bool
     */
    public static function check($modulo, $permiso) {
        // Si no está logueado o no tiene permisos, denegar acceso.
        if (!isset($_SESSION['loggedin']) || !isset($_SESSION['permisos'])) {
            return false;
        }

        // El grupo de Administradores (ID 1) siempre tiene acceso a todo.
        if (isset($_SESSION['user_group_id']) && $_SESSION['user_group_id'] == 1) {
            return true;
        }

        // Si el módulo no está en su lista de permisos, denegar acceso.
        if (!isset($_SESSION['permisos'][$modulo])) {
            return false;
        }

        // Devolver true si el permiso específico está activado (es 1 o true).
        return !empty($_SESSION['permisos'][$modulo][$permiso]);
    }

    // --- NUEVO: Generar Token CSRF ---
    // Crea un token único para la sesión si no existe.
    public static function generateCsrfToken() {
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }

    // --- NUEVO: Validar Token CSRF ---
    // Verifica que las peticiones que modifican datos vengan de nuestro propio sitio.
    public static function validateCsrfToken() {
        // Solo validamos en peticiones que cambian datos (POST, PUT, DELETE)
        if ($_SERVER['REQUEST_METHOD'] === 'POST' || $_SERVER['REQUEST_METHOD'] === 'PUT' || $_SERVER['REQUEST_METHOD'] === 'DELETE') {
            
            // Buscamos el token en los encabezados (enviado por AJAX)
            $headers = apache_request_headers();
            $token_header = $headers['X-CSRF-Token'] ?? '';
            
            // Compatibilidad: Algunos servidores no entregan apache_request_headers, buscamos en $_SERVER
            if (empty($token_header)) {
                $token_header = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
            }

            // También buscamos si se envió por POST directo (formularios normales)
            $token_post = $_POST['csrf_token'] ?? '';

            $token_session = $_SESSION['csrf_token'] ?? null;

            // Si no hay token en sesión, o no coincide ni con el header ni con el post
            if (!$token_session || (!hash_equals($token_session, $token_header) && !hash_equals($token_session, $token_post))) {
                // Respuesta de error de seguridad
                http_response_code(403);
                header('Content-Type: application/json');
                echo json_encode(["message" => "Error de seguridad: Token CSRF inválido o expirado. Por favor recargue la página."]);
                exit();
            }
        }
    }
}
?>