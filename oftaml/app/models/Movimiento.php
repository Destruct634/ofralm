<?php
class Movimiento {
    private $conn;
    private $table_name = "movimientos_inventario";

    public function __construct($db) {
        $this->conn = $db;
    }

    public function leer() {
        $query = "SELECT 
                    m.id, m.tipo_movimiento, m.cantidad, m.fecha_movimiento, m.notas,
                    p.nombre_producto,
                    u.nombre_completo as usuario,
                    pr.nombre_proveedor
                  FROM " . $this->table_name . " m
                  LEFT JOIN productos p ON m.id_producto = p.id
                  LEFT JOIN usuarios u ON m.id_usuario = u.id
                  LEFT JOIN proveedores pr ON m.id_proveedor = pr.id
                  ORDER BY m.fecha_movimiento DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }
    
    public function crearAjuste($data) {
        $diferencia = $data['cantidad'];
        if ($diferencia == 0) { return true; }

        $this->conn->beginTransaction();
        try {
            $query_mov = "INSERT INTO " . $this->table_name . " SET 
                          id_producto=:id_producto, tipo_movimiento='Ajuste', cantidad=:cantidad,
                          id_usuario=:id_usuario, notas=:notas";
            $stmt_mov = $this->conn->prepare($query_mov);
            $stmt_mov->execute([
                ':id_producto' => $data['id_producto'],
                ':cantidad' => $diferencia,
                ':id_usuario' => $data['id_usuario'],
                ':notas' => $data['notas']
            ]);

            $query_prod = "UPDATE productos SET stock_actual = stock_actual + :cantidad 
                           WHERE id = :id_producto";
            $stmt_prod = $this->conn->prepare($query_prod);
            $stmt_prod->execute([
                ':cantidad' => $diferencia,
                ':id_producto' => $data['id_producto']
            ]);

            $this->conn->commit();
            return true;
        } catch (Exception $e) {
            $this->conn->rollBack();
            return false;
        }
    }

    public function crearEntrada($data) {
        $this->conn->beginTransaction();
        try {
            $query_mov = "INSERT INTO " . $this->table_name . " SET 
                          id_producto=:id_producto, tipo_movimiento='Entrada', cantidad=:cantidad,
                          precio_unitario=:precio_unitario, id_usuario=:id_usuario,
                          notas=:notas";
            $stmt_mov = $this->conn->prepare($query_mov);
            
            $query_prod = "UPDATE productos SET stock_actual = stock_actual + :cantidad 
                           WHERE id = :id_producto";
            $stmt_prod = $this->conn->prepare($query_prod);

            foreach ($data['detalle'] as $item) {
                $stmt_mov->execute([
                    ':id_producto' => $item['id_producto'],
                    ':cantidad' => $item['cantidad'],
                    ':precio_unitario' => $item['precio_unitario'],
                    ':id_usuario' => $data['id_usuario'],
                    ':notas' => $data['notas']
                ]);
                $stmt_prod->execute([
                    ':cantidad' => $item['cantidad'],
                    ':id_producto' => $item['id_producto']
                ]);
            }

            $this->conn->commit();
            return true;
        } catch (Exception $e) {
            $this->conn->rollBack();
            return false;
        }
    }

    public function crearSalida($data) {
        $this->conn->beginTransaction();
        try {
            $query_mov = "INSERT INTO " . $this->table_name . " SET 
                          id_producto=:id_producto, tipo_movimiento='Salida', cantidad=:cantidad,
                          id_usuario=:id_usuario, notas=:notas";
            $stmt_mov = $this->conn->prepare($query_mov);
            
            $query_prod = "UPDATE productos SET stock_actual = stock_actual - :cantidad 
                           WHERE id = :id_producto";
            $stmt_prod = $this->conn->prepare($query_prod);

            foreach ($data['detalle'] as $item) {
                $check_stock_q = "SELECT stock_actual FROM productos WHERE id = ?";
                $stmt_check = $this->conn->prepare($check_stock_q);
                $stmt_check->execute([$item['id_producto']]);
                $producto = $stmt_check->fetch(PDO::FETCH_ASSOC);
                if ($producto['stock_actual'] < $item['cantidad']) {
                    throw new Exception("Stock insuficiente para uno de los productos.");
                }
            }

            foreach ($data['detalle'] as $item) {
                $stmt_mov->execute([
                    ':id_producto' => $item['id_producto'],
                    ':cantidad' => $item['cantidad'],
                    ':id_usuario' => $data['id_usuario'],
                    ':notas' => $data['notas']
                ]);
                $stmt_prod->execute([
                    ':cantidad' => $item['cantidad'],
                    ':id_producto' => $item['id_producto']
                ]);
            }

            $this->conn->commit();
            return true;
        } catch (Exception $e) {
            $this->conn->rollBack();
            throw $e;
        }
    }

    // --- INICIO DE NUEVA FUNCIÓN PARA FACTURACIÓN ---
    /**
     * Registra un movimiento de 'Venta' en el log de inventario.
     */
    public function registrarVenta($id_producto, $cantidad, $id_usuario, $referencia) {
        $cantidad_negativa = $cantidad * -1;
        $fecha_actual = date('Y-m-d H:i:s');

        try {
            $query_mov = "INSERT INTO " . $this->table_name . " SET 
                          id_producto=:id_producto, 
                          tipo_movimiento='Venta', 
                          cantidad=:cantidad,
                          id_usuario=:id_usuario, 
                          notas=:notas,
                          fecha_movimiento=:fecha_movimiento";
            
            $stmt_mov = $this->conn->prepare($query_mov);
            
            return $stmt_mov->execute([
                ':id_producto' => $id_producto,
                ':cantidad' => $cantidad_negativa,
                ':id_usuario' => $id_usuario,
                ':notas' => $referencia,
                ':fecha_movimiento' => $fecha_actual
            ]);
        } catch (Exception $e) {
            return false;
        }
    }
    // --- FIN DE NUEVA FUNCIÓN ---
}