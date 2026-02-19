<?php
// app/reports/factura_report.php

// Incluir archivos necesarios
include_once '../../config/Database.php';
include_once '../../app/models/Factura.php';
include_once '../../app/models/Configuracion.php';
include_once '../../app/models/Pago.php';

// Validar que se haya proporcionado un ID de factura
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("Error: ID de factura no válido.");
}

$id_factura = intval($_GET['id']);

// Conexión a la base de datos
$database = new Database();
$db = $database->getConnection();

// Instanciar modelos
$factura_model = new Factura($db);
$config_model = new Configuracion($db);
$pago_model = new Pago($db);

// Obtener datos
$factura = $factura_model->leerUno($id_factura);
$detalle = $factura_model->leerDetalle($id_factura);
$pagos = $pago_model->leerPorFactura($id_factura);
$config = $config_model->leer();

if (!$factura) {
    die("Error: Factura no encontrada.");
}

// --- CORRECCIÓN LÓGICA: Calcular Subtotal BRUTO ---
// Recalculamos el bruto para que el reporte tenga sentido matemático (Bruto - Descuento = Neto)
$subtotal_bruto_general = 0;
$descuento_total_items = 0;

foreach ($detalle as $item) {
    $bruto_item = floatval($item['cantidad']) * floatval($item['precio_unitario']);
    $subtotal_bruto_general += $bruto_item;
    $descuento_total_items += floatval($item['descuento']);
}

// El descuento total es la suma de descuentos por ítem + descuento general de la factura
$descuento_total_general = $descuento_total_items + floatval($factura['descuento_total']);

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Factura <?php echo htmlspecialchars($factura['correlativo']); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            font-family: 'Courier New', Courier, monospace;
            color: #000;
            background-color: #fff;
        }
        .ticket-container {
            width: 300px; /* Ancho similar a un ticket de 80mm */
            margin: 20px auto;
            padding: 10px;
        }
        .ticket-header {
            text-align: center;
            margin-bottom: 15px;
        }
        .ticket-header h5, .ticket-header p {
            margin: 0;
            font-size: 12px;
        }
        .ticket-header h5 {
            font-size: 14px;
            font-weight: bold;
        }
        .details p {
            margin: 0;
            font-size: 11px;
        }
        .items-table {
            width: 100%;
            font-size: 11px;
            border-top: 1px dashed #000;
            border-bottom: 1px dashed #000;
            margin: 15px 0;
        }
        .items-table th, .items-table td {
            padding: 4px 2px;
        }
        .items-table .text-end {
            text-align: right;
        }
        .totals-table {
            width: 100%;
            font-size: 12px;
        }
        .totals-table td {
            padding: 2px;
        }
        .footer-text {
            text-align: center;
            font-size: 10px;
            margin-top: 20px;
        }
        @media print {
            body {
                margin: 0;
                padding: 0;
            }
            .ticket-container {
                border: none;
                box-shadow: none;
                margin: 0;
                width: 100%;
            }
        }
    </style>
</head>
<body>
    <div class="ticket-container">
        <div class="ticket-header">
            <h5><?php echo htmlspecialchars($config['nombre_clinica']); ?></h5>
            <p><?php echo htmlspecialchars($config['direccion']); ?></p>
            <p>Tel: <?php echo htmlspecialchars($config['telefono']); ?></p>
        </div>

        <div class="details">
            <p><strong>Factura:</strong> <?php echo htmlspecialchars($factura['correlativo']); ?></p>
            <p><strong>Fecha:</strong> <?php echo date("d/m/Y h:i A", strtotime($factura['fecha_emision'])); ?></p>
            <p><strong>Paciente:</strong> <?php echo htmlspecialchars($factura['paciente_nombre']); ?></p>
        </div>

        <table class="items-table">
            <thead>
                <tr>
                    <th>Desc.</th>
                    <th class="text-center">Cant.</th>
                    <th class="text-end">Precio</th>
                    <th class="text-end">Total</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($detalle as $item): ?>
                    <?php 
                        // Calculamos el subtotal bruto de la línea para mostrarlo
                        $subtotal_bruto_item = floatval($item['cantidad']) * floatval($item['precio_unitario']);
                    ?>
                    <tr>
                        <td colspan="4"><?php echo htmlspecialchars($item['descripcion_item']); ?></td>
                    </tr>
                    <tr>
                        <td></td>
                        <td class="text-center"><?php echo $item['cantidad']; ?></td>
                        <td class="text-end">L <?php echo number_format($item['precio_unitario'], 2); ?></td>
                        <td class="text-end">L <?php echo number_format($subtotal_bruto_item, 2); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <table class="totals-table">
            <tbody>
                <tr>
                    <td>Subtotal:</td>
                    <td class="text-end">L <?php echo number_format($subtotal_bruto_general, 2); ?></td>
                </tr>
                <?php if ($descuento_total_general > 0): ?>
                <tr>
                    <td>Descuento:</td>
                    <td class="text-end">- L <?php echo number_format($descuento_total_general, 2); ?></td>
                </tr>
                <?php endif; ?>
                <tr>
                    <td>ISV:</td>
                    <td class="text-end">L <?php echo number_format($factura['isv_total'], 2); ?></td>
                </tr>
                <tr class="fw-bold">
                    <td>Total:</td>
                    <td class="text-end">L <?php echo number_format($factura['total'], 2); ?></td>
                </tr>
            </tbody>
        </table>

        <?php if (!empty($pagos)): ?>
            <div class="details mt-3">
                <p class="fw-bold">Formas de Pago:</p>
                <?php foreach($pagos as $pago): ?>
                    <p>
                        <strong><?php echo htmlspecialchars($pago['forma_pago']); ?>:</strong> L <?php echo number_format($pago['monto'], 2); ?>
                        <?php if(!empty($pago['referencia'])): ?>
                            <small>(Ref: <?php echo htmlspecialchars($pago['referencia']); ?>)</small>
                        <?php endif; ?>
                    </p>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <div class="footer-text">
            <p>¡Gracias por su visita!</p>
        </div>
    </div>

    <?php if (isset($_GET['action']) && $_GET['action'] == 'print'): ?>
        <script>
            window.onload = function() {
                window.print();
            };
        </script>
    <?php endif; ?>
</body>
</html>