<?php
class Database {
    private $host = "localhost";
    private $db_name = "crewsync";
    private $username = "root";
    private $password = "";

    private static $instance = null;
    private $conn;

    // blocked from outside - no "new Database()" allowed
    private function __construct() {
        try {
            $this->conn = new PDO(
                "mysql:host=" . $this->host . ";dbname=" . $this->db_name,
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