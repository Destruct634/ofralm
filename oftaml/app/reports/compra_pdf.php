<?php
// app/reports/compra_pdf.php

require_once __DIR__ . '/../../vendor/autoload.php';
include_once __DIR__ . '/../../config/Database.php';
include_once __DIR__ . '/../core/Auth.php';
include_once __DIR__ . '/../models/Compra.php';
include_once __DIR__ . '/../models/Configuracion.php';

session_start();

if (isset($_GET['downloadToken'])) {
    setcookie('downloadToken', $_GET['downloadToken'], time() + 20, "/");
}

if (!Auth::check('compras', 'ver')) {
    die('Acceso Denegado.');
}
if (!isset($_GET['id'])) {
    die("Error: No se ha especificado una compra.");
}

$id_compra = intval($_GET['id']);
$database = new Database();
$db = $database->getConnection();

$config_model = new Configuracion($db);
$config = $config_model->leer();

$compra_model = new Compra($db);
$compra = $compra_model->leerUno($id_compra);
$detalle = $compra_model->leerDetalle($id_compra);

ob_start();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Registro de Compra <?php echo htmlspecialchars($compra['correlativo']); ?></title>
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
                </td>
            </tr>
        </table>
    </div>
    <h2 class="document-title">REGISTRO DE COMPRA: <?php echo htmlspecialchars($compra['correlativo']); ?></h2>
    <div class="info">
        <table>
            <tr>
                <td><strong>Proveedor:</strong></td>
                <td><?php echo htmlspecialchars($compra['nombre_proveedor']); ?></td>
                <td><strong>Fecha:</strong></td>
                <td><?php echo date("d/m/Y", strtotime($compra['fecha_compra'])); ?></td>
            </tr>
            <tr>
                <td><strong>N° Factura:</strong></td>
                <td><?php echo htmlspecialchars($compra['numero_factura']); ?></td>
                <td><strong>N° Orden:</strong></td>
                <td><?php echo htmlspecialchars($compra['numero_orden']); ?></td>
            </tr>
             <tr>
                <td><strong>Estado:</strong></td>
                <td><?php echo htmlspecialchars($compra['estado']); ?></td>
                <td><strong>Registrado por:</strong></td>
                <td><?php echo htmlspecialchars($compra['usuario_nombre']); ?></td>
            </tr>
        </table>
    </div>
    <div class="detalle">
        <table>
            <thead>
                <tr>
                    <th>Producto</th>
                    <th class="text-right">Cantidad</th>
                    <th class="text-right">Precio Unitario</th>
                    <th class="text-right">Subtotal</th>
                    <th>ISV</th>
                    <th class="text-right">Total</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $subtotal_general = 0;
                $isv_general = 0;
                foreach ($detalle as $item): 
                    $subtotal = $item['cantidad'] * $item['precio_compra'];
                    $isv = $subtotal * ($item['porcentaje'] / 100);
                    $total_linea = $subtotal + $isv;
                    $subtotal_general += $subtotal;
                    $isv_general += $isv;
                ?>
                <tr>
                    <td><?php echo htmlspecialchars($item['nombre_producto']); ?></td>
                    <td class="text-right"><?php echo $item['cantidad']; ?></td>
                    <td class="text-right">L <?php echo number_format($item['precio_compra'], 2); ?></td>
                    <td class="text-right">L <?php echo number_format($subtotal, 2); ?></td>
                    <td><?php echo htmlspecialchars($item['nombre_isv']); ?></td>
                    <td class="text-right">L <?php echo number_format($total_linea, 2); ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <div class="totales">
        <table>
            <tr>
                <td>Subtotal:</td>
                <td class="text-right">L <?php echo number_format($subtotal_general, 2); ?></td>
            </tr>
            <tr>
                <td>ISV:</td>
                <td class="text-right">L <?php echo number_format($isv_general, 2); ?></td>
            </tr>
            <tr>
                <td><strong>Total General:</strong></td>
                <td class="text-right"><strong>L <?php echo number_format($compra['total_compra'], 2); ?></strong></td>
            </tr>
        </table>
    </div>
</body>
</html>
<?php
$html = ob_get_clean();
$mpdf = new \Mpdf\Mpdf(['format' => 'LETTER']);
$mpdf->WriteHTML($html);
$mpdf->Output('Compra-' . $compra['correlativo'] . '.pdf', 'I');
?>
