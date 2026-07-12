<?php

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/Feedback.php';
require_once __DIR__ . '/../middleware/auth.php';
class FeedbackController {

    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

public function submit() {
        $feedbackModel = new Feedback($this->db);

        // Attach user_id if logged in, null for guests
        $user = optionalAuth();
        $userId = $user ? $user['user_id'] : null;
       
       
        error_log("FEEDBACK DEBUG cookie: " . ($_COOKIE[JWT_COOKIE_NAME] ?? 'MISSING'));
error_log("FEEDBACK DEBUG user: " . json_encode($user));

        $data = json_decode(file_get_contents("php://input"), true);

        $name        = trim($data['name'] ?? '');
        $email       = trim($data['email'] ?? '');
        $messageType = trim($data['message_type'] ?? 'General Inquiry');
        $message     = trim($data['message'] ?? '');

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

        $created = $feedbackModel->create($userId, $name, $email, $messageType, $message);

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
        $feedbackModel = new Feedback($this->db);

        $feedback = $feedbackModel->getAll();

        echo json_encode([
            "success"  => true,
            "feedback" => $feedback
        ]);
    }

public function updateStatus() {
        $feedbackModel = new Feedback($this->db);

        $data = json_decode(file_get_contents("php://input"), true);

        $feedbackId = $data['feedback_id'] ?? '';
        $isHandled  = $data['is_handled'] ?? null;

        if (!$feedbackId || !in_array($isHandled, [0, 1, true, false], true)) {
            echo json_encode([
                "success" => false,
                "message" => "Valid feedback_id and is_handled (0 or 1) are required"
            ]);
            return;
        }

        $updated = $feedbackModel->updateStatus($feedbackId, (int) $isHandled);

        echo json_encode([
            "success" => $updated,
            "message" => $updated ? "Status updated" : "Could not update status"
        ]);
    }
}