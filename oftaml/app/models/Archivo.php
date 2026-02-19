<?php
class Archivo {
    private $conn;
    private $table_name = "archivos_historial";

    public function __construct($db) {
        $this->conn = $db;
    }

    public function leerPorHistorial($id_historial) {
        $query = "SELECT 
                    a.id, a.nombre_original, a.ruta_archivo, a.nombre_guardado, a.fecha_subida,
                    u.nombre_completo as usuario_subida,
                    ac.nombre_categoria
                  FROM " . $this->table_name . " a
                  LEFT JOIN usuarios u ON a.id_usuario_subida = u.id
                  LEFT JOIN archivo_categorias ac ON a.id_categoria = ac.id
                  WHERE a.id_historial = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $id_historial);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function leerUno($id) {
        $query = "SELECT * FROM " . $this->table_name . " WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function crear($id_paciente, $id_historial, $nombre_original, $nombre_guardado, $ruta, $id_usuario, $id_categoria) {
        $query = "INSERT INTO " . $this->table_name . " 
                  SET id_paciente=:id_paciente, id_historial=:id_historial, 
                      id_usuario_subida=:id_usuario_subida, nombre_original=:nombre_original, 
                      nombre_guardado=:nombre_guardado, ruta_archivo=:ruta_archivo, 
                      id_categoria=:id_categoria";
        
        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(":id_paciente", $id_paciente);
        $stmt->bindParam(":id_historial", $id_historial);
        $stmt->bindParam(":id_usuario_subida", $id_usuario);
        $stmt->bindParam(":nombre_original", $nombre_original);
        $stmt->bindParam(":nombre_guardado", $nombre_guardado);
        $stmt->bindParam(":ruta_archivo", $ruta);
        
        if (empty($id_categoria)) $id_categoria = null;
        $stmt->bindParam(":id_categoria", $id_categoria);

        return $stmt->execute();
    }

    public function eliminar($id) {
        $stmt = $this->conn->prepare("DELETE FROM " . $this->table_name . " WHERE id = ?");
        $stmt->bindParam(1, $id);
        return $stmt->execute();
    }

    public function contarPorPaciente($id_paciente) {
        $query = "SELECT COUNT(a.id) as total 
                  FROM " . $this->table_name . " a
                  WHERE a.id_paciente = ?";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $id_paciente);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row['total'];
    }

    public function leerPorPacientePaginado($id_paciente, $limit, $offset) {
        $query = "SELECT 
                    a.id, a.nombre_original, a.ruta_archivo, a.nombre_guardado, a.fecha_subida,
                    u.nombre_completo as usuario_subida,
                    ac.nombre_categoria
                  FROM " . $this->table_name . " a
                  LEFT JOIN usuarios u ON a.id_usuario_subida = u.id
                  LEFT JOIN archivo_categorias ac ON a.id_categoria = ac.id
                  WHERE a.id_paciente = :id_paciente
                  ORDER BY a.fecha_subida DESC
                  LIMIT :limit OFFSET :offset";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id_paciente", $id_paciente, PDO::PARAM_INT);
        $stmt->bindParam(":limit", $limit, PDO::PARAM_INT);
        $stmt->bindParam(":offset", $offset, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>