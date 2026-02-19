<?php
class TipoIsv {
    private $conn;
    private $table_name = "tipos_isv";

    public function __construct($db) {
        $this->conn = $db;
    }

    public function leer() {
        $stmt = $this->conn->prepare("SELECT * FROM " . $this->table_name . " ORDER BY nombre_isv");
        $stmt->execute();
        return $stmt;
    }

    public function crear($data) {
        $stmt = $this->conn->prepare("INSERT INTO " . $this->table_name . " SET nombre_isv=:nombre, porcentaje=:porcentaje");
        $nombre = htmlspecialchars(strip_tags($data['nombre_isv']));
        $porcentaje = htmlspecialchars(strip_tags($data['porcentaje']));
        $stmt->bindParam(":nombre", $nombre);
        $stmt->bindParam(":porcentaje", $porcentaje);
        return $stmt->execute();
    }

    public function actualizar($id, $data) {
        $stmt = $this->conn->prepare("UPDATE " . $this->table_name . " SET nombre_isv = :nombre, porcentaje = :porcentaje WHERE id = :id");
        $nombre = htmlspecialchars(strip_tags($data['nombre_isv']));
        $porcentaje = htmlspecialchars(strip_tags($data['porcentaje']));
        $id_param = htmlspecialchars(strip_tags($id));
        $stmt->bindParam(':nombre', $nombre);
        $stmt->bindParam(':porcentaje', $porcentaje);
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