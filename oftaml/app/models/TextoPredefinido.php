<?php
// app/models/TextoPredefinido.php
class TextoPredefinido {
    private $conn;
    private $table_name = "textos_predefinidos";

    public function __construct($db) {
        $this->conn = $db;
    }

    public function leer() {
        $stmt = $this->conn->prepare("SELECT * FROM " . $this->table_name . " ORDER BY titulo");
        $stmt->execute();
        return $stmt;
    }

    public function leerActivos() {
        $stmt = $this->conn->prepare("SELECT titulo, contenido FROM " . $this->table_name . " WHERE estado = 'Activo' ORDER BY titulo");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function crear($data) {
        $stmt = $this->conn->prepare("INSERT INTO " . $this->table_name . " SET titulo=:titulo, contenido=:contenido, estado=:estado");
        $titulo = htmlspecialchars(strip_tags($data['titulo']));
        $contenido = strip_tags($data['contenido'], '<p><b><i><u><ul><ol><li><br><a>');
        $estado = htmlspecialchars(strip_tags($data['estado']));
        $stmt->bindParam(":titulo", $titulo);
        $stmt->bindParam(":contenido", $contenido);
        $stmt->bindParam(":estado", $estado);
        return $stmt->execute();
    }

    public function actualizar($id, $data) {
        $stmt = $this->conn->prepare("UPDATE " . $this->table_name . " SET titulo = :titulo, contenido = :contenido, estado = :estado WHERE id = :id");
        $titulo = htmlspecialchars(strip_tags($data['titulo']));
        $contenido = strip_tags($data['contenido'], '<p><b><i><u><ul><ol><li><br><a>');
        $estado = htmlspecialchars(strip_tags($data['estado']));
        $id_param = htmlspecialchars(strip_tags($id));
        $stmt->bindParam(':titulo', $titulo);
        $stmt->bindParam(':contenido', $contenido);
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