<?php
// app/models/ConsultaPlantilla.php
class ConsultaPlantilla {
    private $conn;
    private $table_name = "consulta_plantillas";

    public function __construct($db) {
        $this->conn = $db;
    }

    // Leer todas las plantillas para la tabla de gestión
    public function leer() {
        $query = "SELECT id, titulo, estado FROM " . $this->table_name . " ORDER BY titulo ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    // Leer solo las plantillas activas (para el dropdown del historial)
    public function leerActivos() {
        $query = "SELECT id, titulo, contenido FROM " . $this->table_name . " WHERE estado = 'Activo' ORDER BY titulo ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    // Leer una sola plantilla por ID
    public function leerUno($id) {
        $query = "SELECT id, titulo, contenido, estado FROM " . $this->table_name . " WHERE id = ? LIMIT 0,1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Crear una nueva plantilla
    public function crear($data) {
        $query = "INSERT INTO " . $this->table_name . " SET titulo=:titulo, contenido=:contenido, estado=:estado";
        $stmt = $this->conn->prepare($query);

        $allowed_tags = '<p><b><i><u><ul><ol><li><br><a><strong><em>';
        $titulo = htmlspecialchars(strip_tags($data['titulo']));
        $contenido = strip_tags($data['contenido'], $allowed_tags);
        $estado = htmlspecialchars(strip_tags($data['estado']));

        $stmt->bindParam(":titulo", $titulo);
        $stmt->bindParam(":contenido", $contenido);
        $stmt->bindParam(":estado", $estado);

        return $stmt->execute();
    }

    // Actualizar una plantilla
    public function actualizar($id, $data) {
        $query = "UPDATE " . $this->table_name . " SET titulo=:titulo, contenido=:contenido, estado=:estado WHERE id = :id";
        $stmt = $this->conn->prepare($query);

        $allowed_tags = '<p><b><i><u><ul><ol><li><br><a><strong><em>';
        $titulo = htmlspecialchars(strip_tags($data['titulo']));
        $contenido = strip_tags($data['contenido'], $allowed_tags);
        $estado = htmlspecialchars(strip_tags($data['estado']));
        $id = htmlspecialchars(strip_tags($id));

        $stmt->bindParam(":titulo", $titulo);
        $stmt->bindParam(":contenido", $contenido);
        $stmt->bindParam(":estado", $estado);
        $stmt->bindParam(":id", $id);

        return $stmt->execute();
    }

    // Eliminar una plantilla
    public function eliminar($id) {
        $query = "DELETE FROM " . $this->table_name . " WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        $id = htmlspecialchars(strip_tags($id));
        $stmt->bindParam(1, $id);
        return $stmt->execute();
    }
}
?>