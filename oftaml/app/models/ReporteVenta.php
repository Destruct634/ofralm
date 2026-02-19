<?php
class ReporteVenta {
    private $conn;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function generarReporte($filtros) {
        // Base de la consulta: Vamos al detalle para poder filtrar por tipo (Producto/Servicio)
        $query = "SELECT 
                    f.id as id_factura,
                    f.correlativo, 
                    f.fecha_emision,
                    CONCAT(p.nombres, ' ', p.apellidos) as paciente,
                    CONCAT(m.nombres, ' ', m.apellidos) as medico,
                    u.nombre_completo as tecnico,
                    fd.tipo_item,
                    fd.descripcion_item,
                    fd.cantidad,
                    fd.precio_unitario,
                    fd.descuento,
                    (fd.cantidad * fd.precio_unitario - fd.descuento) as subtotal_item
                  FROM factura_detalle fd
                  JOIN facturas f ON fd.id_factura = f.id
                  JOIN pacientes p ON f.id_paciente = p.id
                  LEFT JOIN medicos m ON f.id_medico = m.id
                  LEFT JOIN usuarios u ON f.id_tecnico = u.id
                  WHERE f.estado = 'Pagada'"; // Solo ventas reales

        $params = [];

        // 1. Filtro de Fechas
        if (!empty($filtros['fecha_inicio']) && !empty($filtros['fecha_fin'])) {
            $query .= " AND DATE(f.fecha_emision) BETWEEN :inicio AND :fin";
            $params[':inicio'] = $filtros['fecha_inicio'];
            $params[':fin'] = $filtros['fecha_fin'];
        }

        // 2. Filtro de Tipo (Producto/Servicio)
        if (!empty($filtros['tipo']) && $filtros['tipo'] !== 'Todos') {
            $query .= " AND fd.tipo_item = :tipo";
            $params[':tipo'] = $filtros['tipo'];
        }

        // 3. Filtro de Médico
        if (!empty($filtros['id_medico'])) {
            $query .= " AND f.id_medico = :id_medico";
            $params[':id_medico'] = $filtros['id_medico'];
        }

        // 4. Filtro de Técnico
        if (!empty($filtros['id_tecnico'])) {
            $query .= " AND f.id_tecnico = :id_tecnico";
            $params[':id_tecnico'] = $filtros['id_tecnico'];
        }

        $query .= " ORDER BY f.fecha_emision DESC, f.correlativo DESC";

        $stmt = $this->conn->prepare($query);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>