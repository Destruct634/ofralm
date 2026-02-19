<?php
// app/models/Diagnostico.php
class Diagnostico {
    private $conn;
    private $table_name = "diagnosticos";

    public function __construct($db) {
        $this->conn = $db;
    }

    public function leer() {
        $query = "SELECT id, codigo, descripcion, estado FROM " . $this->table_name . " ORDER BY descripcion ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    public function leerUno($id) {
        $query = "SELECT id, codigo, descripcion, estado FROM " . $this->table_name . " WHERE id = ? LIMIT 0,1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function leerPorDescripcion($descripcion) {
        $query = "SELECT id FROM " . $this->table_name . " WHERE descripcion = ? LIMIT 0,1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $descripcion);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function crear($data) {
        $query = "INSERT INTO " . $this->table_name . " SET descripcion = :descripcion, codigo = :codigo, estado = :estado";
        $stmt = $this->conn->prepare($query);
        
        $descripcion = htmlspecialchars(strip_tags($data['descripcion']));
        $codigo = !empty($data['codigo']) ? htmlspecialchars(strip_tags($data['codigo'])) : null;
        $estado = htmlspecialchars(strip_tags($data['estado']));

        $stmt->bindParam(":descripcion", $descripcion);
        $stmt->bindParam(":codigo", $codigo);
        $stmt->bindParam(":estado", $estado);
        
        if ($stmt->execute()) {
            return $this->conn->lastInsertId();
        }
        return false;
    }

    public function actualizar($id, $data) {
        $query = "UPDATE " . $this->table_name . " SET 
                    descripcion = :descripcion, 
                    codigo = :codigo, 
                    estado = :estado
                  WHERE id = :id";
        $stmt = $this->conn->prepare($query);

        $id = htmlspecialchars(strip_tags($id));
        $descripcion = htmlspecialchars(strip_tags($data['descripcion']));
        $codigo = !empty($data['codigo']) ? htmlspecialchars(strip_tags($data['codigo'])) : null;
        $estado = htmlspecialchars(strip_tags($data['estado']));

        $stmt->bindParam(":id", $id);
        $stmt->bindParam(":descripcion", $descripcion);
        $stmt->bindParam(":codigo", $codigo);
        $stmt->bindParam(":estado", $estado);

        return $stmt->execute();
    }

    public function eliminar($id) {
        $query = "DELETE FROM " . $this->table_name . " WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $id);
        return $stmt->execute();
    }

    public function encontrarOCrear($item) {
        if (is_numeric($item)) {
            return $item;
        }
        $existente = $this->leerPorDescripcion($item);
        if ($existente) {
            return $existente['id'];
        }
        $data = [
            'descripcion' => $item,
            'codigo' => null,
            'estado' => 'Activo'
        ];
        return $this->crear($data);
    }

    // --- INICIO DE NUEVA FUNCIÓN ---
    /**
     * Obtiene los 10 diagnósticos más frecuentes.
     */
    public function getTop10Diagnosticos() {
        $query = "SELECT 
                    d.descripcion, 
                    COUNT(hd.id) as total
                  FROM historial_diagnosticos hd
                  JOIN diagnosticos d ON hd.id_diagnostico = d.id
                  GROUP BY d.id, d.descripcion
                  ORDER BY total DESC
                  LIMIT 10";

        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        
        $labels = [];
        $data = [];

        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $labels[] = $row['descripcion'];
            $data[] = $row['total'];
        }

        // Devolvemos en el formato que Chart.js necesita
        return [
            'labels' => $labels,
            'data' => $data
        ];
    }
    // --- FIN DE NUEVA FUNCIÓN ---
}
?>