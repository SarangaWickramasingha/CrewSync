<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../middleware/auth.php';

class NotificationController {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    // Get notifications for the authenticated user
    public function getNotifications() {
        $user = requireAuth();
        
        $stmt = $this->db->prepare("
            SELECT notif_id as id, type, message as text, is_read as `read`, created_at as time
            FROM notifications 
            WHERE user_id = ? 
            ORDER BY created_at DESC
        ");
        $stmt->execute([$user['user_id']]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Convert SQL datetime to a reader-friendly format
        foreach ($rows as &$row) {
            $row['read'] = (bool)$row['read'];
            $timestamp = strtotime($row['time']);
            $row['time'] = "Today, " . date("g:i A", $timestamp); // Custom simplified time format
        }

        echo json_encode(["success" => true, "notifications" => $rows]);
    }

    // Create a new notification
    public function createNotification() {
        $user = requireAuth();
        $data = json_decode(file_get_contents("php://input"), true) ?? [];
        
        $type = $data['type'] ?? 'system';
        $message = $data['text'] ?? '';
        
        if (empty($message)) {
            http_response_code(400);
            echo json_encode(["success" => false, "message" => "Notification text is required"]);
            return;
        }

        $stmt = $this->db->prepare("
            INSERT INTO notifications (user_id, type, message, is_read) 
            VALUES (?, ?, ?, 0)
        ");
        $stmt->execute([$user['user_id'], $type, $message]);

        echo json_encode(["success" => true, "notif_id" => $this->db->lastInsertId()]);
    }

    // Mark notifications as read
    public function markRead() {
        $user = requireAuth();
        $data = json_decode(file_get_contents("php://input"), true) ?? [];
        $notifId = isset($data['id']) ? intval($data['id']) : null;

        if ($notifId) {
            $stmt = $this->db->prepare("UPDATE notifications SET is_read = 1 WHERE notif_id = ? AND user_id = ?");
            $stmt->execute([$notifId, $user['user_id']]);
        } else {
            $stmt = $this->db->prepare("UPDATE notifications SET is_read = 1 WHERE user_id = ?");
            $stmt->execute([$user['user_id']]);
        }

        echo json_encode(["success" => true]);
    }

    // Delete a notification
    public function deleteNotification($notifId) {
        $user = requireAuth();
        
        $stmt = $this->db->prepare("DELETE FROM notifications WHERE notif_id = ? AND user_id = ?");
        $stmt->execute([$notifId, $user['user_id']]);

        echo json_encode(["success" => true]);
    }
}