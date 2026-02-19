<?php
// Aseguramos la carga de Auth y Configuración
include_once __DIR__ . '/../../core/Auth.php';
if (!class_exists('Database')) include_once __DIR__ . '/../../config/Database.php';
if (!class_exists('Configuracion')) include_once __DIR__ . '/../../models/Configuracion.php';

$db_instance = new Database();
$db = $db_instance->getConnection();
$config_model = new Configuracion($db);
$config = $config_model->leer();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Portal del Médico - <?php echo htmlspecialchars($config['nombre_clinica'] ?? 'Clínica'); ?></title>
    <link rel="icon" type="image/svg+xml" href="favicon.svg">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/2.0.8/css/dataTables.bootstrap5.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />
    
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" />
    <link href="https://cdn.jsdelivr.net/npm/summernote@0.9.0/dist/summernote-bs5.min.css" rel="stylesheet">

    <link href="css/style.css" rel="stylesheet">

    <style>
        /* Estilo para asegurar que los modales del historial se vean bien sobre el fondo del portal */
        .select2-container--open { z-index: 99999 !important; }
    </style>

    <meta name="csrf-token" content="<?php echo $_SESSION['csrf_token'] ?? ''; ?>">

    <script>
        const USER_GROUP_ID = <?php echo json_encode($_SESSION['user_group_id'] ?? 0); ?>;
        const PERMISOS = <?php echo json_encode($_SESSION['permisos'] ?? []); ?>;
        const CSRF_TOKEN = "<?php echo $_SESSION['csrf_token'] ?? ''; ?>";
    </script>
</head>
<body class="bg-light">
    <nav class="navbar navbar-expand-lg navbar-dark bg-success shadow-sm sticky-top">
        <div class="container-fluid">
            <a class="navbar-brand" href="index.php?page=mis_citas"><i class="fas fa-stethoscope me-2"></i>Portal del Médico</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto align-items-center">
                    <li class="nav-item">
                        <span class="navbar-text me-2">
                            Bienvenido(a), Dr(a). <?php echo htmlspecialchars($_SESSION['user_name']); ?>
                        </span>
                    </li>
                    <li class="nav-item">
                        <form action="../app/controllers/AuthController.php" method="POST" class="d-flex">
                             <input type="hidden" name="action" value="logout">
                             <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token'] ?? ''; ?>">
                             <button type="submit" class="btn btn-outline-light btn-sm" title="Cerrar Sesión"><i class="fas fa-sign-out-alt"></i></button>
                        </form>
                    </li>
                </ul>
            </div>
        </div>
    </nav>
    <div class="container mt-4 mb-5">