<?php
// app/reports/nota_credito_report.php

require_once __DIR__ . '/../../vendor/autoload.php';
include_once __DIR__ . '/../../config/Database.php';
include_once __DIR__ . '/../core/Auth.php';
include_once __DIR__ . '/../models/NotaCredito.php';
include_once __DIR__ . '/../models/Configuracion.php';

session_start();

if (!Auth::check('facturacion', 'ver')) { // Usamos el mismo permiso
    die('Acceso Denegado.');
}
if (!isset($_GET['id'])) {
    die("Error: No se ha especificado una nota de crédito.");
}

$id_nota_credito = intval($_GET['id']);
$database = new Database();
$db = $database->getConnection();

$config_model = new Configuracion($db);
$config = $config_model->leer();

$nc_model = new NotaCredito($db);
$nc = $nc_model->leerUno($id_nota_credito);
$detalle = $nc_model->leerDetalle($id_nota_credito);

ob_start();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Nota de Crédito <?php echo htmlspecialchars($nc['correlativo']); ?></title>
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
                    <p>RTN: <?php echo htmlspecialchars($config['rtn']); ?></p>
                </td>
            </tr>
        </table>
    </div>
    <h2 class="document-title">NOTA DE CRÉDITO: <?php echo htmlspecialchars($nc['correlativo']); ?></h2>
    <div class="info">
        <table>
            <tr>
                <td><strong>Paciente:</strong></td>
                <td><?php echo htmlspecialchars($nc['paciente_nombre']); ?></td>
                <td><strong>Fecha Emisión:</strong></td>
                <td><?php echo date("d/m/Y", strtotime($nc['fecha_emision'])); ?></td>
            </tr>
            <tr>
                <td><strong>Factura Afectada:</strong></td>
                <td><?php echo htmlspecialchars($nc['factura_asociada']); ?></td>
                <td><strong>Emitida por:</strong></td>
                <td><?php echo htmlspecialchars($nc['usuario_nombre']); ?></td>
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
                    <th class="text-right">Subtotal</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($detalle as $item): ?>
                <tr>
                    <td><?php echo htmlspecialchars($item['descripcion_item']); ?></td>
                    <td class="text-right"><?php echo $item['cantidad']; ?></td>
                    <td class="text-right">L <?php echo number_format($item['precio_unitario'], 2); ?></td>
                    <td class="text-right">L <?php echo number_format($item['subtotal'], 2); ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <div class="totales">
        <table>
            <tr>
                <td>Subtotal:</td>
                <td class="text-right">L <?php echo number_format($nc['subtotal'], 2); ?></td>
            </tr>
             <tr>
                <td>ISV:</td>
                <td class="text-right">L <?php echo number_format($nc['isv_total'], 2); ?></td>
            </tr>
            <tr>
                <td><strong>Total (Crédito a favor):</strong></td>
                <td class="text-right"><strong>L <?php echo number_format($nc['total'], 2); ?></strong></td>
            </tr>
        </table>
    </div>
    <?php if(!empty($nc['motivo'])): ?>
    <div class="notes">
        <strong>Motivo:</strong>
        <p><?php echo nl2br(htmlspecialchars($nc['motivo'])); ?></p>
    </div>
    <?php endif; ?>
</body>
</html>
<?php
$html = ob_get_clean();
$mpdf = new \Mpdf\Mpdf(['format' => 'LETTER']);
$mpdf->WriteHTML($html);
$mpdf->Output('NotaCredito-' . $nc['correlativo'] . '.pdf', 'I');
?>