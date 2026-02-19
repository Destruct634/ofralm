<?php
// app/models/Grupo.php
class Grupo {
    private $conn;
    private $table_name = "grupos";

    public function __construct($db) {
        $this->conn = $db;
    }

    public function leer() {
        $stmt = $this->conn->prepare("SELECT * FROM " . $this->table_name);
        $stmt->execute();
        return $stmt;
    }

    public function crear($data) {
        $stmt = $this->conn->prepare("INSERT INTO " . $this->table_name . " SET nombre_grupo=:nombre_grupo");
        $nombre = htmlspecialchars(strip_tags($data['nombre_grupo']));
        $stmt->bindParam(":nombre_grupo", $nombre);
        return $stmt->execute();
    }

    public function actualizar($id, $data) {
        $stmt = $this->conn->prepare("UPDATE " . $this->table_name . " SET nombre_grupo=:nombre_grupo WHERE id=:id");
        $nombre = htmlspecialchars(strip_tags($data['nombre_grupo']));
        $id_param = htmlspecialchars(strip_tags($id));
        $stmt->bindParam(":nombre_grupo", $nombre);
        $stmt->bindParam(":id", $id_param);
        return $stmt->execute();
    }

    public function eliminar($id) {
        $stmt = $this->conn->prepare("DELETE FROM " . $this->table_name . " WHERE id = ?");
        $stmt->bindParam(1, $id);
        return $stmt->execute();
    }
    
    // --- MÉTODO ACTUALIZADO PARA ORGANIZAR POR CATEGORÍAS ---
    public function obtenerPermisos($id_grupo) {
        $query = "SELECT 
                    m.id as id_modulo, 
                    m.nombre_display, 
                    m.nombre_modulo,
                    m.categoria, -- Traemos la categoría
                    p.ver, p.crear, p.editar, p.borrar
                  FROM modulos m
                  LEFT JOIN permisos p ON m.id = p.id_modulo AND p.id_grupo = ?
                  ORDER BY 
                    CASE m.categoria 
                        WHEN 'Clínica' THEN 1 
                        WHEN 'Ventas' THEN 2 
                        WHEN 'Inventario' THEN 3 
                        WHEN 'Reportes' THEN 4 
                        WHEN 'Configuración' THEN 5 
                        ELSE 6 
                    END, 
                    m.nombre_display";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $id_grupo);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function actualizarPermisos($id_grupo, $permisos) {
        $query = "INSERT INTO permisos (id_grupo, id_modulo, ver, crear, editar, borrar) 
                  VALUES (:id_grupo, :id_modulo, :ver, :crear, :editar, :borrar)
                  ON DUPLICATE KEY UPDATE 
                  ver = VALUES(ver), 
                  crear = VALUES(crear), 
                  editar = VALUES(editar), 
                  borrar = VALUES(borrar)";
        
        $stmt = $this->conn->prepare($query);

        try {
            $this->conn->beginTransaction();
            foreach ($permisos as $p) {
                $stmt->execute([
                    'id_grupo' => $id_grupo,
                    'id_modulo' => $p['id_modulo'],
                    'ver' => !empty($p['ver']) ? 1 : 0,
                    'crear' => !empty($p['crear']) ? 1 : 0,
                    'editar' => !empty($p['editar']) ? 1 : 0,
                    'borrar' => !empty($p['borrar']) ? 1 : 0,
                ]);
            }
            $this->conn->commit();
            return true;
        } catch (Exception $e) {
            $this->conn->rollBack();
            return false;
        }
    }
}
?>