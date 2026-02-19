<?php
// app/models/Usuario.php
class Usuario {
    private $conn;
    private $table_name = "usuarios";

    public function __construct($db) {
        $this->conn = $db;
    }

    public function leer() {
        $query = "SELECT u.id, u.nombre_completo, u.usuario, u.estado, g.nombre_grupo 
                  FROM " . $this->table_name . " u
                  LEFT JOIN grupos g ON u.id_grupo = g.id";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    public function leerUno($id) {
        $stmt = $this->conn->prepare("SELECT id, nombre_completo, usuario, estado, id_grupo FROM " . $this->table_name . " WHERE id = ?");
        $stmt->bindParam(1, $id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    public function crear($data) {
        $query = "INSERT INTO " . $this->table_name . " SET nombre_completo=:nombre, usuario=:usuario, password=:password, estado=:estado, id_grupo=:id_grupo";
        $stmt = $this->conn->prepare($query);
        $nombre = htmlspecialchars(strip_tags($data['nombre_completo']));
        $usuario = htmlspecialchars(strip_tags($data['usuario']));
        $estado = htmlspecialchars(strip_tags($data['estado']));
        $id_grupo = htmlspecialchars(strip_tags($data['id_grupo']));
        $password_hash = password_hash($data['password'], PASSWORD_BCRYPT);
        $stmt->bindParam(":nombre", $nombre);
        $stmt->bindParam(":usuario", $usuario);
        $stmt->bindParam(":password", $password_hash);
        $stmt->bindParam(":estado", $estado);
        $stmt->bindParam(":id_grupo", $id_grupo);
        return $stmt->execute();
    }

    public function actualizar($id, $data) {
        $password_part = !empty($data['password']) ? ", password=:password" : "";
        $query = "UPDATE " . $this->table_name . " SET nombre_completo=:nombre, usuario=:usuario, estado=:estado, id_grupo=:id_grupo " . $password_part . " WHERE id=:id";
        $stmt = $this->conn->prepare($query);
        $nombre = htmlspecialchars(strip_tags($data['nombre_completo']));
        $usuario = htmlspecialchars(strip_tags($data['usuario']));
        $estado = htmlspecialchars(strip_tags($data['estado']));
        $id_grupo = htmlspecialchars(strip_tags($data['id_grupo']));
        $id_param = htmlspecialchars(strip_tags($id));
        $stmt->bindParam(":nombre", $nombre);
        $stmt->bindParam(":usuario", $usuario);
        $stmt->bindParam(":estado", $estado);
        $stmt->bindParam(":id_grupo", $id_grupo);
        $stmt->bindParam(":id", $id_param);
        if (!empty($data['password'])) {
            $password_hash = password_hash($data['password'], PASSWORD_BCRYPT);
            $stmt->bindParam(":password", $password_hash);
        }
        return $stmt->execute();
    }

    public function eliminar($id) {
        $stmt = $this->conn->prepare("DELETE FROM " . $this->table_name . " WHERE id = ?");
        $stmt->bindParam(1, $id);
        return $stmt->execute();
    }
    
    public function login($usuario, $password) {
        $stmt = $this->conn->prepare("SELECT * FROM " . $this->table_name . " WHERE usuario = ? AND estado = 'Activo'");
        $stmt->bindParam(1, $usuario);
        $stmt->execute();

        if ($stmt->rowCount() == 1) {
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            if (password_verify($password, $user['password'])) {
                return $user;
            }
        }
        return false;
    }
}
?>