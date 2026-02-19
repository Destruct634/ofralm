<?php
class Configuracion {
    private $conn;
    private $table_name = "configuracion";

    public function __construct($db) {
        $this->conn = $db;
    }

    public function leer() {
        $query = "SELECT * FROM " . $this->table_name . " WHERE id = 1";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function actualizar($data, $logo_filename = null) {
        // --- INICIO DE MODIFICACIÓN: Agregar zona_horaria ---
        $query = "UPDATE " . $this->table_name . " SET 
                    nombre_clinica = :nombre_clinica, 
                    direccion = :direccion, 
                    telefono = :telefono, 
                    email = :email, 
                    rtn = :rtn,
                    zona_horaria = :zona_horaria, 
                    theme_mode = :theme_mode,
                    background_color = :background_color,
                    navbar_color = :navbar_color,
                    navbar_sticky = :navbar_sticky";
        // --- FIN DE MODIFICACIÓN ---
        
        if ($logo_filename) {
            $query .= ", logo = :logo";
        }
        
        $query .= " WHERE id = 1";
        $stmt = $this->conn->prepare($query);

        // Bind de los datos
        $stmt->bindParam(':nombre_clinica', $data['nombre_clinica']);
        $stmt->bindParam(':direccion', $data['direccion']);
        $stmt->bindParam(':telefono', $data['telefono']);
        $stmt->bindParam(':email', $data['email']);
        $stmt->bindParam(':rtn', $data['rtn']);
        // --- Bind Nuevo ---
        $stmt->bindParam(':zona_horaria', $data['zona_horaria']);
        // ------------------
        $stmt->bindParam(':theme_mode', $data['theme_mode']);
        $stmt->bindParam(':background_color', $data['background_color']);
        $stmt->bindParam(':navbar_color', $data['navbar_color']);
        $stmt->bindParam(':navbar_sticky', $data['navbar_sticky']);

        if ($logo_filename) {
            $stmt->bindParam(':logo', $logo_filename);
        }

        return $stmt->execute();
    }
}
?>