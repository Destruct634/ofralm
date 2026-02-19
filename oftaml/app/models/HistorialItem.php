<?php
// app/models/HistorialItem.php
class HistorialItem {
    private $conn;
    private $table_name = "historial_items";

    public function __construct($db) {
        $this->conn = $db;
    }

    /**
     * Lee todos los items (productos/servicios) asociados a un ID de historial.
     * Devuelve un formato compatible con el array 'itemsConsulta' del JS.
     */
    public function leerPorHistorial($id_historial) {
        $query = "SELECT 
                    tipo_item as tipo,
                    id_item,
                    descripcion_item as descripcion,
                    cantidad,
                    precio_unitario as precio,
                    descuento,
                    id_isv,
                    -- Creamos un id_raw (S-1 o P-1) para compatibilidad con el JS
                    CONCAT(IF(tipo_item = 'Servicio', 'S-', 'P-'), id_item) as id_raw
                  FROM " . $this->table_name . " 
                  WHERE id_historial = ?";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $id_historial);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Crea una nueva entrada de item en el historial.
     */
    public function crear($id_historial, $item) {
        $query = "INSERT INTO " . $this->table_name . " SET
                    id_historial = :id_historial,
                    tipo_item = :tipo_item,
                    id_item = :id_item,
                    descripcion_item = :descripcion_item,
                    cantidad = :cantidad,
                    precio_unitario = :precio_unitario,
                    descuento = :descuento,
                    id_isv = :id_isv";
        
        $stmt = $this->conn->prepare($query);

        // Limpiar datos
        $stmt->bindParam(":id_historial", $id_historial);
        $stmt->bindParam(":tipo_item", $item['tipo']);
        $stmt->bindParam(":id_item", $item['id_item']);
        $stmt->bindParam(":descripcion_item", $item['descripcion']);
        $stmt->bindParam(":cantidad", $item['cantidad']);
        $stmt->bindParam(":precio_unitario", $item['precio']);
        $stmt->bindParam(":descuento", $item['descuento']);
        $stmt->bindParam(":id_isv", $item['id_isv']);

        return $stmt->execute();
    }

    /**
     * Elimina todos los items asociados a un ID de historial.
     * (Esto se usa al 'Editar' para borrar los items antiguos antes de guardar los nuevos)
     */
    public function eliminarPorHistorial($id_historial) {
        $query = "DELETE FROM " . $this->table_name . " WHERE id_historial = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $id_historial);
        return $stmt->execute();
    }
}
?>