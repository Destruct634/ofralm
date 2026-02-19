<?php
class Factura {
    private $conn;
    private $table_name = "facturas";

    public function __construct($db) {
        $this->conn = $db;
        include_once __DIR__ . '/Movimiento.php';
    }

    private function obtenerSiguienteCorrelativo() {
        include_once __DIR__ . '/ConfiguracionFacturacion.php'; 
        $config_facturacion_model = new ConfiguracionFacturacion($this->conn);
        $config = $config_facturacion_model->leer();
        $prefijo = $config['prefijo_correlativo'];
        $numero = $config['siguiente_numero'];
        $correlativo = $prefijo . str_pad($numero, 5, "0", STR_PAD_LEFT);
        $update_stmt = $this->conn->prepare("UPDATE configuracion_facturacion SET siguiente_numero = siguiente_numero + 1 WHERE id = 1");
        $update_stmt->execute();
        return $correlativo;
    }

    public function leer($startDate = null, $endDate = null, $estado = null) {
        $query = "SELECT f.*, CONCAT(p.nombres, ' ', p.apellidos) as paciente_nombre 
                  FROM " . $this->table_name . " f 
                  JOIN pacientes p ON f.id_paciente = p.id";
        $conditions = [];
        $params = [];
        if ($startDate && $endDate) {
            $conditions[] = "DATE(f.fecha_emision) BETWEEN :startDate AND :endDate";
            $params[':startDate'] = $startDate;
            $params[':endDate'] = $endDate;
        }
        if ($estado) {
            $conditions[] = "f.estado = :estado";
            $params[':estado'] = $estado;
        }
        if (count($conditions) > 0) {
            $query .= " WHERE " . implode(' AND ', $conditions);
        }
        $query .= " ORDER BY f.fecha_emision DESC, f.id DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute($params);
        return $stmt;
    }
    
    public function leerCuentasPorCobrar() {
        $query = "SELECT 
                    f.id,
                    f.correlativo,
                    f.fecha_emision,
                    f.total,
                    CONCAT(p.nombres, ' ', p.apellidos) as paciente_nombre,
                    p.telefono as paciente_telefono,
                    COALESCE((SELECT SUM(pd.monto) FROM pagos pg JOIN pago_detalle pd ON pg.id = pd.id_pago WHERE pg.id_factura = f.id), 0) as total_pagado,
                    (f.total - COALESCE((SELECT SUM(pd.monto) FROM pagos pg JOIN pago_detalle pd ON pg.id = pd.id_pago WHERE pg.id_factura = f.id), 0)) as saldo_pendiente
                  FROM 
                    facturas f
                  JOIN 
                    pacientes p ON f.id_paciente = p.id
                  WHERE 
                    f.estado != 'Anulada'
                  GROUP BY 
                    f.id
                  HAVING 
                    saldo_pendiente > 0.01
                  ORDER BY 
                    f.fecha_emision ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    public function leerUno($id_factura) {
        $query = "SELECT f.*, 
                    CONCAT(p.nombres, ' ', p.apellidos) as paciente_nombre, 
                    u.nombre_completo as usuario_nombre,
                    CONCAT(m.nombres, ' ', m.apellidos) as medico_nombre,
                    ut.nombre_completo as tecnico_nombre
                  FROM " . $this->table_name . " f 
                  JOIN pacientes p ON f.id_paciente = p.id 
                  JOIN usuarios u ON f.id_usuario = u.id
                  LEFT JOIN medicos m ON f.id_medico = m.id
                  LEFT JOIN usuarios ut ON f.id_tecnico = ut.id
                  WHERE f.id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$id_factura]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function leerDetalle($id_factura) {
        $query = "SELECT fd.*, i.nombre_isv, i.porcentaje
                  FROM factura_detalle fd
                  LEFT JOIN tipos_isv i ON fd.id_isv = i.id
                  WHERE fd.id_factura = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$id_factura]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function crear($data) {
        // --- CORRECCIÓN: Verificar si ya existe transacción ---
        $nested = $this->conn->inTransaction();
        if (!$nested) $this->conn->beginTransaction();
        
        try {
            $correlativo = $this->obtenerSiguienteCorrelativo(); 
            
            $query_factura = "INSERT INTO " . $this->table_name . " SET 
                                correlativo=:correlativo, id_paciente=:id_paciente, id_medico=:id_medico, id_tecnico=:id_tecnico, 
                                fecha_emision=:fecha, subtotal=:subtotal, isv_total=:isv, 
                                descuento_total=:descuento, total=:total, id_usuario=:id_usuario, 
                                estado='Borrador'";

            $stmt_factura = $this->conn->prepare($query_factura);
            
            $stmt_factura->execute([
                ':correlativo' => $correlativo,
                ':id_paciente' => $data['id_paciente'],
                ':id_medico' => $data['id_medico'],
                ':id_tecnico' => $data['id_tecnico'],
                ':fecha' => $data['fecha_emision'],
                ':subtotal' => $data['subtotal'],
                ':isv' => $data['isv_total'],
                ':descuento' => $data['descuento_total'],
                ':total' => $data['total'],
                ':id_usuario' => $data['id_usuario']
            ]);
            
            $id_factura = $this->conn->lastInsertId();

            $query_detalle = "INSERT INTO factura_detalle 
                              (id_factura, tipo_item, id_item, descripcion_item, cantidad, precio_unitario, descuento, id_isv) 
                              VALUES 
                              (:id_factura, :tipo, :id_item, :descripcion, :cantidad, :precio, :descuento, :id_isv)";
            
            $stmt_detalle = $this->conn->prepare($query_detalle);
            
            foreach ($data['detalle'] as $item) {
                $stmt_detalle->execute([
                    ':id_factura' => $id_factura,
                    ':tipo' => $item['tipo'],
                    ':id_item' => $item['id'],
                    ':descripcion' => $item['descripcion'],
                    ':cantidad' => $item['cantidad'],
                    ':precio' => $item['precio'],
                    ':descuento' => $item['descuento'],
                    ':id_isv' => $item['id_isv']
                ]);
            }

            if (!$nested) $this->conn->commit();
            return $id_factura;
        } catch(Exception $e) {
            if (!$nested) $this->conn->rollBack();
            return false;
        }
    }
    
    public function crearDesdeHistorial($data) {
        try {
            $correlativo = $this->obtenerSiguienteCorrelativo();
            $query_factura = "INSERT INTO " . $this->table_name . " SET 
                                correlativo=:correlativo, id_paciente=:id_paciente, id_medico=:id_medico, id_tecnico=:id_tecnico,
                                fecha_emision=:fecha, subtotal=:subtotal, isv_total=:isv, 
                                descuento_total=:descuento, total=:total, id_usuario=:id_usuario, 
                                estado='Borrador'";

            $stmt_factura = $this->conn->prepare($query_factura);
            
            $stmt_factura->execute([
                ':correlativo' => $correlativo,
                ':id_paciente' => $data['id_paciente'],
                ':id_medico' => $data['id_medico'],
                ':id_tecnico' => $data['id_tecnico'],
                ':fecha' => $data['fecha_emision'],
                ':subtotal' => $data['subtotal'],
                ':isv' => $data['isv_total'],
                ':descuento' => $data['descuento_total'],
                ':total' => $data['total'],
                ':id_usuario' => $data['id_usuario']
            ]);

            $id_factura = $this->conn->lastInsertId();

            $query_detalle = "INSERT INTO factura_detalle 
                              (id_factura, tipo_item, id_item, descripcion_item, cantidad, precio_unitario, descuento, id_isv) 
                              VALUES 
                              (:id_factura, :tipo, :id_item, :descripcion, :cantidad, :precio, :descuento, :id_isv)";
            
            $stmt_detalle = $this->conn->prepare($query_detalle);
            
            foreach ($data['detalle'] as $item) {
                $stmt_detalle->execute([
                    ':id_factura' => $id_factura,
                    ':tipo' => $item['tipo'],
                    ':id_item' => $item['id'],
                    ':descripcion' => $item['descripcion'],
                    ':cantidad' => $item['cantidad'],
                    ':precio' => $item['precio'],
                    ':descuento' => $item['descuento'],
                    ':id_isv' => $item['id_isv']
                ]);
            }
            
            return $id_factura;
        
        } catch(Exception $e) {
            throw $e;
        }
    }

    public function actualizar($id_factura, $data) {
        // --- CORRECCIÓN: Verificar si ya existe transacción ---
        $nested = $this->conn->inTransaction();
        if (!$nested) $this->conn->beginTransaction();
        
        try {
            $query_factura = "UPDATE " . $this->table_name . " SET 
                                id_paciente=:id_paciente, id_medico=:id_medico, id_tecnico=:id_tecnico, 
                                fecha_emision=:fecha, subtotal=:subtotal, isv_total=:isv, 
                                descuento_total=:descuento, total=:total
                              WHERE id=:id";
            $stmt_factura = $this->conn->prepare($query_factura);
            
            $stmt_factura->execute([
                ':id' => $id_factura,
                ':id_paciente' => $data['id_paciente'],
                ':id_medico' => $data['id_medico'],
                ':id_tecnico' => $data['id_tecnico'],
                ':fecha' => $data['fecha_emision'],
                ':subtotal' => $data['subtotal'],
                ':isv' => $data['isv_total'],
                ':descuento' => $data['descuento_total'],
                ':total' => $data['total']
            ]);

            $stmt_delete = $this->conn->prepare("DELETE FROM factura_detalle WHERE id_factura = ?");
            $stmt_delete->execute([$id_factura]);

            $query_detalle = "INSERT INTO factura_detalle 
                              (id_factura, tipo_item, id_item, descripcion_item, cantidad, precio_unitario, descuento, id_isv) 
                              VALUES 
                              (:id_factura, :tipo, :id_item, :descripcion, :cantidad, :precio, :descuento, :id_isv)";
            
            $stmt_detalle = $this->conn->prepare($query_detalle);
            
            foreach ($data['detalle'] as $item) {
                $stmt_detalle->execute([
                    ':id_factura' => $id_factura,
                    ':tipo' => $item['tipo'],
                    ':id_item' => $item['id'],
                    ':descripcion' => $item['descripcion'],
                    ':cantidad' => $item['cantidad'],
                    ':precio' => $item['precio'],
                    ':descuento' => $item['descuento'],
                    ':id_isv' => $item['id_isv']
                ]);
            }

            if (!$nested) $this->conn->commit();
            return true;
        } catch(Exception $e) {
            if (!$nested) $this->conn->rollBack();
            // Si es anidada, lanzamos la excepción para que el padre se entere y haga rollback general
            if ($nested) throw $e;
            return false;
        }
    }
    
    public function cambiarEstado($id_factura, $nuevo_estado) {
        $nested = $this->conn->inTransaction();
        if (!$nested) $this->conn->beginTransaction();

        try {
            // 1. Obtener estado actual
            $stmt_actual = $this->conn->prepare("SELECT estado, correlativo FROM " . $this->table_name . " WHERE id = ?");
            $stmt_actual->execute([$id_factura]);
            $actual = $stmt_actual->fetch(PDO::FETCH_ASSOC);
            
            if (!$actual) throw new Exception("Factura no encontrada");

            $estado_anterior = $actual['estado'];

            // 2. Si pasa de 'Pagada' a 'Anulada', devolver stock
            if ($estado_anterior === 'Pagada' && $nuevo_estado === 'Anulada') {
                
                $movimiento_model = new Movimiento($this->conn);
                $id_usuario = $_SESSION['user_id'] ?? 0;
                $notas = "Anulación de Factura " . $actual['correlativo'];

                // Obtener items de la factura
                $stmt_items = $this->conn->prepare("SELECT id_item, cantidad, tipo_item FROM factura_detalle WHERE id_factura = ?");
                $stmt_items->execute([$id_factura]);
                $items = $stmt_items->fetchAll(PDO::FETCH_ASSOC);

                $stmt_stock = $this->conn->prepare("UPDATE productos SET stock_actual = stock_actual + :cantidad WHERE id = :id AND es_inventariable = 1");

                foreach ($items as $item) {
                    if ($item['tipo_item'] === 'Producto') {
                        // Devolver Stock
                        $stmt_stock->execute([
                            ':cantidad' => $item['cantidad'],
                            ':id' => $item['id_item']
                        ]);

                        // Registrar Movimiento de "Entrada" por Anulación
                        $stmt_log = $this->conn->prepare("INSERT INTO movimientos_inventario 
                            (id_producto, tipo_movimiento, cantidad, precio_unitario, id_usuario, notas, fecha_movimiento) 
                            VALUES (?, 'Entrada', ?, 0, ?, ?, NOW())");
                        
                        $stmt_log->execute([
                            $item['id_item'], 
                            $item['cantidad'], 
                            $id_usuario, 
                            $notas
                        ]);
                    }
                }
            }

            // 3. Actualizar estado
            $query = "UPDATE " . $this->table_name . " SET estado = :estado WHERE id = :id";
            $stmt = $this->conn->prepare($query);
            $stmt->execute([':estado' => $nuevo_estado, ':id' => $id_factura]);

            if (!$nested) $this->conn->commit();
            return true;

        } catch (Exception $e) {
            if (!$nested) $this->conn->rollBack();
            error_log("Error al cambiar estado de factura: " . $e->getMessage());
            return false;
        }
    }
    
    public function eliminar($id) {
        $nested = $this->conn->inTransaction();
        if (!$nested) $this->conn->beginTransaction();

        try {
            $stmt_detalle = $this->conn->prepare("DELETE FROM factura_detalle WHERE id_factura = ?");
            $stmt_detalle->execute([$id]);
            $stmt_pagos = $this->conn->prepare("DELETE FROM pagos WHERE id_factura = ?");
            $stmt_pagos->execute([$id]);
            $stmt_factura = $this->conn->prepare("DELETE FROM " . $this->table_name . " WHERE id = ?");
            if (!$stmt_factura->execute([$id])) { throw new Exception("Error al eliminar factura."); }
            
            if (!$nested) $this->conn->commit(); 
            return true;
        } catch (Exception $e) { 
            if (!$nested) $this->conn->rollBack(); 
            return false; 
        }
    }
    public function search($term) {
        $query = "SELECT f.id, f.correlativo, CONCAT(f.correlativo, ' - ', CONCAT(p.nombres, ' ', p.apellidos)) as text FROM " . $this->table_name . " f JOIN pacientes p ON f.id_paciente = p.id WHERE f.correlativo LIKE :term OR CONCAT(p.nombres, ' ', p.apellidos) LIKE :term LIMIT 10";
        $stmt = $this->conn->prepare($query); $searchTerm = "%" . $term . "%"; $stmt->bindParam(":term", $searchTerm); $stmt->execute(); return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    public function sumarFacturacionHoy() {
        $query = "SELECT COALESCE(SUM(total), 0) as total_dia FROM " . $this->table_name . " WHERE DATE(fecha_emision) = CURDATE() AND estado = 'Pagada'";
        $stmt = $this->conn->prepare($query); $stmt->execute(); return $stmt->fetchColumn();
    }
    public function obtenerSaldoTotalCuentasPorCobrar() {
        $query = "SELECT COALESCE(SUM(f.total - COALESCE((SELECT SUM(pd.monto) FROM pagos pg JOIN pago_detalle pd ON pg.id = pd.id_pago WHERE pg.id_factura = f.id), 0)), 0) as saldo_total FROM facturas f WHERE f.estado != 'Anulada' HAVING saldo_total > 0";
        $stmt = $this->conn->prepare($query); $stmt->execute(); return $stmt->fetchColumn() ?: 0;
    }
    public function obtenerIngresosUltimos7Dias() {
        $query = "SELECT d.fecha, COALESCE(SUM(f.total), 0) as total FROM ( SELECT CURDATE() - INTERVAL (a.a + (10 * b.a) + (100 * c.a)) DAY as fecha FROM (SELECT 0 as a UNION ALL SELECT 1 UNION ALL SELECT 2 UNION ALL SELECT 3 UNION ALL SELECT 4 UNION ALL SELECT 5 UNION ALL SELECT 6 UNION ALL SELECT 7 UNION ALL SELECT 8 UNION ALL SELECT 9) as a CROSS JOIN (SELECT 0 as a UNION ALL SELECT 1 UNION ALL SELECT 2 UNION ALL SELECT 3 UNION ALL SELECT 4 UNION ALL SELECT 5 UNION ALL SELECT 6 UNION ALL SELECT 7 UNION ALL SELECT 8 UNION ALL SELECT 9) as b CROSS JOIN (SELECT 0 as a UNION ALL SELECT 1 UNION ALL SELECT 2 UNION ALL SELECT 3 UNION ALL SELECT 4 UNION ALL SELECT 5 UNION ALL SELECT 6 UNION ALL SELECT 7 UNION ALL SELECT 8 UNION ALL SELECT 9) as c ) d LEFT JOIN facturas f ON DATE(f.fecha_emision) = d.fecha AND f.estado = 'Pagada' WHERE d.fecha BETWEEN CURDATE() - INTERVAL 6 DAY AND CURDATE() GROUP BY d.fecha ORDER BY d.fecha ASC";
        $stmt = $this->conn->prepare($query); $stmt->execute(); return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    public function obtenerIngresosMensualesPorCategoria() {
        $query = "SELECT YEAR(f.fecha_emision) as anio, MONTH(f.fecha_emision) as mes, fd.tipo_item, SUM(fd.cantidad * fd.precio_unitario - fd.descuento) as total_mes FROM factura_detalle fd JOIN facturas f ON fd.id_factura = f.id WHERE f.estado = 'Pagada' AND f.fecha_emision >= DATE_SUB(CURDATE(), INTERVAL 12 MONTH) GROUP BY YEAR(f.fecha_emision), MONTH(f.fecha_emision), fd.tipo_item ORDER BY anio ASC, mes ASC";
        $stmt = $this->conn->prepare($query); $stmt->execute(); $raw_data = $stmt->fetchAll(PDO::FETCH_ASSOC); $results = []; foreach ($raw_data as $row) { $label = date("M Y", mktime(0, 0, 0, $row['mes'], 1, $row['anio'])); if (!isset($results[$label])) { $results[$label] = ['label' => $label, 'Producto' => 0, 'Servicio' => 0]; } $results[$label][$row['tipo_item']] = (float)$row['total_mes']; } $final_data = []; for ($i = 11; $i >= 0; $i--) { $date = new DateTime("first day of -$i months"); $label = $date->format("M Y"); if (isset($results[$label])) { $final_data[] = $results[$label]; } else { $final_data[] = ['label' => $label, 'Producto' => 0, 'Servicio' => 0]; } } return $final_data;
    }

    // --- IMPORTANTE: Mantenemos esta función que agregamos en la versión anterior para evitar duplicados ---
    public function buscarBorradorDelDia($id_paciente, $id_medico, $fecha_cita) {
        $query = "SELECT id FROM " . $this->table_name . " 
                  WHERE id_paciente = :id_paciente 
                  AND id_medico = :id_medico 
                  AND DATE(fecha_emision) = DATE(:fecha) 
                  AND estado = 'Borrador' 
                  LIMIT 1";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute([
            ':id_paciente' => $id_paciente,
            ':id_medico' => $id_medico,
            ':fecha' => $fecha_cita
        ]);
        
        if ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            return $row['id'];
        }
        return false;
    }
}
?>