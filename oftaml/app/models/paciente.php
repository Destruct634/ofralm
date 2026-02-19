<?php
// app/models/Paciente.php
class Paciente {
    private $conn;
    private $table_name = "pacientes";

    public function __construct($db) {
        $this->conn = $db;
    }

    public function leer() {
        $query = "SELECT 
                    p.id, p.nombres, p.apellidos, p.numero_identidad, p.sexo, 
                    p.direccion, p.telefono, p.email, p.fecha_nacimiento, 
                    p.tiene_seguro, p.numero_poliza, p.fecha_creacion, p.observaciones,
                    a.nombre as nombre_aseguradora
                  FROM " . $this->table_name . " p
                  LEFT JOIN aseguradoras a ON p.id_aseguradora = a.id";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    public function leerUno($id) {
        $query = "SELECT 
                    id, nombres, apellidos, numero_identidad, sexo, direccion, telefono, email, 
                    fecha_nacimiento, tiene_seguro, id_aseguradora, numero_poliza, observaciones,
                    antecedente_dm, antecedente_hta, antecedente_glaucoma, antecedente_asma,
                    antecedente_cirugias, antecedente_trauma, antecedente_otros, alergias,
                    fecha_creacion 
                  FROM " . $this->table_name . " 
                  WHERE id = ? LIMIT 0,1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function crear($data) {
        $query = "INSERT INTO " . $this->table_name . " SET 
                    nombres=:nombres, apellidos=:apellidos, numero_identidad=:numero_identidad, 
                    sexo=:sexo, direccion=:direccion, telefono=:telefono, email=:email, 
                    fecha_nacimiento=:fecha_nacimiento, tiene_seguro=:tiene_seguro, 
                    id_aseguradora=:id_aseguradora, numero_poliza=:numero_poliza, 
                    observaciones=:observaciones,
                    antecedente_dm=:antecedente_dm, antecedente_hta=:antecedente_hta,
                    antecedente_glaucoma=:antecedente_glaucoma, antecedente_asma=:antecedente_asma,
                    antecedente_cirugias=:antecedente_cirugias, antecedente_trauma=:antecedente_trauma,
                    antecedente_otros=:antecedente_otros, alergias=:alergias";
        $stmt = $this->conn->prepare($query);

        // Datos básicos (existentes)
        $nombres = htmlspecialchars(strip_tags($data['nombres']));
        $apellidos = htmlspecialchars(strip_tags($data['apellidos']));
        $numero_identidad = htmlspecialchars(strip_tags($data['numero_identidad']));
        $sexo = htmlspecialchars(strip_tags($data['sexo']));
        $direccion = htmlspecialchars(strip_tags($data['direccion']));
        $telefono = htmlspecialchars(strip_tags($data['telefono']));
        $email = htmlspecialchars(strip_tags($data['email']));
        $fecha_nacimiento = htmlspecialchars(strip_tags($data['fecha_nacimiento']));
        $observaciones = !empty($data['observaciones']) ? htmlspecialchars(strip_tags($data['observaciones'])) : null;
        
        $tiene_seguro = htmlspecialchars(strip_tags($data['tiene_seguro']));
        $id_aseguradora = ($data['tiene_seguro'] == 'Sí' && !empty($data['id_aseguradora'])) ? htmlspecialchars(strip_tags($data['id_aseguradora'])) : null;
        $numero_poliza = ($data['tiene_seguro'] == 'Sí' && !empty($data['numero_poliza'])) ? htmlspecialchars(strip_tags($data['numero_poliza'])) : null;

        // Nuevos campos de antecedentes
        $antecedente_dm = $data['antecedente_dm'] ?? 0;
        $antecedente_hta = $data['antecedente_hta'] ?? 0;
        $antecedente_glaucoma = $data['antecedente_glaucoma'] ?? 0;
        $antecedente_asma = $data['antecedente_asma'] ?? 0;
        
        $alergias = !empty($data['alergias']) ? htmlspecialchars(strip_tags($data['alergias'])) : null;
        $antecedente_cirugias = !empty($data['antecedente_cirugias']) ? htmlspecialchars(strip_tags($data['antecedente_cirugias'])) : null;
        $antecedente_trauma = !empty($data['antecedente_trauma']) ? htmlspecialchars(strip_tags($data['antecedente_trauma'])) : null;
        $antecedente_otros = !empty($data['antecedente_otros']) ? htmlspecialchars(strip_tags($data['antecedente_otros'])) : null;

        // Bind params básicos
        $stmt->bindParam(":nombres", $nombres);
        $stmt->bindParam(":apellidos", $apellidos);
        $stmt->bindParam(":numero_identidad", $numero_identidad);
        $stmt->bindParam(":sexo", $sexo);
        $stmt->bindParam(":direccion", $direccion);
        $stmt->bindParam(":telefono", $telefono);
        $stmt->bindParam(":email", $email);
        $stmt->bindParam(":fecha_nacimiento", $fecha_nacimiento);
        $stmt->bindParam(":tiene_seguro", $tiene_seguro);
        $stmt->bindParam(":id_aseguradora", $id_aseguradora);
        $stmt->bindParam(":numero_poliza", $numero_poliza);
        $stmt->bindParam(":observaciones", $observaciones);
        
        // Bind params de antecedentes
        $stmt->bindParam(":antecedente_dm", $antecedente_dm);
        $stmt->bindParam(":antecedente_hta", $antecedente_hta);
        $stmt->bindParam(":antecedente_glaucoma", $antecedente_glaucoma);
        $stmt->bindParam(":antecedente_asma", $antecedente_asma);
        $stmt->bindParam(":antecedente_cirugias", $antecedente_cirugias);
        $stmt->bindParam(":antecedente_trauma", $antecedente_trauma);
        $stmt->bindParam(":antecedente_otros", $antecedente_otros);
        $stmt->bindParam(":alergias", $alergias);

        return $stmt->execute();
    }

    public function actualizar($id, $data) {
        $query = "UPDATE " . $this->table_name . " SET 
                    nombres=:nombres, apellidos=:apellidos, numero_identidad=:numero_identidad, 
                    sexo=:sexo, direccion=:direccion, telefono=:telefono, email=:email, 
                    fecha_nacimiento=:fecha_nacimiento, tiene_seguro=:tiene_seguro, 
                    id_aseguradora=:id_aseguradora, numero_poliza=:numero_poliza, 
                    observaciones=:observaciones,
                    antecedente_dm=:antecedente_dm, antecedente_hta=:antecedente_hta,
                    antecedente_glaucoma=:antecedente_glaucoma, antecedente_asma=:antecedente_asma,
                    antecedente_cirugias=:antecedente_cirugias, antecedente_trauma=:antecedente_trauma,
                    antecedente_otros=:antecedente_otros, alergias=:alergias
                  WHERE id=:id";
        $stmt = $this->conn->prepare($query);

        // Datos básicos (existentes)
        $nombres = htmlspecialchars(strip_tags($data['nombres']));
        $apellidos = htmlspecialchars(strip_tags($data['apellidos']));
        $numero_identidad = htmlspecialchars(strip_tags($data['numero_identidad']));
        $sexo = htmlspecialchars(strip_tags($data['sexo']));
        $direccion = htmlspecialchars(strip_tags($data['direccion']));
        $telefono = htmlspecialchars(strip_tags($data['telefono']));
        $email = htmlspecialchars(strip_tags($data['email']));
        $fecha_nacimiento = htmlspecialchars(strip_tags($data['fecha_nacimiento']));
        $observaciones = !empty($data['observaciones']) ? htmlspecialchars(strip_tags($data['observaciones'])) : null;
        $id_param = htmlspecialchars(strip_tags($id));

        $tiene_seguro = htmlspecialchars(strip_tags($data['tiene_seguro']));
        $id_aseguradora = ($data['tiene_seguro'] == 'Sí' && !empty($data['id_aseguradora'])) ? htmlspecialchars(strip_tags($data['id_aseguradora'])) : null;
        $numero_poliza = ($data['tiene_seguro'] == 'Sí' && !empty($data['numero_poliza'])) ? htmlspecialchars(strip_tags($data['numero_poliza'])) : null;

        // Nuevos campos de antecedentes
        $antecedente_dm = $data['antecedente_dm'] ?? 0;
        $antecedente_hta = $data['antecedente_hta'] ?? 0;
        $antecedente_glaucoma = $data['antecedente_glaucoma'] ?? 0;
        $antecedente_asma = $data['antecedente_asma'] ?? 0;
        
        $alergias = !empty($data['alergias']) ? htmlspecialchars(strip_tags($data['alergias'])) : null;
        $antecedente_cirugias = !empty($data['antecedente_cirugias']) ? htmlspecialchars(strip_tags($data['antecedente_cirugias'])) : null;
        $antecedente_trauma = !empty($data['antecedente_trauma']) ? htmlspecialchars(strip_tags($data['antecedente_trauma'])) : null;
        $antecedente_otros = !empty($data['antecedente_otros']) ? htmlspecialchars(strip_tags($data['antecedente_otros'])) : null;

        // Bind params básicos
        $stmt->bindParam(":nombres", $nombres);
        $stmt->bindParam(":apellidos", $apellidos);
        $stmt->bindParam(":numero_identidad", $numero_identidad);
        $stmt->bindParam(":sexo", $sexo);
        $stmt->bindParam(":direccion", $direccion);
        $stmt->bindParam(":telefono", $telefono);
        $stmt->bindParam(":email", $email);
        $stmt->bindParam(":fecha_nacimiento", $fecha_nacimiento);
        $stmt->bindParam(":tiene_seguro", $tiene_seguro);
        $stmt->bindParam(":id_aseguradora", $id_aseguradora);
        $stmt->bindParam(":numero_poliza", $numero_poliza);
        $stmt->bindParam(":observaciones", $observaciones);
        $stmt->bindParam(":id", $id_param);

        // Bind params de antecedentes
        $stmt->bindParam(":antecedente_dm", $antecedente_dm);
        $stmt->bindParam(":antecedente_hta", $antecedente_hta);
        $stmt->bindParam(":antecedente_glaucoma", $antecedente_glaucoma);
        $stmt->bindParam(":antecedente_asma", $antecedente_asma);
        $stmt->bindParam(":antecedente_cirugias", $antecedente_cirugias);
        $stmt->bindParam(":antecedente_trauma", $antecedente_trauma);
        $stmt->bindParam(":antecedente_otros", $antecedente_otros);
        $stmt->bindParam(":alergias", $alergias);

        return $stmt->execute();
    }
    
    public function eliminar($id) {
        $query = "DELETE FROM " . $this->table_name . " WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $id);
        return $stmt->execute();
    }

    public function contar() {
        $query = "SELECT COUNT(*) as total FROM " . $this->table_name;
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row['total'];
    }

    public function search($term) {
        $query = "SELECT id, CONCAT(nombres, ' ', apellidos, ' (ID: ', numero_identidad, ')') as text 
                  FROM " . $this->table_name . " 
                  WHERE nombres LIKE :term OR apellidos LIKE :term OR numero_identidad LIKE :term
                  LIMIT 10";
        $stmt = $this->conn->prepare($query);
        $searchTerm = "%" . $term . "%";
        $stmt->bindParam(":term", $searchTerm);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function searchById($id) {
        $query = "SELECT id, CONCAT(nombres, ' ', apellidos, ' (ID: ', numero_identidad, ')') as text 
                  FROM " . $this->table_name . " WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function contarNuevosEsteMes() {
        $query = "SELECT COUNT(*) as total_mes FROM " . $this->table_name . " WHERE MONTH(fecha_creacion) = MONTH(CURDATE()) AND YEAR(fecha_creacion) = YEAR(CURDATE())";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchColumn();
    }
    
    // (Funciones de Estado de Cuenta...)
    public function obtenerBalanceInicial($id_paciente, $startDate) {
        // ... (código existente)
        $query_cargos = "SELECT COALESCE(SUM(total), 0) FROM facturas 
                         WHERE id_paciente = :id_paciente AND fecha_emision < :startDate AND estado != 'Anulada'";
        $stmt_cargos = $this->conn->prepare($query_cargos);
        $stmt_cargos->execute(['id_paciente' => $id_paciente, 'startDate' => $startDate]);
        $total_cargos = $stmt_cargos->fetchColumn();

        $query_pagos = "SELECT COALESCE(SUM(pd.monto), 0) 
                        FROM pagos p
                        JOIN pago_detalle pd ON p.id = pd.id_pago
                        JOIN facturas f ON p.id_factura = f.id
                        WHERE f.id_paciente = :id_paciente AND p.fecha_pago < :startDate";
        $stmt_pagos = $this->conn->prepare($query_pagos);
        $stmt_pagos->execute(['id_paciente' => $id_paciente, 'startDate' => $startDate]);
        $total_pagos = $stmt_pagos->fetchColumn();

        $query_nc = "SELECT COALESCE(SUM(total), 0) FROM notas_credito
                     WHERE id_paciente = :id_paciente AND fecha_emision < :startDate AND estado != 'Anulada'";
        $stmt_nc = $this->conn->prepare($query_nc);
        $stmt_nc->execute(['id_paciente' => $id_paciente, 'startDate' => $startDate]);
        $total_nc = $stmt_nc->fetchColumn();
        
        return $total_cargos - ($total_pagos + $total_nc);
    }

    public function obtenerEstadoDeCuenta($id_paciente, $startDate, $endDate) {
        // ... (código existente)
        $query = "
            (SELECT fecha_emision as fecha, 'Factura' as tipo, correlativo as descripcion, total as cargo, 0 as abono FROM facturas WHERE id_paciente = :id_paciente AND fecha_emision BETWEEN :startDate AND :endDate AND estado != 'Anulada')
            UNION ALL
            (SELECT p.fecha_pago as fecha, 'Pago' as tipo, CONCAT('Abono a Factura ', f.correlativo) as descripcion, 0 as cargo, pd.monto as abono FROM pagos p JOIN pago_detalle pd ON p.id = pd.id_pago JOIN facturas f ON p.id_factura = f.id WHERE f.id_paciente = :id_paciente2 AND p.fecha_pago BETWEEN :startDate2 AND :endDate2)
            UNION ALL
            (SELECT nc.fecha_emision as fecha, 'Nota de Crédito' as tipo, CONCAT('Nota de Crédito a Factura ', f.correlativo) as descripcion, 0 as cargo, nc.total as abono FROM notas_credito nc JOIN facturas f ON nc.id_factura_asociada = f.id WHERE nc.id_paciente = :id_paciente3 AND nc.fecha_emision BETWEEN :startDate3 AND :endDate3 AND nc.estado != 'Anulada')
            ORDER BY fecha ASC, tipo DESC
        ";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([
            'id_paciente' => $id_paciente, 'startDate' => $startDate, 'endDate' => $endDate,
            'id_paciente2' => $id_paciente, 'startDate2' => $startDate, 'endDate2' => $endDate,
            'id_paciente3' => $id_paciente, 'startDate3' => $startDate, 'endDate3' => $endDate
        ]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    // --- INICIO DE NUEVA FUNCIÓN ---
    /**
     * Obtiene la distribución de antecedentes patológicos (DM, HTA, Glaucoma, Asma)
     * de toda la base de pacientes.
     */
    public function getDistribucionAntecedentes() {
        $query = "SELECT 
                    SUM(antecedente_dm) as total_dm,
                    SUM(antecedente_hta) as total_hta,
                    SUM(antecedente_glaucoma) as total_glaucoma,
                    SUM(antecedente_asma) as total_asma,
                    COUNT(id) as total_pacientes
                  FROM " . $this->table_name;
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        $labels = ['Diabetes (DM)', 'Hipertensión (HTA)', 'Glaucoma', 'Asma'];
        $data = [
            (int)$row['total_dm'],
            (int)$row['total_hta'],
            (int)$row['total_glaucoma'],
            (int)$row['total_asma']
        ];
        
        $colors = [
            'rgba(255, 99, 132, 0.7)', // Rojo
            'rgba(54, 162, 235, 0.7)', // Azul
            'rgba(75, 192, 192, 0.7)', // Verde
            'rgba(255, 206, 86, 0.7)'  // Amarillo
        ];
        $borderColors = [
            'rgba(255, 99, 132, 1)',
            'rgba(54, 162, 235, 1)',
            'rgba(75, 192, 192, 1)',
            'rgba(255, 206, 86, 1)'
        ];

        return [
            'labels' => $labels,
            'datasets' => [[
                'label' => 'Total de Pacientes',
                'data' => $data,
                'backgroundColor' => $colors,
                'borderColor' => $borderColors,
                'borderWidth' => 1
            ]]
        ];
    }
    // --- FIN DE NUEVA FUNCIÓN ---
}
?>