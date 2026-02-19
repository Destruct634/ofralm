<?php
class Proveedor {
    private $conn;
    private $table_name = "proveedores";

    public function __construct($db) {
        $this->conn = $db;
    }

    public function leer() {
        $stmt = $this->conn->prepare("SELECT * FROM " . $this->table_name . " ORDER BY nombre_proveedor");
        $stmt->execute();
        return $stmt;
    }

    public function search($term) {
        $query = "SELECT id, nombre_proveedor as text 
                  FROM " . $this->table_name . " 
                  WHERE nombre_proveedor LIKE :term AND estado = 'Activo'
                  LIMIT 10";
        $stmt = $this->conn->prepare($query);
        $searchTerm = "%" . $term . "%";
        $stmt->bindParam(":term", $searchTerm);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function crear($data) {
        $stmt = $this->conn->prepare("INSERT INTO " . $this->table_name . " SET nombre_proveedor=:nombre, contacto=:contacto, telefono=:telefono, email=:email, estado=:estado");
        $nombre = htmlspecialchars(strip_tags($data['nombre_proveedor']));
        $contacto = htmlspecialchars(strip_tags($data['contacto']));
        $telefono = htmlspecialchars(strip_tags($data['telefono']));
        $email = htmlspecialchars(strip_tags($data['email']));
        $estado = htmlspecialchars(strip_tags($data['estado']));
        $stmt->bindParam(":nombre", $nombre);
        $stmt->bindParam(":contacto", $contacto);
        $stmt->bindParam(":telefono", $telefono);
        $stmt->bindParam(":email", $email);
        $stmt->bindParam(":estado", $estado);
        return $stmt->execute();
    }

    public function actualizar($id, $data) {
        $stmt = $this->conn->prepare("UPDATE " . $this->table_name . " SET nombre_proveedor=:nombre, contacto=:contacto, telefono=:telefono, email=:email, estado=:estado WHERE id = :id");
        $nombre = htmlspecialchars(strip_tags($data['nombre_proveedor']));
        $contacto = htmlspecialchars(strip_tags($data['contacto']));
        $telefono = htmlspecialchars(strip_tags($data['telefono']));
        $email = htmlspecialchars(strip_tags($data['email']));
        $estado = htmlspecialchars(strip_tags($data['estado']));
        $id_param = htmlspecialchars(strip_tags($id));
        $stmt->bindParam(':nombre', $nombre);
        $stmt->bindParam(':contacto', $contacto);
        $stmt->bindParam(':telefono', $telefono);
        $stmt->bindParam(':email', $email);
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
