<?php
session_start();
// Si el usuario ya está logueado, redirigir al dashboard
if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true) {
    header('Location: index.php?page=dashboard');
    exit;
}

// Cargar personalización del login (color / imagen) si existe
$login_cfg = array();
$login_cfg_file = __DIR__ . '/../config/login_custom.json';
if (file_exists($login_cfg_file)) {
    $tmp = json_decode(@file_get_contents($login_cfg_file), true);
    if (is_array($tmp)) $login_cfg = $tmp;
}
$login_color = isset($login_cfg['color']) ? $login_cfg['color'] : null;
// Si la imagen es relativa a project root como 'public/uploads/xxx', la convertimos a ruta desde public/
// __DIR__ es public/, por eso prefix ../ para llegar a project root; pero queremos ruta accesible desde navegador.
// Guardamos la ruta que ya usábamos: si en JSON se guarda 'public/uploads/xxx', la ruta web relativa desde public/ es 'uploads/xxx' or '../public/...' 
// Para simplificar asumimos que JSON guarda 'public/uploads/...' (como hicimos antes), entonces la ruta web desde public/ será '../public/uploads/...'.
// Aquí componemos la URL usada en HTML.
$login_image = null;
if (!empty($login_cfg['image'])) {
    // si image ya empieza con 'public/', hacemos '../' . image para que desde public/ apunte al archivo (ej: '../public/uploads/x.png')
    if (strpos($login_cfg['image'], 'public/') === 0) {
        $login_image = '../' . $login_cfg['image'];
    } else {
        // si fuera una ruta distinta, úsala directamente (puede que ya sea relativa a public/)
        $login_image = $login_cfg['image'];
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar Sesión - LaserVision</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">

    <style>
        /* Fondo del login: prioridad a imagen si existe, sino color, sino fallback */
        body {
            <?php if ($login_image): ?>
                background-image: url("<?php echo htmlspecialchars($login_image, ENT_QUOTES, 'UTF-8'); ?>");
                background-position: center;
                background-repeat: no-repeat;
                background-size: cover;
                background-color: #000; /* backup while image loads */
            <?php elseif ($login_color): ?>
                background-color: <?php echo htmlspecialchars($login_color, ENT_QUOTES, 'UTF-8'); ?>;
            <?php else: ?>
                background-color: #f8f9fa;
            <?php endif; ?>
        }
        /* Opcional: contenedor centrado igual que el original */
        .login-container { max-width: 400px; margin: 100px auto; }
        /* Si la imagen hace que el texto no tenga contraste, puedes activar overlay en HTML o con esta clase */
        .login-overlay {
            position: fixed;
            inset: 0;
            pointer-events: none;
            background: rgba(0,0,0,0.25); /* ajusta opacidad si hace falta */
        }
    </style>
</head>
<body>
    <!-- Descomenta esta línea si quieres overlay oscuro por defecto sobre la imagen -->
    <!-- <div class="login-overlay"></div> -->

    <div class="login-container">
        <div class="card shadow-sm">
            <div class="card-header text-center bg-primary text-white">
                <h3><i class="fas fa-hospital-user me-2"></i>LaserVision</h3>
            </div>
            <div class="card-body p-4">
                <h5 class="card-title text-center mb-4">Iniciar Sesión</h5>
                <?php if (isset($_SESSION['error_login'])): ?>
                    <div class="alert alert-danger"><?php echo $_SESSION['error_login']; unset($_SESSION['error_login']); ?></div>
                <?php endif; ?>
                <form action="../app/controllers/AuthController.php" method="POST">
                    <input type="hidden" name="action" value="login">
                    <div class="mb-3">
                        <label for="usuario" class="form-label">Usuario</label>
                        <input type="text" class="form-control" id="usuario" name="usuario" required>
                    </div>
                    <div class="mb-3">
                        <label for="password" class="form-label">Contraseña</label>
                        <input type="password" class="form-control" id="password" name="password" required>
                    </div>
                    <button type="submit" class="btn btn-primary w-100">Entrar</button>
                </form>
            </div>
        </div>
    </div>
</body>
</html>
