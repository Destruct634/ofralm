<?php
class ReporteMedico {
    private $conn;

    public function __construct($db) {
        $this->conn = $db;
    }

    // 1. Citas por Tipo de Servicio
    public function obtenerCitasPorServicio($medico_id, $fecha_inicio, $fecha_fin) {
        $query = "SELECT s.nombre_servicio, COUNT(c.id) as total
                  FROM citas c
                  JOIN servicios s ON c.id_servicio = s.id
                  WHERE c.id_medico = :medico_id 
                  AND c.estado != 'Cancelada'
                  AND c.fecha_cita BETWEEN :inicio AND :fin
                  GROUP BY s.nombre_servicio";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute([':medico_id' => $medico_id, ':inicio' => $fecha_inicio, ':fin' => $fecha_fin]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // 2. Ingresos Facturados por el Médico
    public function obtenerIngresos($medico_id, $fecha_inicio, $fecha_fin) {
        // Usamos la columna id_medico de la tabla facturas
        $query = "SELECT SUM(total) as total_facturado
                  FROM facturas
                  WHERE id_medico = :medico_id
                  AND estado = 'Pagada'
                  AND DATE(fecha_emision) BETWEEN :inicio AND :fin";

        $stmt = $this->conn->prepare($query);
        $stmt->execute([':medico_id' => $medico_id, ':inicio' => $fecha_inicio, ':fin' => $fecha_fin]);
        $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
        return $resultado['total_facturado'] ? $resultado['total_facturado'] : 0;
    }

    // 3. Desglose de Ingresos por Día (Para gráfico de línea)
    public function obtenerIngresosPorDia($medico_id, $fecha_inicio, $fecha_fin) {
        $query = "SELECT DATE(fecha_emision) as fecha, SUM(total) as total
                  FROM facturas
                  WHERE id_medico = :medico_id
                  AND estado = 'Pagada'
                  AND DATE(fecha_emision) BETWEEN :inicio AND :fin
                  GROUP BY DATE(fecha_emision)
                  ORDER BY fecha ASC";

        $stmt = $this->conn->prepare($query);
        $stmt->execute([':medico_id' => $medico_id, ':inicio' => $fecha_inicio, ':fin' => $fecha_fin]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // 4. Presentismo (Estados de Citas)
    public function obtenerPresentismo($medico_id, $fecha_inicio, $fecha_fin) {
        $query = "SELECT estado, COUNT(*) as cantidad
                  FROM citas
                  WHERE id_medico = :medico_id
                  AND fecha_cita BETWEEN :inicio AND :fin
                  GROUP BY estado";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute([':medico_id' => $medico_id, ':inicio' => $fecha_inicio, ':fin' => $fecha_fin]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>