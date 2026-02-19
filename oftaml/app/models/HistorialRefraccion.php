<?php
// app/models/HistorialRefraccion.php
class HistorialRefraccion {
    private $conn;
    private $table_name = "historial_refraccion";

    public function __construct($db) {
        $this->conn = $db;
    }

    /**
     * Lee la refracción asociada a un ID de historial.
     */
    public function leerUnoPorHistorial($id_historial) {
        $query = "SELECT * FROM " . $this->table_name . " WHERE id_historial = ? LIMIT 0,1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $id_historial);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Crea una nueva entrada de refracción.
     */
    public function crear($id_historial, $data) {
        if ($this->leerUnoPorHistorial($id_historial)) {
            return $this->actualizar($id_historial, $data);
        }
        $query = "INSERT INTO " . $this->table_name . " SET
                    id_historial = :id_historial,
                    tipo_refraccion = :tipo_refraccion,
                    od_esfera = :od_esfera,
                    od_cilindro = :od_cilindro,
                    od_eje = :od_eje,
                    od_av = :od_av,
                    os_esfera = :os_esfera,
                    os_cilindro = :os_cilindro,
                    os_eje = :os_eje,
                    os_av = :os_av,
                    `add` = :add,
                    observaciones = :observaciones";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id_historial", $id_historial);
        $stmt->bindParam(":tipo_refraccion", $data['tipo_refraccion']);
        $stmt->bindParam(":od_esfera", $data['od_esfera']);
        $stmt->bindParam(":od_cilindro", $data['od_cilindro']);
        $stmt->bindParam(":od_eje", $data['od_eje']);
        $stmt->bindParam(":od_av", $data['od_av']);
        $stmt->bindParam(":os_esfera", $data['os_esfera']);
        $stmt->bindParam(":os_cilindro", $data['os_cilindro']);
        $stmt->bindParam(":os_eje", $data['os_eje']);
        $stmt->bindParam(":os_av", $data['os_av']);
        $stmt->bindParam(":add", $data['add']);
        $stmt->bindParam(":observaciones", $data['observaciones']);
        return $stmt->execute();
    }

    /**
     * Actualiza una entrada de refracción existente.
     */
    public function actualizar($id_historial, $data) {
        $existente = $this->leerUnoPorHistorial($id_historial);
        if ($existente) {
            $query = "UPDATE " . $this->table_name . " SET
                        tipo_refraccion = :tipo_refraccion,
                        od_esfera = :od_esfera,
                        od_cilindro = :od_cilindro,
                        od_eje = :od_eje,
                        od_av = :od_av,
                        os_esfera = :os_esfera,
                        os_cilindro = :os_cilindro,
                        os_eje = :os_eje,
                        os_av = :os_av,
                        `add` = :add,
                        observaciones = :observaciones
                      WHERE id_historial = :id_historial";
        } else {
            $query = "INSERT INTO " . $this->table_name . " SET
                        id_historial = :id_historial,
                        tipo_refraccion = :tipo_refraccion,
                        od_esfera = :od_esfera,
                        od_cilindro = :od_cilindro,
                        od_eje = :od_eje,
                        od_av = :od_av,
                        os_esfera = :os_esfera,
                        os_cilindro = :os_cilindro,
                        os_eje = :os_eje,
                        os_av = :os_av,
                        `add` = :add,
                        observaciones = :observaciones";
        }
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id_historial", $id_historial);
        $stmt->bindParam(":tipo_refraccion", $data['tipo_refraccion']);
        $stmt->bindParam(":od_esfera", $data['od_esfera']);
        $stmt->bindParam(":od_cilindro", $data['od_cilindro']);
        $stmt->bindParam(":od_eje", $data['od_eje']);
        $stmt->bindParam(":od_av", $data['od_av']);
        $stmt->bindParam(":os_esfera", $data['os_esfera']);
        $stmt->bindParam(":os_cilindro", $data['os_cilindro']);
        $stmt->bindParam(":os_eje", $data['os_eje']);
        $stmt->bindParam(":os_av", $data['os_av']);
        $stmt->bindParam(":add", $data['add']);
        $stmt->bindParam(":observaciones", $data['observaciones']);
        return $stmt->execute();
    }

    // --- INICIO DE CORRECCIÓN ---
    /**
     * Obtiene la distribución de errores refractivos (Miopía/Hipermetropía)
     * contando TODOS los ojos (OD y OS) de las refracciones actuales.
     */
    public function obtenerDistribucionErrores() {
        
        $query = "
            SELECT 
                CASE 
                    WHEN esfera < 0 THEN 'Miopía'
                    WHEN esfera > 0 THEN 'Hipermetropía'
                    WHEN esfera = 0 THEN 'Emetropía'
                END as label,
                COUNT(*) as total
            FROM (
                SELECT od_esfera as esfera FROM " . $this->table_name . " 
                WHERE tipo_refraccion = 'Refracción Actual' AND od_esfera IS NOT NULL
                
                UNION ALL
                
                SELECT os_esfera as esfera FROM " . $this->table_name . " 
                WHERE tipo_refraccion = 'Refracción Actual' AND os_esfera IS NOT NULL
            ) as esferas_unidas
            -- La línea 'WHERE label IS NOT NULL' se ha eliminado. Era la causa del error.
            GROUP BY label
        ";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        
        $labels = [];
        $data = [];
        $colors = [
            'Miopía' => 'rgba(54, 162, 235, 0.7)',  // Azul
            'Hipermetropía' => 'rgba(255, 99, 132, 0.7)', // Rojo
            'Emetropía' => 'rgba(75, 192, 192, 0.7)',  // Verde
        ];
        $borderColors = [
            'Miopía' => 'rgba(54, 162, 235, 1)',
            'Hipermetropía' => 'rgba(255, 99, 132, 1)',
            'Emetropía' => 'rgba(75, 192, 192, 1)',
        ];

        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $labels[] = $row['label'];
            $data[] = $row['total'];
        }

        if(empty($labels)) {
            $labels[] = 'Sin Datos';
            $data[] = 1;
             $colors['Sin Datos'] = 'rgba(201, 203, 207, 0.7)';
             $borderColors['Sin Datos'] = 'rgba(201, 203, 207, 1)';
        }

        return [
            'labels' => $labels,
            'datasets' => [[
                'label' => 'Total de Ojos',
                'data' => $data,
                'backgroundColor' => array_map(function($label) use ($colors) { return $colors[$label] ?? '#CCC'; }, $labels),
                'borderColor' => array_map(function($label) use ($borderColors) { return $borderColors[$label] ?? '#FFF'; }, $labels),
                'borderWidth' => 1
            ]]
        ];
    }
    // --- FIN DE CORRECCIÓN ---
}
?>