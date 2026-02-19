<?php
// FIX: Ruta corregida para incluir Auth.php y Database
include_once __DIR__ . '/../../core/Auth.php';
// Database y Configuracion ya deberían estar cargados desde index.php, pero aseguramos
if (!class_exists('Database')) include_once __DIR__ . '/../../config/Database.php';
if (!class_exists('Configuracion')) include_once __DIR__ . '/../../models/Configuracion.php';

// LEER LA CONFIGURACIÓN DEL TEMA AL INICIO DE CADA PÁGINA
$db_instance = new Database();
$db = $db_instance->getConnection();
$config_model = new Configuracion($db);
$config = $config_model->leer();

// Asignar valores por defecto si no existen
$theme_mode = $config['theme_mode'] ?? 'light';
$bg_color = $config['background_color'] ?? '#f8f9fa';
$navbar_color = $config['navbar_color'] ?? '#343a40';
$navbar_sticky_class = ($config['navbar_sticky'] ?? '0') == '1' ? 'sticky-top' : '';
?>
<!DOCTYPE html>
<html lang="es" data-bs-theme="<?php echo htmlspecialchars($theme_mode); ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($config['nombre_clinica'] ?? 'Gestión Clínica'); ?></title>
    <link rel="icon" type="image/svg+xml" href="favicon.svg">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/2.0.8/css/dataTables.bootstrap5.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" />
    <link href="https://cdn.jsdelivr.net/npm/summernote@0.9.0/dist/summernote-bs5.min.css" rel="stylesheet">
    
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <link href="css/style.css" rel="stylesheet">

    <style>
        <?php if ($theme_mode == 'light'): ?>
        body {
            background-color: <?php echo htmlspecialchars($bg_color); ?> !important;
        }
        <?php endif; ?>
        .navbar {
             background-color: <?php echo htmlspecialchars($navbar_color); ?> !important;
        }
        
        .select2-container--open {
            z-index: 99999 !important;
        }
    </style>

    <meta name="csrf-token" content="<?php echo $_SESSION['csrf_token'] ?? ''; ?>">

    <script>
        const USER_GROUP_ID = <?php echo json_encode($_SESSION['user_group_id'] ?? 0); ?>;
        const PERMISOS = <?php echo json_encode($_SESSION['permisos'] ?? []); ?>;
        const CSRF_TOKEN = "<?php echo $_SESSION['csrf_token'] ?? ''; ?>";
    </script>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark shadow-sm <?php echo $navbar_sticky_class; ?>">
        <div class="container-fluid">
            <a class="navbar-brand" href="index.php?page=dashboard"><i class="fas fa-hospital-user me-2"></i><?php echo htmlspecialchars($config['nombre_clinica'] ?? 'Clínica MVC'); ?></a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto align-items-center">
                    <li class="nav-item"><a class="nav-link" href="index.php?page=dashboard"><i class="fas fa-tachometer-alt me-1"></i>Dashboard</a></li>
                    <li class="nav-item"><a class="nav-link" href="index.php?page=pacientes"><i class="fas fa-users me-1"></i>Pacientes</a></li>
                    <li class="nav-item"><a class="nav-link" href="index.php?page=citas"><i class="fas fa-calendar-check me-1"></i>Citas</a></li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdownVentas" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="fas fa-dollar-sign me-1"></i>Ventas
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdownVentas">
                            <li><a class="dropdown-item" href="index.php?page=cotizaciones"><i class="fas fa-file-alt fa-fw me-2"></i>Cotizaciones</a></li>
                            <li><a class="dropdown-item" href="index.php?page=facturacion"><i class="fas fa-file-invoice-dollar fa-fw me-2"></i>Facturación</a></li>
                            <li><a class="dropdown-item" href="index.php?page=cuentas_por_cobrar"><i class="fas fa-hand-holding-usd fa-fw me-2"></i>Cuentas por Cobrar</a></li>
                            <li><a class="dropdown-item" href="index.php?page=notas_credito"><i class="fas fa-undo-alt fa-fw me-2"></i>Notas de Crédito</a></li>
                        </ul>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdownInventario" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="fas fa-dolly-flatbed me-1"></i>Inventario
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdownInventario">
                            <li><a class="dropdown-item" href="index.php?page=inventario"><i class="fas fa-exchange-alt fa-fw me-2"></i>Movimientos</a></li>
                            <li><a class="dropdown-item" href="index.php?page=compras"><i class="fas fa-shopping-cart fa-fw me-2"></i>Compras</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="index.php?page=productos"><i class="fas fa-boxes fa-fw me-2"></i>Productos</a></li>
                            <li><a class="dropdown-item" href="index.php?page=servicios"><i class="fas fa-concierge-bell fa-fw me-2"></i>Servicios</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="index.php?page=categorias_producto"><i class="fas fa-tags fa-fw me-2"></i>Categorías de Producto</a></li>
                            <li><a class="dropdown-item" href="index.php?page=categorias_servicio"><i class="fas fa-tags fa-fw me-2"></i>Categorías de Servicio</a></li>
                            <li><a class="dropdown-item" href="index.php?page=proveedores"><i class="fas fa-truck fa-fw me-2"></i>Proveedores</a></li>
                        </ul>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdownReportes" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="fas fa-chart-line me-1"></i>Reportes
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdownReportes">
                            
                            <?php if (Auth::check('reporte_stock', 'ver')): ?>
                            <li><a class="dropdown-item" href="index.php?page=reporte_stock"><i class="fas fa-boxes fa-fw me-2"></i>Reporte de Stock</a></li>
                            <?php endif; ?>
                            
                            <?php if (Auth::check('reportes_clinicos', 'ver')): ?>
                            <li><a class="dropdown-item" href="index.php?page=reportes_clinicos"><i class="fas fa-chart-bar fa-fw me-2"></i>Reportes Clínicos</a></li>
                            <?php endif; ?>

                            <?php if (Auth::check('reportes_medicos', 'ver')): ?>
                            <li><a class="dropdown-item" href="index.php?page=reportes_medicos"><i class="fas fa-user-md fa-fw me-2"></i>Reportes Médicos</a></li>
                            <?php endif; ?>
                            
                            <?php if (Auth::check('reportes_ventas', 'ver')): ?>
                            <li><a class="dropdown-item" href="index.php?page=reportes_ventas"><i class="fas fa-cash-register fa-fw me-2"></i>Reporte de Ventas</a></li>
                            <?php endif; ?>
                        </ul>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdownConfig" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="fas fa-cogs me-1"></i>Configuraciones
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdownConfig">
                            <li><h6 class="dropdown-header">Personal y Acceso</h6></li>
                            <li><a class="dropdown-item" href="index.php?page=medicos"><i class="fas fa-user-md fa-fw me-2"></i>Médicos</a></li>
                            <li><a class="dropdown-item" href="index.php?page=usuarios"><i class="fas fa-user-shield fa-fw me-2"></i>Gestión de Usuarios</a></li>
                            <li><a class="dropdown-item" href="index.php?page=grupos"><i class="fas fa-users-cog fa-fw me-2"></i>Grupos y Permisos</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><h6 class="dropdown-header">Catálogos Clínicos</h6></li>
                            <li><a class="dropdown-item" href="index.php?page=especialidades"><i class="fas fa-notes-medical fa-fw me-2"></i>Especialidades</a></li>
                            <li><a class="dropdown-item" href="index.php?page=aseguradoras"><i class="fas fa-building fa-fw me-2"></i>Aseguradoras</a></li>
                            <?php if (Auth::check('consulta_plantillas', 'ver') || Auth::check('pacientes', 'ver')): ?>
                                <li><a class="dropdown-item" href="index.php?page=diagnosticos"><i class="fas fa-stethoscope fa-fw me-2"></i>Diagnósticos (IDx)</a></li>
                            <?php endif; ?>
                            <li><hr class="dropdown-divider"></li>
                            <li><h6 class="dropdown-header">Ajustes del Sistema</h6></li>
                            <li><a class="dropdown-item" href="index.php?page=configuracion"><i class="fas fa-cog fa-fw me-2"></i>Información General</a></li>
                            <li><a class="dropdown-item" href="index.php?page=dashboard_settings"><i class="fas fa-th-large fa-fw me-2"></i>Personalizar Dashboard</a></li>
                            <li><a class="dropdown-item" href="index.php?page=configuracion_facturacion"><i class="fas fa-file-invoice fa-fw me-2"></i>Facturación</a></li>
                            <li><a class="dropdown-item" href="index.php?page=textos_predefinidos"><i class="fas fa-file-alt fa-fw me-2"></i>Textos Predefinidos</a></li>

                            <?php if (Auth::check('consulta_plantillas', 'ver')): ?>
                            <li><a class="dropdown-item" href="index.php?page=consulta_plantillas"><i class="fas fa-file-medical-alt fa-fw me-2"></i>Plantillas de Consulta</a></li>
                            <?php endif; ?>
                            
                            <?php if (Auth::check('archivo_categorias', 'ver')): ?>
                                <li><a class="dropdown-item" href="index.php?page=archivo_categorias"><i class="fas fa-tags fa-fw me-2"></i>Categorías de Archivos</a></li>
                            <?php endif; ?>

                            <li><a class="dropdown-item" href="index.php?page=tipos_isv"><i class="fas fa-percentage fa-fw me-2"></i>Tipos de ISV</a></li>
                        </ul>
                    </li>
                    <li class="nav-item">
                        <span class="navbar-text ms-3 me-2">
                            Hola, <?php echo htmlspecialchars($_SESSION['user_name']); ?>
                        </span>
                    </li>
                    <li class="nav-item">
                        <form action="../app/controllers/AuthController.php" method="POST" class="d-flex">
                             <input type="hidden" name="action" value="logout">
                             <button type="submit" class="btn btn-danger btn-sm" title="Cerrar Sesión"><i class="fas fa-sign-out-alt"></i></button>
                        </form>
                    </li>
                </ul>
            </div>
        </div>
    </nav>
    <div class="container mt-4 mb-5">