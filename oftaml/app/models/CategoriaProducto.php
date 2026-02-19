<?php
class CategoriaProducto {
    private $conn;
    private $table_name = "categorias_producto";

    public function __construct($db) {
        $this->conn = $db;
    }

    public function leer() {
        $stmt = $this->conn->prepare("SELECT * FROM " . $this->table_name . " ORDER BY nombre_categoria");
        $stmt->execute();
        return $stmt;
    }

    public function crear($data) {
        $stmt = $this->conn->prepare("INSERT INTO " . $this->table_name . " SET nombre_categoria=:nombre");
        $nombre = htmlspecialchars(strip_tags($data['nombre_categoria']));
        $stmt->bindParam(":nombre", $nombre);
        return $stmt->execute();
    }

    public function actualizar($id, $data) {
        $stmt = $this->conn->prepare("UPDATE " . $this->table_name . " SET nombre_categoria=:nombre WHERE id = :id");
        $nombre = htmlspecialchars(strip_tags($data['nombre_categoria']));
        $id_param = htmlspecialchars(strip_tags($id));
        $stmt->bindParam(':nombre', $nombre);
        $stmt->bindParam(':id', $id_param);
        return $stmt->execute();
    }

    public function eliminar($id) {
        $stmt = $this->conn->prepare("DELETE FROM " . $this->table_name . " WHERE id = ?");
        $stmt->bindParam(1, $id);
        return $stmt->execute();
    }
}
?>