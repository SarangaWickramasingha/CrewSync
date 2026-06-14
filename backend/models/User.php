<?php

class User {

    private $conn;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function findByEmail($email) {

        $query = "SELECT * FROM users WHERE email = :email";

        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(':email', $email);

        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}