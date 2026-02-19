<?php
class Aseguradora {
    private $conn;
    private $table_name = "aseguradoras";

    public function __construct($db) {
        $this->conn = $db;
    }

    public function leer() {
        $stmt = $this->conn->prepare("SELECT * FROM " . $this->table_name . " ORDER BY nombre");
        $stmt->execute();
        return $stmt;
    }

    public function leerActivas() {
        $stmt = $this->conn->prepare("SELECT * FROM " . $this->table_name . " WHERE estado = 'Activo' ORDER BY nombre");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function crear($data) {
        $stmt = $this->conn->prepare("INSERT INTO " . $this->table_name . " SET nombre=:nombre, estado=:estado");
        $nombre = htmlspecialchars(strip_tags($data['nombre']));
        $estado = htmlspecialchars(strip_tags($data['estado']));
        $stmt->bindParam(":nombre", $nombre);
        $stmt->bindParam(":estado", $estado);
        return $stmt->execute();
    }

    public function actualizar($id, $data) {
        $stmt = $this->conn->prepare("UPDATE " . $this->table_name . " SET nombre = :nombre, estado = :estado WHERE id = :id");
        $nombre = htmlspecialchars(strip_tags($data['nombre']));
        $estado = htmlspecialchars(strip_tags($data['estado']));
        $id_param = htmlspecialchars(strip_tags($id));
        $stmt->bindParam(':nombre', $nombre);
        $stmt->bindParam(':estado', $estado);
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