<?php

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/Feedback.php';

class FeedbackController {

    public function submit() {

        $database = new Database();
        $db = $database->connect();
        $feedbackModel = new Feedback($db);

        $data = json_decode(
            file_get_contents("php://input"),
            true
        );

        $name = trim($data['name'] ?? '');
        $email = trim($data['email'] ?? '');
        $messageType = trim($data['message_type'] ?? 'General Inquiry');
        $message = trim($data['message'] ?? '');

        if (!$name || !$email || !$message) {

            echo json_encode([
                "success" => false,
                "message" => "Name, email, and message are required"
            ]);

            return;
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {

            echo json_encode([
                "success" => false,
                "message" => "Please enter a valid email address"
            ]);

            return;
        }

        $created = $feedbackModel->create($name, $email, $messageType, $message);

        if (!$created) {

            echo json_encode([
                "success" => false,
                "message" => "Could not submit feedback"
            ]);

            return;
        }

        echo json_encode([
            "success" => true,
            "message" => "Feedback submitted successfully"
        ]);
    }

    public function list() {

        $database = new Database();
        $db = $database->connect();
        $feedbackModel = new Feedback($db);

        $feedback = $feedbackModel->getAll();

        echo json_encode([
            "success" => true,
            "feedback" => $feedback
        ]);
    }

    public function updateStatus() {

        $database = new Database();
        $db = $database->connect();
        $feedbackModel = new Feedback($db);

        $data = json_decode(
            file_get_contents("php://input"),
            true
        );

        $feedbackId = $data['feedback_id'] ?? '';
        $status = $data['status'] ?? '';

        $allowedStatuses = ['New', 'Reviewed', 'Assigned', 'Dismissed'];

        if (!$feedbackId || !in_array($status, $allowedStatuses)) {

            echo json_encode([
                "success" => false,
                "message" => "Valid feedback_id and status are required"
            ]);

            return;
        }

        $updated = $feedbackModel->updateStatus($feedbackId, $status);

        echo json_encode([
            "success" => $updated,
            "message" => $updated ? "Status updated" : "Could not update status"
        ]);
    }
}