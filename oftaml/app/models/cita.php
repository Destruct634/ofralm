<?php
// app/models/Cita.php
class Cita {
    private $conn;
    private $table_name = "citas";

    public function __construct($db) {
        $this->conn = $db;
    }

    public function leer($startDate = null, $endDate = null, $medicoId = null, $categoriaId = null) {
        $query = "SELECT 
                    c.id, c.id_paciente, c.id_medico, c.id_servicio,
                    CONCAT(p.nombres, ' ', p.apellidos) as paciente,
                    p.telefono as paciente_telefono,
                    CONCAT(m.nombres, ' ', m.apellidos) as medico,
                    e.nombre as especialidad,
                    s.nombre_servicio as motivo_detalle,
                    cs.nombre_categoria as tipo_cita,
                    c.fecha_cita, c.hora_cita, c.estado, c.notificado, c.facturada
                  FROM " . $this->table_name . " c 
                  LEFT JOIN pacientes p ON c.id_paciente = p.id
                  LEFT JOIN medicos m ON c.id_medico = m.id
                  LEFT JOIN especialidades e ON m.id_especialidad = e.id
                  LEFT JOIN servicios s ON c.id_servicio = s.id
                  LEFT JOIN categorias_servicio cs ON s.id_categoria_servicio = cs.id";
        
        $conditions = [];
        $params = [];

        if ($startDate && $endDate) {
            $conditions[] = "c.fecha_cita BETWEEN :startDate AND :endDate";
            $params[':startDate'] = $startDate;
            $params[':endDate'] = $endDate;
        }
        
        if ($medicoId) {
            $conditions[] = "c.id_medico = :medicoId";
            $params[':medicoId'] = $medicoId;
        }

        if ($categoriaId) {
            $conditions[] = "s.id_categoria_servicio = :categoriaId";
            $params[':categoriaId'] = $categoriaId;
        }

        if (count($conditions) > 0) {
            $query .= " WHERE " . implode(' AND ', $conditions);
        }
        
        $query .= " ORDER BY c.fecha_cita ASC, c.hora_cita ASC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute($params);
        return $stmt;
    }
    
    public function leerUno($id) {
        // MODIFICACIÓN: Se añade el JOIN con servicios para obtener la categoría
        $query = "SELECT 
                    c.id, c.id_paciente, c.id_medico, 
                    m.id_especialidad, 
                    c.id_servicio, 
                    s.id_categoria_servicio, 
                    c.fecha_cita, c.hora_cita, c.estado, c.notificado, c.facturada 
                  FROM " . $this->table_name . " c
                  LEFT JOIN medicos m ON c.id_medico = m.id
                  LEFT JOIN servicios s ON c.id_servicio = s.id
                  WHERE c.id = ? LIMIT 0,1";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function crear($data) {
        $query = "INSERT INTO " . $this->table_name . " SET id_paciente=:id_paciente, id_medico=:id_medico, id_servicio=:id_servicio, fecha_cita=:fecha_cita, hora_cita=:hora_cita, estado=:estado";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id_paciente", $data['id_paciente']);
        $stmt->bindParam(":id_medico", $data['id_medico']);
        $stmt->bindParam(":id_servicio", $data['id_servicio']);
        $stmt->bindParam(":fecha_cita", $data['fecha_cita']);
        $stmt->bindParam(":hora_cita", $data['hora_cita']);
        $stmt->bindParam(":estado", $data['estado']);
        if ($stmt->execute()) {
            return $this->conn->lastInsertId();
        }
        return false;
    }

    public function actualizar($id, $data) {
        $query = "UPDATE " . $this->table_name . " SET id_paciente=:id_paciente, id_medico=:id_medico, id_servicio=:id_servicio, fecha_cita=:fecha_cita, hora_cita=:hora_cita, estado=:estado WHERE id=:id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id_paciente", $data['id_paciente']);
        $stmt->bindParam(":id_medico", $data['id_medico']);
        $stmt->bindParam(":id_servicio", $data['id_servicio']);
        $stmt->bindParam(":fecha_cita", $data['fecha_cita']);
        $stmt->bindParam(":hora_cita", $data['hora_cita']);
        $stmt->bindParam(":estado", $data['estado']);
        $stmt->bindParam(":id", $id);
        return $stmt->execute();
    }
    
    public function marcarComoFacturada($id_cita) {
        $query = "UPDATE " . $this->table_name . " SET facturada = 1 WHERE id = :id_cita";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id_cita", $id_cita);
        return $stmt->execute();
    }
    public function actualizarNotificacion($id_cita, $estado_notificacion) {
        $query = "UPDATE " . $this->table_name . " SET notificado = :notificado WHERE id = :id_cita";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":notificado", $estado_notificacion);
        $stmt->bindParam(":id_cita", $id_cita);
        return $stmt->execute();
    }
    public function leerPorPaciente($id_paciente) {
        $query = "SELECT 
                    c.id, c.id_medico, c.fecha_cita, 
                    (SELECT COUNT(hc.id) FROM historial_clinico hc WHERE hc.id_cita = c.id) as tiene_historial,
                    s.nombre_servicio as motivo_detalle,
                    CONCAT(m.nombres, ' ', m.apellidos) as medico_nombre
                  FROM " . $this->table_name . " c
                  LEFT JOIN medicos m ON c.id_medico = m.id
                  LEFT JOIN servicios s ON c.id_servicio = s.id
                  WHERE c.id_paciente = ?
                  ORDER BY c.fecha_cita DESC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $id_paciente);
        $stmt->execute();
        return $stmt;
    }
    public function eliminar($id) {
        $query = "DELETE FROM " . $this->table_name . " WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $id);
        return $stmt->execute();
    }
    public function obtenerPacientes() {
        $query = "SELECT id, nombres, apellidos FROM pacientes ORDER BY apellidos, nombres";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    public function obtenerMedicosPorEspecialidad($idEspecialidad, $idMedicoActual = null) {
        $query = "SELECT id, nombres, apellidos, estado FROM medicos 
                  WHERE (id_especialidad = :idEspecialidad AND estado = 'Activo') 
                  OR id = :idMedicoActual";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":idEspecialidad", $idEspecialidad);
        $stmt->bindParam(":idMedicoActual", $idMedicoActual);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    public function obtenerTodosLosMedicos() {
        $query = "SELECT id, nombres, apellidos FROM medicos WHERE estado = 'Activo' ORDER BY apellidos, nombres";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    public function marcarComoCompletada($id_cita) {
        $query = "UPDATE " . $this->table_name . " SET estado = 'Completada' WHERE id = :id_cita";
        $stmt = $this->conn->prepare($query);
        $id_cita_limpio = htmlspecialchars(strip_tags($id_cita));
        $stmt->bindParam(":id_cita", $id_cita_limpio);
        return $stmt->execute();
    }

    public function obtenerServiciosParaCitas($categoriaId = null) {
        $query = "SELECT s.id, s.nombre_servicio, cs.nombre_categoria 
                  FROM servicios s
                  JOIN categorias_servicio cs ON s.id_categoria_servicio = cs.id
                  WHERE s.mostrar_en_citas = 1";
        
        if ($categoriaId) {
            $query .= " AND s.id_categoria_servicio = :categoriaId";
        }
        
        $query .= " ORDER BY cs.nombre_categoria, s.nombre_servicio";
        
        $stmt = $this->conn->prepare($query);
        
        if ($categoriaId) {
            $stmt->bindParam(":categoriaId", $categoriaId);
        }
        
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function contarCitasHoy() {
        $query = "SELECT COUNT(*) as total_hoy FROM " . $this->table_name . " WHERE fecha_cita = CURDATE() AND estado = 'Programada'";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchColumn();
    }
    public function leerProximasCitas($limite = 5) {
        $query = "SELECT 
                    c.hora_cita,
                    CONCAT(p.nombres, ' ', p.apellidos) as paciente_nombre,
                    s.nombre_servicio as motivo,
                    CONCAT(m.nombres, ' ', m.apellidos) as medico_nombre
                  FROM " . $this->table_name . " c
                  JOIN pacientes p ON c.id_paciente = p.id
                  JOIN servicios s ON c.id_servicio = s.id
                  JOIN medicos m ON c.id_medico = m.id
                  WHERE c.fecha_cita = CURDATE() AND c.estado = 'Programada' AND c.hora_cita >= CURTIME()
                  ORDER BY c.hora_cita ASC
                  LIMIT :limite";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':limite', $limite, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    public function obtenerConteoCitasPorCategoriaSemana() {
        $query = "SELECT 
                    c.fecha_cita,
                    cs.nombre_categoria,
                    COUNT(c.id) as total_citas
                  FROM " . $this->table_name . " c
                  JOIN servicios s ON c.id_servicio = s.id
                  JOIN categorias_servicio cs ON s.id_categoria_servicio = cs.id
                  WHERE
                    c.fecha_cita BETWEEN CURDATE() - INTERVAL 6 DAY AND CURDATE()
                  GROUP BY
                    c.fecha_cita, cs.nombre_categoria
                  ORDER BY
                    c.fecha_cita ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $raw_data = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $labels = [];
        $categories_found = [];
        $data_pivot = [];
        $datasets = [];
        $colors = [ 'rgba(78, 115, 223, 0.8)', 'rgba(28, 200, 138, 0.8)', 'rgba(246, 194, 62, 0.8)', 'rgba(231, 74, 59, 0.8)', 'rgba(111, 66, 193, 0.8)', 'rgba(54, 185, 204, 0.8)', ];
        $color_index = 0;
        date_default_timezone_set('America/Tegucigalpa');
        for ($i = 6; $i >= 0; $i--) {
            $timestamp = strtotime("-$i days");
            $fecha_str = date('Y-m-d', $timestamp);
            $labels[$fecha_str] = date('D j', $timestamp);
        }
        foreach ($raw_data as $row) {
            $fecha = $row['fecha_cita'];
            $categoria = $row['nombre_categoria'];
            if (!in_array($categoria, $categories_found)) {
                $categories_found[] = $categoria;
            }
            if (isset($labels[$fecha])) {
                 $data_pivot[$fecha][$categoria] = (int)$row['total_citas'];
            }
        }
        foreach ($categories_found as $categoria) {
            $data_para_categoria = [];
            foreach (array_keys($labels) as $fecha_str) {
                $data_para_categoria[] = $data_pivot[$fecha_str][$categoria] ?? 0;
            }
            $color = $colors[$color_index % count($colors)];
            $color_index++;
            $datasets[] = [
                'label' => $categoria,
                'data' => $data_para_categoria,
                'backgroundColor' => $color,
            ];
        }
        return ['labels' => array_values($labels), 'datasets' => $datasets];
    }
    
    public function crearVisitaRapida($id_paciente, $id_medico) {
        $query = "INSERT INTO " . $this->table_name . " 
                  SET id_paciente=:id_paciente, 
                      id_medico=:id_medico, 
                      id_servicio= 1, -- Asumimos 1 = Consulta General
                      fecha_cita=CURDATE(), 
                      hora_cita=CURTIME(), 
                      estado='Completada'";
        
        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(":id_paciente", $id_paciente);
        $stmt->bindParam(":id_medico", $id_medico);

        if ($stmt->execute()) {
            return $this->conn->lastInsertId();
        }
        return false;
    }
}
?>