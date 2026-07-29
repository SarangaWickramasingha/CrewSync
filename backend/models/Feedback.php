<?php

class Feedback {

    private $conn;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function create($userId, $name, $email, $messageType, $message) {
        if (!$this->conn) {
            return false;
        }

        $query = "INSERT INTO feedback (user_id, name, email, subject, message)
                VALUES (:user_id, :name, :email, :subject, :message)";

        $stmt = $this->conn->prepare($query);
        $stmt->bindValue(':user_id', $userId, $userId === null ? PDO::PARAM_NULL : PDO::PARAM_INT);
        $stmt->bindParam(':name', $name);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':subject', $messageType);
        $stmt->bindParam(':message', $message);

        return $stmt->execute();
    }
        public function getAll() {
        if (!$this->conn) {
            return [];
        }

        $query = "SELECT * FROM feedback ORDER BY created_at DESC";

        $stmt = $this->conn->prepare($query);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function updateStatus($feedbackId, $isHandled) {
        if (!$this->conn) {
            return false;
        }

        $query = "UPDATE feedback SET is_handled = :is_handled WHERE feedback_id = :feedback_id";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':is_handled', $isHandled, PDO::PARAM_INT);
        $stmt->bindParam(':feedback_id', $feedbackId, PDO::PARAM_INT);

        return $stmt->execute();
    }


}