<?php
// app/reports/cotizacion_report.php

require_once __DIR__ . '/../../vendor/autoload.php';
include_once __DIR__ . '/../../config/Database.php';
include_once __DIR__ . '/../core/Auth.php';
include_once __DIR__ . '/../models/Cotizacion.php';
include_once __DIR__ . '/../models/Configuracion.php';

session_start();

// Previene el caching del archivo para la descarga
if (isset($_GET['downloadToken'])) {
    setcookie('downloadToken', $_GET['downloadToken'], time() + 20, "/");
}

// Verificación de permisos
if (!Auth::check('cotizaciones', 'ver')) {
    die('Acceso Denegado.');
}
if (!isset($_GET['id'])) {
    die("Error: No se ha especificado una cotización.");
}

$id_cotizacion = intval($_GET['id']);
$database = new Database();
$db = $database->getConnection();

// Cargar configuración de la clínica
$config_model = new Configuracion($db);
$config = $config_model->leer();

// Cargar datos de la cotización
$cotizacion_model = new Cotizacion($db);
$cotizacion = $cotizacion_model->leerUno($id_cotizacion);
$detalle = $cotizacion_model->leerDetalle($id_cotizacion);

ob_start();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Cotización <?php echo htmlspecialchars($cotizacion['correlativo']); ?></title>
    <style>
        body { font-family: sans-serif; font-size: 11px; }
        .header-table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        .header-table .logo-cell { width: 25%; text-align: left; vertical-align: top; }
        .header-table .info-cell { width: 75%; text-align: left; vertical-align: top; padding-left: 10px; }
        .header-table img { max-width: 100px; height: auto; }
        .header-table h1 { margin: 0; font-size: 18px; }
        .header-table p { margin: 2px 0; }
        .document-title { text-align: center; font-size: 16px; margin-bottom: 20px; }
        .info { border: 1px solid #ccc; padding: 10px; border-radius: 5px; margin-bottom: 20px; }
        .info table { width: 100%; border-collapse: collapse; }
        .info td { padding: 3px; }
        .detalle table { width: 100%; border-collapse: collapse; }
        .detalle th, .detalle td { border: 1px solid #ccc; padding: 6px; text-align: left; }
        .detalle th { background-color: #f2f2f2; }
        .totales { float: right; width: 40%; margin-top: 20px; }
        .totales table { width: 100%; }
        .totales td { padding: 4px; }
        .text-right { text-align: right; }
        .notes { margin-top: 40px; border-top: 1px solid #ccc; padding-top: 10px; }
    </style>
</head>
<body>
    <div class="header">
        <table class="header-table">
            <tr>
                <td class="logo-cell">
                    <?php if (!empty($config['logo'])): ?>
                        <img src="<?php echo __DIR__ . '/../../public/uploads/logos/' . $config['logo']; ?>" width="60">
                    <?php endif; ?>
                </td>
                <td class="info-cell">
                    <h1><?php echo htmlspecialchars($config['nombre_clinica']); ?></h1>
                    <p><?php echo htmlspecialchars($config['direccion']); ?></p>
                    <p>Tel: <?php echo htmlspecialchars($config['telefono']); ?> | Email: <?php echo htmlspecialchars($config['email']); ?></p>
                    <p>RTN: <?php echo htmlspecialchars($config['rtn']); ?></p>
                </td>
            </tr>
        </table>
    </div>
    <h2 class="document-title">COTIZACIÓN: <?php echo htmlspecialchars($cotizacion['correlativo']); ?></h2>
    <div class="info">
        <table>
            <tr>
                <td><strong>Paciente:</strong></td>
                <td><?php echo htmlspecialchars($cotizacion['paciente_nombre']); ?></td>
                <td><strong>Fecha Emisión:</strong></td>
                <td><?php echo date("d/m/Y", strtotime($cotizacion['fecha_emision'])); ?></td>
            </tr>
            <tr>
                <td><strong>Estado:</strong></td>
                <td><?php echo htmlspecialchars($cotizacion['estado']); ?></td>
                <td><strong>Fecha Vencimiento:</strong></td>
                <td><?php echo date("d/m/Y", strtotime($cotizacion['fecha_vencimiento'])); ?></td>
            </tr>
             <tr>
                <td><strong>Atendido por:</strong></td>
                <td colspan="3"><?php echo htmlspecialchars($cotizacion['usuario_nombre']); ?></td>
            </tr>
        </table>
    </div>
    <div class="detalle">
        <table>
            <thead>
                <tr>
                    <th>Descripción</th>
                    <th class="text-right">Cantidad</th>
                    <th class="text-right">Precio Unit.</th>
                    <th class="text-right">Descuento</th>
                    <th class="text-right">Subtotal</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($detalle as $item): 
                    $subtotal_linea = ($item['cantidad'] * $item['precio_unitario']) - $item['descuento'];
                ?>
                <tr>
                    <td><?php echo htmlspecialchars($item['descripcion_item']); ?></td>
                    <td class="text-right"><?php echo $item['cantidad']; ?></td>
                    <td class="text-right">L <?php echo number_format($item['precio_unitario'], 2); ?></td>
                    <td class="text-right">L <?php echo number_format($item['descuento'], 2); ?></td>
                    <td class="text-right">L <?php echo number_format($subtotal_linea, 2); ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <div class="totales">
        <table>
            <tr>
                <td>Subtotal:</td>
                <td class="text-right">L <?php echo number_format($cotizacion['subtotal'], 2); ?></td>
            </tr>
             <tr>
                <td>ISV:</td>
                <td class="text-right">L <?php echo number_format($cotizacion['isv_total'], 2); ?></td>
            </tr>
            <tr>
                <td>Descuento General:</td>
                <td class="text-right">L <?php echo number_format($cotizacion['descuento_total'], 2); ?></td>
            </tr>
            <tr>
                <td><strong>Total a Pagar:</strong></td>
                <td class="text-right"><strong>L <?php echo number_format($cotizacion['total'], 2); ?></strong></td>
            </tr>
        </table>
    </div>

    <?php if(!empty($cotizacion['notas'])): ?>
    <div class="notes">
        <strong>Notas:</strong>
        <p><?php echo nl2br(htmlspecialchars($cotizacion['notas'])); ?></p>
    </div>
    <?php endif; ?>
</body>
</html>
<?php
$html = ob_get_clean();

// Configuración de mPDF
$mpdf = new \Mpdf\Mpdf([
    'format' => 'LETTER',
    'margin_left' => 10,
    'margin_right' => 10,
    'margin_top' => 40,
    'margin_bottom' => 20,
    'margin_header' => 10,
    'margin_footer' => 10
]);

// Cabecera y Pie de página
$mpdf->SetHTMLHeader('<div style="text-align: right; font-size: 9px;">Cotización N°: ' . $cotizacion['correlativo'] . '</div>');
$mpdf->SetHTMLFooter('<div style="text-align: center; font-size: 9px;">Página {PAGENO} de {nbpg}</div>');

$mpdf->WriteHTML($html);
$mpdf->Output('Cotizacion-' . $cotizacion['correlativo'] . '.pdf', 'I'); // 'I' para ver en navegador, 'D' para forzar descarga
?>