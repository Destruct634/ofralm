<?php
// app/models/DashboardWidget.php
class DashboardWidget {
    private $conn;
    private $table_name = "dashboard_widgets";

    public function __construct($db) {
        $this->conn = $db;
    }

    public function leerTodos() {
        $query = "SELECT * FROM " . $this->table_name . " ORDER BY orden ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function leerActivos($id_grupo_usuario) {
        $query = "SELECT * FROM " . $this->table_name . " WHERE activo = 1";

        // Si el usuario no es del grupo de Administradores (asumimos ID 1),
        // solo le mostramos los widgets para 'Todos'.
        if ($id_grupo_usuario != 1) {
            $query .= " AND rol_requerido = 'Todos'";
        }
        
        $query .= " ORDER BY orden ASC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function actualizarConfiguracion($widgets) {
        $this->conn->beginTransaction();
        try {
            $query = "UPDATE " . $this->table_name . " SET activo = :activo, orden = :orden, rol_requerido = :rol_requerido WHERE id = :id";
            $stmt = $this->conn->prepare($query);

            foreach ($widgets as $widget) {
                $stmt->execute([
                    ':activo' => $widget['activo'],
                    ':orden' => $widget['orden'],
                    ':rol_requerido' => $widget['rol_requerido'],
                    ':id' => $widget['id']
                ]);
            }
            $this->conn->commit();
            return true;
        } catch (Exception $e) {
            $this->conn->rollBack();
            error_log($e->getMessage());
            return false;
        }
    }
}
?>