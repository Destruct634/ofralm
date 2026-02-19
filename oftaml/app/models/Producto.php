<?php
class Producto {
    private $conn;
    private $table_name = "productos";

    public function __construct($db) {
        $this->conn = $db;
    }

    public function leer() {
        $query = "SELECT 
                    p.*, 
                    c.nombre_categoria, 
                    i.nombre_isv 
                  FROM " . $this->table_name . " p
                  LEFT JOIN categorias_producto c ON p.id_categoria = c.id
                  LEFT JOIN tipos_isv i ON p.id_isv = i.id
                  ORDER BY p.nombre_producto ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    public function leerUno($id) {
        $query = "SELECT * FROM " . $this->table_name . " WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    public function crear($data) {
        $query = "INSERT INTO " . $this->table_name . " SET 
                    codigo=:codigo, codigo_barras=:codigo_barras, nombre_producto=:nombre_producto, 
                    descripcion=:descripcion, id_categoria=:id_categoria, stock_minimo=:stock_minimo, 
                    id_isv=:id_isv, precio_compra=:precio_compra, precio_venta=:precio_venta, 
                    unidad_medida=:unidad_medida, es_inventariable=:es_inventariable";
        $stmt = $this->conn->prepare($query);

        // Limpiar datos
        foreach($data as $key => &$value) {
            $value = htmlspecialchars(strip_tags($value));
        }

        $stmt->bindParam(":codigo", $data['codigo']);
        $stmt->bindParam(":codigo_barras", $data['codigo_barras']);
        $stmt->bindParam(":nombre_producto", $data['nombre_producto']);
        $stmt->bindParam(":descripcion", $data['descripcion']);
        $stmt->bindParam(":id_categoria", $data['id_categoria']);
        $stmt->bindParam(":stock_minimo", $data['stock_minimo']);
        $stmt->bindParam(":id_isv", $data['id_isv']);
        $stmt->bindParam(":precio_compra", $data['precio_compra']);
        $stmt->bindParam(":precio_venta", $data['precio_venta']);
        $stmt->bindParam(":unidad_medida", $data['unidad_medida']);
        $stmt->bindParam(":es_inventariable", $data['es_inventariable']);

        return $stmt->execute();
    }

    public function actualizar($id, $data) {
        $query = "UPDATE " . $this->table_name . " SET 
                    codigo=:codigo, codigo_barras=:codigo_barras, nombre_producto=:nombre_producto, 
                    descripcion=:descripcion, id_categoria=:id_categoria, stock_minimo=:stock_minimo, 
                    id_isv=:id_isv, precio_compra=:precio_compra, precio_venta=:precio_venta, 
                    unidad_medida=:unidad_medida, es_inventariable=:es_inventariable
                  WHERE id=:id";
        $stmt = $this->conn->prepare($query);

        // Limpiar datos
        foreach($data as $key => &$value) {
            $value = htmlspecialchars(strip_tags($value));
        }
        $id_param = htmlspecialchars(strip_tags($id));

        $stmt->bindParam(":codigo", $data['codigo']);
        $stmt->bindParam(":codigo_barras", $data['codigo_barras']);
        $stmt->bindParam(":nombre_producto", $data['nombre_producto']);
        $stmt->bindParam(":descripcion", $data['descripcion']);
        $stmt->bindParam(":id_categoria", $data['id_categoria']);
        $stmt->bindParam(":stock_minimo", $data['stock_minimo']);
        $stmt->bindParam(":id_isv", $data['id_isv']);
        $stmt->bindParam(":precio_compra", $data['precio_compra']);
        $stmt->bindParam(":precio_venta", $data['precio_venta']);
        $stmt->bindParam(":unidad_medida", $data['unidad_medida']);
        $stmt->bindParam(":es_inventariable", $data['es_inventariable']);
        $stmt->bindParam(":id", $id_param);

        return $stmt->execute();
    }

    public function eliminar($id) {
        $query = "DELETE FROM " . $this->table_name . " WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $id);
        return $stmt->execute();
    }
    
    public function search($term) {
        $query = "SELECT id, CONCAT(nombre_producto, ' (Código: ', codigo, ')') as text, precio_venta, id_isv
                  FROM " . $this->table_name . " 
                  WHERE (nombre_producto LIKE :term OR codigo LIKE :term OR codigo_barras LIKE :term)
                  AND es_inventariable = 1
                  LIMIT 10";
        $stmt = $this->conn->prepare($query);
        $searchTerm = "%" . $term . "%";
        $stmt->bindParam(":term", $searchTerm);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function obtenerCategorias() {
        $stmt = $this->conn->prepare("SELECT id, nombre_categoria FROM categorias_producto ORDER BY nombre_categoria ASC");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function obtenerTiposISV() {
        $stmt = $this->conn->prepare("SELECT id, nombre_isv, porcentaje FROM tipos_isv");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function leerProductosConBajoStock($limite = 5) {
        $query = "SELECT codigo, nombre_producto, stock_actual, stock_minimo 
                  FROM " . $this->table_name . " 
                  WHERE es_inventariable = 1 AND stock_actual <= stock_minimo
                  ORDER BY stock_actual ASC
                  LIMIT :limite";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':limite', $limite, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>