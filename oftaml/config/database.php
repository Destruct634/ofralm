<?php
// config/Database.php
class Database {
    private $host = '127.0.0.1';
    private $db_name = 'hospital_db';
    private $username = 'root';
    private $password = '';
    //private $password = 'uHLJehiN6w4cpldoIcYr';
    public $conn;

    public function getConnection() {
        $this->conn = null;
        try {
            $this->conn = new PDO('mysql:host=' . $this->host . ';dbname=' . $this->db_name, $this->username, $this->password);
            $this->conn->exec("set names utf8");
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            // --- OPTIMIZACIÓN ZONA HORARIA ---
            $zona_horaria = null;

            // 1. Intentar obtener de sesión (más rápido)
            if (isset($_SESSION['time_zone']) && !empty($_SESSION['time_zone'])) {
                $zona_horaria = $_SESSION['time_zone'];
            } else {
                // 2. Fallback: Consultar BD si no está en sesión
                try {
                    $stmt = $this->conn->query("SELECT zona_horaria FROM configuracion WHERE id = 1");
                    $config = $stmt->fetch(PDO::FETCH_ASSOC);
                    if ($config && !empty($config['zona_horaria'])) {
                        $zona_horaria = $config['zona_horaria'];
                        // Guardar en sesión para la próxima carga
                        if(session_status() === PHP_SESSION_ACTIVE) {
                            $_SESSION['time_zone'] = $zona_horaria;
                        }
                    }
                } catch (Exception $e) {
                    // Si falla la consulta inicial (ej. instalación nueva), usar default
                    $zona_horaria = 'America/Tegucigalpa';
                }
            }

            // 3. Configurar Entorno si tenemos zona horaria
            if ($zona_horaria) {
                // Configurar PHP
                date_default_timezone_set($zona_horaria);

                // Configurar MySQL (Calcular offset numérico para compatibilidad)
                $now = new DateTime("now", new DateTimeZone($zona_horaria));
                $mins = $now->getOffset() / 60;
                $sgn = ($mins < 0 ? -1 : 1);
                $mins = abs($mins);
                $hrs = floor($mins / 60);
                $mins -= $hrs * 60;
                $offset = sprintf('%+d:%02d', $hrs * $sgn, $mins);

                $this->conn->exec("SET time_zone='$offset'");
            }

        } catch(PDOException $exception) {
            echo "Error de conexión: " . $exception->getMessage();
        }
        return $this->conn;
    }
}
?>