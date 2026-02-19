<?php
// public/index.php
include_once '../app/core/init.php';
include_once '../config/Database.php';
include_once '../app/models/Configuracion.php';

$database = new Database();
$db = $database->getConnection();

$config_model = new Configuracion($db);
$system_config = $config_model->leer();

$page = isset($_GET['page']) ? $_GET['page'] : 'dashboard';

if (isset($_SESSION['is_medico']) && $_SESSION['is_medico'] === true) {
    include '../app/views/templates/header_medico.php';
} else {
    include '../app/views/templates/header.php';
}

switch ($page) {
    case 'mis_citas': include '../app/views/mis_citas/index.php'; break;
    case 'pacientes': include '../app/views/pacientes/index.php'; break;
    case 'citas': include '../app/views/citas/index.php'; break;
    case 'medicos': include '../app/views/medicos/index.php'; break;
    case 'especialidades': include '../app/views/especialidades/index.php'; break;
    case 'historial': include '../app/views/historial/index.php'; break;
    case 'usuarios': include '../app/views/usuarios/index.php'; break;
    case 'grupos': include '../app/views/grupos/index.php'; break;
    case 'aseguradoras': include '../app/views/aseguradoras/index.php'; break;
    case 'tipos_isv': include '../app/views/tipos_isv/index.php'; break;
    case 'proveedores': include '../app/views/proveedores/index.php'; break;
    case 'categorias_producto': include '../app/views/categorias_producto/index.php'; break;
    case 'productos': include '../app/views/productos/index.php'; break;
    case 'configuracion': include '../app/views/configuracion/index.php'; break;
    case 'inventario': include '../app/views/movimientos/index.php'; break;
    case 'compras': include '../app/views/compras/index.php'; break;
    case 'facturacion': include '../app/views/facturacion/index.php'; break;
    case 'cuentas_por_cobrar': include '../app/views/cuentas_por_cobrar/index.php'; break;
    case 'notas_credito': include '../app/views/notas_credito/index.php'; break;
    case 'cotizaciones': include '../app/views/cotizaciones/index.php'; break;
    case 'categorias_servicio': include '../app/views/categorias_servicio/index.php'; break;
    case 'servicios': include '../app/views/servicios/index.php'; break;
    case 'reporte_stock': include '../app/views/reportes/stock.php'; break;
    case 'configuracion_facturacion': include '../app/views/configuracion_facturacion/index.php'; break;
    case 'dashboard_settings': include '../app/views/dashboard_settings/index.php'; break;
    case 'textos_predefinidos': include '../app/views/textos_predefinidos/index.php'; break;
    case 'archivo_categorias': include '../app/views/archivo_categorias/index.php'; break;
    case 'consulta_plantillas': include '../app/views/consulta_plantillas/index.php'; break;
    case 'diagnosticos': include '../app/views/diagnosticos/index.php'; break;
    case 'reportes_clinicos': include '../app/views/reportes_clinicos/index.php'; break;
    case 'reportes_medicos': include '../app/views/reportes_medicos/index.php'; break;
    case 'reportes_ventas': include '../app/views/reportes_ventas/index.php'; break;

    case 'dashboard': default: include '../app/views/dashboard/index.php'; break;
}

include '../app/views/templates/footer.php';
?>