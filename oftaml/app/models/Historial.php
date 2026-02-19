<?php
// app/models/Historial.php
class Historial {
    private $conn;
    private $table_name = "historial_clinico";

    public function __construct($db) {
        $this->conn = $db;
    }

    public function leerPorPaciente($id_paciente) {
        // MODIFICACIÓN: Concatenamos ruta y nombre_original separados por '::'
        // Luego agrupamos todo con '||'
        $query = "SELECT 
                    h.id, h.id_cita, h.fecha_registro,
                    CONCAT(m.nombres, ' ', m.apellidos) as medico,
                    e.nombre as especialidad,
                    (SELECT COUNT(*) FROM archivos_historial WHERE id_historial = h.id) as total_archivos,
                    
                    (SELECT GROUP_CONCAT(CONCAT(ruta_archivo, nombre_guardado, '::', nombre_original) SEPARATOR '||') 
                     FROM archivos_historial 
                     WHERE id_historial = h.id
                    ) as archivos_concatenados,

                    h.hea,
                    h.av_sc_od, h.av_sc_os, h.av_cc_od, h.av_cc_os, h.pio_od, h.pio_os,
                    
                    r.od_esfera, r.od_cilindro, r.od_eje, r.od_av,
                    r.os_esfera, r.os_cilindro, r.os_eje, r.os_av,
                    r.tipo_refraccion,
                    
                    h.biomicroscopia, h.fondo_ojo, h.observaciones,
                    
                    (SELECT GROUP_CONCAT(d.descripcion SEPARATOR ', ') 
                     FROM historial_diagnosticos hd 
                     JOIN diagnosticos d ON hd.id_diagnostico = d.id 
                     WHERE hd.id_historial = h.id
                    ) as diagnostico,
                    
                    h.tratamiento
                    
                  FROM " . $this->table_name . " h
                  LEFT JOIN medicos m ON h.id_medico = m.id
                  LEFT JOIN especialidades e ON m.id_especialidad = e.id
                  LEFT JOIN historial_refraccion r ON h.id = r.id_historial
                  WHERE h.id_paciente = ?
                  GROUP BY h.id
                  ORDER BY h.fecha_registro DESC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $id_paciente);
        $stmt->execute();
        return $stmt;
    }

    public function leerUno($id) {
        $query = "SELECT 
                    id, id_cita, id_paciente, id_medico,
                    tratamiento, observaciones,
                    hea, av_sc_od, av_sc_os, av_cc_od, av_cc_os,
                    pio_od, pio_os, biomicroscopia, fondo_ojo
                  FROM " . $this->table_name . " 
                  WHERE id = ? LIMIT 0,1";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function existePorCita($id_cita) {
        $query = "SELECT id FROM " . $this->table_name . " WHERE id_cita = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $id_cita);
        $stmt->execute();
        return $stmt->rowCount() > 0;
    }

    public function crear($data) {
        if($this->existePorCita($data['id_cita'])){
            return false;
        }

        // --- CORRECCIÓN AQUÍ: Se añade diagnostico='' para evitar el error SQL 1364 ---
        $query = "INSERT INTO " . $this->table_name . " SET 
                    id_cita=:id_cita, 
                    id_paciente=:id_paciente, 
                    id_medico=:id_medico, 
                    hea=:hea,
                    av_sc_od=:av_sc_od, av_sc_os=:av_sc_os,
                    av_cc_od=:av_cc_od, av_cc_os=:av_cc_os,
                    pio_od=:pio_od, pio_os=:pio_os,
                    biomicroscopia=:biomicroscopia,
                    fondo_ojo=:fondo_ojo,
                    diagnostico='', 
                    tratamiento=:tratamiento, 
                    observaciones=:observaciones";
        
        $stmt = $this->conn->prepare($query);

        $allowed_tags = '<p><b><i><u><ul><ol><li><br><a>';

        // Bind de campos básicos
        $stmt->bindParam(":id_cita", $data['id_cita']);
        $stmt->bindParam(":id_paciente", $data['id_paciente']);
        $stmt->bindParam(":id_medico", $data['id_medico']);
        
        // Bind de nuevos campos
        $stmt->bindParam(":hea", $data['hea']);
        $stmt->bindParam(":av_sc_od", $data['av_sc_od']);
        $stmt->bindParam(":av_sc_os", $data['av_sc_os']);
        $stmt->bindParam(":av_cc_od", $data['av_cc_od']);
        $stmt->bindParam(":av_cc_os", $data['av_cc_os']);
        $stmt->bindParam(":pio_od", $data['pio_od']);
        $stmt->bindParam(":pio_os", $data['pio_os']);
        
        // Limpiar HTML de campos Summernote
        $biomicroscopia = strip_tags($data['biomicroscopia'], $allowed_tags);
        $fondo_ojo = strip_tags($data['fondo_ojo'], $allowed_tags);
        $tratamiento = strip_tags($data['tratamiento'], $allowed_tags);
        
        $stmt->bindParam(":biomicroscopia", $biomicroscopia);
        $stmt->bindParam(":fondo_ojo", $fondo_ojo);
        $stmt->bindParam(":tratamiento", $tratamiento);
        $stmt->bindParam(":observaciones", $data['observaciones']);

        return $stmt->execute();
    }

    // (Se elimina la lógica de transacciones anidadas: beginTransaction, commit, rollBack)
    public function actualizar($id, $data, $id_usuario_modifica) {
        
        // 1. Leer datos antiguos para el log
        $stmt_datos_ant = $this->conn->prepare("SELECT tratamiento, observaciones FROM " . $this->table_name . " WHERE id = ?");
        $stmt_datos_ant->execute([$id]);
        $datos_anteriores = $stmt_datos_ant->fetch(PDO::FETCH_ASSOC);
        
        if (!$datos_anteriores) {
            throw new Exception("No se encontró la entrada del historial.");
        }

        // 2. Query de actualización
        $query_update = "UPDATE " . $this->table_name . " SET 
                            hea=:hea,
                            av_sc_od=:av_sc_od, av_sc_os=:av_sc_os,
                            av_cc_od=:av_cc_od, av_cc_os=:av_cc_os,
                            pio_od=:pio_od, pio_os=:pio_os,
                            biomicroscopia=:biomicroscopia,
                            fondo_ojo=:fondo_ojo,
                            tratamiento=:tratamiento, 
                            observaciones=:observaciones 
                         WHERE id=:id";
        
        $stmt_update = $this->conn->prepare($query_update);

        $allowed_tags = '<p><b><i><u><ul><ol><li><br><a>';
        
        // Limpiar y bindear datos
        $id_param = htmlspecialchars(strip_tags($id));
        $stmt_update->bindParam(":id", $id_param);
        
        $stmt_update->bindParam(":hea", $data['hea']);
        $stmt_update->bindParam(":av_sc_od", $data['av_sc_od']);
        $stmt_update->bindParam(":av_sc_os", $data['av_sc_os']);
        $stmt_update->bindParam(":av_cc_od", $data['av_cc_od']);
        $stmt_update->bindParam(":av_cc_os", $data['av_cc_os']);
        $stmt_update->bindParam(":pio_od", $data['pio_od']);
        $stmt_update->bindParam(":pio_os", $data['pio_os']);
        
        $biomicroscopia = strip_tags($data['biomicroscopia'], $allowed_tags);
        $fondo_ojo = strip_tags($data['fondo_ojo'], $allowed_tags);
        $tratamiento = strip_tags($data['tratamiento'], $allowed_tags);
        
        $stmt_update->bindParam(":biomicroscopia", $biomicroscopia);
        $stmt_update->bindParam(":fondo_ojo", $fondo_ojo);
        $stmt_update->bindParam(":tratamiento", $tratamiento);
        $stmt_update->bindParam(":observaciones", $data['observaciones']);
        
        if (!$stmt_update->execute()) {
            throw new Exception("Error al actualizar la entrada del historial.");
        }

        // 3. Query de Log
        $query_log = "INSERT INTO historial_clinico_log (
                        id_historial, id_usuario_modifica, 
                        tratamiento_anterior, observaciones_anterior
                      ) VALUES (
                        :id_historial, :id_usuario_modifica, 
                        :tratamiento_anterior, :observaciones_anterior
                      )";
        
        $stmt_log = $this->conn->prepare($query_log);
        $stmt_log->bindParam(":id_historial", $id_param);
        $stmt_log->bindParam(":id_usuario_modifica", $id_usuario_modifica);
        $stmt_log->bindParam(":tratamiento_anterior", $datos_anteriores['tratamiento']);
        $stmt_log->bindParam(":observaciones_anterior", $datos_anteriores['observaciones']);

        if (!$stmt_log->execute()) {
            throw new Exception("Error al guardar el log de cambios.");
        }
        
        return true;
    }

    public function leerLogPorHistorial($id_historial) {
        $query = "SELECT 
                    l.fecha_modificacion,
                    l.diagnostico_anterior,
                    l.tratamiento_anterior,
                    l.observaciones_anterior,
                    u.nombre_completo as usuario_modifica
                  FROM historial_clinico_log l
                  JOIN usuarios u ON l.id_usuario_modifica = u.id
                  WHERE l.id_historial = ?
                  ORDER BY l.fecha_modificacion DESC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $id_historial);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function contarPioAltaHoy() {
        $query = "SELECT COUNT(DISTINCT id_paciente) as total_pacientes
                  FROM " . $this->table_name . " 
                  WHERE (CAST(pio_od AS UNSIGNED) > 21 OR CAST(pio_os AS UNSIGNED) > 21) 
                  AND DATE(fecha_registro) = CURDATE()";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchColumn();
    }
    
    public function leerDiagnosticos($id_historial) {
        $query = "SELECT 
                    d.id, 
                    CONCAT(IFNULL(CONCAT('(', d.codigo, ') '), ''), d.descripcion) as text
                  FROM historial_diagnosticos hd
                  JOIN diagnosticos d ON hd.id_diagnostico = d.id
                  WHERE hd.id_historial = ?";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $id_historial);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>