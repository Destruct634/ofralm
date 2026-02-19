<?php
// app/models/ArchivoCategoria.php
class ArchivoCategoria {
    private $conn;
    private $table_name = "archivo_categorias";

    public function __construct($db) {
        $this->conn = $db;
    }

    // Leer todas las categorías
    public function leer() {
        $query = "SELECT id, nombre_categoria, estado FROM " . $this->table_name . " ORDER BY nombre_categoria ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    // Leer solo las categorías activas (para dropdowns)
    public function leerActivos() {
        $query = "SELECT id, nombre_categoria FROM " . $this->table_name . " WHERE estado = 'Activo' ORDER BY nombre_categoria ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    // Leer una sola categoría por ID
    public function leerUno($id) {
        $query = "SELECT id, nombre_categoria, estado FROM " . $this->table_name . " WHERE id = ? LIMIT 0,1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Crear una nueva categoría
    public function crear($data) {
        $query = "INSERT INTO " . $this->table_name . " SET nombre_categoria=:nombre_categoria, estado=:estado";
        $stmt = $this->conn->prepare($query);

        // Limpiar datos
        $nombre_categoria = htmlspecialchars(strip_tags($data['nombre_categoria']));
        $estado = htmlspecialchars(strip_tags($data['estado']));

        $stmt->bindParam(":nombre_categoria", $nombre_categoria);
        $stmt->bindParam(":estado", $estado);

        return $stmt->execute();
    }

    // Actualizar una categoría
    public function actualizar($id, $data) {
        $query = "UPDATE " . $this->table_name . " SET nombre_categoria = :nombre_categoria, estado = :estado WHERE id = :id";
        $stmt = $this->conn->prepare($query);

        // Limpiar datos
        $nombre_categoria = htmlspecialchars(strip_tags($data['nombre_categoria']));
        $estado = htmlspecialchars(strip_tags($data['estado']));
        $id = htmlspecialchars(strip_tags($id));

        $stmt->bindParam(':nombre_categoria', $nombre_categoria);
        $stmt->bindParam(':estado', $estado);
        $stmt->bindParam(':id', $id);

        return $stmt->execute();
    }

    // Eliminar una categoría
    public function eliminar($id) {
        $query = "DELETE FROM " . $this->table_name . " WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        $id = htmlspecialchars(strip_tags($id));
        $stmt->bindParam(1, $id);
        return $stmt->execute();
    }
}
?>