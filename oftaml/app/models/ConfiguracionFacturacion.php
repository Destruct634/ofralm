<?php
class ConfiguracionFacturacion {
    private $conn;
    private $table_name = "configuracion_facturacion";

    public function __construct($db) {
        $this->conn = $db;
    }

    public function leer() {
        $stmt = $this->conn->prepare("SELECT * FROM " . $this->table_name . " WHERE id = 1");
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function actualizar($data) {
        $query = "UPDATE " . $this->table_name . " SET prefijo_correlativo=:prefijo, siguiente_numero=:siguiente WHERE id = 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":prefijo", $data['prefijo_correlativo']);
        $stmt->bindParam(":siguiente", $data['siguiente_numero']);
        return $stmt->execute();
    }
}
?>
