<?php
// app/models/Medico.php
class Medico {
    private $conn;
    private $table_name = "medicos";

    public function __construct($db) {
        $this->conn = $db;
    }

    public function leer() {
        $query = "SELECT m.id, m.nombres, m.apellidos, m.id_especialidad, e.nombre as especialidad, m.telefono, m.email, m.estado 
                  FROM " . $this->table_name . " m LEFT JOIN especialidades e ON m.id_especialidad = e.id
                  ORDER BY m.apellidos ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }
    
    public function leerPorUsuarioId($id_usuario) {
        $query = "SELECT id, id_especialidad, telefono, email FROM " . $this->table_name . " WHERE id_usuario = ? LIMIT 0,1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $id_usuario);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function guardarDesdeUsuario($id_usuario, $data) {
        $stmt_check = $this->conn->prepare("SELECT id FROM " . $this->table_name . " WHERE id_usuario = ?");
        $stmt_check->execute([$id_usuario]);
        $medico_existente = $stmt_check->fetch(PDO::FETCH_ASSOC);

        if ($medico_existente) {
            $query = "UPDATE " . $this->table_name . " SET id_especialidad=:id_especialidad, telefono=:telefono, email=:email, estado=:estado, nombres=:nombres, apellidos=:apellidos WHERE id_usuario=:id_usuario";
        } else {
            $query = "INSERT INTO " . $this->table_name . " SET id_especialidad=:id_especialidad, telefono=:telefono, email=:email, estado=:estado, nombres=:nombres, apellidos=:apellidos, id_usuario=:id_usuario";
        }
        
        $stmt = $this->conn->prepare($query);

        $nombre_completo = explode(' ', $data['nombre_completo'], 2);
        $nombres = $nombre_completo[0] ?? '';
        $apellidos = $nombre_completo[1] ?? '';
        $email = htmlspecialchars(strip_tags($data['email']));

        $stmt->bindParam(":nombres", $nombres);
        $stmt->bindParam(":apellidos", $apellidos);
        $stmt->bindParam(":id_especialidad", $data['id_especialidad']);
        $stmt->bindParam(":telefono", $data['telefono']);
        $stmt->bindParam(":email", $email);
        $stmt->bindParam(":estado", $data['estado']);
        $stmt->bindParam(":id_usuario", $id_usuario);
        
        return $stmt->execute();
    }

    public function eliminarPorUsuario($id_usuario) {
        $stmt = $this->conn->prepare("DELETE FROM " . $this->table_name . " WHERE id_usuario = ?");
        return $stmt->execute([$id_usuario]);
    }

    public function obtenerEspecialidades() {
        $query = "SELECT id, nombre FROM especialidades WHERE estado = 'Activo' ORDER BY nombre";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>