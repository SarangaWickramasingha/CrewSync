<?php
class Database {
    private $host;
    private $port;
    private $db_name;
    private $username;
    private $password;

    private static $instance = null;
    private $conn;

    private function __construct() {
        $this->host = Env::get('DB_HOST', 'localhost');
        $this->port = (int) Env::get('DB_PORT', 3306);
        $this->db_name = Env::get('DB_NAME', 'crewsync');
        $this->username = Env::get('DB_USERNAME', 'root');
        $this->password = Env::get('DB_PASSWORD', '');

        try {
            $this->conn = new PDO(
                "mysql:host=" . $this->host . ";port=" . $this->port . ";dbname=" . $this->db_name,
                $this->username,
                $this->password
            );
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            error_log("Database connection failed: " . $e->getMessage());
        }
    }

    // only way to get the instance
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new Database();
        }
        return self::$instance;
    }

    // returns the PDO connection
    public function getConnection() {
        return $this->conn;
    }
}