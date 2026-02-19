<?php
// app/models/Pago.php

include_once __DIR__ . '/Movimiento.php';

class Pago {
    private $conn;
    private $table_name = "pagos";
    private $table_detalle = "pago_detalle";

    public function __construct($db) {
        $this->conn = $db;
    }

    public function crear($data) {
        $this->conn->beginTransaction();
        try {
            // 1. Crear el registro principal del pago
            $query_pago = "INSERT INTO " . $this->table_name . " SET id_factura=:id_factura, monto_total=:monto_total, id_usuario=:id_usuario";
            $stmt_pago = $this->conn->prepare($query_pago);
            $stmt_pago->execute([
                ':id_factura' => $data['id_factura'],
                ':monto_total' => $data['monto_total'],
                ':id_usuario' => $_SESSION['user_id']
            ]);
            $id_pago = $this->conn->lastInsertId();

            // 2. Insertar cada método de pago en el detalle
            $query_detalle = "INSERT INTO " . $this->table_detalle . " (id_pago, forma_pago, monto, referencia) VALUES (:id_pago, :forma_pago, :monto, :referencia)";
            $stmt_detalle = $this->conn->prepare($query_detalle);

            foreach ($data['detalle'] as $item) {
                $stmt_detalle->execute([
                    ':id_pago' => $id_pago,
                    ':forma_pago' => $item['forma_pago'],
                    ':monto' => $item['monto'],
                    ':referencia' => $item['referencia']
                ]);
            }

            // 3. Actualizar el estado de la factura Y AHORA EL INVENTARIO
            $this->actualizarEstadoFacturaYInventario($data['id_factura']);

            $this->conn->commit();
            return true;

        } catch (Exception $e) {
            $this->conn->rollBack();
            error_log($e->getMessage());
            throw $e; 
        }
    }

    private function actualizarEstadoFacturaYInventario($id_factura) {
        
        // 1. Obtener el total y correlativo de la factura
        $stmt_factura = $this->conn->prepare("SELECT total, correlativo, estado FROM facturas WHERE id = ?");
        $stmt_factura->execute([$id_factura]);
        $factura = $stmt_factura->fetch(PDO::FETCH_ASSOC);
        
        if (!$factura) { throw new Exception("La factura con ID $id_factura no existe."); }
        
        $total_factura = (float)$factura['total'];
        $estado_actual = $factura['estado'];
        $correlativo_factura = $factura['correlativo'];

        // 2. Obtener la suma de todos los pagos realizados
        $stmt_pagos = $this->conn->prepare("SELECT SUM(pd.monto) as total_pagado 
                                           FROM pagos p 
                                           JOIN pago_detalle pd ON p.id = pd.id_pago 
                                           WHERE p.id_factura = ?");
        $stmt_pagos->execute([$id_factura]);
        $pagos = $stmt_pagos->fetch(PDO::FETCH_ASSOC);
        $total_pagado = (float)$pagos['total_pagado'];

        // 3. Determinar nuevo estado y lógica de inventario
        $nuevo_estado = 'Borrador';
        $descontar_inventario = false;

        if ($total_pagado >= $total_factura - 0.01) {
            // PAGADA COMPLETA
            $nuevo_estado = 'Pagada';
            // Solo descontamos inventario si NO estaba pagada antes
            if ($estado_actual !== 'Pagada') {
                $descontar_inventario = true;
            }
        } elseif ($total_pagado > 0) {
            // PAGO PARCIAL
            $nuevo_estado = 'Pago Parcial';
        }

        // 4. Aplicar descuento de inventario si corresponde (Solo al pagar completo)
        if ($descontar_inventario) {
            $movimiento_model = new Movimiento($this->conn);
            $referencia_factura = "Factura #" . $correlativo_factura;
            $id_usuario = $_SESSION['user_id'] ?? 0;
            
            // --- CORRECCIÓN: Verificar stock antes de restar ---
            $stmt_check_stock = $this->conn->prepare("SELECT stock_actual, nombre_producto FROM productos WHERE id = ?");
            $stmt_stock = $this->conn->prepare(
                "UPDATE productos SET stock_actual = stock_actual - :cantidad 
                 WHERE id = :id_producto AND es_inventariable = 1"
            );
            
            $stmt_detalle = $this->conn->prepare(
                "SELECT id_item, tipo_item, cantidad 
                 FROM factura_detalle 
                 WHERE id_factura = ?"
            );
            $stmt_detalle->execute([$id_factura]);
            $items_factura = $stmt_detalle->fetchAll(PDO::FETCH_ASSOC);
            
            foreach ($items_factura as $item) {
                if ($item['tipo_item'] === 'Producto') {
                    // Verificar stock actual
                    $stmt_check_stock->execute([$item['id_item']]);
                    $prod = $stmt_check_stock->fetch(PDO::FETCH_ASSOC);
                    
                    if ($prod && $prod['stock_actual'] < $item['cantidad']) {
                        throw new Exception("Error: Stock insuficiente para el producto '{$prod['nombre_producto']}'. Stock actual: {$prod['stock_actual']}, solicitado: {$item['cantidad']}.");
                    }

                    // i) Descontar stock (Ahora es seguro hacerlo)
                    $stmt_stock->execute([
                        ':cantidad' => $item['cantidad'],
                        ':id_producto' => $item['id_item']
                    ]);
                    
                    // ii) Registrar el movimiento en el log
                    $movimiento_model->registrarVenta(
                        $item['id_item'],
                        $item['cantidad'],
                        $id_usuario,
                        $referencia_factura
                    );
                }
            }
        }

        // 5. Actualizar el estado en la base de datos
        if ($nuevo_estado !== $estado_actual) {
            $stmt_update = $this->conn->prepare("UPDATE facturas SET estado = :estado WHERE id = :id");
            $stmt_update->execute([':estado' => $nuevo_estado, ':id' => $id_factura]);
        }
    }

    public function leerPorFactura($id_factura) {
        $query = "SELECT pd.forma_pago, pd.monto, pd.referencia, p.fecha_pago 
                  FROM " . $this->table_name . " p
                  JOIN " . $this->table_detalle . " pd ON p.id = pd.id_pago
                  WHERE p.id_factura = ?
                  ORDER BY p.fecha_pago DESC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$id_factura]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>