<?php
// app/reports/estado_cuenta_report.php
require_once __DIR__ . '/../../vendor/autoload.php';
include_once __DIR__ . '/../../config/Database.php';
include_once __DIR__ . '/../models/Configuracion.php';
include_once __DIR__ . '/../models/Paciente.php'; // Se usa el modelo de Paciente

session_start();

if (!isset($_GET['paciente_id']) || !isset($_GET['start_date']) || !isset($_GET['end_date'])) {
    die("Error: Faltan parámetros para generar el reporte.");
}

$id_paciente = intval($_GET['paciente_id']);
$start_date = $_GET['start_date'];
$end_date = $_GET['end_date'];

$database = new Database();
$db = $database->getConnection();

$config_model = new Configuracion($db);
$config = $config_model->leer();

$paciente_model = new Paciente($db);
// Necesitamos un método 'leerUno' en el modelo Paciente para obtener el nombre
$paciente_info = $paciente_model->leerUno($id_paciente); 
$balance_inicial = $paciente_model->obtenerBalanceInicial($id_paciente, $start_date);
$transacciones = $paciente_model->obtenerEstadoDeCuenta($id_paciente, $start_date, $end_date);

ob_start();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Estado de Cuenta - <?php echo htmlspecialchars($paciente_info['nombres'] . ' ' . $paciente_info['apellidos']); ?></title>
    <style>
        /* (Se utilizarán los mismos estilos que en los reportes de compra/cotización) */
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
                <td class="logo-cell"><img src="<?php echo __DIR__ . '/../../public/uploads/logos/' . $config['logo']; ?>" width="60"></td>
                <td class="info-cell">
                    <h1><?php echo htmlspecialchars($config['nombre_clinica']); ?></h1>
                    <p><?php echo htmlspecialchars($config['direccion']); ?></p>
                </td>
            </tr>
        </table>
    </div>
    <h2 class="document-title">ESTADO DE CUENTA</h2>
    <div class="info">
        <table>
            <tr>
                <td><strong>Paciente:</strong></td>
                <td><?php echo htmlspecialchars($paciente_info['nombres'] . ' ' . $paciente_info['apellidos']); ?></td>
                <td><strong>Periodo:</strong></td>
                <td><?php echo date("d/m/Y", strtotime($start_date)) . ' - ' . date("d/m/Y", strtotime($end_date)); ?></td>
            </tr>
        </table>
    </div>
    <div class="detalle">
        <table>
            <thead>
                <tr>
                    <th>Fecha</th>
                    <th>Tipo</th>
                    <th>Descripción</th>
                    <th class="text-right">Cargos</th>
                    <th class="text-right">Abonos</th>
                    <th class="text-right">Saldo</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td colspan="5"><strong>Balance Inicial</strong></td>
                    <td class="text-right"><strong>L <?php echo number_format($balance_inicial, 2); ?></strong></td>
                </tr>
                <?php 
                $saldo_corriente = $balance_inicial;
                foreach ($transacciones as $item): 
                    $saldo_corriente += ($item['cargo'] - $item['abono']);
                ?>
                <tr>
                    <td><?php echo date("d/m/Y", strtotime($item['fecha'])); ?></td>
                    <td><?php echo htmlspecialchars($item['tipo']); ?></td>
                    <td><?php echo htmlspecialchars($item['descripcion']); ?></td>
                    <td class="text-right">L <?php echo number_format($item['cargo'], 2); ?></td>
                    <td class="text-right">L <?php echo number_format($item['abono'], 2); ?></td>
                    <td class="text-right">L <?php echo number_format($saldo_corriente, 2); ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <div class="totales">
        <table>
            <tr>
                <td><strong>Balance Final:</strong></td>
                <td class="text-right"><strong>L <?php echo number_format($saldo_corriente, 2); ?></strong></td>
            </tr>
        </table>
    </div>
</body>
</html>
<?php
$html = ob_get_clean();
$mpdf = new \Mpdf\Mpdf(['format' => 'LETTER']);
$mpdf->WriteHTML($html);
$mpdf->Output('EstadoDeCuenta.pdf', 'I');
?>