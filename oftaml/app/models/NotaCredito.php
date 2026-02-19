<?php
// app/models/NotaCredito.php
class NotaCredito {
    private $conn;
    private $table_name = "notas_credito";

    public function __construct($db) {
        $this->conn = $db;
    }

    public function leer() {
        $query = "SELECT 
                    nc.id, 
                    nc.correlativo, 
                    f.correlativo as factura_asociada, 
                    CONCAT(p.nombres, ' ', p.apellidos) as paciente_nombre,
                    nc.fecha_emision, 
                    nc.total, 
                    nc.estado
                  FROM " . $this->table_name . " nc
                  JOIN facturas f ON nc.id_factura_asociada = f.id
                  JOIN pacientes p ON nc.id_paciente = p.id
                  ORDER BY nc.fecha_emision DESC, nc.id DESC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    // --- NUEVO MÉTODO AÑADIDO ---
    public function leerUno($id) {
        $query = "SELECT 
                    nc.*,
                    f.correlativo as factura_asociada,
                    CONCAT(p.nombres, ' ', p.apellidos) as paciente_nombre,
                    u.nombre_completo as usuario_nombre
                  FROM " . $this->table_name . " nc
                  JOIN facturas f ON nc.id_factura_asociada = f.id
                  JOIN pacientes p ON nc.id_paciente = p.id
                  JOIN usuarios u ON nc.id_usuario = u.id
                  WHERE nc.id = ?
                  LIMIT 0,1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // --- NUEVO MÉTODO AÑADIDO ---
    public function leerDetalle($id_nota_credito) {
        $query = "SELECT 
                    ncd.*,
                    i.nombre_isv,
                    i.porcentaje
                  FROM nota_credito_detalle ncd
                  LEFT JOIN tipos_isv i ON ncd.id_isv = i.id
                  WHERE ncd.id_nota_credito = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $id_nota_credito);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    private function obtenerSiguienteCorrelativo() {
        $config_stmt = $this->conn->prepare("SELECT prefijo_correlativo, siguiente_numero FROM configuracion_notas_credito WHERE id = 1 FOR UPDATE");
        $config_stmt->execute();
        $config = $config_stmt->fetch(PDO::FETCH_ASSOC);
        $prefijo = $config['prefijo_correlativo'];
        $numero = $config['siguiente_numero'];
        $correlativo = $prefijo . str_pad($numero, 5, "0", STR_PAD_LEFT);
        
        $update_stmt = $this->conn->prepare("UPDATE configuracion_notas_credito SET siguiente_numero = siguiente_numero + 1 WHERE id = 1");
        $update_stmt->execute();
        return $correlativo;
    }

    public function crear($data) {
        $this->conn->beginTransaction();
        try {
            $correlativo = $this->obtenerSiguienteCorrelativo();
            
            $query_nc = "INSERT INTO " . $this->table_name . " SET 
                            correlativo=:correlativo, 
                            id_factura_asociada=:id_factura, 
                            id_paciente=:id_paciente, 
                            fecha_emision=:fecha, 
                            motivo=:motivo, 
                            subtotal=:subtotal, 
                            isv_total=:isv, 
                            total=:total, 
                            id_usuario=:id_usuario, 
                            estado='Aplicada'";
            
            $stmt_nc = $this->conn->prepare($query_nc);
            $stmt_nc->execute([
                ':correlativo' => $correlativo,
                ':id_factura' => $data['id_factura_asociada'],
                ':id_paciente' => $data['id_paciente'],
                ':fecha' => $data['fecha_emision'],
                ':motivo' => $data['motivo'],
                ':subtotal' => $data['subtotal'],
                ':isv' => $data['isv_total'],
                ':total' => $data['total'],
                ':id_usuario' => $data['id_usuario']
            ]);
            $id_nota_credito = $this->conn->lastInsertId();

            $query_detalle = "INSERT INTO nota_credito_detalle 
                                (id_nota_credito, tipo_item, id_item, descripcion_item, cantidad, precio_unitario, subtotal, id_isv) 
                              VALUES 
                                (:id_nc, :tipo, :id_item, :desc, :cant, :precio, :subtotal, :id_isv)";
            $stmt_detalle = $this->conn->prepare($query_detalle);
            
            $stmt_stock = $this->conn->prepare("UPDATE productos SET stock_actual = stock_actual + :cantidad WHERE id = :id_producto AND es_inventariable = 1");

            foreach ($data['detalle'] as $item) {
                if ($item['cantidad'] > 0) {
                    $stmt_detalle->execute([
                        ':id_nc' => $id_nota_credito,
                        ':tipo' => $item['tipo_item'],
                        ':id_item' => $item['id_item'],
                        ':desc' => $item['descripcion_item'],
                        ':cant' => $item['cantidad'],
                        ':precio' => $item['precio_unitario'],
                        ':subtotal' => $item['subtotal'],
                        ':id_isv' => $item['id_isv']
                    ]);

                    if ($item['tipo_item'] === 'Producto') {
                        $stmt_stock->execute([
                            ':cantidad' => $item['cantidad'],
                            ':id_producto' => $item['id_item']
                        ]);
                    }
                }
            }

            $this->conn->commit();
            return $id_nota_credito;
        } catch(Exception $e) {
            $this->conn->rollBack();
            error_log("Error al crear nota de crédito: " . $e->getMessage());
            return false;
        }
    }
}
?>