<?php
class Cotizacion {
    private $conn;
    private $table_name = "cotizaciones";

    public function __construct($db) {
        $this->conn = $db;
    }

    private function obtenerSiguienteCorrelativo() {
        $query = "SELECT MAX(id) as max_id FROM " . $this->table_name;
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $next_id = ($row['max_id'] ?? 0) + 1;
        return "COT-" . str_pad($next_id, 5, "0", STR_PAD_LEFT);
    }

    public function leer($startDate = null, $endDate = null) {
        $query = "SELECT c.*, CONCAT(p.nombres, ' ', p.apellidos) as paciente_nombre 
                  FROM " . $this->table_name . " c 
                  JOIN pacientes p ON c.id_paciente = p.id";
        
        $conditions = [];
        $params = [];

        if ($startDate && $endDate) {
            $conditions[] = "c.fecha_emision BETWEEN :startDate AND :endDate";
            $params[':startDate'] = $startDate;
            $params[':endDate'] = $endDate;
        }

        if (count($conditions) > 0) {
            $query .= " WHERE " . implode(' AND ', $conditions);
        }
        
        $query .= " ORDER BY c.fecha_emision DESC, c.id DESC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute($params);
        return $stmt;
    }

    public function leerUno($id_cotizacion) {
        $query = "SELECT c.*, CONCAT(p.nombres, ' ', p.apellidos) as paciente_nombre, u.nombre_completo as usuario_nombre
                  FROM " . $this->table_name . " c 
                  JOIN pacientes p ON c.id_paciente = p.id 
                  JOIN usuarios u ON c.id_usuario = u.id
                  WHERE c.id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$id_cotizacion]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function leerDetalle($id_cotizacion) {
        $query = "SELECT cd.*, i.nombre_isv, i.porcentaje
                  FROM cotizacion_detalle cd
                  LEFT JOIN tipos_isv i ON cd.id_isv = i.id
                  WHERE cd.id_cotizacion = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$id_cotizacion]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function crear($data) {
        $this->conn->beginTransaction();
        try {
            $correlativo = $this->obtenerSiguienteCorrelativo();
            $query_cotizacion = "INSERT INTO " . $this->table_name . " SET correlativo=:correlativo, id_paciente=:id_paciente, fecha_emision=:fecha, fecha_vencimiento=:vencimiento, subtotal=:subtotal, isv_total=:isv, descuento_total=:descuento, total=:total, id_usuario=:id_usuario, estado='Borrador', notas=:notas";
            $stmt_cotizacion = $this->conn->prepare($query_cotizacion);
            $stmt_cotizacion->execute([
                ':correlativo' => $correlativo,
                ':id_paciente' => $data['id_paciente'],
                ':fecha' => $data['fecha_emision'],
                ':vencimiento' => $data['fecha_vencimiento'],
                ':subtotal' => $data['subtotal'],
                ':isv' => $data['isv_total'],
                ':descuento' => $data['descuento_total'],
                ':total' => $data['total'],
                ':id_usuario' => $data['id_usuario'],
                ':notas' => $data['notas']
            ]);
            $id_cotizacion = $this->conn->lastInsertId();

            $query_detalle = "INSERT INTO cotizacion_detalle (id_cotizacion, tipo_item, id_item, descripcion_item, cantidad, precio_unitario, descuento, id_isv) VALUES (:id_cotizacion, :tipo, :id_item, :desc, :cant, :precio, :desc_item, :id_isv)";
            $stmt_detalle = $this->conn->prepare($query_detalle);

            foreach ($data['detalle'] as $item) {
                $stmt_detalle->execute([
                    ':id_cotizacion' => $id_cotizacion,
                    ':tipo' => $item['tipo'],
                    ':id_item' => $item['id'],
                    ':desc' => $item['descripcion'],
                    ':cant' => $item['cantidad'],
                    ':precio' => $item['precio'],
                    ':desc_item' => $item['descuento'],
                    ':id_isv' => $item['id_isv']
                ]);
            }

            $this->conn->commit();
            return $id_cotizacion;
        } catch(Exception $e) {
            $this->conn->rollBack();
            error_log($e->getMessage());
            return false;
        }
    }

    public function actualizar($id_cotizacion, $data) {
        $this->conn->beginTransaction();
        try {
            $query_cotizacion = "UPDATE " . $this->table_name . " SET id_paciente=:id_paciente, fecha_emision=:fecha, fecha_vencimiento=:vencimiento, subtotal=:subtotal, isv_total=:isv, descuento_total=:descuento, total=:total, notas=:notas WHERE id=:id";
            $stmt_cotizacion = $this->conn->prepare($query_cotizacion);
            $stmt_cotizacion->execute([
                ':id_paciente' => $data['id_paciente'],
                ':fecha' => $data['fecha_emision'],
                ':vencimiento' => $data['fecha_vencimiento'],
                ':subtotal' => $data['subtotal'],
                ':isv' => $data['isv_total'],
                ':descuento' => $data['descuento_total'],
                ':total' => $data['total'],
                ':notas' => $data['notas'],
                ':id' => $id_cotizacion
            ]);

            $stmt_delete = $this->conn->prepare("DELETE FROM cotizacion_detalle WHERE id_cotizacion = ?");
            $stmt_delete->execute([$id_cotizacion]);

            $query_detalle = "INSERT INTO cotizacion_detalle (id_cotizacion, tipo_item, id_item, descripcion_item, cantidad, precio_unitario, descuento, id_isv) VALUES (:id_cotizacion, :tipo, :id_item, :desc, :cant, :precio, :desc_item, :id_isv)";
            $stmt_detalle = $this->conn->prepare($query_detalle);
            foreach ($data['detalle'] as $item) {
                $stmt_detalle->execute([
                    ':id_cotizacion' => $id_cotizacion,
                    ':tipo' => $item['tipo'],
                    ':id_item' => $item['id'],
                    ':desc' => $item['descripcion'],
                    ':cant' => $item['cantidad'],
                    ':precio' => $item['precio'],
                    ':desc_item' => $item['descuento'],
                    ':id_isv' => $item['id_isv']
                ]);
            }
            $this->conn->commit();
            return true;
        } catch(Exception $e) {
            $this->conn->rollBack();
            error_log($e->getMessage());
            return false;
        }
    }

    public function eliminar($id_cotizacion) {
        $this->conn->beginTransaction();
        try {
            $stmt_delete_det = $this->conn->prepare("DELETE FROM cotizacion_detalle WHERE id_cotizacion = ?");
            $stmt_delete_det->execute([$id_cotizacion]);

            $stmt_delete_cot = $this->conn->prepare("DELETE FROM " . $this->table_name . " WHERE id = ?");
            $stmt_delete_cot->execute([$id_cotizacion]);

            $this->conn->commit();
            return true;
        } catch (Exception $e) {
            $this->conn->rollBack();
            error_log($e->getMessage());
            return false;
        }
    }


    public function convertirAFactura($id_cotizacion, $id_usuario) {
        $stmt_config = $this->conn->prepare("SELECT prefijo_correlativo, siguiente_numero FROM configuracion_facturacion WHERE id = 1");
        $stmt_config->execute();
        $config_factura = $stmt_config->fetch(PDO::FETCH_ASSOC);
        $correlativo_factura = $config_factura['prefijo_correlativo'] . str_pad($config_factura['siguiente_numero'], 5, "0", STR_PAD_LEFT);

        $this->conn->beginTransaction();
        try {
            $cotizacion = $this->leerUno($id_cotizacion);
            if (!$cotizacion) {
                throw new Exception("Cotización no encontrada.");
            }

            $query_factura = "INSERT INTO facturas (correlativo, id_paciente, fecha_emision, subtotal, isv_total, descuento_total, total, estado, id_usuario) 
                              VALUES (:correlativo, :id_paciente, CURDATE(), :subtotal, :isv_total, :descuento_total, :total, 'Borrador', :id_usuario)";
            $stmt_factura = $this->conn->prepare($query_factura);
            $stmt_factura->execute([
                ':correlativo' => $correlativo_factura,
                ':id_paciente' => $cotizacion['id_paciente'],
                ':subtotal' => $cotizacion['subtotal'],
                ':isv_total' => $cotizacion['isv_total'],
                ':descuento_total' => $cotizacion['descuento_total'],
                ':total' => $cotizacion['total'],
                ':id_usuario' => $id_usuario
            ]);
            $id_factura = $this->conn->lastInsertId();

            $detalle_cotizacion = $this->leerDetalle($id_cotizacion);
            $query_detalle = "INSERT INTO factura_detalle (id_factura, tipo_item, id_item, descripcion_item, cantidad, precio_unitario, descuento, id_isv) 
                              VALUES (:id_factura, :tipo_item, :id_item, :descripcion_item, :cantidad, :precio_unitario, :descuento, :id_isv)";
            $stmt_detalle = $this->conn->prepare($query_detalle);

            foreach ($detalle_cotizacion as $item) {
                $stmt_detalle->execute([
                    ':id_factura' => $id_factura,
                    ':tipo_item' => $item['tipo_item'],
                    ':id_item' => $item['id_item'],
                    ':descripcion_item' => $item['descripcion_item'],
                    ':cantidad' => $item['cantidad'],
                    ':precio_unitario' => $item['precio_unitario'],
                    ':descuento' => $item['descuento'],
                    ':id_isv' => $item['id_isv']
                ]);
            }

            $query_update_cot = "UPDATE " . $this->table_name . " SET estado = 'Facturada' WHERE id = ?";
            $stmt_update_cot = $this->conn->prepare($query_update_cot);
            $stmt_update_cot->execute([$id_cotizacion]);
            
            $query_update_config = "UPDATE configuracion_facturacion SET siguiente_numero = siguiente_numero + 1 WHERE id = 1";
            $this->conn->prepare($query_update_config)->execute();

            $this->conn->commit();
            return $id_factura;
        } catch (Exception $e) {
            $this->conn->rollBack();
            error_log($e->getMessage());
            return false;
        }
    }
}
?>