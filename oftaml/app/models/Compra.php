<?php
class Compra {
    private $conn;
    private $table_name = "compras";

    public function __construct($db) {
        $this->conn = $db;
    }
    
    private function obtenerSiguienteCorrelativo() {
        $query = "SELECT MAX(id) as max_id FROM " . $this->table_name;
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $next_id = ($row['max_id'] ?? 0) + 1;
        return "C-" . str_pad($next_id, 5, "0", STR_PAD_LEFT);
    }

    public function leer() {
        $query = "SELECT c.*, p.nombre_proveedor 
                  FROM " . $this->table_name . " c 
                  LEFT JOIN proveedores p ON c.id_proveedor = p.id 
                  ORDER BY c.fecha_compra DESC, c.id DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    public function leerUno($id_compra) {
        $query = "SELECT c.*, p.nombre_proveedor, u.nombre_completo as usuario_nombre
                  FROM " . $this->table_name . " c 
                  LEFT JOIN proveedores p ON c.id_proveedor = p.id 
                  LEFT JOIN usuarios u ON c.id_usuario = u.id
                  WHERE c.id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$id_compra]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function leerDetalle($id_compra) {
        $query = "SELECT cd.*, p.nombre_producto, i.nombre_isv, i.porcentaje 
                  FROM compras_detalle cd 
                  JOIN productos p ON cd.id_producto = p.id
                  LEFT JOIN tipos_isv i ON cd.id_isv = i.id
                  WHERE cd.id_compra = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$id_compra]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function crear($data) {
        $this->conn->beginTransaction();
        try {
            $correlativo = $this->obtenerSiguienteCorrelativo();
            $query_compra = "INSERT INTO " . $this->table_name . " SET correlativo=:correlativo, id_proveedor=:id_proveedor, numero_factura=:factura, numero_orden=:orden, fecha_compra=:fecha, total_compra=:total, id_usuario=:id_usuario, estado='Borrador'";
            $stmt_compra = $this->conn->prepare($query_compra);
            $stmt_compra->bindParam(":correlativo", $correlativo);
            $stmt_compra->bindParam(":id_proveedor", $data['id_proveedor']);
            $stmt_compra->bindParam(":factura", $data['numero_factura']);
            $stmt_compra->bindParam(":orden", $data['numero_orden']);
            $stmt_compra->bindParam(":fecha", $data['fecha_compra']);
            $stmt_compra->bindParam(":total", $data['total_compra']);
            $stmt_compra->bindParam(":id_usuario", $data['id_usuario']);
            $stmt_compra->execute();
            $id_compra = $this->conn->lastInsertId();

            $query_detalle = "INSERT INTO compras_detalle (id_compra, id_producto, cantidad, precio_compra, id_isv) VALUES (:id_compra, :id_producto, :cantidad, :precio, :id_isv)";
            $stmt_detalle = $this->conn->prepare($query_detalle);
            foreach ($data['detalle'] as $item) {
                $stmt_detalle->execute([
                    ':id_compra' => $id_compra,
                    ':id_producto' => $item['id_producto'],
                    ':cantidad' => $item['cantidad'],
                    ':precio' => $item['precio'],
                    ':id_isv' => $item['id_isv']
                ]);
            }

            $this->conn->commit();
            return true;
        } catch(Exception $e) {
            $this->conn->rollBack();
            return false;
        }
    }

    public function actualizar($id_compra, $data) {
        $this->conn->beginTransaction();
        try {
            $query_compra = "UPDATE " . $this->table_name . " SET id_proveedor=:id_proveedor, numero_factura=:factura, numero_orden=:orden, fecha_compra=:fecha, total_compra=:total WHERE id=:id";
            $stmt_compra = $this->conn->prepare($query_compra);
            $stmt_compra->bindParam(":id_proveedor", $data['id_proveedor']);
            $stmt_compra->bindParam(":factura", $data['numero_factura']);
            $stmt_compra->bindParam(":orden", $data['numero_orden']);
            $stmt_compra->bindParam(":fecha", $data['fecha_compra']);
            $stmt_compra->bindParam(":total", $data['total_compra']);
            $stmt_compra->bindParam(":id", $id_compra);
            $stmt_compra->execute();

            $stmt_delete = $this->conn->prepare("DELETE FROM compras_detalle WHERE id_compra = ?");
            $stmt_delete->execute([$id_compra]);

            $query_detalle = "INSERT INTO compras_detalle (id_compra, id_producto, cantidad, precio_compra, id_isv) VALUES (:id_compra, :id_producto, :cantidad, :precio, :id_isv)";
            $stmt_detalle = $this->conn->prepare($query_detalle);
            foreach ($data['detalle'] as $item) {
                $stmt_detalle->execute([
                    ':id_compra' => $id_compra,
                    ':id_producto' => $item['id_producto'],
                    ':cantidad' => $item['cantidad'],
                    ':precio' => $item['precio'],
                    ':id_isv' => $item['id_isv']
                ]);
            }

            $this->conn->commit();
            return true;
        } catch(Exception $e) {
            $this->conn->rollBack();
            return false;
        }
    }
    
    public function cambiarEstado($id_compra, $nuevo_estado) {
        $query = "UPDATE " . $this->table_name . " SET estado = :estado WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":estado", $nuevo_estado);
        $stmt->bindParam(":id", $id_compra);
        return $stmt->execute();
    }
    
    public function recibirCompra($id_compra, $id_usuario) {
        $this->conn->beginTransaction();
        try {
            // Obtener el ID del proveedor Y el correlativo de la compra
            $query_compra_header = "SELECT id_proveedor, correlativo FROM compras WHERE id = ?";
            $stmt_compra_header = $this->conn->prepare($query_compra_header);
            $stmt_compra_header->execute([$id_compra]);
            $compra_header = $stmt_compra_header->fetch(PDO::FETCH_ASSOC);
            $id_proveedor = $compra_header['id_proveedor'];
            $correlativo_compra = $compra_header['correlativo'];

            $query_detalles = "SELECT * FROM compras_detalle WHERE id_compra = ?";
            $stmt_detalles = $this->conn->prepare($query_detalles);
            $stmt_detalles->execute([$id_compra]);
            $detalles = $stmt_detalles->fetchAll(PDO::FETCH_ASSOC);

            $stmt_mov = $this->conn->prepare("INSERT INTO movimientos_inventario (id_producto, tipo_movimiento, cantidad, precio_unitario, id_usuario, notas, id_proveedor) VALUES (?, 'Entrada', ?, ?, ?, ?, ?)");
            $stmt_prod = $this->conn->prepare("UPDATE productos SET stock_actual = stock_actual + ? WHERE id = ?");

            foreach($detalles as $item) {
                // Usar el correlativo en la nota
                $notas = "Entrada por Compra " . $correlativo_compra;
                $stmt_mov->execute([$item['id_producto'], $item['cantidad'], $item['precio_compra'], $id_usuario, $notas, $id_proveedor]);
                $stmt_prod->execute([$item['cantidad'], $item['id_producto']]);
            }
            
            $this->cambiarEstado($id_compra, 'Recibida');

            $this->conn->commit();
            return true;
        } catch (Exception $e) {
            $this->conn->rollBack();
            return false;
        }
    }
}
?>
