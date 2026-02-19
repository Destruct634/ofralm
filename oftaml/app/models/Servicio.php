<?php
class Servicio {
    private $conn;
    private $table_name = "servicios";

    public function __construct($db) {
        $this->conn = $db;
    }

    public function leer() {
        $query = "SELECT 
                    s.id, s.codigo, s.nombre_servicio, s.descripcion, s.id_categoria_servicio,
                    s.id_isv, s.precio_venta, s.mostrar_en_citas,
                    cs.nombre_categoria as nombre_categoria_servicio,
                    ti.nombre_isv
                  FROM " . $this->table_name . " s
                  LEFT JOIN categorias_servicio cs ON s.id_categoria_servicio = cs.id
                  LEFT JOIN tipos_isv ti ON s.id_isv = ti.id
                  ORDER BY s.nombre_servicio";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }
    
    public function search($term) {
        $query = "SELECT id, nombre_servicio as text, precio_venta, id_isv 
                  FROM " . $this->table_name . " 
                  WHERE nombre_servicio LIKE :term OR codigo LIKE :term
                  LIMIT 10";
        $stmt = $this->conn->prepare($query);
        $searchTerm = "%" . $term . "%";
        $stmt->bindParam(":term", $searchTerm);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function leerUno($id) {
        $stmt = $this->conn->prepare("SELECT * FROM " . $this->table_name . " WHERE id = ?");
        $stmt->bindParam(1, $id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function crear($data) {
        $query = "INSERT INTO " . $this->table_name . " SET 
                  codigo=:codigo, nombre_servicio=:nombre, descripcion=:descripcion, 
                  id_categoria_servicio=:id_categoria, id_isv=:id_isv, precio_venta=:precio,
                  mostrar_en_citas=:mostrar_en_citas";
        $stmt = $this->conn->prepare($query);

        $mostrar_en_citas = !empty($data['mostrar_en_citas']) ? 1 : 0;

        $stmt->bindParam(":codigo", $data['codigo']);
        $stmt->bindParam(":nombre", $data['nombre_servicio']);
        $stmt->bindParam(":descripcion", $data['descripcion']);
        $stmt->bindParam(":id_categoria", $data['id_categoria_servicio']);
        $stmt->bindParam(":id_isv", $data['id_isv']);
        $stmt->bindParam(":precio", $data['precio_venta']);
        $stmt->bindParam(":mostrar_en_citas", $mostrar_en_citas);
        
        return $stmt->execute();
    }

    public function actualizar($id, $data) {
        $query = "UPDATE " . $this->table_name . " SET 
                  codigo=:codigo, nombre_servicio=:nombre, descripcion=:descripcion, 
                  id_categoria_servicio=:id_categoria, id_isv=:id_isv, precio_venta=:precio,
                  mostrar_en_citas=:mostrar_en_citas
                  WHERE id = :id";
        $stmt = $this->conn->prepare($query);

        $mostrar_en_citas = !empty($data['mostrar_en_citas']) ? 1 : 0;

        $stmt->bindParam(":codigo", $data['codigo']);
        $stmt->bindParam(":nombre", $data['nombre_servicio']);
        $stmt->bindParam(":descripcion", $data['descripcion']);
        $stmt->bindParam(":id_categoria", $data['id_categoria_servicio']);
        $stmt->bindParam(":id_isv", $data['id_isv']);
        $stmt->bindParam(":precio", $data['precio_venta']);
        $stmt->bindParam(":mostrar_en_citas", $mostrar_en_citas);
        $stmt->bindParam(":id", $id);

        return $stmt->execute();
    }

    public function eliminar($id) {
        $stmt = $this->conn->prepare("DELETE FROM " . $this->table_name . " WHERE id = ?");
        $stmt->bindParam(1, $id);
        return $stmt->execute();
    }
    
    public function obtenerCategorias() {
        $stmt = $this->conn->prepare("SELECT id, nombre_categoria FROM categorias_servicio ORDER BY nombre_categoria");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function obtenerTiposIsv() {
        $stmt = $this->conn->prepare("SELECT id, nombre_isv, porcentaje FROM tipos_isv ORDER BY nombre_isv");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>
