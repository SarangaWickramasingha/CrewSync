<?php

class Feedback {

    private $conn;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function create($name, $email, $messageType, $message) {

        $query = "INSERT INTO feedback (name, email, message_type, message)
                   VALUES (:name, :email, :message_type, :message)";

        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(':name', $name);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':message_type', $messageType);
        $stmt->bindParam(':message', $message);

        return $stmt->execute();
    }

    public function getAll() {

        $query = "SELECT * FROM feedback ORDER BY created_at DESC";

        $stmt = $this->conn->prepare($query);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function updateStatus($feedbackId, $status) {

        $query = "UPDATE feedback SET status = :status WHERE feedback_id = :feedback_id";

        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(':status', $status);
        $stmt->bindParam(':feedback_id', $feedbackId);

        return $stmt->execute();
    }
}