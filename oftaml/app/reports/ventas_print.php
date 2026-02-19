<?php
// app/reports/ventas_print.php
include_once '../../config/Database.php';
include_once '../../app/models/ReporteVenta.php';
include_once '../../app/models/Configuracion.php';

$database = new Database();
$db = $database->getConnection();

// Obtener Configuración (Logo, Nombre)
$configModel = new Configuracion($db);
$config = $configModel->leer();

// Obtener Datos
$reporteModel = new ReporteVenta($db);
$filtros = [
    'fecha_inicio' => $_GET['fi'],
    'fecha_fin' => $_GET['ff'],
    'tipo' => $_GET['tipo'],
    'id_medico' => $_GET['med'] ?? null,
    'id_tecnico' => $_GET['tec'] ?? null
];
$datos = $reporteModel->generarReporte($filtros);

$totalGeneral = 0;
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reporte de Ventas</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 12px; }
        .header { text-align: center; margin-bottom: 20px; }
        .header h2 { margin: 5px 0; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { border: 1px solid #ddd; padding: 6px; text-align: left; }
        th { background-color: #f2f2f2; }
        .text-end { text-align: right; }
        .badge { padding: 2px 5px; border-radius: 3px; font-size: 10px; color: white; }
        .bg-prod { background-color: #17a2b8; }
        .bg-serv { background-color: #007bff; }
    </style>
</head>
<body onload="window.print()">
    <div class="header">
        <?php if($config['logo']): ?>
            <img src="../../public/uploads/logos/<?php echo $config['logo']; ?>" height="50"><br>
        <?php endif; ?>
        <h2><?php echo $config['nombre_clinica']; ?></h2>
        <h3>Reporte de Ventas</h3>
        <p>Del <?php echo date('d/m/Y', strtotime($filtros['fecha_inicio'])); ?> al <?php echo date('d/m/Y', strtotime($filtros['fecha_fin'])); ?></p>
    </div>

    <table>
        <thead>
            <tr>
                <th>Fecha</th>
                <th>Factura</th>
                <th>Paciente</th>
                <th>Descripción</th>
                <th>Tipo</th>
                <th>Responsable</th>
                <th class="text-end">Total</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($datos as $row): 
                $totalGeneral += $row['subtotal_item'];
            ?>
            <tr>
                <td><?php echo date('d/m/Y H:i', strtotime($row['fecha_emision'])); ?></td>
                <td><?php echo $row['correlativo']; ?></td>
                <td><?php echo $row['paciente']; ?></td>
                <td><?php echo $row['descripcion_item']; ?></td>
                <td>
                    <span class="badge <?php echo $row['tipo_item']=='Producto'?'bg-prod':'bg-serv'; ?>">
                        <?php echo $row['tipo_item']; ?>
                    </span>
                </td>
                <td>
                    <?php 
                        if($row['medico']) echo "Med: " . $row['medico'] . "<br>";
                        if($row['tecnico']) echo "Tec: " . $row['tecnico'];
                    ?>
                </td>
                <td class="text-end">L <?php echo number_format($row['subtotal_item'], 2); ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
        <tfoot>
            <tr>
                <th colspan="6" class="text-end">TOTAL VENTAS:</th>
                <th class="text-end">L <?php echo number_format($totalGeneral, 2); ?></th>
            </tr>
        </tfoot>
    </table>
</body>
</html>